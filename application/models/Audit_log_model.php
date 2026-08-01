<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_log_model extends CI_Model
{
    private $table = 'audit_logs';

    public function append(array $data)
    {
        if (!$this->db->table_exists('audit_logs')) return FALSE;
        return $this->db->insert($this->table, $data);
    }
}
