<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$settings = isset($settings) && is_array($settings) ? $settings : [];
$php_ceiling_mib = isset($php_ceiling_mib) ? (int) $php_ceiling_mib : 10;
$app_max_mib = 10;

// Category descriptions & icon mapping
$category_details = [
    'spmi_evidence' => [
        'icon' => '<svg class="tw-w-5 tw-h-5 tw-text-blue-600" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        'desc' => 'Bukti unggahan dokumen atau arsip pendukung asesmen SPMI oleh auditi maupun auditor.',
        'tech_key' => 'spmi_evidence'
    ],
    'ppepp_documents' => [
        'icon' => '<svg class="tw-w-5 tw-h-5 tw-text-indigo-600" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/></svg>',
        'desc' => 'Arsip regulasi, manual, formulir, dan bukti tahap PPEPP (Penetapan hingga Peningkatan).',
        'tech_key' => 'ppepp_documents'
    ],
    'profile_photos' => [
        'icon' => '<svg class="tw-w-5 tw-h-5 tw-text-emerald-600" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'desc' => 'Foto profil akun pengguna pada halaman Akun Saya.',
        'tech_key' => 'profile_photos'
    ],
    'spreadsheet_imports' => [
        'icon' => '<svg class="tw-w-5 tw-h-5 tw-text-emerald-700" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
        'desc' => 'Berkas lembar kerja spreadsheet (.xlsx, .xls) untuk import data massal akun, prodi, dan instrumen.',
        'tech_key' => 'spreadsheet_imports'
    ],
    'spmi_source_pdf' => [
        'icon' => '<svg class="tw-w-5 tw-h-5 tw-text-rose-600" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
        'desc' => 'Dokumen induk PDF standar SPMI universitas yang diunggah pada detail versi standar.',
        'tech_key' => 'spmi_source_pdf'
    ],
    'institution_logo' => [
        'icon' => '<svg class="tw-w-5 tw-h-5 tw-text-amber-600" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        'desc' => 'Logo resmi perguruan tinggi atau lembaga pada pengaturan Profil Lembaga.',
        'tech_key' => 'institution_logo'
    ],
];
?>

