<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_version_model extends CI_Model
{
    protected $table = 'spmi_versions';

    public function schema_ready()
    {
        return $this->db->table_exists($this->table)
            && $this->db->table_exists('organization_units')
            && $this->db->table_exists('file_assets')
            && $this->db->table_exists('users');
    }

    public function find($id)
    {
        return $this->base_query()
            ->where('spmi_versions.id', (int) $id)
            ->get()
            ->row();
    }

    public function get_for_organization_unit($organization_unit_id)
    {
        return $this->base_query()
            ->where(
                'spmi_versions.organization_unit_id',
                (int) $organization_unit_id
            )
            ->order_by('spmi_versions.document_code', 'ASC')
            ->order_by('spmi_versions.effective_date', 'DESC')
            ->order_by('spmi_versions.id', 'DESC')
            ->get()
            ->result();
    }

    public function find_active(
        $organization_unit_id,
        $document_code,
        $on_date
    ) {
        return $this->base_query()
            ->where(
                'spmi_versions.organization_unit_id',
                (int) $organization_unit_id
            )
            ->where('spmi_versions.document_code', (string) $document_code)
            ->where('spmi_versions.status', 'active')
            ->where('spmi_versions.effective_date <=', (string) $on_date)
            ->group_start()
                ->where('spmi_versions.expires_at IS NULL', NULL, FALSE)
                ->or_where('spmi_versions.expires_at >=', (string) $on_date)
            ->group_end()
            ->limit(1)
            ->get()
            ->row();
    }

    public function revision_exists(
        $organization_unit_id,
        $document_code,
        $revision_number,
        $except_id = NULL
    ) {
        $this->db
            ->from($this->table)
            ->where('organization_unit_id', (int) $organization_unit_id)
            ->where('document_code', (string) $document_code)
            ->where('revision_number', (string) $revision_number);

        if ($except_id !== NULL) {
            $this->db->where('id !=', (int) $except_id);
        }

        return $this->db->count_all_results() > 0;
    }

    public function find_for_update($id)
    {
        $query = $this->db
            ->where('id', (int) $id)
            ->limit(1)
            ->get_compiled_select($this->table);

        return $this->db->query($query . ' FOR UPDATE')->row();
    }

    public function lock_identity($organization_unit_id, $document_code)
    {
        $query = $this->db
            ->where('organization_unit_id', (int) $organization_unit_id)
            ->where('document_code', (string) $document_code)
            ->order_by('id', 'ASC')
            ->get_compiled_select($this->table);

        return $this->db->query($query . ' FOR UPDATE')->result();
    }

    public function create(array $data)
    {
        if (!$this->db->insert($this->table, $data)) {
            return NULL;
        }

        return (int) $this->db->insert_id();
    }

    public function update_draft($id, array $data)
    {
        $updated = $this->db
            ->where('id', (int) $id)
            ->where('status', 'draft')
            ->update($this->table, $data);

        return $updated && $this->db->affected_rows() === 1;
    }

    public function transition($id, $from_status, $to_status, array $data = [])
    {
        $data['status'] = (string) $to_status;
        $updated = $this->db
            ->where('id', (int) $id)
            ->where('status', (string) $from_status)
            ->update($this->table, $data);

        return $updated && $this->db->affected_rows() === 1;
    }

    public function retire_active_for_identity(
        $organization_unit_id,
        $document_code,
        $except_id,
        $expires_at
    ) {
        $query = $this->db
            ->where('organization_unit_id', (int) $organization_unit_id)
            ->where('document_code', (string) $document_code)
            ->where('status', 'active')
            ->where('id !=', (int) $except_id)
            ->update($this->table, [
                'status' => 'retired',
                'expires_at' => (string) $expires_at,
            ]);

        return $query;
    }

    protected function base_query()
    {
        return $this->db
            ->select(
                'spmi_versions.*, '
                . 'organization_units.code AS organization_unit_code, '
                . 'organization_units.name AS organization_unit_name, '
                . 'organization_units.active AS organization_unit_active, '
                . 'creator.nama AS creator_name, '
                . 'approver.nama AS approver_name, '
                . 'file_assets.category AS source_file_category, '
                . 'file_assets.stored_name AS source_file_stored_name, '
                . 'file_assets.original_name AS source_file_original_name, '
                . 'file_assets.storage_scope AS source_file_storage_scope, '
                . 'file_assets.status AS source_file_status'
            )
            ->from($this->table)
            ->join(
                'organization_units',
                'organization_units.id = spmi_versions.organization_unit_id'
            )
            ->join(
                'file_assets',
                'file_assets.id = spmi_versions.source_file_asset_id'
            )
            ->join('users AS creator', 'creator.id = spmi_versions.created_by')
            ->join(
                'users AS approver',
                'approver.id = spmi_versions.approved_by',
                'left'
            );
    }
}
