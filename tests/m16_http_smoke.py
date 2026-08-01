#!/usr/bin/env python3
import argparse
import http.cookiejar
import os
import re
import sys
import urllib.parse
import urllib.request
import urllib.error


def request(opener, url, data=None):
    encoded = None if data is None else urllib.parse.urlencode(data).encode("utf-8")
    return opener.open(url, encoded, timeout=10)


def status_request(opener, url, data=None):
    try:
        response = request(opener, url, data)
        return response.getcode(), response
    except urllib.error.HTTPError as error:
        return error.code, error


def body(response):
    return response.read().decode("utf-8", "replace")


def csrf(html):
    match = re.search(r'name="csrf_test_name" value="([^"]+)"', html)
    if not match:
        raise RuntimeError("CSRF token not found")
    return match.group(1)


def assert_header(headers, name, expected):
    actual = headers.get(name)
    if actual != expected:
        raise RuntimeError(f"{name} expected {expected!r}, got {actual!r}")


def main():
    parser = argparse.ArgumentParser(description="M16 AMI HTTP smoke")
    parser.add_argument("--base-url", default=os.getenv("AMI_BASE_URL", "http://127.0.0.1:8081/index.php"))
    parser.add_argument("--email", default=os.getenv("AMI_SMOKE_EMAIL"))
    parser.add_argument("--password", default=os.getenv("AMI_SMOKE_PASSWORD"))
    args = parser.parse_args()

    jar = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
    login_url = args.base_url.rstrip("/") + "/auth/login"

    login_page = request(opener, login_url)
    assert_header(login_page.headers, "X-Content-Type-Options", "nosniff")
    assert_header(login_page.headers, "Referrer-Policy", "same-origin")
    assert_header(login_page.headers, "X-Frame-Options", "DENY")
    assert_header(login_page.headers, "Cache-Control", "private, no-store, max-age=0, must-revalidate")
    csp = login_page.headers.get("Content-Security-Policy-Report-Only", "")
    if "default-src 'self'" not in csp or login_page.headers.get("Content-Security-Policy"):
        raise RuntimeError("CSP report-only header missing or enforced CSP present")

    html = body(login_page)
    token = csrf(html)

    if not args.email or not args.password:
        raise RuntimeError("AMI_SMOKE_EMAIL dan AMI_SMOKE_PASSWORD wajib diisi")

    posted = request(opener, login_url, {"csrf_test_name": token, "email": args.email, "password": args.password})
    if posted.getcode() not in (200, 302):
        raise RuntimeError(f"Login POST returned HTTP {posted.getcode()}")

    dashboard = request(opener, args.base_url.rstrip('/') + '/dashboard')
    assert_header(dashboard.headers, "Cache-Control", "private, no-store, max-age=0, must-revalidate")
    assert_header(dashboard.headers, "X-Content-Type-Options", "nosniff")
    assert_header(dashboard.headers, "Referrer-Policy", "same-origin")
    assert_header(dashboard.headers, "X-Frame-Options", "DENY")

    archive_status, archive = status_request(opener, args.base_url.rstrip('/') + '/lpmpi/legacy-ami-archive')
    if archive_status != 200:
        raise RuntimeError(f"Archive page returned HTTP {archive_status}")

    task_status, task_page = status_request(opener, args.base_url.rstrip('/') + '/auditee/tugas')
    if task_status not in (200, 302, 403):
        raise RuntimeError(f"Legacy auditee page returned HTTP {task_status}")

    logout_page = request(opener, args.base_url.rstrip('/') + '/dashboard')
    logout_token = csrf(body(logout_page))
    logout = request(opener, args.base_url.rstrip('/') + '/auth/logout', {"csrf_test_name": logout_token})
    if logout.getcode() not in (200, 302):
        raise RuntimeError(f"Logout POST returned HTTP {logout.getcode()}")

    print("M16 HTTP smoke passed.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
