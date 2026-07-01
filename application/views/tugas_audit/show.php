<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$jawaban = isset($jawaban) ? $jawaban : [];
$status_meta = status_audit_meta($tugas->status);
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
                <span class="ami-status <?php echo html_escape($status_meta['tone']); ?>">
                    <?php echo html_escape($status_meta['label']); ?>
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

<?php
$status_order = [STATUS_BELUM_DIISI, STATUS_DIISI, STATUS_DINILAI];
$current_status_index = array_search($tugas->status, $status_order, TRUE);
$current_status_index = $current_status_index === FALSE ? 0 : $current_status_index;
$timeline_steps = [
    ['label' => 'Tugas dibuat', 'icon' => 'fa-clipboard-list'],
    ['label' => 'Diisi auditee', 'icon' => 'fa-pen'],
    ['label' => 'Dinilai auditor', 'icon' => 'fa-star'],
];
?>
<div class="ami-panel mb-3">
    <div class="ami-panel-body">
        <div class="ami-stat-label mb-2">Perjalanan status tugas</div>
        <div class="ami-timeline" aria-label="Status tugas audit">
            <?php foreach ($timeline_steps as $index => $step): ?>
                <?php
                $step_class = $index < $current_status_index ? 'is-complete' : ($index === $current_status_index ? 'is-current' : '');
                ?>
                <div class="ami-timeline-step <?php echo $step_class; ?>"<?php echo $index === $current_status_index ? ' aria-current="step"' : ''; ?>>
                    <div class="ami-timeline-marker">
                        <i class="fas <?php echo html_escape($step['icon']); ?>" aria-hidden="true"></i>
                    </div>
                    <div class="ami-timeline-label"><?php echo html_escape($step['label']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
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
                        <div><?php echo nl2br(html_escape($item->temuan ?: 'Belum ada catatan.')); ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="ami-panel"><div class="ami-empty">
        <div class="ami-empty-icon"><i class="fas fa-question-circle" aria-hidden="true"></i></div>
        <div class="ami-empty-title">Belum ada pertanyaan</div>
        <div>Tambahkan pertanyaan pada standar ini agar tugas dapat diisi.</div>
        <a href="<?php echo site_url('pertanyaan/create'); ?>" class="btn btn-primary btn-ami"><i class="fas fa-plus" aria-hidden="true"></i>Tambah pertanyaan</a>
    </div></div>
<?php endif; ?>

<div class="ami-actions justify-content-end mt-3">
    <a href="<?php echo site_url('tugas_audit'); ?>" class="btn btn-outline-ami btn-ami">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>Kembali
    </a>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
