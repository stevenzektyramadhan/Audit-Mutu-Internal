<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pertanyaan_model extends CI_Model
{
    protected $table = 'pertanyaan';

    public function create($data)
    {
        if (!array_key_exists('urutan', $data) && isset($data['standar_id'])) {
            $last_order = $this->db
                ->select_max('urutan', 'last_order')
                ->where('standar_id', (int) $data['standar_id'])
                ->get($this->table)
                ->row();
            $data['urutan'] = $last_order && $last_order->last_order !== NULL
                ? (int) $last_order->last_order + 1
                : 1;
        }

        return $this->db->insert($this->table, $data);
    }

    public function insert_bulk($standar_id, $data_array)
    {
        $standar_id = (int) $standar_id;
        if ($standar_id <= 0 || empty($data_array)) {
            return 0;
        }

        $last_order = $this->db
            ->select_max('urutan', 'last_order')
            ->where('standar_id', $standar_id)
            ->get($this->table)
            ->row();
        $next_order = $last_order && $last_order->last_order !== NULL
            ? (int) $last_order->last_order + 1
            : 1;

        $this->db->trans_start();

        foreach ($data_array as $data) {
            $row = [
                'standar_id' => $standar_id,
                'urutan' => $next_order++,
                'isi_pertanyaan' => isset($data['isi_pertanyaan']) ? $data['isi_pertanyaan'] : '',
                'nilai_standar' => isset($data['nilai_standar']) ? $data['nilai_standar'] : NULL,
                'baseline' => isset($data['baseline']) ? $data['baseline'] : NULL,
                'target_2025' => isset($data['target_2025']) ? $data['target_2025'] : NULL,
                'target_2026' => isset($data['target_2026']) ? $data['target_2026'] : NULL,
                'target_2027' => isset($data['target_2027']) ? $data['target_2027'] : NULL,
                'target_2028' => isset($data['target_2028']) ? $data['target_2028'] : NULL,
                'target_2029' => isset($data['target_2029']) ? $data['target_2029'] : NULL,
                'target_2030' => isset($data['target_2030']) ? $data['target_2030'] : NULL,
                'kategori' => isset($data['kategori']) ? $data['kategori'] : NULL,
            ];

            if (!$this->db->insert($this->table, $row)) {
                $this->db->trans_rollback();
                return FALSE;
            }
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            return FALSE;
        }

        return count($data_array);
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

    public function get_all_with_standar($standar_id = NULL)
    {
        if (!$this->db->table_exists($this->table)) {
            return [];
        }

        $this->db
            ->select('pertanyaan.*, standar.nama_standar')
            ->from($this->table)
            ->join('standar', 'standar.id = pertanyaan.standar_id', 'left');

        if ($standar_id !== NULL) {
            $this->db->where('pertanyaan.standar_id', (int) $standar_id);
        }

        return $this->db
            ->order_by('pertanyaan.urutan', 'ASC')
            ->order_by('pertanyaan.id', 'ASC')
            ->get()
            ->result();
    }

    public function get_by_standar($standar_id)
    {
        return $this->db
            ->where('standar_id', (int) $standar_id)
            ->order_by('urutan', 'ASC')
            ->order_by('id', 'ASC')
            ->get($this->table)
            ->result();
    }
}
