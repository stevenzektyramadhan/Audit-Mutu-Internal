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
VERSION_PATTERN = re.compile(r'name="version" value="(\d+)"')
SUBMITTED_STATUS_PATTERN = re.compile(r'\bsubmitted\b|\bdiisi\b|\bterkirim\b', re.IGNORECASE)
PRIVATE_EVIDENCE_DOWNLOAD_PATTERN = re.compile(r'href=["\']([^"\']*/auditor/spmi/evidence/\d+/download)["\']')
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


def no_redirect_request(opener: urllib.request.OpenerDirector, url: str, data: bytes, content_type: str) -> tuple[int, str, str]:
    request_value = urllib.request.Request(url, data=data, headers={"Content-Type": content_type})
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


def submit_auditee_assignment(opener: urllib.request.OpenerDirector, base_url: str, assignment_id: str) -> list[str]:
    assignment_url = base_url + "/auditee/spmi/assignment/" + assignment_id
    page = expect_page(opener, assignment_url, "M17-07A Runtime Package")
    token = csrf(page)
    version_match = VERSION_PATTERN.search(page)
    item_ids = ITEM_PATTERN.findall(page)
    if version_match is None or not item_ids:
        raise RuntimeError("auditee assignment form did not expose versioned realization fields")
    version = version_match.group(1)
    for item_id in item_ids:
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


def expect_denied(opener: urllib.request.OpenerDirector, url: str) -> None:
    status, _ = request(opener, url)
    if status not in (403, 404):
        raise RuntimeError(f"cross-user ownership denial failed for {url}: HTTP {status}")


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

    auditee_a = sessions["auditee-a@m17-07a.test"]
    auditee_a_evidence_urls = submit_auditee_assignment(auditee_a, base_url, args.auditee_a_assignment)
    expect_denied(auditee_a, base_url + "/auditee/spmi/assignment/" + args.auditee_b_assignment)

    auditee_b = sessions["auditee-b@m17-07a.test"]
    submit_auditee_assignment(auditee_b, base_url, args.auditee_b_assignment)

    auditor_a = sessions["auditor-a@m17-07a.test"]
    auditor_a_page = expect_page(auditor_a, base_url + "/auditor/spmi/assignment/" + args.auditor_a_assignment, "M17-07A Runtime Package")
    assert_evidence_urls(auditor_a_page, auditee_a_evidence_urls)
    auditor_a_download_url = extract_private_evidence_download_url(auditor_a_page, base_url)
    if auditor_a_download_url is None:
        raise RuntimeError("Auditor A assignment page did not render a private evidence download URL")
    assert_private_evidence_download(auditor_a, auditor_a_download_url)
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
