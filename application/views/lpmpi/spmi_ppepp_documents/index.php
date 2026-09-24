<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$selected_stage = isset($selected_stage) ? (string) $selected_stage : 'penetapan';
$selected_year = isset($selected_year) ? (int) $selected_year : (int) date('Y');
$stage_label = isset($stages[$selected_stage]) ? $stages[$selected_stage] : $selected_stage;
$stage_categories = isset($categories[$selected_stage]) && is_array($categories[$selected_stage]) ? $categories[$selected_stage] : [];
$checklist_categories = isset($penetapan_core_categories) && is_array($penetapan_core_categories) ? $penetapan_core_categories : [];
$year_options = [];
foreach ((array) $years as $year) {
    $year_value = is_object($year) ? $year->period_year : $year;
    $year_options[(string) $year_value] = $year_value;
}
$current_year = (string) date('Y');
$year_options[$current_year] = $current_year;
$query = '?stage=' . rawurlencode($selected_stage) . '&year=' . rawurlencode((string) $selected_year);
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
            <div>
                <h2 class="ami-section-title mb-1">Dokumen PPEPP</h2>
                <p class="text-muted mb-0">Arsip bukti pelaksanaan siklus PPEPP SPMI per tahap dan tahun.</p>
            </div>
            <a class="btn-ami btn-primary mt-3 mt-md-0" href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents/create') . $query); ?>">
                <i class="fas fa-plus" aria-hidden="true"></i> Tambah dokumen
            </a>
        </div>

        <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between mb-4">
            <ul class="nav nav-tabs mb-3 mb-lg-0" role="tablist" aria-label="Tahap PPEPP">
                <?php foreach ($stages as $stage_key => $label): ?>
                    <?php $stage_url = site_url('lpmpi/spmi-ppepp-documents') . '?stage=' . rawurlencode((string) $stage_key) . '&year=' . rawurlencode((string) $selected_year); ?>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?php echo $selected_stage === (string) $stage_key ? 'active' : ''; ?>" href="<?php echo html_escape($stage_url); ?>" aria-current="<?php echo $selected_stage === (string) $stage_key ? 'page' : 'false'; ?>">
                            <?php echo html_escape($label); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php echo form_open('lpmpi/spmi-ppepp-documents', ['method' => 'get', 'class' => 'form-inline']); ?>
                <input type="hidden" name="stage" value="<?php echo html_escape($selected_stage); ?>">
                <label class="mr-2 mb-2 mb-lg-0" for="ppepp-year">Tahun</label>
                <select class="form-control mr-2 mb-2 mb-lg-0" id="ppepp-year" name="year">
                    <?php foreach ($year_options as $year_value): ?>
                        <option value="<?php echo html_escape((string) $year_value); ?>" <?php echo (int) $year_value === $selected_year ? 'selected' : ''; ?>><?php echo html_escape((string) $year_value); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn-ami btn-outline-ami mb-2 mb-lg-0" type="submit">Terapkan</button>
            <?php echo form_close(); ?>
        </div>

        <?php if ($selected_stage === 'penetapan'): ?>
            <section class="mb-4" aria-labelledby="penetapan-checklist-title">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h3 class="ami-section-title h5 mb-1" id="penetapan-checklist-title">Checklist Penetapan</h3>
                        <p class="text-muted small mb-0">Kelengkapan kategori inti pada tahun yang dipilih.</p>
                    </div>
                    <span class="badge badge-light border"><?php echo html_escape((string) $selected_year); ?></span>
                </div>
                <div class="row">
                    <?php foreach ($checklist_categories as $category_key): ?>
                        <?php
                        $category_count = isset($checklist[$category_key]) ? (int) $checklist[$category_key] : 0;
                        $category_label = isset($stage_categories[$category_key]) ? $stage_categories[$category_key] : $category_key;
                        ?>
                        <div class="col-sm-6 col-xl-4 mb-3">
                            <div class="border rounded p-3 h-100 d-flex align-items-center justify-content-between">
                                <span><?php echo html_escape($category_label); ?></span>
                                <span class="badge <?php echo $category_count > 0 ? 'badge-success' : 'badge-secondary'; ?>">
                                    <?php echo html_escape($category_count > 0 ? (string) $category_count . ' dokumen' : 'Belum ada'); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (empty($documents)): ?>
            <div class="ami-empty">
                <div class="ami-empty-icon"><i class="fas fa-folder-open" aria-hidden="true"></i></div>
                <div class="ami-empty-title">Belum ada dokumen</div>
                <div>Belum ada arsip <?php echo html_escape($stage_label); ?> untuk tahun <?php echo html_escape((string) $selected_year); ?>.</div>
                <a href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents/create') . $query); ?>" class="btn btn-primary btn-ami mt-3"><i class="fas fa-plus" aria-hidden="true"></i> Tambah dokumen</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table ami-table">
                    <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Judul &amp; Deskripsi</th>
                        <th>Tanggal</th>
                        <th>Tahun</th>
                        <th>Jenis</th>
                        <th>Uploader</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($documents as $document): ?>
                        <?php
                        $category_label = isset($stage_categories[$document->category]) ? $stage_categories[$document->category] : $document->category;
                        $has_file = !empty($document->stored_name);
                        $has_url = !empty($document->external_url);
                        ?>
                        <tr>
                            <td><?php echo html_escape($category_label); ?></td>
                            <td>
                                <strong><?php echo html_escape($document->title); ?></strong>
                                <?php if (!empty($document->description)): ?>
                                    <div class="text-muted small mt-1"><?php echo nl2br(html_escape($document->description)); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo html_escape($document->document_date ?: '-'); ?></td>
                            <td><?php echo html_escape((string) $document->period_year); ?></td>
                            <td>
                                <?php if ($has_file): ?><span class="badge badge-info">File</span><?php endif; ?>
                                <?php if ($has_url): ?><span class="badge badge-secondary">URL</span><?php endif; ?>
                            </td>
                            <td><?php echo html_escape($document->uploader_name ?: '-'); ?></td>
                            <td>
                                <div class="ami-row-actions">
                                    <?php if ($has_file): ?>
                                        <a class="ami-action-btn" href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents/download/' . (int) $document->id) . $query); ?>" title="Download file">
                                            <i class="fas fa-download" aria-hidden="true"></i><span>Download</span>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($has_url): ?>
                                        <a class="ami-action-btn" href="<?php echo html_escape($document->external_url); ?>" target="_blank" rel="noopener noreferrer" title="Buka URL dokumen">
                                            <i class="fas fa-external-link-alt" aria-hidden="true"></i><span>URL</span>
                                        </a>
                                    <?php endif; ?>
                                    <a class="ami-action-btn" href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents/edit/' . (int) $document->id) . $query); ?>" title="Edit dokumen">
                                        <i class="fas fa-edit" aria-hidden="true"></i><span>Edit</span>
                                    </a>
                                    <?php echo form_open('lpmpi/spmi-ppepp-documents/delete/' . (int) $document->id . $query, ['class' => 'd-inline', 'onsubmit' => 'return confirm(\'Hapus dokumen PPEPP ini?\');']); ?>
                                        <button type="submit" class="ami-action-btn danger" title="Hapus dokumen">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i><span>Hapus</span>
                                        </button>
                                    <?php echo form_close(); ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
