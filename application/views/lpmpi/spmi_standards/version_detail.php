<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

// Icon helper
$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<path d="m15 18-6-6 6-6"/>',
        'edit' => '<path d="m4 16-1 4 4-1L18 8l-3-3L4 16ZM13 6l3 3M20 4l1 1"/>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
        'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'trash' => '<path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        'file-text' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/>',
        'external-link' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>',
    ];
    return '<svg class="std-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['file-text']) . '</svg>';
};

// Siapkan data indikator & target untuk setiap standar
$ci =& get_instance();
$ci->load->model('Spmi_indicators_model');
$standard_indicators = [];
$indicator_targets = [];

if (!empty($standards)) {
    foreach ($standards as $std) {
        $indicators = $ci->Spmi_indicators_model->get_indicators($std->id);
        $standard_indicators[$std->id] = $indicators;
        foreach ($indicators as $ind) {
            $indicator_targets[$ind->id] = $ci->Spmi_indicators_model->get_targets($ind->id);
        }
    }
}
?>

<div id="standards-root" class="tw-mx-auto tw-max-w-screen-2xl">
    <!-- Action Bar / Back Link -->
    <div class="tw-mb-4">
        <a href="<?php echo site_url('lpmpi/spmi-standards'); ?>" class="std-button std-button-secondary tw-text-xs">
            <?php echo $icon('arrow-left'); ?> Kembali ke Daftar Versi
        </a>
    </div>

    <!-- Hero Card Versi -->
    <section class="std-hero tw-mb-6 tw-rounded-lg tw-border tw-p-5 sm:tw-p-6">
        <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-4">
            <div>
                <p class="std-eyebrow">Dokumen Standar Mutu</p>
                <h2 class="tw-text-2xl tw-font-bold tw-m-0 tw-text-slate-800">
                    <?php echo html_escape($version->version_code); ?> — <?php echo html_escape($version->title); ?>
                </h2>
                <div class="tw-flex tw-items-center tw-gap-3 tw-mt-2">
                    <span class="std-badge std-badge-<?php echo strtolower($version->status); ?>">
                        Status: <?php echo html_escape(ucfirst($version->status)); ?>
                    </span>
                    <?php if (!$mutable): ?>
                        <span class="std-muted tw-text-xs">(Versi ini bersifat hanya-baca)</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tw-flex tw-items-center tw-gap-2 tw-flex-wrap">
                <?php if ($mutable): ?>
                    <a class="std-button std-button-secondary tw-text-xs" href="<?php echo site_url('lpmpi/spmi-standards/version/edit/' . (int) $version->id); ?>">
                        <?php echo $icon('edit'); ?> Edit Versi
                    </a>
                <?php endif; ?>

                <?php if (!empty($transitions)): ?>
                    <?php foreach ($transitions as $next_status): ?>
                        <?php echo form_open('lpmpi/spmi-standards/version/transition/' . (int) $version->id, ['class' => 'tw-inline']); ?>
                            <input type="hidden" name="status" value="<?php echo html_escape($next_status); ?>">
                            <button class="std-button std-button-primary tw-text-xs" type="submit" onclick="return confirm('Pindahkan status versi ke <?php echo html_escape($next_status); ?>?');">
                                Pindah ke <?php echo html_escape(ucfirst($next_status)); ?>
                            </button>
                        <?php echo form_close(); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dokumen Sumber PDF Section -->
        <div class="tw-mt-6 tw-pt-5 tw-border-t tw-border-slate-200">
            <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-3">
                <div>
                    <h4 class="tw-text-sm tw-font-bold tw-m-0 tw-text-slate-800">Dokumen Sumber PDF</h4>
                    <p class="std-muted tw-text-xs tw-mt-0.5">Berkas dokumen SK / Standar resmi yang disahkan universitas. PDF maksimal <?php echo html_escape((string) $source_pdf_limit_mib); ?> MiB.</p>
                </div>
                <div class="tw-flex tw-items-center tw-gap-2">
                    <?php if ($version->source_file_path): ?>
                        <a class="std-button std-button-secondary tw-text-xs" href="<?php echo site_url('lpmpi/spmi-standards/source/download/' . (int) $version->id); ?>">
                            <?php echo $icon('download'); ?> Unduh Berkas PDF
                        </a>
                        <?php if ($mutable): ?>
                            <?php echo form_open('lpmpi/spmi-standards/source/delete/' . (int) $version->id, ['class' => 'tw-inline', 'onsubmit' => "return confirm('Hapus dokumen sumber PDF ini?');"]); ?>
                                <button class="std-button tw-text-xs tw-border tw-border-rose-200 tw-text-rose-700 hover:tw-bg-rose-50" type="submit">
                                    <?php echo $icon('trash'); ?> Hapus PDF
                                </button>
                            <?php echo form_close(); ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="std-muted tw-text-xs tw-italic">Belum ada dokumen sumber PDF.</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($mutable && !$version->source_file_path): ?>
                <div class="tw-mt-3">
                    <?php echo form_open_multipart('lpmpi/spmi-standards/source/upload/' . (int) $version->id, ['class' => 'tw-flex tw-items-center tw-gap-2 tw-flex-wrap']); ?>
                        <input type="file" name="source_pdf" accept="application/pdf,.pdf" required class="tw-text-xs tw-border tw-border-slate-300 tw-rounded tw-p-1.5 tw-bg-white">
                        <button class="std-button std-button-primary tw-text-xs" type="submit">
                            <?php echo $icon('upload'); ?> Unggah PDF
                        </button>
                    <?php echo form_close(); ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Daftar Standar & Indikator SPMI -->
    <section class="std-surface">
        <div class="std-surface-body">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-6 tw-flex-wrap tw-gap-3">
                <div>
                    <h3 class="tw-text-lg tw-font-bold tw-m-0">Katalog Standar &amp; Indikator Mutu</h3>
                    <p class="std-muted tw-text-xs tw-mt-1">Daftar pernyataan standar SPMI, rincian indikator kinerja (IKU/IKT), dan target capaian tahunan.</p>
                </div>
                <?php if ($mutable): ?>
                    <a class="std-button std-button-primary tw-text-xs" href="<?php echo site_url('lpmpi/spmi-standards/standard/create/' . (int) $version->id); ?>">
                        <?php echo $icon('plus'); ?> Tambah Standar Baru
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($standards)): ?>
                <div class="tw-text-center tw-py-12 tw-text-slate-500">
                    <div class="tw-mb-3"><?php echo $icon('file-text'); ?></div>
                    <p class="tw-font-bold tw-text-base tw-text-slate-700">Belum ada standar SPMI</p>
                    <p class="tw-text-sm">Klik tombol 'Tambah Standar Baru' untuk mulai menyusun standar dalam versi ini.</p>
                </div>
            <?php else: ?>
                <div class="tw-space-y-4">
                    <?php foreach ($standards as $std):
                        $indicators = isset($standard_indicators[$std->id]) ? $standard_indicators[$std->id] : [];
                        $total_indicators = count($indicators);
                    ?>
                        <div class="tw-border tw-border-slate-200 tw-rounded-lg tw-bg-white tw-overflow-hidden">
                            <!-- Header Kartu Standar -->
                            <div class="tw-p-4 sm:tw-p-5 tw-bg-slate-50 tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-3 tw-border-b tw-border-slate-200">
                                <div class="tw-flex tw-items-start tw-gap-3">
                                    <span class="tw-font-mono tw-font-bold tw-text-xs tw-bg-white tw-border tw-border-slate-300 tw-px-2 tw-py-1 tw-rounded tw-text-slate-700 tw-mt-0.5">
                                        <?php echo (int) $std->display_order; ?>. <?php echo html_escape($std->standard_code); ?>
                                    </span>
                                    <div>
                                        <h4 class="tw-text-base tw-font-bold tw-m-0 tw-text-slate-900">
                                            <?php echo html_escape($std->title); ?>
                                        </h4>
                                        <?php if (!empty($std->description)): ?>
                                            <p class="std-muted tw-text-xs tw-mt-1 tw-mb-0"><?php echo html_escape($std->description); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="tw-flex tw-items-center tw-gap-2 tw-flex-wrap">
                                    <span class="std-badge std-badge-draft tw-text-xs">
                                        <?php echo $total_indicators; ?> Indikator
                                    </span>

                                    <?php if ($mutable): ?>
                                        <a class="std-button std-button-secondary tw-text-xs tw-py-1 tw-px-2.5" href="<?php echo site_url('lpmpi/spmi-indicators/indicator/create/' . (int) $std->id); ?>">
                                            <?php echo $icon('plus'); ?> Tambah Indikator
                                        </a>
                                        <a class="std-button std-button-secondary tw-text-xs tw-py-1 tw-px-2.5" href="<?php echo site_url('lpmpi/spmi-standards/standard/edit/' . (int) $std->id); ?>" title="Edit Standar">
                                            <?php echo $icon('edit'); ?> Edit
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($total_indicators > 0): ?>
                                        <button type="button" class="std-button std-button-secondary tw-text-xs tw-py-1 tw-px-2.5" data-toggle-indicators="std-ind-<?php echo (int) $std->id; ?>">
                                            <span class="toggle-text">Tutup</span> <?php echo $icon('chevron-down'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- List Indikator Expandable -->
                            <div id="std-ind-<?php echo (int) $std->id; ?>" class="tw-p-4 sm:tw-p-5 tw-space-y-3">
                                <?php if (empty($indicators)): ?>
                                    <div class="tw-py-4 tw-text-center std-muted tw-text-xs tw-italic">
                                        Belum ada indikator mutu pada standar ini.
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($indicators as $ind):
                                        $type = strtoupper($ind->indicator_type);
                                        $is_iku = $type === 'IKU';
                                        $targets = isset($indicator_targets[$ind->id]) ? $indicator_targets[$ind->id] : [];
                                    ?>
                                        <div class="tw-border tw-border-slate-200 tw-rounded-lg tw-p-4 tw-bg-slate-50/50 hover:tw-bg-slate-50 tw-transition-colors">
                                            <div class="tw-flex tw-flex-col lg:tw-flex-row lg:tw-items-start lg:tw-justify-between tw-gap-3">
                                                <div class="tw-space-y-1.5 tw-flex-1">
                                                    <div class="tw-flex tw-items-center tw-gap-2 tw-flex-wrap">
                                                        <span class="tw-text-xs tw-font-bold tw-px-2 tw-py-0.5 tw-rounded <?php echo $is_iku ? 'tw-bg-blue-100 tw-text-blue-800' : 'tw-bg-purple-100 tw-text-purple-800'; ?>">
                                                            <?php echo html_escape($type); ?>
                                                        </span>
                                                        <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-700">
                                                            <?php echo html_escape($ind->indicator_code); ?>
                                                        </span>
                                                        <?php if (!empty($ind->responsible_pic_name)): ?>
                                                            <span class="std-muted tw-text-xs">
                                                                &middot; PIC: <strong><?php echo html_escape($ind->responsible_pic_name); ?></strong>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <h5 class="tw-text-sm tw-font-bold tw-m-0 tw-text-slate-900 tw-leading-relaxed">
                                                        <?php echo html_escape($ind->title); ?>
                                                    </h5>

                                                    <!-- Target Tahunan Summary -->
                                                    <?php if (!empty($targets)): ?>
                                                        <div class="tw-flex tw-items-center tw-gap-2 tw-flex-wrap tw-pt-1">
                                                            <span class="std-muted tw-text-xs tw-font-bold">Target:</span>
                                                            <?php foreach ($targets as $tgt): ?>
                                                                <span class="tw-text-xs tw-bg-white tw-border tw-border-slate-200 tw-px-2 tw-py-0.5 tw-rounded tw-text-slate-700">
                                                                    <strong><?php echo (int) $tgt->target_year; ?>:</strong> <?php echo html_escape($tgt->target_value); ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="tw-flex tw-items-center tw-gap-2 tw-self-start tw-pt-1">
                                                    <a class="std-button std-button-secondary tw-text-xs tw-py-1 tw-px-2.5" href="<?php echo site_url('lpmpi/spmi-indicators/indicator/detail/' . (int) $ind->id); ?>">
                                                        <?php echo $icon('external-link'); ?> Detail &amp; Target
                                                    </a>
                                                    <?php if ($mutable): ?>
                                                        <a class="std-button std-button-secondary tw-text-xs tw-py-1 tw-px-2.5" href="<?php echo site_url('lpmpi/spmi-indicators/indicator/edit/' . (int) $ind->id); ?>">
                                                            <?php echo $icon('edit'); ?> Edit
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- Expand/Collapse Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-toggle-indicators]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = btn.getAttribute('data-toggle-indicators');
            var panel = document.getElementById(targetId);
            var label = btn.querySelector('.toggle-text');
            if (panel) {
                var isHidden = panel.classList.contains('tw-hidden');
                if (isHidden) {
                    panel.classList.remove('tw-hidden');
                    if (label) label.textContent = 'Tutup';
                } else {
                    panel.classList.add('tw-hidden');
                    if (label) label.textContent = 'Buka';
                }
            }
        });
    });
});
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
