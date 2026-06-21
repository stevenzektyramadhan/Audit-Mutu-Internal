<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$jawaban = isset($jawaban) ? $jawaban : [];
$read_only = $tugas->status === STATUS_DINILAI;
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<?php if (validation_errors()): ?>
    <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
<?php endif; ?>

<div class="ami-panel mb-3">
    <div class="ami-panel-body">
        <div class="row">
            <div class="col-md-4 mb-2 mb-md-0">
                <div class="ami-stat-label">Auditee</div>
                <div class="font-weight-bold"><?php echo html_escape($tugas->auditee_nama); ?></div>
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
                <div class="ami-stat-label">Standar</div>
                <div class="font-weight-bold"><?php echo html_escape($tugas->nama_standar); ?></div>
            </div>
            <div class="col-md-4">
                <div class="ami-stat-label">Status</div>
                <span class="ami-status status-<?php echo html_escape($tugas->status); ?>">
                    <?php echo $read_only ? 'Sudah dinilai' : 'Siap dinilai'; ?>
                </span>
            </div>
        </div>
    </div>
</div>

<?php echo form_open('auditor/simpan_nilai/' . (int) $tugas->id); ?>
    <?php foreach ($jawaban as $index => $item): ?>
        <?php
        $field_skor = 'skor[' . (int) $item->id . ']';
        $field_catatan = 'catatan[' . (int) $item->id . ']';
        $current_skor = (int) set_value($field_skor, $item->skor);
        $current_catatan = set_value($field_catatan, $item->catatan);
        ?>
        <div class="ami-panel mb-3">
            <div class="ami-panel-body">
                <div class="d-flex align-items-start mb-3">
                    <span class="ami-nav-badge mr-2" style="margin-left:0;"><?php echo (int) $index + 1; ?></span>
                    <div class="font-weight-bold"><?php echo html_escape($item->isi_pertanyaan); ?></div>
                </div>

                <div class="row">
                    <div class="col-lg-7 mb-3 mb-lg-0">
                        <div class="ami-stat-label mb-1">Jawaban auditee</div>
                        <div class="mb-2"><?php echo nl2br(html_escape($item->jawaban ?: '-')); ?></div>
                        <?php if (!empty($item->link_bukti)): ?>
                            <a href="<?php echo html_escape($item->link_bukti); ?>" target="_blank" rel="noopener noreferrer">
                                <i class="fas fa-external-link-alt mr-1" aria-hidden="true"></i>Lihat bukti dokumen
                            </a>
                        <?php else: ?>
                            <span class="text-muted">Link bukti belum tersedia.</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-5">
                        <div class="form-group">
                            <label for="skor-<?php echo (int) $item->id; ?>">Skor</label>
                            <select id="skor-<?php echo (int) $item->id; ?>" name="skor[<?php echo (int) $item->id; ?>]" class="form-control bg-dark text-light border-secondary" required <?php echo $read_only ? 'disabled' : ''; ?>>
                                <option value="">Pilih skor</option>
                                <?php foreach (skor_audit_options() as $skor => $label): ?>
                                    <option value="<?php echo $skor; ?>" <?php echo $current_skor === $skor ? 'selected' : ''; ?>>
                                        <?php echo $skor . ' - ' . html_escape($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label for="catatan-<?php echo (int) $item->id; ?>">Catatan atau temuan</label>
                            <textarea id="catatan-<?php echo (int) $item->id; ?>" name="catatan[<?php echo (int) $item->id; ?>]" rows="3" class="form-control bg-dark text-light border-secondary" <?php echo $read_only ? 'readonly' : ''; ?>><?php echo html_escape($current_catatan); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="ami-actions justify-content-end">
        <a href="<?php echo site_url('auditor'); ?>" class="btn btn-outline-ami btn-ami">Kembali</a>
        <?php if (!$read_only && !empty($jawaban)): ?>
            <button type="submit" class="btn btn-primary btn-ami">
                <i class="fas fa-save" aria-hidden="true"></i>Simpan penilaian
            </button>
        <?php endif; ?>
    </div>
<?php echo form_close(); ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
