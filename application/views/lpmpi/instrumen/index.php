<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Instrumen Standar</h2>
        </div>

        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Kode Standar</th>
                    <th>Nama Standar</th>
                    <th>File Instrumen</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($standar_list)): ?>
                    <?php $no = 1; ?>
                    <?php foreach ($standar_list as $row): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo html_escape($row->kode_standar ?? '-'); ?></td>
                            <td><?php echo html_escape($row->nama_standar); ?></td>
                            <td>
                                <?php if (!empty($row->file_instrumen)): ?>
                                    <a href="<?php echo base_url('uploads/instrumen/' . html_escape($row->file_instrumen)); ?>" target="_blank" class="btn-ami btn-sm btn-outline-ami">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Belum ada file</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="ami-row-actions">
                                    <a href="#" class="ami-action-btn" title="Upload file"><i class="fas fa-upload"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5"><div class="ami-empty">
                            <div class="ami-empty-icon"><i class="fas fa-file-upload" aria-hidden="true"></i></div>
                            <div class="ami-empty-title">Belum ada standar</div>
                            <div>Buat standar terlebih dahulu untuk mengupload instrumen.</div>
                        </div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>