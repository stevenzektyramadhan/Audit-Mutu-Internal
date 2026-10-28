<?php
declare(strict_types=1);

function regressionAssert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, 'FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

function regressionWrite(string $path, string $contents): void
{
    $directory = dirname($path);
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Unable to create regression fixture directory.');
    }
    if (file_put_contents($path, $contents) === false) {
        throw new RuntimeException('Unable to write regression fixture.');
    }
}

function regressionSnapshot(string $directory): string
{
    $entries = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($iterator as $entry) {
        if ($entry->isFile()) {
            $entries[] = substr($entry->getPathname(), strlen($directory) + 1) . ':' . $entry->getSize() . ':' . $entry->getMTime();
        }
    }
    sort($entries);
    return hash('sha256', implode("\n", $entries));
}

function regressionRemoveDirectory(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $entry) {
        if ($entry->isDir()) {
            rmdir($entry->getPathname());
        } else {
            unlink($entry->getPathname());
        }
    }
    rmdir($directory);
}

$repositoryRoot = dirname(__DIR__);
$doctorPath = $repositoryRoot . '/scripts/ami_doctor.php';
$builderPath = $repositoryRoot . '/scripts/build_native_zip.sh';
$doctorSource = file_get_contents($doctorPath);
$builderSource = file_get_contents($builderPath);
$ignoreSource = file_get_contents($repositoryRoot . '/.gitignore');

regressionAssert($doctorSource !== false, 'doctor source is readable');
regressionAssert($builderSource !== false, 'builder source is readable');
regressionAssert($ignoreSource !== false, 'ignore rules are readable');

foreach ([
    "PHP_SAPI !== 'cli'",
    'SELECT 1',
    'vendor autoload is present and readable',
    'exactly three production config files',
    'Verify web-server private-path protection externally',
] as $requiredDoctorFragment) {
    regressionAssert(strpos($doctorSource, $requiredDoctorFragment) !== false, 'doctor contract is present: ' . $requiredDoctorFragment);
}
foreach (['file_put_contents(', 'mkdir(', 'unlink(', 'rename(', 'copy('] as $forbiddenDoctorFragment) {
    regressionAssert(strpos($doctorSource, $forbiddenDoctorFragment) === false, 'doctor has no file-write primitive: ' . $forbiddenDoctorFragment);
}

foreach ([
    'AMI_PRODUCTION_VENDOR_DIR',
    'composer.lock',
    'production vendor was installed with development dependencies',
    'PACKAGE-METADATA.json',
    'PACKAGE-MANIFEST.sha256',
    'database_init.sql',
    'uploads/$category/.gitkeep',
    'AMI/',
] as $requiredBuilderFragment) {
    regressionAssert(strpos($builderSource, $requiredBuilderFragment) !== false, 'builder contract is present: ' . $requiredBuilderFragment);
}
regressionAssert(strpos($ignoreSource, '/dist/') !== false, 'dist output is ignored');
regressionAssert(strpos($ignoreSource, '/.ami-native-package.*/') !== false, 'native package staging is ignored');

$fixtureRoot = sys_get_temp_dir() . '/ami-doctor-regression-' . bin2hex(random_bytes(8));
$builderFixtureRoot = sys_get_temp_dir() . '/ami-builder-regression-' . bin2hex(random_bytes(8));
$packageVersion = 'native-regression-' . bin2hex(random_bytes(4));
$packagePath = $repositoryRoot . '/dist/AMI-NATIVE-ALMALINUX-' . $packageVersion . '.zip';
register_shutdown_function(static function () use ($fixtureRoot): void {
    regressionRemoveDirectory($fixtureRoot);
});
register_shutdown_function(static function () use ($builderFixtureRoot): void {
    regressionRemoveDirectory($builderFixtureRoot);
});
register_shutdown_function(static function () use ($packagePath): void {
    foreach ([$packagePath, $packagePath . '.sha256', $packagePath . '.manifest.sha256', $packagePath . '.metadata.json'] as $path) {
        if (is_file($path)) {
            unlink($path);
        }
    }
});

