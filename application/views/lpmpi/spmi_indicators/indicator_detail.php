<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

// Icon helper
$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<path d="m15 18-6-6 6-6"/>',
        'edit' => '<path d="m4 16-1 4 4-1L18 8l-3-3L4 16ZM13 6l3 3M20 4l1 1"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'target' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
    ];
    return '<svg class="std-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['target']) . '</svg>';
};

$type = strtoupper($indicator->indicator_type);
$is_iku = $type === 'IKU';
?>

<div id="standards-root" class="tw-mx-auto tw-max-w-screen-2xl">
    <!-- Back Link -->
    <div class="tw-mb-4">
        <a href="<?php echo site_url('lpmpi/spmi-standards/version/detail/' . (int) $indicator->version_id); ?>" class="std-button std-button-secondary tw-text-xs">
            <?php echo $icon('arrow-left'); ?> Kembali ke Versi Standar
        </a>
    </div>

    <!-- Hero Card Indikator -->
    <section class="std-hero tw-mb-6 tw-rounded-lg tw-border tw-p-5 sm:tw-p-6">
        <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-start sm:tw-justify-between tw-gap-4">
            <div class="tw-space-y-2 tw-flex-1">
                <div class="tw-flex tw-items-center tw-gap-2 tw-flex-wrap">
                    <span class="tw-text-xs tw-font-bold tw-px-2.5 tw-py-0.5 tw-rounded <?php echo $is_iku ? 'tw-bg-blue-100 tw-text-blue-800' : 'tw-bg-purple-100 tw-text-purple-800'; ?>">
                        <?php echo html_escape($type); ?>
                    </span>
                    <span class="tw-font-mono tw-font-bold tw-text-xs tw-bg-white tw-border tw-border-slate-300 tw-px-2 tw-py-0.5 tw-rounded tw-text-slate-700">
                        <?php echo html_escape($indicator->indicator_code); ?>
                    </span>
                    <span class="std-muted tw-text-xs">
                        Standar: <strong><?php echo html_escape($indicator->standard_code . ' — ' . $indicator->standard_title); ?></strong>
                    </span>
                </div>

                <h2 class="tw-text-xl sm:tw-text-2xl tw-font-bold tw-m-0 tw-text-slate-900 tw-leading-relaxed">
                    <?php echo html_escape($indicator->title); ?>
                </h2>

                <?php if (!$mutable): ?>
                    <p class="std-muted tw-text-xs tw-m-0 tw-italic">
                        Versi ini bersifat hanya-baca (approved / active / retired).
                    </p>
                <?php endif; ?>
            </div>

            <?php if ($mutable): ?>
                <div class="tw-flex tw-items-center tw-gap-2">
                    <a class="std-button std-button-secondary tw-text-xs" href="<?php echo site_url('lpmpi/spmi-indicators/indicator/edit/' . (int) $indicator->id); ?>">
                        <?php echo $icon('edit'); ?> Edit Indikator
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Meta Grid -->
        <div class="tw-mt-6 tw-pt-5 tw-border-t tw-border-slate-200 tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-4">
            <div class="tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-p-3.5">
                <span class="std-muted tw-text-xs tw-block tw-mb-1 tw-font-bold tw-uppercase tw-tracking-wider">Unit Lingkup</span>
                <strong class="tw-text-sm tw-text-slate-800 tw-block"><?php echo html_escape($indicator->scope_unit_name); ?></strong>
            </div>

            <div class="tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-p-3.5">
                <span class="std-muted tw-text-xs tw-block tw-mb-1 tw-font-bold tw-uppercase tw-tracking-wider">Penanggung Jawab</span>
                <strong class="tw-text-sm tw-text-slate-800 tw-block"><?php echo html_escape($indicator->responsible_unit_name); ?></strong>
                <span class="std-muted tw-text-xs tw-block tw-mt-0.5">PIC: <?php echo html_escape($indicator->responsible_pic_name ?: '-'); ?></span>
            </div>

            <div class="tw-bg-white tw-border tw-border-slate-200 tw-rounded-lg tw-p-3.5">
                <span class="std-muted tw-text-xs tw-block tw-mb-1 tw-font-bold tw-uppercase tw-tracking-wider">Kebutuhan Bukti</span>
                <p class="tw-text-xs tw-text-slate-700 tw-m-0 tw-leading-relaxed"><?php echo nl2br(html_escape($indicator->evidence_requirement)); ?></p>
            </div>
        </div>
    </section>

    <!-- Target Tahunan Section -->
    <section class="std-surface">
        <div class="std-surface-body">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-6 tw-flex-wrap tw-gap-3">
                <div>
                    <h3 class="tw-text-lg tw-font-bold tw-m-0">Target Capaian Tahunan</h3>
                    <p class="std-muted tw-text-xs tw-mt-1">Rencana target tahunan mutu untuk indikator kinerja ini.</p>
                </div>
                <?php if ($mutable): ?>
                    <a class="std-button std-button-primary tw-text-xs" href="<?php echo site_url('lpmpi/spmi-indicators/target/create/' . (int) $indicator->id); ?>">
                        <?php echo $icon('plus'); ?> Tambah Target Tahun
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($targets)): ?>
                <div class="tw-text-center tw-py-10 tw-text-slate-500">
                    <div class="tw-mb-2"><?php echo $icon('target'); ?></div>
                    <p class="tw-font-bold tw-text-base tw-text-slate-700">Belum ada target tahunan</p>
                    <p class="tw-text-xs">Tetapkan target capaian untuk tahun-tahun akademik mendatang.</p>
                </div>
            <?php else: ?>
                <div class="std-table-wrap">
                    <table class="std-table">
                        <thead>
                            <tr>
                                <th>Tahun Target</th>
                                <th>Nilai Target Capaian</th>
                                <th class="tw-text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($targets as $target): ?>
                                <tr>
                                    <td data-label="Tahun Target">
                                        <span class="tw-font-mono tw-font-bold tw-text-sm tw-text-slate-900">
                                            <?php echo (int) $target->target_year; ?>
                                        </span>
                                    </td>
                                    <td data-label="Nilai Target Capaian">
                                        <div class="tw-text-sm tw-text-slate-800 tw-font-medium">
                                            <?php echo nl2br(html_escape($target->target_value)); ?>
                                        </div>
                                    </td>
                                    <td data-label="Aksi" class="tw-text-right">
                                        <?php if ($mutable): ?>
                                            <a class="std-button std-button-secondary tw-text-xs tw-py-1 tw-px-2.5" href="<?php echo site_url('lpmpi/spmi-indicators/target/edit/' . (int) $target->id); ?>">
                                                <?php echo $icon('edit'); ?> Edit
                                            </a>
                                        <?php else: ?>
                                            <span class="std-muted tw-text-xs">Hanya-baca</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
