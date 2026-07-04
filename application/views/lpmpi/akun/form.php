<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = isset($is_edit) ? $is_edit : FALSE;
$account = isset($account) ? $account : NULL;
$selected_role = set_value('role', $is_edit ? $account->role : '');
$selected_jenis_unit = set_value('jenis_unit', $is_edit ? $account->jenis_unit : '');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel" style="max-width: 680px;">
    <div class="ami-panel-body">
        <h2 class="ami-section-title mb-4"><?php echo $is_edit ? 'Edit Akun' : 'Tambah Akun'; ?></h2>

        <?php if (validation_errors()): ?>
            <div class="alert alert-danger" style="background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.2); color: #ea868f; border-radius: 7px; padding: 12px 15px; font-size: 14px; margin-bottom: 20px;">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <?php echo form_open($action); ?>
            <div class="mb-3">
                <label for="nama" class="form-label text-light">Nama Lengkap</label>
                <input type="text" class="form-control bg-dark text-light border-secondary" id="nama" name="nama" value="<?php echo html_escape(set_value('nama', $is_edit ? $account->nama : '')); ?>" required style="border-radius: 7px;">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label text-light">Email</label>
                <input type="email" class="form-control bg-dark text-light border-secondary" id="email" name="email" value="<?php echo html_escape(set_value('email', $is_edit ? $account->email : '')); ?>" required style="border-radius: 7px;">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label text-light"><?php echo $is_edit ? 'Password Baru' : 'Password'; ?></label>
                <div class="ami-password-wrap">
                    <input type="password" class="form-control bg-dark text-light border-secondary" id="password" name="password" <?php echo $is_edit ? '' : 'required'; ?> style="border-radius: 7px;">
                    <button type="button" class="ami-password-toggle" data-password-toggle="password" aria-label="Tampilkan password" aria-pressed="false">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
                <?php if ($is_edit): ?>
                    <small class="text-muted">Kosongkan jika password tidak ingin diubah.</small>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label text-light">Role</label>
                <select class="form-control bg-dark text-light border-secondary" id="role" name="role" required style="border-radius: 7px;" data-role-select>
                    <option value="">Pilih role...</option>
                    <option value="auditor" <?php echo set_select('role', 'auditor', $selected_role === 'auditor'); ?>>Auditor</option>
                    <option value="auditee" <?php echo set_select('role', 'auditee', $selected_role === 'auditee'); ?>>Auditee</option>
                </select>
            </div>

            <div data-unit-fields>
                <div class="mb-3">
                    <label for="nama_unit" class="form-label text-light">Nama Unit</label>
                    <input type="text" class="form-control bg-dark text-light border-secondary" id="nama_unit" name="nama_unit" value="<?php echo html_escape(set_value('nama_unit', $is_edit ? $account->nama_unit : '')); ?>" style="border-radius: 7px;" placeholder="Contoh: Program Studi Informatika">
                </div>

                <div class="mb-4">
                    <label for="jenis_unit" class="form-label text-light">Jenis Unit</label>
                    <select class="form-control bg-dark text-light border-secondary" id="jenis_unit" name="jenis_unit" style="border-radius: 7px;">
                        <option value="">Pilih jenis unit...</option>
                        <option value="prodi" <?php echo set_select('jenis_unit', 'prodi', $selected_jenis_unit === 'prodi'); ?>>Prodi</option>
                        <option value="unit" <?php echo set_select('jenis_unit', 'unit', $selected_jenis_unit === 'unit'); ?>>Unit</option>
                        <option value="lembaga" <?php echo set_select('jenis_unit', 'lembaga', $selected_jenis_unit === 'lembaga'); ?>>Lembaga</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?php echo site_url('lpmpi/akun'); ?>" class="btn btn-secondary text-light" style="border-radius: 7px; background: transparent; border: 1px solid var(--ami-border);">Batal</a>
                <button type="submit" class="btn btn-ami" style="background: var(--ami-blue); color: white; border: none;">
                    <i class="fas fa-save" aria-hidden="true"></i>
                    <?php echo $is_edit ? 'Simpan Perubahan' : 'Simpan Akun'; ?>
                </button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<script>
(function () {
    var roleSelect = document.querySelector('[data-role-select]');
    var unitFields = document.querySelector('[data-unit-fields]');
    var namaUnit = document.getElementById('nama_unit');
    var jenisUnit = document.getElementById('jenis_unit');

    if (!roleSelect || !unitFields || !namaUnit || !jenisUnit) {
        return;
    }

    function syncUnitFields() {
        var isAuditee = roleSelect.value === 'auditee';
        unitFields.style.display = isAuditee ? '' : 'none';
        namaUnit.required = isAuditee;
        jenisUnit.required = isAuditee;
        namaUnit.disabled = !isAuditee;
        jenisUnit.disabled = !isAuditee;
    }

    roleSelect.addEventListener('change', syncUnitFields);
    syncUnitFields();
}());
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
