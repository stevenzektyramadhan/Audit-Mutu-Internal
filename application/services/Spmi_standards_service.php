<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_standards_service
{
    const STATUSES = ['draft', 'review', 'approved', 'active', 'retired'];
    const MUTABLE_STATUSES = ['draft', 'review'];
    const TRANSITIONS = ['draft' => ['review'], 'review' => ['draft', 'approved'], 'approved' => ['active'], 'active' => ['retired'], 'retired' => []];
    protected $ci;
    protected $model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Spmi_standards_model');
        $this->model = $this->ci->Spmi_standards_model;
    }

    public function versions() { return $this->model->get_versions(); }
    public function version($id) { return $this->model->find_version((int) $id); }
    public function standards($version_id) { return $this->model->get_standards((int) $version_id); }
    public function standard($id) { return $this->model->find_standard((int) $id); }
    public function is_mutable($version) { return $version && in_array($version->status, self::MUTABLE_STATUSES, TRUE); }

    public function create_version($data, $user_id)
    {
        $payload = $this->version_data($data);
        if ($payload['version_code'] === '' || !preg_match('/^[A-Z0-9._-]+$/', $payload['version_code'])) return $this->fail('Kode versi tidak valid.');
        if ($payload['title'] === '' || strlen($payload['title']) > 200) return $this->fail('Judul versi wajib diisi dan maksimal 200 karakter.');
        if ($this->model->find_version_by_code($payload['version_code'])) return $this->fail('Kode versi sudah digunakan.');
        $payload['created_by'] = (int) $user_id;
        return $this->model->create_version($payload) ? $this->ok('Versi standar berhasil dibuat.') : $this->fail('Versi standar gagal dibuat.');
    }

    public function update_version($id, $data)
    {
        $this->ci->db->trans_start();
        $version = $this->model->find_version((int) $id, TRUE);
        if (!$version) return $this->finish(FALSE, 'Versi tidak ditemukan.');
        if (!$this->is_mutable($version)) return $this->finish(FALSE, 'Versi ini bersifat hanya-baca.');
        $payload = $this->version_data($data);
        if ($payload['version_code'] === '' || !preg_match('/^[A-Z0-9._-]+$/', $payload['version_code']) || $payload['title'] === '' || strlen($payload['title']) > 200) return $this->finish(FALSE, 'Kode versi dan judul wajib valid.');
        $other = $this->model->find_version_by_code($payload['version_code']);
        if ($other && (int) $other->id !== (int) $id) return $this->finish(FALSE, 'Kode versi sudah digunakan.');
        unset($payload['created_by']);
        $ok = $this->model->update_version($id, $payload);
        return $this->finish($ok, $ok ? 'Versi standar berhasil diperbarui.' : 'Versi standar gagal diperbarui.');
    }

    public function transition($id, $next_status)
    {
        $this->ci->db->trans_start();
        $version = $this->model->find_version((int) $id, TRUE);
        if (!$version || !isset(self::TRANSITIONS[$version->status]) || !in_array($next_status, self::TRANSITIONS[$version->status], TRUE)) return $this->finish(FALSE, 'Transisi status tidak diizinkan.');
        $db_debug = $this->ci->db->db_debug;
        if ($next_status === 'active') $this->ci->db->db_debug = FALSE;
        $ok = $this->model->update_version($id, ['status' => $next_status]);
        $this->ci->db->db_debug = $db_debug;
        if (!$ok && $next_status === 'active' && $this->is_duplicate_active_error()) return $this->finish(FALSE, 'Hanya satu versi yang dapat aktif.');
        return $this->finish($ok, $ok ? 'Status versi berhasil diubah.' : 'Status versi gagal diubah.');
    }

    public function create_standard($version_id, $data)
    {
        $this->ci->db->trans_start();
        $version = $this->model->find_version($version_id, TRUE);
        if (!$this->is_mutable($version)) return $this->finish(FALSE, 'Versi ini bersifat hanya-baca.');
        $payload = ['version_id' => (int) $version_id, 'standard_code' => strtoupper(trim((string) (isset($data['standard_code']) ? $data['standard_code'] : ''))), 'display_order' => (int) (isset($data['display_order']) ? $data['display_order'] : 0), 'title' => trim((string) (isset($data['title']) ? $data['title'] : '')), 'description' => trim((string) (isset($data['description']) ? $data['description'] : ''))];
        if (!preg_match('/^[A-Z0-9._-]+$/', $payload['standard_code']) || $payload['display_order'] < 1 || $payload['title'] === '' || strlen($payload['title']) > 200) return $this->finish(FALSE, 'Kode, urutan, dan judul standar wajib valid.');
        $ok = $this->model->create_standard($payload);
        return $this->finish($ok, $ok ? 'Standar berhasil ditambahkan.' : 'Kode atau urutan standar sudah digunakan.');
    }

    public function update_standard($id, $data)
    {
        $this->ci->db->trans_start();
        $standard = $this->model->find_standard($id);
        $version = $standard ? $this->model->find_version($standard->version_id, TRUE) : NULL;
        if (!$standard || !$this->is_mutable($version)) return $this->finish(FALSE, 'Standar ini bersifat hanya-baca.');
        $payload = ['standard_code' => strtoupper(trim((string) $data['standard_code'])), 'display_order' => (int) $data['display_order'], 'title' => trim((string) $data['title']), 'description' => trim((string) $data['description'])];
        if (!preg_match('/^[A-Z0-9._-]+$/', $payload['standard_code']) || $payload['display_order'] < 1 || $payload['title'] === '' || strlen($payload['title']) > 200) return $this->finish(FALSE, 'Kode, urutan, dan judul standar wajib valid.');
        $ok = $this->model->update_standard($id, $payload);
        return $this->finish($ok, $ok ? 'Standar berhasil diperbarui.' : 'Kode atau urutan standar sudah digunakan.');
    }

    public function set_source($id, $path) { return $this->guard_mutable_update($id, ['source_file_path' => basename($path)]); }
    public function clear_source($id) { return $this->guard_mutable_update($id, ['source_file_path' => NULL], TRUE); }
    private function guard_mutable_update($id, $data, $require_source = FALSE) { $this->ci->db->trans_start(); $version = $this->model->find_version($id, TRUE); if (!$this->is_mutable($version)) return $this->finish(FALSE, 'Versi ini bersifat hanya-baca.'); if ($require_source && empty($version->source_file_path)) return $this->finish(FALSE, 'Dokumen sumber tidak ditemukan.'); $previous_source_file_path = $version->source_file_path; $ok = $this->model->update_version($id, $data); $result = $this->finish($ok, $ok ? 'Dokumen sumber berhasil diperbarui.' : 'Dokumen sumber gagal diperbarui.'); if ($result['success']) $result['previous_source_file_path'] = $previous_source_file_path; return $result; }
    private function version_data($data) { return ['version_code' => strtoupper(trim((string) (isset($data['version_code']) ? $data['version_code'] : ''))), 'title' => trim((string) (isset($data['title']) ? $data['title'] : '')), 'description' => trim((string) (isset($data['description']) ? $data['description'] : ''))]; }
    private function is_duplicate_active_error() { $error = $this->ci->db->error(); return isset($error['code']) && (int) $error['code'] === 1062; }
    private function finish($success, $message) { $this->ci->db->trans_complete(); return $success && $this->ci->db->trans_status() ? $this->ok($message) : $this->fail($message); }
    private function ok($message) { return ['success' => TRUE, 'message' => $message]; }
    private function fail($message) { return ['success' => FALSE, 'message' => $message]; }
}
