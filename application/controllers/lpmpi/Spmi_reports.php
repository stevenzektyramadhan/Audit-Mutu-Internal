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
        $radar_cycle = trim((string) $this->input->get('radar_cycle', TRUE));
        $selector = $this->service->version_report_selector($this->input->get('report_cycle_id', TRUE), $this->input->get('report_id', TRUE));

        $this->load->view('lpmpi/spmi_reports/index', [
            'title' => 'Laporan SPMI',
            'page_title' => 'Laporan SPMI',
            'page_subtitle' => 'Beranda / Insights / Laporan SPMI',
            'active_menu' => 'spmi_reports',
            'reports' => $this->service->reports(),
            'finalized_assessments' => $this->service->finalized_versions(),
            'radar_cycles' => $this->service->report_cycles(),
            'radar_selected_cycle' => $radar_cycle,
            'radar_recap' => $this->service->score_recap($radar_cycle),
            'version_report_cycles' => $selector['version_report_cycles'],
            'selected_report_cycle_id' => $selector['selected_report_cycle_id'],
            'version_reports' => $selector['version_reports'],
            'selected_version_report' => $selector['selected_version_report'],
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
        $this->load->view('lpmpi/spmi_reports/detail', ['title' => 'Detail Laporan SPMI', 'page_title' => 'Detail Laporan SPMI', 'page_subtitle' => 'Beranda / Insights / Laporan SPMI / Detail', 'active_menu' => 'spmi_reports', 'report' => $data['report'], 'items' => $data['items'], 'standards' => $data['standards']]);
    }

    public function print_report($id)
    {
        $data = $this->service->report((int) $id);
        if (!$data) { show_error('Laporan SPMI tidak ditemukan.', 404, 'Not Found'); return; }
        $this->load->view('lpmpi/spmi_reports/print', ['title' => 'Cetak Laporan SPMI', 'report' => $data['report'], 'items' => $data['items'], 'standards' => $data['standards']]);
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
        $headers = ['A' => 'No', 'B' => 'Indikator', 'C' => 'Realisasi', 'D' => 'URL Bukti', 'E' => 'File Bukti', 'F' => 'MIME Bukti', 'G' => 'Ukuran Bukti', 'H' => 'SHA-256 Bukti', 'I' => 'Bukti Auditor', 'J' => 'Skor', 'K' => 'Deskriptor', 'L' => 'Jenis Temuan', 'M' => 'Temuan', 'N' => 'Rekomendasi', 'O' => 'Rencana perbaikan', 'P' => 'Tanggal bukti'];
        foreach ($headers as $column => $label) $this->set_text($sheet, $column . '1', $label);
        $row = 2;
        foreach ($data['standards'] as $standard) { $this->set_text($sheet, 'A' . $row, $standard['source_standard_code_snapshot'] . ' — ' . $standard['source_standard_title_snapshot']); $sheet->mergeCells('A' . $row . ':P' . $row); $sheet->getStyle('A' . $row)->getFont()->setBold(TRUE); $row++; foreach ($standard['items'] as $item) { $this->set_text($sheet, 'A' . $row, $item->standard_item_display_order ?: $item->display_order); $this->set_text($sheet, 'B' . $row, $item->indicator_code_snapshot . ' — ' . $item->indicator_title_snapshot); $this->set_text($sheet, 'C' . $row, $item->realization_snapshot); $this->set_text($sheet, 'D' . $row, isset($item->evidence_url_snapshot) ? $item->evidence_url_snapshot : NULL); $this->set_text($sheet, 'E' . $row, isset($item->evidence_file_original_name_snapshot) ? $item->evidence_file_original_name_snapshot : NULL); $this->set_text($sheet, 'F' . $row, isset($item->evidence_file_mime_type_snapshot) ? $item->evidence_file_mime_type_snapshot : NULL); $this->set_text($sheet, 'G' . $row, isset($item->evidence_file_size_bytes_snapshot) ? $item->evidence_file_size_bytes_snapshot : NULL); $this->set_text($sheet, 'H' . $row, isset($item->evidence_file_sha256_snapshot) ? $item->evidence_file_sha256_snapshot : NULL); $this->set_text($sheet, 'I' . $row, $this->auditor_evidence_text(isset($item->auditor_evidence_snapshot) ? $item->auditor_evidence_snapshot : NULL)); $sheet->setCellValue('J' . $row, (int) $item->score); $this->set_text($sheet, 'K' . $row, $item->descriptor_snapshot); $this->set_text($sheet, 'L' . $row, isset($item->finding_type_snapshot) ? $item->finding_type_snapshot : NULL); $this->set_text($sheet, 'M' . $row, $item->finding_snapshot); $this->set_text($sheet, 'N' . $row, $item->recommendation_snapshot); $this->set_text($sheet, 'O' . $row, isset($item->improvement_plan_snapshot) ? $item->improvement_plan_snapshot : NULL); $this->set_text($sheet, 'P' . $row, isset($item->evidence_date_snapshot) ? $item->evidence_date_snapshot : NULL); $row++; } }
        $sheet->getStyle('A1:P' . max(1, $row - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP)->setWrapText(TRUE);
        $sheet->getStyle('A1:P1')->getFont()->setBold(TRUE);
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
