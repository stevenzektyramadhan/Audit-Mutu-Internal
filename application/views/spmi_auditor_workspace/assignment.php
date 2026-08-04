<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
$readonly = $assignment->state === 'closed' || !$assessment || $assessment->status === 'finalized' || !in_array($assignment->submission_status, ['submitted', 'resubmitted'], TRUE);
$version = $assessment ? (int) $assessment->version : 0;
?>
<div class="ami-panel"><div class="ami-panel-body">
<h2 class="ami-section-title"><?php echo html_escape($assignment->source_package_code . ' — ' . $assignment->source_package_title); ?></h2>
<p><?php echo nl2br(html_escape($assignment->source_package_description ?: 'Deskripsi paket belum diisi.')); ?></p>
<p><strong>Status submission</strong>: <?php echo html_escape($assignment->submission_status); ?></p>
<?php if (!$readonly): ?><?php echo form_open('auditor/spmi/assignment/' . (int) $assignment->id . '/save'); ?><input type="hidden" name="version" value="<?php echo html_escape((string) $version); ?>"><?php endif; ?>
<?php foreach ($items as $item): ?>
<article class="card mb-3"><div class="card-body">
<h3><?php echo html_escape((string) $item->display_order . '. ' . $item->question_code); ?></h3>
<p><?php echo nl2br(html_escape($item->question_text)); ?></p>
<p><strong>Instruksi bukti</strong><br><?php echo nl2br(html_escape($item->evidence_instruction)); ?></p>
<p><strong>Realisasi submitted</strong><br><?php echo nl2br(html_escape($item->assessment ? $item->assessment->realization_snapshot : '')); ?></p>
<div><strong>Rubrik</strong><?php foreach ($item->rubrics as $rubric): ?><div><?php echo html_escape((string) $rubric->score . ' — ' . $rubric->descriptor); ?></div><?php endforeach; ?></div>
<ul><?php foreach ($item->evidence as $evidence): ?><li><a href="<?php echo site_url('auditor/spmi/evidence/' . (int) $evidence->id . '/download'); ?>"><?php echo html_escape($evidence->original_name); ?></a></li><?php endforeach; ?></ul>
<?php $current_score = $item->assessment ? $item->assessment->score : ''; ?>
<label for="score-<?php echo (int) $item->id; ?>">Skor</label>
<select id="score-<?php echo (int) $item->id; ?>" name="assessment[<?php echo (int) $item->id; ?>][score]" <?php echo $readonly ? 'disabled' : ''; ?>><option value="">Pilih skor</option><?php for ($score = 1; $score <= 4; $score++): ?><option value="<?php echo $score; ?>" <?php echo (string) $current_score === (string) $score ? 'selected' : ''; ?>><?php echo $score; ?></option><?php endfor; ?></select>
<?php $current_finding_type = $item->assessment ? (string) $item->assessment->finding_type : ''; ?>
<label for="finding-type-<?php echo (int) $item->id; ?>">Jenis temuan</label>
<select id="finding-type-<?php echo (int) $item->id; ?>" name="assessment[<?php echo (int) $item->id; ?>][finding_type]" <?php echo $readonly ? 'disabled' : ''; ?>><option value="" <?php echo $current_finding_type === '' ? 'selected' : ''; ?>>Tidak ada</option><option value="ob" <?php echo $current_finding_type === 'ob' ? 'selected' : ''; ?>>OB</option><option value="kts" <?php echo $current_finding_type === 'kts' ? 'selected' : ''; ?>>KTS</option></select>
<label for="finding-<?php echo (int) $item->id; ?>">Temuan</label>
<textarea id="finding-<?php echo (int) $item->id; ?>" name="assessment[<?php echo (int) $item->id; ?>][finding]" <?php echo $readonly ? 'disabled' : ''; ?>><?php echo html_escape($item->assessment ? $item->assessment->finding : ''); ?></textarea>
<label for="recommendation-<?php echo (int) $item->id; ?>">Rekomendasi</label>
<textarea id="recommendation-<?php echo (int) $item->id; ?>" name="assessment[<?php echo (int) $item->id; ?>][recommendation]" <?php echo $readonly ? 'disabled' : ''; ?>><?php echo html_escape($item->assessment ? $item->assessment->recommendation : ''); ?></textarea>
</div></article>
<?php endforeach; ?>
<?php if (!$readonly): ?><button type="submit">Simpan draft</button><button type="submit" formaction="<?php echo site_url('auditor/spmi/assignment/' . (int) $assignment->id . '/finalize'); ?>">Finalisasi</button><?php echo form_close(); ?><?php endif; ?>
<?php if ($assignment->state !== 'closed' && in_array($assignment->submission_status, ['submitted', 'resubmitted'], TRUE) && (!$assessment || $assessment->status !== 'finalized')): ?><?php echo form_open('auditor/spmi/assignment/' . (int) $assignment->id . '/return'); ?><input type="hidden" name="submission_version" value="<?php echo html_escape((string) $assignment->submission_version); ?>"><label for="revision-reason">Alasan revisi</label><textarea id="revision-reason" name="reason" required></textarea><button type="submit">Kembalikan untuk revisi</button><?php echo form_close(); ?><?php endif; ?>
<h3>Riwayat revisi</h3><?php if (empty($revision_history)): ?><p>Belum ada riwayat revisi.</p><?php else: ?><ol><?php foreach ($revision_history as $event): ?><li><?php echo html_escape($event->created_at); ?> — <?php echo html_escape($event->previous_status . ' → ' . $event->new_status); ?>, versi <?php echo html_escape((string) $event->previous_version . ' → ' . (string) $event->resulting_version); ?> oleh <?php echo html_escape($event->actor_name ?: $event->actor_email); ?><br><?php echo nl2br(html_escape($event->reason)); ?></li><?php endforeach; ?></ol><?php endif; ?>
</div></div>
<?php include APPPATH . 'views/layouts/footer.php'; ?>
