<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_rtm extends Admin_Lpmpi_Controller
{
    protected $service;
    public function __construct() { parent::__construct(); $this->load->helper(['form', 'url']); $this->load->library('form_validation'); require_once APPPATH . 'services/Spmi_rtm_service.php'; $this->service = new Spmi_rtm_service(); }
    public function index() { $this->render('index', ['title' => 'RTM SPMI', 'page_title' => 'RTM SPMI', 'page_subtitle' => 'Beranda / Insights / RTM SPMI', 'active_menu' => 'spmi_rtm', 'meetings' => $this->service->meetings()]); }
    public function create() { $this->render('form', $this->form_data('Tambah RTM SPMI', 'lpmpi/spmi-rtm/store', NULL)); }
    public function store() { $this->require_post(); $this->rules(); if (!$this->form_validation->run()) { $this->create(); return; } $this->flash_redirect($this->service->create($this->post_data(), $this->session->userdata('user_id')), 'lpmpi/spmi-rtm'); }
    public function detail($id) { $data = $this->service->meeting($id); if (!$data) { show_error('RTM tidak ditemukan.', 404, 'Not Found'); return; } $this->render('detail', array_merge($data, ['title' => 'Detail RTM SPMI', 'page_title' => 'Detail RTM SPMI', 'page_subtitle' => 'Beranda / RTM SPMI / Detail', 'active_menu' => 'spmi_rtm'])); }
    public function edit($id) { $data = $this->service->meeting($id); if (!$data) { show_error('RTM tidak ditemukan.', 404, 'Not Found'); return; } if ($data['meeting']->status !== 'draft') { $this->frozen_redirect(); return; } $this->render('form', array_merge($this->form_data('Edit RTM SPMI', 'lpmpi/spmi-rtm/update/' . (int) $id, $data['meeting']), ['linked_reports' => $data['reports'], 'participants' => $data['participants'], 'decisions' => $data['decisions']])); }
    public function update($id) { $this->require_post(); $this->rules(); if (!$this->form_validation->run()) { $this->edit($id); return; } $this->flash_redirect($this->service->update($id, $this->post_data()), 'lpmpi/spmi-rtm/detail/' . (int) $id); }
    public function resolve($id) { $this->require_post(); $this->flash_redirect($this->service->resolve($id, $this->session->userdata('user_id')), 'lpmpi/spmi-rtm/detail/' . (int) $id); }
    public function print_report($id) { $data = $this->service->meeting($id); if (!$data) { show_error('RTM tidak ditemukan.', 404, 'Not Found'); return; } $this->load->view('lpmpi/spmi_rtm/print', $data); }
    private function render($view, $data) { $this->load->view('lpmpi/spmi_rtm/' . $view, $data); }
    private function form_data($title, $action, $meeting) { return array_merge(['title' => $title, 'page_title' => $title, 'page_subtitle' => 'Beranda / RTM SPMI / Form', 'active_menu' => 'spmi_rtm', 'action' => $action, 'meeting' => $meeting, 'linked_reports' => [], 'participants' => [], 'decisions' => []], $this->service->form_options()); }
    private function post_data() { return ['meeting_code' => $this->input->post('meeting_code', TRUE), 'meeting_title' => $this->input->post('meeting_title', TRUE), 'meeting_date' => $this->input->post('meeting_date', TRUE), 'location' => $this->input->post('location', TRUE), 'report_ids' => $this->input->post('report_ids', TRUE), 'participant_ids' => $this->input->post('participant_ids', TRUE), 'decisions' => $this->input->post('decisions', TRUE)]; }
    private function rules() { $this->form_validation->set_rules('meeting_code', 'Kode rapat', 'required|max_length[128]'); $this->form_validation->set_rules('meeting_title', 'Judul rapat', 'required|max_length[200]'); $this->form_validation->set_rules('meeting_date', 'Tanggal rapat', 'required'); $this->form_validation->set_rules('location', 'Lokasi', 'required|max_length[200]'); }
    private function require_post() { if ($this->input->method(TRUE) !== 'POST') { show_error('Method tidak diizinkan.', 405, 'Method Not Allowed'); exit; } }
    private function flash_redirect($result, $uri) { $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']); redirect($uri); }
    private function frozen_redirect() { $this->session->set_flashdata('error', 'RTM resolved bersifat hanya-baca.'); redirect('lpmpi/spmi-rtm'); }
}
