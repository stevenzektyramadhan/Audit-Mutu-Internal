<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_service
{
    const INVALID_CREDENTIALS_MESSAGE = 'Email atau password salah.';
    const THROTTLED_MESSAGE = 'Terlalu banyak percobaan login. Coba lagi beberapa saat.';
    const DUMMY_PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';

    protected $ci;
    protected $user_model;
    protected $auth_security;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('User_model');
        $this->ci->load->library('auth_security');
        $this->user_model = $this->ci->User_model;
        $this->auth_security = $this->ci->auth_security;
    }

    public function login($email, $password)
    {
        $email = strtolower(trim((string) $email));
        $throttle = $this->auth_security->check_login_throttle($email);

        if (!$throttle['allowed']) {
            $this->auth_security->record_event(
                Auth_security::EVENT_LOGIN_THROTTLED,
                $email,
                NULL,
                'temporary_lock'
            );

            return [
                'success' => FALSE,
                'message' => self::THROTTLED_MESSAGE,
                'retry_after' => (int) $throttle['retry_after'],
            ];
        }

        $user = $this->user_model->find_by_email($email);
        $password_hash = $user ? $user->password : self::DUMMY_PASSWORD_HASH;
        $password_valid = password_verify((string) $password, $password_hash);

        if (!$user || !$password_valid) {
            $this->auth_security->record_event(
                Auth_security::EVENT_LOGIN_FAILED,
                $email,
                $user ? $user->id : NULL,
                'credentials_invalid'
            );

            return ['success' => FALSE, 'message' => self::INVALID_CREDENTIALS_MESSAGE];
        }

        if ((int) $user->is_active !== 1) {
            $this->auth_security->record_event(
                Auth_security::EVENT_LOGIN_FAILED,
                $email,
                $user->id,
                'account_inactive'
            );

            return ['success' => FALSE, 'message' => self::INVALID_CREDENTIALS_MESSAGE];
        }

        if (password_needs_rehash($user->password, PASSWORD_DEFAULT)) {
            $this->user_model->update_password_hash(
                $user->id,
                password_hash((string) $password, PASSWORD_DEFAULT)
            );
        }

        $this->ci->session->unset_userdata([
            'user_id',
            'nama',
            'profile_photo_path',
            'email',
            'role',
            'logged_in',
            'auth_started_at',
            'last_activity_at',
            'session_version',
        ]);
        $this->ci->session->sess_regenerate(TRUE);

        $now = time();
        $session_data = [
            'user_id' => $user->id,
            'nama' => $user->nama,
            'profile_photo_path' => $user->profile_photo_path,
            'email' => $user->email,
            'role' => $user->role,
            'logged_in' => TRUE,
            'auth_started_at' => $now,
            'last_activity_at' => $now,
            'session_version' => (int) $user->session_version,
        ];

        $this->ci->session->set_userdata($session_data);
        $this->user_model->update_last_login($user->id);
        $this->auth_security->record_event(
            Auth_security::EVENT_LOGIN_SUCCEEDED,
            $user->email,
            $user->id,
            'credentials_valid'
        );

        return ['success' => TRUE, 'message' => 'Login berhasil.'];
    }

    public function logout()
    {
        $user_id = $this->ci->session->userdata('user_id');
        $email = (string) $this->ci->session->userdata('email');

        if ($user_id) {
            $user = $this->user_model->find($user_id);
            $this->auth_security->record_event(
                Auth_security::EVENT_LOGOUT,
                $email,
                $user ? $user_id : NULL,
                'user_initiated'
            );
        }

        $this->ci->session->sess_destroy();
    }
}
