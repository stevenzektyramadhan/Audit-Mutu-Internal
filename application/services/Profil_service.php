<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profil_service
{
    protected $ci;
    protected $model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Profil_model');
        $this->model = $this->ci->Profil_model;
    }

    public function prodi($id) { return $this->model->find_prodi((int) $id); }
    public function mahasiswa_stat($id) { return $this->model->find_mahasiswa_stat((int) $id); }
    public function create_prodi($input) { return $this->save_prodi($input, 0); }
    public function update_prodi($id, $input) { return $this->save_prodi($input, (int) $id); }
    public function create_mahasiswa_stat($input) { return $this->save_mahasiswa_stat($input, 0); }
    public function update_mahasiswa_stat($id, $input) { return $this->save_mahasiswa_stat($input, (int) $id); }

    public function delete_prodi($id)
    {
        if (!$this->prodi($id)) return $this->fail('Data program studi tidak ditemukan.');
        return $this->model->delete_prodi((int) $id)
            ? $this->success('Program studi berhasil dihapus.')
            : $this->fail('Program studi gagal dihapus.');
    }

    public function delete_mahasiswa_stat($id)
    {
        if (!$this->mahasiswa_stat($id)) return $this->fail('Data statistik mahasiswa tidak ditemukan.');
        return $this->model->delete_mahasiswa_stat((int) $id)
            ? $this->success('Statistik mahasiswa berhasil dihapus.')
            : $this->fail('Statistik mahasiswa gagal dihapus.');
    }

    private function save_prodi($input, $id)
    {
        if ($id && !$this->prodi($id)) return $this->fail('Data program studi tidak ditemukan.');

        $limits = [
            'kode_prodi' => 20,
            'nama_prodi' => 200,
            'status' => 50,
            'jenjang' => 20,
            'akreditasi' => 50,
            'rasio_dosen_mahasiswa' => 20,
        ];
        $data = ['id_prodi_pddikti' => NULL];
        foreach ($limits as $field => $limit) {
            $value = trim((string) (isset($input[$field]) ? $input[$field] : ''));
            if (strlen($value) > $limit) return $this->fail('Nilai ' . str_replace('_', ' ', $field) . ' melebihi batas karakter.');
            $data[$field] = $value === '' ? NULL : $value;
        }
        if ($data['nama_prodi'] === NULL) return $this->fail('Nama program studi wajib diisi.');

        $date = trim((string) (isset($input['tanggal_sk_akreditasi']) ? $input['tanggal_sk_akreditasi'] : ''));
        if ($date !== '') {
            $parsed = DateTime::createFromFormat('!Y-m-d', $date);
            if (!$parsed || $parsed->format('Y-m-d') !== $date) return $this->fail('Tanggal SK akreditasi tidak valid.');
        }
        $data['tanggal_sk_akreditasi'] = $date === '' ? NULL : $date;

        $saved = $id ? $this->model->update_prodi($id, $data) : $this->model->create_prodi($data);
        return $saved ? $this->success('Program studi berhasil disimpan.') : $this->fail('Program studi gagal disimpan.');
    }

    private function save_mahasiswa_stat($input, $id)
    {
        if ($id && !$this->mahasiswa_stat($id)) return $this->fail('Data statistik mahasiswa tidak ditemukan.');

        $jenjang = trim((string) (isset($input['jenjang']) ? $input['jenjang'] : ''));
        $jumlah = trim((string) (isset($input['jumlah']) ? $input['jumlah'] : ''));
        if ($jenjang === '' || strlen($jenjang) > 50) return $this->fail('Jenjang wajib diisi dan maksimal 50 karakter.');
        if (!preg_match('/^\d+$/', $jumlah) || strlen($jumlah) > strlen((string) PHP_INT_MAX)) return $this->fail('Jumlah mahasiswa harus berupa bilangan bulat non-negatif.');
        if (strlen($jumlah) === strlen((string) PHP_INT_MAX) && strcmp($jumlah, (string) PHP_INT_MAX) > 0) return $this->fail('Jumlah mahasiswa melebihi batas sistem.');

        $data = ['jenjang' => $jenjang, 'jumlah' => (int) $jumlah];
        $saved = $id ? $this->model->update_mahasiswa_stat($id, $data) : $this->model->create_mahasiswa_stat($data);
        return $saved ? $this->success('Statistik mahasiswa berhasil disimpan.') : $this->fail('Statistik mahasiswa gagal disimpan.');
    }

    private function success($message) { return ['success' => TRUE, 'message' => $message]; }
    private function fail($message) { return ['success' => FALSE, 'message' => $message]; }
}
