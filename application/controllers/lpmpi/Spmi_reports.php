<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_reports extends Admin_Lpmpi_Controller
{
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['form', 'url']);
        require_once APPPATH . 'services/Spmi_reports_service.php';
        $this->service = new Spmi_reports_service();
    }

    public function index()
    {
        $this->load->view('lpmpi/spmi_reports/index', ['title' => 'Laporan SPMI', 'page_title' => 'Laporan SPMI', 'page_subtitle' => 'Beranda / Insights / Laporan SPMI', 'active_menu' => 'spmi_reports', 'reports' => $this->service->reports(), 'finalized_assessments' => $this->service->finalized_assessments()]);
    }

    public function create($assessment_id)
    {
        if ($this->input->method(TRUE) !== 'POST') { show_error('Method tidak diizinkan.', 405, 'Method Not Allowed'); return; }
        $result = $this->service->generate((int) $assessment_id, (int) $this->session->userdata('user_id'));
        $this->session->set_flashdata($result['success'] ? 'success' : 'error', $result['message']);
        redirect($result['success'] && !empty($result['report_id']) ? 'lpmpi/spmi-reports/detail/' . (int) $result['report_id'] : 'lpmpi/spmi-reports');
    }

    public function detail($id)
    {
        $data = $this->service->report((int) $id);
        if (!$data) { show_error('Laporan SPMI tidak ditemukan.', 404, 'Not Found'); return; }
        $this->load->view('lpmpi/spmi_reports/detail', ['title' => 'Detail Laporan SPMI', 'page_title' => 'Detail Laporan SPMI', 'page_subtitle' => 'Beranda / Insights / Laporan SPMI / Detail', 'active_menu' => 'spmi_reports', 'report' => $data['report'], 'items' => $data['items']]);
    }

    public function print_report($id)
    {
        $data = $this->service->report((int) $id);
        if (!$data) { show_error('Laporan SPMI tidak ditemukan.', 404, 'Not Found'); return; }
        $this->load->view('lpmpi/spmi_reports/print', ['title' => 'Cetak Laporan SPMI', 'report' => $data['report'], 'items' => $data['items']]);
    }

    public function export($id)
    {
        $data = $this->service->report((int) $id);
        if (!$data) { show_error('Laporan SPMI tidak ditemukan.', 404, 'Not Found'); return; }
        $autoload = FCPATH . 'vendor/autoload.php';
        if (!is_file($autoload)) { show_error('Library PhpSpreadsheet belum terpasang. Jalankan composer install terlebih dahulu.', 500, 'Export gagal'); return; }
        require_once $autoload;
        if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) { show_error('Library PhpSpreadsheet tidak dapat dimuat.', 500, 'Export gagal'); return; }
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getProperties()->setCreator('AMI')->setTitle('Laporan SPMI')->setSubject('Snapshot laporan SPMI');
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan SPMI');
        $headers = ['A' => 'No', 'B' => 'Kode Pertanyaan', 'C' => 'Pertanyaan', 'D' => 'Indikator', 'E' => 'Realisasi', 'F' => 'Skor', 'G' => 'Deskriptor', 'H' => 'Temuan', 'I' => 'Rekomendasi'];
        foreach ($headers as $column => $label) $this->set_text($sheet, $column . '1', $label);
        $row = 2;
        foreach ($data['items'] as $item) { $this->set_text($sheet, 'A' . $row, $item->display_order); $this->set_text($sheet, 'B' . $row, $item->question_code_snapshot); $this->set_text($sheet, 'C' . $row, $item->question_text_snapshot); $this->set_text($sheet, 'D' . $row, $item->indicator_code_snapshot . ' — ' . $item->indicator_title_snapshot); $this->set_text($sheet, 'E' . $row, $item->realization_snapshot); $sheet->setCellValue('F' . $row, (int) $item->score); $this->set_text($sheet, 'G' . $row, $item->descriptor_snapshot); $this->set_text($sheet, 'H' . $row, $item->finding_snapshot); $this->set_text($sheet, 'I' . $row, $item->recommendation_snapshot); $row++; }
        $sheet->getStyle('A1:I' . max(1, $row - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP)->setWrapText(TRUE);
        $sheet->getStyle('A1:I1')->getFont()->setBold(TRUE);
        $sheet->freezePane('A2');
        while (ob_get_level() > 0) @ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="laporan_spmi.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        $spreadsheet->disconnectWorksheets();
        exit;
    }

    private function set_text($sheet, $cell, $value)
    {
        $value = trim((string) $value);
        $formula_prefixes = ['=', '+', '-', '@'];
        if ($value !== '' && in_array($value[0], $formula_prefixes, TRUE)) $value = "'" . $value;
        $sheet->setCellValueExplicit($cell, $value === '' ? '-' : $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    }
}
