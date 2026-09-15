<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php';

class AkunImportReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
    public function readCell($column_address, $row, $worksheet_name = '')
    {
        return $row >= 1 && $row <= 1001 && in_array($column_address, ['A', 'B', 'C', 'D'], TRUE);
    }
}

class Akun_import_service
{
    const HEADERS = ['NIP/NIDN', 'Nama', 'Email', 'Role'];
    protected $ci;
    protected $users;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('User_model');
        $this->users = $this->ci->User_model;
    }

    public function parse($path)
    {
        $this->load_library();
        $spreadsheet = NULL;
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
            $account_sheet = NULL;
            foreach ($reader->listWorksheetInfo($path) as $info) {
                if ($info['worksheetName'] === 'Master Akun') { $account_sheet = $info; break; }
            }
            if ($account_sheet === NULL) throw new RuntimeException('Sheet Master Akun wajib tersedia.');
            if ((int) $account_sheet['totalRows'] > 1001 || (int) $account_sheet['totalColumns'] !== 4) throw new RuntimeException('Workbook maksimal 1.000 baris dan tepat 4 kolom.');
            $reader->setLoadSheetsOnly('Master Akun');
            $reader->setReadFilter(new AkunImportReadFilter());
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getSheetByName('Master Akun');
            if (!$sheet) throw new RuntimeException('Sheet Master Akun wajib tersedia.');
            if ($sheet->rangeToArray('A1:D1', NULL, TRUE, FALSE)[0] !== self::HEADERS) throw new RuntimeException('Header workbook tidak sesuai template Master Akun.');
            $valid = []; $errors = []; $identities = []; $emails = [];
            for ($number = 2; $number <= $sheet->getHighestRow(); $number++) {
                $cells = []; $has_value = FALSE; $has_formula = FALSE;
                for ($column = 1; $column <= 4; $column++) {
                    $cell = $sheet->getCellByColumnAndRow($column, $number);
                    $has_formula = $has_formula || $cell->getDataType() === 'f';
                    $has_value = $has_value || trim((string) $cell->getValue()) !== '';
                    $cells[] = trim((string) $cell->getFormattedValue());
                }
                if (!$has_value) continue;
                if ($has_formula) { $errors[] = ['row' => $number, 'message' => 'Formula tidak diizinkan.']; continue; }
                $identity_cell = $sheet->getCellByColumnAndRow(1, $number);
                if ($identity_cell->getDataType() === \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC) { $errors[] = ['row' => $number, 'message' => 'NIP/NIDN harus diisi sebagai teks.']; continue; }
                $row = $this->normalize($cells);
                $message = $this->validate($row, $identities, $emails, 'check');
                if ($message) { $errors[] = ['row' => $number, 'message' => $message]; continue; }
                $valid[] = $row; $identities[$row['identity_number']] = TRUE; $emails[$row['email']] = TRUE;
            }
            return ['valid' => $valid, 'errors' => $errors, 'total' => max(0, $sheet->getHighestRow() - 1)];
        } finally { if ($spreadsheet !== NULL) $spreadsheet->disconnectWorksheets(); }
    }

    public function confirm($rows)
    {
        $this->ci->db->trans_begin();
        try {
            if (!is_array($rows) || !$rows) return $this->rollback('Preview tidak memiliki baris valid.');
            $identities = []; $emails = []; $created = [];
            foreach ($rows as $row) {
                $row = $this->normalize($row);
                $message = $this->validate($row, $identities, $emails, 'lock');
                if ($message) return $this->rollback('Konfirmasi ditolak: ' . $message);
                $password = $this->temporary_password();
                $hash = password_hash($password, PASSWORD_DEFAULT);
                if (!is_string($hash) || !$this->users->create(['identity_number' => $row['identity_number'], 'nama' => $row['nama'], 'email' => $row['email'], 'password' => $hash, 'must_change_password' => 1, 'role' => $row['role']])) return $this->rollback('Akun gagal disimpan.');
                $identities[$row['identity_number']] = TRUE; $emails[$row['email']] = TRUE;
                $created[] = ['identity_number' => $row['identity_number'], 'nama' => $row['nama'], 'email' => $row['email'], 'role' => $row['role'], 'password' => $password];
            }
            if (!$this->ci->db->trans_status()) return $this->rollback('Akun gagal disimpan.');
            $this->ci->db->trans_commit();
            return ['success' => TRUE, 'message' => count($created) . ' akun berhasil dibuat.', 'created' => $created];
        } catch (Throwable $exception) {
            $this->ci->db->trans_rollback();
            log_message('error', 'Account import confirmation failed.');
            return ['success' => FALSE, 'message' => 'Import akun gagal diproses. Silakan unggah ulang preview.'];
        }
    }

    private function validate($row, $identities, $emails, $check_database)
    {
        if (!preg_match('/\A[[:alnum:].\/-]{1,32}\z/u', $row['identity_number'])) return 'NIP/NIDN wajib diisi dan maksimal 32 karakter.';
        if ($row['nama'] === '' || mb_strlen($row['nama']) > 100) return 'Nama wajib diisi dan maksimal 100 karakter.';
        if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) return 'Email tidak valid.';
        if (!in_array($row['role'], ['auditor', 'auditee'], TRUE)) return 'Role hanya auditor atau auditee.';
        if (isset($identities[$row['identity_number']])) return 'NIP/NIDN duplikat dalam file.';
        if (isset($emails[$row['email']])) return 'Email duplikat dalam file.';
        if ($check_database && ($this->users->find_by_identity_number($row['identity_number'], $check_database === 'lock') || $this->users->find_by_email($row['email'], $check_database === 'lock'))) return 'NIP/NIDN atau email sudah terdaftar.';
        return NULL;
    }

    private function normalize($row)
    {
        return ['identity_number' => trim((string) ($row['identity_number'] ?? $row[0] ?? '')), 'nama' => trim((string) ($row['nama'] ?? $row[1] ?? '')), 'email' => strtolower(trim((string) ($row['email'] ?? $row[2] ?? ''))), 'role' => strtolower(trim((string) ($row['role'] ?? $row[3] ?? '')))];
    }

    private function temporary_password() { return bin2hex(random_bytes(9)); }
    private function rollback($message) { $this->ci->db->trans_rollback(); return ['success' => FALSE, 'message' => $message]; }
    private function load_library() { $autoload = FCPATH . 'vendor/autoload.php'; if (!is_file($autoload)) throw new RuntimeException('Library PhpSpreadsheet belum terpasang.'); require_once $autoload; }
}
