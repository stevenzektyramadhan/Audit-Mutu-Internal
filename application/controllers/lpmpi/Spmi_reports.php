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
        $index_data = $this->service->index_data($this->input->get(NULL, TRUE));

        $this->load->view('lpmpi/spmi_reports/index', [
            'title' => 'Laporan SPMI',
            'page_title' => 'Laporan SPMI',
            'page_subtitle' => 'Beranda / Insights / Laporan SPMI',
            'active_menu' => 'spmi_reports',
            'index_data' => $index_data,
            'reports' => $index_data['reports'],
            'finalized_assessments' => $this->service->finalized_versions(),
        ]);
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
        $this->load->view('lpmpi/spmi_reports/detail', ['title' => 'Detail Laporan SPMI', 'page_title' => 'Detail Laporan SPMI', 'page_subtitle' => 'Beranda / Insights / Laporan SPMI / Detail', 'active_menu' => 'spmi_reports', 'report' => $data['report'], 'items' => $data['items'], 'standards' => $data['standards'], 'contributors' => $data['contributors']]);
    }

    public function print_report($id)
    {
        $data = $this->service->report((int) $id);
        if (!$data) { show_error('Laporan SPMI tidak ditemukan.', 404, 'Not Found'); return; }
        $this->load->view('lpmpi/spmi_reports/print', ['title' => 'Cetak Laporan SPMI', 'report' => $data['report'], 'items' => $data['items'], 'standards' => $data['standards'], 'contributors' => $data['contributors']]);
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
        $headers = ['A' => 'No', 'B' => 'Auditor penanggung jawab', 'C' => 'Email auditor', 'D' => 'Indikator', 'E' => 'Realisasi', 'F' => 'URL Bukti', 'G' => 'File Bukti', 'H' => 'MIME Bukti', 'I' => 'Ukuran Bukti', 'J' => 'SHA-256 Bukti', 'K' => 'Bukti Auditor', 'L' => 'Skor', 'M' => 'Deskriptor', 'N' => 'Jenis Temuan', 'O' => 'Temuan', 'P' => 'Rekomendasi', 'Q' => 'Rencana perbaikan', 'R' => 'Tanggal bukti'];
        foreach ($headers as $column => $label) $this->set_text($sheet, $column . '1', $label);
        $row = 2;
        foreach ($data['standards'] as $standard) { $this->set_text($sheet, 'A' . $row, $standard['source_standard_code_snapshot'] . ' — ' . $standard['source_standard_title_snapshot']); $sheet->mergeCells('A' . $row . ':R' . $row); $sheet->getStyle('A' . $row)->getFont()->setBold(TRUE); $row++; foreach ($standard['items'] as $item) { $this->set_text($sheet, 'A' . $row, $item->standard_item_display_order ?: $item->display_order); $this->set_text($sheet, 'B' . $row, isset($item->auditor_name_snapshot) ? $item->auditor_name_snapshot : $data['report']->auditor_name_snapshot); $this->set_text($sheet, 'C' . $row, isset($item->auditor_email_snapshot) ? $item->auditor_email_snapshot : NULL); $this->set_text($sheet, 'D' . $row, $item->indicator_code_snapshot . ' — ' . $item->indicator_title_snapshot); $this->set_text($sheet, 'E' . $row, $item->realization_snapshot); $this->set_text($sheet, 'F' . $row, isset($item->evidence_url_snapshot) ? $item->evidence_url_snapshot : NULL); $this->set_text($sheet, 'G' . $row, isset($item->evidence_file_original_name_snapshot) ? $item->evidence_file_original_name_snapshot : NULL); $this->set_text($sheet, 'H' . $row, isset($item->evidence_file_mime_type_snapshot) ? $item->evidence_file_mime_type_snapshot : NULL); $this->set_text($sheet, 'I' . $row, isset($item->evidence_file_size_bytes_snapshot) ? $item->evidence_file_size_bytes_snapshot : NULL); $this->set_text($sheet, 'J' . $row, isset($item->evidence_file_sha256_snapshot) ? $item->evidence_file_sha256_snapshot : NULL); $this->set_text($sheet, 'K' . $row, $this->auditor_evidence_text(isset($item->auditor_evidence_snapshot) ? $item->auditor_evidence_snapshot : NULL)); $sheet->setCellValue('L' . $row, (int) $item->score); $this->set_text($sheet, 'M' . $row, $item->descriptor_snapshot); $this->set_text($sheet, 'N' . $row, isset($item->finding_type_snapshot) ? $item->finding_type_snapshot : NULL); $this->set_text($sheet, 'O' . $row, $item->finding_snapshot); $this->set_text($sheet, 'P' . $row, $item->recommendation_snapshot); $this->set_text($sheet, 'Q' . $row, isset($item->improvement_plan_snapshot) ? $item->improvement_plan_snapshot : NULL); $this->set_text($sheet, 'R' . $row, isset($item->evidence_date_snapshot) ? $item->evidence_date_snapshot : NULL); $row++; } }
        $sheet->getStyle('A1:R' . max(1, $row - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP)->setWrapText(TRUE);
        $sheet->getStyle('A1:R1')->getFont()->setBold(TRUE);
        $sheet->freezePane('A2');
        if ($data['items']) {
            $radar_sheet = $spreadsheet->createSheet();
            $radar_sheet->setTitle('Data Radar');
            $this->set_text($radar_sheet, 'A1', 'Indikator');
            $this->set_text($radar_sheet, 'B1', 'Skor');
            $radar_row = 2;
            foreach ($data['items'] as $item) {
                $this->set_text($radar_sheet, 'A' . $radar_row, $item->indicator_code_snapshot);
                $radar_sheet->setCellValue('B' . $radar_row, (int) $item->score);
                $radar_row++;
            }
            $radar_last_row = $radar_row - 1;
            $radar_range = "'Data Radar'!\$A\$2:\$A\$" . $radar_last_row;
            $score_range = "'Data Radar'!\$B\$2:\$B\$" . $radar_last_row;
            $series = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries(
                \PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_RADARCHART,
                \PhpOffice\PhpSpreadsheet\Chart\DataSeries::GROUPING_STANDARD,
                [0],
                [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', "'Data Radar'!\$B\$1", NULL, 1)],
                [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', $radar_range, NULL, count($data['items']))],
                [new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('Number', $score_range, NULL, count($data['items']))]
            );
            $chart = new \PhpOffice\PhpSpreadsheet\Chart\Chart(
                'radar_capaian_indikator',
                new \PhpOffice\PhpSpreadsheet\Chart\Title('Radar Capaian Indikator'),
                new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_RIGHT, NULL, FALSE),
                new \PhpOffice\PhpSpreadsheet\Chart\PlotArea(NULL, [$series])
            );
            $chart->setTopLeftPosition('A' . ($row + 2));
            $chart->setBottomRightPosition('H' . ($row + 20));
            $sheet->addChart($chart);
            $radar_sheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
        }
        while (ob_get_level() > 0) @ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="laporan_spmi.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->setIncludeCharts(TRUE);
        $writer->save('php://output');
        $spreadsheet->disconnectWorksheets();
        exit;
    }

    public function export_word($id)
    {
        $data = $this->service->report((int) $id);
        if (!$data) { show_error('Laporan SPMI tidak ditemukan.', 404, 'Not Found'); return; }
        $html = $this->load->view('lpmpi/spmi_reports/word', ['report' => $data['report'], 'items' => $data['items'], 'standards' => $data['standards'], 'contributors' => $data['contributors']], TRUE);
        while (ob_get_level() > 0) @ob_end_clean();
        header('Content-Type: application/msword; charset=UTF-8');
        header('Content-Disposition: attachment; filename="laporan_spmi.doc"');
        header('Cache-Control: max-age=0');
        echo "\xEF\xBB\xBF" . $html;
        exit;
    }

    private function set_text($sheet, $cell, $value)
    {
        $value = trim((string) $value);
        $formula_prefixes = ['=', '+', '-', '@'];
        if ($value !== '' && in_array($value[0], $formula_prefixes, TRUE)) $value = "'" . $value;
        $sheet->setCellValueExplicit($cell, $value === '' ? '-' : $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    }

    private function auditor_evidence_text($snapshot)
    {
        $evidence = json_decode((string) $snapshot, TRUE);
        if (!is_array($evidence)) return '-';
        $lines = [];
        foreach ($evidence as $item) {
            if (!is_array($item)) continue;
            $lines[] = (string) (isset($item['original_name']) ? $item['original_name'] : '-') . ' / ' . (string) (isset($item['mime_type']) ? $item['mime_type'] : '-') . ' / ' . (string) (isset($item['size_bytes']) ? (int) $item['size_bytes'] : 0) . ' bytes / ' . (string) (isset($item['sha256']) ? $item['sha256'] : '-');
        }
        return $lines ? implode("\n", $lines) : '-';
    }
}
