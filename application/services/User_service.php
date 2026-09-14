<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_service
{
    const ALLOWED_ROLES = ['super_admin', 'admin_lpmpi', 'auditor', 'auditee'];

    protected $ci;
    protected $user_model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('User_model');
        $this->user_model = $this->ci->User_model;
    }

    public function get_all_users($filters = [])
    {
        $allowed_roles = $this->allowed_managed_roles(isset($filters['actor_role']) ? $filters['actor_role'] : '');
        if (!$allowed_roles) {
            return [];
        }

        $role = isset($filters['role']) && in_array($filters['role'], self::ALLOWED_ROLES, TRUE)
            ? $filters['role']
            : '';
        $query_role = in_array($role, $allowed_roles, TRUE) ? $role : '';

        $users = $this->user_model->get_all([
            'q' => trim(isset($filters['q']) ? $filters['q'] : ''),
            'role' => $query_role,
        ]);

        return array_values(array_filter($users, function ($user) use ($allowed_roles) {
            return isset($user->role) && in_array($user->role, $allowed_roles, TRUE);
        }));
    }

    public function get_user($id)
    {
        return $this->user_model->find($id);
    }

    public function create_user($data)
    {
        $actor_role = isset($data['actor_role']) ? $data['actor_role'] : '';
        $data = $this->normalize($data);

        if (!$this->is_valid($data, TRUE)) {
            return $this->fail('Data pengguna tidak valid.');
        }

        if (!$this->can_manage_role($actor_role, $data['role'])) {
            return $this->fail('Role pengguna tidak boleh dikelola oleh akun ini.');
        }

        if ($this->user_model->find_by_email($data['email'])) {
            return $this->fail('Email sudah terdaftar.');
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        if ($this->user_model->create($data)) {
            return ['success' => TRUE, 'message' => 'Pengguna berhasil ditambahkan.'];
        }

        return $this->fail('Gagal menambahkan pengguna.');
    }

    public function update_user($id, $data)
    {
        $actor_role = isset($data['actor_role']) ? $data['actor_role'] : '';
        $user = $this->user_model->find($id);
        if (!$user) {
            return $this->fail('Pengguna tidak ditemukan.');
        }

        $data = $this->normalize($data);
        if (!$this->is_valid($data, FALSE)) {
            return $this->fail('Data pengguna tidak valid.');
        }

        if (!$this->can_manage_role($actor_role, $user->role) || !$this->can_manage_role($actor_role, $data['role'])) {
            return $this->fail('Role pengguna tidak boleh dikelola oleh akun ini.');
        }

        if ($this->user_model->email_exists_except($data['email'], $id)) {
            return $this->fail('Email sudah digunakan pengguna lain.');
        }

        if ($user->role === 'super_admin'
            && $data['role'] !== 'super_admin'
            && $this->user_model->count_by_role('super_admin') <= 1) {
            return $this->fail('Super Admin terakhir tidak dapat diubah ke role lain.');
        }

        if ($data['role'] !== $user->role) {
            $dependency = $this->user_model->user_dependency_category($id);
            if ($dependency !== '') {
                return $this->fail('Role pengguna tidak dapat diubah karena masih terikat pada ' . $dependency . '.');
            }
        }

        if ($data['password'] === '') {
            unset($data['password']);
        } else {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if ($this->user_model->update($id, $data)) {
            return ['success' => TRUE, 'message' => 'Pengguna berhasil diperbarui.'];
        }

        return $this->fail('Gagal memperbarui pengguna.');
    }

    public function delete_user($id, $current_user_id, $actor_role = '')
    {
        $user = $this->user_model->find($id);
        if (!$user) {
            return $this->fail('Pengguna tidak ditemukan.');
        }

        if (!$this->can_manage_role($actor_role, $user->role)) {
            return $this->fail('Role pengguna tidak boleh dikelola oleh akun ini.');
        }

        if ((int) $id === (int) $current_user_id) {
            return $this->fail('Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        if ($user->role === 'super_admin') {
            return $this->fail('Super Admin tidak dapat dihapus.');
        }

        $dependency = $this->user_model->user_dependency_category($id);
        if ($dependency !== '') {
            return $this->fail('Pengguna tidak dapat dihapus karena masih terikat pada ' . $dependency . '.');
        }

        if ($this->user_model->delete($id)) {
            return ['success' => TRUE, 'message' => 'Pengguna berhasil dihapus.'];
        }

        return $this->fail('Gagal menghapus pengguna.');
    }

    public function allowed_managed_roles($actor_role)
    {
        $map = [
            'super_admin' => self::ALLOWED_ROLES,
            'admin_lpmpi' => ['auditor', 'auditee'],
        ];

        return isset($map[$actor_role]) ? $map[$actor_role] : [];
    }

    private function can_manage_role($actor_role, $target_role)
    {
        return in_array($target_role, $this->allowed_managed_roles($actor_role), TRUE);
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

    private function fail($message)
    {
        return ['success' => FALSE, 'message' => $message];
    }
}
