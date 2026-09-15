<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    protected $table = 'users';

    public function __construct()
    {
        parent::__construct();
    }

    public function find_by_email($email, $for_update = FALSE)
    {
        $query = $this->db
            ->where('email', $email)
            ->limit(1)
            ->get_compiled_select($this->table);

        return $this->db->query($query . ($for_update ? ' FOR UPDATE' : ''))->row();
    }

    public function find_by_identity_number($identity_number, $for_update = FALSE)
    {
        $query = $this->db
            ->where('identity_number', $identity_number)
            ->limit(1)
            ->get_compiled_select($this->table);

        return $this->db->query($query . ($for_update ? ' FOR UPDATE' : ''))->row();
    }

    public function find($id)
    {
        return $this->db->where('id', (int) $id)->get($this->table)->row();
    }

    public function email_exists_except($email, $id)
    {
        return $this->db
            ->where('email', $email)
            ->where('id !=', (int) $id)
            ->count_all_results($this->table) > 0;
    }

    public function create($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        return $this->db->where('id', (int) $id)->update($this->table, $data);
    }

    public function update_own_profile($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, $data);
    }

    public function update_password($id, $password_hash)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, ['password' => $password_hash, 'must_change_password' => 0]);
    }

    public function find_profile_photo_for_update($id)
    {
        $query = $this->db
            ->select('profile_photo_path')
            ->where('id', (int) $id)
            ->limit(1)
            ->get_compiled_select($this->table);

        return $this->db->query($query . ' FOR UPDATE')->row();
    }

    public function delete($id)
    {
        return $this->db->where('id', (int) $id)->delete($this->table);
    }

    public function has_audit_assignments($id)
    {
        if (!$this->db->table_exists('tugas_audit')) {
            return FALSE;
        }

        return $this->db
            ->group_start()
                ->where('auditor_id', (int) $id)
                ->or_where('auditee_id', (int) $id)
            ->group_end()
            ->count_all_results('tugas_audit') > 0;
    }

    public function user_dependency_category($id)
    {
        $checks = [
            ['table' => 'tugas_audit', 'fields' => ['auditor_id', 'auditee_id'], 'category' => 'tugas audit AMI'],
            ['table' => 'spmi_audit_assignments', 'fields' => ['auditor_id', 'auditee_id', 'created_by'], 'category' => 'penugasan audit SPMI'],
            ['table' => 'spmi_audit_cycles', 'fields' => ['created_by'], 'category' => 'siklus audit SPMI'],
            ['table' => 'spmi_versions', 'fields' => ['created_by'], 'category' => 'versi SPMI'],
            ['table' => 'spmi_reports', 'fields' => ['generated_by'], 'category' => 'laporan SPMI'],
            ['table' => 'spmi_rtm_meetings', 'fields' => ['created_by', 'resolved_by'], 'category' => 'rapat RTM SPMI'],
            ['table' => 'spmi_rtm_participants', 'fields' => ['user_id'], 'category' => 'peserta RTM SPMI'],
            ['table' => 'spmi_rtm_follow_ups', 'fields' => ['responsible_user_id', 'started_by', 'completed_by', 'created_by'], 'category' => 'tindak lanjut RTM SPMI'],
            ['table' => 'user_unit_assignments', 'fields' => ['user_id'], 'category' => 'penempatan unit organisasi'],
        ];

        foreach ($checks as $check) {
            if (!$this->db->table_exists($check['table'])) {
                continue;
            }

            $fields = [];
            foreach ($check['fields'] as $field) {
                if ($this->db->field_exists($field, $check['table'])) {
                    $fields[] = $field;
                }
            }

            if (!$fields) {
                continue;
            }

            $this->db->from($check['table'])->group_start();
            foreach ($fields as $index => $field) {
                if ($index === 0) {
                    $this->db->where($field, (int) $id);
                } else {
                    $this->db->or_where($field, (int) $id);
                }
            }
            $count = $this->db->group_end()->count_all_results();

            if ($count > 0) {
                return $check['category'];
            }
        }

        return '';
    }

    public function get_all($filters = [])
    {
        $this->db->from($this->table);

        if (!empty($filters['q'])) {
            $this->db
                ->group_start()
                    ->like('nama', $filters['q'])
                    ->or_like('email', $filters['q'])
                ->group_end();
        }

        if (!empty($filters['role'])) {
            $this->db->where('role', $filters['role']);
        }

        return $this->db->order_by('id', 'ASC')->get()->result();
    }

    public function get_by_role($role)
    {
        return $this->db->where('role', $role)->get($this->table)->result();
    }

    public function count_all()
    {
        if (!$this->db->table_exists($this->table)) {
            return 0;
        }

        return (int) $this->db->count_all_results($this->table);
    }

    public function count_by_role($role)
    {
        if (!$this->db->table_exists($this->table)) {
            return 0;
        }

        return (int) $this->db
            ->where('role', $role)
            ->count_all_results($this->table);
    }
}
