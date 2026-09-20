<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
$editable = $assignment->state !== 'closed' && in_array($assignment->submission_status, ['draft', 'returned_for_revision'], TRUE);
$version = isset($assignment->version) ? (int) $assignment->version : 0;
$upload_forms = [];
$delete_forms = [];

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        'lock' => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'alert-triangle' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'external-link' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>',
        'file' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/>',
        'save' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>',
        'upload-cloud' => '<path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M12 12v9"/><path d="m16 16-4-4-4 4"/>',
        'history' => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>',
    ];
    return '<svg class="adte-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['file']) . '</svg>';
};
?>
<main id="auditee-root" data-ui-contract="ami-row-actions ami-action-btn" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-7xl">
        <!-- Top Back Action -->
        <div class="ami-row-actions no-print tw-mb-5">
            <a class="ami-action-btn adte-back-link tw-text-sm" href="<?php echo site_url('auditee/spmi'); ?>">
                <?php echo $icon('arrow-left'); ?>
                <span>Kembali ke Workspace SPMI</span>
            </a>
        </div>

        <!-- Sticky Header / Workspace Summary Card -->
        <header class="tw-sticky tw-top-4 tw-z-30 tw-mb-6 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white/95 tw-backdrop-blur-md tw-p-6 tw-shadow-sm">
            <div class="tw-flex tw-flex-col lg:tw-flex-row lg:tw-items-center lg:tw-justify-between tw-gap-4">
                <div class="tw-min-w-0">
                    <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2.5 tw-mb-2">
                        <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-slate-100 tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200">
                            <?php echo html_escape($assignment->source_standard_code); ?>
                        </span>
                        <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-slate-100 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-semibold tw-text-slate-700">
                            Auditor: <?php echo html_escape($assignment->auditor_name); ?>
                        </span>
                        <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-blue-50 tw-border tw-border-blue-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-blue-800">
                            <?php
                            $sub_map = [
                                'draft' => 'Draft',
                                'submitted' => 'Dikirim',
                                'returned_for_revision' => 'Perlu revisi',
                                'resubmitted' => 'Dikirim ulang',
                            ];
                            $clean_sub = isset($sub_map[$assignment->submission_status]) ? $sub_map[$assignment->submission_status] : ($assignment->submission_status ?: 'Draft');
                            ?>
                            Pengajuan v<?php echo html_escape((string) $version); ?> · <?php echo html_escape($clean_sub); ?>
                        </span>
                    </div>
                    <h1 class="tw-text-xl sm:tw-text-2xl tw-font-bold tw-tracking-tight tw-text-slate-950 tw-m-0">
                        <?php echo html_escape($assignment->source_standard_code . ' — ' . $assignment->source_standard_title); ?>
                    </h1>
                </div>

                <!-- Quick Action Buttons -->
                <?php if ($editable): ?>
                    <div class="tw-flex tw-items-center tw-gap-2.5 tw-flex-shrink-0">
                        <button type="submit" form="spmi-realization-form" class="tw-button-secondary tw-text-xs">
                            <?php echo $icon('save'); ?>
                            <span>Simpan draft</span>
                        </button>
                        <button type="button" id="spmi-final-submit" data-confirm-url="<?php echo html_escape(site_url('auditee/spmi/assignment/' . (int) $assignment->id . '/confirm')); ?>" class="tw-button-primary tw-text-xs">
                            <?php echo $icon('check'); ?>
                            <span><?php echo $assignment->submission_status === 'returned_for_revision' ? 'Kirim ulang revisi' : 'Submit sekali'; ?></span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>


            <!-- Status Banner: Returned for Revision Prominence -->
            <?php if ($assignment->submission_status === 'returned_for_revision'): ?>
                <div class="tw-mt-4 tw-rounded-xl tw-border tw-border-rose-200 tw-bg-rose-50/80 tw-p-4">
                    <div class="tw-flex tw-items-center tw-gap-2 tw-text-rose-900 tw-font-bold tw-text-xs tw-mb-1">
                        <span class="tw-text-rose-600"><?php echo $icon('alert-triangle'); ?></span>
                        <span>Pengajuan Dikembalikan untuk Revisi oleh Auditor</span>
                    </div>
                    <?php if (!empty($revision_history)): ?>
                        <?php $last_event = reset($revision_history); ?>
                        <div class="tw-text-xs tw-text-rose-800 tw-leading-relaxed tw-mt-1.5">
                            <strong>Catatan Auditor (<?php echo html_escape($last_event->actor_name ?: $last_event->actor_email); ?>):</strong><br>
                            <?php echo nl2br(html_escape($last_event->reason)); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php elseif (!$editable && $assignment->state === 'closed'): ?>
                <div class="tw-mt-4 tw-flex tw-items-center tw-gap-2 tw-rounded-xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-px-3.5 tw-py-2.5 tw-text-xs tw-text-slate-700">
                    <span class="tw-text-slate-500"><?php echo $icon('lock'); ?></span>
                    <span>Siklus telah ditutup. Riwayat pengajuan hanya dapat dilihat.</span>
                </div>
            <?php elseif (!$editable): ?>
                <div class="tw-mt-4 tw-flex tw-items-center tw-gap-2 tw-rounded-xl tw-border tw-border-slate-200 tw-bg-slate-50 tw-px-3.5 tw-py-2.5 tw-text-xs tw-text-slate-700">
                    <span class="tw-text-slate-500"><?php echo $icon('lock'); ?></span>
                    <span>Pengajuan sudah dikirim dan saat ini bersifat hanya-baca.</span>
                </div>
            <?php endif; ?>
        </header>

        <!-- Main Realization Form Opening -->
        <?php if ($editable): ?>
            <?php echo form_open('auditee/spmi/assignment/' . (int) $assignment->id . '/save', ['id' => 'spmi-realization-form']); ?>
            <input type="hidden" name="version" value="<?php echo html_escape((string) $version); ?>">
        <?php endif; ?>

        <!-- List of Assignment Items -->
        <div class="tw-space-y-6">
            <?php foreach ($items as $item): ?><article class="card mb-3 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-shadow-sm tw-overflow-hidden">
                <?php
                $file_capable = in_array($item->evidence_policy, ['file', 'either', 'both'], TRUE);
                $url_capable = in_array($item->evidence_policy, ['url', 'either', 'both'], TRUE);
                $policy_labels = [
                    'none' => 'Tidak memerlukan bukti',
                    'file' => 'Berkas',
                    'url' => 'Tautan',
                    'either' => 'Berkas atau tautan',
                    'both' => 'Berkas dan tautan',
                ];
                $clean_policy = isset($policy_labels[$item->evidence_policy]) ? $policy_labels[$item->evidence_policy] : $item->evidence_policy;
            ?>
                    <!-- Item Header -->
                    <div class="tw-border-b tw-border-slate-100 tw-bg-slate-50/70 tw-p-5 tw-flex tw-items-center tw-justify-between tw-gap-3">
                        <div class="tw-flex tw-items-center tw-gap-2.5">
                            <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-white tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200">
