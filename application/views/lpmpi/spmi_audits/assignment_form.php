<?php defined('BASEPATH') OR exit('No direct script access allowed'); include APPPATH . 'views/layouts/header.php'; include APPPATH . 'views/layouts/sidebar.php'; ?>
<main id="audits-root" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8"><div class="tw-mx-auto tw-max-w-2xl"><?php echo validation_errors('<div class="tw-mb-5 tw-rounded-lg tw-border tw-border-red-200 tw-bg-red-50 tw-p-4 tw-text-sm tw-text-red-700">', '</div>'); ?><div class="tw-mb-6"><p class="tw-mb-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-text-slate-500">Penugasan SPMI</p><h1 class="tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950">Tambah penugasan</h1><p class="tw-mt-2 tw-text-sm tw-text-slate-600">Siklus draft: <strong><?php echo html_escape($cycle->cycle_code . ' — ' . $cycle->title); ?></strong></p></div><div class="tw-mb-5 tw-rounded-xl tw-border tw-border-blue-200 tw-bg-blue-50 tw-p-4 tw-text-sm tw-leading-6 tw-text-blue-800">M7 membuat snapshot immutable dari versi M3, paket/pertanyaan/rubrik M6, serta identitas auditor dan auditee. Workspace audit memakai snapshot ini saat tersedia.</div><?php if (empty($packages) || empty($auditors) || empty($auditees)): ?><div class="tw-rounded-2xl tw-border tw-border-dashed tw-border-slate-300 tw-bg-white tw-p-8 tw-text-center"><h2 class="tw-font-bold tw-text-slate-900">Prasyarat belum lengkap</h2><p class="tw-mt-2 tw-text-sm tw-text-slate-500">Pastikan tersedia paket M6 lengkap dengan rubrik 1–4, akun auditor, dan akun auditee.</p></div><?php else: ?>
<?php echo form_open('lpmpi/spmi-audits/assignment/store/' . (int) $cycle->id); ?>
<div class="tw-relative"><label for="source_package_picker" class="tw-label">Paket Instrumen</label><div class="tw-flex"><input type="text" id="source_package_picker" class="tw-field tw-rounded-r-none" placeholder="Ketik kode atau nama paket..." role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="source_package_popover" autocomplete="off"><div class="tw-flex tw-items-center tw-rounded-r-lg tw-border tw-border-l-0 tw-border-slate-300 tw-bg-slate-50"><button type="button" id="source_package_clear" class="tw-hidden tw-px-3 tw-text-lg tw-text-slate-500" aria-label="Hapus paket" disabled>&times;</button></div></div><input type="hidden" id="source_package_id" name="source_package_id" required><div id="source_package_popover" class="tw-absolute tw-left-0 tw-right-0 tw-z-10 tw-mt-2 tw-hidden tw-overflow-hidden tw-rounded-lg tw-border tw-border-slate-200 tw-bg-white tw-shadow-lg" role="listbox" aria-label="Hasil paket instrumen"><div id="source_package_count" class="tw-border-b tw-border-slate-100 tw-p-3 tw-text-xs tw-text-slate-500" role="status" aria-live="polite">Paket instrumen ditemukan: <?php echo count($packages); ?></div><?php foreach ($packages as $package): ?><button type="button" id="source_package_option_<?php echo (int) $package->id; ?>" class="tw-block tw-w-full tw-border-0 tw-border-b tw-border-slate-100 tw-bg-white tw-p-3 tw-text-left tw-text-sm tw-text-slate-700" role="option" aria-selected="false" data-package-id="<?php echo (int) $package->id; ?>"><?php echo html_escape($package->version_code . " / " . $package->standard_code . " / " . $package->package_code . " — " . $package->title . " [" . $package->version_status . "]"); ?></button><?php endforeach; ?><div id="source_package_no_results" class="tw-hidden tw-p-3 tw-text-sm tw-text-slate-500" role="status">Paket tidak ditemukan</div></div></div>
<div class="tw-mt-5 tw-grid tw-gap-5 sm:tw-grid-cols-2"><label><span class="tw-label">Auditor</span><select id="auditor_id" name="auditor_id" class="tw-field" required><option value="">Pilih auditor</option><?php foreach ($auditors as $user): ?><option value="<?php echo (int) $user->id; ?>"><?php echo html_escape($user->nama . ' — ' . $user->email); ?></option><?php endforeach; ?></select></label><label><span class="tw-label">Auditee</span><select id="auditee_id" name="auditee_id" class="tw-field" required><option value="">Pilih auditee</option><?php foreach ($auditees as $user): ?><option value="<?php echo (int) $user->id; ?>"><?php echo html_escape($user->nama . ' — ' . $user->email); ?></option><?php endforeach; ?></select></label></div><button class="tw-button-primary tw-mt-8 tw-w-full" type="submit">Buat snapshot penugasan</button></form><?php endif; ?></div></div></main>
<script data-package-search-picker>
(function () {
    var input = document.getElementById('source_package_picker');
    var packageId = document.getElementById('source_package_id');
    var popover = document.getElementById('source_package_popover');
    var list = document.getElementById('source_package_popover');
    var count = document.getElementById('source_package_count');
    var clear = document.getElementById('source_package_clear');
    if (!input || !packageId || !popover || !list || !count || !clear) {
        return;
    }

    input.closest('.tw-relative').classList.add('source-package-field');
    var options = Array.prototype.slice.call(list.querySelectorAll('[role="option"]'));
    var activeIndex = -1;

    function setClearState() {
        var selected = packageId.value !== '';
        clear.disabled = !selected;
        clear.classList.toggle('tw-hidden', !selected);
    }

    function setOpen(open) {
        popover.classList.toggle('tw-hidden', !open);
        input.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function setActive(index) {
        activeIndex = index;
        options.forEach(function (option, optionIndex) {
            var active = optionIndex === activeIndex && !option.classList.contains('tw-hidden');
            option.setAttribute('aria-selected', active ? 'true' : 'false');
            option.classList.toggle('tw-bg-slate-100', active);
        });
        if (activeIndex >= 0 && options[activeIndex]) {
            input.setAttribute('aria-activedescendant', options[activeIndex].id);
            options[activeIndex].scrollIntoView({ block: 'nearest' });
        } else {
            input.removeAttribute('aria-activedescendant');
        }
    }

    function render() {
        var query = input.value.trim().toLocaleLowerCase();
        var visible = options.filter(function (option) {
            var matches = query === '' || option.textContent.toLocaleLowerCase().indexOf(query) !== -1;
            option.classList.toggle('tw-hidden', !matches);
            return matches;
        });
        count.textContent = 'Paket instrumen ditemukan: ' + visible.length;
        document.getElementById('source_package_no_results').classList.toggle('tw-hidden', visible.length !== 0);
        setActive(-1);
    }

    function choose(option) {
        packageId.value = option.getAttribute('data-package-id');
        input.value = option.textContent;
        setClearState();
        setOpen(false);
        setActive(-1);
    }

    input.addEventListener('focus', function () {
        render();
        setOpen(true);
    });
    input.addEventListener('input', function () {
        render();
        setOpen(true);
    });
    input.addEventListener('keydown', function (event) {
        var visible = options.filter(function (option) { return !option.classList.contains('tw-hidden'); });
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            if (!visible.length) return;
            var next = activeIndex < 0 ? (event.key === 'ArrowDown' ? 0 : visible.length - 1) : (visible.indexOf(options[activeIndex]) + (event.key === 'ArrowDown' ? 1 : -1) + visible.length) % visible.length;
            setActive(options.indexOf(visible[next]));
            return;
        }
        if ((event.key === 'Enter' || event.key === ' ') && activeIndex >= 0 && options[activeIndex] && !options[activeIndex].classList.contains('tw-hidden')) {
            event.preventDefault();
            choose(options[activeIndex]);
        } else if (event.key === 'Escape') {
            setOpen(false);
        }
    });
    options.forEach(function (option) {
        option.addEventListener('click', function () { choose(option); });
        option.addEventListener('mouseenter', function () { setActive(options.indexOf(option)); });
    });
    clear.addEventListener('click', function () {
        input.value = '';
        packageId.value = '';
        setClearState();
        render();
        input.focus();
        setOpen(true);
    });
    document.addEventListener('pointerdown', function (event) {
        if (!popover.parentNode.contains(event.target)) {
            setOpen(false);
        }
    });
    setClearState();
}());
</script><?php include APPPATH . 'views/layouts/footer.php'; ?>
