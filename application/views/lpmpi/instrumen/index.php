<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
            <h2 class="ami-section-title tw-m-0">Instrumen Standar</h2>
        </div>

        <div class="tw-overflow-x-auto">
            <table class="ami-table">
                <thead>
                <tr>
                    <th>#</th>
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
                            <td><?php echo html_escape($row->nama_standar); ?></td>
                            <td>
                                <?php if (!empty($row->file_instrumen)): ?>
                                    <a href="<?php echo site_url('lpmpi/instrumen/download/' . (int) $row->id); ?>" class="btn-ami tw-text-xs btn-outline-ami">
                                        <i class="fas fa-download" aria-hidden="true"></i> Download
                                    </a>
                                    <div class="tw-text-slate-500 tw-mt-2 tw-text-xs"><?php echo html_escape($row->file_instrumen); ?></div>
                                <?php else: ?>
                                    <span class="tw-text-slate-500">Belum ada file</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="ami-row-actions tw-items-center">
                                    <?php echo form_open_multipart('lpmpi/instrumen/upload/' . (int) $row->id, ['class' => 'tw-flex tw-items-center tw-gap-2']); ?>
                                        <input type="file" name="file_instrumen" accept=".pdf,.doc,.docx" class="ami-form-control" required>
                                        <button type="submit" class="ami-action-btn" title="Upload file instrumen">
                                            <i class="fas fa-upload" aria-hidden="true"></i><span>Upload</span>
                                        </button>
                                    <?php echo form_close(); ?>

                                    <?php if (!empty($row->file_instrumen)): ?>
                                        <?php echo form_open('lpmpi/instrumen/delete/' . (int) $row->id, ['class' => 'tw-inline', 'onsubmit' => "return confirm('Hapus file instrumen untuk standar ini?');"]); ?>
                                            <button type="submit" class="ami-action-btn danger" title="Hapus file instrumen">
                                                <i class="fas fa-trash-alt" aria-hidden="true"></i><span>Hapus</span>
                                            </button>
                                        <?php echo form_close(); ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4"><div class="ami-empty">
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
