<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<path d="m15 18-6-6 6-6"/>',
        'edit' => '<path d="m4 16-1 4 4-1L18 8l-3-3L4 16ZM13 6l3 3M20 4l1 1"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'trash' => '<path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
        'help-circle' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01"/>',
        'external-link' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
    ];
    return '<svg class="inst-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['help-circle']) . '</svg>';
};

$status = strtolower($package->version_status);
$total_questions = count($questions);
?>

<div id="instruments-root" class="tw-mx-auto tw-max-w-screen-2xl">
    <!-- Back Link -->
    <div class="tw-mb-4">
        <a href="<?php echo site_url('lpmpi/spmi-instruments'); ?>" class="inst-button inst-button-secondary tw-text-xs">
            <?php echo $icon('arrow-left'); ?> Kembali ke Daftar Paket
        </a>
    </div>

    <!-- Hero Card Paket -->
    <section class="inst-hero tw-mb-6 tw-rounded-lg tw-border tw-p-5 sm:tw-p-6">
        <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-start sm:tw-justify-between tw-gap-4">
            <div class="tw-space-y-2 tw-flex-1">
                <div class="tw-flex tw-items-center tw-gap-2 tw-flex-wrap">
                    <span class="tw-font-mono tw-font-bold tw-text-xs tw-bg-white tw-border tw-border-slate-300 tw-px-2.5 tw-py-0.5 tw-rounded tw-text-slate-800">
                        <?php echo html_escape($package->package_code); ?>
                    </span>
                    <span class="inst-badge inst-badge-<?php echo $status; ?>">
                        Status Versi: <?php echo html_escape(ucfirst($package->version_status)); ?>
                    </span>
                    <span class="inst-muted tw-text-xs">
                        Standar: <strong><?php echo html_escape($package->standard_code . ' — ' . $package->standard_title); ?></strong>
                    </span>
                    <span class="inst-muted tw-text-xs">
                        (Versi: <strong><?php echo html_escape($package->version_code); ?></strong>)
                    </span>
                </div>

                <h2 class="tw-text-xl sm:tw-text-2xl tw-font-bold tw-m-0 tw-text-slate-900 tw-leading-relaxed">
                    <?php echo html_escape($package->title); ?>
                </h2>

                <p class="tw-text-xs tw-text-slate-600 tw-m-0 tw-leading-relaxed">
                    <?php echo nl2br(html_escape($package->description ?: 'Deskripsi paket belum diisi.')); ?>
                </p>

                <?php if (!$mutable): ?>
                    <p class="inst-muted tw-text-xs tw-m-0 tw-italic tw-pt-1">
                        Hanya-baca: versi approved, active, dan retired tidak dapat diubah.
                    </p>
                <?php endif; ?>
            </div>

            <?php if ($mutable): ?>
                <div class="tw-flex tw-items-center tw-gap-2 tw-flex-wrap">
                    <a class="inst-button inst-button-secondary tw-text-xs" href="<?php echo site_url('lpmpi/spmi-instruments/package/edit/' . (int) $package->id); ?>">
                        <?php echo $icon('edit'); ?> Edit Paket
                    </a>
                    <a class="inst-button inst-button-primary tw-text-xs" href="<?php echo site_url('lpmpi/spmi-instruments/question/create/' . (int) $package->id); ?>">
                        <?php echo $icon('plus'); ?> Tambah Pertanyaan
                    </a>
                    <?php if ($total_questions === 0): ?>
                        <?php echo form_open('lpmpi/spmi-instruments/package/delete/' . (int) $package->id, ['class' => 'tw-inline', 'onsubmit' => "return confirm('Hapus paket instrumen ini? Paket hanya dapat dihapus jika belum memiliki pertanyaan.');"]); ?>
                            <button type="submit" class="inst-button inst-button-danger tw-text-xs">
                                <?php echo $icon('trash'); ?> Hapus Paket
                            </button>
                        <?php echo form_close(); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Daftar Pertanyaan Section -->
    <section class="inst-surface">
        <div class="inst-surface-body">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-6 tw-flex-wrap tw-gap-3">
                <div>
                    <h3 class="tw-text-lg tw-font-bold tw-m-0">Daftar Butir Pertanyaan Audit</h3>
                    <p class="inst-muted tw-text-xs tw-mt-1">Daftar butir pertanyaan audit, pemetaan indikator, dan kebijakan jenis bukti fisik.</p>
                </div>
                <div class="tw-flex tw-items-center tw-gap-2">
                    <span class="inst-badge inst-badge-draft tw-font-mono"><?php echo $total_questions; ?> Butir Pertanyaan</span>
                    <?php if ($mutable): ?>
                        <a class="inst-button inst-button-primary tw-text-xs" href="<?php echo site_url('lpmpi/spmi-instruments/question/create/' . (int) $package->id); ?>">
                            <?php echo $icon('plus'); ?> Tambah Pertanyaan
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (empty($questions)): ?>
                <div class="tw-text-center tw-py-12 tw-text-slate-500">
                    <div class="tw-mb-3"><?php echo $icon('help-circle'); ?></div>
                    <p class="tw-font-bold tw-text-base tw-text-slate-700">Belum ada pertanyaan audit</p>
                    <p class="tw-text-sm">Gunakan tombol 'Tambah Pertanyaan' untuk menyusun butir instrumen audit.</p>
                </div>
            <?php else: ?>
                <div class="tw-space-y-4">
                    <?php foreach ($questions as $q):
                        $policy = !empty($q->evidence_policy) ? $q->evidence_policy : 'none';
                        $policy_label = strtoupper($policy);
                    ?>
                        <div class="tw-border tw-border-slate-200 tw-rounded-lg tw-p-4 sm:tw-p-5 tw-bg-white hover:tw-bg-slate-50 tw-transition-colors">
                            <div class="tw-flex tw-flex-col lg:tw-flex-row lg:tw-items-start lg:tw-justify-between tw-gap-4">
                                <div class="tw-space-y-2 tw-flex-1">
                                    <div class="tw-flex tw-items-center tw-gap-2 tw-flex-wrap">
                                        <span class="tw-font-mono tw-font-bold tw-text-xs tw-bg-slate-100 tw-px-2 tw-py-0.5 tw-rounded tw-text-slate-700">
                                            #<?php echo (int) $q->display_order; ?> &middot; <?php echo html_escape($q->question_code); ?>
                                        </span>
                                        <span class="inst-badge inst-badge-policy tw-font-mono tw-text-xs">
                                            Bukti: <?php echo html_escape($policy_label); ?>
                                        </span>
                                        <?php if (!empty($q->indicator_code)): ?>
                                            <span class="inst-muted tw-text-xs">
                                                Indikator: <strong><?php echo html_escape($q->indicator_code . ' — ' . $q->indicator_title); ?></strong>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="tw-text-sm tw-font-bold tw-text-slate-900 tw-leading-relaxed">
                                        <?php echo nl2br(html_escape($q->question_text)); ?>
                                    </div>

                                    <div class="tw-bg-slate-50 tw-p-3 tw-rounded tw-border tw-border-slate-200 tw-text-xs tw-text-slate-700">
                                        <strong class="tw-text-slate-800 tw-block tw-mb-0.5">Instruksi Bukti / Verifikasi:</strong>
                                        <?php echo nl2br(html_escape($q->evidence_instruction)); ?>
                                    </div>
                                </div>

                                <div class="tw-flex tw-items-center tw-gap-2 tw-self-start tw-pt-1">
                                    <a class="inst-button inst-button-secondary tw-text-xs tw-py-1 tw-px-2.5" href="<?php echo site_url('lpmpi/spmi-instruments/question/detail/' . (int) $q->id); ?>">
                                        <?php echo $icon('external-link'); ?> Detail
                                    </a>
                                    <?php if ($mutable): ?>
                                        <a class="inst-button inst-button-secondary tw-text-xs tw-py-1 tw-px-2.5" href="<?php echo site_url('lpmpi/spmi-instruments/question/edit/' . (int) $q->id); ?>">
                                            <?php echo $icon('edit'); ?> Edit
                                        </a>
                                        <?php echo form_open('lpmpi/spmi-instruments/question/delete/' . (int) $q->id, ['class' => 'tw-inline', 'onsubmit' => "return confirm('Hapus pertanyaan ini? Pertanyaan hanya dapat dihapus jika belum memiliki rubrik.');"]); ?>
                                            <button type="submit" class="inst-button inst-button-danger tw-text-xs tw-py-1 tw-px-2.5" title="Hapus pertanyaan">
                                                <?php echo $icon('trash'); ?> Hapus
                                            </button>
                                        <?php echo form_close(); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
