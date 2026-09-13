<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Akun Controller - Manajemen Akun Auditee & Auditor (Admin LPMPI)
 *
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Session $session
 */
class Akun extends Admin_Lpmpi_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
    }

    public function index()
    {
        redirect($this->canonical_index_uri());
    }

    public function create()
    {
        redirect('users/create');
    }

    public function store()
    {
        redirect('users/create');
    }

    public function edit($id)
    {
        redirect('users/edit/' . (int) $id);
    }

    public function update($id)
    {
        redirect('users/edit/' . (int) $id);
    }

    public function delete($id)
    {
        redirect('users');
    }

    private function canonical_index_uri()
    {
        $query = [];
        $q = trim((string) $this->input->get('q', TRUE));
        $role = (string) $this->input->get('role', TRUE);

        if ($q !== '') {
            $query['q'] = $q;
        }

        if (in_array($role, ['auditor', 'auditee'], TRUE)) {
            $query['role'] = $role;
        }

        return 'users' . ($query ? '?' . http_build_query($query, '', '&') : '');
    }
}
