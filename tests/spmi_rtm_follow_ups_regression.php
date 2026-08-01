<?php
$root = dirname(__DIR__);
function follow_up_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function follow_up_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
$migration = follow_up_source('migrations/022_create_spmi_rtm_follow_ups.sql');
$schema = follow_up_source('database_schema.sql');
$model = follow_up_source('application/models/Spmi_rtm_follow_ups_model.php');
$service = follow_up_source('application/services/Spmi_rtm_follow_ups_service.php');
$controller = follow_up_source('application/controllers/lpmpi/Spmi_follow_ups.php');
$routes = follow_up_source('application/config/routes.php');
$sidebar = follow_up_source('application/views/layouts/sidebar.php');
$views = follow_up_source('application/views/lpmpi/spmi_follow_ups/index.php') . follow_up_source('application/views/lpmpi/spmi_follow_ups/form.php') . follow_up_source('application/views/lpmpi/spmi_follow_ups/detail.php');
foreach (['spmi_rtm_follow_ups', 'follow_up_code', 'decision_id', 'decision_text_snapshot', 'action_text_snapshot', 'responsible_name_snapshot', 'responsible_email_snapshot', 'responsible_role_snapshot', "status` ENUM('open','in_progress','completed')", 'started_by', 'started_at', 'completion_note', 'UNIQUE KEY `uq_spmi_rtm_follow_ups_code`', 'UNIQUE KEY `uq_spmi_rtm_follow_ups_decision`', 'fk_spmi_rtm_follow_ups_started_by', 'ON DELETE RESTRICT', 'ON UPDATE RESTRICT', 'ENGINE=InnoDB DEFAULT CHARSET=utf8'] as $literal) follow_up_check(strpos($migration, $literal) !== FALSE, 'M12 migration contract missing: ' . $literal);
follow_up_check(!preg_match('/(^|;|\R)\s*INSERT\s+/i', $migration), 'M12 migration must be seed-free.');
foreach (['current parity migration 001-022', 'spmi_rtm_follow_ups', 'follow_up_code', 'started_by', 'started_at', 'uq_spmi_rtm_follow_ups_code', 'uq_spmi_rtm_follow_ups_decision', 'fk_spmi_rtm_follow_ups_started_by'] as $literal) follow_up_check(strpos($schema, $literal) !== FALSE, 'M12 schema parity missing: ' . $literal);
foreach (['follow_ups', 'resolved_decision_for_update', 'follow_up_by_decision_for_update', 'user_for_update', 'FOR UPDATE', 'insert_follow_up', 'update_follow_up'] as $literal) follow_up_check(strpos($model, $literal) !== FALSE, 'M12 model contract missing: ' . $literal);
foreach (['trans_begin', 'trans_rollback', 'trans_complete', 'TRANSITIONS', "'open' => ['in_progress']", "'in_progress' => ['completed']", "'completed' => []", 'meeting_status !== \'resolved\'', "'follow_up_code' => 'RTM-FU-'", 'decision_text_snapshot', 'responsible_name_snapshot', 'status !== \'open\'', 'started_by', 'started_at', 'Catatan penyelesaian wajib diisi.', 'completed_by', 'completed_at'] as $literal) follow_up_check(strpos($service, $literal) !== FALSE, 'M12 service contract missing: ' . $literal);
follow_up_check(strpos($service, "if (\$status === 'in_progress')") !== FALSE, 'M12 start transition must persist actor and timestamp.');
foreach (['extends Admin_Lpmpi_Controller', 'store', 'update', 'transition', 'method(TRUE) !== \'POST\'', 'Spmi_rtm_follow_ups_service'] as $literal) follow_up_check(strpos($controller, $literal) !== FALSE, 'M12 controller contract missing: ' . $literal);
foreach (['lpmpi/spmi-follow-ups', 'create/(:num)', 'store/(:num)', 'detail/(:num)', 'edit/(:num)', 'update/(:num)', 'transition/(:num)'] as $literal) follow_up_check(strpos($routes, $literal) !== FALSE, 'M12 route missing: ' . $literal);
follow_up_check(substr_count($sidebar, "'key' => 'spmi_follow_ups', 'label' => 'Tindak Lanjut RTM', 'icon' => 'fa-tasks', 'url' => 'lpmpi/spmi-follow-ups', 'group' => 'Insights'") === 2, 'M12 sidebar entry must be management-only.');
follow_up_check(strpos($views, 'form_open(') !== FALSE && strpos($views, 'html_escape') !== FALSE && strpos($views, 'follow_up_code') !== FALSE && strpos($views, "status === 'completed'") !== FALSE, 'M12 views must use CSRF, escaped output, code, and completed readonly state.');
follow_up_check(strpos($model . $service . $controller . $views, 'tugas_audit') === FALSE && strpos($model . $service . $controller . $views, 'evidence') === FALSE && strpos($model . $service . $controller . $views, 'notification') === FALSE, 'M12 must stay isolated from legacy, evidence, and notifications.');
fwrite(STDOUT, "SPMI RTM follow-up regression checks passed.\n");
