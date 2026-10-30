<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_indicators_model extends CI_Model
{
    public function get_standards()
    {
        return $this->db->select('s.*, v.version_code, v.title AS version_title, v.status AS version_status')->from('spmi_standards s')->join('spmi_versions v', 'v.id = s.version_id')->order_by('v.id', 'DESC')->order_by('s.display_order', 'ASC')->get()->result();
    }

    public function find_standard($id, $for_update = FALSE) { return $for_update ? $this->db->query('SELECT * FROM spmi_standards WHERE id = ' . (int) $id . ' FOR UPDATE')->row() : $this->db->where('id', (int) $id)->get('spmi_standards')->row(); }
    public function find_version($id, $for_update = FALSE) { return $for_update ? $this->db->query('SELECT * FROM spmi_versions WHERE id = ' . (int) $id . ' FOR UPDATE')->row() : $this->db->where('id', (int) $id)->get('spmi_versions')->row(); }
    public function find_active_unit($id) { return $this->db->where(['id' => (int) $id, 'is_active' => 1])->get('organization_units')->row(); }
    public function get_units() { return $this->db->where('is_active', 1)->order_by('name', 'ASC')->get('organization_units')->result(); }
    public function get_indicators($standard_id) { return $this->db->where('standard_id', (int) $standard_id)->order_by('indicator_code', 'ASC')->get('spmi_indicators')->result(); }
    public function find_indicator($id) { return $this->db->select('i.*, s.standard_code, s.title AS standard_title, s.version_id, v.version_code, v.title AS version_title, v.status AS version_status, su.name AS scope_unit_name, ru.name AS responsible_unit_name')->from('spmi_indicators i')->join('spmi_standards s', 's.id = i.standard_id')->join('spmi_versions v', 'v.id = s.version_id')->join('organization_units su', 'su.id = i.scope_organization_unit_id')->join('organization_units ru', 'ru.id = i.responsible_organization_unit_id')->where('i.id', (int) $id)->get()->row(); }
    public function find_indicator_for_update($id) { return $this->db->where('id', (int) $id)->get('spmi_indicators')->row(); }
    public function lock_indicator($id) { return $this->db->query('SELECT * FROM spmi_indicators WHERE id = ' . (int) $id . ' FOR UPDATE')->row(); }
    public function find_by_code($standard_id, $code, $exclude_id = 0) { $this->db->where(['standard_id' => (int) $standard_id, 'indicator_code' => $code]); if ($exclude_id) $this->db->where('id !=', (int) $exclude_id); return $this->db->get('spmi_indicators')->row(); }
    public function create_indicator($data) { return $this->db->insert('spmi_indicators', $data); }
    public function update_indicator($id, $data) { return $this->db->where('id', (int) $id)->update('spmi_indicators', $data); }
    public function get_targets($indicator_id) { return $this->db->where('indicator_id', (int) $indicator_id)->order_by('target_year', 'ASC')->get('spmi_indicator_targets')->result(); }
    public function find_target($id) { return $this->db->where('id', (int) $id)->get('spmi_indicator_targets')->row(); }
    public function find_target_by_year($indicator_id, $year, $exclude_id = 0) { $this->db->where(['indicator_id' => (int) $indicator_id, 'target_year' => (int) $year]); if ($exclude_id) $this->db->where('id !=', (int) $exclude_id); return $this->db->get('spmi_indicator_targets')->row(); }
    public function create_target($data) { return $this->db->insert('spmi_indicator_targets', $data); }
    public function update_target($id, $data) { return $this->db->where('id', (int) $id)->update('spmi_indicator_targets', $data); }
    public function find_indicator_by_standard_code($standard_id, $code, $for_update = FALSE) { return $for_update ? $this->db->query('SELECT * FROM spmi_indicators WHERE standard_id = ' . (int) $standard_id . ' AND indicator_code = ' . $this->db->escape($code) . ' FOR UPDATE')->row() : $this->db->where(['standard_id' => (int) $standard_id, 'indicator_code' => $code])->get('spmi_indicators')->row(); }
    public function upsert_indicator($data, $id = 0) { return $id ? $this->db->where('id', (int) $id)->update('spmi_indicators', $data) : $this->db->insert('spmi_indicators', $data); }
    public function find_target_by_indicator_year($indicator_id, $year, $for_update = FALSE) { return $for_update ? $this->db->query('SELECT * FROM spmi_indicator_targets WHERE indicator_id = ' . (int) $indicator_id . ' AND target_year = ' . (int) $year . ' FOR UPDATE')->row() : $this->find_target_by_year($indicator_id, $year); }
    public function upsert_target($data, $id = 0) { return $id ? $this->db->where('id', (int) $id)->update('spmi_indicator_targets', $data) : $this->db->insert('spmi_indicator_targets', $data); }
    public function indicator_has_assignments($indicator_id) { return $this->db->select('1', FALSE)->where('source_indicator_id', (int) $indicator_id)->limit(1)->get('spmi_audit_assignment_items')->num_rows() > 0; }
    public function indicator_has_legacy_dependencies($indicator_id) { return $this->table_has_row('spmi_instrument_questions', 'indicator_id', $indicator_id); }
    public function delete_indicator_targets($indicator_id) { $targets = $this->db->query('SELECT id FROM spmi_indicator_targets WHERE indicator_id = ' . (int) $indicator_id . ' FOR UPDATE')->result(); foreach ($targets as $target) if (!$this->delete_by_id('spmi_indicator_targets', (int) $target->id)) return FALSE; return TRUE; }
    public function delete_indicator($id) { return $this->delete_by_id('spmi_indicators', $id); }
    public function get_master_rows($version_id) { return $this->db->select('s.standard_code, s.display_order AS standard_order, s.title AS standard_title, s.description AS standard_description, i.indicator_code, i.indicator_type, i.title AS indicator_title, su.code AS scope_unit_code, ru.code AS responsible_unit_code, i.responsible_pic_name, i.evidence_requirement, t.target_year, t.target_value, i.evidence_policy')->from('spmi_standards s')->join('spmi_indicators i', 'i.standard_id = s.id', 'left')->join('organization_units su', 'su.id = i.scope_organization_unit_id', 'left')->join('organization_units ru', 'ru.id = i.responsible_organization_unit_id', 'left')->join('spmi_indicator_targets t', 't.indicator_id = i.id', 'left')->where('s.version_id', (int) $version_id)->order_by('s.display_order', 'ASC')->order_by('i.indicator_code', 'ASC')->order_by('t.target_year', 'ASC')->get()->result(); }
    private function delete_by_id($table, $id) { if (!$this->db->where('id', (int) $id)->delete($table)) return FALSE; return $this->db->affected_rows() === 1; }
    private function table_has_row($table, $column, $id) { if (!$this->db->table_exists($table)) return FALSE; return $this->db->select('1', FALSE)->where($column, (int) $id)->limit(1)->get($table)->num_rows() > 0; }
}
