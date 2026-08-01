<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_auditee_dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_guard');
        $this->auth_guard->only(['auditee']);
        $this->load->model('Spmi_auditee_dashboard_model');
    }

    public function index()
    {
        $this->load->view('spmi_auditee_dashboard/index', ['title' => 'Dashboard SPMI', 'page_title' => 'Dashboard SPMI', 'page_subtitle' => 'Beranda / Overview / Dashboard SPMI', 'active_menu' => 'spmi_auditee_dashboard', 'dashboard' => $this->Spmi_auditee_dashboard_model->dashboard((int) $this->session->userdata('user_id'))]);
    }
}
