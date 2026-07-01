<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Laporan Controller — Laporan & Statistik (Admin LPMPI)
 * @property Laporan_model $Laporan_model
 */
class Laporan extends Admin_Lpmpi_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Laporan_model');
    }

    public function index()
    {
        $data['title']        = 'Laporan & Statistik';
        $data['page_title']   = 'Laporan & Statistik';
        $data['page_subtitle'] = 'Rekap skor rata-rata per standar, filter periode & auditee';
        $data['active_menu']  = 'laporan';

        $this->load->view('lpmpi/laporan/index', $data);
    }
}