<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$row = isset($row) ? $row : NULL;
$faculties = isset($faculties) && is_array($faculties) ? $faculties : [];
$return_url = isset($return_url) ? $return_url : 'lpmpi/master-data-prodi-staf';
$selected_faculty = set_value('faculty_id', isset($selected_faculty_id) ? $selected_faculty_id : '');
$require_faculty = !empty($require_faculty);

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="ami-section-head">
            <h2 class="ami-section-title m-0"><?php echo html_escape($page_title); ?></h2>
            <a class="btn-outline-ami btn-ami" href="<?php echo html_escape(site_url($return_url)); ?>">Kembali</a>
        </div>

        <?php if (validation_errors()): ?>
            <div class="ami-alert ami-alert-danger" role="alert">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

            <?php echo form_open($action); ?>
                <input type="hidden" name="require_faculty" value="<?php echo $require_faculty ? '1' : '0'; ?>">
            <div class="tw-flex tw-flex-wrap tw-gap-4">
                <div class="ami-form-group tw-w-full md:tw-w-1/3">
                    <label for="kode-prodi">Kode Prodi</label>
                    <input type="text" class="ami-form-control" id="kode-prodi" name="kode_prodi" maxlength="20" value="<?php echo html_escape(set_value('kode_prodi', $row ? $row->kode_prodi : '')); ?>" required>
                </div>
                <div class="ami-form-group tw-w-full md:tw-w-2/3">
                    <label for="nama-prodi">Nama Program Studi</label>
                    <input type="text" class="ami-form-control" id="nama-prodi" name="nama_prodi" maxlength="200" value="<?php echo html_escape(set_value('nama_prodi', $row ? $row->nama_prodi : '')); ?>" required>
                </div>
            </div>

            <div class="ami-form-group">
                <label for="prodi-faculty">Fakultas</label>
                <select class="ami-form-control" id="prodi-faculty" name="faculty_id" <?php echo $require_faculty ? 'required' : ''; ?>>
                    <option value="">Pilih Fakultas aktif...</option>
                    <?php foreach ($faculties as $faculty): ?>
                        <option value="<?php echo (int) $faculty->id; ?>" <?php echo set_select('faculty_id', $faculty->id, (string) $selected_faculty === (string) $faculty->id); ?>><?php echo html_escape($faculty->code . ' — ' . $faculty->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text ami-muted">Pilih Fakultas secara eksplisit sebagai parent Program Studi.</small>
            </div>

            <div class="tw-flex tw-flex-wrap tw-gap-4">
                <div class="ami-form-group tw-w-full md:tw-w-1/3">
                    <label for="prodi-status">Status</label>
                    <input type="text" class="ami-form-control" id="prodi-status" name="status" maxlength="50" value="<?php echo html_escape(set_value('status', $row ? $row->status : '')); ?>" placeholder="Contoh: Aktif">
                </div>
                <div class="ami-form-group tw-w-full md:tw-w-1/3">
                    <label for="prodi-jenjang">Jenjang</label>
                    <input type="text" class="ami-form-control" id="prodi-jenjang" name="jenjang" maxlength="20" value="<?php echo html_escape(set_value('jenjang', $row ? $row->jenjang : '')); ?>" placeholder="Contoh: D3, S1, S2" required>
                </div>
                <div class="ami-form-group tw-w-full md:tw-w-1/3">
                    <label for="prodi-akreditasi">Akreditasi</label>
                    <input type="text" class="ami-form-control" id="prodi-akreditasi" name="akreditasi" maxlength="50" value="<?php echo html_escape(set_value('akreditasi', $row ? $row->akreditasi : '')); ?>">
                </div>
            </div>

            <div class="tw-flex tw-flex-wrap tw-gap-4">
                <div class="ami-form-group tw-w-full md:tw-w-1/2">
                    <label for="tanggal-sk-akreditasi">Tanggal SK Akreditasi</label>
                    <input type="date" class="ami-form-control" id="tanggal-sk-akreditasi" name="tanggal_sk_akreditasi" value="<?php echo html_escape(set_value('tanggal_sk_akreditasi', $row ? $row->tanggal_sk_akreditasi : '')); ?>">
                </div>
                <div class="ami-form-group tw-w-full md:tw-w-1/2">
                    <label for="rasio-dosen-mahasiswa">Rasio Dosen/Mahasiswa</label>
                    <input type="text" class="ami-form-control" id="rasio-dosen-mahasiswa" name="rasio_dosen_mahasiswa" maxlength="20" value="<?php echo html_escape(set_value('rasio_dosen_mahasiswa', $row ? $row->rasio_dosen_mahasiswa : '')); ?>">
                </div>
            </div>

            <div class="tw-flex tw-justify-end tw-flex-wrap" style="gap: 8px;">
                <a class="btn-outline-ami btn-ami" href="<?php echo html_escape(site_url($return_url)); ?>">Batal</a>
                <button type="submit" class="tw-button-primary btn-ami">Simpan</button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
