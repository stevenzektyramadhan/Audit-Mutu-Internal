<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends MY_Controller
{
    protected $account_service;

    public function __construct()
    {
        parent::__construct();
        $this->_require_capability(Authorization_policy::CAP_ACCOUNT_SELF);
        $this->load->helper(['form', 'url', 'app']);
        $this->load->library('form_validation');
        $this->load->library('file_security');
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
            if ($upload['file_name'] !== NULL && !$this->file_security->retire(
                'user_photos',
                $upload['file_name'],
                'user',
                $user_id,
                $user_id,
                'database_update_failed'
            )) {
                log_message('error', 'Gagal membersihkan foto akun baru untuk user ' . $user_id);
            }
            $this->session->set_flashdata('error', $result['message']);
            redirect('account');
            return;
        }

        if ($upload['file_name'] !== NULL && !empty($result['previous_profile_photo_path'])
            && !$this->file_security->retire(
                'user_photos',
                $result['previous_profile_photo_path'],
                'user',
                $user_id,
                $user_id,
                'replaced'
            )) {
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
        $resolved = $this->file_security->resolve(
            'user_photos',
            $file_name,
            'user',
            $this->_user_id(),
            $this->_user_id()
        );
        if ($resolved === NULL) {
            show_404();
            return;
        }
        $path = $resolved['path'];

        $mime = $this->image_mime($path);
        if ($mime === NULL) {
            show_404();
            return;
        }
        $this->file_security->record_read(
            $resolved['asset'],
            'user_photos',
            'user',
            $this->_user_id(),
            $this->_user_id()
        );

        $this->output
            ->set_header('Cache-Control: private, no-store, max-age=0')
            ->set_header('Pragma: no-cache')
            ->set_header('Expires: 0')
            ->set_header('X-Content-Type-Options: nosniff')
            ->set_content_type($mime)
            ->set_header('Content-Length: ' . (string) filesize($path))
            ->set_output(file_get_contents($path));
    }

    private function handle_photo_upload()
    {
        if (empty($_FILES['profile_photo']['name'])) {
            return ['success' => TRUE, 'file_name' => NULL, 'message' => ''];
        }

        return $this->file_security->upload(
            'profile_photo',
            'user_photos',
            'user',
            $this->_user_id(),
            $this->_user_id()
        );
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
