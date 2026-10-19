<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tugas = isset($tugas) ? $tugas : [];
$status_svgs = [
    'fa-check-circle' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>',
    'fa-save' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 3h12l2 2v16H5V3Z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></svg>',
    'fa-star' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg>',
];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h2 class="ami-section-title tw-m-0">Daftar tugas siap dinilai</h2>
        </div>

        <div class="tw-overflow-x-auto">
            <table class="ami-table">
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
                                    <div class="tw-text-xs tw-text-slate-500">
                                        <?php echo html_escape(format_tanggal_indo($item->tanggal_buka)); ?> - <?php echo html_escape(format_tanggal_indo($item->tanggal_tutup)); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="tw-font-bold"><?php echo html_escape($item->auditee_nama ?: '-'); ?></div>
                                <?php if (!empty($item->auditee_unit)): ?>
                                    <div class="tw-text-xs tw-text-slate-500">
                                        <?php echo html_escape($item->auditee_unit); ?>
                                        <?php if (!empty($item->auditee_jenis_unit)): ?>
                                            &middot; <?php echo html_escape(strtoupper($item->auditee_jenis_unit)); ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="tw-font-bold"><?php echo html_escape($item->nama_standar ?: '-'); ?></td>
                            <td>
                                <?php if (!empty($item->submitted_at)): ?>
                                    <?php echo html_escape(format_tanggal_indo($item->submitted_at)); ?>
                                <?php else: ?>
                                    <span class="tw-text-slate-500">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo (int) $item->jumlah_dinilai; ?> / <?php echo (int) $item->jumlah_pertanyaan; ?></td>
                            <td>
                                <span class="ami-status <?php echo html_escape($item->penilaian_status_class); ?>">
                                    <?php echo $status_svgs[$item->penilaian_status_icon] ?? $status_svgs['fa-star']; ?>
                                    <?php echo html_escape($item->penilaian_status_label); ?>
                                </span>
                            </td>
                            <td>
                                <a class="ami-action-btn" href="<?php echo site_url('auditor/penilaian/form/' . (int) $item->id); ?>">
                                    <?php if ($item->penilaian_status === 'selesai'): ?>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg> Detail
                                    <?php else: ?>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg> Nilai
                                    <?php endif; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="ami-empty">
                                <div class="ami-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2h12M6 22h12M8 2v4a4 4 0 0 0 2 3.5L12 10l2-.5A4 4 0 0 0 16 6V2m0 20v-4a4 4 0 0 0-2-3.5L12 14l-2 .5A4 4 0 0 0 8 18v4"/></svg></div>
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
