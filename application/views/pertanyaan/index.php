<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$tones = ['tone-violet', 'tone-green', 'tone-amber', 'tone-blue', 'tone-rose', 'tone-teal'];
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="ami-section-head" style="flex-wrap:wrap;">
            <h2 class="ami-section-title m-0">Daftar pertanyaan audit</h2>
            <div class="ami-actions">
                <a class="btn-ami btn-outline-ami" href="<?php echo site_url('pertanyaan/create'); ?>">
                    <svg width="16" height="16" class="tw-h-4 tw-w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg> Tambah pertanyaan
                </a>
                <?php if ($standar_id !== NULL): ?>
                    <a href="<?php echo site_url('pertanyaan/download_template/' . (int) $standar_id); ?>" class="btn-ami btn-outline-ami">
                        <svg width="16" height="16" class="tw-h-4 tw-w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 3h8l4 4v14H6zM14 3v5h4M9 13h6M9 17h4"></path></svg> Download Template Excel
                    </a>
                    <button type="button" class="btn-ami btn-outline-ami" data-modal-open="modalImport" aria-controls="modalImport" aria-expanded="false">
                        <svg width="16" height="16" class="tw-h-4 tw-w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 16V4m0 0L8 8m4-4 4 4M5 14v5h14v-5"></path></svg> Import dari Excel
                    </button>
                <?php else: ?>
                    <button type="button" class="btn-ami btn-outline-ami" disabled title="Pilih satu standar terlebih dahulu">
                        <svg width="16" height="16" class="tw-h-4 tw-w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 3h8l4 4v14H6zM14 3v5h4M9 13h6M9 17h4"></path></svg> Download Template Excel
                    </button>
                    <button type="button" class="btn-ami btn-outline-ami" disabled title="Pilih satu standar terlebih dahulu">
                        <svg width="16" height="16" class="tw-h-4 tw-w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 16V4m0 0L8 8m4-4 4 4M5 14v5h14v-5"></path></svg> Import dari Excel
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($standar_id === NULL): ?>
            <div class="ami-flash" style="background:var(--ami-link-soft);color:var(--ami-blue);">
                Pilih satu standar pada filter untuk mengaktifkan download template dan import Excel.
            </div>
        <?php endif; ?>

        <form method="get" action="<?php echo site_url('pertanyaan'); ?>" class="ami-actions tw-items-end mb-3">
            <div style="width:280px;max-width:100%;">
                <label for="standar-filter" class="ami-stat-label">Filter standar</label>
                <select id="standar-filter" name="standar_id" class="ami-form-control">
                    <option value="">Semua standar</option>
                    <?php foreach ($standar as $std): ?>
                        <option value="<?php echo (int) $std->id; ?>" <?php echo (int) $filter_standar_id === (int) $std->id ? 'selected' : ''; ?>>
                            <?php echo html_escape($std->nama_standar); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-ami"><svg width="16" height="16" class="tw-h-4 tw-w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M7 12h10m-7 6h4"></path></svg>Terapkan</button>
            <?php if ($filter_standar_id !== NULL): ?>
                <a href="<?php echo site_url('pertanyaan'); ?>" class="btn-outline-ami btn-ami">Reset</a>
            <?php endif; ?>
        </form>

        <div class="tw-overflow-x-auto">
            <table class="ami-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Standar</th>
                    <th>Isi pertanyaan</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($pertanyaan)): ?>
                    <?php $no = 1; foreach ($pertanyaan as $row): ?>
                        <?php 
                            $standar_badge = str_replace('Standar ', '', $row->nama_standar ?? '');
                            $tone_class = $tones[($row->standar_id ?? 0) % count($tones)];
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td>
                                <?php if (!empty($row->nama_standar)): ?>
                                    <span class="ami-status <?php echo $tone_class; ?>">
                                        <?php echo html_escape($standar_badge); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="tw-text-slate-500">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width: 350px;">
                                <?php echo html_escape($row->isi_pertanyaan); ?>
                            </td>
                            <td>
                                <?php echo html_escape(format_tanggal_indo($row->created_at)); ?>
                            </td>
                            <td>
                                <div class="ami-row-actions">
                                    <a href="<?php echo site_url('pertanyaan/edit/'.$row->id); ?>" class="ami-action-btn" title="Edit pertanyaan">
                                        <svg width="16" height="16" class="tw-h-4 tw-w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m4 16-.8 4.8L8 20l11-11-4-4L4 16zM13 6l4 4"></path></svg><span>Edit</span>
                                    </a>
                                    <?php echo form_open('pertanyaan/delete/' . (int) $row->id, ['onsubmit' => "return confirm('Apakah Anda yakin ingin menghapus pertanyaan ini?');"]); ?>
                                        <button type="submit" class="ami-action-btn danger" title="Hapus pertanyaan">
                                            <svg width="16" height="16" class="tw-h-4 tw-w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M10 11v6m4-6v6M9 7V4h6v3m-9 0 1 13h10l1-13"></path></svg><span>Hapus</span>
                                        </button>
                                    <?php echo form_close(); ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5"><div class="ami-empty">
                            <div class="ami-empty-icon"><svg width="24" height="24" class="tw-h-6 tw-w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M9.7 9a2.4 2.4 0 1 1 3.7 2c-.9.6-1.4 1-1.4 2M12 16.5h.01"></path></svg></div>
                            <div class="ami-empty-title">Pertanyaan tidak ditemukan</div>
                            <div><?php echo $filter_standar_id !== NULL ? 'Belum ada pertanyaan pada standar yang dipilih.' : 'Tambahkan pertanyaan audit berdasarkan standar.'; ?></div>
                            <?php if ($filter_standar_id !== NULL): ?>
                                <a href="<?php echo site_url('pertanyaan'); ?>" class="btn-outline-ami btn-ami"><svg width="16" height="16" class="tw-h-4 tw-w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 12a8 8 0 1 0 2.3-5.7L4 8.5M4 4v4.5h4.5"></path></svg>Reset filter</a>
                            <?php else: ?>
                                <a href="<?php echo site_url('pertanyaan/create'); ?>" class="btn-ami"><svg width="16" height="16" class="tw-h-4 tw-w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg>Tambah pertanyaan</a>
                            <?php endif; ?>
                        </div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!empty($pertanyaan)): ?>
        <div class="mt-3 tw-text-slate-500" style="font-size: 13px;">
            Menampilkan <?php echo count($pertanyaan); ?> pertanyaan
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($standar_id !== NULL): ?>
<div class="tw-fixed tw-inset-0 tw-z-50 tw-hidden tw-overflow-y-auto tw-p-4 sm:tw-p-6" id="modalImport" tabindex="-1" role="dialog" aria-labelledby="modalImportLabel" aria-modal="true" aria-hidden="true">
    <div class="tw-fixed tw-inset-0 tw-bg-slate-950/60" data-modal-close="modalImport"></div>
    <div class="tw-relative tw-mx-auto tw-flex tw-min-h-full tw-w-full tw-max-w-lg tw-items-center">
        <div class="tw-relative tw-w-full tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-shadow-2xl">
            <?php echo form_open_multipart('pertanyaan/import/' . (int) $standar_id); ?>
                <div class="tw-flex tw-items-center tw-justify-between tw-gap-4 tw-border-b tw-border-slate-200 tw-px-5 tw-py-4">
                    <h2 class="tw-m-0 tw-text-lg tw-font-semibold tw-text-slate-900" id="modalImportLabel">Import Pertanyaan dari Excel</h2>
                    <button type="button" class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-lg tw-text-slate-500 hover:tw-bg-slate-100 hover:tw-text-slate-900 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500" data-modal-close="modalImport" aria-label="Tutup">
                        <svg width="20" height="20" class="tw-h-5 tw-w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                    </button>
                </div>
                <div class="tw-space-y-5 tw-px-5 tw-py-5">
                    <p class="tw-m-0 tw-text-sm tw-leading-6 tw-text-slate-600">
                        Upload file Excel (.xlsx atau .xls) sesuai format template, maksimal <?php echo html_escape((string) (isset($upload_limit_mib) ? $upload_limit_mib : (function_exists('get_upload_limit_mib') ? get_upload_limit_mib('spreadsheet_imports') : 2))); ?> MB.
                        <a href="<?php echo site_url('pertanyaan/download_template/' . (int) $standar_id); ?>">Download template di sini</a>.
                    </p>
                    <div>
                        <label for="file-excel" class="tw-mb-2 tw-block tw-text-sm tw-font-semibold tw-text-slate-800">File Excel <span class="tw-text-red-600">*</span></label>
                        <input type="file" class="tw-block tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-p-2 tw-text-sm tw-text-slate-700 file:tw-mr-3 file:tw-rounded file:tw-border-0 file:tw-bg-slate-100 file:tw-px-3 file:tw-py-1.5 file:tw-font-medium file:tw-text-slate-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500" id="file-excel" name="file_excel" accept=".xlsx,.xls" required>
                    </div>
                </div>
                <div class="tw-flex tw-flex-col-reverse tw-gap-2 tw-border-t tw-border-slate-200 tw-px-5 tw-py-4 sm:tw-flex-row sm:tw-justify-end">
                    <button type="button" class="btn-ami btn-outline-ami" data-modal-close="modalImport">Batal</button>
                    <button type="submit" class="btn-ami" data-loading-text="Memproses file...">
                        <svg width="16" height="16" class="tw-h-4 tw-w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="6"></circle><path d="m16 16 4 4"></path></svg> Preview Import
                    </button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($standar_id !== NULL): ?>
<script>
(function () {
    var modal = document.getElementById('modalImport');
    var opener = document.querySelector('[data-modal-open="modalImport"]');
    if (!modal || !opener) return;
    var lastFocused = null;
    var focusable = 'a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled])';
    function setOpen(open) {
        modal.hidden = !open;
        modal.classList.toggle('tw-hidden', !open);
        modal.setAttribute('aria-hidden', open ? 'false' : 'true');
        opener.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.classList.toggle('tw-overflow-hidden', open);
        if (open) {
            lastFocused = document.activeElement;
            var first = modal.querySelector('input, button');
            if (first) first.focus();
        } else if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
    }
    opener.addEventListener('click', function () { setOpen(true); });
    modal.querySelectorAll('[data-modal-close="modalImport"]').forEach(function (close) {
        close.addEventListener('click', function () { setOpen(false); });
    });
    modal.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') { event.preventDefault(); setOpen(false); return; }
        if (event.key !== 'Tab') return;
        var nodes = Array.prototype.slice.call(modal.querySelectorAll(focusable));
        if (!nodes.length) return;
        var first = nodes[0];
        var last = nodes[nodes.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });
}());
</script>
<?php endif; ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
