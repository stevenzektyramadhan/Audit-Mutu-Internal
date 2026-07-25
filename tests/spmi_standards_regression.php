#!/usr/bin/env php
<?php

$root = dirname(__DIR__);
$failures = [];
$checks = 0;

function m303_check($condition, $message)
{
    global $failures, $checks;
    $checks++;
    if (!$condition) {
        $failures[] = $message;
    }
}

function m303_source($root, $path)
{
    $contents = file_get_contents(
        $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path)
    );
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }
    return $contents;
}

$migration = m303_source($root, 'migrations/018_create_spmi_standards.sql');
$schema = m303_source($root, 'database_schema.sql');
$model = m303_source($root, 'application/models/Spmi_standard_model.php');
$service = m303_source($root, 'application/services/Spmi_standard_service.php');
$workflow = m303_source(
    $root,
    'application/services/Spmi_version_workflow_service.php'
);
$controller = m303_source($root, 'application/controllers/Spmi_standards.php');
$versions_controller = m303_source(
    $root,
    'application/controllers/Spmi_versions.php'
);
$routes = m303_source($root, 'application/config/routes.php');
$index_view = m303_source(
    $root,
    'application/views/lpmpi/spmi_standards/index.php'
);
$form_view = m303_source(
    $root,
    'application/views/lpmpi/spmi_standards/form.php'
);
$version_view = m303_source(
    $root,
    'application/views/lpmpi/spmi_versions/show.php'
);
$runner = m303_source($root, 'scripts/database/apply_local_m3_03.php');

$minimum_columns = [
    'id',
    'spmi_version_id',
    'code',
    'name',
    'group_type',
    'standard_type',
    'rationale',
    'definitions',
    'sort_order',
    'active',
    'created_at',
    'updated_at',
];
foreach ([$migration, $schema] as $sql) {
    m303_check(
        strpos($sql, 'CREATE TABLE IF NOT EXISTS `spmi_standards`') !== FALSE,
        'Tabel spmi_standards belum dibuat secara idempotent.'
    );
    foreach ($minimum_columns as $column) {
        m303_check(
            strpos($sql, '`' . $column . '`') !== FALSE,
            'Kolom spmi_standards belum tersedia: ' . $column . '.'
        );
    }
    m303_check(
        strpos(
            $sql,
            'UNIQUE KEY `uq_spmi_standard_code` (`spmi_version_id`, `code`)'
        ) !== FALSE,
        'Kode standar belum unik per versi.'
    );
    m303_check(
        strpos($sql, 'KEY `idx_spmi_standard_order`') !== FALSE
            && strpos($sql, 'KEY `idx_spmi_standard_group`') !== FALSE,
        'Index urutan/kelompok master standar belum lengkap.'
    );
    m303_check(
        strpos(
            $sql,
            'FOREIGN KEY (`spmi_version_id`) REFERENCES `spmi_versions` (`id`)'
        ) !== FALSE
            && strpos($sql, 'ON UPDATE RESTRICT ON DELETE RESTRICT') !== FALSE,
        'Standar belum terikat permanen ke versi SPMI.'
    );
    foreach ([
        'chk_spmi_standard_identity',
        'chk_spmi_standard_sort_order',
        'chk_spmi_standard_active',
        'chk_spmi_standard_group_type',
    ] as $constraint) {
        m303_check(
            strpos($sql, 'CONSTRAINT `' . $constraint . '`') !== FALSE,
            'Constraint master standar belum tersedia: ' . $constraint . '.'
        );
    }
    m303_check(
        strpos($sql, 'trg_spmi_standards_insert_draft') !== FALSE
            && strpos(
                $sql,
                'spmi standards can only be added to a draft version'
            ) !== FALSE,
        'Insert standar belum dibatasi ke versi draft.'
    );
    m303_check(
        strpos($sql, 'trg_spmi_standards_update_draft') !== FALSE
            && strpos(
                $sql,
                'spmi standards are immutable outside a draft version'
            ) !== FALSE,
        'Standar versi non-draft belum immutable.'
    );
    m303_check(
        strpos($sql, 'trg_spmi_standards_no_delete') !== FALSE
            && strpos($sql, 'spmi standard history cannot be deleted') !== FALSE,
        'Histori standar masih dapat dihapus.'
    );
}

m303_check(
    strpos($migration, 'Manual, idempotent upgrade') !== FALSE,
    'Migration 018 belum menyatakan mode manual/idempotent.'
);
m303_check(
    strpos($migration, 'Rollback is intentionally manual and destructive') !== FALSE
        && strpos($migration, 'SELECT COUNT(*) FROM spmi_standards') !== FALSE
        && strpos($migration, 'Never run this rollback automatically') !== FALSE,
    'Rollback aman migration 018 belum didokumentasikan.'
);
m303_check(
    strpos($migration, 'ALTER TABLE `standar`') === FALSE
        && strpos($migration, 'ALTER TABLE `pertanyaan`') === FALSE,
    'M3-03 tidak boleh melakukan cutover destruktif terhadap master legacy.'
);

