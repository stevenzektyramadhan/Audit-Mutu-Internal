<?php
$root = dirname(__DIR__);
function m16_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function m16_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }

$migration = m16_source('migrations/024_create_audit_logs.sql');
$schema = m16_source('database_schema.sql');
$model = m16_source('application/models/Audit_log_model.php');
$logger = m16_source('application/libraries/Audit_logger.php');
$response = m16_source('application/hooks/Response_security.php');
$mutation = m16_source('application/hooks/Audit_mutation.php');
$config = m16_source('application/config/config.php');
$hooks = m16_source('application/config/hooks.php');
$auth = m16_source('application/controllers/Auth.php');
$routes = m16_source('application/config/routes.php');
$sidebar = m16_source('application/views/layouts/sidebar.php');

foreach (['CREATE TABLE IF NOT EXISTS `audit_logs`', '`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY', '`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP', '`actor_user_id` BIGINT UNSIGNED NULL', '`actor_role` VARCHAR(32) NULL', '`event_type` VARCHAR(64) NOT NULL', '`outcome` VARCHAR(32) NOT NULL', '`request_method` VARCHAR(8) NULL', '`route` VARCHAR(255) NULL', '`resource_type` VARCHAR(64) NULL', '`resource_id` BIGINT UNSIGNED NULL', '`client_ip` VARCHAR(45) NULL', '`metadata_json` TEXT NULL', 'KEY `idx_audit_logs_created_at` (`created_at`)', 'KEY `idx_audit_logs_actor_created` (`actor_user_id`, `created_at`)', 'KEY `idx_audit_logs_event_created` (`event_type`, `created_at`)', 'KEY `idx_audit_logs_resource` (`resource_type`, `resource_id`)', 'ENGINE=InnoDB DEFAULT CHARSET=utf8'] as $literal) m16_check(strpos($migration, $literal) !== FALSE, 'M16 migration contract missing: ' . $literal);
m16_check(strpos($migration, 'FOREIGN KEY') === FALSE && strpos($migration, 'REFERENCES') === FALSE, 'M16 audit log must have no foreign keys.');
m16_check(!preg_match('/(^|;|\R)\s*INSERT\s+/i', $migration), 'M16 audit log migration must be seed-free.');
foreach (['current parity migration 001-024', 'CREATE TABLE IF NOT EXISTS `audit_logs`', '`route` VARCHAR(255) NULL', '`client_ip` VARCHAR(45) NULL', 'KEY `idx_audit_logs_actor_created` (`actor_user_id`, `created_at`)'] as $literal) m16_check(strpos($schema, $literal) !== FALSE, 'M16 schema parity missing: ' . $literal);

m16_check(strpos($model, 'function append(array $data)') !== FALSE && strpos($model, "insert(") !== FALSE, 'Audit_log_model must append into audit_logs.');
m16_check(!preg_match('/function\s+(update|delete|replace)\s*\(/i', $model), 'Audit_log_model must stay append-only.');
m16_check(strpos($model, "table_exists('audit_logs')") !== FALSE, 'Audit log writes must not break pre-migration runtime.');

foreach (["private \$allowed_metadata = ['reason', 'resource_state', 'operation']", "\$this->ci->uri->uri_string()", "\$_SERVER['REMOTE_ADDR']", 'resource_id($resource_id)', 'catch (Throwable $exception)'] as $literal) m16_check(strpos($logger, $literal) !== FALSE, 'Audit_logger contract missing: ' . $literal);
foreach (['password', 'hash', 'csrf', 'sess_id', 'REQUEST_URI', 'QUERY_STRING', 'HTTP_X_FORWARDED_FOR', 'request_body', 'email', 'evidence', 'export', 'report', 'HTTP_USER_AGENT'] as $forbidden) m16_check(stripos($logger, $forbidden) === FALSE, 'Audit_logger must not mention forbidden data: ' . $forbidden);

foreach (['Cache-Control: private, no-store, max-age=0, must-revalidate', 'Pragma: no-cache', 'Expires: 0', 'X-Content-Type-Options: nosniff', 'Referrer-Policy: same-origin', 'X-Frame-Options: DENY'] as $header) m16_check(strpos($response, $header) !== FALSE, 'Response header missing: ' . $header);
m16_check(strpos($response, "default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; img-src 'self' data:; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; connect-src 'self'; frame-src 'none'; manifest-src 'self'") !== FALSE, 'CSP report-only value changed.');
m16_check(strpos($response, 'Content-Security-Policy:') === FALSE, 'Enforced CSP header must not be set.');

foreach (["\$config['enable_hooks'] = TRUE;", "\$config['proxy_ips'] = '';", "\$config['sess_cookie_name'] = 'ami_ci_session';", "\$config['cookie_httponly'] \t= TRUE;"] as $literal) m16_check(strpos($config, $literal) !== FALSE, 'Config contract missing: ' . $literal);
foreach (['post_controller_constructor', 'Response_security', 'set_headers', 'Audit_mutation', 'log_post'] as $literal) m16_check(strpos($hooks, $literal) !== FALSE, 'Hook registration missing: ' . $literal);
m16_check(strpos($hooks, "post_controller'][]") === FALSE, 'Mutation audit hook must no longer wait for post_controller.');

foreach (["log('auth.login', 'failure', 'auth', 'login', ['reason' => 'validation'])", "log('auth.login', 'failure', 'auth', 'login', ['reason' => 'credentials'])", "log('auth.login', 'success', 'auth', 'login')", "log('auth.logout', 'success', 'auth', 'logout')"] as $literal) m16_check(strpos($auth, $literal) !== FALSE, 'Auth audit contract missing: ' . $literal);
m16_check(strpos($auth, "log('auth.logout'") < strpos($auth, 'sess_destroy()'), 'Logout must be logged before session destroy.');

foreach (["\$ci->input->method(TRUE) !== 'POST'", "['auth/login', 'auth/logout']", "is_cli()", "log('http.mutation', 'attempted', 'http', NULL, ['operation' => 'POST'])", 'catch (Throwable $exception)'] as $literal) m16_check(strpos($mutation, $literal) !== FALSE, 'Mutation audit hook missing: ' . $literal);
m16_check(strpos($routes . $sidebar, 'audit_logs') === FALSE && strpos($routes . $sidebar, 'm16') === FALSE, 'M16 must add no routes or sidebar menu.');
foreach (['lpmpi/instrumen/download/(:num)', 'auditor/penilaian', 'auditee/tugas'] as $legacy_route) m16_check(strpos($routes, $legacy_route) !== FALSE, 'Legacy route missing after M16: ' . $legacy_route);
foreach (["'key' => 'dashboard'", "'url' => 'lpmpi/laporan'", "'url' => 'lpmpi/instrumen'"] as $legacy_menu) m16_check(strpos($sidebar, $legacy_menu) !== FALSE, 'Legacy sidebar target missing after M16: ' . $legacy_menu);

fwrite(STDOUT, "M16 security regression checks passed.\n");
