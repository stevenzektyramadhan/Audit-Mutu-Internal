<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_unit_assignment_model extends CI_Model
{
    protected $table = 'user_unit_assignments';

    public function schema_ready()
    {
        return $this->db->table_exists($this->table)
            && $this->db->table_exists('organization_units');
    }

    public function get_for_user($user_id)
    {
        return $this->base_query()
            ->where('user_unit_assignments.user_id', (int) $user_id)
            ->order_by('user_unit_assignments.valid_from', 'DESC')
            ->order_by('user_unit_assignments.id', 'DESC')
            ->get()
            ->result();
    }

    public function find($id)
    {
        return $this->base_query()
            ->where('user_unit_assignments.id', (int) $id)
            ->get()
            ->row();
    }

    public function get_active_for_user($user_id, $on_date)
    {
        return $this->base_query()
            ->where('user_unit_assignments.user_id', (int) $user_id)
            ->where('users.is_active', 1)
            ->where('organization_units.active', 1)
            ->where('user_unit_assignments.valid_from <=', (string) $on_date)
            ->group_start()
                ->where('user_unit_assignments.valid_until IS NULL', NULL, FALSE)
                ->or_where('user_unit_assignments.valid_until >=', (string) $on_date)
            ->group_end()
            ->order_by('user_unit_assignments.is_primary', 'DESC')
            ->order_by('organization_units.name', 'ASC')
            ->get()
            ->result();
    }

    public function lock_user($user_id)
    {
        $sql = $this->db
            ->select('id')
            ->where('id', (int) $user_id)
            ->limit(1)
            ->get_compiled_select('users');

        return $this->db->query($sql . ' FOR UPDATE')->row();
    }

    public function has_overlap(
        $user_id,
        $organization_unit_id,
        $position_code,
        $valid_from,
        $valid_until
    ) {
        return $this->overlap_query($valid_from, $valid_until)
            ->where('user_id', (int) $user_id)
            ->where('organization_unit_id', (int) $organization_unit_id)
            ->where('position_code', (string) $position_code)
            ->count_all_results() > 0;
    }

    public function has_primary_overlap($user_id, $valid_from, $valid_until)
    {
        return $this->overlap_query($valid_from, $valid_until)
            ->where('user_id', (int) $user_id)
            ->where('is_primary', 1)
            ->count_all_results() > 0;
    }

    public function create($data)
    {
        if (!$this->db->insert($this->table, $data)) {
            return NULL;
        }

        return (int) $this->db->insert_id();
    }

    public function set_valid_until($id, $valid_until)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, ['valid_until' => (string) $valid_until]);
    }

    public function has_history_for_user($user_id)
    {
        return $this->schema_ready()
            && $this->db
                ->where('user_id', (int) $user_id)
                ->count_all_results($this->table) > 0;
    }

    protected function base_query()
    {
        return $this->db
            ->select(
                'user_unit_assignments.*, '
                . 'organization_units.code AS organization_unit_code, '
                . 'organization_units.name AS organization_unit_name, '
                . 'organization_units.type AS organization_unit_type, '
                . 'organization_units.active AS organization_unit_active, '
                . 'users.nama AS user_name, users.email AS user_email, '
                . 'users.role AS user_role, users.is_active AS user_active'
            )
            ->from($this->table)
            ->join(
                'organization_units',
                'organization_units.id = user_unit_assignments.organization_unit_id'
            )
            ->join('users', 'users.id = user_unit_assignments.user_id');
    }

    protected function overlap_query($valid_from, $valid_until)
    {
        $this->db->from($this->table);
        if ($valid_until !== NULL) {
            $this->db->where('valid_from <=', (string) $valid_until);
        }

        return $this->db
            ->group_start()
                ->where('valid_until IS NULL', NULL, FALSE)
                ->or_where('valid_until >=', (string) $valid_from)
            ->group_end();
    }
}
