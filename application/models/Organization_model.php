<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Organization_model extends CI_Model
{
    public function get_units()
    {
        return $this->db->order_by('code', 'ASC')->get('organization_units')->result();
    }

    public function find_unit($id)
    {
        return $this->db->where('id', (int) $id)->get('organization_units')->row();
    }

    public function find_active_unit($id)
    {
        return $this->db->where(['id' => (int) $id, 'is_active' => 1])->get('organization_units')->row();
    }

    public function code_exists($code, $except_id = 0)
    {
        $this->db->where('code', $code);
        if ($except_id) $this->db->where('id !=', (int) $except_id);
        return $this->db->count_all_results('organization_units') > 0;
    }

    public function create_unit($data) { return $this->db->insert('organization_units', $data); }
    public function update_unit($id, $data) { return $this->db->where('id', (int) $id)->update('organization_units', $data); }

    public function has_active_children($id)
    {
        return $this->db->where(['parent_id' => (int) $id, 'is_active' => 1])->count_all_results('organization_units') > 0;
    }

    public function get_assignments($unit_id = 0)
    {
        $this->db->select('a.*, u.nama, u.email, o.name AS unit_name, o.code AS unit_code')
            ->from('user_unit_assignments a')
            ->join('users u', 'u.id = a.user_id')
            ->join('organization_units o', 'o.id = a.organization_unit_id');
        if ($unit_id) $this->db->where('a.organization_unit_id', (int) $unit_id);
        return $this->db->order_by('a.valid_from', 'DESC')->get()->result();
    }

    public function get_users() { return $this->db->order_by('nama', 'ASC')->get('users')->result(); }
    public function find_user($id) { return $this->db->where('id', (int) $id)->get('users')->row(); }
    public function create_assignment($data) { return $this->db->insert('user_unit_assignments', $data); }
    public function find_assignment($id) { return $this->db->where('id', (int) $id)->get('user_unit_assignments')->row(); }
    public function end_assignment($id, $until) { return $this->db->where('id', (int) $id)->update('user_unit_assignments', ['valid_until' => $until]); }
    public function clear_primary($user_id) { return $this->db->where(['user_id' => (int) $user_id, 'valid_until' => NULL])->update('user_unit_assignments', ['is_primary' => 0]); }

    public function get_capabilities()
    {
        return $this->db->order_by('code', 'ASC')->get('capabilities')->result();
    }

    public function get_role_capability_codes($role)
    {
        return $this->db->select('c.code')->from('role_capabilities rc')->join('capabilities c', 'c.id = rc.capability_id')->where('rc.role', $role)->get()->result_array();
    }

    public function replace_role_capabilities($role, $capability_ids)
    {
        $this->db->where('role', $role)->delete('role_capabilities');
        foreach ($capability_ids as $capability_id) {
            $this->db->insert('role_capabilities', ['role' => $role, 'capability_id' => (int) $capability_id]);
        }
        return $this->db->trans_status();
    }
}
