<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

function spmi_report_value($value) {
    $value = trim((string) $value);
    return $value === '' ? '-' : $value;
}

function spmi_report_format_bytes($bytes) {
    $bytes = (int) $bytes;
    if ($bytes <= 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = (int) floor(log($bytes, 1024));
    $i = min($i, count($units) - 1);
    return round($bytes / pow(1024, $i), 1) . ' ' . $units[$i];
}

function spmi_report_auditor_evidence($value) {
    $items = json_decode((string) $value, TRUE);
    if (!is_array($items)) return [];
    $valid = [];
    foreach ($items as $item) {
        if (!is_array($item)) continue;
        $valid[] = [
            'original_name' => isset($item['original_name']) ? (string) $item['original_name'] : '-',
            'mime_type' => isset($item['mime_type']) ? (string) $item['mime_type'] : '-',
            'size_bytes' => isset($item['size_bytes']) ? (int) $item['size_bytes'] : 0,
            'sha256' => isset($item['sha256']) ? (string) $item['sha256'] : '-',
        ];
    }
    return $valid;
}

// Lucide icon helper
$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'file-spreadsheet' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h2"/><path d="M8 17h2"/><path d="M14 13h2"/><path d="M14 17h2"/>',
        'printer' => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
        'shield-check' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        'external-link' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>',
        'file' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/>',
        'paperclip' => '<path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/>',
    ];
    return '<svg class="reports-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['info']) . '</svg>';
};
?>

