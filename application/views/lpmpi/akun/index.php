<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Daftar Akun Auditee & Auditor</h2>
            <a class="btn-ami btn-outline-ami" href="#">
                <i class="fas fa-plus"></i> Tambah Akun
            </a>
        </div>

        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Unit</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($auditee_list) || !empty($auditor_list)): ?>
                    <?php $no = 1; ?>
                    <?php if (!empty($auditor_list)): ?>
                        <?php foreach ($auditor_list as $row): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo html_escape($row->nama); ?></td>
                                <td><?php echo html_escape($row->email); ?></td>
                                <td><span class="status-badge status-aktif">Auditor</span></td>
                                <td><?php echo html_escape($row->nama_unit ?? '-'); ?></td>
                                <td>
                                    <div class="ami-row-actions">
                                        <a href="#" class="ami-action-btn" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="#" class="ami-action-btn danger" title="Hapus" onclick="return confirm('Yakin?')"><i class="fas fa-trash-alt"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!empty($auditee_list)): ?>
                        <?php foreach ($auditee_list as $row): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo html_escape($row->nama); ?></td>
                                <td><?php echo html_escape($row->email); ?></td>
                                <td><span class="status-badge status-berlangsung">Auditee</span></td>
                                <td><?php echo html_escape($row->nama_unit ?? '-'); ?></td>
                                <td>
                                    <div class="ami-row-actions">
                                        <a href="#" class="ami-action-btn" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="#" class="ami-action-btn danger" title="Hapus" onclick="return confirm('Yakin?')"><i class="fas fa-trash-alt"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6"><div class="ami-empty">
                            <div class="ami-empty-icon"><i class="fas fa-users" aria-hidden="true"></i></div>
                            <div class="ami-empty-title">Belum ada akun</div>
                            <div>Tambahkan akun auditee atau auditor terlebih dahulu.</div>
                        </div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>