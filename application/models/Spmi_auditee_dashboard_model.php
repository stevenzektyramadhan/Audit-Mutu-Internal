<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_auditee_dashboard_model extends CI_Model
{
    public function dashboard($user_id)
    {
        $base = function () use ($user_id) {
            return $this->db->from('spmi_audit_assignments a')->join('spmi_audit_cycles c', 'c.id = a.cycle_id')->where('a.auditee_id', (int) $user_id)->where_in('c.state', ['configured', 'closed']);
        };
        $assignments = $base()->count_all_results();
        $draft = $base()->join('spmi_auditee_submissions s', 's.assignment_id = a.id')->where('s.status', 'draft')->count_all_results();
        $submitted = $base()->join('spmi_auditee_submissions s', 's.assignment_id = a.id')->where('s.status', 'submitted')->count_all_results();
        $attention_count = $this->attention_count($user_id);
        $due_soon = $this->due_count($user_id, 'BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)');
        $overdue = $this->due_count($user_id, '< CURDATE()');
        $notifications = [];
        if ($overdue > 0) $notifications[] = ['severity' => 'danger', 'title' => 'Penugasan melewati tenggat', 'detail' => 'Submission SPMI perlu segera ditindaklanjuti.', 'route' => 'auditee/spmi-dashboard', 'count' => $overdue];
        if ($due_soon > 0) $notifications[] = ['severity' => 'warning', 'title' => 'Tenggat submission mendekat', 'detail' => 'Submission SPMI jatuh tempo dalam tujuh hari.', 'route' => 'auditee/spmi-dashboard', 'count' => $due_soon];
        return ['assignments' => $assignments, 'submissions_draft' => $draft, 'submissions_submitted' => $submitted, 'attention_count' => $attention_count, 'due_soon' => $due_soon, 'overdue' => $overdue, 'notifications' => $notifications];
    }

    public function attention_count($user_id)
    {
        return (int) $this->db->select('COUNT(DISTINCT a.id) AS total', FALSE)->from('spmi_audit_assignments a')->join('spmi_audit_cycles c', 'c.id = a.cycle_id')->join('spmi_auditee_submissions s', 's.assignment_id = a.id', 'left')->where('a.auditee_id', (int) $user_id)->where_in('c.state', ['configured', 'closed'])->where("(s.status IS NULL OR s.status IN ('draft', 'returned_for_revision'))", NULL, FALSE)->get()->row()->total;
    }

    protected function due_count($user_id, $predicate)
    {
        return (int) $this->db->select('COUNT(*) AS total', FALSE)->from('spmi_audit_assignments a')->join('spmi_audit_cycles c', 'c.id = a.cycle_id')->where('a.auditee_id', (int) $user_id)->where_in('c.state', ['configured', 'closed'])->where('c.end_date ' . $predicate, NULL, FALSE)->get()->row()->total;
    }
}
