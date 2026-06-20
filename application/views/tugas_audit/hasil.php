<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$score_tones = [
    4 => 'tone-green',
    3 => 'tone-amber',
    2 => 'tone-blue',
    1 => 'tone-rose'
];
?>

<div class="mb-4 d-flex align-items-center px-3 py-3 rounded" style="background: var(--ami-panel); border: 1px solid var(--ami-border); gap: 10px;">
    <div class="tone-blue d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 6px;">
        <i class="fas fa-info-circle"></i>
    </div>
    <div class="text-light" style="font-size: 14px;">
        Hanya menampilkan tugas dengan status <span class="font-weight-bold" style="color: var(--ami-blue);">dinilai</span>. Skor rata-rata dihitung dari seluruh pertanyaan.
    </div>
</div>

<?php if (!empty($hasil)): ?>
    <?php foreach ($hasil as $row): ?>
    <div class="ami-panel mb-3">
        <div class="ami-panel-body p-4 d-flex justify-content-between align-items-start">
            <div>
                <h4 class="text-light mb-1" style="font-weight: 500;"><?php echo html_escape($row->auditee_nama); ?></h4>
                <div class="text-muted" style="font-size: 13px;">
                    <?php echo html_escape($row->nama_standar); ?> &middot; Auditor: <?php echo html_escape($row->auditor_nama); ?> &middot; 
                    <?php echo (new DateTime($row->created_at))->format('d M Y'); ?>
                </div>
                
                <div class="mt-3 d-flex flex-wrap gap-2">
                    <?php foreach ([4, 3, 2, 1] as $s): ?>
                        <?php if (!empty($row->stats[$s])): ?>
                            <span class="ami-status <?php echo $score_tones[$s]; ?>" style="font-size: 11.5px;">
                                Skor <?php echo $s; ?>: <?php echo $row->stats[$s]; ?> pertanyaan
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="text-right">
                <div style="font-size: 28px; line-height: 1; color: <?php echo $row->rata_rata >= 3 ? '#5cc865' : '#c88c5c'; ?>;">
                    <?php echo $row->rata_rata; ?>
                </div>
                <div class="text-muted" style="font-size: 12px; margin-top: 4px;">
                    rata-rata / 4
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <div class="mt-4 text-center text-muted" style="font-size: 13px;">
        Menampilkan <?php echo count($hasil); ?> hasil audit yang sudah selesai dinilai
    </div>
<?php else: ?>
    <div class="ami-panel text-center py-5">
        <div class="text-muted">Belum ada hasil audit dengan status dinilai.</div>
    </div>
<?php endif; ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
