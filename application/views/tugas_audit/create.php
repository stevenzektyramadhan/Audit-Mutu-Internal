<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel" style="max-width: 600px;">
    <div class="ami-panel-body">
        <h2 class="ami-section-title mb-4">Buat Tugas Audit Baru</h2>

        <?php if (validation_errors()): ?>
            <div class="ami-flash ami-flash-error">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <?php echo form_open('tugas_audit/store'); ?>
            <div class="tw-mb-3">
                <label for="periode_id">Pilih Periode</label>
                <select class="ami-form-control" id="periode_id" name="periode_id" required>
                    <option value="">Pilih periode...</option>
                    <?php if (!empty($periode)): ?>
                        <?php foreach ($periode as $p): ?>
                            <option value="<?php echo (int) $p->id; ?>" <?php echo set_select('periode_id', $p->id); ?>><?php echo html_escape($p->nama_periode); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="tw-mb-3">
                <label for="auditee_id">Pilih Auditee</label>
                <select class="ami-form-control" id="auditee_id" name="auditee_id" required>
                    <option value="">Pilih auditee...</option>
                    <?php if (!empty($auditee)): ?>
                        <?php foreach ($auditee as $a): ?>
                            <option value="<?php echo $a->id; ?>" <?php echo set_select('auditee_id', $a->id); ?>><?php echo html_escape($a->nama); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="tw-mb-3">
                <label for="auditor_id">Pilih Auditor</label>
                <select class="ami-form-control" id="auditor_id" name="auditor_id" required>
                    <option value="">Pilih auditor...</option>
                    <?php if (!empty($auditor)): ?>
                        <?php foreach ($auditor as $a): ?>
                            <option value="<?php echo $a->id; ?>" <?php echo set_select('auditor_id', $a->id); ?>><?php echo html_escape($a->nama); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="tw-mb-4">
                <label for="standar_id">Pilih Standar</label>
                <select class="ami-form-control" id="standar_id" name="standar_id" required>
                    <option value="">Pilih standar...</option>
                    <?php if (!empty($standar)): ?>
                        <?php foreach ($standar as $s): ?>
                            <option value="<?php echo $s->id; ?>" <?php echo set_select('standar_id', $s->id); ?>><?php echo html_escape($s->nama_standar); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="ami-actions" style="justify-content:end;">
                <a href="<?php echo site_url('tugas_audit'); ?>" class="btn-ami btn-outline-ami">Batal</a>
                <button type="submit" class="btn-ami">Buat Tugas</button>
            </div>

        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
