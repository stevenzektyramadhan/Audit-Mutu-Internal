<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$profil = isset($profil) ? $profil : NULL;
$prodi = isset($prodi) ? $prodi : [];
$mahasiswa_stats = isset($mahasiswa_stats) ? $mahasiswa_stats : [];
$akreditasi_summary = isset($akreditasi_summary) ? $akreditasi_summary : [];
$schema_ready = !empty($schema_ready);
$can_manage = !empty($can_manage);
$nama_sinkron = $profil ? trim((string) ($profil->nama_pt_pddikti ?: $profil->nama_pt)) : '';

$logo_src = base_url('assets/img/logo-2.png');
if ($profil && !empty($profil->logo_path)) {
    $logo_src = base_url('uploads/profil/' . rawurlencode($profil->logo_path));
} elseif ($profil && !empty($profil->logo_url)) {
    $logo_src = $profil->logo_url;
}

$identity_rows = [
    'Kode PT' => $profil ? $profil->kode_pt : '',
    'Nomor SK PT' => $profil ? $profil->nomor_sk_pt : '',
    'Tanggal SK PT' => $profil && !empty($profil->tanggal_sk_pt) ? format_tanggal_indo($profil->tanggal_sk_pt) : '',
    'Tanggal Berdiri' => $profil && !empty($profil->tanggal_berdiri) ? format_tanggal_indo($profil->tanggal_berdiri) : '',
    'Jumlah Dosen' => $profil && $profil->jumlah_dosen !== NULL ? number_format((int) $profil->jumlah_dosen, 0, ',', '.') : '',
    'Jumlah Tendik' => $profil && $profil->jumlah_tendik !== NULL ? number_format((int) $profil->jumlah_tendik, 0, ',', '.') : '',
    'Status PT' => $profil ? $profil->status_pt : '',
    'Kode Pos' => $profil ? $profil->kode_pos : '',
    'Telepon' => $profil ? $profil->telepon : '',
    'Faksimile' => $profil ? $profil->faksimile : '',
    'Email' => $profil ? $profil->email : '',
];

