#!/usr/bin/env php
<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only.\n");
    exit(1);
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
$host = isset($config['hostname']) ? strtolower(trim((string) $config['hostname'])) : '';
$database = isset($config['database']) ? trim((string) $config['database']) : '';
if (!in_array($host, ['localhost', '127.0.0.1', '::1'], TRUE)) {
    fwrite(STDERR, "Refusing non-local database host.\n");
    exit(1);
}
if ($database === '' || preg_match('/^[A-Za-z0-9_]+$/', $database) !== 1) {
    fwrite(STDERR, "Database name is missing or unsafe.\n");
    exit(1);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$port = !empty($config['port']) ? (int) $config['port'] : (int) ini_get('mysqli.default_port');
$connection = new mysqli(
    $config['hostname'],
    $config['username'],
    $config['password'],
    $database,
    $port
);
$connection->set_charset('utf8mb4');

$migration_path = $root . DIRECTORY_SEPARATOR . 'migrations'
    . DIRECTORY_SEPARATOR . '014_immutable_security_audit_log.sql';
$sql = file_get_contents($migration_path);
if ($sql === FALSE) {
    fwrite(STDERR, "Migration 014 could not be read.\n");
    exit(1);
}

$connection->multi_query($sql);
do {
    $result = $connection->store_result();
    if ($result instanceof mysqli_result) {
        $result->free();
    }
} while ($connection->more_results() && $connection->next_result());

$tables = [];
$query = $connection->query(
    "SELECT TABLE_NAME
     FROM INFORMATION_SCHEMA.TABLES
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME IN ('security_audit_logs', 'security_audit_chain_state')
     ORDER BY TABLE_NAME"
);
while ($row = $query->fetch_assoc()) {
    $tables[] = $row['TABLE_NAME'];
}
$query->free();

$triggers = [];
$query = $connection->query(
    "SELECT TRIGGER_NAME
     FROM INFORMATION_SCHEMA.TRIGGERS
     WHERE TRIGGER_SCHEMA = DATABASE()
       AND TRIGGER_NAME IN (
         'trg_security_audit_logs_no_delete',
         'trg_security_audit_logs_no_update'
       )
     ORDER BY TRIGGER_NAME"
);
while ($row = $query->fetch_assoc()) {
    $triggers[] = $row['TRIGGER_NAME'];
}
$query->free();
$connection->close();

if ($tables !== ['security_audit_chain_state', 'security_audit_logs']
    || $triggers !== [
        'trg_security_audit_logs_no_delete',
        'trg_security_audit_logs_no_update',
    ]) {
    fwrite(STDERR, "Migration 014 verification failed.\n");
    exit(1);
}

fwrite(
    STDOUT,
    "Migration 014 applied and append-only triggers verified on the configured local database.\n"
);
