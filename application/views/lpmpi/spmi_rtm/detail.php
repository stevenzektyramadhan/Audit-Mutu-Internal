<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$reports_by_id = [];
foreach ($reports as $r) {
    $reports_by_id[(int) $r->report_id] = $r;
}

$role_labels = [
    'super_admin' => 'Super Admin',
    'admin_lpmpi' => 'Admin LPMPI',
    'auditor' => 'Auditor',
    'auditee' => 'Auditee',
];

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'printer' => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
        'edit' => '<path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'map-pin' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'file-text' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'check-square' => '<polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
        'plus' => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'alert-triangle' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'lock' => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
    ];
    return '<svg class="rtm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['file-text']) . '</svg>';
};
?>

<main id="rtm-root" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-7xl">
        <!-- Top Back Action -->
        <div class="ami-row-actions no-print tw-mb-5">
            <a class="btn-ami btn-outline-ami ami-action-btn rtm-back-link tw-text-sm" href="<?php echo site_url('lpmpi/spmi-rtm'); ?>">
                <?php echo $icon('arrow-left'); ?>
                <span>Kembali ke RTM SPMI</span>
            </a>
        </div>

        <!-- Header Card: Info & Action Buttons -->
        <header class="tw-mb-6 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm">
            <div class="tw-flex tw-flex-col tw-gap-4 sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-pb-6 tw-border-b tw-border-slate-100">
                <div>
                    <div class="tw-flex tw-items-center tw-gap-2.5 tw-mb-2">
                        <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-slate-100 tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200">
                            <?php echo html_escape($meeting->meeting_code); ?>
                        </span>
                        <?php if ($meeting->status === 'resolved'): ?>
                            <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-bg-emerald-50 tw-border tw-border-emerald-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-emerald-800">
                                <?php echo $icon('check-circle'); ?>
                                <span>Resolved</span>
                            </span>
                        <?php else: ?>
                            <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-bg-amber-50 tw-border tw-border-amber-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-amber-800">
                                <?php echo $icon('clock'); ?>
                                <span>Draft (Mutable)</span>
                            </span>
                        <?php endif; ?>
                    </div>
                    <h1 class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950 tw-m-0">
                        <?php echo html_escape($meeting->meeting_code . ' — ' . $meeting->meeting_title); ?>
                    </h1>
                </div>

                <div class="ami-row-actions no-print tw-flex tw-flex-wrap tw-items-center tw-gap-2.5">
                    <a class="btn-ami btn-outline-ami tw-button-secondary" href="<?php echo site_url('lpmpi/spmi-rtm/print/' . (int) $meeting->id); ?>">
                        <?php echo $icon('printer'); ?>
                        <span>Print</span>
                    </a>
                    <?php if ($meeting->status === 'draft'): ?>
                        <a class="btn-ami tw-button-primary" href="<?php echo site_url('lpmpi/spmi-rtm/edit/' . (int) $meeting->id); ?>">
                            <?php echo $icon('edit'); ?>
                            <span>Edit</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Metadata Grid -->
            <div class="tw-mt-6 tw-grid tw-gap-4 sm:tw-grid-cols-2 lg:tw-grid-cols-3 tw-text-sm">
                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100">
                    <span class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Tanggal Rapat</span>
                    <div class="tw-flex tw-items-center tw-gap-2 tw-text-slate-900 tw-font-medium">
                        <span class="tw-text-slate-400"><?php echo $icon('calendar'); ?></span>
                        <span><?php echo html_escape($meeting->meeting_date); ?></span>
                    </div>
                </div>

                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100">
                    <span class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Lokasi Pertemuan</span>
                    <div class="tw-flex tw-items-center tw-gap-2 tw-text-slate-900 tw-font-medium">
                        <span class="tw-text-slate-400"><?php echo $icon('map-pin'); ?></span>
                        <span><?php echo html_escape($meeting->location); ?></span>
                    </div>
                </div>

                <div class="tw-rounded-xl tw-bg-slate-50 tw-p-3.5 tw-border tw-border-slate-100">
                    <span class="tw-block tw-text-xs tw-font-semibold tw-text-slate-500 tw-mb-1">Status Rapat</span>
                    <span class="tw-text-slate-800 tw-font-semibold tw-capitalize">
                        <?php echo html_escape($meeting->status); ?>
                    </span>
                </div>
            </div>

            <!-- Status Notice -->
            <?php if ($meeting->status === 'resolved'): ?>
                <div class="tw-mt-5 tw-flex tw-items-center tw-gap-2.5 tw-rounded-lg tw-border tw-border-emerald-200 tw-bg-emerald-50/70 tw-px-3.5 tw-py-2.5 tw-text-xs tw-text-emerald-900">
                    <span class="tw-text-emerald-600"><?php echo $icon('lock'); ?></span>
                    <span>RTM resolved permanen dan hanya-baca. Seluruh butir keputusan dapat ditindaklanjuti.</span>
                </div>
            <?php else: ?>
                <div class="tw-mt-5 tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-3 tw-rounded-lg tw-border tw-border-amber-200 tw-bg-amber-50/70 tw-px-4 tw-py-3 tw-text-xs tw-text-amber-900">
                    <div class="tw-flex tw-items-center tw-gap-2.5">
                        <span class="tw-text-amber-600"><?php echo $icon('alert-triangle'); ?></span>
                        <span>RTM masih berstatus <strong>Draft</strong>. Anda dapat mengedit agenda sebelum menyelesaikan secara permanen.</span>
                    </div>
                    <?php echo form_open('lpmpi/spmi-rtm/resolve/' . (int) $meeting->id, ['class' => 'tw-m-0', 'onsubmit' => "return confirm('PERINGATAN: Apakah Anda yakin ingin menyelesaikan (resolve) RTM ini secara permanen? Setelah di-resolve, data rapat dan keputusan akan dikunci (hanya-baca) dan tidak dapat diubah lagi.');"]); ?>
                        <button class="btn-ami tw-button-danger tw-text-xs tw-py-1.5 tw-px-3.5" type="submit">
                            <?php echo $icon('check-circle'); ?>
                            <span>Resolve permanen</span>
                        </button>
                    <?php echo form_close(); ?>
                </div>
            <?php endif; ?>
        </header>

        <!-- Two Column Grid: Linked Reports & Participants -->
        <div class="tw-mb-8 tw-grid tw-gap-6 lg:tw-grid-cols-2">
            <!-- Linked Reports Section -->
            <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm" aria-labelledby="linked-reports-title">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <div class="tw-flex tw-items-center tw-gap-2">
                        <span class="tw-text-blue-600"><?php echo $icon('file-text'); ?></span>
                        <h2 id="linked-reports-title" class="tw-text-base tw-font-bold tw-text-slate-900 tw-m-0">Laporan M10</h2>
                    </div>
                    <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-slate-100 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-slate-700">
                        <?php echo count($reports); ?> Laporan
                    </span>
                </div>

                <?php if (empty($reports)): ?>
                    <p class="tw-text-sm tw-text-slate-500 tw-italic">Tidak ada laporan yang terhubung.</p>
                <?php else: ?>
                    <ul class="tw-space-y-2.5 tw-p-0 tw-m-0 tw-list-none">
                        <?php foreach ($reports as $report): ?>
                            <li class="tw-flex tw-items-start tw-justify-between tw-gap-2 tw-rounded-xl tw-border tw-border-slate-100 tw-bg-slate-50/80 tw-p-3.5">
                                <div class="tw-min-w-0">
                                    <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-white tw-px-2 tw-py-0.5 tw-rounded tw-border tw-border-slate-200">
                                        <?php echo html_escape($report->report_number . ' — ' . $report->cycle_title_snapshot); ?>
                                    </span>
                                    <div class="tw-mt-1.5 tw-text-xs tw-font-semibold tw-text-slate-800 tw-truncate">
                                        <?php echo html_escape($report->cycle_title_snapshot); ?>
                                    </div>
                                    <div class="tw-mt-0.5 tw-text-[11px] tw-text-slate-500">
                                        Siklus: <?php echo html_escape($report->cycle_code_snapshot); ?>
                                    </div>
                                </div>
                                <a class="tw-text-xs tw-font-semibold tw-text-blue-600 hover:tw-underline tw-whitespace-nowrap" href="<?php echo site_url('lpmpi/spmi-reports/detail/' . (int) $report->report_id); ?>">
                                    Lihat Detail &rarr;
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <!-- Participants Snapshot Section -->
            <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm" aria-labelledby="participants-title">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <div class="tw-flex tw-items-center tw-gap-2">
                        <span class="tw-text-blue-600"><?php echo $icon('users'); ?></span>
                        <h2 id="participants-title" class="tw-text-base tw-font-bold tw-text-slate-900 tw-m-0">Peserta snapshot</h2>
                    </div>
                    <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-slate-100 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-slate-700">
                        <?php echo count($participants); ?> Peserta
                    </span>
                </div>

                <?php if (empty($participants)): ?>
                    <p class="tw-text-sm tw-text-slate-500 tw-italic">Tidak ada peserta tercatat.</p>
                <?php else: ?>
                    <ul class="tw-space-y-2.5 tw-p-0 tw-m-0 tw-list-none">
                        <?php foreach ($participants as $participant):
                            $raw_role = (string) $participant->role_snapshot;
                            $clean_role = isset($role_labels[$raw_role]) ? $role_labels[$raw_role] : ucfirst(str_replace('_', ' ', $raw_role));
                        ?>
                            <li class="tw-flex tw-items-center tw-justify-between tw-gap-2 tw-rounded-xl tw-border tw-border-slate-100 tw-bg-slate-50/80 tw-p-3.5">
                                <div class="tw-min-w-0">
                                    <div class="tw-text-xs tw-font-bold tw-text-slate-900 tw-truncate">
                                        <?php echo html_escape($participant->name_snapshot); ?>
                                    </div>
                                    <div class="tw-text-[11px] tw-text-slate-500 tw-truncate tw-mt-0.5">
                                        <?php echo html_escape($participant->email_snapshot); ?>
                                    </div>
                                </div>
                                <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-white tw-border tw-border-slate-200 tw-px-2.5 tw-py-0.5 tw-text-[11px] tw-font-medium tw-text-slate-700 tw-whitespace-nowrap">
                                    <?php echo html_escape($clean_role); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </div>

        <!-- Decisions and Actions Section -->
        <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm" aria-labelledby="decisions-title">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-5">
                <div>
                    <h2 id="decisions-title" class="tw-text-lg tw-font-bold tw-text-slate-950 tw-m-0">Keputusan dan tindakan</h2>
                    <p class="tw-mt-1 tw-text-xs tw-text-slate-500">Ketetapan hasil rapat tinjauan manajemen dan tindak lanjut perbaikan mutu.</p>
                </div>
                <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-slate-100 tw-px-3 tw-py-1 tw-text-xs tw-font-bold tw-text-slate-700">
                    <?php echo count($decisions); ?> Butir Keputusan
                </span>
            </div>

            <!-- Desktop View: Structured Table -->
            <div class="tw-hidden md:tw-block tw-overflow-hidden tw-rounded-xl tw-border tw-border-slate-200 print-table">
                <table class="tw-w-full tw-text-left tw-text-sm">
                    <thead class="tw-border-b tw-border-slate-200 tw-bg-slate-50 tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wider tw-text-slate-500">
                        <tr>
                            <th class="tw-px-4 tw-py-3.5 tw-w-12 tw-text-center">No</th>
                            <th class="tw-px-4 tw-py-3.5 tw-w-2/5">Keputusan</th>
                            <th class="tw-px-4 tw-py-3.5 tw-w-2/5">Tindakan</th>
                            <th class="tw-px-4 tw-py-3.5 tw-min-w-[180px]">Sumber</th>
                            <th class="text-center tw-px-4 tw-py-3.5 tw-w-48 tw-text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="tw-divide-y tw-divide-slate-200">
                        <?php foreach ($decisions as $decision): ?>
                            <tr class="hover:tw-bg-slate-50/70 tw-align-top">
                                <td class="tw-px-4 tw-py-4 tw-text-center tw-font-bold tw-text-slate-500 tw-text-xs">
                                    <?php echo html_escape($decision->display_order); ?>
                                </td>
                                <td class="tw-px-4 tw-py-4">
                                    <div class="tw-text-xs tw-leading-relaxed tw-text-slate-900 tw-font-medium">
                                        <?php echo nl2br(html_escape($decision->decision_text)); ?>
                                    </div>
                                </td>
                                <td class="tw-px-4 tw-py-4">
                                    <div class="tw-text-xs tw-leading-relaxed tw-text-slate-800"><?php echo nl2br(html_escape($decision->action_text)); ?></div></td><td class="tw-px-4 tw-py-4"><?php
                                        $r_id = (int) $decision->report_id;
                                        if ($r_id > 0 && isset($reports_by_id[$r_id])):
                                            $linked_rep = $reports_by_id[$r_id];
                                    ?>
                                        <div class="tw-text-xs">
                                            <a class="tw-font-mono tw-font-bold tw-whitespace-nowrap tw-text-blue-600 hover:tw-underline tw-bg-blue-50 tw-px-2 tw-py-0.5 tw-rounded tw-border tw-border-blue-200 tw-inline-block" href="<?php echo site_url('lpmpi/spmi-reports/detail/' . $r_id); ?>" title="Buka Detail Laporan">
                                                <?php echo html_escape($linked_rep->report_number); ?>
                                            </a>
                                            <?php if ($decision->report_item_id): ?>
                                                <div class="tw-text-[11px] tw-text-slate-500 tw-mt-1">
                                                    Butir laporan #<?php echo html_escape($decision->report_item_id); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif ($r_id > 0): ?>
                                        <div class="tw-text-xs tw-text-slate-600">
                                            <?php echo html_escape('Laporan #' . $decision->report_id . ($decision->report_item_id ? ' / Item #' . $decision->report_item_id : '')); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="tw-text-xs tw-text-slate-400 tw-italic">-</span>
                                    <?php endif; ?></td>
                                <td class="text-center tw-px-4 tw-py-4 tw-text-center">
                                    <?php if ($meeting->status === "resolved" && (int) $decision->has_follow_up === 0): ?>
                                        <a class="btn-ami btn-outline-ami ami-action-btn" href="<?php echo site_url("lpmpi/spmi-follow-ups/create/" . (int) $decision->id); ?>">
                                            <?php echo $icon('plus'); ?>
                                            <span>+ Tindak Lanjut</span>
                                        </a>
                                    <?php elseif ($meeting->status === "resolved"): ?>
                                        <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-whitespace-nowrap tw-text-xs tw-font-medium tw-text-emerald-700 tw-bg-emerald-50 tw-px-2.5 tw-py-1 tw-rounded-full tw-border tw-border-emerald-200">
                                            <?php echo $icon('check-circle'); ?>
                                            <span>Sudah ditindaklanjuti</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="tw-text-xs tw-text-slate-400 tw-italic">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile View: Stacked Cards Per Decision -->
            <div class="tw-grid tw-gap-4 md:tw-hidden print-cards">
                <?php foreach ($decisions as $decision): ?>
                    <article class="tw-rounded-xl tw-border tw-border-slate-200 tw-bg-slate-50/70 tw-p-4 tw-space-y-3">
                        <div class="tw-flex tw-items-center tw-justify-between">
                            <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-800 tw-bg-white tw-px-2 tw-py-0.5 tw-rounded tw-border tw-border-slate-200">
                                Butir #<?php echo html_escape($decision->display_order); ?>
                            </span>
                            <div class="tw-text-[11px] tw-text-slate-600">
                                <?php
                                $r_id = (int) $decision->report_id;
                                if ($r_id > 0 && isset($reports_by_id[$r_id])):
                                    $linked_rep = $reports_by_id[$r_id];
                                ?>
                                    <span class="tw-text-slate-400">Sumber:</span>
                                    <a class="tw-font-mono tw-font-bold tw-whitespace-nowrap tw-text-blue-600 hover:tw-underline" href="<?php echo site_url('lpmpi/spmi-reports/detail/' . $r_id); ?>">
                                        <?php echo html_escape($linked_rep->report_number); ?>
                                    </a>
                                    <?php if ($decision->report_item_id): ?>
                                        <span class="tw-text-slate-500 tw-ml-1">· Butir laporan #<?php echo html_escape($decision->report_item_id); ?></span>
                                    <?php endif; ?>
                                <?php elseif ($r_id > 0): ?>
                                    <span>Sumber: Laporan #<?php echo (int) $decision->report_id; ?></span>
                                <?php else: ?>
                                    <span class="tw-text-slate-400">Sumber: -</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <strong class="tw-block tw-text-xs tw-text-slate-500 tw-mb-1">Keputusan:</strong>
                            <p class="tw-text-xs tw-leading-relaxed tw-text-slate-900 tw-font-medium tw-m-0">
                                <?php echo nl2br(html_escape($decision->decision_text)); ?>
                            </p>
                        </div>

                        <div>
                            <strong class="tw-block tw-text-xs tw-text-slate-500 tw-mb-1">Tindakan:</strong>
                            <p class="tw-text-xs tw-leading-relaxed tw-text-slate-800 tw-m-0">
                                <?php echo nl2br(html_escape($decision->action_text)); ?>
                            </p>
                        </div>

                        <?php if ($meeting->status === "resolved" && (int) $decision->has_follow_up === 0): ?>
                            <div class="tw-pt-2 tw-border-t tw-border-slate-200">
                                <a class="btn-ami btn-outline-ami ami-action-btn tw-button-primary tw-w-full tw-min-h-[44px] tw-text-sm tw-justify-center" href="<?php echo site_url("lpmpi/spmi-follow-ups/create/" . (int) $decision->id); ?>">
                                    <?php echo $icon('plus'); ?>
                                    <span>+ Tindak Lanjut</span>
                                </a>
                            </div>
                        <?php elseif ($meeting->status === "resolved"): ?>
                            <div class="tw-pt-2 tw-border-t tw-border-slate-200 tw-flex tw-items-center tw-justify-between">
                                <span class="tw-text-xs tw-text-slate-500">Status Tindak Lanjut:</span>
                                <span class="tw-inline-flex tw-items-center tw-gap-1 tw-text-xs tw-font-medium tw-text-emerald-700 tw-bg-emerald-50 tw-px-2.5 tw-py-1 tw-rounded-full tw-border tw-border-emerald-200">
                                    <?php echo $icon('check-circle'); ?>
                                    <span>Sudah ditindaklanjuti</span>
                                </span>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
