<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->library('auth_guard');
    }

    public function index()
    {
        $this->auth_guard->check();

        $role = $this->session->userdata('role');

        if ($role === 'super_admin') {
            $this->load->view('dashboard/super_admin');
            return;
        }

        if ($role === 'auditor') {
            $this->load->view('dashboard/auditor');
            return;
        }

        if ($role === 'auditee') {
            $this->load->view('dashboard/auditee');
            return;
        }

        show_error('Role tidak dikenal.', 403, 'Forbidden');
    }
}
