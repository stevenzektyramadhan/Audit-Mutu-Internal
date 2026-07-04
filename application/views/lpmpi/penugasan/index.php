<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$status_labels = [
    STATUS_BELUM_DIISI => 'Belum Diisi',
    STATUS_DIISI => 'Diisi',
    STATUS_DINILAI => 'Dinilai',
];
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Penugasan Auditor</h2>
            <a class="btn-ami btn-outline-ami" href="<?php echo site_url('lpmpi/penugasan/create'); ?>">
                <i class="fas fa-plus"></i> Buat Tugas Baru
            </a>
        </div>

        <?php echo form_open('lpmpi/penugasan', ['method' => 'get', 'class' => 'ami-filter-bar']); ?>
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
                <label class="ami-stat-label" for="standar_id">Standar</label>
                <select class="form-control" id="standar_id" name="standar_id">
                    <option value="0">Semua standar</option>
                    <?php foreach ($standar_list as $standar): ?>
                        <option value="<?php echo (int) $standar->id; ?>" <?php echo (int) $filters['standar_id'] === (int) $standar->id ? 'selected' : ''; ?>>
                            <?php echo html_escape($standar->nama_standar); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ami-filter-select">
                <label class="ami-stat-label" for="auditee_id">Auditee</label>
                <select class="form-control" id="auditee_id" name="auditee_id">
                    <option value="0">Semua auditee</option>
                    <?php foreach ($auditee_list as $auditee): ?>
                        <option value="<?php echo (int) $auditee->id; ?>" <?php echo (int) $filters['auditee_id'] === (int) $auditee->id ? 'selected' : ''; ?>>
                            <?php echo html_escape($auditee->nama); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-ami btn-outline-ami">
                <i class="fas fa-filter" aria-hidden="true"></i> Filter
            </button>
            <a href="<?php echo site_url('lpmpi/penugasan'); ?>" class="btn-ami btn-outline-ami">
                <i class="fas fa-sync-alt" aria-hidden="true"></i> Reset
            </a>
        <?php echo form_close(); ?>

        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Periode</th>
                    <th>Standar</th>
                    <th>Auditor</th>
                    <th>Auditee</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($tugas_list)): ?>
                    <?php $no = 1; ?>
                    <?php foreach ($tugas_list as $row): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo html_escape($row->nama_periode ?? '-'); ?></td>
                            <td><?php echo html_escape($row->nama_standar ?? '-'); ?></td>
                            <td><?php echo html_escape($row->auditor_nama ?? '-'); ?></td>
                            <td>
                                <div><?php echo html_escape($row->auditee_nama ?? '-'); ?></div>
                                <?php if (!empty($row->auditee_unit)): ?>
                                    <div class="text-muted" style="font-size: 12px;"><?php echo html_escape($row->auditee_unit); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="ami-status status-<?php echo html_escape($row->status ?? STATUS_BELUM_DIISI); ?>">
                                    <?php echo html_escape($status_labels[$row->status] ?? ($row->status ?? '-')); ?>
                                </span>
                            </td>
                            <td>
                                <div class="ami-row-actions">
                                    <a href="<?php echo site_url('tugas_audit/show/' . (int) $row->id); ?>" class="ami-action-btn" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php echo form_open('lpmpi/penugasan/delete/' . (int) $row->id, ['class' => 'd-inline', 'onsubmit' => "return confirm('Hapus penugasan ini beserta seluruh jawaban auditnya?');"]); ?>
                                        <button type="submit" class="ami-action-btn danger" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
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
                            <div class="ami-empty-title">Belum ada penugasan</div>
                            <div>Buat penugasan untuk menugaskan auditor ke auditee.</div>
                        </div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
