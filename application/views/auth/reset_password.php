<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$icon = static function ($name) {
    $paths = [
        'eye' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off' => '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>'
    ];
    return '<svg width="20" height="20" class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['eye']) . '</svg>';
};
$app_name = isset($app_name) && trim((string) $app_name) !== '' ? trim((string) $app_name) : 'Audit Mutu Internal';
$app_short_name = isset($app_short_name) && trim((string) $app_short_name) !== '' ? trim((string) $app_short_name) : 'AMI';
$organization_logo_url = isset($organization_logo_url) && trim((string) $organization_logo_url) !== '' ? (string) $organization_logo_url : base_url('assets/img/Logo-UNMUH-BABEL-Web.png');
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Password - <?php echo html_escape($app_short_name); ?></title>
    <link rel="icon" href="<?php echo html_escape(base_url('favicon.ico')); ?>" type="image/x-icon">
    <?php $auth_css_ver = file_exists(FCPATH . 'assets/css/auth.css') ? filemtime(FCPATH . 'assets/css/auth.css') : time(); ?>
    <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/auth.css?v=' . $auth_css_ver)); ?>">
</head>
<body id="auth-root" class="tw-m-0 tw-min-h-screen tw-bg-slate-50 tw-text-slate-900 tw-font-sans">
    <main class="auth-shell">
        <section class="auth-visual" aria-label="Identitas Sistem Penjaminan Mutu Internal">
            <img src="<?php echo html_escape(base_url('assets/img/unmuh-foto.jpg')); ?>" alt="Lingkungan kampus" class="auth-visual-image">
            <div class="auth-visual-overlay" aria-hidden="true"></div>
            <div class="auth-visual-content tw-relative tw-z-10 tw-flex tw-h-full tw-min-h-[220px] tw-flex-col tw-justify-end tw-p-6 sm:tw-p-10 lg:tw-p-14">
                <div class="tw-max-w-xl tw-text-white">
                    <p class="tw-m-0 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.22em] tw-text-blue-100"><?php echo html_escape($app_name); ?></p>
                    <h2 class="tw-mb-0 tw-mt-3 tw-text-2xl tw-font-bold tw-leading-tight sm:tw-text-3xl lg:tw-text-4xl">Atur ulang akses dengan aman.</h2>
                    <p class="tw-mb-0 tw-mt-3 tw-max-w-lg tw-text-sm tw-leading-6 tw-text-slate-200">Buat password baru untuk melanjutkan akses ke sistem penjaminan mutu internal.</p>
                </div>
            </div>
        </section>

        <section class="auth-form-side tw-px-5 tw-py-10 sm:tw-px-10 lg:tw-px-14" aria-label="Form atur ulang password">
            <div class="auth-panel">
                <div class="tw-mb-6">
                    <div class="auth-brand-row tw-mb-5 tw-flex tw-items-center tw-gap-3 sm:tw-gap-3.5">
                        <img src="<?php echo html_escape(base_url('assets/img/Logo-UNMUH-BABEL-Web.png')); ?>" alt="Logo Universitas Muhammadiyah Bangka Belitung" class="auth-brand-unmuh">
                        <div class="auth-brand-sep" aria-hidden="true"></div>
                        <img src="<?php echo html_escape($organization_logo_url); ?>" alt="Logo LPM" class="auth-brand-lpm">
                        <span class="tw-text-xs tw-font-bold tw-text-slate-700"><?php echo html_escape($app_short_name); ?> System</span>
                    </div>
                    <p class="tw-mb-1.5 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.18em] tw-text-blue-700">Pemulihan Akun</p>
                    <h1 id="reset-password-title" class="tw-m-0 tw-text-2xl tw-font-bold tw-tracking-tight tw-text-slate-950 sm:tw-text-3xl">Atur Ulang Password</h1>
                    <p class="tw-mb-0 tw-mt-2 tw-text-sm tw-leading-6 tw-text-slate-500">Buat password baru untuk akun Anda.</p>
                </div>

                <?php if (validation_errors()): ?>
                    <div class="tw-mb-5 tw-rounded-xl tw-border tw-border-red-200 tw-bg-red-50 tw-p-3.5 tw-text-xs tw-text-red-800" role="alert">
                        <span class="tw-font-medium"><?php echo html_escape(strip_tags(validation_errors())); ?></span>
                    </div>
                <?php endif; ?>

                <?php echo form_open('auth/reset-password/submit', ['class' => 'tw-space-y-4']); ?>
                    <input type="hidden" name="token" value="<?php echo html_escape(isset($token) ? $token : ''); ?>">
                    <div>
                        <label for="password" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">Password baru</label>
                        <div class="tw-relative tw-w-full">
                            <input class="tw-block tw-h-12 tw-w-full tw-min-w-0 tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-pr-12 tw-text-sm tw-text-slate-900 focus:tw-border-blue-600 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-blue-100 tw-transition" type="password" id="password" name="password" autocomplete="new-password" required>
                            <button class="tw-absolute tw-right-0.5 tw-top-1/2 tw-flex tw-h-11 tw-w-11 -tw-translate-y-1/2 tw-items-center tw-justify-center tw-rounded-full tw-border-0 tw-bg-transparent tw-p-0 tw-text-slate-400 tw-transition hover:tw-bg-slate-100/80 hover:tw-text-slate-700 focus:tw-bg-slate-100 focus:tw-text-slate-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-200" type="button" data-password-toggle="password" aria-label="Tampilkan password" aria-pressed="false"><span aria-hidden="true"><?php echo $icon('eye'); ?></span></button>
                        </div>
                        <small class="tw-mt-1.5 tw-block tw-text-xs tw-text-slate-500">Gunakan minimal 12 karakter.</small>
                        <?php if (form_error('password')): ?><small class="tw-mt-1.5 tw-block tw-text-xs tw-font-medium tw-text-red-600"><?php echo html_escape(strip_tags(form_error('password'))); ?></small><?php endif; ?>
                    </div>
                    <div>
                        <label for="password_confirmation" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">Konfirmasi password baru</label>
                        <div class="tw-relative tw-w-full">
                            <input class="tw-block tw-h-12 tw-w-full tw-min-w-0 tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-pr-12 tw-text-sm tw-text-slate-900 focus:tw-border-blue-600 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-blue-100 tw-transition" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                            <button class="tw-absolute tw-right-0.5 tw-top-1/2 tw-flex tw-h-11 tw-w-11 -tw-translate-y-1/2 tw-items-center tw-justify-center tw-rounded-full tw-border-0 tw-bg-transparent tw-p-0 tw-text-slate-400 tw-transition hover:tw-bg-slate-100/80 hover:tw-text-slate-700 focus:tw-bg-slate-100 focus:tw-text-slate-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-200" type="button" data-password-toggle="password_confirmation" aria-label="Tampilkan password" aria-pressed="false"><span aria-hidden="true"><?php echo $icon('eye'); ?></span></button>
                        </div>
                        <?php if (form_error('password_confirmation')): ?><small class="tw-mt-1.5 tw-block tw-text-xs tw-font-medium tw-text-red-600"><?php echo html_escape(strip_tags(form_error('password_confirmation'))); ?></small><?php endif; ?>
                    </div>
                    <div class="tw-pt-2"><button class="tw-button-primary tw-w-full tw-min-h-[44px] tw-text-sm tw-shadow-md tw-shadow-blue-900/10" type="submit">Simpan Password</button></div>
                <?php echo form_close(); ?>

                <div class="tw-mt-6 tw-text-center"><a class="tw-text-xs tw-font-semibold tw-text-slate-600 hover:tw-text-blue-600 tw-transition" href="<?php echo site_url('auth'); ?>">Kembali ke login</a></div>
                <div class="tw-mt-8 tw-border-t tw-border-slate-200 tw-pt-5 tw-text-center tw-text-xs tw-text-slate-400">&copy; <?php echo date('Y'); ?> University Internal Audit System. All Rights Reserved.</div>
            </div>
        </section>
    </main>
<script>
(function () {
    document.querySelectorAll('[data-password-toggle]').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var input = document.getElementById(toggle.getAttribute('data-password-toggle'));
            if (!input) return;
            var showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            toggle.setAttribute('aria-label', showing ? 'Tampilkan password' : 'Sembunyikan password');
            toggle.setAttribute('aria-pressed', showing ? 'false' : 'true');
        });
    });
})();
</script>
</body>
</html>
