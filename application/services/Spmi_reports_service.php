<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'services/Upload_size_settings_service.php';

class Spmi_reports_service
{
    const FINAL_SUBMISSION_STATUSES = ['submitted', 'resubmitted'];

    protected $ci;
    protected $model;

    public function __construct() { $this->ci = &get_instance(); $this->ci->load->model('Spmi_reports_model'); $this->model = $this->ci->Spmi_reports_model; }
    public function index_data(array $filters = [], $page = 1, $per_page = 15)
    {
        $page = $this->positive_integer(isset($filters['page']) ? $filters['page'] : $page) ?: 1;
        $per_page = $this->positive_integer($per_page) ?: 15;
        $clean_filters = $this->normalize_index_filters($filters, $this->model->index_options());
        $clean_filters = $this->normalize_index_filters($filters, $this->model->index_options($clean_filters));
        $clean_filters['page'] = $page;
        $options = $this->model->index_options($clean_filters);
        $all_reports = $this->model->reports($clean_filters);
        foreach ($all_reports as $report) $report->is_historical = FALSE;
        $all_reports = array_merge($all_reports, $this->model->historical_reports($clean_filters));
        usort($all_reports, function ($left, $right) {
            $generated_at = strtotime((string) $right->generated_at) <=> strtotime((string) $left->generated_at);
            return $generated_at ?: ((int) $right->id <=> (int) $left->id);
        });
        $count = count($all_reports);
        $page_count = max(1, (int) ceil($count / $per_page));
        if ($clean_filters['page'] > $page_count) $clean_filters['page'] = $page_count;
        $summary = $this->model->index_summary($clean_filters);
        $summary->report_count = $count;
        return ['filters' => $clean_filters, 'options' => $options, 'count' => $count, 'pagination' => ['page' => $clean_filters['page'], 'per_page' => $per_page, 'page_count' => $page_count], 'reports' => array_slice($all_reports, ($clean_filters['page'] - 1) * $per_page, $per_page), 'summary' => $summary, 'standard_analysis' => $this->model->index_standard_analysis($clean_filters), 'historical_enabled' => $this->model->historical_table_exists(), 'historical_upload_max_bytes' => Upload_size_settings_service::limit_bytes('ppepp_documents')];
    }
    protected function normalize_index_filters($input, $options)
    {
        $value = function ($name) use ($input) { return isset($input[$name]) && is_scalar($input[$name]) ? trim((string) $input[$name]) : ''; };
        $filters = ['academic_year' => $value('academic_year'), 'cycle_id' => $this->positive_integer($value('cycle_id')), 'version_id' => $this->positive_integer($value('version_id')), 'auditee_id' => $this->positive_integer($value('auditee_id')), 'q' => substr($value('q'), 0, 120), 'page' => $this->positive_integer($value('page')) ?: 1];
        $valid = ['academic_year' => [], 'cycle_id' => [], 'version_id' => [], 'auditee_id' => []];
        foreach ($options['academic_years'] as $row) $valid['academic_year'][] = (string) $row->academic_year_snapshot;
        foreach ($options['cycles'] as $row) $valid['cycle_id'][] = (int) $row->source_cycle_id;
        foreach ($options['versions'] as $row) $valid['version_id'][] = (int) $row->source_version_id;
        foreach ($options['auditees'] as $row) $valid['auditee_id'][] = (int) $row->auditee_id_snapshot;
        if ($filters['academic_year'] !== '' && !in_array($filters['academic_year'], $valid['academic_year'], TRUE)) $filters['academic_year'] = '';
        foreach (['cycle_id', 'version_id', 'auditee_id'] as $name) if ($filters[$name] && !in_array($filters[$name], $valid[$name], TRUE)) $filters[$name] = 0;
        return $filters;
    }
    public function finalized_assessments() { return $this->model->finalized_assessments(); }
    public function finalized_versions() { return $this->model->finalized_versions(); }
    public function report($id) { $report = $this->model->report_by_id($id); if (!$report) return NULL; $items = $this->model->report_items($report->id); $standards = []; $contributors = []; foreach ($items as $item) { $code = $item->source_standard_code_snapshot ?: $report->source_standard_code_snapshot; $title = $item->source_standard_title_snapshot ?: $report->source_standard_title_snapshot; $key = (string) $code . "\n" . (string) $title; if (!isset($standards[$key])) $standards[$key] = ['source_standard_code_snapshot' => $code, 'source_standard_title_snapshot' => $title, 'items' => []]; $standards[$key]['items'][] = $item; $auditor_id = isset($item->auditor_id_snapshot) ? (int) $item->auditor_id_snapshot : 0; $auditor_name = isset($item->auditor_name_snapshot) ? trim((string) $item->auditor_name_snapshot) : ''; $auditor_email = isset($item->auditor_email_snapshot) ? trim((string) $item->auditor_email_snapshot) : ''; if ($auditor_id > 0 && $auditor_name !== '') $contributors[$auditor_id . "\n" . $auditor_email] = ['id' => $auditor_id, 'name' => $auditor_name, 'email' => $auditor_email]; } if (!$contributors && trim((string) $report->auditor_name_snapshot) !== '') $contributors['legacy'] = ['id' => isset($report->auditor_id_snapshot) ? (int) $report->auditor_id_snapshot : 0, 'name' => $report->auditor_name_snapshot, 'email' => '']; return ['report' => $report, 'items' => $items, 'standards' => array_values($standards), 'contributors' => array_values($contributors)]; }
    public function historical_report($id) { return $this->model->historical_report($id); }

