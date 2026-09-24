<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$icon = static function ($name) {
    $paths = [
        'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'send' => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
        'alert-circle' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
        'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
    ];
    return '<svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['mail']) . '</svg>';
};
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - AMI Sistem Penjaminan Mutu Internal</title>
    <meta name="description" content="Pengaturan Ulang Kata Sandi Akun AMI Perguruan Tinggi">
    <link rel="icon" href="<?php echo html_escape(base_url('favicon.ico')); ?>" type="image/x-icon">
    <?php $auth_css_ver = file_exists(FCPATH . 'assets/css/auth.css') ? filemtime(FCPATH . 'assets/css/auth.css') : time(); ?>
    <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/auth.css?v=' . $auth_css_ver)); ?>">
</head>
<body id="auth-root" class="tw-m-0 tw-min-h-screen tw-bg-slate-50 tw-text-slate-900 tw-font-sans">
    <main class="auth-shell">
        <!-- Visual Hero Section (Left) -->
        <section class="auth-visual" aria-label="Identitas Sistem Penjaminan Mutu Internal">
            <img src="<?php echo html_escape(base_url('assets/img/login-bg.jpg')); ?>" alt="Lingkungan kampus" class="auth-visual-image">
            <div class="auth-visual-overlay" aria-hidden="true"></div>
            <div class="auth-visual-content tw-relative tw-z-10 tw-flex tw-h-full tw-min-h-[220px] tw-flex-col tw-justify-end tw-p-6 sm:tw-p-10 lg:tw-p-14">
                <div class="tw-max-w-xl tw-text-white">
                    <p class="tw-m-0 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.22em] tw-text-blue-100">Audit Mutu Internal</p>
                    <h2 class="tw-mb-0 tw-mt-3 tw-text-2xl tw-font-bold tw-leading-tight sm:tw-text-3xl lg:tw-text-4xl">Pemulihan akses aman untuk kelancaran penjaminan mutu.</h2>
                    <p class="tw-mb-0 tw-mt-3 tw-max-w-lg tw-text-sm tw-leading-6 tw-text-slate-200">Gunakan email institusi terdaftar untuk menerima tautan resmi pengaturan ulang kata sandi.</p>
                </div>
            </div>
        </section>

        <!-- Form Section (Right) -->
        <section class="auth-form-side tw-px-5 tw-py-10 sm:tw-px-10 lg:tw-px-14" aria-label="Form lupa password">
            <div class="auth-panel">
                <div class="tw-mb-6">
                    <div class="auth-brand-row tw-mb-5 tw-flex tw-items-center tw-gap-3 sm:tw-gap-3.5">
                        <img src="<?php echo html_escape(base_url('assets/img/Logo-UNMUH-BABEL-Web.png')); ?>" alt="Logo Universitas Muhammadiyah Bangka Belitung" class="auth-brand-unmuh">
                        <div class="auth-brand-sep" aria-hidden="true"></div>
                        <img src="<?php echo html_escape(base_url('assets/img/logo-2.png')); ?>" alt="Logo LPM" class="auth-brand-lpm">
                    </div>
                    <p class="tw-mb-1.5 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.18em] tw-text-blue-700">Pemulihan Akun</p>
                    <h1 id="forgot-password-title" class="tw-m-0 tw-text-2xl tw-font-bold tw-tracking-tight tw-text-slate-950 sm:tw-text-3xl">
                        Lupa Password?
                    </h1>
                    <p class="tw-mb-0 tw-mt-2 tw-text-sm tw-leading-6 tw-text-slate-500">
                        Masukkan email akun Anda. Kami akan mengirimkan instruksi untuk mengatur ulang kata sandi.
                    </p>
                </div>

                <!-- Flash Success Alert -->
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="tw-mb-5 tw-flex tw-items-center tw-gap-2.5 tw-rounded-xl tw-border tw-border-emerald-200 tw-bg-emerald-50 tw-p-3.5 tw-text-xs tw-text-emerald-800" role="status">
                        <span class="tw-text-emerald-600 tw-flex-shrink-0"><?php echo $icon('check-circle'); ?></span>
                        <span class="tw-font-medium"><?php echo html_escape($this->session->flashdata('success')); ?></span>
                    </div>
                <?php endif; ?>

                <!-- General Validation Error Alert -->
                <?php if (validation_errors()): ?>
                    <div class="tw-mb-5 tw-flex tw-items-center tw-gap-2.5 tw-rounded-xl tw-border tw-border-red-200 tw-bg-red-50 tw-p-3.5 tw-text-xs tw-text-red-800" role="alert">
                        <span class="tw-text-red-600 tw-flex-shrink-0"><?php echo $icon('alert-circle'); ?></span>
                        <span class="tw-font-medium"><?php echo html_escape(strip_tags(validation_errors())); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Form Request Password Reset -->
                <?php echo form_open('auth/forgot-password/request', ['class' => 'tw-space-y-4', 'id' => 'forgot-password-form']); ?>
                    <div>
                        <label for="email" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Email
                        </label>
                        <div class="tw-relative tw-w-full">
                            <span class="tw-absolute tw-left-3.5 tw-top-1/2 -tw-translate-y-1/2 tw-text-slate-400 tw-pointer-events-none">
                                <?php echo $icon('mail'); ?>
                            </span>
                            <input type="email" name="email" id="email" value="<?php echo html_escape(set_value('email')); ?>" required autocomplete="email" placeholder="nama@universitas.ac.id" class="tw-block tw-h-12 tw-w-full tw-min-w-0 tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-pl-10 tw-pr-3.5 tw-text-sm tw-text-slate-900 focus:tw-border-blue-600 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-blue-100 tw-transition">
                        </div>
                        <?php if (form_error('email')): ?>
                            <div class="tw-mt-1.5 tw-text-xs tw-text-red-600 tw-font-medium"><?php echo html_escape(strip_tags(form_error('email'))); ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Submit Button -->
                    <div class="tw-pt-2">
                        <button type="submit" class="tw-button-primary tw-w-full tw-min-h-[44px] tw-text-sm tw-shadow-md tw-shadow-blue-900/10">
                            <?php echo $icon('send'); ?>
                            <span>Kirim Instruksi</span>
                        </button>
                    </div>
                <?php echo form_close(); ?>

                <!-- Back to Login Link -->
                <div class="tw-text-center tw-mt-6">
                    <a href="<?php echo site_url('auth'); ?>" class="tw-inline-flex tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-text-slate-600 hover:tw-text-blue-600 tw-transition">
                        <?php echo $icon('arrow-left'); ?>
                        <span>Kembali ke login</span>
                    </a>
                </div>

                <!-- Footer Copyright -->
                <div class="tw-mt-8 tw-border-t tw-border-slate-200 tw-pt-5 tw-text-center tw-text-xs tw-text-slate-400">
                    &copy; <?php echo date('Y'); ?> Sistem Penjaminan Mutu Internal. All rights reserved.
                </div>
            </div>
        </section>
    </main>
</body>
</html>
