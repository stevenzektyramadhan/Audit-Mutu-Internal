<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_standards_service
{
    const STATUSES = ['draft', 'active'];
    const MUTABLE_STATUSES = ['draft'];
    const TRANSITIONS = [
        'draft' => ['active'],
        'active' => []
    ];
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
        if (!$this->is_mutable($version) || !isset(self::TRANSITIONS[$version->status]) || !in_array($next_status, self::TRANSITIONS[$version->status], TRUE)) return $this->finish(FALSE, 'Transisi status tidak diizinkan.');
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
        $db_debug = $this->ci->db->db_debug;
        $this->ci->db->db_debug = FALSE;
        try {
            $ok = $this->model->create_standard($payload);
        } finally {
            $this->ci->db->db_debug = $db_debug;
        }
        if (!$ok && $this->is_duplicate_key_error()) return $this->finish(FALSE, 'Kode atau urutan standar sudah digunakan.');
        return $this->finish($ok, $ok ? 'Standar berhasil ditambahkan.' : 'Standar gagal ditambahkan.');
    }

    public function update_standard($id, $data)
    {
        $this->ci->db->trans_start();
        $standard = $this->model->find_standard($id);
        $version = $standard ? $this->model->find_version($standard->version_id, TRUE) : NULL;
        if (!$standard || !$this->is_mutable($version)) return $this->finish(FALSE, 'Standar ini bersifat hanya-baca.');
        $payload = ['standard_code' => strtoupper(trim((string) $data['standard_code'])), 'display_order' => (int) $data['display_order'], 'title' => trim((string) $data['title']), 'description' => trim((string) $data['description'])];
        if (!preg_match('/^[A-Z0-9._-]+$/', $payload['standard_code']) || $payload['display_order'] < 1 || $payload['title'] === '' || strlen($payload['title']) > 200) return $this->finish(FALSE, 'Kode, urutan, dan judul standar wajib valid.');
        $db_debug = $this->ci->db->db_debug;
        $this->ci->db->db_debug = FALSE;
        try {
            $ok = $this->model->update_standard($id, $payload);
        } finally {
            $this->ci->db->db_debug = $db_debug;
        }
        if (!$ok && $this->is_duplicate_key_error()) return $this->finish(FALSE, 'Kode atau urutan standar sudah digunakan.');
        return $this->finish($ok, $ok ? 'Standar berhasil diperbarui.' : 'Standar gagal diperbarui.');
    }

    public function delete_standard($id)
    {
        if (!$this->ci->db->trans_begin()) return $this->fail('Standar mutu gagal dihapus.');
        $standard = $this->model->find_standard((int) $id);
        if (!$standard) return $this->rollback('Standar tidak ditemukan.');
        $version = $this->model->find_version((int) $standard->version_id, TRUE);
        $standard = $version ? $this->model->find_standard((int) $id, TRUE) : NULL;
        if (!$version || !$standard || (int) $standard->version_id !== (int) $version->id) return $this->rollback('Standar tidak ditemukan.');
        if ($version->status !== 'draft') return $this->rollback('Standar hanya dapat dihapus pada versi draft.');
        if ($this->model->standard_has_assignments((int) $id)) {
            return $this->rollback('Standar tidak dapat dihapus karena sudah memiliki riwayat penugasan audit AMI.');
        }
        if ($this->model->standard_has_legacy_dependencies((int) $id)) return $this->rollback('Standar tidak dapat dihapus karena masih dipakai data instrumen SPMI lama.');
        foreach ($this->model->get_standard_indicators((int) $id, TRUE) as $indicator) {
            if ($this->model->indicator_has_assignments((int) $indicator->id)) return $this->rollback('Standar tidak dapat dihapus karena salah satu indikatornya sudah memiliki riwayat penugasan audit AMI.');
            if ($this->model->indicator_has_legacy_dependencies((int) $indicator->id)) return $this->rollback('Standar tidak dapat dihapus karena salah satu indikatornya masih dipakai data instrumen SPMI lama.');
        }
        if (!$this->model->delete_standard_children((int) $id)) return $this->rollback('Indikator standar gagal dihapus.');
        if (!$this->model->delete_standard((int) $id)) return $this->rollback('Standar mutu gagal dihapus.');
        return $this->finish(TRUE, 'Standar mutu beserta indikatornya berhasil dihapus.', 'Standar mutu gagal dihapus.');
    }

    public function delete_version($id)
    {
        if (!$this->ci->db->trans_begin()) return $this->fail('Versi standar SPMI gagal dihapus.');
        $version = $this->model->find_version((int) $id, TRUE);
        if (!$version) return $this->rollback('Versi standar tidak ditemukan.');
        if ($version->status !== 'draft') return $this->rollback('Versi standar hanya dapat dihapus saat draft.');
        if ($this->model->version_has_assignments((int) $id)) {
            return $this->rollback('Versi tidak dapat dihapus karena sudah pernah ditugaskan pada siklus audit AMI.');
        }

        $standards = $this->model->get_standards((int) $id, TRUE);
        foreach ($standards as $std) {
            if ($this->model->standard_has_assignments((int) $std->id)) return $this->rollback('Versi tidak dapat dihapus karena salah satu standarnya sudah memiliki riwayat penugasan audit AMI.');
            if ($this->model->standard_has_legacy_dependencies((int) $std->id)) return $this->rollback('Versi tidak dapat dihapus karena salah satu standarnya masih dipakai data instrumen SPMI lama.');
            foreach ($this->model->get_standard_indicators((int) $std->id, TRUE) as $indicator) {
                if ($this->model->indicator_has_assignments((int) $indicator->id)) return $this->rollback('Versi tidak dapat dihapus karena salah satu indikatornya sudah memiliki riwayat penugasan audit AMI.');
                if ($this->model->indicator_has_legacy_dependencies((int) $indicator->id)) return $this->rollback('Versi tidak dapat dihapus karena salah satu indikatornya masih dipakai data instrumen SPMI lama.');
            }
        }

        foreach ($standards as $std) {
            if (!$this->model->delete_standard_children((int) $std->id)) return $this->rollback('Indikator standar gagal dihapus.');
            if (!$this->model->delete_standard((int) $std->id)) return $this->rollback('Standar mutu gagal dihapus.');
        }

        $source_file = $version->source_file_path;
        if (!$this->model->delete_version((int) $id)) return $this->rollback('Versi standar SPMI gagal dihapus.');
        $result = $this->finish(TRUE, 'Versi standar SPMI berhasil dihapus.', 'Versi standar SPMI gagal dihapus.');
        if ($result['success'] && !empty($source_file)) {
            $result['deleted_source_file_path'] = $source_file;
        }
        return $result;
    }

    public function set_source($id, $path) { return $this->guard_mutable_update($id, ['source_file_path' => basename($path)]); }
    public function clear_source($id) { return $this->guard_mutable_update($id, ['source_file_path' => NULL], TRUE); }
    private function guard_mutable_update($id, $data, $require_source = FALSE) { $this->ci->db->trans_start(); $version = $this->model->find_version($id, TRUE); if (!$this->is_mutable($version)) return $this->finish(FALSE, 'Versi ini bersifat hanya-baca.'); if ($require_source && empty($version->source_file_path)) return $this->finish(FALSE, 'Dokumen sumber tidak ditemukan.'); $previous_source_file_path = $version->source_file_path; $ok = $this->model->update_version($id, $data); $result = $this->finish($ok, $ok ? 'Dokumen sumber berhasil diperbarui.' : 'Dokumen sumber gagal diperbarui.'); if ($result['success']) $result['previous_source_file_path'] = $previous_source_file_path; return $result; }
    private function version_data($data) { return ['version_code' => strtoupper(trim((string) (isset($data['version_code']) ? $data['version_code'] : ''))), 'title' => trim((string) (isset($data['title']) ? $data['title'] : '')), 'description' => trim((string) (isset($data['description']) ? $data['description'] : ''))]; }
    private function is_duplicate_active_error() { return $this->is_duplicate_key_error(); }
    private function is_duplicate_key_error() { $error = $this->ci->db->error(); return isset($error['code']) && (int) $error['code'] === 1062; }
    private function finish($success, $message, $failure_message = NULL) { $completed = $this->ci->db->trans_complete(); return $success && $completed && $this->ci->db->trans_status() ? $this->ok($message) : $this->fail($failure_message ?: $message); }
    private function rollback($message) { $this->ci->db->trans_rollback(); return $this->fail($message); }
    private function ok($message) { return ['success' => TRUE, 'message' => $message]; }
    private function fail($message) { return ['success' => FALSE, 'message' => $message]; }
}
