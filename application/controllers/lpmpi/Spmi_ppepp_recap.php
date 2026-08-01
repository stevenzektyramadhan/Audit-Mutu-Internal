<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_ppepp_recap extends Admin_Lpmpi_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Spmi_ppepp_recap_model');
    }

    public function index()
    {
        $this->load->view('lpmpi/spmi_ppepp_recap/index', [
            'title' => 'Rekap PPEPP SPMI',
            'page_title' => 'Rekap PPEPP SPMI',
            'page_subtitle' => 'Beranda / Insights / Rekap PPEPP SPMI',
            'active_menu' => 'spmi_ppepp_recap',
            'recap' => $this->Spmi_ppepp_recap_model->recap(),
        ]);
    }
}
