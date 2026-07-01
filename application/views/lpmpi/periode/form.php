<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = isset($periode) && $periode !== NULL;
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel" style="max-width: 600px;">
    <div class="ami-panel-body">
        <h2 class="ami-section-title mb-4">
            <?php echo $is_edit ? 'Edit Periode Audit' : 'Tambah Periode Audit'; ?>
        </h2>

        <?php if (validation_errors()): ?>
            <div class="alert alert-danger" style="background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.2); color: #ea868f; border-radius: 7px; padding: 12px 15px; font-size: 14px; margin-bottom: 20px;">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <?php echo form_open($action); ?>

            <div class="mb-3">
                <label for="nama_periode" class="form-label text-light">Nama Periode</label>
                <input type="text" class="form-control bg-dark text-light border-secondary" id="nama_periode" name="nama_periode" value="<?php echo html_escape(set_value('nama_periode', $is_edit ? $periode->nama_periode : '')); ?>" required style="border-radius: 7px;" placeholder="Contoh: Semester Ganjil 2025/2026">
            </div>

            <div class="mb-3">
                <label for="tahun_akademik" class="form-label text-light">Tahun Akademik</label>
                <input type="text" class="form-control bg-dark text-light border-secondary" id="tahun_akademik" name="tahun_akademik" value="<?php echo html_escape(set_value('tahun_akademik', $is_edit ? $periode->tahun_akademik : '')); ?>" required style="border-radius: 7px;" placeholder="Contoh: 2025/2026">
            </div>

            <div class="mb-3">
                <label for="semester" class="form-label text-light">Semester</label>
                <select class="form-control bg-dark text-light border-secondary" id="semester" name="semester" required style="border-radius: 7px;">
                    <option value="ganjil" <?php echo set_select('semester', 'ganjil', $is_edit && $periode->semester === 'ganjil'); ?>>Ganjil</option>
                    <option value="genap" <?php echo set_select('semester', 'genap', $is_edit && $periode->semester === 'genap'); ?>>Genap</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="tanggal_buka" class="form-label text-light">Tanggal Buka</label>
                <input type="date" class="form-control bg-dark text-light border-secondary" id="tanggal_buka" name="tanggal_buka" value="<?php echo html_escape(set_value('tanggal_buka', $is_edit ? $periode->tanggal_buka : '')); ?>" required style="border-radius: 7px;">
            </div>

            <div class="mb-3">
                <label for="tanggal_tutup" class="form-label text-light">Tanggal Tutup</label>
                <input type="date" class="form-control bg-dark text-light border-secondary" id="tanggal_tutup" name="tanggal_tutup" value="<?php echo html_escape(set_value('tanggal_tutup', $is_edit ? $periode->tanggal_tutup : '')); ?>" required style="border-radius: 7px;">
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_aktif" name="is_aktif" value="1" <?php echo set_checkbox('is_aktif', '1', $is_edit && (int) $periode->is_aktif === 1); ?>>
                    <label class="form-check-label text-light" for="is_aktif">
                        Aktifkan periode ini
                    </label>
                    <small class="d-block text-muted" style="font-size: 12px; margin-top: 2px;">
                        Hanya satu periode yang bisa aktif dalam satu waktu. Periode lain akan otomatis dinonaktifkan.
                    </small>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?php echo site_url('periode'); ?>" class="btn btn-secondary text-light" style="border-radius: 7px; background: transparent; border: 1px solid var(--ami-border);">Batal</a>
                <button type="submit" class="btn btn-ami" style="background: var(--ami-blue); color: white; border: none;">
                    <i class="fas fa-save" aria-hidden="true"></i>
                    <?php echo $is_edit ? 'Simpan Perubahan' : 'Simpan Periode'; ?>
                </button>
            </div>

        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
