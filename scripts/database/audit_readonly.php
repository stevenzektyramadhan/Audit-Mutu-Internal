#!/usr/bin/env php
<?php
/**
 * Read-only database/schema consistency audit for TASK M0-02.
 *
 * Usage:
 *   php scripts/database/audit_readonly.php checks
 *   php scripts/database/audit_readonly.php schema
 *
 * The script never prints names, email addresses, evidence URLs, passwords,
 * or stored file paths. File checks emit aggregate counts only.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This audit can only be run from the command line.\n");
    exit(1);
}

$projectRoot = dirname(__DIR__, 2);
$environment = getenv('CI_ENV');
$environment = $environment !== false && trim($environment) !== ''
    ? trim($environment)
    : 'development';

if (!in_array($environment, ['development', 'testing', 'production'], true)) {
    fwrite(STDERR, "CI_ENV must be development, testing, or production.\n");
    exit(1);
}

define('BASEPATH', $projectRoot . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
define('APPPATH', $projectRoot . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
define('FCPATH', $projectRoot . DIRECTORY_SEPARATOR);
define('ENVIRONMENT', $environment);

require APPPATH . 'config' . DIRECTORY_SEPARATOR . 'database.php';
require APPPATH . 'helpers' . DIRECTORY_SEPARATOR . 'app_helper.php';

/**
 * Open the configured connection without exposing credentials in output.
 */
function audit_connect(array $config)
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $port = !empty($config['port'])
        ? (int) $config['port']
        : (int) ini_get('mysqli.default_port');

    $connection = new mysqli(
        $config['hostname'],
        $config['username'],
        $config['password'],
        $config['database'],
        $port
    );
    $connection->set_charset('utf8mb4');

    return $connection;
}

/**
 * Return the first scalar value from a SELECT query.
 */
function audit_scalar(mysqli $connection, $sql)
{
    $result = $connection->query($sql);
    $row = $result->fetch_row();

    return isset($row[0]) ? (int) $row[0] : 0;
}

/**
 * Emit tab-separated schema metadata without row data.
 */
function audit_emit_rows(mysqli $connection, $title, $sql, array $fields)
{
    echo '### ' . $title . PHP_EOL;
    echo implode("\t", $fields) . PHP_EOL;

    $result = $connection->query($sql);
    while ($row = $result->fetch_assoc()) {
        $values = [];
        foreach ($fields as $field) {
            $value = $row[$field];
            $values[] = $value === null
                ? '<NULL>'
                : str_replace(["\t", "\r", "\n"], ' ', (string) $value);
        }
        echo implode("\t", $values) . PHP_EOL;
    }
}

/**
 * Export structure from INFORMATION_SCHEMA. No application rows are emitted.
 */
function audit_schema(mysqli $connection)
{
    echo "mode\tschema" . PHP_EOL;
    echo 'server_version' . "\t" . $connection->server_info . PHP_EOL;

    audit_emit_rows(
        $connection,
        'TABLES',
        "SELECT TABLE_NAME, ENGINE, TABLE_COLLATION
         FROM INFORMATION_SCHEMA.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_TYPE = 'BASE TABLE'
         ORDER BY TABLE_NAME",
        ['TABLE_NAME', 'ENGINE', 'TABLE_COLLATION']
    );

    audit_emit_rows(
        $connection,
        'COLUMNS',
        "SELECT TABLE_NAME, ORDINAL_POSITION, COLUMN_NAME, COLUMN_TYPE,
                IS_NULLABLE, COLUMN_DEFAULT, EXTRA,
                CHARACTER_SET_NAME, COLLATION_NAME
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
         ORDER BY TABLE_NAME, ORDINAL_POSITION",
        [
            'TABLE_NAME',
            'ORDINAL_POSITION',
            'COLUMN_NAME',
            'COLUMN_TYPE',
            'IS_NULLABLE',
            'COLUMN_DEFAULT',
            'EXTRA',
            'CHARACTER_SET_NAME',
            'COLLATION_NAME',
        ]
    );

    audit_emit_rows(
        $connection,
        'INDEXES',
        "SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX,
                COLUMN_NAME, SUB_PART, INDEX_TYPE
         FROM INFORMATION_SCHEMA.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE()
         ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX",
        [
            'TABLE_NAME',
            'INDEX_NAME',
            'NON_UNIQUE',
            'SEQ_IN_INDEX',
            'COLUMN_NAME',
            'SUB_PART',
            'INDEX_TYPE',
        ]
    );

    audit_emit_rows(
        $connection,
        'FOREIGN_KEYS',
        "SELECT k.TABLE_NAME, k.CONSTRAINT_NAME, k.COLUMN_NAME,
                k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME,
                r.UPDATE_RULE, r.DELETE_RULE
         FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
         INNER JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS r
             ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
            AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
            AND r.TABLE_NAME = k.TABLE_NAME
         WHERE k.TABLE_SCHEMA = DATABASE()
           AND k.REFERENCED_TABLE_NAME IS NOT NULL
         ORDER BY k.TABLE_NAME, k.CONSTRAINT_NAME, k.ORDINAL_POSITION",
        [
            'TABLE_NAME',
            'CONSTRAINT_NAME',
            'COLUMN_NAME',
            'REFERENCED_TABLE_NAME',
            'REFERENCED_COLUMN_NAME',
            'UPDATE_RULE',
            'DELETE_RULE',
        ]
    );

    try {
        audit_emit_rows(
            $connection,
            'CHECK_CONSTRAINTS',
            "SELECT tc.TABLE_NAME, tc.CONSTRAINT_NAME, cc.CHECK_CLAUSE
             FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
             INNER JOIN INFORMATION_SCHEMA.CHECK_CONSTRAINTS cc
                 ON cc.CONSTRAINT_SCHEMA = tc.CONSTRAINT_SCHEMA
                AND cc.CONSTRAINT_NAME = tc.CONSTRAINT_NAME
             WHERE tc.CONSTRAINT_SCHEMA = DATABASE()
               AND tc.CONSTRAINT_TYPE = 'CHECK'
             ORDER BY tc.TABLE_NAME, tc.CONSTRAINT_NAME",
            ['TABLE_NAME', 'CONSTRAINT_NAME', 'CHECK_CLAUSE']
        );
    } catch (mysqli_sql_exception $exception) {
        // Older supported database variants may not expose CHECK_CONSTRAINTS.
        echo "### CHECK_CONSTRAINTS" . PHP_EOL;
        echo "status\tmetadata_not_supported_by_server" . PHP_EOL;
    }
}

