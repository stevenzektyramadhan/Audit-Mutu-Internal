<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$title = 'Dashboard Auditee';
$page_title = 'Dashboard Auditee';
$page_subtitle = 'Pengisian bukti audit dan status penilaian';
$active_menu = 'dashboard';
$stats = isset($stats) ? $stats : [];
$tugas_saya = isset($tugas_saya) ? $tugas_saya : [];
$menu_badges = ['tugas_saya' => isset($stats['total_tugas']) ? $stats['total_tugas'] : 0];
$status_labels = [
    STATUS_BELUM_DIISI => ['label' => 'Belum diisi', 'icon' => 'fa-exclamation-circle', 'tone' => 'tone-amber'],
    STATUS_DIISI => ['label' => 'Sudah diisi', 'icon' => 'fa-clock', 'tone' => 'tone-blue'],
    STATUS_DINILAI => ['label' => 'Dinilai', 'icon' => 'fa-check-circle', 'tone' => 'tone-green'],
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
        <?php $meta = isset($status_labels[$tugas->status]) ? $status_labels[$tugas->status] : ['label' => $tugas->status, 'icon' => 'fa-circle', 'tone' => 'tone-blue']; ?>
        <div class="ami-task-card">
            <div class="ami-task-icon <?php echo html_escape($meta['tone']); ?>">
                <i class="fas <?php echo html_escape($meta['icon']); ?>" aria-hidden="true"></i>
            </div>
            <div class="ami-task-main">
                <div class="ami-task-title"><?php echo html_escape($tugas->nama_standar); ?></div>
                <div class="ami-task-meta">
                    Auditor: <?php echo html_escape($tugas->auditor_nama); ?> &middot; <?php echo html_escape($meta['label']); ?>
                </div>
            </div>
            <div>
                <?php if ($tugas->status === STATUS_BELUM_DIISI): ?>
                    <a class="btn btn-primary btn-ami" href="<?php echo site_url('auditee/isi/' . (int) $tugas->id); ?>">
                        <i class="fas fa-pen" aria-hidden="true"></i>
                        Isi
                    </a>
                <?php else: ?>
                    <span class="ami-status status-<?php echo html_escape($tugas->status); ?>">
                        <i class="fas <?php echo html_escape($meta['icon']); ?>" aria-hidden="true"></i>
                        <?php echo html_escape($meta['label']); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="ami-panel">
        <div class="ami-empty">Belum ada tugas audit untuk akun ini.</div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-6">
        <div class="ami-section-head">
            <h2 class="ami-section-title">Pengisian audit</h2>
        </div>
        <div class="ami-panel">
            <div class="ami-panel-body">
                <div class="d-flex align-items-center">
                    <div class="ami-task-icon tone-amber mr-3"><i class="fas fa-pen" aria-hidden="true"></i></div>
                    <div>
                        <div class="font-weight-bold"><?php echo html_escape((string) (isset($stats['belum_diisi']) ? $stats['belum_diisi'] : 0)); ?> tugas perlu diisi</div>
                        <div class="text-muted small">Jawaban singkat dan link bukti dokumen.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="ami-section-head">
            <h2 class="ami-section-title">Hasil penilaian</h2>
        </div>
        <div class="ami-panel">
            <div class="ami-panel-body">
                <div class="d-flex align-items-center">
                    <div class="ami-task-icon tone-green mr-3"><i class="fas fa-check-circle" aria-hidden="true"></i></div>
                    <div>
                        <div class="font-weight-bold"><?php echo html_escape((string) (isset($stats['dinilai']) ? $stats['dinilai'] : 0)); ?> tugas sudah dinilai</div>
                        <div class="text-muted small">Skor dan catatan auditor tersedia setelah penilaian.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
