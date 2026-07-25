#!/usr/bin/env php
<?php

$root = dirname(__DIR__);
$checks = 0;
$failures = [];

function file_check($condition, $message)
{
    global $checks, $failures;
    $checks++;
    if (!$condition) {
        $failures[] = $message;
    }
}

function file_source($root, $path)
{
    $contents = file_get_contents(
        $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path)
    );
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }
    return $contents;
}

$library = file_source($root, 'application/libraries/File_security.php');
$model = file_source($root, 'application/models/File_asset_model.php');
$migration = file_source($root, 'migrations/013_file_security_foundation.sql');
$schema = file_source($root, 'database_schema.sql');
$helper = file_source($root, 'application/helpers/app_helper.php');

foreach (['file_assets', 'file_security_events'] as $table) {
    file_check(strpos($migration, 'CREATE TABLE IF NOT EXISTS `' . $table . '`') !== FALSE, 'Migration tidak membuat tabel ' . $table . '.');
    file_check(strpos($schema, 'CREATE TABLE IF NOT EXISTS `' . $table . '`') !== FALSE, 'Schema utama tidak memuat tabel ' . $table . '.');
}
foreach (['original_name', 'stored_name', 'mime_type', 'size_bytes', 'sha256', 'retention_until', 'deleted_at', 'purged_at'] as $column) {
    file_check(strpos($migration, '`' . $column . '`') !== FALSE, 'Metadata file belum memiliki kolom ' . $column . '.');
}

file_check(strpos($library, 'bin2hex(random_bytes(24))') !== FALSE, 'Nama storage belum menggunakan random bytes.');
file_check(strpos($library, "hash_file('sha256'") !== FALSE, 'Checksum SHA-256 belum dihitung.');
file_check(strpos($library, 'hash_equals((string) $asset->sha256') !== FALSE, 'Checksum belum diverifikasi saat file dibaca.');
file_check(strpos($library, 'finfo_file(') !== FALSE, 'MIME belum dideteksi dari isi file.');
file_check(strpos($library, 'getimagesize(') !== FALSE, 'Isi gambar belum diverifikasi.');
file_check(strpos($library, 'new ZipArchive()') !== FALSE, 'Container OpenXML belum diperiksa.');
file_check(strpos($library, "'word/document.xml'") !== FALSE, 'DOCX belum dibedakan berdasarkan isi container.');
file_check(strpos($library, "'xl/workbook.xml'") !== FALSE, 'XLSX belum dibedakan berdasarkan isi container.');
file_check(strpos($library, "'vbaproject.bin'") !== FALSE, 'Macro OpenXML belum ditolak.');
file_check(strpos($library, "'/embeddings/'") !== FALSE, 'Embedded object OpenXML belum ditolak.');
file_check(strpos($library, 'TargetMode="External"') !== FALSE, 'External relationship OpenXML belum ditolak.');
file_check(strpos($library, 'pdf_has_active_content') !== FALSE, 'Active-content PDF belum diperiksa.');
file_check(strpos($library, 'image_has_clean_ending') !== FALSE, 'Trailing payload gambar belum diperiksa.');

foreach (['php', 'phar', 'phtml', 'exe', 'js', 'html', 'svg'] as $blocked) {
    file_check(strpos($library, "'" . $blocked . "'") !== FALSE, 'Ekstensi berbahaya belum tercantum: ' . $blocked);
}
foreach (['pdf', 'docx', 'xlsx', 'png', 'jpg', 'jpeg'] as $allowed) {
    file_check(strpos($library, "'" . $allowed . "'") !== FALSE, 'Allowlist awal belum memuat: ' . $allowed);
}
file_check(strpos($library, "'doc',") === FALSE, 'Format DOC lama masih diizinkan untuk upload baru.');
file_check(strpos($library, "'xls',") === FALSE, 'Format XLS lama masih diizinkan untuk upload baru.');
file_check(strpos($library, "'gif',") === FALSE, 'GIF masih diizinkan untuk upload baru.');

file_check(strpos($library, "'Content-Disposition: attachment;") !== FALSE, 'Download belum dipaksa sebagai attachment.');
file_check(strpos($library, "header('X-Content-Type-Options: nosniff')") !== FALSE, 'Header nosniff belum diterapkan.');
file_check(strpos($library, "Content-Security-Policy: sandbox") !== FALSE, 'Download belum memakai sandbox defense-in-depth.');
file_check(
    strpos($library, '$this->CI->db->update') === FALSE
        && strpos($library, 'File_asset_model->update') === FALSE
        && strpos($library, 'File_asset_model->set_status') === FALSE,
    'Library seharusnya menyerahkan perubahan status ke method domain pada model.'
);
file_check(strpos($model, "'status' => 'deleted'") !== FALSE, 'Soft-delete file belum diimplementasikan.');
file_check(strpos($model, "'retention_until' =>") !== FALSE, 'Retensi file belum dicatat.');
file_check(strpos($library, 'function purge_expired') !== FALSE, 'Purge setelah retensi belum tersedia.');
file_check(strpos($library, 'function discard_temporary') !== FALSE, 'Cleanup khusus file sementara belum tersedia.');
file_check(strpos($library, "event('upload_succeeded'") !== FALSE, 'Upload sukses belum dicatat.');
file_check(strpos($library, "event('upload_blocked'") !== FALSE, 'Upload ditolak belum dicatat.');
file_check(strpos($library, "'download_succeeded'") !== FALSE, 'Download sensitif belum dicatat.');
file_check(strpos($library, "event('download_blocked'") !== FALSE, 'Download ditolak belum dicatat.');

