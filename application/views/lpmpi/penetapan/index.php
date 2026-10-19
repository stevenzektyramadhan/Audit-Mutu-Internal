<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$kategori_labels = [
    'pelaksanaan' => ['label' => 'Pelaksanaan', 'icon' => '<path d="M14 3 6 13h6l-2 8 8-10h-6l2-8Z"/>'],
    'pengendalian' => ['label' => 'Pengendalian', 'icon' => '<path d="M4 6h16M4 12h16M4 18h16M8 4v4M16 10v4M10 16v4"/>'],
    'peningkatan' => ['label' => 'Peningkatan', 'icon' => '<path d="M12 19V5m0 0-6 6m6-6 6 6"/>'],
];
$kategori_icon = static function ($path) {
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
};
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="tw-mb-6 tw-flex tw-items-center tw-justify-between tw-gap-4">
            <h2 class="ami-section-title tw-m-0">Penetapan</h2>
        </div>

        <div class="tw-mb-6 tw-flex tw-flex-wrap tw-gap-2 tw-border-b tw-border-slate-200" id="penetapanTabs" role="tablist" aria-label="Kategori penetapan">
            <?php foreach ($kategori_list as $index => $kategori): ?>
                <?php $meta = $kategori_labels[$kategori]; ?>
                <a class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-t-md tw-border-b-2 tw-px-4 tw-py-3 tw-text-sm tw-font-bold tw-transition-colors <?php echo $index === 0 ?'tw-border-blue-700 tw-text-blue-700' : 'tw-border-transparent tw-text-slate-500 hover:tw-border-slate-300 hover:tw-text-slate-800'; ?>" id="<?php echo html_escape($kategori); ?>-tab" href="#<?php echo html_escape($kategori); ?>" role="tab" aria-controls="<?php echo html_escape($kategori); ?>" aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                    <?php echo $kategori_icon($meta['icon']); ?> <span><?php echo html_escape($meta['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <div id="penetapanTabsContent">
            <?php foreach ($kategori_list as $index => $kategori): ?>
                <?php $meta = $kategori_labels[$kategori]; ?>
                <div class="<?php echo $index === 0 ?'' : 'tw-hidden'; ?>" id="<?php echo html_escape($kategori); ?>" role="tabpanel" aria-labelledby="<?php echo html_escape($kategori); ?>-tab" <?php echo $index === 0 ? '' : 'hidden'; ?>>
                    <div class="tw-overflow-x-auto">
                        <table class="ami-table tw-w-full">
                            <thead>
                            <tr>
                                <th class="tw-min-w-[180px]">Standar</th>
                                <th class="tw-min-w-[150px]">Status</th>
                                <th class="tw-min-w-[260px]">Deskripsi</th>
                                <th class="tw-min-w-[220px]">Upload File</th>
                                <th>Aksi</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($penetapan_by_kategori[$kategori])): ?>
                                <?php foreach ($penetapan_by_kategori[$kategori] as $row): ?>
                                    <?php $form_id = 'penetapan-form-' . (int) $row->id; ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo html_escape($row->nama_standar ?? '-'); ?></strong>
                                            <?php echo form_open_multipart('lpmpi/penetapan/update/' . (int) $row->id, ['id' => $form_id]); ?>
                                            <?php echo form_close(); ?>
                                        </td>
                                        <td>
                                            <input type="text" class="tw-w-full tw-rounded-md tw-border tw-border-slate-300 tw-px-3 tw-py-2" name="status" form="<?php echo html_escape($form_id); ?>" value="<?php echo html_escape($row->status ?? ''); ?>" placeholder="Terpenuhi / Proses">
                                        </td>
                                        <td>
                                            <textarea class="tw-w-full tw-rounded-md tw-border tw-border-slate-300 tw-px-3 tw-py-2" name="deskripsi" form="<?php echo html_escape($form_id); ?>" rows="2" placeholder="Deskripsi singkat"><?php echo html_escape($row->deskripsi ?? ''); ?></textarea>
                                        </td>
                                        <td>
                                            <?php if (!empty($row->file_path)): ?>
                                                <a href="<?php echo site_url('lpmpi/penetapan/download/' . (int) $row->id); ?>" class="btn-ami btn-outline-ami tw-mb-2">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/></svg> Download
                                                </a>
                                                <div class="tw-mb-2 tw-text-xs tw-text-slate-500"><?php echo html_escape($row->file_path); ?></div>
                                            <?php endif; ?>
                                            <input type="file" class="tw-w-full tw-rounded-md tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2 tw-text-sm" name="file_penetapan" form="<?php echo html_escape($form_id); ?>" accept=".pdf,.doc,.docx,.xls,.xlsx">
                                        </td>
                                        <td>
                                            <div class="ami-row-actions tw-flex tw-flex-wrap tw-items-center tw-gap-2">
                                                <button type="submit" class="ami-action-btn" form="<?php echo html_escape($form_id); ?>" title="Simpan">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 4h12l2 2v14H5zM8 4v6h8V4M8 16h8"/></svg><span>Simpan</span>
                                                </button>
                                                <?php if (!empty($row->file_path)): ?>
                                                    <?php echo form_open('lpmpi/penetapan/delete_file/' . (int) $row->id, ['class' => 'tw-inline', 'onsubmit' => "return confirm('Hapus file penetapan ini?');"]); ?>
                                                        <button type="submit" class="ami-action-btn danger" title="Hapus file">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M10 11v6m4-6v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                                                        </button>
                                                    <?php echo form_close(); ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5"><div class="ami-empty">
                                        <div class="ami-empty-icon"><?php echo $kategori_icon($meta['icon']); ?></div>
                                        <div class="ami-empty-title">Belum ada standar</div>
                                        <div>Buat standar terlebih dahulu untuk mengelola data <?php echo html_escape(strtolower($meta['label'])); ?>.</div>
                                    </div></td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
(function () {
    var root = document.getElementById('penetapanTabs');
    if (!root) return;
    var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
    var panels = tabs.map(function (tab) { return document.getElementById(tab.getAttribute('aria-controls')); });
    function activate(index) {
        tabs.forEach(function (tab, tabIndex) {
            var active = tabIndex === index;
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            tab.classList.toggle('tw-border-blue-700', active);
            tab.classList.toggle('tw-text-blue-700', active);
            tab.classList.toggle('tw-border-transparent', !active);
            tab.classList.toggle('tw-text-slate-500', !active);
            panels[tabIndex].hidden = !active;
            panels[tabIndex].classList.toggle('tw-hidden', !active);
        });
    }
    tabs.forEach(function (tab, index) {
        tab.addEventListener('click', function (event) {
            event.preventDefault();
            activate(index);
        });
        tab.addEventListener('keydown', function (event) {
            if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
            event.preventDefault();
            var next = (index + (event.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length;
            tabs[next].focus();
            activate(next);
        });
    });
}());
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
