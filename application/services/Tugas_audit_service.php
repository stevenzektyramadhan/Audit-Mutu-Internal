<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tugas_audit_service
{
    protected $ci;
    protected $tugas_audit_model;
    protected $jawaban_audit_model;
    protected $user_model;
    protected $standar_model;
    protected $pertanyaan_model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Tugas_audit_model');
        $this->ci->load->model('Jawaban_audit_model');
        $this->ci->load->model('User_model');
        $this->ci->load->model('Standar_model');
        $this->ci->load->model('Pertanyaan_model');
        $this->tugas_audit_model = $this->ci->Tugas_audit_model;
        $this->jawaban_audit_model = $this->ci->Jawaban_audit_model;
        $this->user_model = $this->ci->User_model;
        $this->standar_model = $this->ci->Standar_model;
        $this->pertanyaan_model = $this->ci->Pertanyaan_model;
    }

    public function get_all_tugas($filters = [])
    {
        return $this->tugas_audit_model->get_all_tugas($this->normalize_filters($filters, TRUE));
    }
    
    public function get_hasil_audit($filters = [])
    {
        return $this->tugas_audit_model->get_hasil_audit_with_stats($this->normalize_filters($filters, FALSE));
    }

    public function get_form_options()
    {
        return [
            'auditor' => $this->user_model->get_by_role('auditor'),
            'auditee' => $this->user_model->get_by_role('auditee'),
            'standar' => $this->standar_model->get_all_with_count(),
        ];
    }

    public function get_detail($id)
    {
        $tugas = $this->tugas_audit_model->find_with_relations($id);

        if (!$tugas) {
            return ['success' => FALSE, 'message' => 'Tugas audit tidak ditemukan.'];
        }

        $jawaban = $this->jawaban_audit_model->get_by_tugas($id);
        $total_skor = 0;
        $jumlah_skor = 0;

        foreach ($jawaban as $item) {
            if ($item->skor !== NULL) {
                $total_skor += (int) $item->skor;
                $jumlah_skor++;
            }
        }

        return [
            'success' => TRUE,
            'tugas' => $tugas,
            'jawaban' => $jawaban,
            'rata_rata' => $jumlah_skor > 0 ? $total_skor / $jumlah_skor : NULL,
        ];
    }

    public function create_tugas($data)
    {
        $auditor = $this->user_model->find(isset($data['auditor_id']) ? $data['auditor_id'] : 0);
        $auditee = $this->user_model->find(isset($data['auditee_id']) ? $data['auditee_id'] : 0);
        $standar = $this->standar_model->find(isset($data['standar_id']) ? $data['standar_id'] : 0);

        if (!$auditor || $auditor->role !== 'auditor') {
            return ['success' => FALSE, 'message' => 'Auditor yang dipilih tidak valid.'];
        }

        if (!$auditee || $auditee->role !== 'auditee') {
            return ['success' => FALSE, 'message' => 'Auditee yang dipilih tidak valid.'];
        }

        if (!$standar) {
            return ['success' => FALSE, 'message' => 'Standar yang dipilih tidak valid.'];
        }

        $pertanyaan = $this->pertanyaan_model->get_by_standar($standar->id);
        if (empty($pertanyaan)) {
            return ['success' => FALSE, 'message' => 'Standar belum memiliki pertanyaan audit.'];
        }

        $data = [
            'auditor_id' => (int) $auditor->id,
            'auditee_id' => (int) $auditee->id,
            'standar_id' => (int) $standar->id,
            'status' => STATUS_BELUM_DIISI,
        ];

        $this->ci->db->trans_start();

        // 1. Insert tugas_audit
        $tugas_id = $this->tugas_audit_model->create($data);

        $jawaban_data = [];
        foreach ($pertanyaan as $p) {
            $jawaban_data[] = [
                'tugas_audit_id' => $tugas_id,
                'pertanyaan_id' => $p->id
            ];
        }
        $this->jawaban_audit_model->insert_batch($jawaban_data);

        $this->ci->db->trans_complete();

        if ($this->ci->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Gagal membuat tugas audit.'];
        }

        return ['success' => true, 'message' => 'Tugas audit berhasil dibuat.'];
    }

    public function delete_tugas($id)
    {
        if (!$this->tugas_audit_model->find_with_relations($id)) {
            return ['success' => FALSE, 'message' => 'Tugas audit tidak ditemukan.'];
        }

        if ($this->tugas_audit_model->delete($id)) {
            return ['success' => TRUE, 'message' => 'Tugas audit dan seluruh jawabannya berhasil dihapus.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal menghapus tugas audit.'];
    }

    private function normalize_filters($filters, $with_status)
    {
        $result = ['q' => trim(isset($filters['q']) ? $filters['q'] : '')];

        if ($with_status) {
            $status = isset($filters['status']) ? $filters['status'] : '';
            $result['status'] = in_array($status, [STATUS_BELUM_DIISI, STATUS_DIISI, STATUS_DINILAI], TRUE)
                ? $status
                : '';
        }

        return $result;
    }
}
