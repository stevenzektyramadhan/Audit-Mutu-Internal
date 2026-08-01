<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Organization extends Admin_Lpmpi_Controller
{
    protected $organization_service;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['form', 'url']);
        $this->load->library('form_validation');
        require_once APPPATH . 'services/Organization_service.php';
        $this->organization_service = new Organization_service();
    }

    public function index()
    {
        $this->require_capability('organization.view');
        $data = $this->page_data('Struktur Organisasi', 'Struktur Organisasi');
        $data['units'] = $this->organization_service->units();
        $data['assignments'] = $this->organization_service->assignments();
        $data['can_manage'] = $this->can('organization.manage');
        $data['can_assign'] = $this->can('organization.assignment.manage');
        $data['can_capabilities'] = $this->can('organization.capability.manage');
        $data['can_view_capabilities'] = $this->can('organization.view');
        $this->load->view('lpmpi/organization/index', $data);
    }

    public function create()
    {
        $this->require_capability('organization.manage');
        $this->load->view('lpmpi/organization/unit_form', $this->unit_form_data('Tambah Unit Organisasi', 'lpmpi/organization/unit/store', NULL));
    }

    public function store_unit()
    {
        $this->require_post_capability('organization.manage');
        $this->set_unit_rules();
        if (!$this->form_validation->run()) { $this->create(); return; }
        $result = $this->organization_service->create_unit($this->unit_input());
        $this->flash_redirect($result, 'lpmpi/organization');
    }

    public function edit($id)
    {
        $this->require_capability('organization.manage');
        $unit = $this->organization_service->find_unit((int) $id);
        if (!$unit || $unit->parent_id === NULL) show_error('Unit tidak ditemukan.', 404, 'Not Found');
        $this->load->view('lpmpi/organization/unit_form', $this->unit_form_data('Edit Unit Organisasi', 'lpmpi/organization/unit/update/' . (int) $id, $unit));
    }

    public function update_unit($id)
    {
        $this->require_post_capability('organization.manage');
        $this->set_unit_rules();
        if (!$this->form_validation->run()) { $this->edit($id); return; }
        $result = $this->organization_service->update_unit((int) $id, $this->unit_input());
        $this->flash_redirect($result, 'lpmpi/organization');
    }

    public function toggle($id)
    {
        $this->require_post_capability('organization.manage');
        $this->flash_redirect($this->organization_service->toggle_unit((int) $id), 'lpmpi/organization');
    }

    public function assignment_create()
    {
        $this->require_capability('organization.assignment.manage');
        $this->load->view('lpmpi/organization/assignment_form', $this->assignment_form_data());
    }

    public function assignment_store()
    {
        $this->require_post_capability('organization.assignment.manage');
        $this->set_assignment_rules();
        if (!$this->form_validation->run()) { $this->assignment_create(); return; }
        $this->flash_redirect($this->organization_service->create_assignment($this->assignment_input()), 'lpmpi/organization');
    }

    public function assignment_end($id)
    {
        $this->require_post_capability('organization.assignment.manage');
        $until = $this->input->post('valid_until', TRUE);
        $this->flash_redirect($this->organization_service->end_assignment((int) $id, $until), 'lpmpi/organization');
    }

    public function capabilities()
    {
        $this->require_capability('organization.view');
        $data = $this->page_data('Kapabilitas Organisasi', 'Struktur Organisasi');
        $data['capabilities'] = $this->organization_service->capabilities();
        $data['role_capabilities'] = [];
        foreach (Organization_service::ROLES as $role) $data['role_capabilities'][$role] = $this->organization_service->role_codes($role);
        $data['can_manage_capabilities'] = $this->can('organization.capability.manage');
        $this->load->view('lpmpi/organization/capabilities', $data);
    }

    public function capabilities_update()
    {
        $this->require_post_capability('organization.capability.manage');
        $role = $this->input->post('role', TRUE);
        $ids = $this->input->post('capability_ids', TRUE);
        $this->flash_redirect($this->organization_service->update_capabilities($role, $ids), 'lpmpi/organization/capabilities');
    }

    private function can($capability) { return $this->organization_service->can((string) $this->session->userdata('role'), $capability); }
    private function require_capability($capability) { if (!$this->can($capability)) show_error('Akses ditolak.', 403, 'Forbidden'); }
    private function require_post_capability($capability) { if ($this->input->method(TRUE) !== 'POST') show_error('Method tidak diizinkan.', 405, 'Method Not Allowed'); $this->require_capability($capability); }
    private function flash_redirect($result, $url) { $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']); redirect($url); }
    private function page_data($title, $subtitle) { return ['title' => $title . ' - AMI', 'page_title' => $title, 'page_subtitle' => $subtitle, 'active_menu' => 'organization']; }
    private function unit_form_data($title, $action, $unit) { $data = $this->page_data($title, 'Struktur Organisasi / ' . $title); $data['action'] = $action; $data['unit'] = $unit; $data['units'] = $this->organization_service->units(); return $data; }
    private function assignment_form_data() { $data = $this->page_data('Tambah Penempatan', 'Struktur Organisasi / Penempatan'); $data['users'] = $this->organization_service->users(); $data['units'] = $this->organization_service->units(); return $data; }
    private function set_unit_rules() { $this->form_validation->set_rules('code', 'Kode Unit', 'required|max_length[64]'); $this->form_validation->set_rules('name', 'Nama Unit', 'required|max_length[200]'); $this->form_validation->set_rules('type', 'Tipe Unit', 'required|in_list[faculty,upps,study_program,institute,bureau,unit]'); }
    private function set_assignment_rules() { $this->form_validation->set_rules('user_id', 'Pengguna', 'required|integer'); $this->form_validation->set_rules('organization_unit_id', 'Unit', 'required|integer'); $this->form_validation->set_rules('position_code', 'Posisi', 'required|max_length[64]'); $this->form_validation->set_rules('valid_from', 'Mulai berlaku', 'required'); }
    private function unit_input() { return ['code' => $this->input->post('code', TRUE), 'name' => $this->input->post('name', TRUE), 'type' => $this->input->post('type', TRUE), 'parent_id' => $this->input->post('parent_id', TRUE)]; }
    private function assignment_input() { return ['user_id' => $this->input->post('user_id', TRUE), 'organization_unit_id' => $this->input->post('organization_unit_id', TRUE), 'position_code' => $this->input->post('position_code', TRUE), 'valid_from' => $this->input->post('valid_from', TRUE), 'valid_until' => $this->input->post('valid_until', TRUE), 'is_primary' => $this->input->post('is_primary', TRUE)]; }
}
