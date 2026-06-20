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
$stat_cards = [
    ['label' => 'Total tugas', 'value' => isset($stats['total_tugas']) ? $stats['total_tugas'] : 0, 'icon' => 'fa-clipboard-list', 'tone' => 'tone-blue'],
    ['label' => 'Belum dinilai', 'value' => isset($stats['belum_dinilai']) ? $stats['belum_dinilai'] : 0, 'icon' => 'fa-clock', 'tone' => 'tone-amber'],
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
    <h2 class="ami-section-title">Tugas menunggu penilaian</h2>
    <a href="<?php echo site_url('auditor/tugas'); ?>" class="small font-weight-bold">Lihat tugas</a>
</div>

<?php if (!empty($pending_tugas)): ?>
    <?php foreach ($pending_tugas as $tugas): ?>
        <div class="ami-task-card">
            <div class="ami-task-icon tone-blue"><i class="fas fa-building" aria-hidden="true"></i></div>
            <div class="ami-task-main">
                <div class="ami-task-title"><?php echo html_escape($tugas->auditee_nama); ?></div>
                <div class="ami-task-meta">
                    <?php echo html_escape($tugas->nama_standar); ?> &middot; Jawaban auditee sudah masuk
                </div>
            </div>
            <div>
                <a class="btn btn-primary btn-ami" href="<?php echo site_url('auditor/nilai/' . (int) $tugas->id); ?>">
                    <i class="fas fa-star" aria-hidden="true"></i>
                    Nilai
                </a>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="ami-panel">
        <div class="ami-empty">Belum ada tugas yang siap dinilai.</div>
    </div>
<?php endif; ?>

<div class="ami-section-head">
    <h2 class="ami-section-title">Hasil penilaian terakhir</h2>
</div>
<div class="ami-panel">
    <?php if (!empty($graded_tugas)): ?>
        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>Auditee</th>
                    <th>Standar</th>
                    <th>Rata-rata skor</th>
                    <th class="text-right">Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($graded_tugas as $tugas): ?>
                    <tr>
                        <td><?php echo html_escape($tugas->auditee_nama); ?></td>
                        <td><?php echo html_escape($tugas->nama_standar); ?></td>
                        <td>
                            <span class="<?php echo (float) $tugas->rata_rata >= 3 ? 'text-success' : 'text-warning'; ?> font-weight-bold">
                                <?php echo html_escape(number_format((float) $tugas->rata_rata, 1)); ?> / 4
                            </span>
                        </td>
                        <td class="text-right"><a href="<?php echo site_url('auditor/nilai/' . (int) $tugas->id); ?>">Lihat detail</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="ami-empty">Belum ada tugas yang sudah dinilai.</div>
    <?php endif; ?>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
