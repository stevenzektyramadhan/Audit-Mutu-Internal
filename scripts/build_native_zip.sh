#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'

fail() {
    printf 'ERROR: %s\n' "$*" >&2
    exit 1
}

require_command() {
    command -v "$1" >/dev/null 2>&1 || fail "required command is unavailable: $1"
}

script_directory="$(CDPATH= cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
repository_root="$(dirname "$script_directory")"

[[ "$(uname -s)" == 'Linux' ]] || fail 'this builder must run on Linux'
[[ "$(pwd -P)" == "$repository_root" ]] || fail "run this builder from the repository root: $repository_root"

for command_name in git php sha256sum cp find sort mktemp date mkdir grep cut mv rm cmp; do
    require_command "$command_name"
done

version="${1:-$(git -C "$repository_root" rev-parse --short=12 HEAD)}"
[[ "$version" =~ ^[A-Za-z0-9][A-Za-z0-9._-]*$ ]] || fail 'version may contain only letters, numbers, dots, underscores, and hyphens'

archive_name="AMI-NATIVE-ALMALINUX-${version}.zip"
distribution_directory="$repository_root/dist"
archive_path="$distribution_directory/$archive_name"
checksum_path="$archive_path.sha256"
manifest_path="$archive_path.manifest.sha256"
metadata_path="$archive_path.metadata.json"
vendor_source="${AMI_PRODUCTION_VENDOR_DIR:-$repository_root/vendor}"

require_tracked_file() {
    local relative_path="$1"
    git -C "$repository_root" ls-files --error-unmatch -- "$relative_path" >/dev/null 2>&1 || fail "required tracked input is missing: $relative_path"
    [[ -f "$repository_root/$relative_path" && ! -L "$repository_root/$relative_path" ]] || fail "required regular input is unavailable: $relative_path"
}

require_regular_file() {
    local relative_path="$1"
    [[ -f "$repository_root/$relative_path" && ! -L "$repository_root/$relative_path" ]] || fail "required native handoff file is missing: $relative_path"
}

copy_tracked_file() {
    local relative_path="$1"
    local destination="$staging_directory/AMI/$relative_path"
    require_tracked_file "$relative_path"
    mkdir -p -- "$(dirname "$destination")"
    cp -p -- "$repository_root/$relative_path" "$destination"
}

copy_allowlisted_file() {
    local relative_path="$1"
    local destination="$staging_directory/AMI/$relative_path"
    require_regular_file "$relative_path"
    mkdir -p -- "$(dirname "$destination")"
    cp -p -- "$repository_root/$relative_path" "$destination"
}