/**
 * Required and supplemental data-quality checks.
 *
 * Every statement is a hard-coded SELECT. Results are aggregate counts only.
 */
function audit_database_checks(mysqli $connection)
{
    $checks = [
        'tugas_tanpa_jawaban' =>
            "SELECT COUNT(*)
             FROM tugas_audit t
             LEFT JOIN jawaban_audit j ON j.tugas_id = t.id
             WHERE j.id IS NULL",

        'jawaban_tanpa_tugas' =>
            "SELECT COUNT(*)
             FROM jawaban_audit j
             LEFT JOIN tugas_audit t ON t.id = j.tugas_id
             WHERE t.id IS NULL",

        'jawaban_tanpa_pertanyaan' =>
            "SELECT COUNT(*)
             FROM jawaban_audit j
             LEFT JOIN pertanyaan p ON p.id = j.pertanyaan_id
             WHERE p.id IS NULL",

        'tugas_tanpa_periode' =>
            "SELECT COUNT(*)
             FROM tugas_audit t
             LEFT JOIN periode_audit p ON p.id = t.periode_id
             WHERE t.periode_id IS NULL OR p.id IS NULL",

        'user_role_invalid' =>
            "SELECT COUNT(*)
             FROM users
             WHERE role IS NULL
                OR role NOT IN ('super_admin', 'admin_lpmpi', 'auditor', 'auditee')",

        'assignment_role_invalid' =>
            "SELECT COUNT(*)
             FROM tugas_audit t
             LEFT JOIN users auditor ON auditor.id = t.auditor_id
             LEFT JOIN users auditee ON auditee.id = t.auditee_id
             WHERE auditor.id IS NULL
                OR auditor.role <> 'auditor'
                OR auditee.id IS NULL
                OR auditee.role <> 'auditee'",

        'auditor_dan_auditee_sama' =>
            "SELECT COUNT(*)
             FROM tugas_audit
             WHERE auditor_id = auditee_id",

        'status_tugas_tidak_konsisten' =>
            "SELECT COUNT(*)
             FROM (
                 SELECT
                     t.id,
                     t.status,
                     COUNT(j.id) AS total_jawaban,
                     SUM(COALESCE(j.is_submitted, 0) = 1) AS auditee_submitted,
                     SUM(COALESCE(j.is_nilai_submitted, 0) = 1) AS nilai_submitted,
                     SUM(j.skor BETWEEN 1 AND 4) AS scored
                 FROM tugas_audit t
                 LEFT JOIN jawaban_audit j ON j.tugas_id = t.id
                 GROUP BY t.id, t.status
                 HAVING
                     (status = 'belum_diisi'
                         AND (auditee_submitted > 0 OR nilai_submitted > 0))
                     OR
                     (status = 'diisi'
                         AND (
                             total_jawaban = 0
                             OR auditee_submitted <> total_jawaban
                             OR nilai_submitted = total_jawaban
                         ))
                     OR
                     (status = 'dinilai'
                         AND (
                             total_jawaban = 0
                             OR auditee_submitted <> total_jawaban
                             OR nilai_submitted <> total_jawaban
                             OR scored <> total_jawaban
                         ))
             ) inconsistent_tasks",

        'is_submitted_tidak_seragam' =>
            "SELECT COUNT(*)
             FROM (
                 SELECT tugas_id
                 FROM jawaban_audit
                 GROUP BY tugas_id
                 HAVING MIN(COALESCE(is_submitted, 0))
                      <> MAX(COALESCE(is_submitted, 0))
             ) mixed_submit_flags",

        'is_nilai_submitted_tidak_seragam' =>
            "SELECT COUNT(*)
             FROM (
                 SELECT tugas_id
                 FROM jawaban_audit
                 GROUP BY tugas_id
                 HAVING MIN(COALESCE(is_nilai_submitted, 0))
                      <> MAX(COALESCE(is_nilai_submitted, 0))
             ) mixed_assessment_flags",

        'submitted_tanpa_timestamp' =>
            "SELECT COUNT(*)
             FROM jawaban_audit
             WHERE is_submitted = 1 AND submitted_at IS NULL",

        'nilai_submitted_tanpa_timestamp' =>
            "SELECT COUNT(*)
             FROM jawaban_audit
             WHERE is_nilai_submitted = 1 AND nilai_submitted_at IS NULL",

        'duplikat_assignment' =>
            "SELECT COUNT(*)
             FROM (
                 SELECT periode_id, standar_id, auditor_id, auditee_id
                 FROM tugas_audit
                 GROUP BY periode_id, standar_id, auditor_id, auditee_id
                 HAVING COUNT(*) > 1
             ) duplicate_assignments",

        'duplikat_jawaban_per_pertanyaan' =>
            "SELECT COUNT(*)
             FROM (
                 SELECT tugas_id, pertanyaan_id
                 FROM jawaban_audit
                 GROUP BY tugas_id, pertanyaan_id
                 HAVING COUNT(*) > 1
             ) duplicate_answers",

        'duplikat_penetapan' =>
            "SELECT COUNT(*)
             FROM (
                 SELECT standar_id, kategori
                 FROM penetapan
                 GROUP BY standar_id, kategori
                 HAVING COUNT(*) > 1
             ) duplicate_penetapan",

        'periode_aktif_lebih_dari_satu' =>
            "SELECT CASE
                 WHEN SUM(is_aktif = 1) > 1 THEN SUM(is_aktif = 1) - 1
                 ELSE 0
             END
             FROM periode_audit",

        'periode_tanggal_invalid' =>
            "SELECT COUNT(*)
             FROM periode_audit
             WHERE tanggal_tutup < tanggal_buka",

        'skor_di_luar_1_4' =>
            "SELECT COUNT(*)
             FROM jawaban_audit
             WHERE skor IS NOT NULL
               AND skor NOT BETWEEN 1 AND 4",

        'flag_bukan_boolean' =>
            "SELECT COUNT(*)
             FROM jawaban_audit
             WHERE COALESCE(is_submitted, 0) NOT IN (0, 1)
                OR COALESCE(is_nilai_submitted, 0) NOT IN (0, 1)",

        'flag_state_bernilai_null' =>
            "SELECT COUNT(*)
             FROM jawaban_audit
             WHERE is_submitted IS NULL
                OR is_nilai_submitted IS NULL",

        'standar_jawaban_tidak_sesuai_tugas' =>
            "SELECT COUNT(*)
             FROM jawaban_audit j
             INNER JOIN tugas_audit t ON t.id = j.tugas_id
             INNER JOIN pertanyaan p ON p.id = j.pertanyaan_id
             WHERE p.standar_id <> t.standar_id",

        'jumlah_jawaban_tidak_sesuai_master_saat_ini' =>
            "SELECT COUNT(*)
             FROM (
                 SELECT
                     t.id,
                     COUNT(DISTINCT j.id) AS jumlah_jawaban,
                     (
                         SELECT COUNT(*)
                         FROM pertanyaan p
                         WHERE p.standar_id = t.standar_id
                     ) AS jumlah_pertanyaan
                 FROM tugas_audit t
                 LEFT JOIN jawaban_audit j ON j.tugas_id = t.id
                 GROUP BY t.id
                 HAVING jumlah_jawaban <> jumlah_pertanyaan
             ) answer_count_mismatch",

        'profil_lembaga_lebih_dari_satu' =>
            "SELECT CASE
                 WHEN COUNT(*) > 1 THEN COUNT(*) - 1
                 ELSE 0
             END
             FROM profil_lembaga",
    ];

    echo "mode\tchecks" . PHP_EOL;
    echo 'server_version' . "\t" . $connection->server_info . PHP_EOL;
    echo "check\tproblem_count" . PHP_EOL;

    foreach ($checks as $name => $sql) {
        echo $name . "\t" . audit_scalar($connection, $sql) . PHP_EOL;
    }
}

