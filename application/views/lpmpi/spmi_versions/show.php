<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$badge_classes = [
    'draft' => 'badge-secondary',
    'review' => 'badge-warning',
    'approved' => 'badge-info',
    'active' => 'badge-success',
    'retired' => 'badge-dark',
];
$status_label = isset($status_labels[$version->status])
    ? $status_labels[$version->status]
    : $version->status;

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start mb-4" style="gap: 12px;">
            <div>
                <div class="text-muted mb-1"><?php echo ami_e($version->organization_unit_name); ?></div>
                <h2 class="ami-section-title mb-1"><?php echo ami_e($version->title); ?></h2>
                <code><?php echo ami_e($version->document_code); ?></code>
                <span class="mx-2 text-muted">/</span>
                Revisi <strong><?php echo ami_e($version->revision_number); ?></strong>
            </div>
            <span class="badge <?php echo isset($badge_classes[$version->status]) ? $badge_classes[$version->status] : 'badge-secondary'; ?> p-2">
                <?php echo ami_e($status_label); ?>
            </span>
        </div>

        <?php if ((string) $version->status === 'active'): ?>
            <div class="alert alert-success">
                <strong>Versi aktif bersifat read-only.</strong>
                Gunakan clone untuk membuat revisi baru tanpa mengubah atau menghapus histori ini.
            </div>
        <?php elseif ((string) $version->status === 'review' && (int) $version->created_by === (int) $current_user_id): ?>
            <div class="alert alert-warning">
                Separation of duties aktif: pembuat versi tidak dapat menyetujui versinya sendiri.
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-7">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Tanggal efektif</dt>
                    <dd class="col-sm-7"><?php echo ami_e($version->effective_date); ?></dd>
                    <dt class="col-sm-5">Tanggal berakhir</dt>
                    <dd class="col-sm-7"><?php echo $version->expires_at !== NULL ? ami_e($version->expires_at) : '—'; ?></dd>
                    <dt class="col-sm-5">Pembuat</dt>
                    <dd class="col-sm-7"><?php echo ami_e($version->creator_name); ?></dd>
                    <dt class="col-sm-5">Approver</dt>
                    <dd class="col-sm-7">
                        <?php echo $version->approver_name !== NULL
                            ? ami_e($version->approver_name)
                            : 'Belum ada'; ?>
                    </dd>
                    <dt class="col-sm-5">Disetujui pada</dt>
                    <dd class="col-sm-7"><?php echo $version->approved_at !== NULL ? ami_e($version->approved_at) : '—'; ?></dd>
                </dl>
            </div>
            <div class="col-lg-5">
                <div class="border rounded p-3">
                    <div class="font-weight-bold mb-2">PDF Sumber</div>
                    <div class="text-break mb-2"><?php echo ami_e($version->source_file_original_name); ?></div>
                    <div class="small text-muted text-break mb-3">
                        SHA-256: <?php echo ami_e($version->source_file_sha256); ?>
                    </div>
                    <a
                        class="btn btn-sm btn-outline-primary"
                        href="<?php echo site_url('spmi-versions/download/' . (int) $version->id); ?>"
                    >
                        <i class="fas fa-download" aria-hidden="true"></i> Unduh PDF
                    </a>
                </div>
            </div>
        </div>

        <hr>

        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
            <a
                href="<?php echo site_url('spmi-versions?unit_id=' . (int) $version->organization_unit_id); ?>"
                class="btn btn-outline-secondary"
            >
                Kembali
            </a>

            <?php if ($can_manage_standards): ?>
                <a
                    href="<?php echo site_url('spmi-versions/' . (int) $version->id . '/standards'); ?>"
                    class="btn btn-outline-primary"
                >
                    <i class="fas fa-layer-group" aria-hidden="true"></i>
                    Kelola Standar (<?php echo (int) $standard_count; ?>)
                </a>
            <?php endif; ?>

            <?php if ((string) $version->status === 'draft'): ?>
                <a
                    href="<?php echo site_url('spmi-versions/edit/' . (int) $version->id); ?>"
                    class="btn btn-outline-primary"
                >
                    <i class="fas fa-edit" aria-hidden="true"></i> Edit Draft
                </a>
                <?php echo form_open('spmi-versions/submit-review/' . (int) $version->id, ['class' => 'd-inline']); ?>
                    <button type="submit" class="btn btn-ami">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i> Kirim ke Review
                    </button>
                <?php echo form_close(); ?>
            <?php elseif ((string) $version->status === 'review'): ?>
                <?php echo form_open('spmi-versions/approve/' . (int) $version->id, ['class' => 'd-inline']); ?>
                    <button
                        type="submit"
                        class="btn btn-ami"
                        <?php echo (int) $version->created_by === (int) $current_user_id ? 'disabled title="Pembuat tidak boleh menyetujui sendiri"' : ''; ?>
                    >
                        <i class="fas fa-check" aria-hidden="true"></i> Setujui
                    </button>
                <?php echo form_close(); ?>
            <?php elseif ((string) $version->status === 'approved'): ?>
                <?php echo form_open('spmi-versions/activate/' . (int) $version->id, ['class' => 'd-inline']); ?>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-bolt" aria-hidden="true"></i> Aktifkan
                    </button>
                <?php echo form_close(); ?>
                <a
                    href="<?php echo site_url('spmi-versions/clone/' . (int) $version->id); ?>"
                    class="btn btn-outline-primary"
                >
                    <i class="fas fa-copy" aria-hidden="true"></i> Clone ke Draft
                </a>
            <?php elseif ((string) $version->status === 'active'): ?>
                <a
                    href="<?php echo site_url('spmi-versions/clone/' . (int) $version->id); ?>"
                    class="btn btn-outline-primary"
                >
                    <i class="fas fa-copy" aria-hidden="true"></i> Clone ke Draft
                </a>
                <?php echo form_open('spmi-versions/retire/' . (int) $version->id, ['class' => 'd-inline']); ?>
                    <button type="submit" class="btn btn-outline-warning">
                        <i class="fas fa-archive" aria-hidden="true"></i> Arsipkan
                    </button>
                <?php echo form_close(); ?>
            <?php elseif ((string) $version->status === 'retired'): ?>
                <a
                    href="<?php echo site_url('spmi-versions/clone/' . (int) $version->id); ?>"
                    class="btn btn-outline-primary"
                >
                    <i class="fas fa-copy" aria-hidden="true"></i> Clone ke Draft
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
