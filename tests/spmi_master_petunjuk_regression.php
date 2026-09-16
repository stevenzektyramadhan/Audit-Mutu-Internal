<?php
$root = dirname(__DIR__);
function spmi_master_petunjuk_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function spmi_master_petunjuk_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }

$controller = spmi_master_petunjuk_source('application/controllers/lpmpi/Spmi_master.php');

foreach (['createSheet', "setTitle('Petunjuk')", 'Petunjuk Pengisian Template Master SPMI', 'Kriteria', 'Profil UPPS', 'Standar', 'Indikator', 'Indicator Title', 'Evidence Requirement', 'Nilai Tertimbang', 'tidak diimport', 'Contoh', 'jangan disalin ke sheet SPMI Master apa adanya', 'Kolom', 'Wajib?', 'Isi dengan', 'Aturan', 'setActiveSheetIndex(0)', 'Evidence Policy', 'none, file, url, either, atau both', 'Tidak case-sensitive saat diimport; disimpan lowercase', 'A25:N25'] as $literal) spmi_master_petunjuk_check(stripos($controller, $literal) !== FALSE, 'Petunjuk worksheet contract missing: ' . $literal);
foreach (['Standard Code', 'Standard Order', 'Standard Title', 'Standard Description', 'Indicator Code', 'Indicator Type', 'Indicator Title', 'Scope Unit Code', 'Responsible Unit Code', 'Responsible PIC', 'Evidence Requirement', 'Target Year', 'Target Value', 'Evidence Policy'] as $header) spmi_master_petunjuk_check(strpos($controller, $header) !== FALSE, 'Petunjuk column guidance missing: ' . $header);

fwrite(STDOUT, "SPMI master Petunjuk regression checks passed.\n");
