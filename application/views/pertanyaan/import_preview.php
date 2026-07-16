<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$error_rows = [];
foreach ($errors as $error) {
    $error_rows[(int) $error['row']] = TRUE;
}
$error_row_count = count($error_rows);
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-start justify-content-between flex-wrap mb-4" style="gap:12px;">
            <div>
                <h2 class="ami-section-title mb-1">Preview Import Pertanyaan</h2>
                <div class="text-muted"><?php echo html_escape($standar->nama_standar); ?></div>
            </div>
            <a href="<?php echo site_url('pertanyaan?standar_id=' . (int) $standar_id); ?>" class="btn btn-outline-ami btn-ami">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> Batal
            </a>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-2">
                <div class="alert alert-success mb-0">
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <strong><?php echo count($valid); ?></strong> baris siap diimport
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="alert <?php echo $error_row_count > 0 ? 'alert-danger' : 'alert-light'; ?> mb-0">
                    <i class="fas fa-times-circle" aria-hidden="true"></i>
                    <strong><?php echo $error_row_count; ?></strong> baris error
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="alert <?php echo !empty($warnings) ? 'alert-warning' : 'alert-light'; ?> mb-0">
                    <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                    <strong><?php echo count($warnings); ?></strong> warning
                </div>
            </div>
        </div>

        <h3 class="ami-section-title">Data siap diimport</h3>
        <div class="table-responsive mb-4">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Indikator</th>
                    <th>Nilai Standar</th>
                    <th>Kategori</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($valid as $row): ?>
                    <?php
                    $indicator = (string) $row['isi_pertanyaan'];
                    if (function_exists('mb_strimwidth')) {
                        $indicator_preview = mb_strimwidth($indicator, 0, 50, '...');
                    } else {
                        $indicator_preview = strlen($indicator) > 50 ? substr($indicator, 0, 47) . '...' : $indicator;
                    }
                    ?>
                    <tr>
                        <td><?php echo (int) $row['no']; ?></td>
                        <td title="<?php echo html_escape($indicator); ?>"><?php echo html_escape($indicator_preview); ?></td>
                        <td><?php echo html_escape($row['nilai_standar']); ?></td>
                        <td><?php echo html_escape($row['kategori']); ?></td>
                        <td><span class="ami-status tone-green">Siap</span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($errors)): ?>
            <h3 class="ami-section-title">Baris yang dilewati karena error</h3>
            <div class="table-responsive mb-4">
                <table class="table ami-table">
                    <thead>
                    <tr>
                        <th>Baris</th>
                        <th>Kolom Bermasalah</th>
                        <th>Alasan Error</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($errors as $error): ?>
                        <tr>
                            <td><?php echo (int) $error['row']; ?></td>
                            <td><?php echo html_escape($error['column']); ?></td>
                            <td><?php echo html_escape($error['reason']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if (!empty($warnings)): ?>
            <h3 class="ami-section-title">Warning dan koreksi otomatis</h3>
            <div class="table-responsive mb-4">
                <table class="table ami-table">
                    <thead>
                    <tr>
                        <th>Baris</th>
                        <th>Kolom</th>
                        <th>Keterangan</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($warnings as $warning): ?>
                        <tr>
                            <td><?php echo (int) $warning['row']; ?></td>
                            <td><?php echo html_escape($warning['column']); ?></td>
                            <td><?php echo html_escape($warning['reason']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="d-flex align-items-center justify-content-end flex-wrap" style="gap:10px;">
            <a href="<?php echo site_url('pertanyaan?standar_id=' . (int) $standar_id); ?>" class="btn btn-outline-ami btn-ami">Batal</a>
            <?php echo form_open('pertanyaan/import_confirm/' . (int) $standar_id, ['class' => 'd-inline']); ?>
                <input type="hidden" name="import_token" value="<?php echo html_escape($import_token); ?>">
                <button type="submit" class="btn btn-primary btn-ami" data-loading-text="Mengimport data...">
                    <i class="fas fa-check" aria-hidden="true"></i> Konfirmasi Import
                </button>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
