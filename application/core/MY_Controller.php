<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller — Base controller untuk seluruh aplikasi AMI.
 *
 * Menyediakan method helper untuk autentikasi dan pengecekan role.
 * Seluruh controller khusus role wajib extend class turunan yang sesuai:
 *   - Admin_Controller      → super_admin & admin_lpmpi
 *   - Auditor_Controller    → auditor
 *   - Auditee_Controller    → auditee
 *   - Admin_Lpmpi_Controller → admin_lpmpi & super_admin (base fitur LPMPI)
 */
class MY_Controller extends CI_Controller
{
    /** @var Organization_logo_service */
    protected $organization_logo_service;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('System_settings_model');
        require_once APPPATH . 'services/Organization_logo_service.php';
        $this->organization_logo_service = new Organization_logo_service();
        $profile = $this->System_settings_model->profile();
        $settings = $this->System_settings_model->settings();
        $organization_name = ($profile && !empty($profile->nama_pt)) ? trim((string) $profile->nama_pt) : 'Universitas Muhammadiyah Babel';
        $app_name = ($settings && property_exists($settings, 'app_name') && $settings->app_name !== NULL && trim((string) $settings->app_name) !== '')
            ? trim((string) $settings->app_name)
            : 'Audit Mutu Internal';
        $app_short_name = ($settings && property_exists($settings, 'app_short_name') && $settings->app_short_name !== NULL && trim((string) $settings->app_short_name) !== '')
            ? trim((string) $settings->app_short_name)
            : 'AMI';

        $this->load->vars([
            'organization_logo_url' => $this->organization_logo_service->public_url($profile),
            'organization_name'     => $organization_name,
            'app_name'              => $app_name,
            'app_short_name'        => $app_short_name,
        ]);
    }

    /**
     * Cek apakah user sudah login.
     * Redirect ke halaman auth jika belum.
     */
    protected function _check_login()
    {
        if (!$this->session->userdata('user_id')) {
            redirect('auth');
            exit;
        }
    }

    /**
     * Cek apakah role user termasuk dalam daftar yang diizinkan.
     *
     * @param array $allowed_roles  Daftar role yang diizinkan
     */
    protected function _check_role(array $allowed_roles)
    {
        $this->_check_login();

        $role = $this->session->userdata('role');
        if (!in_array($role, $allowed_roles, true)) {
            show_error('Akses ditolak. Anda tidak memiliki wewenang untuk mengakses halaman ini.', 403, 'Forbidden');
        }
    }

    /**
     * Ambil user_id dari session.
     *
     * @return int
     */
    protected function _user_id()
    {
        return (int) $this->session->userdata('user_id');
    }
}

// ------------------------------------------------------------------------

/**
 * Admin_Controller — Untuk halaman yang bisa diakses super_admin DAN admin_lpmpi.
 *
 * super_admin tetap dapat mengakses seluruh halaman yang diperuntukkan bagi admin_lpmpi.
 */
class Admin_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->_check_role(['super_admin', 'admin_lpmpi']);
    }
}

// ------------------------------------------------------------------------

/**
 * Admin_Lpmpi_Controller — Base controller khusus untuk fitur Admin LPMPI.
 *
 * Extend class ini untuk semua controller fitur kelola instrumen,
 * periode audit, penugasan, penetapan, dan laporan.
 * Dapat diakses oleh admin_lpmpi dan super_admin.
 */
class Admin_Lpmpi_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->_check_role(['super_admin', 'admin_lpmpi']);
    }
}

// ------------------------------------------------------------------------

/**
 * Auditor_Controller — Untuk halaman yang hanya bisa diakses oleh auditor.
 */
class Auditor_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->_check_role(['auditor']);
    }
}

// ------------------------------------------------------------------------

/**
 * Auditee_Controller — Untuk halaman yang hanya bisa diakses oleh auditee.
 */
class Auditee_Controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->_check_role(['auditee']);
    }
}
