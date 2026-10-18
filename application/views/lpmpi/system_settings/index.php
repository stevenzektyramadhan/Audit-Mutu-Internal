<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$settings = isset($settings) && is_array($settings) ? $settings : [];
$app_name = isset($settings['app_name']) ? (string) $settings['app_name'] : 'Audit Mutu Internal';
$app_short_name = isset($settings['app_short_name']) ? (string) $settings['app_short_name'] : 'AMI';
$login_hero_title = isset($settings['login_hero_title']) ? (string) $settings['login_hero_title'] : 'Budaya mutu tumbuh dari proses yang jelas dan bukti yang tepercaya.';
$login_hero_description = isset($settings['login_hero_description']) ? (string) $settings['login_hero_description'] : 'Ruang kerja terpadu untuk pelaksanaan, penilaian, dan tindak lanjut Sistem Penjaminan Mutu Internal perguruan tinggi.';
$organization_name = isset($settings['organization_name']) ? (string) $settings['organization_name'] : '';
$organization_description = isset($settings['organization_description']) ? (string) $settings['organization_description'] : '';
$organization_logo_local_url = isset($settings['organization_logo_local_url']) ? (string) $settings['organization_logo_local_url'] : '';
$organization_logo_fallback_url = isset($settings['organization_logo_fallback_url']) ? (string) $settings['organization_logo_fallback_url'] : base_url('assets/img/logo-2.png');
$organization_logo_preview_url = $organization_logo_local_url !== '' ? $organization_logo_local_url : $organization_logo_fallback_url;
$institution_logo_limit_mib = isset($institution_logo_limit_mib) ? (int) $institution_logo_limit_mib : 4;
$turnstile_site_key = isset($settings['turnstile_site_key']) ? (string) $settings['turnstile_site_key'] : '';
$turnstile_secret_configured = !empty($settings['turnstile_secret_configured']);
$turnstile_enabled = !empty($settings['turnstile_enabled']);
$turnstile_state = isset($settings['turnstile_state']) ? (string) $settings['turnstile_state'] : 'disabled';
$turnstile_configuration_invalid = !empty($settings['turnstile_configuration_invalid']);
$can_manage_turnstile = !empty($can_manage_turnstile);

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div id="system-settings-root" class="tw-max-w-7xl tw-mx-auto tw-space-y-6">
    <!-- Header Section -->
    <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-4 tw-border-b tw-border-slate-200 tw-pb-5">
        <div>
            <div class="tw-inline-flex tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-tracking-wider tw-text-blue-700 tw-uppercase tw-mb-1">
                <svg class="tw-w-4 tw-h-4" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>PENGATURAN SISTEM</span>
            </div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-slate-900 tw-tracking-tight">Pengaturan Sistem</h1>
            <span class="tw-sr-only tw-bg-slate-900"></span>
            <p class="tw-text-sm tw-text-slate-500 tw-mt-1">
                Kelola identitas utama perguruan tinggi dan verifikasi keamanan login akun.
            </p>
        </div>
    </div>

    <!-- Shared Settings Navigation Tabs -->
    <nav class="tw-flex tw-items-center tw-gap-1 sm:tw-gap-2 tw-border-b tw-border-slate-200 tw-pb-px tw-overflow-x-auto tw-no-scrollbar" aria-label="Tab Pengaturan">
        <a href="<?php echo html_escape(site_url('lpmpi/system-settings')); ?>" class="tw-inline-flex tw-items-center tw-gap-2 tw-px-3.5 sm:tw-px-4 tw-py-2.5 tw-text-xs sm:tw-text-sm tw-font-semibold tw-border-b-2 tw-border-blue-600 tw-text-blue-700 tw-bg-blue-50/50 tw-rounded-t-lg tw-transition-colors tw-whitespace-nowrap focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500/20" aria-current="page">
            <svg class="tw-w-4 tw-h-4 tw-text-blue-600 tw-shrink-0" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Pengaturan Sistem</span>
        </a>
        <a href="<?php echo html_escape(site_url('lpmpi/upload-size-settings')); ?>" class="tw-inline-flex tw-items-center tw-gap-2 tw-px-3.5 sm:tw-px-4 tw-py-2.5 tw-text-xs sm:tw-text-sm tw-font-medium tw-border-b-2 tw-border-transparent tw-text-slate-600 hover:tw-text-slate-900 hover:tw-bg-slate-50 tw-rounded-t-lg tw-transition-colors tw-whitespace-nowrap focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-slate-300">
            <svg class="tw-w-4 tw-h-4 tw-text-slate-400 tw-shrink-0" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            <span>Ukuran Upload</span>
        </a>
    </nav>

    <?php echo form_open_multipart('lpmpi/system-settings/update', ['id' => 'system-settings-form', 'class' => 'tw-space-y-6']); ?>
        <!-- Section: Identitas Aplikasi -->
        <section class="tw-bg-white tw-border tw-border-slate-200 tw-rounded-2xl tw-p-5 sm:tw-p-6 tw-shadow-sm" aria-labelledby="app-branding-heading">
            <div class="tw-flex tw-items-center tw-gap-3 tw-border-b tw-border-slate-100 tw-pb-4 tw-mb-5">
                <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-blue-50 tw-border tw-border-blue-100 tw-flex tw-items-center tw-justify-center tw-text-blue-700 tw-shrink-0">
                    <svg class="tw-w-5 tw-h-5" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 id="app-branding-heading" class="tw-text-base tw-font-bold tw-text-slate-900 tw-m-0">Identitas Aplikasi</h2>
                    <p class="tw-text-xs tw-text-slate-500 tw-mt-0.5 tw-mb-0">Kustomisasi nama sistem dan singkatan yang ditampilkan pada header, bilah navigasi, dan judul halaman.</p>
                </div>
            </div>

            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-5">
                <div class="md:tw-col-span-2">
                    <label for="app_name" class="tw-block tw-text-xs tw-font-bold tw-text-slate-700 tw-uppercase tw-tracking-wider tw-mb-1.5">
                        Nama aplikasi <span class="tw-text-slate-400 tw-font-normal">(default: Audit Mutu Internal)</span>
                    </label>
                    <input
                        type="text"
                        id="app_name"
                        name="app_name"
                        value="<?php echo html_escape(set_value('app_name', $app_name)); ?>"
                        maxlength="150"
                        class="tw-block tw-w-full tw-min-h-[44px] tw-rounded-xl tw-border-slate-300 tw-px-3.5 tw-py-2.5 tw-text-sm tw-font-medium tw-text-slate-900 focus:tw-border-blue-600 focus:tw-ring-1 focus:tw-ring-blue-600 tw-transition-colors"
                        placeholder="Contoh: Audit Mutu Internal"
                    >
                    <p class="tw-mt-1.5 tw-text-xs tw-text-slate-500 tw-leading-normal">
                        Nama lengkap sistem aplikasi yang digunakan pada halaman login dan judul dokumen/peramban.
                    </p>
                </div>

                <div>
                    <label for="app_short_name" class="tw-block tw-text-xs tw-font-bold tw-text-slate-700 tw-uppercase tw-tracking-wider tw-mb-1.5">
                        Singkatan aplikasi <span class="tw-text-slate-400 tw-font-normal">(default: AMI)</span>
                    </label>
                    <input
                        type="text"
                        id="app_short_name"
                        name="app_short_name"
                        value="<?php echo html_escape(set_value('app_short_name', $app_short_name)); ?>"
                        maxlength="30"
                        class="tw-block tw-w-full tw-min-h-[44px] tw-rounded-xl tw-border-slate-300 tw-px-3.5 tw-py-2.5 tw-text-sm tw-font-bold tw-text-slate-900 focus:tw-border-blue-600 focus:tw-ring-1 focus:tw-ring-blue-600 tw-transition-colors"
                        placeholder="Contoh: AMI"
                    >
                    <p class="tw-mt-1.5 tw-text-xs tw-text-slate-500 tw-leading-normal">
                        Singkatan ringkas yang tampil menonjol pada logo bilah samping (sidebar).
                    </p>
                </div>
            </div>
        </section>

        <!-- Section: Identitas Organisasi -->
        <section class="tw-bg-white tw-border tw-border-slate-200 tw-rounded-2xl tw-p-5 sm:tw-p-6 tw-shadow-sm" aria-labelledby="organization-heading">
            <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-3 tw-border-b tw-border-slate-100 tw-pb-4 tw-mb-5">
                <div class="tw-flex tw-items-center tw-gap-3">
                    <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-blue-50 tw-border tw-border-blue-100 tw-flex tw-items-center tw-justify-center tw-text-blue-700 tw-shrink-0">
                        <svg class="tw-w-5 tw-h-5" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h2 id="organization-heading" class="tw-text-base tw-font-bold tw-text-slate-900 tw-m-0">Identitas Organisasi</h2>
                        <p class="tw-text-xs tw-text-slate-500 tw-mt-0.5 tw-mb-0">Informasi ini digunakan sebagai identitas lembaga di dalam sistem.</p>
                    </div>
                </div>
                <!-- Shortcut to Profil Lembaga -->
                <div class="tw-flex tw-items-center tw-gap-2">
                    <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-slate-100 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-semibold tw-text-slate-700">Profil lembaga</span>
                    <a href="<?php echo html_escape(site_url('profil')); ?>" class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3 tw-py-1.5 tw-rounded-lg tw-bg-slate-50 hover:tw-bg-slate-100 tw-border tw-border-slate-200 tw-text-xs tw-font-semibold tw-text-slate-700 tw-transition-colors tw-w-fit">
                        <svg class="tw-w-3.5 tw-h-3.5 tw-text-slate-500" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <span>Buka Profil Lembaga</span>
                    </a>
                </div>
            </div>

            <div class="tw-space-y-5">
                <div>
                    <label for="organization_name" class="tw-block tw-text-xs tw-font-bold tw-text-slate-700 tw-uppercase tw-tracking-wider tw-mb-1.5">
                        Nama organisasi <span class="tw-text-red-600" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="text"
                        id="organization_name"
                        name="organization_name"
                        value="<?php echo html_escape(set_value('organization_name', $organization_name)); ?>"
                        maxlength="200"
                        required
                        autocomplete="organization"
                        class="tw-block tw-w-full tw-min-h-[44px] tw-rounded-xl tw-border-slate-300 tw-px-3.5 tw-py-2.5 tw-text-sm tw-font-medium tw-text-slate-900 focus:tw-border-blue-600 focus:tw-ring-1 focus:tw-ring-blue-600 tw-transition-colors"
                        placeholder="Contoh: Universitas Muhammadiyah Bangka Belitung"
                    >
                </div>

                <div>
                    <label for="organization_description" class="tw-block tw-text-xs tw-font-bold tw-text-slate-700 tw-uppercase tw-tracking-wider tw-mb-1.5">
                        Deskripsi organisasi <span class="tw-font-normal tw-normal-case tw-tracking-normal tw-text-slate-400">(opsional)</span>
                    </label>
                    <textarea
                        id="organization_description"
                        name="organization_description"
                        rows="3"
                        class="tw-block tw-w-full tw-rounded-xl tw-border-slate-300 tw-px-3.5 tw-py-2.5 tw-text-sm tw-leading-relaxed tw-text-slate-900 focus:tw-border-blue-600 focus:tw-ring-1 focus:tw-ring-blue-600 tw-transition-colors tw-resize-y tw-min-h-[96px]"
                        placeholder="Ringkasan visi, misi, atau deskripsi singkat lembaga..."
                        aria-describedby="organization_description_help"
                    ><?php echo html_escape(set_value('organization_description', $organization_description)); ?></textarea>
                    <p id="organization_description_help" class="tw-mt-1.5 tw-text-xs tw-text-slate-500 tw-leading-normal">
                        Digunakan sebagai informasi pengenal lembaga pada profil dan portal publik. Tidak ditampilkan pada bilah samping (sidebar).
                    </p>
                </div>

                <!-- Custom Logo Upload Component -->
                <div class="tw-pt-3 tw-border-t tw-border-slate-100">
                    <label class="tw-block tw-text-xs tw-font-bold tw-text-slate-700 tw-uppercase tw-tracking-wider tw-mb-2">
                        Ganti logo organisasi <span class="tw-font-normal tw-normal-case tw-tracking-normal tw-text-slate-400">(opsional)</span>
                    </label>
                    <div class="tw-flex tw-flex-col sm:tw-flex-row tw-items-center sm:tw-items-start tw-gap-4">
                        <!-- Preview Box -->
                        <div class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-p-3 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-h-[120px] tw-w-[120px] tw-shrink-0 tw-shadow-inner">
                            <img
                                id="logo-preview-image"
                                src="<?php echo html_escape($organization_logo_preview_url); ?>"
                                alt="Logo organisasi saat ini"
                                class="tw-max-h-full tw-max-w-full tw-object-contain"
                            >
                        </div>
                        <!-- Dropzone / Picker -->
                        <div class="tw-flex-1 tw-w-full tw-space-y-2">
                            <div
                                id="logo-dropzone"
                                class="tw-relative tw-flex tw-flex-col tw-items-center tw-justify-center tw-p-5 tw-border-2 tw-border-dashed tw-border-slate-300 hover:tw-border-blue-500 tw-rounded-2xl tw-bg-slate-50/70 hover:tw-bg-blue-50/40 tw-transition-all tw-cursor-pointer group"
                                onclick="document.getElementById('logo').click()"
                                role="button"
                                tabindex="0"
                                onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();document.getElementById('logo').click();}"
                                aria-label="Pilih atau seret berkas logo organisasi"
                            >
                                <input
                                    type="file"
                                    id="logo"
                                    name="logo"
                                    accept="image/jpeg,image/png,image/gif"
                                    class="tw-sr-only"
                                    onchange="handleLogoSelect(this)"
                                >
                                <div class="tw-w-10 tw-h-10 tw-rounded-full tw-bg-white tw-shadow-sm tw-flex tw-items-center tw-justify-center tw-text-slate-400 group-hover:tw-text-blue-600 group-hover:tw-scale-110 tw-transition-all tw-mb-2">
                                    <svg class="tw-w-5 tw-h-5" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="tw-text-center">
                                    <span class="tw-text-xs tw-font-semibold tw-text-slate-800 group-hover:tw-text-blue-700 tw-transition-colors">
                                        Klik untuk memilih atau seret berkas logo ke sini
                                    </span>
                                    <div id="logo-filename-display" class="tw-text-[11px] tw-text-slate-500 tw-mt-1">
                                        Format JPG, PNG, atau GIF. Maksimal <?php echo html_escape((string) $institution_logo_limit_mib); ?> MiB.
                                    </div>
                                </div>
                            </div>
                            <div class="tw-flex tw-items-center tw-justify-between tw-text-[11px] tw-text-slate-500">
                                <span>Logo lokal akan diprioritaskan oleh Profil Lembaga; URL PDDikti tetap menjadi fallback.</span>
                                <button
                                    type="button"
                                    id="logo-reset-btn"
                                    class="tw-hidden tw-text-xs tw-font-semibold tw-text-red-600 hover:tw-text-red-700 tw-underline tw-cursor-pointer"
                                    onclick="resetLogoSelection(event)"
                                >
                                    Batal ganti logo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section: Tampilan Halaman Login -->
        <section class="tw-bg-white tw-border tw-border-slate-200 tw-rounded-2xl tw-p-5 sm:tw-p-6 tw-shadow-sm" aria-labelledby="login-hero-heading">
            <div class="tw-flex tw-items-center tw-gap-3 tw-border-b tw-border-slate-100 tw-pb-4 tw-mb-5">
                <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-blue-50 tw-border tw-border-blue-100 tw-flex tw-items-center tw-justify-center tw-text-blue-700 tw-shrink-0">
                    <svg class="tw-w-5 tw-h-5" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 id="login-hero-heading" class="tw-text-base tw-font-bold tw-text-slate-900 tw-m-0">Tampilan Halaman Login</h2>
                    <p class="tw-text-xs tw-text-slate-500 tw-mt-0.5 tw-mb-0">Kustomisasi teks headline dan deskripsi pengantar yang ditampilkan pada latar visual sisi kiri halaman login.</p>
                </div>
            </div>

            <div class="tw-space-y-5">
                <div>
                    <label for="login_hero_title" class="tw-block tw-text-xs tw-font-bold tw-text-slate-700 tw-uppercase tw-tracking-wider tw-mb-1.5">
                        Judul Utama Login <span class="tw-text-slate-400 tw-font-normal tw-normal-case tw-tracking-normal">(maksimal 255 karakter)</span>
                    </label>
                    <textarea
                        id="login_hero_title"
                        name="login_hero_title"
                        rows="2"
                        maxlength="255"
                        class="tw-block tw-w-full tw-rounded-xl tw-border-slate-300 tw-px-3.5 tw-py-2.5 tw-text-sm tw-font-medium tw-leading-relaxed tw-text-slate-900 focus:tw-border-blue-600 focus:tw-ring-1 focus:tw-ring-blue-600 tw-transition-colors tw-resize-y tw-min-h-[72px]"
                        placeholder="Contoh: Budaya mutu tumbuh dari proses yang jelas dan bukti yang tepercaya."
                        aria-describedby="login_hero_title_help"
                    ><?php echo html_escape(set_value('login_hero_title', $login_hero_title, FALSE)); ?></textarea>
                    <p id="login_hero_title_help" class="tw-mt-1.5 tw-text-xs tw-text-slate-500 tw-leading-normal">
                        Kalimat utama atau slogan yang tampil menonjol pada panel kiri halaman login di atas latar foto kampus.
                    </p>
                </div>

                <div>
                    <label for="login_hero_description" class="tw-block tw-text-xs tw-font-bold tw-text-slate-700 tw-uppercase tw-tracking-wider tw-mb-1.5">
                        Deskripsi Login <span class="tw-text-slate-400 tw-font-normal tw-normal-case tw-tracking-normal">(maksimal 1000 karakter)</span>
                    </label>
                    <textarea
                        id="login_hero_description"
                        name="login_hero_description"
                        rows="3"
                        maxlength="1000"
                        class="tw-block tw-w-full tw-rounded-xl tw-border-slate-300 tw-px-3.5 tw-py-2.5 tw-text-sm tw-leading-relaxed tw-text-slate-900 focus:tw-border-blue-600 focus:tw-ring-1 focus:tw-ring-blue-600 tw-transition-colors tw-resize-y tw-min-h-[96px]"
                        placeholder="Ringkasan penjelasan ruang kerja atau pengantar sistem..."
                        aria-describedby="login_hero_description_help"
                    ><?php echo html_escape(set_value('login_hero_description', $login_hero_description, FALSE)); ?></textarea>
                    <p id="login_hero_description_help" class="tw-mt-1.5 tw-text-xs tw-text-slate-500 tw-leading-normal">
                        Paragraf penjelas di bawah judul utama pada panel kiri login. Berbeda dari deskripsi profil organisasi.
                    </p>
                </div>
            </div>
        </section>

        <!-- Section: Keamanan Login (Cloudflare Turnstile) -->
        <section class="tw-bg-white tw-border tw-border-slate-200 tw-rounded-2xl tw-p-5 sm:tw-p-6 tw-shadow-sm" aria-labelledby="turnstile-heading">
            <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-3 tw-border-b tw-border-slate-100 tw-pb-4 tw-mb-5">
                <div class="tw-flex tw-items-center tw-gap-3">
                    <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-indigo-50 tw-border tw-border-indigo-100 tw-flex tw-items-center tw-justify-center tw-text-indigo-700 tw-shrink-0">
                        <svg class="tw-w-5 tw-h-5" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 id="turnstile-heading" class="tw-text-base tw-font-bold tw-text-slate-900 tw-m-0">Cloudflare Turnstile</h2>
                        <p class="tw-text-xs tw-text-slate-500 tw-mt-0.5 tw-mb-0">CAPTCHA aktif hanya jika site key dan secret key yang valid tersedia.</p>
                    </div>
                </div>
                <div>
                    <?php if ($turnstile_enabled): ?>
                        <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-border tw-border-emerald-200 tw-bg-emerald-50 tw-px-3 tw-py-1 tw-text-xs tw-font-semibold tw-text-emerald-700">
                            <span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-emerald-500" aria-hidden="true"></span>
                            <span>Aktif</span>
                        </span>
                    <?php elseif ($turnstile_configuration_invalid): ?>
                        <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-border tw-border-red-200 tw-bg-red-50 tw-px-3 tw-py-1 tw-text-xs tw-font-semibold tw-text-red-700">
                            <span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-red-500" aria-hidden="true"></span>
                            <span>Konfigurasi tidak valid</span>
                        </span>
                    <?php else: ?>
                        <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-border tw-border-slate-200 tw-bg-slate-100 tw-px-3 tw-py-1 tw-text-xs tw-font-semibold tw-text-slate-600">
                            <span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-slate-400" aria-hidden="true"></span>
                            <span>Tidak dikonfigurasi</span>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tw-space-y-4">
                <?php if ($can_manage_turnstile): ?>
                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                        <div>
                            <label for="turnstile_site_key" class="tw-block tw-text-xs tw-font-bold tw-text-slate-700 tw-uppercase tw-tracking-wider tw-mb-1.5">
                                Site key
                            </label>
                            <input
                                type="text"
                                id="turnstile_site_key"
                                name="turnstile_site_key"
                                value="<?php echo html_escape(set_value('turnstile_site_key', $turnstile_site_key)); ?>"
                                maxlength="255"
                                autocomplete="off"
                                class="tw-block tw-w-full tw-min-h-[44px] tw-rounded-xl tw-border-slate-300 tw-px-3.5 tw-py-2.5 tw-text-sm tw-font-mono tw-text-slate-900 focus:tw-border-blue-600 focus:tw-ring-1 focus:tw-ring-blue-600 tw-transition-colors"
                                placeholder="0x4AAAAAA..."
                            >
                        </div>
                        <div>
                            <label for="turnstile_secret_key" class="tw-block tw-text-xs tw-font-bold tw-text-slate-700 tw-uppercase tw-tracking-wider tw-mb-1.5">
                                Secret key baru
                            </label>
                            <input
                                type="password"
                                id="turnstile_secret_key"
                                name="turnstile_secret_key"
                                value="" maxlength="255" autocomplete="new-password"
                                class="tw-block tw-w-full tw-min-h-[44px] tw-rounded-xl tw-border-slate-300 tw-px-3.5 tw-py-2.5 tw-text-sm tw-font-mono tw-text-slate-900 focus:tw-border-blue-600 focus:tw-ring-1 focus:tw-ring-blue-600 tw-transition-colors"
                                placeholder="Masukkan hanya jika ingin mengganti secret..."
                            >
                        </div>
                    </div>
                    <div class="tw-flex tw-items-start tw-gap-3 tw-rounded-xl tw-border tw-border-blue-100 tw-bg-blue-50/70 tw-p-3.5 tw-text-xs tw-leading-relaxed tw-text-blue-900" role="note">
                        <svg class="tw-mt-0.5 tw-h-4 tw-w-4 tw-flex-shrink-0 tw-text-blue-600" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="tw-m-0">Secret key tidak dapat dibaca kembali. Biarkan kolom secret key baru kosong untuk mempertahankan secret key yang tersimpan.</p>
                    </div>
                    <?php if ($turnstile_state === 'configuration_invalid'): ?>
                        <div class="tw-rounded-xl tw-border tw-border-red-200 tw-bg-red-50 tw-p-3.5 tw-text-xs tw-leading-relaxed tw-text-red-800" role="alert">
                            Konfigurasi Turnstile tidak valid atau tidak dapat dibaca. Masukkan ulang site key dan secret key baru, lalu simpan. Nilai tersimpan tidak ditampilkan.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="tw-rounded-xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-4 tw-text-xs tw-leading-relaxed tw-text-slate-600" role="note">
                        Pengaturan kunci Turnstile dikelola oleh Super Admin.
                    </div>
                <?php endif; ?>

                <!-- Semantic Status Badges & Info -->
                <dl class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-3 tw-rounded-xl tw-border tw-border-slate-200 tw-bg-slate-50/70 tw-p-4 tw-m-0">
                    <div class="tw-flex tw-items-center tw-justify-between tw-gap-3">
                        <dt class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wider tw-text-slate-500">Secret tersimpan</dt>
                        <dd class="tw-m-0">
                            <?php if ($turnstile_secret_configured): ?>
                                <span class="tw-inline-flex tw-items-center tw-gap-1 tw-rounded-full tw-border tw-border-emerald-200 tw-bg-emerald-50 tw-px-2.5 tw-py-0.5 tw-text-[11px] tw-font-semibold tw-text-emerald-700">
                                    <svg class="tw-w-3 tw-h-3" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span><?php echo html_escape('Ya'); ?></span>
                                </span>
                            <?php else: ?>
                                <span class="tw-inline-flex tw-items-center tw-rounded-full tw-border tw-border-slate-200 tw-bg-white tw-px-2.5 tw-py-0.5 tw-text-[11px] tw-font-medium tw-text-slate-500">
                                    <?php echo html_escape('Tidak'); ?>
                                </span>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <div class="tw-flex tw-items-center tw-justify-between tw-gap-3">
                        <dt class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wider tw-text-slate-500">CAPTCHA login</dt>
                        <dd class="tw-m-0">
                            <?php if ($turnstile_enabled): ?>
                                <span class="tw-inline-flex tw-items-center tw-gap-1 tw-rounded-full tw-border tw-border-emerald-200 tw-bg-emerald-50 tw-px-2.5 tw-py-0.5 tw-text-[11px] tw-font-semibold tw-text-emerald-700">
                                    <span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-emerald-500" aria-hidden="true"></span>
                                    <span><?php echo html_escape('Aktif'); ?></span>
                                </span>
                            <?php elseif ($turnstile_configuration_invalid): ?>
                                <span class="tw-inline-flex tw-items-center tw-rounded-full tw-border tw-border-red-200 tw-bg-red-50 tw-px-2.5 tw-py-0.5 tw-text-[11px] tw-font-medium tw-text-red-700">
                                    <?php echo html_escape('Tidak tersedia'); ?>
                                </span>
                            <?php else: ?>
                                <span class="tw-inline-flex tw-items-center tw-rounded-full tw-border tw-border-slate-200 tw-bg-white tw-px-2.5 tw-py-0.5 tw-text-[11px] tw-font-medium tw-text-slate-500">
                                    <?php echo html_escape('Tidak dikonfigurasi'); ?>
                                </span>
                            <?php endif; ?>
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        <!-- Save Action Bar -->
        <div class="tw-flex tw-flex-col sm:tw-flex-row tw-items-center tw-justify-between tw-gap-4 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-4 tw-shadow-sm">
            <div class="tw-flex tw-items-center tw-gap-2 tw-text-xs tw-text-slate-500">
                <svg class="tw-w-4 tw-h-4 tw-text-slate-400 tw-shrink-0" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Perubahan diterapkan setelah pengaturan disimpan.</span>
            </div>
            <div class="tw-flex tw-w-full sm:tw-w-auto tw-items-center tw-gap-3">
                <a href="<?php echo html_escape(site_url('lpmpi/system-settings')); ?>" class="tw-w-full sm:tw-w-auto tw-inline-flex tw-min-h-[44px] tw-items-center tw-justify-center tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-px-4 tw-py-2.5 tw-text-sm tw-font-semibold tw-text-slate-700 hover:tw-bg-slate-50 hover:tw-border-slate-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-slate-300 tw-transition-colors">
                    Batal
                </a>
                <button type="submit" class="tw-w-full sm:tw-w-auto tw-inline-flex tw-min-h-[44px] tw-items-center tw-justify-center tw-gap-2 tw-rounded-xl tw-bg-blue-600 hover:tw-bg-blue-700 tw-px-5 tw-py-2.5 tw-text-sm tw-font-semibold tw-text-white tw-shadow-sm hover:tw-shadow tw-transition-all focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-600 focus:tw-ring-offset-2">
                    <svg class="tw-h-4 tw-w-4" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </div>
    <?php echo form_close(); ?>
