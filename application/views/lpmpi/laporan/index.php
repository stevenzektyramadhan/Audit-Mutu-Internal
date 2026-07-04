<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$query = http_build_query(array_filter($filters));
$export_url = site_url('lpmpi/laporan/export') . ($query ? '?' . $query : '');
?>

<div class="ami-panel mb-3">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Laporan & Statistik</h2>
            <a class="btn-ami btn-outline-ami" href="<?php echo $export_url; ?>">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
        </div>

        <?php echo form_open('lpmpi/laporan', ['method' => 'get', 'class' => 'ami-filter-bar']); ?>
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
            <a href="<?php echo site_url('lpmpi/laporan'); ?>" class="btn-ami btn-outline-ami">
                <i class="fas fa-sync-alt" aria-hidden="true"></i> Reset
            </a>
        <?php echo form_close(); ?>
    </div>
</div>

<div class="ami-panel mb-3">
    <div class="ami-panel-body">
        <h2 class="ami-section-title mb-3">Grafik Skor Rata-rata</h2>
        <?php if (!empty($rekap)): ?>
            <div style="height: 320px;">
                <canvas id="laporanChart" aria-label="Grafik skor rata-rata per standar"></canvas>
            </div>
        <?php else: ?>
            <div class="ami-empty">
                <div class="ami-empty-icon"><i class="fas fa-chart-pie" aria-hidden="true"></i></div>
                <div class="ami-empty-title">Belum ada data skor</div>
                <div>Data akan muncul setelah auditor mengisi skor audit.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="ami-panel">
    <div class="ami-panel-body">
        <h2 class="ami-section-title mb-3">Rekap per Standar</h2>
        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>Standar</th>
                    <th>Total Tugas</th>
                    <th>Total Jawaban</th>
                    <th>Rata-rata Skor</th>
                    <th>Rentang Skor</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($rekap)): ?>
                    <?php foreach ($rekap as $row): ?>
                        <tr>
                            <td><?php echo html_escape($row->nama_standar); ?></td>
                            <td><?php echo (int) $row->total_tugas; ?></td>
                            <td><?php echo (int) $row->total_jawaban; ?></td>
                            <td><strong><?php echo number_format((float) $row->rata_rata_skor, 2); ?></strong></td>
                            <td>
                                <?php if ($row->skor_min === NULL): ?>
                                    <span class="text-muted">-</span>
                                <?php else: ?>
                                    <?php echo (int) $row->skor_min; ?> - <?php echo (int) $row->skor_max; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo site_url('lpmpi/laporan/detail/' . (int) $row->standar_id) . ($query ? '?' . $query : ''); ?>" class="ami-action-btn">
                                    <i class="fas fa-eye" aria-hidden="true"></i><span>Detail</span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6"><div class="ami-empty">
                            <div class="ami-empty-icon"><i class="fas fa-chart-pie" aria-hidden="true"></i></div>
                            <div class="ami-empty-title">Belum ada laporan</div>
                            <div>Gunakan filter berbeda atau tunggu data audit selesai dinilai.</div>
                        </div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($rekap)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    (function () {
        if (typeof Chart === 'undefined') return;
        var canvas = document.getElementById('laporanChart');
        if (!canvas) return;

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: <?php echo $chart_labels; ?>,
                datasets: [{
                    label: 'Rata-rata skor',
                    data: <?php echo $chart_values; ?>,
                    backgroundColor: 'rgba(77, 163, 255, 0.55)',
                    borderColor: 'rgba(77, 163, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 4
                    }
                }
            }
        });
    })();
    </script>
<?php endif; ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
