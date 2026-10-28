<?php
declare(strict_types=1);

function native_runtime_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, 'FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

function native_runtime_remove_directory(string $directory): void
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
$GLOBALS['config'] = [];

function config_item($key)
{
    return array_key_exists($key, $GLOBALS['config']) ? $GLOBALS['config'][$key] : NULL;
}

require $repositoryRoot . '/application/helpers/app_helper.php';

$helperSource = (string) file_get_contents($repositoryRoot . '/application/helpers/app_helper.php');
$configSource = (string) file_get_contents($repositoryRoot . '/application/config/config.php');
$indexSource = (string) file_get_contents($repositoryRoot . '/index.php');
$ppeppSource = (string) file_get_contents($repositoryRoot . '/application/services/Spmi_ppepp_documents_service.php');
$nginxSource = (string) file_get_contents($repositoryRoot . '/deploy/nginx/ami.conf');
$fpmSource = (string) file_get_contents($repositoryRoot . '/deploy/php-fpm/ami.conf');

native_runtime_assert(strpos($indexSource, "define('ENVIRONMENT', 'production')") !== false, 'index fixes ENVIRONMENT to production');
native_runtime_assert(strpos($indexSource, 'CI_ENV') === false, 'index does not use a server environment selector');
native_runtime_assert(strpos($configSource, "config_item('base_url')") === false, 'base URL setup does not recurse through config_item');
foreach (['HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR', 'HTTP_X_FORWARDED_PROTO'] as $requestValue) {
    native_runtime_assert(strpos($configSource, $requestValue) === false, 'base URL does not inspect ' . $requestValue);
}
native_runtime_assert(strpos($configSource, "\$config['base_url'] = isset(\$config['base_url'])") !== false, 'base URL keeps direct config-array initialization');
native_runtime_assert(strpos($helperSource, 'sys_get_temp_dir(') === false, 'storage helper has no temporary-directory fallback');
native_runtime_assert(strpos($helperSource, 'ami-private-storage') === false, 'storage helper has no legacy temporary root');
native_runtime_assert(strpos($nginxSource, 'CI_ENV') === false, 'Nginx does not inject CI_ENV');
native_runtime_assert(strpos($fpmSource, 'CI_ENV') === false && strpos($fpmSource, 'APP_PRODUCTION_CONFIG_FILE') === false, 'PHP-FPM does not inject application configuration');
native_runtime_assert(strpos($nginxSource, 'client_max_body_size 12M;') !== false, 'Nginx allows multipart overhead above the 10 MiB application limit');
native_runtime_assert(strpos($fpmSource, 'upload_max_filesize] = 11M') !== false && strpos($fpmSource, 'post_max_size] = 12M') !== false, 'PHP upload limits match the native 10 MiB application cap');

$GLOBALS['config'] = [
    'private_storage_path' => FCPATH . 'uploads/',
    'public_uploads_path' => FCPATH . 'uploads/',
];

foreach (app_private_storage_categories() as $category) {
    $expected = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR;
    native_runtime_assert(private_storage_dir($category) === $expected, 'native private category resolves: ' . $category);
    native_runtime_assert(is_dir($expected), 'native private category exists: ' . $category);
}

$nativePublic = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'profil' . DIRECTORY_SEPARATOR;
native_runtime_assert(public_uploads_dir('profil') === $nativePublic, 'native public profile root resolves');
native_runtime_assert(is_dir($nativePublic), 'native public profile directory exists');

$fixtureRoot = sys_get_temp_dir() . '/ami-native-storage-regression-' . bin2hex(random_bytes(8));
register_shutdown_function(static function () use ($fixtureRoot): void {
    native_runtime_remove_directory($fixtureRoot);
});

$GLOBALS['config']['private_storage_path'] = $fixtureRoot . '/external-private/';
$externalPpepp = private_storage_dir('ppepp_documents');
native_runtime_assert($externalPpepp === $fixtureRoot . '/external-private/ppepp_documents/', 'alternate private root is honored');
native_runtime_assert(is_dir($externalPpepp), 'alternate private PPEPP directory is prepared');
native_runtime_assert(public_uploads_dir('profil') === $nativePublic, 'alternate private root does not change public root');

$GLOBALS['config']['private_storage_path'] = '';
try {
    private_storage_dir('ppepp_documents');
    native_runtime_assert(false, 'missing private root is rejected');
} catch (RuntimeException $exception) {
    native_runtime_assert(strpos($exception->getMessage(), 'root') !== false, 'missing private root has a diagnostic');
}

$GLOBALS['config']['public_uploads_path'] = '';
try {
    public_uploads_dir('profil');
    native_runtime_assert(false, 'missing public root is rejected');
} catch (RuntimeException $exception) {
    native_runtime_assert(strpos($exception->getMessage(), 'root') !== false, 'missing public root has a diagnostic');
}

try {
    private_storage_dir('not_a_category');
    native_runtime_assert(false, 'invalid private category is rejected');
} catch (InvalidArgumentException $exception) {
    native_runtime_assert(true, 'invalid private category diagnostic');
}

try {
    public_uploads_dir('ppepp_documents');
    native_runtime_assert(false, 'non-profil public category is rejected');
} catch (InvalidArgumentException $exception) {
    native_runtime_assert(true, 'non-profil public category diagnostic');
}

native_runtime_assert(strpos($ppeppSource, "private_storage_dir('ppepp_documents')") !== false, 'PPEPP writes through the central private resolver');
native_runtime_assert(strpos($ppeppSource, "private_storage_path('ppepp_documents', \$document->stored_name)") !== false, 'PPEPP reads through the central private resolver');
foreach (['prepare_directory', 'random_name', 'move_uploaded_file'] as $reason) {
    native_runtime_assert(strpos($ppeppSource, "log_upload_failure('" . $reason . "')") !== false, 'PPEPP logs safe upload reason: ' . $reason);
}
native_runtime_assert(strpos($ppeppSource, 'File dokumen PPEPP gagal disimpan.') !== false, 'PPEPP retains the generic user-facing storage error');

fwrite(STDOUT, "PASS: native runtime and storage regression assertions\n");
