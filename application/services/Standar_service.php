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

    public function create_standar($data)
    {
        if ($this->standar_model->create($data)) {
            return ['success' => true, 'message' => 'Standar berhasil ditambahkan.'];
        }
        return ['success' => false, 'message' => 'Gagal menambahkan standar.'];
    }
}
