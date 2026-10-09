<?php defined('BASEPATH') OR exit('No direct script access allowed');

$flash_success = $this->session->flashdata('success');
$flash_error   = $this->session->flashdata('error');
$flash_warning = $this->session->flashdata('warning');
$flash_info    = $this->session->flashdata('info');

$has_flash = !empty($flash_success) || !empty($flash_error) || !empty($flash_warning) || !empty($flash_info);
?>
<!-- Global Toast Notifications Container -->
<div id="ami-toast-container" class="ami-toast-container" aria-live="polite" aria-atomic="true">
    <?php if (!empty($flash_success)): ?>
        <div class="ami-toast ami-toast-success ami-toast-flash" role="status" data-type="success" data-auto-dismiss="4500">
            <div class="ami-toast-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                </svg>
            </div>
            <div class="ami-toast-body">
                <div class="ami-toast-title">Berhasil</div>
                <div class="ami-toast-message"><?php echo html_escape($flash_success); ?></div>
            </div>
            <button type="button" class="ami-toast-close" aria-label="Tutup notifikasi" data-toast-close>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                </svg>
            </button>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash_error)): ?>
        <div class="ami-toast ami-toast-error ami-toast-flash" role="alert" data-type="error" data-auto-dismiss="6000">
            <div class="ami-toast-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div class="ami-toast-body">
                <div class="ami-toast-title">Terjadi Kesalahan</div>
                <div class="ami-toast-message"><?php echo html_escape($flash_error); ?></div>
            </div>
            <button type="button" class="ami-toast-close" aria-label="Tutup notifikasi" data-toast-close>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                </svg>
            </button>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash_warning)): ?>
        <div class="ami-toast ami-toast-warning ami-toast-flash" role="status" data-type="warning" data-auto-dismiss="5000">
            <div class="ami-toast-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div class="ami-toast-body">
                <div class="ami-toast-title">Peringatan</div>
                <div class="ami-toast-message"><?php echo html_escape($flash_warning); ?></div>
            </div>
            <button type="button" class="ami-toast-close" aria-label="Tutup notifikasi" data-toast-close>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                </svg>
            </button>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash_info)): ?>
        <div class="ami-toast ami-toast-info ami-toast-flash" role="status" data-type="info" data-auto-dismiss="4500">
            <div class="ami-toast-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
                </svg>
            </div>
            <div class="ami-toast-body">
                <div class="ami-toast-title">Informasi</div>
                <div class="ami-toast-message"><?php echo html_escape($flash_info); ?></div>
            </div>
            <button type="button" class="ami-toast-close" aria-label="Tutup notifikasi" data-toast-close>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                </svg>
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Global Accessible Confirmation Modal -->
<div id="ami-confirm-modal" class="ami-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="ami-confirm-title" aria-describedby="ami-confirm-message" hidden>
    <div class="ami-confirm-backdrop" data-confirm-backdrop></div>
    <div class="ami-confirm-card" role="document">
        <div id="ami-confirm-badge" class="ami-confirm-badge ami-badge-danger" aria-hidden="true">
            <svg id="ami-confirm-icon-danger" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <svg id="ami-confirm-icon-warning" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <svg id="ami-confirm-icon-info" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
            </svg>
        </div>
        <h3 id="ami-confirm-title" class="ami-confirm-title">Konfirmasi Tindakan</h3>
        <p id="ami-confirm-message" class="ami-confirm-desc">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
        <div class="ami-confirm-actions">
            <button type="button" id="ami-confirm-cancel" class="ami-confirm-btn-cancel">Batal</button>
            <button type="button" id="ami-confirm-submit" class="ami-confirm-btn-submit ami-btn-danger">Ya, Lanjutkan</button>
        </div>
    </div>
</div>
