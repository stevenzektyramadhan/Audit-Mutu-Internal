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

    public function get_standards($version_id)
    {
        return $this->db->where('version_id', (int) $version_id)->order_by('display_order', 'ASC')->get('spmi_standards')->result();
    }

    public function find_standard($id)
    {
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

    public function upsert_standard($data, $id = 0)
    {
        if ($id) return $this->db->where('id', (int) $id)->update('spmi_standards', $data);
        return $this->db->insert('spmi_standards', $data);
    }
}
