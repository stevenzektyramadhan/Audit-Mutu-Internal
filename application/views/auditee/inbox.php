<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tugas = isset($tugas) ? $tugas : [];
$filters = isset($filters) ? $filters : ['periode_id' => 0, 'status' => ''];
$periode_list = isset($periode_list) ? $periode_list : [];
$status_options = [
    '' => 'Semua status',
    'belum_diisi' => 'Belum diisi',
    'draft' => 'Draft',
    'submitted' => 'Submitted',
    'revisi' => 'Revisi',
    'dinilai' => 'Sudah dinilai',
];
$status_icons = [
    'belum_diisi' => 'fa-exclamation-circle',
    'draft' => 'fa-save',
    'submitted' => 'fa-paper-plane',
    'revisi' => 'fa-undo',
    'dinilai' => 'fa-check-circle',
];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Daftar tugas masuk</h2>
        </div>

        <form action="<?php echo site_url('auditee/tugas'); ?>" method="get" class="ami-filter-bar">
            <div class="ami-filter-select">
                <label class="ami-stat-label" for="periode_id">Periode</label>
                <select class="form-control" id="periode_id" name="periode_id">
                    <option value="0">Semua periode</option>
                    <?php foreach ($periode_list as $periode): ?>
                        <option value="<?php echo (int) $periode->id; ?>" <?php echo (int) $filters['periode_id'] === (int) $periode->id ? 'selected' : ''; ?>>
                            <?php echo html_escape($periode->nama_periode); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ami-filter-select">
                <label class="ami-stat-label" for="status">Status</label>
                <select class="form-control" id="status" name="status">
                    <?php foreach ($status_options as $value => $label): ?>
                        <option value="<?php echo html_escape($value); ?>" <?php echo $filters['status'] === $value ? 'selected' : ''; ?>>
                            <?php echo html_escape($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-ami btn-outline-ami">
                <i class="fas fa-filter" aria-hidden="true"></i> Filter
            </button>
            <a href="<?php echo site_url('auditee/tugas'); ?>" class="btn-ami btn-outline-ami">
                <i class="fas fa-sync-alt" aria-hidden="true"></i> Reset
            </a>
        </form>

        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Periode</th>
                    <th>Standar</th>
                    <th>Auditor</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Instrumen</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($tugas)): ?>
                    <?php foreach ($tugas as $index => $item): ?>
                        <?php $status_key = $item->display_status; ?>
                        <tr>
                            <td><?php echo (int) $index + 1; ?></td>
                            <td>
                                <div><?php echo html_escape($item->nama_periode ?: '-'); ?></div>
                                <?php if (!empty($item->tanggal_buka) && !empty($item->tanggal_tutup)): ?>
                                    <div class="text-muted" style="font-size:12px;">
                                        <?php echo html_escape(format_tanggal_indo($item->tanggal_buka)); ?> - <?php echo html_escape(format_tanggal_indo($item->tanggal_tutup)); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="font-weight-bold"><?php echo html_escape($item->nama_standar ?: '-'); ?></td>
                            <td><?php echo html_escape($item->auditor_nama ?: '-'); ?></td>
                            <td><?php echo (int) $item->jumlah_terisi; ?> / <?php echo (int) $item->jumlah_pertanyaan; ?></td>
                            <td>
                                <span class="ami-status status-<?php echo html_escape($status_key); ?>">
                                    <i class="fas <?php echo html_escape($status_icons[$status_key] ?? 'fa-circle'); ?>" aria-hidden="true"></i>
                                    <?php echo html_escape($item->display_status_label); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($item->file_instrumen)): ?>
                                    <a class="ami-action-btn" href="<?php echo site_url('auditee/download_instrumen/' . (int) $item->id); ?>">
                                        <i class="fas fa-download" aria-hidden="true"></i> Download
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Belum ada</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a class="ami-action-btn" href="<?php echo site_url('auditee/form/' . (int) $item->id); ?>">
                                    <?php if (in_array($status_key, ['submitted', 'dinilai'], TRUE)): ?>
                                        <i class="fas fa-eye" aria-hidden="true"></i> Lihat
                                    <?php else: ?>
                                        <i class="fas fa-pen" aria-hidden="true"></i> Isi
                                    <?php endif; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="ami-empty">
                                <div class="ami-empty-icon"><i class="fas fa-clipboard-check" aria-hidden="true"></i></div>
                                <div class="ami-empty-title">Tidak ada tugas</div>
                                <div>Tugas sesuai filter akan tampil di sini.</div>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
