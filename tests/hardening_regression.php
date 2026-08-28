<?php

$root = dirname(__DIR__);

function source($root, $path)
{
    $contents = file_get_contents($root . DIRECTORY_SEPARATOR . $path);
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }
    return $contents;
}

function check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$json_flags = 'JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT';
check(substr_count(source($root, 'application/controllers/Profil.php'), $json_flags) === 4, 'Semua JSON chart profil harus memakai JSON_HEX flags.');
check(substr_count(source($root, 'application/controllers/lpmpi/Laporan.php'), $json_flags) === 2, 'Semua JSON chart laporan harus memakai JSON_HEX flags.');

$encoded = json_encode(['</script><script>alert("x")</script>'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
check(strpos($encoded, '<script>') === FALSE && strpos($encoded, '\\u003C') !== FALSE, 'Payload chart masih memuat tag script literal.');

$dashboard_service = source($root, 'application/services/Dashboard_service.php');
$dashboard_model = source($root, 'application/models/Tugas_audit_model.php');
$dashboard_controller = source($root, 'application/controllers/Dashboard.php');
$dashboard_view = source($root, 'application/views/dashboard/super_admin.php');
check(strpos($dashboard_service, '$this->periode_model->get_active()') !== FALSE, 'Dashboard harus memakai periode audit aktif.');
check(strpos($dashboard_service, 'if ($active_periode)') !== FALSE, 'Dashboard harus menangani ketiadaan periode aktif.');
check(strpos($dashboard_service, 'count_by_status_for_period((int) $active_periode->id)') !== FALSE, 'Dashboard harus menghitung status untuk periode aktif.');
check(strpos($dashboard_model, "->where('periode_id', (int) \$periode_id)") !== FALSE, 'Agregat status harus dibatasi periode aktif.');
check(strpos($dashboard_model, "->where_in('status', array_keys(\$counts))") !== FALSE, 'Agregat status harus hanya memuat status tugas resmi.');
check(strpos($dashboard_controller, $json_flags) !== FALSE, 'JSON chart dashboard harus memakai JSON_HEX flags.');
check(strpos($dashboard_view, "site_url('lpmpi/laporan')") !== FALSE, 'Dashboard harus menautkan laporan detail.');
check(strpos($dashboard_view, 'Belum ada periode audit aktif') !== FALSE, 'Dashboard harus memiliki no-data state periode aktif.');
check(strpos($dashboard_view, '$active_task_count > 0') !== FALSE, 'Dashboard hanya boleh menampilkan chart saat periode aktif memiliki tugas.');
check(strpos($dashboard_view, 'Belum ada tugas audit pada periode aktif') !== FALSE, 'Dashboard harus memiliki no-data state untuk periode aktif tanpa tugas.');
check(strpos($dashboard_view, 'aria-describedby="status-summary-title status-summary-counts"') !== FALSE, 'Canvas chart harus terhubung ke ringkasan status tekstual.');
check(strpos($dashboard_view, 'id="status-summary-counts"') !== FALSE, 'Ringkasan status chart harus memiliki target deskripsi.');
check(strpos($dashboard_view, "type: 'doughnut'") !== FALSE, 'Dashboard harus memakai chart status operasional.');

$auth = source($root, 'application/controllers/Auth.php');
check(strpos($auth, "method(TRUE) !== 'POST'") !== FALSE, 'Logout harus POST-only.');
check(strpos(source($root, 'application/views/layouts/sidebar.php'), "form_open('auth/logout')") !== FALSE, 'Logout harus memakai form CSRF.');
check(strpos(source($root, 'application/controllers/Profil.php'), "set_flashdata('error', \$exception->getMessage())") === FALSE, 'Detail exception PDDikti tidak boleh tampil ke user.');

$service = source($root, 'application/services/Pertanyaan_service.php');
check(strpos($service, 'MAX_IMPORT_ROWS = 10000') !== FALSE, 'Import Excel harus memiliki batas baris.');
check(strpos($service, "['totalColumns'] > 10") !== FALSE, 'Import Excel harus menolak kolom setelah J.');
check(strpos($service, 'setReadFilter(new AmiPertanyaanReadFilter())') !== FALSE, 'Import Excel harus memakai bounded read filter.');

$schema = source($root, 'database_schema.sql');
check(strpos($schema, "INSERT INTO `users`") === FALSE, 'Schema produksi tidak boleh memuat akun demo.');
foreach (['urutan', 'nilai_standar', 'baseline', 'target_2025', 'target_2026', 'target_2027', 'target_2028', 'target_2029', 'target_2030', 'kategori'] as $column) {
    check(strpos($schema, '`' . $column . '`') !== FALSE, 'Kolom schema hilang: ' . $column);
}

$migration = source($root, 'migrations/010_reconcile_pertanyaan_columns.sql');
check(strpos($migration, 'INFORMATION_SCHEMA.COLUMNS') !== FALSE, 'Migration 010 harus idempotent.');
check(substr_count($migration, "CALL `ami_add_pertanyaan_column`") === 10, 'Migration 010 harus merekonsiliasi sepuluh kolom.');

$helper = source($root, 'application/helpers/app_helper.php');
check(strpos($helper, "['instrumen', 'penetapan', 'bukti_auditor', 'tmp', 'user_photos', 'spmi_source']") !== FALSE, 'Resolver harus membatasi kategori private.');
check(strpos($helper, 'basename($stored_name) !== $stored_name') !== FALSE, 'Resolver harus menolak path traversal.');
check(strpos($helper, "FCPATH . 'uploads'") !== FALSE, 'Resolver harus mempertahankan fallback file lama.');
check(strpos(source($root, 'application/views/lpmpi/instrumen/index.php'), "base_url('uploads/instrumen/") === FALSE, 'View instrumen tidak boleh mengekspos URL private.');
check(strpos(source($root, 'application/views/lpmpi/penetapan/index.php'), "base_url('uploads/penetapan/") === FALSE, 'View penetapan tidak boleh mengekspos URL private.');
$apache_deny = "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n";
check(source($root, 'uploads/bukti_auditor/.htaccess') === $apache_deny, 'Legacy bukti auditor harus ditolak oleh Apache 2.4 dan Apache lama.');

$auditee = source($root, 'application/controllers/Auditee.php');
$auditor = source($root, 'application/controllers/Auditor.php');
check(strpos($auditee, 'find_tugas_for_auditee') !== FALSE, 'Download auditee harus memeriksa ownership tugas.');
check(strpos($auditor, 'find_jawaban_for_auditor') !== FALSE, 'Download auditor harus memeriksa ownership jawaban.');
check(strpos(source($root, 'application/controllers/lpmpi/Instrumen.php'), 'extends Admin_Lpmpi_Controller') !== FALSE, 'Download instrumen admin harus role-protected.');
check(strpos(source($root, 'application/controllers/lpmpi/Penetapan.php'), 'extends Admin_Lpmpi_Controller') !== FALSE, 'Download penetapan admin harus role-protected.');

$config = source($root, 'application/config/config.php');
$readme = source($root, 'README.md');
check(strpos($config, "getenv('GOOGLE_DRIVE_EVIDENCE_FOLDER_ID')") !== FALSE, 'Config harus membaca folder Google Drive dari environment.');
check(strpos($config, "getenv('GOOGLE_DRIVE_AUTH_MODE')") !== FALSE, 'Config harus membaca mode auth Google Drive dari environment.');
check(strpos($config, "getenv('GOOGLE_DRIVE_SERVICE_ACCOUNT_JSON_PATH')") !== FALSE, 'Config harus membaca path service account Google Drive dari environment.');
check(strpos($config, "getenv('GOOGLE_DRIVE_OAUTH_CLIENT_SECRET_JSON_PATH')") !== FALSE, 'Config harus membaca path OAuth client secret Google Drive dari environment.');
check(strpos($config, "getenv('GOOGLE_DRIVE_OAUTH_REFRESH_TOKEN_JSON_PATH')") !== FALSE, 'Config harus membaca path OAuth refresh token Google Drive dari environment.');
check(strpos($config, "getenv('SPMI_EVIDENCE_STORAGE_BACKEND')") !== FALSE, 'Config harus membaca backend bukti SPMI dari environment.');
check(strpos($config, "in_array($" . "spmi_evidence_storage_backend, ['local', 'google_drive'], TRUE)") !== FALSE && strpos($config, ": 'local';") !== FALSE, 'Config harus allowlist backend bukti SPMI dan default aman ke lokal.');
check(strpos($config, 'realpath($config[\'google_drive_service_account_json_path\'])') !== FALSE, 'Config produksi harus resolve path kredensial Google Drive.');
check(strpos($config, 'strpos($google_drive_config_path . DIRECTORY_SEPARATOR, $google_drive_web_root . DIRECTORY_SEPARATOR) === 0') !== FALSE, 'Config produksi harus menolak kredensial Google Drive di bawah FCPATH.');
check(strpos($config, '$drive_config_valid') !== FALSE, 'Config produksi harus memasukkan validasi Drive ke fail-closed gate.');
check(strpos($config, '$production_drive_service_account_only') !== FALSE && strpos($config, "['spmi_evidence_storage_backend'] === 'google_drive'") !== FALSE && strpos($config, '$drive_config_valid = $production_drive_service_account_only;') !== FALSE, 'Config produksi harus gagal tertutup saat backend Drive dipilih tanpa config Drive valid.');
check(strpos($config, "ENVIRONMENT !== 'production'") !== FALSE && strpos($config, "google_drive_oauth_client_secret_json_path'] === ''") !== FALSE && strpos($config, "google_drive_oauth_refresh_token_json_path'] === ''") !== FALSE, 'Config produksi harus menolak OAuth dan mixed credential Google Drive.');
check(strpos($readme, 'SPMI_EVIDENCE_STORAGE_BACKEND=local') !== FALSE, 'README deployment harus mendokumentasikan backend bukti SPMI default.');
check(strpos($readme, 'GOOGLE_DRIVE_EVIDENCE_FOLDER_ID=') !== FALSE, 'README deployment harus mendokumentasikan folder Google Drive tanpa nilai nyata.');
check(strpos($readme, 'GOOGLE_DRIVE_SERVICE_ACCOUNT_JSON_PATH=') !== FALSE, 'README deployment harus mendokumentasikan path kredensial Google Drive tanpa nilai nyata.');
check(strpos($readme, 'backend lokal secara default') !== FALSE, 'README harus menjelaskan backend lokal tetap default.');
check(strpos($readme, '033_add_spmi_drive_evidence_metadata.sql') !== FALSE, 'README upgrade harus mencantumkan migration 033.');
check(strpos($readme, '### Handover Google Shared Drive Bukti SPMI') !== FALSE, 'README harus memiliki runbook handover Shared Drive SPMI.');
check(strpos($readme, 'Berlaku hanya untuk bukti SPMI auditee dan auditor baru') !== FALSE, 'README handover harus membatasi scope Drive ke bukti SPMI baru.');
check(strpos($readme, 'tanpa URL publik, ID Drive pada UI, atau permission publik') !== FALSE, 'README handover harus melarang akses Drive publik.');
check(strpos($readme, 'minimal dua administrator pemulihan manusia') !== FALSE, 'README handover harus meminta minimal dua admin pemulihan.');
check(strpos($readme, 'backup dulu lalu jalankan migration `033_add_spmi_drive_evidence_metadata.sql` satu kali') !== FALSE, 'README handover harus meminta backup dan migration 033 sekali.');
check(strpos($readme, '`spmi_drive_trash_outbox` masih manual') !== FALSE, 'README handover harus menjelaskan retry trash outbox manual.');
check(strpos($readme, 'root `compose.yaml` tidak boleh memuat secret Drive production') !== FALSE, 'README handover harus melarang secret Drive produksi di compose root.');

fwrite(STDOUT, "Hardening regression checks passed.\n");
