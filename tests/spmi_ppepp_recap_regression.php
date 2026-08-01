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

$model = source($root, 'application/models/Spmi_ppepp_recap_model.php');
$controller = source($root, 'application/controllers/lpmpi/Spmi_ppepp_recap.php');
$view = source($root, 'application/views/lpmpi/spmi_ppepp_recap/index.php');
$routes = source($root, 'application/config/routes.php');
$sidebar = source($root, 'application/views/layouts/sidebar.php');

check(substr_count($model, 'function recap()') === 1, 'Recap model must expose one recap method.');
foreach (['spmi_standards', 'spmi_indicators', 'spmi_indicator_targets', 'spmi_audit_cycles', 'spmi_audit_assignments', 'spmi_auditee_submissions', 'spmi_auditor_assessments', 'spmi_reports', 'spmi_rtm_meetings', 'spmi_rtm_decisions', 'spmi_rtm_follow_ups'] as $table) {
    check(strpos($model, $table) !== FALSE, 'M3-M12 table missing: ' . $table);
}
foreach (['tugas_audit', 'jawaban_audit', 'Laporan_model', 'Dashboard'] as $forbidden) {
    check(stripos($model, $forbidden) === FALSE, 'Legacy source must remain excluded: ' . $forbidden);
}
check(strpos($model, "state IN ('configured', 'closed')") !== FALSE, 'Cycle state filter missing.');
check(strpos($model, "where('status', 'resolved')") !== FALSE, 'Resolved RTM meeting filter missing.');
check(strpos($model, "where_in('status', ['open', 'in_progress'])") !== FALSE, 'Open follow-up filter missing.');
check(strpos($model, "where('due_date IS NOT NULL', NULL, FALSE)") !== FALSE && strpos($model, "where('due_date < CURDATE()', NULL, FALSE)") !== FALSE, 'Overdue follow-up predicate missing.');
foreach (['submissions_draft', 'submissions_submitted', 'assessments_draft', 'assessments_finalized', 'meetings_resolved', 'follow_ups_open', 'follow_ups_in_progress', 'follow_ups_completed', 'follow_ups_overdue'] as $literal) {
    check(strpos($model, $literal) !== FALSE || strpos($view, $literal) !== FALSE, 'Planned PPEPP metric missing: ' . $literal);
}
foreach (['evidence', 'findings', 'overdue_follow_ups', 'target_revisions', 'recommendations'] as $forbidden_metric) {
    check(strpos($model, "'" . $forbidden_metric . "'") === FALSE && strpos($view, "'" . $forbidden_metric . "'") === FALSE, 'Forbidden recap metric still present: ' . $forbidden_metric);
}
check(strpos($controller, 'extends Admin_Lpmpi_Controller') !== FALSE && substr_count($controller, 'public function ') === 2, 'Controller must have constructor and index only.');
check(strpos($controller, "'active_menu' => 'spmi_ppepp_recap'") !== FALSE, 'Active menu missing.');
check(strpos($controller, "'page_subtitle' => 'Beranda / Insights / Rekap PPEPP SPMI'") !== FALSE, 'Controller subtitle must include Insights.');
check(strpos($routes, '$route[\'lpmpi/spmi-recap\'] = \'lpmpi/Spmi_ppepp_recap/index\';') !== FALSE, 'Recap route missing.');
check(substr_count($sidebar, "'key' => 'spmi_ppepp_recap', 'label' => 'Rekap PPEPP SPMI', 'icon' => 'fa-project-diagram', 'url' => 'lpmpi/spmi-recap', 'group' => 'Insights'") === 2, 'Recap sidebar entry must appear twice.');
foreach (['Belum ada standar, indikator, atau target SPMI yang ditetapkan.', 'Belum ada siklus, penugasan, atau submission SPMI yang berjalan.', 'Belum ada penilaian auditor atau laporan SPMI yang dihasilkan.', 'Belum ada RTM resolved atau keputusan pengendalian SPMI.', 'Belum ada tindak lanjut RTM yang terbuka atau diselesaikan.'] as $empty_text) {
    check(strpos($view, $empty_text) !== FALSE, 'Stage zero-state missing: ' . $empty_text);
}
check(strpos($view, 'form_open') === FALSE && strpos($view, 'export') === FALSE && strpos($view, 'print') === FALSE, 'View must remain read-only without actions or exports.');

fwrite(STDOUT, "SPMI PPEPP recap regression checks passed.\n");
