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

$menus = [
    'super_admin' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => 'dashboard'],
        ['key' => 'spmi_workspace', 'label' => 'Workspace SPMI', 'icon' => 'fa-laptop-house', 'url' => 'auditee/spmi'],
        ['key' => 'account', 'label' => 'Akun Saya', 'icon' => 'fa-user-circle', 'url' => 'account'],
        ['key' => 'periode', 'label' => 'Periode Audit', 'icon' => 'fa-calendar-alt', 'url' => 'periode'],
        ['key' => 'users', 'label' => 'Data Pengguna', 'icon' => 'fa-users', 'url' => 'users'],
        ['key' => 'akun', 'label' => 'Akun Auditee/Auditor', 'icon' => 'fa-user-cog', 'url' => 'lpmpi/akun'],
        ['key' => 'standar', 'label' => 'Data Standar', 'icon' => 'fa-award', 'url' => 'standar'],
        ['key' => 'pertanyaan', 'label' => 'Data Pertanyaan', 'icon' => 'fa-tasks', 'url' => 'pertanyaan'],
        ['key' => 'instrumen', 'label' => 'Instrumen Standar', 'icon' => 'fa-file-upload', 'url' => 'lpmpi/instrumen'],
        ['key' => 'tugas_audit', 'label' => 'Tugas Audit', 'icon' => 'fa-clipboard-list', 'url' => 'tugas_audit'],
        ['key' => 'penugasan', 'label' => 'Penugasan Auditor', 'icon' => 'fa-clipboard-list', 'url' => 'lpmpi/penugasan'],
        ['key' => 'penetapan', 'label' => 'Penetapan', 'icon' => 'fa-gavel', 'url' => 'lpmpi/penetapan'],
        ['key' => 'hasil_audit', 'label' => 'Hasil Audit', 'icon' => 'fa-chart-bar', 'url' => 'tugas_audit/hasil'],
        ['key' => 'laporan', 'label' => 'Laporan & Statistik', 'icon' => 'fa-chart-pie', 'url' => 'lpmpi/laporan'],
        ['key' => 'profil', 'label' => 'Profil Lembaga', 'icon' => 'fa-university', 'url' => 'profil'],
    ],
    'admin_lpmpi' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => 'dashboard'],
        ['key' => 'account', 'label' => 'Akun Saya', 'icon' => 'fa-user-circle', 'url' => 'account'],
        ['key' => 'periode', 'label' => 'Periode Audit', 'icon' => 'fa-calendar-alt', 'url' => 'periode'],
        ['key' => 'akun', 'label' => 'Akun Auditee/Auditor', 'icon' => 'fa-user-cog', 'url' => 'lpmpi/akun'],
        ['key' => 'instrumen', 'label' => 'Instrumen Standar', 'icon' => 'fa-file-upload', 'url' => 'lpmpi/instrumen'],
        ['key' => 'penugasan', 'label' => 'Penugasan Auditor', 'icon' => 'fa-clipboard-list', 'url' => 'lpmpi/penugasan'],
        ['key' => 'penetapan', 'label' => 'Penetapan', 'icon' => 'fa-gavel', 'url' => 'lpmpi/penetapan'],
        ['key' => 'laporan', 'label' => 'Laporan & Statistik', 'icon' => 'fa-chart-pie', 'url' => 'lpmpi/laporan'],
        ['key' => 'profil', 'label' => 'Profil Lembaga', 'icon' => 'fa-university', 'url' => 'profil'],
    ],
    'auditor' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => 'dashboard'],
        ['key' => 'account', 'label' => 'Akun Saya', 'icon' => 'fa-user-circle', 'url' => 'account'],
        ['key' => 'tugas_audit', 'label' => 'Tugas Audit', 'icon' => 'fa-clipboard-list', 'url' => 'auditor/tugas'],
        ['key' => 'penilaian', 'label' => 'Penilaian Auditee', 'icon' => 'fa-star', 'url' => 'auditor/penilaian'],
    ],
    'auditee' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => 'dashboard'],
        ['key' => 'account', 'label' => 'Akun Saya', 'icon' => 'fa-user-circle', 'url' => 'account'],
        ['key' => 'tugas_saya', 'label' => 'Tugas Saya', 'icon' => 'fa-clipboard-list', 'url' => 'auditee/tugas'],
        ['key' => 'pengisian', 'label' => 'Pengisian Audit', 'icon' => 'fa-pen', 'url' => 'auditee/tugas?status=belum_diisi'],
        ['key' => 'hasil_penilaian', 'label' => 'Hasil Penilaian', 'icon' => 'fa-eye', 'url' => 'auditee/tugas?status=dinilai'],
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

