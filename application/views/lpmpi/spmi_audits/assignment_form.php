<?php defined('BASEPATH') OR exit('No direct script access allowed'); include APPPATH . 'views/layouts/header.php'; include APPPATH . 'views/layouts/sidebar.php'; ?>
<div id="audits-root" class="tw-min-w-0 tw-flex-1">
    <div class="assignment-page">
        <a class="assignment-back" href="<?php echo site_url('lpmpi/spmi-audits/cycle/detail/' . (int) $cycle->id); ?>#assignments"><span aria-hidden="true">&#8592;</span> Kembali ke siklus</a>
        <?php echo validation_errors('<div class="assignment-error tw-rounded-lg tw-border tw-border-red-200 tw-bg-red-50 tw-p-4 tw-text-sm tw-text-red-700" role="alert">', '</div>'); ?>
        <header class="assignment-hero">
            <p class="assignment-kicker">Penugasan SPMI</p>
            <h1>Tambah penugasan</h1>
            <p class="assignment-subtitle">Siklus draft: <strong><?php echo html_escape($cycle->cycle_code . ' — ' . $cycle->title); ?></strong></p>
        </header>
        <div class="assignment-note" role="note"><span class="assignment-note-icon" aria-hidden="true">&#9432;</span><span>Penugasan ini dibuat sebagai draft. Setiap standar yang dipilih akan menyalin snapshot versi, indikator, kebijakan bukti, rubrik global 1–4, serta identitas auditor dan auditi.</span></div>
        <?php if (empty($versions) || empty($standards_by_version) || empty($auditors) || empty($auditees)): ?>
            <section class="assignment-card" aria-labelledby="assignment-prerequisite-title"><div class="assignment-section-head"><span class="assignment-section-index">!</span><div><h2 id="assignment-prerequisite-title">Prasyarat belum lengkap</h2><p>Pastikan tersedia versi draft/review dengan standar, serta akun auditor dan auditi.</p></div></div></section>
        <?php else: ?>
            <?php echo form_open('lpmpi/spmi-audits/assignment/store/' . (int) $cycle->id); ?>
            <section class="assignment-card" aria-labelledby="assignment-info-title">
                <div class="assignment-section-head"><span class="assignment-section-index">01</span><div><h2 id="assignment-info-title">Informasi penugasan</h2><p>Tentukan versi standar dan konteks pengguna yang akan menjalankan audit.</p></div></div>
                <div class="assignment-fields">
                    <div class="assignment-field assignment-field--wide"><label class="assignment-label" for="source_version_id">Versi SPMI <span class="assignment-help">Wajib dipilih sebagai sumber snapshot standar.</span></label><select id="source_version_id" name="source_version_id" class="tw-field" required><option value="">Pilih versi</option><?php foreach ($versions as $version): ?><option value="<?php echo (int) $version->id; ?>"><?php echo html_escape($version->version_code . ' — ' . $version->title . ' [' . $version->status . ']'); ?></option><?php endforeach; ?></select></div>
                    <div class="assignment-field"><label class="assignment-label" for="auditee_id">Auditi <span class="assignment-help">Wajib dipilih.</span></label><input type="search" class="assignment-search" data-role-search="auditee_id" data-role-search-target="auditee_id" aria-label="Cari auditi berdasarkan nama" aria-controls="auditee_id" placeholder="Cari berdasarkan nama" autocomplete="off" hidden><span id="auditee-search-status" data-role-search-status="auditee_id" class="assignment-status" aria-live="polite" hidden></span><select id="auditee_id" name="auditee_id" class="tw-field" required><option value="">Pilih auditi</option><?php foreach ($auditees as $user): ?><option value="<?php echo (int) $user->id; ?>" data-search="<?php echo html_escape(mb_strtolower($user->nama, 'UTF-8')); ?>"><?php echo html_escape($user->nama . ' — ' . $user->email); ?></option><?php endforeach; ?></select></div>
                    <div class="assignment-field"><label class="assignment-label" for="auditor-search">Auditor standar <span class="assignment-help">Pencarian menyaring pilihan tanpa menghapus nilai yang sudah dipilih.</span></label><input id="auditor-search" type="search" class="assignment-search" data-role-search="auditor_id" data-role-search-target="assignment-auditor" aria-label="Cari auditor berdasarkan nama" placeholder="Cari berdasarkan nama" autocomplete="off" hidden><span id="auditor-search-status" data-role-search-status="auditor_id" class="assignment-status" aria-live="polite" hidden></span></div>
                </div>
            </section>
            <section class="assignment-card" aria-labelledby="assignment-standards-title">
                <div class="assignment-section-head"><span class="assignment-section-index">02</span><div><h2 id="assignment-standards-title">Pilih standar yang diaudit</h2><p>Semua standar pada versi terpilih dipilih otomatis. Hapus pilihan untuk audit bertahap, lalu tetapkan auditor per standar.</p></div></div>
                <div class="assignment-toolbar"><div><label class="assignment-label" for="standards-search">Cari standar</label><input id="standards-search" class="assignment-search" type="search" placeholder="Cari kode atau judul standar" autocomplete="off"><span id="standards-search-status" class="assignment-status" aria-live="polite"></span></div><label class="assignment-toggle"><input id="selected-only" type="checkbox"> <span>Hanya tampilkan yang dipilih</span></label></div>
                <p id="selected-count" class="assignment-count" aria-live="polite">0 standar dipilih.</p>
                <div id="standards-scope" class="assignment-table-wrap" aria-live="polite">
                     <table class="assignment-table"><caption>Daftar standar pada versi terpilih.</caption><thead><tr><th scope="col">Standar</th><th scope="col">Auditor penanggung jawab</th><th scope="col">Status</th></tr></thead><tbody id="standards-body"><tr><td colspan="3"><p class="assignment-empty">Pilih versi terlebih dahulu.</p></td></tr></tbody></table>
                </div>
                <noscript><p class="assignment-help" style="margin-top: 12px;">JavaScript diperlukan untuk menampilkan standar pada versi yang dipilih.</p></noscript>
                <div class="assignment-actions"><a href="<?php echo site_url('lpmpi/spmi-audits/cycle/detail/' . (int) $cycle->id); ?>#assignments">Batal</a><button class="tw-button-primary" type="submit">Buat snapshot penugasan</button></div>
            </section>
            </form>
            <script>
            (function () {
                var standardsByVersion = <?php echo json_encode($standards_by_version, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                var auditors = <?php echo json_encode(array_map(function ($user) { return ['id' => (int) $user->id, 'label' => $user->nama . ' — ' . $user->email, 'search' => mb_strtolower($user->nama, 'UTF-8')]; }, $auditors), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                var versionSelect = document.getElementById('source_version_id');
                var body = document.getElementById('standards-body');
                var selectedCount = document.getElementById('selected-count');
                var standardsSearch = document.getElementById('standards-search');
                var selectedOnly = document.getElementById('selected-only');
                var searchStatus = document.getElementById('standards-search-status');
                var roleSearches = document.querySelectorAll('[data-role-search]');

                function clearNode(node) { while (node.firstChild) node.removeChild(node.firstChild); }
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
                function updateCount() {
                    var checks = body.querySelectorAll('.assignment-check');
                    var selected = 0;
                    for (var i = 0; i < checks.length; i++) if (checks[i].checked) selected++;
                    selectedCount.textContent = selected + ' standar dipilih.';
                }
                function applyFilters() {
                    var query = standardsSearch.value.trim().toLocaleLowerCase();
                    var rows = body.querySelectorAll('tr[data-standard-search]');
                    var visible = 0;
                    for (var i = 0; i < rows.length; i++) {
                        var selected = rows[i].querySelector('.assignment-check').checked;
                        var matches = !query || rows[i].getAttribute('data-standard-search').indexOf(query) !== -1;
                        var show = matches && (!selectedOnly.checked || selected);
                        rows[i].hidden = !show;
                        if (show) visible++;
                    }
                    searchStatus.textContent = query || selectedOnly.checked ? visible + ' standar ditampilkan.' : '';
                }
                function renderStandards() {
                    var standards = standardsByVersion[versionSelect.value] || [];
                    clearNode(body);
                    if (!standards.length) {
                        var emptyRow = document.createElement('tr');
                        var emptyCell = document.createElement('td');
                        emptyCell.colSpan = 3;
                        var empty = document.createElement('p');
                        empty.className = 'assignment-empty';
                        empty.textContent = versionSelect.value ? 'Versi ini belum memiliki standar.' : 'Pilih versi terlebih dahulu.';
                        emptyCell.appendChild(empty);
                        emptyRow.appendChild(emptyCell);
                        body.appendChild(emptyRow);
                        updateCount();
                        return;
                    }
                    standards.forEach(function (standard, index) {
                        var row = document.createElement('tr');
                        row.setAttribute('data-standard-search', (standard.standard_code + ' ' + standard.title).toLocaleLowerCase());
                        var standardCell = document.createElement('td');
                        var standardLabel = document.createElement('label');
                        standardLabel.className = 'assignment-standard';
                        var checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.name = 'assignment_groups[' + index + '][source_standard_ids][]';
                        checkbox.value = standard.id;
                        checkbox.checked = true;
                        checkbox.className = 'assignment-check';
                        var text = document.createElement('span');
                        var code = document.createElement('span');
                        code.className = 'assignment-standard-code';
                        code.textContent = standard.standard_code;
                        var title = document.createElement('span');
                        title.className = 'assignment-standard-title';
                        title.textContent = standard.title;
                        text.appendChild(code);
                        text.appendChild(title);
                        standardLabel.appendChild(checkbox);
                        standardLabel.appendChild(text);
                        standardCell.appendChild(standardLabel);
                         var auditorCell = document.createElement('td');
                         auditorCell.setAttribute('data-label', 'Auditor penanggung jawab');
                        var select = document.createElement('select');
                        select.name = 'assignment_groups[' + index + '][auditor_id]';
                        select.setAttribute('aria-label', 'Auditor untuk ' + standard.standard_code + ' — ' + standard.title);
                        select.className = 'tw-field assignment-auditor-select';
                        select.required = true;
                        fillAuditorOptions(select);
                        auditorCell.appendChild(select);
                         var statusCell = document.createElement('td');
                         statusCell.className = 'assignment-standard-state';
                         statusCell.setAttribute('data-label', 'Status');
                        statusCell.textContent = 'Dipilih';
                        checkbox.addEventListener('change', function () {
                            select.disabled = !checkbox.checked;
                            select.required = checkbox.checked;
                            if (checkbox.checked) select.setAttribute('required', 'required'); else select.removeAttribute('required');
                            row.classList.toggle('is-selected', checkbox.checked);
                            statusCell.textContent = checkbox.checked ? 'Dipilih' : 'Dilewati';
                            updateCount();
                            applyFilters();
                        });
                        row.className = 'is-selected';
                        row.appendChild(standardCell);
                        row.appendChild(auditorCell);
                        row.appendChild(statusCell);
                        body.appendChild(row);
                    });
                    updateCount();
                    applyFilters();
                    var auditorSearch = document.querySelector('[data-role-search="auditor_id"]');
                    if (auditorSearch) auditorSearch.dispatchEvent(new Event('input'));
                }
                function initRoleSearch(search) {
                    var target = search.getAttribute('data-role-search-target');
                    var select = document.getElementById(target);
                    var status = document.querySelector('[data-role-search-status="' + search.getAttribute('data-role-search') + '"]');
                    if (!status || (target !== 'assignment-auditor' && !select)) return;
                    search.hidden = false;
                    status.hidden = false;
                    function updateRoleOptions() {
                        var query = search.value.trim().toLocaleLowerCase();
                        var matches = 0;
                        var selects = target === 'assignment-auditor' ? document.querySelectorAll('.assignment-auditor-select') : [select];
                        for (var selectIndex = 0; selectIndex < selects.length; selectIndex++) {
                            var currentSelect = selects[selectIndex];
                            if (!currentSelect) continue;
                            for (var optionIndex = 0; optionIndex < currentSelect.options.length; optionIndex++) {
                                var option = currentSelect.options[optionIndex];
                                var name = option.getAttribute('data-search');
                                if (name === null) continue;
                                var matchesQuery = !query || name.indexOf(query) !== -1;
                                option.hidden = !matchesQuery && !option.selected;
                                if (matchesQuery) matches++;
                            }
                        }
                        status.textContent = query ? matches + ' pilihan ditemukan.' : matches + ' pilihan tersedia.';
                    }
                    search.addEventListener('input', updateRoleOptions);
                    updateRoleOptions();
                }
                versionSelect.addEventListener('change', renderStandards);
                standardsSearch.addEventListener('input', applyFilters);
                selectedOnly.addEventListener('change', applyFilters);
                for (var i = 0; i < roleSearches.length; i++) initRoleSearch(roleSearches[i]);
            }());
            </script>
        <?php endif; ?>
    </div>
</div>
<?php include APPPATH . 'views/layouts/footer.php'; ?>
