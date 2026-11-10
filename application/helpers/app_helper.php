<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('status_audit_meta')) {
    function status_audit_meta($status)
    {
        $statuses = [
            STATUS_BELUM_DIISI => ['label' => 'Belum diisi', 'icon' => 'fa-exclamation-circle', 'tone' => 'status-belum_diisi'],
            STATUS_DIISI => ['label' => 'Sudah diisi', 'icon' => 'fa-clock', 'tone' => 'status-diisi'],
            STATUS_DINILAI => ['label' => 'Sudah dinilai', 'icon' => 'fa-check-circle', 'tone' => 'status-dinilai'],
        ];

        return isset($statuses[$status])
            ? $statuses[$status]
            : ['label' => (string) $status, 'icon' => 'fa-circle', 'tone' => 'status-diisi'];
    }
}

if (!function_exists('skor_audit_options')) {
    function skor_audit_options()
    {
        return [
            1 => 'Tidak sesuai',
            2 => 'Kurang sesuai',
            3 => 'Sesuai',
            4 => 'Sangat sesuai',
        ];
    }
}

if (!function_exists('format_tanggal_indo')) {
    function format_tanggal_indo($datetime)
    {
        $timestamp = strtotime((string) $datetime);
        if ($timestamp === FALSE) {
            return '-';
        }

        $bulan = [
            1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
        ];

        return date('d', $timestamp) . ' ' . $bulan[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    }
}

if (!function_exists('app_private_storage_categories')) {
    function app_private_storage_categories()
    {
        return [
            'audit_evidence',
            'bukti_auditor',
            'instrumen',
            'penetapan',
            'ppepp_documents',
            'rtm_photos',
            'spmi_historical_reports',
            'spmi_source',
            'tmp',
            'user_photos',
        ];
    }
}

if (!function_exists('app_public_upload_categories')) {
    function app_public_upload_categories()
    {
        return ['profil'];
    }
}

if (!function_exists('app_storage_config_value')) {
    function app_storage_config_value($key)
    {
        $configured = function_exists('config_item') ? config_item($key) : NULL;
        if (($configured === NULL || $configured === '') && isset($GLOBALS['config'][$key])) {
            $configured = $GLOBALS['config'][$key];
        }

        return is_string($configured) ? trim($configured) : '';
    }
}

if (!function_exists('app_storage_is_absolute_path')) {
    function app_storage_is_absolute_path($path)
    {
        return is_string($path)
            && ($path !== '')
            && ($path[0] === '/' || $path[0] === '\\' || preg_match('/\A[A-Za-z]:[\\\\\/]/', $path) === 1);
    }
}

if (!function_exists('app_storage_root')) {
    function app_storage_root($key)
    {
        $root = app_storage_config_value($key);
        if ($root === '') {
            throw new RuntimeException('Konfigurasi root penyimpanan belum diisi.');
        }
        if (!app_storage_is_absolute_path($root)) {
            throw new RuntimeException('Konfigurasi root penyimpanan harus menggunakan path absolut.');
        }

        $root = rtrim($root, '/\\');
        return $root === '' ? DIRECTORY_SEPARATOR : $root . DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('app_prepare_storage_directory')) {
    function app_prepare_storage_directory($directory, $kind, $category)
    {
        if (!is_dir($directory) && !mkdir($directory, 0775, TRUE) && !is_dir($directory)) {
            throw new RuntimeException('Direktori penyimpanan ' . $kind . ' untuk kategori ' . $category . ' tidak dapat disiapkan.');
        }
        if (!is_writable($directory)) {
            throw new RuntimeException('Direktori penyimpanan ' . $kind . ' untuk kategori ' . $category . ' tidak dapat ditulis.');
        }

        return $directory;
    }
}

if (!function_exists('private_storage_dir')) {
    function private_storage_dir($category)
    {
        if (!is_string($category) || !in_array($category, app_private_storage_categories(), TRUE)) {
            throw new InvalidArgumentException('Kategori penyimpanan tidak valid.');
        }

        return app_prepare_storage_directory(
            app_storage_root('private_storage_path') . $category . DIRECTORY_SEPARATOR,
            'private',
            $category
        );
    }
}

if (!function_exists('public_uploads_dir')) {
    function public_uploads_dir($category = '')
    {
        if ($category !== '' && (!is_string($category) || !in_array($category, app_public_upload_categories(), TRUE))) {
            throw new InvalidArgumentException('Kategori public uploads tidak valid.');
        }

        $root = app_storage_root('public_uploads_path');
        if ($category === '') {
            return app_prepare_storage_directory($root, 'public', 'root');
        }

        return app_prepare_storage_directory($root . $category . DIRECTORY_SEPARATOR, 'public', $category);
    }
}

if (!function_exists('public_uploads_path')) {
    function public_uploads_path($category, $stored_name)
    {
        $stored_name = (string) $stored_name;
        if ($stored_name === '' || basename($stored_name) !== $stored_name) {
            return NULL;
        }

        $path = public_uploads_dir($category) . $stored_name;
        return is_file($path) ? $path : NULL;
    }
}

if (!function_exists('delete_public_upload')) {
    function delete_public_upload($category, $stored_name)
    {
        $path = public_uploads_path($category, $stored_name);
        return $path === NULL || unlink($path);
    }
}

if (!function_exists('private_storage_path')) {
    function private_storage_path($category, $stored_name)
    {
        $stored_name = (string) $stored_name;
        if ($stored_name === '' || basename($stored_name) !== $stored_name) {
            return NULL;
        }

        $private_path = private_storage_dir($category) . $stored_name;
        if (is_file($private_path)) {
            return $private_path;
        }

        if (!in_array($category, ['instrumen', 'penetapan', 'bukti_auditor', 'rtm_photos'], TRUE) || !defined('FCPATH')) {
            return NULL;
        }

        $legacy_path = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR . $stored_name;
        return is_file($legacy_path) ? $legacy_path : NULL;
    }
}

if (!function_exists('delete_private_file')) {
    function delete_private_file($category, $stored_name)
    {
        $path = private_storage_path($category, $stored_name);
        return $path === NULL || unlink($path);
    }
}
