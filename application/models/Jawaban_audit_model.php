<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jawaban_audit_model extends CI_Model
{
    protected $table = 'jawaban_audit';

    public function insert_batch($data)
    {
        return $this->db->insert_batch($this->table, $data);
    }

    public function get_by_tugas($tugas_id)
    {
        if (!$this->db->table_exists($this->table) || !$this->db->table_exists('pertanyaan')) {
            return [];
        }

        return $this->db
            ->select('jawaban_audit.*, pertanyaan.isi_pertanyaan')
            ->from($this->table)
            ->join('pertanyaan', 'pertanyaan.id = jawaban_audit.pertanyaan_id', 'left')
            ->where('jawaban_audit.tugas_id', (int) $tugas_id)
            ->order_by('pertanyaan.id', 'ASC')
            ->get()
            ->result();
    }

    public function update_penilaian_batch($data)
    {
        if (empty($data)) {
            return TRUE;
        }

        return $this->db->update_batch($this->table, $data, 'id');
    }

    public function update_jawaban_batch($data)
    {
        if (empty($data)) {
            return TRUE;
        }

        return $this->db->update_batch($this->table, $data, 'id');
    }
}
