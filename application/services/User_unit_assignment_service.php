<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_unit_assignment_service
{
    protected $ci;
    protected $assignment_model;
    protected $organization_unit_model;
    protected $user_model;
    protected $audit_logger;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('User_unit_assignment_model');
        $this->ci->load->model('Organization_unit_model');
        $this->ci->load->model('User_model');
        $this->ci->load->library('audit_logger');

        $this->assignment_model = $this->ci->User_unit_assignment_model;
        $this->organization_unit_model = $this->ci->Organization_unit_model;
        $this->user_model = $this->ci->User_model;
        $this->audit_logger = $this->ci->audit_logger;
    }

    public function schema_ready()
    {
        return $this->assignment_model->schema_ready();
    }

    public function find_user($user_id)
    {
        return $this->user_model->find((int) $user_id);
    }

    public function get_for_user($user_id)
    {
        return $this->assignment_model->get_for_user((int) $user_id);
    }

    public function find($assignment_id)
    {
        return $this->assignment_model->find((int) $assignment_id);
    }

    public function get_active_units()
    {
        return array_values(array_filter(
            $this->organization_unit_model->get_all(),
            function ($unit) {
                return (int) $unit->active === 1;
            }
        ));
    }

    public function create($user_id, array $input)
    {
        $user_id = (int) $user_id;
        $data = $this->normalize($user_id, $input);
        $validation = $this->validate($data);
        if ($validation !== TRUE) {
            return $validation;
        }

        $this->ci->db->trans_begin();
        $locked_user = $this->assignment_model->lock_user($user_id);
        if (!$locked_user) {
            $this->ci->db->trans_rollback();
            return ['success' => FALSE, 'message' => 'Pengguna tidak ditemukan.'];
        }

        if ($this->assignment_model->has_overlap(
            $user_id,
            $data['organization_unit_id'],
            $data['position_code'],
            $data['valid_from'],
            $data['valid_until']
        )) {
            $this->ci->db->trans_rollback();
            return [
                'success' => FALSE,
                'message' => 'Periode assignment untuk unit dan jabatan yang sama bertumpuk.',
            ];
        }

        if ($data['is_primary'] === 1
            && $this->assignment_model->has_primary_overlap(
                $user_id,
                $data['valid_from'],
                $data['valid_until']
            )) {
            $this->ci->db->trans_rollback();
            return [
                'success' => FALSE,
                'message' => 'Pengguna sudah memiliki assignment primary pada periode tersebut.',
            ];
        }

        $assignment_id = $this->assignment_model->create($data);
        if (!$assignment_id || !$this->ci->db->trans_status()) {
            $this->ci->db->trans_rollback();
            return ['success' => FALSE, 'message' => 'Assignment unit dan jabatan gagal disimpan.'];
        }
        $this->ci->db->trans_commit();

        $this->audit_logger->record(
            'user_unit_assignment_created',
            'user_unit_assignment',
            $assignment_id,
            'create',
            NULL,
            $data,
            ['changed_fields' => array_keys($data), 'scope' => 'organization_membership']
        );

        return [
            'success' => TRUE,
            'message' => 'Assignment unit dan jabatan berhasil ditambahkan.',
            'id' => $assignment_id,
        ];
    }

    public function end($assignment_id, $valid_until)
    {
        $assignment = $this->assignment_model->find((int) $assignment_id);
        if (!$assignment) {
            return ['success' => FALSE, 'message' => 'Assignment tidak ditemukan.'];
        }

        $valid_until = trim((string) $valid_until);
        if (!$this->valid_date($valid_until)) {
            return ['success' => FALSE, 'message' => 'Tanggal berakhir tidak valid.'];
        }

        $minimum = max(date('Y-m-d'), (string) $assignment->valid_from);
        if ($valid_until < $minimum) {
            return [
                'success' => FALSE,
                'message' => 'Tanggal berakhir tidak boleh sebelum hari ini atau tanggal mulai.',
            ];
        }
        if ($assignment->valid_until !== NULL
            && (string) $assignment->valid_until < date('Y-m-d')) {
            return ['success' => FALSE, 'message' => 'Assignment yang sudah berakhir tidak dapat diubah.'];
        }
        if ($assignment->valid_until !== NULL
            && $valid_until > (string) $assignment->valid_until) {
            return [
                'success' => FALSE,
                'message' => 'Aksi akhiri assignment tidak dapat memperpanjang masa berlaku.',
            ];
        }
        if ($assignment->valid_until !== NULL
            && $valid_until === (string) $assignment->valid_until) {
            return ['success' => TRUE, 'message' => 'Tanggal berakhir assignment tidak berubah.'];
        }

        $before = $this->snapshot($assignment);
        if (!$this->assignment_model->set_valid_until((int) $assignment->id, $valid_until)) {
            return ['success' => FALSE, 'message' => 'Assignment gagal diakhiri.'];
        }
        $after = $before;
        $after['valid_until'] = $valid_until;

        $this->audit_logger->record(
            'user_unit_assignment_ended',
            'user_unit_assignment',
            (int) $assignment->id,
            'end',
            $before,
            $after,
            [
                'changed_fields' => ['valid_until'],
                'status_from' => 'active_or_scheduled',
                'status_to' => 'ended',
                'scope' => 'organization_membership',
            ]
        );

        return ['success' => TRUE, 'message' => 'Masa berlaku assignment berhasil diakhiri.'];
    }

    protected function normalize($user_id, array $input)
    {
        $valid_until = trim(isset($input['valid_until']) ? (string) $input['valid_until'] : '');

        return [
            'user_id' => (int) $user_id,
            'organization_unit_id' => (int) (
                isset($input['organization_unit_id'])
                    ? $input['organization_unit_id']
                    : 0
            ),
            'position_code' => strtoupper(trim(
                isset($input['position_code']) ? (string) $input['position_code'] : ''
            )),
            'valid_from' => trim(
                isset($input['valid_from']) ? (string) $input['valid_from'] : ''
            ),
            'valid_until' => $valid_until !== '' ? $valid_until : NULL,
            'is_primary' => !empty($input['is_primary']) ? 1 : 0,
        ];
    }

    protected function validate(array $data)
    {
        $user = $this->user_model->find($data['user_id']);
        if (!$user) {
            return ['success' => FALSE, 'message' => 'Pengguna tidak ditemukan.'];
        }

        $unit = $this->organization_unit_model->find($data['organization_unit_id']);
        if (!$unit || (int) $unit->active !== 1) {
            return ['success' => FALSE, 'message' => 'Pilih unit organisasi yang aktif.'];
        }

        if (!preg_match('/^[A-Z0-9][A-Z0-9._-]{0,63}$/', $data['position_code'])) {
            return [
                'success' => FALSE,
                'message' => 'Kode jabatan hanya boleh memakai huruf, angka, titik, garis bawah, atau tanda hubung.',
            ];
        }

        if (!$this->valid_date($data['valid_from'])
            || ($data['valid_until'] !== NULL && !$this->valid_date($data['valid_until']))) {
            return ['success' => FALSE, 'message' => 'Periode assignment tidak valid.'];
        }
        if ($data['valid_until'] !== NULL && $data['valid_until'] < $data['valid_from']) {
            return ['success' => FALSE, 'message' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.'];
        }

        return TRUE;
    }

    protected function valid_date($value)
    {
        $date = DateTime::createFromFormat('!Y-m-d', (string) $value);
        return $date && $date->format('Y-m-d') === (string) $value;
    }

    protected function snapshot($assignment)
    {
        return [
            'user_id' => (int) $assignment->user_id,
            'organization_unit_id' => (int) $assignment->organization_unit_id,
            'position_code' => (string) $assignment->position_code,
            'valid_from' => (string) $assignment->valid_from,
            'valid_until' => $assignment->valid_until === NULL
                ? NULL
                : (string) $assignment->valid_until,
            'is_primary' => (int) $assignment->is_primary,
        ];
    }
}
