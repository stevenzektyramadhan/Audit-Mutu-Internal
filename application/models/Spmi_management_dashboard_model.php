<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_management_dashboard_model extends CI_Model
{
    public function dashboard()
    {
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
                'reports' => $count('spmi_reports', ['a.cycle_id IN (' . $eligible_cycles . ')' => NULL], 'spmi_reports r', [['spmi_auditor_assessments aa', 'aa.id = r.assessment_id'], ['spmi_audit_assignments a', 'a.id = aa.assignment_id']]),
            ],
            'pengendalian' => ['meetings_resolved' => $count('spmi_rtm_meetings', ['status' => 'resolved']), 'decisions' => $count('spmi_rtm_decisions', ['m.status' => 'resolved'], 'spmi_rtm_decisions d', [['spmi_rtm_meetings m', 'm.id = d.meeting_id']])],
            'peningkatan' => [
                'follow_ups_open' => $count('spmi_rtm_follow_ups', ['status' => 'open']),
                'follow_ups_in_progress' => $count('spmi_rtm_follow_ups', ['status' => 'in_progress']),
                'follow_ups_completed' => $count('spmi_rtm_follow_ups', ['status' => 'completed']),
                'follow_ups_overdue' => (int) $this->db->select('COUNT(*) AS total', FALSE)->where_in('status', ['open', 'in_progress'])->where('due_date IS NOT NULL', NULL, FALSE)->where('due_date < CURDATE()', NULL, FALSE)->get('spmi_rtm_follow_ups')->row()->total,
            ],
        ];
        return ['metrics' => $metrics, 'notifications' => $this->notifications($metrics)];
    }

    public function export_rows($year)
    {
        $dashboard = $this->dashboard();
        $rows = [];
        foreach ($dashboard['metrics'] as $stage => $items) foreach ($items as $metric => $count) $rows[] = [(int) $year, $stage, $metric, (int) $count];
        return $rows;
    }

    protected function notifications($metrics)
    {
        $notifications = [];
        $overdue = (int) $metrics['peningkatan']['follow_ups_overdue'];
        if ($overdue > 0) $notifications[] = ['severity' => 'danger', 'title' => 'Tindak lanjut melewati tenggat', 'detail' => 'Tindak lanjut RTM perlu perhatian.', 'route' => 'lpmpi/spmi-follow-ups', 'count' => $overdue];
        $drafts = (int) $metrics['pelaksanaan']['submissions_draft'];
        if ($drafts > 0) $notifications[] = ['severity' => 'warning', 'title' => 'Submission masih draft', 'detail' => 'Submission SPMI belum dikirim auditee.', 'route' => 'lpmpi/spmi-audits', 'count' => $drafts];
        $assessment_drafts = (int) $metrics['evaluasi']['assessments_draft'];
        if ($assessment_drafts > 0) $notifications[] = ['severity' => 'info', 'title' => 'Penilaian masih draft', 'detail' => 'Penilaian auditor belum difinalisasi.', 'route' => 'lpmpi/spmi-reports', 'count' => $assessment_drafts];
        return $notifications;
    }
}
