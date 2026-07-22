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

    /** @var Jawaban_model */
    public $Jawaban_model;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_guard');
        $this->auth_guard->only(['auditor']);
        $this->load->helper('download');

        require_once APPPATH . 'services/Auditor_service.php';
        $this->auditor_service = new Auditor_service();
        $this->load->model('Jawaban_model');
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

    public function penilaian()
    {
        $auditor_id = $this->user_id();

        $data['title'] = 'Inbox Penilaian - AMI';
        $data['page_title'] = 'Penilaian Auditee';
        $data['page_subtitle'] = 'Beranda / Penilaian Auditee';
        $data['active_menu'] = 'penilaian';
        $data['tugas'] = $this->Jawaban_model->get_inbox_by_auditor($auditor_id);
        $data['menu_badges'] = ['penilaian' => $this->Jawaban_model->count_pending_penilaian($auditor_id)];

        $this->load->view('auditor/inbox', $data);
    }

    public function form_penilaian($tugas_id)
    {
        $detail = $this->get_penilaian_detail_or_404((int) $tugas_id);

        if (empty($detail['tugas']->is_auditee_submitted)) {
            show_error('Auditee belum submit jawaban untuk tugas audit ini.', 403, 'Tugas belum siap dinilai');
            return;
        }

        $data = $detail;
        $data['title'] = 'Form Penilaian - AMI';
        $data['page_title'] = 'Form Penilaian';
        $data['page_subtitle'] = 'Beranda / Penilaian Auditee / Form Penilaian';
        $data['active_menu'] = 'penilaian';
        $data['menu_badges'] = ['penilaian' => $this->Jawaban_model->count_pending_penilaian($this->user_id())];

        $this->load->view('auditor/form_penilaian', $data);
    }

    public function save_penilaian_item($jawaban_id)
    {
        if (!$this->require_post_json()) {
            return;
        }

        $jawaban_id = (int) $jawaban_id;
        $jawaban = $this->Jawaban_model->find_jawaban_for_auditor($jawaban_id, $this->user_id());
        if (!$jawaban) {
            $this->json_response(['success' => FALSE, 'message' => 'Jawaban audit tidak ditemukan atau bukan milik Anda.'], 404);
            return;
        }

        $upload = $this->handle_bukti_upload($jawaban_id);
        if (!$upload['success']) {
            $this->json_response(['success' => FALSE, 'message' => $upload['message']], 422);
            return;
        }

        $data = $this->input->post(NULL, TRUE);
        if (!empty($upload['file_name'])) {
            $data['dokumen_bukti'] = $upload['file_name'];
        }

        $result = $this->Jawaban_model->save_penilaian_item($jawaban_id, $this->user_id(), $data);
        if (!$result['success']) {
            if (!empty($upload['file_name'])) {
                $this->delete_bukti_file($upload['file_name']);
            }

            $this->json_response($result, 422);
            return;
        }

        if (!empty($upload['file_name']) && !empty($jawaban->dokumen_bukti)) {
            $this->delete_bukti_file($jawaban->dokumen_bukti);
        }

        $response = $this->format_penilaian_response($result['jawaban']);
        $response['success'] = TRUE;
        $response['message'] = $result['message'];
        $this->json_response($response);
    }

    public function save_penilaian_draft($tugas_id)
    {
        if (!$this->require_post()) {
            return;
        }

        $tugas_id = (int) $tugas_id;
        $result = $this->Jawaban_model->save_penilaian_batch(
            $tugas_id,
            $this->user_id(),
            $this->input->post(NULL, TRUE)
        );

        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('auditor/penilaian/form/' . $tugas_id);
    }

    public function submit_penilaian($tugas_id)
    {
        if (!$this->require_post()) {
            return;
        }

        $tugas_id = (int) $tugas_id;
        $draft_result = $this->Jawaban_model->save_penilaian_batch(
            $tugas_id,
            $this->user_id(),
            $this->input->post(NULL, TRUE)
        );

        if (!$draft_result['success']) {
            $this->session->set_flashdata('error', $draft_result['message']);
            redirect('auditor/penilaian/form/' . $tugas_id);
            return;
        }

        $result = $this->Jawaban_model->submit_penilaian($tugas_id, $this->user_id());
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('auditor/penilaian/form/' . $tugas_id);
    }

    public function revisi_penilaian($tugas_id)
    {
        if (!$this->require_post()) {
            return;
        }

        $tugas_id = (int) $tugas_id;
        $result = $this->Jawaban_model->revisi_tugas($tugas_id, $this->user_id());
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('auditor/penilaian');
    }

    public function download_bukti_penilaian($jawaban_id)
    {
        $jawaban = $this->Jawaban_model->find_jawaban_for_auditor((int) $jawaban_id, $this->user_id());
        if (!$jawaban || empty($jawaban->dokumen_bukti)) {
            show_error('File bukti tidak ditemukan.', 404, 'File tidak ditemukan');
            return;
        }

        $path = private_storage_path('bukti_auditor', $jawaban->dokumen_bukti);
        if ($path === NULL) {
            show_error('File bukti tidak ditemukan di server.', 404, 'File tidak ditemukan');
            return;
        }

        force_download($path, NULL);
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
        $this->form_penilaian((int) $tugas_id);
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

    private function get_penilaian_detail_or_404($tugas_id)
    {
        $tugas = $this->Jawaban_model->find_tugas_for_auditor((int) $tugas_id, $this->user_id());
        if (!$tugas) {
            show_error('Tugas audit tidak ditemukan atau bukan milik Anda.', 404, 'Tugas tidak ditemukan');
            exit;
        }

        return [
            'tugas' => $tugas,
            'jawaban' => $this->Jawaban_model->get_by_tugas((int) $tugas_id),
        ];
    }

    private function require_post()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            return FALSE;
        }

        return TRUE;
    }

    private function require_post_json()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            $this->json_response(['success' => FALSE, 'message' => 'Method tidak diizinkan.'], 405);
            return FALSE;
        }

        return TRUE;
    }

    private function handle_bukti_upload($jawaban_id)
    {
        if (empty($_FILES['dokumen_bukti']['name'])) {
            return ['success' => TRUE, 'file_name' => NULL, 'message' => ''];
        }

        $upload_dir = $this->bukti_upload_dir();
        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, TRUE)) {
            return ['success' => FALSE, 'file_name' => NULL, 'message' => 'Folder upload bukti tidak dapat dibuat.'];
        }

        $config = [
            'upload_path' => $upload_dir,
            'allowed_types' => 'pdf|doc|docx|jpg|jpeg|png',
            'max_size' => 5120,
            'file_name' => 'bukti_auditor_' . (int) $jawaban_id . '_' . date('YmdHis'),
            'overwrite' => FALSE,
            'remove_spaces' => TRUE,
        ];

        $this->load->library('upload');
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('dokumen_bukti')) {
            return [
                'success' => FALSE,
                'file_name' => NULL,
                'message' => strip_tags($this->upload->display_errors('', '')),
            ];
        }

        $upload_data = $this->upload->data();
        return ['success' => TRUE, 'file_name' => $upload_data['file_name'], 'message' => ''];
    }

    private function format_penilaian_response($jawaban)
    {
        $skor = $jawaban && $jawaban->skor !== NULL ? (int) $jawaban->skor : 0;
        $skor_options = skor_audit_options();
        $jenis_temuan = $jawaban ? (string) $jawaban->jenis_temuan : '';
        $dokumen_bukti = $jawaban ? (string) $jawaban->dokumen_bukti : '';

        return [
            'csrf' => [
                'name' => $this->security->get_csrf_token_name(),
                'hash' => $this->security->get_csrf_hash(),
            ],
            'jawaban' => [
                'id' => $jawaban ? (int) $jawaban->id : 0,
                'skor' => $skor,
                'skor_label' => $skor > 0 ? $skor . ' - ' . ($skor_options[$skor] ?? '') : 'Belum ada',
                'temuan' => $jawaban ? (string) $jawaban->temuan : '',
                'jenis_temuan' => $jenis_temuan,
                'jenis_temuan_label' => $jenis_temuan !== '' ? strtoupper($jenis_temuan) : '-',
                'saran_perbaikan' => $jawaban ? (string) $jawaban->saran_perbaikan : '',
                'rencana_perbaikan' => $jawaban ? (string) $jawaban->rencana_perbaikan : '',
                'tgl_bukti' => $jawaban ? (string) $jawaban->tgl_bukti : '',
                'tgl_bukti_label' => !empty($jawaban->tgl_bukti) ? format_tanggal_indo($jawaban->tgl_bukti) : '-',
                'dokumen_bukti' => $dokumen_bukti,
                'dokumen_bukti_label' => $dokumen_bukti !== '' ? $dokumen_bukti : '-',
                'download_url' => $dokumen_bukti !== '' ? site_url('auditor/penilaian/download_bukti/' . (int) $jawaban->id) : '',
                'status_label' => $skor > 0 ? 'Sudah dinilai' : 'Belum dinilai',
                'status_class' => $skor > 0 ? 'status-dinilai' : 'status-belum_diisi',
                'status_icon' => $skor > 0 ? 'fa-check-circle' : 'fa-exclamation-circle',
            ],
        ];
    }

    private function bukti_upload_dir()
    {
        return private_storage_dir('bukti_auditor');
    }

    private function delete_bukti_file($file_name)
    {
        delete_private_file('bukti_auditor', $file_name);
    }

    private function json_response($payload, $status = 200)
    {
        if (!isset($payload['csrf'])) {
            $payload['csrf'] = [
                'name' => $this->security->get_csrf_token_name(),
                'hash' => $this->security->get_csrf_hash(),
            ];
        }

        $this->output
            ->set_status_header((int) $status)
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    private function user_id()
    {
        return (int) $this->session->userdata('user_id');
    }
}
