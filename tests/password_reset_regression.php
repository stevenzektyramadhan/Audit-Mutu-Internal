<?php

function read_source($path)
{
    $source = file_get_contents(dirname(__DIR__) . '/' . $path);
    if ($source === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path . '.');
    }

    return $source;
}

$migration = read_source('migrations/032_create_password_reset_tokens.sql');
$schema = read_source('database_schema.sql');
$token_model = read_source('application/models/Password_reset_token_model.php');
$user_model = read_source('application/models/User_model.php');
$auth_service = read_source('application/services/Auth_service.php');
$auth_controller = read_source('application/controllers/Auth.php');
$routes = read_source('application/config/routes.php');
$email_config = read_source('application/config/email.php');

foreach ([$migration, $schema] as $source) {
    if (strpos($source, 'CREATE TABLE IF NOT EXISTS `password_reset_tokens`') === FALSE
        || strpos($source, '`token_hash` CHAR(64) NOT NULL') === FALSE
        || strpos($source, 'FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE') === FALSE) {
        throw new RuntimeException('Schema token reset harus idempoten dan menyimpan hash token dengan FK pengguna.');
    }
}

function table_definition($source)
{
    $start = strpos($source, 'CREATE TABLE IF NOT EXISTS `password_reset_tokens`');
    $end = strpos($source, ') ENGINE=InnoDB DEFAULT CHARSET=utf8;', $start);
    if ($start === FALSE || $end === FALSE) {
        throw new RuntimeException('Definisi tabel token reset tidak lengkap.');
    }

    return substr($source, $start, $end - $start + strlen(') ENGINE=InnoDB DEFAULT CHARSET=utf8;'));
}

function require_contains($haystack, $needle, $message)
{
    if (strpos($haystack, $needle) === FALSE) {
        throw new RuntimeException($message);
    }
}

function extract_non_201_status_branch($source)
{
    $branch_start = strpos($source, 'if ($status_code !== 201)');
    if ($branch_start === FALSE) {
        throw new RuntimeException('Auth service harus memeriksa status HTTP Brevo non-201.');
    }

    $branch_end = strpos($source, 'return FALSE;', $branch_start);
    if ($branch_end === FALSE) {
        throw new RuntimeException('Cabang status Brevo non-201 harus mengembalikan FALSE.');
    }

    return substr($source, $branch_start, $branch_end - $branch_start + strlen('return FALSE;'));
}

function extract_error_log_calls($source)
{
    if (!preg_match_all("/log_message\\(\\s*'error'\\s*,.*?\\);/s", $source, $matches)) {
        throw new RuntimeException('Auth service harus tetap memiliki log_message error untuk kontrak reset password.');
    }

    return $matches[0];
}

function extract_method_body($source, $signature)
{
    $signature_start = strpos($source, $signature);
    if ($signature_start === FALSE) {
        throw new RuntimeException('Controller reset password kehilangan method: ' . $signature);
    }

    $body_start = strpos($source, '{', $signature_start);
    if ($body_start === FALSE) {
        throw new RuntimeException('Controller reset password kehilangan body method: ' . $signature);
    }

    $depth = 1;
    $offset = $body_start + 1;
    $source_length = strlen($source);

    while ($offset < $source_length && $depth > 0) {
        $character = $source[$offset];
        if ($character === '{') {
            $depth++;
        } elseif ($character === '}') {
            $depth--;
        }

        $offset++;
    }

    if ($depth !== 0) {
        throw new RuntimeException('Controller reset password memiliki brace tidak seimbang pada method: ' . $signature);
    }

    return substr($source, $body_start + 1, $offset - $body_start - 2);
}

if (table_definition($migration) !== table_definition($schema)) {
    throw new RuntimeException('Bootstrap schema harus tepat sama dengan migration token reset.');
}

foreach ([
    'find_active_by_hash(',
    'find_active_by_hash_for_update',
    'expires_at > NOW() FOR UPDATE',
    'invalidate_active_for_user',
] as $contract) {
    if (strpos($token_model, $contract) === FALSE) {
        throw new RuntimeException('Model token reset kehilangan kontrak: ' . $contract);
    }
}

if (strpos($user_model, 'public function update_password($id, $password_hash)') === FALSE) {
    throw new RuntimeException('User_model harus menyediakan update password khusus.');
}

foreach ([
    'bin2hex(random_bytes(32))',
    "hash('sha256', \$token)",
    "site_url('auth/reset-password/' . rawurlencode(\$token))",
    'password_hash($password, PASSWORD_DEFAULT)',
    'is_string($password_hash)',
    'invalidate_active_for_user',
    'trans_begin()',
] as $contract) {
    if (strpos($auth_service, $contract) === FALSE) {
        throw new RuntimeException('Auth service kehilangan kontrak reset: ' . $contract);
    }
}

