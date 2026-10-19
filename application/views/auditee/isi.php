<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$jawaban = isset($jawaban) ? $jawaban : [];
$read_only = $tugas->status !== STATUS_BELUM_DIISI;
$sudah_dinilai = $tugas->status === STATUS_DINILAI;
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
                <div class="ami-stat-label">Standar</div>
                        <div class="tw-font-bold"><?php echo html_escape($tugas->nama_standar); ?></div>
            </div>
            <div>
                <div class="ami-stat-label">Auditor</div>
                        <div class="tw-font-bold"><?php echo html_escape($tugas->auditor_nama); ?></div>
            </div>
            <div>
                <div class="ami-stat-label">Status</div>
                <span class="ami-status status-<?php echo html_escape($tugas->status); ?>">
                    <?php echo $tugas->status === STATUS_BELUM_DIISI ? 'Belum diisi' : ($sudah_dinilai ? 'Dinilai' : 'Menunggu penilaian'); ?>
                </span>
            </div>
        </div>
    </div>
</div>

<?php if (!$read_only): ?>
    <div class="ami-alert ami-alert-info" style="background:#e6f1fb;color:#185fa5;border:0;border-radius:8px;">
        Jawaban boleh singkat. Setiap pertanyaan wajib disertai link bukti dokumen yang dapat dibuka auditor.
    </div>
<?php endif; ?>

<?php echo form_open('auditee/simpan_jawaban/' . (int) $tugas->id); ?>
    <?php foreach ($jawaban as $index => $item): ?>
        <?php
        $field_jawaban = 'jawaban[' . (int) $item->id . ']';
        $field_link = 'link_bukti[' . (int) $item->id . ']';
        $current_jawaban = set_value($field_jawaban, $item->jawaban);
        $current_link = set_value($field_link, $item->link_bukti);
        ?>
        <div class="ami-panel ami-mb-3">
            <div class="ami-panel-body">
                <div class="ami-flex ami-align-start ami-mb-3">
                    <span class="ami-nav-badge ami-mr-2" style="margin-left:0;"><?php echo (int) $index + 1; ?></span>
                    <div class="ami-font-bold"><?php echo html_escape($item->isi_pertanyaan); ?></div>
                </div>

                <div class="ami-form-group">
                    <label for="jawaban-<?php echo (int) $item->id; ?>">Jawaban singkat</label>
                        <textarea id="jawaban-<?php echo (int) $item->id; ?>" name="jawaban[<?php echo (int) $item->id; ?>]" rows="3" class="ami-form-control" <?php echo $read_only ? 'readonly' : ''; ?>><?php echo html_escape($current_jawaban); ?></textarea>
                </div>
                <div class="ami-form-group<?php echo $sudah_dinilai ?'' : ' ami-mb-0'; ?>">
                    <label for="link-<?php echo (int) $item->id; ?>">Link bukti dokumen <span class="text-danger">*</span></label>
                        <input id="link-<?php echo (int) $item->id; ?>" type="url" name="link_bukti[<?php echo (int) $item->id; ?>]" value="<?php echo html_escape($current_link); ?>" class="ami-form-control" placeholder="https://..." required <?php echo $read_only ? 'readonly' : ''; ?>>
                    <?php if ($read_only && !empty($item->link_bukti)): ?>
                        <a class="d-inline-block ami-mt-2" href="<?php echo html_escape($item->link_bukti); ?>" target="_blank" rel="noopener noreferrer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 3h7v7M10 14 21 3M21 14v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h6"/></svg>Buka bukti
                        </a>
                    <?php endif; ?>
                </div>

                <?php if ($sudah_dinilai): ?>
                    <div class="ami-grid-2 ami-pt-3" style="border-top:1px solid var(--ami-border);">
                        <div>
                            <div class="ami-stat-label">Skor auditor</div>
                            <div class="ami-stat-value" style="font-size:20px;"><?php echo html_escape((string) $item->skor); ?> / 4</div>
                        </div>
                        <div>
                            <div class="ami-stat-label">Catatan atau temuan</div>
                            <div><?php echo nl2br(html_escape($item->temuan ?: 'Tidak ada catatan.')); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="ami-actions ami-justify-end">
        <a href="<?php echo site_url('auditee/tugas'); ?>" class="btn-outline-ami btn-ami">Kembali</a>
        <?php if (!$read_only && !empty($jawaban)): ?>
            <button type="submit" class="tw-button-primary btn-ami">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="M22 2 11 13"/></svg>Kirim jawaban
            </button>
        <?php endif; ?>
    </div>
<?php echo form_close(); ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
