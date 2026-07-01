<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Penugasan Controller — Penugasan Auditor ke Auditee per Standar (Admin LPMPI)
 * @property Tugas_audit_model $Tugas_audit_model
 */
class Penugasan extends Admin_Lpmpi_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Tugas_audit_model');
    }

    public function index()
    {
        $data['title']        = 'Penugasan Auditor';
        $data['page_title']   = 'Penugasan Auditor';
        $data['page_subtitle'] = 'Atur penugasan auditor ke auditee per standar';
        $data['active_menu']  = 'penugasan';

        $data['tugas_list']   = $this->Tugas_audit_model->get_all_tugas();

        $this->load->view('lpmpi/penugasan/index', $data);
    }
}