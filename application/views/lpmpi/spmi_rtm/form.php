<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$meeting = isset($meeting) ? $meeting : NULL;
$linked_ids = [];
foreach ((array) $linked_reports as $linked) {
    $linked_ids[] = (int) ($linked->report_id ?? $linked->id);
}
$participant_ids = [];
foreach ((array) $participants as $participant) {
    $participant_ids[] = (int) $participant->user_id;
}

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'map-pin' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        'file-text' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
        'trash' => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
        'plus' => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
    ];
    return '<svg class="rtm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['file-text']) . '</svg>';
};
?>

<main id="rtm-root" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-4xl">
        <!-- Top Back Action -->
        <div class="ami-row-actions no-print tw-mb-5">
            <a class="ami-action-btn rtm-back-link tw-text-sm" href="<?php echo site_url($meeting ? 'lpmpi/spmi-rtm/detail/' . (int) $meeting->id : 'lpmpi/spmi-rtm'); ?>">
                <?php echo $icon('arrow-left'); ?>
                <span>Kembali</span>
            </a>
        </div>

        <?php echo validation_errors('<div class="tw-mb-6 tw-rounded-xl tw-border tw-border-red-200 tw-bg-red-50 tw-p-4 tw-text-sm tw-text-red-700">', '</div>'); ?>

        <div class="tw-mb-6">
            <p class="tw-mb-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-text-slate-500">Formulir RTM</p>
            <h1 class="tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950 tw-m-0">
                <?php echo html_escape($title); ?>
            </h1>
            <p class="tw-mt-2 tw-text-sm tw-text-slate-500">
                Kelola agenda rapat tinjauan manajemen, tautkan laporan audit SPMI, peserta rapat, serta butir keputusan &amp; tindakan.
            </p>
        </div>

        <?php echo form_open($action, ['id' => 'rtm-form', 'class' => 'tw-space-y-6']); ?>
            <!-- Section 1: Meeting Information -->
            <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm">
                <h2 class="tw-text-base tw-font-bold tw-text-slate-950 tw-mb-4 tw-flex tw-items-center tw-gap-2">
                    <span class="tw-text-blue-600"><?php echo $icon('calendar'); ?></span>
                    <span>1. Informasi Pertemuan</span>
                </h2>

                <div class="tw-grid tw-gap-4 sm:tw-grid-cols-3">
                    <div>
                        <label for="meeting_code" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Kode Rapat <span class="tw-text-red-500">*</span>
                        </label>
                        <input id="meeting_code" name="meeting_code" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2.5 tw-text-sm tw-font-mono tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" maxlength="128" value="<?php echo html_escape($meeting ? $meeting->meeting_code : ''); ?>" placeholder="Contoh: RTM-2026-001" required>
                        <span class="tw-mt-1 tw-block tw-text-[11px] tw-text-slate-500">Huruf besar, angka, titik, strip.</span>
                    </div>

                    <div class="sm:tw-col-span-2">
                        <label for="meeting_title" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Judul Rapat <span class="tw-text-red-500">*</span>
                        </label>
                        <input id="meeting_title" name="meeting_title" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" maxlength="200" value="<?php echo html_escape($meeting ? $meeting->meeting_title : ''); ?>" placeholder="Agenda pembahasan hasil audit SPMI..." required>
                    </div>

                    <div>
                        <label for="meeting_date" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Tanggal Rapat <span class="tw-text-red-500">*</span>
                        </label>
                        <input id="meeting_date" name="meeting_date" type="date" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" value="<?php echo html_escape($meeting ? $meeting->meeting_date : ''); ?>" required>
                    </div>

                    <div class="sm:tw-col-span-2">
                        <label for="location" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Lokasi Pertemuan <span class="tw-text-red-500">*</span>
                        </label>
                        <input id="location" name="location" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" maxlength="200" value="<?php echo html_escape($meeting ? $meeting->location : ''); ?>" placeholder="Ruang Rapat Senat / Gedung Rektorat Lt. 2" required>
                    </div>
                </div>
            </section>

            <!-- Section 2: Linked Reports -->
            <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                    <h2 class="tw-text-base tw-font-bold tw-text-slate-950 tw-flex tw-items-center tw-gap-2">
                        <span class="tw-text-blue-600"><?php echo $icon('file-text'); ?></span>
                        <span>2. Laporan SPMI Terhubung <span class="tw-text-red-500">*</span></span>
                    </h2>
                    <span class="tw-text-xs tw-text-slate-500">Pilih minimal 1 laporan</span>
                </div>
                <p class="tw-text-xs tw-text-slate-500 tw-mb-4">
                    Centang laporan hasil audit yang menjadi dasar agenda pembahasan rapat ini.
                </p>

                <div class="tw-max-h-60 tw-overflow-y-auto tw-rounded-xl tw-border tw-border-slate-200 tw-p-3 tw-space-y-2 tw-bg-slate-50/50">
                    <?php foreach ($reports as $report):
                        $is_checked = in_array((int) $report->id, $linked_ids, TRUE);
                    ?>
                        <label class="tw-flex tw-items-start tw-gap-3 tw-p-2.5 tw-rounded-lg tw-border tw-border-slate-200 tw-bg-white hover:tw-border-blue-300 tw-cursor-pointer tw-transition">
                            <input type="checkbox" class="report-checkbox tw-mt-1 tw-h-4 tw-w-4 tw-rounded tw-border-slate-300 tw-text-blue-600 focus:tw-ring-blue-500" data-target-id="<?php echo (int) $report->id; ?>" <?php echo $is_checked ? 'checked' : ''; ?>>
                            <div class="tw-min-w-0 tw-flex-1">
                                <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-slate-100 tw-px-2 tw-py-0.5 tw-rounded tw-border tw-border-slate-200">
                                    <?php echo html_escape($report->report_number); ?>
                                </span>
                                <div class="tw-text-xs tw-font-semibold tw-text-slate-800 tw-mt-1">
                                    <?php echo html_escape($report->cycle_title_snapshot); ?>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <select id="report_ids" name="report_ids[]" class="tw-hidden" multiple required>
                    <?php foreach ($reports as $report): ?>
                        <option value="<?php echo (int) $report->id; ?>" <?php echo in_array((int) $report->id, $linked_ids, TRUE) ? 'selected' : ''; ?>>
                            <?php echo html_escape($report->report_number . ' — ' . $report->cycle_title_snapshot); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </section>

            <!-- Section 3: Participants -->
            <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                    <h2 class="tw-text-base tw-font-bold tw-text-slate-950 tw-flex tw-items-center tw-gap-2">
                        <span class="tw-text-blue-600"><?php echo $icon('users'); ?></span>
                        <span>3. Peserta Rapat <span class="tw-text-red-500">*</span></span>
                    </h2>
                    <span class="tw-text-xs tw-text-slate-500">Pilih minimal 1 peserta</span>
                </div>
                <p class="tw-text-xs tw-text-slate-500 tw-mb-4">
                    Centang seluruh pejabat, auditor, atau pimpinan unit yang hadir dalam RTM ini.
                </p>

                <div class="tw-max-h-64 tw-overflow-y-auto tw-rounded-xl tw-border tw-border-slate-200 tw-p-3 tw-space-y-2 tw-bg-slate-50/50">
                    <?php foreach ($users as $user):
                        $is_checked = in_array((int) $user->id, $participant_ids, TRUE);
                    ?>
                        <label class="tw-flex tw-items-center tw-justify-between tw-gap-3 tw-p-2.5 tw-rounded-lg tw-border tw-border-slate-200 tw-bg-white hover:tw-border-blue-300 tw-cursor-pointer tw-transition">
                            <div class="tw-flex tw-items-center tw-gap-3 tw-min-w-0">
                                <input type="checkbox" class="user-checkbox tw-h-4 tw-w-4 tw-rounded tw-border-slate-300 tw-text-blue-600 focus:tw-ring-blue-500" data-target-id="<?php echo (int) $user->id; ?>" <?php echo $is_checked ? 'checked' : ''; ?>>
                                <div class="tw-min-w-0">
                                    <div class="tw-text-xs tw-font-bold tw-text-slate-900 tw-truncate">
                                        <?php echo html_escape($user->nama); ?>
                                    </div>
                                    <div class="tw-text-[11px] tw-text-slate-500 tw-truncate">
                                        <?php echo html_escape($user->email); ?>
                                    </div>
                                </div>
                            </div>
                            <span class="tw-inline-flex tw-items-center tw-rounded tw-bg-slate-100 tw-border tw-border-slate-200 tw-px-2 tw-py-0.5 tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-600 tw-whitespace-nowrap">
                                <?php echo html_escape($user->role); ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <select id="participant_ids" name="participant_ids[]" class="tw-hidden" multiple required>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo (int) $user->id; ?>" <?php echo in_array((int) $user->id, $participant_ids, TRUE) ? 'selected' : ''; ?>>
                            <?php echo html_escape($user->nama . ' — ' . $user->role); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </section>

            <!-- Section 4: Decisions and Actions -->
            <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <div>
                        <h2 class="tw-text-base tw-font-bold tw-text-slate-950 tw-m-0">
                            4. Keputusan dan Tindakan <span class="tw-text-red-500">*</span>
                        </h2>
                        <p class="tw-mt-1 tw-text-xs tw-text-slate-500">
                            Rumuskan ketetapan dan langkah tindakan perbaikan mutu. Minimal 1 butir lengkap.
                        </p>
                    </div>
                </div>

                <div id="decisions-container" class="tw-space-y-4">
                    <?php
                    $decision_count = max(1, count((array) $decisions));
                    for ($i = 0; $i < $decision_count; $i++):
                        $decision = isset($decisions[$i]) ? $decisions[$i] : NULL;
                    ?>
                        <div class="decision-card tw-rounded-xl tw-border tw-border-slate-200 tw-bg-slate-50/70 tw-p-4 tw-space-y-3" data-index="<?php echo $i; ?>">
                            <div class="tw-flex tw-items-center tw-justify-between">
                                <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-800 tw-bg-white tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200 decision-label">
                                    Butir #<?php echo $i + 1; ?>
                                </span>
                            </div>

                            <div>
                                <label for="decision_<?php echo $i; ?>" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1">
                                    Keputusan <span class="tw-text-red-500">*</span>
                                </label>
                                <textarea id="decision_<?php echo $i; ?>" name="decisions[<?php echo $i; ?>][decision_text]" rows="2" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-p-3 tw-text-xs tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" placeholder="Isi ketetapan hasil rapat..." required><?php echo html_escape($decision ? $decision->decision_text : ''); ?></textarea>
                            </div>

                            <div>
                                <label for="action_<?php echo $i; ?>" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1">
                                    Tindakan Perbaikan <span class="tw-text-red-500">*</span>
                                </label>
                                <textarea id="action_<?php echo $i; ?>" name="decisions[<?php echo $i; ?>][action_text]" rows="2" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-p-3 tw-text-xs tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" placeholder="Langkah konkrit yang harus dilakukan..." required><?php echo html_escape($decision ? $decision->action_text : ''); ?></textarea>
                            </div>

                            <div class="tw-grid tw-gap-3 sm:tw-grid-cols-2">
                                <div>
                                    <label for="decision_report_<?php echo $i; ?>" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-600 tw-mb-1">
                                        Laporan Terkait (Opsional)
                                    </label>
                                    <select id="decision_report_<?php echo $i; ?>" name="decisions[<?php echo $i; ?>][report_id]" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2 tw-text-xs tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none">
                                        <option value="0">- Tidak ditautkan spesifik -</option>
                                        <?php foreach ($reports as $report): ?>
                                            <option value="<?php echo (int) $report->id; ?>" <?php echo $decision && (int) $decision->report_id === (int) $report->id ? 'selected' : ''; ?>>
                                                <?php echo html_escape($report->report_number); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="decision_item_<?php echo $i; ?>" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-600 tw-mb-1">
                                        ID Item Laporan (Opsional)
                                    </label>
                                    <input id="decision_item_<?php echo $i; ?>" name="decisions[<?php echo $i; ?>][report_item_id]" type="number" min="0" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2 tw-text-xs tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" value="<?php echo html_escape($decision ? $decision->report_item_id : ''); ?>" placeholder="Contoh: 12">
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="tw-mt-4">
                    <button type="button" id="btn-add-decision" class="tw-button-secondary tw-text-xs tw-w-full sm:tw-w-auto">
                        <?php echo $icon('plus'); ?>
                        <span>Tambah Butir Keputusan</span>
                    </button>
                </div>
            </section>

            <!-- Submit Section -->
            <div class="tw-flex tw-items-center tw-justify-end tw-gap-3">
                <a class="tw-button-secondary" href="<?php echo site_url($meeting ? 'lpmpi/spmi-rtm/detail/' . (int) $meeting->id : 'lpmpi/spmi-rtm'); ?>">
                    Batal
                </a>
                <button class="btn-ami tw-button-primary" type="submit">
                    <?php echo $icon('check'); ?>
                    <span>Simpan draft</span>
                </button>
            </div>
        <?php echo form_close(); ?>
    </div>
