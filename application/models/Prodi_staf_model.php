<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prodi_staf_model extends CI_Model
{
    public function has_tables()
    {
        return $this->db->table_exists('profil_prodi')
            && $this->db->table_exists('staf_prodi')
            && $this->db->table_exists('users');
    }

    public function find_prodi($id, $lock = FALSE)
    {
        if ($lock) return $this->db->query('SELECT * FROM profil_prodi WHERE id = ? FOR UPDATE', [(int) $id])->row();
        return $this->db->where('id', (int) $id)->get('profil_prodi')->row();
    }

    public function find_prodi_pair_for_update($source_id, $target_id)
    {
        return $this->db->query('SELECT * FROM profil_prodi WHERE id IN (?, ?) ORDER BY id ASC FOR UPDATE', [(int) $source_id, (int) $target_id])->result();
    }

    public function list_prodi($exclude_id = 0)
    {
        if ($exclude_id) $this->db->where('id !=', (int) $exclude_id);
        return $this->db->order_by('jenjang', 'ASC')->order_by('nama_prodi', 'ASC')->get('profil_prodi')->result();
    }

    public function list_eligible_users()
    {
        return $this->db->select('id, nama, email, role')
            ->where_in('role', ['auditor', 'auditee'])
            ->order_by('nama', 'ASC')->order_by('email', 'ASC')->get('users')->result();
    }

    public function find_eligible_user($id, $lock = FALSE)
    {
        if ($lock) return $this->db->query("SELECT id, role FROM users WHERE id = ? AND role IN ('auditor', 'auditee') FOR UPDATE", [(int) $id])->row();
        return $this->db->where('id', (int) $id)->where_in('role', ['auditor', 'auditee'])->get('users')->row();
    }

    public function list_staf($prodi_id, $status)
    {
        return $this->db->select('staf_prodi.*, users.nama, users.email, users.role')
            ->from('staf_prodi')->join('users', 'users.id = staf_prodi.id_akun')
            ->where('staf_prodi.id_prodi', (int) $prodi_id)->where('staf_prodi.status', $status)
            ->order_by('users.nama', 'ASC')->order_by('users.email', 'ASC')->get()->result();
    }

    public function find_staf($id, $prodi_id, $lock = FALSE)
    {
        if ($lock) return $this->db->query('SELECT * FROM staf_prodi WHERE id = ? AND id_prodi = ? FOR UPDATE', [(int) $id, (int) $prodi_id])->row();
        return $this->db->where('id', (int) $id)->where('id_prodi', (int) $prodi_id)->get('staf_prodi')->row();
    }

    public function find_pair($account_id, $prodi_id, $lock = FALSE)
    {
        if ($lock) return $this->db->query('SELECT * FROM staf_prodi WHERE id_akun = ? AND id_prodi = ? FOR UPDATE', [(int) $account_id, (int) $prodi_id])->row();
        return $this->db->where('id_akun', (int) $account_id)->where('id_prodi', (int) $prodi_id)->get('staf_prodi')->row();
    }

    public function create_staf($account_id, $prodi_id, $jabatan)
    {
        return $this->db->insert('staf_prodi', ['id_akun' => (int) $account_id, 'id_prodi' => (int) $prodi_id, 'jabatan' => $jabatan, 'status' => 'active']);
    }

    public function update_jabatan($id, $prodi_id, $jabatan)
    {
        return $this->db->where('id', (int) $id)->where('id_prodi', (int) $prodi_id)->update('staf_prodi', ['jabatan' => $jabatan]);
    }

    public function move_staf($id, $source_prodi_id, $target_prodi_id)
    {
        return $this->db->where('id', (int) $id)->where('id_prodi', (int) $source_prodi_id)->where('status', 'active')->update('staf_prodi', ['id_prodi' => (int) $target_prodi_id]);
    }

    public function update_status($id, $prodi_id, $from, $to)
    {
        return $this->db->where('id', (int) $id)->where('id_prodi', (int) $prodi_id)->where('status', $from)->update('staf_prodi', ['status' => $to]);
    }
}
