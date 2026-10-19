<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tugas = isset($tugas) ? $tugas : [];
$filters = isset($filters) ? $filters : ['periode_id' => 0, 'status' => ''];
$periode_list = isset($periode_list) ? $periode_list : [];
$status_options = [
    '' => 'Semua status',
    'belum_diisi' => 'Belum diisi',
    'draft' => 'Draft',
    'submitted' => 'Submitted',
    'revisi' => 'Revisi',
    'dinilai' => 'Sudah dinilai',
];
$status_icons = [
    'belum_diisi' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5m0 3h.01"/></svg>',
    'draft' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 3h12l2 2v16H5V3Z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></svg>',
    'submitted' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="M22 2 11 13"/></svg>',
    'revisi' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v6h6"/></svg>',
    'dinilai' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>',
];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h2 class="ami-section-title tw-m-0">Daftar tugas masuk</h2>
        </div>

        <form action="<?php echo site_url('auditee/tugas'); ?>" method="get" class="ami-filter-bar">
            <div class="ami-filter-select">
                <label class="ami-stat-label" for="periode_id">Periode</label>
                        <select class="ami-form-control" id="periode_id" name="periode_id">
                    <option value="0">Semua periode</option>
                    <?php foreach ($periode_list as $periode): ?>
                        <option value="<?php echo (int) $periode->id; ?>" <?php echo (int) $filters['periode_id'] === (int) $periode->id ? 'selected' : ''; ?>>
                            <?php echo html_escape($periode->nama_periode); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ami-filter-select">
                <label class="ami-stat-label" for="status">Status</label>
                        <select class="ami-form-control" id="status" name="status">
                    <?php foreach ($status_options as $value => $label): ?>
                        <option value="<?php echo html_escape($value); ?>" <?php echo $filters['status'] === $value ? 'selected' : ''; ?>>
                            <?php echo html_escape($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-ami btn-outline-ami">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 5h16M7 12h10m-7 7h4"/></svg> Filter
            </button>
            <a href="<?php echo site_url('auditee/tugas'); ?>" class="btn-ami btn-outline-ami">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 12a9 9 0 0 1 15-6.7L21 8M21 4v4h-4M21 12a9 9 0 0 1-15 6.7L3 16m0 4v-4h4"/></svg> Reset
            </a>
        </form>

        <div class="tw-overflow-x-auto">
            <table class="ami-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Periode</th>
                    <th>Standar</th>
                    <th>Auditor</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Instrumen</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($tugas)): ?>
                    <?php foreach ($tugas as $index => $item): ?>
                        <?php $status_key = $item->display_status; ?>
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
                            <td class="tw-font-bold"><?php echo html_escape($item->nama_standar ?: '-'); ?></td>
                            <td><?php echo html_escape($item->auditor_nama ?: '-'); ?></td>
                            <td><?php echo (int) $item->jumlah_terisi; ?> / <?php echo (int) $item->jumlah_pertanyaan; ?></td>
                            <td>
                                <span class="ami-status status-<?php echo html_escape($status_key); ?>">
                                    <?php echo $status_icons[$status_key] ?? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg>'; ?>
                                    <?php echo html_escape($item->display_status_label); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($item->file_instrumen)): ?>
                                    <a class="ami-action-btn" href="<?php echo site_url('auditee/download_instrumen/' . (int) $item->id); ?>">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5M5 21h14"/></svg> Download
                                    </a>
                                <?php else: ?>
                                    <span class="tw-text-slate-500">Belum ada</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a class="ami-action-btn" href="<?php echo site_url('auditee/form/' . (int) $item->id); ?>">
                                    <?php if (in_array($status_key, ['submitted', 'dinilai'], TRUE)): ?>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg> Lihat
                                    <?php else: ?>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m4 20 4.5-1L19 8.5 15.5 5 5 15.5 4 20Z"/><path d="m14 6 4 4"/></svg> Isi
                                    <?php endif; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="ami-empty">
                                <div class="ami-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 5h6v3H9zM6 4h2v16h8V4h2v18H6z"/><path d="m9 14 2 2 4-4"/></svg></div>
                                <div class="ami-empty-title">Tidak ada tugas</div>
                                <div>Tugas sesuai filter akan tampil di sini.</div>
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
