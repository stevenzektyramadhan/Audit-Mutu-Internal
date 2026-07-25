<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'config/security_bootstrap.php';

class MY_Log extends CI_Log
{
    public function write_log($level, $msg)
    {
        $message = '[request_id=' . ami_request_id() . '] ' . ami_sanitize_log_message($msg);
        return parent::write_log($level, $message);
    }
}
