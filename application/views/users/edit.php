<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel" style="max-width:600px;">
    <div class="ami-panel-body">
        <h2 class="ami-section-title mb-4">Edit Pengguna</h2>

        <?php if (validation_errors()): ?>
            <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
        <?php endif; ?>

        <?php echo form_open('users/update/' . (int) $user->id); ?>
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo html_escape(set_value('nama', $user->nama)); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo html_escape(set_value('email', $user->email)); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password Baru</label>
                <input type="password" class="form-control" id="password" name="password">
                <small class="text-muted">Kosongkan jika password tidak ingin diubah.</small>
            </div>
            <div class="form-group mb-4">
                <label for="role">Role Pengguna</label>
                <select class="form-control" id="role" name="role" required>
                    <option value="super_admin" <?php echo set_select('role', 'super_admin', $user->role === 'super_admin'); ?>>Super Admin</option>
                    <option value="auditor" <?php echo set_select('role', 'auditor', $user->role === 'auditor'); ?>>Auditor</option>
                    <option value="auditee" <?php echo set_select('role', 'auditee', $user->role === 'auditee'); ?>>Auditee</option>
                </select>
            </div>
            <div class="ami-actions justify-content-end">
                <a href="<?php echo site_url('users'); ?>" class="btn btn-outline-ami btn-ami">Batal</a>
                <button type="submit" class="btn btn-primary btn-ami"><i class="fas fa-save" aria-hidden="true"></i>Simpan perubahan</button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
