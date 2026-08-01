<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_mutation
{
    public function log_post()
    {
        if (is_cli()) return;

        $ci = &get_instance();
        if ($ci->input->method(TRUE) !== 'POST') return;

        $path = trim($ci->uri->uri_string(), '/');
        if (in_array(strtolower($path), ['auth/login', 'auth/logout'], TRUE)) return;

        try {
            $ci->load->library('Audit_logger');
            $ci->audit_logger->log('http.mutation', 'attempted', 'http', NULL, ['operation' => 'POST']);
        } catch (Throwable $exception) {
            log_message('error', 'Audit mutation log failed: ' . $exception->getMessage());
        }
    }
}
