<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_service
{
    protected $ci;
    protected $user_model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('User_model');
        $this->user_model = $this->ci->User_model;
    }

    public function login($email, $password)
    {
        $user = $this->user_model->find_by_email($email);

        if (!$user) {
            return ['success' => false, 'message' => 'Email atau password salah.'];
        }

        if (!password_verify($password, $user->password)) {
            return ['success' => false, 'message' => 'Email atau password salah.'];
        }

        $session_data = [
            'user_id' => $user->id,
            'nama' => $user->nama,
            'email' => $user->email,
            'role' => $user->role,
            'logged_in' => TRUE,
        ];

        $this->ci->session->set_userdata($session_data);
        $this->ci->session->sess_regenerate(TRUE);

        return ['success' => true, 'message' => 'Login berhasil.'];
    }
}
