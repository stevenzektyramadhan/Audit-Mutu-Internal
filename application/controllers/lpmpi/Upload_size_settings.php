<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upload_size_settings extends Admin_Lpmpi_Controller
{
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['form', 'url']);
        require_once APPPATH . 'services/Upload_size_settings_service.php';
        $this->service = new Upload_size_settings_service();
    }

    public function index()
    {
        $this->load->view('lpmpi/upload_size_settings/index', [
            'title' => 'Pengaturan Ukuran Upload',
            'page_title' => 'Pengaturan Ukuran Upload',
            'page_subtitle' => 'Beranda / Management / Pengaturan Upload',
            'active_menu' => 'upload_size_settings',
            'settings' => $this->service->settings(),
            'php_ceiling_mib' => $this->service->php_ceiling_mib(),
        ]);
    }

    public function update()
    {
        $this->require_post();
        $result = $this->service->update($this->input->post(NULL, TRUE));
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('lpmpi/upload-size-settings');
    }

    private function require_post()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            exit;
        }
    }
}
