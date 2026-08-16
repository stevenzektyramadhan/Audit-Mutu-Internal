<?php defined('BASEPATH') OR exit('No direct script access allowed'); include APPPATH . 'views/layouts/header.php'; include APPPATH . 'views/layouts/sidebar.php'; ?>
<div class="ami-panel"><div class="ami-panel-body"><h2 class="ami-section-title mb-3">Workspace SPMI</h2>
<form method="get" action="<?php echo site_url('auditee/spmi'); ?>" class="ami-filter-bar mb-3">
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
            <?php foreach (['' => 'Semua status', 'draft' => 'draft', 'submitted' => 'submitted', 'returned_for_revision' => 'returned_for_revision', 'resubmitted' => 'resubmitted'] as $value => $label): ?>
                <option value="<?php echo html_escape($value); ?>" <?php echo $filters['status'] === $value ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn-ami btn-outline-ami"><i class="fas fa-filter" aria-hidden="true"></i> Filter</button>
    <a href="<?php echo site_url('auditee/spmi'); ?>" class="btn-ami btn-outline-ami"><i class="fas fa-sync-alt" aria-hidden="true"></i> Reset</a>
</form>
<?php if (empty($assignments)): ?><div class="ami-empty">Belum ada penugasan SPMI yang dapat diisi.</div><?php else: ?><div class="table-responsive"><table class="table ami-table"><thead><tr><th>Siklus</th><th>Paket</th><th>Status</th><th>Aksi</th></tr></thead><tbody><?php foreach ($assignments as $assignment): ?><tr><td><?php echo html_escape($assignment->cycle_code . ' — ' . $assignment->cycle_title); ?></td><td><?php echo html_escape($assignment->source_package_code . ' — ' . $assignment->source_package_title); ?></td><td><?php echo html_escape($assignment->submission_status ?: 'draft'); ?></td><td><a href="<?php echo site_url('auditee/spmi/assignment/' . (int) $assignment->id); ?>">Buka</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div></div>
<?php include APPPATH . 'views/layouts/footer.php'; ?>
