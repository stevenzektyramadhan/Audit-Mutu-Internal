<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'config/security_bootstrap.php';

class MY_Exceptions extends CI_Exceptions
{
    public function show_error($heading, $message, $template = 'error_general', $status_code = 500)
    {
        if ($this->is_production_server_error($status_code)) {
            $heading = 'Permintaan tidak dapat diproses';
            $message = 'Terjadi kesalahan internal. Sampaikan ID berikut kepada administrator: '
                . ami_request_id()
                . '.';
            $template = 'error_general';
        }

        $heading = $this->encode_output($heading);
        $message = is_array($message)
            ? array_map([$this, 'encode_output'], $message)
            : $this->encode_output($message);

        return parent::show_error($heading, $message, $template, $status_code);
    }

    public function show_exception($exception)
    {
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            set_status_header(500);
            echo $this->show_error(
                'Permintaan tidak dapat diproses',
                'Terjadi kesalahan internal.',
                'error_general',
                500
            );
            return;
        }

        parent::show_exception($exception);
    }

    public function show_php_error($severity, $message, $filepath, $line)
    {
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            echo $this->show_error(
                'Permintaan tidak dapat diproses',
                'Terjadi kesalahan internal.',
                'error_general',
                500
            );
            return;
        }

        parent::show_php_error($severity, $message, $filepath, $line);
    }

    private function is_production_server_error($status_code)
    {
        return defined('ENVIRONMENT')
            && ENVIRONMENT === 'production'
            && (int) $status_code >= 500;
    }

    protected function encode_output($value)
    {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
            TRUE
        );
    }
}
