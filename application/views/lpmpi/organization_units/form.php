<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = $unit !== NULL;
$selected_type = set_value('type', $is_edit ? $unit->type : '', FALSE);
$selected_parent = set_value(
    'parent_id',
    $is_edit && $unit->parent_id !== NULL ? (string) $unit->parent_id : '',
    FALSE
);

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel" style="max-width: 760px;">
    <div class="ami-panel-body">
        <h2 class="ami-section-title mb-4">
            <?php echo $is_edit ? 'Edit Unit Organisasi' : 'Tambah Unit Organisasi'; ?>
        </h2>

        <?php if (validation_errors()): ?>
            <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
        <?php endif; ?>

        <?php echo form_open($action); ?>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="code">Kode</label>
                    <input
                        type="text"
                        class="form-control"
                        id="code"
                        name="code"
                        maxlength="50"
                        value="<?php echo ami_e(set_value('code', $is_edit ? $unit->code : '', FALSE)); ?>"
                        placeholder="Contoh: FT atau IF-S1"
                        required
                    >
                    <small class="form-text text-muted">Huruf, angka, titik, garis bawah, atau tanda hubung.</small>
                </div>
                <div class="form-group col-md-8">
                    <label for="name">Nama Unit</label>
                    <input
                        type="text"
                        class="form-control"
                        id="name"
                        name="name"
                        maxlength="200"
                        value="<?php echo ami_e(set_value('name', $is_edit ? $unit->name : '', FALSE)); ?>"
                        placeholder="Contoh: Fakultas Teknik"
                        required
                    >
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="type">Jenis Unit</label>
                    <select class="form-control" id="type" name="type" required>
                        <option value="">Pilih jenis unit...</option>
                        <?php foreach ($type_labels as $value => $label): ?>
                            <option
                                value="<?php echo ami_e($value); ?>"
                                <?php echo set_select('type', $value, $selected_type === $value); ?>
                            >
                                <?php echo ami_e($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-6" data-parent-field>
                    <label for="parent_id">Unit Induk</label>
                    <select class="form-control" id="parent_id" name="parent_id">
                        <option value="">Tanpa induk (root)</option>
                        <?php foreach ($parent_options as $parent): ?>
                            <option
                                value="<?php echo (int) $parent->id; ?>"
                                data-unit-type="<?php echo ami_e($parent->type); ?>"
                                <?php echo set_select(
                                    'parent_id',
                                    (string) $parent->id,
                                    (string) $selected_parent === (string) $parent->id
                                ); ?>
                            >
                                <?php echo ami_e(
                                    str_repeat('— ', (int) $parent->tree_depth)
                                    . $parent->name
                                    . ' (' . $parent->code . ')'
                                    . ((int) $parent->active === 1 ? '' : ' — nonaktif')
                                ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted" data-parent-help>
                        Pilihan induk mengikuti jenis unit.
                    </small>
                </div>
            </div>

            <?php if (!$is_edit): ?>
                <input type="hidden" name="active" value="0">
                <div class="form-group form-check mb-4">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        id="active"
                        name="active"
                        value="1"
                        <?php echo set_checkbox('active', '1', TRUE); ?>
                    >
                    <label class="form-check-label" for="active">Aktifkan unit setelah dibuat</label>
                </div>
            <?php else: ?>
                <div class="alert alert-secondary">
                    Status saat ini:
                    <strong><?php echo (int) $unit->active === 1 ? 'Aktif' : 'Nonaktif'; ?></strong>.
                    Perubahan status dilakukan dari daftar unit agar selalu melalui pemeriksaan unit turunan.
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-end" style="gap: 8px;">
                <a href="<?php echo site_url('organization-units'); ?>" class="btn btn-outline-secondary">
                    Batal
                </a>
                <button type="submit" class="btn btn-ami">
                    <i class="fas fa-save" aria-hidden="true"></i>
                    <?php echo $is_edit ? 'Simpan Perubahan' : 'Simpan Unit'; ?>
                </button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<script nonce="<?php echo ami_csp_nonce(); ?>">
(function () {
    var typeSelect = document.getElementById('type');
    var parentSelect = document.getElementById('parent_id');
    var parentField = document.querySelector('[data-parent-field]');
    var allowed = <?php echo ami_json($allowed_parent_types); ?>;

    if (!typeSelect || !parentSelect || !parentField) {
        return;
    }

    function syncParentOptions() {
        var type = typeSelect.value;
        var allowedTypes = allowed[type] || [];
        var isRoot = type === 'university';
        var options = parentSelect.querySelectorAll('option[data-unit-type]');

        parentField.style.display = isRoot ? 'none' : '';
        parentSelect.required = type !== '' && !isRoot;

        options.forEach(function (option) {
            var visible = allowedTypes.indexOf(option.getAttribute('data-unit-type')) !== -1;
            option.hidden = !visible;
            option.disabled = !visible;
            if (!visible && option.selected) {
                parentSelect.value = '';
            }
        });

        if (isRoot) {
            parentSelect.value = '';
        }
    }

    typeSelect.addEventListener('change', syncParentOptions);
    syncParentOptions();
}());
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
