<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_auditee_workspace extends CI_Controller
{
    protected $service;

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
        $this->load->view('spmi_auditee_workspace/index', ['title' => 'Workspace SPMI', 'page_title' => 'Workspace SPMI', 'page_subtitle' => 'Beranda / Workspace SPMI', 'active_menu' => 'spmi_workspace', 'assignments' => $this->service->assignments($this->user_id())]);
    }

    public function assignment($id)
    {
        $workspace = $this->service->workspace((int) $id, $this->user_id());
        if (!$workspace) { show_error('Penugasan tidak ditemukan.', 404, 'Not Found'); return; }
        $workspace['title'] = 'Workspace SPMI'; $workspace['page_title'] = 'Workspace SPMI'; $workspace['page_subtitle'] = 'Beranda / Workspace SPMI / Penugasan'; $workspace['active_menu'] = 'spmi_workspace';
        $this->load->view('spmi_auditee_workspace/assignment', $workspace);
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
        header('Content-Type: ' . $file['mime']); header('Content-Length: ' . (string) filesize($file['path'])); header('Content-Disposition: attachment; filename="' . rawurlencode($file['name']) . '"'); header('Cache-Control: private, no-store'); header('Pragma: no-cache'); readfile($file['path']);
    }

    protected function user_id() { return (int) $this->session->userdata('user_id'); }
    protected function require_post() { if ($this->input->method(TRUE) !== 'POST') { show_error('Method tidak diizinkan.', 405, 'Method Not Allowed'); exit; } }
}
