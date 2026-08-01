<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Legacy_ami_archive extends Admin_Lpmpi_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Legacy_ami_archive_model');
    }

    public function index()
    {
        $this->load->view('lpmpi/legacy_ami_archive/index', [
            'title' => 'Arsip AMI Legacy',
            'page_title' => 'Arsip AMI Legacy',
            'page_subtitle' => 'Beranda / Insights / Arsip AMI Legacy',
            'active_menu' => 'legacy_ami_archive',
            'preflight' => $this->Legacy_ami_archive_model->preflight(),
            'runs' => $this->Legacy_ami_archive_model->runs(),
        ]);
    }

    public function preflight()
    {
        $this->load->view('lpmpi/legacy_ami_archive/preflight', [
            'title' => 'Preflight Arsip AMI Legacy',
            'page_title' => 'Preflight Arsip AMI Legacy',
            'page_subtitle' => 'Beranda / Arsip AMI Legacy / Preflight',
            'active_menu' => 'legacy_ami_archive',
            'preflight' => $this->Legacy_ami_archive_model->preflight(),
        ]);
    }

    public function run($id)
    {
        $run = $this->Legacy_ami_archive_model->run((int) $id);
        if (!$run) { show_error('Run arsip AMI legacy tidak ditemukan.', 404, 'Not Found'); return; }
        $this->load->view('lpmpi/legacy_ami_archive/run', [
            'title' => 'Detail Run Arsip AMI Legacy',
            'page_title' => 'Detail Run Arsip AMI Legacy',
            'page_subtitle' => 'Beranda / Arsip AMI Legacy / Run',
            'active_menu' => 'legacy_ami_archive',
            'run' => $run,
            'tasks' => $this->Legacy_ami_archive_model->tasks((int) $id),
            'issues' => $this->Legacy_ami_archive_model->issues((int) $id),
        ]);
    }

    public function task($id)
    {
        $task = $this->Legacy_ami_archive_model->task((int) $id);
        if (!$task) { show_error('Tugas arsip AMI legacy tidak ditemukan.', 404, 'Not Found'); return; }
        $run = $this->Legacy_ami_archive_model->run((int) $task->run_id);
        $this->load->view('lpmpi/legacy_ami_archive/task', [
            'title' => 'Detail Tugas Arsip AMI Legacy',
            'page_title' => 'Detail Tugas Arsip AMI Legacy',
            'page_subtitle' => 'Beranda / Arsip AMI Legacy / Tugas',
            'active_menu' => 'legacy_ami_archive',
            'run' => $run,
            'task' => $task,
            'answers' => $this->Legacy_ami_archive_model->answers((int) $id),
        ]);
    }

    public function issues()
    {
        $this->load->view('lpmpi/legacy_ami_archive/issues', [
            'title' => 'Rekonsiliasi Arsip AMI Legacy',
            'page_title' => 'Rekonsiliasi Arsip AMI Legacy',
            'page_subtitle' => 'Beranda / Arsip AMI Legacy / Rekonsiliasi',
            'active_menu' => 'legacy_ami_archive',
            'issues' => $this->Legacy_ami_archive_model->issues(),
        ]);
    }
}
