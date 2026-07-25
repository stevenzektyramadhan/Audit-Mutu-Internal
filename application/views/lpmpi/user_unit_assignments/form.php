<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="mb-4">
            <div class="ami-stat-label">Pengguna</div>
            <h2 class="ami-section-title mb-1"><?php echo ami_e($user->nama); ?></h2>
            <div class="text-muted"><?php echo ami_e($user->email); ?> · <?php echo ami_e($user->role); ?></div>
        </div>

        <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
        <?php echo form_open($action); ?>
            <div class="form-group">
                <label for="organization-unit-id">Unit Organisasi</label>
                <select id="organization-unit-id" name="organization_unit_id" class="form-control" required>
                    <option value="">Pilih unit aktif</option>
                    <?php foreach ($units as $unit): ?>
                        <option
                            value="<?php echo (int) $unit->id; ?>"
                            <?php echo set_select('organization_unit_id', (string) $unit->id); ?>
                        >
                            <?php echo ami_e($unit->code . ' — ' . $unit->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="position-code">Kode Jabatan</label>
                <input
                    id="position-code"
                    type="text"
                    name="position_code"
                    class="form-control"
                    maxlength="64"
                    value="<?php echo ami_e(set_value('position_code')); ?>"
                    placeholder="Contoh: KAPRODI, AUDITOR, STAFF"
                    required
                >
                <small class="form-text text-muted">
                    Huruf, angka, titik, garis bawah, dan tanda hubung; otomatis disimpan uppercase.
                </small>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="valid-from">Berlaku Mulai</label>
                    <input
                        id="valid-from"
                        type="date"
                        name="valid_from"
                        class="form-control"
                        value="<?php echo ami_e(set_value('valid_from', date('Y-m-d'))); ?>"
                        required
                    >
                </div>
                <div class="form-group col-md-6">
                    <label for="valid-until">Berlaku Sampai</label>
                    <input
                        id="valid-until"
                        type="date"
                        name="valid_until"
                        class="form-control"
                        value="<?php echo ami_e(set_value('valid_until')); ?>"
                    >
                    <small class="form-text text-muted">Kosongkan bila berlaku tanpa batas akhir.</small>
                </div>
            </div>

            <div class="custom-control custom-checkbox mb-4">
                <input
                    type="checkbox"
                    class="custom-control-input"
                    id="is-primary"
                    name="is_primary"
                    value="1"
                    <?php echo set_checkbox('is_primary', '1'); ?>
                >
                <label class="custom-control-label" for="is-primary">
                    Jadikan assignment primary
                </label>
                <small class="form-text text-muted">
                    Hanya satu assignment primary boleh aktif pada periode yang sama.
                </small>
            </div>

            <div class="d-flex flex-wrap" style="gap: 8px;">
                <button type="submit" class="btn btn-primary btn-ami">
                    <i class="fas fa-save" aria-hidden="true"></i> Simpan
                </button>
                <a href="<?php echo site_url('user-unit-assignments/' . (int) $user->id); ?>" class="btn btn-outline-ami btn-ami">
                    Batal
                </a>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
