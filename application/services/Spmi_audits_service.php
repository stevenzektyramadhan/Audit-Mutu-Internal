<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_audits_service
{
    const TRANSITIONS = ['draft' => ['configured', 'closed'], 'configured' => ['draft', 'closed'], 'closed' => []];
    protected $ci;
    protected $model;

    public function __construct() { $this->ci = &get_instance(); $this->ci->load->helper('app'); $this->ci->load->model('Spmi_audits_model'); $this->model = $this->ci->Spmi_audits_model; }
    public function cycles() { return $this->model->cycles(); }
    public function cycle($id) { return $this->model->cycle($id); }
    public function assignments($cycle_id) { return $this->model->assignments($cycle_id); }
    public function assignment($id) { return $this->model->assignment($id); }
    public function items($id) { return $this->model->items($id); }
    public function rubrics($id) { return $this->model->rubrics($id); }
    public function standards() { return $this->model->standards(); }
    public function auditors() { return $this->model->users_by_role('auditor'); }
    public function auditees() { return $this->model->users_by_role('auditee'); }
    public function create_cycle($data, $user_id) { $payload = $this->cycle_data($data); if (!$this->valid_cycle($payload) || $this->model->cycle_by_code($payload['cycle_code'])) return $this->fail('Kode, judul, tanggal, atau rentang siklus tidak valid.'); $payload['created_by'] = (int) $user_id; return $this->model->insert_cycle($payload) ? $this->ok('Siklus SPMI berhasil dibuat.') : $this->fail('Siklus SPMI gagal dibuat.'); }
    public function update_cycle($id, $data) { $this->ci->db->trans_begin(); $cycle = $this->model->cycle($id, TRUE); $payload = $this->cycle_data($data); if (!$cycle || $cycle->state !== 'draft' || !$this->valid_cycle($payload) || ($this->model->cycle_by_code($payload['cycle_code'], $id))) return $this->rollback('Metadata hanya dapat diubah pada siklus draft.'); return $this->finish($this->model->update_cycle($id, $payload), 'Siklus SPMI berhasil diperbarui.'); }
    public function transition($id, $state) { $this->ci->db->trans_begin(); $cycle = $this->model->cycle($id, TRUE); if (!$cycle || !isset(self::TRANSITIONS[$cycle->state]) || !in_array($state, self::TRANSITIONS[$cycle->state], TRUE)) return $this->rollback('Transisi status siklus tidak diizinkan.'); return $this->finish($this->model->update_cycle($id, ['state' => $state]), 'Status siklus berhasil diubah.'); }

    public function create_assignment($cycle_id, $data, $user_id)
    {
        $this->ci->db->trans_begin();

        try {
            $cycle = $this->model->cycle($cycle_id, TRUE);
            if (!$cycle || $cycle->state !== 'draft') return $this->rollback('Penugasan hanya dapat dibuat pada siklus draft.');

            $standard = $this->model->standard_for_update((int) $data['source_standard_id']);
            if (!$standard) return $this->rollback('Standar SPMI tidak ditemukan.');

            $version = $this->model->version_for_update($standard->version_id);
            if (!$version || !in_array($version->status, ['draft', 'review'], TRUE)) return $this->rollback('Versi sumber harus berstatus draft atau review.');

            $indicators = $this->model->standard_indicators($standard->id);
            if (!$indicators) return $this->rollback('Standar SPMI belum memiliki indikator.');

            $rubric_options = skor_audit_options();
            if (array_keys($rubric_options) !== [1, 2, 3, 4]) return $this->rollback('Skala skor audit global tidak valid.');

            $auditor = $this->model->user((int) $data['auditor_id']);
            $auditee = $this->model->user((int) $data['auditee_id']);
            if (!$auditor || $auditor->role !== 'auditor' || !$auditee || $auditee->role !== 'auditee' || (int) $auditor->id === (int) $auditee->id) return $this->rollback('Auditor dan auditee wajib valid, ber-role tepat, dan berbeda.');
            if ($this->model->assignment_by_tuple($cycle_id, $standard->id, $auditor->id, $auditee->id)) return $this->rollback('Tuple penugasan sudah digunakan pada siklus ini.');

            $assignment = ['cycle_id' => (int) $cycle_id, 'auditor_id' => (int) $auditor->id, 'auditee_id' => (int) $auditee->id, 'created_by' => (int) $user_id, 'source_version_id' => (int) $version->id, 'source_version_code' => $version->version_code, 'source_version_title' => $version->title, 'source_standard_id' => (int) $standard->id, 'source_standard_code' => $standard->standard_code, 'source_standard_title' => $standard->title, 'auditor_name' => $auditor->nama, 'auditor_email' => $auditor->email, 'auditee_name' => $auditee->nama, 'auditee_email' => $auditee->email];
            $assignment_id = $this->model->insert_assignment($assignment);
            if (!$assignment_id) return $this->rollback('Penugasan gagal dibuat.');

            foreach ($indicators as $order => $indicator) {
                $item_id = $this->model->insert_item(['assignment_id' => $assignment_id, 'source_indicator_id' => (int) $indicator->id, 'indicator_code' => $indicator->indicator_code, 'indicator_title' => $indicator->title, 'display_order' => $order + 1, 'evidence_instruction' => $indicator->evidence_requirement, 'evidence_policy' => 'none']);
                if (!$item_id) return $this->rollback('Snapshot indikator gagal dibuat.');

                foreach ($rubric_options as $score => $descriptor) {
                    if (!$this->model->insert_rubric(['assignment_item_id' => $item_id, 'score' => (int) $score, 'descriptor' => $descriptor])) return $this->rollback('Snapshot rubrik gagal dibuat.');
                }
            }

            return $this->finish(TRUE, 'Penugasan SPMI berhasil dibuat.');
        } catch (Throwable $e) {
            return $this->rollback('Penugasan SPMI gagal dibuat.');
        }
    }

    public function delete_assignment($id) { $this->ci->db->trans_begin(); $assignment = $this->model->assignment($id); $cycle = $assignment ? $this->model->cycle($assignment->cycle_id, TRUE) : NULL; if (!$assignment || !$cycle || $cycle->state !== 'draft') return $this->rollback('Penugasan hanya dapat dihapus pada siklus draft.'); if ($this->model->assignment_workspace_descendant_exists($id)) return $this->rollback('Penugasan tidak dapat dihapus karena data workspace auditee atau auditor sudah ada.'); if (!$this->model->delete_assignment_children($id)) return $this->rollback('Snapshot penugasan gagal dihapus.'); return $this->finish($this->model->delete_assignment($id), 'Penugasan berhasil dihapus.'); }
    private function cycle_data($data) { return ['cycle_code' => strtoupper(trim((string) (isset($data['cycle_code']) ? $data['cycle_code'] : ''))), 'title' => trim((string) (isset($data['title']) ? $data['title'] : '')), 'description' => trim((string) (isset($data['description']) ? $data['description'] : '')) ?: NULL, 'academic_year' => trim((string) (isset($data['academic_year']) ? $data['academic_year'] : '')), 'semester' => strtolower(trim((string) (isset($data['semester']) ? $data['semester'] : ''))), 'start_date' => trim((string) (isset($data['start_date']) ? $data['start_date'] : '')), 'end_date' => trim((string) (isset($data['end_date']) ? $data['end_date'] : ''))]; }
    private function valid_cycle($data) { return preg_match('/^[A-Z0-9._-]+$/', $data['cycle_code']) && strlen($data['cycle_code']) <= 64 && $data['title'] !== '' && strlen($data['title']) <= 200 && $data['academic_year'] !== '' && strlen($data['academic_year']) <= 20 && in_array($data['semester'], ['ganjil', 'genap'], TRUE) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['start_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['end_date']) && $data['end_date'] >= $data['start_date']; }
    private function ok($message) { return ['success' => TRUE, 'message' => $message]; }
    private function fail($message) { return ['success' => FALSE, 'message' => $message]; }
    private function finish($success, $message) { $this->ci->db->trans_complete(); return $success && $this->ci->db->trans_status() ? $this->ok($message) : $this->fail($message); }
    private function rollback($message) { $this->ci->db->trans_rollback(); return $this->fail($message); }
}
