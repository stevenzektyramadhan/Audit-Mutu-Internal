<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tautan Tidak Berlaku - AMI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <style>
        :root { --auth-bg: #f3f5f8; --auth-card: #fff; --auth-text: #1a1a2e; --auth-muted: #555770; --auth-green: #1b5e20; --auth-green-hover: #145218; }
        *, *::before, *::after { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; display: flex; flex-direction: column; background: var(--auth-bg); color: var(--auth-text); font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .auth-bg { position: fixed; inset: 0; z-index: 0; background: url('<?php echo html_escape(base_url('assets/img/login-bg.jpg')); ?>') center/cover; }
        .auth-bg::after { content: ''; position: absolute; inset: 0; background: rgba(0, 0, 0, .35); }
        .auth-navbar, .auth-footer { position: fixed; left: 0; right: 0; z-index: 2; display: flex; align-items: center; padding: 0 28px; background: rgba(27, 94, 32, .9); color: rgba(255, 255, 255, .85); }
        .auth-navbar { top: 0; height: 56px; }
        .auth-footer { bottom: 0; min-height: 44px; font-size: 11px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; }
        .auth-brand { color: #fff; font-size: 20px; font-weight: 800; text-decoration: none; }
        .auth-content { position: relative; z-index: 1; flex: 1; display: flex; align-items: center; justify-content: center; padding: 80px 20px 60px; }
        .auth-card { width: 100%; max-width: 460px; padding: 36px; background: var(--auth-card); border: 1px solid rgba(0, 0, 0, .08); border-radius: 16px; box-shadow: 0 20px 60px rgba(0, 0, 0, .15); text-align: center; }
        h1 { margin: 0 0 12px; color: var(--auth-green); font-size: 28px; font-weight: 800; }
        .auth-copy { margin: 0; color: var(--auth-muted); font-size: 14px; line-height: 1.6; }
        .auth-actions { display: grid; gap: 12px; margin-top: 28px; }
        .auth-button, .auth-link { display: block; min-height: 48px; padding: 14px 20px; border-radius: 10px; font: inherit; font-size: 14px; font-weight: 700; text-decoration: none; }
        .auth-button { background: var(--auth-green); color: #fff; }
        .auth-button:hover, .auth-button:focus { background: var(--auth-green-hover); color: #fff; outline: 3px solid rgba(27, 94, 32, .2); outline-offset: 2px; }
        .auth-link { border: 1px solid #d1d5db; color: var(--auth-muted); }
        .auth-link:hover, .auth-link:focus { border-color: var(--auth-green); color: var(--auth-green); text-decoration: underline; outline: 0; }
        @media (max-width: 576px) { .auth-navbar, .auth-footer { padding: 0 16px; } .auth-card { padding: 28px 24px; border-radius: 12px; } }
    </style>
</head>
<body>
<div class="auth-bg" aria-hidden="true"></div>
<nav class="auth-navbar"><a class="auth-brand" href="<?php echo site_url('auth'); ?>">AMI System</a></nav>
<main class="auth-content">
    <section class="auth-card" aria-labelledby="invalid-reset-title">
        <h1 id="invalid-reset-title">Tautan Tidak Berlaku</h1>
        <p class="auth-copy">Tautan pengaturan ulang password tidak dapat digunakan. Minta tautan baru untuk melanjutkan.</p>
        <div class="auth-actions">
            <a class="auth-button" href="<?php echo site_url('auth/forgot-password'); ?>">Minta Tautan Baru</a>
            <a class="auth-link" href="<?php echo site_url('auth'); ?>">Kembali ke login</a>
        </div>
    </section>
</main>
<footer class="auth-footer"><span>&copy; <?php echo date('Y'); ?> University Internal Audit System. All Rights Reserved.</span></footer>
</body>
</html>
