<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$row = isset($row) ? $row : NULL;
$faculties = isset($faculties) && is_array($faculties) ? $faculties : [];
$action = isset($action) ? $action : '';
$return_url = isset($return_url) ? $return_url : 'lpmpi/master-data-prodi-staf';

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<main class="master-data-root">
    <div class="master-data-breadcrumb">Beranda / Management / Master Data Organisasi &amp; Staf / Hubungkan Prodi</div>
    <section class="ami-panel">
        <div class="ami-panel-body">
            <div class="master-directory-head">
                <div><div class="ami-eyebrow">Struktur organisasi</div><h2 class="ami-section-title mb-1">Hubungkan Prodi ke Fakultas</h2><p>Hubungan ini harus dipilih admin secara eksplisit.</p></div>
                <a class="btn btn-outline-ami btn-ami" href="<?php echo html_escape(site_url($return_url)); ?>">Kembali</a>
            </div>

            <?php if (validation_errors()): ?><div class="alert alert-danger" role="alert"><?php echo validation_errors(); ?></div><?php endif; ?>

            <div class="alert alert-warning" role="alert">Sistem tidak menebak atau membuat hubungan berdasarkan kemiripan nama. Pilih Fakultas yang benar untuk Prodi ini.</div>
            <dl class="row mb-4">
                <dt class="col-sm-3">Kode Prodi</dt><dd class="col-sm-9"><?php echo html_escape($row ? $row->kode_prodi : '-'); ?></dd>
                <dt class="col-sm-3">Nama Prodi</dt><dd class="col-sm-9"><?php echo html_escape($row ? $row->nama_prodi : '-'); ?></dd>
            </dl>

            <?php echo form_open($action); ?>
                <div class="form-group">
                    <label for="prodi-link-faculty">Fakultas</label>
                    <select class="form-control" id="prodi-link-faculty" name="faculty_id" required>
                        <option value="">Pilih Fakultas aktif...</option>
                        <?php foreach ($faculties as $faculty): ?>
                            <option value="<?php echo (int) $faculty->id; ?>" <?php echo set_select('faculty_id', $faculty->id); ?>><?php echo html_escape($faculty->code . ' — ' . $faculty->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="d-flex justify-content-end flex-wrap" style="gap: var(--ami-space-sm);"><a class="btn btn-outline-ami btn-ami" href="<?php echo html_escape(site_url($return_url)); ?>">Batal</a><button type="submit" class="btn btn-primary btn-ami">Hubungkan ke Fakultas</button></div>
            <?php echo form_close(); ?>
        </div>
    </section>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
