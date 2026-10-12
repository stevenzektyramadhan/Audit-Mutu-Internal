<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_guard
{
    protected $ci;
    protected $organization_branding_loaded = FALSE;

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    public function check()
    {
        if (!$this->ci->session->userdata('user_id')) {
            redirect('auth');
            exit;
        }

        $this->load_organization_branding();
    }

    public function only(array $roles)
    {
        $this->check();

        $role = $this->ci->session->userdata('role');
        if (!in_array($role, $roles, true)) {
            show_error('Akses ditolak.', 403, 'Forbidden');
        }
    }

    protected function load_organization_branding()
    {
        if ($this->organization_branding_loaded) {
            return;
        }

        $this->ci->load->model('System_settings_model');
        require_once APPPATH . 'services/Organization_logo_service.php';
        $organization_logo_service = new Organization_logo_service();
        $this->ci->load->vars(['organization_logo_url' => $organization_logo_service->public_url($this->ci->System_settings_model->profile())]);
        $this->organization_branding_loaded = TRUE;
    }
}
