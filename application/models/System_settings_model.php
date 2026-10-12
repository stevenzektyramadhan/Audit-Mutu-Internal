<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class System_settings_model extends CI_Model
{
    public function profile()
    {
        if (!$this->db->table_exists('profil_lembaga')) return NULL;
        return $this->db->order_by('id', 'ASC')->limit(1)->get('profil_lembaga')->row();
    }

    public function profile_for_update()
    {
        if (!$this->db->table_exists('profil_lembaga')) return NULL;
        return $this->db->query('SELECT * FROM profil_lembaga ORDER BY id ASC LIMIT 1 FOR UPDATE')->row();
    }

    public function upsert_profile($profile_id, array $data)
    {
        if ((int) $profile_id > 0) {
            return $this->db->where('id', (int) $profile_id)->update('profil_lembaga', $data);
        }

        return $this->db->insert('profil_lembaga', $data);
    }

    public function settings()
    {
        if (!$this->db->table_exists('system_settings')) return NULL;
        return $this->db->where('id', 1)->get('system_settings')->row();
    }

    public function settings_for_update()
    {
        if (!$this->db->table_exists('system_settings')) return NULL;
        return $this->db->query('SELECT * FROM system_settings WHERE id = 1 FOR UPDATE')->row();
    }

    public function upsert_settings(array $data)
    {
        $data['id'] = 1;
        $exists = $this->db->where('id', 1)->count_all_results('system_settings') > 0;
        if ($exists) {
            unset($data['id']);
            return $this->db->where('id', 1)->update('system_settings', $data);
        }

        return $this->db->insert('system_settings', $data);
    }
}
