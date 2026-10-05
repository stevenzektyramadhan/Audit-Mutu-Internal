<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$is_edit = !empty($unit);
$unit_types = ['faculty' => 'Fakultas', 'bureau' => 'Biro', 'unit' => 'Unit', 'institute' => 'Lembaga'];
$parent_types = ['university' => 'Universitas', 'bureau' => 'Biro'];
$selected_type = set_value('type', $is_edit ? $unit->type : '', FALSE);
$selected_parent = set_value('parent_id', $is_edit ? $unit->parent_id : '', FALSE);
$parent_hint = ['faculty' => 'Universitas', 'bureau' => 'Universitas', 'unit' => 'Biro', 'institute' => 'Universitas'];

$unit_code_val = set_value('code', $is_edit ? $unit->code : '', FALSE);
$unit_name_val = set_value('name', $is_edit ? $unit->name : '', FALSE);

$selected_parent_obj = NULL;
if (!empty($selected_parent) && !empty($units)) {
    foreach ($units as $u) {
        if ((int) $u->id === (int) $selected_parent) {
            $selected_parent_obj = $u;
            break;
        }
    }
}

$icon = static function ($name, $class = 'tw-w-4 tw-h-4') {
    $paths = [
        'arrow-left' => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
        'building' => '<rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M8 10h.01"/><path d="M16 10h.01"/><path d="M8 14h.01"/><path d="M16 14h.01"/>',
        'save' => '<path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"/><path d="M7 3v4a1 1 0 0 0 1 1h7"/>',
    ];
    if (!isset($paths[$name])) return '';
    return '<svg xmlns="http://www.w3.org/2000/svg" class="' . html_escape($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[$name] . '</svg>';
};

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<main id="master-data-prodi-staf" class="master-data-root">
    <div id="organization-root">
        <div class="tw-max-w-[58rem] tw-mx-auto tw-space-y-6">
            <div class="master-data-breadcrumb">Beranda / Management / Master Data Organisasi &amp; Staf / <?php echo html_escape($page_title); ?></div>

            <section class="unit-form-card tw-bg-white tw-border tw-border-slate-200 tw-rounded-2xl tw-shadow-sm tw-p-6 sm:tw-p-8" aria-labelledby="unit-form-title">
                <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-start sm:tw-justify-between tw-gap-4 tw-pb-6 tw-border-b tw-border-slate-100">
                    <div class="tw-min-w-0">
                        <div class="ami-eyebrow tw-inline-flex tw-items-center tw-gap-1.5 tw-text-xs tw-font-semibold tw-text-blue-600 tw-uppercase tw-tracking-wider tw-mb-1">
                            STRUKTUR ORGANISASI
                        </div>
                        <h2 id="unit-form-title" class="ami-section-title tw-text-xl sm:tw-text-2xl tw-font-bold tw-text-slate-900 tw-tracking-tight tw-m-0">
                            <?php echo html_escape($page_title); ?>
                        </h2>
                        <p class="tw-mt-1.5 tw-mb-0 tw-text-sm tw-text-slate-600 tw-leading-relaxed">
                            Perbarui identitas dan posisi unit dalam struktur organisasi.
                        </p>
                    </div>

                    <div class="tw-flex-shrink-0">
                        <a class="btn btn-outline-ami btn-ami tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-h-11 tw-px-4 tw-text-sm tw-font-medium tw-rounded-xl tw-border tw-border-slate-300 tw-text-slate-700 tw-bg-white hover:tw-bg-slate-50 tw-transition-colors" href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf')); ?>">
                            <?php echo $icon('arrow-left', 'tw-w-4 tw-h-4'); ?>
                            <span>Kembali</span>
                        </a>
                    </div>
                </div>

                <?php if (validation_errors()): ?>
                    <div class="tw-mt-6 tw-p-4 tw-rounded-xl tw-bg-red-50 tw-border tw-border-red-200 tw-text-red-700 tw-text-sm" role="alert">
                        <div class="tw-font-semibold tw-mb-1">Terdapat kesalahan pengisian formulir:</div>
                        <div class="tw-text-xs tw-leading-relaxed"><?php echo validation_errors(); ?></div>
                    </div>
                <?php endif; ?>

                <?php echo form_open($action); ?>
                <div class="tw-mt-6 tw-space-y-6" id="unit-form-inner">
                    <div class="tw-space-y-5">
                        <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-12 tw-gap-5">
                            <div class="sm:tw-col-span-5 tw-min-w-0">
                                <label for="unit-code" class="tw-block tw-text-sm tw-font-semibold tw-text-slate-800 tw-mb-1.5">
                                    Kode Unit <span class="tw-text-red-500">*</span>
                                </label>
                                <input class="form-control tw-w-full tw-h-[46px] tw-px-3.5 tw-rounded-xl tw-border tw-border-slate-300 tw-text-sm tw-font-mono tw-text-slate-800 focus:tw-border-blue-600 focus:tw-ring-2 focus:tw-ring-blue-100 tw-transition-all" id="unit-code" name="code" maxlength="64" value="<?php echo html_escape($unit_code_val); ?>" required>
                                <small class="tw-block tw-text-xs tw-text-slate-500 tw-mt-1.5">Kode unik uppercase, angka, strip, atau titik.</small>
                            </div>

                            <div class="sm:tw-col-span-7 tw-min-w-0">
                                <label for="unit-type" class="tw-block tw-text-sm tw-font-semibold tw-text-slate-800 tw-mb-1.5">
                                    Tipe Unit <span class="tw-text-red-500">*</span>
                                </label>
                                <select class="form-control tw-w-full tw-h-[46px] tw-px-3.5 tw-rounded-xl tw-border tw-border-slate-300 tw-text-sm tw-text-slate-800 focus:tw-border-blue-600 focus:tw-ring-2 focus:tw-ring-blue-100 tw-transition-all" id="unit-type" name="type" required>
                                    <option value="">Pilih tipe unit...</option>
                                    <?php foreach ($unit_types as $type => $label): ?>
                                        <option value="<?php echo html_escape($type); ?>" <?php echo set_select('type', $type, $selected_type === $type); ?>>
                                            <?php echo html_escape($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="tw-block tw-text-xs tw-text-slate-500 tw-mt-1.5">Pilihan canonical: faculty, bureau, unit, atau institute.</small>
                            </div>
                        </div>

                        <div class="tw-min-w-0">
                            <label for="unit-name" class="tw-block tw-text-sm tw-font-semibold tw-text-slate-800 tw-mb-1.5">
                                Nama Unit <span class="tw-text-red-500">*</span>
                            </label>
                            <input class="form-control tw-w-full tw-h-[46px] tw-px-3.5 tw-rounded-xl tw-border tw-border-slate-300 tw-text-sm tw-text-slate-800 focus:tw-border-blue-600 focus:tw-ring-2 focus:tw-ring-blue-100 tw-transition-all" id="unit-name" name="name" maxlength="200" value="<?php echo html_escape($unit_name_val); ?>" required>
                            <small class="tw-block tw-text-xs tw-text-slate-500 tw-mt-1.5">Nama resmi unit akademik atau non-akademik.</small>
                        </div>

                        <div class="tw-min-w-0">
                            <label for="unit-parent" class="tw-block tw-text-sm tw-font-semibold tw-text-slate-800 tw-mb-1.5">
                                Parent Unit <span class="tw-text-red-500">*</span>
                            </label>
                            <select class="form-control tw-w-full tw-h-[46px] tw-px-3.5 tw-rounded-xl tw-border tw-border-slate-300 tw-text-sm tw-text-slate-800 focus:tw-border-blue-600 focus:tw-ring-2 focus:tw-ring-blue-100 tw-transition-all" id="unit-parent" name="parent_id" required>
                                <option value="">Pilih parent aktif...</option>
                                <?php foreach ($units as $parent): ?>
                                    <?php
                                    $parent_type = (string) $parent->type;
                                    $valid_parent = isset($parent_types[$parent_type]) && (int) $parent->is_active === 1 && (!$is_edit || (int) $parent->id !== (int) $unit->id);
                                    ?>
                                    <?php if ($valid_parent): ?>
                                        <option value="<?php echo (int) $parent->id; ?>" <?php echo set_select('parent_id', $parent->id, (string) $selected_parent === (string) $parent->id); ?>>
                                            <?php echo html_escape($parent_types[$parent_type] . ' — ' . $parent->code . ' — ' . $parent->name); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>

                            <?php if ($selected_parent_obj): ?>
                                <div class="tw-mt-2 tw-inline-flex tw-items-center tw-gap-1.5 tw-text-xs tw-text-slate-600 tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded-lg tw-px-2.5 tw-py-1.5 tw-max-w-full">
                                    <span class="tw-font-medium tw-text-slate-500 tw-flex-shrink-0">Parent terpilih:</span>
                                    <span class="tw-font-semibold tw-text-slate-800 tw-truncate" title="<?php echo html_escape($selected_parent_obj->name); ?>">
                                        <?php echo html_escape((isset($parent_types[$selected_parent_obj->type]) ? $parent_types[$selected_parent_obj->type] : $selected_parent_obj->type) . ' — ' . $selected_parent_obj->code . ' — ' . $selected_parent_obj->name); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="tw-p-4 tw-rounded-xl tw-bg-blue-50/70 tw-border tw-border-blue-100 tw-text-blue-950 tw-text-xs tw-space-y-1.5">
                            <div class="tw-flex tw-items-start tw-gap-2.5">
                                <div class="tw-text-blue-600 tw-mt-0.5 tw-flex-shrink-0">
                                    <?php echo $icon('info', 'tw-w-4 tw-h-4'); ?>
                                </div>
                                <div class="tw-space-y-1 tw-min-w-0">
                                    <div class="tw-font-semibold tw-text-blue-900">
                                        Matriks Parent Canonical
                                    </div>
                                    <p class="tw-m-0 tw-text-blue-800/90 tw-leading-relaxed">
                                        Fakultas, Biro, dan Lembaga → <strong>Universitas</strong>. Unit kerja teknis/pelaksana → <strong>Biro</strong>.
                                        <?php if ($selected_type && isset($parent_hint[$selected_type])): ?>
                                            <span class="tw-block tw-mt-0.5 tw-text-blue-700">Tipe saat ini menyarankan parent bertipe <strong><?php echo html_escape($parent_hint[$selected_type]); ?></strong>.</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tw-pt-5 tw-border-t tw-border-slate-100 tw-flex tw-items-center tw-justify-end tw-gap-3 tw-flex-wrap sm:tw-flex-nowrap">
                        <a class="btn btn-outline-ami btn-ami tw-inline-flex tw-items-center tw-justify-center tw-h-11 tw-px-5 tw-text-sm tw-font-medium tw-rounded-xl tw-border tw-border-slate-300 tw-text-slate-700 tw-bg-white hover:tw-bg-slate-50 tw-transition-colors tw-w-full sm:tw-w-auto" href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf')); ?>">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary btn-ami tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-h-11 tw-px-6 tw-text-sm tw-font-semibold tw-rounded-xl tw-bg-blue-600 hover:tw-bg-blue-700 tw-text-white tw-shadow-sm tw-transition-colors tw-w-full sm:tw-w-auto">
                            <?php echo $icon('save', 'tw-w-4 tw-h-4'); ?>
                            <span>Simpan Unit</span>
                        </button>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </section>
        </div>
    </div>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
