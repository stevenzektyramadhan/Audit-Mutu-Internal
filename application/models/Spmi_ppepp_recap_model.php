<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_ppepp_recap_model extends CI_Model
{
    public function recap()
    {
        $eligible_cycles = "SELECT id FROM spmi_audit_cycles WHERE state IN ('configured', 'closed')";
        $standards = (int) $this->db->select('COUNT(*) AS total', FALSE)->from('spmi_standards')->get()->row()->total;
        $indicators = (int) $this->db->select('COUNT(*) AS total', FALSE)->from('spmi_indicators')->get()->row()->total;
        $targets = (int) $this->db->select('COUNT(*) AS total', FALSE)->from('spmi_indicator_targets')->get()->row()->total;

        $cycles = (int) $this->db->select('COUNT(*) AS total', FALSE)->where_in('state', ['configured', 'closed'])->from('spmi_audit_cycles')->get()->row()->total;
        $assignments = (int) $this->db->select('COUNT(*) AS total', FALSE)->where('cycle_id IN (' . $eligible_cycles . ')', NULL, FALSE)->from('spmi_audit_assignments')->get()->row()->total;
        $submissions_draft = (int) $this->db->select('COUNT(*) AS total', FALSE)->from('spmi_auditee_submissions s')->join('spmi_audit_assignments a', 'a.id = s.assignment_id')->where('a.cycle_id IN (' . $eligible_cycles . ')', NULL, FALSE)->where('s.status', 'draft')->get()->row()->total;
        $submissions_submitted = (int) $this->db->select('COUNT(*) AS total', FALSE)->from('spmi_auditee_submissions s')->join('spmi_audit_assignments a', 'a.id = s.assignment_id')->where('a.cycle_id IN (' . $eligible_cycles . ')', NULL, FALSE)->where('s.status', 'submitted')->get()->row()->total;

        $assessments_draft = (int) $this->db->select('COUNT(*) AS total', FALSE)->from('spmi_auditor_assessments aa')->join('spmi_audit_assignments a', 'a.id = aa.assignment_id')->where('a.cycle_id IN (' . $eligible_cycles . ')', NULL, FALSE)->where('aa.status', 'draft')->get()->row()->total;
        $assessments_finalized = (int) $this->db->select('COUNT(*) AS total', FALSE)->from('spmi_auditor_assessments aa')->join('spmi_audit_assignments a', 'a.id = aa.assignment_id')->where('a.cycle_id IN (' . $eligible_cycles . ')', NULL, FALSE)->where('aa.status', 'finalized')->get()->row()->total;
        $reports = (int) $this->db->select('COUNT(*) AS total', FALSE)->from('spmi_reports r')->join('spmi_auditor_assessments aa', 'aa.id = r.assessment_id')->join('spmi_audit_assignments a', 'a.id = aa.assignment_id')->where('a.cycle_id IN (' . $eligible_cycles . ')', NULL, FALSE)->get()->row()->total;

        $meetings_resolved = (int) $this->db->select('COUNT(*) AS total', FALSE)->where('status', 'resolved')->from('spmi_rtm_meetings')->get()->row()->total;
        $decisions = (int) $this->db->select('COUNT(*) AS total', FALSE)->from('spmi_rtm_decisions d')->join('spmi_rtm_meetings m', 'm.id = d.meeting_id')->where('m.status', 'resolved')->get()->row()->total;

        $follow_ups_open = (int) $this->db->select('COUNT(*) AS total', FALSE)->where('status', 'open')->from('spmi_rtm_follow_ups')->get()->row()->total;
        $follow_ups_in_progress = (int) $this->db->select('COUNT(*) AS total', FALSE)->where('status', 'in_progress')->from('spmi_rtm_follow_ups')->get()->row()->total;
        $follow_ups_completed = (int) $this->db->select('COUNT(*) AS total', FALSE)->where('status', 'completed')->from('spmi_rtm_follow_ups')->get()->row()->total;
        $follow_ups_overdue = (int) $this->db->select('COUNT(*) AS total', FALSE)->where_in('status', ['open', 'in_progress'])->where('due_date IS NOT NULL', NULL, FALSE)->where('due_date < CURDATE()', NULL, FALSE)->from('spmi_rtm_follow_ups')->get()->row()->total;

        return [
            'penetapan' => ['standards' => $standards, 'indicators' => $indicators, 'targets' => $targets],
            'pelaksanaan' => ['cycles' => $cycles, 'assignments' => $assignments, 'submissions_draft' => $submissions_draft, 'submissions_submitted' => $submissions_submitted],
            'evaluasi' => ['assessments_draft' => $assessments_draft, 'assessments_finalized' => $assessments_finalized, 'reports' => $reports],
            'pengendalian' => ['meetings_resolved' => $meetings_resolved, 'decisions' => $decisions],
            'peningkatan' => ['follow_ups_open' => $follow_ups_open, 'follow_ups_in_progress' => $follow_ups_in_progress, 'follow_ups_completed' => $follow_ups_completed, 'follow_ups_overdue' => $follow_ups_overdue],
        ];
    }
}
