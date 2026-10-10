<?php
$root = dirname(__DIR__);
function rtm_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function rtm_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }

$migration = rtm_source('migrations/021_create_spmi_rtm_meetings.sql');
$photo_migration = rtm_source('migrations/045_add_spmi_rtm_photo.sql');
$schema = rtm_source('database_schema.sql');
$model = rtm_source('application/models/Spmi_rtm_model.php');
$service = rtm_source('application/services/Spmi_rtm_service.php');
$controller = rtm_source('application/controllers/lpmpi/Spmi_rtm.php');
$routes = rtm_source('application/config/routes.php');
$sidebar = rtm_source('application/views/layouts/sidebar.php');
$detail = rtm_source('application/views/lpmpi/spmi_rtm/detail.php');
$word = rtm_source('application/views/lpmpi/spmi_rtm/word.php');
$form = rtm_source('application/views/lpmpi/spmi_rtm/form.php');
$views = rtm_source('application/views/lpmpi/spmi_rtm/index.php') . rtm_source('application/views/lpmpi/spmi_rtm/form.php') . rtm_source('application/views/lpmpi/spmi_rtm/detail.php') . rtm_source('application/views/lpmpi/spmi_rtm/print.php');

foreach (['spmi_rtm_meetings', 'spmi_rtm_meeting_reports', 'spmi_rtm_participants', 'spmi_rtm_decisions', 'status` ENUM(\'draft\',\'resolved\')', 'name_snapshot', 'email_snapshot', 'role_snapshot', 'decision_text', 'action_text', 'UNIQUE KEY `uq_spmi_rtm_meetings_code`', 'UNIQUE KEY `uq_spmi_rtm_meeting_reports_report`', 'UNIQUE KEY `uq_spmi_rtm_participants_user`', 'UNIQUE KEY `uq_spmi_rtm_decisions_order', 'ON DELETE RESTRICT', 'ON UPDATE RESTRICT', 'ENGINE=InnoDB DEFAULT CHARSET=utf8'] as $literal) rtm_check(strpos($migration, $literal) !== FALSE, 'M11 migration contract missing: ' . $literal);
rtm_check(!preg_match('/(^|;|\R)\s*(INSERT|UPDATE|DELETE)\s+/i', $migration), 'M11 migration must be seed-free.');
foreach (['current parity migration 001-021', 'current parity migration 001-045', 'spmi_rtm_meetings', 'spmi_rtm_meeting_reports', 'spmi_rtm_participants', 'spmi_rtm_decisions', 'uq_spmi_rtm_decisions_order', 'photo_stored_name', 'photo_original_name', 'photo_mime_type', 'photo_size_bytes', 'photo_sha256', 'uq_spmi_rtm_meetings_photo_stored_name'] as $literal) rtm_check(strpos($schema, $literal) !== FALSE, 'M11 schema parity missing: ' . $literal);
foreach (['DELIMITER $$', 'DROP PROCEDURE IF EXISTS `ami_add_spmi_rtm_photo`', 'CREATE PROCEDURE `ami_add_spmi_rtm_photo`', 'INFORMATION_SCHEMA.COLUMNS', 'INFORMATION_SCHEMA.STATISTICS', 'TABLE_SCHEMA = DATABASE()', 'photo_stored_name', 'photo_original_name', 'photo_mime_type', 'photo_size_bytes', 'photo_sha256', 'CHAR(64) NULL', 'uq_spmi_rtm_meetings_photo_stored_name', 'CALL `ami_add_spmi_rtm_photo`()', 'DROP PROCEDURE `ami_add_spmi_rtm_photo`', "'rtm_photos', 'Foto Dokumentasi RTM', 5", 'ON DUPLICATE KEY UPDATE'] as $literal) rtm_check(strpos($photo_migration, $literal) !== FALSE, 'RTM photo migration contract missing: ' . $literal);
foreach (['meeting', 'meeting_reports', 'participants', 'decisions', 'report_for_update', 'report_item_for_update', 'users_for_update', 'FOR UPDATE', 'delete_children'] as $literal) rtm_check(strpos($model, $literal) !== FALSE, 'M11 model contract missing: ' . $literal);
foreach (['trans_begin', 'trans_rollback', 'trans_complete', 'status !== \'draft\'', 'status\' => \'resolved\'', 'meeting', 'report_for_update', 'users_for_update', 'report_item_id', 'decision_text', 'action_text', 'strtoupper', 'DateTime::createFromFormat', '!Y-m-d', '/^[A-Z0-9._-]+$/', 'strlen($meeting_data[\'meeting_code\']) > 128', 'strlen($meeting_data[\'meeting_title\']) > 200', 'strlen($meeting_data[\'location\']) > 200', 'isset($data[\'decisions\']) ? $data[\'decisions\'] : []', '[\'valid\' => $valid, \'rows\' => $result]', 'save_photo', 'validate_photo', "Upload_size_settings_service::limit_bytes('rtm_photos')", 'private_storage_dir(\'rtm_photos\')', 'private_storage_path(\'rtm_photos\'', 'move_uploaded_file', 'random_bytes(24)', '@chmod($dir, 0700)', '@chmod($path, 0600)', 'hash_file(\'sha256\', $path)', 'photo_sha256', '@unlink($path)', 'is_uploaded_file', 'UPLOAD_ERR_OK', '(int) $file[\'size\'] < 1', 'image/jpeg', 'image/png', 'image/webp', 'delete_private_file(\'rtm_photos\'', 'cleanup_saved($saved)'] as $literal) rtm_check(strpos($service, $literal) !== FALSE, 'M11 service contract missing: ' . $literal);
rtm_check(strpos($service, 'if (!$decision_text && !$action_text && !$report_id && !$report_item_id) continue;') !== FALSE, 'Blank optional decision rows must be ignored.');
rtm_check(strpos($service, 'if (!$decision_text || !$action_text) { $valid = FALSE;') !== FALSE, 'Partial decision rows must be rejected.');
rtm_check(strpos($service, 'if (!$decisions[\'valid\'] || !$decisions[\'rows\'])') !== FALSE, 'At least one complete decision row must be required.');
foreach (['extends Admin_Lpmpi_Controller', "method(TRUE) !== 'POST'", 'store', 'update', 'resolve', 'print_report', 'download_photo', 'Spmi_rtm_service', 'show_error', '$_FILES[\'photo_file\']'] as $literal) rtm_check(strpos($controller, $literal) !== FALSE, 'M11 controller contract missing: ' . $literal);
foreach (['lpmpi/spmi-rtm', 'create', 'store', 'detail/(:num)', 'edit/(:num)', 'update/(:num)', 'resolve/(:num)', 'export-word/(:num)', 'print/(:num)', 'photo/(:num)'] as $literal) rtm_check(strpos($routes, $literal) !== FALSE, 'M11 route missing: ' . $literal);
rtm_check(substr_count($sidebar, "'key' => 'spmi_rtm', 'label' => 'RTM SPMI', 'icon' => 'fa-users-cog', 'url' => 'lpmpi/spmi-rtm', 'group' => 'Insights'") === 2, 'M11 sidebar entry must be management-only.');
rtm_check(strpos($views, 'form_open(') !== FALSE && strpos($views, 'html_escape') !== FALSE && strpos($views, 'nl2br(html_escape(') !== FALSE, 'M11 views must use CSRF forms and escaped multiline output.');
rtm_check(strpos($model . $service . $controller . $views, 'tugas_audit') === FALSE, 'M11 must stay isolated from legacy workflow.');
rtm_check(strpos($views, "status === 'draft'") !== FALSE && strpos($views, 'resolved permanen dan hanya-baca') !== FALSE, 'Resolved RTM must be read-only.');
foreach (['spmi_rtm_follow_ups', 'has_follow_up', 'lpmpi/spmi-follow-ups'] as $literal) rtm_check(strpos($model . $detail, $literal) === FALSE, 'Retired RTM follow-up integration remains: ' . $literal);
rtm_check(strpos($views, 'form_open_multipart') !== FALSE && strpos($views, 'photo_file') !== FALSE, 'RTM form must support optional multipart photo upload.');
rtm_check(substr_count($views, 'lpmpi/spmi-rtm/photo/') >= 3, 'RTM form, detail, and print must expose only protected photo links.');
rtm_check(strpos($views, 'photo_stored_name') === FALSE, 'RTM views must never expose stored private photo names.');
rtm_check(strpos($controller, 'export_word') !== FALSE && strpos($controller, 'application/msword; charset=UTF-8') !== FALSE && strpos($controller, 'rtm_spmi.doc') !== FALSE, 'RTM Word export must return a UTF-8 .doc attachment.');
rtm_check(substr_count($views, 'lpmpi/spmi-rtm/export-word/') >= 3, 'RTM list and detail must expose the protected Word export action.');
rtm_check(strpos($word, 'html_escape') !== FALSE && strpos($word, 'nl2br(html_escape(') !== FALSE && strpos($word, 'photo_stored_name') === FALSE && strpos($word, 'private_storage') === FALSE, 'RTM Word document must escape output and omit private photo storage data.');
rtm_check(strpos($word, 'cycle_start_date_snapshot') === FALSE && strpos($word, 'cycle_end_date_snapshot') === FALSE && strpos($word, 'auditee_name_snapshot') === FALSE && strpos($word, 'report_number') !== FALSE && strpos($word, 'cycle_code_snapshot') !== FALSE && strpos($word, 'cycle_title_snapshot') !== FALSE, 'RTM Word report table must only use fields selected by meeting_reports().');
foreach (['id="participant-search"', 'type="search"', 'aria-controls="participant-checklist"', 'hidden', 'autocomplete="off"', 'id="participant-search-status"', 'role="status"', 'aria-live="polite"', 'id="participant-checklist"', 'class="participant-row', 'data-search="<?php echo html_escape(mb_strtolower(', "'UTF-8'", 'participantSearch.value.trim().toLocaleLowerCase()', 'row.hidden = !matchesQuery && !checkbox.checked', 'participantSearch.hidden = false', 'participantSearch.addEventListener(\'input\', updateParticipantRows)', 'checkbox.addEventListener(\'change\', updateParticipantRows)', 'document.querySelectorAll(\'.user-checkbox\')', 'document.getElementById(\'participant_ids\')'] as $literal) rtm_check(strpos($form, $literal) !== FALSE, 'RTM participant search contract missing: ' . $literal);

fwrite(STDOUT, "SPMI RTM regression checks passed.\n");
