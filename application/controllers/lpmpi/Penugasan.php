<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Penugasan Controller - Penugasan Auditor ke Auditee per Standar.
 *
 * @property Tugas_model $Tugas_model
 * @property Periode_model $Periode_model
 * @property Standar_model $Standar_model
 * @property User_model $User_model
 */
class Penugasan extends Admin_Lpmpi_Controller
{
    protected $tugas_audit_service;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['form', 'url']);
        $this->load->library('form_validation');
        $this->load->model('Tugas_model');
        $this->load->model('Periode_model');
        $this->load->model('Standar_model');
        $this->load->model('User_model');

        require_once APPPATH . 'services/Tugas_audit_service.php';
        $this->tugas_audit_service = new Tugas_audit_service();
    }

    public function index()
    {
        $filters = [
            'periode_id' => (int) $this->input->get('periode_id', TRUE),
            'standar_id' => (int) $this->input->get('standar_id', TRUE),
            'auditee_id' => (int) $this->input->get('auditee_id', TRUE),
        ];

        $data['title']        = 'Penugasan Auditor';
        $data['page_title']   = 'Penugasan Auditor';
        $data['page_subtitle'] = 'Atur penugasan auditor ke auditee per standar';
        $data['active_menu']  = 'penugasan';
        $data['filters']      = $filters;
        $data['periode_list'] = $this->Periode_model->get_all();
        $data['standar_list'] = $this->Standar_model->get_all();
        $data['auditee_list'] = $this->User_model->get_by_role('auditee');
        $data['tugas_list']   = $this->Tugas_model->get_all_tugas($filters);

        $this->load->view('lpmpi/penugasan/index', $data);
    }

    public function create()
    {
        $data['title']        = 'Buat Penugasan Auditor';
        $data['page_title']   = 'Buat Penugasan Auditor';
        $data['page_subtitle'] = 'Pilih periode, standar, auditor, dan auditee';
        $data['active_menu']  = 'penugasan';
        $data = array_merge($data, $this->tugas_audit_service->get_form_options());

        $this->load->view('lpmpi/penugasan/form', $data);
    }

    public function store()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            return;
        }

        $this->form_validation->set_rules('periode_id', 'Periode', 'required|integer');
        $this->form_validation->set_rules('standar_id', 'Standar', 'required|integer');
        $this->form_validation->set_rules('auditor_id', 'Auditor', 'required|integer');
        $this->form_validation->set_rules('auditee_id', 'Auditee', 'required|integer');

        if ($this->form_validation->run() === FALSE) {
            $this->create();
            return;
        }

        $payload = [
            'periode_id' => $this->input->post('periode_id', TRUE),
            'standar_id' => $this->input->post('standar_id', TRUE),
            'auditor_id' => $this->input->post('auditor_id', TRUE),
            'auditee_id' => $this->input->post('auditee_id', TRUE),
        ];

        $result = $this->tugas_audit_service->create_tugas($payload);
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);

        if ($result['success']) {
            redirect('lpmpi/penugasan');
            return;
        }

        $this->create();
    }

    public function delete($id)
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            return;
        }

        $result = $this->tugas_audit_service->delete_tugas((int) $id);
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('lpmpi/penugasan');
    }
}
