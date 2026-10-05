<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$icon = static function ($name) {
    $paths = [
        'file-text' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/>',
        'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'sparkles' => '<path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'cycle' => '<path d="M21 12a9 9 0 0 0-15.3-6.4L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 15.3 6.4L21 16"/><path d="M21 21v-5h-5"/>',
        'layers' => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'search' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'x' => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        'eye' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'printer' => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
        'more-vertical' => '<circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/>',
        'file-spreadsheet' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M8 13h2"/><path d="M14 13h2"/><path d="M8 17h2"/><path d="M14 17h2"/>',
    ];
    return '<svg class="tw-h-4 tw-w-4 tw-flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['file-text']) . '</svg>';
};

$data = is_array($index_data) ? $index_data : [];
$filters = isset($data['filters']) && is_array($data['filters']) ? $data['filters'] : [];
$options = isset($data['options']) && is_array($data['options']) ? $data['options'] : [];
$summary = isset($data['summary']) ? $data['summary'] : (object) [];
$pagination = isset($data['pagination']) && is_array($data['pagination']) ? $data['pagination'] : ['page' => 1, 'per_page' => 20, 'page_count' => 1];
$reports = isset($data['reports']) && is_array($data['reports']) ? $data['reports'] : [];
$standard_analysis = isset($data['standard_analysis']) && is_array($data['standard_analysis']) ? $data['standard_analysis'] : [];
$result_count = isset($data['count']) ? (int) $data['count'] : 0;
$current_page = (int) $pagination['page'];
$page_count = max(1, (int) $pagination['page_count']);

$format_timestamp = static function ($value) {
    $timestamp = trim((string) $value);
    if ($timestamp === '' || strtotime($timestamp) === FALSE) return 'Belum tersedia';
    $months = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
    $time = strtotime($timestamp);
    return date('j', $time) . ' ' . $months[(int) date('n', $time)] . ' ' . date('Y, H:i', $time);
};

$filter_query = static function ($page = NULL) use ($filters) {
    $query = [];
    foreach (['academic_year', 'cycle_id', 'version_id', 'auditee_id', 'q'] as $key) {
        if (isset($filters[$key]) && $filters[$key] !== '' && $filters[$key] !== 0) $query[$key] = $filters[$key];
    }
    if ($page !== NULL && (int) $page > 1) $query['page'] = (int) $page;
    return $query;
};

$page_url = static function ($page) use ($filter_query) {
    $query = $filter_query($page);
    $url = site_url('lpmpi/spmi-reports');
    return $query ? $url . '?' . http_build_query($query) : $url;
};
?>

