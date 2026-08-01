<?php
$root = dirname(__DIR__);
function rtm_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function rtm_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }

$migration = rtm_source('migrations/021_create_spmi_rtm_meetings.sql');
$schema = rtm_source('database_schema.sql');
$model = rtm_source('application/models/Spmi_rtm_model.php');
$service = rtm_source('application/services/Spmi_rtm_service.php');
$controller = rtm_source('application/controllers/lpmpi/Spmi_rtm.php');
$routes = rtm_source('application/config/routes.php');
$sidebar = rtm_source('application/views/layouts/sidebar.php');
$views = rtm_source('application/views/lpmpi/spmi_rtm/index.php') . rtm_source('application/views/lpmpi/spmi_rtm/form.php') . rtm_source('application/views/lpmpi/spmi_rtm/detail.php') . rtm_source('application/views/lpmpi/spmi_rtm/print.php');

foreach (['spmi_rtm_meetings', 'spmi_rtm_meeting_reports', 'spmi_rtm_participants', 'spmi_rtm_decisions', 'status` ENUM(\'draft\',\'resolved\')', 'name_snapshot', 'email_snapshot', 'role_snapshot', 'decision_text', 'action_text', 'UNIQUE KEY `uq_spmi_rtm_meetings_code`', 'UNIQUE KEY `uq_spmi_rtm_meeting_reports_report`', 'UNIQUE KEY `uq_spmi_rtm_participants_user`', 'UNIQUE KEY `uq_spmi_rtm_decisions_order', 'ON DELETE RESTRICT', 'ON UPDATE RESTRICT', 'ENGINE=InnoDB DEFAULT CHARSET=utf8'] as $literal) rtm_check(strpos($migration, $literal) !== FALSE, 'M11 migration contract missing: ' . $literal);
rtm_check(!preg_match('/(^|;|\R)\s*(INSERT|UPDATE|DELETE)\s+/i', $migration), 'M11 migration must be seed-free.');
foreach (['current parity migration 001-021', 'spmi_rtm_meetings', 'spmi_rtm_meeting_reports', 'spmi_rtm_participants', 'spmi_rtm_decisions', 'uq_spmi_rtm_decisions_order'] as $literal) rtm_check(strpos($schema, $literal) !== FALSE, 'M11 schema parity missing: ' . $literal);
foreach (['meeting', 'meeting_reports', 'participants', 'decisions', 'report_for_update', 'report_item_for_update', 'users_for_update', 'FOR UPDATE', 'delete_children'] as $literal) rtm_check(strpos($model, $literal) !== FALSE, 'M11 model contract missing: ' . $literal);
foreach (['trans_begin', 'trans_rollback', 'trans_complete', 'status !== \'draft\'', 'status\' => \'resolved\'', 'meeting', 'report_for_update', 'users_for_update', 'report_item_id', 'decision_text', 'action_text', 'strtoupper', 'DateTime::createFromFormat', '!Y-m-d', '/^[A-Z0-9._-]+$/', 'strlen($meeting_data[\'meeting_code\']) > 128', 'strlen($meeting_data[\'meeting_title\']) > 200', 'strlen($meeting_data[\'location\']) > 200', 'isset($data[\'decisions\']) ? $data[\'decisions\'] : []', '[\'valid\' => $valid, \'rows\' => $result]'] as $literal) rtm_check(strpos($service, $literal) !== FALSE, 'M11 service contract missing: ' . $literal);
rtm_check(strpos($service, 'if (!$decision_text && !$action_text && !$report_id && !$report_item_id) continue;') !== FALSE, 'Blank optional decision rows must be ignored.');
rtm_check(strpos($service, 'if (!$decision_text || !$action_text) { $valid = FALSE;') !== FALSE, 'Partial decision rows must be rejected.');
rtm_check(strpos($service, 'if (!$decisions[\'valid\'] || !$decisions[\'rows\'])') !== FALSE, 'At least one complete decision row must be required.');
foreach (['extends Admin_Lpmpi_Controller', "method(TRUE) !== 'POST'", 'store', 'update', 'resolve', 'print_report', 'Spmi_rtm_service', 'show_error'] as $literal) rtm_check(strpos($controller, $literal) !== FALSE, 'M11 controller contract missing: ' . $literal);
foreach (['lpmpi/spmi-rtm', 'create', 'store', 'detail/(:num)', 'edit/(:num)', 'update/(:num)', 'resolve/(:num)', 'print/(:num)'] as $literal) rtm_check(strpos($routes, $literal) !== FALSE, 'M11 route missing: ' . $literal);
rtm_check(substr_count($sidebar, "'key' => 'spmi_rtm', 'label' => 'RTM SPMI', 'icon' => 'fa-users-cog', 'url' => 'lpmpi/spmi-rtm', 'group' => 'Insights'") === 2, 'M11 sidebar entry must be management-only.');
rtm_check(strpos($views, 'form_open(') !== FALSE && strpos($views, 'html_escape') !== FALSE && strpos($views, 'nl2br(html_escape(') !== FALSE, 'M11 views must use CSRF forms and escaped multiline output.');
rtm_check(strpos($views, 'M12') === FALSE && strpos($model . $service . $controller . $views, 'tugas_audit') === FALSE, 'M11 must stay isolated from legacy and M12 workflow.');
rtm_check(strpos($views, "status === 'draft'") !== FALSE && strpos($views, 'resolved permanen dan hanya-baca') !== FALSE, 'Resolved RTM must be read-only.');

fwrite(STDOUT, "SPMI RTM regression checks passed.\n");
