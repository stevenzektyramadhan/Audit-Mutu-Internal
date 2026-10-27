<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Custom Application Configuration (AMI)
|--------------------------------------------------------------------------
|
| File konfigurasi native CodeIgniter 3 untuk pengaturan khusus AMI.
| Nilai di bawah ini merupakan nilai default (development / fallback).
| Untuk lingkungan production, nilai ini dapat di-override melalui:
| application/config/production/ami.php
|
*/

// Storage Paths
$config['private_storage_path'] = ''; // Jika kosong, helper menggunakan default sys_get_temp_dir() / dev fallback
$config['public_uploads_path']  = ''; // Jika kosong, helper menggunakan FCPATH . 'uploads'

// PDDIKTI Base URLs (JSON string atau URL dipisahkan koma)
$config['pddikti_base_urls'] = '';

// Pengaturan Email / Brevo (Password Reset)
$config['mail_from_address'] = '';
$config['mail_from_name']    = 'Audit Mutu Internal';
$config['brevo_api_key']     = '';

// Google Drive Storage (Opsional)
$config['spmi_evidence_storage_backend']               = 'local'; // 'local' atau 'google_drive'
$config['google_drive_evidence_folder_id']             = '';
$config['google_drive_auth_mode']                      = ''; // 'service_account' untuk production
$config['google_drive_service_account_json_path']      = '';
$config['google_drive_oauth_client_secret_json_path']  = '';
$config['google_drive_oauth_refresh_token_json_path']  = '';
