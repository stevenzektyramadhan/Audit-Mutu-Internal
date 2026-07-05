<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tugas = isset($tugas) ? $tugas : [];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Daftar tugas siap dinilai</h2>
        </div>

        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Periode</th>
                    <th>Auditee</th>
                    <th>Standar</th>
                    <th>Submitted</th>
                    <th>Progress nilai</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($tugas)): ?>
                    <?php foreach ($tugas as $index => $item): ?>
                        <tr>
                            <td><?php echo (int) $index + 1; ?></td>
                            <td>
                                <div><?php echo html_escape($item->nama_periode ?: '-'); ?></div>
                                <?php if (!empty($item->tanggal_buka) && !empty($item->tanggal_tutup)): ?>
                                    <div class="text-muted" style="font-size:12px;">
                                        <?php echo html_escape(format_tanggal_indo($item->tanggal_buka)); ?> - <?php echo html_escape(format_tanggal_indo($item->tanggal_tutup)); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="font-weight-bold"><?php echo html_escape($item->auditee_nama ?: '-'); ?></div>
                                <?php if (!empty($item->auditee_unit)): ?>
                                    <div class="text-muted" style="font-size:12px;">
                                        <?php echo html_escape($item->auditee_unit); ?>
                                        <?php if (!empty($item->auditee_jenis_unit)): ?>
                                            &middot; <?php echo html_escape(strtoupper($item->auditee_jenis_unit)); ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="font-weight-bold"><?php echo html_escape($item->nama_standar ?: '-'); ?></td>
                            <td>
                                <?php if (!empty($item->submitted_at)): ?>
                                    <?php echo html_escape(format_tanggal_indo($item->submitted_at)); ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo (int) $item->jumlah_dinilai; ?> / <?php echo (int) $item->jumlah_pertanyaan; ?></td>
                            <td>
                                <span class="ami-status <?php echo html_escape($item->penilaian_status_class); ?>">
                                    <i class="fas <?php echo html_escape($item->penilaian_status_icon); ?>" aria-hidden="true"></i>
                                    <?php echo html_escape($item->penilaian_status_label); ?>
                                </span>
                            </td>
                            <td>
                                <a class="ami-action-btn" href="<?php echo site_url('auditor/penilaian/form/' . (int) $item->id); ?>">
                                    <?php if ($item->penilaian_status === 'selesai'): ?>
                                        <i class="fas fa-eye" aria-hidden="true"></i> Detail
                                    <?php else: ?>
                                        <i class="fas fa-star" aria-hidden="true"></i> Nilai
                                    <?php endif; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="ami-empty">
                                <div class="ami-empty-icon"><i class="fas fa-hourglass-half" aria-hidden="true"></i></div>
                                <div class="ami-empty-title">Belum ada tugas siap dinilai</div>
                                <div>Tugas akan tampil setelah auditee submit jawaban.</div>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
