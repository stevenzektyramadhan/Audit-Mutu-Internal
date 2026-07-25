#!/usr/bin/env php
<?php

$root = dirname(__DIR__);
$failures = [];
$checks = 0;

function m301_check($condition, $message)
{
    global $failures, $checks;
    $checks++;
    if (!$condition) {
        $failures[] = $message;
    }
}

function m301_source($root, $path)
{
    $contents = file_get_contents(
        $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path)
    );
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }

    return $contents;
}

$migration = m301_source($root, 'migrations/017_create_spmi_versions.sql');
$schema = m301_source($root, 'database_schema.sql');
$model = m301_source($root, 'application/models/Spmi_version_model.php');
$file_security = m301_source($root, 'application/libraries/File_security.php');
$policy = m301_source($root, 'application/libraries/Authorization_policy.php');
$runner = m301_source($root, 'scripts/database/apply_local_m3_01.php');

$minimum_columns = [
    'id',
    'document_code',
    'title',
    'revision_number',
    'effective_date',
    'expires_at',
    'source_file_path',
    'source_file_sha256',
    'status',
    'created_by',
    'approved_by',
    'approved_at',
    'created_at',
    'updated_at',
];

foreach ([$migration, $schema] as $sql) {
    m301_check(
        strpos($sql, 'CREATE TABLE IF NOT EXISTS `spmi_versions`') !== FALSE,
        'Tabel spmi_versions belum tersedia secara idempotent.'
    );
    foreach ($minimum_columns as $column) {
        m301_check(
            strpos($sql, '`' . $column . '`') !== FALSE,
            'Kolom minimum spmi_versions tidak tersedia: ' . $column . '.'
        );
    }

    m301_check(
        strpos($sql, '`organization_unit_id` BIGINT UNSIGNED NOT NULL') !== FALSE,
        'Versi SPMI belum mempunyai organization scope stabil.'
    );
    m301_check(
        strpos($sql, '`source_file_asset_id` BIGINT UNSIGNED NOT NULL') !== FALSE,
        'Versi SPMI belum terhubung ke private file registry.'
    );
    m301_check(
        strpos(
            $sql,
            "ENUM('draft','review','approved','active','retired')"
        ) !== FALSE,
        'Lifecycle status versi SPMI tidak lengkap.'
    );
    m301_check(
        strpos($sql, 'UNIQUE KEY `uq_spmi_version_revision`') !== FALSE,
        'Nomor revisi belum unik per identitas dan scope.'
    );
    m301_check(
        strpos($sql, 'UNIQUE KEY `uq_spmi_version_active_slot`') !== FALSE
            && strpos($sql, "CASE WHEN `status` = 'active' THEN 1 ELSE NULL END")
                !== FALSE,
        'Single-active invariant belum dijaga dengan active slot.'
    );
    m301_check(
        strpos($sql, 'UNIQUE KEY `uq_spmi_version_source_asset`') !== FALSE,
        'Satu private asset masih dapat dipakai sebagai sumber beberapa versi.'
    );
    m301_check(
        strpos(
            $sql,
            'FOREIGN KEY (`organization_unit_id`) REFERENCES `organization_units` (`id`)'
        ) !== FALSE,
        'Foreign key scope organisasi belum tersedia.'
    );
    m301_check(
        strpos(
            $sql,
            'FOREIGN KEY (`source_file_asset_id`) REFERENCES `file_assets` (`id`)'
        ) !== FALSE,
        'Foreign key private source asset belum tersedia.'
    );
    m301_check(
        substr_count($sql, 'ON UPDATE RESTRICT ON DELETE RESTRICT') >= 4,
        'Referential history spmi_versions belum memakai RESTRICT.'
    );
    m301_check(
        strpos($sql, 'CONSTRAINT `chk_spmi_version_dates`') !== FALSE
            && strpos(
                $sql,
                '`expires_at` IS NULL OR `expires_at` >= `effective_date`'
            ) !== FALSE,
        'Rentang masa berlaku belum divalidasi.'
    );
    m301_check(
        strpos($sql, 'CONSTRAINT `chk_spmi_version_source_path`') !== FALSE
            && strpos(
                $sql,
                "^spmi_source/[0-9a-f]{48}[.]pdf$"
            ) !== FALSE,
        'Source path belum dibatasi ke opaque private PDF.'
    );
    m301_check(
        strpos($sql, 'CONSTRAINT `chk_spmi_version_source_sha256`') !== FALSE
            && strpos($sql, "^[0-9a-f]{64}$") !== FALSE,
        'Checksum SHA-256 belum divalidasi.'
    );
    m301_check(
        strpos($sql, 'CONSTRAINT `chk_spmi_version_approval_state`') !== FALSE
            && strpos($sql, "`status` IN ('draft', 'review')") !== FALSE
            && strpos(
                $sql,
                "`status` IN ('approved', 'active', 'retired')"
            ) !== FALSE,
        'Approval provenance belum konsisten dengan lifecycle status.'
    );
    m301_check(
        strpos($sql, 'trg_spmi_versions_active_immutable') !== FALSE
            && strpos($sql, "OLD.`status` = 'active'") !== FALSE,
        'Konten versi aktif belum dilindungi dari update.'
    );
    m301_check(
        strpos($sql, 'trg_spmi_versions_no_delete') !== FALSE
            && strpos($sql, "SIGNAL SQLSTATE '45000'") !== FALSE,
        'Histori versi masih dapat dihapus langsung.'
    );
}

