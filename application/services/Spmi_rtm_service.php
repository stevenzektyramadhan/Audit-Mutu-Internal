<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_rtm_service
{
    protected $ci;
    protected $model;

    public function __construct() { $this->ci = &get_instance(); $this->ci->load->model('Spmi_rtm_model'); $this->model = $this->ci->Spmi_rtm_model; }
    public function meetings() { return $this->model->meetings(); }
    public function meeting($id) { $meeting = $this->model->meeting($id); return $meeting ? ['meeting' => $meeting, 'reports' => $this->model->meeting_reports($id), 'participants' => $this->model->participants($id), 'decisions' => $this->model->decisions($id)] : NULL; }
    public function form_options() { return ['reports' => $this->model->reports(), 'users' => $this->model->users()]; }
    public function create($data, $user_id) { return $this->save(NULL, $data, $user_id); }
    public function update($id, $data) { return $this->save($id, $data, 0); }

    protected function save($id, $data, $user_id)
    {
        $this->ci->db->trans_begin();
        $meeting = $id ? $this->model->meeting($id, TRUE) : NULL;
        if ($id && (!$meeting || $meeting->status !== 'draft')) return $this->rollback('RTM resolved bersifat hanya-baca.');
        $report_ids = $this->ids(isset($data['report_ids']) ? $data['report_ids'] : []);
        $participant_ids = $this->ids(isset($data['participant_ids']) ? $data['participant_ids'] : []);
        if (!$report_ids || !$participant_ids) return $this->rollback('RTM wajib memiliki laporan dan peserta.');
        $reports = [];
        foreach ($report_ids as $report_id) { $report = $this->model->report_for_update($report_id); if (!$report) return $this->rollback('Laporan M10 tidak ditemukan.'); $reports[$report_id] = $report; }
        $users = $this->model->users_for_update($participant_ids);
        if (count($users) !== count($participant_ids)) return $this->rollback('Peserta tidak ditemukan.');
        $meeting_data = ['meeting_code' => strtoupper(trim((string) ($data['meeting_code'] ?? ''))), 'meeting_title' => trim((string) ($data['meeting_title'] ?? '')), 'meeting_date' => trim((string) ($data['meeting_date'] ?? '')), 'location' => trim((string) ($data['location'] ?? ''))];
        $date = DateTime::createFromFormat('!Y-m-d', $meeting_data['meeting_date']);
        if (!$meeting_data['meeting_code'] || strlen($meeting_data['meeting_code']) > 128 || !preg_match('/^[A-Z0-9._-]+$/', $meeting_data['meeting_code'])) return $this->rollback('Kode rapat tidak valid.');
        if (!$date || $date->format('Y-m-d') !== $meeting_data['meeting_date']) return $this->rollback('Tanggal rapat tidak valid.');
        if (!$meeting_data['meeting_title'] || strlen($meeting_data['meeting_title']) > 200 || !$meeting_data['location'] || strlen($meeting_data['location']) > 200) return $this->rollback('Data rapat wajib valid.');
        $decisions = $this->decisions(isset($data['decisions']) ? $data['decisions'] : []);
        if (!$decisions['valid'] || !$decisions['rows']) return $this->rollback('RTM wajib memiliki keputusan dan tindakan lengkap.');
        if (!$id) { $meeting_data['status'] = 'draft'; $meeting_data['created_by'] = (int) $user_id; $id = $this->model->insert_meeting($meeting_data); } else if (!$this->model->update_meeting($id, $meeting_data)) return $this->rollback('RTM gagal diperbarui.');
        if (!$id || !$this->model->delete_children($id)) return $this->rollback('Data RTM gagal disiapkan.');
        foreach ($report_ids as $report_id) if (!$this->model->insert_report_link(['meeting_id' => $id, 'report_id' => $report_id])) return $this->rollback('Laporan RTM gagal disimpan.');
        foreach ($users as $user) if (!$this->model->insert_participant(['meeting_id' => $id, 'user_id' => (int) $user->id, 'name_snapshot' => $user->nama, 'email_snapshot' => $user->email, 'role_snapshot' => $user->role])) return $this->rollback('Snapshot peserta gagal disimpan.');
        foreach ($decisions['rows'] as $decision) {
            if ($decision['report_id'] && !isset($reports[$decision['report_id']])) return $this->rollback('Laporan keputusan harus terhubung ke laporan RTM.');
            if ($decision['report_item_id']) { $item = $this->model->report_item_for_update($decision['report_item_id']); if (!$item || (int) $item->report_id !== (int) $decision['report_id']) return $this->rollback('Item keputusan tidak termasuk laporan terpilih.'); }
            if (!$this->model->insert_decision(['meeting_id' => $id, 'display_order' => $decision['display_order'], 'decision_text' => $decision['decision_text'], 'action_text' => $decision['action_text'], 'report_id' => $decision['report_id'] ?: NULL, 'report_item_id' => $decision['report_item_id'] ?: NULL])) return $this->rollback('Keputusan RTM gagal disimpan.');
        }
        return $this->finish(['success' => TRUE, 'message' => 'RTM SPMI berhasil disimpan.', 'id' => $id]);
    }

    public function resolve($id, $user_id)
    {
        $this->ci->db->trans_begin();
        $meeting = $this->model->meeting($id, TRUE);
        if (!$meeting) return $this->rollback('RTM tidak ditemukan.');
        if ($meeting->status !== 'draft') return $this->rollback('RTM resolved bersifat hanya-baca.');
        $reports = $this->model->meeting_reports($id); $participants = $this->model->participants($id); $decisions = $this->model->decisions($id);
        if (!$reports || !$participants || !$decisions) return $this->rollback('RTM wajib memiliki laporan, peserta, dan keputusan.');
        foreach ($reports as $report) if (!$this->model->report_for_update($report->report_id)) return $this->rollback('Laporan M10 tidak ditemukan.');
        $this->model->users_for_update(array_map(function ($participant) { return (int) $participant->user_id; }, $participants));
        foreach ($decisions as $decision) if (!trim((string) $decision->decision_text) || !trim((string) $decision->action_text)) return $this->rollback('Keputusan dan tindakan tidak boleh kosong.');
        if (!$this->model->update_meeting($id, ['status' => 'resolved', 'resolved_by' => (int) $user_id, 'resolved_at' => date('Y-m-d H:i:s')])) return $this->rollback('RTM gagal diselesaikan.');
        return $this->finish(['success' => TRUE, 'message' => 'RTM SPMI berhasil di-resolve.', 'id' => $id]);
    }

    protected function ids($ids) { $result = []; foreach ((array) $ids as $id) { if ((int) $id > 0) $result[(int) $id] = (int) $id; } return array_values($result); }
    protected function decisions($decisions) { $result = []; $valid = TRUE; foreach ((array) $decisions as $row) { if (!is_array($row)) { $valid = FALSE; continue; } $decision_text = trim((string) ($row['decision_text'] ?? '')); $action_text = trim((string) ($row['action_text'] ?? '')); $report_id = (int) ($row['report_id'] ?? 0); $report_item_id = (int) ($row['report_item_id'] ?? 0); if (!$decision_text && !$action_text && !$report_id && !$report_item_id) continue; if (!$decision_text || !$action_text) { $valid = FALSE; continue; } $result[] = ['display_order' => count($result) + 1, 'decision_text' => $decision_text, 'action_text' => $action_text, 'report_id' => $report_id, 'report_item_id' => $report_item_id]; } return ['valid' => $valid, 'rows' => $result]; }
    protected function rollback($message) { $this->ci->db->trans_rollback(); return ['success' => FALSE, 'message' => $message]; }
    protected function finish($result) { $this->ci->db->trans_complete(); return $this->ci->db->trans_status() ? $result : ['success' => FALSE, 'message' => 'RTM SPMI gagal disimpan.']; }
}
