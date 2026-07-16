<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profil_model extends CI_Model
{
    protected $profil_table = 'profil_lembaga';
    protected $prodi_table = 'profil_prodi';
    protected $mahasiswa_table = 'profil_mahasiswa_stats';

    public function has_tables()
    {
        return $this->db->table_exists($this->profil_table)
            && $this->db->table_exists($this->prodi_table)
            && $this->db->table_exists($this->mahasiswa_table);
    }

    public function get_profil()
    {
        if (!$this->db->table_exists($this->profil_table)) {
            return NULL;
        }

        return $this->db
            ->order_by('id', 'ASC')
            ->limit(1)
            ->get($this->profil_table)
            ->row();
    }

    public function get_prodi()
    {
        if (!$this->db->table_exists($this->prodi_table)) {
            return [];
        }

        return $this->db
            ->order_by('jenjang', 'ASC')
            ->order_by('nama_prodi', 'ASC')
            ->get($this->prodi_table)
            ->result();
    }

    public function get_mahasiswa_stats()
    {
        if (!$this->db->table_exists($this->mahasiswa_table)) {
            return [];
        }

        return $this->db
            ->order_by('jumlah', 'DESC')
            ->order_by('jenjang', 'ASC')
            ->get($this->mahasiswa_table)
            ->result();
    }

    public function get_akreditasi_summary()
    {
        if (!$this->db->table_exists($this->prodi_table)) {
            return [];
        }

        return $this->db
            ->select("COALESCE(NULLIF(TRIM(akreditasi), ''), 'Lainnya') AS akreditasi", FALSE)
            ->select('COUNT(id) AS jumlah', FALSE)
            ->from($this->prodi_table)
            ->group_by("COALESCE(NULLIF(TRIM(akreditasi), ''), 'Lainnya')", FALSE)
            ->order_by('jumlah', 'DESC')
            ->get()
            ->result();
    }

    public function upsert_profil($data)
    {
        if (!$this->db->table_exists($this->profil_table)) {
            return FALSE;
        }

        $data = $this->clean_profil_data($data);
        $existing = $this->get_profil();

        if ($existing) {
            return $this->db
                ->where('id', (int) $existing->id)
                ->update($this->profil_table, $data);
        }

        return $this->db->insert($this->profil_table, $data);
    }

    public function replace_prodi($rows)
    {
        if (!$this->db->table_exists($this->prodi_table)) {
            return FALSE;
        }

        $rows = $this->clean_prodi_rows($rows);

        $this->db->trans_start();
        $this->db->empty_table($this->prodi_table);

        if (!empty($rows)) {
            $this->db->insert_batch($this->prodi_table, $rows);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function replace_mahasiswa_stats($rows)
    {
        if (!$this->db->table_exists($this->mahasiswa_table)) {
            return FALSE;
        }

        $rows = $this->clean_mahasiswa_rows($rows);

        $this->db->trans_start();
        $this->db->empty_table($this->mahasiswa_table);

        if (!empty($rows)) {
            $this->db->insert_batch($this->mahasiswa_table, $rows);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    private function clean_profil_data($data)
    {
        $columns = [
            'id_pt_pddikti',
            'nama_pt_pddikti',
            'nama_pt',
            'kode_pt',
            'nomor_sk_pt',
            'tanggal_sk_pt',
            'tanggal_berdiri',
            'jumlah_dosen',
            'jumlah_tendik',
            'akreditasi',
            'akreditasi_berlaku_sampai',
            'status_pt',
            'kode_pos',
            'telepon',
            'faksimile',
            'email',
            'logo_path',
            'logo_url',
            'last_sync_at',
        ];

        $clean = [];
        foreach ($columns as $column) {
            if (array_key_exists($column, $data)) {
                $clean[$column] = $data[$column];
            }
        }

        foreach (['tanggal_sk_pt', 'tanggal_berdiri', 'akreditasi_berlaku_sampai'] as $date_column) {
            if (array_key_exists($date_column, $clean)) {
                $clean[$date_column] = $this->normalize_date($clean[$date_column]);
            }
        }

        foreach (['jumlah_dosen', 'jumlah_tendik'] as $int_column) {
            if (array_key_exists($int_column, $clean)) {
                $clean[$int_column] = $this->normalize_int($clean[$int_column]);
            }
        }

        foreach ($clean as $key => $value) {
            if (is_string($value)) {
                $clean[$key] = trim($value) !== '' ? trim($value) : NULL;
            }
        }

        return $clean;
    }

    private function clean_prodi_rows($rows)
    {
        $clean = [];

        foreach ((array) $rows as $row) {
            $item = [
                'id_prodi_pddikti' => $this->nullable_string($row, 'id_prodi_pddikti'),
                'kode_prodi' => $this->nullable_string($row, 'kode_prodi'),
                'nama_prodi' => $this->nullable_string($row, 'nama_prodi'),
                'status' => $this->nullable_string($row, 'status'),
                'jenjang' => $this->nullable_string($row, 'jenjang'),
                'akreditasi' => $this->nullable_string($row, 'akreditasi'),
                'tanggal_sk_akreditasi' => $this->normalize_date(isset($row['tanggal_sk_akreditasi']) ? $row['tanggal_sk_akreditasi'] : NULL),
                'rasio_dosen_mahasiswa' => $this->nullable_string($row, 'rasio_dosen_mahasiswa'),
            ];

            if ($item['nama_prodi'] !== NULL || $item['kode_prodi'] !== NULL) {
                $clean[] = $item;
            }
        }

        return $clean;
    }

    private function clean_mahasiswa_rows($rows)
    {
        $clean = [];

        foreach ((array) $rows as $row) {
            $jenjang = $this->nullable_string($row, 'jenjang');
            if ($jenjang === NULL) {
                continue;
            }

            $clean[] = [
                'jenjang' => $jenjang,
                'jumlah' => max(0, (int) (isset($row['jumlah']) ? $row['jumlah'] : 0)),
            ];
        }

        return $clean;
    }

    private function nullable_string($row, $key)
    {
        $value = isset($row[$key]) ? trim((string) $row[$key]) : '';
        return $value !== '' ? $value : NULL;
    }

    private function normalize_int($value)
    {
        if ($value === NULL || $value === '') {
            return NULL;
        }

        return max(0, (int) preg_replace('/[^0-9]/', '', (string) $value));
    }

    private function normalize_date($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return NULL;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $value, $matches)) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }

        $timestamp = strtotime($this->replace_indonesian_month($value));
        return $timestamp !== FALSE ? date('Y-m-d', $timestamp) : NULL;
    }

    private function replace_indonesian_month($value)
    {
        $map = [
            'Januari' => 'January',
            'Februari' => 'February',
            'Maret' => 'March',
            'Mei' => 'May',
            'Juni' => 'June',
            'Juli' => 'July',
            'Agustus' => 'August',
            'Oktober' => 'October',
            'Desember' => 'December',
        ];

        return str_ireplace(array_keys($map), array_values($map), $value);
    }
}
