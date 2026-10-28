# Prompt: Set Up CodeIgniter 3 on Windows with Nginx and XAMPP

You are the receiving deployment AI. Set up this CodeIgniter 3 checkout for **Windows local development** with Nginx, XAMPP PHP, and XAMPP MySQL or MariaDB. Nginx on Windows is not recommended for public internet production. This prompt is for a local workstation only.

## Operating rules

1. Inspect the actual checkout before making any recommendation or change. Do not guess paths, versions, schema state, credentials, or service state.
2. At the start, run `git status --short` from the checkout root. Preserve every local change. Never run `git reset`, `git checkout`, `git restore`, a config overwrite, or a commit.
3. Do not modify SQL, configuration, migrations, or user dirty files unless the operator explicitly approves a specific edit. Do not start or stop services, import a database, install dependencies, or run destructive Git commands as part of this prompt without explicit approval.
4. On any error, missing prerequisite, unexpected file, occupied port, non-empty database, or unclear choice, stop and report the exact command, output, and decision required. Do not work around the error by guessing.
5. Treat the root `README.md` and the deployment templates under `deploy/` as authoritative. If they conflict with the older `docs/PANDUAN_DEPLOY_MULTISITE_CI3.md`, follow the root README and deployment templates and report the conflict.
6. Never print, invent, or add production credentials, default credentials, plaintext passwords, or secrets. Never put a Linux PHP-FPM UNIX socket in a Windows configuration.

## Source-of-truth and checkout discovery

From PowerShell, first identify the checkout and inspect its state. Run read-only checks such as:

```powershell
Get-Location
git rev-parse --show-toplevel
git status --short
Test-Path .\index.php
Test-Path .\application
Test-Path .\composer.json
Test-Path .\composer.lock
Test-Path .\database_schema.sql
Test-Path .\database_init.sql
Get-ChildItem .\deploy\nginx, .\migrations -File | Select-Object FullName
```

Confirm that the checkout is the intended project before continuing. The expected local document root is:

```text
C:/xampp/htdocs/Audit-Mutu-Internal
```

If the checkout is elsewhere, either stop and report the mismatch or obtain explicit approval to adapt the paths. Do not copy the project or overwrite an existing XAMPP checkout.

## Discover Windows, XAMPP, Nginx, PHP, and ports

Use read-only discovery. Adapt only after reporting what exists:

```powershell
$xampp = 'C:\xampp'
$php = 'C:\xampp\php\php.exe'
$phpCgi = 'C:\xampp\php\php-cgi.exe'
$nginx = 'C:\nginx\nginx.exe'

Get-ComputerInfo | Select-Object WindowsProductName, WindowsVersion, OsBuildNumber
Test-Path $xampp
Test-Path "$xampp\apache\bin\httpd.exe"
Test-Path "$xampp\mysql\bin\mysql.exe"
Test-Path $php
Test-Path $phpCgi
Test-Path $nginx
if (Test-Path $php) { & $php -v; & $php --ini; & $php -m }
if (Test-Path $nginx) { & $nginx -v }
Get-Command composer -ErrorAction SilentlyContinue
Get-Command mysql -ErrorAction SilentlyContinue
Get-NetTCPConnection -State Listen -ErrorAction SilentlyContinue |
  Where-Object LocalPort -in 80,443,3306,9000 |
  Select-Object LocalAddress,LocalPort,OwningProcess
```

Check the process names for any occupied port:

```powershell
Get-NetTCPConnection -State Listen -ErrorAction SilentlyContinue |
  Where-Object LocalPort -in 80,443,3306,9000 |
  ForEach-Object { Get-Process -Id $_.OwningProcess -ErrorAction SilentlyContinue |
    Select-Object Id,ProcessName,Path }
```

Port decisions are mandatory:

- Nginx owns local HTTP and HTTPS on 80 and 443. If XAMPP Apache owns either port, stop Apache through the XAMPP Control Panel only after the operator approves it. Do not silently change ports.
- XAMPP MySQL or MariaDB normally owns 3306. Do not stop or replace an existing database service without approval.
- PHP CGI FastCGI listens only on loopback, for example `127.0.0.1:9000`. Never expose port 9000 to the LAN or internet.
- If a required port is occupied by an unknown process, stop and report it.

Check required PHP support. The project requires PHP `>=7.4`, and the repository has been tested with PHP 8.3. Confirm `mysqli`, `mbstring`, `xml`, `zip`, `gd`, `curl`, and `fileinfo`. Check `intl`, `openssl`, and `pdo_mysql` as needed by the installed application and Composer packages:

