<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Response_security
{
    private $csp = "default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'; img-src 'self' data:; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; connect-src 'self'; frame-src 'none'; manifest-src 'self'";

    public function set_headers()
    {
        $ci = &get_instance();
        $ci->output->set_header('Cache-Control: private, no-store, max-age=0, must-revalidate');
        $ci->output->set_header('Pragma: no-cache');
        $ci->output->set_header('Expires: 0');
        $ci->output->set_header('X-Content-Type-Options: nosniff');
        $ci->output->set_header('Referrer-Policy: same-origin');
        $ci->output->set_header('X-Frame-Options: DENY');
        $ci->output->set_header('Content-Security-Policy-Report-Only: ' . $this->csp);
    }
}