<main id="reports-root" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-7xl">
        <!-- Top Back Action -->
        <div class="ami-row-actions no-print tw-mb-5">
            <a class="ami-action-btn reports-back-link tw-text-sm" href="<?php echo site_url('lpmpi/spmi-reports'); ?>">
                <?php echo $icon('arrow-left'); ?>
                <span>Kembali ke Laporan SPMI</span>
            </a>
        </div>

        <!-- Header Card: Info & Action Buttons -->
        <header class="tw-mb-6 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm">
            <div class="tw-flex tw-flex-col tw-gap-4 sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-pb-6 tw-border-b tw-border-slate-100">
                <div>
                    <div class="tw-flex tw-items-center tw-gap-2.5 tw-mb-2">
                        <span class="tw-inline-flex tw-items-center tw-gap-1 tw-rounded-full tw-bg-emerald-50 tw-border tw-border-emerald-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-emerald-800">
                            <?php echo $icon('shield-check'); ?>
                            <span>Laporan Resmi</span>
                        </span>
                        <span class="tw-text-xs tw-text-slate-500">M10 / M17 Final Snapshot</span>
                    </div>
                    <h1 class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950 tw-m-0">
                        <?php echo html_escape($report->report_number); ?>
                    </h1>
                </div>

                <div class="ami-row-actions no-print tw-flex tw-flex-wrap tw-items-center tw-gap-2.5">
                    <a class="ami-action-btn tw-button-secondary" href="<?php echo site_url('lpmpi/spmi-reports/export/' . (int) $report->id); ?>">
                        <?php echo $icon('file-spreadsheet'); ?>
                        <span>Export XLSX</span>
                    </a>
                    <a class="ami-action-btn tw-button-primary" href="<?php echo site_url('lpmpi/spmi-reports/print/' . (int) $report->id); ?>">
                        <?php echo $icon('printer'); ?>
                        <span>Print / Save as PDF</span>
                    </a>
                </div>
            </div>

            <!-- Metadata Grid -->
            <div class="tw-mt-6 tw-grid tw-gap-4 sm:tw-grid-cols-2 lg:tw-grid-cols-3 tw-text-sm">
                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100">
                    <span class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Siklus Audit</span>
                    <strong class="tw-text-slate-900 tw-font-medium">
                        <?php echo html_escape($report->cycle_code_snapshot . ' — ' . $report->cycle_title_snapshot); ?>
                    </strong>
                </div>

                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100">
                    <span class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Periode Pelaksanaan</span>
                    <span class="tw-text-slate-800">
                        <?php echo html_escape($report->cycle_start_date_snapshot . ' — ' . $report->cycle_end_date_snapshot); ?>
                    </span>
                </div>

                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100">
                    <span class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Sumber Dokumen</span>
                    <span class="tw-text-slate-800 tw-font-mono tw-text-xs">
                        <?php echo html_escape($report->source_version_code_snapshot); ?>
                    </span>
                </div>

                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100">
                    <span class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Auditor</span>
                    <strong class="tw-text-slate-900 tw-font-medium">
                        <?php echo html_escape($report->auditor_name_snapshot); ?>
                    </strong>
                </div>

                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100">
                    <span class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Auditee</span>
                    <strong class="tw-text-slate-900 tw-font-medium">
                        <?php echo html_escape($report->auditee_name_snapshot); ?>
                    </strong>
                </div>

                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100">
                    <span class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Finalisasi M9</span>
                    <span class="tw-text-slate-800">
                        <?php echo html_escape($report->assessment_finalized_at_snapshot); ?>
                    </span>
                </div>
            </div>

            <!-- Subtle Notice: Immutable Snapshot -->
            <div class="tw-mt-5 tw-flex tw-items-center tw-gap-2.5 tw-rounded-lg tw-border tw-border-slate-200 tw-bg-slate-50/80 tw-px-3.5 tw-py-2.5 tw-text-xs tw-text-slate-600">
                <span class="tw-text-slate-400"><?php echo $icon('info'); ?></span>
                <span>Laporan ini immutable. Detail dibaca dari snapshot M10/M17 dan tidak mengikuti perubahan sumber.</span>
            </div>
        </header>

        <!-- Audit Result Items Section -->
        <section aria-labelledby="audit-items-title">
            <div class="tw-mb-4 tw-flex tw-items-center tw-justify-between">
                <div>
                    <h2 id="audit-items-title" class="tw-text-lg tw-font-bold tw-text-slate-950 tw-m-0">Butir Hasil Audit Mutu</h2>
                    <p class="tw-mt-1 tw-text-xs tw-text-slate-500">Hasil evaluasi, skor, bukti, temuan, dan rekomendasi per butir instrumen.</p>
                </div>
                <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-slate-100 tw-px-3 tw-py-1 tw-text-xs tw-font-bold tw-text-slate-700">
                    <?php echo count($items); ?> Butir Instrumen
                </span>
            </div>

            <!-- Desktop View: Structured Report Table -->
            <?php foreach ($standards as $standard): $items = $standard['items']; ?>
            <div class="tw-mb-5 tw-hidden md:tw-block tw-overflow-hidden tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-shadow-sm print-table">
                <div class="tw-border-b tw-border-slate-200 tw-bg-slate-50 tw-px-4 tw-py-3 tw-text-sm tw-font-bold tw-text-slate-900">
                    <?php echo html_escape($standard['source_standard_code_snapshot'] . ' — ' . $standard['source_standard_title_snapshot']); ?>
                </div>
                <div class="tw-overflow-x-auto">
                    <table class="tw-w-full tw-text-left tw-text-sm">
                        <thead class="tw-border-b tw-border-slate-200 tw-bg-slate-50 tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wider tw-text-slate-600">
                            <tr>
                                <th class="tw-px-4 tw-py-3.5 tw-w-12 tw-text-center">No</th>
                                <th class="tw-px-4 tw-py-3.5 tw-w-64">Indikator</th>
                                <th class="tw-px-4 tw-py-3.5 tw-w-72">Realisasi &amp; Bukti</th>
                                <th class="tw-px-4 tw-py-3.5 tw-w-36">Skor &amp; Deskriptor</th>
                                <th class="tw-px-4 tw-py-3.5 tw-w-56">Temuan</th>
                                <th class="tw-px-4 tw-py-3.5 tw-w-56">Rekomendasi</th>
                                <th class="tw-px-4 tw-py-3.5 tw-w-56">Rencana Perbaikan</th>
                                <th class="tw-px-4 tw-py-3.5 tw-w-28 tw-whitespace-nowrap">Tgl Bukti</th>
                            </tr>
                        </thead>
                        <tbody class="tw-divide-y tw-divide-slate-200">
                            <?php foreach ($items as $item):
                                $url = isset($item->evidence_url_snapshot) ? trim((string) $item->evidence_url_snapshot) : '';
                                $url_scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
                                $url_is_safe = $url !== '' && filter_var($url, FILTER_VALIDATE_URL) !== FALSE && in_array($url_scheme, ['http', 'https'], TRUE);
                                $file_name = isset($item->evidence_file_original_name_snapshot) ? trim((string) $item->evidence_file_original_name_snapshot) : '';
                                $has_file = $file_name !== '';
                                $auditor_evidences = spmi_report_auditor_evidence(isset($item->auditor_evidence_snapshot) ? $item->auditor_evidence_snapshot : NULL);
                            ?>
                                <tr class="hover:tw-bg-slate-50/70 tw-align-top">
                                    <td class="tw-px-4 tw-py-4 tw-text-center tw-font-bold tw-text-slate-500 tw-text-xs">
                                        <?php echo html_escape($item->standard_item_display_order ?: $item->display_order); ?>
                                    </td>

                                    <!-- Pertanyaan & Indikator -->
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-text-xs tw-text-slate-500 tw-bg-slate-50 tw-p-2 tw-rounded tw-border tw-border-slate-100">
                                            <?php echo html_escape($item->indicator_code_snapshot . ' — ' . $item->indicator_title_snapshot); ?>
                                        </div>
                                    </td>

                                    <!-- Realisasi & Bukti -->
                                    <td class="tw-px-4 tw-py-4">
                                        <!-- Realisasi -->
                                        <div class="tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Realisasi Auditee:</div>
                                        <div class="tw-text-xs tw-leading-relaxed tw-text-slate-800 tw-mb-3">
                                            <?php echo nl2br(html_escape($item->realization_snapshot)); ?>
                                        </div>

                                        <!-- Bukti Auditee Section (Only when present) -->
                                        <?php if ($url !== '' || $has_file): ?>
                                            <div class="tw-border-t tw-border-slate-100 tw-pt-2.5 tw-space-y-2">
                                                <div class="tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-wide tw-text-slate-400">Bukti Auditee:</div>

                                                <!-- URL Bukti -->
                                                <?php if ($url !== ''): ?>
                                                    <div class="tw-flex tw-items-start tw-gap-1.5 tw-text-xs">
                                                        <?php if ($url_is_safe): ?>
                                                            <span class="tw-text-blue-600 tw-mt-0.5"><?php echo $icon('external-link'); ?></span>
                                                            <a href="<?php echo html_escape($url); ?>" target="_blank" rel="noopener noreferrer" class="tw-text-blue-600 hover:tw-underline tw-break-all tw-font-medium">
                                                                <?php echo html_escape($item->evidence_url_snapshot); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="tw-break-all tw-text-slate-700"><?php echo html_escape($item->evidence_url_snapshot); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- File Bukti Auditee -->
                                                <?php if ($has_file): ?>
                                                    <div class="tw-rounded-lg tw-border tw-border-slate-200 tw-bg-slate-50/70 tw-p-2">
                                                        <div class="tw-flex tw-items-center tw-justify-between tw-gap-2">
                                                            <div class="tw-flex tw-items-center tw-gap-1.5 tw-min-w-0">
                                                                <span class="tw-text-slate-400"><?php echo $icon('file'); ?></span>
                                                                <span class="tw-text-xs tw-font-semibold tw-text-slate-800 tw-truncate" title="<?php echo html_escape($item->evidence_file_original_name_snapshot); ?>">
                                                                    <?php echo html_escape($item->evidence_file_original_name_snapshot); ?>
                                                                </span>
                                                            </div>
                                                            <span class="tw-text-[11px] tw-text-slate-500 tw-whitespace-nowrap tw-font-mono">
                                                                <?php echo html_escape(spmi_report_format_bytes($item->evidence_file_size_bytes_snapshot)); ?>
                                                            </span>
                                                        </div>

                                                        <!-- Technical Metadata Details (MIME & SHA-256) -->
                                                        <details class="tw-mt-2 tw-pt-1.5 tw-border-t tw-border-slate-200/80 tw-text-[10px] tw-text-slate-600">
                                                            <summary class="tw-cursor-pointer tw-font-semibold tw-text-slate-500 hover:tw-text-slate-800 select-none">
                                                                Detail teknis / Integritas berkas
                                                            </summary>
                                                            <div class="tw-mt-1.5 tw-space-y-1 tw-bg-white tw-p-2 tw-rounded tw-border tw-border-slate-100">
                                                                <div>
                                                                    <span class="tw-text-slate-400">MIME:</span>
                                                                    <span class="tw-font-mono tw-text-slate-700"><?php echo html_escape(spmi_report_value($item->evidence_file_mime_type_snapshot)); ?></span>
                                                                </div>
                                                                <div>
                                                                    <span class="tw-text-slate-400">SHA-256:</span>
                                                                    <div class="tw-font-mono tw-text-[10px] tw-break-all tw-text-slate-700 tw-bg-slate-50 tw-p-1 tw-rounded tw-mt-0.5">
                                                                        <?php echo html_escape(spmi_report_value($item->evidence_file_sha256_snapshot)); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </details>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Bukti Auditor Section -->
                                        <?php if (!empty($auditor_evidences)): ?>
                                            <div class="tw-mt-3 tw-border-t tw-border-slate-100 tw-pt-2.5">
                                                <div class="tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-wide tw-text-slate-400 tw-mb-1.5">Bukti Tambahan Auditor:</div>
                                                <div class="tw-space-y-2">
                                                    <?php foreach ($auditor_evidences as $aud_ev): ?>
                                                        <div class="tw-rounded-lg tw-border tw-border-slate-200 tw-bg-slate-50/70 tw-p-2">
                                                            <div class="tw-flex tw-items-center tw-justify-between tw-gap-2">
                                                                <div class="tw-flex tw-items-center tw-gap-1.5 tw-min-w-0">
                                                                    <span class="tw-text-blue-500"><?php echo $icon('paperclip'); ?></span>
                                                                    <span class="tw-text-xs tw-font-semibold tw-text-slate-800 tw-truncate" title="<?php echo html_escape($aud_ev['original_name']); ?>">
                                                                        <?php echo html_escape($aud_ev['original_name']); ?>
                                                                    </span>
                                                                </div>
                                                                <span class="tw-text-[11px] tw-text-slate-500 tw-whitespace-nowrap tw-font-mono">
                                                                    <?php echo html_escape(spmi_report_format_bytes($aud_ev['size_bytes'])); ?>
                                                                </span>
                                                            </div>
                                                            <!-- Technical Metadata Details (MIME & SHA-256) -->
                                                            <details class="tw-mt-2 tw-pt-1.5 tw-border-t tw-border-slate-200/80 tw-text-[10px] tw-text-slate-600">
                                                                <summary class="tw-cursor-pointer tw-font-semibold tw-text-slate-500 hover:tw-text-slate-800 select-none">
                                                                    Detail teknis / Integritas berkas
                                                                </summary>
                                                                <div class="tw-mt-1.5 tw-space-y-1 tw-bg-white tw-p-2 tw-rounded tw-border tw-border-slate-100">
                                                                    <div>
                                                                        <span class="tw-text-slate-400">MIME:</span>
                                                                        <span class="tw-font-mono tw-text-slate-700"><?php echo html_escape($aud_ev['mime_type']); ?></span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="tw-text-slate-400">SHA-256:</span>
                                                                        <div class="tw-font-mono tw-text-[10px] tw-break-all tw-text-slate-700 tw-bg-slate-50 tw-p-1 tw-rounded tw-mt-0.5">
                                                                            <?php echo html_escape($aud_ev['sha256']); ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </details>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <p class="tw-mt-2 tw-text-xs tw-text-slate-400 tw-m-0">Tidak ada bukti tambahan auditor</p>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Skor & Deskriptor -->
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-flex tw-items-baseline tw-gap-1.5 tw-mb-2">
                                            <span class="tw-inline-flex tw-h-7 tw-w-7 tw-items-center tw-justify-center tw-rounded-lg tw-bg-blue-600 tw-font-bold tw-text-white tw-text-sm">
                                                <?php echo html_escape($item->score); ?>
                                            </span>
                                            <span class="tw-text-xs tw-text-slate-500">/ 4</span>
                                        </div>
                                        <div class="tw-text-xs tw-text-slate-700 tw-leading-relaxed">
                                            <?php echo nl2br(html_escape($item->descriptor_snapshot)); ?>
                                        </div>
                                    </td>

                                    <!-- Temuan -->
                                    <td class="tw-px-4 tw-py-4">
                                        <?php $finding_type = spmi_report_value(isset($item->finding_type_snapshot) ? $item->finding_type_snapshot : NULL); ?>
                                        <?php if ($finding_type !== '-'): ?>
                                            <span class="tw-inline-flex tw-items-center tw-rounded tw-bg-amber-100 tw-px-2 tw-py-0.5 tw-text-[11px] tw-font-bold tw-text-amber-900 tw-mb-1.5">
                                                <?php echo html_escape($finding_type); ?>
                                            </span>
                                        <?php endif; ?>
                                        <div class="tw-text-xs tw-leading-relaxed tw-text-slate-800">
                                            <?php echo nl2br(html_escape(spmi_report_value(isset($item->finding_snapshot) ? $item->finding_snapshot : NULL))); ?>
                                        </div>
                                    </td>

                                    <!-- Rekomendasi -->
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-text-xs tw-leading-relaxed tw-text-slate-800">
                                            <?php echo nl2br(html_escape(spmi_report_value(isset($item->recommendation_snapshot) ? $item->recommendation_snapshot : NULL))); ?>
                                        </div>
                                    </td>

                                    <!-- Rencana Perbaikan -->
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-text-xs tw-leading-relaxed tw-text-slate-800">
                                            <?php echo nl2br(html_escape(spmi_report_value(isset($item->improvement_plan_snapshot) ? $item->improvement_plan_snapshot : NULL))); ?>
                                        </div>
                                    </td>

                                    <!-- Tanggal Bukti -->
                                    <td class="tw-px-4 tw-py-4 tw-text-xs tw-text-slate-600 tw-whitespace-nowrap">
                                        <?php echo html_escape(spmi_report_value(isset($item->evidence_date_snapshot) ? $item->evidence_date_snapshot : NULL)); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Mobile View: Stacked Cards Per Item -->
            <?php foreach ($standards as $standard): $items = $standard['items']; ?>
            <div class="tw-mb-5 md:tw-hidden">
                <h3 class="tw-mb-3 tw-text-sm tw-font-bold tw-text-slate-900"><?php echo html_escape($standard['source_standard_code_snapshot'] . ' — ' . $standard['source_standard_title_snapshot']); ?></h3>
            <div class="tw-grid tw-gap-4 print-cards">
                <?php foreach ($items as $item):
                    $url = isset($item->evidence_url_snapshot) ? trim((string) $item->evidence_url_snapshot) : '';
                    $url_scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
                    $url_is_safe = $url !== '' && filter_var($url, FILTER_VALIDATE_URL) !== FALSE && in_array($url_scheme, ['http', 'https'], TRUE);
                    $file_name = isset($item->evidence_file_original_name_snapshot) ? trim((string) $item->evidence_file_original_name_snapshot) : '';
                    $has_file = $file_name !== '';
                    $auditor_evidences = spmi_report_auditor_evidence(isset($item->auditor_evidence_snapshot) ? $item->auditor_evidence_snapshot : NULL);
                ?>
                    <article class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-5 tw-shadow-sm tw-space-y-4">
                        <!-- Card Header: No + Indicator -->
                        <div class="tw-border-b tw-border-slate-100 tw-pb-3">
                            <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                                <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-800 tw-bg-slate-100 tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200">
                                    Butir <?php echo html_escape($item->standard_item_display_order ?: $item->display_order); ?>
                                </span>
                                <div class="tw-flex tw-items-center tw-gap-1">
                                    <span class="tw-inline-flex tw-h-6 tw-w-6 tw-items-center tw-justify-center tw-rounded tw-bg-blue-600 tw-font-bold tw-text-white tw-text-xs">
                                        <?php echo html_escape($item->score); ?>
                                    </span>
                                    <span class="tw-text-xs tw-text-slate-500">/ 4</span>
                                </div>
                            </div>
                            <div class="tw-mt-2 tw-text-xs tw-text-slate-500">
                                <?php echo html_escape($item->indicator_code_snapshot . ' — ' . $item->indicator_title_snapshot); ?>
                            </div>
                        </div>

                        <!-- Realisasi & Bukti Section -->
                        <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100 tw-text-xs">
                            <strong class="tw-block tw-text-slate-700 tw-mb-1">Realisasi Auditee:</strong>
                            <p class="tw-text-slate-800 tw-leading-relaxed tw-mb-3">
                                <?php echo nl2br(html_escape($item->realization_snapshot)); ?>
                            </p>

                            <!-- Bukti Auditee Section (Only when present) -->
                            <?php if ($url !== '' || $has_file): ?>
                                <div class="tw-border-t tw-border-slate-200/80 tw-pt-2.5 tw-space-y-2">
                                    <div class="tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-wide tw-text-slate-400">Bukti Auditee:</div>

                                    <!-- URL Bukti -->
                                    <?php if ($url !== ''): ?>
                                        <div class="tw-flex tw-items-start tw-gap-1.5 tw-text-xs">
                                            <?php if ($url_is_safe): ?>
                                                <span class="tw-text-blue-600 tw-mt-0.5"><?php echo $icon('external-link'); ?></span>
                                                <a href="<?php echo html_escape($url); ?>" target="_blank" rel="noopener noreferrer" class="tw-text-blue-600 hover:tw-underline tw-break-all tw-font-medium">
                                                    <?php echo html_escape($item->evidence_url_snapshot); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="tw-break-all tw-text-slate-700"><?php echo html_escape($item->evidence_url_snapshot); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- File Bukti Auditee -->
                                    <?php if ($has_file): ?>
                                        <div class="tw-rounded-lg tw-border tw-border-slate-200 tw-bg-white tw-p-2.5">
                                            <div class="tw-flex tw-items-center tw-justify-between tw-gap-2">
                                                <div class="tw-flex tw-items-center tw-gap-1.5 tw-min-w-0">
                                                    <span class="tw-text-slate-400"><?php echo $icon('file'); ?></span>
                                                    <span class="tw-text-xs tw-font-semibold tw-text-slate-800 tw-truncate" title="<?php echo html_escape($item->evidence_file_original_name_snapshot); ?>">
                                                        <?php echo html_escape($item->evidence_file_original_name_snapshot); ?>
                                                    </span>
                                                </div>
                                                <span class="tw-text-[11px] tw-text-slate-500 tw-whitespace-nowrap tw-font-mono">
                                                    <?php echo html_escape(spmi_report_format_bytes($item->evidence_file_size_bytes_snapshot)); ?>
                                                </span>
                                            </div>

                                            <!-- Technical Metadata Details (MIME & SHA-256) -->
                                            <details class="tw-mt-2 tw-pt-1.5 tw-border-t tw-border-slate-100 tw-text-[10px] tw-text-slate-600">
                                                <summary class="tw-cursor-pointer tw-font-semibold tw-text-slate-500 hover:tw-text-slate-800 select-none">
                                                    Detail teknis / Integritas berkas
                                                </summary>
                                                <div class="tw-mt-1.5 tw-space-y-1 tw-bg-slate-50 tw-p-2 tw-rounded tw-border tw-border-slate-100">
                                                    <div>
                                                        <span class="tw-text-slate-400">MIME:</span>
                                                        <span class="tw-font-mono tw-text-slate-700"><?php echo html_escape(spmi_report_value($item->evidence_file_mime_type_snapshot)); ?></span>
                                                    </div>
                                                    <div>
                                                        <span class="tw-text-slate-400">SHA-256:</span>
                                                        <div class="tw-font-mono tw-text-[10px] tw-break-all tw-text-slate-700 tw-bg-white tw-p-1 tw-rounded tw-mt-0.5">
                                                            <?php echo html_escape(spmi_report_value($item->evidence_file_sha256_snapshot)); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </details>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Bukti Auditor Section -->
                            <?php if (!empty($auditor_evidences)): ?>
                                <div class="tw-mt-3 tw-border-t tw-border-slate-200/80 tw-pt-2.5">
                                    <div class="tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-wide tw-text-slate-400 tw-mb-1.5">Bukti Tambahan Auditor:</div>
                                    <div class="tw-space-y-2">
                                        <?php foreach ($auditor_evidences as $aud_ev): ?>
                                            <div class="tw-rounded-lg tw-border tw-border-slate-200 tw-bg-white tw-p-2.5">
                                                <div class="tw-flex tw-items-center tw-justify-between tw-gap-2">
                                                    <div class="tw-flex tw-items-center tw-gap-1.5 tw-min-w-0">
                                                        <span class="tw-text-blue-500"><?php echo $icon('paperclip'); ?></span>
                                                        <span class="tw-text-xs tw-font-semibold tw-text-slate-800 tw-truncate" title="<?php echo html_escape($aud_ev['original_name']); ?>">
                                                            <?php echo html_escape($aud_ev['original_name']); ?>
                                                        </span>
                                                    </div>
                                                    <span class="tw-text-[11px] tw-text-slate-500 tw-whitespace-nowrap tw-font-mono">
                                                        <?php echo html_escape(spmi_report_format_bytes($aud_ev['size_bytes'])); ?>
                                                    </span>
                                                </div>
                                                <!-- Technical Metadata Details (MIME & SHA-256) -->
                                                <details class="tw-mt-2 tw-pt-1.5 tw-border-t tw-border-slate-100 tw-text-[10px] tw-text-slate-600">
                                                    <summary class="tw-cursor-pointer tw-font-semibold tw-text-slate-500 hover:tw-text-slate-800 select-none">
                                                        Detail teknis / Integritas berkas
                                                    </summary>
                                                    <div class="tw-mt-1.5 tw-space-y-1 tw-bg-slate-50 tw-p-2 tw-rounded tw-border tw-border-slate-100">
                                                        <div>
                                                            <span class="tw-text-slate-400">MIME:</span>
                                                            <span class="tw-font-mono tw-text-slate-700"><?php echo html_escape($aud_ev['mime_type']); ?></span>
                                                        </div>
                                                        <div>
                                                            <span class="tw-text-slate-400">SHA-256:</span>
                                                            <div class="tw-font-mono tw-text-[10px] tw-break-all tw-text-slate-700 tw-bg-white tw-p-1 tw-rounded tw-mt-0.5">
                                                                <?php echo html_escape($aud_ev['sha256']); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </details>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="tw-mt-2 tw-text-xs tw-text-slate-400 tw-m-0">Tidak ada bukti tambahan auditor</p>
                            <?php endif; ?>
                        </div>

                        <!-- Deskriptor Rubrik -->
                        <div class="tw-text-xs">
                            <strong class="tw-text-slate-700 tw-block tw-mb-1">Deskriptor Penilaian:</strong>
                            <p class="tw-text-slate-600 tw-leading-relaxed tw-m-0">
                                <?php echo nl2br(html_escape($item->descriptor_snapshot)); ?>
                            </p>
                        </div>

                        <!-- Temuan -->
                        <div class="tw-text-xs tw-border-t tw-border-slate-100 tw-pt-3">
                            <div class="tw-flex tw-items-center tw-gap-2 tw-mb-1">
                                <strong class="tw-text-slate-700">Temuan Audit:</strong>
                                <?php $finding_type = spmi_report_value(isset($item->finding_type_snapshot) ? $item->finding_type_snapshot : NULL); ?>
                                <?php if ($finding_type !== '-'): ?>
                                    <span class="tw-inline-flex tw-items-center tw-rounded tw-bg-amber-100 tw-px-1.5 tw-py-0.5 tw-text-[10px] tw-font-bold tw-text-amber-900">
                                        <?php echo html_escape($finding_type); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="tw-text-slate-800 tw-leading-relaxed tw-m-0">
                                <?php echo nl2br(html_escape(spmi_report_value(isset($item->finding_snapshot) ? $item->finding_snapshot : NULL))); ?>
                            </p>
                        </div>

                        <!-- Rekomendasi -->
                        <div class="tw-text-xs tw-border-t tw-border-slate-100 tw-pt-3">
                            <strong class="tw-text-slate-700 tw-block tw-mb-1">Rekomendasi Auditor:</strong>
                            <p class="tw-text-slate-800 tw-leading-relaxed tw-m-0">
                                <?php echo nl2br(html_escape(spmi_report_value(isset($item->recommendation_snapshot) ? $item->recommendation_snapshot : NULL))); ?>
                            </p>
                        </div>

                        <!-- Rencana Perbaikan -->
                        <div class="tw-text-xs tw-border-t tw-border-slate-100 tw-pt-3">
                            <strong class="tw-text-slate-700 tw-block tw-mb-1">Rencana Perbaikan Auditee:</strong>
                            <p class="tw-text-slate-800 tw-leading-relaxed tw-m-0">
                                <?php echo nl2br(html_escape(spmi_report_value(isset($item->improvement_plan_snapshot) ? $item->improvement_plan_snapshot : NULL))); ?>
                            </p>
                        </div>

                        <!-- Tanggal Bukti -->
                        <div class="tw-flex tw-items-center tw-justify-between tw-text-xs tw-text-slate-500 tw-border-t tw-border-slate-100 tw-pt-3">
                            <span>Tanggal Bukti:</span>
                            <strong class="tw-text-slate-800">
                                <?php echo html_escape(spmi_report_value(isset($item->evidence_date_snapshot) ? $item->evidence_date_snapshot : NULL)); ?>
                            </strong>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            </div>
            <?php endforeach; ?>
        </section>
    </div>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
