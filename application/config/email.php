<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$mail_from_address = isset($config['mail_from_address']) ? trim((string) $config['mail_from_address']) : '';
$mail_from_name = isset($config['mail_from_name']) ? trim((string) $config['mail_from_name']) : '';
$brevo_api_key = isset($config['brevo_api_key']) ? trim((string) $config['brevo_api_key']) : '';

$config['password_reset_mail_enabled'] = $brevo_api_key !== '' && $mail_from_address !== '';
$config['password_reset_brevo_api_key'] = $brevo_api_key;
$config['password_reset_brevo_api_url'] = 'https://api.brevo.com/v3/smtp/email';
$config['password_reset_from_address'] = $mail_from_address;
$config['password_reset_from_name'] = $mail_from_name;
