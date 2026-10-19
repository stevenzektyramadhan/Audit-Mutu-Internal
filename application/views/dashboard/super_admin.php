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
$icon = static function ($name) {
    $paths = [
        'users' => '<path d="M16 20v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 20v-1a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'user-check' => '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M8.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><polyline points="17 11 19 13 23 9"/>',
        'building' => '<path d="M3 21h18M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M9 7h2M9 11h2M13 7h2M13 11h2"/>',
        'award' => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.12"/>',
        'tasks' => '<path d="M9 11 11 13 15 9M9 17l2 2 4-4M9 5l2 2 4-4"/><rect x="3" y="3" width="18" height="18" rx="2"/>',
        'clipboard-list' => '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 4V2h6v2M8 9h8M8 13h8M8 17h5"/>',
        'alert-circle' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14 9 11"/>',
        'chart-pie' => '<path d="M21 12a9 9 0 1 1-9-9v9Z"/><path d="M12 3a9 9 0 0 1 9 9h-9Z"/>',
        'calendar-times' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="9" y1="14" x2="15" y2="20"/><line x1="15" y1="14" x2="9" y2="20"/>',
        'plus' => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
        'circle' => '<circle cx="12" cy="12" r="10"/>',
    ];
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['circle']) . '</svg>';
};
$status_labels = [
    STATUS_BELUM_DIISI => ['label' => 'Belum diisi', 'icon' => 'alert-circle'],
    STATUS_DIISI => ['label' => 'Diisi', 'icon' => 'clock'],
    STATUS_DINILAI => ['label' => 'Dinilai', 'icon' => 'check-circle'],
];
$stat_cards = [
    ['label' => 'Total User', 'value' => isset($stats['total_user']) ? $stats['total_user'] : 0, 'icon' => 'users', 'tone' => 'tone-blue'],
    ['label' => 'Auditor', 'value' => isset($stats['total_auditor']) ? $stats['total_auditor'] : 0, 'icon' => 'user-check', 'tone' => 'tone-green'],
    ['label' => 'Auditee', 'value' => isset($stats['total_auditee']) ? $stats['total_auditee'] : 0, 'icon' => 'building', 'tone' => 'tone-amber'],
    ['label' => 'Standar', 'value' => isset($stats['total_standar']) ? $stats['total_standar'] : 0, 'icon' => 'award', 'tone' => 'tone-violet'],
    ['label' => 'Pertanyaan', 'value' => isset($stats['total_pertanyaan']) ? $stats['total_pertanyaan'] : 0, 'icon' => 'tasks', 'tone' => 'tone-rose'],
    ['label' => 'Tugas Audit', 'value' => isset($stats['total_tugas']) ? $stats['total_tugas'] : 0, 'icon' => 'clipboard-list', 'tone' => 'tone-teal'],
];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-dashboard-logo-banner">
    <img src="<?= html_escape($organization_logo_url); ?>" alt="Logo LPM" class="ami-dashboard-logo">
    <div>
        <div class="ami-dashboard-logo-title">Lembaga Penjaminan Mutu</div>
        <div class="ami-dashboard-logo-subtitle">Sistem Audit Mutu Internal Perguruan Tinggi</div>
    </div>
</div>

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
                <?php echo $icon($card['icon']); ?>
            </div>
            <div>
                <div class="ami-stat-label"><?php echo html_escape($card['label']); ?></div>
                <div class="ami-stat-value"><?php echo html_escape((string) $card['value']); ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="ami-section-head tw-mt-0">
    <h2 class="ami-section-title">Status tugas periode aktif</h2>
    <a class="btn-outline-ami btn-ami tw-py-1" href="<?php echo site_url('lpmpi/laporan'); ?>">
        <?php echo $icon('chart-pie'); ?>
        Laporan &amp; Statistik
    </a>
</div>
<div class="ami-panel tw-mb-3">
    <div class="ami-panel-body">
        <?php if ($active_periode): ?>
            <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-2.5 tw-mb-3">
                <div class="ami-stat-label tw-mb-0">Periode aktif: <?php echo html_escape($active_periode->nama_periode); ?></div>
                <div class="tw-text-slate-500 tw-text-xs">Laporan menyediakan analisis dan ekspor detail.</div>
            </div>
            <?php if ($active_task_count > 0): ?>
                <div style="height: 320px;">
                    <canvas id="dashboardTaskStatusChart" aria-label="Grafik status tugas periode aktif" aria-describedby="status-summary-title status-summary-counts"></canvas>
                </div>
            <?php else: ?>
                <div class="ami-empty">
                    <div class="ami-empty-icon"><?php echo $icon('clipboard-list'); ?></div>
                    <div class="ami-empty-title">Belum ada tugas audit pada periode aktif</div>
                    <div>Grafik status akan tampil setelah tugas audit dibuat.</div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="ami-empty">
                <div class="ami-empty-icon"><?php echo $icon('calendar-times'); ?></div>
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
                <span class="tw-text-blue-600 tw-font-bold"><?php echo html_escape((string) $standar->total_pertanyaan); ?> soal</span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="ami-empty tw-py-3">
                <div class="ami-empty-title">Belum ada standar audit</div>
                <a href="<?php echo site_url('standar/create'); ?>" class="btn-outline-ami btn-ami"><?php echo $icon('plus'); ?>Tambah standar</a>
            </div>
        <?php endif; ?>
    </section>
</div>

<div class="ami-section-head tw-mt-0">
    <h2 class="ami-section-title">Tugas audit terkini</h2>
    <a class="tw-button-primary btn-ami tw-py-1" href="<?php echo site_url('tugas_audit'); ?>">
        <?php echo $icon('arrow-right'); ?>
        Lihat semua
    </a>
</div>

<div class="ami-panel">
    <?php if (!empty($recent_tugas)): ?>
        <div class="tw-overflow-x-auto">
            <table class="ami-table">
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
                    <?php $meta = isset($status_labels[$tugas->status]) ? $status_labels[$tugas->status] : ['label' => $tugas->status, 'icon' => 'circle']; ?>
                    <tr>
                        <td><?php echo html_escape($tugas->auditee_nama); ?></td>
                        <td><?php echo html_escape($tugas->auditor_nama); ?></td>
                        <td><?php echo html_escape($tugas->nama_standar); ?></td>
                        <td>
                            <span class="ami-status status-<?php echo html_escape($tugas->status); ?>">
                                <?php echo $icon($meta['icon']); ?>
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
            <div class="ami-empty-icon"><?php echo $icon('clipboard-list'); ?></div>
            <div class="ami-empty-title">Belum ada tugas audit</div>
            <div>Buat tugas setelah pengguna, standar, dan pertanyaan tersedia.</div>
            <a href="<?php echo site_url('tugas_audit/create'); ?>" class="tw-button-primary btn-ami"><?php echo $icon('plus'); ?>Buat tugas</a>
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
