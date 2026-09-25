<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$target = isset($target) ? $target : NULL;
$is_edit = !empty($target);
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<path d="m15 18-6-6 6-6"/>',
    ];
    return '<svg class="std-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['arrow-left']) . '</svg>';
};
?>

<div id="standards-root" class="tw-mx-auto tw-max-w-3xl">
    <div class="std-form-shell">
        <div class="std-form-header">
            <a href="<?php echo site_url('lpmpi/spmi-indicators/indicator/detail/' . (int) $indicator->id); ?>" class="std-back-link">
                <?php echo $icon('arrow-left'); ?> Kembali ke Detail Indikator
            </a>
            <p class="std-eyebrow">Target Capaian Tahunan</p>
            <h1><?php echo html_escape($page_title); ?></h1>
            <p class="std-muted">Indikator: <strong><?php echo html_escape($indicator->indicator_code . ' — ' . $indicator->title); ?></strong></p>
        </div>

        <?php if (validation_errors()): ?>
            <div class="std-error" role="alert" aria-live="polite">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <?php echo form_open($action); ?>
            <section class="std-form-section" aria-labelledby="target-information-heading">
                <div class="std-form-section-heading">
                    <h2 id="target-information-heading">Target Capaian Tahunan</h2>
                    <p>Masukkan tahun dan nilai target capaian untuk indikator ini.</p>
                </div>

                <div class="std-form-grid std-form-grid-compact">
                    <div class="std-form-field">
                        <label for="target_year" class="std-label">Tahun Target</label>
                        <p class="std-muted">Pilih tahun untuk menetapkan target capaian.</p>
                        <input class="std-control tw-font-mono" type="number" id="target_year" name="target_year" min="2000" max="2100"
                               placeholder="Contoh: 2025, 2026"
                               value="<?php echo html_escape(set_value('target_year', $is_edit ? $target->target_year : date('Y'))); ?>" required>
                        <span class="std-muted">Tahun target dalam rentang 2000 sampai 2100.</span>
                    </div>

                    <div class="std-form-field">
                        <label for="target_value" class="std-label">Nilai Target Capaian <span class="tw-text-rose-600" aria-hidden="true">*</span></label>
                        <p class="std-muted">Tuliskan capaian yang ingin diraih pada tahun target.</p>
                        <textarea class="std-control std-control-textarea" id="target_value" name="target_value"
                                  placeholder="Contoh: 85% mahasiswa lulus tepat waktu, IPK rata-rata >= 3.25..."
                                  required><?php echo html_escape(set_value('target_value', $is_edit ? $target->target_value : '')); ?></textarea>
                    </div>
                </div>

                <div class="std-form-actions tw-border-t tw-border-slate-200 tw-pt-6">
                    <a class="std-button std-button-secondary tw-justify-center" href="<?php echo site_url('lpmpi/spmi-indicators/indicator/detail/' . (int) $indicator->id); ?>">
                        Batal
                    </a>
                    <button class="std-button std-button-primary tw-justify-center" type="submit">
                        Simpan Target
                    </button>
                </div>
            </section>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
