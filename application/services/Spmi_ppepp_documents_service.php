<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_ppepp_documents_service
{
    protected $ci;
    protected $model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Spmi_ppepp_documents_model');
        $this->ci->config->load('spmi_ppepp', TRUE);
        $this->model = $this->ci->Spmi_ppepp_documents_model;
    }

    public function documents($stage, $year)
    {
        $stage = $this->valid_stage($stage);
        $year = $this->valid_year($year);
        if ($stage === NULL || $year === NULL) return [];

        return $this->model->documents($stage, $year);
    }

    public function years()
    {
        return $this->model->years();
    }

    public function document($id)
    {
        return $this->model->document($id);
    }

    public function penetapan_core_counts($year)
    {
        $year = $this->valid_year($year);
        if ($year === NULL) return [];

        $counts = [];
        foreach ($this->penetapan_core_categories() as $category) $counts[$category] = 0;
        foreach ($this->model->penetapan_core_counts($year, array_keys($counts)) as $row) $counts[$row->category] = (int) $row->total;
        return $counts;
    }

    public function create($data, $file, $user_id)
    {
        $payload = $this->payload($data, $user_id, FALSE);
        if (!$payload['success']) return $payload;

        return $this->persist_create($payload['data'], $file);
    }

    public function update($id, $data, $file, $user_id)
    {
        $payload = $this->payload($data, $user_id, TRUE);
        if (!$payload['success']) return $payload;

        return $this->persist_update($id, $payload['data'], $file);
    }

    public function delete($id)
    {
        $this->ci->db->trans_begin();
        $document = $this->model->document($id, TRUE);
        if (!$document) return $this->rollback('Dokumen PPEPP tidak ditemukan.');

        if (!$this->model->delete_document($id)) return $this->rollback('Dokumen PPEPP gagal dihapus.');

        $stored_name = $document->stored_name;
        $result = $this->finish(['success' => TRUE, 'message' => 'Dokumen PPEPP berhasil dihapus.']);
        if ($result['success'] && $stored_name) delete_private_file('ppepp_documents', $stored_name);
        return $result;
    }

    public function download($id)
    {
        $document = $this->model->document($id);
        if (!$document) return NULL;

        if ($document->stored_name) {
            $path = private_storage_path('ppepp_documents', $document->stored_name);
            return $path && is_file($path) ? [
                'backend' => 'local',
                'path' => $path,
                'mime' => $document->mime_type,
                'name' => $document->original_name,
                'size' => (int) $document->file_size,
            ] : NULL;
        }

        return $document->external_url ? ['backend' => 'url', 'url' => $document->external_url] : NULL;
    }

    protected function persist_create($data, $file)
    {
        $saved = $this->save_upload($file);
        if (!$saved['success']) return $saved;
        $data = array_merge($data, $saved['data']);
        if (!$this->has_document_source($data)) {
            $this->cleanup_saved($saved);
            return ['success' => FALSE, 'message' => 'Dokumen PPEPP wajib memiliki file atau URL.'];
        }

        $this->ci->db->trans_begin();
        $id = $this->model->insert_document($data);
        if (!$id) {
            $this->ci->db->trans_rollback();
            $this->cleanup_saved($saved);
            return ['success' => FALSE, 'message' => 'Dokumen PPEPP gagal disimpan.'];
        }

        $result = $this->finish(['success' => TRUE, 'message' => 'Dokumen PPEPP berhasil disimpan.', 'id' => $id]);
        if (!$result['success']) $this->cleanup_saved($saved);
        return $result;
    }

    protected function persist_update($id, $data, $file)
    {
        $saved = $this->save_upload($file);
        if (!$saved['success']) return $saved;

        $this->ci->db->trans_begin();
        $document = $this->model->document($id, TRUE);
        if (!$document) {
            $this->ci->db->trans_rollback();
            $this->cleanup_saved($saved);
            return ['success' => FALSE, 'message' => 'Dokumen PPEPP tidak ditemukan.'];
        }

        $old_stored_name = $document->stored_name;
        $next = array_merge($data, $saved['data']);
        $candidate = [
            'stored_name' => array_key_exists('stored_name', $next) ? $next['stored_name'] : $document->stored_name,
            'external_url' => $next['external_url'],
        ];
        if (!$this->has_document_source($candidate)) {
            $this->ci->db->trans_rollback();
            $this->cleanup_saved($saved);
            return ['success' => FALSE, 'message' => 'Dokumen PPEPP wajib memiliki file atau URL.'];
        }

        if (!$this->model->update_document($id, $next)) {
            $this->ci->db->trans_rollback();
            $this->cleanup_saved($saved);
            return ['success' => FALSE, 'message' => 'Dokumen PPEPP gagal diperbarui.'];
        }

        $result = $this->finish(['success' => TRUE, 'message' => 'Dokumen PPEPP berhasil diperbarui.', 'id' => (int) $id]);
        if ($result['success']) {
            if (isset($saved['data']['stored_name']) && $old_stored_name && $old_stored_name !== $saved['data']['stored_name']) delete_private_file('ppepp_documents', $old_stored_name);
        } else {
            $this->cleanup_saved($saved);
        }
        return $result;
    }

    protected function payload($data, $user_id, $update)
    {
        $stage = $this->valid_stage(isset($data['stage']) ? $data['stage'] : '');
        $category = $stage === NULL ? NULL : $this->valid_category($stage, isset($data['category']) ? $data['category'] : '');
        $year = $this->valid_year(isset($data['period_year']) ? $data['period_year'] : '');
        $title = $this->text($data, 'title', 200, TRUE);
        $description = $this->text($data, 'description', 10000, FALSE);
        $document_date = $this->date($data, 'document_date');
        $external_url = $this->external_url($data);

        if ($stage === NULL || $category === NULL) return ['success' => FALSE, 'message' => 'Tahap dan kategori PPEPP tidak valid.'];
        if ($year === NULL) return ['success' => FALSE, 'message' => 'Tahun dokumen PPEPP tidak valid.'];
        if ($title === FALSE) return ['success' => FALSE, 'message' => 'Judul dokumen PPEPP wajib diisi dan maksimal 200 karakter.'];
        if ($description === FALSE) return ['success' => FALSE, 'message' => 'Deskripsi dokumen PPEPP terlalu panjang.'];
        if ($document_date === FALSE) return ['success' => FALSE, 'message' => 'Tanggal dokumen PPEPP tidak valid.'];
        if ($external_url === FALSE) return ['success' => FALSE, 'message' => 'URL dokumen PPEPP harus HTTP atau HTTPS yang valid.'];

        $payload = [
            'stage' => $stage,
            'category' => $category,
            'period_year' => $year,
            'title' => $title,
            'description' => $description,
            'document_date' => $document_date,
            'external_url' => $external_url,
        ];
        $payload[$update ? 'updated_by' : 'uploaded_by'] = (int) $user_id;
        return ['success' => TRUE, 'data' => $payload];
    }

    protected function save_upload($file)
    {
        if (!$this->has_upload($file)) return ['success' => TRUE, 'data' => []];

        $validated = $this->validate_file($file);
        if (!$validated['success']) return $validated;

        $dir = private_storage_dir('ppepp_documents');
        if (!is_dir($dir) && !mkdir($dir, 0700, TRUE) && !is_dir($dir)) return ['success' => FALSE, 'message' => 'File dokumen PPEPP gagal disimpan.'];
        @chmod($dir, 0700);

        try {
            $name = bin2hex(random_bytes(24)) . '.' . $validated['extension'];
        } catch (Exception $e) {
            return ['success' => FALSE, 'message' => 'File dokumen PPEPP gagal disimpan.'];
        }

        $path = $dir . $name;
        if (!move_uploaded_file($file['tmp_name'], $path)) return ['success' => FALSE, 'message' => 'File dokumen PPEPP gagal disimpan.'];
        @chmod($path, 0600);

        return ['success' => TRUE, 'path' => $path, 'data' => [
            'stored_name' => $name,
            'original_name' => basename($file['name']),
            'mime_type' => $validated['mime'],
            'file_size' => (int) $file['size'],
        ]];
    }

    protected function validate_file($file)
    {
        $limit_bytes = $this->upload_limit_bytes('ppepp_documents');
        $limit_mib = $this->limit_mib($limit_bytes);
        $message = 'File dokumen PPEPP harus PDF, Word, Excel, atau PowerPoint maksimal ' . $limit_mib . ' MiB.';
        if (!is_array($file) || !isset($file['error'], $file['tmp_name'], $file['size'], $file['name']) || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name']) || (int) $file['size'] > $limit_bytes) {
            return ['success' => FALSE, 'message' => $message];
        }

        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $allowed = $this->allowed_mimes();
        if (!isset($allowed[$extension])) return ['success' => FALSE, 'message' => $message];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : FALSE;
        if ($finfo) finfo_close($finfo);
        if ($mime === FALSE || !in_array($mime, $allowed[$extension], TRUE)) return ['success' => FALSE, 'message' => 'Tipe file dokumen PPEPP tidak valid.'];

        // OOXML structure validation: ZIP-reported files must contain Office package markers
        if (in_array($extension, ['docx', 'xlsx', 'pptx'], TRUE) && in_array($mime, ['application/zip'], TRUE)) {
            if (!$this->validate_ooxml_structure($file['tmp_name'], $extension)) {
                return ['success' => FALSE, 'message' => 'File OOXML tidak valid: struktur dokumen Office tidak ditemukan.'];
            }
        }

        // Legacy OLE validation: CDFV2-reported files must have OLE magic bytes
        if (in_array($extension, ['doc', 'xls', 'ppt'], TRUE) && $mime === 'application/CDFV2') {
            if (!$this->validate_ole_magic($file['tmp_name'])) {
                return ['success' => FALSE, 'message' => 'File Office lama tidak valid.'];
            }
        }

        return ['success' => TRUE, 'mime' => $mime, 'extension' => $extension];
    }

    protected function allowed_mimes()
    {
        return [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword', 'application/vnd.ms-word', 'application/x-msword', 'application/CDFV2'],
            'xls' => ['application/vnd.ms-excel', 'application/msexcel', 'application/x-msexcel', 'application/x-ms-excel', 'application/CDFV2'],
            'ppt' => ['application/vnd.ms-powerpoint', 'application/mspowerpoint', 'application/x-mspowerpoint', 'application/CDFV2'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'],
        ];
    }

    protected function has_upload($file)
    {
        return is_array($file) && isset($file['error']) && (int) $file['error'] !== UPLOAD_ERR_NO_FILE;
    }

    public function upload_limit_mib()
    {
        return $this->limit_mib($this->upload_limit_bytes('ppepp_documents'));
    }

    protected function upload_limit_bytes($category)
    {
        require_once APPPATH . 'services/Upload_size_settings_service.php';
        return (int) Upload_size_settings_service::limit_bytes($category);
    }

    protected function limit_mib($bytes)
    {
        return (int) ($bytes / 1024 / 1024);
    }

    protected function has_document_source($data)
    {
        return !empty($data['stored_name']) || !empty($data['external_url']);
    }

    protected function valid_stage($stage)
    {
        $stage = trim((string) $stage);
        return array_key_exists($stage, $this->stages()) ? $stage : NULL;
    }

    protected function valid_category($stage, $category)
    {
        $category = trim((string) $category);
        $categories = $this->categories($stage);
        return array_key_exists($category, $categories) ? $category : NULL;
    }

    protected function valid_year($year)
    {
        if (is_int($year)) $value = $year;
        elseif (is_string($year) && ctype_digit($year)) $value = (int) $year;
        else return NULL;

        return $value >= 2000 && $value <= ((int) date('Y') + 1) ? $value : NULL;
    }

    protected function text($data, $key, $max, $required)
    {
        $value = trim((string) (isset($data[$key]) ? $data[$key] : ''));
        if ($value === '') return $required ? FALSE : NULL;
        return strlen($value) <= $max ? $value : FALSE;
    }

    protected function date($data, $key)
    {
        $value = trim((string) (isset($data[$key]) ? $data[$key] : ''));
        if ($value === '') return NULL;
        $date = DateTime::createFromFormat('!Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value ? $value : FALSE;
    }

    protected function external_url($data)
    {
        $url = trim((string) (isset($data['external_url']) ? $data['external_url'] : ''));
        if ($url === '') return NULL;
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) return FALSE;
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], TRUE) ? $url : FALSE;
    }

    protected function stages()
    {
        return (array) $this->ci->config->item('spmi_ppepp_stages', 'spmi_ppepp');
    }

    protected function categories($stage)
    {
        $categories = (array) $this->ci->config->item('spmi_ppepp_categories', 'spmi_ppepp');
        return isset($categories[$stage]) && is_array($categories[$stage]) ? $categories[$stage] : [];
    }

    protected function penetapan_core_categories()
    {
        return (array) $this->ci->config->item('spmi_ppepp_penetapan_core_categories', 'spmi_ppepp');
    }

    protected function cleanup_saved($saved)
    {
        if (isset($saved['path']) && is_file($saved['path'])) unlink($saved['path']);
    }

    protected function rollback($message)
    {
        $this->ci->db->trans_rollback();
        return ['success' => FALSE, 'message' => $message];
    }

    protected function finish($result)
    {
        $this->ci->db->trans_complete();
        return $this->ci->db->trans_status() ? $result : ['success' => FALSE, 'message' => 'Dokumen PPEPP gagal disimpan.'];
    }

    /**
     * Validate OOXML ZIP structure contains the expected Office package root.
     */
    protected function validate_ooxml_structure($tmp_path, $extension)
    {
        $required = [
            'docx' => 'word/document.xml',
            'xlsx' => 'xl/workbook.xml',
            'pptx' => 'ppt/presentation.xml',
        ];
        $marker = isset($required[$extension]) ? $required[$extension] : NULL;
        if ($marker === NULL) return FALSE;

        $zip = new ZipArchive();
        if ($zip->open($tmp_path) !== TRUE) return FALSE;
        $has_content_types = ($zip->locateName('[Content_Types].xml') !== FALSE);
        $has_marker = ($zip->locateName($marker) !== FALSE);
        $zip->close();

        return $has_content_types && $has_marker;
    }

    /**
     * Validate legacy Office OLE Compound File magic bytes (D0 CF 11 E0 A1 B1 1A E1).
     */
    protected function validate_ole_magic($tmp_path)
    {
        $handle = fopen($tmp_path, 'rb');
        if (!$handle) return FALSE;
        $header = fread($handle, 8);
        fclose($handle);
        // OLE2 Compound Document magic: D0 CF 11 E0 A1 B1 1A E1
        return $header !== FALSE && substr($header, 0, 8) === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";
    }
}
