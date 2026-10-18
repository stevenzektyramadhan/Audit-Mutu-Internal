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

    public function record_request($scope, $key_hash, $limit, $window_minutes)
    {
        $limit = max(1, min(1000, (int) $limit));
        $window_minutes = max(1, min(1440, (int) $window_minutes));

        return $this->db->query(
            'INSERT INTO ' . $this->table . ' (scope, key_hash, window_started_at, failure_count, blocked_until, updated_at) VALUES (?, ?, UTC_TIMESTAMP(), 1, NULL, UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE blocked_until = IF(window_started_at <= DATE_SUB(UTC_TIMESTAMP(), INTERVAL ' . $window_minutes . ' MINUTE), NULL, IF(failure_count >= ?, DATE_ADD(window_started_at, INTERVAL ' . $window_minutes . ' MINUTE), NULL)), failure_count = IF(window_started_at <= DATE_SUB(UTC_TIMESTAMP(), INTERVAL ' . $window_minutes . ' MINUTE), 1, failure_count + 1), window_started_at = IF(window_started_at <= DATE_SUB(UTC_TIMESTAMP(), INTERVAL ' . $window_minutes . ' MINUTE), UTC_TIMESTAMP(), window_started_at), updated_at = UTC_TIMESTAMP()',
            [$scope, $key_hash, $limit]
        );
    }

    public function consume_request_budget($identity_hash, $ip_hash, $identity_limit, $ip_limit, $window_minutes)
    {
        $transaction_started = FALSE;

        try {
            $this->db->trans_begin();
            $transaction_started = TRUE;
            $retry_after = $this->is_blocked($identity_hash, $ip_hash);
            if ($retry_after <= 0) {
                if (!$this->record_request('identity', $identity_hash, $identity_limit, $window_minutes)
                    || !$this->record_request('ip', $ip_hash, $ip_limit, $window_minutes)) {
                    throw new RuntimeException('Unable to record request rate-limit budget.');
                }
                $retry_after = $this->is_blocked($identity_hash, $ip_hash);
            }

            if (!$this->db->trans_status() || !$this->db->trans_commit()) {
                throw new RuntimeException('Unable to complete request rate-limit budget.');
            }
            $transaction_started = FALSE;

            return ['allowed' => $retry_after <= 0, 'retry_after' => max(0, (int) $retry_after)];
        } catch (Throwable $exception) {
            if ($transaction_started) {
                $this->db->trans_rollback();
            }
            throw new RuntimeException('Unable to consume request rate-limit budget.');
        }
    }

    public function clear_identity($identity_hash)
    {
        return $this->db->where('scope', 'identity')->where('key_hash', $identity_hash)->delete($this->table);
    }

    public function clear_expired($limit = 100)
    {
        return $this->db->query(
            'DELETE FROM ' . $this->table . ' WHERE window_started_at <= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 60 MINUTE) LIMIT ' . max(1, min(100, (int) $limit))
        );
    }
}
