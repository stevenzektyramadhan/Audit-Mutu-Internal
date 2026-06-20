<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Standar extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->library('auth_guard');
        
        $this->auth_guard->check();
        $this->auth_guard->only(['super_admin']);
        
        require_once APPPATH . 'services/Standar_service.php';
        $this->standar_service = new Standar_service();
    }

    public function index()
    {
        $data['title'] = 'Data Standar - AMI';
        $data['page_title'] = 'Data Standar';
        $data['page_subtitle'] = 'Beranda / Data Standar';
        $data['active_menu'] = 'standar';
        $data['standar'] = $this->standar_service->get_all_standar();
        
        $this->load->view('standar/index', $data);
    }

    public function create()
    {
        $this->load->helper('form');
        $data['title'] = 'Tambah Standar - AMI';
        $data['page_title'] = 'Tambah Standar';
        $data['page_subtitle'] = 'Beranda / Data Standar / Tambah';
        $data['active_menu'] = 'standar';
        
        $this->load->view('standar/create', $data);
    }

    public function store()
    {
        $this->load->helper('form');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('nama_standar', 'Nama Standar', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->create();
        } else {
            $data = [
                'nama_standar' => $this->input->post('nama_standar'),
                'deskripsi' => $this->input->post('deskripsi')
            ];

            $result = $this->standar_service->create_standar($data);

            if ($result['success']) {
                $this->session->set_flashdata('success', $result['message']);
                redirect('standar');
            } else {
                $this->session->set_flashdata('error', $result['message']);
                $this->create();
            }
        }
    }
}
