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
                                <?php 
                                    $date = new DateTime($row->created_at);
                                    echo $date->format('d M Y'); 
                                ?>
                            </td>
                            <td>
                                <a href="<?php echo site_url('standar/edit/'.$row->id); ?>" class="action-link edit">Edit</a>
                                <span class="text-muted mx-1">&middot;</span>
                                <?php echo form_open('standar/delete/' . (int) $row->id, ['class' => 'd-inline', 'onsubmit' => "return confirm('Standar, pertanyaan, tugas, dan jawaban terkait akan dihapus. Lanjutkan?');"]); ?>
                                    <button type="submit" class="action-link delete btn btn-link p-0 border-0 align-baseline">Hapus</button>
                                <?php echo form_close(); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada data standar audit.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
