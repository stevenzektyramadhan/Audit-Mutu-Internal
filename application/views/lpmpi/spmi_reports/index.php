<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

// Lucide icon helper
$icon = static function ($name) {
    $paths = [
        'file-text' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/>',
        'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
        'search' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'sparkles' => '<path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>',
        'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    ];
    return '<svg class="reports-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['file-text']) . '</svg>';
};
?>

<main id="reports-root" data-ui-contract="ami-row-actions ami-action-btn" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-7xl">
        <!-- Header / Hero -->
        <div class="tw-mb-8 tw-flex tw-flex-col tw-gap-4 sm:tw-flex-row sm:tw-items-end sm:tw-justify-between">
            <div>
                <p class="tw-mb-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-text-slate-500">Hasil Audit Mutu</p>
                <h1 class="tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950">Laporan SPMI</h1>
                <p class="tw-mt-2 tw-max-w-2xl tw-text-sm tw-text-slate-500">
                    <?php echo nl2br(html_escape('Snapshot immutable dari assessment M9 yang telah difinalisasi.')); ?>
                </p>
            </div>
        </div>

        <?php if (!empty($finalized_assessments)): ?>
            <!-- Section: Assessment Finalized Pending Generation -->
            <section class="tw-mb-8 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm" aria-labelledby="pending-assessments-title">
                <div class="tw-mb-4 tw-flex tw-items-center tw-justify-between">
                    <div>
                        <h2 id="pending-assessments-title" class="tw-text-base tw-font-bold tw-text-slate-900">Assessment finalized belum dilaporkan</h2>
                        <p class="tw-mt-1 tw-text-xs tw-text-slate-500">Assessment berikut telah difinalisasi dan siap dibuatkan dokumen snapshot laporan resmi.</p>
                    </div>
                    <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-bg-amber-50 tw-px-3 tw-py-1 tw-text-xs tw-font-semibold tw-text-amber-800 tw-border tw-border-amber-200">
                        <?php echo count($finalized_assessments); ?> Menunggu
                    </span>
                </div>

                <!-- Desktop Table -->
                <div class="tw-hidden md:tw-block tw-overflow-hidden tw-rounded-xl tw-border tw-border-slate-200">
                    <table class="tw-w-full tw-text-left tw-text-sm">
                        <thead class="tw-border-b tw-border-slate-200 tw-bg-slate-50 tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wider tw-text-slate-500">
                            <tr>
                                <th class="tw-px-4 tw-py-3">Siklus</th>
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
                                        <?php echo html_escape($assessment->auditee_name); ?>
                                    </td>
                                    <td class="tw-px-4 tw-py-3 tw-whitespace-nowrap tw-text-slate-500 tw-text-xs">
                                        <?php echo html_escape($assessment->finalized_at); ?>
                                    </td>
                                    <td class="tw-px-4 tw-py-3 tw-text-right">
                                        <div class="ami-row-actions tw-inline-flex tw-justify-end">
                                            <?php echo form_open('lpmpi/spmi-reports/assessment/create/' . (int) $assessment->id, ['class' => 'tw-m-0']); ?>
                                                <button class="btn-ami ami-action-btn tw-button-primary tw-text-xs tw-py-1.5 tw-px-3" type="submit">
                                                    <?php echo $icon('sparkles'); ?>
                                                    <span>Generate laporan</span>
                                                </button>
                                            <?php echo form_close(); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="tw-grid tw-gap-3 md:tw-hidden">
                    <?php foreach ($finalized_assessments as $assessment): ?>
                        <div class="tw-rounded-xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-4">
                            <div class="tw-text-xs tw-font-semibold tw-text-slate-500">Siklus</div>
                            <div class="tw-text-sm tw-font-bold tw-text-slate-900 tw-mt-0.5">
                                <?php echo html_escape($assessment->cycle_code . ' — ' . $assessment->cycle_title); ?>
                            </div>
                            <div class="tw-mt-2.5 tw-flex tw-items-center tw-gap-2 tw-text-xs tw-text-slate-700">
                                <?php echo $icon('user'); ?>
                                <span><?php echo html_escape($assessment->auditee_name); ?></span>
                            </div>
                            <div class="tw-mt-1 tw-flex tw-items-center tw-gap-2 tw-text-xs tw-text-slate-500">
                                <?php echo $icon('clock'); ?>
                                <span><?php echo html_escape($assessment->finalized_at); ?></span>
                            </div>
                            <div class="tw-mt-3 tw-pt-3 tw-border-t tw-border-slate-200">
                                <div class="ami-row-actions">
                                    <?php echo form_open('lpmpi/spmi-reports/assessment/create/' . (int) $assessment->id, ['class' => 'tw-w-full']); ?>
                                        <button class="btn-ami ami-action-btn tw-button-primary tw-w-full tw-min-h-[44px] tw-text-sm" type="submit">
                                            <?php echo $icon('sparkles'); ?>
                                            <span>Generate laporan</span>
                                        </button>
                                    <?php echo form_close(); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Toolbar Filter & Search -->
        <section class="tw-mb-5 tw-grid tw-gap-3 sm:tw-grid-cols-[1fr_220px]" aria-label="Filter laporan">
            <label class="tw-block">
                <span class="tw-mb-1 tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-slate-500">Cari laporan</span>
                <div class="tw-relative">
                    <input id="report-filter-search" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" type="search" placeholder="Nomor laporan, siklus, auditee...">
                </div>
            </label>
            <label class="tw-block">
                <span class="tw-mb-1 tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-slate-500">Siklus</span>
                <select id="report-filter-cycle" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none">
                    <option value="">Semua siklus</option>
                    <?php
                    $seen_cycles = [];
                    foreach ($reports as $r) {
                        $cycle_key = trim((string) $r->cycle_code_snapshot);
                        if ($cycle_key !== '' && !isset($seen_cycles[$cycle_key])) {
                            $seen_cycles[$cycle_key] = $r->cycle_code_snapshot . ' — ' . $r->cycle_title_snapshot;
                            echo '<option value="' . html_escape($cycle_key) . '">' . html_escape($seen_cycles[$cycle_key]) . '</option>';
                        }
                    }
                    ?>
                </select>
            </label>
        </section>

        <?php if (empty($reports)): ?>
            <div class="tw-rounded-2xl tw-border tw-border-dashed tw-border-slate-300 tw-bg-white tw-p-12 tw-text-center">
                <div class="tw-mx-auto tw-mb-3 tw-flex tw-h-12 tw-w-12 tw-items-center tw-justify-center tw-rounded-full tw-bg-slate-100 tw-text-slate-400">
                    <?php echo $icon('file-text'); ?>
                </div>
                <h2 class="tw-text-base tw-font-bold tw-text-slate-900">Belum ada laporan SPMI</h2>
                <p class="tw-mt-1.5 tw-text-sm tw-text-slate-500">Laporan dibuat secara otomatis dari assessment M9 yang telah selesai dan difinalisasi.</p>
            </div>
        <?php else: ?>
            <!-- Desktop Table View -->
            <div class="tw-hidden md:tw-block tw-overflow-hidden tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-shadow-sm">
                <div class="tw-overflow-x-auto">
                    <table class="tw-w-full tw-text-left tw-text-sm">
                        <thead class="tw-border-b tw-border-slate-200 tw-bg-slate-50 tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wider tw-text-slate-500">
                            <tr>
                                <th class="tw-px-5 tw-py-4">Nomor Laporan</th>
                                <th class="tw-px-5 tw-py-4">Siklus</th>
                                <th class="tw-px-5 tw-py-4">Auditee</th>
                                <th class="tw-px-5 tw-py-4">Finalisasi</th>
                                <th class="tw-px-5 tw-py-4 tw-text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="report-list-table" class="tw-divide-y tw-divide-slate-100">
                            <?php foreach ($reports as $report): ?>
                                <tr class="report-row hover:tw-bg-slate-50"
                                    data-search="<?php echo html_escape(strtolower($report->report_number . ' ' . $report->cycle_code_snapshot . ' ' . $report->cycle_title_snapshot . ' ' . $report->auditee_name_snapshot)); ?>"
                                    data-cycle="<?php echo html_escape(trim((string) $report->cycle_code_snapshot)); ?>">
                                    <td class="tw-px-5 tw-py-4">
                                        <span class="tw-font-mono tw-font-bold tw-text-sm tw-text-slate-900 tw-bg-slate-100 tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200">
                                            <?php echo html_escape($report->report_number); ?>
                                        </span>
                                    </td>
                                    <td class="tw-px-5 tw-py-4 tw-text-slate-700">
                                        <div class="tw-font-medium tw-text-slate-900"><?php echo html_escape($report->cycle_code_snapshot); ?></div>
                                        <div class="tw-text-xs tw-text-slate-500"><?php echo html_escape($report->cycle_title_snapshot); ?></div>
                                    </td>
                                    <td class="tw-px-5 tw-py-4 tw-font-medium tw-text-slate-800">
                                        <?php echo html_escape($report->auditee_name_snapshot); ?>
                                    </td>
                                    <td class="tw-px-5 tw-py-4 tw-whitespace-nowrap tw-text-xs tw-text-slate-500">
                                        <?php echo html_escape($report->assessment_finalized_at_snapshot); ?>
                                    </td>
                                    <td class="tw-px-5 tw-py-4 tw-text-right">
                                        <div class="ami-row-actions tw-inline-flex tw-justify-end">
                                            <a class="ami-action-btn tw-button-secondary tw-text-xs" href="<?php echo site_url('lpmpi/spmi-reports/detail/' . (int) $report->id); ?>">
                                                <span>Detail</span>
                                                <?php echo $icon('arrow-right'); ?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Cards View -->
            <div id="report-list-cards" class="tw-grid tw-gap-3 md:tw-hidden">
                <?php foreach ($reports as $report): ?>
                    <article class="report-row tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-5 tw-shadow-sm"
                             data-search="<?php echo html_escape(strtolower($report->report_number . ' ' . $report->cycle_code_snapshot . ' ' . $report->cycle_title_snapshot . ' ' . $report->auditee_name_snapshot)); ?>"
                             data-cycle="<?php echo html_escape(trim((string) $report->cycle_code_snapshot)); ?>">
                        <div class="tw-flex tw-items-center tw-justify-between tw-gap-2 tw-mb-3">
                            <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-slate-100 tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200">
                                <?php echo html_escape($report->report_number); ?>
                            </span>
                            <span class="tw-text-xs tw-text-slate-500">
                                <?php echo html_escape($report->assessment_finalized_at_snapshot); ?>
                            </span>
                        </div>

                        <h3 class="tw-text-sm tw-font-bold tw-text-slate-900 tw-m-0">
                            <?php echo html_escape($report->cycle_code_snapshot . ' — ' . $report->cycle_title_snapshot); ?>
                        </h3>

                        <div class="tw-mt-2 tw-flex tw-items-center tw-gap-2 tw-text-xs tw-text-slate-600">
                            <?php echo $icon('user'); ?>
                            <span>Auditee: <strong><?php echo html_escape($report->auditee_name_snapshot); ?></strong></span>
                        </div>

                        <div class="tw-mt-4 tw-pt-3 tw-border-t tw-border-slate-100">
                            <div class="ami-row-actions">
                                <a class="ami-action-btn tw-button-secondary tw-w-full tw-min-h-[44px] tw-text-sm tw-justify-center" href="<?php echo site_url('lpmpi/spmi-reports/detail/' . (int) $report->id); ?>">
                                    <span>Detail Laporan</span>
                                    <?php echo $icon('arrow-right'); ?>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Empty Filter Notice -->
            <p id="report-filter-empty" class="tw-hidden tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-8 tw-text-center tw-text-sm tw-text-slate-500 tw-mt-4">
                Tidak ada laporan yang sesuai dengan filter pencarian.
            </p>
        <?php endif; ?>
    </div>
</main>

<script>
(function () {
    var rows = Array.prototype.slice.call(document.querySelectorAll('.report-row'));
    var search = document.getElementById('report-filter-search');
    var cycle = document.getElementById('report-filter-cycle');
    var empty = document.getElementById('report-filter-empty');

    function filter() {
        var query = search ? search.value.trim().toLowerCase() : '';
        var selectedCycle = cycle ? cycle.value.trim() : '';

        var visibleCount = 0;
        rows.forEach(function (row) {
            var text = row.getAttribute('data-search') || '';
            var rowCycle = row.getAttribute('data-cycle') || '';

            var matchSearch = !query || text.indexOf(query) !== -1;
            var matchCycle = !selectedCycle || rowCycle === selectedCycle;

            if (matchSearch && matchCycle) {
                row.classList.remove('tw-hidden');
                visibleCount++;
            } else {
                row.classList.add('tw-hidden');
            }
        });

        if (empty) {
            if (visibleCount === 0 && rows.length > 0) {
                empty.classList.remove('tw-hidden');
            } else {
                empty.classList.add('tw-hidden');
            }
        }
    }

    if (search) search.addEventListener('input', filter);
    if (cycle) cycle.addEventListener('change', filter);
})();
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
