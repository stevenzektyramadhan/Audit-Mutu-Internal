<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Penetapan_model extends CI_Model
{
    /** @var string */
    protected $table = 'penetapan';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all penetapan, optionally filtered by kategori
     * @param  string|null $kategori
     * @return array
     */
    public function get_all($kategori = null)
    {
        $select = 'penetapan.id, penetapan.standar_id, penetapan.kategori, ';
        $select .= 'penetapan.status, penetapan.deskripsi, penetapan.file_path, ';
        $select .= 'penetapan.created_at, penetapan.updated_at, ';
        $select .= 'standar.nama_standar';
        $this->db->select($select);
        $this->db->from($this->table);
        $this->db->join('standar', 'standar.id = penetapan.standar_id', 'left');

        if ($kategori !== null) {
            $this->db->where('penetapan.kategori', $kategori);
        }

        return $this->db->order_by('standar.nama_standar', 'ASC')->get()->result();
    }

    public function get_grouped_by_kategori($kategori_list)
    {
        $result = [];

        foreach ($kategori_list as $kategori) {
            $result[$kategori] = $this->get_all($kategori);
        }

        return $result;
    }

    public function ensure_records_for_standar($standar_list, $kategori_list)
    {
        foreach ($standar_list as $standar) {
            foreach ($kategori_list as $kategori) {
                if (!$this->exists((int) $standar->id, $kategori)) {
                    $this->create([
                        'standar_id' => (int) $standar->id,
                        'kategori' => $kategori,
                    ]);
                }
            }
        }
    }

    public function exists($standar_id, $kategori)
    {
        return $this->db
            ->where('standar_id', (int) $standar_id)
            ->where('kategori', $kategori)
            ->count_all_results($this->table) > 0;
    }

    /**
     * Find by ID
     * @param  int $id
     * @return object|null
     */
    public function find($id)
    {
        return $this->db->where('id', (int) $id)->get($this->table)->row();
    }

    /**
     * Create new record
     * @param  array $data
     * @return bool
     */
    public function create($data)
    {
        return $this->db->insert($this->table, $data);
    }

    /**
     * Update record by ID
     * @param  int   $id
     * @param  array $data
     * @return bool
     */
    public function update($id, $data)
    {
        return $this->db->where('id', (int) $id)->update($this->table, $data);
    }

    /**
     * Delete record by ID
     * @param  int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->db->where('id', (int) $id)->delete($this->table);
    }
}
