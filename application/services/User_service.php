<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_service
{
    const ALLOWED_ROLES = ['super_admin', 'admin_lpmpi', 'auditor', 'auditee'];

    protected $ci;
    protected $user_model;
    protected $audit_logger;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('User_model');
        $this->ci->load->library('audit_logger');
        $this->user_model = $this->ci->User_model;
        $this->audit_logger = $this->ci->audit_logger;
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
            $user_id = (int) $this->ci->db->insert_id();
            $this->record_user_event('user_created', $user_id, 'create', NULL, [
                'role' => $data['role'],
                'is_active' => $data['is_active'],
            ], [
                'role_to' => $data['role'],
                'changed_fields' => ['role', 'is_active'],
            ]);
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
            $user_id = (int) $this->ci->db->insert_id();
            $this->record_user_event('user_created', $user_id, 'create', NULL, [
                'role' => $data['role'],
                'is_active' => $data['is_active'],
            ], [
                'role_to' => $data['role'],
                'changed_fields' => ['role', 'is_active'],
                'scope' => 'lpmpi_participant',
            ]);
            return ['success' => TRUE, 'message' => 'Akun berhasil ditambahkan.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal menambahkan akun.'];
    }

    public function update_user($id, $data, $current_user_id = 0)
    {
        $user = $this->user_model->find($id);
        if (!$user) {
            return ['success' => FALSE, 'message' => 'Pengguna tidak ditemukan.'];
        }

        $has_unit_fields = array_key_exists('nama_unit', $data) || array_key_exists('jenis_unit', $data);
        if (!array_key_exists('is_active', $data)) {
            $data['is_active'] = (int) $user->is_active;
        }
        $data = $this->normalize($data);
        if (!$this->is_valid($data, FALSE)) {
            return ['success' => FALSE, 'message' => 'Data pengguna tidak valid.'];
        }

        if ($this->user_model->email_exists_except($data['email'], $id)) {
            return ['success' => FALSE, 'message' => 'Email sudah digunakan pengguna lain.'];
        }

        $removes_active_super_admin = $user->role === 'super_admin'
            && (int) $user->is_active === 1
            && ($data['role'] !== 'super_admin' || $data['is_active'] !== 1);
        if ($removes_active_super_admin && $this->user_model->count_active_by_role('super_admin') <= 1) {
            return ['success' => FALSE, 'message' => 'Super Admin aktif terakhir tidak dapat dinonaktifkan atau diubah rolenya.'];
        }

        if ((int) $id === (int) $current_user_id
            && ((int) $user->is_active !== $data['is_active'] || $user->role !== $data['role'])) {
            return ['success' => FALSE, 'message' => 'Status aktif atau role akun yang sedang digunakan tidak dapat diubah sendiri.'];
        }

        if ($data['role'] !== $user->role && $this->user_model->has_audit_assignments($id)) {
            return ['success' => FALSE, 'message' => 'Role pengguna tidak dapat diubah karena masih terikat pada tugas audit.'];
        }

        $password_changed = $data['password'] !== '';
        if (!$password_changed) {
            unset($data['password']);
        } else {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $data['password_changed_at'] = date('Y-m-d H:i:s');
        }

        $security_state_changed = $password_changed
            || $data['role'] !== $user->role
            || $data['is_active'] !== (int) $user->is_active;
        if ($security_state_changed) {
            $data['session_version'] = (int) $user->session_version + 1;
        }

        $data = $this->normalize_unit_fields($data);
        if (!$has_unit_fields && $data['role'] === 'auditee') {
            $data['nama_unit'] = $user->nama_unit;
            $data['jenis_unit'] = $user->jenis_unit;
        }

        if ($this->user_model->update($id, $data)) {
            $changed_fields = [];
            if ($data['role'] !== $user->role) {
                $changed_fields[] = 'role';
            }
            if ($data['is_active'] !== (int) $user->is_active) {
                $changed_fields[] = 'is_active';
            }
            if ($password_changed) {
                $changed_fields[] = 'password';
            }
            $this->record_user_event(
                $data['role'] !== $user->role ? 'user_role_changed' : 'user_updated',
                $id,
                'update',
                ['role' => $user->role, 'is_active' => (int) $user->is_active],
                ['role' => $data['role'], 'is_active' => $data['is_active']],
                [
                    'role_from' => $user->role,
                    'role_to' => $data['role'],
                    'status_from' => (int) $user->is_active ? 'active' : 'inactive',
                    'status_to' => $data['is_active'] ? 'active' : 'inactive',
                    'changed_fields' => $changed_fields,
                ]
            );
            return ['success' => TRUE, 'message' => 'Pengguna berhasil diperbarui.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal memperbarui pengguna.'];
    }

    public function update_lpmpi_account($id, $data, $current_user_id = 0)
    {
        $user = $this->user_model->find($id);
        if (!$user || !in_array($user->role, ['auditor', 'auditee'], TRUE)) {
            return ['success' => FALSE, 'message' => 'Akun tidak ditemukan.'];
        }

        if (!array_key_exists('is_active', $data)) {
            $data['is_active'] = (int) $user->is_active;
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

        if ((int) $id === (int) $current_user_id
            && ((int) $user->is_active !== $data['is_active'] || $user->role !== $data['role'])) {
            return ['success' => FALSE, 'message' => 'Status aktif atau role akun yang sedang digunakan tidak dapat diubah sendiri.'];
        }

        $password_changed = $data['password'] !== '';
        if (!$password_changed) {
            unset($data['password']);
        } else {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $data['password_changed_at'] = date('Y-m-d H:i:s');
        }

        $security_state_changed = $password_changed
            || $data['role'] !== $user->role
            || $data['is_active'] !== (int) $user->is_active;
        if ($security_state_changed) {
            $data['session_version'] = (int) $user->session_version + 1;
        }

        $data = $this->normalize_unit_fields($data);

        if ($this->user_model->update($id, $data)) {
            $changed_fields = [];
            if ($data['role'] !== $user->role) {
                $changed_fields[] = 'role';
            }
            if ($data['is_active'] !== (int) $user->is_active) {
                $changed_fields[] = 'is_active';
            }
            if ($password_changed) {
                $changed_fields[] = 'password';
            }
            $this->record_user_event(
                $data['role'] !== $user->role ? 'user_role_changed' : 'user_updated',
                $id,
                'update',
                ['role' => $user->role, 'is_active' => (int) $user->is_active],
                ['role' => $data['role'], 'is_active' => $data['is_active']],
                [
                    'role_from' => $user->role,
                    'role_to' => $data['role'],
                    'status_from' => (int) $user->is_active ? 'active' : 'inactive',
                    'status_to' => $data['is_active'] ? 'active' : 'inactive',
                    'changed_fields' => $changed_fields,
                    'scope' => 'lpmpi_participant',
                ]
            );
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

        if ($user->role === 'super_admin'
            && (int) $user->is_active === 1
            && $this->user_model->count_active_by_role('super_admin') <= 1) {
            return ['success' => FALSE, 'message' => 'Super Admin aktif terakhir tidak dapat dihapus.'];
        }

        if ($this->user_model->delete($id)) {
            $this->record_user_event(
                'user_deleted',
                $id,
                'delete',
                ['role' => $user->role, 'is_active' => (int) $user->is_active],
                NULL,
                ['role_from' => $user->role]
            );
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
            $this->record_user_event(
                'user_deleted',
                $id,
                'delete',
                ['role' => $user->role, 'is_active' => (int) $user->is_active],
                NULL,
                ['role_from' => $user->role, 'scope' => 'lpmpi_participant']
            );
            return ['success' => TRUE, 'message' => 'Akun berhasil dihapus.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal menghapus akun.'];
    }

    private function normalize($data)
    {
        if (!array_key_exists('is_active', $data) || $data['is_active'] === NULL) {
            $is_active = 1;
        } elseif (in_array((string) $data['is_active'], ['0', '1'], TRUE)) {
            $is_active = (int) $data['is_active'];
        } else {
            $is_active = -1;
        }

        return [
            'nama' => trim(isset($data['nama']) ? $data['nama'] : ''),
            'email' => strtolower(trim(isset($data['email']) ? $data['email'] : '')),
            'password' => isset($data['password']) ? (string) $data['password'] : '',
            'role' => isset($data['role']) ? $data['role'] : '',
            'nama_unit' => trim(isset($data['nama_unit']) ? $data['nama_unit'] : ''),
            'jenis_unit' => isset($data['jenis_unit']) ? $data['jenis_unit'] : '',
            'is_active' => $is_active,
        ];
    }

    private function is_valid($data, $password_required)
    {
        return $data['nama'] !== ''
            && filter_var($data['email'], FILTER_VALIDATE_EMAIL) !== FALSE
            && (!$password_required || $data['password'] !== '')
            && in_array($data['role'], self::ALLOWED_ROLES, TRUE)
            && in_array($data['is_active'], [0, 1], TRUE);
    }

    private function is_valid_lpmpi_account($data, $password_required)
    {
        if ($data['nama'] === ''
            || filter_var($data['email'], FILTER_VALIDATE_EMAIL) === FALSE
            || ($password_required && $data['password'] === '')
            || !in_array($data['role'], ['auditor', 'auditee'], TRUE)
            || !in_array($data['is_active'], [0, 1], TRUE)) {
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

    private function record_user_event($event, $id, $action, $before, $after, array $metadata)
    {
        $this->audit_logger->record(
            $event,
            'user_account',
            (int) $id,
            $action,
            $before,
            $after,
            $metadata
        );
    }
}
