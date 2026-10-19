<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$jawaban = isset($jawaban) ? $jawaban : [];
$read_only = $tugas->status === STATUS_DINILAI;
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<?php if (validation_errors()): ?>
    <div class="ami-alert ami-alert-danger"><?php echo validation_errors(); ?></div>
<?php endif; ?>

<div class="ami-panel ami-mb-3">
    <div class="ami-panel-body">
        <div class="ami-grid-3">
            <div>
                <div class="ami-stat-label">Auditee</div>
                <div class="ami-font-bold"><?php echo html_escape($tugas->auditee_nama); ?></div>
            </div>
            <div>
                <div class="ami-stat-label">Standar</div>
                <div class="ami-font-bold"><?php echo html_escape($tugas->nama_standar); ?></div>
            </div>
            <div>
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
        $current_catatan = set_value($field_catatan, $item->temuan);
        ?>
        <div class="ami-panel ami-mb-3">
            <div class="ami-panel-body">
                <div class="ami-flex ami-align-start ami-mb-3">
                    <span class="ami-nav-badge ami-mr-2" style="margin-left:0;"><?php echo (int) $index + 1; ?></span>
                    <div class="ami-font-bold"><?php echo html_escape($item->isi_pertanyaan); ?></div>
                </div>

                <div class="ami-grid-2">
                    <div>
                        <div class="ami-stat-label ami-mb-1">Jawaban auditee</div>
                        <div class="ami-mb-2"><?php echo nl2br(html_escape($item->jawaban ?: '-')); ?></div>
                        <?php if (!empty($item->link_bukti)): ?>
                            <a href="<?php echo html_escape($item->link_bukti); ?>" target="_blank" rel="noopener noreferrer">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 3h7v7M10 14 21 3M21 14v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h6"/></svg>Lihat bukti dokumen
                            </a>
                        <?php else: ?>
                            <span class="ami-muted">Link bukti belum tersedia.</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="ami-form-group">
                            <label for="skor-<?php echo (int) $item->id; ?>">Skor</label>
                            <select id="skor-<?php echo (int) $item->id; ?>" name="skor[<?php echo (int) $item->id; ?>]" class="ami-form-control" required <?php echo $read_only ? 'disabled' : ''; ?>>
                                <option value="">Pilih skor</option>
                                <?php foreach (skor_audit_options() as $skor => $label): ?>
                                    <option value="<?php echo $skor; ?>" <?php echo $current_skor === $skor ? 'selected' : ''; ?>>
                                        <?php echo $skor . ' - ' . html_escape($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="ami-form-group ami-mb-0">
                            <label for="catatan-<?php echo (int) $item->id; ?>">Catatan atau temuan</label>
                            <textarea id="catatan-<?php echo (int) $item->id; ?>" name="catatan[<?php echo (int) $item->id; ?>]" rows="3" class="ami-form-control" <?php echo $read_only ? 'readonly' : ''; ?>><?php echo html_escape($current_catatan); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="ami-actions ami-justify-end">
        <a href="<?php echo site_url('auditor'); ?>" class="btn-outline-ami btn-ami">Kembali</a>
        <?php if (!$read_only && !empty($jawaban)): ?>
            <button type="submit" class="tw-button-primary btn-ami">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 3h12l2 2v16H5V3Z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></svg>Simpan penilaian
            </button>
        <?php endif; ?>
    </div>
<?php echo form_close(); ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
