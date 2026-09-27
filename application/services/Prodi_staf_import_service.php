<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php';

class ProdiStafImportReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
    public function readCell($column_address, $row, $worksheet_name = '')
    {
        return $row >= 1 && $row <= 1001 && in_array($column_address, ['A', 'B', 'C'], TRUE);
    }
}

class Prodi_staf_import_service
{
    const PRODI_HEADERS = ['kode_prodi', 'nama_prodi', 'jenjang'];
    protected $ci;
    protected $model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Prodi_staf_import_model');
        $this->model = $this->ci->Prodi_staf_import_model;
    }

    public function parse($path)
    {
        $spreadsheet = NULL;
        try {
             $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
             $info = $reader->listWorksheetInfo($path);
             $sheets = [];
             foreach ($info as $sheet) $sheets[$sheet['worksheetName']] = $sheet;
              if (count($info) !== 1 || count($sheets) !== 1 || !isset($sheets['Prodi'])) throw new RuntimeException('Workbook wajib tepat memiliki sheet Prodi.');
              if ((int) $sheets['Prodi']['totalRows'] > 1001 || (int) $sheets['Prodi']['totalColumns'] !== 3) throw new RuntimeException('Sheet Prodi maksimal 1.000 baris dan tepat 3 kolom.');
             $reader->setLoadSheetsOnly(['Prodi']);
            $reader->setReadFilter(new ProdiStafImportReadFilter());
            $spreadsheet = $reader->load($path);
            $prodi_sheet = $spreadsheet->getSheetByName('Prodi');
             for ($column = 1; $column <= 3; $column++) if ($prodi_sheet->getCellByColumnAndRow($column, 1)->getDataType() === 'f') throw new RuntimeException('Formula tidak diizinkan.');
             if ($prodi_sheet->rangeToArray('A1:C1', NULL, TRUE, FALSE)[0] !== self::PRODI_HEADERS) throw new RuntimeException('Header workbook tidak sesuai template.');

              $valid_prodi = []; $seen_codes = []; $errors = []; $total = 0;
             for ($row_number = 2; $row_number <= $prodi_sheet->getHighestRow(); $row_number++) {
                 $row = $this->sheet_row($prodi_sheet, $row_number);
                 if ($row === NULL) continue;
                 $total++;
                if ($row === FALSE) { $errors[] = ['sheet' => 'Prodi', 'row' => $row_number, 'message' => 'Formula tidak diizinkan.']; continue; }
                 $item = ['row_number' => $row_number, 'kode_prodi' => $row[0], 'nama_prodi' => $row[1], 'jenjang' => $row[2]];
                if (!preg_match('/\A[^\s]{1,20}\z/u', $item['kode_prodi'])) { $errors[] = ['sheet' => 'Prodi', 'row' => $row_number, 'message' => 'Kode prodi wajib diisi sesuai batas kolom.']; continue; }
                if (isset($seen_codes[$item['kode_prodi']])) { $errors[] = ['sheet' => 'Prodi', 'row' => $row_number, 'message' => 'Kode prodi duplikat dalam file.']; continue; }
                $seen_codes[$item['kode_prodi']] = TRUE;
                if ($item['nama_prodi'] === '' || mb_strlen($item['nama_prodi']) > 200 || $item['jenjang'] === '' || mb_strlen($item['jenjang']) > 20) { $errors[] = ['sheet' => 'Prodi', 'row' => $row_number, 'message' => 'Nama dan jenjang wajib diisi sesuai batas kolom.']; continue; }
                $existing_prodi = $this->model->find_prodi_by_code($item['kode_prodi']);
                if (count($existing_prodi) > 1) { $errors[] = ['sheet' => 'Prodi', 'row' => $row_number, 'message' => 'Kode prodi duplikat pada data profil existing.']; continue; }
                $item['classification'] = $existing_prodi ? 'update' : 'create';
                 $valid_prodi[] = $item;
             }
              return ['prodi' => $valid_prodi, 'errors' => $errors, 'total' => $total];
        } finally { if ($spreadsheet !== NULL) $spreadsheet->disconnectWorksheets(); }
    }

    public function confirm($payload)
    {
        $locked = FALSE;
        try {
            $payload = $this->validate_payload($payload);
            if ($payload === FALSE || empty($payload['prodi'])) return ['success' => FALSE, 'message' => 'Data preview tidak valid.'];
            if (!$this->model->acquire_confirm_lock()) return ['success' => FALSE, 'message' => 'Import sedang diproses. Silakan coba kembali.'];
            $locked = TRUE;
            $this->ci->db->trans_begin();
            $prodi_ids = [];
            foreach ($payload['prodi'] as $row) {
                $existing = $this->model->find_prodi_by_code($row['kode_prodi'], TRUE);
                if (count($existing) > 1) return $this->rollback('Konfirmasi ditolak: kode prodi existing ambigu.');
                $prodi_ids[$row['kode_prodi']] = $existing ? (int) $existing[0]->id : 0;
            }
            foreach ($payload['prodi'] as $row) {
                $data = ['kode_prodi' => $row['kode_prodi'], 'nama_prodi' => $row['nama_prodi'], 'jenjang' => $row['jenjang']];
                if ($prodi_ids[$row['kode_prodi']]) { if (!$this->model->update_prodi($prodi_ids[$row['kode_prodi']], $data)) return $this->rollback('Program studi gagal disimpan.'); }
                else { if (!$this->model->create_prodi($data)) return $this->rollback('Program studi gagal disimpan.'); $prodi_ids[$row['kode_prodi']] = (int) $this->ci->db->insert_id(); }
            }
            if (!$this->ci->db->trans_status()) return $this->rollback('Import gagal disimpan.');
            $this->ci->db->trans_commit();
            return ['success' => TRUE, 'message' => count($payload['prodi']) . ' prodi berhasil disimpan.'];
        } catch (Throwable $exception) { $this->ci->db->trans_rollback(); log_message('error', 'Prodi staf import confirmation failed.'); return ['success' => FALSE, 'message' => 'Import gagal diproses. Silakan unggah ulang preview.']; }
        finally { if ($locked) $this->model->release_confirm_lock(); }
    }

    public function validation_error_report($payload)
    {
        $errors = $this->validate_error_report($payload);
        if ($errors === FALSE) throw new InvalidArgumentException('Payload preview tidak valid.');
        $rows = [];
        foreach ($errors as $error) $rows[] = [$this->csv_text($error['sheet']), $error['row'], $this->csv_text($error['message'])];
        return $rows;
    }

    private function validate_error_report($payload)
    {
        if (!is_array($payload) || !isset($payload['errors']) || !is_array($payload['errors'])) return FALSE;
        foreach ($payload['errors'] as $error) {
            if (!is_array($error) || array_diff(array_keys($error), ['sheet', 'row', 'message']) || !isset($error['sheet'], $error['row'], $error['message']) || !is_string($error['sheet']) || !is_int($error['row']) || !is_string($error['message'])) return FALSE;
            if (($error['sheet'] === '-' && $error['row'] !== 0) || ($error['sheet'] === 'Prodi' && ($error['row'] < 2 || $error['row'] > 1001)) || !in_array($error['sheet'], ['Prodi', '-'], TRUE)) return FALSE;
        }
        return $payload['errors'];
    }

    private function validate_payload($payload)
    {
        if (!is_array($payload) || array_diff(array_keys($payload), ['prodi', 'errors', 'total']) || !isset($payload['prodi'], $payload['errors'], $payload['total']) || !is_array($payload['prodi']) || !is_array($payload['errors']) || !is_int($payload['total']) || $payload['total'] < 0 || $payload['total'] > 1000) return FALSE;
        foreach ($payload['errors'] as $error) if (!is_array($error) || array_diff(array_keys($error), ['sheet', 'row', 'message']) || !isset($error['sheet'], $error['row'], $error['message']) || !is_string($error['sheet']) || !(($error['sheet'] === '-' && $error['row'] === 0) || ($error['sheet'] === 'Prodi' && is_int($error['row']) && $error['row'] >= 2 && $error['row'] <= 1001)) || !is_string($error['message'])) return FALSE;
        $codes = [];
        foreach ($payload['prodi'] as $row) {
            if (!$this->valid_row($row, ['row_number', 'kode_prodi', 'nama_prodi', 'jenjang', 'classification'], ['row_number' => 'integer', 'kode_prodi' => 'string', 'nama_prodi' => 'string', 'jenjang' => 'string', 'classification' => 'string']) || $row['row_number'] < 2 || $row['row_number'] > 1001 || !preg_match('/\A[^\s]{1,20}\z/u', $row['kode_prodi']) || $row['nama_prodi'] === '' || mb_strlen($row['nama_prodi']) > 200 || $row['jenjang'] === '' || mb_strlen($row['jenjang']) > 20 || !in_array($row['classification'], ['create', 'update'], TRUE) || isset($codes[$row['kode_prodi']])) return FALSE;
            $codes[$row['kode_prodi']] = TRUE;
        }
        return count($payload['prodi']) + count($payload['errors']) === $payload['total'] ? $payload : FALSE;
    }

    private function valid_row($row, $keys, $types)
    {
        if (!is_array($row) || count($row) !== count($keys) || array_diff(array_keys($row), $keys)) return FALSE;
        foreach ($types as $key => $type) if (!array_key_exists($key, $row) || gettype($row[$key]) !== $type) return FALSE;
        return TRUE;
    }

    private function csv_text($value)
    {
        if ($value !== '' && (preg_match('/^[\t\r]/', $value) || preg_match('/^\s*[=+\-@]/u', $value))) return "'" . $value;
        return $value;
    }

    private function sheet_row($sheet, $row_number)
    {
        $values = []; $has_value = FALSE;
        for ($column = 1; $column <= 3; $column++) { $cell = $sheet->getCellByColumnAndRow($column, $row_number); if ($cell->getDataType() === 'f') return FALSE; $value = trim((string) $cell->getFormattedValue()); $has_value = $has_value || $value !== ''; $values[] = $value; }
        return $has_value ? $values : NULL;
    }
    private function rollback($message) { $this->ci->db->trans_rollback(); return ['success' => FALSE, 'message' => $message]; }
}
