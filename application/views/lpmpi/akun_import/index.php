<?php defined('BASEPATH') OR exit('No direct script access allowed'); include APPPATH . 'views/layouts/header.php'; include APPPATH . 'views/layouts/sidebar.php'; ?>
<div class="ami-panel"><div class="ami-panel-body">
    <h2 class="ami-section-title">Import Master Akun</h2>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo html_escape($error); ?></div><?php endif; ?>
    <p>Import hanya membuat akun auditor atau auditee baru. Unduh template, isi sheet <strong>Master Akun</strong>, lalu unggah untuk preview. Maksimal 1.000 baris dan <?php echo html_escape((string) $upload_limit_mib); ?> MiB.</p>
    <p><a class="btn-ami btn-outline-ami" href="<?php echo site_url('lpmpi/akun-import/template'); ?>">Unduh template XLSX</a></p>
    <?php echo form_open_multipart('lpmpi/akun-import/preview'); ?>
        <div class="form-group"><label for="account-file">File XLSX</label><input id="account-file" class="form-control-file" type="file" name="account_file" accept=".xlsx" required></div>
        <button class="btn-ami btn-primary" type="submit">Unggah &amp; Preview</button>
        <a class="btn-ami btn-outline-ami" href="<?php echo site_url('users'); ?>">Kembali</a>
    <?php echo form_close(); ?>
</div></div><?php include APPPATH . 'views/layouts/footer.php'; ?>
