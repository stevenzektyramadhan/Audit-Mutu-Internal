<?php

function m17_source($path)
{
    $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path);
    if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path);
    return $value;
}

function m17_optional_source($path)
{
    $full_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $path;
    if (!is_file($full_path)) return '';

    $value = file_get_contents($full_path);
    if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path);
    return $value;
}

function m17_check($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

function m17_table_block($schema, $table)
{
    $pattern = '/CREATE TABLE IF NOT EXISTS `' . preg_quote($table, '/') . '` \((?P<body>.*?)\) ENGINE=InnoDB/s';
    if (!preg_match($pattern, $schema, $matches)) {
        throw new RuntimeException('M17-01 schema table missing: ' . $table);
    }

    return $matches['body'];
}

function m17_first_table_block($schema, $tables, $message)
{
    foreach ($tables as $table) {
        $pattern = '/CREATE TABLE IF NOT EXISTS `' . preg_quote($table, '/') . '` \((?P<body>.*?)\) ENGINE=InnoDB/s';
        if (preg_match($pattern, $schema, $matches)) return $matches['body'];
    }

    throw new RuntimeException($message);
}

function m17_has_column($table_body, $name, $shape)
{
    return preg_match('/`' . preg_quote($name, '/') . '`\s+' . $shape . '/i', $table_body) === 1;
}

function m17_has_no_statement($source, $verb, $message)
{
    m17_check(preg_match('/(?:^|[;$])\s*' . preg_quote($verb, '/') . '\b/i', $source) !== 1, $message);
}

$schema = m17_source('database_schema.sql');
$migration_contract = m17_source('migrations/018_create_spmi_auditee_workspace.sql')
    . "\n" . m17_source('migrations/019_create_spmi_auditor_workspace.sql')
    . "\n" . m17_source('migrations/020_create_spmi_reports.sql')
    . "\n" . m17_source('migrations/021_create_spmi_rtm_meetings.sql')
    . "\n" . m17_source('migrations/022_create_spmi_rtm_follow_ups.sql')
    . "\n" . m17_source('migrations/023_create_legacy_ami_archive.sql')
    . "\n" . m17_source('migrations/024_create_audit_logs.sql')
    . "\n" . m17_optional_source('migrations/025_create_spmi_m17_schema_foundation.sql');

// Given: the canonical schema and additive migration artifacts for M17-01.
$instrument_questions = m17_table_block($schema, 'spmi_instrument_questions');
$submission_items = m17_table_block($schema, 'spmi_auditee_submission_items');
$submissions = m17_table_block($schema, 'spmi_auditee_submissions');
$assessments = m17_table_block($schema, 'spmi_auditor_assessments');
$assessment_items = m17_table_block($schema, 'spmi_auditor_assessment_items');
$report_items = m17_table_block($schema, 'spmi_report_items');

// When: reading only static schema artifacts, without executing migrations or application behavior.
$combined_schema = $schema . "\n" . $migration_contract;
$m17_01a_migration = m17_optional_source('migrations/027_add_revision_lifecycle_schema_correction.sql');

// Then: M17-01 provides dormant, backward-compatible schema for M17-02 through M17-06.
m17_check(
    m17_has_column($instrument_questions, 'evidence_policy', "ENUM\('none','file','url','either','both'\) NOT NULL DEFAULT 'none'"),
    'M17-01 instrument-question evidence_policy must be ENUM none/file/url/either/both with backward-compatible default none.'
);

m17_check(
    m17_has_column($submission_items, 'evidence_url', 'VARCHAR\(500\) NULL'),
    'M17-01 submission item evidence_url must be nullable so existing drafts remain valid.'
);
m17_check(m17_has_column($submissions, 'version', 'INT UNSIGNED NOT NULL DEFAULT 1'), 'M17-01 must preserve submission version default 1.');

$revision_history = m17_first_table_block($combined_schema, ['spmi_auditee_submission_revision_events', 'spmi_auditee_submission_histories', 'spmi_auditee_submission_history'], 'M17-01 immutable submission revision event/history storage table missing.');
foreach (['submission_id', 'assignment_id', 'actor_user_id', 'reason', 'submission_version', 'created_at'] as $literal) {
    m17_check(strpos($revision_history, '`' . $literal . '`') !== FALSE, 'M17-01 revision history field missing: ' . $literal);
}
m17_check(strpos($revision_history, 'ON DELETE RESTRICT') !== FALSE, 'M17-01 revision/history storage must preserve immutable RESTRICT semantics.');

m17_check(
    m17_has_column($assessment_items, 'finding_type', "ENUM\('ob','kts'\) NULL"),
    'M17-01 assessment item finding_type must be nullable ENUM ob/kts.'
);

m17_check(
    m17_has_column($report_items, 'finding_type_snapshot', "ENUM\('ob','kts'\) NULL")
        || m17_has_column($report_items, 'finding_type_snapshot', 'VARCHAR\(32\) NULL'),
    'M17-01 report item finding_type_snapshot must be dormant and nullable.'
);
foreach (['realization_snapshot', 'evidence_url_snapshot', 'evidence_file_original_name_snapshot', 'evidence_file_mime_type_snapshot', 'evidence_file_size_bytes_snapshot', 'evidence_file_sha256_snapshot'] as $column) {
    m17_check(strpos($report_items, '`' . $column . '`') !== FALSE, 'M17-01 report item required realization/evidence snapshot metadata missing: ' . $column);
}

foreach (['ALTER TABLE `pertanyaan`', 'ALTER TABLE `tugas_audit`', 'ALTER TABLE `jawaban_audit`'] as $legacy_mutation) {
    m17_check(strpos($migration_contract, $legacy_mutation) === FALSE, 'M17-01 must not mutate legacy table: ' . $legacy_mutation);
}

// Given: the user-authorized M17-01A schema correction contract is additive and source-only.
// When: inspecting canonical schema and the next raw SQL migration without executing database/runtime behavior.
// Then: canonical submission lifecycle, revision provenance, and assessment provenance are statically present.
m17_check(
    m17_has_column($submissions, 'status', "ENUM\('draft','submitted','returned_for_revision','resubmitted','under_assessment','completed'\) NOT NULL DEFAULT 'draft'"),
    'M17-01A canonical submission status must allow draft/submitted/returned_for_revision/resubmitted/under_assessment/completed.'
);
m17_check(m17_has_column($submissions, 'version', 'INT UNSIGNED NOT NULL DEFAULT 1'), 'M17-01A must preserve submission version as INT UNSIGNED NOT NULL DEFAULT 1.');

foreach (['submission_id', 'assignment_id', 'actor_user_id', 'reason', 'created_at', 'previous_status', 'new_status', 'previous_version', 'resulting_version'] as $literal) {
    m17_check(strpos($revision_history, '`' . $literal . '`') !== FALSE, 'M17-01A revision lifecycle field missing: ' . $literal);
}

m17_check(
    m17_has_column($assessments, 'source_submission_version', 'INT UNSIGNED NULL'),
    'M17-01A auditor assessments must store nullable source_submission_version INT UNSIGNED provenance.'
);

m17_check(strpos($m17_01a_migration, 'INFORMATION_SCHEMA.COLUMNS') !== FALSE, 'M17-01A migration 027 must use additive idempotent column guards.');
m17_check(strpos($m17_01a_migration, 'ALTER TABLE `spmi_auditee_submissions`') !== FALSE, 'M17-01A migration 027 must additively correct canonical submission status.');
foreach (['spmi_auditee_submission_revision_events', 'spmi_auditor_assessments'] as $table) {
    m17_check(strpos($m17_01a_migration, "'" . $table . "'") !== FALSE, 'M17-01A migration 027 must use guarded additive changes for ' . $table . '.');
}
foreach (['INSERT', 'UPDATE', 'DELETE'] as $verb) {
    m17_has_no_statement($m17_01a_migration, $verb, 'M17-01A migration 027 must not perform historical backfill/DML: ' . $verb);
}
foreach (['ALTER TABLE `pertanyaan`', 'ALTER TABLE `tugas_audit`', 'ALTER TABLE `jawaban_audit`'] as $legacy_mutation) {
    m17_check(strpos($m17_01a_migration, $legacy_mutation) === FALSE, 'M17-01A migration 027 must not mutate legacy table: ' . $legacy_mutation);
}

m17_check(strpos($combined_schema, 'current parity migration 001-027') !== FALSE, 'M17-01A schema parity marker missing: current parity migration 001-027');

fwrite(STDOUT, "M17 schema regression checks passed.\n");
