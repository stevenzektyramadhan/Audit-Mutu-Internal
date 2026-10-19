<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tugas = isset($tugas) ? $tugas : [];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-section-head tw-mt-0">
    <h2 class="ami-section-title">Daftar tugas audit saya</h2>
</div>

<div class="ami-alert ami-alert-info" style="background:#e6f1fb;color:#185fa5;border:0;border-radius:8px;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3 4 6v5c0 5 3.4 8.5 8 10 4.6-1.5 8-5 8-10V6l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
    Hanya menampilkan tugas yang ditugaskan kepada Anda.
</div>

<div class="ami-panel">
    <?php if (!empty($tugas)): ?>
        <div class="tw-overflow-x-auto">
            <table class="ami-table">
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
                        <td class="tw-font-bold"><?php echo html_escape($item->auditee_nama); ?></td>
                        <td><?php echo html_escape($item->nama_standar); ?></td>
                        <td>
                            <span class="ami-status status-<?php echo html_escape($item->status); ?>">
                                <?php echo html_escape(isset($status_labels[$item->status]) ? $status_labels[$item->status] : $item->status); ?>
                            </span>
                        </td>
                        <td><?php echo html_escape(format_tanggal_indo($item->created_at)); ?></td>
                        <td>
                            <?php if ($item->status === STATUS_BELUM_DIISI): ?>
                                <span class="tw-text-slate-500">Menunggu auditee</span>
                            <?php elseif ($item->status === STATUS_DIISI): ?>
                                <a class="ami-action-btn" href="<?php echo site_url('auditor/penilaian/form/' . (int) $item->id); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg>Nilai</a>
                            <?php else: ?>
                                <a class="ami-action-btn" href="<?php echo site_url('auditor/penilaian/form/' . (int) $item->id); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>Detail</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="ami-empty">
            <div class="ami-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 5h6M9 3h6v4H9zM5 4h2v16h10V4h2v18H5zM9 11h6m-6 4h6"/></svg></div>
            <div class="ami-empty-title">Belum ada tugas audit</div>
            <div>Penugasan dari super admin akan tampil di halaman ini.</div>
        </div>
    <?php endif; ?>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
