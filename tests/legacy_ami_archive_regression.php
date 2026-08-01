<?php
$root = dirname(__DIR__);
function legacy_archive_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function legacy_archive_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
$migration = legacy_archive_source('migrations/023_create_legacy_ami_archive.sql');
$schema = legacy_archive_source('database_schema.sql');
$model = legacy_archive_source('application/models/Legacy_ami_archive_model.php');
$controller = legacy_archive_source('application/controllers/lpmpi/Legacy_ami_archive.php');
$routes = legacy_archive_source('application/config/routes.php');
$sidebar = legacy_archive_source('application/views/layouts/sidebar.php');
$views = legacy_archive_source('application/views/lpmpi/legacy_ami_archive/index.php') . legacy_archive_source('application/views/lpmpi/legacy_ami_archive/preflight.php') . legacy_archive_source('application/views/lpmpi/legacy_ami_archive/run.php') . legacy_archive_source('application/views/lpmpi/legacy_ami_archive/task.php') . legacy_archive_source('application/views/lpmpi/legacy_ami_archive/issues.php');
foreach (['legacy_ami_archive_runs', 'legacy_ami_archive_tasks', 'legacy_ami_archive_answers', 'legacy_ami_archive_issues', 'legacy_ami_archive_user_units', 'archive_code', 'legacy_tugas_id', 'legacy_jawaban_id', 'legacy_pertanyaan_id', 'issue_code', 'role_snapshot', 'uq_legacy_ami_archive_runs_code', 'uq_legacy_ami_archive_tasks_legacy', 'uq_legacy_ami_archive_answers_legacy', 'fk_legacy_ami_archive_tasks_run', 'fk_legacy_ami_archive_answers_task', 'fk_legacy_ami_archive_issues_run', 'fk_legacy_ami_archive_user_units_task', 'ON DELETE RESTRICT', 'ON UPDATE RESTRICT', 'ENGINE=InnoDB DEFAULT CHARSET=utf8'] as $literal) legacy_archive_check(strpos($migration, $literal) !== FALSE, 'M15 migration contract missing: ' . $literal);
legacy_archive_check(!preg_match('/REFERENCES\s+`?(tugas_audit|jawaban_audit|standar|pertanyaan|periode_audit|users|spmi_[a-z0-9_]+)`?/i', $migration), 'M15 archive migration must not FK to legacy or active SPMI tables.');
legacy_archive_check(!preg_match('/(^|;|\R)\s*(ALTER|DROP|TRUNCATE|DELETE|UPDATE|INSERT)\s+`?(tugas_audit|jawaban_audit|standar|pertanyaan|periode_audit|penetapan|spmi_[a-z0-9_]+)`?/i', $migration), 'M15 migration must not mutate legacy or active SPMI data.');
legacy_archive_check(!preg_match('/(^|;|\R)\s*INSERT\s+/i', $migration), 'M15 migration must be seed-free.');
foreach (['current parity migration 001-023', 'legacy_ami_archive_runs', 'legacy_ami_archive_tasks', 'legacy_ami_archive_answers', 'legacy_ami_archive_issues', 'legacy_ami_archive_user_units', 'uq_legacy_ami_archive_runs_code', 'fk_legacy_ami_archive_user_units_task'] as $literal) legacy_archive_check(strpos($schema, $literal) !== FALSE, 'M15 schema parity missing: ' . $literal);
foreach (['preflight', 'runs', 'run', 'tasks', 'task', 'answers', 'issues', 'legacy_gaps', 'table_exists', 'tugas_audit', 'jawaban_audit', 'standar', 'pertanyaan', 'periode_audit', 'users'] as $literal) legacy_archive_check(strpos($model, $literal) !== FALSE, 'M15 model read contract missing: ' . $literal);
legacy_archive_check(!preg_match('/->\s*(insert|update|delete|replace|insert_batch|update_batch)\s*\(/i', $model), 'M15 model must remain read-only.');
foreach (['extends Admin_Lpmpi_Controller', 'Legacy_ami_archive_model', 'preflight', 'run', 'task', 'issues'] as $literal) legacy_archive_check(strpos($controller, $literal) !== FALSE, 'M15 controller contract missing: ' . $literal);
legacy_archive_check(!preg_match('/function\s+(store|update|delete|archive|backfill|import|confirm)\s*\(/i', $controller), 'M15 controller must not expose mutating archive/backfill actions.');
foreach (['lpmpi/legacy-ami-archive', 'preflight', 'run/(:num)', 'task/(:num)', 'issues'] as $literal) legacy_archive_check(strpos($routes, $literal) !== FALSE, 'M15 route missing: ' . $literal);
foreach (['lpmpi/instrumen/download/(:num)', 'auditor/penilaian', 'auditee/tugas'] as $legacy_route) legacy_archive_check(substr_count($routes, $legacy_route) >= 1, 'Legacy route disappeared: ' . $legacy_route);
foreach (["'key' => 'dashboard'", "'url' => 'lpmpi/laporan'", "'url' => 'lpmpi/instrumen'"] as $legacy_menu) legacy_archive_check(strpos($sidebar, $legacy_menu) !== FALSE, 'Legacy sidebar target disappeared: ' . $legacy_menu);
legacy_archive_check(substr_count($sidebar, "'key' => 'legacy_ami_archive', 'label' => 'Arsip AMI Legacy', 'icon' => 'fa-archive', 'url' => 'lpmpi/legacy-ami-archive', 'group' => 'Insights'") === 2, 'M15 sidebar entry must be management-only.');
legacy_archive_check(strpos($views, 'html_escape') !== FALSE && strpos($views, 'form_open(') === FALSE && strpos($views, 'read-only') !== FALSE && strpos($views, 'tidak ada eksekusi backfill') !== FALSE, 'M15 views must be escaped, read-only, and no forms.');
legacy_archive_check(strpos($views, 'site_url(\'account/photo\')') === FALSE && strpos($views, 'force_download') === FALSE && strpos($views, 'private_storage_path') === FALSE, 'M15 views must not expose files or evidence download paths.');
foreach (['tugas_audit/hasil', 'lpmpi/laporan', 'lpmpi/instrumen', 'auditor/penilaian', 'auditee/tugas'] as $legacy_target) legacy_archive_check(strpos($controller . $views, $legacy_target) === FALSE, 'M15 must not link into legacy takeover target: ' . $legacy_target);
fwrite(STDOUT, "Legacy AMI archive regression checks passed.\n");