check(substr_count($sidebar, "'group' => 'Settings'") === 6, 'Settings group must cover all account and management profile entries.');
check(substr_count($sidebar, "'key' => 'spmi_workspace', 'label' => 'Workspace SPMI', 'icon' => 'fa-laptop-house', 'url' => 'auditee/spmi', 'group' => 'Work'") === 1, 'SPMI workspace menu must be auditee-only.');
check(substr_count($sidebar, "'group' => 'Management'") === 21, 'Management group count changed.');
check(substr_count($sidebar, "'key' => 'organization', 'label' => 'Struktur Organisasi', 'icon' => 'fa-sitemap', 'url' => 'lpmpi/organization'") === 2, 'Organization menu must be shared by management roles.');
check(substr_count($sidebar, "'key' => 'spmi_indicators', 'label' => 'Indikator SPMI', 'icon' => 'fa-chart-line', 'url' => 'lpmpi/spmi-indicators', 'group' => 'Management'") === 2, 'SPMI indicator menu must be shared by two management roles.');
check(substr_count($sidebar, "'key' => 'spmi_master', 'label' => 'Import/Export Master SPMI', 'icon' => 'fa-file-excel', 'url' => 'lpmpi/spmi-master', 'group' => 'Management'") === 2, 'SPMI master menu must be shared by two management roles.');
check(substr_count($sidebar, "'key' => 'spmi_instruments', 'label' => 'Instrumen Audit SPMI', 'icon' => 'fa-clipboard-check', 'url' => 'lpmpi/spmi-instruments', 'group' => 'Management'") === 2, 'SPMI instrument menu must be shared by two management roles.');
check(substr_count($sidebar, "'group' => 'Insights'") === 16, 'Insights group count changed.');
check(substr_count($sidebar, "'key' => 'spmi_dashboard', 'label' => 'Dashboard SPMI', 'icon' => 'fa-tachometer-alt', 'url' => 'lpmpi/spmi-dashboard', 'group' => 'Insights'") === 2, 'Management SPMI dashboard menu must appear twice.');
check(substr_count($sidebar, "'key' => 'spmi_auditor_dashboard', 'label' => 'Dashboard SPMI', 'icon' => 'fa-tachometer-alt', 'url' => 'auditor/spmi-dashboard', 'group' => 'Overview'") === 1, 'Auditor SPMI dashboard menu must appear once.');
check(substr_count($sidebar, "'key' => 'spmi_auditee_dashboard', 'label' => 'Dashboard SPMI', 'icon' => 'fa-tachometer-alt', 'url' => 'auditee/spmi-dashboard', 'group' => 'Overview'") === 1, 'Auditee SPMI dashboard menu must appear once.');
check(substr_count($sidebar, "'key' => 'spmi_rtm', 'label' => 'RTM SPMI', 'icon' => 'fa-users-cog', 'url' => 'lpmpi/spmi-rtm', 'group' => 'Insights'") === 2, 'RTM SPMI menu must be shared by management roles.');
check(substr_count($sidebar, "'key' => 'spmi_ppepp_recap', 'label' => 'Rekap PPEPP SPMI', 'icon' => 'fa-project-diagram', 'url' => 'lpmpi/spmi-recap', 'group' => 'Insights'") === 2, 'PPEPP recap menu must be shared by management roles.');
check(substr_count($sidebar, "'key' => 'legacy_ami_archive', 'label' => 'Arsip AMI Legacy', 'icon' => 'fa-archive', 'url' => 'lpmpi/legacy-ami-archive', 'group' => 'Insights'") === 2, 'Legacy AMI archive menu must be management-only.');
check(strpos($sidebar, "'group' => 'Pengaturan'") === FALSE && strpos($sidebar, "'Pengaturan'") === FALSE, 'Pengaturan group literal must be removed.');
check(strpos($sidebar, '$active_menu === $menu[\'key\']') !== FALSE, 'Active menu comparison must remain exact.');
check(strpos($sidebar, "isset(" . '$menu_badges[$menu[\'key\']]' . ") && (int) " . '$menu_badges[$menu[\'key\']]' . " > 0") !== FALSE, 'Badges must remain positive-only.');
check(strpos($sidebar, "form_open('auth/logout');") !== FALSE
    && strpos($sidebar, "form_open('auth/logout', ['class' => 'mb-0'])") !== FALSE,
    'Sidebar and account dropdown must both retain POST logout forms.');
check(strpos($sidebar, "site_url('account/photo')") !== FALSE && strpos($sidebar, 'ami-account-menu') !== FALSE, 'Account photo and dropdown must remain available.');
check(strpos($sidebar, 'data-theme-toggle') !== FALSE, 'Theme toggle hook must remain.');
foreach (['ami-sidebar', 'data-sidebar-toggle', 'data-sidebar-close', 'ami-sidebar-overlay'] as $hook) {
    check(strpos($sidebar, $hook) !== FALSE, 'Mobile sidebar hook missing: ' . $hook);
}
check(strpos($header, 'min-height: 44px;') !== FALSE, 'Sidebar links need 44px touch targets.');
check(strpos($header, '.ami-nav-link:focus') !== FALSE && strpos($header, '.ami-nav-link.active') !== FALSE, 'Sidebar focus and active states must remain explicit.');
check(strpos($header, '.ami-sidebar .ami-logout') !== FALSE, 'Mobile sidebar logout spacing must remain explicit.');

fwrite(STDOUT, "Sidebar navigation regression checks passed.\n");
