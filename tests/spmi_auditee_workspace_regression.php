<?php
$root = dirname(__DIR__);
function m8_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function m8_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }

$migration = m8_source('migrations/018_create_spmi_auditee_workspace.sql');
$schema = m8_source('database_schema.sql');
$helper = m8_source('application/helpers/app_helper.php');
$model = m8_source('application/models/Spmi_auditee_workspace_model.php');
$service = m8_source('application/services/Spmi_auditee_workspace_service.php');
$controller = m8_source('application/controllers/Spmi_auditee_workspace.php');
$routes = m8_source('application/config/routes.php');
$sidebar = m8_source('application/views/layouts/sidebar.php');
$index = m8_source('application/views/spmi_auditee_workspace/index.php');
$assignment = m8_source('application/views/spmi_auditee_workspace/assignment.php');

foreach (['spmi_auditee_submissions', 'spmi_auditee_submission_items', 'spmi_auditee_evidence', 'ENUM(\'draft\',\'submitted\')', '`version`', 'assignment_item_id', 'ON DELETE RESTRICT', 'UNIQUE KEY'] as $literal) m8_check(strpos($migration, $literal) !== FALSE, 'M8 migration contract missing: ' . $literal);
m8_check(strpos($migration, 'INSERT') === FALSE, 'M8 migration must be seed-free.');
foreach (['current parity migration 001-018', 'spmi_auditee_submissions', 'spmi_auditee_submission_items', 'spmi_auditee_evidence', 'uq_spmi_auditee_submissions_assignment', 'uq_spmi_auditee_submission_items_item', 'fk_spmi_auditee_evidence_submission_item'] as $literal) m8_check(strpos($schema, $literal) !== FALSE, 'M8 schema parity missing: ' . $literal);
m8_check(strpos($helper, "'audit_evidence'") !== FALSE && strpos($helper, "\$category === 'audit_evidence'") !== FALSE, 'M8 private evidence category must not use legacy fallback.');
foreach (['a.auditee_id', 'where_in(\'c.state\', [\'configured\', \'closed\'])', 'FOR UPDATE', 'assignment_item_id', 'submission_id', 'update_realization', 'update_version', 'evidence_for_update'] as $literal) m8_check(strpos($model, $literal) !== FALSE, 'M8 model contract missing: ' . $literal);
foreach (['trans_begin', 'status !== \'draft\'', 'state !== \'configured\'', 'CONFLICT', 'trim', 'finfo_open', 'getimagesize', '5 * 1024 * 1024', 'random_bytes', '0700', 'move_uploaded_file', 'hash_file', 'paths', 'private_storage_path', 'update_version'] as $literal) m8_check(strpos($service, $literal) !== FALSE, 'M8 service contract missing: ' . $literal);
m8_check(strpos($service, "assignment->state === 'draft'") !== FALSE && strpos($service, 'ensure_submission') !== FALSE, 'M8 workspace must hide draft and lazily create only configured submissions.');
m8_check(strpos($service, 'assignment_items') !== FALSE && strpos($service, 'submission_id') !== FALSE, 'M8 closed history must work without submission creation.');
m8_check(substr_count($model, 'FOR UPDATE') >= 3, 'M8 assignment, upload, and delete locks missing.');
m8_check(strpos($model, 'lock_evidence_count') !== FALSE && strpos($service, 'lock_evidence_count') !== FALSE, 'M8 evidence count must use transaction lock.');
m8_check(strpos($model, 'evidence_for_read') !== FALSE && strpos($model, "where_in('c.state', ['configured', 'closed'])") !== FALSE, 'M8 download owner state query missing.');
m8_check(strpos($service, 'public function download($evidence_id, $user_id)') !== FALSE && strpos($service, "private_storage_path('audit_evidence', " . '$evidence->stored_name' . ")") !== FALSE && strpos($service, 'is_file($path)') !== FALSE, 'M8 private evidence download service missing.');
m8_check(strpos($service, 'is_array($realizations)') !== FALSE && strpos($service, 'expected') !== FALSE, 'M8 forged realization item IDs must be rejected.');
m8_check(strpos($controller, 'Cache-Control: private, no-store') !== FALSE, 'M8 download cache headers missing.');
foreach (['extends CI_Controller', 'auth_guard->only([\'auditee\'])', "method(TRUE) !== 'POST'", 'Spmi_auditee_workspace_service', 'html_escape'] as $literal) m8_check(strpos($controller . $assignment . $index, $literal) !== FALSE, 'M8 controller/view contract missing: ' . $literal);
foreach (['auditee/spmi\'', 'assignment/(:num)', 'assignment/(:num)/save', 'assignment/(:num)/submit', 'item/(:num)/evidence/upload', 'evidence/(:num)/delete', 'evidence/(:num)/download'] as $literal) m8_check(strpos($routes, $literal) !== FALSE, 'M8 route missing: ' . $literal);
m8_check(substr_count($sidebar, "'key' => 'spmi_workspace', 'label' => 'Workspace SPMI', 'icon' => 'fa-laptop-house', 'url' => 'auditee/spmi', 'group' => 'Work'") === 1, 'M8 sidebar must be auditee-only.');
m8_check(strpos($assignment, 'form_open(') !== FALSE && strpos($assignment, 'form_open_multipart(') !== FALSE && substr_count($assignment, 'name="version"') >= 3 && substr_count($assignment, 'html_escape') >= 5, 'M8 forms or escaping missing.');
m8_check(strpos($assignment, 'formaction=') !== FALSE && strpos($assignment, 'name="realization[') !== FALSE && strpos($assignment, 'hidden" name="realization[') === FALSE, 'Submit must use current realization fields in same form.');
m8_check(strpos($controller, "post('version'") !== FALSE && strpos($controller, "auditee/spmi/assignment/' . " . '$assignment_id') !== FALSE, 'M8 evidence mutations need posted version and owning assignment redirect.');
foreach (['Auditee', 'Jawaban_model', 'tugas_audit', 'jawaban_audit', 'dokumen_bukti', 'link_bukti'] as $legacy) m8_check(strpos($model . $service . $controller . $index . $assignment, $legacy) === FALSE, 'M8 legacy isolation broken: ' . $legacy);

fwrite(STDOUT, "SPMI auditee workspace regression checks passed.\n");
