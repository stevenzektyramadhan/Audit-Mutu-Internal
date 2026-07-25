<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_standard_model extends CI_Model
{
    protected $table = 'spmi_standards';

    public function schema_ready()
    {
        return $this->db->table_exists($this->table)
            && $this->db->table_exists('spmi_versions');
    }

    public function get_for_version($spmi_version_id)
    {
        return $this->db
            ->where('spmi_version_id', (int) $spmi_version_id)
            ->order_by('sort_order', 'ASC')
            ->order_by('id', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function find($id)
    {
        return $this->db
            ->select(
                'spmi_standards.*, '
                . 'spmi_versions.organization_unit_id, '
                . 'spmi_versions.document_code AS version_document_code, '
                . 'spmi_versions.revision_number AS version_revision_number, '
                . 'spmi_versions.status AS version_status'
            )
            ->from($this->table)
            ->join(
                'spmi_versions',
                'spmi_versions.id = spmi_standards.spmi_version_id'
            )
            ->where('spmi_standards.id', (int) $id)
            ->get()
            ->row();
    }

    public function find_for_update($id)
    {
        $query = $this->db
            ->where('id', (int) $id)
            ->limit(1)
            ->get_compiled_select($this->table);

        return $this->db->query($query . ' FOR UPDATE')->row();
    }

    public function lock_for_version($spmi_version_id)
    {
        $query = $this->db
            ->where('spmi_version_id', (int) $spmi_version_id)
            ->order_by('id', 'ASC')
            ->get_compiled_select($this->table);

        return $this->db->query($query . ' FOR UPDATE')->result();
    }

    public function count_for_version($spmi_version_id)
    {
        return (int) $this->db
            ->where('spmi_version_id', (int) $spmi_version_id)
            ->count_all_results($this->table);
    }

    public function count_active_for_version($spmi_version_id)
    {
        return (int) $this->db
            ->where('spmi_version_id', (int) $spmi_version_id)
            ->where('active', 1)
            ->count_all_results($this->table);
    }

    public function code_exists($spmi_version_id, $code, $except_id = NULL)
    {
        $this->db
            ->from($this->table)
            ->where('spmi_version_id', (int) $spmi_version_id)
            ->where('code', (string) $code);
        if ($except_id !== NULL) {
            $this->db->where('id !=', (int) $except_id);
        }

        return (int) $this->db->count_all_results() > 0;
    }

    public function create(array $data)
    {
        if (!$this->db->insert($this->table, $data)) {
            return NULL;
        }

        return (int) $this->db->insert_id();
    }

    public function insert_batch(array $rows)
    {
        if (empty($rows)) {
            return TRUE;
        }

        return $this->db->insert_batch($this->table, $rows) !== FALSE;
    }

    public function update($id, array $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, $data);
    }

    public function copy_for_version($source_version_id, $target_version_id)
    {
        $copied = $this->db->query(
            'INSERT INTO ' . $this->table . ' (
                spmi_version_id,
                code,
                name,
                group_type,
                standard_type,
                rationale,
                definitions,
                sort_order,
                active
            )
            SELECT
                ?,
                code,
                name,
                group_type,
                standard_type,
                rationale,
                definitions,
                sort_order,
                active
            FROM ' . $this->table . '
            WHERE spmi_version_id = ?
            ORDER BY sort_order, id',
            [(int) $target_version_id, (int) $source_version_id]
        );

        return $copied !== FALSE;
    }
}
