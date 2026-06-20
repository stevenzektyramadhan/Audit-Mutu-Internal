<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_service
{
    protected $ci;
    protected $user_model;
    protected $standar_model;
    protected $pertanyaan_model;
    protected $tugas_audit_model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('User_model');
        $this->ci->load->model('Standar_model');
        $this->ci->load->model('Pertanyaan_model');
        $this->ci->load->model('Tugas_audit_model');

        $this->user_model = $this->ci->User_model;
        $this->standar_model = $this->ci->Standar_model;
        $this->pertanyaan_model = $this->ci->Pertanyaan_model;
        $this->tugas_audit_model = $this->ci->Tugas_audit_model;
    }

    public function get_super_admin_data()
    {
        return [
            'stats' => [
                'total_user' => $this->user_model->count_all(),
                'total_auditor' => $this->user_model->count_by_role('auditor'),
                'total_auditee' => $this->user_model->count_by_role('auditee'),
                'total_standar' => $this->standar_model->count_all(),
                'total_pertanyaan' => $this->pertanyaan_model->count_all(),
                'total_tugas' => $this->tugas_audit_model->count_all(),
                'belum_diisi' => $this->tugas_audit_model->count_all(['status' => STATUS_BELUM_DIISI]),
                'diisi' => $this->tugas_audit_model->count_all(['status' => STATUS_DIISI]),
                'dinilai' => $this->tugas_audit_model->count_all(['status' => STATUS_DINILAI]),
            ],
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

        return [
            'stats' => [
                'total_tugas' => $this->tugas_audit_model->count_all(['auditee_id' => $user_id]),
                'belum_diisi' => $this->tugas_audit_model->count_all(['auditee_id' => $user_id, 'status' => STATUS_BELUM_DIISI]),
                'diisi' => $this->tugas_audit_model->count_all(['auditee_id' => $user_id, 'status' => STATUS_DIISI]),
                'dinilai' => $this->tugas_audit_model->count_all(['auditee_id' => $user_id, 'status' => STATUS_DINILAI]),
            ],
            'tugas_saya' => $this->tugas_audit_model->get_by_auditee($user_id, NULL, 5),
        ];
    }
}
