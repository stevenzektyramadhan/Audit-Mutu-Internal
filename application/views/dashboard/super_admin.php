<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$title = 'Dashboard Super Admin';
$page_title = 'Dashboard';
$page_subtitle = 'Beranda / Dashboard';
$active_menu = 'dashboard';
$stats = isset($stats) ? $stats : [];
$recent_tugas = isset($recent_tugas) ? $recent_tugas : [];
$standar_summary = isset($standar_summary) ? $standar_summary : [];
$active_periode = isset($active_periode) ? $active_periode : NULL;
$task_status_chart = isset($task_status_chart) ? $task_status_chart : '';
$active_task_count = (int) (isset($stats['belum_diisi']) ? $stats['belum_diisi'] : 0)
    + (int) (isset($stats['diisi']) ? $stats['diisi'] : 0)
    + (int) (isset($stats['dinilai']) ? $stats['dinilai'] : 0);
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

<div class="ami-dashboard-logo-banner">
    <img src="<?= base_url('assets/img/logo-2.png'); ?>" alt="Logo LPM" class="ami-dashboard-logo">
    <div>
        <div class="ami-dashboard-logo-title">Lembaga Penjaminan Mutu</div>
        <div class="ami-dashboard-logo-subtitle">Sistem Audit Mutu Internal Perguruan Tinggi</div>
    </div>
</div>

<style>
    .ami-dashboard-logo-banner {
        display: flex;
        align-items: center;
        gap: 16px;
        background: var(--ami-panel);
        border: 1px solid var(--ami-border);
        border-radius: 10px;
        padding: 18px 22px;
        margin-bottom: 18px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }
    .ami-dashboard-logo {
        height: 56px;
        width: auto;
        object-fit: contain;
        flex-shrink: 0;
    }
    .ami-dashboard-logo-title {
        font-size: 17px;
        font-weight: 700;
        color: var(--ami-text);
        line-height: 1.3;
    }
    .ami-dashboard-logo-subtitle {
        font-size: 13px;
        color: var(--ami-muted);
        margin-top: 2px;
    }

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

<div class="ami-section-head mt-0">
    <h2 class="ami-section-title">Status tugas periode aktif</h2>
    <a class="btn btn-outline-ami btn-ami py-1" href="<?php echo site_url('lpmpi/laporan'); ?>">
        <i class="fas fa-chart-pie" aria-hidden="true"></i>
        Laporan &amp; Statistik
    </a>
</div>
<div class="ami-panel mb-3">
    <div class="ami-panel-body">
        <?php if ($active_periode): ?>
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap: 10px;">
                <div class="ami-stat-label mb-0">Periode aktif: <?php echo html_escape($active_periode->nama_periode); ?></div>
                <div class="text-muted small">Laporan menyediakan analisis dan ekspor detail.</div>
            </div>
            <?php if ($active_task_count > 0): ?>
                <div style="height: 320px;">
                    <canvas id="dashboardTaskStatusChart" aria-label="Grafik status tugas periode aktif" aria-describedby="status-summary-title status-summary-counts"></canvas>
                </div>
            <?php else: ?>
                <div class="ami-empty">
                    <div class="ami-empty-icon"><i class="fas fa-clipboard-list" aria-hidden="true"></i></div>
                    <div class="ami-empty-title">Belum ada tugas audit pada periode aktif</div>
                    <div>Grafik status akan tampil setelah tugas audit dibuat.</div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="ami-empty">
                <div class="ami-empty-icon"><i class="fas fa-calendar-times" aria-hidden="true"></i></div>
                <div class="ami-empty-title">Belum ada periode audit aktif</div>
                <div>Status tugas hanya ditampilkan untuk periode yang aktif.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="admin-overview-grid">
    <section class="admin-summary-card" aria-labelledby="status-summary-title">
        <h2 class="admin-summary-title" id="status-summary-title">Ringkasan status tugas</h2>
        <div id="status-summary-counts">
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
            <div class="ami-empty py-3">
                <div class="ami-empty-title">Belum ada standar audit</div>
                <a href="<?php echo site_url('standar/create'); ?>" class="btn btn-outline-ami btn-ami"><i class="fas fa-plus" aria-hidden="true"></i>Tambah standar</a>
            </div>
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
        <div class="ami-empty">
            <div class="ami-empty-icon"><i class="fas fa-clipboard-list" aria-hidden="true"></i></div>
            <div class="ami-empty-title">Belum ada tugas audit</div>
            <div>Buat tugas setelah pengguna, standar, dan pertanyaan tersedia.</div>
            <a href="<?php echo site_url('tugas_audit/create'); ?>" class="btn btn-primary btn-ami"><i class="fas fa-plus" aria-hidden="true"></i>Buat tugas</a>
        </div>
    <?php endif; ?>
</div>

<?php if ($active_periode && $active_task_count > 0 && $task_status_chart !== ''): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    (function () {
        if (typeof Chart === 'undefined') return;
        var canvas = document.getElementById('dashboardTaskStatusChart');
        if (!canvas) return;

        var chartData = <?php echo $task_status_chart; ?>;
        var styles = getComputedStyle(document.documentElement);
        var colors = [
            styles.getPropertyValue('--ami-amber').trim(),
            styles.getPropertyValue('--ami-blue').trim(),
            styles.getPropertyValue('--ami-green').trim()
        ];

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: chartData.labels,
                datasets: [{
                    data: chartData.values,
                    backgroundColor: colors,
                    borderColor: styles.getPropertyValue('--ami-panel').trim(),
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    })();
    </script>
<?php endif; ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
