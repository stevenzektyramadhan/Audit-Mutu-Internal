<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_auditee_workspace extends CI_Controller
{
    protected $service;
    protected $filter_statuses = ['draft', 'submitted', 'returned_for_revision', 'resubmitted'];

    public function __construct()
    {
        parent::__construct();
        $this->load->library('auth_guard');
        $this->auth_guard->only(['auditee']);
        $this->load->helper(['form', 'url', 'app']);
        require_once APPPATH . 'services/Spmi_auditee_workspace_service.php';
        $this->service = new Spmi_auditee_workspace_service();
    }

    public function index()
    {
        $user_id = $this->user_id();
        $filters = $this->filters($this->filter_statuses);
        $attention_count = $this->service->attention_count($user_id);
        $this->load->view('spmi_auditee_workspace/index', [
            'title' => 'Workspace SPMI',
            'page_title' => 'Workspace SPMI',
            'page_subtitle' => 'Beranda / Workspace SPMI',
            'active_menu' => 'spmi_workspace',
            'assignments' => $this->service->assignments($user_id, $filters),
            'cycle_options' => $this->service->cycle_options($user_id),
            'filters' => $filters,
            'menu_badges' => ['spmi_workspace' => $attention_count, 'spmi_auditee_dashboard' => $attention_count],
        ]);
    }

    public function assignment($id)
    {
        $user_id = $this->user_id();
        $workspace = $this->service->workspace((int) $id, $user_id);
        if (!$workspace) { show_error('Penugasan tidak ditemukan.', 404, 'Not Found'); return; }
        $attention_count = $this->service->attention_count($user_id);
        $workspace['title'] = 'Workspace SPMI'; $workspace['page_title'] = 'Workspace SPMI'; $workspace['page_subtitle'] = 'Beranda / Workspace SPMI / Penugasan'; $workspace['active_menu'] = 'spmi_workspace';
        $workspace['menu_badges'] = ['spmi_workspace' => $attention_count, 'spmi_auditee_dashboard' => $attention_count];
        $this->load->view('spmi_auditee_workspace/assignment', $workspace);
    }

    public function confirm($id)
    {
        $user_id = $this->user_id();
        $result = $this->service->confirm((int) $id, $user_id);
        if ($result['status'] === 'not_found') { show_error('Penugasan tidak ditemukan.', 404, 'Not Found'); return; }
        if ($result['status'] === 'forbidden') { show_error('Penugasan belum dapat dikonfirmasi.', 403, 'Forbidden'); return; }

        $workspace = $result['data'];
        $attention_count = $this->service->attention_count($user_id);
        $workspace['title'] = 'Konfirmasi Submission SPMI'; $workspace['page_title'] = 'Konfirmasi Submission SPMI'; $workspace['page_subtitle'] = 'Beranda / Workspace SPMI / Konfirmasi'; $workspace['active_menu'] = 'spmi_workspace';
        $workspace['menu_badges'] = ['spmi_workspace' => $attention_count, 'spmi_auditee_dashboard' => $attention_count];
        $workspace['preview'] = [
            'version' => $this->preview_scalar('version'),
            'realization' => $this->preview_map('realization'),
            'evidence_url' => $this->preview_map('evidence_url'),
        ];
        $this->load->view('spmi_auditee_workspace/confirm', $workspace);
    }

    public function final_result($id)
    {
        $user_id = $this->user_id();
        $result = $this->service->final_result((int) $id, $user_id);
        if (!$result) { show_error('Laporan SPMI tidak ditemukan.', 404, 'Not Found'); return; }
        $attention_count = $this->service->attention_count($user_id);
        $result['title'] = 'Hasil Akhir SPMI'; $result['page_title'] = 'Hasil Akhir SPMI'; $result['page_subtitle'] = 'Beranda / Workspace SPMI / Hasil Akhir'; $result['active_menu'] = 'spmi_workspace';
        $result['menu_badges'] = ['spmi_workspace' => $attention_count, 'spmi_auditee_dashboard' => $attention_count];
        $this->load->view('spmi_auditee_workspace/final_result', $result);
    }

    public function save($id) { $this->mutate($id, FALSE); }
    public function submit($id) { $this->mutate($id, TRUE); }
    public function resubmit($id) { $this->mutate($id, TRUE, TRUE); }

    protected function mutate($id, $submit, $resubmit = FALSE)
    {
        $this->require_post();
        $method = $resubmit ? 'resubmit' : ($submit ? 'submit' : 'save');
        $result = $this->service->$method((int) $id, $this->user_id(), (int) $this->input->post('version', TRUE), $this->input->post('realization', TRUE), $this->input->post('evidence_url', TRUE));
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect('auditee/spmi/assignment/' . (int) $id);
    }

    public function upload_evidence($item_id)
    {
        $this->require_post(); $version = (int) $this->input->post('version', TRUE); $result = $this->service->upload((int) $item_id, $this->user_id(), $version, isset($_FILES['evidence']) ? $_FILES['evidence'] : NULL);
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']); $assignment_id = $this->service->assignment_id_for_item((int) $item_id, $this->user_id()); redirect('auditee/spmi/assignment/' . $assignment_id);
    }

    public function delete_evidence($id)
    {
        $this->require_post(); $version = (int) $this->input->post('version', TRUE); $assignment_id = $this->service->assignment_id_for_evidence((int) $id, $this->user_id()); $result = $this->service->delete((int) $id, $this->user_id(), $version); $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']); redirect('auditee/spmi/assignment/' . $assignment_id);
    }

    public function download_evidence($id)
    {
        $file = $this->service->download((int) $id, $this->user_id());
        if (!$file) { show_error('Bukti tidak ditemukan.', 404, 'Not Found'); return; }
        $this->output_evidence_file($file, 'Bukti tidak ditemukan.');
    }

    protected function user_id() { return (int) $this->session->userdata('user_id'); }
    protected function preview_scalar($key) { $value = $this->input->get($key, TRUE); return is_scalar($value) ? trim((string) $value) : ''; }
    protected function preview_map($key)
    {
        $values = $this->input->get($key, TRUE);
        if (!is_array($values)) return [];
        $clean = [];
        foreach ($values as $item_id => $value) {
            if (!is_scalar($item_id) || !ctype_digit((string) $item_id) || !is_scalar($value)) continue;
            $clean[(int) $item_id] = trim((string) $value);
        }
        return $clean;
    }
    protected function filters($allowed_statuses)
    {
        $cycle_id = $this->input->get('cycle_id', TRUE);
        $status = $this->input->get('status', TRUE);
        $cycle_id = is_scalar($cycle_id) && ctype_digit((string) $cycle_id) ? (int) $cycle_id : 0;
        $status = is_scalar($status) ? trim((string) $status) : '';
        return ['cycle_id' => $cycle_id, 'status' => in_array($status, $allowed_statuses, TRUE) ? $status : ''];
    }
    protected function require_post() { if ($this->input->method(TRUE) !== 'POST') { show_error('Method tidak diizinkan.', 405, 'Method Not Allowed'); exit; } }
    protected function output_evidence_file($file, $not_found_message)
    {
        if ($file['backend'] === 'google_drive') {
            header('Content-Type: ' . $file['mime']);
            header('Content-Length: ' . (string) $file['size']);
            header('Content-Disposition: attachment; filename="' . rawurlencode($file['name']) . '"');
            header('Cache-Control: private, no-store');
            header('Pragma: no-cache');
            $result = $this->service->stream_drive_download($file['drive_file_id'], fopen('php://output', 'wb'));
            if (!$result['success']) { show_error($not_found_message, 404, 'Not Found'); return; }
            return;
        }
        header('Content-Type: ' . $file['mime']);
        header('Content-Length: ' . (string) filesize($file['path']));
        header('Content-Disposition: attachment; filename="' . rawurlencode($file['name']) . '"');
        header('Cache-Control: private, no-store');
        header('Pragma: no-cache');
        readfile($file['path']);
    }
}
