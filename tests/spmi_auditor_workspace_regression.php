<?php
$root = dirname(__DIR__);
function m9_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function m9_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }

$migration = m9_source('migrations/019_create_spmi_auditor_workspace.sql');
$schema = m9_source('database_schema.sql');
$model = m9_source('application/models/Spmi_auditor_workspace_model.php');
$service = m9_source('application/services/Spmi_auditor_workspace_service.php');
$controller = m9_source('application/controllers/Spmi_auditor_workspace.php');
$routes = m9_source('application/config/routes.php');
$sidebar = m9_source('application/views/layouts/sidebar.php');
$index = m9_source('application/views/spmi_auditor_workspace/index.php');
$assignment = m9_source('application/views/spmi_auditor_workspace/assignment.php');

foreach (['spmi_auditor_assessments', 'spmi_auditor_assessment_items', "ENUM('draft','finalized')", 'finalized_at', 'realization_snapshot', 'finding', 'recommendation', 'UNIQUE KEY', 'ON DELETE RESTRICT'] as $literal) m9_check(strpos($migration, $literal) !== FALSE, 'M9 migration contract missing: ' . $literal);
m9_check(strpos($migration, 'INSERT') === FALSE, 'M9 migration must be seed-free.');
foreach (['current parity migration 001-019', 'spmi_auditor_assessments', 'spmi_auditor_assessment_items', 'uq_spmi_auditor_assessments_assignment', 'uq_spmi_auditor_assessment_items_item', 'idx_spmi_auditor_assessment_items_item'] as $literal) m9_check(strpos($schema, $literal) !== FALSE, 'M9 schema parity missing: ' . $literal);
foreach (['a.auditor_id', 'c.state IN (?, ?)', 'assignment_items($assignment_id, $user_id)', 'rubrics($item_id, $user_id)', 'cycle_for_update', 'submission_for_update', 'assessment_for_update', 'assessment_items_for_update', 'FOR UPDATE', 'submitted', 'closed'] as $literal) m9_check(strpos($model, $literal) !== FALSE, 'M9 ownership/lock contract missing: ' . $literal);
foreach (['trans_begin', 'state !== \'configured\'', 'status !== \'submitted\'', 'status !== \'draft\'', 'CONFLICT', 'realization_snapshot', 'raw_score', '!is_string($raw_score)', "in_array(\$raw_score, ['1', '2', '3', '4'], TRUE)", 'Skor wajib kosong atau bernilai 1 sampai 4.', 'finalize', 'affected_rows() !== 1', 'private_storage_path', 'audit_evidence'] as $literal) m9_check(strpos($service, $literal) !== FALSE, 'M9 service lifecycle contract missing: ' . $literal);
m9_check(strpos($service, "(int) \$value['score']") === FALSE && strpos($service, 'is_array($raw_score)') === FALSE, 'M9 score validation must inspect raw scalar string before conversion.');
foreach (['extends CI_Controller', 'auth_guard->only([\'auditor\'])', "method(TRUE) !== 'POST'", 'Spmi_auditor_workspace_service', 'Cache-Control: private, no-store', 'Expires: 0'] as $literal) m9_check(strpos($controller, $literal) !== FALSE, 'M9 controller contract missing: ' . $literal);
foreach (['auditor/spmi\'', 'assignment/(:num)', 'assignment/(:num)/save', 'assignment/(:num)/finalize', 'evidence/(:num)/download'] as $literal) m9_check(strpos($routes, $literal) !== FALSE, 'M9 route missing: ' . $literal);
m9_check(substr_count($sidebar, "'key' => 'spmi_assessment', 'label' => 'Penilaian SPMI', 'icon' => 'fa-clipboard-check', 'url' => 'auditor/spmi', 'group' => 'Work'") === 1, 'M9 sidebar entry must be auditor-only.');
m9_check(substr_count($assignment, 'form_open(') >= 1 && strpos($assignment, 'name="version"') !== FALSE && strpos($assignment, 'formaction=') !== FALSE, 'M9 assessment form contract missing.');
m9_check(substr_count($assignment, 'html_escape') >= 8 && strpos($assignment, 'rubrics') !== FALSE && strpos($assignment, 'realization_snapshot') !== FALSE, 'M9 immutable M7/M8 view data missing.');
foreach (['Auditor.php', 'Auditor_service.php', 'Jawaban_model', 'tugas_audit', 'jawaban_audit', 'bukti_auditor', 'auditor/penilaian', 'auditor/tugas'] as $legacy) m9_check(strpos($model . $service . $controller . $index . $assignment, $legacy) === FALSE, 'M9 legacy isolation broken: ' . $legacy);

