<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Organization_unit_model extends CI_Model
{
    protected $table = 'organization_units';

    public function schema_ready()
    {
        return $this->db->table_exists($this->table);
    }

    public function get_all()
    {
        return $this->db
            ->select(
                'organization_units.*, parent.code AS parent_code, '
                . 'parent.name AS parent_name, parent.type AS parent_type'
            )
            ->from($this->table)
            ->join(
                $this->table . ' AS parent',
                'parent.id = organization_units.parent_id',
                'left'
            )
            ->order_by('organization_units.name', 'ASC')
            ->order_by('organization_units.code', 'ASC')
            ->get()
            ->result();
    }

    public function get_all_plain()
    {
        return $this->db
            ->order_by('name', 'ASC')
            ->order_by('code', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function find($id)
    {
        return $this->db
            ->select(
                'organization_units.*, parent.code AS parent_code, '
                . 'parent.name AS parent_name, parent.type AS parent_type'
            )
            ->from($this->table)
            ->join(
                $this->table . ' AS parent',
                'parent.id = organization_units.parent_id',
                'left'
            )
            ->where('organization_units.id', (int) $id)
            ->get()
            ->row();
    }

    public function get_children($id)
    {
        return $this->db
            ->where('parent_id', (int) $id)
            ->order_by('name', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function code_exists($code, $except_id = NULL)
    {
        $this->db->from($this->table)->where('code', (string) $code);
        if ($except_id !== NULL) {
            $this->db->where('id !=', (int) $except_id);
        }

        return (int) $this->db->count_all_results() > 0;
    }

    public function create($data)
    {
        if (!$this->db->insert($this->table, $data)) {
            return NULL;
        }

        return (int) $this->db->insert_id();
    }

    public function update($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, $data);
    }
}
