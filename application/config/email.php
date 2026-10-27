<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('ami_runtime_config_item')) {
	require_once APPPATH.'config/runtime_config.php';
}

$mail_from_address = trim((string) ami_runtime_config_item('email', 'from_address', 'MAIL_FROM_ADDRESS', ''));
$mail_from_name = trim((string) ami_runtime_config_item('email', 'from_name', 'MAIL_FROM_NAME', ''));
$brevo_api_key = trim((string) ami_runtime_config_item('email', 'brevo_api_key', 'BREVO_API_KEY', ''));

$config['password_reset_mail_enabled'] = $brevo_api_key !== '' && $mail_from_address !== '';
$config['password_reset_brevo_api_key'] = $brevo_api_key;
$config['password_reset_brevo_api_url'] = 'https://api.brevo.com/v3/smtp/email';
$config['password_reset_from_address'] = $mail_from_address;
$config['password_reset_from_name'] = $mail_from_name;
