<?php

$root = dirname(__DIR__);

function source($root, $path)
{
    $contents = file_get_contents($root . DIRECTORY_SEPARATOR . $path);
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }
    return $contents;
}

function check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$sidebar = source($root, 'application/views/layouts/sidebar.php');
$header = source($root, 'application/views/layouts/header.php');
$dashboard_controller = source($root, 'application/controllers/Dashboard.php');

$menus = [
    'super_admin' => [
        ['key' => 'spmi_workspace', 'label' => 'Workspace SPMI', 'icon' => 'fa-laptop-house', 'url' => 'auditee/spmi'],
        ['key' => 'account', 'label' => 'Akun Saya', 'icon' => 'fa-user-circle', 'url' => 'account'],
        ['key' => 'profil', 'label' => 'Profil Lembaga', 'icon' => 'fa-university', 'url' => 'profil'],
    ],
    'admin_lpmpi' => [
        ['key' => 'account', 'label' => 'Akun Saya', 'icon' => 'fa-user-circle', 'url' => 'account'],
        ['key' => 'profil', 'label' => 'Profil Lembaga', 'icon' => 'fa-university', 'url' => 'profil'],
    ],
    'auditor' => [
        ['key' => 'account', 'label' => 'Akun Saya', 'icon' => 'fa-user-circle', 'url' => 'account'],
    ],
    'auditee' => [
        ['key' => 'account', 'label' => 'Akun Saya', 'icon' => 'fa-user-circle', 'url' => 'account'],
    ],
];

foreach ($menus as $role => $entries) {
    foreach ($entries as $entry) {
        $contract = "['key' => '{$entry['key']}', 'label' => '{$entry['label']}', 'icon' => '{$entry['icon']}', 'url' => '{$entry['url']}'";
        $expected_count = 0;
        foreach ($menus as $role_entries) {
            foreach ($role_entries as $role_entry) {
                if ($role_entry === $entry) {
                    $expected_count++;
                }
            }
        }
        check(substr_count($sidebar, $contract) === $expected_count, $role . ' menu contract changed: ' . $entry['key']);
    }
}

check(substr_count($sidebar, "['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => 'dashboard'") === 0, 'Legacy Dashboard sidebar entries must be hidden for all roles.');
check(strpos($dashboard_controller, 'class Dashboard extends CI_Controller') !== FALSE && strpos($dashboard_controller, 'public function index()') !== FALSE, 'Direct Dashboard controller must remain available.');

foreach ([
    "'super_admin' => [\n        ['key' => 'spmi_dashboard', 'label' => 'Dashboard SPMI', 'icon' => 'fa-tachometer-alt', 'url' => 'lpmpi/spmi-dashboard', 'group' => 'Overview'],",
    "'admin_lpmpi' => [\n        ['key' => 'spmi_dashboard', 'label' => 'Dashboard SPMI', 'icon' => 'fa-tachometer-alt', 'url' => 'lpmpi/spmi-dashboard', 'group' => 'Overview'],",
    "'auditor' => [\n        ['key' => 'spmi_auditor_dashboard', 'label' => 'Dashboard SPMI', 'icon' => 'fa-tachometer-alt', 'url' => 'auditor/spmi-dashboard', 'group' => 'Overview'],",
    "'auditee' => [\n        ['key' => 'spmi_auditee_dashboard', 'label' => 'Dashboard SPMI', 'icon' => 'fa-tachometer-alt', 'url' => 'auditee/spmi-dashboard', 'group' => 'Overview'],",
] as $first_menu_contract) {
    check(strpos($sidebar, $first_menu_contract) !== FALSE, 'Each role must begin with its SPMI dashboard menu.');
}

