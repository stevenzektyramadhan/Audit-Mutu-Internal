<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_rtm_model extends CI_Model
{
    public function meetings() { return $this->db->order_by('meeting_date', 'DESC')->order_by('id', 'DESC')->get('spmi_rtm_meetings')->result(); }
    public function meeting($id, $for_update = FALSE) { return $for_update ? $this->db->query('SELECT * FROM spmi_rtm_meetings WHERE id = ? FOR UPDATE', [(int) $id])->row() : $this->db->where('id', (int) $id)->get('spmi_rtm_meetings')->row(); }
    public function reports() { return $this->db->order_by('generated_at', 'DESC')->order_by('id', 'DESC')->get('spmi_reports')->result(); }
    public function report_for_update($id) { return $this->db->query('SELECT * FROM spmi_reports WHERE id = ? FOR UPDATE', [(int) $id])->row(); }
    public function report_item_for_update($id) { return $this->db->query('SELECT * FROM spmi_report_items WHERE id = ? FOR UPDATE', [(int) $id])->row(); }
    public function users_for_update($ids) { if (!$ids) return []; return $this->db->query('SELECT id, nama, email, role FROM users WHERE id IN (' . implode(',', array_map('intval', $ids)) . ') ORDER BY nama ASC FOR UPDATE')->result(); }
    public function users() { return $this->db->where_in('role', ['super_admin', 'admin_lpmpi', 'auditor', 'auditee'])->order_by('nama', 'ASC')->get('users')->result(); }
    public function meeting_reports($meeting_id) { return $this->db->select('mr.*, r.report_number, r.cycle_code_snapshot, r.cycle_title_snapshot')->from('spmi_rtm_meeting_reports mr')->join('spmi_reports r', 'r.id = mr.report_id')->where('mr.meeting_id', (int) $meeting_id)->order_by('mr.id', 'ASC')->get()->result(); }
    public function participants($meeting_id) { return $this->db->where('meeting_id', (int) $meeting_id)->order_by('name_snapshot', 'ASC')->get('spmi_rtm_participants')->result(); }
    public function decisions($meeting_id) { return $this->db->where('meeting_id', (int) $meeting_id)->order_by('display_order', 'ASC')->get('spmi_rtm_decisions')->result(); }
    public function insert_meeting($data) { return $this->db->insert('spmi_rtm_meetings', $data) ? (int) $this->db->insert_id() : 0; }
    public function update_meeting($id, $data) { return $this->db->where('id', (int) $id)->update('spmi_rtm_meetings', $data); }
    public function insert_report_link($data) { return $this->db->insert('spmi_rtm_meeting_reports', $data); }
    public function insert_participant($data) { return $this->db->insert('spmi_rtm_participants', $data); }
    public function insert_decision($data) { return $this->db->insert('spmi_rtm_decisions', $data); }
    public function delete_children($meeting_id) { return $this->db->where('meeting_id', (int) $meeting_id)->delete('spmi_rtm_meeting_reports') && $this->db->where('meeting_id', (int) $meeting_id)->delete('spmi_rtm_participants') && $this->db->where('meeting_id', (int) $meeting_id)->delete('spmi_rtm_decisions'); }
}
