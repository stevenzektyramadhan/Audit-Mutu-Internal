<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Auditee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css" integrity="sha512-P5MgXuoE..." crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?php echo site_url('dashboard'); ?>">AMI Dashboard</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active"><a class="nav-link" href="<?php echo site_url('dashboard'); ?>">Home</a></li>
            </ul>
            <span class="navbar-text text-white mr-3">Auditee</span>
            <a class="btn btn-outline-light" href="<?php echo site_url('auth/logout'); ?>">Logout</a>
        </div>
    </div>
</nav>
<div class="container mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h3 class="card-title">Dashboard Auditee</h3>
            <p class="card-text">Selamat datang, <?php echo html_escape($this->session->userdata('nama')); ?>. Di sini Anda dapat melihat tugas audit dan mengisi jawaban serta link bukti.</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card border-success h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Tugas Saya</h5>
                    <p class="card-text">Lihat tugas audit yang telah diberikan kepada Anda.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card border-info h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Status Audit</h5>
                    <p class="card-text">Cek apakah tugas sudah diisi atau sudah dinilai.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.slim.min.js" integrity="sha512-DfXdz2..." crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.bundle.min.js" integrity="sha512-Piv4xV..." crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>
</html>
