<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Daftar Periode Audit</h2>
            <a class="btn-ami btn-outline-ami" href="<?php echo site_url('periode/create'); ?>">
                <i class="fas fa-plus"></i> Tambah periode
            </a>
        </div>

        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Periode</th>
                    <th>Tahun Akademik</th>
                    <th>Semester</th>
                    <th>Tanggal Buka</th>
                    <th>Tanggal Tutup</th>
                    <th>Status</th>
                    <th>Aktif</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($periode)): ?>
                    <?php $no = 1;
                    $hari_ini = date('Y-m-d');
                    foreach ($periode as $row): ?>
                        <?php
                        // Tentukan status berdasarkan tanggal
                        if ($hari_ini < $row->tanggal_buka) {
                            $status_label = 'Akan Datang';
                            $status_class = 'akan_datang';
                            $status_icon = 'fa-clock';
                        } elseif ($hari_ini >= $row->tanggal_buka && $hari_ini <= $row->tanggal_tutup) {
                            $status_label = 'Sedang Berlangsung';
                            $status_class = 'berlangsung';
                            $status_icon = 'fa-play-circle';
                        } else {
                            $status_label = 'Sudah Berakhir';
                            $status_class = 'berakhir';
                            $status_icon = 'fa-check-circle';
                        }
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo html_escape($row->nama_periode); ?></td>
                            <td><?php echo html_escape($row->tahun_akademik); ?></td>
                            <td><?php echo html_escape(ucfirst($row->semester)); ?></td>
                            <td><?php echo html_escape(format_tanggal_indo($row->tanggal_buka)); ?></td>
                            <td><?php echo html_escape(format_tanggal_indo($row->tanggal_tutup)); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo html_escape($status_class); ?>">
                                    <i class="fas <?php echo html_escape($status_icon); ?>" aria-hidden="true"></i>
                                    <?php echo html_escape($status_label); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ((int) $row->is_aktif === 1): ?>
                                    <span class="status-badge status-aktif">
                                        <i class="fas fa-check-circle" aria-hidden="true"></i> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-nonaktif">
                                        <i class="fas fa-circle" aria-hidden="true"></i> Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="ami-row-actions">
                                    <?php echo form_open('periode/toggle_aktif/' . (int) $row->id, ['class' => 'd-inline']); ?>
                                        <button type="submit" class="ami-action-btn <?php echo (int) $row->is_aktif === 1 ? 'warning' : 'success'; ?>" title="<?php echo (int) $row->is_aktif === 1 ? 'Nonaktifkan' : 'Aktifkan'; ?>">
                                            <i class="fas <?php echo (int) $row->is_aktif === 1 ? 'fa-toggle-on' : 'fa-toggle-off'; ?>" aria-hidden="true"></i>
                                        </button>
                                    <?php echo form_close(); ?>

                                    <a href="<?php echo site_url('periode/edit/' . $row->id); ?>" class="ami-action-btn" title="Edit periode">
                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                    </a>

                                    <?php echo form_open('periode/delete/' . (int) $row->id, ['class' => 'd-inline', 'onsubmit' => "return confirm('Yakin ingin menghapus periode &quot;" . html_escape($row->nama_periode) . "&quot;?');"]); ?>
                                        <button type="submit" class="ami-action-btn danger" title="Hapus periode">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    <?php echo form_close(); ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9"><div class="ami-empty">
                            <div class="ami-empty-icon"><i class="fas fa-calendar-alt" aria-hidden="true"></i></div>
                            <div class="ami-empty-title">Belum ada periode audit</div>
                            <div>Buat periode audit terlebih dahulu untuk memulai siklus audit.</div>
                            <a href="<?php echo site_url('periode/create'); ?>" class="btn btn-primary btn-ami"><i class="fas fa-plus" aria-hidden="true"></i> Tambah periode</a>
                        </div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>

