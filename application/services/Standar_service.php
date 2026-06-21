<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Standar_service
{
    protected $ci;
    protected $standar_model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Standar_model');
        $this->standar_model = $this->ci->Standar_model;
    }

    public function get_all_standar()
    {
        return $this->standar_model->get_all_with_count();
    }

    public function get_standar($id)
    {
        return $this->standar_model->find($id);
    }

    public function create_standar($data)
    {
        $data = $this->normalize($data);
        if ($data['nama_standar'] === '') {
            return ['success' => FALSE, 'message' => 'Nama standar wajib diisi.'];
        }

        if ($this->standar_model->create($data)) {
            return ['success' => true, 'message' => 'Standar berhasil ditambahkan.'];
        }
        return ['success' => false, 'message' => 'Gagal menambahkan standar.'];
    }

    public function update_standar($id, $data)
    {
        if (!$this->standar_model->find($id)) {
            return ['success' => FALSE, 'message' => 'Standar tidak ditemukan.'];
        }

        $data = $this->normalize($data);
        if ($data['nama_standar'] === '') {
            return ['success' => FALSE, 'message' => 'Nama standar wajib diisi.'];
        }

        if ($this->standar_model->update($id, $data)) {
            return ['success' => TRUE, 'message' => 'Standar berhasil diperbarui.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal memperbarui standar.'];
    }

    public function delete_standar($id)
    {
        if (!$this->standar_model->find($id)) {
            return ['success' => FALSE, 'message' => 'Standar tidak ditemukan.'];
        }

        if ($this->standar_model->delete($id)) {
            return ['success' => TRUE, 'message' => 'Standar dan data audit terkait berhasil dihapus.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal menghapus standar.'];
    }

    private function normalize($data)
    {
        return [
            'nama_standar' => trim(isset($data['nama_standar']) ? $data['nama_standar'] : ''),
            'deskripsi' => trim(isset($data['deskripsi']) ? $data['deskripsi'] : ''),
        ];
    }
}
