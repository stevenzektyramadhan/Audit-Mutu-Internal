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