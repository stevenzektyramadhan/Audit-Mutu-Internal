<?php
$root = dirname(__DIR__);
function spmi_ui_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function spmi_ui_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }

$header = spmi_ui_source('application/views/layouts/header.php');
$lists = [
    'application/views/lpmpi/spmi_audits/index.php',
    'application/views/lpmpi/spmi_standards/index.php',
    'application/views/lpmpi/spmi_instruments/index.php',
    'application/views/lpmpi/spmi_indicators/index.php',
    'application/views/lpmpi/spmi_reports/index.php',
    'application/views/lpmpi/spmi_rtm/index.php',
    'application/views/lpmpi/spmi_follow_ups/index.php',
];
$details = [
    'application/views/lpmpi/spmi_indicators/indicator_detail.php',
    'application/views/lpmpi/spmi_reports/detail.php',
    'application/views/lpmpi/spmi_rtm/detail.php',
    'application/views/lpmpi/spmi_follow_ups/detail.php',
    'application/views/lpmpi/spmi_standards/version_detail.php',
    'application/views/lpmpi/spmi_instruments/package_detail.php',
    'application/views/lpmpi/spmi_instruments/question_detail.php',
];

foreach (['.ami-row-actions', '.ami-action-btn', '.btn-ami', '.btn-outline-ami'] as $literal) spmi_ui_check(strpos($header, $literal) !== FALSE, 'SPMI UI header primitive missing: ' . $literal);

foreach ($lists as $path) {
    $view = spmi_ui_source($path);
    spmi_ui_check(strpos($view, 'html_escape') !== FALSE, 'SPMI UI list must keep escaped output: ' . $path);
    spmi_ui_check(strpos($view, 'ami-row-actions') !== FALSE, 'SPMI UI list actions must use .ami-row-actions: ' . $path);
    spmi_ui_check(strpos($view, 'ami-action-btn') !== FALSE, 'SPMI UI list actions must use .ami-action-btn: ' . $path);
}

foreach ($details as $path) {
    $view = spmi_ui_source($path);
    spmi_ui_check(strpos($view, 'html_escape') !== FALSE, 'SPMI UI detail must keep escaped output: ' . $path);
    spmi_ui_check(strpos($view, 'ami-row-actions') !== FALSE, 'SPMI UI detail actions must use .ami-row-actions: ' . $path);
    spmi_ui_check(strpos($view, 'ami-action-btn') !== FALSE, 'SPMI UI detail actions must use .ami-action-btn: ' . $path);
    spmi_ui_check(strpos($view, 'Kembali') !== FALSE, 'SPMI UI detail must expose a Kembali link: ' . $path);
}

$rtm_detail = spmi_ui_source('application/views/lpmpi/spmi_rtm/detail.php');
foreach (['lpmpi/spmi-follow-ups/create/', '$meeting->status === "resolved"', '(int) $decision->has_follow_up === 0'] as $literal) spmi_ui_check(strpos($rtm_detail, $literal) !== FALSE, 'SPMI UI RTM follow-up guard literal missing: ' . $literal);

fwrite(STDOUT, "SPMI UI consistency regression checks passed.\n");
