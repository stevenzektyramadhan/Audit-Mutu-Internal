<?php defined('BASEPATH') OR exit('No direct script access allowed'); include APPPATH . 'views/layouts/header.php'; include APPPATH . 'views/layouts/sidebar.php';
$children = [];
foreach ($units as $unit) {
    $parent_key = $unit->parent_id === NULL ? 'root' : (int) $unit->parent_id;
    $children[$parent_key][] = $unit;
}
$assignment_counts = [];
foreach ($assignments as $assignment) $assignment_counts[$assignment->organization_unit_id] = isset($assignment_counts[$assignment->organization_unit_id]) ? $assignment_counts[$assignment->organization_unit_id] + 1 : 1;
function render_organization_units($parent_key, $children, $counts, $can_manage) {
    if (empty($children[$parent_key])) return;
    echo '<ol class="pl-4">';
    foreach ($children[$parent_key] as $unit) {
        echo '<li class="mb-3">';
        echo '<div class="d-flex align-items-center flex-wrap" style="gap: var(--ami-space-sm);">';
        echo '<strong>' . html_escape($unit->name) . '</strong><span class="text-muted">' . html_escape($unit->code) . '</span>';
        echo '<span class="status-badge status-' . ((int) $unit->is_active === 1 ? 'aktif' : 'nonaktif') . '">' . ((int) $unit->is_active === 1 ? 'Aktif' : 'Nonaktif') . '</span>';
        echo '<span class="text-muted">' . (int) (isset($counts[$unit->id]) ? $counts[$unit->id] : 0) . ' penempatan</span>';
        if ($can_manage) { echo '<a class="ami-action-btn" href="' . site_url('lpmpi/organization/unit/edit/' . (int) $unit->id) . '" title="Edit unit"><i class="fas fa-edit" aria-hidden="true"></i></a>'; echo form_open('lpmpi/organization/unit/toggle/' . (int) $unit->id, ['class' => 'd-inline']); echo '<button class="ami-action-btn ' . ((int) $unit->is_active === 1 ? 'warning' : 'success') . '" type="submit" title="Ubah status"><i class="fas fa-toggle-on" aria-hidden="true"></i></button>'; echo form_close(); }
        echo '</div>';
        render_organization_units((int) $unit->id, $children, $counts, $can_manage);
        echo '</li>';
    }
    echo '</ol>';
}
?>
<div class="ami-panel"><div class="ami-panel-body">
    <div class="d-flex align-items-center justify-content-between flex-wrap mb-4" style="gap: var(--ami-space-md);">
        <h2 class="ami-section-title m-0">Struktur Organisasi</h2>
        <div>
            <?php if ($can_manage): ?><a class="btn-ami btn-outline-ami" href="<?php echo site_url('lpmpi/organization/unit/create'); ?>"><i class="fas fa-plus" aria-hidden="true"></i> Tambah unit</a><?php endif; ?>
            <?php if ($can_assign): ?><a class="btn-ami btn-outline-ami" href="<?php echo site_url('lpmpi/organization/assignment/create'); ?>"><i class="fas fa-user-plus" aria-hidden="true"></i> Penempatan</a><?php endif; ?>
            <?php if ($can_view_capabilities): ?><a class="btn-ami btn-outline-ami" href="<?php echo site_url('lpmpi/organization/capabilities'); ?>"><i class="fas fa-key" aria-hidden="true"></i> Kapabilitas</a><?php endif; ?>
        </div>
    </div>
    <?php if (empty($units)): ?><div class="ami-empty"><div class="ami-empty-icon"><i class="fas fa-sitemap" aria-hidden="true"></i></div><div class="ami-empty-title">Belum ada struktur organisasi</div><div>Tambahkan unit organisasi untuk mulai menyusun hierarki.</div></div>
    <?php else: ?><div class="mb-4"><div class="text-muted mb-2">Hierarki unit</div><?php render_organization_units('root', $children, $assignment_counts, $can_manage); ?></div>
        <div class="table-responsive"><table class="table ami-table"><thead><tr><th>Unit</th><th>Kode</th><th>Status</th><th>Ringkasan penempatan</th></tr></thead><tbody>
        <?php foreach ($units as $unit): ?><tr><td><?php echo html_escape($unit->name); ?></td><td><?php echo html_escape($unit->code); ?></td><td><?php echo (int) $unit->is_active === 1 ? 'Aktif' : 'Nonaktif'; ?></td><td><?php echo (int) (isset($assignment_counts[$unit->id]) ? $assignment_counts[$unit->id] : 0); ?> pengguna</td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</div></div>
<?php include APPPATH . 'views/layouts/footer.php'; ?>
