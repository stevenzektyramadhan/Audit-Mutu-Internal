<?php

$root = dirname(__DIR__);
$compose = file_get_contents($root . DIRECTORY_SEPARATOR . 'compose.yaml');
if ($compose === FALSE) {
    throw new RuntimeException('Tidak dapat membaca compose.yaml.');
}

function compose_private_check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

compose_private_check(substr_count($compose, 'APP_PRIVATE_STORAGE_PATH: /srv/ami/private') === 1, 'Compose harus set APP_PRIVATE_STORAGE_PATH ke private path di luar document root.');
compose_private_check(substr_count($compose, 'private_storage:/srv/ami/private') === 1, 'Compose harus mount named volume khusus ke private storage path.');
compose_private_check(preg_match('/^\s{2}private_storage:\s*$/m', $compose) === 1, 'Compose harus mendeklarasikan named volume private_storage.');
compose_private_check(strpos($compose, 'mkdir -p /srv/ami/private') !== FALSE, 'Compose harus membuat private storage sebelum Apache start.');
compose_private_check(strpos($compose, 'chown -R www-data:www-data /srv/ami/private') !== FALSE, 'Compose harus memberi ownership private storage ke www-data.');
compose_private_check(strpos($compose, 'exec apache2-foreground') !== FALSE, 'Compose command harus tetap menjalankan foreground Apache image php:8.3-apache.');
compose_private_check(strpos($compose, 'private_storage:/var/www/html') === FALSE, 'Private storage tidak boleh dimount ke document root.');
compose_private_check(strpos($compose, 'APP_PRIVATE_STORAGE_PATH: /var/www/html') === FALSE, 'Private storage env tidak boleh menunjuk ke document root.');
compose_private_check(strpos($compose, 'audit_evidence') === FALSE, 'Compose tidak boleh membuat fallback public audit_evidence.');

fwrite(STDOUT, "Compose private storage regression checks passed.\n");
