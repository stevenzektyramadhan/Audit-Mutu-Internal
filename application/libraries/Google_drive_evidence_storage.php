<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Google_drive_evidence_storage
{
    private const FAILURE_MESSAGE = 'Operasi penyimpanan bukti gagal.';
    private const FOLDER_ENV = 'GOOGLE_DRIVE_EVIDENCE_FOLDER_ID';
    private const MODE_ENV = 'GOOGLE_DRIVE_AUTH_MODE';
    private const SERVICE_ACCOUNT_ENV = 'GOOGLE_DRIVE_SERVICE_ACCOUNT_JSON_PATH';
    private const OAUTH_CLIENT_ENV = 'GOOGLE_DRIVE_OAUTH_CLIENT_SECRET_JSON_PATH';
    private const OAUTH_REFRESH_TOKEN_ENV = 'GOOGLE_DRIVE_OAUTH_REFRESH_TOKEN_JSON_PATH';

    private $service;
    private $folder_id;

    public function upload($source_path, $stored_name, $mime_type)
    {
        try {
            $this->ensure_ready();
            $source_path = $this->valid_source_path($source_path);
            $stored_name = $this->valid_stored_name($stored_name);
            $mime_type = $this->valid_mime_type($mime_type);

            $metadata = new Google_Service_Drive_DriveFile([
                'name' => $stored_name,
                'parents' => [$this->folder_id],
            ]);

            $file = $this->service->files->create($metadata, [
                'data' => file_get_contents($source_path),
                'mimeType' => $mime_type,
                'uploadType' => 'multipart',
                'fields' => 'id,name,mimeType,size,sha256Checksum',
                'supportsAllDrives' => TRUE,
            ]);

            return [
                'success' => TRUE,
                'file_id' => $file->getId(),
                'name' => $file->getName(),
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize() === NULL ? NULL : (int) $file->getSize(),
                'sha256' => $file->getSha256Checksum(),
            ];
        } catch (Throwable $exception) {
            return $this->failure();
        }
    }

    public function stream_download($file_id, $output)
    {
        try {
            $this->ensure_ready();
            $file_id = $this->valid_file_id($file_id);

            if (!is_resource($output)) {
                throw new InvalidArgumentException('Invalid output stream.');
            }

            $response = $this->service->files->get($file_id, [
                'alt' => 'media',
                'supportsAllDrives' => TRUE,
            ]);
            $body = $response->getBody();

            while (!$body->eof()) {
                if (fwrite($output, $body->read(1048576)) === FALSE) {
                    throw new RuntimeException('Unable to write output stream.');
                }
            }

            return ['success' => TRUE];
        } catch (Throwable $exception) {
            return $this->failure();
        }
    }

    public function trash($file_id)
    {
        try {
            $this->ensure_ready();
            $file = new Google_Service_Drive_DriveFile(['trashed' => TRUE]);

            $this->service->files->update($this->valid_file_id($file_id), $file, [
                'fields' => 'id,trashed',
                'supportsAllDrives' => TRUE,
            ]);

            return ['success' => TRUE];
        } catch (Throwable $exception) {
            return $this->failure();
        }
    }

    private function ensure_ready()
    {
        if ($this->service instanceof Google_Service_Drive) {
            return;
        }

        $autoload = APPPATH . '../vendor/autoload.php';
        if (!is_file($autoload)) {
            throw new RuntimeException('Composer autoload unavailable.');
        }
        require_once $autoload;

        $this->folder_id = $this->configured_folder_id();

        $client = new Google_Client();
        $client->setApplicationName('AMI Evidence Storage');
        $client->setScopes([Google_Service_Drive::DRIVE]);
        $mode = $this->configured_mode();
        if ($mode === 'service_account') {
            $client->setAuthConfig($this->configured_credentials_path(self::SERVICE_ACCOUNT_ENV));
        } elseif ($mode === 'oauth_refresh_token') {
            $this->configure_oauth_refresh_token($client);
        } else {
            throw new RuntimeException('Google Drive auth mode is not configured.');
        }

        $this->service = new Google_Service_Drive($client);
    }

    private function configured_folder_id()
    {
        $folder_id = $this->env(self::FOLDER_ENV);
        if ($folder_id === '' || !preg_match('/^[A-Za-z0-9_-]{10,}$/', $folder_id)) {
            throw new RuntimeException('Google Drive evidence folder is not configured.');
        }
        return $folder_id;
    }

    private function configured_mode()
    {
        $mode = $this->env(self::MODE_ENV);
        if (!in_array($mode, ['service_account', 'oauth_refresh_token'], TRUE)) {
            throw new RuntimeException('Google Drive auth mode is not configured.');
        }

        $service_account_path = $this->env(self::SERVICE_ACCOUNT_ENV);
        $oauth_client_path = $this->env(self::OAUTH_CLIENT_ENV);
        $oauth_refresh_token_path = $this->env(self::OAUTH_REFRESH_TOKEN_ENV);

        if ($mode === 'oauth_refresh_token' && ENVIRONMENT === 'production') {
            throw new RuntimeException('Google Drive OAuth refresh-token mode is development-only.');
        }
        if ($mode === 'service_account' && ($oauth_client_path !== '' || $oauth_refresh_token_path !== '')) {
            throw new RuntimeException('Google Drive service-account credentials must be exclusive.');
        }
        if ($mode === 'oauth_refresh_token' && $service_account_path !== '') {
            throw new RuntimeException('Google Drive OAuth credentials must be exclusive.');
        }
        return $mode;
    }

    private function configured_credentials_path($env_key)
    {
        $path = $this->env($env_key);
        if ($path === '' || !$this->is_absolute_path($path) || !is_file($path) || !is_readable($path)) {
            throw new RuntimeException('Google Drive credentials are not configured.');
        }
        $realpath = realpath($path);
        $web_root = realpath(FCPATH);
        if ($realpath === FALSE || ($web_root !== FALSE && strpos($realpath . DIRECTORY_SEPARATOR, $web_root . DIRECTORY_SEPARATOR) === 0)) {
            throw new RuntimeException('Google Drive credentials path is not safe.');
        }
        return $path;
    }

    private function configure_oauth_refresh_token($client)
    {
        $client->setAuthConfig($this->configured_credentials_path(self::OAUTH_CLIENT_ENV));
        $token_path = $this->configured_credentials_path(self::OAUTH_REFRESH_TOKEN_ENV);
        $token_json = file_get_contents($token_path);
        $token_data = $token_json === FALSE ? NULL : json_decode($token_json, TRUE);
        $refresh_token = is_array($token_data) && isset($token_data['refresh_token']) && is_string($token_data['refresh_token'])
            ? trim($token_data['refresh_token'])
            : '';
        if ($refresh_token === '') {
            throw new RuntimeException('Google Drive OAuth refresh token is not configured.');
        }
        $access_token = $client->fetchAccessTokenWithRefreshToken($refresh_token);
        if (!is_array($access_token) || isset($access_token['error']) || !isset($access_token['access_token']) || trim((string) $access_token['access_token']) === '') {
            throw new RuntimeException('Google Drive OAuth refresh failed.');
        }
        $client->setAccessToken($access_token);
    }

    private function env($key)
    {
        $value = getenv($key);
        if ($value === FALSE && isset($_SERVER[$key])) {
            $value = $_SERVER[$key];
        }
        if ($value === FALSE && isset($_ENV[$key])) {
            $value = $_ENV[$key];
        }
        return is_scalar($value) ? trim((string) $value) : '';
    }

    private function valid_source_path($source_path)
    {
        if (!is_string($source_path) || $source_path === '' || !is_file($source_path) || !is_readable($source_path)) {
            throw new InvalidArgumentException('Invalid source file.');
        }
        return $source_path;
    }

    private function valid_stored_name($stored_name)
    {
        if (!is_string($stored_name) || $stored_name === '' || basename($stored_name) !== $stored_name) {
            throw new InvalidArgumentException('Invalid evidence filename.');
        }
        if (!preg_match('/\A[A-Za-z0-9._-]{1,180}\z/', $stored_name) || in_array($stored_name, ['.', '..'], TRUE)) {
            throw new InvalidArgumentException('Invalid evidence filename.');
        }
        return $stored_name;
    }

    private function valid_mime_type($mime_type)
    {
        if (!is_string($mime_type) || !preg_match('/\A[-+.A-Za-z0-9]+\/[-+.A-Za-z0-9]+\z/', $mime_type)) {
            throw new InvalidArgumentException('Invalid MIME type.');
        }
        return $mime_type;
    }

    private function valid_file_id($file_id)
    {
        if (!is_string($file_id) || !preg_match('/\A[A-Za-z0-9_-]{10,}\z/', $file_id)) {
            throw new InvalidArgumentException('Invalid Google Drive file ID.');
        }
        return $file_id;
    }

    private function is_absolute_path($path)
    {
        return is_string($path) && ($path !== '' && ($path[0] === '/' || preg_match('/\A[A-Za-z]:[\\\\\/]/', $path) === 1));
    }

    private function failure()
    {
        return ['success' => FALSE, 'message' => self::FAILURE_MESSAGE];
    }
}
