<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
$editable = $assignment->state !== 'closed' && in_array($assignment->submission_status, ['draft', 'returned_for_revision'], TRUE);
$version = isset($assignment->version) ? (int) $assignment->version : 0;
$upload_forms = [];
$delete_forms = [];
?>
<div class="ami-panel"><div class="ami-panel-body"><h2 class="ami-section-title mb-2"><?php echo html_escape($assignment->source_package_code . ' — ' . $assignment->source_package_title); ?></h2><p><?php echo nl2br(html_escape($assignment->source_package_description ?: 'Deskripsi paket belum diisi.')); ?></p><p><strong>Status submission</strong>: <?php echo html_escape($assignment->submission_status ?: 'draft'); ?></p>
<?php if ($editable): ?><?php echo form_open('auditee/spmi/assignment/' . (int) $assignment->id . '/save', ['id' => 'spmi-realization-form']); ?><input type="hidden" name="version" value="<?php echo html_escape((string) $version); ?>">
<?php endif; ?>
<?php foreach ($items as $item): ?><article class="card mb-3"><div class="card-body">
<?php $file_capable = in_array($item->evidence_policy, ['file', 'either', 'both'], TRUE); $url_capable = in_array($item->evidence_policy, ['url', 'either', 'both'], TRUE); ?><h3><?php echo html_escape((string) $item->display_order . '. ' . $item->question_code); ?></h3><p><?php echo nl2br(html_escape($item->question_text)); ?></p><p><strong>Instruksi bukti</strong><br><?php echo nl2br(html_escape($item->evidence_instruction)); ?></p><p><strong>Kebijakan bukti</strong><br><?php echo html_escape($item->evidence_policy); ?></p><label for="realization-<?php echo (int) $item->assignment_item_id; ?>">Realisasi</label><textarea class="form-control" id="realization-<?php echo (int) $item->assignment_item_id; ?>" name="realization[<?php echo (int) $item->assignment_item_id; ?>]" rows="4" <?php echo $editable ? '' : 'readonly'; ?>><?php echo html_escape($item->realization); ?></textarea>
<?php if ($editable && $url_capable): ?><label for="evidence-url-<?php echo (int) $item->assignment_item_id; ?>">URL bukti</label><input class="form-control" type="url" id="evidence-url-<?php echo (int) $item->assignment_item_id; ?>" name="evidence_url[<?php echo (int) $item->assignment_item_id; ?>]" value="<?php echo html_escape($item->evidence_url); ?>">
<?php elseif (!$editable && $item->evidence_url): ?><p><strong>URL bukti</strong><br><?php echo html_escape($item->evidence_url); ?></p><?php endif; ?>
<?php if ($editable && $file_capable): ?><?php ob_start(); ?><?php echo form_open_multipart('auditee/spmi/item/' . (int) $item->assignment_item_id . '/evidence/upload', ['id' => 'spmi-evidence-upload-' . (int) $item->assignment_item_id]); ?><input type="hidden" name="version" value="<?php echo html_escape((string) $version); ?>" form="spmi-evidence-upload-<?php echo (int) $item->assignment_item_id; ?>"><?php echo form_close(); ?><?php $upload_forms[] = ob_get_clean(); ?><label for="evidence-file-<?php echo (int) $item->assignment_item_id; ?>">File bukti</label><input class="form-control" type="file" id="evidence-file-<?php echo (int) $item->assignment_item_id; ?>" name="evidence" accept="application/pdf,image/jpeg,image/png" required form="spmi-evidence-upload-<?php echo (int) $item->assignment_item_id; ?>"><button type="submit" form="spmi-evidence-upload-<?php echo (int) $item->assignment_item_id; ?>">Upload bukti item <?php echo html_escape((string) $item->display_order); ?></button><?php endif; ?>
<ul><?php foreach ($item->evidence as $evidence): ?><li><a href="<?php echo site_url('auditee/spmi/evidence/' . (int) $evidence->id . '/download'); ?>"><?php echo html_escape($evidence->original_name); ?></a><?php if ($editable && $file_capable): ?><?php $delete_form_id = 'spmi-evidence-delete-' . (int) $evidence->id; ob_start(); ?><?php echo form_open('auditee/spmi/evidence/' . (int) $evidence->id . '/delete', ['id' => $delete_form_id, 'class' => 'd-inline']); ?><input type="hidden" name="version" value="<?php echo html_escape((string) $version); ?>"><?php echo form_close(); ?><?php $delete_forms[] = ob_get_clean(); ?><button type="submit" form="<?php echo $delete_form_id; ?>">Hapus <?php echo html_escape($evidence->original_name); ?></button><?php endif; ?></li><?php endforeach; ?></ul></div></article>
<?php endforeach; ?>
<?php if ($editable): ?><button type="submit">Simpan draft</button><button type="button" id="spmi-final-submit" data-confirm-url="<?php echo html_escape(site_url('auditee/spmi/assignment/' . (int) $assignment->id . '/confirm')); ?>"><?php echo $assignment->submission_status === 'returned_for_revision' ? 'Kirim ulang revisi' : 'Submit sekali'; ?></button><?php echo form_close(); ?><script>
(function () {
    var form = document.getElementById('spmi-realization-form');
    var finalButton = document.getElementById('spmi-final-submit');
    if (!form || !finalButton) return;
    finalButton.addEventListener('click', function () {
        var query = new URLSearchParams();
        query.append('version', form.elements.version.value);
        form.querySelectorAll('[name^="realization["], [name^="evidence_url["]').forEach(function (field) {
            query.append(field.name, field.value);
        });
        window.location.href = finalButton.getAttribute('data-confirm-url') + '?' + query.toString();
    });
}());
</script><?php endif; ?>
<?php foreach ($upload_forms as $upload_form): ?><?php echo $upload_form; ?><?php endforeach; ?><?php foreach ($delete_forms as $delete_form): ?><?php echo $delete_form; ?><?php endforeach; ?><?php if (!$editable && $assignment->state === 'closed'): ?><div class="alert alert-info">Riwayat submission closed bersifat hanya-baca.</div><?php elseif (!$editable): ?><div class="alert alert-info">Submission sudah dikirim dan bersifat hanya-baca.</div><?php endif; ?>
<h3>Riwayat revisi</h3><?php if (empty($revision_history)): ?><p>Belum ada riwayat revisi.</p><?php else: ?><ol><?php foreach ($revision_history as $event): ?><li><?php echo html_escape($event->created_at); ?> — <?php echo html_escape($event->previous_status . ' → ' . $event->new_status); ?>, versi <?php echo html_escape((string) $event->previous_version . ' → ' . (string) $event->resulting_version); ?> oleh <?php echo html_escape($event->actor_name ?: $event->actor_email); ?><br><?php echo nl2br(html_escape($event->reason)); ?></li><?php endforeach; ?></ol><?php endif; ?></div></div>
<?php include APPPATH . 'views/layouts/footer.php'; ?>
