<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_unit_assignments extends MY_Controller
{
    public $session;
    public $input;
    public $form_validation;

    protected $assignment_service;

    public function __construct()
    {
        parent::__construct();
        $this->_require_capability(
            Authorization_policy::CAP_USER_UNIT_ASSIGNMENTS_MANAGE
        );
        $this->load->helper(['url', 'form']);
        $this->load->library('form_validation');

        require_once APPPATH . 'services/User_unit_assignment_service.php';
        $this->assignment_service = new User_unit_assignment_service();
        if (!$this->assignment_service->schema_ready()) {
            show_error(
                'Assignment unit dan jabatan belum tersedia. Jalankan migration M2-02 terlebih dahulu.',
                503,
                'Service Unavailable'
            );
            exit;
        }
    }

    public function index($user_id)
    {
        $user = $this->authorized_user((int) $user_id);
        $this->load->view('lpmpi/user_unit_assignments/index', [
            'title' => 'Unit & Jabatan - AMI',
            'page_title' => 'Unit & Jabatan',
            'page_subtitle' => 'Pengaturan / Pengguna / Unit & Jabatan',
            'active_menu' => $this->session->userdata('role') === 'super_admin'
                ? 'users'
                : 'akun',
            'user' => $user,
            'assignments' => $this->assignment_service->get_for_user($user->id),
            'today' => date('Y-m-d'),
        ]);
    }

    public function create($user_id)
    {
        $user = $this->authorized_user((int) $user_id);
        $this->load->view('lpmpi/user_unit_assignments/form', [
            'title' => 'Tambah Unit & Jabatan - AMI',
            'page_title' => 'Tambah Unit & Jabatan',
            'page_subtitle' => 'Pengaturan / Pengguna / Unit & Jabatan / Tambah',
            'active_menu' => $this->session->userdata('role') === 'super_admin'
                ? 'users'
                : 'akun',
            'user' => $user,
            'units' => $this->assignment_service->get_active_units(),
            'action' => 'user-unit-assignments/' . (int) $user->id . '/store',
        ]);
    }

    public function store($user_id)
    {
        $this->require_post();
        $user = $this->authorized_user((int) $user_id);
        $this->set_validation_rules();
        if ($this->form_validation->run() === FALSE) {
            $this->create($user->id);
            return;
        }

        $result = $this->assignment_service->create($user->id, [
            'organization_unit_id' => $this->input->post('organization_unit_id', TRUE),
            'position_code' => $this->input->post('position_code', TRUE),
            'valid_from' => $this->input->post('valid_from', TRUE),
            'valid_until' => $this->input->post('valid_until', TRUE),
            'is_primary' => $this->input->post('is_primary'),
        ]);
        $this->session->set_flashdata(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
        redirect(
            $result['success']
                ? 'user-unit-assignments/' . (int) $user->id
                : 'user-unit-assignments/' . (int) $user->id . '/create'
        );
    }

    public function end($assignment_id)
    {
        $this->require_post();
        $assignment = $this->assignment_service->find((int) $assignment_id);
        if (!$assignment) {
            show_error('Assignment tidak ditemukan.', 404, 'Not Found');
            return;
        }
        $this->authorized_user((int) $assignment->user_id);

        $result = $this->assignment_service->end(
            (int) $assignment->id,
            $this->input->post('valid_until', TRUE)
        );
        $this->session->set_flashdata(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
        redirect('user-unit-assignments/' . (int) $assignment->user_id);
    }

    protected function authorized_user($user_id)
    {
        if (!$this->authorization_policy->canManageUserUnitAssignments(
            $this->_user_id(),
            (int) $user_id
        )) {
            show_error('Akses ditolak.', 403, 'Forbidden');
            exit;
        }

        return $this->assignment_service->find_user((int) $user_id);
    }

    protected function set_validation_rules()
    {
        $this->form_validation->set_rules(
            'organization_unit_id',
            'Unit Organisasi',
            'required|integer'
        );
        $this->form_validation->set_rules(
            'position_code',
            'Kode Jabatan',
            'required|max_length[64]'
        );
        $this->form_validation->set_rules('valid_from', 'Berlaku Mulai', 'required');
        $this->form_validation->set_rules('valid_until', 'Berlaku Sampai', '');
    }

    protected function require_post()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            exit;
        }
    }
}
