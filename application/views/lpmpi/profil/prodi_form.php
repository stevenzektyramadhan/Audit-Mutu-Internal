<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$row = isset($row) ? $row : NULL;

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap" style="gap: 12px;">
            <h2 class="ami-section-title m-0"><?php echo html_escape($page_title); ?></h2>
            <a class="btn btn-outline-ami btn-ami" href="<?php echo html_escape(site_url('profil')); ?>">Kembali</a>
        </div>

        <?php if (validation_errors()): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <?php echo form_open($action); ?>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="kode-prodi">Kode Prodi</label>
                    <input type="text" class="form-control" id="kode-prodi" name="kode_prodi" maxlength="20" value="<?php echo html_escape(set_value('kode_prodi', $row ? $row->kode_prodi : '')); ?>">
                </div>
                <div class="form-group col-md-8">
                    <label for="nama-prodi">Nama Program Studi</label>
                    <input type="text" class="form-control" id="nama-prodi" name="nama_prodi" maxlength="200" value="<?php echo html_escape(set_value('nama_prodi', $row ? $row->nama_prodi : '')); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="prodi-status">Status</label>
                    <input type="text" class="form-control" id="prodi-status" name="status" maxlength="50" value="<?php echo html_escape(set_value('status', $row ? $row->status : '')); ?>" placeholder="Contoh: Aktif">
                </div>
                <div class="form-group col-md-4">
                    <label for="prodi-jenjang">Jenjang</label>
                    <input type="text" class="form-control" id="prodi-jenjang" name="jenjang" maxlength="20" value="<?php echo html_escape(set_value('jenjang', $row ? $row->jenjang : '')); ?>" placeholder="Contoh: D3, S1, S2">
                </div>
                <div class="form-group col-md-4">
                    <label for="prodi-akreditasi">Akreditasi</label>
                    <input type="text" class="form-control" id="prodi-akreditasi" name="akreditasi" maxlength="50" value="<?php echo html_escape(set_value('akreditasi', $row ? $row->akreditasi : '')); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="tanggal-sk-akreditasi">Tanggal SK Akreditasi</label>
                    <input type="date" class="form-control" id="tanggal-sk-akreditasi" name="tanggal_sk_akreditasi" value="<?php echo html_escape(set_value('tanggal_sk_akreditasi', $row ? $row->tanggal_sk_akreditasi : '')); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="rasio-dosen-mahasiswa">Rasio Dosen/Mahasiswa</label>
                    <input type="text" class="form-control" id="rasio-dosen-mahasiswa" name="rasio_dosen_mahasiswa" maxlength="20" value="<?php echo html_escape(set_value('rasio_dosen_mahasiswa', $row ? $row->rasio_dosen_mahasiswa : '')); ?>">
                </div>
            </div>

            <div class="d-flex justify-content-end flex-wrap" style="gap: 8px;">
                <a class="btn btn-outline-ami btn-ami" href="<?php echo html_escape(site_url('profil')); ?>">Batal</a>
                <button type="submit" class="btn btn-primary btn-ami">Simpan</button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
