<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = $version !== NULL;
$unit_value = set_value(
    'organization_unit_id',
    $is_edit ? (string) $version->organization_unit_id : (string) $selected_unit_id,
    FALSE
);

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel" style="max-width: 860px;">
    <div class="ami-panel-body">
        <h2 class="ami-section-title mb-4">
            <?php echo $is_edit ? 'Edit Draft Versi SPMI' : 'Buat Draft Versi SPMI'; ?>
        </h2>

        <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>

        <?php echo form_open_multipart($action); ?>
            <div class="form-row">
                <div class="form-group col-md-7">
                    <label for="organization_unit_id">Unit Organisasi</label>
                    <select
                        class="form-control"
                        id="organization_unit_id"
                        name="organization_unit_id"
                        <?php echo $is_edit ? 'disabled' : 'required'; ?>
                    >
                        <option value="">Pilih unit...</option>
                        <?php foreach ($units as $unit): ?>
                            <?php if (!$unit) { continue; } ?>
                            <option
                                value="<?php echo (int) $unit->id; ?>"
                                <?php echo (string) $unit_value === (string) $unit->id ? 'selected' : ''; ?>
                            >
                                <?php echo ami_e($unit->name . ' (' . $unit->code . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($is_edit): ?>
                        <small class="form-text text-muted">Scope unit tidak dapat dipindahkan setelah draft dibuat.</small>
                    <?php endif; ?>
                </div>
                <div class="form-group col-md-5">
                    <label for="document_code">Kode Dokumen</label>
                    <input
                        type="text"
                        class="form-control"
                        id="document_code"
                        name="document_code"
                        maxlength="64"
                        value="<?php echo ami_e(set_value('document_code', $is_edit ? $version->document_code : '', FALSE)); ?>"
                        placeholder="Contoh: SPMI-STD-01"
                        <?php echo $is_edit ? 'readonly' : 'required'; ?>
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="title">Judul Dokumen</label>
                <input
                    type="text"
                    class="form-control"
                    id="title"
                    name="title"
                    maxlength="255"
                    value="<?php echo ami_e(set_value('title', $is_edit ? $version->title : '', FALSE)); ?>"
                    required
                >
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="revision_number">Nomor Revisi</label>
                    <input
                        type="text"
                        class="form-control"
                        id="revision_number"
                        name="revision_number"
                        maxlength="50"
                        value="<?php echo ami_e(set_value('revision_number', $is_edit ? $version->revision_number : '', FALSE)); ?>"
                        placeholder="Contoh: 01"
                        required
                    >
                </div>
                <div class="form-group col-md-4">
                    <label for="effective_date">Tanggal Efektif</label>
                    <input
                        type="date"
                        class="form-control"
                        id="effective_date"
                        name="effective_date"
                        value="<?php echo ami_e(set_value('effective_date', $is_edit ? $version->effective_date : date('Y-m-d'), FALSE)); ?>"
                        required
                    >
                </div>
                <div class="form-group col-md-4">
                    <label for="expires_at">Tanggal Berakhir</label>
                    <input
                        type="date"
                        class="form-control"
                        id="expires_at"
                        name="expires_at"
                        value="<?php echo ami_e(set_value('expires_at', $is_edit && $version->expires_at !== NULL ? $version->expires_at : '', FALSE)); ?>"
                    >
                    <small class="form-text text-muted">Opsional.</small>
                </div>
            </div>

            <div class="form-group">
                <label for="source_pdf">
                    PDF Sumber <?php echo $is_edit ? '(opsional)' : ''; ?>
                </label>
                <input
                    type="file"
                    class="form-control-file"
                    id="source_pdf"
                    name="source_pdf"
                    accept="application/pdf,.pdf"
                    <?php echo $is_edit ? '' : 'required'; ?>
                >
                <small class="form-text text-muted">
                    PDF maksimal 20 MiB, disimpan private, dan diverifikasi checksum saat diunduh.
                    <?php if ($is_edit): ?>
                        Kosongkan jika file tidak diganti. File saat ini:
                        <strong><?php echo ami_e($version->source_file_original_name); ?></strong>.
                    <?php endif; ?>
                </small>
            </div>

            <div class="alert alert-secondary">
                Setelah dikirim ke review, isi draft dibekukan. Perubahan berikutnya harus melalui clone draft baru.
            </div>

            <div class="d-flex justify-content-end" style="gap: 8px;">
                <a
                    class="btn btn-outline-secondary"
                    href="<?php echo $is_edit
                        ? site_url('spmi-versions/show/' . (int) $version->id)
                        : site_url('spmi-versions?unit_id=' . (int) $selected_unit_id); ?>"
                >
                    Batal
                </a>
                <button type="submit" class="btn btn-ami">
                    <i class="fas fa-save" aria-hidden="true"></i>
                    <?php echo $is_edit ? 'Simpan Perubahan' : 'Simpan Draft'; ?>
                </button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
