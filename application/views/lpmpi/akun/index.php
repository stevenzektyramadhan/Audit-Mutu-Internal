<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$filters = isset($filters) ? $filters : ['q' => '', 'role' => ''];
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Daftar Akun Auditee & Auditor</h2>
            <a class="btn-ami btn-outline-ami" href="<?php echo site_url('lpmpi/akun/create'); ?>">
                <i class="fas fa-plus" aria-hidden="true"></i> Tambah Akun
            </a>
        </div>

        <form method="get" action="<?php echo site_url('lpmpi/akun'); ?>" class="ami-filter-bar">
            <div class="ami-filter-grow">
                <label for="akun-search" class="ami-stat-label">Cari akun</label>
                <input id="akun-search" type="search" name="q" class="form-control" value="<?php echo html_escape($filters['q']); ?>" placeholder="Nama, email, atau unit">
            </div>
            <div class="ami-filter-select">
                <label for="akun-role" class="ami-stat-label">Role</label>
                <select id="akun-role" name="role" class="form-control">
                    <option value="">Semua role</option>
                    <option value="auditor" <?php echo $filters['role'] === 'auditor' ? 'selected' : ''; ?>>Auditor</option>
                    <option value="auditee" <?php echo $filters['role'] === 'auditee' ? 'selected' : ''; ?>>Auditee</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-ami"><i class="fas fa-filter" aria-hidden="true"></i>Terapkan</button>
            <?php if ($filters['q'] !== '' || $filters['role'] !== ''): ?>
                <a href="<?php echo site_url('lpmpi/akun'); ?>" class="btn btn-outline-ami btn-ami">Reset</a>
            <?php endif; ?>
        </form>

        <?php include APPPATH . 'views/lpmpi/akun/list_auditor.php'; ?>
        <?php include APPPATH . 'views/lpmpi/akun/list_auditee.php'; ?>

        <?php if (empty($auditor_list) && empty($auditee_list)): ?>
            <div class="ami-empty">
                <div class="ami-empty-icon"><i class="fas fa-users" aria-hidden="true"></i></div>
                <div class="ami-empty-title">Akun tidak ditemukan</div>
                <div><?php echo $filters['q'] !== '' || $filters['role'] !== '' ? 'Coba ubah kata kunci atau filter role.' : 'Tambahkan akun auditor atau auditee terlebih dahulu.'; ?></div>
                <?php if ($filters['q'] !== '' || $filters['role'] !== ''): ?>
                    <a href="<?php echo site_url('lpmpi/akun'); ?>" class="btn btn-outline-ami btn-ami"><i class="fas fa-undo" aria-hidden="true"></i>Reset filter</a>
                <?php else: ?>
                    <a href="<?php echo site_url('lpmpi/akun/create'); ?>" class="btn btn-primary btn-ami"><i class="fas fa-plus" aria-hidden="true"></i>Tambah Akun</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
