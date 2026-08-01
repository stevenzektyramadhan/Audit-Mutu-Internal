<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_master_service
{
    const HEADERS = ['Standard Code', 'Standard Order', 'Standard Title', 'Standard Description', 'Indicator Code', 'Indicator Type', 'Indicator Title', 'Scope Unit Code', 'Responsible Unit Code', 'Responsible PIC', 'Evidence Requirement', 'Target Year', 'Target Value'];
    protected $ci;
    protected $standards;
    protected $indicators;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Spmi_standards_model');
        $this->ci->load->model('Spmi_indicators_model');
        $this->standards = $this->ci->Spmi_standards_model;
        $this->indicators = $this->ci->Spmi_indicators_model;
    }

    public function versions() { return $this->standards->get_versions(); }
    public function version($id) { return $this->standards->find_version((int) $id); }
    public function mutable($version) { return $version && in_array($version->status, ['draft', 'review'], TRUE); }

    public function parse($path)
    {
        $this->load_library();
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(TRUE);
        $spreadsheet = $reader->load($path);
        try {
            $sheet = $spreadsheet->getSheetByName('SPMI Master');
            if (!$sheet) throw new RuntimeException('Sheet SPMI Master wajib tersedia.');
            $max_rows = 10000;
            if ($sheet->getHighestColumn() !== 'M' || $sheet->getHighestRow() > $max_rows + 1) throw new RuntimeException('Workbook maksimal 10.000 baris dan 13 kolom.');
            $header = $sheet->rangeToArray('A1:M1', NULL, TRUE, FALSE)[0];
            if ($header !== self::HEADERS) throw new RuntimeException('Header workbook tidak sesuai template Master SPMI.');
            $valid = []; $errors = []; $seen = []; $standards_seen = [];
            for ($row_number = 2; $row_number <= $sheet->getHighestRow(); $row_number++) {
                $has_value = FALSE;
                $has_formula = FALSE;
                for ($column = 1; $column <= 13; $column++) { $cell = $sheet->getCellByColumnAndRow($column, $row_number); if ($cell->getDataType() === 'f') $has_formula = TRUE; if (trim((string) $cell->getValue()) !== '') $has_value = TRUE; }
                if (!$has_value) continue;
                if ($has_formula) { $errors[] = ['row' => $row_number, 'message' => 'Formula tidak diizinkan.']; continue; }
                $values = array_map(function ($value) { return trim((string) $value); }, $sheet->rangeToArray('A' . $row_number . ':M' . $row_number, NULL, TRUE, FALSE)[0]);
                $standard_error = $this->validate_standard_metadata($values, $row_number, $standards_seen);
                if ($standard_error) { $errors[] = ['row' => $row_number, 'message' => $standard_error]; continue; }
                $error = $this->validate_row($values, $row_number, $seen);
                if ($error) $errors[] = ['row' => $row_number, 'message' => $error]; else { $valid[] = $this->normalize_row($values); $seen[strtoupper($values[0]) . '|' . strtoupper($values[4])] = $values; }
            }
            return ['valid' => $valid, 'errors' => $errors, 'total' => max(0, $sheet->getHighestRow() - 1)];
        } finally { $spreadsheet->disconnectWorksheets(); }
    }

    public function confirm($version_id, $rows)
    {
        $this->ci->db->trans_begin();
        try {
            $version = $this->standards->find_version((int) $version_id, TRUE);
            if (!$this->mutable($version) || empty($rows)) return $this->rollback_result('Versi harus draft/review dan preview harus memiliki baris valid.');
            foreach ($rows as $row) {
                $unit_scope = $this->unit($row['scope_unit_code']); $unit_responsible = $this->unit($row['responsible_unit_code']);
                if (!$unit_scope || !$unit_responsible) return $this->rollback_result('Unit organisasi aktif tidak ditemukan.');
                $standard = $this->standards->find_standard_by_version_code($version_id, $row['standard_code'], TRUE);
                $standard_data = ['version_id' => (int) $version_id, 'standard_code' => $row['standard_code'], 'display_order' => $row['standard_order'], 'title' => $row['standard_title'], 'description' => $row['standard_description'] ?: NULL];
                if (!$this->standards->upsert_standard($standard_data, $standard ? $standard->id : 0)) return $this->rollback_result('Standar gagal disimpan.');
                $standard_id = $standard ? (int) $standard->id : (int) $this->ci->db->insert_id();
                $indicator = $this->indicators->find_indicator_by_standard_code($standard_id, $row['indicator_code'], TRUE);
                $indicator_data = ['standard_id' => $standard_id, 'indicator_code' => $row['indicator_code'], 'indicator_type' => $row['indicator_type'], 'title' => $row['indicator_title'], 'scope_organization_unit_id' => (int) $unit_scope->id, 'responsible_organization_unit_id' => (int) $unit_responsible->id, 'responsible_pic_name' => $row['responsible_pic'] ?: NULL, 'evidence_requirement' => $row['evidence_requirement']];
                if (!$this->indicators->upsert_indicator($indicator_data, $indicator ? $indicator->id : 0)) return $this->rollback_result('Indikator gagal disimpan.');
                $indicator_id = $indicator ? (int) $indicator->id : (int) $this->ci->db->insert_id();
                if ($row['target_year'] !== NULL && !$this->indicators->upsert_target(['indicator_id' => $indicator_id, 'target_year' => $row['target_year'], 'target_value' => $row['target_value']], ($target = $this->indicators->find_target_by_indicator_year($indicator_id, $row['target_year'], TRUE)) ? $target->id : 0)) return $this->rollback_result('Target gagal disimpan.');
            }
            if (!$this->ci->db->trans_status()) return $this->rollback_result('Master SPMI gagal disimpan.');
            $this->ci->db->trans_commit();
            return ['success' => TRUE, 'message' => 'Master SPMI berhasil diimport secara additive.'];
        } catch (Throwable $exception) {
            $this->ci->db->trans_rollback();
            log_message('error', 'SPMI master confirm database failure: ' . $exception->getMessage());
            return ['success' => FALSE, 'message' => 'Master SPMI gagal disimpan.'];
        }
    }

    public function export_rows($version_id) { return $this->indicators->get_master_rows($version_id); }

    private function validate_row($v, $number, &$seen)
    {
        if ($v[0] === '' || !preg_match('/^[A-Z0-9._-]+$/', strtoupper($v[0]))) return 'Kode standar tidak valid.';
        if ((int) $v[1] < 1 || (string) (int) $v[1] !== $v[1]) return 'Urutan standar harus bilangan positif.';
        if ($v[2] === '' || $v[4] === '' || !preg_match('/^[A-Z0-9._-]+$/', strtoupper($v[4]))) return 'Kode dan judul standar/indikator wajib valid.';
        if (!in_array(strtoupper($v[5]), ['IKU', 'IKT'], TRUE) || $v[6] === '' || $v[7] === '' || $v[8] === '' || $v[10] === '') return 'Data indikator wajib valid.';
        if (($v[11] === '') !== ($v[12] === '')) return 'Target Year dan Target Value harus diisi bersama.';
        if ($v[11] !== '' && ((int) $v[11] < 2000 || (int) $v[11] > 2100 || (string) (int) $v[11] !== $v[11])) return 'Target Year harus 2000-2100.';
        $key = strtoupper($v[0]) . '|' . strtoupper($v[4]);
        if (isset($seen[$key])) { for ($i = 0; $i < 11; $i++) if ($seen[$key][$i] !== $v[$i]) return 'Data berulang bertentangan pada baris ' . $number . '.'; }
        return NULL;
    }
    private function validate_standard_metadata($v, $number, &$seen)
    {
        $key = strtoupper($v[0]);
        $metadata = [(int) $v[1], $v[2], $v[3]];
        if (isset($seen[$key]) && $seen[$key] !== $metadata) return 'Data standar berulang bertentangan pada baris ' . $number . '.';
        $seen[$key] = $metadata;
        return NULL;
    }
    private function normalize_row($v) { $v[0] = strtoupper($v[0]); $v[4] = strtoupper($v[4]); $v[5] = strtoupper($v[5]); $v[1] = (int) $v[1]; $v[11] = $v[11] === '' ? NULL : (int) $v[11]; return ['standard_code' => $v[0], 'standard_order' => $v[1], 'standard_title' => $v[2], 'standard_description' => $v[3], 'indicator_code' => $v[4], 'indicator_type' => $v[5], 'indicator_title' => $v[6], 'scope_unit_code' => strtoupper($v[7]), 'responsible_unit_code' => strtoupper($v[8]), 'responsible_pic' => $v[9], 'evidence_requirement' => $v[10], 'target_year' => $v[11], 'target_value' => $v[12]]; }
    private function unit($code) { return $this->ci->db->where(['code' => strtoupper($code), 'is_active' => 1])->get('organization_units')->row(); }
    private function load_library() { $autoload = FCPATH . 'vendor/autoload.php'; if (!is_file($autoload)) throw new RuntimeException('Library PhpSpreadsheet belum terpasang.'); require_once $autoload; if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) throw new RuntimeException('Library PhpSpreadsheet tidak dapat dimuat.'); }
    private function finish($success, $message) { $this->ci->db->trans_complete(); return ['success' => $success && $this->ci->db->trans_status(), 'message' => $message]; }
    private function rollback_result($message) { $this->ci->db->trans_rollback(); return ['success' => FALSE, 'message' => $message]; }
}