<div id="upload-settings-root" class="tw-max-w-7xl tw-mx-auto tw-space-y-6">
    <!-- Header Section -->
    <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-4 tw-border-b tw-border-slate-200 tw-pb-5">
        <div>
            <div class="tw-inline-flex tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-tracking-wider tw-text-blue-700 tw-uppercase tw-mb-1">
                <svg class="tw-w-4 tw-h-4" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                PENGATURAN SISTEM
            </div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-slate-900 tw-tracking-tight">Pengaturan Ukuran Upload</h1>
            <p class="tw-text-sm tw-text-slate-500 tw-mt-1">
                Atur batas unggahan per jenis berkas. Batas ini dipakai oleh uploader aktif yang sudah terhubung ke pengaturan bersama.
            </p>
        </div>
    </div>

    <!-- Runtime Limits Callout -->
    <div class="tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded-2xl tw-p-5 tw-shadow-sm">
        <div class="tw-flex tw-flex-col lg:tw-flex-row lg:tw-items-center lg:tw-justify-between tw-gap-4">
            <div class="tw-space-y-1.5 tw-max-w-2xl">
                <div class="tw-flex tw-items-center tw-gap-2">
                    <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-6 tw-h-6 tw-rounded-full tw-bg-blue-100 tw-text-blue-700">
                        <svg class="tw-w-4 tw-h-4" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <span class="tw-text-sm tw-font-semibold tw-text-slate-900">Batas Runtime Server Aktif</span>
                </div>
                <p class="tw-text-xs tw-text-slate-600 tw-leading-relaxed">
                    Batas runtime aplikasi tetap maksimal <?php echo html_escape((string) $app_max_mib); ?> MiB dan otomatis mengikuti ceiling aman PHP saat ini: <strong class="tw-text-slate-900"><?php echo html_escape((string) $php_ceiling_mib); ?> MiB</strong>, termasuk 1 MiB headroom multipart dari post_max_size.
                </p>
            </div>
            <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 sm:tw-gap-3">
                <div class="tw-bg-white tw-border tw-border-slate-200 tw-rounded-xl tw-px-3 tw-py-2 tw-text-center tw-min-w-[100px]">
                    <div class="tw-text-[10px] tw-font-medium tw-text-slate-500 tw-uppercase">upload_max_filesize</div>
                    <div class="tw-text-sm tw-font-bold tw-text-slate-800"><?php echo ini_get('upload_max_filesize') ?: '2M'; ?></div>
                </div>
                <div class="tw-bg-white tw-border tw-border-slate-200 tw-rounded-xl tw-px-3 tw-py-2 tw-text-center tw-min-w-[100px]">
                    <div class="tw-text-[10px] tw-font-medium tw-text-slate-500 tw-uppercase">post_max_size</div>
                    <div class="tw-text-sm tw-font-bold tw-text-slate-800"><?php echo ini_get('post_max_size') ?: '8M'; ?></div>
                </div>
                <div class="tw-bg-blue-50 tw-border tw-border-blue-200 tw-rounded-xl tw-px-3 tw-py-2 tw-text-center tw-min-w-[110px]">
                    <div class="tw-text-[10px] tw-font-semibold tw-text-blue-700 tw-uppercase">Ceiling Efektif</div>
                    <div class="tw-text-sm tw-font-bold tw-text-blue-900"><?php echo html_escape((string) $php_ceiling_mib); ?> MiB</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Form -->
    <?php echo form_open('lpmpi/upload-size-settings/update'); ?>
    <div class="tw-space-y-6">
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-4">
            <?php foreach ($settings as $category => $setting):
                $limit_mib = (int) $setting['limit_mib'];
                $is_clamped = $limit_mib > $php_ceiling_mib;
                $effective_limit = min($limit_mib, $php_ceiling_mib);
                $meta = isset($category_details[$category]) ? $category_details[$category] : [
                    'icon' => '<svg class="tw-w-5 tw-h-5 tw-text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
                    'desc' => 'Batas ukuran file untuk kategori ' . html_escape($setting['label']) . '.',
                    'tech_key' => $category
                ];
            ?>
                <div class="tw-bg-white tw-border tw-border-slate-200 tw-rounded-2xl tw-p-5 tw-shadow-sm hover:tw-shadow-md tw-transition-shadow tw-flex tw-flex-col tw-justify-between tw-space-y-4">
                    <div class="tw-space-y-2.5">
                        <div class="tw-flex tw-items-start tw-justify-between tw-gap-3">
                            <div class="tw-flex tw-items-center tw-gap-3">
                                <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-slate-50 tw-border tw-border-slate-100 tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                                    <?php echo $meta['icon']; ?>
                                </div>
                                <div>
                                    <h3 class="tw-text-sm tw-font-bold tw-text-slate-900"><?php echo html_escape($setting['label']); ?></h3>
                                    <code class="tw-text-[11px] tw-font-mono tw-text-slate-400"><?php echo html_escape($category); ?></code>
                                </div>
                            </div>
                            <?php if ($is_clamped): ?>
                                <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded-md tw-text-[11px] tw-font-semibold tw-bg-amber-50 tw-text-amber-700 tw-border tw-border-amber-200" title="Nilai tersimpan <?php echo html_escape((string) $limit_mib); ?> MiB, namun server membatasi maksimal <?php echo html_escape((string) $php_ceiling_mib); ?> MiB">
                                    Tersimpan <?php echo html_escape((string) $limit_mib); ?>M · Efektif <?php echo html_escape((string) $effective_limit); ?>M
                                </span>
                            <?php else: ?>
                                <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded-md tw-text-[11px] tw-font-medium tw-bg-slate-100 tw-text-slate-700">
                                    Efektif <?php echo html_escape((string) $effective_limit); ?> MiB
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="tw-text-xs tw-text-slate-500 tw-leading-relaxed">
                            <?php echo html_escape($meta['desc']); ?>
                        </p>
                    </div>

                    <div class="tw-pt-3 tw-border-t tw-border-slate-100">
                        <label for="input-<?php echo html_escape($category); ?>" class="tw-block tw-text-xs tw-font-medium tw-text-slate-700 tw-mb-1.5">
                            Batas Konfigurasi (Maks. <?php echo html_escape((string) $php_ceiling_mib); ?> MiB)
                        </label>
                        <div class="tw-relative tw-rounded-xl tw-shadow-sm">
                            <input
                                id="input-<?php echo html_escape($category); ?>"
                                class="tw-block tw-w-full tw-rounded-xl tw-border-slate-300 tw-pr-14 tw-pl-3.5 tw-py-2.5 tw-text-sm tw-font-medium tw-text-slate-900 focus:tw-border-blue-600 focus:tw-ring-1 focus:tw-ring-blue-600 tw-transition-colors"
                                name="<?php echo html_escape($category); ?>"
                                type="number"
                                min="1"
                                max="<?php echo html_escape((string) $php_ceiling_mib); ?>"
                                step="1"
                                value="<?php echo html_escape((string) $setting['limit_mib']); ?>"
                                required
                            >
                            <div class="tw-pointer-events-none tw-absolute tw-inset-y-0 tw-right-0 tw-flex tw-items-center tw-pr-3.5">
                                <span class="tw-text-xs tw-font-semibold tw-text-slate-400">MiB</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Submit Bar -->
        <div class="tw-flex tw-flex-col sm:tw-flex-row tw-items-center tw-justify-between tw-gap-4 tw-bg-white tw-border tw-border-slate-200 tw-rounded-2xl tw-p-4 tw-shadow-sm">
            <div class="tw-flex tw-items-center tw-gap-2 tw-text-xs tw-text-slate-500">
                <svg class="tw-w-4 tw-h-4 tw-text-slate-400" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Perubahan langsung berlaku pada uploader terkait setelah disimpan.
            </div>
            <button type="submit" class="tw-w-full sm:tw-w-auto tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-5 tw-py-2.5 tw-rounded-xl tw-bg-blue-600 hover:tw-bg-blue-700 tw-text-white tw-font-semibold tw-text-sm tw-shadow-sm hover:tw-shadow tw-transition-all focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-600 focus:tw-ring-offset-2">
                <svg class="tw-w-4 tw-h-4" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                <span>Simpan pengaturan</span>
            </button>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