```powershell
& $php -r "echo PHP_VERSION, PHP_EOL; foreach (['mysqli','mbstring','xml','zip','gd','curl','fileinfo','intl','openssl','pdo_mysql'] as `$e) { echo `$e, ': ', extension_loaded(`$e) ? 'yes' : 'no', PHP_EOL; }"
```

Stop if PHP, `php-cgi.exe`, a required extension, Composer, or MySQL is missing. Do not download or install a replacement without approval.

## Windows PHP CGI and Nginx boundary

Windows does not use the Linux PHP-FPM UNIX socket. Use XAMPP's CGI executable over loopback FastCGI:

```text
C:/xampp/php/php-cgi.exe
127.0.0.1:9000
```

Before starting anything, inspect the XAMPP PHP configuration and confirm the effective `php.ini`:

```powershell
& $php --ini
Select-String -Path 'C:\xampp\php\php.ini' -Pattern '^(;\s*)?(cgi.fix_pathinfo|cgi.force_redirect|upload_max_filesize|post_max_size|memory_limit|max_execution_time)\s*='
```

For this local setup, the intended PHP settings are:

```ini
cgi.fix_pathinfo=1
cgi.force_redirect=0
upload_max_filesize=20M
post_max_size=25M
memory_limit=256M
max_execution_time=120
```

Review the existing file and preserve unrelated local edits. Do not overwrite `php.ini`. If the operator approves changing these values, restart the `php-cgi.exe` process after the edit and verify with `php --ini` and a runtime `phpinfo()` or equivalent check. Do not expose CGI port 9000.

## Nginx configuration skeleton

Adapt the security intent of `deploy/nginx/ami.conf`, but do not copy its Linux socket or production-only TLS assumptions. Use a Windows Nginx server block with forward slashes and this document root:

```nginx
server {
    listen 80;
    server_name localhost 127.0.0.1;
    root C:/xampp/htdocs/Audit-Mutu-Internal;
    index index.php;

    client_max_body_size 25M;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location ~ /\. { deny all; access_log off; log_not_found off; }
    location ~* ^/(application|system|migrations|vendor|deploy|docs|scripts|\.git)(/|$) { return 404; }
    location ~* ^/uploads/(audit_evidence|bukti_auditor|instrumen|penetapan|ppepp_documents|rtm_photos|spmi_source|tmp|user_photos)(/|$) { return 404; }
    location ~* ^/(uploads|assets)/.*\.(php[0-9]?|phtml|phar|sh|cgi|pl|py)$ { return 403; }
    location ~* \.(sql|env.*|ini|log|sh|bash)$ { return 404; }

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    # Only the CodeIgniter front controller reaches PHP.
    location = /index.php {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root/index.php;
        fastcgi_param SCRIPT_NAME /index.php;
        fastcgi_param PATH_INFO '';
        fastcgi_param CI_ENV development;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_read_timeout 120s;
    }

    location ~ ^/index\.php/ {
        fastcgi_split_path_info ^(/index\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root/index.php;
        fastcgi_param SCRIPT_NAME /index.php;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param CI_ENV development;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_read_timeout 120s;
    }

    location ~ \.php(?:/|$) { return 404; }
}
```

The skeleton denies all PHP except `index.php`, denies the application and source directories listed above, denies private upload categories and executable uploads, and keeps request limits aligned with PHP. If public profile images are served as static files, allow only the narrow safe image pattern required by the actual checkout. Public profile images do not make any other upload category public.

Save a reviewed local copy only where the installed Nginx expects it. Do not overwrite a user configuration. Test the exact configuration before reload or start:

```powershell
& $nginx -t -p 'C:\nginx' -c 'conf\nginx.conf'
```

The command must exit successfully. If it does not, stop and report the complete error. Do not start or reload Nginx until the operator approves service changes.

## Application configuration and Composer

Review the existing local CodeIgniter configuration. Do not overwrite it. Local configuration should normally use `127.0.0.1`, database `ami`, driver `mysqli`, and the development environment. Confirm `APP_BASE_URL` matches the chosen local URL and includes its trailing slash. Keep private storage and logs out of the public document root for any setup intended to resemble public access.

From the checkout root, after explicit approval to install dependencies, use the lock file:

```powershell
composer install --no-dev --prefer-dist --no-interaction --no-scripts
```

Never run `composer update` for this setup. The `--no-scripts` option prevents the repository Composer post-install script from being run blindly. Report the resulting Composer output and any missing extension or platform failure.

## Database path A, fresh empty database

Use this path only after confirming that the target `ami` database is empty and disposable. If it contains any tables or data, stop and use the existing database path instead. Do not combine these paths.

1. Create or select an **empty** `ami` database with `utf8` and `utf8_general_ci`. Use the XAMPP MySQL client only after approval:

   ```powershell
   & 'C:\xampp\mysql\bin\mysql.exe' -u root -p -e "CREATE DATABASE ami CHARACTER SET utf8 COLLATE utf8_general_ci;"
   ```

2. Choose one fresh bootstrap path. Import **only** `database_schema.sql` into the empty database, then run `scripts/bootstrap_super_admin.php` for operator-selected credentials, or import `database_init.sql` for the canonical schema with one already-hashed starter account. Do not import either fresh bootstrap file into an existing database. Do not replay migrations after a fresh import because the canonical schema already includes parity through migration `050`.

    ```powershell
    Get-Content -Raw .\database_schema.sql | & 'C:\xampp\mysql\bin\mysql.exe' -u root -p ami
    ```

    Untuk jalur seeded pada database baru yang kosong, gunakan perintah berikut sebagai alternatif:

    ```powershell
    Get-Content -Raw .\database_init.sql | & 'C:\xampp\mysql\bin\mysql.exe' -u root -p ami
    ```

3. If the starter-account path was used, the operator must change or rotate the initial account credential immediately after first access. Do not document or print that credential.
4. For the schema-only path, bootstrap the first administrator through the repository script. Do not document or print a plaintext or default password:

   ```powershell
   & 'C:\xampp\php\php.exe' .\scripts\bootstrap_super_admin.php
   ```

    The script's password input is hidden on Unix only. In a standard Windows console, interactive input is visible. Use a private console if entering credentials through the script, or choose the seeded `database_init.sql` alternative. Never echo credentials into the report.

## Database path B, existing database

This path is strictly separate from the fresh path. Before any import or create operation, hard stop and obtain explicit confirmation that this is an existing database upgrade. Do not create or import into it automatically.

Before any schema change, make verified backups of the database, application files, private storage, and relevant local configuration. Confirm where each backup is stored and that it can be restored. Do not proceed without that evidence.

Only after DBA review may you identify raw migrations missing from the existing schema and apply them manually, one file at a time, in numeric order. CodeIgniter migrations are disabled. `migrations/*.sql` is manual upgrade history, not an automatic bootstrap mechanism. There are two historical files named `009`; do not infer which one is missing. Resolve that ambiguity with the DBA and inspect the actual schema first. Migration `034_retire_spmi_instruments.sql` is destructive in its effect and requires explicit DBA approval and a tested backup. Never use `--force`, never disable foreign key checks, never run a `DOWN` block, and stop at the first error.

Do not import `database_schema.sql` or `database_init.sql` into an existing database. Report the exact reviewed migration filenames selected, the DBA approval, the backup evidence, and the result of each manual operation.

## Storage and Windows permissions

Use Windows NTFS ACLs. Grant write access only to the account that actually runs PHP and the application process. Do not grant `Everyone:Full Control`. Keep logs, sessions, and private storage outside the document root when the setup is used for public-like testing. At minimum, treat these categories as private and deny direct web access:

```text
audit_evidence
bukti_auditor
instrumen
penetapan
ppepp_documents
rtm_photos
spmi_source
tmp
user_photos
```

Public profile images may be static only when the application and Nginx rule explicitly restrict them to the intended image files. Do not expose executable uploads or private files. Inspect the actual configured storage paths before creating directories or changing ACLs, and obtain approval for each write or permission change.

## Verification gate

Do not report success until the operator has approved the relevant service and database actions and the following checks have evidence. Run read-only checks wherever possible:

- PHP reports a supported version, required extensions are loaded, and the effective `php.ini` values match the approved settings.
- MySQL or MariaDB is sufficiently current, MySQL 8 or MariaDB 10.4 or newer.
- The fresh path, if selected, imports `database_schema.sql` successfully into an isolated empty database and bootstraps the first administrator, or imports `database_init.sql` successfully instead. The existing path, if selected, records DBA-reviewed manual migrations instead.
- `SHOW TABLES` returns the expected current schema tables.
- `system_settings` has exactly one singleton row.
- The upload settings table has seven expected upload-setting rows.
- The `UNIVERSITAS` organization root exists.
- The first administrator can log in without exposing its password in commands, logs, or the report.
- A normal application URL works through Nginx and the front controller.
- Requests for `application`, `system`, `migrations`, `vendor`, `deploy`, `docs`, `scripts`, and `.git` return denial or a non-disclosing 404.
- Requests for private upload paths return denial or a non-disclosing 404, and executable uploads are denied.
- Nginx passes `nginx.exe -t` before any approved reload or start.
- PHP CGI is bound only to loopback and port 9000 is not reachable from the network.

At the end, report the checkout path, the database path selected, commands actually run, service actions not run because approval was absent, validation results, and every error. Do not claim a deployment that you did not execute and verify.
