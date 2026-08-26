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

    public function forgot_password()
    {
        $this->load->view('auth/forgot_password');
    }

    public function send_password_reset()
    {
        if (!$this->require_post()) {
            return;
        }

        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        if ($this->form_validation->run() !== FALSE) {
            $this->auth_service->request_password_reset($this->input->post('email', TRUE));
        }

        $this->session->set_flashdata('success', 'Jika alamat email terdaftar, instruksi pengaturan ulang kata sandi telah dikirim.');
        redirect('auth/forgot-password');
    }

    public function reset_password($token = NULL)
    {
        if (!$this->auth_service->has_active_password_reset_token($token)) {
            $this->load->view('auth/reset_password_invalid');
            return;
        }

        $this->load->view('auth/reset_password', ['token' => $token]);
    }

    public function update_password()
    {
        if (!$this->require_post()) {
            return;
        }

        $this->form_validation->set_rules('token', 'Tautan', 'required|regex_match[/\A[a-f0-9]{64}\z/]');
        $this->form_validation->set_rules('password', 'Kata sandi', 'required|min_length[12]');
        $this->form_validation->set_rules('password_confirmation', 'Konfirmasi kata sandi', 'required|matches[password]');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('auth/reset_password_invalid');
            return;
        }

        $result = $this->auth_service->reset_password(
            $this->input->post('token', TRUE),
            $this->input->post('password', FALSE)
        );

        if (!$result['success']) {
            $this->load->view('auth/reset_password_invalid');
            return;
        }

        $this->session->set_flashdata('success', $result['message']);
        redirect('auth');
    }

    private function require_post()
    {
        if ($this->input->method(TRUE) === 'POST') {
            return TRUE;
        }

        show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
        return FALSE;
    }

    private function redirect_authenticated_user()
    {
        if (!$this->session->userdata('user_id')) {
            return FALSE;
        }

        redirect($this->login_redirect());
        return TRUE;
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
