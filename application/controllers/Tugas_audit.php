<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tugas_audit extends CI_Controller {

    /** @var CI_Session */
    public $session;

    /** @var CI_Input */
    public $input;

    /** @var CI_Form_validation */
    public $form_validation;

    /** @var Auth_guard */
    public $auth_guard;

    /** @var Tugas_audit_service */
    protected $tugas_audit_service;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->library('auth_guard');
        
        $this->auth_guard->check();
        $this->auth_guard->only(['super_admin', 'admin_lpmpi']);
        
        require_once APPPATH . 'services/Tugas_audit_service.php';
        $this->tugas_audit_service = new Tugas_audit_service();
    }

    public function index()
    {
        $status_filter = (string) $this->input->get('status', TRUE);
        $filters = [
            'q' => trim((string) $this->input->get('q', TRUE)),
            'status' => in_array($status_filter, [STATUS_BELUM_DIISI, STATUS_DIISI, STATUS_DINILAI], TRUE) ? $status_filter : '',
        ];

        $data['title'] = 'Tugas Audit - AMI';
        $data['page_title'] = 'Tugas Audit';
        $data['page_subtitle'] = 'Beranda / Tugas Audit';
        $data['active_menu'] = 'tugas_audit';
        $data['tugas'] = $this->tugas_audit_service->get_all_tugas($filters);
        $data['filters'] = $filters;
        
        $this->load->view('tugas_audit/index', $data);
    }
    
    public function hasil()
    {
        $filters = ['q' => trim((string) $this->input->get('q', TRUE))];

        $data['title'] = 'Hasil Audit - AMI';
        $data['page_title'] = 'Hasil Audit';
        $data['page_subtitle'] = 'Beranda / Hasil Audit';
        $data['active_menu'] = 'hasil_audit';
        $data['hasil'] = $this->tugas_audit_service->get_hasil_audit($filters);
        $data['filters'] = $filters;
        
        $this->load->view('tugas_audit/hasil', $data);
    }

    public function show($id)
    {
        $result = $this->tugas_audit_service->get_detail((int) $id);

        if (!$result['success']) {
            show_error($result['message'], 404, 'Tugas tidak ditemukan');
            return;
        }

        $data = $result;
        $data['title'] = 'Detail Tugas Audit - AMI';
        $data['page_title'] = 'Detail Tugas Audit';
        $data['page_subtitle'] = 'Beranda / Tugas Audit / Detail';
        $data['active_menu'] = 'tugas_audit';

        $this->load->view('tugas_audit/show', $data);
    }

    public function create()
    {
        $this->load->helper('form');

        $data['title'] = 'Buat Tugas Audit - AMI';
        $data['page_title'] = 'Buat Tugas Audit';
        $data['page_subtitle'] = 'Beranda / Tugas Audit / Buat Tugas';
        $data['active_menu'] = 'tugas_audit';

        $data = array_merge($data, $this->tugas_audit_service->get_form_options());
        
        $this->load->view('tugas_audit/create', $data);
    }

    public function store()
    {
        $this->load->helper('form');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('auditor_id', 'Auditor', 'required|integer');
        $this->form_validation->set_rules('auditee_id', 'Auditee', 'required|integer');
        $this->form_validation->set_rules('standar_id', 'Standar', 'required|integer');
        $this->form_validation->set_rules('periode_id', 'Periode', 'required|integer');

        if ($this->form_validation->run() === FALSE) {
            $this->create();
        } else {
            $data = [
                'auditor_id' => $this->input->post('auditor_id', TRUE),
                'auditee_id' => $this->input->post('auditee_id', TRUE),
                'standar_id' => $this->input->post('standar_id', TRUE),
                'periode_id' => $this->input->post('periode_id', TRUE),
                'status' => STATUS_BELUM_DIISI
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

    public function delete($id)
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            return;
        }

        $result = $this->tugas_audit_service->delete_tugas((int) $id);
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('tugas_audit');
    }
}
