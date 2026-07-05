<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$jawaban = isset($jawaban) ? $jawaban : [];
$confirmation = !empty($confirmation);
$read_only = !empty($tugas->is_readonly) || $confirmation;
$status_key = $tugas->display_status;
$status_icons = [
    'belum_diisi' => 'fa-exclamation-circle',
    'draft' => 'fa-save',
    'submitted' => 'fa-paper-plane',
    'revisi' => 'fa-undo',
    'dinilai' => 'fa-check-circle',
];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<?php if (validation_errors()): ?>
    <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
<?php endif; ?>

<?php if ($confirmation || $status_key === 'submitted'): ?>
    <div class="alert alert-primary d-flex align-items-center" style="background:#e6f1fb;color:#185fa5;border:0;border-radius:8px;">
        <i class="fas fa-clock mr-2" aria-hidden="true"></i>
        Menunggu penilaian auditor
    </div>
<?php elseif ($status_key === 'revisi'): ?>
    <div class="alert alert-warning d-flex align-items-center" style="background:#faeeda;color:#854f0b;border:0;border-radius:8px;">
        <i class="fas fa-undo mr-2" aria-hidden="true"></i>
        Tugas dikembalikan, silakan perbaiki
    </div>
<?php endif; ?>

<div class="ami-panel mb-3">
    <div class="ami-panel-body">
        <div class="row">
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="ami-stat-label">Periode</div>
                <div class="font-weight-bold"><?php echo html_escape($tugas->nama_periode ?: '-'); ?></div>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="ami-stat-label">Standar</div>
                <div class="font-weight-bold"><?php echo html_escape($tugas->nama_standar ?: '-'); ?></div>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="ami-stat-label">Auditor</div>
                <div class="font-weight-bold"><?php echo html_escape($tugas->auditor_nama ?: '-'); ?></div>
            </div>
            <div class="col-md-3">
                <div class="ami-stat-label">Status</div>
                <span class="ami-status status-<?php echo html_escape($status_key); ?>">
                    <i class="fas <?php echo html_escape($status_icons[$status_key] ?? 'fa-circle'); ?>" aria-hidden="true"></i>
                    <?php echo html_escape($tugas->display_status_label); ?>
                </span>
            </div>
        </div>

        <div class="ami-actions mt-3">
            <a href="<?php echo site_url('auditee/tugas'); ?>" class="btn btn-outline-ami btn-ami">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali
            </a>
            <?php if (!empty($tugas->file_instrumen)): ?>
                <a href="<?php echo site_url('auditee/download_instrumen/' . (int) $tugas->id); ?>" class="btn btn-outline-ami btn-ami">
                    <i class="fas fa-download" aria-hidden="true"></i> Download instrumen
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!$read_only): ?>
    <div class="alert alert-primary" style="background:#e6f1fb;color:#185fa5;border:0;border-radius:8px;">
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
        <div class="ami-panel mb-3">
            <div class="ami-panel-body">
                <div class="d-flex align-items-start mb-3">
                    <span class="ami-nav-badge mr-2" style="margin-left:0;"><?php echo (int) $index + 1; ?></span>
                    <div class="font-weight-bold"><?php echo html_escape($item->isi_pertanyaan); ?></div>
                </div>

                <div class="form-group">
                    <label for="jawaban-<?php echo (int) $item->id; ?>">Jawaban</label>
                    <textarea id="jawaban-<?php echo (int) $item->id; ?>" name="jawaban[<?php echo (int) $item->id; ?>]" rows="4" class="form-control" <?php echo $read_only ? 'readonly' : ''; ?>><?php echo html_escape($current_jawaban); ?></textarea>
                </div>
                <div class="form-group mb-0">
                    <label for="link-<?php echo (int) $item->id; ?>">Link bukti</label>
                    <input id="link-<?php echo (int) $item->id; ?>" type="url" name="link_bukti[<?php echo (int) $item->id; ?>]" value="<?php echo html_escape($current_link); ?>" class="form-control" placeholder="https://..." <?php echo $read_only ? 'readonly' : ''; ?>>
                    <?php if (!empty($current_link)): ?>
                        <a class="d-inline-block mt-2" href="<?php echo html_escape($current_link); ?>" target="_blank" rel="noopener noreferrer">
                            <i class="fas fa-external-link-alt mr-1" aria-hidden="true"></i>Buka bukti
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="ami-actions justify-content-end">
        <a href="<?php echo site_url('auditee/tugas'); ?>" class="btn btn-outline-ami btn-ami">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali
        </a>
        <?php if (!$read_only && !empty($jawaban)): ?>
            <button type="submit" class="btn btn-outline-ami btn-ami" data-loading-text="Menyimpan...">
                <i class="fas fa-save" aria-hidden="true"></i> Save draft
            </button>
            <button type="submit" class="btn btn-primary btn-ami" formaction="<?php echo site_url('auditee/submit/' . (int) $tugas->id); ?>" data-loading-text="Submit...">
                <i class="fas fa-paper-plane" aria-hidden="true"></i> Submit
            </button>
        <?php endif; ?>
    </div>
<?php echo form_close(); ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
