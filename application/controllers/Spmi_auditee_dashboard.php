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
        require_once APPPATH . 'services/Spmi_auditee_workspace_service.php';
    }

    public function index()
    {
        $user_id = (int) $this->session->userdata('user_id');
        $dashboard = $this->Spmi_auditee_dashboard_model->dashboard($user_id);
        $attention_count = (new Spmi_auditee_workspace_service())->attention_count($user_id);
        $this->load->view('spmi_auditee_dashboard/index', ['title' => 'Dashboard SPMI', 'page_title' => 'Dashboard SPMI', 'page_subtitle' => 'Beranda / Overview / Dashboard SPMI', 'active_menu' => 'spmi_auditee_dashboard', 'dashboard' => $dashboard, 'menu_badges' => ['spmi_auditee_dashboard' => $attention_count, 'spmi_workspace' => $attention_count]]);
    }
}
