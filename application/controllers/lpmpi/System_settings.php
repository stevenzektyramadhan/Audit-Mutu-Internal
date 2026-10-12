<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class System_settings extends Admin_Lpmpi_Controller
{
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['form', 'url']);
        require_once APPPATH . 'services/System_settings_service.php';
        $this->service = new System_settings_service();
    }

    public function index()
    {
        $role = (string) $this->session->userdata('role');
        $settings = $this->service->settings();
        $this->load->view('lpmpi/system_settings/index', [
            'title' => 'Pengaturan Sistem',
            'page_title' => 'Pengaturan Sistem',
            'page_subtitle' => 'Beranda / Management / Pengaturan Sistem',
            'active_menu' => 'system_settings',
            'settings' => $settings,
            'institution_logo_limit_mib' => (int) floor($settings['institution_logo_limit_bytes'] / 1024 / 1024),
            'can_manage_turnstile' => $role === 'super_admin',
        ]);
    }

    public function update()
    {
        $this->require_post();
        $result = $this->service->update($this->input->post(NULL, TRUE), (string) $this->session->userdata('role'), isset($_FILES['logo']) ? $_FILES['logo'] : NULL);
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('lpmpi/system-settings');
    }

    private function require_post()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            exit;
        }
    }
}
