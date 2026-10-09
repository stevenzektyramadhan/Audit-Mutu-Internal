<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$cycle = isset($cycle) ? $cycle : NULL;
$is_edit = !empty($cycle && isset($cycle->id));
$academic_year = isset($cycle->academic_year) ? $cycle->academic_year : '';

$back_url = site_url('lpmpi/spmi-audits');

$page_heading = $is_edit ? 'Edit Siklus SPMI' : 'Tambah Siklus SPMI';
$page_subtitle = $is_edit
    ? 'Perbarui informasi periode audit dan jadwal siklus SPMI.'
    : 'Buat periode audit baru untuk mengatur pelaksanaan dan penugasan SPMI.';

$fallback = static function ($property) use ($cycle) {
    return $cycle ? $cycle->$property : '';
};
?>

<main id="audits-root" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-2xl">
        <!-- Header & Explicit Back Action -->
        <div class="tw-mb-6">
            <a class="audits-back-link tw-mb-3" href="<?php echo html_escape($back_url); ?>">
                <svg class="tw-h-4 tw-w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                <span>Kembali</span>
            </a>
            <p class="tw-mb-1 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-text-slate-500">Siklus SPMI</p>
            <h1 class="tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950"><?php echo html_escape($page_heading); ?></h1>
            <p class="tw-mt-1.5 tw-text-sm tw-text-slate-500"><?php echo html_escape($page_subtitle); ?></p>
        </div>

        <?php if (validation_errors()): ?>
            <div class="tw-mb-6 tw-rounded-xl tw-border tw-border-red-200 tw-bg-red-50 tw-p-4 tw-text-sm tw-text-red-700" role="alert" aria-live="polite">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-5 tw-shadow-sm md:tw-p-8">
            <?php echo form_open($action); ?>
                <!-- Section 1: Informasi Siklus -->
                <div class="tw-mb-6">
                    <div class="tw-mb-4">
                        <h2 class="tw-text-base tw-font-bold tw-text-slate-900">Informasi Siklus</h2>
                        <p class="tw-text-xs tw-text-slate-500">Tentukan identitas siklus, judul resmi, serta periode tahun akademik.</p>
                    </div>

                    <div class="tw-grid tw-gap-4.5">
                        <label class="tw-block">
                            <span class="tw-label">Kode siklus</span>
                            <input id="cycle_code" name="cycle_code" class="tw-field" maxlength="64" value="<?php echo html_escape(set_value('cycle_code', $fallback('cycle_code'))); ?>" placeholder="Misal: SIKLUS-2026-GANJIL" required>
                        </label>

                        <label class="tw-block">
                            <span class="tw-label">Judul</span>
                            <input id="title" name="title" class="tw-field" maxlength="200" value="<?php echo html_escape(set_value('title', $fallback('title'))); ?>" placeholder="Misal: Audit Mutu Internal Semester Ganjil 2026/2027" required>
                        </label>

                        <label class="tw-block">
                            <span class="tw-label">Tahun akademik</span>
                            <input id="academic_year" name="academic_year" type="text" class="tw-field" maxlength="20" value="<?php echo html_escape(set_value('academic_year', $academic_year)); ?>" placeholder="2026/2027" required>
                            <small class="tw-help">Contoh: 2026/2027</small>
                        </label>

                        <label class="tw-block">
                            <span class="tw-label">Deskripsi</span>
                            <textarea id="description" name="description" class="tw-field tw-min-h-[120px] tw-resize-y" placeholder="Tambahkan catatan tujuan pelaksanaan atau ruang lingkup audit..."><?php echo html_escape(set_value('description', $fallback('description'))); ?></textarea>
                        </label>
                    </div>
                </div>

                <div class="tw-my-6 tw-border-t tw-border-slate-100"></div>

                <!-- Section 2: Periode Audit -->
                <div class="tw-mb-8">
                    <div class="tw-mb-4">
                        <h2 class="tw-text-base tw-font-bold tw-text-slate-900">Periode Audit</h2>
                        <p class="tw-text-xs tw-text-slate-500">Rentang waktu pelaksanaan audit dari mulai hingga selesai.</p>
                    </div>

                    <div class="tw-grid tw-gap-4.5 sm:tw-grid-cols-2">
                        <label class="tw-block">
                            <span class="tw-label">Tanggal mulai</span>
                            <input id="start_date" name="start_date" type="date" class="tw-field" value="<?php echo html_escape(set_value('start_date', $fallback('start_date'))); ?>" required>
                        </label>

                        <label class="tw-block">
                            <span class="tw-label">Tanggal selesai</span>
                            <input id="end_date" name="end_date" type="date" class="tw-field" value="<?php echo html_escape(set_value('end_date', $fallback('end_date'))); ?>" required>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="tw-mt-8 tw-flex tw-flex-col-reverse tw-gap-2.5 sm:tw-flex-row sm:tw-items-center sm:tw-justify-end">
                    <a class="tw-button-secondary tw-w-full sm:tw-w-auto" href="<?php echo html_escape($back_url); ?>">Batal</a>
                    <button class="tw-button-primary tw-w-full sm:tw-w-auto" type="submit">
                        <svg class="tw-mr-2 tw-h-4 tw-w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Simpan siklus
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
