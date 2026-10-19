<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel" style="max-width: 600px;">
    <div class="ami-panel-body">
        <h2 class="ami-section-title mb-4">Tambah Pertanyaan Audit</h2>

        <?php if (validation_errors()): ?>
            <div class="ami-flash ami-flash-error">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <?php echo form_open('pertanyaan/store'); ?>
            
            <div class="tw-mb-3">
                <label for="standar_id">Pilih Standar</label>
                <select class="ami-form-control" id="standar_id" name="standar_id" required>
                    <option value="">Pilih standar...</option>
                    <?php if (!empty($standar)): ?>
                        <?php foreach ($standar as $std): ?>
                            <option value="<?php echo $std->id; ?>" <?php echo set_select('standar_id', $std->id); ?>><?php echo html_escape($std->nama_standar); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="tw-mb-4">
                <label for="isi_pertanyaan">Isi Pertanyaan</label>
                <textarea class="ami-form-control" id="isi_pertanyaan" name="isi_pertanyaan" rows="4" required><?php echo set_value('isi_pertanyaan'); ?></textarea>
            </div>

            <div class="ami-actions" style="justify-content:end;">
                <a href="<?php echo site_url('pertanyaan'); ?>" class="btn-ami btn-outline-ami">Batal</a>
                <button type="submit" class="btn-ami">Simpan Pertanyaan</button>
            </div>

        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
