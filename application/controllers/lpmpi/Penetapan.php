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
        $this->load->helper(['form', 'url']);
        $this->load->model('Penetapan_model');
        $this->load->model('Standar_model');
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

        $new_file = $this->handle_upload((int) $id);
        if ($new_file === FALSE) {
            redirect('lpmpi/penetapan');
            return;
        }

        if ($new_file !== NULL) {
            $data['file_path'] = $new_file;
        }

        if ($this->Penetapan_model->update((int) $id, $data)) {
            if ($new_file !== NULL && !empty($penetapan->file_path)) {
                $this->delete_local_file($penetapan->file_path);
            }
            $this->session->set_flashdata('success', 'Data penetapan berhasil diperbarui.');
        } else {
            if ($new_file !== NULL) {
                $this->delete_local_file($new_file);
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
            $this->delete_local_file($penetapan->file_path);
            $this->session->set_flashdata('success', 'File penetapan berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus file penetapan.');
        }

        redirect('lpmpi/penetapan');
    }

    private function handle_upload($id)
    {
        if (empty($_FILES['file_penetapan']['name'])) {
            return NULL;
        }

        $upload_dir = $this->upload_dir();
        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, TRUE)) {
            $this->session->set_flashdata('error', 'Folder upload penetapan tidak dapat dibuat.');
            return FALSE;
        }

        $config = [
            'upload_path' => $upload_dir,
            'allowed_types' => 'pdf|doc|docx|xls|xlsx',
            'max_size' => 5120,
            'file_name' => 'penetapan_' . $id . '_' . date('YmdHis'),
            'overwrite' => FALSE,
            'remove_spaces' => TRUE,
        ];

        $this->load->library('upload');
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file_penetapan')) {
            $this->session->set_flashdata('error', strip_tags($this->upload->display_errors('', '')));
            return FALSE;
        }

        $upload_data = $this->upload->data();
        return $upload_data['file_name'];
    }

    private function upload_dir()
    {
        return FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'penetapan' . DIRECTORY_SEPARATOR;
    }

    private function delete_local_file($file_name)
    {
        $path = $this->upload_dir() . basename((string) $file_name);
        if (is_file($path)) {
            unlink($path);
        }
    }
}
