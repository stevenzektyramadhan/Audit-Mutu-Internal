<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$siap_dinilai = isset($siap_dinilai) ? $siap_dinilai : [];
$sudah_dinilai = isset($sudah_dinilai) ? $sudah_dinilai : [];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-section-head mt-0">
    <h2 class="ami-section-title">Tugas siap dinilai</h2>
</div>

<?php if (!empty($siap_dinilai)): ?>
    <?php foreach ($siap_dinilai as $item): ?>
        <div class="ami-task-card align-items-center">
            <div class="ami-task-icon tone-blue"><i class="fas fa-building" aria-hidden="true"></i></div>
            <div class="ami-task-main">
                <div class="ami-task-title"><?php echo html_escape($item->auditee_nama); ?></div>
                <div class="ami-task-meta">
                    <?php echo html_escape($item->nama_standar); ?> &middot;
                    <?php echo html_escape((string) $item->jumlah_pertanyaan); ?> pertanyaan
                </div>
            </div>
            <a class="btn btn-primary btn-ami" href="<?php echo site_url('auditor/nilai/' . (int) $item->id); ?>">
                <i class="fas fa-star" aria-hidden="true"></i> Nilai sekarang
            </a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="ami-panel"><div class="ami-empty">Belum ada tugas yang siap dinilai.</div></div>
<?php endif; ?>

<div class="ami-section-head">
    <h2 class="ami-section-title">Sudah dinilai</h2>
</div>

<div class="ami-panel">
    <?php if (!empty($sudah_dinilai)): ?>
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
                <?php foreach ($sudah_dinilai as $item): ?>
                    <tr>
                        <td class="font-weight-bold"><?php echo html_escape($item->auditee_nama); ?></td>
                        <td><?php echo html_escape($item->nama_standar); ?></td>
                        <td>
                            <span class="<?php echo (float) $item->rata_rata >= 3 ? 'text-success' : 'text-warning'; ?> font-weight-bold">
                                <?php echo html_escape(number_format((float) $item->rata_rata, 1)); ?> / 4
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="<?php echo site_url('auditor/nilai/' . (int) $item->id); ?>">Lihat detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="ami-empty">Belum ada tugas yang selesai dinilai.</div>
    <?php endif; ?>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
