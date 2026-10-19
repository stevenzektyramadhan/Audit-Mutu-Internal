<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$title = 'Dashboard Auditee';
$page_title = 'Dashboard';
$page_subtitle = 'Beranda / Dashboard';
$active_menu = 'dashboard';
$stats = isset($stats) ? $stats : [];
$tugas_saya = isset($tugas_saya) ? $tugas_saya : [];
$menu_badges = ['tugas_saya' => isset($stats['perlu_tindakan']) ? $stats['perlu_tindakan'] : 0];
$icon = static function ($name) {
    $paths = [
        'alert-circle' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
        'save' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>',
        'send' => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
        'undo' => '<path d="M9 14 4 9l5-5M4 9h10a6 6 0 0 1 6 6v1"/>',
        'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14 9 11"/>',
        'clipboard-list' => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 4V2h6v2M8 9h8M8 13h8M8 17h5"/>',
        'circle' => '<circle cx="12" cy="12" r="10"/>',
        'pen' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
        'clipboard-check' => '<path d="M9 5H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/><path d="M9 3h6v4H9z"/><polyline points="9 14 11 16 15 12"/>',
    ];
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['circle']) . '</svg>';
};
$status_labels = [
    'belum_diisi' => ['label' => 'Belum diisi', 'icon' => 'alert-circle', 'tone' => 'tone-amber'],
    'draft' => ['label' => 'Draft', 'icon' => 'save', 'tone' => 'tone-teal'],
    'submitted' => ['label' => 'Submitted', 'icon' => 'send', 'tone' => 'tone-blue'],
    'revisi' => ['label' => 'Revisi', 'icon' => 'undo', 'tone' => 'tone-amber'],
    'dinilai' => ['label' => 'Dinilai', 'icon' => 'check-circle', 'tone' => 'tone-green'],
];
$stat_cards = [
    ['label' => 'Total tugas', 'value' => isset($stats['total_tugas']) ? $stats['total_tugas'] : 0, 'icon' => 'clipboard-list', 'tone' => 'tone-blue'],
    ['label' => 'Belum diisi', 'value' => isset($stats['belum_diisi']) ? $stats['belum_diisi'] : 0, 'icon' => 'alert-circle', 'tone' => 'tone-amber'],
    ['label' => 'Sudah diisi', 'value' => isset($stats['diisi']) ? $stats['diisi'] : 0, 'icon' => 'send', 'tone' => 'tone-teal'],
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
    <h2 class="ami-section-title">Tugas audit saya</h2>
            <a href="<?php echo site_url('auditee/tugas'); ?>" class="tw-text-xs tw-font-bold">Lihat semua</a>
</div>

<?php if (!empty($tugas_saya)): ?>
    <?php foreach ($tugas_saya as $tugas): ?>
        <?php
        $status = isset($tugas->display_status) ? $tugas->display_status : $tugas->status;
        $meta = isset($status_labels[$status]) ? $status_labels[$status] : ['label' => $status, 'icon' => 'circle', 'tone' => 'tone-blue'];
        ?>
        <div class="ami-task-card"<?php echo in_array($status, ['belum_diisi', 'draft', 'revisi'], TRUE) ? ' style="border-left:3px solid #dc3545;"' : ''; ?>>
            <div class="ami-task-icon <?php echo html_escape($meta['tone']); ?>">
                <?php echo $icon($meta['icon']); ?>
            </div>
            <div class="ami-task-main">
                <div class="ami-task-title"><?php echo html_escape($tugas->nama_standar); ?></div>
                <div class="ami-task-meta">
                    Auditor: <?php echo html_escape($tugas->auditor_nama); ?> &middot;
                    <?php if ($status === 'dinilai' || $status === STATUS_DINILAI): ?>
                        Rata-rata: <?php echo html_escape(number_format((float) $tugas->rata_rata, 1)); ?> / 4
                    <?php elseif ($status === 'submitted' || $status === STATUS_DIISI): ?>
                        Menunggu penilaian
                    <?php else: ?>
                        <?php echo html_escape($meta['label']); ?>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <?php if (in_array($status, ['belum_diisi', 'draft', 'revisi', STATUS_BELUM_DIISI], TRUE)): ?>
                    <a class="tw-button-primary btn-ami" href="<?php echo site_url('auditee/form/' . (int) $tugas->id); ?>">
                        <?php echo $icon('pen'); ?>
                        <?php echo $status === 'revisi' ? 'Perbaiki' : 'Isi sekarang'; ?>
                    </a>
                <?php else: ?>
                    <a class="btn-outline-ami btn-ami" href="<?php echo site_url('auditee/form/' . (int) $tugas->id); ?>">
                        <?php echo $icon($meta['icon']); ?>
                        <?php echo ($status === 'dinilai' || $status === STATUS_DINILAI) ? 'Lihat hasil' : 'Lihat jawaban'; ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="ami-panel">
        <div class="ami-empty">
            <div class="ami-empty-icon"><?php echo $icon('clipboard-check'); ?></div>
            <div class="ami-empty-title">Belum ada tugas audit</div>
            <div>Tugas yang diberikan super admin akan tampil di sini.</div>
        </div>
    </div>
<?php endif; ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
