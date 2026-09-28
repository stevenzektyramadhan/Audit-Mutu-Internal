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
        $this->ci->db->trans_begin();
        try {
            $prodi = $this->model->find_prodi_for_update($id);
            if (!$prodi) {
                $this->ci->db->trans_rollback();
                return $this->fail('Data program studi tidak ditemukan.');
            }
            if (!empty($prodi->organization_unit_id)) {
                $this->ci->db->trans_rollback();
                return $this->fail('Program studi yang sudah terhubung ke struktur organisasi tidak dapat dihapus.');
            }
            if ($this->model->prodi_staf_for_update($id)) {
                $this->ci->db->trans_rollback();
                return $this->fail('Program studi tidak dapat dihapus karena masih memiliki relasi staf aktif.');
            }
            if (!$this->model->delete_prodi((int) $id) || !$this->ci->db->trans_status()) {
                $this->ci->db->trans_rollback();
                return $this->fail('Program studi gagal dihapus.');
            }
            $this->ci->db->trans_commit();
            return $this->success('Program studi berhasil dihapus.');
        } catch (Throwable $exception) {
            $this->ci->db->trans_rollback();
            return $this->fail('Program studi gagal dihapus.');
        }
    }

    public function link_prodi($id, $faculty_id)
    {
        $this->ci->db->trans_begin();
        try {
            $prodi = $this->model->find_prodi_for_update((int) $id);
            if (!$prodi) return $this->rollback('Data program studi tidak ditemukan.');
            if (!empty($prodi->organization_unit_id)) return $this->rollback('Program studi sudah terhubung ke struktur organisasi.');
            if (!$this->valid_faculty((int) $faculty_id)) return $this->rollback('Fakultas aktif tidak valid.');
            if (trim((string) $prodi->kode_prodi) === '') return $this->rollback('Kode prodi wajib diisi sebelum menghubungkan struktur organisasi.');

            $unit_result = $this->matching_unbound_study_program(trim((string) $prodi->kode_prodi));
            if (!$unit_result['success']) return $this->rollback($unit_result['message']);
            $unit = $unit_result['unit'];
            if (!$unit) {
                if (!$this->model->create_organization_unit(['code' => trim((string) $prodi->kode_prodi), 'name' => trim((string) $prodi->nama_prodi), 'type' => 'study_program', 'parent_id' => (int) $faculty_id, 'is_active' => 1])) return $this->rollback('Unit organisasi program studi gagal dibuat.');
                $unit_id = (int) $this->ci->db->insert_id();
            } else {
                if (!$this->model->update_organization_unit((int) $unit->id, ['code' => trim((string) $prodi->kode_prodi), 'name' => trim((string) $prodi->nama_prodi), 'parent_id' => (int) $faculty_id])) return $this->rollback('Unit organisasi program studi gagal disimpan.');
                $unit_id = (int) $unit->id;
            }

            if (!$this->model->update_prodi((int) $id, ['organization_unit_id' => $unit_id])) return $this->rollback('Program studi gagal dihubungkan.');
            if (!$this->ci->db->trans_status()) return $this->rollback('Program studi gagal dihubungkan.');
            $this->ci->db->trans_commit();
            return $this->success('Program studi berhasil dihubungkan ke struktur organisasi.');
        } catch (Throwable $exception) {
            $this->ci->db->trans_rollback();
            return $this->fail('Program studi gagal dihubungkan.');
        }
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
        if ($data['kode_prodi'] === NULL) return $this->fail('Kode program studi wajib diisi.');
        if ($data['nama_prodi'] === NULL) return $this->fail('Nama program studi wajib diisi.');
        if ($data['jenjang'] === NULL) return $this->fail('Jenjang program studi wajib diisi.');

        $date = trim((string) (isset($input['tanggal_sk_akreditasi']) ? $input['tanggal_sk_akreditasi'] : ''));
        if ($date !== '') {
            $parsed = DateTime::createFromFormat('!Y-m-d', $date);
            if (!$parsed || $parsed->format('Y-m-d') !== $date) return $this->fail('Tanggal SK akreditasi tidak valid.');
        }
        $data['tanggal_sk_akreditasi'] = $date === '' ? NULL : $date;

        $faculty_id = (int) (isset($input['faculty_id']) ? $input['faculty_id'] : 0);
        $this->ci->db->trans_begin();
        try {
            $current = $id ? $this->model->find_prodi_for_update($id) : NULL;
            if ($id && !$current) return $this->rollback('Data program studi tidak ditemukan.');
            if (!$id || !empty($current->organization_unit_id)) {
                if (!$this->valid_faculty($faculty_id)) return $this->rollback('Fakultas aktif tidak valid.');
            }

            if ($id && empty($current->organization_unit_id)) {
                $saved = $this->model->update_prodi($id, $data);
            } elseif ($id) {
                $unit = $this->model->find_organization_unit_for_update((int) $current->organization_unit_id);
                if (!$unit || $unit->type !== 'study_program') return $this->rollback('Unit organisasi program studi tidak valid.');
                if (!$this->model->update_organization_unit((int) $unit->id, ['code' => $data['kode_prodi'], 'name' => $data['nama_prodi'], 'parent_id' => $faculty_id])) return $this->rollback('Unit organisasi program studi gagal disimpan.');
                $saved = $this->model->update_prodi($id, $data);
            } else {
                if (!$this->model->create_organization_unit(['code' => $data['kode_prodi'], 'name' => $data['nama_prodi'], 'type' => 'study_program', 'parent_id' => $faculty_id, 'is_active' => 1])) return $this->rollback('Unit organisasi program studi gagal dibuat.');
                $data['organization_unit_id'] = (int) $this->ci->db->insert_id();
                $saved = $this->model->create_prodi($data);
            }

            if (!$saved || !$this->ci->db->trans_status()) return $this->rollback('Program studi gagal disimpan.');
            $this->ci->db->trans_commit();
            return $this->success('Program studi berhasil disimpan.');
        } catch (Throwable $exception) {
            $this->ci->db->trans_rollback();
            return $this->fail('Program studi gagal disimpan.');
        }
    }

    private function valid_faculty($faculty_id)
    {
        $faculty = $this->model->find_organization_unit_for_update((int) $faculty_id);
        return $faculty && $faculty->type === 'faculty' && (int) $faculty->is_active === 1;
    }

    private function matching_unbound_study_program($code)
    {
        $units = $this->model->find_organization_units_by_code_for_update($code);
        if (count($units) > 1) return ['success' => FALSE, 'message' => 'Kode unit organisasi ambigu. Pilih kode Prodi lain atau rapikan struktur organisasi terlebih dahulu.', 'unit' => NULL];
        if (count($units) === 0) return ['success' => TRUE, 'message' => '', 'unit' => NULL];
        $unit = $units[0];
        if ($unit->type !== 'study_program') return ['success' => FALSE, 'message' => 'Kode unit organisasi sudah digunakan oleh unit non-Prodi.', 'unit' => NULL];
        if ($this->model->organization_unit_bound_to_prodi((int) $unit->id)) return ['success' => FALSE, 'message' => 'Unit organisasi Program Studi sudah terhubung ke Prodi lain.', 'unit' => NULL];
        return ['success' => TRUE, 'message' => '', 'unit' => $unit];
    }

    private function rollback($message)
    {
        $this->ci->db->trans_rollback();
        return $this->fail($message);
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
