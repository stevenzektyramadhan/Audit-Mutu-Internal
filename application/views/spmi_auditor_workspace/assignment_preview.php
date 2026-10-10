<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'file' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/>',
    ];
    return '<svg class="aud-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['file']) . '</svg>';
};
?>
<main id="auditor-root" data-ui-contract="ami-row-actions ami-action-btn" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-7xl">
        <div class="ami-row-actions no-print tw-mb-5">
            <a class="ami-action-btn aud-back-link tw-text-sm" href="<?php echo site_url('auditor/spmi'); ?>">
                <?php echo $icon('arrow-left'); ?>
                <span>Kembali ke Penilaian SPMI</span>
            </a>
        </div>

        <header class="tw-mb-6 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm">
            <div class="tw-flex tw-flex-col lg:tw-flex-row lg:tw-items-start lg:tw-justify-between tw-gap-4">
                <div class="tw-min-w-0">
                    <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2.5 tw-mb-2">
                        <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-slate-100 tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200">
                            <?php echo html_escape($assignment->source_standard_code); ?>
                        </span>
                        <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-slate-100 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-semibold tw-text-slate-700">
                            Auditi: <?php echo html_escape($assignment->auditee_name); ?>
                        </span>
                        <span class="tw-inline-flex tw-items-center tw-gap-1 tw-rounded-full tw-bg-amber-50 tw-border tw-border-amber-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-amber-800">
                            <?php echo $icon('clock'); ?>
                            <span>Menunggu submission auditi</span>
                        </span>
                    </div>
                    <h1 class="tw-text-xl sm:tw-text-2xl tw-font-bold tw-tracking-tight tw-text-slate-950 tw-m-0">
                        <?php echo html_escape($assignment->source_standard_code . ' — ' . $assignment->source_standard_title); ?>
                    </h1>
                    <p class="tw-mt-2 tw-text-sm tw-text-slate-500 tw-mb-0">
                        <?php echo html_escape($assignment->cycle_code . ' — ' . $assignment->cycle_title); ?>
                    </p>
                </div>
            </div>

            <div class="tw-mt-4 tw-flex tw-items-center tw-gap-2 tw-rounded-xl tw-border tw-border-amber-200 tw-bg-amber-50/80 tw-px-3.5 tw-py-2.5 tw-text-xs tw-text-amber-900">
                <span class="tw-text-amber-600"><?php echo $icon('clock'); ?></span>
                <span>Preview ini hanya menampilkan snapshot penugasan. Realisasi, bukti, riwayat revisi, dan penilaian auditor belum tersedia sampai auditi mengirim submission.</span>
            </div>
        </header>

        <div class="tw-space-y-6">
            <?php foreach ($items as $item): ?>
                <article class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-shadow-sm tw-overflow-hidden">
                    <div class="tw-border-b tw-border-slate-100 tw-bg-slate-50/70 tw-p-5">
                        <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-white tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200">
                            Butir #<?php echo html_escape((string) $item->display_order); ?>: <?php echo html_escape($item->indicator_code); ?>
                        </span>
                    </div>

                    <div class="tw-grid tw-gap-6 lg:tw-grid-cols-2 tw-p-6">
                        <div class="tw-space-y-4 tw-border-b lg:tw-border-b-0 lg:tw-border-r tw-border-slate-100 tw-pb-6 lg:tw-pb-0 lg:tw-pr-6">
                            <div>
                                <span class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-400 tw-mb-1">Indikator:</span>
                                <p class="tw-text-sm tw-font-semibold tw-text-slate-900 tw-leading-relaxed tw-m-0">
                                    <?php echo html_escape($item->indicator_code . ' — ' . $item->indicator_title); ?>
                                </p>
                            </div>

                            <?php if (!empty($item->evidence_instruction)): ?>
                                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100">
                                    <strong class="tw-block tw-text-xs tw-text-slate-600 tw-mb-1">Instruksi bukti:</strong>
                                    <p class="tw-text-xs tw-text-slate-700 tw-leading-relaxed tw-m-0">
                                        <?php echo nl2br(html_escape($item->evidence_instruction)); ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <div class="tw-rounded-xl tw-border tw-border-slate-200 tw-bg-slate-50/70 tw-p-4 tw-text-xs">
                                <strong class="tw-block tw-text-slate-700 tw-mb-2">Rubrik Penilaian:</strong>
                                <div class="tw-space-y-1.5">
                                    <?php foreach ($item->rubrics as $rubric): ?>
                                        <div class="tw-flex tw-items-start tw-gap-2">
                                            <span class="tw-inline-flex tw-h-5 tw-w-5 tw-items-center tw-justify-center tw-rounded tw-bg-white tw-border tw-border-slate-200 tw-font-bold tw-text-slate-800 tw-text-[11px] tw-flex-shrink-0">
                                                <?php echo html_escape((string) $rubric->score); ?>
                                            </span>
                                            <span class="tw-text-slate-600 tw-leading-relaxed">
                                                <?php echo html_escape($rubric->descriptor); ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="tw-space-y-4">
                            <span class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-amber-600 tw-mb-2">
                                Status Pengisian Auditi
                            </span>
                            <div class="tw-rounded-xl tw-border tw-border-amber-200 tw-bg-amber-50/70 tw-p-4 tw-text-xs tw-text-amber-900">
                                Auditi belum mengirim submission untuk butir ini. Konten draft auditi, link bukti, berkas bukti, dan catatan penilaian tidak ditampilkan pada preview auditor.
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
