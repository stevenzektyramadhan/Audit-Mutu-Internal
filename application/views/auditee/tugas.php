<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tugas = isset($tugas) ? $tugas : [];
$filter_status = isset($filter_status) ? $filter_status : NULL;
$status_labels = [
    STATUS_BELUM_DIISI => 'Belum diisi',
    STATUS_DIISI => 'Diisi',
    STATUS_DINILAI => 'Dinilai',
];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-section-head mt-0">
    <h2 class="ami-section-title">Daftar tugas audit saya</h2>
</div>

<?php if ($filter_status === STATUS_DINILAI): ?>
    <div class="alert alert-primary d-flex align-items-center" style="background:#e6f1fb;color:#185fa5;border:0;border-radius:8px;">
        <i class="fas fa-check-circle mr-2" aria-hidden="true"></i>
        Menampilkan tugas yang sudah dinilai beserta akses ke skor dan catatan auditor.
    </div>
<?php else: ?>
    <div class="alert alert-primary d-flex align-items-center" style="background:#e6f1fb;color:#185fa5;border:0;border-radius:8px;">
        <i class="fas fa-info-circle mr-2" aria-hidden="true"></i>
        Klik “Isi sekarang” untuk mengisi jawaban dan link bukti pada tugas yang belum diisi.
    </div>
<?php endif; ?>

<div class="ami-panel">
    <?php if (!empty($tugas)): ?>
        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Standar</th>
                    <th>Auditor</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($tugas as $index => $item): ?>
                    <tr>
                        <td><?php echo (int) $index + 1; ?></td>
                        <td class="font-weight-bold"><?php echo html_escape($item->nama_standar); ?></td>
                        <td><?php echo html_escape($item->auditor_nama); ?></td>
                        <td>
                            <span class="ami-status status-<?php echo html_escape($item->status); ?>">
                                <?php echo html_escape(isset($status_labels[$item->status]) ? $status_labels[$item->status] : $item->status); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($item->status === STATUS_BELUM_DIISI): ?>
                                <a class="ami-action-btn" href="<?php echo site_url('auditee/isi/' . (int) $item->id); ?>"><i class="fas fa-pen" aria-hidden="true"></i>Isi</a>
                            <?php elseif ($item->status === STATUS_DIISI): ?>
                                <a class="ami-action-btn" href="<?php echo site_url('auditee/isi/' . (int) $item->id); ?>"><i class="fas fa-eye" aria-hidden="true"></i>Lihat jawaban</a>
                            <?php else: ?>
                                <a class="ami-action-btn" href="<?php echo site_url('auditee/isi/' . (int) $item->id); ?>"><i class="fas fa-eye" aria-hidden="true"></i>Lihat hasil</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="ami-empty">
            <div class="ami-empty-icon"><i class="fas fa-clipboard-check" aria-hidden="true"></i></div>
            <div class="ami-empty-title">Belum ada tugas audit</div>
            <div>Tugas yang diberikan super admin akan tampil di sini.</div>
        </div>
    <?php endif; ?>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
