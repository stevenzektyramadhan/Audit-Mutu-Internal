<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_standards_model extends CI_Model
{
    public function get_versions()
    {
        return $this->db->order_by('id', 'DESC')->get('spmi_versions')->result();
    }

    public function find_version($id, $for_update = FALSE)
    {
        if ($for_update) return $this->db->query('SELECT * FROM spmi_versions WHERE id = ' . (int) $id . ' FOR UPDATE')->row();
        return $this->db->where('id', (int) $id)->get('spmi_versions')->row();
    }

    public function find_version_by_code($code)
    {
        return $this->db->where('version_code', $code)->get('spmi_versions')->row();
    }

    public function create_version($data)
    {
        $this->db->insert('spmi_versions', $data);
        return $this->db->insert_id();
    }

    public function update_version($id, $data)
    {
        return $this->db->where('id', (int) $id)->update('spmi_versions', $data);
    }

    public function get_standards($version_id, $for_update = FALSE)
    {
        if ($for_update) return $this->db->query('SELECT * FROM spmi_standards WHERE version_id = ' . (int) $version_id . ' ORDER BY display_order ASC, id ASC FOR UPDATE')->result();
        return $this->db->where('version_id', (int) $version_id)->order_by('display_order', 'ASC')->get('spmi_standards')->result();
    }

    public function find_standard($id, $for_update = FALSE)
    {
        if ($for_update) return $this->db->query('SELECT * FROM spmi_standards WHERE id = ' . (int) $id . ' FOR UPDATE')->row();
        return $this->db->where('id', (int) $id)->get('spmi_standards')->row();
    }

    public function create_standard($data)
    {
        return $this->db->insert('spmi_standards', $data);
    }

    public function update_standard($id, $data)
    {
        return $this->db->where('id', (int) $id)->update('spmi_standards', $data);
    }

    public function find_standard_by_version_code($version_id, $code, $for_update = FALSE)
    {
        $query = $this->db->where(['version_id' => (int) $version_id, 'standard_code' => $code]);
        if ($for_update) $query->from('spmi_standards');
        return $for_update ? $this->db->query('SELECT * FROM spmi_standards WHERE version_id = ' . (int) $version_id . ' AND standard_code = ' . $this->db->escape($code) . ' FOR UPDATE')->row() : $query->get('spmi_standards')->row();
    }

    public function find_standard_by_version_order($version_id, $order, $for_update = FALSE)
    {
        if ($for_update) return $this->db->query('SELECT * FROM spmi_standards WHERE version_id = ' . (int) $version_id . ' AND display_order = ' . (int) $order . ' FOR UPDATE')->row();
        return $this->db->where(['version_id' => (int) $version_id, 'display_order' => (int) $order])->get('spmi_standards')->row();
    }

    public function upsert_standard($data, $id = 0)
    {
        if ($id) return $this->db->where('id', (int) $id)->update('spmi_standards', $data);
        return $this->db->insert('spmi_standards', $data);
    }

    public function version_has_assignments($version_id)
    {
        return $this->db->select('1', FALSE)
            ->where('source_version_id', (int) $version_id)
            ->limit(1)
            ->get('spmi_audit_assignments')
            ->num_rows() > 0;
    }

    public function standard_has_assignments($standard_id)
    {
        return $this->db->select('1', FALSE)
            ->where('source_standard_id', (int) $standard_id)
            ->limit(1)
            ->get('spmi_audit_assignments')
            ->num_rows() > 0;
    }

    public function indicator_has_assignments($indicator_id)
    {
        return $this->db->select('1', FALSE)
            ->where('source_indicator_id', (int) $indicator_id)
            ->limit(1)
            ->get('spmi_audit_assignment_items')
            ->num_rows() > 0;
    }

    public function standard_has_legacy_dependencies($standard_id)
    {
        return $this->table_has_row('spmi_instrument_packages', 'standard_id', $standard_id);
    }

    public function indicator_has_legacy_dependencies($indicator_id)
    {
        return $this->table_has_row('spmi_instrument_questions', 'indicator_id', $indicator_id);
    }

    public function get_standard_indicators($standard_id, $for_update = FALSE)
    {
        if ($for_update) return $this->db->query('SELECT * FROM spmi_indicators WHERE standard_id = ' . (int) $standard_id . ' ORDER BY id ASC FOR UPDATE')->result();
        return $this->db->where('standard_id', (int) $standard_id)->order_by('id', 'ASC')->get('spmi_indicators')->result();
    }

    public function delete_standard_children($standard_id)
    {
        foreach ($this->get_standard_indicators($standard_id, TRUE) as $indicator) {
            if (!$this->delete_indicator_targets((int) $indicator->id) || !$this->delete_by_id('spmi_indicators', (int) $indicator->id)) return FALSE;
        }
        return TRUE;
    }

    public function delete_standard($id)
    {
        return $this->delete_by_id('spmi_standards', $id);
    }

    public function delete_version($id)
    {
        return $this->delete_by_id('spmi_versions', $id);
    }

    private function delete_indicator_targets($indicator_id)
    {
        $targets = $this->db->query('SELECT id FROM spmi_indicator_targets WHERE indicator_id = ' . (int) $indicator_id . ' FOR UPDATE')->result();
        foreach ($targets as $target) if (!$this->delete_by_id('spmi_indicator_targets', (int) $target->id)) return FALSE;
        return TRUE;
    }

    private function delete_by_id($table, $id)
    {
        if (!$this->db->where('id', (int) $id)->delete($table)) return FALSE;
        return $this->db->affected_rows() === 1;
    }

    private function table_has_row($table, $column, $id)
    {
        if (!$this->db->table_exists($table)) return FALSE;
        return $this->db->select('1', FALSE)->where($column, (int) $id)->limit(1)->get($table)->num_rows() > 0;
    }
}
