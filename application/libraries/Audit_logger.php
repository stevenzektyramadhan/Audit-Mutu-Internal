<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Central append-only security/domain audit boundary.
 *
 * Callers submit only small state snapshots and allowlisted metadata. Request
 * bodies, passwords, tokens, cookies, raw IPs, and raw user agents are never
 * stored in the ledger.
 */
class Audit_logger
{
    protected $ci;
    protected $model;

    protected $metadata_allowlist = [
        'reason_code',
        'role_from',
        'role_to',
        'status_from',
        'status_to',
        'row_count',
        'format',
        'category',
        'changed_fields',
        'source',
        'file_asset_id',
        'scope',
    ];

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Security_audit_log_model');
        $this->model = $this->ci->Security_audit_log_model;
    }

    public function record(
        $event_type,
        $object_type,
        $object_id,
        $action,
        $before = NULL,
        $after = NULL,
        array $metadata = [],
        $actor_user_id = NULL,
        $outcome = 'success'
    ) {
        if (!$this->model->schema_ready()) {
            log_message('error', 'SECURITY audit_ledger_unavailable request_id=' . ami_request_id());
            return NULL;
        }

        $event_type = $this->token($event_type, 64);
        $object_type = $this->token($object_type, 64);
        $action = $this->token($action, 40);
        $outcome = in_array($outcome, ['success', 'failure', 'blocked'], TRUE)
            ? $outcome
            : 'failure';
        if ($event_type === '' || $object_type === '' || $action === '') {
            return NULL;
        }

        if ($actor_user_id === NULL) {
            $actor_user_id = (int) $this->ci->session->userdata('user_id');
        } else {
            $actor_user_id = (int) $actor_user_id;
        }

        $changes = $this->sanitize_metadata($metadata);
        $changes_json = empty($changes)
            ? NULL
            : json_encode(
                $changes,
                JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE
                    | JSON_INVALID_UTF8_SUBSTITUTE
            );

        $log_id = $this->model->append([
            'actor_user_id' => $actor_user_id > 0 ? $actor_user_id : NULL,
            'event_type' => $event_type,
            'object_type' => $object_type,
            'object_id' => $this->object_identifier($object_id),
            'action' => $action,
            'outcome' => $outcome,
            'before_hash' => $this->snapshot_hash('before', $before),
            'after_hash' => $this->snapshot_hash('after', $after),
            'changes_json' => $changes_json,
            'ip_address' => $this->identifier_hash('ip', $this->client_ip()),
            'user_agent' => $this->identifier_hash('user_agent', $this->user_agent()),
            'request_id' => substr(ami_request_id(), 0, 64),
        ]);

        if ($log_id === NULL) {
            log_message(
                'error',
                'SECURITY audit_append_failed event=' . $event_type
                    . ' object=' . $object_type
                    . ' request_id=' . ami_request_id()
            );
        }

        return $log_id;
    }

    public function snapshot_hash($domain, $value)
    {
        if ($value === NULL || $value === []) {
            return NULL;
        }

        $normalized = $this->normalize_snapshot($value);
        return hash_hmac(
            'sha256',
            $this->token($domain, 20) . "\n" . json_encode(
                $normalized,
                JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE
                    | JSON_INVALID_UTF8_SUBSTITUTE
            ),
            $this->key()
        );
    }

    public function sanitize_metadata(array $metadata)
    {
        $clean = [];
        foreach ($this->metadata_allowlist as $key) {
            if (!array_key_exists($key, $metadata)) {
                continue;
            }

            $value = $metadata[$key];
            if ($value === NULL || $value === '') {
                continue;
            }
            if ($key === 'row_count' || $key === 'file_asset_id') {
                $clean[$key] = max(0, (int) $value);
                continue;
            }

            if ($key === 'changed_fields') {
                $fields = is_array($value) ? $value : [];
                $fields = array_values(array_unique(array_filter(array_map(function ($field) {
                    return $this->token($field, 40);
                }, array_slice($fields, 0, 30)))));
                sort($fields, SORT_STRING);
                $clean[$key] = $fields;
                continue;
            }

            $clean[$key] = $this->token($value, 120);
        }

        ksort($clean, SORT_STRING);
        return $clean;
    }

    private function normalize_snapshot($value)
    {
        if (is_object($value)) {
            $value = get_object_vars($value);
        }
        if (!is_array($value)) {
            return is_scalar($value) || $value === NULL ? $value : gettype($value);
        }

        $normalized = [];
        foreach ($value as $key => $item) {
            $key = (string) $key;
            if (preg_match('/(?:pass|token|secret|cookie|authorization|csrf|session)/i', $key)) {
                continue;
            }
            $normalized[$key] = $this->normalize_snapshot($item);
        }

        if (array_keys($normalized) !== range(0, count($normalized) - 1)) {
            ksort($normalized, SORT_STRING);
        }
        return $normalized;
    }

    private function identifier_hash($domain, $value)
    {
        return 'hmac-sha256:' . hash_hmac(
            'sha256',
            $this->token($domain, 20) . "\n" . (string) $value,
            $this->key()
        );
    }

    private function key()
    {
        $key = (string) $this->ci->config->item('encryption_key');
        return $key !== ''
            ? $key
            : hash('sha256', FCPATH . 'ami-audit-ledger-development');
    }

    private function client_ip()
    {
        $ip = (string) $this->ci->input->ip_address();
        return filter_var($ip, FILTER_VALIDATE_IP) !== FALSE ? $ip : 'unknown';
    }

    private function user_agent()
    {
        return isset($_SERVER['HTTP_USER_AGENT'])
            ? substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 512)
            : 'unknown';
    }

    private function object_identifier($value)
    {
        if ($value === NULL || $value === '') {
            return NULL;
        }

        $value = preg_replace('/[^A-Za-z0-9:_.-]/', '', (string) $value);
        return $value !== '' ? substr($value, 0, 128) : NULL;
    }

    private function token($value, $length)
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9_.-]/', '_', $value);
        $value = trim(preg_replace('/_+/', '_', $value), '_');
        return substr($value, 0, (int) $length);
    }
}
