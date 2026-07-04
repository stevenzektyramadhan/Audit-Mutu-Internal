<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$kategori_labels = [
    'pelaksanaan' => ['label' => 'Pelaksanaan', 'icon' => 'fa-gavel'],
    'pengendalian' => ['label' => 'Pengendalian', 'icon' => 'fa-sliders-h'],
    'peningkatan' => ['label' => 'Peningkatan', 'icon' => 'fa-chart-line'],
];
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Penetapan</h2>
        </div>

        <ul class="nav nav-tabs mb-4" id="penetapanTabs" role="tablist">
            <?php foreach ($kategori_list as $index => $kategori): ?>
                <?php $meta = $kategori_labels[$kategori]; ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>" id="<?php echo html_escape($kategori); ?>-tab" data-toggle="tab" href="#<?php echo html_escape($kategori); ?>" role="tab" aria-controls="<?php echo html_escape($kategori); ?>" aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                        <i class="fas <?php echo html_escape($meta['icon']); ?>"></i> <?php echo html_escape($meta['label']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content" id="penetapanTabsContent">
            <?php foreach ($kategori_list as $index => $kategori): ?>
                <?php $meta = $kategori_labels[$kategori]; ?>
                <div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>" id="<?php echo html_escape($kategori); ?>" role="tabpanel" aria-labelledby="<?php echo html_escape($kategori); ?>-tab">
                    <div class="table-responsive">
                        <table class="table ami-table">
                            <thead>
                            <tr>
                                <th style="min-width: 180px;">Standar</th>
                                <th style="min-width: 150px;">Status</th>
                                <th style="min-width: 260px;">Deskripsi</th>
                                <th style="min-width: 220px;">Upload File</th>
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
                                            <input type="text" class="form-control" name="status" form="<?php echo html_escape($form_id); ?>" value="<?php echo html_escape($row->status ?? ''); ?>" placeholder="Terpenuhi / Proses">
                                        </td>
                                        <td>
                                            <textarea class="form-control" name="deskripsi" form="<?php echo html_escape($form_id); ?>" rows="2" placeholder="Deskripsi singkat"><?php echo html_escape($row->deskripsi ?? ''); ?></textarea>
                                        </td>
                                        <td>
                                            <?php if (!empty($row->file_path)): ?>
                                                <a href="<?php echo base_url('uploads/penetapan/' . rawurlencode($row->file_path)); ?>" target="_blank" rel="noopener noreferrer" class="btn-ami btn-sm btn-outline-ami mb-2">
                                                    <i class="fas fa-download" aria-hidden="true"></i> Download
                                                </a>
                                                <div class="text-muted mb-2" style="font-size: 12px;"><?php echo html_escape($row->file_path); ?></div>
                                            <?php endif; ?>
                                            <input type="file" class="form-control form-control-sm" name="file_penetapan" form="<?php echo html_escape($form_id); ?>" accept=".pdf,.doc,.docx,.xls,.xlsx">
                                        </td>
                                        <td>
                                            <div class="ami-row-actions">
                                                <button type="submit" class="ami-action-btn" form="<?php echo html_escape($form_id); ?>" title="Simpan">
                                                    <i class="fas fa-save" aria-hidden="true"></i><span>Simpan</span>
                                                </button>
                                                <?php if (!empty($row->file_path)): ?>
                                                    <?php echo form_open('lpmpi/penetapan/delete_file/' . (int) $row->id, ['class' => 'd-inline', 'onsubmit' => "return confirm('Hapus file penetapan ini?');"]); ?>
                                                        <button type="submit" class="ami-action-btn danger" title="Hapus file">
                                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
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
                                        <div class="ami-empty-icon"><i class="fas <?php echo html_escape($meta['icon']); ?>" aria-hidden="true"></i></div>
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

<?php include APPPATH . 'views/layouts/footer.php'; ?>
