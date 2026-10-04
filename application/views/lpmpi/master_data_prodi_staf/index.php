<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$organization_units = isset($organization_units) && is_array($organization_units) ? $organization_units : [];
$prodi_directory = isset($prodi_directory) && is_array($prodi_directory) ? $prodi_directory : (isset($prodi) && is_array($prodi) ? $prodi : []);
$summary = isset($summary) && is_array($summary) ? $summary : (isset($organization_summary) && is_array($organization_summary) ? $organization_summary : []);
$non_prodi_staff_placements = isset($non_prodi_staff_placements) && is_array($non_prodi_staff_placements) ? $non_prodi_staff_placements : [];
$mapped_prodi_count = isset($mapped_prodi_count) ? (int) $mapped_prodi_count : 0;
$unmapped_prodi_count = isset($unmapped_prodi_count) ? (int) $unmapped_prodi_count : 0;
$prodi_active_staff_count = isset($prodi_active_staff_count) ? (int) $prodi_active_staff_count : 0;
$non_prodi_staff_count = isset($non_prodi_staff_count) ? (int) $non_prodi_staff_count : 0;

$format_number = static function ($value) {
    return number_format((int) $value, 0, ',', '.');
};
$value = static function ($row, $key, $fallback = '') {
    if (is_object($row) && property_exists($row, $key)) {
        return $row->{$key};
    }
    if (is_array($row) && array_key_exists($key, $row)) {
        return $row[$key];
    }
    return $fallback;
};
$type_labels = [
    'university' => 'Universitas',
    'faculty' => 'Fakultas',
    'study_program' => 'Program Studi',
    'bureau' => 'Biro',
    'unit' => 'Unit',
    'institute' => 'Lembaga',
    'upps' => 'UPPS',
];
$summary_cards = [
    ['key' => 'faculty', 'label' => 'Fakultas', 'icon' => 'fa-building', 'tone' => 'tone-blue'],
    ['key' => 'study_program', 'label' => 'Program Studi', 'icon' => 'fa-graduation-cap', 'tone' => 'tone-teal'],
    ['key' => 'bureau', 'label' => 'Biro', 'icon' => 'fa-briefcase', 'tone' => 'tone-amber'],
    ['key' => 'unit', 'label' => 'Unit', 'icon' => 'fa-sitemap', 'tone' => 'tone-rose'],
    ['key' => 'institute', 'label' => 'Lembaga', 'icon' => 'fa-landmark', 'tone' => 'tone-green'],
    ['key' => 'active_staff', 'label' => 'Staf Aktif', 'icon' => 'fa-user-check', 'tone' => 'tone-teal'],
];

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<style>
    .master-data-root { max-width: 1320px; margin: 0 auto; }
    .master-data-breadcrumb { color: var(--ami-muted); font-size: 12px; letter-spacing: .02em; margin-bottom: 12px; }
    .master-management-card { display: flex; align-items: center; justify-content: space-between; gap: var(--ami-space-lg); margin-bottom: var(--ami-space-lg); }
    .master-management-copy { display: flex; align-items: center; gap: 16px; min-width: 0; }
    .master-management-copy h2 { margin: 0 0 6px; color: var(--ami-text); font-size: 24px; line-height: 1.25; font-weight: 750; letter-spacing: -.02em; }
    .master-management-copy p { max-width: 720px; margin: 0; color: var(--ami-muted); font-size: 13.5px; line-height: 1.5; }
    .master-management-icon { width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 48px; border-radius: 12px; color: var(--ami-blue); background: var(--ami-link-soft); font-size: 20px; }
    .master-management-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 10px; }
    .master-data-summary { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 14px; margin-bottom: var(--ami-space-lg); }
    .master-data-summary .ami-stat-card { min-height: 108px; border-radius: 12px; border: 1px solid var(--ami-border); background: var(--ami-panel); padding: 14px 16px; display: flex; align-items: flex-start; gap: 12px; transition: transform .15s ease, box-shadow .15s ease; }
    .master-data-summary .ami-stat-card:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(15, 23, 42, .05); }
    .master-stat-caption { color: var(--ami-muted); font-size: 11px; margin-top: 4px; line-height: 1.3; }
    .master-directory-head { display: flex; align-items: flex-end; justify-content: space-between; gap: var(--ami-space-md); margin-bottom: var(--ami-space-md); }
    .master-directory-head h3 { margin: 0 0 4px; font-size: 18px; font-weight: 700; color: var(--ami-text); }
    .master-directory-head p { margin: 0; color: var(--ami-muted); font-size: 13px; }
    .master-controls { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .master-controls .form-control { background-color: var(--ami-panel); border: 1px solid var(--ami-border); color: var(--ami-text); }
    .master-controls .form-control::placeholder { color: var(--ami-muted); opacity: 1; }
    .master-controls .form-control:focus { background-color: var(--ami-panel); border-color: var(--ami-link); color: var(--ami-text); box-shadow: 0 0 0 .2rem var(--ami-link-soft); }
    .master-controls select.form-control option { background-color: var(--ami-panel); color: var(--ami-text); }
    .master-control { min-width: 170px; }
    .master-control-search { min-width: 260px; }
    .master-table-wrap { overflow-x: auto; border: 1px solid var(--ami-border); border-radius: 10px; background: var(--ami-panel); }
    .master-data-table { min-width: 900px; margin-bottom: 0; }
    .master-data-table thead th { background: #f8fafc; border-bottom: 1px solid var(--ami-border); color: var(--ami-muted); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; padding: 12px 16px; white-space: nowrap; }
    .master-data-table tbody td { padding: 14px 16px; border-top: 1px solid #f1f5f9; vertical-align: middle; }
    .master-data-table tbody tr:hover td { background-color: #f8fafc; }
    .master-row-secondary { color: var(--ami-muted); display: block; font-size: 12px; font-weight: 400; margin-top: 3px; }
    .master-code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 11.5px; }
    .master-name { color: var(--ami-text); font-weight: 650; font-size: 13.5px; }
    .master-subtext { color: var(--ami-muted); display: block; font-size: 12px; margin-top: 3px; }
    .master-type-badge, .master-status-badge, .master-mapping-badge { border: 1px solid currentColor; border-radius: 999px; display: inline-flex; align-items: center; line-height: 1.2; padding: 3px 9px; font-size: 11px; font-weight: 600; white-space: nowrap; }
    .master-type-badge { color: var(--ami-link); background: var(--ami-link-soft); border-color: transparent; }
    .master-status-active { color: var(--ami-green); background: rgba(59, 109, 17, .08); border-color: rgba(59, 109, 17, .2); }
    .master-status-inactive { color: var(--ami-muted); background: rgba(102, 112, 133, .08); border-color: rgba(102, 112, 133, .2); }
    .master-mapping-mapped { color: var(--ami-green); background: rgba(59, 109, 17, .08); border-color: rgba(59, 109, 17, .2); }
    .master-mapping-unmapped { color: var(--ami-amber); background: rgba(183, 120, 0, .08); border-color: rgba(183, 120, 0, .2); }
    .master-row-actions { align-items: center; display: flex; justify-content: flex-end; gap: 8px; min-width: 160px; white-space: nowrap; }
    .master-row-menu { position: relative; }
    .master-row-menu-toggle { color: var(--ami-muted); font-size: 12.5px; font-weight: 600; padding: 4px 8px; text-decoration: none; }
    .master-row-menu-toggle:hover { color: var(--ami-link); text-decoration: none; }
    .master-row-menu-toggle::after { margin-left: 4px; }
    .master-row-menu .dropdown-menu { background: var(--ami-panel); border: 1px solid var(--ami-border); border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, .1), 0 8px 10px -6px rgba(0, 0, 0, .1); gap: 4px; min-width: 150px; padding: 6px; }
    .master-row-menu .dropdown-menu.show { display: grid; }
    .master-row-menu .dropdown-menu .btn, .master-row-menu .dropdown-menu form { display: block; width: 100%; text-align: left; }
    .master-pagination { display: flex; align-items: center; justify-content: space-between; gap: var(--ami-space-sm); margin-top: var(--ami-space-md); color: var(--ami-muted); font-size: 12.5px; }
    .master-pagination-buttons { display: flex; gap: 8px; }
    .master-pagination button[disabled] { cursor: not-allowed; opacity: .45; }
    .master-no-results { display: none; }
    .master-directory-note { border-left: 3px solid var(--ami-amber); color: #92400e; background: #fffbeb; padding: 4px 8px; border-radius: 0 4px 4px 0; display: inline-block; font-size: 11.5px; line-height: 1.4; margin-top: 4px; }
    .master-staff-list { margin: 0; padding-left: 18px; list-style-type: disc; }
    .master-staff-list li { margin-bottom: 8px; font-size: 13.5px; line-height: 1.5; }
    .master-staff-list li:last-child { margin-bottom: 0; }
    @media (max-width: 1199.98px) { .master-data-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 991.98px) { .master-management-card, .master-directory-head { align-items: flex-start; flex-direction: column; } .master-management-actions { justify-content: flex-start; width: 100%; } }
    @media (max-width: 767.98px) { .master-data-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); } .master-controls, .master-control, .master-control-search { width: 100%; } }
    @media (max-width: 575.98px) { .master-data-summary { grid-template-columns: 1fr; } .master-management-copy { align-items: flex-start; } .master-management-actions, .master-management-actions .btn { width: 100%; } .master-pagination { align-items: flex-start; flex-direction: column; } }
</style>

<main id="master-data-prodi-staf" class="master-data-root" data-page-size="10">
    <div class="master-data-breadcrumb">Beranda / Management / Master Data Organisasi &amp; Staf</div>
    <section class="ami-panel master-management-card" aria-labelledby="master-data-title">
        <div class="ami-panel-body master-management-copy">
            <span class="master-management-icon"><i class="fas fa-sitemap" aria-hidden="true"></i></span>
            <div><div class="ami-eyebrow">Management</div><h2 id="master-data-title">Master Data Organisasi &amp; Staf</h2><p>Direktori canonical untuk Fakultas, Program Studi, Biro, Unit, Lembaga, dan staf aktif dalam satu struktur organisasi.</p></div>
        </div>
        <div class="ami-panel-body master-management-actions" aria-label="Aksi master data">
            <a class="btn btn-outline-ami btn-ami" href="<?php echo html_escape(site_url('lpmpi/prodi-import')); ?>"><i class="fas fa-file-import" aria-hidden="true"></i> Import Prodi</a>
            <a class="btn btn-primary btn-ami" href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf/create')); ?>"><i class="fas fa-plus" aria-hidden="true"></i> Tambah Data Organisasi</a>
        </div>
    </section>

    <section class="master-data-summary" aria-label="Ringkasan organisasi dan staf">
        <?php foreach ($summary_cards as $card): ?>
            <div class="ami-stat-card">
                <div class="ami-stat-icon <?php echo html_escape($card['tone']); ?>"><i class="fas <?php echo html_escape($card['icon']); ?>" aria-hidden="true"></i></div>
                <div><div class="ami-stat-label"><?php echo html_escape($card['label']); ?></div><div class="ami-stat-value"><?php echo html_escape($format_number(isset($summary[$card['key']]) ? $summary[$card['key']] : 0)); ?></div><?php if ($card['key'] === 'active_staff'): ?><div class="master-stat-caption">Pengguna unik berstatus aktif</div><?php endif; ?></div>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="ami-panel" aria-labelledby="directory-title"><div class="ami-panel-body">
        <div class="master-directory-head">
             <div><h3 id="directory-title">Direktori Organisasi &amp; Staf</h3><p>Daftar hierarki organisasi dan metadata akademik Program Studi. Gunakan pencarian atau filter untuk menemukan baris.</p></div>
            <div class="master-controls" role="search" aria-label="Filter direktori">
                <div class="master-control master-control-search"><label class="sr-only" for="master-search">Cari nama atau kode</label><input class="form-control" id="master-search" type="search" placeholder="Cari nama atau kode..." autocomplete="off"></div>
                <div class="master-control"><label class="sr-only" for="master-type-filter">Filter tipe</label><select class="form-control" id="master-type-filter"><option value="">Semua tipe</option><?php foreach ($type_labels as $type => $label): ?><?php if ($type !== 'study_program'): ?><option value="<?php echo html_escape($type); ?>"><?php echo html_escape($label); ?></option><?php endif; ?><?php endforeach; ?><option value="prodi">Program Studi</option><option value="study_program">Program Studi legacy belum terhubung</option></select></div>
                <div class="master-control"><label class="sr-only" for="master-status-filter">Filter status</label><select class="form-control" id="master-status-filter"><option value="">Semua status</option><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select></div>
            </div>
        </div>

        <div class="master-table-wrap"><table class="table ami-table master-data-table"><caption class="sr-only">Daftar organisasi dengan tipe, parent, staf, status, dan aksi.</caption><thead><tr><th scope="col">Organisasi</th><th scope="col">Tipe</th><th scope="col">Parent</th><th scope="col">Staf</th><th scope="col">Status</th><th scope="col" class="text-right">Aksi</th></tr></thead><tbody id="master-directory-body">
            <?php foreach ($organization_units as $unit): ?>
                <?php $unit_type = (string) $value($unit, 'type'); $unit_active = (int) $value($unit, 'is_active') === 1; $unit_is_root = $value($unit, 'parent_id') === NULL; $unit_search = strtolower(trim((string) $value($unit, 'code') . ' ' . (string) $value($unit, 'name'))); ?>
                <tr data-master-row data-search="<?php echo html_escape($unit_search); ?>" data-type="<?php echo html_escape($unit_type); ?>" data-status="<?php echo $unit_active ? 'active' : 'inactive'; ?>">
                    <td><span class="master-name"><?php echo html_escape((string) $value($unit, 'name', '-')); ?></span><span class="master-row-secondary master-code"><?php echo html_escape((string) $value($unit, 'code', '-')); ?><?php if ($unit_type === 'study_program'): ?> · Program Studi<?php endif; ?></span></td>
                    <td><span class="master-type-badge"><?php echo html_escape(isset($type_labels[$unit_type]) ? $type_labels[$unit_type] : $unit_type); ?></span></td>
                    <td><?php echo html_escape((string) $value($unit, 'parent_name', 'Root')); ?><?php if ($value($unit, 'parent_code') !== ''): ?><span class="master-subtext master-code"><?php echo html_escape((string) $value($unit, 'parent_code')); ?></span><?php endif; ?></td>
                    <td class="text-muted">—</td>
                    <td><span class="master-status-badge <?php echo $unit_active ? 'master-status-active' : 'master-status-inactive'; ?>"><?php echo $unit_active ? 'Aktif' : 'Nonaktif'; ?></span></td>
                    <td class="text-right"><div class="master-row-actions">
                        <?php if ($unit_is_root): ?><span class="text-muted" aria-label="Unit root">—</span><?php else: ?><a class="btn btn-sm btn-outline-ami" href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf/unit/detail/' . (int) $value($unit, 'id'))); ?>"><i class="fas fa-eye" aria-hidden="true"></i> Detail</a><?php endif; ?>
                        <?php if (in_array($unit_type, ['faculty', 'bureau', 'unit', 'institute'], TRUE)): ?>
                            <div class="dropdown master-row-menu"><button type="button" class="btn btn-sm btn-link dropdown-toggle master-row-menu-toggle" data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false" aria-label="Aksi unit">Lainnya</button><div class="dropdown-menu dropdown-menu-right">
                                <a class="btn btn-sm btn-outline-ami" href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf/unit/edit/' . (int) $value($unit, 'id'))); ?>"><i class="fas fa-edit" aria-hidden="true"></i> Edit</a>
                                <?php echo form_open('lpmpi/master-data-prodi-staf/unit/toggle/' . (int) $value($unit, 'id')); ?><button type="submit" class="btn btn-sm <?php echo $unit_active ? 'btn-outline-danger' : 'btn-outline-success'; ?>" onclick="return window.confirm('<?php echo $unit_active ? 'Nonaktifkan' : 'Aktifkan'; ?> unit ini?');"><?php echo $unit_active ? 'Nonaktifkan' : 'Aktifkan'; ?></button><?php echo form_close(); ?>
                            </div></div>
                        <?php endif; ?>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($prodi_directory as $row): ?>
                <?php $mapped = !empty($value($row, 'is_mapped_to_organization')); $prodi_active = $mapped && (int) $value($row, 'organization_unit_is_active') === 1; $prodi_name = (string) $value($row, 'nama_prodi', '-'); $prodi_code = (string) $value($row, 'kode_prodi', '-'); $prodi_search = strtolower(trim($prodi_code . ' ' . $prodi_name)); ?>
                <tr data-master-row data-search="<?php echo html_escape($prodi_search); ?>" data-type="prodi" data-status="<?php echo $mapped ? ($prodi_active ? 'active' : 'inactive') : ''; ?>" data-mapping="<?php echo $mapped ? 'mapped' : 'unmapped'; ?>">
                    <td><span class="master-name"><?php echo html_escape($prodi_name); ?></span><span class="master-row-secondary master-code"><?php echo html_escape($prodi_code); ?> · <?php echo html_escape((string) $value($row, 'jenjang', 'Jenjang belum diisi')); ?></span></td>
                    <td><span class="master-type-badge">Program Studi</span></td>
                    <td><?php if ($mapped): ?><span class="master-name"><?php echo html_escape((string) $value($row, 'faculty_name', 'Fakultas belum diisi')); ?></span><span class="master-row-secondary master-code"><?php echo html_escape((string) $value($row, 'organization_unit_code', '-')); ?></span><span class="master-mapping-badge master-mapping-mapped">Terhubung</span><?php else: ?><span class="master-directory-note">Belum terhubung ke struktur organisasi</span><span class="master-mapping-badge master-mapping-unmapped">Belum terhubung</span><?php endif; ?></td>
                    <td><?php echo html_escape($format_number($value($row, 'active_staff_count', 0))); ?> <span class="master-row-secondary">staf aktif</span></td>
                    <td><?php if ($mapped): ?><span class="master-status-badge <?php echo $prodi_active ? 'master-status-active' : 'master-status-inactive'; ?>"><?php echo $prodi_active ? 'Aktif' : 'Nonaktif'; ?></span><?php else: ?><span class="text-muted" aria-label="Belum terhubung">—</span><?php endif; ?></td>
                    <td><div class="master-row-actions"><?php if ($mapped): ?><a class="btn btn-sm btn-primary" href="<?php echo html_escape(site_url('profil/prodi/' . (int) $value($row, 'id') . '/staf')); ?>"><i class="fas fa-users" aria-hidden="true"></i> Kelola</a><?php else: ?><a class="btn btn-sm btn-outline-warning" href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf/prodi/link/' . (int) $value($row, 'id'))); ?>"><i class="fas fa-link" aria-hidden="true"></i> Hubungkan</a><?php endif; ?><div class="dropdown master-row-menu"><button type="button" class="btn btn-sm btn-link dropdown-toggle master-row-menu-toggle" data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false" aria-label="Aksi Program Studi">Lainnya</button><div class="dropdown-menu dropdown-menu-right"><a class="btn btn-sm btn-outline-ami" href="<?php echo html_escape(site_url('profil/prodi/edit/' . (int) $value($row, 'id'))); ?>"><i class="fas fa-edit" aria-hidden="true"></i> Edit</a><a class="btn btn-sm btn-outline-ami" href="<?php echo html_escape(site_url('profil/prodi/' . (int) $value($row, 'id') . '/staf')); ?>"><i class="fas fa-users" aria-hidden="true"></i> Lihat Staf</a><div class="dropdown-divider"></div><?php echo form_open('profil/prodi/delete/' . (int) $value($row, 'id')); ?><button type="submit" class="btn btn-sm btn-outline-danger" onclick="return window.confirm('Hapus program studi ini?');">Hapus</button><?php echo form_close(); ?></div></div></div></td>
                </tr>
            <?php endforeach; ?>
            <tr id="master-no-results" class="master-no-results"><td colspan="6"><div class="ami-empty"><div class="ami-empty-icon"><i class="fas fa-search" aria-hidden="true"></i></div><div class="ami-empty-title">Data tidak ditemukan</div><div>Coba ubah kata kunci atau filter direktori.</div></div></td></tr>
        </tbody></table></div>
        <?php if (empty($organization_units) && empty($prodi_directory)): ?><div class="ami-empty"><div class="ami-empty-icon"><i class="fas fa-sitemap" aria-hidden="true"></i></div><div class="ami-empty-title">Belum ada data direktori</div><div>Tambahkan struktur organisasi atau gunakan workflow import/tambah Prodi.</div></div><?php endif; ?>
        <div class="master-pagination" data-master-pagination><span data-master-summary></span><div class="master-pagination-buttons"><button type="button" class="btn btn-sm btn-outline-ami" data-master-prev>Sebelumnya</button><button type="button" class="btn btn-sm btn-outline-ami" data-master-next>Berikutnya</button></div></div>
    </div></section>

    <section class="ami-panel mt-3" aria-labelledby="staff-placement-title"><div class="ami-panel-body"><div class="master-directory-head"><div><h3 id="staff-placement-title">Penempatan Staf Non-Prodi</h3><p>Daftar penempatan aktif pada Fakultas, Biro, Unit, atau Lembaga.</p></div><div class="master-controls"><span class="master-status-badge master-status-active"><?php echo html_escape($format_number($non_prodi_staff_count)); ?> staf aktif</span><a class="btn btn-sm btn-primary" href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf/placement/create')); ?>">Tambah Penempatan</a></div></div><?php if (empty($non_prodi_staff_placements)): ?><div class="ami-empty">Belum ada penempatan staf non-Prodi aktif.</div><?php else: ?><ul class="master-staff-list"><?php foreach ($non_prodi_staff_placements as $placement): ?><li><strong><?php echo html_escape((string) $value($placement, 'nama', '-')); ?></strong> <span class="text-muted"><?php echo html_escape((string) $value($placement, 'position_code', '')); ?> — <?php echo html_escape((string) $value($placement, 'unit_name', '-')); ?> (<?php echo html_escape((string) $value($placement, 'unit_code', '-')); ?>)</span><?php echo form_open('lpmpi/master-data-prodi-staf/placement/end/' . (int) $value($placement, 'id'), ['class' => 'd-inline ml-2']); ?><input type="hidden" name="valid_until" value="<?php echo html_escape(date('Y-m-d')); ?>"><button type="submit" class="btn btn-sm btn-outline-danger" onclick="return window.confirm('Akhiri penempatan ini?');">Akhiri</button><?php echo form_close(); ?></li><?php endforeach; ?></ul><?php endif; ?></div></section>
</main>

<script>
(function () {
    'use strict';
    var root = document.getElementById('master-data-prodi-staf');
    if (!root) return;
    var rows = Array.prototype.slice.call(root.querySelectorAll('[data-master-row]'));
    var search = document.getElementById('master-search');
    var typeFilter = document.getElementById('master-type-filter');
    var statusFilter = document.getElementById('master-status-filter');
    var summary = root.querySelector('[data-master-summary]');
    var prev = root.querySelector('[data-master-prev]');
    var next = root.querySelector('[data-master-next]');
    var empty = document.getElementById('master-no-results');
    var page = 1;
    var pageSize = parseInt(root.getAttribute('data-page-size'), 10) || 10;
    root.addEventListener('click', function (event) {
        var toggle = event.target.closest('[data-toggle="dropdown"]');
        if (!toggle || !root.contains(toggle)) return;
        var dropdown = toggle.closest('.master-row-menu');
        var menu = dropdown && dropdown.querySelector('.dropdown-menu');
        if (!menu) return;
        var toggleRect = toggle.getBoundingClientRect();
        menu.style.visibility = 'hidden';
        menu.style.display = 'grid';
        var menuHeight = menu.getBoundingClientRect().height;
        menu.style.removeProperty('display');
        menu.style.removeProperty('visibility');
        var spaceBelow = window.innerHeight - toggleRect.bottom;
        var spaceAbove = toggleRect.top;
        dropdown.classList.toggle('dropup', spaceBelow < menuHeight && spaceAbove > spaceBelow);
    }, true);
    function render() {
        var query = (search.value || '').toLowerCase().trim();
        var type = (typeFilter.value || '').toLowerCase();
        var status = (statusFilter.value || '').toLowerCase();
        var visible = rows.filter(function (row) { return (!query || (row.getAttribute('data-search') || '').indexOf(query) !== -1) && (!type || row.getAttribute('data-type') === type) && (!status || row.getAttribute('data-status') === status); });
        var pageCount = Math.max(1, Math.ceil(visible.length / pageSize));
        page = Math.min(page, pageCount);
        rows.forEach(function (row) { row.style.display = 'none'; });
        visible.slice((page - 1) * pageSize, page * pageSize).forEach(function (row) { row.style.display = ''; });
        empty.style.display = visible.length ? 'none' : '';
        summary.textContent = visible.length ? 'Menampilkan ' + ((page - 1) * pageSize + 1) + '–' + Math.min(page * pageSize, visible.length) + ' dari ' + visible.length + ' baris' : 'Tidak ada baris yang sesuai';
        prev.disabled = page <= 1;
        next.disabled = page >= pageCount;
    }
    search.addEventListener('input', function () { page = 1; render(); });
    typeFilter.addEventListener('change', function () { page = 1; render(); });
    statusFilter.addEventListener('change', function () { page = 1; render(); });
    prev.addEventListener('click', function () { if (page > 1) { page--; render(); } });
    next.addEventListener('click', function () { if (page < Math.ceil(rows.length / pageSize)) { page++; render(); } });
    render();
}());
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
