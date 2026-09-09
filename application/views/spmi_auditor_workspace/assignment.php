<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
$readonly = $assignment->state === 'closed' || !$assessment || $assessment->status === 'finalized' || !in_array($assignment->submission_status, ['submitted', 'resubmitted'], TRUE);
$version = $assessment ? (int) $assessment->version : 0;
$upload_forms = [];
$delete_forms = [];
?>
<div class="ami-panel"><div class="ami-panel-body">
<h2 class="ami-section-title"><?php echo html_escape($assignment->source_package_code . ' — ' . $assignment->source_package_title); ?></h2>
<p><?php echo nl2br(html_escape($assignment->source_package_description ?: 'Deskripsi paket belum diisi.')); ?></p>
<p><strong>Status submission</strong>: <?php echo html_escape($assignment->submission_status); ?></p>
<?php if (!$readonly): ?><?php echo form_open('auditor/spmi/assignment/' . (int) $assignment->id . '/save'); ?><input type="hidden" name="version" value="<?php echo html_escape((string) $version); ?>"><input type="hidden" name="source_submission_version" value="<?php echo html_escape((string) $assignment->submission_version); ?>">
<?php endif; ?>
<?php foreach ($items as $item): ?>
<?php
$current_score = $item->assessment ? $item->assessment->score : '';
$current_finding_type = $item->assessment ? (string) $item->assessment->finding_type : '';
$upload_form_id = 'auditor-evidence-upload-' . (int) $item->id;
?>
<article class="card mb-3"<?php if (!$readonly): ?> data-autosave-card="<?php echo (int) ($item->assessment ? $item->assessment->id : 0); ?>"<?php endif; ?>><div class="card-body">
<h3><?php echo html_escape((string) $item->display_order . '. ' . $item->question_code); ?></h3>
<p><?php echo nl2br(html_escape($item->question_text)); ?></p>
<p><strong>Instruksi bukti</strong><br><?php echo nl2br(html_escape($item->evidence_instruction)); ?></p>
<p><strong>Realisasi submitted</strong><br><?php echo nl2br(html_escape($item->assessment ? $item->assessment->realization_snapshot : '')); ?></p>
<?php $evidence_url = trim((string) $item->evidence_url); $evidence_scheme = strtolower((string) parse_url($evidence_url, PHP_URL_SCHEME)); $is_evidence_link = $evidence_url !== '' && filter_var($evidence_url, FILTER_VALIDATE_URL) && in_array($evidence_scheme, ['http', 'https'], TRUE); ?>
<p><strong>Evidence</strong><br><span>Link bukti: </span><?php if ($evidence_url === ''): ?><span class="text-muted">Tidak ada link bukti</span><?php elseif ($is_evidence_link): ?><a href="<?php echo html_escape($evidence_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo html_escape($evidence_url); ?></a><?php else: ?><span class="text-muted">Link bukti tidak valid</span><?php endif; ?></p>
<div><strong>Rubrik</strong><?php foreach ($item->rubrics as $rubric): ?><div><?php echo html_escape((string) $rubric->score . ' — ' . $rubric->descriptor); ?></div><?php endforeach; ?></div>
<p><strong>Bukti auditee</strong></p><ul><?php foreach ($item->evidence as $evidence): ?><li><a href="<?php echo site_url('auditor/spmi/evidence/' . (int) $evidence->id . '/download'); ?>"><?php echo html_escape($evidence->original_name); ?></a></li><?php endforeach; ?></ul>
<p><strong>Bukti auditor</strong></p><ul><?php foreach ($item->auditor_evidence as $evidence): ?><li><a href="<?php echo site_url('auditor/spmi/auditor-evidence/' . (int) $evidence->id . '/download'); ?>"><?php echo html_escape($evidence->original_name); ?></a> <small><?php echo html_escape($evidence->mime_type . ' / ' . (string) $evidence->size_bytes . ' bytes / ' . $evidence->sha256); ?></small><?php if (!$readonly): ?><?php $delete_form_id = 'auditor-evidence-delete-' . (int) $evidence->id; ob_start(); ?><?php echo form_open('auditor/spmi/auditor-evidence/' . (int) $evidence->id . '/delete', ['class' => 'd-inline', 'id' => $delete_form_id]); ?><input type="hidden" name="version" value="<?php echo html_escape((string) $version); ?>"><input type="hidden" name="source_submission_version" value="<?php echo html_escape((string) $assignment->submission_version); ?>"><?php echo form_close(); ?><?php $delete_forms[] = ob_get_clean(); ?><button type="submit" form="<?php echo $delete_form_id; ?>" class="btn btn-ami ami-action-btn danger">Hapus</button><?php endif; ?></li><?php endforeach; ?></ul>
<?php if (!$readonly && $item->assessment): ?><?php ob_start(); ?><?php echo form_open_multipart('auditor/spmi/assessment-item/' . (int) $item->assessment->id . '/evidence/upload', ['id' => $upload_form_id]); ?><input type="hidden" name="version" value="<?php echo html_escape((string) $version); ?>"><input type="hidden" name="source_submission_version" value="<?php echo html_escape((string) $assignment->submission_version); ?>"><?php echo form_close(); ?><?php $upload_forms[] = ob_get_clean(); ?><input class="form-control" type="file" name="evidence" accept="application/pdf,image/jpeg,image/png" required form="<?php echo $upload_form_id; ?>"><button type="submit" form="<?php echo $upload_form_id; ?>" class="btn btn-primary btn-ami">Upload bukti auditor</button><?php endif; ?>
<?php if ($readonly): ?>
<label for="score-<?php echo (int) $item->id; ?>">Skor</label><select class="form-control" id="score-<?php echo (int) $item->id; ?>" disabled><option value=""><?php echo html_escape((string) $current_score); ?></option></select>
<label for="finding-type-<?php echo (int) $item->id; ?>">Jenis temuan</label><select class="form-control" id="finding-type-<?php echo (int) $item->id; ?>" disabled><option value=""><?php echo html_escape($current_finding_type); ?></option></select>
<label for="finding-<?php echo (int) $item->id; ?>">Temuan</label><textarea class="form-control" id="finding-<?php echo (int) $item->id; ?>" disabled><?php echo html_escape($item->assessment ? $item->assessment->finding : ''); ?></textarea>
<label for="recommendation-<?php echo (int) $item->id; ?>">Rekomendasi</label><textarea class="form-control" id="recommendation-<?php echo (int) $item->id; ?>" disabled><?php echo html_escape($item->assessment ? $item->assessment->recommendation : ''); ?></textarea>
<label for="improvement-plan-<?php echo (int) $item->id; ?>">Rencana perbaikan</label><textarea class="form-control" id="improvement-plan-<?php echo (int) $item->id; ?>" disabled><?php echo html_escape($item->assessment ? $item->assessment->improvement_plan : ''); ?></textarea>
<label for="evidence-date-<?php echo (int) $item->id; ?>">Tanggal bukti</label><input class="form-control" id="evidence-date-<?php echo (int) $item->id; ?>" type="date" disabled value="<?php echo html_escape($item->assessment ? $item->assessment->evidence_date : ''); ?>">
<?php else: ?>
<label for="score-<?php echo (int) $item->id; ?>">Skor</label><select class="form-control" id="score-<?php echo (int) $item->id; ?>" name="assessment[<?php echo (int) $item->id; ?>][score]" data-autosave-field="score"><option value="">Pilih skor</option><?php for ($score = 1; $score <= 4; $score++): ?><option value="<?php echo $score; ?>" <?php echo (string) $current_score === (string) $score ? 'selected' : ''; ?>><?php echo $score; ?></option><?php endfor; ?></select>
<label for="finding-type-<?php echo (int) $item->id; ?>">Jenis temuan</label><select class="form-control" id="finding-type-<?php echo (int) $item->id; ?>" name="assessment[<?php echo (int) $item->id; ?>][finding_type]" data-autosave-field="finding_type"><option value="" <?php echo $current_finding_type === '' ? 'selected' : ''; ?>>Tidak ada</option><option value="ob" <?php echo $current_finding_type === 'ob' ? 'selected' : ''; ?>>OB</option><option value="kts" <?php echo $current_finding_type === 'kts' ? 'selected' : ''; ?>>KTS</option></select>
<label for="finding-<?php echo (int) $item->id; ?>">Temuan</label><textarea class="form-control" id="finding-<?php echo (int) $item->id; ?>" name="assessment[<?php echo (int) $item->id; ?>][finding]" data-autosave-field="finding"><?php echo html_escape($item->assessment ? $item->assessment->finding : ''); ?></textarea>
<label for="recommendation-<?php echo (int) $item->id; ?>">Rekomendasi</label><textarea class="form-control" id="recommendation-<?php echo (int) $item->id; ?>" name="assessment[<?php echo (int) $item->id; ?>][recommendation]" data-autosave-field="recommendation"><?php echo html_escape($item->assessment ? $item->assessment->recommendation : ''); ?></textarea>
<label for="improvement-plan-<?php echo (int) $item->id; ?>">Rencana perbaikan</label><textarea class="form-control" id="improvement-plan-<?php echo (int) $item->id; ?>" name="assessment[<?php echo (int) $item->id; ?>][improvement_plan]" data-autosave-field="improvement_plan"><?php echo html_escape($item->assessment ? $item->assessment->improvement_plan : ''); ?></textarea>
<label for="evidence-date-<?php echo (int) $item->id; ?>">Tanggal bukti</label><input class="form-control" id="evidence-date-<?php echo (int) $item->id; ?>" type="date" name="assessment[<?php echo (int) $item->id; ?>][evidence_date]" value="<?php echo html_escape($item->assessment ? $item->assessment->evidence_date : ''); ?>" data-autosave-field="evidence_date"><button type="button" class="btn btn-outline-ami btn-ami" data-autosave-item="<?php echo (int) ($item->assessment ? $item->assessment->id : 0); ?>">Simpan item</button><span role="status" aria-live="polite" data-autosave-status></span>
<?php endif; ?>
</div></article>
<?php endforeach; ?>
<?php if (!$readonly): ?><button type="submit" class="btn btn-primary btn-ami">Simpan draft</button><button type="submit" class="btn btn-primary btn-ami" formaction="<?php echo site_url('auditor/spmi/assignment/' . (int) $assignment->id . '/finalize'); ?>">Finalisasi</button><?php echo form_close(); ?><script>
(function () {
    var form = document.querySelector('form[action*="/auditor/spmi/assignment/"][action$="/save"]');
    if (!form) return;
    var csrfName = <?php echo json_encode($this->security->get_csrf_token_name()); ?>;
    var urlBase = <?php echo json_encode(site_url('auditor/spmi/item/')); ?>;
    form.querySelectorAll('[data-autosave-item]').forEach(function (button) {
        button.addEventListener('click', function () {
            var card = button.closest('[data-autosave-card]');
            var status = card.querySelector('[data-autosave-status]');
            var data = new FormData();
            var csrf = form.querySelector('input[name="' + csrfName + '"]');
            data.append(csrfName, csrf ? csrf.value : '');
            data.append('version', form.querySelector('input[name="version"]').value);
            data.append('source_submission_version', form.querySelector('input[name="source_submission_version"]').value);
            card.querySelectorAll('[data-autosave-field]').forEach(function (field) { data.append(field.dataset.autosaveField, field.value); });
            status.textContent = 'Menyimpan...';
            button.disabled = true;
            fetch(urlBase + button.dataset.autosaveItem + '/save', { method: 'POST', body: data, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (response) { return response.json().then(function (json) { return { ok: response.ok, json: json }; }); })
                 .then(function (result) {
                      var json = result.json;
                      if (json.csrf && json.csrf.name && json.csrf.hash) document.querySelectorAll('input[name="' + json.csrf.name + '"]').forEach(function (input) { input.value = json.csrf.hash; input.defaultValue = json.csrf.hash; });
                      if (!result.ok || !json.success) { status.textContent = json.message || 'Item gagal disimpan.'; return; }
                      document.querySelectorAll('input[name="version"]').forEach(function (input) { input.value = json.version; input.defaultValue = json.version; });
                      status.textContent = json.message || 'Item tersimpan.';
                  })
                 .catch(function () { status.textContent = 'Item gagal disimpan. Periksa koneksi lalu coba lagi.'; })
                 .finally(function () { button.disabled = false; });
        });
    });
}());
</script><?php endif; ?>
<?php foreach ($upload_forms as $upload_form): ?><?php echo $upload_form; ?><?php endforeach; ?><?php foreach ($delete_forms as $delete_form): ?><?php echo $delete_form; ?><?php endforeach; ?>
<?php if ($assignment->state !== 'closed' && in_array($assignment->submission_status, ['submitted', 'resubmitted'], TRUE) && (!$assessment || $assessment->status !== 'finalized')): ?><?php echo form_open('auditor/spmi/assignment/' . (int) $assignment->id . '/return'); ?><input type="hidden" name="submission_version" value="<?php echo html_escape((string) $assignment->submission_version); ?>"><label for="revision-reason">Alasan revisi</label><textarea class="form-control" id="revision-reason" name="reason" required></textarea><button type="submit" class="btn btn-ami ami-action-btn danger">Kembalikan untuk revisi</button><?php echo form_close(); ?><?php endif; ?>
<h3>Riwayat revisi</h3><?php if (empty($revision_history)): ?><p>Belum ada riwayat revisi.</p><?php else: ?><ol><?php foreach ($revision_history as $event): ?><li><?php echo html_escape($event->created_at); ?> — <?php echo html_escape($event->previous_status . ' → ' . $event->new_status); ?>, versi <?php echo html_escape((string) $event->previous_version . ' → ' . (string) $event->resulting_version); ?> oleh <?php echo html_escape($event->actor_name ?: $event->actor_email); ?><br><?php echo nl2br(html_escape($event->reason)); ?></li><?php endforeach; ?></ol><?php endif; ?>
</div></div>
<?php include APPPATH . 'views/layouts/footer.php';
