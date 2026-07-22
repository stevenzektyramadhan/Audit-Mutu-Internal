<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_service
{
    protected $ci;
    protected $user_model;
    protected $standar_model;
    protected $pertanyaan_model;
    protected $tugas_audit_model;
    protected $jawaban_model;
    protected $periode_model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('User_model');
        $this->ci->load->model('Standar_model');
        $this->ci->load->model('Pertanyaan_model');
        $this->ci->load->model('Tugas_audit_model');
        $this->ci->load->model('Jawaban_model');
        $this->ci->load->model('Periode_model');

        $this->user_model = $this->ci->User_model;
        $this->standar_model = $this->ci->Standar_model;
        $this->pertanyaan_model = $this->ci->Pertanyaan_model;
        $this->tugas_audit_model = $this->ci->Tugas_audit_model;
        $this->jawaban_model = $this->ci->Jawaban_model;
        $this->periode_model = $this->ci->Periode_model;
    }

    public function get_super_admin_data()
    {
        $active_periode = $this->periode_model->get_active();
        $task_status_counts = [
            STATUS_BELUM_DIISI => 0,
            STATUS_DIISI => 0,
            STATUS_DINILAI => 0,
        ];

        if ($active_periode) {
            $task_status_counts = $this->tugas_audit_model->count_by_status_for_period((int) $active_periode->id);
        }

        return [
            'stats' => [
                'total_user' => $this->user_model->count_all(),
                'total_auditor' => $this->user_model->count_by_role('auditor'),
                'total_auditee' => $this->user_model->count_by_role('auditee'),
                'total_standar' => $this->standar_model->count_all(),
                'total_pertanyaan' => $this->pertanyaan_model->count_all(),
                'total_tugas' => $this->tugas_audit_model->count_all(),
                'belum_diisi' => $task_status_counts[STATUS_BELUM_DIISI],
                'diisi' => $task_status_counts[STATUS_DIISI],
                'dinilai' => $task_status_counts[STATUS_DINILAI],
            ],
            'active_periode' => $active_periode,
            'task_status_counts' => $task_status_counts,
            'recent_tugas' => $this->tugas_audit_model->get_recent([], 5),
            'standar_summary' => $this->standar_model->get_summary(5),
        ];
    }

    public function get_auditor_data($user_id)
    {
        $where = ['auditor_id' => (int) $user_id];

        return [
            'stats' => [
                'total_tugas' => $this->tugas_audit_model->count_all($where),
                'belum_dinilai' => $this->tugas_audit_model->count_all($where, STATUS_DINILAI),
                'siap_dinilai' => $this->tugas_audit_model->count_all($where + ['status' => STATUS_DIISI]),
                'dinilai' => $this->tugas_audit_model->count_all($where + ['status' => STATUS_DINILAI]),
            ],
            'pending_tugas' => $this->tugas_audit_model->get_by_auditor((int) $user_id, STATUS_DIISI, 5),
            'graded_tugas' => $this->tugas_audit_model->get_by_auditor((int) $user_id, STATUS_DINILAI, 5),
        ];
    }

    public function get_auditee_data($user_id)
    {
        $user_id = (int) $user_id;
        $tugas = $this->jawaban_model->get_inbox_by_auditee($user_id);
        $stats = [
            'total_tugas' => count($tugas),
            'belum_diisi' => 0,
            'draft' => 0,
            'diisi' => 0,
            'revisi' => 0,
            'dinilai' => 0,
            'perlu_tindakan' => 0,
        ];

        foreach ($tugas as $item) {
            $status = isset($item->display_status) ? $item->display_status : '';

            if (isset($stats[$status])) {
                $stats[$status]++;
            }

            if (in_array($status, ['belum_diisi', 'draft', 'revisi'], TRUE)) {
                $stats['perlu_tindakan']++;
            }

            if ($status === 'submitted') {
                $stats['diisi']++;
            }
        }

        return [
            'stats' => $stats,
            'tugas_saya' => array_slice($tugas, 0, 5),
        ];
    }
}
