#!/usr/bin/env php
<?php

$root = dirname(__DIR__);
$failures = [];
$checks = 0;

function policy_check($condition, $message)
{
    global $failures, $checks;
    $checks++;
    if (!$condition) {
        $failures[] = $message;
    }
}

function policy_source($root, $path)
{
    $contents = file_get_contents($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path));
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }

    return $contents;
}

$policy = policy_source($root, 'application/libraries/Authorization_policy.php');
$guard = policy_source($root, 'application/libraries/Auth_guard.php');
$core = policy_source($root, 'application/core/MY_Controller.php');
$auditee = policy_source($root, 'application/controllers/Auditee.php');
$auditeeLegacy = policy_source($root, 'application/controllers/auditee/Tugas.php');
$auditor = policy_source($root, 'application/controllers/Auditor.php');
$jawabanModel = policy_source($root, 'application/models/Jawaban_model.php');
$authSecurity = policy_source($root, 'application/libraries/Auth_security.php');

foreach ([
    'canViewAuditAssignment',
    'canEditAuditeeSubmission',
    'canAssessAssignment',
    'canViewEvidence',
    'canManageOrganizationUnits',
    'canManageUserUnitAssignments',
    'activeOrganizationAssignments',
    'activeOrganizationUnitIds',
    'canAccessOrganizationUnit',
    'capabilityMatrix',
    'capabilityScope',
    'allowsInOrganizationUnit',
    'allowsInAnyOrganizationUnit',
    'canManageSpmiVersion',
    'canManageRtm',
    'canSubmitFollowUp',
    'canVerifyFollowUp',
] as $method) {
    policy_check(strpos($policy, 'function ' . $method . '(') !== FALSE, 'Policy API ' . $method . ' belum tersedia.');
}

policy_check(strpos($policy, "self::CAP_USERS_MANAGE => ['super_admin']") !== FALSE, 'Kelola user global harus khusus Super Admin.');
policy_check(strpos($policy, "self::CAP_PARTICIPANT_ACCOUNTS_MANAGE => ['super_admin', 'admin_lpmpi']") !== FALSE, 'Capability akun partisipan tidak sesuai.');
policy_check(strpos($policy, "self::CAP_ORGANIZATION_UNITS_MANAGE => ['super_admin', 'admin_lpmpi']") !== FALSE, 'Capability master unit organisasi tidak sesuai.');
policy_check(strpos($policy, "self::CAP_USER_UNIT_ASSIGNMENTS_MANAGE => ['super_admin', 'admin_lpmpi']") !== FALSE, 'Capability assignment unit dan jabatan tidak sesuai.');
policy_check(strpos($policy, 'user_unit_assignment_model->get_active_for_user') !== FALSE, 'Scope organisasi belum bersumber dari assignment aktif.');
policy_check(strpos($policy, "self::CAP_AUDIT_SUBMISSION_FILL => ['auditee']") !== FALSE, 'Capability pengisian Auditee harus deny role lain.');
policy_check(strpos($policy, "self::CAP_AUDIT_SUBMISSION_SUBMIT => ['auditee']") !== FALSE, 'Capability submit Auditee harus eksplisit.');
policy_check(strpos($policy, "self::CAP_AUDIT_ASSESSMENT_FILL => ['auditor']") !== FALSE, 'Capability penilaian Auditor harus deny role lain.');
policy_check(strpos($policy, "self::CAP_AUDIT_ASSESSMENT_SUBMIT => ['auditor']") !== FALSE, 'Capability submit penilaian harus eksplisit.');
policy_check(strpos($policy, "self::CAP_RTM_FINALIZE => []") !== FALSE, 'Finalisasi RTM tanpa keputusan bisnis harus deny-default.');
policy_check(strpos($policy, "self::CAP_FOLLOWUP_VERIFY => []") !== FALSE, 'Verifikasi follow-up tanpa keputusan bisnis harus deny-default.');
policy_check(strpos($guard, 'authorization_policy->allows') !== FALSE, 'Auth guard harus mendelegasikan capability ke central policy.');
policy_check(strpos($guard, 'function require_capability_in_organization_unit(') !== FALSE, 'Guard capability + organization scope belum tersedia.');
policy_check(strpos($guard, 'authorization_policy->allowsInOrganizationUnit') !== FALSE, 'Guard scope organisasi belum mendelegasikan ke central policy.');
policy_check(strpos($guard, 'function only(') === FALSE, 'Role-only guard lama harus dihapus.');
policy_check(strpos($core, '_check_role(') === FALSE, 'Role-only helper lama harus dihapus.');

