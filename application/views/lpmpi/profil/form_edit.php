<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$profil = isset($profil) ? $profil : NULL;

if (!function_exists('profil_value')) {
    function profil_value($profil, $field)
    {
        return $profil && isset($profil->{$field}) ? $profil->{$field} : '';
    }
}

$logo_src = '';
if ($profil && !empty($profil->logo_path)) {
    $logo_src = base_url('uploads/profil/' . rawurlencode($profil->logo_path));
} elseif ($profil && !empty($profil->logo_url)) {
    $logo_src = $profil->logo_url;
}

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap mb-4" style="gap:12px;">
            <h2 class="ami-section-title m-0">Edit Profil Lembaga</h2>
            <a href="<?php echo site_url('profil'); ?>" class="btn btn-outline-ami btn-ami">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali
            </a>
        </div>

        <?php if (validation_errors()): ?>
            <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
        <?php endif; ?>

        <?php echo form_open_multipart('profil/update'); ?>
            <div class="row">
                <div class="col-lg-6">
                    <h3 class="ami-section-title mb-3">Identitas PDDikti</h3>

                    <div class="form-group">
                        <label for="nama_pt_pddikti">Nama PT di PDDikti</label>
                        <input type="text" class="form-control" id="nama_pt_pddikti" name="nama_pt_pddikti" value="<?php echo html_escape(set_value('nama_pt_pddikti', profil_value($profil, 'nama_pt_pddikti'))); ?>" placeholder="Contoh: Universitas Muhammadiyah Bangka Belitung">
                    </div>

                    <div class="form-group">
                        <label for="id_pt_pddikti">ID PT PDDikti</label>
                        <input type="text" class="form-control" id="id_pt_pddikti" name="id_pt_pddikti" value="<?php echo html_escape(set_value('id_pt_pddikti', profil_value($profil, 'id_pt_pddikti'))); ?>">
                    </div>

                    <div class="form-group">
                        <label for="nama_pt">Nama Universitas</label>
                        <input type="text" class="form-control" id="nama_pt" name="nama_pt" value="<?php echo html_escape(set_value('nama_pt', profil_value($profil, 'nama_pt'))); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="kode_pt">Kode PT</label>
                        <input type="text" class="form-control" id="kode_pt" name="kode_pt" value="<?php echo html_escape(set_value('kode_pt', profil_value($profil, 'kode_pt'))); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="nomor_sk_pt">Nomor SK PT</label>
                            <input type="text" class="form-control" id="nomor_sk_pt" name="nomor_sk_pt" value="<?php echo html_escape(set_value('nomor_sk_pt', profil_value($profil, 'nomor_sk_pt'))); ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="tanggal_sk_pt">Tanggal SK PT</label>
                            <input type="date" class="form-control" id="tanggal_sk_pt" name="tanggal_sk_pt" value="<?php echo html_escape(set_value('tanggal_sk_pt', profil_value($profil, 'tanggal_sk_pt'))); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tanggal_berdiri">Tanggal Berdiri</label>
                        <input type="date" class="form-control" id="tanggal_berdiri" name="tanggal_berdiri" value="<?php echo html_escape(set_value('tanggal_berdiri', profil_value($profil, 'tanggal_berdiri'))); ?>">
                    </div>
                </div>

                <div class="col-lg-6">
                    <h3 class="ami-section-title mb-3">Kontak dan Akreditasi</h3>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="jumlah_dosen">Jumlah Dosen</label>
                            <input type="number" min="0" class="form-control" id="jumlah_dosen" name="jumlah_dosen" value="<?php echo html_escape(set_value('jumlah_dosen', profil_value($profil, 'jumlah_dosen'))); ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="jumlah_tendik">Jumlah Tendik</label>
                            <input type="number" min="0" class="form-control" id="jumlah_tendik" name="jumlah_tendik" value="<?php echo html_escape(set_value('jumlah_tendik', profil_value($profil, 'jumlah_tendik'))); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="akreditasi">Akreditasi</label>
                            <input type="text" class="form-control" id="akreditasi" name="akreditasi" value="<?php echo html_escape(set_value('akreditasi', profil_value($profil, 'akreditasi'))); ?>" placeholder="Unggul / Baik Sekali / Baik">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="akreditasi_berlaku_sampai">Berlaku Sampai</label>
                            <input type="date" class="form-control" id="akreditasi_berlaku_sampai" name="akreditasi_berlaku_sampai" value="<?php echo html_escape(set_value('akreditasi_berlaku_sampai', profil_value($profil, 'akreditasi_berlaku_sampai'))); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="status_pt">Status PT</label>
                            <input type="text" class="form-control" id="status_pt" name="status_pt" value="<?php echo html_escape(set_value('status_pt', profil_value($profil, 'status_pt'))); ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="kode_pos">Kode Pos</label>
                            <input type="text" class="form-control" id="kode_pos" name="kode_pos" value="<?php echo html_escape(set_value('kode_pos', profil_value($profil, 'kode_pos'))); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="telepon">Telepon</label>
                            <input type="text" class="form-control" id="telepon" name="telepon" value="<?php echo html_escape(set_value('telepon', profil_value($profil, 'telepon'))); ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="faksimile">Faksimile</label>
                            <input type="text" class="form-control" id="faksimile" name="faksimile" value="<?php echo html_escape(set_value('faksimile', profil_value($profil, 'faksimile'))); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo html_escape(set_value('email', profil_value($profil, 'email'))); ?>">
                    </div>
                </div>
            </div>

            <div class="ami-panel mt-3 mb-3" style="box-shadow:none;">
                <div class="ami-panel-body">
                    <h3 class="ami-section-title mb-3">Logo Lembaga</h3>
                    <div class="row align-items-center">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <?php if ($logo_src !== ''): ?>
                                <img src="<?php echo html_escape($logo_src); ?>" alt="Logo lembaga" style="width:130px;height:130px;object-fit:contain;border:1px solid var(--ami-border);border-radius:8px;padding:10px;">
                            <?php else: ?>
                                <div class="ami-empty py-3">
                                    <div class="ami-empty-icon"><i class="fas fa-image" aria-hidden="true"></i></div>
                                    <div class="ami-empty-title">Belum ada logo</div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-9">
                            <div class="form-group">
                                <label for="logo">Upload Logo Manual</label>
                                <input type="file" class="form-control" id="logo" name="logo" accept=".jpg,.jpeg,.png,.gif">
                                <small class="form-text text-muted">Logo manual akan diprioritaskan dibanding logo dari PDDikti.</small>
                            </div>
                            <div class="form-group mb-0">
                                <label for="logo_url">URL Logo PDDikti</label>
                                <input type="url" class="form-control" id="logo_url" name="logo_url" value="<?php echo html_escape(set_value('logo_url', profil_value($profil, 'logo_url'))); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ami-actions justify-content-end">
                <a href="<?php echo site_url('profil'); ?>" class="btn btn-outline-ami btn-ami">
                    <i class="fas fa-times" aria-hidden="true"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary btn-ami" data-loading-text="Menyimpan...">
                    <i class="fas fa-save" aria-hidden="true"></i> Simpan Profil
                </button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
