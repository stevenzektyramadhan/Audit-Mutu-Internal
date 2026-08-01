<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_rtm_follow_ups_model extends CI_Model
{
    public function follow_ups() { return $this->db->order_by('f.status', 'ASC')->order_by('f.due_date', 'ASC')->order_by('f.id', 'DESC')->get('spmi_rtm_follow_ups f')->result(); }
    public function follow_up($id, $for_update = FALSE) { return $for_update ? $this->db->query('SELECT * FROM spmi_rtm_follow_ups WHERE id = ? FOR UPDATE', [(int) $id])->row() : $this->db->where('id', (int) $id)->get('spmi_rtm_follow_ups')->row(); }
    public function detail($id) { return $this->db->select('f.*, d.display_order, m.meeting_code, m.meeting_title')->from('spmi_rtm_follow_ups f')->join('spmi_rtm_decisions d', 'd.id = f.decision_id')->join('spmi_rtm_meetings m', 'm.id = d.meeting_id')->where('f.id', (int) $id)->get()->row(); }
    public function resolved_decisions() { return $this->db->select('d.*, m.meeting_code, m.meeting_title')->from('spmi_rtm_decisions d')->join('spmi_rtm_meetings m', 'm.id = d.meeting_id')->where('m.status', 'resolved')->where('NOT EXISTS (SELECT 1 FROM spmi_rtm_follow_ups f WHERE f.decision_id = d.id)', NULL, FALSE)->order_by('m.meeting_date', 'DESC')->order_by('d.display_order', 'ASC')->get()->result(); }
    public function users() { return $this->db->where_in('role', ['super_admin', 'admin_lpmpi', 'auditor', 'auditee'])->order_by('nama', 'ASC')->get('users')->result(); }
    public function resolved_decision_for_update($id) { return $this->db->query('SELECT d.*, m.status AS meeting_status FROM spmi_rtm_decisions d INNER JOIN spmi_rtm_meetings m ON m.id = d.meeting_id WHERE d.id = ? FOR UPDATE', [(int) $id])->row(); }
    public function follow_up_by_decision_for_update($decision_id) { return $this->db->query('SELECT * FROM spmi_rtm_follow_ups WHERE decision_id = ? FOR UPDATE', [(int) $decision_id])->row(); }
    public function user_for_update($id) { return $this->db->query('SELECT id, nama, email, role FROM users WHERE id = ? FOR UPDATE', [(int) $id])->row(); }
    public function insert_follow_up($data) { return $this->db->insert('spmi_rtm_follow_ups', $data) ? (int) $this->db->insert_id() : 0; }
    public function update_follow_up($id, $data) { return $this->db->where('id', (int) $id)->update('spmi_rtm_follow_ups', $data); }
}
