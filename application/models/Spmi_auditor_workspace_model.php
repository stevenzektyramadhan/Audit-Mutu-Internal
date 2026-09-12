<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_auditor_workspace_model extends CI_Model
{
    public function assignments($user_id, $filters)
    {
        $this->db->select('a.id, a.cycle_id, a.source_package_code, a.source_package_title, c.cycle_code, c.title AS cycle_title, c.state, s.status AS submission_status, aa.status AS assessment_status')
            ->from('spmi_audit_assignments a')
            ->join('spmi_audit_cycles c', 'c.id = a.cycle_id')
            ->join('spmi_auditee_submissions s', 's.assignment_id = a.id')
            ->join('spmi_auditor_assessments aa', 'aa.assignment_id = a.id AND aa.source_submission_version = s.version', 'left')
            ->where('a.auditor_id', (int) $user_id)
            ->where_in('c.state', ['configured', 'closed'])
            ->group_start()
                ->where('c.state !=', 'closed')
                ->or_where('aa.id IS NOT NULL', NULL, FALSE)
            ->group_end()
            ->where_in('s.status', ['submitted', 'resubmitted', 'returned_for_revision']);

        if (!empty($filters['cycle_id'])) {
            $this->db->where('a.cycle_id', (int) $filters['cycle_id']);
        }

        if (!empty($filters['status'])) {
            $this->db->where('s.status', $filters['status']);
        }

        return $this->db->order_by('c.start_date', 'DESC')->order_by('a.id', 'ASC')->get()->result();
    }

    public function cycle_options($user_id)
    {
        return $this->db->distinct()
            ->select('c.id, c.cycle_code, c.title, c.state, c.start_date')
            ->from('spmi_audit_assignments a')
            ->join('spmi_audit_cycles c', 'c.id = a.cycle_id')
            ->join('spmi_auditee_submissions s', 's.assignment_id = a.id')
            ->join('spmi_auditor_assessments aa', 'aa.assignment_id = a.id AND aa.source_submission_version = s.version', 'left')
            ->where('a.auditor_id', (int) $user_id)
            ->where_in('c.state', ['configured', 'closed'])
            ->group_start()
                ->where('c.state !=', 'closed')
                ->or_where('aa.id IS NOT NULL', NULL, FALSE)
            ->group_end()
            ->where_in('s.status', ['submitted', 'resubmitted', 'returned_for_revision'])
            ->order_by('c.start_date', 'DESC')
            ->order_by('c.id', 'ASC')
            ->get()->result();
    }

    public function attention_count($user_id)
    {
        return (int) $this->db->select('COUNT(DISTINCT a.id) AS total', FALSE)
            ->from('spmi_audit_assignments a')
            ->join('spmi_audit_cycles c', 'c.id = a.cycle_id')
            ->join('spmi_auditee_submissions s', 's.assignment_id = a.id')
            ->join('spmi_auditor_assessments aa', 'aa.assignment_id = a.id AND aa.source_submission_version = s.version', 'left')
            ->where('a.auditor_id', (int) $user_id)
            ->where_in('c.state', ['configured', 'closed'])
            ->group_start()
                ->where('c.state !=', 'closed')
                ->or_where('aa.id IS NOT NULL', NULL, FALSE)
            ->group_end()
            ->where_in('s.status', ['submitted', 'resubmitted'])
            ->group_start()
                ->where('aa.status IS NULL', NULL, FALSE)
                ->or_where('aa.status !=', 'finalized')
            ->group_end()
            ->get()->row()->total;
    }

    public function assignment($assignment_id, $user_id, $for_update = FALSE)
    {
        $sql = 'SELECT a.*, c.cycle_code, c.title AS cycle_title, c.state, s.id AS submission_id, s.status AS submission_status, s.version AS submission_version, aa.id AS assessment_id, aa.status AS assessment_status, aa.version AS assessment_version, aa.source_submission_version FROM spmi_audit_assignments a JOIN spmi_audit_cycles c ON c.id = a.cycle_id JOIN spmi_auditee_submissions s ON s.assignment_id = a.id LEFT JOIN spmi_auditor_assessments aa ON aa.assignment_id = a.id AND aa.source_submission_version = s.version WHERE a.id = ? AND a.auditor_id = ? AND s.status IN (?, ?, ?) AND c.state IN (?, ?)';
        if ($for_update) $sql .= ' FOR UPDATE';
        return $this->db->query($sql, [(int) $assignment_id, (int) $user_id, 'submitted', 'resubmitted', 'returned_for_revision', 'configured', 'closed'])->row();
    }

    public function cycle_for_update($cycle_id) { return $this->db->query('SELECT * FROM spmi_audit_cycles WHERE id = ? FOR UPDATE', [(int) $cycle_id])->row(); }
    public function submission_for_update($assignment_id) { return $this->db->query('SELECT * FROM spmi_auditee_submissions WHERE assignment_id = ? AND status IN (?, ?, ?) FOR UPDATE', [(int) $assignment_id, 'submitted', 'resubmitted', 'returned_for_revision'])->row(); }
    public function assessment_for_update($assignment_id, $user_id, $source_submission_version) { return $this->db->query('SELECT aa.* FROM spmi_auditor_assessments aa JOIN spmi_audit_assignments a ON a.id = aa.assignment_id WHERE aa.assignment_id = ? AND a.auditor_id = ? AND aa.source_submission_version = ? FOR UPDATE', [(int) $assignment_id, (int) $user_id, (int) $source_submission_version])->row(); }
    public function assessment_items_for_update($assessment_id, $user_id) { return $this->db->query('SELECT ai.* FROM spmi_auditor_assessment_items ai JOIN spmi_auditor_assessments aa ON aa.id = ai.assessment_id JOIN spmi_audit_assignments a ON a.id = aa.assignment_id WHERE ai.assessment_id = ? AND a.auditor_id = ? ORDER BY ai.id ASC FOR UPDATE', [(int) $assessment_id, (int) $user_id])->result(); }
    public function submitted_items($assignment_id, $user_id) { return $this->db->query('SELECT si.assignment_item_id, si.realization FROM spmi_auditee_submission_items si JOIN spmi_auditee_submissions s ON s.id = si.submission_id JOIN spmi_audit_assignments a ON a.id = s.assignment_id WHERE s.assignment_id = ? AND a.auditor_id = ? AND s.status IN (?, ?) ORDER BY si.assignment_item_id ASC', [(int) $assignment_id, (int) $user_id, 'submitted', 'resubmitted'])->result(); }
    public function assignment_items($assignment_id, $user_id) { return $this->db->select('i.*, si.evidence_url')->from('spmi_audit_assignment_items i')->join('spmi_audit_assignments a', 'a.id = i.assignment_id')->join('spmi_auditee_submissions s', 's.assignment_id = a.id')->join('spmi_auditee_submission_items si', 'si.submission_id = s.id AND si.assignment_item_id = i.id')->where('a.id', (int) $assignment_id)->where('a.auditor_id', (int) $user_id)->where_in('s.status', ['submitted', 'resubmitted', 'returned_for_revision'])->order_by('i.display_order', 'ASC')->get()->result(); }
    public function rubrics($item_id, $user_id) { return $this->db->select('r.*')->from('spmi_audit_assignment_item_rubrics r')->join('spmi_audit_assignment_items i', 'i.id = r.assignment_item_id')->join('spmi_audit_assignments a', 'a.id = i.assignment_id')->where('r.assignment_item_id', (int) $item_id)->where('a.auditor_id', (int) $user_id)->order_by('r.score', 'ASC')->get()->result(); }
    public function evidence($assignment_item_id, $user_id) { return $this->db->select('e.*')->from('spmi_auditee_evidence e')->join('spmi_auditee_submission_items si', 'si.id = e.submission_item_id')->join('spmi_auditee_submissions s', 's.id = si.submission_id')->join('spmi_audit_assignments a', 'a.id = s.assignment_id')->where('si.assignment_item_id', (int) $assignment_item_id)->where('a.auditor_id', (int) $user_id)->where_in('s.status', ['submitted', 'resubmitted', 'returned_for_revision'])->get()->result(); }
    public function assessment($assignment_id, $user_id, $source_submission_version) { return $this->db->select('aa.*')->from('spmi_auditor_assessments aa')->join('spmi_audit_assignments a', 'a.id = aa.assignment_id')->where('aa.assignment_id', (int) $assignment_id)->where('a.auditor_id', (int) $user_id)->where('aa.source_submission_version', (int) $source_submission_version)->get()->row(); }
    public function assessment_items($assessment_id, $user_id) { return $this->db->select('ai.*')->from('spmi_auditor_assessment_items ai')->join('spmi_auditor_assessments aa', 'aa.id = ai.assessment_id')->join('spmi_audit_assignments a', 'a.id = aa.assignment_id')->where('ai.assessment_id', (int) $assessment_id)->where('a.auditor_id', (int) $user_id)->order_by('ai.id', 'ASC')->get()->result(); }
    public function auditor_evidence($assessment_item_id, $user_id) { return $this->db->select('e.*')->from('spmi_auditor_assessment_evidence e')->join('spmi_auditor_assessment_items ai', 'ai.id = e.assessment_item_id')->join('spmi_auditor_assessments aa', 'aa.id = ai.assessment_id')->join('spmi_audit_assignments a', 'a.id = aa.assignment_id')->where('e.assessment_item_id', (int) $assessment_item_id)->where('a.auditor_id', (int) $user_id)->order_by('e.id', 'ASC')->get()->result(); }
    public function lock_auditor_evidence_count($assessment_item_id) { return count($this->db->query('SELECT id FROM spmi_auditor_assessment_evidence WHERE assessment_item_id = ? FOR UPDATE', [(int) $assessment_item_id])->result()); }
    public function assessment_item_for_update($assessment_item_id, $user_id, $version) { return $this->db->query('SELECT ai.*, aa.id AS assessment_id, aa.version, aa.status AS assessment_status, aa.source_submission_version, a.id AS assignment_id, c.state, s.status AS submission_status, s.version AS submission_version FROM spmi_auditor_assessment_items ai JOIN spmi_auditor_assessments aa ON aa.id = ai.assessment_id JOIN spmi_audit_assignments a ON a.id = aa.assignment_id JOIN spmi_audit_cycles c ON c.id = a.cycle_id JOIN spmi_auditee_submissions s ON s.assignment_id = a.id AND s.version = aa.source_submission_version WHERE ai.id = ? AND a.auditor_id = ? AND aa.version = ? AND s.status IN (?, ?) FOR UPDATE', [(int) $assessment_item_id, (int) $user_id, (int) $version, 'submitted', 'resubmitted'])->row(); }
    public function auditor_evidence_for_update($evidence_id, $user_id, $version) { return $this->db->query('SELECT e.*, ai.assessment_id, aa.version, aa.status AS assessment_status, aa.source_submission_version, a.id AS assignment_id, c.state, s.status AS submission_status, s.version AS submission_version FROM spmi_auditor_assessment_evidence e JOIN spmi_auditor_assessment_items ai ON ai.id = e.assessment_item_id JOIN spmi_auditor_assessments aa ON aa.id = ai.assessment_id JOIN spmi_audit_assignments a ON a.id = aa.assignment_id JOIN spmi_audit_cycles c ON c.id = a.cycle_id JOIN spmi_auditee_submissions s ON s.assignment_id = a.id AND s.version = aa.source_submission_version WHERE e.id = ? AND a.auditor_id = ? AND aa.version = ? AND s.status IN (?, ?) FOR UPDATE', [(int) $evidence_id, (int) $user_id, (int) $version, 'submitted', 'resubmitted'])->row(); }
    public function auditor_evidence_for_read($evidence_id, $user_id) { return $this->db->select('e.*')->from('spmi_auditor_assessment_evidence e')->join('spmi_auditor_assessment_items ai', 'ai.id = e.assessment_item_id')->join('spmi_auditor_assessments aa', 'aa.id = ai.assessment_id')->join('spmi_audit_assignments a', 'a.id = aa.assignment_id')->join('spmi_audit_cycles c', 'c.id = a.cycle_id')->join('spmi_auditee_submissions s', 's.assignment_id = a.id AND s.version = aa.source_submission_version')->where('e.id', (int) $evidence_id)->where('a.auditor_id', (int) $user_id)->where_in('c.state', ['configured', 'closed'])->where_in('s.status', ['submitted', 'resubmitted'])->get()->row(); }
    public function assessment_item_autosave_for_update($assessment_item_id, $user_id) { return $this->db->query('SELECT ai.*, aa.id AS assessment_id, aa.version, aa.status AS assessment_status, aa.source_submission_version, a.id AS assignment_id, c.state, s.status AS submission_status, s.version AS submission_version FROM spmi_auditor_assessment_items ai JOIN spmi_auditor_assessments aa ON aa.id = ai.assessment_id JOIN spmi_audit_assignments a ON a.id = aa.assignment_id JOIN spmi_audit_cycles c ON c.id = a.cycle_id JOIN spmi_auditee_submissions s ON s.assignment_id = a.id AND s.version = aa.source_submission_version WHERE ai.id = ? AND a.auditor_id = ? AND s.status IN (?, ?) FOR UPDATE', [(int) $assessment_item_id, (int) $user_id, 'submitted', 'resubmitted'])->row(); }
    public function add_auditor_evidence($data) { return $this->db->insert('spmi_auditor_assessment_evidence', $data); }
    public function delete_auditor_evidence($id) { return $this->db->where('id', (int) $id)->delete('spmi_auditor_assessment_evidence'); }
    public function add_drive_trash_outbox($data) { return $this->db->insert('spmi_drive_trash_outbox', $data); }
    public function assignment_id_for_assessment_item($assessment_item_id, $user_id) { return $this->db->select('a.id')->from('spmi_auditor_assessment_items ai')->join('spmi_auditor_assessments aa', 'aa.id = ai.assessment_id')->join('spmi_audit_assignments a', 'a.id = aa.assignment_id')->where('ai.id', (int) $assessment_item_id)->where('a.auditor_id', (int) $user_id)->get()->row(); }
    public function assignment_id_for_auditor_evidence($evidence_id, $user_id) { return $this->db->select('a.id')->from('spmi_auditor_assessment_evidence e')->join('spmi_auditor_assessment_items ai', 'ai.id = e.assessment_item_id')->join('spmi_auditor_assessments aa', 'aa.id = ai.assessment_id')->join('spmi_audit_assignments a', 'a.id = aa.assignment_id')->where('e.id', (int) $evidence_id)->where('a.auditor_id', (int) $user_id)->get()->row(); }
    public function create_assessment($assignment_id, $source_submission_version) { return $this->db->insert('spmi_auditor_assessments', ['assignment_id' => (int) $assignment_id, 'source_submission_version' => (int) $source_submission_version]) ? (int) $this->db->insert_id() : 0; }
    public function create_item($data) { return $this->db->insert('spmi_auditor_assessment_items', $data); }
    public function update_item($id, $data) { return $this->db->where('id', (int) $id)->update('spmi_auditor_assessment_items', $data); }
    public function update_assessment($id, $version, $status = 'draft') { $data = ['version' => (int) $version + 1, 'status' => $status]; if ($status === 'finalized') $data['finalized_at'] = date('Y-m-d H:i:s'); return $this->db->where(['id' => (int) $id, 'version' => (int) $version, 'status' => 'draft'])->update('spmi_auditor_assessments', $data); }
    public function update_submission_status($submission_id, $version, $from_statuses, $status) { return $this->db->where(['id' => (int) $submission_id, 'version' => (int) $version])->where_in('status', $from_statuses)->update('spmi_auditee_submissions', ['version' => (int) $version + 1, 'status' => $status]); }
    public function add_revision_event($data) { return $this->db->insert('spmi_auditee_submission_revision_events', $data); }
    public function revision_history($assignment_id, $user_id) { return $this->db->select('e.*, u.nama AS actor_name, u.email AS actor_email')->from('spmi_auditee_submission_revision_events e')->join('spmi_audit_assignments a', 'a.id = e.assignment_id')->join('users u', 'u.id = e.actor_user_id')->where('e.assignment_id', (int) $assignment_id)->where('a.auditor_id', (int) $user_id)->order_by('e.created_at', 'ASC')->order_by('e.id', 'ASC')->get()->result(); }
    public function evidence_for_read($evidence_id, $user_id) { return $this->db->select('e.*')->from('spmi_auditee_evidence e')->join('spmi_auditee_submission_items si', 'si.id = e.submission_item_id')->join('spmi_auditee_submissions s', 's.id = si.submission_id')->join('spmi_audit_assignments a', 'a.id = s.assignment_id')->join('spmi_audit_cycles c', 'c.id = a.cycle_id')->where('e.id', (int) $evidence_id)->where('a.auditor_id', (int) $user_id)->where_in('s.status', ['submitted', 'resubmitted', 'returned_for_revision'])->where_in('c.state', ['configured', 'closed'])->get()->row(); }
}
