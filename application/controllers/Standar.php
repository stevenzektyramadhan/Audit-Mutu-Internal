<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Standar extends CI_Controller {

    /** @var CI_Session */
    public $session;

    /** @var CI_Input */
    public $input;

    /** @var CI_Form_validation */
    public $form_validation;

    /** @var Auth_guard */
    public $auth_guard;

    /** @var Standar_service */
    protected $standar_service;

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
                'nama_standar' => $this->input->post('nama_standar', TRUE),
                'deskripsi' => $this->input->post('deskripsi', TRUE)
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

    public function edit($id)
    {
        $standar = $this->standar_service->get_standar((int) $id);
        if (!$standar) {
            show_error('Standar tidak ditemukan.', 404, 'Not Found');
            return;
        }

        $data['title'] = 'Edit Standar - AMI';
        $data['page_title'] = 'Edit Standar';
        $data['page_subtitle'] = 'Beranda / Data Standar / Edit';
        $data['active_menu'] = 'standar';
        $data['standar'] = $standar;

        $this->load->view('standar/edit', $data);
    }

    public function update($id)
    {
        $this->form_validation->set_rules('nama_standar', 'Nama Standar', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->edit($id);
            return;
        }

        $result = $this->standar_service->update_standar((int) $id, [
            'nama_standar' => $this->input->post('nama_standar', TRUE),
            'deskripsi' => $this->input->post('deskripsi', TRUE),
        ]);

        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect($result['success'] ? 'standar' : 'standar/edit/' . (int) $id);
    }

    public function delete($id)
    {
        $this->require_post();
        $result = $this->standar_service->delete_standar((int) $id);
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('standar');
    }

    private function require_post()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            exit;
        }
    }
}
