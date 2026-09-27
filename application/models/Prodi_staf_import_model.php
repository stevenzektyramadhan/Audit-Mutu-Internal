<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prodi_staf_import_model extends CI_Model
{
    public function find_prodi_by_code($code, $lock = FALSE)
    {
        if ($lock) return $this->db->query('SELECT * FROM profil_prodi WHERE kode_prodi = ? FOR UPDATE', [$code])->result();
        return $this->db->where('kode_prodi', $code)->get('profil_prodi')->result();
    }

    // ponytail: one global lock serializes small imports; use keyed locks if independent batches need concurrency.
    public function acquire_confirm_lock() { return (int) $this->db->query("SELECT GET_LOCK('prodi_import_confirm', 10) AS acquired")->row()->acquired === 1; }
    public function release_confirm_lock() { $this->db->query("SELECT RELEASE_LOCK('prodi_import_confirm')"); }

    public function create_prodi($data) { return $this->db->insert('profil_prodi', $data); }
    public function update_prodi($id, $data) { return $this->db->where('id', (int) $id)->update('profil_prodi', $data); }
}
