<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$version = isset($version) ? $version : NULL;
$is_edit = !empty($version && isset($version->id));
$back_url = $is_edit
    ? site_url('lpmpi/spmi-standards/version/detail/' . (int) $version->id)
    : site_url('lpmpi/spmi-standards');

$page_heading = $is_edit ? 'Edit Versi Standar SPMI' : 'Tambah Versi Standar SPMI';
$fallback = static function ($property) use ($version) {
    return $version ? $version->$property : '';
};
?>

<div id="standards-root" class="tw-mx-auto tw-max-w-2xl tw-w-full">
    <div class="std-form-shell">
        <div class="std-form-header">
            <a class="std-back-link" href="<?php echo html_escape($back_url); ?>">
                <svg class="std-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Kembali
            </a>
            <p class="std-eyebrow">VERSI STANDAR</p>
            <h1><?php echo html_escape($page_heading); ?></h1>
            <p class="std-muted">Kelola identitas dan deskripsi versi standar SPMI.</p>
        </div>

        <?php if (validation_errors()): ?>
            <div class="std-error" role="alert" aria-live="polite">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <section class="std-form-section" aria-labelledby="version-form-section-title">
            <div class="std-form-section-heading">
                <h2 id="version-form-section-title">Informasi Versi</h2>
                <p class="std-muted">Tentukan kode versi unik, judul resmi, serta deskripsi ruang lingkup versi standar.</p>
            </div>

            <?php echo form_open($action); ?>
                <div class="std-form-grid std-form-grid-compact">
                    <div>
                        <label class="std-label" for="version_code">Kode versi</label>
                        <input id="version_code" name="version_code" class="std-control" value="<?php echo html_escape(set_value('version_code', $fallback('version_code'))); ?>" required placeholder="Misal: SPMI-2026">
                    </div>
                </div>

                <div class="std-form-field">
                    <label class="std-label" for="title">Judul</label>
                    <input id="title" name="title" class="std-control" value="<?php echo html_escape(set_value('title', $fallback('title'))); ?>" required maxlength="200" placeholder="Misal: Standar Penjaminan Mutu Internal 2026">
                </div>

                <div class="std-form-field">
                    <label class="std-label" for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="std-control std-control-textarea" placeholder="Tambahkan ringkasan konteks atau catatan pemberlakuan versi standar..."><?php echo html_escape(set_value('description', $fallback('description'))); ?></textarea>
                </div>

                <div class="std-form-actions">
                    <a class="std-button std-button-secondary" href="<?php echo html_escape($back_url); ?>">Batal</a>
                    <button class="std-button std-button-primary" type="submit">
                        <svg class="std-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Simpan
                    </button>
                </div>
            <?php echo form_close(); ?>
        </section>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
