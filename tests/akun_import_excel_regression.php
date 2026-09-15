<?php

define('BASEPATH', __DIR__ . '/');
define('FCPATH', dirname(__DIR__) . '/');

class AkunImportFixtureUsers
{
    public function find_by_identity_number($identity_number, $for_update = FALSE) { return NULL; }
    public function find_by_email($email, $for_update = FALSE) { return NULL; }
}

class AkunImportFixtureLoader
{
    public $ci;
    public function model($name) { $this->ci->User_model = new AkunImportFixtureUsers(); }
}

class AkunImportFixtureCi
{
    public $load;
    public function __construct() { $this->load = new AkunImportFixtureLoader(); $this->load->ci = $this; }
}

$akun_import_fixture_ci = new AkunImportFixtureCi();
function &get_instance()
{
    global $akun_import_fixture_ci;
    return $akun_import_fixture_ci;
}

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/application/services/Akun_import_service.php';

function akun_import_excel_check($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

$temporary = tempnam(sys_get_temp_dir(), 'akun-import-');
if ($temporary === FALSE) throw new RuntimeException('Temporary fixture tidak dapat dibuat.');
$path = $temporary . '.xlsx';
unlink($temporary);

try {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Master Akun');
    $sheet->fromArray(Akun_import_service::HEADERS, NULL, 'A1');
    $sheet->getStyle('A2:A1001')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
    $sheet->setCellValueExplicit('A2', '001234', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->fromArray(['Nama Nol', 'nol@example.test', 'auditee'], NULL, 'B2');
    $sheet->setCellValueExplicit('A3', '001235', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('B3', '=CONCAT("Nama", " Formula")');
    $sheet->setCellValue('C3', 'formula@example.test');
    $sheet->setCellValue('D3', 'auditor');
    $sheet->setCellValue('A4', 1236);
    $sheet->fromArray(['Nama Numerik', 'numeric@example.test', 'auditor'], NULL, 'B4');
    (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($path);
    $spreadsheet->disconnectWorksheets();

    $result = (new Akun_import_service())->parse($path);
    akun_import_excel_check(count($result['valid']) === 1, 'Hanya NIP/NIDN teks yang valid boleh dipreview.');
    akun_import_excel_check($result['valid'][0]['identity_number'] === '001234', 'NIP/NIDN teks dengan nol depan harus tidak berubah.');
    akun_import_excel_check(count($result['errors']) === 2, 'Formula dan NIP/NIDN numerik harus menjadi error per baris.');
    akun_import_excel_check($result['errors'][0] === ['row' => 3, 'message' => 'Formula tidak diizinkan.'], 'Formula harus ditolak sebelum nilai hasil formula dipakai.');
    akun_import_excel_check($result['errors'][1] === ['row' => 4, 'message' => 'NIP/NIDN harus diisi sebagai teks.'], 'NIP/NIDN numerik harus ditolak agar nol depan tidak hilang.');
} finally {
    if (isset($spreadsheet)) $spreadsheet->disconnectWorksheets();
    if (is_file($path)) unlink($path);
}

fwrite(STDOUT, "Akun import Excel regression checks passed.\n");
