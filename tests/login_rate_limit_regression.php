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

function login_rate_limit_method_body($source, $signature)
{
    $start = strpos($source, $signature);
    if ($start === FALSE) {
        throw new RuntimeException('Rate-limit method missing: ' . $signature);
    }

    $body_start = strpos($source, '{', $start);
    if ($body_start === FALSE) {
        throw new RuntimeException('Rate-limit method body missing: ' . $signature);
    }

    $depth = 1;
    $offset = $body_start + 1;
    while ($offset < strlen($source) && $depth > 0) {
        if ($source[$offset] === '{') $depth++;
        if ($source[$offset] === '}') $depth--;
        $offset++;
    }

    if ($depth !== 0) {
        throw new RuntimeException('Rate-limit method has unbalanced braces: ' . $signature);
    }

    return substr($source, $body_start + 1, $offset - $body_start - 2);
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
login_rate_limit_check(strpos($migration, "ENUM('identity','ip')") !== FALSE && strpos($schema, "ENUM('identity','ip')") !== FALSE, 'Request budgets must reuse existing identity/ip scopes without schema changes.');

foreach ([
    'class Login_rate_limit_model extends CI_Model',
    "protected \$table = 'login_rate_limit_buckets';",
    'ON DUPLICATE KEY UPDATE',
    'UTC_TIMESTAMP()',
    'public function record_request($scope, $key_hash, $limit, $window_minutes)',
    'public function consume_request_budget($identity_hash, $ip_hash, $identity_limit, $ip_limit, $window_minutes)',
    "record_request('identity', \$identity_hash, \$identity_limit, \$window_minutes)",
    "record_request('ip', \$ip_hash, \$ip_limit, \$window_minutes)",
    'trans_begin()',
    'trans_commit()',
    'trans_rollback()',
] as $literal) {
    login_rate_limit_check(strpos($model, $literal) !== FALSE, 'Login rate-limit model contract missing: ' . $literal);
}

foreach ([
    'if (!$this->require_post())',
    '$this->input->ip_address()',
    '$this->auth_service->login($email, $password, $client_ip)',
    '$this->auth_service->consume_login_request_budget($email, $client_ip)',
    "['reason' => 'turnstile_configuration']",
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
    'public function consume_login_request_budget($email, $client_ip)',
    'private function consume_request_budget($email, $client_ip, $identity_purpose, $ip_purpose, $identity_limit, $ip_limit, $window_minutes, $error_message)',
    "'login-request-identity:'",
    "'login-request-ip:'",
    "'password-reset-identity:'",
    "'password-reset-ip:'",
    'hash_hmac(\'sha256\', $identity_purpose . $identity, $key)',
    'hash_hmac(\'sha256\', $ip_purpose . (string) $client_ip, $key)',
    '$this->login_rate_limit_model->consume_request_budget(',
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

$login_request_budget_body = login_rate_limit_method_body($service, 'public function consume_login_request_budget($email, $client_ip)');
foreach ([
    "'login-request-identity:'",
    "'login-request-ip:'",
    '10',
    '60',
    '15',
] as $literal) {
    login_rate_limit_check(strpos($login_request_budget_body, $literal) !== FALSE, 'Pre-Siteverify request budget contract missing: ' . $literal);
}
$controller_login_body = login_rate_limit_method_body($controller, 'public function login()');
$validation_pos = strpos($controller_login_body, '$this->form_validation->run()');
$preverify_budget_pos = strpos($controller_login_body, '$this->auth_service->consume_login_request_budget($email, $client_ip)');
$siteverify_pos = strpos($controller_login_body, 'verify_turnstile_response');
$credential_login_pos = strpos($controller_login_body, '$this->auth_service->login($email, $password, $client_ip)');
login_rate_limit_check($validation_pos !== FALSE && $preverify_budget_pos !== FALSE && $siteverify_pos !== FALSE && $credential_login_pos !== FALSE && $validation_pos < $preverify_budget_pos && $preverify_budget_pos < $siteverify_pos && $siteverify_pos < $credential_login_pos, 'Pre-Siteverify budget must run after validation and before Siteverify or credential lookup.');
$preverify_budget_return_pos = strpos($controller_login_body, 'return;', $preverify_budget_pos);
login_rate_limit_check($preverify_budget_return_pos !== FALSE && $preverify_budget_return_pos < $siteverify_pos, 'Blocked pre-Siteverify budget must return before Siteverify.');
login_rate_limit_check(strpos($controller_login_body, "header('Retry-After: '") !== FALSE && strpos($controller_login_body, "header('Cache-Control: no-store')") !== FALSE, 'Pre-Siteverify budget denial must preserve generic 429 cache controls.');

$login_service_body = login_rate_limit_method_body($service, 'public function login($email, $password, $client_ip)');
login_rate_limit_check(strpos($login_service_body, "'login-request-identity:'") === FALSE && strpos($login_service_body, "'login-request-ip:'") === FALSE, 'Credential login must not share request-budget HMAC purposes.');
login_rate_limit_check(strpos($login_service_body, "'password-reset-identity:'") === FALSE && strpos($login_service_body, "'password-reset-ip:'") === FALSE, 'Credential login must not share password-reset HMAC purposes.');

foreach ([
    '$login_error',
    'html_escape($login_error)',
    'role="alert"',
] as $literal) {
    login_rate_limit_check(strpos($view, $literal) !== FALSE, 'Login view rate-limit alert contract missing: ' . $literal);
}
login_rate_limit_check(strpos($controller, 'Too many sign-in attempts. Please try again later.') !== FALSE, 'Rate-limit response must retain one generic user-facing message.');

fwrite(STDOUT, "Login rate-limit regression checks passed.\n");
