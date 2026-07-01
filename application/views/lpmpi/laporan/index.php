<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Laporan & Statistik</h2>
            <a class="btn-ami btn-outline-ami" href="#">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
        </div>

        <div class="ami-empty">
            <div class="ami-empty-icon"><i class="fas fa-chart-pie" aria-hidden="true"></i></div>
            <div class="ami-empty-title">Fitur Laporan & Statistik</div>
            <div>Fitur ini akan menampilkan rekap skor rata-rata per standar, filter periode & auditee, serta grafik skor.</div>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>