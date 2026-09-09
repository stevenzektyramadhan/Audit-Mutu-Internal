<?php
$root = dirname(__DIR__);
function spmi_audit_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function spmi_audit_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
function spmi_audit_match($pattern, $subject, $message) { if (!preg_match($pattern, $subject)) throw new RuntimeException($message); }

$migration = spmi_audit_source('migrations/017_create_spmi_audit_cycles.sql');
$migration_031 = spmi_audit_source('migrations/031_add_spmi_audit_cycle_academic_period.sql');
$schema = spmi_audit_source('database_schema.sql');
$model = spmi_audit_source('application/models/Spmi_audits_model.php');
$service = spmi_audit_source('application/services/Spmi_audits_service.php');
$helper = spmi_audit_source('application/helpers/app_helper.php');
$auditor_workspace_service = spmi_audit_source('application/services/Spmi_auditor_workspace_service.php');
$controller = spmi_audit_source('application/controllers/lpmpi/Spmi_audits.php');
$routes = spmi_audit_source('application/config/routes.php');
$sidebar = spmi_audit_source('application/views/layouts/sidebar.php');
$views = [];
foreach (['index', 'cycle_form', 'cycle_detail', 'assignment_form', 'assignment_detail'] as $view) $views[] = spmi_audit_source('application/views/lpmpi/spmi_audits/' . $view . '.php');

