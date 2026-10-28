<?php
declare(strict_types=1);

function native_guard_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, 'FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

function native_guard_remove_directory(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $entry) {
        $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
    }
    rmdir($directory);
}

$repositoryRoot = dirname(__DIR__);
define('BASEPATH', $repositoryRoot . '/system/');
define('FCPATH', $repositoryRoot . DIRECTORY_SEPARATOR);
define('APPPATH', $repositoryRoot . '/application/');
define('ENVIRONMENT', 'production');
$GLOBALS['config'] = [];

function config_item($key)
{
    return array_key_exists($key, $GLOBALS['config']) ? $GLOBALS['config'][$key] : NULL;
}

require $repositoryRoot . '/application/helpers/app_helper.php';
require $repositoryRoot . '/application/hooks/Production_config_guard.php';

$fixtureRoot = sys_get_temp_dir() . '/ami-native-guard-regression-' . bin2hex(random_bytes(8));
register_shutdown_function(static function () use ($fixtureRoot): void {
    native_guard_remove_directory($fixtureRoot);
});

foreach (['logs', 'sessions', 'project-logs', 'project-sessions', 'external-private', 'external-public'] as $directory) {
    $path = $fixtureRoot . '/' . $directory;
    if (!mkdir($path, 0775, true) && !is_dir($path)) {
        throw new RuntimeException('Unable to create guard fixture directory.');
    }
}

$nativePreset = [
    'base_url' => 'https://ami.example.test/',
    'encryption_key' => str_repeat('a', 32),
    'cookie_secure' => TRUE,
    'log_threshold' => 1,
    'log_path' => $fixtureRoot . '/project-logs/',
    'sess_save_path' => $fixtureRoot . '/project-sessions',
    'private_storage_path' => FCPATH . 'uploads/',
    'public_uploads_path' => FCPATH . 'uploads/',
];
$GLOBALS['config'] = $nativePreset;
native_guard_assert(Production_config_guard::failed_checks() === [], 'guard accepts the official in-project uploads, logs, and sessions preset');
(new Production_config_guard())->validate();

$externalPreset = $nativePreset;
$externalPreset['private_storage_path'] = $fixtureRoot . '/external-private/';
$externalPreset['public_uploads_path'] = $fixtureRoot . '/external-public/';
$externalPreset['log_path'] = $fixtureRoot . '/logs/';
$externalPreset['sess_save_path'] = $fixtureRoot . '/sessions/';
$GLOBALS['config'] = $externalPreset;
native_guard_assert(Production_config_guard::failed_checks() === [], 'guard accepts a single-value external private root override');

$GLOBALS['config'] = $nativePreset;
$GLOBALS['config']['base_url'] = 'https://GANTI-DOMAIN.example/';
$missingBaseUrl = Production_config_guard::failed_checks();
native_guard_assert(in_array('base_url', $missingBaseUrl, true), 'guard names a placeholder base URL failure');

$GLOBALS['config'] = $nativePreset;
$GLOBALS['config']['private_storage_path'] = '';
$missingPrivateRoot = Production_config_guard::failed_checks();
native_guard_assert(in_array('private_storage_path', $missingPrivateRoot, true), 'guard names a missing private root failure');

$childPath = $fixtureRoot . '/guard-failure-child.php';
$stdoutPath = $fixtureRoot . '/guard-failure.stdout';
$stderrPath = $fixtureRoot . '/guard-failure.stderr';
$childConfig = $externalPreset;
$childConfig['base_url'] = '';
$childConfig['encryption_key'] = 'DO_NOT_PRINT_NATIVE_GUARD_SECRET_123';
$childSource = "<?php\n"
    . "define('BASEPATH', " . var_export($repositoryRoot . '/system/', true) . ");\n"
    . "define('FCPATH', " . var_export($fixtureRoot . '/', true) . ");\n"
    . "define('APPPATH', " . var_export($repositoryRoot . '/application/', true) . ");\n"
    . "define('ENVIRONMENT', 'production');\n"
    . "\$GLOBALS['config'] = " . var_export($childConfig, true) . ";\n"
    . "function config_item(\$key) { return array_key_exists(\$key, \$GLOBALS['config']) ? \$GLOBALS['config'][\$key] : NULL; }\n"
    . "require " . var_export($repositoryRoot . '/application/helpers/app_helper.php', true) . ";\n"
    . "require " . var_export($repositoryRoot . '/application/hooks/Production_config_guard.php', true) . ";\n"
    . "(new Production_config_guard())->validate();\n";

if (file_put_contents($childPath, $childSource) === false) {
    throw new RuntimeException('Unable to write guard failure child process.');
}

$command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($childPath)
    . ' >' . escapeshellarg($stdoutPath)
    . ' 2>' . escapeshellarg($stderrPath);
$ignoredOutput = [];
$exitCode = 0;
exec($command, $ignoredOutput, $exitCode);
$stdout = (string) file_get_contents($stdoutPath);
$stderr = (string) file_get_contents($stderrPath);
$combined = $stdout . $stderr;

native_guard_assert($exitCode === 1, 'guard failure exits nonzero');
native_guard_assert(trim($stdout) === 'Production configuration is incomplete. Please check application/config/production/ configuration files.', 'guard keeps the user-facing 503 response generic');
native_guard_assert(strpos($combined, 'base_url') !== false, 'guard logs the failed check name');
native_guard_assert(strpos($combined, 'DO_NOT_PRINT_NATIVE_GUARD_SECRET_123') === false, 'guard diagnostics never log the encryption key');
native_guard_assert(strpos($combined, $fixtureRoot . '/external-private/') === false, 'guard diagnostics never log storage paths');

fwrite(STDOUT, "PASS: native production guard regression assertions\n");
