<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account_service
{
    protected $ci;
    protected $user_model;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('User_model');
        $this->user_model = $this->ci->User_model;
    }

    public function get_own_account($user_id)
    {
        return $this->user_model->find((int) $user_id);
    }

    public function update_own_profile($user_id, $nama, $profile_photo_path = NULL)
    {
        $nama = trim((string) $nama);
        if ($nama === '') {
            return ['success' => FALSE, 'message' => 'Nama wajib diisi.'];
        }

        $data = ['nama' => $nama];
        if ($profile_photo_path !== NULL) {
            $data['profile_photo_path'] = $profile_photo_path;
        }

        $this->ci->db->trans_start();
        $account = $this->user_model->find_profile_photo_for_update((int) $user_id);
        if ($account) {
            $this->user_model->update_own_profile((int) $user_id, $data);
        } else {
            $this->ci->db->trans_mark_rollback();
        }
        $this->ci->db->trans_complete();

        return $this->ci->db->trans_status()
            ? [
                'success' => TRUE,
                'message' => 'Akun berhasil diperbarui.',
                'previous_profile_photo_path' => $account->profile_photo_path,
            ]
            : ['success' => FALSE, 'message' => 'Akun gagal diperbarui.'];
    }
}
