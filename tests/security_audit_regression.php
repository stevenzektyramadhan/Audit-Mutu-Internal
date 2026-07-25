<?php

$root = dirname(__DIR__);
$checks = 0;

function m108_check($condition, $message)
{
    global $checks;
    $checks++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function m108_source($root, $path)
{
    $source = file_get_contents($root . DIRECTORY_SEPARATOR . $path);
    if ($source === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }
    return $source;
}

$migration = m108_source($root, 'migrations/014_immutable_security_audit_log.sql');
$schema = m108_source($root, 'database_schema.sql');
$model = m108_source($root, 'application/models/Security_audit_log_model.php');
$logger = m108_source($root, 'application/libraries/Audit_logger.php');
$maintenance = m108_source($root, 'application/controllers/Maintenance.php');
$runner = m108_source($root, 'scripts/database/apply_local_m1_08.php');
$routes = m108_source($root, 'application/config/routes.php');

foreach ([$migration, $schema] as $sql) {
    foreach ([
        'security_audit_logs',
        'security_audit_chain_state',
        '`event_uuid`',
        '`actor_user_id`',
        '`event_type`',
        '`object_type`',
        '`object_id`',
        '`action`',
        '`outcome`',
        '`before_hash`',
        '`after_hash`',
        '`changes_json`',
        '`ip_address`',
        '`user_agent`',
        '`request_id`',
        '`previous_hash`',
        '`entry_hash`',
        '`created_at`',
        'trg_security_audit_logs_no_update',
        'trg_security_audit_logs_no_delete',
        "SIGNAL SQLSTATE '45000'",
        'INSERT IGNORE INTO `security_audit_chain_state`',
    ] as $required) {
        m108_check(strpos($sql, $required) !== FALSE, 'Schema audit belum memuat ' . $required);
    }
}

m108_check(
    strpos($migration, 'FOREIGN KEY (`actor_user_id`)') === FALSE,
    'Actor audit tidak boleh hilang akibat FK user delete.'
);
m108_check(
    substr_count($migration, 'DROP TRIGGER IF EXISTS') === 2,
    'Migration harus dapat memasang ulang kedua trigger secara idempotent.'
);
m108_check(strpos($model, 'FOR UPDATE') !== FALSE, 'Writer chain belum diserialisasi.');
m108_check(strpos($model, 'GENESIS_HASH') !== FALSE, 'Genesis hash belum eksplisit.');
m108_check(strpos($model, 'calculate_entry_hash') !== FALSE, 'Entry hash belum dihitung.');
m108_check(strpos($model, 'verify_chain') !== FALSE, 'Verifier chain belum tersedia.');
m108_check(strpos($model, "order_by('id', 'ASC')") !== FALSE, 'Verifier tidak membaca urutan append.');
m108_check(
    preg_match('/public\s+function\s+(?:update|delete)\s*\(/i', $model) !== 1,
    'Model ledger tidak boleh menyediakan update/delete.'
);
m108_check(strpos($model, '$this->db->insert($this->table') !== FALSE, 'Model tidak menyediakan append insert.');
m108_check(strpos($model, 'previous_hash') !== FALSE && strpos($model, 'entry_hash') !== FALSE, 'Hash chain tidak lengkap.');

foreach ([
    'hash_hmac(',
    'hmac-sha256:',
    'sanitize_metadata',
    'metadata_allowlist',
    'normalize_snapshot',
    'pass|token|secret|cookie|authorization|csrf|session',
    'audit_ledger_unavailable',
    'audit_append_failed',
] as $required) {
    m108_check(strpos($logger, $required) !== FALSE, 'Audit logger belum memuat kontrol ' . $required);
}
m108_check(strpos($logger, '$_POST') === FALSE, 'Audit logger tidak boleh membaca body POST.');
m108_check(strpos($logger, "input->post") === FALSE, 'Audit logger tidak boleh membaca body request.');
m108_check(strpos($logger, 'HTTP_COOKIE') === FALSE, 'Audit logger tidak boleh membaca cookie mentah.');
m108_check(strpos($logger, "'password'") === FALSE, 'Metadata audit tidak boleh memiliki field password.');

foreach ([
    'reason_code',
    'role_from',
    'role_to',
    'status_from',
    'status_to',
    'row_count',
    'format',
    'category',
    'changed_fields',
    'source',
    'file_asset_id',
    'scope',
] as $metadata_key) {
    m108_check(strpos($logger, "'" . $metadata_key . "'") !== FALSE, 'Metadata allowlist kurang ' . $metadata_key);
}

$integration_expectations = [
    'application/libraries/Auth_security.php' => [
        'audit_logger',
        'login_failed',
        'login_succeeded',
        'logout',
    ],
    'application/libraries/File_security.php' => [
        'audit_logger',
        'upload_succeeded',
        'file_retired',
        'download_succeeded',
    ],
    'application/services/User_service.php' => [
        'user_role_changed',
        'user_created',
        'user_deleted',
    ],
    'application/services/Standar_service.php' => [
        'standard_created',
        'standard_updated',
        'standard_deleted',
    ],
    'application/services/Pertanyaan_service.php' => [
        'indicator_created',
        'indicator_updated',
        'indicator_deleted',
        'indicator_bulk_imported',
    ],
    'application/services/Tugas_audit_service.php' => [
        'assignment_created',
        'assignment_deleted',
    ],
    'application/services/Periode_service.php' => [
        'audit_period_activated',
        'audit_period_deactivated',
    ],
    'application/models/Jawaban_model.php' => [
        'auditee_submission_submitted',
        'auditor_assessment_submitted',
        'auditee_revision_requested',
    ],
    'application/controllers/lpmpi/Laporan.php' => [
        'sensitive_report_exported',
        "'format' => 'xlsx'",
    ],
];

foreach ($integration_expectations as $path => $needles) {
    $source = m108_source($root, $path);
    foreach ($needles as $needle) {
        m108_check(strpos($source, $needle) !== FALSE, $path . ' belum mencatat ' . $needle);
    }
}

m108_check(strpos($maintenance, 'verify_audit_log') !== FALSE, 'CLI chain verifier belum tersedia.');
m108_check(strpos($maintenance, 'verify_chain') !== FALSE, 'CLI belum memanggil chain verifier.');
m108_check(strpos($runner, "CI_ENV") !== FALSE && strpos($runner, 'production') !== FALSE, 'Runner lokal belum menolak production.');
m108_check(strpos($runner, "['localhost', '127.0.0.1', '::1']") !== FALSE, 'Runner lokal belum membatasi host DB.');
m108_check(strpos($runner, 'INFORMATION_SCHEMA.TRIGGERS') !== FALSE, 'Runner belum memverifikasi trigger.');
m108_check(stripos($routes, 'security_audit_logs') === FALSE, 'Ledger tidak boleh memiliki route mutasi publik.');

$view_matches = [];
$view_iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(
        $root . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views',
        FilesystemIterator::SKIP_DOTS
    )
);
foreach ($view_iterator as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
        $source = file_get_contents($file->getPathname());
        if ($source !== FALSE && stripos($source, 'security_audit_logs') !== FALSE) {
            $view_matches[] = $file->getPathname();
        }
    }
}
m108_check(empty($view_matches), 'UI tidak boleh menyediakan edit/delete ledger.');

fwrite(STDOUT, '[PASS] immutable security audit regression (' . $checks . " checks)\n");
