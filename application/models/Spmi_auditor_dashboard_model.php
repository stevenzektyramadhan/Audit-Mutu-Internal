<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_auditor_dashboard_model extends CI_Model
{
    public function dashboard($user_id)
    {
        $base = function () use ($user_id) {
            return $this->db->from('spmi_audit_assignments a')->join('spmi_audit_cycles c', 'c.id = a.cycle_id')->where('a.auditor_id', (int) $user_id)->where_in('c.state', ['configured', 'closed']);
        };
        $assignments = $base()->count_all_results();
        $submitted = $base()->join('spmi_auditee_submissions s', 's.assignment_id = a.id')->where_in('s.status', ['submitted', 'resubmitted'])->count_all_results();
        $draft_assessments = $base()->join('spmi_auditor_assessments aa', 'aa.assignment_id = a.id')->where('aa.status', 'draft')->count_all_results();
        $finalized = $base()->join('spmi_auditor_assessments aa', 'aa.assignment_id = a.id')->where('aa.status', 'finalized')->count_all_results();
        $attention_count = $this->attention_count($user_id);
        $due_soon = $this->due_count($user_id, 'BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)');
        $overdue = $this->due_count($user_id, '< CURDATE()');
        $notifications = [];
        if ($overdue > 0) $notifications[] = ['severity' => 'danger', 'title' => 'Penugasan melewati tenggat', 'detail' => 'Penugasan SPMI perlu segera ditindaklanjuti.', 'route' => 'auditor/spmi-dashboard', 'count' => $overdue];
        if ($due_soon > 0) $notifications[] = ['severity' => 'warning', 'title' => 'Tenggat penugasan mendekat', 'detail' => 'Penugasan SPMI jatuh tempo dalam tujuh hari.', 'route' => 'auditor/spmi-dashboard', 'count' => $due_soon];
        return ['assignments' => $assignments, 'submissions_submitted' => $submitted, 'assessments_draft' => $draft_assessments, 'assessments_finalized' => $finalized, 'attention_count' => $attention_count, 'due_soon' => $due_soon, 'overdue' => $overdue, 'notifications' => $notifications];
    }

    public function attention_count($user_id)
    {
        return (int) $this->db->select('COUNT(DISTINCT a.id) AS total', FALSE)->from('spmi_audit_assignments a')->join('spmi_audit_cycles c', 'c.id = a.cycle_id')->join('spmi_auditee_submissions s', 's.assignment_id = a.id')->join('spmi_auditor_assessments aa', 'aa.assignment_id = a.id AND aa.source_submission_version = s.version', 'left')->where('a.auditor_id', (int) $user_id)->where_in('c.state', ['configured', 'closed'])->where_in('s.status', ['submitted', 'resubmitted'])->group_start()->where('aa.status IS NULL', NULL, FALSE)->or_where('aa.status !=', 'finalized')->group_end()->get()->row()->total;
    }

    protected function due_count($user_id, $predicate)
    {
        return (int) $this->db->select('COUNT(*) AS total', FALSE)->from('spmi_audit_assignments a')->join('spmi_audit_cycles c', 'c.id = a.cycle_id')->where('a.auditor_id', (int) $user_id)->where_in('c.state', ['configured', 'closed'])->where('c.end_date ' . $predicate, NULL, FALSE)->get()->row()->total;
    }
}