Butir #<?php echo html_escape((string) $item->display_order); ?>: <?php echo html_escape($item->indicator_code); ?>
                            </span>
                            <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-slate-100 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-medium tw-text-slate-600">
                                Kebijakan: <?php echo html_escape($clean_policy); ?>
                            </span>
                        </div>
                    </div>

                    <!-- 2-Column Responsive Body -->
                    <div class="card-body tw-grid tw-gap-6 lg:tw-grid-cols-2 tw-p-6">
                        <!-- LEFT COLUMN: Rujukan Pertanyaan & Instruksi -->
                        <div class="tw-space-y-4 tw-border-b lg:tw-border-b-0 lg:tw-border-r tw-border-slate-100 tw-pb-6 lg:tw-pb-0 lg:tw-pr-6">
                            <div>
                                <span class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-400 tw-mb-1">
                                    Pertanyaan Standar:
                                </span>
<h3><?php echo html_escape((string) $item->display_order . '. ' . $item->indicator_code . ' — ' . $item->indicator_title); ?></h3>
                                <p class="tw-text-sm tw-text-slate-800 tw-leading-relaxed tw-mt-1 tw-mb-0">
                                </p>
                            </div>

                            <?php if (!empty($item->evidence_instruction)): ?>
                                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100">
                                    <strong class="tw-block tw-text-xs tw-text-slate-600 tw-mb-1">Instruksi bukti:</strong>
                                    <p class="tw-text-xs tw-text-slate-700 tw-leading-relaxed tw-m-0">
                                        <?php echo nl2br(html_escape($item->evidence_instruction)); ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <div class="tw-rounded-lg tw-bg-slate-50/50 tw-p-3 tw-border tw-border-slate-100 tw-text-xs tw-text-slate-500">
                                <strong>Kebijakan bukti:</strong> <?php echo html_escape($clean_policy); ?>
                                <span class="tw-sr-only"><?php echo html_escape($item->evidence_policy); ?></span>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Realisasi & Bukti -->
                        <div class="tw-space-y-4">
                            <!-- Input Realisasi -->
                            <div>
                                <label for="realization-<?php echo (int) $item->assignment_item_id; ?>" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                                    Realisasi
                                </label>
                                <textarea class="form-control tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-p-3 tw-text-xs tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none <?php echo $editable ? '' : 'tw-bg-slate-50 tw-text-slate-700'; ?>" id="realization-<?php echo (int) $item->assignment_item_id; ?>" name="realization[<?php echo (int) $item->assignment_item_id; ?>]" rows="4" placeholder="Jelaskan implementasi dan pemenuhan standar..." <?php echo $editable ? '' : 'readonly'; ?>><?php echo html_escape($item->realization); ?></textarea>
                            </div>

                            <!-- Input URL Bukti (Jika didukung kebijakan) -->
                            <?php if ($editable && $url_capable): ?>
                                <div>
                                    <label for="evidence-url-<?php echo (int) $item->assignment_item_id; ?>" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                                        URL bukti
                                    </label>
                                    <input class="form-control tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2 tw-text-xs tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" type="url" id="evidence-url-<?php echo (int) $item->assignment_item_id; ?>" name="evidence_url[<?php echo (int) $item->assignment_item_id; ?>]" value="<?php echo html_escape($item->evidence_url); ?>" placeholder="https://drive.google.com/...">
                                </div>
                            <?php elseif (!$editable && $item->evidence_url): ?>
                                <div class="tw-rounded-xl tw-border tw-border-slate-100 tw-bg-slate-50 tw-p-3 tw-text-xs">
                                    <strong class="tw-text-slate-500 tw-block tw-mb-1">URL bukti:</strong>
                                    <a href="<?php echo html_escape($item->evidence_url); ?>" target="_blank" rel="noopener noreferrer" class="tw-font-medium tw-text-blue-600 hover:tw-underline tw-break-all tw-inline-flex tw-items-center tw-gap-1">
                                        <span><?php echo html_escape($item->evidence_url); ?></span>
                                        <?php echo $icon('external-link'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <!-- Upload Berkas Bukti (Jika didukung kebijakan) -->
                            <?php if ($file_capable): ?>
                                <div class="tw-pt-3 tw-border-t tw-border-slate-100">
                                    <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                                        <label for="evidence-file-<?php echo (int) $item->assignment_item_id; ?>" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700">
                                            File bukti
                                        </label>
                                        <span class="tw-text-[11px] tw-text-slate-400">PDF, JPG, PNG (Maks <?php echo html_escape((string) $upload_limit_mib); ?> MiB, maks 5 file)</span>
                                    </div>

                                    <!-- Upload Form Trigger -->
                                    <?php if ($editable && $file_capable): ?>
                                        <?php ob_start(); ?><?php echo form_open_multipart('auditee/spmi/item/' . (int) $item->assignment_item_id . '/evidence/upload', ['id' => 'spmi-evidence-upload-' . (int) $item->assignment_item_id]); ?>
                                            <input type="hidden" name="version" value="<?php echo html_escape((string) $version); ?>" form="spmi-evidence-upload-<?php echo (int) $item->assignment_item_id; ?>">
                                        <?php echo form_close(); ?>
                                        <?php $upload_forms[] = ob_get_clean(); ?>

                                        <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center tw-gap-2 tw-mb-3">
                                            <input class="form-control tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-2.5 tw-py-1.5 tw-text-xs tw-text-slate-700" type="file" id="evidence-file-<?php echo (int) $item->assignment_item_id; ?>" name="evidence" accept="application/pdf,image/jpeg,image/png" required form="spmi-evidence-upload-<?php echo (int) $item->assignment_item_id; ?>">
                                            <button type="submit" form="spmi-evidence-upload-<?php echo (int) $item->assignment_item_id; ?>" class="tw-button-secondary tw-text-xs tw-whitespace-nowrap">
                                                <?php echo $icon('upload-cloud'); ?>
                                                <span>Upload bukti item <?php echo html_escape((string) $item->display_order); ?></span>
                                            </button>
                                        </div>
                                        <span role="alert" data-evidence-upload-error class="tw-block tw-text-xs tw-text-red-600 tw-mb-2"></span>
                                    <?php endif; ?>

                                    <!-- File List Cards -->
                                    <?php if (empty($item->evidence)): ?>
                                        <p class="tw-text-xs tw-text-slate-400 tw-italic tw-m-0">Belum ada berkas bukti yang diunggah.</p>
                                    <?php else: ?>
                                        <ul class="tw-space-y-2 tw-p-0 tw-m-0 tw-list-none">
                                            <?php foreach ($item->evidence as $evidence): ?>
                                                <li class="tw-flex tw-items-center tw-justify-between tw-gap-2 tw-rounded-lg tw-border tw-border-slate-200 tw-bg-slate-50 tw-px-3 tw-py-2 tw-text-xs">
                                                    <div class="tw-flex tw-items-center tw-gap-2 tw-min-w-0">
                                                        <span class="tw-text-blue-600"><?php echo $icon('file'); ?></span>
                                                        <a href="<?php echo site_url('auditee/spmi/evidence/' . (int) $evidence->id . '/download'); ?>" class="tw-font-medium tw-text-blue-600 hover:tw-underline tw-truncate">
                                                            <?php echo html_escape($evidence->original_name); ?>
                                                        </a>
                                                    </div>

                                                    <?php if ($editable): ?>
                                                        <?php $delete_form_id = 'spmi-evidence-delete-' . (int) $evidence->id; ob_start(); ?>
                                                        <?php echo form_open('auditee/spmi/evidence/' . (int) $evidence->id . '/delete', ['id' => $delete_form_id, 'class' => 'd-inline tw-hidden']); ?>
                                                            <input type="hidden" name="version" value="<?php echo html_escape((string) $version); ?>">
                                                        <?php echo form_close(); ?>
                                                        <?php $delete_forms[] = ob_get_clean(); ?>

                                                        <button type="submit" form="<?php echo $delete_form_id; ?>" class="tw-text-[11px] tw-text-red-600 hover:tw-underline tw-bg-transparent tw-border-0 tw-p-0 tw-flex-shrink-0">
                                                            Hapus <?php echo html_escape($evidence->original_name); ?>
                                                        </button>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Global Bottom Action Footer -->
        <?php if ($editable): ?>
            <div class="tw-mt-8 tw-flex tw-items-center tw-justify-end tw-gap-3">
                <button type="submit" class="tw-button-secondary">
                    <?php echo $icon('save'); ?>
                    <span>Simpan draft</span>
                </button>
                <button type="button" id="spmi-final-submit" data-confirm-url="<?php echo html_escape(site_url('auditee/spmi/assignment/' . (int) $assignment->id . '/confirm')); ?>" class="tw-button-primary">
                    <?php echo $icon('check'); ?>
                    <span><?php echo $assignment->submission_status === 'returned_for_revision' ? 'Kirim ulang revisi' : 'Submit sekali'; ?></span>
                </button>
            </div>
            <?php echo form_close(); ?><script>
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
            </script>
        <?php endif; ?>

        <!-- Hidden Forms Container -->
        <div class="tw-hidden">
            <?php foreach ($upload_forms as $upload_form): ?>
                <?php echo $upload_form; ?>
            <?php endforeach; ?>
            <?php foreach ($delete_forms as $delete_form): ?>
                <?php echo $delete_form; ?>
            <?php endforeach; ?>
        </div>

        <?php if ($editable): ?>
            <script>
            (function () {
                var forms = document.querySelectorAll('form[id^="spmi-evidence-upload-"]');
                forms.forEach(function (uploadForm) {
                    uploadForm.addEventListener('submit', function (event) {
                        event.preventDefault();
                        var button = document.querySelector('button[form="' + uploadForm.id + '"]');
                        var fileInput = document.querySelector('input[type="file"][form="' + uploadForm.id + '"]');
                        var item = fileInput ? fileInput.closest('article') : null;
                        var error = item ? item.querySelector('[data-evidence-upload-error]') : null;
                        if (!fileInput || !fileInput.files.length) return;
                        if (button) button.disabled = true;
                        if (error) error.textContent = '';
                        fetch(uploadForm.action, { method: 'POST', body: new FormData(uploadForm), credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(function (response) { return response.json().then(function (payload) { return { ok: response.ok, payload: payload }; }); })
                            .then(function (result) {
                                var payload = result.payload;
                                if (!result.ok || !payload.success) {
                                    if (error) error.textContent = payload.message || 'Upload gagal.';
                                    return;
                                }
                                document.querySelectorAll('input[name="version"]').forEach(function (input) { input.value = payload.version; });
                                if (payload.csrf && payload.csrf.name && payload.csrf.hash) {
                                    document.querySelectorAll('input[name="' + payload.csrf.name + '"]').forEach(function (input) { input.value = payload.csrf.hash; });
                                }
                                var evidence = payload.evidence;
                                var entry = document.createElement('li');
                                entry.className = 'tw-flex tw-items-center tw-justify-between tw-gap-2 tw-rounded-lg tw-border tw-border-slate-200 tw-bg-slate-50 tw-px-3 tw-py-2 tw-text-xs';
                                var link = document.createElement('a');
                                link.href = '<?php echo site_url('auditee/spmi/evidence/'); ?>' + encodeURIComponent(evidence.id) + '/download';
                                link.textContent = evidence.original_name;
                                link.className = 'tw-font-medium tw-text-blue-600 hover:tw-underline tw-truncate';
                                entry.appendChild(link);
                                var deleteFormId = 'spmi-evidence-delete-' + evidence.id;
                                var deleteForm = document.createElement('form');
                                deleteForm.id = deleteFormId;
                                deleteForm.method = 'post';
                                deleteForm.action = '<?php echo site_url('auditee/spmi/evidence/'); ?>' + encodeURIComponent(evidence.id) + '/delete';
                                deleteForm.className = 'd-inline tw-hidden';
                                var versionInput = document.createElement('input');
                                versionInput.type = 'hidden';
                                versionInput.name = 'version';
                                versionInput.value = payload.version;
                                deleteForm.appendChild(versionInput);
                                var csrfInput = document.createElement('input');
                                csrfInput.type = 'hidden';
                                csrfInput.name = payload.csrf.name;
                                csrfInput.value = payload.csrf.hash;
                                deleteForm.appendChild(csrfInput);
                                document.body.appendChild(deleteForm);
                                var deleteButton = document.createElement('button');
                                deleteButton.type = 'submit';
                                deleteButton.setAttribute('form', deleteFormId);
                                deleteButton.textContent = 'Hapus ' + evidence.original_name;
                                deleteButton.className = 'tw-text-[11px] tw-text-red-600 hover:tw-underline tw-bg-transparent tw-border-0 tw-p-0 tw-flex-shrink-0';
                                entry.appendChild(deleteButton);
                                var evidenceList = item ? item.querySelector('ul') : null;
                                if (evidenceList) evidenceList.appendChild(entry);
                                fileInput.value = '';
                            })
                            .catch(function (uploadError) { if (error) error.textContent = uploadError.message; })
                            .finally(function () { if (button) button.disabled = false; });
                    });
                });
            }());
            </script>
        <?php endif; ?>

        <!-- Section: Riwayat Revisi Timeline -->
        <?php if (!empty($revision_history)): ?>
            <section class="tw-mt-8 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm" aria-labelledby="history-title">
                <div class="tw-flex tw-items-center tw-gap-2 tw-mb-4">
                    <span class="tw-text-blue-600"><?php echo $icon('history'); ?></span>
                    <h2 id="history-title" class="tw-text-base tw-font-bold tw-text-slate-950 tw-m-0">Riwayat revisi</h2>
                </div>

                <ol class="tw-space-y-4 tw-p-0 tw-m-0 tw-list-none">
                    <?php foreach ($revision_history as $event): ?>
                        <li class="tw-relative tw-pl-6 tw-border-l-2 tw-border-slate-200 tw-space-y-1">
                            <div class="tw-absolute -tw-left-[5px] tw-top-1.5 tw-h-2 tw-w-2 tw-rounded-full tw-bg-blue-600"></div>
                            <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-xs">
                                <span class="tw-font-mono tw-text-slate-500"><?php echo html_escape($event->created_at); ?></span>
                                <span class="tw-font-bold tw-text-slate-800"><?php echo html_escape($event->previous_status . ' → ' . $event->new_status); ?></span>
                                <span class="tw-inline-flex tw-items-center tw-rounded tw-bg-slate-100 tw-px-2 tw-py-0.5 tw-text-[11px] tw-text-slate-600">
                                    Versi <?php echo html_escape((string) $event->previous_version . ' → ' . (string) $event->resulting_version); ?>
                                </span>
                                <span class="tw-text-slate-500">oleh <?php echo html_escape($event->actor_name ?: $event->actor_email); ?></span>
                            </div>
                            <?php if (!empty($event->reason)): ?>
                                <p class="tw-text-xs tw-text-slate-700 tw-bg-slate-50 tw-p-2.5 tw-rounded-lg tw-border tw-border-slate-100 tw-m-0 tw-mt-1">
                                    <?php echo nl2br(html_escape($event->reason)); ?>
                                </p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </section>
        <?php endif; ?>
    </div>
</main>
<?php include APPPATH . 'views/layouts/footer.php'; ?>
