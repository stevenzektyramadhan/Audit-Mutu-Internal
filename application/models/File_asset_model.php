<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class File_asset_model extends CI_Model
{
    protected $asset_table = 'file_assets';
    protected $event_table = 'file_security_events';

    public function schema_ready()
    {
        return $this->db->table_exists($this->asset_table)
            && $this->db->table_exists($this->event_table);
    }

    public function create_asset(array $data)
    {
        if (!$this->db->insert($this->asset_table, $data)) {
            return NULL;
        }

        return (int) $this->db->insert_id();
    }

    public function find($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->limit(1)
            ->get($this->asset_table)
            ->row();
    }

    public function find_for_update($id)
    {
        $query = $this->db
            ->where('id', (int) $id)
            ->limit(1)
            ->get_compiled_select($this->asset_table);

        return $this->db->query($query . ' FOR UPDATE')->row();
    }

    public function assign_owner($id, $owner_type, $owner_id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->where('status', 'active')
            ->where('owner_type', (string) $owner_type)
            ->group_start()
                ->where('owner_id IS NULL', NULL, FALSE)
                ->or_where('owner_id', (int) $owner_id)
            ->group_end()
            ->update($this->asset_table, [
                'owner_id' => (int) $owner_id,
            ]);
    }

    public function find_by_storage($category, $stored_name)
    {
        return $this->db
            ->where('category', (string) $category)
            ->where('stored_name', (string) $stored_name)
            ->limit(1)
            ->get($this->asset_table)
            ->row();
    }

    public function active_names($category, array $stored_names)
    {
        $stored_names = array_values(array_unique(array_filter(array_map('strval', $stored_names))));
        if (empty($stored_names) || !$this->schema_ready()) {
            return [];
        }

        $rows = $this->db
            ->select('stored_name, original_name')
            ->where('category', (string) $category)
            ->where('status', 'active')
            ->where_in('stored_name', $stored_names)
            ->get($this->asset_table)
            ->result();

        $result = [];
        foreach ($rows as $row) {
            $result[(string) $row->stored_name] = (string) $row->original_name;
        }

        return $result;
    }

    public function retire($id, $actor_user_id, $retention_until)
    {
        return $this->db
            ->where('id', (int) $id)
            ->where('status', 'active')
            ->update($this->asset_table, [
                'status' => 'deleted',
                'deleted_by' => $actor_user_id > 0 ? (int) $actor_user_id : NULL,
                'deleted_at' => date('Y-m-d H:i:s'),
                'retention_until' => $retention_until,
            ]);
    }

    public function expired($limit)
    {
        return $this->db
            ->where('status', 'deleted')
            ->where('retention_until IS NOT NULL', NULL, FALSE)
            ->where('retention_until <=', date('Y-m-d H:i:s'))
            ->order_by('id', 'ASC')
            ->limit(max(1, min(500, (int) $limit)))
            ->get($this->asset_table)
            ->result();
    }

    public function mark_purged($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->where('status', 'deleted')
            ->update($this->asset_table, [
                'status' => 'purged',
                'purged_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function record_event(array $data)
    {
        return $this->db->insert($this->event_table, $data);
    }
}
