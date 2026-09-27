<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$prodi = isset($prodi) && is_array($prodi) ? $prodi : [];
$jenjang_counts = isset($jenjang_counts) && is_array($jenjang_counts) ? $jenjang_counts : [];
$total_prodi = isset($total_prodi) ? (int) $total_prodi : 0;
$total_levels = isset($total_levels) ? (int) $total_levels : 0;
$total_staff = isset($total_staff) ? (int) $total_staff : 0;
$format_number = static function ($value) {
    return number_format((int) $value, 0, ',', '.');
};
$level_tone = static function ($level) {
    $level = strtoupper(trim((string) $level));
    return $level === 'D3' ? 'master-level-d3' : ($level === 'S1' ? 'master-level-s1' : ($level === 'S2' ? 'master-level-s2' : 'master-level-other'));
};

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<style>
    .master-data-root { max-width: 1320px; margin: 0 auto; }
    .master-data-breadcrumb { color: var(--ami-muted); font-size: 12px; letter-spacing: .02em; margin-bottom: 8px; }
    .master-management-card { display: flex; align-items: center; justify-content: space-between; gap: var(--ami-space-lg); margin-bottom: var(--ami-space-lg); }
    .master-management-copy { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .master-management-copy h2 { margin: 0 0 6px; color: var(--ami-text); font-size: 25px; line-height: 1.2; }
    .master-management-copy p { max-width: 680px; margin: 0; color: var(--ami-muted); }
    .master-management-icon { width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 48px; border-radius: 14px; color: var(--ami-blue); background: var(--ami-link-soft); font-size: 20px; }
    .master-management-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: var(--ami-space-sm); }
    .master-data-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: var(--ami-space-md); margin-bottom: var(--ami-space-lg); }
    .master-data-summary .ami-stat-card { min-height: 116px; }
    .master-data-levels { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .master-level-badge { display: inline-flex; align-items: center; gap: 5px; border: 1px solid currentColor; border-radius: 999px; padding: 3px 8px; font-size: 11px; font-weight: 700; line-height: 1.2; }
    .master-level-d3 { color: var(--ami-blue); background: rgba(24, 95, 165, .08); }
    .master-level-s1 { color: var(--ami-green); background: rgba(59, 109, 17, .08); }
    .master-level-s2 { color: #534ab7; background: rgba(83, 74, 183, .08); }
    .master-level-other { color: var(--ami-muted); background: rgba(102, 112, 133, .08); }
    .master-directory-head { display: flex; align-items: flex-end; justify-content: space-between; gap: var(--ami-space-md); margin-bottom: var(--ami-space-md); }
    .master-directory-head h3 { margin: 0 0 4px; font-size: 18px; }
    .master-directory-head p { margin: 0; color: var(--ami-muted); font-size: 12px; }
    .master-controls { display: flex; align-items: center; gap: var(--ami-space-sm); flex-wrap: wrap; }
    .master-controls .form-control { background-color: var(--ami-panel); border: 1px solid var(--ami-border); color: var(--ami-text); }
    .master-controls .form-control::placeholder { color: var(--ami-muted); opacity: 1; }
    .master-controls .form-control:focus { background-color: var(--ami-panel); border-color: var(--ami-link); color: var(--ami-text); box-shadow: 0 0 0 .2rem var(--ami-link-soft); }
    .master-controls select.form-control option { background-color: var(--ami-panel); color: var(--ami-text); }
    .master-control { min-width: 190px; }
    .master-table-wrap { overflow-x: auto; }
    .master-data-table { min-width: 920px; margin-bottom: 0; }
    .master-data-table th { white-space: nowrap; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; }
    .master-data-table td { vertical-align: middle; }
    .master-code { color: var(--ami-muted); font-family: monospace; font-size: 12px; }
    .master-program-name { font-weight: 700; color: var(--ami-text); }
    .master-staff-count { font-weight: 700; }
    .master-date { color: var(--ami-muted); white-space: nowrap; }
    .master-row-actions { display: flex; justify-content: flex-end; flex-wrap: wrap; gap: 6px; min-width: 245px; }
    .master-pagination { display: flex; align-items: center; justify-content: space-between; gap: var(--ami-space-sm); margin-top: var(--ami-space-md); color: var(--ami-muted); font-size: 12px; }
    .master-pagination-buttons { display: flex; gap: 6px; }
    .master-pagination button[disabled] { cursor: not-allowed; opacity: .45; }
    .master-no-results { display: none; }
    @media (max-width: 991.98px) { .master-data-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); } .master-data-summary .master-level-card { grid-column: 1 / -1; } .master-management-card, .master-directory-head { align-items: flex-start; flex-direction: column; } .master-management-actions { justify-content: flex-start; } }
    @media (max-width: 575.98px) { .master-data-summary { grid-template-columns: 1fr; } .master-data-summary .master-level-card { grid-column: auto; } .master-management-copy { align-items: flex-start; } .master-management-actions, .master-management-actions .btn, .master-control { width: 100%; } .master-pagination { align-items: flex-start; flex-direction: column; } }
