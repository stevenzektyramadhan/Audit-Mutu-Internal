<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_reports_service
{
    const FINAL_SUBMISSION_STATUSES = ['submitted', 'resubmitted'];

    protected $ci;
    protected $model;

    public function __construct() { $this->ci = &get_instance(); $this->ci->load->model('Spmi_reports_model'); $this->model = $this->ci->Spmi_reports_model; }
    public function reports() { return $this->model->reports(); }
    public function report_cycles() { return $this->model->report_cycles(); }
    public function score_recap($cycle_code)
    {
        $cycle_code = trim((string) $cycle_code);
        if ($cycle_code === '') return [];

        $rows = $this->model->score_recap_per_indicator_per_auditee($cycle_code);

        $grouped = [];
        foreach ($rows as $row) {
            $auditee = $row->auditee_name;
            if (!isset($grouped[$auditee])) {
                $grouped[$auditee] = ['labels' => [], 'values' => []];
            }
            $grouped[$auditee]['labels'][] = $row->item_code;
            $grouped[$auditee]['values'][] = (int) $row->score;
        }
        return $grouped;
    }
    public function finalized_assessments() { return $this->model->finalized_assessments(); }
    public function finalized_versions() { return $this->model->finalized_versions(); }
    public function version_report_selector($cycle_id, $report_id)
    {
        $cycle_id = $this->positive_integer($cycle_id);
        $report_id = $this->positive_integer($report_id);
        $selector = ['version_report_cycles' => $this->model->version_report_cycles(), 'selected_report_cycle_id' => $cycle_id, 'version_reports' => [], 'selected_version_report' => NULL];
        if (!$cycle_id) return $selector;
        $selector['version_reports'] = $this->model->version_reports_for_cycle($cycle_id);
        if ($report_id) $selector['selected_version_report'] = $this->model->version_report_for_cycle($cycle_id, $report_id);
        return $selector;
    }
    public function report($id) { $report = $this->model->report_by_id($id); if (!$report) return NULL; $items = $this->model->report_items($report->id); $standards = []; foreach ($items as $item) { $code = $item->source_standard_code_snapshot ?: $report->source_standard_code_snapshot; $title = $item->source_standard_title_snapshot ?: $report->source_standard_title_snapshot; $key = (string) $code . "\n" . (string) $title; if (!isset($standards[$key])) $standards[$key] = ['source_standard_code_snapshot' => $code, 'source_standard_title_snapshot' => $title, 'items' => []]; $standards[$key]['items'][] = $item; } return ['report' => $report, 'items' => $items, 'standards' => array_values($standards)]; }

    public function generate($assessment_id, $user_id)
    {
        $this->ci->db->trans_begin();
        $assessment = $this->model->assessment_for_update($assessment_id);
        $cycle = $assessment ? $this->model->cycle_for_update($assessment->cycle_id) : NULL;
        if (!$assessment || $assessment->status !== 'finalized' || !$assessment->finalized_at || !$cycle || !in_array($cycle->state, ['configured', 'closed'], TRUE)) return $this->rollback('Assessment M9 belum memenuhi syarat laporan versi.');
        $existing = $this->model->version_report_for_update($assessment->cycle_id, $assessment->source_version_id, $assessment->auditor_id, $assessment->auditee_id);
        if ($existing) return $this->finish_existing($existing);
        $assignments = $this->model->assignments_for_version_report_for_update($assessment->cycle_id, $assessment->source_version_id, $assessment->auditor_id, $assessment->auditee_id);
        if (!$assignments) return $this->rollback('Scope standar laporan versi tidak ditemukan.');
        $report_items = [];
        $finalized_at = NULL;
        foreach ($assignments as $assignment) {
            $final_assessment = $this->model->finalized_assessment_for_assignment_for_update($assignment->id);
            if (!$final_assessment) return $this->rollback('Semua standar penugasan pada versi wajib finalized sebelum laporan dibuat.');
            $submission = $this->model->submission_for_update($assignment->id, $final_assessment->source_submission_version);
            if (!$submission || (int) $final_assessment->source_submission_version !== (int) $submission->version) return $this->rollback('Assessment M9 tidak cocok dengan versi submission aktif.');
            $items = $this->model->submission_items_for_report($final_assessment->id, $submission->id);
            if (!$items || count($items) !== $this->model->assignment_items_count($assignment->id)) return $this->rollback('Set item assessment M9 belum lengkap.');
            foreach ($items as $item) { if ($item->score === NULL || (int) $item->score < 1 || (int) $item->score > 4) return $this->rollback('Semua item assessment M9 wajib memiliki skor 1 sampai 4.'); $rubric = $this->model->rubric($item->assignment_item_id, $item->score); if (!$rubric || trim((string) $rubric->descriptor) === '') return $this->rollback('Rubrik M7 tidak cocok dengan skor assessment M9.'); $report_items[] = ['assignment' => $assignment, 'item' => $item, 'rubric' => $rubric]; }
            if ($finalized_at === NULL || $finalized_at < $final_assessment->finalized_at) $finalized_at = $final_assessment->finalized_at;
        }
        $report_number = 'SPMI-' . strtoupper(preg_replace('/[^A-Za-z0-9-]/', '-', (string) $assessment->cycle_code)) . '-' . strtoupper(preg_replace('/[^A-Za-z0-9-]/', '-', (string) $assessment->source_version_code)) . '-V-' . (int) $assessment->auditee_id . '-' . (int) $assessment->auditor_id;
        $report_id = $this->model->insert_report(['assessment_id' => NULL, 'report_number' => $report_number, 'report_scope' => 'version', 'source_cycle_id' => (int) $assessment->cycle_id, 'cycle_code_snapshot' => $assessment->cycle_code, 'cycle_title_snapshot' => $assessment->cycle_title, 'cycle_start_date_snapshot' => $assessment->cycle_start_date, 'cycle_end_date_snapshot' => $assessment->cycle_end_date, 'source_version_id' => (int) $assessment->source_version_id, 'source_version_code_snapshot' => $assessment->source_version_code, 'source_version_title_snapshot' => $assessment->source_version_title, 'source_standard_code_snapshot' => NULL, 'source_standard_title_snapshot' => NULL, 'auditor_name_snapshot' => $assessment->auditor_name, 'auditor_id_snapshot' => (int) $assessment->auditor_id, 'auditee_name_snapshot' => $assessment->auditee_name, 'auditee_id_snapshot' => (int) $assessment->auditee_id, 'assessment_finalized_at_snapshot' => $finalized_at, 'generated_by' => (int) $user_id]);
        if (!$report_id || $this->ci->db->affected_rows() !== 1) return $this->rollback('Laporan SPMI gagal dibuat.');
        foreach ($report_items as $index => $report_item) {
            $assignment = $report_item['assignment']; $item = $report_item['item']; $rubric = $report_item['rubric'];
            $auditor_evidence = [];
            foreach ($this->model->auditor_evidence_for_report($item->id) as $evidence) $auditor_evidence[] = ['original_name' => (string) $evidence->original_name, 'mime_type' => (string) $evidence->mime_type, 'size_bytes' => (int) $evidence->size_bytes, 'sha256' => (string) $evidence->sha256];
            $auditor_evidence_snapshot = json_encode($auditor_evidence);
            if ($auditor_evidence_snapshot === FALSE || !$this->model->insert_item(['report_id' => $report_id, 'source_standard_id' => (int) $assignment->source_standard_id, 'source_standard_code_snapshot' => $assignment->source_standard_code, 'source_standard_title_snapshot' => $assignment->source_standard_title, 'source_standard_display_order' => (int) $assignment->source_standard_display_order, 'display_order' => $index + 1, 'standard_item_display_order' => (int) $item->display_order, 'indicator_code_snapshot' => $item->indicator_code, 'indicator_title_snapshot' => $item->indicator_title, 'realization_snapshot' => $item->realization_snapshot, 'evidence_url_snapshot' => $item->evidence_url_snapshot, 'evidence_file_original_name_snapshot' => $item->evidence_file_original_name_snapshot, 'evidence_file_mime_type_snapshot' => $item->evidence_file_mime_type_snapshot, 'evidence_file_size_bytes_snapshot' => $item->evidence_file_size_bytes_snapshot, 'evidence_file_sha256_snapshot' => $item->evidence_file_sha256_snapshot, 'auditor_evidence_snapshot' => $auditor_evidence_snapshot, 'score' => (int) $item->score, 'descriptor_snapshot' => $rubric->descriptor, 'finding_snapshot' => $item->finding, 'finding_type_snapshot' => $item->finding_type, 'recommendation_snapshot' => $item->recommendation, 'improvement_plan_snapshot' => $item->improvement_plan, 'evidence_date_snapshot' => $item->evidence_date])) return $this->rollback('Item laporan SPMI gagal dibuat.');
        }
        return $this->finish(['success' => TRUE, 'message' => 'Laporan SPMI berhasil dibuat.', 'report_id' => $report_id]);
    }

    protected function finish_existing($report) { $this->ci->db->trans_commit(); return ['success' => TRUE, 'message' => 'Laporan SPMI sudah tersedia.', 'report_id' => (int) $report->id]; }
    protected function positive_integer($value) { return is_scalar($value) && preg_match('/^[1-9][0-9]*$/', (string) $value) ? (int) $value : 0; }
    protected function rollback($message) { $this->ci->db->trans_rollback(); return ['success' => FALSE, 'message' => $message]; }
    protected function finish($result) { $this->ci->db->trans_complete(); return $this->ci->db->trans_status() ? $result : ['success' => FALSE, 'message' => 'Laporan SPMI gagal disimpan.']; }
}
