<?php

$root = dirname(__DIR__);

function m102_check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function m102_source($root, $path)
{
    $contents = file_get_contents($root . DIRECTORY_SEPARATOR . $path);
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }
    return $contents;
}

function m102_run_startup($root, $environment)
{
    $command = [PHP_BINARY, $root . DIRECTORY_SEPARATOR . 'index.php'];
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($command, $descriptors, $pipes, $root, $environment);
    if (!is_resource($process)) {
        throw new RuntimeException('Tidak dapat menjalankan bootstrap production.');
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exit_code = proc_close($process);

    return [
        'exit_code' => $exit_code,
        'stdout' => (string) $stdout,
        'stderr' => (string) $stderr,
    ];
}

function m102_remove_tree($path)
{
    if (!is_dir($path)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $item) {
        if ($item->isDir()) {
            rmdir($item->getPathname());
        } else {
            unlink($item->getPathname());
        }
    }
    rmdir($path);
}

defined('BASEPATH') OR define('BASEPATH', $root . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
defined('APPPATH') OR define('APPPATH', $root . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
require_once APPPATH . 'config/security_bootstrap.php';

$valid_url = ami_parse_production_base_url('https://ami.example.ac.id/app/');
m102_check($valid_url !== FALSE && $valid_url['host'] === 'ami.example.ac.id', 'Base URL production yang valid ditolak.');
foreach ([
    'http://ami.example.ac.id/',
    'https://user:pass@ami.example.ac.id/',
    'https://ami.example.ac.id/?debug=1',
    'https://ami.example.ac.id/#fragment',
    'https://ami.example.ac.id/%0d%0aX-Test:bad',
    'https://bad host.example/',
] as $invalid_url) {
    m102_check(ami_parse_production_base_url($invalid_url) === FALSE, 'Base URL tidak aman diterima: ' . $invalid_url);
}

m102_check(
    ami_allowed_hosts('ami.example.ac.id,reports.example.ac.id', 'ami.example.ac.id')
        === ['ami.example.ac.id', 'reports.example.ac.id'],
    'Allowed hosts tidak dinormalisasi dengan benar.'
);
m102_check(ami_allowed_hosts('*.example.ac.id', 'ami.example.ac.id') === FALSE, 'Wildcard host tidak boleh diterima.');
m102_check(ami_valid_proxy_list('10.0.0.1,192.168.10.0/24,2001:db8::/32'), 'Proxy IP/CIDR valid ditolak.');
m102_check(!ami_valid_proxy_list('10.0.0.0/99'), 'CIDR proxy invalid diterima.');
m102_check(ami_default_database_password('ami_local_password', 'ami_app', 'ami'), 'Password Compose harus dikenali sebagai default.');
m102_check(ami_default_database_password('ami_app', 'ami_app', 'ami'), 'Password yang sama dengan username harus ditolak.');
m102_check(!ami_default_database_password('m102-unique-db-secret-value', 'ami_app', 'ami'), 'Password non-default ditolak.');
m102_check(ami_privileged_database_username('root'), 'User database root harus ditolak.');

$sanitized = ami_sanitize_log_message("password=super-secret\r\nAuthorization: Bearer abc.def");
m102_check(strpos($sanitized, 'super-secret') === FALSE, 'Password belum di-redact dari log.');
m102_check(strpos($sanitized, 'abc.def') === FALSE, 'Bearer token belum di-redact dari log.');
m102_check(strpos($sanitized, "\r") === FALSE && strpos($sanitized, "\n") === FALSE, 'Log injection newline belum dinormalisasi.');

$config_source = m102_source($root, 'application/config/config.php');
$database_source = m102_source($root, 'application/config/database.php');
$log_source = m102_source($root, 'application/core/MY_Log.php');
$exceptions_source = m102_source($root, 'application/core/MY_Exceptions.php');
$index_source = m102_source($root, 'index.php');
$env_example = m102_source($root, '.env.example');
$compose_source = m102_source($root, 'compose.yaml');

foreach ([
    'APP_BASE_URL',
    'APP_ALLOWED_HOSTS',
    'APP_TRUSTED_PROXIES',
    'APP_ENCRYPTION_KEY',
    'APP_COOKIE_SECURE',
    'APP_LOG_THRESHOLD',
    'APP_LOG_PATH',
    'APP_SESSION_SAVE_PATH',
    'APP_PRIVATE_STORAGE_PATH',
] as $name) {
    m102_check(strpos($config_source, $name) !== FALSE, 'Config aplikasi belum membaca ' . $name);
    m102_check(strpos($env_example, $name . '=') !== FALSE, '.env.example belum memuat ' . $name);
}
foreach (['DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE'] as $name) {
    m102_check(strpos($database_source, "getenv('" . $name . "')") !== FALSE, 'Config database belum membaca ' . $name);
    m102_check(strpos($env_example, $name . '=') !== FALSE, '.env.example belum memuat ' . $name);
}
m102_check(strpos($config_source, "log_threshold'] === 1") !== FALSE, 'Production belum menolak debug log threshold.');
m102_check(strpos($config_source, 'ami_display_errors_disabled()') !== FALSE, 'Production belum memvalidasi display_errors.');
m102_check(strpos($config_source, 'ami_path_is_outside($session_path, $web_root)') !== FALSE, 'Session production belum diwajibkan di luar web root.');
m102_check(strpos($database_source, 'ami_default_database_password') !== FALSE, 'Default database password belum ditolak.');
m102_check(strpos($database_source, 'ami_privileged_database_username') !== FALSE, 'User database privileged belum ditolak.');
m102_check(strpos($database_source, "'save_queries' => (ENVIRONMENT !== 'production')") !== FALSE, 'Query recording production belum dimatikan.');
m102_check(strpos($index_source, "header('X-Request-ID: '.AMI_REQUEST_ID)") !== FALSE, 'Response belum memiliki correlation ID.');
m102_check(strpos($log_source, '[request_id=') !== FALSE && strpos($log_source, 'ami_sanitize_log_message') !== FALSE, 'Log belum memuat correlation ID atau sanitization.');
m102_check(strpos($exceptions_source, '$exception->getFile()') === FALSE, 'Production exception handler tidak boleh menampilkan filesystem path.');
m102_check(strpos($exceptions_source, '$exception->getTrace()') === FALSE, 'Production exception handler tidak boleh menampilkan stack trace.');
m102_check(strpos($exceptions_source, 'ami_request_id()') !== FALSE, 'Safe error page belum menampilkan request ID.');
m102_check(strpos($env_example, 'ami_local_password') === FALSE, '.env.example memuat password development.');
m102_check(strpos($env_example, 'local_root_password') === FALSE, '.env.example memuat root password development.');
m102_check(strpos($compose_source, 'ami_local_password') === FALSE, 'Compose memuat password development literal.');
m102_check(strpos($compose_source, 'local_root_password') === FALSE, 'Compose memuat root password development literal.');
m102_check(
    substr_count($compose_source, '${AMI_DEV_DB_PASSWORD:?') === 2,
    'Compose harus mengambil password user database dari environment tanpa fallback.'
);
m102_check(
    substr_count($compose_source, '${AMI_DEV_DB_ROOT_PASSWORD:?') === 1,
    'Compose harus mengambil root password dari environment tanpa fallback.'
);

$schema_and_seed = m102_source($root, 'database_schema.sql') . m102_source($root, 'database_dummy.sql');
m102_check(
    preg_match('/INSERT\s+INTO\s+`?users`?/i', $schema_and_seed) !== 1,
    'Schema/seed tidak boleh membuat akun default.'
);

$temp_root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ami-m102-' . bin2hex(random_bytes(8));
$log_path = $temp_root . DIRECTORY_SEPARATOR . 'logs';
$session_path = $temp_root . DIRECTORY_SEPARATOR . 'sessions';
$private_path = $temp_root . DIRECTORY_SEPARATOR . 'private';
foreach ([$temp_root, $log_path, $session_path, $private_path] as $directory) {
    if (!is_dir($directory) && !mkdir($directory, 0700, TRUE)) {
        throw new RuntimeException('Tidak dapat membuat fixture directory ' . $directory);
    }
}

$system_environment = getenv();
if (!is_array($system_environment)) {
    $system_environment = [];
}
$valid_environment = array_merge($system_environment, [
    'CI_ENV' => 'production',
    'APP_BASE_URL' => 'https://ami.example.ac.id/',
    'APP_ALLOWED_HOSTS' => 'ami.example.ac.id',
    'APP_TRUSTED_PROXIES' => '',
    'APP_ENCRYPTION_KEY' => str_repeat('a', 64),
    'APP_COOKIE_SECURE' => 'true',
    'APP_LOG_THRESHOLD' => '1',
    'APP_LOG_PATH' => $log_path,
    'APP_SESSION_SAVE_PATH' => $session_path,
    'APP_PRIVATE_STORAGE_PATH' => $private_path,
    'DB_HOST' => '127.0.0.1',
    'DB_USERNAME' => 'ami_app',
    'DB_PASSWORD' => 'm102-unique-db-secret-value',
    'DB_DATABASE' => 'ami_test',
    'HTTP_HOST' => 'ami.example.ac.id',
]);

$scenarios = [
    'empty encryption key' => ['APP_ENCRYPTION_KEY' => ''],
    'default database password' => ['DB_PASSWORD' => 'ami_local_password'],
    'privileged database username' => ['DB_USERNAME' => 'root'],
    'missing private storage' => ['APP_PRIVATE_STORAGE_PATH' => $temp_root . DIRECTORY_SEPARATOR . 'missing'],
    'debug log threshold' => ['APP_LOG_THRESHOLD' => '4'],
    'invalid base URL' => ['APP_BASE_URL' => 'http://ami.example.ac.id/'],
    'unapproved Host' => ['HTTP_HOST' => 'evil.example.test'],
    'invalid trusted proxy' => ['APP_TRUSTED_PROXIES' => '10.0.0.0/99'],
];

try {
    foreach ($scenarios as $name => $overrides) {
        $result = m102_run_startup($root, array_merge($valid_environment, $overrides));
        m102_check($result['exit_code'] !== 0, 'Startup tidak gagal untuk scenario: ' . $name);
        m102_check(
            trim($result['stdout']) === 'Production configuration is incomplete.',
            'Startup membocorkan detail untuk scenario ' . $name . ': ' . trim($result['stdout'])
        );
        m102_check(trim($result['stderr']) === '', 'Startup menulis detail ke stderr untuk scenario: ' . $name);
        m102_check(
            preg_match('/(?:Stack trace|Fatal error|\\.php on line|[A-Z]:\\\\|DB_PASSWORD)/i', $result['stdout']) !== 1,
            'Startup response membocorkan stack/path/secret untuk scenario: ' . $name
        );
    }
} finally {
    m102_remove_tree($temp_root);
}

fwrite(STDOUT, "Security configuration regression checks passed.\n");
