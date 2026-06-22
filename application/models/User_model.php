<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    protected $table = 'users';

    public function __construct()
    {
        parent::__construct();
    }

    public function find_by_email($email)
    {
        return $this->db->where('email', $email)->get($this->table)->row();
    }

    public function find($id)
    {
        return $this->db->where('id', (int) $id)->get($this->table)->row();
    }

    public function email_exists_except($email, $id)
    {
        return $this->db
            ->where('email', $email)
            ->where('id !=', (int) $id)
            ->count_all_results($this->table) > 0;
    }

    public function create($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        return $this->db->where('id', (int) $id)->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db->where('id', (int) $id)->delete($this->table);
    }

    public function has_audit_assignments($id)
    {
        if (!$this->db->table_exists('tugas_audit')) {
            return FALSE;
        }

        return $this->db
            ->group_start()
                ->where('auditor_id', (int) $id)
                ->or_where('auditee_id', (int) $id)
            ->group_end()
            ->count_all_results('tugas_audit') > 0;
    }

    public function get_all($filters = [])
    {
        $this->db->from($this->table);

        if (!empty($filters['q'])) {
            $this->db
                ->group_start()
                    ->like('nama', $filters['q'])
                    ->or_like('email', $filters['q'])
                ->group_end();
        }

        if (!empty($filters['role'])) {
            $this->db->where('role', $filters['role']);
        }

        return $this->db->order_by('id', 'ASC')->get()->result();
    }

    public function get_by_role($role)
    {
        return $this->db->where('role', $role)->get($this->table)->result();
    }

    public function count_all()
    {
        if (!$this->db->table_exists($this->table)) {
            return 0;
        }

        return (int) $this->db->count_all_results($this->table);
    }

    public function count_by_role($role)
    {
        if (!$this->db->table_exists($this->table)) {
            return 0;
        }

        return (int) $this->db
            ->where('role', $role)
            ->count_all_results($this->table);
    }
}
