<?php
$root = dirname(__DIR__);
function m8_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function m8_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
function m8_table_block($source, $table) { $pattern = '/CREATE TABLE IF NOT EXISTS `' . preg_quote($table, '/') . '` \((.*?)\n\) ENGINE=/s'; if (!preg_match($pattern, $source, $match)) throw new RuntimeException('Table block missing: ' . $table); return $match[1]; }
function m8_between($source, $start, $end, $message) { $start_pos = strpos($source, $start); $end_pos = strpos($source, $end, $start_pos === FALSE ? 0 : $start_pos); if ($start_pos === FALSE || $end_pos === FALSE || $end_pos < $start_pos) throw new RuntimeException($message); return substr($source, $start_pos, $end_pos - $start_pos); }

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
$confirm = m8_source('application/views/spmi_auditee_workspace/confirm.php');

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
m8_check(strpos($assignment, 'Simpan draft') !== FALSE && strpos($assignment, '/save') !== FALSE && strpos($assignment, 'formaction=') === FALSE, 'Draft save must remain direct POST while final action leaves for confirmation.');
m8_check(strpos($assignment, 'spmi-final-submit') !== FALSE && strpos($assignment, 'type="button"') !== FALSE && strpos($assignment, 'data-confirm-url') !== FALSE && strpos($assignment, 'finalButton.addEventListener(\'click\'') !== FALSE && strpos($assignment, 'URLSearchParams') !== FALSE, 'M17-07F final control must use click-only confirmation GET without implicit submit.');
m8_check(strpos($assignment, 'event.submitter') === FALSE && strpos($assignment, 'event.preventDefault()') === FALSE, 'M17-07F final control must not depend on form submit interception.');
m8_check(strpos($assignment, 'query.append(\'version\'') !== FALSE && strpos($assignment, 'query.append(field.name, field.value)') !== FALSE && strpos($assignment, 'realization[') !== FALSE && strpos($assignment, 'evidence_url[') !== FALSE, 'M17-07F confirmation URL must carry current version and answer maps.');
m8_check(strpos($assignment, 'csrf') === FALSE && strpos($assignment, 'FormData') === FALSE, 'M17-07F confirmation URL must not serialize CSRF fields.');
m8_check(strpos($confirm, 'form_open($post_action)') !== FALSE && strpos($confirm, "submission_status === 'returned_for_revision'") !== FALSE && strpos($confirm, '/resubmit') !== FALSE && strpos($confirm, '/submit') !== FALSE, 'M17-07F confirm must select authoritative final POST from status.');
m8_check(strpos($confirm, 'array_key_exists($item_id, $preview_realizations)') !== FALSE && strpos($confirm, 'array_key_exists($item_id, $preview_evidence_urls)') !== FALSE, 'M17-07F preview must override only matching item IDs.');
m8_check(substr_count($confirm, 'html_escape') >= 10 && strpos($confirm, 'form_open_multipart') === FALSE && strpos($confirm, '<textarea') === FALSE && strpos($confirm, 'type="file"') === FALSE, 'M17-07F confirm must be escaped and read-only.');
m8_check(strpos($confirm, 'name="realization[') !== FALSE && strpos($confirm, 'name="evidence_url[') !== FALSE && strpos($confirm, 'name="version"') !== FALSE && strpos($confirm, '/download') !== FALSE, 'M17-07F confirm must post complete payload and owner-scoped evidence links.');
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
m8_check(strpos($assignment, "form_open('auditee/spmi/assignment/' . (int) \$assignment->id . '/save', ['id' => 'spmi-realization-form'])") !== FALSE && strpos($assignment, 'form_open_multipart(\'auditee/spmi/assignment/\' . (int) $assignment->id . \'/save\'') === FALSE, 'M17-07G realization draft form must stay global non-multipart.');
m8_check(strpos($assignment, "form_open_multipart('auditee/spmi/item/' . (int) \$item->assignment_item_id . '/evidence/upload', ['id' => 'spmi-evidence-upload-' . (int) \$item->assignment_item_id])") !== FALSE, 'M17-07G upload helper must be a detached multipart form keyed by assignment_item_id.');
m8_check(strpos($assignment, 'type="file"') !== FALSE && strpos($assignment, 'name="evidence"') !== FALSE && strpos($assignment, 'accept="application/pdf,image/jpeg,image/png"') !== FALSE && strpos($assignment, 'required') !== FALSE, 'M17-07G evidence file input contract missing name/accept/required controls.');
m8_check(strpos($assignment, 'form="spmi-evidence-upload-<?php echo (int) $item->assignment_item_id; ?>"') !== FALSE, 'M17-07G card-local evidence controls must use external form association per assignment item.');
m8_check(strpos($assignment, 'name="version" value="<?php echo html_escape((string) $version); ?>" form="spmi-evidence-upload-<?php echo (int) $item->assignment_item_id; ?>"') !== FALSE || strpos($assignment, 'form="spmi-evidence-upload-<?php echo (int) $item->assignment_item_id; ?>"><input type="hidden" name="version" value="<?php echo html_escape((string) $version); ?>">') !== FALSE, 'M17-07G upload helper must carry version with the detached assignment-item form.');
m8_check(strpos($assignment, '$file_capable = in_array($item->evidence_policy, [\'file\', \'either\', \'both\'], TRUE);') !== FALSE && strpos($assignment, '$url_capable = in_array($item->evidence_policy, [\'url\', \'either\', \'both\'], TRUE);') !== FALSE, 'M17-07G policy-aware source visibility missing actual file/url capability calculations.');
$assignment_card_block = m8_between($assignment, '<?php foreach ($items as $item): ?><article class="card mb-3"><div class="card-body">', '<?php endforeach; ?>', 'M17-07G assignment card block missing.');
m8_check(strpos($assignment_card_block, 'name="evidence_url[') !== FALSE && strpos($assignment_card_block, 'form_open_multipart(') !== FALSE && strpos($assignment_card_block, 'name="evidence"') !== FALSE, 'M17-07G evidence URL and file controls must live inside each question card.');
m8_check(strpos($assignment_card_block, 'name="evidence_url[') < strpos($assignment_card_block, 'name="evidence"'), 'M17-07G evidence source order must render URL before file control inside the card.');
$global_form_close_pos = strpos($assignment, '<?php echo form_close(); ?><script>');
$upload_emit_pos = strpos($assignment, '<?php foreach ($upload_forms as $upload_form): ?>');
$delete_emit_pos = strpos($assignment, '<?php foreach ($delete_forms as $delete_form): ?>');
$upload_buffer_pos = strpos($assignment, '<?php ob_start(); ?><?php echo form_open_multipart(');
$delete_buffer_pos = strpos($assignment, '<?php $delete_form_id = \'spmi-evidence-delete-');
$upload_control_pos = strpos($assignment, 'form="spmi-evidence-upload-<?php echo (int) $item->assignment_item_id; ?>"');
$delete_control_pos = strpos($assignment, 'form="<?php echo $delete_form_id; ?>"');
m8_check($global_form_close_pos !== FALSE && $upload_emit_pos !== FALSE && $delete_emit_pos !== FALSE && $upload_buffer_pos !== FALSE && $delete_buffer_pos !== FALSE && $upload_control_pos !== FALSE && $delete_control_pos !== FALSE, 'M17-07G buffered upload/delete form ordering markers missing.');
m8_check($upload_buffer_pos < $global_form_close_pos && $delete_buffer_pos < $global_form_close_pos, 'M17-07G detached upload/delete forms must be buffered before the global realization form closes.');
m8_check($upload_emit_pos > $global_form_close_pos && $delete_emit_pos > $global_form_close_pos, 'M17-07G buffered upload/delete forms must be emitted only after the global realization form closes.');
m8_check($upload_control_pos < $upload_emit_pos && $delete_control_pos < $delete_emit_pos, 'M17-07G card-local controls must remain externally associated before buffered form output is emitted.');
$assignment_script = m8_between($assignment, '<script>', '</script>', 'M17-07G assignment script block missing.');
foreach (['URLSearchParams', 'query.append(field.name, field.value)', 'realization[', 'evidence_url['] as $literal) m8_check(strpos($assignment_script, $literal) !== FALSE, 'M17-07G text preview script contract missing: ' . $literal);
foreach (['FormData', 'type="file"', 'name="evidence"', 'spmi-evidence-upload-', 'multipart'] as $literal) m8_check(strpos($assignment_script, $literal) === FALSE, 'M17-07G assignment JavaScript must remain text-only, found: ' . $literal);

