<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_auditor_workspace extends CI_Controller
{
    protected $service;
    protected $filter_statuses = ['submitted', 'resubmitted', 'returned_for_revision'];
    public function __construct() { parent::__construct(); $this->load->library('auth_guard'); $this->auth_guard->only(['auditor']); $this->load->helper(['form', 'url', 'app']); require_once APPPATH . 'services/Spmi_auditor_workspace_service.php'; $this->service = new Spmi_auditor_workspace_service(); }
    public function index()
    {
        $user_id = $this->user_id();
        $filters = $this->filters($this->filter_statuses);
        $attention_count = $this->service->attention_count($user_id);
        $this->load->view('spmi_auditor_workspace/index', ['title' => 'Penilaian SPMI', 'page_title' => 'Penilaian SPMI', 'page_subtitle' => 'Work / Penilaian SPMI', 'active_menu' => 'spmi_assessment', 'assignments' => $this->service->assignments($user_id, $filters), 'cycle_options' => $this->service->cycle_options($user_id), 'filters' => $filters, 'menu_badges' => ['spmi_assessment' => $attention_count, 'spmi_auditor_dashboard' => $attention_count]]);
    }
    public function assignment($id) { $user_id = $this->user_id(); $workspace = $this->service->workspace((int) $id, $user_id); if (!$workspace) { show_error('Penugasan tidak ditemukan.', 404, 'Not Found'); return; } $attention_count = $this->service->attention_count($user_id); $workspace['title'] = 'Penilaian SPMI'; $workspace['page_title'] = 'Penilaian SPMI'; $workspace['page_subtitle'] = 'Work / Penilaian SPMI / Penugasan'; $workspace['active_menu'] = 'spmi_assessment'; $workspace['menu_badges'] = ['spmi_assessment' => $attention_count, 'spmi_auditor_dashboard' => $attention_count]; $this->load->view('spmi_auditor_workspace/assignment', $workspace); }
    public function save($id) { $this->mutate($id, FALSE); }
    public function save_item($id) { if ($this->input->method(TRUE) !== 'POST') { $this->json(['success' => FALSE, 'message' => 'Method tidak diizinkan.'], 405); return; } $value = []; foreach (['score', 'finding_type', 'finding', 'recommendation', 'improvement_plan', 'evidence_date'] as $field) $value[$field] = $this->input->post($field, TRUE); $result = $this->service->save_item((int) $id, $this->user_id(), $this->json_post_int('version'), $this->json_post_int('source_submission_version'), $value); if ($result['success']) { $this->json(['success' => TRUE, 'message' => $result['message'], 'version' => $result['version']], 200); return; } $this->json($result, $result['message'] === Spmi_auditor_workspace_service::CONFLICT ? 409 : 422); }
    public function finalize($id) { $this->mutate($id, TRUE); }
    protected function mutate($id, $finalize) { $this->require_post(); $method = $finalize ? 'finalize' : 'save'; $result = $this->service->$method((int) $id, $this->user_id(), $this->post_int('version'), $this->post_int('source_submission_version'), $this->input->post('assessment', TRUE)); $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']); redirect('auditor/spmi/assignment/' . (int) $id); }
    public function return_for_revision($id) { $this->require_post(); $result = $this->service->return_for_revision((int) $id, $this->user_id(), (int) $this->input->post('submission_version', TRUE), $this->input->post('reason', TRUE)); $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']); redirect('auditor/spmi/assignment/' . (int) $id); }
    public function download_evidence($id) { $file = $this->service->download((int) $id, $this->user_id()); if (!$file) { show_error('Bukti tidak ditemukan.', 404, 'Not Found'); return; } header('Content-Type: ' . $file['mime']); header('Content-Length: ' . (string) filesize($file['path'])); header('Content-Disposition: attachment; filename="' . rawurlencode($file['name']) . '"'); header('Cache-Control: private, no-store, max-age=0'); header('Pragma: no-cache'); header('Expires: 0'); readfile($file['path']); }
    public function upload_auditor_evidence($assessment_item_id) { $this->require_post(); $result = $this->service->upload_auditor_evidence((int) $assessment_item_id, $this->user_id(), $this->post_int('version'), $this->post_int('source_submission_version'), isset($_FILES['evidence']) ? $_FILES['evidence'] : NULL); $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']); redirect('auditor/spmi/assignment/' . $this->service->assignment_id_for_assessment_item((int) $assessment_item_id, $this->user_id())); }
    public function delete_auditor_evidence($id) { $this->require_post(); $assignment_id = $this->service->assignment_id_for_auditor_evidence((int) $id, $this->user_id()); $result = $this->service->delete_auditor_evidence((int) $id, $this->user_id(), $this->post_int('version'), $this->post_int('source_submission_version')); $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']); redirect('auditor/spmi/assignment/' . $assignment_id); }
    public function download_auditor_evidence($id) { $file = $this->service->download_auditor_evidence((int) $id, $this->user_id()); if (!$file) { show_error('Bukti auditor tidak ditemukan.', 404, 'Not Found'); return; } header('Content-Type: ' . $file['mime']); header('Content-Length: ' . (string) filesize($file['path'])); header('Content-Disposition: attachment; filename="' . rawurlencode($file['name']) . '"'); header('Cache-Control: private, no-store, max-age=0'); header('Pragma: no-cache'); header('Expires: 0'); readfile($file['path']); }
    protected function user_id() { return (int) $this->session->userdata('user_id'); }
    protected function post_int($key) { $value = $this->input->post($key, TRUE); if (!is_scalar($value) || !ctype_digit((string) $value)) { show_error('Data tidak valid.', 400, 'Bad Request'); exit; } return (int) $value; }
    protected function json_post_int($key) { $value = $this->input->post($key, TRUE); if (!is_scalar($value) || !ctype_digit((string) $value)) { $this->json(['success' => FALSE, 'message' => 'Data tidak valid.'], 422); exit; } return (int) $value; }
    protected function json($payload, $status) { $payload['csrf'] = ['name' => $this->security->get_csrf_token_name(), 'hash' => $this->security->get_csrf_hash()]; $this->output->set_status_header($status)->set_content_type('application/json', 'utf-8')->set_output(json_encode($payload)); }
    protected function filters($allowed_statuses)
    {
        $cycle_id = $this->input->get('cycle_id', TRUE);
        $status = $this->input->get('status', TRUE);
        $cycle_id = is_scalar($cycle_id) && ctype_digit((string) $cycle_id) ? (int) $cycle_id : 0;
        $status = is_scalar($status) ? trim((string) $status) : '';
        return ['cycle_id' => $cycle_id, 'status' => in_array($status, $allowed_statuses, TRUE) ? $status : ''];
    }
    protected function require_post() { if ($this->input->method(TRUE) !== 'POST') { show_error('Method tidak diizinkan.', 405, 'Method Not Allowed'); exit; } }
}
