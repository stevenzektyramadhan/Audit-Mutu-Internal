<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Penetapan Controller - Halaman Penetapan tiga kategori.
 *
 * @property Penetapan_model $Penetapan_model
 * @property Standar_model $Standar_model
 */
class Penetapan extends Admin_Lpmpi_Controller
{
    private $kategori_list = ['pelaksanaan', 'pengendalian', 'peningkatan'];

    public function __construct()
    {
        parent::__construct();
        $this->_require_capability(
            Authorization_policy::CAP_AUDIT_PACKAGE_MANAGE
        );
        $this->load->helper(['form', 'url', 'download']);
        $this->load->model('Penetapan_model');
        $this->load->model('Standar_model');
        $this->load->library('file_security');
    }

    public function index()
    {
        $standar = $this->Standar_model->get_all();
        $this->Penetapan_model->ensure_records_for_standar($standar, $this->kategori_list);

        $data['title']        = 'Penetapan';
        $data['page_title']   = 'Penetapan';
        $data['page_subtitle'] = 'Kelola penetapan pelaksanaan, pengendalian, dan peningkatan';
        $data['active_menu']  = 'penetapan';
        $data['kategori_list'] = $this->kategori_list;
        $data['penetapan_by_kategori'] = $this->Penetapan_model->get_grouped_by_kategori($this->kategori_list);
        $stored_names = [];
        foreach ($data['penetapan_by_kategori'] as $rows) {
            foreach ($rows as $row) {
                $stored_names[] = (string) $row->file_path;
            }
        }
        $data['file_names'] = $this->file_security->original_names('penetapan', $stored_names);

        $this->load->view('lpmpi/penetapan/index', $data);
    }

    public function update($id)
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            return;
        }

        $penetapan = $this->Penetapan_model->find((int) $id);
        if (!$penetapan) {
            show_error('Data penetapan tidak ditemukan.', 404, 'Not Found');
            return;
        }

        $data = [
            'status' => $this->input->post('status', TRUE),
            'deskripsi' => $this->input->post('deskripsi', TRUE),
        ];

        $upload = $this->handle_upload((int) $id);
        if (!$upload['success']) {
            $this->session->set_flashdata('error', $upload['message']);
            redirect('lpmpi/penetapan');
            return;
        }
        $new_file = $upload['file_name'];

        if ($new_file !== NULL) {
            $data['file_path'] = $new_file;
        }

        if ($this->Penetapan_model->update((int) $id, $data)) {
            if ($new_file !== NULL && !empty($penetapan->file_path)) {
                $this->file_security->retire(
                    'penetapan',
                    $penetapan->file_path,
                    'penetapan',
                    (int) $id,
                    $this->_user_id(),
                    'replaced'
                );
            }
            $this->session->set_flashdata('success', 'Data penetapan berhasil diperbarui.');
        } else {
            if ($new_file !== NULL) {
                $this->file_security->retire(
                    'penetapan',
                    $new_file,
                    'penetapan',
                    (int) $id,
                    $this->_user_id(),
                    'database_update_failed'
                );
            }
            $this->session->set_flashdata('error', 'Gagal memperbarui data penetapan.');
        }

        redirect('lpmpi/penetapan');
    }

    public function delete_file($id)
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            return;
        }

        $penetapan = $this->Penetapan_model->find((int) $id);
        if (!$penetapan || empty($penetapan->file_path)) {
            $this->session->set_flashdata('error', 'File penetapan tidak ditemukan.');
            redirect('lpmpi/penetapan');
            return;
        }

        if ($this->Penetapan_model->update((int) $id, ['file_path' => NULL])) {
            $this->file_security->retire(
                'penetapan',
                $penetapan->file_path,
                'penetapan',
                (int) $id,
                $this->_user_id(),
                'user_deleted'
            );
            $this->session->set_flashdata('success', 'File penetapan berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus file penetapan.');
        }

        redirect('lpmpi/penetapan');
    }

    public function download($id)
    {
        $penetapan = $this->Penetapan_model->find((int) $id);
        if (!$penetapan || empty($penetapan->file_path)
            || !$this->file_security->download(
                'penetapan',
                $penetapan->file_path,
                'penetapan',
                (int) $id,
                $this->_user_id()
            )) {
            show_error('File penetapan tidak ditemukan.', 404, 'File tidak ditemukan');
            return;
        }
    }

    private function handle_upload($id)
    {
        if (empty($_FILES['file_penetapan']['name'])) {
            return ['success' => TRUE, 'file_name' => NULL, 'message' => ''];
        }

        return $this->file_security->upload(
            'file_penetapan',
            'penetapan',
            'penetapan',
            (int) $id,
            $this->_user_id()
        );
    }
}
