<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AMI</title>
    <script>
        (function () {
            try {
                var theme = localStorage.getItem('ami-theme');
                if (theme === 'light' || theme === 'dark') document.documentElement.setAttribute('data-theme', theme);
            } catch (error) {}
        })();
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { min-height: 100vh; background: #161616 !important; color: #e1e3e6; }
        .card { background: #222222; border: 1px solid #33373e; border-radius: 10px; }
        .form-control { background: #191b1f; border-color: #4a4f57; color: #e1e3e6; }
        .form-control:focus { background: #191b1f; border-color: #4da3ff; color: #fff; box-shadow: 0 0 0 .2rem rgba(77,163,255,.18); }
        .ami-password-wrap { position: relative; }
        .ami-password-wrap .form-control { padding-right: 44px; }
        .ami-password-toggle { position: absolute; top: 50%; right: 5px; transform: translateY(-50%); width: 34px; height: 34px; border: 0; border-radius: 6px; background: transparent; color: #a1a5ab; cursor: pointer; }
        .ami-password-toggle:hover, .ami-password-toggle:focus { color: #fff; background: rgba(255,255,255,.07); outline: 0; }
        .ami-login-theme { position: fixed; top: 18px; right: 18px; z-index: 10; min-height: 38px; padding: 7px 11px; border: 1px solid #4a4f57; border-radius: 7px; background: #222; color: #e1e3e6; display: inline-flex; align-items: center; gap: 7px; font-size: 12px; font-weight: 700; cursor: pointer; }
        .ami-login-theme:hover, .ami-login-theme:focus { border-color: #4da3ff; color: #4da3ff; outline: 0; }
        html[data-theme="light"] body { background: #f3f5f8 !important; color: #202733; }
        html[data-theme="light"] .card { background: #fff; border-color: #d7dde6; }
        html[data-theme="light"] .form-control { background: #fff; border-color: #bdc6d2; color: #202733; }
        html[data-theme="light"] .form-control:focus { background: #fff; color: #202733; }
        html[data-theme="light"] .ami-password-toggle:hover, html[data-theme="light"] .ami-password-toggle:focus { color: #185fa5; background: #edf5fd; }
        html[data-theme="light"] .ami-login-theme { background: #fff; border-color: #d7dde6; color: #344054; }
    </style>
</head>
<body>
<button type="button" class="ami-login-theme" data-theme-toggle aria-label="Aktifkan mode terang">
    <i class="fas fa-sun" data-theme-icon aria-hidden="true"></i>
    <span class="d-none d-sm-inline" data-theme-label>Mode terang</span>
</button>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-4">Login AMI</h4>
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('error')); ?></div>
                    <?php endif; ?>
                    <?php echo form_open('auth/login'); ?>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?php echo set_value('email'); ?>" required>
                            <?php echo form_error('email', '<small class="text-danger">', '</small>'); ?>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="ami-password-wrap">
                                <input type="password" name="password" id="password" class="form-control" required>
                                <button type="button" class="ami-password-toggle" data-password-toggle="password" aria-label="Tampilkan password" aria-pressed="false">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            <?php echo form_error('password', '<small class="text-danger">', '</small>'); ?>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Masuk</button>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    'use strict';
    var themeToggle = document.querySelector('[data-theme-toggle]');
    function getTheme() {
        return document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
    }
    function renderThemeToggle() {
        var light = getTheme() === 'light';
        var icon = themeToggle.querySelector('[data-theme-icon]');
        var label = themeToggle.querySelector('[data-theme-label]');
        themeToggle.setAttribute('aria-label', light ? 'Aktifkan mode gelap' : 'Aktifkan mode terang');
        if (label) label.textContent = light ? 'Mode gelap' : 'Mode terang';
        icon.classList.toggle('fa-sun', !light);
        icon.classList.toggle('fa-moon', light);
    }
    renderThemeToggle();
    themeToggle.addEventListener('click', function () {
        var theme = getTheme() === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', theme);
        try { localStorage.setItem('ami-theme', theme); } catch (error) {}
        renderThemeToggle();
    });

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
