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
}
