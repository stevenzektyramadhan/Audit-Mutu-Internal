<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Instrumen Controller - Upload Instrumen per Standar (Admin LPMPI)
 *
 * @property CI_Input $input
 * @property CI_Session $session
 * @property File_security $file_security
 * @property Standar_model $Standar_model
 */
class Instrumen extends Admin_Lpmpi_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->_require_capability(
            Authorization_policy::CAP_AUDIT_PACKAGE_MANAGE
        );
        $this->load->helper(['form', 'url', 'download']);
        $this->load->model('Standar_model');
        $this->load->library('file_security');
    }

    public function index()
    {
        $data['title'] = 'Instrumen Standar';
        $data['page_title'] = 'Instrumen Standar';
        $data['page_subtitle'] = 'Upload dan kelola file instrumen per standar';
        $data['active_menu'] = 'instrumen';
        $data['standar_list'] = $this->Standar_model->get_all();
        $stored_names = array_map(function ($row) {
            return (string) $row->file_instrumen;
        }, $data['standar_list']);
        $data['file_names'] = $this->file_security->original_names('instrumen', $stored_names);

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

        $upload = $this->file_security->upload(
            'file_instrumen',
            'instrumen',
            'standar',
            (int) $id,
            $this->_user_id()
        );
        if (!$upload['success']) {
            $this->session->set_flashdata('error', $upload['message']);
            redirect('lpmpi/instrumen');
            return;
        }

        $new_file = $upload['file_name'];

        if (!$this->Standar_model->update_instrumen_file((int) $id, $new_file)) {
            $this->file_security->retire(
                'instrumen',
                $new_file,
                'standar',
                (int) $id,
                $this->_user_id(),
                'database_update_failed'
            );
            $this->session->set_flashdata('error', 'Gagal menyimpan data file instrumen.');
            redirect('lpmpi/instrumen');
            return;
        }

        if (!empty($standar->file_instrumen)) {
            $this->file_security->retire(
                'instrumen',
                $standar->file_instrumen,
                'standar',
                (int) $id,
                $this->_user_id(),
                'replaced'
            );
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
            $this->file_security->retire(
                'instrumen',
                $standar->file_instrumen,
                'standar',
                (int) $id,
                $this->_user_id(),
                'user_deleted'
            );
            $this->session->set_flashdata('success', 'File instrumen berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data file instrumen.');
        }

        redirect('lpmpi/instrumen');
    }

    public function download($id)
    {
        $standar = $this->Standar_model->find((int) $id);
        if (!$standar || empty($standar->file_instrumen)
            || !$this->file_security->download(
                'instrumen',
                $standar->file_instrumen,
                'standar',
                (int) $id,
                $this->_user_id()
            )) {
            show_error('File instrumen tidak ditemukan.', 404, 'File tidak ditemukan');
            return;
        }
    }
}
