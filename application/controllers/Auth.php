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
        require_once APPPATH . 'services/System_settings_service.php';
        $this->system_settings_service = new System_settings_service();
        require_once APPPATH . 'services/Organization_logo_service.php';
        $this->organization_logo_service = new Organization_logo_service();
        $this->load->model('System_settings_model');
        $this->load->library('Audit_logger');
    }

    public function index()
    {
        if ($this->session->userdata('user_id')) {
            redirect($this->login_redirect());
        }

        $this->render_login();
    }

    public function login()
    {
        if (!$this->require_post()) {
            return;
        }

        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->audit_logger->log('auth.login', 'failure', 'auth', 'login', ['reason' => 'validation']);
            $this->render_login();
            return;
        }

        $email = $this->input->post('email', TRUE);
        $password = $this->input->post('password', TRUE);
        $client_ip = $this->input->ip_address();
        $turnstile = $this->system_settings_service->turnstile_public_settings();
        if (!empty($turnstile['configuration_invalid'])) {
            $this->audit_logger->log('auth.login', 'failure', 'auth', 'login', ['reason' => 'turnstile_configuration']);
            $this->render_login(['login_error' => 'Verifikasi keamanan gagal. Silakan coba lagi.']);
            return;
        }
        if (!empty($turnstile['enabled'])) {
            $budget = $this->auth_service->consume_login_request_budget($email, $client_ip);
            if (empty($budget['allowed'])) {
                $this->audit_logger->log('auth.login', 'failure', 'auth', 'login', ['reason' => 'rate_limited']);
                set_status_header(429);
                header('Retry-After: ' . max(1, (int) $budget['retry_after']));
                header('Cache-Control: no-store');
                $this->render_login(['login_error' => 'Too many sign-in attempts. Please try again later.']);
                return;
            }
            $captcha = $this->system_settings_service->verify_turnstile_response($this->input->post('cf-turnstile-response', TRUE), $client_ip);
            if (empty($captcha['success'])) {
                $this->audit_logger->log('auth.login', 'failure', 'auth', 'login', ['reason' => 'turnstile']);
                $this->render_login(['login_error' => $captcha['message']]);
                return;
            }
        }

        $result = $this->auth_service->login($email, $password, $client_ip);

        if ($result['success']) {
            $this->audit_logger->log('auth.login', 'success', 'auth', 'login');
            redirect($this->login_redirect());
        }

        if (!empty($result['rate_limited'])) {
            $this->audit_logger->log('auth.login', 'failure', 'auth', 'login', ['reason' => 'rate_limited']);
            set_status_header(429);
            header('Retry-After: ' . max(1, (int) $result['retry_after']));
            header('Cache-Control: no-store');
            $this->render_login(['login_error' => 'Too many sign-in attempts. Please try again later.']);
            return;
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
        $this->render_auth_view('auth/forgot_password');
    }

    public function send_password_reset()
    {
        if (!$this->require_post()) {
            return;
        }

        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        if ($this->form_validation->run() !== FALSE) {
            $this->auth_service->request_password_reset($this->input->post('email', TRUE), $this->input->ip_address());
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

    private function render_login(array $data = [])
    {
        $data['turnstile'] = $this->system_settings_service->turnstile_public_settings();
        $this->render_auth_view('auth/login', $data);
    }

    private function render_auth_view($view, array $data = [])
    {
        $profile = $this->System_settings_model->profile();
        $settings = $this->System_settings_model->settings();
        $app_name = ($settings && property_exists($settings, 'app_name') && $settings->app_name !== NULL && trim((string) $settings->app_name) !== '')
            ? trim((string) $settings->app_name)
            : 'Audit Mutu Internal';
        $app_short_name = ($settings && property_exists($settings, 'app_short_name') && $settings->app_short_name !== NULL && trim((string) $settings->app_short_name) !== '')
            ? trim((string) $settings->app_short_name)
            : 'AMI';
        $login_hero_title = ($settings && property_exists($settings, 'login_hero_title') && $settings->login_hero_title !== NULL && trim((string) $settings->login_hero_title) !== '')
            ? trim((string) $settings->login_hero_title)
            : 'Budaya mutu tumbuh dari proses yang jelas dan bukti yang tepercaya.';
        $login_hero_description = ($settings && property_exists($settings, 'login_hero_description') && $settings->login_hero_description !== NULL && trim((string) $settings->login_hero_description) !== '')
            ? trim((string) $settings->login_hero_description)
            : 'Ruang kerja terpadu untuk pelaksanaan, penilaian, dan tindak lanjut Sistem Penjaminan Mutu Internal perguruan tinggi.';

        $data['organization_logo_url'] = $this->organization_logo_service->public_url($profile);
        $data['organization_name'] = ($profile && !empty($profile->nama_pt)) ? trim((string) $profile->nama_pt) : 'Universitas Muhammadiyah Babel';
        $data['organization_description'] = $profile && property_exists($profile, 'deskripsi') ? trim((string) $profile->deskripsi) : '';
        $data['login_hero_title'] = $login_hero_title;
        $data['login_hero_description'] = $login_hero_description;
        $data['app_name'] = $app_name;
        $data['app_short_name'] = $app_short_name;
        $this->load->view($view, $data);
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
