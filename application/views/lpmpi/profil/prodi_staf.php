<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$icon = function ($name, $class = 'tw-w-4 tw-h-4') {
    $paths = [
        'arrow-left' => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
        'plus' => '<path d="M5 12h14"/><path d="M12 5v14"/>',
        'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'user-check' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/>',
        'user-x' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" x2="22" y1="8" y2="13"/><line x1="22" x2="17" y1="8" y2="13"/>',
        'arrow-right-left' => '<path d="m16 3 4 4-4 4"/><path d="M20 7H4"/><path d="m8 21-4-4 4-4"/><path d="M4 17h16"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        'refresh-cw' => '<path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/>',
        'graduation-cap' => '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>'
    ];
    if (!isset($paths[$name])) return '';
    return '<svg xmlns="http://www.w3.org/2000/svg" class="' . html_escape($class) . '" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[$name] . '</svg>';
};

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<main id="master-data-prodi-staf" class="master-data-root">
    <div id="organization-root">
        <div class="roster-root tw-space-y-6">
            <div class="master-data-breadcrumb">Beranda / Management / Master Data Organisasi &amp; Staf / Kelola Staf Prodi</div>

            <section class="roster-card" aria-labelledby="roster-title">
                <div class="tw-flex tw-flex-col lg:tw-flex-row lg:tw-items-center lg:tw-justify-between tw-gap-4">
                    <div class="tw-min-w-0">
                        <div class="ami-eyebrow tw-inline-flex tw-items-center tw-gap-1.5 tw-text-xs tw-font-semibold tw-text-blue-600 tw-uppercase tw-tracking-wider tw-mb-1">
                            <?php echo $icon('graduation-cap', 'tw-w-3.5 tw-h-3.5'); ?>
                            <span>Program Studi</span>
                        </div>
                        <h2 id="roster-title" class="ami-section-title tw-text-xl sm:tw-text-2xl tw-font-bold tw-text-slate-900 tw-tracking-tight tw-m-0">
                            <?php echo html_escape($page_title); ?>
                        </h2>
                        <p class="master-code tw-mt-1.5 tw-mb-0 tw-text-sm tw-text-slate-600 tw-font-medium">
                            <span class="tw-text-slate-900 tw-font-semibold"><?php echo html_escape($prodi->nama_prodi ?: '-'); ?></span>
                            <?php if (!empty($prodi->kode_prodi)): ?>
                                <span class="tw-mx-1.5 tw-text-slate-300">·</span>
                                <span class="tw-font-mono tw-text-slate-700 tw-bg-slate-100 tw-px-2 tw-py-0.5 tw-rounded tw-text-xs"><?php echo html_escape($prodi->kode_prodi); ?></span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="tw-flex tw-items-center tw-gap-2.5 tw-flex-wrap sm:tw-flex-nowrap">
                        <a class="btn-outline-ami btn-ami tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-h-11 tw-px-4 tw-text-sm tw-font-medium tw-rounded-xl tw-border tw-border-slate-300 tw-text-slate-700 tw-bg-white hover:tw-bg-slate-50 tw-transition-colors" href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf')); ?>">
                            <?php echo $icon('arrow-left', 'tw-w-4 tw-h-4'); ?>
                            <span>Kembali</span>
                        </a>
                    </div>
                </div>
            </section>

            <section class="roster-card" aria-labelledby="add-staf-title">
                <div class="tw-pb-4 tw-mb-5 tw-border-b tw-border-slate-100">
                    <h3 id="add-staf-title" class="tw-text-base tw-font-bold tw-text-slate-900 tw-flex tw-items-center tw-gap-2 tw-m-0">
                        <?php echo $icon('plus', 'tw-w-4 tw-h-4 tw-text-blue-600'); ?>
                        <span>Tambah Staf Existing</span>
                    </h3>
                    <p class="tw-text-xs tw-text-slate-500 tw-mt-1 tw-mb-0">Hubungkan akun auditor atau auditee ke program studi ini.</p>
                </div>

                <?php echo form_open('profil/prodi/' . (int) $prodi->id . '/staf/add'); ?>
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-12 tw-gap-4 tw-items-end">
                    <div class="ami-form-group md:tw-col-span-6 tw-m-0">
                        <label for="staf-account" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700 tw-mb-1.5">Akun auditor atau auditee <span class="tw-text-rose-500">*</span></label>
                        <select class="ami-form-control tw-w-full tw-h-11 tw-px-3.5 tw-text-sm tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-text-slate-800 focus:tw-border-blue-500 focus:tw-ring-2 focus:tw-ring-blue-100 tw-transition-colors" id="staf-account" name="id_akun" required>
                            <option value="">Pilih akun</option>
                            <?php foreach ($eligible_users as $user): ?>
                                <option value="<?php echo (int) $user->id; ?>"><?php echo html_escape($user->nama . ' — ' . $user->email . ' (' . $user->role . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="ami-form-group md:tw-col-span-4 tw-m-0">
                        <label for="staf-jabatan" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700 tw-mb-1.5">Jabatan</label>
                        <input class="ami-form-control tw-w-full tw-h-11 tw-px-3.5 tw-text-sm tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-text-slate-800 placeholder:tw-text-slate-400 focus:tw-border-blue-500 focus:tw-ring-2 focus:tw-ring-blue-100 tw-transition-colors" id="staf-jabatan" name="jabatan" maxlength="100" placeholder="Contoh: Dosen, Sekretaris Prodi">
                    </div>
                    <div class="ami-form-group md:tw-col-span-2 tw-m-0">
                        <button type="submit" class="tw-button-primary btn-ami tw-w-full tw-h-11 tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-4 tw-text-sm tw-font-semibold tw-rounded-xl tw-bg-blue-600 hover:tw-bg-blue-700 tw-text-white tw-shadow-sm tw-transition-colors">
                            <?php echo $icon('plus', 'tw-w-4 tw-h-4'); ?>
                            <span>Tambah</span>
                        </button>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </section>

            <div class="roster-card tw-py-4">
                <div class="ami-form-group tw-m-0">
                    <label for="prodi-staf-search" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700 tw-mb-1.5">Cari staf</label>
                    <div class="tw-relative">
                        <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3.5 tw-flex tw-items-center tw-pointer-events-none tw-text-slate-400">
                            <?php echo $icon('search', 'tw-w-4 tw-h-4'); ?>
                        </div>
                        <input class="ami-form-control tw-w-full tw-h-11 tw-pl-10 tw-pr-4 tw-text-sm tw-rounded-xl tw-border tw-border-slate-300 tw-bg-white tw-text-slate-800 placeholder:tw-text-slate-400 focus:tw-border-blue-500 focus:tw-ring-2 focus:tw-ring-blue-100 tw-transition-colors" id="prodi-staf-search" data-roster-search type="search" placeholder="Nama, email, role, atau jabatan" autocomplete="off">
                    </div>
                </div>
            </div>

            <?php foreach (['active' => ['title' => 'Staf Aktif', 'rows' => $active_staf], 'inactive' => ['title' => 'Staf Tidak Aktif', 'rows' => $inactive_staf]] as $status => $section): ?>
                <section class="roster-card" data-roster-section="<?php echo html_escape($status); ?>" data-roster-page-size="10">
                    <div class="tw-flex tw-items-center tw-justify-between tw-pb-4 tw-mb-4 tw-border-b tw-border-slate-100">
                        <div class="tw-flex tw-items-center tw-gap-2.5">
                            <div class="tw-p-2 tw-rounded-lg <?php echo $status === 'active' ? 'tw-bg-emerald-50 tw-text-emerald-600' : 'tw-bg-slate-100 tw-text-slate-500'; ?>">
                                <?php echo $icon($status === 'active' ? 'user-check' : 'user-x', 'tw-w-4 tw-h-4'); ?>
                            </div>
                            <div>
                                <h3 class="tw-text-base tw-font-bold tw-text-slate-900 tw-m-0"><?php echo html_escape($section['title']); ?></h3>
                                <p class="tw-text-xs tw-text-slate-500 tw-mt-0.5 tw-mb-0"><?php echo $status === 'active' ? 'Daftar staf yang saat ini aktif terhubung.' : 'Daftar staf yang dinonaktifkan dari prodi.'; ?></p>
                            </div>
                        </div>
                        <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-1 tw-rounded-full tw-text-xs tw-font-semibold <?php echo $status === 'active' ? 'tw-bg-emerald-50 tw-text-emerald-700 tw-border tw-border-emerald-200' : 'tw-bg-slate-100 tw-text-slate-600 tw-border tw-border-slate-200'; ?>">
                            <?php echo count($section['rows']); ?> staf
                        </span>
                    </div>

                    <div class="roster-table-wrap ami-table-wrap">
                        <table class="roster-table ami-table">
                            <thead>
                                <tr>
                                    <th class="tw-w-1/4">Nama</th>
                                    <th class="tw-w-1/4">Email</th>
                                    <th class="tw-w-28">Role</th>
                                    <th>Jabatan</th>
                                    <th class="tw-text-right tw-w-64">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($section['rows'])): ?>
                                <tr data-roster-source-empty>
                                    <td colspan="5" class="tw-py-10 tw-text-center tw-text-slate-500">
                                        <div class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-2">
                                            <div class="tw-p-3 tw-bg-slate-100 tw-rounded-full tw-text-slate-400">
                                                <?php echo $icon('users', 'tw-w-6 tw-h-6'); ?>
                                            </div>
                                            <span class="tw-font-medium tw-text-sm">Belum ada staf.</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: foreach ($section['rows'] as $staf): ?>
                                <tr data-roster-row data-roster-search="<?php echo html_escape(mb_strtolower($staf->nama . ' ' . $staf->email . ' ' . $staf->role . ' ' . $staf->jabatan, 'UTF-8')); ?>">
                                    <td data-label="Nama" class="tw-font-medium tw-text-slate-900">
                                        <div class="tw-flex tw-items-center tw-gap-2.5">
                                            <div class="tw-w-8 tw-h-8 tw-rounded-full tw-bg-slate-100 tw-text-slate-600 tw-flex tw-items-center tw-justify-center tw-text-xs tw-font-bold tw-shrink-0">
                                                <?php echo html_escape(mb_substr($staf->nama, 0, 1, 'UTF-8')); ?>
                                            </div>
                                            <span class="tw-truncate"><?php echo html_escape($staf->nama); ?></span>
                                        </div>
                                    </td>
                                    <td data-label="Email" class="tw-text-slate-600">
                                        <span class="tw-text-xs tw-font-mono"><?php echo html_escape($staf->email); ?></span>
                                    </td>
                                    <td data-label="Role">
                                        <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-xs tw-font-medium <?php echo $staf->role === 'auditor' ? 'tw-bg-indigo-50 tw-text-indigo-700 tw-border tw-border-indigo-100' : 'tw-bg-blue-50 tw-text-blue-700 tw-border tw-border-blue-100'; ?>">
                                            <?php echo html_escape($staf->role); ?>
                                        </span>
                                    </td>
                                    <td data-label="Jabatan">
                                        <?php echo form_open('profil/prodi/' . (int) $prodi->id . '/staf/update/' . (int) $staf->id, ['class' => 'd-flex tw-flex tw-items-center tw-gap-1.5 tw-m-0']); ?>
                                            <input class="ami-form-control tw-text-xs tw-h-9 tw-px-2.5 tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-text-slate-800 focus:tw-border-blue-500 tw-transition-colors tw-w-full sm:tw-w-44" name="jabatan" maxlength="100" value="<?php echo html_escape($staf->jabatan); ?>" placeholder="Jabatan">
                                            <button type="submit" class="roster-action-btn tw-text-xs btn-outline-ami tw-h-9 tw-px-2.5 tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white hover:tw-bg-slate-50 tw-text-slate-700 tw-font-medium tw-transition-colors">
                                                <?php echo $icon('check', 'tw-w-3 tw-h-3'); ?>
                                                <span>Simpan</span>
                                            </button>
                                        <?php echo form_close(); ?>
                                    </td>
                                    <td data-label="Aksi">
                                        <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-end tw-gap-1.5">
                                        <?php if ($status === 'active'): ?>
                                            <?php echo form_open('profil/prodi/' . (int) $prodi->id . '/staf/move/' . (int) $staf->id, ['class' => 'd-flex tw-flex tw-items-center tw-gap-1.5 tw-m-0']); ?>
                                                <select class="ami-form-control tw-text-xs tw-h-9 tw-px-2 tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-text-slate-800 focus:tw-border-blue-500 tw-transition-colors tw-w-28 sm:tw-w-36" name="target_prodi_id" required>
                                                    <option value="">Pindah ke</option>
                                                    <?php foreach ($target_prodi as $target): ?>
                                                        <option value="<?php echo (int) $target->id; ?>"><?php echo html_escape($target->nama_prodi ?: $target->kode_prodi); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="roster-action-btn tw-text-xs btn-outline-ami tw-h-9 tw-px-2.5 tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white hover:tw-bg-slate-50 tw-text-slate-700 tw-font-medium tw-transition-colors">
                                                    <?php echo $icon('arrow-right-left', 'tw-w-3 tw-h-3'); ?>
                                                    <span>Pindah</span>
                                                </button>
                                            <?php echo form_close(); ?>
                                            <?php echo form_open('profil/prodi/' . (int) $prodi->id . '/staf/deactivate/' . (int) $staf->id, ['class' => 'tw-m-0']); ?>
                                                <button type="submit" class="roster-action-btn tw-text-xs ami-btn-outline-danger tw-h-9 tw-px-2.5 tw-rounded-lg tw-border tw-border-rose-300 tw-bg-white hover:tw-bg-rose-50 tw-text-rose-600 tw-font-medium tw-transition-colors">
                                                    <?php echo $icon('user-x', 'tw-w-3 tw-h-3'); ?>
                                                    <span>Nonaktifkan</span>
                                                </button>
                                            <?php echo form_close(); ?>
                                        <?php else: ?>
                                            <?php echo form_open('profil/prodi/' . (int) $prodi->id . '/staf/reactivate/' . (int) $staf->id, ['class' => 'tw-m-0']); ?>
                                                <button type="submit" class="roster-action-btn tw-text-xs btn-outline-ami tw-h-9 tw-px-3 tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white hover:tw-bg-slate-50 tw-text-slate-700 tw-font-medium tw-transition-colors">
                                                    <?php echo $icon('refresh-cw', 'tw-w-3 tw-h-3'); ?>
                                                    <span>Aktifkan kembali</span>
                                                </button>
                                            <?php echo form_close(); ?>
                                        <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                            <tr data-roster-no-results hidden>
                                <td colspan="5" class="tw-py-8 tw-text-center tw-text-slate-500">
                                    <div class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-1.5">
                                        <span class="tw-font-medium tw-text-sm">Tidak ada staf yang cocok dengan pencarian.</span>
                                        <span class="tw-text-xs tw-text-slate-400">Coba ubah kata kunci pencarian.</span>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="tw-flex tw-items-center tw-justify-between tw-flex-wrap tw-gap-3 tw-pt-4 tw-mt-2 tw-border-t tw-border-slate-100" data-roster-controls>
                        <span class="ami-muted tw-text-xs tw-font-medium tw-text-slate-500" data-roster-summary></span>
                        <div class="tw-flex tw-items-center tw-gap-2">
                            <button type="button" class="btn-outline-ami tw-inline-flex tw-items-center tw-gap-1.5 tw-h-9 tw-px-3 tw-text-xs tw-font-medium tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white hover:tw-bg-slate-50 tw-text-slate-700 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed tw-transition-colors" data-roster-prev>Sebelumnya</button>
                            <button type="button" class="btn-outline-ami tw-inline-flex tw-items-center tw-gap-1.5 tw-h-9 tw-px-3 tw-text-xs tw-font-medium tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white hover:tw-bg-slate-50 tw-text-slate-700 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed tw-transition-colors" data-roster-next>Berikutnya</button>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<script>
