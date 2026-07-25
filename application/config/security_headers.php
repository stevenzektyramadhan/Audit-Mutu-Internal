<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('ami_csp_nonce')) {
    /**
     * Return the request-scoped nonce used by the Content Security Policy.
     */
    function ami_csp_nonce()
    {
        if (!defined('AMI_CSP_NONCE')) {
            try {
                $nonce = base64_encode(random_bytes(18));
            } catch (Exception $exception) {
                $nonce = base64_encode(hash(
                    'sha256',
                    (defined('AMI_REQUEST_ID') ? AMI_REQUEST_ID : uniqid('', TRUE)) . microtime(TRUE),
                    TRUE
                ));
            }

            define('AMI_CSP_NONCE', $nonce);
        }

        return AMI_CSP_NONCE;
    }
}

if (!function_exists('ami_security_request_is_https')) {
    /**
     * Detect HTTPS from web-server controlled values only.
     *
     * Forwarded headers are deliberately ignored here. A trusted reverse proxy
     * must normalize the upstream request into HTTPS=on or SERVER_PORT=443.
     */
    function ami_security_request_is_https(array $server)
    {
        if (isset($server['HTTPS'])) {
            $https = strtolower(trim((string) $server['HTTPS']));
            if ($https !== '' && $https !== 'off' && $https !== '0') {
                return TRUE;
            }
        }

        return isset($server['SERVER_PORT']) && (int) $server['SERVER_PORT'] === 443;
    }
}

if (!function_exists('ami_security_header_values')) {
    /**
     * Build deterministic header values so the policy can be regression tested.
     */
    function ami_security_header_values($environment, $is_https, $nonce)
    {
        $nonce = (string) $nonce;
        if (preg_match('/\A[A-Za-z0-9+\/=_-]{16,128}\z/', $nonce) !== 1) {
            throw new InvalidArgumentException('Invalid CSP nonce.');
        }

        $csp = [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "frame-src 'none'",
            "form-action 'self'",
            "script-src 'self' 'nonce-" . $nonce . "' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "script-src-attr 'none'",
            "style-src 'self' 'nonce-" . $nonce . "' https://cdnjs.cloudflare.com https://fonts.googleapis.com",
            "style-src-attr 'unsafe-inline'",
            "font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com",
            "img-src 'self' data: blob:",
            "connect-src 'self'",
            "media-src 'none'",
            "worker-src 'none'",
            "manifest-src 'self'",
        ];

        $production_https = strtolower(trim((string) $environment)) === 'production'
            && (bool) $is_https;
        if ($production_https) {
            $csp[] = 'upgrade-insecure-requests';
        }

        $headers = [
            'Content-Security-Policy' => implode('; ', $csp),
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'accelerometer=(), autoplay=(), camera=(), display-capture=(), encrypted-media=(), fullscreen=(self), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), midi=(), payment=(), publickey-credentials-get=(), usb=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Cross-Origin-Resource-Policy' => 'same-origin',
            'X-Permitted-Cross-Domain-Policies' => 'none',
            'Cache-Control' => 'private, no-store, max-age=0, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        if ($production_https) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        return $headers;
    }
}

if (!function_exists('ami_apply_security_headers')) {
    function ami_apply_security_headers()
    {
        ami_csp_nonce();

        if (PHP_SAPI === 'cli' || headers_sent()) {
            return;
        }

        $headers = ami_security_header_values(
            defined('ENVIRONMENT') ? ENVIRONMENT : 'development',
            ami_security_request_is_https($_SERVER),
            AMI_CSP_NONCE
        );

        foreach ($headers as $name => $value) {
            header($name . ': ' . $value, TRUE);
        }
    }
}

ami_apply_security_headers();
