<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$icon = static function ($name) {
    $paths = [
        'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'lock' => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'eye' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off' => '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>',
        'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
        'alert-circle' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
    ];
    return '<svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['lock']) . '</svg>';
};
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AMI Sistem Penjaminan Mutu Internal</title>
    <meta name="description" content="Login ke Sistem Audit Mutu Internal Perguruan Tinggi">
    <link rel="icon" href="<?php echo html_escape(base_url('favicon.ico')); ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo html_escape(base_url('assets/css/auth.css')); ?>">
</head>
<body id="auth-root" class="tw-m-0 tw-min-h-screen tw-bg-slate-50 tw-text-slate-900 tw-font-sans">
    <main class="auth-shell">
        <section class="auth-visual" aria-label="Identitas Sistem Penjaminan Mutu Internal">
            <img src="<?php echo html_escape(base_url('assets/img/login-bg.jpg')); ?>" alt="Lingkungan kampus" class="auth-visual-image">
            <div class="auth-visual-overlay" aria-hidden="true"></div>
            <div class="auth-visual-content tw-relative tw-z-10 tw-flex tw-h-full tw-min-h-[220px] tw-flex-col tw-justify-between tw-p-6 sm:tw-p-10 lg:tw-p-14">
                <div class="tw-flex tw-items-center">
                    <div class="tw-flex tw-items-center tw-gap-2.5 tw-rounded-xl tw-border tw-border-white/25 tw-bg-white/80 tw-px-3 tw-py-2 tw-shadow-sm tw-backdrop-blur-md">
                        <img src="<?php echo html_escape(base_url('assets/img/logo-1.png')); ?>" alt="Logo Universitas" class="tw-h-9 tw-w-auto tw-object-contain">
                        <div class="tw-h-7 tw-w-px tw-bg-slate-200"></div>
                        <img src="<?php echo html_escape(base_url('assets/img/logo-2.png')); ?>" alt="Logo LPM" class="tw-h-9 tw-w-auto tw-object-contain">
                    </div>
                </div>
                <div class="tw-max-w-xl tw-text-white">
                    <p class="tw-m-0 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.22em] tw-text-blue-100">Audit Mutu Internal</p>
                    <h2 class="tw-mb-0 tw-mt-3 tw-text-2xl tw-font-bold tw-leading-tight sm:tw-text-3xl lg:tw-text-4xl">Budaya mutu tumbuh dari proses yang jelas dan bukti yang tepercaya.</h2>
                    <p class="tw-mb-0 tw-mt-3 tw-max-w-lg tw-text-sm tw-leading-6 tw-text-slate-200">Ruang kerja terpadu untuk pelaksanaan, penilaian, dan tindak lanjut Sistem Penjaminan Mutu Internal perguruan tinggi.</p>
                </div>
            </div>
        </section>

        <section class="auth-form-side tw-px-5 tw-py-10 sm:tw-px-10 lg:tw-px-14" aria-label="Form login">
            <div class="auth-panel">
                <div class="tw-mb-7">
                    <p class="tw-mb-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.18em] tw-text-blue-700">Selamat datang</p>
                    <h1 class="tw-m-0 tw-text-2xl tw-font-bold tw-tracking-tight tw-text-slate-950 sm:tw-text-3xl">
                    Sistem Penjaminan Mutu Internal
                </h1>
                    <p class="tw-mb-0 tw-mt-2 tw-text-sm tw-leading-6 tw-text-slate-500">
                    Masuk ke akun Anda untuk mengakses instrumen dan penilaian.
                </p>
            </div>

            <!-- Flash Error Alert -->
            <?php if ($this->session->flashdata('error')): ?>
                <div class="tw-mb-5 tw-flex tw-items-center tw-gap-2.5 tw-rounded-xl tw-border tw-border-red-200 tw-bg-red-50 tw-p-3.5 tw-text-xs tw-text-red-800" role="alert">
                    <span class="tw-text-red-600 tw-flex-shrink-0"><?php echo $icon('alert-circle'); ?></span>
                    <span class="tw-font-medium"><?php echo html_escape($this->session->flashdata('error')); ?></span>
                </div>
            <?php endif; ?>

            <!-- Flash Success Alert (e.g. after password reset) -->
            <?php if ($this->session->flashdata('success')): ?>
                <div class="tw-mb-5 tw-flex tw-items-center tw-gap-2.5 tw-rounded-xl tw-border tw-border-emerald-200 tw-bg-emerald-50 tw-p-3.5 tw-text-xs tw-text-emerald-800" role="alert">
                    <span class="tw-text-emerald-600 tw-flex-shrink-0"><?php echo $icon('check-circle'); ?></span>
                    <span class="tw-font-medium"><?php echo html_escape($this->session->flashdata('success')); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('warning')): ?>
                <div class="tw-mb-5 tw-flex tw-items-center tw-gap-2.5 tw-rounded-xl tw-border tw-border-amber-200 tw-bg-amber-50 tw-p-3.5 tw-text-xs tw-text-amber-800" role="alert">
                    <span class="tw-text-amber-600 tw-flex-shrink-0"><?php echo $icon('alert-circle'); ?></span>
                    <span class="tw-font-medium"><?php echo html_escape($this->session->flashdata('warning')); ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <?php echo form_open('auth/login', ['class' => 'tw-space-y-4', 'id' => 'login-form']); ?>

                <!-- Email Field -->
                <div>
                    <label for="email" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                        Email
                    </label>
                    <div class="tw-relative tw-w-full">
                        <span class="tw-absolute tw-left-3.5 tw-top-1/2 -tw-translate-y-1/2 tw-text-slate-400 tw-pointer-events-none">
                            <?php echo $icon('mail'); ?>
                        </span>
                        <input type="email" name="email" id="email" value="<?php echo set_value('email'); ?>" required autocomplete="email" placeholder="nama@universitas.ac.id" class="tw-block tw-h-12 tw-w-full tw-min-w-0 tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-pl-10 tw-pr-3.5 tw-text-sm tw-text-slate-900 focus:tw-border-blue-600 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-blue-100 tw-transition">
                    </div>
                    <?php echo form_error('email', '<div class="tw-mt-1.5 tw-text-xs tw-text-red-600 tw-font-medium">', '</div>'); ?>
                </div>

                <!-- Password Field -->
                <div>
                    <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-x-3 tw-gap-y-1 tw-mb-1.5">
                        <label for="password" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700">
                            Password
                        </label>
                        <a href="<?php echo site_url('auth/forgot-password'); ?>" class="tw-ml-auto tw-max-w-full tw-text-right tw-text-xs tw-font-semibold tw-leading-5 tw-text-blue-600 hover:tw-underline">
                            Lupa password?
                        </a>
                    </div>
                    <div class="tw-relative tw-w-full">
                        <span class="tw-absolute tw-left-3.5 tw-top-1/2 -tw-translate-y-1/2 tw-text-slate-400 tw-pointer-events-none">
                            <?php echo $icon('lock'); ?>
                        </span>
                        <input type="password" name="password" id="password" required autocomplete="current-password" placeholder="••••••••" class="tw-block tw-h-12 tw-w-full tw-min-w-0 tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-pl-10 tw-pr-12 tw-text-sm tw-text-slate-900 focus:tw-border-blue-600 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-blue-100 tw-transition">
                        <button type="button" id="password-toggle-btn" class="tw-absolute tw-right-0.5 tw-top-1/2 -tw-translate-y-1/2 tw-flex tw-h-11 tw-w-11 tw-items-center tw-justify-center tw-rounded-full tw-border-0 tw-bg-transparent tw-p-0 tw-text-slate-400 tw-transition hover:tw-bg-slate-100/80 hover:tw-text-slate-700 focus:tw-bg-slate-100 focus:tw-text-slate-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-200" aria-label="Tampilkan password" aria-pressed="false">
                            <span id="icon-eye"><?php echo $icon('eye'); ?></span>
                            <span id="icon-eye-off" class="tw-hidden"><?php echo $icon('eye-off'); ?></span>
                        </button>
                    </div>
                    <?php echo form_error('password', '<div class="tw-mt-1.5 tw-text-xs tw-text-red-600 tw-font-medium">', '</div>'); ?>
                </div>

                <!-- Submit Button -->
                <div class="tw-pt-2">
                    <button type="submit" class="tw-button-primary tw-w-full tw-min-h-[44px] tw-text-sm tw-shadow-md tw-shadow-blue-900/10">
                        <span>Masuk</span>
                        <?php echo $icon('arrow-right'); ?>
                    </button>
                </div>

            <?php echo form_close(); ?>
                <div class="tw-mt-7 tw-border-t tw-border-slate-200 tw-pt-5 tw-text-center tw-text-xs tw-text-slate-400">
                    &copy; <?php echo date('Y'); ?> Sistem Penjaminan Mutu Internal. All rights reserved.
                </div>
            </div>
        </section>
    </main>

    <!-- Password Visibility Toggle Script (Vanilla JS) -->
    <script>
    (function () {
        'use strict';
        var passwordInput = document.getElementById('password');
        var toggleBtn = document.getElementById('password-toggle-btn');
        var iconEye = document.getElementById('icon-eye');
        var iconEyeOff = document.getElementById('icon-eye-off');

        if (!passwordInput || !toggleBtn) return;

        toggleBtn.addEventListener('click', function () {
            var isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            toggleBtn.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
            toggleBtn.setAttribute('aria-pressed', isPassword ? 'true' : 'false');

            if (iconEye && iconEyeOff) {
                iconEye.classList.toggle('tw-hidden', isPassword);
                iconEyeOff.classList.toggle('tw-hidden', !isPassword);
            }
        });
    })();
    </script>
</body>
</html>
