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
