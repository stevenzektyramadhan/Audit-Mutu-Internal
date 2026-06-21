<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$filters = isset($filters) ? $filters : ['q' => '', 'status' => ''];
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Daftar tugas audit</h2>
            <a class="btn-ami btn-outline-ami" href="<?php echo site_url('tugas_audit/create'); ?>">
                <i class="fas fa-plus"></i> Buat tugas
            </a>
        </div>

        <form method="get" action="<?php echo site_url('tugas_audit'); ?>" class="ami-filter-bar">
            <div class="ami-filter-grow">
                <label for="tugas-search" class="ami-stat-label">Cari tugas</label>
                <input id="tugas-search" type="search" name="q" class="form-control" value="<?php echo html_escape($filters['q']); ?>" placeholder="Auditee, auditor, atau standar">
            </div>
            <div class="ami-filter-select">
                <label for="status-filter" class="ami-stat-label">Status</label>
                <select id="status-filter" name="status" class="form-control">
                    <option value="">Semua status</option>
                    <option value="belum_diisi" <?php echo $filters['status'] === STATUS_BELUM_DIISI ? 'selected' : ''; ?>>Belum diisi</option>
                    <option value="diisi" <?php echo $filters['status'] === STATUS_DIISI ? 'selected' : ''; ?>>Sudah diisi</option>
                    <option value="dinilai" <?php echo $filters['status'] === STATUS_DINILAI ? 'selected' : ''; ?>>Sudah dinilai</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-ami"><i class="fas fa-filter" aria-hidden="true"></i>Terapkan</button>
            <?php if ($filters['q'] !== '' || $filters['status'] !== ''): ?>
                <a href="<?php echo site_url('tugas_audit'); ?>" class="btn btn-outline-ami btn-ami">Reset</a>
            <?php endif; ?>
        </form>

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
                        <?php $status_meta = status_audit_meta($row->status); ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo html_escape($row->auditee_nama); ?></td>
                            <td><?php echo html_escape($row->auditor_nama); ?></td>
                            <td><?php echo html_escape($row->nama_standar); ?></td>
                            <td>
                                <span class="ami-status <?php echo html_escape($status_meta['tone']); ?>">
                                    <?php echo html_escape($status_meta['label']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo html_escape(format_tanggal_indo($row->created_at)); ?>
                            </td>
                            <td>
                                <div class="ami-row-actions">
                                    <a href="<?php echo site_url('tugas_audit/show/'.$row->id); ?>" class="ami-action-btn" title="Lihat detail tugas">
                                        <i class="fas fa-eye" aria-hidden="true"></i><span>Detail</span>
                                    </a>
                                    <?php echo form_open('tugas_audit/delete/' . (int) $row->id, ['class' => 'd-inline', 'onsubmit' => "return confirm('Apakah Anda yakin ingin menghapus tugas ini beserta seluruh jawabannya?');"]); ?>
                                        <button type="submit" class="ami-action-btn danger" title="Hapus tugas">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i><span>Hapus</span>
                                        </button>
                                    <?php echo form_close(); ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7"><div class="ami-empty">
                            <div class="ami-empty-icon"><i class="fas fa-clipboard-list" aria-hidden="true"></i></div>
                            <div class="ami-empty-title">Tugas audit tidak ditemukan</div>
                            <div><?php echo $filters['q'] !== '' || $filters['status'] !== '' ? 'Coba ubah kata kunci atau filter status.' : 'Buat penugasan auditor dan auditee untuk memulai audit.'; ?></div>
                            <?php if ($filters['q'] !== '' || $filters['status'] !== ''): ?>
                                <a href="<?php echo site_url('tugas_audit'); ?>" class="btn btn-outline-ami btn-ami"><i class="fas fa-undo" aria-hidden="true"></i>Reset filter</a>
                            <?php else: ?>
                                <a href="<?php echo site_url('tugas_audit/create'); ?>" class="btn btn-primary btn-ami"><i class="fas fa-plus" aria-hidden="true"></i>Buat tugas</a>
                            <?php endif; ?>
                        </div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
