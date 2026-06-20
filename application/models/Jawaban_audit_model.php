<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jawaban_audit_model extends CI_Model
{
    protected $table = 'jawaban_audit';

    public function insert_batch($data)
    {
        return $this->db->insert_batch($this->table, $data);
    }
}
