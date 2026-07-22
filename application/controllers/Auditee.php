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

    /** @var Jawaban_model */
    public $Jawaban_model;

    /** @var Periode_model */
    public $Periode_model;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_guard');
        $this->auth_guard->only(['auditee']);
        $this->load->helper(['form', 'url', 'download']);
        $this->load->library('form_validation');
        $this->load->model('Jawaban_model');
        $this->load->model('Periode_model');
    }

    public function index()
    {
        $this->tugas();
    }

    public function tugas($action = NULL, $id = NULL)
    {
        if ($action !== NULL) {
            $this->dispatch_tugas_action($action, $id);
            return;
        }

        $periode_id = (int) $this->input->get('periode_id', TRUE);
        $status = (string) $this->input->get('status', TRUE);
        $allowed_status = ['belum_diisi', 'draft', 'submitted', 'revisi', 'dinilai'];
        $status = in_array($status, $allowed_status, TRUE) ? $status : '';
        $page_title = 'Tugas Saya';
        $active_menu = 'tugas_saya';

        if (in_array($status, ['belum_diisi', 'draft', 'revisi'], TRUE)) {
            $page_title = 'Pengisian Audit';
            $active_menu = 'pengisian';
        } elseif ($status === 'dinilai') {
            $page_title = 'Hasil Penilaian';
            $active_menu = 'hasil_penilaian';
        }

        $data['title'] = 'Inbox Tugas - AMI';
        $data['page_title'] = $page_title;
        $data['page_subtitle'] = 'Beranda / ' . $data['page_title'];
        $data['active_menu'] = $active_menu;
        $data['filters'] = [
            'periode_id' => $periode_id,
            'status' => $status,
        ];
        $data['periode_list'] = $this->Periode_model->get_all();
        $data['tugas'] = $this->Jawaban_model->get_inbox_by_auditee($this->user_id(), $periode_id, $status);
        $data['menu_badges'] = ['tugas_saya' => $this->Jawaban_model->count_need_attention($this->user_id())];

        $this->load->view('auditee/inbox', $data);
    }

    public function isi($tugas_id)
    {
        $this->form((int) $tugas_id);
    }

    public function form($tugas_id)
    {
        $detail = $this->get_detail_or_404((int) $tugas_id);
        $this->load_form_view($detail);
    }

    public function simpan_jawaban($tugas_id)
    {
        $this->submit((int) $tugas_id);
    }

    public function save($tugas_id)
    {
        $this->require_post();
        $detail = $this->get_detail_or_404((int) $tugas_id);

        if ($detail['tugas']->is_readonly) {
            $this->session->set_flashdata('error', 'Jawaban sudah disubmit dan tidak dapat diubah.');
            redirect('auditee/form/' . (int) $tugas_id);
            return;
        }

        $this->set_answer_rules($detail['jawaban'], FALSE);
        if ($this->form_validation->run() === FALSE) {
            $this->load_form_view($detail);
            return;
        }

        $result = $this->Jawaban_model->save_answers((int) $tugas_id, $this->input->post(NULL, TRUE), FALSE);
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('auditee/form/' . (int) $tugas_id);
    }

    public function submit($tugas_id)
    {
        $this->require_post();
        $detail = $this->get_detail_or_404((int) $tugas_id);

        if ($detail['tugas']->is_readonly) {
            $this->session->set_flashdata('error', 'Jawaban sudah disubmit dan tidak dapat diubah.');
            redirect('auditee/form/' . (int) $tugas_id);
            return;
        }

        $this->set_answer_rules($detail['jawaban'], TRUE);
        if ($this->form_validation->run() === FALSE) {
            $this->load_form_view($detail);
            return;
        }

        $result = $this->Jawaban_model->save_answers((int) $tugas_id, $this->input->post(NULL, TRUE), TRUE);
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);

        if ($result['success']) {
            redirect('auditee/konfirmasi/' . (int) $tugas_id);
            return;
        }

        redirect('auditee/form/' . (int) $tugas_id);
    }

    public function konfirmasi($tugas_id)
    {
        $detail = $this->get_detail_or_404((int) $tugas_id);
        $this->load_form_view($detail, ['confirmation' => TRUE]);
    }

    public function download_instrumen($tugas_id)
    {
        $detail = $this->get_detail_or_404((int) $tugas_id);
        $file_name = (string) $detail['tugas']->file_instrumen;

        if ($file_name === '') {
            $this->session->set_flashdata('error', 'File instrumen belum tersedia.');
            redirect('auditee/tugas');
            return;
        }

        $path = private_storage_path('instrumen', $file_name);
        if ($path === NULL) {
            show_error('File instrumen tidak ditemukan di server.', 404, 'File tidak ditemukan');
            return;
        }

        force_download($path, NULL);
    }

    private function get_detail_or_404($tugas_id)
    {
        $tugas = $this->Jawaban_model->find_tugas_for_auditee((int) $tugas_id, $this->user_id());
        if (!$tugas) {
            show_error('Tugas audit tidak ditemukan atau bukan milik Anda.', 404, 'Tugas tidak ditemukan');
            exit;
        }

        return [
            'tugas' => $tugas,
            'jawaban' => $this->Jawaban_model->get_by_tugas((int) $tugas_id),
        ];
    }

    private function load_form_view($detail, $extra = [])
    {
        $data = array_merge($detail, $extra);
        $data['title'] = 'Form Pengisian - AMI';
        $data['page_title'] = !empty($extra['confirmation']) ? 'Konfirmasi Submit' : 'Form Pengisian';
        $data['page_subtitle'] = 'Beranda / Tugas Saya / ' . $data['page_title'];
        $data['active_menu'] = in_array($detail['tugas']->display_status, ['belum_diisi', 'draft', 'revisi'], TRUE)
            ? 'pengisian'
            : ($detail['tugas']->display_status === 'dinilai' ? 'hasil_penilaian' : 'tugas_saya');
        $data['menu_badges'] = ['tugas_saya' => $this->Jawaban_model->count_need_attention($this->user_id())];

        $this->load->view('auditee/form_isian', $data);
    }

    private function set_answer_rules($jawaban, $submit)
    {
        foreach ($jawaban as $index => $item) {
            $number = (int) $index + 1;
            $id = (int) $item->id;
            $this->form_validation->set_rules(
                'jawaban[' . $id . ']',
                'Jawaban pertanyaan ' . $number,
                $submit ? 'trim|required' : 'trim'
            );
            $this->form_validation->set_rules(
                'link_bukti[' . $id . ']',
                'Link bukti pertanyaan ' . $number,
                $submit ? 'trim|required|valid_url' : 'trim'
            );
        }
    }

    private function require_post()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            exit;
        }
    }

    private function dispatch_tugas_action($action, $id)
    {
        $id = (int) $id;

        switch ((string) $action) {
            case 'form':
                $this->form($id);
                return;
            case 'save':
                $this->save($id);
                return;
            case 'submit':
                $this->submit($id);
                return;
            case 'konfirmasi':
                $this->konfirmasi($id);
                return;
            case 'download_instrumen':
                $this->download_instrumen($id);
                return;
            default:
                show_404();
                return;
        }
    }

    private function user_id()
    {
        return (int) $this->session->userdata('user_id');
    }
}
