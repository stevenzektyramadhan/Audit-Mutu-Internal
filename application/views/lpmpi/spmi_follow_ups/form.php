<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$follow_up = isset($follow_up) ? $follow_up : NULL;
$decision_id = isset($decision_id) ? $decision_id : 0;

$selected_decision = NULL;
if (isset($decisions)) {
    foreach ($decisions as $d) {
        if ((int) $d->id === (int) $decision_id) {
            $selected_decision = $d;
            break;
        }
    }
}

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'file-text' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
    ];
    return '<svg class="fu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['file-text']) . '</svg>';
};
?>

<main id="follow-ups-root" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-3xl">
        <!-- Top Back Action -->
        <div class="ami-row-actions no-print tw-mb-5">
            <a class="ami-action-btn fu-back-link tw-text-sm" href="<?php echo site_url('lpmpi/spmi-follow-ups'); ?>">
                <?php echo $icon('arrow-left'); ?>
                <span>Kembali ke Tindak Lanjut RTM</span>
            </a>
        </div>

        <?php echo validation_errors('<div class="tw-mb-6 tw-rounded-xl tw-border tw-border-red-200 tw-bg-red-50 tw-p-4 tw-text-sm tw-text-red-700">', '</div>'); ?>

        <div class="tw-mb-6">
            <p class="tw-mb-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-text-slate-500">Penugasan Kerja</p>
            <h1 class="tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950 tw-m-0">
                <?php echo html_escape($title); ?>
            </h1>
            <p class="tw-mt-2 tw-text-sm tw-text-slate-500">
                Tentukan penanggung jawab, batas waktu, dan catatan pelaksanaan perbaikan mutu.
            </p>
        </div>

        <!-- Section: Immutable Mandate Context -->
        <?php if ($selected_decision || $follow_up): ?>
            <section class="tw-mb-6 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-slate-50/80 tw-p-5">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                    <span class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-500">
                        Mandat Keputusan Sumber (Immutable)
                    </span>
                    <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-white tw-px-2 tw-py-0.5 tw-rounded tw-border tw-border-slate-200">
                        <?php echo html_escape($selected_decision ? $selected_decision->meeting_code : ($follow_up ? $follow_up->meeting_code ?? 'RTM' : '')); ?>
                    </span>
                </div>
                <div class="tw-space-y-2.5 tw-text-xs">
                    <div>
                        <strong class="tw-text-slate-700">Keputusan:</strong>
                        <p class="tw-text-slate-900 tw-font-medium tw-leading-relaxed tw-mt-0.5 tw-m-0">
                            <?php echo nl2br(html_escape($selected_decision ? $selected_decision->decision_text : ($follow_up ? $follow_up->decision_text_snapshot : ''))); ?>
                        </p>
                    </div>
                    <?php if ($selected_decision && !empty($selected_decision->action_text)): ?>
                        <div class="tw-border-t tw-border-slate-200/80 tw-pt-2">
                            <strong class="tw-text-slate-700">Tindakan Disepakati:</strong>
                            <p class="tw-text-slate-800 tw-leading-relaxed tw-mt-0.5 tw-m-0">
                                <?php echo nl2br(html_escape($selected_decision->action_text)); ?>
                            </p>
                        </div>
                    <?php elseif ($follow_up && !empty($follow_up->action_text_snapshot)): ?>
                        <div class="tw-border-t tw-border-slate-200/80 tw-pt-2">
                            <strong class="tw-text-slate-700">Tindakan Disepakati:</strong>
                            <p class="tw-text-slate-800 tw-leading-relaxed tw-mt-0.5 tw-m-0">
                                <?php echo nl2br(html_escape($follow_up->action_text_snapshot)); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Main Form Card -->
        <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm">
            <?php echo form_open($action, ['class' => 'tw-space-y-5']); ?>
                <?php if (!$follow_up): ?>
                    <div class="tw-hidden">
                        <label for="decision_id">Keputusan RTM resolved</label>
                        <select id="decision_id" class="form-control" disabled>
                            <option>
                                <?php foreach ($decisions as $decision): ?>
                                    <?php if ((int) $decision->id === (int) $decision_id): ?>
                                        <?php echo html_escape($decision->meeting_code . ' / #' . $decision->display_order . ' — ' . $decision->decision_text); ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </option>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- Penanggung Jawab -->
                <div>
                    <label for="responsible_user_id" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                        Penanggung Jawab (PIC) <span class="tw-text-red-500">*</span>
                    </label>
                    <select id="responsible_user_id" name="responsible_user_id" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" required>
                        <option value="">Pilih penanggung jawab...</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo (int) $user->id; ?>" <?php echo $follow_up && (int) $follow_up->responsible_user_id === (int) $user->id ? 'selected' : ''; ?>>
                                <?php echo html_escape($user->nama . ' — ' . ucfirst(str_replace('_', ' ', $user->role))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="tw-mt-1 tw-block tw-text-[11px] tw-text-slate-500">Pengguna yang bertanggung jawab mengeksekusi tindakan ini.</span>
                </div>

                <!-- Batas Waktu -->
                <div>
                    <label for="due_date" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                        Batas Waktu (Due Date)
                    </label>
                    <input id="due_date" name="due_date" type="date" class="tw-w-full sm:tw-w-64 tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" value="<?php echo html_escape($follow_up ? $follow_up->due_date : ''); ?>">
                    <span class="tw-mt-1 tw-block tw-text-[11px] tw-text-slate-500">Opsional. Kosongkan jika batas waktu belum ditetapkan secara definitif.</span>
                </div>

                <!-- Catatan Tindak Lanjut -->
                <div>
                    <label for="follow_up_note" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                        Catatan Tindak Lanjut
                    </label>
                    <textarea id="follow_up_note" name="follow_up_note" rows="4" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-p-3 tw-text-xs tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" maxlength="10000" placeholder="Tambahkan rincian teknis, milestone, atau catatan instruksi khusus..."><?php echo html_escape($follow_up ? $follow_up->follow_up_note : ''); ?></textarea>
                </div>

                <!-- Submit Action Buttons -->
                <div class="tw-pt-3 tw-border-t tw-border-slate-100 tw-flex tw-items-center tw-justify-end tw-gap-3">
                    <a class="btn-ami btn-outline-ami tw-button-secondary tw-text-xs" href="<?php echo site_url('lpmpi/spmi-follow-ups'); ?>">
                        Batal
                    </a>
                    <button class="btn-ami tw-button-primary tw-text-xs" type="submit">
                        <?php echo $icon('check'); ?>
                        <span>Simpan</span>
                    </button>
                </div>
            <?php echo form_close(); ?>
        </section>
    </div>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
