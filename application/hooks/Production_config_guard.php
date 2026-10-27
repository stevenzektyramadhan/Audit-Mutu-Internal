<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Production_config_guard
{
    public function validate()
    {
        if (ENVIRONMENT !== 'production') {
            return;
        }

        $base_url = config_item('base_url');
        $encryption_key = config_item('encryption_key');
        $cookie_secure = config_item('cookie_secure');
        $log_threshold = config_item('log_threshold');
        $log_path = config_item('log_path');
        $private_storage_path = config_item('private_storage_path');
        $public_uploads_path = config_item('public_uploads_path');
        $session_save_path = config_item('sess_save_path');
        $backend = config_item('spmi_evidence_storage_backend');

        $web_root = defined('FCPATH') ? realpath(FCPATH) : FALSE;

        // Base URL HTTPS
        $base_url_valid = is_string($base_url) && strpos($base_url, 'https://') === 0;

        // Encryption Key
        $encryption_key_valid = is_string($encryption_key) && trim($encryption_key) !== '' && $encryption_key !== 'AMI_DEFAULT_DEV_ENCRYPTION_KEY_32_CHARS!';

        // Cookie Secure
        $cookie_secure_valid = $cookie_secure === TRUE;

        // Logging
        $real_log_path = ($log_path !== FALSE && $log_path !== '') ? realpath($log_path) : FALSE;
        $log_valid = (int) $log_threshold > 0
            && $real_log_path !== FALSE
            && is_dir($real_log_path)
            && is_writable($real_log_path)
            && ($web_root === FALSE || strpos($real_log_path . DIRECTORY_SEPARATOR, $web_root . DIRECTORY_SEPARATOR) !== 0);

        // Private Storage (opsional: jika diisi harus valid & di luar webroot)
        $private_storage_valid = TRUE;
        if (!empty($private_storage_path)) {
            $real_private = realpath($private_storage_path);
            $private_storage_valid = $real_private !== FALSE
                && is_dir($real_private)
                && is_writable($real_private)
                && ($web_root === FALSE || strpos($real_private . DIRECTORY_SEPARATOR, $web_root . DIRECTORY_SEPARATOR) !== 0);
        }

        // Public Uploads (jika diisi harus valid)
        $public_uploads_valid = TRUE;
        if (!empty($public_uploads_path)) {
            $real_public = realpath($public_uploads_path);
            $public_uploads_valid = $real_public !== FALSE && is_dir($real_public) && is_writable($real_public);
        }

        // Session Save Path
        $session_valid = TRUE;
        if (!empty($session_save_path) && !(defined('PHP_SAPI') && PHP_SAPI === 'cli')) {
            $real_session = realpath($session_save_path);
            $session_valid = $real_session !== FALSE && is_dir($real_session) && is_writable($real_session);
        }

        $is_valid = $base_url_valid
            && $encryption_key_valid
            && $cookie_secure_valid
            && $log_valid
            && $private_storage_valid
            && $public_uploads_valid
            && $session_valid;

        if (!$is_valid) {
            header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
            echo 'Production configuration is incomplete. Please check application/config/production/ configuration files.';
            exit(1);
        }
    }
}
