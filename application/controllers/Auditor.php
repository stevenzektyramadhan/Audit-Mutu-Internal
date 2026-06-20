<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auditor extends CI_Controller
{
    /** @var CI_Session */
    public $session;

    /** @var Auth_guard */
    public $auth_guard;

    /** @var CI_Input */
    public $input;

    /** @var CI_Form_validation */
    public $form_validation;

    /** @var Auditor_service */
    protected $auditor_service;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_guard');
        $this->auth_guard->only(['auditor']);

        require_once APPPATH . 'services/Auditor_service.php';
        $this->auditor_service = new Auditor_service();
    }

    public function index()
    {
        $data = $this->auditor_service->get_penilaian_data($this->user_id());
        $data['title'] = 'Penilaian Auditee - AMI';
        $data['page_title'] = 'Penilaian Auditee';
        $data['page_subtitle'] = 'Beranda / Penilaian Auditee';
        $data['active_menu'] = 'penilaian';
        $data['menu_badges'] = ['tugas_audit' => $data['jumlah_siap_dinilai']];

        $this->load->view('auditor/index', $data);
    }

    public function tugas()
    {
        $data = $this->auditor_service->get_tugas_data($this->user_id());
        $data['title'] = 'Tugas Audit - AMI';
        $data['page_title'] = 'Tugas Audit';
        $data['page_subtitle'] = 'Beranda / Tugas Audit';
        $data['active_menu'] = 'tugas_audit';
        $data['menu_badges'] = ['tugas_audit' => $data['jumlah_siap_dinilai']];

        $this->load->view('auditor/tugas', $data);
    }

    public function nilai($tugas_id)
    {
        $result = $this->auditor_service->get_detail((int) $tugas_id, $this->user_id());

        if (!$result['success']) {
            show_error($result['message'], 404, 'Tugas tidak ditemukan');
            return;
        }

        if ($result['tugas']->status === STATUS_BELUM_DIISI) {
            show_error('Auditee belum mengisi tugas audit ini.', 403, 'Tugas belum siap dinilai');
            return;
        }

        $this->load_nilai_view($result);
    }

    public function simpan_nilai($tugas_id)
    {
        $tugas_id = (int) $tugas_id;
        $detail = $this->auditor_service->get_detail($tugas_id, $this->user_id());

        if (!$detail['success']) {
            show_error($detail['message'], 404, 'Tugas tidak ditemukan');
            return;
        }

        foreach ($detail['jawaban'] as $index => $jawaban) {
            $this->form_validation->set_rules(
                'skor[' . (int) $jawaban->id . ']',
                'Skor pertanyaan ' . ((int) $index + 1),
                'required|in_list[1,2,3,4]'
            );
        }

        if ($this->form_validation->run() === FALSE) {
            $this->load_nilai_view($detail);
            return;
        }

        $result = $this->auditor_service->simpan_nilai(
            $tugas_id,
            $this->user_id(),
            $this->input->post(NULL, TRUE)
        );

        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);

        if (!$result['success']) {
            redirect('auditor/nilai/' . $tugas_id);
            return;
        }

        redirect('auditor');
    }

    private function load_nilai_view($result)
    {
        $data = $result;
        $data['title'] = 'Form Penilaian - AMI';
        $data['page_title'] = 'Penilaian Audit';
        $data['page_subtitle'] = 'Beranda / Penilaian Auditee / Form Penilaian';
        $data['active_menu'] = 'penilaian';

        $this->load->view('auditor/nilai', $data);
    }

    private function user_id()
    {
        return (int) $this->session->userdata('user_id');
    }
}