</style>

<main id="master-data-prodi-staf" class="master-data-root" data-page-size="10">
    <div class="master-data-breadcrumb">Beranda / Management / Master Data Prodi &amp; Staf</div>
    <section class="ami-panel master-management-card" aria-labelledby="master-data-title">
        <div class="ami-panel-body master-management-copy">
            <span class="master-management-icon"><i class="fas fa-graduation-cap" aria-hidden="true"></i></span>
            <div><div class="ami-eyebrow">Management</div><h2 id="master-data-title">Master Data Prodi &amp; Staf</h2><p>Pusat pengelolaan program studi dan roster staf untuk kebutuhan pengelolaan mutu. Halaman ini bukan halaman manajemen akun; pembuatan akun tetap dilakukan melalui menu Manajemen Pengguna.</p></div>
        </div>
        <div class="ami-panel-body master-management-actions" aria-label="Aksi master data">
            <a class="btn btn-outline-ami btn-ami" href="<?php echo html_escape(site_url('lpmpi/prodi-import')); ?>"><i class="fas fa-file-import" aria-hidden="true"></i> Import Prodi</a>
            <a class="btn btn-primary btn-ami" href="<?php echo html_escape(site_url('profil/prodi/create')); ?>"><i class="fas fa-plus" aria-hidden="true"></i> Tambah Prodi</a>
        </div>
    </section>

    <section class="master-data-summary" aria-label="Ringkasan data program studi">
        <div class="ami-stat-card"><div class="ami-stat-icon tone-blue"><i class="fas fa-graduation-cap" aria-hidden="true"></i></div><div><div class="ami-stat-label">Total Program Studi</div><div class="ami-stat-value"><?php echo html_escape($format_number($total_prodi)); ?></div></div></div>
        <div class="ami-stat-card"><div class="ami-stat-icon tone-teal"><i class="fas fa-user-check" aria-hidden="true"></i></div><div><div class="ami-stat-label">Total Staf Terhubung</div><div class="ami-stat-value"><?php echo html_escape($format_number($total_staff)); ?></div></div></div>
        <div class="ami-stat-card master-level-card"><div class="ami-stat-icon tone-rose"><i class="fas fa-layer-group" aria-hidden="true"></i></div><div><div class="ami-stat-label">Jenjang Tersedia</div><div class="ami-stat-value"><?php echo html_escape($format_number($total_levels)); ?></div><div class="master-data-levels"><?php if (empty($jenjang_counts)): ?><span class="text-muted">Belum tersedia</span><?php else: foreach ($jenjang_counts as $level => $count): ?><span class="master-level-badge <?php echo html_escape($level_tone($level)); ?>"><?php echo html_escape($level); ?> <span><?php echo html_escape($format_number($count)); ?></span></span><?php endforeach; endif; ?></div></div></div>
    </section>

    <section class="ami-panel" aria-labelledby="directory-title"><div class="ami-panel-body">
        <div class="master-directory-head"><div><h3 id="directory-title">Direktori Program Studi</h3><p>Kelola data Prodi atau pilih roster untuk melihat staf yang sudah terhubung.</p></div><div class="master-controls"><div class="master-control"><label class="sr-only" for="master-search">Cari kode atau nama program studi</label><input class="form-control" id="master-search" type="search" placeholder="Cari kode atau nama..." autocomplete="off"></div><div class="master-control"><label class="sr-only" for="master-level-filter">Filter jenjang</label><select class="form-control" id="master-level-filter"><option value="">Semua jenjang</option><?php foreach ($jenjang_counts as $level => $count): ?><option value="<?php echo html_escape(strtolower($level)); ?>"><?php echo html_escape($level); ?></option><?php endforeach; ?></select></div></div></div>
        <div class="master-table-wrap"><table class="table ami-table master-data-table"><caption class="sr-only">Direktori program studi, jumlah staf aktif terhubung, dan tindakan pengelolaan</caption><thead><tr><th scope="col">No</th><th scope="col">Kode</th><th scope="col">Nama Program Studi</th><th scope="col">Jenjang</th><th scope="col">Jumlah Staf</th><th scope="col">Terakhir Diperbarui</th><th scope="col" class="text-right">Aksi</th></tr></thead><tbody id="master-directory-body">
        <?php foreach ($prodi as $index => $row): ?><?php $level = trim((string) ($row->jenjang ?? '')); $updated_at = trim((string) ($row->updated_at ?? '')); ?><tr data-master-row data-search="<?php echo html_escape(strtolower((string) ($row->kode_prodi ?? '') . ' ' . (string) ($row->nama_prodi ?? ''))); ?>" data-level="<?php echo html_escape(strtolower($level)); ?>"><td data-column="number"><?php echo html_escape((string) ($index + 1)); ?></td><td class="master-code"><?php echo html_escape((string) ($row->kode_prodi ?? '-')); ?></td><td class="master-program-name"><?php echo html_escape((string) ($row->nama_prodi ?? '-')); ?></td><td><span class="master-level-badge <?php echo html_escape($level_tone($level)); ?>"><?php echo html_escape($level !== '' ? $level : '-'); ?></span></td><td class="master-staff-count"><?php echo html_escape($format_number($row->active_staff_count ?? 0)); ?></td><td class="master-date"><?php echo html_escape($updated_at !== '' ? format_tanggal_indo($updated_at) : '-'); ?></td><td><div class="master-row-actions"><a class="btn btn-sm btn-outline-ami" href="<?php echo html_escape(site_url('profil/prodi/edit/' . (int) $row->id)); ?>"><i class="fas fa-edit" aria-hidden="true"></i> Edit</a><?php echo form_open('profil/prodi/delete/' . (int) $row->id, ['class' => 'd-inline']); ?><button type="submit" class="btn btn-sm btn-outline-danger" onclick="return window.confirm('Hapus program studi ini?');">Hapus</button><?php echo form_close(); ?><a class="btn btn-sm btn-outline-ami" href="<?php echo html_escape(site_url('profil/prodi/' . (int) $row->id . '/staf')); ?>"><i class="fas fa-users" aria-hidden="true"></i> Lihat Staf</a></div></td></tr><?php endforeach; ?>
        <tr id="master-no-results" class="master-no-results"><td colspan="7"><div class="ami-empty"><div class="ami-empty-icon"><i class="fas fa-search" aria-hidden="true"></i></div><div class="ami-empty-title">Data tidak ditemukan</div><div>Coba ubah kata kunci atau filter jenjang.</div></div></td></tr>
        </tbody></table></div>
        <?php if (empty($prodi)): ?><div class="ami-empty"><div class="ami-empty-icon"><i class="fas fa-graduation-cap" aria-hidden="true"></i></div><div class="ami-empty-title">Belum ada data program studi</div><div>Gunakan workflow import atau tambah Prodi untuk mengisi direktori.</div><div class="mt-3"><a class="btn btn-primary btn-ami" href="<?php echo html_escape(site_url('profil/prodi/create')); ?>"><i class="fas fa-plus" aria-hidden="true"></i> Tambah Prodi</a></div></div><?php endif; ?>
        <div class="master-pagination" data-master-pagination><span data-master-summary></span><div class="master-pagination-buttons"><button type="button" class="btn btn-sm btn-outline-ami" data-master-prev>‹ Sebelumnya</button><button type="button" class="btn btn-sm btn-outline-ami" data-master-next>Berikutnya ›</button></div></div>
    </div></section>
