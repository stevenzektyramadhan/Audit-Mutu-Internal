<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pertanyaan extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->library('auth_guard');
        
        $this->auth_guard->check();
        $this->auth_guard->only(['super_admin']);
        
        require_once APPPATH . 'services/Pertanyaan_service.php';
        $this->pertanyaan_service = new Pertanyaan_service();
    }

    public function index()
    {
        $data['title'] = 'Data Pertanyaan - AMI';
        $data['page_title'] = 'Data Pertanyaan';
        $data['page_subtitle'] = 'Beranda / Data Pertanyaan';
        $data['active_menu'] = 'pertanyaan';
        $data['pertanyaan'] = $this->pertanyaan_service->get_all_pertanyaan();
        $data['standar'] = $this->pertanyaan_service->get_all_standar();
        
        $this->load->view('pertanyaan/index', $data);
    }

    public function create()
    {
        $this->load->helper('form');
        $data['title'] = 'Tambah Pertanyaan - AMI';
        $data['page_title'] = 'Tambah Pertanyaan';
        $data['page_subtitle'] = 'Beranda / Data Pertanyaan / Tambah';
        $data['active_menu'] = 'pertanyaan';
        $data['standar'] = $this->pertanyaan_service->get_all_standar();
        
        $this->load->view('pertanyaan/create', $data);
    }

    public function store()
    {
        $this->load->helper('form');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('standar_id', 'Standar', 'required');
        $this->form_validation->set_rules('isi_pertanyaan', 'Isi Pertanyaan', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->create();
        } else {
            $data = [
                'standar_id' => $this->input->post('standar_id'),
                'isi_pertanyaan' => $this->input->post('isi_pertanyaan')
            ];

            $result = $this->pertanyaan_service->create_pertanyaan($data);

            if ($result['success']) {
                $this->session->set_flashdata('success', $result['message']);
                redirect('pertanyaan');
            } else {
                $this->session->set_flashdata('error', $result['message']);
                $this->create();
            }
        }
    }
}