<main id="reports-root" data-ui-contract="ami-row-actions ami-action-btn" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-7xl">
        <header class="reports-header tw-mb-6">
            <p class="tw-mb-1.5 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-text-slate-500">Hasil Audit Mutu</p>
            <h1 class="tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950">Laporan SPMI</h1>
            <p class="tw-mt-1.5 tw-max-w-2xl tw-text-sm tw-text-slate-500">Kelola, tinjau, dan ekspor hasil audit mutu yang telah difinalisasi.</p>
        </header>

        <!-- Filter Area: Compact 2-row toolbar -->
        <section class="reports-filter tw-mb-6 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-4 tw-shadow-sm md:tw-p-5" aria-labelledby="report-filter-title">
            <div class="tw-mb-3.5 tw-flex tw-items-center tw-justify-between">
                <h2 id="report-filter-title" class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-font-bold tw-text-slate-900">
                    <?php echo $icon('search'); ?><span>Filter Laporan</span>
                </h2>
                <?php if (($filters['academic_year'] ?? '') === ''): ?>
                    <p class="tw-text-[11px] tw-text-slate-400 tw-hidden sm:tw-block">Semua tahun mencakup laporan historis yang belum memiliki snapshot tahun akademik.</p>
                <?php endif; ?>
            </div>
            <?php echo form_open('lpmpi/spmi-reports', ['method' => 'get', 'class' => 'tw-space-y-3']); ?>
                <!-- Row 1: Dropdown filters -->
                <div class="tw-grid tw-gap-3 sm:tw-grid-cols-2 lg:tw-grid-cols-4">
                    <label class="tw-block">
                        <span class="tw-mb-1 tw-flex tw-items-center tw-gap-1.5 tw-text-xs tw-font-semibold tw-text-slate-600">
                            <?php echo $icon('calendar'); ?><span>Tahun Akademik</span>
                        </span>
                        <select name="academic_year" class="tw-h-11 tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-slate-200">
                            <option value="">Semua tahun</option>
                            <?php foreach (($options['academic_years'] ?? []) as $year): ?>
                                <option value="<?php echo html_escape($year->academic_year_snapshot); ?>" <?php echo (string) ($filters['academic_year'] ?? '') === (string) $year->academic_year_snapshot ? 'selected' : ''; ?>>
                                    <?php echo html_escape($year->academic_year_snapshot); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="tw-block">
                        <span class="tw-mb-1 tw-flex tw-items-center tw-gap-1.5 tw-text-xs tw-font-semibold tw-text-slate-600">
                            <?php echo $icon('cycle'); ?><span>Siklus</span>
                        </span>
                        <select name="cycle_id" class="tw-h-11 tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-slate-200">
                            <option value="">Semua siklus</option>
                            <?php foreach (($options['cycles'] ?? []) as $cycle): ?>
                                <option value="<?php echo html_escape((string) (int) $cycle->source_cycle_id); ?>" <?php echo (int) ($filters['cycle_id'] ?? 0) === (int) $cycle->source_cycle_id ? 'selected' : ''; ?>>
                                    <?php echo html_escape($cycle->cycle_code_snapshot . ' — ' . $cycle->cycle_title_snapshot); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="tw-block">
                        <span class="tw-mb-1 tw-flex tw-items-center tw-gap-1.5 tw-text-xs tw-font-semibold tw-text-slate-600">
                            <?php echo $icon('layers'); ?><span>Versi SPMI</span>
                        </span>
                        <select name="version_id" class="tw-h-11 tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-slate-200">
                            <option value="">Semua versi</option>
                            <?php foreach (($options['versions'] ?? []) as $version): ?>
                                <option value="<?php echo html_escape((string) (int) $version->source_version_id); ?>" <?php echo (int) ($filters['version_id'] ?? 0) === (int) $version->source_version_id ? 'selected' : ''; ?>>
                                    <?php echo html_escape($version->source_version_code_snapshot . ' — ' . $version->source_version_title_snapshot); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="tw-block">
                        <span class="tw-mb-1 tw-flex tw-items-center tw-gap-1.5 tw-text-xs tw-font-semibold tw-text-slate-600">
                            <?php echo $icon('users'); ?><span>Auditee</span>
                        </span>
                        <select name="auditee_id" class="tw-h-11 tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-slate-200">
                            <option value="">Semua auditee</option>
                            <?php foreach (($options['auditees'] ?? []) as $auditee): ?>
                                <option value="<?php echo html_escape((string) (int) $auditee->auditee_id_snapshot); ?>" <?php echo (int) ($filters['auditee_id'] ?? 0) === (int) $auditee->auditee_id_snapshot ? 'selected' : ''; ?>>
                                    <?php echo html_escape($auditee->auditee_name_snapshot); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <!-- Row 2: Search input + Actions -->
                <div class="tw-flex tw-flex-col tw-gap-2.5 sm:tw-flex-row sm:tw-items-center">
                    <div class="tw-relative tw-flex-1">
                        <span class="tw-pointer-events-none tw-absolute tw-inset-y-0 tw-left-3 tw-flex tw-items-center tw-text-slate-400">
                            <?php echo $icon('search'); ?>
                        </span>
                        <input name="q" value="<?php echo html_escape((string) ($filters['q'] ?? '')); ?>" class="tw-h-11 tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-pl-9 tw-pr-3 tw-text-sm tw-text-slate-900 placeholder:tw-text-slate-400 focus:tw-border-slate-950 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-slate-200" type="search" placeholder="Cari nomor laporan, auditor, auditee, standar...">
                    </div>
                    <div class="tw-flex tw-items-center tw-gap-2">
                        <button class="btn-ami tw-button-primary tw-h-11 tw-px-5 tw-text-sm" type="submit">Tampilkan</button>
                        <a class="btn-ami tw-button-secondary tw-inline-flex tw-h-11 tw-items-center tw-gap-1.5 tw-px-4 tw-text-sm" href="<?php echo site_url('lpmpi/spmi-reports'); ?>">
                            <?php echo $icon('x'); ?><span>Reset</span>
                        </a>
                    </div>
                </div>
            <?php echo form_close(); ?>
            <?php if (($filters['academic_year'] ?? '') === ''): ?>
                <p class="tw-mt-2.5 tw-text-[11px] tw-text-slate-400 sm:tw-hidden">Semua tahun mencakup laporan historis yang belum memiliki snapshot tahun akademik.</p>
            <?php endif; ?>
        </section>

        <!-- Summary Snapshot Strip -->
        <section class="tw-mb-6" aria-labelledby="snapshot-summary-title">
            <div class="tw-mb-3 tw-flex tw-items-baseline tw-justify-between">
                <div>
                    <h2 id="snapshot-summary-title" class="tw-text-base tw-font-bold tw-text-slate-900">Ringkasan snapshot</h2>
                    <p class="tw-text-xs tw-text-slate-500">Data immutable dari laporan yang sesuai filter saat ini.</p>
                </div>
            </div>
            <div class="tw-grid tw-grid-cols-2 tw-gap-3 md:tw-grid-cols-2 lg:tw-grid-cols-4">
                <?php
                $cards = [
                    ['label' => 'Laporan', 'value' => (int) ($summary->report_count ?? 0), 'icon' => 'file-text'],
                    ['label' => 'Auditee', 'value' => (int) ($summary->auditee_count ?? 0), 'icon' => 'users'],
                    ['label' => 'Standar', 'value' => (int) ($summary->standard_count ?? 0), 'icon' => 'layers'],
                    ['label' => 'Rata-rata skor', 'value' => $summary->average_score !== NULL && $summary->average_score !== '' ? number_format((float) $summary->average_score, 2, ',', '.') : '—', 'icon' => 'sparkles'],
                ];
                foreach ($cards as $card): ?>
                    <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-4 tw-shadow-sm md:tw-p-5">
                        <div class="tw-flex tw-items-center tw-gap-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-slate-500">
                            <span class="reports-metric-icon"><?php echo $icon($card['icon']); ?></span>
                            <span><?php echo html_escape($card['label']); ?></span>
                        </div>
                        <div class="tw-mt-2 tw-text-2xl tw-font-bold tw-tracking-tight tw-text-slate-950">
                            <?php echo html_escape((string) $card['value']); ?>
                            <?php if ($card['label'] === 'Rata-rata skor' && $card['value'] !== '—'): ?>
                                <span class="tw-text-xs tw-font-normal tw-text-slate-400"> / 4</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Primary Content: Daftar Laporan -->
        <section class="tw-mb-8" aria-labelledby="report-list-title">
            <div class="tw-mb-3 tw-flex tw-flex-col tw-gap-1 sm:tw-flex-row sm:tw-items-end sm:tw-justify-between">
                <div>
                    <h2 id="report-list-title" class="tw-text-lg tw-font-bold tw-text-slate-900">Daftar laporan</h2>
                    <p class="tw-text-xs tw-text-slate-500"><?php echo html_escape((string) $result_count); ?> laporan ditemukan · Urutan terbaru berdasarkan waktu pembuatan snapshot.</p>
                </div>
                <p class="tw-text-xs tw-font-medium tw-text-slate-500" aria-live="polite">
                    Halaman <?php echo html_escape((string) $current_page); ?> dari <?php echo html_escape((string) $page_count); ?>
                </p>
            </div>

            <?php if ($result_count === 0): ?>
                <div class="tw-rounded-2xl tw-border tw-border-dashed tw-border-slate-300 tw-bg-white tw-p-10 tw-text-center md:tw-p-12">
                    <div class="tw-mx-auto tw-mb-3 tw-flex tw-h-12 tw-w-12 tw-items-center tw-justify-center tw-rounded-full tw-bg-slate-100 tw-text-slate-400">
                        <?php echo $icon('file-text'); ?>
                    </div>
                    <h3 class="tw-text-base tw-font-bold tw-text-slate-900">Belum ada laporan SPMI.</h3>
                    <p class="tw-mt-2 tw-text-sm tw-text-slate-500">Belum ada snapshot yang cocok dengan filter ini. Coba ubah filter atau tampilkan semua riwayat laporan.</p>
                </div>
            <?php else: ?>
                <!-- Desktop Table View -->
                <div class="tw-hidden tw-overflow-hidden tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-shadow-sm md:tw-block">
                    <div class="tw-overflow-x-auto">
                        <table class="tw-w-full tw-text-left tw-text-sm">
                            <thead class="tw-border-b tw-border-slate-200 tw-bg-slate-50 tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wider tw-text-slate-500">
                                <tr>
                                    <th class="tw-px-4 tw-py-3.5 tw-w-[28%]">Laporan</th>
                                    <th class="tw-px-4 tw-py-3.5 tw-w-[20%]">Auditee</th>
                                    <th class="tw-px-4 tw-py-3.5 tw-w-[18%]">Versi SPMI</th>
                                    <th class="tw-px-4 tw-py-3.5 tw-w-[16%]">Auditor</th>
                                    <th class="tw-px-4 tw-py-3.5 tw-w-[10%]">Finalisasi</th>
                                    <th class="tw-px-4 tw-py-3.5 tw-w-[8%] tw-text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="tw-divide-y tw-divide-slate-100">
                                <?php foreach ($reports as $report): ?>
                                    <tr class="hover:tw-bg-slate-50/80 tw-transition-colors">
                                        <td class="tw-px-4 tw-py-3.5">
                                            <span class="tw-block tw-font-mono tw-text-xs tw-font-bold tw-text-slate-900 tw-tracking-tight">
                                                <?php echo html_escape($report->report_number); ?>
                                            </span>
                                            <div class="tw-mt-1 tw-text-xs tw-text-slate-500">
                                                <?php echo html_escape((string) (int) $report->item_count); ?> indikator · <?php echo html_escape((string) (int) $report->standard_count); ?> standar
                                            </div>
                                        </td>
                                        <td class="tw-px-4 tw-py-3.5">
                                            <div class="tw-font-medium tw-text-slate-900"><?php echo html_escape($report->auditee_name_snapshot); ?></div>
                                        </td>
                                        <td class="tw-px-4 tw-py-3.5">
                                            <div class="tw-font-medium tw-text-slate-900"><?php echo html_escape($report->source_version_code_snapshot); ?></div>
                                            <div class="tw-mt-0.5 tw-text-xs tw-text-slate-500 tw-line-clamp-1"><?php echo html_escape($report->source_version_title_snapshot); ?></div>
                                        </td>
                                        <td class="tw-px-4 tw-py-3.5">
                                            <div class="tw-font-medium tw-text-slate-800"><?php echo html_escape($report->auditor_name_snapshot); ?></div>
                                        </td>
                                        <td class="tw-whitespace-nowrap tw-px-4 tw-py-3.5 tw-text-xs tw-text-slate-500">
                                            <?php echo html_escape($format_timestamp($report->assessment_finalized_at_snapshot)); ?>
                                        </td>
                                        <td class="tw-px-4 tw-py-3.5 tw-text-right">
                                            <div class="ami-row-actions tw-relative tw-inline-flex tw-items-center tw-justify-end tw-gap-1.5">
                                                <a class="ami-action-btn tw-button-primary tw-h-9 tw-px-3 tw-text-xs tw-inline-flex tw-items-center tw-gap-1.5" href="<?php echo site_url('lpmpi/spmi-reports/detail/' . (int) $report->id); ?>" title="Detail Laporan">
                                                    <?php echo $icon('eye'); ?>
                                                    <span>Detail</span>
                                                </a>
                                                <div class="reports-action-dropdown tw-relative">
                                                    <button type="button" class="reports-dropdown-toggle ami-action-btn tw-button-secondary tw-h-9 tw-w-9 tw-p-0 tw-inline-flex tw-items-center tw-justify-center" aria-haspopup="true" aria-expanded="false" aria-label="Menu opsi laporan <?php echo html_escape($report->report_number); ?>" title="Opsi Lainnya">
                                                        <?php echo $icon('more-vertical'); ?>
                                                    </button>
                                                    <div class="reports-dropdown-menu tw-hidden tw-absolute tw-right-0 tw-z-30 tw-mt-1 tw-w-44 tw-rounded-xl tw-border tw-border-slate-200 tw-bg-white tw-py-1 tw-shadow-lg" role="menu">
                                                        <a class="reports-dropdown-item tw-flex tw-w-full tw-items-center tw-gap-2.5 tw-px-3.5 tw-py-2 tw-text-xs tw-font-medium tw-text-slate-700 hover:tw-bg-slate-50 hover:tw-text-slate-900" href="<?php echo site_url('lpmpi/spmi-reports/print/' . (int) $report->id); ?>" target="_blank" rel="noopener" role="menuitem">
                                                            <?php echo $icon('printer'); ?>
                                                            <span>Print laporan</span>
                                                        </a>
                                                        <a class="reports-dropdown-item tw-flex tw-w-full tw-items-center tw-gap-2.5 tw-px-3.5 tw-py-2 tw-text-xs tw-font-medium tw-text-slate-700 hover:tw-bg-slate-50 hover:tw-text-slate-900" href="<?php echo site_url('lpmpi/spmi-reports/export/' . (int) $report->id); ?>" role="menuitem">
                                                            <?php echo $icon('file-spreadsheet'); ?>
                                                            <span>Export XLSX</span>
                                                        </a>
                                                    </div>
                                                </div>
                                                <span class="tw-sr-only">
                                                    <a href="<?php echo site_url('lpmpi/spmi-reports/detail/' . (int) $report->id); ?>">Detail</a>
                                                    <a href="<?php echo site_url('lpmpi/spmi-reports/print/' . (int) $report->id); ?>">Print</a>
                                                    <a href="<?php echo site_url('lpmpi/spmi-reports/export/' . (int) $report->id); ?>">XLSX</a>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Cards View -->
                <div class="tw-grid tw-gap-3 md:tw-hidden">
                    <?php foreach ($reports as $report): ?>
                        <article class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-4 tw-shadow-sm">
                            <div class="tw-flex tw-items-start tw-justify-between tw-gap-2">
                                <div>
                                    <span class="tw-block tw-font-mono tw-text-xs tw-font-bold tw-text-slate-900 tw-tracking-tight tw-break-all">
                                        <?php echo html_escape($report->report_number); ?>
                                    </span>
                                    <div class="tw-mt-1 tw-text-[11px] tw-text-slate-500">
                                        <?php echo html_escape((string) (int) $report->item_count); ?> indikator · <?php echo html_escape((string) (int) $report->standard_count); ?> standar
                                    </div>
                                </div>
                                <span class="tw-whitespace-nowrap tw-text-right tw-text-[11px] tw-text-slate-400">
                                    <?php echo html_escape($format_timestamp($report->assessment_finalized_at_snapshot)); ?>
                                </span>
                            </div>

                            <dl class="tw-mt-3 tw-grid tw-grid-cols-2 tw-gap-2 tw-border-t tw-border-slate-100 tw-pt-3 tw-text-xs">
                                <div>
                                    <dt class="tw-font-bold tw-uppercase tw-tracking-wide tw-text-slate-400 tw-text-[10px]">Auditee</dt>
                                    <dd class="tw-mt-0.5 tw-font-medium tw-text-slate-900"><?php echo html_escape($report->auditee_name_snapshot); ?></dd>
                                </div>
                                <div>
                                    <dt class="tw-font-bold tw-uppercase tw-tracking-wide tw-text-slate-400 tw-text-[10px]">Auditor</dt>
                                    <dd class="tw-mt-0.5 tw-font-medium tw-text-slate-800"><?php echo html_escape($report->auditor_name_snapshot); ?></dd>
                                </div>
                                <div class="tw-col-span-2">
                                    <dt class="tw-font-bold tw-uppercase tw-tracking-wide tw-text-slate-400 tw-text-[10px]">Versi SPMI</dt>
                                    <dd class="tw-mt-0.5 tw-text-slate-700"><?php echo html_escape($report->source_version_code_snapshot . ' — ' . $report->source_version_title_snapshot); ?></dd>
                                </div>
                            </dl>

                            <div class="tw-mt-3.5 tw-border-t tw-border-slate-100 tw-pt-3">
                                <div class="ami-row-actions tw-flex tw-items-center tw-gap-2">
                                    <a class="ami-action-btn tw-button-primary tw-min-h-[44px] tw-flex-1 tw-justify-center tw-text-xs tw-inline-flex tw-items-center tw-gap-1.5" href="<?php echo site_url('lpmpi/spmi-reports/detail/' . (int) $report->id); ?>">
                                        <?php echo $icon('eye'); ?>
                                        <span>Detail</span>
                                    </a>
                                    <div class="reports-action-dropdown tw-relative">
                                        <button type="button" class="reports-dropdown-toggle ami-action-btn tw-button-secondary tw-min-h-[44px] tw-min-w-[44px] tw-p-0 tw-inline-flex tw-items-center tw-justify-center" aria-haspopup="true" aria-expanded="false" aria-label="Menu opsi laporan <?php echo html_escape($report->report_number); ?>" title="Opsi Lainnya">
                                            <?php echo $icon('more-vertical'); ?>
                                        </button>
                                        <div class="reports-dropdown-menu tw-hidden tw-absolute tw-right-0 tw-z-30 tw-mt-1 tw-w-44 tw-rounded-xl tw-border tw-border-slate-200 tw-bg-white tw-py-1 tw-shadow-lg" role="menu">
                                            <a class="reports-dropdown-item tw-flex tw-w-full tw-items-center tw-gap-2.5 tw-px-3.5 tw-py-2.5 tw-text-xs tw-font-medium tw-text-slate-700 hover:tw-bg-slate-50 hover:tw-text-slate-900" href="<?php echo site_url('lpmpi/spmi-reports/print/' . (int) $report->id); ?>" target="_blank" rel="noopener" role="menuitem">
                                                <?php echo $icon('printer'); ?>
                                                <span>Print laporan</span>
                                            </a>
                                            <a class="reports-dropdown-item tw-flex tw-w-full tw-items-center tw-gap-2.5 tw-px-3.5 tw-py-2.5 tw-text-xs tw-font-medium tw-text-slate-700 hover:tw-bg-slate-50 hover:tw-text-slate-900" href="<?php echo site_url('lpmpi/spmi-reports/export/' . (int) $report->id); ?>" role="menuitem">
                                                <?php echo $icon('file-spreadsheet'); ?>
                                                <span>Export XLSX</span>
                                            </a>
                                        </div>
                                    </div>
                                    <span class="tw-sr-only">
                                        <a href="<?php echo site_url('lpmpi/spmi-reports/detail/' . (int) $report->id); ?>">Detail</a>
                                        <a href="<?php echo site_url('lpmpi/spmi-reports/print/' . (int) $report->id); ?>">Print</a>
                                        <a href="<?php echo site_url('lpmpi/spmi-reports/export/' . (int) $report->id); ?>">XLSX</a>
                                    </span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- Accessible Pagination -->
                <?php if ($page_count > 1): ?>
                    <nav class="tw-mt-5 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3" aria-label="Navigasi halaman laporan">
                        <div>
                            <?php if ($current_page > 1): ?>
                                <a class="ami-action-btn tw-button-secondary tw-inline-flex tw-min-h-[40px] tw-items-center tw-gap-2 tw-text-xs" rel="prev" href="<?php echo html_escape($page_url($current_page - 1)); ?>">
                                    <?php echo $icon('arrow-left'); ?><span>Sebelumnya</span>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-1" aria-label="Halaman laporan">
                            <?php for ($page = 1; $page <= $page_count; $page++): ?>
                                <a class="ami-action-btn tw-inline-flex tw-min-h-[40px] tw-min-w-[40px] tw-items-center tw-justify-center tw-rounded-lg tw-text-xs <?php echo $page === $current_page ? 'tw-button-primary' : 'tw-button-secondary'; ?>" href="<?php echo html_escape($page_url($page)); ?>" <?php echo $page === $current_page ? 'aria-current="page"' : ''; ?>>
                                    <?php echo html_escape((string) $page); ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                        <div>
                            <?php if ($current_page < $page_count): ?>
                                <a class="ami-action-btn tw-button-secondary tw-inline-flex tw-min-h-[40px] tw-items-center tw-gap-2 tw-text-xs" rel="next" href="<?php echo html_escape($page_url($current_page + 1)); ?>">
                                    <span>Berikutnya</span><?php echo $icon('arrow-right'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </section>

        <!-- Pending Finalized Assessment Section -->
        <?php if (!empty($finalized_assessments)): ?>
            <section class="reports-pending tw-mb-8 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-5 tw-shadow-sm" aria-labelledby="pending-assessments-title">
                <div class="tw-mb-4 tw-flex tw-flex-col tw-gap-3 sm:tw-flex-row sm:tw-items-start sm:tw-justify-between">
                    <div>
                        <div class="tw-flex tw-items-center tw-gap-2">
                            <h2 id="pending-assessments-title" class="tw-text-base tw-font-bold tw-text-slate-900">Laporan siap diterbitkan</h2>
                            <span class="tw-inline-flex tw-items-center tw-rounded-full tw-border tw-border-amber-200 tw-bg-amber-50 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-semibold tw-text-amber-800">
                                <?php echo html_escape((string) count($finalized_assessments)); ?> Menunggu
                            </span>
                        </div>
                        <p class="tw-mt-1 tw-text-xs tw-text-slate-500">Hasil penilaian telah final dan siap dibuatkan satu dokumen snapshot laporan resmi.</p>
                    </div>
                </div>

                <!-- Desktop Pending Table -->
                <div class="tw-hidden tw-overflow-hidden tw-rounded-xl tw-border tw-border-slate-200 md:tw-block">
                    <div class="tw-overflow-x-auto">
                        <table class="tw-w-full tw-text-left tw-text-sm">
                            <thead class="tw-border-b tw-border-slate-200 tw-bg-slate-50 tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wider tw-text-slate-500">
                                <tr>
                                    <th class="tw-px-4 tw-py-3">Siklus</th>
                                    <th class="tw-px-4 tw-py-3">Versi / Standar</th>
                                    <th class="tw-px-4 tw-py-3">Auditee</th>
                                    <th class="tw-px-4 tw-py-3">Finalisasi</th>
                                    <th class="tw-px-4 tw-py-3 tw-text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="tw-divide-y tw-divide-slate-100">
                                <?php foreach ($finalized_assessments as $assessment): ?>
                                    <tr class="hover:tw-bg-slate-50">
                                        <td class="tw-px-4 tw-py-3 tw-font-medium tw-text-slate-900">
                                            <?php echo html_escape($assessment->cycle_code . ' — ' . $assessment->cycle_title); ?>
                                        </td>
                                        <td class="tw-px-4 tw-py-3 tw-text-slate-700">
                                            <div class="tw-font-medium"><?php echo html_escape($assessment->source_version_code . ' — ' . $assessment->source_version_title); ?></div>
                                            <div class="tw-mt-0.5 tw-text-xs tw-text-slate-500"><?php echo html_escape((string) (int) $assessment->standard_count); ?> standar</div>
                                        </td>
                                        <td class="tw-px-4 tw-py-3 tw-text-slate-700"><?php echo html_escape($assessment->auditee_name); ?></td>
                                        <td class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-text-xs tw-text-slate-500"><?php echo html_escape($format_timestamp($assessment->finalized_at)); ?></td>
                                        <td class="tw-px-4 tw-py-3 tw-text-right">
                                            <div class="ami-row-actions tw-inline-flex tw-justify-end">
                                                <?php echo form_open('lpmpi/spmi-reports/assessment/create/' . (int) $assessment->anchor_assessment_id, ['class' => 'tw-m-0']); ?>
                                                    <button class="btn-ami ami-action-btn tw-button-primary tw-min-h-[38px] tw-text-xs" type="submit">
                                                        <?php echo $icon('sparkles'); ?><span>Generate laporan</span>
                                                    </button>
                                                <?php echo form_close(); ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Pending Cards -->
                <div class="tw-grid tw-gap-3 md:tw-hidden">
                    <?php foreach ($finalized_assessments as $assessment): ?>
                        <article class="tw-rounded-xl tw-border tw-border-slate-200 tw-bg-slate-50/50 tw-p-4">
                            <div class="tw-text-xs tw-font-semibold tw-text-slate-500">Siklus</div>
                            <div class="tw-mt-0.5 tw-text-sm tw-font-bold tw-text-slate-900"><?php echo html_escape($assessment->cycle_code . ' — ' . $assessment->cycle_title); ?></div>
                            <div class="tw-mt-2 tw-text-xs tw-text-slate-700"><?php echo html_escape($assessment->source_version_code . ' — ' . $assessment->source_version_title); ?> · <?php echo html_escape((string) (int) $assessment->standard_count); ?> standar</div>
                            <div class="tw-mt-1 tw-text-xs tw-text-slate-600"><?php echo html_escape($assessment->auditee_name); ?></div>
                            <div class="tw-mt-1 tw-text-xs tw-text-slate-500"><?php echo html_escape($format_timestamp($assessment->finalized_at)); ?></div>
                            <div class="tw-mt-3 tw-border-t tw-border-slate-200 tw-pt-3">
                                <div class="ami-row-actions">
                                    <div class="tw-w-full">
                                        <?php echo form_open('lpmpi/spmi-reports/assessment/create/' . (int) $assessment->anchor_assessment_id, ['class' => 'tw-w-full']); ?>
                                            <button class="btn-ami ami-action-btn tw-button-primary tw-min-h-[44px] tw-w-full tw-text-sm" type="submit">
                                                <?php echo $icon('sparkles'); ?><span>Generate laporan</span>
                                            </button>
                                        <?php echo form_close(); ?>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Analisis per Standar Section -->
        <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-5 tw-shadow-sm md:tw-p-6" aria-labelledby="standard-analysis-title">
            <div class="tw-mb-4">
                <h2 id="standard-analysis-title" class="tw-text-base tw-font-bold tw-text-slate-900">Analisis per standar</h2>
                <p class="tw-mt-1 tw-text-xs tw-text-slate-500">Rata-rata skor dan temuan dari snapshot laporan yang sesuai filter saat ini.</p>
            </div>
            <?php if (empty($standard_analysis)): ?>
                <div class="tw-rounded-xl tw-border tw-border-dashed tw-border-slate-300 tw-p-6 tw-text-center tw-text-sm tw-text-slate-500">
                    Belum ada data analisis standar pada snapshot dengan filter ini.
                </div>
            <?php else: ?>
                <div class="tw-grid tw-gap-3.5 lg:tw-grid-cols-2">
                    <?php foreach ($standard_analysis as $analysis):
                        $score = max(0, min(4, (float) $analysis->average_score));
                        $width = ($score / 4) * 100;
                    ?>
                        <article class="tw-rounded-xl tw-border tw-border-slate-200 tw-p-4 tw-transition-colors hover:tw-border-slate-300">
                            <div class="tw-flex tw-items-start tw-justify-between tw-gap-3">
                                <div>
                                    <h3 class="tw-text-sm tw-font-bold tw-text-slate-900"><?php echo html_escape($analysis->standard_code); ?></h3>
                                    <p class="tw-mt-0.5 tw-text-xs tw-text-slate-500 tw-line-clamp-2"><?php echo html_escape($analysis->standard_title); ?></p>
                                </div>
                                <span class="tw-whitespace-nowrap tw-font-mono tw-text-sm tw-font-bold tw-text-slate-900">
                                    <?php echo html_escape(number_format($score, 2, ',', '.')); ?> <span class="tw-text-xs tw-font-normal tw-text-slate-400">/ 4</span>
                                </span>
                            </div>
                            <div class="tw-mt-3" role="img" aria-label="Skor rata-rata <?php echo html_escape(number_format($score, 2, ',', '.')); ?> dari 4 untuk <?php echo html_escape($analysis->standard_code); ?>">
                                <div class="tw-h-2 tw-overflow-hidden tw-rounded-full tw-bg-slate-100">
                                    <div class="tw-h-full tw-rounded-full tw-bg-slate-700" style="width: <?php echo html_escape((string) $width); ?>%;"></div>
                                </div>
                            </div>
                            <div class="tw-mt-3 tw-flex tw-flex-wrap tw-gap-x-4 tw-gap-y-1 tw-text-xs tw-text-slate-500">
                                <span><?php echo html_escape((string) (int) $analysis->indicator_count); ?> indikator</span>
                                <span><?php echo html_escape((string) (int) $analysis->finding_count); ?> temuan</span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<script>
(function () {
    var root = document.getElementById('reports-root');
    if (!root) return;

    function closeAllDropdowns() {
        var openMenus = root.querySelectorAll('.reports-dropdown-menu:not(.tw-hidden)');
        for (var i = 0; i < openMenus.length; i++) {
            var menu = openMenus[i];
            menu.classList.add('tw-hidden');
            var container = menu.closest('.reports-action-dropdown');
            if (container) {
                container.classList.remove('reports-dropup');
                var toggle = container.querySelector('.reports-dropdown-toggle');
                if (toggle) toggle.setAttribute('aria-expanded', 'false');
            }
        }
    }

    root.addEventListener('click', function (event) {
        var toggle = event.target.closest('.reports-dropdown-toggle');
        if (toggle && root.contains(toggle)) {
            event.preventDefault();
            event.stopPropagation();
            var container = toggle.closest('.reports-action-dropdown');
            var menu = container ? container.querySelector('.reports-dropdown-menu') : null;
            if (!menu) return;

            var isHidden = menu.classList.contains('tw-hidden');
            closeAllDropdowns();

            if (isHidden) {
                var rect = toggle.getBoundingClientRect();
                var menuHeight = 90;
                var spaceBelow = window.innerHeight - rect.bottom;
                if (spaceBelow < menuHeight && rect.top > menuHeight) {
                    container.classList.add('reports-dropup');
                } else {
                    container.classList.remove('reports-dropup');
                }
                menu.classList.remove('tw-hidden');
                toggle.setAttribute('aria-expanded', 'true');
            }
            return;
        }

        if (event.target.closest('.reports-dropdown-item')) {
            closeAllDropdowns();
            return;
        }

        closeAllDropdowns();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' || event.key === 'Esc') {
            closeAllDropdowns();
        }
    });
})();
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
