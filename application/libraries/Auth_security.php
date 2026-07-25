<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_security
{
    const EVENT_LOGIN_FAILED = 'login_failed';
    const EVENT_LOGIN_SUCCEEDED = 'login_succeeded';
    const EVENT_LOGIN_THROTTLED = 'login_throttled';
    const EVENT_LOGOUT = 'logout';
    const EVENT_SESSION_IDLE_EXPIRED = 'session_idle_expired';
    const EVENT_SESSION_ABSOLUTE_EXPIRED = 'session_absolute_expired';
    const EVENT_SESSION_REVOKED = 'session_revoked';
    const EVENT_AUTHORIZATION_OVERRIDE = 'authorization_override';

    protected $ci;
    protected $event_model;
    protected $audit_logger;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Auth_security_event_model');
        $this->ci->load->library('audit_logger');
        $this->event_model = $this->ci->Auth_security_event_model;
        $this->audit_logger = $this->ci->audit_logger;
    }

    public function check_login_throttle($email)
    {
        $window = max(60, (int) $this->ci->config->item('auth_login_window'));
        $email_limit = max(1, (int) $this->ci->config->item('auth_login_email_limit'));
        $ip_limit = max(1, (int) $this->ci->config->item('auth_login_ip_limit'));
        $since_timestamp = time() - $window;
        $since = date('Y-m-d H:i:s', $since_timestamp);
        $email_hash = $this->hash_identifier('email', $this->normalize_email($email));
        $ip_hash = $this->hash_identifier('ip', $this->client_ip());

        $most_recent_success = $this->event_model->most_recent_email_success($email_hash, $since);
        $email_failure_since = $most_recent_success ?: $since;
        $email_count = $this->event_model->count_recent_email_failures($email_hash, $email_failure_since);
        $ip_count = $this->event_model->count_recent_ip_failures($ip_hash, $since);

        if ($email_count < $email_limit && $ip_count < $ip_limit) {
            return ['allowed' => TRUE, 'retry_after' => 0];
        }

        $oldest = $email_count >= $email_limit
            ? $this->event_model->oldest_recent_email_failure($email_hash, $email_failure_since)
            : $this->event_model->oldest_recent_ip_failure($ip_hash, $since);
        $oldest_timestamp = $oldest ? strtotime($oldest) : time();
        $retry_after = max(1, ($oldest_timestamp + $window) - time());

        return ['allowed' => FALSE, 'retry_after' => $retry_after];
    }

    public function record_event($event_type, $email = '', $user_id = NULL, $reason = NULL)
    {
        $allowed_events = [
            self::EVENT_LOGIN_FAILED,
            self::EVENT_LOGIN_SUCCEEDED,
            self::EVENT_LOGIN_THROTTLED,
            self::EVENT_LOGOUT,
            self::EVENT_SESSION_IDLE_EXPIRED,
            self::EVENT_SESSION_ABSOLUTE_EXPIRED,
            self::EVENT_SESSION_REVOKED,
            self::EVENT_AUTHORIZATION_OVERRIDE,
        ];

        if (!in_array($event_type, $allowed_events, TRUE)) {
            return FALSE;
        }

        $request_id = defined('AMI_REQUEST_ID') ? (string) AMI_REQUEST_ID : '';
        $request_id = preg_replace('/[^a-f0-9]/i', '', $request_id);
        if ($request_id === '') {
            $request_id = bin2hex(random_bytes(16));
        }

        $clean_reason = $reason === NULL
            ? NULL
            : substr(preg_replace('/[^a-z0-9_]/', '', strtolower((string) $reason)), 0, 40);

        $recorded = $this->event_model->record([
            'user_id' => $user_id ? (int) $user_id : NULL,
            'email_hash' => $this->hash_identifier('email', $this->normalize_email($email)),
            'ip_hash' => $this->hash_identifier('ip', $this->client_ip()),
            'user_agent_hash' => $this->hash_identifier('user_agent', $this->user_agent()),
            'event_type' => $event_type,
            'reason' => $clean_reason !== '' ? $clean_reason : NULL,
            'request_id' => substr($request_id, 0, 64),
        ]);

        $actions = [
            self::EVENT_LOGIN_FAILED => 'authenticate',
            self::EVENT_LOGIN_SUCCEEDED => 'authenticate',
            self::EVENT_LOGIN_THROTTLED => 'authenticate',
            self::EVENT_LOGOUT => 'logout',
            self::EVENT_SESSION_IDLE_EXPIRED => 'expire',
            self::EVENT_SESSION_ABSOLUTE_EXPIRED => 'expire',
            self::EVENT_SESSION_REVOKED => 'revoke',
            self::EVENT_AUTHORIZATION_OVERRIDE => 'override',
        ];
        $blocked = in_array(
            $event_type,
            [self::EVENT_LOGIN_FAILED, self::EVENT_LOGIN_THROTTLED],
            TRUE
        );
        $audit_actor = in_array(
            $event_type,
            [
                self::EVENT_LOGIN_SUCCEEDED,
                self::EVENT_LOGOUT,
                self::EVENT_SESSION_IDLE_EXPIRED,
                self::EVENT_SESSION_ABSOLUTE_EXPIRED,
                self::EVENT_SESSION_REVOKED,
                self::EVENT_AUTHORIZATION_OVERRIDE,
            ],
            TRUE
        ) ? (int) $user_id : 0;

        $audit_id = $this->audit_logger->record(
            $event_type,
            $user_id ? 'user_account' : 'authentication',
            $user_id ? (int) $user_id : NULL,
            $actions[$event_type],
            NULL,
            $event_type === self::EVENT_LOGIN_SUCCEEDED ? ['authenticated' => TRUE] : NULL,
            ['reason_code' => $clean_reason],
            $audit_actor,
            $blocked ? 'blocked' : 'success'
        );

        return $recorded && $audit_id !== NULL;
    }

    private function normalize_email($email)
    {
        return strtolower(trim((string) $email));
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

    private function hash_identifier($domain, $value)
    {
        $key = (string) $this->ci->config->item('encryption_key');
        if ($key === '') {
            // Development-only deterministic fallback. Production already
            // fails closed when APP_ENCRYPTION_KEY is missing.
            $key = hash('sha256', FCPATH . 'ami-auth-security-development');
        }

        return hash_hmac('sha256', $domain . "\0" . (string) $value, $key);
    }
}
