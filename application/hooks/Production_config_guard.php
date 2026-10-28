<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Production_config_guard
{
    public function validate()
    {
        if (ENVIRONMENT !== 'production') {
            return;
        }

        $failed_checks = self::failed_checks();
        if ($failed_checks !== array()) {
            $message = 'Production configuration failed checks: ' . implode(', ', $failed_checks);
            if (function_exists('log_message')) {
                log_message('error', $message);
            } else {
                error_log($message);
            }
            header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
            echo 'Production configuration is incomplete. Please check application/config/production/ configuration files.';
            exit(1);
        }
    }

    public static function failed_checks()
    {
        $failed_checks = array();
        $base_url = config_item('base_url');
        $encryption_key = config_item('encryption_key');
        $log_path = config_item('log_path');
        $session_save_path = config_item('sess_save_path');

        if (!self::valid_base_url($base_url)) {
            $failed_checks[] = 'base_url';
        }
        if (!self::valid_encryption_key($encryption_key)) {
            $failed_checks[] = 'encryption_key';
        }
        if (config_item('cookie_secure') !== TRUE) {
            $failed_checks[] = 'cookie_secure';
        }
        if ((int) config_item('log_threshold') < 1) {
            $failed_checks[] = 'log_threshold';
        }
        if (!self::prepare_writable_directory($log_path)) {
            $failed_checks[] = 'log_path';
        }
        if (!self::prepare_writable_directory($session_save_path)) {
            $failed_checks[] = 'sess_save_path';
        }

        self::append_storage_checks($failed_checks, 'private_storage_path', 'private');
        self::append_storage_checks($failed_checks, 'public_uploads_path', 'public');

        return $failed_checks;
    }

    protected static function append_storage_checks(&$failed_checks, $config_key, $visibility)
    {
        $root = config_item($config_key);
        if (!self::valid_storage_root($root)) {
            $failed_checks[] = $config_key;
            return;
        }

        $categories = $visibility === 'private'
            ? app_private_storage_categories()
            : app_public_upload_categories();
        foreach ($categories as $category) {
            try {
                $directory = $visibility === 'private'
                    ? private_storage_dir($category)
                    : public_uploads_dir($category);
                if (!is_dir($directory) || !is_writable($directory)) {
                    $failed_checks[] = $visibility . '_storage_' . $category;
                }
            } catch (Throwable $exception) {
                $failed_checks[] = $visibility . '_storage_' . $category;
            }
        }
    }

    protected static function valid_base_url($base_url)
    {
        if (self::placeholder($base_url) || substr($base_url, -1) !== '/') {
            return FALSE;
        }

        $parts = parse_url($base_url);
        return is_array($parts)
            && isset($parts['scheme'], $parts['host'])
            && strtolower($parts['scheme']) === 'https'
            && $parts['host'] !== '';
    }

    protected static function valid_encryption_key($encryption_key)
    {
        return !self::placeholder($encryption_key)
            && $encryption_key !== 'AMI_DEFAULT_DEV_ENCRYPTION_KEY_32_CHARS!'
            && strlen($encryption_key) >= 32;
    }

    protected static function valid_storage_root($root)
    {
        return !self::placeholder($root) && app_storage_is_absolute_path($root);
    }

    protected static function prepare_writable_directory($directory)
    {
        if (!is_string($directory) || trim($directory) === '' || !app_storage_is_absolute_path($directory)) {
            return FALSE;
        }
        if (!is_dir($directory) && !mkdir($directory, 0775, TRUE) && !is_dir($directory)) {
            return FALSE;
        }

        return is_writable($directory);
    }

    protected static function placeholder($value)
    {
        return !is_string($value) || trim($value) === '' || stripos($value, 'GANTI') !== FALSE;
    }
}
