<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$selected_stage = isset($selected_stage) ? (string) $selected_stage : 'penetapan';
$selected_year = isset($selected_year) ? (int) $selected_year : (int) date('Y');
$ppepp_stage_label = isset($stages[$selected_stage]) ? $stages[$selected_stage] : $selected_stage;
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
$stage_descriptions = [
    'penetapan' => 'Arsip kebijakan, manual, formulir, dan standar sebagai fondasi SPMI.',
    'pelaksanaan' => 'Arsip pelaksanaan standar dan bukti kegiatan pada tahun terpilih.',
    'pengendalian' => 'Arsip analisis penyebab dan tindakan koreksi untuk pengendalian mutu.',
    'peningkatan' => 'Arsip revisi standar dan rekomendasi peningkatan mutu berkelanjutan.',
];
$stage_icons = [
    'penetapan' => 'fa-compass',
    'pelaksanaan' => 'fa-play-circle',
    'pengendalian' => 'fa-shield-alt',
    'peningkatan' => 'fa-chart-line',
];
$stage_description = isset($stage_descriptions[$selected_stage]) ? $stage_descriptions[$selected_stage] : 'Arsip dokumen PPEPP SPMI pada tahap terpilih.';
$stage_icon = isset($stage_icons[$selected_stage]) ? $stage_icons[$selected_stage] : 'fa-folder-open';

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
            <div class="ppepp-heading">
                <div class="ppepp-heading-icon" aria-hidden="true"><i class="fas <?php echo html_escape($stage_icon); ?>"></i></div>
                <div>
                    <nav class="ppepp-breadcrumb" aria-label="Breadcrumb"><a href="<?php echo html_escape(site_url('lpmpi/spmi-dashboard')); ?>">Beranda</a><span aria-hidden="true">/</span><span>Dokumen PPEPP</span><span aria-hidden="true">/</span><strong><?php echo html_escape($ppepp_stage_label); ?></strong></nav>
                    <h2 class="ami-section-title mb-1">Dokumen <?php echo html_escape($ppepp_stage_label); ?></h2>
                    <p class="text-muted mb-0"><?php echo html_escape($stage_description); ?></p>
                </div>
            </div>
            <a class="btn-ami btn-primary mt-3 mt-md-0" href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents/create') . $query); ?>">
                <i class="fas fa-plus" aria-hidden="true"></i> Tambah dokumen
            </a>
        </div>

        <div class="ppepp-toolbar">
            <div class="ppepp-search">
                <label for="ppepp-document-search">Cari dokumen</label>
                <div class="ppepp-search-control"><i class="fas fa-search" aria-hidden="true"></i><input type="search" class="form-control" id="ppepp-document-search" placeholder="Judul, kategori, uploader..." autocomplete="off"><button type="button" class="ppepp-search-clear" id="ppepp-document-search-clear" hidden>Hapus</button></div>
            </div>
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
                <div class="ppepp-checklist-grid">
                    <?php foreach ($checklist_categories as $category_key): ?>
                        <?php
                        $category_count = isset($checklist[$category_key]) ? (int) $checklist[$category_key] : 0;
                        $category_label = isset($stage_categories[$category_key]) ? $stage_categories[$category_key] : $category_key;
                        ?>
                        <div class="ppepp-checklist-card">
                            <div class="ppepp-checklist-card-inner">
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
                <div>Belum ada arsip <?php echo html_escape($ppepp_stage_label); ?> untuk tahun <?php echo html_escape((string) $selected_year); ?>.</div>
                <a href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents/create') . $query); ?>" class="btn btn-primary btn-ami mt-3"><i class="fas fa-plus" aria-hidden="true"></i> Tambah dokumen</a>
            </div>
        <?php else: ?>
            <div class="ppepp-document-grid" id="ppepp-document-grid">
                    <?php foreach ($documents as $document): ?>
                        <?php
                        $category_label = isset($stage_categories[$document->category]) ? $stage_categories[$document->category] : $document->category;
                        $has_file = !empty($document->stored_name);
                        $has_url = !empty($document->external_url);
                        ?>
                        <article class="ppepp-document-card" data-ppepp-document-card>
                            <div class="ppepp-document-card-top"><span class="ppepp-document-category"><?php echo html_escape($category_label); ?></span><span class="ppepp-document-year"><?php echo html_escape((string) $document->period_year); ?></span></div>
                            <h3><?php echo html_escape($document->title); ?></h3>
                                <?php if (!empty($document->description)): ?>
                                    <div class="text-muted small ppepp-document-description"><?php echo nl2br(html_escape($document->description)); ?></div>
                                <?php endif; ?>
                            <dl class="ppepp-document-meta"><div><dt>Tanggal</dt><dd><?php echo html_escape($document->document_date ?: '-'); ?></dd></div><div><dt>Uploader</dt><dd><?php echo html_escape($document->uploader_name ?: '-'); ?></dd></div></dl>
                            <div class="ppepp-document-types" aria-label="Jenis dokumen">
                                <?php if ($has_file): ?><span class="badge badge-info">File</span><?php endif; ?>
                                <?php if ($has_url): ?><span class="badge badge-secondary">URL</span><?php endif; ?>
                            </div>
                            <div class="ami-row-actions ppepp-document-actions">
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
                        </article>
                    <?php endforeach; ?>
            </div>
            <div class="ami-empty ppepp-no-match" id="ppepp-document-no-match" hidden><div class="ami-empty-icon"><i class="fas fa-search" aria-hidden="true"></i></div><div class="ami-empty-title">Dokumen tidak ditemukan</div><div>Coba kata kunci lain atau hapus pencarian.</div></div>
        <?php endif; ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
