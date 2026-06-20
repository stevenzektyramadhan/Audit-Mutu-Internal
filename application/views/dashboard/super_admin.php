<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$title = 'Dashboard Super Admin';
$page_title = 'Dashboard';
$page_subtitle = 'Beranda / Dashboard';
$active_menu = 'dashboard';
$stats = isset($stats) ? $stats : [];
$recent_tugas = isset($recent_tugas) ? $recent_tugas : [];
$standar_summary = isset($standar_summary) ? $standar_summary : [];
$menu_badges = ['tugas_audit' => isset($stats['total_tugas']) ? $stats['total_tugas'] : 0];
$status_labels = [
    STATUS_BELUM_DIISI => ['label' => 'Belum diisi', 'icon' => 'fa-exclamation-circle'],
    STATUS_DIISI => ['label' => 'Diisi', 'icon' => 'fa-clock'],
    STATUS_DINILAI => ['label' => 'Dinilai', 'icon' => 'fa-check-circle'],
];
$stat_cards = [
    ['label' => 'Total User', 'value' => isset($stats['total_user']) ? $stats['total_user'] : 0, 'icon' => 'fa-users', 'tone' => 'tone-blue'],
    ['label' => 'Auditor', 'value' => isset($stats['total_auditor']) ? $stats['total_auditor'] : 0, 'icon' => 'fa-user-check', 'tone' => 'tone-green'],
    ['label' => 'Auditee', 'value' => isset($stats['total_auditee']) ? $stats['total_auditee'] : 0, 'icon' => 'fa-building', 'tone' => 'tone-amber'],
    ['label' => 'Standar', 'value' => isset($stats['total_standar']) ? $stats['total_standar'] : 0, 'icon' => 'fa-award', 'tone' => 'tone-violet'],
    ['label' => 'Pertanyaan', 'value' => isset($stats['total_pertanyaan']) ? $stats['total_pertanyaan'] : 0, 'icon' => 'fa-tasks', 'tone' => 'tone-rose'],
    ['label' => 'Tugas Audit', 'value' => isset($stats['total_tugas']) ? $stats['total_tugas'] : 0, 'icon' => 'fa-clipboard-list', 'tone' => 'tone-teal'],
];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<style>
    .admin-stat-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(120px, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .admin-stat-grid .ami-stat-card {
        min-height: 72px;
        padding: 12px;
        gap: 10px;
    }

    .admin-stat-grid .ami-stat-icon {
        width: 36px;
        height: 36px;
        flex-basis: 36px;
    }

    .admin-stat-grid .ami-stat-value {
        font-size: 21px;
    }

    .admin-overview-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .admin-summary-card {
        background: var(--ami-panel);
        border: 1px solid var(--ami-border);
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.2);
    }

    .admin-summary-title {
        color: var(--ami-muted);
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .admin-summary-row {
        min-height: 29px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid var(--ami-border);
        font-size: 13px;
    }

    .admin-summary-row:last-child {
        border-bottom: 0;
    }

    @media (max-width: 1199.98px) {
        .admin-stat-grid {
            grid-template-columns: repeat(3, minmax(140px, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .admin-stat-grid,
        .admin-overview-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="admin-stat-grid">
    <?php foreach ($stat_cards as $card): ?>
        <div class="ami-stat-card">
            <div class="ami-stat-icon <?php echo html_escape($card['tone']); ?>">
                <i class="fas <?php echo html_escape($card['icon']); ?>" aria-hidden="true"></i>
            </div>
            <div>
                <div class="ami-stat-label"><?php echo html_escape($card['label']); ?></div>
                <div class="ami-stat-value"><?php echo html_escape((string) $card['value']); ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="admin-overview-grid">
    <section class="admin-summary-card" aria-labelledby="status-summary-title">
        <h2 class="admin-summary-title" id="status-summary-title">Ringkasan status tugas</h2>
        <div class="admin-summary-row">
            <span>Belum diisi</span>
            <span class="ami-status status-belum_diisi"><?php echo html_escape((string) (isset($stats['belum_diisi']) ? $stats['belum_diisi'] : 0)); ?> tugas</span>
        </div>
        <div class="admin-summary-row">
            <span>Sudah diisi</span>
            <span class="ami-status status-diisi"><?php echo html_escape((string) (isset($stats['diisi']) ? $stats['diisi'] : 0)); ?> tugas</span>
        </div>
        <div class="admin-summary-row">
            <span>Sudah dinilai</span>
            <span class="ami-status status-dinilai"><?php echo html_escape((string) (isset($stats['dinilai']) ? $stats['dinilai'] : 0)); ?> tugas</span>
        </div>
    </section>

    <section class="admin-summary-card" aria-labelledby="standar-summary-title">
        <h2 class="admin-summary-title" id="standar-summary-title">Standar aktif</h2>
        <?php if (!empty($standar_summary)): ?>
            <?php foreach ($standar_summary as $standar): ?>
                <div class="admin-summary-row">
                    <span><?php echo html_escape($standar->nama_standar); ?></span>
                    <span class="text-primary font-weight-bold"><?php echo html_escape((string) $standar->total_pertanyaan); ?> soal</span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="ami-empty py-3">Belum ada standar audit.</div>
        <?php endif; ?>
    </section>
</div>

<div class="ami-section-head mt-0">
    <h2 class="ami-section-title">Tugas audit terkini</h2>
    <a class="btn btn-primary btn-ami py-1" href="<?php echo site_url('tugas_audit'); ?>">
        <i class="fas fa-arrow-right" aria-hidden="true"></i>
        Lihat semua
    </a>
</div>

<div class="ami-panel">
    <?php if (!empty($recent_tugas)): ?>
        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>Auditee</th>
                    <th>Auditor</th>
                    <th>Standar</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recent_tugas as $tugas): ?>
                    <?php $meta = isset($status_labels[$tugas->status]) ? $status_labels[$tugas->status] : ['label' => $tugas->status, 'icon' => 'fa-circle']; ?>
                    <tr>
                        <td><?php echo html_escape($tugas->auditee_nama); ?></td>
                        <td><?php echo html_escape($tugas->auditor_nama); ?></td>
                        <td><?php echo html_escape($tugas->nama_standar); ?></td>
                        <td>
                            <span class="ami-status status-<?php echo html_escape($tugas->status); ?>">
                                <i class="fas <?php echo html_escape($meta['icon']); ?>" aria-hidden="true"></i>
                                <?php echo html_escape($meta['label']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="ami-empty">Belum ada tugas audit.</div>
    <?php endif; ?>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
