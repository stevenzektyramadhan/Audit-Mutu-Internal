<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<path d="m15 18-6-6 6-6"/>',
        'edit' => '<path d="m4 16-1 4 4-1L18 8l-3-3L4 16ZM13 6l3 3M20 4l1 1"/>',
        'trash' => '<path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
        'help-circle' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01"/>',
    ];
    return '<svg class="inst-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['help-circle']) . '</svg>';
};

$back_url = site_url('lpmpi/spmi-instruments/package/detail/' . (int) $question->package_id);
$policy_label = strtoupper(!empty($question->evidence_policy) ? $question->evidence_policy : 'none');
?>

<div id="instruments-root" class="tw-mx-auto tw-max-w-screen-2xl">
    <!-- Back Link -->
    <div class="tw-mb-4">
        <a href="<?php echo $back_url; ?>" class="inst-button inst-button-secondary tw-text-xs">
            <?php echo $icon('arrow-left'); ?> Kembali ke Detail Paket
        </a>
    </div>

    <!-- Detail Pertanyaan Card -->
    <section class="inst-surface form-shell" style="max-width: 48rem;">
        <div class="inst-surface-body">
            <div class="form-header">
                <div class="tw-flex tw-items-center tw-justify-between tw-gap-3 tw-flex-wrap">
                    <div>
                        <p class="inst-eyebrow">Rincian Pertanyaan Audit</p>
                        <h2 class="tw-text-2xl tw-font-bold tw-m-0 tw-text-slate-800">
                            #<?php echo (int) $question->display_order; ?> &middot; <?php echo html_escape($question->question_code); ?>
                        </h2>
                    </div>
                    <?php if ($mutable): ?>
                        <div class="tw-flex tw-items-center tw-gap-2">
                            <a class="inst-button inst-button-secondary tw-text-xs" href="<?php echo site_url('lpmpi/spmi-instruments/question/edit/' . (int) $question->id); ?>">
                                <?php echo $icon('edit'); ?> Edit
                            </a>
                            <?php if ($mutable): ?>
                                <?php echo form_open('lpmpi/spmi-instruments/question/delete/' . (int) $question->id, ['class' => 'tw-inline', 'onsubmit' => "return confirm('Hapus pertanyaan ini? Pertanyaan hanya dapat dihapus jika belum memiliki rubrik.');"]); ?>
                                    <button type="submit" class="inst-button inst-button-danger tw-text-xs">
                                        <?php echo $icon('trash'); ?> Hapus pertanyaan
                                    </button>
                                <?php echo form_close(); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <p class="form-subtitle">
                    Paket: <strong><?php echo html_escape($question->package_code . ' — ' . $question->package_title); ?></strong>
                </p>
                <?php if (!$mutable): ?>
                    <p class="inst-muted tw-text-xs tw-m-0 tw-italic tw-pt-1">Hanya-baca: versi approved, active, dan retired tidak dapat diubah.</p>
                <?php endif; ?>
            </div>

            <!-- Content Details -->
            <div class="tw-space-y-5">
                <div class="tw-border tw-border-slate-200 tw-rounded-lg tw-p-4 tw-bg-slate-50">
                    <span class="inst-muted tw-text-xs tw-font-bold tw-block tw-mb-1 tw-uppercase tw-tracking-wider">Teks Pertanyaan</span>
                    <p class="tw-text-sm tw-font-bold tw-text-slate-900 tw-m-0 tw-leading-relaxed">
                        <?php echo nl2br(html_escape($question->question_text)); ?>
                    </p>
                </div>

                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                    <div class="tw-border tw-border-slate-200 tw-rounded-lg tw-p-4 tw-bg-white">
                        <span class="inst-muted tw-text-xs tw-font-bold tw-block tw-mb-1 tw-uppercase tw-tracking-wider">Indikator Terkait</span>
                        <strong class="tw-text-xs tw-text-slate-800 tw-block">
                            <?php echo html_escape($question->indicator_code . ' — ' . $question->indicator_title); ?>
                        </strong>
                    </div>

                    <div class="tw-border tw-border-slate-200 tw-rounded-lg tw-p-4 tw-bg-white">
                        <span class="inst-muted tw-text-xs tw-font-bold tw-block tw-mb-1 tw-uppercase tw-tracking-wider">Kebijakan Jenis Bukti</span>
                        <span class="inst-badge inst-badge-policy tw-font-mono tw-text-xs">
                            <?php echo html_escape($question->evidence_policy ?: 'none'); ?>
                        </span>
                    </div>
                </div>

                <div class="tw-border tw-border-slate-200 tw-rounded-lg tw-p-4 tw-bg-white">
                    <span class="inst-muted tw-text-xs tw-font-bold tw-block tw-mb-1 tw-uppercase tw-tracking-wider">Instruksi Verifikasi Bukti</span>
                    <p class="tw-text-xs tw-text-slate-700 tw-m-0 tw-leading-relaxed">
                        <?php echo nl2br(html_escape($question->evidence_instruction)); ?>
                    </p>
                </div>

                <div class="tw-bg-slate-50 tw-p-3.5 tw-rounded-lg tw-border tw-border-slate-200 tw-text-xs inst-muted">
                    Penilaian pertanyaan instrumen ini menggunakan <strong>skala global skor audit 1–4</strong> yang konsisten di seluruh instrumen SPMI.
                </div>
            </div>
        </div>
    </section>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
