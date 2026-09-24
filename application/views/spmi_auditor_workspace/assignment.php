<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
$readonly = $assignment->state === 'closed' || !$assessment || $assessment->status === 'finalized' || !in_array($assignment->submission_status, ['submitted', 'resubmitted'], TRUE);
$version = $assessment ? (int) $assessment->version : 0;
$upload_forms = [];
$delete_forms = [];

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        'lock' => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'alert-triangle' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'external-link' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>',
        'file' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/>',
        'paperclip' => '<path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/>',
        'history' => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>',
        'save' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>',
        'rotate-ccw' => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>',
        'trash' => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
    ];
    return '<svg class="aud-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['file']) . '</svg>';
};
?>
<main id="auditor-root" data-ui-contract="ami-row-actions ami-action-btn" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-7xl">
        <!-- Top Back Action -->
        <div class="ami-row-actions no-print tw-mb-5">
            <a class="ami-action-btn aud-back-link tw-text-sm" href="<?php echo site_url('auditor/spmi'); ?>">
                <?php echo $icon('arrow-left'); ?>
                <span>Kembali ke Penilaian SPMI</span>
            </a>
        </div>

        <!-- Sticky Header / Workspace Summary Card -->
        <header class="tw-sticky tw-top-4 tw-z-30 tw-mb-6 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white/95 tw-backdrop-blur-md tw-p-6 tw-shadow-sm">
            <div class="tw-flex tw-flex-col lg:tw-flex-row lg:tw-items-center lg:tw-justify-between tw-gap-4">
                <div class="tw-min-w-0">
                    <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2.5 tw-mb-2">
                        <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-slate-100 tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200">
                            <?php echo html_escape($assignment->source_standard_code); ?>
                        </span>
                        <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-slate-100 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-semibold tw-text-slate-700">
                            Auditee: <?php echo html_escape($assignment->auditee_name); ?>
                        </span>
                        <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-blue-50 tw-border tw-border-blue-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-blue-800">
                            Submission v<?php echo html_escape((string) $assignment->submission_version); ?> (<?php echo html_escape($assignment->submission_status); ?>)
                        </span>
                        <?php if ($assessment && $assessment->status === 'finalized'): ?>
                            <span class="tw-inline-flex tw-items-center tw-gap-1 tw-rounded-full tw-bg-emerald-50 tw-border tw-border-emerald-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-emerald-800">
                                <?php echo $icon('check'); ?>
                                <span>Penilaian Finalized</span>
                            </span>
                        <?php else: ?>
                            <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-amber-50 tw-border tw-border-amber-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-amber-800">
                                Penilaian Draft
                            </span>
                        <?php endif; ?>
                    </div>
                    <h1 class="tw-text-xl sm:tw-text-2xl tw-font-bold tw-tracking-tight tw-text-slate-950 tw-m-0">
                        <?php echo html_escape($assignment->source_standard_code . ' — ' . $assignment->source_standard_title); ?>
                    </h1>
                </div>

                <!-- Quick Action Buttons -->
                <?php if (!$readonly): ?>
                    <div class="tw-flex tw-items-center tw-gap-2.5 tw-flex-shrink-0">
                        <button type="submit" form="assessment-batch-form" class="btn btn-primary btn-ami tw-button-secondary tw-text-xs">
                            <?php echo $icon('save'); ?>
                            <span>Simpan draft</span>
                        </button>
                        <button type="submit" form="assessment-batch-form" formaction="<?php echo site_url('auditor/spmi/assignment/' . (int) $assignment->id . '/finalize'); ?>" onclick="return confirm('Apakah Anda yakin ingin memfinalisasi penilaian ini? Seluruh skor dan temuan akan dikunci permanen untuk Laporan SPMI.');" class="btn btn-primary btn-ami tw-button-primary tw-text-xs">
                            <?php echo $icon('check'); ?>
                            <span>Finalisasi</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>


            <!-- Status Banner Callouts -->
            <?php if ($assessment && $assessment->status === 'finalized'): ?>
                <div class="tw-mt-4 tw-flex tw-items-center tw-gap-2 tw-rounded-xl tw-border tw-border-emerald-200 tw-bg-emerald-50/80 tw-px-3.5 tw-py-2.5 tw-text-xs tw-text-emerald-900">
                    <span class="tw-text-emerald-600"><?php echo $icon('lock'); ?></span>
                    <span>Penilaian SPMI telah difinalisasi. Seluruh data terkunci permanen dan menjadi arsip resmi laporan.</span>
                </div>
            <?php elseif ($assignment->submission_status === 'returned_for_revision'): ?>
                <div class="tw-mt-4 tw-flex tw-items-center tw-gap-2 tw-rounded-xl tw-border tw-border-amber-200 tw-bg-amber-50/80 tw-px-3.5 tw-py-2.5 tw-text-xs tw-text-amber-900">
                    <span class="tw-text-amber-600"><?php echo $icon('alert-triangle'); ?></span>
                    <span>Penugasan ini telah dikembalikan untuk revisi. Ruang kerja berstatus hanya-baca sampai auditee mengirim ulang perbaikan.</span>
                </div>
            <?php endif; ?>
        </header>

        <!-- Main Batch Form Opening -->
        <?php if (!$readonly): ?>
            <?php echo form_open('auditor/spmi/assignment/' . (int) $assignment->id . '/save', ['id' => 'assessment-batch-form']); ?>
            <input type="hidden" name="version" value="<?php echo html_escape((string) $version); ?>">
            <input type="hidden" name="source_submission_version" value="<?php echo html_escape((string) $assignment->submission_version); ?>">
        <?php endif; ?>

        <!-- List of Assessment Items -->
        <div class="tw-space-y-6">
            <?php foreach ($items as $item):
                $current_score = $item->assessment ? $item->assessment->score : '';
                $current_finding_type = $item->assessment ? (string) $item->assessment->finding_type : '';
                $upload_form_id = 'auditor-evidence-upload-' . (int) $item->id;
                $evidence_url = trim((string) $item->evidence_url);
                $evidence_scheme = strtolower((string) parse_url($evidence_url, PHP_URL_SCHEME));
                $is_evidence_link = $evidence_url !== '' && filter_var($evidence_url, FILTER_VALIDATE_URL) && in_array($evidence_scheme, ['http', 'https'], TRUE);
            ?>
                <article class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-shadow-sm tw-overflow-hidden" <?php if (!$readonly): ?> data-autosave-card="<?php echo (int) ($item->assessment ? $item->assessment->id : 0); ?>"<?php endif; ?>>
                    <!-- Item Header -->
                    <div class="tw-border-b tw-border-slate-100 tw-bg-slate-50/70 tw-p-5 tw-flex tw-items-center tw-justify-between tw-gap-3">
                        <div class="tw-flex tw-items-center tw-gap-2.5">
                            <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-white tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200">