/**
 * Verify local/private file references without printing stored names or paths.
 */
function audit_file_checks(mysqli $connection)
{
    $sets = [
        ['standar', 'id', 'file_instrumen', 'instrumen'],
        ['penetapan', 'id', 'file_path', 'penetapan'],
        ['jawaban_audit', 'id', 'dokumen_bukti', 'bukti_auditor'],
        ['users', 'id', 'profile_photo_path', 'user_photos'],
    ];

    $totalMissing = 0;

    foreach ($sets as $set) {
        list($table, $idColumn, $fileColumn, $category) = $set;
        $result = $connection->query(
            "SELECT `$idColumn` AS row_id, `$fileColumn` AS stored_name
             FROM `$table`
             WHERE `$fileColumn` IS NOT NULL
               AND TRIM(`$fileColumn`) <> ''"
        );

        $missing = 0;
        while ($row = $result->fetch_assoc()) {
            if (private_storage_path($category, (string) $row['stored_name']) === null) {
                $missing++;
            }
        }

        $totalMissing += $missing;
        echo 'missing_file_' . $table . '_' . $fileColumn
            . "\t" . $missing . PHP_EOL;
    }

    $result = $connection->query(
        "SELECT id, logo_path
         FROM profil_lembaga
         WHERE logo_path IS NOT NULL
           AND TRIM(logo_path) <> ''"
    );

    $missingLogo = 0;
    while ($row = $result->fetch_assoc()) {
        $storedName = (string) $row['logo_path'];
        $logoPath = FCPATH
            . 'uploads'
            . DIRECTORY_SEPARATOR
            . 'profil'
            . DIRECTORY_SEPARATOR
            . $storedName;

        if (
            $storedName === ''
            || basename($storedName) !== $storedName
            || !is_file($logoPath)
        ) {
            $missingLogo++;
        }
    }

    $totalMissing += $missingLogo;
    echo 'missing_file_profil_lembaga_logo_path'
        . "\t" . $missingLogo . PHP_EOL;
    echo 'missing_file_references_total'
        . "\t" . $totalMissing . PHP_EOL;
}

