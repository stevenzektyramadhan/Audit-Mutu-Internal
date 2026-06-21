<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$status_tones = [
    'dinilai' => 'tone-green',
    'diisi' => 'tone-blue',
    'belum_diisi' => 'tone-amber'
];

$status_labels = [
    'dinilai' => 'Dinilai',
    'diisi' => 'Diisi',
    'belum_diisi' => 'Belum diisi'
];
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
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo html_escape($row->auditee_nama); ?></td>
                            <td><?php echo html_escape($row->auditor_nama); ?></td>
                            <td><?php echo html_escape($row->nama_standar); ?></td>
                            <td>
                                <span class="ami-status <?php echo isset($status_tones[$row->status]) ? $status_tones[$row->status] : 'tone-blue'; ?>">
                                    <?php echo html_escape(isset($status_labels[$row->status]) ? $status_labels[$row->status] : $row->status); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                    $date = new DateTime($row->created_at);
                                    echo $date->format('d M Y'); 
                                ?>
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
