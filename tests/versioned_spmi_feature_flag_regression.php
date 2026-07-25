#!/usr/bin/env php
<?php

$root = dirname(__DIR__);
$failures = [];
$checks = 0;

function m303a_check($condition, $message)
{
    global $failures, $checks;
    $checks++;
    if (!$condition) {
        $failures[] = $message;
    }
}

function m303a_source($root, $path)
{
    $contents = file_get_contents(
        $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path)
    );
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }

    return $contents;
}

function m303a_flag_value($root, $environment_value)
{
    if ($environment_value === NULL) {
        putenv('FEATURE_VERSIONED_SPMI');
    } else {
        putenv('FEATURE_VERSIONED_SPMI=' . $environment_value);
    }

    $config = [];
    require $root . DIRECTORY_SEPARATOR . 'application'
        . DIRECTORY_SEPARATOR . 'config'
        . DIRECTORY_SEPARATOR . 'features.php';

    return isset($config['feature_versioned_spmi'])
        ? $config['feature_versioned_spmi']
        : NULL;
}

if (!defined('BASEPATH')) {
    define('BASEPATH', $root . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
}

$previous_flag = getenv('FEATURE_VERSIONED_SPMI');

try {
    m303a_check(
        m303a_flag_value($root, NULL) === FALSE,
        'Versioned SPMI harus default OFF ketika environment tidak tersedia.'
    );
    foreach (['0', 'false', 'off', 'invalid'] as $disabled_value) {
        m303a_check(
            m303a_flag_value($root, $disabled_value) === FALSE,
            'Nilai flag harus fail-closed: ' . $disabled_value . '.'
        );
    }
    foreach (['1', 'true', 'on'] as $enabled_value) {
        m303a_check(
            m303a_flag_value($root, $enabled_value) === TRUE,
            'Nilai flag ON tidak dikenali: ' . $enabled_value . '.'
        );
    }
} finally {
    if ($previous_flag === FALSE) {
        putenv('FEATURE_VERSIONED_SPMI');
    } else {
        putenv('FEATURE_VERSIONED_SPMI=' . $previous_flag);
    }
}

$autoload = m303a_source($root, 'application/config/autoload.php');
$sidebar = m303a_source($root, 'application/views/layouts/sidebar.php');
$versions_controller = m303a_source(
    $root,
    'application/controllers/Spmi_versions.php'
);
$standards_controller = m303a_source(
    $root,
    'application/controllers/Spmi_standards.php'
);
$routes = m303a_source($root, 'application/config/routes.php');
$legacy_controller = m303a_source($root, 'application/controllers/Standar.php');
$legacy_service = m303a_source($root, 'application/services/Standar_service.php');
$documentation = m303a_source(
    $root,
    'docs/milestones/M3-03-master-21-standar.md'
);

m303a_check(
    strpos($autoload, "'features'") !== FALSE,
    'Config feature flag belum diautoload.'
);
m303a_check(
    substr_count($sidebar, "'feature' => 'feature_versioned_spmi'") === 2,
    'Menu Versi SPMI untuk role pengelola belum ditandai feature flag.'
);
m303a_check(
    strpos(
        $sidebar,
        "isset(\$menu['feature']) && \$this->config->item(\$menu['feature']) !== TRUE"
    ) !== FALSE,
    'Sidebar belum menyembunyikan menu ketika feature flag OFF.'
);

foreach ([
    'Spmi_versions' => $versions_controller,
    'Spmi_standards' => $standards_controller,
] as $name => $controller) {
    $guard_position = strpos(
        $controller,
        "\$this->config->item('feature_versioned_spmi') !== TRUE"
    );
    $authorization_position = strpos($controller, '$this->_require_capability(');

    m303a_check(
        $guard_position !== FALSE
            && strpos($controller, 'show_404();', $guard_position) !== FALSE,
        $name . ' belum menolak direct URL dengan 404 ketika flag OFF.'
    );
    m303a_check(
        $authorization_position !== FALSE
            && $guard_position < $authorization_position,
        $name . ' harus memeriksa flag sebelum authorization agar Super Admin juga diblokir.'
    );
}

m303a_check(
    substr_count($routes, "= 'Spmi_versions/") >= 1
        && substr_count($routes, "= 'Spmi_standards/") >= 1,
    'Route Versioned SPMI tidak seluruhnya menuju controller yang dijaga flag.'
);
m303a_check(
    strpos($legacy_controller, 'class Standar extends CI_Controller') !== FALSE
        && strpos($legacy_controller, 'Standar_service.php') !== FALSE
        && strpos($legacy_controller, 'feature_versioned_spmi') === FALSE,
    'Workflow Data Standar legacy ikut berubah atau terikat ke feature flag.'
);
m303a_check(
    strpos($legacy_service, "load->model('Standar_model')") !== FALSE
        && strpos($legacy_service, 'Spmi_standard_model') === FALSE,
    'Data Standar legacy tidak lagi memakai source of truth legacy.'
);
m303a_check(
    preg_match(
        "/'key'\\s*=>\\s*'standar'[^\\r\\n]*'feature'/",
        $sidebar
    ) !== 1,
    'Menu Data Standar legacy tidak boleh ikut disembunyikan oleh flag.'
);

$migration_files = glob(
    $root . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . '*.sql'
);
$migration_names = array_map('basename', is_array($migration_files) ? $migration_files : []);
sort($migration_names, SORT_STRING);
m303a_check(
    in_array('018_create_spmi_standards.sql', $migration_names, TRUE),
    'Migration 018 harus tetap dipertahankan.'
);
m303a_check(
    count(array_filter($migration_names, function ($name) {
        return preg_match('/^019_/', $name) === 1;
    })) === 0,
    'Containment feature flag tidak boleh menambahkan migration database.'
);

m303a_check(
    strpos(
        $documentation,
        'IMPLEMENTED — DEFERRED — NOT CANONICAL FOR MVP'
    ) !== FALSE,
    'Status produk deferred/non-canonical belum didokumentasikan.'
);
m303a_check(
    strpos($documentation, 'source of truth MVP') !== FALSE
        && strpos($documentation, 'FEATURE_VERSIONED_SPMI') !== FALSE,
    'Dokumentasi belum menetapkan tabel standar legacy sebagai source of truth MVP.'
);

if (!empty($failures)) {
    foreach ($failures as $failure) {
        fwrite(STDERR, '[FAIL] ' . $failure . PHP_EOL);
    }
    exit(1);
}

fwrite(
    STDOUT,
    '[PASS] M3-03A Versioned SPMI feature flag regression ('
        . $checks
        . " checks)\n"
);
