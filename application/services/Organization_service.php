<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Organization_service
{
    const TYPES = ['university', 'faculty', 'upps', 'study_program', 'institute', 'bureau', 'unit'];
    const ROLES = ['super_admin', 'admin_lpmpi'];
    protected $ci;
    protected $model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Organization_model');
        $this->model = $this->ci->Organization_model;
    }

    public function can($role, $capability)
    {
        foreach ($this->model->get_role_capability_codes($role) as $row) if ($row['code'] === $capability) return TRUE;
        return FALSE;
    }

    public function units() { return $this->model->get_units(); }
    public function find_unit($id) { return $this->model->find_unit((int) $id); }
    public function assignments($unit_id = 0) { return $this->model->get_assignments($unit_id); }
    public function users() { return $this->model->get_users(); }
    public function capabilities() { return $this->model->get_capabilities(); }
    public function role_codes($role) { return array_column($this->model->get_role_capability_codes($role), 'code'); }

    public function create_unit($data) { return $this->save_unit($data, 0); }
    public function update_unit($id, $data) { return $this->save_unit($data, (int) $id); }

    private function save_unit($data, $id)
    {
        $code = strtoupper(trim((string) (isset($data['code']) ? $data['code'] : '')));
        $name = trim((string) (isset($data['name']) ? $data['name'] : ''));
        $type = (string) (isset($data['type']) ? $data['type'] : '');
        $parent_id = (int) (isset($data['parent_id']) ? $data['parent_id'] : 0);
        if ($code === '' || strlen($code) > 64 || !preg_match('/^[A-Z0-9._-]+$/', $code)) return $this->fail('Kode unit tidak valid.');
        if ($name === '' || strlen($name) > 200) return $this->fail('Nama unit wajib diisi dan maksimal 200 karakter.');
        if (!in_array($type, self::TYPES, TRUE)) return $this->fail('Tipe unit tidak valid.');
        if ($this->model->code_exists($code, $id)) return $this->fail('Kode unit sudah digunakan.');
        $current = $id ? $this->model->find_unit($id) : NULL;
        if ($id && (!$current || $current->parent_id === NULL)) return $this->fail('Unit root tidak dapat diubah.');
        if ($parent_id) {
            $parent = $this->model->find_unit($parent_id);
            if (!$parent || (int) $parent->is_active !== 1 || $parent_id === $id || $this->is_descendant($parent_id, $id)) return $this->fail('Parent unit tidak valid.');
        } else return $this->fail('Unit non-root wajib memiliki parent.');
        $payload = ['code' => $code, 'name' => $name, 'type' => $type, 'parent_id' => $parent_id ?: NULL];
        $ok = $id ? $this->model->update_unit($id, $payload) : $this->model->create_unit($payload);
        return $ok ? ['success' => TRUE, 'message' => 'Unit organisasi berhasil disimpan.'] : $this->fail('Unit organisasi gagal disimpan.');
    }

    private function is_descendant($candidate, $id)
    {
        if (!$id) return FALSE;
        $seen = [];
        while ($candidate) {
            if (isset($seen[$candidate])) return TRUE;
            $seen[$candidate] = TRUE;
            if ($candidate === $id) return TRUE;
            $unit = $this->model->find_unit($candidate);
            $candidate = $unit ? (int) $unit->parent_id : 0;
        }
        return FALSE;
    }

    public function toggle_unit($id)
    {
        $unit = $this->model->find_unit($id);
        if (!$unit || $unit->parent_id === NULL) return $this->fail('Unit root tidak dapat dinonaktifkan.');
        if ((int) $unit->is_active === 1 && $this->model->has_active_children($id)) return $this->fail('Nonaktifkan child unit terlebih dahulu.');
        return $this->model->update_unit($id, ['is_active' => (int) !$unit->is_active]) ? ['success' => TRUE, 'message' => 'Status unit berhasil diubah.'] : $this->fail('Status unit gagal diubah.');
    }

    public function create_assignment($data)
    {
        $from = trim((string) $data['valid_from']); $until = trim((string) $data['valid_until']);
        if (!$data['user_id'] || !$data['organization_unit_id'] || trim((string) $data['position_code']) === '' || $from === '') return $this->fail('Data penempatan wajib diisi.');
        if (!$this->model->find_user($data['user_id'])) return $this->fail('Pengguna tidak ditemukan.');
        if (!$this->model->find_active_unit($data['organization_unit_id'])) return $this->fail('Unit aktif tidak ditemukan.');
        if (!$this->valid_date($from) || ($until !== '' && !$this->valid_date($until))) return $this->fail('Format tanggal penempatan tidak valid.');
        if ($until !== '' && $until < $from) return $this->fail('Tanggal berakhir tidak boleh sebelum tanggal mulai.');
        $this->ci->db->trans_start();
        if (!empty($data['is_primary']) && $this->is_current_assignment($from, $until)) $this->model->clear_primary($data['user_id']);
        $ok = $this->model->create_assignment(['user_id' => (int) $data['user_id'], 'organization_unit_id' => (int) $data['organization_unit_id'], 'position_code' => trim($data['position_code']), 'valid_from' => $from, 'valid_until' => $until ?: NULL, 'is_primary' => !empty($data['is_primary']) ? 1 : 0]);
        $this->ci->db->trans_complete();
        return $ok && $this->ci->db->trans_status() ? ['success' => TRUE, 'message' => 'Penempatan berhasil ditambahkan.'] : $this->fail('Penempatan gagal disimpan.');
    }

    public function end_assignment($id, $until)
    {
        $assignment = $this->model->find_assignment($id);
        $until = trim((string) $until);
        if (!$assignment) return $this->fail('Penempatan tidak ditemukan.');
        if (!$this->valid_date($until) || $until < $assignment->valid_from) return $this->fail('Tanggal berakhir tidak valid.');
        return $this->model->end_assignment($id, $until) ? ['success' => TRUE, 'message' => 'Penempatan berhasil diakhiri.'] : $this->fail('Penempatan gagal diakhiri.');
    }

    private function valid_date($value)
    {
        $date = DateTime::createFromFormat('!Y-m-d', $value);
        return $date !== FALSE && $date->format('Y-m-d') === $value;
    }

    private function is_current_assignment($from, $until)
    {
        $today = date('Y-m-d');
        return $from <= $today && ($until === '' || $until >= $today);
    }

    public function update_capabilities($role, $ids)
    {
        if (!in_array($role, self::ROLES, TRUE)) return $this->fail('Role tidak valid.');
        $known_ids = [];
        foreach ($this->model->get_capabilities() as $capability) $known_ids[(int) $capability->id] = TRUE;
        $capability_ids = [];
        foreach ((array) $ids as $capability_id) {
            $capability_id = (int) $capability_id;
            if (isset($known_ids[$capability_id])) $capability_ids[$capability_id] = $capability_id;
        }
        if ($role === 'super_admin') {
            foreach ($this->model->get_capabilities() as $capability) {
                if (in_array($capability->code, ['organization.view', 'organization.capability.manage'], TRUE)) $capability_ids[(int) $capability->id] = (int) $capability->id;
            }
        }
        $this->ci->db->trans_start();
        $ok = $this->model->replace_role_capabilities($role, array_values($capability_ids));
        $this->ci->db->trans_complete();
        return $ok && $this->ci->db->trans_status() ? ['success' => TRUE, 'message' => 'Kapabilitas role berhasil diperbarui.'] : $this->fail('Kapabilitas gagal diperbarui.');
    }

    private function fail($message) { return ['success' => FALSE, 'message' => $message]; }
}
