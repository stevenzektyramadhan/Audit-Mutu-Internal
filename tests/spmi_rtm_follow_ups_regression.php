<?php
$root = dirname(__DIR__);
function follow_up_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function follow_up_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }

$migration = follow_up_source('migrations/022_create_spmi_rtm_follow_ups.sql');
$schema = follow_up_source('database_schema.sql');
foreach (['spmi_rtm_follow_ups', 'follow_up_code', 'decision_id', 'UNIQUE KEY `uq_spmi_rtm_follow_ups_code`', 'UNIQUE KEY `uq_spmi_rtm_follow_ups_decision`', 'fk_spmi_rtm_follow_ups_started_by', 'ON DELETE RESTRICT', 'ON UPDATE RESTRICT', 'ENGINE=InnoDB DEFAULT CHARSET=utf8'] as $literal) follow_up_check(strpos($migration, $literal) !== FALSE, 'Historic migration contract missing: ' . $literal);
follow_up_check(!preg_match('/(^|;|\R)\s*(DELETE|DROP)\s+/i', $migration), 'Historic follow-up migration must not remove historical data or schema.');
foreach (['current parity migration 001-022', 'spmi_rtm_follow_ups', 'follow_up_code', 'started_by', 'started_at', 'uq_spmi_rtm_follow_ups_code', 'uq_spmi_rtm_follow_ups_decision', 'fk_spmi_rtm_follow_ups_started_by'] as $literal) follow_up_check(strpos($schema, $literal) !== FALSE, 'Historic schema parity missing: ' . $literal);

foreach (['application/controllers/lpmpi/Spmi_follow_ups.php', 'application/services/Spmi_rtm_follow_ups_service.php', 'application/models/Spmi_rtm_follow_ups_model.php', 'application/views/lpmpi/spmi_follow_ups/index.php', 'application/views/lpmpi/spmi_follow_ups/form.php', 'application/views/lpmpi/spmi_follow_ups/detail.php', 'assets/css/spmi-follow-ups.css', 'assets/css/spmi-follow-ups.source.css'] as $path) follow_up_check(!is_file($root . DIRECTORY_SEPARATOR . $path), 'Retired active artifact remains: ' . $path);

$active_sources = '';
foreach (['application/config/routes.php', 'application/models/Spmi_rtm_model.php', 'application/models/Spmi_management_dashboard_model.php', 'application/models/Spmi_ppepp_recap_model.php', 'application/views/layouts/sidebar.php', 'application/views/layouts/header.php', 'application/views/lpmpi/spmi_rtm/detail.php', 'application/views/lpmpi/spmi_management_dashboard/index.php', 'application/views/lpmpi/spmi_ppepp_recap/index.php', 'tailwind.config.js'] as $path) $active_sources .= "\n" . follow_up_source($path);
foreach (['lpmpi/spmi-follow-ups', 'spmi_follow_ups', 'has_follow_up', 'follow_ups_open', 'follow_ups_in_progress', 'follow_ups_completed', 'follow_ups_overdue', 'spmi_rtm_follow_ups'] as $literal) follow_up_check(strpos($active_sources, $literal) === FALSE, 'Active follow-up reference remains: ' . $literal);

$user_model = follow_up_source('application/models/User_model.php');
follow_up_check(strpos($user_model, "['table' => 'spmi_rtm_follow_ups'") !== FALSE, 'Historic follow-up rows must remain protected from referenced-user deletion.');
fwrite(STDOUT, "SPMI RTM follow-up retirement regression checks passed.\n");
