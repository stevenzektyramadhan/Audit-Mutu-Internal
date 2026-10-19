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

<div class="ami-section-head tw-mt-0">
    <h2 class="ami-section-title">Daftar tugas audit saya</h2>
</div>

<?php if ($filter_status === STATUS_DINILAI): ?>
    <div class="ami-alert ami-alert-info" style="background:#e6f1fb;color:#185fa5;border:0;border-radius:8px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
        Menampilkan tugas yang sudah dinilai beserta akses ke skor dan catatan auditor.
    </div>
<?php else: ?>
    <div class="ami-alert ami-alert-info" style="background:#e6f1fb;color:#185fa5;border:0;border-radius:8px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/></svg>
        Klik “Isi sekarang” untuk mengisi jawaban dan link bukti pada tugas yang belum diisi.
    </div>
<?php endif; ?>

<div class="ami-panel">
    <?php if (!empty($tugas)): ?>
        <div class="tw-overflow-x-auto">
            <table class="ami-table">
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
                            <td class="tw-font-bold"><?php echo html_escape($item->nama_standar); ?></td>
                        <td><?php echo html_escape($item->auditor_nama); ?></td>
                        <td>
                            <span class="ami-status status-<?php echo html_escape($item->status); ?>">
                                <?php echo html_escape(isset($status_labels[$item->status]) ? $status_labels[$item->status] : $item->status); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($item->status === STATUS_BELUM_DIISI): ?>
                                <a class="ami-action-btn" href="<?php echo site_url('auditee/isi/' . (int) $item->id); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m4 20 4.5-1L19 8.5 15.5 5 5 15.5 4 20Z"/><path d="m14 6 4 4"/></svg>Isi</a>
                            <?php elseif ($item->status === STATUS_DIISI): ?>
                                <a class="ami-action-btn" href="<?php echo site_url('auditee/isi/' . (int) $item->id); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>Lihat jawaban</a>
                            <?php else: ?>
                                <a class="ami-action-btn" href="<?php echo site_url('auditee/isi/' . (int) $item->id); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>Lihat hasil</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="ami-empty">
            <div class="ami-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 5h6v3H9zM6 4h2v16h8V4h2v18H6z"/><path d="m9 14 2 2 4-4"/></svg></div>
            <div class="ami-empty-title">Belum ada tugas audit</div>
            <div>Tugas yang diberikan super admin akan tampil di sini.</div>
        </div>
    <?php endif; ?>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
