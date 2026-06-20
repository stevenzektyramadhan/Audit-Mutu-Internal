<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auditee_service
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

    public function get_tugas_data($auditee_id, $status = NULL)
    {
        $auditee_id = (int) $auditee_id;

        return [
            'tugas' => $this->tugas_audit_model->get_by_auditee($auditee_id, $status),
            'jumlah_belum_diisi' => $this->tugas_audit_model->count_all([
                'auditee_id' => $auditee_id,
                'status' => STATUS_BELUM_DIISI,
            ]),
        ];
    }

    public function get_detail($tugas_id, $auditee_id)
    {
        $tugas = $this->tugas_audit_model->find_for_auditee($tugas_id, $auditee_id);

        if (!$tugas) {
            return ['success' => FALSE, 'message' => 'Tugas audit tidak ditemukan atau bukan milik Anda.'];
        }

        return [
            'success' => TRUE,
            'tugas' => $tugas,
            'jawaban' => $this->jawaban_audit_model->get_by_tugas($tugas_id),
        ];
    }

    public function simpan_jawaban($tugas_id, $auditee_id, $input)
    {
        $detail = $this->get_detail($tugas_id, $auditee_id);

        if (!$detail['success']) {
            return $detail;
        }

        if ($detail['tugas']->status !== STATUS_BELUM_DIISI) {
            return ['success' => FALSE, 'message' => 'Jawaban tugas ini sudah dikirim dan tidak dapat diubah.'];
        }

        $jawaban_input = isset($input['jawaban']) && is_array($input['jawaban']) ? $input['jawaban'] : [];
        $link_input = isset($input['link_bukti']) && is_array($input['link_bukti']) ? $input['link_bukti'] : [];
        $updates = [];

        foreach ($detail['jawaban'] as $item) {
            $jawaban_id = (int) $item->id;
            $jawaban = isset($jawaban_input[$jawaban_id]) ? trim($jawaban_input[$jawaban_id]) : '';
            $link_bukti = isset($link_input[$jawaban_id]) ? trim($link_input[$jawaban_id]) : '';

            if (!$this->is_valid_evidence_url($link_bukti)) {
                return [
                    'success' => FALSE,
                    'message' => 'Setiap pertanyaan wajib memiliki link bukti dengan format URL http atau https yang valid.',
                ];
            }

            $updates[] = [
                'id' => $jawaban_id,
                'jawaban' => $jawaban,
                'link_bukti' => $link_bukti,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        if (empty($updates)) {
            return ['success' => FALSE, 'message' => 'Tidak ada pertanyaan audit yang dapat diisi.'];
        }

        $this->ci->db->trans_start();
        $this->jawaban_audit_model->update_jawaban_batch($updates);
        $this->tugas_audit_model->update_status($tugas_id, STATUS_DIISI);
        $this->ci->db->trans_complete();

        if ($this->ci->db->trans_status() === FALSE) {
            return ['success' => FALSE, 'message' => 'Jawaban gagal disimpan. Silakan coba kembali.'];
        }

        return ['success' => TRUE, 'message' => 'Jawaban dan link bukti berhasil dikirim kepada auditor.'];
    }

    private function is_valid_evidence_url($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            return FALSE;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], TRUE);
    }
}
