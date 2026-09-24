<?php defined('BASEPATH') OR exit('No direct script access allowed'); include APPPATH . 'views/layouts/header.php'; include APPPATH . 'views/layouts/sidebar.php'; ?>
<main id="audits-root" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8"><div class="tw-mx-auto tw-max-w-2xl">
<?php echo validation_errors('<div class="tw-mb-5 tw-rounded-lg tw-border tw-border-red-200 tw-bg-red-50 tw-p-4 tw-text-sm tw-text-red-700">', '</div>'); ?>
<div class="tw-mb-6"><p class="tw-mb-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-text-slate-500">Penugasan SPMI</p><h1 class="tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950">Tambah penugasan</h1><p class="tw-mt-2 tw-text-sm tw-text-slate-600">Siklus draft: <strong><?php echo html_escape($cycle->cycle_code . ' — ' . $cycle->title); ?></strong></p></div>
<div class="tw-mb-5 tw-rounded-xl tw-border tw-border-blue-200 tw-bg-blue-50 tw-p-4 tw-text-sm tw-leading-6 tw-text-blue-800">Setiap standar yang dipilih membuat satu snapshot immutable versi, standar, indikator, kebijakan bukti, rubrik global 1–4, serta identitas auditor dan auditee.</div>
<?php if (empty($versions) || empty($standards_by_version) || empty($auditors) || empty($auditees)): ?>
<div class="tw-rounded-2xl tw-border tw-border-dashed tw-border-slate-300 tw-bg-white tw-p-8 tw-text-center"><h2 class="tw-font-bold tw-text-slate-900">Prasyarat belum lengkap</h2><p class="tw-mt-2 tw-text-sm tw-text-slate-500">Pastikan tersedia versi draft/review dengan standar, serta akun auditor dan auditee.</p></div>
<?php else: ?>
<?php echo form_open('lpmpi/spmi-audits/assignment/store/' . (int) $cycle->id); ?>
<label><span class="tw-label">Versi SPMI</span><select id="source_version_id" name="source_version_id" class="tw-field" required><option value="">Pilih versi</option><?php foreach ($versions as $version): ?><option value="<?php echo (int) $version->id; ?>"><?php echo html_escape($version->version_code . ' — ' . $version->title . ' [' . $version->status . ']'); ?></option><?php endforeach; ?></select></label>
<fieldset class="tw-mt-5"><legend class="tw-label">Standar yang diaudit</legend><p class="tw-mb-3 tw-text-sm tw-text-slate-500">Semua standar versi terpilih dipilih secara otomatis. Hapus pilihan untuk audit bertahap.</p><div id="standards-scope" class="tw-space-y-2" aria-live="polite"><p class="tw-text-sm tw-text-slate-500">Pilih versi terlebih dahulu.</p></div></fieldset>
<div class="tw-mt-5 tw-grid tw-gap-5 sm:tw-grid-cols-2"><label><span class="tw-label">Auditor</span><select id="auditor_id" name="auditor_id" class="tw-field" required><option value="">Pilih auditor</option><?php foreach ($auditors as $user): ?><option value="<?php echo (int) $user->id; ?>"><?php echo html_escape($user->nama . ' — ' . $user->email); ?></option><?php endforeach; ?></select></label><label><span class="tw-label">Auditee</span><select id="auditee_id" name="auditee_id" class="tw-field" required><option value="">Pilih auditee</option><?php foreach ($auditees as $user): ?><option value="<?php echo (int) $user->id; ?>"><?php echo html_escape($user->nama . ' — ' . $user->email); ?></option><?php endforeach; ?></select></label></div>
<button class="tw-button-primary tw-mt-8 tw-w-full" type="submit">Buat snapshot penugasan</button></form>
<script>
(function () {
    var standardsByVersion = <?php echo json_encode($standards_by_version, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var versionSelect = document.getElementById('source_version_id');
    var scope = document.getElementById('standards-scope');
    function renderStandards() {
        var standards = standardsByVersion[versionSelect.value] || [];
        scope.innerHTML = '';
        if (!standards.length) {
            scope.textContent = versionSelect.value ? 'Versi ini belum memiliki standar.' : 'Pilih versi terlebih dahulu.';
            return;
        }
        standards.forEach(function (standard) {
            var label = document.createElement('label');
            label.className = 'tw-flex tw-items-start tw-gap-3 tw-rounded-lg tw-border tw-border-slate-200 tw-bg-white tw-p-3 tw-text-sm tw-text-slate-700';
            var checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'source_standard_ids[]';
            checkbox.value = standard.id;
            checkbox.checked = true;
            checkbox.className = 'tw-mt-1';
            var text = document.createElement('span');
            text.textContent = standard.standard_code + ' — ' + standard.title;
            label.appendChild(checkbox);
            label.appendChild(text);
            scope.appendChild(label);
        });
    }
    versionSelect.addEventListener('change', renderStandards);
}());
</script>
<?php endif; ?></div></main>
<?php include APPPATH . 'views/layouts/footer.php'; ?>
