<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_logger
{
    private $ci;
    private $allowed_metadata = ['reason', 'resource_state', 'operation'];

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Audit_log_model');
    }

    public function log($event_type, $outcome, $resource_type = NULL, $resource_id = NULL, array $metadata = [])
    {
        if (is_cli()) return FALSE;

        $safe_metadata = $this->filter_metadata($metadata);
        $data = [
            'event_type' => $this->limit_scalar($event_type, 64),
            'outcome' => $this->limit_scalar($outcome, 32),
            'actor_user_id' => $this->actor_user_id(),
            'actor_role' => $this->limit_scalar($this->ci->session->userdata('role'), 32),
            'resource_type' => $this->limit_scalar($resource_type, 64),
            'resource_id' => $this->resource_id($resource_id),
            'request_method' => $this->limit_scalar($this->ci->input->method(TRUE), 8),
            'route' => $this->limit_scalar($this->ci->uri->uri_string(), 255),
            'client_ip' => $this->limit_scalar(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : NULL, 45),
            'metadata_json' => empty($safe_metadata) ? NULL : json_encode($safe_metadata),
        ];

        try {
            return $this->ci->Audit_log_model->append($data);
        } catch (Throwable $exception) {
            log_message('error', 'Audit log failed: ' . $exception->getMessage());
            return FALSE;
        }
    }

    private function actor_user_id()
    {
        $user_id = $this->ci->session->userdata('user_id');
        return $user_id ? (int) $user_id : NULL;
    }

    private function resource_id($resource_id)
    {
        if ($resource_id === NULL || $resource_id === FALSE || $resource_id === '') return NULL;
        return ctype_digit((string) $resource_id) ? (int) $resource_id : NULL;
    }

    private function filter_metadata(array $metadata)
    {
        $safe = [];
        foreach ($this->allowed_metadata as $key) {
            if (array_key_exists($key, $metadata) && is_scalar($metadata[$key])) {
                $safe[$key] = $this->limit_scalar($metadata[$key], 128);
            }
        }
        return $safe;
    }

    private function limit_scalar($value, $limit)
    {
        if ($value === NULL || $value === FALSE) return NULL;
        $value = (string) $value;
        return mb_substr($value, 0, $limit, 'UTF-8');
    }
}