file_check(strpos($helper, 'basename($stored_name) !== $stored_name') !== FALSE, 'Resolver belum menolak path traversal.');
file_check(strpos($helper, "ENVIRONMENT === 'production'") !== FALSE, 'Production belum menolak fallback legacy di document root.');

$upload_controllers = [
    'application/controllers/Account.php',
    'application/controllers/Auditor.php',
    'application/controllers/Pertanyaan.php',
    'application/controllers/Profil.php',
    'application/controllers/lpmpi/Instrumen.php',
    'application/controllers/lpmpi/Penetapan.php',
];
foreach ($upload_controllers as $path) {
    $source = file_source($root, $path);
    file_check(strpos($source, 'file_security') !== FALSE, $path . ' belum memakai layanan file terpusat.');
    file_check(strpos($source, 'do_upload(') === FALSE, $path . ' masih memakai upload generik langsung.');
    file_check(strpos($source, 'move_uploaded_file(') === FALSE, $path . ' masih memindahkan upload tanpa layanan terpusat.');
    file_check(strpos($source, 'unlink(') === FALSE, $path . ' masih menghapus file langsung pada flow normal.');
}

foreach ([
    'application/controllers/Auditee.php',
    'application/controllers/auditee/Tugas.php',
    'application/controllers/Auditor.php',
    'application/controllers/lpmpi/Instrumen.php',
    'application/controllers/lpmpi/Penetapan.php',
] as $path) {
    $source = file_source($root, $path);
    file_check(strpos($source, 'file_security->download(') !== FALSE, $path . ' belum memakai download aman terpusat.');
    file_check(strpos($source, 'force_download(') === FALSE, $path . ' masih memakai nama storage pada Content-Disposition.');
}

$instrument_view = file_source($root, 'application/views/lpmpi/instrumen/index.php');
$evidence_view = file_source($root, 'application/views/auditor/form_penilaian.php');
$import_view = file_source($root, 'application/views/pertanyaan/index.php');
file_check(strpos($instrument_view, '.doc,') === FALSE, 'UI instrumen masih menawarkan DOC.');
file_check(strpos($evidence_view, '.doc,') === FALSE, 'UI bukti auditor masih menawarkan DOC.');
file_check(strpos($import_view, '.xls"') === FALSE && strpos($import_view, '.xls,') === FALSE, 'UI import masih menawarkan XLS.');

$maintenance = file_source($root, 'application/controllers/Maintenance.php');
file_check(strpos($maintenance, 'is_cli_request()') !== FALSE, 'Purge retention tidak dibatasi untuk CLI.');
file_check(strpos($maintenance, 'purge_expired(') !== FALSE, 'CLI maintenance belum menjalankan purge retention.');

$migration_script = file_source($root, 'scripts/migrate_private_storage.php');
file_check(strpos($migration_script, '--apply') !== FALSE, 'Migrasi legacy tidak menggunakan dry-run sebagai default.');
file_check(strpos($migration_script, "hash_file('sha256'") !== FALSE, 'Migrasi legacy tidak memverifikasi checksum.');
file_check(strpos($migration_script, 'strpos($normalize($destination_real), $normalize($web_root))') !== FALSE, 'Migrasi legacy tidak menolak tujuan di document root.');

$local_runner = file_source($root, 'scripts/database/apply_local_m1_06.php');
file_check(strpos($local_runner, "=== 'production'") !== FALSE, 'Runner migration lokal tidak menolak mode production.');
file_check(strpos($local_runner, "['localhost', '127.0.0.1', '::1']") !== FALSE, 'Runner migration lokal tidak membatasi host database.');
file_check(strpos($local_runner, '013_file_security_foundation.sql') !== FALSE, 'Runner lokal tidak dikunci ke migration 013.');

if (!empty($failures)) {
    foreach ($failures as $failure) {
        fwrite(STDERR, '[FAIL] ' . $failure . PHP_EOL);
    }
    exit(1);
}

fwrite(STDOUT, '[PASS] file security regression (' . $checks . ' checks)' . PHP_EOL);
exit(0);
