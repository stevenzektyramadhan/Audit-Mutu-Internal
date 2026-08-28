<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_auditee_workspace_model extends CI_Model
{
    public function assignments($user_id, $filters = [])
    {
        $this->db->select("a.*, c.cycle_code, c.title AS cycle_title, c.state, s.id AS submission_id, COALESCE(s.status, 'draft') AS submission_status, s.version")
            ->from('spmi_audit_assignments a')
            ->join('spmi_audit_cycles c', 'c.id = a.cycle_id')
            ->join('spmi_auditee_submissions s', 's.assignment_id = a.id', 'left')
            ->where('a.auditee_id', (int) $user_id)
            ->where_in('c.state', ['configured', 'closed'])
            ->where("s.status IS NULL OR s.status IN ('draft', 'submitted', 'returned_for_revision', 'resubmitted')", NULL, FALSE);

        if (!empty($filters['cycle_id'])) {
            $this->db->where('a.cycle_id', (int) $filters['cycle_id']);
        }

        $status = isset($filters['status']) ? $filters['status'] : '';
        if ($status === 'draft') {
            $this->db->where("s.status IS NULL OR s.status = 'draft'", NULL, FALSE);
        } elseif ($status !== '') {
            $this->db->where('s.status', $status);
        }

        return $this->db->order_by('c.start_date', 'DESC')->order_by('a.id', 'ASC')->get()->result();
    }

    public function cycle_options($user_id)
    {
        return $this->db->distinct()
            ->select('c.id, c.cycle_code, c.title, c.state, c.start_date')
            ->from('spmi_audit_assignments a')
            ->join('spmi_audit_cycles c', 'c.id = a.cycle_id')
            ->join('spmi_auditee_submissions s', 's.assignment_id = a.id', 'left')
            ->where('a.auditee_id', (int) $user_id)
            ->where_in('c.state', ['configured', 'closed'])
            ->where("s.status IS NULL OR s.status IN ('draft', 'submitted', 'returned_for_revision', 'resubmitted')", NULL, FALSE)
            ->order_by('c.start_date', 'DESC')
            ->order_by('c.id', 'ASC')
            ->get()->result();
    }

    public function attention_count($user_id)
    {
        return (int) $this->db->select('COUNT(DISTINCT a.id) AS total', FALSE)
            ->from('spmi_audit_assignments a')
            ->join('spmi_audit_cycles c', 'c.id = a.cycle_id')
            ->join('spmi_auditee_submissions s', 's.assignment_id = a.id', 'left')
            ->where('a.auditee_id', (int) $user_id)
            ->where_in('c.state', ['configured', 'closed'])
            ->where("(s.status IS NULL OR s.status IN ('draft', 'returned_for_revision'))", NULL, FALSE)
            ->get()->row()->total;
    }

    public function final_result_for_auditee($assignment_id, $user_id) { $report = $this->db->query('SELECT r.* FROM spmi_reports r JOIN spmi_auditor_assessments aa ON r.assessment_id = aa.id JOIN spmi_audit_assignments a ON aa.assignment_id = a.id WHERE r.assessment_id = aa.id AND aa.assignment_id = a.id AND a.id = ? AND a.auditee_id = ?', [(int) $assignment_id, (int) $user_id])->row(); if (!$report) return NULL; return ['report' => $report, 'report_items' => $this->db->where('report_id', (int) $report->id)->order_by('display_order', 'ASC')->get('spmi_report_items')->result()]; }
    public function assignment($assignment_id, $user_id, $for_update = FALSE) { $sql = 'SELECT a.*, c.cycle_code, c.title AS cycle_title, c.state, s.id AS submission_id, s.status AS submission_status, s.version, s.submitted_at FROM spmi_audit_assignments a JOIN spmi_audit_cycles c ON c.id = a.cycle_id LEFT JOIN spmi_auditee_submissions s ON s.assignment_id = a.id WHERE a.id = ? AND a.auditee_id = ?'; if ($for_update) $sql .= ' FOR UPDATE'; return $this->db->query($sql, [(int) $assignment_id, (int) $user_id])->row(); }
    public function confirmation_assignment($assignment_id, $user_id) { return $this->db->query('SELECT a.*, c.cycle_code, c.title AS cycle_title, c.state, s.id AS submission_id, s.status AS submission_status, s.version, s.submitted_at FROM spmi_audit_assignments a JOIN spmi_audit_cycles c ON c.id = a.cycle_id LEFT JOIN spmi_auditee_submissions s ON s.assignment_id = a.id WHERE a.id = ? AND a.auditee_id = ?', [(int) $assignment_id, (int) $user_id])->row(); }
    public function ensure_submission($assignment_id) { $submission = $this->db->where('assignment_id', (int) $assignment_id)->get('spmi_auditee_submissions')->row(); if ($submission) return $submission->id; if (!$this->db->insert('spmi_auditee_submissions', ['assignment_id' => (int) $assignment_id])) return 0; $submission_id = (int) $this->db->insert_id(); foreach ($this->db->select('id')->where('assignment_id', (int) $assignment_id)->get('spmi_audit_assignment_items')->result() as $item) if (!$this->db->insert('spmi_auditee_submission_items', ['submission_id' => $submission_id, 'assignment_item_id' => (int) $item->id, 'realization' => ''])) return 0; return $submission_id; }
    public function items($submission_id) { return $this->db->select('si.*, si.evidence_url, ai.display_order, ai.question_code, ai.question_text, ai.evidence_instruction, ai.evidence_policy')->from('spmi_auditee_submission_items si')->join('spmi_audit_assignment_items ai', 'ai.id = si.assignment_item_id')->where('si.submission_id', (int) $submission_id)->order_by('ai.display_order', 'ASC')->get()->result(); }
    public function assignment_items($assignment_id) { return $this->db->select("ai.*, ai.id AS assignment_item_id, '' AS realization, NULL AS evidence_url")->from('spmi_audit_assignment_items ai')->where('ai.assignment_id', (int) $assignment_id)->order_by('ai.display_order', 'ASC')->get()->result(); }
    public function evidence($submission_item_id) { return $this->db->where('submission_item_id', (int) $submission_item_id)->order_by('id', 'ASC')->get('spmi_auditee_evidence')->result(); }
    public function lock_evidence_count($submission_item_id) { return count($this->db->query('SELECT id FROM spmi_auditee_evidence WHERE submission_item_id = ? FOR UPDATE', [(int) $submission_item_id])->result()); }
    public function item_for_update($item_id, $user_id, $version) { return $this->db->query('SELECT si.*, s.id AS submission_id, s.version, s.status, a.id AS assignment_id, a.auditee_id, c.state FROM spmi_auditee_submission_items si JOIN spmi_auditee_submissions s ON s.id = si.submission_id JOIN spmi_audit_assignments a ON a.id = s.assignment_id JOIN spmi_audit_cycles c ON c.id = a.cycle_id WHERE si.assignment_item_id = ? AND a.auditee_id = ? AND s.version = ? AND s.status IN (?, ?) FOR UPDATE', [(int) $item_id, (int) $user_id, (int) $version, 'draft', 'returned_for_revision'])->row(); }
    public function evidence_for_update($evidence_id, $user_id, $version) { return $this->db->query('SELECT e.*, s.id AS submission_id, s.version, s.status, a.id AS assignment_id, a.auditee_id, c.state FROM spmi_auditee_evidence e JOIN spmi_auditee_submission_items si ON si.id = e.submission_item_id JOIN spmi_auditee_submissions s ON s.id = si.submission_id JOIN spmi_audit_assignments a ON a.id = s.assignment_id JOIN spmi_audit_cycles c ON c.id = a.cycle_id WHERE e.id = ? AND a.auditee_id = ? AND s.version = ? AND s.status IN (?, ?) FOR UPDATE', [(int) $evidence_id, (int) $user_id, (int) $version, 'draft', 'returned_for_revision'])->row(); }
    public function evidence_for_read($evidence_id, $user_id) { return $this->db->select('e.*, c.state')->from('spmi_auditee_evidence e')->join('spmi_auditee_submission_items si', 'si.id = e.submission_item_id')->join('spmi_auditee_submissions s', 's.id = si.submission_id')->join('spmi_audit_assignments a', 'a.id = s.assignment_id')->join('spmi_audit_cycles c', 'c.id = a.cycle_id')->where('e.id', (int) $evidence_id)->where('a.auditee_id', (int) $user_id)->where_in('c.state', ['configured', 'closed'])->get()->row(); }
    public function assignment_id_for_item($item_id, $user_id) { return $this->db->select('a.id')->from('spmi_audit_assignment_items ai')->join('spmi_audit_assignments a', 'a.id = ai.assignment_id')->where('ai.id', (int) $item_id)->where('a.auditee_id', (int) $user_id)->get()->row(); }
    public function assignment_id_for_evidence($evidence_id, $user_id) { return $this->db->select('a.id')->from('spmi_auditee_evidence e')->join('spmi_auditee_submission_items si', 'si.id = e.submission_item_id')->join('spmi_auditee_submissions s', 's.id = si.submission_id')->join('spmi_audit_assignments a', 'a.id = s.assignment_id')->where('e.id', (int) $evidence_id)->where('a.auditee_id', (int) $user_id)->get()->row(); }
    public function update_realization($submission_id, $item_id, $realization, $evidence_url) { return $this->db->where(['submission_id' => (int) $submission_id, 'assignment_item_id' => (int) $item_id])->update('spmi_auditee_submission_items', ['realization' => $realization, 'evidence_url' => $evidence_url === '' ? NULL : $evidence_url]); }
    public function update_version($submission_id, $version, $status = 'draft', $submitted = FALSE, $from_statuses = ['draft']) { $data = ['version' => (int) $version + 1, 'status' => $status]; if ($submitted) $data['submitted_at'] = date('Y-m-d H:i:s'); return $this->db->where(['id' => (int) $submission_id, 'version' => (int) $version])->where_in('status', $from_statuses)->update('spmi_auditee_submissions', $data); }
    public function add_revision_event($data) { return $this->db->insert('spmi_auditee_submission_revision_events', $data); }
    public function revision_history($assignment_id, $user_id) { return $this->db->select('e.*, u.nama AS actor_name, u.email AS actor_email')->from('spmi_auditee_submission_revision_events e')->join('spmi_audit_assignments a', 'a.id = e.assignment_id')->join('users u', 'u.id = e.actor_user_id')->where('e.assignment_id', (int) $assignment_id)->where('a.auditee_id', (int) $user_id)->order_by('e.created_at', 'ASC')->order_by('e.id', 'ASC')->get()->result(); }
    public function add_evidence($data) { return $this->db->insert('spmi_auditee_evidence', $data); }
    public function delete_evidence($id) { return $this->db->where('id', (int) $id)->delete('spmi_auditee_evidence'); }
    public function add_drive_trash_outbox($data) { return $this->db->insert('spmi_drive_trash_outbox', $data); }
    public function count_evidence($item_id) { return (int) $this->db->where('submission_item_id', (int) $item_id)->count_all_results('spmi_auditee_evidence'); }
}
