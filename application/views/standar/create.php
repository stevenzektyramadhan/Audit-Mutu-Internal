<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel" style="max-width: 600px;">
    <div class="ami-panel-body">
        <h2 class="ami-section-title mb-4">Tambah Standar Audit</h2>

        <?php if (validation_errors()): ?>
            <div class="ami-flash ami-flash-error">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <?php echo form_open('standar/store'); ?>
            
            <div class="tw-mb-3">
                <label for="nama_standar">Nama Standar</label>
                <input type="text" class="ami-form-control" id="nama_standar" name="nama_standar" value="<?php echo set_value('nama_standar'); ?>" required>
            </div>

            <div class="tw-mb-4">
                <label for="deskripsi">Deskripsi (Opsional)</label>
                <textarea class="ami-form-control" id="deskripsi" name="deskripsi" rows="4"><?php echo set_value('deskripsi'); ?></textarea>
            </div>

            <div class="ami-actions" style="justify-content:end;">
                <a href="<?php echo site_url('standar'); ?>" class="btn-ami btn-outline-ami">Batal</a>
                <button type="submit" class="btn-ami">Simpan Standar</button>
            </div>

        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