Butir #<?php echo html_escape((string) $item->display_order); ?>: <?php echo html_escape($item->indicator_code); ?>
                            </span>
                            <?php if ($item->assessment && $item->assessment->score): ?>
                                <span class="tw-inline-flex tw-items-center tw-gap-1 tw-rounded-full tw-bg-blue-50 tw-border tw-border-blue-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-blue-800">
                                    Skor: <?php echo html_escape($item->assessment->score); ?> / 4
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if (!$readonly): ?>
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <span role="status" aria-live="polite" data-autosave-status class="tw-text-xs tw-text-slate-500"></span>
                                <button type="button" class="btn btn-outline-ami btn-ami tw-button-secondary tw-text-xs tw-py-1 tw-px-2.5" data-autosave-item="<?php echo (int) ($item->assessment ? $item->assessment->id : 0); ?>">
                                    <span>Simpan item</span>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- 2-Column Responsive Body -->
                    <div class="tw-grid tw-gap-6 lg:tw-grid-cols-2 tw-p-6">
                        <!-- LEFT COLUMN: Context & Auditee Evidence (Reference) -->
                        <div class="tw-space-y-4 tw-border-b lg:tw-border-b-0 lg:tw-border-r tw-border-slate-100 tw-pb-6 lg:tw-pb-0 lg:tw-pr-6">
                            <!-- Indikator -->
                            <div>
                                <span class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-400 tw-mb-1">
                                    Indikator:
                                </span>
                                <p class="tw-text-sm tw-font-semibold tw-text-slate-900 tw-leading-relaxed tw-m-0">
