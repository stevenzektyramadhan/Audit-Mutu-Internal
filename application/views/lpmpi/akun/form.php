<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = isset($is_edit) ? $is_edit : FALSE;
$account = isset($account) ? $account : NULL;
$selected_role = set_value('role', $is_edit ? $account->role : '');
$selected_jenis_unit = set_value('jenis_unit', $is_edit ? $account->jenis_unit : '');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel tw-max-w-3xl">
    <div class="ami-panel-body">
        <h2 class="ami-section-title tw-mb-4"><?php echo $is_edit ? 'Edit Akun' : 'Tambah Akun'; ?></h2>

        <?php if (validation_errors()): ?>
            <div class="ami-flash ami-flash-error">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <?php echo form_open($action); ?>
            <div class="tw-mb-4">
                <label for="nama" class="tw-block tw-mb-1 tw-text-sm tw-font-semibold">Nama Lengkap</label>
                <input type="text" class="ami-form-control" id="nama" name="nama" value="<?php echo html_escape(set_value('nama', $is_edit ? $account->nama : '')); ?>" required>
            </div>

            <div class="tw-mb-4">
                <label for="email" class="tw-block tw-mb-1 tw-text-sm tw-font-semibold">Email</label>
                <input type="email" class="ami-form-control" id="email" name="email" value="<?php echo html_escape(set_value('email', $is_edit ? $account->email : '')); ?>" required>
            </div>

            <div class="tw-mb-4">
                <label for="password" class="tw-block tw-mb-1 tw-text-sm tw-font-semibold"><?php echo $is_edit ? 'Password Baru' : 'Password'; ?></label>
                <div class="ami-password-wrap">
                    <input type="password" class="ami-form-control" id="password" name="password" <?php echo $is_edit ? '' : 'required'; ?> >
                    <button type="button" class="ami-password-toggle" data-password-toggle="password" aria-label="Tampilkan password" aria-pressed="false">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
                <?php if ($is_edit): ?>
                    <small class="tw-text-xs tw-text-slate-500">Kosongkan jika password tidak ingin diubah.</small>
                <?php endif; ?>
            </div>

            <div class="tw-mb-4">
                <label for="role" class="tw-block tw-mb-1 tw-text-sm tw-font-semibold">Role</label>
                <select class="ami-form-control" id="role" name="role" required data-role-select>
                    <option value="">Pilih role...</option>
                    <option value="auditor" <?php echo set_select('role', 'auditor', $selected_role === 'auditor'); ?>>Auditor</option>
                    <option value="auditee" <?php echo set_select('role', 'auditee', $selected_role === 'auditee'); ?>>Auditi</option>
                </select>
            </div>

            <div data-unit-fields>
                <div class="tw-mb-4">
                    <label for="nama_unit" class="tw-block tw-mb-1 tw-text-sm tw-font-semibold">Nama Unit</label>
                    <input type="text" class="ami-form-control" id="nama_unit" name="nama_unit" value="<?php echo html_escape(set_value('nama_unit', $is_edit ? $account->nama_unit : '')); ?>" placeholder="Contoh: Program Studi Informatika">
                </div>

                <div class="tw-mb-4">
                    <label for="jenis_unit" class="tw-block tw-mb-1 tw-text-sm tw-font-semibold">Jenis Unit</label>
                    <select class="ami-form-control" id="jenis_unit" name="jenis_unit">
                        <option value="">Pilih jenis unit...</option>
                        <option value="prodi" <?php echo set_select('jenis_unit', 'prodi', $selected_jenis_unit === 'prodi'); ?>>Prodi</option>
                        <option value="unit" <?php echo set_select('jenis_unit', 'unit', $selected_jenis_unit === 'unit'); ?>>Unit</option>
                        <option value="lembaga" <?php echo set_select('jenis_unit', 'lembaga', $selected_jenis_unit === 'lembaga'); ?>>Lembaga</option>
                    </select>
                </div>
            </div>

            <div class="tw-flex tw-justify-end tw-gap-2">
                <a href="<?php echo site_url('lpmpi/akun'); ?>" class="btn-ami btn-outline-ami">Batal</a>
                <button type="submit" class="btn-ami">
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
