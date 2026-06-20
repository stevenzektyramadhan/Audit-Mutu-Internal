<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tugas = isset($tugas) ? $tugas : [];
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-section-head mt-0">
    <h2 class="ami-section-title">Tugas yang perlu diisi</h2>
</div>

<div class="alert alert-primary d-flex align-items-center" style="background:#e6f1fb;color:#185fa5;border:0;border-radius:8px;">
    <i class="fas fa-info-circle mr-2" aria-hidden="true"></i>
    Lengkapi jawaban singkat dan link bukti untuk setiap pertanyaan sebelum mengirim tugas.
</div>

<?php if (!empty($tugas)): ?>
    <?php foreach ($tugas as $item): ?>
        <div class="ami-task-card align-items-center" style="border-left:3px solid #dc3545;">
            <div class="ami-task-icon tone-amber"><i class="fas fa-pen" aria-hidden="true"></i></div>
            <div class="ami-task-main">
                <div class="ami-task-title"><?php echo html_escape($item->nama_standar); ?></div>
                <div class="ami-task-meta">
                    Auditor: <?php echo html_escape($item->auditor_nama); ?> &middot;
                    <?php echo html_escape((string) $item->jumlah_pertanyaan); ?> pertanyaan
                </div>
            </div>
            <a class="btn btn-primary btn-ami" href="<?php echo site_url('auditee/isi/' . (int) $item->id); ?>">
                <i class="fas fa-pen" aria-hidden="true"></i>Isi sekarang
            </a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="ami-panel">
        <div class="ami-empty">Tidak ada tugas yang perlu diisi saat ini.</div>
    </div>
<?php endif; ?>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
