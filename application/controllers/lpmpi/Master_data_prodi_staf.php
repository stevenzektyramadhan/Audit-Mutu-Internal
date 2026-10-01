<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master_data_prodi_staf extends Admin_Lpmpi_Controller
{
    protected $organization_service;
    protected $profil_service;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['form', 'url']);
        $this->load->library('form_validation');
        $this->load->model('Profil_model');
        $this->load->model('Organization_model');
        require_once APPPATH . 'services/Organization_service.php';
        $this->organization_service = new Organization_service();
        require_once APPPATH . 'services/Profil_service.php';
        $this->profil_service = new Profil_service();
    }

    public function index()
    {
        if ($this->input->method(TRUE) !== 'GET') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            return;
        }

        $this->require_capability('organization.view');
        $prodi = $this->Profil_model->get_prodi_master_directory_data();
        $organization_units = $this->Organization_model->get_master_directory_units();
        $summary = $this->Organization_model->get_master_directory_summary();
        $non_prodi_staff_placements = $this->Organization_model->get_current_non_prodi_staff_placements();
        $non_prodi_staff_count = $this->Organization_model->count_current_non_prodi_staff_placements();
        $jenjang_counts = [];
        $prodi_active_staff_count = 0;
        $mapped_prodi_count = 0;
        $unmapped_prodi_count = 0;
        foreach ($prodi as $row) {
            $level = trim((string) ($row->jenjang ?? ''));
            if ($level !== '') {
                $jenjang_counts[$level] = isset($jenjang_counts[$level]) ? $jenjang_counts[$level] + 1 : 1;
            }
            $prodi_active_staff_count += (int) ($row->active_staff_count ?? 0);
            if (!empty($row->is_mapped_to_organization)) {
                $mapped_prodi_count++;
            } else {
                $unmapped_prodi_count++;
            }
        }

        $this->load->view('lpmpi/master_data_prodi_staf/index', [
            'title' => 'Master Data Organisasi & Staf - AMI',
            'page_title' => 'Master Data Organisasi & Staf',
            'page_subtitle' => 'Beranda / Management / Master Data Organisasi & Staf',
            'directory_subtitle' => 'Kelola Fakultas, Program Studi, Biro, Unit, Lembaga, dan penempatan staf dalam satu direktori organisasi.',
            'active_menu' => 'master_data_prodi_staf',
            'organization_units' => $organization_units,
            'organization_summary' => $summary,
            'summary' => $summary,
            'prodi' => $prodi,
            'prodi_directory' => $prodi,
            'mapped_prodi_count' => $mapped_prodi_count,
            'unmapped_prodi_count' => $unmapped_prodi_count,
            'non_prodi_staff_placements' => $non_prodi_staff_placements,
            'non_prodi_staff_count' => $non_prodi_staff_count,
            'prodi_active_staff_count' => $prodi_active_staff_count,
            'jenjang_counts' => $jenjang_counts,
            'total_prodi' => count($prodi),
            'total_levels' => count($jenjang_counts),
            'total_staff' => (int) $summary['active_staff'],
        ]);
    }

    public function unit_detail($id)
    {
        $this->require_capability('organization.view');
        $unit = $this->organization_service->find_unit((int) $id);
        if (!$unit || $unit->parent_id === NULL) show_error('Unit tidak ditemukan.', 404, 'Not Found');
        $data = $this->unit_page_data('Detail Unit Organisasi', NULL, $unit);
        $data['parent_unit'] = $this->organization_service->find_unit((int) $unit->parent_id);
        $data['assignments'] = $this->organization_service->assignments((int) $id);
        $this->load->view('lpmpi/master_data_prodi_staf/unit_detail', $data);
    }

    public function create()
    {
        if ($this->input->method(TRUE) !== 'GET') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            return;
        }

        $this->require_capability('organization.manage');
        $this->load->view('lpmpi/master_data_prodi_staf/create_form', $this->create_page_data());
    }

    public function prodi_store()
    {
        $this->require_post_capability('organization.manage');
        $this->prodi_rules(TRUE);
        if ($this->form_validation->run() === FALSE) {
            $this->load->view('lpmpi/master_data_prodi_staf/create_form', $this->create_page_data());
            return;
        }

        $result = $this->profil_service->create_prodi($this->prodi_input());
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('lpmpi/master-data-prodi-staf');
    }

    public function prodi_link($id)
    {
        $this->require_capability('organization.manage');
        $row = $this->Profil_model->find_prodi((int) $id);
        if (!$row) show_error('Program studi tidak ditemukan.', 404, 'Not Found');
        if (!empty($row->organization_unit_id)) {
            $this->session->set_flashdata('error', 'Program studi sudah terhubung ke struktur organisasi.');
            redirect('lpmpi/master-data-prodi-staf');
            return;
        }
        $this->load->view('lpmpi/master_data_prodi_staf/prodi_link_form', [
            'title' => 'Hubungkan Program Studi - AMI',
            'page_title' => 'Hubungkan Program Studi',
            'active_menu' => 'master_data_prodi_staf',
            'row' => $row,
            'faculties' => $this->Profil_model->active_faculties(),
            'action' => 'profil/prodi/link/' . (int) $id,
            'return_url' => 'lpmpi/master-data-prodi-staf',
        ]);
    }

    public function unit_create()
    {
        $this->require_capability('organization.manage');
        $this->load->view('lpmpi/master_data_prodi_staf/unit_form', $this->unit_page_data('Tambah Unit Organisasi', 'lpmpi/master-data-prodi-staf/unit/store', NULL));
    }

    public function unit_store()
    {
        $this->require_post_capability('organization.manage');
        $this->set_unit_rules();
        if (!$this->form_validation->run()) { $this->unit_create(); return; }
        $this->flash_redirect($this->organization_service->create_canonical_unit($this->unit_input()), 'lpmpi/master-data-prodi-staf');
    }

    public function unit_edit($id)
    {
        $this->require_capability('organization.manage');
        $unit = $this->organization_service->find_unit((int) $id);
        if (!$unit || $unit->parent_id === NULL) show_error('Unit tidak ditemukan.', 404, 'Not Found');
        $this->load->view('lpmpi/master_data_prodi_staf/unit_form', $this->unit_page_data('Edit Unit Organisasi', 'lpmpi/master-data-prodi-staf/unit/update/' . (int) $id, $unit));
    }

    public function unit_update($id)
    {
        $this->require_post_capability('organization.manage');
        $this->set_unit_rules();
        if (!$this->form_validation->run()) { $this->unit_edit($id); return; }
        $this->flash_redirect($this->organization_service->update_canonical_unit((int) $id, $this->unit_input()), 'lpmpi/master-data-prodi-staf');
    }

    public function unit_toggle($id)
    {
        $this->require_post_capability('organization.manage');
        $this->flash_redirect($this->organization_service->toggle_canonical_unit((int) $id), 'lpmpi/master-data-prodi-staf');
    }

    public function placement_create()
    {
        $this->require_capability('organization.assignment.manage');
        $this->load->view('lpmpi/master_data_prodi_staf/placement_form', $this->placement_page_data());
    }

    public function placement_store()
    {
        $this->require_post_capability('organization.assignment.manage');
        $this->set_assignment_rules();
        if (!$this->form_validation->run()) { $this->placement_create(); return; }
        $this->flash_redirect($this->organization_service->create_canonical_non_prodi_assignment($this->assignment_input()), 'lpmpi/master-data-prodi-staf');
    }

    public function placement_end($id)
    {
        $this->require_post_capability('organization.assignment.manage');
        $this->form_validation->set_rules('valid_until', 'Berakhir berlaku', 'required');
        if (!$this->form_validation->run()) { $this->flash_redirect(['success' => FALSE, 'message' => validation_errors(' ', ' ')], 'lpmpi/master-data-prodi-staf'); return; }
        $this->flash_redirect($this->organization_service->end_canonical_non_prodi_assignment((int) $id, $this->input->post('valid_until', TRUE)), 'lpmpi/master-data-prodi-staf');
    }

    private function can($capability) { return $this->organization_service->can((string) $this->session->userdata('role'), $capability); }
    private function require_capability($capability) { if (!$this->can($capability)) show_error('Akses ditolak.', 403, 'Forbidden'); }
    private function require_post_capability($capability) { if ($this->input->method(TRUE) !== 'POST') show_error('Method tidak diizinkan.', 405, 'Method Not Allowed'); $this->require_capability($capability); }
    private function flash_redirect($result, $url) { $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']); redirect($url); }
    private function create_page_data() { return ['title' => 'Tambah Master Data Organisasi & Staf - AMI', 'page_title' => 'Tambah Master Data Organisasi & Staf', 'page_subtitle' => 'Master Data Organisasi & Staf / Tambah Data', 'active_menu' => 'master_data_prodi_staf', 'create_url' => 'lpmpi/master-data-prodi-staf/create', 'units' => $this->organization_service->units(), 'faculties' => $this->Profil_model->active_faculties(), 'return_url' => 'lpmpi/master-data-prodi-staf', 'unit_store_action' => 'lpmpi/master-data-prodi-staf/unit/store', 'prodi_store_action' => 'lpmpi/master-data-prodi-staf/prodi/store']; }
    private function unit_page_data($title, $action, $unit) { return ['title' => $title . ' - AMI', 'page_title' => $title, 'page_subtitle' => 'Master Data Organisasi & Staf / ' . $title, 'active_menu' => 'master_data_prodi_staf', 'action' => $action, 'unit' => $unit, 'units' => $this->organization_service->units()]; }
    private function placement_page_data() { return ['title' => 'Tambah Penempatan Non-Prodi - AMI', 'page_title' => 'Tambah Penempatan Non-Prodi', 'page_subtitle' => 'Master Data Organisasi & Staf / Penempatan Non-Prodi', 'active_menu' => 'master_data_prodi_staf', 'action' => 'lpmpi/master-data-prodi-staf/placement/store', 'users' => $this->organization_service->users(), 'units' => $this->organization_service->units()]; }
    private function set_unit_rules() { $this->form_validation->set_rules('code', 'Kode Unit', 'required|max_length[64]'); $this->form_validation->set_rules('name', 'Nama Unit', 'required|max_length[200]'); $this->form_validation->set_rules('type', 'Tipe Unit', 'required|in_list[faculty,bureau,unit,institute]'); $this->form_validation->set_rules('parent_id', 'Parent Unit', 'required|integer'); }
    private function set_assignment_rules() { $this->form_validation->set_rules('user_id', 'Pengguna', 'required|integer'); $this->form_validation->set_rules('organization_unit_id', 'Unit', 'required|integer'); $this->form_validation->set_rules('position_code', 'Posisi', 'required|max_length[64]'); $this->form_validation->set_rules('valid_from', 'Mulai berlaku', 'required'); }
    private function prodi_rules($require_faculty) { $this->form_validation->set_rules('nama_prodi', 'Nama program studi', 'required|max_length[200]'); $this->form_validation->set_rules('kode_prodi', 'Kode prodi', 'required|max_length[20]'); $this->form_validation->set_rules('jenjang', 'Jenjang', 'required|max_length[20]'); $this->form_validation->set_rules('faculty_id', 'Fakultas', $require_faculty ? 'required|integer|greater_than[0]' : 'integer|greater_than[0]'); foreach (['status' => 50, 'akreditasi' => 50, 'rasio_dosen_mahasiswa' => 20] as $field => $length) { $this->form_validation->set_rules($field, ucwords(str_replace('_', ' ', $field)), 'max_length[' . $length . ']'); } $this->form_validation->set_rules('tanggal_sk_akreditasi', 'Tanggal SK akreditasi', 'callback_valid_optional_date'); }
    public function valid_optional_date($value) { if (trim((string) $value) === '') return TRUE; $date = DateTime::createFromFormat('!Y-m-d', (string) $value); if ($date && $date->format('Y-m-d') === $value) return TRUE; $this->form_validation->set_message('valid_optional_date', '{field} tidak valid.'); return FALSE; }
    private function unit_input() { return ['code' => $this->input->post('code', TRUE), 'name' => $this->input->post('name', TRUE), 'type' => $this->input->post('type', TRUE), 'parent_id' => $this->input->post('parent_id', TRUE)]; }
    private function prodi_input() { return ['kode_prodi' => $this->input->post('kode_prodi', TRUE), 'nama_prodi' => $this->input->post('nama_prodi', TRUE), 'status' => $this->input->post('status', TRUE), 'jenjang' => $this->input->post('jenjang', TRUE), 'akreditasi' => $this->input->post('akreditasi', TRUE), 'tanggal_sk_akreditasi' => $this->input->post('tanggal_sk_akreditasi', TRUE), 'rasio_dosen_mahasiswa' => $this->input->post('rasio_dosen_mahasiswa', TRUE), 'faculty_id' => $this->input->post('faculty_id', TRUE)]; }
    private function assignment_input() { return ['user_id' => $this->input->post('user_id', TRUE), 'organization_unit_id' => $this->input->post('organization_unit_id', TRUE), 'position_code' => $this->input->post('position_code', TRUE), 'valid_from' => $this->input->post('valid_from', TRUE), 'valid_until' => $this->input->post('valid_until', TRUE), 'is_primary' => $this->input->post('is_primary', TRUE)]; }
}
