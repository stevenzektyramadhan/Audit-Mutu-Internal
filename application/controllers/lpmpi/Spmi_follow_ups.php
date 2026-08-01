<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_follow_ups extends Admin_Lpmpi_Controller
{
    protected $service;
    public function __construct() { parent::__construct(); $this->load->helper(['form', 'url']); $this->load->library('form_validation'); require_once APPPATH . 'services/Spmi_rtm_follow_ups_service.php'; $this->service = new Spmi_rtm_follow_ups_service(); }
    public function index() { $this->render('index', ['title' => 'Tindak Lanjut RTM', 'page_title' => 'Tindak Lanjut RTM', 'page_subtitle' => 'Beranda / Insights / Tindak Lanjut RTM', 'active_menu' => 'spmi_follow_ups', 'follow_ups' => $this->service->follow_ups()]); }
    public function create($decision_id) { $this->render('form', array_merge($this->form_data('Tambah Tindak Lanjut RTM', 'lpmpi/spmi-follow-ups/store/' . (int) $decision_id, NULL), $this->service->form_options(), ['decision_id' => (int) $decision_id])); }
    public function store($decision_id) { $this->require_post(); $this->rules(); if (!$this->form_validation->run()) { $this->create($decision_id); return; } $this->flash_redirect($this->service->create($decision_id, $this->post_data(), $this->session->userdata('user_id')), 'lpmpi/spmi-follow-ups'); }
    public function detail($id) { $follow_up = $this->service->follow_up($id); if (!$follow_up) { show_error('Tindak lanjut RTM tidak ditemukan.', 404, 'Not Found'); return; } $this->render('detail', ['title' => 'Detail Tindak Lanjut RTM', 'page_title' => 'Detail Tindak Lanjut RTM', 'page_subtitle' => 'Beranda / Tindak Lanjut RTM / Detail', 'active_menu' => 'spmi_follow_ups', 'follow_up' => $follow_up]); }
    public function edit($id) { $follow_up = $this->service->follow_up($id); if (!$follow_up) { show_error('Tindak lanjut RTM tidak ditemukan.', 404, 'Not Found'); return; } if ($follow_up->status !== 'open') { $this->frozen_redirect(); return; } $this->render('form', array_merge($this->form_data('Edit Tindak Lanjut RTM', 'lpmpi/spmi-follow-ups/update/' . (int) $id, $follow_up), $this->service->form_options(), ['decision_id' => (int) $follow_up->decision_id])); }
    public function update($id) { $this->require_post(); $this->rules(); if (!$this->form_validation->run()) { $this->edit($id); return; } $this->flash_redirect($this->service->update($id, $this->post_data()), 'lpmpi/spmi-follow-ups/detail/' . (int) $id); }
    public function transition($id) { $this->require_post(); $this->flash_redirect($this->service->transition($id, $this->input->post('status', TRUE), ['completion_note' => $this->input->post('completion_note', TRUE)], $this->session->userdata('user_id')), 'lpmpi/spmi-follow-ups/detail/' . (int) $id); }
    private function form_data($title, $action, $follow_up) { return ['title' => $title, 'page_title' => $title, 'page_subtitle' => 'Beranda / Insights / Tindak Lanjut RTM / Form', 'active_menu' => 'spmi_follow_ups', 'action' => $action, 'follow_up' => $follow_up]; }
    private function post_data() { return ['responsible_user_id' => $this->input->post('responsible_user_id', TRUE), 'due_date' => $this->input->post('due_date', TRUE), 'follow_up_note' => $this->input->post('follow_up_note', TRUE)]; }
    private function rules() { $this->form_validation->set_rules('responsible_user_id', 'Penanggung jawab', 'required|integer'); $this->form_validation->set_rules('due_date', 'Batas waktu', 'trim'); $this->form_validation->set_rules('follow_up_note', 'Catatan tindak lanjut', 'max_length[10000]'); }
    private function require_post() { if ($this->input->method(TRUE) !== 'POST') { show_error('Method tidak diizinkan.', 405, 'Method Not Allowed'); exit; } }
    private function flash_redirect($result, $uri) { $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']); redirect($uri); }
    private function frozen_redirect() { $this->session->set_flashdata('error', 'Tindak lanjut completed atau in_progress bersifat hanya-baca.'); redirect('lpmpi/spmi-follow-ups'); }
    private function render($view, $data) { $this->load->view('lpmpi/spmi_follow_ups/' . $view, $data); }
}
