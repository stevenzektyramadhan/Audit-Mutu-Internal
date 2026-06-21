<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pertanyaan_service
{
    protected $ci;
    protected $pertanyaan_model;
    protected $standar_model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Pertanyaan_model');
        $this->ci->load->model('Standar_model');
        $this->pertanyaan_model = $this->ci->Pertanyaan_model;
        $this->standar_model = $this->ci->Standar_model;
    }

    public function get_all_pertanyaan()
    {
        return $this->pertanyaan_model->get_all_with_standar();
    }

    public function get_pertanyaan($id)
    {
        return $this->pertanyaan_model->find($id);
    }
    
    public function get_all_standar()
    {
        return $this->standar_model->get_all_with_count();
    }

    public function create_pertanyaan($data)
    {
        $validation = $this->validate_data($data);
        if (!$validation['success']) {
            return $validation;
        }

        $data = $validation['data'];
        if ($this->pertanyaan_model->create($data)) {
            return ['success' => true, 'message' => 'Pertanyaan berhasil ditambahkan.'];
        }
        return ['success' => false, 'message' => 'Gagal menambahkan pertanyaan.'];
    }

    public function update_pertanyaan($id, $data)
    {
        $pertanyaan = $this->pertanyaan_model->find($id);
        if (!$pertanyaan) {
            return ['success' => FALSE, 'message' => 'Pertanyaan tidak ditemukan.'];
        }

        $validation = $this->validate_data($data);
        if (!$validation['success']) {
            return $validation;
        }

        if ((int) $validation['data']['standar_id'] !== (int) $pertanyaan->standar_id
            && $this->pertanyaan_model->is_used_in_answers($id)) {
            return ['success' => FALSE, 'message' => 'Pertanyaan yang sudah digunakan dalam audit tidak dapat dipindahkan ke standar lain.'];
        }

        if ($this->pertanyaan_model->update($id, $validation['data'])) {
            return ['success' => TRUE, 'message' => 'Pertanyaan berhasil diperbarui.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal memperbarui pertanyaan.'];
    }

    public function delete_pertanyaan($id)
    {
        if (!$this->pertanyaan_model->find($id)) {
            return ['success' => FALSE, 'message' => 'Pertanyaan tidak ditemukan.'];
        }

        if ($this->pertanyaan_model->delete($id)) {
            return ['success' => TRUE, 'message' => 'Pertanyaan berhasil dihapus.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal menghapus pertanyaan.'];
    }

    private function validate_data($data)
    {
        $standar_id = isset($data['standar_id']) ? (int) $data['standar_id'] : 0;
        $isi_pertanyaan = trim(isset($data['isi_pertanyaan']) ? $data['isi_pertanyaan'] : '');

        if (!$this->standar_model->find($standar_id)) {
            return ['success' => FALSE, 'message' => 'Standar yang dipilih tidak valid.'];
        }

        if ($isi_pertanyaan === '') {
            return ['success' => FALSE, 'message' => 'Isi pertanyaan wajib diisi.'];
        }

        return [
            'success' => TRUE,
            'data' => ['standar_id' => $standar_id, 'isi_pertanyaan' => $isi_pertanyaan],
        ];
    }
}
