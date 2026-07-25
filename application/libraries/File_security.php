<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Central file validation, storage, metadata, retention, and delivery policy.
 */
class File_security
{
    const DEFAULT_RETENTION_DAYS = 90;

    /** @var CI_Controller */
    private $CI;

    private $policies = [
        'instrumen' => [
            'extensions' => ['pdf', 'docx', 'xlsx', 'png', 'jpg', 'jpeg'],
            'max_bytes' => 5242880,
            'scope' => 'private',
        ],
        'penetapan' => [
            'extensions' => ['pdf', 'docx', 'xlsx', 'png', 'jpg', 'jpeg'],
            'max_bytes' => 5242880,
            'scope' => 'private',
        ],
        'bukti_auditor' => [
            'extensions' => ['pdf', 'docx', 'xlsx', 'png', 'jpg', 'jpeg'],
            'max_bytes' => 5242880,
            'scope' => 'private',
        ],
        'notulen' => [
            'extensions' => ['pdf', 'docx', 'xlsx', 'png', 'jpg', 'jpeg'],
            'max_bytes' => 10485760,
            'scope' => 'private',
        ],
        'daftar_hadir' => [
            'extensions' => ['pdf', 'docx', 'xlsx', 'png', 'jpg', 'jpeg'],
            'max_bytes' => 10485760,
            'scope' => 'private',
        ],
        'user_photos' => [
            'extensions' => ['png', 'jpg', 'jpeg'],
            'max_bytes' => 2097152,
            'scope' => 'private',
        ],
        'tmp' => [
            'extensions' => ['xlsx'],
            'max_bytes' => 2097152,
            'scope' => 'temporary',
        ],
        'profil' => [
            'extensions' => ['png', 'jpg', 'jpeg'],
            'max_bytes' => 4194304,
            'scope' => 'public',
        ],
    ];

