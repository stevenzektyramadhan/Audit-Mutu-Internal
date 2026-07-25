<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('ami_e')) {
    /**
     * Encode untrusted data for an HTML text or quoted-attribute context.
     *
     * Double encoding is intentional: a stored entity such as &quot; must
     * remain text and must never become an attribute delimiter in the browser.
     */
    function ami_e($value)
    {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
            TRUE
        );
    }
}
if (!function_exists('ami_text')) {
    /**
     * Render plain text with line breaks. Business data is not rich HTML.
     */
    function ami_text($value)
    {
        return nl2br(ami_e($value), FALSE);
    }
}

if (!function_exists('ami_safe_http_url')) {
    /**
     * Return a browser-linkable HTTP(S) URL or an empty string.
     */
    function ami_safe_http_url($value)
    {
        $url = trim((string) $value);
        if ($url === ''
            || preg_match('/[\x00-\x1F\x7F]/', $url)
            || filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            return '';
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = (string) parse_url($url, PHP_URL_HOST);

        return in_array($scheme, ['http', 'https'], TRUE) && $host !== ''
            ? $url
            : '';
    }
}

if (!function_exists('ami_json')) {
    /**
     * Encode data for direct embedding inside a script element.
     */
    function ami_json($value)
    {
        $encoded = json_encode(
            $value,
            JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
                | JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_INVALID_UTF8_SUBSTITUTE
        );

        return $encoded === FALSE ? 'null' : $encoded;
    }
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
        $categories = [
            'spmi_source',
            'instrumen',
            'penetapan',
            'bukti_auditor',
            'notulen',
            'daftar_hadir',
            'tmp',
            'user_photos',
        ];
        if (!in_array($category, $categories, TRUE)) {
            throw new InvalidArgumentException('Kategori penyimpanan tidak valid.');
        }

        $configured = getenv('APP_PRIVATE_STORAGE_PATH');
        $root = $configured !== FALSE && trim($configured) !== ''
            ? trim($configured)
            : sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ami-private-storage';

        return rtrim($root, '/\\') . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('ami_valid_stored_name')) {
    function ami_valid_stored_name($stored_name)
    {
        $stored_name = (string) $stored_name;
        if ($stored_name === ''
            || basename($stored_name) !== $stored_name
            || $stored_name !== trim($stored_name, " .\t\n\r\0\x0B")
            || preg_match('/[\x00-\x1F\x7F\/\\\\:]/', $stored_name)) {
            return FALSE;
        }

        $base = strtoupper((string) pathinfo($stored_name, PATHINFO_FILENAME));
        return preg_match('/^(?:CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])$/', $base) !== 1;
    }
}

if (!function_exists('private_storage_path')) {
    function private_storage_path($category, $stored_name)
    {
        $stored_name = (string) $stored_name;
        if (!ami_valid_stored_name($stored_name)) {
            return NULL;
        }

        $private_path = private_storage_dir($category) . $stored_name;
        if (is_file($private_path)) {
            return $private_path;
        }

        if ($category === 'user_photos') {
            return NULL;
        }

        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            return NULL;
        }

        $legacy_path = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR . $stored_name;
        return is_file($legacy_path) ? $legacy_path : NULL;
    }
}
