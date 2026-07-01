<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Penugasan Auditor</h2>
            <a class="btn-ami btn-outline-ami" href="#">
                <i class="fas fa-plus"></i> Buat Tugas Baru
            </a>
        </div>

        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Periode</th>
                    <th>Standar</th>
                    <th>Auditor</th>
                    <th>Auditee</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($tugas_list)): ?>
                    <?php $no = 1; ?>
                    <?php foreach ($tugas_list as $row): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo html_escape($row->nama_periode ?? '-'); ?></td>
                            <td><?php echo html_escape($row->nama_standar ?? '-'); ?></td>
                            <td><?php echo html_escape($row->auditor_nama ?? '-'); ?></td>
                            <td><?php echo html_escape($row->auditee_nama ?? '-'); ?></td>
                            <td><?php echo html_escape($row->status ?? '-'); ?></td>
                            <td>
                                <div class="ami-row-actions">
                                    <a href="#" class="ami-action-btn" title="Detail"><i class="fas fa-eye"></i></a>
                                    <a href="#" class="ami-action-btn danger" title="Hapus" onclick="return confirm('Yakin?')"><i class="fas fa-trash-alt"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7"><div class="ami-empty">
                            <div class="ami-empty-icon"><i class="fas fa-clipboard-list" aria-hidden="true"></i></div>
                            <div class="ami-empty-title">Belum ada penugasan</div>
                            <div>Buat penugasan untuk menugaskan auditor ke auditee.</div>
                        </div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>