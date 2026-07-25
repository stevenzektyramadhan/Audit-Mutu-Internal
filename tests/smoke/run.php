#!/usr/bin/env php
<?php
/**
 * M0-03 end-to-end baseline plus security checks through M2-01.
 *
 * One-command usage:
 *   php tests/smoke/run.php
 *
 * The harness creates a randomly named local MySQL database, imports the
 * repository schema without its CREATE DATABASE/USE statements, seeds
 * synthetic fixtures, starts a local PHP server, exercises the HTTP workflow,
 * and drops the temporary database in a finally block.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Smoke tests can only run from the command line.\n");
    exit(1);
}

if (!extension_loaded('curl')
    || !extension_loaded('mysqli')
    || !extension_loaded('fileinfo')
    || !extension_loaded('zip')) {
    fwrite(STDERR, "Smoke tests require the curl, mysqli, fileinfo, and zip PHP extensions.\n");
    exit(1);
}

$projectRoot = dirname(__DIR__, 2);
$requestedEnvironment = getenv('CI_ENV');
if ($requestedEnvironment !== false && strtolower(trim($requestedEnvironment)) === 'production') {
    fwrite(STDERR, "Refusing to run smoke tests with CI_ENV=production.\n");
    exit(1);
}

define('BASEPATH', $projectRoot . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
define('APPPATH', $projectRoot . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
define('FCPATH', $projectRoot . DIRECTORY_SEPARATOR);
define('ENVIRONMENT', 'development');

require APPPATH . 'config' . DIRECTORY_SEPARATOR . 'database.php';

class SmokeFailure extends RuntimeException
{
}

class SmokeResponse
{
    public $status;
    public $body;
    public $effectiveUrl;
    public $headers;

    public function __construct($status, $body, $effectiveUrl, array $headers = [])
    {
        $this->status = (int) $status;
        $this->body = (string) $body;
        $this->effectiveUrl = (string) $effectiveUrl;
        $this->headers = $headers;
    }

    public function header($name)
    {
        $name = strtolower((string) $name);
        return isset($this->headers[$name]) ? $this->headers[$name] : NULL;
    }
}

class SmokeHttpClient
{
    private $baseUrl;
    private $handle;
    private $responseHeaders = [];

    public function __construct($baseUrl)
    {
        $this->baseUrl = rtrim((string) $baseUrl, '/');
        $this->handle = curl_init();

        curl_setopt_array($this->handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_COOKIEFILE => '',
            CURLOPT_USERAGENT => 'AMI-M0-03-Smoke/1.0',
            CURLOPT_NOPROXY => '*',
            CURLOPT_HEADERFUNCTION => function ($handle, $line) {
                $length = strlen($line);
                $line = trim($line);
                if (stripos($line, 'HTTP/') === 0) {
                    $this->responseHeaders = [];
                    return $length;
                }
                $separator = strpos($line, ':');
                if ($separator !== FALSE) {
                    $name = strtolower(trim(substr($line, 0, $separator)));
                    $this->responseHeaders[$name] = trim(substr($line, $separator + 1));
                }
                return $length;
            },
        ]);
    }

    public function get($path)
    {
        curl_setopt_array($this->handle, [
            CURLOPT_URL => $this->url($path),
            CURLOPT_POSTFIELDS => null,
            CURLOPT_POST => false,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => [],
        ]);

        return $this->execute();
    }

    public function post($path, array $data)
    {
        curl_setopt_array($this->handle, [
            CURLOPT_URL => $this->url($path),
            CURLOPT_HTTPGET => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        return $this->execute();
    }

    public function post_multipart($path, array $data, array $files)
    {
        foreach ($files as $field => $file) {
            $data[$field] = new CURLFile(
                $file['path'],
                isset($file['mime']) ? $file['mime'] : 'application/octet-stream',
                isset($file['name']) ? $file['name'] : basename($file['path'])
            );
        }

        curl_setopt_array($this->handle, [
            CURLOPT_URL => $this->url($path),
            CURLOPT_HTTPGET => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [],
        ]);

        return $this->execute();
    }

    public function close()
    {
        if ($this->handle !== null) {
            curl_close($this->handle);
            $this->handle = null;
        }
    }

    public function cookie_value($name)
    {
        $cookies = curl_getinfo($this->handle, CURLINFO_COOKIELIST);
        if (!is_array($cookies)) {
            return NULL;
        }

        foreach ($cookies as $cookie) {
            $fields = explode("\t", $cookie);
            if (count($fields) >= 7 && $fields[5] === $name) {
                return $fields[6];
            }
        }

        return NULL;
    }

    private function url($path)
    {
        return $this->baseUrl . '/' . ltrim((string) $path, '/');
    }

    private function execute()
    {
        $this->responseHeaders = [];
        $body = curl_exec($this->handle);
        if ($body === false) {
            throw new SmokeFailure('HTTP request failed: ' . curl_error($this->handle));
        }

        return new SmokeResponse(
            curl_getinfo($this->handle, CURLINFO_RESPONSE_CODE),
            $body,
            curl_getinfo($this->handle, CURLINFO_EFFECTIVE_URL),
            $this->responseHeaders
        );
    }
}

function smoke_assert($condition, $message)
{
    if (!$condition) {
        throw new SmokeFailure($message);
    }
}

function smoke_assert_status(SmokeResponse $response, $expected, $context)
{
    $hint = '';
    if (strpos($response->body, 'The action you have requested is not allowed.') !== false) {
        $hint = ' (CSRF rejection)';
    } elseif (strpos($response->body, 'Akses ditolak') !== false) {
        $hint = ' (authorization rejection)';
    }

    smoke_assert(
        $response->status === (int) $expected,
        $context
            . ': expected HTTP '
            . (int) $expected
            . ', got '
            . $response->status
            . $hint
            . ' at '
            . (parse_url($response->effectiveUrl, PHP_URL_PATH) ?: '/')
    );
}

function smoke_assert_contains($body, $needle, $context)
{
    smoke_assert(
        strpos((string) $body, (string) $needle) !== false,
        $context . ': response did not contain expected marker'
    );
}

function smoke_assert_not_contains($body, $needle, $context)
{
    smoke_assert(
        strpos((string) $body, (string) $needle) === false,
        $context . ': response contained a forbidden marker'
    );
}

function smoke_csrf($body)
{
    $matched = preg_match(
        '/<input\b[^>]*\bname=(["\'])csrf_test_name\1[^>]*\bvalue=(["\'])(.*?)\2[^>]*>/is',
        (string) $body,
        $matches
    );

    if (!$matched) {
        throw new SmokeFailure('CSRF token was not found in the rendered form.');
    }

    return [
        'csrf_test_name',
        html_entity_decode($matches[3], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
    ];
}

function smoke_login(SmokeHttpClient $client, $email, $password)
{
    $form = $client->get('/auth');
    smoke_assert_status($form, 200, 'login form');
    list($csrfName, $csrfHash) = smoke_csrf($form->body);

    return $client->post('/auth/login', [
        $csrfName => $csrfHash,
        'email' => $email,
        'password' => $password,
    ]);
}

function smoke_post_form(SmokeHttpClient $client, $formPath, $submitPath, array $data)
{
    $form = $client->get($formPath);
    smoke_assert_status($form, 200, 'form ' . $formPath);
    list($csrfName, $csrfHash) = smoke_csrf($form->body);
    $data[$csrfName] = $csrfHash;

    return $client->post($submitPath, $data);
}

function smoke_post_multipart(SmokeHttpClient $client, $formPath, $submitPath, array $data, array $files)
{
    $form = $client->get($formPath);
    smoke_assert_status($form, 200, 'multipart form ' . $formPath);
    list($csrfName, $csrfHash) = smoke_csrf($form->body);
    $data[$csrfName] = $csrfHash;

    return $client->post_multipart($submitPath, $data, $files);
}

function smoke_free_port()
{
    $socket = @stream_socket_server('tcp://127.0.0.1:0', $errorNumber, $errorMessage);
    if ($socket === false) {
        throw new SmokeFailure('Unable to allocate a local HTTP port.');
    }

    $address = stream_socket_get_name($socket, false);
    fclose($socket);
    $separator = strrpos($address, ':');

    return (int) substr($address, $separator + 1);
}

function smoke_remove_tree($path, $expectedParent)
{
    $resolvedParent = realpath($expectedParent);
    $resolvedPath = realpath($path);

    if (
        $resolvedParent === false
        || $resolvedPath === false
        || strpos($resolvedPath, $resolvedParent . DIRECTORY_SEPARATOR) !== 0
        || strpos(basename($resolvedPath), 'ami-smoke-') !== 0
    ) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($resolvedPath, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isDir()) {
            @rmdir($item->getPathname());
        } else {
            @unlink($item->getPathname());
        }
    }

    @rmdir($resolvedPath);
}

function smoke_load_schema(mysqli $connection, $schemaPath)
{
    $schema = file_get_contents($schemaPath);
    if ($schema === false) {
        throw new SmokeFailure('Unable to read database_schema.sql.');
    }

    $schema = preg_replace(
        '/^\s*CREATE\s+DATABASE\b.*?;\s*$/mi',
        '',
        $schema
    );
    $schema = preg_replace(
        '/^\s*USE\s+`?[a-zA-Z0-9_]+`?\s*;\s*$/mi',
        '',
        $schema
    );

    smoke_assert(
        stripos($schema, 'CREATE DATABASE') === false
            && preg_match('/^\s*USE\s+/mi', $schema) !== 1,
        'Schema isolation guard failed to remove database-selection statements.'
    );

    if (!$connection->multi_query($schema)) {
        throw new SmokeFailure('Unable to import the isolated smoke-test schema.');
    }

    do {
        $result = $connection->store_result();
        if ($result instanceof mysqli_result) {
            $result->free();
        }
    } while ($connection->more_results() && $connection->next_result());

    if ($connection->errno) {
        throw new SmokeFailure('The isolated schema import did not complete.');
    }
}

function smoke_insert_fixture_user(mysqli $connection, $name, $email, $password, $role, $unit = null)
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $unitType = $role === 'auditee' ? 'unit' : null;
    $statement = $connection->prepare(
        'INSERT INTO users (nama, email, password, role, nama_unit, jenis_unit)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $statement->bind_param('ssssss', $name, $email, $hash, $role, $unit, $unitType);
    $statement->execute();
    $id = (int) $connection->insert_id;
    $statement->close();

    return $id;
}

function smoke_seed(mysqli $connection)
{
    $fixturePassword = 'SmokePass!123';
    $users = [
        'super_admin' => [
            'name' => 'Smoke Super Admin',
            'email' => 'super-admin@smoke.test',
            'role' => 'super_admin',
        ],
        'admin_lpmpi' => [
            'name' => 'Smoke Admin LPMPI',
            'email' => 'admin-lpmpi@smoke.test',
            'role' => 'admin_lpmpi',
        ],
        'auditor_a' => [
            'name' => 'Smoke Auditor A',
            'email' => 'auditor-a@smoke.test',
            'role' => 'auditor',
        ],
        'auditor_b' => [
            'name' => 'Smoke Auditor B',
            'email' => 'auditor-b@smoke.test',
            'role' => 'auditor',
        ],
        'auditee_a' => [
            'name' => 'Smoke Auditee A',
            'email' => 'auditee-a@smoke.test',
            'role' => 'auditee',
            'unit' => 'Smoke Unit A',
        ],
        'auditee_b' => [
            'name' => 'Smoke Auditee B',
            'email' => 'auditee-b@smoke.test',
            'role' => 'auditee',
            'unit' => 'Smoke Unit B',
        ],
        'session_user' => [
            'name' => 'Smoke Session User',
            'email' => 'session-user@smoke.test',
            'role' => 'auditor',
        ],
        'throttle_user' => [
            'name' => 'Smoke Throttle User',
            'email' => 'throttle-user@smoke.test',
            'role' => 'auditor',
        ],
        'inactive_user' => [
            'name' => 'Smoke Inactive User',
            'email' => 'inactive-user@smoke.test',
            'role' => 'auditor',
            'is_active' => 0,
        ],
    ];

    $connection->begin_transaction();

    foreach ($users as $key => $user) {
        $users[$key]['id'] = smoke_insert_fixture_user(
            $connection,
            $user['name'],
            $user['email'],
            $fixturePassword,
            $user['role'],
            isset($user['unit']) ? $user['unit'] : null
        );
        $users[$key]['password'] = $fixturePassword;

        if (isset($user['is_active']) && (int) $user['is_active'] === 0) {
            $connection->query(
                'UPDATE users SET is_active = 0 WHERE id = ' . (int) $users[$key]['id']
            );
        }
    }

    $connection->query(
        "INSERT INTO periode_audit
            (nama_periode, tahun_akademik, semester, tanggal_buka, tanggal_tutup, is_aktif)
         VALUES
            ('Smoke Period', '2026/2027', 'ganjil', '2026-01-01', '2027-12-31', 1)"
    );
    $periodId = (int) $connection->insert_id;

    $xssPayload = '<img src=x onerror=alert(5101)>';
    $standardName = 'Smoke Standard ' . $xssPayload;
    $standardDescription = 'Synthetic M0-03 fixture ' . $xssPayload;
    $standardStatement = $connection->prepare(
        'INSERT INTO standar (nama_standar, deskripsi) VALUES (?, ?)'
    );
    $standardStatement->bind_param('ss', $standardName, $standardDescription);
    $standardStatement->execute();
    $standardId = (int) $connection->insert_id;
    $standardStatement->close();

    $questionStatement = $connection->prepare(
        "INSERT INTO pertanyaan
            (standar_id, urutan, isi_pertanyaan, kategori)
         VALUES (?, ?, ?, 'IKU')"
    );

    foreach ([1, 2] as $order) {
        $question = $order === 1
            ? 'Smoke Question 1 ' . $xssPayload
            : 'Smoke Question 2 </textarea><script>alert(5102)</script>';
        $questionStatement->bind_param('iis', $standardId, $order, $question);
        $questionStatement->execute();
    }
    $questionStatement->close();

    $connection->commit();

    return [
        'users' => $users,
        'period_id' => $periodId,
        'standard_id' => $standardId,
    ];
}

function smoke_env(array $overrides)
{
    $environment = getenv();
    if (!is_array($environment)) {
        $environment = [];
    }

    foreach ($_ENV as $key => $value) {
        if (!array_key_exists($key, $environment)) {
            $environment[$key] = $value;
        }
    }

    foreach ($overrides as $key => $value) {
        $environment[$key] = (string) $value;
    }

    return $environment;
}

function smoke_start_server($projectRoot, $baseUrl, array $databaseConfig, $databaseName, $tempRoot)
{
    $parsed = parse_url($baseUrl);
    $address = $parsed['host'] . ':' . $parsed['port'];
    $privateRoot = $tempRoot . DIRECTORY_SEPARATOR . 'private';
    $logRoot = $tempRoot . DIRECTORY_SEPARATOR . 'logs';
    $sessionRoot = $tempRoot . DIRECTORY_SEPARATOR . 'sessions';
    mkdir($privateRoot, 0775, true);
    mkdir($logRoot, 0775, true);
    mkdir($sessionRoot, 0775, true);

    $stdout = $tempRoot . DIRECTORY_SEPARATOR . 'server.stdout.log';
    $stderr = $tempRoot . DIRECTORY_SEPARATOR . 'server.stderr.log';
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['file', $stdout, 'ab'],
        2 => ['file', $stderr, 'ab'],
    ];

    $command = [
        PHP_BINARY,
        '-S',
        $address,
        $projectRoot . DIRECTORY_SEPARATOR . 'index.php',
    ];

    $environment = smoke_env([
        'CI_ENV' => 'testing',
        'DB_HOST' => $databaseConfig['hostname'],
        'DB_USERNAME' => $databaseConfig['username'],
        'DB_PASSWORD' => $databaseConfig['password'],
        'DB_DATABASE' => $databaseName,
        'APP_BASE_URL' => $baseUrl,
        'APP_COOKIE_SECURE' => 'false',
        'APP_ENCRYPTION_KEY' => 'm0-03-smoke-fixture-key',
        'APP_PRIVATE_STORAGE_PATH' => $privateRoot,
        'APP_LOG_PATH' => $logRoot,
        'APP_SESSION_SAVE_PATH' => $sessionRoot,
        'APP_LOG_THRESHOLD' => '1',
    ]);

    $process = proc_open(
        $command,
        $descriptors,
        $pipes,
        $projectRoot,
        $environment,
        ['bypass_shell' => true]
    );

    if (!is_resource($process)) {
        throw new SmokeFailure('Unable to start the local smoke-test server.');
    }

    if (isset($pipes[0]) && is_resource($pipes[0])) {
        fclose($pipes[0]);
    }

    return [
        'process' => $process,
        'stdout' => $stdout,
        'stderr' => $stderr,
    ];
}

function smoke_wait_for_server($baseUrl, $process)
{
    $client = new SmokeHttpClient($baseUrl);
    $lastStatus = 0;

    try {
        for ($attempt = 0; $attempt < 80; $attempt++) {
            $status = proc_get_status($process);
            if (!$status['running']) {
                throw new SmokeFailure('Smoke-test server exited during startup.');
            }

            try {
                $response = $client->get('/auth');
                $lastStatus = $response->status;
                if ($response->status === 200) {
                    return;
                }
            } catch (Throwable $ignored) {
                // The server may not be listening yet.
            }

            usleep(100000);
        }
    } finally {
        $client->close();
    }

    throw new SmokeFailure(
        'Smoke-test server did not become ready'
        . ($lastStatus > 0 ? '; last HTTP status was ' . $lastStatus : '')
        . '.'
    );
}

function smoke_db_row(mysqli $connection, $sql)
{
    $result = $connection->query($sql);
    $row = $result->fetch_assoc();

    return $row ?: [];
}

function smoke_db_rows(mysqli $connection, $sql)
{
    $result = $connection->query($sql);
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    return $rows;
}

function smoke_canonical_json_value($value)
{
    if (!is_array($value)) {
        return $value;
    }

    $canonical = [];
    foreach ($value as $key => $item) {
        $canonical[$key] = smoke_canonical_json_value($item);
    }
    $is_list = empty($canonical)
        || array_keys($canonical) === range(0, count($canonical) - 1);
    if (!$is_list) {
        ksort($canonical, SORT_STRING);
    }
    return $canonical;
}

function smoke_audit_entry_hash(array $row)
{
    $canonical = [];
    foreach ([
        'event_uuid',
        'actor_user_id',
        'event_type',
        'object_type',
        'object_id',
        'action',
        'outcome',
        'before_hash',
        'after_hash',
        'changes_json',
        'ip_address',
        'user_agent',
        'request_id',
        'previous_hash',
        'created_at',
    ] as $field) {
        $value = array_key_exists($field, $row) ? $row[$field] : NULL;
        if ($field === 'actor_user_id') {
            $value = $value !== NULL ? (int) $value : NULL;
        } elseif ($field === 'changes_json' && $value !== NULL) {
            $decoded = json_decode((string) $value, TRUE);
            $value = json_last_error() === JSON_ERROR_NONE
                ? json_encode(
                    smoke_canonical_json_value($decoded),
                    JSON_UNESCAPED_SLASHES
                        | JSON_UNESCAPED_UNICODE
                        | JSON_INVALID_UTF8_SUBSTITUTE
                )
                : (string) $value;
        } elseif ($value !== NULL) {
            $value = (string) $value;
        }
        $canonical[$field] = $value;
    }

    return hash(
        'sha256',
        json_encode(
            $canonical,
            JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_INVALID_UTF8_SUBSTITUTE
        )
    );
}

function smoke_case($name, callable $test, array &$results)
{
    $test();
    $results[] = $name;
    echo '[PASS] ' . $name . PHP_EOL;
}

$databaseConfig = $db['default'];
$allowedHosts = ['localhost', '127.0.0.1', '::1'];
$allowRemote = getenv('SMOKE_ALLOW_REMOTE_DB') === '1';

if (!$allowRemote && !in_array(strtolower($databaseConfig['hostname']), $allowedHosts, true)) {
    fwrite(
        STDERR,
        "Refusing a non-local database host. Set SMOKE_ALLOW_REMOTE_DB=1 only for an isolated CI database server.\n"
    );
    exit(1);
}

$databaseName = 'ami_smoke_' . getmypid() . '_' . bin2hex(random_bytes(4));
$tempParent = sys_get_temp_dir();
$tempRoot = $tempParent . DIRECTORY_SEPARATOR . 'ami-smoke-' . getmypid() . '-' . bin2hex(random_bytes(4));
$adminConnection = null;
$testConnection = null;
$server = null;
$clients = [];
$results = [];
$failed = false;
$failureMessage = '';

try {
    if (!mkdir($tempRoot, 0775, true) && !is_dir($tempRoot)) {
        throw new SmokeFailure('Unable to create the isolated smoke-test directory.');
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $port = !empty($databaseConfig['port'])
        ? (int) $databaseConfig['port']
        : (int) ini_get('mysqli.default_port');

    $adminConnection = new mysqli(
        $databaseConfig['hostname'],
        $databaseConfig['username'],
        $databaseConfig['password'],
        '',
        $port
    );
    $adminConnection->set_charset('utf8mb4');

    smoke_assert(
        preg_match('/^ami_smoke_[0-9]+_[a-f0-9]{8}$/', $databaseName) === 1,
        'Generated database name failed the isolation allowlist.'
    );
    smoke_assert(
        strcasecmp($databaseName, $databaseConfig['database']) !== 0,
        'Smoke database must not equal the configured application database.'
    );

    $adminConnection->query(
        "CREATE DATABASE `$databaseName` CHARACTER SET utf8 COLLATE utf8_general_ci"
    );

    $testConnection = new mysqli(
        $databaseConfig['hostname'],
        $databaseConfig['username'],
        $databaseConfig['password'],
        $databaseName,
        $port
    );
    $testConnection->set_charset('utf8mb4');

    smoke_load_schema(
        $testConnection,
        $projectRoot . DIRECTORY_SEPARATOR . 'database_schema.sql'
    );
    $fixture = smoke_seed($testConnection);

    $httpPort = smoke_free_port();
    $baseUrl = 'http://127.0.0.1:' . $httpPort . '/';
    $server = smoke_start_server(
        $projectRoot,
        $baseUrl,
        $databaseConfig,
        $databaseName,
        $tempRoot
    );
    smoke_wait_for_server($baseUrl, $server['process']);

    smoke_case('security headers use a request nonce and safe cache policy', function () use ($baseUrl, &$clients) {
        $client = new SmokeHttpClient($baseUrl);
        $clients[] = $client;
        $response = $client->get('/auth');

        smoke_assert_status($response, 200, 'security headers login page');
        $csp = $response->header('content-security-policy');
        smoke_assert(is_string($csp) && $csp !== '', 'CSP header is missing.');
        smoke_assert_contains($csp, "frame-ancestors 'none'", 'CSP frame protection');
        smoke_assert_contains($csp, "script-src-attr 'none'", 'CSP inline event protection');
        smoke_assert(
            preg_match("/'nonce-([^']+)'/", $csp, $nonce_match) === 1,
            'CSP request nonce is missing.'
        );
        $nonce = $nonce_match[1];
        preg_match_all('/<(?:script|style)\b[^>]*>/i', $response->body, $tags);
        smoke_assert(!empty($tags[0]), 'Login page has no nonce-bearing script/style fixtures.');
        foreach ($tags[0] as $tag) {
            smoke_assert_contains($tag, 'nonce="' . $nonce . '"', 'HTML CSP nonce');
        }
        smoke_assert($response->header('x-frame-options') === 'DENY', 'X-Frame-Options is not DENY.');
        smoke_assert($response->header('x-content-type-options') === 'nosniff', 'nosniff header is missing.');
        smoke_assert(
            $response->header('referrer-policy') === 'strict-origin-when-cross-origin',
            'Referrer-Policy is not set.'
        );
        smoke_assert_contains(
            (string) $response->header('permissions-policy'),
            'camera=()',
            'Permissions-Policy'
        );
        smoke_assert_contains((string) $response->header('cache-control'), 'no-store', 'dynamic cache policy');
        smoke_assert(
            $response->header('strict-transport-security') === NULL,
            'HSTS must not be emitted by the development HTTP server.'
        );
    }, $results);

    smoke_case('login invalid', function () use ($baseUrl, &$clients) {
        $client = new SmokeHttpClient($baseUrl);
        $clients[] = $client;
        $response = smoke_login($client, 'missing-user@smoke.test', 'WrongPassword!');
        smoke_assert_status($response, 200, 'invalid login');
        smoke_assert_contains($response->body, 'Email atau password salah.', 'invalid login');
        smoke_assert(
            preg_match('~/auth/?$~', parse_url($response->effectiveUrl, PHP_URL_PATH)) === 1,
            'Invalid login did not return to the auth page.'
        );
    }, $results);

    $roleCases = [
        'super_admin' => ['fixture' => 'super_admin', 'marker' => 'Dashboard Super Admin'],
        'admin_lpmpi' => ['fixture' => 'admin_lpmpi', 'marker' => 'Dashboard Super Admin'],
        'auditor' => ['fixture' => 'auditor_a', 'marker' => 'Dashboard Auditor'],
        'auditee' => ['fixture' => 'auditee_a', 'marker' => 'Dashboard Auditee'],
    ];
    $roleClients = [];
    $taskId = 0;
    $answerRows = [];

    foreach ($roleCases as $role => $case) {
        $client = new SmokeHttpClient($baseUrl);
        $clients[] = $client;
        $roleClients[$role] = $client;
        $user = $fixture['users'][$case['fixture']];

        smoke_case('login valid: ' . $role, function () use ($client, $user) {
            $response = smoke_login($client, $user['email'], $user['password']);
            smoke_assert_status($response, 200, 'valid login');
            smoke_assert(
                preg_match('~/dashboard/?$~', parse_url($response->effectiveUrl, PHP_URL_PATH)) === 1,
                'Valid login did not reach the dashboard.'
            );
        }, $results);

        smoke_case('dashboard role: ' . $role, function () use ($client, $user, $case) {
            $response = $client->get('/dashboard');
            smoke_assert_status($response, 200, 'dashboard');
            smoke_assert_contains($response->body, $case['marker'], 'dashboard role');
            smoke_assert_contains($response->body, $user['name'], 'dashboard session identity');
        }, $results);
    }

    $sessionClient = new SmokeHttpClient($baseUrl);
    $clients[] = $sessionClient;
    $sessionUser = $fixture['users']['session_user'];

    smoke_case('login regenerates session id', function () use ($sessionClient, $sessionUser) {
        $form = $sessionClient->get('/auth');
        smoke_assert_status($form, 200, 'fixation login form');
        $before = $sessionClient->cookie_value('ami_ci_session');
        list($csrfName, $csrfHash) = smoke_csrf($form->body);

        $response = $sessionClient->post('/auth/login', [
            $csrfName => $csrfHash,
            'email' => $sessionUser['email'],
            'password' => $sessionUser['password'],
        ]);
        $after = $sessionClient->cookie_value('ami_ci_session');

        smoke_assert_status($response, 200, 'fixation login');
        smoke_assert($before !== NULL && $after !== NULL, 'Session cookie was not issued.');
        smoke_assert(!hash_equals($before, $after), 'Session ID did not change after login.');
    }, $results);

    smoke_case('logout invalidates session', function () use ($sessionClient) {
        $logout = smoke_post_form($sessionClient, '/dashboard', '/auth/logout', []);
        smoke_assert_status($logout, 200, 'logout');
        smoke_assert(
            preg_match('~/auth/?$~', parse_url($logout->effectiveUrl, PHP_URL_PATH)) === 1,
            'Logout did not return to the auth page.'
        );

        $protected = $sessionClient->get('/dashboard');
        smoke_assert_status($protected, 200, 'dashboard after logout');
        smoke_assert(
            preg_match('~/auth/?$~', parse_url($protected->effectiveUrl, PHP_URL_PATH)) === 1,
            'Logged-out session could still reach a protected page.'
        );
    }, $results);

    smoke_case('inactive account denied generically', function () use ($baseUrl, $fixture, &$clients) {
        $client = new SmokeHttpClient($baseUrl);
        $clients[] = $client;
        $user = $fixture['users']['inactive_user'];
        $response = smoke_login($client, $user['email'], $user['password']);

        smoke_assert_status($response, 200, 'inactive login');
        smoke_assert_contains($response->body, 'Email atau password salah.', 'inactive login');
        smoke_assert(
            preg_match('~/auth/?$~', parse_url($response->effectiveUrl, PHP_URL_PATH)) === 1,
            'Inactive account reached an authenticated page.'
        );
    }, $results);

    smoke_case('repeated login attempts are throttled', function () use ($baseUrl, $fixture, $testConnection, &$clients) {
        $client = new SmokeHttpClient($baseUrl);
        $clients[] = $client;
        $user = $fixture['users']['throttle_user'];

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $response = smoke_login($client, $user['email'], 'DefinitelyWrong!' . $attempt);
            smoke_assert_status($response, 200, 'failed login attempt ' . $attempt);
            smoke_assert_contains($response->body, 'Email atau password salah.', 'failed login attempt');
        }

        $blocked = smoke_login($client, $user['email'], $user['password']);
        smoke_assert_status($blocked, 200, 'throttled login');
        smoke_assert_contains($blocked->body, 'Terlalu banyak percobaan login.', 'throttled login');
        smoke_assert(
            preg_match('~/auth/?$~', parse_url($blocked->effectiveUrl, PHP_URL_PATH)) === 1,
            'Throttled login reached an authenticated page.'
        );

        $events = smoke_db_row(
            $testConnection,
            "SELECT COUNT(*) AS total FROM auth_security_events WHERE event_type = 'login_throttled'"
        );
        smoke_assert((int) $events['total'] >= 1, 'Throttled login was not recorded.');
    }, $results);

    smoke_case('session version revokes existing session', function () use ($baseUrl, $fixture, $testConnection, &$clients) {
        $client = new SmokeHttpClient($baseUrl);
        $clients[] = $client;
        $user = $fixture['users']['session_user'];
        $login = smoke_login($client, $user['email'], $user['password']);
        smoke_assert_status($login, 200, 'revocation login');

        $testConnection->query(
            'UPDATE users SET session_version = session_version + 1 WHERE id = ' . (int) $user['id']
        );

        $protected = $client->get('/dashboard');
        smoke_assert_status($protected, 200, 'dashboard after session revocation');
        smoke_assert(
            preg_match('~/auth/?$~', parse_url($protected->effectiveUrl, PHP_URL_PATH)) === 1,
            'Revoked session could still reach a protected page.'
        );
    }, $results);

    smoke_case(
        'organization hierarchy validates, preserves, and audits unit status',
        function () use ($roleClients, $testConnection) {
            $index = $roleClients['super_admin']->get('/organization-units');
            smoke_assert_status($index, 200, 'organization unit index');
            smoke_assert_contains($index->body, 'UNIVERSITY', 'organization root seed');

            $root = smoke_db_row(
                $testConnection,
                "SELECT id FROM organization_units WHERE code = 'UNIVERSITY' LIMIT 1"
            );
            smoke_assert(!empty($root), 'University root seed is missing.');

            $faculty_create = smoke_post_form(
                $roleClients['super_admin'],
                '/organization-units/create',
                '/organization-units/store',
                [
                    'code' => 'FT',
                    'name' => 'Fakultas Teknik',
                    'type' => 'faculty',
                    'parent_id' => (int) $root['id'],
                    'active' => 1,
                ]
            );
            smoke_assert_status($faculty_create, 200, 'faculty creation');
            smoke_assert_contains(
                $faculty_create->body,
                'Unit organisasi berhasil ditambahkan.',
                'faculty creation'
            );

            $faculty = smoke_db_row(
                $testConnection,
                "SELECT id, parent_id, active
                 FROM organization_units
                 WHERE code = 'FT'
                 LIMIT 1"
            );
            smoke_assert(
                !empty($faculty) && (int) $faculty['parent_id'] === (int) $root['id'],
                'Faculty hierarchy was not persisted.'
            );

            $invalid_program = smoke_post_form(
                $roleClients['super_admin'],
                '/organization-units/create',
                '/organization-units/store',
                [
                    'code' => 'IF-BAD',
                    'name' => 'Program Studi Tidak Valid',
                    'type' => 'study_program',
                    'parent_id' => (int) $root['id'],
                    'active' => 1,
                ]
            );
            smoke_assert_status($invalid_program, 200, 'invalid study program hierarchy');
            smoke_assert_contains(
                $invalid_program->body,
                'Program studi wajib berada langsung di bawah fakultas/UPPS.',
                'invalid study program hierarchy'
            );

            $program_create = smoke_post_form(
                $roleClients['super_admin'],
                '/organization-units/create',
                '/organization-units/store',
                [
                    'code' => 'IF-S1',
                    'name' => 'Informatika',
                    'type' => 'study_program',
                    'parent_id' => (int) $faculty['id'],
                    'active' => 1,
                ]
            );
            smoke_assert_status($program_create, 200, 'study program creation');
            smoke_assert_contains(
                $program_create->body,
                'Unit organisasi berhasil ditambahkan.',
                'study program creation'
            );

            $program = smoke_db_row(
                $testConnection,
                "SELECT id, parent_id, active
                 FROM organization_units
                 WHERE code = 'IF-S1'
                 LIMIT 1"
            );
            smoke_assert(
                !empty($program) && (int) $program['parent_id'] === (int) $faculty['id'],
                'Study program is not linked to its faculty.'
            );

            $blocked_deactivation = smoke_post_form(
                $roleClients['super_admin'],
                '/organization-units',
                '/organization-units/toggle-active/' . (int) $faculty['id'],
                []
            );
            smoke_assert_status($blocked_deactivation, 200, 'parent deactivation guard');
            smoke_assert_contains(
                $blocked_deactivation->body,
                'Nonaktifkan seluruh unit turunan yang masih aktif terlebih dahulu.',
                'parent deactivation guard'
            );

            smoke_post_form(
                $roleClients['super_admin'],
                '/organization-units',
                '/organization-units/toggle-active/' . (int) $program['id'],
                []
            );
            smoke_post_form(
                $roleClients['super_admin'],
                '/organization-units',
                '/organization-units/toggle-active/' . (int) $faculty['id'],
                []
            );

            $inactive = smoke_db_row(
                $testConnection,
                "SELECT
                    SUM(CASE WHEN active = 0 THEN 1 ELSE 0 END) AS inactive_count,
                    COUNT(*) AS total
                 FROM organization_units
                 WHERE code IN ('FT', 'IF-S1')"
            );
            smoke_assert(
                (int) $inactive['total'] === 2 && (int) $inactive['inactive_count'] === 2,
                'Deactivation must preserve both organization rows.'
            );

            $events = smoke_db_row(
                $testConnection,
                "SELECT COUNT(*) AS total
                 FROM security_audit_logs
                 WHERE event_type IN (
                    'organization_unit_created',
                    'organization_unit_deactivated'
                 )"
            );
            smoke_assert((int) $events['total'] >= 4, 'Organization mutations were not audited.');
        },
        $results
    );

    smoke_case(
        'create assignment and answer rows',
        function () use ($roleClients, $fixture, $testConnection, &$taskId, &$answerRows) {
            $response = smoke_post_form(
                $roleClients['super_admin'],
                '/tugas_audit/create',
                '/tugas_audit/store',
                [
                    'periode_id' => $fixture['period_id'],
                    'standar_id' => $fixture['standard_id'],
                    'auditor_id' => $fixture['users']['auditor_a']['id'],
                    'auditee_id' => $fixture['users']['auditee_a']['id'],
                ]
            );
            smoke_assert_status($response, 200, 'assignment creation');
            smoke_assert_contains($response->body, 'Tugas audit berhasil dibuat.', 'assignment creation');

            $task = smoke_db_row(
                $testConnection,
                'SELECT id, status, periode_id, standar_id, auditor_id, auditee_id
                 FROM tugas_audit
                 ORDER BY id DESC
                 LIMIT 1'
            );
            smoke_assert(!empty($task), 'Assignment row was not created.');
            smoke_assert($task['status'] === 'belum_diisi', 'New assignment status is not belum_diisi.');
            smoke_assert((int) $task['periode_id'] === (int) $fixture['period_id'], 'Assignment period mismatch.');
            $taskId = (int) $task['id'];
            $answerRows = smoke_db_rows(
                $testConnection,
                'SELECT id, is_submitted, is_nilai_submitted
                 FROM jawaban_audit
                 WHERE tugas_id = ' . $taskId . '
                 ORDER BY id'
            );
            smoke_assert(count($answerRows) === 2, 'Assignment did not create one answer row per fixture question.');
        },
        $results
    );

    smoke_case(
        'file foundation validates, stores, downloads, and retains instruments',
        function () use ($projectRoot, $roleClients, $fixture, $testConnection, $tempRoot, &$taskId) {
            $valid_path = $tempRoot . DIRECTORY_SEPARATOR . 'm1-06-valid.pdf';
            $valid_body = "%PDF-1.4\n% M1-06 private instrument marker\n1 0 obj\n<<>>\nendobj\n%%EOF\n";
            if (file_put_contents($valid_path, $valid_body) === FALSE) {
                throw new SmokeFailure('Unable to create valid isolated PDF fixture.');
            }

            $upload = smoke_post_multipart(
                $roleClients['admin_lpmpi'],
                '/lpmpi/instrumen',
                '/lpmpi/instrumen/upload/' . (int) $fixture['standard_id'],
                [],
                [
                    'file_instrumen' => [
                        'path' => $valid_path,
                        'name' => 'Instrumen Keamanan M1-06.pdf',
                        'mime' => 'application/pdf',
                    ],
                ]
            );
            smoke_assert_status($upload, 200, 'valid instrument upload');
            smoke_assert_contains($upload->body, 'File instrumen berhasil diupload.', 'valid instrument upload');

            $standard = smoke_db_row(
                $testConnection,
                'SELECT file_instrumen FROM standar WHERE id = ' . (int) $fixture['standard_id']
            );
            $stored_name = (string) $standard['file_instrumen'];
            smoke_assert(
                preg_match('/^[a-f0-9]{48}\.pdf$/', $stored_name) === 1,
                'Instrument storage name is not cryptographically random.'
            );

            $asset = smoke_db_row(
                $testConnection,
                "SELECT id, original_name, storage_scope, size_bytes, sha256, status, is_legacy
                 FROM file_assets
                 WHERE category = 'instrumen'
                   AND stored_name = '" . $testConnection->real_escape_string($stored_name) . "'"
            );
            smoke_assert($asset['original_name'] === 'Instrumen Keamanan M1-06.pdf', 'Original instrument name was not retained as metadata.');
            smoke_assert($asset['storage_scope'] === 'private', 'Instrument was not registered as private.');
            smoke_assert($asset['status'] === 'active' && (int) $asset['is_legacy'] === 0, 'Instrument metadata status is invalid.');
            smoke_assert(strlen((string) $asset['sha256']) === 64, 'Instrument SHA-256 metadata is missing.');

            $private_path = $tempRoot
                . DIRECTORY_SEPARATOR
                . 'private'
                . DIRECTORY_SEPARATOR
                . 'instrumen'
                . DIRECTORY_SEPARATOR
                . $stored_name;
            smoke_assert(is_file($private_path), 'Instrument was not stored in isolated private storage.');
            smoke_assert(
                !is_file($projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'instrumen' . DIRECTORY_SEPARATOR . $stored_name),
                'Instrument was written inside the public document root.'
            );
            smoke_assert(
                hash_equals((string) $asset['sha256'], (string) hash_file('sha256', $private_path)),
                'Stored instrument checksum does not match metadata.'
            );

            $admin_download = $roleClients['admin_lpmpi']->get(
                '/lpmpi/instrumen/download/' . (int) $fixture['standard_id']
            );
            smoke_assert_status($admin_download, 200, 'admin instrument download');
            smoke_assert_contains($admin_download->body, 'M1-06 private instrument marker', 'admin instrument download');
            smoke_assert(
                stripos((string) $admin_download->header('content-disposition'), 'attachment;') === 0,
                'Instrument download is not forced as an attachment.'
            );
            smoke_assert(
                stripos((string) $admin_download->header('content-disposition'), 'Instrumen%20Keamanan%20M1-06.pdf') !== FALSE,
                'Instrument download does not preserve the encoded original name.'
            );
            smoke_assert(
                strtolower((string) $admin_download->header('x-content-type-options')) === 'nosniff',
                'Instrument download is missing nosniff.'
            );

            $auditee_download = $roleClients['auditee']->get(
                '/auditee/download_instrumen/' . (int) $taskId
            );
            smoke_assert_status($auditee_download, 200, 'auditee instrument download');
            smoke_assert_contains($auditee_download->body, 'M1-06 private instrument marker', 'auditee instrument download');

            $invalid_path = $tempRoot . DIRECTORY_SEPARATOR . 'm1-06-invalid.pdf';
            if (file_put_contents($invalid_path, "<?php echo 'not a pdf'; ?>") === FALSE) {
                throw new SmokeFailure('Unable to create invalid isolated upload fixture.');
            }
            $blocked = smoke_post_multipart(
                $roleClients['admin_lpmpi'],
                '/lpmpi/instrumen',
                '/lpmpi/instrumen/upload/' . (int) $fixture['standard_id'],
                [],
                [
                    'file_instrumen' => [
                        'path' => $invalid_path,
                        'name' => 'payload.pdf',
                        'mime' => 'application/pdf',
                    ],
                ]
            );
            smoke_assert_status($blocked, 200, 'content-mismatch instrument upload');
            smoke_assert_contains($blocked->body, 'Isi file tidak sesuai', 'content-mismatch instrument upload');
            $after_block = smoke_db_row(
                $testConnection,
                'SELECT file_instrumen FROM standar WHERE id = ' . (int) $fixture['standard_id']
            );
            smoke_assert($after_block['file_instrumen'] === $stored_name, 'Blocked upload replaced the active instrument.');

            $replacement_body = "%PDF-1.4\n% M1-06 replacement instrument marker\n1 0 obj\n<<>>\nendobj\n%%EOF\n";
            if (file_put_contents($valid_path, $replacement_body) === FALSE) {
                throw new SmokeFailure('Unable to create replacement PDF fixture.');
            }
            $replacement = smoke_post_multipart(
                $roleClients['admin_lpmpi'],
                '/lpmpi/instrumen',
                '/lpmpi/instrumen/upload/' . (int) $fixture['standard_id'],
                [],
                [
                    'file_instrumen' => [
                        'path' => $valid_path,
                        'name' => 'Instrumen Pengganti.pdf',
                        'mime' => 'application/pdf',
                    ],
                ]
            );
            smoke_assert_status($replacement, 200, 'replacement instrument upload');
            smoke_assert_contains($replacement->body, 'File instrumen berhasil diupload.', 'replacement instrument upload');

            $retired = smoke_db_row(
                $testConnection,
                'SELECT status, deleted_at, retention_until
                 FROM file_assets
                 WHERE id = ' . (int) $asset['id']
            );
            smoke_assert(
                $retired['status'] === 'deleted'
                    && $retired['deleted_at'] !== NULL
                    && $retired['retention_until'] !== NULL,
                'Replaced instrument was not soft-deleted with retention.'
            );
            smoke_assert(is_file($private_path), 'Soft-deleted instrument was physically unlinked before retention expired.');

            $replacement_standard = smoke_db_row(
                $testConnection,
                'SELECT file_instrumen FROM standar WHERE id = ' . (int) $fixture['standard_id']
            );
            $replacement_name = (string) $replacement_standard['file_instrumen'];
            $replacement_path = $tempRoot
                . DIRECTORY_SEPARATOR
                . 'private'
                . DIRECTORY_SEPARATOR
                . 'instrumen'
                . DIRECTORY_SEPARATOR
                . $replacement_name;
            if (file_put_contents($replacement_path, "\nTAMPERED", FILE_APPEND) === FALSE) {
                throw new SmokeFailure('Unable to tamper with isolated checksum fixture.');
            }
            $tampered = $roleClients['admin_lpmpi']->get(
                '/lpmpi/instrumen/download/' . (int) $fixture['standard_id']
            );
            smoke_assert_status($tampered, 404, 'tampered instrument download');
            if (file_put_contents($replacement_path, $replacement_body) === FALSE) {
                throw new SmokeFailure('Unable to restore isolated checksum fixture.');
            }
            $restored = $roleClients['admin_lpmpi']->get(
                '/lpmpi/instrumen/download/' . (int) $fixture['standard_id']
            );
            smoke_assert_status($restored, 200, 'restored instrument download');

            $event_summary = smoke_db_row(
                $testConnection,
                "SELECT
                    SUM(event_type = 'upload_succeeded') AS uploads,
                    SUM(event_type = 'upload_blocked') AS blocked,
                    SUM(event_type = 'download_succeeded') AS downloads,
                    SUM(event_type = 'file_retired') AS retired
                 FROM file_security_events
                 WHERE category = 'instrumen'"
            );
            smoke_assert((int) $event_summary['uploads'] >= 2, 'Successful instrument uploads were not logged.');
            smoke_assert((int) $event_summary['blocked'] >= 1, 'Blocked instrument upload was not logged.');
            smoke_assert((int) $event_summary['downloads'] >= 2, 'Sensitive instrument downloads were not logged.');
            smoke_assert((int) $event_summary['retired'] >= 1, 'Instrument retirement was not logged.');
        },
        $results
    );

    smoke_case(
        'auditee save draft',
        function () use ($roleClients, $testConnection, &$taskId, &$answerRows) {
            $firstId = (int) $answerRows[0]['id'];
            $secondId = (int) $answerRows[1]['id'];
            $response = smoke_post_form(
                $roleClients['auditee'],
                '/auditee/form/' . $taskId,
                '/auditee/save/' . $taskId,
                [
                    'jawaban' => [
                        $firstId => 'Synthetic draft answer',
                        $secondId => '',
                    ],
                    'link_bukti' => [
                        $firstId => 'https://evidence.invalid/draft',
                        $secondId => '',
                    ],
                ]
            );
            smoke_assert_status($response, 200, 'auditee draft');
            smoke_assert_contains($response->body, 'Draft jawaban berhasil disimpan.', 'auditee draft');

            $task = smoke_db_row(
                $testConnection,
                'SELECT status FROM tugas_audit WHERE id = ' . (int) $taskId
            );
            $answers = smoke_db_rows(
                $testConnection,
                'SELECT jawaban, link_bukti, is_submitted
                 FROM jawaban_audit
                 WHERE tugas_id = ' . (int) $taskId . '
                 ORDER BY id'
            );
            smoke_assert($task['status'] === 'belum_diisi', 'Draft changed task out of belum_diisi.');
            smoke_assert($answers[0]['jawaban'] === 'Synthetic draft answer', 'Draft answer was not persisted.');
            smoke_assert((int) $answers[0]['is_submitted'] === 0, 'Draft was incorrectly marked submitted.');
            smoke_assert($answers[1]['jawaban'] === '', 'Incomplete draft was not preserved as allowed.');
        },
        $results
    );

    smoke_case(
        'auditee ownership rejection',
        function () use ($baseUrl, $fixture, &$clients, &$taskId) {
            $otherAuditee = new SmokeHttpClient($baseUrl);
            $clients[] = $otherAuditee;
            $user = $fixture['users']['auditee_b'];
            $login = smoke_login($otherAuditee, $user['email'], $user['password']);
            smoke_assert_status($login, 200, 'second auditee login');

            $response = $otherAuditee->get('/auditee/form/' . (int) $taskId);
            smoke_assert_status($response, 404, 'auditee ownership');
            smoke_assert_contains($response->body, 'bukan milik Anda', 'auditee ownership');
        },
        $results
    );

    smoke_case(
        'auditee submit',
        function () use ($roleClients, $testConnection, &$taskId, &$answerRows) {
            $answers = [];
            $links = [];
            foreach ($answerRows as $index => $answer) {
                $answerId = (int) $answer['id'];
                $answers[$answerId] = $index === 0
                    ? 'Synthetic submitted answer 1 <svg onload=alert(5103)>'
                    : 'Synthetic submitted answer 2 </textarea><script>alert(5104)</script>';
                $links[$answerId] = 'https://evidence.invalid/submitted/' . $answerId;
            }

            $response = smoke_post_form(
                $roleClients['auditee'],
                '/auditee/form/' . $taskId,
                '/auditee/submit/' . $taskId,
                [
                    'jawaban' => $answers,
                    'link_bukti' => $links,
                ]
            );
            smoke_assert_status($response, 200, 'auditee submit');
            smoke_assert_contains($response->body, 'Menunggu penilaian auditor', 'auditee submit');

            $task = smoke_db_row(
                $testConnection,
                'SELECT status FROM tugas_audit WHERE id = ' . (int) $taskId
            );
            $summary = smoke_db_row(
                $testConnection,
                'SELECT
                    COUNT(*) AS total,
                    SUM(is_submitted = 1) AS submitted,
                    SUM(submitted_at IS NOT NULL) AS timestamped
                 FROM jawaban_audit
                 WHERE tugas_id = ' . (int) $taskId
            );
            smoke_assert($task['status'] === 'diisi', 'Submitted task status is not diisi.');
            smoke_assert((int) $summary['submitted'] === (int) $summary['total'], 'Not all answers were submitted.');
            smoke_assert((int) $summary['timestamped'] === (int) $summary['total'], 'Submission timestamps are incomplete.');
        },
        $results
    );

    smoke_case(
        'auditor evidence upload uses private metadata and safe download headers',
        function () use ($roleClients, $testConnection, $tempRoot, &$taskId, &$answerRows) {
            $answer_id = (int) $answerRows[0]['id'];
            $evidence_path = $tempRoot . DIRECTORY_SEPARATOR . 'm1-06-evidence.pdf';
            $evidence_body = "%PDF-1.4\n% M1-06 auditor evidence marker\n1 0 obj\n<<>>\nendobj\n%%EOF\n";
            if (file_put_contents($evidence_path, $evidence_body) === FALSE) {
                throw new SmokeFailure('Unable to create auditor evidence fixture.');
            }

            $response = smoke_post_multipart(
                $roleClients['auditor'],
                '/auditor/penilaian/form/' . (int) $taskId,
                '/auditor/penilaian/save_item/' . $answer_id,
                [
                    'skor' => '3',
                    'temuan' => 'M1-06 evidence draft',
                    'jenis_temuan' => 'ob',
                    'tgl_bukti' => '2026-07-24',
                ],
                [
                    'dokumen_bukti' => [
                        'path' => $evidence_path,
                        'name' => 'Bukti Auditor M1-06.pdf',
                        'mime' => 'application/pdf',
                    ],
                ]
            );
            smoke_assert_status($response, 200, 'auditor evidence upload');
            $payload = json_decode($response->body, TRUE);
            smoke_assert(is_array($payload) && !empty($payload['success']), 'Auditor evidence upload did not return success JSON.');
            smoke_assert(
                isset($payload['jawaban']['dokumen_bukti_label'])
                    && $payload['jawaban']['dokumen_bukti_label'] === 'Bukti Auditor M1-06.pdf',
                'Auditor response exposed the storage name instead of original metadata.'
            );

            $answer = smoke_db_row(
                $testConnection,
                'SELECT dokumen_bukti FROM jawaban_audit WHERE id = ' . $answer_id
            );
            $stored_name = (string) $answer['dokumen_bukti'];
            smoke_assert(
                preg_match('/^[a-f0-9]{48}\.pdf$/', $stored_name) === 1,
                'Auditor evidence storage name is not random.'
            );
            $asset = smoke_db_row(
                $testConnection,
                "SELECT category, owner_type, owner_id, original_name, status, sha256
                 FROM file_assets
                 WHERE stored_name = '" . $testConnection->real_escape_string($stored_name) . "'"
            );
            smoke_assert(
                $asset['category'] === 'bukti_auditor'
                    && $asset['owner_type'] === 'jawaban_audit'
                    && (int) $asset['owner_id'] === $answer_id,
                'Auditor evidence metadata owner is invalid.'
            );
            smoke_assert($asset['original_name'] === 'Bukti Auditor M1-06.pdf', 'Auditor original file name metadata is missing.');

            $download = $roleClients['auditor']->get(
                '/auditor/penilaian/download_bukti/' . $answer_id
            );
            smoke_assert_status($download, 200, 'auditor evidence download');
            smoke_assert_contains($download->body, 'M1-06 auditor evidence marker', 'auditor evidence download');
            smoke_assert(
                strtolower((string) $download->header('x-content-type-options')) === 'nosniff',
                'Auditor evidence download is missing nosniff.'
            );
            smoke_assert(
                stripos((string) $download->header('content-disposition'), 'attachment;') === 0,
                'Auditor evidence is not forced as an attachment.'
            );

            $blocked_path = $tempRoot . DIRECTORY_SEPARATOR . 'm1-06-script.html';
            if (file_put_contents($blocked_path, '<script>alert(1)</script>') === FALSE) {
                throw new SmokeFailure('Unable to create blocked script fixture.');
            }
            $blocked = smoke_post_multipart(
                $roleClients['auditor'],
                '/auditor/penilaian/form/' . (int) $taskId,
                '/auditor/penilaian/save_item/' . $answer_id,
                [],
                [
                    'dokumen_bukti' => [
                        'path' => $blocked_path,
                        'name' => 'evidence.html',
                        'mime' => 'text/html',
                    ],
                ]
            );
            smoke_assert_status($blocked, 422, 'auditor script upload rejection');
            $after = smoke_db_row(
                $testConnection,
                'SELECT dokumen_bukti FROM jawaban_audit WHERE id = ' . $answer_id
            );
            smoke_assert($after['dokumen_bukti'] === $stored_name, 'Blocked script replaced auditor evidence.');
        },
        $results
    );

    smoke_case(
        'auditor ownership rejection',
        function () use ($baseUrl, $fixture, &$clients, &$taskId) {
            $otherAuditor = new SmokeHttpClient($baseUrl);
            $clients[] = $otherAuditor;
            $user = $fixture['users']['auditor_b'];
            $login = smoke_login($otherAuditor, $user['email'], $user['password']);
            smoke_assert_status($login, 200, 'second auditor login');

            $response = $otherAuditor->get('/auditor/penilaian/form/' . (int) $taskId);
            smoke_assert_status($response, 404, 'auditor ownership');
            smoke_assert_contains($response->body, 'bukan milik Anda', 'auditor ownership');
        },
        $results
    );

    smoke_case(
        'auditor assess and finalize',
        function () use ($roleClients, $testConnection, &$taskId, &$answerRows) {
            $payload = [
                'skor' => [],
                'temuan' => [],
                'jenis_temuan' => [],
                'saran_perbaikan' => [],
                'rencana_perbaikan' => [],
                'tgl_bukti' => [],
            ];

            foreach ($answerRows as $index => $answer) {
                $answerId = (int) $answer['id'];
                $payload['skor'][$answerId] = $index === 0 ? 3 : 4;
                $payload['temuan'][$answerId] = $index === 0
                    ? 'Synthetic finding 1 <img src=x onerror=alert(5105)>'
                    : 'Synthetic finding 2';
                $payload['jenis_temuan'][$answerId] = 'ob';
                $payload['saran_perbaikan'][$answerId] = $index === 0
                    ? 'Synthetic recommendation <svg onload=alert(5106)>'
                    : '=WEBSERVICE("https://formula.invalid/m1-05")';
                $payload['rencana_perbaikan'][$answerId] = $index === 0
                    ? 'Synthetic action plan </textarea><script>alert(5107)</script>'
                    : 'Synthetic action plan';
                $payload['tgl_bukti'][$answerId] = '2026-07-24';
            }

            $response = smoke_post_form(
                $roleClients['auditor'],
                '/auditor/penilaian/form/' . $taskId,
                '/auditor/penilaian/submit/' . $taskId,
                $payload
            );
            smoke_assert_status($response, 200, 'auditor finalization');
            smoke_assert_contains(
                $response->body,
                'Penilaian sudah disubmit dan form terkunci.',
                'auditor finalization'
            );

            $task = smoke_db_row(
                $testConnection,
                'SELECT status FROM tugas_audit WHERE id = ' . (int) $taskId
            );
            $summary = smoke_db_row(
                $testConnection,
                'SELECT
                    COUNT(*) AS total,
                    SUM(skor BETWEEN 1 AND 4) AS scored,
                    SUM(is_nilai_submitted = 1) AS submitted,
                    SUM(nilai_submitted_at IS NOT NULL) AS timestamped
                 FROM jawaban_audit
                 WHERE tugas_id = ' . (int) $taskId
            );
            smoke_assert($task['status'] === 'dinilai', 'Finalized task status is not dinilai.');
            smoke_assert((int) $summary['scored'] === (int) $summary['total'], 'Not all answers have valid scores.');
            smoke_assert((int) $summary['submitted'] === (int) $summary['total'], 'Assessment flags are incomplete.');
            smoke_assert((int) $summary['timestamped'] === (int) $summary['total'], 'Assessment timestamps are incomplete.');
        },
        $results
    );

    smoke_case(
        'stored XSS and unsafe URL are encoded',
        function () use ($roleClients, $fixture, $testConnection, &$taskId, &$answerRows) {
            $first_answer_id = (int) $answerRows[0]['id'];
            $second_answer_id = (int) $answerRows[1]['id'];
            $unsafe_url = 'javascript:alert(5108)';
            $legacy_file_name = '&quot; onmouseover=alert(5109) x=&quot;.pdf';
            $stored_answer = 'Synthetic submitted answer 1 <svg onload=alert(5103)>';
            $stored_finding = 'Synthetic finding 1 <img src=x onerror=alert(5105)>';
            $stored_recommendation = 'Synthetic recommendation <svg onload=alert(5106)>';
            $stored_plan = 'Synthetic action plan </textarea><script>alert(5107)</script>';

            $statement = $testConnection->prepare(
                'UPDATE jawaban_audit
                 SET jawaban = ?, temuan = ?, saran_perbaikan = ?,
                     rencana_perbaikan = ?, link_bukti = ?
                 WHERE id = ?'
            );
            $statement->bind_param(
                'sssssi',
                $stored_answer,
                $stored_finding,
                $stored_recommendation,
                $stored_plan,
                $unsafe_url,
                $first_answer_id
            );
            $statement->execute();
            $statement->close();

            $formula = '=WEBSERVICE("https://formula.invalid/m1-05")';
            $statement = $testConnection->prepare(
                'UPDATE jawaban_audit
                 SET saran_perbaikan = ?, dokumen_bukti = ?
                 WHERE id = ?'
            );
            $statement->bind_param('ssi', $formula, $legacy_file_name, $second_answer_id);
            $statement->execute();
            $statement->close();

            $responses = [
                'auditee form' => $roleClients['auditee']->get('/auditee/form/' . $taskId),
                'auditor form' => $roleClients['auditor']->get('/auditor/penilaian/form/' . $taskId),
                'admin detail' => $roleClients['super_admin']->get('/tugas_audit/show/' . $taskId),
                'LPMPI detail' => $roleClients['admin_lpmpi']->get(
                    '/lpmpi/laporan/detail/' . (int) $fixture['standard_id']
                ),
            ];

            foreach ($responses as $context => $response) {
                smoke_assert_status($response, 200, $context . ' XSS rendering');
                foreach ([
                    '<img src=x onerror=alert(5101)>',
                    '<svg onload=alert(5103)>',
                    '</textarea><script>alert(5104)</script>',
                    '<img src=x onerror=alert(5105)>',
                ] as $raw_payload) {
                    smoke_assert_not_contains($response->body, $raw_payload, $context);
                }
                smoke_assert_not_contains(
                    $response->body,
                    'href="javascript:',
                    $context . ' unsafe evidence URL'
                );
            }

            smoke_assert_contains(
                $responses['auditor form']->body,
                '&lt;svg onload=alert(5103)&gt;',
                'encoded answer'
            );
            smoke_assert_contains(
                $responses['auditor form']->body,
                '&lt;img src=x onerror=alert(5105)&gt;',
                'encoded finding'
            );
            smoke_assert_contains(
                $responses['auditor form']->body,
                '&amp;quot; onmouseover=alert(5109) x=&amp;quot;.pdf',
                'double-encoded legacy file name'
            );

            $chart = $roleClients['admin_lpmpi']->get('/lpmpi/laporan');
            smoke_assert_status($chart, 200, 'chart XSS rendering');
            smoke_assert_not_contains(
                $chart->body,
                '<img src=x onerror=alert(5101)>',
                'chart raw standard name'
            );
            smoke_assert_contains($chart->body, '\\u003Cimg', 'chart JSON_HEX_TAG');
        },
        $results
    );

    smoke_case(
        'Excel export keeps formulas as text',
        function () use ($projectRoot, $roleClients, $tempRoot) {
            $response = $roleClients['admin_lpmpi']->get('/lpmpi/laporan/export');
            smoke_assert_status($response, 200, 'LPMPI Excel export');

            require_once $projectRoot . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
            $path = $tempRoot . DIRECTORY_SEPARATOR . 'm1-05-export.xlsx';
            if (file_put_contents($path, $response->body) === FALSE) {
                throw new SmokeFailure('Unable to persist the isolated Excel response.');
            }

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $formula = '=WEBSERVICE("https://formula.invalid/m1-05")';
            $found_formula = FALSE;

            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                $highest_row = (int) $sheet->getHighestDataRow();
                for ($row = 2; $row <= $highest_row; $row++) {
                    $cell = $sheet->getCell('H' . $row);
                    if ($cell->getValue() === $formula) {
                        $found_formula = TRUE;
                        smoke_assert(
                            $cell->getDataType() === \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING,
                            'Formula-like recommendation was not exported as an explicit string.'
                        );
                    }

                    smoke_assert(
                        strpos((string) $sheet->getCell('D' . $row)->getValue(), 'javascript:') !== 0,
                        'Unsafe evidence URL was exported as a hyperlink value.'
                    );
                }
            }

            $spreadsheet->disconnectWorksheets();
            smoke_assert($found_formula, 'Formula-like regression fixture was missing from the export.');
        },
        $results
    );

    smoke_case(
        'evidence ownership rejection',
        function () use ($baseUrl, $fixture, $roleClients, $testConnection, $tempRoot, &$clients, &$answerRows) {
            $answer_id = (int) $answerRows[0]['id'];
            $file_name = 'm1-04-evidence-' . bin2hex(random_bytes(6)) . '.pdf';
            $evidence_dir = $tempRoot
                . DIRECTORY_SEPARATOR
                . 'private'
                . DIRECTORY_SEPARATOR
                . 'bukti_auditor';
            if (!is_dir($evidence_dir) && !mkdir($evidence_dir, 0775, TRUE) && !is_dir($evidence_dir)) {
                throw new SmokeFailure('Unable to create isolated evidence fixture directory.');
            }

            $marker = 'M1-04 scoped evidence fixture';
            $legacy_body = "%PDF-1.4\n% " . $marker . "\n%%EOF\n";
            if (file_put_contents($evidence_dir . DIRECTORY_SEPARATOR . $file_name, $legacy_body) === FALSE) {
                throw new SmokeFailure('Unable to create isolated evidence fixture.');
            }

            $statement = $testConnection->prepare(
                'UPDATE jawaban_audit SET dokumen_bukti = ? WHERE id = ?'
            );
            $statement->bind_param('si', $file_name, $answer_id);
            $statement->execute();
            $statement->close();

            $allowed = $roleClients['auditor']->get(
                '/auditor/penilaian/download_bukti/' . $answer_id
            );
            smoke_assert_status($allowed, 200, 'assigned auditor evidence');
            smoke_assert_contains($allowed->body, $marker, 'assigned auditor evidence');

            $other_auditor = new SmokeHttpClient($baseUrl);
            $clients[] = $other_auditor;
            $user = $fixture['users']['auditor_b'];
            $login = smoke_login($other_auditor, $user['email'], $user['password']);
            smoke_assert_status($login, 200, 'evidence attacker login');

            $denied = $other_auditor->get(
                '/auditor/penilaian/download_bukti/' . $answer_id
            );
            smoke_assert_status($denied, 404, 'cross-auditor evidence');
        },
        $results
    );

    smoke_case(
        'final score mutation rejected',
        function () use ($roleClients, $testConnection, &$taskId, &$answerRows) {
            $answer_id = (int) $answerRows[0]['id'];
            $before = smoke_db_row(
                $testConnection,
                'SELECT skor, temuan, is_nilai_submitted
                 FROM jawaban_audit
                 WHERE id = ' . $answer_id
            );

            $form = $roleClients['auditor']->get('/auditor/penilaian/form/' . (int) $taskId);
            smoke_assert_status($form, 200, 'final assessment form');
            list($csrf_name, $csrf_hash) = smoke_csrf($form->body);
            $response = $roleClients['auditor']->post(
                '/auditor/penilaian/save_item/' . $answer_id,
                [
                    $csrf_name => $csrf_hash,
                    'skor' => '1',
                    'temuan' => 'Unauthorized post-final mutation',
                ]
            );
            smoke_assert_status($response, 404, 'post-final score mutation');

            $after = smoke_db_row(
                $testConnection,
                'SELECT skor, temuan, is_nilai_submitted
                 FROM jawaban_audit
                 WHERE id = ' . $answer_id
            );
            smoke_assert($after === $before, 'Final assessment changed after a denied mutation.');
        },
        $results
    );

    smoke_case('capability matrix rejects wrong roles', function () use ($roleClients) {
        smoke_assert_status($roleClients['auditee']->get('/users'), 403, 'auditee users capability');
        smoke_assert_status($roleClients['admin_lpmpi']->get('/users'), 403, 'LPMPI users capability');
        smoke_assert_status($roleClients['auditor']->get('/lpmpi/laporan'), 403, 'auditor report capability');
        smoke_assert_status($roleClients['auditor']->get('/organization-units'), 403, 'auditor organization capability');
    }, $results);

    smoke_case('LPMPI report opens', function () use ($roleClients) {
        $response = $roleClients['admin_lpmpi']->get('/lpmpi/laporan');
        smoke_assert_status($response, 200, 'LPMPI report');
        smoke_assert_contains($response->body, 'Laporan &amp; Statistik', 'LPMPI report');
        smoke_assert_contains($response->body, 'Smoke Standard', 'LPMPI report data');
    }, $results);

    smoke_case('immutable audit ledger covers sensitive workflow and rejects tampering', function () use ($testConnection) {
        $events = smoke_db_rows(
            $testConnection,
            'SELECT event_type, COUNT(*) AS total
             FROM security_audit_logs
             GROUP BY event_type'
        );
        $event_counts = [];
        foreach ($events as $event) {
            $event_counts[$event['event_type']] = (int) $event['total'];
        }
        foreach ([
            'login_failed',
            'login_succeeded',
            'logout',
            'assignment_created',
            'auditee_submission_submitted',
            'auditor_assessment_submitted',
            'upload_succeeded',
            'file_retired',
            'sensitive_report_exported',
        ] as $event_type) {
            smoke_assert(
                isset($event_counts[$event_type]) && $event_counts[$event_type] > 0,
                'Required immutable event is missing: ' . $event_type
            );
        }

        $rows = smoke_db_rows(
            $testConnection,
            'SELECT id, event_uuid, actor_user_id, event_type, object_type, object_id,
                    action, outcome, before_hash, after_hash, changes_json,
                    ip_address, user_agent, request_id, previous_hash,
                    entry_hash, created_at
             FROM security_audit_logs
             ORDER BY id ASC'
        );
        smoke_assert(!empty($rows), 'Immutable ledger is empty.');
        $previous_hash = str_repeat('0', 64);
        foreach ($rows as $row) {
            smoke_assert(
                hash_equals($previous_hash, $row['previous_hash']),
                'Audit previous hash does not form a continuous chain.'
            );
            $calculated_hash = smoke_audit_entry_hash($row);
            smoke_assert(
                hash_equals($row['entry_hash'], $calculated_hash),
                'Audit entry hash verification failed for id '
                    . (int) $row['id']
                    . ' event '
                    . $row['event_type']
                    . '.'
            );
            smoke_assert(
                strpos((string) $row['ip_address'], 'hmac-sha256:') === 0,
                'Audit ledger stored a raw IP address.'
            );
            smoke_assert(
                strpos((string) $row['user_agent'], 'hmac-sha256:') === 0,
                'Audit ledger stored a raw user agent.'
            );
            smoke_assert(
                stripos((string) $row['changes_json'], 'password') === FALSE
                    && stripos((string) $row['changes_json'], 'cookie') === FALSE
                    && stripos((string) $row['changes_json'], 'token') === FALSE,
                'Audit metadata contains a forbidden sensitive key.'
            );
            $previous_hash = $row['entry_hash'];
        }

        $state = smoke_db_row(
            $testConnection,
            'SELECT current_hash, last_log_id
             FROM security_audit_chain_state
             WHERE id = 1'
        );
        smoke_assert(
            hash_equals($previous_hash, $state['current_hash']),
            'Audit chain head does not match the last entry.'
        );

        $first = smoke_db_row(
            $testConnection,
            'SELECT id, event_type FROM security_audit_logs ORDER BY id ASC LIMIT 1'
        );
        $before_count = count($rows);
        $update_blocked = FALSE;
        try {
            $testConnection->query(
                "UPDATE security_audit_logs
                 SET event_type = 'tampered'
                 WHERE id = " . (int) $first['id']
            );
        } catch (mysqli_sql_exception $exception) {
            $update_blocked = strpos($exception->getMessage(), 'append-only') !== FALSE;
        }
        smoke_assert($update_blocked, 'Database trigger did not block audit UPDATE.');

        $delete_blocked = FALSE;
        try {
            $testConnection->query(
                'DELETE FROM security_audit_logs WHERE id = ' . (int) $first['id']
            );
        } catch (mysqli_sql_exception $exception) {
            $delete_blocked = strpos($exception->getMessage(), 'append-only') !== FALSE;
        }
        smoke_assert($delete_blocked, 'Database trigger did not block audit DELETE.');

        $after = smoke_db_row(
            $testConnection,
            'SELECT COUNT(*) AS total, MIN(event_type) AS first_event
             FROM security_audit_logs'
        );
        smoke_assert((int) $after['total'] === $before_count, 'Audit row count changed after tamper attempts.');
        $preserved = smoke_db_row(
            $testConnection,
            'SELECT event_type FROM security_audit_logs WHERE id = ' . (int) $first['id']
        );
        smoke_assert($preserved['event_type'] === $first['event_type'], 'Audit event changed after blocked UPDATE.');
    }, $results);

    echo PHP_EOL;
    echo 'Smoke tests passed: ' . count($results) . PHP_EOL;
    echo 'Database isolation: temporary database only; cleanup scheduled.' . PHP_EOL;
} catch (Throwable $exception) {
    $failed = true;
    $failureMessage = $exception->getMessage();
} finally {
    foreach ($clients as $client) {
        if ($client instanceof SmokeHttpClient) {
            $client->close();
        }
    }

    if (is_array($server) && isset($server['process']) && is_resource($server['process'])) {
        if ($failed && isset($server['stderr']) && is_file($server['stderr'])) {
            $serverLog = file($server['stderr'], FILE_IGNORE_NEW_LINES);
            if (is_array($serverLog) && !empty($serverLog)) {
                $serverLog = array_slice($serverLog, -12);
                fwrite(STDERR, "[SERVER LOG]\n" . implode(PHP_EOL, $serverLog) . PHP_EOL);
            }
        }

        @proc_terminate($server['process']);
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $status = proc_get_status($server['process']);
            if (!$status['running']) {
                break;
            }
            usleep(100000);
        }
        @proc_close($server['process']);
    }

    if ($testConnection instanceof mysqli) {
        $testConnection->close();
    }

    if ($adminConnection instanceof mysqli) {
        if (preg_match('/^ami_smoke_[0-9]+_[a-f0-9]{8}$/', $databaseName) === 1) {
            try {
                $adminConnection->query("DROP DATABASE IF EXISTS `$databaseName`");
            } catch (Throwable $ignored) {
                $failed = true;
                if ($failureMessage === '') {
                    $failureMessage = 'Temporary smoke database cleanup failed.';
                }
            }
        }
        $adminConnection->close();
    }

    smoke_remove_tree($tempRoot, $tempParent);
}

if ($failed) {
    fwrite(STDERR, '[FAIL] ' . $failureMessage . PHP_EOL);
    exit(1);
}

exit(0);
