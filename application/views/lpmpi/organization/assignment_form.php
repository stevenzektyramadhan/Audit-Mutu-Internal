<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<path d="m15 18-6-6 6-6"/>',
        'user-check' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M16 11l2 2 4-4"/>',
    ];
    return '<svg class="org-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['user-check']) . '</svg>';
};
?>

<div id="organization-root" class="tw-mx-auto tw-max-w-screen-2xl">
    <section class="org-surface form-shell">
        <div class="org-surface-body">
            <div class="form-header">
                <a href="<?php echo site_url('lpmpi/organization'); ?>" class="org-back-link">
                    <?php echo $icon('arrow-left'); ?> Kembali ke Struktur Organisasi
                </a>
                <p class="org-eyebrow">Penempatan Pejabat / Pengguna</p>
                <h2 class="tw-text-2xl tw-font-bold tw-m-0 tw-text-slate-800">Tambah Penempatan Baru</h2>
                <p class="form-subtitle">Tetapkan pengguna ke unit kerja tertentu dengan masa jabatan dan penanda penempatan utama.</p>
            </div>

            <?php if (validation_errors()): ?>
                <div class="org-error"><?php echo validation_errors(); ?></div>
            <?php endif; ?>

            <?php echo form_open('lpmpi/organization/assignment/store'); ?>
                <div class="form-section">
                    <div class="form-section-heading">
                        <?php echo $icon('user-check'); ?>
                        <span>
                            <strong>Data Penugasan Unit</strong>
                            <small>Pilih pengguna dan unit tujuan penempatan.</small>
                        </span>
                    </div>

                    <div class="tw-space-y-4">
                        <div>
                            <label for="user_id" class="org-label">Pengguna / Pegawai</label>
                            <select class="org-control" id="user_id" name="user_id" required>
                                <option value="">Pilih pengguna...</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo (int) $user->id; ?>" <?php echo set_select('user_id', $user->id); ?>>
                                        <?php echo html_escape($user->nama . ' (' . $user->email . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="organization_unit_id" class="org-label">Unit Kerja (Hanya Unit Aktif)</label>
                            <select class="org-control" id="organization_unit_id" name="organization_unit_id" required>
                                <option value="">Pilih unit aktif...</option>
                                <?php foreach ($units as $unit): ?>
                                    <?php if ((int) $unit->is_active === 1): ?>
                                        <option value="<?php echo (int) $unit->id; ?>" <?php echo set_select('organization_unit_id', $unit->id); ?>>
                                            <?php echo html_escape($unit->code . ' - ' . $unit->name); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="position_code" class="org-label">Kode Jabatan / Posisi</label>
                            <input class="org-control tw-font-mono" id="position_code" name="position_code" maxlength="64"
                                   placeholder="Contoh: DEKAN, KAPRODI, SEKRETARIS, STAFF"
                                   value="<?php echo html_escape(set_value('position_code')); ?>" required>
                        </div>

                        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                            <div>
                                <label for="valid_from" class="org-label">Berlaku Mulai</label>
                                <input class="org-control" type="date" id="valid_from" name="valid_from"
                                       value="<?php echo html_escape(set_value('valid_from', date('Y-m-d'))); ?>" required>
                            </div>
                            <div>
                                <label for="valid_until" class="org-label">Berlaku Sampai (Opsional)</label>
                                <input class="org-control" type="date" id="valid_until" name="valid_until"
                                       value="<?php echo html_escape(set_value('valid_until')); ?>">
                                <span class="org-muted tw-text-[11px] tw-mt-1 tw-block">Kosongkan jika masa jabatan belum ditentukan.</span>
                            </div>
                        </div>

                        <div class="tw-pt-2">
                            <label class="tw-flex tw-items-center tw-gap-3 tw-cursor-pointer tw-p-2 tw-border tw-border-slate-200 tw-rounded-md hover:tw-bg-slate-50">
                                <input type="checkbox" id="is_primary" name="is_primary" value="1"
                                       <?php echo set_checkbox('is_primary', '1'); ?>
                                       class="tw-rounded tw-text-blue-600">
                                <div>
                                    <strong class="tw-text-xs tw-text-slate-800 tw-block">Jadikan Penempatan Utama</strong>
                                    <span class="org-muted tw-text-[11px]">Jika dicentang, penempatan utama aktif lainnya untuk pengguna ini akan disesuaikan.</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-end tw-gap-2 tw-mt-6">
                    <a class="org-button org-button-secondary tw-justify-center" href="<?php echo site_url('lpmpi/organization'); ?>">
                        Batal
                    </a>
                    <button class="org-button org-button-primary tw-justify-center" type="submit">
                        Simpan Penempatan
                    </button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </section>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
