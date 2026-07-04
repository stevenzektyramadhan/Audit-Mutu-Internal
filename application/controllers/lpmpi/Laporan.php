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
        }, $rekap));
        $data['chart_values'] = json_encode(array_map(function ($row) {
            return round((float) $row->rata_rata_skor, 2);
        }, $rekap));

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
        $filters = $this->filters();
        $rekap = $this->Laporan_model->rekap_per_standar($filters);

        $filename = 'laporan_ami_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "Standar\tTotal Tugas\tTotal Jawaban\tRata-rata Skor\tSkor Minimum\tSkor Maksimum\n";
        foreach ($rekap as $row) {
            echo $this->excel_cell($row->nama_standar) . "\t";
            echo (int) $row->total_tugas . "\t";
            echo (int) $row->total_jawaban . "\t";
            echo number_format((float) $row->rata_rata_skor, 2, '.', '') . "\t";
            echo ($row->skor_min === NULL ? '-' : (int) $row->skor_min) . "\t";
            echo ($row->skor_max === NULL ? '-' : (int) $row->skor_max) . "\n";
        }
    }

    private function filters()
    {
        return [
            'periode_id' => (int) $this->input->get('periode_id', TRUE),
            'auditee_id' => (int) $this->input->get('auditee_id', TRUE),
        ];
    }

    private function excel_cell($value)
    {
        return str_replace(["\t", "\r", "\n"], ' ', (string) $value);
    }
}
