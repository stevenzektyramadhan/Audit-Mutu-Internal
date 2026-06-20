<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tugas_audit extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->library('auth_guard');
        
        $this->auth_guard->check();
        $this->auth_guard->only(['super_admin']);
        
        require_once APPPATH . 'services/Tugas_audit_service.php';
        $this->tugas_audit_service = new Tugas_audit_service();
    }

    public function index()
    {
        $data['title'] = 'Tugas Audit - AMI';
        $data['page_title'] = 'Tugas Audit';
        $data['page_subtitle'] = 'Beranda / Tugas Audit';
        $data['active_menu'] = 'tugas_audit';
        $data['tugas'] = $this->tugas_audit_service->get_all_tugas();
        
        $this->load->view('tugas_audit/index', $data);
    }
    
    public function hasil()
    {
        $data['title'] = 'Hasil Audit - AMI';
        $data['page_title'] = 'Hasil Audit';
        $data['page_subtitle'] = 'Beranda / Hasil Audit';
        $data['active_menu'] = 'hasil_audit';
        $data['hasil'] = $this->tugas_audit_service->get_hasil_audit();
        
        $this->load->view('tugas_audit/hasil', $data);
    }

    public function create()
    {
        $this->load->helper('form');
        $this->load->model('User_model');
        $this->load->model('Standar_model');

        $data['title'] = 'Buat Tugas Audit - AMI';
        $data['page_title'] = 'Buat Tugas Audit';
        $data['page_subtitle'] = 'Beranda / Tugas Audit / Buat Tugas';
        $data['active_menu'] = 'tugas_audit';
        
        $data['auditor'] = $this->User_model->get_by_role('auditor');
        $data['auditee'] = $this->User_model->get_by_role('auditee');
        $data['standar'] = $this->Standar_model->get_all_with_count();
        
        $this->load->view('tugas_audit/create', $data);
    }

    public function store()
    {
        $this->load->helper('form');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('auditor_id', 'Auditor', 'required');
        $this->form_validation->set_rules('auditee_id', 'Auditee', 'required');
        $this->form_validation->set_rules('standar_id', 'Standar', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->create();
        } else {
            $data = [
                'auditor_id' => $this->input->post('auditor_id'),
                'auditee_id' => $this->input->post('auditee_id'),
                'standar_id' => $this->input->post('standar_id'),
                'status' => 'belum_diisi'
            ];

            $result = $this->tugas_audit_service->create_tugas($data);

            if ($result['success']) {
                $this->session->set_flashdata('success', $result['message']);
                redirect('tugas_audit');
            } else {
                $this->session->set_flashdata('error', $result['message']);
                $this->create();
            }
        }
    }
}
