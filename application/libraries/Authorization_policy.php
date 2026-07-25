<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Central capability and object-authorization policy.
 *
 * Role checks belong here. Controllers request a capability or a scoped
 * object and never treat an ID from the URL as proof of authorization.
 */
class Authorization_policy
{
    const CAP_DASHBOARD_VIEW = 'dashboard.view';
    const CAP_ACCOUNT_SELF = 'account.self';
    const CAP_PROFILE_VIEW = 'profile.view';
    const CAP_PROFILE_MANAGE = 'profile.manage';
    const CAP_USERS_MANAGE = 'users.manage';
    const CAP_PARTICIPANT_ACCOUNTS_MANAGE = 'participant_accounts.manage';
    const CAP_ORGANIZATION_UNITS_MANAGE = 'organization_units.manage';
    const CAP_USER_UNIT_ASSIGNMENTS_MANAGE = 'user_unit_assignments.manage';
    const CAP_SPMI_MANAGE = 'spmi.manage';
    const CAP_ASSIGNMENTS_MANAGE = 'assignments.manage';
    const CAP_REPORTS_VIEW = 'reports.view';
    const CAP_AUDITEE_WORK = 'auditee_assignment.work';
    const CAP_AUDITOR_WORK = 'auditor_assignment.work';

    const OVERRIDE_ASSIGNMENT_VIEW = 'override.assignment.view';
    const OVERRIDE_EVIDENCE_VIEW = 'override.evidence.view';

    protected $ci;
    protected $user_model;
    protected $jawaban_model;
    protected $tugas_audit_model;
    protected $user_unit_assignment_model;
    protected $auth_security;
    protected $user_cache = [];
    protected $object_cache = [];

    protected $capability_roles = [
        self::CAP_DASHBOARD_VIEW => ['super_admin', 'admin_lpmpi', 'auditor', 'auditee'],
        self::CAP_ACCOUNT_SELF => ['super_admin', 'admin_lpmpi', 'auditor', 'auditee'],
        self::CAP_PROFILE_VIEW => ['super_admin', 'admin_lpmpi', 'auditor', 'auditee'],
        self::CAP_PROFILE_MANAGE => ['super_admin', 'admin_lpmpi'],
        self::CAP_USERS_MANAGE => ['super_admin'],
        self::CAP_PARTICIPANT_ACCOUNTS_MANAGE => ['super_admin', 'admin_lpmpi'],
        self::CAP_ORGANIZATION_UNITS_MANAGE => ['super_admin', 'admin_lpmpi'],
        self::CAP_USER_UNIT_ASSIGNMENTS_MANAGE => ['super_admin', 'admin_lpmpi'],
        self::CAP_SPMI_MANAGE => ['super_admin', 'admin_lpmpi'],
        self::CAP_ASSIGNMENTS_MANAGE => ['super_admin', 'admin_lpmpi'],
        self::CAP_REPORTS_VIEW => ['super_admin', 'admin_lpmpi'],
        self::CAP_AUDITEE_WORK => ['auditee'],
        self::CAP_AUDITOR_WORK => ['auditor'],
    ];

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('User_model');
        $this->ci->load->model('Jawaban_model');
        $this->ci->load->model('Tugas_audit_model');
        $this->ci->load->model('User_unit_assignment_model');
        $this->ci->load->library('auth_security');

