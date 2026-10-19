<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$row = isset($row) ? $row : NULL;

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="ami-section-head">
            <h2 class="ami-section-title m-0"><?php echo html_escape($page_title); ?></h2>
            <a class="btn-outline-ami btn-ami" href="<?php echo html_escape(site_url('profil')); ?>">Kembali</a>
        </div>

        <?php if (validation_errors()): ?>
            <div class="ami-alert ami-alert-danger" role="alert">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <?php echo form_open($action); ?>
            <div class="tw-flex tw-flex-wrap tw-gap-4">
                <div class="ami-form-group tw-w-full md:tw-w-1/2">
                    <label for="mahasiswa-jenjang">Jenjang</label>
                    <input type="text" class="ami-form-control" id="mahasiswa-jenjang" name="jenjang" maxlength="50" value="<?php echo html_escape(set_value('jenjang', $row ? $row->jenjang : '')); ?>" placeholder="Contoh: D3, S1, S2" required>
                </div>
                <div class="ami-form-group tw-w-full md:tw-w-1/2">
                    <label for="mahasiswa-jumlah">Jumlah</label>
                    <input type="number" class="ami-form-control" id="mahasiswa-jumlah" name="jumlah" min="0" value="<?php echo html_escape(set_value('jumlah', $row ? $row->jumlah : '')); ?>" required>
                </div>
            </div>

            <div class="tw-flex tw-justify-end tw-flex-wrap" style="gap: 8px;">
                <a class="btn-outline-ami btn-ami" href="<?php echo html_escape(site_url('profil')); ?>">Batal</a>
                <button type="submit" class="tw-button-primary btn-ami">Simpan</button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