(function () {
    var search = document.querySelector('[data-roster-search]');
    var sections = Array.prototype.slice.call(document.querySelectorAll('[data-roster-section]'));
    var state = sections.map(function (section) {
        return { section: section, page: 1, rows: Array.prototype.slice.call(section.querySelectorAll('[data-roster-row]')) };
    });
    function render(item) {
        var query = search ? search.value.toLowerCase().trim() : '';
        var matches = item.rows.filter(function (row) {
            return !query || (row.getAttribute('data-roster-search') || '').indexOf(query) !== -1;
        });
        var pageSize = parseInt(item.section.getAttribute('data-roster-page-size'), 10) || 10;
        var pages = Math.max(1, Math.ceil(matches.length / pageSize));
        item.page = Math.min(item.page, pages);
        var start = (item.page - 1) * pageSize;
        var pageRows = matches.slice(start, start + pageSize);
        item.rows.forEach(function (row) { row.hidden = pageRows.indexOf(row) === -1; });
        var noResults = item.section.querySelector('[data-roster-no-results]');
        var sourceEmpty = item.section.querySelector('[data-roster-source-empty]');
        var controls = item.section.querySelector('[data-roster-controls]');
        var summary = item.section.querySelector('[data-roster-summary]');
        noResults.hidden = item.rows.length === 0 || matches.length !== 0;
        if (sourceEmpty) sourceEmpty.hidden = item.rows.length !== 0;
        controls.hidden = item.rows.length === 0;
        summary.textContent = matches.length ? 'Menampilkan ' + (start + 1) + '–' + Math.min(start + pageSize, matches.length) + ' dari ' + matches.length + ' staf' : '0 staf cocok';
        item.section.querySelector('[data-roster-prev]').disabled = item.page === 1 || matches.length === 0;
        item.section.querySelector('[data-roster-next]').disabled = item.page === pages || matches.length === 0;
    }
    state.forEach(function (item) {
        item.section.querySelector('[data-roster-prev]').addEventListener('click', function () { item.page -= 1; render(item); });
        item.section.querySelector('[data-roster-next]').addEventListener('click', function () { item.page += 1; render(item); });
        render(item);
    });
    if (search) search.addEventListener('input', function () { state.forEach(function (item) { item.page = 1; render(item); }); });
}());
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
