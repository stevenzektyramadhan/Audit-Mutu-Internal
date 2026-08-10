<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_auditor_workspace extends CI_Controller
{
    protected $service;
    public function __construct() { parent::__construct(); $this->load->library('auth_guard'); $this->auth_guard->only(['auditor']); $this->load->helper(['form', 'url', 'app']); require_once APPPATH . 'services/Spmi_auditor_workspace_service.php'; $this->service = new Spmi_auditor_workspace_service(); }
    public function index() { $this->load->view('spmi_auditor_workspace/index', ['title' => 'Penilaian SPMI', 'page_title' => 'Penilaian SPMI', 'page_subtitle' => 'Work / Penilaian SPMI', 'active_menu' => 'spmi_assessment', 'assignments' => $this->service->assignments($this->user_id())]); }
    public function assignment($id) { $workspace = $this->service->workspace((int) $id, $this->user_id()); if (!$workspace) { show_error('Penugasan tidak ditemukan.', 404, 'Not Found'); return; } $workspace['title'] = 'Penilaian SPMI'; $workspace['page_title'] = 'Penilaian SPMI'; $workspace['page_subtitle'] = 'Work / Penilaian SPMI / Penugasan'; $workspace['active_menu'] = 'spmi_assessment'; $this->load->view('spmi_auditor_workspace/assignment', $workspace); }
    public function save($id) { $this->mutate($id, FALSE); }
    public function finalize($id) { $this->mutate($id, TRUE); }
    protected function mutate($id, $finalize) { $this->require_post(); $method = $finalize ? 'finalize' : 'save'; $result = $this->service->$method((int) $id, $this->user_id(), $this->post_int('version'), $this->post_int('source_submission_version'), $this->input->post('assessment', TRUE)); $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']); redirect('auditor/spmi/assignment/' . (int) $id); }
    public function return_for_revision($id) { $this->require_post(); $result = $this->service->return_for_revision((int) $id, $this->user_id(), (int) $this->input->post('submission_version', TRUE), $this->input->post('reason', TRUE)); $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']); redirect('auditor/spmi/assignment/' . (int) $id); }
    public function download_evidence($id) { $file = $this->service->download((int) $id, $this->user_id()); if (!$file) { show_error('Bukti tidak ditemukan.', 404, 'Not Found'); return; } header('Content-Type: ' . $file['mime']); header('Content-Length: ' . (string) filesize($file['path'])); header('Content-Disposition: attachment; filename="' . rawurlencode($file['name']) . '"'); header('Cache-Control: private, no-store, max-age=0'); header('Pragma: no-cache'); header('Expires: 0'); readfile($file['path']); }
    protected function user_id() { return (int) $this->session->userdata('user_id'); }
    protected function post_int($key) { $value = $this->input->post($key, TRUE); if (!is_scalar($value) || !ctype_digit((string) $value)) { show_error('Data tidak valid.', 400, 'Bad Request'); exit; } return (int) $value; }
    protected function require_post() { if ($this->input->method(TRUE) !== 'POST') { show_error('Method tidak diizinkan.', 405, 'Method Not Allowed'); exit; } }
}
