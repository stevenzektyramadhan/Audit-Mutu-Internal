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
foreach (['legacy_ami_archive_runs' => 17071, 'legacy_ami_archive_tasks' => 17072, 'legacy_ami_archive_answers' => 17073, 'legacy_ami_archive_issues' => 17074] as $archive_table => $archive_id) {
    m17_07a_check(strpos($fixture, 'INSERT INTO `' . $archive_table . '`') !== FALSE, 'M17-07A fixture coverage gap: missing source-backed read-only legacy archive rows for ' . $archive_table . '.');
    m17_07a_check(strpos($fixture, '(' . $archive_id . ',') !== FALSE, 'M17-07A fixture coverage gap: missing deterministic legacy archive row ID ' . $archive_id . ' for ' . $archive_table . '.');
}

m17_07a_check(strpos($runner, 'M17_07A_FIXTURE_PASSWORD=') !== FALSE, 'M17-07A runner must own the fixture-only plaintext password.');
preg_match('/M17_07A_FIXTURE_PASSWORD=([^\n]+)/', $runner, $password_match);
m17_07a_check(isset($password_match[1]), 'M17-07A runner fixture password is unreadable.');
preg_match('/\$2[aby]\$\d\d\$[^\'\s,;]+/', $fixture, $hash_match);
m17_07a_check(isset($hash_match[0]) && m17_07a_password_valid($hash_match[0], trim($password_match[1], "\"'")), 'M17-07A fixture bcrypt hash must authenticate with the runner password.');
m17_07a_check(strpos($runner, 'COMPOSE_BAKE=false docker compose -p "$project" -f "$compose_file" "$@"') !== FALSE, 'M17-07A runner must disable Compose Bake locally for every fixture docker compose call.');
m17_07a_check(strpos($runner, 'M17_07A_REUSE_APP_IMAGE') !== FALSE, 'M17-07A runner must expose an opt-in caller-specified app image reuse path.');
m17_07a_check(strpos($runner, 'docker image tag "$M17_07A_REUSE_APP_IMAGE" "${project}-app:latest"') !== FALSE, 'M17-07A reuse path must tag only the caller image as the unique project app image.');
m17_07a_check(strpos($runner, 'compose up --no-build --detach') !== FALSE, 'M17-07A reuse path must start without a build or download.');
m17_07a_check(strpos($runner, 'compose up --build --detach') !== FALSE, 'M17-07A default start path must retain its build behavior.');
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
m17_07a_check(strpos($smoke, 'UPLOAD_VALIDATION_MESSAGE = "Bukti harus berupa PDF, JPEG, atau PNG maksimal 5 MiB."') !== FALSE, 'M17-07A smoke must assert the exact source-backed invalid evidence upload message.');
m17_07a_check(strpos($smoke, 'UPLOAD_CAP_MESSAGE = "Maksimal 5 bukti per item."') !== FALSE, 'M17-07A smoke must assert the exact source-backed evidence cap message.');
m17_07a_check(strpos($smoke, 'def evidence_multipart_body(') !== FALSE, 'M17-07A smoke must centralize real multipart evidence upload body construction.');
m17_07a_check(strpos($smoke, 'name="csrf_test_name"') !== FALSE, 'M17-07A evidence upload security lanes must use the current csrf_test_name multipart field.');
m17_07a_check(strpos($smoke, 'name="version"') !== FALSE, 'M17-07A evidence upload security lanes must use the current version multipart field.');
m17_07a_check(strpos($smoke, 'name=\"evidence\"; filename=\"{filename}\"') !== FALSE, 'M17-07A evidence upload security lanes must use the current evidence multipart file field.');
m17_07a_check(strpos($smoke, 'def assert_evidence_upload_security_lanes(') !== FALSE, 'M17-07A smoke must define auditee evidence upload security lanes before the valid upload flow.');
$upload_security_helper_offset = strpos($smoke, 'def assert_evidence_upload_security_lanes(');
$submit_function_offset_for_upload_security = strpos($smoke, 'def submit_auditee_assignment(');
m17_07a_check($upload_security_helper_offset !== FALSE && $submit_function_offset_for_upload_security !== FALSE && $upload_security_helper_offset < $submit_function_offset_for_upload_security, 'M17-07A smoke must define evidence upload security lanes before submit_auditee_assignment().');
$upload_security_helper_body = substr($smoke, $upload_security_helper_offset, $submit_function_offset_for_upload_security - $upload_security_helper_offset);
foreach (['invalid-MIME auditee evidence upload', 'm17-07a.txt', 'text/plain', 'UPLOAD_VALIDATION_MESSAGE', 'oversize auditee evidence upload', '5 * 1024 * 1024 + 1', 'assert_missing_csrf_upload_denied', 'assert_stale_version_upload_rejected', 'for index in range(5):', 'sixth auditee evidence upload', 'UPLOAD_CAP_MESSAGE', 'return token, version, {item_ids[0]}'] as $token) {
    m17_07a_check(strpos($upload_security_helper_body, $token) !== FALSE, 'M17-07A evidence upload security helper missing token ' . $token . '.');
}
foreach (['def assert_missing_csrf_upload_denied(', 'status != 403', 'source-backed CI CSRF status', 'def assert_stale_version_upload_rejected(', 'CONFLICT_MESSAGE'] as $token) {
    m17_07a_check(strpos($smoke, $token) !== FALSE, 'M17-07A evidence upload security support missing token ' . $token . '.');
}
$upload_rejection_offset = strpos($smoke, 'def assert_evidence_upload_rejected(');
$missing_csrf_offset = strpos($smoke, 'def assert_missing_csrf_upload_denied(');
$stale_rejection_offset = strpos($smoke, 'def assert_stale_version_upload_rejected(');
$upload_security_lanes_offset = strpos($smoke, 'def assert_evidence_upload_security_lanes(');
m17_07a_check($upload_rejection_offset !== FALSE && $missing_csrf_offset !== FALSE && $upload_rejection_offset < $missing_csrf_offset, 'M17-07A smoke must define the evidence upload rejection helper before missing-CSRF support.');
m17_07a_check($stale_rejection_offset !== FALSE && $upload_security_lanes_offset !== FALSE && $stale_rejection_offset < $upload_security_lanes_offset, 'M17-07A smoke must define stale upload rejection before evidence upload security lanes.');
$upload_rejection_body = substr($smoke, $upload_rejection_offset, $missing_csrf_offset - $upload_rejection_offset);
$stale_rejection_body = substr($smoke, $stale_rejection_offset, $upload_security_lanes_offset - $stale_rejection_offset);
m17_07a_check(strpos($upload_rejection_body, 'status, location, context, first_assignment_page, refreshed_token, observed_version = upload_evidence_variant(') !== FALSE, 'M17-07A upload rejection helper must inspect the first assignment page fetched after redirect.');
m17_07a_check(strpos($upload_rejection_body, 'if expected_message not in first_assignment_page:') !== FALSE, 'M17-07A upload rejection helper must assert flash rejection message on the first assignment page.');
m17_07a_check(strpos($upload_rejection_body, 'page = expect_page(opener, assignment_url, "M17-07A Runtime Package")') === FALSE, 'M17-07A upload rejection helper must not consume one-shot flash with a second assignment GET.');
m17_07a_check(strpos($stale_rejection_body, 'status, location, context, first_assignment_page, refreshed_token, observed_version = upload_evidence_variant(') !== FALSE, 'M17-07A stale upload rejection helper must inspect the first assignment page fetched after redirect.');
m17_07a_check(strpos($stale_rejection_body, 'if CONFLICT_MESSAGE not in first_assignment_page:') !== FALSE, 'M17-07A stale upload rejection helper must assert flash conflict message on the first assignment page.');
m17_07a_check(strpos($stale_rejection_body, 'page = expect_page(opener, assignment_url, "M17-07A Runtime Package")') === FALSE, 'M17-07A stale upload rejection helper must not consume one-shot flash with a second assignment GET.');
m17_07a_check(strpos($upload_security_helper_body, 'database_schema.sql') === FALSE && strpos($upload_security_helper_body, 'database_dummy.sql') === FALSE && strpos($upload_security_helper_body, 'stored_path') === FALSE, 'M17-07A evidence upload security helper must not derive IDs or paths from schema, fixture SQL, or storage internals.');
m17_07a_check(strpos($smoke, 'def save_incomplete_auditee_draft(') !== FALSE, 'M17-07A smoke must define an incomplete Auditee draft helper before the full submit flow.');
m17_07a_check(strpos($smoke, 'assignment_url + "/save"') !== FALSE, 'M17-07A smoke incomplete draft helper must POST to the assignment save endpoint.');
m17_07a_check(strpos($smoke, 'M17-07A incomplete draft') !== FALSE, 'M17-07A smoke incomplete draft helper must persist one recognizable realization without evidence.');
m17_07a_check(strpos($smoke, 'expected redirect after incomplete auditee draft save') !== FALSE, 'M17-07A smoke incomplete draft save must require redirect semantics instead of accepting 200.');
m17_07a_check(strpos($smoke, 'assert_assignment_redirect_location("incomplete auditee draft save"') !== FALSE, 'M17-07A smoke incomplete draft save must require redirect to the exact assignment URL.');
m17_07a_check(strpos($smoke, 'auditee incomplete draft did not remain editable') !== FALSE, 'M17-07A smoke incomplete draft reload must require the hidden editable version input.');
m17_07a_check(strpos($smoke, 'auditee incomplete draft did not show draft status') !== FALSE, 'M17-07A smoke incomplete draft reload must require draft status.');
m17_07a_check(strpos($smoke, 'auditee incomplete draft did not keep submit controls available') !== FALSE, 'M17-07A smoke incomplete draft reload must require submit controls to remain available.');
m17_07a_check(strpos($smoke, 'def find_fixture_url_policy_item_id(page: str) -> str:') !== FALSE, 'M17-07A smoke must derive the fixture URL-policy item ID from current page fields.');
m17_07a_check(strpos($smoke, 'M17R-Q-URL') !== FALSE, 'M17-07A smoke must target the fixture URL-policy item by page-rendered question code.');
m17_07a_check(strpos($smoke, 'def assert_policy_rejection_before_valid_submit(') !== FALSE, 'M17-07A smoke must define an evidence-policy rejection helper before the full submit flow.');
m17_07a_check(strpos($smoke, 'fields = {"csrf_test_name": token, "version": version}') !== FALSE, 'M17-07A smoke policy rejection must submit the current CSRF token and hidden version.');
m17_07a_check(strpos($smoke, 'if item_id != url_policy_item_id:') !== FALSE, 'M17-07A smoke policy rejection must deliberately omit only the URL evidence for the derived URL-policy item.');
m17_07a_check(strpos($smoke, 'Bukti wajib sesuai kebijakan sebelum submit.') !== FALSE, 'M17-07A smoke must assert the exact evidence-policy rejection flash message.');
m17_07a_check(strpos($smoke, 'expected redirect after evidence-policy rejection submit') !== FALSE, 'M17-07A smoke policy rejection submit must require redirect semantics instead of accepting 200.');
m17_07a_check(strpos($smoke, 'assert_assignment_redirect_location("evidence-policy rejection submit"') !== FALSE, 'M17-07A smoke policy rejection submit must require redirect to the exact assignment URL.');
m17_07a_check(strpos($smoke, 'auditee policy rejection changed draft version') !== FALSE, 'M17-07A smoke policy rejection must prove the hidden version remains unchanged after rollback.');
m17_07a_check(strpos($smoke, 'auditee policy rejection did not show draft status') !== FALSE, 'M17-07A smoke policy rejection reload must require draft status.');
m17_07a_check(strpos($smoke, 'auditee policy rejection did not keep submit controls available') !== FALSE, 'M17-07A smoke policy rejection reload must require submit controls to remain available.');
m17_07a_check(strpos($smoke, 'return csrf(page), observed_version') !== FALSE, 'M17-07A smoke policy rejection helper must return refreshed csrf(page) and observed_version before valid uploads.');
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
m17_07a_check(strpos($smoke, 'def unauthenticated_opener() -> urllib.request.OpenerDirector:') !== FALSE, 'M17-07A smoke must define a cookie-free unauthenticated opener.');
m17_07a_check(strpos($smoke, 'return urllib.request.build_opener()') !== FALSE, 'M17-07A unauthenticated opener must not install a CookieJar or reuse authenticated cookies.');
m17_07a_check(strpos($smoke, 'def assert_unauthenticated_private_evidence_denied(download_url: str) -> None:') !== FALSE, 'M17-07A smoke must define unauthenticated direct private evidence denial helper.');
m17_07a_check(strpos($smoke, 'expected auth redirect HTTP 302/303/307') !== FALSE, 'M17-07A smoke must require CodeIgniter auth redirect semantics for unauthenticated private evidence download.');
m17_07a_check(strpos($smoke, "redirect('auth') with index_page='index.php'") !== FALSE, 'M17-07A smoke must verify unauthenticated private evidence redirects to same-origin auth entrypoint.');
m17_07a_check(strpos($smoke, 'def assert_private_evidence_traversal_denied(opener: urllib.request.OpenerDirector, download_url: str) -> None:') !== FALSE, 'M17-07A smoke must define route-level traversal/private evidence denial helper.');
m17_07a_check(strpos($smoke, 'download_path.rsplit("/", 2)[0]') !== FALSE, 'M17-07A traversal helper must derive the download route prefix from the rendered URL.');
foreach (['"abc"', '"..%2F1"', '"%2e%2e%2f1"', '"1/../../application/config/database.php"'] as $traversal_payload) {
    m17_07a_check(strpos($smoke, $traversal_payload) !== FALSE, 'M17-07A traversal helper missing route payload ' . $traversal_payload . '.');
}
m17_07a_check(strpos($smoke, 'probe_url = origin + route_prefix + "/" + payload + "/download"') !== FALSE, 'M17-07A traversal helper must build route-level URL probes only.');
m17_07a_check(strpos($smoke, 'path traversal/private evidence download denial') !== FALSE, 'M17-07A smoke must label traversal/private evidence denial failures.');
m17_07a_check(strpos($smoke, 'if "%PDF-1.4" in content:') !== FALSE, 'M17-07A denial helper must fail if a denied private evidence response leaks PDF content.');
m17_07a_check(strpos($smoke, 'assert_unauthenticated_private_evidence_denied(auditor_a_download_url)') !== FALSE, 'M17-07A smoke must prove unauthenticated direct access to the rendered Auditor A private evidence URL is denied.');
m17_07a_check(strpos($smoke, 'assert_private_evidence_traversal_denied(auditor_a, auditor_a_download_url)') !== FALSE, 'M17-07A smoke must probe traversal/private evidence denial after extracting the rendered Auditor A private evidence URL.');
m17_07a_check(strpos($smoke, 'expect_denied(auditor_a, auditor_b_download_url)') !== FALSE, 'M17-07A smoke must prove Auditor A cannot download Auditor B private evidence URL when safely extractable.');
$unauth_download_helper = substr($smoke, strpos($smoke, 'def assert_unauthenticated_private_evidence_denied(download_url: str) -> None:'), strpos($smoke, 'def assert_private_evidence_traversal_denied(opener: urllib.request.OpenerDirector, download_url: str) -> None:') - strpos($smoke, 'def assert_unauthenticated_private_evidence_denied(download_url: str) -> None:'));
m17_07a_check(strpos($unauth_download_helper, 'no_redirect_request(unauthenticated_opener(), download_url, None, None)') !== FALSE, 'M17-07A smoke unauthenticated private evidence helper must disable redirect following.');
m17_07a_check(strpos($unauth_download_helper, 'if status not in (302, 303, 307):') !== FALSE, 'M17-07A smoke unauthenticated private evidence helper must require redirect status.');
m17_07a_check(strpos($unauth_download_helper, 'login_parts.path not in ("/index.php/auth", "/index.php/auth/login")') !== FALSE, 'M17-07A smoke unauthenticated private evidence helper must require source-aligned auth entrypoint on same origin.');
m17_07a_check(strpos($unauth_download_helper, 'if login_parts.query or login_parts.fragment:') !== FALSE, 'M17-07A smoke unauthenticated private evidence helper must reject redirect targets with query or fragment.');
m17_07a_check(strpos($unauth_download_helper, 'if "%PDF-1.4" in content:') !== FALSE, 'M17-07A smoke unauthenticated private evidence helper must reject leaked PDF bytes.');
$owner_download_call_offset = strpos($smoke, 'assert_private_evidence_download(auditor_a, auditor_a_download_url)');
$unauth_download_call_offset = strpos($smoke, 'assert_unauthenticated_private_evidence_denied(auditor_a_download_url)');
$traversal_download_call_offset = strpos($smoke, 'assert_private_evidence_traversal_denied(auditor_a, auditor_a_download_url)');
$cross_auditor_assignment_denial_offset = strpos($smoke, 'expect_denied(auditor_a, base_url + "/auditor/spmi/assignment/" + args.auditor_b_assignment)');
m17_07a_check($owner_download_call_offset !== FALSE && $unauth_download_call_offset !== FALSE && $owner_download_call_offset < $unauth_download_call_offset, 'M17-07A smoke must extract and prove authorized owner download before unauthenticated direct denial.');
m17_07a_check($unauth_download_call_offset !== FALSE && $traversal_download_call_offset !== FALSE && $unauth_download_call_offset < $traversal_download_call_offset, 'M17-07A smoke must run unauthenticated direct denial before traversal/private evidence probes.');
m17_07a_check($traversal_download_call_offset !== FALSE && $cross_auditor_assignment_denial_offset !== FALSE && $traversal_download_call_offset < $cross_auditor_assignment_denial_offset, 'M17-07A smoke must retain Auditor A assignment ownership denial after new private download denial probes.');
m17_07a_check(strpos($smoke, 'def assert_return_resubmit_and_stale_finalize_rejection(') !== FALSE, 'M17-07A smoke must define the return/resubmit/stale-finalize runtime proof helper.');
m17_07a_check(strpos($smoke, 'SUBMISSION_VERSION_PATTERN = re.compile') !== FALSE, 'M17-07A smoke must parse auditor return submission_version from the rendered form.');
m17_07a_check(strpos($smoke, 'SOURCE_SUBMISSION_VERSION_PATTERN = re.compile') !== FALSE, 'M17-07A smoke must parse stale-finalize source_submission_version from the rendered auditor form.');
m17_07a_check(strpos($smoke, 'name="source_submission_version"') !== FALSE, 'M17-07A smoke source_submission_version pattern must be compatible with the rendered form field name.');
m17_07a_check(strpos($smoke, 'ASSESSMENT_ITEM_PATTERN = re.compile') !== FALSE, 'M17-07A smoke must parse auditor assessment item IDs from rendered assessment fields.');
m17_07a_check(strpos($smoke, 'CONFLICT_MESSAGE = "Data telah diperbarui di sesi lain. Muat ulang halaman lalu coba lagi."') !== FALSE, 'M17-07A smoke must assert the exact stale-finalization conflict message.');
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
$submitted_assert_offset = strpos($smoke, 'assert_submitted_assignment(opener, assignment_url');
$draft_helper_offset = strpos($smoke, 'def save_incomplete_auditee_draft(');
$submitted_assert_offset = strpos($smoke, 'def assert_submitted_assignment(');
m17_07a_check($draft_helper_offset !== FALSE && $submitted_assert_offset !== FALSE && $draft_helper_offset < $submitted_assert_offset, 'M17-07A smoke must define save_incomplete_auditee_draft() before assert_submitted_assignment().');
$draft_helper_body = substr($smoke, $draft_helper_offset, $submitted_assert_offset - $draft_helper_offset);
m17_07a_check(strpos($draft_helper_body, 'return csrf(page), observed_version') !== FALSE, 'M17-07A smoke incomplete draft helper must return refreshed csrf(page) and observed_version.');
$submit_function_offset = strpos($smoke, 'def submit_auditee_assignment(');
$submit_function_end = strpos($smoke, 'def assert_return_resubmit_and_stale_finalize_rejection(', $submit_function_offset);
m17_07a_check($submit_function_offset !== FALSE && $submit_function_end !== FALSE && $submit_function_offset < $submit_function_end, 'M17-07A smoke must define submit_auditee_assignment() before assert_return_resubmit_and_stale_finalize_rejection().');
$submit_function_body = substr($smoke, $submit_function_offset, $submit_function_end - $submit_function_offset);
m17_07a_check(strpos($submit_function_body, 'token, version = save_incomplete_auditee_draft(opener, assignment_url, token, version, item_ids[0])') !== FALSE, 'M17-07A smoke submit helper must assign token, version from save_incomplete_auditee_draft().');
$save_call_offset = strpos($submit_function_body, 'token, version = save_incomplete_auditee_draft(opener, assignment_url, token, version, item_ids[0])');
$upload_loop_offset = strpos($submit_function_body, 'for item_id in item_ids:');
$first_upload_offset = strpos($submit_function_body, 'upload_evidence(opener, assignment_url');
$submit_post_in_body_offset = strpos($submit_function_body, 'submit_request(opener, submit_url');
$submitted_assert_in_body_offset = strpos($submit_function_body, 'assert_submitted_assignment(opener, assignment_url');
m17_07a_check($save_call_offset !== FALSE && $first_upload_offset !== FALSE && $save_call_offset < $first_upload_offset, 'M17-07A smoke must save and verify the incomplete draft before the first full evidence upload in submit_auditee_assignment().');
m17_07a_check($submit_post_in_body_offset !== FALSE && $submitted_assert_in_body_offset !== FALSE && $submit_post_in_body_offset < $submitted_assert_in_body_offset, 'M17-07A smoke must assert submitted status after the submit POST.');
m17_07a_check($save_call_offset !== FALSE && $upload_loop_offset !== FALSE && $submit_post_in_body_offset !== FALSE && $save_call_offset < $upload_loop_offset && $save_call_offset < $submit_post_in_body_offset, 'M17-07A smoke submit helper must save the incomplete draft before uploads and the final submit POST.');
$policy_call_offset = strpos($submit_function_body, 'token, version = assert_policy_rejection_before_valid_submit(opener, assignment_url, item_ids)');
m17_07a_check($policy_call_offset !== FALSE && $save_call_offset !== FALSE && $upload_loop_offset !== FALSE && $submit_post_in_body_offset !== FALSE && $save_call_offset < $policy_call_offset && $policy_call_offset < $upload_loop_offset && $policy_call_offset < $submit_post_in_body_offset, 'M17-07A smoke submit helper must reject the missing URL-policy evidence after the draft save and before valid uploads/submission.');
$upload_security_call_offset = strpos($submit_function_body, 'token, version, preloaded_evidence_item_ids = assert_evidence_upload_security_lanes(opener, assignment_url, base_url, item_ids, token, version)');
m17_07a_check($upload_security_call_offset !== FALSE && $save_call_offset !== FALSE && $upload_loop_offset !== FALSE && $submit_post_in_body_offset !== FALSE && $save_call_offset < $upload_security_call_offset && $upload_security_call_offset < $upload_loop_offset && $upload_security_call_offset < $submit_post_in_body_offset, 'M17-07A smoke submit helper must run evidence upload security lanes before valid uploads/submission.');
m17_07a_check(strpos($submit_function_body, 'if item_id in preloaded_evidence_item_ids:') !== FALSE, 'M17-07A smoke submit helper must preserve valid submit after five-file-cap preload by skipping already-populated item IDs.');
m17_07a_check(strpos($submit_function_body, 'submit_url, preview_fields, confirm_page = assert_confirmation_preview(') !== FALSE, 'M17-07A smoke submit helper must drive the final auditee submit through the confirmation preview GET.');
m17_07a_check(strpos($submit_function_body, 'fields = {"csrf_test_name": csrf(confirm_page), "version": preview_fields["version"]}') !== FALSE, 'M17-07A smoke submit helper must post the confirmation-page CSRF plus preview version to the existing submit endpoint.');
m17_07a_check(strpos($submit_function_body, 'assert_confirm_denied_after_submission(opener, assignment_url, preview_fields, "submitted auditee confirmation read")') !== FALSE, 'M17-07A smoke submit helper must prove confirmation GET is denied after submit.');
$policy_helper_offset = strpos($smoke, 'def assert_policy_rejection_before_valid_submit(');
$submitted_assert_offset = strpos($smoke, 'def assert_submitted_assignment(');
m17_07a_check($policy_helper_offset !== FALSE && $submitted_assert_offset !== FALSE && $policy_helper_offset < $submitted_assert_offset, 'M17-07A smoke must define assert_policy_rejection_before_valid_submit() before assert_submitted_assignment().');
$policy_helper_body = substr($smoke, $policy_helper_offset, $submitted_assert_offset - $policy_helper_offset);
m17_07a_check(strpos($policy_helper_body, 'return csrf(page), observed_version') !== FALSE, 'M17-07A smoke policy rejection helper must return refreshed csrf(page) and observed_version from the rejection reload.');
$return_helper_offset = strpos($smoke, 'def assert_return_resubmit_and_stale_finalize_rejection(');
$evidence_assert_offset = strpos($smoke, 'def assert_evidence_urls(');
m17_07a_check($return_helper_offset !== FALSE && $evidence_assert_offset !== FALSE && $return_helper_offset < $evidence_assert_offset, 'M17-07A smoke must define return/resubmit/stale-finalize proof before the evidence URL assertion block.');
$return_helper_body = substr($smoke, $return_helper_offset, $evidence_assert_offset - $return_helper_offset);
foreach (['SUBMISSION_VERSION_PATTERN.search(auditor_page)', 'SOURCE_SUBMISSION_VERSION_PATTERN.search(auditor_page)', 'ASSESSMENT_ITEM_PATTERN.findall(auditor_page)', 'stale_source_submission_version = source_submission_version_match.group(1)', 'csrf(auditor_page)', 'auditor_assignment_url + "/return"', '"submission_version": submission_version_match.group(1)', '"reason": return_reason', 'submitted → returned_for_revision', 'Kirim ulang revisi', 'returned_version_match.group(1)', 'assert_confirmation_preview(', 'submit_request(auditee, resubmit_url', 'assert_resubmitted_assignment(auditee, auditee_assignment_url', 'csrf(auditor_page_after_resubmit)', '"version": stale_assessment_version', '"source_submission_version": stale_source_submission_version', 'auditor_assignment_url + "/finalize"', 'status not in (302, 303)', 'CONFLICT_MESSAGE not in stale_rejection_page', 'Penilaian SPMI berhasil difinalisasi.', 'Status submission</strong>: finalized', 'VERSION_PATTERN.search(stale_rejection_page)', 'auditor draft form after stale auditor finalization rejection'] as $token) {
    m17_07a_check(strpos($return_helper_body, $token) !== FALSE, 'M17-07A return/resubmit/stale-finalize helper missing token ' . $token . '.');
}
m17_07a_check(strpos($return_helper_body, 'resubmit_url, preview_fields, confirm_page = assert_confirmation_preview(') !== FALSE, 'M17-07A return/resubmit helper must drive returned revision through the confirmation preview GET.');
m17_07a_check(strpos($return_helper_body, 'submit_request(auditee, resubmit_url, form_data(fields), "application/x-www-form-urlencoded")') !== FALSE, 'M17-07A return/resubmit helper must post to the confirmation-selected existing resubmit endpoint.');
m17_07a_check(strpos($return_helper_body, 'assert_confirm_denied_after_submission(auditee, auditee_assignment_url, preview_fields, "resubmitted auditee confirmation read")') !== FALSE, 'M17-07A return/resubmit helper must prove confirmation GET is denied after resubmit.');
$source_capture_offset = strpos($return_helper_body, 'stale_source_submission_version = source_submission_version_match.group(1)');
$resubmit_offset = strpos($return_helper_body, 'resubmit_url, preview_fields, confirm_page = assert_confirmation_preview(');
$finalize_offset = strpos($return_helper_body, 'auditor_assignment_url + "/finalize"');
m17_07a_check($source_capture_offset !== FALSE && $resubmit_offset !== FALSE && $source_capture_offset < $resubmit_offset, 'M17-07A stale source_submission_version must be captured from the original auditor form before return/resubmit.');
m17_07a_check($source_capture_offset !== FALSE && $finalize_offset !== FALSE && $source_capture_offset < $finalize_offset, 'M17-07A stale finalize must replay the original pre-return source_submission_version token.');
m17_07a_check(strpos($smoke, 'returned_for_revision → resubmitted') !== FALSE, 'M17-07A smoke must assert resubmitted revision history on the auditee page.');
foreach (['stored_path', 'database_schema.sql', 'database_dummy.sql', 'spmi_reports', 'spmi_rtm'] as $forbidden_smoke_token) {
    m17_07a_check(strpos($return_helper_body, $forbidden_smoke_token) === FALSE, 'M17-07A return/resubmit/stale-finalize helper must not overreach into ' . $forbidden_smoke_token . '.');
}
$finalize_helper_offset = strpos($smoke, 'def assert_finalize_validation_success_and_immutability(');
m17_07a_check($finalize_helper_offset !== FALSE, 'M17-07A smoke must define a finalize validation/success/immutability helper.');
$finalize_block_offset = strpos($smoke, 'def current_assessment_form(page: str)');
m17_07a_check($finalize_block_offset !== FALSE && $finalize_block_offset < $finalize_helper_offset, 'M17-07A smoke must derive the current assessment form before the finalize proof helper.');
$finalize_helper_body = substr($smoke, $finalize_block_offset, $evidence_assert_offset - $finalize_block_offset);
    foreach (['current_assessment_form(auditor_page)', 'valid_assessment_fields(token, version, source_submission_version, item_ids)', 'auditor_assignment_url + "/finalize"', 'Semua item wajib diberi skor 1 sampai 4 sebelum finalisasi.', 'Uraian temuan wajib diisi untuk OB atau KTS sebelum finalisasi.', 'Rekomendasi wajib diisi untuk KTS sebelum finalisasi.', 'assert_assignment_redirect_location("invalid auditor finalization"', 'auditor draft form after invalid finalization did not remain editable', 'assessment[" + item_id + "][score]', 'assessment[" + item_id + "][finding_type]', 'assessment[" + item_id + "][finding]', 'assessment[" + item_id + "][recommendation]', 'Penilaian SPMI berhasil difinalisasi.', 'auditor finalized page still exposed editable version/source_submission_version fields', 'auditor finalized page did not render read-only disabled assessment controls', 'disabled', 'Finalisasi', 'Simpan draft', 'pre_final_assessment_version = version', 'pre_final_source_submission_version = source_submission_version', 'finalized UI intentionally exposes no editable version tokens; replay pre-final tokens as an authorization/immutability forgery', 'for action in ("save", "finalize"):', 'mutation_fields = valid_assessment_fields(csrf(finalized_page), pre_final_assessment_version, pre_final_source_submission_version, item_ids)', 'auditor finalized page reloaded before post-final forgery still exposed editable version/source_submission_version fields', 'post-finalized auditor " + action', 'CONFLICT_MESSAGE', 'SOURCE_SUBMISSION_VERSION_PATTERN.search(mutation_page) is not None', 'post-finalized auditor " + action + " forgery was not rejected as immutable/read-only'] as $token) {
        m17_07a_check(strpos($finalize_helper_body, $token) !== FALSE, 'M17-07A finalize validation/success/immutability helper missing token ' . $token . '.');
    }
    foreach (['str(int(version) + 1)', 'finalized_version', 'valid_assessment_fields(token, finalized_version'] as $forbidden_token) {
        m17_07a_check(strpos($finalize_helper_body, $forbidden_token) === FALSE, 'M17-07A finalize helper must not fabricate post-final versions or reuse stale pre-final CSRF via ' . $forbidden_token . '.');
    }
