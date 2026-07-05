<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$title = 'Dashboard Auditee';
$page_title = 'Dashboard';
$page_subtitle = 'Beranda / Dashboard';
$active_menu = 'dashboard';
$stats = isset($stats) ? $stats : [];
$tugas_saya = isset($tugas_saya) ? $tugas_saya : [];
$menu_badges = ['tugas_saya' => isset($stats['perlu_tindakan']) ? $stats['perlu_tindakan'] : 0];
$status_labels = [
    'belum_diisi' => ['label' => 'Belum diisi', 'icon' => 'fa-exclamation-circle', 'tone' => 'tone-amber'],
    'draft' => ['label' => 'Draft', 'icon' => 'fa-save', 'tone' => 'tone-teal'],
    'submitted' => ['label' => 'Submitted', 'icon' => 'fa-paper-plane', 'tone' => 'tone-blue'],
    'revisi' => ['label' => 'Revisi', 'icon' => 'fa-undo', 'tone' => 'tone-amber'],
    'dinilai' => ['label' => 'Dinilai', 'icon' => 'fa-check-circle', 'tone' => 'tone-green'],
];
$stat_cards = [
    ['label' => 'Total tugas', 'value' => isset($stats['total_tugas']) ? $stats['total_tugas'] : 0, 'icon' => 'fa-clipboard-list', 'tone' => 'tone-blue'],
    ['label' => 'Belum diisi', 'value' => isset($stats['belum_diisi']) ? $stats['belum_diisi'] : 0, 'icon' => 'fa-exclamation-circle', 'tone' => 'tone-amber'],
    ['label' => 'Sudah diisi', 'value' => isset($stats['diisi']) ? $stats['diisi'] : 0, 'icon' => 'fa-paper-plane', 'tone' => 'tone-teal'],
    ['label' => 'Sudah dinilai', 'value' => isset($stats['dinilai']) ? $stats['dinilai'] : 0, 'icon' => 'fa-check-circle', 'tone' => 'tone-green'],
];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<style>
    .ami-dashboard-logo-banner {
        display: flex;
        align-items: center;
        gap: 16px;
        background: var(--ami-panel);
        border: 1px solid var(--ami-border);
        border-radius: 10px;
        padding: 18px 22px;
        margin-bottom: 18px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }
    .ami-dashboard-logo {
        height: 56px;
        width: auto;
        object-fit: contain;
        flex-shrink: 0;
    }
    .ami-dashboard-logo-title {
        font-size: 17px;
        font-weight: 700;
        color: var(--ami-text);
        line-height: 1.3;
    }
    .ami-dashboard-logo-subtitle {
        font-size: 13px;
        color: var(--ami-muted);
        margin-top: 2px;
    }
</style>

<div class="ami-dashboard-logo-banner">
    <img src="<?= base_url('assets/img/logo-2.png'); ?>" alt="Logo LPM" class="ami-dashboard-logo">
    <div>
        <div class="ami-dashboard-logo-title">Lembaga Penjaminan Mutu</div>
        <div class="ami-dashboard-logo-subtitle">Sistem Audit Mutu Internal Perguruan Tinggi</div>
    </div>
</div>

<div class="ami-stat-grid">
    <?php foreach ($stat_cards as $card): ?>
        <div class="ami-stat-card">
            <div class="ami-stat-icon <?php echo html_escape($card['tone']); ?>">
                <i class="fas <?php echo html_escape($card['icon']); ?>" aria-hidden="true"></i>
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
    <a href="<?php echo site_url('auditee/tugas'); ?>" class="small font-weight-bold">Lihat semua</a>
</div>

<?php if (!empty($tugas_saya)): ?>
    <?php foreach ($tugas_saya as $tugas): ?>
        <?php
        $status = isset($tugas->display_status) ? $tugas->display_status : $tugas->status;
        $meta = isset($status_labels[$status]) ? $status_labels[$status] : ['label' => $status, 'icon' => 'fa-circle', 'tone' => 'tone-blue'];
        ?>
        <div class="ami-task-card"<?php echo in_array($status, ['belum_diisi', 'draft', 'revisi'], TRUE) ? ' style="border-left:3px solid #dc3545;"' : ''; ?>>
            <div class="ami-task-icon <?php echo html_escape($meta['tone']); ?>">
                <i class="fas <?php echo html_escape($meta['icon']); ?>" aria-hidden="true"></i>
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
                    <a class="btn btn-primary btn-ami" href="<?php echo site_url('auditee/form/' . (int) $tugas->id); ?>">
                        <i class="fas fa-pen" aria-hidden="true"></i>
                        <?php echo $status === 'revisi' ? 'Perbaiki' : 'Isi sekarang'; ?>
                    </a>
                <?php else: ?>
                    <a class="btn btn-outline-ami btn-ami" href="<?php echo site_url('auditee/form/' . (int) $tugas->id); ?>">
                        <i class="fas <?php echo html_escape($meta['icon']); ?>" aria-hidden="true"></i>
                        <?php echo ($status === 'dinilai' || $status === STATUS_DINILAI) ? 'Lihat hasil' : 'Lihat jawaban'; ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="ami-panel">
        <div class="ami-empty">
            <div class="ami-empty-icon"><i class="fas fa-clipboard-check" aria-hidden="true"></i></div>
            <div class="ami-empty-title">Belum ada tugas audit</div>
            <div>Tugas yang diberikan super admin akan tampil di sini.</div>
        </div>
    </div>
<?php endif; ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
