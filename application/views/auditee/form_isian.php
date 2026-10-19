<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$jawaban = isset($jawaban) ? $jawaban : [];
$confirmation = !empty($confirmation);
$read_only = !empty($tugas->is_readonly) || $confirmation;
$status_key = $tugas->display_status;
$status_icons = [
    'belum_diisi' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5m0 3h.01"/></svg>',
    'draft' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 3h12l2 2v16H5V3Z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></svg>',
    'submitted' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="M22 2 11 13"/></svg>',
    'revisi' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v6h6"/></svg>',
    'dinilai' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>',
];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<?php if (validation_errors()): ?>
    <div class="ami-alert ami-alert-danger"><?php echo validation_errors(); ?></div>
<?php endif; ?>

<?php if ($confirmation || $status_key === 'submitted'): ?>
    <div class="ami-alert ami-alert-info" style="background:#e6f1fb;color:#185fa5;border:0;border-radius:8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        Menunggu penilaian auditor
    </div>
<?php elseif ($status_key === 'revisi'): ?>
    <div class="ami-alert ami-alert-warning" style="background:#faeeda;color:#854f0b;border:0;border-radius:8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v6h6"/></svg>
        Tugas dikembalikan, silakan perbaiki
    </div>
<?php endif; ?>

<div class="ami-panel ami-mb-3">
    <div class="ami-panel-body">
        <div class="ami-grid-4">
            <div>
                <div class="ami-stat-label">Periode</div>
                <div class="tw-font-bold"><?php echo html_escape($tugas->nama_periode ?: '-'); ?></div>
            </div>
            <div>
                <div class="ami-stat-label">Standar</div>
                <div class="tw-font-bold"><?php echo html_escape($tugas->nama_standar ?: '-'); ?></div>
            </div>
            <div>
                <div class="ami-stat-label">Auditor</div>
                <div class="tw-font-bold"><?php echo html_escape($tugas->auditor_nama ?: '-'); ?></div>
            </div>
            <div>
                <div class="ami-stat-label">Status</div>
                <span class="ami-status status-<?php echo html_escape($status_key); ?>">
                    <?php echo $status_icons[$status_key] ?? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg>'; ?>
                    <?php echo html_escape($tugas->display_status_label); ?>
                </span>
            </div>
        </div>

        <div class="ami-actions ami-mt-3">
            <a href="<?php echo site_url('auditee/tugas'); ?>" class="btn-outline-ami btn-ami">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6M9 12h12"/></svg> Kembali
            </a>
            <?php if (!empty($tugas->file_instrumen)): ?>
                <a href="<?php echo site_url('auditee/download_instrumen/' . (int) $tugas->id); ?>" class="btn-outline-ami btn-ami">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5M5 21h14"/></svg> Download instrumen
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!$read_only): ?>
    <div class="ami-alert ami-alert-info" style="background:#e6f1fb;color:#185fa5;border:0;border-radius:8px;">
        Simpan draft boleh belum lengkap. Submit hanya bisa dilakukan setelah semua jawaban dan link bukti terisi.
    </div>
<?php endif; ?>

<?php echo form_open('auditee/save/' . (int) $tugas->id); ?>
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
                    <label for="jawaban-<?php echo (int) $item->id; ?>">Jawaban</label>
                    <textarea id="jawaban-<?php echo (int) $item->id; ?>" name="jawaban[<?php echo (int) $item->id; ?>]" rows="4" class="ami-form-control" <?php echo $read_only ? 'readonly' : ''; ?>><?php echo html_escape($current_jawaban); ?></textarea>
                </div>
                <div class="ami-form-group ami-mb-0">
                    <label for="link-<?php echo (int) $item->id; ?>">Link bukti</label>
                    <input id="link-<?php echo (int) $item->id; ?>" type="url" name="link_bukti[<?php echo (int) $item->id; ?>]" value="<?php echo html_escape($current_link); ?>" class="ami-form-control" placeholder="https://..." <?php echo $read_only ? 'readonly' : ''; ?>>
                    <?php if (!empty($current_link)): ?>
                        <a class="d-inline-block ami-mt-2" href="<?php echo html_escape($current_link); ?>" target="_blank" rel="noopener noreferrer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 3h7v7M10 14 21 3M21 14v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h6"/></svg>Buka bukti
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="ami-actions ami-justify-end">
        <a href="<?php echo site_url('auditee/tugas'); ?>" class="btn-outline-ami btn-ami">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6M9 12h12"/></svg> Kembali
        </a>
        <?php if (!$read_only && !empty($jawaban)): ?>
            <button type="submit" class="btn-outline-ami btn-ami" data-loading-text="Menyimpan...">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 3h12l2 2v16H5V3Z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></svg> Save draft
            </button>
            <button type="submit" class="tw-button-primary btn-ami" formaction="<?php echo site_url('auditee/submit/' . (int) $tugas->id); ?>" data-loading-text="Submit...">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="M22 2 11 13"/></svg> Submit
            </button>
        <?php endif; ?>
    </div>
<?php echo form_close(); ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
