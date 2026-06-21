<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_service
{
    const ALLOWED_ROLES = ['super_admin', 'auditor', 'auditee'];

    protected $ci;
    protected $user_model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('User_model');
        $this->user_model = $this->ci->User_model;
    }

    public function get_all_users()
    {
        return $this->user_model->get_all();
    }

    public function get_user($id)
    {
        return $this->user_model->find($id);
    }

    public function create_user($data)
    {
        $data = $this->normalize($data);

        if (!$this->is_valid($data, TRUE)) {
            return ['success' => FALSE, 'message' => 'Data pengguna tidak valid.'];
        }

        $existing_user = $this->user_model->find_by_email($data['email']);
        if ($existing_user) {
            return ['success' => false, 'message' => 'Email sudah terdaftar.'];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        if ($this->user_model->create($data)) {
            return ['success' => true, 'message' => 'Pengguna berhasil ditambahkan.'];
        }

        return ['success' => false, 'message' => 'Gagal menambahkan pengguna.'];
    }

    public function update_user($id, $data)
    {
        $user = $this->user_model->find($id);
        if (!$user) {
            return ['success' => FALSE, 'message' => 'Pengguna tidak ditemukan.'];
        }

        $data = $this->normalize($data);
        if (!$this->is_valid($data, FALSE)) {
            return ['success' => FALSE, 'message' => 'Data pengguna tidak valid.'];
        }

        if ($this->user_model->email_exists_except($data['email'], $id)) {
            return ['success' => FALSE, 'message' => 'Email sudah digunakan pengguna lain.'];
        }

        if ($user->role === 'super_admin'
            && $data['role'] !== 'super_admin'
            && $this->user_model->count_by_role('super_admin') <= 1) {
            return ['success' => FALSE, 'message' => 'Super Admin terakhir tidak dapat diubah ke role lain.'];
        }

        if ($data['role'] !== $user->role && $this->user_model->has_audit_assignments($id)) {
            return ['success' => FALSE, 'message' => 'Role pengguna tidak dapat diubah karena masih terikat pada tugas audit.'];
        }

        if ($data['password'] === '') {
            unset($data['password']);
        } else {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if ($this->user_model->update($id, $data)) {
            return ['success' => TRUE, 'message' => 'Pengguna berhasil diperbarui.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal memperbarui pengguna.'];
    }

    public function delete_user($id, $current_user_id)
    {
        $user = $this->user_model->find($id);
        if (!$user) {
            return ['success' => FALSE, 'message' => 'Pengguna tidak ditemukan.'];
        }

        if ((int) $id === (int) $current_user_id) {
            return ['success' => FALSE, 'message' => 'Anda tidak dapat menghapus akun yang sedang digunakan.'];
        }

        if ($this->user_model->delete($id)) {
            return ['success' => TRUE, 'message' => 'Pengguna berhasil dihapus.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal menghapus pengguna.'];
    }

    private function normalize($data)
    {
        return [
            'nama' => trim(isset($data['nama']) ? $data['nama'] : ''),
            'email' => strtolower(trim(isset($data['email']) ? $data['email'] : '')),
            'password' => isset($data['password']) ? (string) $data['password'] : '',
            'role' => isset($data['role']) ? $data['role'] : '',
        ];
    }

    private function is_valid($data, $password_required)
    {
        return $data['nama'] !== ''
            && filter_var($data['email'], FILTER_VALIDATE_EMAIL) !== FALSE
            && (!$password_required || $data['password'] !== '')
            && in_array($data['role'], self::ALLOWED_ROLES, TRUE);
    }
}
