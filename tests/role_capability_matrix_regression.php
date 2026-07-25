#!/usr/bin/env php
<?php

$root = dirname(__DIR__);
$failures = [];
$checks = 0;

function m203_check($condition, $message)
{
    global $failures, $checks;
    $checks++;
    if (!$condition) {
        $failures[] = $message;
    }
}

function m203_source($root, $path)
{
    $source = file_get_contents(
        $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path)
    );
    if ($source === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }
    return $source;
}

class M203FakeLoader
{
    public function model($name)
    {
    }

    public function library($name)
    {
    }
}

class M203FakeUserModel
{
    private $users;

    public function __construct()
    {
        $this->users = [
            1 => (object) ['id' => 1, 'role' => 'super_admin', 'is_active' => 1],
            2 => (object) ['id' => 2, 'role' => 'admin_lpmpi', 'is_active' => 1],
            3 => (object) ['id' => 3, 'role' => 'auditor', 'is_active' => 1],
            4 => (object) ['id' => 4, 'role' => 'auditee', 'is_active' => 1],
            5 => (object) ['id' => 5, 'role' => 'admin_lpmpi', 'is_active' => 0],
        ];
    }

    public function find($id)
    {
        return isset($this->users[(int) $id])
            ? clone $this->users[(int) $id]
            : NULL;
    }
}

class M203FakeAssignmentModel
{
    public function schema_ready()
    {
        return TRUE;
    }

    public function get_active_for_user($user_id, $on_date)
    {
        if ((string) $on_date !== '2026-07-25') {
            return [];
        }

        $unit_by_user = [
            2 => 10,
            3 => 10,
            4 => 20,
            5 => 10,
        ];
        if (!isset($unit_by_user[(int) $user_id])) {
            return [];
        }

        return [
            (object) [
                'user_id' => (int) $user_id,
                'organization_unit_id' => $unit_by_user[(int) $user_id],
                'position_code' => 'MEMBER',
                'is_primary' => 1,
            ],
        ];
    }
}

class M203FakeOrganizationUnitModel
{
    public function find($id)
    {
        $units = [
            10 => (object) ['id' => 10, 'active' => 1],
            20 => (object) ['id' => 20, 'active' => 1],
            30 => (object) ['id' => 30, 'active' => 0],
        ];
        return isset($units[(int) $id]) ? clone $units[(int) $id] : NULL;
    }
}

class M203FakeCi
{
    public $load;
    public $User_model;
    public $Jawaban_model;
    public $Tugas_audit_model;
    public $User_unit_assignment_model;
    public $Organization_unit_model;
    public $auth_security;

    public function __construct()
    {
        $this->load = new M203FakeLoader();
        $this->User_model = new M203FakeUserModel();
        $this->Jawaban_model = new stdClass();
        $this->Tugas_audit_model = new stdClass();
        $this->User_unit_assignment_model = new M203FakeAssignmentModel();
        $this->Organization_unit_model = new M203FakeOrganizationUnitModel();
        $this->auth_security = new stdClass();
    }
}

$m203_fake_ci = new M203FakeCi();

function &get_instance()
{
    global $m203_fake_ci;
    return $m203_fake_ci;
}

