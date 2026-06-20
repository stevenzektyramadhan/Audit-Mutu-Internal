<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_service
{
    protected $ci;
    protected $user_model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('User_model');
        $this->user_model = $this->ci->User_model;
    }

    public function get_all_users()
    {
        return $this->user_model->get_all();
    }

    public function create_user($data)
    {
        $existing_user = $this->user_model->find_by_email($data['email']);
        if ($existing_user) {
            return ['success' => false, 'message' => 'Email sudah terdaftar.'];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        if ($this->user_model->create($data)) {
            return ['success' => true, 'message' => 'Pengguna berhasil ditambahkan.'];
        }

        return ['success' => false, 'message' => 'Gagal menambahkan pengguna.'];
    }
}