m17_07a_check(strpos($finalize_helper_body, 'database_schema.sql') === FALSE && strpos($finalize_helper_body, 'database_dummy.sql') === FALSE && strpos($finalize_helper_body, 'spmi_reports') === FALSE && strpos($finalize_helper_body, 'spmi_rtm') === FALSE, 'M17-07A finalize helper must not overreach into schema, fixture SQL, reports, or RTM.');
$report_lane_offset = strpos($smoke, 'def assert_report_snapshot_final_result_and_rtm(');
$main_offset = strpos($smoke, 'def main() -> int:');
m17_07a_check($report_lane_offset !== FALSE && $main_offset !== FALSE && $report_lane_offset < $main_offset, 'M17-07A smoke must define report snapshot/final-result/RTM helper before main().');
$report_lane_body = substr($smoke, $report_lane_offset, $main_offset - $report_lane_offset);
foreach (['REPORT_CREATE_PATTERN = re.compile', 'REPORT_DETAIL_PATTERN = re.compile', 'RTM_DETAIL_PATTERN = re.compile', 'OPTION_PATTERN_TEMPLATE', 'def same_origin_url(base_url: str, url: str) -> str:', 'def extract_option_value(page: str, marker: str, label: str) -> str:', 'Assessment finalized belum dilaporkan', 'REPORT_CREATE_PATTERN.search(reports_page)', 'same_origin_url(base_url, create_match.group(1))', 'base_url + "/lpmpi/spmi-reports"', 'submit_request(admin, create_url, form_data({"csrf_test_name": csrf(reports_page)}), "application/x-www-form-urlencoded")', 'expected redirect after admin LPMPI report create from finalized assessment', 'REPORT_DETAIL_PATTERN.search(urllib.parse.urlsplit(detail_url).path)', 'Laporan ini immutable. Detail dibaca dari snapshot M10/M17', 'base_url + "/lpmpi/spmi-reports/print/" + report_id', 'base_url + "/auditee/spmi/assignment/" + auditee_a_assignment_id + "/final-result"', 'Hasil akhir ini readonly dan dibaca dari snapshot laporan SPMI.', 'status != 404', 'Auditee B final-result cross-owner read must return 404', 'base_url + "/lpmpi/spmi-rtm/create"', 'Tambah RTM SPMI', 'extract_option_value(rtm_form, "M17-07A Admin LPMPI — admin_lpmpi", "admin LPMPI participant")', '"meeting_code": "M17-07A-RTM"', '"meeting_title": "M17-07A RTM source-backed report smoke"', '"meeting_date": "2026-08-09"', '"location": "M17-07A Runtime Room"', '"report_ids[]": report_id', '"participant_ids[]": participant_id', '"decisions[0][decision_text]": "M17-07A RTM decision from report snapshot"', '"decisions[0][action_text]": "M17-07A RTM action from report snapshot"', '"decisions[0][report_id]": report_id', '"decisions[0][report_item_id]": ""', 'base_url + "/lpmpi/spmi-rtm/store"', 'expected redirect after admin LPMPI RTM create from generated report', 'base_url + "/lpmpi/spmi-rtm"', 'RTM index did not expose created source-backed RTM detail URL', 'Laporan M10', 'Peserta snapshot', 'M17-07A RTM action from report snapshot'] as $token) {
    m17_07a_check(strpos($report_lane_body, $token) !== FALSE || strpos($smoke, $token) !== FALSE, 'M17-07A report/final-result/RTM helper missing token ' . $token . '.');
}
foreach (['database_schema.sql', 'database_dummy.sql', 'tests/fixtures', 'compose exec', 'mysql', 'stored_path'] as $forbidden_report_lane_token) {
    m17_07a_check(strpos($report_lane_body, $forbidden_report_lane_token) === FALSE, 'M17-07A report/final-result/RTM helper must derive runtime IDs from rendered HTTP data, not ' . $forbidden_report_lane_token . '.');
}
m17_07a_check(strpos($report_lane_body, 'M17R-V1 / M17R-S1 / M17R-P1') !== FALSE, 'M17-07A report/final-result helper must assert the source code display rendered by report/final-result views.');
m17_07a_check(strpos($report_lane_body, 'M17-07A Admin LPMPI — admin_lpmpi') !== FALSE, 'M17-07A report/final-result/RTM helper must match rendered RTM participant label semantics.');
$legacy_archive_lane_offset = strpos($smoke, 'def assert_legacy_ami_archive_read_only_lane(');
$main_offset = strpos($smoke, 'def main() -> int:');
m17_07a_check($legacy_archive_lane_offset !== FALSE && $main_offset !== FALSE && $legacy_archive_lane_offset < $main_offset, 'M17-07A smoke must define legacy archive read-only runtime helper before main().');
$legacy_archive_lane_body = substr($smoke, $legacy_archive_lane_offset, $main_offset - $legacy_archive_lane_offset);
foreach (['base_url + "/lpmpi/legacy-ami-archive"', 'admin-lpmpi@m17-07a.test', 'Arsip AMI Legacy', 'Preflight Arsip AMI Legacy', 'Rekonsiliasi Arsip AMI Legacy', 'Detail Run Arsip AMI Legacy', 'Tugas Arsip AMI Legacy', 'Browser arsip read-only', 'Legacy AMI tetap authoritative; tidak ada eksekusi backfill dari layar ini.', 'Archive Runs', 'Read-only count', 'Tidak menulis data', 'Daftar issue archive-owned', 'Tidak memperbaiki atau mengubah tabel legacy', 'Snapshot read-only dari legacy AMI', 'Tugas Archive', 'Issue Run', 'legacy archive index missing source-backed read-only/archive marker', 'not product failure', 'LEGACY_ARCHIVE_RUN_PATTERN.search(archive_page)', 'LEGACY_ARCHIVE_TASK_PATTERN.search(run_page)', 'same_origin_url(base_url, run_match.group(1))', 'same_origin_url(base_url, task_match.group(1))'] as $token) {
    m17_07a_check(strpos($legacy_archive_lane_body, $token) !== FALSE || strpos($smoke, $token) !== FALSE, 'M17-07A legacy archive read-only helper missing token ' . $token . '.');
}
foreach (['submit_request(', 'upload_request(', 'form_data(', 'csrf(', '/store', '/update', '/delete', '/transition', '/resolve'] as $forbidden_legacy_archive_token) {
    m17_07a_check(strpos($legacy_archive_lane_body, $forbidden_legacy_archive_token) === FALSE, 'M17-07A legacy archive helper must stay read-only and avoid ' . $forbidden_legacy_archive_token . '.');
}
$stale_call_offset = strpos($smoke, 'assert_return_resubmit_and_stale_finalize_rejection(auditor_a, auditee_a, base_url, args.auditor_a_assignment, args.auditee_a_assignment, auditor_a_page)');
$finalize_call_offset = strpos($smoke, 'assert_finalize_validation_success_and_immutability(auditor_a, base_url, args.auditor_a_assignment)');
$report_lane_call_offset = strpos($smoke, 'assert_report_snapshot_final_result_and_rtm(admin_lpmpi, auditee_a, auditee_b, base_url, args.auditee_a_assignment)');
$legacy_archive_lane_call_offset = strpos($smoke, 'assert_legacy_ami_archive_read_only_lane(admin_lpmpi, base_url)');
$download_refresh_offset = strpos($smoke, 'auditor_a_page = expect_page(auditor_a, base_url + "/auditor/spmi/assignment/" + args.auditor_a_assignment, "M17-07A Runtime Package")', $stale_call_offset === FALSE ? 0 : $stale_call_offset);
m17_07a_check($stale_call_offset !== FALSE && $finalize_call_offset !== FALSE && $download_refresh_offset !== FALSE && $stale_call_offset < $finalize_call_offset && $finalize_call_offset < $download_refresh_offset, 'M17-07A smoke must run finalize validation/success/immutability after stale rejection and before retained evidence download proof.');
m17_07a_check($finalize_call_offset !== FALSE && $report_lane_call_offset !== FALSE && $download_refresh_offset !== FALSE && $finalize_call_offset < $report_lane_call_offset && $report_lane_call_offset < $download_refresh_offset, 'M17-07A smoke must run report snapshot/final-result/RTM lane after finalized assessment and before retained evidence download proof.');
$submission_offset = strpos($smoke, 'submit_auditee_assignment(auditee_a, base_url, args.auditee_a_assignment)');
$submission_a_policy_offset = strpos($smoke, 'submit_auditee_assignment(auditee_a, base_url, args.auditee_a_assignment, reject_missing_url_policy=True)');
$auditor_a_open_offset = strpos($smoke, 'expect_page(auditor_a, base_url + "/auditor/spmi/assignment/" + args.auditor_a_assignment');
$auditor_a_evidence_offset = strpos($smoke, 'assert_evidence_urls(auditor_a_page, auditee_a_evidence_urls)');
$return_proof_call_offset = strpos($smoke, 'assert_return_resubmit_and_stale_finalize_rejection(auditor_a, auditee_a, base_url, args.auditor_a_assignment, args.auditee_a_assignment, auditor_a_page)');
$auditor_a_download_offset = strpos($smoke, 'assert_private_evidence_download(auditor_a, auditor_a_download_url)');
$submission_b_offset = strpos($smoke, 'submit_auditee_assignment(auditee_b, base_url, args.auditee_b_assignment)');
$auditor_b_open_offset = strpos($smoke, 'expect_page(auditor_b, base_url + "/auditor/spmi/assignment/" + args.auditor_b_assignment');
m17_07a_check($legacy_archive_lane_call_offset !== FALSE && $submission_a_policy_offset !== FALSE && $legacy_archive_lane_call_offset < $submission_a_policy_offset, 'M17-07A smoke must run legacy archive read-only lane before mutating normal SPMI runtime lanes.');
m17_07a_check($submission_a_policy_offset !== FALSE && $auditor_a_open_offset !== FALSE && $submission_a_policy_offset < $auditor_a_open_offset, 'M17-07A smoke must run Auditee A policy rejection plus valid submit before Auditor A opens the assignment.');
m17_07a_check($submission_b_offset !== FALSE && strpos($submission_b_offset === FALSE ? '' : substr($smoke, $submission_b_offset, 140), 'reject_missing_url_policy=True') === FALSE, 'M17-07A smoke must not weaken Auditee B by running the Auditee A policy-rejection lane there.');
m17_07a_check($auditor_a_open_offset !== FALSE && $auditor_a_evidence_offset !== FALSE && $auditor_a_open_offset < $auditor_a_evidence_offset, 'M17-07A smoke must inspect Auditor A owned assignment page for Auditee A evidence URLs after opening it.');
m17_07a_check($auditor_a_open_offset !== FALSE && $auditor_a_evidence_offset !== FALSE && $return_proof_call_offset !== FALSE && $auditor_a_download_offset !== FALSE && $auditor_a_open_offset < $auditor_a_evidence_offset && $auditor_a_evidence_offset < $return_proof_call_offset && $return_proof_call_offset < $auditor_a_download_offset, 'M17-07A smoke must run return/resubmit/stale-finalize proof after Auditor A initial open/evidence assertion and before the retained evidence download proof.');
m17_07a_check($submission_b_offset !== FALSE && $auditor_b_open_offset !== FALSE && $submission_b_offset < $auditor_b_open_offset, 'M17-07A smoke must submit Auditee B before Auditor B opens the assignment.');
m17_07a_check(strpos($smoke, 'def assert_confirmation_preview(') !== FALSE, 'M17-07A smoke must define a confirmation preview helper for 07F GET proof.');
m17_07a_check(strpos($smoke, 'def assert_confirm_creation_not_lazy(opener: urllib.request.OpenerDirector, assignment_url: str) -> None:') !== FALSE, 'M17-07A smoke must define an owned no-lazy-creation confirmation helper.');
m17_07a_check(strpos($smoke, 'def assert_confirm_foreign_not_found(opener: urllib.request.OpenerDirector, assignment_url: str) -> None:') !== FALSE, 'M17-07A smoke must define a foreign-owner confirmation 404 helper.');
m17_07a_check(strpos($smoke, 'assert_confirm_foreign_not_found(auditee_b, auditee_a_assignment_url)') !== FALSE, 'M17-07A smoke must prove Auditee B cannot confirm Auditee A assignment.');
m17_07a_check(strpos($smoke, 'assert_confirm_creation_not_lazy(auditee_b, base_url + "/auditee/spmi/assignment/" + args.auditee_b_assignment)') !== FALSE, 'M17-07A smoke must prove confirmation GET does not lazily create submissions for owned configured assignments.');
$m17_06_graph_offset = strpos($plan, 'M17-06 — Report Snapshot and Auditee Result');
$m17_05a_graph_offset = strpos($plan, 'M17-05A — Auditor Evidence Read Parity');
$m17_07_graph_offset = strpos($plan, 'M17-07 — Runtime and End-to-End Hardening');
$m17_08_graph_offset = strpos($plan, 'M17-08 — Cutover Readiness Audit');
m17_07a_check($m17_06_graph_offset !== FALSE, 'M17 plan graph must include the current M17-06 dependency node.');
m17_07a_check($m17_05a_graph_offset !== FALSE, 'M17 plan graph must include the current corrective M17-05A dependency node.');
m17_07a_check($m17_07_graph_offset !== FALSE, 'M17 plan graph must include the current M17-07 dependency node.');
m17_07a_check($m17_08_graph_offset !== FALSE, 'M17 plan graph must include the current M17-08 terminal dependency node.');
m17_07a_check($m17_06_graph_offset < $m17_05a_graph_offset && $m17_05a_graph_offset < $m17_07_graph_offset && $m17_07_graph_offset < $m17_08_graph_offset, 'M17 plan must preserve the current dependency graph sequence M17-06 -> M17-05A -> M17-07 -> M17-08.');
m17_07a_check(strpos($plan, "M17-07 Runtime Hardening\n\nPASS") !== FALSE, 'M17 plan status table must record M17-07 PASS under its current status-table title after the full runtime hardening lanes pass.');
m17_07a_check(strpos($plan, "M17-08 Cutover Readiness Audit\n\nNOT_STARTED") !== FALSE, 'M17 plan status table must keep M17-08 not started under its current status-table title.');
m17_07a_check(strpos($plan, 'M17-07 is eligible after this PASS') === FALSE, 'M17-06 must not declare M17-07 eligible before M17-07A passes.');
$historical_m17_07_blocked_log = "### 2026-08-05 00:02 — M17-07\n- Status: BLOCKED\n- Branch: dev\n- Start SHA: see Git history\n- End SHA: see Git history (self SHA unavailable in preimage)\n- Commit: docs(m17): mark M17-07 runtime fixture blocker\n- Files: docs/plan/m17-spmi-workspace-parity-master-plan.md\n- Tests: STATIC SOURCE REVIEW only; GIT_MASTER=1 git diff --check PASS; markdown lint scripts unavailable in repo root because there is no package.json or bun script in /home/steven/Documents/Audit-Mutu-Internal; no Docker/DB/migration/HTTP/browser mutation was run\n- Runtime verification: not run\n- Notes: Static preflight evidence found Docker/Compose isolation available, database_dummy.sql:1-3 says it does not create demo users, database_dummy.sql:7-8 selects pre-existing auditor/auditee users, application/services/Auth_service.php:16-25 requires a stored password hash for login, and application/services/User_service.php:47-68 shows user creation exists as application behavior; however no repository-controlled isolated fixture/bootstrap CLI or entrypoint exists to provision synthetic roles and the minimal SPMI runtime graph in a uniquely named fresh Compose project with teardown, so the required M17-07 multi-role runtime graph cannot be provisioned safely without ad hoc test data or shared-state changes; minimum resolution is a repository-controlled disposable fixture bootstrap that creates the synthetic roles and minimal SPMI graph in a fresh Compose project with teardown, then rerun all required runtime lanes; M17-08 remains NOT_STARTED and was not made eligible\n- Next gate: STOP: M17-08 must not start; no post-M17 milestone may start";
m17_07a_check(strpos($plan, $historical_m17_07_blocked_log) !== FALSE, 'Historical M17-07 BLOCKED execution log must remain unchanged in full.');
m17_07a_check(strpos($plan, "M17-08 Cutover Readiness Audit\n\nNOT_STARTED") !== FALSE, 'M17-08 must remain NOT_STARTED.');

fwrite(STDOUT, "M17-07A runtime fixture static contract passed.\n");
