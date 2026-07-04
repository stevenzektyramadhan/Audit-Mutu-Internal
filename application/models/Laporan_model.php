<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function rekap_per_standar($filters = [])
    {
        if (!$this->tables_exist(['jawaban_audit', 'tugas_audit', 'standar', 'users'])) {
            return [];
        }

        $this->db
            ->select('standar.id AS standar_id, standar.nama_standar')
            ->select('COUNT(DISTINCT tugas_audit.id) AS total_tugas')
            ->select('COUNT(jawaban_audit.id) AS total_jawaban')
            ->select('AVG(jawaban_audit.skor) AS rata_rata_skor')
            ->select('MIN(jawaban_audit.skor) AS skor_min')
            ->select('MAX(jawaban_audit.skor) AS skor_max')
            ->from('standar')
            ->join('tugas_audit', 'tugas_audit.standar_id = standar.id', 'left')
            ->join('jawaban_audit', 'jawaban_audit.tugas_id = tugas_audit.id AND jawaban_audit.skor IS NOT NULL', 'left')
            ->join('users AS auditee', 'auditee.id = tugas_audit.auditee_id', 'left');

        $this->apply_filters($filters);

        return $this->db
            ->group_by('standar.id')
            ->order_by('standar.nama_standar', 'ASC')
            ->get()
            ->result();
    }

    public function detail_per_standar($standar_id, $filters = [])
    {
        if (!$this->tables_exist(['jawaban_audit', 'tugas_audit', 'standar', 'pertanyaan', 'users'])) {
            return [];
        }

        $this->db
            ->select('jawaban_audit.id, jawaban_audit.jawaban, jawaban_audit.link_bukti, jawaban_audit.skor, jawaban_audit.temuan, jawaban_audit.jenis_temuan, jawaban_audit.saran_perbaikan')
            ->select('pertanyaan.isi_pertanyaan, tugas_audit.id AS tugas_id, tugas_audit.status AS tugas_status')
            ->select('standar.nama_standar, auditor.nama AS auditor_nama, auditee.nama AS auditee_nama, periode_audit.nama_periode')
            ->from('jawaban_audit')
            ->join('tugas_audit', 'tugas_audit.id = jawaban_audit.tugas_id')
            ->join('standar', 'standar.id = tugas_audit.standar_id')
            ->join('pertanyaan', 'pertanyaan.id = jawaban_audit.pertanyaan_id', 'left')
            ->join('users AS auditor', 'auditor.id = tugas_audit.auditor_id', 'left')
            ->join('users AS auditee', 'auditee.id = tugas_audit.auditee_id', 'left')
            ->join('periode_audit', 'periode_audit.id = tugas_audit.periode_id', 'left')
            ->where('tugas_audit.standar_id', (int) $standar_id);

        $this->apply_filters($filters);

        return $this->db
            ->order_by('auditee.nama', 'ASC')
            ->order_by('pertanyaan.id', 'ASC')
            ->get()
            ->result();
    }

    public function find_standar($standar_id)
    {
        return $this->db
            ->where('id', (int) $standar_id)
            ->get('standar')
            ->row();
    }

    private function apply_filters($filters)
    {
        if (!empty($filters['periode_id'])) {
            $this->db->where('tugas_audit.periode_id', (int) $filters['periode_id']);
        }

        if (!empty($filters['auditee_id'])) {
            $this->db->where('tugas_audit.auditee_id', (int) $filters['auditee_id']);
        }
    }

    private function tables_exist($tables)
    {
        foreach ($tables as $table) {
            if (!$this->db->table_exists($table)) {
                return FALSE;
            }
        }

        return TRUE;
    }
}
