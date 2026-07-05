<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jawaban_model extends CI_Model
{
    protected $table = 'jawaban_audit';

    public function get_inbox_by_auditee($auditee_id, $periode_id = 0, $display_status = '')
    {
        if (!$this->tables_exist(['tugas_audit', 'jawaban_audit', 'users', 'standar', 'periode_audit'])) {
            return [];
        }

        $this->db
            ->select('tugas_audit.id, tugas_audit.status AS tugas_status, tugas_audit.created_at, tugas_audit.periode_id')
            ->select('standar.nama_standar, standar.file_instrumen')
            ->select('auditor.nama AS auditor_nama')
            ->select('periode_audit.nama_periode, periode_audit.tanggal_buka, periode_audit.tanggal_tutup')
            ->select('COUNT(jawaban_audit.id) AS jumlah_pertanyaan', FALSE)
            ->select("SUM(CASE WHEN (jawaban_audit.jawaban IS NOT NULL AND TRIM(jawaban_audit.jawaban) != '') OR (jawaban_audit.link_bukti IS NOT NULL AND TRIM(jawaban_audit.link_bukti) != '') THEN 1 ELSE 0 END) AS jumlah_terisi", FALSE)
            ->select('MIN(COALESCE(jawaban_audit.is_submitted, 0)) AS min_is_submitted', FALSE)
            ->select('MAX(COALESCE(jawaban_audit.is_submitted, 0)) AS max_is_submitted', FALSE)
            ->select('MAX(jawaban_audit.submitted_at) AS submitted_at', FALSE)
            ->select('SUM(CASE WHEN jawaban_audit.submitted_at IS NOT NULL THEN 1 ELSE 0 END) AS jumlah_pernah_submit', FALSE)
            ->select('SUM(CASE WHEN jawaban_audit.skor IS NOT NULL OR jawaban_audit.is_nilai_submitted = 1 THEN 1 ELSE 0 END) AS jumlah_dinilai', FALSE)
            ->select('AVG(jawaban_audit.skor) AS rata_rata', FALSE)
            ->from('tugas_audit')
            ->join('jawaban_audit', 'jawaban_audit.tugas_id = tugas_audit.id', 'left')
            ->join('standar', 'standar.id = tugas_audit.standar_id', 'left')
            ->join('users AS auditor', 'auditor.id = tugas_audit.auditor_id', 'left')
            ->join('periode_audit', 'periode_audit.id = tugas_audit.periode_id', 'left')
            ->where('tugas_audit.auditee_id', (int) $auditee_id);

        if ((int) $periode_id > 0) {
            $this->db->where('tugas_audit.periode_id', (int) $periode_id);
        }

        $rows = $this->db
            ->group_by('tugas_audit.id')
            ->order_by('tugas_audit.id', 'DESC')
            ->get()
            ->result();

        foreach ($rows as $row) {
            $this->attach_display_status($row);
        }

        if ($display_status !== '') {
            $rows = array_values(array_filter($rows, function ($row) use ($display_status) {
                return $row->display_status === $display_status;
            }));
        }

        return $rows;
    }

    public function count_need_attention($auditee_id)
    {
        $rows = $this->get_inbox_by_auditee($auditee_id);
        $count = 0;

        foreach ($rows as $row) {
            if (in_array($row->display_status, ['belum_diisi', 'draft', 'revisi'], TRUE)) {
                $count++;
            }
        }

        return $count;
    }

    public function find_tugas_for_auditee($tugas_id, $auditee_id)
    {
        if (!$this->tables_exist(['tugas_audit', 'users', 'standar', 'periode_audit'])) {
            return NULL;
        }

        $tugas = $this->db
            ->select('tugas_audit.*, tugas_audit.status AS tugas_status')
            ->select('standar.nama_standar, standar.deskripsi AS standar_deskripsi, standar.file_instrumen')
            ->select('auditor.nama AS auditor_nama, auditor.email AS auditor_email')
            ->select('periode_audit.nama_periode, periode_audit.tahun_akademik, periode_audit.semester, periode_audit.tanggal_buka, periode_audit.tanggal_tutup')
            ->from('tugas_audit')
            ->join('users AS auditor', 'auditor.id = tugas_audit.auditor_id', 'left')
            ->join('standar', 'standar.id = tugas_audit.standar_id', 'left')
            ->join('periode_audit', 'periode_audit.id = tugas_audit.periode_id', 'left')
            ->where('tugas_audit.id', (int) $tugas_id)
            ->where('tugas_audit.auditee_id', (int) $auditee_id)
            ->get()
            ->row();

        if ($tugas) {
            $summary = $this->get_summary_by_tugas((int) $tugas_id);
            foreach ($summary as $key => $value) {
                $tugas->{$key} = $value;
            }
            $this->attach_display_status($tugas);
        }

        return $tugas;
    }

    public function get_by_tugas($tugas_id)
    {
        if (!$this->tables_exist(['jawaban_audit', 'pertanyaan'])) {
            return [];
        }

        return $this->db
            ->select('jawaban_audit.*, pertanyaan.isi_pertanyaan')
            ->from($this->table)
            ->join('pertanyaan', 'pertanyaan.id = jawaban_audit.pertanyaan_id', 'left')
            ->where('jawaban_audit.tugas_id', (int) $tugas_id)
            ->order_by('pertanyaan.id', 'ASC')
            ->get()
            ->result();
    }

    public function save_answers($tugas_id, $input, $submit = FALSE)
    {
        $jawaban = $this->get_by_tugas($tugas_id);
        if (empty($jawaban)) {
            return ['success' => FALSE, 'message' => 'Tidak ada pertanyaan audit yang dapat disimpan.'];
        }

        $jawaban_input = isset($input['jawaban']) && is_array($input['jawaban']) ? $input['jawaban'] : [];
        $link_input = isset($input['link_bukti']) && is_array($input['link_bukti']) ? $input['link_bukti'] : [];
        $now = date('Y-m-d H:i:s');
        $updates = [];

        foreach ($jawaban as $item) {
            $jawaban_id = (int) $item->id;
            $isi_jawaban = isset($jawaban_input[$jawaban_id]) ? trim((string) $jawaban_input[$jawaban_id]) : '';
            $link_bukti = isset($link_input[$jawaban_id]) ? trim((string) $link_input[$jawaban_id]) : '';

            if ($submit && ($isi_jawaban === '' || $link_bukti === '')) {
                return ['success' => FALSE, 'message' => 'Semua jawaban dan link bukti wajib diisi sebelum submit.'];
            }

            if ($link_bukti !== '' && !$this->is_valid_evidence_url($link_bukti)) {
                return ['success' => FALSE, 'message' => 'Link bukti harus berupa URL http atau https yang valid.'];
            }

            $row = [
                'id' => $jawaban_id,
                'jawaban' => $isi_jawaban,
                'link_bukti' => $link_bukti,
                'is_submitted' => $submit ? 1 : 0,
                'updated_at' => $now,
            ];

            if ($submit) {
                $row['submitted_at'] = $now;
            }

            $updates[] = $row;
        }

        $this->db->trans_start();
        $this->db->update_batch($this->table, $updates, 'id');
        $this->db
            ->where('id', (int) $tugas_id)
            ->update('tugas_audit', ['status' => $submit ? STATUS_DIISI : STATUS_BELUM_DIISI]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => FALSE, 'message' => $submit ? 'Jawaban gagal disubmit.' : 'Draft jawaban gagal disimpan.'];
        }

        return [
            'success' => TRUE,
            'message' => $submit ? 'Jawaban berhasil disubmit. Menunggu penilaian auditor.' : 'Draft jawaban berhasil disimpan.',
        ];
    }

    private function get_summary_by_tugas($tugas_id)
    {
        if (!$this->db->table_exists($this->table)) {
            return [];
        }

        $row = $this->db
            ->select('COUNT(id) AS jumlah_pertanyaan', FALSE)
            ->select("SUM(CASE WHEN (jawaban IS NOT NULL AND TRIM(jawaban) != '') OR (link_bukti IS NOT NULL AND TRIM(link_bukti) != '') THEN 1 ELSE 0 END) AS jumlah_terisi", FALSE)
            ->select('MIN(COALESCE(is_submitted, 0)) AS min_is_submitted', FALSE)
            ->select('MAX(COALESCE(is_submitted, 0)) AS max_is_submitted', FALSE)
            ->select('MAX(submitted_at) AS submitted_at', FALSE)
            ->select('SUM(CASE WHEN submitted_at IS NOT NULL THEN 1 ELSE 0 END) AS jumlah_pernah_submit', FALSE)
            ->select('SUM(CASE WHEN skor IS NOT NULL OR is_nilai_submitted = 1 THEN 1 ELSE 0 END) AS jumlah_dinilai', FALSE)
            ->from($this->table)
            ->where('tugas_id', (int) $tugas_id)
            ->get()
            ->row_array();

        return is_array($row) ? $row : [];
    }

    private function attach_display_status($row)
    {
        $jumlah_pertanyaan = (int) ($row->jumlah_pertanyaan ?? 0);
        $jumlah_terisi = (int) ($row->jumlah_terisi ?? 0);
        $jumlah_pernah_submit = (int) ($row->jumlah_pernah_submit ?? 0);
        $jumlah_dinilai = (int) ($row->jumlah_dinilai ?? 0);
        $min_submitted = (int) ($row->min_is_submitted ?? 0);
        $max_submitted = (int) ($row->max_is_submitted ?? 0);

        if ($jumlah_pernah_submit > 0 && $max_submitted === 0) {
            $status = 'revisi';
            $label = 'Revisi';
        } elseif ($jumlah_pertanyaan > 0 && $max_submitted === 1 && $min_submitted === 1 && ($row->tugas_status === STATUS_DINILAI || $jumlah_dinilai >= $jumlah_pertanyaan)) {
            $status = 'dinilai';
            $label = 'Sudah dinilai';
        } elseif ($jumlah_pertanyaan > 0 && $max_submitted === 1 && $min_submitted === 1) {
            $status = 'submitted';
            $label = 'Submitted';
        } elseif ($jumlah_terisi > 0) {
            $status = 'draft';
            $label = 'Draft';
        } else {
            $status = 'belum_diisi';
            $label = 'Belum diisi';
        }

        $row->display_status = $status;
        $row->display_status_label = $label;
        $row->is_readonly = in_array($status, ['submitted', 'dinilai'], TRUE);
    }

    private function is_valid_evidence_url($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            return FALSE;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], TRUE);
    }

    private function tables_exist($tables)
    {
        foreach ($tables as $table) {
            if (!$this->db->table_exists($table)) {
                return FALSE;
            }
        }

        return TRUE;
    }
}
