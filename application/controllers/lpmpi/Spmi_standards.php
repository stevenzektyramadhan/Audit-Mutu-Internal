<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_standards extends Admin_Lpmpi_Controller
{
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['form', 'url', 'download', 'app']);
        require_once APPPATH . 'services/Spmi_standards_service.php';
        $this->service = new Spmi_standards_service();
    }

    public function index()
    {
        $this->render('index', ['title' => 'Standar SPMI', 'page_title' => 'Standar SPMI Berbasis Versi', 'page_subtitle' => 'Beranda / Standar SPMI', 'active_menu' => 'spmi_standards', 'versions' => $this->service->versions()]);
    }

    public function version_create() { $this->render('version_form', $this->form_data('Tambah Versi Standar SPMI', 'lpmpi/spmi-standards/version/store', NULL)); }
    public function version_store()
    {
        $this->require_post();
        $result = $this->service->create_version($this->input->post(NULL, TRUE), $this->_user_id());
        $this->flash_redirect($result, 'lpmpi/spmi-standards');
    }
    public function version_detail($id)
    {
        $version = $this->service->version($id);
        if (!$version) show_error('Versi tidak ditemukan.', 404, 'Not Found');
        $this->render('version_detail', ['title' => 'Detail Versi SPMI', 'page_title' => 'Versi Standar SPMI', 'page_subtitle' => 'Beranda / Standar SPMI / Detail', 'active_menu' => 'spmi_standards', 'version' => $version, 'standards' => $this->service->standards($id), 'mutable' => $this->service->is_mutable($version), 'transitions' => isset(Spmi_standards_service::TRANSITIONS[$version->status]) ? Spmi_standards_service::TRANSITIONS[$version->status] : []]);
    }
    public function version_edit($id)
    {
        $version = $this->service->version($id);
        if (!$version) show_error('Versi tidak ditemukan.', 404, 'Not Found');
        if (!$this->service->is_mutable($version)) { $this->readonly_error(); return; }
        $this->render('version_form', $this->form_data('Edit Versi Standar SPMI', 'lpmpi/spmi-standards/version/update/' . (int) $id, $version));
    }
    public function version_update($id)
    {
        $this->require_post();
        $result = $this->service->update_version($id, $this->input->post(NULL, TRUE));
        $this->flash_redirect($result, 'lpmpi/spmi-standards/version/detail/' . (int) $id);
    }
    public function version_transition($id)
    {
        $this->require_post();
        $result = $this->service->transition($id, (string) $this->input->post('status', TRUE));
        $this->flash_redirect($result, 'lpmpi/spmi-standards/version/detail/' . (int) $id);
    }
    public function source_upload($id)
    {
        $this->require_post();
        $version = $this->service->version($id);
        if (!$version || !$this->service->is_mutable($version)) { $this->flash_redirect(['success' => FALSE, 'message' => 'Versi ini bersifat hanya-baca.'], 'lpmpi/spmi-standards/version/detail/' . (int) $id); return; }
        $dir = private_storage_dir('spmi_source');
        if (!is_dir($dir) && !mkdir($dir, 0755, TRUE)) { $this->flash_redirect(['success' => FALSE, 'message' => 'Folder sumber tidak dapat dibuat.'], 'lpmpi/spmi-standards/version/detail/' . (int) $id); return; }
        $this->load->library('upload');
        $this->upload->initialize(['upload_path' => $dir, 'allowed_types' => 'pdf', 'max_size' => 5120, 'file_name' => 'spmi_' . (int) $id . '_' . bin2hex(random_bytes(8)), 'overwrite' => FALSE, 'remove_spaces' => TRUE]);
        if (!$this->upload->do_upload('source_pdf')) { $this->flash_redirect(['success' => FALSE, 'message' => strip_tags($this->upload->display_errors('', ''))], 'lpmpi/spmi-standards/version/detail/' . (int) $id); return; }
        $new_file = $this->upload->data('file_name');
        $result = $this->service->set_source($id, $new_file);
        if (!$result['success']) delete_private_file('spmi_source', $new_file);
        elseif (!empty($result['previous_source_file_path'])) delete_private_file('spmi_source', $result['previous_source_file_path']);
        $this->flash_redirect($result, 'lpmpi/spmi-standards/version/detail/' . (int) $id);
    }
    public function source_download($id)
    {
        $version = $this->service->version($id);
        $path = $version && $version->source_file_path ? private_storage_path('spmi_source', $version->source_file_path) : NULL;
        if (!$path) show_error('Dokumen sumber tidak ditemukan.', 404, 'File Not Found');
        force_download($path, NULL);
    }
    public function source_delete($id)
    {
        $this->require_post();
        $result = $this->service->clear_source($id);
        if ($result['success'] && !empty($result['previous_source_file_path'])) delete_private_file('spmi_source', $result['previous_source_file_path']);
        $this->flash_redirect($result, 'lpmpi/spmi-standards/version/detail/' . (int) $id);
    }
    public function standard_create($version_id)
    {
        $version = $this->service->version($version_id);
        if (!$version) { $this->session->set_flashdata('error', 'Versi tidak ditemukan.'); redirect('lpmpi/spmi-standards'); return; }
        if (!$this->service->is_mutable($version)) { $this->readonly_error(); return; }
        $this->render('standard_form', $this->standard_form_data('Tambah Standar SPMI', 'lpmpi/spmi-standards/standard/store/' . (int) $version_id, NULL, $version_id));
    }
    public function standard_store($version_id)
    {
        $this->require_post();
        $result = $this->service->create_standard($version_id, $this->input->post(NULL, TRUE));
        $this->flash_redirect($result, 'lpmpi/spmi-standards/version/detail/' . (int) $version_id);
    }
    public function standard_edit($id)
    {
        $standard = $this->service->standard($id);
        if (!$standard) show_error('Standar tidak ditemukan.', 404, 'Not Found');
        $version = $this->service->version($standard->version_id);
        if (!$this->service->is_mutable($version)) { $this->readonly_error(); return; }
        $this->render('standard_form', $this->standard_form_data('Edit Standar SPMI', 'lpmpi/spmi-standards/standard/update/' . (int) $id, $standard, $standard->version_id));
    }
    public function standard_update($id)
    {
        $this->require_post();
        $standard = $this->service->standard($id);
        $result = $this->service->update_standard($id, $this->input->post(NULL, TRUE));
        $this->flash_redirect($result, 'lpmpi/spmi-standards/version/detail/' . (int) ($standard ? $standard->version_id : 0));
    }
    private function render($view, $data) { $this->load->view('lpmpi/spmi_standards/' . $view, $data); }
    private function form_data($title, $action, $version) { return ['title' => $title, 'page_title' => 'Versi Standar SPMI', 'page_subtitle' => 'Beranda / Standar SPMI / Form', 'active_menu' => 'spmi_standards', 'action' => $action, 'version' => $version]; }
    private function standard_form_data($title, $action, $standard, $version_id) { return ['title' => $title, 'page_title' => 'Daftar Standar', 'page_subtitle' => 'Beranda / Standar SPMI / Form', 'active_menu' => 'spmi_standards', 'action' => $action, 'standard' => $standard, 'version_id' => $version_id]; }
    private function flash_redirect($result, $uri) { $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']); redirect($uri); }
    private function readonly_error() { $this->session->set_flashdata('error', 'Versi approved, active, dan retired bersifat hanya-baca.'); redirect('lpmpi/spmi-standards'); }
    private function require_post() { if ($this->input->method(TRUE) !== 'POST') { show_error('Method tidak diizinkan.', 405, 'Method Not Allowed'); exit; } }
}
