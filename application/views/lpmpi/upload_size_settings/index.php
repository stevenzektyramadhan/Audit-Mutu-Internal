<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$settings = isset($settings) && is_array($settings) ? $settings : [];
$php_ceiling_mib = isset($php_ceiling_mib) ? (int) $php_ceiling_mib : 10;
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="mb-4">
            <h2 class="ami-section-title mb-1">Pengaturan Ukuran Upload</h2>
            <p class="text-muted mb-0">Batas ini dipakai oleh uploader aktif yang sudah terhubung ke pengaturan bersama.</p>
        </div>

        <div class="alert alert-light border small">
            Batas runtime aplikasi tetap maksimal 10 MiB dan otomatis mengikuti ceiling aman PHP saat ini: <?php echo html_escape((string) $php_ceiling_mib); ?> MiB, termasuk 1 MiB headroom multipart dari post_max_size.
        </div>

        <?php echo form_open('lpmpi/upload-size-settings/update'); ?>
            <div class="table-responsive">
                <table class="table ami-table">
                    <thead>
                    <tr>
                        <th>Kategori</th>
                        <th style="width: 220px;">Batas (MiB)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($settings as $category => $setting): ?>
                        <tr>
                            <td>
                                <strong><?php echo html_escape($setting['label']); ?></strong>
                                <div class="text-muted small"><?php echo html_escape($category); ?></div>
                            </td>
                            <td>
                                <input class="form-control" name="<?php echo html_escape($category); ?>" type="number" min="1" max="<?php echo html_escape((string) $php_ceiling_mib); ?>" step="1" value="<?php echo html_escape((string) $setting['limit_mib']); ?>" required>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end border-top pt-3">
                <button class="btn-ami btn-primary" type="submit"><i class="fas fa-save" aria-hidden="true"></i> Simpan pengaturan</button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
