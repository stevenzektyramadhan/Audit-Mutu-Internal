<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('ami_fail_closed')) {
    function ami_fail_closed()
    {
        if (!headers_sent()) {
            header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
            header('Content-Type: text/plain; charset=UTF-8');
        }

        echo 'Production configuration is incomplete.';
        exit(1);
    }
}

if (!function_exists('ami_request_id')) {
    function ami_request_id()
    {
        if (defined('AMI_REQUEST_ID')) {
            return AMI_REQUEST_ID;
        }

        try {
            $request_id = bin2hex(random_bytes(16));
        } catch (Exception $exception) {
            $request_id = hash('sha256', uniqid('', TRUE) . microtime(TRUE));
        }

        define('AMI_REQUEST_ID', $request_id);
        return $request_id;
    }
}

if (!function_exists('ami_sanitize_log_message')) {
    function ami_sanitize_log_message($message)
    {
        $message = str_replace(["\r", "\n"], ['\\r', '\\n'], (string) $message);
        $patterns = [
            '/\b(Bearer)\s+[A-Za-z0-9._~+\/=-]+/i' => '$1 [REDACTED]',
            '/\b(password|passwd|pwd|token|secret|authorization|cookie)\b(\s*[:=]\s*)([^\s,;]+)/i' => '$1$2[REDACTED]',
        ];
        $message = preg_replace(array_keys($patterns), array_values($patterns), $message);
        if ($message === NULL) {
            $message = 'Log message could not be normalized.';
        }

        return strlen($message) > 8192 ? substr($message, 0, 8192) . ' [TRUNCATED]' : $message;
    }
}

if (!function_exists('ami_normalize_host')) {
    function ami_normalize_host($host)
    {
        $host = strtolower(rtrim(trim((string) $host), '.'));
        $host_without_brackets = trim($host, '[]');
        if (filter_var($host_without_brackets, FILTER_VALIDATE_IP) !== FALSE) {
            return $host_without_brackets;
        }

        return filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== FALSE
            ? $host
            : FALSE;
    }
}

if (!function_exists('ami_parse_production_base_url')) {
    function ami_parse_production_base_url($url)
    {
        $url = trim((string) $url);
        if (
            $url === ''
            || filter_var($url, FILTER_VALIDATE_URL) === FALSE
            || preg_match('/[\x00-\x20\x7f]|%(?:0a|0d)/i', $url)
        ) {
            return FALSE;
        }

        $parts = parse_url($url);
        if (
            $parts === FALSE
            || !isset($parts['scheme'], $parts['host'])
            || strtolower($parts['scheme']) !== 'https'
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['query'])
            || isset($parts['fragment'])
        ) {
            return FALSE;
        }

        $host = ami_normalize_host($parts['host']);
        if ($host === FALSE) {
            return FALSE;
        }

        if (isset($parts['port']) && ((int) $parts['port'] < 1 || (int) $parts['port'] > 65535)) {
            return FALSE;
        }

        return ['host' => $host, 'parts' => $parts];
    }
}

if (!function_exists('ami_allowed_hosts')) {
    function ami_allowed_hosts($value, $base_host)
    {
        $raw_hosts = $value === FALSE || trim((string) $value) === ''
            ? [$base_host]
            : explode(',', (string) $value);
        $hosts = [];

        foreach ($raw_hosts as $raw_host) {
            $host = ami_normalize_host($raw_host);
            if ($host === FALSE) {
                return FALSE;
            }
            $hosts[$host] = TRUE;
        }

        return array_keys($hosts);
    }
}

if (!function_exists('ami_request_host')) {
    function ami_request_host()
    {
        if (!isset($_SERVER['HTTP_HOST']) || trim((string) $_SERVER['HTTP_HOST']) === '') {
            return NULL;
        }

        $raw_host = trim((string) $_SERVER['HTTP_HOST']);
        if (preg_match('/[\x00-\x20\x7f\/\\\\@]/', $raw_host)) {
            return FALSE;
        }

        $parts = parse_url('http://' . $raw_host);
        if ($parts === FALSE || !isset($parts['host'])) {
            return FALSE;
        }

        return ami_normalize_host($parts['host']);
    }
}

if (!function_exists('ami_valid_proxy_list')) {
    function ami_valid_proxy_list($value)
    {
        if ($value === FALSE || trim((string) $value) === '') {
            return TRUE;
        }

        foreach (explode(',', (string) $value) as $entry) {
            $entry = trim($entry);
            $parts = explode('/', $entry, 2);
            $ip = $parts[0];
            if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
                return FALSE;
            }
            if (isset($parts[1])) {
                if ($parts[1] === '' || !ctype_digit($parts[1])) {
                    return FALSE;
                }
                $max_prefix = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== FALSE ? 128 : 32;
                if ((int) $parts[1] < 0 || (int) $parts[1] > $max_prefix) {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }
}

if (!function_exists('ami_display_errors_disabled')) {
    function ami_display_errors_disabled()
    {
        return in_array(
            strtolower(trim((string) ini_get('display_errors'))),
            ['', '0', 'off', 'none', 'no', 'false'],
            TRUE
        );
    }
}

if (!function_exists('ami_path_is_outside')) {
    function ami_path_is_outside($candidate, $web_root)
    {
        $candidate = realpath((string) $candidate);
        $web_root = realpath((string) $web_root);
        if ($candidate === FALSE || $web_root === FALSE) {
            return FALSE;
        }

        $candidate = strtolower(rtrim(str_replace('\\', '/', $candidate), '/') . '/');
        $web_root = strtolower(rtrim(str_replace('\\', '/', $web_root), '/') . '/');
        return strpos($candidate, $web_root) !== 0;
    }
}

if (!function_exists('ami_default_database_password')) {
    function ami_default_database_password($password, $username, $database)
    {
        $password = trim((string) $password);
        $normalized = strtolower($password);
        $known_defaults = [
            '',
            'root',
            'admin',
            'password',
            'password123',
            'changeme',
            'secret',
            'ami_secret',
            'ami_local_password',
            'local_root_password',
        ];

        return in_array($normalized, $known_defaults, TRUE)
            || hash_equals(strtolower(trim((string) $username)), $normalized)
            || hash_equals(strtolower(trim((string) $database)), $normalized);
    }
}

if (!function_exists('ami_privileged_database_username')) {
    function ami_privileged_database_username($username)
    {
        return in_array(
            strtolower(trim((string) $username)),
            ['root', 'mysql.sys', 'mysql.session', 'mysql.infoschema'],
            TRUE
        );
    }
}