    private $forbidden_extensions = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
        'cgi', 'pl', 'py', 'rb', 'sh', 'bash', 'exe', 'dll', 'com', 'msi',
        'bat', 'cmd', 'ps1', 'vbs', 'vbe', 'js', 'jse', 'html', 'htm',
        'svg', 'xml', 'jar', 'apk', 'app', 'scr',
    ];

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('File_asset_model');
        $this->CI->load->library('audit_logger');
    }

    public function schema_ready()
    {
        return $this->CI->File_asset_model->schema_ready();
    }

    public function upload($field, $category, $owner_type, $owner_id, $actor_user_id)
    {
        $policy = $this->policy($category);
        if ($policy === NULL) {
            return $this->failure($category, $owner_type, $owner_id, $actor_user_id, 'invalid_category', 'Kategori file tidak didukung.');
        }

        if (!$this->schema_ready()) {
            return $this->failure($category, $owner_type, $owner_id, $actor_user_id, 'schema_missing', 'Database file belum siap. Jalankan migration 013.');
        }

        if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
            return $this->failure($category, $owner_type, $owner_id, $actor_user_id, 'missing_upload', 'File wajib dipilih.');
        }

        $file = $_FILES[$field];
        $error = isset($file['error']) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;
        if ($error !== UPLOAD_ERR_OK) {
            return $this->failure(
                $category,
                $owner_type,
                $owner_id,
                $actor_user_id,
                'upload_error_' . $error,
                $this->upload_error_message($error)
            );
        }

        $tmp_name = isset($file['tmp_name']) ? (string) $file['tmp_name'] : '';
        $size = isset($file['size']) ? (int) $file['size'] : 0;
        if ($tmp_name === '' || !is_uploaded_file($tmp_name)) {
            return $this->failure($category, $owner_type, $owner_id, $actor_user_id, 'invalid_upload_source', 'Sumber upload tidak valid.');
        }
        if ($size < 1 || $size > (int) $policy['max_bytes']) {
            $limit = (int) ceil($policy['max_bytes'] / 1048576);
            return $this->failure($category, $owner_type, $owner_id, $actor_user_id, 'invalid_size', 'Ukuran file harus lebih dari 0 dan maksimal ' . $limit . ' MiB.');
        }

        $raw_name = isset($file['name']) ? (string) $file['name'] : '';
        $original_name = $this->safe_original_name($raw_name);
        $extension = strtolower((string) pathinfo($original_name, PATHINFO_EXTENSION));
        if (!$this->safe_extension($original_name, $extension, $policy['extensions'])) {
            return $this->failure(
                $category,
                $owner_type,
                $owner_id,
                $actor_user_id,
                'extension_blocked',
                'Jenis file tidak diizinkan. Gunakan: ' . strtoupper(implode(', ', $policy['extensions'])) . '.'
            );
        }

        $content = $this->validate_content($tmp_name, $extension);
        if (!$content['valid']) {
            return $this->failure($category, $owner_type, $owner_id, $actor_user_id, $content['reason'], 'Isi file tidak sesuai dengan ekstensi atau format yang diizinkan.');
        }

        $stored_extension = $extension === 'jpeg' ? 'jpg' : $extension;
        try {
            $stored_name = bin2hex(random_bytes(24)) . '.' . $stored_extension;
        } catch (Exception $exception) {
            log_message('error', 'Random file name generation failed: ' . $exception->getMessage());
            return $this->failure($category, $owner_type, $owner_id, $actor_user_id, 'random_failed', 'File gagal disimpan.');
        }

        $directory = $this->storage_directory($category, $policy['scope']);
        $mode = $policy['scope'] === 'public' ? 0755 : 0700;
        if (!is_dir($directory) && !mkdir($directory, $mode, TRUE) && !is_dir($directory)) {
            return $this->failure($category, $owner_type, $owner_id, $actor_user_id, 'storage_unavailable', 'Penyimpanan file tidak tersedia.');
        }

        $path = $directory . $stored_name;
        if (!move_uploaded_file($tmp_name, $path)) {
            return $this->failure($category, $owner_type, $owner_id, $actor_user_id, 'move_failed', 'File gagal disimpan.');
        }
        @chmod($path, $policy['scope'] === 'public' ? 0644 : 0600);

        $checksum = hash_file('sha256', $path);
        $actual_size = filesize($path);
        if ($checksum === FALSE || $actual_size === FALSE) {
            @unlink($path);
            return $this->failure($category, $owner_type, $owner_id, $actor_user_id, 'checksum_failed', 'File gagal diverifikasi.');
        }

        $asset_id = $this->CI->File_asset_model->create_asset([
            'category' => $category,
            'owner_type' => $this->normalize_token($owner_type, 40),
            'owner_id' => (int) $owner_id > 0 ? (int) $owner_id : NULL,
            'storage_scope' => $policy['scope'],
            'stored_name' => $stored_name,
            'original_name' => $original_name,
            'extension' => $stored_extension,
            'mime_type' => $content['mime'],
            'size_bytes' => (int) $actual_size,
            'sha256' => $checksum,
            'status' => 'active',
            'is_legacy' => 0,
            'uploaded_by' => (int) $actor_user_id > 0 ? (int) $actor_user_id : NULL,
        ]);

        if ($asset_id === NULL) {
            @unlink($path);
            return $this->failure($category, $owner_type, $owner_id, $actor_user_id, 'metadata_failed', 'Metadata file gagal disimpan.');
        }

        $this->event('upload_succeeded', 'success', NULL, $category, $owner_type, $owner_id, $actor_user_id, $asset_id);

        return [
            'success' => TRUE,
            'asset_id' => $asset_id,
            'file_name' => $stored_name,
            'original_name' => $original_name,
            'path' => $path,
            'mime_type' => $content['mime'],
            'size_bytes' => (int) $actual_size,
            'sha256' => $checksum,
            'message' => '',
        ];
    }

    public function download($category, $stored_name, $owner_type, $owner_id, $actor_user_id)
    {
        $resolved = $this->resolve($category, $stored_name, $owner_type, $owner_id, $actor_user_id);
        if ($resolved === NULL) {
            return FALSE;
        }

        $this->event(
            'download_succeeded',
            'success',
            NULL,
            $category,
            $owner_type,
            $owner_id,
            $actor_user_id,
            (int) $resolved['asset']->id
        );

        $this->stream_attachment($resolved['path'], (string) $resolved['asset']->original_name);
        return TRUE;
    }

    public function resolve($category, $stored_name, $owner_type, $owner_id, $actor_user_id = 0)
    {
        if (!$this->schema_ready() || $this->policy($category) === NULL) {
            $this->event('download_blocked', 'blocked', 'schema_or_category', $category, $owner_type, $owner_id, $actor_user_id);
            return NULL;
        }

        $stored_name = (string) $stored_name;
        if (!ami_valid_stored_name($stored_name)) {
            $this->event('download_blocked', 'blocked', 'path_traversal', $category, $owner_type, $owner_id, $actor_user_id);
            return NULL;
        }

        $asset = $this->CI->File_asset_model->find_by_storage($category, $stored_name);
        if ($asset && $asset->status !== 'active') {
            $this->event('download_blocked', 'blocked', 'inactive_asset', $category, $owner_type, $owner_id, $actor_user_id, (int) $asset->id);
            return NULL;
        }
        if ($asset
            && ((string) $asset->owner_type !== $this->normalize_token($owner_type, 40)
                || ($asset->owner_id !== NULL
                    && (int) $owner_id > 0
                    && (int) $asset->owner_id !== (int) $owner_id))) {
            $this->event('download_blocked', 'blocked', 'owner_mismatch', $category, $owner_type, $owner_id, $actor_user_id, (int) $asset->id);
            return NULL;
        }

        $path = $this->stored_path($category, $stored_name, $asset);
        if ($path === NULL) {
            $this->event('download_blocked', 'blocked', 'missing_file', $category, $owner_type, $owner_id, $actor_user_id, $asset ? (int) $asset->id : NULL);
            return NULL;
        }

        if (!$asset) {
            $asset = $this->register_legacy($category, $stored_name, $path, $owner_type, $owner_id, $actor_user_id);
            if (!$asset) {
                $this->event('download_blocked', 'blocked', 'legacy_registration_failed', $category, $owner_type, $owner_id, $actor_user_id);
                return NULL;
            }
        }

        $actual_size = filesize($path);
        $actual_hash = hash_file('sha256', $path);
        if ($actual_size === FALSE
            || $actual_hash === FALSE
            || (int) $asset->size_bytes !== (int) $actual_size
            || !hash_equals((string) $asset->sha256, $actual_hash)) {
            $this->event('download_blocked', 'blocked', 'integrity_mismatch', $category, $owner_type, $owner_id, $actor_user_id, (int) $asset->id);
            return NULL;
        }

        return ['asset' => $asset, 'path' => $path];
    }

    public function record_read($asset, $category, $owner_type, $owner_id, $actor_user_id)
    {
        $this->event(
            'download_succeeded',
            'success',
            NULL,
            $category,
            $owner_type,
            $owner_id,
            $actor_user_id,
            $asset ? (int) $asset->id : NULL
        );
    }

    public function retire($category, $stored_name, $owner_type, $owner_id, $actor_user_id, $reason = 'replaced')
    {
        if ((string) $stored_name === '' || !$this->schema_ready()) {
            return TRUE;
        }

        $resolved = $this->resolve($category, $stored_name, $owner_type, $owner_id, $actor_user_id);
        if ($resolved === NULL) {
            return FALSE;
        }

        $asset = $resolved['asset'];
        $retention_until = date('Y-m-d H:i:s', time() + self::DEFAULT_RETENTION_DAYS * 86400);
        $retired = $this->CI->File_asset_model->retire((int) $asset->id, (int) $actor_user_id, $retention_until);
        if ($retired) {
            $this->event('file_retired', 'success', $reason, $category, $owner_type, $owner_id, $actor_user_id, (int) $asset->id);
        }

        return $retired;
    }

    public function discard_temporary($stored_name, $owner_type, $owner_id, $actor_user_id)
    {
        $resolved = $this->resolve('tmp', $stored_name, $owner_type, $owner_id, $actor_user_id);
        if ($resolved === NULL) {
            return FALSE;
        }

        $asset = $resolved['asset'];
        $path = $resolved['path'];
        $retired = $this->CI->File_asset_model->retire(
            (int) $asset->id,
            (int) $actor_user_id,
            date('Y-m-d H:i:s')
        );
        $removed = !is_file($path) || @unlink($path);
        if ($retired && $removed) {
            $this->CI->File_asset_model->mark_purged((int) $asset->id);
            $this->event('temporary_file_destroyed', 'success', 'ephemeral_cleanup', 'tmp', $owner_type, $owner_id, $actor_user_id, (int) $asset->id);
        }

        return $retired && $removed;
    }

    public function original_names($category, array $stored_names)
    {
        $names = $this->CI->File_asset_model->active_names($category, $stored_names);
        foreach ($stored_names as $stored_name) {
            $stored_name = (string) $stored_name;
            if ($stored_name !== '' && !isset($names[$stored_name])) {
                $names[$stored_name] = $this->safe_original_name($stored_name);
            }
        }

        return $names;
    }

    public function original_name($category, $stored_name)
    {
        $names = $this->original_names($category, [(string) $stored_name]);
        return isset($names[(string) $stored_name])
            ? $names[(string) $stored_name]
            : '';
    }

    public function purge_expired($limit = 100)
    {
        if (!$this->schema_ready()) {
            return 0;
        }

        $purged = 0;
        foreach ($this->CI->File_asset_model->expired($limit) as $asset) {
            $path = $this->stored_path((string) $asset->category, (string) $asset->stored_name, $asset);
            if ($path !== NULL && is_file($path) && !@unlink($path)) {
                $this->event('purge_blocked', 'blocked', 'unlink_failed', $asset->category, $asset->owner_type, $asset->owner_id, 0, (int) $asset->id);
                continue;
            }

            if ($this->CI->File_asset_model->mark_purged((int) $asset->id)) {
                $purged++;
                $this->event('purge_succeeded', 'success', 'retention_expired', $asset->category, $asset->owner_type, $asset->owner_id, 0, (int) $asset->id);
            }
        }

        return $purged;
    }

    private function register_legacy($category, $stored_name, $path, $owner_type, $owner_id, $actor_user_id)
    {
        $policy = $this->policy($category);
        $extension = strtolower((string) pathinfo($stored_name, PATHINFO_EXTENSION));
        if ($policy === NULL || !$this->safe_extension($stored_name, $extension, $policy['extensions'])) {
            return NULL;
        }

        $content = $this->validate_content($path, $extension);
        if (!$content['valid']) {
            return NULL;
        }

        $size = filesize($path);
        $checksum = hash_file('sha256', $path);
        if ($size === FALSE || $checksum === FALSE) {
            return NULL;
        }

        $scope = $this->path_is_inside($path, FCPATH) ? 'public' : 'private';
        $asset_id = $this->CI->File_asset_model->create_asset([
            'category' => $category,
            'owner_type' => $this->normalize_token($owner_type, 40),
            'owner_id' => (int) $owner_id > 0 ? (int) $owner_id : NULL,
            'storage_scope' => $scope,
            'stored_name' => $stored_name,
            'original_name' => $this->safe_original_name($stored_name),
            'extension' => substr($extension, 0, 16),
            'mime_type' => $content['mime'],
            'size_bytes' => (int) $size,
            'sha256' => $checksum,
            'status' => 'active',
            'is_legacy' => 1,
            'uploaded_by' => NULL,
        ]);

        if ($asset_id === NULL) {
            return $this->CI->File_asset_model->find_by_storage($category, $stored_name);
        }

        $this->event('legacy_registered', 'success', 'lazy_registration', $category, $owner_type, $owner_id, $actor_user_id, $asset_id);
        return $this->CI->File_asset_model->find_by_storage($category, $stored_name);
    }

    private function stored_path($category, $stored_name, $asset = NULL)
    {
        $policy = $this->policy($category);
        if ($policy === NULL) {
            return NULL;
        }

        $scope = $asset && isset($asset->storage_scope) ? (string) $asset->storage_scope : $policy['scope'];
        if ($scope === 'public') {
            $path = $this->storage_directory($category, 'public') . $stored_name;
            return is_file($path) ? $path : NULL;
        }

        return private_storage_path($category, $stored_name);
    }

    private function storage_directory($category, $scope)
    {
        if ($scope === 'public') {
            return FCPATH
                . 'uploads'
                . DIRECTORY_SEPARATOR
                . ($category === 'profil' ? 'profil' : $category)
                . DIRECTORY_SEPARATOR;
        }

        return private_storage_dir($category);
    }

    private function validate_content($path, $extension)
    {
        $mime = $this->detect_mime($path);

        if ($extension === 'pdf') {
            $prefix = $this->file_prefix($path, 5);
            return [
                'valid' => $mime === 'application/pdf'
                    && $prefix === '%PDF-'
                    && !$this->pdf_has_active_content($path),
                'mime' => 'application/pdf',
                'reason' => 'mime_mismatch',
            ];
        }

        if ($extension === 'png' || $extension === 'jpg' || $extension === 'jpeg') {
            $image = @getimagesize($path);
            $expected = $extension === 'png' ? 'image/png' : 'image/jpeg';
            return [
                'valid' => is_array($image)
                    && isset($image['mime'])
                    && $image['mime'] === $expected
                    && $mime === $expected
                    && $this->image_has_clean_ending($path, $extension),
                'mime' => $expected,
                'reason' => 'invalid_image',
            ];
        }

        if ($extension === 'docx' || $extension === 'xlsx') {
            return $this->validate_openxml($path, $extension);
        }

        return ['valid' => FALSE, 'mime' => 'application/octet-stream', 'reason' => 'unsupported_content'];
    }

    private function validate_openxml($path, $extension)
    {
        if (!class_exists('ZipArchive')) {
            return ['valid' => FALSE, 'mime' => 'application/octet-stream', 'reason' => 'zip_extension_missing'];
        }

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::RDONLY) !== TRUE) {
            return ['valid' => FALSE, 'mime' => 'application/octet-stream', 'reason' => 'invalid_openxml'];
        }

        $required = $extension === 'docx' ? 'word/document.xml' : 'xl/workbook.xml';
        $valid = $zip->locateName('[Content_Types].xml', ZipArchive::FL_NOCASE) !== FALSE
            && $zip->locateName($required, ZipArchive::FL_NOCASE) !== FALSE
            && $zip->numFiles > 0
            && $zip->numFiles <= 5000;
        $total_uncompressed = 0;

        for ($index = 0; $valid && $index < $zip->numFiles; $index++) {
            $stat = $zip->statIndex($index);
            if (!is_array($stat) || !isset($stat['name'], $stat['size'])) {
                $valid = FALSE;
                break;
            }

            $entry = str_replace('\\', '/', (string) $stat['name']);
            $lower = strtolower($entry);
            $segments = explode('/', $entry);
            $total_uncompressed += (int) $stat['size'];

            if ($entry === ''
                || $entry[0] === '/'
                || in_array('..', $segments, TRUE)
                || strpos($entry, "\0") !== FALSE
                || strpos($lower, 'vbaproject.bin') !== FALSE
                || strpos($lower, '/activex/') !== FALSE
                || strpos($lower, '/embeddings/') !== FALSE
                || preg_match('/\.(?:php\d*|phtml|phar|exe|dll|com|msi|bat|cmd|ps1|vbs|js|html?|svg)$/i', $lower)) {
                $valid = FALSE;
            }

            if ($valid && substr($lower, -5) === '.rels') {
                $relationships = $zip->getFromIndex($index, 1048576);
                if ($relationships === FALSE
                    || stripos($relationships, 'TargetMode="External"') !== FALSE
                    || stripos($relationships, "TargetMode='External'") !== FALSE) {
                    $valid = FALSE;
                }
            }
        }

        $zip->close();
        if ($total_uncompressed > 100 * 1024 * 1024) {
            $valid = FALSE;
        }

        return [
            'valid' => $valid,
            'mime' => $extension === 'docx'
                ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'reason' => 'invalid_openxml',
        ];
    }

    private function safe_extension($original_name, $extension, array $allowed)
    {
        if ($extension === '' || !in_array($extension, $allowed, TRUE)) {
            return FALSE;
        }

        $parts = explode('.', strtolower($original_name));
        array_pop($parts);
        foreach ($parts as $part) {
            if (in_array($part, $this->forbidden_extensions, TRUE)) {
                return FALSE;
            }
        }

        return !in_array($extension, $this->forbidden_extensions, TRUE);
    }

    private function safe_original_name($name)
    {
        $name = basename(str_replace('\\', '/', (string) $name));
        $name = preg_replace('/[\x00-\x1F\x7F]+/', '_', $name);
        $name = trim((string) $name, " .\t\n\r\0\x0B");
        if ($name === '') {
            $name = 'file';
        }

        return strlen($name) > 240 ? substr($name, 0, 240) : $name;
    }

    private function stream_attachment($path, $original_name)
    {
        $size = filesize($path);
        $fallback = preg_replace('/[^A-Za-z0-9._-]+/', '_', $original_name);
        $fallback = trim((string) $fallback, '._');
        if ($fallback === '') {
            $fallback = 'download';
        }

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        header('Content-Type: application/octet-stream');
        header(
            'Content-Disposition: attachment; filename="' . $fallback
                . '"; filename*=UTF-8\'\'' . rawurlencode($original_name)
        );
        header('X-Content-Type-Options: nosniff');
        header("Content-Security-Policy: sandbox; default-src 'none'");
        header('Cache-Control: private, no-transform, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . (string) $size);

        $handle = fopen($path, 'rb');
        if ($handle === FALSE) {
            exit;
        }
        while (!feof($handle)) {
            $chunk = fread($handle, 1048576);
            if ($chunk === FALSE) {
                break;
            }
            echo $chunk;
        }
        fclose($handle);
        exit;
    }

    private function failure($category, $owner_type, $owner_id, $actor_user_id, $reason, $message)
    {
        $this->event('upload_blocked', 'blocked', $reason, $category, $owner_type, $owner_id, $actor_user_id);
        return [
            'success' => FALSE,
            'asset_id' => NULL,
            'file_name' => NULL,
            'original_name' => NULL,
            'path' => NULL,
            'message' => $message,
        ];
    }

    private function event($event_type, $outcome, $reason, $category, $owner_type, $owner_id, $actor_user_id, $asset_id = NULL)
    {
        $message = sprintf(
            'file_event=%s outcome=%s category=%s owner=%s:%d actor=%d request_id=%s reason=%s',
            $this->normalize_token($event_type, 40),
            $this->normalize_token($outcome, 16),
            $this->normalize_token($category, 40),
            $this->normalize_token($owner_type, 40),
            (int) $owner_id,
            (int) $actor_user_id,
            ami_request_id(),
            $this->normalize_token($reason, 64)
        );
        log_message($outcome === 'blocked' ? 'error' : 'info', $message);

        if (!$this->schema_ready()) {
            return FALSE;
        }

        $recorded = $this->CI->File_asset_model->record_event([
            'file_asset_id' => $asset_id !== NULL ? (int) $asset_id : NULL,
            'actor_user_id' => (int) $actor_user_id > 0 ? (int) $actor_user_id : NULL,
            'event_type' => $this->normalize_token($event_type, 40),
            'outcome' => $this->normalize_token($outcome, 16),
            'reason' => $reason !== NULL ? $this->normalize_token($reason, 64) : NULL,
            'category' => $this->normalize_token($category, 40),
            'owner_type' => $this->normalize_token($owner_type, 40),
            'owner_id' => (int) $owner_id > 0 ? (int) $owner_id : NULL,
            'request_id' => ami_request_id(),
        ]);

        $actions = [
            'upload_succeeded' => 'upload',
            'upload_blocked' => 'upload',
            'download_succeeded' => 'download',
            'download_blocked' => 'download',
            'file_retired' => 'delete',
            'temporary_file_destroyed' => 'delete',
            'purge_succeeded' => 'purge',
            'purge_blocked' => 'purge',
            'legacy_registered' => 'register',
        ];
        $action = isset($actions[$event_type]) ? $actions[$event_type] : 'file_event';
        $before = in_array($event_type, ['file_retired', 'temporary_file_destroyed'], TRUE)
            ? ['status' => 'active']
            : NULL;
        $after = NULL;
        if ($event_type === 'upload_succeeded' || $event_type === 'legacy_registered') {
            $after = ['status' => 'active'];
        } elseif (in_array($event_type, ['file_retired', 'temporary_file_destroyed'], TRUE)) {
            $after = ['status' => 'deleted'];
        } elseif ($event_type === 'purge_succeeded') {
            $after = ['status' => 'purged'];
        }

        $audit_id = $this->CI->audit_logger->record(
            $event_type,
            'file_asset',
            $asset_id !== NULL ? (int) $asset_id : $owner_type . ':' . (int) $owner_id,
            $action,
            $before,
            $after,
            [
                'reason_code' => $reason,
                'category' => $category,
                'file_asset_id' => $asset_id,
            ],
            (int) $actor_user_id,
            $outcome === 'blocked' ? 'blocked' : 'success'
        );

        return $recorded && $audit_id !== NULL;
    }

    private function policy($category)
    {
        return isset($this->policies[$category]) ? $this->policies[$category] : NULL;
    }

    private function detect_mime($path)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $path) : FALSE;
        if ($finfo) {
            finfo_close($finfo);
        }

        return $mime !== FALSE ? strtolower(trim((string) $mime)) : '';
    }

    private function file_prefix($path, $length)
    {
        $handle = @fopen($path, 'rb');
        if ($handle === FALSE) {
            return '';
        }
        $prefix = fread($handle, (int) $length);
        fclose($handle);
        return $prefix !== FALSE ? $prefix : '';
    }

    private function pdf_has_active_content($path)
    {
        $contents = @file_get_contents($path);
        if ($contents === FALSE) {
            return TRUE;
        }

        return preg_match(
            '/\/(?:JavaScript|JS|Launch|EmbeddedFile|OpenAction|AA)\b/i',
            $contents
        ) === 1;
    }

    private function image_has_clean_ending($path, $extension)
    {
        $contents = @file_get_contents($path);
        if ($contents === FALSE) {
            return FALSE;
        }

        if ($extension === 'png') {
            return substr($contents, -12) === "\x00\x00\x00\x00IEND\xAE\x42\x60\x82";
        }

        return substr($contents, -2) === "\xFF\xD9";
    }

    private function upload_error_message($error)
    {
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            return 'Ukuran file melewati batas server.';
        }
        if ($error === UPLOAD_ERR_NO_FILE) {
            return 'File wajib dipilih.';
        }
        return 'Upload file tidak lengkap atau gagal.';
    }

    private function normalize_token($value, $length)
    {
        $value = preg_replace('/[^a-zA-Z0-9_.:-]+/', '_', (string) $value);
        return substr((string) $value, 0, (int) $length);
    }

    private function path_is_inside($path, $root)
    {
        $path = realpath($path);
        $root = realpath($root);
        if ($path === FALSE || $root === FALSE) {
            return FALSE;
        }

        $path = strtolower(str_replace('\\', '/', $path));
        $root = strtolower(rtrim(str_replace('\\', '/', $root), '/') . '/');
        return strpos($path, $root) === 0;
    }
}
