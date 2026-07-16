<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pddikti_service
{
    private $base_urls = [
        'https://pddikti.rone.dev/api',
        'https://pddikti.fastapicloud.dev/api',
    ];
    private $active_base_url = 'https://pddikti.rone.dev/api';

    public function fetch_all($nama_pt, $id_pt = NULL)
    {
        $nama_pt = trim((string) $nama_pt);
        $id_pt = trim((string) $id_pt);
        $search_record = [];
        $warnings = [];

        if ($nama_pt === '') {
            throw new Exception('Nama PT untuk sinkronisasi PDDikti wajib diisi.');
        }

        if ($id_pt === '') {
            $search_record = $this->cari_pt($nama_pt);
            $id_pt = $this->id_from_record($search_record);
        }

        try {
            $detail = $this->get_detail_pt($id_pt);
        } catch (Exception $exception) {
            if ($id_pt !== '') {
                $search_record = $this->cari_pt($nama_pt);
                $id_pt = $this->id_from_record($search_record);
                try {
                    $detail = $this->get_detail_pt($id_pt);
                } catch (Exception $detail_exception) {
                    $detail = [];
                    $warnings[] = 'Detail profil belum diperbarui karena PDDikti belum tersedia: ' . $detail_exception->getMessage();
                }
            } else {
                throw $exception;
            }
        }

        $prodi_available = TRUE;
        try {
            $prodi_payload = $this->get_prodi_pt($id_pt);
        } catch (Exception $exception) {
            $prodi_available = FALSE;
            $prodi_payload = [];
            $warnings[] = 'Data program studi belum diperbarui karena PDDikti belum tersedia: ' . $exception->getMessage();
        }

        $rasio = [];

        try {
            $rasio = $this->get_rasio_pt($id_pt);
        } catch (Exception $exception) {
            $rasio = [];
        }

        $profil = $this->map_detail_pt($detail, $id_pt, $nama_pt, $rasio, $search_record);
        if ($prodi_available && empty($profil['jumlah_dosen'])) {
            $profil['jumlah_dosen'] = $this->extract_total_dosen($prodi_payload);
        }
        $profil['logo_url'] = $this->get_logo_pt($id_pt);
        $profil['last_sync_at'] = date('Y-m-d H:i:s');

        $prodi = $prodi_available ? $this->map_prodi_pt($prodi_payload, $rasio) : NULL;
        $mahasiswa_stats = [];

        try {
            $mahasiswa_stats = $this->map_mahasiswa_stats($this->get_mahasiswa_stats($id_pt));
        } catch (Exception $exception) {
            $mahasiswa_stats = [];
        }

        if ($prodi_available && empty($mahasiswa_stats)) {
            $mahasiswa_stats = $this->derive_mahasiswa_stats_from_prodi($prodi_payload);
        }

        if (!$prodi_available && empty($mahasiswa_stats)) {
            $mahasiswa_stats = NULL;
        }

        return [
            'profil' => $profil,
            'prodi' => $prodi,
            'mahasiswa_stats' => $mahasiswa_stats,
            'warnings' => $warnings,
        ];
    }

    public function cari_id_pt($nama_pt)
    {
        return $this->id_from_record($this->cari_pt($nama_pt));
    }

    public function cari_pt($nama_pt)
    {
        $payload = $this->fetch('/search/pt/' . rawurlencode((string) $nama_pt) . '/');
        $records = $this->as_records($this->unwrap_payload($payload));

        if (empty($records)) {
            throw new Exception('Data perguruan tinggi tidak ditemukan di PDDikti.');
        }

        $fallback = NULL;
        foreach ($records as $record) {
            $id = $this->id_from_record($record, FALSE);
            if ($id === '') {
                continue;
            }

            if ($fallback === NULL) {
                $fallback = $record;
            }

            $candidate_name = strtolower($this->pick($record, ['nama_pt', 'nama', 'text', 'label']));
            if ($candidate_name !== '' && $candidate_name === strtolower(trim((string) $nama_pt))) {
                return $record;
            }
        }

        if ($fallback !== NULL) {
            return $fallback;
        }

        throw new Exception('ID perguruan tinggi tidak ditemukan pada response PDDikti.');
    }

    public function get_detail_pt($id_pt)
    {
        $payload = $this->fetch('/pt/detail/' . rawurlencode((string) $id_pt) . '/');
        $records = $this->as_records($this->unwrap_payload($payload));
        return !empty($records) ? $records[0] : [];
    }

    public function get_logo_pt($id_pt)
    {
        return $this->active_base_url . '/pt/logo/' . rawurlencode((string) $id_pt) . '/';
    }

    public function get_prodi_pt($id_pt, $id_thsmt = NULL)
    {
        $id_thsmt = $id_thsmt ?: $this->current_semester_code();
        return $this->fetch('/pt/prodi/' . rawurlencode((string) $id_pt) . '/' . rawurlencode((string) $id_thsmt));
    }

    public function get_rasio_pt($id_pt)
    {
        return $this->fetch('/pt/rasio/' . rawurlencode((string) $id_pt) . '/');
    }

    public function get_mahasiswa_stats($id_pt)
    {
        return $this->fetch('/pt/mahasiswa/' . rawurlencode((string) $id_pt) . '/');
    }

    private function fetch($endpoint)
    {
        $attempts = 3;
        $last_message = '';

        foreach ($this->base_urls_for_request() as $base_url) {
            $url = $base_url . $endpoint;

            for ($attempt = 1; $attempt <= $attempts; $attempt++) {
                try {
                    $response = $this->request($url);
                } catch (Exception $exception) {
                    $last_message = $exception->getMessage();
                    if ($attempt < $attempts) {
                        sleep($attempt);
                        continue;
                    }

                    break;
                }

                $body = $response['body'];
                $status = (int) $response['status'];

                if (($status >= 500 || $status === 429) && $attempt < $attempts) {
                    sleep($attempt);
                    continue;
                }

                if ($status >= 500 || $status === 429) {
                    $last_message = $this->http_error_message($status, $endpoint, $base_url);
                    break;
                }

                if ($status >= 400) {
                    throw new Exception($this->http_error_message($status, $endpoint, $base_url));
                }

                $decoded = json_decode($body, TRUE);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Response PDDikti tidak valid JSON.');
                }

                $this->active_base_url = $base_url;
                return $decoded;
            }
        }

        throw new Exception($last_message !== '' ? $last_message : 'Gagal mengambil data dari PDDikti.');
    }

    private function request($url)
    {
        $body = FALSE;
        $status = 0;

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
            curl_setopt($ch, CURLOPT_USERAGENT, 'AMI-Profil/1.0');
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
            $body = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($body === FALSE) {
                throw new Exception('Gagal menghubungi PDDikti: ' . ($error ?: 'koneksi gagal.'));
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 20,
                    'header' => "Accept: application/json\r\nUser-Agent: AMI-Profil/1.0\r\n",
                ],
            ]);
            $body = @file_get_contents($url, FALSE, $context);
            if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
                $status = (int) $matches[1];
            }

            if ($body === FALSE) {
                throw new Exception('Gagal menghubungi PDDikti. Ekstensi cURL tidak tersedia.');
            }
        }

        return ['body' => $body, 'status' => $status];
    }

    private function http_error_message($status, $endpoint, $base_url)
    {
        if ((int) $status === 503) {
            return 'Layanan PDDikti sedang tidak tersedia (HTTP 503). Coba sinkronkan lagi beberapa saat nanti, atau isi profil secara manual dulu. Endpoint: ' . $base_url . $endpoint;
        }

        if ((int) $status === 429) {
            return 'PDDikti membatasi terlalu banyak permintaan (HTTP 429). Tunggu beberapa saat lalu coba lagi. Endpoint: ' . $base_url . $endpoint;
        }

        return 'PDDikti mengembalikan HTTP ' . (int) $status . ' untuk endpoint ' . $base_url . $endpoint . '.';
    }

    private function base_urls_for_request()
    {
        $urls = [$this->active_base_url];

        foreach ($this->base_urls as $base_url) {
            if (!in_array($base_url, $urls, TRUE)) {
                $urls[] = $base_url;
            }
        }

        return $urls;
    }

    private function map_detail_pt($detail, $id_pt, $nama_pt, $rasio_payload, $search_record = [])
    {
        $nama_pddikti = $this->pick($search_record, ['nama_pt', 'nama', 'text', 'label']) ?: $nama_pt;

        return [
            'id_pt_pddikti' => $id_pt,
            'nama_pt_pddikti' => $nama_pddikti,
            'nama_pt' => $this->pick($detail, ['nama_pt', 'nama', 'nm_lemb', 'nama_lembaga']) ?: $nama_pddikti,
            'kode_pt' => $this->pick($detail, ['kode_pt', 'kode', 'kode_perguruan_tinggi']) ?: $this->pick($search_record, ['kode_pt', 'kode', 'kode_perguruan_tinggi']),
            'nomor_sk_pt' => $this->pick($detail, ['no_sk_pend', 'nomor_sk_pt', 'no_sk', 'sk_pendirian_sp', 'sk_pendirian']),
            'tanggal_sk_pt' => $this->parse_date($this->pick($detail, ['tgl_sk_pend', 'tgl_sk_pendirian_sp', 'tanggal_sk_pt', 'tanggal_sk'])),
            'tanggal_berdiri' => $this->parse_date($this->pick($detail, ['tgl_berdiri', 'tgl_berdiri_pt', 'tanggal_berdiri'])),
            'jumlah_dosen' => $this->pick($detail, ['jumlah_dosen', 'jml_dosen', 'dosen']) ?: $this->extract_total_dosen($rasio_payload),
            'jumlah_tendik' => $this->pick($detail, ['jumlah_tendik', 'jml_tendik', 'tenaga_kependidikan', 'tendik']),
            'akreditasi' => $this->pick($detail, ['akreditasi', 'akreditas', 'akreditasi_pt']),
            'akreditasi_berlaku_sampai' => $this->parse_date($this->pick($detail, ['akreditasi_berlaku_sampai', 'tgl_akreditasi_berlaku', 'tanggal_akreditasi_berlaku'])),
            'status_pt' => $this->pick($detail, ['status', 'status_pt', 'stat_sp']),
            'kode_pos' => $this->pick($detail, ['kode_pos', 'kd_pos']),
            'telepon' => $this->pick($detail, ['telepon', 'telp', 'no_tel']),
            'faksimile' => $this->pick($detail, ['fax', 'no_fax', 'faksimile', 'faks']),
            'email' => $this->pick($detail, ['email', 'email_pt']),
        ];
    }

    private function map_prodi_pt($payload, $rasio_payload)
    {
        $records = $this->as_records($this->unwrap_payload($payload));
        $rasio_map = $this->build_rasio_map($rasio_payload);
        $rows = [];

        foreach ($records as $record) {
            $id = $this->pick($record, ['id_prodi_pddikti', 'id_prodi', 'id_sms', 'id']);
            $kode = $this->pick($record, ['kode_prodi', 'kode_program_studi', 'kode']);
            $nama = $this->pick($record, ['nama_prodi', 'nama_program_studi', 'nama', 'nm_lemb']);

            if ($kode === '' && $nama === '') {
                continue;
            }

            $rows[] = [
                'id_prodi_pddikti' => $id,
                'kode_prodi' => $kode,
                'nama_prodi' => $nama,
                'status' => $this->pick($record, ['status', 'status_prodi', 'stat_prodi']),
                'jenjang' => $this->normalize_jenjang($this->pick($record, ['jenjang', 'jenjang_prodi', 'jenjang_didik', 'nama_jenjang', 'strata'])),
                'akreditasi' => $this->pick($record, ['akreditasi', 'akreditas']),
                'tanggal_sk_akreditasi' => $this->parse_date($this->pick($record, ['tanggal_sk_akreditasi', 'tgl_sk_akreditasi', 'tgl_sk'])),
                'rasio_dosen_mahasiswa' => $this->pick($record, ['rasio_dosen_mahasiswa', 'rasio', 'ratio']) ?: $this->find_rasio_for_prodi($record, $rasio_map),
            ];
        }

        return $rows;
    }

    private function map_mahasiswa_stats($payload)
    {
        $data = $this->unwrap_payload($payload);
        $rows = [];

        if (is_array($data) && !$this->is_list($data)) {
            foreach ($data as $key => $value) {
                if (is_scalar($value) && $this->is_known_jenjang_key((string) $key) && preg_match('/\d+/', (string) $value)) {
                    $rows[] = [
                        'jenjang' => $this->normalize_jenjang((string) $key),
                        'jumlah' => (int) preg_replace('/[^0-9]/', '', (string) $value),
                    ];
                }
            }

            if (!empty($rows)) {
                return $rows;
            }
        }

        foreach ($this->as_records($data) as $record) {
            $jenjang = $this->pick($record, ['jenjang', 'nama_jenjang', 'strata', 'label']);
            $jumlah = $this->pick($record, ['jumlah', 'total', 'jml_mhs', 'jumlah_mahasiswa', 'value']);

            if ($jenjang === '' || $jumlah === '') {
                continue;
            }

            $rows[] = [
                'jenjang' => $this->normalize_jenjang($jenjang),
                'jumlah' => (int) preg_replace('/[^0-9]/', '', (string) $jumlah),
            ];
        }

        return $rows;
    }

    private function build_rasio_map($payload)
    {
        $records = $this->as_records($this->unwrap_payload($payload));
        $map = [];

        foreach ($records as $record) {
            $ratio = $this->pick($record, ['rasio_dosen_mahasiswa', 'rasio', 'ratio']);
            if ($ratio === '') {
                $dosen = (int) preg_replace('/[^0-9]/', '', $this->pick($record, ['jumlah_dosen', 'jml_dosen', 'dosen']));
                $mahasiswa = (int) preg_replace('/[^0-9]/', '', $this->pick($record, ['jumlah_mahasiswa', 'jml_mhs', 'mahasiswa']));
                if ($dosen > 0 && $mahasiswa > 0) {
                    $ratio = '1:' . max(1, (int) round($mahasiswa / $dosen));
                }
            }

            if ($ratio === '') {
                continue;
            }

            foreach (['id_prodi_pddikti', 'id_prodi', 'id_sms', 'id', 'kode_prodi', 'kode', 'nama_prodi', 'nama'] as $key) {
                $value = strtolower(trim($this->pick($record, [$key])));
                if ($value !== '') {
                    $map[$value] = $ratio;
                }
            }
        }

        return $map;
    }

    private function find_rasio_for_prodi($record, $rasio_map)
    {
        foreach (['id_prodi_pddikti', 'id_prodi', 'id_sms', 'id', 'kode_prodi', 'kode', 'nama_prodi', 'nama_program_studi', 'nama'] as $key) {
            $value = strtolower(trim($this->pick($record, [$key])));
            if ($value !== '' && isset($rasio_map[$value])) {
                return $rasio_map[$value];
            }
        }

        return '';
    }

    private function extract_total_dosen($payload)
    {
        $records = $this->as_records($this->unwrap_payload($payload));
        $total = 0;

        foreach ($records as $record) {
            $value = $this->pick($record, ['jumlah_dosen', 'jml_dosen', 'dosen']);
            if ($value !== '') {
                $total += (int) preg_replace('/[^0-9]/', '', (string) $value);
            }
        }

        return $total > 0 ? $total : '';
    }

    private function derive_mahasiswa_stats_from_prodi($payload)
    {
        $records = $this->as_records($this->unwrap_payload($payload));
        $stats = [];

        foreach ($records as $record) {
            $jenjang = $this->normalize_jenjang($this->pick($record, ['jenjang', 'jenjang_prodi', 'jenjang_didik', 'nama_jenjang', 'strata']));
            $jumlah = $this->pick($record, ['jumlah_mahasiswa', 'jml_mhs', 'mahasiswa']);

            if ($jenjang === '' || $jumlah === '') {
                continue;
            }

            if (!isset($stats[$jenjang])) {
                $stats[$jenjang] = 0;
            }

            $stats[$jenjang] += (int) preg_replace('/[^0-9]/', '', (string) $jumlah);
        }

        $rows = [];
        foreach ($stats as $jenjang => $jumlah) {
            $rows[] = [
                'jenjang' => $jenjang,
                'jumlah' => $jumlah,
            ];
        }

        return $rows;
    }

    private function unwrap_payload($payload)
    {
        if (!is_array($payload)) {
            return [];
        }

        foreach (['data', 'result', 'results', 'items', 'rows'] as $key) {
            if (array_key_exists($key, $payload) && is_array($payload[$key])) {
                return $payload[$key];
            }
        }

        return $payload;
    }

    private function as_records($data)
    {
        if (!is_array($data)) {
            return [];
        }

        if ($this->is_list($data)) {
            return array_values(array_filter($data, 'is_array'));
        }

        return [$data];
    }

    private function is_list($array)
    {
        if (!is_array($array)) {
            return FALSE;
        }

        return array_keys($array) === range(0, count($array) - 1);
    }

    private function pick($row, $keys)
    {
        if (!is_array($row)) {
            return '';
        }

        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== NULL && trim((string) $row[$key]) !== '') {
                return trim((string) $row[$key]);
            }
        }

        return '';
    }

    private function id_from_record($record, $throw = TRUE)
    {
        $id = $this->pick($record, ['id', 'id_pt', 'id_sp', 'id_pt_pddikti']);

        if ($id !== '' || !$throw) {
            return $id;
        }

        throw new Exception('ID perguruan tinggi tidak ditemukan pada response PDDikti.');
    }

    private function parse_date($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return NULL;
        }

        $value = str_ireplace(
            ['Januari', 'Februari', 'Maret', 'Mei', 'Juni', 'Juli', 'Agustus', 'Oktober', 'Desember'],
            ['January', 'February', 'March', 'May', 'June', 'July', 'August', 'October', 'December'],
            $value
        );

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $value, $matches)) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }

        $timestamp = strtotime($value);
        return $timestamp !== FALSE ? date('Y-m-d', $timestamp) : NULL;
    }

    private function normalize_jenjang($value)
    {
        $value = trim((string) $value);
        $upper = strtoupper($value);

        $map = [
            'D1' => 'Diploma',
            'D2' => 'Diploma',
            'D3' => 'Diploma',
            'D4' => 'Sarjana Terapan',
            'S1' => 'Sarjana',
            'S2' => 'Magister',
            'S3' => 'Doktor',
            'PROFESI' => 'Profesi',
        ];

        return isset($map[$upper]) ? $map[$upper] : ($value !== '' ? $value : 'Lainnya');
    }

    private function is_known_jenjang_key($value)
    {
        $value = strtolower(trim((string) $value));

        $keywords = [
            'diploma',
            'sarjana',
            'sarjana terapan',
            'magister',
            'doktor',
            'profesi',
            'd1',
            'd2',
            'd3',
            'd4',
            's1',
            's2',
            's3',
        ];

        foreach ($keywords as $keyword) {
            if ($value === $keyword || strpos($value, $keyword) !== FALSE) {
                return TRUE;
            }
        }

        return FALSE;
    }

    private function current_semester_code()
    {
        $year = (int) date('Y');
        $month = (int) date('n');

        if ($month >= 8) {
            return (string) $year . '1';
        }

        return (string) ($year - 1) . '2';
    }
}
