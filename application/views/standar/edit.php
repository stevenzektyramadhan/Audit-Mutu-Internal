<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel" style="max-width:600px;">
    <div class="ami-panel-body">
        <h2 class="ami-section-title mb-4">Edit Standar Audit</h2>

        <?php if (validation_errors()): ?>
            <div class="ami-flash ami-flash-error"><?php echo validation_errors(); ?></div>
        <?php endif; ?>

        <?php echo form_open('standar/update/' . (int) $standar->id); ?>
            <div class="tw-mb-3">
                <label for="nama_standar">Nama Standar</label>
                <input type="text" class="ami-form-control" id="nama_standar" name="nama_standar" value="<?php echo html_escape(set_value('nama_standar', $standar->nama_standar)); ?>" required>
            </div>
            <div class="tw-mb-4">
                <label for="deskripsi">Deskripsi</label>
                <textarea class="ami-form-control" id="deskripsi" name="deskripsi" rows="4"><?php echo html_escape(set_value('deskripsi', $standar->deskripsi)); ?></textarea>
            </div>
            <div class="ami-actions tw-justify-end">
                <a href="<?php echo site_url('standar'); ?>" class="btn-outline-ami btn-ami">Batal</a>
                <button type="submit" class="btn-ami"><i class="fas fa-save" aria-hidden="true"></i>Simpan perubahan</button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
