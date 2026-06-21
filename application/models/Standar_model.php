<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Standar_model extends CI_Model
{
    protected $table = 'standar';

    public function create($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function find($id)
    {
        return $this->db->where('id', (int) $id)->get($this->table)->row();
    }

    public function update($id, $data)
    {
        return $this->db->where('id', (int) $id)->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db->where('id', (int) $id)->delete($this->table);
    }

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

    public function count_all()
    {
        if (!$this->db->table_exists($this->table)) {
            return 0;
        }

        return (int) $this->db->count_all_results($this->table);
    }

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
