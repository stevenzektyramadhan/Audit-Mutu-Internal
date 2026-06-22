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
$filters = isset($filters) ? $filters : ['q' => '', 'role' => ''];
?>



<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Daftar pengguna</h2>
            <a class="btn-ami btn-outline-ami" href="<?php echo site_url('users/create'); ?>">
                <i class="fas fa-plus"></i> Tambah pengguna
            </a>
        </div>

        <form method="get" action="<?php echo site_url('users'); ?>" class="ami-filter-bar">
            <div class="ami-filter-grow">
                <label for="user-search" class="ami-stat-label">Cari pengguna</label>
                <input id="user-search" type="search" name="q" class="form-control" value="<?php echo html_escape($filters['q']); ?>" placeholder="Nama atau email">
            </div>
            <div class="ami-filter-select">
                <label for="role-filter" class="ami-stat-label">Role</label>
                <select id="role-filter" name="role" class="form-control">
                    <option value="">Semua role</option>
                    <option value="super_admin" <?php echo $filters['role'] === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                    <option value="auditor" <?php echo $filters['role'] === 'auditor' ? 'selected' : ''; ?>>Auditor</option>
                    <option value="auditee" <?php echo $filters['role'] === 'auditee' ? 'selected' : ''; ?>>Auditee</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-ami"><i class="fas fa-filter" aria-hidden="true"></i>Terapkan</button>
            <?php if ($filters['q'] !== '' || $filters['role'] !== ''): ?>
                <a href="<?php echo site_url('users'); ?>" class="btn btn-outline-ami btn-ami">Reset</a>
            <?php endif; ?>
        </form>

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
                                <?php echo html_escape(format_tanggal_indo($user->created_at)); ?>
                            </td>
                            <td>
                                <div class="ami-row-actions">
                                    <a href="<?php echo site_url('users/edit/'.$user->id); ?>" class="ami-action-btn" title="Edit pengguna">
                                        <i class="fas fa-edit" aria-hidden="true"></i><span>Edit</span>
                                    </a>
                                    <?php echo form_open('users/delete/' . (int) $user->id, ['class' => 'd-inline', 'onsubmit' => "return confirm('Pengguna dan tugas audit terkait akan dihapus. Lanjutkan?');"]); ?>
                                        <button type="submit" class="ami-action-btn danger" title="Hapus pengguna">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i><span>Hapus</span>
                                        </button>
                                    <?php echo form_close(); ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="ami-empty">
                                <div class="ami-empty-icon"><i class="fas fa-users" aria-hidden="true"></i></div>
                                <div class="ami-empty-title">Pengguna tidak ditemukan</div>
                                <div><?php echo $filters['q'] !== '' || $filters['role'] !== '' ? 'Coba ubah kata kunci atau filter role.' : 'Tambahkan akun auditor atau auditee untuk memulai.'; ?></div>
                                <?php if ($filters['q'] !== '' || $filters['role'] !== ''): ?>
                                    <a href="<?php echo site_url('users'); ?>" class="btn btn-outline-ami btn-ami"><i class="fas fa-undo" aria-hidden="true"></i>Reset filter</a>
                                <?php else: ?>
                                    <a href="<?php echo site_url('users/create'); ?>" class="btn btn-primary btn-ami"><i class="fas fa-plus" aria-hidden="true"></i>Tambah pengguna</a>
                                <?php endif; ?>
                            </div>
                        </td>
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
