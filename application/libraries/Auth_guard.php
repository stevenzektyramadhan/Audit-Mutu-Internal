<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_guard
{
    protected $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    public function check()
    {
        if (!$this->ci->session->userdata('user_id')) {
            redirect('auth');
            exit;
        }
    }

    public function only(array $roles)
    {
        $this->check();

        $role = $this->ci->session->userdata('role');
        if (!in_array($role, $roles, true)) {
            show_error('Akses ditolak.', 403, 'Forbidden');
        }
    }
}
