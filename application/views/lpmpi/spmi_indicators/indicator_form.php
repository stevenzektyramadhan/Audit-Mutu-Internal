<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$indicator = isset($indicator) ? $indicator : NULL;
$is_edit = !empty($indicator);
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

?>

<div id="standards-root" class="tw-mx-auto tw-max-w-3xl">
    <div class="std-form-shell">
        <div class="std-form-header">
            <a class="std-back-link" href="<?php echo site_url($is_edit ? 'lpmpi/spmi-indicators/indicator/detail/' . (int) $indicator->id : 'lpmpi/spmi-standards'); ?>">Kembali</a>
            <p class="std-eyebrow">Indikator Mutu</p>
            <h1><?php echo html_escape($page_title); ?></h1>
            <p class="std-muted">Standar: <strong><?php echo html_escape($standard->standard_code . ' — ' . $standard->title); ?></strong></p>
        </div>

        <?php if (validation_errors()): ?>
            <div class="std-error" role="alert" aria-live="polite">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <?php echo form_open($action); ?>
            <section class="std-form-section" aria-labelledby="indicator-information-heading">
                <div class="std-form-section-heading">
                    <h2 id="indicator-information-heading">Informasi Indikator</h2>
                    <p class="std-muted">Kode, jenis, dan judul indikator kinerja mutu.</p>
                </div>

                <div class="std-form-grid std-form-grid-compact">
                    <div class="std-form-field">
                        <label for="indicator_code" class="std-label">Kode Indikator</label>
                        <input class="std-control" id="indicator_code" name="indicator_code" maxlength="64"
                               placeholder="Contoh: IKU-01, IKT-PBM-02"
                               value="<?php echo html_escape(set_value('indicator_code', $is_edit ? $indicator->indicator_code : '')); ?>" required>
                    </div>
                    <div class="std-form-field">
                        <label for="indicator_type" class="std-label">Jenis Indikator</label>
                        <select class="std-control" id="indicator_type" name="indicator_type" required>
                            <option value="">Pilih jenis...</option>
                            <option value="IKU" <?php echo set_select('indicator_type', 'IKU', $is_edit && $indicator->indicator_type === 'IKU'); ?>>IKU (Indikator Kinerja Utama)</option>
                            <option value="IKT" <?php echo set_select('indicator_type', 'IKT', $is_edit && $indicator->indicator_type === 'IKT'); ?>>IKT (Indikator Kinerja Tambahan)</option>
                        </select>
                    </div>
                </div>

                <div class="std-form-field">
                    <label for="title" class="std-label">Judul Indikator Kinerja</label>
                    <input class="std-control" id="title" name="title" maxlength="200"
                           placeholder="Contoh: Persentase kelulusan tepat waktu mahasiswa S1"
                           value="<?php echo html_escape(set_value('title', $is_edit ? $indicator->title : '')); ?>" required>
                </div>
            </section>

            <section class="std-form-section" aria-labelledby="scope-responsibility-heading">
                <div class="std-form-section-heading">
                    <h2 id="scope-responsibility-heading">Lingkup &amp; Penanggung Jawab</h2>
                    <p class="std-muted">Penetapan unit kerja yang dievaluasi dan PIC pelaksana.</p>
                </div>

                <div class="std-form-grid std-form-grid-compact">
                    <div class="std-form-field">
                        <label for="scope_organization_unit_id" class="std-label">Unit Lingkup</label>
                        <select class="std-control" id="scope_organization_unit_id" name="scope_organization_unit_id" required>
                            <option value="">Pilih unit lingkup...</option>
                            <?php foreach ($units as $unit): ?>
                                <option value="<?php echo (int) $unit->id; ?>" <?php echo set_select('scope_organization_unit_id', $unit->id, $is_edit && (int) $indicator->scope_organization_unit_id === (int) $unit->id); ?>>
                                    <?php echo html_escape($unit->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="std-form-field">
                        <label for="responsible_organization_unit_id" class="std-label">Unit Penanggung Jawab</label>
                        <select class="std-control" id="responsible_organization_unit_id" name="responsible_organization_unit_id" required>
                            <option value="">Pilih unit penanggung jawab...</option>
                            <?php foreach ($units as $unit): ?>
                                <option value="<?php echo (int) $unit->id; ?>" <?php echo set_select('responsible_organization_unit_id', $unit->id, $is_edit && (int) $indicator->responsible_organization_unit_id === (int) $unit->id); ?>>
                                    <?php echo html_escape($unit->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="std-form-field">
                    <label for="responsible_pic_name" class="std-label">Nama PIC / Penanggung Jawab (Opsional)</label>
                    <input class="std-control" id="responsible_pic_name" name="responsible_pic_name" maxlength="200"
                           placeholder="Contoh: Wakil Dekan I / Ketua Gugus Mutu"
                           value="<?php echo html_escape(set_value('responsible_pic_name', $is_edit ? $indicator->responsible_pic_name : '')); ?>">
                </div>
            </section>

            <section class="std-form-section" aria-labelledby="evidence-requirements-heading">
                <div class="std-form-section-heading">
                    <h2 id="evidence-requirements-heading">Kebutuhan Bukti</h2>
                    <p class="std-muted">Dokumen atau tautan bukti yang wajib diunggah auditee.</p>
                </div>

                <div class="std-form-field">
                    <label for="evidence_requirement" class="std-label">Deskripsi Bukti Fisik / Portofolio</label>
                    <textarea class="std-control std-control-textarea" id="evidence_requirement" name="evidence_requirement"
                               placeholder="Contoh: SK penetapan kurikulum, laporan tracer study, data PDDikti..."
                               required><?php echo html_escape(set_value('evidence_requirement', $is_edit ? $indicator->evidence_requirement : '')); ?></textarea>
                </div>
                <div class="std-form-field">
                    <label for="evidence_policy" class="std-label">Kebijakan Bukti</label>
                    <select class="std-control" id="evidence_policy" name="evidence_policy" required>
                        <option value="none" <?php echo set_select('evidence_policy', 'none', !$is_edit || $indicator->evidence_policy === 'none'); ?>>Tidak wajib</option>
                        <option value="file" <?php echo set_select('evidence_policy', 'file', $is_edit && $indicator->evidence_policy === 'file'); ?>>File wajib</option>
                        <option value="url" <?php echo set_select('evidence_policy', 'url', $is_edit && $indicator->evidence_policy === 'url'); ?>>URL wajib</option>
                        <option value="either" <?php echo set_select('evidence_policy', 'either', $is_edit && $indicator->evidence_policy === 'either'); ?>>File atau URL wajib</option>
                        <option value="both" <?php echo set_select('evidence_policy', 'both', $is_edit && $indicator->evidence_policy === 'both'); ?>>File dan URL wajib</option>
                    </select>
                </div>
            </section>

            <div class="std-form-actions">
                <a class="std-button std-button-secondary" href="<?php echo site_url($is_edit ? 'lpmpi/spmi-indicators/indicator/detail/' . (int) $indicator->id : 'lpmpi/spmi-standards'); ?>">Batal</a>
                <button class="std-button std-button-primary" type="submit">Simpan Indikator</button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
