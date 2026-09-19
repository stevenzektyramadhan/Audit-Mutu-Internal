<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_reports_model extends CI_Model
{
    public function reports()
    {
        return $this->db->order_by('generated_at', 'DESC')->order_by('id', 'DESC')->get('spmi_reports')->result();
    }

    public function report_cycles()
    {
        return $this->db->select('cycle_code_snapshot, cycle_title_snapshot', FALSE)
            ->from('spmi_reports')
            ->group_by('cycle_code_snapshot, cycle_title_snapshot')
            ->order_by('cycle_code_snapshot', 'DESC')
            ->get()->result();
    }

    public function version_report_cycles()
    {
        return $this->db->select('source_cycle_id, cycle_code_snapshot, cycle_title_snapshot', FALSE)
            ->from('spmi_reports')->where('report_scope', 'version')->where('source_cycle_id IS NOT NULL', NULL, FALSE)
            ->group_by('source_cycle_id, cycle_code_snapshot, cycle_title_snapshot')
            ->order_by('cycle_code_snapshot', 'DESC')->order_by('source_cycle_id', 'DESC')->get()->result();
    }

    public function version_reports_for_cycle($cycle_id)
    {
        return $this->db->select('id, report_number, source_version_code_snapshot, source_version_title_snapshot, auditor_name_snapshot, auditee_name_snapshot, assessment_finalized_at_snapshot')
            ->from('spmi_reports')->where('source_cycle_id', (int) $cycle_id)->where('report_scope', 'version')
            ->where('source_version_id IS NOT NULL', NULL, FALSE)->where('auditor_id_snapshot IS NOT NULL', NULL, FALSE)->where('auditee_id_snapshot IS NOT NULL', NULL, FALSE)
            ->order_by('source_version_code_snapshot', 'ASC')->order_by('auditee_name_snapshot', 'ASC')->order_by('auditor_name_snapshot', 'ASC')->order_by('id', 'ASC')->get()->result();
    }

    public function version_report_for_cycle($cycle_id, $report_id)
    {
        return $this->db->where('id', (int) $report_id)->where('source_cycle_id', (int) $cycle_id)->where('report_scope', 'version')->get('spmi_reports')->row();
    }

    public function score_recap_per_indicator_per_auditee($cycle_code)
    {
        return $this->db
            ->select('r.auditee_name_snapshot AS auditee_name, COALESCE(ri.source_standard_code_snapshot, r.source_standard_code_snapshot) AS standard_code, ri.indicator_code_snapshot AS item_code, ri.indicator_title_snapshot AS item_title, ri.score AS score', FALSE)
            ->from('spmi_reports r')
            ->join('spmi_report_items ri', 'ri.report_id = r.id')
            ->where('r.cycle_code_snapshot', $cycle_code)
            ->order_by('r.auditee_name_snapshot', 'ASC')
            ->order_by('COALESCE(ri.source_standard_code_snapshot, r.source_standard_code_snapshot)', 'ASC', FALSE)
            ->order_by('ri.display_order', 'ASC')
            ->get()->result();
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

    public function finalized_versions()
    {
        return $this->db->query("SELECT MAX(CASE WHEN s.id IS NOT NULL THEN aa.id END) AS anchor_assessment_id, a.cycle_id, a.source_version_id, a.auditor_id, a.auditee_id, c.cycle_code, c.title AS cycle_title, a.source_version_code, a.source_version_title, a.auditor_name, a.auditee_name, COUNT(DISTINCT a.id) AS standard_count, MAX(aa.finalized_at) AS finalized_at FROM spmi_audit_assignments a JOIN spmi_audit_cycles c ON c.id = a.cycle_id LEFT JOIN spmi_auditor_assessments aa ON aa.assignment_id = a.id AND aa.status = 'finalized' AND aa.finalized_at IS NOT NULL LEFT JOIN spmi_auditee_submissions s ON s.assignment_id = a.id AND s.version = aa.source_submission_version AND s.status IN ('submitted', 'resubmitted') LEFT JOIN spmi_reports r ON r.source_cycle_id = a.cycle_id AND r.source_version_id = a.source_version_id AND r.auditor_id_snapshot = a.auditor_id AND r.auditee_id_snapshot = a.auditee_id AND r.report_scope = 'version' WHERE c.state IN ('configured', 'closed') AND a.source_version_id IS NOT NULL AND r.id IS NULL GROUP BY a.cycle_id, a.source_version_id, a.auditor_id, a.auditee_id, c.cycle_code, c.title, a.source_version_code, a.source_version_title, a.auditor_name, a.auditee_name HAVING COUNT(DISTINCT a.id) = COUNT(DISTINCT CASE WHEN s.id IS NOT NULL THEN a.id END) ORDER BY finalized_at DESC")->result();
    }

    public function report_by_id($id)
    {
        return $this->db->where('id', (int) $id)->get('spmi_reports')->row();
    }

    public function report_items($report_id)
    {
        return $this->db->where('report_id', (int) $report_id)->order_by('display_order', 'ASC')->get('spmi_report_items')->result();
    }

    public function version_report_for_update($cycle_id, $version_id, $auditor_id, $auditee_id)
    {
        return $this->db->query("SELECT * FROM spmi_reports WHERE source_cycle_id = ? AND source_version_id = ? AND auditor_id_snapshot = ? AND auditee_id_snapshot = ? AND report_scope = 'version' FOR UPDATE", [(int) $cycle_id, (int) $version_id, (int) $auditor_id, (int) $auditee_id])->row();
    }

    public function report_for_assessment_for_update($assessment_id)
    {
        return $this->db->query('SELECT * FROM spmi_reports WHERE assessment_id = ? FOR UPDATE', [(int) $assessment_id])->row();
    }

    public function assessment_for_update($assessment_id)
    {
        return $this->db->query('SELECT aa.*, a.cycle_id, a.source_version_id, a.source_version_code, a.source_version_title, a.source_standard_id, a.source_standard_code, a.source_standard_title, a.auditor_id, a.auditee_id, a.auditor_name, a.auditee_name, c.cycle_code, c.title AS cycle_title, c.start_date AS cycle_start_date, c.end_date AS cycle_end_date FROM spmi_auditor_assessments aa JOIN spmi_audit_assignments a ON a.id = aa.assignment_id JOIN spmi_auditee_submissions s ON s.assignment_id = a.id AND s.version = aa.source_submission_version JOIN spmi_audit_cycles c ON c.id = a.cycle_id WHERE aa.id = ? FOR UPDATE', [(int) $assessment_id])->row();
    }

    public function assignments_for_version_report_for_update($cycle_id, $version_id, $auditor_id, $auditee_id)
    {
        return $this->db->query('SELECT a.*, s.display_order AS source_standard_display_order FROM spmi_audit_assignments a JOIN spmi_standards s ON s.id = a.source_standard_id WHERE a.cycle_id = ? AND a.source_version_id = ? AND a.auditor_id = ? AND a.auditee_id = ? ORDER BY s.display_order ASC FOR UPDATE', [(int) $cycle_id, (int) $version_id, (int) $auditor_id, (int) $auditee_id])->result();
    }

    public function finalized_assessment_for_assignment_for_update($assignment_id)
    {
        return $this->db->query("SELECT aa.* FROM spmi_auditor_assessments aa JOIN spmi_auditee_submissions s ON s.assignment_id = aa.assignment_id AND s.version = aa.source_submission_version AND s.status IN ('submitted', 'resubmitted') WHERE aa.assignment_id = ? AND aa.status = 'finalized' AND aa.finalized_at IS NOT NULL ORDER BY aa.finalized_at DESC, aa.id DESC LIMIT 1 FOR UPDATE", [(int) $assignment_id])->row();
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
        return $this->db->query('SELECT ai.*, i.display_order, i.indicator_code, i.indicator_title, si.realization AS realization_snapshot, si.evidence_url AS evidence_url_snapshot, e.original_name AS evidence_file_original_name_snapshot, e.mime_type AS evidence_file_mime_type_snapshot, e.size_bytes AS evidence_file_size_bytes_snapshot, e.sha256 AS evidence_file_sha256_snapshot FROM spmi_auditor_assessment_items ai JOIN spmi_audit_assignment_items i ON i.id = ai.assignment_item_id JOIN spmi_auditee_submission_items si ON si.assignment_item_id = ai.assignment_item_id AND si.submission_id = ? LEFT JOIN spmi_auditee_evidence e ON e.id = (SELECT MIN(e2.id) FROM spmi_auditee_evidence e2 WHERE e2.submission_item_id = si.id) WHERE ai.assessment_id = ? ORDER BY i.display_order ASC, e.id ASC FOR UPDATE', [(int) $submission_id, (int) $assessment_id])->result();
    }
    public function auditor_evidence_for_report($assessment_item_id)
    {
        return $this->db->query('SELECT original_name, mime_type, size_bytes, sha256 FROM spmi_auditor_assessment_evidence WHERE assessment_item_id = ? ORDER BY id ASC FOR UPDATE', [(int) $assessment_item_id])->result();
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
