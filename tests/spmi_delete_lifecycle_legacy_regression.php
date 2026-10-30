<?php
declare(strict_types=1);

$failures = [];

function spmi_lifecycle_assert(bool $condition, string $message): void
{
    global $failures;

    if (!$condition) {
        $failures[] = $message;
    }
}

function spmi_lifecycle_method(string $source, string $method): string
{
    $pattern = '/public\s+function\s+' . preg_quote($method, '/') . '\s*\([^)]*\)\s*\{/';
    if (!preg_match($pattern, $source, $matches, PREG_OFFSET_CAPTURE)) {
        return '';
    }

    $bodyStart = $matches[0][1] + strlen($matches[0][0]);
    $depth = 1;
    $length = strlen($source);
    for ($offset = $bodyStart; $offset < $length; $offset++) {
        if ($source[$offset] === '{') {
            $depth++;
        } elseif ($source[$offset] === '}' && --$depth === 0) {
            return substr($source, $bodyStart, $offset - $bodyStart);
        }
    }

    return '';
}

$repositoryRoot = dirname(__DIR__);
$paths = [
    'standards_model' => $repositoryRoot . '/application/models/Spmi_standards_model.php',
    'standards_service' => $repositoryRoot . '/application/services/Spmi_standards_service.php',
    'standards_controller' => $repositoryRoot . '/application/controllers/lpmpi/Spmi_standards.php',
    'audits_model' => $repositoryRoot . '/application/models/Spmi_audits_model.php',
    'audits_service' => $repositoryRoot . '/application/services/Spmi_audits_service.php',
    'routes' => $repositoryRoot . '/application/config/routes.php',
    'legacy_archive_model' => $repositoryRoot . '/application/models/Legacy_ami_archive_model.php',
    'reports_model' => $repositoryRoot . '/application/models/Spmi_reports_model.php',
    'reports_service' => $repositoryRoot . '/application/services/Spmi_reports_service.php',
    'reports_controller' => $repositoryRoot . '/application/controllers/lpmpi/Spmi_reports.php',
    'reports_index' => $repositoryRoot . '/application/views/lpmpi/spmi_reports/index.php',
    'reports_list' => $repositoryRoot . '/application/views/lpmpi/spmi_reports/report_list.php',
    'standards_css' => $repositoryRoot . '/assets/css/standards-management.css',
    'reports_css' => $repositoryRoot . '/assets/css/spmi-reports.css',
    'historical_migration' => $repositoryRoot . '/migrations/051_create_spmi_historical_reports.sql',
    'database_schema' => $repositoryRoot . '/database_schema.sql',
    'database_init' => $repositoryRoot . '/database_init.sql',
];
$source = [];

foreach ($paths as $name => $path) {
    $contents = file_get_contents($path);
    spmi_lifecycle_assert($contents !== false, 'source file is readable: ' . $name);
    $source[$name] = $contents === false ? '' : $contents;
}

$service = $source['standards_service'];
$deleteStandard = spmi_lifecycle_method($service, 'delete_standard');
$deleteVersion = spmi_lifecycle_method($service, 'delete_version');

spmi_lifecycle_assert(
    preg_match('/const\s+MUTABLE_STATUSES\s*=\s*\[\s*[\'\"]draft[\'\"]\s*\]\s*;/', $service) === 1,
    'only draft versions are mutable'
);
spmi_lifecycle_assert(
    preg_match('/\$version->status\s*!==\s*[\'\"]draft[\'\"]/', $deleteStandard) === 1,
    'standard deletion rejects every non-draft version'
);
spmi_lifecycle_assert(
    preg_match('/standard_has_assignments\s*\(\s*\(int\)\s*\$id\s*\)/', $deleteStandard) === 1,
    'standard deletion rejects standards with assignments'
);
spmi_lifecycle_assert(
    preg_match('/standard_has_assignments.*delete_standard/s', $deleteStandard) === 1,
    'standard assignment guard runs before deletion'
);
spmi_lifecycle_assert(
    preg_match('/\$version->status\s*!==\s*[\'\"]draft[\'\"]/', $deleteVersion) === 1,
    'version deletion rejects every non-draft version'
);
spmi_lifecycle_assert(
    preg_match('/version_has_assignments\s*\(\s*\(int\)\s*\$id\s*\)/', $deleteVersion) === 1,
    'version deletion rejects versions with assignments'
);
spmi_lifecycle_assert(
    preg_match('/version_has_assignments.*delete_version/s', $deleteVersion) === 1,
    'version assignment guard runs before deletion'
);

$auditsModel = $source['audits_model'];
$auditsService = $source['audits_service'];
$activeVersionFilter = '/where(?:_in)?\s*\(\s*[\'\"]v\.status[\'\"]\s*,\s*(?:\[\s*)?[\'\"]active[\'\"]\s*(?:\])?\s*\)/';
spmi_lifecycle_assert(
    preg_match($activeVersionFilter, spmi_lifecycle_method($auditsModel, 'versions')) === 1,
    'assignment version choices contain only active versions'
);
spmi_lifecycle_assert(
    preg_match($activeVersionFilter, spmi_lifecycle_method($auditsModel, 'standards_by_version')) === 1,
    'assignment standard choices contain only active-version standards'
);
spmi_lifecycle_assert(
    preg_match('/\$version->status\s*!==\s*[\'\"]active[\'\"]/', spmi_lifecycle_method($auditsService, 'create_assignment')) === 1,
    'assignment creation rejects a source version unless it is active'
);

