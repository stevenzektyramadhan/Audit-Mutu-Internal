<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$target = isset($target) ? $target : NULL;
$is_edit = !empty($target);
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<path d="m15 18-6-6 6-6"/>',
        'target' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
    ];
    return '<svg class="std-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['target']) . '</svg>';
};
?>

<div id="standards-root" class="tw-mx-auto tw-max-w-screen-2xl">
    <section class="std-surface form-shell" style="max-width: 40rem;">
        <div class="std-surface-body">
            <!-- Form Header -->
            <div class="form-header">
                <a href="<?php echo site_url('lpmpi/spmi-indicators/indicator/detail/' . (int) $indicator->id); ?>" class="org-back-link">
                    <?php echo $icon('arrow-left'); ?> Kembali ke Detail Indikator
                </a>
                <p class="std-eyebrow">Target Capaian Tahunan</p>
                <h2 class="tw-text-2xl tw-font-bold tw-m-0 tw-text-slate-800"><?php echo html_escape($page_title); ?></h2>
                <p class="form-subtitle">Indikator: <strong><?php echo html_escape($indicator->indicator_code . ' — ' . $indicator->title); ?></strong></p>
            </div>

            <?php if (validation_errors()): ?>
                <div class="tw-bg-rose-50 tw-border tw-border-rose-200 tw-text-rose-800 tw-p-3.5 tw-rounded-lg tw-text-xs tw-mb-5">
                    <?php echo validation_errors(); ?>
                </div>
            <?php endif; ?>

            <?php echo form_open($action); ?>
                <div class="form-section">
                    <div class="form-section-heading">
                        <?php echo $icon('target'); ?>
                        <span>
                            <strong>Target Capaian Mutu</strong>
                            <small>Masukkan tahun dan nilai target capaian.</small>
                        </span>
                    </div>

                    <div class="tw-space-y-4">
                        <div>
                            <label for="target_year" class="org-label">Tahun Target</label>
                            <input class="org-control tw-font-mono" type="number" id="target_year" name="target_year" min="2000" max="2100"
                                   placeholder="Contoh: 2025, 2026"
                                   value="<?php echo html_escape(set_value('target_year', $is_edit ? $target->target_year : date('Y'))); ?>" required>
                            <span class="std-muted tw-text-[11px] tw-mt-1 tw-block">Tahun target dalam rentang 2000 sampai 2100.</span>
                        </div>

                        <div>
                            <label for="target_value" class="org-label">Nilai Target Capaian</label>
                            <textarea class="org-control tw-h-24" id="target_value" name="target_value"
                                      placeholder="Contoh: 85% mahasiswa lulus tepat waktu, IPK rata-rata >= 3.25..."
                                      required><?php echo html_escape(set_value('target_value', $is_edit ? $target->target_value : '')); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-end tw-gap-2 tw-mt-6">
                    <a class="std-button std-button-secondary tw-justify-center" href="<?php echo site_url('lpmpi/spmi-indicators/indicator/detail/' . (int) $indicator->id); ?>">
                        Batal
                    </a>
                    <button class="std-button std-button-primary tw-justify-center" type="submit">
                        Simpan Target
                    </button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </section>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