m8_check(strpos($routes, 'auditee/spmi/assignment/(:num)/resubmit') !== FALSE, 'M17-04 auditee resubmit POST route missing.');
m8_check(strpos($controller, 'public function resubmit(') !== FALSE && strpos($controller, "method(TRUE) !== 'POST'") !== FALSE && strpos($controller, 'resubmit(') !== FALSE, 'M17-04 auditee resubmit POST action missing.');
m8_check(strpos($service, 'public function resubmit(') !== FALSE && strpos($service, "status !== 'returned_for_revision'") !== FALSE && strpos($service, "'status' => 'resubmitted'") !== FALSE, 'M17-04 returned_for_revision to resubmitted transition missing.');
m8_check(strpos($service, "status !== 'returned_for_revision'") !== FALSE && strpos($service, 'update_realization') !== FALSE && strpos($service, 'update_version') !== FALSE, 'M17-04 auditee edits must be restricted to returned revisions and versioned.');
m8_check(strpos($model, 'revision_history') !== FALSE && strpos($model, 'a.auditee_id') !== FALSE && strpos($model, 'spmi_auditee_submission_revision_events') !== FALSE, 'M17-04 ownership-scoped immutable revision history read missing.');

m8_check(strpos($routes, 'auditee/spmi/assignment/(:num)/final-result') !== FALSE && strpos($routes, 'spmi_auditee_workspace/final_result/$1') !== FALSE, 'M17-06 auditee final-result route must live under SPMI assignment namespace.');
m8_check(strpos($controller, 'public function final_result(') !== FALSE && strpos($controller, 'final_result(') !== FALSE && strpos($controller, 'spmi_auditee_workspace/final_result') !== FALSE, 'M17-06 auditee final-result controller action/view missing.');
m8_check(strpos($service, 'public function final_result(') !== FALSE && strpos($service, 'final_result_for_auditee') !== FALSE, 'M17-06 auditee final-result service must resolve readonly report snapshot.');
m8_check(strpos($model, 'final_result_for_auditee') !== FALSE && strpos($model, 'spmi_reports r') !== FALSE && strpos($model, 'spmi_auditor_assessments aa') !== FALSE && strpos($model, 'spmi_audit_assignments a') !== FALSE && strpos($model, 'a.auditee_id') !== FALSE, 'M17-06 final-result ownership SQL must bind report->assessment->assignment and filter auditee_id.');
m8_check(preg_match('/WHERE\s+r\.assessment_id\s*=\s*aa\.id\s+AND\s+aa\.assignment_id\s*=\s*a\.id\s+AND\s+a\.id\s*=\s*\?\s+AND\s+a\.auditee_id\s*=\s*\?/i', $model) || (strpos($model, 'r.assessment_id = aa.id') !== FALSE && strpos($model, 'aa.assignment_id = a.id') !== FALSE && strpos($model, 'a.id = ?') !== FALSE && strpos($model, 'a.auditee_id = ?') !== FALSE), 'M17-06 direct foreign final-result URL must resolve no owned report.');
$final_result_view = m8_source('application/views/spmi_auditee_workspace/final_result.php');
foreach (['report_number', 'cycle_code_snapshot', 'report_items', 'finding_type_snapshot', 'evidence_url_snapshot', 'evidence_file_original_name_snapshot', 'evidence_file_mime_type_snapshot', 'evidence_file_size_bytes_snapshot', 'evidence_file_sha256_snapshot'] as $literal) m8_check(strpos($final_result_view, $literal) !== FALSE, 'M17-06 final-result view must read snapshot field: ' . $literal);
m8_check(substr_count($final_result_view, 'html_escape') >= 8 && strpos($final_result_view, 'nl2br(html_escape(') !== FALSE, 'M17-06 final-result view must safely escape snapshot output.');
foreach (['form_open', 'form_open_multipart', 'formaction=', 'method="post"', '<textarea', '<input', '<button type="submit"'] as $mutable) m8_check(strpos($final_result_view, $mutable) === FALSE, 'M17-06 final-result view must be readonly and contain no mutation control: ' . $mutable);

