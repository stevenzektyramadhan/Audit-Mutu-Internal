<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = !empty($unit);
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<path d="m15 18-6-6 6-6"/>',
        'building' => '<rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M12 10h.01M12 14h.01M16 10h.01M16 14h.01M8 10h.01M8 14h.01"/>',
    ];
    return '<svg class="org-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['building']) . '</svg>';
};
?>

<div id="organization-root" class="tw-mx-auto tw-max-w-screen-2xl">
    <section class="org-surface form-shell">
        <div class="org-surface-body">
            <div class="form-header">
                <a href="<?php echo site_url('lpmpi/organization'); ?>" class="org-back-link">
                    <?php echo $icon('arrow-left'); ?> Kembali ke Struktur Organisasi
                </a>
                <p class="org-eyebrow">Kelembagaan</p>
                <h2 class="tw-text-2xl tw-font-bold tw-m-0 tw-text-slate-800"><?php echo html_escape($page_title); ?></h2>
                <p class="form-subtitle">Atur identitas unit kerja, kode referensi, dan induk hierarki dalam struktur organisasi.</p>
            </div>

            <?php if (validation_errors()): ?>
                <div class="org-error"><?php echo validation_errors(); ?></div>
            <?php endif; ?>

            <?php echo form_open($action); ?>
                <div class="form-section">
                    <div class="form-section-heading">
                        <?php echo $icon('building'); ?>
                        <span>
                            <strong>Informasi Unit Kerja</strong>
                            <small>Data kode, nama, dan jenis tingkatan unit.</small>
                        </span>
                    </div>

                    <div class="tw-space-y-4">
                        <div>
                            <label for="code" class="org-label">Kode Unit</label>
                            <input class="org-control tw-font-mono" id="code" name="code" maxlength="64"
                                   placeholder="Contoh: FTI, PRODI_IF, LPPM"
                                   value="<?php echo html_escape(set_value('code', $is_edit ? $unit->code : '')); ?>" required>
                            <span class="org-muted tw-text-[11px] tw-mt-1 tw-block">Format kode huruf kapital, angka, garis bawah, atau strip.</span>
                        </div>

                        <div>
                            <label for="name" class="org-label">Nama Unit</label>
                            <input class="org-control" id="name" name="name" maxlength="200"
                                   placeholder="Contoh: Fakultas Teknologi Informasi"
                                   value="<?php echo html_escape(set_value('name', $is_edit ? $unit->name : '')); ?>" required>
                        </div>

                        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                            <div>
                                <label for="type" class="org-label">Tipe Unit</label>
                                <select class="org-control" id="type" name="type" required>
                                    <option value="">Pilih tipe...</option>
                                    <?php foreach (['faculty' => 'Fakultas', 'upps' => 'UPPS', 'study_program' => 'Program Studi', 'institute' => 'Institut', 'bureau' => 'Biro', 'unit' => 'Unit'] as $value => $label): ?>
                                        <option value="<?php echo html_escape($value); ?>" <?php echo set_select('type', $value, $is_edit && $unit->type === $value); ?>>
                                            <?php echo html_escape($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label for="parent_id" class="org-label">Parent Unit</label>
                                <select class="org-control" id="parent_id" name="parent_id" required>
                                    <option value="">Pilih parent hierarki...</option>
                                    <?php foreach ($units as $parent): ?>
                                        <?php if (!$is_edit || (int) $parent->id !== (int) $unit->id): ?>
                                            <option value="<?php echo (int) $parent->id; ?>" <?php echo set_select('parent_id', $parent->id, $is_edit && (int) $unit->parent_id === (int) $parent->id); ?>>
                                                <?php echo html_escape($parent->code . ' - ' . $parent->name); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-end tw-gap-2 tw-mt-6">
                    <a class="org-button org-button-secondary tw-justify-center" href="<?php echo site_url('lpmpi/organization'); ?>">
                        Batal
                    </a>
                    <button class="org-button org-button-primary tw-justify-center" type="submit">
                        Simpan Unit Kerja
                    </button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </section>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
