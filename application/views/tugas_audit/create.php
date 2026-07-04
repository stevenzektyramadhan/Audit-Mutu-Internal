<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel" style="max-width: 600px;">
    <div class="ami-panel-body">
        <h2 class="ami-section-title mb-4">Buat Tugas Audit Baru</h2>

        <?php if (validation_errors()): ?>
            <div class="alert alert-danger" style="background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.2); color: #ea868f; border-radius: 7px; padding: 12px 15px; font-size: 14px; margin-bottom: 20px;">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <?php echo form_open('tugas_audit/store'); ?>
            <div class="mb-3">
                <label for="periode_id" class="form-label text-light">Pilih Periode</label>
                <select class="form-control bg-dark text-light border-secondary" id="periode_id" name="periode_id" required style="border-radius: 7px;">
                    <option value="">Pilih periode...</option>
                    <?php if (!empty($periode)): ?>
                        <?php foreach ($periode as $p): ?>
                            <option value="<?php echo (int) $p->id; ?>" <?php echo set_select('periode_id', $p->id); ?>><?php echo html_escape($p->nama_periode); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="auditee_id" class="form-label text-light">Pilih Auditee</label>
                <select class="form-control bg-dark text-light border-secondary" id="auditee_id" name="auditee_id" required style="border-radius: 7px;">
                    <option value="">Pilih auditee...</option>
                    <?php if (!empty($auditee)): ?>
                        <?php foreach ($auditee as $a): ?>
                            <option value="<?php echo $a->id; ?>" <?php echo set_select('auditee_id', $a->id); ?>><?php echo html_escape($a->nama); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="auditor_id" class="form-label text-light">Pilih Auditor</label>
                <select class="form-control bg-dark text-light border-secondary" id="auditor_id" name="auditor_id" required style="border-radius: 7px;">
                    <option value="">Pilih auditor...</option>
                    <?php if (!empty($auditor)): ?>
                        <?php foreach ($auditor as $a): ?>
                            <option value="<?php echo $a->id; ?>" <?php echo set_select('auditor_id', $a->id); ?>><?php echo html_escape($a->nama); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="mb-4">
                <label for="standar_id" class="form-label text-light">Pilih Standar</label>
                <select class="form-control bg-dark text-light border-secondary" id="standar_id" name="standar_id" required style="border-radius: 7px;">
                    <option value="">Pilih standar...</option>
                    <?php if (!empty($standar)): ?>
                        <?php foreach ($standar as $s): ?>
                            <option value="<?php echo $s->id; ?>" <?php echo set_select('standar_id', $s->id); ?>><?php echo html_escape($s->nama_standar); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?php echo site_url('tugas_audit'); ?>" class="btn btn-secondary text-light" style="border-radius: 7px; background: transparent; border: 1px solid var(--ami-border);">Batal</a>
                <button type="submit" class="btn btn-ami" style="background: var(--ami-blue); color: white; border: none;">Buat Tugas</button>
            </div>

        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
