<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel mb-4">
    <div class="ami-panel-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between" style="gap: 16px;">
            <div>
                <div class="ami-stat-label">Pengguna</div>
                <h2 class="ami-section-title mb-1"><?php echo ami_e($user->nama); ?></h2>
                <div class="text-muted">
                    <?php echo ami_e($user->email); ?> · <?php echo ami_e($user->role); ?>
                </div>
                <?php if (!empty($user->nama_unit) || !empty($user->jenis_unit)): ?>
                    <div class="mt-2 text-muted" style="font-size: 13px;">
                        Data unit lama: <?php echo ami_e($user->nama_unit ?: '-'); ?>
                        (<?php echo ami_e($user->jenis_unit ?: '-'); ?>).
                        Nilai ini hanya untuk kompatibilitas; otorisasi memakai assignment aktif di bawah.
                    </div>
                <?php endif; ?>
            </div>
            <div class="d-flex flex-wrap" style="gap: 8px;">
                <a href="<?php echo site_url('user-unit-assignments/' . (int) $user->id . '/create'); ?>" class="btn btn-primary btn-ami">
                    <i class="fas fa-plus" aria-hidden="true"></i> Tambah Assignment
                </a>
                <a href="<?php echo site_url($this->session->userdata('role') === 'super_admin' ? 'users' : 'lpmpi/akun'); ?>" class="btn btn-outline-ami btn-ami">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>Unit</th>
                    <th>Jabatan</th>
                    <th>Periode</th>
                    <th>Primary</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($assignments)): ?>
                    <?php foreach ($assignments as $assignment): ?>
                        <?php
                        if ((string) $assignment->valid_from > $today) {
                            $status = 'Akan datang';
                            $tone = 'tone-blue';
                        } elseif ($assignment->valid_until !== NULL
                            && (string) $assignment->valid_until < $today) {
                            $status = 'Berakhir';
                            $tone = 'tone-amber';
                        } elseif ((int) $assignment->organization_unit_active !== 1
                            || (int) $assignment->user_active !== 1) {
                            $status = 'Tidak aktif';
                            $tone = 'tone-amber';
                        } else {
                            $status = 'Aktif';
                            $tone = 'tone-green';
                        }
                        $can_end = $assignment->valid_until === NULL
                            || (string) $assignment->valid_until >= $today;
                        $minimum_end = max($today, (string) $assignment->valid_from);
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo ami_e($assignment->organization_unit_name); ?></strong>
                                <div class="text-muted" style="font-size: 12px;">
                                    <?php echo ami_e($assignment->organization_unit_code); ?>
                                    · <?php echo ami_e($assignment->organization_unit_type); ?>
                                </div>
                            </td>
                            <td><code><?php echo ami_e($assignment->position_code); ?></code></td>
                            <td>
                                <?php echo ami_e($assignment->valid_from); ?>
                                s.d.
                                <?php echo ami_e($assignment->valid_until ?: 'seterusnya'); ?>
                            </td>
                            <td>
                                <?php if ((int) $assignment->is_primary === 1): ?>
                                    <span class="ami-status tone-violet">Primary</span>
                                <?php else: ?>
                                    <span class="text-muted">Tambahan</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="ami-status <?php echo ami_e($tone); ?>"><?php echo ami_e($status); ?></span></td>
                            <td>
                                <?php if ($can_end): ?>
                                    <?php echo form_open(
                                        'user-unit-assignments/end/' . (int) $assignment->id,
                                        ['class' => 'd-flex align-items-center', 'style' => 'gap: 6px;']
                                    ); ?>
                                        <input
                                            type="date"
                                            name="valid_until"
                                            class="form-control form-control-sm"
                                            min="<?php echo ami_e($minimum_end); ?>"
                                            max="<?php echo ami_e($assignment->valid_until ?: '9999-12-31'); ?>"
                                            value="<?php echo ami_e($assignment->valid_until ?: $minimum_end); ?>"
                                            required
                                            aria-label="Tanggal akhir assignment"
                                        >
                                        <button type="submit" class="ami-action-btn danger" data-confirm-assignment-end>
                                            <i class="fas fa-calendar-times" aria-hidden="true"></i><span>Akhiri</span>
                                        </button>
                                    <?php echo form_close(); ?>
                                <?php else: ?>
                                    <span class="text-muted">Riwayat</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="ami-empty">
                                <div class="ami-empty-icon"><i class="fas fa-id-badge" aria-hidden="true"></i></div>
                                <div class="ami-empty-title">Belum ada assignment</div>
                                <div>Tambahkan unit, kode jabatan, dan periode berlaku untuk pengguna ini.</div>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3 text-muted" style="font-size: 13px;">
            Assignment lama tidak dihapus. Perubahan dilakukan dengan mengakhiri masa berlakunya.
        </div>
    </div>
</div>

<script nonce="<?php echo ami_csp_nonce(); ?>">
document.querySelectorAll('[data-confirm-assignment-end]').forEach(function (button) {
    button.addEventListener('click', function (event) {
        if (!window.confirm('Akhiri masa berlaku assignment ini? Riwayat tetap disimpan.')) {
            event.preventDefault();
        }
    });
});
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
