<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_service
{
    protected $ci;
    protected $user_model;
    protected $password_reset_token_model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('User_model');
        $this->ci->load->model('Password_reset_token_model');
        $this->user_model = $this->ci->User_model;
        $this->password_reset_token_model = $this->ci->Password_reset_token_model;
    }

    public function login($email, $password)
    {
        $user = $this->user_model->find_by_email($email);

        if (!$user) {
            return ['success' => false, 'message' => 'Email atau password salah.'];
        }

        if (!password_verify($password, $user->password)) {
            return ['success' => false, 'message' => 'Email atau password salah.'];
        }

        $session_data = [
            'user_id' => $user->id,
            'nama' => $user->nama,
            'profile_photo_path' => $user->profile_photo_path ?? NULL,
            'email' => $user->email,
            'role' => $user->role,
            'logged_in' => TRUE,
        ];

        $this->ci->session->set_userdata($session_data);
        $this->ci->session->sess_regenerate(TRUE);

        return ['success' => true, 'message' => 'Login berhasil.'];
    }

    public function request_password_reset($email)
    {
        $generic_result = ['success' => TRUE, 'message' => 'Jika alamat email terdaftar, instruksi pengaturan ulang kata sandi telah dikirim.'];
        $user = $this->user_model->find_by_email($email);

        if (!$user) {
            return $generic_result;
        }

        $this->ci->config->load('email');
        if (!$this->ci->config->item('password_reset_mail_enabled')) {
            $this->invalidate_password_reset_tokens((int) $user->id);
            return $generic_result;
        }

        try {
            $token = bin2hex(random_bytes(32));
        } catch (Throwable $exception) {
            $this->invalidate_password_reset_tokens((int) $user->id);
            log_message('error', 'Password reset token generation failed.');
            return $generic_result;
        }

        $this->ci->db->trans_begin();
        try {
            $this->password_reset_token_model->invalidate_active_for_user((int) $user->id);
            if (!$this->password_reset_token_model->create((int) $user->id, hash('sha256', $token), date('Y-m-d H:i:s', time() + 3600))) {
                throw new RuntimeException('Unable to store password reset token.');
            }

            if (!$this->send_password_reset_email($user, $token)) {
                if (!$this->password_reset_token_model->invalidate_active_for_user((int) $user->id)) {
                    throw new RuntimeException('Unable to invalidate password reset token.');
                }
                $this->ci->db->trans_commit();
                log_message('error', 'Password reset email delivery failed.');
                return $generic_result;
            }

            if (!$this->ci->db->trans_status()) {
                throw new RuntimeException('Unable to complete password reset request transaction.');
            }
            $this->ci->db->trans_commit();
        } catch (Throwable $exception) {
            $this->ci->db->trans_rollback();
            $this->invalidate_password_reset_tokens((int) $user->id);
            log_message('error', 'Password reset request could not be completed.');
        }

        return $generic_result;
    }

    public function has_active_password_reset_token($token)
    {
        if (!is_string($token) || !preg_match('/\A[a-f0-9]{64}\z/', $token)) {
            return FALSE;
        }

        return (bool) $this->password_reset_token_model->find_active_by_hash(hash('sha256', $token));
    }

    public function reset_password($token, $password)
    {
        if (!is_string($token) || !preg_match('/\A[a-f0-9]{64}\z/', $token)) {
            return ['success' => FALSE, 'message' => 'Tautan pengaturan ulang kata sandi tidak valid atau telah kedaluwarsa.'];
        }

        $this->ci->db->trans_begin();
        try {
            $reset_token = $this->password_reset_token_model->find_active_by_hash_for_update(hash('sha256', $token));
            if (!$reset_token) {
                $this->ci->db->trans_rollback();
                return ['success' => FALSE, 'message' => 'Tautan pengaturan ulang kata sandi tidak valid atau telah kedaluwarsa.'];
            }

            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            if (!is_string($password_hash) || !$this->user_model->update_password((int) $reset_token->user_id, $password_hash)) {
                throw new RuntimeException('Unable to update password.');
            }

            if (!$this->password_reset_token_model->invalidate_active_for_user((int) $reset_token->user_id)) {
                throw new RuntimeException('Unable to consume password reset token.');
            }
            if (!$this->ci->db->trans_status()) {
                throw new RuntimeException('Unable to complete password reset transaction.');
            }
            $this->ci->db->trans_commit();
            return ['success' => TRUE, 'message' => 'Kata sandi berhasil diperbarui. Silakan masuk.'];
        } catch (Throwable $exception) {
            $this->ci->db->trans_rollback();
            log_message('error', 'Password reset could not be completed.');
            return ['success' => FALSE, 'message' => 'Tautan pengaturan ulang kata sandi tidak valid atau telah kedaluwarsa.'];
        }
    }

    private function invalidate_password_reset_tokens($user_id)
    {
        $this->ci->db->trans_begin();
        try {
            $this->password_reset_token_model->invalidate_active_for_user($user_id);
            $this->ci->db->trans_commit();
        } catch (Throwable $exception) {
            $this->ci->db->trans_rollback();
            log_message('error', 'Password reset token invalidation failed.');
        }
    }

    private function send_password_reset_email($user, $token)
    {
        $api_url = 'https://api.brevo.com/v3/smtp/email';
        $reset_url = site_url('auth/reset-password/' . rawurlencode($token));
        $sender = [
            'email' => $this->ci->config->item('password_reset_from_address'),
        ];
        $from_name = trim((string) $this->ci->config->item('password_reset_from_name'));
        if ($from_name !== '') {
            $sender['name'] = $from_name;
        }

        $body = json_encode([
            'sender' => $sender,
            'to' => [[
                'email' => $user->email,
            ]],
            'subject' => 'Pengaturan ulang kata sandi AMI',
            'textContent' => "Gunakan tautan berikut untuk mengatur ulang kata sandi Anda dalam 60 menit:\n\n" . $reset_url,
        ]);

        if (!is_string($body)) {
            log_message('error', 'Password reset email delivery request could not be encoded.');
            return FALSE;
        }

        $send_warning = FALSE;
        $error_handler = function ($errno, $errstr) use (&$send_warning) {
            if (!(error_reporting() & $errno)) {
                return FALSE;
            }

            if (in_array($errno, [E_WARNING, E_USER_WARNING, E_CORE_WARNING, E_COMPILE_WARNING], TRUE)) {
                $send_warning = TRUE;
                return TRUE;
            }

            return FALSE;
        };

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'api-key: ' . $this->ci->config->item('password_reset_brevo_api_key'),
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Content-Length: ' . strlen($body),
                ],
                'content' => $body,
                'ignore_errors' => TRUE,
                'timeout' => 10,
            ],
            'ssl' => [
                'verify_peer' => TRUE,
                'verify_peer_name' => TRUE,
                'allow_self_signed' => FALSE,
            ],
        ]);

        $response = FALSE;
        $http_response_header = [];
        set_error_handler($error_handler);
        try {
            $response = file_get_contents($api_url, FALSE, $context);
        } finally {
            restore_error_handler();
        }

        if ($send_warning || !is_string($response)) {
            log_message('error', 'Password reset email delivery request failed.');
            return FALSE;
        }

        $status_code = $this->parse_http_status_code($http_response_header);
        if ($status_code !== 201) {
            $brevo_reply_present = is_string($response) && $response !== '';
            $brevo_reply_category = $brevo_reply_present ? 'present' : 'empty';
            log_message('error', 'Password reset email delivery returned an unsuccessful status. status_code=' . ($status_code === NULL ? 'unknown' : (string) $status_code) . ' response_present=' . ($brevo_reply_present ? 'yes' : 'no') . ' response_category=' . $brevo_reply_category);
            return FALSE;
        }

        $decoded_response = json_decode($response, TRUE);
        if (!is_array($decoded_response) || !isset($decoded_response['messageId']) || !is_string($decoded_response['messageId']) || trim($decoded_response['messageId']) === '') {
            log_message('error', 'Password reset email delivery response was invalid.');
            return FALSE;
        }

        return TRUE;
    }

    private function parse_http_status_code($headers)
    {
        if (!is_array($headers) || !isset($headers[0]) || !is_string($headers[0])) {
            return NULL;
        }

        for ($index = count($headers) - 1; $index >= 0; $index--) {
            if (is_string($headers[$index]) && preg_match('/\AHTTP\/\S+\s+(\d{3})\b/', $headers[$index], $matches)) {
                return (int) $matches[1];
            }
        }

        return NULL;
    }
}