</main>

<script>
(function () {
    'use strict';
    var root = document.getElementById('master-data-prodi-staf');
    if (!root) return;
    var rows = Array.prototype.slice.call(root.querySelectorAll('[data-master-row]'));
    var search = document.getElementById('master-search');
    var filter = document.getElementById('master-level-filter');
    var summary = root.querySelector('[data-master-summary]');
    var prev = root.querySelector('[data-master-prev]');
    var next = root.querySelector('[data-master-next]');
    var empty = document.getElementById('master-no-results');
    var page = 1;
    var pageSize = parseInt(root.getAttribute('data-page-size'), 10) || 10;
    function render() {
        var query = (search.value || '').toLowerCase().trim();
        var level = (filter.value || '').toLowerCase();
        var visible = rows.filter(function (row) { return (!query || row.getAttribute('data-search').indexOf(query) !== -1) && (!level || row.getAttribute('data-level') === level); });
        var pageCount = Math.max(1, Math.ceil(visible.length / pageSize));
        page = Math.min(page, pageCount);
        rows.forEach(function (row) { row.style.display = 'none'; });
        visible.slice((page - 1) * pageSize, page * pageSize).forEach(function (row) { row.style.display = ''; });
        empty.style.display = visible.length ? 'none' : '';
        summary.textContent = visible.length ? 'Menampilkan ' + ((page - 1) * pageSize + 1) + '–' + Math.min(page * pageSize, visible.length) + ' dari ' + visible.length + ' Prodi' : 'Tidak ada Prodi yang sesuai';
        prev.disabled = page <= 1;
        next.disabled = page >= pageCount;
    }
    search.addEventListener('input', function () { page = 1; render(); });
    filter.addEventListener('change', function () { page = 1; render(); });
    prev.addEventListener('click', function () { if (page > 1) { page--; render(); } });
    next.addEventListener('click', function () { page++; render(); });
    render();
}());
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
