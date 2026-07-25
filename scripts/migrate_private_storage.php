#!/usr/bin/env php
<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only.\n");
    exit(1);
}

$root = dirname(__DIR__);
$configured = getenv('APP_PRIVATE_STORAGE_PATH');
$destination_root = $configured !== FALSE ? trim((string) $configured) : '';
$apply = in_array('--apply', $argv, TRUE);

if ($destination_root === '') {
    fwrite(STDERR, "APP_PRIVATE_STORAGE_PATH must point to an existing directory outside the web root.\n");
    exit(1);
}

$web_root = realpath($root);
$destination_real = realpath($destination_root);
if ($web_root === FALSE || $destination_real === FALSE || !is_dir($destination_real) || !is_writable($destination_real)) {
    fwrite(STDERR, "The configured private storage directory is missing or not writable.\n");
    exit(1);
}

$normalize = function ($path) {
    return strtolower(rtrim(str_replace('\\', '/', (string) $path), '/') . '/');
};
if (strpos($normalize($destination_real), $normalize($web_root)) === 0) {
    fwrite(STDERR, "Private storage must be outside the web root.\n");
    exit(1);
}

$categories = ['instrumen', 'penetapan', 'bukti_auditor'];
$planned = 0;
$migrated = 0;

foreach ($categories as $category) {
    $source = $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $category;
    if (!is_dir($source)) {
        continue;
    }

    $destination = $destination_real . DIRECTORY_SEPARATOR . $category;
    if ($apply && !is_dir($destination) && !mkdir($destination, 0700, TRUE) && !is_dir($destination)) {
        fwrite(STDERR, "Unable to create destination for {$category}.\n");
        exit(1);
    }

    foreach (new DirectoryIterator($source) as $entry) {
        if ($entry->isDot() || !$entry->isFile() || $entry->getFilename()[0] === '.') {
            continue;
        }

        $name = $entry->getFilename();
        if (basename($name) !== $name) {
            fwrite(STDERR, "Unsafe legacy file name skipped.\n");
            continue;
        }

        $planned++;
        fwrite(STDOUT, ($apply ? 'MIGRATE ' : 'PLAN ') . $category . DIRECTORY_SEPARATOR . $name . PHP_EOL);
        if (!$apply) {
            continue;
        }

        $source_path = $entry->getPathname();
        $destination_path = $destination . DIRECTORY_SEPARATOR . $name;
        if (is_file($destination_path)) {
            if (!hash_equals((string) hash_file('sha256', $source_path), (string) hash_file('sha256', $destination_path))) {
                fwrite(STDERR, "Destination conflict for {$category}/{$name}.\n");
                exit(1);
            }
        } elseif (!copy($source_path, $destination_path)) {
            fwrite(STDERR, "Copy failed for {$category}/{$name}.\n");
            exit(1);
        }

        if (!hash_equals((string) hash_file('sha256', $source_path), (string) hash_file('sha256', $destination_path))) {
            fwrite(STDERR, "Checksum verification failed for {$category}/{$name}.\n");
            exit(1);
        }
        @chmod($destination_path, 0600);

        if (!unlink($source_path)) {
            fwrite(STDERR, "Source cleanup failed after verified copy for {$category}/{$name}.\n");
            exit(1);
        }
        $migrated++;
    }
}

if (!$apply) {
    fwrite(STDOUT, "Dry run complete. Planned files: {$planned}. Re-run with --apply after reviewing the list.\n");
    exit(0);
}

fwrite(STDOUT, "Private storage migration complete. Migrated files: {$migrated}.\n");
