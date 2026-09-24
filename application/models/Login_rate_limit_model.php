<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login_rate_limit_model extends CI_Model
{
    protected $table = 'login_rate_limit_buckets';

    public function is_blocked($identity_hash, $ip_hash)
    {
        $query = $this->db->query(
            'SELECT MAX(TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP(), blocked_until)) AS retry_after FROM ' . $this->table . ' WHERE (scope = ? AND key_hash = ?) OR (scope = ? AND key_hash = ?)',
            ['identity', $identity_hash, 'ip', $ip_hash]
        );
        if ($query === FALSE) {
            throw new RuntimeException('Unable to read login rate-limit buckets.');
        }

        $row = $query->row();

        return max(0, (int) ($row->retry_after ?? 0));
    }

    public function record_failure($scope, $key_hash, $threshold)
    {
        return $this->db->query(
            'INSERT INTO ' . $this->table . ' (scope, key_hash, window_started_at, failure_count, blocked_until, updated_at) VALUES (?, ?, UTC_TIMESTAMP(), 1, NULL, UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE blocked_until = IF(IF(window_started_at <= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 15 MINUTE), 1, failure_count + 1) >= ?, DATE_ADD(IF(window_started_at <= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 15 MINUTE), UTC_TIMESTAMP(), window_started_at), INTERVAL 15 MINUTE), NULL), failure_count = IF(window_started_at <= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 15 MINUTE), 1, failure_count + 1), window_started_at = IF(window_started_at <= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 15 MINUTE), UTC_TIMESTAMP(), window_started_at), updated_at = UTC_TIMESTAMP()',
            [$scope, $key_hash, (int) $threshold]
        );
    }

    public function clear_identity($identity_hash)
    {
        return $this->db->where('scope', 'identity')->where('key_hash', $identity_hash)->delete($this->table);
    }

    public function clear_expired($limit = 100)
    {
        return $this->db->query(
            'DELETE FROM ' . $this->table . ' WHERE window_started_at <= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 15 MINUTE) LIMIT ' . max(1, min(100, (int) $limit))
        );
    }
}
