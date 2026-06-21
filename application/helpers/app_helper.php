<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('status_audit_meta')) {
    function status_audit_meta($status)
    {
        $statuses = [
            STATUS_BELUM_DIISI => ['label' => 'Belum diisi', 'icon' => 'fa-exclamation-circle', 'tone' => 'status-belum_diisi'],
            STATUS_DIISI => ['label' => 'Sudah diisi', 'icon' => 'fa-clock', 'tone' => 'status-diisi'],
            STATUS_DINILAI => ['label' => 'Sudah dinilai', 'icon' => 'fa-check-circle', 'tone' => 'status-dinilai'],
        ];

        return isset($statuses[$status])
            ? $statuses[$status]
            : ['label' => (string) $status, 'icon' => 'fa-circle', 'tone' => 'status-diisi'];
    }
}

if (!function_exists('skor_audit_options')) {
    function skor_audit_options()
    {
        return [
            1 => 'Tidak sesuai',
            2 => 'Kurang sesuai',
            3 => 'Sesuai',
            4 => 'Sangat sesuai',
        ];
    }
}

if (!function_exists('format_tanggal_indo')) {
    function format_tanggal_indo($datetime)
    {
        $timestamp = strtotime((string) $datetime);
        if ($timestamp === FALSE) {
            return '-';
        }

        $bulan = [
            1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
        ];

        return date('d', $timestamp) . ' ' . $bulan[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    }
}
