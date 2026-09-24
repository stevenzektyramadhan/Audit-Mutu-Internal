<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$indicator = isset($indicator) ? $indicator : NULL;
$is_edit = !empty($indicator);
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
        'building' => '<rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M8 10h.01"/><path d="M16 10h.01"/><path d="M8 14h.01"/><path d="M16 14h.01"/>',
        'file' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
    ];
    return '<svg class="tw-h-4 tw-w-4 tw-flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['info']) . '</svg>';
};

$back_url = $is_edit
    ? site_url('lpmpi/spmi-indicators/indicator/detail/' . (int) $indicator->id)
    : (isset($standard->version_id) ? site_url('lpmpi/spmi-standards/version/detail/' . (int) $standard->version_id) : site_url('lpmpi/spmi-standards'));
?>

<main id="standards-root" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-2xl lg:tw-max-w-3xl">
        <!-- Top Back Navigation -->
        <div class="ami-row-actions no-print tw-mb-5">
            <a href="<?php echo html_escape($back_url); ?>" class="ami-action-btn tw-inline-flex tw-items-center tw-gap-2 tw-text-sm tw-font-semibold tw-text-blue-700 hover:tw-underline">
                <?php echo $icon('arrow-left'); ?>
                <span>Kembali ke Detail Versi</span>
            </a>
        </div>

        <!-- Form Card Container -->
        <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 sm:tw-p-8 tw-shadow-sm">
            <!-- Header Section -->
            <div class="tw-border-b tw-border-slate-100 tw-pb-6 tw-mb-6">
                <span class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest tw-text-blue-700">
                    Indikator Mutu
                </span>
                <h1 class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950 tw-mt-1.5 tw-mb-2">
                    <?php echo $is_edit ? 'Edit Indikator SPMI' : 'Tambah Indikator SPMI'; ?>
                </h1>
                <div class="tw-flex tw-items-center tw-gap-2 tw-text-xs tw-text-slate-600 tw-bg-slate-50 tw-p-2.5 tw-rounded-xl tw-border tw-border-slate-100">
                    <span class="tw-font-bold tw-text-slate-500">Standar:</span>
                    <span class="tw-font-mono tw-font-bold tw-text-slate-800"><?php echo html_escape($standard->standard_code); ?></span>
                    <span class="tw-text-slate-400">·</span>
                    <span class="tw-truncate"><?php echo html_escape($standard->title); ?></span>
                </div>
            </div>

            <!-- Global Error Banner -->
            <?php if (validation_errors()): ?>
                <div class="tw-mb-6 tw-rounded-xl tw-border tw-border-rose-200 tw-bg-rose-50 tw-p-4 tw-text-xs tw-text-rose-800">
                    <?php echo validation_errors(); ?>
                </div>
            <?php endif; ?>

            <?php echo form_open($action, ['class' => 'tw-space-y-6', 'id' => 'indicator-form']); ?>
                <!-- Section 1 — Informasi Indikator -->
                <section class="tw-space-y-4">
                    <div class="tw-flex tw-items-center tw-gap-2 tw-pb-2 tw-border-b tw-border-slate-100">
                        <span class="tw-text-blue-600"><?php echo $icon('info'); ?></span>
                        <h2 class="tw-text-sm tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-800 tw-m-0">
                            1. Informasi Indikator
                        </h2>
                    </div>

                    <div class="tw-grid tw-gap-4 sm:tw-grid-cols-2">
                        <!-- Kode Indikator -->
                        <div>
                            <label for="indicator_code" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                                Kode Indikator <span class="tw-text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="indicator_code"
                                   name="indicator_code"
                                   maxlength="64"
                                   placeholder="Contoh: IKU-01, IKT-PBM-02"
                                   value="<?php echo html_escape(set_value('indicator_code', $is_edit ? $indicator->indicator_code : '')); ?>"
                                   required
                                   class="tw-block tw-h-12 tw-w-full tw-min-w-0 tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-text-sm tw-font-mono tw-text-slate-900 focus:tw-border-blue-600 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-blue-100 tw-transition">
                            <?php if (form_error('indicator_code')): ?>
                                <div class="tw-mt-1.5 tw-text-xs tw-text-rose-600 tw-font-medium"><?php echo form_error('indicator_code'); ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Jenis Indikator -->
                        <div>
                            <label for="indicator_type" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                                Jenis Indikator <span class="tw-text-red-500">*</span>
                            </label>
                            <select id="indicator_type"
                                    name="indicator_type"
                                    required
                                    class="tw-block tw-h-12 tw-w-full tw-min-w-0 tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-text-sm tw-text-slate-900 focus:tw-border-blue-600 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-blue-100 tw-transition">
                                <option value="">Pilih jenis...</option>
                                <option value="IKU" <?php echo set_select('indicator_type', 'IKU', $is_edit && $indicator->indicator_type === 'IKU'); ?>>IKU (Indikator Kinerja Utama)</option>
                                <option value="IKT" <?php echo set_select('indicator_type', 'IKT', $is_edit && $indicator->indicator_type === 'IKT'); ?>>IKT (Indikator Kinerja Tambahan)</option>
                            </select>
                            <?php if (form_error('indicator_type')): ?>
                                <div class="tw-mt-1.5 tw-text-xs tw-text-rose-600 tw-font-medium"><?php echo form_error('indicator_type'); ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Judul Indikator Kinerja (Full Width) -->
                        <div class="sm:tw-col-span-2">
                            <label for="title" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                                Judul Indikator Kinerja <span class="tw-text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   maxlength="200"
                                   placeholder="Contoh: Persentase kelulusan tepat waktu mahasiswa program Sarjana"
                                   value="<?php echo html_escape(set_value('title', $is_edit ? $indicator->title : '')); ?>"
                                   required
                                   class="tw-block tw-h-12 tw-w-full tw-min-w-0 tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-text-sm tw-text-slate-900 focus:tw-border-blue-600 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-blue-100 tw-transition">
                            <?php if (form_error('title')): ?>
                                <div class="tw-mt-1.5 tw-text-xs tw-text-rose-600 tw-font-medium"><?php echo form_error('title'); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <!-- Section 2 — Lingkup & Penanggung Jawab -->
                <section class="tw-space-y-4">
                    <div class="tw-flex tw-items-center tw-gap-2 tw-pb-2 tw-border-b tw-border-slate-100">
                        <span class="tw-text-blue-600"><?php echo $icon('building'); ?></span>
                        <h2 class="tw-text-sm tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-800 tw-m-0">
                            2. Lingkup &amp; Penanggung Jawab
                        </h2>
                    </div>

                    <div class="tw-grid tw-gap-4 sm:tw-grid-cols-2">
                        <!-- Unit Lingkup -->
                        <div>
                            <label for="scope_organization_unit_id" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                                Unit Lingkup <span class="tw-text-red-500">*</span>
                            </label>
                            <select id="scope_organization_unit_id"
                                    name="scope_organization_unit_id"
                                    required
                                    class="tw-block tw-h-12 tw-w-full tw-min-w-0 tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-text-sm tw-text-slate-900 focus:tw-border-blue-600 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-blue-100 tw-transition">
                                <option value="">Pilih unit lingkup...</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?php echo (int) $unit->id; ?>" <?php echo set_select('scope_organization_unit_id', $unit->id, $is_edit && (int) $indicator->scope_organization_unit_id === (int) $unit->id); ?>>
                                        <?php echo html_escape($unit->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (form_error('scope_organization_unit_id')): ?>
                                <div class="tw-mt-1.5 tw-text-xs tw-text-rose-600 tw-font-medium"><?php echo form_error('scope_organization_unit_id'); ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Unit Penanggung Jawab -->
                        <div>
                            <label for="responsible_organization_unit_id" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                                Unit Penanggung Jawab <span class="tw-text-red-500">*</span>
                            </label>
                            <select id="responsible_organization_unit_id"
                                    name="responsible_organization_unit_id"
                                    required
                                    class="tw-block tw-h-12 tw-w-full tw-min-w-0 tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-text-sm tw-text-slate-900 focus:tw-border-blue-600 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-blue-100 tw-transition">
                                <option value="">Pilih unit penanggung jawab...</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?php echo (int) $unit->id; ?>" <?php echo set_select('responsible_organization_unit_id', $unit->id, $is_edit && (int) $indicator->responsible_organization_unit_id === (int) $unit->id); ?>>
                                        <?php echo html_escape($unit->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (form_error('responsible_organization_unit_id')): ?>
                                <div class="tw-mt-1.5 tw-text-xs tw-text-rose-600 tw-font-medium"><?php echo form_error('responsible_organization_unit_id'); ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Nama PIC / Penanggung Jawab (Full Width) -->
                        <div class="sm:tw-col-span-2">
                            <label for="responsible_pic_name" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                                Nama PIC / Penanggung Jawab (Opsional)
                            </label>
                            <input type="text"
                                   id="responsible_pic_name"
                                   name="responsible_pic_name"
                                   maxlength="200"
                                   placeholder="Contoh: Wakil Dekan I / Ketua Gugus Mutu"
                                   value="<?php echo html_escape(set_value('responsible_pic_name', $is_edit ? $indicator->responsible_pic_name : '')); ?>"
                                   class="tw-block tw-h-12 tw-w-full tw-min-w-0 tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-text-sm tw-text-slate-900 focus:tw-border-blue-600 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-blue-100 tw-transition">
                            <?php if (form_error('responsible_pic_name')): ?>
                                <div class="tw-mt-1.5 tw-text-xs tw-text-rose-600 tw-font-medium"><?php echo form_error('responsible_pic_name'); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <!-- Section 3 — Kebutuhan Bukti -->
                <section class="tw-space-y-4">
                    <div class="tw-flex tw-items-center tw-gap-2 tw-pb-2 tw-border-b tw-border-slate-100">
                        <span class="tw-text-blue-600"><?php echo $icon('file'); ?></span>
                        <h2 class="tw-text-sm tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-800 tw-m-0">
                            3. Kebutuhan Bukti
                        </h2>
                    </div>

                    <div>
                        <label for="evidence_requirement" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Deskripsi Bukti Fisik / Portofolio <span class="tw-text-red-500">*</span>
                        </label>
                        <textarea id="evidence_requirement"
                                  name="evidence_requirement"
                                  rows="4"
                                  required
                                  placeholder="Contoh: SK penetapan kurikulum, laporan tracer study, data PDDikti, sertifikat akreditasi..."
                                  class="tw-block tw-min-h-[120px] tw-w-full tw-min-w-0 tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-p-3.5 tw-text-sm tw-text-slate-900 focus:tw-border-blue-600 focus:tw-outline-none focus:tw-ring-4 focus:tw-ring-blue-100 tw-transition tw-resize-y"><?php echo html_escape(set_value('evidence_requirement', $is_edit ? $indicator->evidence_requirement : '')); ?></textarea>
                        <?php if (form_error('evidence_requirement')): ?>
                            <div class="tw-mt-1.5 tw-text-xs tw-text-rose-600 tw-font-medium"><?php echo form_error('evidence_requirement'); ?></div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Actions -->
                <div class="tw-flex tw-flex-col-reverse sm:tw-flex-row sm:tw-justify-end tw-gap-3 tw-pt-4 tw-border-t tw-border-slate-100">
                    <a href="<?php echo html_escape($back_url); ?>" class="std-button std-button-secondary tw-w-full sm:tw-w-auto tw-min-h-[44px] tw-justify-center">
                        Batal
                    </a>
                    <button type="submit" class="std-button std-button-primary tw-w-full sm:tw-w-auto tw-min-h-[44px] tw-justify-center">
                        <?php echo $icon('check'); ?>
                        <span>Simpan Indikator</span>
                    </button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
