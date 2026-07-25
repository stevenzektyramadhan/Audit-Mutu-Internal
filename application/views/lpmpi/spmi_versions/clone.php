<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel" style="max-width: 760px;">
    <div class="ami-panel-body">
        <h2 class="ami-section-title mb-2">Clone ke Draft Baru</h2>
        <p class="text-muted mb-4">
            Sumber: <?php echo ami_e($version->document_code . ' / Revisi ' . $version->revision_number); ?>.
            PDF akan disalin menjadi aset private baru; versi sumber tetap utuh.
        </p>

        <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>

        <?php echo form_open($action); ?>
            <div class="form-group">
                <label for="title">Judul Dokumen</label>
                <input
                    type="text"
                    class="form-control"
                    id="title"
                    name="title"
                    maxlength="255"
                    value="<?php echo ami_e(set_value('title', $version->title, FALSE)); ?>"
                    required
                >
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="revision_number">Nomor Revisi Baru</label>
                    <input
                        type="text"
                        class="form-control"
                        id="revision_number"
                        name="revision_number"
                        maxlength="50"
                        value="<?php echo ami_e(set_value('revision_number')); ?>"
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
                        value="<?php echo ami_e(set_value('effective_date', date('Y-m-d'))); ?>"
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
                        value="<?php echo ami_e(set_value('expires_at')); ?>"
                    >
                </div>
            </div>

            <div class="alert alert-info">
                Clone selalu dimulai sebagai draft. Approval versi sumber tidak diwariskan.
            </div>

            <div class="d-flex justify-content-end" style="gap: 8px;">
                <a
                    href="<?php echo site_url('spmi-versions/show/' . (int) $version->id); ?>"
                    class="btn btn-outline-secondary"
                >
                    Batal
                </a>
                <button type="submit" class="btn btn-ami">
                    <i class="fas fa-copy" aria-hidden="true"></i> Buat Draft Clone
                </button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
