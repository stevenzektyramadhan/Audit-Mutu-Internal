<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper(array('url', 'form'));
        require_once APPPATH . 'services/Auth_service.php';
        $this->auth_service = new Auth_service();
        $this->load->library('Audit_logger');
    }

    public function index()
    {
        if ($this->session->userdata('user_id')) {
            redirect($this->login_redirect());
        }

        $this->load->view('auth/login');
    }

    public function login()
    {
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->audit_logger->log('auth.login', 'failure', 'auth', 'login', ['reason' => 'validation']);
            $this->load->view('auth/login');
            return;
        }

        $email = $this->input->post('email', TRUE);
        $password = $this->input->post('password', TRUE);

        $result = $this->auth_service->login($email, $password);

        if ($result['success']) {
            $this->audit_logger->log('auth.login', 'success', 'auth', 'login');
            redirect($this->login_redirect());
        }

        $this->audit_logger->log('auth.login', 'failure', 'auth', 'login', ['reason' => 'credentials']);
        $this->session->set_flashdata('error', $result['message']);
        redirect('auth');
    }

    public function logout()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            return;
        }

        $this->audit_logger->log('auth.logout', 'success', 'auth', 'logout');
        $this->session->sess_destroy();
        redirect('auth');
    }

    private function login_redirect()
    {
        $login_redirects = [
            'super_admin' => 'lpmpi/spmi-dashboard',
            'admin_lpmpi' => 'lpmpi/spmi-dashboard',
            'auditor' => 'auditor/spmi-dashboard',
            'auditee' => 'auditee/spmi-dashboard',
        ];

        return $login_redirects[$this->session->userdata('role')] ?? 'dashboard';
    }
}
