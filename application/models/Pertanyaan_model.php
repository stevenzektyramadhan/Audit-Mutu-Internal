<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pertanyaan_model extends CI_Model
{
    protected $table = 'pertanyaan';

    public function create($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function count_all()
    {
        if (!$this->db->table_exists($this->table)) {
            return 0;
        }

        return (int) $this->db->count_all_results($this->table);
    }

    public function get_all_with_standar()
    {
        if (!$this->db->table_exists($this->table)) {
            return [];
        }

        return $this->db
            ->select('pertanyaan.*, standar.nama_standar')
            ->from($this->table)
            ->join('standar', 'standar.id = pertanyaan.standar_id', 'left')
            ->order_by('pertanyaan.id', 'ASC')
            ->get()
            ->result();
    }
}