m9_check(strpos($routes, 'auditor/spmi/assignment/(:num)/return') !== FALSE, 'M17-04 auditor return-for-revision POST route missing.');
m9_check(strpos($controller, 'public function return_for_revision(') !== FALSE && strpos($controller, "method(TRUE) !== 'POST'") !== FALSE && strpos($controller, "post('reason'") !== FALSE, 'M17-04 auditor return-for-revision POST action with mandatory reason missing.');
m9_check(strpos($service, 'return_for_revision') !== FALSE && strpos($service, 'trim($reason)') !== FALSE && strpos($service, "status !== 'submitted'") !== FALSE && strpos($service, "status !== 'resubmitted'") !== FALSE, 'M17-04 submitted or resubmitted to returned_for_revision transition missing.');
m9_check(strpos($service, "'status' => 'returned_for_revision'") !== FALSE && strpos($service, 'source_submission_version') !== FALSE && strpos($service, 'actor') !== FALSE && strpos($service, 'reason') !== FALSE, 'M17-04 return event must capture actor status version and reason.');
m9_check(strpos($model, 'revision_history') !== FALSE && strpos($model, 'a.auditor_id') !== FALSE && strpos($model, 'spmi_auditee_submission_revision_events') !== FALSE, 'M17-04 ownership-scoped immutable revision history read missing for auditor.');
m9_check(strpos($service, 'source_submission_version') !== FALSE && strpos($service, 'delete') === FALSE && strpos($service, 'reset') === FALSE && preg_match('/source_submission_version.{0,400}(CONFLICT|STALE|stale|versi)/s', $service), 'M17-04 stale assessment draft rejection using source_submission_version without deletion/reset missing.');
m9_check(strpos($model, 'create_assessment($assignment_id, $source_submission_version)') !== FALSE && strpos($model, "'source_submission_version' => (int) $" . 'source_submission_version') !== FALSE && strpos($service, 'create_assessment($assignment->id, $submission->version)') !== FALSE, 'M17-04 create path must bind assessment draft to locked submission source version.');
m9_check(preg_match('/source_submission_version\s*!==\s*NULL.{0,220}source_submission_version.{0,120}submission->version.{0,120}CONFLICT/s', $service), 'M17-04 stale save/finalize guard must compare source_submission_version to live submission version.');
m9_check(preg_match('/source_submission_version\s*===\s*NULL\s*&&\s*\$submission->status\s*===\s*\'resubmitted\'.{0,120}CONFLICT/s', $service) && preg_match('/source_submission_version\s*!==\s*NULL\s*&&\s*\(int\)\s*\$assessment->source_submission_version\s*!==\s*\(int\)\s*\$submission->version.{0,120}CONFLICT/s', $service), 'M17-04 stale guard must reject bound mismatches and unbound legacy drafts only after resubmitted.');

foreach (["post('assessment', TRUE)", '$this->service->$method'] as $literal) m9_check(strpos($controller, $literal) !== FALSE, 'M17-05 controller must pass nested assessment payload to service: ' . $literal);
m9_check(strpos($controller, "post('finding_type'") === FALSE && strpos($controller, 'finding_type_by_item') === FALSE, 'M17-05 controller must not invent a separate finding_type API.');
foreach (['finding_type', 'raw_finding_type', "in_array($" . "raw_finding_type, ['ob', 'kts'], TRUE)", '$finding_type = NULL', "'finding_type' => $" . "finding_type"] as $literal) m9_check(strpos($service, $literal) !== FALSE, 'M17-05 service finding_type normalization/allowlist contract missing: ' . $literal);
m9_check(preg_match('/finalize.{0,600}score.{0,300}(finding_type|raw_finding_type).{0,260}(finding|raw_finding).{0,260}(recommendation|raw_recommendation)/s', $service), 'M17-05 finalize must validate score, OB/KTS finding, and KTS recommendation in one server-side branch.');
m9_check(preg_match('/(finding_type|raw_finding_type).{0,140}(ob|kts).{0,180}(finding|raw_finding).{0,180}(wajib|required|harus|empty\()/is', $service), 'M17-05 OB/KTS finalization must reject missing finding.');
m9_check(preg_match('/(finding_type|raw_finding_type).{0,80}kts.{0,220}(recommendation|raw_recommendation).{0,180}(wajib|required|harus|empty\()/is', $service), 'M17-05 KTS finalization must reject missing recommendation.');
m9_check(preg_match('/raw_score.{0,260}NULL.{0,900}finding_type/s', $service), 'M17-05 draft saves must remain partial while accepting finding_type.');
foreach (['finding_type', "'finding_type'", 'update_item'] as $literal) m9_check(strpos($model . $service, $literal) !== FALSE, 'M17-05 model finding_type persistence contract missing: ' . $literal);
m9_check(strpos($model, 'spmi_auditor_assessment_items.finding_type') !== FALSE || preg_match('/assessment_items.{0,400}ai\.\*/s', $model), 'M17-05 assessment item reads must include finding_type through ai.*.');
foreach (['[finding_type]', 'value=""', 'value="ob"', 'value="kts"', 'html_escape', 'readonly', 'disabled'] as $literal) m9_check(strpos($assignment, $literal) !== FALSE, 'M17-05 view controlled/read-only/escaped finding_type contract missing: ' . $literal);
m9_check(strpos($assignment, 'action_plan') === FALSE && strpos($service, 'action_plan') === FALSE && strpos($model, 'action_plan') === FALSE, 'M17-05 assessment must not add action plan/report behavior.');

