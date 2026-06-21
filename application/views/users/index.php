<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$role_tones = [
    'super_admin' => 'tone-violet',
    'auditor' => 'tone-green',
    'auditee' => 'tone-amber'
];

$role_labels = [
    'super_admin' => 'super_admin',
    'auditor' => 'auditor',
    'auditee' => 'auditee'
];
?>



<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Daftar pengguna</h2>
            <a class="btn-ami btn-outline-ami" href="<?php echo site_url('users/create'); ?>">
                <i class="fas fa-plus"></i> Tambah pengguna
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
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($users)): ?>
                    <?php $no = 1; foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo html_escape($user->nama); ?></td>
                            <td><?php echo html_escape($user->email); ?></td>
                            <td>
                                <span class="ami-status <?php echo isset($role_tones[$user->role]) ? $role_tones[$user->role] : 'tone-blue'; ?>">
                                    <?php echo html_escape(isset($role_labels[$user->role]) ? $role_labels[$user->role] : $user->role); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                    $date = new DateTime($user->created_at);
                                    echo $date->format('d M Y'); 
                                ?>
                            </td>
                            <td>
                                <a href="<?php echo site_url('users/edit/'.$user->id); ?>" class="action-link edit">Edit</a>
                                <span class="text-muted mx-1">&middot;</span>
                                <?php echo form_open('users/delete/' . (int) $user->id, ['class' => 'd-inline', 'onsubmit' => "return confirm('Pengguna dan tugas audit terkait akan dihapus. Lanjutkan?');"]); ?>
                                    <button type="submit" class="action-link delete btn btn-link p-0 border-0 align-baseline">Hapus</button>
                                <?php echo form_close(); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada data pengguna.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!empty($users)): ?>
        <div class="mt-3 text-muted" style="font-size: 13px;">
            Menampilkan <?php echo count($users); ?> pengguna
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
