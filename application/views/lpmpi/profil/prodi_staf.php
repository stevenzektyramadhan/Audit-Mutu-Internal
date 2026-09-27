<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel mb-3">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap" style="gap: 12px;">
            <div>
                <h2 class="ami-section-title m-0"><?php echo html_escape($page_title); ?></h2>
                <div class="text-muted mt-1"><?php echo html_escape($prodi->nama_prodi ?: '-'); ?><?php echo !empty($prodi->kode_prodi) ? ' (' . html_escape($prodi->kode_prodi) . ')' : ''; ?></div>
            </div>
            <a class="btn btn-outline-ami btn-ami" href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf')); ?>">Kembali</a>
        </div>

        <h3 class="h5 mb-3">Tambah Staf Existing</h3>
        <?php echo form_open('profil/prodi/' . (int) $prodi->id . '/staf/add'); ?>
         <div class="form-row align-items-end">
                <div class="form-group col-md-6">
                    <label for="staf-account">Akun auditor atau auditee</label>
                    <select class="form-control" id="staf-account" name="id_akun" required>
                        <option value="">Pilih akun</option>
                        <?php foreach ($eligible_users as $user): ?>
                            <option value="<?php echo (int) $user->id; ?>"><?php echo html_escape($user->nama . ' — ' . $user->email . ' (' . $user->role . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="staf-jabatan">Jabatan</label>
                    <input class="form-control" id="staf-jabatan" name="jabatan" maxlength="100">
                </div>
                <div class="form-group col-md-2"><button type="submit" class="btn btn-primary btn-ami btn-block">Tambah</button></div>
            </div>
         <?php echo form_close(); ?>
         <div class="form-group mb-0 mt-3">
             <label for="prodi-staf-search">Cari staf</label>
             <input class="form-control" id="prodi-staf-search" data-roster-search type="search" placeholder="Nama, email, role, atau jabatan" autocomplete="off">
         </div>
     </div>
 </div>

<?php foreach (['active' => ['title' => 'Staf Aktif', 'rows' => $active_staf], 'inactive' => ['title' => 'Staf Tidak Aktif', 'rows' => $inactive_staf]] as $status => $section): ?>
    <div class="ami-panel mb-3" data-roster-section="<?php echo html_escape($status); ?>" data-roster-page-size="10">
        <div class="ami-panel-body">
            <h3 class="h5 mb-3"><?php echo html_escape($section['title']); ?></h3>
            <div class="table-responsive"><table class="table ami-table"><thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Jabatan</th><th>Aksi</th></tr></thead><tbody>
            <?php if (empty($section['rows'])): ?>
                <tr data-roster-source-empty><td colspan="5">Belum ada staf.</td></tr>
            <?php else: foreach ($section['rows'] as $staf): ?>
                <tr data-roster-row data-roster-search="<?php echo html_escape(mb_strtolower($staf->nama . ' ' . $staf->email . ' ' . $staf->role . ' ' . $staf->jabatan, 'UTF-8')); ?>">
                    <td><?php echo html_escape($staf->nama); ?></td><td><?php echo html_escape($staf->email); ?></td><td><?php echo html_escape($staf->role); ?></td>
                    <td>
                        <?php echo form_open('profil/prodi/' . (int) $prodi->id . '/staf/update/' . (int) $staf->id, ['class' => 'd-flex', 'style' => 'gap:6px;']); ?>
                            <input class="form-control form-control-sm" name="jabatan" maxlength="100" value="<?php echo html_escape($staf->jabatan); ?>"><button type="submit" class="btn btn-sm btn-outline-ami">Simpan</button>
                        <?php echo form_close(); ?>
                    </td>
                    <td><div class="d-flex flex-wrap" style="gap:6px;">
                        <?php if ($status === 'active'): ?>
                            <?php echo form_open('profil/prodi/' . (int) $prodi->id . '/staf/move/' . (int) $staf->id, ['class' => 'd-flex', 'style' => 'gap:6px;']); ?>
                                <select class="form-control form-control-sm" name="target_prodi_id" required><option value="">Pindah ke</option><?php foreach ($target_prodi as $target): ?><option value="<?php echo (int) $target->id; ?>"><?php echo html_escape($target->nama_prodi ?: $target->kode_prodi); ?></option><?php endforeach; ?></select><button type="submit" class="btn btn-sm btn-outline-ami">Pindah</button>
                            <?php echo form_close(); ?>
                            <?php echo form_open('profil/prodi/' . (int) $prodi->id . '/staf/deactivate/' . (int) $staf->id); ?><button type="submit" class="btn btn-sm btn-outline-danger">Nonaktifkan</button><?php echo form_close(); ?>
                        <?php else: ?>
                            <?php echo form_open('profil/prodi/' . (int) $prodi->id . '/staf/reactivate/' . (int) $staf->id); ?><button type="submit" class="btn btn-sm btn-outline-ami">Aktifkan kembali</button><?php echo form_close(); ?>
                        <?php endif; ?>
                    </div></td>
                </tr>
            <?php endforeach; endif; ?>
            <tr data-roster-no-results hidden><td colspan="5">Tidak ada staf yang cocok dengan pencarian.</td></tr>
            </tbody></table></div>
            <div class="d-flex align-items-center justify-content-between flex-wrap mt-2" style="gap: 6px;" data-roster-controls>
                <span class="text-muted small" data-roster-summary></span>
                <div class="d-flex" style="gap: 6px;"><button type="button" class="btn btn-sm btn-outline-ami" data-roster-prev>Sebelumnya</button><button type="button" class="btn btn-sm btn-outline-ami" data-roster-next>Berikutnya</button></div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

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
