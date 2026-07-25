<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$query = http_build_query(array_filter($filters));
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0"><?php echo ami_e($standar->nama_standar); ?></h2>
            <a href="<?php echo site_url('lpmpi/laporan') . ($query ? '?' . $query : ''); ?>" class="btn-ami btn-outline-ami">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali
            </a>
        </div>

        <div class="table-responsive">
            <table class="table ami-table">
                <thead>
                <tr>
                    <th>Periode</th>
                    <th>Auditee</th>
                    <th>Auditor</th>
                    <th>Pertanyaan</th>
                    <th>Jawaban</th>
                    <th>Skor</th>
                    <th>Temuan</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($detail)): ?>
                    <?php foreach ($detail as $row): ?>
                        <?php $safe_link_bukti = ami_safe_http_url($row->link_bukti ?? ''); ?>
                        <tr>
                            <td><?php echo ami_e($row->nama_periode ?? '-'); ?></td>
                            <td><?php echo ami_e($row->auditee_nama ?? '-'); ?></td>
                            <td><?php echo ami_e($row->auditor_nama ?? '-'); ?></td>
                            <td><?php echo ami_e($row->isi_pertanyaan ?? '-'); ?></td>
                            <td>
                                <div><?php echo ami_e($row->jawaban ?? '-'); ?></div>
                                <?php if ($safe_link_bukti !== ''): ?>
                                    <a href="<?php echo ami_e($safe_link_bukti); ?>" target="_blank" rel="noopener noreferrer">Bukti</a>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo $row->skor === NULL ? '-' : (int) $row->skor; ?></strong></td>
                            <td>
                                <div><?php echo ami_e($row->temuan ?? '-'); ?></div>
                                <?php if (!empty($row->jenis_temuan)): ?>
                                    <span class="text-muted" style="font-size: 12px;"><?php echo ami_e(strtoupper($row->jenis_temuan)); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7"><div class="ami-empty">
                            <div class="ami-empty-icon"><i class="fas fa-clipboard-check" aria-hidden="true"></i></div>
                            <div class="ami-empty-title">Belum ada detail laporan</div>
                            <div>Belum ada jawaban audit untuk standar dan filter ini.</div>
                        </div></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