    public function store_historical_report(array $input, $file, $user_id)
    {
        if (!$this->model->historical_table_exists()) return ['success' => FALSE, 'message' => 'Arsip laporan historis belum tersedia. Jalankan migrasi 051 terlebih dahulu.'];
        $payload = $this->historical_payload($input, $user_id);
        if (!$payload['success']) return $payload;
        $saved = $this->save_historical_upload($file);
        if (!$saved['success']) return $saved;
        $this->ci->db->trans_begin();
        $id = $this->model->insert_historical_report(array_merge($payload['data'], $saved['data']));
        if (!$id) return $this->rollback_historical('Laporan historis gagal disimpan.', $saved);
        $result = $this->finish_historical(['success' => TRUE, 'message' => 'Laporan historis berhasil disimpan.', 'id' => $id]);
        if (!$result['success']) $this->cleanup_historical_upload($saved);
        return $result;
    }

    public function delete_historical_report($id, $user_id)
    {
        if (!$this->model->historical_table_exists()) return ['success' => FALSE, 'message' => 'Arsip laporan historis belum tersedia. Jalankan migrasi 051 terlebih dahulu.'];
        $this->ci->db->trans_begin();
        $row = $this->model->historical_report($id);
        if (!$row) return $this->rollback_historical('Laporan historis tidak ditemukan.');
        if (!$this->model->delete_historical_report($id)) return $this->rollback_historical('Laporan historis gagal dihapus.');
        $result = $this->finish_historical(['success' => TRUE, 'message' => 'Laporan historis berhasil dihapus.']);
        if ($result['success']) delete_private_file('spmi_historical_reports', $row->stored_name);
        return $result;
    }