$controller = $source['standards_controller'];
foreach (['version_delete', 'standard_delete'] as $handler) {
    $method = spmi_lifecycle_method($controller, $handler);
    spmi_lifecycle_assert($method !== '', 'standards controller provides POST ' . $handler . ' handler');
    spmi_lifecycle_assert(preg_match('/\$this->require_post\s*\(\s*\)/', $method) === 1, $handler . ' requires POST');
}
spmi_lifecycle_assert(
    preg_match('/\$route\s*\[\s*[\'\"]lpmpi\/spmi-standards\/version\/delete\/\(:num\)[\'\"]\s*\]\s*=\s*[\'\"]lpmpi\/Spmi_standards\/version_delete\/\$1[\'\"]\s*;/', $source['routes']) === 1,
    'version delete route targets the POST handler'
);
spmi_lifecycle_assert(
    preg_match('/\$route\s*\[\s*[\'\"]lpmpi\/spmi-standards\/standard\/delete\/\(:num\)[\'\"]\s*\]\s*=\s*[\'\"]lpmpi\/Spmi_standards\/standard_delete\/\$1[\'\"]\s*;/', $source['routes']) === 1,
    'standard delete route targets the POST handler'
);

$archiveModel = $source['legacy_archive_model'];
spmi_lifecycle_assert(
    preg_match('/private\s+\$archive_tables\s*=\s*\[[^;]+legacy_ami_archive_runs[^;]+legacy_ami_archive_user_units[^;]+\];/s', $archiveModel) === 1,
    'legacy archive keeps its optional table set explicit'
);
spmi_lifecycle_assert(
    preg_match('/function\s+tables_exist\s*\([^)]*\).*?\$this->db->table_exists\s*\(\s*\$table\s*\)/s', $archiveModel) === 1,
    'legacy archive checks optional table existence before access'
);
foreach (['runs', 'run', 'tasks', 'task', 'answers', 'issues'] as $method) {
    spmi_lifecycle_assert(
        preg_match('/^\s*if\s*\(\s*!\$this->tables_exist\s*\(/', spmi_lifecycle_method($archiveModel, $method)) === 1,
        'legacy archive ' . $method . ' returns safely when optional tables are absent'
    );
}
foreach (['database_schema', 'database_init'] as $schema) {
    spmi_lifecycle_assert(
        stripos($source[$schema], 'legacy_ami_archive_') === false,
        $schema . ' excludes optional legacy archive tables'
    );
}

spmi_lifecycle_assert(
    preg_match('/CREATE TABLE IF NOT EXISTS `spmi_historical_reports`/', $source['historical_migration']) === 1,
    'historical report migration is delivered without changing the demo schema'
);
spmi_lifecycle_assert(
    preg_match('/function\s+historical_table_exists\s*\([^)]*\).*?table_exists\s*\(\s*[\'\"]spmi_historical_reports[\'\"]\s*\)/s', $source['reports_model']) === 1,
    'historical report access checks that its optional table exists'
);
spmi_lifecycle_assert(
    preg_match('/historical_table_exists\s*\(\s*\).*?return\s*\[\s*\]/s', spmi_lifecycle_method($source['reports_model'], 'historical_reports')) === 1,
    'historical list safely returns no rows when its table is absent'
);
spmi_lifecycle_assert(
    preg_match('/private_storage_dir\(\s*[\'\"]spmi_historical_reports[\'\"]\s*\)/', $source['reports_service']) === 1,
    'historical uploads use the approved private storage category'
);
spmi_lifecycle_assert(
    preg_match('/function\s+store_historical\s*\([^)]*\).*?require_post\s*\(\s*\)/s', $source['reports_controller']) === 1
        && preg_match('/function\s+delete_historical\s*\([^)]*\).*?require_post\s*\(\s*\)/s', $source['reports_controller']) === 1,
    'historical report mutations require POST'
);
spmi_lifecycle_assert(
    strpos($source['reports_index'], 'lpmpi/spmi-reports/historical/store') !== false
        && strpos($source['reports_list'], 'Arsip Historis') !== false,
    'historical upload and list presentation are wired into SPMI reports'
);
spmi_lifecycle_assert(
    strpos($source['standards_css'], '.std-button-danger') !== false
        && strpos($source['standards_css'], '.std-dropdown-item-danger') !== false
        && strpos($source['reports_css'], '.reports-btn-danger') !== false,
    'all destructive SPMI controls have explicit danger styling'
);

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, 'FAIL: ' . $failure . PHP_EOL);
    }
    exit(1);
}

fwrite(STDOUT, "PASS: SPMI delete lifecycle and legacy archive source regression assertions\n");
