<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
    </div>
</main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    'use strict';

    var app = document.querySelector('.ami-app');
    var sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    var sidebarClosers = document.querySelectorAll('[data-sidebar-close]');
    var themeToggle = document.querySelector('[data-theme-toggle]');

    function getTheme() {
        return document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
    }

    function renderThemeToggle() {
        if (!themeToggle) return;
        var light = getTheme() === 'light';
        var label = themeToggle.querySelector('[data-theme-label]');
        var icon = themeToggle.querySelector('[data-theme-icon]');
        themeToggle.setAttribute('aria-label', light ? 'Aktifkan mode gelap' : 'Aktifkan mode terang');
        if (label) label.textContent = light ? 'Mode gelap' : 'Mode terang';
        if (icon) {
            icon.classList.toggle('fa-sun', !light);
            icon.classList.toggle('fa-moon', light);
        }
    }

    if (themeToggle) {
        renderThemeToggle();
        themeToggle.addEventListener('click', function () {
            var theme = getTheme() === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', theme);
            try { localStorage.setItem('ami-theme', theme); } catch (error) {}
            renderThemeToggle();
        });
    }

    function setSidebar(open) {
        if (!app) return;
        app.classList.toggle('sidebar-open', open);
        document.body.classList.toggle('ami-sidebar-lock', open);
        if (sidebarToggle) sidebarToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            setSidebar(!app.classList.contains('sidebar-open'));
        });
    }

    sidebarClosers.forEach(function (closer) {
        closer.addEventListener('click', function () { setSidebar(false); });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') setSidebar(false);
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) setSidebar(false);
    });

    document.querySelectorAll('.ami-sidebar .ami-nav-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth < 992) setSidebar(false);
        });
    });

    function restoreSubmitState() {
        document.querySelectorAll('form[data-submitting="true"]').forEach(function (form) {
            form.removeAttribute('data-submitting');
            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (button) {
                button.disabled = false;
                if (button.dataset.originalHtml !== undefined) {
                    button.innerHTML = button.dataset.originalHtml;
                    delete button.dataset.originalHtml;
                }
            });
        });
    }

    document.querySelectorAll('form').forEach(function (form) {
        if ((form.getAttribute('method') || 'get').toLowerCase() !== 'post') return;

        form.addEventListener('submit', function (event) {
            if (event.defaultPrevented) return;
            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            form.dataset.submitting = 'true';
            var submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (button) {
                button.disabled = true;
            });

            if (submitter && submitter.tagName === 'BUTTON') {
                submitter.dataset.originalHtml = submitter.innerHTML;
                var loadingText = submitter.getAttribute('data-loading-text') || 'Memproses...';
                submitter.innerHTML = '<span class="ami-loading-spinner" aria-hidden="true"></span><span>' + loadingText + '</span>';
            }
        });
    });

    document.querySelectorAll('[data-password-toggle]').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var inputId = toggle.getAttribute('data-password-toggle');
            var input = document.getElementById(inputId);
            if (!input) return;

            var showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            toggle.setAttribute('aria-label', showing ? 'Tampilkan password' : 'Sembunyikan password');
            toggle.setAttribute('aria-pressed', showing ? 'false' : 'true');
            var icon = toggle.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye', showing);
                icon.classList.toggle('fa-eye-slash', !showing);
            }
        });
    });

    window.addEventListener('pageshow', restoreSubmitState);
})();
</script>
</body>
</html>
