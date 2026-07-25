#!/usr/bin/env php
<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only.\n");
    exit(1);
}

function m301_execute_sql_script(mysqli $connection, $sql)
{
    $delimiter = ';';
    $buffer = '';
    foreach (preg_split('/\R/', (string) $sql) as $line) {
        if (preg_match('/^\s*--/', $line) === 1) {
            continue;
        }
        if (preg_match('/^\s*DELIMITER\s+(\S+)\s*$/i', $line, $match) === 1) {
            if (trim($buffer) !== '') {
                throw new RuntimeException(
                    'Unexpected DELIMITER change inside an SQL statement.'
                );
            }
            $delimiter = $match[1];
            continue;
        }

        $buffer .= $line . "\n";
        $trimmed = rtrim($buffer);
        $delimiter_length = strlen($delimiter);
        if ($delimiter_length < 1
            || substr($trimmed, -$delimiter_length) !== $delimiter) {
            continue;
        }

        $statement = trim(substr($trimmed, 0, -$delimiter_length));
        $buffer = '';
        if ($statement !== '') {
            $connection->query($statement);
        }
    }

    if (trim($buffer) !== '') {
        throw new RuntimeException('Migration 017 ends with incomplete SQL.');
    }
}

$root = dirname(__DIR__, 2);
$requested_environment = getenv('CI_ENV');
if ($requested_environment !== FALSE
    && strtolower(trim((string) $requested_environment)) === 'production') {
    fwrite(STDERR, "Refusing to apply a local migration in production mode.\n");
    exit(1);
}

define('BASEPATH', $root . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
define('APPPATH', $root . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
define('ENVIRONMENT', 'development');
require APPPATH . 'config' . DIRECTORY_SEPARATOR . 'database.php';

$config = isset($db['default']) ? $db['default'] : [];
$host = isset($config['hostname'])
    ? strtolower(trim((string) $config['hostname']))
    : '';
$database = isset($config['database'])
    ? trim((string) $config['database'])
    : '';

if (!in_array($host, ['localhost', '127.0.0.1', '::1'], TRUE)) {
    fwrite(STDERR, "Refusing non-local database host.\n");
    exit(1);
}
if ($database === '' || preg_match('/^[A-Za-z0-9_]+$/', $database) !== 1) {
    fwrite(STDERR, "Database name is missing or unsafe.\n");
    exit(1);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$port = !empty($config['port'])
    ? (int) $config['port']
    : (int) ini_get('mysqli.default_port');
$connection = new mysqli(
    $config['hostname'],
    $config['username'],
    $config['password'],
    $database,
    $port
);
$connection->set_charset('utf8mb4');

$migration_path = $root . DIRECTORY_SEPARATOR . 'migrations'
    . DIRECTORY_SEPARATOR . '017_create_spmi_versions.sql';
$sql = file_get_contents($migration_path);
if ($sql === FALSE) {
    fwrite(STDERR, "Migration 017 could not be read.\n");
    exit(1);
}

m301_execute_sql_script($connection, $sql);

$columns = [];
$query = $connection->query(
    "SELECT COLUMN_NAME
     FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'spmi_versions'
     ORDER BY ORDINAL_POSITION"
);
while ($row = $query->fetch_assoc()) {
    $columns[] = $row['COLUMN_NAME'];
}
$query->free();

$expected_columns = [
    'id',
    'organization_unit_id',
    'document_code',
    'title',
    'revision_number',
    'effective_date',
    'expires_at',
    'source_file_asset_id',
    'source_file_path',
    'source_file_sha256',
    'status',
    'created_by',
    'approved_by',
    'approved_at',
    'created_at',
    'updated_at',
    'active_slot',
];

$indexes = [];
$query = $connection->query(
    "SELECT DISTINCT INDEX_NAME
     FROM INFORMATION_SCHEMA.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'spmi_versions'"
);
while ($row = $query->fetch_assoc()) {
    $indexes[] = $row['INDEX_NAME'];
}
$query->free();

$foreign_keys = [];
$query = $connection->query(
    "SELECT CONSTRAINT_NAME
     FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
     WHERE CONSTRAINT_SCHEMA = DATABASE()
       AND TABLE_NAME = 'spmi_versions'
     ORDER BY CONSTRAINT_NAME"
);
while ($row = $query->fetch_assoc()) {
    $foreign_keys[] = $row['CONSTRAINT_NAME'];
}
$query->free();

$checks = [];
$query = $connection->query(
    "SELECT CONSTRAINT_NAME
     FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
     WHERE CONSTRAINT_SCHEMA = DATABASE()
       AND TABLE_NAME = 'spmi_versions'
       AND CONSTRAINT_TYPE = 'CHECK'
     ORDER BY CONSTRAINT_NAME"
);
while ($row = $query->fetch_assoc()) {
    $checks[] = $row['CONSTRAINT_NAME'];
}
$query->free();

$triggers = [];
$query = $connection->query(
    "SELECT TRIGGER_NAME
     FROM INFORMATION_SCHEMA.TRIGGERS
     WHERE TRIGGER_SCHEMA = DATABASE()
       AND EVENT_OBJECT_TABLE = 'spmi_versions'
     ORDER BY TRIGGER_NAME"
);
while ($row = $query->fetch_assoc()) {
    $triggers[] = $row['TRIGGER_NAME'];
}
$query->free();
$connection->close();

$required_indexes = [
    'uq_spmi_version_revision',
    'uq_spmi_version_active_slot',
    'uq_spmi_version_source_asset',
    'idx_spmi_version_status_dates',
];
$required_foreign_keys = [
    'fk_spmi_version_approver',
    'fk_spmi_version_creator',
    'fk_spmi_version_source_asset',
    'fk_spmi_version_unit',
];
$required_checks = [
    'chk_spmi_version_approval_state',
    'chk_spmi_version_dates',
    'chk_spmi_version_identity',
    'chk_spmi_version_source_path',
    'chk_spmi_version_source_sha256',
];
$required_triggers = [
    'trg_spmi_versions_active_immutable',
    'trg_spmi_versions_no_delete',
];

$missing_indexes = array_diff($required_indexes, $indexes);
if ($columns !== $expected_columns
    || !empty($missing_indexes)
    || $foreign_keys !== $required_foreign_keys
    || $checks !== $required_checks
    || $triggers !== $required_triggers) {
    fwrite(STDERR, "Migration 017 verification failed.\n");
    exit(1);
}

fwrite(
    STDOUT,
    "Migration 017 applied; spmi_versions columns, indexes, foreign keys, and history guards verified on the configured local database.\n"
);
