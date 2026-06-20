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
    
    public function get_all_standar()
    {
        return $this->standar_model->get_all_with_count();
    }

    public function create_pertanyaan($data)
    {
        if ($this->pertanyaan_model->create($data)) {
            return ['success' => true, 'message' => 'Pertanyaan berhasil ditambahkan.'];
        }
        return ['success' => false, 'message' => 'Gagal menambahkan pertanyaan.'];
    }
}
