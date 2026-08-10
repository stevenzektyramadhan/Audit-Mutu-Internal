#!/usr/bin/env python3
from __future__ import annotations

import argparse
import html
import http.cookiejar
import os
import re
import sys
import urllib.error
import urllib.parse
import urllib.request

PASSWORD = os.environ["M17_07A_FIXTURE_PASSWORD"]
CSRF_PATTERN = re.compile(r'name="csrf_test_name" value="([^"]+)"')
ITEM_PATTERN = re.compile(r'name="realization\[(\d+)\]"')
ASSESSMENT_ITEM_PATTERN = re.compile(r'name="assessment\[(\d+)\]\[score\]"')
URL_POLICY_ITEM_PATTERN = re.compile(
    r'M17R-Q-URL(?:(?!name="realization\[).)*name="realization\[(\d+)\]"|name="realization\[(\d+)\]"(?:(?!name="realization\[).)*M17R-Q-URL',
    re.DOTALL,
)
VERSION_PATTERN = re.compile(r'name="version" value="(\d+)"')
SUBMISSION_VERSION_PATTERN = re.compile(r'name="submission_version" value="(\d+)"')
SOURCE_SUBMISSION_VERSION_PATTERN = re.compile(r'name="source_submission_version" value="(\d+)"')
SUBMITTED_STATUS_PATTERN = re.compile(r'\bsubmitted\b|\bdiisi\b|\bterkirim\b', re.IGNORECASE)
RESUBMITTED_STATUS_PATTERN = re.compile(r'\bresubmitted\b|\bdikirim ulang\b', re.IGNORECASE)
CONFLICT_MESSAGE = "Data telah diperbarui di sesi lain. Muat ulang halaman lalu coba lagi."
UPLOAD_VALIDATION_MESSAGE = "Bukti harus berupa PDF, JPEG, atau PNG maksimal 5 MiB."
UPLOAD_CAP_MESSAGE = "Maksimal 5 bukti per item."
PRIVATE_EVIDENCE_DOWNLOAD_PATTERN = re.compile(r'href=["\']([^"\']*/auditor/spmi/evidence/\d+/download)["\']')
REPORT_CREATE_PATTERN = re.compile(r'action=["\']([^"\']*/lpmpi/spmi-reports/assessment/create/\d+)["\']')
REPORT_DETAIL_PATTERN = re.compile(r'/lpmpi/spmi-reports/detail/(\d+)')
RTM_DETAIL_PATTERN = re.compile(r'href=["\']([^"\']*/lpmpi/spmi-rtm/detail/\d+)["\']')
LEGACY_ARCHIVE_RUN_PATTERN = re.compile(r'href=["\']([^"\']*/lpmpi/legacy-ami-archive/run/\d+)["\']')
LEGACY_ARCHIVE_TASK_PATTERN = re.compile(r'href=["\']([^"\']*/lpmpi/legacy-ami-archive/task/\d+)["\']')
OPTION_PATTERN_TEMPLATE = r'<option value="(\d+)"[^>]*>(?:(?!</option>).)*{marker}(?:(?!</option>).)*</option>'
DASHBOARD_MARKERS = {
    "super-admin@m17-07a.test": "Dashboard Super Admin",
    "admin-lpmpi@m17-07a.test": "Dashboard Super Admin",
    "auditor-a@m17-07a.test": "Dashboard Auditor",
    "auditor-b@m17-07a.test": "Dashboard Auditor",
    "auditee-a@m17-07a.test": "Dashboard Auditee",
    "auditee-b@m17-07a.test": "Dashboard Auditee",
}


