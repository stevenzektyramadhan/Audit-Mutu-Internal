<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_reports_model extends CI_Model
{
    public function index_options($filters = NULL)
    {
        $filters = is_array($filters) ? $filters : ['academic_year' => '', 'cycle_id' => 0, 'version_id' => 0, 'auditee_id' => 0, 'q' => '', 'page' => 1];
        $options_query = function ($select, $group_by, $order_by, $positive_id_column = NULL) use ($filters) {
            $query = $this->db->select($select, FALSE)->from('spmi_reports r')->where_in('r.report_scope', ['version', 'version_auditee']);
            if ($filters['academic_year'] !== '') $query->where('r.academic_year_snapshot', $filters['academic_year']);
            if ($filters['cycle_id']) $query->where('r.source_cycle_id', (int) $filters['cycle_id']);
            if ($filters['version_id']) $query->where('r.source_version_id', (int) $filters['version_id']);
            if ($positive_id_column !== NULL) $query->where($positive_id_column . ' >', 0);
            return $query->group_by($group_by)->order_by($order_by[0], $order_by[1])->get()->result();
        };
        return [
            'academic_years' => $this->db->select('academic_year_snapshot', FALSE)->from('spmi_reports')->where_in('report_scope', ['version', 'version_auditee'])->where('academic_year_snapshot IS NOT NULL', NULL, FALSE)->where("TRIM(academic_year_snapshot) != ''", NULL, FALSE)->group_by('academic_year_snapshot')->order_by('academic_year_snapshot', 'DESC')->get()->result(),
            'cycles' => $options_query('source_cycle_id, cycle_code_snapshot, cycle_title_snapshot', 'source_cycle_id, cycle_code_snapshot, cycle_title_snapshot', ['cycle_code_snapshot', 'DESC']),
            'versions' => $options_query('source_version_id, source_version_code_snapshot, source_version_title_snapshot', 'source_version_id, source_version_code_snapshot, source_version_title_snapshot', ['source_version_code_snapshot', 'DESC'], 'r.source_version_id'),
            'auditees' => $options_query('auditee_id_snapshot, auditee_name_snapshot', 'auditee_id_snapshot, auditee_name_snapshot', ['auditee_name_snapshot', 'ASC'], 'r.auditee_id_snapshot'),
        ];
    }

    public function index_count($filters)
    {
        $query = $this->db->select('COUNT(DISTINCT r.id) AS report_count', FALSE)->from('spmi_reports r')->join('spmi_report_items ri', 'ri.report_id = r.id', 'left');
        $this->apply_index_filters($query, $filters);
        return (int) $query->get()->row()->report_count;
    }

    public function index_reports($filters, $limit, $offset)
    {
        $query = $this->db->select('r.*, AVG(ri.score) AS average_score, COUNT(ri.id) AS item_count, COUNT(DISTINCT CONCAT(COALESCE(ri.source_standard_code_snapshot, r.source_standard_code_snapshot), "\\n", COALESCE(ri.source_standard_title_snapshot, r.source_standard_title_snapshot))) AS standard_count, COALESCE(GROUP_CONCAT(DISTINCT NULLIF(TRIM(ri.auditor_name_snapshot), "") ORDER BY ri.auditor_name_snapshot SEPARATOR ", "), r.auditor_name_snapshot) AS contributor_names, SUM(CASE WHEN ri.finding_snapshot IS NOT NULL AND TRIM(ri.finding_snapshot) != "" THEN 1 ELSE 0 END) AS finding_count', FALSE)->from('spmi_reports r')->join('spmi_report_items ri', 'ri.report_id = r.id', 'left');
        $this->apply_index_filters($query, $filters);
        return $query->group_by('r.id')->order_by('r.generated_at', 'DESC')->order_by('r.id', 'DESC')->limit((int) $limit, (int) $offset)->get()->result();
    }

    public function index_summary($filters)
    {
        $query = $this->db->select('COUNT(DISTINCT r.id) AS report_count, COUNT(DISTINCT r.auditee_id_snapshot) AS auditee_count, COUNT(DISTINCT CONCAT(COALESCE(ri.source_standard_code_snapshot, r.source_standard_code_snapshot), "\\n", COALESCE(ri.source_standard_title_snapshot, r.source_standard_title_snapshot))) AS standard_count, AVG(ri.score) AS average_score', FALSE)->from('spmi_reports r')->join('spmi_report_items ri', 'ri.report_id = r.id', 'left');
        $this->apply_index_filters($query, $filters);
        return $query->get()->row();
    }

    public function index_standard_analysis($filters)
    {
        $query = $this->db->select('COALESCE(ri.source_standard_code_snapshot, r.source_standard_code_snapshot) AS standard_code, COALESCE(ri.source_standard_title_snapshot, r.source_standard_title_snapshot) AS standard_title, AVG(ri.score) AS average_score, COUNT(ri.id) AS indicator_count, SUM(CASE WHEN ri.finding_snapshot IS NOT NULL AND TRIM(ri.finding_snapshot) != "" THEN 1 ELSE 0 END) AS finding_count', FALSE)->from('spmi_reports r')->join('spmi_report_items ri', 'ri.report_id = r.id');
        $this->apply_index_filters($query, $filters);
        return $query->group_by('COALESCE(ri.source_standard_code_snapshot, r.source_standard_code_snapshot), COALESCE(ri.source_standard_title_snapshot, r.source_standard_title_snapshot)', FALSE)->order_by('standard_code', 'ASC')->order_by('standard_title', 'ASC')->get()->result();
    }

    protected function apply_index_filters($query, $filters)
    {
        $query->where_in('r.report_scope', ['version', 'version_auditee']);
        if ($filters['academic_year'] !== '') $query->where('r.academic_year_snapshot', $filters['academic_year']);
        if ($filters['cycle_id']) $query->where('r.source_cycle_id', (int) $filters['cycle_id']);
        if ($filters['version_id']) $query->where('r.source_version_id', (int) $filters['version_id']);
        if ($filters['auditee_id']) $query->where('r.auditee_id_snapshot', (int) $filters['auditee_id']);
        if ($filters['q'] !== '') {
            $query->group_start()->like('r.report_number', $filters['q'])->or_like('r.cycle_code_snapshot', $filters['q'])->or_like('r.cycle_title_snapshot', $filters['q'])->or_like('r.source_version_code_snapshot', $filters['q'])->or_like('r.source_version_title_snapshot', $filters['q'])->or_like('r.auditor_name_snapshot', $filters['q'])->or_like('r.auditee_name_snapshot', $filters['q'])->or_like('ri.auditor_name_snapshot', $filters['q'])->or_like('ri.auditor_email_snapshot', $filters['q'])->or_like('ri.source_standard_code_snapshot', $filters['q'])->or_like('ri.source_standard_title_snapshot', $filters['q'])->or_like('ri.indicator_code_snapshot', $filters['q'])->or_like('ri.indicator_title_snapshot', $filters['q'])->group_end();
        }
        return $query;
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
        return $this->db->query("SELECT MAX(CASE WHEN s.id IS NOT NULL THEN aa.id END) AS anchor_assessment_id, a.cycle_id, a.source_version_id, a.auditee_id, c.cycle_code, c.title AS cycle_title, a.source_version_code, a.source_version_title, a.auditee_name, COUNT(DISTINCT a.id) AS standard_count, MAX(aa.finalized_at) AS finalized_at FROM spmi_audit_assignments a JOIN spmi_audit_cycles c ON c.id = a.cycle_id LEFT JOIN spmi_auditor_assessments aa ON aa.assignment_id = a.id AND aa.status = 'finalized' AND aa.finalized_at IS NOT NULL LEFT JOIN spmi_auditee_submissions s ON s.assignment_id = a.id AND s.version = aa.source_submission_version AND s.status IN ('submitted', 'resubmitted') LEFT JOIN spmi_reports r ON r.source_cycle_id = a.cycle_id AND r.source_version_id = a.source_version_id AND r.auditee_id_snapshot = a.auditee_id AND r.report_scope = 'version_auditee' WHERE c.state IN ('configured', 'closed') AND a.source_version_id IS NOT NULL AND r.id IS NULL GROUP BY a.cycle_id, a.source_version_id, a.auditee_id, c.cycle_code, c.title, a.source_version_code, a.source_version_title, a.auditee_name HAVING COUNT(DISTINCT a.id) = COUNT(DISTINCT CASE WHEN s.id IS NOT NULL THEN a.id END) ORDER BY finalized_at DESC")->result();
    }

    public function report_by_id($id)
    {
        return $this->db->where('id', (int) $id)->get('spmi_reports')->row();
    }

    public function report_items($report_id)
    {
        return $this->db->where('report_id', (int) $report_id)->order_by('display_order', 'ASC')->get('spmi_report_items')->result();
    }

    public function version_auditee_report_for_update($cycle_id, $version_id, $auditee_id)
    {
        return $this->db->query("SELECT * FROM spmi_reports WHERE source_cycle_id = ? AND source_version_id = ? AND auditee_id_snapshot = ? AND report_scope = 'version_auditee' FOR UPDATE", [(int) $cycle_id, (int) $version_id, (int) $auditee_id])->row();
    }

    public function report_for_assessment_for_update($assessment_id)
    {
        return $this->db->query('SELECT * FROM spmi_reports WHERE assessment_id = ? FOR UPDATE', [(int) $assessment_id])->row();
    }

    public function assessment_for_update($assessment_id)
    {
        return $this->db->query('SELECT aa.*, a.cycle_id, a.source_version_id, a.source_version_code, a.source_version_title, a.source_standard_id, a.source_standard_code, a.source_standard_title, a.auditor_id, a.auditee_id, a.auditor_name, a.auditee_name, c.cycle_code, c.title AS cycle_title, c.start_date AS cycle_start_date, c.end_date AS cycle_end_date FROM spmi_auditor_assessments aa JOIN spmi_audit_assignments a ON a.id = aa.assignment_id JOIN spmi_auditee_submissions s ON s.assignment_id = a.id AND s.version = aa.source_submission_version JOIN spmi_audit_cycles c ON c.id = a.cycle_id WHERE aa.id = ? FOR UPDATE', [(int) $assessment_id])->row();
    }

    public function assignments_for_version_auditee_report_for_update($cycle_id, $version_id, $auditee_id)
    {
        return $this->db->query('SELECT a.*, u.email AS auditor_email, s.display_order AS source_standard_display_order FROM spmi_audit_assignments a JOIN spmi_standards s ON s.id = a.source_standard_id JOIN users u ON u.id = a.auditor_id WHERE a.cycle_id = ? AND a.source_version_id = ? AND a.auditee_id = ? ORDER BY s.display_order ASC, a.auditor_id ASC FOR UPDATE', [(int) $cycle_id, (int) $version_id, (int) $auditee_id])->result();
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
