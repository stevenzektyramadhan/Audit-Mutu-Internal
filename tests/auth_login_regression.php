<?php

$auth_service = file_get_contents(dirname(__DIR__) . '/application/services/Auth_service.php');

if ($auth_service === FALSE) {
    throw new RuntimeException('Tidak dapat membaca Auth_service.php.');
}

if (strpos($auth_service, "'profile_photo_path' => \$user->profile_photo_path ?? NULL,") === FALSE) {
    throw new RuntimeException('Login harus mendukung user legacy tanpa profile_photo_path.');
}

$photo_path = strpos($auth_service, "'profile_photo_path' => \$user->profile_photo_path ?? NULL,");
$session_regenerate = strpos($auth_service, 'sess_regenerate(TRUE)');

if ($session_regenerate === FALSE || $session_regenerate < $photo_path) {
    throw new RuntimeException('Login harus tetap meregenerasi sesi setelah membangun data sesi.');
}

fwrite(STDOUT, "Auth login regression checks passed.\n");
