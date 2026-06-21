<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Daftar tugas audit</h2>
            <a class="btn-ami btn-outline-ami" href="<?php echo site_url('tugas_audit/create'); ?>">
                <i class="fas fa-plus"></i> Buat tugas
            </a>
        </div>

        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Auditee</th>
                    <th>Auditor</th>
                    <th>Standar</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($tugas)): ?>
                    <?php $no = 1; foreach ($tugas as $row): ?>
                        <?php $status_meta = status_audit_meta($row->status); ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo html_escape($row->auditee_nama); ?></td>
                            <td><?php echo html_escape($row->auditor_nama); ?></td>
                            <td><?php echo html_escape($row->nama_standar); ?></td>
                            <td>
                                <span class="ami-status <?php echo html_escape($status_meta['tone']); ?>">
                                    <?php echo html_escape($status_meta['label']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo html_escape(format_tanggal_indo($row->created_at)); ?>
                            </td>
                            <td>
                                <a href="<?php echo site_url('tugas_audit/show/'.$row->id); ?>" class="action-link edit">Detail</a>
                                <span class="text-muted mx-1">&middot;</span>
                                <?php echo form_open('tugas_audit/delete/' . (int) $row->id, ['class' => 'd-inline', 'onsubmit' => "return confirm('Apakah Anda yakin ingin menghapus tugas ini beserta seluruh jawabannya?');"]); ?>
                                    <button type="submit" class="action-link delete btn btn-link p-0 border-0 align-baseline">Hapus</button>
                                <?php echo form_close(); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada data tugas audit.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
