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

    public function exists_duplicate($periode_id, $standar_id, $auditor_id, $auditee_id, $exclude_id = NULL)
    {
        $this->db
            ->where('periode_id', (int) $periode_id)
            ->where('standar_id', (int) $standar_id)
            ->where('auditor_id', (int) $auditor_id)
            ->where('auditee_id', (int) $auditee_id);

        if ($exclude_id !== NULL) {
            $this->db->where('id !=', (int) $exclude_id);
        }

        return $this->db->count_all_results($this->table) > 0;
    }

    public function delete($id)
    {
        return $this->db->where('id', (int) $id)->delete($this->table);
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

    public function count_by_status_for_period($periode_id)
    {
        $counts = [
            STATUS_BELUM_DIISI => 0,
            STATUS_DIISI => 0,
            STATUS_DINILAI => 0,
        ];

        if (!$this->db->table_exists($this->table)) {
            return $counts;
        }

        $rows = $this->db
            ->select('status, COUNT(*) AS total')
            ->from($this->table)
            ->where('periode_id', (int) $periode_id)
            ->where_in('status', array_keys($counts))
            ->group_by('status')
            ->get()
            ->result();

        foreach ($rows as $row) {
            $counts[$row->status] = (int) $row->total;
        }

        return $counts;
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

    public function get_by_auditor($auditor_id, $status = NULL, $limit = NULL)
    {
        if (!$this->tables_exist(['tugas_audit', 'users', 'standar', 'jawaban_audit'])) {
            return [];
        }

        $this->db
            ->select('tugas_audit.id, tugas_audit.status, tugas_audit.created_at, tugas_audit.auditor_id, tugas_audit.auditee_id, tugas_audit.standar_id, standar.nama_standar, auditee.nama AS auditee_nama, COUNT(jawaban_audit.id) AS jumlah_pertanyaan, AVG(jawaban_audit.skor) AS rata_rata')
            ->from($this->table)
            ->join('users AS auditee', 'auditee.id = tugas_audit.auditee_id', 'left')
            ->join('standar', 'standar.id = tugas_audit.standar_id', 'left')
            ->join('jawaban_audit', 'jawaban_audit.tugas_id = tugas_audit.id', 'left')
            ->where('tugas_audit.auditor_id', (int) $auditor_id);

        if ($status !== NULL) {
            $this->db->where('tugas_audit.status', $status);
        }

        $this->db
            ->group_by('tugas_audit.id')
            ->order_by('tugas_audit.id', 'DESC');

        if ($limit !== NULL) {
            $this->db->limit((int) $limit);
        }

        return $this->db->get()->result();
    }

    public function find_for_auditor($tugas_id, $auditor_id)
    {
        if (!$this->tables_exist(['tugas_audit', 'users', 'standar'])) {
            return NULL;
        }

        return $this->db
            ->select('tugas_audit.*, standar.nama_standar, standar.deskripsi AS standar_deskripsi, auditee.nama AS auditee_nama, auditee.email AS auditee_email')
            ->from($this->table)
            ->join('users AS auditee', 'auditee.id = tugas_audit.auditee_id', 'left')
            ->join('standar', 'standar.id = tugas_audit.standar_id', 'left')
            ->where('tugas_audit.id', (int) $tugas_id)
            ->where('tugas_audit.auditor_id', (int) $auditor_id)
            ->get()
            ->row();
    }

    public function get_by_auditee($auditee_id, $status = NULL, $limit = NULL)
    {
        if (!$this->tables_exist(['tugas_audit', 'users', 'standar', 'jawaban_audit'])) {
            return [];
        }

        $this->db
            ->select('tugas_audit.id, tugas_audit.status, tugas_audit.created_at, tugas_audit.auditor_id, tugas_audit.auditee_id, tugas_audit.standar_id, standar.nama_standar, auditor.nama AS auditor_nama, COUNT(jawaban_audit.id) AS jumlah_pertanyaan, AVG(jawaban_audit.skor) AS rata_rata')
            ->from($this->table)
            ->join('users AS auditor', 'auditor.id = tugas_audit.auditor_id', 'left')
            ->join('standar', 'standar.id = tugas_audit.standar_id', 'left')
            ->join('jawaban_audit', 'jawaban_audit.tugas_id = tugas_audit.id', 'left')
            ->where('tugas_audit.auditee_id', (int) $auditee_id);

        if ($status !== NULL) {
            $this->db->where('tugas_audit.status', $status);
        }

        $this->db
            ->group_by('tugas_audit.id')
            ->order_by('tugas_audit.id', 'DESC');

        if ($limit !== NULL) {
            $this->db->limit((int) $limit);
        }

        return $this->db->get()->result();
    }

    public function find_for_auditee($tugas_id, $auditee_id)
    {
        if (!$this->tables_exist(['tugas_audit', 'users', 'standar'])) {
            return NULL;
        }

        return $this->db
            ->select('tugas_audit.*, standar.nama_standar, standar.deskripsi AS standar_deskripsi, auditor.nama AS auditor_nama, auditor.email AS auditor_email')
            ->from($this->table)
            ->join('users AS auditor', 'auditor.id = tugas_audit.auditor_id', 'left')
            ->join('standar', 'standar.id = tugas_audit.standar_id', 'left')
            ->where('tugas_audit.id', (int) $tugas_id)
            ->where('tugas_audit.auditee_id', (int) $auditee_id)
            ->get()
            ->row();
    }

    public function update_status($id, $status)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, ['status' => $status]);
    }

    public function get_all_tugas($filters = [])
    {
        if (!$this->tables_exist(['tugas_audit', 'users', 'standar'])) {
            return [];
        }

        $this->db
            ->select('tugas_audit.id, tugas_audit.periode_id, tugas_audit.standar_id, tugas_audit.auditor_id, tugas_audit.auditee_id, tugas_audit.status, tugas_audit.created_at, standar.nama_standar, auditor.nama AS auditor_nama, auditee.nama AS auditee_nama, auditee.nama_unit AS auditee_unit, periode_audit.nama_periode')
            ->from($this->table)
            ->join('users AS auditor', 'auditor.id = tugas_audit.auditor_id', 'left')
            ->join('users AS auditee', 'auditee.id = tugas_audit.auditee_id', 'left')
            ->join('standar', 'standar.id = tugas_audit.standar_id', 'left')
            ->join('periode_audit', 'periode_audit.id = tugas_audit.periode_id', 'left');

        if (!empty($filters['q'])) {
            $this->db
                ->group_start()
                    ->like('auditee.nama', $filters['q'])
                    ->or_like('auditor.nama', $filters['q'])
                    ->or_like('standar.nama_standar', $filters['q'])
                ->group_end();
        }

        if (!empty($filters['status'])) {
            $this->db->where('tugas_audit.status', $filters['status']);
        }

        if (!empty($filters['periode_id'])) {
            $this->db->where('tugas_audit.periode_id', (int) $filters['periode_id']);
        }

        if (!empty($filters['standar_id'])) {
            $this->db->where('tugas_audit.standar_id', (int) $filters['standar_id']);
        }

        if (!empty($filters['auditee_id'])) {
            $this->db->where('tugas_audit.auditee_id', (int) $filters['auditee_id']);
        }

        return $this->db->order_by('tugas_audit.id', 'DESC')->get()->result();
    }

    public function find_with_relations($id)
    {
        if (!$this->tables_exist(['tugas_audit', 'users', 'standar'])) {
            return NULL;
        }

        return $this->db
            ->select('tugas_audit.*, standar.nama_standar, standar.deskripsi AS standar_deskripsi, auditor.nama AS auditor_nama, auditor.email AS auditor_email, auditee.nama AS auditee_nama, auditee.email AS auditee_email')
            ->from($this->table)
            ->join('users AS auditor', 'auditor.id = tugas_audit.auditor_id', 'left')
            ->join('users AS auditee', 'auditee.id = tugas_audit.auditee_id', 'left')
            ->join('standar', 'standar.id = tugas_audit.standar_id', 'left')
            ->where('tugas_audit.id', (int) $id)
            ->get()
            ->row();
    }

    public function get_hasil_audit_with_stats($filters = [])
    {
        if (!$this->tables_exist(['tugas_audit', 'users', 'standar', 'jawaban_audit'])) {
            return [];
        }

        $this->db
            ->select('tugas_audit.id, tugas_audit.created_at, standar.nama_standar, auditor.nama AS auditor_nama, auditee.nama AS auditee_nama')
            ->from($this->table)
            ->join('users AS auditor', 'auditor.id = tugas_audit.auditor_id', 'left')
            ->join('users AS auditee', 'auditee.id = tugas_audit.auditee_id', 'left')
            ->join('standar', 'standar.id = tugas_audit.standar_id', 'left')
            ->where('tugas_audit.status', STATUS_DINILAI);

        if (!empty($filters['q'])) {
            $this->db
                ->group_start()
                    ->like('auditee.nama', $filters['q'])
                    ->or_like('auditor.nama', $filters['q'])
                    ->or_like('standar.nama_standar', $filters['q'])
                ->group_end();
        }

        $tugas = $this->db->order_by('tugas_audit.id', 'DESC')->get()->result();

        foreach ($tugas as &$t) {
            $jawaban = $this->db->select('skor')
                                ->from('jawaban_audit')
                                ->where('tugas_id', $t->id)
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
