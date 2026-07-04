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
        $role = isset($filters['role']) && in_array($filters['role'], self::ALLOWED_ROLES, TRUE)
            ? $filters['role']
            : '';

        return $this->user_model->get_all([
            'q' => trim(isset($filters['q']) ? $filters['q'] : ''),
            'role' => $role,
        ]);
    }

    public function get_lpmpi_accounts($filters = [])
    {
        $role = isset($filters['role']) && in_array($filters['role'], ['auditor', 'auditee'], TRUE)
            ? $filters['role']
            : '';

        return $this->user_model->get_lpmpi_accounts([
            'q' => trim(isset($filters['q']) ? $filters['q'] : ''),
            'role' => $role,
        ]);
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
        $data = $this->normalize_unit_fields($data);

        if ($this->user_model->create($data)) {
            return ['success' => true, 'message' => 'Pengguna berhasil ditambahkan.'];
        }

        return ['success' => false, 'message' => 'Gagal menambahkan pengguna.'];
    }

    public function create_lpmpi_account($data)
    {
        $data = $this->normalize($data);

        if (!$this->is_valid_lpmpi_account($data, TRUE)) {
            return ['success' => FALSE, 'message' => 'Data akun tidak valid.'];
        }

        if ($this->user_model->find_by_email($data['email'])) {
            return ['success' => FALSE, 'message' => 'Email sudah terdaftar.'];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data = $this->normalize_unit_fields($data);

        if ($this->user_model->create($data)) {
            return ['success' => TRUE, 'message' => 'Akun berhasil ditambahkan.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal menambahkan akun.'];
    }

    public function update_user($id, $data)
    {
        $user = $this->user_model->find($id);
        if (!$user) {
            return ['success' => FALSE, 'message' => 'Pengguna tidak ditemukan.'];
        }

        $has_unit_fields = array_key_exists('nama_unit', $data) || array_key_exists('jenis_unit', $data);
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

        $data = $this->normalize_unit_fields($data);
        if (!$has_unit_fields && $data['role'] === 'auditee') {
            $data['nama_unit'] = $user->nama_unit;
            $data['jenis_unit'] = $user->jenis_unit;
        }

        if ($this->user_model->update($id, $data)) {
            return ['success' => TRUE, 'message' => 'Pengguna berhasil diperbarui.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal memperbarui pengguna.'];
    }

    public function update_lpmpi_account($id, $data)
    {
        $user = $this->user_model->find($id);
        if (!$user || !in_array($user->role, ['auditor', 'auditee'], TRUE)) {
            return ['success' => FALSE, 'message' => 'Akun tidak ditemukan.'];
        }

        $data = $this->normalize($data);
        if (!$this->is_valid_lpmpi_account($data, FALSE)) {
            return ['success' => FALSE, 'message' => 'Data akun tidak valid.'];
        }

        if ($this->user_model->email_exists_except($data['email'], $id)) {
            return ['success' => FALSE, 'message' => 'Email sudah digunakan akun lain.'];
        }

        if ($data['role'] !== $user->role && $this->user_model->has_audit_assignments($id)) {
            return ['success' => FALSE, 'message' => 'Role akun tidak dapat diubah karena masih terikat pada tugas audit.'];
        }

        if ($data['password'] === '') {
            unset($data['password']);
        } else {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $data = $this->normalize_unit_fields($data);

        if ($this->user_model->update($id, $data)) {
            return ['success' => TRUE, 'message' => 'Akun berhasil diperbarui.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal memperbarui akun.'];
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

    public function delete_lpmpi_account($id, $current_user_id)
    {
        $user = $this->user_model->find($id);
        if (!$user || !in_array($user->role, ['auditor', 'auditee'], TRUE)) {
            return ['success' => FALSE, 'message' => 'Akun tidak ditemukan.'];
        }

        if ((int) $id === (int) $current_user_id) {
            return ['success' => FALSE, 'message' => 'Anda tidak dapat menghapus akun yang sedang digunakan.'];
        }

        if ($this->user_model->has_audit_assignments($id)) {
            return ['success' => FALSE, 'message' => 'Akun tidak dapat dihapus karena masih terikat pada tugas audit.'];
        }

        if ($this->user_model->delete($id)) {
            return ['success' => TRUE, 'message' => 'Akun berhasil dihapus.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal menghapus akun.'];
    }

    private function normalize($data)
    {
        return [
            'nama' => trim(isset($data['nama']) ? $data['nama'] : ''),
            'email' => strtolower(trim(isset($data['email']) ? $data['email'] : '')),
            'password' => isset($data['password']) ? (string) $data['password'] : '',
            'role' => isset($data['role']) ? $data['role'] : '',
            'nama_unit' => trim(isset($data['nama_unit']) ? $data['nama_unit'] : ''),
            'jenis_unit' => isset($data['jenis_unit']) ? $data['jenis_unit'] : '',
        ];
    }

    private function is_valid($data, $password_required)
    {
        return $data['nama'] !== ''
            && filter_var($data['email'], FILTER_VALIDATE_EMAIL) !== FALSE
            && (!$password_required || $data['password'] !== '')
            && in_array($data['role'], self::ALLOWED_ROLES, TRUE);
    }

    private function is_valid_lpmpi_account($data, $password_required)
    {
        if ($data['nama'] === ''
            || filter_var($data['email'], FILTER_VALIDATE_EMAIL) === FALSE
            || ($password_required && $data['password'] === '')
            || !in_array($data['role'], ['auditor', 'auditee'], TRUE)) {
            return FALSE;
        }

        if ($data['role'] === 'auditee') {
            return $data['nama_unit'] !== ''
                && in_array($data['jenis_unit'], ['prodi', 'unit', 'lembaga'], TRUE);
        }

        return $data['jenis_unit'] === ''
            || in_array($data['jenis_unit'], ['prodi', 'unit', 'lembaga'], TRUE);
    }

    private function normalize_unit_fields($data)
    {
        if ($data['role'] !== 'auditee') {
            $data['nama_unit'] = NULL;
            $data['jenis_unit'] = NULL;
            return $data;
        }

        $data['nama_unit'] = $data['nama_unit'] !== '' ? $data['nama_unit'] : NULL;
        $data['jenis_unit'] = $data['jenis_unit'] !== '' ? $data['jenis_unit'] : NULL;

        return $data;
    }
}
