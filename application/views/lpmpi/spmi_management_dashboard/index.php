<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<?php
$stage_details = [
    'penetapan' => ['title' => 'Penetapan', 'description' => 'Tetapkan standar', 'caption' => 'Fondasi standar dan sasaran mutu.', 'icon' => 'compass', 'tone' => 'tone-blue', 'empty' => 'Belum ada data penetapan SPMI.'],
    'pelaksanaan' => ['title' => 'Pelaksanaan', 'description' => 'Jalankan proses', 'caption' => 'Siklus dan submission yang berjalan.', 'icon' => 'play', 'tone' => 'tone-teal', 'empty' => 'Belum ada data pelaksanaan SPMI.'],
    'pengendalian' => ['title' => 'Pengendalian', 'description' => 'Kendalikan hasil', 'caption' => 'RTM dan keputusan perbaikan.', 'icon' => 'sliders', 'tone' => 'tone-rose', 'empty' => 'Belum ada data pengendalian SPMI.'],
    'peningkatan' => ['title' => 'Peningkatan', 'description' => 'Tingkatkan mutu', 'caption' => 'Tindak lanjut menuju mutu berkelanjutan.', 'icon' => 'arrow-up', 'tone' => 'tone-green', 'empty' => 'Belum ada data peningkatan SPMI.'],
];
$spmi_icons = [
    'arrow-up' => '<path d="M12 19V5m0 0-6 6m6-6 6 6"/>',
    'badge' => '<path d="M12 3 5 6v5c0 4.5 3 8.3 7 10 4-1.7 7-5.5 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/>',
    'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
    'bolt' => '<path d="m13 2-9 12h7l-1 8 9-12h-7l1-8Z"/>',
    'calendar' => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
    'clipboard' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1M9 12h6M9 16h4"/>',
    'compass' => '<circle cx="12" cy="12" r="9"/><path d="m15 9-2 4-4 2 2-4 4-2Z"/>',
    'crosshairs' => '<circle cx="12" cy="12" r="7"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/>',
    'download' => '<path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/>',
    'flag' => '<path d="M5 21V4m0 0c4-3 6 3 14 0v10c-8 3-10-3-14 0"/>',
    'file' => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v5h4M9 13h6M9 17h6"/>',
    'gauge' => '<path d="M4 14a8 8 0 1 1 16 0"/><path d="m12 14 3-4M6 18h12"/>',
    'layers' => '<path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5M3 16l9 5 9-5"/>',
    'play' => '<circle cx="12" cy="12" r="9"/><path d="m10 8 6 4-6 4V8Z"/>',
    'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
    'sliders' => '<path d="M4 6h16M4 12h16M4 18h16M8 4v4M16 10v4M10 16v4"/>',
    'tasks' => '<path d="M9 6h11M9 12h11M9 18h11M4 6h.01M4 12h.01M4 18h.01"/>',
    'triangle' => '<path d="m12 3 9 17H3L12 3Z"/><path d="M12 9v5M12 17h.01"/>',
    'users' => '<path d="M16 20v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 20v-1a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
    'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
    'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    'chart' => '<path d="M4 19V5M4 19h16"/><path d="m7 15 3-4 3 2 5-6"/>',
    'list' => '<path d="M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01"/>',
    'target' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
    'rotate' => '<path d="M20 11a8 8 0 0 0-14.5-3L3 11"/><path d="M3 5v6h6M4 13a8 8 0 0 0 14.5 3L21 13"/><path d="M21 19v-6h-6"/>',
    'user-check' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M16 11l2 2 4-4"/>',
    'send' => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
    'file-check' => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v5h4M9 15l2 2 4-4"/>',
    'clipboard-check' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1M9 13l2 2 4-4"/>',
    'file-text' => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v5h4M9 13h6M9 17h4"/>',
    'handshake' => '<path d="m5 12 3-3 4 4 4-4 3 3M3 14l3 3 3-3M21 14l-3 3-3-3M8 9l2-2h4l2 2"/>',
    'git-branch' => '<circle cx="6" cy="6" r="3"/><circle cx="18" cy="18" r="3"/><path d="M6 9v3a6 6 0 0 0 6 6h3M18 9v3"/>',
    'circle-dot' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/>',
    'loader' => '<path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>',
    'check-square' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="m8 12 3 3 5-6"/>',
    'alarm' => '<circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2M5 3 2 6M19 3l3 3"/>',
];
$spmi_metric_icons = [
    'standards' => 'badge', 'indicators' => 'gauge', 'targets' => 'flag', 'cycles' => 'calendar',
    'assignments' => 'user-check', 'submissions_draft' => 'file-text', 'submissions_submitted' => 'send',
    'assessments_draft' => 'clipboard', 'assessments_finalized' => 'clipboard-check', 'reports' => 'chart',
    'meetings_resolved' => 'handshake', 'decisions' => 'git-branch', 'follow_ups_open' => 'circle-dot',
    'follow_ups_in_progress' => 'loader', 'follow_ups_completed' => 'check-square', 'follow_ups_overdue' => 'alarm',
];
$spmi_icon = static function ($name) use ($spmi_icons) {
    return '<svg class="spmi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($spmi_icons[$name] ?? $spmi_icons['file']) . '</svg>';
};
?>
<div id="spmi-dashboard-root" class="tw-mx-auto tw-max-w-screen-2xl">
    <section class="tw-flex tw-flex-col tw-items-start tw-justify-between tw-gap-6 tw-rounded-lg tw-border tw-p-5 tw-mb-6 sm:tw-flex-row sm:tw-items-center sm:tw-p-7 ami-dashboard-logo-banner" aria-labelledby="spmi-dashboard-title">
        <div class="tw-flex tw-items-center tw-gap-4">
            <img src="<?php echo html_escape(base_url('assets/img/logo-2.png')); ?>" alt="Logo LPM" class="tw-h-[52px] tw-w-[52px] tw-rounded-xl tw-bg-white tw-object-contain tw-p-1.5">
            <div><div class="spmi-eyebrow">Sistem Penjaminan Mutu Internal</div><h2 id="spmi-dashboard-title">Dashboard SPMI</h2><p>Pulse PPEPP untuk membantu tim mutu menjaga ritme perbaikan.</p></div>
        </div>
        <a class="tw-inline-flex tw-items-center tw-gap-2 tw-whitespace-nowrap tw-rounded-md tw-border tw-px-4 tw-py-2.5 tw-font-bold spmi-export" href="<?php echo site_url('lpmpi/spmi-dashboard/export?year=' . date('Y')); ?>"><?php echo $spmi_icon('download'); ?> Export XLSX Tahunan</a>
    </section>

    <section class="tw-mb-6 tw-rounded-lg tw-border tw-p-4 sm:tw-p-6 spmi-flow" aria-labelledby="spmi-flow-title"><div class="tw-mb-4 tw-flex tw-items-center tw-justify-between tw-gap-4"><div><div class="spmi-eyebrow">Siklus mutu</div><h3 id="spmi-flow-title">Alur PPEPP</h3></div><span class="spmi-flow-note">4 tahap terhubung</span></div><div class="spmi-stepper"><?php foreach ($stage_details as $stage => $detail): ?><a class="spmi-flow-step <?php echo $detail['tone']; ?>" href="#spmi-stage-<?php echo html_escape($stage); ?>"><span class="spmi-flow-marker"><span class="spmi-flow-number"><?php echo html_escape(str_pad((string) (array_search($stage, array_keys($stage_details), TRUE) + 1), 2, '0', STR_PAD_LEFT)); ?></span><?php echo $spmi_icon($detail['icon']); ?></span><span class="spmi-flow-copy"><strong><?php echo html_escape($detail['title']); ?></strong><span><?php echo html_escape($detail['description']); ?></span></span></a><?php endforeach; ?></div></section>

    <section class="tw-mb-6 tw-grid tw-grid-cols-1 tw-gap-6 xl:tw-grid-cols-[1.15fr_.85fr]" aria-labelledby="spmi-activity-title">
         <div class="ami-panel spmi-activity-panel"><div class="ami-panel-body"><div class="tw-mb-4 tw-flex tw-items-center tw-justify-between tw-gap-4"><div><div class="spmi-eyebrow">Live feed</div><h3 id="spmi-activity-title">Aktivitas</h3></div><span class="spmi-heading-icon"><?php echo $spmi_icon('bolt'); ?></span></div><?php if (!$notifications): ?><div class="ami-empty">Belum ada notifikasi management SPMI.</div><?php else: foreach ($notifications as $notification): ?><?php $notification_tone = $notification['severity'] === 'danger' ? 'tone-rose' : ($notification['severity'] === 'warning' ? 'tone-amber' : 'tone-blue'); ?><a class="ami-task-card" href="<?php echo site_url($notification['route']); ?>"><div class="ami-task-icon <?php echo $notification_tone; ?>"><?php echo $spmi_icon('bell'); ?></div><div class="ami-task-main"><div class="ami-task-title"><?php echo html_escape($notification['title']); ?><span class="ami-status <?php echo $notification_tone; ?> tw-ml-2"><?php echo html_escape((string) $notification['count']); ?></span></div><div class="ami-task-meta"><?php echo html_escape($notification['detail']); ?></div></div></a><?php endforeach; endif; ?></div></div>
         <div class="ami-panel spmi-attention-panel"><div class="ami-panel-body"><div class="tw-mb-4 tw-flex tw-items-center tw-justify-between tw-gap-4"><div><div class="spmi-eyebrow">Prioritas</div><h3>Perlu perhatian</h3></div><span class="spmi-heading-icon"><?php echo $spmi_icon('crosshairs'); ?></span></div><div class="tw-grid tw-gap-2"><?php foreach ([['label' => 'Submission masih draft', 'value' => $metrics['pelaksanaan']['submissions_draft'], 'icon' => 'file', 'tone' => 'tone-amber'], ['label' => 'Penilaian masih draft', 'value' => $metrics['evaluasi']['assessments_draft'], 'icon' => 'clipboard', 'tone' => 'tone-blue'], ['label' => 'Tindak lanjut overdue', 'value' => $metrics['peningkatan']['follow_ups_overdue'], 'icon' => 'triangle', 'tone' => 'tone-rose']] as $attention): ?><div class="tw-grid tw-grid-cols-[30px_1fr_auto] tw-items-center tw-gap-2.5 tw-rounded-md tw-border tw-p-3 spmi-attention-item"><span class="spmi-attention-icon <?php echo $attention['tone']; ?>"><?php echo $spmi_icon($attention['icon']); ?></span><span><?php echo html_escape($attention['label']); ?></span><strong><?php echo html_escape((string) $attention['value']); ?></strong></div><?php endforeach; ?></div></div></div>
    </section>

    <section class="tw-mb-6 tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-grid-cols-2 xl:tw-grid-cols-4" aria-label="KPI utama SPMI">
        <?php foreach ([
            ['label' => 'Total Pengguna', 'value' => $account_totals['total_user'], 'icon' => 'users', 'tone' => 'tone-blue'],
            ['label' => 'Siklus Aktif', 'value' => $metrics['pelaksanaan']['cycles'], 'icon' => 'calendar', 'tone' => 'tone-teal'],
            ['label' => 'Penugasan', 'value' => $metrics['pelaksanaan']['assignments'], 'icon' => 'tasks', 'tone' => 'tone-amber'],
            ['label' => 'Laporan', 'value' => $metrics['evaluasi']['reports'], 'icon' => 'file', 'tone' => 'tone-rose'],
        ] as $kpi): ?><div class="ami-stat-card tw-min-h-[108px]"><div class="ami-stat-icon <?php echo $kpi['tone']; ?>"><?php echo $spmi_icon($kpi['icon']); ?></div><div><div class="ami-stat-label"><?php echo html_escape($kpi['label']); ?></div><div class="ami-stat-value"><?php echo html_escape((string) $kpi['value']); ?></div></div></div><?php endforeach; ?>
    </section>

    <section class="tw-mb-6 spmi-summary" aria-labelledby="spmi-summary-title">
        <div class="tw-mb-4 tw-flex tw-items-end tw-justify-between tw-gap-4">
            <div><div class="spmi-eyebrow">Ringkasan siklus</div><h3 id="spmi-summary-title">Ringkasan PPEPP</h3></div>
            <span class="spmi-flow-note">4 tahap mutu</span>
        </div>
        <div class="ami-stat-grid spmi-summary-grid">
            <?php foreach (array_keys($stage_details) as $stage): ?>
                <?php $items = $metrics[$stage]; ?>
                <section id="spmi-stage-<?php echo html_escape($stage); ?>" class="ami-panel spmi-stage <?php echo $stage === 'peningkatan' ? 'spmi-stage-wide' : ''; ?>" aria-labelledby="spmi-stage-title-<?php echo html_escape($stage); ?>">
                    <div class="ami-panel-body"><div class="spmi-stage-heading"><span class="spmi-stage-icon <?php echo $stage_details[$stage]['tone']; ?>"><?php echo $spmi_icon($stage_details[$stage]['icon']); ?></span><div><h3 id="spmi-stage-title-<?php echo html_escape($stage); ?>"><?php echo html_escape($stage_details[$stage]['title']); ?></h3><p><?php echo html_escape($stage_details[$stage]['caption']); ?></p></div></div>
                    <div class="spmi-metric-list"><?php foreach ($items as $key => $value): ?><div class="tw-grid tw-grid-cols-[30px_1fr_auto] tw-items-center tw-gap-2.5 tw-rounded-md tw-border tw-p-3 spmi-attention-item spmi-metric-row"><span class="spmi-attention-icon spmi-metric-icon <?php echo $stage_details[$stage]['tone']; ?>"><?php echo $spmi_icon($spmi_metric_icons[$key] ?? $stage_details[$stage]['icon']); ?></span><span class="spmi-metric-label"><?php echo html_escape(ucwords(str_replace('_', ' ', $key))); ?></span><strong><?php echo html_escape((string) $value); ?></strong></div><?php endforeach; ?></div>
                    <?php if (array_sum($items) === 0): ?><div class="ami-empty spmi-stage-empty"><?php echo html_escape($stage_details[$stage]['empty']); ?></div><?php endif; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
