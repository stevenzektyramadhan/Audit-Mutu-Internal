<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('ami_runtime_config_item')) {
    require_once dirname(__DIR__) . '/config/runtime_config.php';
}

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

if (!function_exists('private_storage_dir')) {
    function private_storage_dir($category)
    {
        $categories = ['instrumen', 'penetapan', 'bukti_auditor', 'tmp', 'user_photos', 'spmi_source', 'ppepp_documents'];
        $categories[] = 'rtm_photos';
        $categories[] = 'audit_evidence';
        if (!in_array($category, $categories, TRUE)) {
            throw new InvalidArgumentException('Kategori penyimpanan tidak valid.');
        }

		$configured = ami_runtime_config_item('app', 'private_storage_path', 'APP_PRIVATE_STORAGE_PATH', FALSE);
        $root = $configured !== FALSE && trim($configured) !== ''
            ? trim($configured)
            : sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ami-private-storage';

        return rtrim($root, '/\\') . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('public_uploads_dir')) {
    function public_uploads_dir($category = '')
    {
        $allowed = ['profil'];
        if ($category !== '' && !in_array($category, $allowed, TRUE)) {
            throw new InvalidArgumentException('Kategori public uploads tidak valid.');
        }

		$configured = ami_runtime_config_item('app', 'public_uploads_path', 'APP_PUBLIC_UPLOADS_PATH', FALSE);
        $root = $configured !== FALSE && trim($configured) !== ''
            ? rtrim(trim($configured), '/\\')
            : rtrim(FCPATH . 'uploads', '/\\');

        if ($category === '') {
            return $root . DIRECTORY_SEPARATOR;
        }

        return $root . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR;
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

        if (in_array($category, ['user_photos', 'spmi_source', 'ppepp_documents'], TRUE) || $category === 'audit_evidence') {
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
