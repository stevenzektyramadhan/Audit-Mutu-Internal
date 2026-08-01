<?php defined('BASEPATH') OR exit('No direct script access allowed'); include APPPATH . 'views/layouts/header.php'; include APPPATH . 'views/layouts/sidebar.php'; ?>
<?php $sections = [
    'penetapan' => [
        'title' => 'Penetapan',
        'empty' => 'Belum ada standar, indikator, atau target SPMI yang ditetapkan.',
        'items' => [
            'standards' => 'Standar',
            'indicators' => 'Indikator',
            'targets' => 'Target Tahunan',
        ],
    ],
    'pelaksanaan' => [
        'title' => 'Pelaksanaan',
        'empty' => 'Belum ada siklus, penugasan, atau submission SPMI yang berjalan.',
        'items' => [
            'cycles' => 'Siklus Aktif/Tersedia',
            'assignments' => 'Penugasan Snapshot',
            'submissions_draft' => 'Submission Draft',
            'submissions_submitted' => 'Submission Terkirim',
        ],
    ],
    'evaluasi' => [
        'title' => 'Evaluasi',
        'empty' => 'Belum ada penilaian auditor atau laporan SPMI yang dihasilkan.',
        'items' => [
            'assessments_draft' => 'Penilaian Draft',
            'assessments_finalized' => 'Penilaian Final',
            'reports' => 'Laporan SPMI',
        ],
    ],
    'pengendalian' => [
        'title' => 'Pengendalian',
        'empty' => 'Belum ada RTM resolved atau keputusan pengendalian SPMI.',
        'items' => [
            'meetings_resolved' => 'RTM Resolved',
            'decisions' => 'Keputusan RTM',
        ],
    ],
    'peningkatan' => [
        'title' => 'Peningkatan',
        'empty' => 'Belum ada tindak lanjut RTM yang terbuka atau diselesaikan.',
        'items' => [
            'follow_ups_open' => 'Tindak Lanjut Open',
            'follow_ups_in_progress' => 'Tindak Lanjut In Progress',
            'follow_ups_completed' => 'Tindak Lanjut Completed',
            'follow_ups_overdue' => 'Tindak Lanjut Overdue',
        ],
    ],
]; ?>
<div class="ami-panel">
    <div class="ami-panel-body">
        <h2 class="ami-section-title">Rekap PPEPP SPMI</h2>
        <p class="text-muted">Ringkasan live PPEPP dari data SPMI M3-M12 tanpa mengubah dashboard atau laporan AMI lama.</p>
        <?php foreach ($sections as $key => $section): ?>
            <?php $total = 0; foreach ($section['items'] as $item_key => $label) { $total += (int) $recap[$key][$item_key]; } ?>
            <section class="mb-4" aria-labelledby="ppepp-<?php echo html_escape($key); ?>">
                <h3 id="ppepp-<?php echo html_escape($key); ?>" class="ami-section-title"><?php echo html_escape($section['title']); ?></h3>
                <div class="row">
                    <?php foreach ($section['items'] as $item_key => $label): ?>
                        <div class="col-md-4 mb-3">
                            <div class="ami-stat-card h-100">
                                <div class="text-muted"><?php echo html_escape($label); ?></div>
                                <div class="h2 mb-0"><?php echo html_escape((string) $recap[$key][$item_key]); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($total === 0): ?>
                    <div class="small text-muted mt-2"><?php echo html_escape($section['empty']); ?></div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </div>
</div>
<?php include APPPATH . 'views/layouts/footer.php'; ?>
