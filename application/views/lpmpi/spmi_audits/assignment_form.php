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
<div class="tw-mt-5 tw-grid tw-gap-5 sm:tw-grid-cols-2"><label><span class="tw-label">Cari auditor</span><input type="search" class="tw-field" data-role-search="auditor_id" data-role-search-target="assignment-auditor" aria-label="Cari auditor berdasarkan nama" placeholder="Cari berdasarkan nama" autocomplete="off" hidden><span id="auditor-search-status" data-role-search-status="auditor_id" class="tw-mt-1 tw-block tw-text-xs tw-text-slate-500" aria-live="polite" hidden></span></label><label><span class="tw-label">Auditee</span><input type="search" class="tw-field" data-role-search="auditee_id" data-role-search-target="auditee_id" aria-label="Cari auditee berdasarkan nama" aria-controls="auditee_id" placeholder="Cari berdasarkan nama" autocomplete="off" hidden><span id="auditee-search-status" data-role-search-status="auditee_id" class="tw-mt-1 tw-block tw-text-xs tw-text-slate-500" aria-live="polite" hidden></span><select id="auditee_id" name="auditee_id" class="tw-field" required><option value="">Pilih auditee</option><?php foreach ($auditees as $user): ?><option value="<?php echo (int) $user->id; ?>" data-search="<?php echo html_escape(mb_strtolower($user->nama, 'UTF-8')); ?>"><?php echo html_escape($user->nama . ' — ' . $user->email); ?></option><?php endforeach; ?></select></label></div>
<fieldset class="tw-mt-5"><legend class="tw-label">Standar yang diaudit</legend><p class="tw-mb-3 tw-text-sm tw-text-slate-500">Semua standar versi terpilih dipilih secara otomatis. Hapus pilihan untuk audit bertahap, lalu pilih auditor untuk setiap standar.</p><div id="standards-scope" class="tw-space-y-3" aria-live="polite"><p class="tw-text-sm tw-text-slate-500">Pilih versi terlebih dahulu.</p></div></fieldset>
<button class="tw-button-primary tw-mt-8 tw-w-full" type="submit">Buat snapshot penugasan</button></form>
<script>
(function () {
    var standardsByVersion = <?php echo json_encode($standards_by_version, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var auditors = <?php echo json_encode(array_map(function ($user) { return ['id' => (int) $user->id, 'label' => $user->nama . ' — ' . $user->email, 'search' => mb_strtolower($user->nama, 'UTF-8')]; }, $auditors), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var versionSelect = document.getElementById('source_version_id');
    var scope = document.getElementById('standards-scope');
    var auditorSearch = document.querySelector('[data-role-search="auditor_id"]');
    function fillAuditorOptions(select) {
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Pilih auditor';
        select.appendChild(placeholder);
        auditors.forEach(function (auditor) {
            var option = document.createElement('option');
            option.value = auditor.id;
            option.textContent = auditor.label;
            option.setAttribute('data-search', auditor.search);
            select.appendChild(option);
        });
    }
    function renderStandards() {
        var standards = standardsByVersion[versionSelect.value] || [];
        scope.innerHTML = '';
        if (!standards.length) {
            scope.textContent = versionSelect.value ? 'Versi ini belum memiliki standar.' : 'Pilih versi terlebih dahulu.';
            return;
        }
        standards.forEach(function (standard, index) {
            var label = document.createElement('label');
            label.className = 'tw-grid tw-gap-3 tw-rounded-lg tw-border tw-border-slate-200 tw-bg-white tw-p-3 tw-text-sm tw-text-slate-700 sm:tw-grid-cols-[minmax(0,1fr)_minmax(14rem,18rem)]';
            var standardLabel = document.createElement('span');
            standardLabel.className = 'tw-flex tw-items-start tw-gap-3';
            var checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'assignment_groups[' + index + '][source_standard_ids][]';
            checkbox.value = standard.id;
            checkbox.checked = true;
            checkbox.className = 'tw-mt-1';
            var text = document.createElement('span');
            text.textContent = standard.standard_code + ' — ' + standard.title;
            var select = document.createElement('select');
            select.name = 'assignment_groups[' + index + '][auditor_id]';
            select.className = 'tw-field assignment-auditor-select';
            select.required = true;
            fillAuditorOptions(select);
            checkbox.addEventListener('change', function () {
                select.disabled = !checkbox.checked;
                select.required = checkbox.checked;
            });
            standardLabel.appendChild(checkbox);
            standardLabel.appendChild(text);
            label.appendChild(standardLabel);
            label.appendChild(select);
            scope.appendChild(label);
        });
        if (auditorSearch) {
            auditorSearch.dispatchEvent(new Event('input'));
        }
    }
    versionSelect.addEventListener('change', renderStandards);
    var roleSearches = document.querySelectorAll('[data-role-search]');
    function initRoleSearch(search) {
        var select = document.getElementById(search.getAttribute('data-role-search-target'));
        var status = document.querySelector('[data-role-search-status="' + search.getAttribute('data-role-search') + '"]');
        if (!select || !status) {
            var target = search.getAttribute('data-role-search-target');
            if (target !== 'assignment-auditor' || !status) {
                return;
            }
        }
        search.hidden = false;
        status.hidden = false;
        function updateRoleOptions() {
            var query = search.value.trim().toLocaleLowerCase();
            var matches = 0;
            var selects = search.getAttribute('data-role-search-target') === 'assignment-auditor' ? document.querySelectorAll('.assignment-auditor-select') : [document.getElementById(search.getAttribute('data-role-search-target'))];
            for (var selectIndex = 0; selectIndex < selects.length; selectIndex++) {
                var select = selects[selectIndex];
                if (!select) {
                    continue;
                }
                for (var optionIndex = 0; optionIndex < select.options.length; optionIndex++) {
                var option = select.options[optionIndex];
                var name = option.getAttribute('data-search');
                if (name === null) {
                    continue;
                }
                var matchesQuery = !query || name.indexOf(query) !== -1;
                option.hidden = !matchesQuery && !option.selected;
                if (matchesQuery) {
                    matches++;
                }
                }
            }
            status.textContent = query ? matches + ' pilihan ditemukan.' : matches + ' pilihan tersedia.';
        }
        search.addEventListener('input', updateRoleOptions);
        updateRoleOptions();
    }
    for (var i = 0; i < roleSearches.length; i++) {
        initRoleSearch(roleSearches[i]);
    }
}());
</script>
<?php endif; ?></div></main>
<?php include APPPATH . 'views/layouts/footer.php'; ?>
