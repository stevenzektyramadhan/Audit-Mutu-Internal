<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_reports_service
{
    const FINAL_SUBMISSION_STATUSES = ['submitted', 'resubmitted'];

    protected $ci;
    protected $model;

    public function __construct() { $this->ci = &get_instance(); $this->ci->load->model('Spmi_reports_model'); $this->model = $this->ci->Spmi_reports_model; }
    public function reports() { return $this->model->reports(); }
    public function finalized_assessments() { return $this->model->finalized_assessments(); }
    public function report($id) { $report = $this->model->report_by_id($id); return $report ? ['report' => $report, 'items' => $this->model->report_items($report->id)] : NULL; }

    public function generate($assessment_id, $user_id)
    {
        $this->ci->db->trans_begin();
        $assessment = $this->model->assessment_for_update($assessment_id);
        $existing = $this->model->report_for_assessment_for_update($assessment_id);
        if ($existing) return $this->finish_existing($existing);
        $cycle = $assessment ? $this->model->cycle_for_update($assessment->cycle_id) : NULL;
        $submission = $assessment ? $this->model->submission_for_update($assessment->assignment_id, $assessment->source_submission_version) : NULL;
        if (!$assessment || $assessment->status !== 'finalized' || !$assessment->finalized_at || !$cycle || !in_array($cycle->state, ['configured', 'closed'], TRUE) || !$submission || !in_array($submission->status, self::FINAL_SUBMISSION_STATUSES, TRUE)) return $this->rollback('Assessment M9 belum memenuhi syarat laporan.');
        if ((int) $assessment->source_submission_version !== (int) $submission->version) return $this->rollback('Assessment M9 tidak cocok dengan versi submission aktif.');
        $items = $this->model->submission_items_for_report($assessment->id, $submission->id);
        if (!$items || count($items) !== $this->model->assignment_items_count($assessment->assignment_id)) return $this->rollback('Set item assessment M9 belum lengkap.');
        foreach ($items as $item) {
            if ($item->score === NULL || (int) $item->score < 1 || (int) $item->score > 4) return $this->rollback('Semua item assessment M9 wajib memiliki skor 1 sampai 4.');
            $rubric = $this->model->rubric($item->assignment_item_id, $item->score);
            if (!$rubric || trim((string) $rubric->descriptor) === '') return $this->rollback('Rubrik M7 tidak cocok dengan skor assessment M9.');
        }
        $report_number = 'SPMI-' . strtoupper(preg_replace('/[^A-Za-z0-9-]/', '-', (string) $assessment->cycle_code)) . '-' . str_pad((string) $assessment->id, 6, '0', STR_PAD_LEFT);
        $report_id = $this->model->insert_report(['assessment_id' => (int) $assessment->id, 'report_number' => $report_number, 'cycle_code_snapshot' => $assessment->cycle_code, 'cycle_title_snapshot' => $assessment->cycle_title, 'cycle_start_date_snapshot' => $assessment->cycle_start_date, 'cycle_end_date_snapshot' => $assessment->cycle_end_date, 'source_version_code_snapshot' => $assessment->source_version_code, 'source_version_title_snapshot' => $assessment->source_version_title, 'source_standard_code_snapshot' => $assessment->source_standard_code, 'source_standard_title_snapshot' => $assessment->source_standard_title, 'source_package_code_snapshot' => $assessment->source_package_code, 'source_package_title_snapshot' => $assessment->source_package_title, 'auditor_name_snapshot' => $assessment->auditor_name, 'auditee_name_snapshot' => $assessment->auditee_name, 'assessment_finalized_at_snapshot' => $assessment->finalized_at, 'generated_by' => (int) $user_id]);
        if (!$report_id || $this->ci->db->affected_rows() !== 1) return $this->rollback('Laporan SPMI gagal dibuat.');
        foreach ($items as $item) {
            $rubric = $this->model->rubric($item->assignment_item_id, $item->score);
            $auditor_evidence = [];
            foreach ($this->model->auditor_evidence_for_report($item->id) as $evidence) $auditor_evidence[] = ['original_name' => (string) $evidence->original_name, 'mime_type' => (string) $evidence->mime_type, 'size_bytes' => (int) $evidence->size_bytes, 'sha256' => (string) $evidence->sha256];
            $auditor_evidence_snapshot = json_encode($auditor_evidence);
            if ($auditor_evidence_snapshot === FALSE || !$this->model->insert_item(['report_id' => $report_id, 'display_order' => (int) $item->display_order, 'question_code_snapshot' => $item->question_code, 'question_text_snapshot' => $item->question_text, 'indicator_code_snapshot' => $item->indicator_code, 'indicator_title_snapshot' => $item->indicator_title, 'realization_snapshot' => $item->realization_snapshot, 'evidence_url_snapshot' => $item->evidence_url_snapshot, 'evidence_file_original_name_snapshot' => $item->evidence_file_original_name_snapshot, 'evidence_file_mime_type_snapshot' => $item->evidence_file_mime_type_snapshot, 'evidence_file_size_bytes_snapshot' => $item->evidence_file_size_bytes_snapshot, 'evidence_file_sha256_snapshot' => $item->evidence_file_sha256_snapshot, 'auditor_evidence_snapshot' => $auditor_evidence_snapshot, 'score' => (int) $item->score, 'descriptor_snapshot' => $rubric->descriptor, 'finding_snapshot' => $item->finding, 'finding_type_snapshot' => $item->finding_type, 'recommendation_snapshot' => $item->recommendation, 'improvement_plan_snapshot' => $item->improvement_plan, 'evidence_date_snapshot' => $item->evidence_date])) return $this->rollback('Item laporan SPMI gagal dibuat.');
        }
        return $this->finish(['success' => TRUE, 'message' => 'Laporan SPMI berhasil dibuat.', 'report_id' => $report_id]);
    }

    protected function finish_existing($report) { $this->ci->db->trans_commit(); return ['success' => TRUE, 'message' => 'Laporan SPMI sudah tersedia.', 'report_id' => (int) $report->id]; }
    protected function rollback($message) { $this->ci->db->trans_rollback(); return ['success' => FALSE, 'message' => $message]; }
    protected function finish($result) { $this->ci->db->trans_complete(); return $this->ci->db->trans_status() ? $result : ['success' => FALSE, 'message' => 'Laporan SPMI gagal disimpan.']; }
}
