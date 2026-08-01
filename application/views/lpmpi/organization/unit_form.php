<?php defined('BASEPATH') OR exit('No direct script access allowed'); $is_edit = !empty($unit); include APPPATH . 'views/layouts/header.php'; include APPPATH . 'views/layouts/sidebar.php'; ?>
<div class="ami-panel" style="max-width: 680px;"><div class="ami-panel-body">
    <h2 class="ami-section-title mb-4"><?php echo html_escape($page_title); ?></h2>
    <?php if (validation_errors()): ?><div class="alert alert-danger"><?php echo validation_errors(); ?></div><?php endif; ?>
    <?php echo form_open($action); ?>
    <div class="form-group"><label for="code">Kode Unit</label><input class="form-control" id="code" name="code" maxlength="64" value="<?php echo html_escape(set_value('code', $is_edit ? $unit->code : '')); ?>" required></div>
    <div class="form-group"><label for="name">Nama Unit</label><input class="form-control" id="name" name="name" maxlength="200" value="<?php echo html_escape(set_value('name', $is_edit ? $unit->name : '')); ?>" required></div>
    <div class="form-group"><label for="type">Tipe Unit</label><select class="form-control" id="type" name="type" required><?php foreach (['faculty' => 'Fakultas', 'upps' => 'UPPS', 'study_program' => 'Program Studi', 'institute' => 'Institut', 'bureau' => 'Biro', 'unit' => 'Unit'] as $value => $label): ?><option value="<?php echo html_escape($value); ?>" <?php echo set_select('type', $value, $is_edit && $unit->type === $value); ?>><?php echo html_escape($label); ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label for="parent_id">Parent Unit</label><select class="form-control" id="parent_id" name="parent_id" required><option value="">Pilih parent</option><?php foreach ($units as $parent): ?><?php if (!$is_edit || (int) $parent->id !== (int) $unit->id): ?><option value="<?php echo (int) $parent->id; ?>" <?php echo set_select('parent_id', $parent->id, $is_edit && (int) $unit->parent_id === (int) $parent->id); ?>><?php echo html_escape($parent->code . ' - ' . $parent->name); ?></option><?php endif; ?><?php endforeach; ?></select></div>
    <div class="d-flex justify-content-end" style="gap: var(--ami-space-sm);"><a class="btn btn-secondary" href="<?php echo site_url('lpmpi/organization'); ?>">Batal</a><button class="btn btn-ami" type="submit">Simpan</button></div>
    <?php echo form_close(); ?>
</div></div>
<?php include APPPATH . 'views/layouts/footer.php'; ?>