check(substr_count($sidebar, "'group' => 'Settings'") === 6, 'Settings group must cover all account and management profile entries.');
check(substr_count($sidebar, "'key' => 'spmi_workspace', 'label' => 'Workspace SPMI', 'icon' => 'fa-laptop-house', 'url' => 'auditee/spmi', 'group' => 'Work'") === 1, 'SPMI workspace menu must be auditee-only.');
check(substr_count($sidebar, "'group' => 'Management'") === 10, 'Management group count changed.');
$ppepp_contract = "['key' => 'spmi_ppepp_documents', 'label' => 'Dokumen PPEPP', 'icon' => 'fa-folder-open', 'url' => 'lpmpi/spmi-ppepp-documents', 'group' => 'Management']";
check(substr_count($sidebar, $ppepp_contract) === 2, 'PPEPP documents menu must appear once in each management role.');
$super_admin_start = strpos($sidebar, "'super_admin' => [");
$admin_lpmpi_start = strpos($sidebar, "'admin_lpmpi' => [");
$auditor_start = strpos($sidebar, "'auditor' => [");
$auditee_start = strpos($sidebar, "'auditee' => [");
check($super_admin_start !== FALSE && strpos($sidebar, $ppepp_contract, $super_admin_start) !== FALSE && strpos($sidebar, $ppepp_contract, $super_admin_start) < $admin_lpmpi_start, 'PPEPP menu must be inside super_admin Management menu.');
check($admin_lpmpi_start !== FALSE && strpos($sidebar, $ppepp_contract, $admin_lpmpi_start) !== FALSE && strpos($sidebar, $ppepp_contract, $admin_lpmpi_start) < $auditor_start, 'PPEPP menu must be inside admin_lpmpi Management menu.');
check($auditor_start !== FALSE && strpos($sidebar, $ppepp_contract, $auditor_start) === FALSE, 'PPEPP menu must not be available to auditor.');
check($auditee_start !== FALSE && strpos($sidebar, $ppepp_contract, $auditee_start) === FALSE, 'PPEPP menu must not be available to auditee.');
check(substr_count($sidebar, "'key' => 'organization', 'label' => 'Struktur Organisasi', 'icon' => 'fa-sitemap', 'url' => 'lpmpi/organization'") === 2, 'Organization menu must be shared by management roles.');
check(strpos($sidebar, "'key' => 'spmi_indicators'") === FALSE, 'SPMI indicator menu must be removed from sidebar.');
check(strpos($sidebar, "'key' => 'spmi_master'") === FALSE, 'SPMI master menu must be removed from sidebar.');
check(strpos($sidebar, "'key' => 'spmi_instruments'") === FALSE, 'Retired SPMI instrument menu must not remain in the sidebar.');
check(substr_count($sidebar, "'key' => 'users', 'label' => 'Manajemen Pengguna', 'icon' => 'fa-users', 'url' => 'users', 'group' => 'Management'") === 2, 'Users menu must be shared by management roles.');
check(strpos($sidebar, "'key' => 'akun', 'label' => 'Akun Auditor & Auditee'") === FALSE, 'Akun menu must be removed after consolidation.');
check(substr_count($sidebar, "'group' => 'Insights'") === 6, 'Insights group count changed.');
check(substr_count($sidebar, "'key' => 'spmi_dashboard', 'label' => 'Dashboard SPMI', 'icon' => 'fa-tachometer-alt', 'url' => 'lpmpi/spmi-dashboard', 'group' => 'Overview'") === 2, 'Management SPMI dashboard menu must appear twice under Overview.');
check(substr_count($sidebar, "'key' => 'spmi_auditor_dashboard', 'label' => 'Dashboard SPMI', 'icon' => 'fa-tachometer-alt', 'url' => 'auditor/spmi-dashboard', 'group' => 'Overview'") === 1, 'Auditor SPMI dashboard menu must appear once.');
check(substr_count($sidebar, "'key' => 'spmi_auditee_dashboard', 'label' => 'Dashboard SPMI', 'icon' => 'fa-tachometer-alt', 'url' => 'auditee/spmi-dashboard', 'group' => 'Overview'") === 1, 'Auditee SPMI dashboard menu must appear once.');
check(substr_count($sidebar, "'key' => 'spmi_rtm', 'label' => 'RTM SPMI', 'icon' => 'fa-users-cog', 'url' => 'lpmpi/spmi-rtm', 'group' => 'Insights'") === 2, 'RTM SPMI menu must be shared by management roles.');
check(strpos($sidebar, "'key' => 'spmi_ppepp_recap'") === FALSE, 'PPEPP recap menu must be removed from sidebar.');
foreach (['tugas_audit', 'penugasan', 'penetapan', 'periode', 'standar', 'pertanyaan', 'instrumen', 'hasil_audit', 'laporan', 'legacy_ami_archive', 'penilaian', 'tugas_saya', 'pengisian', 'hasil_penilaian'] as $legacy_key) {
    check(strpos($sidebar, "'key' => '{$legacy_key}'") === FALSE, 'Legacy sidebar menu must be hidden: ' . $legacy_key);
}
check(strpos($sidebar, "'group' => 'Pengaturan'") === FALSE && strpos($sidebar, "'Pengaturan'") === FALSE, 'Pengaturan group literal must be removed.');
check(strpos($sidebar, '$active_menu === $menu[\'key\']') !== FALSE, 'Active menu comparison must remain exact.');
check(strpos($sidebar, "isset(" . '$menu_badges[$menu[\'key\']]' . ") && (int) " . '$menu_badges[$menu[\'key\']]' . " > 0") !== FALSE, 'Badges must remain positive-only.');
foreach (['application/controllers/Spmi_auditor_workspace.php', 'application/controllers/Spmi_auditee_workspace.php'] as $path) {
    $controller = source($root, $path);
    check(strpos($controller, "'menu_badges' =>") !== FALSE, 'M17-07E workspace sidebar badge missing: ' . $path);
}
check(strpos($sidebar, "form_open('auth/logout');") !== FALSE
    && strpos($sidebar, "form_open('auth/logout', ['class' => 'mb-0'])") !== FALSE,
    'Sidebar and account dropdown must both retain POST logout forms.');
check(strpos($sidebar, "site_url('account/photo')") !== FALSE && strpos($sidebar, 'ami-account-menu') !== FALSE, 'Account photo and dropdown must remain available.');
check(strpos($sidebar, 'data-theme-toggle') === FALSE, 'Theme toggle must remain removed for light-only UI.');
foreach (['ami-sidebar', 'data-sidebar-toggle', 'data-sidebar-close', 'ami-sidebar-overlay'] as $hook) {
    check(strpos($sidebar, $hook) !== FALSE, 'Mobile sidebar hook missing: ' . $hook);
}
check(strpos($header, 'min-height: 44px;') !== FALSE, 'Sidebar links need 44px touch targets.');
check(strpos($header, '.ami-nav-link:focus') !== FALSE && strpos($header, '.ami-nav-link.active') !== FALSE, 'Sidebar focus and active states must remain explicit.');
check(strpos($header, '.ami-sidebar .ami-logout') !== FALSE, 'Mobile sidebar logout spacing must remain explicit.');

fwrite(STDOUT, "Sidebar navigation regression checks passed.\n");
