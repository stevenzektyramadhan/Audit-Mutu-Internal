<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_indicators_service
{
    protected $ci;
    protected $model;
    public function __construct() { $this->ci = &get_instance(); $this->ci->load->model('Spmi_indicators_model'); $this->model = $this->ci->Spmi_indicators_model; }
    public function standards() { return $this->model->get_standards(); }
    public function units() { return $this->model->get_units(); }
    public function standard($id) { return $this->model->find_standard($id); }
    public function standard_version($standard_id) { $standard = $this->model->find_standard($standard_id); return $standard ? $this->model->find_version($standard->version_id) : NULL; }
    public function indicator($id) { return $this->model->find_indicator($id); }
    public function indicators($standard_id) { return $this->model->get_indicators($standard_id); }
    public function targets($indicator_id) { return $this->model->get_targets($indicator_id); }
    public function target($id) { return $this->model->find_target($id); }
    public function is_mutable($version) { return $version && in_array($version->status, ['draft', 'review'], TRUE); }

    public function create_indicator($standard_id, $data)
    {
        $this->ci->db->trans_start();
        $standard = $this->model->find_standard($standard_id);
        $version = $standard ? $this->model->find_version($standard->version_id, TRUE) : NULL;
        if (!$this->is_mutable($version)) return $this->finish(FALSE, 'Standar ini berada pada versi hanya-baca.');
        $payload = $this->indicator_data($data, $standard_id);
        $error = $this->validate_indicator($payload);
        if ($error) return $this->finish(FALSE, $error);
        if (!$this->model->find_active_unit($payload['scope_organization_unit_id']) || !$this->model->find_active_unit($payload['responsible_organization_unit_id'])) return $this->finish(FALSE, 'Unit organisasi aktif tidak ditemukan.');
        if ($this->model->find_by_code($standard_id, $payload['indicator_code'])) return $this->finish(FALSE, 'Kode indikator sudah digunakan pada standar ini.');
        $ok = $this->model->create_indicator($payload);
        return $this->finish($ok, $ok ? 'Indikator SPMI berhasil ditambahkan.' : 'Indikator SPMI gagal disimpan.');
    }

    public function update_indicator($id, $data)
    {
        $this->ci->db->trans_start();
        $current = $this->model->find_indicator_for_update($id);
        $standard = $current ? $this->model->find_standard($current->standard_id) : NULL;
        $version = $standard ? $this->model->find_version($standard->version_id, TRUE) : NULL;
        if (!$current || !$this->is_mutable($version)) return $this->finish(FALSE, 'Indikator ini berada pada versi hanya-baca.');
        $payload = $this->indicator_data($data, $current->standard_id);
        $error = $this->validate_indicator($payload);
        if ($error) return $this->finish(FALSE, $error);
        if (!$this->model->find_active_unit($payload['scope_organization_unit_id']) || !$this->model->find_active_unit($payload['responsible_organization_unit_id'])) return $this->finish(FALSE, 'Unit organisasi aktif tidak ditemukan.');
        if ($this->model->find_by_code($current->standard_id, $payload['indicator_code'], $id)) return $this->finish(FALSE, 'Kode indikator sudah digunakan pada standar ini.');
        unset($payload['standard_id']);
        $ok = $this->model->update_indicator($id, $payload);
        return $this->finish($ok, $ok ? 'Indikator SPMI berhasil diperbarui.' : 'Indikator SPMI gagal diperbarui.');
    }

    public function create_target($indicator_id, $data)
    {
        return $this->save_target($indicator_id, 0, $data);
    }
    public function update_target($id, $data)
    {
        $target = $this->model->find_target($id);
        return $target ? $this->save_target($target->indicator_id, $id, $data) : $this->fail('Target tidak ditemukan.');
    }
    private function save_target($indicator_id, $id, $data)
    {
        $this->ci->db->trans_start();
        $indicator = $this->model->find_indicator_for_update($indicator_id);
        $standard = $indicator ? $this->model->find_standard($indicator->standard_id) : NULL;
        $version = $standard ? $this->model->find_version($standard->version_id, TRUE) : NULL;
        if (!$indicator || !$this->is_mutable($version)) return $this->finish(FALSE, 'Indikator ini berada pada versi hanya-baca.');
        $year = (int) (isset($data['target_year']) ? $data['target_year'] : 0);
        $value = trim((string) (isset($data['target_value']) ? $data['target_value'] : ''));
        if ($year < 2000 || $year > 2100 || $value === '') return $this->finish(FALSE, 'Tahun target harus 2000-2100 dan target wajib diisi.');
        if ($this->model->find_target_by_year($indicator_id, $year, $id)) return $this->finish(FALSE, 'Target untuk tahun tersebut sudah digunakan.');
        $payload = ['indicator_id' => (int) $indicator_id, 'target_year' => $year, 'target_value' => $value];
        $ok = $id ? $this->model->update_target($id, ['target_year' => $year, 'target_value' => $value]) : $this->model->create_target($payload);
        return $this->finish($ok, $ok ? 'Target tahunan berhasil disimpan.' : 'Target tahunan gagal disimpan.');
    }
    private function indicator_data($data, $standard_id) { return ['standard_id' => (int) $standard_id, 'indicator_code' => strtoupper(trim((string) (isset($data['indicator_code']) ? $data['indicator_code'] : ''))), 'indicator_type' => (string) (isset($data['indicator_type']) ? $data['indicator_type'] : ''), 'title' => trim((string) (isset($data['title']) ? $data['title'] : '')), 'scope_organization_unit_id' => (int) (isset($data['scope_organization_unit_id']) ? $data['scope_organization_unit_id'] : 0), 'responsible_organization_unit_id' => (int) (isset($data['responsible_organization_unit_id']) ? $data['responsible_organization_unit_id'] : 0), 'responsible_pic_name' => trim((string) (isset($data['responsible_pic_name']) ? $data['responsible_pic_name'] : '')) ?: NULL, 'evidence_requirement' => trim((string) (isset($data['evidence_requirement']) ? $data['evidence_requirement'] : ''))]; }
    private function validate_indicator($payload) { if ($payload['indicator_code'] === '' || strlen($payload['indicator_code']) > 64 || !preg_match('/^[A-Z0-9._-]+$/', $payload['indicator_code'])) return 'Kode indikator tidak valid.'; if (!in_array($payload['indicator_type'], ['IKU', 'IKT'], TRUE)) return 'Jenis indikator tidak valid.'; if ($payload['title'] === '' || strlen($payload['title']) > 200) return 'Judul indikator wajib diisi dan maksimal 200 karakter.'; if ($payload['responsible_pic_name'] !== NULL && strlen($payload['responsible_pic_name']) > 200) return 'Nama PIC maksimal 200 karakter.'; if ($payload['evidence_requirement'] === '') return 'Kebutuhan bukti wajib diisi.'; return NULL; }
    private function finish($success, $message) { $this->ci->db->trans_complete(); return $success && $this->ci->db->trans_status() ? ['success' => TRUE, 'message' => $message] : $this->fail($message); }
    private function fail($message) { return ['success' => FALSE, 'message' => $message]; }
}
