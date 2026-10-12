<?php

$root = dirname(__DIR__);

function organization_branding_source($path)
{
    global $root;
    $contents = file_get_contents($root . DIRECTORY_SEPARATOR . $path);
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }
    return $contents;
}

function organization_branding_check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$logo_service = organization_branding_source('application/services/Organization_logo_service.php');

foreach ([
    "base_url('uploads/profil/' . rawurlencode(\$profile->logo_path))",
    "(string) \$profile->logo_url",
    "base_url('assets/img/logo-2.png')",
    'public function public_url($profile)',
    "return \$preview['local_url'] !== '' ? \$preview['local_url'] : \$preview['fallback_url'];",
] as $literal) {
    organization_branding_check(strpos($logo_service, $literal) !== FALSE, 'Logo service precedence contract missing: ' . $literal);
}

organization_branding_check(strpos($logo_service, "'local_url' => \$local_url") !== FALSE, 'Logo preview must expose local URL separately.');
organization_branding_check(strpos($logo_service, "'fallback_url' => \$profile && !empty(\$profile->logo_url) ? (string) \$profile->logo_url : base_url('assets/img/logo-2.png')") !== FALSE, 'Logo preview fallback must prefer remote URL over default asset.');
organization_branding_check(strpos($logo_service, "return \$preview['local_url'] !== '' ? \$preview['local_url'] : \$preview['fallback_url'];") !== FALSE, 'Resolved public logo must prefer local URL over fallback URL.');

$my_controller = organization_branding_source('application/core/MY_Controller.php');
foreach ([
    "APPPATH . 'services/Organization_logo_service.php'",
    'new Organization_logo_service()',
    "\$this->load->model('System_settings_model')",
    "->public_url(\$this->System_settings_model->profile())",
    "\$this->load->vars(['organization_logo_url' =>",
] as $literal) {
    organization_branding_check(strpos($my_controller, $literal) !== FALSE, 'MY_Controller must populate shared organization_logo_url loader var from System_settings_model::profile(): ' . $literal);
}
organization_branding_check(substr_count($my_controller, "'organization_logo_url'") === 1, 'MY_Controller must define the protected organization logo loader key once.');

$auth_guard = organization_branding_source('application/libraries/Auth_guard.php');
foreach ([
    "APPPATH . 'services/Organization_logo_service.php'",
    'new Organization_logo_service()',
    "\$this->ci->load->model('System_settings_model')",
    "->public_url(\$this->ci->System_settings_model->profile())",
    "\$this->ci->load->vars(['organization_logo_url' =>",
] as $literal) {
    organization_branding_check(strpos($auth_guard, $literal) !== FALSE, 'Auth_guard must populate identical organization_logo_url loader var after authentication: ' . $literal);
}
organization_branding_check(substr_count($auth_guard, "'organization_logo_url'") === 1, 'Auth_guard must define the protected organization logo loader key once per request.');
organization_branding_check(strpos($auth_guard, 'function check()') < strpos($auth_guard, "'organization_logo_url'"), 'Auth_guard branding must run after authentication succeeds.');

foreach ([
    'application/views/auth/login.php',
    'application/views/auth/forgot_password.php',
    'application/views/layouts/sidebar.php',
    'application/views/dashboard/super_admin.php',
    'application/views/dashboard/auditor.php',
    'application/views/dashboard/auditee.php',
    'application/views/lpmpi/spmi_management_dashboard/index.php',
    'application/views/spmi_auditor_dashboard/index.php',
    'application/views/spmi_auditee_dashboard/index.php',
] as $view_path) {
    $view = organization_branding_source($view_path);
    organization_branding_check(stripos($view, 'profil_lembaga') === FALSE, 'Template must not query organization profile directly: ' . $view_path);
    organization_branding_check(stripos($view, '$this->db') === FALSE, 'Template must not perform database access: ' . $view_path);
    organization_branding_check(strpos($view, 'html_escape($organization_logo_url)') !== FALSE, 'Template must consume escaped organization_logo_url: ' . $view_path);
    organization_branding_check(stripos($view, 'logo-2.png') === FALSE, 'Template must not hard-code the default organization logo: ' . $view_path);
}

$sidebar = organization_branding_source('application/views/layouts/sidebar.php');
organization_branding_check(substr_count($sidebar, 'html_escape($organization_logo_url)') === 1, 'Sidebar must render only the escaped shared organization_logo_url.');
organization_branding_check(strpos($sidebar, '$organization_logo_url =') === FALSE, 'Sidebar must not resolve or mutate organization_logo_url locally.');

