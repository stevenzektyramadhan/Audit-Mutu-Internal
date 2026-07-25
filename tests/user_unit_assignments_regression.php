#!/usr/bin/env php
<?php

$root = dirname(__DIR__);
$failures = [];
$checks = 0;

function m202_check($condition, $message)
{
    global $failures, $checks;
    $checks++;
    if (!$condition) {
        $failures[] = $message;
    }
}

function m202_source($root, $path)
{
    $contents = file_get_contents(
        $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path)
    );
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }
    return $contents;
}

class M202FakeLoader
{
    public function model($name)
    {
    }

    public function library($name)
    {
    }
}

class M202FakeDb
{
    public $in_transaction = FALSE;

    public function trans_begin()
    {
        $this->in_transaction = TRUE;
    }

    public function trans_commit()
    {
        $this->in_transaction = FALSE;
    }

    public function trans_rollback()
    {
        $this->in_transaction = FALSE;
    }

    public function trans_status()
    {
        return TRUE;
    }
}

class M202FakeUserModel
{
    public $records;

    public function __construct()
    {
        $this->records = [
            10 => (object) [
                'id' => 10,
                'nama' => 'Auditee M2',
                'email' => 'auditee-m2@example.test',
                'role' => 'auditee',
                'is_active' => 1,
            ],
        ];
    }

    public function find($id)
    {
        return isset($this->records[(int) $id])
            ? clone $this->records[(int) $id]
            : NULL;
    }
}

class M202FakeOrganizationUnitModel
{
    public $records;

    public function __construct()
    {
        $this->records = [
            1 => (object) [
                'id' => 1,
                'parent_id' => NULL,
                'code' => 'UNIVERSITY',
                'name' => 'Universitas',
                'type' => 'university',
                'active' => 1,
            ],
            2 => (object) [
                'id' => 2,
                'parent_id' => 1,
                'code' => 'OLD-UNIT',
                'name' => 'Unit Nonaktif',
                'type' => 'unit',
                'active' => 0,
            ],
        ];
    }

    public function find($id)
    {
        return isset($this->records[(int) $id])
            ? clone $this->records[(int) $id]
            : NULL;
    }

    public function get_all()
    {
        return array_map(function ($record) {
            return clone $record;
        }, array_values($this->records));
    }
}

class M202FakeAssignmentModel
{
    public $records = [];
    private $next_id = 1;

    public function schema_ready()
    {
        return TRUE;
    }

    public function lock_user($id)
    {
        return (int) $id === 10 ? (object) ['id' => 10] : NULL;
    }

    public function get_for_user($user_id)
    {
        return array_values(array_filter($this->records, function ($row) use ($user_id) {
            return (int) $row->user_id === (int) $user_id;
        }));
    }

    public function find($id)
    {
        return isset($this->records[(int) $id])
            ? clone $this->records[(int) $id]
            : NULL;
    }

