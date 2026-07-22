<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$name_parts = preg_split('/\s+/', trim((string) $account->nama));
$initials = '';
foreach (array_slice($name_parts, 0, 2) as $part) {
    if ($part !== '') {
        $initials .= strtoupper(substr($part, 0, 1));
    }
}
if ($initials === '') {
    $initials = 'A';
}
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel ami-account-panel">
    <div class="ami-panel-body">
        <div class="ami-account-heading">
            <?php if (!empty($account->profile_photo_path)): ?>
                <img src="<?php echo site_url('account/photo'); ?>" alt="Foto profil <?php echo html_escape($account->nama); ?>" class="ami-account-photo">
            <?php else: ?>
                <div class="ami-account-photo ami-account-initials" aria-label="Inisial <?php echo html_escape($initials); ?>"><?php echo html_escape($initials); ?></div>
            <?php endif; ?>
            <div>
                <h2 class="ami-section-title mb-1">Profil akun</h2>
                <p class="text-muted mb-0">Foto hanya terlihat melalui sesi akun Anda.</p>
            </div>
        </div>

        <?php if (validation_errors()): ?>
            <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
        <?php endif; ?>

        <?php echo form_open_multipart('account/update'); ?>
            <div class="form-group">
                <label for="nama">Nama lengkap</label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo html_escape(set_value('nama', $account->nama)); ?>" required maxlength="100" autocomplete="name">
            </div>
            <div class="form-group mb-4">
                <label for="profile_photo">Foto profil</label>
                <input type="file" class="form-control-file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png">
                <small class="text-muted">Opsional. JPEG atau PNG, maksimal 2 MiB.</small>
            </div>
            <div class="ami-actions justify-content-end">
                <button type="submit" class="btn btn-primary btn-ami"><i class="fas fa-save" aria-hidden="true"></i>Simpan perubahan</button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
