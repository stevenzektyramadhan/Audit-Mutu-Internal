<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$role = $this->session->userdata('role');
$nama = $this->session->userdata('nama');
$profile_photo_path = $this->session->userdata('profile_photo_path');
$active_menu = isset($active_menu) ? $active_menu : 'dashboard';
$menu_badges = isset($menu_badges) ? $menu_badges : [];
$menus = [
    'super_admin' => [
        ['key' => 'spmi_dashboard', 'label' => 'Dashboard SPMI', 'icon' => 'fa-tachometer-alt', 'url' => 'lpmpi/spmi-dashboard', 'group' => 'Overview'],
        ['key' => 'users', 'label' => 'Manajemen Pengguna', 'icon' => 'fa-users', 'url' => 'users', 'group' => 'Management'],
        ['key' => 'master_data_prodi_staf', 'label' => 'Master Data Organisasi & Staf', 'icon' => 'fa-graduation-cap', 'url' => 'lpmpi/master-data-prodi-staf', 'group' => 'Management'],
        ['key' => 'spmi_ppepp_documents', 'label' => 'Dokumen PPEPP', 'icon' => 'fa-folder-open', 'url' => 'lpmpi/spmi-ppepp-documents', 'group' => 'Management'],
        ['key' => 'spmi_reports', 'label' => 'Laporan SPMI', 'icon' => 'fa-file-alt', 'url' => 'lpmpi/spmi-reports', 'group' => 'Insights'],
        ['key' => 'spmi_rtm', 'label' => 'RTM SPMI', 'icon' => 'fa-users-cog', 'url' => 'lpmpi/spmi-rtm', 'group' => 'Insights'],
        ['key' => 'spmi_follow_ups', 'label' => 'Tindak Lanjut RTM', 'icon' => 'fa-tasks', 'url' => 'lpmpi/spmi-follow-ups', 'group' => 'Insights'],
        ['key' => 'account', 'label' => 'Akun Saya', 'icon' => 'fa-user-circle', 'url' => 'account', 'group' => 'Settings'],
        ['key' => 'upload_size_settings', 'label' => 'Pengaturan Upload', 'icon' => 'fa-upload', 'url' => 'lpmpi/upload-size-settings', 'group' => 'Settings'],
        ['key' => 'profil', 'label' => 'Profil Lembaga', 'icon' => 'fa-university', 'url' => 'profil', 'group' => 'Settings'],
    ],
    'admin_lpmpi' => [
        ['key' => 'spmi_dashboard', 'label' => 'Dashboard SPMI', 'icon' => 'fa-tachometer-alt', 'url' => 'lpmpi/spmi-dashboard', 'group' => 'Overview'],
        ['key' => 'users', 'label' => 'Manajemen Pengguna', 'icon' => 'fa-users', 'url' => 'users', 'group' => 'Management'],
        ['key' => 'master_data_prodi_staf', 'label' => 'Master Data Organisasi & Staf', 'icon' => 'fa-graduation-cap', 'url' => 'lpmpi/master-data-prodi-staf', 'group' => 'Management'],
        ['key' => 'spmi_ppepp_documents', 'label' => 'Dokumen PPEPP', 'icon' => 'fa-folder-open', 'url' => 'lpmpi/spmi-ppepp-documents', 'group' => 'Management'],
        ['key' => 'spmi_reports', 'label' => 'Laporan SPMI', 'icon' => 'fa-file-alt', 'url' => 'lpmpi/spmi-reports', 'group' => 'Insights'],
        ['key' => 'spmi_rtm', 'label' => 'RTM SPMI', 'icon' => 'fa-users-cog', 'url' => 'lpmpi/spmi-rtm', 'group' => 'Insights'],
        ['key' => 'spmi_follow_ups', 'label' => 'Tindak Lanjut RTM', 'icon' => 'fa-tasks', 'url' => 'lpmpi/spmi-follow-ups', 'group' => 'Insights'],
        ['key' => 'account', 'label' => 'Akun Saya', 'icon' => 'fa-user-circle', 'url' => 'account', 'group' => 'Settings'],
        ['key' => 'upload_size_settings', 'label' => 'Pengaturan Upload', 'icon' => 'fa-upload', 'url' => 'lpmpi/upload-size-settings', 'group' => 'Settings'],
        ['key' => 'profil', 'label' => 'Profil Lembaga', 'icon' => 'fa-university', 'url' => 'profil', 'group' => 'Settings'],
    ],
    'auditor' => [
        ['key' => 'spmi_auditor_dashboard', 'label' => 'Dashboard SPMI', 'icon' => 'fa-tachometer-alt', 'url' => 'auditor/spmi-dashboard', 'group' => 'Overview'],
        ['key' => 'spmi_assessment', 'label' => 'Penilaian SPMI', 'icon' => 'fa-clipboard-check', 'url' => 'auditor/spmi', 'group' => 'Work'],
        ['key' => 'account', 'label' => 'Akun Saya', 'icon' => 'fa-user-circle', 'url' => 'account', 'group' => 'Settings'],
    ],
    'auditee' => [
        ['key' => 'spmi_auditee_dashboard', 'label' => 'Dashboard SPMI', 'icon' => 'fa-tachometer-alt', 'url' => 'auditee/spmi-dashboard', 'group' => 'Overview'],
        ['key' => 'spmi_workspace', 'label' => 'Workspace SPMI', 'icon' => 'fa-laptop-house', 'url' => 'auditee/spmi', 'group' => 'Work'],
        ['key' => 'account', 'label' => 'Akun Saya', 'icon' => 'fa-user-circle', 'url' => 'account', 'group' => 'Settings'],
    ],
];
$page_title = isset($page_title) ? $page_title : 'Dashboard';
$page_subtitle = isset($page_subtitle) ? $page_subtitle : '';
$current_menus = isset($menus[$role]) ? $menus[$role] : [];
$ppepp_stages = $this->config->item('spmi_ppepp_stages', 'spmi_ppepp');
if (empty($ppepp_stages) || !is_array($ppepp_stages)) {
    $this->config->load('spmi_ppepp', TRUE, TRUE);
    $ppepp_stages = $this->config->item('spmi_ppepp_stages', 'spmi_ppepp');
}
if (empty($ppepp_stages) || !is_array($ppepp_stages)) {
    $ppepp_stages = [
        'penetapan' => 'Penetapan',
        'pelaksanaan' => 'Pelaksanaan',
        'pengendalian' => 'Pengendalian',
        'peningkatan' => 'Peningkatan',
    ];
}
$ppepp_selected_stage = isset($selected_stage) ? (string) $selected_stage : '';
$ppepp_selected_year = isset($selected_year) ? (int) $selected_year : (int) date('Y');
$ppepp_is_active = $active_menu === 'spmi_ppepp_documents';
$evaluasi_is_active = in_array($active_menu, ['spmi_standards', 'spmi_audits'], TRUE);
$name_parts = preg_split('/\s+/', trim((string) $nama));
$initial = '';
foreach (array_slice($name_parts, 0, 2) as $name_part) {
    if ($name_part !== '') {
        $initial .= strtoupper(substr($name_part, 0, 1));
    }
}
if ($initial === '') {
    $initial = 'A';
}
?>
<aside class="ami-sidebar" id="ami-sidebar" aria-label="Navigasi utama">
    <div class="ami-brand">
        <a href="<?= site_url(); ?>" class="ami-brand-link">
            <img src="<?= base_url('assets/img/logo-2.png'); ?>" alt="Logo LPM" class="ami-logo-img">
            <div class="ami-brand-text">
                <div class="ami-brand-title">AMI</div>
                <div class="ami-brand-subtitle">Universitas Muhammadiyah Babel</div>
            </div>
        </a>
        <button type="button" class="ami-sidebar-close" data-sidebar-close aria-label="Tutup menu">
            <svg class="tw-w-5 tw-h-5" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <nav class="ami-nav">
        <?php
        $current_group = '';
        $group_labels = [
            'Overview' => 'OVERVIEW',
            'Management' => 'MANAJEMEN',
            'Insights' => 'HASIL & TINDAK LANJUT',
            'Settings' => 'PENGATURAN',
            'Work' => 'WORKSPACE',
        ];
        ?>
        <?php foreach ($current_menus as $menu): ?>
            <?php if (isset($menu['group']) && $menu['group'] !== $current_group): ?>
                <?php
                $current_group = $menu['group'];
                $display_group_label = isset($group_labels[$current_group]) ? $group_labels[$current_group] : $current_group;
                ?>
                <div class="ami-nav-label"><?php echo html_escape($display_group_label); ?></div>
            <?php endif; ?>
            <?php if ($menu['key'] === 'spmi_ppepp_documents'): ?>
                <div class="ami-nav-subgroup-label" aria-hidden="true">Siklus PPEPP</div>
                <?php
                $ppepp_step_num = 1;
                $evaluasi_nav_id = 'ami-nav-evaluasi';
                foreach ($ppepp_stages as $stage_key => $stage_label):
                    $stage_key = (string) $stage_key;
                    if ($stage_key === 'pengendalian'):
                        $evaluasi_step_badge = sprintf('%02d', $ppepp_step_num++);
                ?>
                    <button type="button" class="ami-nav-link ami-nav-disclosure bg-transparent <?php echo $evaluasi_is_active ? 'has-active-child' : ''; ?>" data-nav-disclosure aria-expanded="<?php echo $evaluasi_is_active ? 'true' : 'false'; ?>" aria-controls="<?php echo $evaluasi_nav_id; ?>">
                        <span class="ami-nav-step" aria-hidden="true"><?php echo $evaluasi_step_badge; ?></span>
                        <i class="fas fa-layer-group sr-only" aria-hidden="true"></i>
                        <span>Evaluasi</span>
                        <i class="fas fa-chevron-down ami-nav-chevron" aria-hidden="true"></i>
                    </button>
                    <ul class="ami-nav-submenu" id="<?php echo $evaluasi_nav_id; ?>" <?php echo $evaluasi_is_active ? '' : 'hidden'; ?> aria-label="Evaluasi SPMI">
                        <li>
                            <a class="ami-nav-link ami-nav-sub-link" href="<?php echo site_url('lpmpi/spmi-standards'); ?>"<?php echo $active_menu === 'spmi_standards' ? ' aria-current="page"' : ''; ?>>
                                <span>Standar SPMI</span>
                            </a>
                        </li>
                        <li>
                            <a class="ami-nav-link ami-nav-sub-link" href="<?php echo site_url('lpmpi/spmi-audits'); ?>"<?php echo $active_menu === 'spmi_audits' ? ' aria-current="page"' : ''; ?>>
                                <span>Siklus &amp; Penugasan SPMI</span>
                            </a>
                        </li>
                    </ul>
                <?php
                    endif;
                    $stage_url = site_url($menu['url']) . '?stage=' . rawurlencode($stage_key) . '&year=' . rawurlencode((string) $ppepp_selected_year);
                    $stage_is_current = $ppepp_is_active && $ppepp_selected_stage !== '' && $ppepp_selected_stage === $stage_key;
                    $step_badge = sprintf('%02d', $ppepp_step_num++);
                ?>
                    <a class="ami-nav-link ami-nav-ppepp-stage-link <?php echo $stage_is_current ? 'active' : ''; ?>" href="<?php echo html_escape($stage_url); ?>"<?php echo $stage_is_current ? ' aria-current="page"' : ''; ?>>
                        <span class="ami-nav-step" aria-hidden="true"><?php echo $step_badge; ?></span>
                        <i class="fas <?php echo html_escape($menu['icon']); ?> sr-only" aria-hidden="true"></i>
                        <span><?php echo html_escape($stage_label); ?></span>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <a class="ami-nav-link <?php echo $active_menu === $menu['key'] ? 'active' : ''; ?>" href="<?php echo site_url($menu['url']); ?>">
                    <i class="fas <?php echo html_escape($menu['icon']); ?>" aria-hidden="true"></i>
                    <span><?php echo html_escape($menu['label']); ?></span>
                    <?php if (isset($menu_badges[$menu['key']]) && (int) $menu_badges[$menu['key']] > 0): ?>
                        <span class="ami-nav-badge"><?php echo html_escape((string) $menu_badges[$menu['key']]); ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <div class="ami-logout">
        <?php echo form_open('auth/logout'); ?>
            <button type="submit" class="ami-nav-link w-100 border-0 bg-transparent text-left">
                <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                <span>Logout</span>
            </button>
        <?php echo form_close(); ?>
    </div>
