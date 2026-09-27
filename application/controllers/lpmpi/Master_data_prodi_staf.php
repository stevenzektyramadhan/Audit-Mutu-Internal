<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master_data_prodi_staf extends Admin_Lpmpi_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Profil_model');
    }

    public function index()
    {
        if ($this->input->method(TRUE) !== 'GET') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            return;
        }

        $prodi = $this->Profil_model->get_prodi_master_data();
        $jenjang_counts = [];
        $total_staff = 0;
        foreach ($prodi as $row) {
            $level = trim((string) ($row->jenjang ?? ''));
            if ($level !== '') {
                $jenjang_counts[$level] = isset($jenjang_counts[$level]) ? $jenjang_counts[$level] + 1 : 1;
            }
            $total_staff += (int) ($row->active_staff_count ?? 0);
        }

        $this->load->view('lpmpi/master_data_prodi_staf/index', [
            'title' => 'Master Data Prodi & Staf - AMI',
            'page_title' => 'Master Data Prodi & Staf',
            'page_subtitle' => 'Beranda / Management / Master Data Prodi & Staf',
            'active_menu' => 'master_data_prodi_staf',
            'prodi' => $prodi,
            'jenjang_counts' => $jenjang_counts,
            'total_prodi' => count($prodi),
            'total_levels' => count($jenjang_counts),
            'total_staff' => $total_staff,
        ]);
    }
}
