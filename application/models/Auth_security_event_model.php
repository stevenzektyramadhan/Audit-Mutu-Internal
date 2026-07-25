<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_security_event_model extends CI_Model
{
    protected $table = 'auth_security_events';

    public function record($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function count_recent_email_failures($email_hash, $since)
    {
        return (int) $this->db
            ->where('email_hash', $email_hash)
            ->where('event_type', 'login_failed')
            ->where('created_at >=', $since)
            ->count_all_results($this->table);
    }

    public function most_recent_email_success($email_hash, $since)
    {
        $row = $this->db
            ->select('created_at')
            ->where('email_hash', $email_hash)
            ->where('event_type', 'login_succeeded')
            ->where('created_at >=', $since)
            ->order_by('created_at', 'DESC')
            ->limit(1)
            ->get($this->table)
            ->row();

        return $row ? $row->created_at : NULL;
    }

    public function count_recent_ip_failures($ip_hash, $since)
    {
        return (int) $this->db
            ->where('ip_hash', $ip_hash)
            ->where('event_type', 'login_failed')
            ->where('created_at >=', $since)
            ->count_all_results($this->table);
    }

    public function oldest_recent_email_failure($email_hash, $since)
    {
        return $this->oldest_failure('email_hash', $email_hash, $since);
    }

    public function oldest_recent_ip_failure($ip_hash, $since)
    {
        return $this->oldest_failure('ip_hash', $ip_hash, $since);
    }

    private function oldest_failure($field, $value, $since)
    {
        $row = $this->db
            ->select('created_at')
            ->where($field, $value)
            ->where('event_type', 'login_failed')
            ->where('created_at >=', $since)
            ->order_by('created_at', 'ASC')
            ->limit(1)
            ->get($this->table)
            ->row();

        return $row ? $row->created_at : NULL;
    }
}
