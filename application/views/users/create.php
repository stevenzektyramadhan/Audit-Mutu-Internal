<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel" style="max-width: 600px;">
    <div class="ami-panel-body">
        <h2 class="ami-section-title mb-4">Tambah Pengguna Baru</h2>

        <?php if (validation_errors()): ?>
            <div class="alert alert-danger" style="background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.2); color: #ea868f; border-radius: 7px; padding: 12px 15px; font-size: 14px; margin-bottom: 20px;">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <?php echo form_open('users/store'); ?>
            
            <div class="mb-3">
                <label for="nama" class="form-label text-light">Nama Lengkap</label>
                <input type="text" class="form-control bg-dark text-light border-secondary" id="nama" name="nama" value="<?php echo set_value('nama'); ?>" required style="border-radius: 7px;">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label text-light">Alamat Email</label>
                <input type="email" class="form-control bg-dark text-light border-secondary" id="email" name="email" value="<?php echo set_value('email'); ?>" required style="border-radius: 7px;">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label text-light">Password</label>
                <div class="ami-password-wrap">
                    <input type="password" class="form-control bg-dark text-light border-secondary" id="password" name="password" required style="border-radius: 7px;">
                    <button type="button" class="ami-password-toggle" data-password-toggle="password" aria-label="Tampilkan password" aria-pressed="false">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label for="role" class="form-label text-light">Role Pengguna</label>
                <select class="form-control bg-dark text-light border-secondary" id="role" name="role" required style="border-radius: 7px;">
                    <option value="">Pilih role...</option>
                    <option value="super_admin" <?php echo set_select('role', 'super_admin'); ?>>Super Admin</option>
                    <option value="admin_lpmpi" <?php echo set_select('role', 'admin_lpmpi'); ?>>Admin LPMPI</option>
                    <option value="auditor" <?php echo set_select('role', 'auditor'); ?>>Auditor</option>
                    <option value="auditee" <?php echo set_select('role', 'auditee'); ?>>Auditee</option>
                </select>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?php echo site_url('users'); ?>" class="btn btn-secondary text-light" style="border-radius: 7px; background: transparent; border: 1px solid var(--ami-border);">Batal</a>
                <button type="submit" class="btn btn-ami" style="background: var(--ami-blue); color: white; border: none;">Simpan Pengguna</button>
            </div>

        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
