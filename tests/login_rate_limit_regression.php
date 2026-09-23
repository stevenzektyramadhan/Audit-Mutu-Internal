<?php

function login_rate_limit_source($path)
{
    $source = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path);
    if ($source === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path . '.');
    }

    return $source;
}

function login_rate_limit_check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$migration = login_rate_limit_source('migrations/040_create_login_rate_limit_buckets.sql');
$schema = login_rate_limit_source('database_schema.sql');
$model = login_rate_limit_source('application/models/Login_rate_limit_model.php');
$service = login_rate_limit_source('application/services/Auth_service.php');
$controller = login_rate_limit_source('application/controllers/Auth.php');
$view = login_rate_limit_source('application/views/auth/login.php');

foreach ([$migration, $schema] as $source) {
    foreach ([
        'CREATE TABLE IF NOT EXISTS `login_rate_limit_buckets`',
        '`scope`',
        '`key_hash` CHAR(64) NOT NULL',
        '`window_started_at` DATETIME NOT NULL',
        '`failure_count` SMALLINT UNSIGNED NOT NULL',
        '`blocked_until` DATETIME NULL',
        'PRIMARY KEY (`scope`, `key_hash`)',
        'KEY `idx_login_rate_limit_buckets_window` (`window_started_at`)',
    ] as $literal) {
        login_rate_limit_check(strpos($source, $literal) !== FALSE, 'Login rate-limit schema contract missing: ' . $literal);
    }
}

login_rate_limit_check(strpos($schema, 'current parity migration 001-040') !== FALSE, 'Bootstrap schema must record migration 040 parity.');
login_rate_limit_check(strpos($migration, 'FOREIGN KEY') === FALSE, 'Login rate-limit buckets must not add foreign keys.');
login_rate_limit_check(strpos($migration, 'INSERT ') === FALSE, 'Login rate-limit migration must not seed attempt data.');

foreach ([
    'class Login_rate_limit_model extends CI_Model',
    "protected \$table = 'login_rate_limit_buckets';",
    'ON DUPLICATE KEY UPDATE',
    'UTC_TIMESTAMP()',
] as $literal) {
    login_rate_limit_check(strpos($model, $literal) !== FALSE, 'Login rate-limit model contract missing: ' . $literal);
}

foreach ([
    'if (!$this->require_post())',
    '$this->input->ip_address()',
    '$this->auth_service->login($email, $password, $client_ip)',
    '429',
    "header('Retry-After: '",
    "header('Cache-Control: no-store')",
    "['reason' => 'rate_limited']",
] as $literal) {
    login_rate_limit_check(strpos($controller, $literal) !== FALSE, 'Auth controller rate-limit contract missing: ' . $literal);
}

foreach ([
    '$this->ci->load->model(\'Login_rate_limit_model\')',
    'hash_hmac(\'sha256\'',
    "strtolower(trim(\$email))",
    "'identity:'",
    "'ip:'",
    'password_verify($password, $password_hash)',
    '$this->login_rate_limit_model->is_blocked(',
    '$this->login_rate_limit_model->record_failure(',
    '$this->login_rate_limit_model->clear_identity(',
] as $literal) {
    login_rate_limit_check(strpos($service, $literal) !== FALSE, 'Auth service rate-limit contract missing: ' . $literal);
}

$blocked_check = strpos($service, '$this->login_rate_limit_model->is_blocked(');
$password_verify = strpos($service, 'password_verify($password, $password_hash)');
$record_failure = strpos($service, '$this->login_rate_limit_model->record_failure(');
$clear_identity = strpos($service, '$this->login_rate_limit_model->clear_identity(');
login_rate_limit_check($blocked_check !== FALSE && $password_verify !== FALSE && $blocked_check < $password_verify, 'Rate-limit block check must occur before password verification.');
login_rate_limit_check($record_failure !== FALSE && $password_verify !== FALSE && $record_failure > $password_verify, 'Only failed credential verification may record a login attempt.');
login_rate_limit_check($clear_identity !== FALSE && $password_verify !== FALSE && $clear_identity > $password_verify, 'Successful login must clear only its identity bucket after password verification.');
login_rate_limit_check(strpos($service, "['success' => FALSE, 'rate_limited' => TRUE") !== FALSE, 'Rate-limited service result must stay structured and generic.');
login_rate_limit_check(strpos($service, "'Email atau password salah.'") !== FALSE, 'Invalid credentials must retain a generic message.');
login_rate_limit_check(stripos($model, 'email') === FALSE && stripos($model, 'client_ip') === FALSE, 'Rate-limit model must not persist raw email or client IP.');

foreach ([
    '$login_error',
    'html_escape($login_error)',
    'role="alert"',
] as $literal) {
    login_rate_limit_check(strpos($view, $literal) !== FALSE, 'Login view rate-limit alert contract missing: ' . $literal);
}
login_rate_limit_check(strpos($controller, 'Too many sign-in attempts. Please try again later.') !== FALSE, 'Rate-limit response must retain one generic user-facing message.');

fwrite(STDOUT, "Login rate-limit regression checks passed.\n");
