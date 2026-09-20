<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_ppepp_documents extends Admin_Lpmpi_Controller
{
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['form', 'url']);
        $this->config->load('spmi_ppepp', TRUE);
        require_once APPPATH . 'services/Spmi_ppepp_documents_service.php';
        $this->service = new Spmi_ppepp_documents_service();
    }

    public function index()
    {
        $stage = $this->input->get('stage', TRUE) ?: 'penetapan';
        $year = $this->input->get('year', TRUE) ?: date('Y');

        $this->render('index', $this->page_data([
            'title' => 'Dokumen PPEPP SPMI',
            'page_title' => 'Dokumen PPEPP SPMI',
            'page_subtitle' => 'Beranda / Manajemen SPMI / Dokumen PPEPP',
            'selected_stage' => $stage,
            'selected_year' => (int) $year,
            'documents' => $this->service->documents($stage, $year),
            'years' => $this->service->years(),
            'checklist' => $this->service->penetapan_core_counts($year),
        ]));
    }

    public function create()
    {
        $valid_stages = array_keys((array) $this->config->item('spmi_ppepp_stages', 'spmi_ppepp'));
        $stage = $this->input->get('stage', TRUE);
        $stage = in_array($stage, $valid_stages, TRUE) ? $stage : 'penetapan';
        $year  = (int) ($this->input->get('year', TRUE) ?: date('Y'));

        $defaults = (object) ['stage' => $stage, 'period_year' => $year];
        $this->render('form', $this->form_data('Tambah Dokumen PPEPP', 'lpmpi/spmi-ppepp-documents/store', $defaults));
    }

    public function store()
    {
        $this->require_post();
        $this->flash_redirect($this->service->create($this->input->post(NULL, TRUE), $this->file(), $this->session->userdata('user_id')), 'lpmpi/spmi-ppepp-documents');
    }

    public function edit($id)
    {
        $document = $this->service->document($id);
        if (!$document) {
            show_error('Dokumen PPEPP tidak ditemukan.', 404, 'Not Found');
            return;
        }

        $this->render('form', $this->form_data('Edit Dokumen PPEPP', 'lpmpi/spmi-ppepp-documents/update/' . (int) $id, $document));
    }

    public function update($id)
    {
        $this->require_post();
        $this->flash_redirect($this->service->update($id, $this->input->post(NULL, TRUE), $this->file(), $this->session->userdata('user_id')), 'lpmpi/spmi-ppepp-documents');
    }

    public function delete($id)
    {
        $this->require_post();
        $this->flash_redirect($this->service->delete($id), 'lpmpi/spmi-ppepp-documents');
    }

    public function download($id)
    {
        $download = $this->service->download($id);
        if (!$download) {
            show_error('Dokumen PPEPP tidak ditemukan.', 404, 'Not Found');
            return;
        }

        if ($download['backend'] === 'url' && !empty($download['url'])) {
            redirect($download['url']);
            return;
        }

        if ($download['backend'] !== 'local' || empty($download['path']) || !is_file($download['path'])) {
            show_error('Dokumen PPEPP tidak ditemukan.', 404, 'Not Found');
            return;
        }

        $name = str_replace(['"', "\r", "\n"], '', basename((string) $download['name']));
        if ($name === '') $name = 'dokumen-ppepp';

        header('Content-Type: ' . ($download['mime'] ?: 'application/octet-stream'));
        header('Content-Length: ' . (int) $download['size']);
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Cache-Control: private, no-store');
        readfile($download['path']);
        exit;
    }

    private function form_data($title, $action, $document)
    {
        return $this->page_data([
            'title' => $title,
            'page_title' => $title,
            'page_subtitle' => 'Beranda / Manajemen SPMI / Dokumen PPEPP / Form',
            'action' => $action,
            'document' => $document,
        ]);
    }

    private function page_data($data)
    {
        return array_merge([
            'active_menu' => 'spmi_ppepp_documents',
            'stages' => (array) $this->config->item('spmi_ppepp_stages', 'spmi_ppepp'),
            'categories' => (array) $this->config->item('spmi_ppepp_categories', 'spmi_ppepp'),
            'penetapan_core_categories' => (array) $this->config->item('spmi_ppepp_penetapan_core_categories', 'spmi_ppepp'),
        ], $data);
    }

    private function file()
    {
        return isset($_FILES['document_file']) ? $_FILES['document_file'] : NULL;
    }

    private function require_post()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            exit;
        }
    }

    private function flash_redirect($result, $uri)
    {
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect($uri);
    }

    private function render($view, $data)
    {
        $this->load->view('lpmpi/spmi_ppepp_documents/' . $view, $data);
    }
}
