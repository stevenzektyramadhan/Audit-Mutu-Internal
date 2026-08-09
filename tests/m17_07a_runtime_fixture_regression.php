<?php

function m17_07a_source($path)
{
    $full_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $path;
    $source = file_get_contents($full_path);
    if ($source === FALSE) throw new RuntimeException('M17-07A required artifact missing or unreadable: ' . $path);
    return $source;
}

function m17_07a_check($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

function m17_07a_password_valid($hash, $password)
{
    return password_verify($password, $hash);
}

// Given: a repository-controlled disposable M17-07A runtime fixture contract.
$compose = m17_07a_source('tests/m17_07a_runtime.compose.yaml');
$fixture = m17_07a_source('tests/fixtures/m17_07a_runtime.sql');
$smoke = m17_07a_source('tests/m17_07a_http_smoke.py');
$runner = m17_07a_source('tests/run_m17_07a_runtime.sh');
$plan = m17_07a_source('docs/plan/m17-spmi-workspace-parity-master-plan.md');

// When: only the static test infrastructure is inspected.
// Then: the isolated runtime topology, deterministic seed, and exact-project cleanup contract are present.
m17_07a_check(strpos($compose, 'database_schema.sql:/docker-entrypoint-initdb.d/01-schema.sql:ro') !== FALSE, 'M17-07A Compose must initialize the fresh DB from database_schema.sql.');
m17_07a_check(strpos($compose, 'm17_07a_runtime.sql:/docker-entrypoint-initdb.d/02-fixture.sql:ro') !== FALSE, 'M17-07A Compose must initialize the fresh DB from the dedicated fixture.');
m17_07a_check(strpos($compose, 'database_dummy.sql') === FALSE, 'M17-07A Compose must never reference database_dummy.sql.');
m17_07a_check(strpos($compose, 'CI_ENV: development') !== FALSE, 'M17-07A Compose must run the app with CI_ENV=development.');
m17_07a_check(strpos($compose, 'APP_PRIVATE_STORAGE_PATH: /srv/ami/private') !== FALSE, 'M17-07A Compose must override private storage away from the temporary fallback.');
m17_07a_check(strpos($compose, 'm17_07a_private_storage:/srv/ami/private') !== FALSE, 'M17-07A private storage must use its own disposable named volume.');
m17_07a_check(strpos($compose, "  m17_07a_private_storage:\n") !== FALSE, 'M17-07A Compose must declare the private-storage volume.');
m17_07a_check(strpos($compose, 'mkdir -p /srv/ami/private') !== FALSE, 'M17-07A app startup must create only the disposable private-storage mount.');
m17_07a_check(strpos($compose, 'chown www-data:www-data /srv/ami/private') !== FALSE, 'M17-07A app startup must give Apache ownership of only the disposable private-storage mount.');
m17_07a_check(strpos($compose, 'exec apache2-foreground') !== FALSE, 'M17-07A app startup must exec the normal Apache foreground process after private-storage initialization.');
foreach (['chown www-data:www-data /var/www/html', 'chown www-data:www-data /var/www/html/application/cache/sessions', 'chown www-data:www-data /var/www/html/uploads', 'chown -R', 'chmod'] as $forbidden_startup_token) {
    m17_07a_check(strpos($compose, $forbidden_startup_token) === FALSE, 'M17-07A app startup must not mutate application source, sessions, uploads, or permissions with ' . $forbidden_startup_token . '.');
}
m17_07a_check(strpos($compose, 'MYSQL_ROOT_PASSWORD: m17-07a-test-only-root-password') !== FALSE, 'M17-07A Compose must retain Docker MySQL initialization with a clearly test-only root password.');
m17_07a_check(preg_match('/\bports:\s*\n\s*-\s*["\']127\.0\.0\.1::80["\']/m', $compose) === 1, 'M17-07A app must publish only an ephemeral loopback port.');
m17_07a_check(strpos($compose, "  mysql:\n") !== FALSE, 'M17-07A Compose must expose the test-only MySQL service as mysql.');
m17_07a_check(preg_match('/\bmysql:\s*\n(?:(?!\n\s{2}\w).)*?\bports:/s', $compose) !== 1, 'M17-07A MySQL service must not publish a host port.');

foreach (['super_admin', 'admin_lpmpi', 'auditor', 'auditee'] as $role) {
    m17_07a_check(substr_count($fixture, "'" . $role . "'") >= 1, 'M17-07A fixture missing supported role ' . $role . '.');
}
foreach (['super-admin', 'admin-lpmpi', 'auditor-a', 'auditor-b', 'auditee-a', 'auditee-b'] as $account) {
    m17_07a_check(substr_count($fixture, "'" . $account . "@m17-07a.test'") >= 1, 'M17-07A fixture missing deterministic account ' . $account . '.');
}
foreach (['none', 'file', 'url', 'either', 'both'] as $policy) {
    m17_07a_check(strpos($fixture, "'" . $policy . "'") !== FALSE, 'M17-07A fixture missing evidence policy ' . $policy . '.');
}
foreach (['START TRANSACTION;', 'COMMIT;', 'spmi_instrument_rubrics', 'spmi_audit_assignments', 'spmi_audit_assignment_items', 'spmi_audit_assignment_item_rubrics', "'configured'"] as $token) {
    m17_07a_check(strpos($fixture, $token) !== FALSE, 'M17-07A fixture missing required topology token ' . $token . '.');
}
foreach (['spmi_auditee_submissions', 'spmi_auditor_assessments', 'spmi_reports', 'spmi_rtm_meetings', 'spmi_auditee_submission_revision_events'] as $forbidden_table) {
    m17_07a_check(strpos($fixture, 'INSERT INTO `' . $forbidden_table . '`') === FALSE, 'M17-07A fixture must not pre-create ' . $forbidden_table . '.');
}

m17_07a_check(strpos($runner, 'M17_07A_FIXTURE_PASSWORD=') !== FALSE, 'M17-07A runner must own the fixture-only plaintext password.');
preg_match('/M17_07A_FIXTURE_PASSWORD=([^\n]+)/', $runner, $password_match);
m17_07a_check(isset($password_match[1]), 'M17-07A runner fixture password is unreadable.');
preg_match('/\$2[aby]\$\d\d\$[^\'\s,;]+/', $fixture, $hash_match);
m17_07a_check(isset($hash_match[0]) && m17_07a_password_valid($hash_match[0], trim($password_match[1], "\"'")), 'M17-07A fixture bcrypt hash must authenticate with the runner password.');
m17_07a_check(strpos($runner, 'COMPOSE_BAKE=false docker compose -p "$project" -f "$compose_file" "$@"') !== FALSE, 'M17-07A runner must disable Compose Bake locally for every fixture docker compose call.');
foreach (['start', 'bootstrap', 'verify', 'smoke', 'teardown', 'run', 'new_project', 'project="m17_07a_', 'trap cleanup_on_exit EXIT', "trap 'exit 130' INT", "trap 'exit 143' TERM", 'down --volumes --remove-orphans', 'docker compose -p "$project"', 'docker volume ls --filter "label=com.docker.compose.project=$project" --quiet', 'curl --fail --silent --show-error --max-time 5', 'name="csrf_test_name"', 'compose exec -T mysql mysql', "cycle.cycle_code = 'M17R-C1'", "package.package_code = 'M17R-P1'"] as $token) {
    m17_07a_check(strpos($runner, $token) !== FALSE, 'M17-07A runner missing exact-project lifecycle token ' . $token . '.');
}
m17_07a_check(strpos($runner, "'1 2 1 2'") === FALSE, 'M17-07A runner must not hardcode fixture assignment IDs.');
m17_07a_check(strpos($runner, '-uroot') === FALSE, 'M17-07A runner must not use a MySQL root client workflow.');
m17_07a_check(strpos($runner, '.multibrain') === FALSE, 'M17-07A runner must not inspect .multibrain, which is pre-existing untracked content outside test artifacts.');
m17_07a_check(preg_match('/run\)\s+new_project\s+install_run_cleanup\s+start\s+bootstrap\s+verify_fixture\s+smoke/s', $runner) === 1, 'M17-07A run command must orchestrate start -> bootstrap -> verify -> smoke.');
foreach (['urllib.request', 'http.cookiejar.CookieJar', '/auth/login', 'csrf_test_name', 'multipart/form-data; boundary=', 'application/pdf', 'filename=\"m17-07a.pdf\"', '/auditee/spmi/assignment/', '/submit', '/auditor/spmi/assignment/', 'submit_auditee_assignment', 'auditor-a@m17-07a.test', 'auditor-b@m17-07a.test', 'auditee-a@m17-07a.test', 'auditee-b@m17-07a.test'] as $token) {
    m17_07a_check(strpos($smoke, $token) !== FALSE, 'M17-07A smoke runner missing normal HTTP fixture contract ' . $token . '.');
}
$dashboard_markers = [
    'super-admin@m17-07a.test' => 'Dashboard Super Admin',
    'admin-lpmpi@m17-07a.test' => 'Dashboard Super Admin',
    'auditor-a@m17-07a.test' => 'Dashboard Auditor',
    'auditor-b@m17-07a.test' => 'Dashboard Auditor',
    'auditee-a@m17-07a.test' => 'Dashboard Auditee',
    'auditee-b@m17-07a.test' => 'Dashboard Auditee',
];
m17_07a_check(strpos($smoke, 'DASHBOARD_MARKERS = {') !== FALSE, 'M17-07A smoke must centralize dashboard marker mapping.');
m17_07a_check(preg_match('/for\s+email,\s+marker\s+in\s+DASHBOARD_MARKERS\.items\(\):/', $smoke) === 1, 'M17-07A smoke must iterate every dashboard marker account.');
m17_07a_check(strpos($smoke, 'login(base_url, email)') !== FALSE, 'M17-07A smoke dashboard loop must authenticate each mapped account.');
m17_07a_check(strpos($smoke, 'expect_page(opener, base_url + "/dashboard", marker)') !== FALSE, 'M17-07A smoke dashboard loop must verify the mapped dashboard marker.');
foreach ($dashboard_markers as $email => $marker) {
    m17_07a_check(strpos($smoke, '"' . $email . '": "' . $marker . '"') !== FALSE, 'M17-07A smoke missing dashboard marker mapping for ' . $email . '.');
}
m17_07a_check(strpos($smoke, 'NoRedirectHandler') !== FALSE, 'M17-07A smoke submit must disable transparent urllib redirects.');
m17_07a_check(strpos($smoke, 'submit_request(') !== FALSE, 'M17-07A smoke submit must use a submit-specific request helper.');
m17_07a_check(strpos($smoke, 'upload_request(') !== FALSE, 'M17-07A smoke upload must use an upload-specific no-redirect request helper.');
m17_07a_check(strpos($smoke, 'expected redirect after auditee evidence upload') !== FALSE, 'M17-07A smoke upload must require redirect semantics instead of accepting generic status.');
m17_07a_check(strpos($smoke, 'assert_assignment_redirect_location("auditee evidence upload"') !== FALSE, 'M17-07A smoke upload must require redirect to the exact assignment URL.');
m17_07a_check(strpos($smoke, 'def bounded_location(location: str) -> str:') !== FALSE, 'M17-07A smoke must centralize bounded redirect Location diagnostics.');
m17_07a_check(strpos($smoke, '"query": "[redacted]" if parsed.query else ""') !== FALSE, 'M17-07A smoke redirect diagnostics must redact query strings.');
m17_07a_check(strpos($smoke, '"fragment": "[redacted]" if parsed.fragment else ""') !== FALSE, 'M17-07A smoke redirect diagnostics must redact fragments.');
m17_07a_check(strpos($smoke, 'value.replace(PASSWORD, "[fixture-password]")[:240]') !== FALSE, 'M17-07A smoke redirect diagnostics must redact fixture passwords and bound output length.');
m17_07a_check(strpos($smoke, 'Location={location') === FALSE, 'M17-07A smoke redirect failures must not emit raw Location values.');
m17_07a_check(strpos($smoke, 'resolved={resolved_location.geturl()}') === FALSE, 'M17-07A smoke redirect failures must not emit raw resolved redirect URLs.');
m17_07a_check(substr_count($smoke, 'bounded_location(location)') >= 3, 'M17-07A smoke redirect failures must format observed Location values through bounded_location().');
m17_07a_check(strpos($smoke, 'bounded_location(resolved_location.geturl())') !== FALSE, 'M17-07A smoke redirect failures must format resolved redirect URLs through bounded_location().');
m17_07a_check(strpos($smoke, 'observed post-upload version=') !== FALSE, 'M17-07A smoke upload failure must report observed post-upload version.');
m17_07a_check(strpos($smoke, 'observed post-upload status=') !== FALSE, 'M17-07A smoke upload failure must report observed post-upload status.');
m17_07a_check(strpos($smoke, 'auditee evidence upload did not advance assignment version') !== FALSE, 'M17-07A smoke upload must prove the assignment version advances before the next upload.');
m17_07a_check(strpos($smoke, 'expected redirect after auditee submission') !== FALSE, 'M17-07A smoke submit must require redirect semantics instead of accepting 200.');
m17_07a_check(strpos($smoke, 'urllib.parse.urlsplit(') !== FALSE, 'M17-07A smoke submit must parse redirect targets with stdlib URL splitting.');
m17_07a_check(strpos($smoke, 'urllib.parse.urljoin(') !== FALSE, 'M17-07A smoke submit must resolve relative redirect targets with stdlib URL joining.');
m17_07a_check(strpos($smoke, 'expected_assignment = urllib.parse.urlsplit(assignment_url)') !== FALSE, 'M17-07A smoke submit must canonicalize the expected assignment URL before comparison.');
m17_07a_check(strpos($smoke, 'resolved_location = urllib.parse.urlsplit(urllib.parse.urljoin(assignment_url, location))') !== FALSE, 'M17-07A smoke submit must resolve the redirect Location before comparison.');
m17_07a_check(strpos($smoke, 'resolved_location.scheme != expected_assignment.scheme') !== FALSE, 'M17-07A smoke submit must reject foreign-scheme redirects.');
m17_07a_check(strpos($smoke, 'resolved_location.netloc != expected_assignment.netloc') !== FALSE, 'M17-07A smoke submit must reject foreign-origin redirects.');
m17_07a_check(strpos($smoke, 'resolved_location.path != expected_assignment.path') !== FALSE, 'M17-07A smoke submit must reject redirects to the wrong assignment path.');
m17_07a_check(strpos($smoke, 'resolved_location.query != expected_assignment.query') !== FALSE, 'M17-07A smoke submit must reject redirects with the wrong query string.');
m17_07a_check(strpos($smoke, 'resolved_location.fragment != expected_assignment.fragment') !== FALSE, 'M17-07A smoke submit must reject redirects with the wrong fragment.');
m17_07a_check(strpos($smoke, 'assert_submitted_assignment(') !== FALSE, 'M17-07A smoke submit must observe submitted assignment state after POST.');
m17_07a_check(strpos($smoke, 'def assert_evidence_urls(page: str, evidence_urls: list[str]) -> None:') !== FALSE, 'M17-07A smoke must define a page-only evidence URL assertion.');
m17_07a_check(strpos($smoke, 'missing_urls = [url for url in evidence_urls if url not in page]') !== FALSE, 'M17-07A smoke must check rendered evidence URLs by inspecting the auditor page HTML.');
m17_07a_check(strpos($smoke, 'auditor assignment page did not render submitted auditee evidence URLs') !== FALSE, 'M17-07A smoke must fail when Auditor A cannot see submitted Auditee A evidence URLs.');
m17_07a_check(strpos($smoke, 'PRIVATE_EVIDENCE_DOWNLOAD_PATTERN') !== FALSE, 'M17-07A smoke must define a private auditor evidence download URL extractor.');
m17_07a_check(strpos($smoke, 'def extract_private_evidence_download_url(page: str, base_url: str) -> str | None:') !== FALSE, 'M17-07A smoke must extract existing private download links from the auditor assignment page.');
m17_07a_check(strpos($smoke, 'def assert_private_evidence_download(opener: urllib.request.OpenerDirector, download_url: str) -> None:') !== FALSE, 'M17-07A smoke must perform a normal authenticated private evidence download.');
m17_07a_check(strpos($smoke, 'expected authorized private evidence PDF download') !== FALSE, 'M17-07A smoke must fail when Auditor A cannot download own private evidence as PDF content.');
m17_07a_check(strpos($smoke, 'assert_private_evidence_download(auditor_a, auditor_a_download_url)') !== FALSE, 'M17-07A smoke must prove Auditor A can download own assignment private evidence.');
m17_07a_check(strpos($smoke, 'expect_denied(auditor_a, auditor_b_download_url)') !== FALSE, 'M17-07A smoke must prove Auditor A cannot download Auditor B private evidence URL when safely extractable.');
m17_07a_check(strpos($smoke, 'stored_path') === FALSE, 'M17-07A smoke must not assert or expose private stored paths.');
m17_07a_check(strpos($smoke, 'urllib.request.urlopen') === FALSE, 'M17-07A smoke must not dereference submitted external evidence URLs.');
m17_07a_check(strpos($smoke, 'SUBMITTED_STATUS_PATTERN') !== FALSE, 'M17-07A smoke must structurally detect the submitted status label.');
m17_07a_check(strpos($smoke, 'auditee assignment still exposed editable version after submit') !== FALSE, 'M17-07A smoke must fail when a submitted assignment still renders an editable hidden version input.');
m17_07a_check(strpos($smoke, 'auditee assignment page did not show submitted read-only status after submit') !== FALSE, 'M17-07A smoke must require the submitted read-only status marker after submit.');
$submitted_assert_function = strstr($smoke, 'def assert_submitted_assignment(');
m17_07a_check($submitted_assert_function !== FALSE, 'M17-07A smoke must define assert_submitted_assignment().');
$submitted_assert_function = strstr($submitted_assert_function, 'def submit_auditee_assignment(', TRUE);
m17_07a_check($submitted_assert_function !== FALSE, 'M17-07A smoke submitted assertion function must appear before submit_auditee_assignment().');
m17_07a_check(strpos($submitted_assert_function, 'if version_match is not None:') !== FALSE, 'M17-07A smoke submitted assertion must treat hidden version input as editable rollback evidence.');
m17_07a_check(strpos($submitted_assert_function, 'if SUBMITTED_STATUS_PATTERN.search(page) is None:') !== FALSE, 'M17-07A smoke submitted assertion must require submitted status marker when no hidden version remains.');
m17_07a_check(strpos($submitted_assert_function, 'auditee assignment did not expose version after submit') === FALSE, 'M17-07A smoke submitted assertion must not expect an editable hidden version after successful submit.');
m17_07a_check(strpos($submitted_assert_function, 'auditee assignment version did not advance after submit') === FALSE, 'M17-07A smoke submitted assertion must not require hidden version advancement on the submitted read-only page.');
$submit_post_offset = strpos($smoke, 'submit_request(opener, assignment_url + "/submit"');
$submitted_assert_offset = strpos($smoke, 'assert_submitted_assignment(opener, assignment_url');
m17_07a_check($submit_post_offset !== FALSE && $submitted_assert_offset !== FALSE && $submit_post_offset < $submitted_assert_offset, 'M17-07A smoke must assert submitted status after the submit POST.');
$submission_offset = strpos($smoke, 'submit_auditee_assignment(auditee_a, base_url, args.auditee_a_assignment)');
$auditor_a_open_offset = strpos($smoke, 'expect_page(auditor_a, base_url + "/auditor/spmi/assignment/" + args.auditor_a_assignment');
$auditor_a_evidence_offset = strpos($smoke, 'assert_evidence_urls(auditor_a_page, auditee_a_evidence_urls)');
$submission_b_offset = strpos($smoke, 'submit_auditee_assignment(auditee_b, base_url, args.auditee_b_assignment)');
$auditor_b_open_offset = strpos($smoke, 'expect_page(auditor_b, base_url + "/auditor/spmi/assignment/" + args.auditor_b_assignment');
m17_07a_check($submission_offset !== FALSE && $auditor_a_open_offset !== FALSE && $submission_offset < $auditor_a_open_offset, 'M17-07A smoke must submit Auditee A before Auditor A opens the assignment.');
m17_07a_check($auditor_a_open_offset !== FALSE && $auditor_a_evidence_offset !== FALSE && $auditor_a_open_offset < $auditor_a_evidence_offset, 'M17-07A smoke must inspect Auditor A owned assignment page for Auditee A evidence URLs after opening it.');
m17_07a_check($submission_b_offset !== FALSE && $auditor_b_open_offset !== FALSE && $submission_b_offset < $auditor_b_open_offset, 'M17-07A smoke must submit Auditee B before Auditor B opens the assignment.');
$m17_06_graph_offset = strpos($plan, 'M17-06 — Report Snapshot and Auditee Result');
$m17_05a_graph_offset = strpos($plan, 'M17-05A — Auditor Evidence Read Parity');
$m17_07_graph_offset = strpos($plan, 'M17-07 — Runtime and End-to-End Hardening');
$m17_08_graph_offset = strpos($plan, 'M17-08 — Cutover Readiness Audit');
m17_07a_check($m17_06_graph_offset !== FALSE, 'M17 plan graph must include the current M17-06 dependency node.');
m17_07a_check($m17_05a_graph_offset !== FALSE, 'M17 plan graph must include the current corrective M17-05A dependency node.');
m17_07a_check($m17_07_graph_offset !== FALSE, 'M17 plan graph must include the current M17-07 dependency node.');
m17_07a_check($m17_08_graph_offset !== FALSE, 'M17 plan graph must include the current M17-08 terminal dependency node.');
m17_07a_check($m17_06_graph_offset < $m17_05a_graph_offset && $m17_05a_graph_offset < $m17_07_graph_offset && $m17_07_graph_offset < $m17_08_graph_offset, 'M17 plan must preserve the current dependency graph sequence M17-06 -> M17-05A -> M17-07 -> M17-08.');
m17_07a_check(strpos($plan, "M17-07 Runtime Hardening\n\nNOT_STARTED") !== FALSE, 'M17 plan status table must keep M17-07 actionable not started under its current status-table title after M17-05A PASS.');
m17_07a_check(strpos($plan, "M17-08 Cutover Readiness Audit\n\nNOT_STARTED") !== FALSE, 'M17 plan status table must keep M17-08 not started under its current status-table title.');
m17_07a_check(strpos($plan, 'M17-07 is eligible after this PASS') === FALSE, 'M17-06 must not declare M17-07 eligible before M17-07A passes.');
$historical_m17_07_blocked_log = "### 2026-08-05 00:02 — M17-07\n- Status: BLOCKED\n- Branch: dev\n- Start SHA: see Git history\n- End SHA: see Git history (self SHA unavailable in preimage)\n- Commit: docs(m17): mark M17-07 runtime fixture blocker\n- Files: docs/plan/m17-spmi-workspace-parity-master-plan.md\n- Tests: STATIC SOURCE REVIEW only; GIT_MASTER=1 git diff --check PASS; markdown lint scripts unavailable in repo root because there is no package.json or bun script in /home/steven/Documents/Audit-Mutu-Internal; no Docker/DB/migration/HTTP/browser mutation was run\n- Runtime verification: not run\n- Notes: Static preflight evidence found Docker/Compose isolation available, database_dummy.sql:1-3 says it does not create demo users, database_dummy.sql:7-8 selects pre-existing auditor/auditee users, application/services/Auth_service.php:16-25 requires a stored password hash for login, and application/services/User_service.php:47-68 shows user creation exists as application behavior; however no repository-controlled isolated fixture/bootstrap CLI or entrypoint exists to provision synthetic roles and the minimal SPMI runtime graph in a uniquely named fresh Compose project with teardown, so the required M17-07 multi-role runtime graph cannot be provisioned safely without ad hoc test data or shared-state changes; minimum resolution is a repository-controlled disposable fixture bootstrap that creates the synthetic roles and minimal SPMI graph in a fresh Compose project with teardown, then rerun all required runtime lanes; M17-08 remains NOT_STARTED and was not made eligible\n- Next gate: STOP: M17-08 must not start; no post-M17 milestone may start";
m17_07a_check(strpos($plan, $historical_m17_07_blocked_log) !== FALSE, 'Historical M17-07 BLOCKED execution log must remain unchanged in full.');
m17_07a_check(strpos($plan, "M17-08 Cutover Readiness Audit\n\nNOT_STARTED") !== FALSE, 'M17-08 must remain NOT_STARTED.');

fwrite(STDOUT, "M17-07A runtime fixture static contract passed.\n");
