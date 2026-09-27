#!/usr/bin/env bash
set -euo pipefail

readonly root=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
readonly compose_file="$root/tests/prodi_staf_runtime.compose.yaml"
project="prodi_staf_p42_$(id -u)_$(date +%s)_$RANDOM"

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
    if ! cleanup && [ "$status" -eq 0 ]; then
        exit 1
    fi
    exit "$status"
}

base_url() {
    local binding
    binding=$(compose port app 80)
    printf 'http://127.0.0.1:%s/index.php\n' "${binding##*:}"
}

wait_for_login_form() {
    local attempts=60 page
    while [ "$attempts" -gt 0 ]; do
        if page=$(curl --fail --silent --show-error --max-time 5 "$(base_url)/auth") && grep -q 'name="csrf_test_name"' <<< "$page"; then
            return 0
        fi
        attempts=$((attempts - 1))
        sleep 2
    done
    printf '%s\n' 'P4.2 login form did not become ready within 120 seconds.' >&2
    return 1
}

seed_fixture() {
    compose exec -T db mysql -uami_runtime -pami_runtime_password ami <<'SQL'
INSERT INTO users (id, nama, email, identity_number, password, role) VALUES
    (910001, 'P4.2 Admin LPMPI', 'admin-lpmpi@p42.runtime.test', 'P42-ADMIN', '$2y$10$nZ.P5gw3Tk5k.LApjXOBdexhq0waxSsXdepBdN.UA2ykJkv8ZUH5S', 'admin_lpmpi'),
    (910002, 'P4.2 Auditor', 'auditor@p42.runtime.test', 'P42-AUDITOR', '$2y$10$nZ.P5gw3Tk5k.LApjXOBdexhq0waxSsXdepBdN.UA2ykJkv8ZUH5S', 'auditor'),
    (910003, 'P4.2 Auditee', 'auditee@p42.runtime.test', 'P42-AUDITEE', '$2y$10$nZ.P5gw3Tk5k.LApjXOBdexhq0waxSsXdepBdN.UA2ykJkv8ZUH5S', 'auditee');
INSERT INTO profil_prodi (id, kode_prodi, nama_prodi, jenjang) VALUES
    (920001, 'P42-SOURCE', 'P4.2 Source Prodi', 'S1'),
    (920002, 'P42-TARGET', 'P4.2 Target Prodi', 'S1');
SQL
}

main() {
    if [ "${1:-run}" != "run" ] || [ "$#" -gt 1 ]; then
        printf 'usage: %s [run]\n' "$0" >&2
        exit 2
    fi
    trap cleanup_on_exit EXIT
    trap 'exit 130' INT
    trap 'exit 143' TERM
    compose up --build --detach
    wait_for_login_form
    seed_fixture
    python3 "$root/tests/prodi_staf_runtime_smoke.py" --base-url "$(base_url)" --project "$project" --compose-file "$compose_file"
    printf 'P4.2 isolated staf_prodi runtime regression passed (project %s).\n' "$project"
}

main "$@"
