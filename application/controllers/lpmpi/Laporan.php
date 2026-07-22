<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Laporan Controller - Laporan & Statistik Admin LPMPI.
 *
 * @property Laporan_model $Laporan_model
 * @property Periode_model $Periode_model
 * @property User_model $User_model
 */
class Laporan extends Admin_Lpmpi_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['url', 'form']);
        $this->load->model('Laporan_model');
        $this->load->model('Periode_model');
        $this->load->model('User_model');
    }

    public function index()
    {
        $filters = $this->filters();
        $rekap = $this->Laporan_model->rekap_per_standar($filters);

        $data['title']        = 'Laporan & Statistik';
        $data['page_title']   = 'Laporan & Statistik';
        $data['page_subtitle'] = 'Rekap skor rata-rata per standar, filter periode & auditee';
        $data['active_menu']  = 'laporan';
        $data['filters']      = $filters;
        $data['periode_list'] = $this->Periode_model->get_all();
        $data['auditee_list'] = $this->User_model->get_by_role('auditee');
        $data['rekap']        = $rekap;
        $data['chart_labels'] = json_encode(array_map(function ($row) {
            return $row->nama_standar;
        }, $rekap), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $data['chart_values'] = json_encode(array_map(function ($row) {
            return round((float) $row->rata_rata_skor, 2);
        }, $rekap), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        $this->load->view('lpmpi/laporan/index', $data);
    }

    public function detail($standar_id)
    {
        $standar = $this->Laporan_model->find_standar((int) $standar_id);
        if (!$standar) {
            show_error('Standar tidak ditemukan.', 404, 'Not Found');
            return;
        }

        $filters = $this->filters();

        $data['title']        = 'Detail Laporan Standar';
        $data['page_title']   = 'Detail Laporan';
        $data['page_subtitle'] = $standar->nama_standar;
        $data['active_menu']  = 'laporan';
        $data['filters']      = $filters;
        $data['standar']      = $standar;
        $data['detail']       = $this->Laporan_model->detail_per_standar((int) $standar_id, $filters);

        $this->load->view('lpmpi/laporan/detail', $data);
    }

    public function export()
    {
        $this->load_phpspreadsheet();

        $filters = $this->filters();
        $standar_list = $this->Laporan_model->standar_untuk_export($filters);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('AMI')
            ->setTitle('Laporan AMI')
            ->setSubject('Export laporan audit mutu internal');

        if (empty($standar_list)) {
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Laporan');
            $this->write_export_sheet($sheet, []);
        } else {
            $used_titles = [];
            foreach ($standar_list as $index => $standar) {
                $sheet = $index === 0
                    ? $spreadsheet->getActiveSheet()
                    : $spreadsheet->createSheet($index);
                $sheet->setTitle($this->sheet_title($standar->nama_standar, $used_titles));

                $rows = $this->Laporan_model->export_per_standar((int) $standar->standar_id, $filters);
                $this->write_export_sheet($sheet, $rows);
            }
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'laporan_ami_' . date('Ymd_His') . '.xlsx';
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        $spreadsheet->disconnectWorksheets();
        exit;
    }

    private function filters()
    {
        return [
            'periode_id' => (int) $this->input->get('periode_id', TRUE),
            'auditee_id' => (int) $this->input->get('auditee_id', TRUE),
        ];
    }

    private function load_phpspreadsheet()
    {
        $autoload = FCPATH . 'vendor/autoload.php';
        if (!is_file($autoload)) {
            show_error('Library PhpSpreadsheet belum terpasang. Jalankan composer install terlebih dahulu.', 500, 'Export gagal');
            exit;
        }

        require_once $autoload;

        if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
            show_error('Library PhpSpreadsheet tidak dapat dimuat.', 500, 'Export gagal');
            exit;
        }
    }

    private function write_export_sheet($sheet, $rows)
    {
        $headers = [
            'A' => 'Auditee',
            'B' => 'Pertanyaan',
            'C' => 'Jawaban',
            'D' => 'Link Bukti',
            'E' => 'Skor',
            'F' => 'Temuan',
            'G' => 'Jenis Temuan',
            'H' => 'Saran Perbaikan',
        ];

        foreach ($headers as $column => $label) {
            $this->set_cell_text($sheet, $column . '1', $label);
        }

        if (empty($rows)) {
            $sheet->mergeCells('A2:H2');
            $this->set_cell_text($sheet, 'A2', 'Belum ada data laporan untuk filter ini.');
            $last_row = 2;
        } else {
            $row_number = 2;
            foreach ($rows as $row) {
                $this->set_cell_text($sheet, 'A' . $row_number, $row->auditee_nama ?? '-');
                $this->set_cell_text($sheet, 'B' . $row_number, $row->isi_pertanyaan ?? '-');
                $this->set_cell_text($sheet, 'C' . $row_number, $row->jawaban ?? '-');
                $this->set_link_cell($sheet, 'D' . $row_number, $row->link_bukti ?? '');

                if ($row->skor === NULL || $row->skor === '') {
                    $this->set_cell_text($sheet, 'E' . $row_number, '-');
                } else {
                    $sheet->setCellValue('E' . $row_number, (int) $row->skor);
                }

                $this->set_cell_text($sheet, 'F' . $row_number, $row->temuan ?? '-');
                $this->set_cell_text($sheet, 'G' . $row_number, !empty($row->jenis_temuan) ? strtoupper($row->jenis_temuan) : '-');
                $this->set_cell_text($sheet, 'H' . $row_number, $row->saran_perbaikan ?? '-');

                $row_number++;
            }
            $last_row = $row_number - 1;
        }

        $this->style_export_sheet($sheet, $last_row);
    }

    private function style_export_sheet($sheet, $last_row)
    {
        $widths = [
            'A' => 26,
            'B' => 48,
            'C' => 42,
            'D' => 38,
            'E' => 10,
            'F' => 40,
            'G' => 16,
            'H' => 40,
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:H' . max(1, (int) $last_row));
        $sheet->getStyle('A1:H1')->getFont()->setBold(TRUE);
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFEAF2F8');
        $sheet->getStyle('A1:H' . max(1, (int) $last_row))->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP)
            ->setWrapText(TRUE);
        $sheet->getStyle('A1:H1')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    }

    private function set_cell_text($sheet, $cell, $value)
    {
        $value = trim((string) $value);
        $sheet->setCellValueExplicit(
            $cell,
            $value === '' ? '-' : $value,
            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
        );
    }

    private function set_link_cell($sheet, $cell, $value)
    {
        $value = trim((string) $value);
        $this->set_cell_text($sheet, $cell, $value);

        if ($value !== '' && filter_var($value, FILTER_VALIDATE_URL) !== FALSE) {
            $sheet->getCell($cell)->getHyperlink()->setUrl($value);
            $sheet->getStyle($cell)->getFont()
                ->getColor()->setARGB('FF0563C1');
            $sheet->getStyle($cell)->getFont()->setUnderline(TRUE);
        }
    }

    private function sheet_title($value, &$used_titles)
    {
        $title = str_replace(['\\', '/', '?', '*', '[', ']', ':'], ' ', (string) $value);
        $title = trim(preg_replace('/\s+/', ' ', $title));
        $title = $title === '' ? 'Standar' : $title;

        $base = mb_substr($title, 0, 31);
        $title = $base;
        $counter = 2;

        while (isset($used_titles[strtolower($title)])) {
            $suffix = ' ' . $counter;
            $title = mb_substr($base, 0, 31 - mb_strlen($suffix)) . $suffix;
            $counter++;
        }

        $used_titles[strtolower($title)] = TRUE;
        return $title;
    }
}
