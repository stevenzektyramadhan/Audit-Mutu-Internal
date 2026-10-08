<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_management_dashboard_model extends CI_Model
{
    public function dashboard($year)
    {
        $this->load->model('User_model');
        $this->load->model('Spmi_ppepp_documents_model');
        $this->config->load('spmi_ppepp', TRUE);
        $eligible_cycles = "SELECT id FROM spmi_audit_cycles WHERE state IN ('configured', 'closed')";
        $count = function ($table, $where = [], $from = NULL, $joins = []) {
            $query = $this->db->select('COUNT(*) AS total', FALSE)->from($from ?: $table);
            foreach ($joins as $join) $query->join($join[0], $join[1], isset($join[2]) ? $join[2] : 'inner');
            foreach ($where as $key => $value) $value === NULL ? $query->where($key, NULL, FALSE) : $query->where($key, $value);
            return (int) $query->get()->row()->total;
        };
        $metrics = [
            'penetapan' => ['standards' => $count('spmi_standards'), 'indicators' => $count('spmi_indicators'), 'targets' => $count('spmi_indicator_targets')],
            'pelaksanaan' => [
                'cycles' => $count('spmi_audit_cycles', ['state IN (\'configured\', \'closed\')' => NULL]),
                'assignments' => $count('spmi_audit_assignments', ['cycle_id IN (' . $eligible_cycles . ')' => NULL]),
                'submissions_draft' => $count('spmi_auditee_submissions', ['a.cycle_id IN (' . $eligible_cycles . ')' => NULL, 's.status' => 'draft'], 'spmi_auditee_submissions s', [['spmi_audit_assignments a', 'a.id = s.assignment_id']]),
                'submissions_submitted' => $count('spmi_auditee_submissions', ['a.cycle_id IN (' . $eligible_cycles . ')' => NULL, 's.status' => 'submitted'], 'spmi_auditee_submissions s', [['spmi_audit_assignments a', 'a.id = s.assignment_id']]),
            ],
            'evaluasi' => [
                'assessments_draft' => $count('spmi_auditor_assessments', ['a.cycle_id IN (' . $eligible_cycles . ')' => NULL, 'aa.status' => 'draft'], 'spmi_auditor_assessments aa', [['spmi_audit_assignments a', 'a.id = aa.assignment_id']]),
                'assessments_finalized' => $count('spmi_auditor_assessments', ['a.cycle_id IN (' . $eligible_cycles . ')' => NULL, 'aa.status' => 'finalized'], 'spmi_auditor_assessments aa', [['spmi_audit_assignments a', 'a.id = aa.assignment_id']]),
                'reports' => $count('spmi_reports', ['r.source_cycle_id IN (' . $eligible_cycles . ')' => NULL], 'spmi_reports r'),
            ],
            'pengendalian' => ['meetings_resolved' => $count('spmi_rtm_meetings', ['status' => 'resolved']), 'decisions' => $count('spmi_rtm_decisions', ['m.status' => 'resolved'], 'spmi_rtm_decisions d', [['spmi_rtm_meetings m', 'm.id = d.meeting_id']])],
        ];
        $stages = $this->config->item('spmi_ppepp_stages', 'spmi_ppepp');
        $categories = $this->config->item('spmi_ppepp_categories', 'spmi_ppepp');
        $counts = [];
        foreach ($this->Spmi_ppepp_documents_model->dashboard_counts($year, array_keys($stages)) as $row) {
            $counts[$row->stage][$row->category] = (int) $row->total;
        }
        $document_summary = [];
        foreach ($stages as $stage => $label) {
            $document_summary[$stage] = ['label' => $label, 'categories' => []];
            foreach ($categories[$stage] as $key => $category_label) {
                $document_summary[$stage]['categories'][] = ['key' => $key, 'label' => $category_label, 'count' => isset($counts[$stage][$key]) ? $counts[$stage][$key] : 0];
            }
        }
        return [
            'selected_year' => (int) $year,
            'years' => $this->Spmi_ppepp_documents_model->years(),
            'document_summary' => $document_summary,
            'metrics' => $metrics,
            'notifications' => $this->notifications($metrics),
            'account_totals' => [
                'total_user' => $this->User_model->count_all(),
                'total_auditor' => $this->User_model->count_by_role('auditor'),
                'total_auditee' => $this->User_model->count_by_role('auditee'),
            ],
        ];
    }

    public function export_rows($year)
    {
        $dashboard = $this->dashboard($year);
        $rows = [];
        foreach ($dashboard['document_summary'] as $stage => $summary) foreach ($summary['categories'] as $category) $rows[] = [(int) $year, $summary['label'], $category['label'], (int) $category['count']];
        foreach ($dashboard['metrics']['evaluasi'] as $metric => $count) $rows[] = [(int) $year, 'Evaluasi', ucwords(str_replace('_', ' ', $metric)), (int) $count];
        return $rows;
    }

    protected function notifications($metrics)
    {
        $notifications = [];
        $drafts = (int) $metrics['pelaksanaan']['submissions_draft'];
        if ($drafts > 0) $notifications[] = ['severity' => 'warning', 'title' => 'Submission masih draft', 'detail' => 'Submission SPMI belum dikirim auditee.', 'route' => 'lpmpi/spmi-audits', 'count' => $drafts];
        $assessment_drafts = (int) $metrics['evaluasi']['assessments_draft'];
        if ($assessment_drafts > 0) $notifications[] = ['severity' => 'info', 'title' => 'Penilaian masih draft', 'detail' => 'Penilaian auditor belum difinalisasi.', 'route' => 'lpmpi/spmi-reports', 'count' => $assessment_drafts];
        return $notifications;
    }
}
