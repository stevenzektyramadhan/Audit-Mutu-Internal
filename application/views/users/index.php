<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$role_tones = ['super_admin' => 'users-tone-violet', 'admin_lpmpi' => 'users-tone-blue', 'auditor' => 'users-tone-green', 'auditee' => 'users-tone-amber'];
$role_labels = ['super_admin' => 'super_admin', 'admin_lpmpi' => 'admin_lpmpi', 'auditor' => 'auditor', 'auditee' => 'auditee'];
$filters = isset($filters) ? $filters : ['q' => '', 'role' => ''];
$actor_role = (string) $this->session->userdata('role');
$filter_roles = $actor_role === 'super_admin'
    ? ['super_admin' => 'Super Admin', 'admin_lpmpi' => 'Admin LPMPI', 'auditor' => 'Auditor', 'auditee' => 'Auditee']
    : ['auditor' => 'Auditor', 'auditee' => 'Auditee'];
$icon = static function ($name) {
    $paths = [
        'plus' => '<path d="M12 5v14M5 12h14"/>', 'filter' => '<path d="M4 5h16M7 12h10M10 19h4"/>',
        'users' => '<path d="M16 20v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 20v-1a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'edit' => '<path d="m4 16-1 4 4-1L18 8l-3-3L4 16ZM13 6l3 3M20 4l1 1"/>', 'trash' => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>', 'undo' => '<path d="M9 14 4 9l5-5M4 9h10a6 6 0 0 1 6 6v1"/>',
    ];
    return '<svg class="users-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['users']) . '</svg>';
};
?>

<div id="users-management-root" class="tw-mx-auto tw-max-w-screen-2xl">
    <section class="users-hero tw-mb-6 tw-flex tw-flex-col tw-items-start tw-justify-between tw-gap-4 tw-rounded-lg tw-border tw-p-5 sm:tw-flex-row sm:tw-items-center">
        <div><p class="users-eyebrow">Direktori akses</p><h2 class="tw-m-0">Manajemen Pengguna</h2><p class="users-muted tw-mb-0 tw-mt-1">Kelola identitas akun dan role pengguna. Penempatan unit organisasi tersedia di tab Penempatan.</p></div>
        <a class="users-button users-button-primary tw-inline-flex tw-items-center tw-gap-2" href="<?php echo site_url('users/create'); ?>"><?php echo $icon('plus'); ?>Tambah pengguna</a>
    </section>
    <section class="users-surface">
        <div class="users-surface-body">
            <form method="get" action="<?php echo site_url('users'); ?>" class="users-toolbar tw-flex tw-flex-col tw-gap-3 lg:tw-flex-row lg:tw-items-end">
                <div class="tw-flex-1"><label for="user-search" class="users-label">Cari pengguna</label><input id="user-search" type="search" name="q" class="users-control" value="<?php echo html_escape($filters['q']); ?>" placeholder="Nama atau email"></div>
                <div class="lg:tw-w-56"><label for="role-filter" class="users-label">Role</label><select id="role-filter" name="role" class="users-control"><option value="">Semua role</option><?php foreach ($filter_roles as $value => $label): ?><option value="<?php echo html_escape($value); ?>" <?php echo $filters['role'] === $value ? 'selected' : ''; ?>><?php echo html_escape($label); ?></option><?php endforeach; ?></select></div>
                <button type="submit" class="users-button users-button-primary tw-inline-flex tw-items-center tw-justify-center tw-gap-2"><?php echo $icon('filter'); ?>Terapkan</button>
                <?php if ($filters['q'] !== '' || $filters['role'] !== ''): ?><a href="<?php echo site_url('users'); ?>" class="users-button users-button-secondary tw-inline-flex tw-items-center tw-justify-center">Reset</a><?php endif; ?>
            </form>
            <div class="users-table-wrap">
                <table class="users-table"><thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Dibuat</th><th>Aksi</th></tr></thead><tbody>
                <?php if (!empty($users)): foreach ($users as $user): ?><tr><td data-label="Nama"><strong><?php echo html_escape($user->nama); ?></strong></td><td data-label="Email" class="users-muted"><?php echo html_escape($user->email); ?></td><td data-label="Role"><span class="users-badge <?php echo isset($role_tones[$user->role]) ? $role_tones[$user->role] : 'users-tone-blue'; ?>"><?php echo html_escape(isset($role_labels[$user->role]) ? $role_labels[$user->role] : $user->role); ?></span></td><td data-label="Dibuat" class="users-muted"><?php echo html_escape(format_tanggal_indo($user->created_at)); ?></td><td data-label="Aksi"><div class="tw-flex tw-flex-wrap tw-gap-2"><a href="<?php echo site_url('users/edit/'.$user->id); ?>" class="users-action" title="Edit pengguna"><?php echo $icon('edit'); ?><span>Edit</span></a><?php if ($user->role !== 'super_admin'): ?><?php echo form_open('users/delete/' . (int) $user->id, ['class' => 'tw-inline', 'onsubmit' => "return confirm('Pengguna tidak dapat dihapus jika masih memiliki data terkait. Lanjutkan?');"]); ?><button type="submit" class="users-action users-action-danger" title="Hapus pengguna"><?php echo $icon('trash'); ?><span>Hapus</span></button><?php echo form_close(); ?><?php endif; ?></div></td></tr><?php endforeach; else: ?>
                    <tr><td colspan="5"><div class="users-empty"><div class="users-empty-icon"><?php echo $icon('users'); ?></div><strong>Pengguna tidak ditemukan</strong><p><?php echo $filters['q'] !== '' || $filters['role'] !== '' ? 'Coba ubah kata kunci atau filter role.' : 'Tambahkan akun auditor atau auditee untuk memulai.'; ?></p><?php if ($filters['q'] !== '' || $filters['role'] !== ''): ?><a href="<?php echo site_url('users'); ?>" class="users-button users-button-secondary tw-inline-flex tw-items-center tw-gap-2"><?php echo $icon('undo'); ?>Reset filter</a><?php else: ?><a href="<?php echo site_url('users/create'); ?>" class="users-button users-button-primary tw-inline-flex tw-items-center tw-gap-2"><?php echo $icon('plus'); ?>Tambah pengguna</a><?php endif; ?><a href="<?php echo site_url('lpmpi/organization?tab=assignments'); ?>" class="users-button users-button-secondary tw-inline-flex tw-items-center tw-gap-2">Buka tab Penempatan</a></div></td></tr>
                <?php endif; ?></tbody></table>
            </div>
            <?php if (!empty($users)): ?><p class="users-muted tw-mb-0 tw-mt-4">Menampilkan <?php echo count($users); ?> pengguna</p><?php endif; ?>
        </div>
    </section>
</div>
<?php include APPPATH . 'views/layouts/footer.php'; ?>
