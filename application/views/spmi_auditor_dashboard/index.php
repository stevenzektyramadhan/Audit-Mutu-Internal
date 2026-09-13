<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$icon = static function ($name) {
    $paths = [
        'clipboard-list' => '<path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M15 2H9a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1Z"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
        'send' => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'alert-triangle' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'bell' => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
        'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
    ];
    return '<svg class="aud-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['clipboard-list']) . '</svg>';
};
?>

<main id="auditor-root" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-7xl">
        <!-- Banner Lembaga -->
        <div class="tw-mb-6 tw-flex tw-items-center tw-gap-4 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-5 tw-shadow-sm">
            <img src="<?php echo base_url('assets/img/logo-2.png'); ?>" alt="Logo LPM" class="tw-h-12 tw-w-auto tw-object-contain tw-flex-shrink-0">
            <div>
                <h2 class="tw-text-base tw-font-bold tw-text-slate-900 tw-m-0">Lembaga Penjaminan Mutu</h2>
                <p class="tw-text-xs tw-text-slate-500 tw-m-0 tw-mt-0.5">Sistem Audit Mutu Internal Perguruan Tinggi</p>
            </div>
        </div>

        <!-- Header / Hero -->
        <div class="tw-mb-8">
            <p class="tw-mb-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-text-slate-500">Workspace Auditor</p>
            <h1 class="tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950 tw-m-0">Dashboard SPMI Auditor</h1>
            <p class="tw-mt-2 tw-max-w-2xl tw-text-sm tw-text-slate-500">
                Ringkasan operasional penugasan SPMI, pemantauan batas waktu, dan penilaian yang menjadi tanggung jawab Anda.
            </p>
        </div>

        <!-- Section: Perlu Tindakan / Notifikasi -->
        <?php if (!empty($dashboard['notifications'])): ?>
            <section class="tw-mb-8 tw-space-y-3" aria-labelledby="notifications-title">
                <div class="tw-flex tw-items-center tw-gap-2">
                    <span class="tw-text-amber-600"><?php echo $icon('bell'); ?></span>
                    <h2 id="notifications-title" class="tw-text-sm tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-m-0">
                        Perlu Tindakan Segera
                    </h2>
                </div>
                <div class="tw-grid tw-gap-3 sm:tw-grid-cols-2">
                    <?php foreach ($dashboard['notifications'] as $notification):
                        $is_danger = $notification['severity'] === 'danger';
                        $card_bg = $is_danger ? 'tw-bg-red-50/80 tw-border-red-200 hover:tw-border-red-300' : 'tw-bg-amber-50/80 tw-border-amber-200 hover:tw-border-amber-300';
                        $icon_color = $is_danger ? 'tw-text-red-600' : 'tw-text-amber-600';
                        $badge_bg = $is_danger ? 'tw-bg-red-100 tw-text-red-800' : 'tw-bg-amber-100 tw-text-amber-800';
                    ?>
                        <a href="<?php echo site_url($notification['route']); ?>" class="tw-flex tw-items-start tw-gap-3.5 tw-p-4 tw-rounded-2xl tw-border <?php echo $card_bg; ?> tw-transition tw-shadow-sm">
                            <div class="tw-flex tw-h-10 tw-w-10 tw-items-center tw-justify-center tw-rounded-xl tw-bg-white tw-shadow-sm <?php echo $icon_color; ?> tw-flex-shrink-0">
                                <?php echo $icon('alert-triangle'); ?>
                            </div>
                            <div class="tw-min-w-0 tw-flex-1">
                                <div class="tw-flex tw-items-center tw-justify-between tw-gap-2">
                                    <h3 class="tw-text-xs tw-font-bold tw-text-slate-900 tw-truncate tw-m-0">
                                        <?php echo html_escape($notification['title']); ?>
                                    </h3>
                                    <span class="tw-inline-flex tw-items-center tw-rounded-full <?php echo $badge_bg; ?> tw-px-2 tw-py-0.5 tw-text-[11px] tw-font-bold">
                                        <?php echo html_escape((string) $notification['count']); ?>
                                    </span>
                                </div>
                                <p class="tw-mt-1 tw-text-xs tw-text-slate-600 tw-m-0">
                                    <?php echo html_escape($notification['detail']); ?>
                                </p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Operational KPI Cards Grid -->
        <section aria-labelledby="kpi-title" class="tw-mb-8">
            <h2 id="kpi-title" class="tw-text-sm tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-4">
                Metrik Penugasan &amp; Penilaian
            </h2>

            <div class="tw-grid tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-4">
                <!-- 1. Total Penugasan -->
                <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-5 tw-shadow-sm">
                    <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                        <span class="tw-text-xs tw-font-semibold tw-text-slate-500">Penugasan</span>
                        <span class="tw-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-lg tw-bg-blue-50 tw-text-blue-600">
                            <?php echo $icon('clipboard-list'); ?>
                        </span>
                    </div>
                    <div class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950">
                        <?php echo (int) $dashboard['assignments']; ?>
                    </div>
                    <p class="tw-mt-1 tw-text-[11px] tw-text-slate-500 tw-m-0">Total penugasan aktif Anda</p>
                </div>

                <!-- 2. Pengajuan Terkirim -->
                <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-5 tw-shadow-sm">
                    <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                        <span class="tw-text-xs tw-font-semibold tw-text-slate-500">Pengajuan Terkirim</span>
                        <span class="tw-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-lg tw-bg-teal-50 tw-text-teal-600">
                            <?php echo $icon('send'); ?>
                        </span>
                    </div>
                    <div class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950">
                        <?php echo (int) $dashboard['submissions_submitted']; ?>
                    </div>
                    <p class="tw-mt-1 tw-text-[11px] tw-text-slate-500 tw-m-0">Siap untuk dievaluasi</p>
                </div>

                <!-- 3. Penilaian Draft -->
                <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-5 tw-shadow-sm">
                    <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                        <span class="tw-text-xs tw-font-semibold tw-text-slate-500">Penilaian Draft</span>
                        <span class="tw-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-lg tw-bg-amber-50 tw-text-amber-600">
                            <?php echo $icon('clock'); ?>
                        </span>
                    </div>
                    <div class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950">
                        <?php echo (int) $dashboard['assessments_draft']; ?>
                    </div>
                    <p class="tw-mt-1 tw-text-[11px] tw-text-slate-500 tw-m-0">Sedang dalam proses pengisian</p>
                </div>

                <!-- 4. Penilaian Final -->
                <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-5 tw-shadow-sm">
                    <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                        <span class="tw-text-xs tw-font-semibold tw-text-slate-500">Penilaian Final</span>
                        <span class="tw-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-lg tw-bg-emerald-50 tw-text-emerald-600">
                            <?php echo $icon('check-circle'); ?>
                        </span>
                    </div>
                    <div class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950">
                        <?php echo (int) $dashboard['assessments_finalized']; ?>
                    </div>
                    <p class="tw-mt-1 tw-text-[11px] tw-text-slate-500 tw-m-0">Selesai &amp; terkunci resmi</p>
                </div>

                <!-- 5. Jatuh Tempo 7 Hari -->
                <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-5 tw-shadow-sm">
                    <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                        <span class="tw-text-xs tw-font-semibold tw-text-slate-500">Jatuh Tempo 7 Hari</span>
                        <span class="tw-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-lg tw-bg-amber-50 tw-text-amber-600">
                            <?php echo $icon('calendar'); ?>
                        </span>
                    </div>
                    <div class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-tracking-tight <?php echo (int) $dashboard['due_soon'] > 0 ? 'tw-text-amber-600' : 'tw-text-slate-950'; ?>">
                        <?php echo (int) $dashboard['due_soon']; ?>
                    </div>
                    <p class="tw-mt-1 tw-text-[11px] tw-text-slate-500 tw-m-0">Batas akhir dalam 7 hari</p>
                </div>

                <!-- 6. Terlambat -->
                <div class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-5 tw-shadow-sm">
                    <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                        <span class="tw-text-xs tw-font-semibold tw-text-slate-500">Terlambat</span>
                        <span class="tw-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-lg tw-bg-red-50 tw-text-red-600">
                            <?php echo $icon('alert-triangle'); ?>
                        </span>
                    </div>
                    <div class="tw-text-2xl sm:tw-text-3xl tw-font-bold tw-tracking-tight <?php echo (int) $dashboard['overdue'] > 0 ? 'tw-text-red-600' : 'tw-text-slate-950'; ?>">
                        <?php echo (int) $dashboard['overdue']; ?>
                    </div>
                    <p class="tw-mt-1 tw-text-[11px] tw-text-slate-500 tw-m-0">Melewati tenggat siklus</p>
                </div>
            </div>
        </section>

        <!-- Quick Access to Penilaian SPMI -->
        <section class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-4 tw-rounded-2xl tw-border tw-border-blue-200 tw-bg-blue-50/70 tw-p-6">
            <div>
                <h3 class="tw-text-base tw-font-bold tw-text-blue-950 tw-m-0">Buka Ruang Kerja Penilaian SPMI</h3>
                <p class="tw-mt-1 tw-text-xs tw-text-blue-800 tw-m-0">
                    Masuk ke daftar penugasan untuk melakukan penilaian instrumen, penilaian rubrik, dan verifikasi bukti auditee.
                </p>
            </div>
            <a href="<?php echo site_url('auditor/spmi'); ?>" class="tw-button-primary tw-text-xs tw-whitespace-nowrap">
                <span>Buka Penilaian SPMI</span>
                <?php echo $icon('arrow-right'); ?>
            </a>
        </section>
    </div>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
