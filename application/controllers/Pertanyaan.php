<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pertanyaan extends Admin_Lpmpi_Controller {

    /** @var CI_Session */
    public $session;

    /** @var CI_Input */
    public $input;

    /** @var CI_Form_validation */
    public $form_validation;

    /** @var Pertanyaan_service */
    protected $pertanyaan_service;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['form', 'url']);
        $this->load->library(['form_validation', 'session']);
        
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
        $data['standar_id'] = $filter_standar_id;
        
        $this->load->view('pertanyaan/index', $data);
    }

    public function download_template($standar_id)
    {
        $standar = $this->pertanyaan_service->get_standar((int) $standar_id);
        if (!$standar) {
            show_error('Standar tidak ditemukan.', 404, 'Not Found');
            return;
        }

        $spreadsheet = $this->pertanyaan_service->generate_template((int) $standar_id);
        $standard_name = preg_replace('/[^A-Za-z0-9_-]+/', '_', $standar->nama_standar);
        $standard_name = trim($standard_name, '_');
        $filename = 'template_pertanyaan_' . ($standard_name !== '' ? $standard_name : (int) $standar_id) . '.xlsx';

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        $spreadsheet->disconnectWorksheets();
        exit;
    }

    public function import($standar_id)
    {
        $this->require_post();
        $standar_id = (int) $standar_id;
        $standar = $this->pertanyaan_service->get_standar($standar_id);
        if (!$standar) {
            show_error('Standar tidak ditemukan.', 404, 'Not Found');
            return;
        }

        if (!isset($_FILES['file_excel']) || (int) $_FILES['file_excel']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->redirect_import_error($standar_id, 'File Excel wajib diunggah.');
            return;
        }

        $extension = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['xlsx', 'xls'], TRUE)) {
            $this->redirect_import_error($standar_id, 'Format file tidak didukung. Gunakan file .xlsx atau .xls.');
            return;
        }

        if ((int) $_FILES['file_excel']['size'] > 2 * 1024 * 1024) {
            $this->redirect_import_error($standar_id, 'Ukuran file maksimal 2MB.');
            return;
        }

        $upload_dir = $this->temporary_upload_dir();
        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, TRUE)) {
            $this->redirect_import_error($standar_id, 'Folder temporary upload tidak dapat dibuat.');
            return;
        }

        $config = [
            'upload_path' => $upload_dir,
            'allowed_types' => 'xlsx|xls',
            'max_size' => 2048,
            'file_name' => 'pertanyaan_' . $this->_user_id() . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)),
            'overwrite' => FALSE,
            'remove_spaces' => TRUE,
        ];
        $this->load->library('upload');
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file_excel')) {
            $message = strip_tags($this->upload->display_errors('', ''));
            $this->redirect_import_error($standar_id, $message !== '' ? $message : 'File gagal diunggah.');
            return;
        }

        $upload_data = $this->upload->data();
        $file_path = $upload_data['full_path'];

        $parse_error = NULL;
        try {
            $result = $this->pertanyaan_service->import_excel($standar_id, $file_path);
        } catch (\Throwable $exception) {
            $parse_error = $exception->getMessage();
        } finally {
            if (is_file($file_path)) {
                unlink($file_path);
            }
        }

        if ($parse_error !== NULL) {
            $this->redirect_import_error($standar_id, $parse_error);
            return;
        }

        if (empty($result['valid'])) {
            $this->redirect_import_error($standar_id, 'Tidak ada baris valid yang dapat diimport. Periksa kembali isi file Excel.');
            return;
        }

        $token = bin2hex(random_bytes(16));
        $imports = $this->active_imports();
        $imports[$token] = [
            'user_id' => $this->_user_id(),
            'standar_id' => $standar_id,
            'valid' => $result['valid'],
            'errors' => $result['errors'],
            'warnings' => $result['warnings'],
            'total' => $result['total'],
            'created_at' => time(),
        ];
        $this->session->set_userdata('pertanyaan_imports', $imports);

        $data['title'] = 'Preview Import Pertanyaan - AMI';
        $data['page_title'] = 'Preview Import Pertanyaan';
        $data['page_subtitle'] = 'Beranda / Data Pertanyaan / Preview Import';
        $data['active_menu'] = 'pertanyaan';
        $data['standar'] = $standar;
        $data['standar_id'] = $standar_id;
        $data['import_token'] = $token;
        $data['valid'] = $result['valid'];
        $data['errors'] = $result['errors'];
        $data['warnings'] = $result['warnings'];
        $data['total'] = $result['total'];

        $this->load->view('pertanyaan/import_preview', $data);
    }

    public function import_confirm($standar_id)
    {
        $this->require_post();
        $standar_id = (int) $standar_id;

        if (!$this->pertanyaan_service->get_standar($standar_id)) {
            show_error('Standar tidak ditemukan.', 404, 'Not Found');
            return;
        }

        $this->form_validation->set_rules('import_token', 'Token import', 'required|alpha_numeric|exact_length[32]');
        if ($this->form_validation->run() === FALSE) {
            $this->redirect_import_error($standar_id, 'Sesi preview import tidak valid. Silakan upload ulang file Excel.');
            return;
        }

        $token = (string) $this->input->post('import_token', TRUE);
        $imports = $this->active_imports();
        if (!isset($imports[$token])
            || (int) $imports[$token]['user_id'] !== $this->_user_id()
            || (int) $imports[$token]['standar_id'] !== $standar_id) {
            $this->redirect_import_error($standar_id, 'Sesi preview import sudah kedaluwarsa atau tidak ditemukan.');
            return;
        }

        $payload = $imports[$token];
        unset($imports[$token]);
        $this->session->set_userdata('pertanyaan_imports', $imports);

        $inserted = $this->pertanyaan_service->insert_bulk($standar_id, $payload['valid']);
        if ($inserted === FALSE) {
            $this->redirect_import_error($standar_id, 'Gagal menyimpan data import. Seluruh perubahan telah dibatalkan.');
            return;
        }

        $message = (int) $inserted . ' baris berhasil diimport.';
        if (!empty($payload['errors'])) {
            $error_rows = [];
            foreach ($payload['errors'] as $error) {
                $error_rows[(int) $error['row']] = TRUE;
            }
            $message .= ' ' . count($error_rows) . ' baris error dilewati.';
        }
        if (!empty($payload['warnings'])) {
            $message .= ' ' . count($payload['warnings']) . ' warning dikoreksi otomatis.';
        }

        $this->session->set_flashdata('success', $message);
        redirect('pertanyaan?standar_id=' . $standar_id);
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

    private function redirect_import_error($standar_id, $message)
    {
        $this->session->set_flashdata('error', $message);
        redirect('pertanyaan?standar_id=' . (int) $standar_id);
    }

    private function temporary_upload_dir()
    {
        return private_storage_dir('tmp');
    }

    private function active_imports()
    {
        $imports = $this->session->userdata('pertanyaan_imports');
        $imports = is_array($imports) ? $imports : [];
        $cutoff = time() - 1800;

        foreach ($imports as $token => $payload) {
            if (!isset($payload['created_at']) || (int) $payload['created_at'] < $cutoff) {
                unset($imports[$token]);
            }
        }

        return $imports;
    }
}
