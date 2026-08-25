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

$auth_controller = file_get_contents(dirname(__DIR__) . '/application/controllers/Auth.php');

if ($auth_controller === FALSE) {
    throw new RuntimeException('Tidak dapat membaca Auth.php.');
}

foreach ([
    "'super_admin' => 'lpmpi/spmi-dashboard'",
    "'admin_lpmpi' => 'lpmpi/spmi-dashboard'",
    "'auditor' => 'auditor/spmi-dashboard'",
    "'auditee' => 'auditee/spmi-dashboard'",
] as $expected_redirect) {
    if (strpos($auth_controller, $expected_redirect) === FALSE) {
        throw new RuntimeException('Login sukses harus mengarahkan role ke dashboard SPMI: ' . $expected_redirect);
    }
}

if (substr_count($auth_controller, "'super_admin' => 'lpmpi/spmi-dashboard'") !== 1) {
    throw new RuntimeException('Mapping redirect role login tidak boleh diduplikasi.');
}

if (strpos($auth_controller, 'private function login_redirect()') === FALSE) {
    throw new RuntimeException('Redirect auth harus memakai helper tujuan login bersama.');
}

if (strpos($auth_controller, 'redirect($this->login_redirect());') === FALSE) {
    throw new RuntimeException('Login sukses harus memakai hasil mapping role sesi untuk redirect.');
}

$index_method_start = strpos($auth_controller, 'public function index()');
$login_method_start = strpos($auth_controller, 'public function login()');

if ($index_method_start === FALSE || $login_method_start === FALSE) {
    throw new RuntimeException('Auth::index() dan Auth::login() harus tetap tersedia.');
}

$index_method = substr($auth_controller, $index_method_start, $login_method_start - $index_method_start);

if (strpos($index_method, "redirect('dashboard');") !== FALSE) {
    throw new RuntimeException('Auth::index() tidak boleh mengarahkan sesi aktif langsung ke dashboard legacy.');
}

if (strpos($index_method, 'redirect($this->login_redirect());') === FALSE) {
    throw new RuntimeException('Auth::index() harus memakai tujuan login SPMI yang sama.');
}

fwrite(STDOUT, "Auth login regression checks passed.\n");
