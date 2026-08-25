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
        <h2 class="ami-section-title">Dashboard SPMI</h2>
        <p class="text-muted mb-0">Ringkasan live PPEPP SPMI dari data M3-M12.</p>
    </div>
    <a class="btn btn-primary btn-ami" href="<?php echo site_url('lpmpi/spmi-dashboard/export?year=' . date('Y')); ?>">
        Export XLSX Tahunan
    </a>
</div>

<section class="ami-panel mb-4" aria-labelledby="spmi-notifications">
    <div class="ami-panel-body">
        <h3 id="spmi-notifications" class="ami-section-title">Notifikasi live</h3>
        <?php if (!$notifications): ?>
            <div class="ami-empty py-3">Belum ada notifikasi management SPMI.</div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
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

<section class="mb-4" aria-labelledby="spmi-account-totals">
    <div class="ami-section-head mt-0">
        <div>
            <h3 id="spmi-account-totals" class="ami-section-title">Akun terdaftar</h3>
            <p class="text-muted mb-0">Ringkasan akun pengguna yang terdaftar di AMI.</p>
        </div>
    </div>
    <div class="ami-stat-grid">
        <?php foreach ([
            'total_user' => ['label' => 'Total Pengguna Terdaftar', 'icon' => 'fa-users', 'tone' => 'tone-blue'],
            'total_auditor' => ['label' => 'Total Auditor', 'icon' => 'fa-user-check', 'tone' => 'tone-amber'],
            'total_auditee' => ['label' => 'Total Auditee', 'icon' => 'fa-user-edit', 'tone' => 'tone-rose'],
        ] as $key => $account): ?>
            <div class="ami-stat-card">
                <div class="ami-stat-icon <?php echo $account['tone']; ?>">
                    <i class="fas <?php echo $account['icon']; ?>" aria-hidden="true"></i>
                </div>
                <div>
                    <div class="ami-stat-label"><?php echo html_escape($account['label']); ?></div>
                    <div class="ami-stat-value"><?php echo html_escape((string) $account_totals[$key]); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php $empty_states = ['penetapan' => 'Belum ada data penetapan SPMI.', 'pelaksanaan' => 'Belum ada data pelaksanaan SPMI.', 'evaluasi' => 'Belum ada data evaluasi SPMI.', 'pengendalian' => 'Belum ada data pengendalian SPMI.', 'peningkatan' => 'Belum ada data peningkatan SPMI.']; ?>
<?php foreach ($metrics as $stage => $items): ?>
    <section class="mb-4" aria-labelledby="spmi-stage-<?php echo html_escape($stage); ?>">
        <div class="ami-section-head mt-0">
            <h3 id="spmi-stage-<?php echo html_escape($stage); ?>" class="ami-section-title"><?php echo html_escape(ucfirst($stage)); ?></h3>
        </div>
        <div class="ami-stat-grid">
            <?php foreach ($items as $key => $value): ?>
                <div class="ami-stat-card">
                    <div class="ami-stat-icon tone-blue">
                        <i class="fas fa-chart-bar" aria-hidden="true"></i>
                    </div>
                    <div>
                        <div class="ami-stat-label"><?php echo html_escape(ucwords(str_replace('_', ' ', $key))); ?></div>
                        <div class="ami-stat-value"><?php echo html_escape((string) $value); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php $stage_total = array_sum($items); ?>
        <?php if ($stage_total === 0): ?>
            <div class="small text-muted"><?php echo html_escape($empty_states[$stage]); ?></div>
        <?php endif; ?>
    </section>
<?php endforeach; ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
