<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Periode_service
{
    /** @var CI_Controller */
    protected $ci;

    /** @var Periode_model */
    protected $periode_model;
    protected $audit_logger;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('Periode_model');
        $this->ci->load->library('audit_logger');
        $this->periode_model = $this->ci->Periode_model;
        $this->audit_logger = $this->ci->audit_logger;
    }

    /**
     * Ambil semua periode.
     *
     * @return array
     */
    public function get_all()
    {
        return $this->periode_model->get_all();
    }

    /**
     * Cari periode berdasarkan ID.
     *
     * @param int $id
     * @return object|null
     */
    public function find($id)
    {
        return $this->periode_model->find((int) $id);
    }

    /**
     * Buat periode baru.
     *
     * @param array $data
     * @return array ['success' => bool, 'message' => string]
     */
    public function create($data)
    {
        $data = $this->normalize($data);

        $validation = $this->validate($data);
        if ($validation !== TRUE) {
            return $validation;
        }

        // Jika data.is_aktif = 1, nonaktifkan semua periode lain dulu
        $is_aktif = !empty($data['is_aktif']) ? 1 : 0;
        unset($data['is_aktif']);

        $this->ci->db->trans_start();

        if ($is_aktif) {
            $this->periode_model->deactivate_all();
        }

        $data['is_aktif'] = $is_aktif;
        $inserted = $this->periode_model->create($data);

        $this->ci->db->trans_complete();

        if ($inserted && $this->ci->db->trans_status()) {
            $periode_id = (int) $this->ci->db->insert_id();
            $this->audit_logger->record(
                'audit_period_created',
                'audit_period',
                $periode_id,
                'create',
                NULL,
                $data,
                [
                    'status_to' => $is_aktif ? 'active' : 'inactive',
                    'changed_fields' => array_keys($data),
                ]
            );
            return ['success' => TRUE, 'message' => 'Periode audit berhasil ditambahkan.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal menambahkan periode audit.'];
    }

    /**
     * Update periode.
     *
     * @param int   $id
     * @param array $data
     * @return array ['success' => bool, 'message' => string]
     */
    public function update($id, $data)
    {
        $id = (int) $id;
        $existing = $this->periode_model->find($id);

        if (!$existing) {
            return ['success' => FALSE, 'message' => 'Periode audit tidak ditemukan.'];
        }

        $data = $this->normalize($data);

        $validation = $this->validate($data, $id);
        if ($validation !== TRUE) {
            return $validation;
        }

        $is_aktif = !empty($data['is_aktif']) ? 1 : 0;
        unset($data['is_aktif']);

        $this->ci->db->trans_start();

        if ($is_aktif) {
            $this->periode_model->deactivate_all();
        }

        $data['is_aktif'] = $is_aktif;
        $updated = $this->periode_model->update($id, $data);

        $this->ci->db->trans_complete();

        if ($updated && $this->ci->db->trans_status()) {
            $this->audit_logger->record(
                'audit_period_updated',
                'audit_period',
                $id,
                'update',
                [
                    'nama_periode' => $existing->nama_periode,
                    'tahun_akademik' => $existing->tahun_akademik,
                    'semester' => $existing->semester,
                    'tanggal_buka' => $existing->tanggal_buka,
                    'tanggal_tutup' => $existing->tanggal_tutup,
                    'is_aktif' => (int) $existing->is_aktif,
                ],
                $data,
                [
                    'status_from' => (int) $existing->is_aktif ? 'active' : 'inactive',
                    'status_to' => $is_aktif ? 'active' : 'inactive',
                    'changed_fields' => array_keys($data),
                ]
            );
            return ['success' => TRUE, 'message' => 'Periode audit berhasil diperbarui.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal memperbarui periode audit.'];
    }

    /**
     * Hapus periode.
     *
     * @param int $id
     * @return array ['success' => bool, 'message' => string]
     */
    public function delete($id)
    {
        $id = (int) $id;
        $existing = $this->periode_model->find($id);

        if (!$existing) {
            return ['success' => FALSE, 'message' => 'Periode audit tidak ditemukan.'];
        }

        if ($this->periode_model->delete($id)) {
            $this->audit_logger->record(
                'audit_period_deleted',
                'audit_period',
                $id,
                'delete',
                ['is_aktif' => (int) $existing->is_aktif],
                NULL,
                ['status_from' => (int) $existing->is_aktif ? 'active' : 'inactive']
            );
            return ['success' => TRUE, 'message' => 'Periode audit berhasil dihapus.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal menghapus periode audit.'];
    }

    /**
     * Toggle status aktif/nonaktif periode.
     *
     * @param int $id
     * @return array ['success' => bool, 'message' => string]
     */
    public function toggle_aktif($id)
    {
        $id = (int) $id;
        $existing = $this->periode_model->find($id);

        if (!$existing) {
            return ['success' => FALSE, 'message' => 'Periode audit tidak ditemukan.'];
        }

        $is_aktif = (int) $existing->is_aktif;

        // Jika sudah aktif, nonaktifkan
        if ($is_aktif === 1) {
            $this->periode_model->update($id, ['is_aktif' => 0]);
            $this->audit_logger->record(
                'audit_period_deactivated',
                'audit_period',
                $id,
                'deactivate',
                ['is_aktif' => 1],
                ['is_aktif' => 0],
                ['status_from' => 'active', 'status_to' => 'inactive']
            );
            return ['success' => TRUE, 'message' => 'Periode audit dinonaktifkan.'];
        }

        // Jika tidak aktif, aktifkan (nonaktifkan semua yg lain dulu)
        if ($this->periode_model->set_active($id)) {
            $this->audit_logger->record(
                'audit_period_activated',
                'audit_period',
                $id,
                'activate',
                ['is_aktif' => 0],
                ['is_aktif' => 1],
                ['status_from' => 'inactive', 'status_to' => 'active']
            );
            return ['success' => TRUE, 'message' => 'Periode audit berhasil diaktifkan.'];
        }

        return ['success' => FALSE, 'message' => 'Gagal mengubah status periode audit.'];
    }

    /**
     * Normalisasi data input.
     *
     * @param array $data
     * @return array
     */
    private function normalize($data)
    {
        return [
            'nama_periode'   => trim(isset($data['nama_periode']) ? (string) $data['nama_periode'] : ''),
            'tahun_akademik' => trim(isset($data['tahun_akademik']) ? (string) $data['tahun_akademik'] : ''),
            'semester'       => isset($data['semester']) && in_array($data['semester'], ['ganjil', 'genap'], TRUE) ? $data['semester'] : 'ganjil',
            'tanggal_buka'   => trim(isset($data['tanggal_buka']) ? (string) $data['tanggal_buka'] : ''),
            'tanggal_tutup'  => trim(isset($data['tanggal_tutup']) ? (string) $data['tanggal_tutup'] : ''),
            'is_aktif'       => !empty($data['is_aktif']) ? 1 : 0,
        ];
    }

    /**
     * Validasi data periode.
     *
     * @param array    $data
     * @param int|null $id
     * @return array|true TRUE jika valid, array pesan error jika tidak
     */
    private function validate($data, $id = NULL)
    {
        if ($data['nama_periode'] === '') {
            return ['success' => FALSE, 'message' => 'Nama periode wajib diisi.'];
        }

        if ($data['tahun_akademik'] === '') {
            return ['success' => FALSE, 'message' => 'Tahun akademik wajib diisi.'];
        }

        if (!in_array($data['semester'], ['ganjil', 'genap'], TRUE)) {
            return ['success' => FALSE, 'message' => 'Semester tidak valid.'];
        }

        if ($data['tanggal_buka'] === '') {
            return ['success' => FALSE, 'message' => 'Tanggal buka wajib diisi.'];
        }

        if ($data['tanggal_tutup'] === '') {
            return ['success' => FALSE, 'message' => 'Tanggal tutup wajib diisi.'];
        }

        if ($data['tanggal_tutup'] < $data['tanggal_buka']) {
            return ['success' => FALSE, 'message' => 'Tanggal tutup tidak boleh lebih awal dari tanggal buka.'];
        }

        return TRUE;
    }
}

