<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$post_action = $assignment->submission_status === 'returned_for_revision'
    ? 'auditee/spmi/assignment/' . (int) $assignment->id . '/resubmit'
    : 'auditee/spmi/assignment/' . (int) $assignment->id . '/submit';

$preview = isset($preview) && is_array($preview) ? $preview : [];
$preview_realizations = isset($preview['realization']) && is_array($preview['realization']) ? $preview['realization'] : [];
$preview_evidence_urls = isset($preview['evidence_url']) && is_array($preview['evidence_url']) ? $preview['evidence_url'] : [];
$preview_version = isset($preview['version']) && is_scalar($preview['version']) && $preview['version'] !== ''
    ? (string) $preview['version']
    : (string) $assignment->version;

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        'alert-triangle' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'external-link' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>',
        'file' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/>',
        'send' => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
    ];
    return '<svg class="adte-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['file']) . '</svg>';
};
?>

<main id="auditee-root" data-ui-contract="ami-row-actions ami-action-btn" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-4xl">
        <!-- Top Back Action -->
        <div class="ami-row-actions no-print tw-mb-5">
            <a class="ami-action-btn adte-back-link tw-text-sm" href="<?php echo site_url('auditee/spmi/assignment/' . (int) $assignment->id); ?>">
                <?php echo $icon('arrow-left'); ?>
                <span>Kembali mengedit</span>
            </a>
        </div>

        <!-- Header Card -->
        <header class="tw-mb-6 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm">
            <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-slate-100 tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200">
                <?php echo html_escape($assignment->source_standard_code); ?>
            </span>
            <h1 class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950 tw-mt-2 tw-mb-1">
                Konfirmasi Submission
            </h1>
            <p class="tw-text-sm tw-text-slate-600 tw-font-medium tw-m-0">
                <?php echo html_escape($assignment->source_standard_code . ' — ' . $assignment->source_standard_title); ?>
            </p>

            <div class="tw-mt-5 tw-flex tw-items-center tw-gap-2.5 tw-rounded-xl tw-border tw-border-amber-200 tw-bg-amber-50/80 tw-p-4 tw-text-xs tw-text-amber-900">
                <span class="tw-text-amber-600"><?php echo $icon('alert-triangle'); ?></span>
                <span>Periksa seluruh realisasi dan bukti sebelum pengiriman. Halaman ini hanya-baca. Setelah dikirim, pengajuan akan terkunci untuk evaluasi auditor.</span>
            </div>
        </header>

        <?php echo form_open($post_action); ?>
            <input type="hidden" name="version" value="<?php echo html_escape($preview_version); ?>">

            <div class="tw-space-y-6 tw-mb-8">
                <?php foreach ($items as $item):
                    $item_id = (int) $item->assignment_item_id;
                    $realization = array_key_exists($item_id, $preview_realizations) ? $preview_realizations[$item_id] : $item->realization;
                    $evidence_url = array_key_exists($item_id, $preview_evidence_urls) ? $preview_evidence_urls[$item_id] : $item->evidence_url;
                    $evidence_scheme = strtolower((string) parse_url((string) $evidence_url, PHP_URL_SCHEME));
                    $is_evidence_link = $evidence_url !== '' && filter_var($evidence_url, FILTER_VALIDATE_URL) !== FALSE && in_array($evidence_scheme, ['http', 'https'], TRUE);
                    $policy_labels = [
                        'none' => 'Tidak memerlukan bukti',
                        'file' => 'Berkas',
                        'url' => 'Tautan',
                        'either' => 'Berkas atau tautan',
                        'both' => 'Berkas dan tautan',
                    ];
                    $clean_policy = isset($policy_labels[$item->evidence_policy]) ? $policy_labels[$item->evidence_policy] : $item->evidence_policy;
                ?>
                    <input type="hidden" name="realization[<?php echo $item_id; ?>]" value="<?php echo html_escape((string) $realization); ?>">
                    <input type="hidden" name="evidence_url[<?php echo $item_id; ?>]" value="<?php echo html_escape((string) $evidence_url); ?>">

                    <article class="card mb-3 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-shadow-sm tw-overflow-hidden">
                        <div class="tw-border-b tw-border-slate-100 tw-bg-slate-50/70 tw-p-4 tw-flex tw-items-center tw-justify-between">
                            <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900">
