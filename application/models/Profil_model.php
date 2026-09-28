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

    public function get_prodi_master_data()
    {
        if (!$this->db->table_exists($this->prodi_table)) {
            return [];
        }

        $has_staff_table = $this->db->table_exists('staf_prodi');
        $this->db->select('profil_prodi.*');
        if ($has_staff_table) {
            $this->db->select("COUNT(CASE WHEN staf_prodi.status = 'active' THEN staf_prodi.id END) AS active_staff_count", FALSE);
            $this->db->join('staf_prodi', 'staf_prodi.id_prodi = profil_prodi.id', 'left');
        } else {
            $this->db->select('0 AS active_staff_count', FALSE);
        }

        return $this->db
            ->from($this->prodi_table)
            ->order_by('profil_prodi.jenjang', 'ASC')
            ->order_by('profil_prodi.nama_prodi', 'ASC')
            ->group_by('profil_prodi.id')
            ->get()
            ->result();
    }

    public function get_prodi_master_directory_data()
    {
        if (!$this->db->table_exists($this->prodi_table)) {
            return [];
        }

        $has_staff_table = $this->db->table_exists('staf_prodi');
        $this->db->select('profil_prodi.*');
        $this->db->select('ou.id AS mapped_organization_unit_id, ou.code AS organization_unit_code, ou.name AS organization_unit_name, ou.type AS organization_unit_type, ou.is_active AS organization_unit_is_active');
        $this->db->select('parent.id AS faculty_id, parent.code AS faculty_code, parent.name AS faculty_name');
        $this->db->select('CASE WHEN profil_prodi.organization_unit_id IS NULL THEN 0 WHEN ou.id IS NULL THEN 0 ELSE 1 END AS is_mapped_to_organization', FALSE);
        $this->db->select("CASE WHEN profil_prodi.organization_unit_id IS NULL THEN 'unmapped' WHEN ou.id IS NULL THEN 'missing_unit' WHEN ou.type != 'study_program' THEN 'invalid_type' ELSE 'mapped' END AS organization_mapping_status", FALSE);
        if ($has_staff_table) {
            $this->db->select("COUNT(CASE WHEN staf_prodi.status = 'active' THEN staf_prodi.id END) AS active_staff_count", FALSE);
            $this->db->join('staf_prodi', 'staf_prodi.id_prodi = profil_prodi.id', 'left');
        } else {
            $this->db->select('0 AS active_staff_count', FALSE);
        }

        return $this->db
            ->from($this->prodi_table)
            ->join('organization_units ou', 'ou.id = profil_prodi.organization_unit_id', 'left')
            ->join('organization_units parent', 'parent.id = ou.parent_id', 'left')
            ->order_by('parent.code', 'ASC')
            ->order_by('profil_prodi.jenjang', 'ASC')
            ->order_by('profil_prodi.nama_prodi', 'ASC')
            ->group_by('profil_prodi.id')
            ->get()
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

    public function find_prodi($id)
    {
        return $this->db->where('id', (int) $id)->get($this->prodi_table)->row();
    }

    public function active_faculties()
    {
        return $this->db->where(['type' => 'faculty', 'is_active' => 1])->order_by('code', 'ASC')->order_by('name', 'ASC')->get('organization_units')->result();
    }

    public function prodi_faculty_id($organization_unit_id)
    {
        $unit = $this->db->select('parent_id')->where(['id' => (int) $organization_unit_id, 'type' => 'study_program'])->get('organization_units')->row();
        return $unit ? (int) $unit->parent_id : 0;
    }

    public function find_prodi_for_update($id)
    {
        return $this->db->query('SELECT * FROM profil_prodi WHERE id = ? FOR UPDATE', [(int) $id])->row();
    }

    public function create_prodi($data)
    {
        return $this->db->insert($this->prodi_table, $data);
    }

    public function update_prodi($id, $data)
    {
        return $this->db->where('id', (int) $id)->update($this->prodi_table, $data);
    }

    public function delete_prodi($id)
    {
        return $this->db->where('id', (int) $id)->delete($this->prodi_table);
    }

    public function find_organization_unit_for_update($id)
    {
        return $this->db->query('SELECT * FROM organization_units WHERE id = ? FOR UPDATE', [(int) $id])->row();
    }

    public function find_organization_units_by_code_for_update($code)
    {
        return $this->db->query('SELECT * FROM organization_units WHERE code = ? FOR UPDATE', [trim((string) $code)])->result();
    }

    public function organization_unit_bound_to_prodi($organization_unit_id, $except_prodi_id = 0)
    {
        $this->db->where('organization_unit_id', (int) $organization_unit_id);
        if ($except_prodi_id) $this->db->where('id !=', (int) $except_prodi_id);
        return $this->db->count_all_results($this->prodi_table) > 0;
    }

    public function create_organization_unit($data)
    {
        return $this->db->insert('organization_units', $data);
    }

    public function update_organization_unit($id, $data)
    {
        return $this->db->where('id', (int) $id)->update('organization_units', $data);
    }

    public function prodi_has_staf($id)
    {
        return $this->db->table_exists('staf_prodi')
            && $this->db->where('id_prodi', (int) $id)->count_all_results('staf_prodi') > 0;
    }

    public function prodi_staf_for_update($id)
    {
        if (!$this->db->table_exists('staf_prodi')) {
            return [];
        }

        return $this->db->query('SELECT id FROM staf_prodi WHERE id_prodi = ? FOR UPDATE', [(int) $id])->result();
    }

    public function find_mahasiswa_stat($id)
    {
        return $this->db->where('id', (int) $id)->get($this->mahasiswa_table)->row();
    }

    public function create_mahasiswa_stat($data)
    {
        return $this->db->insert($this->mahasiswa_table, $data);
    }

    public function update_mahasiswa_stat($id, $data)
    {
        return $this->db->where('id', (int) $id)->update($this->mahasiswa_table, $data);
    }

    public function delete_mahasiswa_stat($id)
    {
        return $this->db->where('id', (int) $id)->delete($this->mahasiswa_table);
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
        $this->db->query('SELECT id FROM profil_prodi ORDER BY id ASC FOR UPDATE');
        $relations = [];
        $organization_links = [];
        foreach ($this->db->select('kode_prodi, organization_unit_id')->where('kode_prodi IS NOT NULL', NULL, FALSE)->where('organization_unit_id IS NOT NULL', NULL, FALSE)->get($this->prodi_table)->result_array() as $link) {
            if (!isset($organization_links[$link['kode_prodi']])) {
                $organization_links[$link['kode_prodi']] = (int) $link['organization_unit_id'];
            }
        }
        if ($this->db->table_exists('staf_prodi')) {
            $query = $this->db->select('staf_prodi.id_akun, staf_prodi.jabatan, staf_prodi.status, profil_prodi.kode_prodi')
                ->from('staf_prodi')
                ->join($this->prodi_table, 'profil_prodi.id = staf_prodi.id_prodi')
                ->where('profil_prodi.kode_prodi IS NOT NULL', NULL, FALSE)
                ->get_compiled_select();
            $relations = $this->db->query($query . ' FOR UPDATE')->result_array();
        }

        $this->db->delete($this->prodi_table);

        foreach ($rows as &$row) {
            $code = isset($row['kode_prodi']) ? $row['kode_prodi'] : NULL;
            $row['organization_unit_id'] = $code !== NULL && isset($organization_links[$code]) ? $organization_links[$code] : NULL;
        }
        unset($row);

        if (!empty($rows)) {
            $this->db->insert_batch($this->prodi_table, $rows);
        }

        if (!empty($relations)) {
            $prodi_ids = [];
            foreach ($this->db->select('id, kode_prodi')->where('kode_prodi IS NOT NULL', NULL, FALSE)->get($this->prodi_table)->result() as $prodi) {
                if (!isset($prodi_ids[$prodi->kode_prodi])) {
                    $prodi_ids[$prodi->kode_prodi] = (int) $prodi->id;
                }
            }
            foreach ($relations as $relation) {
                if (isset($prodi_ids[$relation['kode_prodi']])) {
                    $this->db->insert('staf_prodi', [
                        'id_akun' => (int) $relation['id_akun'],
                        'id_prodi' => $prodi_ids[$relation['kode_prodi']],
                        'jabatan' => $relation['jabatan'],
                        'status' => $relation['status'],
                    ]);
                }
            }
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