skip_tracked_tree_file() {
    case "$1" in
        application/cache/*|application/logs/*|application/config/production/*.php|application/config/production/*.php.example|assets/css/*.source.css)
            return 0
            ;;
    esac
    return 1
}

copy_tracked_tree() {
    local relative_tree="$1"
    local relative_path
    while IFS= read -r -d '' relative_path; do
        if skip_tracked_tree_file "$relative_path"; then
            continue
        fi
        copy_tracked_file "$relative_path"
    done < <(git -C "$repository_root" ls-files -z -- "$relative_tree")
}

validate_production_vendor() {
    [[ -d "$vendor_source" ]] || fail "production vendor directory is missing: $vendor_source"
    vendor_source="$(CDPATH= cd -- "$vendor_source" && pwd -P)"
    [[ -f "$vendor_source/autoload.php" ]] || fail "production vendor is missing vendor/autoload.php: $vendor_source"
    [[ -f "$vendor_source/composer/installed.php" ]] || fail "production vendor is missing composer metadata: $vendor_source/composer/installed.php"
    [[ -f "$vendor_source/composer/installed.json" ]] || fail "production vendor is missing composer metadata: $vendor_source/composer/installed.json"
    [[ -z "$(find "$vendor_source" -type l -print -quit)" ]] || fail 'production vendor must not contain symlinks'
    [[ -z "$(find "$vendor_source" -type d -name .git -print -quit)" ]] || fail 'production vendor must not contain .git directories'

    AMI_LOCK_PATH="$repository_root/composer.lock" \
    AMI_INSTALLED_PATH="$vendor_source/composer/installed.php" \
    AMI_INSTALLED_JSON_PATH="$vendor_source/composer/installed.json" \
    php <<'PHP'
<?php
declare(strict_types=1);

function vendorFail(string $message): void
{
    fwrite(STDERR, "ERROR: " . $message . PHP_EOL);
    exit(1);
}

$lockContents = @file_get_contents(getenv('AMI_LOCK_PATH') ?: '');
if ($lockContents === false) {
    vendorFail('composer.lock cannot be read');
}
try {
    $lock = json_decode($lockContents, true, 512, JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    vendorFail('composer.lock is not valid JSON');
}

$installed = require (getenv('AMI_INSTALLED_PATH') ?: '');
if (!is_array($installed) || !isset($installed['versions']) || !is_array($installed['versions'])) {
    vendorFail('vendor composer metadata is invalid');
}
if (!empty($installed['root']['dev'])) {
    vendorFail('production vendor was installed with development dependencies');
}

$installedJsonContents = @file_get_contents(getenv('AMI_INSTALLED_JSON_PATH') ?: '');
if ($installedJsonContents === false) {
    vendorFail('production vendor installed.json cannot be read');
}
try {
    $installedJson = json_decode($installedJsonContents, true, 512, JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    vendorFail('production vendor installed.json is not valid JSON');
}
if (($installedJson['dev'] ?? null) !== false) {
    vendorFail('production vendor was installed with development dependencies');
}

$lockedPackages = [];
foreach ($lock['packages'] ?? [] as $package) {
    if (!is_array($package) || empty($package['name'])) {
        vendorFail('composer.lock production package metadata is invalid');
    }
    $lockedPackages[$package['name']] = $package;
}

foreach ($lockedPackages as $name => $lockedPackage) {
    if (!isset($installed['versions'][$name]) || !is_array($installed['versions'][$name])) {
        vendorFail('production vendor is missing locked package: ' . $name);
    }
    $installedPackage = $installed['versions'][$name];
    if (!empty($installedPackage['dev_requirement'])) {
        vendorFail('production vendor contains a dev-only package: ' . $name);
    }
    $expectedReference = $lockedPackage['dist']['reference'] ?? ($lockedPackage['source']['reference'] ?? '');
    if ($expectedReference !== '' && ($installedPackage['reference'] ?? '') !== $expectedReference) {
        vendorFail('production vendor reference does not match composer.lock: ' . $name);
    }
}

foreach ($installed['versions'] as $name => $installedPackage) {
    if ($name === ($installed['root']['name'] ?? '')) {
        continue;
    }
    if (isset($installedPackage['provided'])) {
        continue;
    }
    if (!isset($lockedPackages[$name]) || !empty($installedPackage['dev_requirement'])) {
        vendorFail('production vendor includes a package outside composer.lock production dependencies: ' . $name);
    }
}
PHP
}

vendor_file_is_allowed() {
    local relative_path="$1"
    local base_name="${relative_path##*/}"
    case "$relative_path" in
        .git/*|*/.git/*|.github/*|*/.github/*|test/*|tests/*|*/test/*|*/tests/*|node_modules/*|*/node_modules/*|docs/*|*/docs/*|doc/*|*/doc/*)
            return 1
            ;;
    esac
    case "$base_name" in
        .env|.env.*|Dockerfile|Dockerfile.*|docker-compose*.yml|docker-compose*.yaml|compose*.yml|compose*.yaml|*.swp|*.swo|*~|*.bak|*.dump|*.sql|README*|CHANGELOG*|CONTRIBUTING*|UPGRADING*)
            return 1
            ;;
    esac
    return 0
}

copy_production_vendor() {
    AMI_VENDOR_SOURCE="$vendor_source" AMI_DESTINATION_VENDOR="$staging_directory/AMI/vendor" php <<'PHP'
<?php
declare(strict_types=1);

$vendorSource = getenv('AMI_VENDOR_SOURCE') ?: '';
$destVendor = getenv('AMI_DESTINATION_VENDOR') ?: '';

function vendor_file_is_allowed(string $relativePath): bool {
    $baseName = basename($relativePath);
    if (preg_match('#(^|/)(\.git|\.github|tests?|node_modules|docs?)(/|$)#i', $relativePath)) {
        return false;
    }
    if (preg_match('/^(\.env(\..+)?|Dockerfile(\..+)?|docker-compose.*\.ya?ml|compose.*\.ya?ml|.*\.swp|.*\.swo|.*~|.*\.bak|.*\.dump|.*\.sql|README.*|CHANGELOG.*|CONTRIBUTING.*|UPGRADING.*)$/i', $baseName)) {
        return false;
    }
    return true;
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($vendorSource, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }
    $pathname = $file->getPathname();
    $relativePath = substr($pathname, strlen($vendorSource) + 1);
    if (!vendor_file_is_allowed($relativePath)) {
        continue;
    }
    $targetPath = $destVendor . '/' . $relativePath;
    $targetDir = dirname($targetPath);
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    copy($pathname, $targetPath);
}
PHP

    [[ -f "$staging_directory/AMI/vendor/autoload.php" ]] || fail 'filtered production vendor is missing autoload.php'
    [[ -f "$staging_directory/AMI/vendor/composer/installed.php" ]] || fail 'filtered production vendor is missing composer metadata'
    php -r 'require $argv[1]; if (!class_exists("PhpOffice\\PhpSpreadsheet\\Spreadsheet") || !class_exists("Google\\Client")) { fwrite(STDERR, "ERROR: filtered production vendor cannot autoload required packages" . PHP_EOL); exit(1); }' "$staging_directory/AMI/vendor/autoload.php"
}

verify_production_front_controller() {
    AMI_FRONT_CONTROLLER="$repository_root/index.php" php <<'PHP'
<?php
declare(strict_types=1);

$source = @file_get_contents(getenv('AMI_FRONT_CONTROLLER') ?: '');
if ($source === false) {
    fwrite(STDERR, "ERROR: index.php cannot be read" . PHP_EOL);
    exit(1);
}

$withoutComments = '';
foreach (token_get_all($source) as $token) {
    if (is_array($token)) {
        if ($token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT) {
            continue;
        }
        $withoutComments .= $token[1];
    } else {
        $withoutComments .= $token;
    }
}

if (preg_match('~define\s*\(\s*[\'\"]ENVIRONMENT[\'\"]\s*,\s*[\'\"]production[\'\"]\s*\)~', $withoutComments) !== 1) {
    fwrite(STDERR, "ERROR: index.php must select the production environment before native packaging" . PHP_EOL);
    exit(1);
}
PHP
}

copy_upload_skeleton() {
    local category
    mkdir -p -- "$staging_directory/AMI/uploads"
    copy_tracked_file 'uploads/.htaccess'
    for category in audit_evidence bukti_auditor instrumen penetapan ppepp_documents profil rtm_photos spmi_source tmp user_photos; do
        mkdir -p -- "$staging_directory/AMI/uploads/$category"
        copy_tracked_file "uploads/$category/.gitkeep"
        if git -C "$repository_root" ls-files --error-unmatch -- "uploads/$category/.htaccess" >/dev/null 2>&1; then
            copy_tracked_file "uploads/$category/.htaccess"
        fi
    done
}

materialize_production_configs() {
    local configuration_name
    local source_path
    local destination_path
    for configuration_name in config database ami; do
        source_path="application/config/production/$configuration_name.php.example"
        destination_path="$staging_directory/AMI/application/config/production/$configuration_name.php"
        require_tracked_file "$source_path"
        mkdir -p -- "$(dirname "$destination_path")"
        cp -p -- "$repository_root/$source_path" "$destination_path"
        cmp -s "$repository_root/$source_path" "$destination_path" \
            || fail "production config was not materialized directly from its tracked template: $configuration_name.php"
        php -l "$destination_path" >/dev/null \
            || fail "production config template has invalid PHP syntax: $configuration_name.php.example"
    done
}

write_package_metadata() {
    local source_commit="$1"
    local source_dirty="$2"
    local composer_lock_checksum="$3"
    cat > "$staging_directory/AMI/PACKAGE-METADATA.json" <<EOF
{
  "format": 1,
  "package": "AMI Native AlmaLinux",
  "version": "$version",
  "build_utc": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
  "source_commit": "$source_commit",
  "source_tree_dirty": $source_dirty,
  "composer_lock_sha256": "$composer_lock_checksum",
  "vendor": "verified production-only composer tree",
  "config_files": [
    "application/config/production/config.php",
    "application/config/production/database.php",
    "application/config/production/ami.php"
  ]
}
EOF
}

verify_clean_payload() {
    local package_root="$staging_directory/AMI"
    local relative_path
    local blocked_path

    for relative_path in \
        index.php \
        application \
        system \
        assets \
        vendor/autoload.php \
        uploads/.htaccess \
        database_schema.sql \
        scripts/bootstrap_super_admin.php \
        scripts/ami_doctor.php \
        deploy/nginx/ami.conf \
        deploy/php-fpm/ami.conf \
        docs/DEPLOY_NATIVE_ALMALINUX_ZIP.md \
        docs/KONFIGURASI_IT_RINGKAS.md \
        PACKAGE-METADATA.json; do
        [[ -e "$package_root/$relative_path" ]] || fail "package staging is missing required path: $relative_path"
    done

    for relative_path in .git .env node_modules tests test backups backup database_init.sql database_dummy.sql composer.json composer.lock package.json package-lock.json tailwind.config.js PROMPT_OPENCODE_AMI_NATIVE_ALMALINUX_ZIP.md; do
        [[ ! -e "$package_root/$relative_path" ]] || fail "forbidden package path was staged: $relative_path"
    done

    blocked_path="$(find "$package_root" \( -name .git -o -name .env -o -name '.env.*' -o -name node_modules -o -name test -o -name tests -o -name backup -o -name backups -o -name Dockerfile -o -name 'Dockerfile.*' -o -name 'docker-compose*.yml' -o -name 'docker-compose*.yaml' -o -name 'compose*.yml' -o -name 'compose*.yaml' -o -name '*.swp' -o -name '*.swo' -o -name '*~' -o -name '*.bak' -o -name '*.dump' -o -name '*.backup' \) -print -quit)"
    [[ -z "$blocked_path" ]] || fail "forbidden generated or secret-bearing artifact was staged: ${blocked_path#$package_root/}"

    while IFS= read -r -d '' relative_path; do
        relative_path="${relative_path#$package_root/}"
        case "$relative_path" in
            uploads/.htaccess|uploads/*/.gitkeep|uploads/*/.htaccess)
                ;;
            *)
                fail "upload payload is not a clean skeleton: $relative_path"
                ;;
        esac
    done < <(find "$package_root/uploads" -type f -print0)

    for relative_path in audit_evidence bukti_auditor instrumen penetapan ppepp_documents profil rtm_photos spmi_source tmp user_photos; do
        [[ -f "$package_root/uploads/$relative_path/.gitkeep" ]] || fail "upload skeleton marker is missing: uploads/$relative_path/.gitkeep"
    done

    while IFS= read -r -d '' relative_path; do
        relative_path="${relative_path#$package_root/}"
        case "$relative_path" in
            application/cache/index.html|application/cache/sessions/.gitkeep|application/logs/index.html)
                ;;
            *)
                fail "runtime cache or log content was staged: $relative_path"
                ;;
        esac
    done < <(find "$package_root/application/cache" "$package_root/application/logs" -type f -print0)

    [[ ! -e "$package_root/application/config/production/config.php.example" ]] || fail 'production config examples must not be shipped beside materialized configs'
    [[ ! -e "$package_root/application/config/production/database.php.example" ]] || fail 'production config examples must not be shipped beside materialized configs'
    [[ ! -e "$package_root/application/config/production/ami.php.example" ]] || fail 'production config examples must not be shipped beside materialized configs'
}

write_internal_manifest() {
    (
        cd "$staging_directory/AMI"
        while IFS= read -r -d '' relative_path; do
            sha256sum -- "${relative_path#./}"
        done < <(find . -type f ! -name PACKAGE-MANIFEST.sha256 -print0 | LC_ALL=C sort -z)
    ) > "$staging_directory/AMI/PACKAGE-MANIFEST.sha256"
}

create_archive() {
    AMI_STAGING_DIRECTORY="$staging_directory" AMI_ARCHIVE_PATH="$staging_directory/$archive_name" php <<'PHP'
<?php
declare(strict_types=1);

$stagingPath = getenv('AMI_STAGING_DIRECTORY') ?: '';
$archivePath = getenv('AMI_ARCHIVE_PATH') ?: '';
if (!class_exists('ZipArchive')) {
    fwrite(STDERR, "ERROR: PHP ZipArchive is required to build the native ZIP" . PHP_EOL);
    exit(1);
}

$archive = new ZipArchive();
if ($archive->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "ERROR: unable to create native ZIP" . PHP_EOL);
    exit(1);
}

try {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($stagingPath, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($iterator as $entry) {
        if (!$entry->isFile() || $entry->isLink()) {
            throw new RuntimeException('staging contains a non-regular file');
        }
        $path = $entry->getPathname();
        if ($path === $archivePath) {
            continue;
        }
        $relativePath = substr($path, strlen($stagingPath) + 1);
        if (strpos($relativePath, 'AMI/') !== 0 || !$archive->addFile($path, $relativePath)) {
            throw new RuntimeException('unable to add an allowlisted file to the ZIP');
        }
    }
    if (!$archive->close()) {
        throw new RuntimeException('unable to finalize native ZIP');
    }
} catch (Throwable $exception) {
    $archive->close();
    @unlink($archivePath);
    fwrite(STDERR, "ERROR: native ZIP creation failed" . PHP_EOL);
    exit(1);
}
PHP
}

verify_archive() {
    AMI_ARCHIVE_PATH="$staging_directory/$archive_name" php <<'PHP'
<?php
declare(strict_types=1);

$archivePath = getenv('AMI_ARCHIVE_PATH') ?: '';
if (!class_exists('ZipArchive')) {
    fwrite(STDERR, "ERROR: PHP ZipArchive is required to verify the native ZIP" . PHP_EOL);
    exit(1);
}
$archive = new ZipArchive();
if ($archive->open($archivePath) !== true) {
    fwrite(STDERR, "ERROR: native ZIP cannot be opened for verification" . PHP_EOL);
    exit(1);
}

$requiredEntries = [
    'AMI/index.php',
    'AMI/vendor/autoload.php',
    'AMI/database_schema.sql',
    'AMI/scripts/bootstrap_super_admin.php',
    'AMI/scripts/ami_doctor.php',
    'AMI/application/config/production/config.php',
    'AMI/application/config/production/database.php',
    'AMI/application/config/production/ami.php',
    'AMI/PACKAGE-METADATA.json',
    'AMI/PACKAGE-MANIFEST.sha256',
];
$entries = [];
for ($index = 0; $index < $archive->numFiles; $index++) {
    $entryName = $archive->getNameIndex($index);
    if (strpos($entryName, 'AMI/') !== 0 || strpos($entryName, '../') !== false) {
        $archive->close();
        fwrite(STDERR, "ERROR: native ZIP has an invalid root entry" . PHP_EOL);
        exit(1);
    }
    $entries[$entryName] = true;
}
$archive->close();

foreach ($requiredEntries as $entryName) {
    if (!isset($entries[$entryName])) {
        fwrite(STDERR, "ERROR: native ZIP is missing a required allowlisted entry" . PHP_EOL);
        exit(1);
    }
}
PHP
}

for relative_path in \
    index.php \
    composer.lock \
    database_schema.sql \
    scripts/bootstrap_super_admin.php \
    application/config/production/config.php.example \
    application/config/production/database.php.example \
    application/config/production/ami.php.example \
    deploy/nginx/ami.conf \
    deploy/php-fpm/ami.conf \
    uploads/.htaccess \
    application/cache/index.html \
    application/logs/index.html; do
    require_tracked_file "$relative_path"
done
require_regular_file scripts/ami_doctor.php

for relative_path in application system assets; do
    [[ -d "$repository_root/$relative_path" ]] || fail "required runtime tree is missing: $relative_path"
    [[ -n "$(git -C "$repository_root" ls-files -- "$relative_path")" ]] || fail "required runtime tree has no tracked files: $relative_path"
done
for relative_path in docs/DEPLOY_NATIVE_ALMALINUX_ZIP.md docs/KONFIGURASI_IT_RINGKAS.md; do
    require_regular_file "$relative_path"
done

validate_production_vendor
verify_production_front_controller

source_commit="$(git -C "$repository_root" rev-parse --verify HEAD)"
source_dirty=false
if [[ -n "$(git -C "$repository_root" status --porcelain)" ]]; then
    source_dirty=true
fi
composer_lock_checksum="$(sha256sum "$repository_root/composer.lock" | cut -d ' ' -f 1)"

staging_directory="$(mktemp -d "$repository_root/.ami-native-package.XXXXXX")"
cleanup() {
    rm -rf -- "$staging_directory"
}
trap cleanup EXIT

mkdir -p -- "$staging_directory/AMI/application/cache/sessions" "$staging_directory/AMI/application/logs"
copy_tracked_tree application
copy_tracked_tree system
copy_tracked_tree assets
copy_tracked_file index.php
copy_tracked_file database_schema.sql
copy_tracked_file scripts/bootstrap_super_admin.php
copy_allowlisted_file scripts/ami_doctor.php
copy_tracked_file deploy/nginx/ami.conf
copy_tracked_file deploy/php-fpm/ami.conf
if git -C "$repository_root" ls-files --error-unmatch -- deploy/nginx/ami-acme-bootstrap.conf >/dev/null 2>&1; then
    copy_tracked_file deploy/nginx/ami-acme-bootstrap.conf
fi
for relative_path in favicon.ico license.txt; do
    if git -C "$repository_root" ls-files --error-unmatch -- "$relative_path" >/dev/null 2>&1; then
        copy_tracked_file "$relative_path"
    fi
done

mkdir -p -- "$staging_directory/AMI/docs"
cp -p -- "$repository_root/docs/DEPLOY_NATIVE_ALMALINUX_ZIP.md" "$staging_directory/AMI/docs/DEPLOY_NATIVE_ALMALINUX_ZIP.md"
cp -p -- "$repository_root/docs/KONFIGURASI_IT_RINGKAS.md" "$staging_directory/AMI/docs/KONFIGURASI_IT_RINGKAS.md"
copy_tracked_file application/cache/index.html
copy_tracked_file application/logs/index.html
printf '' > "$staging_directory/AMI/application/cache/sessions/.gitkeep"
copy_upload_skeleton
materialize_production_configs
copy_production_vendor
write_package_metadata "$source_commit" "$source_dirty" "$composer_lock_checksum"
verify_clean_payload
write_internal_manifest
create_archive
verify_archive

(
    cd "$staging_directory"
    sha256sum -- "$archive_name" > "$archive_name.sha256"
)
cp -p -- "$staging_directory/AMI/PACKAGE-MANIFEST.sha256" "$staging_directory/$archive_name.manifest.sha256"
cp -p -- "$staging_directory/AMI/PACKAGE-METADATA.json" "$staging_directory/$archive_name.metadata.json"

for output_path in "$archive_path" "$checksum_path" "$manifest_path" "$metadata_path"; do
    [[ ! -e "$output_path" ]] || fail "refusing to overwrite existing release artifact: $output_path"
done

mkdir -p -- "$distribution_directory"
mv -- "$staging_directory/$archive_name" "$archive_path"
mv -- "$staging_directory/$archive_name.sha256" "$checksum_path"
mv -- "$staging_directory/$archive_name.manifest.sha256" "$manifest_path"
mv -- "$staging_directory/$archive_name.metadata.json" "$metadata_path"

printf 'Created: %s\n' "$archive_path"
printf 'SHA-256: %s\n' "$checksum_path"
printf 'Manifest: %s\n' "$manifest_path"
printf 'Metadata: %s\n' "$metadata_path"