foreach (['cycle_id', 'status', "s.status IS NULL OR s.status IN ('draft', 'submitted', 'returned_for_revision', 'resubmitted')", 'a.auditee_id'] as $literal) m8_check(strpos($model, $literal) !== FALSE, 'M17-07E auditee owned filter query missing: ' . $literal);
m8_check(strpos($model, 'attention_count') !== FALSE && strpos($model, "s.status IS NULL OR s.status IN ('draft', 'returned_for_revision')") !== FALSE, 'M17-07E auditee badge must include lazy missing submission, draft, and returned work.');
foreach (['filter', 'cycle_options', 'attention_count', 'menu_badges', 'is_scalar', 'ctype_digit'] as $literal) m8_check(strpos($controller, $literal) !== FALSE, 'M17-07E auditee scalar-safe filter/badge controller contract missing: ' . $literal);
foreach (['cycle_options', 'attention_count', 'assignments($user_id, $filters)', 'filters'] as $literal) m8_check(strpos($service, $literal) !== FALSE, 'M17-07E auditee filter/badge service contract missing: ' . $literal);
foreach (['method="get"', 'name="cycle_id"', 'name="status"', 'site_url(\'auditee/spmi\')', 'html_escape'] as $literal) m8_check(strpos($index, $literal) !== FALSE, 'M17-07E auditee GET filter UI contract missing: ' . $literal);
foreach (['Spmi_auditee_workspace_service', 'attention_count($user_id)', 'spmi_workspace'] as $literal) m8_check(strpos(m8_source('application/controllers/Spmi_auditee_dashboard.php'), $literal) !== FALSE, 'M17-07E auditee dashboard must propagate workspace attention badge: ' . $literal);