try {
    foreach ([
        'application/config/production',
        'application/logs',
        'application/cache/sessions',
        'uploads',
        'vendor',
        'scripts',
    ] as $relativeDirectory) {
        $path = $fixtureRoot . '/' . $relativeDirectory;
        if (!mkdir($path, 0755, true) && !is_dir($path)) {
            throw new RuntimeException('Unable to create regression fixture path.');
        }
    }
    foreach ([
        'audit_evidence',
        'bukti_auditor',
        'instrumen',
        'penetapan',
        'ppepp_documents',
        'profil',
        'rtm_photos',
        'spmi_source',
        'tmp',
        'user_photos',
    ] as $category) {
        if (!mkdir($fixtureRoot . '/uploads/' . $category, 0755, true) && !is_dir($fixtureRoot . '/uploads/' . $category)) {
            throw new RuntimeException('Unable to create upload fixture path.');
        }
    }

    regressionWrite($fixtureRoot . '/index.php', "<?php\ndefine('ENVIRONMENT', 'production');\n");
    regressionWrite($fixtureRoot . '/vendor/autoload.php', "<?php\nclass ComposerAutoloaderInitRegression {}\n");
    regressionWrite(
        $fixtureRoot . '/application/config/production/config.php',
        "<?php\ndefined('BASEPATH') OR exit('No direct script access allowed');\n"
        . "\$config['base_url'] = 'https://example.org/';\n"
        . "\$config['encryption_key'] = '0123456789abcdef0123456789abcdef';\n"
        . "\$config['cookie_secure'] = TRUE;\n"
        . "\$config['log_threshold'] = 1;\n"
        . "\$config['log_path'] = " . var_export($fixtureRoot . '/application/logs', true) . ";\n"
        . "\$config['sess_save_path'] = " . var_export($fixtureRoot . '/application/cache/sessions', true) . ";\n"
        . 'file_put_contents(' . var_export($fixtureRoot . '/config-must-not-run', true) . ", 'must-not-exist');\n"
    );
    regressionWrite(
        $fixtureRoot . '/application/config/production/ami.php',
        "<?php\ndefined('BASEPATH') OR exit('No direct script access allowed');\n"
        . "\$config['private_storage_path'] = " . var_export($fixtureRoot . '/uploads', true) . ";\n"
        . "\$config['public_uploads_path'] = " . var_export($fixtureRoot . '/uploads', true) . ";\n"
    );
    regressionWrite(
        $fixtureRoot . '/application/config/production/database.php',
        "<?php\ndefined('BASEPATH') OR exit('No direct script access allowed');\n"
        . "\$db['default'] = array('hostname' => '127.0.0.1:1', 'username' => 'doctor_regression_user', 'password' => 'DO_NOT_PRINT_REGRESSION_SECRET', 'database' => 'doctor_regression_database', 'dbdriver' => 'mysqli');\n"
    );
    copy($doctorPath, $fixtureRoot . '/scripts/ami_doctor.php');

    $before = regressionSnapshot($fixtureRoot);
    $output = [];
    $exitCode = 0;
    exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($fixtureRoot . '/scripts/ami_doctor.php') . ' 2>&1', $output, $exitCode);
    $after = regressionSnapshot($fixtureRoot);
    $combinedOutput = implode("\n", $output);

    regressionAssert($exitCode !== 0, 'doctor rejects the unavailable fixture database or local prerequisite gap');
    regressionAssert(strpos($combinedOutput, 'DO_NOT_PRINT_REGRESSION_SECRET') === false, 'doctor never prints database password values');
    regressionAssert(strpos($combinedOutput, 'PASS: CLI execution') !== false, 'doctor executes against a self-contained fixture');
    regressionAssert(strpos($combinedOutput, 'NOTE: Verify web-server private-path protection externally') !== false, 'doctor includes the external web-server reminder');
    regressionAssert($before === $after, 'doctor leaves the fixture unchanged');
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL: regression fixture setup failed' . PHP_EOL);
    exit(1);
}

