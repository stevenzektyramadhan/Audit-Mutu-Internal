#!/usr/bin/env php
<?php

$root = dirname(__DIR__);
$failures = [];
$checks = 0;

function encoding_check($condition, $message)
{
    global $failures, $checks;
    $checks++;
    if (!$condition) {
        $failures[] = $message;
    }
}

function encoding_source($root, $path)
{
    $contents = file_get_contents(
        $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path)
    );
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }

    return $contents;
}

if (!defined('BASEPATH')) {
    define('BASEPATH', $root . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
}

require_once $root
    . DIRECTORY_SEPARATOR
    . 'application'
    . DIRECTORY_SEPARATOR
    . 'helpers'
    . DIRECTORY_SEPARATOR
    . 'app_helper.php';

$payload = '<img src=x onerror="alert(1)">';
$encoded = ami_e($payload);
encoding_check(
    $encoded === '&lt;img src=x onerror=&quot;alert(1)&quot;&gt;',
    'ami_e tidak meng-encode HTML dan quoted attribute dengan benar.'
);
encoding_check(
    strpos(ami_e('&quot; onmouseover=alert(2)'), '&amp;quot;') === 0,
    'ami_e harus melakukan double encoding untuk mencegah entity breakout.'
);
encoding_check(
    ami_text("baris 1\n<script>alert(3)</script>")
        === "baris 1<br>\n&lt;script&gt;alert(3)&lt;/script&gt;",
    'ami_text harus mempertahankan baris tanpa mengizinkan HTML.'
);

encoding_check(
    ami_safe_http_url('https://evidence.example/path?q=1') === 'https://evidence.example/path?q=1',
    'URL HTTPS yang valid seharusnya diterima.'
);
foreach ([
    'javascript:alert(1)',
    'data:text/html,<script>alert(1)</script>',
    'file:///etc/passwd',
    'ftp://example.test/file',
    '//example.test/path',
    "https://example.test/\r\nX-Test: injected",
] as $unsafe_url) {
    encoding_check(
        ami_safe_http_url($unsafe_url) === '',
        'Skema/control character URL berbahaya tidak ditolak: ' . $unsafe_url
    );
}

$json = ami_json(['value' => '</script><img onerror="alert(4)">']);
encoding_check(stripos($json, '</script>') === FALSE, 'ami_json membiarkan penutup script mentah.');
encoding_check(stripos($json, '<img') === FALSE, 'ami_json membiarkan tag HTML mentah.');
encoding_check(strpos($json, '\\u003C') !== FALSE, 'ami_json tidak memakai JSON_HEX_TAG.');

$view_files = array_merge(
    glob($root . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . '*.php'),
    glob($root . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*.php'),
    glob($root . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*.php')
);

$business_views = '';
foreach ($view_files as $file) {
    if (strpos(str_replace('\\', '/', $file), '/views/errors/') !== FALSE) {
        continue;
    }

    $business_views .= (string) file_get_contents($file);
}

encoding_check(strpos($business_views, 'html_escape(') === FALSE, 'Business view masih melewati helper ami_e().');
encoding_check(strpos($business_views, 'ami_e(') !== FALSE, 'Helper HTML terpusat tidak digunakan oleh view.');
encoding_check(strpos($business_views, 'json_encode(') === FALSE, 'View masih menanam JSON tanpa ami_json().');
encoding_check(
    preg_match('/href="<\?php echo [^;]*(?:->link_bukti|\$current_link)/', $business_views) !== 1,
    'URL bukti masih langsung dimasukkan ke href tanpa allowlist.'
);
encoding_check(
    preg_match('/echo\s+set_value\s*\(/', $business_views) !== 1,
    'Nilai form masih bergantung pada escaping implisit set_value().'
);

$auditor_view = encoding_source($root, 'application/views/auditor/form_penilaian.php');
$profile_view = encoding_source($root, 'application/views/lpmpi/profil/index.php');
$report_controller = encoding_source($root, 'application/controllers/lpmpi/Laporan.php');
$exceptions = encoding_source($root, 'application/core/MY_Exceptions.php');
$exception_view = encoding_source($root, 'application/views/errors/html/error_exception.php');

foreach ([
    '$item->jawaban',
    '$item->temuan',
    '$item->saran_perbaikan',
    '$item->rencana_perbaikan',
    '$dokumen_bukti',
] as $field) {
    encoding_check(
        strpos($auditor_view, 'ami_e(' . $field) !== FALSE
            || strpos($auditor_view, 'ami_text(' . $field) !== FALSE,
        'Field berisiko belum melalui output encoding: ' . $field
    );
}

encoding_check(
    strpos($profile_view, 'ami_safe_http_url($profil->logo_url)') !== FALSE,
    'URL logo eksternal belum memakai allowlist HTTP(S).'
);
encoding_check(
    strpos($report_controller, 'setCellValueExplicit') !== FALSE
        && strpos($report_controller, 'DataType::TYPE_STRING') !== FALSE,
    'Export Excel belum memaksa input teks menjadi cell string.'
);
encoding_check(
    strpos($report_controller, '$value = ami_safe_http_url($value);') !== FALSE,
    'Hyperlink export belum memakai allowlist HTTP(S).'
);
encoding_check(
    strpos($exceptions, '$this->encode_output($message)') !== FALSE,
    'Pesan show_error belum di-encode sebelum template framework.'
);
encoding_check(
    strpos($exception_view, 'htmlspecialchars((string) $message') !== FALSE,
    'Detail exception development masih dirender raw.'
);

if (!empty($failures)) {
    foreach ($failures as $failure) {
        fwrite(STDERR, '[FAIL] ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo '[PASS] output encoding regression (' . $checks . ' checks)' . PHP_EOL;
exit(0);