m8_check(strpos($routes, 'auditee/spmi/assignment/(:num)/confirm') !== FALSE && strpos($routes, 'spmi_auditee_workspace/confirm/$1') !== FALSE, 'M17-07F auditee confirmation GET route missing.');
m8_check(strpos($controller, 'public function confirm(') !== FALSE && strpos($controller, 'show_error(\'Penugasan tidak ditemukan.\', 404') !== FALSE && strpos($controller, 'show_error(\'Penugasan belum dapat dikonfirmasi.\', 403') !== FALSE, 'M17-07F confirm controller must split 404 missing/non-owned and 403 ineligible.');
m8_check(strpos($controller, "'version' => \$this->preview_scalar('version')") !== FALSE && strpos($controller, "'realization' => \$this->preview_map('realization')") !== FALSE && strpos($controller, "'evidence_url' => \$this->preview_map('evidence_url')") !== FALSE && strpos($controller, 'input->get($key, TRUE)') !== FALSE && strpos($controller, "spmi_auditee_workspace/confirm") !== FALSE, 'M17-07F confirm controller must pass safe GET preview only to confirm view.');
m8_check(strpos($controller, "'active_menu' => 'spmi_workspace'") !== FALSE && strpos($controller, "'menu_badges' => ['spmi_workspace' => " . '$attention_count') !== FALSE, 'M17-07F confirm controller must preserve active sidebar and badges.');
m8_check(strpos($service, 'public function confirm($assignment_id, $user_id)') !== FALSE && strpos($service, 'confirmation_assignment') !== FALSE && strpos($service, "'status' => 'not_found'") !== FALSE && strpos($service, "'status' => 'forbidden'") !== FALSE && strpos($service, "'status' => 'ok'") !== FALSE, 'M17-07F confirm service must return distinct state result.');
m8_check(strpos($service, "submission_status, ['draft', 'returned_for_revision'], TRUE") !== FALSE && strpos($service, "state !== 'configured'") !== FALSE && strpos($service, '!$assignment->submission_id') !== FALSE, 'M17-07F confirm service eligibility must require configured cycle and existing draft/returned submission.');
$confirm_service = preg_replace('/\s+/', ' ', $service);
m8_check(preg_match('/public function confirm\(\$assignment_id, \$user_id\).*?protected function filters/s', $service, $confirm_match), 'M17-07F confirm service block missing.');
foreach (['workspace(', 'ensure_submission', 'trans_begin', 'FOR UPDATE', 'insert(', 'update(', 'delete('] as $side_effect) m8_check(strpos($confirm_match[0], $side_effect) === FALSE, 'M17-07F confirm service must be read-only, found side effect token: ' . $side_effect);
m8_check(strpos($confirm_match[0], 'items($assignment->submission_id)') !== FALSE && strpos($confirm_match[0], 'evidence($item->id)') !== FALSE, 'M17-07F confirm service must load current items and evidence only after eligibility.');
m8_check(strpos($model, 'public function confirmation_assignment($assignment_id, $user_id)') !== FALSE && strpos($model, 'a.auditee_id = ?') !== FALSE && strpos($model, 'LEFT JOIN spmi_auditee_submissions') !== FALSE, 'M17-07F confirm model must be owner-scoped and preserve missing submission visibility.');
preg_match('/public function confirmation_assignment\(\$assignment_id, \$user_id\).*?public function ensure_submission/s', $model, $confirm_model_match);
m8_check(!empty($confirm_model_match), 'M17-07F confirmation model block missing.');
foreach (['FOR UPDATE', 'insert(', 'update(', 'delete('] as $side_effect) m8_check(strpos($confirm_model_match[0], $side_effect) === FALSE, 'M17-07F confirmation model accessor must be read-only, found side effect token: ' . $side_effect);

fwrite(STDOUT, "SPMI auditee workspace regression checks passed.\n");
