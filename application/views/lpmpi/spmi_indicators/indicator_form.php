<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$indicator = isset($indicator) ? $indicator : NULL;
$is_edit = !empty($indicator);
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<path d="m15 18-6-6 6-6"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
        'building' => '<rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M12 10h.01M12 14h.01M16 10h.01M16 14h.01M8 10h.01M8 14h.01"/>',
        'file' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/>',
    ];
    return '<svg class="std-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['info']) . '</svg>';
};
?>

<div id="standards-root" class="tw-mx-auto tw-max-w-screen-2xl">
    <section class="std-surface form-shell" style="max-width: 48rem;">
        <div class="std-surface-body">
            <!-- Form Header -->
            <div class="form-header">
                <a href="<?php echo site_url($is_edit ? 'lpmpi/spmi-indicators/indicator/detail/' . (int) $indicator->id : 'lpmpi/spmi-standards'); ?>" class="org-back-link">
                    <?php echo $icon('arrow-left'); ?> Kembali
                </a>
                <p class="std-eyebrow">Indikator Mutu</p>
                <h2 class="tw-text-2xl tw-font-bold tw-m-0 tw-text-slate-800"><?php echo html_escape($page_title); ?></h2>
                <p class="form-subtitle">Standar: <strong><?php echo html_escape($standard->standard_code . ' — ' . $standard->title); ?></strong></p>
            </div>

            <?php if (validation_errors()): ?>
                <div class="tw-bg-rose-50 tw-border tw-border-rose-200 tw-text-rose-800 tw-p-3.5 tw-rounded-lg tw-text-xs tw-mb-5">
                    <?php echo validation_errors(); ?>
                </div>
            <?php endif; ?>

            <?php echo form_open($action); ?>
                <!-- Section 1: Informasi Indikator -->
                <div class="form-section">
                    <div class="form-section-heading">
                        <?php echo $icon('info'); ?>
                        <span>
                            <strong>Informasi Indikator</strong>
                            <small>Kode, jenis, dan judul indikator kinerja mutu.</small>
                        </span>
                    </div>

                    <div class="tw-space-y-4">
                        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                            <div>
                                <label for="indicator_code" class="org-label">Kode Indikator</label>
                                <input class="org-control tw-font-mono" id="indicator_code" name="indicator_code" maxlength="64"
                                       placeholder="Contoh: IKU-01, IKT-PBM-02"
                                       value="<?php echo html_escape(set_value('indicator_code', $is_edit ? $indicator->indicator_code : '')); ?>" required>
                            </div>
                            <div>
                                <label for="indicator_type" class="org-label">Jenis Indikator</label>
                                <select class="org-control" id="indicator_type" name="indicator_type" required>
                                    <option value="">Pilih jenis...</option>
                                    <option value="IKU" <?php echo set_select('indicator_type', 'IKU', $is_edit && $indicator->indicator_type === 'IKU'); ?>>IKU (Indikator Kinerja Utama)</option>
                                    <option value="IKT" <?php echo set_select('indicator_type', 'IKT', $is_edit && $indicator->indicator_type === 'IKT'); ?>>IKT (Indikator Kinerja Tambahan)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="title" class="org-label">Judul Indikator Kinerja</label>
                            <input class="org-control" id="title" name="title" maxlength="200"
                                   placeholder="Contoh: Persentase kelulusan tepat waktu mahasiswa S1"
                                   value="<?php echo html_escape(set_value('title', $is_edit ? $indicator->title : '')); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Lingkup & Penanggung Jawab -->
                <div class="form-section">
                    <div class="form-section-heading">
                        <?php echo $icon('building'); ?>
                        <span>
                            <strong>Lingkup &amp; Penanggung Jawab</strong>
                            <small>Penetapan unit kerja yang dievaluasi dan PIC pelaksana.</small>
                        </span>
                    </div>

                    <div class="tw-space-y-4">
                        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                            <div>
                                <label for="scope_organization_unit_id" class="org-label">Unit Lingkup</label>
                                <select class="org-control" id="scope_organization_unit_id" name="scope_organization_unit_id" required>
                                    <option value="">Pilih unit lingkup...</option>
                                    <?php foreach ($units as $unit): ?>
                                        <option value="<?php echo (int) $unit->id; ?>" <?php echo set_select('scope_organization_unit_id', $unit->id, $is_edit && (int) $indicator->scope_organization_unit_id === (int) $unit->id); ?>>
                                            <?php echo html_escape($unit->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label for="responsible_organization_unit_id" class="org-label">Unit Penanggung Jawab</label>
                                <select class="org-control" id="responsible_organization_unit_id" name="responsible_organization_unit_id" required>
                                    <option value="">Pilih unit penanggung jawab...</option>
                                    <?php foreach ($units as $unit): ?>
                                        <option value="<?php echo (int) $unit->id; ?>" <?php echo set_select('responsible_organization_unit_id', $unit->id, $is_edit && (int) $indicator->responsible_organization_unit_id === (int) $unit->id); ?>>
                                            <?php echo html_escape($unit->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="responsible_pic_name" class="org-label">Nama PIC / Penanggung Jawab (Opsional)</label>
                            <input class="org-control" id="responsible_pic_name" name="responsible_pic_name" maxlength="200"
                                   placeholder="Contoh: Wakil Dekan I / Ketua Gugus Mutu"
                                   value="<?php echo html_escape(set_value('responsible_pic_name', $is_edit ? $indicator->responsible_pic_name : '')); ?>">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Kebutuhan Bukti -->
                <div class="form-section">
                    <div class="form-section-heading">
                        <?php echo $icon('file'); ?>
                        <span>
                            <strong>Kebutuhan Bukti</strong>
                            <small>Dokumen atau tautan bukti yang wajib diunggah auditee.</small>
                        </span>
                    </div>

                    <div>
                        <label for="evidence_requirement" class="org-label">Deskripsi Bukti Fisik / Portofolio</label>
                        <textarea class="org-control tw-h-24" id="evidence_requirement" name="evidence_requirement"
                                   placeholder="Contoh: SK penetapan kurikulum, laporan tracer study, data PDDikti..."
                                   required><?php echo html_escape(set_value('evidence_requirement', $is_edit ? $indicator->evidence_requirement : '')); ?></textarea>
                    </div>
                    <div class="tw-mt-4">
                        <label for="evidence_policy" class="org-label">Kebijakan Bukti</label>
                        <select class="org-control" id="evidence_policy" name="evidence_policy" required>
                            <option value="none" <?php echo set_select('evidence_policy', 'none', !$is_edit || $indicator->evidence_policy === 'none'); ?>>Tidak wajib</option>
                            <option value="file" <?php echo set_select('evidence_policy', 'file', $is_edit && $indicator->evidence_policy === 'file'); ?>>File wajib</option>
                            <option value="url" <?php echo set_select('evidence_policy', 'url', $is_edit && $indicator->evidence_policy === 'url'); ?>>URL wajib</option>
                            <option value="either" <?php echo set_select('evidence_policy', 'either', $is_edit && $indicator->evidence_policy === 'either'); ?>>File atau URL wajib</option>
                            <option value="both" <?php echo set_select('evidence_policy', 'both', $is_edit && $indicator->evidence_policy === 'both'); ?>>File dan URL wajib</option>
                        </select>
                    </div>
                </div>

                <!-- Actions -->
                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-end tw-gap-2 tw-mt-6">
                    <a class="std-button std-button-secondary tw-justify-center" href="<?php echo site_url($is_edit ? 'lpmpi/spmi-indicators/indicator/detail/' . (int) $indicator->id : 'lpmpi/spmi-standards'); ?>">
                        Batal
                    </a>
                    <button class="std-button std-button-primary tw-justify-center" type="submit">
                        Simpan Indikator
                    </button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </section>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
