<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upload_size_settings_model extends CI_Model
{
    public function all()
    {
        if (!$this->db->table_exists('spmi_upload_size_settings')) return [];
        return $this->db->order_by('category', 'ASC')->get('spmi_upload_size_settings')->result();
    }

    public function replace_all(array $rows)
    {
        foreach ($rows as $row) {
            $exists = $this->db->where('category', $row['category'])->count_all_results('spmi_upload_size_settings') > 0;
            if ($exists) {
                if (!$this->db->where('category', $row['category'])->update('spmi_upload_size_settings', $row)) return FALSE;
            } elseif (!$this->db->insert('spmi_upload_size_settings', $row)) {
                return FALSE;
            }
        }
        return TRUE;
    }
}
