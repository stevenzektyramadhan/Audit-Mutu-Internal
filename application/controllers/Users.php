<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller {

    /** @var CI_Session */
    public $session;

    /** @var CI_Input */
    public $input;

    /** @var CI_Form_validation */
    public $form_validation;

    /** @var Auth_guard */
    public $auth_guard;

    /** @var User_service */
    protected $user_service;

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
        $role_filter = (string) $this->input->get('role', TRUE);
        $filters = [
            'q' => trim((string) $this->input->get('q', TRUE)),
            'role' => in_array($role_filter, ['super_admin', 'auditor', 'auditee'], TRUE) ? $role_filter : '',
        ];

        $data['title'] = 'Data Pengguna - AMI';
        $data['page_title'] = 'Data Pengguna';
        $data['page_subtitle'] = 'Beranda / Data Pengguna';
        $data['active_menu'] = 'users';
        $data['users'] = $this->user_service->get_all_users($filters);
        $data['filters'] = $filters;
        
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
        $this->form_validation->set_rules('role', 'Role', 'required|in_list[super_admin,auditor,auditee]');

        if ($this->form_validation->run() === FALSE) {
            $this->create();
        } else {
            $data = [
                'nama' => $this->input->post('nama', TRUE),
                'email' => $this->input->post('email', TRUE),
                'password' => $this->input->post('password', TRUE),
                'role' => $this->input->post('role', TRUE)
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

    public function edit($id)
    {
        $user = $this->user_service->get_user((int) $id);
        if (!$user) {
            show_error('Pengguna tidak ditemukan.', 404, 'Not Found');
            return;
        }

        $data['title'] = 'Edit Pengguna - AMI';
        $data['page_title'] = 'Edit Pengguna';
        $data['page_subtitle'] = 'Beranda / Data Pengguna / Edit';
        $data['active_menu'] = 'users';
        $data['user'] = $user;

        $this->load->view('users/edit', $data);
    }

    public function update($id)
    {
        $this->form_validation->set_rules('nama', 'Nama', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('role', 'Role', 'required|in_list[super_admin,auditor,auditee]');

        if ($this->form_validation->run() === FALSE) {
            $this->edit($id);
            return;
        }

        $result = $this->user_service->update_user((int) $id, [
            'nama' => $this->input->post('nama', TRUE),
            'email' => $this->input->post('email', TRUE),
            'password' => $this->input->post('password', TRUE),
            'role' => $this->input->post('role', TRUE),
        ]);

        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect($result['success'] ? 'users' : 'users/edit/' . (int) $id);
    }

    public function delete($id)
    {
        $this->require_post();
        $result = $this->user_service->delete_user((int) $id, (int) $this->session->userdata('user_id'));
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('users');
    }

    private function require_post()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            exit;
        }
    }
}