class NoRedirectHandler(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


def bounded_context(page: str) -> str:
    compact = re.sub(r"\s+", " ", page).strip()
    escaped = compact.replace(PASSWORD, "[fixture-password]").replace("<", "&lt;").replace(">", "&gt;")
    return escaped[:600]


def bounded_location(location: str) -> str:
    if not location:
        return "[none]"
    parsed = urllib.parse.urlsplit(location)
    value = repr(
        {
            "scheme": parsed.scheme,
            "netloc": parsed.netloc,
            "path": parsed.path,
            "query": "[redacted]" if parsed.query else "",
            "fragment": "[redacted]" if parsed.fragment else "",
        }
    )
    return value.replace(PASSWORD, "[fixture-password]")[:240]


def request(opener: urllib.request.OpenerDirector, url: str, data: bytes | None = None, content_type: str | None = None) -> tuple[int, str]:
    headers = {} if content_type is None else {"Content-Type": content_type}
    request_value = urllib.request.Request(url, data=data, headers=headers)
    try:
        with opener.open(request_value, timeout=10) as response:
            return response.status, response.read().decode("utf-8", "replace")
    except urllib.error.HTTPError as error:
        return error.code, error.read().decode("utf-8", "replace")


def no_redirect_request(
    opener: urllib.request.OpenerDirector,
    url: str,
    data: bytes | None,
    content_type: str | None,
) -> tuple[int, str, str]:
    headers = {} if content_type is None else {"Content-Type": content_type}
    request_value = urllib.request.Request(url, data=data, headers=headers)
    handlers = [handler for handler in opener.handlers if not isinstance(handler, urllib.request.HTTPRedirectHandler)]
    no_redirect_opener = urllib.request.build_opener(*handlers, NoRedirectHandler)
    try:
        with no_redirect_opener.open(request_value, timeout=10) as response:
            return response.status, response.headers.get("Location", ""), response.read().decode("utf-8", "replace")
    except urllib.error.HTTPError as error:
        return error.code, error.headers.get("Location", ""), error.read().decode("utf-8", "replace")


def submit_request(opener: urllib.request.OpenerDirector, url: str, data: bytes, content_type: str) -> tuple[int, str, str]:
    return no_redirect_request(opener, url, data, content_type)


def upload_request(opener: urllib.request.OpenerDirector, url: str, data: bytes, content_type: str) -> tuple[int, str, str]:
    return no_redirect_request(opener, url, data, content_type)


def assert_assignment_redirect_location(action: str, assignment_url: str, location: str, context: str) -> None:
    if not location:
        raise RuntimeError(
            f"expected redirect after {action}; "
            f"observed HTTP redirect without Location header; context={bounded_context(context)}"
        )
    expected_assignment = urllib.parse.urlsplit(assignment_url)
    resolved_location = urllib.parse.urlsplit(urllib.parse.urljoin(assignment_url, location))
    if (
        resolved_location.scheme != expected_assignment.scheme
        or resolved_location.netloc != expected_assignment.netloc
        or resolved_location.path != expected_assignment.path
        or resolved_location.query != expected_assignment.query
        or resolved_location.fragment != expected_assignment.fragment
    ):
        raise RuntimeError(
            f"expected redirect after {action} to the exact assignment URL; "
            f"observed location={bounded_location(location)}; "
            f"resolved location={bounded_location(resolved_location.geturl())}; "
            f"expected={expected_assignment.geturl()}; context={bounded_context(context)}"
        )


def csrf(page: str) -> str:
    match = CSRF_PATTERN.search(page)
    if match is None:
        raise RuntimeError("CSRF-protected form omitted csrf_test_name")
    return match.group(1)


def form_data(fields: dict[str, str]) -> bytes:
    return urllib.parse.urlencode(fields).encode()


def same_origin_url(base_url: str, url: str) -> str:
    candidate = urllib.parse.urljoin(base_url + "/", html.unescape(url))
    base_parts = urllib.parse.urlsplit(base_url)
    candidate_parts = urllib.parse.urlsplit(candidate)
    if candidate_parts.scheme != base_parts.scheme or candidate_parts.netloc != base_parts.netloc:
        raise RuntimeError(f"fixture page rendered foreign URL {bounded_location(candidate)}")
    return candidate


def login(base_url: str, email: str) -> urllib.request.OpenerDirector:
    jar = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
    login_url = base_url + "/auth/login"
    status, page = request(opener, login_url)
    if status != 200:
        raise RuntimeError(f"login page for {email} returned HTTP {status}")
    status, _ = request(opener, login_url, form_data({"csrf_test_name": csrf(page), "email": email, "password": PASSWORD}), "application/x-www-form-urlencoded")
    if status not in (200, 302):
        raise RuntimeError(f"login POST for {email} returned HTTP {status}")
    return opener


def expect_page(opener: urllib.request.OpenerDirector, url: str, marker: str) -> str:
    status, page = request(opener, url)
    if status != 200 or marker not in page:
        raise RuntimeError(f"fixture workflow failed at {url}: HTTP {status}")
    return page


def upload_evidence(opener: urllib.request.OpenerDirector, assignment_url: str, url: str, token: str, version: str) -> tuple[str, str]:
    boundary = "M17_07A_EVIDENCE"
    body = (
        f"--{boundary}\r\nContent-Disposition: form-data; name=\"csrf_test_name\"\r\n\r\n{token}\r\n"
        f"--{boundary}\r\nContent-Disposition: form-data; name=\"version\"\r\n\r\n{version}\r\n"
        f"--{boundary}\r\nContent-Disposition: form-data; name=\"evidence\"; filename=\"m17-07a.pdf\"\r\nContent-Type: application/pdf\r\n\r\n%PDF-1.4\n%%EOF\n\r\n"
        f"--{boundary}--\r\n"
    ).encode()
    status, location, context = upload_request(opener, url, body, f"multipart/form-data; boundary={boundary}")
    page = expect_page(opener, assignment_url, "M17-07A Runtime Package")
    version_match = VERSION_PATTERN.search(page)
    observed_version = "[missing]" if version_match is None else version_match.group(1)
    submitted_status = SUBMITTED_STATUS_PATTERN.search(page)
    observed_status = "submitted" if submitted_status is not None else "editable"
    if status not in (302, 303):
        raise RuntimeError(
            "expected redirect after auditee evidence upload; "
            f"observed HTTP {status}; location={bounded_location(location)}; "
            f"context={bounded_context(context)}; observed post-upload version={observed_version}; "
            f"observed post-upload status={observed_status}"
        )
    assert_assignment_redirect_location("auditee evidence upload", assignment_url, location, context)
    if version_match is None:
        raise RuntimeError(
            "auditee evidence upload did not return the current submission version; "
            f"observed post-upload status={observed_status}"
        )
    if observed_version == version:
        raise RuntimeError(
            "auditee evidence upload did not advance assignment version; "
            f"observed post-upload version={observed_version}; observed post-upload status={observed_status}"
        )
    return csrf(page), observed_version


def evidence_multipart_body(token: str | None, version: str, filename: str, content_type: str, content: bytes) -> tuple[bytes, str]:
    boundary = "M17_07A_EVIDENCE_SECURITY"
    parts = []
    if token is not None:
        parts.append(f"--{boundary}\r\nContent-Disposition: form-data; name=\"csrf_test_name\"\r\n\r\n{token}\r\n".encode())
    parts.extend(
        [
            f"--{boundary}\r\nContent-Disposition: form-data; name=\"version\"\r\n\r\n{version}\r\n".encode(),
            (
                f"--{boundary}\r\nContent-Disposition: form-data; name=\"evidence\"; filename=\"{filename}\"\r\n"
                f"Content-Type: {content_type}\r\n\r\n"
            ).encode(),
            content,
            f"\r\n--{boundary}--\r\n".encode(),
        ]
    )
    return b"".join(parts), f"multipart/form-data; boundary={boundary}"


def upload_evidence_variant(
    opener: urllib.request.OpenerDirector,
    assignment_url: str,
    url: str,
    token: str | None,
    version: str,
    filename: str,
    content_type: str,
    content: bytes,
) -> tuple[int, str, str, str, str, str]:
    body, multipart_type = evidence_multipart_body(token, version, filename, content_type, content)
    status, location, context = upload_request(opener, url, body, multipart_type)
    page = expect_page(opener, assignment_url, "M17-07A Runtime Package")
    version_match = VERSION_PATTERN.search(page)
    if version_match is None:
        raise RuntimeError(
            "auditee assignment did not expose hidden version after evidence security upload attempt; "
            f"context={bounded_context(page)}"
        )
    return status, location, context, page, csrf(page), version_match.group(1)


def assert_evidence_upload_rejected(
    opener: urllib.request.OpenerDirector,
    assignment_url: str,
    url: str,
    token: str,
    version: str,
    filename: str,
    content_type: str,
    content: bytes,
    expected_message: str,
    action: str,
) -> tuple[str, str]:
    status, location, context, first_assignment_page, refreshed_token, observed_version = upload_evidence_variant(
        opener,
        assignment_url,
        url,
        token,
        version,
        filename,
        content_type,
        content,
    )
    if status not in (302, 303):
        raise RuntimeError(
            f"expected redirect after {action}; "
            f"observed HTTP {status}; location={bounded_location(location)}; "
            f"context={bounded_context(context)}; observed draft version={observed_version}"
        )
    assert_assignment_redirect_location(action, assignment_url, location, context)
    if expected_message not in first_assignment_page:
        raise RuntimeError(
            f"{action} did not show expected upload rejection message; "
            f"expected={expected_message}; observed draft version={observed_version}; context={bounded_context(first_assignment_page)}"
        )
    if observed_version != version:
        raise RuntimeError(
            f"{action} changed draft version; "
            f"observed draft version={observed_version}; previous version={version}; context={bounded_context(first_assignment_page)}"
        )
    return refreshed_token, observed_version


def assert_missing_csrf_upload_denied(
    opener: urllib.request.OpenerDirector,
    assignment_url: str,
    url: str,
    version: str,
) -> tuple[str, str]:
    body, multipart_type = evidence_multipart_body(None, version, "m17-07a-csrf.pdf", "application/pdf", b"%PDF-1.4\n%%EOF\n")
    status, _, _ = upload_request(opener, url, body, multipart_type)
    if status != 403:
        raise RuntimeError(f"missing-CSRF auditee evidence upload was not denied by source-backed CI CSRF status; observed HTTP {status}")
    page = expect_page(opener, assignment_url, "M17-07A Runtime Package")
    version_match = VERSION_PATTERN.search(page)
    if version_match is None:
        raise RuntimeError(
            "auditee assignment did not remain editable after missing-CSRF upload denial; "
            f"context={bounded_context(page)}"
        )
    return csrf(page), version_match.group(1)


def assert_stale_version_upload_rejected(
    opener: urllib.request.OpenerDirector,
    assignment_url: str,
    url: str,
    token: str,
    version: str,
) -> tuple[str, str]:
    stale_version = str(int(version) - 1) if int(version) > 0 else str(int(version) + 1)
    status, location, context, first_assignment_page, refreshed_token, observed_version = upload_evidence_variant(
        opener,
        assignment_url,
        url,
        token,
        stale_version,
        "m17-07a-stale.pdf",
        "application/pdf",
        b"%PDF-1.4\n%%EOF\n",
    )
    if status not in (302, 303):
        raise RuntimeError(
            "expected redirect after stale-version auditee evidence upload; "
            f"observed HTTP {status}; location={bounded_location(location)}; context={bounded_context(context)}"
        )
    assert_assignment_redirect_location("stale-version auditee evidence upload", assignment_url, location, context)
    if CONFLICT_MESSAGE not in first_assignment_page:
        raise RuntimeError(
            "stale-version auditee evidence upload did not show expected conflict message; "
            f"context={bounded_context(first_assignment_page)}"
        )
    if observed_version != version:
        raise RuntimeError(
            "stale-version auditee evidence upload changed draft version; "
            f"observed draft version={observed_version}; previous version={version}; context={bounded_context(first_assignment_page)}"
        )
    return refreshed_token, observed_version


def assert_evidence_upload_security_lanes(
    opener: urllib.request.OpenerDirector,
    assignment_url: str,
    base_url: str,
    item_ids: list[str],
    token: str,
    version: str,
) -> tuple[str, str, set[str]]:
    first_item_url = base_url + "/auditee/spmi/item/" + item_ids[0] + "/evidence/upload"
    token, version = assert_evidence_upload_rejected(
        opener,
        assignment_url,
        first_item_url,
        token,
        version,
        "m17-07a.txt",
        "text/plain",
        b"not a pdf image",
        UPLOAD_VALIDATION_MESSAGE,
        "invalid-MIME auditee evidence upload",
    )
    token, version = assert_evidence_upload_rejected(
        opener,
        assignment_url,
        first_item_url,
        token,
        version,
        "m17-07a-oversize.pdf",
        "application/pdf",
        b"%PDF-1.4\n" + (b"0" * (5 * 1024 * 1024 + 1)),
        UPLOAD_VALIDATION_MESSAGE,
        "oversize auditee evidence upload",
    )
    token, version = assert_missing_csrf_upload_denied(opener, assignment_url, first_item_url, version)
    token, version = assert_stale_version_upload_rejected(opener, assignment_url, first_item_url, token, version)
    for index in range(5):
        token, version = upload_evidence(opener, assignment_url, first_item_url, token, version)
    token, version = assert_evidence_upload_rejected(
        opener,
        assignment_url,
        first_item_url,
        token,
        version,
        "m17-07a-sixth.pdf",
        "application/pdf",
        b"%PDF-1.4\n%%EOF\n",
        UPLOAD_CAP_MESSAGE,
        "sixth auditee evidence upload",
    )
    return token, version, {item_ids[0]}


def save_incomplete_auditee_draft(opener: urllib.request.OpenerDirector, assignment_url: str, token: str, version: str, item_id: str) -> tuple[str, str]:
    page = expect_page(opener, assignment_url, "M17-07A Runtime Package")
    token = csrf(page)
    version_match = VERSION_PATTERN.search(page)
    if version_match is None:
        raise RuntimeError(
            "auditee incomplete draft did not remain editable before save; "
            f"previous version={version}; context={bounded_context(page)}"
        )
    version = version_match.group(1)
    fields = {
        "csrf_test_name": token,
        "version": version,
        "realization[" + item_id + "]": "M17-07A incomplete draft",
    }
    status, location, context = submit_request(opener, assignment_url + "/save", form_data(fields), "application/x-www-form-urlencoded")
    page = expect_page(opener, assignment_url, "M17-07A Runtime Package")
    version_match = VERSION_PATTERN.search(page)
    observed_version = "[missing]" if version_match is None else version_match.group(1)
    if status not in (302, 303):
        raise RuntimeError(
            "expected redirect after incomplete auditee draft save; "
            f"observed HTTP {status}; location={bounded_location(location)}; "
            f"context={bounded_context(context)}; observed draft version={observed_version}"
        )
    assert_assignment_redirect_location("incomplete auditee draft save", assignment_url, location, context)
    if version_match is None:
        raise RuntimeError(
            "auditee incomplete draft did not remain editable; "
            f"previous version={version}; context={bounded_context(page)}"
        )
    if observed_version == version:
        raise RuntimeError(
            "auditee incomplete draft did not advance assignment version; "
            f"observed draft version={observed_version}; previous version={version}; context={bounded_context(page)}"
        )
    if "M17-07A incomplete draft" not in page:
        raise RuntimeError(
            "auditee incomplete draft did not render the partial realization; "
            f"observed draft version={observed_version}; context={bounded_context(page)}"
        )
    if "Status submission</strong>: draft" not in page:
        raise RuntimeError(
            "auditee incomplete draft did not show draft status; "
            f"observed draft version={observed_version}; context={bounded_context(page)}"
        )
    if "Submit sekali" not in page:
        raise RuntimeError(
            "auditee incomplete draft did not keep submit controls available; "
            f"observed draft version={observed_version}; context={bounded_context(page)}"
        )
    return csrf(page), observed_version


def find_fixture_url_policy_item_id(page: str) -> str:
    match = URL_POLICY_ITEM_PATTERN.search(page)
    if match is None:
        raise RuntimeError(
            "auditee assignment page did not expose the fixture URL-policy item M17R-Q-URL; "
            f"context={bounded_context(page)}"
        )
    return next(group for group in match.groups() if group is not None)


def assert_policy_rejection_before_valid_submit(opener: urllib.request.OpenerDirector, assignment_url: str, item_ids: list[str]) -> tuple[str, str]:
    page = expect_page(opener, assignment_url, "M17-07A Runtime Package")
    token = csrf(page)
    version_match = VERSION_PATTERN.search(page)
    if version_match is None:
        raise RuntimeError(
            "auditee assignment did not expose hidden version before evidence-policy rejection; "
            f"context={bounded_context(page)}"
        )
    version = version_match.group(1)
    url_policy_item_id = find_fixture_url_policy_item_id(page)
    fields = {"csrf_test_name": token, "version": version}
    for item_id in item_ids:
        fields["realization[" + item_id + "]"] = "M17-07A policy rejection realization"
        if item_id != url_policy_item_id:
            fields["evidence_url[" + item_id + "]"] = "https://m17-07a.test/evidence/" + item_id
    status, location, context = submit_request(opener, assignment_url + "/submit", form_data(fields), "application/x-www-form-urlencoded")
    page = expect_page(opener, assignment_url, "M17-07A Runtime Package")
    version_match = VERSION_PATTERN.search(page)
    observed_version = "[missing]" if version_match is None else version_match.group(1)
    if status not in (302, 303):
        raise RuntimeError(
            "expected redirect after evidence-policy rejection submit; "
            f"observed HTTP {status}; location={bounded_location(location)}; "
            f"context={bounded_context(context)}; observed draft version={observed_version}"
        )
    assert_assignment_redirect_location("evidence-policy rejection submit", assignment_url, location, context)
    if "Bukti wajib sesuai kebijakan sebelum submit." not in page:
        raise RuntimeError(
            "auditee policy rejection did not show expected evidence-policy flash; "
            f"observed draft version={observed_version}; context={bounded_context(page)}"
        )
    if version_match is None:
        raise RuntimeError(
            "auditee policy rejection did not remain editable; "
            f"previous version={version}; context={bounded_context(page)}"
        )
    if observed_version != version:
        raise RuntimeError(
            "auditee policy rejection changed draft version; "
            f"observed draft version={observed_version}; previous version={version}; context={bounded_context(page)}"
        )
    if "Status submission</strong>: draft" not in page:
        raise RuntimeError(
            "auditee policy rejection did not show draft status; "
            f"observed draft version={observed_version}; context={bounded_context(page)}"
        )
    if "Submit sekali" not in page:
        raise RuntimeError(
            "auditee policy rejection did not keep submit controls available; "
            f"observed draft version={observed_version}; context={bounded_context(page)}"
        )
    return csrf(page), observed_version


def assert_submitted_assignment(opener: urllib.request.OpenerDirector, assignment_url: str, previous_version: str) -> None:
    page = expect_page(opener, assignment_url, "M17-07A Runtime Package")
    version_match = VERSION_PATTERN.search(page)
    if version_match is not None:
        raise RuntimeError(
            "auditee assignment still exposed editable version after submit; "
            f"observed post-submit version={version_match.group(1)}; previous version={previous_version}; "
            f"context={bounded_context(page)}"
        )
    if SUBMITTED_STATUS_PATTERN.search(page) is None:
        raise RuntimeError(
            "auditee assignment page did not show submitted read-only status after submit; "
            f"previous version={previous_version}; context={bounded_context(page)}"
        )


def assert_resubmitted_assignment(opener: urllib.request.OpenerDirector, assignment_url: str, previous_version: str, reason: str) -> None:
    page = expect_page(opener, assignment_url, "M17-07A Runtime Package")
    version_match = VERSION_PATTERN.search(page)
    if version_match is not None:
        raise RuntimeError(
            "auditee assignment still exposed editable version after resubmit; "
            f"observed post-resubmit version={version_match.group(1)}; previous version={previous_version}; "
            f"context={bounded_context(page)}"
        )
    if RESUBMITTED_STATUS_PATTERN.search(page) is None:
        raise RuntimeError(
            "auditee assignment page did not show resubmitted read-only status after resubmit; "
            f"previous version={previous_version}; context={bounded_context(page)}"
        )
    for marker in ("returned_for_revision → resubmitted", "Resubmitted by auditee.", reason):
        if marker not in page:
            raise RuntimeError(
                "auditee resubmit page did not render expected revision history; "
                f"missing={marker}; previous version={previous_version}; context={bounded_context(page)}"
            )


def submit_auditee_assignment(opener: urllib.request.OpenerDirector, base_url: str, assignment_id: str, reject_missing_url_policy: bool = False) -> list[str]:
    assignment_url = base_url + "/auditee/spmi/assignment/" + assignment_id
    page = expect_page(opener, assignment_url, "M17-07A Runtime Package")
    token = csrf(page)
    version_match = VERSION_PATTERN.search(page)
    item_ids = ITEM_PATTERN.findall(page)
    if version_match is None or not item_ids:
        raise RuntimeError("auditee assignment form did not expose versioned realization fields")
    version = version_match.group(1)
    token, version = save_incomplete_auditee_draft(opener, assignment_url, token, version, item_ids[0])
    if reject_missing_url_policy:
        token, version = assert_policy_rejection_before_valid_submit(opener, assignment_url, item_ids)
    token, version, preloaded_evidence_item_ids = assert_evidence_upload_security_lanes(opener, assignment_url, base_url, item_ids, token, version)
    for item_id in item_ids:
        if item_id in preloaded_evidence_item_ids:
            continue
        token, version = upload_evidence(opener, assignment_url, base_url + "/auditee/spmi/item/" + item_id + "/evidence/upload", token, version)
    fields = {"csrf_test_name": token, "version": version}
    evidence_urls = []
    for item_id in item_ids:
        fields["realization[" + item_id + "]"] = "M17-07A normal auditee submission"
        evidence_url = "https://m17-07a.test/evidence/" + item_id
        fields["evidence_url[" + item_id + "]"] = evidence_url
        evidence_urls.append(evidence_url)
    status, location, context = submit_request(opener, assignment_url + "/submit", form_data(fields), "application/x-www-form-urlencoded")
    if status not in (302, 303):
        raise RuntimeError(
            "expected redirect after auditee submission; "
            f"observed HTTP {status}; location={bounded_location(location)}; context={bounded_context(context)}"
        )
    assert_assignment_redirect_location("auditee submission", assignment_url, location, context)
    assert_submitted_assignment(opener, assignment_url, version)
    return evidence_urls


def assert_return_resubmit_and_stale_finalize_rejection(
    auditor: urllib.request.OpenerDirector,
    auditee: urllib.request.OpenerDirector,
    base_url: str,
    auditor_assignment_id: str,
    auditee_assignment_id: str,
    auditor_page: str,
) -> None:
    auditor_assignment_url = base_url + "/auditor/spmi/assignment/" + auditor_assignment_id
    auditee_assignment_url = base_url + "/auditee/spmi/assignment/" + auditee_assignment_id
    assessment_version_match = VERSION_PATTERN.search(auditor_page)
    submission_version_match = SUBMISSION_VERSION_PATTERN.search(auditor_page)
    source_submission_version_match = SOURCE_SUBMISSION_VERSION_PATTERN.search(auditor_page)
    assessment_item_ids = ASSESSMENT_ITEM_PATTERN.findall(auditor_page)
    if assessment_version_match is None or submission_version_match is None or source_submission_version_match is None or not assessment_item_ids:
        raise RuntimeError(
            "auditor assignment page did not expose draft assessment version, submission version, source submission version, and assessment items; "
            f"context={bounded_context(auditor_page)}"
        )
    stale_assessment_version = assessment_version_match.group(1)
    stale_source_submission_version = source_submission_version_match.group(1)
    return_reason = "M17-07A return-for-revision proof"
    fields = {"csrf_test_name": csrf(auditor_page), "submission_version": submission_version_match.group(1), "reason": return_reason}
    status, location, context = submit_request(auditor, auditor_assignment_url + "/return", form_data(fields), "application/x-www-form-urlencoded")
    returned_auditor_page = expect_page(auditor, auditor_assignment_url, "M17-07A Runtime Package")
    if status not in (302, 303):
        raise RuntimeError(
            "expected redirect after auditor return-for-revision; "
            f"observed HTTP {status}; location={bounded_location(location)}; context={bounded_context(context)}"
        )
    assert_assignment_redirect_location("auditor return-for-revision", auditor_assignment_url, location, context)
    for marker in ("Status submission</strong>: returned_for_revision", "submitted → returned_for_revision", return_reason):
        if marker not in returned_auditor_page:
            raise RuntimeError(
                "auditor return-for-revision page did not render expected returned state/history; "
                f"missing={marker}; context={bounded_context(returned_auditor_page)}"
            )

    returned_auditee_page = expect_page(auditee, auditee_assignment_url, "M17-07A Runtime Package")
    returned_version_match = VERSION_PATTERN.search(returned_auditee_page)
    returned_item_ids = ITEM_PATTERN.findall(returned_auditee_page)
    if returned_version_match is None or not returned_item_ids or "Kirim ulang revisi" not in returned_auditee_page:
        raise RuntimeError(
            "auditee returned assignment did not expose editable resubmit form; "
            f"context={bounded_context(returned_auditee_page)}"
        )
    fields = {"csrf_test_name": csrf(returned_auditee_page), "version": returned_version_match.group(1)}
    for item_id in returned_item_ids:
        fields["realization[" + item_id + "]"] = "M17-07A resubmitted auditee realization"
        fields["evidence_url[" + item_id + "]"] = "https://m17-07a.test/resubmitted-evidence/" + item_id
    status, location, context = submit_request(auditee, auditee_assignment_url + "/resubmit", form_data(fields), "application/x-www-form-urlencoded")
    if status not in (302, 303):
        raise RuntimeError(
            "expected redirect after auditee resubmit; "
            f"observed HTTP {status}; location={bounded_location(location)}; context={bounded_context(context)}"
        )
    assert_assignment_redirect_location("auditee resubmit", auditee_assignment_url, location, context)
    assert_resubmitted_assignment(auditee, auditee_assignment_url, returned_version_match.group(1), return_reason)

    auditor_page_after_resubmit = expect_page(auditor, auditor_assignment_url, "M17-07A Runtime Package")
    fields = {
        "csrf_test_name": csrf(auditor_page_after_resubmit),
        "version": stale_assessment_version,
        "source_submission_version": stale_source_submission_version,
    }
    for item_id in assessment_item_ids:
        fields["assessment[" + item_id + "][score]"] = "3"
        fields["assessment[" + item_id + "][finding_type]"] = ""
        fields["assessment[" + item_id + "][finding]"] = "M17-07A stale finalization finding"
        fields["assessment[" + item_id + "][recommendation]"] = "M17-07A stale finalization recommendation"
    status, location, context = submit_request(auditor, auditor_assignment_url + "/finalize", form_data(fields), "application/x-www-form-urlencoded")
    stale_rejection_page = expect_page(auditor, auditor_assignment_url, "M17-07A Runtime Package")
    if status not in (302, 303):
        raise RuntimeError(
            "expected redirect after stale auditor finalization rejection; "
            f"observed HTTP {status}; location={bounded_location(location)}; context={bounded_context(context)}"
        )
    assert_assignment_redirect_location("stale auditor finalization", auditor_assignment_url, location, context)
    if CONFLICT_MESSAGE not in stale_rejection_page or "Penilaian SPMI berhasil difinalisasi." in stale_rejection_page or "Status submission</strong>: finalized" in stale_rejection_page:
        raise RuntimeError(
            "stale auditor finalization was not rejected with the conflict message/no finalization proof; "
            f"context={bounded_context(stale_rejection_page)}"
        )
    if VERSION_PATTERN.search(stale_rejection_page) is None:
        raise RuntimeError(
            "auditor draft form after stale auditor finalization rejection did not remain editable; "
            f"context={bounded_context(stale_rejection_page)}"
        )


def current_assessment_form(page: str) -> tuple[str, str, str, list[str]]:
    assessment_version_match = VERSION_PATTERN.search(page)
    source_submission_version_match = SOURCE_SUBMISSION_VERSION_PATTERN.search(page)
    assessment_item_ids = ASSESSMENT_ITEM_PATTERN.findall(page)
    if assessment_version_match is None or source_submission_version_match is None or not assessment_item_ids:
        raise RuntimeError(
            "auditor assignment page did not expose editable assessment form; "
            f"context={bounded_context(page)}"
        )
    return csrf(page), assessment_version_match.group(1), source_submission_version_match.group(1), assessment_item_ids


def valid_assessment_fields(token: str, version: str, source_submission_version: str, item_ids: list[str]) -> dict[str, str]:
    fields = {"csrf_test_name": token, "version": version, "source_submission_version": source_submission_version}
    for index, item_id in enumerate(item_ids):
        finding_type = "kts" if index == 0 else "ob" if index == 1 else ""
        fields["assessment[" + item_id + "][score]"] = str((index % 4) + 1)
        fields["assessment[" + item_id + "][finding_type]"] = finding_type
        fields["assessment[" + item_id + "][finding]"] = "M17-07A valid auditor finding " + item_id
        fields["assessment[" + item_id + "][recommendation]"] = "M17-07A valid auditor recommendation " + item_id
    return fields


def assert_invalid_finalize_rejected(
    auditor: urllib.request.OpenerDirector,
    auditor_assignment_url: str,
    page: str,
    message: str,
    invalid_fields: dict[str, str],
) -> str:
    token, version, source_submission_version, item_ids = current_assessment_form(page)
    fields = valid_assessment_fields(token, version, source_submission_version, item_ids)
    fields.update(invalid_fields)
    status, location, context = submit_request(auditor, auditor_assignment_url + "/finalize", form_data(fields), "application/x-www-form-urlencoded")
    rejected_page = expect_page(auditor, auditor_assignment_url, "M17-07A Runtime Package")
    if status not in (302, 303):
        raise RuntimeError(
            "expected redirect after invalid auditor finalization; "
            f"observed HTTP {status}; location={bounded_location(location)}; context={bounded_context(context)}"
        )
    assert_assignment_redirect_location("invalid auditor finalization", auditor_assignment_url, location, context)
    if message not in rejected_page or "Penilaian SPMI berhasil difinalisasi." in rejected_page:
        raise RuntimeError(
            "invalid auditor finalization did not show expected validation error/no success proof; "
            f"expected={message}; context={bounded_context(rejected_page)}"
        )
    if VERSION_PATTERN.search(rejected_page) is None or SOURCE_SUBMISSION_VERSION_PATTERN.search(rejected_page) is None or "Finalisasi" not in rejected_page or "Simpan draft" not in rejected_page:
        raise RuntimeError(
            "auditor draft form after invalid finalization did not remain editable; "
            f"context={bounded_context(rejected_page)}"
        )
    return rejected_page


def assert_finalize_validation_success_and_immutability(auditor: urllib.request.OpenerDirector, base_url: str, auditor_assignment_id: str) -> None:
    auditor_assignment_url = base_url + "/auditor/spmi/assignment/" + auditor_assignment_id
    auditor_page = expect_page(auditor, auditor_assignment_url, "M17-07A Runtime Package")
    token, version, source_submission_version, item_ids = current_assessment_form(auditor_page)

    auditor_page = assert_invalid_finalize_rejected(
        auditor,
        auditor_assignment_url,
        auditor_page,
        "Semua item wajib diberi skor 1 sampai 4 sebelum finalisasi.",
        {"assessment[" + item_ids[0] + "][score]": ""},
    )
    auditor_page = assert_invalid_finalize_rejected(
        auditor,
        auditor_assignment_url,
        auditor_page,
        "Uraian temuan wajib diisi untuk OB atau KTS sebelum finalisasi.",
        {"assessment[" + item_ids[0] + "][finding_type]": "ob", "assessment[" + item_ids[0] + "][finding]": ""},
    )
    auditor_page = assert_invalid_finalize_rejected(
        auditor,
        auditor_assignment_url,
        auditor_page,
        "Rekomendasi wajib diisi untuk KTS sebelum finalisasi.",
        {"assessment[" + item_ids[0] + "][finding_type]": "kts", "assessment[" + item_ids[0] + "][finding]": "M17-07A KTS finding", "assessment[" + item_ids[0] + "][recommendation]": ""},
    )

    token, version, source_submission_version, item_ids = current_assessment_form(auditor_page)
    pre_final_assessment_version = version
    pre_final_source_submission_version = source_submission_version
    fields = valid_assessment_fields(token, version, source_submission_version, item_ids)
    status, location, context = submit_request(auditor, auditor_assignment_url + "/finalize", form_data(fields), "application/x-www-form-urlencoded")
    finalized_page = expect_page(auditor, auditor_assignment_url, "M17-07A Runtime Package")
    if status not in (302, 303):
        raise RuntimeError(
            "expected redirect after valid auditor finalization; "
            f"observed HTTP {status}; location={bounded_location(location)}; context={bounded_context(context)}"
        )
    assert_assignment_redirect_location("valid auditor finalization", auditor_assignment_url, location, context)
    if "Penilaian SPMI berhasil difinalisasi." not in finalized_page:
        raise RuntimeError(
            "valid auditor finalization did not show expected success flash; "
            f"context={bounded_context(finalized_page)}"
        )
    if VERSION_PATTERN.search(finalized_page) is not None or SOURCE_SUBMISSION_VERSION_PATTERN.search(finalized_page) is not None:
        raise RuntimeError(
            "auditor finalized page still exposed editable version/source_submission_version fields; "
            f"context={bounded_context(finalized_page)}"
        )
    if " disabled" not in finalized_page:
        raise RuntimeError(
            "auditor finalized page did not render read-only disabled assessment controls; "
            f"context={bounded_context(finalized_page)}"
        )
    if "Finalisasi" in finalized_page or "Simpan draft" in finalized_page:
        raise RuntimeError(
            "auditor finalized page still exposed mutation buttons; "
            f"context={bounded_context(finalized_page)}"
        )

    # finalized UI intentionally exposes no editable version tokens; replay pre-final tokens as an authorization/immutability forgery.
    for action in ("save", "finalize"):
        finalized_page = expect_page(auditor, auditor_assignment_url, "M17-07A Runtime Package")
        if VERSION_PATTERN.search(finalized_page) is not None or SOURCE_SUBMISSION_VERSION_PATTERN.search(finalized_page) is not None:
            raise RuntimeError(
                "auditor finalized page reloaded before post-final forgery still exposed editable version/source_submission_version fields; "
                f"context={bounded_context(finalized_page)}"
            )
        mutation_fields = valid_assessment_fields(csrf(finalized_page), pre_final_assessment_version, pre_final_source_submission_version, item_ids)
        status, location, context = submit_request(auditor, auditor_assignment_url + "/" + action, form_data(mutation_fields), "application/x-www-form-urlencoded")
        mutation_page = expect_page(auditor, auditor_assignment_url, "M17-07A Runtime Package")
        if status not in (302, 303):
            raise RuntimeError(
                "expected redirect after post-finalized auditor " + action + "; "
                f"observed HTTP {status}; location={bounded_location(location)}; context={bounded_context(context)}"
            )
        assert_assignment_redirect_location("post-finalized auditor " + action, auditor_assignment_url, location, context)
        if (
            CONFLICT_MESSAGE not in mutation_page
            or "Penilaian SPMI berhasil difinalisasi." in mutation_page
            or VERSION_PATTERN.search(mutation_page) is not None
            or SOURCE_SUBMISSION_VERSION_PATTERN.search(mutation_page) is not None
            or " disabled" not in mutation_page
            or "Finalisasi" in mutation_page
            or "Simpan draft" in mutation_page
        ):
            raise RuntimeError(
                "post-finalized auditor " + action + " forgery was not rejected as immutable/read-only; "
                f"context={bounded_context(mutation_page)}"
            )


def assert_evidence_urls(page: str, evidence_urls: list[str]) -> None:
    missing_urls = [url for url in evidence_urls if url not in page]
    if missing_urls:
        raise RuntimeError(
            "auditor assignment page did not render submitted auditee evidence URLs; "
            f"missing={missing_urls}; context={bounded_context(page)}"
        )


def extract_private_evidence_download_url(page: str, base_url: str) -> str | None:
    base_parts = urllib.parse.urlsplit(base_url)
    for match in PRIVATE_EVIDENCE_DOWNLOAD_PATTERN.finditer(page):
        candidate = urllib.parse.urljoin(base_url + "/", html.unescape(match.group(1)))
        parsed = urllib.parse.urlsplit(candidate)
        if parsed.scheme == base_parts.scheme and parsed.netloc == base_parts.netloc:
            return candidate
    return None


def assert_private_evidence_download(opener: urllib.request.OpenerDirector, download_url: str) -> None:
    status, content = request(opener, download_url)
    if status != 200 or "%PDF-1.4" not in content:
        raise RuntimeError(
            "expected authorized private evidence PDF download; "
            f"observed HTTP {status}; url={bounded_location(download_url)}; context={bounded_context(content)}"
        )


def expect_denied(opener: urllib.request.OpenerDirector, url: str, action: str = "cross-user ownership denial") -> None:
    status, content = request(opener, url)
    if status not in (403, 404):
        raise RuntimeError(f"{action} failed for {bounded_location(url)}: HTTP {status}; context={bounded_context(content)}")
    if "%PDF-1.4" in content:
        raise RuntimeError(f"{action} leaked private PDF content for {bounded_location(url)}")


def unauthenticated_opener() -> urllib.request.OpenerDirector:
    return urllib.request.build_opener()


def assert_unauthenticated_private_evidence_denied(download_url: str) -> None:
    parsed = urllib.parse.urlsplit(download_url)
    origin = urllib.parse.urlunsplit((parsed.scheme, parsed.netloc, "", "", ""))
    status, location, content = no_redirect_request(unauthenticated_opener(), download_url, None, None)
    if status not in (302, 303, 307):
        raise RuntimeError(
            "unauthenticated direct private evidence download denial failed; "
            f"expected auth redirect HTTP 302/303/307; observed HTTP {status}; "
            f"url={bounded_location(download_url)}; context={bounded_context(content)}"
        )
    if not location:
        raise RuntimeError(
            "unauthenticated direct private evidence download denial failed; "
            f"missing redirect Location header; url={bounded_location(download_url)}; context={bounded_context(content)}"
        )
    login_url = same_origin_url(origin, location)
    login_parts = urllib.parse.urlsplit(login_url)
    if login_parts.path not in ("/index.php/auth", "/index.php/auth/login"):
        raise RuntimeError(
            "unauthenticated direct private evidence download denial failed; "
            f"expected same-origin auth entrypoint target from redirect('auth') with index_page='index.php'; observed={bounded_location(login_url)}; "
            f"url={bounded_location(download_url)}; context={bounded_context(content)}"
        )
    if login_parts.query or login_parts.fragment:
        raise RuntimeError(
            "unauthenticated direct private evidence download denial failed; "
            f"expected bare auth entrypoint redirect target; observed={bounded_location(login_url)}; "
            f"url={bounded_location(download_url)}; context={bounded_context(content)}"
        )
    if "%PDF-1.4" in content:
        raise RuntimeError(
            "unauthenticated direct private evidence download denial leaked private PDF content; "
            f"url={bounded_location(download_url)}; context={bounded_context(content)}"
        )


def assert_private_evidence_traversal_denied(opener: urllib.request.OpenerDirector, download_url: str) -> None:
    parsed = urllib.parse.urlsplit(download_url)
    download_path = parsed.path.rstrip("/")
    route_prefix = download_path.rsplit("/", 2)[0]
    origin = urllib.parse.urlunsplit((parsed.scheme, parsed.netloc, "", "", ""))
    for payload in ("abc", "..%2F1", "%2e%2e%2f1", "1/../../application/config/database.php"):
        probe_url = origin + route_prefix + "/" + payload + "/download"
        expect_denied(opener, probe_url, "path traversal/private evidence download denial")


def extract_option_value(page: str, marker: str, label: str) -> str:
    pattern = re.compile(OPTION_PATTERN_TEMPLATE.format(marker=re.escape(marker)), re.DOTALL)
    match = pattern.search(page)
    if match is None:
        raise RuntimeError(f"RTM form did not render selectable {label}; context={bounded_context(page)}")
    return match.group(1)


def assert_report_snapshot_final_result_and_rtm(
    admin: urllib.request.OpenerDirector,
    auditee_a: urllib.request.OpenerDirector,
    auditee_b: urllib.request.OpenerDirector,
    base_url: str,
    auditee_a_assignment_id: str,
) -> None:
    reports_url = base_url + "/lpmpi/spmi-reports"
    reports_page = expect_page(admin, reports_url, "Assessment finalized belum dilaporkan")
    create_match = REPORT_CREATE_PATTERN.search(reports_page)
    if create_match is None:
        raise RuntimeError(
            "admin LPMPI reports page did not render a finalized-assessment report create form; "
            f"context={bounded_context(reports_page)}"
        )
    create_url = same_origin_url(base_url, create_match.group(1))
    status, location, context = submit_request(admin, create_url, form_data({"csrf_test_name": csrf(reports_page)}), "application/x-www-form-urlencoded")
    if status not in (302, 303):
        raise RuntimeError(
            "expected redirect after admin LPMPI report create from finalized assessment; "
            f"observed HTTP {status}; location={bounded_location(location)}; context={bounded_context(context)}"
        )
    detail_url = same_origin_url(base_url, location)
    detail_match = REPORT_DETAIL_PATTERN.search(urllib.parse.urlsplit(detail_url).path)
    if detail_match is None:
        raise RuntimeError(
            "report create did not redirect to immutable report detail; "
            f"location={bounded_location(location)}; resolved={bounded_location(detail_url)}; context={bounded_context(context)}"
        )
    report_id = detail_match.group(1)

    report_detail_page = expect_page(admin, detail_url, "Laporan ini immutable. Detail dibaca dari snapshot M10/M17")
    for marker in ("M17R-V1 / M17R-S1 / M17R-P1", "M17-07A valid auditor finding", "M17-07A valid auditor recommendation"):
        if marker not in report_detail_page:
            raise RuntimeError(f"immutable report detail missing finalized snapshot marker {marker}; context={bounded_context(report_detail_page)}")
    expect_page(admin, base_url + "/lpmpi/spmi-reports/print/" + report_id, "Print / Save as PDF")

    final_result_url = base_url + "/auditee/spmi/assignment/" + auditee_a_assignment_id + "/final-result"
    final_result_page = expect_page(auditee_a, final_result_url, "Hasil akhir ini readonly dan dibaca dari snapshot laporan SPMI.")
    if "M17R-V1 / M17R-S1 / M17R-P1" not in final_result_page or "M17-07A valid auditor finding" not in final_result_page:
        raise RuntimeError(f"Auditee A final-result page did not render readonly report snapshot markers; context={bounded_context(final_result_page)}")
    status, denied_page = request(auditee_b, final_result_url)
    if status != 404:
        raise RuntimeError(f"Auditee B final-result cross-owner read must return 404; observed HTTP {status}; context={bounded_context(denied_page)}")

    rtm_create_url = base_url + "/lpmpi/spmi-rtm/create"
    rtm_form = expect_page(admin, rtm_create_url, "Tambah RTM SPMI")
    participant_id = extract_option_value(rtm_form, "M17-07A Admin LPMPI — admin_lpmpi", "admin LPMPI participant")
    fields = {
        "csrf_test_name": csrf(rtm_form),
        "meeting_code": "M17-07A-RTM",
        "meeting_title": "M17-07A RTM source-backed report smoke",
        "meeting_date": "2026-08-09",
        "location": "M17-07A Runtime Room",
        "report_ids[]": report_id,
        "participant_ids[]": participant_id,
        "decisions[0][decision_text]": "M17-07A RTM decision from report snapshot",
        "decisions[0][action_text]": "M17-07A RTM action from report snapshot",
        "decisions[0][report_id]": report_id,
        "decisions[0][report_item_id]": "",
    }
    status, location, context = submit_request(admin, base_url + "/lpmpi/spmi-rtm/store", form_data(fields), "application/x-www-form-urlencoded")
    if status not in (302, 303):
        raise RuntimeError(
            "expected redirect after admin LPMPI RTM create from generated report; "
            f"observed HTTP {status}; location={bounded_location(location)}; context={bounded_context(context)}"
        )
    rtm_index = expect_page(admin, base_url + "/lpmpi/spmi-rtm", "M17-07A-RTM")
    rtm_detail_url = None
    for match in RTM_DETAIL_PATTERN.finditer(rtm_index):
        candidate = same_origin_url(base_url, match.group(1))
        detail_page = expect_page(admin, candidate, "M17-07A-RTM")
        if "M17-07A RTM source-backed report smoke" in detail_page:
            rtm_detail_url = candidate
            break
    if rtm_detail_url is None:
        raise RuntimeError(f"RTM index did not expose created source-backed RTM detail URL; context={bounded_context(rtm_index)}")
    rtm_detail = expect_page(admin, rtm_detail_url, "M17-07A RTM decision from report snapshot")
    for marker in ("Laporan M10", "Peserta snapshot", "M17-07A RTM action from report snapshot"):
        if marker not in rtm_detail:
            raise RuntimeError(f"RTM detail did not render source-backed report consumption marker {marker}; context={bounded_context(rtm_detail)}")


def assert_legacy_ami_archive_read_only_lane(admin: urllib.request.OpenerDirector, base_url: str) -> None:
    archive_url = base_url + "/lpmpi/legacy-ami-archive"
    archive_page = expect_page(admin, archive_url, "Arsip AMI Legacy")
    for marker in ("Browser arsip read-only", "Legacy AMI tetap authoritative; tidak ada eksekusi backfill dari layar ini.", "Archive Runs"):
        if marker not in archive_page:
            raise RuntimeError(f"legacy archive index missing source-backed read-only/archive marker {marker}; context={bounded_context(archive_page)}")
    if "Belum ada run arsip" in archive_page:
        raise RuntimeError(
            "M17-07A fixture coverage gap: legacy archive runtime lane needs source-backed legacy_ami_archive_* rows; "
            "index rendered no archive runs, not product failure."
        )

    preflight_page = expect_page(admin, archive_url + "/preflight", "Preflight Arsip AMI Legacy")
    for marker in ("Read-only count", "Tidak menulis data", "legacy_ami_archive_tasks"):
        if marker not in preflight_page:
            raise RuntimeError(f"legacy archive preflight missing source-backed read-only/archive marker {marker}; context={bounded_context(preflight_page)}")

    issues_page = expect_page(admin, archive_url + "/issues", "Rekonsiliasi Arsip AMI Legacy")
    for marker in ("Daftar issue archive-owned", "Tidak memperbaiki atau mengubah tabel legacy"):
        if marker not in issues_page:
            raise RuntimeError(f"legacy archive issues page missing source-backed read-only marker {marker}; context={bounded_context(issues_page)}")

    run_match = LEGACY_ARCHIVE_RUN_PATTERN.search(archive_page)
    if run_match is None:
        raise RuntimeError(f"legacy archive index rendered rows without run detail link; context={bounded_context(archive_page)}")
    run_page = expect_page(admin, same_origin_url(base_url, run_match.group(1)), "Detail Run Arsip AMI Legacy")
    for marker in ("Tugas Archive", "Issue Run"):
        if marker not in run_page:
            raise RuntimeError(f"legacy archive run detail missing source-backed archive marker {marker}; context={bounded_context(run_page)}")

    task_match = LEGACY_ARCHIVE_TASK_PATTERN.search(run_page)
    if task_match is None:
        raise RuntimeError(
            "M17-07A fixture coverage gap: legacy archive run has no task detail link; "
            f"needs legacy_ami_archive_tasks rows, not product failure; context={bounded_context(run_page)}"
        )
    task_page = expect_page(admin, same_origin_url(base_url, task_match.group(1)), "Tugas Arsip AMI Legacy")
    for marker in ("Snapshot read-only dari legacy AMI", "Legacy Tugas ID", "Jawaban"):
        if marker not in task_page:
            raise RuntimeError(f"legacy archive task detail missing source-backed read-only marker {marker}; context={bounded_context(task_page)}")


def main() -> int:
    parser = argparse.ArgumentParser(description="M17-07A normal auditee-to-auditor HTTP fixture smoke")
    parser.add_argument("--base-url", required=True)
    parser.add_argument("--auditor-a-assignment", required=True)
    parser.add_argument("--auditor-b-assignment", required=True)
    parser.add_argument("--auditee-a-assignment", required=True)
    parser.add_argument("--auditee-b-assignment", required=True)
    args = parser.parse_args()
    assignment_ids = (args.auditor_a_assignment, args.auditor_b_assignment, args.auditee_a_assignment, args.auditee_b_assignment)
    if not all(identifier.isdecimal() for identifier in assignment_ids):
        raise RuntimeError("fixture assignment identifiers must be decimal IDs")
    base_url = args.base_url.rstrip("/")

    sessions: dict[str, urllib.request.OpenerDirector] = {}
    for email, marker in DASHBOARD_MARKERS.items():
        opener = login(base_url, email)
        expect_page(opener, base_url + "/dashboard", marker)
        sessions[email] = opener

    admin_lpmpi = sessions["admin-lpmpi@m17-07a.test"]
    assert_legacy_ami_archive_read_only_lane(admin_lpmpi, base_url)

    auditee_a = sessions["auditee-a@m17-07a.test"]
    auditee_a_evidence_urls = submit_auditee_assignment(auditee_a, base_url, args.auditee_a_assignment, reject_missing_url_policy=True)
    expect_denied(auditee_a, base_url + "/auditee/spmi/assignment/" + args.auditee_b_assignment)

    auditee_b = sessions["auditee-b@m17-07a.test"]
    submit_auditee_assignment(auditee_b, base_url, args.auditee_b_assignment)

    auditor_a = sessions["auditor-a@m17-07a.test"]
    auditor_a_page = expect_page(auditor_a, base_url + "/auditor/spmi/assignment/" + args.auditor_a_assignment, "M17-07A Runtime Package")
    assert_evidence_urls(auditor_a_page, auditee_a_evidence_urls)
    assert_return_resubmit_and_stale_finalize_rejection(auditor_a, auditee_a, base_url, args.auditor_a_assignment, args.auditee_a_assignment, auditor_a_page)
    assert_finalize_validation_success_and_immutability(auditor_a, base_url, args.auditor_a_assignment)
    assert_report_snapshot_final_result_and_rtm(admin_lpmpi, auditee_a, auditee_b, base_url, args.auditee_a_assignment)
    auditor_a_page = expect_page(auditor_a, base_url + "/auditor/spmi/assignment/" + args.auditor_a_assignment, "M17-07A Runtime Package")
    auditor_a_download_url = extract_private_evidence_download_url(auditor_a_page, base_url)
    if auditor_a_download_url is None:
        raise RuntimeError("Auditor A assignment page did not render a private evidence download URL")
    assert_private_evidence_download(auditor_a, auditor_a_download_url)
    assert_unauthenticated_private_evidence_denied(auditor_a_download_url)
    assert_private_evidence_traversal_denied(auditor_a, auditor_a_download_url)
    expect_denied(auditor_a, base_url + "/auditor/spmi/assignment/" + args.auditor_b_assignment)

    auditor_b = sessions["auditor-b@m17-07a.test"]
    auditor_b_page = expect_page(auditor_b, base_url + "/auditor/spmi/assignment/" + args.auditor_b_assignment, "M17-07A Runtime Package")
    auditor_b_download_url = extract_private_evidence_download_url(auditor_b_page, base_url)
    if auditor_b_download_url is not None:
        expect_denied(auditor_a, auditor_b_download_url)

    print("M17-07A HTTP fixture smoke passed.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
