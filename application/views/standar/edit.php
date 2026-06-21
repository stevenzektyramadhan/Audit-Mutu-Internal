<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel" style="max-width:600px;">
    <div class="ami-panel-body">
        <h2 class="ami-section-title mb-4">Edit Standar Audit</h2>

        <?php if (validation_errors()): ?>
            <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
        <?php endif; ?>

        <?php echo form_open('standar/update/' . (int) $standar->id); ?>
            <div class="form-group">
                <label for="nama_standar">Nama Standar</label>
                <input type="text" class="form-control" id="nama_standar" name="nama_standar" value="<?php echo html_escape(set_value('nama_standar', $standar->nama_standar)); ?>" required>
            </div>
            <div class="form-group mb-4">
                <label for="deskripsi">Deskripsi</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"><?php echo html_escape(set_value('deskripsi', $standar->deskripsi)); ?></textarea>
            </div>
            <div class="ami-actions justify-content-end">
                <a href="<?php echo site_url('standar'); ?>" class="btn btn-outline-ami btn-ami">Batal</a>
                <button type="submit" class="btn btn-primary btn-ami"><i class="fas fa-save" aria-hidden="true"></i>Simpan perubahan</button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
