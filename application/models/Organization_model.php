<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Organization_model extends CI_Model
{
    public function get_units()
    {
        return $this->db->order_by('code', 'ASC')->get('organization_units')->result();
    }

    public function get_master_directory_units()
    {
        return $this->db
            ->select('ou.id, ou.parent_id, ou.code, ou.name, ou.type, ou.is_active, parent.code AS parent_code, parent.name AS parent_name, parent.type AS parent_type')
            ->from('organization_units ou')
            ->join('organization_units parent', 'parent.id = ou.parent_id', 'left')
            ->join('profil_prodi pp_linked', 'pp_linked.organization_unit_id = ou.id', 'left')
            ->where_in('ou.type', ['university', 'faculty', 'study_program', 'bureau', 'unit', 'institute'])
            ->where('(ou.type != ' . $this->db->escape('study_program') . ' OR pp_linked.organization_unit_id IS NULL)', NULL, FALSE)
            ->order_by('ou.parent_id IS NOT NULL', 'ASC', FALSE)
            ->order_by('parent.code', 'ASC')
            ->order_by('ou.type', 'ASC')
            ->order_by('ou.code', 'ASC')
            ->get()
            ->result();
    }

    public function get_master_directory_summary()
    {
        $summary = [
            'faculty' => 0,
            'study_program' => 0,
            'bureau' => 0,
            'unit' => 0,
            'institute' => 0,
            'active_staff' => 0,
        ];

        $unit_counts = $this->db
            ->select('type, COUNT(id) AS total', FALSE)
            ->from('organization_units')
            ->where_in('type', ['faculty', 'bureau', 'unit', 'institute'])
            ->group_by('type')
            ->get()
            ->result();

        foreach ($unit_counts as $row) {
            if (isset($summary[$row->type])) {
                $summary[$row->type] = (int) $row->total;
            }
        }

        // ponytail: prodi count from canonical profil_prodi, not organization_units
        $summary['study_program'] = (int) $this->db->count_all('profil_prodi');
        $summary['active_staff'] = $this->count_master_directory_active_staff();
        return $summary;
    }

    public function get_current_non_prodi_staff_placements()
    {
        return $this->db
            ->select('a.id, a.user_id, a.organization_unit_id, a.position_code, a.valid_from, a.valid_until, a.is_primary, u.nama, u.email, ou.code AS unit_code, ou.name AS unit_name, ou.type AS unit_type')
            ->from('user_unit_assignments a')
            ->join('users u', 'u.id = a.user_id')
            ->join('organization_units ou', 'ou.id = a.organization_unit_id')
            ->where_in('ou.type', ['faculty', 'bureau', 'unit', 'institute'])
            ->where('a.valid_from <= CURDATE()', NULL, FALSE)
            ->group_start()
                ->where('a.valid_until IS NULL', NULL, FALSE)
                ->or_where('a.valid_until > CURDATE()', NULL, FALSE)
            ->group_end()
            ->order_by('ou.type', 'ASC')
            ->order_by('ou.code', 'ASC')
            ->order_by('u.nama', 'ASC')
            ->get()
            ->result();
    }

    public function count_current_non_prodi_staff_placements()
    {
        return (int) $this->db
            ->select('COUNT(DISTINCT a.user_id) AS total', FALSE)
            ->from('user_unit_assignments a')
            ->join('organization_units ou', 'ou.id = a.organization_unit_id')
            ->where_in('ou.type', ['faculty', 'bureau', 'unit', 'institute'])
            ->where('a.valid_from <= CURDATE()', NULL, FALSE)
            ->group_start()
                ->where('a.valid_until IS NULL', NULL, FALSE)
                ->or_where('a.valid_until > CURDATE()', NULL, FALSE)
            ->group_end()
            ->get()
            ->row()
            ->total;
    }

    private function count_master_directory_active_staff()
    {
        $prodi_staff = $this->db
            ->select('id_akun AS user_id')
            ->from('staf_prodi')
            ->where('status', 'active')
            ->get_compiled_select();

        $non_prodi_staff = $this->db
            ->select('a.user_id')
            ->from('user_unit_assignments a')
            ->join('organization_units ou', 'ou.id = a.organization_unit_id')
            ->where_in('ou.type', ['faculty', 'bureau', 'unit', 'institute'])
            ->where('a.valid_from <= CURDATE()', NULL, FALSE)
            ->group_start()
                ->where('a.valid_until IS NULL', NULL, FALSE)
                ->or_where('a.valid_until > CURDATE()', NULL, FALSE)
            ->group_end()
            ->get_compiled_select();

        return (int) $this->db
            ->query('SELECT COUNT(DISTINCT user_id) AS total FROM ((' . $prodi_staff . ') UNION ALL (' . $non_prodi_staff . ')) active_directory_staff')
            ->row()
            ->total;
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
