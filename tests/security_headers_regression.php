<?php

$root = dirname(__DIR__);
$checks = 0;

function m107_check($condition, $message)
{
    global $checks;
    $checks++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function m107_source($root, $path)
{
    $contents = file_get_contents($root . DIRECTORY_SEPARATOR . $path);
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }
    return $contents;
}

defined('BASEPATH') OR define('BASEPATH', $root . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
defined('ENVIRONMENT') OR define('ENVIRONMENT', 'testing');
require_once $root . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'config'
    . DIRECTORY_SEPARATOR . 'security_headers.php';

$nonce = 'M107RegressionNonce123456';
$development = ami_security_header_values('development', FALSE, $nonce);
$production_http = ami_security_header_values('production', FALSE, $nonce);
$production_https = ami_security_header_values('production', TRUE, $nonce);

foreach ([
    'Content-Security-Policy',
    'X-Frame-Options',
    'X-Content-Type-Options',
    'Referrer-Policy',
    'Permissions-Policy',
    'Cache-Control',
    'Pragma',
    'Expires',
] as $required_header) {
    m107_check(isset($development[$required_header]), 'Header wajib tidak tersedia: ' . $required_header);
}

$csp = $development['Content-Security-Policy'];
foreach ([
    "default-src 'self'",
    "base-uri 'self'",
    "object-src 'none'",
    "frame-ancestors 'none'",
    "form-action 'self'",
    "script-src 'self' 'nonce-" . $nonce . "'",
    "script-src-attr 'none'",
    "style-src 'self' 'nonce-" . $nonce . "'",
    "style-src-attr 'unsafe-inline'",
    'https://cdnjs.cloudflare.com',
    'https://cdn.jsdelivr.net',
    'https://fonts.googleapis.com',
    'https://fonts.gstatic.com',
] as $directive) {
    m107_check(strpos($csp, $directive) !== FALSE, 'CSP belum memuat: ' . $directive);
}

m107_check(strpos($csp, "'unsafe-eval'") === FALSE, 'CSP tidak boleh mengizinkan unsafe-eval.');
m107_check(
    preg_match("/script-src[^;]*'unsafe-inline'/", $csp) !== 1,
    'JavaScript inline tidak boleh diizinkan tanpa nonce.'
);
m107_check($development['X-Frame-Options'] === 'DENY', 'X-Frame-Options harus DENY.');
m107_check($development['X-Content-Type-Options'] === 'nosniff', 'MIME sniffing belum dimatikan.');
m107_check(
    $development['Referrer-Policy'] === 'strict-origin-when-cross-origin',
    'Referrer policy tidak sesuai.'
);
m107_check(
    strpos($development['Permissions-Policy'], 'camera=()') !== FALSE
        && strpos($development['Permissions-Policy'], 'microphone=()') !== FALSE
        && strpos($development['Permissions-Policy'], 'geolocation=()') !== FALSE,
    'Capability sensitif browser belum dibatasi.'
);
m107_check(strpos($development['Cache-Control'], 'no-store') !== FALSE, 'Halaman dinamis masih boleh di-cache.');
m107_check(!isset($development['Strict-Transport-Security']), 'HSTS tidak boleh aktif pada development HTTP.');
m107_check(!isset($production_http['Strict-Transport-Security']), 'HSTS tidak boleh dikirim pada production HTTP.');
m107_check(
    isset($production_https['Strict-Transport-Security'])
        && $production_https['Strict-Transport-Security'] === 'max-age=31536000; includeSubDomains',
    'HSTS production HTTPS tidak sesuai.'
);
m107_check(
    strpos($production_https['Content-Security-Policy'], 'upgrade-insecure-requests') !== FALSE,
    'Production HTTPS harus meningkatkan mixed content.'
);
m107_check(
    strpos($production_http['Content-Security-Policy'], 'upgrade-insecure-requests') === FALSE,
    'Production HTTP tidak boleh memaksa upgrade melalui CSP sebelum HTTPS aktif.'
);

m107_check(
    ami_security_request_is_https(['HTTPS' => 'on', 'SERVER_PORT' => '80']),
    'HTTPS=on tidak dikenali.'
);
m107_check(
    ami_security_request_is_https(['SERVER_PORT' => '443']),
    'Port 443 tidak dikenali sebagai HTTPS.'
);
m107_check(
    !ami_security_request_is_https(['HTTPS' => 'off', 'SERVER_PORT' => '80', 'HTTP_X_FORWARDED_PROTO' => 'https']),
    'Forwarded proto dari sumber tidak tepercaya tidak boleh mengaktifkan HSTS.'
);

$index_source = m107_source($root, 'index.php');
m107_check(
    strpos($index_source, "require_once APPPATH.'config/security_headers.php'") !== FALSE,
    'Front controller belum memasang security headers secara global.'
);

$view_root = $root . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($view_root, FilesystemIterator::SKIP_DOTS)
);
$tag_count = 0;
foreach ($iterator as $file) {
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
        continue;
    }

    $contents = file_get_contents($file->getPathname());
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca view ' . $file->getPathname());
    }

    foreach (preg_split('/\R/', $contents) as $line_number => $line) {
        if (preg_match('/<(?:script|style)\b/i', $line) !== 1) {
            continue;
        }

        $tag_count++;
        m107_check(
            strpos($line, 'nonce="<?php echo ami_csp_nonce(); ?>"') !== FALSE,
            'Tag script/style tanpa nonce di '
                . $file->getPathname()
                . ':'
                . ($line_number + 1)
        );
    }

    m107_check(
        preg_match('/\son[a-z]+\s*=/i', $contents) !== 1,
        'Inline event handler ditemukan di ' . $file->getPathname()
    );
}
m107_check($tag_count > 0, 'Tidak ada tag script/style yang diaudit.');

$file_security = m107_source($root, 'application/libraries/File_security.php');
m107_check(
    strpos($file_security, "Content-Security-Policy: sandbox; default-src 'none'") !== FALSE,
    'Download file privat harus mengganti CSP halaman dengan sandbox.'
);
m107_check(
    strpos($file_security, 'Cache-Control: private, no-transform, no-store, must-revalidate') !== FALSE,
    'Download file privat belum memakai no-store.'
);

fwrite(STDOUT, '[PASS] security headers regression (' . $checks . " checks)\n");