policy_check(strpos($policy, 'find_tugas_for_auditee') !== FALSE, 'Policy tugas Auditee harus memakai query ter-scope.');
policy_check(strpos($policy, 'find_tugas_for_auditor') !== FALSE, 'Policy tugas Auditor harus memakai query ter-scope.');
policy_check(strpos($policy, 'find_jawaban_for_auditee') !== FALSE, 'Policy bukti Auditee harus memakai query ter-scope.');
policy_check(strpos($policy, 'find_jawaban_for_auditor') !== FALSE, 'Policy bukti Auditor harus memakai query ter-scope.');
policy_check(strpos($jawabanModel, "->where('tugas_audit.auditee_id', (int) \$auditee_id)") !== FALSE, 'Query bukti Auditee tidak membatasi owner.');
policy_check(strpos($jawabanModel, "->where('tugas_audit.auditor_id', (int) \$auditor_id)") !== FALSE, 'Query bukti Auditor tidak membatasi owner.');

policy_check(strpos($auditee, 'authorization_policy->getViewableAssignment') !== FALSE, 'Controller Auditee utama belum memakai policy objek.');
policy_check(strpos($auditee, 'authorization_policy->canEditAuditeeSubmission') !== FALSE, 'Mutasi Auditee utama belum memakai policy state.');
policy_check(strpos($auditeeLegacy, 'authorization_policy->getViewableAssignment') !== FALSE, 'Jalur Auditee legacy belum memakai policy objek.');
policy_check(strpos($auditor, 'authorization_policy->getAssessableEvidence') !== FALSE, 'Mutasi bukti Auditor belum memakai policy objek/state.');
policy_check(strpos($auditor, 'authorization_policy->canAssessAssignment') !== FALSE, 'Mutasi penilaian batch belum memakai policy objek/state.');
policy_check(strpos($auditor, 'authorization_policy->getViewableEvidence') !== FALSE, 'Download bukti belum memakai policy objek.');
policy_check(strpos($auditor, 'if (!$this->require_post())') !== FALSE, 'Jalur penilaian legacy harus membatasi method POST.');

policy_check(strpos($policy, 'authorizeSuperAdminOverride') !== FALSE, 'Super Admin override eksplisit belum tersedia.');
policy_check(strpos($policy, 'strlen($reason) < 10') !== FALSE, 'Super Admin override harus mewajibkan alasan.');
policy_check(strpos($policy, 'EVENT_AUTHORIZATION_OVERRIDE') !== FALSE, 'Super Admin override harus menghasilkan security event.');
policy_check(strpos($policy, 'SECURITY authorization_override') !== FALSE, 'Detail objek override harus masuk technical security log.');
policy_check(strpos($authSecurity, "const EVENT_AUTHORIZATION_OVERRIDE = 'authorization_override';") !== FALSE, 'Event override belum di-allowlist.');

if (!defined('BASEPATH')) {
    define('BASEPATH', $root . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
}
require_once $root . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'Authorization_policy.php';
$reflection = new ReflectionClass('Authorization_policy');
$denyDefaultPolicy = $reflection->newInstanceWithoutConstructor();
policy_check($denyDefaultPolicy->canManageRtm(1, 1) === FALSE, 'RTM tanpa schema/policy harus deny by default.');
policy_check($denyDefaultPolicy->canSubmitFollowUp(1, 1) === FALSE, 'Follow-up tanpa PIC membership harus deny by default.');
policy_check($denyDefaultPolicy->canVerifyFollowUp(1, 1) === FALSE, 'Verifikasi follow-up tanpa capability harus deny by default.');

if (!empty($failures)) {
    foreach ($failures as $failure) {
        fwrite(STDERR, '[FAIL] ' . $failure . PHP_EOL);
    }
    exit(1);
}

echo '[PASS] authorization policy regression (' . $checks . ' checks)' . PHP_EOL;
exit(0);
