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
$footer = source($root, 'application/views/layouts/footer.php');
$dashboard_controller = source($root, 'application/controllers/Dashboard.php');
$routes = source($root, 'application/config/routes.php');
$organization_controller = source($root, 'application/controllers/lpmpi/Organization.php');

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

check(substr_count($sidebar, "'group' => 'Settings'") === 8, 'Settings group must cover all account, upload setting, and management profile entries.');
check(substr_count($sidebar, "'key' => 'spmi_workspace', 'label' => 'Workspace SPMI', 'icon' => 'fa-laptop-house', 'url' => 'auditee/spmi', 'group' => 'Work'") === 1, 'SPMI workspace menu must be auditee-only.');
check(substr_count($sidebar, "'group' => 'Management'") === 6, 'Management group count changed.');
$master_data_contract = "['key' => 'master_data_prodi_staf', 'label' => 'Master Data Organisasi & Staf', 'icon' => 'fa-graduation-cap', 'url' => 'lpmpi/master-data-prodi-staf', 'group' => 'Management']";
check(substr_count($sidebar, $master_data_contract) === 2, 'Master Data Organisasi & Staf menu must appear once in each management role.');
$organization_contract = "['key' => 'organization', 'label' => 'Struktur Organisasi', 'icon' => 'fa-sitemap', 'url' => 'lpmpi/organization', 'group' => 'Management']";
$standards_contract = "['key' => 'spmi_standards', 'label' => 'Standar SPMI', 'icon' => 'fa-layer-group', 'url' => 'lpmpi/spmi-standards', 'group' => 'Management']";
$super_admin_start = strpos($sidebar, "'super_admin' => [");
$admin_lpmpi_start = strpos($sidebar, "'admin_lpmpi' => [");
$auditor_start = strpos($sidebar, "'auditor' => [");
$auditee_start = strpos($sidebar, "'auditee' => [");
$ppepp_contract = "['key' => 'spmi_ppepp_documents', 'label' => 'Dokumen PPEPP', 'icon' => 'fa-folder-open', 'url' => 'lpmpi/spmi-ppepp-documents', 'group' => 'Management']";
check(strpos($sidebar, $master_data_contract) !== FALSE && strpos($sidebar, $master_data_contract) < strpos($sidebar, $ppepp_contract), 'Master Data Organisasi & Staf must precede PPEPP navigation.');
$master_data_position = strpos($sidebar, $master_data_contract);
check($master_data_position < $auditor_start, 'Master Data Prodi & Staf must be inside management menus.');
check(strpos($sidebar, $master_data_contract, $auditor_start) === FALSE, 'Master Data Prodi & Staf must not be available to auditor.');
check(strpos($sidebar, $master_data_contract, $auditee_start) === FALSE, 'Master Data Prodi & Staf must not be available to auditee.');
check(substr_count($sidebar, $ppepp_contract) === 2, 'PPEPP documents menu must appear once in each management role.');
check($super_admin_start !== FALSE && strpos($sidebar, $ppepp_contract, $super_admin_start) !== FALSE && strpos($sidebar, $ppepp_contract, $super_admin_start) < $admin_lpmpi_start, 'PPEPP menu must be inside super_admin Management menu.');
check($admin_lpmpi_start !== FALSE && strpos($sidebar, $ppepp_contract, $admin_lpmpi_start) !== FALSE && strpos($sidebar, $ppepp_contract, $admin_lpmpi_start) < $auditor_start, 'PPEPP menu must be inside admin_lpmpi Management menu.');
check($auditor_start !== FALSE && strpos($sidebar, $ppepp_contract, $auditor_start) === FALSE, 'PPEPP menu must not be available to auditor.');
check($auditee_start !== FALSE && strpos($sidebar, $ppepp_contract, $auditee_start) === FALSE, 'PPEPP menu must not be available to auditee.');
check(strpos($sidebar, "if (\$menu['key'] === 'spmi_ppepp_documents')") !== FALSE, 'PPEPP must have a dedicated sidebar renderer.');
check(strpos($sidebar, "'key' => 'spmi_standards'") === FALSE && strpos($sidebar, "'key' => 'spmi_audits'") === FALSE, 'Standards and audits must no longer be flat menu entries.');
check(strpos($sidebar, 'ami-nav-ppepp-stage-link') !== FALSE, 'PPEPP stages must be direct navigation links.');
check(strpos($sidebar, 'ami-nav-ppepp-stages') === FALSE, 'PPEPP must not retain its old disclosure submenu.');
check(strpos($sidebar, "foreach (\$ppepp_stages as \$stage_key => \$stage_label)") !== FALSE, 'PPEPP stages must derive from the configured stage group.');
check(strpos($sidebar, "'?stage=' . rawurlencode(\$stage_key) . '&year=' . rawurlencode((string) \$ppepp_selected_year)") !== FALSE, 'PPEPP child links must preserve stage and year query.');
check(strpos($sidebar, "\$stage_is_current = \$ppepp_is_active && \$ppepp_selected_stage !== ''") !== FALSE, 'PPEPP direct-link current state must require selected stage.');
check(strpos($sidebar, "\$evaluasi_is_active = in_array(\$active_menu, ['spmi_standards', 'spmi_audits'], TRUE);") !== FALSE, 'Evaluasi active state must be limited to its two child pages.');
check(substr_count($sidebar, "data-nav-disclosure") === 1 && strpos($sidebar, 'ami-nav-evaluasi') !== FALSE, 'Evaluasi must be the only sidebar disclosure.');
foreach (["<span>Evaluasi</span>", "site_url('lpmpi/spmi-standards')", "site_url('lpmpi/spmi-audits')", 'aria-label="Evaluasi SPMI"', 'class="ami-nav-submenu"', 'aria-expanded=', 'aria-controls=', 'hidden'] as $contract) {
    check(strpos($sidebar, $contract) !== FALSE, 'Evaluasi disclosure contract missing: ' . $contract);
}
check(strpos($sidebar, "\$active_menu === 'spmi_standards' ? ' aria-current=\"page\"' : ''") !== FALSE
    && strpos($sidebar, "\$active_menu === 'spmi_audits' ? ' aria-current=\"page\"' : ''") !== FALSE,
    'Evaluasi children must own current state individually.');
