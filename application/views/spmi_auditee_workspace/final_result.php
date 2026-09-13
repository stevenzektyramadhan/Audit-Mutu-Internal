<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

function spmi_final_result_value($value) {
    $value = trim((string) $value);
    return $value === '' ? '-' : $value;
}

function spmi_final_result_auditor_evidence($value) {
    $items = json_decode((string) $value, TRUE);
    if (!is_array($items)) return [(string) $value];
    $lines = [];
    foreach ($items as $item) {
        if (!is_array($item)) continue;
        $lines[] = (string) (isset($item['original_name']) ? $item['original_name'] : '-') . ' / ' . (string) (isset($item['mime_type']) ? $item['mime_type'] : '-') . ' / ' . (string) (isset($item['size_bytes']) ? (int) $item['size_bytes'] : 0) . ' bytes / ' . (string) (isset($item['sha256']) ? $item['sha256'] : '-');
    }
    return $lines ?: ['-'];
}

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'shield-check' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
    ];
    return '<svg class="adte-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['shield-check']) . '</svg>';
};
?>

<main id="auditee-root" data-ui-contract="ami-row-actions ami-action-btn" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-7xl">
        <!-- Top Back Action -->
        <div class="ami-row-actions no-print tw-mb-5">
            <a class="ami-action-btn adte-back-link tw-text-sm" href="<?php echo site_url('auditee/spmi'); ?>">
                <?php echo $icon('arrow-left'); ?>
                <span>Kembali ke Workspace SPMI</span>
            </a>
        </div>

        <!-- Header Card: Info Resmi Laporan -->
        <header class="tw-mb-6 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm">
            <div class="tw-flex tw-items-center tw-gap-2.5 tw-mb-2">
                <span class="tw-inline-flex tw-items-center tw-gap-1 tw-rounded-full tw-bg-emerald-50 tw-border tw-border-emerald-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-emerald-800">
                    <?php echo $icon('shield-check'); ?>
                    <span>Laporan Resmi SPMI</span>
                </span>
                <span class="tw-text-xs tw-text-slate-500">M10 Final Snapshot</span>
            </div>

            <h1 class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950 tw-m-0">
                <?php echo html_escape($report->report_number); ?>
            </h1>

            <dl class="row tw-hidden">
                <dt class="col-sm-3">Siklus</dt><dd class="col-sm-9"><?php echo html_escape($report->cycle_code_snapshot . ' — ' . $report->cycle_title_snapshot); ?></dd>
                <dt class="col-sm-3">Sumber</dt><dd class="col-sm-9"><?php echo html_escape($report->source_version_code_snapshot . ' / ' . $report->source_standard_code_snapshot . ' / ' . $report->source_package_code_snapshot); ?></dd>
                <dt class="col-sm-3">Auditor / Auditee</dt><dd class="col-sm-9"><?php echo html_escape($report->auditor_name_snapshot . ' / ' . $report->auditee_name_snapshot); ?></dd>
                <dt class="col-sm-3">Finalisasi</dt><dd class="col-sm-9"><?php echo html_escape($report->assessment_finalized_at_snapshot); ?></dd>
            </dl>

            <div class="tw-mt-6 tw-grid tw-gap-4 sm:tw-grid-cols-2 lg:tw-grid-cols-4 tw-text-sm">
                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100">
                    <span class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Siklus</span>
                    <strong class="tw-text-slate-900 tw-font-medium">
                        <?php echo html_escape($report->cycle_code_snapshot . ' — ' . $report->cycle_title_snapshot); ?>
                    </strong>
                </div>

                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100">
                    <span class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Sumber Paket</span>
                    <span class="tw-text-slate-800 tw-font-mono tw-text-xs">
                        <?php echo html_escape($report->source_version_code_snapshot . ' / ' . $report->source_standard_code_snapshot . ' / ' . $report->source_package_code_snapshot); ?>
                    </span>
                </div>

                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100">
                    <span class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Auditor / Auditee</span>
                    <strong class="tw-text-slate-900 tw-font-medium">
                        <?php echo html_escape($report->auditor_name_snapshot . ' / ' . $report->auditee_name_snapshot); ?>
                    </strong>
                </div>

                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100">
                    <span class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Finalisasi</span>
                    <span class="tw-text-slate-800">
                        <?php echo html_escape($report->assessment_finalized_at_snapshot); ?>
                    </span>
                </div>
            </div>

            <div class="tw-mt-5 tw-flex tw-items-center tw-gap-2.5 tw-rounded-lg tw-border tw-border-slate-200 tw-bg-slate-50/80 tw-px-3.5 tw-py-2.5 tw-text-xs tw-text-slate-600">
                <span class="tw-text-slate-400"><?php echo $icon('info'); ?></span>
                <span>Hasil akhir ini readonly dan dibaca dari snapshot laporan SPMI.</span>
            </div>
        </header>

        <!-- Section: Hasil Akhir Butir Instrumen -->
        <section aria-labelledby="final-items-title">
            <div class="tw-mb-4 tw-flex tw-items-center tw-justify-between">
                <div>
                    <h2 id="final-items-title" class="tw-text-lg tw-font-bold tw-text-slate-950 tw-m-0">Hasil Evaluasi &amp; Nilai Butir SPMI</h2>
                    <p class="tw-mt-1 tw-text-xs tw-text-slate-500">Rekap skor, uraian temuan auditor, rekomendasi, dan rencana perbaikan mutu.</p>
                </div>
                <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-slate-100 tw-px-3 tw-py-1 tw-text-xs tw-font-bold tw-text-slate-700">
                    <?php echo count($report_items); ?> Butir Instrumen
                </span>
            </div>

            <!-- Desktop View: Table -->
            <div class="tw-hidden md:tw-block tw-overflow-hidden tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-shadow-sm">
                <div class="tw-overflow-x-auto">
                    <table class="table ami-table tw-w-full tw-text-left tw-text-sm">
                        <thead class="tw-border-b tw-border-slate-200 tw-bg-slate-50 tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wider tw-text-slate-600">
                            <tr>
                                <th class="tw-px-4 tw-py-3.5 tw-w-12 tw-text-center">No</th>
                                <th class="tw-px-4 tw-py-3.5 tw-w-64">Pertanyaan</th>
                                <th class="tw-px-4 tw-py-3.5 tw-w-64">Realisasi &amp; Bukti</th>
                                <th class="tw-px-4 tw-py-3.5 tw-w-36">Skor</th>
                                <th class="tw-px-4 tw-py-3.5 tw-w-40">Deskriptor</th>
                                <th class="tw-px-4 tw-py-3.5 tw-w-56">Temuan</th>
                                <th class="tw-px-4 tw-py-3.5 tw-w-56">Rekomendasi</th>
                                <th class="tw-px-4 tw-py-3.5 tw-w-56">Rencana perbaikan</th>
                                <th class="tw-px-4 tw-py-3.5 tw-w-28 tw-whitespace-nowrap">Tanggal bukti</th>
                            </tr>
                        </thead>
                        <tbody class="tw-divide-y tw-divide-slate-200">
                            <?php foreach ($report_items as $item): ?>
                                <tr class="hover:tw-bg-slate-50/70 tw-align-top">
                                    <td class="tw-px-4 tw-py-4 tw-text-center tw-font-bold tw-text-slate-500 tw-text-xs">
                                        <?php echo html_escape($item->display_order); ?>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-800 tw-bg-slate-100 tw-px-2 tw-py-0.5 tw-rounded tw-border tw-border-slate-200 tw-inline-block tw-mb-1.5">
                                            <?php echo html_escape($item->question_code_snapshot); ?>
                                        </span>
                                        <div class="tw-text-slate-900 tw-font-medium tw-leading-relaxed tw-mb-2">
                                            <?php echo nl2br(html_escape($item->question_text_snapshot)); ?>
                                        </div>
                                        <div class="tw-text-xs tw-text-slate-500 tw-bg-slate-50 tw-p-2 tw-rounded tw-border tw-border-slate-100">
                                            <strong class="tw-text-slate-600">Indikator:</strong><br>
                                            <?php echo html_escape($item->indicator_code_snapshot . ' — ' . $item->indicator_title_snapshot); ?>
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Realisasi:</div>
                                        <div class="tw-text-xs tw-leading-relaxed tw-text-slate-800 tw-mb-3">
                                            <?php echo nl2br(html_escape($item->realization_snapshot)); ?>
                                        </div>
                                        <div class="tw-border-t tw-border-slate-100 tw-pt-2 tw-space-y-1 tw-text-[11px] tw-text-slate-600">
                                            <div><strong class="tw-text-slate-700">URL:</strong> <?php echo html_escape(spmi_final_result_value(isset($item->evidence_url_snapshot) ? $item->evidence_url_snapshot : NULL)); ?></div>
                                            <div><strong class="tw-text-slate-700">File:</strong> <?php echo html_escape(spmi_final_result_value(isset($item->evidence_file_original_name_snapshot) ? $item->evidence_file_original_name_snapshot : NULL)); ?></div>
                                            <div><strong class="tw-text-slate-700">MIME:</strong> <?php echo html_escape(spmi_final_result_value(isset($item->evidence_file_mime_type_snapshot) ? $item->evidence_file_mime_type_snapshot : NULL)); ?></div>
                                            <div><strong class="tw-text-slate-700">Ukuran:</strong> <?php echo html_escape(spmi_final_result_value(isset($item->evidence_file_size_bytes_snapshot) ? $item->evidence_file_size_bytes_snapshot : NULL)); ?></div>
                                            <div class="tw-break-all"><strong class="tw-text-slate-700">SHA-256:</strong> <?php echo html_escape(spmi_final_result_value(isset($item->evidence_file_sha256_snapshot) ? $item->evidence_file_sha256_snapshot : NULL)); ?></div>
                                            <div class="tw-mt-1.5 tw-pt-1 tw-border-t tw-border-dashed tw-border-slate-200">
                                                <strong class="tw-text-slate-700">Bukti auditor:</strong>
                                                <?php foreach (spmi_final_result_auditor_evidence(isset($item->auditor_evidence_snapshot) ? $item->auditor_evidence_snapshot : NULL) as $line): ?>
                                                    <div class="tw-break-all tw-mt-0.5"><?php echo nl2br(html_escape($line)); ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-flex tw-items-baseline tw-gap-1.5 tw-mb-2">
                                            <span class="tw-inline-flex tw-h-7 tw-w-7 tw-items-center tw-justify-center tw-rounded-lg tw-bg-blue-600 tw-font-bold tw-text-white tw-text-sm">
                                                <?php echo html_escape($item->score); ?>
                                            </span>
                                            <span class="tw-text-xs tw-text-slate-500">/ 4</span>
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-text-xs tw-text-slate-700 tw-leading-relaxed">
                                            <?php echo nl2br(html_escape($item->descriptor_snapshot)); ?>
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <?php $finding_type = spmi_final_result_value(isset($item->finding_type_snapshot) ? $item->finding_type_snapshot : NULL); ?>
                                        <?php if ($finding_type !== '-'): ?>
                                            <span class="tw-inline-flex tw-items-center tw-rounded tw-bg-amber-100 tw-px-2 tw-py-0.5 tw-text-[11px] tw-font-bold tw-text-amber-900 tw-mb-1.5">
                                                <?php echo html_escape($finding_type); ?>
                                            </span>
                                        <?php endif; ?>
                                        <div class="tw-text-xs tw-leading-relaxed tw-text-slate-800">
                                            <?php echo nl2br(html_escape(spmi_final_result_value(isset($item->finding_snapshot) ? $item->finding_snapshot : NULL))); ?>
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-text-xs tw-leading-relaxed tw-text-slate-800">
                                            <?php echo nl2br(html_escape(spmi_final_result_value(isset($item->recommendation_snapshot) ? $item->recommendation_snapshot : NULL))); ?>
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4">
                                        <div class="tw-text-xs tw-leading-relaxed tw-text-slate-800">
                                            <?php echo nl2br(html_escape(spmi_final_result_value(isset($item->improvement_plan_snapshot) ? $item->improvement_plan_snapshot : NULL))); ?>
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4 tw-text-xs tw-text-slate-600 tw-whitespace-nowrap">
                                        <?php echo html_escape(spmi_final_result_value(isset($item->evidence_date_snapshot) ? $item->evidence_date_snapshot : NULL)); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile View: Stacked Cards -->
            <div class="tw-grid tw-gap-4 md:tw-hidden">
                <?php foreach ($report_items as $item): ?>
                    <article class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-5 tw-shadow-sm tw-space-y-4">
                        <div class="tw-border-b tw-border-slate-100 tw-pb-3">
                            <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                                <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-800 tw-bg-slate-100 tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200">
                                    Butir <?php echo html_escape($item->display_order); ?>: <?php echo html_escape($item->question_code_snapshot); ?>
                                </span>
                                <div class="tw-flex tw-items-center tw-gap-1">
                                    <span class="tw-inline-flex tw-h-6 tw-w-6 tw-items-center tw-justify-center tw-rounded tw-bg-blue-600 tw-font-bold tw-text-white tw-text-xs">
                                        <?php echo html_escape($item->score); ?>
                                    </span>
                                    <span class="tw-text-xs tw-text-slate-500">/ 4</span>
                                </div>
                            </div>
                            <h3 class="tw-text-sm tw-font-bold tw-text-slate-900 tw-leading-snug tw-m-0">
                                <?php echo nl2br(html_escape($item->question_text_snapshot)); ?>
                            </h3>
                            <div class="tw-mt-2 tw-text-xs tw-text-slate-500">
                                <strong class="tw-text-slate-600">Indikator:</strong> <?php echo html_escape($item->indicator_code_snapshot . ' — ' . $item->indicator_title_snapshot); ?>
                            </div>
                        </div>

                        <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100 tw-text-xs">
                            <strong class="tw-block tw-text-slate-700 tw-mb-1">Realisasi &amp; Bukti:</strong>
                            <p class="tw-text-slate-800 tw-leading-relaxed tw-mb-2">
                                <?php echo nl2br(html_escape($item->realization_snapshot)); ?>
                            </p>
                            <div class="tw-border-t tw-border-slate-200/80 tw-pt-2 tw-space-y-1 tw-text-[11px] tw-text-slate-600">
                                <div><strong>URL:</strong> <?php echo html_escape(spmi_final_result_value(isset($item->evidence_url_snapshot) ? $item->evidence_url_snapshot : NULL)); ?></div>
                                <div><strong>File:</strong> <?php echo html_escape(spmi_final_result_value(isset($item->evidence_file_original_name_snapshot) ? $item->evidence_file_original_name_snapshot : NULL)); ?></div>
                            </div>
                        </div>

                        <div class="tw-text-xs tw-border-t tw-border-slate-100 tw-pt-3">
                            <strong class="tw-text-slate-700 tw-block tw-mb-1">Deskriptor:</strong>
                            <p class="tw-text-slate-600 tw-m-0"><?php echo nl2br(html_escape($item->descriptor_snapshot)); ?></p>
                        </div>

                        <div class="tw-text-xs tw-border-t tw-border-slate-100 tw-pt-3">
                            <strong class="tw-text-slate-700 tw-block tw-mb-1">Temuan:</strong>
                            <p class="tw-text-slate-800 tw-m-0"><?php echo nl2br(html_escape(spmi_final_result_value(isset($item->finding_snapshot) ? $item->finding_snapshot : NULL))); ?></p>
                        </div>

                        <div class="tw-text-xs tw-border-t tw-border-slate-100 tw-pt-3">
                            <strong class="tw-text-slate-700 tw-block tw-mb-1">Rekomendasi:</strong>
                            <p class="tw-text-slate-800 tw-m-0"><?php echo nl2br(html_escape(spmi_final_result_value(isset($item->recommendation_snapshot) ? $item->recommendation_snapshot : NULL))); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
