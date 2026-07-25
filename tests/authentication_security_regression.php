#!/usr/bin/env php
<?php

$root = dirname(__DIR__);
$failures = [];

function auth_check($condition, $message)
{
    global $failures;
    if (!$condition) {
        $failures[] = $message;
    }
}

function auth_source($root, $path)
{
    $contents = file_get_contents($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path));
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }

    return $contents;
}

$config = auth_source($root, 'application/config/config.php');
$authService = auth_source($root, 'application/services/Auth_service.php');
$authGuard = auth_source($root, 'application/libraries/Auth_guard.php');
$authSecurity = auth_source($root, 'application/libraries/Auth_security.php');
$eventModel = auth_source($root, 'application/models/Auth_security_event_model.php');
$userService = auth_source($root, 'application/services/User_service.php');
$schema = auth_source($root, 'database_schema.sql');
$migration = auth_source($root, 'migrations/012_authentication_hardening.sql');

auth_check(strpos($config, "\$config['sess_regenerate_destroy'] = TRUE;") !== FALSE, 'ID sesi lama harus dihancurkan saat rotasi.');
auth_check(strpos($config, "\$config['cookie_httponly']") !== FALSE, 'Cookie HttpOnly harus tetap aktif.');
auth_check(strpos($config, "\$config['auth_idle_timeout'] = 1800;") !== FALSE, 'Idle timeout 30 menit belum dikonfigurasi.');
auth_check(strpos($config, "\$config['auth_absolute_timeout'] = 28800;") !== FALSE, 'Absolute timeout 8 jam belum dikonfigurasi.');
auth_check(strpos($config, "\$config['auth_login_email_limit'] = 5;") !== FALSE, 'Throttle per email belum dikonfigurasi.');
auth_check(strpos($config, "\$config['auth_login_ip_limit'] = 20;") !== FALSE, 'Throttle per IP belum dikonfigurasi.');

auth_check(strpos($authService, 'password_verify(') !== FALSE, 'Login harus memakai password_verify().');
auth_check(strpos($authService, 'password_needs_rehash(') !== FALSE, 'Hash lama harus dapat ditingkatkan setelah login sah.');
auth_check(strpos($authService, 'sess_regenerate(TRUE)') !== FALSE, 'Login harus meregenerasi dan menghancurkan ID sesi lama.');
auth_check(substr_count($authService, 'Email atau password salah.') === 1, 'Pesan credential invalid harus didefinisikan satu kali dan tetap generik.');
auth_check(strpos($authService, 'is_active') !== FALSE, 'Login harus menolak akun nonaktif.');
auth_check(strpos($authService, 'check_login_throttle') !== FALSE, 'Login harus melewati throttle sebelum verifikasi credential.');
auth_check(strpos($authService, 'auth_started_at') !== FALSE && strpos($authService, 'session_version') !== FALSE, 'Session harus membawa waktu mulai dan versi pencabutan.');
auth_check(strpos($authService, 'sess_destroy()') !== FALSE, 'Logout harus menghancurkan session.');

auth_check(strpos($authGuard, 'auth_idle_timeout') !== FALSE, 'Guard harus menerapkan idle timeout.');
auth_check(strpos($authGuard, 'auth_absolute_timeout') !== FALSE, 'Guard harus menerapkan absolute timeout.');
auth_check(strpos($authGuard, 'session_version') !== FALSE, 'Guard harus memeriksa versi session terhadap database.');
auth_check(strpos($authGuard, 'is_active') !== FALSE, 'Guard harus memeriksa status akun pada request terlindungi.');

auth_check(strpos($authSecurity, "hash_hmac('sha256'") !== FALSE, 'Identifier security event harus di-HMAC.');
auth_check(strpos($authSecurity, 'HTTP_USER_AGENT') !== FALSE, 'Security event harus mengikat user-agent pseudonim.');
auth_check(strpos($eventModel, "event_type', 'login_failed'") !== FALSE, 'Throttle harus menghitung event login gagal saja.');
auth_check(strpos($schema, 'CREATE TABLE IF NOT EXISTS `auth_security_events`') !== FALSE, 'Bootstrap schema harus memuat security event.');
auth_check(strpos($schema, '`email_hash` CHAR(64)') !== FALSE && strpos($schema, '`ip_hash` CHAR(64)') !== FALSE, 'Security event tidak boleh menyimpan email/IP mentah.');
auth_check(strpos($schema, '`session_version` INT UNSIGNED NOT NULL DEFAULT 1') !== FALSE, 'Bootstrap schema harus memuat session_version.');
auth_check(strpos($migration, 'INFORMATION_SCHEMA.COLUMNS') !== FALSE && strpos($migration, 'CREATE TABLE IF NOT EXISTS') !== FALSE, 'Migration 012 harus idempotent.');
auth_check(strpos($userService, 'PASSWORD_DEFAULT') !== FALSE, 'Pembuatan/perubahan password harus memakai API hashing PHP.');
auth_check(strpos($userService, "session_version'] = (int) \$user->session_version + 1") !== FALSE, 'Perubahan password/role/status harus mencabut session lama.');

$runtimeHash = password_hash('M1-03 synthetic password', PASSWORD_DEFAULT);
auth_check(is_string($runtimeHash) && password_verify('M1-03 synthetic password', $runtimeHash), 'Password hashing API PHP gagal diverifikasi pada runtime.');
auth_check(!password_verify('wrong value', $runtimeHash), 'Password hashing API menerima password yang salah.');

if (!empty($failures)) {
    foreach ($failures as $failure) {
        fwrite(STDERR, '[FAIL] ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo '[PASS] authentication security regression (29 checks)' . PHP_EOL;
exit(0);
