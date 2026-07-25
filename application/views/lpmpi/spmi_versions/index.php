<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$badge_classes = [
    'draft' => 'badge-secondary',
    'review' => 'badge-warning',
    'approved' => 'badge-info',
    'active' => 'badge-success',
    'retired' => 'badge-dark',
];

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4" style="gap: 12px;">
            <div>
                <h2 class="ami-section-title m-0">Workflow Versi SPMI</h2>
                <div class="text-muted mt-1">
                    Draft → review → disetujui → aktif → diarsipkan.
                </div>
            </div>
            <?php if ($selected_unit): ?>
                <a
                    class="btn btn-ami"
                    href="<?php echo site_url('spmi-versions/create?unit_id=' . (int) $selected_unit->id); ?>"
                >
                    <i class="fas fa-plus" aria-hidden="true"></i> Buat draft
                </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($units)): ?>
            <?php echo form_open('spmi-versions', ['method' => 'get', 'class' => 'mb-4']); ?>
                <div class="form-row align-items-end">
                    <div class="form-group col-md-8 mb-md-0">
                        <label for="unit_id">Scope Unit Organisasi</label>
                        <select class="form-control" id="unit_id" name="unit_id">
                            <?php foreach ($units as $unit): ?>
                                <option
                                    value="<?php echo (int) $unit->id; ?>"
                                    <?php echo $selected_unit && (int) $selected_unit->id === (int) $unit->id ? 'selected' : ''; ?>
                                >
                                    <?php echo ami_e($unit->name . ' (' . $unit->code . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4 mb-0">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-filter" aria-hidden="true"></i> Tampilkan
                        </button>
                    </div>
                </div>
            <?php echo form_close(); ?>
        <?php endif; ?>

        <div class="alert alert-info">
            Versi aktif bersifat read-only. Revisi selalu dibuat sebagai draft baru melalui fitur clone.
            Aktivasi versi baru tidak menghapus versi sebelumnya.
        </div>

        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>Dokumen</th>
                    <th>Revisi</th>
                    <th>Tanggal Efektif</th>
                    <th>Status</th>
                    <th>Pembuat / Approver</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($versions)): ?>
                    <?php foreach ($versions as $version): ?>
                        <tr>
                            <td>
                                <strong><?php echo ami_e($version->title); ?></strong>
                                <div><code><?php echo ami_e($version->document_code); ?></code></div>
                            </td>
                            <td><?php echo ami_e($version->revision_number); ?></td>
                            <td>
                                <?php echo ami_e($version->effective_date); ?>
                                <?php if ($version->expires_at !== NULL): ?>
                                    <div class="small text-muted">
                                        s.d. <?php echo ami_e($version->expires_at); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo isset($badge_classes[$version->status]) ? $badge_classes[$version->status] : 'badge-secondary'; ?>">
                                    <?php echo ami_e(isset($status_labels[$version->status]) ? $status_labels[$version->status] : $version->status); ?>
                                </span>
                            </td>
                            <td>
                                <div><?php echo ami_e($version->creator_name); ?></div>
                                <div class="small text-muted">
                                    <?php echo $version->approver_name !== NULL
                                        ? 'Approver: ' . ami_e($version->approver_name)
                                        : 'Belum disetujui'; ?>
                                </div>
                            </td>
                            <td>
                                <a
                                    class="btn btn-sm btn-outline-primary"
                                    href="<?php echo site_url('spmi-versions/show/' . (int) $version->id); ?>"
                                >
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="ami-empty">
                                <div class="ami-empty-icon">
                                    <i class="fas fa-file-contract" aria-hidden="true"></i>
                                </div>
                                <div class="ami-empty-title">Belum ada versi SPMI</div>
                                <div>
                                    <?php echo empty($units)
                                        ? 'Tidak ada unit organisasi aktif dalam scope Anda.'
                                        : 'Buat draft pertama untuk unit yang dipilih.'; ?>
                                </div>
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
