<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Security_audit_log_model extends CI_Model
{
    const GENESIS_HASH = '0000000000000000000000000000000000000000000000000000000000000000';

    protected $table = 'security_audit_logs';
    protected $state_table = 'security_audit_chain_state';

    public function schema_ready()
    {
        return $this->db->table_exists($this->table)
            && $this->db->table_exists($this->state_table);
    }

    /**
     * Append exactly one entry while serializing writers on the chain-state row.
     * This method intentionally exposes no update or delete operation.
     */
    public function append(array $data)
    {
        if (!$this->schema_ready()) {
            return NULL;
        }

        $this->db->trans_start();

        $state = $this->db
            ->query(
                'SELECT current_hash
                 FROM ' . $this->state_table . '
                 WHERE id = 1
                 FOR UPDATE'
            )
            ->row();

        if (!$state) {
            $this->db->insert($this->state_table, [
                'id' => 1,
                'current_hash' => self::GENESIS_HASH,
                'last_log_id' => NULL,
            ]);
            $state = (object) ['current_hash' => self::GENESIS_HASH];
        }

        $data['event_uuid'] = $this->event_uuid();
        $data['created_at'] = (new DateTimeImmutable())->format('Y-m-d H:i:s.u');
        $data['previous_hash'] = (string) $state->current_hash;
        $data['entry_hash'] = $this->calculate_entry_hash($data);

        $inserted = $this->db->insert($this->table, $data);
        $log_id = $inserted ? (int) $this->db->insert_id() : 0;

        if ($inserted) {
            $this->db
                ->where('id', 1)
                ->update($this->state_table, [
                    'current_hash' => $data['entry_hash'],
                    'last_log_id' => $log_id,
                ]);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() !== FALSE && $log_id > 0
            ? $log_id
            : NULL;
    }

    public function calculate_entry_hash($row)
    {
        $row = is_object($row) ? get_object_vars($row) : (array) $row;
        $canonical = [];
        foreach ([
            'event_uuid',
            'actor_user_id',
            'event_type',
            'object_type',
            'object_id',
            'action',
            'outcome',
            'before_hash',
            'after_hash',
            'changes_json',
            'ip_address',
            'user_agent',
            'request_id',
            'previous_hash',
            'created_at',
        ] as $field) {
            $value = array_key_exists($field, $row) ? $row[$field] : NULL;
            if ($field === 'actor_user_id') {
                $value = $value !== NULL ? (int) $value : NULL;
            } elseif ($field === 'changes_json' && $value !== NULL) {
                $decoded = json_decode((string) $value, TRUE);
                $value = json_last_error() === JSON_ERROR_NONE
                    ? json_encode(
                        $this->canonicalize_json_value($decoded),
                        JSON_UNESCAPED_SLASHES
                            | JSON_UNESCAPED_UNICODE
                            | JSON_INVALID_UTF8_SUBSTITUTE
                    )
                    : (string) $value;
            } elseif ($value !== NULL) {
                $value = (string) $value;
            }
            $canonical[$field] = $value;
        }

        return hash(
            'sha256',
            json_encode(
                $canonical,
                JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE
                    | JSON_INVALID_UTF8_SUBSTITUTE
            )
        );
    }

    private function canonicalize_json_value($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        $canonical = [];
        foreach ($value as $key => $item) {
            $canonical[$key] = $this->canonicalize_json_value($item);
        }

        $is_list = empty($canonical)
            || array_keys($canonical) === range(0, count($canonical) - 1);
        if (!$is_list) {
            ksort($canonical, SORT_STRING);
        }

        return $canonical;
    }

    public function verify_chain()
    {
        if (!$this->schema_ready()) {
            return [
                'valid' => FALSE,
                'checked' => 0,
                'first_invalid_id' => NULL,
                'head_hash' => self::GENESIS_HASH,
                'reason' => 'schema_missing',
            ];
        }

        $rows = $this->db->order_by('id', 'ASC')->get($this->table)->result();
        $previous = self::GENESIS_HASH;
        $checked = 0;

        foreach ($rows as $row) {
            $checked++;
            if (!hash_equals($previous, (string) $row->previous_hash)
                || !hash_equals((string) $row->entry_hash, $this->calculate_entry_hash($row))) {
                return [
                    'valid' => FALSE,
                    'checked' => $checked,
                    'first_invalid_id' => (int) $row->id,
                    'head_hash' => $previous,
                    'reason' => 'entry_mismatch',
                ];
            }
            $previous = (string) $row->entry_hash;
        }

        $state = $this->db
            ->where('id', 1)
            ->get($this->state_table)
            ->row();
        if (!$state
            || !hash_equals($previous, (string) $state->current_hash)
            || ($checked === 0 && $state->last_log_id !== NULL)
            || ($checked > 0 && (int) $state->last_log_id !== (int) end($rows)->id)) {
            return [
                'valid' => FALSE,
                'checked' => $checked,
                'first_invalid_id' => NULL,
                'head_hash' => $previous,
                'reason' => 'chain_state_mismatch',
            ];
        }

        return [
            'valid' => TRUE,
            'checked' => $checked,
            'first_invalid_id' => NULL,
            'head_hash' => $previous,
            'reason' => NULL,
        ];
    }

    private function event_uuid()
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (Exception $exception) {
            return substr(hash('sha256', uniqid('', TRUE) . microtime(TRUE)), 0, 32);
        }
    }
}
