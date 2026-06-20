<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tugas_audit_model extends CI_Model
{
    protected $table = 'tugas_audit';

    public function create($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function count_all($where = [], $excluded_status = NULL)
    {
        if (!$this->db->table_exists($this->table)) {
            return 0;
        }

        if (!empty($where)) {
            $this->db->where($where);
        }

        if ($excluded_status !== NULL) {
            $this->db->where('status !=', $excluded_status);
        }

        return (int) $this->db->count_all_results($this->table);
    }

    public function get_recent($where = [], $limit = 5)
    {
        if (!$this->tables_exist(['tugas_audit', 'users', 'standar'])) {
            return [];
        }

        $this->db
            ->select('tugas_audit.id, tugas_audit.status, tugas_audit.created_at, standar.nama_standar, auditor.nama AS auditor_nama, auditee.nama AS auditee_nama')
            ->from($this->table)
            ->join('users AS auditor', 'auditor.id = tugas_audit.auditor_id')
            ->join('users AS auditee', 'auditee.id = tugas_audit.auditee_id')
            ->join('standar', 'standar.id = tugas_audit.standar_id');

        if (!empty($where)) {
            $this->db->where($where);
        }

        return $this->db
            ->order_by('tugas_audit.id', 'DESC')
            ->limit((int) $limit)
            ->get()
            ->result();
    }

    public function get_all_tugas()
    {
        if (!$this->tables_exist(['tugas_audit', 'users', 'standar'])) {
            return [];
        }

        return $this->db
            ->select('tugas_audit.id, tugas_audit.status, tugas_audit.created_at, standar.nama_standar, auditor.nama AS auditor_nama, auditee.nama AS auditee_nama')
            ->from($this->table)
            ->join('users AS auditor', 'auditor.id = tugas_audit.auditor_id', 'left')
            ->join('users AS auditee', 'auditee.id = tugas_audit.auditee_id', 'left')
            ->join('standar', 'standar.id = tugas_audit.standar_id', 'left')
            ->order_by('tugas_audit.id', 'ASC')
            ->get()
            ->result();
    }

    public function get_hasil_audit_with_stats()
    {
        if (!$this->tables_exist(['tugas_audit', 'users', 'standar', 'jawaban_audit'])) {
            return [];
        }

        $tugas = $this->db
            ->select('tugas_audit.id, tugas_audit.created_at, standar.nama_standar, auditor.nama AS auditor_nama, auditee.nama AS auditee_nama')
            ->from($this->table)
            ->join('users AS auditor', 'auditor.id = tugas_audit.auditor_id', 'left')
            ->join('users AS auditee', 'auditee.id = tugas_audit.auditee_id', 'left')
            ->join('standar', 'standar.id = tugas_audit.standar_id', 'left')
            ->where('tugas_audit.status', 'dinilai')
            ->order_by('tugas_audit.id', 'DESC')
            ->get()
            ->result();

        foreach ($tugas as &$t) {
            $jawaban = $this->db->select('skor')
                                ->from('jawaban_audit')
                                ->where('tugas_audit_id', $t->id)
                                ->get()
                                ->result();
            
            $total_skor = 0;
            $count = 0;
            $stats = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            foreach ($jawaban as $j) {
                if ($j->skor > 0) {
                    $total_skor += $j->skor;
                    $count++;
                    if (isset($stats[$j->skor])) {
                        $stats[$j->skor]++;
                    }
                }
            }
            $t->rata_rata = $count > 0 ? number_format($total_skor / $count, 1) : 0;
            $t->stats = $stats;
        }

        return $tugas;
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
