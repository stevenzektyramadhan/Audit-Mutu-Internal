<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$role = $this->session->userdata('role');
$nama = $this->session->userdata('nama');
$active_menu = isset($active_menu) ? $active_menu : 'dashboard';
$menu_badges = isset($menu_badges) ? $menu_badges : [];
$role_labels = [
    'super_admin' => 'Super Admin',
    'auditor' => 'Auditor',
    'auditee' => 'Auditee',
];
$menus = [
    'super_admin' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => 'dashboard'],
        ['key' => 'users', 'label' => 'Data Pengguna', 'icon' => 'fa-users', 'url' => 'users'],
        ['key' => 'standar', 'label' => 'Data Standar', 'icon' => 'fa-award', 'url' => 'standar'],
        ['key' => 'pertanyaan', 'label' => 'Data Pertanyaan', 'icon' => 'fa-tasks', 'url' => 'pertanyaan'],
        ['key' => 'tugas_audit', 'label' => 'Tugas Audit', 'icon' => 'fa-clipboard-list', 'url' => 'tugas_audit'],
        ['key' => 'hasil_audit', 'label' => 'Hasil Audit', 'icon' => 'fa-chart-bar', 'url' => 'tugas_audit/hasil'],
    ],
    'auditor' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => 'dashboard'],
        ['key' => 'tugas_audit', 'label' => 'Tugas Audit', 'icon' => 'fa-clipboard-list', 'url' => 'auditor/tugas'],
        ['key' => 'penilaian', 'label' => 'Penilaian Auditee', 'icon' => 'fa-star', 'url' => 'auditor/tugas'],
    ],
    'auditee' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => 'dashboard'],
        ['key' => 'tugas_saya', 'label' => 'Tugas Saya', 'icon' => 'fa-clipboard-list', 'url' => 'auditee/tugas'],
        ['key' => 'pengisian', 'label' => 'Pengisian Audit', 'icon' => 'fa-pen', 'url' => 'auditee/tugas'],
        ['key' => 'hasil_penilaian', 'label' => 'Hasil Penilaian', 'icon' => 'fa-eye', 'url' => 'auditee/tugas'],
    ],
];
$current_menus = isset($menus[$role]) ? $menus[$role] : [];
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
<aside class="ami-sidebar">
    <div class="ami-brand">
        <div class="ami-logo"><i class="fas fa-clipboard-check" aria-hidden="true"></i></div>
        <div class="ami-brand-title">AMI<br>Perguruan Tinggi</div>
    </div>

    <div class="ami-user">
        <div class="ami-avatar"><?php echo html_escape($initial); ?></div>
        <div>
            <div class="ami-user-name"><?php echo html_escape($nama); ?></div>
            <div class="ami-user-role"><?php echo html_escape(isset($role_labels[$role]) ? $role_labels[$role] : $role); ?></div>
        </div>
    </div>

    <nav class="ami-nav">
        <div class="ami-nav-label">Menu</div>
        <?php foreach ($current_menus as $menu): ?>
            <a class="ami-nav-link <?php echo $active_menu === $menu['key'] ? 'active' : ''; ?>" href="<?php echo site_url($menu['url']); ?>">
                <i class="fas <?php echo html_escape($menu['icon']); ?>" aria-hidden="true"></i>
                <span><?php echo html_escape($menu['label']); ?></span>
                <?php if (isset($menu_badges[$menu['key']]) && (int) $menu_badges[$menu['key']] > 0): ?>
                    <span class="ami-nav-badge"><?php echo html_escape((string) $menu_badges[$menu['key']]); ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="ami-logout">
        <a class="ami-nav-link" href="<?php echo site_url('auth/logout'); ?>">
            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>

<main class="ami-main">
    <div class="ami-topbar">
        <div>
            <h1 class="ami-page-title"><?php echo html_escape($page_title); ?></h1>
            <div class="ami-page-subtitle"><?php echo html_escape($page_subtitle); ?></div>
        </div>
    </div>
    <div class="ami-content">
