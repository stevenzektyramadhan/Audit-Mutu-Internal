<?php
$root = dirname(__DIR__);
function m8_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function m8_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
function m8_table_block($source, $table) { $pattern = '/CREATE TABLE IF NOT EXISTS `' . preg_quote($table, '/') . '` \((.*?)\n\) ENGINE=/s'; if (!preg_match($pattern, $source, $match)) throw new RuntimeException('Table block missing: ' . $table); return $match[1]; }

$migration = m8_source('migrations/018_create_spmi_auditee_workspace.sql');
$schema = m8_source('database_schema.sql');
$helper = m8_source('application/helpers/app_helper.php');
$model = m8_source('application/models/Spmi_auditee_workspace_model.php');
$service = m8_source('application/services/Spmi_auditee_workspace_service.php');
$audits_model = m8_source('application/models/Spmi_audits_model.php');
$audits_service = m8_source('application/services/Spmi_audits_service.php');
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

$assignment_items_schema = m8_table_block($schema, 'spmi_audit_assignment_items');
$submission_items_schema = m8_table_block($schema, 'spmi_auditee_submission_items');
m8_check(strpos($assignment_items_schema, "`evidence_policy` ENUM('none','file','url','either','both') NOT NULL DEFAULT 'none'") !== FALSE, 'M17-03 assignment-item schema must snapshot immutable evidence_policy capacity.');
m8_check(strpos($submission_items_schema, '`evidence_url` VARCHAR(500) NULL') !== FALSE, 'M17-03 submission item schema must persist evidence_url.');
m8_check(strpos($audits_model, 'evidence_policy') !== FALSE && preg_match('/package_questions\([^)]*\).*select\([^\n]*q\.\*,/s', $audits_model), 'M17-03 assignment creation source query must expose question evidence_policy.');
m8_check(strpos($audits_service, "'evidence_policy' => $" . "question->evidence_policy") !== FALSE, 'M17-03 assignment creation must copy question evidence_policy into assignment item snapshot.');
m8_check(strpos($model, 'ai.evidence_policy') !== FALSE && strpos($model, 'si.evidence_url') !== FALSE, 'M17-03 auditee model must read policy snapshot and existing evidence_url.');
m8_check(strpos($model, 'update_realization') !== FALSE && strpos($model, 'evidence_url') !== FALSE, 'M17-03 auditee model must persist evidence_url with realization.');
m8_check(strpos($service, '$evidence_urls') !== FALSE && strpos($service, 'evidence_policy') !== FALSE && strpos($service, 'validate_evidence_url') !== FALSE, 'M17-03 submit path must branch on evidence_policy snapshots and validate evidence URLs.');
m8_check(strpos($service, "case 'none'") !== FALSE && strpos($service, "case 'file'") !== FALSE && strpos($service, "case 'url'") !== FALSE && strpos($service, "case 'either'") !== FALSE && strpos($service, "case 'both'") !== FALSE, 'M17-03 submit path must cover none/file/url/either/both policies.');
m8_check(strpos($service, "case 'none'") !== FALSE && preg_match("/case 'none'.{0,600}trim/s", $service), 'M17-03 none policy still requires realization while evidence remains optional.');
m8_check(strpos($service, "case 'file'") !== FALSE && preg_match("/case 'file'.{0,600}(count_evidence|lock_evidence_count)/s", $service), 'M17-03 file policy must require at least one private evidence file.');
m8_check(strpos($service, "case 'url'") !== FALSE && preg_match("/case 'url'.{0,600}validate_evidence_url/s", $service), 'M17-03 url policy must require a valid HTTP/HTTPS URL.');
m8_check(strpos($service, "case 'either'") !== FALSE && preg_match("/case 'either'.{0,800}(validate_evidence_url).{0,800}(count_evidence|lock_evidence_count)|(count_evidence|lock_evidence_count).{0,800}(validate_evidence_url)/s", $service), 'M17-03 either policy must require valid URL or file.');
m8_check(strpos($service, "case 'both'") !== FALSE && preg_match("/case 'both'.{0,800}(validate_evidence_url).{0,800}(count_evidence|lock_evidence_count)|(count_evidence|lock_evidence_count).{0,800}(validate_evidence_url)/s", $service), 'M17-03 both policy must require valid URL and file.');
m8_check(strpos($service, 'filter_var') !== FALSE && strpos($service, 'FILTER_VALIDATE_URL') !== FALSE && strpos($service, 'parse_url') !== FALSE && strpos($service, 'in_array($scheme, [\'http\', \'https\'], TRUE)') !== FALSE, 'M17-03 evidence URL validation must be syntax-only HTTP/HTTPS.');
foreach (['curl_', 'file_get_contents($url', 'fopen($url', 'get_headers', 'fsockopen', 'stream_socket_client'] as $remote_fetch) m8_check(strpos($service, $remote_fetch) === FALSE, 'M17-03 evidence URL handling must not fetch remote URLs: ' . $remote_fetch);
foreach (['finfo_open', 'getimagesize', '5 * 1024 * 1024', 'private_storage_path', 'lock_evidence_count', '>= 5', 'evidence_for_update', 'evidence_for_read', 'a.auditee_id', 'update_version'] as $literal) m8_check(strpos($service . $model, $literal) !== FALSE, 'M17-03 must preserve private owned versioned file controls: ' . $literal);
m8_check(strpos($assignment, 'evidence_policy') !== FALSE && strpos($assignment, 'name="evidence_url[') !== FALSE && strpos($assignment, 'type="url"') !== FALSE && strpos($assignment, 'html_escape($item->evidence_url') !== FALSE, 'M17-03 UI must expose policy and controlled escaped evidence_url input.');

m8_check(strpos($routes, 'auditee/spmi/assignment/(:num)/resubmit') !== FALSE, 'M17-04 auditee resubmit POST route missing.');
m8_check(strpos($controller, 'public function resubmit(') !== FALSE && strpos($controller, "method(TRUE) !== 'POST'") !== FALSE && strpos($controller, 'resubmit(') !== FALSE, 'M17-04 auditee resubmit POST action missing.');
m8_check(strpos($service, 'public function resubmit(') !== FALSE && strpos($service, "status !== 'returned_for_revision'") !== FALSE && strpos($service, "'status' => 'resubmitted'") !== FALSE, 'M17-04 returned_for_revision to resubmitted transition missing.');
m8_check(strpos($service, "status !== 'returned_for_revision'") !== FALSE && strpos($service, 'update_realization') !== FALSE && strpos($service, 'update_version') !== FALSE, 'M17-04 auditee edits must be restricted to returned revisions and versioned.');
m8_check(strpos($model, 'revision_history') !== FALSE && strpos($model, 'a.auditee_id') !== FALSE && strpos($model, 'spmi_auditee_submission_revision_events') !== FALSE, 'M17-04 ownership-scoped immutable revision history read missing.');

fwrite(STDOUT, "SPMI auditee workspace regression checks passed.\n");