</div>

<script>
var originalLogoSrc = <?php echo json_encode($organization_logo_preview_url); ?>;
var defaultHelpText = 'Format JPG, PNG, atau GIF. Maksimal <?php echo (int) $institution_logo_limit_mib; ?> MiB.';

function handleLogoFile(file) {
    if (!file) return;
    var filenameDisplay = document.getElementById('logo-filename-display');
    var previewImage = document.getElementById('logo-preview-image');
    var resetBtn = document.getElementById('logo-reset-btn');

    if (filenameDisplay) {
        filenameDisplay.innerHTML = '<strong class="tw-text-blue-700">' + escapeHtml(file.name) + '</strong> (' + (file.size / 1024 / 1024).toFixed(2) + ' MiB)';
    }
    if (resetBtn) {
        resetBtn.classList.remove('tw-hidden');
    }

    var reader = new FileReader();
    reader.onload = function(e) {
        if (previewImage) {
            previewImage.src = e.target.result;
        }
    };
    reader.readAsDataURL(file);
}

function handleLogoSelect(input) {
    if (input.files && input.files[0]) {
        handleLogoFile(input.files[0]);
    }
}

function resetLogoSelection(event) {
    if (event) event.stopPropagation();
    var input = document.getElementById('logo');
    var filenameDisplay = document.getElementById('logo-filename-display');
    var previewImage = document.getElementById('logo-preview-image');
    var resetBtn = document.getElementById('logo-reset-btn');

    if (input) input.value = '';
    if (filenameDisplay) {
        filenameDisplay.textContent = defaultHelpText;
    }
    if (previewImage) {
        previewImage.src = originalLogoSrc;
    }
    if (resetBtn) {
        resetBtn.classList.add('tw-hidden');
    }
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Drag and drop event listeners
document.addEventListener('DOMContentLoaded', function() {
    var dropzone = document.getElementById('logo-dropzone');
    var fileInput = document.getElementById('logo');

    if (!dropzone || !fileInput) return;

    ['dragenter', 'dragover'].forEach(function(eventName) {
        dropzone.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('tw-border-blue-600', 'tw-bg-blue-50/60');
        }, false);
    });

    ['dragleave', 'drop'].forEach(function(eventName) {
        dropzone.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('tw-border-blue-600', 'tw-bg-blue-50/60');
        }, false);
    });

    dropzone.addEventListener('drop', function(e) {
        var dt = e.dataTransfer;
        if (dt && dt.files && dt.files.length > 0) {
            fileInput.files = dt.files;
            handleLogoFile(dt.files[0]);
        }
    }, false);
});
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
