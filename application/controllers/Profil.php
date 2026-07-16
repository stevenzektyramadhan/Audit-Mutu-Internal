<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profil extends MY_Controller
{
    /** @var Profil_model */
    public $Profil_model;

    public function __construct()
    {
        parent::__construct();
        $this->_check_login();
        $this->load->helper(['form', 'url']);
        $this->load->model('Profil_model');
    }

    public function index()
    {
        $schema_ready = $this->Profil_model->has_tables();
        $profil = $schema_ready ? $this->Profil_model->get_profil() : NULL;
        $prodi = $schema_ready ? $this->Profil_model->get_prodi() : [];
        $mahasiswa_stats = $schema_ready ? $this->Profil_model->get_mahasiswa_stats() : [];
        $akreditasi_summary = $schema_ready ? $this->Profil_model->get_akreditasi_summary() : [];

        $data = [
            'title' => 'Profil Lembaga - AMI',
            'page_title' => 'Profil Lembaga',
            'page_subtitle' => 'Identitas perguruan tinggi, mahasiswa, dan program studi',
            'active_menu' => 'profil',
            'schema_ready' => $schema_ready,
            'can_manage' => $this->can_manage(),
            'profil' => $profil,
            'prodi' => $prodi,
            'mahasiswa_stats' => $mahasiswa_stats,
            'mahasiswa_total' => $this->sum_mahasiswa($mahasiswa_stats),
            'akreditasi_summary' => $akreditasi_summary,
            'akreditasi_chart_labels' => json_encode(array_map(function ($row) {
                return $row->akreditasi;
            }, $akreditasi_summary)),
            'akreditasi_chart_values' => json_encode(array_map(function ($row) {
                return (int) $row->jumlah;
            }, $akreditasi_summary)),
            'mahasiswa_chart_labels' => json_encode(array_map(function ($row) {
                return $row->jenjang;
            }, $mahasiswa_stats)),
            'mahasiswa_chart_values' => json_encode(array_map(function ($row) {
                return (int) $row->jumlah;
            }, $mahasiswa_stats)),
        ];

        $this->load->view('lpmpi/profil/index', $data);
    }

    public function edit()
    {
        $this->require_manage();
        $this->require_schema_ready();

        $data = [
            'title' => 'Edit Profil Lembaga - AMI',
            'page_title' => 'Edit Profil Lembaga',
            'page_subtitle' => 'Beranda / Profil Lembaga / Edit',
            'active_menu' => 'profil',
            'profil' => $this->Profil_model->get_profil(),
        ];

        $this->load->view('lpmpi/profil/form_edit', $data);
    }

    public function update()
    {
        $this->require_manage();
        $this->require_schema_ready();
        $this->require_post();

        $this->form_validation->set_rules('nama_pt', 'Nama universitas', 'required');
        $email = trim((string) $this->input->post('email', TRUE));
        if ($email !== '') {
            $this->form_validation->set_rules('email', 'Email', 'valid_email');
        }

        if ($this->form_validation->run() === FALSE) {
            $this->edit();
            return;
        }

        $existing = $this->Profil_model->get_profil();
        $data = $this->profile_input();
        $upload = $this->handle_logo_upload();

        if (!$upload['success']) {
            $this->session->set_flashdata('error', $upload['message']);
            redirect('profil/edit');
            return;
        }

        if (!empty($upload['file_name'])) {
            $data['logo_path'] = $upload['file_name'];
        }

        if ($this->Profil_model->upsert_profil($data)) {
            if (!empty($upload['file_name']) && $existing && !empty($existing->logo_path)) {
                $this->delete_local_logo($existing->logo_path);
            }

            $this->session->set_flashdata('success', 'Profil lembaga berhasil disimpan.');
            redirect('profil');
            return;
        }

        if (!empty($upload['file_name'])) {
            $this->delete_local_logo($upload['file_name']);
        }

        $this->session->set_flashdata('error', 'Profil lembaga gagal disimpan.');
        redirect('profil/edit');
    }

    public function sinkronisasi()
    {
        $this->require_manage();
        $this->require_schema_ready();
        $this->require_post();

        $profil = $this->Profil_model->get_profil();
        $nama_pt = trim((string) $this->input->post('nama_pt_pddikti', TRUE));
        $id_pt = trim((string) $this->input->post('id_pt_pddikti', TRUE));

        if ($nama_pt === '' && $profil) {
            $nama_pt = trim((string) ($profil->nama_pt_pddikti ?: $profil->nama_pt));
        }

        if ($id_pt === '' && $profil) {
            $id_pt = trim((string) $profil->id_pt_pddikti);
        }

        if ($nama_pt === '') {
            $this->session->set_flashdata('error', 'Nama PT di PDDikti wajib diisi sebelum sinkronisasi.');
            redirect('profil');
            return;
        }

        require_once APPPATH . 'services/Pddikti_service.php';
        $service = new Pddikti_service();

        try {
            $result = $service->fetch_all($nama_pt, $id_pt);
        } catch (Exception $exception) {
            $this->session->set_flashdata('error', $exception->getMessage());
            redirect('profil');
            return;
        }

        $warnings = isset($result['warnings']) && is_array($result['warnings']) ? $result['warnings'] : [];
        $replace_prodi = isset($result['prodi']) && is_array($result['prodi']) && !empty($result['prodi']);
        $replace_mahasiswa_stats = isset($result['mahasiswa_stats']) && is_array($result['mahasiswa_stats']) && !empty($result['mahasiswa_stats']);

        if (!$replace_prodi) {
            $warnings[] = 'Data program studi lokal dipertahankan karena PDDikti tidak mengirim data prodi.';
        }

        if (!$replace_mahasiswa_stats) {
            $warnings[] = 'Data statistik mahasiswa lokal dipertahankan karena PDDikti tidak mengirim data statistik.';
        }

        $this->db->trans_start();
        $this->Profil_model->upsert_profil($this->preserve_existing_profile_values($result['profil'], $profil));

        if ($replace_prodi) {
            $this->Profil_model->replace_prodi($result['prodi']);
        }

        if ($replace_mahasiswa_stats) {
            $this->Profil_model->replace_mahasiswa_stats($result['mahasiswa_stats']);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Sinkronisasi PDDikti gagal disimpan ke database lokal.');
            redirect('profil');
            return;
        }

        if (!empty($warnings)) {
            log_message('error', 'Sinkronisasi profil PDDikti sebagian: ' . implode(' ', array_unique($warnings)));
        }

        $message = empty($warnings)
            ? 'Profil lembaga berhasil disinkronkan dari PDDikti.'
            : 'Profil lembaga berhasil disinkronkan. Beberapa data PDDikti belum tersedia, jadi data lokal yang sudah ada tetap dipertahankan.';

        $this->session->set_flashdata('success', $message);
        redirect('profil');
    }

    public function upload_logo()
    {
        $this->require_manage();
        $this->require_schema_ready();
        $this->require_post();

        $existing = $this->Profil_model->get_profil();
        $upload = $this->handle_logo_upload(TRUE);

        if (!$upload['success']) {
            $this->session->set_flashdata('error', $upload['message']);
            redirect('profil/edit');
            return;
        }

        if (empty($upload['file_name'])) {
            $this->session->set_flashdata('error', 'Pilih file logo terlebih dahulu.');
            redirect('profil/edit');
            return;
        }

        if ($this->Profil_model->upsert_profil(['logo_path' => $upload['file_name']])) {
            if ($existing && !empty($existing->logo_path)) {
                $this->delete_local_logo($existing->logo_path);
            }

            $this->session->set_flashdata('success', 'Logo lembaga berhasil diupload.');
            redirect('profil');
            return;
        }

        $this->delete_local_logo($upload['file_name']);
        $this->session->set_flashdata('error', 'Logo lembaga gagal disimpan.');
        redirect('profil/edit');
    }

    private function profile_input()
    {
        return [
            'id_pt_pddikti' => $this->input->post('id_pt_pddikti', TRUE),
            'nama_pt_pddikti' => $this->input->post('nama_pt_pddikti', TRUE),
            'nama_pt' => $this->input->post('nama_pt', TRUE),
            'kode_pt' => $this->input->post('kode_pt', TRUE),
            'nomor_sk_pt' => $this->input->post('nomor_sk_pt', TRUE),
            'tanggal_sk_pt' => $this->input->post('tanggal_sk_pt', TRUE),
            'tanggal_berdiri' => $this->input->post('tanggal_berdiri', TRUE),
            'jumlah_dosen' => $this->input->post('jumlah_dosen', TRUE),
            'jumlah_tendik' => $this->input->post('jumlah_tendik', TRUE),
            'akreditasi' => $this->input->post('akreditasi', TRUE),
            'akreditasi_berlaku_sampai' => $this->input->post('akreditasi_berlaku_sampai', TRUE),
            'status_pt' => $this->input->post('status_pt', TRUE),
            'kode_pos' => $this->input->post('kode_pos', TRUE),
            'telepon' => $this->input->post('telepon', TRUE),
            'faksimile' => $this->input->post('faksimile', TRUE),
            'email' => $this->input->post('email', TRUE),
            'logo_url' => $this->input->post('logo_url', TRUE),
        ];
    }

    private function preserve_existing_profile_values($data, $existing)
    {
        if (!$existing) {
            return $data;
        }

        $preserve_columns = [
            'nama_pt',
            'kode_pt',
            'nomor_sk_pt',
            'tanggal_sk_pt',
            'tanggal_berdiri',
            'jumlah_dosen',
            'jumlah_tendik',
            'akreditasi',
            'akreditasi_berlaku_sampai',
            'status_pt',
            'kode_pos',
            'telepon',
            'faksimile',
            'email',
            'logo_path',
            'logo_url',
        ];

        foreach ($preserve_columns as $column) {
            $new_value = isset($data[$column]) ? $data[$column] : NULL;
            $old_value = isset($existing->{$column}) ? $existing->{$column} : NULL;

            if (($new_value === NULL || $new_value === '') && $old_value !== NULL && $old_value !== '') {
                $data[$column] = $old_value;
            }
        }

        return $data;
    }

    private function handle_logo_upload($required = FALSE)
    {
        if (empty($_FILES['logo']['name'])) {
            return $required
                ? ['success' => FALSE, 'file_name' => NULL, 'message' => 'File logo wajib dipilih.']
                : ['success' => TRUE, 'file_name' => NULL, 'message' => ''];
        }

        $upload_dir = $this->logo_upload_dir();
        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, TRUE)) {
            return ['success' => FALSE, 'file_name' => NULL, 'message' => 'Folder upload logo tidak dapat dibuat.'];
        }

        $config = [
            'upload_path' => $upload_dir,
            'allowed_types' => 'jpg|jpeg|png|gif',
            'max_size' => 4096,
            'file_name' => 'logo_lembaga_' . date('YmdHis'),
            'overwrite' => FALSE,
            'remove_spaces' => TRUE,
        ];

        $this->load->library('upload');
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('logo')) {
            return [
                'success' => FALSE,
                'file_name' => NULL,
                'message' => strip_tags($this->upload->display_errors('', '')),
            ];
        }

        $upload_data = $this->upload->data();
        return ['success' => TRUE, 'file_name' => $upload_data['file_name'], 'message' => ''];
    }

    private function sum_mahasiswa($rows)
    {
        $total = 0;
        foreach ($rows as $row) {
            $total += (int) $row->jumlah;
        }
        return $total;
    }

    private function require_manage()
    {
        $this->_check_role(['super_admin', 'admin_lpmpi']);
    }

    private function require_schema_ready()
    {
        if (!$this->Profil_model->has_tables()) {
            show_error('Tabel profil belum tersedia. Jalankan migration 008_create_profil_tables.sql terlebih dahulu.', 500, 'Database belum siap');
            exit;
        }
    }

    private function require_post()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            exit;
        }
    }

    private function can_manage()
    {
        return in_array($this->session->userdata('role'), ['super_admin', 'admin_lpmpi'], TRUE);
    }

    private function logo_upload_dir()
    {
        return FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'profil' . DIRECTORY_SEPARATOR;
    }

    private function delete_local_logo($file_name)
    {
        $path = $this->logo_upload_dir() . basename((string) $file_name);
        if (is_file($path)) {
            unlink($path);
        }
    }
}
