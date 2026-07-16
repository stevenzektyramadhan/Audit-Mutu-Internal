<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '../vendor/autoload.php';

class Pertanyaan_service
{
    protected $ci;
    protected $pertanyaan_model;
    protected $standar_model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Pertanyaan_model');
        $this->ci->load->model('Standar_model');
        $this->pertanyaan_model = $this->ci->Pertanyaan_model;
        $this->standar_model = $this->ci->Standar_model;
    }

    public function get_all_pertanyaan($standar_id = NULL)
    {
        $standar_id = $standar_id !== NULL ? (int) $standar_id : NULL;
        if ($standar_id !== NULL && !$this->standar_model->find($standar_id)) {
            $standar_id = NULL;
        }

        return $this->pertanyaan_model->get_all_with_standar($standar_id);
    }

    public function get_pertanyaan($id)
    {
        return $this->pertanyaan_model->find($id);
    }

    public function get_standar($id)
    {
        return $this->standar_model->find((int) $id);
    }
    
    public function get_all_standar()
    {
        return $this->standar_model->get_all_with_count();
    }

    public function generate_template($standar_id)
    {
        $standar = $this->standar_model->find((int) $standar_id);
        if (!$standar) {
            throw new InvalidArgumentException('Standar tidak ditemukan.');
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('AMI')
            ->setTitle('Template Import Pertanyaan - ' . $standar->nama_standar)
            ->setSubject('Template import indikator ketercapaian standar');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pertanyaan');

        $headers = [
            'No',
            'Indikator',
            'Nilai Standar',
            'Baseline 2024/2025',
            'Target 2025/2026',
            'Target 2026/2027',
            'Target 2027/2028',
            'Target 2028/2029',
            'Target 2029/2030',
            'Kategori',
        ];
        $sheet->fromArray($headers, NULL, 'A1');
        $sheet->fromArray([
            1,
            'Program Studi menyusun profil lulusan...',
            '100% tersedia',
            '100% tersedia',
            '100% tersedia',
            '100% tersedia',
            '100% tersedia',
            '100% tersedia',
            '100% tersedia',
            'IKU',
        ], NULL, 'A2');

        $sheet->getStyle('A1:J1')->getFont()->setBold(TRUE);
        $sheet->getStyle('A1:J1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9D9D9');
        $sheet->getStyle('A1:J1')->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
            ->setWrapText(TRUE);
        $sheet->getStyle('A2:J2')->getFont()->getColor()->setARGB('FF808080');
        $sheet->getStyle('A2:J1000')->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP)
            ->setWrapText(TRUE);

        $widths = [
            'A' => 5,
            'B' => 60,
            'C' => 30,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 20,
            'H' => 20,
            'I' => 20,
            'J' => 10,
        ];
        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $validation = $sheet->getCell('J2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validation->setAllowBlank(FALSE);
        $validation->setShowDropDown(TRUE);
        $validation->setShowErrorMessage(TRUE);
        $validation->setErrorTitle('Kategori tidak valid');
        $validation->setError('Pilih kategori IKU atau IKT.');
        $validation->setFormula1('"IKU,IKT"');
        $validation->setSqref('J2:J1000');

        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:J1');
        $sheet->getRowDimension(1)->setRowHeight(32);

        return $spreadsheet;
    }

    public function import_excel($standar_id, $file_path)
    {
        if (!$this->standar_model->find((int) $standar_id)) {
            throw new InvalidArgumentException('Standar tidak ditemukan.');
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
        } catch (\Throwable $exception) {
            throw new RuntimeException('File tidak dapat dibaca, pastikan format .xlsx.', 0, $exception);
        }

        try {
            $sheet = $spreadsheet->getSheetByName('Pertanyaan');
            if ($sheet === NULL) {
                throw new RuntimeException("Sheet 'Pertanyaan' tidak ditemukan di file.");
            }

            $valid = [];
            $errors = [];
            $warnings = [];
            $total = 0;
            $highest_row = $sheet->getHighestDataRow();

            for ($row_number = 2; $row_number <= $highest_row; $row_number++) {
                $values = [];
                foreach (range('A', 'J') as $column) {
                    $values[$column] = trim((string) $sheet->getCell($column . $row_number)->getFormattedValue());
                }

                if (implode('', $values) === '') {
                    continue;
                }

                $total++;
                $row_errors = [];

                if ($values['A'] === '' || !is_numeric($values['A'])
                    || (float) $values['A'] != floor((float) $values['A'])) {
                    $row_errors[] = ['column' => 'No', 'reason' => 'No harus berupa angka bulat.'];
                }

                if ($values['B'] === '') {
                    $row_errors[] = ['column' => 'Indikator', 'reason' => 'Indikator tidak boleh kosong.'];
                }

                if ($values['C'] === '') {
                    $row_errors[] = ['column' => 'Nilai Standar', 'reason' => 'Nilai Standar tidak boleh kosong.'];
                }

                $category = strtoupper($values['J']);
                if ($category === '') {
                    $row_errors[] = ['column' => 'Kategori', 'reason' => 'Kategori tidak boleh kosong.'];
                } elseif (!in_array($category, ['IKU', 'IKT'], TRUE)) {
                    $warnings[] = [
                        'row' => $row_number,
                        'column' => 'Kategori',
                        'reason' => 'Kategori "' . $values['J'] . '" tidak dikenal dan dikoreksi menjadi IKU.',
                    ];
                    $category = 'IKU';
                }

                if (!empty($row_errors)) {
                    foreach ($row_errors as $error) {
                        $errors[] = [
                            'row' => $row_number,
                            'column' => $error['column'],
                            'reason' => $error['reason'],
                        ];
                    }
                    continue;
                }

                $valid[] = [
                    'source_row' => $row_number,
                    'no' => (int) $values['A'],
                    'isi_pertanyaan' => $values['B'],
                    'nilai_standar' => $values['C'],
                    'baseline' => $this->empty_to_null($values['D']),
                    'target_2025' => $this->empty_to_null($values['E']),
                    'target_2026' => $this->empty_to_null($values['F']),
                    'target_2027' => $this->empty_to_null($values['G']),
                    'target_2028' => $this->empty_to_null($values['H']),
                    'target_2029' => $this->empty_to_null($values['I']),
                    'target_2030' => NULL,
                    'kategori' => $category,
                ];
            }

            return [
                'valid' => $valid,
                'errors' => $errors,
                'warnings' => $warnings,
                'total' => $total,
            ];
        } finally {
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }
    }

    public function insert_bulk($standar_id, $data_array)
    {
        if (!$this->standar_model->find((int) $standar_id)) {
            return FALSE;
        }

        return $this->pertanyaan_model->insert_bulk((int) $standar_id, $data_array);
    }

    public function create_pertanyaan($data)
    {
        $validation = $this->validate_data($data);
        if (!$validation['success']) {
            return $validation;
        }

        $data = $validation['data'];
        if ($this->pertanyaan_model->create($data)) {
            return ['success' => true, 'message' => 'Pertanyaan berhasil ditambahkan.'];
        }
        return ['success' => false, 'message' => 'Gagal menambahkan pertanyaan.'];
    }

    public function update_pertanyaan($id, $data)
    {
        $pertanyaan = $this->pertanyaan_model->find($id);
        if (!$pertanyaan) {
            return ['success' => FALSE, 'message' => 'Pertanyaan tidak ditemukan.'];
        }

        $validation = $this->validate_data($data);
        if (!$validation['success']) {
            return $validation;
        }

        if ((int) $validation['data']['standar_id'] !== (int) $pertanyaan->standar_id
            && $this->pertanyaan_model->is_used_in_answers($id)) {
            return ['success' => FALSE, 'message' => 'Pertanyaan yang sudah digunakan dalam audit tidak dapat dipindahkan ke standar lain.'];
        }

        if ($this->pertanyaan_model->update($id, $validation['data'])) {
            return ['success' => TRUE, 'message' => 'Pertanyaan berhasil diperbarui.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal memperbarui pertanyaan.'];
    }

    public function delete_pertanyaan($id)
    {
        if (!$this->pertanyaan_model->find($id)) {
            return ['success' => FALSE, 'message' => 'Pertanyaan tidak ditemukan.'];
        }

        if ($this->pertanyaan_model->delete($id)) {
            return ['success' => TRUE, 'message' => 'Pertanyaan berhasil dihapus.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal menghapus pertanyaan.'];
    }

    private function validate_data($data)
    {
        $standar_id = isset($data['standar_id']) ? (int) $data['standar_id'] : 0;
        $isi_pertanyaan = trim(isset($data['isi_pertanyaan']) ? $data['isi_pertanyaan'] : '');

        if (!$this->standar_model->find($standar_id)) {
            return ['success' => FALSE, 'message' => 'Standar yang dipilih tidak valid.'];
        }

        if ($isi_pertanyaan === '') {
            return ['success' => FALSE, 'message' => 'Isi pertanyaan wajib diisi.'];
        }

        return [
            'success' => TRUE,
            'data' => ['standar_id' => $standar_id, 'isi_pertanyaan' => $isi_pertanyaan],
        ];
    }

    private function empty_to_null($value)
    {
        return $value === '' ? NULL : $value;
    }
}
