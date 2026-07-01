<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Periode — Controller manajemen periode audit.
 * Hanya dapat diakses oleh admin_lpmpi dan super_admin.
 */
class Periode extends Admin_Lpmpi_Controller {

    /** @var CI_Session */
    public $session;

    /** @var CI_Input */
    public $input;

    /** @var CI_Form_validation */
    public $form_validation;

    /** @var Periode_service */
    protected $periode_service;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->library('form_validation');

        require_once APPPATH . 'services/Periode_service.php';
        $this->periode_service = new Periode_service();
    }

    public function index()
    {
        $data['title'] = 'Periode Audit - AMI';
        $data['page_title'] = 'Periode Audit';
        $data['page_subtitle'] = 'Beranda / Periode Audit';
        $data['active_menu'] = 'periode';
        $data['periode'] = $this->periode_service->get_all();

        $this->load->view('lpmpi/periode/index', $data);
    }

    public function create()
    {
        $data['title'] = 'Tambah Periode Audit - AMI';
        $data['page_title'] = 'Tambah Periode Audit';
        $data['page_subtitle'] = 'Beranda / Periode Audit / Tambah';
        $data['active_menu'] = 'periode';
        $data['action'] = 'periode/store';
        $data['periode'] = NULL;

        $this->load->view('lpmpi/periode/form', $data);
    }


    public function store()
    {
        $this->form_validation->set_rules('nama_periode', 'Nama Periode', 'required');
        $this->form_validation->set_rules('tahun_akademik', 'Tahun Akademik', 'required');
        $this->form_validation->set_rules('semester', 'Semester', 'required|in_list[ganjil,genap]');
        $this->form_validation->set_rules('tanggal_buka', 'Tanggal Buka', 'required');
        $this->form_validation->set_rules('tanggal_tutup', 'Tanggal Tutup', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->create();
        } else {
            $data = [
                'nama_periode'   => $this->input->post('nama_periode', TRUE),
                'tahun_akademik' => $this->input->post('tahun_akademik', TRUE),
                'semester'       => $this->input->post('semester', TRUE),
                'tanggal_buka'   => $this->input->post('tanggal_buka', TRUE),
                'tanggal_tutup'  => $this->input->post('tanggal_tutup', TRUE),
                'is_aktif'       => $this->input->post('is_aktif') ? 1 : 0,
            ];

            $result = $this->periode_service->create($data);

            if ($result['success']) {
                $this->session->set_flashdata('success', $result['message']);
                redirect('periode');
            } else {
                $this->session->set_flashdata('error', $result['message']);
                $this->create();
            }
        }
    }

    public function edit($id)
    {
        $periode = $this->periode_service->find((int) $id);

        if (!$periode) {
            show_error('Periode audit tidak ditemukan.', 404, 'Not Found');
            return;
        }

        $data['title'] = 'Edit Periode Audit - AMI';
        $data['page_title'] = 'Edit Periode Audit';
        $data['page_subtitle'] = 'Beranda / Periode Audit / Edit';
        $data['active_menu'] = 'periode';
        $data['action'] = 'periode/update/' . (int) $id;
        $data['periode'] = $periode;

        $this->load->view('lpmpi/periode/form', $data);
    }
    public function update($id)
    {
        $id = (int) $id;

        $this->form_validation->set_rules('nama_periode', 'Nama Periode', 'required');
        $this->form_validation->set_rules('tahun_akademik', 'Tahun Akademik', 'required');
        $this->form_validation->set_rules('semester', 'Semester', 'required|in_list[ganjil,genap]');
        $this->form_validation->set_rules('tanggal_buka', 'Tanggal Buka', 'required');
        $this->form_validation->set_rules('tanggal_tutup', 'Tanggal Tutup', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->edit($id);
        } else {
            $data = [
                'nama_periode'   => $this->input->post('nama_periode', TRUE),
                'tahun_akademik' => $this->input->post('tahun_akademik', TRUE),
                'semester'       => $this->input->post('semester', TRUE),
                'tanggal_buka'   => $this->input->post('tanggal_buka', TRUE),
                'tanggal_tutup'  => $this->input->post('tanggal_tutup', TRUE),
                'is_aktif'       => $this->input->post('is_aktif') ? 1 : 0,
            ];

            $result = $this->periode_service->update($id, $data);

            if ($result['success']) {
                $this->session->set_flashdata('success', $result['message']);
                redirect('periode');
            } else {
                $this->session->set_flashdata('error', $result['message']);
                $this->edit($id);
            }
        }
    }

    public function delete($id)
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            return;
        }

        $result = $this->periode_service->delete((int) $id);
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('periode');
    }

    public function toggle_aktif($id)
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            return;
        }

        $result = $this->periode_service->toggle_aktif((int) $id);
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('periode');
    }
}

