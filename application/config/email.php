<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$mail_from_address = trim((string) getenv('MAIL_FROM_ADDRESS'));
$mail_from_name = trim((string) getenv('MAIL_FROM_NAME'));
$brevo_api_key = trim((string) getenv('BREVO_API_KEY'));

$config['password_reset_mail_enabled'] = $brevo_api_key !== '' && $mail_from_address !== '';
$config['password_reset_brevo_api_key'] = $brevo_api_key;
$config['password_reset_brevo_api_url'] = 'https://api.brevo.com/v3/smtp/email';
$config['password_reset_from_address'] = $mail_from_address;
$config['password_reset_from_name'] = $mail_from_name;
