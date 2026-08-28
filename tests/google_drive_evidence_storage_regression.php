<?php

$root = dirname(__DIR__);
$library = file_get_contents($root . DIRECTORY_SEPARATOR . 'application/libraries/Google_drive_evidence_storage.php');
$bootstrap = file_get_contents($root . DIRECTORY_SEPARATOR . 'scripts/google_drive_oauth_bootstrap.php');
if ($library === FALSE || $bootstrap === FALSE) {
    throw new RuntimeException('Tidak dapat membaca source Google Drive evidence storage.');
}

function gd_check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

foreach (['GOOGLE_DRIVE_EVIDENCE_FOLDER_ID', 'GOOGLE_DRIVE_AUTH_MODE', 'GOOGLE_DRIVE_SERVICE_ACCOUNT_JSON_PATH', 'GOOGLE_DRIVE_OAUTH_CLIENT_SECRET_JSON_PATH', 'GOOGLE_DRIVE_OAUTH_REFRESH_TOKEN_JSON_PATH'] as $env_key) {
    gd_check(strpos($library, $env_key) !== FALSE, 'Library harus membaca env key: ' . $env_key);
}

gd_check(strpos($library, "APPPATH . '../vendor/autoload.php'") !== FALSE, 'Autoload Composer harus hanya dipanggil dari library.');
gd_check(substr_count($library, "'supportsAllDrives' => TRUE") === 3, 'Upload, download, dan trash harus mendukung Shared Drive.');
gd_check(strpos($library, 'Google_Service_Drive::DRIVE') !== FALSE, 'Scope harus dapat mengakses folder evidence yang dibagikan ke service account.');
gd_check(strpos($library, 'Google_Service_Drive::DRIVE_FILE') === FALSE, 'Scope drive.file tidak dapat dipakai untuk folder evidence yang sudah ada.');
gd_check(strpos($library, "['service_account', 'oauth_refresh_token']") !== FALSE, 'Auth mode harus eksplisit service_account atau oauth_refresh_token.');
gd_check(strpos($library, "fetchAccessTokenWithRefreshToken(") !== FALSE, 'OAuth harus menukar refresh token menjadi access token in-memory.');
gd_check(strpos($library, "setAccessToken(") !== FALSE, 'OAuth access token harus dipasang hanya pada client in-memory.');
gd_check(strpos($library, "ENVIRONMENT === 'production'") !== FALSE, 'OAuth refresh-token mode harus ditolak di production.');
gd_check(substr_count($library, 'configured_credentials_path(') >= 3, 'File kredensial OAuth dan service account harus tervalidasi sebagai path eksternal readable.');
gd_check(strpos($library, "json_decode(\$token_json, TRUE)") !== FALSE, 'Token JSON harus didecode sebagai array.');
gd_check(strpos($library, "isset(\$token_data['refresh_token'])") !== FALSE, 'Token JSON harus berisi refresh_token.');
gd_check(strpos($library, "isset(\$access_token['error'])") !== FALSE, 'Error response token refresh harus ditolak.');
gd_check(strpos($library, "isset(\$access_token['access_token'])") !== FALSE, 'Token refresh tanpa access_token harus ditolak.');
gd_check(strpos($library, "'parents' => [\$this->folder_id]") !== FALSE, 'Upload harus masuk folder terkonfigurasi.');
gd_check(strpos($library, "'alt' => 'media'") !== FALSE, 'Download harus memakai media stream, bukan public URL.');
gd_check(strpos($library, "new Google_Service_Drive_DriveFile(['trashed' => TRUE])") !== FALSE, 'Delete harus berupa trash, bukan hapus permanen.');
gd_check(strpos($library, 'file_get_contents($source_path)') !== FALSE, 'Upload hanya membaca path file tervalidasi.');
gd_check(strpos($library, 'basename($stored_name) !== $stored_name') !== FALSE, 'Filename harus opaque dan menolak path traversal.');
gd_check(strpos($library, 'Operasi penyimpanan bukti gagal.') !== FALSE, 'Kegagalan harus generik.');
gd_check(strpos($bootstrap, "PHP_SAPI !== 'cli'") !== FALSE, 'Bootstrap harus CLI-only.');
gd_check(strpos($bootstrap, "ENVIRONMENT === 'production'") !== FALSE, 'Bootstrap harus menolak production.');
gd_check(strpos($bootstrap, "127.0.0.1") !== FALSE, 'Bootstrap harus bind loopback 127.0.0.1.');
gd_check(strpos($bootstrap, "isset(\$client_data['installed'])") !== FALSE, 'Bootstrap harus menerima Google desktop OAuth client secret.');
gd_check(strpos($bootstrap, "OAuth client JSON must be outside the web root") !== FALSE, 'Bootstrap harus menolak client secret di web root.');
gd_check(strpos($bootstrap, "setAccessType('offline')") !== FALSE, 'Bootstrap harus meminta offline access.');
gd_check(strpos($bootstrap, "setPrompt('consent')") !== FALSE, 'Bootstrap harus memaksa consent agar refresh token diterbitkan.');
gd_check(strpos($bootstrap, "setState(") !== FALSE, 'Bootstrap harus memakai state CSRF.');
gd_check(strpos($bootstrap, 'The localhost callback listener will wait up to 15 minutes.') !== FALSE, 'Bootstrap harus memberi tahu batas waktu callback 15 menit.');
gd_check(strpos($bootstrap, 'stream_socket_accept($server, 900)') !== FALSE, 'Bootstrap harus menunggu callback hingga 15 menit.');
gd_check(strpos($bootstrap, 'Timed out waiting up to 15 minutes for the OAuth callback.') !== FALSE, 'Timeout bootstrap harus menjelaskan batas waktu callback.');
gd_check(strpos($bootstrap, "fetchAccessTokenWithAuthCode(") !== FALSE, 'Bootstrap harus menukar authorization code tanpa menampilkannya.');
gd_check(strpos($bootstrap, "json_encode(['refresh_token'") !== FALSE, 'Bootstrap hanya boleh menulis refresh_token.');
gd_check(strpos($bootstrap, 'file_exists($token_path)') !== FALSE, 'Bootstrap harus menolak refresh-token path yang sudah ada sebelum membuka flow OAuth.');
gd_check(strpos($bootstrap, 'OAuth client JSON must be outside the web root.') !== FALSE, 'Client secret hanya perlu validasi lokasi dan baca, bukan writable.');
gd_check(strpos($bootstrap, 'Refresh token path parent must be writable outside the web root.') !== FALSE, 'Refresh token destination parent harus tetap writable.');
gd_check(strpos($bootstrap, "chmod(\$token_path, 0600)") !== FALSE, 'Bootstrap harus membuat file token 0600.');
gd_check(strpos($bootstrap, "rename(\$temporary_path, \$token_path)") !== FALSE, 'Bootstrap harus menulis token secara atomik.');
gd_check(strpos($library, "if (\$mode === 'service_account' && (\$oauth_client_path !== '' || \$oauth_refresh_token_path !== ''))") !== FALSE, 'Service account harus menolak kedua jalur OAuth bila ada.');
gd_check(strpos($library, "if (\$mode === 'oauth_refresh_token' && \$service_account_path !== '')") !== FALSE, 'OAuth mode harus menolak service-account path yang dikonfigurasi.');

foreach (['createPermission', 'permissions->create', 'webViewLink', 'webContentLink', 'setSubject', 'JWT', 'log_message', 'error_log'] as $forbidden) {
    gd_check(stripos($library, $forbidden) === FALSE, 'Library tidak boleh memuat pola terlarang: ' . $forbidden);
}

foreach (['file_put_contents', 'error_log', 'log_message'] as $forbidden) {
    gd_check(stripos($library, $forbidden) === FALSE, 'Library tidak boleh menulis atau log secret/token: ' . $forbidden);
}

fwrite(STDOUT, "Google Drive evidence storage regression checks passed.\n");