if (strpos($auth_service, "'token' =>") !== FALSE || strpos($auth_service, 'print_debugger') !== FALSE) {
    throw new RuntimeException('Auth service tidak boleh mengekspos token atau email debugger.');
}

if (strpos($auth_service, 'find_active_by_hash(') === FALSE || strpos($auth_service, 'find_active_by_hash_for_update(') === FALSE) {
    throw new RuntimeException('Auth service harus memisahkan lookup token GET non-locking dan POST locking.');
}

$send_password_reset_email_start = strpos($auth_service, 'private function send_password_reset_email($user, $token)');
if ($send_password_reset_email_start === FALSE) {
    throw new RuntimeException('Auth service kehilangan kontrak pengiriman email reset kata sandi.');
}

$send_password_reset_email_body_start = strpos($auth_service, '{', $send_password_reset_email_start);
$send_password_reset_email_body_end = strpos($auth_service, 'private function', $send_password_reset_email_start + 1);
if ($send_password_reset_email_body_start === FALSE) {
    throw new RuntimeException('Auth service kehilangan awal body pengiriman email reset kata sandi.');
}

if ($send_password_reset_email_body_end === FALSE) {
    $send_password_reset_email_body_end = strlen($auth_service);
}

$send_password_reset_email_body = substr($auth_service, $send_password_reset_email_body_start, $send_password_reset_email_body_end - $send_password_reset_email_body_start);
foreach ([
    'set_error_handler',
    'restore_error_handler',
    "log_message('error',",
    "https://api.brevo.com/v3/smtp/email",
    'stream_context_create(',
    'file_get_contents(',
    'json_encode(',
    "'api-key: '",
    'Content-Type: application/json',
    "'verify_peer' => TRUE",
    "'verify_peer_name' => TRUE",
    "'allow_self_signed' => FALSE",
    "'timeout' =>",
    'http_response_header',
    '201',
    'messageId',
] as $contract) {
    if (strpos($send_password_reset_email_body, $contract) === FALSE) {
        throw new RuntimeException('Auth service harus mengamankan transport email reset dan tetap mencatat error: ' . $contract);
    }
}

foreach ([
    "load->library('email')",
    'load->library("email")',
    '->email->',
    'CI_Email',
] as $forbidden_contract) {
    if (strpos($send_password_reset_email_body, $forbidden_contract) !== FALSE) {
        throw new RuntimeException('Auth service reset password tidak boleh bergantung pada CI Email: ' . $forbidden_contract);
    }
}

$non_201_status_branch = extract_non_201_status_branch($send_password_reset_email_body);
foreach ([
    'log_message(\'error\'',
    'status_code',
] as $contract) {
    require_contains(
        $non_201_status_branch,
        $contract,
        'Auth service harus mencatat metadata aman untuk kegagalan status Brevo non-201: ' . $contract
    );
}

if (strpos($non_201_status_branch, 'response_present') === FALSE
    && strpos($non_201_status_branch, 'response_category') === FALSE) {
    throw new RuntimeException('Auth service harus mencatat sinyal aman presence/category response saat status Brevo non-201.');
}

foreach (extract_error_log_calls($send_password_reset_email_body) as $log_call) {
    foreach ([
        'password_reset_brevo_api_key',
        'api-key:',
        '$user->email',
        '$token',
        '$reset_url',
        '$body',
        '$context',
        '$http_response_header',
        '$response',
        'json_encode([',
    ] as $forbidden_contract) {
        if (strpos($log_call, $forbidden_contract) !== FALSE) {
            throw new RuntimeException('Log error reset password tidak boleh mengekspos rahasia atau payload Brevo: ' . $forbidden_contract);
        }
    }
}

if (strpos($send_password_reset_email_body, '@file_get_contents(') !== FALSE) {
    throw new RuntimeException('Auth service tidak boleh memakai suppressor @ pada pengiriman email reset HTTPS.');
}

if (strpos($auth_controller, "['token' => \$token]") === FALSE) {
    throw new RuntimeException('Controller reset password harus mengirim token mentah ke view.');
}

foreach (['set_flashdata', 'audit_logger->log'] as $sensitive_sink) {
    $sink_position = strpos($auth_service, $sensitive_sink);
    if ($sink_position !== FALSE && strpos(substr($auth_service, $sink_position, 200), '$token') !== FALSE) {
        throw new RuntimeException('Token reset tidak boleh masuk ke response atau audit.');
    }
}

foreach ([
    "\$route['auth/forgot-password'] =",
    "\$route['auth/reset-password/(:any)'] =",
    "\$route['auth/reset-password/submit'] =",
] as $contract) {
    if (strpos($routes, $contract) === FALSE) {
        throw new RuntimeException('Route reset password kehilangan kontrak: ' . $contract);
    }
}

