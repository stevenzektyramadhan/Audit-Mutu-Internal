<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_auditor_workspace_service
{
    const CONFLICT = 'Data telah diperbarui di sesi lain. Muat ulang halaman lalu coba lagi.';
    protected $ci;
    protected $model;

    public function __construct() { $this->ci = &get_instance(); $this->ci->load->model('Spmi_auditor_workspace_model'); $this->model = $this->ci->Spmi_auditor_workspace_model; }
    public function assignments($user_id) { return $this->model->assignments($user_id); }

    public function workspace($assignment_id, $user_id)
    {
        $assignment = $this->model->assignment($assignment_id, $user_id);
        if (!$assignment || ($assignment->state === 'closed' && !$assignment->assessment_id)) return NULL;
        if (!$assignment->assessment_id && $assignment->state === 'configured') {
            $created = $this->create($assignment_id, $user_id);
            if (!$created['success']) return NULL;
            $assignment = $this->model->assignment($assignment_id, $user_id);
        }
        $assessment = $this->model->assessment($assignment_id, $user_id);
        $items = $this->model->assignment_items($assignment_id, $user_id);
        $assessment_items = $assessment ? $this->model->assessment_items($assessment->id, $user_id) : [];
        $by_item = [];
        foreach ($assessment_items as $item) $by_item[(string) $item->assignment_item_id] = $item;
        foreach ($items as $item) { $item->rubrics = $this->model->rubrics($item->id, $user_id); $item->assessment = isset($by_item[(string) $item->id]) ? $by_item[(string) $item->id] : NULL; $item->evidence = $this->model->evidence($item->id, $user_id); if (!$item->assessment) $item->realization_snapshot = ''; }
        return ['assignment' => $assignment, 'assessment' => $assessment, 'items' => $items];
    }

    public function save($assignment_id, $user_id, $version, $values) { return $this->mutate($assignment_id, $user_id, $version, $values, FALSE); }
    public function finalize($assignment_id, $user_id, $version, $values) { return $this->mutate($assignment_id, $user_id, $version, $values, TRUE); }

    protected function create($assignment_id, $user_id)
    {
        $this->ci->db->trans_begin();
        $assignment = $this->model->assignment($assignment_id, $user_id, TRUE);
        $cycle = $assignment ? $this->model->cycle_for_update($assignment->cycle_id) : NULL;
        $submission = $assignment ? $this->model->submission_for_update($assignment->id) : NULL;
        if (!$assignment || !$cycle || $cycle->state !== 'configured' || !$submission || $submission->status !== 'submitted' || $this->model->assessment_for_update($assignment->id, $user_id)) return $this->rollback(self::CONFLICT);
        $assessment_id = $this->model->create_assessment($assignment->id);
        if (!$assessment_id) return $this->rollback('Penilaian SPMI gagal dibuat.');
        foreach ($this->model->submitted_items($assignment->id, $user_id) as $item) if (!$this->model->create_item(['assessment_id' => $assessment_id, 'assignment_item_id' => (int) $item->assignment_item_id, 'realization_snapshot' => (string) $item->realization])) return $this->rollback('Snapshot realisasi gagal dibuat.');
        return $this->finish('Penilaian SPMI berhasil dibuka.');
    }

    protected function mutate($assignment_id, $user_id, $version, $values, $finalize)
    {
        $this->ci->db->trans_begin();
        $assignment = $this->model->assignment($assignment_id, $user_id, TRUE);
        $cycle = $assignment ? $this->model->cycle_for_update($assignment->cycle_id) : NULL;
        $submission = $assignment ? $this->model->submission_for_update($assignment->id) : NULL;
        $assessment = $assignment ? $this->model->assessment_for_update($assignment->id, $user_id) : NULL;
        if (!$assignment || !$cycle || !$submission || $cycle->state !== 'configured' || $submission->status !== 'submitted' || !$assessment || $assessment->status !== 'draft' || (int) $assessment->version !== (int) $version || !is_array($values)) return $this->rollback(self::CONFLICT);
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
            if ($finalize && $score === NULL) return $this->rollback('Semua item wajib diberi skor 1 sampai 4 sebelum finalisasi.');
            if (!$this->model->update_item($item->id, ['score' => $score, 'finding' => $this->text($value, 'finding'), 'recommendation' => $this->text($value, 'recommendation')])) return $this->rollback('Penilaian SPMI gagal disimpan.');
        }
        if (!$this->model->update_assessment($assessment->id, $version, $finalize ? 'finalized' : 'draft') || $this->ci->db->affected_rows() !== 1) return $this->rollback(self::CONFLICT);
        return $this->finish($finalize ? 'Penilaian SPMI berhasil difinalisasi.' : 'Draft penilaian SPMI berhasil disimpan.');
    }

    public function download($evidence_id, $user_id) { $evidence = $this->model->evidence_for_read($evidence_id, $user_id); if (!$evidence) return NULL; $path = private_storage_path('audit_evidence', $evidence->stored_name); return $path && is_file($path) ? ['path' => $path, 'mime' => $evidence->mime_type, 'name' => $evidence->original_name] : NULL; }
    protected function text($value, $key) { return trim((string) (isset($value[$key]) ? $value[$key] : '')) ?: NULL; }
    protected function rollback($message) { $this->ci->db->trans_rollback(); return ['success' => FALSE, 'message' => $message]; }
    protected function finish($message) { $this->ci->db->trans_complete(); return ['success' => $this->ci->db->trans_status(), 'message' => $message]; }
}
