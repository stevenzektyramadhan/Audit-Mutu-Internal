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
$app_helper = m301_source($root, 'application/helpers/app_helper.php');
$policy = m301_source($root, 'application/libraries/Authorization_policy.php');
$runner = m301_source($root, 'scripts/database/apply_local_m3_01.php');
$workflow = m301_source(
    $root,
    'application/services/Spmi_version_workflow_service.php'
);
$controller = m301_source($root, 'application/controllers/Spmi_versions.php');
$routes = m301_source($root, 'application/config/routes.php');
$sidebar = m301_source($root, 'application/views/layouts/sidebar.php');
$index_view = m301_source(
    $root,
    'application/views/lpmpi/spmi_versions/index.php'
);
$show_view = m301_source(
    $root,
    'application/views/lpmpi/spmi_versions/show.php'
);

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
foreach ([
    'find_for_update',
    'lock_identity',
    'create',
    'update_draft',
    'transition',
    'retire_active_for_identity',
] as $method) {
    m301_check(
        preg_match('/public\s+function\s+' . $method . '\s*\(/', $model) === 1,
        'M3-02 belum menyediakan primitive persistence ' . $method . '.'
    );
}
m301_check(
    preg_match('/public\s+function\s+delete\s*\(/i', $model) !== 1,
    'Workflow M3-02 tidak boleh membuka operasi delete histori versi.'
);

m301_check(
    strpos($file_security, "'spmi_source' => [") !== FALSE
        && strpos($file_security, "'extensions' => ['pdf']") !== FALSE,
    'Kategori source SPMI belum dibatasi hanya PDF.'
);
m301_check(
    strpos($app_helper, "'spmi_source'") !== FALSE,
    'Kategori spmi_source belum diizinkan oleh private storage resolver.'
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

foreach ([
    'create_draft',
    'update_draft',
    'submit_for_review',
    'approve',
    'activate',
    'retire',
    'clone_to_draft',
] as $method) {
    m301_check(
        preg_match('/public\s+function\s+' . $method . '\s*\(/', $workflow) === 1,
        'Workflow M3-02 belum menyediakan operasi ' . $method . '.'
    );
}
m301_check(
    strpos($workflow, "'draft'") !== FALSE
        && strpos($workflow, "'review'") !== FALSE
        && strpos($workflow, "'approved'") !== FALSE
        && strpos($workflow, "'active'") !== FALSE
        && strpos($workflow, "'retired'") !== FALSE,
    'State machine M3-02 belum memuat seluruh status.'
);
m301_check(
    strpos($workflow, "(int) \$locked->created_by === \$actor_user_id") !== FALSE
        && strpos(
            $workflow,
            'Pembuat versi tidak boleh menjadi satu-satunya approver.'
        ) !== FALSE,
    'Separation of duties creator/approver belum dipaksa di service.'
);
m301_check(
    strpos($workflow, 'trans_begin()') !== FALSE
        && strpos($workflow, 'lock_identity(') !== FALSE
        && strpos($workflow, 'retire_active_for_identity(') !== FALSE
        && strpos($workflow, "transition(\$id, 'approved', 'active'") !== FALSE
        && strpos($workflow, 'trans_commit()') !== FALSE,
    'Aktivasi dan retirement belum berada dalam transaction/lock boundary.'
);
m301_check(
    strpos($workflow, "Hanya versi berstatus draft yang dapat diubah.") !== FALSE,
    'Service belum menolak edit versi non-draft.'
);
m301_check(
    strpos($workflow, "'spmi_version_activated'") !== FALSE
        && strpos($workflow, "'spmi_version_retired'") !== FALSE
        && strpos($workflow, "'spmi_version_approved'") !== FALSE,
    'Transisi penting M3-02 belum dicatat ke audit ledger.'
);
m301_check(
    strpos($workflow, "'spmi_version_cloned'") !== FALSE
        && strpos($workflow, "['approved', 'active', 'retired']") !== FALSE,
    'Clone ke draft baru belum dibatasi ke versi sumber yang stabil.'
);
m301_check(
    strpos($file_security, 'public function duplicate(') !== FALSE
        && strpos($file_security, "'storage_scope' => 'private'") !== FALSE
        && strpos($file_security, "'owner_id' => NULL") !== FALSE
        && strpos($file_security, "'file_duplicated'") !== FALSE,
    'Clone belum membuat private file asset baru yang belum terikat.'
);
m301_check(
    strpos($controller, 'CAP_SPMI_VERSION_MANAGE') !== FALSE
        && strpos(
            $controller,
            '_require_capability_in_organization_unit'
        ) !== FALSE,
    'Controller versi belum menerapkan capability dan organization scope guard.'
);
m301_check(
    strpos($controller, 'protected function require_post()') !== FALSE
        && substr_count($controller, '$this->require_post();') >= 4,
    'Mutation endpoint versi belum dipaksa menggunakan POST.'
);
m301_check(
    strpos($controller, "\$this->file_security->upload(") !== FALSE
        && strpos($controller, "\$this->file_security->duplicate(") !== FALSE
        && strpos($controller, "\$this->file_security->download(") !== FALSE,
    'Upload, clone, dan download PDF belum melewati File_security.'
);
foreach ([
    'spmi-versions/submit-review/(:num)',
    'spmi-versions/approve/(:num)',
    'spmi-versions/activate/(:num)',
    'spmi-versions/retire/(:num)',
    'spmi-versions/clone-store/(:num)',
    'spmi-versions/download/(:num)',
] as $route) {
    m301_check(
        strpos($routes, "\$route['" . $route . "']") !== FALSE,
        'Route eksplisit M3-02 belum tersedia: ' . $route . '.'
    );
}
m301_check(
    substr_count($sidebar, "'key' => 'spmi_versions'") === 2
        && substr_count(
            $sidebar,
            "'capability' => Authorization_policy::CAP_SPMI_VERSION_MANAGE"
        ) >= 2,
    'Menu versi SPMI belum tersedia dan terfilter untuk kedua role pengelola.'
);
m301_check(
    strpos($index_view, 'Workflow Versi SPMI') !== FALSE
        && strpos($show_view, 'Versi aktif bersifat read-only') !== FALSE
        && strpos($show_view, "form_open('spmi-versions/approve/") !== FALSE,
    'UI lifecycle M3-02 belum menampilkan workflow/read-only/approval action.'
);

if (!empty($failures)) {
    foreach ($failures as $failure) {
        fwrite(STDERR, '[FAIL] ' . $failure . PHP_EOL);
    }
    exit(1);
}

fwrite(
    STDOUT,
    '[PASS] M3-01/M3-02 SPMI version workflow regression ('
        . $checks
        . " checks)\n"
);
