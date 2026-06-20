<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auditee extends CI_Controller
{
    /** @var CI_Session */
    public $session;

    /** @var Auth_guard */
    public $auth_guard;

    /** @var CI_Input */
    public $input;

    /** @var CI_Form_validation */
    public $form_validation;

    /** @var Auditee_service */
    protected $auditee_service;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_guard');
        $this->auth_guard->only(['auditee']);

        require_once APPPATH . 'services/Auditee_service.php';
        $this->auditee_service = new Auditee_service();
    }

    public function index()
    {
        $data = $this->auditee_service->get_tugas_data($this->user_id(), STATUS_BELUM_DIISI);
        $data['title'] = 'Pengisian Audit - AMI';
        $data['page_title'] = 'Pengisian Audit';
        $data['page_subtitle'] = 'Beranda / Pengisian Audit';
        $data['active_menu'] = 'pengisian';
        $data['menu_badges'] = ['tugas_saya' => $data['jumlah_belum_diisi']];

        $this->load->view('auditee/index', $data);
    }

    public function tugas()
    {
        $filter_status = $this->input->get('status', TRUE) === STATUS_DINILAI ? STATUS_DINILAI : NULL;
        $data = $this->auditee_service->get_tugas_data($this->user_id(), $filter_status);
        $data['filter_status'] = $filter_status;
        $data['title'] = $filter_status === STATUS_DINILAI ? 'Hasil Penilaian - AMI' : 'Tugas Saya - AMI';
        $data['page_title'] = $filter_status === STATUS_DINILAI ? 'Hasil Penilaian' : 'Tugas Saya';
        $data['page_subtitle'] = 'Beranda / ' . $data['page_title'];
        $data['active_menu'] = $filter_status === STATUS_DINILAI ? 'hasil_penilaian' : 'tugas_saya';
        $data['menu_badges'] = ['tugas_saya' => $data['jumlah_belum_diisi']];

        $this->load->view('auditee/tugas', $data);
    }

    public function isi($tugas_id)
    {
        $result = $this->auditee_service->get_detail((int) $tugas_id, $this->user_id());

        if (!$result['success']) {
            show_error($result['message'], 404, 'Tugas tidak ditemukan');
            return;
        }

        $this->load_isi_view($result);
    }

    public function simpan_jawaban($tugas_id)
    {
        $tugas_id = (int) $tugas_id;
        $detail = $this->auditee_service->get_detail($tugas_id, $this->user_id());

        if (!$detail['success']) {
            show_error($detail['message'], 404, 'Tugas tidak ditemukan');
            return;
        }

        foreach ($detail['jawaban'] as $index => $jawaban) {
            $this->form_validation->set_rules(
                'link_bukti[' . (int) $jawaban->id . ']',
                'Link bukti pertanyaan ' . ((int) $index + 1),
                'required|valid_url'
            );
        }

        if ($this->form_validation->run() === FALSE) {
            $this->load_isi_view($detail);
            return;
        }

        $result = $this->auditee_service->simpan_jawaban(
            $tugas_id,
            $this->user_id(),
            $this->input->post(NULL, TRUE)
        );

        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);

        if (!$result['success']) {
            redirect('auditee/isi/' . $tugas_id);
            return;
        }

        redirect('auditee/tugas');
    }

    private function load_isi_view($result)
    {
        $data = $result;
        $data['title'] = 'Pengisian Audit - AMI';
        $data['page_title'] = $result['tugas']->status === STATUS_BELUM_DIISI ? 'Isi Audit' : 'Detail Audit';
        $data['page_subtitle'] = 'Beranda / Tugas Saya / ' . $data['page_title'];
        $data['active_menu'] = $result['tugas']->status === STATUS_DINILAI ? 'hasil_penilaian' : 'pengisian';

        $this->load->view('auditee/isi', $data);
    }

    private function user_id()
    {
        return (int) $this->session->userdata('user_id');
    }
}
