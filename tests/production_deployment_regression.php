<?php

$root = dirname(__DIR__);

function deployment_source($root, $path)
{
    $source = file_get_contents($root . DIRECTORY_SEPARATOR . $path);
    if ($source === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }

    return $source;
}

function deployment_check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$schema = deployment_source($root, 'database_schema.sql');
$index = deployment_source($root, 'index.php');
$loader = deployment_source($root, 'application/config/runtime_config.php');
$fpm = deployment_source($root, 'deploy/php-fpm/ami.conf');
$nginx = deployment_source($root, 'deploy/nginx/ami.conf');
$bootstrap = deployment_source($root, 'scripts/bootstrap_super_admin.php');
$guide = deployment_source($root, 'docs/PANDUAN_DEPLOY_PRODUCTION_ALMALINUX.md');
$guide_text = deployment_source($root, 'docs/PANDUAN_DEPLOY_PRODUCTION_ALMALINUX.txt');

deployment_check(strpos($schema, 'CREATE DATABASE') === FALSE && strpos($schema, "\nUSE `") === FALSE, 'Schema bootstrap tidak boleh membuat atau memilih database target.');
deployment_check(strpos($schema, 'mariadb --database=ami') !== FALSE, 'Schema harus mendokumentasikan database target dipilih operator.');
deployment_check(strpos($index, "require_once APPPATH.'config/runtime_config.php';") !== FALSE, 'Index harus memuat loader native setelah APPPATH tersedia.');
deployment_check(strpos($index, "getenv('APP_PRODUCTION_CONFIG_FILE')") !== FALSE, 'Index production harus memakai pointer config non-secret.');
deployment_check(strpos($loader, '($mode & 0022) !== 0') !== FALSE, 'Loader harus menolak config group/world writable.');
deployment_check(substr_count($fpm, 'env[') === 2, 'Pool FPM hanya boleh memuat dua environment non-secret.');
deployment_check(strpos($fpm, 'env[CI_ENV] = production') !== FALSE && strpos($fpm, 'env[APP_PRODUCTION_CONFIG_FILE] = /etc/ami/production.php') !== FALSE, 'Pool FPM harus memilih config production native.');
deployment_check(strpos($fpm, 'env[DB_PASSWORD]') === FALSE, 'Pool FPM tidak boleh berisi password database.');
deployment_check(strpos($nginx, 'location = /index.php') !== FALSE && strpos($nginx, 'location ~ ^/index\\.php/') !== FALSE, 'Nginx harus mendukung index dan PATH_INFO CI3.');
deployment_check(strpos($nginx, 'SCRIPT_FILENAME $document_root/index.php') !== FALSE && strpos($nginx, 'location ~ \\.php(?:/|$) { return 404; }') !== FALSE, 'Nginx harus hanya mengeksekusi front controller.');
deployment_check(strpos($bootstrap, '--config=/etc/ami/production.php') !== FALSE && strpos($bootstrap, 'password_hash($password, PASSWORD_DEFAULT)') !== FALSE, 'Bootstrap admin harus memakai config native dan hash PHP.');
deployment_check(strpos($bootstrap, 'begin_transaction()') !== FALSE && strpos($bootstrap, 'INSERT INTO users') !== FALSE, 'Bootstrap admin harus memakai transaksi dan prepared insert.');

foreach (array($guide, $guide_text) as $document) {
    deployment_check(strpos($document, '/etc/ami/production.php') !== FALSE, 'Runbook harus memakai config native di luar webroot.');
    deployment_check(strpos($document, 'ami-acme-bootstrap.conf') !== FALSE, 'Runbook harus menjelaskan bootstrap ACME.');
    deployment_check(strpos($document, 'mariadb --database=ami') !== FALSE, 'Runbook harus mengimpor schema ke database yang dipilih eksplisit.');
    deployment_check(strpos($document, 'Berita acara') !== FALSE && strpos($document, 'restore drill') !== FALSE, 'Runbook harus memuat sign-off dan restore drill.');
    deployment_check(strpos($document, 'sudo -u ami /usr/bin/php -l /etc/ami/production.php') !== FALSE, 'Runbook harus memvalidasi sintaks config native tanpa mencetak secret.');
    deployment_check(strpos($document, 'root:ami 0640') !== FALSE, 'Runbook harus mendokumentasikan permission config production.');
    deployment_check(strpos($document, 'socket owner/group `nginx`') !== FALSE, 'Runbook harus mendokumentasikan ownership socket FPM yang benar.');
}

deployment_check(strpos($guide, '| `DB_USERNAME` |') !== FALSE && strpos($guide_text, '| `DB_USERNAME` |') !== FALSE, 'Kedua runbook harus memetakan setiap credential database secara terpisah.');
deployment_check(strpos($guide, '| `MAIL_FROM_ADDRESS` |') !== FALSE && strpos($guide_text, '| `MAIL_FROM_ADDRESS` |') !== FALSE, 'Kedua runbook harus memetakan konfigurasi email secara terpisah.');
deployment_check(strpos($guide, '| `GOOGLE_DRIVE_EVIDENCE_FOLDER_ID` |') !== FALSE && strpos($guide_text, '| `GOOGLE_DRIVE_EVIDENCE_FOLDER_ID` |') !== FALSE, 'Kedua runbook harus memetakan konfigurasi Google Drive secara terpisah.');

fwrite(STDOUT, "Production deployment regression checks passed.\n");
