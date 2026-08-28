<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_auditor_workspace_service
{
    const CONFLICT = 'Data telah diperbarui di sesi lain. Muat ulang halaman lalu coba lagi.';

    protected $ci;
    protected $model;
    protected $drive_storage;

    public function __construct() { $this->ci = &get_instance(); $this->ci->load->model('Spmi_auditor_workspace_model'); $this->model = $this->ci->Spmi_auditor_workspace_model; }
    public function assignments($user_id, $filters) { return $this->model->assignments($user_id, $this->filters($filters)); }
    public function cycle_options($user_id) { return $this->model->cycle_options($user_id); }
    public function attention_count($user_id) { return $this->model->attention_count($user_id); }

    protected function filters($filters)
    {
        $cycle_id = isset($filters['cycle_id']) && is_scalar($filters['cycle_id']) && ctype_digit((string) $filters['cycle_id']) ? (int) $filters['cycle_id'] : 0;
        $status = isset($filters['status']) && is_scalar($filters['status']) ? trim((string) $filters['status']) : '';
        return [
            'cycle_id' => $cycle_id,
            'status' => in_array($status, ['submitted', 'resubmitted', 'returned_for_revision'], TRUE) ? $status : '',
        ];
    }

    public function workspace($assignment_id, $user_id)
    {
        $assignment = $this->model->assignment($assignment_id, $user_id);
        if (!$assignment || ($assignment->state === 'closed' && !$assignment->assessment_id)) return NULL;
        if (!$assignment->assessment_id && $assignment->state === 'configured' && in_array($assignment->submission_status, ['submitted', 'resubmitted'], TRUE)) {
            $created = $this->create($assignment_id, $user_id);
            if (!$created['success']) return NULL;
            $assignment = $this->model->assignment($assignment_id, $user_id);
        }
        $assessment = $this->model->assessment($assignment_id, $user_id, $assignment->submission_version);
        $items = $this->model->assignment_items($assignment_id, $user_id);
        $assessment_items = $assessment ? $this->model->assessment_items($assessment->id, $user_id) : [];
        $by_item = [];
        foreach ($assessment_items as $item) $by_item[(string) $item->assignment_item_id] = $item;
        foreach ($items as $item) { $item->rubrics = $this->model->rubrics($item->id, $user_id); $item->assessment = isset($by_item[(string) $item->id]) ? $by_item[(string) $item->id] : NULL; $item->evidence = $this->model->evidence($item->id, $user_id); $item->auditor_evidence = $item->assessment ? $this->model->auditor_evidence($item->assessment->id, $user_id) : []; if (!$item->assessment) $item->realization_snapshot = ''; }
        return ['assignment' => $assignment, 'assessment' => $assessment, 'items' => $items, 'revision_history' => $this->model->revision_history($assignment_id, $user_id)];
    }

    public function save($assignment_id, $user_id, $version, $source_submission_version, $values) { return $this->mutate($assignment_id, $user_id, $version, $source_submission_version, $values, FALSE); }
    public function save_item($assessment_item_id, $user_id, $version, $source_submission_version, $value)
    {
        if (!$this->valid_int($version) || !$this->valid_int($source_submission_version)) return ['success' => FALSE, 'message' => self::CONFLICT, 'version' => (int) $version];
        $this->ci->db->trans_begin();
        $item = $this->model->assessment_item_autosave_for_update($assessment_item_id, $user_id);
        if (!$item || $item->state !== 'configured' || !in_array($item->submission_status, ['submitted', 'resubmitted'], TRUE) || $item->assessment_status !== 'draft' || (int) $item->version !== (int) $version || (int) $source_submission_version !== (int) $item->submission_version || (int) $item->source_submission_version !== (int) $item->submission_version) return $this->versioned($this->rollback(self::CONFLICT), $version);
        $validated = $this->validate_item_value($value);
        if (!$validated['success']) return $this->versioned($this->rollback($validated['message']), $version);
        if (!$this->model->update_item($item->id, $validated['data'])) return $this->versioned($this->rollback('Penilaian SPMI gagal disimpan.'), $version);
        if (!$this->model->update_assessment($item->assessment_id, $version) || $this->ci->db->affected_rows() !== 1) return $this->versioned($this->rollback(self::CONFLICT), $version);
        $result = $this->finish('Draft item penilaian SPMI berhasil disimpan.');
        if (!$result['success']) return $this->versioned($result, $version);
        return ['success' => TRUE, 'message' => $result['message'], 'version' => (int) $version + 1];
    }
    public function finalize($assignment_id, $user_id, $version, $source_submission_version, $values) { return $this->mutate($assignment_id, $user_id, $version, $source_submission_version, $values, TRUE); }

    protected function create($assignment_id, $user_id)
    {
        $this->ci->db->trans_begin();
        $assignment = $this->model->assignment($assignment_id, $user_id, TRUE);
        $cycle = $assignment ? $this->model->cycle_for_update($assignment->cycle_id) : NULL;
        $submission = $assignment ? $this->model->submission_for_update($assignment->id) : NULL;
        if (!$assignment || !$cycle || $cycle->state !== 'configured' || !$submission || !in_array($submission->status, ['submitted', 'resubmitted'], TRUE) || $this->model->assessment_for_update($assignment->id, $user_id, $submission->version)) return $this->rollback(self::CONFLICT);
        $assessment_id = $this->model->create_assessment($assignment->id, $submission->version);
        if (!$assessment_id) return $this->rollback('Penilaian SPMI gagal dibuat.');
        foreach ($this->model->submitted_items($assignment->id, $user_id) as $item) if (!$this->model->create_item(['assessment_id' => $assessment_id, 'assignment_item_id' => (int) $item->assignment_item_id, 'realization_snapshot' => (string) $item->realization])) return $this->rollback('Snapshot realisasi gagal dibuat.');
        return $this->finish('Penilaian SPMI berhasil dibuka.');
    }

    protected function mutate($assignment_id, $user_id, $version, $source_submission_version, $values, $finalize)
    {
        if (!$this->valid_int($version) || !$this->valid_int($source_submission_version)) return ['success' => FALSE, 'message' => self::CONFLICT];
        $this->ci->db->trans_begin();
        $assignment = $this->model->assignment($assignment_id, $user_id, TRUE);
        $cycle = $assignment ? $this->model->cycle_for_update($assignment->cycle_id) : NULL;
        $submission = $assignment ? $this->model->submission_for_update($assignment->id) : NULL;
        $assessment = $assignment ? $this->model->assessment_for_update($assignment->id, $user_id, $submission->version) : NULL;
        if (!$assignment || !$cycle || !$submission || $cycle->state !== 'configured' || !in_array($submission->status, ['submitted', 'resubmitted'], TRUE) || !$assessment || $assessment->status !== 'draft' || (int) $assessment->version !== (int) $version || (int) $source_submission_version !== (int) $submission->version || !is_array($values)) return $this->rollback(self::CONFLICT);
        if ($assessment->source_submission_version === NULL && $submission->status === 'resubmitted') return $this->rollback(self::CONFLICT);
        if ($assessment->source_submission_version !== NULL && (int) $assessment->source_submission_version !== (int) $submission->version) return $this->rollback(self::CONFLICT);
        $items = $this->model->assessment_items_for_update($assessment->id, $user_id);
        $expected = [];
        foreach ($items as $item) $expected[(string) $item->assignment_item_id] = $item;
        foreach ($values as $item_id => $value) if (!isset($expected[(string) $item_id]) || !is_array($value)) return $this->rollback('Data penilaian tidak valid.');
        foreach ($items as $item) {
            $value = isset($values[$item->assignment_item_id]) ? $values[$item->assignment_item_id] : [];
            $raw_score = array_key_exists('score', $value) ? $value['score'] : '';
            if ($raw_score === '') $score = NULL;
            elseif (!is_string($raw_score) || !in_array($raw_score, ['1', '2', '3', '4'], TRUE)) return $this->rollback('Skor wajib kosong atau bernilai 1 sampai 4.');
            else $score = (int) $raw_score;
            $raw_finding_type = array_key_exists('finding_type', $value) ? $value['finding_type'] : '';
            if ($raw_finding_type === '') $finding_type = NULL;
            elseif (!is_string($raw_finding_type) || !in_array($raw_finding_type, ['ob', 'kts'], TRUE)) return $this->rollback('Jenis temuan wajib kosong, OB, atau KTS.');
            else $finding_type = $raw_finding_type;
            $finding = $this->text($value, 'finding');
            $recommendation = $this->text($value, 'recommendation');
            $improvement_plan = $this->text($value, 'improvement_plan');
            $evidence_date = $this->date($value, 'evidence_date');
            if ($evidence_date === FALSE) return $this->rollback('Tanggal bukti tidak valid.');
            if ($finalize && $score === NULL) return $this->rollback('Semua item wajib diberi skor 1 sampai 4 sebelum finalisasi.');
            if ($finalize && in_array($finding_type, ['ob', 'kts'], TRUE) && $finding === NULL) return $this->rollback('Uraian temuan wajib diisi untuk OB atau KTS sebelum finalisasi.');
            if ($finalize && $finding_type === 'kts' && $recommendation === NULL) return $this->rollback('Rekomendasi wajib diisi untuk KTS sebelum finalisasi.');
            if ($finalize && $finding_type === 'kts' && $improvement_plan === NULL) return $this->rollback('Rencana perbaikan wajib diisi untuk KTS sebelum finalisasi.');
            if (!$this->model->update_item($item->id, ['score' => $score, 'finding_type' => $finding_type, 'finding' => $finding, 'recommendation' => $recommendation, 'improvement_plan' => $improvement_plan, 'evidence_date' => $evidence_date])) return $this->rollback('Penilaian SPMI gagal disimpan.');
        }
        if (!$this->model->update_assessment($assessment->id, $version, $finalize ? 'finalized' : 'draft') || $this->ci->db->affected_rows() !== 1) return $this->rollback(self::CONFLICT);
        return $this->finish($finalize ? 'Penilaian SPMI berhasil difinalisasi.' : 'Draft penilaian SPMI berhasil disimpan.');
    }

    public function return_for_revision($assignment_id, $user_id, $submission_version, $reason)
    {
        $reason = trim($reason);
        if ($reason === '') return ['success' => FALSE, 'message' => 'Alasan revisi wajib diisi.'];
        $this->ci->db->trans_begin();
        $assignment = $this->model->assignment($assignment_id, $user_id, TRUE);
        $cycle = $assignment ? $this->model->cycle_for_update($assignment->cycle_id) : NULL;
        $submission = $assignment ? $this->model->submission_for_update($assignment->id) : NULL;
        $assessment = $assignment ? $this->model->assessment_for_update($assignment->id, $user_id, $submission->version) : NULL;
        if (!$assignment || !$cycle || !$submission || $cycle->state !== 'configured' || ($submission->status !== 'submitted' && $submission->status !== 'resubmitted') || (int) $submission->version !== (int) $submission_version || ($assessment && $assessment->status === 'finalized')) return $this->rollback(self::CONFLICT);
        $next_version = (int) $submission->version + 1;
        $event_status = ['status' => 'returned_for_revision'];
        if (!$this->model->update_submission_status($submission->id, $submission->version, ['submitted', 'resubmitted'], $event_status['status']) || $this->ci->db->affected_rows() !== 1) return $this->rollback(self::CONFLICT);
        if (!$this->model->add_revision_event(['submission_id' => (int) $submission->id, 'assignment_id' => (int) $assignment->id, 'actor_user_id' => (int) $user_id, 'reason' => $reason, 'submission_version' => $next_version, 'previous_status' => (string) $submission->status, 'new_status' => 'returned_for_revision', 'previous_version' => (int) $submission->version, 'resulting_version' => $next_version])) return $this->rollback('Riwayat revisi gagal disimpan.');
        return $this->finish('Submission dikembalikan untuk revisi.');
    }

    public function download($evidence_id, $user_id) { $evidence = $this->model->evidence_for_read($evidence_id, $user_id); if (!$evidence) return NULL; if ($this->is_drive_evidence($evidence)) return ['backend' => 'google_drive', 'drive_file_id' => $evidence->drive_file_id, 'mime' => $evidence->mime_type, 'name' => $evidence->original_name, 'size' => (int) $evidence->size_bytes]; $path = private_storage_path('audit_evidence', $evidence->stored_name); return $path && is_file($path) ? ['backend' => 'local', 'path' => $path, 'mime' => $evidence->mime_type, 'name' => $evidence->original_name] : NULL; }
    public function upload_auditor_evidence($assessment_item_id, $user_id, $version, $source_submission_version, $file)
    {
        $this->ci->db->trans_begin();
        $item = $this->model->assessment_item_for_update($assessment_item_id, $user_id, $version);
        if (!$this->valid_int($source_submission_version) || !$item || $item->state !== 'configured' || $item->assessment_status !== 'draft' || !in_array($item->submission_status, ['submitted', 'resubmitted'], TRUE) || (int) $source_submission_version !== (int) $item->submission_version || (int) $item->source_submission_version !== (int) $item->submission_version) return $this->rollback(self::CONFLICT);
        if ($this->model->lock_auditor_evidence_count($item->id) >= 5) return $this->rollback('Maksimal 5 bukti per item.');
        $validated = $this->validate_file($file);
        if (!$validated['success']) return $this->rollback($validated['message']);
        $use_drive = $this->use_drive_storage();
        $dir = private_storage_dir('audit_evidence');
        if (!is_dir($dir) && !mkdir($dir, 0700, TRUE) && !is_dir($dir)) return $this->rollback('Bukti gagal disimpan.');
        $paths = [];
        try { $name = bin2hex(random_bytes(24)) . '.' . $validated['extension']; } catch (Exception $e) { return $this->rollback('Bukti gagal disimpan.'); }
        $path = $dir . $name;
        $paths[] = $path;
        if (!move_uploaded_file($file['tmp_name'], $path)) return $this->rollback('Bukti gagal disimpan.');
        $hash = hash_file('sha256', $path);
        $drive_file_id = NULL;
        $drive_folder_id = NULL;
        if ($hash !== FALSE && $use_drive) {
            $upload = $this->drive_storage()->upload($path, $name, $validated['mime']);
            if (!$upload['success']) { $this->cleanup_paths($paths); return $this->rollback('Bukti gagal disimpan.'); }
            $drive_file_id = $upload['file_id'];
            $drive_folder_id = $this->ci->config->item('google_drive_evidence_folder_id');
        }
        $data = ['assessment_item_id' => $item->id, 'stored_name' => $name, 'original_name' => basename($file['name']), 'mime_type' => $validated['mime'], 'size_bytes' => (int) $file['size'], 'sha256' => $hash, 'storage_backend' => $use_drive ? 'google_drive' : 'local', 'drive_file_id' => $drive_file_id, 'drive_folder_id' => $drive_folder_id];
        if ($hash === FALSE || !$this->model->add_auditor_evidence($data) || !$this->model->update_assessment($item->assessment_id, $version) || $this->ci->db->affected_rows() !== 1) { $this->ci->db->trans_rollback(); if ($drive_file_id !== NULL) { $trash = $this->drive_storage()->trash($drive_file_id); if (!$trash['success']) $this->record_drive_trash_failure('spmi_auditor_assessment_evidence', NULL, $drive_file_id, $drive_folder_id, $name); } $this->cleanup_paths($paths); return ['success' => FALSE, 'message' => self::CONFLICT]; }
        $result = $this->finish('Bukti auditor berhasil ditambahkan.');
        if (!$result['success'] && $drive_file_id !== NULL) { $trash = $this->drive_storage()->trash($drive_file_id); if (!$trash['success']) $this->record_drive_trash_failure('spmi_auditor_assessment_evidence', NULL, $drive_file_id, $drive_folder_id, $name); }
        if ($use_drive || !$result['success']) $this->cleanup_paths($paths);
        return $result;
    }
    public function delete_auditor_evidence($evidence_id, $user_id, $version, $source_submission_version)
    {
        $this->ci->db->trans_begin();
        $evidence = $this->model->auditor_evidence_for_update($evidence_id, $user_id, $version);
        if (!$this->valid_int($source_submission_version) || !$evidence || $evidence->state !== 'configured' || $evidence->assessment_status !== 'draft' || !in_array($evidence->submission_status, ['submitted', 'resubmitted'], TRUE) || (int) $source_submission_version !== (int) $evidence->submission_version || (int) $evidence->source_submission_version !== (int) $evidence->submission_version) return $this->rollback(self::CONFLICT);
        if (!$this->model->delete_auditor_evidence($evidence_id) || !$this->model->update_assessment($evidence->assessment_id, $version) || $this->ci->db->affected_rows() !== 1) return $this->rollback(self::CONFLICT);
        $result = $this->finish('Bukti auditor berhasil dihapus.');
        if ($result['success'] && $this->is_drive_evidence($evidence)) { $trash = $this->drive_storage()->trash($evidence->drive_file_id); if (!$trash['success']) $this->record_drive_trash_failure('spmi_auditor_assessment_evidence', (int) $evidence->id, $evidence->drive_file_id, $evidence->drive_folder_id, $evidence->stored_name); }
        $path = private_storage_path('audit_evidence', $evidence->stored_name);
        if ($result['success'] && !$this->is_drive_evidence($evidence) && $path && is_file($path)) unlink($path);
        return $result;
    }
    public function download_auditor_evidence($evidence_id, $user_id) { $evidence = $this->model->auditor_evidence_for_read($evidence_id, $user_id); if (!$evidence) return NULL; if ($this->is_drive_evidence($evidence)) return ['backend' => 'google_drive', 'drive_file_id' => $evidence->drive_file_id, 'mime' => $evidence->mime_type, 'name' => $evidence->original_name, 'size' => (int) $evidence->size_bytes]; $path = private_storage_path('audit_evidence', $evidence->stored_name); return $path && is_file($path) ? ['backend' => 'local', 'path' => $path, 'mime' => $evidence->mime_type, 'name' => $evidence->original_name] : NULL; }
    public function stream_drive_download($file_id, $output) { return $this->drive_storage()->stream_download($file_id, $output); }
    public function assignment_id_for_assessment_item($assessment_item_id, $user_id) { $row = $this->model->assignment_id_for_assessment_item($assessment_item_id, $user_id); return $row ? (int) $row->id : 0; }
    public function assignment_id_for_auditor_evidence($evidence_id, $user_id) { $row = $this->model->assignment_id_for_auditor_evidence($evidence_id, $user_id); return $row ? (int) $row->id : 0; }
    protected function validate_file($file) { if (!is_array($file) || !isset($file['error'], $file['tmp_name'], $file['size'], $file['name']) || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name']) || (int) $file['size'] > 5 * 1024 * 1024) return ['success' => FALSE, 'message' => 'Bukti harus berupa PDF, JPEG, atau PNG maksimal 5 MiB.']; $finfo = finfo_open(FILEINFO_MIME_TYPE); $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : FALSE; if ($finfo) finfo_close($finfo); $image = @getimagesize($file['tmp_name']); $allowed = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png']; if (!isset($allowed[$mime]) || (($mime === 'image/jpeg' || $mime === 'image/png') && (!$image || $image['mime'] !== $mime))) return ['success' => FALSE, 'message' => 'Bukti harus berupa PDF, JPEG, atau PNG maksimal 5 MiB.']; return ['success' => TRUE, 'mime' => $mime, 'extension' => $allowed[$mime]]; }
    protected function cleanup_paths($paths) { foreach ($paths as $path) if (is_file($path)) unlink($path); }
    protected function use_drive_storage() { return $this->ci->config->item('spmi_evidence_storage_backend') === 'google_drive'; }
    protected function drive_storage() { if (!$this->drive_storage) { $this->ci->load->library('Google_drive_evidence_storage'); $this->drive_storage = $this->ci->google_drive_evidence_storage; } return $this->drive_storage; }
    protected function is_drive_evidence($evidence) { return isset($evidence->storage_backend, $evidence->drive_file_id) && $evidence->storage_backend === 'google_drive' && $evidence->drive_file_id !== ''; }
    protected function record_drive_trash_failure($source_table, $source_id, $drive_file_id, $drive_folder_id, $stored_name) { $this->model->add_drive_trash_outbox(['source_table' => $source_table, 'source_id' => $source_id, 'operation' => 'trash', 'drive_file_id' => $drive_file_id, 'drive_folder_id' => $drive_folder_id, 'stored_name' => $stored_name, 'status' => 'pending', 'last_error' => 'drive_trash_failed']); }
    protected function validate_item_value($value)
    {
        $fields = ['score', 'finding_type', 'finding', 'recommendation', 'improvement_plan', 'evidence_date'];
        if (!is_array($value) || count(array_diff(array_keys($value), $fields)) || count(array_diff($fields, array_keys($value)))) return ['success' => FALSE, 'message' => 'Data penilaian tidak valid.'];
        foreach ($fields as $field) if (!is_scalar($value[$field]) && $value[$field] !== NULL) return ['success' => FALSE, 'message' => 'Data penilaian tidak valid.'];
        $raw_score = (string) $value['score'];
        if ($raw_score === '') $score = NULL;
        elseif (!in_array($raw_score, ['1', '2', '3', '4'], TRUE)) return ['success' => FALSE, 'message' => 'Skor wajib kosong atau bernilai 1 sampai 4.'];
        else $score = (int) $raw_score;
        $raw_finding_type = (string) $value['finding_type'];
        if ($raw_finding_type === '') $finding_type = NULL;
        elseif (!in_array($raw_finding_type, ['ob', 'kts'], TRUE)) return ['success' => FALSE, 'message' => 'Jenis temuan wajib kosong, OB, atau KTS.'];
        else $finding_type = $raw_finding_type;
        $evidence_date = $this->date($value, 'evidence_date');
        if ($evidence_date === FALSE) return ['success' => FALSE, 'message' => 'Tanggal bukti tidak valid.'];
        return ['success' => TRUE, 'data' => ['score' => $score, 'finding_type' => $finding_type, 'finding' => $this->text($value, 'finding'), 'recommendation' => $this->text($value, 'recommendation'), 'improvement_plan' => $this->text($value, 'improvement_plan'), 'evidence_date' => $evidence_date]];
    }
    protected function text($value, $key) { return trim((string) (isset($value[$key]) ? $value[$key] : '')) ?: NULL; }
    protected function date($value, $key) { if (!isset($value[$key]) || $value[$key] === '') return NULL; if (!is_string($value[$key])) return FALSE; $date = trim($value[$key]); return $date === '' || (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && date('Y-m-d', strtotime($date)) === $date) ? $date : FALSE; }
    protected function valid_int($value) { return (is_int($value) && $value >= 0) || (is_string($value) && ctype_digit($value)); }
    protected function rollback($message) { $this->ci->db->trans_rollback(); return ['success' => FALSE, 'message' => $message]; }
    protected function finish($message) { $this->ci->db->trans_complete(); return ['success' => $this->ci->db->trans_status(), 'message' => $message]; }
    protected function versioned($result, $version) { $result['version'] = (int) $version; return $result; }
}