try {
    foreach ([
        'vendor/composer',
        'vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet',
        'vendor/google/apiclient/src',
    ] as $relativeDirectory) {
        $path = $builderFixtureRoot . '/' . $relativeDirectory;
        if (!mkdir($path, 0755, true) && !is_dir($path)) {
            throw new RuntimeException('Unable to create builder fixture path.');
        }
    }
    $lock = json_decode((string) file_get_contents($repositoryRoot . '/composer.lock'), true, 512, JSON_THROW_ON_ERROR);
    $versions = [
        'codeigniter/framework' => [
            'dev_requirement' => false,
            'reference' => 'fixture-root',
        ],
    ];
    foreach ($lock['packages'] as $package) {
        $versions[$package['name']] = [
            'dev_requirement' => false,
            'reference' => $package['dist']['reference'] ?? ($package['source']['reference'] ?? ''),
        ];
    }
    regressionWrite(
        $builderFixtureRoot . '/vendor/composer/installed.php',
        "<?php return " . var_export([
            'root' => ['name' => 'codeigniter/framework', 'dev' => false],
            'versions' => $versions,
        ], true) . ";\n"
    );
    regressionWrite(
        $builderFixtureRoot . '/vendor/composer/installed.json',
        json_encode(['packages' => [], 'dev' => false, 'dev-package-names' => []], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n"
    );
    regressionWrite(
        $builderFixtureRoot . '/vendor/autoload.php',
        "<?php\nspl_autoload_register(static function (\$class): void {\n"
        . "    \$map = ['PhpOffice\\\\PhpSpreadsheet\\\\Spreadsheet' => " . var_export($builderFixtureRoot . '/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php', true) . ", 'Google\\\\Client' => " . var_export($builderFixtureRoot . '/vendor/google/apiclient/src/Client.php', true) . "];\n"
        . "    if (isset(\$map[\$class])) { require \$map[\$class]; }\n"
        . "});\n"
    );
    regressionWrite($builderFixtureRoot . '/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php', "<?php\nnamespace PhpOffice\\PhpSpreadsheet; class Spreadsheet {}\n");
    regressionWrite($builderFixtureRoot . '/vendor/google/apiclient/src/Client.php', "<?php\nnamespace Google; class Client {}\n");

    $output = [];
    $exitCode = 0;
    exec(
        'cd ' . escapeshellarg($repositoryRoot)
        . ' && AMI_PRODUCTION_VENDOR_DIR=' . escapeshellarg($builderFixtureRoot . '/vendor')
        . ' bash scripts/build_native_zip.sh ' . escapeshellarg($packageVersion) . ' 2>&1',
        $output,
        $exitCode
    );
    $combinedOutput = implode("\n", $output);
    regressionAssert($exitCode === 0, 'builder creates an archive from a production-only fixture vendor: ' . $combinedOutput);
    regressionAssert(is_file($packagePath), 'builder creates the ZIP artifact');
    regressionAssert(is_file($packagePath . '.sha256'), 'builder creates the SHA-256 sidecar');
    regressionAssert(is_file($packagePath . '.manifest.sha256'), 'builder creates the manifest sidecar');
    regressionAssert(is_file($packagePath . '.metadata.json'), 'builder creates the metadata sidecar');

    $archive = new ZipArchive();
    regressionAssert($archive->open($packagePath) === true, 'builder ZIP opens for inspection');
    $entries = [];
    for ($index = 0; $index < $archive->numFiles; $index++) {
        $entry = $archive->getNameIndex($index);
        regressionAssert(strpos($entry, 'AMI/') === 0, 'ZIP has only the AMI root');
        $entries[$entry] = true;
    }
    foreach ([
        'AMI/index.php',
        'AMI/vendor/autoload.php',
        'AMI/database_schema.sql',
        'AMI/scripts/bootstrap_super_admin.php',
        'AMI/scripts/ami_doctor.php',
        'AMI/deploy/nginx/ami.conf',
        'AMI/deploy/php-fpm/ami.conf',
        'AMI/docs/DEPLOY_NATIVE_ALMALINUX_ZIP.md',
        'AMI/docs/KONFIGURASI_IT_RINGKAS.md',
        'AMI/application/config/production/config.php',
        'AMI/application/config/production/database.php',
        'AMI/application/config/production/ami.php',
        'AMI/PACKAGE-METADATA.json',
        'AMI/PACKAGE-MANIFEST.sha256',
    ] as $requiredEntry) {
        regressionAssert(isset($entries[$requiredEntry]), 'ZIP includes required entry: ' . $requiredEntry);
    }
    foreach ([
        'AMI/database_init.sql',
        'AMI/composer.lock',
        'AMI/composer.json',
        'AMI/package.json',
        'AMI/PROMPT_OPENCODE_AMI_NATIVE_ALMALINUX_ZIP.md',
        'AMI/application/config/production/config.php.example',
        'AMI/application/config/production/database.php.example',
        'AMI/application/config/production/ami.php.example',
    ] as $forbiddenEntry) {
        regressionAssert(!isset($entries[$forbiddenEntry]), 'ZIP excludes forbidden entry: ' . $forbiddenEntry);
    }
    foreach (array_keys($entries) as $entry) {
        regressionAssert(strpos($entry, '/node_modules/') === false, 'ZIP excludes node_modules');
        regressionAssert(strpos($entry, '/tests/') === false, 'ZIP excludes tests');
        regressionAssert(strpos($entry, '/.git/') === false, 'ZIP excludes Git metadata');
        if (strpos($entry, 'AMI/uploads/') === 0) {
            regressionAssert(
                preg_match('~^AMI/uploads/(?:\.htaccess|(?:audit_evidence|bukti_auditor|instrumen|penetapan|ppepp_documents|profil|rtm_photos|spmi_source|tmp|user_photos)/(?:\.gitkeep|\.htaccess))$~', $entry) === 1,
                'ZIP contains only upload skeleton files'
            );
        }
    }
    foreach (['config', 'database', 'ami'] as $configurationName) {
        regressionAssert(
            $archive->getFromName('AMI/application/config/production/' . $configurationName . '.php')
                === file_get_contents($repositoryRoot . '/application/config/production/' . $configurationName . '.php.example'),
            'ZIP config is materialized directly from the tracked ' . $configurationName . ' template'
        );
    }
    $archiveManifest = $archive->getFromName('AMI/PACKAGE-MANIFEST.sha256');
    $archiveMetadata = $archive->getFromName('AMI/PACKAGE-METADATA.json');
    $archive->close();
    regressionAssert($archiveManifest === file_get_contents($packagePath . '.manifest.sha256'), 'manifest sidecar matches the ZIP manifest');
    regressionAssert($archiveMetadata === file_get_contents($packagePath . '.metadata.json'), 'metadata sidecar matches ZIP metadata');

    $checksum = trim((string) file_get_contents($packagePath . '.sha256'));
    regressionAssert(strpos($checksum, hash_file('sha256', $packagePath)) === 0, 'SHA-256 sidecar matches the ZIP');
    $metadata = json_decode((string) $archiveMetadata, true, 512, JSON_THROW_ON_ERROR);
    regressionAssert($metadata['version'] === $packageVersion, 'metadata records the package version');
    regressionAssert($metadata['composer_lock_sha256'] === hash_file('sha256', $repositoryRoot . '/composer.lock'), 'metadata records composer.lock provenance');
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL: builder regression fixture setup failed' . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "PASS: native handoff regression assertions\n");
