<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel mb-3">
    <div class="ami-panel-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap: 12px;">
            <div>
                <div class="text-muted mb-1">
                    <?php echo ami_e($version->organization_unit_name); ?>
                </div>
                <h2 class="ami-section-title mb-1">
                    <?php echo ami_e($version->title); ?>
                </h2>
                <code><?php echo ami_e($version->document_code); ?></code>
                <span class="mx-2 text-muted">/</span>
                Revisi <strong><?php echo ami_e($version->revision_number); ?></strong>
                <span class="badge badge-secondary ml-2">
                    <?php echo ami_e(strtoupper($version->status)); ?>
                </span>
            </div>
            <div class="d-flex flex-wrap" style="gap: 8px;">
                <a
                    class="btn btn-outline-secondary"
                    href="<?php echo site_url('spmi-versions/show/' . (int) $version->id); ?>"
                >
                    Kembali ke Versi
                </a>
                <?php if ($is_draft): ?>
                    <a
                        class="btn btn-ami"
                        href="<?php echo site_url('spmi-versions/' . (int) $version->id . '/standards/create'); ?>"
                    >
                        <i class="fas fa-plus" aria-hidden="true"></i> Tambah Standar
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$is_draft): ?>
            <div class="alert alert-info mt-3 mb-0">
                Master standar pada versi non-draft bersifat read-only.
                Clone versi untuk membuat perubahan tanpa mengubah histori.
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row mb-3">
    <?php foreach ($group_labels as $group_key => $group_label): ?>
        <div class="col-md-6 col-xl-3 mb-2">
            <div class="ami-panel h-100">
                <div class="ami-panel-body">
                    <div class="ami-stat-label"><?php echo ami_e($group_label); ?></div>
                    <div class="ami-stat-value">
                        <?php echo isset($group_counts[$group_key])
                            ? (int) $group_counts[$group_key]
                            : 0; ?>
                    </div>
                    <div class="small text-muted">standar aktif</div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap: 10px;">
            <div>
                <h2 class="ami-section-title">Daftar Standar</h2>
                <div class="small text-muted mt-1">
                    <?php echo count($standards); ?> standar tersimpan.
                    Kode harus unik di dalam versi ini.
                </div>
            </div>
            <?php if ($is_draft && !empty($standards)): ?>
                <?php echo form_open(
                    'spmi-versions/' . (int) $version->id . '/standards/reorder',
                    ['id' => 'standard-reorder-form', 'class' => 'm-0']
                ); ?>
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-sort-numeric-down" aria-hidden="true"></i>
                        Simpan Urutan
                    </button>
                <?php echo form_close(); ?>
            <?php endif; ?>
        </div>

        <?php if (empty($standards)): ?>
            <div class="ami-empty">
                <div class="ami-empty-icon">
                    <i class="fas fa-layer-group" aria-hidden="true"></i>
                </div>
                <div class="ami-empty-title">Belum ada standar pada versi ini</div>
                <?php if ($is_draft): ?>
                    <p class="mb-2">
                        Muat struktur awal 21 standar: 8 pendidikan, 3 penelitian,
                        3 pengabdian, dan 7 standar internal.
                    </p>
                    <?php echo form_open(
                        'spmi-versions/' . (int) $version->id . '/standards/seed',
                        ['class' => 'd-inline']
                    ); ?>
                        <button type="submit" class="btn btn-ami">
                            Muat 21 Standar Awal
                        </button>
                    <?php echo form_close(); ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table ami-table">
                    <thead>
                        <tr>
                            <th style="width: 86px;">Urutan</th>
                            <th>Kode / Nama</th>
                            <th>Kelompok</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th style="width: 190px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($standards as $standard): ?>
                            <tr>
                                <td>
                                    <?php if ($is_draft): ?>
                                        <input
                                            class="form-control form-control-sm"
                                            type="number"
                                            min="1"
                                            max="100000"
                                            name="orders[<?php echo (int) $standard->id; ?>]"
                                            value="<?php echo (int) $standard->sort_order; ?>"
                                            form="standard-reorder-form"
                                            required
                                        >
                                    <?php else: ?>
                                        <?php echo (int) $standard->sort_order; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code><?php echo ami_e($standard->code); ?></code>
                                    <div class="font-weight-bold mt-1">
                                        <?php echo ami_e($standard->name); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php echo ami_e(
                                        isset($group_labels[$standard->group_type])
                                            ? $group_labels[$standard->group_type]
                                            : $standard->group_type
                                    ); ?>
                                </td>
                                <td>
                                    <?php echo ami_e(
                                        isset($type_labels[$standard->standard_type])
                                            ? $type_labels[$standard->standard_type]
                                            : $standard->standard_type
                                    ); ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo (int) $standard->active === 1 ? 'badge-success' : 'badge-secondary'; ?>">
                                        <?php echo (int) $standard->active === 1 ? 'Aktif' : 'Nonaktif'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($is_draft): ?>
                                        <div class="ami-row-actions">
                                            <a
                                                class="ami-action-btn"
                                                href="<?php echo site_url(
                                                    'spmi-versions/' . (int) $version->id
                                                    . '/standards/edit/' . (int) $standard->id
                                                ); ?>"
                                            >
                                                <i class="fas fa-edit" aria-hidden="true"></i>
                                                Edit
                                            </a>
                                            <?php echo form_open(
                                                'spmi-versions/' . (int) $version->id
                                                    . '/standards/toggle-active/'
                                                    . (int) $standard->id,
                                                ['class' => 'd-inline']
                                            ); ?>
                                                <button type="submit" class="ami-action-btn">
                                                    <?php echo (int) $standard->active === 1
                                                        ? 'Nonaktifkan'
                                                        : 'Aktifkan'; ?>
                                                </button>
                                            <?php echo form_close(); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Read-only</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
