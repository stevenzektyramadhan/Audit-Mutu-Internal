<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$completed = $follow_up->status === 'completed';

$role_labels = [
    'super_admin' => 'Super Admin',
    'admin_lpmpi' => 'Admin LPMPI',
    'auditor' => 'Auditor',
    'auditee' => 'Auditee',
];
$clean_role = isset($role_labels[$follow_up->responsible_role_snapshot]) ? $role_labels[$follow_up->responsible_role_snapshot] : ucfirst(str_replace('_', ' ', (string) $follow_up->responsible_role_snapshot));

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'edit' => '<path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>',
        'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'play' => '<polygon points="5 3 19 12 5 21 5 3"/>',
        'file-text' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'lock' => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
        'shield-check' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
    ];
    return '<svg class="fu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['file-text']) . '</svg>';
};
?>

<main id="follow-ups-root" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-4xl">
        <!-- Top Back Action -->
        <div class="ami-row-actions no-print tw-mb-5">
            <a class="btn-ami btn-outline-ami ami-action-btn fu-back-link tw-text-sm" href="<?php echo site_url('lpmpi/spmi-follow-ups'); ?>">
                <?php echo $icon('arrow-left'); ?>
                <span>Kembali ke Tindak Lanjut RTM</span>
            </a>
        </div>

        <!-- Header Card -->
        <header class="tw-mb-6 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm">
            <div class="tw-flex tw-flex-col tw-gap-4 sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-pb-6 tw-border-b tw-border-slate-100">
                <div>
                    <div class="tw-flex tw-items-center tw-gap-2.5 tw-mb-2">
                        <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-slate-100 tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200">
                            <?php echo html_escape($follow_up->follow_up_code); ?>
                        </span>
                        <?php if ($follow_up->status === 'completed'): ?>
                            <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-bg-emerald-50 tw-border tw-border-emerald-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-emerald-800">
                                <?php echo $icon('check-circle'); ?>
                                <span>Selesai</span>
                            </span>
                        <?php elseif ($follow_up->status === 'in_progress'): ?>
                            <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-bg-blue-50 tw-border tw-border-blue-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-blue-800">
                                <?php echo $icon('play'); ?>
                                <span>Sedang berjalan</span>
                            </span>
                        <?php else: ?>
                            <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-bg-amber-50 tw-border tw-border-amber-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-amber-800">
                                <?php echo $icon('clock'); ?>
                                <span>Belum dimulai</span>
                            </span>
                        <?php endif; ?>
                    </div>
                    <h1 class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950 tw-m-0">
                        Tindak Lanjut RTM
                    </h1>
                </div>

                <?php if (!$completed && $follow_up->status === 'open'): ?>
                    <div class="ami-row-actions no-print">
                        <a class="btn-ami tw-button-primary" href="<?php echo site_url('lpmpi/spmi-follow-ups/edit/' . (int) $follow_up->id); ?>">
                            <?php echo $icon('edit'); ?>
                            <span>Edit</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Metadata Grid -->
            <div class="tw-mt-6 tw-grid tw-gap-4 sm:tw-grid-cols-2 lg:tw-grid-cols-3 tw-text-sm">
                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100 sm:tw-col-span-2">
                    <span class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">RTM Terkait</span>
                    <div class="tw-flex tw-items-center tw-gap-2">
                        <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-white tw-px-2 tw-py-0.5 tw-rounded tw-border tw-border-slate-200">
                            <?php echo html_escape($follow_up->meeting_code . ' — ' . $follow_up->meeting_title); ?>
                        </span>
                    </div>
                </div>

                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100">
                    <span class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Batas Waktu</span>
                    <div class="tw-flex tw-items-center tw-gap-1.5 tw-text-xs tw-text-slate-800 tw-font-medium">
                        <span class="tw-text-slate-400"><?php echo $icon('calendar'); ?></span>
                        <span><?php echo html_escape($follow_up->due_date ?: 'Tidak ditentukan'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Terminal Status Notice -->
            <?php if ($completed): ?>
                <div class="tw-mt-5 tw-flex tw-items-center tw-gap-2.5 tw-rounded-lg tw-border tw-border-emerald-200 tw-bg-emerald-50/70 tw-px-3.5 tw-py-2.5 tw-text-xs tw-text-emerald-900">
                    <span class="tw-text-emerald-600"><?php echo $icon('shield-check'); ?></span>
                    <span>Tindak lanjut completed dan bersifat terminal, hanya-baca.</span>
                </div>
            <?php endif; ?>
        </header>

        <!-- Section A: Penanggung Jawab -->
        <section class="tw-mb-6 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm" aria-labelledby="pic-section-title">
            <h2 id="pic-section-title" class="tw-text-base tw-font-bold tw-text-slate-950 tw-mb-4 tw-flex tw-items-center tw-gap-2">
                <span class="tw-text-blue-600"><?php echo $icon('user'); ?></span>
                <span>Penanggung Jawab</span>
            </h2>

            <div class="tw-flex tw-items-center tw-justify-between tw-gap-3 tw-p-4 tw-rounded-xl tw-border tw-border-slate-100 tw-bg-slate-50/80">
                <div class="tw-min-w-0">
                    <div class="tw-text-sm tw-font-bold tw-text-slate-900">
                        <?php echo html_escape($follow_up->responsible_name_snapshot); ?>
                    </div>
                    <div class="tw-text-xs tw-text-slate-500 tw-mt-0.5">
                        <?php echo html_escape($follow_up->responsible_email_snapshot); ?>
                    </div>
                </div>
                <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-white tw-border tw-border-slate-200 tw-px-3 tw-py-1 tw-text-xs tw-font-medium tw-text-slate-700 tw-whitespace-nowrap">
                    <?php echo html_escape($clean_role); ?>
                </span>
            </div>
        </section>

        <!-- Section B: Mandat RTM -->
        <section class="tw-mb-6 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm" aria-labelledby="mandate-section-title">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                <h2 id="mandate-section-title" class="tw-text-base tw-font-bold tw-text-slate-950 tw-flex tw-items-center tw-gap-2">
                    <span class="tw-text-blue-600"><?php echo $icon('file-text'); ?></span>
                    <span>Mandat Keputusan &amp; Tindakan RTM</span>
                </h2>
                <span class="tw-text-[11px] tw-text-slate-400">Snapshot M11</span>
            </div>

            <div class="tw-space-y-4">
                <div class="tw-rounded-xl tw-border tw-border-slate-100 tw-bg-slate-50/80 tw-p-4">
                    <span class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-500 tw-mb-1.5">Keputusan:</span>
                    <p class="tw-text-xs tw-leading-relaxed tw-text-slate-900 tw-font-medium tw-m-0">
                        <?php echo nl2br(html_escape($follow_up->decision_text_snapshot)); ?>
                    </p>
                </div>

                <div class="tw-rounded-xl tw-border tw-border-slate-100 tw-bg-slate-50/80 tw-p-4">
                    <span class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-500 tw-mb-1.5">Tindakan:</span>
                    <p class="tw-text-xs tw-leading-relaxed tw-text-slate-800 tw-m-0">
                        <?php echo nl2br(html_escape($follow_up->action_text_snapshot)); ?>
                    </p>
                </div>
            </div>

            <div class="tw-mt-4 tw-flex tw-items-center tw-gap-2 tw-text-[11px] tw-text-slate-500">
                <span><?php echo $icon('info'); ?></span>
                <span>Keputusan dan tindakan sumber M11 bersifat immutable; data di atas dibaca dari snapshot resmi.</span>
            </div>
        </section>

        <!-- Section C: Catatan Tindak Lanjut -->
        <?php if (!empty($follow_up->follow_up_note)): ?>
            <section class="tw-mb-6 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm">
                <h2 class="tw-text-base tw-font-bold tw-text-slate-950 tw-mb-3">
                    Catatan tindak lanjut
                </h2>
                <div class="tw-rounded-xl tw-border tw-border-slate-100 tw-bg-slate-50/80 tw-p-4">
                    <p class="tw-text-xs tw-leading-relaxed tw-text-slate-800 tw-m-0">
                        <?php echo nl2br(html_escape($follow_up->follow_up_note)); ?>
                    </p>
                </div>
            </section>
        <?php endif; ?>

        <!-- Section D: Progress & Penyelesaian -->
        <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm" aria-labelledby="progress-section-title">
            <h2 id="progress-section-title" class="tw-text-base tw-font-bold tw-text-slate-950 tw-mb-4">
                Progress &amp; Penyelesaian
            </h2>

            <!-- Lifecycle Timestamps Log -->
            <div class="tw-grid tw-gap-3 sm:tw-grid-cols-2 tw-text-xs tw-mb-6">
                <div class="tw-p-3 tw-rounded-xl tw-border tw-border-slate-100 tw-bg-slate-50/60">
                    <span class="tw-text-slate-400 tw-block tw-text-[11px]">Waktu Dimulai:</span>
                    <strong class="tw-text-slate-800">
                        <?php echo html_escape(!empty($follow_up->started_at) ? $follow_up->started_at : 'Belum dimulai'); ?>
                    </strong>
                </div>

                <div class="tw-p-3 tw-rounded-xl tw-border tw-border-slate-100 tw-bg-slate-50/60">
                    <span class="tw-text-slate-400 tw-block tw-text-[11px]">Waktu Selesai:</span>
                    <strong class="tw-text-slate-800">
                        <?php echo html_escape(!empty($follow_up->completed_at) ? $follow_up->completed_at : 'Belum selesai'); ?>
                    </strong>
                </div>
            </div>

            <?php if ($completed): ?>
                <!-- Completion Note Display -->
                <div>
                    <h3 class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-600 tw-mb-2">Catatan penyelesaian:</h3>
                    <div class="tw-rounded-xl tw-border tw-border-emerald-200 tw-bg-emerald-50/50 tw-p-4">
                        <p class="tw-text-xs tw-leading-relaxed tw-text-slate-900 tw-m-0">
                            <?php echo nl2br(html_escape($follow_up->completion_note)); ?>
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Transition Controls -->
                <div class="tw-pt-2 tw-border-t tw-border-slate-100">
                    <?php if ($follow_up->status === 'open'): ?>
                        <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-3 tw-p-4 tw-rounded-xl tw-bg-blue-50/50 tw-border tw-border-blue-100">
                            <div>
                                <h3 class="tw-text-sm tw-font-bold tw-text-slate-900 tw-m-0">Siap melaksanakan tindak lanjut?</h3>
                                <p class="tw-text-xs tw-text-slate-500 tw-mt-0.5 tw-mb-0">Ubah status menjadi In Progress untuk mencatat permulaan kerja.</p>
                            </div>
                            <?php echo form_open('lpmpi/spmi-follow-ups/transition/' . (int) $follow_up->id, ['class' => 'tw-m-0']); ?>
                                <input type="hidden" name="status" value="in_progress">
                                <button class="btn-ami tw-button-primary tw-text-xs" type="submit">
                                    <?php echo $icon('play'); ?>
                                    <span>Mulai tindak lanjut</span>
                                </button>
                            <?php echo form_close(); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($follow_up->status === 'in_progress'): ?>
                        <div class="tw-p-5 tw-rounded-xl tw-bg-slate-50 tw-border tw-border-slate-200">
                            <h3 class="tw-text-sm tw-font-bold tw-text-slate-900 tw-mb-1">Tandai Penyelesaian</h3>
                            <p class="tw-text-xs tw-text-slate-500 tw-mb-3">Tuliskan hasil evaluasi atau bukti konkret penyelesaian sebelum menutup tindak lanjut.</p>

                            <?php echo form_open('lpmpi/spmi-follow-ups/transition/' . (int) $follow_up->id, ['onsubmit' => "return confirm('Konfirmasi penyelesaian: Apakah Anda yakin tindak lanjut ini telah selesai dilaksanakan? Catatan penyelesaian akan dikunci permanen.');"]); ?>
                                <input type="hidden" name="status" value="completed">
                                <label for="completion_note" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                                    Catatan penyelesaian <span class="tw-text-red-500">*</span>
                                </label>
                                <textarea id="completion_note" name="completion_note" rows="3" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-p-3 tw-text-xs tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none tw-mb-3" placeholder="Jelaskan hasil pelaksanaan tindak lanjut secara detail..." required></textarea>
                                <div class="tw-flex tw-justify-end">
                                    <button class="btn-ami tw-button-primary tw-text-xs" type="submit">
                                        <?php echo $icon('check-circle'); ?>
                                        <span>Tandai completed</span>
                                    </button>
                                </div>
                            <?php echo form_close(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
