<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

// Lucide icon helper
$icon = static function ($name) {
    $paths = [
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'layer' => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
        'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>',
        'file' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/>',
        'external-link' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>',
        'x' => '<path d="M18 6 6 18M6 6l12 12"/>',
    ];
    return '<svg class="std-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['file']) . '</svg>';
};
?>

<div id="standards-root" class="tw-mx-auto tw-max-w-screen-2xl">
    <!-- Hero Banner -->
    <section class="std-hero tw-mb-6 tw-flex tw-flex-col tw-items-start tw-justify-between tw-gap-4 tw-rounded-lg tw-border tw-p-5 sm:tw-flex-row sm:tw-items-center">
        <div>
            <p class="std-eyebrow">Manajemen Standar Mutu</p>
            <h2 class="tw-m-0">Standar SPMI Berbasis Versi</h2>
            <p class="std-muted tw-mb-0 tw-mt-1">Kelola katalog versi standar, instrumen terkait, unduh template, dan import data master.</p>
        </div>
        <div>
            <a class="std-button std-button-primary" href="<?php echo site_url('lpmpi/spmi-standards/version/create'); ?>">
                <?php echo $icon('plus'); ?> Tambah Versi Baru
            </a>
        </div>
    </section>

    <!-- Version Table Section -->
    <section class="std-surface">
        <div class="std-surface-body">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4 tw-flex-wrap tw-gap-2">
                <div>
                    <h3 class="tw-text-lg tw-font-bold tw-m-0">Daftar Versi Standar</h3>
                    <p class="std-muted tw-text-xs tw-mt-1">Katalog versi dokumen standar mutu internal dan riwayat status siklusnya.</p>
                </div>
            </div>

            <?php if (empty($versions)): ?>
                <div class="tw-text-center tw-py-12 tw-text-slate-500">
                    <div class="tw-mb-3"><?php echo $icon('layer'); ?></div>
                    <p class="tw-font-bold tw-text-base tw-text-slate-700">Belum ada versi standar SPMI</p>
                    <p class="tw-text-sm">Buat versi pertama untuk mulai mengelola katalog standar mutu perguruan tinggi.</p>
                </div>
            <?php else: ?>
                <div class="std-table-wrap">
                    <table class="std-table">
                        <thead>
                            <tr>
                                <th>Kode Versi</th>
                                <th>Judul Versi</th>
                                <th>Status</th>
                                <th class="tw-text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($versions as $version):
                                $status = strtolower($version->status);
                                $is_mutable = in_array($status, ['draft', 'review'], TRUE);
                                $badge_class = 'std-badge-' . $status;
                            ?>
                                <tr>
                                    <td data-label="Kode Versi">
                                        <span class="tw-font-mono tw-font-bold tw-text-xs tw-bg-slate-100 tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200">
                                            <?php echo html_escape($version->version_code); ?>
                                        </span>
                                    </td>
                                    <td data-label="Judul Versi">
                                        <strong class="tw-text-slate-800"><?php echo html_escape($version->title); ?></strong>
                                    </td>
                                    <td data-label="Status">
                                        <span class="std-badge <?php echo $badge_class; ?>">
                                            <?php echo html_escape(ucfirst($version->status)); ?>
                                        </span>
                                    </td>
                                    <td data-label="Aksi" class="tw-text-right">
                                        <div class="tw-inline-flex tw-items-center tw-gap-2">
                                            <a class="std-button std-button-secondary tw-text-xs" href="<?php echo site_url('lpmpi/spmi-standards/version/detail/' . (int) $version->id); ?>">
                                                <?php echo $icon('external-link'); ?> Detail
                                            </a>

                                            <!-- Dropdown Menu Aksi -->
                                            <div class="std-dropdown" data-std-dropdown>
                                                <button type="button" class="std-button std-button-secondary tw-text-xs" data-dropdown-trigger aria-haspopup="true" aria-expanded="false">
                                                    Aksi <?php echo $icon('chevron-down'); ?>
                                                </button>
                                                <div class="std-dropdown-menu tw-hidden" data-dropdown-menu>
                                                    <a class="std-dropdown-item" href="<?php echo site_url('lpmpi/spmi-master/export/' . (int) $version->id); ?>">
                                                        <?php echo $icon('download'); ?> Export Master XLSX
                                                    </a>
                                                    <?php if ($is_mutable): ?>
                                                        <div class="std-dropdown-divider"></div>
                                                        <a class="std-dropdown-item" href="<?php echo site_url('lpmpi/spmi-master/template/' . (int) $version->id); ?>">
                                                            <?php echo $icon('file'); ?> Download Template Master
                                                        </a>
                                                        <button type="button" class="std-dropdown-item" data-modal-trigger="modal-import-<?php echo (int) $version->id; ?>">
                                                            <?php echo $icon('upload'); ?> Import Master Excel
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modals Import Master (Hanya untuk versi mutable: draft/review) -->
    <?php foreach ($versions as $version):
        if (!in_array(strtolower($version->status), ['draft', 'review'], TRUE)) continue;
    ?>
        <div id="modal-import-<?php echo (int) $version->id; ?>" class="std-modal-overlay" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="modal-title-<?php echo (int) $version->id; ?>">
            <div class="std-modal-card" data-modal-card>
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <div>
                        <h4 id="modal-title-<?php echo (int) $version->id; ?>" class="tw-text-base tw-font-bold tw-m-0 tw-text-slate-800">
                            Import Master SPMI
                        </h4>
                        <p class="std-muted tw-text-xs tw-mt-0.5">
                            Versi: <strong><?php echo html_escape($version->version_code); ?></strong> — <?php echo html_escape($version->title); ?>
                        </p>
                    </div>
                    <button type="button" class="tw-text-slate-400 hover:tw-text-slate-600 tw-p-1 tw-rounded" data-modal-close aria-label="Tutup modal">
                        <?php echo $icon('x'); ?>
                    </button>
                </div>

                <?php echo form_open_multipart('lpmpi/spmi-master/preview/' . (int) $version->id, ['class' => 'import-form']); ?>
                    <div class="tw-mb-4">
                        <div class="std-file-dropzone" data-file-trigger>
                            <div class="tw-mb-2 tw-text-slate-400">
                                <?php echo $icon('file'); ?>
                            </div>
                            <span class="tw-block tw-text-xs tw-font-bold tw-text-slate-700 tw-mb-1 file-display-name">
                                Belum ada file dipilih
                            </span>
                            <span class="std-button std-button-secondary tw-text-xs file-button-text">
                                Pilih File (.xlsx)
                            </span>
                            <input type="file" name="master_file" accept=".xlsx" required class="tw-hidden file-native-input">
                        </div>
                        <div class="tw-mt-3 tw-bg-slate-50 tw-p-3 tw-rounded tw-border tw-border-slate-200">
                            <ul class="tw-text-[11px] std-muted tw-space-y-1 tw-pl-4 tw-list-disc tw-m-0">
                                <li>Hanya berkas format <strong>.xlsx</strong> (Maksimal 2 MiB).</li>
                                <li>Harus menggunakan template resmi Master SPMI.</li>
                                <li>Import bersifat <strong>additive / no-delete</strong>: data lama tidak terhapus.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="tw-flex tw-justify-end tw-gap-2">
                        <button type="button" class="std-button std-button-secondary tw-text-xs" data-modal-close>
                            Batal
                        </button>
                        <button type="submit" class="std-button std-button-primary tw-text-xs submit-btn" disabled>
                            Unggah &amp; Preview
                        </button>
                    </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Interactivity script (Vanilla JS) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Dropdown toggle
    document.querySelectorAll('[data-std-dropdown]').forEach(function (container) {
        var trigger = container.querySelector('[data-dropdown-trigger]');
        var menu = container.querySelector('[data-dropdown-menu]');

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = !menu.classList.contains('tw-hidden');
            document.querySelectorAll('[data-dropdown-menu]').forEach(function (m) {
                m.classList.add('tw-hidden');
            });
            if (!isOpen) {
                menu.classList.remove('tw-hidden');
                trigger.setAttribute('aria-expanded', 'true');
            } else {
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    });

    document.addEventListener('click', function () {
        document.querySelectorAll('[data-dropdown-menu]').forEach(function (menu) {
            menu.classList.add('tw-hidden');
        });
        document.querySelectorAll('[data-dropdown-trigger]').forEach(function (trig) {
            trig.setAttribute('aria-expanded', 'false');
        });
    });

    // 2. Modal helpers
    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';

        // Reset file selection inside the closed modal
        var form = modal.querySelector('.import-form');
        if (form) {
            form.reset();
            var displayName = form.querySelector('.file-display-name');
            var buttonText = form.querySelector('.file-button-text');
            var submitBtn = form.querySelector('.submit-btn');
            if (displayName) {
                displayName.textContent = 'Belum ada file dipilih';
                displayName.classList.remove('tw-text-blue-600');
            }
            if (buttonText) {
                buttonText.textContent = 'Pilih File (.xlsx)';
            }
            if (submitBtn) {
                submitBtn.disabled = true;
            }
        }
    }

    function openModal(modalId) {
        // Ensure all other modals are closed
        document.querySelectorAll('.std-modal-overlay.is-open').forEach(function (m) {
            closeModal(m);
        });

        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }
    }

    document.querySelectorAll('[data-modal-trigger]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var modalId = btn.getAttribute('data-modal-trigger');
            openModal(modalId);
        });
    });

    // Close buttons (X and Batal)
    document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var modal = btn.closest('.std-modal-overlay');
            closeModal(modal);
        });
    });

    // Backdrop click
    document.querySelectorAll('.std-modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                closeModal(overlay);
            }
        });
    });

    // Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.key === 'Esc') {
            document.querySelectorAll('.std-modal-overlay.is-open').forEach(function (modal) {
                closeModal(modal);
            });
        }
    });

    // 3. File picker inside modal
    document.querySelectorAll('.import-form').forEach(function (form) {
        var dropzone = form.querySelector('[data-file-trigger]');
        var fileInput = form.querySelector('.file-native-input');
        var displayName = form.querySelector('.file-display-name');
        var buttonText = form.querySelector('.file-button-text');
        var submitBtn = form.querySelector('.submit-btn');

        if (!dropzone || !fileInput) return;

        dropzone.addEventListener('click', function (e) {
            e.stopPropagation();
            fileInput.click();
        });

        fileInput.addEventListener('change', function () {
            if (fileInput.files && fileInput.files.length > 0) {
                var fileName = fileInput.files[0].name;
                displayName.textContent = fileName;
                displayName.classList.add('tw-text-blue-600');
                buttonText.textContent = 'Ganti File';
                submitBtn.disabled = false;
            } else {
                displayName.textContent = 'Belum ada file dipilih';
                displayName.classList.remove('tw-text-blue-600');
                buttonText.textContent = 'Pilih File (.xlsx)';
                submitBtn.disabled = true;
            }
        });
    });
});
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
