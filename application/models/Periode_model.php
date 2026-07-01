<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Periode_model extends CI_Model
{
    protected $table = 'periode_audit';

    /**
     * Ambil semua periode, diurutkan berdasarkan created_at DESC.
     *
     * @return array
     */
    public function get_all()
    {
        return $this->db
            ->order_by('created_at', 'DESC')
            ->get($this->table)
            ->result();
    }

    /**
     * Cari periode berdasarkan ID.
     *
     * @param int $id
     * @return object|null
     */
    public function find($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->get($this->table)
            ->row();
    }

    /**
     * Simpan periode baru.
     *
     * @param array $data
     * @return bool
     */
    public function create($data)
    {
        return $this->db->insert($this->table, $data);
    }

    /**
     * Update periode.
     *
     * @param int   $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        return $this->db
            ->where('id', (int) $id)
            ->update($this->table, $data);
    }

    /**
     * Hapus periode (hard delete).
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->db
            ->where('id', (int) $id)
            ->delete($this->table);
    }

    /**
     * Nonaktifkan semua periode (set is_aktif = 0).
     *
     * @return bool
     */
    public function deactivate_all()
    {
        return $this->db
            ->update($this->table, ['is_aktif' => 0]);
    }

    /**
     * Aktifkan periode tertentu (dan nonaktifkan yang lain).
     *
     * @param int $id
     * @return bool
     */
    public function set_active($id)
    {
        $this->db->trans_start();

        $this->deactivate_all();
        $this->db
            ->where('id', (int) $id)
            ->update($this->table, ['is_aktif' => 1]);

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /**
     * Ambil periode yang sedang aktif.
     *
     * @return object|null
     */
    public function get_active()
    {
        return $this->db
            ->where('is_aktif', 1)
            ->get($this->table)
            ->row();
    }
}
