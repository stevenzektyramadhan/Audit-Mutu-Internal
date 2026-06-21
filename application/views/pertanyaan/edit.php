<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel" style="max-width:700px;">
    <div class="ami-panel-body">
        <h2 class="ami-section-title mb-4">Edit Pertanyaan Audit</h2>

        <?php if (validation_errors()): ?>
            <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
        <?php endif; ?>

        <?php echo form_open('pertanyaan/update/' . (int) $pertanyaan->id); ?>
            <div class="form-group">
                <label for="standar_id">Standar</label>
                <select class="form-control" id="standar_id" name="standar_id" required>
                    <?php foreach ($standar as $std): ?>
                        <option value="<?php echo (int) $std->id; ?>" <?php echo set_select('standar_id', $std->id, (int) $pertanyaan->standar_id === (int) $std->id); ?>>
                            <?php echo html_escape($std->nama_standar); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-4">
                <label for="isi_pertanyaan">Isi Pertanyaan</label>
                <textarea class="form-control" id="isi_pertanyaan" name="isi_pertanyaan" rows="5" required><?php echo html_escape(set_value('isi_pertanyaan', $pertanyaan->isi_pertanyaan)); ?></textarea>
            </div>
            <div class="ami-actions justify-content-end">
                <a href="<?php echo site_url('pertanyaan'); ?>" class="btn btn-outline-ami btn-ami">Batal</a>
                <button type="submit" class="btn btn-primary btn-ami"><i class="fas fa-save" aria-hidden="true"></i>Simpan perubahan</button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
