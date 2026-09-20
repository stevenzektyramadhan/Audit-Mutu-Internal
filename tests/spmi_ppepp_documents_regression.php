<?php

$root = dirname(__DIR__);

function ppepp_source($path)
{
    $contents = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path);
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }
    return $contents;
}

function ppepp_check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$migration = ppepp_source('migrations/038_create_spmi_ppepp_documents.sql');
$schema = ppepp_source('database_schema.sql');
$config = ppepp_source('application/config/spmi_ppepp.php');
$helper = ppepp_source('application/helpers/app_helper.php');

$table_contract = [
    'CREATE TABLE IF NOT EXISTS `spmi_ppepp_documents`',
    "`stage` ENUM('penetapan','pelaksanaan','pengendalian','peningkatan') NOT NULL",
    '`category` VARCHAR(64) NOT NULL',
    '`period_year` SMALLINT UNSIGNED NOT NULL',
    '`title` VARCHAR(200) NOT NULL',
    '`description` TEXT NULL',
    '`document_date` DATE NULL',
    '`stored_name` VARCHAR(255) NULL',
    '`original_name` VARCHAR(255) NULL',
    '`mime_type` VARCHAR(127) NULL',
    '`file_size` INT UNSIGNED NULL',
    '`external_url` VARCHAR(500) NULL',
    '`uploaded_by` INT NOT NULL',
    '`updated_by` INT NULL',
    '`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
    '`updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP',
    'KEY `idx_spmi_ppepp_documents_stage_year` (`stage`, `period_year`)',
    'KEY `idx_spmi_ppepp_documents_category` (`category`)',
    'UNIQUE KEY `uq_spmi_ppepp_documents_stored_name` (`stored_name`)',
    'CONSTRAINT `fk_spmi_ppepp_documents_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT',
    'CONSTRAINT `fk_spmi_ppepp_documents_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT',
    'ENGINE=InnoDB DEFAULT CHARSET=utf8',
];
foreach ($table_contract as $literal) {
    ppepp_check(strpos($migration, $literal) !== FALSE, 'Migration PPEPP contract missing: ' . $literal);
    ppepp_check(strpos($schema, $literal) !== FALSE, 'Schema PPEPP parity missing: ' . $literal);
}
ppepp_check(substr_count($migration, 'CREATE TABLE IF NOT EXISTS `spmi_ppepp_documents`') === 1, 'Migration must create exactly one PPEPP metadata table.');
ppepp_check(!preg_match('/(^|;|\R)\s*(INSERT|UPDATE|DELETE)\s+/i', $migration), 'Migration PPEPP must not seed or mutate data.');
ppepp_check(strpos($migration, "'evaluasi'") === FALSE, 'Migration PPEPP must exclude Evaluasi stage.');
ppepp_check(strpos($migration, '`penetapan`') === FALSE, 'Migration PPEPP must not reuse legacy penetapan table.');
ppepp_check(strpos($schema, 'current parity migration 001-038') !== FALSE, 'Schema parity comment must indicate 001-038.');

$ordered_stages = "'penetapan' => 'Penetapan',\n    'pelaksanaan' => 'Pelaksanaan',\n    'pengendalian' => 'Pengendalian',\n    'peningkatan' => 'Peningkatan'";
ppepp_check(strpos($config, $ordered_stages) !== FALSE, 'PPEPP stages must stay ordered and limited to four non-Evaluasi stages.');
foreach ([
    "'kebijakan_spmi' => 'Kebijakan SPMI'",
    "'manual_spmi' => 'Manual SPMI'",
    "'formulir_spmi' => 'Formulir SPMI'",
    "'standar_spmi' => 'Standar SPMI'",
    "'mekanisme_pendokumentasian' => 'Mekanisme Pendokumentasian'",
    "'laporan_pelaksanaan' => 'Laporan Pelaksanaan Standar'",
    "'bukti_pelaksanaan' => 'Bukti Pelaksanaan'",
    "'analisis_penyebab' => 'Analisis Penyebab Standar Tidak Tercapai'",
    "'laporan_koreksi' => 'Laporan Tindakan Koreksi'",
    "'penetapan_standar_baru' => 'Penetapan/Revisi Standar Baru'",
    "'rekomendasi_peningkatan' => 'Rekomendasi Peningkatan'",
] as $literal) {
    ppepp_check(strpos($config, $literal) !== FALSE, 'PPEPP config category missing: ' . $literal);
}
ppepp_check(substr_count($config, "'lainnya' => 'Lainnya'") === 4, 'Each PPEPP stage must have exactly one lainnya category.');
ppepp_check(strpos($config, "'evaluasi'") === FALSE, 'PPEPP config must not add Evaluasi upload stage.');
ppepp_check(strpos($config, "'spmi_ppepp_penetapan_core_categories'") !== FALSE, 'PPEPP config must expose Penetapan checklist categories.');
foreach (['kebijakan_spmi', 'manual_spmi', 'formulir_spmi', 'standar_spmi', 'mekanisme_pendokumentasian'] as $category) {
    ppepp_check(substr_count($config, "'" . $category . "'") >= 2, 'Penetapan core checklist missing: ' . $category);
}

ppepp_check(strpos($helper, "'ppepp_documents'") !== FALSE, 'Private storage helper must accept ppepp_documents category.');
ppepp_check(strpos($helper, "['user_photos', 'spmi_source', 'ppepp_documents']") !== FALSE, 'ppepp_documents must be private-only with no public uploads fallback.');
ppepp_check(strpos($helper, "FCPATH . 'uploads'") !== FALSE, 'Legacy public uploads fallback must remain for existing categories.');

if (!defined('BASEPATH')) {
    define('BASEPATH', $root . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
}
if (!defined('FCPATH')) {
    define('FCPATH', sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ami-ppepp-public-root' . DIRECTORY_SEPARATOR);
}
putenv('APP_PRIVATE_STORAGE_PATH=' . sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ami-ppepp-private-root');
require_once $root . DIRECTORY_SEPARATOR . 'application/helpers/app_helper.php';

$legacy_dir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'ppepp_documents' . DIRECTORY_SEPARATOR;
if (!is_dir($legacy_dir) && !mkdir($legacy_dir, 0777, TRUE)) {
    throw new RuntimeException('Tidak dapat menyiapkan direktori uji fallback PPEPP.');
}
$legacy_file = $legacy_dir . 'unsafe-public.pdf';
file_put_contents($legacy_file, 'public fallback must stay ignored');
ppepp_check(private_storage_path('ppepp_documents', 'unsafe-public.pdf') === NULL, 'ppepp_documents must ignore public uploads fallback even when a file exists there.');
@unlink($legacy_file);
@rmdir($legacy_dir);
@rmdir(dirname($legacy_dir));
@rmdir(FCPATH);

fwrite(STDOUT, "SPMI PPEPP documents regression checks passed.\n");
