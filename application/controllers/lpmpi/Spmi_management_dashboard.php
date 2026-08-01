<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_management_dashboard extends Admin_Lpmpi_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Spmi_management_dashboard_model');
    }

    public function index()
    {
        $data = $this->Spmi_management_dashboard_model->dashboard();
        $this->load->view('lpmpi/spmi_management_dashboard/index', array_merge($data, ['title' => 'Dashboard SPMI', 'page_title' => 'Dashboard SPMI', 'page_subtitle' => 'Beranda / Insights / Dashboard SPMI', 'active_menu' => 'spmi_dashboard']));
    }

    public function export()
    {
        $year = (int) $this->input->get('year', TRUE);
        $year = $year >= 2000 && $year <= 2100 ? $year : (int) date('Y');
        $autoload = FCPATH . 'vendor/autoload.php';
        if (!is_file($autoload)) { show_error('Library PhpSpreadsheet belum terpasang. Jalankan composer install terlebih dahulu.', 500, 'Export gagal'); return; }
        require_once $autoload;
        if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) { show_error('Library PhpSpreadsheet tidak dapat dimuat.', 500, 'Export gagal'); return; }
        $spreadsheet_class = '\\PhpOffice\\PhpSpreadsheet\\Spreadsheet';
        $writer_class = '\\PhpOffice\\PhpSpreadsheet\\Writer\\Xlsx';
        $spreadsheet = new $spreadsheet_class();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dashboard SPMI');
        $values = [['Tahun', 'Tahap', 'Metrik', 'Jumlah']];
        foreach ($this->Spmi_management_dashboard_model->export_rows($year) as $row) $values[] = $row;
        foreach ($values as $row_number => $row) foreach ($row as $column_number => $value) {
            $value = (string) $value;
            if (isset($value[0]) && in_array($value[0], ['=', '+', '-', '@'], TRUE)) $value = "'" . $value;
            $sheet->setCellValueExplicitByColumnAndRow($column_number + 1, $row_number + 1, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
        while (ob_get_level() > 0) ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="spmi-dashboard-' . $year . '.xlsx"');
        header('Cache-Control: max-age=0');
        (new $writer_class($spreadsheet))->save('php://output');
        $spreadsheet->disconnectWorksheets();
        exit;
    }
}
