<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Password_reset_token_model extends CI_Model
{
    protected $table = 'password_reset_tokens';

    public function create($user_id, $token_hash, $expires_at)
    {
        return $this->db->insert($this->table, [
            'user_id' => (int) $user_id,
            'token_hash' => $token_hash,
            'expires_at' => $expires_at,
        ]);
    }

    public function find_active_by_hash_for_update($token_hash)
    {
        return $this->db->query(
            'SELECT * FROM ' . $this->table . ' WHERE token_hash = ? AND consumed_at IS NULL AND expires_at > NOW() FOR UPDATE',
            [$token_hash]
        )->row();
    }

    public function find_active_by_hash($token_hash)
    {
        return $this->db->query(
            'SELECT * FROM ' . $this->table . ' WHERE token_hash = ? AND consumed_at IS NULL AND expires_at > NOW()',
            [$token_hash]
        )->row();
    }

    public function invalidate_active_for_user($user_id)
    {
        return $this->db
            ->where('user_id', (int) $user_id)
            ->where('consumed_at IS NULL', NULL, FALSE)
            ->update($this->table, ['consumed_at' => date('Y-m-d H:i:s')]);
    }
}
