<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = !empty($unit);
$unit_types = ['faculty' => 'Fakultas', 'bureau' => 'Biro', 'unit' => 'Unit', 'institute' => 'Lembaga'];
$parent_types = ['university' => 'Universitas', 'bureau' => 'Biro'];
$selected_type = set_value('type', $is_edit ? $unit->type : '');
$selected_parent = set_value('parent_id', $is_edit ? $unit->parent_id : '');
$parent_hint = ['faculty' => 'Universitas', 'bureau' => 'Universitas', 'unit' => 'Biro', 'institute' => 'Universitas'];

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<main class="master-data-root">
    <div class="master-data-breadcrumb">Beranda / Management / Master Data Organisasi &amp; Staf / <?php echo html_escape($page_title); ?></div>
    <section class="ami-panel">
        <div class="ami-panel-body">
            <div class="master-directory-head">
                <div><div class="ami-eyebrow">Struktur organisasi</div><h2 class="ami-section-title mb-1"><?php echo html_escape($page_title); ?></h2><p>Unit generik hanya dapat ditempatkan pada parent sesuai matriks canonical.</p></div>
                <a class="btn btn-outline-ami btn-ami" href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf')); ?>">Kembali</a>
            </div>

            <?php if (validation_errors()): ?><div class="alert alert-danger" role="alert"><?php echo validation_errors(); ?></div><?php endif; ?>

            <?php echo form_open($action); ?>
                <div class="form-row">
                    <div class="form-group col-md-4"><label for="unit-code">Kode Unit</label><input class="form-control" id="unit-code" name="code" maxlength="64" value="<?php echo html_escape(set_value('code', $is_edit ? $unit->code : '')); ?>" required></div>
                    <div class="form-group col-md-8"><label for="unit-name">Nama Unit</label><input class="form-control" id="unit-name" name="name" maxlength="200" value="<?php echo html_escape(set_value('name', $is_edit ? $unit->name : '')); ?>" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6"><label for="unit-type">Tipe Unit</label><select class="form-control" id="unit-type" name="type" required><option value="">Pilih tipe unit...</option><?php foreach ($unit_types as $type => $label): ?><option value="<?php echo html_escape($type); ?>" <?php echo set_select('type', $type, $selected_type === $type); ?>><?php echo html_escape($label); ?></option><?php endforeach; ?></select><small class="form-text text-muted">Pilihan canonical: faculty, bureau, unit, atau institute.</small></div>
                    <div class="form-group col-md-6"><label for="unit-parent">Parent Unit</label><select class="form-control" id="unit-parent" name="parent_id" required><option value="">Pilih parent aktif...</option><?php foreach ($units as $parent): ?><?php $parent_type = (string) $parent->type; $valid_parent = isset($parent_types[$parent_type]) && (int) $parent->is_active === 1 && (!$is_edit || (int) $parent->id !== (int) $unit->id); ?><?php if ($valid_parent): ?><option value="<?php echo (int) $parent->id; ?>" <?php echo set_select('parent_id', $parent->id, (string) $selected_parent === (string) $parent->id); ?>><?php echo html_escape($parent_types[$parent_type] . ' — ' . $parent->code . ' — ' . $parent->name); ?></option><?php endif; ?><?php endforeach; ?></select><small class="form-text text-muted">Matrix: Fakultas/Biro/Lembaga → Universitas; Unit → Biro<?php if ($selected_type && isset($parent_hint[$selected_type])): ?>. Tipe saat ini menyarankan <?php echo html_escape($parent_hint[$selected_type]); ?><?php endif; ?>.</small></div>
                </div>
                <div class="d-flex justify-content-end flex-wrap" style="gap: var(--ami-space-sm);"><a class="btn btn-outline-ami btn-ami" href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf')); ?>">Batal</a><button type="submit" class="btn btn-primary btn-ami">Simpan Unit</button></div>
            <?php echo form_close(); ?>
        </div>
    </section>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
