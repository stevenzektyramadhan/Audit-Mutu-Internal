<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tugas_audit_service
{
    protected $ci;
    protected $tugas_audit_model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Tugas_audit_model');
        $this->tugas_audit_model = $this->ci->Tugas_audit_model;
    }

    public function get_all_tugas()
    {
        return $this->tugas_audit_model->get_all_tugas();
    }
    
    public function get_hasil_audit()
    {
        return $this->tugas_audit_model->get_hasil_audit_with_stats();
    }

    public function create_tugas($data)
    {
        $this->ci->db->trans_start();

        // 1. Insert tugas_audit
        $tugas_id = $this->tugas_audit_model->create($data);

        // 2. Ambil daftar pertanyaan berdasarkan standar_id
        $pertanyaan = $this->ci->db->where('standar_id', $data['standar_id'])->get('pertanyaan')->result();

        // 3. Insert baris kosong ke jawaban_audit
        if (!empty($pertanyaan)) {
            $jawaban_data = [];
            foreach ($pertanyaan as $p) {
                $jawaban_data[] = [
                    'tugas_audit_id' => $tugas_id,
                    'pertanyaan_id' => $p->id
                ];
            }
            $this->ci->load->model('Jawaban_audit_model');
            $this->ci->Jawaban_audit_model->insert_batch($jawaban_data);
        }

        $this->ci->db->trans_complete();

        if ($this->ci->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Gagal membuat tugas audit.'];
        }

        return ['success' => true, 'message' => 'Tugas audit berhasil dibuat.'];
    }
}
