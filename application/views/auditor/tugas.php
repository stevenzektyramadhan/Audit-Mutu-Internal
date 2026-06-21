<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tugas = isset($tugas) ? $tugas : [];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-section-head mt-0">
    <h2 class="ami-section-title">Daftar tugas audit saya</h2>
</div>

<div class="alert alert-primary d-flex align-items-center" style="background:#e6f1fb;color:#185fa5;border:0;border-radius:8px;">
    <i class="fas fa-shield-alt mr-2" aria-hidden="true"></i>
    Hanya menampilkan tugas yang ditugaskan kepada Anda.
</div>

<div class="ami-panel">
    <?php if (!empty($tugas)): ?>
        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Auditee</th>
                    <th>Standar</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($tugas as $index => $item): ?>
                    <?php
                    $status_labels = [
                        STATUS_BELUM_DIISI => 'Belum diisi',
                        STATUS_DIISI => 'Diisi',
                        STATUS_DINILAI => 'Dinilai',
                    ];
                    ?>
                    <tr>
                        <td><?php echo (int) $index + 1; ?></td>
                        <td class="font-weight-bold"><?php echo html_escape($item->auditee_nama); ?></td>
                        <td><?php echo html_escape($item->nama_standar); ?></td>
                        <td>
                            <span class="ami-status status-<?php echo html_escape($item->status); ?>">
                                <?php echo html_escape(isset($status_labels[$item->status]) ? $status_labels[$item->status] : $item->status); ?>
                            </span>
                        </td>
                        <td><?php echo html_escape(format_tanggal_indo($item->created_at)); ?></td>
                        <td>
                            <?php if ($item->status === STATUS_BELUM_DIISI): ?>
                                <span class="text-muted">Menunggu auditee</span>
                            <?php elseif ($item->status === STATUS_DIISI): ?>
                                <a class="ami-action-btn" href="<?php echo site_url('auditor/nilai/' . (int) $item->id); ?>"><i class="fas fa-star" aria-hidden="true"></i>Nilai</a>
                            <?php else: ?>
                                <a class="ami-action-btn" href="<?php echo site_url('auditor/nilai/' . (int) $item->id); ?>"><i class="fas fa-eye" aria-hidden="true"></i>Detail</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="ami-empty">
            <div class="ami-empty-icon"><i class="fas fa-clipboard-list" aria-hidden="true"></i></div>
            <div class="ami-empty-title">Belum ada tugas audit</div>
            <div>Penugasan dari super admin akan tampil di halaman ini.</div>
        </div>
    <?php endif; ?>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