foreach ([
    'application/controllers/lpmpi/Master_data_prodi_staf.php',
    'application/controllers/Spmi_auditee_workspace.php',
] as $controller_path) {
    $controller = organization_branding_source($controller_path);
    organization_branding_check(strpos($controller, "'organization_logo_url'") === FALSE, 'Protected shared-layout controller must rely on loader var, not manual branding data: ' . $controller_path);
    organization_branding_check(strpos($controller, 'Organization_logo_service') === FALSE, 'Protected shared-layout controller must not instantiate Organization_logo_service manually: ' . $controller_path);
    organization_branding_check(strpos($controller, 'System_settings_model') === FALSE, 'Protected shared-layout controller must not load System_settings_model only for branding: ' . $controller_path);
}

$auth_controller = organization_branding_source('application/controllers/Auth.php');
organization_branding_check(strpos($auth_controller, 'private function render_auth_view($view, array $data = [])') !== FALSE, 'Auth must use a shared private auth render path.');
organization_branding_check(strpos($auth_controller, "\$this->render_auth_view('auth/login', \$data);") !== FALSE, 'Login render must pass through branding seam.');
organization_branding_check(strpos($auth_controller, "\$this->render_auth_view('auth/forgot_password');") !== FALSE, 'Forgot-password render must pass through branding seam.');
organization_branding_check(substr_count($auth_controller, "'organization_logo_url'") === 1, 'Auth should define the organization logo view key in one shared place.');
organization_branding_check(strpos($auth_controller, '$profile = $this->System_settings_model->profile();') !== FALSE, 'Auth shared render path must load the organization profile once.');
organization_branding_check(strpos($auth_controller, "\$data['organization_description'] =") !== FALSE, 'Auth shared render path must provide organization_description to public auth views.');
organization_branding_check(strpos($auth_controller, "trim((string) \$profile->deskripsi)") !== FALSE, 'Auth shared render path must normalize blank organization descriptions.');
organization_branding_check(strpos($auth_controller, 'turnstile_public_settings()') !== FALSE && strpos($auth_controller, 'verify_turnstile_response(') !== FALSE, 'Auth Turnstile behavior must remain present.');

foreach ([
    'application/views/auth/login.php' => 'Ruang kerja terpadu untuk pelaksanaan, penilaian, dan tindak lanjut Sistem Penjaminan Mutu Internal perguruan tinggi.',
    'application/views/auth/forgot_password.php' => 'Gunakan email institusi terdaftar untuk menerima tautan resmi pengaturan ulang kata sandi.',
] as $view_path => $fallback_copy) {
    $view = organization_branding_source($view_path);
    organization_branding_check(strpos($view, 'html_escape($organization_description)') !== FALSE, 'Public auth view must render escaped organization_description: ' . $view_path);
    organization_branding_check(strpos($view, $fallback_copy) !== FALSE, 'Public auth view must retain its static description fallback: ' . $view_path);
}

foreach ([
    'application/controllers/Dashboard.php',
    'application/controllers/lpmpi/Spmi_management_dashboard.php',
    'application/controllers/Spmi_auditor_dashboard.php',
    'application/controllers/Spmi_auditee_dashboard.php',
] as $controller_path) {
    $controller = organization_branding_source($controller_path);
    organization_branding_check(strpos($controller, "APPPATH . 'services/Organization_logo_service.php'") === FALSE, 'Dashboard controller must stop manual Organization_logo_service injection: ' . $controller_path);
    organization_branding_check(strpos($controller, 'System_settings_model') === FALSE, 'Dashboard controller must stop manual System_settings_model branding reads: ' . $controller_path);
    organization_branding_check(strpos($controller, "'organization_logo_url'") === FALSE, 'Dashboard controller must rely on shared loader organization_logo_url: ' . $controller_path);
    organization_branding_check(strpos($controller, '->public_url($this->System_settings_model->profile())') === FALSE, 'Dashboard controller must not resolve branding manually: ' . $controller_path);
    organization_branding_check(strpos($controller, 'get_profile()') === FALSE, 'Controller branding path must not call nonexistent get_profile(): ' . $controller_path);
}

organization_branding_check(strpos($auth_controller, '$this->load->view(') === FALSE || strpos($auth_controller, '$this->load->view($view, $data);') !== FALSE, 'Auth direct renders must remain behind shared render helper for login/forgot pages.');

fwrite(STDOUT, "Organization branding regression checks passed.\n");
