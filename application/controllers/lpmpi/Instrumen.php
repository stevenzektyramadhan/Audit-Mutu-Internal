<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Instrumen Controller - Upload Instrumen per Standar (Admin LPMPI)
 *
 * @property CI_Input $input
 * @property CI_Session $session
 * @property CI_Upload $upload
 * @property Standar_model $Standar_model
 */
class Instrumen extends Admin_Lpmpi_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['form', 'url']);
        $this->load->model('Standar_model');
    }

    public function index()
    {
        $data['title'] = 'Instrumen Standar';
        $data['page_title'] = 'Instrumen Standar';
        $data['page_subtitle'] = 'Upload dan kelola file instrumen per standar';
        $data['active_menu'] = 'instrumen';
        $data['standar_list'] = $this->Standar_model->get_all();

        $this->load->view('lpmpi/instrumen/index', $data);
    }

    public function upload($id)
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            return;
        }

        $standar = $this->Standar_model->find((int) $id);
        if (!$standar) {
            show_error('Standar tidak ditemukan.', 404, 'Not Found');
            return;
        }

        $upload_dir = $this->upload_dir();
        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, TRUE)) {
            $this->session->set_flashdata('error', 'Folder upload instrumen tidak dapat dibuat.');
            redirect('lpmpi/instrumen');
            return;
        }

        $config = [
            'upload_path' => $upload_dir,
            'allowed_types' => 'pdf|doc|docx',
            'max_size' => 5120,
            'file_name' => 'instrumen_' . (int) $id . '_' . date('YmdHis'),
            'overwrite' => FALSE,
            'remove_spaces' => TRUE,
        ];

        $this->load->library('upload');
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file_instrumen')) {
            $this->session->set_flashdata('error', strip_tags($this->upload->display_errors('', '')));
            redirect('lpmpi/instrumen');
            return;
        }

        $upload_data = $this->upload->data();
        $new_file = $upload_data['file_name'];

        if (!$this->Standar_model->update_instrumen_file((int) $id, $new_file)) {
            $this->delete_local_file($new_file);
            $this->session->set_flashdata('error', 'Gagal menyimpan data file instrumen.');
            redirect('lpmpi/instrumen');
            return;
        }

        if (!empty($standar->file_instrumen)) {
            $this->delete_local_file($standar->file_instrumen);
        }

        $this->session->set_flashdata('success', 'File instrumen berhasil diupload.');
        redirect('lpmpi/instrumen');
    }

    public function delete($id)
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            return;
        }

        $standar = $this->Standar_model->find((int) $id);
        if (!$standar) {
            show_error('Standar tidak ditemukan.', 404, 'Not Found');
            return;
        }

        if (empty($standar->file_instrumen)) {
            $this->session->set_flashdata('error', 'Standar ini belum memiliki file instrumen.');
            redirect('lpmpi/instrumen');
            return;
        }

        if ($this->Standar_model->clear_instrumen_file((int) $id)) {
            $this->delete_local_file($standar->file_instrumen);
            $this->session->set_flashdata('success', 'File instrumen berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data file instrumen.');
        }

        redirect('lpmpi/instrumen');
    }

    private function upload_dir()
    {
        return FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'instrumen' . DIRECTORY_SEPARATOR;
    }

    private function delete_local_file($file_name)
    {
        $path = $this->upload_dir() . basename((string) $file_name);
        if (is_file($path)) {
            unlink($path);
        }
    }
}
