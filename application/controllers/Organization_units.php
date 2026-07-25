<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Organization_units extends MY_Controller
{
    public $session;
    public $input;
    public $form_validation;

    protected $organization_unit_service;

    public function __construct()
    {
        parent::__construct();
        $this->_require_capability(Authorization_policy::CAP_ORGANIZATION_UNITS_MANAGE);
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->helper(['url', 'form']);

        require_once APPPATH . 'services/Organization_unit_service.php';
        $this->organization_unit_service = new Organization_unit_service();

        if (!$this->organization_unit_service->schema_ready()) {
            show_error(
                'Master unit organisasi belum tersedia. Jalankan migration M2-01 terlebih dahulu.',
                503,
                'Service Unavailable'
            );
            exit;
        }
    }

    public function index()
    {
        $this->load->view('lpmpi/organization_units/index', [
            'title' => 'Unit Organisasi - AMI',
            'page_title' => 'Unit Organisasi',
            'page_subtitle' => 'Pengaturan / Unit Organisasi',
            'active_menu' => 'organization_units',
            'units' => $this->organization_unit_service->get_tree(),
            'type_labels' => $this->organization_unit_service->type_labels(),
        ]);
    }

    public function create()
    {
        $this->render_form(NULL, 'organization-units/store');
    }

    public function store()
    {
        $this->require_post();
        $this->set_validation_rules();

        if ($this->form_validation->run() === FALSE) {
            $this->create();
            return;
        }

        $result = $this->organization_unit_service->create($this->input_data(TRUE));
        if ($result['success']) {
            $this->session->set_flashdata('success', $result['message']);
            redirect('organization-units');
            return;
        }

        $this->session->set_flashdata('error', $result['message']);
        $this->create();
    }

    public function edit($id)
    {
        $unit = $this->organization_unit_service->find((int) $id);
        if (!$unit) {
            show_error('Unit organisasi tidak ditemukan.', 404, 'Not Found');
            return;
        }

        $this->render_form($unit, 'organization-units/update/' . (int) $id);
    }

    public function update($id)
    {
        $this->require_post();
        $id = (int) $id;
        $this->set_validation_rules();

        if ($this->form_validation->run() === FALSE) {
            $this->edit($id);
            return;
        }

        $result = $this->organization_unit_service->update($id, $this->input_data(FALSE));
        if ($result['success']) {
            $this->session->set_flashdata('success', $result['message']);
            redirect('organization-units');
            return;
        }

        $this->session->set_flashdata('error', $result['message']);
        $this->edit($id);
    }

    public function toggle_active($id)
    {
        $this->require_post();
        $result = $this->organization_unit_service->toggle_active((int) $id);
        $this->session->set_flashdata(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
        redirect('organization-units');
    }

    protected function render_form($unit, $action)
    {
        $is_edit = $unit !== NULL;
        $this->load->view('lpmpi/organization_units/form', [
            'title' => ($is_edit ? 'Edit' : 'Tambah') . ' Unit Organisasi - AMI',
            'page_title' => ($is_edit ? 'Edit' : 'Tambah') . ' Unit Organisasi',
            'page_subtitle' => 'Pengaturan / Unit Organisasi / ' . ($is_edit ? 'Edit' : 'Tambah'),
            'active_menu' => 'organization_units',
            'unit' => $unit,
            'action' => $action,
            'type_labels' => $this->organization_unit_service->type_labels(),
            'allowed_parent_types' => $this->organization_unit_service->allowed_parent_types(),
            'parent_options' => $this->organization_unit_service->get_parent_options(
                $is_edit ? (int) $unit->id : NULL
            ),
        ]);
    }

    protected function set_validation_rules()
    {
        $this->form_validation->set_rules('code', 'Kode', 'required|max_length[50]');
        $this->form_validation->set_rules('name', 'Nama Unit', 'required|max_length[200]');
        $this->form_validation->set_rules(
            'type',
            'Jenis Unit',
            'required|in_list[university,faculty,study_program,institute,bureau,unit]'
        );
        $this->form_validation->set_rules('parent_id', 'Unit Induk', 'integer');
    }

    protected function input_data($include_active)
    {
        $data = [
            'code' => $this->input->post('code', TRUE),
            'name' => $this->input->post('name', TRUE),
            'type' => $this->input->post('type', TRUE),
            'parent_id' => $this->input->post('parent_id', TRUE),
        ];

        if ($include_active) {
            $data['active'] = (int) $this->input->post('active') === 1 ? 1 : 0;
        }

        return $data;
    }

    protected function require_post()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            exit;
        }
    }
}