<?php echo html_escape($item->indicator_code . ' — ' . $item->indicator_title); ?>
                                </p>
                            </div>

                            <!-- Instruksi Bukti -->
                            <?php if (!empty($item->evidence_instruction)): ?>
                                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100">
                                    <strong class="tw-block tw-text-xs tw-text-slate-600 tw-mb-1">Instruksi bukti:</strong>
                                    <p class="tw-text-xs tw-text-slate-700 tw-leading-relaxed tw-m-0">
                                        <?php echo nl2br(html_escape($item->evidence_instruction)); ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <!-- Realisasi Submitted Auditee -->
                            <div class="tw-rounded-xl tw-border tw-border-blue-100 tw-bg-blue-50/40 tw-p-4">
                                <strong class="tw-block tw-text-xs tw-text-blue-900 tw-mb-1">Realisasi submitted:</strong>
                                <p class="tw-text-xs tw-text-slate-800 tw-leading-relaxed tw-m-0">
                                    <?php echo nl2br(html_escape($item->assessment ? $item->assessment->realization_snapshot : '')); ?>
                                </p>
                            </div>

                            <!-- Link Bukti Auditee -->
                            <div class="tw-text-xs">
                                <span class="tw-text-slate-500">Link bukti: </span>
                                <?php if ($evidence_url === ''): ?>
                                    <span class="tw-text-slate-400 tw-italic">Tidak ada link bukti</span>
                                <?php elseif ($is_evidence_link): ?>
                                    <a href="<?php echo html_escape($evidence_url); ?>" target="_blank" rel="noopener noreferrer" class="tw-font-medium tw-text-blue-600 hover:tw-underline tw-break-all tw-inline-flex tw-items-center tw-gap-1">
                                        <span><?php echo html_escape($evidence_url); ?></span>
                                        <?php echo $icon('external-link'); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="tw-text-slate-400 tw-italic">Link bukti tidak valid</span>
                                <?php endif; ?>
                            </div>

                            <!-- Berkas Bukti Auditee -->
                            <div>
                                <span class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-400 tw-mb-2">
                                    Bukti auditee:
                                </span>
                                <?php if (empty($item->evidence)): ?>
                                    <p class="tw-text-xs tw-text-slate-400 tw-italic tw-m-0">Tidak ada berkas bukti auditee.</p>
                                <?php else: ?>
                                    <ul class="tw-space-y-1.5 tw-p-0 tw-m-0 tw-list-none">
                                        <?php foreach ($item->evidence as $evidence): ?>
                                            <li class="tw-flex tw-items-center tw-gap-2 tw-rounded-lg tw-border tw-border-slate-200 tw-bg-white tw-px-3 tw-py-2 tw-text-xs">
                                                <span class="tw-text-slate-400"><?php echo $icon('file'); ?></span>
                                                <a href="<?php echo site_url('auditor/spmi/evidence/' . (int) $evidence->id . '/download'); ?>" class="tw-font-medium tw-text-blue-600 hover:tw-underline tw-truncate">
                                                    <?php echo html_escape($evidence->original_name); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>

                            <!-- Rubrik Penilaian -->
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

                        <!-- RIGHT COLUMN: Auditor Assessment (Editable / Read-only) -->
                        <div class="tw-space-y-4">
                            <span class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-blue-600 tw-mb-2">
                                Evaluasi &amp; Penilaian Auditor
                            </span>

                            <?php if ($readonly): ?>
                                <div class="tw-grid tw-gap-3 sm:tw-grid-cols-2">
                                    <div>
                                        <label for="score-<?php echo (int) $item->id; ?>" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Skor</label>
                                        <select class="tw-w-full tw-rounded-lg tw-border tw-border-slate-200 tw-bg-slate-50 tw-px-3 tw-py-2 tw-text-xs tw-text-slate-700" id="score-<?php echo (int) $item->id; ?>" disabled>
                                            <option value=""><?php echo html_escape((string) $current_score ?: 'Belum dinilai'); ?></option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="finding-type-<?php echo (int) $item->id; ?>" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Jenis temuan</label>
                                        <select class="tw-w-full tw-rounded-lg tw-border tw-border-slate-200 tw-bg-slate-50 tw-px-3 tw-py-2 tw-text-xs tw-text-slate-700" id="finding-type-<?php echo (int) $item->id; ?>" disabled>
                                            <option value=""><?php echo html_escape(strtoupper($current_finding_type) ?: 'Tidak ada'); ?></option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label for="finding-<?php echo (int) $item->id; ?>" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Temuan</label>
                                    <textarea class="tw-w-full tw-rounded-lg tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-2.5 tw-text-xs tw-text-slate-700" id="finding-<?php echo (int) $item->id; ?>" rows="2" disabled><?php echo html_escape($item->assessment ? $item->assessment->finding : ''); ?></textarea>
                                </div>

                                <div>
                                    <label for="recommendation-<?php echo (int) $item->id; ?>" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Rekomendasi</label>
                                    <textarea class="tw-w-full tw-rounded-lg tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-2.5 tw-text-xs tw-text-slate-700" id="recommendation-<?php echo (int) $item->id; ?>" rows="2" disabled><?php echo html_escape($item->assessment ? $item->assessment->recommendation : ''); ?></textarea>
                                </div>

                                <div>
                                    <label for="improvement-plan-<?php echo (int) $item->id; ?>" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Rencana perbaikan</label>
                                    <textarea class="tw-w-full tw-rounded-lg tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-2.5 tw-text-xs tw-text-slate-700" id="improvement-plan-<?php echo (int) $item->id; ?>" rows="2" disabled><?php echo html_escape($item->assessment ? $item->assessment->improvement_plan : ''); ?></textarea>
                                </div>

                                <div>
                                    <label for="evidence-date-<?php echo (int) $item->id; ?>" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Tanggal bukti</label>
                                    <input class="tw-w-full sm:tw-w-48 tw-rounded-lg tw-border tw-border-slate-200 tw-bg-slate-50 tw-px-3 tw-py-2 tw-text-xs tw-text-slate-700" id="evidence-date-<?php echo (int) $item->id; ?>" type="date" disabled value="<?php echo html_escape($item->assessment ? $item->assessment->evidence_date : ''); ?>">
                                </div>
                            <?php else: ?>
                                <div class="tw-grid tw-gap-3 sm:tw-grid-cols-2">
                                    <div>
                                        <label for="score-<?php echo (int) $item->id; ?>" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1">
                                            Skor <span class="tw-text-red-500">*</span>
                                        </label>
                                        <select class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2 tw-text-xs tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" id="score-<?php echo (int) $item->id; ?>" name="assessment[<?php echo (int) $item->id; ?>][score]" data-autosave-field="score">
                                            <option value="">Pilih skor</option>
                                            <?php for ($score = 1; $score <= 4; $score++): ?>
                                                <option value="<?php echo $score; ?>" <?php echo (string) $current_score === (string) $score ? 'selected' : ''; ?>>
                                                    Skor <?php echo $score; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="finding-type-<?php echo (int) $item->id; ?>" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1">
                                            Jenis temuan
                                        </label>
                                        <select class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2 tw-text-xs tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" id="finding-type-<?php echo (int) $item->id; ?>" name="assessment[<?php echo (int) $item->id; ?>][finding_type]" data-autosave-field="finding_type">
                                            <option value="" <?php echo $current_finding_type === '' ? 'selected' : ''; ?>>Tidak ada</option>
                                            <option value="ob" <?php echo $current_finding_type === 'ob' ? 'selected' : ''; ?>>OB (Observasi)</option>
                                            <option value="kts" <?php echo $current_finding_type === 'kts' ? 'selected' : ''; ?>>KTS (Ketidaksesuaian)</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label for="finding-<?php echo (int) $item->id; ?>" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1">
                                        Temuan
                                    </label>
                                    <textarea class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-p-2.5 tw-text-xs tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" id="finding-<?php echo (int) $item->id; ?>" name="assessment[<?php echo (int) $item->id; ?>][finding]" rows="2" placeholder="Uraian temuan auditor..." data-autosave-field="finding"><?php echo html_escape($item->assessment ? $item->assessment->finding : ''); ?></textarea>
                                </div>

                                <div>
                                    <label for="recommendation-<?php echo (int) $item->id; ?>" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1">
                                        Rekomendasi
                                    </label>
                                    <textarea class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-p-2.5 tw-text-xs tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" id="recommendation-<?php echo (int) $item->id; ?>" name="assessment[<?php echo (int) $item->id; ?>][recommendation]" rows="2" placeholder="Rekomendasi tindakan..." data-autosave-field="recommendation"><?php echo html_escape($item->assessment ? $item->assessment->recommendation : ''); ?></textarea>
                                </div>

                                <div>
                                    <label for="improvement-plan-<?php echo (int) $item->id; ?>" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1">
                                        Rencana perbaikan
                                    </label>
                                    <textarea class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-p-2.5 tw-text-xs tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" id="improvement-plan-<?php echo (int) $item->id; ?>" name="assessment[<?php echo (int) $item->id; ?>][improvement_plan]" rows="2" placeholder="Rencana tindak perbaikan..." data-autosave-field="improvement_plan"><?php echo html_escape($item->assessment ? $item->assessment->improvement_plan : ''); ?></textarea>
                                </div>

                                <div>
                                    <label for="evidence-date-<?php echo (int) $item->id; ?>" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1">
                                        Tanggal bukti
                                    </label>
                                    <input class="tw-w-full sm:tw-w-48 tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2 tw-text-xs tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" id="evidence-date-<?php echo (int) $item->id; ?>" type="date" name="assessment[<?php echo (int) $item->id; ?>][evidence_date]" value="<?php echo html_escape($item->assessment ? $item->assessment->evidence_date : ''); ?>" data-autosave-field="evidence_date">
                                </div>
                            <?php endif; ?>

                            <!-- Bukti Auditor File List & Actions -->
                            <div class="tw-pt-3 tw-border-t tw-border-slate-100">
                                <span class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-600 tw-mb-2">
                                    Bukti auditor:
                                </span>
                                <?php if (empty($item->auditor_evidence)): ?>
                                    <p class="tw-text-xs tw-text-slate-400 tw-italic tw-mb-3">Belum ada bukti tambahan auditor. PDF, JPG, PNG maksimal <?php echo html_escape((string) $upload_limit_mib); ?> MiB.</p>
                                <?php else: ?>
                                    <ul class="tw-space-y-2 tw-p-0 tw-mb-3 tw-list-none">
                                        <?php foreach ($item->auditor_evidence as $evidence): ?>
                                            <li class="tw-flex tw-items-center tw-justify-between tw-gap-2 tw-rounded-lg tw-border tw-border-slate-200 tw-bg-slate-50 tw-px-3 tw-py-2 tw-text-xs">
                                                <div class="tw-flex tw-items-center tw-gap-2 tw-min-w-0">
                                                    <span class="tw-text-blue-600"><?php echo $icon('paperclip'); ?></span>
                                                    <a href="<?php echo site_url('auditor/spmi/auditor-evidence/' . (int) $evidence->id . '/download'); ?>" class="tw-font-medium tw-text-blue-600 hover:tw-underline tw-truncate">
                                                        <?php echo html_escape($evidence->original_name); ?>
                                                    </a>
                                                    <span class="tw-text-[11px] tw-text-slate-400 tw-whitespace-nowrap">
                                                        (<?php echo html_escape((string) round($evidence->size_bytes / 1024, 1)); ?> KB)
                                                    </span>
                                                </div>

                                                <?php if (!$readonly): ?>
                                                    <?php $delete_form_id = 'auditor-evidence-delete-' . (int) $evidence->id; ob_start(); ?>
                                                    <?php echo form_open('auditor/spmi/auditor-evidence/' . (int) $evidence->id . '/delete', ['class' => 'tw-hidden', 'id' => $delete_form_id]); ?>
                                                        <input type="hidden" name="version" value="<?php echo html_escape((string) $version); ?>">
                                                        <input type="hidden" name="source_submission_version" value="<?php echo html_escape((string) $assignment->submission_version); ?>">
                                                    <?php echo form_close(); ?>
                                                    <?php $delete_forms[] = ob_get_clean(); ?>
                                                    <button type="submit" form="<?php echo $delete_form_id; ?>" class="btn btn-ami ami-action-btn danger tw-text-[11px] tw-text-red-600 hover:tw-underline tw-bg-transparent tw-border-0 tw-p-0">
                                                        Hapus
                                                    </button>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>

                                <!-- Upload Bukti Auditor Form -->
                                <?php if (!$readonly && $item->assessment): ?>
                                    <?php ob_start(); ?>
                                    <?php echo form_open_multipart('auditor/spmi/assessment-item/' . (int) $item->assessment->id . '/evidence/upload', ['id' => $upload_form_id, 'class' => 'tw-hidden']); ?>
                                        <input type="hidden" name="version" value="<?php echo html_escape((string) $version); ?>">
                                        <input type="hidden" name="source_submission_version" value="<?php echo html_escape((string) $assignment->submission_version); ?>">
                                    <?php echo form_close(); ?>
                                    <?php $upload_forms[] = ob_get_clean(); ?>

                                    <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center tw-gap-2">
                                        <input class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-2.5 tw-py-1.5 tw-text-xs tw-text-slate-700" type="file" name="evidence" accept="application/pdf,image/jpeg,image/png" required form="<?php echo $upload_form_id; ?>">
                                        <button type="submit" form="<?php echo $upload_form_id; ?>" class="btn btn-primary btn-ami tw-button-secondary tw-text-xs tw-whitespace-nowrap">
                                            <span>Upload bukti auditor</span>
                                        </button>
                                    </div>
                                    <p class="tw-text-[11px] tw-text-slate-400 tw-mt-1 tw-mb-0">PDF, JPG, PNG maksimal <?php echo html_escape((string) $upload_limit_mib); ?> MiB; maks 5 file.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Global Bottom Actions -->
        <?php if (!$readonly): ?>
            <div class="tw-mt-8 tw-flex tw-items-center tw-justify-end tw-gap-3">
                <button type="submit" class="btn btn-primary btn-ami tw-button-secondary">
                    <?php echo $icon('save'); ?>
                    <span>Simpan draft</span>
                </button>
                <button type="submit" class="btn btn-primary btn-ami tw-button-primary" formaction="<?php echo site_url('auditor/spmi/assignment/' . (int) $assignment->id . '/finalize'); ?>" onclick="return confirm('Apakah Anda yakin ingin memfinalisasi penilaian ini? Seluruh skor dan temuan akan dikunci permanen untuk Laporan SPMI.');">
                    <?php echo $icon('check'); ?>
                    <span>Finalisasi</span>
                </button>
            </div>
            <?php echo form_close(); ?><script>
            (function () {
                var form = document.querySelector('form[action*="/auditor/spmi/assignment/"][action$="/save"]');
                if (!form) return;
                var csrfName = <?php echo json_encode($this->security->get_csrf_token_name()); ?>;
                var urlBase = <?php echo json_encode(site_url('auditor/spmi/item/')); ?>;
                form.querySelectorAll('[data-autosave-item]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var card = button.closest('[data-autosave-card]');
                        var status = card.querySelector('[data-autosave-status]');
                        var data = new FormData();
                        var csrf = form.querySelector('input[name="' + csrfName + '"]');
                        data.append(csrfName, csrf ? csrf.value : '');
                        data.append('version', form.querySelector('input[name="version"]').value);
                        data.append('source_submission_version', form.querySelector('input[name="source_submission_version"]').value);
                        card.querySelectorAll('[data-autosave-field]').forEach(function (field) { data.append(field.dataset.autosaveField, field.value); });
                        status.textContent = 'Menyimpan...';
                        button.disabled = true;
                        fetch(urlBase + button.dataset.autosaveItem + '/save', { method: 'POST', body: data, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(function (response) { return response.json().then(function (json) { return { ok: response.ok, json: json }; }); })
                             .then(function (result) {
                                  var json = result.json;
                                  if (json.csrf && json.csrf.name && json.csrf.hash) document.querySelectorAll('input[name="' + json.csrf.name + '"]').forEach(function (input) { input.value = json.csrf.hash; input.defaultValue = json.csrf.hash; });
                                  if (!result.ok || !json.success) { status.textContent = json.message || 'Item gagal disimpan.'; return; }
                                  document.querySelectorAll('input[name="version"]').forEach(function (input) { input.value = json.version; input.defaultValue = json.version; });
                                  status.textContent = json.message || 'Item tersimpan.';
                              })
                             .catch(function () { status.textContent = 'Item gagal disimpan. Periksa koneksi lalu coba lagi.'; })
                             .finally(function () { button.disabled = false; });
                    });
                });
            }());
            </script>
        <?php endif; ?>

        <!-- Hidden Forms Container -->
        <div class="tw-hidden">
            <?php foreach ($upload_forms as $upload_form): ?><?php echo $upload_form; ?><?php endforeach; ?>
            <?php foreach ($delete_forms as $delete_form): ?><?php echo $delete_form; ?><?php endforeach; ?>
        </div>

        <!-- Section: Kembalikan untuk Revisi -->
        <?php if ($assignment->state !== 'closed' && in_array($assignment->submission_status, ['submitted', 'resubmitted'], TRUE) && (!$assessment || $assessment->status !== 'finalized')): ?>
            <section class="tw-mt-8 tw-rounded-2xl tw-border tw-border-amber-200 tw-bg-amber-50/70 tw-p-6">
                <div class="tw-flex tw-items-center tw-gap-2 tw-mb-2">
                    <span class="tw-text-amber-600"><?php echo $icon('alert-triangle'); ?></span>
                    <h2 class="tw-text-base tw-font-bold tw-text-amber-950 tw-m-0">Kembalikan untuk revisi</h2>
                </div>
                <p class="tw-text-xs tw-text-amber-800 tw-leading-relaxed tw-mb-4">
                    Jika terdapat bukti atau realisasi yang tidak lengkap dan memerlukan perbaikan dari auditee, Anda dapat mengembalikan penugasan ini.
                </p>

                <?php echo form_open('auditor/spmi/assignment/' . (int) $assignment->id . '/return', ['onsubmit' => "return confirm('Apakah Anda yakin ingin mengembalikan penugasan ini untuk revisi? Auditee akan diminta mengunggah perbaikan.');"]); ?>
                    <input type="hidden" name="submission_version" value="<?php echo html_escape((string) $assignment->submission_version); ?>">
                    <div class="tw-mb-3">
                        <label for="revision-reason" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-amber-900 tw-mb-1.5">
                            Alasan revisi <span class="tw-text-red-500">*</span>
                        </label>
                        <textarea class="tw-w-full tw-rounded-lg tw-border tw-border-amber-300 tw-bg-white tw-p-3 tw-text-xs tw-text-slate-900 focus:tw-border-amber-600 focus:tw-outline-none" id="revision-reason" name="reason" rows="3" placeholder="Tuliskan catatan detail dan bagian bukti yang harus diperbaiki oleh auditee..." required></textarea>
                    </div>
                    <div class="tw-flex tw-justify-end">
                        <button type="submit" class="btn btn-ami ami-action-btn danger tw-button-danger tw-text-xs">
                            <?php echo $icon('rotate-ccw'); ?>
                            <span>Kembalikan untuk revisi</span>
                        </button>
                    </div>
                <?php echo form_close(); ?>
            </section>
        <?php endif; ?>

        <!-- Section: Riwayat Revisi Timeline -->
        <section class="tw-mt-8 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm" aria-labelledby="history-title">
            <div class="tw-flex tw-items-center tw-gap-2 tw-mb-4">
                <span class="tw-text-blue-600"><?php echo $icon('history'); ?></span>
                <h2 id="history-title" class="tw-text-base tw-font-bold tw-text-slate-950 tw-m-0">Riwayat revisi</h2>
            </div>

            <?php if (empty($revision_history)): ?>
                <p class="tw-text-xs tw-text-slate-400 tw-italic tw-m-0">Belum ada riwayat revisi.</p>
            <?php else: ?>
                <ol class="tw-space-y-4 tw-p-0 tw-m-0 tw-list-none">
                    <?php foreach ($revision_history as $event): ?>
                        <li class="tw-relative tw-pl-6 tw-border-l-2 tw-border-slate-200 tw-space-y-1">
                            <div class="tw-absolute -tw-left-[5px] tw-top-1.5 tw-h-2 tw-w-2 tw-rounded-full tw-bg-blue-600"></div>
                            <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-xs">
                                <span class="tw-font-mono tw-text-slate-500"><?php echo html_escape($event->created_at); ?></span>
                                <span class="tw-font-bold tw-text-slate-800"><?php echo html_escape($event->previous_status . ' → ' . $event->new_status); ?></span>
                                <span class="tw-inline-flex tw-items-center tw-rounded tw-bg-slate-100 tw-px-2 tw-py-0.5 tw-text-[11px] tw-text-slate-600">
                                    Versi <?php echo html_escape((string) $event->previous_version . ' → ' . (string) $event->resulting_version); ?>
                                </span>
                                <span class="tw-text-slate-500">oleh <?php echo html_escape($event->actor_name ?: $event->actor_email); ?></span>
                            </div>
                            <?php if (!empty($event->reason)): ?>
                                <p class="tw-text-xs tw-text-slate-700 tw-bg-slate-50 tw-p-2.5 tw-rounded-lg tw-border tw-border-slate-100 tw-m-0 tw-mt-1">
                                    <?php echo nl2br(html_escape($event->reason)); ?>
                                </p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
