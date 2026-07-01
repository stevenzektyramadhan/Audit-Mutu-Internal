<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Rata-rata skor per standar
     * @param  int|null $periode_id
     * @return array
     */
    public function skor_per_standar($periode_id = null)
    {
        $select = 'standar.id as standar_id, ';
        $select .= 'standar.nama_standar, ';
        $select .= 'COUNT(DISTINCT jawaban_audit.id) as total_jawaban, ';
        $select .= 'AVG(jawaban_audit.skor) as rata_rata_skor';

        $this->db->select($select);
        $this->db->from('jawaban_audit');
        $this->db->join('tugas_audit', 'tugas_audit.id = jawaban_audit.tugas_id');
        $this->db->join('standar', 'standar.id = tugas_audit.standar_id');
        $this->db->where('jawaban_audit.is_nilai_submitted', 1);

        if ($periode_id !== null) {
            $this->db->where('tugas_audit.periode_id', (int) $periode_id);
        }

        $this->db->group_by('standar.id');
        return $this->db->get()->result();
    }

    /**
     * Rata-rata skor per auditee
     * @param  int|null $periode_id
     * @return array
     */
    public function skor_per_auditee($periode_id = null)
    {
        $select = 'auditee.id as auditee_id, ';
        $select .= 'auditee.nama as auditee_nama, ';
        $select .= 'COUNT(DISTINCT jawaban_audit.id) as total_jawaban, ';
        $select .= 'AVG(jawaban_audit.skor) as rata_rata_skor';

        $this->db->select($select);
        $this->db->from('jawaban_audit');
        $this->db->join('tugas_audit', 'tugas_audit.id = jawaban_audit.tugas_id');
        $this->db->join('users auditee', 'auditee.id = tugas_audit.auditee_id');
        $this->db->where('jawaban_audit.is_nilai_submitted', 1);

        if ($periode_id !== null) {
            $this->db->where('tugas_audit.periode_id', (int) $periode_id);
        }

        $this->db->group_by('auditee.id');
        return $this->db->get()->result();
    }
}