foreach (['spmi_audit_assignments a', 'spmi_auditee_submissions s', 'spmi_auditee_submission_items si', 'spmi_audit_assignment_items i'] as $literal) m9_check(strpos($model, $literal) !== FALSE, 'M17-05A auditor evidence URL source chain missing: ' . $literal);
m9_check(preg_match('/assignment_items\(\$assignment_id, \$user_id\).*select\(\'i\.\*, si\.evidence_url\'\).*from\(\'spmi_audit_assignment_items i\'\).*join\(\'spmi_audit_assignments a\', \'a\.id = i\.assignment_id\'\).*join\(\'spmi_auditee_submissions s\', \'s\.assignment_id = a\.id\'\).*join\(\'spmi_auditee_submission_items si\', \'si\.submission_id = s\.id AND si\.assignment_item_id = i\.id\'\)/s', $model) === 1, 'M17-05A assignment_items must select URL through assignment submission item ownership chain.');
m9_check(preg_match('/assignment_items\(\$assignment_id, \$user_id\).*where\(\'a\.id\', \(int\) \$assignment_id\).*where\(\'a\.auditor_id\', \(int\) \$user_id\).*where_in\(\'s\.status\', \[\'submitted\', \'resubmitted\', \'returned_for_revision\'\]\).*order_by\(\'i\.display_order\', \'ASC\'\)/s', $model) === 1, 'M17-05A assignment_items must filter exact assignment, auditor ownership, readable status, and preserve order.');
m9_check(strpos($model, 'si.evidence_url') !== FALSE, 'M17-05A auditor evidence URL query must select si.evidence_url.');
foreach (['a.auditor_id', 'a.id', 'i.assignment_id', 's.status', 'submitted', 'resubmitted'] as $literal) m9_check(strpos($model, $literal) !== FALSE, 'M17-05A auditor evidence URL owner/status constraint missing: ' . $literal);
foreach (['evidence_url', 'filter_var', 'FILTER_VALIDATE_URL', 'parse_url', 'PHP_URL_SCHEME', "['http', 'https']", 'html_escape', 'target="_blank"', 'rel="noopener noreferrer"'] as $literal) m9_check(strpos($assignment, $literal) !== FALSE, 'M17-05A safe auditor evidence URL view contract missing: ' . $literal);
m9_check(preg_match('/evidence_url\s*={2,3}\s*\'\'.{0,220}(text-muted|Tidak ada|Belum ada|-)/s', $assignment) === 1, 'M17-05A empty evidence URL must render a neutral non-clickable marker.');
m9_check(preg_match('/filter_var\([^,]+,\s*FILTER_VALIDATE_URL\)/', $assignment) === 1, 'M17-05A evidence URL validation must use filter_var URL validation.');
m9_check(preg_match('/parse_url\([^,]+,\s*PHP_URL_SCHEME\)/', $assignment) === 1, 'M17-05A evidence URL scheme extraction must use parse_url PHP_URL_SCHEME.');
m9_check(strpos($assignment, "['http', 'https']") !== FALSE && strpos($assignment, "['https', 'http']") === FALSE, 'M17-05A evidence URL scheme allowlist must be exactly http/https in that order.');
m9_check(preg_match('/<a\b[^>]*href="<\?php\s+echo\s+html_escape\([^)]*evidence_url[^)]*\);\s*\?>"[^>]*target="_blank"[^>]*rel="noopener noreferrer"/s', $assignment) === 1, 'M17-05A safe evidence URL link must escape href and open with noopener noreferrer.');
m9_check(substr_count($assignment, 'html_escape($evidence_url)') >= 2, 'M17-05A safe evidence URL link must escape href and display text.');
m9_check(preg_match('/else\s*:\s*.{0,220}(tidak valid|invalid|text-muted)/is', $assignment) === 1, 'M17-05A unsafe evidence URL schemes must render a neutral non-clickable state.');
m9_check(strpos($assignment, 'site_url(\'auditor/spmi/evidence/\' . (int) $evidence->id . \'/download\')') !== FALSE && strpos($assignment, 'realization_snapshot') !== FALSE, 'M17-05A realization/file download display must remain preserved beside URL evidence.');

fwrite(STDOUT, "SPMI auditor workspace regression checks passed.\n");