</main>

<script>
(function () {
    var reportCheckboxes = document.querySelectorAll('.report-checkbox');
    var reportSelect = document.getElementById('report_ids');
    if (reportCheckboxes && reportSelect) {
        reportCheckboxes.forEach(function (cb) {
            cb.addEventListener('change', function () {
                var targetId = this.getAttribute('data-target-id');
                var opt = reportSelect.querySelector('option[value="' + targetId + '"]');
                if (opt) opt.selected = this.checked;
            });
        });
    }

    var userCheckboxes = document.querySelectorAll('.user-checkbox');
    var userSelect = document.getElementById('participant_ids');
    if (userCheckboxes && userSelect) {
        userCheckboxes.forEach(function (cb) {
            cb.addEventListener('change', function () {
                var targetId = this.getAttribute('data-target-id');
                var opt = userSelect.querySelector('option[value="' + targetId + '"]');
                if (opt) opt.selected = this.checked;
            });
        });
    }

    var addBtn = document.getElementById('btn-add-decision');
    var container = document.getElementById('decisions-container');
    if (addBtn && container) {
        addBtn.addEventListener('click', function () {
            var cards = container.querySelectorAll('.decision-card');
            var nextIdx = cards.length;
            var template = cards[0].cloneNode(true);

            template.setAttribute('data-index', nextIdx);
            var label = template.querySelector('.decision-label');
            if (label) label.textContent = 'Butir #' + (nextIdx + 1);

            var decTextarea = template.querySelector('textarea[name*="[decision_text]"]');
            if (decTextarea) {
                decTextarea.id = 'decision_' + nextIdx;
                decTextarea.name = 'decisions[' + nextIdx + '][decision_text]';
                decTextarea.value = '';
                var decLabel = template.querySelector('label[for^="decision_"]');
                if (decLabel) decLabel.setAttribute('for', 'decision_' + nextIdx);
            }

            var actTextarea = template.querySelector('textarea[name*="[action_text]"]');
            if (actTextarea) {
                actTextarea.id = 'action_' + nextIdx;
                actTextarea.name = 'decisions[' + nextIdx + '][action_text]';
                actTextarea.value = '';
                var actLabel = template.querySelector('label[for^="action_"]');
                if (actLabel) actLabel.setAttribute('for', 'action_' + nextIdx);
            }

            var repSelect = template.querySelector('select[name*="[report_id]"]');
            if (repSelect) {
                repSelect.id = 'decision_report_' + nextIdx;
                repSelect.name = 'decisions[' + nextIdx + '][report_id]';
                repSelect.value = '0';
                var repLabel = template.querySelector('label[for^="decision_report_"]');
                if (repLabel) repLabel.setAttribute('for', 'decision_report_' + nextIdx);
            }

            var itemInput = template.querySelector('input[name*="[report_item_id]"]');
            if (itemInput) {
                itemInput.id = 'decision_item_' + nextIdx;
                itemInput.name = 'decisions[' + nextIdx + '][report_item_id]';
                itemInput.value = '';
                var itemLabel = template.querySelector('label[for^="decision_item_"]');
                if (itemLabel) itemLabel.setAttribute('for', 'decision_item_' + nextIdx);
            }

            container.appendChild(template);
        });
    }
})();
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
