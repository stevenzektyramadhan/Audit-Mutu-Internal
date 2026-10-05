<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$allowed_types = ['faculty' => 'Fakultas', 'bureau' => 'Biro', 'unit' => 'Unit', 'institute' => 'Lembaga'];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<style>
    .placement-create-root { max-width: 860px; margin: 0 auto; }
    .placement-section { border: 1px solid var(--ami-border); border-radius: 12px; padding: 20px; margin-bottom: 20px; background: #fafbfc; }
    .placement-section-title { font-size: 14.5px; font-weight: 700; color: var(--ami-text); margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
    .placement-create-root .form-control { height: 44px; border-radius: 8px; border: 1px solid var(--ami-border); font-size: 13.5px; }
    .placement-create-root select.form-control { height: 44px; }
    .placement-create-root .form-control:focus { border-color: var(--ami-link); box-shadow: 0 0 0 .2rem var(--ami-link-soft); outline: none; }
    .placement-checkbox-card { border: 1px solid var(--ami-border); border-radius: 8px; padding: 14px 16px; background: #ffffff; cursor: pointer; transition: background .15s ease, border-color .15s ease; }
    .placement-checkbox-card:hover { border-color: #cbd5e1; background: #f8fafc; }
</style>

<main class="master-data-root placement-create-root">
    <div class="master-data-breadcrumb">Beranda / Management / Master Data Organisasi &amp; Staf / Penempatan Non-Prodi</div>
    <section class="ami-panel">
        <div class="ami-panel-body">
            <div class="master-directory-head mb-4">
                <div>
                    <div class="ami-eyebrow">Direktori staf</div>
                    <h2 class="ami-section-title mb-1"><?php echo html_escape($page_title); ?></h2>
                    <p class="text-muted" style="font-size: 13.5px;">Tempatkan pengguna aktif pada unit generic non-Prodi yang masih aktif.</p>
                </div>
                <a class="btn btn-outline-ami btn-ami" href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf')); ?>">Kembali</a>
            </div>

            <?php if (validation_errors()): ?><div class="alert alert-danger" role="alert"><?php echo validation_errors(); ?></div><?php endif; ?>

            <?php echo form_open($action); ?>
                <div class="placement-section">
                    <div class="placement-section-title"><i class="fas fa-user-tag text-primary" aria-hidden="true"></i> Subjek Penempatan &amp; Unit</div>
                    <div class="form-group mb-3">
                        <label for="placement-user" class="font-weight-bold">Pengguna / Staf</label>
                        <select class="form-control" id="placement-user" name="user_id" required>
                            <option value="">Pilih pengguna...</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo (int) $user->id; ?>" <?php echo set_select('user_id', $user->id); ?>><?php echo html_escape($user->nama . ' (' . $user->email . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label for="placement-unit" class="font-weight-bold">Unit Non-Prodi Aktif</label>
                        <select class="form-control" id="placement-unit" name="organization_unit_id" required>
                            <option value="">Pilih unit aktif...</option>
                            <?php foreach ($units as $unit): ?>
                                <?php if ((int) $unit->is_active === 1 && isset($allowed_types[$unit->type])): ?>
                                    <option value="<?php echo (int) $unit->id; ?>" <?php echo set_select('organization_unit_id', $unit->id); ?>><?php echo html_escape($allowed_types[$unit->type] . ' — ' . $unit->code . ' — ' . $unit->name); ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted mt-2">Program Studi, UPPS, Universitas, dan unit nonaktif tidak tersedia untuk penempatan non-Prodi.</small>
                    </div>
                </div>

                <div class="placement-section">
                    <div class="placement-section-title"><i class="fas fa-briefcase text-primary" aria-hidden="true"></i> Informasi Posisi &amp; Periode Masa Tugas</div>
                    <div class="form-group mb-3">
                        <label for="placement-position" class="font-weight-bold">Kode Jabatan / Posisi</label>
                        <input class="form-control" id="placement-position" name="position_code" maxlength="64" value="<?php echo html_escape(set_value('position_code')); ?>" placeholder="Contoh: KEPALA_BIRO, STAF_ADMIN, KETUA_LEMBAGA" required>
                    </div>
                    <div class="form-row mb-0">
                        <div class="form-group col-md-6 mb-0">
                            <label for="placement-from" class="font-weight-bold">Berlaku Mulai</label>
                            <input class="form-control" type="date" id="placement-from" name="valid_from" value="<?php echo html_escape(set_value('valid_from', date('Y-m-d'))); ?>" required>
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label for="placement-until" class="font-weight-bold">Berlaku Sampai <span class="text-muted font-weight-normal">(Opsional)</span></label>
                            <input class="form-control" type="date" id="placement-until" name="valid_until" value="<?php echo html_escape(set_value('valid_until')); ?>">
                        </div>
                    </div>
                </div>

                <div class="placement-section">
                    <div class="placement-section-title"><i class="fas fa-sliders-h text-primary" aria-hidden="true"></i> Opsi Penempatan</div>
                    <div class="placement-checkbox-card">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" id="placement-primary" name="is_primary" value="1" <?php echo set_checkbox('is_primary', '1'); ?>>
                            <label class="custom-control-label font-weight-bold text-slate-800" for="placement-primary">Jadikan penempatan utama</label>
                            <div class="text-muted small mt-1 ml-0">Menjadikan unit ini sebagai afiliasi struktural primer bagi pengguna yang bersangkutan.</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end flex-wrap mt-4" style="gap: var(--ami-space-sm);">
                    <a class="btn btn-outline-ami btn-ami" href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf')); ?>">Batal</a>
                    <button type="submit" class="btn btn-primary btn-ami">Simpan Penempatan</button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </section>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
