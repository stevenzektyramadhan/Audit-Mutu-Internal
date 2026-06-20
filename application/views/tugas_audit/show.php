<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$jawaban = isset($jawaban) ? $jawaban : [];
$status_labels = [
    STATUS_BELUM_DIISI => 'Belum diisi',
    STATUS_DIISI => 'Sudah diisi',
    STATUS_DINILAI => 'Sudah dinilai',
];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel mb-3">
    <div class="ami-panel-body">
        <div class="row">
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="ami-stat-label">Auditee</div>
                <div class="font-weight-bold"><?php echo html_escape($tugas->auditee_nama); ?></div>
                <div class="text-muted small"><?php echo html_escape($tugas->auditee_email); ?></div>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="ami-stat-label">Auditor</div>
                <div class="font-weight-bold"><?php echo html_escape($tugas->auditor_nama); ?></div>
                <div class="text-muted small"><?php echo html_escape($tugas->auditor_email); ?></div>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="ami-stat-label">Standar</div>
                <div class="font-weight-bold"><?php echo html_escape($tugas->nama_standar); ?></div>
            </div>
            <div class="col-md-3">
                <div class="ami-stat-label">Status</div>
                <span class="ami-status status-<?php echo html_escape($tugas->status); ?>">
                    <?php echo html_escape(isset($status_labels[$tugas->status]) ? $status_labels[$tugas->status] : $tugas->status); ?>
                </span>
            </div>
        </div>

        <?php if (!empty($tugas->standar_deskripsi)): ?>
            <div class="mt-3 pt-3" style="border-top:1px solid var(--ami-border);">
                <div class="ami-stat-label">Deskripsi standar</div>
                <div><?php echo nl2br(html_escape($tugas->standar_deskripsi)); ?></div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($rata_rata !== NULL): ?>
    <div class="ami-panel mb-3">
        <div class="ami-panel-body d-flex align-items-center justify-content-between">
            <div>
                <div class="font-weight-bold">Ringkasan penilaian</div>
                <div class="text-muted small">Rata-rata dari <?php echo html_escape((string) count($jawaban)); ?> pertanyaan audit.</div>
            </div>
            <div class="ami-stat-value"><?php echo html_escape(number_format((float) $rata_rata, 1)); ?> / 4</div>
        </div>
    </div>
<?php endif; ?>

<div class="ami-section-head">
    <h2 class="ami-section-title">Jawaban audit</h2>
</div>

<?php if (!empty($jawaban)): ?>
    <?php foreach ($jawaban as $index => $item): ?>
        <div class="ami-panel mb-3">
            <div class="ami-panel-body">
                <div class="d-flex align-items-start mb-3">
                    <span class="ami-nav-badge mr-2" style="margin-left:0;"><?php echo (int) $index + 1; ?></span>
                    <div class="font-weight-bold"><?php echo html_escape($item->isi_pertanyaan); ?></div>
                </div>

                <div class="row">
                    <div class="col-lg-7 mb-3 mb-lg-0">
                        <div class="ami-stat-label">Jawaban auditee</div>
                        <div class="mb-2"><?php echo nl2br(html_escape($item->jawaban ?: 'Belum diisi.')); ?></div>
                        <?php if (!empty($item->link_bukti)): ?>
                            <a href="<?php echo html_escape($item->link_bukti); ?>" target="_blank" rel="noopener noreferrer">
                                <i class="fas fa-external-link-alt mr-1" aria-hidden="true"></i>Lihat bukti dokumen
                            </a>
                        <?php else: ?>
                            <span class="text-muted">Link bukti belum tersedia.</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-5">
                        <div class="ami-stat-label">Skor auditor</div>
                        <div class="font-weight-bold mb-2">
                            <?php echo $item->skor !== NULL ? html_escape((string) $item->skor) . ' / 4' : 'Belum dinilai'; ?>
                        </div>
                        <div class="ami-stat-label">Catatan atau temuan</div>
                        <div><?php echo nl2br(html_escape($item->catatan ?: 'Belum ada catatan.')); ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="ami-panel"><div class="ami-empty">Belum ada pertanyaan pada tugas audit ini.</div></div>
<?php endif; ?>

<div class="ami-actions justify-content-end mt-3">
    <a href="<?php echo site_url('tugas_audit'); ?>" class="btn btn-outline-ami btn-ami">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>Kembali
    </a>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
