<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tugas_audit_service
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

    public function get_all_tugas()
    {
        return $this->tugas_audit_model->get_all_tugas();
    }
    
    public function get_hasil_audit()
    {
        return $this->tugas_audit_model->get_hasil_audit_with_stats();
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
            $this->jawaban_audit_model->insert_batch($jawaban_data);
        }

        $this->ci->db->trans_complete();

        if ($this->ci->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Gagal membuat tugas audit.'];
        }

        return ['success' => true, 'message' => 'Tugas audit berhasil dibuat.'];
    }
}
