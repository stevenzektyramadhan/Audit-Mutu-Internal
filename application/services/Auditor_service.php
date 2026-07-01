<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auditor_service
{
    protected $ci;
    protected $tugas_audit_model;
    protected $jawaban_audit_model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Tugas_audit_model');
        $this->ci->load->model('Jawaban_audit_model');

        $this->tugas_audit_model = $this->ci->Tugas_audit_model;
        $this->jawaban_audit_model = $this->ci->Jawaban_audit_model;
    }

    public function get_dashboard_data($auditor_id)
    {
        $auditor_id = (int) $auditor_id;
        $where = ['auditor_id' => $auditor_id];

        return [
            'stats' => [
                'total_tugas' => $this->tugas_audit_model->count_all($where),
                'belum_dinilai' => $this->tugas_audit_model->count_all($where, STATUS_DINILAI),
                'dinilai' => $this->tugas_audit_model->count_all($where + ['status' => STATUS_DINILAI]),
            ],
            'pending_tugas' => $this->tugas_audit_model->get_by_auditor($auditor_id, STATUS_DIISI, 5),
            'graded_tugas' => $this->tugas_audit_model->get_by_auditor($auditor_id, STATUS_DINILAI, 5),
        ];
    }

    public function get_tugas_data($auditor_id)
    {
        $auditor_id = (int) $auditor_id;

        return [
            'tugas' => $this->tugas_audit_model->get_by_auditor($auditor_id),
            'jumlah_siap_dinilai' => $this->tugas_audit_model->count_all([
                'auditor_id' => $auditor_id,
                'status' => STATUS_DIISI,
            ]),
        ];
    }

    public function get_penilaian_data($auditor_id)
    {
        $auditor_id = (int) $auditor_id;

        return [
            'siap_dinilai' => $this->tugas_audit_model->get_by_auditor($auditor_id, STATUS_DIISI),
            'sudah_dinilai' => $this->tugas_audit_model->get_by_auditor($auditor_id, STATUS_DINILAI),
            'jumlah_siap_dinilai' => $this->tugas_audit_model->count_all([
                'auditor_id' => $auditor_id,
                'status' => STATUS_DIISI,
            ]),
        ];
    }

    public function get_detail($tugas_id, $auditor_id)
    {
        $tugas = $this->tugas_audit_model->find_for_auditor($tugas_id, $auditor_id);

        if (!$tugas) {
            return ['success' => FALSE, 'message' => 'Tugas audit tidak ditemukan atau bukan milik Anda.'];
        }

        return [
            'success' => TRUE,
            'tugas' => $tugas,
            'jawaban' => $this->jawaban_audit_model->get_by_tugas($tugas_id),
        ];
    }

    public function simpan_nilai($tugas_id, $auditor_id, $input)
    {
        $detail = $this->get_detail($tugas_id, $auditor_id);

        if (!$detail['success']) {
            return $detail;
        }

        if ($detail['tugas']->status !== STATUS_DIISI) {
            $message = $detail['tugas']->status === STATUS_BELUM_DIISI
                ? 'Auditee belum mengisi tugas audit ini.'
                : 'Tugas audit ini sudah dinilai dan tidak dapat dinilai ulang.';

            return ['success' => FALSE, 'message' => $message];
        }

        $skor_input = isset($input['skor']) && is_array($input['skor']) ? $input['skor'] : [];
        $catatan_input = isset($input['catatan']) && is_array($input['catatan']) ? $input['catatan'] : [];
        $updates = [];

        foreach ($detail['jawaban'] as $jawaban) {
            $jawaban_id = (int) $jawaban->id;
            $skor = isset($skor_input[$jawaban_id]) ? (int) $skor_input[$jawaban_id] : 0;

            if (!in_array($skor, [1, 2, 3, 4], TRUE)) {
                return ['success' => FALSE, 'message' => 'Semua pertanyaan wajib diberi skor 1 sampai 4.'];
            }

            $updates[] = [
                'id' => $jawaban_id,
                'skor' => $skor,
                'temuan' => isset($catatan_input[$jawaban_id]) ? trim($catatan_input[$jawaban_id]) : '',
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        if (empty($updates)) {
            return ['success' => FALSE, 'message' => 'Tidak ada jawaban audit yang dapat dinilai.'];
        }

        $this->ci->db->trans_start();
        $this->jawaban_audit_model->update_penilaian_batch($updates);
        $this->tugas_audit_model->update_status($tugas_id, STATUS_DINILAI);
        $this->ci->db->trans_complete();

        if ($this->ci->db->trans_status() === FALSE) {
            return ['success' => FALSE, 'message' => 'Penilaian gagal disimpan. Silakan coba kembali.'];
        }

        return ['success' => TRUE, 'message' => 'Penilaian audit berhasil disimpan.'];
    }
}