if (!defined('BASEPATH')) {
    define('BASEPATH', $root . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
}

require_once $root . DIRECTORY_SEPARATOR . 'application'
    . DIRECTORY_SEPARATOR . 'libraries'
    . DIRECTORY_SEPARATOR . 'Authorization_policy.php';

$policy = new Authorization_policy();
$matrix = $policy->capabilityMatrix();

$minimum_matrix = [
    Authorization_policy::CAP_SPMI_VERSION_MANAGE => ['super_admin', 'admin_lpmpi'],
    Authorization_policy::CAP_SPMI_STANDARD_MANAGE => ['super_admin', 'admin_lpmpi'],
    Authorization_policy::CAP_SPMI_INDICATOR_MANAGE => ['super_admin', 'admin_lpmpi'],
    Authorization_policy::CAP_SPMI_IMPORT => ['super_admin', 'admin_lpmpi'],
    Authorization_policy::CAP_AUDIT_PERIOD_MANAGE => ['super_admin', 'admin_lpmpi'],
    Authorization_policy::CAP_AUDIT_PACKAGE_MANAGE => ['super_admin', 'admin_lpmpi'],
    Authorization_policy::CAP_AUDIT_ASSIGNMENT_MANAGE => ['super_admin', 'admin_lpmpi'],
    Authorization_policy::CAP_AUDIT_SUBMISSION_FILL => ['auditee'],
    Authorization_policy::CAP_AUDIT_SUBMISSION_SUBMIT => ['auditee'],
    Authorization_policy::CAP_AUDIT_ASSESSMENT_FILL => ['auditor'],
    Authorization_policy::CAP_AUDIT_ASSESSMENT_SUBMIT => ['auditor'],
    Authorization_policy::CAP_AUDIT_REPORT_VIEW => ['super_admin', 'admin_lpmpi'],
    Authorization_policy::CAP_AUDIT_REPORT_EXPORT => ['super_admin', 'admin_lpmpi'],
    Authorization_policy::CAP_RTM_MANAGE => [],
    Authorization_policy::CAP_RTM_FINALIZE => [],
    Authorization_policy::CAP_FOLLOWUP_FILL => [],
    Authorization_policy::CAP_FOLLOWUP_VERIFY => [],
    Authorization_policy::CAP_SECURITY_AUDITLOG_VIEW => ['super_admin'],
];

foreach ($minimum_matrix as $capability => $expected_roles) {
    m203_check(isset($matrix[$capability]), 'Capability minimum tidak terdaftar: ' . $capability);
    m203_check(
        isset($matrix[$capability]) && $matrix[$capability] === $expected_roles,
        'Role matrix tidak sesuai untuk ' . $capability
    );

    foreach ([
        1 => 'super_admin',
        2 => 'admin_lpmpi',
        3 => 'auditor',
        4 => 'auditee',
    ] as $user_id => $role) {
        m203_check(
            $policy->allows($user_id, $capability)
                === in_array($role, $expected_roles, TRUE),
            'Evaluasi role salah untuk ' . $role . ' pada ' . $capability
        );
    }
}

m203_check(
    $policy->allows(5, Authorization_policy::CAP_SPMI_STANDARD_MANAGE) === FALSE,
    'User nonaktif tidak boleh memperoleh capability.'
);
m203_check($policy->allows(999, 'unknown.capability') === FALSE, 'Capability tidak dikenal harus deny.');
m203_check(
    $policy->capabilityScope(Authorization_policy::CAP_USERS_MANAGE)
        === Authorization_policy::SCOPE_GLOBAL,
    'Capability user global memiliki mode scope yang salah.'
);
m203_check(
    $policy->capabilityScope(Authorization_policy::CAP_AUDIT_ASSESSMENT_FILL)
        === Authorization_policy::SCOPE_ORGANIZATION,
    'Capability assessment harus dapat dikombinasikan dengan scope organisasi.'
);
m203_check($policy->capabilityScope('unknown.capability') === NULL, 'Scope capability tidak dikenal harus NULL.');

$date = '2026-07-25';
m203_check(
    $policy->allowsInOrganizationUnit(
        1,
        Authorization_policy::CAP_SPMI_STANDARD_MANAGE,
        20,
        $date
    ) === TRUE,
    'Super Admin dengan tanggung jawab global harus dapat bekerja pada unit aktif.'
);
m203_check(
    $policy->allowsInOrganizationUnit(
        1,
        Authorization_policy::CAP_SPMI_STANDARD_MANAGE,
        999,
        $date
    ) === FALSE,
    'Scope organisasi harus menolak unit yang tidak ada.'
);
m203_check(
    $policy->allowsInOrganizationUnit(
        2,
        Authorization_policy::CAP_SPMI_STANDARD_MANAGE,
        10,
        $date
    ) === TRUE,
    'Admin LPMPI harus diterima pada direct active membership.'
);
m203_check(
    $policy->allowsInOrganizationUnit(
        2,
        Authorization_policy::CAP_SPMI_STANDARD_MANAGE,
        20,
        $date
    ) === FALSE,
    'Admin LPMPI tidak boleh mengakses unit tanpa membership.'
);
m203_check(
    $policy->allowsInOrganizationUnit(
        2,
        Authorization_policy::CAP_SPMI_STANDARD_MANAGE,
        30,
        $date
    ) === FALSE,
    'Unit nonaktif harus ditolak.'
);
m203_check(
    $policy->allowsInOrganizationUnit(
        3,
        Authorization_policy::CAP_AUDIT_ASSESSMENT_FILL,
        10,
        $date
    ) === TRUE,
    'Auditor dengan membership aktif harus lolos capability + unit scope.'
);
m203_check(
    $policy->allowsInOrganizationUnit(
        3,
        Authorization_policy::CAP_AUDIT_SUBMISSION_FILL,
        10,
        $date
    ) === FALSE,
    'Membership unit tidak boleh memberikan capability role lain.'
);
m203_check(
    $policy->allowsInOrganizationUnit(
        4,
        Authorization_policy::CAP_AUDIT_SUBMISSION_FILL,
        10,
        $date
    ) === FALSE,
    'Direct membership tidak boleh diwariskan ke unit lain.'
);
m203_check(
    $policy->allowsInOrganizationUnit(
        4,
        Authorization_policy::CAP_AUDIT_SUBMISSION_FILL,
        20,
        $date
    ) === TRUE,
    'Auditee harus diterima pada direct active membership sendiri.'
);
m203_check(
    $policy->allowsInOrganizationUnit(
        2,
        Authorization_policy::CAP_RTM_MANAGE,
        10,
        $date
    ) === FALSE,
    'RTM tanpa keputusan final harus tetap deny meskipun membership cocok.'
);
m203_check(
    $policy->allowsInAnyOrganizationUnit(
        2,
        Authorization_policy::CAP_AUDIT_REPORT_VIEW,
        $date
    ) === TRUE,
    'Admin dengan membership aktif harus lolos any-unit scope.'
);
m203_check(
    $policy->allowsInAnyOrganizationUnit(
        2,
        Authorization_policy::CAP_AUDIT_REPORT_VIEW,
        '2030-01-01'
    ) === FALSE,
    'Assignment di luar masa berlaku tidak boleh memberi scope.'
);
m203_check(
    $policy->allowsInOrganizationUnit(
        1,
        Authorization_policy::CAP_SECURITY_AUDITLOG_VIEW,
        999,
        $date
    ) === TRUE,
    'Capability global tidak boleh bergantung pada unit request.'
);

$controller_capabilities = [
    'application/controllers/Standar.php' => ['CAP_SPMI_STANDARD_MANAGE'],
    'application/controllers/Spmi_versions.php' => ['CAP_SPMI_VERSION_MANAGE'],
    'application/controllers/Spmi_standards.php' => ['CAP_SPMI_STANDARD_MANAGE'],
    'application/controllers/Pertanyaan.php' => [
        'CAP_SPMI_INDICATOR_MANAGE',
        'CAP_SPMI_IMPORT',
    ],
    'application/controllers/Periode.php' => ['CAP_AUDIT_PERIOD_MANAGE'],
    'application/controllers/Tugas_audit.php' => ['CAP_AUDIT_ASSIGNMENT_MANAGE'],
    'application/controllers/lpmpi/Instrumen.php' => ['CAP_AUDIT_PACKAGE_MANAGE'],
    'application/controllers/lpmpi/Penetapan.php' => ['CAP_AUDIT_PACKAGE_MANAGE'],
    'application/controllers/lpmpi/Penugasan.php' => ['CAP_AUDIT_ASSIGNMENT_MANAGE'],
    'application/controllers/lpmpi/Laporan.php' => [
        'CAP_AUDIT_REPORT_VIEW',
        'CAP_AUDIT_REPORT_EXPORT',
    ],
    'application/controllers/Auditee.php' => [
        'CAP_AUDIT_SUBMISSION_FILL',
        'CAP_AUDIT_SUBMISSION_SUBMIT',
    ],
    'application/controllers/auditee/Tugas.php' => [
        'CAP_AUDIT_SUBMISSION_SUBMIT',
    ],
    'application/controllers/Auditor.php' => [
        'CAP_AUDIT_ASSESSMENT_FILL',
        'CAP_AUDIT_ASSESSMENT_SUBMIT',
    ],
    'application/controllers/Account.php' => ['CAP_ACCOUNT_SELF'],
    'application/controllers/Dashboard.php' => ['CAP_DASHBOARD_VIEW'],
    'application/controllers/Profil.php' => ['CAP_PROFILE_VIEW', 'CAP_PROFILE_MANAGE'],
    'application/controllers/Users.php' => ['CAP_USERS_MANAGE'],
    'application/controllers/lpmpi/Akun.php' => ['CAP_PARTICIPANT_ACCOUNTS_MANAGE'],
    'application/controllers/Organization_units.php' => ['CAP_ORGANIZATION_UNITS_MANAGE'],
    'application/controllers/User_unit_assignments.php' => [
        'CAP_USER_UNIT_ASSIGNMENTS_MANAGE',
    ],
];

foreach ($controller_capabilities as $path => $constants) {
    $source = m203_source($root, $path);
    foreach ($constants as $constant) {
        m203_check(
            strpos($source, $constant) !== FALSE,
            $path . ' belum memakai capability ' . $constant
        );
    }
}

$core = m203_source($root, 'application/core/MY_Controller.php');
$guard = m203_source($root, 'application/libraries/Auth_guard.php');
$sidebar = m203_source($root, 'application/views/layouts/sidebar.php');
$all_application = m203_source(
    $root,
    'application/libraries/Authorization_policy.php'
) . $core;
foreach (array_keys($controller_capabilities) as $path) {
    $all_application .= m203_source($root, $path);
}
m203_check(strpos($all_application, 'CAP_SPMI_MANAGE') === FALSE, 'Capability SPMI coarse masih dipakai.');
m203_check(strpos($all_application, 'CAP_ASSIGNMENTS_MANAGE') === FALSE, 'Capability assignment coarse masih dipakai.');
m203_check(strpos($all_application, 'CAP_AUDITEE_WORK') === FALSE, 'Capability Auditee coarse masih dipakai.');
m203_check(strpos($all_application, 'CAP_AUDITOR_WORK') === FALSE, 'Capability Auditor coarse masih dipakai.');
m203_check(
    strpos($sidebar, "authorization_policy->allows(\$user_id, \$menu['capability'])") !== FALSE,
    'Sidebar belum mengikuti capability matrix.'
);
m203_check(
    strpos($guard, 'function require_capability_in_organization_unit(') !== FALSE
        && strpos($core, 'function _require_capability_in_organization_unit(') !== FALSE,
    'Controller guard belum dapat menggabungkan capability dengan organization scope.'
);

if (!empty($failures)) {
    foreach ($failures as $failure) {
        fwrite(STDERR, '[FAIL] ' . $failure . PHP_EOL);
    }
    exit(1);
}

fwrite(STDOUT, '[PASS] M2-03 role capability matrix regression (' . $checks . " checks)\n");
exit(0);
