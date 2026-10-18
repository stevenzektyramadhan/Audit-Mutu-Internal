<?php

function system_settings_source($path)
{
    $contents = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path);
    if ($contents === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path);
    return $contents;
}

function system_settings_check($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

function system_settings_method_body($source, $signature)
{
    $start = strpos($source, $signature);
    if ($start === FALSE) throw new RuntimeException('Method missing: ' . $signature);
    $body_start = strpos($source, '{', $start);
    if ($body_start === FALSE) throw new RuntimeException('Method body missing: ' . $signature);

    $depth = 1;
    $offset = $body_start + 1;
    while ($offset < strlen($source) && $depth > 0) {
        if ($source[$offset] === '{') $depth++;
        if ($source[$offset] === '}') $depth--;
        $offset++;
    }
    if ($depth !== 0) throw new RuntimeException('Method braces are unbalanced: ' . $signature);

    return substr($source, $body_start + 1, $offset - $body_start - 2);
}

$migration = system_settings_source('migrations/047_create_system_settings.sql');
$schema = system_settings_source('database_schema.sql');
$model = system_settings_source('application/models/System_settings_model.php');
$service = system_settings_source('application/services/System_settings_service.php');
$logo_service = system_settings_source('application/services/Organization_logo_service.php');
$controller = system_settings_source('application/controllers/lpmpi/System_settings.php');
$auth_controller = system_settings_source('application/controllers/Auth.php');
$login_view = system_settings_source('application/views/auth/login.php');
$settings_view = system_settings_source('application/views/lpmpi/system_settings/index.php');
$header = system_settings_source('application/views/layouts/header.php');
$response_security = system_settings_source('application/hooks/Response_security.php');
$tailwind_config = system_settings_source('tailwind.config.js');
$package = system_settings_source('package.json');
$system_settings_css = system_settings_source('assets/css/system-settings.css');
$routes = system_settings_source('application/config/routes.php');
$readme = system_settings_source('README.md');

foreach ([
    'ADD COLUMN `deskripsi` TEXT NULL AFTER `nama_pt`',
    'CREATE TABLE IF NOT EXISTS `system_settings`',
    '`id` TINYINT UNSIGNED NOT NULL PRIMARY KEY',
    '`turnstile_site_key` VARCHAR(255) NULL',
    '`turnstile_secret_ciphertext` TEXT NULL',
    'CONSTRAINT `chk_system_settings_singleton` CHECK (`id` = 1)',
    'ON DUPLICATE KEY UPDATE',
] as $literal) {
    system_settings_check(strpos($migration, $literal) !== FALSE, 'Migration 047 missing: ' . $literal);
}
foreach ([
    '`deskripsi` TEXT NULL',
    'CREATE TABLE IF NOT EXISTS `system_settings`',
    '`turnstile_secret_ciphertext` TEXT NULL',
    'CONSTRAINT `chk_system_settings_singleton` CHECK (`id` = 1)',
    '(1, NULL, NULL)',
] as $literal) {
    system_settings_check(strpos($schema, $literal) !== FALSE, 'Schema parity missing: ' . $literal);
}
system_settings_check(strpos($schema, 'current parity migration 001-047') !== FALSE, 'Schema parity comment must indicate 001-047.');

foreach ([
    'profile_for_update()',
    'settings_for_update()',
    'upsert_profile($profile_id, array $data)',
    'upsert_settings(array $data)',
    "where('id', 1)",
    'SELECT * FROM profil_lembaga ORDER BY id ASC LIMIT 1 FOR UPDATE',
    'SELECT * FROM system_settings WHERE id = 1 FOR UPDATE',
] as $literal) {
    system_settings_check(strpos($model, $literal) !== FALSE, 'Persistence model contract missing: ' . $literal);
}
foreach (['openssl_encrypt', 'openssl_decrypt', 'file_get_contents(self::TURNSTILE_SITEVERIFY_URL', 'input->post', 'session->set_flashdata'] as $forbidden) {
    system_settings_check(strpos($model, $forbidden) === FALSE, 'Model must stay persistence-only and not contain: ' . $forbidden);
}

foreach ([
    "const TURNSTILE_SITEVERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify'",
    'public function update(array $data, $actor_role, array $logo_upload = NULL)',
    '$can_manage_turnstile = (string) $actor_role === \'super_admin\'',
    'organization_name',
    'organization_description',
    "require_once APPPATH . 'services/Organization_logo_service.php'",
    '$this->logo_service = new Organization_logo_service();',
    '$logo = $this->logo_service->preview_urls($profile);',
    "'organization_logo_local_url' => \$logo['local_url']",
    "'organization_logo_fallback_url' => \$logo['fallback_url']",
    "'institution_logo_limit_bytes' => \$this->logo_service->limit_bytes()",
    'mb_strlen($organization_name, \'UTF-8\') > 200',
    '\'deskripsi\' => $organization_description === \'\' ? NULL : $organization_description',
    "'turnstile_secret_configured'",
    "'turnstile_state'",
    "'turnstile_configuration_invalid'",
    "'state' => \$turnstile['state']",
    "'enabled' => \$turnstile['enabled']",
    "'site_key' => \$turnstile['enabled'] ? \$site_key : ''",
    'protected function turnstile_configuration($site_key, $ciphertext)',
    "'state' => 'disabled'",
    "'state' => 'configuration_invalid'",
    "'state' => 'enabled'",
    "if (\$site_key === '')",
    '$this->is_plausible_turnstile_secret($secret)',
    'strlen($token) > 2048',
    "'Content-Type: application/x-www-form-urlencoded'",
    "'timeout' => 5",
    "'verify_peer' => TRUE",
    "'verify_peer_name' => TRUE",
    "'allow_self_signed' => FALSE",
    'http_build_query($payload, \'\', \'&\')',
    "'remoteip'",
    'AES-256-GCM',
    'random_bytes(12)',
    'openssl_encrypt((string) $secret, \'aes-256-gcm\'',
    'openssl_decrypt($ciphertext, \'aes-256-gcm\'',
    'hash(\'sha256\', $configured, TRUE)',
    '$secret = $this->decrypt_secret($ciphertext)',
    'trans_begin()',
    'trans_commit()',
    'trans_rollback()',
] as $literal) {
    system_settings_check(strpos($service, $literal) !== FALSE, 'Service security/validation contract missing: ' . $literal);
}
$turnstile_public_settings_body = system_settings_method_body($service, 'public function turnstile_public_settings()');
system_settings_check(strpos($turnstile_public_settings_body, "'site_key' => \$turnstile['enabled'] ? \$site_key : ''") !== FALSE, 'Public Turnstile settings must hide site key unless enabled.');
system_settings_check(strpos($turnstile_public_settings_body, 'turnstile_secret_ciphertext') !== FALSE && strpos($turnstile_public_settings_body, "'secret' =>") === FALSE, 'Public Turnstile settings may read ciphertext but must never expose a secret.');
$turnstile_verify_body = system_settings_method_body($service, 'public function verify_turnstile_response($token, $remote_ip = \'\')');
foreach ([
    "if (\$turnstile['state'] === 'disabled')",
    "if (!\$turnstile['enabled'])",
    "'timeout' => 5",
    "\$warning || !is_string(\$response) || \$this->parse_http_status_code(\$headers) !== 200",
    'json_decode($response, TRUE)',
    "!is_array(\$decoded) || !isset(\$decoded['success']) || \$decoded['success'] !== TRUE",
    "['success' => FALSE, 'message' => 'Verifikasi keamanan gagal. Silakan coba lagi.']",
] as $literal) {
    system_settings_check(strpos($turnstile_verify_body, $literal) !== FALSE, 'Turnstile must fail closed for: ' . $literal);
}
system_settings_check(strpos($turnstile_verify_body, 'file_get_contents(self::TURNSTILE_SITEVERIFY_URL') > strpos($turnstile_verify_body, "if (!\$turnstile['enabled'])"), 'Invalid configured Turnstile must fail before Siteverify.');
foreach ([
    'class Organization_logo_service',
    "require_once APPPATH . 'services/Upload_size_settings_service.php'",
    "const MIME_EXTENSIONS = [",
    "'image/jpeg' => 'jpg'",
    "'image/png' => 'png'",
    "'image/gif' => 'gif'",
    'public function preview_urls($profile)',
    "base_url('uploads/profil/' . rawurlencode(\$profile->logo_path))",
    "base_url('assets/img/logo-2.png')",
    'public function limit_bytes()',
    "Upload_size_settings_service::limit_bytes('institution_logo')",
    'public function store_candidate($upload)',
    'UPLOAD_ERR_NO_FILE',
    'UPLOAD_ERR_OK',
    'is_uploaded_file($upload[\'tmp_name\'])',
    '$size <= 0',
    '$size > $limit',
    '$finfo = new finfo(FILEINFO_MIME_TYPE)',
    '@getimagesize($upload[\'tmp_name\']) === FALSE',
    "FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'profil' . DIRECTORY_SEPARATOR",
    "'logo_lembaga_' . bin2hex(random_bytes(16)) . '.' . self::MIME_EXTENSIONS[\$mime]",
    'move_uploaded_file($upload[\'tmp_name\'], $directory . $file_name)',
    'public function delete_local($file_name)',
    'basename((string) $file_name)',
] as $literal) {
    system_settings_check(strpos($logo_service, $literal) !== FALSE, 'Organization logo service contract missing: ' . $literal);
}
foreach ([
    '$candidate = $this->logo_service->store_candidate($logo_upload);',
    '$profile_data[\'logo_path\'] = $candidate[\'file_name\'];',
    '$this->rollback(\'Pengaturan sistem gagal disimpan.\', $candidate[\'file_name\'])',
    'if (!empty($candidate_file)) $this->logo_service->delete_local($candidate_file);',
    '$this->logo_service->delete_local($profile->logo_path);',
] as $literal) {
    system_settings_check(strpos($service, $literal) !== FALSE, 'System settings transaction/logo orchestration missing: ' . $literal);
}
foreach (['const MIME_EXTENSIONS', 'function store_candidate', 'function detected_mime', 'function upload_dir'] as $forbidden) {
    system_settings_check(strpos($service, $forbidden) === FALSE, 'System settings service must not retain logo implementation detail: ' . $forbidden);
}
system_settings_check(strpos($logo_service, 'logo_url') !== FALSE && strpos($logo_service, "'logo_url' =>") === FALSE, 'Organization logo service must expose remote logo fallback but never persist or delete logo_url.');
system_settings_check(strpos($service, '$_FILES') === FALSE && strpos($service, 'input->post') === FALSE, 'System settings service must receive upload/POST data from controller, not globals/input.');
system_settings_check(strpos($logo_service, '$_FILES') === FALSE && strpos($logo_service, 'input->post') === FALSE && strpos($logo_service, 'session') === FALSE, 'Organization logo service must not access request globals, CI input, or session.');
system_settings_check(strpos($logo_service, '$upload[\'name\']') === FALSE && strpos($logo_service, '$_FILES[\'logo\'][\'name\']') === FALSE, 'Logo upload must not trust or persist client filenames.');
$turnstile_gate_pos = strpos($service, 'if ($can_manage_turnstile)');
$turnstile_persist_gate_pos = strpos($service, 'if ($can_manage_turnstile)', $turnstile_gate_pos === FALSE ? 0 : $turnstile_gate_pos + 1);
$site_key_read_pos = strpos($service, "\$site_key = trim((string) (\$data['turnstile_site_key'] ?? ''))");
$secret_read_pos = strpos($service, "\$secret_replacement = trim((string) (\$data['turnstile_secret_key'] ?? ''))");
$settings_lock_pos = strpos($service, '$settings = $this->model->settings_for_update();');
$settings_upsert_pos = strpos($service, '$this->model->upsert_settings($settings_data)');
$profile_upsert_pos = strpos($service, '$this->model->upsert_profile($profile ? (int) $profile->id : 0, $profile_data)');
system_settings_check($turnstile_gate_pos !== FALSE, 'Service must gate Turnstile handling behind exact super_admin role.');
system_settings_check($turnstile_persist_gate_pos !== FALSE, 'Service must separately gate Turnstile persistence behind exact super_admin role.');
system_settings_check($site_key_read_pos !== FALSE && $turnstile_gate_pos < $site_key_read_pos, 'Service must not read submitted Turnstile site key before super_admin authorization.');
system_settings_check($secret_read_pos !== FALSE && $turnstile_gate_pos < $secret_read_pos, 'Service must not read submitted Turnstile secret before super_admin authorization.');
system_settings_check($settings_lock_pos !== FALSE && $turnstile_persist_gate_pos < $settings_lock_pos, 'Service must not lock system_settings before super_admin authorization.');
system_settings_check($settings_upsert_pos !== FALSE && $turnstile_persist_gate_pos < $settings_upsert_pos, 'Service must not upsert system_settings before super_admin authorization.');
system_settings_check($profile_upsert_pos !== FALSE && $profile_upsert_pos < $settings_upsert_pos, 'Profile persistence must remain available before optional Turnstile persistence.');
foreach (['turnstile_secret_key' => '$secret_replacement', 'turnstile_secret_ciphertext' => '$ciphertext'] as $input => $stored) {
    system_settings_check(strpos($service, $input) !== FALSE && strpos($service, $stored) !== FALSE, 'Secret replacement must be accepted but stored only as ciphertext.');
}
system_settings_check(strpos($service, '\'secret\' => $secret') !== FALSE, 'Siteverify request must include decrypted secret only in POST payload.');
system_settings_check(strpos($service, 'log_message') === FALSE, 'System settings service must not log Turnstile secrets or tokens.');

foreach ([
    'class System_settings extends Admin_Lpmpi_Controller',
    "require_once APPPATH . 'services/System_settings_service.php'",
    "'active_menu' => 'system_settings'",
    'public function update()',
    '$this->require_post();',
    "input->post(NULL, TRUE)",
    "isset(\$_FILES['logo']) ? \$_FILES['logo'] : NULL",
    '(string) $this->session->userdata(\'role\')',
    '\'can_manage_turnstile\' => $role === \'super_admin\'',
    '$this->service->update($this->input->post(NULL, TRUE), (string) $this->session->userdata(\'role\'), isset($_FILES[\'logo\']) ? $_FILES[\'logo\'] : NULL)',
    '\'institution_logo_limit_mib\' => (int) floor($settings[\'institution_logo_limit_bytes\'] / 1024 / 1024)',
    "redirect('lpmpi/system-settings')",
    "show_error('Method tidak diizinkan.', 405, 'Method Not Allowed')",
] as $literal) {
    system_settings_check(strpos($controller, $literal) !== FALSE, 'System settings controller contract missing: ' . $literal);
}

foreach ([
    "\$route['lpmpi/system-settings'] = 'lpmpi/System_settings/index';",
    "\$route['lpmpi/system-settings/update'] = 'lpmpi/System_settings/update';",
] as $literal) {
    system_settings_check(strpos($routes, $literal) !== FALSE, 'System settings route missing: ' . $literal);
}
system_settings_check(substr_count($routes, 'system-settings') === 2, 'System settings must expose exactly index and update routes.');

foreach ([
    "require_once APPPATH . 'services/System_settings_service.php'",
    '$this->system_settings_service = new System_settings_service();',
    '$this->render_login();',
    '$data[\'turnstile\'] = $this->system_settings_service->turnstile_public_settings();',
    '$this->system_settings_service->verify_turnstile_response($this->input->post(\'cf-turnstile-response\', TRUE), $client_ip)',
    "['reason' => 'turnstile']",
    "['reason' => 'turnstile_configuration']",
    '$this->render_login([\'login_error\' => $captcha[\'message\']]);',
] as $literal) {
    system_settings_check(strpos($auth_controller, $literal) !== FALSE, 'Auth Turnstile integration missing: ' . $literal);
}
$captcha_pos = strpos($auth_controller, 'verify_turnstile_response');
$login_pos = strpos($auth_controller, '$result = $this->auth_service->login($email, $password, $client_ip);');
$turnstile_configuration_pos = strpos($auth_controller, "if (!empty(\$turnstile['configuration_invalid']))");
system_settings_check($captcha_pos !== FALSE && $login_pos !== FALSE && $captcha_pos < $login_pos, 'Turnstile verification must happen before credential login/rate-limit mutation.');
$turnstile_configuration_return_pos = strpos($auth_controller, 'return;', $turnstile_configuration_pos === FALSE ? 0 : $turnstile_configuration_pos);
system_settings_check($turnstile_configuration_pos !== FALSE && $turnstile_configuration_pos < $captcha_pos && $turnstile_configuration_pos < $login_pos && $turnstile_configuration_return_pos !== FALSE && $turnstile_configuration_return_pos < $captcha_pos, 'Configured-invalid Turnstile must fail closed before Siteverify and credential login.');
$failure_pos = strpos($auth_controller, "['reason' => 'turnstile']");
system_settings_check($failure_pos !== FALSE && $failure_pos < $login_pos, 'CAPTCHA failure must return before credential login.');

foreach ([
    "\$turnstile = isset(\$turnstile) && is_array(\$turnstile) ? \$turnstile : [];",
    "\$turnstile_enabled = !empty(\$turnstile['enabled']) && !empty(\$turnstile['site_key']);",
    'class="cf-turnstile"',
    'data-sitekey="<?php echo html_escape($turnstile_site_key); ?>"',
    '<script async defer src="https://challenges.cloudflare.com/turnstile/v0/api.js"></script>',
    'Aktifkan JavaScript untuk menyelesaikan verifikasi keamanan sebelum masuk.',
] as $literal) {
    system_settings_check(strpos($login_view, $literal) !== FALSE, 'Login Turnstile UI contract missing: ' . $literal);
}
system_settings_check(substr_count($login_view, 'https://challenges.cloudflare.com/turnstile/v0/api.js') === 1, 'Turnstile script must be declared exactly once.');
system_settings_check(strpos($login_view, 'if ($turnstile_enabled):') !== FALSE, 'Turnstile widget must be conditionally rendered.');
system_settings_check(strpos($response_security, "script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://challenges.cloudflare.com") !== FALSE, 'CSP must allow the Turnstile API script only from challenges.cloudflare.com.');
system_settings_check(strpos($response_security, "frame-src https://challenges.cloudflare.com") !== FALSE, 'CSP must allow the Turnstile challenge frame only from challenges.cloudflare.com.');

foreach ([
    "form_open_multipart('lpmpi/system-settings/update'",
    'name="organization_name"',
    'name="organization_description"',
    '$organization_logo_local_url',
    '$organization_logo_fallback_url',
    '$organization_logo_preview_url = $organization_logo_local_url !== \'\' ? $organization_logo_local_url : $organization_logo_fallback_url',
    'name="logo"',
    'accept="image/jpeg,image/png,image/gif"',
    'Logo organisasi saat ini',
    'Maksimal <?php echo html_escape((string) $institution_logo_limit_mib); ?> MiB',
    'Logo lokal akan diprioritaskan oleh Profil Lembaga; URL PDDikti tetap menjadi fallback.',
    'name="turnstile_site_key"',
    'name="turnstile_secret_key"',
    'value="" maxlength="255" autocomplete="new-password"',
    'Biarkan kolom secret key baru kosong untuk mempertahankan secret key yang tersimpan.',
    'Secret key tidak dapat dibaca kembali.',
    '$turnstile_secret_configured',
    '$turnstile_enabled',
    '$turnstile_state',
    '$turnstile_configuration_invalid',
    'Konfigurasi tidak valid',
    'Tidak dikonfigurasi',
    'Konfigurasi Turnstile tidak valid atau tidak dapat dibaca.',
    'Nilai tersimpan tidak ditampilkan.',
    'Secret tersimpan',
    'CAPTCHA login',
    'html_escape(set_value(\'organization_name\'',
    'html_escape(set_value(\'organization_description\'',
    'html_escape(set_value(\'turnstile_site_key\'',
] as $literal) {
    system_settings_check(strpos($settings_view, $literal) !== FALSE, 'System settings view contract missing: ' . $literal);
}
system_settings_check(strpos($settings_view, 'turnstile_secret_key') !== FALSE, 'Expected secret replacement input contract missing.');
system_settings_check(strpos($settings_view, 'turnstile_secret_ciphertext') === FALSE && strpos($settings_view, 'secret_replacement') === FALSE, 'System settings view must not expose secret storage or derived values.');
$view_turnstile_gate_pos = strpos($settings_view, 'if ($can_manage_turnstile):');
$view_non_super_pos = strpos($settings_view, 'Pengaturan kunci Turnstile dikelola oleh Super Admin.');
$view_site_key_input_pos = strpos($settings_view, 'name="turnstile_site_key"');
$view_secret_key_input_pos = strpos($settings_view, 'name="turnstile_secret_key"');
system_settings_check(strpos($settings_view, '$can_manage_turnstile = !empty($can_manage_turnstile);') !== FALSE, 'System settings view must normalize the Turnstile capability.');
system_settings_check($view_turnstile_gate_pos !== FALSE && $view_site_key_input_pos !== FALSE && $view_secret_key_input_pos !== FALSE && $view_turnstile_gate_pos < $view_site_key_input_pos && $view_turnstile_gate_pos < $view_secret_key_input_pos, 'Turnstile key inputs must be gated by the Super Admin capability.');
system_settings_check($view_non_super_pos !== FALSE, 'Non-Super Admin users must receive a Turnstile ownership notice.');

foreach ([
    "\$active_menu === 'system_settings'",
    "base_url('assets/css/system-settings.css')",
    'html_escape(base_url(\'assets/css/system-settings.css\'))',
] as $literal) {
    system_settings_check(strpos($header, $literal) !== FALSE, 'System settings stylesheet hook missing: ' . $literal);
}
foreach ([
    './application/views/lpmpi/system_settings/**/*.php',
] as $literal) {
    system_settings_check(strpos($tailwind_config, $literal) !== FALSE, 'Tailwind content discovery missing: ' . $literal);
}
system_settings_check(strpos($package, 'tailwindcss -i assets/css/system-settings.source.css -o assets/css/system-settings.css') !== FALSE, 'System settings Tailwind build target missing.');
foreach (['.tw-bg-slate-900', '.tw-rounded-2xl'] as $literal) {
    system_settings_check(strpos($system_settings_css, $literal) !== FALSE, 'Generated system settings CSS missing: ' . $literal);
}

foreach ([
    '`047_create_system_settings.sql`',
    'profil_lembaga.deskripsi',
    'Cloudflare Turnstile',
    'kegagalan CAPTCHA tidak menambah bucket kegagalan kredensial',
    'Request reset password valid dibatasi 3 per identitas dan 10 per IP dalam 60 menit',
    'php tests/system_settings_regression.php',
] as $literal) {
    system_settings_check(strpos($readme, $literal) !== FALSE, 'README system settings documentation missing: ' . $literal);
}
system_settings_check(strpos($readme, '001-047') !== FALSE || strpos($readme, '001-048') !== FALSE, 'README system settings documentation missing migration range.');

fwrite(STDOUT, "System settings regression checks passed.\n");
