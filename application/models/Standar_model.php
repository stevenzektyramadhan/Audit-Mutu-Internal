<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Standar_model extends CI_Model
{
    /** @var string */
    protected $table = 'standar';

    /**
     * Create new standar
     * @param  array $data
     * @return bool
     */
    public function create($data)
    {
        return $this->db->insert($this->table, $data);
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
     * Update standar by ID
     * @param  int   $id
     * @param  array $data
     * @return bool
     */
    public function update($id, $data)
    {
        return $this->db->where('id', (int) $id)->update($this->table, $data);
    }

    public function update_instrumen_file($id, $file_name)
    {
        return $this->update((int) $id, ['file_instrumen' => $file_name]);
    }

    public function clear_instrumen_file($id)
    {
        return $this->update((int) $id, ['file_instrumen' => NULL]);
    }

    /**
     * Delete standar by ID
     * @param  int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->db->where('id', (int) $id)->delete($this->table);
    }

    /**
     * Get all standar
     * @return array
     */
    public function get_all()
    {
        if (!$this->db->table_exists($this->table)) {
            return [];
        }

        return $this->db
            ->order_by('standar.id', 'ASC')
            ->get($this->table)
            ->result();
    }

    /**
     * Get all standar with pertanyaan count
     * @return array
     */
    public function get_all_with_count()
    {
        if (!$this->db->table_exists($this->table)) {
            return [];
        }

        return $this->db
            ->select('standar.*, COUNT(pertanyaan.id) AS total_pertanyaan')
            ->from($this->table)
            ->join('pertanyaan', 'pertanyaan.standar_id = standar.id', 'left')
            ->group_by('standar.id')
            ->order_by('standar.id', 'ASC')
            ->get()
            ->result();
    }

    /**
     * Count all standar
     * @return int
     */
    public function count_all()
    {
        if (!$this->db->table_exists($this->table)) {
            return 0;
        }

        return (int) $this->db->count_all_results($this->table);
    }

    /**
     * Get standar summary with pertanyaan count
     * @param  int $limit
     * @return array
     */
    public function get_summary($limit = 5)
    {
        if (!$this->db->table_exists($this->table) || !$this->db->table_exists('pertanyaan')) {
            return [];
        }

        return $this->db
            ->select('standar.id, standar.nama_standar, COUNT(pertanyaan.id) AS total_pertanyaan')
            ->from($this->table)
            ->join('pertanyaan', 'pertanyaan.standar_id = standar.id', 'left')
            ->group_by('standar.id')
            ->order_by('standar.id', 'DESC')
            ->limit((int) $limit)
            ->get()
            ->result();
    }
}
