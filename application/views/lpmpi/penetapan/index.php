<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div class="ami-panel">
    <div class="ami-panel-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="ami-section-title m-0">Penetapan</h2>
        </div>

        <ul class="nav nav-tabs mb-4" id="penetapanTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="pelaksanaan-tab" data-toggle="tab" href="#pelaksanaan" role="tab" aria-controls="pelaksanaan" aria-selected="true">
                    <i class="fas fa-gavel"></i> Pelaksanaan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="pengendalian-tab" data-toggle="tab" href="#pengendalian" role="tab" aria-controls="pengendalian" aria-selected="false">
                    <i class="fas fa-sliders-h"></i> Pengendalian
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="peningkatan-tab" data-toggle="tab" href="#peningkatan" role="tab" aria-controls="peningkatan" aria-selected="false">
                    <i class="fas fa-chart-line"></i> Peningkatan
                </a>
            </li>
        </ul>

        <div class="tab-content" id="penetapanTabsContent">
            <div class="tab-pane fade show active" id="pelaksanaan" role="tabpanel" aria-labelledby="pelaksanaan-tab">
                <div class="ami-empty">
                    <div class="ami-empty-icon"><i class="fas fa-gavel" aria-hidden="true"></i></div>
                    <div class="ami-empty-title">Data Pelaksanaan</div>
                    <div>Fitur penetapan pelaksanaan akan segera tersedia.</div>
                </div>
            </div>
            <div class="tab-pane fade" id="pengendalian" role="tabpanel" aria-labelledby="pengendalian-tab">
                <div class="ami-empty">
                    <div class="ami-empty-icon"><i class="fas fa-sliders-h" aria-hidden="true"></i></div>
                    <div class="ami-empty-title">Data Pengendalian</div>
                    <div>Fitur penetapan pengendalian akan segera tersedia.</div>
                </div>
            </div>
            <div class="tab-pane fade" id="peningkatan" role="tabpanel" aria-labelledby="peningkatan-tab">
                <div class="ami-empty">
                    <div class="ami-empty-icon"><i class="fas fa-chart-line" aria-hidden="true"></i></div>
                    <div class="ami-empty-title">Data Peningkatan</div>
                    <div>Fitur penetapan peningkatan akan segera tersedia.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>