Butir #<?php echo html_escape((string) $item->display_order); ?>: <?php echo html_escape($item->indicator_code); ?>
                            </span>
                            <span class="tw-text-[11px] tw-text-slate-500">
                                Kebijakan: <?php echo html_escape($clean_policy); ?>
                            </span>
                        </div>

                        <div class="card-body tw-p-6 tw-space-y-4">
                            <div>
<h3><?php echo html_escape((string) $item->display_order . '. ' . $item->indicator_code . ' — ' . $item->indicator_title); ?></h3>
                                <p class="tw-text-xs tw-font-semibold tw-text-slate-800 tw-leading-relaxed tw-m-0">
                                </p>
                            </div>

                            <?php if (!empty($item->evidence_instruction)): ?>
                                <div class="tw-text-xs tw-text-slate-500">
                                    <strong>Instruksi bukti</strong><br>
                                    <?php echo nl2br(html_escape($item->evidence_instruction)); ?>
                                </div>
                            <?php endif; ?>

                            <div class="tw-rounded-lg tw-bg-slate-50/50 tw-p-2 tw-border tw-border-slate-100 tw-text-xs tw-text-slate-500">
                                <strong>Kebijakan bukti:</strong> <?php echo html_escape($clean_policy); ?>
                                <span class="tw-sr-only"><?php echo html_escape($item->evidence_policy); ?></span>
                            </div>

                            <div class="tw-rounded-xl tw-border tw-border-blue-100 tw-bg-blue-50/40 tw-p-4 tw-text-xs">
                                <p><strong>Realisasi</strong><br><?php echo nl2br(html_escape((string) $realization)); ?></p>
                            </div>

                            <div class="tw-text-xs">
                                <p><strong>URL bukti</strong><br>
                                <?php if ($evidence_url === ''): ?>
                                    <span class="tw-text-slate-400 tw-italic">Tidak ada URL bukti</span>
                                <?php elseif ($is_evidence_link): ?>
                                    <a href="<?php echo html_escape((string) $evidence_url); ?>" target="_blank" rel="noopener noreferrer" class="tw-font-medium tw-text-blue-600 hover:tw-underline tw-break-all tw-inline-flex tw-items-center tw-gap-1">
                                        <span><?php echo html_escape((string) $evidence_url); ?></span>
                                        <?php echo $icon('external-link'); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="tw-text-slate-400 tw-italic">URL bukti tidak valid</span>
                                <?php endif; ?>
                                </p>
                            </div>

                            <div class="tw-text-xs">
                                <p><strong>Bukti file</strong></p>
                                <ul class="tw-space-y-1.5 tw-p-0 tw-m-0 tw-list-none">
                                    <?php foreach ($item->evidence as $evidence): ?>
                                        <li class="tw-flex tw-items-center tw-gap-2 tw-rounded-lg tw-border tw-border-slate-200 tw-bg-slate-50 tw-px-3 tw-py-2">
                                            <span class="tw-text-blue-600"><?php echo $icon('file'); ?></span>
                                            <a href="<?php echo site_url('auditee/spmi/evidence/' . (int) $evidence->id . '/download'); ?>" class="tw-font-medium tw-text-blue-600 hover:tw-underline tw-truncate">
                                                <?php echo html_escape($evidence->original_name); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                    <?php if (empty($item->evidence)): ?>
                                        <li class="tw-text-slate-400 tw-italic">Tidak ada bukti file</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Confirmation Action Buttons -->
            <div class="tw-flex tw-items-center tw-justify-end tw-gap-3 tw-pt-4 tw-border-t tw-border-slate-200">
                <a href="<?php echo site_url('auditee/spmi/assignment/' . (int) $assignment->id); ?>" class="tw-button-secondary">
                    Kembali mengedit
                </a>
                <button type="submit" class="tw-button-primary">
                    <?php echo $icon('send'); ?>
                    <span>Kirim submission</span>
                </button>
            </div>
        <?php echo form_close(); ?>
    </div>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
