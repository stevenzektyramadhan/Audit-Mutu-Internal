<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    /** @var CI_Session */
    public $session;

    /** @var Auth_guard */
    public $auth_guard;

    /** @var Dashboard_service */
    protected $dashboard_service;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->library('auth_guard');
        require_once APPPATH . 'services/Dashboard_service.php';
        $this->dashboard_service = new Dashboard_service();
    }

    public function index()
    {
        $this->auth_guard->check();

        $role = $this->session->userdata('role');
        $user_id = (int) $this->session->userdata('user_id');

        if ($role === 'super_admin' || $role === 'admin_lpmpi') {
            $data = $this->dashboard_service->get_super_admin_data();
            $data['task_status_chart'] = json_encode([
                'labels' => ['Belum diisi', 'Diisi', 'Dinilai'],
                'values' => [
                    (int) $data['task_status_counts'][STATUS_BELUM_DIISI],
                    (int) $data['task_status_counts'][STATUS_DIISI],
                    (int) $data['task_status_counts'][STATUS_DINILAI],
                ],
            ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            $this->load->view('dashboard/super_admin', $data);
            return;
        }

        if ($role === 'auditor') {
            $data = $this->dashboard_service->get_auditor_data($user_id);
            $this->load->view('dashboard/auditor', $data);
            return;
        }

        if ($role === 'auditee') {
            $data = $this->dashboard_service->get_auditee_data($user_id);
            $this->load->view('dashboard/auditee', $data);
            return;
        }

        show_error('Role tidak dikenal.', 403, 'Forbidden');
    }
}