foreach (['spmi_audit_cycles', 'spmi_audit_assignments', 'spmi_audit_assignment_items', 'spmi_audit_assignment_item_rubrics', "ENUM('draft','configured','closed')", 'source_version_code', 'source_package_code', 'auditor_name', 'auditee_email', 'UNIQUE KEY', 'ON DELETE RESTRICT'] as $literal) spmi_audit_check(strpos($migration, $literal) !== FALSE, 'M7 migration contract missing: ' . $literal);
spmi_audit_check(strpos($migration, 'spmi_audit_assignment_items') !== FALSE && strpos($migration, "`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP") !== FALSE, 'M7 item snapshot timestamp missing.');
spmi_audit_check(strpos($migration, 'spmi_audit_assignment_item_rubrics') !== FALSE && substr_count($migration, "`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP") >= 4, 'M7 rubric snapshot timestamp missing.');
foreach (['spmi_audit_cycles', 'academic_year', 'semester', '`academic_year` VARCHAR(20) NULL', "`semester` ENUM('ganjil','genap') NULL"] as $literal) spmi_audit_check(strpos($migration_031, $literal) !== FALSE, 'M31 migration contract missing: ' . $literal);
spmi_audit_check(strpos($migration_031, 'ALTER TABLE `spmi_audit_cycles`') !== FALSE, 'M31 migration must alter spmi_audit_cycles.');
spmi_audit_check(!preg_match('/\b(INSERT|UPDATE|DELETE)\b/i', $migration_031), 'M31 migration must stay additive and avoid DML/backfill.');
foreach (['current parity migration 001-018', 'spmi_audit_cycles', 'spmi_audit_assignments', 'spmi_audit_assignment_items', 'spmi_audit_assignment_item_rubrics', 'uq_spmi_audit_assignments_tuple', 'uq_spmi_audit_assignment_items_order', 'uq_spmi_audit_assignment_items_question', 'uq_spmi_audit_assignment_item_rubrics_score'] as $literal) spmi_audit_check(strpos($schema, $literal) !== FALSE, 'M7 schema parity missing: ' . $literal);
foreach (['current parity migration 001-031', 'academic_year', 'semester'] as $literal) spmi_audit_check(strpos($schema, $literal) !== FALSE, 'M31 schema parity missing: ' . $literal);
spmi_audit_match('/CREATE TABLE IF NOT EXISTS `spmi_audit_cycles` \((?s).*`academic_year` VARCHAR\(20\) NULL.*`semester` ENUM\(\'ganjil\',\'genap\'\) NULL/', $schema, 'M31 schema must expose nullable academic period fields on spmi_audit_cycles.');
spmi_audit_check(substr_count($schema, "`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP") >= 14, 'M7 baseline child snapshot timestamps missing.');
spmi_audit_check(strpos($migration, 'INSERT') === FALSE && strpos($migration, '`tugas_audit`') === FALSE && strpos($migration, '`jawaban_audit`') === FALSE && strpos($migration, '`standar`') === FALSE && strpos($migration, '`pertanyaan`') === FALSE, 'M7 migration must be additive and seed-free.');
foreach (['cycles', 'assignments', 'package_for_update', 'package_questions', 'users_by_role', 'delete_assignment_children'] as $literal) spmi_audit_check(strpos($model, $literal) !== FALSE, 'M7 model contract missing: ' . $literal);
spmi_audit_check(strpos($model, 'package_for_update') !== FALSE && strpos($model, 'FOR UPDATE') !== FALSE, 'M7 package lookup must lock source package rows.');
foreach (['assignment_workspace_descendant_exists', 'spmi_auditee_submissions', 'spmi_auditor_assessments', 'spmi_auditee_submission_revision_events', 'assignment_id'] as $literal) spmi_audit_check(strpos($model, $literal) !== FALSE, 'M7 assignment workspace preflight missing: ' . $literal);
foreach (['TRANSITIONS', "'draft' => ['configured', 'closed']", "'configured' => ['draft', 'closed']", "'closed' => []", 'trans_begin', 'cycle($cycle_id, TRUE)', 'version_for_update', 'in_array($version->status, [\'draft\', \'review\']', 'role !==', 'assignment_by_tuple', 'source_version_title', 'question_text', 'descriptor', 'rollback'] as $literal) spmi_audit_check(strpos($service, $literal) !== FALSE, 'M7 service contract missing: ' . $literal);
spmi_audit_check(strpos($service, '$this->ci->load->helper(\'app\')') !== FALSE && strpos($service, 'skor_audit_options()') !== FALSE, 'M7 assignment service must load app helper itself and call skor_audit_options().');
spmi_audit_check(strpos($service, 'array_keys($rubric_options) !== [1, 2, 3, 4]') !== FALSE, 'M7 assignment service must guard the global score scale keys.');
foreach (['Tidak sesuai', 'Kurang sesuai', 'Sesuai', 'Sangat sesuai'] as $descriptor) spmi_audit_check(strpos($helper, $descriptor) !== FALSE, 'Global skor_audit_options descriptor missing: ' . $descriptor);
spmi_audit_check(strpos($service . $model, 'question_rubrics') === FALSE, 'M7 assignment snapshot must not require source question rubrics.');
spmi_audit_check(strpos($service, '$rubric_options as $score => $descriptor') !== FALSE && strpos($service, "'assignment_item_id' => \$item_id") !== FALSE && strpos($service, "'score' => (int) \$score") !== FALSE && strpos($service, "'descriptor' => \$descriptor") !== FALSE, 'M7 assignment snapshot must write four helper descriptors per item.');
spmi_audit_check(strpos($auditor_workspace_service, "in_array(\$raw_score, ['1', '2', '3', '4'], TRUE)") !== FALSE, 'M9 auditor logic must preserve scalar string score validation.');
foreach (['academic_year', 'semester', "'academic_year' =>", "'semester' =>"] as $literal) spmi_audit_check(strpos($service, $literal) !== FALSE, 'M31 service cycle contract missing: ' . $literal);
spmi_audit_check(strpos($service, 'strlen($data[\'academic_year\']) <= 20') !== FALSE, 'M31 service must validate academic_year length.');
spmi_audit_check(strpos($service, 'in_array($data[\'semester\'], [\'ganjil\', \'genap\'], TRUE)') !== FALSE, 'M31 service must restrict semester to ganjil/genap.');
spmi_audit_check(strpos($service, 'delete_assignment($id)') !== FALSE, 'M7 delete assignment service contract missing.');
spmi_audit_check(strpos($service, '$cycle->state !== \'draft\'') !== FALSE, 'M7 delete assignment draft guard missing.');
spmi_audit_check(strpos($service, 'assignment_workspace_descendant_exists($id)') !== FALSE, 'M7 delete assignment must call workspace descendant preflight.');
spmi_audit_check(strpos($service, 'Penugasan tidak dapat dihapus karena data workspace auditee atau auditor sudah ada.') !== FALSE, 'M7 delete assignment workspace-data business failure message missing.');
spmi_audit_check(strpos($service, 'assignment_workspace_descendant_exists($id)') > strpos($service, '$cycle->state !== \'draft\'') && strpos($service, 'assignment_workspace_descendant_exists($id)') < strpos($service, 'delete_assignment_children($id)'), 'M7 delete assignment preflight must run after draft guard and before child deletion.');
foreach (['extends Admin_Lpmpi_Controller', 'form_validation', "method(TRUE) !== 'POST'", 'show_error', 'cycle_create', 'cycle_detail', 'assignment_create', 'assignment_detail'] as $literal) spmi_audit_check(strpos($controller, $literal) !== FALSE, 'M7 controller contract missing: ' . $literal);
spmi_audit_check(strpos($controller, "set_rules('academic_year'") !== FALSE && strpos($controller, 'required|max_length[20]') !== FALSE, 'M31 controller must require academic_year.');
spmi_audit_check(strpos($controller, "set_rules('semester'") !== FALSE && strpos($controller, 'required|in_list[ganjil,genap]') !== FALSE, 'M31 controller must require semester with ganjil/genap.');
foreach (['lpmpi/spmi-audits', 'cycle/create', 'cycle/store', 'cycle/detail', 'cycle/edit', 'cycle/update', 'cycle/transition', 'assignment/create', 'assignment/store', 'assignment/detail', 'assignment/delete'] as $literal) spmi_audit_check(strpos($routes, $literal) !== FALSE, 'M7 route missing: ' . $literal);
spmi_audit_check(substr_count($sidebar, "'key' => 'spmi_audits', 'label' => 'Siklus & Penugasan SPMI', 'icon' => 'fa-calendar-check', 'url' => 'lpmpi/spmi-audits', 'group' => 'Management'") === 2, 'M7 sidebar entry must exist only for management roles.');
foreach ($views as $view) { spmi_audit_check(strpos($view, 'html_escape') !== FALSE, 'M7 view must escape output.'); spmi_audit_check(strpos($view, "include APPPATH . 'views/layouts/header.php'") !== FALSE, 'M7 view header missing.'); }
spmi_audit_check(strpos($views[1], 'form_open(') !== FALSE && strpos($views[2], 'form_open(') !== FALSE && strpos($views[3], 'form_open(') !== FALSE, 'M7 POST forms missing.');
spmi_audit_check(strpos($views[1], 'name="academic_year"') !== FALSE && strpos($views[1], 'id="academic_year"') !== FALSE, 'M31 cycle form must render academic_year input.');
spmi_audit_check(strpos($views[1], 'name="semester"') !== FALSE && strpos($views[1], 'value="ganjil"') !== FALSE && strpos($views[1], 'value="genap"') !== FALSE, 'M31 cycle form must render semester select options.');
spmi_audit_check(strpos($views[0], 'academic_year') !== FALSE && strpos($views[0], 'semester') !== FALSE, 'M31 cycle index must render academic period columns.');
spmi_audit_check(strpos($views[2], 'academic_year') !== FALSE && strpos($views[2], 'semester') !== FALSE, 'M31 cycle detail must render academic period metadata.');
spmi_audit_match('/html_escape\([^;]*academic_year[^;]{0,200}(\?:|\?\?)/s', $views[0], 'M31 cycle index must keep escaped historical-null fallback for academic period display.');
spmi_audit_match('/html_escape\([^;]*academic_year[^;]{0,200}(\?:|\?\?)/s', $views[2], 'M31 cycle detail must keep escaped historical-null fallback for academic period display.');
spmi_audit_check(strpos($views[2], 'frozen') !== FALSE && strpos($views[2], 'draft') !== FALSE && strpos($views[4], 'M8/M9') !== FALSE, 'M7 frozen and future workspace notices missing.');
spmi_audit_check(strpos($views[2], 'nl2br(html_escape(') !== FALSE && strpos($views[4], 'nl2br(html_escape(') !== FALSE, 'M7 multiline snapshots must be escaped.');
spmi_audit_check(strpos($views[2], 'ami-row-actions') !== FALSE && strpos($views[2], 'ami-action-btn') !== FALSE, 'M7 cycle detail actions must use standard wrapper/classes.');
spmi_audit_check(strpos($views[2], "site_url('lpmpi/spmi-audits/assignment/detail/'") !== FALSE || strpos($views[2], 'lpmpi/spmi-audits/assignment/detail/') !== FALSE, 'M7 cycle detail must retain Snapshot detail route.');
spmi_audit_check(strpos($views[2], "form_open('lpmpi/spmi-audits/assignment/delete/'") !== FALSE || strpos($views[2], 'lpmpi/spmi-audits/assignment/delete/') !== FALSE, 'M7 cycle detail must retain delete POST form_open.');
spmi_audit_check(strpos($views[2], 'return confirm(') !== FALSE || strpos($views[2], 'onsubmit=') !== FALSE, 'M7 cycle detail delete action must keep POST form confirmation.');
spmi_audit_check(strpos($views[4], '$this->service->rubrics') === FALSE && strpos($views[4], '$rubrics_by_item') !== FALSE, 'M7 assignment detail must use controller-provided rubrics.');
spmi_audit_check(strpos($controller, '$rubrics_by_item') !== FALSE && strpos($controller, "'rubrics_by_item' => " . '$rubrics_by_item') !== FALSE, 'M7 controller must pass rubric data to assignment detail view.');
spmi_audit_check(strpos($service, 'tugas_audit') === FALSE && strpos($service, 'jawaban_audit') === FALSE && strpos($service, 'assignment_status') === FALSE && strpos($service, "'status'") === FALSE, 'M7 service must not map legacy tables or assignment status.');

fwrite(STDOUT, "SPMI audits regression checks passed.\n");
