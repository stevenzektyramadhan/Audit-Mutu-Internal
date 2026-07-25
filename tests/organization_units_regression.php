#!/usr/bin/env php
<?php

$root = dirname(__DIR__);
$failures = [];
$checks = 0;

function m201_check($condition, $message)
{
    global $failures, $checks;
    $checks++;
    if (!$condition) {
        $failures[] = $message;
    }
}

function m201_source($root, $path)
{
    $contents = file_get_contents(
        $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path)
    );
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }

    return $contents;
}

class M201FakeLoader
{
    public function model($name)
    {
    }

    public function library($name)
    {
    }
}

class M201FakeDb
{
    public function trans_start()
    {
    }

    public function trans_complete()
    {
    }

    public function trans_status()
    {
        return TRUE;
    }
}

class M201FakeAuditLogger
{
    public $events = [];

    public function record(
        $event_type,
        $object_type,
        $object_id,
        $action,
        $before = NULL,
        $after = NULL,
        array $metadata = []
    ) {
        $this->events[] = compact(
            'event_type',
            'object_type',
            'object_id',
            'action',
            'before',
            'after',
            'metadata'
        );
        return count($this->events);
    }
}

class M201FakeOrganizationUnitModel
{
    public $records = [];
    private $next_id = 1;

    public function __construct()
    {
        $this->create([
            'parent_id' => NULL,
            'code' => 'UNIVERSITY',
            'name' => 'Universitas',
            'type' => 'university',
            'active' => 1,
            'metadata_json' => NULL,
        ]);
    }

    public function schema_ready()
    {
        return TRUE;
    }

    public function find($id)
    {
        $id = (int) $id;
        if (!isset($this->records[$id])) {
            return NULL;
        }

        return $this->with_parent(clone $this->records[$id]);
    }

    public function get_all()
    {
        return array_map(function ($row) {
            return $this->with_parent(clone $row);
        }, array_values($this->records));
    }

    public function get_all_plain()
    {
        return array_map(function ($row) {
            return clone $row;
        }, array_values($this->records));
    }

    public function get_children($id)
    {
        return array_values(array_filter($this->get_all_plain(), function ($row) use ($id) {
            return $row->parent_id !== NULL && (int) $row->parent_id === (int) $id;
        }));
    }

