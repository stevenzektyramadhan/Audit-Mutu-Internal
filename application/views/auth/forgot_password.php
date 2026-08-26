<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - AMI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <style>
        :root { --auth-bg: #f3f5f8; --auth-card: #ffffff; --auth-text: #1a1a2e; --auth-muted: #555770; --auth-label: #374151; --auth-border: #d1d5db; --auth-green: #1b5e20; --auth-green-hover: #145218; --auth-danger: #991b1b; --auth-danger-bg: #fef2f2; --auth-danger-border: #fecaca; --auth-success: #166534; --auth-success-bg: #f0fdf4; --auth-success-border: #bbf7d0; }
        *, *::before, *::after { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; display: flex; flex-direction: column; background: var(--auth-bg); color: var(--auth-text); font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .auth-bg { position: fixed; inset: 0; z-index: 0; background: url('<?php echo html_escape(base_url('assets/img/login-bg.jpg')); ?>') center/cover; }
        .auth-bg::after { content: ''; position: absolute; inset: 0; background: rgba(0, 0, 0, .35); }
        .auth-navbar, .auth-footer { position: fixed; left: 0; right: 0; z-index: 2; display: flex; align-items: center; justify-content: space-between; padding: 0 28px; background: rgba(27, 94, 32, .9); color: rgba(255, 255, 255, .85); }
        .auth-navbar { top: 0; height: 56px; }
        .auth-footer { bottom: 0; min-height: 44px; font-size: 11px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; }
        .auth-brand { color: #fff; font-size: 20px; font-weight: 800; text-decoration: none; }
        .auth-content { position: relative; z-index: 1; flex: 1; display: flex; align-items: center; justify-content: center; padding: 80px 20px 60px; }
        .auth-card { width: 100%; max-width: 460px; padding: 36px; background: var(--auth-card); border: 1px solid rgba(0, 0, 0, .08); border-radius: 16px; box-shadow: 0 20px 60px rgba(0, 0, 0, .15); }
        h1 { margin: 0 0 8px; color: var(--auth-green); font-size: 28px; font-weight: 800; text-align: center; }
        .auth-intro { margin: 0 0 28px; color: var(--auth-muted); font-size: 14px; line-height: 1.6; text-align: center; }
        .auth-alert { margin-bottom: 20px; padding: 12px 16px; border: 1px solid var(--auth-danger-border); border-radius: 10px; background: var(--auth-danger-bg); color: var(--auth-danger); font-size: 13px; }
        .auth-alert--success { border-color: var(--auth-success-border); background: var(--auth-success-bg); color: var(--auth-success); }
        .auth-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: var(--auth-label); font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .auth-input { width: 100%; height: 50px; padding: 0 16px; border: 1.5px solid var(--auth-border); border-radius: 10px; color: var(--auth-text); font: inherit; font-size: 15px; }
        .auth-input:focus { border-color: var(--auth-green); box-shadow: 0 0 0 3px rgba(27, 94, 32, .15); outline: 0; }
        .auth-error { display: block; margin-top: 6px; color: #ef4444; font-size: 12px; font-weight: 500; }
        .auth-button { width: 100%; min-height: 52px; margin-top: 8px; border: 0; border-radius: 10px; background: var(--auth-green); color: #fff; font: inherit; font-size: 15px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; cursor: pointer; }
        .auth-button:hover, .auth-button:focus { background: var(--auth-green-hover); outline: 3px solid rgba(27, 94, 32, .2); outline-offset: 2px; }
        .auth-link { display: block; margin-top: 20px; color: var(--auth-muted); font-size: 14px; font-weight: 600; text-align: center; text-decoration: none; }
        .auth-link:hover, .auth-link:focus { color: var(--auth-green); text-decoration: underline; }
        @media (max-width: 576px) { .auth-navbar, .auth-footer { padding: 0 16px; } .auth-card { padding: 28px 24px; border-radius: 12px; } .auth-footer { justify-content: center; text-align: center; } }
    </style>
</head>
<body>
<div class="auth-bg" aria-hidden="true"></div>
<nav class="auth-navbar"><a class="auth-brand" href="<?php echo site_url('auth'); ?>">AMI System</a></nav>
<main class="auth-content">
    <section class="auth-card" aria-labelledby="forgot-password-title">
        <h1 id="forgot-password-title">Lupa Password?</h1>
        <p class="auth-intro">Masukkan email Anda untuk menerima instruksi pengaturan ulang kata sandi.</p>

        <?php if ($this->session->flashdata('success')): ?>
            <div class="auth-alert auth-alert--success" role="status"><?php echo html_escape($this->session->flashdata('success')); ?></div>
        <?php endif; ?>
        <?php if (validation_errors()): ?>
            <div class="auth-alert" role="alert"><?php echo html_escape(strip_tags(validation_errors())); ?></div>
        <?php endif; ?>

        <?php echo form_open('auth/forgot-password/request'); ?>
            <div class="auth-group">
                <label for="email">Email</label>
                <input class="auth-input" type="email" id="email" name="email" autocomplete="email" value="<?php echo html_escape(set_value('email')); ?>" required>
                <?php if (form_error('email')): ?><small class="auth-error"><?php echo html_escape(strip_tags(form_error('email'))); ?></small><?php endif; ?>
            </div>
            <button class="auth-button" type="submit">Kirim Instruksi</button>
        <?php echo form_close(); ?>

        <a class="auth-link" href="<?php echo site_url('auth'); ?>">Kembali ke login</a>
    </section>
</main>
<footer class="auth-footer"><span>&copy; <?php echo date('Y'); ?> University Internal Audit System. All Rights Reserved.</span></footer>
</body>
</html>
