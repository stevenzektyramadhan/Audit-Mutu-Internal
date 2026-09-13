<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$actor_role = (string) $this->session->userdata('role');
$role_options = $actor_role === 'super_admin'
    ? ['super_admin' => 'Super Admin', 'admin_lpmpi' => 'Admin LPMPI', 'auditor' => 'Auditor', 'auditee' => 'Auditee']
    : ['auditor' => 'Auditor', 'auditee' => 'Auditee'];
$selected_role = set_value('role', $user->role);
$selected_jenis_unit = set_value('jenis_unit', isset($user->jenis_unit) ? $user->jenis_unit : '');
?>

<div id="users-management-root" class="tw-mx-auto">
    <section class="users-surface form-shell">
        <div class="users-surface-body">
            <div class="form-header">
                <a href="<?php echo site_url('users'); ?>" class="users-back-link">
                    <svg class="users-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                    Kembali ke Pengguna
                </a>
                <p class="users-eyebrow">Direktori akses</p>
                <h1 class="users-heading">Edit Pengguna</h1>
                <p class="form-subtitle">Perbarui identitas, password, atau hak akses akun pengguna.</p>
            </div>

            <?php if (validation_errors()): ?>
                <div class="users-error"><?php echo validation_errors(); ?></div>
            <?php endif; ?>

            <?php echo form_open('users/update/' . (int) $user->id); ?>
                <div class="form-section">
                    <div class="form-section-heading">
                        <svg class="users-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="8" r="3"/><path d="M5 20c.8-3.2 3.1-5 7-5s6.2 1.8 7 5"/>
                        </svg>
                        <span><strong>Informasi Akun</strong><small>Data dasar untuk identitas pengguna.</small></span>
                    </div>
                    <div class="users-form-grid">
                        <div class="form-field">
                            <label for="nama" class="users-label">Nama Lengkap</label>
                            <input type="text" class="users-control" id="nama" name="nama" value="<?php echo html_escape(set_value('nama', $user->nama)); ?>" required>
                        </div>
                        <div class="form-field">
                            <label for="email" class="users-label">Alamat Email</label>
                            <input type="email" class="users-control" id="email" name="email" value="<?php echo html_escape(set_value('email', $user->email)); ?>" required>
                        </div>
                        <div class="form-field">
                            <label for="password" class="users-label">Password Baru</label>
                            <div class="users-password">
                                <input type="password" class="users-control" id="password" name="password">
                                <button type="button" class="users-password-toggle" data-password-toggle="password" aria-label="Tampilkan password" aria-pressed="false">
                                    <svg class="users-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                            <small class="users-muted">Kosongkan jika password tidak ingin diubah.</small>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-heading">
                        <svg class="users-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 3 4 7v5c0 4.4 3.4 7.7 8 9 4.6-1.3 8-4.6 8-9V7l-8-4Z"/><path d="m9 12 2 2 4-4"/>
                        </svg>
                        <span><strong>Hak Akses</strong><small>Tentukan peran dan unit kerja pengguna.</small></span>
                    </div>
                    <div class="users-form-grid">
                        <div class="form-field">
                            <label for="role" class="users-label">Role Pengguna</label>
                            <select class="users-control" id="role" name="role" required data-role-select>
                                <?php foreach ($role_options as $value => $label): ?>
                                    <option value="<?php echo html_escape($value); ?>" <?php echo set_select('role', $value, $selected_role === $value); ?>><?php echo html_escape($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div data-unit-fields class="form-field-group tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                            <div>
                                <label for="nama_unit" class="users-label">Unit</label>
                                <input type="text" class="users-control" id="nama_unit" name="nama_unit" value="<?php echo html_escape(set_value('nama_unit', isset($user->nama_unit) ? $user->nama_unit : '')); ?>" placeholder="Contoh: Program Studi Informatika">
                            </div>
                            <div>
                                <label for="jenis_unit" class="users-label">Jenis Unit</label>
                                <select class="users-control" id="jenis_unit" name="jenis_unit">
                                    <option value="">Pilih jenis unit...</option>
                                    <option value="prodi" <?php echo set_select('jenis_unit', 'prodi', $selected_jenis_unit === 'prodi'); ?>>Prodi</option>
                                    <option value="unit" <?php echo set_select('jenis_unit', 'unit', $selected_jenis_unit === 'unit'); ?>>Unit</option>
                                    <option value="lembaga" <?php echo set_select('jenis_unit', 'lembaga', $selected_jenis_unit === 'lembaga'); ?>>Lembaga</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="users-actions tw-mt-6 tw-flex tw-flex-col tw-justify-end tw-gap-2 sm:tw-flex-row">
                    <a href="<?php echo site_url('users'); ?>" class="users-button users-button-secondary tw-inline-flex tw-justify-center">Batal</a>
                    <button type="submit" class="users-button users-button-primary">Simpan perubahan</button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </section>
</div>

<script>
(function () {
    var roleSelect = document.querySelector('[data-role-select]');
    var unitFields = document.querySelector('[data-unit-fields]');
    var namaUnit = document.getElementById('nama_unit');
    var jenisUnit = document.getElementById('jenis_unit');
    if (!roleSelect || !unitFields || !namaUnit || !jenisUnit) return;
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
