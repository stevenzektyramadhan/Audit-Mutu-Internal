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
check(strpos($helper, "['instrumen', 'penetapan', 'bukti_auditor', 'tmp', 'user_photos']") !== FALSE, 'Resolver harus membatasi kategori private.');
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

fwrite(STDOUT, "Hardening regression checks passed.\n");
