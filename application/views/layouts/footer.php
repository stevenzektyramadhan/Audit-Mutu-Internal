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
    var sidebarDesktopToggle = document.querySelector('[data-sidebar-desktop-toggle]');
    var sidebarClosers = document.querySelectorAll('[data-sidebar-close]');

    // Desktop Collapse Persistence
    try {
        var savedCollapsed = localStorage.getItem('ami_sidebar_collapsed');
        if (savedCollapsed === 'true' && window.innerWidth >= 992) {
            if (app) app.classList.add('sidebar-collapsed');
        }
    } catch (e) {}

    if (sidebarDesktopToggle) {
        sidebarDesktopToggle.addEventListener('click', function () {
            if (!app) return;
            var isCollapsed = app.classList.toggle('sidebar-collapsed');
            try {
                localStorage.setItem('ami_sidebar_collapsed', isCollapsed ? 'true' : 'false');
            } catch (e) {}
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
            if (link.hasAttribute('data-nav-disclosure')) return;
            if (window.innerWidth < 992) setSidebar(false);
        });
    });

    document.querySelectorAll('[data-nav-disclosure]').forEach(function (disclosure) {
        var controlsId = disclosure.getAttribute('aria-controls');
        var submenu = controlsId ? document.getElementById(controlsId) : null;
        var storageKey = controlsId ? 'ami_nav_disclosure_' + controlsId : null;

        if (storageKey && !disclosure.classList.contains('has-active-child') && disclosure.getAttribute('aria-expanded') !== 'true') {
            try {
                var savedState = localStorage.getItem(storageKey);
                if (savedState !== null) {
                    var shouldExpand = savedState === 'true';
                    disclosure.setAttribute('aria-expanded', shouldExpand ? 'true' : 'false');
                    if (submenu) submenu.hidden = !shouldExpand;
                }
            } catch (e) {}
        }

        disclosure.addEventListener('click', function () {
            if (app && app.classList.contains('sidebar-collapsed')) {
                app.classList.remove('sidebar-collapsed');
                try {
                    localStorage.setItem('ami_sidebar_collapsed', 'false');
                } catch (e) {}
            }
            var expanded = disclosure.getAttribute('aria-expanded') === 'true';
            var submenu = document.getElementById(disclosure.getAttribute('aria-controls'));
            disclosure.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            if (submenu) submenu.hidden = expanded;
            if (storageKey) {
                try {
                    localStorage.setItem(storageKey, expanded ? 'false' : 'true');
                } catch (e) {}
            }
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

    // ==========================================
    // Global Toast Notification Engine
    // ==========================================
    var toastContainer = document.getElementById('ami-toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'ami-toast-container';
        toastContainer.className = 'ami-toast-container';
        toastContainer.setAttribute('aria-live', 'polite');
        toastContainer.setAttribute('aria-atomic', 'true');
        document.body.appendChild(toastContainer);
    }

    function dismissToast(toast) {
        if (!toast || toast.classList.contains('ami-toast-hiding')) return;
        toast.classList.add('ami-toast-hiding');
        setTimeout(function () {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 260);
    }

    function initToastItem(toast) {
        if (!toast) return;
        var closeBtn = toast.querySelector('[data-toast-close]');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                dismissToast(toast);
            });
        }

        var duration = parseInt(toast.getAttribute('data-auto-dismiss'), 10) || 4500;
        var timer = null;
        var remaining = duration;
        var startTime = Date.now();

        function startTimer() {
            startTime = Date.now();
            timer = setTimeout(function () {
                dismissToast(toast);
            }, remaining);
        }

        function pauseTimer() {
            if (timer) {
                clearTimeout(timer);
                timer = null;
                remaining -= Date.now() - startTime;
                if (remaining < 800) remaining = 800;
            }
        }

        toast.addEventListener('mouseenter', pauseTimer);
        toast.addEventListener('mouseleave', startTimer);
        startTimer();
    }

    document.querySelectorAll('.ami-toast').forEach(function (toast) {
        initToastItem(toast);
    });

    var TOAST_ICONS = {
        success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>',
        error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>'
    };

    var TOAST_TITLES = {
        success: 'Berhasil',
        error: 'Terjadi Kesalahan',
        warning: 'Peringatan',
        info: 'Informasi'
    };

    window.AmiToast = {
        show: function (options) {
            options = options || {};
            var type = options.type || 'info';
            var title = options.title || TOAST_TITLES[type] || 'Notifikasi';
            var message = options.message || '';
            var duration = options.duration || (type === 'error' ? 6000 : (type === 'warning' ? 5000 : 4500));

            var toast = document.createElement('div');
            toast.className = 'ami-toast ami-toast-' + type;
            toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
            toast.setAttribute('data-type', type);
            toast.setAttribute('data-auto-dismiss', duration);

            var iconHtml = TOAST_ICONS[type] || TOAST_ICONS.info;
            var closeIcon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';

            var titleEl = document.createElement('div');
            titleEl.className = 'ami-toast-title';
            titleEl.textContent = title;

            var msgEl = document.createElement('div');
            msgEl.className = 'ami-toast-message';
            msgEl.textContent = message;

            var bodyEl = document.createElement('div');
            bodyEl.className = 'ami-toast-body';
            bodyEl.appendChild(titleEl);
            bodyEl.appendChild(msgEl);

            var iconEl = document.createElement('div');
            iconEl.className = 'ami-toast-icon';
            iconEl.innerHTML = iconHtml;

            var closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'ami-toast-close';
            closeBtn.setAttribute('aria-label', 'Tutup notifikasi');
            closeBtn.setAttribute('data-toast-close', '');
            closeBtn.innerHTML = closeIcon;

            toast.appendChild(iconEl);
            toast.appendChild(bodyEl);
            toast.appendChild(closeBtn);

            toastContainer.appendChild(toast);
            initToastItem(toast);
            return toast;
        },
        success: function (msg, title) { return this.show({ type: 'success', message: msg, title: title }); },
        error: function (msg, title) { return this.show({ type: 'error', message: msg, title: title }); },
        warning: function (msg, title) { return this.show({ type: 'warning', message: msg, title: title }); },
        info: function (msg, title) { return this.show({ type: 'info', message: msg, title: title }); }
    };

    // ==========================================
    // Global Accessible Confirmation Modal Engine
    // ==========================================
    var confirmModal = document.getElementById('ami-confirm-modal');
    var confirmTitle = document.getElementById('ami-confirm-title');
    var confirmMsg = document.getElementById('ami-confirm-message');
    var confirmBadge = document.getElementById('ami-confirm-badge');
    var confirmCancelBtn = document.getElementById('ami-confirm-cancel');
    var confirmSubmitBtn = document.getElementById('ami-confirm-submit');
    var confirmActiveCallback = null;
    var confirmCancelCallback = null;
    var confirmPrevActiveEl = null;

    function hideConfirmModal() {
        if (!confirmModal) return;
        confirmModal.setAttribute('hidden', '');
        document.body.classList.remove('ami-modal-lock');
        confirmActiveCallback = null;
        confirmCancelCallback = null;
        if (confirmPrevActiveEl && typeof confirmPrevActiveEl.focus === 'function') {
            confirmPrevActiveEl.focus();
        }
        confirmPrevActiveEl = null;
    }

    if (confirmModal) {
        if (confirmCancelBtn) {
            confirmCancelBtn.addEventListener('click', function () {
                if (confirmCancelCallback) confirmCancelCallback();
                hideConfirmModal();
            });
        }

        var backdrop = confirmModal.querySelector('[data-confirm-backdrop]');
        if (backdrop) {
            backdrop.addEventListener('click', function () {
                if (confirmCancelCallback) confirmCancelCallback();
                hideConfirmModal();
            });
        }

        if (confirmSubmitBtn) {
            confirmSubmitBtn.addEventListener('click', function () {
                var cb = confirmActiveCallback;
                hideConfirmModal();
                if (cb) cb();
            });
        }

        document.addEventListener('keydown', function (e) {
            if (confirmModal.hasAttribute('hidden')) return;
            if (e.key === 'Escape') {
                if (confirmCancelCallback) confirmCancelCallback();
                hideConfirmModal();
                e.preventDefault();
            } else if (e.key === 'Tab') {
                var focusables = [confirmCancelBtn, confirmSubmitBtn].filter(Boolean);
                if (focusables.length === 0) return;
                var first = focusables[0];
                var last = focusables[focusables.length - 1];
                if (e.shiftKey && document.activeElement === first) {
                    last.focus();
                    e.preventDefault();
                } else if (!e.shiftKey && document.activeElement === last) {
                    first.focus();
                    e.preventDefault();
                }
            }
        });
    }

    window.AmiConfirm = function (options) {
        options = options || {};
        var title = options.title || 'Konfirmasi Tindakan';
        var message = options.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
        var confirmText = options.confirmText || 'Ya, Lanjutkan';
        var cancelText = options.cancelText || 'Batal';
        var variant = options.variant || 'danger';

        confirmPrevActiveEl = document.activeElement;
        confirmActiveCallback = options.onConfirm || null;
        confirmCancelCallback = options.onCancel || null;

        if (confirmTitle) confirmTitle.textContent = title;
        if (confirmMsg) confirmMsg.textContent = message;
        if (confirmCancelBtn) confirmCancelBtn.textContent = cancelText;

        if (confirmSubmitBtn) {
            confirmSubmitBtn.textContent = confirmText;
            confirmSubmitBtn.className = 'ami-confirm-btn-submit ' + (
                variant === 'warning' ? 'ami-btn-warning' : (variant === 'info' ? 'ami-btn-primary' : 'ami-btn-danger')
            );
        }

        if (confirmBadge) {
            confirmBadge.className = 'ami-confirm-badge ' + (
                variant === 'warning' ? 'ami-badge-warning' : (variant === 'info' ? 'ami-badge-info' : 'ami-badge-danger')
            );
            var iconDanger = document.getElementById('ami-confirm-icon-danger');
            var iconWarning = document.getElementById('ami-confirm-icon-warning');
            var iconInfo = document.getElementById('ami-confirm-icon-info');
            if (iconDanger) iconDanger.style.display = variant === 'danger' ? '' : 'none';
            if (iconWarning) iconWarning.style.display = variant === 'warning' ? '' : 'none';
            if (iconInfo) iconInfo.style.display = variant === 'info' ? '' : 'none';
        }

        if (confirmModal) {
            confirmModal.removeAttribute('hidden');
            document.body.classList.add('ami-modal-lock');
            if (confirmCancelBtn) confirmCancelBtn.focus();
        }
    };

    // ==========================================
    // Intercept Native confirm() on Forms & Links
    // ==========================================
    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form || form.tagName !== 'FORM') return;
        if (form.dataset.amiConfirmed === 'true') {
            form.removeAttribute('data-amiConfirmed');
            return;
        }

        var onsubmitAttr = form.getAttribute('onsubmit') || '';
        var dataConfirm = form.getAttribute('data-confirm');
        var submitter = event.submitter;
        var submitterConfirm = submitter ? (submitter.getAttribute('data-confirm') || submitter.getAttribute('onclick')) : null;

        var confirmMsg = dataConfirm;
        if (!confirmMsg && onsubmitAttr.indexOf('confirm(') !== -1) {
            var match = onsubmitAttr.match(/confirm\(\s*(['"])(.*?)\1\s*\)/);
            if (match) confirmMsg = match[2];
        }
        if (!confirmMsg && submitterConfirm && submitterConfirm.indexOf('confirm(') !== -1) {
            var match2 = submitterConfirm.match(/confirm\(\s*(['"])(.*?)\1\s*\)/);
            if (match2) confirmMsg = match2[2];
        }

        if (confirmMsg) {
            event.preventDefault();
            event.stopImmediatePropagation();

            var isDanger = /hapus|delete|akhiri|keluar|reset|batal/i.test(confirmMsg);
            window.AmiConfirm({
                title: isDanger ? 'Konfirmasi Hapus' : 'Konfirmasi Tindakan',
                message: confirmMsg,
                confirmText: isDanger ? 'Ya, Hapus' : 'Ya, Lanjutkan',
                variant: isDanger ? 'danger' : 'warning',
                onConfirm: function () {
                    form.dataset.amiConfirmed = 'true';
                    if (submitter && submitter.name) {
                        var hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = submitter.name;
                        hidden.value = submitter.value;
                        form.appendChild(hidden);
                    }
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit(submitter);
                    } else {
                        form.submit();
                    }
                }
            });
        }
    }, true);

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('a[onclick*="confirm("], a[data-confirm], button[onclick*="confirm("]:not([type="submit"]), [data-confirm-action]');
        if (!trigger) return;
        if (trigger.dataset.amiConfirmed === 'true') {
            trigger.removeAttribute('data-amiConfirmed');
            return;
        }

        var onclickAttr = trigger.getAttribute('onclick') || '';
        var dataConfirm = trigger.getAttribute('data-confirm');
        var confirmMsg = dataConfirm;
        if (!confirmMsg && onclickAttr.indexOf('confirm(') !== -1) {
            var match = onclickAttr.match(/confirm\(\s*(['"])(.*?)\1\s*\)/);
            if (match) confirmMsg = match[2];
        }

        if (confirmMsg) {
            event.preventDefault();
            event.stopImmediatePropagation();

            var isDanger = /hapus|delete|akhiri|keluar|reset|batal/i.test(confirmMsg);
            window.AmiConfirm({
                title: isDanger ? 'Konfirmasi Hapus' : 'Konfirmasi Tindakan',
                message: confirmMsg,
                confirmText: isDanger ? 'Ya, Hapus' : 'Ya, Lanjutkan',
                variant: isDanger ? 'danger' : 'warning',
                onConfirm: function () {
                    trigger.dataset.amiConfirmed = 'true';
                    trigger.click();
                }
            });
        }
    }, true);

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
