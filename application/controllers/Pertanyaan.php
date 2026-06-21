<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pertanyaan extends CI_Controller {

    /** @var CI_Session */
    public $session;

    /** @var CI_Input */
    public $input;

    /** @var CI_Form_validation */
    public $form_validation;

    /** @var Auth_guard */
    public $auth_guard;

    /** @var Pertanyaan_service */
    protected $pertanyaan_service;

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
        $filter_standar_id = (int) $this->input->get('standar_id', TRUE);
        $filter_standar_id = $filter_standar_id > 0 ? $filter_standar_id : NULL;

        $data['title'] = 'Data Pertanyaan - AMI';
        $data['page_title'] = 'Data Pertanyaan';
        $data['page_subtitle'] = 'Beranda / Data Pertanyaan';
        $data['active_menu'] = 'pertanyaan';
        $data['pertanyaan'] = $this->pertanyaan_service->get_all_pertanyaan($filter_standar_id);
        $data['standar'] = $this->pertanyaan_service->get_all_standar();
        $data['filter_standar_id'] = $filter_standar_id;
        
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

        $this->form_validation->set_rules('standar_id', 'Standar', 'required|integer');
        $this->form_validation->set_rules('isi_pertanyaan', 'Isi Pertanyaan', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->create();
        } else {
            $data = [
                'standar_id' => $this->input->post('standar_id', TRUE),
                'isi_pertanyaan' => $this->input->post('isi_pertanyaan', TRUE)
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

    public function edit($id)
    {
        $pertanyaan = $this->pertanyaan_service->get_pertanyaan((int) $id);
        if (!$pertanyaan) {
            show_error('Pertanyaan tidak ditemukan.', 404, 'Not Found');
            return;
        }

        $data['title'] = 'Edit Pertanyaan - AMI';
        $data['page_title'] = 'Edit Pertanyaan';
        $data['page_subtitle'] = 'Beranda / Data Pertanyaan / Edit';
        $data['active_menu'] = 'pertanyaan';
        $data['pertanyaan'] = $pertanyaan;
        $data['standar'] = $this->pertanyaan_service->get_all_standar();

        $this->load->view('pertanyaan/edit', $data);
    }

    public function update($id)
    {
        $this->form_validation->set_rules('standar_id', 'Standar', 'required|integer');
        $this->form_validation->set_rules('isi_pertanyaan', 'Isi Pertanyaan', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->edit($id);
            return;
        }

        $result = $this->pertanyaan_service->update_pertanyaan((int) $id, [
            'standar_id' => $this->input->post('standar_id', TRUE),
            'isi_pertanyaan' => $this->input->post('isi_pertanyaan', TRUE),
        ]);

        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect($result['success'] ? 'pertanyaan' : 'pertanyaan/edit/' . (int) $id);
    }

    public function delete($id)
    {
        $this->require_post();
        $result = $this->pertanyaan_service->delete_pertanyaan((int) $id);
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('pertanyaan');
    }

    private function require_post()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            exit;
        }
    }
}
