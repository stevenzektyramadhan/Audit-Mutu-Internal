<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$question = isset($question) ? $question : NULL;
$is_edit = !empty($question);
$evidence_policy = $question && in_array($question->evidence_policy, ['none', 'file', 'url', 'either', 'both'], TRUE) ? $question->evidence_policy : 'none';

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<path d="m15 18-6-6 6-6"/>',
        'help-circle' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01"/>',
        'shield' => '<path d="M12 3 4 7v5c0 4.4 3.4 7.7 8 9 4.6-1.3 8-4.6 8-9V7l-8-4Z"/><path d="m9 12 2 2 4-4"/>',
        'file' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/>',
    ];
    return '<svg class="inst-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['help-circle']) . '</svg>';
};

$back_url = site_url('lpmpi/spmi-instruments/package/detail/' . (int) $package->id);
?>

<div id="instruments-root" class="tw-mx-auto tw-max-w-screen-2xl">
    <section class="inst-surface form-shell" style="max-width: 48rem;">
        <div class="inst-surface-body">
            <!-- Form Header -->
            <div class="form-header">
                <a href="<?php echo $back_url; ?>" class="inst-back-link">
                    <?php echo $icon('arrow-left'); ?> Kembali ke Detail Paket
                </a>
                <p class="inst-eyebrow">Butir Instrumen Audit</p>
                <h2 class="tw-text-2xl tw-font-bold tw-m-0 tw-text-slate-800"><?php echo html_escape($title); ?></h2>
                <p class="form-subtitle">Paket: <strong><?php echo html_escape($package->package_code . ' — ' . $package->title); ?></strong></p>
            </div>

            <?php if (validation_errors()): ?>
                <div class="tw-bg-rose-50 tw-border tw-border-rose-200 tw-text-rose-800 tw-p-3.5 tw-rounded-lg tw-text-xs tw-mb-5">
                    <?php echo validation_errors(); ?>
                </div>
            <?php endif; ?>

            <?php echo form_open($action); ?>
                <!-- Section 1: Informasi Pertanyaan -->
                <div class="form-section">
                    <div class="form-section-heading">
                        <?php echo $icon('help-circle'); ?>
                        <span>
                            <strong>Informasi Butir Pertanyaan</strong>
                            <small>Kode, urutan penomoran, dan teks pertanyaan audit.</small>
                        </span>
                    </div>

                    <div class="tw-space-y-4">
                        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                            <div>
                                <label for="question_code" class="inst-label">Kode Pertanyaan</label>
                                <input class="inst-control tw-font-mono" id="question_code" name="question_code" maxlength="64"
                                       placeholder="Contoh: Q-PBM-01"
                                       value="<?php echo html_escape(set_value('question_code', $question ? $question->question_code : '')); ?>" required>
                            </div>
                            <div>
                                <label for="display_order" class="inst-label">Urutan</label>
                                <input class="inst-control" id="display_order" name="display_order" type="number" min="1"
                                       value="<?php echo html_escape(set_value('display_order', $question ? $question->display_order : '1')); ?>" required>
                            </div>
                        </div>

                        <div>
                            <label for="question_text" class="inst-label">Teks Pertanyaan Audit</label>
                            <textarea class="inst-control tw-h-24" id="question_text" name="question_text"
                                      placeholder="Masukkan rumusan pertanyaan audit mutu..."
                                      required><?php echo html_escape(set_value('question_text', $question ? $question->question_text : '')); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Indikator Terkait -->
                <div class="form-section">
                    <div class="form-section-heading">
                        <?php echo $icon('shield'); ?>
                        <span>
                            <strong>Indikator Terkait</strong>
                            <small>Wajib memilih indikator yang bernaung di standar induk yang sama.</small>
                        </span>
                    </div>

                    <div>
                        <label for="indicator_id" class="inst-label">Indikator Mutu</label>
                        <select class="inst-control" id="indicator_id" name="indicator_id" required>
                            <option value="">Pilih indikator mutu...</option>
                            <?php foreach ($indicators as $indicator): ?>
                                <option value="<?php echo (int) $indicator->id; ?>" <?php echo set_select('indicator_id', $indicator->id, $question && (int) $question->indicator_id === (int) $indicator->id); ?>>
                                    <?php echo html_escape($indicator->indicator_code . ' — ' . $indicator->title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Section 3: Instruksi & Kebijakan Bukti -->
                <div class="form-section">
                    <div class="form-section-heading">
                        <?php echo $icon('file'); ?>
                        <span>
                            <strong>Instruksi &amp; Kebijakan Bukti</strong>
                            <small>Pedoman verifikasi bukti bagi auditee dan auditor.</small>
                        </span>
                    </div>

                    <div class="tw-space-y-4">
                        <div>
                            <label for="evidence_policy" class="inst-label">Kebijakan Jenis Bukti</label>
                            <select class="inst-control" id="evidence_policy" name="evidence_policy" required>
                                <option value="none" <?php echo set_select('evidence_policy', 'none', $evidence_policy === 'none'); ?>>None (Tidak mewajibkan bukti fisik)</option>
                                <option value="file" <?php echo set_select('evidence_policy', 'file', $evidence_policy === 'file'); ?>>File (Wajib unggah dokumen/PDF/Gambar)</option>
                                <option value="url" <?php echo set_select('evidence_policy', 'url', $evidence_policy === 'url'); ?>>URL (Wajib menyertakan tautan web)</option>
                                <option value="either" <?php echo set_select('evidence_policy', 'either', $evidence_policy === 'either'); ?>>Either (Boleh memilih salah satu: File atau URL)</option>
                                <option value="both" <?php echo set_select('evidence_policy', 'both', $evidence_policy === 'both'); ?>>Both (Wajib menyertakan keduanya: File dan URL)</option>
                            </select>
                        </div>

                        <div>
                            <label for="evidence_instruction" class="inst-label">Instruksi Bukti</label>
                            <textarea class="inst-control tw-h-24" id="evidence_instruction" name="evidence_instruction"
                                      placeholder="Penjelasan bukti yang sah dan kriteria penilaian..."
                                      required><?php echo html_escape(set_value('evidence_instruction', $question ? $question->evidence_instruction : '')); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-end tw-gap-2 tw-mt-6">
                    <a class="inst-button inst-button-secondary tw-justify-center" href="<?php echo $back_url; ?>">
                        Batal
                    </a>
                    <button class="inst-button inst-button-primary tw-justify-center" type="submit">
                        Simpan Pertanyaan
                    </button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </section>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
