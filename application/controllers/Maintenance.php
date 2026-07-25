<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Maintenance extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->input->is_cli_request()) {
            show_404();
            exit;
        }
        $this->load->library('file_security');
        $this->load->model('Security_audit_log_model');
    }

    public function purge_files($limit = 100)
    {
        $purged = $this->file_security->purge_expired((int) $limit);
        fwrite(STDOUT, 'Purged retired files: ' . $purged . PHP_EOL);
    }

    public function verify_audit_log()
    {
        $result = $this->Security_audit_log_model->verify_chain();
        fwrite(
            $result['valid'] ? STDOUT : STDERR,
            json_encode($result, JSON_UNESCAPED_SLASHES) . PHP_EOL
        );

        if (!$result['valid']) {
            exit(1);
        }
    }
}
