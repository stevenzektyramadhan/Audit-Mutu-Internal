<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['spmi_ppepp_stages'] = [
    'penetapan' => 'Penetapan',
    'pelaksanaan' => 'Pelaksanaan',
    'pengendalian' => 'Pengendalian',
    'peningkatan' => 'Peningkatan',
];

$config['spmi_ppepp_categories'] = [
    'penetapan' => [
        'kebijakan_spmi' => 'Kebijakan SPMI',
        'manual_spmi' => 'Manual SPMI',
        'formulir_spmi' => 'Formulir SPMI',
        'standar_spmi' => 'Standar SPMI',
        'mekanisme_pendokumentasian' => 'Mekanisme Pendokumentasian',
        'lainnya' => 'Lainnya',
    ],
    'pelaksanaan' => [
        'laporan_pelaksanaan' => 'Laporan Pelaksanaan Standar',
        'bukti_pelaksanaan' => 'Bukti Pelaksanaan',
        'lainnya' => 'Lainnya',
    ],
    'pengendalian' => [
        'analisis_penyebab' => 'Analisis Penyebab Standar Tidak Tercapai',
        'laporan_koreksi' => 'Laporan Tindakan Koreksi',
        'lainnya' => 'Lainnya',
    ],
    'peningkatan' => [
        'penetapan_standar_baru' => 'Penetapan/Revisi Standar Baru',
        'rekomendasi_peningkatan' => 'Rekomendasi Peningkatan',
        'lainnya' => 'Lainnya',
    ],
];

$config['spmi_ppepp_penetapan_core_categories'] = [
    'kebijakan_spmi',
    'manual_spmi',
    'formulir_spmi',
    'standar_spmi',
    'mekanisme_pendokumentasian',
];
