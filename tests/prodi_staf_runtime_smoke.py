#!/usr/bin/env python3
import argparse
import http.cookiejar
import re
import subprocess
import sys
import urllib.error
import urllib.parse
import urllib.request


ADMIN_EMAIL = "admin-lpmpi@p42.runtime.test"
PASSWORD = "p4.2-runtime-password"
SOURCE_PRODI_ID = 920001
TARGET_PRODI_ID = 920002
AUDITOR_ID = 910002
REDIRECT_STATUSES = (301, 302, 303, 307, 308)


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, request, fp, code, msg, headers, newurl):
        return None


def request(opener, url, data=None):
    encoded = None if data is None else urllib.parse.urlencode(data).encode("utf-8")
    return opener.open(url, encoded, timeout=15)


def body(response):
    return response.read().decode("utf-8", "replace")


def csrf(html):
    match = re.search(r'name="csrf_test_name" value="([^"]+)"', html)
    if not match:
        raise RuntimeError("CSRF token not found")
    return match.group(1)


def normalize_destination(url):
    parsed = urllib.parse.urlsplit(url)
    if parsed.scheme not in ("http", "https") or not parsed.netloc or parsed.query or parsed.fragment:
        raise RuntimeError(f"invalid roster destination: {url!r}")
    return urllib.parse.urlunsplit((parsed.scheme, parsed.netloc, parsed.path.rstrip("/") or "/", "", ""))


def expect_redirect(opener, redirect_opener, url, data, roster_url, success):
    destination = normalize_destination(roster_url)
    try:
        response = request(redirect_opener, url, data)
    except urllib.error.HTTPError as error:
        if error.code not in REDIRECT_STATUSES:
            raise RuntimeError(f"POST {url} returned HTTP {error.code}") from error
        location = error.headers.get("Location", "")
        if normalize_destination(urllib.parse.urljoin(url, location)) != destination:
            raise RuntimeError(f"POST {url} redirected to {location!r}, not {destination!r}")
    else:
        if normalize_destination(response.geturl()) != destination:
            raise RuntimeError(f"POST {url} ended at {response.geturl()!r}, not {destination!r}")

    response = request(opener, destination)
    html = body(response)
    if response.getcode() != 200:
        raise RuntimeError(f"GET {destination} returned HTTP {response.getcode()}")
    if success not in html:
        raise RuntimeError(f"GET {destination} did not render {success!r}")


def roster_token(opener, roster_url):
    response = request(opener, roster_url)
    html = body(response)
    if response.getcode() != 200 or "P4.2 Source Prodi" not in html and "P4.2 Target Prodi" not in html:
        raise RuntimeError("authenticated management roster GET was not allowed")
    return csrf(html)


def query(args, sql):
    command = [
        "docker", "compose", "-p", args.project, "-f", args.compose_file,
        "exec", "-T", "db", "mysql", "-N", "-B",
        "-uami_runtime", "-pami_runtime_password", "ami", "-e", sql,
    ]
    result = subprocess.run(command, check=True, capture_output=True, text=True)
    return result.stdout.strip()


def expect_state(args, expected):
    actual = query(args, "SELECT id, id_akun, id_prodi, jabatan, status FROM staf_prodi WHERE id_akun = 910002;")
    if actual != expected:
        raise RuntimeError(f"unexpected staf_prodi state: {actual!r}, expected {expected!r}")


def main():
    parser = argparse.ArgumentParser(description="P4.2 isolated staf_prodi HTTP smoke")
    parser.add_argument("--base-url", required=True)
    parser.add_argument("--project", required=True)
    parser.add_argument("--compose-file", required=True)
    args = parser.parse_args()
    base_url = args.base_url.rstrip("/")
    source_roster = f"{base_url}/profil/prodi/{SOURCE_PRODI_ID}/staf"
    target_roster = f"{base_url}/profil/prodi/{TARGET_PRODI_ID}/staf"

    anonymous = urllib.request.build_opener(NoRedirect())
    try:
        request(anonymous, source_roster)
    except urllib.error.HTTPError as error:
        if error.code not in REDIRECT_STATUSES or "/index.php/auth" not in error.headers.get("Location", ""):
            raise RuntimeError("unauthenticated roster request was not redirected to login") from error
    else:
        raise RuntimeError("unauthenticated roster request was unexpectedly allowed")

    jar = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
    redirect_opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar), NoRedirect())
    login_page = request(opener, f"{base_url}/auth")
    login_token = csrf(body(login_page))
    login = request(opener, f"{base_url}/auth/login", {"csrf_test_name": login_token, "email": ADMIN_EMAIL, "password": PASSWORD})
    if login.getcode() != 200 or "/lpmpi/spmi-dashboard" not in login.geturl():
        raise RuntimeError(f"admin login did not reach its dashboard: {login.geturl()!r}")

    add_token = roster_token(opener, source_roster)
    expect_redirect(opener, redirect_opener, f"{source_roster}/add", {"csrf_test_name": add_token, "id_akun": AUDITOR_ID, "jabatan": "P4.2 Auditor"}, source_roster, "Staf program studi berhasil ditambahkan.")
    relation_id = query(args, "SELECT id FROM staf_prodi WHERE id_akun = 910002 AND id_prodi = 920001 AND status = 'active';")
    if not relation_id.isdigit():
        raise RuntimeError(f"add did not create exactly one known relation ID: {relation_id!r}")
    expect_state(args, f"{relation_id}\t910002\t920001\tP4.2 Auditor\tactive")

    move_token = roster_token(opener, source_roster)
    expect_redirect(opener, redirect_opener, f"{source_roster}/move/{relation_id}", {"csrf_test_name": move_token, "target_prodi_id": TARGET_PRODI_ID}, source_roster, "Staf berhasil dipindahkan ke program studi tujuan.")
    expect_state(args, f"{relation_id}\t910002\t920002\tP4.2 Auditor\tactive")

    deactivate_token = roster_token(opener, target_roster)
    expect_redirect(opener, redirect_opener, f"{target_roster}/deactivate/{relation_id}", {"csrf_test_name": deactivate_token}, target_roster, "Relasi staf berhasil dinonaktifkan.")
    expect_state(args, f"{relation_id}\t910002\t920002\tP4.2 Auditor\tinactive")
    print("P4.2 staf_prodi HTTP smoke passed.")


if __name__ == "__main__":
    sys.exit(main())
