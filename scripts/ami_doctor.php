<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "ami_doctor.php must be run from the CLI.\n");
    exit(1);
}

$repositoryRoot = dirname(__DIR__);

if (!defined('BASEPATH')) {
    define('BASEPATH', $repositoryRoot . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
}
if (!defined('APPPATH')) {
    define('APPPATH', $repositoryRoot . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
}
if (!defined('FCPATH')) {
    define('FCPATH', $repositoryRoot . DIRECTORY_SEPARATOR);
}

function doctorCheck(array &$checks, bool $passed, string $label, string $detail = ''): void
{
    $checks[] = ['passed' => $passed, 'label' => $label];
    $message = ($passed ? 'PASS' : 'FAIL') . ': ' . $label;
    if ($detail !== '') {
        $message .= ' (' . $detail . ')';
    }
    fwrite($passed ? STDOUT : STDERR, $message . PHP_EOL);
}

function doctorNote(string $message): void
{
    fwrite(STDOUT, 'NOTE: ' . $message . PHP_EOL);
}

function doctorContainsPlaceholder($value): bool
{
    if (!is_string($value) || trim($value) === '') {
        return false;
    }

    $normalized = strtoupper($value);
    foreach ([
        'GANTI',
        'CHANGE_ME',
        'CHANGE-ME',
        'REPLACE',
        'YOUR_',
        'EXAMPLE.INVALID',
        'AMI_DEFAULT_DEV_ENCRYPTION_KEY',
    ] as $marker) {
        if (strpos($normalized, $marker) !== false) {
            return true;
        }
    }

    return false;
}

function doctorStringValue(array $values, string $key): ?string
{
    if (!array_key_exists($key, $values) || !is_string($values[$key])) {
        return null;
    }

    return trim($values[$key]);
}

function doctorPathIsWithin(string $candidatePath, string $parentPath): bool
{
    $candidatePath = rtrim($candidatePath, '/\\');
    $parentPath = rtrim($parentPath, '/\\');

    return $candidatePath === $parentPath
        || strpos($candidatePath . DIRECTORY_SEPARATOR, $parentPath . DIRECTORY_SEPARATOR) === 0;
}

function doctorCheckDirectory(
    array &$checks,
    string $label,
    ?string $path,
    ?string $webRoot = null,
    bool $mustBeOutsideWebRoot = false
): ?string {
    if ($path === null || $path === '') {
        doctorCheck($checks, false, $label, 'path is not configured');
        return null;
    }

    $resolvedPath = realpath($path);
    if ($resolvedPath === false || !is_dir($resolvedPath)) {
        doctorCheck($checks, false, $label, 'directory does not exist');
        return null;
    }

    if (!is_writable($resolvedPath)) {
        doctorCheck($checks, false, $label, 'not writable by the CLI PHP user');
        return $resolvedPath;
    }

    if ($mustBeOutsideWebRoot && $webRoot !== null && doctorPathIsWithin($resolvedPath, $webRoot)) {
        doctorCheck($checks, false, $label, 'must be outside the document root');
        return $resolvedPath;
    }

    doctorCheck($checks, true, $label, 'effective path: ' . $resolvedPath);
    return $resolvedPath;
}

function doctorSourceWithoutComments(string $source): string
{
    $result = '';
    foreach (token_get_all($source) as $token) {
        if (is_array($token)) {
            if ($token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT) {
                continue;
            }
            $result .= $token[1];
            continue;
        }
        $result .= $token;
    }

    return $result;
}

function doctorMeaningfulTokens(string $source): array
{
    $tokens = [];
    foreach (token_get_all($source) as $token) {
        if (is_array($token) && in_array($token[0], [T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }
        $tokens[] = $token;
    }

    return $tokens;
}

function doctorTokenText($token): string
{
    return is_array($token) ? $token[1] : $token;
}

function doctorDecodePhpString(string $literal): ?string
{
    if (strlen($literal) < 2 || ($literal[0] !== "'" && $literal[0] !== '"') || substr($literal, -1) !== $literal[0]) {
        return null;
    }

    $quote = $literal[0];
    $content = substr($literal, 1, -1);
    if ($quote === '"') {
        return stripcslashes($content);
    }

    $decoded = '';
    for ($index = 0, $length = strlen($content); $index < $length; $index++) {
        if ($content[$index] === '\\' && $index + 1 < $length && ($content[$index + 1] === '\\' || $content[$index + 1] === "'")) {
            $decoded .= $content[++$index];
            continue;
        }
        $decoded .= $content[$index];
    }

    return $decoded;
}

function doctorFindAssignment(array $tokens, string $variable, string $key): ?array
{
    $tokenCount = count($tokens);
    for ($index = 0; $index + 5 < $tokenCount; $index++) {
        if (!is_array($tokens[$index]) || $tokens[$index][0] !== T_VARIABLE || $tokens[$index][1] !== $variable) {
            continue;
        }
        if (doctorTokenText($tokens[$index + 1]) !== '[' || !is_array($tokens[$index + 2]) || $tokens[$index + 2][0] !== T_CONSTANT_ENCAPSED_STRING) {
            continue;
        }
        if (doctorDecodePhpString($tokens[$index + 2][1]) !== $key || doctorTokenText($tokens[$index + 3]) !== ']' || doctorTokenText($tokens[$index + 4]) !== '=') {
            continue;
        }

        $expression = [];
        $depth = 0;
        for ($cursor = $index + 5; $cursor < $tokenCount; $cursor++) {
            $text = doctorTokenText($tokens[$cursor]);
            if ($text === ';' && $depth === 0) {
                return $expression;
            }
            if ($text === '(' || $text === '[' || $text === '{') {
                $depth++;
            } elseif ($text === ')' || $text === ']' || $text === '}') {
                $depth--;
            }
            $expression[] = $tokens[$cursor];
        }
    }

    return null;
}

function doctorEvaluateScalar(array $tokens): array
{
    $value = null;
    $expectValue = true;
    $concatenating = false;

    foreach ($tokens as $token) {
        $text = doctorTokenText($token);
        if ($text === '.') {
            if ($expectValue) {
                return [false, null];
            }
            $expectValue = true;
            $concatenating = true;
            continue;
        }
        if (!$expectValue) {
            return [false, null];
        }

        $nextValue = null;
        if (is_array($token) && $token[0] === T_CONSTANT_ENCAPSED_STRING) {
            $nextValue = doctorDecodePhpString($token[1]);
        } elseif (is_array($token) && $token[0] === T_LNUMBER) {
            $nextValue = (int) $token[1];
        } elseif (is_array($token) && $token[0] === T_STRING) {
            switch (strtoupper($token[1])) {
                case 'TRUE':
                    $nextValue = true;
                    break;
                case 'FALSE':
                    $nextValue = false;
                    break;
                case 'NULL':
                    $nextValue = null;
                    break;
                case 'APPPATH':
                    $nextValue = APPPATH;
                    break;
                case 'FCPATH':
                    $nextValue = FCPATH;
                    break;
                case 'DIRECTORY_SEPARATOR':
                    $nextValue = DIRECTORY_SEPARATOR;
                    break;
                default:
                    return [false, null];
            }
        } else {
            return [false, null];
        }

        if ($nextValue === null && !(is_array($token) && $token[0] === T_STRING && strtoupper($token[1]) === 'NULL')) {
            return [false, null];
        }
        if ($value !== null || $concatenating) {
            if (!is_string($value) || !is_string($nextValue)) {
                return [false, null];
            }
            $value .= $nextValue;
        } else {
            $value = $nextValue;
        }
        $expectValue = false;
    }

    return $expectValue ? [false, null] : [true, $value];
}

function doctorReadConfigValues(string $source, array $keys): array
{
    $tokens = doctorMeaningfulTokens($source);
    $values = [];
    foreach ($keys as $key) {
        $assignment = doctorFindAssignment($tokens, '$config', $key);
        if ($assignment === null) {
            continue;
        }
        [$valid, $value] = doctorEvaluateScalar($assignment);
        if ($valid) {
            $values[$key] = $value;
        }
    }

    return $values;
}

function doctorReadDatabaseSettings(string $source): array
{
    $settings = [];
    foreach (['hostname', 'username', 'password', 'database', 'dbdriver'] as $key) {
        $valuePattern = '(\'(?:\\\\.|[^\'])*\'|"(?:\\\\.|[^"])*"|TRUE|FALSE|NULL|[0-9]+)';
        $pattern = '~[\'\"]' . preg_quote($key, '~') . '[\'\"]\s*=>\s*' . $valuePattern . '~is';
        if (preg_match($pattern, $source, $matches) !== 1) {
            continue;
        }
        [$valid, $value] = doctorEvaluateScalar(doctorMeaningfulTokens('<?php ' . $matches[1]));
        if ($valid) {
            $settings[$key] = $value;
        }
    }

    return $settings;
}

function doctorSourceHasPlaceholder(string $source): bool
{
    $source = strtoupper(doctorSourceWithoutComments($source));
    foreach (['GANTI', 'CHANGE_ME', 'CHANGE-ME', 'REPLACE', 'YOUR_', 'EXAMPLE.INVALID', 'AMI_DEFAULT_DEV_ENCRYPTION_KEY'] as $marker) {
        if (strpos($source, $marker) !== false) {
            return true;
        }
    }

    return false;
}

function doctorFrontControllerIsProduction(string $frontControllerPath): bool
{
    $source = @file_get_contents($frontControllerPath);
    if ($source === false) {
        return false;
    }

    return preg_match(
        '~define\s*\(\s*[\'\"]ENVIRONMENT[\'\"]\s*,\s*[\'\"]production[\'\"]\s*\)~',
        doctorSourceWithoutComments($source)
    ) === 1;
}

function doctorDatabaseSettingsAreComplete(array $databaseSettings): bool
{
    foreach (['hostname', 'username', 'password', 'database'] as $key) {
        $value = doctorStringValue($databaseSettings, $key);
        if ($value === null || $value === '' || doctorContainsPlaceholder($value)) {
            return false;
        }
    }

    return doctorStringValue($databaseSettings, 'dbdriver') === 'mysqli';
}

function doctorDatabaseTarget(string $configuredHostname): array
{
    $hostname = $configuredHostname;
    $port = null;
    $socket = null;

    if (preg_match('/^\[([^\]]+)\]:(\d+)$/', $configuredHostname, $matches) === 1) {
        return [$matches[1], (int) $matches[2], null];
    }

    if (preg_match('/^([^:]+):(\d+)$/', $configuredHostname, $matches) === 1) {
        return [$matches[1], (int) $matches[2], null];
    }

    if (strpos($configuredHostname, 'localhost:') === 0) {
        $socket = substr($configuredHostname, strlen('localhost:'));
        $hostname = 'localhost';
    }

    return [$hostname, $port, $socket];
}

function doctorCheckDatabase(array &$checks, array $databaseSettings): void
{
    if (!doctorDatabaseSettingsAreComplete($databaseSettings)) {
        doctorNote('Database SELECT 1 skipped because database configuration is incomplete or still has placeholders.');
        return;
    }

    if (!extension_loaded('mysqli')) {
        doctorCheck($checks, false, 'database SELECT 1', 'mysqli is unavailable');
        return;
    }

    $connection = null;
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        [$hostname, $port, $socket] = doctorDatabaseTarget((string) $databaseSettings['hostname']);
        $connection = mysqli_init();
        if ($connection === false) {
            throw new RuntimeException('mysqli initialization failed');
        }

        mysqli_options($connection, MYSQLI_OPT_CONNECT_TIMEOUT, 5);
        mysqli_real_connect(
            $connection,
            $hostname,
            (string) $databaseSettings['username'],
            (string) $databaseSettings['password'],
            (string) $databaseSettings['database'],
            $port,
            $socket
        );
        $queryResult = $connection->query('SELECT 1');
        $row = $queryResult->fetch_row();
        if (!is_array($row) || (int) ($row[0] ?? 0) !== 1) {
            throw new RuntimeException('unexpected SELECT 1 result');
        }
        $queryResult->free();
        doctorCheck($checks, true, 'database SELECT 1', 'read-only query succeeded');
    } catch (Throwable $exception) {
        doctorCheck($checks, false, 'database SELECT 1', 'connection or read-only query failed');
    } finally {
        if ($connection instanceof mysqli) {
            $connection->close();
        }
        mysqli_report(MYSQLI_REPORT_OFF);
    }
}

$checks = [];

doctorCheck($checks, true, 'CLI execution');
doctorCheck($checks, version_compare(PHP_VERSION, '7.4.0', '>='), 'PHP version', PHP_VERSION);

foreach (['mysqli', 'mbstring', 'xml', 'zip', 'gd', 'curl', 'fileinfo', 'openssl'] as $extension) {
    doctorCheck($checks, extension_loaded($extension), 'PHP extension: ' . $extension);
}

$frontControllerPath = $repositoryRoot . DIRECTORY_SEPARATOR . 'index.php';
doctorCheck(
    $checks,
    is_file($frontControllerPath) && doctorFrontControllerIsProduction($frontControllerPath),
    'front controller selects production environment'
);

$vendorAutoloadPath = $repositoryRoot . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$vendorAutoloadSource = is_readable($vendorAutoloadPath) ? file_get_contents($vendorAutoloadPath) : false;
doctorCheck(
    $checks,
    is_file($vendorAutoloadPath)
        && $vendorAutoloadSource !== false
        && strpos($vendorAutoloadSource, 'ComposerAutoloaderInit') !== false,
    'vendor autoload is present and readable'
);

$productionDirectory = $repositoryRoot . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'production';
$expectedConfigFiles = ['ami.php', 'config.php', 'database.php'];
$actualConfigFiles = glob($productionDirectory . DIRECTORY_SEPARATOR . '*.php') ?: [];
$actualConfigFiles = array_map('basename', $actualConfigFiles);
sort($actualConfigFiles);
doctorCheck(
    $checks,
    $actualConfigFiles === $expectedConfigFiles,
    'exactly three production config files',
    'expected ami.php, config.php, database.php'
);

$configurationSources = [];
foreach ($expectedConfigFiles as $configFile) {
    $configPath = $productionDirectory . DIRECTORY_SEPARATOR . $configFile;
    if (!is_file($configPath) || !is_readable($configPath)) {
        doctorCheck($checks, false, 'production config: ' . $configFile, 'missing or unreadable');
        continue;
    }

    $source = file_get_contents($configPath);
    if ($source === false) {
        doctorCheck($checks, false, 'production config: ' . $configFile, 'could not be read');
        continue;
    }
    $configurationSources[$configFile] = $source;
    doctorCheck($checks, true, 'production config: ' . $configFile, 'readable without executing it');
}

$configSource = $configurationSources['config.php'] ?? '';
$amiSource = $configurationSources['ami.php'] ?? '';
$databaseSource = $configurationSources['database.php'] ?? '';
$configValues = $configSource === ''
    ? []
    : doctorReadConfigValues($configSource, ['base_url', 'encryption_key', 'cookie_secure', 'log_threshold', 'log_path', 'sess_save_path']);
$amiValues = $amiSource === ''
    ? []
    : doctorReadConfigValues($amiSource, ['private_storage_path', 'public_uploads_path']);
$databaseSettings = $databaseSource === '' ? [] : doctorReadDatabaseSettings($databaseSource);

if ($configSource !== '') {
    doctorCheck($checks, !doctorSourceHasPlaceholder($configSource), 'config.php has no unresolved placeholder values');
    $baseUrl = doctorStringValue($configValues, 'base_url');
    $baseUrlValid = $baseUrl !== null
        && !doctorContainsPlaceholder($baseUrl)
        && filter_var($baseUrl, FILTER_VALIDATE_URL) !== false
        && strpos($baseUrl, 'https://') === 0;
    doctorCheck($checks, $baseUrlValid, 'base URL is HTTPS and not a placeholder');

    $encryptionKey = doctorStringValue($configValues, 'encryption_key');
    doctorCheck(
        $checks,
        $encryptionKey !== null && strlen($encryptionKey) >= 32 && !doctorContainsPlaceholder($encryptionKey),
        'encryption key is configured without revealing it'
    );
    doctorCheck($checks, ($configValues['cookie_secure'] ?? null) === true, 'secure cookies are enabled');
    doctorCheck($checks, (int) ($configValues['log_threshold'] ?? 0) > 0, 'log threshold is enabled');

    doctorCheckDirectory($checks, 'log directory', doctorStringValue($configValues, 'log_path'));
    doctorCheckDirectory($checks, 'session directory', doctorStringValue($configValues, 'sess_save_path'));
} else {
    doctorCheck($checks, false, 'config.php values', 'could not read configuration values');
}

if ($amiSource !== '') {
    doctorCheck($checks, !doctorSourceHasPlaceholder($amiSource), 'ami.php has no unresolved placeholder values');
    $privateStorageConfigured = doctorStringValue($amiValues, 'private_storage_path');
    $publicUploadsConfigured = doctorStringValue($amiValues, 'public_uploads_path');
    doctorCheck(
        $checks,
        $privateStorageConfigured !== null && !doctorContainsPlaceholder($privateStorageConfigured),
        'private storage setting is valid'
    );
    doctorCheck(
        $checks,
        $publicUploadsConfigured !== null && !doctorContainsPlaceholder($publicUploadsConfigured),
        'public uploads setting is valid'
    );

    $privateStorageRoot = $privateStorageConfigured === ''
        ? $repositoryRoot . DIRECTORY_SEPARATOR . 'uploads'
        : $privateStorageConfigured;
    $publicUploadsRoot = $publicUploadsConfigured === ''
        ? $repositoryRoot . DIRECTORY_SEPARATOR . 'uploads'
        : $publicUploadsConfigured;

    $privateStoragePath = doctorCheckDirectory($checks, 'private upload root', $privateStorageRoot);
    $publicUploadsPath = doctorCheckDirectory($checks, 'public upload root', $publicUploadsRoot);

    foreach ([
        'audit_evidence',
        'bukti_auditor',
        'instrumen',
        'penetapan',
        'ppepp_documents',
        'rtm_photos',
        'spmi_source',
        'tmp',
        'user_photos',
    ] as $category) {
        $categoryPath = $privateStoragePath === null
            ? null
            : $privateStoragePath . DIRECTORY_SEPARATOR . $category;
        doctorCheckDirectory($checks, 'private upload category: ' . $category, $categoryPath);
    }

    $profilePath = $publicUploadsPath === null
        ? null
        : $publicUploadsPath . DIRECTORY_SEPARATOR . 'profil';
    doctorCheckDirectory($checks, 'public upload category: profil', $profilePath);
} else {
    doctorCheck($checks, false, 'ami.php values', 'could not read configuration values');
}

if ($databaseSettings === []) {
    doctorCheck($checks, false, 'database.php default connection settings', 'missing default mysqli settings');
} else {
    doctorCheck(
        $checks,
        !doctorSourceHasPlaceholder($databaseSource) && doctorDatabaseSettingsAreComplete($databaseSettings),
        'database.php has complete non-placeholder mysqli settings'
    );
    doctorCheckDatabase($checks, $databaseSettings);
}

doctorNote('Verify web-server private-path protection externally with the deployed Nginx or Apache configuration and an authenticated HTTP denial check.');

$failureCount = 0;
foreach ($checks as $check) {
    if (!$check['passed']) {
        $failureCount++;
    }
}

fwrite(STDOUT, 'SUMMARY: ' . ($failureCount === 0 ? 'all mandatory checks passed' : $failureCount . ' mandatory check(s) failed') . PHP_EOL);
exit($failureCount === 0 ? 0 : 1);
