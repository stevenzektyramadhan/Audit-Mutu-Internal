<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = !empty($package);
$package = isset($package) ? $package : NULL;
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<path d="m15 18-6-6 6-6"/>',
        'box' => '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/>',
    ];
    return '<svg class="inst-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['box']) . '</svg>';
};

$back_url = $is_edit ? site_url('lpmpi/spmi-instruments/package/detail/' . (int) $package->id) : site_url('lpmpi/spmi-instruments');
?>

<div id="instruments-root" class="tw-mx-auto tw-max-w-screen-2xl">
    <section class="inst-surface form-shell" style="max-width: 46rem;">
        <div class="inst-surface-body">
            <!-- Form Header -->
            <div class="form-header">
                <a href="<?php echo $back_url; ?>" class="inst-back-link">
                    <?php echo $icon('arrow-left'); ?> Kembali
                </a>
                <p class="inst-eyebrow">Paket Instrumen</p>
                <h2 class="tw-text-2xl tw-font-bold tw-m-0 tw-text-slate-800"><?php echo html_escape($title); ?></h2>
                <p class="form-subtitle">Standar Induk: <strong><?php echo html_escape($standard->standard_code . ' — ' . $standard->title); ?></strong></p>
            </div>

            <?php if (validation_errors()): ?>
                <div class="tw-bg-rose-50 tw-border tw-border-rose-200 tw-text-rose-800 tw-p-3.5 tw-rounded-lg tw-text-xs tw-mb-5">
                    <?php echo validation_errors(); ?>
                </div>
            <?php endif; ?>

            <?php echo form_open($action); ?>
                <div class="form-section">
                    <div class="form-section-heading">
                        <?php echo $icon('box'); ?>
                        <span>
                            <strong>Informasi Paket</strong>
                            <small>Kode, urutan, judul, dan deskripsi paket instrumen.</small>
                        </span>
                    </div>

                    <div class="tw-space-y-4">
                        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                            <div>
                                <label for="package_code" class="inst-label">Kode Paket</label>
                                <input class="inst-control tw-font-mono" id="package_code" name="package_code" maxlength="64"
                                       placeholder="Contoh: PKG-PBM-01"
                                       value="<?php echo html_escape(set_value('package_code', $package ? $package->package_code : '')); ?>" required>
                            </div>
                            <div>
                                <label for="display_order" class="inst-label">Urutan</label>
                                <input class="inst-control" id="display_order" name="display_order" type="number" min="1"
                                       value="<?php echo html_escape(set_value('display_order', $package ? $package->display_order : '1')); ?>" required>
                            </div>
                        </div>

                        <div>
                            <label for="title" class="inst-label">Judul Paket</label>
                            <input class="inst-control" id="title" name="title" maxlength="200"
                                   placeholder="Contoh: Instrumen Evaluasi Pembelajaran Semester"
                                   value="<?php echo html_escape(set_value('title', $package ? $package->title : '')); ?>" required>
                        </div>

                        <div>
                            <label for="description" class="inst-label">Deskripsi Paket (Opsional)</label>
                            <textarea class="inst-control tw-h-24" id="description" name="description"
                                      placeholder="Penjelasan cakupan butir pertanyaan instrumen..."><?php echo html_escape(set_value('description', $package ? $package->description : '')); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-end tw-gap-2 tw-mt-6">
                    <a class="inst-button inst-button-secondary tw-justify-center" href="<?php echo $back_url; ?>">
                        Batal
                    </a>
                    <button class="inst-button inst-button-primary tw-justify-center" type="submit">
                        Simpan Paket
                    </button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </section>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
