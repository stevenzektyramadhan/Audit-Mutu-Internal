#!/usr/bin/env bash
set -euo pipefail

M17_07A_FIXTURE_PASSWORD=m17-07a-only-password
readonly M17_07A_FIXTURE_PASSWORD
root=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
compose_file="$root/tests/m17_07a_runtime.compose.yaml"
project=""

usage() {
    printf '%s\n' "usage: $0 start|bootstrap|verify|smoke|teardown|run [--project NAME]"
    printf '%s\n' 'bootstrap waits for the normal CSRF login form after MySQL initializes the fresh named volume.'
    printf '%s\n' 'run generates an isolated project, then runs start -> bootstrap -> verify -> smoke and always tears it down.'
    printf '%s\n' 'start/bootstrap/verify/smoke/teardown require --project for a manually managed isolated environment.'
}

compose() {
    COMPOSE_BAKE=false docker compose -p "$project" -f "$compose_file" "$@"
}

cleanup() {
    compose down --volumes --remove-orphans
    test -z "$(docker ps --all --quiet --filter "label=com.docker.compose.project=$project")"
    test -z "$(docker network ls --filter "label=com.docker.compose.project=$project" --quiet)"
    test -z "$(docker volume ls --filter "label=com.docker.compose.project=$project" --quiet)"
}

cleanup_on_exit() {
    local status=$?
    trap - EXIT INT TERM
    if cleanup; then
        :
    else
        local cleanup_status=$?
        if [ "$status" -eq 0 ]; then
            exit "$cleanup_status"
        fi
    fi
    exit "$status"
}

install_run_cleanup() {
    trap cleanup_on_exit EXIT
    trap 'exit 130' INT
    trap 'exit 143' TERM
}

require_project() {
    if [ -z "$project" ]; then
        printf '%s\n' '--project NAME is required for this command.' >&2
        exit 2
    fi
}

new_project() {
    project="m17_07a_$(id -u)_$(date +%s)_$RANDOM"
}

base_url() {
    compose port app 80 | awk -F: '{print "http://127.0.0.1:" $NF "/index.php"}'
}

wait_for_login_form() {
    local attempts=60
    local page
    while [ "$attempts" -gt 0 ]; do
        if page=$(curl --fail --silent --show-error --max-time 5 "$(base_url)/auth") && grep -q 'name="csrf_test_name"' <<< "$page"; then
            return 0
        fi
        attempts=$((attempts - 1))
        sleep 2
    done
    printf '%s\n' 'normal CSRF login form did not become ready within 120 seconds.' >&2
    return 1
}

assignment_ids() {
    compose exec -T mysql mysql -N -B -uami_runtime -pami_runtime_password ami -e "
        SELECT GROUP_CONCAT(assignment_id ORDER BY auditor_email SEPARATOR ' ')
        FROM (
            SELECT assignment.id AS assignment_id, assignment.auditor_email
            FROM spmi_audit_assignments AS assignment
            JOIN spmi_audit_cycles AS cycle ON cycle.id = assignment.cycle_id
            JOIN spmi_instrument_packages AS package ON package.id = assignment.source_package_id
            WHERE cycle.cycle_code = 'M17R-C1'
              AND package.package_code = 'M17R-P1'
              AND (
                  (assignment.auditor_email = 'auditor-a@m17-07a.test' AND assignment.auditee_email = 'auditee-a@m17-07a.test')
                  OR (assignment.auditor_email = 'auditor-b@m17-07a.test' AND assignment.auditee_email = 'auditee-b@m17-07a.test')
              )
        ) AS fixture_assignments;
    "
}

require_assignment_ids() {
    local ids
    read -r auditor_a auditor_b <<< "$(assignment_ids)"
    ids="$auditor_a $auditor_b"
    if [[ ! "$ids" =~ ^[0-9]+\ [0-9]+$ ]]; then
        printf '%s\n' 'fixture assignment lookup did not return the two synthetic assignment IDs.' >&2
        return 1
    fi
}

verify_fixture() {
    compose ps --status running --quiet | grep -q .
    compose exec -T mysql mysql -N -uami_runtime -pami_runtime_password ami -e "
        SELECT COUNT(*) = 6 FROM users WHERE email LIKE '%@m17-07a.test';
        SELECT COUNT(*) = 5 FROM spmi_instrument_questions WHERE package_id = (SELECT id FROM spmi_instrument_packages WHERE package_code = 'M17R-P1');
        SELECT COUNT(*) = 40 FROM spmi_audit_assignment_item_rubrics;
        SELECT COUNT(*) = 2 FROM spmi_audit_assignments;
        SELECT COUNT(*) = 0 FROM spmi_auditee_submissions;
        SELECT COUNT(*) = 0 FROM spmi_auditor_assessments;
    " | grep -cx '1' | grep -qx '6'
}

start() {
    compose up --build --detach
}

bootstrap() {
    wait_for_login_form
}

smoke() {
    export M17_07A_FIXTURE_PASSWORD
    require_assignment_ids
    python3 "$root/tests/m17_07a_http_smoke.py" --base-url "$(base_url)" --auditor-a-assignment "$auditor_a" --auditor-b-assignment "$auditor_b" --auditee-a-assignment "$auditor_a" --auditee-b-assignment "$auditor_b"
}

command=${1:-}
shift || true
while [ $# -gt 0 ]; do
    case "$1" in
        --project) project=${2:?--project requires NAME}; shift 2 ;;
        *) usage >&2; exit 2 ;;
    esac
done

case "$command" in
    run)
        new_project
        install_run_cleanup
        start
        bootstrap
        verify_fixture
        smoke
        ;;
    start)
        require_project
        start
        ;;
    bootstrap)
        require_project
        bootstrap
        ;;
    verify)
        require_project
        verify_fixture
        ;;
    smoke)
        require_project
        smoke
        ;;
    teardown)
        require_project
        cleanup
        ;;
    *)
        usage >&2
        exit 2
        ;;
esac
