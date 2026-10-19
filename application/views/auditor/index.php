<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$siap_dinilai = isset($siap_dinilai) ? $siap_dinilai : [];
$sudah_dinilai = isset($sudah_dinilai) ? $sudah_dinilai : [];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-section-head tw-mt-0">
    <h2 class="ami-section-title">Tugas siap dinilai</h2>
</div>

<?php if (!empty($siap_dinilai)): ?>
    <?php foreach ($siap_dinilai as $item): ?>
        <div class="ami-task-card tw-items-center">
            <div class="ami-task-icon tone-blue"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 21h18M6 21V4h12v17M9 8h1m4 0h1m-6 4h1m4 0h1m-6 4h1m4 0h1"/></svg></div>
            <div class="ami-task-main">
                <div class="ami-task-title"><?php echo html_escape($item->auditee_nama); ?></div>
                <div class="ami-task-meta">
                    <?php echo html_escape($item->nama_standar); ?> &middot;
                    <?php echo html_escape((string) $item->jumlah_pertanyaan); ?> pertanyaan
                </div>
            </div>
            <a class="tw-button-primary btn-ami" href="<?php echo site_url('auditor/penilaian/form/' . (int) $item->id); ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg> Nilai sekarang
            </a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
        <div class="ami-panel"><div class="ami-empty">
        <div class="ami-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2h12M6 22h12M8 2v4a4 4 0 0 0 2 3.5L12 10l2-0.5A4 4 0 0 0 16 6V2m0 20v-4a4 4 0 0 0-2-3.5L12 14l-2 .5A4 4 0 0 0 8 18v4"/></svg></div>
        <div class="ami-empty-title">Belum ada tugas siap dinilai</div>
        <div>Tugas akan tersedia setelah auditee mengirim jawaban dan bukti.</div>
        <a href="<?php echo site_url('auditor/tugas'); ?>" class="btn-outline-ami btn-ami"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>Lihat semua tugas</a>
    </div></div>
<?php endif; ?>

<div class="ami-section-head">
    <h2 class="ami-section-title">Sudah dinilai</h2>
</div>

<div class="ami-panel">
    <?php if (!empty($sudah_dinilai)): ?>
        <div class="tw-overflow-x-auto">
            <table class="ami-table">
                <thead>
                <tr>
                    <th>Auditee</th>
                    <th>Standar</th>
                    <th>Rata-rata skor</th>
                    <th class="tw-text-right">Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($sudah_dinilai as $item): ?>
                    <tr>
                        <td class="tw-font-bold"><?php echo html_escape($item->auditee_nama); ?></td>
                        <td><?php echo html_escape($item->nama_standar); ?></td>
                        <td>
                            <span class="<?php echo (float) $item->rata_rata >= 3 ?'tw-text-green-600' : 'tw-text-amber-600'; ?> tw-font-bold">
                                <?php echo html_escape(number_format((float) $item->rata_rata, 1)); ?> / 4
                            </span>
                        </td>
                        <td class="tw-text-right">
                            <a class="ami-action-btn" href="<?php echo site_url('auditor/penilaian/form/' . (int) $item->id); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>Lihat detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="ami-empty">
            <div class="ami-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg></div>
            <div class="ami-empty-title">Belum ada penilaian selesai</div>
            <div>Tugas yang sudah dinilai akan dirangkum di bagian ini.</div>
        </div>
    <?php endif; ?>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