    protected function historical_payload(array $input, $user_id)
    {
        $title = $this->historical_text($input, 'title', 200, TRUE);
        $academic_year = trim((string) (isset($input['academic_year']) ? $input['academic_year'] : ''));
        $report_number = $this->historical_text($input, 'report_number', 128, FALSE);
        $version_label = $this->historical_text($input, 'version_label', 200, FALSE);
        $auditee_name = $this->historical_text($input, 'auditee_name', 200, FALSE);
        $report_date = $this->historical_date($input, 'report_date');
        $description = $this->historical_text($input, 'description', 2000, FALSE);
        if ($title === FALSE) return ['success' => FALSE, 'message' => 'Judul laporan historis wajib diisi dan maksimal 200 karakter.'];
        if (!preg_match('/^\d{4}(\/\d{4})?$/', $academic_year)) return ['success' => FALSE, 'message' => 'Tahun akademik laporan historis tidak valid.'];
        if ($report_number === FALSE) return ['success' => FALSE, 'message' => 'Nomor laporan historis maksimal 128 karakter.'];
        if ($version_label === FALSE || $auditee_name === FALSE) return ['success' => FALSE, 'message' => 'Label versi atau nama auditi laporan historis terlalu panjang.'];
        if ($report_date === FALSE) return ['success' => FALSE, 'message' => 'Tanggal laporan historis tidak valid.'];
        if ($description === FALSE) return ['success' => FALSE, 'message' => 'Deskripsi laporan historis maksimal 2000 karakter.'];
        if ($report_number === NULL) {
            try {
                $report_number = 'HIST-' . substr($academic_year, 0, 4) . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            } catch (Throwable $exception) {
                return ['success' => FALSE, 'message' => 'Nomor laporan historis gagal dibuat.'];
            }
        }
        return ['success' => TRUE, 'data' => ['report_number' => $report_number, 'title' => $title, 'academic_year' => $academic_year, 'version_label' => $version_label, 'auditee_name' => $auditee_name, 'report_date' => $report_date, 'description' => $description, 'uploaded_by' => (int) $user_id]];
    }

    protected function save_historical_upload($file)
    {
        $validated = $this->validate_historical_upload($file);
        if (!$validated['success']) return $validated;
        try {
            $directory = private_storage_dir('spmi_historical_reports');
            $stored_name = bin2hex(random_bytes(24)) . '.' . $validated['extension'];
        } catch (Throwable $exception) {
            return ['success' => FALSE, 'message' => 'File laporan historis gagal disimpan.'];
        }
        if (!is_dir($directory) || !is_writable($directory)) return ['success' => FALSE, 'message' => 'File laporan historis gagal disimpan.'];
        chmod($directory, 0700);
        $path = $directory . $stored_name;
        if (!move_uploaded_file($file['tmp_name'], $path)) return ['success' => FALSE, 'message' => 'File laporan historis gagal disimpan.'];
        chmod($path, 0600);
        return ['success' => TRUE, 'path' => $path, 'data' => ['stored_name' => $stored_name, 'original_name' => basename((string) $file['name']), 'mime_type' => $validated['mime'], 'file_size' => (int) $file['size']]];
    }

