<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'services/Upload_size_settings_service.php';

class Organization_logo_service
{
    const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
    ];

    public function preview_urls($profile)
    {
        $local_url = $this->local_url($profile);
        return [
            'local_url' => $local_url,
            'fallback_url' => $profile && !empty($profile->logo_url) ? (string) $profile->logo_url : base_url('assets/img/logo-2.png'),
        ];
    }

    public function public_url($profile)
    {
        $preview = $this->preview_urls($profile);
        return $preview['local_url'] !== '' ? $preview['local_url'] : $preview['fallback_url'];
    }

    public function limit_bytes()
    {
        return Upload_size_settings_service::limit_bytes('institution_logo');
    }

    public function store_candidate($upload)
    {
        if (!$this->has_upload($upload)) {
            return ['success' => TRUE, 'file_name' => NULL, 'message' => ''];
        }

        if ((int) $upload['error'] !== UPLOAD_ERR_OK) {
            return ['success' => FALSE, 'file_name' => NULL, 'message' => 'Upload logo gagal. Pilih file gambar JPG, PNG, atau GIF yang valid.'];
        }
        if (empty($upload['tmp_name']) || !is_uploaded_file($upload['tmp_name'])) {
            return ['success' => FALSE, 'file_name' => NULL, 'message' => 'Upload logo tidak valid.'];
        }

        $size = isset($upload['size']) ? (int) $upload['size'] : 0;
        $limit = $this->limit_bytes();
        if ($size <= 0) {
            return ['success' => FALSE, 'file_name' => NULL, 'message' => 'File logo tidak boleh kosong.'];
        }
        if ($size > $limit) {
            return ['success' => FALSE, 'file_name' => NULL, 'message' => 'Ukuran logo melebihi batas ' . (int) floor($limit / 1024 / 1024) . ' MiB.'];
        }

        $mime = $this->detected_mime($upload['tmp_name']);
        if (!isset(self::MIME_EXTENSIONS[$mime])) {
            return ['success' => FALSE, 'file_name' => NULL, 'message' => 'Format logo harus JPG, PNG, atau GIF.'];
        }
        if (@getimagesize($upload['tmp_name']) === FALSE) {
            return ['success' => FALSE, 'file_name' => NULL, 'message' => 'File logo bukan gambar yang valid.'];
        }

        $directory = $this->upload_dir();
        if (!is_dir($directory) && !mkdir($directory, 0755, TRUE)) {
            return ['success' => FALSE, 'file_name' => NULL, 'message' => 'Folder upload logo tidak dapat dibuat.'];
        }
        if (!is_dir($directory) || !is_writable($directory)) {
            return ['success' => FALSE, 'file_name' => NULL, 'message' => 'Folder upload logo tidak dapat ditulis.'];
        }

        try {
            $file_name = 'logo_lembaga_' . bin2hex(random_bytes(16)) . '.' . self::MIME_EXTENSIONS[$mime];
        } catch (Throwable $exception) {
            return ['success' => FALSE, 'file_name' => NULL, 'message' => 'Nama file logo gagal dibuat.'];
        }

        if (!move_uploaded_file($upload['tmp_name'], $directory . $file_name)) {
            return ['success' => FALSE, 'file_name' => NULL, 'message' => 'Logo gagal disimpan ke folder upload.'];
        }
        @chmod($directory . $file_name, 0644);

        return ['success' => TRUE, 'file_name' => $file_name, 'message' => ''];
    }

    public function delete_local($file_name)
    {
        $file_name = basename((string) $file_name);
        if ($file_name === '') return;
        if (function_exists('delete_public_upload')) {
            delete_public_upload('profil', $file_name);
            return;
        }
        $path = $this->upload_dir() . $file_name;
        if (is_file($path)) @unlink($path);
    }

    protected function has_upload($upload)
    {
        return is_array($upload) && isset($upload['error']) && (int) $upload['error'] !== UPLOAD_ERR_NO_FILE;
    }

    protected function detected_mime($tmp_name)
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp_name);
        return is_string($mime) ? $mime : '';
    }

    protected function local_url($profile)
    {
        return $profile && !empty($profile->logo_path) ? base_url('uploads/profil/' . rawurlencode($profile->logo_path)) : '';
    }

    protected function upload_dir()
    {
        if (function_exists('public_uploads_dir')) {
            return public_uploads_dir('profil');
        }
        return FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'profil' . DIRECTORY_SEPARATOR;
    }
}
