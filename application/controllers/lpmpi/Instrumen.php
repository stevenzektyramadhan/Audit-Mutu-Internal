<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Instrumen Controller — Upload Instrumen per Standar (Admin LPMPI)
 * @property Standar_model $Standar_model
 */
class Instrumen extends Admin_Lpmpi_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Standar_model');
    }

    public function index()
    {
        $data['title']        = 'Instrumen Standar';
        $data['page_title']   = 'Instrumen Standar';
        $data['page_subtitle'] = 'Upload dan kelola file instrumen per standar';
        $data['active_menu']  = 'instrumen';

        $data['standar_list'] = $this->Standar_model->get_all();

        $this->load->view('lpmpi/instrumen/index', $data);
    }
}