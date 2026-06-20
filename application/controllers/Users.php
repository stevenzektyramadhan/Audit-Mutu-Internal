<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->library('auth_guard');
        
        // Memastikan hanya super_admin yang bisa mengakses halaman ini
        $this->auth_guard->check();
        $this->auth_guard->only(['super_admin']);
        
        require_once APPPATH . 'services/User_service.php';
        $this->user_service = new User_service();
    }

    public function index()
    {
        $data['title'] = 'Data Pengguna - AMI';
        $data['page_title'] = 'Data Pengguna';
        $data['page_subtitle'] = 'Beranda / Data Pengguna';
        $data['active_menu'] = 'users';
        $data['users'] = $this->user_service->get_all_users();
        
        $this->load->view('users/index', $data);
    }

    public function create()
    {
        $this->load->helper('form');
        $data['title'] = 'Tambah Pengguna - AMI';
        $data['page_title'] = 'Tambah Pengguna';
        $data['page_subtitle'] = 'Beranda / Data Pengguna / Tambah';
        $data['active_menu'] = 'users';
        
        $this->load->view('users/create', $data);
    }

    public function store()
    {
        $this->load->helper('form');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('nama', 'Nama', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Password', 'required');
        $this->form_validation->set_rules('role', 'Role', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->create();
        } else {
            $data = [
                'nama' => $this->input->post('nama'),
                'email' => $this->input->post('email'),
                'password' => $this->input->post('password'),
                'role' => $this->input->post('role')
            ];

            $result = $this->user_service->create_user($data);

            if ($result['success']) {
                $this->session->set_flashdata('success', $result['message']);
                redirect('users');
            } else {
                $this->session->set_flashdata('error', $result['message']);
                $this->create();
            }
        }
    }
}
