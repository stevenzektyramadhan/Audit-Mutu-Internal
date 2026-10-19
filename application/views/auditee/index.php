<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tugas = isset($tugas) ? $tugas : [];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-section-head tw-mt-0">
    <h2 class="ami-section-title">Tugas yang perlu diisi</h2>
</div>

<div class="ami-alert ami-alert-info" style="background:#e6f1fb;color:#185fa5;border:0;border-radius:8px;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/></svg>
    Lengkapi jawaban singkat dan link bukti untuk setiap pertanyaan sebelum mengirim tugas.
</div>

<?php if (!empty($tugas)): ?>
    <?php foreach ($tugas as $item): ?>
        <div class="ami-task-card tw-items-center" style="border-left:3px solid #dc3545;">
            <div class="ami-task-icon tone-amber"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m4 20 4.5-1L19 8.5 15.5 5 5 15.5 4 20Z"/><path d="m14 6 4 4"/></svg></div>
            <div class="ami-task-main">
                <div class="ami-task-title"><?php echo html_escape($item->nama_standar); ?></div>
                <div class="ami-task-meta">
                    Auditor: <?php echo html_escape($item->auditor_nama); ?> &middot;
                    <?php echo html_escape((string) $item->jumlah_pertanyaan); ?> pertanyaan
                </div>
            </div>
            <a class="tw-button-primary btn-ami" href="<?php echo site_url('auditee/isi/' . (int) $item->id); ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m4 20 4.5-1L19 8.5 15.5 5 5 15.5 4 20Z"/><path d="m14 6 4 4"/></svg>Isi sekarang
            </a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="ami-panel">
        <div class="ami-empty">
            <div class="ami-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg></div>
            <div class="ami-empty-title">Semua tugas sudah ditangani</div>
            <div>Tidak ada tugas yang perlu diisi saat ini.</div>
            <a href="<?php echo site_url('auditee/tugas'); ?>" class="btn-outline-ami btn-ami"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>Lihat semua tugas</a>
        </div>
    </div>
<?php endif; ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