    public function has_overlap(
        $user_id,
        $unit_id,
        $position_code,
        $valid_from,
        $valid_until
    ) {
        foreach ($this->records as $row) {
            if ((int) $row->user_id === (int) $user_id
                && (int) $row->organization_unit_id === (int) $unit_id
                && (string) $row->position_code === (string) $position_code
                && $this->overlaps($row, $valid_from, $valid_until)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function has_primary_overlap($user_id, $valid_from, $valid_until)
    {
        foreach ($this->records as $row) {
            if ((int) $row->user_id === (int) $user_id
                && (int) $row->is_primary === 1
                && $this->overlaps($row, $valid_from, $valid_until)) {
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

    public function set_valid_until($id, $valid_until)
    {
        if (!isset($this->records[(int) $id])) {
            return FALSE;
        }
        $this->records[(int) $id]->valid_until = (string) $valid_until;
        return TRUE;
    }

    private function overlaps($row, $valid_from, $valid_until)
    {
        $existing_end = $row->valid_until === NULL ? '9999-12-31' : $row->valid_until;
        $new_end = $valid_until === NULL ? '9999-12-31' : $valid_until;
        return (string) $row->valid_from <= $new_end
            && (string) $valid_from <= $existing_end;
    }
}

class M202FakeAuditLogger
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

class M202FakeCi
{
    public $load;
    public $db;
    public $User_unit_assignment_model;
    public $Organization_unit_model;
    public $User_model;
    public $audit_logger;

    public function __construct()
    {
        $this->load = new M202FakeLoader();
        $this->db = new M202FakeDb();
        $this->User_unit_assignment_model = new M202FakeAssignmentModel();
        $this->Organization_unit_model = new M202FakeOrganizationUnitModel();
        $this->User_model = new M202FakeUserModel();
        $this->audit_logger = new M202FakeAuditLogger();
    }
}

$m202_fake_ci = new M202FakeCi();

function &get_instance()
{
    global $m202_fake_ci;
    return $m202_fake_ci;
}

if (!defined('BASEPATH')) {
    define('BASEPATH', $root . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
}

require_once $root . DIRECTORY_SEPARATOR . 'application'
    . DIRECTORY_SEPARATOR . 'services'
    . DIRECTORY_SEPARATOR . 'User_unit_assignment_service.php';

$service = new User_unit_assignment_service();
$today = date('Y-m-d');
$primary_end = date('Y-m-d', strtotime('+10 days'));
$next_start = date('Y-m-d', strtotime('+11 days'));

m202_check($service->schema_ready() === TRUE, 'Service tidak mendeteksi schema assignment.');

$primary = $service->create(10, [
    'organization_unit_id' => 1,
    'position_code' => 'kaprodi',
    'valid_from' => $today,
    'valid_until' => $primary_end,
    'is_primary' => 1,
]);
m202_check($primary['success'] === TRUE, 'Assignment primary valid gagal dibuat.');
m202_check(
    $m202_fake_ci->User_unit_assignment_model->find($primary['id'])->position_code === 'KAPRODI',
    'Kode jabatan tidak dinormalisasi uppercase.'
);

$secondary = $service->create(10, [
    'organization_unit_id' => 1,
    'position_code' => 'auditor',
    'valid_from' => $today,
    'valid_until' => NULL,
    'is_primary' => 0,
]);
m202_check($secondary['success'] === TRUE, 'Assignment tambahan yang bertumpuk gagal dibuat.');

$duplicate = $service->create(10, [
    'organization_unit_id' => 1,
    'position_code' => 'KAPRODI',
    'valid_from' => date('Y-m-d', strtotime('+2 days')),
    'valid_until' => date('Y-m-d', strtotime('+3 days')),
    'is_primary' => 0,
]);
m202_check($duplicate['success'] === FALSE, 'Periode unit/jabatan yang sama boleh bertumpuk.');

$primary_overlap = $service->create(10, [
    'organization_unit_id' => 1,
    'position_code' => 'DEKAN',
    'valid_from' => date('Y-m-d', strtotime('+2 days')),
    'valid_until' => date('Y-m-d', strtotime('+3 days')),
    'is_primary' => 1,
]);
m202_check($primary_overlap['success'] === FALSE, 'Assignment primary yang bertumpuk tidak ditolak.');

$next_primary = $service->create(10, [
    'organization_unit_id' => 1,
    'position_code' => 'DEKAN',
    'valid_from' => $next_start,
    'valid_until' => NULL,
    'is_primary' => 1,
]);
m202_check($next_primary['success'] === TRUE, 'Primary berurutan tanpa overlap gagal dibuat.');

$inactive_unit = $service->create(10, [
    'organization_unit_id' => 2,
    'position_code' => 'STAFF',
    'valid_from' => $today,
    'valid_until' => NULL,
    'is_primary' => 0,
]);
m202_check($inactive_unit['success'] === FALSE, 'Assignment ke unit nonaktif tidak ditolak.');

$invalid_position = $service->create(10, [
    'organization_unit_id' => 1,
    'position_code' => 'bad position',
    'valid_from' => $today,
    'valid_until' => NULL,
    'is_primary' => 0,
]);
m202_check($invalid_position['success'] === FALSE, 'Kode jabatan dengan spasi tidak ditolak.');

$invalid_period = $service->create(10, [
    'organization_unit_id' => 1,
    'position_code' => 'STAFF',
    'valid_from' => $primary_end,
    'valid_until' => $today,
    'is_primary' => 0,
]);
m202_check($invalid_period['success'] === FALSE, 'Tanggal akhir sebelum tanggal mulai tidak ditolak.');

$before_count = count($m202_fake_ci->User_unit_assignment_model->records);
$ended = $service->end($primary['id'], date('Y-m-d', strtotime('+3 days')));
m202_check($ended['success'] === TRUE, 'Assignment aktif gagal diakhiri.');
m202_check(
    count($m202_fake_ci->User_unit_assignment_model->records) === $before_count,
    'Mengakhiri assignment menghapus riwayat.'
);
m202_check(
    $m202_fake_ci->User_unit_assignment_model->find($primary['id'])->valid_until
        === date('Y-m-d', strtotime('+3 days')),
    'Tanggal berakhir assignment tidak diperbarui.'
);

$event_types = array_map(function ($event) {
    return $event['event_type'];
}, $m202_fake_ci->audit_logger->events);
m202_check(
    in_array('user_unit_assignment_created', $event_types, TRUE),
    'Pembuatan assignment belum menghasilkan audit event.'
);
m202_check(
    in_array('user_unit_assignment_ended', $event_types, TRUE),
    'Pengakhiran assignment belum menghasilkan audit event.'
);

$migration = m202_source($root, 'migrations/016_create_user_unit_assignments.sql');
$schema = m202_source($root, 'database_schema.sql');
$model = m202_source($root, 'application/models/User_unit_assignment_model.php');
$controller = m202_source($root, 'application/controllers/User_unit_assignments.php');
$routes = m202_source($root, 'application/config/routes.php');
$policy = m202_source($root, 'application/libraries/Authorization_policy.php');
$user_model = m202_source($root, 'application/models/User_model.php');
$user_service = m202_source($root, 'application/services/User_service.php');

foreach ([$migration, $schema] as $sql) {
    m202_check(
        strpos($sql, 'CREATE TABLE IF NOT EXISTS `user_unit_assignments`') !== FALSE,
        'Schema user_unit_assignments belum tersedia.'
    );
    m202_check(
        strpos($sql, 'CONSTRAINT `fk_user_unit_assignment_user`') !== FALSE
            && strpos($sql, 'ON UPDATE RESTRICT ON DELETE RESTRICT') !== FALSE,
        'Riwayat assignment belum dilindungi foreign key RESTRICT.'
    );
    m202_check(
        strpos($sql, 'CHECK (`valid_until` IS NULL OR `valid_until` >= `valid_from`)') !== FALSE,
        'Constraint periode assignment belum tersedia.'
    );
}

m202_check(
    preg_match('/public\s+function\s+delete\s*\(/i', $model) !== 1,
    'Model assignment tidak boleh menyediakan hard delete.'
);
m202_check(
    preg_match('/function\s+delete\s*\(/i', $controller) !== 1,
    'Controller assignment tidak boleh menyediakan hard delete.'
);
m202_check(
    strpos($routes, 'user-unit-assignments/end/(:num)') !== FALSE
        && strpos($routes, 'user-unit-assignments/delete') === FALSE,
    'Route assignment harus memakai end tanpa hard delete.'
);
m202_check(
    strpos($policy, 'activeOrganizationAssignments') !== FALSE
        && strpos($policy, 'canAccessOrganizationUnit') !== FALSE,
    'Policy belum memakai assignment aktif sebagai sumber scope organisasi.'
);
m202_check(
    strpos($model, "->where('users.is_active', 1)") !== FALSE
        && strpos($model, "->where('organization_units.active', 1)") !== FALSE,
    'Assignment efektif belum mensyaratkan user dan unit aktif.'
);
m202_check(
    strpos($model, "->where('user_unit_assignments.valid_from <=',") !== FALSE
        && strpos($model, "'user_unit_assignments.valid_until IS NULL'") !== FALSE
        && strpos($model, "->or_where('user_unit_assignments.valid_until >=',") !== FALSE,
    'Query assignment aktif belum membatasi periode inklusif.'
);
m202_check(
    strpos($policy, 'activeOrganizationUnitIds') !== FALSE
        && strpos($policy, 'parent_id') === FALSE,
    'Policy M2-02 tidak boleh mengarang pewarisan scope parent/descendant.'
);
m202_check(
    strpos($user_model, 'function has_unit_assignment_history(') !== FALSE
        && substr_count($user_service, 'has_unit_assignment_history(') >= 2,
    'Penghapusan user belum dilindungi oleh riwayat assignment.'
);
m202_check(
    strpos($controller, "method(TRUE) !== 'POST'") !== FALSE,
    'Mutasi assignment belum dibatasi POST.'
);

if (!empty($failures)) {
    foreach ($failures as $failure) {
        fwrite(STDERR, '[FAIL] ' . $failure . PHP_EOL);
    }
    exit(1);
}

fwrite(STDOUT, '[PASS] M2-02 user unit assignments regression (' . $checks . " checks)\n");
exit(0);
