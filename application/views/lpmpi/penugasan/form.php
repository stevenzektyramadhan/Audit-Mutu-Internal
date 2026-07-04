<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel" style="max-width: 720px;">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Buat Penugasan Auditor</h2>
            <a href="<?php echo site_url('lpmpi/penugasan'); ?>" class="btn-ami btn-outline-ami">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali
            </a>
        </div>

        <?php if (validation_errors()): ?>
            <div class="alert ami-flash ami-flash-error" role="alert">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                <span><?php echo validation_errors(); ?></span>
            </div>
        <?php endif; ?>

        <?php echo form_open('lpmpi/penugasan/store'); ?>
            <div class="form-group">
                <label for="periode_id">Periode Audit</label>
                <select class="form-control" id="periode_id" name="periode_id" required>
                    <option value="">Pilih periode...</option>
                    <?php foreach ($periode as $item): ?>
                        <option value="<?php echo (int) $item->id; ?>" <?php echo set_select('periode_id', $item->id); ?>>
                            <?php echo html_escape($item->nama_periode); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="standar_id">Standar</label>
                <select class="form-control" id="standar_id" name="standar_id" required>
                    <option value="">Pilih standar...</option>
                    <?php foreach ($standar as $item): ?>
                        <option value="<?php echo (int) $item->id; ?>" <?php echo set_select('standar_id', $item->id); ?>>
                            <?php echo html_escape($item->nama_standar); ?>
                            <?php if (isset($item->total_pertanyaan)): ?>
                                (<?php echo (int) $item->total_pertanyaan; ?> pertanyaan)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="auditor_id">Auditor</label>
                <select class="form-control" id="auditor_id" name="auditor_id" required>
                    <option value="">Pilih auditor...</option>
                    <?php foreach ($auditor as $item): ?>
                        <option value="<?php echo (int) $item->id; ?>" <?php echo set_select('auditor_id', $item->id); ?>>
                            <?php echo html_escape($item->nama); ?> - <?php echo html_escape($item->email); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="auditee_id">Auditee</label>
                <select class="form-control" id="auditee_id" name="auditee_id" required>
                    <option value="">Pilih auditee...</option>
                    <?php foreach ($auditee as $item): ?>
                        <option value="<?php echo (int) $item->id; ?>" <?php echo set_select('auditee_id', $item->id); ?>>
                            <?php echo html_escape($item->nama); ?>
                            <?php if (!empty($item->nama_unit)): ?>
                                - <?php echo html_escape($item->nama_unit); ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn-ami" style="background: var(--ami-blue); color: #ffffff; border: 0;">
                    <i class="fas fa-save" aria-hidden="true"></i> Simpan Penugasan
                </button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
