<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_standards extends MY_Controller
{
    public $session;
    public $input;
    public $form_validation;

    protected $standards;

    public function __construct()
    {
        parent::__construct();
        $this->_require_capability(
            Authorization_policy::CAP_SPMI_STANDARD_MANAGE
        );
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->helper(['url', 'form']);

        require_once APPPATH . 'services/Spmi_standard_service.php';
        $this->standards = new Spmi_standard_service();

        if (!$this->standards->schema_ready()) {
            show_error(
                'Master standar SPMI belum tersedia. Jalankan migration M3-03 terlebih dahulu.',
                503,
                'Service Unavailable'
            );
            exit;
        }
    }

    public function index($spmi_version_id)
    {
        $version = $this->authorized_version((int) $spmi_version_id);
        $rows = $this->standards->get_for_version((int) $version->id);

        $this->load->view('lpmpi/spmi_standards/index', [
            'title' => 'Master Standar SPMI - AMI',
            'page_title' => 'Master Standar SPMI',
            'page_subtitle' => 'SPMI / Versi Dokumen / Standar',
            'active_menu' => 'spmi_versions',
            'version' => $version,
            'standards' => $rows,
            'group_counts' => $this->standards->group_counts(
                (int) $version->id
            ),
            'group_labels' => $this->standards->group_labels(),
            'type_labels' => $this->standards->type_labels(),
            'is_draft' => (string) $version->status === 'draft',
        ]);
    }

    public function seed($spmi_version_id)
    {
        $this->require_post();
        $version = $this->authorized_version((int) $spmi_version_id);
        $this->require_draft($version);

        $result = $this->standards->seed_defaults(
            (int) $version->id,
            $this->_user_id()
        );
        $this->flash_result($result);
        redirect('spmi-versions/' . (int) $version->id . '/standards');
    }

    public function create($spmi_version_id)
    {
        $version = $this->authorized_version((int) $spmi_version_id);
        $this->require_draft($version);
        $this->render_form(
            $version,
            NULL,
            'spmi-versions/' . (int) $version->id . '/standards/store',
            $this->standards->count_for_version((int) $version->id) + 1
        );
    }

    public function store($spmi_version_id)
    {
        $this->require_post();
        $version = $this->authorized_version((int) $spmi_version_id);
        $this->require_draft($version);
        $this->set_validation_rules();

        if ($this->form_validation->run() === FALSE) {
            $this->render_form(
                $version,
                NULL,
                'spmi-versions/' . (int) $version->id . '/standards/store',
                $this->standards->count_for_version((int) $version->id) + 1
            );
            return;
        }

        $result = $this->standards->create(
            (int) $version->id,
            $this->standard_input(),
            $this->_user_id()
        );
        $this->flash_result($result);
        redirect('spmi-versions/' . (int) $version->id . '/standards');
    }

    public function edit($spmi_version_id, $standard_id)
    {
        $context = $this->authorized_standard(
            (int) $spmi_version_id,
            (int) $standard_id
        );
        $this->require_draft($context['version']);
        $this->render_form(
            $context['version'],
            $context['standard'],
            'spmi-versions/' . (int) $context['version']->id
                . '/standards/update/' . (int) $context['standard']->id,
            (int) $context['standard']->sort_order
        );
    }

    public function update($spmi_version_id, $standard_id)
    {
        $this->require_post();
        $context = $this->authorized_standard(
            (int) $spmi_version_id,
            (int) $standard_id
        );
        $this->require_draft($context['version']);
        $this->set_validation_rules();

        if ($this->form_validation->run() === FALSE) {
            $this->render_form(
                $context['version'],
                $context['standard'],
                'spmi-versions/' . (int) $context['version']->id
                    . '/standards/update/' . (int) $context['standard']->id,
                (int) $context['standard']->sort_order
            );
            return;
        }

        $result = $this->standards->update(
            (int) $context['standard']->id,
            $this->standard_input(),
            $this->_user_id()
        );
        $this->flash_result($result);
        redirect(
            'spmi-versions/' . (int) $context['version']->id . '/standards'
        );
    }

    public function toggle_active($spmi_version_id, $standard_id)
    {
        $this->require_post();
        $context = $this->authorized_standard(
            (int) $spmi_version_id,
            (int) $standard_id
        );
        $this->require_draft($context['version']);

        $result = $this->standards->toggle_active(
            (int) $context['standard']->id,
            $this->_user_id()
        );
        $this->flash_result($result);
        redirect(
            'spmi-versions/' . (int) $context['version']->id . '/standards'
        );
    }

    public function reorder($spmi_version_id)
    {
        $this->require_post();
        $version = $this->authorized_version((int) $spmi_version_id);
        $this->require_draft($version);
        $orders = $this->input->post('orders');

        $result = $this->standards->reorder(
            (int) $version->id,
            is_array($orders) ? $orders : [],
            $this->_user_id()
        );
        $this->flash_result($result);
        redirect('spmi-versions/' . (int) $version->id . '/standards');
    }

    protected function authorized_version($id)
    {
        $version = $this->standards->find_version((int) $id);
        if (!$version) {
            show_error('Versi SPMI tidak ditemukan.', 404, 'Not Found');
            exit;
        }
        $this->_require_capability_in_organization_unit(
            Authorization_policy::CAP_SPMI_STANDARD_MANAGE,
            (int) $version->organization_unit_id
        );
        return $version;
    }

    protected function authorized_standard($spmi_version_id, $standard_id)
    {
        $version = $this->authorized_version((int) $spmi_version_id);
        $standard = $this->standards->find((int) $standard_id);
        if (!$standard
            || (int) $standard->spmi_version_id !== (int) $version->id) {
            show_error('Standar SPMI tidak ditemukan pada versi ini.', 404, 'Not Found');
            exit;
        }
        return ['version' => $version, 'standard' => $standard];
    }

    protected function require_draft($version)
    {
        if ((string) $version->status !== 'draft') {
            show_error(
                'Master standar hanya dapat diubah pada versi berstatus draft.',
                409,
                'Conflict'
            );
            exit;
        }
    }

    protected function render_form(
        $version,
        $standard,
        $action,
        $default_sort_order
    ) {
        $is_edit = $standard !== NULL;
        $this->load->view('lpmpi/spmi_standards/form', [
            'title' => ($is_edit ? 'Edit' : 'Tambah') . ' Standar SPMI - AMI',
            'page_title' => ($is_edit ? 'Edit' : 'Tambah') . ' Standar SPMI',
            'page_subtitle' => 'SPMI / Versi Dokumen / Standar / '
                . ($is_edit ? 'Edit' : 'Tambah'),
            'active_menu' => 'spmi_versions',
            'version' => $version,
            'standard' => $standard,
            'action' => $action,
            'default_sort_order' => (int) $default_sort_order,
            'group_labels' => $this->standards->group_labels(),
            'type_labels' => $this->standards->type_labels(),
        ]);
    }

    protected function set_validation_rules()
    {
        $this->form_validation->set_rules(
            'code',
            'Kode Standar',
            'required|max_length[64]'
        );
        $this->form_validation->set_rules(
            'name',
            'Nama Standar',
            'required|max_length[255]'
        );
        $this->form_validation->set_rules(
            'group_type',
            'Kelompok',
            'required|in_list[education,research,community_service,internal]'
        );
        $this->form_validation->set_rules(
            'standard_type',
            'Jenis Standar',
            'required|in_list[sn_dikti,internal]'
        );
        $this->form_validation->set_rules(
            'sort_order',
            'Urutan',
            'required|integer|greater_than[0]'
        );
    }

    protected function standard_input()
    {
        return [
            'code' => $this->input->post('code', TRUE),
            'name' => $this->input->post('name', TRUE),
            'group_type' => $this->input->post('group_type', TRUE),
            'standard_type' => $this->input->post('standard_type', TRUE),
            'rationale' => $this->input->post('rationale', TRUE),
            'definitions' => $this->input->post('definitions', TRUE),
            'sort_order' => $this->input->post('sort_order'),
        ];
    }

    protected function flash_result(array $result)
    {
        $this->session->set_flashdata(
            !empty($result['success']) ? 'success' : 'error',
            isset($result['message']) ? $result['message'] : 'Operasi gagal.'
        );
    }

    protected function require_post()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            exit;
        }
    }
}
