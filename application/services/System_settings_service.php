<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class System_settings_service
{
    const TURNSTILE_SITEVERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    protected $ci;
    protected $model;
    protected $logo_service;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('System_settings_model');
        $this->model = $this->ci->System_settings_model;
        require_once APPPATH . 'services/Organization_logo_service.php';
        $this->logo_service = new Organization_logo_service();
    }

    public function settings()
    {
        $profile = $this->model->profile();
        $settings = $this->model->settings();
        $logo = $this->logo_service->preview_urls($profile);
        $site_key = $settings ? trim((string) $settings->turnstile_site_key) : '';
        $ciphertext = $settings ? trim((string) $settings->turnstile_secret_ciphertext) : '';

        return [
            'organization_name' => $profile ? (string) $profile->nama_pt : '',
            'organization_description' => $profile && property_exists($profile, 'deskripsi') ? (string) $profile->deskripsi : '',
            'organization_logo_local_url' => $logo['local_url'],
            'organization_logo_fallback_url' => $logo['fallback_url'],
            'institution_logo_limit_bytes' => $this->logo_service->limit_bytes(),
            'turnstile_site_key' => $site_key,
            'turnstile_secret_configured' => $ciphertext !== '' && $this->decrypt_secret($ciphertext) !== NULL,
            'turnstile_enabled' => $this->turnstile_is_enabled($site_key, $ciphertext),
        ];
    }

    public function update(array $data, $actor_role, array $logo_upload = NULL)
    {
        $can_manage_turnstile = (string) $actor_role === 'super_admin';
        $organization_name = trim((string) ($data['organization_name'] ?? ''));
        $organization_description = trim((string) ($data['organization_description'] ?? ''));

        if ($organization_name === '' || mb_strlen($organization_name, 'UTF-8') > 200) {
            return ['success' => FALSE, 'message' => 'Nama organisasi wajib diisi dan maksimal 200 karakter.'];
        }

        if ($can_manage_turnstile) {
            $site_key = trim((string) ($data['turnstile_site_key'] ?? ''));
            $secret_replacement = trim((string) ($data['turnstile_secret_key'] ?? ''));

            if ($site_key !== '' && !$this->is_plausible_turnstile_key($site_key)) {
                return ['success' => FALSE, 'message' => 'Site key Turnstile tidak valid.'];
            }
            if ($secret_replacement !== '' && !$this->is_plausible_turnstile_secret($secret_replacement)) {
                return ['success' => FALSE, 'message' => 'Secret key Turnstile tidak valid.'];
            }
        }

        $candidate = $this->logo_service->store_candidate($logo_upload);
        if (!$candidate['success']) return ['success' => FALSE, 'message' => $candidate['message']];

        $this->ci->db->trans_begin();
        $profile = $this->model->profile_for_update();

        $profile_data = [
            'nama_pt' => $organization_name,
            'deskripsi' => $organization_description === '' ? NULL : $organization_description,
        ];
        if (!empty($candidate['file_name'])) {
            $profile_data['logo_path'] = $candidate['file_name'];
        }

        if (!$this->model->upsert_profile($profile ? (int) $profile->id : 0, $profile_data)) {
            return $this->rollback('Pengaturan sistem gagal disimpan.', $candidate['file_name']);
        }

        if ($can_manage_turnstile) {
            $settings = $this->model->settings_for_update();
            $ciphertext = $settings ? (string) $settings->turnstile_secret_ciphertext : NULL;

            if ($secret_replacement !== '') {
                $ciphertext = $this->encrypt_secret($secret_replacement);
                if ($ciphertext === NULL) return $this->rollback('Secret key Turnstile gagal dienkripsi. Periksa konfigurasi APP_ENCRYPTION_KEY.', $candidate['file_name']);
            }

            $settings_data = [
                'turnstile_site_key' => $site_key === '' ? NULL : $site_key,
                'turnstile_secret_ciphertext' => $ciphertext === '' ? NULL : $ciphertext,
            ];

            if (!$this->model->upsert_settings($settings_data)) {
                return $this->rollback('Pengaturan sistem gagal disimpan.', $candidate['file_name']);
            }
        }

        $result = $this->finish(['success' => TRUE, 'message' => 'Pengaturan sistem berhasil disimpan.'], $candidate['file_name']);
        if (!empty($result['success']) && !empty($candidate['file_name']) && $profile && !empty($profile->logo_path)) {
            $this->logo_service->delete_local($profile->logo_path);
        }
        return $result;
    }

    public function turnstile_public_settings()
    {
        $settings = $this->model->settings();
        $site_key = $settings ? trim((string) $settings->turnstile_site_key) : '';
        $ciphertext = $settings ? trim((string) $settings->turnstile_secret_ciphertext) : '';

        return [
            'enabled' => $this->turnstile_is_enabled($site_key, $ciphertext),
            'site_key' => $this->turnstile_is_enabled($site_key, $ciphertext) ? $site_key : '',
        ];
    }

    public function verify_turnstile_response($token, $remote_ip = '')
    {
        $token = trim((string) $token);
        if ($token === '' || strlen($token) > 2048) {
            return ['success' => FALSE, 'message' => 'Verifikasi keamanan gagal. Silakan coba lagi.'];
        }

        $settings = $this->model->settings();
        $site_key = $settings ? trim((string) $settings->turnstile_site_key) : '';
        $ciphertext = $settings ? trim((string) $settings->turnstile_secret_ciphertext) : '';
        if (!$this->turnstile_is_enabled($site_key, $ciphertext)) {
            return ['success' => TRUE, 'message' => 'Turnstile tidak aktif.'];
        }

        $secret = $this->decrypt_secret($ciphertext);
        if ($secret === NULL || $secret === '') {
            return ['success' => FALSE, 'message' => 'Verifikasi keamanan gagal. Silakan coba lagi.'];
        }

        $payload = ['secret' => $secret, 'response' => $token];
        $remote_ip = trim((string) $remote_ip);
        if ($remote_ip !== '') $payload['remoteip'] = $remote_ip;
        $body = http_build_query($payload, '', '&');
        $headers = [];
        $warning = FALSE;
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Accept: application/json',
                    'Content-Length: ' . strlen($body),
                ],
                'content' => $body,
                'ignore_errors' => TRUE,
                'timeout' => 5,
            ],
            'ssl' => [
                'verify_peer' => TRUE,
                'verify_peer_name' => TRUE,
                'allow_self_signed' => FALSE,
            ],
        ]);
        $handler = function ($errno) use (&$warning) {
            if (!(error_reporting() & $errno)) return FALSE;
            $warning = TRUE;
            return TRUE;
        };

        set_error_handler($handler);
        try {
            $response = file_get_contents(self::TURNSTILE_SITEVERIFY_URL, FALSE, $context);
            if (isset($http_response_header) && is_array($http_response_header)) $headers = $http_response_header;
        } finally {
            restore_error_handler();
        }

        if ($warning || !is_string($response) || $this->parse_http_status_code($headers) !== 200) {
            return ['success' => FALSE, 'message' => 'Verifikasi keamanan gagal. Silakan coba lagi.'];
        }

        $decoded = json_decode($response, TRUE);
        if (!is_array($decoded) || !isset($decoded['success']) || $decoded['success'] !== TRUE) {
            return ['success' => FALSE, 'message' => 'Verifikasi keamanan gagal. Silakan coba lagi.'];
        }

        return ['success' => TRUE, 'message' => 'Verifikasi keamanan berhasil.'];
    }

    protected function turnstile_is_enabled($site_key, $ciphertext)
    {
        return $this->is_plausible_turnstile_key($site_key) && $this->decrypt_secret($ciphertext) !== NULL;
    }

    protected function is_plausible_turnstile_key($value)
    {
        return is_string($value) && preg_match('/\A[A-Za-z0-9_-]{20,255}\z/', $value) === 1;
    }

    protected function is_plausible_turnstile_secret($value)
    {
        return is_string($value) && preg_match('/\A[A-Za-z0-9_-]{20,255}\z/', $value) === 1;
    }

    protected function encrypt_secret($secret)
    {
        $key = $this->encryption_key();
        if ($key === NULL) return NULL;
        try {
            $iv = random_bytes(12);
        } catch (Throwable $exception) {
            return NULL;
        }
        $tag = '';
        $ciphertext = openssl_encrypt((string) $secret, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if (!is_string($ciphertext) || strlen($tag) !== 16) return NULL;

        $payload = json_encode([
            'v' => 1,
            'alg' => 'AES-256-GCM',
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
            'ct' => base64_encode($ciphertext),
        ]);

        return is_string($payload) ? $payload : NULL;
    }

    protected function decrypt_secret($payload)
    {
        $payload = trim((string) $payload);
        if ($payload === '') return NULL;
        $key = $this->encryption_key();
        if ($key === NULL) return NULL;
        $decoded = json_decode($payload, TRUE);
        if (!is_array($decoded) || ($decoded['alg'] ?? '') !== 'AES-256-GCM') return NULL;

        $iv = base64_decode((string) ($decoded['iv'] ?? ''), TRUE);
        $tag = base64_decode((string) ($decoded['tag'] ?? ''), TRUE);
        $ciphertext = base64_decode((string) ($decoded['ct'] ?? ''), TRUE);
        if (!is_string($iv) || strlen($iv) !== 12 || !is_string($tag) || strlen($tag) !== 16 || !is_string($ciphertext)) return NULL;

        $secret = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        return is_string($secret) && $secret !== '' ? $secret : NULL;
    }

    protected function encryption_key()
    {
        $configured = (string) $this->ci->config->item('encryption_key');
        if ($configured === '') return NULL;
        return hash('sha256', $configured, TRUE);
    }

    protected function parse_http_status_code($headers)
    {
        if (!is_array($headers)) return NULL;
        for ($index = count($headers) - 1; $index >= 0; $index--) {
            if (is_string($headers[$index]) && preg_match('/\AHTTP\/\S+\s+(\d{3})\b/', $headers[$index], $matches)) {
                return (int) $matches[1];
            }
        }
        return NULL;
    }

    protected function rollback($message, $candidate_file = NULL)
    {
        $this->ci->db->trans_rollback();
        if (!empty($candidate_file)) $this->logo_service->delete_local($candidate_file);
        return ['success' => FALSE, 'message' => $message];
    }

    protected function finish($result, $candidate_file = NULL)
    {
        if ($this->ci->db->trans_status() === FALSE) {
            $this->ci->db->trans_rollback();
            if (!empty($candidate_file)) $this->logo_service->delete_local($candidate_file);
            return ['success' => FALSE, 'message' => 'Pengaturan sistem gagal disimpan.'];
        }
        $this->ci->db->trans_commit();
        return $result;
    }
}
