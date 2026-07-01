<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Penetapan Controller — Halaman Penetapan (3 tab) (Admin LPMPI)
 */
class Penetapan extends Admin_Lpmpi_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Penetapan_model');
    }

    public function index()
    {
        $data['title']        = 'Penetapan';
        $data['page_title']   = 'Penetapan';
        $data['page_subtitle'] = 'Kelola penetapan pelaksanaan, pengendalian, dan peningkatan';
        $data['active_menu']  = 'penetapan';

        $this->load->view('lpmpi/penetapan/index', $data);
    }
}