<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$title = 'Dashboard Auditor';
$page_title = 'Dashboard Auditor';
$page_subtitle = 'Daftar tugas audit dan progres penilaian auditee';
$active_menu = 'dashboard';
$stats = isset($stats) ? $stats : [];
$pending_tugas = isset($pending_tugas) ? $pending_tugas : [];
$graded_tugas = isset($graded_tugas) ? $graded_tugas : [];
$menu_badges = ['tugas_audit' => isset($stats['siap_dinilai']) ? $stats['siap_dinilai'] : 0];
$icon = static function ($name) {
    $paths = [
        'clipboard-list' => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 4V2h6v2M8 9h8M8 13h8M8 17h5"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14 9 11"/>',
        'building' => '<path d="M3 21h18M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M9 7h2M9 11h2M13 7h2M13 11h2"/>',
        'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        'hourglass' => '<path d="M5 3h14M5 21h14M7 3v4c0 2 5 5 5 5s5-3 5-5V3M7 21v-4c0-2 5-5 5-5s5 3 5 5v4"/>',
        'list' => '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>',
        'eye' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/>',
        'circle' => '<circle cx="12" cy="12" r="10"/>',
    ];
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['circle']) . '</svg>';
};
$stat_cards = [
    ['label' => 'Total tugas', 'value' => isset($stats['total_tugas']) ? $stats['total_tugas'] : 0, 'icon' => 'clipboard-list', 'tone' => 'tone-blue'],
    ['label' => 'Belum dinilai', 'value' => isset($stats['belum_dinilai']) ? $stats['belum_dinilai'] : 0, 'icon' => 'clock', 'tone' => 'tone-amber'],
    ['label' => 'Sudah dinilai', 'value' => isset($stats['dinilai']) ? $stats['dinilai'] : 0, 'icon' => 'check-circle', 'tone' => 'tone-green'],
];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-dashboard-logo-banner">
    <img src="<?= html_escape($organization_logo_url); ?>" alt="Logo LPM" class="ami-dashboard-logo">
    <div>
        <div class="ami-dashboard-logo-title">Lembaga Penjaminan Mutu</div>
        <div class="ami-dashboard-logo-subtitle">Sistem Audit Mutu Internal Perguruan Tinggi</div>
    </div>
</div>

<div class="ami-stat-grid">
    <?php foreach ($stat_cards as $card): ?>
        <div class="ami-stat-card">
            <div class="ami-stat-icon <?php echo html_escape($card['tone']); ?>">
                <?php echo $icon($card['icon']); ?>
            </div>
            <div>
                <div class="ami-stat-label"><?php echo html_escape($card['label']); ?></div>
                <div class="ami-stat-value"><?php echo html_escape((string) $card['value']); ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="ami-section-head">
    <h2 class="ami-section-title">Tugas menunggu penilaian</h2>
            <a href="<?php echo site_url('auditor/tugas'); ?>" class="tw-text-xs tw-font-bold">Lihat tugas</a>
</div>

<?php if (!empty($pending_tugas)): ?>
    <?php foreach ($pending_tugas as $tugas): ?>
        <div class="ami-task-card">
            <div class="ami-task-icon tone-blue"><?php echo $icon('building'); ?></div>
            <div class="ami-task-main">
                <div class="ami-task-title"><?php echo html_escape($tugas->auditee_nama); ?></div>
                <div class="ami-task-meta">
                    <?php echo html_escape($tugas->nama_standar); ?> &middot; Jawaban auditee sudah masuk
                </div>
            </div>
            <div>
                <a class="tw-button-primary btn-ami" href="<?php echo site_url('auditor/nilai/' . (int) $tugas->id); ?>">
                    <?php echo $icon('star'); ?>
                    Nilai
                </a>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="ami-panel">
        <div class="ami-empty">
            <div class="ami-empty-icon"><?php echo $icon('hourglass'); ?></div>
            <div class="ami-empty-title">Belum ada tugas siap dinilai</div>
            <div>Tugas akan siap setelah auditee mengirim jawabannya.</div>
            <a href="<?php echo site_url('auditor/tugas'); ?>" class="btn-outline-ami btn-ami"><?php echo $icon('list'); ?>Lihat semua tugas</a>
        </div>
    </div>
<?php endif; ?>

<div class="ami-section-head">
    <h2 class="ami-section-title">Hasil penilaian terakhir</h2>
</div>
<div class="ami-panel">
    <?php if (!empty($graded_tugas)): ?>
        <div class="tw-overflow-x-auto">
            <table class="ami-table">
                <thead>
                <tr>
                    <th>Auditee</th>
                    <th>Standar</th>
                    <th>Rata-rata skor</th>
                    <th class="tw-text-right">Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($graded_tugas as $tugas): ?>
                    <tr>
                        <td><?php echo html_escape($tugas->auditee_nama); ?></td>
                        <td><?php echo html_escape($tugas->nama_standar); ?></td>
                        <td>
                            <span class="<?php echo (float) $tugas->rata_rata >= 3 ?'tw-text-green-600' : 'tw-text-amber-600'; ?> tw-font-bold">
                                <?php echo html_escape(number_format((float) $tugas->rata_rata, 1)); ?> / 4
                            </span>
                        </td>
                    <td class="tw-text-right"><a class="ami-action-btn" href="<?php echo site_url('auditor/nilai/' . (int) $tugas->id); ?>"><?php echo $icon('eye'); ?>Lihat detail</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="ami-empty">
            <div class="ami-empty-icon"><?php echo $icon('star'); ?></div>
            <div class="ami-empty-title">Belum ada penilaian selesai</div>
            <div>Ringkasan hasil penilaian terakhir akan tampil di sini.</div>
        </div>
    <?php endif; ?>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
