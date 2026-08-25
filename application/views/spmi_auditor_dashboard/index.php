<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-dashboard-logo-banner">
    <img src="<?= base_url('assets/img/logo-2.png'); ?>" alt="Logo LPM" class="ami-dashboard-logo">
    <div>
        <div class="ami-dashboard-logo-title">Lembaga Penjaminan Mutu</div>
        <div class="ami-dashboard-logo-subtitle">Sistem Audit Mutu Internal Perguruan Tinggi</div>
    </div>
</div>

<div class="ami-section-head mt-0">
    <div>
        <h2 class="ami-section-title">Dashboard SPMI Auditor</h2>
        <p class="text-muted mb-0">Ringkasan penugasan SPMI yang menjadi tanggung jawab Anda.</p>
    </div>
</div>

<section class="ami-panel mb-4" aria-labelledby="auditor-notifications">
    <div class="ami-panel-body">
        <h3 id="auditor-notifications" class="ami-section-title">Notifikasi</h3>
        <?php if (!$dashboard['notifications']): ?>
            <div class="ami-empty py-3">Belum ada notifikasi penugasan SPMI.</div>
        <?php else: ?>
            <?php foreach ($dashboard['notifications'] as $notification): ?>
                <?php $notification_tone = $notification['severity'] === 'danger' ? 'tone-rose' : ($notification['severity'] === 'warning' ? 'tone-amber' : 'tone-blue'); ?>
                <a class="ami-task-card" href="<?php echo site_url($notification['route']); ?>">
                    <div class="ami-task-icon <?php echo $notification_tone; ?>">
                        <i class="fas fa-bell" aria-hidden="true"></i>
                    </div>
                    <div class="ami-task-main">
                        <div class="ami-task-title">
                            <?php echo html_escape($notification['title']); ?>
                            <span class="ami-status <?php echo $notification_tone; ?> ml-2"><?php echo html_escape((string) $notification['count']); ?></span>
                        </div>
                        <div class="ami-task-meta"><?php echo html_escape($notification['detail']); ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<div class="ami-stat-grid">
    <?php foreach (['assignments' => ['Penugasan', 'fa-clipboard-list', 'tone-blue'], 'submissions_submitted' => ['Submission Terkirim', 'fa-paper-plane', 'tone-teal'], 'assessments_draft' => ['Penilaian Draft', 'fa-clock', 'tone-amber'], 'assessments_finalized' => ['Penilaian Final', 'fa-check-circle', 'tone-green'], 'due_soon' => ['Jatuh Tempo 7 Hari', 'fa-calendar-alt', 'tone-amber'], 'overdue' => ['Terlambat', 'fa-exclamation-circle', 'tone-rose']] as $key => $card): ?>
        <div class="ami-stat-card">
            <div class="ami-stat-icon <?php echo html_escape($card[2]); ?>">
                <i class="fas <?php echo html_escape($card[1]); ?>" aria-hidden="true"></i>
            </div>
            <div>
                <div class="ami-stat-label"><?php echo html_escape($card[0]); ?></div>
                <div class="ami-stat-value"><?php echo html_escape((string) $dashboard[$key]); ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if ((int) $dashboard['assignments'] === 0): ?>
    <div class="small text-muted">Belum ada penugasan SPMI untuk Anda.</div>
<?php endif; ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