    public function code_exists($code, $except_id = NULL)
    {
        foreach ($this->records as $id => $row) {
            if ($except_id !== NULL && (int) $except_id === (int) $id) {
                continue;
            }
            if (strcasecmp($row->code, (string) $code) === 0) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function create($data)
    {
        $id = $this->next_id++;
        $row = (object) $data;
        $row->id = $id;
        $this->records[$id] = $row;
        return $id;
    }

    public function update($id, $data)
    {
        $id = (int) $id;
        if (!isset($this->records[$id])) {
            return FALSE;
        }
        foreach ($data as $field => $value) {
            $this->records[$id]->{$field} = $value;
        }
        return TRUE;
    }

    private function with_parent($row)
    {
        $parent = $row->parent_id !== NULL && isset($this->records[(int) $row->parent_id])
            ? $this->records[(int) $row->parent_id]
            : NULL;
        $row->parent_code = $parent ? $parent->code : NULL;
        $row->parent_name = $parent ? $parent->name : NULL;
        $row->parent_type = $parent ? $parent->type : NULL;
        return $row;
    }
}

class M201FakeCi
{
    public $load;
    public $db;
    public $Organization_unit_model;
    public $audit_logger;

    public function __construct()
    {
        $this->load = new M201FakeLoader();
        $this->db = new M201FakeDb();
        $this->Organization_unit_model = new M201FakeOrganizationUnitModel();
        $this->audit_logger = new M201FakeAuditLogger();
    }
}

$m201_fake_ci = new M201FakeCi();

function &get_instance()
{
    global $m201_fake_ci;
    return $m201_fake_ci;
}

if (!defined('BASEPATH')) {
    define('BASEPATH', $root . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
}

require_once $root . DIRECTORY_SEPARATOR . 'application'
    . DIRECTORY_SEPARATOR . 'services'
    . DIRECTORY_SEPARATOR . 'Organization_unit_service.php';

$service = new Organization_unit_service();
m201_check($service->schema_ready() === TRUE, 'Service tidak mendeteksi schema unit.');

$faculty = $service->create([
    'parent_id' => 1,
    'code' => 'ft',
    'name' => 'Fakultas Teknik',
    'type' => 'faculty',
    'active' => 1,
]);
m201_check($faculty['success'] === TRUE, 'Fakultas valid gagal dibuat.');
m201_check($m201_fake_ci->Organization_unit_model->find(2)->code === 'FT', 'Kode tidak dinormalisasi uppercase.');

$invalid_program = $service->create([
    'parent_id' => 1,
    'code' => 'IF-BAD',
    'name' => 'Program Studi Tidak Valid',
    'type' => 'study_program',
    'active' => 1,
]);
m201_check($invalid_program['success'] === FALSE, 'Program studi boleh langsung di bawah universitas.');

$program = $service->create([
    'parent_id' => 2,
    'code' => 'IF-S1',
    'name' => 'Informatika',
    'type' => 'study_program',
    'active' => 1,
]);
m201_check($program['success'] === TRUE, 'Program studi di bawah fakultas gagal dibuat.');

$duplicate = $service->create([
    'parent_id' => 2,
    'code' => 'if-s1',
    'name' => 'Duplikat Informatika',
    'type' => 'study_program',
    'active' => 1,
]);
m201_check($duplicate['success'] === FALSE, 'Kode duplikat case-insensitive tidak ditolak.');

$bad_code = $service->create([
    'parent_id' => 1,
    'code' => 'BAD CODE',
    'name' => 'Kode Tidak Valid',
    'type' => 'bureau',
    'active' => 1,
]);
m201_check($bad_code['success'] === FALSE, 'Kode dengan spasi tidak ditolak.');

$orphan_bureau = $service->create([
    'parent_id' => NULL,
    'code' => 'BAUK',
    'name' => 'Biro Administrasi',
    'type' => 'bureau',
    'active' => 1,
]);
m201_check($orphan_bureau['success'] === FALSE, 'Unit non-root tanpa induk tidak ditolak.');

$deactivate_parent = $service->toggle_active(2);
m201_check($deactivate_parent['success'] === FALSE, 'Induk dengan turunan aktif dapat dinonaktifkan.');

$deactivate_program = $service->toggle_active(3);
m201_check($deactivate_program['success'] === TRUE, 'Leaf aktif gagal dinonaktifkan.');
m201_check((int) $m201_fake_ci->Organization_unit_model->find(3)->active === 0, 'Status leaf tidak berubah.');

$deactivate_faculty = $service->toggle_active(2);
m201_check($deactivate_faculty['success'] === TRUE, 'Induk tanpa turunan aktif gagal dinonaktifkan.');

$activate_program_early = $service->toggle_active(3);
m201_check($activate_program_early['success'] === FALSE, 'Turunan dapat aktif saat induk nonaktif.');

$activate_faculty = $service->toggle_active(2);
$activate_program = $service->toggle_active(3);
m201_check($activate_faculty['success'] === TRUE, 'Fakultas gagal diaktifkan kembali.');
m201_check($activate_program['success'] === TRUE, 'Program studi gagal diaktifkan setelah induk aktif.');

$incompatible_type = $service->update(2, [
    'parent_id' => 1,
    'code' => 'FT',
    'name' => 'Fakultas Teknik',
    'type' => 'bureau',
]);
m201_check($incompatible_type['success'] === FALSE, 'Jenis induk dapat diubah hingga merusak relasi program studi.');

$unit_parent = $service->create([
    'parent_id' => 1,
    'code' => 'UNIT-A',
    'name' => 'Unit A',
    'type' => 'unit',
    'active' => 1,
]);
$unit_child = $service->create([
    'parent_id' => $unit_parent['id'],
    'code' => 'UNIT-B',
    'name' => 'Unit B',
    'type' => 'unit',
    'active' => 1,
]);
m201_check($unit_parent['success'] === TRUE && $unit_child['success'] === TRUE, 'Hierarki unit bertingkat gagal dibuat.');

$cycle = $service->update($unit_parent['id'], [
    'parent_id' => $unit_child['id'],
    'code' => 'UNIT-A',
    'name' => 'Unit A',
    'type' => 'unit',
]);
m201_check($cycle['success'] === FALSE, 'Siklus pada hierarki unit bertingkat tidak ditolak.');

$tree = $service->get_tree();
$depth_by_code = [];
foreach ($tree as $unit) {
    $depth_by_code[$unit->code] = (int) $unit->tree_depth;
}
m201_check(isset($depth_by_code['UNIVERSITY']) && $depth_by_code['UNIVERSITY'] === 0, 'Root tree tidak berada di depth 0.');
m201_check(isset($depth_by_code['FT']) && $depth_by_code['FT'] === 1, 'Fakultas tidak berada di depth 1.');
m201_check(isset($depth_by_code['IF-S1']) && $depth_by_code['IF-S1'] === 2, 'Program studi tidak berada di depth 2.');

$parent_options = $service->get_parent_options(2);
$parent_option_ids = array_map(function ($unit) {
    return (int) $unit->id;
}, $parent_options);
m201_check(!in_array(2, $parent_option_ids, TRUE), 'Unit sendiri tersedia sebagai opsi induk.');
m201_check(!in_array(3, $parent_option_ids, TRUE), 'Turunan tersedia sebagai opsi induk.');
m201_check(count($m201_fake_ci->audit_logger->events) >= 8, 'Mutasi unit belum menghasilkan audit event.');

$migration = m201_source($root, 'migrations/015_create_organization_units.sql');
$schema = m201_source($root, 'database_schema.sql');
$model = m201_source($root, 'application/models/Organization_unit_model.php');
$controller = m201_source($root, 'application/controllers/Organization_units.php');
$routes = m201_source($root, 'application/config/routes.php');
$policy = m201_source($root, 'application/libraries/Authorization_policy.php');
$index_view = m201_source($root, 'application/views/lpmpi/organization_units/index.php');
$form_view = m201_source($root, 'application/views/lpmpi/organization_units/form.php');

foreach ([$migration, $schema] as $sql) {
    m201_check(strpos($sql, 'CREATE TABLE IF NOT EXISTS `organization_units`') !== FALSE, 'Schema organization_units belum tersedia.');
    m201_check(strpos($sql, 'UNIQUE KEY `uq_organization_units_code`') !== FALSE, 'Unique key kode unit belum tersedia.');
    m201_check(strpos($sql, 'FOREIGN KEY (`parent_id`) REFERENCES `organization_units` (`id`)') !== FALSE, 'Self foreign key hierarchy belum tersedia.');
    m201_check(strpos($sql, "'UNIVERSITY', 'Universitas', 'university'") !== FALSE, 'Seed root universitas belum tersedia.');
}

m201_check(preg_match('/public\s+function\s+delete\s*\(/i', $model) !== 1, 'Model tidak boleh menyediakan hard delete.');
m201_check(preg_match('/function\s+delete\s*\(/i', $controller) !== 1, 'Controller tidak boleh menyediakan delete.');
m201_check(strpos($routes, 'organization-units/toggle-active') !== FALSE, 'Route perubahan status belum tersedia.');
m201_check(strpos($routes, 'organization-units/delete') === FALSE, 'Route hard delete tidak boleh tersedia.');
m201_check(strpos($policy, 'CAP_ORGANIZATION_UNITS_MANAGE') !== FALSE, 'Capability master organisasi belum tersedia.');
m201_check(strpos($controller, 'CAP_ORGANIZATION_UNITS_MANAGE') !== FALSE, 'Controller belum memakai capability khusus.');
m201_check(strpos($controller, "method(TRUE) !== 'POST'") !== FALSE, 'Mutasi controller belum dibatasi POST.');
m201_check(strpos($index_view, 'ami_e($row->name)') !== FALSE, 'Nama unit pada daftar belum di-escape.');
m201_check(strpos($form_view, 'ami_json($allowed_parent_types)') !== FALSE, 'Data script hierarchy belum memakai encoder JSON.');
m201_check(strpos($form_view, 'ami_csp_nonce()') !== FALSE, 'Script form belum memakai CSP nonce.');

if (!empty($failures)) {
    foreach ($failures as $failure) {
        fwrite(STDERR, '[FAIL] ' . $failure . PHP_EOL);
    }
    exit(1);
}

fwrite(STDOUT, '[PASS] M2-01 organization units regression (' . $checks . " checks)\n");
exit(0);
