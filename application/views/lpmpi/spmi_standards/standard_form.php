<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$standard = isset($standard) ? $standard : NULL;
$version_id = isset($version_id) ? (int) $version_id : 0;
$parent_url = site_url('lpmpi/spmi-standards/version/detail/' . $version_id);
$page_title = isset($title) && strpos($title, 'Edit') === 0 ? 'Edit Standar' : 'Tambah Standar';
$fallback = static function ($property) use ($standard) {
    return $standard ? $standard->$property : '';
};
?>

<div id="standards-root" class="tw-mx-auto tw-max-w-3xl">
    <div class="std-form-shell">
        <div class="std-form-header">
            <a class="std-back-link" href="<?php echo $parent_url; ?>">
                <svg class="std-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Kembali ke Detail Versi
            </a>
            <p class="std-eyebrow">Manajemen Standar Mutu</p>
            <h1><?php echo html_escape($page_title); ?></h1>
            <p class="std-muted">Lengkapi informasi standar agar katalog versi SPMI tersusun jelas dan mudah ditinjau.</p>
        </div>

        <?php if (validation_errors()): ?>
            <div class="std-error" role="alert" aria-live="polite">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <section class="std-form-section" aria-labelledby="standard-form-section-title">
            <div class="std-form-section-heading">
                <h2 id="standard-form-section-title">Informasi Standar</h2>
                <p class="std-muted">Gunakan kode dan urutan untuk mengatur posisi standar dalam versi ini.</p>
            </div>

            <?php echo form_open($action); ?>
                <div class="std-form-grid std-form-grid-compact">
                    <div>
                        <label class="std-label" for="standard_code">Kode standar</label>
                        <input id="standard_code" name="standard_code" class="std-control" value="<?php echo html_escape(set_value('standard_code', $fallback('standard_code'))); ?>" required>
                    </div>
                    <div>
                        <label class="std-label" for="display_order">Urutan</label>
                        <input id="display_order" name="display_order" type="number" min="1" class="std-control" value="<?php echo html_escape(set_value('display_order', $fallback('display_order'))); ?>" required>
                    </div>
                </div>

                <div class="std-form-field">
                    <label class="std-label" for="title">Judul</label>
                    <input id="title" name="title" class="std-control" value="<?php echo html_escape(set_value('title', $fallback('title'))); ?>" required maxlength="200">
                </div>

                <div class="std-form-field">
                    <label class="std-label" for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="std-control std-control-textarea"><?php echo html_escape(set_value('description', $fallback('description'))); ?></textarea>
                </div>

                <div class="std-form-actions">
                    <a class="std-button std-button-secondary" href="<?php echo $parent_url; ?>">Batal</a>
                    <button class="std-button std-button-primary" type="submit">Simpan Standar</button>
                </div>
            <?php echo form_close(); ?>
        </section>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
