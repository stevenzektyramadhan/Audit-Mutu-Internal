<?php defined('BASEPATH') OR exit('No direct script access allowed'); include APPPATH . 'views/layouts/header.php'; include APPPATH . 'views/layouts/sidebar.php'; ?>
<div class="ami-panel"><div class="ami-panel-body"><h2 class="ami-section-title">Penilaian SPMI</h2><p>Pilih penugasan dengan bukti auditee yang sudah submitted, resubmitted, atau returned_for_revision.</p>
<form method="get" action="<?php echo site_url('auditor/spmi'); ?>" class="ami-filter-bar mb-3">
    <div class="ami-filter-select">
        <label class="ami-stat-label" for="cycle_id">Siklus</label>
        <select class="form-control" id="cycle_id" name="cycle_id">
            <option value="0"><?php echo html_escape('Semua siklus'); ?></option>
            <?php foreach ($cycle_options as $cycle): ?>
                <option value="<?php echo html_escape((string) $cycle->id); ?>" <?php echo (int) $filters['cycle_id'] === (int) $cycle->id ? 'selected' : ''; ?>><?php echo html_escape($cycle->cycle_code . ' — ' . $cycle->title); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="ami-filter-select">
        <label class="ami-stat-label" for="status">Status</label>
        <select class="form-control" id="status" name="status">
            <?php foreach (['' => 'Semua status', 'submitted' => 'submitted', 'resubmitted' => 'resubmitted', 'returned_for_revision' => 'returned_for_revision'] as $value => $label): ?>
                <option value="<?php echo html_escape($value); ?>" <?php echo $filters['status'] === $value ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn-ami btn-outline-ami"><i class="fas fa-filter" aria-hidden="true"></i> Filter</button>
    <a href="<?php echo site_url('auditor/spmi'); ?>" class="btn-ami btn-outline-ami"><i class="fas fa-sync-alt" aria-hidden="true"></i> Reset</a>
</form><div class="table-responsive"><table class="table"><thead><tr><th>Siklus</th><th>Paket</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($assignments as $assignment): ?><tr><td><?php echo html_escape($assignment->cycle_code . ' — ' . $assignment->cycle_title); ?></td><td><?php echo html_escape($assignment->source_package_code . ' — ' . $assignment->source_package_title); ?></td><td><?php echo html_escape(($assignment->assessment_status ?: 'Belum dibuka') . ' / ' . $assignment->submission_status); ?></td><td><a href="<?php echo site_url('auditor/spmi/assignment/' . (int) $assignment->id); ?>">Buka</a></td></tr><?php endforeach; ?><?php if (!$assignments): ?><tr><td colspan="4">Belum ada penugasan SPMI yang dapat dinilai.</td></tr><?php endif; ?></tbody></table></div></div></div>
<?php include APPPATH . 'views/layouts/footer.php'; ?>