$reset_password_submit_route_position = strpos($routes, "\$route['auth/reset-password/submit'] =");
$reset_password_wildcard_route_position = strpos($routes, "\$route['auth/reset-password/(:any)'] =");
if ($reset_password_submit_route_position === FALSE || $reset_password_wildcard_route_position === FALSE) {
    throw new RuntimeException('Route reset password submit dan wildcard harus sama-sama terdaftar.');
}

if ($reset_password_submit_route_position > $reset_password_wildcard_route_position) {
    throw new RuntimeException('Route auth/reset-password/submit harus dideklarasikan sebelum wildcard auth/reset-password/(:any) agar submit tidak tertangkap token route.');
}

foreach ([
    'public function forgot_password()',
    'public function send_password_reset()',
    'public function reset_password($token = NULL)',
    'public function update_password()',
    'min_length[12]',
    'matches[password]',
    'private function require_post()',
] as $contract) {
    if (strpos($auth_controller, $contract) === FALSE) {
        throw new RuntimeException('Controller reset password kehilangan kontrak: ' . $contract);
    }
}

$forgot_password_body = extract_method_body($auth_controller, 'public function forgot_password()');
if (strpos($forgot_password_body, 'redirect_authenticated_user()') !== FALSE) {
    throw new RuntimeException('forgot_password tidak boleh mengalihkan pengguna yang sudah login; otorisasi tetap ditentukan oleh verifikasi email/token reset.');
}
require_contains(
    $forgot_password_body,
    "load->view('auth/forgot_password')",
    'forgot_password harus tetap menampilkan view GET form lupa kata sandi.'
);

$send_password_reset_body = extract_method_body($auth_controller, 'public function send_password_reset()');
if (strpos($send_password_reset_body, 'redirect_authenticated_user()') !== FALSE) {
    throw new RuntimeException('send_password_reset tidak boleh mengalihkan pengguna yang sudah login; respons generik harus tetap tersedia dari sesi login.');
}
require_contains(
    $send_password_reset_body,
    '!$this->require_post()',
    'send_password_reset harus tetap mempertahankan kontrak require_post.'
);
foreach ([
    "set_rules('email', 'Email', 'required|valid_email')",
    'request_password_reset($this->input->post(\'email\', TRUE))',
    "set_flashdata('success', 'Jika alamat email terdaftar, instruksi pengaturan ulang kata sandi telah dikirim.')",
    "redirect('auth/forgot-password')",
] as $contract) {
    require_contains(
        $send_password_reset_body,
        $contract,
        'send_password_reset harus mempertahankan kontrak respons generik reset password: ' . $contract
    );
}

$reset_password_body = extract_method_body($auth_controller, 'public function reset_password($token = NULL)');
if (strpos($reset_password_body, 'redirect_authenticated_user()') !== FALSE) {
    throw new RuntimeException('reset_password tidak boleh mengalihkan pengguna yang sudah login sebelum validasi token reset.');
}

$update_password_body = extract_method_body($auth_controller, 'public function update_password()');
require_contains(
    $update_password_body,
    '!$this->require_post()',
    'update_password harus tetap mempertahankan kontrak require_post.'
);
if (strpos($update_password_body, 'redirect_authenticated_user()') !== FALSE) {
    throw new RuntimeException('update_password tidak boleh mengalihkan pengguna yang sudah login sebelum reset password berbasis token diproses.');
}

foreach (['BREVO_API_KEY', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME'] as $variable) {
    if (strpos($email_config, "getenv('" . $variable . "')") === FALSE) {
        throw new RuntimeException('Konfigurasi email harus membaca ' . $variable . ' dari environment.');
    }
}

foreach ([
    'password_reset_mail_enabled',
    'password_reset_from_address',
    'password_reset_from_name',
    'password_reset_brevo_api_key',
    'password_reset_brevo_api_url',
] as $contract) {
    if (strpos($email_config, $contract) === FALSE) {
        throw new RuntimeException('Konfigurasi email reset kehilangan kontrak Brevo HTTPS: ' . $contract);
    }
}

if (strpos($email_config, 'https://api.brevo.com/v3/smtp/email') === FALSE) {
    throw new RuntimeException('Konfigurasi email reset harus mengunci endpoint HTTPS Brevo native.');
}

foreach ([
    'MAIL_HOST',
    'MAIL_PORT',
    'MAIL_USERNAME',
    'MAIL_PASSWORD',
    'MAIL_ENCRYPTION',
    "'protocol'",
    'smtp_host',
    'smtp_port',
    'smtp_user',
    'smtp_pass',
    'smtp_crypto',
] as $forbidden_contract) {
    if (strpos($email_config, $forbidden_contract) !== FALSE) {
        throw new RuntimeException('Konfigurasi email reset tidak boleh lagi merujuk kontrak SMTP/CI Email: ' . $forbidden_contract);
    }
}

fwrite(STDOUT, "Password reset regression checks passed.\n");
