<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Spmi_ppepp_recap extends Admin_Lpmpi_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Spmi_ppepp_recap_model');
    }

    public function index()
    {
        redirect('lpmpi/spmi-dashboard');
    }
}
