<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$tones = ['tone-violet', 'tone-green', 'tone-amber', 'tone-blue', 'tone-rose', 'tone-teal'];
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Daftar pertanyaan audit</h2>
            <a class="btn-ami btn-outline-ami" href="<?php echo site_url('pertanyaan/create'); ?>">
                <i class="fas fa-plus"></i> Tambah pertanyaan
            </a>
        </div>

        <div class="mb-3" style="width: 250px;">
            <select class="form-control bg-dark text-light border-secondary" style="border-radius: 7px; height: 42px; font-size: 14px;">
                <option value="">Semua standar</option>
                <?php if (!empty($standar)): ?>
                    <?php foreach ($standar as $std): ?>
                        <option value="<?php echo $std->id; ?>"><?php echo html_escape($std->nama_standar); ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Standar</th>
                    <th>Isi pertanyaan</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($pertanyaan)): ?>
                    <?php $no = 1; foreach ($pertanyaan as $row): ?>
                        <?php 
                            $standar_badge = str_replace('Standar ', '', $row->nama_standar ?? '');
                            $tone_class = $tones[($row->standar_id ?? 0) % count($tones)];
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td>
                                <?php if (!empty($row->nama_standar)): ?>
                                    <span class="ami-status <?php echo $tone_class; ?>">
                                        <?php echo html_escape($standar_badge); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width: 350px;">
                                <?php echo html_escape($row->isi_pertanyaan); ?>
                            </td>
                            <td>
                                <?php 
                                    $date = new DateTime($row->created_at);
                                    echo $date->format('d M Y'); 
                                ?>
                            </td>
                            <td>
                                <a href="<?php echo site_url('pertanyaan/edit/'.$row->id); ?>" class="action-link edit">Edit</a>
                                <span class="text-muted mx-1">&middot;</span>
                                <?php echo form_open('pertanyaan/delete/' . (int) $row->id, ['class' => 'd-inline', 'onsubmit' => "return confirm('Apakah Anda yakin ingin menghapus pertanyaan ini?');"]); ?>
                                    <button type="submit" class="action-link delete btn btn-link p-0 border-0 align-baseline">Hapus</button>
                                <?php echo form_close(); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data pertanyaan audit.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!empty($pertanyaan)): ?>
        <div class="mt-3 text-muted" style="font-size: 13px;">
            Menampilkan <?php echo count($pertanyaan); ?> pertanyaan
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