</aside>

<button type="button" class="ami-sidebar-overlay" data-sidebar-close aria-label="Tutup menu"></button>

<main class="ami-main">
    <div class="ami-topbar">
        <div class="ami-topbar-heading">
            <button type="button" class="ami-desktop-toggle d-none d-lg-inline-flex" data-sidebar-desktop-toggle aria-label="Toggle sidebar desktop" title="Sembunyikan/Tampilkan sidebar">
                <svg class="tw-w-5 tw-h-5 ami-icon-panel-close" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
                <svg class="tw-w-5 tw-h-5 ami-icon-panel-open tw-hidden" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                </svg>
            </button>
            <button type="button" class="ami-menu-toggle d-lg-none" data-sidebar-toggle aria-controls="ami-sidebar" aria-expanded="false" aria-label="Buka menu">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
            <div>
                <h1 class="ami-page-title"><?php echo html_escape($page_title ?? 'Dashboard'); ?></h1>
                <div class="ami-page-subtitle"><?php echo html_escape($page_subtitle ?? ''); ?></div>
            </div>
        </div>
        <div class="ami-topbar-actions">
            <div class="dropdown">
                <button type="button" class="ami-account-toggle" id="account-menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Menu akun">
                    <span class="ami-avatar ami-topbar-avatar avatar-<?php echo html_escape($role); ?>">
                        <?php if ($profile_photo_path): ?>
                            <img src="<?php echo site_url('account/photo'); ?>" alt="" class="ami-avatar-image">
                        <?php else: ?>
                            <?php echo html_escape($initial); ?>
                        <?php endif; ?>
                    </span>
                    <span class="d-none d-md-inline"><?php echo html_escape($nama); ?></span>
                    <i class="fas fa-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right ami-account-menu" aria-labelledby="account-menu">
                    <a class="dropdown-item" href="<?php echo site_url('account'); ?>"><i class="fas fa-user-circle" aria-hidden="true"></i>Akun Saya</a>
                    <div class="dropdown-divider"></div>
                    <?php echo form_open('auth/logout', ['class' => 'mb-0']); ?>
                        <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt" aria-hidden="true"></i>Logout</button>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="ami-content">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert ami-flash ami-flash-success" role="alert">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
                <span><?php echo html_escape($this->session->flashdata('success')); ?></span>
            </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert ami-flash ami-flash-error" role="alert">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                <span><?php echo html_escape($this->session->flashdata('error')); ?></span>
            </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('warning')): ?>
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                <span><?php echo html_escape($this->session->flashdata('warning')); ?></span>
            </div>
        <?php endif; ?>
