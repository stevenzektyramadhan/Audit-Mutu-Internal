<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_reports_model extends CI_Model
{
    public function reports()
    {
        return $this->db->order_by('generated_at', 'DESC')->order_by('id', 'DESC')->get('spmi_reports')->result();
    }

    public function finalized_assessments()
    {
        return $this->db->select('aa.id, c.cycle_code, c.title AS cycle_title, a.auditee_name, aa.finalized_at')
            ->from('spmi_auditor_assessments aa')->join('spmi_audit_assignments a', 'a.id = aa.assignment_id')
            ->join('spmi_auditee_submissions s', 's.assignment_id = a.id AND aa.source_submission_version = s.version')
            ->join('spmi_audit_cycles c', 'c.id = a.cycle_id')->join('spmi_reports r', 'r.assessment_id = aa.id', 'left')
            ->where('aa.status', 'finalized')->where('r.id IS NULL', NULL, FALSE)
            ->where_in('c.state', ['configured', 'closed'])->order_by('aa.finalized_at', 'DESC')->get()->result();
    }

    public function report_by_id($id)
    {
        return $this->db->where('id', (int) $id)->get('spmi_reports')->row();
    }

    public function report_items($report_id)
    {
        return $this->db->where('report_id', (int) $report_id)->order_by('display_order', 'ASC')->get('spmi_report_items')->result();
    }

    public function report_for_assessment_for_update($assessment_id)
    {
        return $this->db->query('SELECT * FROM spmi_reports WHERE assessment_id = ? FOR UPDATE', [(int) $assessment_id])->row();
    }

    public function assessment_for_update($assessment_id)
    {
        return $this->db->query('SELECT aa.*, a.cycle_id, a.source_version_code, a.source_version_title, a.source_standard_code, a.source_standard_title, a.source_package_code, a.source_package_title, a.auditor_name, a.auditee_name, c.cycle_code, c.title AS cycle_title, c.start_date AS cycle_start_date, c.end_date AS cycle_end_date FROM spmi_auditor_assessments aa JOIN spmi_audit_assignments a ON a.id = aa.assignment_id JOIN spmi_auditee_submissions s ON s.assignment_id = a.id AND s.version = aa.source_submission_version JOIN spmi_audit_cycles c ON c.id = a.cycle_id WHERE aa.id = ? FOR UPDATE', [(int) $assessment_id])->row();
    }

    public function cycle_for_update($cycle_id)
    {
        return $this->db->query('SELECT * FROM spmi_audit_cycles WHERE id = ? FOR UPDATE', [(int) $cycle_id])->row();
    }

    public function submission_for_update($assignment_id, $source_submission_version)
    {
        return $this->db->query('SELECT * FROM spmi_auditee_submissions WHERE assignment_id = ? AND version = ? AND status IN (?, ?) FOR UPDATE', [(int) $assignment_id, (int) $source_submission_version, 'submitted', 'resubmitted'])->row();
    }

    public function submission_items_for_report($assessment_id, $submission_id)
    {
        return $this->db->query('SELECT ai.*, i.display_order, i.question_code, i.question_text, i.indicator_code, i.indicator_title, si.realization AS realization_snapshot, si.evidence_url AS evidence_url_snapshot, e.original_name AS evidence_file_original_name_snapshot, e.mime_type AS evidence_file_mime_type_snapshot, e.size_bytes AS evidence_file_size_bytes_snapshot, e.sha256 AS evidence_file_sha256_snapshot FROM spmi_auditor_assessment_items ai JOIN spmi_audit_assignment_items i ON i.id = ai.assignment_item_id JOIN spmi_auditee_submission_items si ON si.assignment_item_id = ai.assignment_item_id AND si.submission_id = ? LEFT JOIN spmi_auditee_evidence e ON e.id = (SELECT MIN(e2.id) FROM spmi_auditee_evidence e2 WHERE e2.submission_item_id = si.id) WHERE ai.assessment_id = ? ORDER BY i.display_order ASC, e.id ASC FOR UPDATE', [(int) $submission_id, (int) $assessment_id])->result();
    }

    public function assignment_items_count($assignment_id)
    {
        return (int) $this->db->where('assignment_id', (int) $assignment_id)->count_all_results('spmi_audit_assignment_items');
    }

    public function rubric($assignment_item_id, $score)
    {
        return $this->db->where(['assignment_item_id' => (int) $assignment_item_id, 'score' => (int) $score])->get('spmi_audit_assignment_item_rubrics')->row();
    }

    public function insert_report($data)
    {
        return $this->db->insert('spmi_reports', $data) ? (int) $this->db->insert_id() : 0;
    }

    public function insert_item($data)
    {
        return $this->db->insert('spmi_report_items', $data);
    }
}
