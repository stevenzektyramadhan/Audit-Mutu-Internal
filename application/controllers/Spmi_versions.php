<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_versions extends MY_Controller
{
    public $session;
    public $input;
    public $form_validation;
    public $file_security;

    protected $workflow;

    public function __construct()
    {
        parent::__construct();
        $this->_require_capability(Authorization_policy::CAP_SPMI_VERSION_MANAGE);
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->library('file_security');
        $this->load->model('Organization_unit_model');
        $this->load->model('Spmi_standard_model');
        $this->load->helper(['url', 'form']);

        require_once APPPATH . 'services/Spmi_version_workflow_service.php';
        $this->workflow = new Spmi_version_workflow_service();

        if (!$this->workflow->schema_ready()) {
            show_error(
                'Versi dan master standar SPMI belum tersedia. Jalankan migration sampai M3-03 terlebih dahulu.',
                503,
                'Service Unavailable'
            );
            exit;
        }
    }

    public function index()
    {
        $units = $this->accessible_units();
        $selected_unit = NULL;
        $selected_id = (int) $this->input->get('unit_id', TRUE);

        if ($selected_id > 0) {
            $selected_unit = $this->unit_from_list($units, $selected_id);
            if (!$selected_unit) {
                show_error('Akses ke unit organisasi ditolak.', 403, 'Forbidden');
                return;
            }
        } elseif (!empty($units)) {
            $selected_unit = reset($units);
        }

        $this->load->view('lpmpi/spmi_versions/index', [
            'title' => 'Versi SPMI - AMI',
            'page_title' => 'Versi SPMI',
            'page_subtitle' => 'SPMI / Versi Dokumen',
            'active_menu' => 'spmi_versions',
            'units' => $units,
            'selected_unit' => $selected_unit,
            'versions' => $selected_unit
                ? $this->workflow->get_for_organization_unit((int) $selected_unit->id)
                : [],
            'status_labels' => $this->workflow->status_labels(),
        ]);
    }

    public function create()
    {
        $units = $this->accessible_units();
        if (empty($units)) {
            show_error(
                'Tidak ada unit organisasi aktif dalam scope Anda.',
                403,
                'Forbidden'
            );
            return;
        }

        $selected_id = (int) $this->input->get('unit_id', TRUE);
        if ($selected_id > 0 && !$this->unit_from_list($units, $selected_id)) {
            show_error('Akses ke unit organisasi ditolak.', 403, 'Forbidden');
            return;
        }

        $this->render_form(NULL, $units, $selected_id, 'spmi-versions/store');
    }

    public function store()
    {
        $this->require_post();
        $this->set_create_validation_rules();
        $unit_id = (int) $this->input->post('organization_unit_id');
        $this->_require_capability_in_organization_unit(
            Authorization_policy::CAP_SPMI_VERSION_MANAGE,
            $unit_id
        );

        if ($this->form_validation->run() === FALSE) {
            $this->render_form(
                NULL,
                $this->accessible_units(),
                $unit_id,
                'spmi-versions/store'
            );
            return;
        }

        $upload = $this->file_security->upload(
            'source_pdf',
            'spmi_source',
            'spmi_version',
            0,
            $this->_user_id()
        );
        if (!$upload['success']) {
            $this->session->set_flashdata('error', $upload['message']);
            $this->render_form(
                NULL,
                $this->accessible_units(),
                $unit_id,
                'spmi-versions/store'
            );
            return;
        }

        $result = $this->workflow->create_draft(
            $this->version_input(TRUE),
            (int) $upload['asset_id'],
            $this->_user_id()
        );
        if (!$result['success']) {
            $this->file_security->retire(
                'spmi_source',
                $upload['file_name'],
                'spmi_version',
                0,
                $this->_user_id(),
                'create_failed'
            );
            $this->session->set_flashdata('error', $result['message']);
            redirect('spmi-versions/create?unit_id=' . $unit_id);
            return;
        }

        $this->session->set_flashdata('success', $result['message']);
        redirect('spmi-versions/show/' . (int) $result['id']);
    }

    public function show($id)
    {
        $version = $this->authorized_version((int) $id);
        $this->load->view('lpmpi/spmi_versions/show', [
            'title' => 'Detail Versi SPMI - AMI',
            'page_title' => 'Detail Versi SPMI',
            'page_subtitle' => 'SPMI / Versi Dokumen / Detail',
            'active_menu' => 'spmi_versions',
            'version' => $version,
            'status_labels' => $this->workflow->status_labels(),
            'current_user_id' => $this->_user_id(),
            'standard_count' => $this->Spmi_standard_model->count_for_version(
                (int) $version->id
            ),
            'can_manage_standards' => $this->authorization_policy
                ->allowsInOrganizationUnit(
                    $this->_user_id(),
                    Authorization_policy::CAP_SPMI_STANDARD_MANAGE,
                    (int) $version->organization_unit_id
                ),
        ]);
    }

    public function edit($id)
    {
        $version = $this->authorized_version((int) $id);
        if ((string) $version->status !== 'draft') {
            show_error('Hanya versi berstatus draft yang dapat diubah.', 409, 'Conflict');
            return;
        }

        $this->render_form(
            $version,
            [$this->Organization_unit_model->find((int) $version->organization_unit_id)],
            (int) $version->organization_unit_id,
            'spmi-versions/update/' . (int) $version->id
        );
    }

    public function update($id)
    {
        $this->require_post();
        $version = $this->authorized_version((int) $id);
        $this->set_edit_validation_rules();
        if ($this->form_validation->run() === FALSE) {
            $this->render_form(
                $version,
                [$this->Organization_unit_model->find((int) $version->organization_unit_id)],
                (int) $version->organization_unit_id,
                'spmi-versions/update/' . (int) $version->id
            );
            return;
        }

        $upload = NULL;
        if ($this->has_upload('source_pdf')) {
            $upload = $this->file_security->upload(
                'source_pdf',
                'spmi_source',
                'spmi_version',
                (int) $version->id,
                $this->_user_id()
            );
            if (!$upload['success']) {
                $this->session->set_flashdata('error', $upload['message']);
                redirect('spmi-versions/edit/' . (int) $version->id);
                return;
            }
        }

        $result = $this->workflow->update_draft(
            (int) $version->id,
            $this->version_input(FALSE),
            $upload ? (int) $upload['asset_id'] : 0,
            $this->_user_id()
        );
        if (!$result['success'] && $upload) {
            $this->file_security->retire(
                'spmi_source',
                $upload['file_name'],
                'spmi_version',
                (int) $version->id,
                $this->_user_id(),
                'update_failed'
            );
        }
        if ($result['success'] && !empty($result['old_stored_name'])) {
            $this->file_security->retire(
                'spmi_source',
                $result['old_stored_name'],
                'spmi_version',
                (int) $version->id,
                $this->_user_id(),
                'replaced'
            );
        }

        $this->session->set_flashdata(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
        redirect(
            $result['success']
                ? 'spmi-versions/show/' . (int) $version->id
                : 'spmi-versions/edit/' . (int) $version->id
        );
    }

    public function submit_review($id)
    {
        $this->run_action((int) $id, 'submit_for_review');
    }

    public function approve($id)
    {
        $this->run_action((int) $id, 'approve');
    }

    public function activate($id)
    {
        $this->run_action((int) $id, 'activate');
    }

    public function retire($id)
    {
        $this->run_action((int) $id, 'retire');
    }

    public function clone_form($id)
    {
        $version = $this->authorized_version((int) $id);
        if (!in_array((string) $version->status, ['approved', 'active', 'retired'], TRUE)) {
            show_error('Versi dengan status ini tidak dapat disalin.', 409, 'Conflict');
            return;
        }

        $this->load->view('lpmpi/spmi_versions/clone', [
            'title' => 'Clone Versi SPMI - AMI',
            'page_title' => 'Clone Versi SPMI',
            'page_subtitle' => 'SPMI / Versi Dokumen / Clone Draft',
            'active_menu' => 'spmi_versions',
            'version' => $version,
            'action' => 'spmi-versions/clone-store/' . (int) $version->id,
        ]);
    }

    public function clone_store($id)
    {
        $this->require_post();
        $version = $this->authorized_version((int) $id);
        $this->set_clone_validation_rules();
        if ($this->form_validation->run() === FALSE) {
            $this->clone_form((int) $version->id);
            return;
        }

        $copy = $this->file_security->duplicate(
            'spmi_source',
            (string) $version->source_file_stored_name,
            'spmi_version',
            (int) $version->id,
            $this->_user_id()
        );
        if (!$copy['success']) {
            $this->session->set_flashdata('error', $copy['message']);
            redirect('spmi-versions/clone/' . (int) $version->id);
            return;
        }

        $result = $this->workflow->clone_to_draft(
            (int) $version->id,
            [
                'title' => $this->input->post('title', TRUE),
                'revision_number' => $this->input->post('revision_number', TRUE),
                'effective_date' => $this->input->post('effective_date', TRUE),
                'expires_at' => $this->input->post('expires_at', TRUE),
            ],
            (int) $copy['asset_id'],
            $this->_user_id()
        );
        if (!$result['success']) {
            $this->file_security->retire(
                'spmi_source',
                $copy['file_name'],
                'spmi_version',
                0,
                $this->_user_id(),
                'clone_failed'
            );
        }

        $this->session->set_flashdata(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
        redirect(
            $result['success']
                ? 'spmi-versions/show/' . (int) $result['id']
                : 'spmi-versions/clone/' . (int) $version->id
        );
    }

    public function download($id)
    {
        $version = $this->authorized_version((int) $id);
        if (!$this->file_security->download(
            'spmi_source',
            (string) $version->source_file_stored_name,
            'spmi_version',
            (int) $version->id,
            $this->_user_id()
        )) {
            show_error('File PDF tidak ditemukan atau gagal diverifikasi.', 404, 'Not Found');
        }
    }

    protected function run_action($id, $method)
    {
        $this->require_post();
        $version = $this->authorized_version((int) $id);
        $result = $this->workflow->{$method}(
            (int) $version->id,
            $this->_user_id()
        );
        $this->session->set_flashdata(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
        redirect('spmi-versions/show/' . (int) $version->id);
    }

    protected function authorized_version($id)
    {
        $version = $this->workflow->find((int) $id);
        if (!$version) {
            show_error('Versi SPMI tidak ditemukan.', 404, 'Not Found');
            exit;
        }

        $this->_require_capability_in_organization_unit(
            Authorization_policy::CAP_SPMI_VERSION_MANAGE,
            (int) $version->organization_unit_id
        );
        return $version;
    }

    protected function accessible_units()
    {
        $units = [];
        foreach ($this->Organization_unit_model->get_all() as $unit) {
            if ((int) $unit->active === 1
                && $this->authorization_policy->allowsInOrganizationUnit(
                    $this->_user_id(),
                    Authorization_policy::CAP_SPMI_VERSION_MANAGE,
                    (int) $unit->id
                )) {
                $units[] = $unit;
            }
        }
        return $units;
    }

    protected function unit_from_list(array $units, $id)
    {
        foreach ($units as $unit) {
            if ((int) $unit->id === (int) $id) {
                return $unit;
            }
        }
        return NULL;
    }

    protected function render_form($version, array $units, $selected_unit_id, $action)
    {
        $is_edit = $version !== NULL;
        $this->load->view('lpmpi/spmi_versions/form', [
            'title' => ($is_edit ? 'Edit' : 'Tambah') . ' Versi SPMI - AMI',
            'page_title' => ($is_edit ? 'Edit' : 'Tambah') . ' Versi SPMI',
            'page_subtitle' => 'SPMI / Versi Dokumen / ' . ($is_edit ? 'Edit Draft' : 'Draft Baru'),
            'active_menu' => 'spmi_versions',
            'version' => $version,
            'units' => $units,
            'selected_unit_id' => (int) $selected_unit_id,
            'action' => $action,
        ]);
    }

    protected function set_create_validation_rules()
    {
        $this->form_validation->set_rules(
            'organization_unit_id',
            'Unit Organisasi',
            'required|integer'
        );
        $this->set_edit_validation_rules();
    }

    protected function set_edit_validation_rules()
    {
        if ($this->input->post('document_code') !== NULL) {
            $this->form_validation->set_rules(
                'document_code',
                'Kode Dokumen',
                'required|max_length[64]'
            );
        }
        $this->form_validation->set_rules('title', 'Judul', 'required|max_length[255]');
        $this->form_validation->set_rules(
            'revision_number',
            'Nomor Revisi',
            'required|max_length[50]'
        );
        $this->form_validation->set_rules('effective_date', 'Tanggal Efektif', 'required');
        $this->form_validation->set_rules('expires_at', 'Tanggal Berakhir', 'max_length[10]');
    }

    protected function set_clone_validation_rules()
    {
        $this->set_edit_validation_rules();
    }

    protected function version_input($include_identity)
    {
        $data = [
            'title' => $this->input->post('title', TRUE),
            'revision_number' => $this->input->post('revision_number', TRUE),
            'effective_date' => $this->input->post('effective_date', TRUE),
            'expires_at' => $this->input->post('expires_at', TRUE),
        ];
        if ($include_identity) {
            $data['organization_unit_id'] = (int) $this->input->post('organization_unit_id');
            $data['document_code'] = $this->input->post('document_code', TRUE);
        }
        return $data;
    }

    protected function has_upload($field)
    {
        return isset($_FILES[$field])
            && is_array($_FILES[$field])
            && isset($_FILES[$field]['error'])
            && (int) $_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    protected function require_post()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            exit;
        }
    }
}
