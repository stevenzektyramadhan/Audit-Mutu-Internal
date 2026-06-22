<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AMI Sistem Penjaminan Mutu Internal</title>
    <meta name="description" content="Login ke Sistem Audit Mutu Internal Perguruan Tinggi">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ===== CSS Variables ===== */
        :root {
            --login-bg: #f3f5f8;
            --card-bg: #ffffff;
            --card-border: rgba(0,0,0,0.08);
            --card-shadow: 0 20px 60px rgba(0,0,0,0.15), 0 8px 20px rgba(0,0,0,0.1);
            --text-primary: #1a1a2e;
            --text-secondary: #555770;
            --text-muted: #8e90a6;
            --input-bg: #ffffff;
            --input-border: #d1d5db;
            --input-focus-border: #1b5e20;
            --input-focus-shadow: 0 0 0 3px rgba(27,94,32,0.15);
            --input-text: #1a1a2e;
            --input-placeholder: #9ca3af;
            --btn-bg: #1b5e20;
            --btn-bg-hover: #145218;
            --btn-text: #ffffff;
            --label-color: #374151;
            --navbar-bg: rgba(27,94,32,0.9);
            --navbar-text: #ffffff;
            --footer-bg: rgba(27,94,32,0.92);
            --footer-text: rgba(255,255,255,0.85);
            --overlay-color: rgba(0,0,0,0.35);
            --alert-danger-bg: #fef2f2;
            --alert-danger-border: #fecaca;
            --alert-danger-text: #991b1b;
            --toggle-bg: rgba(255,255,255,0.15);
            --toggle-border: rgba(255,255,255,0.3);
            --toggle-text: #ffffff;
            --icon-color: #9ca3af;
        }



        /* ===== Reset & Base ===== */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--login-bg);
            color: var(--text-primary);
            overflow-x: hidden;
        }

        /* ===== Background ===== */
        .login-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
        }

        .login-bg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .login-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--overlay-color);
        }

        /* ===== Navbar ===== */
        .login-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            height: 56px;
            background: var(--navbar-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .login-navbar__brand {
            font-size: 20px;
            font-weight: 800;
            color: var(--navbar-text);
            letter-spacing: -0.02em;
            text-decoration: none;
        }

        .login-navbar__brand:hover {
            color: var(--navbar-text);
            text-decoration: none;
        }

        /* ===== Theme Toggle ===== */
        .ami-login-theme {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border: 1px solid var(--toggle-border);
            border-radius: 8px;
            background: var(--toggle-bg);
            color: var(--toggle-text);
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .ami-login-theme:hover,
        .ami-login-theme:focus {
            background: rgba(255,255,255,0.25);
            border-color: rgba(255,255,255,0.5);
            outline: 0;
            transform: translateY(-1px);
        }

        .ami-login-theme i {
            font-size: 14px;
        }

        /* ===== Main Content ===== */
        .login-content {
            position: relative;
            z-index: 10;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 80px 20px 60px;
        }

        /* ===== Login Card ===== */
        .login-card {
            width: 100%;
            max-width: 460px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            animation: cardSlideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        @keyframes cardSlideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== Logo Row ===== */
        .login-logos {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
            padding: 28px 24px 0;
        }

        .login-logos img {
            height: 60px;
            width: auto;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .login-logos img:hover {
            transform: scale(1.08);
        }

        /* ===== Card Body ===== */
        .login-card__body {
            padding: 24px 36px 36px;
        }

        .login-card__title {
            font-size: 28px;
            font-weight: 800;
            color: var(--btn-bg);
            text-align: center;
            margin-bottom: 4px;
            letter-spacing: -0.02em;
        }

        .login-card__subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            text-align: center;
            margin-bottom: 28px;
            font-weight: 400;
        }

        /* ===== Alert ===== */
        .login-alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
            border: 1px solid var(--alert-danger-border);
            background: var(--alert-danger-bg);
            color: var(--alert-danger-text);
            animation: alertFadeIn 0.3s ease;
        }

        @keyframes alertFadeIn {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .login-alert i {
            margin-right: 8px;
        }

        /* ===== Form ===== */
        .login-form-group {
            margin-bottom: 20px;
        }

        .login-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: var(--label-color);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
        }

        .login-input-wrap {
            position: relative;
        }

        .login-input-wrap .form-control {
            width: 100%;
            height: 50px;
            padding: 0 48px 0 16px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            color: var(--input-text);
            background: var(--input-bg);
            border: 1.5px solid var(--input-border);
            border-radius: 10px;
            transition: all 0.25s ease;
        }

        .login-input-wrap .form-control::placeholder {
            color: var(--input-placeholder);
        }

        .login-input-wrap .form-control:focus {
            border-color: var(--input-focus-border);
            box-shadow: var(--input-focus-shadow);
            outline: none;
            background: var(--input-bg);
            color: var(--input-text);
        }

        .login-input-icon {
            position: absolute;
            top: 50%;
            right: 16px;
            transform: translateY(-50%);
            color: var(--icon-color);
            font-size: 16px;
            pointer-events: none;
        }

        /* Password toggle overrides pointer-events */
        .ami-password-toggle {
            position: absolute;
            top: 50%;
            right: 6px;
            transform: translateY(-50%);
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: var(--icon-color);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.2s ease;
        }

        .ami-password-toggle:hover,
        .ami-password-toggle:focus {
            color: var(--btn-bg);
            background: rgba(27,94,32,0.08);
            outline: 0;
        }

        html[data-theme="dark"] .ami-password-toggle:hover,
        html[data-theme="dark"] .ami-password-toggle:focus {
            color: #4caf50;
            background: rgba(76,175,80,0.12);
        }

        .login-error-text {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: #ef4444;
            font-weight: 500;
        }

        /* ===== Submit Button ===== */
        .login-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            height: 52px;
            margin-top: 28px;
            padding: 0 24px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--btn-text);
            background: var(--btn-bg);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 50%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .login-btn:hover {
            background: var(--btn-bg-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(27,94,32,0.35);
        }

        .login-btn:hover::before {
            opacity: 1;
        }

        .login-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(27,94,32,0.2);
        }

        .login-btn i {
            font-size: 14px;
            transition: transform 0.3s ease;
        }

        .login-btn:hover i {
            transform: translateX(4px);
        }

        /* ===== Lupa Password ===== */
        .login-forgot {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s ease;
            cursor: default;
        }

        .login-forgot:hover {
            color: var(--btn-bg);
            text-decoration: none;
        }

        /* ===== Footer ===== */
        .login-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            height: 44px;
            background: var(--footer-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .login-footer__copy {
            color: var(--footer-text);
        }

        .login-footer__links {
            display: flex;
            gap: 24px;
        }

        .login-footer__links a {
            color: var(--footer-text);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .login-footer__links a:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        /* ===== Responsive ===== */
        @media (max-width: 576px) {
            .login-navbar { padding: 0 16px; }
            .login-navbar__brand { font-size: 17px; }
            .login-card { max-width: 100%; border-radius: 12px; }
            .login-card__body { padding: 20px 24px 28px; }
            .login-card__title { font-size: 24px; }
            .login-logos img { height: 45px; }
            .login-logos { gap: 12px; padding: 20px 16px 0; }
            .login-footer { 
                flex-direction: column; 
                height: auto; 
                padding: 10px 16px;
                gap: 4px;
                text-align: center;
            }
            .login-footer__links { gap: 16px; }
        }
    </style>
</head>
<body>

<!-- Background Image -->
<div class="login-bg">
    <img src="<?= base_url('assets/img/login-bg.jpg'); ?>" alt="Campus Background">
</div>

<!-- Top Navbar -->
<nav class="login-navbar">
    <a href="<?= base_url('auth'); ?>" class="login-navbar__brand">AMI System</a>

</nav>

<!-- Main Content -->
<main class="login-content">
    <div class="login-card">
        <!-- Logos -->
        <div class="login-logos">
            <img src="<?= base_url('assets/img/logo-1.png'); ?>" alt="Logo Universitas">
            <img src="<?= base_url('assets/img/logo-2.png'); ?>" alt="Logo AMI">
            <img src="<?= base_url('assets/img/logo-3.png'); ?>" alt="Logo Diktisaintek">
        </div>

        <!-- Card Body -->
        <div class="login-card__body">
            <h1 class="login-card__title">Login AMI</h1>
            <p class="login-card__subtitle">Masuk ke Sistem Penjaminan Mutu Internal</p>

            <!-- Flash Error -->
            <?php if ($this->session->flashdata('error')): ?>
                <div class="login-alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo html_escape($this->session->flashdata('error')); ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <?php echo form_open('auth/login'); ?>

                <!-- Email -->
                <div class="login-form-group">
                    <label for="email" class="login-label">Email</label>
                    <div class="login-input-wrap">
                        <input type="email" name="email" id="email" class="form-control"
                               placeholder="nama@universitas.ac.id"
                               value="<?php echo set_value('email'); ?>" required>
                        <span class="login-input-icon">
                            <i class="fas fa-envelope"></i>
                        </span>
                    </div>
                    <?php echo form_error('email', '<small class="login-error-text">', '</small>'); ?>
                </div>

                <!-- Password -->
                <div class="login-form-group">
                    <label for="password" class="login-label">Password</label>
                    <div class="login-input-wrap">
                        <input type="password" name="password" id="password" class="form-control"
                               placeholder="••••••••" required>
                        <button type="button" class="ami-password-toggle"
                                data-password-toggle="password"
                                aria-label="Tampilkan password" aria-pressed="false">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    <?php echo form_error('password', '<small class="login-error-text">', '</small>'); ?>
                </div>

                <!-- Submit -->
                <button type="submit" class="login-btn">
                    Masuk <i class="fas fa-arrow-right"></i>
                </button>

            <?php echo form_close(); ?>

            <!-- Lupa Password (placeholder, no action in MVP) -->
            <span class="login-forgot">Lupa password?</span>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="login-footer">
    <span class="login-footer__copy">&copy; <?= date('Y'); ?> University Internal Audit System. All Rights Reserved.</span>
    <div class="login-footer__links">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Help Center</a>
    </div>
</footer>

<!-- Scripts -->
<script>
(function () {
    'use strict';

    // ===== Password Toggle =====
    var toggle = document.querySelector('[data-password-toggle]');
    if (!toggle) return;

    toggle.addEventListener('click', function () {
        var input = document.getElementById(toggle.getAttribute('data-password-toggle'));
        if (!input) return;
        var showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        toggle.setAttribute('aria-label', showing ? 'Tampilkan password' : 'Sembunyikan password');
        toggle.setAttribute('aria-pressed', showing ? 'false' : 'true');
        var icon = toggle.querySelector('i');
        icon.classList.toggle('fa-eye', showing);
        icon.classList.toggle('fa-eye-slash', !showing);
    });
})();
</script>

</body>
</html>