m301_check(
    strpos($migration, 'Manual, idempotent upgrade') !== FALSE,
    'Migration 017 belum menyatakan mode upgrade manual/idempotent.'
);
m301_check(
    strpos($migration, 'Rollback is intentionally manual and destructive') !== FALSE
        && strpos($migration, 'SELECT COUNT(*) FROM spmi_versions') !== FALSE
        && strpos($migration, 'Never run this rollback automatically') !== FALSE,
    'Prosedur rollback aman migration 017 belum didokumentasikan.'
);
m301_check(
    strpos($migration, 'ALTER TABLE `standar`') === FALSE
        && strpos($migration, 'ALTER TABLE `pertanyaan`') === FALSE,
    'M3-01 tidak boleh melakukan cutover destruktif terhadap master legacy.'
);
m301_check(
    strpos($schema, 'CREATE TABLE IF NOT EXISTS `file_assets`')
        < strpos($schema, 'CREATE TABLE IF NOT EXISTS `spmi_versions`'),
    'Fresh schema membuat spmi_versions sebelum dependensi file_assets.'
);

foreach ([
    'schema_ready',
    'find',
    'get_for_organization_unit',
    'find_active',
    'revision_exists',
] as $method) {
    m301_check(
        preg_match('/public\s+function\s+' . $method . '\s*\(/', $model) === 1,
        'Model versi SPMI belum menyediakan method ' . $method . '.'
    );
}
m301_check(
    strpos($model, "->where('spmi_versions.status', 'active')") !== FALSE
        && strpos($model, "'spmi_versions.effective_date <='") !== FALSE
        && strpos($model, "'spmi_versions.expires_at IS NULL'") !== FALSE
        && strpos($model, "'spmi_versions.expires_at >='") !== FALSE,
    'Active version lookup belum effective-dated.'
);
m301_check(
    strpos($model, 'spmi_versions.organization_unit_id') !== FALSE,
    'Query model belum dibatasi dengan organization unit.'
);
m301_check(
    preg_match('/public\s+function\s+(create|update|delete)\s*\(/i', $model) !== 1,
    'M3-01 belum boleh membuka mutation model sebelum workflow M3-02.'
);

m301_check(
    strpos($file_security, "'spmi_source' => [") !== FALSE
        && strpos($file_security, "'extensions' => ['pdf']") !== FALSE,
    'Kategori source SPMI belum dibatasi hanya PDF.'
);
m301_check(
    preg_match(
        "/'spmi_source'\\s*=>\\s*\\[[\\s\\S]*?'scope'\\s*=>\\s*'private'/",
        $file_security
    ) === 1,
    'Source SPMI belum dipaksa ke private storage.'
);
m301_check(
    strpos($file_security, "hash_file('sha256'") !== FALSE
        && strpos($file_security, 'hash_equals') !== FALSE,
    'Upload/download source belum memakai verifikasi checksum terpusat.'
);
m301_check(
    strpos($policy, "CAP_SPMI_VERSION_MANAGE = 'spmi.version.manage'") !== FALSE
        && strpos(
            $policy,
            'self::CAP_SPMI_VERSION_MANAGE => self::SCOPE_ORGANIZATION'
        ) !== FALSE,
    'Capability pengelolaan versi belum organization-scoped.'
);

m301_check(
    strpos($runner, "=== 'production'") !== FALSE,
    'Runner migration lokal tidak menolak production.'
);
m301_check(
    strpos($runner, "['localhost', '127.0.0.1', '::1']") !== FALSE,
    'Runner migration lokal tidak membatasi host database.'
);
m301_check(
    strpos($runner, '017_create_spmi_versions.sql') !== FALSE,
    'Runner lokal tidak dikunci ke migration 017.'
);
foreach ([
    'INFORMATION_SCHEMA.COLUMNS',
    'INFORMATION_SCHEMA.STATISTICS',
    'INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS',
    'INFORMATION_SCHEMA.TRIGGERS',
] as $verification_source) {
    m301_check(
        strpos($runner, $verification_source) !== FALSE,
        'Runner belum memverifikasi ' . $verification_source . '.'
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
    '[PASS] M3-01 SPMI version foundation regression ('
        . $checks
        . " checks)\n"
);