check(substr_count($sidebar, 'Standar SPMI</span>') === 1 && substr_count($sidebar, 'Siklus &amp; Penugasan SPMI</span>') === 1, 'Evaluasi must contain exactly two child links.');
check(substr_count($sidebar, "'key' => 'spmi_ppepp_documents'") === 2
    && strpos($sidebar, "'key' => 'spmi_standards'") === FALSE
    && strpos($sidebar, "'key' => 'spmi_audits'") === FALSE,
    'PPEPP and Evaluasi navigation sources must remain management-only.');
check(substr_count($sidebar, $organization_contract) === 0, 'Organization menu must be hidden from all sidebars.');
check(strpos($routes, "\$route['lpmpi/organization'] = 'lpmpi/Organization/index';") !== FALSE, 'Direct Organization index route must remain available.');
check(strpos($routes, "\$route['lpmpi/organization/unit/store'] = 'lpmpi/Organization/store_unit';") !== FALSE, 'Direct Organization mutation route must remain available.');
check(strpos($organization_controller, 'class Organization extends Admin_Lpmpi_Controller') !== FALSE && strpos($organization_controller, 'public function index()') !== FALSE, 'Direct Organization controller must remain available.');
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
$upload_settings_contract = "['key' => 'upload_size_settings', 'label' => 'Pengaturan Upload', 'icon' => 'fa-upload', 'url' => 'lpmpi/upload-size-settings', 'group' => 'Settings']";
check(substr_count($sidebar, $upload_settings_contract) === 2, 'Upload settings menu must appear once in each management role.');
check(strpos($sidebar, $upload_settings_contract, $super_admin_start) !== FALSE && strpos($sidebar, $upload_settings_contract, $super_admin_start) < $admin_lpmpi_start, 'Upload settings menu must be inside super_admin Settings menu.');
check(strpos($sidebar, $upload_settings_contract, $admin_lpmpi_start) !== FALSE && strpos($sidebar, $upload_settings_contract, $admin_lpmpi_start) < $auditor_start, 'Upload settings menu must be inside admin_lpmpi Settings menu.');
check(strpos($sidebar, $upload_settings_contract, $auditor_start) === FALSE || strpos($sidebar, $upload_settings_contract, $auditor_start) > $auditee_start, 'Upload settings menu must not be available to auditor.');
check(strpos($sidebar, $upload_settings_contract, $auditee_start) === FALSE, 'Upload settings menu must not be available to auditee.');
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
check(strpos($footer, "if (link.hasAttribute('data-nav-disclosure')) return;") !== FALSE, 'PPEPP disclosure must not close the mobile drawer.');
check(strpos($footer, "disclosure.setAttribute('aria-expanded', expanded ? 'false' : 'true');") !== FALSE && strpos($footer, 'submenu.hidden = expanded;') !== FALSE, 'PPEPP disclosure JS must toggle expanded and hidden state.');
check(strpos($footer, "event.key === 'Escape'") !== FALSE && strpos($footer, "window.addEventListener('resize'") !== FALSE && strpos($footer, 'ami-sidebar-lock') !== FALSE, 'Existing mobile drawer scripts must remain present.');
check(strpos($header, 'min-height: 44px;') !== FALSE, 'Sidebar links need 44px touch targets.');
check(strpos($header, '.ami-nav-link:focus') !== FALSE && strpos($header, '.ami-nav-link.active') !== FALSE, 'Sidebar focus and active states must remain explicit.');
check(strpos($header, '.ami-sidebar .ami-logout') !== FALSE, 'Mobile sidebar logout spacing must remain explicit.');

fwrite(STDOUT, "Sidebar navigation regression checks passed.\n");
