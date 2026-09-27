<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prodi_staf_import extends Admin_Lpmpi_Controller
{
    const XLSX_MAX_ENTRIES = 100;
    const XLSX_MAX_UNCOMPRESSED_BYTES = 2097152;
    const XLSX_MAX_XML_BYTES = 524288;
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['form', 'url']);
        require_once APPPATH . 'services/Upload_size_settings_service.php';
        require_once APPPATH . 'services/Prodi_staf_import_service.php';
        $this->service = new Prodi_staf_import_service();
    }

    public function index()
    {
        $this->purge();
        $this->load->view('lpmpi/prodi_staf_import/index', ['title' => 'Import Prodi - AMI', 'page_title' => 'Import Prodi', 'active_menu' => 'master_data_prodi_staf', 'error' => $this->session->flashdata('error'), 'upload_limit_mib' => (int) (Upload_size_settings_service::limit_bytes('spreadsheet_imports') / 1024 / 1024)]);
    }

    public function template()
    {
        require_once FCPATH . 'vendor/autoload.php';
        $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $prodi = $book->getActiveSheet(); $prodi->setTitle('Prodi');
        foreach (Prodi_staf_import_service::PRODI_HEADERS as $index => $header) $prodi->setCellValueExplicitByColumnAndRow($index + 1, 1, $header, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        while (ob_get_level() > 0) @ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); header('Content-Disposition: attachment; filename="import_prodi.xlsx"');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save('php://output'); exit;
    }

    public function preview()
    {
        $this->require_post(); $this->purge();
        if (!isset($_FILES['import_file']) || !is_uploaded_file($_FILES['import_file']['tmp_name'])) return $this->fail('File upload tidak valid.');
        $file = $_FILES['import_file']; $limit = Upload_size_settings_service::limit_bytes('spreadsheet_imports');
        if ((int) $file['error'] !== UPLOAD_ERR_OK || strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'xlsx' || (int) $file['size'] > $limit) return $this->fail('File wajib .xlsx dan maksimal ' . (int) ($limit / 1024 / 1024) . ' MiB.');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'], TRUE)) return $this->fail('MIME file tidak didukung.');
        if (!$this->preflight_xlsx($file['tmp_name'])) return $this->fail('Arsip XLSX tidak valid atau melebihi batas aman.');
        $path = $this->tmp() . 'prodi_import_upload_' . bin2hex(random_bytes(12)) . '.xlsx';
        if (!move_uploaded_file($file['tmp_name'], $path)) return $this->fail('File upload tidak dapat disimpan.');
        try { $result = $this->service->parse($path); } catch (Throwable $exception) { log_message('error', 'Prodi import preview failed: ' . $exception->getMessage()); $result = ['prodi' => [], 'errors' => [['sheet' => '-', 'row' => 0, 'message' => 'Workbook tidak dapat diproses.']], 'total' => 0]; } finally { if (is_file($path)) unlink($path); }
        if (empty($result['prodi']) && empty($result['errors'])) return $this->render_preview($result, '');
        $this->clear(); $basename = 'prodi_import_preview_' . bin2hex(random_bytes(12)) . '.json'; $preview = $this->tmp() . $basename;
        file_put_contents($preview, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), LOCK_EX);
        $token = bin2hex(random_bytes(16));
        $this->session->set_userdata('prodi_import_preview', ['user_id' => $this->_user_id(), 'basename' => $basename, 'sha256' => hash_file('sha256', $preview), 'created_at' => time(), 'token' => $token]);
        $this->render_preview($result, $token);
    }

    public function confirm()
    {
        $this->require_post(); $this->purge(); $preview = $this->preview_payload();
        if ($preview === FALSE) return $this->fail('Preview tidak valid atau sudah kedaluwarsa.');
        $path = $preview['path'];
        $claim = $this->tmp() . 'prodi_import_claim_' . bin2hex(random_bytes(12)) . '.json'; if (!rename($path, $claim)) return $this->fail('Preview sedang tidak tersedia.'); $this->session->unset_userdata('prodi_import_preview');
        try { $result = $this->service->confirm(json_decode(file_get_contents($claim), TRUE, 512, JSON_THROW_ON_ERROR)); } catch (Throwable $exception) { $result = ['success' => FALSE, 'message' => 'Import gagal diproses. Silakan unggah ulang preview.']; } finally { if (is_file($claim)) unlink($claim); }
        if (!$result['success']) return $this->fail($result['message']); $this->session->set_flashdata('success', $result['message']); redirect('lpmpi/master-data-prodi-staf');
    }

    public function errors()
    {
        $this->require_post(); $this->purge(); $preview = $this->preview_payload();
        if ($preview === FALSE) return $this->fail('Preview tidak valid atau sudah kedaluwarsa.');
        try { $rows = $this->service->validation_error_report($preview['payload']); } catch (Throwable $exception) { return $this->fail('Preview tidak valid atau sudah kedaluwarsa.'); }
        while (ob_get_level() > 0) @ob_end_clean();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="prodi_validation_errors.csv"');
        header('Cache-Control: no-store, private');
        header('X-Content-Type-Options: nosniff');
        $output = fopen('php://output', 'wb');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['Sheet', 'Row', 'Message']);
        foreach ($rows as $row) fputcsv($output, $row);
        fclose($output); exit;
    }

    public function cancel() { $this->require_post(); $this->clear(); redirect('lpmpi/master-data-prodi-staf'); }
    private function render_preview($result, $token) { $this->load->view('lpmpi/prodi_staf_import/preview', ['title' => 'Preview Import Prodi - AMI', 'page_title' => 'Preview Import Prodi', 'active_menu' => 'master_data_prodi_staf', 'token' => !empty($result['prodi']) ? $token : '', 'error_preview_token' => $token] + $result); }
    private function tmp() { $dir = private_storage_dir('tmp'); if (!is_dir($dir)) mkdir($dir, 0700, TRUE); return $dir; }
    private function clear() { $meta = $this->session->userdata('prodi_import_preview'); if (is_array($meta) && isset($meta['basename'])) { $path = $this->tmp() . basename($meta['basename']); if (is_file($path)) unlink($path); } $this->session->unset_userdata('prodi_import_preview'); $this->session->unset_userdata('prodi_staf_import_preview'); }
    private function preview_payload()
    {
        $meta = $this->session->userdata('prodi_import_preview'); $basename = is_array($meta) ? (string) ($meta['basename'] ?? '') : '';
        if (!is_array($meta) || $basename !== basename($basename) || !preg_match('/\Aprodi_import_preview_[a-f0-9]{24}\.json\z/', $basename) || !hash_equals((string) ($meta['token'] ?? ''), (string) $this->input->post('token', TRUE)) || (int) ($meta['user_id'] ?? 0) !== $this->_user_id() || time() - (int) ($meta['created_at'] ?? 0) > 1800) return FALSE;
        $path = $this->tmp() . $basename;
        if (!is_file($path) || !hash_equals((string) ($meta['sha256'] ?? ''), hash_file('sha256', $path))) return FALSE;
        try { return ['path' => $path, 'payload' => json_decode(file_get_contents($path), TRUE, 512, JSON_THROW_ON_ERROR)]; } catch (Throwable $exception) { return FALSE; }
    }
    private function purge() { $cutoff = time() - 1800; foreach (['prodi_import_preview_*.json', 'prodi_import_claim_*.json', 'prodi_staf_preview_*.json', 'prodi_staf_claim_*.json'] as $pattern) foreach (glob($this->tmp() . $pattern, GLOB_NOSORT) ?: [] as $path) if (is_file($path) && filemtime($path) < $cutoff) unlink($path); }
    private function fail($message) { $this->session->set_flashdata('error', $message); redirect('lpmpi/prodi-import'); }
    private function require_post() { if ($this->input->method(TRUE) !== 'POST') { show_error('Method tidak diizinkan.', 405, 'Method Not Allowed'); exit; } }
    private function preflight_xlsx($path)
    {
        if (!class_exists('ZipArchive')) return FALSE;
        $zip = new ZipArchive();
        if ($zip->open($path) !== TRUE || $zip->numFiles < 1 || $zip->numFiles > self::XLSX_MAX_ENTRIES) return FALSE;
        $total = 0;
        try {
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $stat = $zip->statIndex($index);
                if (!is_array($stat) || !isset($stat['name'], $stat['size']) || strpos($stat['name'], '\\') !== FALSE || $stat['name'] === '' || $stat['name'][0] === '/' || preg_match('#(^|/)\.\.?(?:/|$)#', $stat['name']) || (isset($stat['encryption_method']) && (int) $stat['encryption_method'] !== 0)) return FALSE;
                $size = (int) $stat['size'];
                if ($size < 0 || $size > self::XLSX_MAX_UNCOMPRESSED_BYTES || (substr($stat['name'], -4) === '.xml' && $size > self::XLSX_MAX_XML_BYTES)) return FALSE;
                $total += $size;
                if ($total > self::XLSX_MAX_UNCOMPRESSED_BYTES) return FALSE;
            }
            return TRUE;
        } finally { $zip->close(); }
    }
}