$akreditasi_text = '';
if ($profil && !empty($profil->akreditasi)) {
    $akreditasi_text = $profil->akreditasi;
    if (!empty($profil->akreditasi_berlaku_sampai)) {
        $akreditasi_text .= ' - Berlaku sampai ' . format_tanggal_indo($profil->akreditasi_berlaku_sampai);
    }
}

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<style>
    .profil-hero {
        display: grid;
        grid-template-columns: 170px minmax(0, 1fr);
        gap: 20px;
        align-items: start;
    }

    .profil-logo {
        width: 150px;
        height: 150px;
        border-radius: 8px;
        border: 1px solid var(--ami-border);
        background: rgba(255, 255, 255, 0.04);
        object-fit: contain;
        padding: 12px;
    }

    .profil-title {
        margin: 0 0 6px;
        color: var(--ami-text);
        font-size: 22px;
        font-weight: 700;
        line-height: 1.25;
    }

    .profil-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .profil-field {
        min-height: 58px;
        border: 1px solid var(--ami-border);
        border-radius: 8px;
        padding: 10px 12px;
        background: rgba(255, 255, 255, 0.025);
    }

    .profil-field-value {
        color: var(--ami-text);
        font-weight: 700;
        line-height: 1.35;
        word-break: break-word;
    }

    .profil-chart-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .profil-chart-box {
        height: 280px;
        position: relative;
    }

    .profil-chart-box.profil-chart-single {
        max-width: 460px;
        margin: 24px auto 0;
    }

    .profil-mahasiswa-head {
        display: grid;
        grid-template-columns: minmax(210px, 280px) minmax(0, 1fr);
        gap: 18px;
        align-items: stretch;
    }

    .profil-total-box {
        min-height: 210px;
        border: 1px solid var(--ami-border);
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        background: rgba(77, 163, 255, 0.08);
    }

    .profil-total-icon {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e6f1fb;
        color: var(--ami-blue);
        margin-bottom: 12px;
        font-size: 22px;
    }

    .profil-total-number {
        font-size: 38px;
        line-height: 1;
        font-weight: 800;
        color: var(--ami-text);
    }

    .profil-breakdown {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    @media (max-width: 991.98px) {
        .profil-hero,
        .profil-mahasiswa-head,
        .profil-chart-grid {
            grid-template-columns: 1fr;
        }

        .profil-grid,
        .profil-breakdown {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .profil-grid,
        .profil-breakdown {
            grid-template-columns: 1fr;
        }

        .profil-logo {
            width: 118px;
            height: 118px;
        }
    }
</style>

<div class="ami-section-head mt-0">
    <h2 class="ami-section-title">Profil Lembaga</h2>
    <?php if ($can_manage): ?>
        <div class="ami-actions">
            <a href="<?php echo site_url('profil/edit'); ?>" class="btn btn-outline-ami btn-ami">
                <i class="fas fa-edit" aria-hidden="true"></i> Edit Manual
            </a>
            <?php if ($schema_ready): ?>
                <?php echo form_open('profil/sinkronisasi', ['class' => 'ami-actions mb-0']); ?>
                    <?php if ($nama_sinkron === ''): ?>
                        <input type="text" name="nama_pt_pddikti" class="form-control" required placeholder="Nama PT di PDDikti" style="min-width:260px;">
                    <?php else: ?>
                        <input type="hidden" name="nama_pt_pddikti" value="<?php echo html_escape($nama_sinkron); ?>">
                    <?php endif; ?>
                    <?php if ($profil && !empty($profil->id_pt_pddikti)): ?>
                        <input type="hidden" name="id_pt_pddikti" value="<?php echo html_escape($profil->id_pt_pddikti); ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary btn-ami" data-confirm-sync data-loading-text="Sinkronisasi...">
                        <i class="fas fa-sync-alt" aria-hidden="true"></i> Sinkronkan dari PDDikti
                    </button>
                <?php echo form_close(); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php if (!$schema_ready): ?>
    <div class="ami-panel">
        <div class="ami-empty">
            <div class="ami-empty-icon"><i class="fas fa-database" aria-hidden="true"></i></div>
            <div class="ami-empty-title">Tabel profil belum tersedia</div>
            <div>Jalankan migration <strong>008_create_profil_tables.sql</strong> sebelum menggunakan fitur profil.</div>
        </div>
    </div>
<?php elseif (!$profil): ?>
    <div class="ami-panel">
        <div class="ami-empty">
            <div class="ami-empty-icon"><i class="fas fa-university" aria-hidden="true"></i></div>
            <div class="ami-empty-title">Data profil belum diisi</div>
            <div>Silakan edit manual atau sinkronkan dari PDDikti.</div>
            <?php if ($can_manage): ?>
                <div class="ami-actions justify-content-center mt-3">
                    <a href="<?php echo site_url('profil/edit'); ?>" class="btn btn-outline-ami btn-ami">
                        <i class="fas fa-edit" aria-hidden="true"></i> Edit Manual
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="ami-panel mb-3">
        <div class="ami-panel-body">
            <div class="profil-hero">
                <div>
                    <img src="<?php echo html_escape($logo_src); ?>" alt="Logo universitas" class="profil-logo">
                </div>
                <div>
                    <h2 class="profil-title"><?php echo html_escape($profil->nama_pt ?: 'Nama universitas belum diisi'); ?></h2>
                    <?php if ($akreditasi_text !== ''): ?>
                        <span class="ami-status status-dinilai">
                            <i class="fas fa-award" aria-hidden="true"></i>
                            <?php echo html_escape($akreditasi_text); ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($profil->last_sync_at)): ?>
                        <div class="text-muted mt-2" style="font-size:12px;">
                            Terakhir disinkronkan: <?php echo html_escape(format_tanggal_indo($profil->last_sync_at)); ?>
                        </div>
                    <?php endif; ?>

                    <div class="profil-grid">
                        <?php foreach ($identity_rows as $label => $value): ?>
                            <div class="profil-field">
                                <div class="ami-stat-label"><?php echo html_escape($label); ?></div>
                                <div class="profil-field-value"><?php echo html_escape($value !== '' && $value !== NULL ? $value : '-'); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ami-panel mb-3">
        <div class="ami-panel-body">
            <h2 class="ami-section-title mb-3">Statistik Akreditasi Program Studi</h2>
            <?php if (!empty($akreditasi_summary)): ?>
                <div class="profil-chart-box">
                    <canvas id="akreditasiChart" aria-label="Statistik akreditasi program studi"></canvas>
                </div>
            <?php else: ?>
                <div class="ami-empty">
                    <div class="ami-empty-icon"><i class="fas fa-chart-pie" aria-hidden="true"></i></div>
                    <div class="ami-empty-title">Belum ada data akreditasi prodi</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="ami-panel mb-3">
        <div class="ami-panel-body">
            <h2 class="ami-section-title mb-3">Data Mahasiswa</h2>
            <div class="profil-mahasiswa-head">
                <div class="profil-total-box">
                    <div class="profil-total-icon"><i class="fas fa-user-graduate" aria-hidden="true"></i></div>
                    <div class="profil-total-number"><?php echo html_escape(number_format((int) $mahasiswa_total, 0, ',', '.')); ?></div>
                    <div class="ami-stat-label mt-2">Total Mahasiswa</div>
                </div>
                <div>
                    <?php if (!empty($mahasiswa_stats)): ?>
                        <div class="profil-breakdown">
                            <?php foreach ($mahasiswa_stats as $row): ?>
                                <div class="profil-field">
                                    <div class="ami-stat-label"><?php echo html_escape($row->jenjang ?: 'Lainnya'); ?></div>
                                    <div class="profil-field-value"><?php echo html_escape(number_format((int) $row->jumlah, 0, ',', '.')); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="ami-empty">
                            <div class="ami-empty-icon"><i class="fas fa-user-graduate" aria-hidden="true"></i></div>
                            <div class="ami-empty-title">Belum ada statistik mahasiswa</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($mahasiswa_stats)): ?>
                <div class="profil-chart-box profil-chart-single">
                    <canvas id="mahasiswaChart" aria-label="Distribusi mahasiswa per jenjang"></canvas>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="ami-panel">
        <div class="ami-panel-body">
            <h2 class="ami-section-title mb-3">Daftar Program Studi</h2>
            <div class="table-responsive">
                <table class="table ami-table">
                    <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Prodi</th>
                        <th>Nama Program Studi</th>
                        <th>Status</th>
                        <th>Jenjang</th>
                        <th>Akreditasi</th>
                        <th>Tanggal SK Akreditasi</th>
                        <th>Rasio Dosen/Mahasiswa</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($prodi)): ?>
                        <?php foreach ($prodi as $index => $row): ?>
                            <tr>
                                <td><?php echo (int) $index + 1; ?></td>
                                <td><?php echo html_escape($row->kode_prodi ?: '-'); ?></td>
                                <td><strong><?php echo html_escape($row->nama_prodi ?: '-'); ?></strong></td>
                                <td><?php echo html_escape($row->status ?: '-'); ?></td>
                                <td><?php echo html_escape($row->jenjang ?: '-'); ?></td>
                                <td><?php echo html_escape($row->akreditasi ?: '-'); ?></td>
                                <td><?php echo !empty($row->tanggal_sk_akreditasi) ? html_escape(format_tanggal_indo($row->tanggal_sk_akreditasi)) : '-'; ?></td>
                                <td><?php echo html_escape($row->rasio_dosen_mahasiswa ?: '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="ami-empty">
                                    <div class="ami-empty-icon"><i class="fas fa-list" aria-hidden="true"></i></div>
                                    <div class="ami-empty-title">Belum ada data program studi</div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
(function () {
    'use strict';
    document.querySelectorAll('[data-confirm-sync]').forEach(function (button) {
        button.addEventListener('click', function (event) {
            if (!window.confirm('Sinkronkan profil dari PDDikti sekarang?')) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });
})();
</script>

<?php if ($schema_ready && ($profil || !empty($akreditasi_summary) || !empty($mahasiswa_stats))): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    (function () {
        if (typeof Chart === 'undefined') return;

        var colors = ['#4da3ff', '#75c94b', '#f0ad4e', '#ff8aae', '#8f85ff', '#5fd6b1', '#c4a15a'];

        function makeDoughnut(id, labels, values) {
            var canvas = document.getElementById(id);
            if (!canvas || !values || values.length === 0) return;

            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderColor: 'rgba(0,0,0,0)',
                        borderWidth: 1
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
        }

        makeDoughnut('akreditasiChart', <?php echo $akreditasi_chart_labels; ?>, <?php echo $akreditasi_chart_values; ?>);
        makeDoughnut('mahasiswaChart', <?php echo $mahasiswa_chart_labels; ?>, <?php echo $mahasiswa_chart_values; ?>);
    })();
    </script>
<?php endif; ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
