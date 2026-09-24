<?php defined('BASEPATH') OR exit('No direct script access allowed'); include APPPATH . 'views/layouts/header.php'; include APPPATH . 'views/layouts/sidebar.php'; ?>
<div class="ami-panel"><div class="ami-panel-body">
    <h2 class="ami-section-title">Hasil Import Master Akun</h2>
    <div class="alert alert-success"><?php echo html_escape($message); ?> Simpan informasi ini sekarang; kata sandi sementara tidak akan ditampilkan lagi.</div>
    <div class="table-responsive"><table class="table ami-table"><thead><tr><th>NIP/NIDN</th><th>Nama</th><th>Email</th><th>Role</th><th>Kata sandi sementara</th></tr></thead><tbody><?php foreach ($created as $account): ?><tr><td><?php echo html_escape($account['identity_number']); ?></td><td><?php echo html_escape($account['nama']); ?></td><td><?php echo html_escape($account['email']); ?></td><td><?php echo html_escape($account['role']); ?></td><td><code><?php echo html_escape($account['password']); ?></code></td></tr><?php endforeach; ?></tbody></table></div>
    <p>Setiap pengguna perlu memakai <a href="<?php echo site_url('auth/forgot-password'); ?>">Lupa Password</a> untuk mengganti kata sandi sementara.</p>
    <a class="btn-ami btn-primary" href="<?php echo site_url('users'); ?>">Kembali ke Pengguna</a>
</div></div><?php include APPPATH . 'views/layouts/footer.php'; ?>