/**
 * Emit aggregate row counts, never row values.
 */
function audit_row_counts(mysqli $connection)
{
    echo "### ROW_COUNTS" . PHP_EOL;
    echo "table\trow_count" . PHP_EOL;

    $result = $connection->query(
        "SELECT TABLE_NAME
         FROM INFORMATION_SCHEMA.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_TYPE = 'BASE TABLE'
         ORDER BY TABLE_NAME"
    );

    while ($row = $result->fetch_assoc()) {
        $table = $row['TABLE_NAME'];
        $escapedTable = '`' . str_replace('`', '``', $table) . '`';
        echo $table
            . "\t"
            . audit_scalar($connection, "SELECT COUNT(*) FROM $escapedTable")
            . PHP_EOL;
    }
}

$mode = isset($argv[1]) ? strtolower(trim($argv[1])) : 'checks';
if (in_array($mode, ['-h', '--help', 'help'], true)) {
    echo "Usage:\n";
    echo "  php scripts/database/audit_readonly.php checks\n";
    echo "  php scripts/database/audit_readonly.php schema\n";
    exit(0);
}

if (!in_array($mode, ['checks', 'schema'], true)) {
    fwrite(STDERR, "Unknown mode. Use checks or schema.\n");
    exit(1);
}

$connection = null;

try {
    $connection = audit_connect($db['default']);
    $connection->query('START TRANSACTION READ ONLY');

    if ($mode === 'schema') {
        audit_schema($connection);
    } else {
        audit_database_checks($connection);
        audit_file_checks($connection);
        audit_row_counts($connection);
    }

    $connection->rollback();
    $connection->close();
    exit(0);
} catch (Throwable $exception) {
    if ($connection instanceof mysqli) {
        try {
            $connection->rollback();
            $connection->close();
        } catch (Throwable $ignored) {
            // Preserve the original failure.
        }
    }

    fwrite(
        STDERR,
        'Read-only audit failed. Verify CI_ENV and database availability.'
        . PHP_EOL
    );
    exit(2);
}
