<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap: 12px;">
            <div>
                <h2 class="ami-section-title m-0">Hierarki Unit Organisasi</h2>
                <div class="text-muted mt-1">
                    Unit dinonaktifkan tanpa dihapus agar referensi dan histori tetap utuh.
                </div>
            </div>
            <a class="btn btn-ami" href="<?php echo site_url('organization-units/create'); ?>">
                <i class="fas fa-plus" aria-hidden="true"></i> Tambah unit
            </a>
        </div>

        <div class="alert alert-info mb-4">
            Program studi wajib berada langsung di bawah fakultas/UPPS. Unit induk harus
            diaktifkan sebelum unit turunannya.
        </div>

        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>Unit</th>
                    <th>Kode</th>
                    <th>Jenis</th>
                    <th>Induk</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($units)): ?>
                    <?php foreach ($units as $row): ?>
                        <tr>
                            <td>
                                <div style="padding-left: <?php echo (int) $row->tree_depth * 24; ?>px;">
                                    <?php if ((int) $row->tree_depth > 0): ?>
                                        <span class="text-muted" aria-hidden="true">└─</span>
                                    <?php endif; ?>
                                    <strong><?php echo ami_e($row->name); ?></strong>
                                    <?php if (!empty($row->tree_orphaned)): ?>
                                        <span class="badge badge-danger ml-2">Induk hilang</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><code><?php echo ami_e($row->code); ?></code></td>
                            <td>
                                <?php echo ami_e(isset($type_labels[$row->type]) ? $type_labels[$row->type] : $row->type); ?>
                            </td>
                            <td>
                                <?php echo $row->parent_name !== NULL
                                    ? ami_e($row->parent_name . ' (' . $row->parent_code . ')')
                                    : '<span class="text-muted">Root</span>'; ?>
                            </td>
                            <td>
                                <?php if ((int) $row->active === 1): ?>
                                    <span class="status-badge status-aktif">
                                        <i class="fas fa-check-circle" aria-hidden="true"></i> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-nonaktif">
                                        <i class="fas fa-circle" aria-hidden="true"></i> Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="ami-row-actions">
                                    <a
                                        href="<?php echo site_url('organization-units/edit/' . (int) $row->id); ?>"
                                        class="ami-action-btn"
                                        title="Edit unit"
                                    >
                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                    </a>
                                    <?php echo form_open(
                                        'organization-units/toggle-active/' . (int) $row->id,
                                        ['class' => 'd-inline']
                                    ); ?>
                                        <button
                                            type="submit"
                                            class="ami-action-btn <?php echo (int) $row->active === 1 ? 'warning' : 'success'; ?>"
                                            title="<?php echo (int) $row->active === 1 ? 'Nonaktifkan unit' : 'Aktifkan unit'; ?>"
                                        >
                                            <i class="fas <?php echo (int) $row->active === 1 ? 'fa-toggle-on' : 'fa-toggle-off'; ?>" aria-hidden="true"></i>
                                        </button>
                                    <?php echo form_close(); ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="ami-empty">
                                <div class="ami-empty-icon">
                                    <i class="fas fa-sitemap" aria-hidden="true"></i>
                                </div>
                                <div class="ami-empty-title">Belum ada unit organisasi</div>
                                <div>Jalankan seed root universitas atau buat unit universitas terlebih dahulu.</div>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
