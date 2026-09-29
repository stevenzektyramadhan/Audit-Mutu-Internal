<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$allowed_types = ['faculty' => 'Fakultas', 'bureau' => 'Biro', 'unit' => 'Unit', 'institute' => 'Lembaga'];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<main class="master-data-root">
    <div class="master-data-breadcrumb">Beranda / Management / Master Data Organisasi &amp; Staf / Penempatan Non-Prodi</div>
    <section class="ami-panel">
        <div class="ami-panel-body">
            <div class="master-directory-head"><div><div class="ami-eyebrow">Direktori staf</div><h2 class="ami-section-title mb-1"><?php echo html_escape($page_title); ?></h2><p>Tempatkan pengguna aktif pada unit generic non-Prodi yang masih aktif.</p></div><a class="btn btn-outline-ami btn-ami" href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf')); ?>">Kembali</a></div>
            <?php if (validation_errors()): ?><div class="alert alert-danger" role="alert"><?php echo validation_errors(); ?></div><?php endif; ?>
            <?php echo form_open($action); ?>
                <div class="form-group"><label for="placement-user">Pengguna / Staf</label><select class="form-control" id="placement-user" name="user_id" required><option value="">Pilih pengguna...</option><?php foreach ($users as $user): ?><option value="<?php echo (int) $user->id; ?>" <?php echo set_select('user_id', $user->id); ?>><?php echo html_escape($user->nama . ' (' . $user->email . ')'); ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label for="placement-unit">Unit Non-Prodi Aktif</label><select class="form-control" id="placement-unit" name="organization_unit_id" required><option value="">Pilih unit aktif...</option><?php foreach ($units as $unit): ?><?php if ((int) $unit->is_active === 1 && isset($allowed_types[$unit->type])): ?><option value="<?php echo (int) $unit->id; ?>" <?php echo set_select('organization_unit_id', $unit->id); ?>><?php echo html_escape($allowed_types[$unit->type] . ' — ' . $unit->code . ' — ' . $unit->name); ?></option><?php endif; ?><?php endforeach; ?></select><small class="form-text text-muted">Program Studi, UPPS, Universitas, dan unit nonaktif tidak tersedia untuk penempatan non-Prodi.</small></div>
                <div class="form-row"><div class="form-group col-md-6"><label for="placement-position">Kode Jabatan / Posisi</label><input class="form-control" id="placement-position" name="position_code" maxlength="64" value="<?php echo html_escape(set_value('position_code')); ?>" required></div><div class="form-group col-md-3"><label for="placement-from">Berlaku Mulai</label><input class="form-control" type="date" id="placement-from" name="valid_from" value="<?php echo html_escape(set_value('valid_from', date('Y-m-d'))); ?>" required></div><div class="form-group col-md-3"><label for="placement-until">Berlaku Sampai</label><input class="form-control" type="date" id="placement-until" name="valid_until" value="<?php echo html_escape(set_value('valid_until')); ?>"></div></div>
                <div class="form-group"><div class="custom-control custom-checkbox"><input class="custom-control-input" type="checkbox" id="placement-primary" name="is_primary" value="1" <?php echo set_checkbox('is_primary', '1'); ?>><label class="custom-control-label" for="placement-primary">Jadikan penempatan utama</label></div></div>
                <div class="d-flex justify-content-end flex-wrap" style="gap: var(--ami-space-sm);"><a class="btn btn-outline-ami btn-ami" href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf')); ?>">Batal</a><button type="submit" class="btn btn-primary btn-ami">Simpan Penempatan</button></div>
            <?php echo form_close(); ?>
        </div>
    </section>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