    protected function validate_historical_upload($file)
    {
        $limit_bytes = Upload_size_settings_service::limit_bytes('ppepp_documents');
        $limit_mib = (int) ($limit_bytes / 1024 / 1024);
        $message = 'File laporan historis harus PDF, Word, atau Excel maksimal ' . $limit_mib . ' MiB.';
        if (!is_array($file) || !isset($file['error'], $file['tmp_name'], $file['size'], $file['name']) || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name']) || (int) $file['size'] > $limit_bytes) return ['success' => FALSE, 'message' => $message];
        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $allowed_mimes = [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword', 'application/vnd.ms-word', 'application/x-msword', 'application/CDFV2'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
            'xls' => ['application/vnd.ms-excel', 'application/msexcel', 'application/x-msexcel', 'application/x-ms-excel', 'application/CDFV2'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
        ];
        if (!isset($allowed_mimes[$extension])) return ['success' => FALSE, 'message' => $message];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : FALSE;
        if ($finfo) finfo_close($finfo);
        if ($mime === FALSE || !in_array($mime, $allowed_mimes[$extension], TRUE)) return ['success' => FALSE, 'message' => 'Tipe file laporan historis tidak valid.'];
        if (in_array($extension, ['docx', 'xlsx'], TRUE) && $mime === 'application/zip' && !$this->valid_historical_ooxml($file['tmp_name'], $extension)) return ['success' => FALSE, 'message' => 'File OOXML tidak valid: struktur dokumen Office tidak ditemukan.'];
        if (in_array($extension, ['doc', 'xls'], TRUE) && $mime === 'application/CDFV2' && !$this->valid_historical_ole($file['tmp_name'])) return ['success' => FALSE, 'message' => 'File Office lama tidak valid.'];
        return ['success' => TRUE, 'mime' => $mime, 'extension' => $extension];
    }

    protected function valid_historical_ooxml($path, $extension)
    {
        $markers = ['docx' => 'word/document.xml', 'xlsx' => 'xl/workbook.xml'];
        $zip = new ZipArchive();
        if (!isset($markers[$extension]) || $zip->open($path) !== TRUE) return FALSE;
        $valid = $zip->locateName('[Content_Types].xml') !== FALSE && $zip->locateName($markers[$extension]) !== FALSE;
        $zip->close();
        return $valid;
    }

    protected function valid_historical_ole($path)
    {
        $handle = fopen($path, 'rb');
        if (!$handle) return FALSE;
        $header = fread($handle, 8);
        fclose($handle);
        return $header !== FALSE && substr($header, 0, 8) === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";
    }

    protected function historical_text(array $input, $key, $max, $required)
    {
        $value = trim((string) (isset($input[$key]) ? $input[$key] : ''));
        if ($value === '') return $required ? FALSE : NULL;
        return strlen($value) <= $max ? $value : FALSE;
    }

    protected function historical_date(array $input, $key)
    {
        $value = trim((string) (isset($input[$key]) ? $input[$key] : ''));
        if ($value === '') return NULL;
        $date = DateTime::createFromFormat('!Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value ? $value : FALSE;
    }

    protected function cleanup_historical_upload($saved)
    {
        if (isset($saved['path']) && is_file($saved['path'])) unlink($saved['path']);
    }

    protected function rollback_historical($message, $saved = [])
    {
        $this->ci->db->trans_rollback();
        $this->cleanup_historical_upload($saved);
        return ['success' => FALSE, 'message' => $message];
    }

    protected function finish_historical($result)
    {
        $this->ci->db->trans_complete();
        return $this->ci->db->trans_status() ? $result : ['success' => FALSE, 'message' => 'Laporan historis gagal disimpan.'];
    }

    public function generate($assessment_id, $user_id)
    {
        $this->ci->db->trans_begin();
        $assessment = $this->model->assessment_for_update($assessment_id);
        $cycle = $assessment ? $this->model->cycle_for_update($assessment->cycle_id) : NULL;
        if (!$assessment || $assessment->status !== 'finalized' || !$assessment->finalized_at || !$cycle || !in_array($cycle->state, ['configured', 'closed'], TRUE)) return $this->rollback('Assessment M9 belum memenuhi syarat laporan versi.');
        $existing = $this->model->version_auditee_report_for_update($assessment->cycle_id, $assessment->source_version_id, $assessment->auditee_id);
        if ($existing) return $this->finish_existing($existing);
        $assignments = $this->model->assignments_for_version_auditee_report_for_update($assessment->cycle_id, $assessment->source_version_id, $assessment->auditee_id);
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
        $report_number = 'SPMI-' . strtoupper(preg_replace('/[^A-Za-z0-9-]/', '-', (string) $assessment->cycle_code)) . '-' . strtoupper(preg_replace('/[^A-Za-z0-9-]/', '-', (string) $assessment->source_version_code)) . '-V-' . (int) $assessment->auditee_id;
        $academic_year_snapshot = trim((string) $cycle->academic_year);
        $report_id = $this->model->insert_report(['assessment_id' => NULL, 'report_number' => $report_number, 'report_scope' => 'version_auditee', 'source_cycle_id' => (int) $assessment->cycle_id, 'academic_year_snapshot' => $academic_year_snapshot === '' ? NULL : $academic_year_snapshot, 'cycle_code_snapshot' => $assessment->cycle_code, 'cycle_title_snapshot' => $assessment->cycle_title, 'cycle_start_date_snapshot' => $assessment->cycle_start_date, 'cycle_end_date_snapshot' => $assessment->cycle_end_date, 'source_version_id' => (int) $assessment->source_version_id, 'source_version_code_snapshot' => $assessment->source_version_code, 'source_version_title_snapshot' => $assessment->source_version_title, 'source_standard_code_snapshot' => NULL, 'source_standard_title_snapshot' => NULL, 'auditor_name_snapshot' => 'Tim auditor', 'auditor_id_snapshot' => NULL, 'auditee_name_snapshot' => $assessment->auditee_name, 'auditee_id_snapshot' => (int) $assessment->auditee_id, 'assessment_finalized_at_snapshot' => $finalized_at, 'generated_by' => (int) $user_id]);
        if (!$report_id || $this->ci->db->affected_rows() !== 1) return $this->rollback('Laporan SPMI gagal dibuat.');
        foreach ($report_items as $index => $report_item) {
            $assignment = $report_item['assignment']; $item = $report_item['item']; $rubric = $report_item['rubric'];
            $auditor_evidence = [];
            foreach ($this->model->auditor_evidence_for_report($item->id) as $evidence) $auditor_evidence[] = ['original_name' => (string) $evidence->original_name, 'mime_type' => (string) $evidence->mime_type, 'size_bytes' => (int) $evidence->size_bytes, 'sha256' => (string) $evidence->sha256];
            $auditor_evidence_snapshot = json_encode($auditor_evidence);
            if ($auditor_evidence_snapshot === FALSE || !$this->model->insert_item(['report_id' => $report_id, 'auditor_id_snapshot' => (int) $assignment->auditor_id, 'auditor_name_snapshot' => $assignment->auditor_name, 'auditor_email_snapshot' => $assignment->auditor_email, 'source_standard_id' => (int) $assignment->source_standard_id, 'source_standard_code_snapshot' => $assignment->source_standard_code, 'source_standard_title_snapshot' => $assignment->source_standard_title, 'source_standard_display_order' => (int) $assignment->source_standard_display_order, 'display_order' => $index + 1, 'standard_item_display_order' => (int) $item->display_order, 'indicator_code_snapshot' => $item->indicator_code, 'indicator_title_snapshot' => $item->indicator_title, 'realization_snapshot' => $item->realization_snapshot, 'evidence_url_snapshot' => $item->evidence_url_snapshot, 'evidence_file_original_name_snapshot' => $item->evidence_file_original_name_snapshot, 'evidence_file_mime_type_snapshot' => $item->evidence_file_mime_type_snapshot, 'evidence_file_size_bytes_snapshot' => $item->evidence_file_size_bytes_snapshot, 'evidence_file_sha256_snapshot' => $item->evidence_file_sha256_snapshot, 'auditor_evidence_snapshot' => $auditor_evidence_snapshot, 'score' => (int) $item->score, 'descriptor_snapshot' => $rubric->descriptor, 'finding_snapshot' => $item->finding, 'finding_type_snapshot' => $item->finding_type, 'recommendation_snapshot' => $item->recommendation, 'improvement_plan_snapshot' => $item->improvement_plan, 'evidence_date_snapshot' => $item->evidence_date])) return $this->rollback('Item laporan SPMI gagal dibuat.');
        }
        return $this->finish(['success' => TRUE, 'message' => 'Laporan SPMI berhasil dibuat.', 'report_id' => $report_id]);
    }

    protected function finish_existing($report) { $this->ci->db->trans_commit(); return ['success' => TRUE, 'message' => 'Laporan SPMI sudah tersedia.', 'report_id' => (int) $report->id]; }
    protected function positive_integer($value) { return is_scalar($value) && preg_match('/^[1-9][0-9]*$/', (string) $value) ? (int) $value : 0; }
    protected function rollback($message) { $this->ci->db->trans_rollback(); return ['success' => FALSE, 'message' => $message]; }
    protected function finish($result) { $this->ci->db->trans_complete(); return $this->ci->db->trans_status() ? $result : ['success' => FALSE, 'message' => 'Laporan SPMI gagal disimpan.']; }
}
