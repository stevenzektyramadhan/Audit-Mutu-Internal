<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Akun Controller — Manajemen Akun Auditee & Auditor (Admin LPMPI)
 * @property User_model $User_model
 */
class Akun extends Admin_Lpmpi_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
    }

    public function index()
    {
        $data['title']       = 'Akun Auditee & Auditor';
        $data['page_title']  = 'Akun Auditee / Auditor';
        $data['page_subtitle'] = 'Kelola akun auditee dan auditor';
        $data['active_menu'] = 'akun';

        $data['auditee_list']  = $this->User_model->get_by_role('auditee');
        $data['auditor_list']  = $this->User_model->get_by_role('auditor');

        $this->load->view('lpmpi/akun/index', $data);
    }
}