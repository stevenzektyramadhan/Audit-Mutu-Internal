<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_ppepp_documents_model extends CI_Model
{
    public function documents($stage, $year)
    {
        return $this->db->select('d.*, u.nama AS uploader_name, uu.nama AS updater_name')
            ->from('spmi_ppepp_documents d')
            ->join('users u', 'u.id = d.uploaded_by')
            ->join('users uu', 'uu.id = d.updated_by', 'left')
            ->where('d.stage', $stage)
            ->where('d.period_year', (int) $year)
            ->order_by('d.category', 'ASC')
            ->order_by('d.document_date', 'DESC')
            ->order_by('d.id', 'DESC')
            ->get()
            ->result();
    }

    public function years()
    {
        return $this->db->select('period_year')
            ->from('spmi_ppepp_documents')
            ->group_by('period_year')
            ->order_by('period_year', 'DESC')
            ->get()
            ->result();
    }

    public function document($id, $for_update = FALSE)
    {
        if ($for_update) {
            return $this->db->query('SELECT * FROM spmi_ppepp_documents WHERE id = ? FOR UPDATE', [(int) $id])->row();
        }

        return $this->db->where('id', (int) $id)->get('spmi_ppepp_documents')->row();
    }

    public function penetapan_core_counts($year, $categories)
    {
        if (!$categories) return [];

        return $this->db->select('category, COUNT(*) AS total')
            ->from('spmi_ppepp_documents')
            ->where('stage', 'penetapan')
            ->where('period_year', (int) $year)
            ->where_in('category', $categories)
            ->group_by('category')
            ->get()
            ->result();
    }

    public function insert_document($data)
    {
        return $this->db->insert('spmi_ppepp_documents', $data) ? (int) $this->db->insert_id() : 0;
    }

    public function update_document($id, $data)
    {
        return $this->db->where('id', (int) $id)->update('spmi_ppepp_documents', $data);
    }

    public function delete_document($id)
    {
        return $this->db->where('id', (int) $id)->delete('spmi_ppepp_documents');
    }
}
