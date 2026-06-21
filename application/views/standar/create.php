<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel" style="max-width: 600px;">
    <div class="ami-panel-body">
        <h2 class="ami-section-title mb-4">Tambah Standar Audit</h2>

        <?php if (validation_errors()): ?>
            <div class="alert alert-danger" style="background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.2); color: #ea868f; border-radius: 7px; padding: 12px 15px; font-size: 14px; margin-bottom: 20px;">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <?php echo form_open('standar/store'); ?>
            
            <div class="mb-3">
                <label for="nama_standar" class="form-label text-light">Nama Standar</label>
                <input type="text" class="form-control bg-dark text-light border-secondary" id="nama_standar" name="nama_standar" value="<?php echo set_value('nama_standar'); ?>" required style="border-radius: 7px;">
            </div>

            <div class="mb-4">
                <label for="deskripsi" class="form-label text-light">Deskripsi (Opsional)</label>
                <textarea class="form-control bg-dark text-light border-secondary" id="deskripsi" name="deskripsi" rows="4" style="border-radius: 7px;"><?php echo set_value('deskripsi'); ?></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?php echo site_url('standar'); ?>" class="btn btn-secondary text-light" style="border-radius: 7px; background: transparent; border: 1px solid var(--ami-border);">Batal</a>
                <button type="submit" class="btn btn-ami" style="background: var(--ami-blue); color: white; border: none;">Simpan Standar</button>
            </div>

        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
