<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = $standard !== NULL;
$selected_group = set_value(
    'group_type',
    $is_edit ? $standard->group_type : 'education',
    FALSE
);
$selected_type = set_value(
    'standard_type',
    $is_edit ? $standard->standard_type : 'sn_dikti',
    FALSE
);

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel" style="max-width: 900px;">
    <div class="ami-panel-body">
        <div class="mb-4">
            <div class="text-muted mb-1">
                <?php echo ami_e($version->document_code); ?>
                / Revisi <?php echo ami_e($version->revision_number); ?>
            </div>
            <h2 class="ami-section-title">
                <?php echo $is_edit ? 'Edit Standar SPMI' : 'Tambah Standar SPMI'; ?>
            </h2>
        </div>

        <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>

        <?php echo form_open($action); ?>
            <div class="form-row">
                <div class="form-group col-md-8">
                    <label for="code">Kode Standar</label>
                    <input
                        class="form-control"
                        type="text"
                        id="code"
                        name="code"
                        maxlength="64"
                        value="<?php echo ami_e(set_value(
                            'code',
                            $is_edit ? $standard->code : '',
                            FALSE
                        )); ?>"
                        placeholder="Contoh: STD-PEND-01"
                        required
                    >
                    <small class="form-text text-muted">
                        Unik di dalam versi ini; kode yang sama boleh dipakai pada versi lain.
                    </small>
                </div>
                <div class="form-group col-md-4">
                    <label for="sort_order">Urutan</label>
                    <input
                        class="form-control"
                        type="number"
                        id="sort_order"
                        name="sort_order"
                        min="1"
                        max="100000"
                        value="<?php echo (int) set_value(
                            'sort_order',
                            (string) $default_sort_order,
                            FALSE
                        ); ?>"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="name">Nama Standar</label>
                <input
                    class="form-control"
                    type="text"
                    id="name"
                    name="name"
                    maxlength="255"
                    value="<?php echo ami_e(set_value(
                        'name',
                        $is_edit ? $standard->name : '',
                        FALSE
                    )); ?>"
                    required
                >
            </div>

            <div class="form-row">
                <div class="form-group col-md-7">
                    <label for="group_type">Kelompok</label>
                    <select
                        class="form-control"
                        id="group_type"
                        name="group_type"
                        required
                    >
                        <?php foreach ($group_labels as $value => $label): ?>
                            <option
                                value="<?php echo ami_e($value); ?>"
                                <?php echo (string) $selected_group === (string) $value ? 'selected' : ''; ?>
                            >
                                <?php echo ami_e($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-5">
                    <label for="standard_type">Jenis Standar</label>
                    <select
                        class="form-control"
                        id="standard_type"
                        name="standard_type"
                        required
                    >
                        <?php foreach ($type_labels as $value => $label): ?>
                            <option
                                value="<?php echo ami_e($value); ?>"
                                <?php echo (string) $selected_type === (string) $value ? 'selected' : ''; ?>
                            >
                                <?php echo ami_e($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">
                        Kelompok internal wajib berjenis Internal.
                    </small>
                </div>
            </div>

            <div class="form-group">
                <label for="rationale">Rasional</label>
                <textarea
                    class="form-control"
                    id="rationale"
                    name="rationale"
                    rows="4"
                ><?php echo ami_e(set_value(
                    'rationale',
                    $is_edit && $standard->rationale !== NULL
                        ? $standard->rationale
                        : '',
                    FALSE
                )); ?></textarea>
            </div>

            <div class="form-group">
                <label for="definitions">Definisi</label>
                <textarea
                    class="form-control"
                    id="definitions"
                    name="definitions"
                    rows="4"
                ><?php echo ami_e(set_value(
                    'definitions',
                    $is_edit && $standard->definitions !== NULL
                        ? $standard->definitions
                        : '',
                    FALSE
                )); ?></textarea>
            </div>

            <div class="d-flex justify-content-end" style="gap: 8px;">
                <a
                    class="btn btn-outline-secondary"
                    href="<?php echo site_url(
                        'spmi-versions/' . (int) $version->id . '/standards'
                    ); ?>"
                >
                    Batal
                </a>
                <button type="submit" class="btn btn-ami">
                    <i class="fas fa-save" aria-hidden="true"></i>
                    Simpan Standar
                </button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
