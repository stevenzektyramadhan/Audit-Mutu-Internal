<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$settings = isset($settings) && is_array($settings) ? $settings : [];
$organization_name = isset($settings['organization_name']) ? (string) $settings['organization_name'] : '';
$organization_description = isset($settings['organization_description']) ? (string) $settings['organization_description'] : '';
$organization_logo_local_url = isset($settings['organization_logo_local_url']) ? (string) $settings['organization_logo_local_url'] : '';
$organization_logo_fallback_url = isset($settings['organization_logo_fallback_url']) ? (string) $settings['organization_logo_fallback_url'] : base_url('assets/img/logo-2.png');
$organization_logo_preview_url = $organization_logo_local_url !== '' ? $organization_logo_local_url : $organization_logo_fallback_url;
$institution_logo_limit_mib = isset($institution_logo_limit_mib) ? (int) $institution_logo_limit_mib : 4;
$turnstile_site_key = isset($settings['turnstile_site_key']) ? (string) $settings['turnstile_site_key'] : '';
$turnstile_secret_configured = !empty($settings['turnstile_secret_configured']);
$turnstile_enabled = !empty($settings['turnstile_enabled']);
$can_manage_turnstile = !empty($can_manage_turnstile);

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<main id="system-settings-root" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-4xl tw-space-y-6">
        <header class="tw-border-b tw-border-slate-200 tw-pb-5">
            <p class="tw-mb-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-text-blue-700">Pengaturan Sistem</p>
            <h1 class="tw-m-0 tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950">Identitas dan keamanan login</h1>
            <p class="tw-mt-2 tw-max-w-2xl tw-text-sm tw-leading-6 tw-text-slate-500">Kelola identitas organisasi dan konfigurasi verifikasi keamanan untuk halaman login.</p>
        </header>

        <?php echo form_open_multipart('lpmpi/system-settings/update', ['id' => 'system-settings-form', 'class' => 'tw-space-y-6']); ?>
            <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm sm:tw-p-8" aria-labelledby="organization-heading">
                <div class="tw-mb-6 tw-flex tw-flex-col tw-gap-3 tw-border-b tw-border-slate-100 tw-pb-5 sm:tw-flex-row sm:tw-items-start sm:tw-justify-between">
                    <div>
                        <h2 id="organization-heading" class="tw-m-0 tw-text-lg tw-font-bold tw-text-slate-950">Identitas organisasi</h2>
                        <p class="tw-mb-0 tw-mt-1 tw-text-sm tw-text-slate-500">Informasi ini digunakan sebagai identitas lembaga di dalam sistem.</p>
                    </div>
                    <span class="tw-inline-flex tw-w-fit tw-items-center tw-rounded-full tw-bg-slate-100 tw-px-3 tw-py-1 tw-text-xs tw-font-semibold tw-text-slate-700">Profil lembaga</span>
                </div>
                <div class="tw-grid tw-gap-5">
                    <div>
                        <label for="organization_name" class="tw-mb-1.5 tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700">Nama organisasi <span class="tw-text-red-600" aria-hidden="true">*</span></label>
                        <input type="text" id="organization_name" name="organization_name" value="<?php echo html_escape(set_value('organization_name', $organization_name)); ?>" maxlength="200" required autocomplete="organization" class="tw-w-full tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-3 tw-text-sm tw-text-slate-900 focus:tw-border-blue-600 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-blue-100">
                    </div>
                    <div>
                        <label for="organization_description" class="tw-mb-1.5 tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700">Deskripsi organisasi <span class="tw-font-normal tw-normal-case tw-tracking-normal tw-text-slate-400">(opsional)</span></label>
                        <textarea id="organization_description" name="organization_description" rows="5" class="tw-min-h-[8rem] tw-w-full tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-3 tw-text-sm tw-leading-6 tw-text-slate-900 focus:tw-border-blue-600 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-blue-100"><?php echo html_escape(set_value('organization_description', $organization_description)); ?></textarea>
                    </div>
                    <div class="tw-grid tw-gap-4 sm:tw-grid-cols-[120px_minmax(0,1fr)] sm:tw-items-center">
                        <div class="tw-flex tw-h-28 tw-w-28 tw-items-center tw-justify-center tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-3">
                            <img src="<?php echo html_escape($organization_logo_preview_url); ?>" alt="Logo organisasi saat ini" class="tw-max-h-full tw-max-w-full tw-object-contain">
                        </div>
                        <div>
                            <label for="logo" class="tw-mb-1.5 tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700">Ganti logo organisasi <span class="tw-font-normal tw-normal-case tw-tracking-normal tw-text-slate-400">(opsional)</span></label>
                            <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/gif" class="tw-block tw-w-full tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-3 tw-text-sm tw-text-slate-900 file:tw-mr-3 file:tw-rounded-lg file:tw-border-0 file:tw-bg-slate-100 file:tw-px-3 file:tw-py-2 file:tw-text-sm file:tw-font-semibold file:tw-text-slate-700 hover:file:tw-bg-slate-200 focus:tw-border-blue-600 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-blue-100">
                            <p class="tw-mb-0 tw-mt-2 tw-text-xs tw-leading-5 tw-text-slate-500">Format JPG, PNG, atau GIF. Maksimal <?php echo html_escape((string) $institution_logo_limit_mib); ?> MiB. Logo lokal akan diprioritaskan oleh Profil Lembaga; URL PDDikti tetap menjadi fallback.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm sm:tw-p-8" aria-labelledby="turnstile-heading">
                <div class="tw-mb-6 tw-flex tw-flex-col tw-gap-3 tw-border-b tw-border-slate-100 tw-pb-5 sm:tw-flex-row sm:tw-items-start sm:tw-justify-between">
                    <div>
                        <h2 id="turnstile-heading" class="tw-m-0 tw-text-lg tw-font-bold tw-text-slate-950">Cloudflare Turnstile</h2>
                        <p class="tw-mb-0 tw-mt-1 tw-text-sm tw-text-slate-500">CAPTCHA aktif hanya jika site key dan secret key yang valid tersedia.</p>
                    </div>
                    <?php if ($turnstile_enabled): ?>
                        <span class="tw-inline-flex tw-w-fit tw-items-center tw-gap-1.5 tw-rounded-full tw-border tw-border-emerald-200 tw-bg-emerald-50 tw-px-3 tw-py-1 tw-text-xs tw-font-semibold tw-text-emerald-700"><span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-emerald-500" aria-hidden="true"></span>Aktif</span>
                    <?php else: ?>
                        <span class="tw-inline-flex tw-w-fit tw-items-center tw-gap-1.5 tw-rounded-full tw-border tw-border-slate-200 tw-bg-slate-100 tw-px-3 tw-py-1 tw-text-xs tw-font-semibold tw-text-slate-600"><span class="tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-slate-400" aria-hidden="true"></span>Tidak aktif</span>
                    <?php endif; ?>
                 </div>
                 <div class="tw-space-y-4">
                     <?php if ($can_manage_turnstile): ?>
                         <div class="tw-grid tw-gap-4 md:tw-grid-cols-2">
                             <div>
                                 <label for="turnstile_site_key" class="tw-mb-1.5 tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700">Site key</label>
                                 <input type="text" id="turnstile_site_key" name="turnstile_site_key" value="<?php echo html_escape(set_value('turnstile_site_key', $turnstile_site_key)); ?>" maxlength="255" autocomplete="off" class="tw-w-full tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-3 tw-text-sm tw-font-mono tw-text-slate-900 focus:tw-border-blue-600 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-blue-100">
                             </div>
                             <div>
                                 <label for="turnstile_secret_key" class="tw-mb-1.5 tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700">Secret key baru</label>
                                 <input type="password" id="turnstile_secret_key" name="turnstile_secret_key" value="" maxlength="255" autocomplete="new-password" class="tw-w-full tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-3 tw-text-sm tw-text-slate-900 focus:tw-border-blue-600 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-blue-100">
                             </div>
                         </div>
                         <div class="tw-flex tw-items-start tw-gap-3 tw-rounded-xl tw-border tw-border-blue-100 tw-bg-blue-50 tw-p-4 tw-text-sm tw-leading-6 tw-text-blue-900" role="note">
                             <svg class="tw-mt-0.5 tw-h-5 tw-w-5 tw-flex-shrink-0 tw-text-blue-600" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                             <p class="tw-m-0">Secret key tidak dapat dibaca kembali. Biarkan kolom secret key baru kosong untuk mempertahankan secret key yang tersimpan.</p>
                         </div>
                     <?php else: ?>
                         <div class="tw-rounded-xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-4 tw-text-sm tw-leading-6 tw-text-slate-600" role="note">Pengaturan kunci Turnstile dikelola oleh Super Admin.</div>
                     <?php endif; ?>
                     <dl class="tw-grid tw-gap-3 tw-rounded-xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-p-4 sm:tw-grid-cols-2">
                        <div class="tw-flex tw-items-center tw-justify-between tw-gap-3"><dt class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wider tw-text-slate-500">Secret tersimpan</dt><dd class="tw-m-0"><?php if ($turnstile_secret_configured): ?><span class="tw-inline-flex tw-rounded-full tw-border tw-border-emerald-200 tw-bg-emerald-50 tw-px-2.5 tw-py-1 tw-text-xs tw-font-semibold tw-text-emerald-700"><?php echo html_escape('Ya'); ?></span><?php else: ?><span class="tw-inline-flex tw-rounded-full tw-border tw-border-slate-200 tw-bg-white tw-px-2.5 tw-py-1 tw-text-xs tw-font-semibold tw-text-slate-600"><?php echo html_escape('Tidak'); ?></span><?php endif; ?></dd></div>
                        <div class="tw-flex tw-items-center tw-justify-between tw-gap-3"><dt class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wider tw-text-slate-500">CAPTCHA login</dt><dd class="tw-m-0"><?php if ($turnstile_enabled): ?><span class="tw-inline-flex tw-rounded-full tw-border tw-border-emerald-200 tw-bg-emerald-50 tw-px-2.5 tw-py-1 tw-text-xs tw-font-semibold tw-text-emerald-700"><?php echo html_escape('Aktif'); ?></span><?php else: ?><span class="tw-inline-flex tw-rounded-full tw-border tw-border-slate-200 tw-bg-white tw-px-2.5 tw-py-1 tw-text-xs tw-font-semibold tw-text-slate-600"><?php echo html_escape('Tidak aktif'); ?></span><?php endif; ?></dd></div>
                    </dl>
                </div>
            </section>

            <div class="tw-flex tw-flex-col tw-items-stretch tw-justify-between tw-gap-4 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-4 tw-shadow-sm sm:tw-flex-row sm:tw-items-center sm:tw-p-5">
                <p class="tw-mb-0 tw-text-sm tw-text-slate-500">Perubahan diterapkan setelah pengaturan disimpan.</p>
                <div class="tw-flex tw-flex-col-reverse tw-items-stretch tw-gap-3 sm:tw-flex-row sm:tw-items-center">
                    <a href="<?php echo html_escape(site_url('lpmpi/system-settings')); ?>" class="tw-inline-flex tw-min-h-[44px] tw-items-center tw-justify-center tw-rounded-xl tw-px-4 tw-py-2.5 tw-text-center tw-text-sm tw-font-semibold tw-text-slate-600 tw-transition hover:tw-bg-slate-100 hover:tw-text-slate-900 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-slate-400 focus:tw-ring-offset-2">Batal</a>
                    <button type="submit" class="tw-inline-flex tw-min-h-[44px] tw-items-center tw-justify-center tw-gap-2 tw-rounded-xl tw-bg-slate-900 tw-px-5 tw-py-2.5 tw-text-sm tw-font-semibold tw-text-white tw-shadow-sm tw-transition hover:tw-bg-slate-800 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-slate-700 focus:tw-ring-offset-2"><svg class="tw-h-4 tw-w-4" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Simpan Pengaturan</span></button>
                </div>
            </div>
        <?php echo form_close(); ?>
    </div>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
