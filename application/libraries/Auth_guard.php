<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_guard
{
    protected $ci;
    protected $user_model;
    protected $auth_security;
    protected $authorization_policy;
    protected $validated = FALSE;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('User_model');
        $this->ci->load->library('auth_security');
        $this->ci->load->library('authorization_policy');
        $this->user_model = $this->ci->User_model;
        $this->auth_security = $this->ci->auth_security;
        $this->authorization_policy = $this->ci->authorization_policy;
    }

    public function check()
    {
        if ($this->validated) {
            return;
        }

        $user_id = (int) $this->ci->session->userdata('user_id');
        $started_at = (int) $this->ci->session->userdata('auth_started_at');
        $last_activity_at = (int) $this->ci->session->userdata('last_activity_at');
        $session_version = (int) $this->ci->session->userdata('session_version');
        $email = (string) $this->ci->session->userdata('email');
        $now = time();

        if (!$user_id || !$started_at || !$last_activity_at || !$session_version) {
            $this->invalidate_and_redirect(NULL, 'missing_auth_state');
        }

        $idle_timeout = max(60, (int) $this->ci->config->item('auth_idle_timeout'));
        if (($now - $last_activity_at) > $idle_timeout) {
            $this->invalidate_and_redirect(
                Auth_security::EVENT_SESSION_IDLE_EXPIRED,
                'idle_timeout',
                $email,
                $user_id
            );
        }

        $absolute_timeout = max($idle_timeout, (int) $this->ci->config->item('auth_absolute_timeout'));
        if (($now - $started_at) > $absolute_timeout) {
            $this->invalidate_and_redirect(
                Auth_security::EVENT_SESSION_ABSOLUTE_EXPIRED,
                'absolute_timeout',
                $email,
                $user_id
            );
        }

        $user = $this->user_model->find($user_id);
        $session_role = (string) $this->ci->session->userdata('role');

        if (!$user
            || (int) $user->is_active !== 1
            || (int) $user->session_version !== $session_version
            || (string) $user->role !== $session_role) {
            $this->invalidate_and_redirect(
                Auth_security::EVENT_SESSION_REVOKED,
                'account_changed',
                $email,
                $user ? $user_id : NULL
            );
        }

        $this->ci->session->set_userdata('last_activity_at', $now);
        $this->validated = TRUE;
    }

    public function require_capability($capability)
    {
        $this->check();

        $user_id = (int) $this->ci->session->userdata('user_id');
        if (!$this->authorization_policy->allows($user_id, $capability)) {
            show_error('Akses ditolak.', 403, 'Forbidden');
            exit;
        }
    }

    public function require_capability_in_organization_unit(
        $capability,
        $organization_unit_id,
        $on_date = NULL
    ) {
        $this->check();

        $user_id = (int) $this->ci->session->userdata('user_id');
        if (!$this->authorization_policy->allowsInOrganizationUnit(
            $user_id,
            $capability,
            (int) $organization_unit_id,
            $on_date
        )) {
            show_error('Akses ditolak.', 403, 'Forbidden');
            exit;
        }
    }

    private function invalidate_and_redirect($event_type, $reason, $email = '', $user_id = NULL)
    {
        if ($event_type !== NULL) {
            $this->auth_security->record_event($event_type, $email, $user_id, $reason);
        }

        $this->ci->session->sess_destroy();
        redirect('auth');
        exit;
    }
}
