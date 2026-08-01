<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Legacy_ami_archive_model extends CI_Model
{
    private $archive_tables = ['legacy_ami_archive_runs', 'legacy_ami_archive_tasks', 'legacy_ami_archive_answers', 'legacy_ami_archive_issues', 'legacy_ami_archive_user_units'];
    private $legacy_tables = ['tugas_audit', 'jawaban_audit', 'standar', 'pertanyaan', 'periode_audit', 'users'];

    public function preflight()
    {
        $legacy_ready = $this->tables_exist($this->legacy_tables);
        $archive_ready = $this->tables_exist($this->archive_tables);
        return [
            'legacy_ready' => $legacy_ready,
            'archive_ready' => $archive_ready,
            'legacy_counts' => $this->counts($this->legacy_tables),
            'archive_counts' => $this->counts($this->archive_tables),
            'gaps' => $legacy_ready ? $this->legacy_gaps() : [],
        ];
    }

    public function runs()
    {
        if (!$this->tables_exist(['legacy_ami_archive_runs'])) return [];
        return $this->db->order_by('created_at', 'DESC')->order_by('id', 'DESC')->get('legacy_ami_archive_runs')->result();
    }

    public function run($id)
    {
        if (!$this->tables_exist(['legacy_ami_archive_runs'])) return NULL;
        return $this->db->where('id', (int) $id)->get('legacy_ami_archive_runs')->row();
    }

    public function tasks($run_id)
    {
        if (!$this->tables_exist(['legacy_ami_archive_tasks', 'legacy_ami_archive_answers'])) return [];
        return $this->db
            ->select('legacy_ami_archive_tasks.*, COUNT(legacy_ami_archive_answers.id) AS answer_count', FALSE)
            ->from('legacy_ami_archive_tasks')
            ->join('legacy_ami_archive_answers', 'legacy_ami_archive_answers.archive_task_id = legacy_ami_archive_tasks.id', 'left')
            ->where('legacy_ami_archive_tasks.run_id', (int) $run_id)
            ->group_by('legacy_ami_archive_tasks.id')
            ->order_by('legacy_ami_archive_tasks.legacy_tugas_id', 'ASC')
            ->get()
            ->result();
    }

    public function task($id)
    {
        if (!$this->tables_exist(['legacy_ami_archive_tasks'])) return NULL;
        return $this->db->where('id', (int) $id)->get('legacy_ami_archive_tasks')->row();
    }

    public function answers($task_id)
    {
        if (!$this->tables_exist(['legacy_ami_archive_answers'])) return [];
        return $this->db->where('archive_task_id', (int) $task_id)->order_by('question_order_snapshot', 'ASC')->order_by('id', 'ASC')->get('legacy_ami_archive_answers')->result();
    }

    public function issues($run_id = 0)
    {
        if (!$this->tables_exist(['legacy_ami_archive_issues'])) return [];
        if ((int) $run_id > 0) $this->db->where('run_id', (int) $run_id);
        return $this->db->order_by('severity', 'DESC')->order_by('id', 'ASC')->get('legacy_ami_archive_issues')->result();
    }

    private function counts($tables)
    {
        $counts = [];
        foreach ($tables as $table) $counts[$table] = $this->db->table_exists($table) ? (int) $this->db->count_all_results($table) : NULL;
        return $counts;
    }

    private function legacy_gaps()
    {
        $gaps = [];
        $checks = [
            'missing_standard' => 'SELECT COUNT(*) AS total FROM tugas_audit t LEFT JOIN standar s ON s.id = t.standar_id WHERE s.id IS NULL',
            'missing_period' => 'SELECT COUNT(*) AS total FROM tugas_audit t LEFT JOIN periode_audit p ON p.id = t.periode_id WHERE t.periode_id IS NOT NULL AND p.id IS NULL',
            'missing_auditor' => 'SELECT COUNT(*) AS total FROM tugas_audit t LEFT JOIN users u ON u.id = t.auditor_id WHERE u.id IS NULL',
            'missing_auditee' => 'SELECT COUNT(*) AS total FROM tugas_audit t LEFT JOIN users u ON u.id = t.auditee_id WHERE u.id IS NULL',
            'orphan_answer' => 'SELECT COUNT(*) AS total FROM jawaban_audit j LEFT JOIN tugas_audit t ON t.id = j.tugas_id WHERE t.id IS NULL',
            'missing_question' => 'SELECT COUNT(*) AS total FROM jawaban_audit j LEFT JOIN pertanyaan p ON p.id = j.pertanyaan_id WHERE p.id IS NULL',
        ];
        foreach ($checks as $key => $sql) $gaps[$key] = (int) $this->db->query($sql)->row()->total;
        return $gaps;
    }

    private function tables_exist($tables)
    {
        foreach ($tables as $table) if (!$this->db->table_exists($table)) return FALSE;
        return TRUE;
    }
}
