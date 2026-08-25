<?php

$root = dirname(__DIR__);

function source($root, $path)
{
    $contents = file_get_contents($root . DIRECTORY_SEPARATOR . $path);
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }
    return $contents;
}

function check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$routes = source($root, 'application/config/routes.php');
$sidebar = source($root, 'application/views/layouts/sidebar.php');
$management_controller = source($root, 'application/controllers/lpmpi/Spmi_management_dashboard.php');
$auditor_controller = source($root, 'application/controllers/Spmi_auditor_dashboard.php');
$auditee_controller = source($root, 'application/controllers/Spmi_auditee_dashboard.php');
$management_model = source($root, 'application/models/Spmi_management_dashboard_model.php');
$auditor_model = source($root, 'application/models/Spmi_auditor_dashboard_model.php');
$auditee_model = source($root, 'application/models/Spmi_auditee_dashboard_model.php');
$management_view = source($root, 'application/views/lpmpi/spmi_management_dashboard/index.php');
$auditor_view = source($root, 'application/views/spmi_auditor_dashboard/index.php');
$auditee_view = source($root, 'application/views/spmi_auditee_dashboard/index.php');

foreach ([
    '$route[\'lpmpi/spmi-dashboard\'] = \'lpmpi/Spmi_management_dashboard/index\';',
    '$route[\'lpmpi/spmi-dashboard/export\'] = \'lpmpi/Spmi_management_dashboard/export\';',
    '$route[\'auditor/spmi-dashboard\'] = \'Spmi_auditor_dashboard/index\';',
    '$route[\'auditee/spmi-dashboard\'] = \'Spmi_auditee_dashboard/index\';',
] as $route) {
    check(strpos($routes, $route) !== FALSE, 'M14 route missing: ' . $route);
}

