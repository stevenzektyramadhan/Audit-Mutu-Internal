<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/Auditor.php';

/**
 * Proxy controller untuk fitur penilaian auditor.
 *
 * Project ini masih memiliki controller top-level `Auditor.php`, sehingga
 * route publik diarahkan ke bridge di controller tersebut agar URL lama tetap
 * aman. Class ini menjaga struktur controller sesuai TASK-11 sampai TASK-13.
 */
class Penilaian extends Auditor
{
    public function index()
    {
        $this->penilaian();
    }

    public function form($tugas_id)
    {
        $this->form_penilaian((int) $tugas_id);
    }

    public function nilai($tugas_id)
    {
        $this->form_penilaian((int) $tugas_id);
    }

    public function save_item($jawaban_id)
    {
        $this->save_penilaian_item((int) $jawaban_id);
    }

    public function save($tugas_id)
    {
        $this->save_penilaian_draft((int) $tugas_id);
    }

    public function submit($tugas_id)
    {
        $this->submit_penilaian((int) $tugas_id);
    }

    public function revisi($tugas_id)
    {
        $this->revisi_penilaian((int) $tugas_id);
    }

    public function download_bukti($jawaban_id)
    {
        $this->download_bukti_penilaian((int) $jawaban_id);
    }
}
