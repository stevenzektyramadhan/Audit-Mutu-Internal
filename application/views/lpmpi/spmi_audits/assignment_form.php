<?php defined('BASEPATH') OR exit('No direct script access allowed'); include APPPATH . 'views/layouts/header.php'; include APPPATH . 'views/layouts/sidebar.php'; ?>
<div class="ami-panel"><div class="ami-panel-body"><?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?><h2 class="ami-section-title mb-4">Tambah Penugasan SPMI</h2><p>Siklus draft: <strong><?php echo html_escape($cycle->cycle_code . ' — ' . $cycle->title); ?></strong></p><div class="alert alert-info">M7 membuat snapshot immutable dari versi M3, paket/pertanyaan/rubrik M6, serta identitas auditor dan auditee. M8/M9 workspace belum tersedia.</div><?php if (empty($packages) || empty($auditors) || empty($auditees)): ?><div class="ami-empty"><div class="ami-empty-title">Prasyarat belum lengkap</div><div>Pastikan tersedia paket M6 lengkap dengan rubrik 1–4, akun auditor, dan akun auditee.</div></div><?php else: ?>
<?php echo form_open('lpmpi/spmi-audits/assignment/store/' . (int) $cycle->id); ?>
<div class="form-group"><label for="source_package_picker">Paket Instrumen</label><div class="input-group"><input type="text" id="source_package_picker" class="form-control" placeholder="Ketik kode atau nama paket..." role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="source_package_popover" autocomplete="off"><div class="input-group-append"><button type="button" id="source_package_clear" class="btn btn-outline-secondary d-none" aria-label="Hapus paket" disabled>&times;</button></div></div><input type="hidden" id="source_package_id" name="source_package_id" required><div id="source_package_popover" class="list-group mt-2 d-none" role="listbox" aria-label="Hasil paket instrumen"><div id="source_package_count" class="list-group-item text-muted" role="status" aria-live="polite">Paket instrumen ditemukan: <?php echo count($packages); ?></div><?php foreach ($packages as $package): ?><button type="button" id="source_package_option_<?php echo (int) $package->id; ?>" class="list-group-item list-group-item-action" role="option" aria-selected="false" data-package-id="<?php echo (int) $package->id; ?>"><?php echo html_escape($package->version_code . " / " . $package->standard_code . " / " . $package->package_code . " — " . $package->title . " [" . $package->version_status . "]"); ?></button><?php endforeach; ?><div id="source_package_no_results" class="list-group-item text-muted d-none" role="status">Paket tidak ditemukan</div></div></div>
<div class="form-group"><label for="auditor_id">Auditor</label><select id="auditor_id" name="auditor_id" class="form-control" required><option value="">Pilih auditor</option><?php foreach ($auditors as $user): ?><option value="<?php echo (int) $user->id; ?>"><?php echo html_escape($user->nama . ' — ' . $user->email); ?></option><?php endforeach; ?></select></div><div class="form-group"><label for="auditee_id">Auditee</label><select id="auditee_id" name="auditee_id" class="form-control" required><option value="">Pilih auditee</option><?php foreach ($auditees as $user): ?><option value="<?php echo (int) $user->id; ?>"><?php echo html_escape($user->nama . ' — ' . $user->email); ?></option><?php endforeach; ?></select></div><button class="btn-ami" type="submit">Buat snapshot penugasan</button></form><?php endif; ?></div></div>
<style>
.source-package-field { position: relative; }
#source_package_popover { position: absolute; top: 100%; left: 0; right: 0; z-index: 1050; margin-top: .5rem; }
</style>
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

    input.closest('.form-group').classList.add('source-package-field');
    var options = Array.prototype.slice.call(list.querySelectorAll('[role="option"]'));
    var activeIndex = -1;

    function setClearState() {
        var selected = packageId.value !== '';
        clear.disabled = !selected;
        clear.classList.toggle('d-none', !selected);
    }

    function setOpen(open) {
        popover.classList.toggle('d-none', !open);
        input.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function setActive(index) {
        activeIndex = index;
        options.forEach(function (option, optionIndex) {
            var active = optionIndex === activeIndex && !option.classList.contains('d-none');
            option.setAttribute('aria-selected', active ? 'true' : 'false');
            option.classList.toggle('active', active);
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
            option.classList.toggle('d-none', !matches);
            return matches;
        });
        count.textContent = 'Paket instrumen ditemukan: ' + visible.length;
        document.getElementById('source_package_no_results').classList.toggle('d-none', visible.length !== 0);
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
        var visible = options.filter(function (option) { return !option.classList.contains('d-none'); });
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            if (!visible.length) return;
            var next = activeIndex < 0 ? (event.key === 'ArrowDown' ? 0 : visible.length - 1) : (visible.indexOf(options[activeIndex]) + (event.key === 'ArrowDown' ? 1 : -1) + visible.length) % visible.length;
            setActive(options.indexOf(visible[next]));
            return;
        }
        if ((event.key === 'Enter' || event.key === ' ') && activeIndex >= 0 && options[activeIndex] && !options[activeIndex].classList.contains('d-none')) {
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
