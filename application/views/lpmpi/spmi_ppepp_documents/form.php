<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$document = isset($document) ? $document : NULL;
$selected_stage = $document && isset($document->stage) ? (string) $document->stage : 'penetapan';
$selected_category = $document && isset($document->category) ? (string) $document->category : '';
$selected_year = $document && isset($document->period_year) ? (int) $document->period_year : (int) date('Y');
$selected_stage_categories = isset($categories[$selected_stage]) && is_array($categories[$selected_stage]) ? $categories[$selected_stage] : [];
$return_query = '?stage=' . rawurlencode($selected_stage) . '&year=' . rawurlencode((string) $selected_year);
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="ami-section-title mb-1"><?php echo html_escape($title); ?></h2>
                <p class="text-muted mb-0">Simpan file, URL, atau keduanya sebagai sumber arsip dokumen PPEPP.</p>
            </div>
            <a class="btn-ami btn-outline-ami" href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents') . $return_query); ?>">Kembali</a>
        </div>

        <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>

        <div class="alert alert-light border small">
            File dan URL bersifat opsional secara terpisah, tetapi minimal salah satunya wajib diisi. Saat mengedit, sumber yang sudah tersimpan tetap digunakan jika tidak diganti.
            <?php if ($document && (!empty($document->original_name) || !empty($document->external_url))): ?>
                <div class="mt-2"><strong>Metadata saat ini:</strong>
                    <?php if (!empty($document->original_name)): ?>File: <?php echo html_escape($document->original_name); ?><?php endif; ?>
                    <?php if (!empty($document->external_url)): ?><?php echo !empty($document->original_name) ? ' · ' : ''; ?>URL tersedia<?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php echo form_open_multipart($action, ['class' => 'needs-validation']); ?>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="ppepp-stage">Tahap <span class="text-danger">*</span></label>
                    <select class="form-control" id="ppepp-stage" name="stage" required>
                        <?php foreach ($stages as $stage_key => $label): ?>
                            <option value="<?php echo html_escape($stage_key); ?>" <?php echo $selected_stage === (string) $stage_key ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="ppepp-category">Kategori <span class="text-danger">*</span></label>
                    <select class="form-control" id="ppepp-category" name="category" required>
                        <option value="">Pilih kategori...</option>
                        <?php foreach ($selected_stage_categories as $category_key => $label): ?>
                            <option value="<?php echo html_escape($category_key); ?>" <?php echo $selected_category === (string) $category_key ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Pilih kategori yang sesuai dengan tahap PPEPP.</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="ppepp-period-year">Tahun dokumen <span class="text-danger">*</span></label>
                    <input class="form-control" id="ppepp-period-year" name="period_year" type="number" min="2000" max="<?php echo html_escape((string) ((int) date('Y') + 1)); ?>" value="<?php echo html_escape((string) $selected_year); ?>" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="ppepp-document-date">Tanggal dokumen</label>
                    <input class="form-control" id="ppepp-document-date" name="document_date" type="date" value="<?php echo html_escape($document->document_date ?? ''); ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="ppepp-document-file">File dokumen</label>
                    <input class="form-control-file" id="ppepp-document-file" name="document_file" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                    <small class="form-text text-muted">PDF, Word, Excel, atau PowerPoint; maksimal 10 MiB.</small>
                </div>
            </div>

            <div class="form-group">
                <label for="ppepp-title">Judul dokumen <span class="text-danger">*</span></label>
                <input class="form-control" id="ppepp-title" name="title" type="text" maxlength="200" value="<?php echo html_escape($document->title ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="ppepp-description">Deskripsi</label>
                <textarea class="form-control" id="ppepp-description" name="description" rows="4" maxlength="10000"><?php echo html_escape($document->description ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="ppepp-external-url">URL eksternal</label>
                <input class="form-control" id="ppepp-external-url" name="external_url" type="url" maxlength="500" value="<?php echo html_escape($document->external_url ?? ''); ?>" placeholder="https://...">
                <small class="form-text text-muted">Gunakan URL HTTP atau HTTPS. File dan URL dapat disimpan bersamaan.</small>
            </div>

            <div class="d-flex justify-content-end border-top pt-3">
                <a class="btn-ami btn-outline-ami mr-2" href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents') . $return_query); ?>">Batal</a>
                <button class="btn-ami btn-primary" type="submit"><i class="fas fa-save" aria-hidden="true"></i> Simpan dokumen</button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
