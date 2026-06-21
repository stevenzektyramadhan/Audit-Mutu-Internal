<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Daftar standar audit</h2>
            <a class="btn-ami btn-outline-ami" href="<?php echo site_url('standar/create'); ?>">
                <i class="fas fa-plus"></i> Tambah standar
            </a>
        </div>

        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nama standar</th>
                    <th>Deskripsi</th>
                    <th>Jml pertanyaan</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($standar)): ?>
                    <?php $no = 1; foreach ($standar as $row): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo html_escape($row->nama_standar); ?></td>
                            <td>
                                <?php if (!empty($row->deskripsi)): ?>
                                    <?php echo html_escape($row->deskripsi); ?>
                                <?php else: ?>
                                    <span class="text-muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="text-primary font-weight-bold"><?php echo (int) $row->total_pertanyaan; ?></span>
                            </td>
                            <td>
                                <?php echo html_escape(format_tanggal_indo($row->created_at)); ?>
                            </td>
                            <td>
                                <div class="ami-row-actions">
                                    <a href="<?php echo site_url('standar/edit/'.$row->id); ?>" class="ami-action-btn" title="Edit standar">
                                        <i class="fas fa-edit" aria-hidden="true"></i><span>Edit</span>
                                    </a>
                                    <?php echo form_open('standar/delete/' . (int) $row->id, ['class' => 'd-inline', 'onsubmit' => "return confirm('Standar, pertanyaan, tugas, dan jawaban terkait akan dihapus. Lanjutkan?');"]); ?>
                                        <button type="submit" class="ami-action-btn danger" title="Hapus standar">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i><span>Hapus</span>
                                        </button>
                                    <?php echo form_close(); ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6"><div class="ami-empty">
                            <div class="ami-empty-icon"><i class="fas fa-book" aria-hidden="true"></i></div>
                            <div class="ami-empty-title">Belum ada standar audit</div>
                            <div>Buat standar terlebih dahulu sebelum menambahkan pertanyaan.</div>
                            <a href="<?php echo site_url('standar/create'); ?>" class="btn btn-primary btn-ami"><i class="fas fa-plus" aria-hidden="true"></i>Tambah standar</a>
                        </div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
