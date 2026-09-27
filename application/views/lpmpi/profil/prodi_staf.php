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
    </div>
</div>

<?php foreach (['active' => ['title' => 'Staf Aktif', 'rows' => $active_staf], 'inactive' => ['title' => 'Staf Tidak Aktif', 'rows' => $inactive_staf]] as $status => $section): ?>
    <div class="ami-panel mb-3">
        <div class="ami-panel-body">
            <h3 class="h5 mb-3"><?php echo html_escape($section['title']); ?></h3>
            <div class="table-responsive"><table class="table ami-table"><thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Jabatan</th><th>Aksi</th></tr></thead><tbody>
            <?php if (empty($section['rows'])): ?>
                <tr><td colspan="5">Belum ada staf.</td></tr>
            <?php else: foreach ($section['rows'] as $staf): ?>
                <tr>
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
            </tbody></table></div>
        </div>
    </div>
<?php endforeach; ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
