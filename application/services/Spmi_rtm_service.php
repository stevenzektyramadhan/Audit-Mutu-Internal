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
    public function create($data, $file, $user_id) { return $this->save(NULL, $data, $file, $user_id); }
    public function update($id, $data, $file) { return $this->save($id, $data, $file, 0); }
    public function upload_limit_mib() { return $this->limit_mib($this->upload_limit_bytes()); }

    public function download($id)
    {
        $meeting = $this->model->meeting($id);
        if (!$meeting || !$meeting->photo_stored_name) return NULL;
        $path = private_storage_path('rtm_photos', $meeting->photo_stored_name);
        return $path && is_file($path) ? ['path' => $path, 'mime' => $meeting->photo_mime_type, 'name' => $meeting->photo_original_name, 'size' => (int) $meeting->photo_size_bytes] : NULL;
    }

    protected function save($id, $data, $file, $user_id)
    {
        $saved = $this->save_photo($file);
        if (!$saved['success']) return $saved;
        $this->ci->db->trans_begin();
        $meeting = $id ? $this->model->meeting($id, TRUE) : NULL;
        if ($id && (!$meeting || $meeting->status !== 'draft')) return $this->rollback('RTM resolved bersifat hanya-baca.', $saved);
        $report_ids = $this->ids(isset($data['report_ids']) ? $data['report_ids'] : []);
        $participant_ids = $this->ids(isset($data['participant_ids']) ? $data['participant_ids'] : []);
        if (!$report_ids || !$participant_ids) return $this->rollback('RTM wajib memiliki laporan dan peserta.', $saved);
        $reports = [];
        foreach ($report_ids as $report_id) { $report = $this->model->report_for_update($report_id); if (!$report) return $this->rollback('Laporan M10 tidak ditemukan.', $saved); $reports[$report_id] = $report; }
        $users = $this->model->users_for_update($participant_ids);
        if (count($users) !== count($participant_ids)) return $this->rollback('Peserta tidak ditemukan.', $saved);
        $meeting_data = ['meeting_code' => strtoupper(trim((string) ($data['meeting_code'] ?? ''))), 'meeting_title' => trim((string) ($data['meeting_title'] ?? '')), 'meeting_date' => trim((string) ($data['meeting_date'] ?? '')), 'location' => trim((string) ($data['location'] ?? ''))];
        $date = DateTime::createFromFormat('!Y-m-d', $meeting_data['meeting_date']);
        if (!$meeting_data['meeting_code'] || strlen($meeting_data['meeting_code']) > 128 || !preg_match('/^[A-Z0-9._-]+$/', $meeting_data['meeting_code'])) return $this->rollback('Kode rapat tidak valid.', $saved);
        if (!$date || $date->format('Y-m-d') !== $meeting_data['meeting_date']) return $this->rollback('Tanggal rapat tidak valid.', $saved);
        if (!$meeting_data['meeting_title'] || strlen($meeting_data['meeting_title']) > 200 || !$meeting_data['location'] || strlen($meeting_data['location']) > 200) return $this->rollback('Data rapat wajib valid.', $saved);
        $decisions = $this->decisions(isset($data['decisions']) ? $data['decisions'] : []);
        if (!$decisions['valid'] || !$decisions['rows']) return $this->rollback('RTM wajib memiliki keputusan dan tindakan lengkap.', $saved);
        if (isset($saved['data'])) $meeting_data = array_merge($meeting_data, $saved['data']);
        $old_photo = $meeting ? $meeting->photo_stored_name : NULL;
        if (!$id) { $meeting_data['status'] = 'draft'; $meeting_data['created_by'] = (int) $user_id; $id = $this->model->insert_meeting($meeting_data); } else if (!$this->model->update_meeting($id, $meeting_data)) return $this->rollback('RTM gagal diperbarui.', $saved);
        if (!$id || !$this->model->delete_children($id)) return $this->rollback('Data RTM gagal disiapkan.', $saved);
        foreach ($report_ids as $report_id) if (!$this->model->insert_report_link(['meeting_id' => $id, 'report_id' => $report_id])) return $this->rollback('Laporan RTM gagal disimpan.', $saved);
        foreach ($users as $user) if (!$this->model->insert_participant(['meeting_id' => $id, 'user_id' => (int) $user->id, 'name_snapshot' => $user->nama, 'email_snapshot' => $user->email, 'role_snapshot' => $user->role])) return $this->rollback('Snapshot peserta gagal disimpan.', $saved);
        foreach ($decisions['rows'] as $decision) {
            if ($decision['report_id'] && !isset($reports[$decision['report_id']])) return $this->rollback('Laporan keputusan harus terhubung ke laporan RTM.', $saved);
            if ($decision['report_item_id']) { $item = $this->model->report_item_for_update($decision['report_item_id']); if (!$item || (int) $item->report_id !== (int) $decision['report_id']) return $this->rollback('Item keputusan tidak termasuk laporan terpilih.', $saved); }
            if (!$this->model->insert_decision(['meeting_id' => $id, 'display_order' => $decision['display_order'], 'decision_text' => $decision['decision_text'], 'action_text' => $decision['action_text'], 'report_id' => $decision['report_id'] ?: NULL, 'report_item_id' => $decision['report_item_id'] ?: NULL])) return $this->rollback('Keputusan RTM gagal disimpan.', $saved);
        }
        $result = $this->finish(['success' => TRUE, 'message' => 'RTM SPMI berhasil disimpan.', 'id' => $id]);
        if ($result['success'] && isset($saved['data']) && $old_photo && $old_photo !== $saved['data']['photo_stored_name']) delete_private_file('rtm_photos', $old_photo);
        if (!$result['success']) $this->cleanup_saved($saved);
        return $result;
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
    protected function save_photo($file)
    {
        if (!$this->has_upload($file)) return ['success' => TRUE];
        $validated = $this->validate_photo($file);
        if (!$validated['success']) return $validated;
        $dir = private_storage_dir('rtm_photos');
        if (!is_dir($dir) && !mkdir($dir, 0700, TRUE) && !is_dir($dir)) return ['success' => FALSE, 'message' => 'Foto dokumentasi RTM gagal disimpan.'];
        @chmod($dir, 0700);
        try { $name = bin2hex(random_bytes(24)) . '.' . $validated['extension']; } catch (Exception $e) { return ['success' => FALSE, 'message' => 'Foto dokumentasi RTM gagal disimpan.']; }
        $path = $dir . $name;
        if (!move_uploaded_file($file['tmp_name'], $path)) return ['success' => FALSE, 'message' => 'Foto dokumentasi RTM gagal disimpan.'];
        @chmod($path, 0600);
        $sha256 = hash_file('sha256', $path);
        if ($sha256 === FALSE) { @unlink($path); return ['success' => FALSE, 'message' => 'Foto dokumentasi RTM gagal disimpan.']; }
        return ['success' => TRUE, 'path' => $path, 'data' => ['photo_stored_name' => $name, 'photo_original_name' => basename($file['name']), 'photo_mime_type' => $validated['mime'], 'photo_size_bytes' => (int) $file['size'], 'photo_sha256' => $sha256]];
    }

    protected function validate_photo($file)
    {
        $limit = $this->upload_limit_bytes();
        $message = 'Foto dokumentasi RTM harus JPEG, PNG, atau WebP maksimal ' . $this->limit_mib($limit) . ' MiB.';
        if (!is_array($file) || !isset($file['error'], $file['tmp_name'], $file['size'], $file['name']) || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name']) || (int) $file['size'] < 1 || (int) $file['size'] > $limit) return ['success' => FALSE, 'message' => $message];
        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        if (!isset($allowed[$extension])) return ['success' => FALSE, 'message' => $message];
        $finfo = finfo_open(FILEINFO_MIME_TYPE); $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : FALSE; if ($finfo) finfo_close($finfo);
        return $mime === $allowed[$extension] ? ['success' => TRUE, 'mime' => $mime, 'extension' => $extension] : ['success' => FALSE, 'message' => 'Tipe foto dokumentasi RTM tidak valid.'];
    }

    protected function has_upload($file) { return is_array($file) && isset($file['error']) && (int) $file['error'] !== UPLOAD_ERR_NO_FILE; }
    protected function upload_limit_bytes() { require_once APPPATH . 'services/Upload_size_settings_service.php'; return (int) Upload_size_settings_service::limit_bytes('rtm_photos'); }
    protected function limit_mib($bytes) { return (int) ($bytes / 1024 / 1024); }
    protected function cleanup_saved($saved) { if (isset($saved['path']) && is_file($saved['path'])) unlink($saved['path']); }
    protected function rollback($message, $saved = []) { $this->ci->db->trans_rollback(); $this->cleanup_saved($saved); return ['success' => FALSE, 'message' => $message]; }
    protected function finish($result) { $this->ci->db->trans_complete(); return $this->ci->db->trans_status() ? $result : ['success' => FALSE, 'message' => 'RTM SPMI gagal disimpan.']; }
}