        $this->user_model = $this->ci->User_model;
        $this->jawaban_model = $this->ci->Jawaban_model;
        $this->tugas_audit_model = $this->ci->Tugas_audit_model;
        $this->user_unit_assignment_model = $this->ci->User_unit_assignment_model;
        $this->auth_security = $this->ci->auth_security;
    }

    public function allows($user_id, $capability)
    {
        $user = $this->active_user($user_id);
        $roles = isset($this->capability_roles[$capability])
            ? $this->capability_roles[$capability]
            : [];

        return $user && in_array((string) $user->role, $roles, TRUE);
    }

    public function getViewableAssignment($user_id, $assignment_id)
    {
        $user = $this->active_user($user_id);
        if (!$user || (int) $assignment_id < 1) {
            return NULL;
        }

        $cache_key = 'assignment:view:' . (int) $user_id . ':' . (int) $assignment_id;
        if (array_key_exists($cache_key, $this->object_cache)) {
            return $this->object_cache[$cache_key];
        }

        if ($this->allows($user_id, self::CAP_ASSIGNMENTS_MANAGE)) {
            $assignment = $this->tugas_audit_model->find_with_relations((int) $assignment_id);
        } elseif ($this->allows($user_id, self::CAP_AUDITEE_WORK)) {
            $assignment = $this->jawaban_model->find_tugas_for_auditee(
                (int) $assignment_id,
                (int) $user_id
            );
        } elseif ($this->allows($user_id, self::CAP_AUDITOR_WORK)) {
            $assignment = $this->jawaban_model->find_tugas_for_auditor(
                (int) $assignment_id,
                (int) $user_id
            );
        } else {
            $assignment = NULL;
        }

        $this->object_cache[$cache_key] = $assignment ?: NULL;
        return $this->object_cache[$cache_key];
    }

    public function canViewAuditAssignment($user_id, $assignment_id)
    {
        return $this->getViewableAssignment($user_id, $assignment_id) !== NULL;
    }

    public function getEditableAuditeeAssignment($user_id, $assignment_id)
    {
        if (!$this->allows($user_id, self::CAP_AUDITEE_WORK)) {
            return NULL;
        }

        $assignment = $this->jawaban_model->find_tugas_for_auditee(
            (int) $assignment_id,
            (int) $user_id
        );

        return $assignment && empty($assignment->is_readonly) ? $assignment : NULL;
    }

    public function canEditAuditeeSubmission($user_id, $assignment_id)
    {
        return $this->getEditableAuditeeAssignment($user_id, $assignment_id) !== NULL;
    }

    public function getAssessableAssignment($user_id, $assignment_id)
    {
        if (!$this->allows($user_id, self::CAP_AUDITOR_WORK)) {
            return NULL;
        }

        $assignment = $this->jawaban_model->find_tugas_for_auditor(
            (int) $assignment_id,
            (int) $user_id
        );

        return $assignment
            && !empty($assignment->is_auditee_submitted)
            && empty($assignment->is_nilai_readonly)
                ? $assignment
                : NULL;
    }

    public function canAssessAssignment($user_id, $assignment_id)
    {
        return $this->getAssessableAssignment($user_id, $assignment_id) !== NULL;
    }

    public function canRequestAuditeeRevision($user_id, $assignment_id)
    {
        return $this->getAssessableAssignment($user_id, $assignment_id) !== NULL;
    }

    public function getViewableEvidence($user_id, $evidence_id)
    {
        if ((int) $evidence_id < 1) {
            return NULL;
        }

        if ($this->allows($user_id, self::CAP_AUDITOR_WORK)) {
            return $this->jawaban_model->find_jawaban_for_auditor(
                (int) $evidence_id,
                (int) $user_id
            );
        }

        if ($this->allows($user_id, self::CAP_AUDITEE_WORK)) {
            return $this->jawaban_model->find_jawaban_for_auditee(
                (int) $evidence_id,
                (int) $user_id
            );
        }

        return NULL;
    }

    public function canViewEvidence($user_id, $evidence_id)
    {
        return $this->getViewableEvidence($user_id, $evidence_id) !== NULL;
    }

    public function getAssessableEvidence($user_id, $evidence_id)
    {
        if (!$this->allows($user_id, self::CAP_AUDITOR_WORK)) {
            return NULL;
        }

        $evidence = $this->jawaban_model->find_jawaban_for_auditor(
            (int) $evidence_id,
            (int) $user_id
        );
        if (!$evidence) {
            return NULL;
        }

        return $this->getAssessableAssignment($user_id, $evidence->tugas_id)
            ? $evidence
            : NULL;
    }

    public function canManageSpmiVersion($user_id)
    {
        return $this->allows($user_id, self::CAP_SPMI_MANAGE);
    }

    public function canManageOrganizationUnits($user_id)
    {
        return $this->allows($user_id, self::CAP_ORGANIZATION_UNITS_MANAGE);
    }

    public function canManageUserUnitAssignments($actor_user_id, $target_user_id)
    {
        $actor = $this->active_user($actor_user_id);
        $target = $this->user_model->find((int) $target_user_id);
        if (!$actor || !$target
            || !$this->allows($actor_user_id, self::CAP_USER_UNIT_ASSIGNMENTS_MANAGE)) {
            return FALSE;
        }

        if ((string) $actor->role === 'super_admin') {
            return TRUE;
        }

        return (string) $actor->role === 'admin_lpmpi'
            && in_array((string) $target->role, ['auditor', 'auditee'], TRUE);
    }

    public function activeOrganizationAssignments($user_id, $on_date = NULL)
    {
        $user = $this->active_user($user_id);
        if (!$user || !$this->user_unit_assignment_model->schema_ready()) {
            return [];
        }

        $on_date = $on_date === NULL ? date('Y-m-d') : (string) $on_date;
        if (!$this->valid_date($on_date)) {
            return [];
        }

        return $this->user_unit_assignment_model->get_active_for_user(
            (int) $user_id,
            $on_date
        );
    }

    public function activeOrganizationUnitIds($user_id, $on_date = NULL)
    {
        $ids = [];
        foreach ($this->activeOrganizationAssignments($user_id, $on_date) as $assignment) {
            $ids[(int) $assignment->organization_unit_id] = TRUE;
        }

        return array_keys($ids);
    }

    public function canAccessOrganizationUnit($user_id, $organization_unit_id, $on_date = NULL)
    {
        return in_array(
            (int) $organization_unit_id,
            $this->activeOrganizationUnitIds($user_id, $on_date),
            TRUE
        );
    }

    public function canManageRtm($user_id, $rtm_id)
    {
        // RTM has no current schema, organization scope, or finalizer policy.
        // Deny by default until its milestone supplies all three.
        return FALSE;
    }

    public function canSubmitFollowUp($user_id, $rtm_item_id)
    {
        // PIC membership does not exist in the current identity model.
        return FALSE;
    }

    public function canVerifyFollowUp($user_id, $rtm_item_id)
    {
        // Verifier capability and separation-of-duties rules are undecided.
        return FALSE;
    }

    public function getAssignmentWithSuperAdminOverride(
        $user_id,
        $assignment_id,
        $reason
    ) {
        $assignment = $this->tugas_audit_model->find_with_relations((int) $assignment_id);
        if (!$assignment || !$this->authorizeSuperAdminOverride(
            $user_id,
            self::OVERRIDE_ASSIGNMENT_VIEW,
            'audit_assignment',
            $assignment_id,
            $reason
        )) {
            return NULL;
        }

        return $assignment;
    }

    public function getEvidenceWithSuperAdminOverride(
        $user_id,
        $evidence_id,
        $reason
    ) {
        $evidence = $this->jawaban_model->find_jawaban_with_assignment((int) $evidence_id);
        if (!$evidence || !$this->authorizeSuperAdminOverride(
            $user_id,
            self::OVERRIDE_EVIDENCE_VIEW,
            'audit_evidence',
            $evidence_id,
            $reason
        )) {
            return NULL;
        }

        return $evidence;
    }

    public function authorizeSuperAdminOverride(
        $user_id,
        $capability,
        $object_type,
        $object_id,
        $reason
    ) {
        $user = $this->active_user($user_id);
        $allowed_overrides = [
            self::OVERRIDE_ASSIGNMENT_VIEW,
            self::OVERRIDE_EVIDENCE_VIEW,
        ];
        $reason = trim((string) $reason);

        if (!$user
            || $user->role !== 'super_admin'
            || !in_array($capability, $allowed_overrides, TRUE)
            || !preg_match('/^[a-z_]+$/', (string) $object_type)
            || (int) $object_id < 1
            || strlen($reason) < 10) {
            return FALSE;
        }

        $event_reason = substr('override_' . str_replace('.', '_', $capability), 0, 40);
        $this->auth_security->record_event(
            Auth_security::EVENT_AUTHORIZATION_OVERRIDE,
            $user->email,
            $user->id,
            $event_reason
        );

        $safe_reason = preg_replace('/[\r\n\t]+/', ' ', $reason);
        log_message(
            'error',
            'SECURITY authorization_override actor_user_id='
                . (int) $user->id
                . ' capability='
                . $capability
                . ' object_type='
                . $object_type
                . ' object_id='
                . (int) $object_id
                . ' reason='
                . substr($safe_reason, 0, 255)
        );

        return TRUE;
    }

    private function active_user($user_id)
    {
        $user_id = (int) $user_id;
        if ($user_id < 1) {
            return NULL;
        }

        if (!array_key_exists($user_id, $this->user_cache)) {
            $user = $this->user_model->find($user_id);
            $this->user_cache[$user_id] = $user && (int) $user->is_active === 1
                ? $user
                : NULL;
        }

        return $this->user_cache[$user_id];
    }

    private function valid_date($value)
    {
        $date = DateTime::createFromFormat('!Y-m-d', (string) $value);
        return $date && $date->format('Y-m-d') === (string) $value;
    }
}
