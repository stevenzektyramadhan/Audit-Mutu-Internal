<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Akun Controller - Manajemen Akun Auditee & Auditor (Admin LPMPI)
 *
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Session $session
 */
class Akun extends Admin_Lpmpi_Controller
{
    /** @var User_service */
    protected $user_service;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->library('form_validation');

        require_once APPPATH . 'services/User_service.php';
        $this->user_service = new User_service();
    }

    public function index()
    {
        $role_filter = (string) $this->input->get('role', TRUE);
        $filters = [
            'q' => trim((string) $this->input->get('q', TRUE)),
            'role' => in_array($role_filter, ['auditor', 'auditee'], TRUE) ? $role_filter : '',
        ];

        $data['title'] = 'Akun Auditee & Auditor';
        $data['page_title'] = 'Akun Auditee / Auditor';
        $data['page_subtitle'] = 'Kelola akun auditee dan auditor';
        $data['active_menu'] = 'akun';
        $data['filters'] = $filters;
        $data['auditee_list'] = [];
        $data['auditor_list'] = [];

        foreach ($this->user_service->get_lpmpi_accounts($filters) as $account) {
            if ($account->role === 'auditee') {
                $data['auditee_list'][] = $account;
            } elseif ($account->role === 'auditor') {
                $data['auditor_list'][] = $account;
            }
        }

        $this->load->view('lpmpi/akun/index', $data);
    }

    public function create()
    {
        $data = $this->form_data('Tambah Akun', 'lpmpi/akun/store', NULL);
        $this->load->view('lpmpi/akun/form_tambah', $data);
    }

    public function store()
    {
        $this->set_validation_rules(TRUE);

        if ($this->form_validation->run() === FALSE) {
            $this->create();
            return;
        }

        $result = $this->user_service->create_lpmpi_account($this->input_data());
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);

        if ($result['success']) {
            redirect('lpmpi/akun');
            return;
        }

        $this->create();
    }

    public function edit($id)
    {
        $account = $this->user_service->get_user((int) $id);
        if (!$account || !in_array($account->role, ['auditor', 'auditee'], TRUE)) {
            show_error('Akun tidak ditemukan.', 404, 'Not Found');
            return;
        }

        $data = $this->form_data('Edit Akun', 'lpmpi/akun/update/' . (int) $id, $account);
        $this->load->view('lpmpi/akun/form_edit', $data);
    }

    public function update($id)
    {
        $this->set_validation_rules(FALSE);

        if ($this->form_validation->run() === FALSE) {
            $this->edit($id);
            return;
        }

        $result = $this->user_service->update_lpmpi_account((int) $id, $this->input_data());
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect($result['success'] ? 'lpmpi/akun' : 'lpmpi/akun/edit/' . (int) $id);
    }

    public function delete($id)
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            return;
        }

        $result = $this->user_service->delete_lpmpi_account((int) $id, (int) $this->session->userdata('user_id'));
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('lpmpi/akun');
    }

    private function form_data($page_title, $action, $account)
    {
        return [
            'title' => $page_title . ' - AMI',
            'page_title' => $page_title,
            'page_subtitle' => 'Beranda / Akun Auditee & Auditor / ' . $page_title,
            'active_menu' => 'akun',
            'action' => $action,
            'account' => $account,
        ];
    }

    private function input_data()
    {
        return [
            'nama' => $this->input->post('nama', TRUE),
            'email' => $this->input->post('email', TRUE),
            'password' => $this->input->post('password', TRUE),
            'role' => $this->input->post('role', TRUE),
            'nama_unit' => $this->input->post('nama_unit', TRUE),
            'jenis_unit' => $this->input->post('jenis_unit', TRUE),
        ];
    }

    private function set_validation_rules($password_required)
    {
        $this->form_validation->set_rules('nama', 'Nama', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('role', 'Role', 'required|in_list[auditor,auditee]');

        if ($this->input->post('role', TRUE) === 'auditee') {
            $this->form_validation->set_rules('nama_unit', 'Nama Unit', 'required');
            $this->form_validation->set_rules('jenis_unit', 'Jenis Unit', 'required|in_list[prodi,unit,lembaga]');
        } else {
            $this->form_validation->set_rules('jenis_unit', 'Jenis Unit', 'in_list[,prodi,unit,lembaga]');
        }

        if ($password_required) {
            $this->form_validation->set_rules('password', 'Password', 'required');
        }
    }
}
