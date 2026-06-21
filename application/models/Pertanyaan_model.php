<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pertanyaan_model extends CI_Model
{
    protected $table = 'pertanyaan';

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

    public function is_used_in_answers($id)
    {
        if (!$this->db->table_exists('jawaban_audit')) {
            return FALSE;
        }

        return $this->db
            ->where('pertanyaan_id', (int) $id)
            ->count_all_results('jawaban_audit') > 0;
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

    public function get_by_standar($standar_id)
    {
        return $this->db
            ->where('standar_id', (int) $standar_id)
            ->order_by('id', 'ASC')
            ->get($this->table)
            ->result();
    }
}
