<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_audits_model extends CI_Model
{
    public function cycles() { return $this->db->order_by('start_date', 'DESC')->order_by('id', 'DESC')->get('spmi_audit_cycles')->result(); }
    public function cycle($id, $for_update = FALSE) { return $for_update ? $this->db->query('SELECT * FROM spmi_audit_cycles WHERE id = ' . (int) $id . ' FOR UPDATE')->row() : $this->db->where('id', (int) $id)->get('spmi_audit_cycles')->row(); }
    public function cycle_by_code($code, $exclude = 0) { $this->db->where('cycle_code', $code); if ($exclude) $this->db->where('id !=', (int) $exclude); return $this->db->get('spmi_audit_cycles')->row(); }
    public function insert_cycle($data) { return $this->db->insert('spmi_audit_cycles', $data) ? $this->db->insert_id() : 0; }
    public function update_cycle($id, $data) { return $this->db->where('id', (int) $id)->update('spmi_audit_cycles', $data); }
    public function assignments($cycle_id) { return $this->db->where('cycle_id', (int) $cycle_id)->order_by('id', 'ASC')->get('spmi_audit_assignments')->result(); }
    public function assignment($id) { return $this->db->where('id', (int) $id)->get('spmi_audit_assignments')->row(); }
    public function assignment_by_tuple($cycle_id, $standard_id, $auditor_id, $auditee_id) { return $this->db->where(['cycle_id' => (int) $cycle_id, 'source_standard_id' => (int) $standard_id, 'auditor_id' => (int) $auditor_id, 'auditee_id' => (int) $auditee_id])->get('spmi_audit_assignments')->row(); }
    public function insert_assignment($data) { return $this->db->insert('spmi_audit_assignments', $data) ? $this->db->insert_id() : 0; }
    public function insert_item($data) { return $this->db->insert('spmi_audit_assignment_items', $data) ? $this->db->insert_id() : 0; }
    public function insert_rubric($data) { return $this->db->insert('spmi_audit_assignment_item_rubrics', $data); }
    public function items($assignment_id) { return $this->db->where('assignment_id', (int) $assignment_id)->order_by('display_order', 'ASC')->get('spmi_audit_assignment_items')->result(); }
    public function rubrics($item_id) { return $this->db->where('assignment_item_id', (int) $item_id)->order_by('score', 'ASC')->get('spmi_audit_assignment_item_rubrics')->result(); }
    public function assignment_workspace_descendant_exists($assignment_id) { foreach (['spmi_auditee_submissions', 'spmi_auditor_assessments', 'spmi_auditee_submission_revision_events'] as $table) { if ($this->db->select('1', FALSE)->where('assignment_id', (int) $assignment_id)->limit(1)->get($table)->num_rows() > 0) return TRUE; } return FALSE; }
    public function delete_assignment_children($id) { $items = $this->items($id); foreach ($items as $item) { if (!$this->db->where('assignment_item_id', (int) $item->id)->delete('spmi_audit_assignment_item_rubrics')) return FALSE; } return $this->db->where('assignment_id', (int) $id)->delete('spmi_audit_assignment_items'); }
    public function delete_assignment($id) { return $this->db->where('id', (int) $id)->delete('spmi_audit_assignments'); }
    public function standards() { return $this->db->select('s.*, v.version_code, v.title AS version_title, v.status AS version_status')->from('spmi_standards s')->join('spmi_versions v', 'v.id = s.version_id')->order_by('v.id', 'DESC')->order_by('s.display_order', 'ASC')->get()->result(); }
    public function standard_for_update($id) { return $this->db->query('SELECT s.*, v.version_code, v.title AS version_title, v.status AS version_status FROM spmi_standards s JOIN spmi_versions v ON v.id = s.version_id WHERE s.id = ' . (int) $id . ' FOR UPDATE')->row(); }
    public function version_for_update($id) { return $this->db->query('SELECT * FROM spmi_versions WHERE id = ' . (int) $id . ' FOR UPDATE')->row(); }
    public function standard_indicators($standard_id) { return $this->db->where('standard_id', (int) $standard_id)->order_by('indicator_code', 'ASC')->get('spmi_indicators')->result(); }
    public function users_by_role($role) { return $this->db->where('role', $role)->order_by('nama', 'ASC')->get('users')->result(); }
    public function user($id) { return $this->db->where('id', (int) $id)->get('users')->row(); }
}
