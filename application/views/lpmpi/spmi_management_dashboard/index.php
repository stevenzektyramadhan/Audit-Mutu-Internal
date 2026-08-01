<?php defined('BASEPATH') OR exit('No direct script access allowed'); include APPPATH . 'views/layouts/header.php'; include APPPATH . 'views/layouts/sidebar.php'; ?>
<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap mb-4">
            <div><h2 class="ami-section-title">Dashboard SPMI</h2><p class="text-muted mb-0">Ringkasan live PPEPP SPMI dari data M3-M12.</p></div>
            <a class="btn btn-primary" href="<?php echo site_url('lpmpi/spmi-dashboard/export?year=' . date('Y')); ?>">Export XLSX Tahunan</a>
        </div>
        <?php $empty_states = ['penetapan' => 'Belum ada data penetapan SPMI.', 'pelaksanaan' => 'Belum ada data pelaksanaan SPMI.', 'evaluasi' => 'Belum ada data evaluasi SPMI.', 'pengendalian' => 'Belum ada data pengendalian SPMI.', 'peningkatan' => 'Belum ada data peningkatan SPMI.']; ?>
        <?php foreach ($metrics as $stage => $items): ?>
            <section class="mb-4" aria-labelledby="spmi-stage-<?php echo html_escape($stage); ?>">
                <h3 id="spmi-stage-<?php echo html_escape($stage); ?>" class="ami-section-title"><?php echo html_escape(ucfirst($stage)); ?></h3>
                <div class="row">
                    <?php foreach ($items as $key => $value): ?><div class="col-md-3 mb-3"><div class="ami-stat-card h-100"><div class="text-muted"><?php echo html_escape(ucwords(str_replace('_', ' ', $key))); ?></div><div class="h2 mb-0"><?php echo html_escape((string) $value); ?></div></div></div><?php endforeach; ?>
                </div>
                <?php $stage_total = array_sum($items); if ($stage_total === 0): ?><div class="small text-muted"><?php echo html_escape($empty_states[$stage]); ?></div><?php endif; ?>
            </section>
        <?php endforeach; ?>
        <section aria-labelledby="spmi-notifications"><h3 id="spmi-notifications" class="ami-section-title">Notifikasi live</h3><?php if (!$notifications): ?><div class="small text-muted">Belum ada notifikasi management SPMI.</div><?php else: ?><div class="list-group"><?php foreach ($notifications as $notification): ?><a class="list-group-item list-group-item-action" href="<?php echo site_url($notification['route']); ?>"><strong><?php echo html_escape($notification['title']); ?></strong><span class="badge badge-<?php echo html_escape($notification['severity']); ?> ml-2"><?php echo html_escape((string) $notification['count']); ?></span><div class="small text-muted"><?php echo html_escape($notification['detail']); ?></div></a><?php endforeach; ?></div><?php endif; ?></section>
    </div>
</div>
<?php include APPPATH . 'views/layouts/footer.php'; ?>