check(strpos($management_controller, 'extends Admin_Lpmpi_Controller') !== FALSE, 'Management dashboard role base changed.');
check(strpos($auditor_controller, "auth_guard->only(['auditor'])") !== FALSE, 'Auditor dashboard guard missing.');
check(strpos($auditee_controller, "auth_guard->only(['auditee'])") !== FALSE, 'Auditee dashboard guard missing.');
check(strpos($management_controller, 'PhpSpreadsheet') !== FALSE && strpos($management_controller, 'php://output') !== FALSE, 'Management XLSX export contract missing.');
check(strpos($management_controller, 'ob_end_clean') !== FALSE, 'Export buffer clearing missing.');
check(strpos($management_controller, "header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')") !== FALSE, 'Native XLSX MIME header missing.');
check(strpos($management_controller, "header('Content-Disposition: attachment; filename=\"spmi-dashboard-' . \$year . '.xlsx\"')") !== FALSE, 'Native XLSX attachment header missing.');
check(strpos($management_controller, "FCPATH . 'vendor/autoload.php'") !== FALSE && strpos($management_controller, 'require_once $autoload') !== FALSE && strpos($management_controller, "class_exists('PhpOffice\\\\PhpSpreadsheet\\\\Spreadsheet')") !== FALSE, 'PhpSpreadsheet Composer autoload guard missing.');
check(strpos($management_controller, "'page_subtitle' => 'Beranda / Insights / Dashboard SPMI'") !== FALSE, 'Management dashboard subtitle must include Insights.');
check(strpos($management_model, 'function export_rows($year)') !== FALSE, 'Export rows must receive requested year.');
check(strpos($management_model, "'account_totals'") !== FALSE, 'Management account totals contract missing.');
foreach (['total_user', 'total_auditor', 'total_auditee'] as $account_total) {
    check(strpos($management_model, "'" . $account_total . "'") !== FALSE, 'Management account total missing: ' . $account_total);
}
check(strpos($management_model, 'User_model') !== FALSE && strpos($management_model, 'count_all()') !== FALSE && strpos($management_model, "count_by_role('auditor')") !== FALSE && strpos($management_model, "count_by_role('auditee')") !== FALSE, 'Raw registered-account count sources missing.');
check(strpos($auditor_model, 'account_totals') === FALSE && strpos($auditee_model, 'account_totals') === FALSE, 'Account totals must not leak to role dashboards.');
foreach ([$management_model, $auditor_model, $auditee_model] as $model) {
    foreach (['tugas_audit', 'jawaban_audit', 'Dashboard_service', 'Laporan_model'] as $legacy) {
        check(stripos($model, $legacy) === FALSE, 'Legacy reference leaked into M14 model: ' . $legacy);
    }
    check(stripos($model, 'insert') === FALSE && stripos($model, 'update') === FALSE && stripos($model, 'delete') === FALSE, 'M14 model must remain read-only.');
}
foreach ([
    'penetapan' => ['standards', 'indicators', 'targets'],
    'pelaksanaan' => ['cycles', 'assignments', 'submissions_draft', 'submissions_submitted'],
    'evaluasi' => ['assessments_draft', 'assessments_finalized', 'reports'],
    'pengendalian' => ['meetings_resolved', 'decisions'],
    'peningkatan' => ['follow_ups_open', 'follow_ups_in_progress', 'follow_ups_completed', 'follow_ups_overdue'],
] as $stage => $metrics) {
    check(strpos($management_model, "'" . $stage . "'") !== FALSE, 'Management stage missing: ' . $stage);
    foreach ($metrics as $metric) check(strpos($management_model, "'" . $metric . "'") !== FALSE, 'Management metric missing: ' . $metric);
}
foreach (['evidence', 'findings', 'overdue_follow_ups', 'target_revisions', 'recommendations'] as $forbidden) {
    check(strpos($management_model, "'" . $forbidden . "'") === FALSE, 'Unplanned management metric present: ' . $forbidden);
}
check(strpos($management_model, "state IN ('configured', 'closed')") !== FALSE, 'Management cycles must be configured or closed.');
check(strpos($management_model, "'m.status' => 'resolved'") !== FALSE, 'Management decisions must use resolved meetings.');
check(strpos($management_model, "where_in('status', ['open', 'in_progress'])") !== FALSE && strpos($management_model, "where('due_date < CURDATE()", 0) !== FALSE, 'Management overdue predicate missing.');
foreach (['assignments', 'submissions_submitted', 'assessments_draft', 'assessments_finalized', 'due_soon', 'overdue', 'notifications'] as $metric) check(strpos($auditor_model, "'" . $metric . "'") !== FALSE || strpos($auditor_model, '$' . $metric) !== FALSE, 'Auditor metric missing: ' . $metric);
foreach (['assignments', 'submissions_draft', 'submissions_submitted', 'due_soon', 'overdue', 'notifications'] as $metric) check(strpos($auditee_model, "'" . $metric . "'") !== FALSE || strpos($auditee_model, '$' . $metric) !== FALSE, 'Auditee metric missing: ' . $metric);
check(strpos($auditor_model, "where('a.auditor_id', (int) \$user_id)") !== FALSE, 'Auditor owner filter missing.');
check(strpos($auditee_model, "where('a.auditee_id', (int) \$user_id)") !== FALSE, 'Auditee owner filter missing.');
foreach (['severity', 'title', 'detail', 'route', 'count'] as $field) {
    check(strpos($management_model, "'" . $field . "'") !== FALSE, 'Live notification field missing: ' . $field);
}
check(strpos($management_model, "'route' => 'auditor/") === FALSE && strpos($management_model, "'route' => 'auditee/") === FALSE, 'Management notification route must not target auditor or auditee.');
foreach ([$auditor_model, $auditee_model] as $model) {
    check(strpos($model, "'notifications'") !== FALSE && strpos($model, "'route'") !== FALSE, 'Role notification payload missing.');
    check(strpos($model, 'insert') === FALSE && strpos($model, 'update') === FALSE && strpos($model, 'delete') === FALSE, 'Role notification surface must remain read-only.');
}
foreach ([$management_view, $auditor_view, $auditee_view] as $view) {
    check(strpos($view, 'layouts/header.php') !== FALSE && strpos($view, 'layouts/sidebar.php') !== FALSE, 'AMI shell missing from dashboard view.');
    check(strpos($view, 'html_escape(') !== FALSE, 'Escaped dashboard output missing.');
    check(strpos($view, 'Belum ada') !== FALSE, 'Dashboard zero state missing.');
    check(strpos($view, '<form') === FALSE && strpos($view, 'form_open') === FALSE, 'Dashboard view must remain read-only.');
}
foreach ([$management_view, $auditor_view, $auditee_view] as $view) {
    $notification_position = strpos($view, 'aria-labelledby="');
    $metric_position = strpos($view, 'ami-stat-grid');
    check(strpos($view, 'ami-dashboard-logo-banner') !== FALSE, 'SPMI dashboard identity banner missing.');
    check($notification_position !== FALSE && $metric_position !== FALSE && $notification_position < $metric_position, 'SPMI notifications must precede metrics.');
    check(strpos($view, 'ami-stat-grid') !== FALSE && strpos($view, 'ami-stat-icon') !== FALSE, 'SPMI dashboard must reuse AMI stat primitives.');
    check(strpos($view, 'ami-task-card') !== FALSE && strpos($view, 'ami-task-icon') !== FALSE, 'SPMI notifications must reuse AMI task cards.');
    check(strpos($view, 'list-group') === FALSE, 'SPMI notifications must not use Bootstrap list groups.');
    check(strpos($view, "'danger' ? 'tone-rose'") !== FALSE && strpos($view, "'warning' ? 'tone-amber'") !== FALSE, 'SPMI notification severity tone mapping changed.');
}
check(strpos($management_view, "site_url('lpmpi/spmi-dashboard/export?year=' . date('Y'))") !== FALSE, 'Management export action changed.');
check(strpos($management_view, 'ami-empty') !== FALSE || strpos($management_view, 'Belum ada data') !== FALSE, 'Management zero state must remain present.');
foreach (['Belum ada data penetapan SPMI.', 'Belum ada data pelaksanaan SPMI.', 'Belum ada data evaluasi SPMI.', 'Belum ada data pengendalian SPMI.', 'Belum ada data peningkatan SPMI.'] as $empty_text) check(strpos($management_view, $empty_text) !== FALSE, 'Management stage zero state missing: ' . $empty_text);
check(strpos($auditor_view, 'Belum ada penugasan SPMI untuk Anda.') !== FALSE && strpos($auditee_view, 'Belum ada penugasan SPMI untuk Anda.') !== FALSE, 'Role empty state missing.');
check(strpos($auditor_view, 'print') === FALSE && strpos($auditor_view, 'export') === FALSE, 'Auditor dashboard must not expose print/export.');
check(strpos($auditee_view, 'print') === FALSE && strpos($auditee_view, 'export') === FALSE, 'Auditee dashboard must not expose print/export.');
check(strpos($management_controller, "in_array(") !== FALSE && strpos($management_controller, "['=', '+', '-', '@']") !== FALSE, 'Export formula safety missing.');
foreach ([
    "'key' => 'spmi_dashboard', 'label' => 'Dashboard SPMI', 'icon' => 'fa-tachometer-alt', 'url' => 'lpmpi/spmi-dashboard', 'group' => 'Overview'",
    "'key' => 'spmi_auditor_dashboard', 'label' => 'Dashboard SPMI', 'icon' => 'fa-tachometer-alt', 'url' => 'auditor/spmi-dashboard', 'group' => 'Overview'",
    "'key' => 'spmi_auditee_dashboard', 'label' => 'Dashboard SPMI', 'icon' => 'fa-tachometer-alt', 'url' => 'auditee/spmi-dashboard', 'group' => 'Overview'",
] as $entry) {
    check(strpos($sidebar, $entry) !== FALSE, 'M14 sidebar entry missing.');
}
check(substr_count($sidebar, "'key' => 'spmi_dashboard'") === 2, 'Management dashboard sidebar must appear twice.');
check(substr_count($sidebar, "'key' => 'spmi_auditor_dashboard'") === 1, 'Auditor dashboard sidebar must appear once.');
check(substr_count($sidebar, "'key' => 'spmi_auditee_dashboard'") === 1, 'Auditee dashboard sidebar must appear once.');

fwrite(STDOUT, "SPMI dashboard regression checks passed.\n");
