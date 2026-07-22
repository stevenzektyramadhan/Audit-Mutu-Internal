<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends MY_Controller
{
    protected $account_service;

    public function __construct()
    {
        parent::__construct();
        $this->_check_login();
        $this->load->helper(['form', 'url', 'app']);
        $this->load->library('form_validation');
        require_once APPPATH . 'services/Account_service.php';
        $this->account_service = new Account_service();
    }

    public function index()
    {
        $account = $this->account_service->get_own_account($this->_user_id());
        if (!$account) {
            show_error('Pengguna tidak ditemukan.', 404, 'Not Found');
            return;
        }

        $this->load->view('account/index', [
            'title' => 'Akun Saya - AMI',
            'page_title' => 'Akun Saya',
            'page_subtitle' => 'Perbarui nama dan foto profil Anda',
            'active_menu' => 'account',
            'account' => $account,
        ]);
    }

    public function update()
    {
        $this->require_post();
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim');
        if ($this->form_validation->run() === FALSE) {
            $this->index();
            return;
        }

        $user_id = $this->_user_id();
        $account = $this->account_service->get_own_account($user_id);
        if (!$account) {
            show_error('Pengguna tidak ditemukan.', 404, 'Not Found');
            return;
        }

        $upload = $this->handle_photo_upload();
        if (!$upload['success']) {
            $this->session->set_flashdata('error', $upload['message']);
            redirect('account');
            return;
        }

        $result = $this->account_service->update_own_profile(
            $user_id,
            $this->input->post('nama', TRUE),
            $upload['file_name']
        );
        if (!$result['success']) {
            if ($upload['file_name'] !== NULL && !delete_private_file('user_photos', $upload['file_name'])) {
                log_message('error', 'Gagal membersihkan foto akun baru untuk user ' . $user_id);
            }
            $this->session->set_flashdata('error', $result['message']);
            redirect('account');
            return;
        }

        if ($upload['file_name'] !== NULL && !empty($result['previous_profile_photo_path'])
            && !delete_private_file('user_photos', $result['previous_profile_photo_path'])) {
            log_message('error', 'Gagal membersihkan foto akun lama untuk user ' . $user_id);
        }

        $session_data = [
            'nama' => trim((string) $this->input->post('nama', TRUE)),
            'profile_photo_path' => $upload['file_name'] !== NULL
                ? $upload['file_name']
                : $result['previous_profile_photo_path'],
        ];
        $this->session->set_userdata($session_data);
        $this->session->sess_regenerate(TRUE);
        $this->session->set_flashdata('success', $result['message']);
        redirect('account');
    }

    public function photo()
    {
        $account = $this->account_service->get_own_account($this->_user_id());
        $file_name = $account ? (string) $account->profile_photo_path : '';
        $path = private_storage_path('user_photos', $file_name);
        if ($path === NULL) {
            show_404();
            return;
        }

        $mime = $this->image_mime($path);
        if ($mime === NULL) {
            show_404();
            return;
        }

        $this->output
            ->set_header('Cache-Control: private, no-store, max-age=0')
            ->set_header('Pragma: no-cache')
            ->set_header('Expires: 0')
            ->set_content_type($mime)
            ->set_header('Content-Length: ' . (string) filesize($path))
            ->set_output(file_get_contents($path));
    }

    private function handle_photo_upload()
    {
        if (empty($_FILES['profile_photo']['name'])) {
            return ['success' => TRUE, 'file_name' => NULL, 'message' => ''];
        }

        $file = $_FILES['profile_photo'];
        if (!isset($file['error'], $file['tmp_name'], $file['size'])
            || $file['error'] !== UPLOAD_ERR_OK
            || !is_uploaded_file($file['tmp_name'])
            || (int) $file['size'] > 2 * 1024 * 1024) {
            return ['success' => FALSE, 'file_name' => NULL, 'message' => 'Foto harus berupa JPEG atau PNG maksimal 2 MiB.'];
        }

        $mime = $this->image_mime($file['tmp_name']);
        $extension = $mime === 'image/jpeg' ? 'jpg' : ($mime === 'image/png' ? 'png' : NULL);
        if ($extension === NULL) {
            return ['success' => FALSE, 'file_name' => NULL, 'message' => 'Foto harus berupa JPEG atau PNG.'];
        }

        $directory = private_storage_dir('user_photos');
        if (!is_dir($directory) && !mkdir($directory, 0700, TRUE) && !is_dir($directory)) {
            log_message('error', 'Direktori foto akun tidak dapat dibuat.');
            return ['success' => FALSE, 'file_name' => NULL, 'message' => 'Foto gagal disimpan.'];
        }

        try {
            $file_name = bin2hex(random_bytes(24)) . '.' . $extension;
        } catch (Exception $exception) {
            log_message('error', 'Nama foto akun acak gagal dibuat: ' . $exception->getMessage());
            return ['success' => FALSE, 'file_name' => NULL, 'message' => 'Foto gagal disimpan.'];
        }

        if (!move_uploaded_file($file['tmp_name'], $directory . $file_name)) {
            return ['success' => FALSE, 'file_name' => NULL, 'message' => 'Foto gagal disimpan.'];
        }

        return ['success' => TRUE, 'file_name' => $file_name, 'message' => ''];
    }

    private function image_mime($path)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $path) : FALSE;
        if ($finfo) {
            finfo_close($finfo);
        }
        $image = @getimagesize($path);

        return $image && in_array($mime, ['image/jpeg', 'image/png'], TRUE) && $image['mime'] === $mime
            ? $mime
            : NULL;
    }

    private function require_post()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            show_error('Method tidak diizinkan.', 405, 'Method Not Allowed');
            exit;
        }
    }
}
