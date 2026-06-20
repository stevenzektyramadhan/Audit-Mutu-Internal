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

    public function create($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function get_all()
    {
        return $this->db->get($this->table)->result();
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
