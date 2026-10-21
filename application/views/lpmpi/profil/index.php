<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$profil = isset($profil) ? $profil : NULL;
$prodi = isset($prodi) && is_array($prodi) ? $prodi : [];
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

$deskripsi_lembaga = $profil && property_exists($profil, 'deskripsi') ? trim((string) $profil->deskripsi) : '';

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

    .profil-prodi-table-wrap {
        overflow-x: visible;
    }

    .profil-prodi-table {
        width: 100%;
        min-width: 0;
        margin-bottom: 0;
        table-layout: fixed;
    }

    .profil-prodi-table th {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .profil-prodi-table th,
    .profil-prodi-table td {
        overflow-wrap: anywhere;
        white-space: normal;
        vertical-align: middle;
    }

    .profil-prodi-table th:nth-child(1),
    .profil-prodi-table td:nth-child(1) {
        width: 20%;
    }

    .profil-prodi-table th:nth-child(2),
    .profil-prodi-table td:nth-child(2) {
        width: 60%;
    }

    .profil-prodi-table th:nth-child(3),
    .profil-prodi-table td:nth-child(3) {
        width: 20%;
    }

    .profil-prodi-code {
        color: var(--ami-muted);
        font-family: monospace;
        font-size: 12px;
    }

    .profil-prodi-name {
        color: var(--ami-text);
        font-weight: 700;
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

        .ami-section-head > .ami-actions {
            align-items: stretch;
            flex-direction: column;
            gap: 8px;
        }

        .ami-section-head > .ami-actions > a,
        .ami-section-head > .ami-actions > form,
        .ami-section-head > .ami-actions > form .btn,
        .ami-section-head > .ami-actions > form .form-control {
            width: 100%;
        }

        .ami-section-head > .ami-actions > form {
            align-items: stretch;
            flex-direction: column;
            gap: 8px;
        }

        .ami-section-head > .ami-actions > form .form-control {
            min-width: 0 !important;
        }
    }
</style>

<div class="ami-section-head mt-0">
    <h2 class="ami-section-title">Profil Lembaga</h2>
    <?php if ($can_manage): ?>
        <div class="ami-actions">
            <a href="<?php echo site_url('profil/edit'); ?>" class="btn-outline-ami btn-ami">
                <i class="fas fa-edit" aria-hidden="true"></i> Edit Manual
            </a>
            <a href="<?php echo html_escape(site_url('profil/mahasiswa/create')); ?>" class="btn-outline-ami btn-ami">
                <i class="fas fa-plus" aria-hidden="true"></i> Tambah Statistik Mahasiswa
            </a>
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
                <div class="ami-actions tw-justify-center mt-3">
                    <a href="<?php echo site_url('profil/edit'); ?>" class="btn-outline-ami btn-ami">
                        <i class="fas fa-edit" aria-hidden="true"></i> Edit Manual
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="ami-panel">
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
                        <div class="ami-muted mt-2" style="font-size:12px;">
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

    <!-- Tentang Lembaga Section -->
    <div class="tw-mb-4 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-5 sm:tw-p-6 tw-shadow-sm">
        <div class="tw-flex tw-items-center tw-justify-between tw-pb-3.5 tw-border-b tw-border-slate-100 tw-mb-4">
            <div class="tw-flex tw-items-center tw-gap-2.5">
                <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-8 tw-h-8 tw-rounded-lg tw-bg-blue-50 tw-text-blue-700">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 16v-4"/>
                        <path d="M12 8h.01"/>
                    </svg>
                </span>
                <div>
                    <h2 class="tw-text-base tw-font-bold tw-text-slate-900 tw-m-0">Tentang Lembaga</h2>
                    <p class="tw-text-xs tw-text-slate-500 tw-m-0 tw-mt-0.5">Ringkasan profil institusi, visi, misi, atau gambaran umum perguruan tinggi.</p>
                </div>
            </div>
            <?php if ($can_manage): ?>
                <a href="<?php echo html_escape(site_url('lpmpi/system-settings')); ?>" class="tw-hidden sm:tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-rounded-lg tw-border tw-border-slate-200 tw-bg-slate-50 hover:tw-bg-slate-100 tw-text-xs tw-font-semibold tw-text-slate-700 hover:tw-text-slate-900 tw-transition-colors">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                        <path d="m15 5 4 4"/>
                    </svg>
                    <span>Ubah di Pengaturan</span>
                </a>
            <?php endif; ?>
        </div>

        <?php if ($deskripsi_lembaga !== ''): ?>
            <div class="tw-text-sm tw-text-slate-700 tw-leading-relaxed tw-whitespace-pre-line tw-break-words">
                <?php echo html_escape($deskripsi_lembaga); ?>
            </div>
        <?php else: ?>
            <div class="tw-flex tw-flex-col sm:tw-flex-row tw-items-center tw-gap-4 tw-p-4 sm:tw-p-5 tw-rounded-xl tw-border tw-border-dashed tw-border-slate-200 tw-bg-slate-50/60 tw-text-center sm:tw-text-left">
                <div class="tw-w-10 tw-h-10 tw-rounded-full tw-bg-slate-100 tw-text-slate-400 tw-flex tw-items-center tw-justify-center tw-shrink-0">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="tw-flex-1">
                    <div class="tw-text-sm tw-font-semibold tw-text-slate-800">Belum ada deskripsi profil lembaga</div>
                    <div class="tw-text-xs tw-text-slate-500 tw-mt-0.5">Deskripsi atau ringkasan institusi belum ditambahkan pada pengaturan sistem.</div>
                </div>
                <?php if ($can_manage): ?>
                    <a href="<?php echo html_escape(site_url('lpmpi/system-settings')); ?>" class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3.5 tw-py-2 tw-rounded-lg tw-bg-blue-600 hover:tw-bg-blue-700 tw-text-white tw-text-xs tw-font-semibold tw-shadow-sm tw-transition-colors">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14"/>
                            <path d="M12 5v14"/>
                        </svg>
                        <span>Tambah Deskripsi</span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="ami-panel">
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

    <div class="ami-panel">
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
                                    <?php if ($can_manage): ?>
                                        <div class="mt-2 tw-flex tw-flex-wrap tw-items-center" style="gap: 6px;">
                                            <a href="<?php echo html_escape(site_url('profil/mahasiswa/edit/' . (int) $row->id)); ?>" class="tw-text-xs btn-outline-ami">Edit</a>
                                            <?php echo form_open('profil/mahasiswa/delete/' . (int) $row->id, ['class' => 'd-inline']); ?>
                                                <button type="submit" class="tw-text-xs ami-btn-outline-danger" onclick="return window.confirm('Hapus statistik mahasiswa ini?');">Hapus</button>
                                            <?php echo form_close(); ?>
                                        </div>
                                    <?php endif; ?>
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

<?php endif; ?>

<?php if ($schema_ready): ?>
    <div class="ami-panel">
        <div class="ami-panel-body">
            <div class="ami-section-head mb-3">
                <div>
                    <h2 class="ami-section-title mb-1">Daftar Program Studi</h2>
                    <p class="ami-muted mb-0">Informasi program studi yang terdaftar pada profil lembaga.</p>
                </div>
                <?php if ($can_manage): ?>
                    <div class="ami-actions">
                        <a href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf')); ?>" class="btn-outline-ami btn-ami">Kelola di Master Data</a>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($prodi)): ?>
                <div class="ami-table-wrap table-responsive profil-prodi-table-wrap">
                    <table class="ami-table profil-prodi-table">
                        <caption class="sr-only">Daftar program studi berdasarkan kode, nama, dan jenjang</caption>
                        <thead>
                            <tr>
                                <th scope="col">Kode</th>
                                <th scope="col">Nama Program Studi</th>
                                <th scope="col">Jenjang</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prodi as $row): ?>
                                <tr>
                                    <td class="profil-prodi-code"><?php echo html_escape((string) ($row->kode_prodi ?? '-')); ?></td>
                                    <td class="profil-prodi-name"><?php echo html_escape((string) ($row->nama_prodi ?? '-')); ?></td>
                                    <td><?php echo html_escape((string) ($row->jenjang ?? '-')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="ami-empty">
                    <div class="ami-empty-icon"><i class="fas fa-graduation-cap" aria-hidden="true"></i></div>
                    <div class="ami-empty-title">Belum ada data program studi</div>
                    <div>Daftar program studi akan tampil setelah data tersedia.</div>
                </div>
            <?php endif; ?>
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
