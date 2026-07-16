<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$tones = ['tone-violet', 'tone-green', 'tone-amber', 'tone-blue', 'tone-rose', 'tone-teal'];
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap mb-4" style="gap:12px;">
            <h2 class="ami-section-title m-0">Daftar pertanyaan audit</h2>
            <div class="ami-actions">
                <a class="btn-ami btn-outline-ami" href="<?php echo site_url('pertanyaan/create'); ?>">
                    <i class="fas fa-plus"></i> Tambah pertanyaan
                </a>
                <?php if ($standar_id !== NULL): ?>
                    <a href="<?php echo site_url('pertanyaan/download_template/' . (int) $standar_id); ?>" class="btn btn-outline-success btn-sm btn-ami">
                        <i class="fas fa-file-excel" aria-hidden="true"></i> Download Template Excel
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-sm btn-ami" data-toggle="modal" data-target="#modalImport">
                        <i class="fas fa-upload" aria-hidden="true"></i> Import dari Excel
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-outline-success btn-sm btn-ami" disabled title="Pilih satu standar terlebih dahulu">
                        <i class="fas fa-file-excel" aria-hidden="true"></i> Download Template Excel
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm btn-ami" disabled title="Pilih satu standar terlebih dahulu">
                        <i class="fas fa-upload" aria-hidden="true"></i> Import dari Excel
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($standar_id === NULL): ?>
            <div class="alert alert-info">
                Pilih satu standar pada filter untuk mengaktifkan download template dan import Excel.
            </div>
        <?php endif; ?>

        <form method="get" action="<?php echo site_url('pertanyaan'); ?>" class="ami-actions align-items-end mb-3">
            <div style="width:280px;max-width:100%;">
                <label for="standar-filter" class="ami-stat-label">Filter standar</label>
                <select id="standar-filter" name="standar_id" class="form-control">
                    <option value="">Semua standar</option>
                    <?php foreach ($standar as $std): ?>
                        <option value="<?php echo (int) $std->id; ?>" <?php echo (int) $filter_standar_id === (int) $std->id ? 'selected' : ''; ?>>
                            <?php echo html_escape($std->nama_standar); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-ami"><i class="fas fa-filter" aria-hidden="true"></i>Terapkan</button>
            <?php if ($filter_standar_id !== NULL): ?>
                <a href="<?php echo site_url('pertanyaan'); ?>" class="btn btn-outline-ami btn-ami">Reset</a>
            <?php endif; ?>
        </form>

        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Standar</th>
                    <th>Isi pertanyaan</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($pertanyaan)): ?>
                    <?php $no = 1; foreach ($pertanyaan as $row): ?>
                        <?php 
                            $standar_badge = str_replace('Standar ', '', $row->nama_standar ?? '');
                            $tone_class = $tones[($row->standar_id ?? 0) % count($tones)];
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td>
                                <?php if (!empty($row->nama_standar)): ?>
                                    <span class="ami-status <?php echo $tone_class; ?>">
                                        <?php echo html_escape($standar_badge); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width: 350px;">
                                <?php echo html_escape($row->isi_pertanyaan); ?>
                            </td>
                            <td>
                                <?php echo html_escape(format_tanggal_indo($row->created_at)); ?>
                            </td>
                            <td>
                                <div class="ami-row-actions">
                                    <a href="<?php echo site_url('pertanyaan/edit/'.$row->id); ?>" class="ami-action-btn" title="Edit pertanyaan">
                                        <i class="fas fa-edit" aria-hidden="true"></i><span>Edit</span>
                                    </a>
                                    <?php echo form_open('pertanyaan/delete/' . (int) $row->id, ['class' => 'd-inline', 'onsubmit' => "return confirm('Apakah Anda yakin ingin menghapus pertanyaan ini?');"]); ?>
                                        <button type="submit" class="ami-action-btn danger" title="Hapus pertanyaan">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i><span>Hapus</span>
                                        </button>
                                    <?php echo form_close(); ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5"><div class="ami-empty">
                            <div class="ami-empty-icon"><i class="fas fa-question-circle" aria-hidden="true"></i></div>
                            <div class="ami-empty-title">Pertanyaan tidak ditemukan</div>
                            <div><?php echo $filter_standar_id !== NULL ? 'Belum ada pertanyaan pada standar yang dipilih.' : 'Tambahkan pertanyaan audit berdasarkan standar.'; ?></div>
                            <?php if ($filter_standar_id !== NULL): ?>
                                <a href="<?php echo site_url('pertanyaan'); ?>" class="btn btn-outline-ami btn-ami"><i class="fas fa-undo" aria-hidden="true"></i>Reset filter</a>
                            <?php else: ?>
                                <a href="<?php echo site_url('pertanyaan/create'); ?>" class="btn btn-primary btn-ami"><i class="fas fa-plus" aria-hidden="true"></i>Tambah pertanyaan</a>
                            <?php endif; ?>
                        </div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!empty($pertanyaan)): ?>
        <div class="mt-3 text-muted" style="font-size: 13px;">
            Menampilkan <?php echo count($pertanyaan); ?> pertanyaan
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($standar_id !== NULL): ?>
<div class="modal fade" id="modalImport" tabindex="-1" role="dialog" aria-labelledby="modalImportLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open_multipart('pertanyaan/import/' . (int) $standar_id); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImportLabel">Import Pertanyaan dari Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        Upload file Excel (.xlsx atau .xls) sesuai format template, maksimal 2 MB.
                        <a href="<?php echo site_url('pertanyaan/download_template/' . (int) $standar_id); ?>">Download template di sini</a>.
                    </p>
                    <div class="form-group mb-0">
                        <label for="file-excel">File Excel <span class="text-danger">*</span></label>
                        <input type="file" class="form-control-file" id="file-excel" name="file_excel" accept=".xlsx,.xls" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" data-loading-text="Memproses file...">
                        <i class="fas fa-search" aria-hidden="true"></i> Preview Import
                    </button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
