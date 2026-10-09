<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$units = isset($units) && is_array($units) ? $units : [];
$faculties = isset($faculties) && is_array($faculties) ? $faculties : [];
$return_url = isset($return_url) ? $return_url : 'lpmpi/master-data-prodi-staf';
$unit_store_action = isset($unit_store_action) ? $unit_store_action : 'lpmpi/master-data-prodi-staf/unit/store';
$prodi_store_action = isset($prodi_store_action) ? $prodi_store_action : 'lpmpi/master-data-prodi-staf/prodi/store';
$entity_types = [
    'faculty' => 'Fakultas',
    'bureau' => 'Biro',
    'unit' => 'Unit',
    'institute' => 'Lembaga',
    'study_program' => 'Program Studi',
];
$parent_types = ['university' => 'Universitas', 'bureau' => 'Biro'];
$parent_hint = ['faculty' => 'Universitas', 'bureau' => 'Universitas', 'unit' => 'Biro', 'institute' => 'Universitas'];
$selected_entity_type = set_value('entity_type', '');
$selected_parent = set_value('parent_id', '');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<style>
    .master-create-root { max-width: 960px; margin: 0 auto; }
    .master-create-intro { color: var(--ami-muted); margin-bottom: var(--ami-space-lg); font-size: 13.5px; line-height: 1.5; }
    .master-create-branch { border: 1px solid var(--ami-border); border-radius: 12px; margin-top: var(--ami-space-md); padding: 20px; background: #fafbfc; }
    .master-create-branch[hidden] { display: none; }
    .master-create-branch legend { color: var(--ami-text); font-size: 15px; font-weight: 700; padding: 0 var(--ami-space-xs); width: auto; margin-bottom: 12px; }
    .master-create-hint { color: var(--ami-muted); display: block; font-size: 12px; line-height: 1.45; margin-top: 5px; }
    .master-create-root .form-control { height: 44px; border-radius: 8px; border: 1px solid var(--ami-border); font-size: 13.5px; }
    .master-create-root select.form-control { height: 44px; }
    .master-create-root .form-control:focus { border-color: var(--ami-link); box-shadow: 0 0 0 .2rem var(--ami-link-soft); outline: none; }
    .master-create-status { color: var(--ami-muted); margin: var(--ami-space-sm) 0 0; font-size: 12.5px; font-weight: 500; }
    .master-create-status:focus { outline: 2px solid var(--ami-link); outline-offset: 3px; }
    .master-create-empty-state { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 32px 20px; text-align: center; background: #f8fafc; margin-top: 16px; }
    .master-create-empty-icon { width: 44px; height: 44px; margin: 0 auto 10px; border-radius: 50%; background: #e2e8f0; color: #64748b; display: flex; align-items: center; justify-content: center; font-size: 18px; }
</style>

<main class="master-create-root">
    <div class="master-data-breadcrumb">Beranda / Management / Master Data Organisasi &amp; Staf / Tambah Data</div>
    <section class="ami-panel" aria-labelledby="master-create-title">
        <div class="ami-panel-body">
            <div class="master-directory-head">
                <div>
                    <div class="ami-eyebrow">Struktur organisasi</div>
                    <h2 id="master-create-title" class="ami-section-title mb-1">Tambah Data Organisasi</h2>
                    <p class="master-create-intro">Pilih tipe data terlebih dahulu. Form akan menampilkan field yang sesuai dan mengirim ke workflow yang sudah tersedia.</p>
                </div>
                <a class="btn btn-outline-ami btn-ami tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-min-h-[44px] tw-px-3.5 tw-py-2 tw-rounded-lg tw-bg-white tw-border tw-border-slate-300 tw-text-slate-700 hover:tw-bg-slate-50 hover:tw-text-slate-900 hover:tw-border-slate-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500/20 focus:tw-border-blue-500 tw-text-sm tw-font-semibold tw-transition-colors tw-w-fit tw-no-underline" href="<?php echo html_escape(site_url($return_url)); ?>">
                    <svg class="tw-w-[18px] tw-h-[18px] tw-shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m12 19-7-7 7-7"/>
                        <path d="M19 12H5"/>
                    </svg>
                    <span>Kembali</span>
                </a>
            </div>

            <?php if (validation_errors()): ?><div class="alert alert-danger" role="alert"><?php echo validation_errors(); ?></div><?php endif; ?>

            <?php echo form_open($unit_store_action, ['id' => 'master-create-form', 'novalidate' => 'novalidate']); ?>
                <div class="form-group mb-3">
                    <label for="entity-type" class="font-weight-bold">Tipe Data</label>
                    <select class="form-control" id="entity-type" name="entity_type" aria-describedby="entity-type-hint" required>
                        <option value="">Pilih tipe data...</option>
                        <?php foreach ($entity_types as $type => $label): ?><option value="<?php echo html_escape($type); ?>" <?php echo set_select('entity_type', $type, $selected_entity_type === $type); ?>><?php echo html_escape($label); ?></option><?php endforeach; ?>
                    </select>
                    <small id="entity-type-hint" class="master-create-hint">Pilihan ini menentukan field dan endpoint POST yang digunakan.</small>
                </div>
                <p id="master-create-status" class="master-create-status" aria-live="polite" tabindex="-1">Pilih tipe data untuk mengaktifkan form.</p>

                <div id="master-create-placeholder" class="master-create-empty-state">
                    <div class="master-create-empty-icon"><i class="fas fa-layer-group" aria-hidden="true"></i></div>
                    <div class="font-weight-bold text-slate-700 mb-1">Belum Ada Tipe yang Dipilih</div>
                    <div class="text-muted small">Pilih Fakultas, Biro, Unit, Lembaga, atau Program Studi pada dropdown di atas untuk membuka formulir input.</div>
                </div>

                <fieldset id="generic-branch" class="master-create-branch" hidden disabled>
                    <legend>Data organisasi</legend>
                    <input type="hidden" id="generic-type" name="type" value="<?php echo html_escape(set_value('type', '')); ?>" disabled>
                    <div class="form-row">
                        <div class="form-group col-md-4"><label for="unit-code" class="font-weight-bold">Kode Unit</label><input class="form-control" id="unit-code" name="code" maxlength="64" value="<?php echo html_escape(set_value('code')); ?>" required><small class="master-create-hint">Gunakan kode singkat yang unik untuk struktur organisasi.</small></div>
                        <div class="form-group col-md-8"><label for="unit-name" class="font-weight-bold">Nama Unit</label><input class="form-control" id="unit-name" name="name" maxlength="200" value="<?php echo html_escape(set_value('name')); ?>" required></div>
                    </div>
                    <div class="form-group mb-0"><label for="unit-parent" class="font-weight-bold">Parent Unit</label><select class="form-control" id="unit-parent" name="parent_id" required><option value="">Pilih parent aktif...</option><?php foreach ($units as $parent): ?><?php $parent_type = (string) (isset($parent->type) ? $parent->type : ''); $valid_parent = isset($parent_types[$parent_type]) && (int) $parent->is_active === 1; ?><?php if ($valid_parent): ?><option value="<?php echo (int) $parent->id; ?>" data-parent-type="<?php echo html_escape($parent_type); ?>" <?php echo set_select('parent_id', $parent->id, (string) $selected_parent === (string) $parent->id); ?>><?php echo html_escape($parent_types[$parent_type] . ' — ' . $parent->code . ' — ' . $parent->name); ?></option><?php endif; ?><?php endforeach; ?></select><small id="unit-parent-hint" class="master-create-hint">Pilih parent aktif sesuai tipe organisasi.</small></div>
                </fieldset>

                <fieldset id="prodi-branch" class="master-create-branch" hidden disabled>
                    <legend>Data Program Studi</legend>
                    <input type="hidden" name="require_faculty" value="1" disabled>
                    <div class="form-row">
                        <div class="form-group col-md-4"><label for="kode-prodi" class="font-weight-bold">Kode Prodi</label><input type="text" class="form-control" id="kode-prodi" name="kode_prodi" maxlength="20" value="<?php echo html_escape(set_value('kode_prodi')); ?>" required></div>
                        <div class="form-group col-md-8"><label for="nama-prodi" class="font-weight-bold">Nama Program Studi</label><input type="text" class="form-control" id="nama-prodi" name="nama_prodi" maxlength="200" value="<?php echo html_escape(set_value('nama_prodi')); ?>" required></div>
                    </div>
                    <div class="form-group"><label for="prodi-faculty" class="font-weight-bold">Fakultas</label><select class="form-control" id="prodi-faculty" name="faculty_id" required><option value="">Pilih Fakultas aktif...</option><?php foreach ($faculties as $faculty): ?><option value="<?php echo (int) $faculty->id; ?>" <?php echo set_select('faculty_id', $faculty->id); ?>><?php echo html_escape($faculty->code . ' — ' . $faculty->name); ?></option><?php endforeach; ?></select><small class="master-create-hint">Pilih Fakultas secara eksplisit sebagai parent Program Studi.</small></div>
                    <div class="form-row">
                        <div class="form-group col-md-4"><label for="prodi-status" class="font-weight-bold">Status</label><input type="text" class="form-control" id="prodi-status" name="status" maxlength="50" value="<?php echo html_escape(set_value('status')); ?>" placeholder="Contoh: Aktif"></div>
                        <div class="form-group col-md-4"><label for="prodi-jenjang" class="font-weight-bold">Jenjang</label><input type="text" class="form-control" id="prodi-jenjang" name="jenjang" maxlength="20" value="<?php echo html_escape(set_value('jenjang')); ?>" placeholder="Contoh: D3, S1, S2" required></div>
                        <div class="form-group col-md-4"><label for="prodi-akreditasi" class="font-weight-bold">Akreditasi</label><input type="text" class="form-control" id="prodi-akreditasi" name="akreditasi" maxlength="50" value="<?php echo html_escape(set_value('akreditasi')); ?>"></div>
                    </div>
                    <div class="form-row mb-0"><div class="form-group col-md-6"><label for="tanggal-sk-akreditasi" class="font-weight-bold">Tanggal SK Akreditasi</label><input type="date" class="form-control" id="tanggal-sk-akreditasi" name="tanggal_sk_akreditasi" value="<?php echo html_escape(set_value('tanggal_sk_akreditasi')); ?>"></div><div class="form-group col-md-6"><label for="rasio-dosen-mahasiswa" class="font-weight-bold">Rasio Dosen/Mahasiswa</label><input type="text" class="form-control" id="rasio-dosen-mahasiswa" name="rasio_dosen_mahasiswa" maxlength="20" value="<?php echo html_escape(set_value('rasio_dosen_mahasiswa')); ?>"></div></div>
                </fieldset>

                <div class="d-flex justify-content-end flex-wrap mt-4" style="gap: var(--ami-space-sm);"><a class="btn btn-outline-ami btn-ami" href="<?php echo html_escape(site_url($return_url)); ?>">Batal</a><button id="master-create-submit" type="submit" class="btn btn-primary btn-ami" disabled>Simpan Data</button></div>
            <?php echo form_close(); ?>
            <noscript><div class="alert alert-warning mt-3" role="alert">Aktifkan JavaScript untuk memilih tipe data dan mengaktifkan field yang sesuai.</div></noscript>
        </div>
    </section>
</main>

<script>
(function () {
    'use strict';
    var form = document.getElementById('master-create-form');
    var type = document.getElementById('entity-type');
    var generic = document.getElementById('generic-branch');
    var prodi = document.getElementById('prodi-branch');
    var genericType = document.getElementById('generic-type');
    var parent = document.getElementById('unit-parent');
    var parentHint = document.getElementById('unit-parent-hint');
    var submit = document.getElementById('master-create-submit');
    var status = document.getElementById('master-create-status');
    var placeholder = document.getElementById('master-create-placeholder');
    var unitAction = <?php echo json_encode(site_url($unit_store_action)); ?>;
    var prodiAction = <?php echo json_encode(site_url($prodi_store_action)); ?>;
    if (!form || !type || !generic || !prodi || !parent || !parentHint || !submit) return;
    function setBranch(branch, active) {
        branch.hidden = !active;
        branch.disabled = !active;
        Array.prototype.forEach.call(branch.querySelectorAll('input, select, textarea'), function (control) { control.disabled = !active; });
    }
    function sync(resetParent) {
        var selected = type.value;
        var isProdi = selected === 'study_program';
        var isGeneric = ['faculty', 'bureau', 'unit', 'institute'].indexOf(selected) !== -1;
        var requiredParentType = selected === 'unit' ? 'bureau' : 'university';
        setBranch(generic, isGeneric);
        setBranch(prodi, isProdi);
        if (placeholder) {
            placeholder.style.display = (isGeneric || isProdi) ? 'none' : 'block';
        }
        genericType.value = isGeneric ? selected : '';
        genericType.disabled = !isGeneric;
        if (resetParent) parent.value = '';
        Array.prototype.forEach.call(parent.querySelectorAll('option[data-parent-type]'), function (option) {
            var matches = isGeneric && option.getAttribute('data-parent-type') === requiredParentType;
            option.hidden = !matches;
            option.disabled = !matches;
        });
        parentHint.textContent = isGeneric
            ? 'Pilih parent aktif tipe ' + (requiredParentType === 'bureau' ? 'Biro' : 'Universitas') + '.'
            : 'Pilih parent aktif sesuai tipe organisasi.';
        form.action = isProdi ? prodiAction : unitAction;
        submit.disabled = !isGeneric && !isProdi;
        status.textContent = isProdi ? 'Field Program Studi aktif.' : (isGeneric ? 'Field organisasi aktif.' : 'Pilih tipe data untuk mengaktifkan form.');
    }
    type.addEventListener('change', function () { sync(true); });
    sync(false);
}());
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
