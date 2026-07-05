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

    public function get_inbox_by_auditor($auditor_id)
    {
        if (!$this->tables_exist(['tugas_audit', 'jawaban_audit', 'users', 'standar', 'periode_audit'])) {
            return [];
        }

        $rows = $this->db
            ->select('tugas_audit.id, tugas_audit.status AS tugas_status, tugas_audit.created_at, tugas_audit.periode_id')
            ->select('standar.nama_standar')
            ->select('auditee.nama AS auditee_nama, auditee.email AS auditee_email, auditee.nama_unit AS auditee_unit, auditee.jenis_unit AS auditee_jenis_unit')
            ->select('periode_audit.nama_periode, periode_audit.tanggal_buka, periode_audit.tanggal_tutup')
            ->select('COUNT(jawaban_audit.id) AS jumlah_pertanyaan', FALSE)
            ->select('SUM(CASE WHEN COALESCE(jawaban_audit.is_submitted, 0) = 1 THEN 1 ELSE 0 END) AS jumlah_submitted', FALSE)
            ->select('SUM(CASE WHEN jawaban_audit.skor IS NOT NULL THEN 1 ELSE 0 END) AS jumlah_dinilai', FALSE)
            ->select('SUM(CASE WHEN COALESCE(jawaban_audit.is_nilai_submitted, 0) = 1 THEN 1 ELSE 0 END) AS jumlah_nilai_submitted', FALSE)
            ->select('MIN(COALESCE(jawaban_audit.is_nilai_submitted, 0)) AS min_nilai_submitted', FALSE)
            ->select('MAX(COALESCE(jawaban_audit.is_nilai_submitted, 0)) AS max_nilai_submitted', FALSE)
            ->select('MAX(jawaban_audit.submitted_at) AS submitted_at', FALSE)
            ->select('AVG(jawaban_audit.skor) AS rata_rata', FALSE)
            ->from('tugas_audit')
            ->join('jawaban_audit', 'jawaban_audit.tugas_id = tugas_audit.id', 'left')
            ->join('standar', 'standar.id = tugas_audit.standar_id', 'left')
            ->join('users AS auditee', 'auditee.id = tugas_audit.auditee_id', 'left')
            ->join('periode_audit', 'periode_audit.id = tugas_audit.periode_id', 'left')
            ->where('tugas_audit.auditor_id', (int) $auditor_id)
            ->group_by('tugas_audit.id')
            ->having('jumlah_pertanyaan >', 0)
            ->having('jumlah_submitted = jumlah_pertanyaan', NULL, FALSE)
            ->order_by('submitted_at', 'DESC')
            ->get()
            ->result();

        foreach ($rows as $row) {
            $this->attach_auditor_penilaian_status($row);
        }

        return $rows;
    }

    public function count_pending_penilaian($auditor_id)
    {
        $rows = $this->get_inbox_by_auditor($auditor_id);
        $count = 0;

        foreach ($rows as $row) {
            if ($row->penilaian_status !== 'selesai') {
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

    public function find_tugas_for_auditor($tugas_id, $auditor_id)
    {
        if (!$this->tables_exist(['tugas_audit', 'jawaban_audit', 'users', 'standar', 'periode_audit'])) {
            return NULL;
        }

        $tugas = $this->db
            ->select('tugas_audit.*, tugas_audit.status AS tugas_status')
            ->select('standar.nama_standar, standar.deskripsi AS standar_deskripsi')
            ->select('auditee.nama AS auditee_nama, auditee.email AS auditee_email, auditee.nama_unit AS auditee_unit, auditee.jenis_unit AS auditee_jenis_unit')
            ->select('periode_audit.nama_periode, periode_audit.tahun_akademik, periode_audit.semester, periode_audit.tanggal_buka, periode_audit.tanggal_tutup')
            ->from('tugas_audit')
            ->join('users AS auditee', 'auditee.id = tugas_audit.auditee_id', 'left')
            ->join('standar', 'standar.id = tugas_audit.standar_id', 'left')
            ->join('periode_audit', 'periode_audit.id = tugas_audit.periode_id', 'left')
            ->where('tugas_audit.id', (int) $tugas_id)
            ->where('tugas_audit.auditor_id', (int) $auditor_id)
            ->get()
            ->row();

        if ($tugas) {
            $summary = $this->get_summary_by_tugas((int) $tugas_id);
            foreach ($summary as $key => $value) {
                $tugas->{$key} = $value;
            }
            $this->attach_auditor_penilaian_status($tugas);
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

    public function find_jawaban_for_auditor($jawaban_id, $auditor_id)
    {
        if (!$this->tables_exist(['jawaban_audit', 'tugas_audit', 'pertanyaan'])) {
            return NULL;
        }

        return $this->db
            ->select('jawaban_audit.*, pertanyaan.isi_pertanyaan')
            ->select('tugas_audit.auditor_id, tugas_audit.auditee_id, tugas_audit.status AS tugas_status')
            ->from($this->table)
            ->join('tugas_audit', 'tugas_audit.id = jawaban_audit.tugas_id', 'left')
            ->join('pertanyaan', 'pertanyaan.id = jawaban_audit.pertanyaan_id', 'left')
            ->where('jawaban_audit.id', (int) $jawaban_id)
            ->where('tugas_audit.auditor_id', (int) $auditor_id)
            ->get()
            ->row();
    }

    public function save_penilaian_item($jawaban_id, $auditor_id, $data)
    {
        $jawaban = $this->find_jawaban_for_auditor((int) $jawaban_id, (int) $auditor_id);
        if (!$jawaban) {
            return ['success' => FALSE, 'message' => 'Jawaban audit tidak ditemukan atau bukan milik Anda.'];
        }

        $tugas = $this->find_tugas_for_auditor((int) $jawaban->tugas_id, (int) $auditor_id);
        if (!$tugas || empty($tugas->is_auditee_submitted)) {
            return ['success' => FALSE, 'message' => 'Auditee belum submit jawaban untuk tugas audit ini.'];
        }

        if (!empty($tugas->is_nilai_readonly)) {
            return ['success' => FALSE, 'message' => 'Penilaian sudah disubmit dan tidak dapat diubah.'];
        }

        $row = $this->normalize_penilaian_row($data, FALSE);
        if ($row === FALSE) {
            return ['success' => FALSE, 'message' => 'Skor harus bernilai 1 sampai 4.'];
        }

        $row['is_nilai_submitted'] = 0;
        $row['nilai_submitted_at'] = NULL;
        $row['updated_at'] = date('Y-m-d H:i:s');

        $success = $this->db
            ->where('id', (int) $jawaban_id)
            ->update($this->table, $row);

        if (!$success) {
            return ['success' => FALSE, 'message' => 'Penilaian pertanyaan gagal disimpan.'];
        }

        return [
            'success' => TRUE,
            'message' => 'Penilaian pertanyaan berhasil disimpan.',
            'jawaban' => $this->find_jawaban_for_auditor((int) $jawaban_id, (int) $auditor_id),
        ];
    }

    public function save_penilaian_batch($tugas_id, $auditor_id, $input)
    {
        $tugas = $this->find_tugas_for_auditor((int) $tugas_id, (int) $auditor_id);
        if (!$tugas) {
            return ['success' => FALSE, 'message' => 'Tugas audit tidak ditemukan atau bukan milik Anda.'];
        }

        if (empty($tugas->is_auditee_submitted)) {
            return ['success' => FALSE, 'message' => 'Auditee belum submit jawaban untuk tugas audit ini.'];
        }

        if (!empty($tugas->is_nilai_readonly)) {
            return ['success' => FALSE, 'message' => 'Penilaian sudah disubmit dan tidak dapat diubah.'];
        }

        $jawaban = $this->get_by_tugas((int) $tugas_id);
        if (empty($jawaban)) {
            return ['success' => FALSE, 'message' => 'Tidak ada jawaban audit yang dapat dinilai.'];
        }

        $updates = [];
        foreach ($jawaban as $item) {
            $jawaban_id = (int) $item->id;
            $row = $this->normalize_penilaian_row([
                'skor' => $this->array_value($input, 'skor', $jawaban_id),
                'temuan' => $this->array_value($input, 'temuan', $jawaban_id),
                'jenis_temuan' => $this->array_value($input, 'jenis_temuan', $jawaban_id),
                'saran_perbaikan' => $this->array_value($input, 'saran_perbaikan', $jawaban_id),
                'rencana_perbaikan' => $this->array_value($input, 'rencana_perbaikan', $jawaban_id),
                'tgl_bukti' => $this->array_value($input, 'tgl_bukti', $jawaban_id),
            ], FALSE);

            if ($row === FALSE) {
                return ['success' => FALSE, 'message' => 'Skor harus bernilai 1 sampai 4.'];
            }

            $row['id'] = $jawaban_id;
            $row['is_nilai_submitted'] = 0;
            $row['nilai_submitted_at'] = NULL;
            $row['updated_at'] = date('Y-m-d H:i:s');
            $updates[] = $row;
        }

        $this->db->trans_start();
        $this->db->update_batch($this->table, $updates, 'id');
        $this->db
            ->where('id', (int) $tugas_id)
            ->update('tugas_audit', ['status' => STATUS_DIISI]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => FALSE, 'message' => 'Draft penilaian gagal disimpan.'];
        }

        return ['success' => TRUE, 'message' => 'Draft penilaian berhasil disimpan.'];
    }

    public function submit_penilaian($tugas_id, $auditor_id)
    {
        $tugas = $this->find_tugas_for_auditor((int) $tugas_id, (int) $auditor_id);
        if (!$tugas) {
            return ['success' => FALSE, 'message' => 'Tugas audit tidak ditemukan atau bukan milik Anda.'];
        }

        if (empty($tugas->is_auditee_submitted)) {
            return ['success' => FALSE, 'message' => 'Auditee belum submit jawaban untuk tugas audit ini.'];
        }

        if (!empty($tugas->is_nilai_readonly)) {
            return ['success' => FALSE, 'message' => 'Penilaian sudah disubmit.'];
        }

        $jawaban = $this->get_by_tugas((int) $tugas_id);
        if (empty($jawaban)) {
            return ['success' => FALSE, 'message' => 'Tidak ada jawaban audit yang dapat disubmit.'];
        }

        $now = date('Y-m-d H:i:s');
        $updates = [];
        foreach ($jawaban as $index => $item) {
            $skor = $item->skor !== NULL ? (int) $item->skor : 0;
            if (!in_array($skor, [1, 2, 3, 4], TRUE)) {
                return [
                    'success' => FALSE,
                    'message' => 'Semua pertanyaan wajib diberi skor sebelum submit. Cek pertanyaan nomor ' . ((int) $index + 1) . '.',
                ];
            }

            $updates[] = [
                'id' => (int) $item->id,
                'is_nilai_submitted' => 1,
                'nilai_submitted_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->db->trans_start();
        $this->db->update_batch($this->table, $updates, 'id');
        $this->db
            ->where('id', (int) $tugas_id)
            ->update('tugas_audit', ['status' => STATUS_DINILAI]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => FALSE, 'message' => 'Penilaian gagal disubmit.'];
        }

        return ['success' => TRUE, 'message' => 'Penilaian berhasil disubmit dan dikunci.'];
    }

    public function revisi_tugas($tugas_id, $auditor_id)
    {
        $tugas = $this->find_tugas_for_auditor((int) $tugas_id, (int) $auditor_id);
        if (!$tugas) {
            return ['success' => FALSE, 'message' => 'Tugas audit tidak ditemukan atau bukan milik Anda.'];
        }

        if (empty($tugas->is_auditee_submitted)) {
            return ['success' => FALSE, 'message' => 'Tugas ini belum berada dalam status submitted.'];
        }

        $this->db->trans_start();
        $this->db
            ->where('tugas_id', (int) $tugas_id)
            ->update($this->table, [
                'is_submitted' => 0,
                'is_nilai_submitted' => 0,
                'nilai_submitted_at' => NULL,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        $this->db
            ->where('id', (int) $tugas_id)
            ->update('tugas_audit', ['status' => STATUS_BELUM_DIISI]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => FALSE, 'message' => 'Tugas gagal dikembalikan untuk revisi.'];
        }

        return ['success' => TRUE, 'message' => 'Tugas berhasil dikembalikan ke auditee untuk revisi.'];
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
            ->select('SUM(CASE WHEN COALESCE(is_submitted, 0) = 1 THEN 1 ELSE 0 END) AS jumlah_submitted', FALSE)
            ->select('MAX(submitted_at) AS submitted_at', FALSE)
            ->select('SUM(CASE WHEN submitted_at IS NOT NULL THEN 1 ELSE 0 END) AS jumlah_pernah_submit', FALSE)
            ->select('SUM(CASE WHEN skor IS NOT NULL OR is_nilai_submitted = 1 THEN 1 ELSE 0 END) AS jumlah_dinilai', FALSE)
            ->select('SUM(CASE WHEN COALESCE(is_nilai_submitted, 0) = 1 THEN 1 ELSE 0 END) AS jumlah_nilai_submitted', FALSE)
            ->select('MIN(COALESCE(is_nilai_submitted, 0)) AS min_nilai_submitted', FALSE)
            ->select('MAX(COALESCE(is_nilai_submitted, 0)) AS max_nilai_submitted', FALSE)
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

    private function attach_auditor_penilaian_status($row)
    {
        $jumlah_pertanyaan = (int) ($row->jumlah_pertanyaan ?? 0);
        $jumlah_submitted = (int) ($row->jumlah_submitted ?? 0);
        $jumlah_dinilai = (int) ($row->jumlah_dinilai ?? 0);
        $min_nilai_submitted = (int) ($row->min_nilai_submitted ?? 0);
        $max_nilai_submitted = (int) ($row->max_nilai_submitted ?? 0);

        $row->is_auditee_submitted = $jumlah_pertanyaan > 0 && $jumlah_submitted === $jumlah_pertanyaan;

        if ($jumlah_pertanyaan > 0 && $min_nilai_submitted === 1 && $max_nilai_submitted === 1) {
            $status = 'selesai';
            $label = 'Sudah dinilai';
            $class = 'status-dinilai';
            $icon = 'fa-check-circle';
        } elseif ($jumlah_dinilai > 0) {
            $status = 'draft_nilai';
            $label = 'Draft nilai';
            $class = 'status-draft';
            $icon = 'fa-save';
        } else {
            $status = 'perlu_dinilai';
            $label = 'Perlu dinilai';
            $class = 'status-diisi';
            $icon = 'fa-star';
        }

        $row->penilaian_status = $status;
        $row->penilaian_status_label = $label;
        $row->penilaian_status_class = $class;
        $row->penilaian_status_icon = $icon;
        $row->is_nilai_readonly = $status === 'selesai';
    }

    private function is_valid_evidence_url($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            return FALSE;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        return in_array($scheme, ['http', 'https'], TRUE);
    }

    private function normalize_penilaian_row($data, $require_skor)
    {
        $skor = isset($data['skor']) ? trim((string) $data['skor']) : '';
        if ($skor === '') {
            if ($require_skor) {
                return FALSE;
            }
            $skor = NULL;
        } else {
            $skor = (int) $skor;
            if (!in_array($skor, [1, 2, 3, 4], TRUE)) {
                return FALSE;
            }
        }

        $jenis_temuan = isset($data['jenis_temuan']) ? strtolower(trim((string) $data['jenis_temuan'])) : '';
        if (!in_array($jenis_temuan, ['ob', 'kts'], TRUE)) {
            $jenis_temuan = NULL;
        }

        $tgl_bukti = isset($data['tgl_bukti']) ? trim((string) $data['tgl_bukti']) : '';
        if (!$this->is_valid_date($tgl_bukti)) {
            $tgl_bukti = NULL;
        }

        $row = [
            'skor' => $skor,
            'temuan' => isset($data['temuan']) ? trim((string) $data['temuan']) : '',
            'jenis_temuan' => $jenis_temuan,
            'saran_perbaikan' => isset($data['saran_perbaikan']) ? trim((string) $data['saran_perbaikan']) : '',
            'rencana_perbaikan' => isset($data['rencana_perbaikan']) ? trim((string) $data['rencana_perbaikan']) : '',
            'tgl_bukti' => $tgl_bukti,
        ];

        if (isset($data['dokumen_bukti'])) {
            $row['dokumen_bukti'] = $data['dokumen_bukti'] !== '' ? basename((string) $data['dokumen_bukti']) : NULL;
        }

        return $row;
    }

    private function array_value($input, $key, $id)
    {
        return isset($input[$key]) && is_array($input[$key]) && array_key_exists($id, $input[$key])
            ? $input[$key][$id]
            : NULL;
    }

    private function is_valid_date($date)
    {
        if ($date === '') {
            return FALSE;
        }

        $dt = DateTime::createFromFormat('Y-m-d', $date);
        return $dt && $dt->format('Y-m-d') === $date;
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