if (!defined('BASEPATH')) {
    define('BASEPATH', $root . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
}
$config = [];
require $root . DIRECTORY_SEPARATOR . 'application'
    . DIRECTORY_SEPARATOR . 'config'
    . DIRECTORY_SEPARATOR . 'spmi_standard_seed.php';
$seed = isset($config['standards']) ? $config['standards'] : [];

m303_check(count($seed) === 21, 'Seed awal wajib berisi tepat 21 standar.');
$group_counts = [
    'education' => 0,
    'research' => 0,
    'community_service' => 0,
    'internal' => 0,
];
$type_counts = ['sn_dikti' => 0, 'internal' => 0];
$codes = [];
$names = [];
foreach ($seed as $row) {
    $group = isset($row['group_type']) ? $row['group_type'] : '';
    $type = isset($row['standard_type']) ? $row['standard_type'] : '';
    if (isset($group_counts[$group])) {
        $group_counts[$group]++;
    }
    if (isset($type_counts[$type])) {
        $type_counts[$type]++;
    }
    $codes[] = isset($row['code']) ? $row['code'] : '';
    $names[] = isset($row['name']) ? $row['name'] : '';
    m303_check(
        ($group === 'internal' && $type === 'internal')
            || ($group !== 'internal' && $type === 'sn_dikti'),
        'Jenis seed tidak konsisten dengan kelompok: ' . $group . '.'
    );
}
m303_check(
    $group_counts === [
        'education' => 8,
        'research' => 3,
        'community_service' => 3,
        'internal' => 7,
    ],
    'Distribusi seed wajib 8/3/3/7.'
);
m303_check(
    $type_counts === ['sn_dikti' => 14, 'internal' => 7],
    'Seed wajib berisi 14 SN Dikti dan 7 standar internal.'
);
m303_check(
    count(array_unique($codes)) === 21,
    'Seluruh kode seed harus unik.'
);
foreach ([
    'Standar Kompetensi Lulusan',
    'Standar Proses Pembelajaran',
    'Standar Penilaian Pembelajaran',
    'Standar Pengelolaan Pembelajaran',
    'Standar Isi Pembelajaran',
    'Standar Dosen dan Tenaga Kependidikan',
    'Standar Sarana dan Prasarana',
    'Standar Pembiayaan',
    'Standar Luaran Penelitian',
    'Standar Proses Penelitian',
    'Standar Masukan Penelitian',
    'Standar Luaran Pengabdian',
    'Standar Proses Pengabdian',
    'Standar Masukan Pengabdian',
    'Standar Jati Diri',
    'Standar AIK',
    'Standar VMTS',
    'Standar Tata Pamong',
    'Standar Kerja Sama',
    'Standar Kemahasiswaan dan Alumni',
    'Standar Pengelolaan Keuangan',
] as $required_name) {
    m303_check(
        in_array($required_name, $names, TRUE),
        'Seed belum memuat ' . $required_name . '.'
    );
}

foreach ([
    'schema_ready',
    'get_for_version',
    'find',
    'find_for_update',
    'lock_for_version',
    'count_for_version',
    'code_exists',
    'create',
    'insert_batch',
    'update',
    'copy_for_version',
] as $method) {
    m303_check(
        preg_match('/public\s+function\s+' . $method . '\s*\(/', $model) === 1,
        'Model standar belum menyediakan method ' . $method . '.'
    );
}
m303_check(
    preg_match('/public\s+function\s+delete\s*\(/i', $model) !== 1,
    'Model standar tidak boleh menyediakan hard delete.'
);
m303_check(
    strpos($model, 'WHERE spmi_version_id = ?') !== FALSE
        && strpos($model, 'INSERT INTO ') !== FALSE
        && strpos($model, 'ORDER BY sort_order, id') !== FALSE,
    'Clone standar belum dilakukan di persistence layer.'
);

foreach ([
    'seed_defaults',
    'create',
    'update',
    'toggle_active',
    'reorder',
] as $method) {
    m303_check(
        preg_match('/public\s+function\s+' . $method . '\s*\(/', $service) === 1,
        'Service standar belum menyediakan operasi ' . $method . '.'
    );
}
m303_check(
    strpos($service, 'count($configured) !== 21') !== FALSE
        && strpos($service, "'education' => 8") !== FALSE
        && strpos($service, "'internal' => 7") !== FALSE,
    'Service belum memvalidasi jumlah/distribusi seed.'
);
m303_check(
    strpos($service, "!== 'draft'") !== FALSE
        && substr_count($service, 'find_for_update(') >= 4
        && substr_count($service, 'trans_begin()') >= 4,
    'Mutation master standar belum memakai draft/transaction/lock boundary.'
);
m303_check(
    strpos($service, 'code_exists(') !== FALSE
        && strpos($service, 'Kode standar sudah digunakan pada versi ini.') !== FALSE,
    'Keunikan kode per versi belum divalidasi oleh service.'
);
m303_check(
    strpos($service, 'Daftar urutan harus mencakup seluruh standar') !== FALSE
        && strpos($service, 'array_unique(array_values($normalized))') !== FALSE,
    'Reorder belum mewajibkan seluruh ID dengan urutan unik.'
);
m303_check(
    preg_match('/public\s+function\s+delete\s*\(/i', $service) !== 1
        && strpos($service, 'dinonaktifkan tanpa menghapus histori') !== FALSE,
    'Service harus memakai deactivation, bukan delete.'
);
foreach ([
    'spmi_standards_seeded',
    'spmi_standard_created',
    'spmi_standard_updated',
    'spmi_standard_deactivated',
    'spmi_standards_reordered',
] as $event) {
    m303_check(
        strpos($service, "'" . $event . "'") !== FALSE,
        'Audit event standar belum tersedia: ' . $event . '.'
    );
}

m303_check(
    strpos($workflow, "load->model('Spmi_standard_model')") !== FALSE
        && strpos($workflow, 'copy_for_version(') !== FALSE
        && strpos($workflow, '$copied_standard_count') !== FALSE,
    'Clone versi belum ikut menyalin master standar.'
);
m303_check(
    strpos($workflow, 'Jumlah standar hasil clone tidak sesuai versi sumber.') !== FALSE
        && strpos($workflow, "'row_count' => \$copied_standard_count") !== FALSE,
    'Clone belum memverifikasi dan mengaudit jumlah standar.'
);

m303_check(
    strpos($controller, 'CAP_SPMI_STANDARD_MANAGE') !== FALSE
        && strpos(
            $controller,
            '_require_capability_in_organization_unit'
        ) !== FALSE,
    'Controller standar belum memakai capability dan organization scope guard.'
);
m303_check(
    substr_count($controller, '$this->require_post();') >= 4
        && strpos($controller, 'protected function require_draft') !== FALSE,
    'Mutation endpoint standar belum dipaksa POST dan draft.'
);
m303_check(
    strpos($controller, 'spmi_version_id !== (int) $version->id') !== FALSE,
    'Controller belum menolak standard ID dari versi lain.'
);
foreach ([
    'spmi-versions/(:num)/standards',
    'spmi-versions/(:num)/standards/seed',
    'spmi-versions/(:num)/standards/create',
    'spmi-versions/(:num)/standards/store',
    'spmi-versions/(:num)/standards/edit/(:num)',
    'spmi-versions/(:num)/standards/update/(:num)',
    'spmi-versions/(:num)/standards/toggle-active/(:num)',
    'spmi-versions/(:num)/standards/reorder',
] as $route) {
    m303_check(
        strpos($routes, "\$route['" . $route . "']") !== FALSE,
        'Route master standar belum tersedia: ' . $route . '.'
    );
}
m303_check(
    strpos($index_view, 'Muat 21 Standar Awal') !== FALSE
        && strpos($index_view, 'orders[') !== FALSE
        && strpos($index_view, 'Read-only') !== FALSE,
    'UI standar belum menyediakan seed, reorder, dan state read-only.'
);
m303_check(
    strpos($form_view, 'Unik di dalam versi ini') !== FALSE
        && strpos($form_view, 'rationale') !== FALSE
        && strpos($form_view, 'definitions') !== FALSE,
    'Form standar belum menjelaskan scope kode atau field naratif.'
);
m303_check(
    strpos($versions_controller, 'count_for_version(') !== FALSE
        && strpos($version_view, 'Kelola Standar (') !== FALSE,
    'Detail versi belum menampilkan akses dan jumlah standar.'
);

m303_check(
    strpos($runner, "=== 'production'") !== FALSE
        && strpos($runner, "['localhost', '127.0.0.1', '::1']") !== FALSE,
    'Runner migration M3-03 belum dibatasi ke database lokal non-production.'
);
m303_check(
    strpos($runner, '018_create_spmi_standards.sql') !== FALSE,
    'Runner migration belum dikunci ke migration 018.'
);
foreach ([
    'INFORMATION_SCHEMA.COLUMNS',
    'INFORMATION_SCHEMA.STATISTICS',
    'INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS',
    'INFORMATION_SCHEMA.TABLE_CONSTRAINTS',
    'INFORMATION_SCHEMA.TRIGGERS',
] as $source) {
    m303_check(
        strpos($runner, $source) !== FALSE,
        'Runner belum memverifikasi ' . $source . '.'
    );
}

if (!empty($failures)) {
    foreach ($failures as $failure) {
        fwrite(STDERR, '[FAIL] ' . $failure . PHP_EOL);
    }
    exit(1);
}

fwrite(
    STDOUT,
    '[PASS] M3-03 versioned SPMI standard master regression ('
        . $checks
        . " checks)\n"
);
