<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prodi_staf_service
{
    protected $ci;
    protected $model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Prodi_staf_model');
        $this->model = $this->ci->Prodi_staf_model;
    }

    public function ready() { return $this->model->has_tables(); }
    public function prodi($id) { return $this->model->find_prodi((int) $id); }
    public function prodi_list($exclude_id = 0) { return $this->model->list_prodi((int) $exclude_id); }
    public function eligible_users() { return $this->model->list_eligible_users(); }
    public function staf($prodi_id, $status) { return $this->model->list_staf((int) $prodi_id, $status); }

    public function add($prodi_id, $account_id, $jabatan)
    {
        return $this->transaction(function () use ($prodi_id, $account_id, $jabatan) {
            if (!$this->model->find_prodi($prodi_id, TRUE)) return $this->fail('Program studi tidak ditemukan.');
            if (!$this->model->find_eligible_user($account_id, TRUE)) return $this->fail('Akun staf tidak tersedia atau rolenya tidak memenuhi syarat.');
            if ($this->model->find_pair($account_id, $prodi_id, TRUE)) return $this->fail('Akun sudah memiliki relasi dengan program studi ini.');
            if (!$this->model->create_staf($account_id, $prodi_id, $this->jabatan($jabatan))) return $this->fail('Relasi staf gagal disimpan.');
            return $this->success('Staf program studi berhasil ditambahkan.');
        });
    }

    public function update_jabatan($prodi_id, $staf_id, $jabatan)
    {
        return $this->transaction(function () use ($prodi_id, $staf_id, $jabatan) {
            if (!$this->model->find_prodi($prodi_id, TRUE)) return $this->fail('Program studi tidak ditemukan.');
            if (!$this->model->find_staf($staf_id, $prodi_id, TRUE)) return $this->fail('Relasi staf tidak ditemukan pada program studi ini.');
            if (!$this->model->update_jabatan($staf_id, $prodi_id, $this->jabatan($jabatan))) return $this->fail('Jabatan staf gagal diperbarui.');
            return $this->success('Jabatan staf berhasil diperbarui.');
        });
    }

    public function move($source_prodi_id, $staf_id, $target_prodi_id)
    {
        return $this->transaction(function () use ($source_prodi_id, $staf_id, $target_prodi_id) {
            if ((int) $source_prodi_id === (int) $target_prodi_id) return $this->fail('Program studi tujuan harus berbeda.');
            $prodi = [];
            foreach ($this->model->find_prodi_pair_for_update($source_prodi_id, $target_prodi_id) as $row) $prodi[(int) $row->id] = $row;
            if (!isset($prodi[(int) $source_prodi_id])) return $this->fail('Program studi asal tidak ditemukan.');
            if (!isset($prodi[(int) $target_prodi_id])) return $this->fail('Program studi tujuan tidak ditemukan.');
            $staf = $this->model->find_staf($staf_id, $source_prodi_id, TRUE);
            if (!$staf) return $this->fail('Relasi staf tidak ditemukan pada program studi ini.');
            if ($staf->status !== 'active') return $this->fail('Hanya relasi staf aktif yang dapat dipindahkan.');
            if ($this->model->find_pair($staf->id_akun, $target_prodi_id, TRUE)) return $this->fail('Akun sudah memiliki relasi dengan program studi tujuan.');
            if (!$this->model->move_staf($staf_id, $source_prodi_id, $target_prodi_id)) return $this->fail('Relasi staf gagal dipindahkan.');
            return $this->success('Staf berhasil dipindahkan ke program studi tujuan.');
        });
    }

    public function deactivate($prodi_id, $staf_id) { return $this->transition($prodi_id, $staf_id, 'active', 'inactive', 'Relasi staf berhasil dinonaktifkan.'); }
    public function reactivate($prodi_id, $staf_id) { return $this->transition($prodi_id, $staf_id, 'inactive', 'active', 'Relasi staf berhasil diaktifkan kembali.'); }

    private function transition($prodi_id, $staf_id, $from, $to, $message)
    {
        return $this->transaction(function () use ($prodi_id, $staf_id, $from, $to, $message) {
            if (!$this->model->find_prodi($prodi_id, TRUE)) return $this->fail('Program studi tidak ditemukan.');
            $staf = $this->model->find_staf($staf_id, $prodi_id, TRUE);
            if (!$staf) return $this->fail('Relasi staf tidak ditemukan pada program studi ini.');
            if ($staf->status !== $from) return $this->fail('Status relasi staf tidak dapat diubah.');
            if (!$this->model->update_status($staf_id, $prodi_id, $from, $to)) return $this->fail('Status relasi staf gagal diperbarui.');
            return $this->success($message);
        });
    }

    private function transaction($callback)
    {
        $this->ci->db->trans_begin();
        try {
            $result = $callback();
            if (empty($result['success']) || !$this->ci->db->trans_status()) {
                $this->ci->db->trans_rollback();
                return !empty($result['success']) ? $this->fail('Perubahan staf gagal disimpan.') : $result;
            }
            $this->ci->db->trans_commit();
            return $result;
        } catch (Throwable $exception) {
            $this->ci->db->trans_rollback();
            log_message('error', 'Prodi staf manual mutation failed.');
            return $this->fail('Perubahan staf gagal diproses.');
        }
    }

    private function jabatan($value)
    {
        $value = trim((string) $value);
        if (mb_strlen($value) > 100) throw new InvalidArgumentException('Jabatan maksimal 100 karakter.');
        return $value === '' ? NULL : $value;
    }
    private function success($message) { return ['success' => TRUE, 'message' => $message]; }
    private function fail($message) { return ['success' => FALSE, 'message' => $message]; }
}
