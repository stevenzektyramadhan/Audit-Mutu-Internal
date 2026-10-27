#!/usr/bin/env bash
set -e

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Muat file .env jika ada
if [ -f "$DIR/.env" ]; then
    set -a
    . "$DIR/.env"
    set +a
fi

# Fallback default konfigurasi
export CI_ENV="${CI_ENV:-development}"
export APP_LOG_THRESHOLD="${APP_LOG_THRESHOLD:-1}"
export APP_ENCRYPTION_KEY="${APP_ENCRYPTION_KEY:-local-development-only-key}"
export APP_BASE_URL="${APP_BASE_URL:-http://127.0.0.1:8000/}"

export DB_HOST="${DB_HOST:-172.19.0.2}"
export DB_USERNAME="${DB_USERNAME:-ami_local}"
export DB_PASSWORD="${DB_PASSWORD:-ami_local_password}"
export DB_DATABASE="${DB_DATABASE:-ami}"

# Path upload & storage
export APP_PRIVATE_STORAGE_PATH="${APP_PRIVATE_STORAGE_PATH:-$DIR/storage/private}"
export APP_PUBLIC_UPLOADS_PATH="${APP_PUBLIC_UPLOADS_PATH:-$DIR/uploads}"

for path in \
    "$APP_PRIVATE_STORAGE_PATH"/{audit_evidence,bukti_auditor,instrumen,penetapan,ppepp_documents,rtm_photos,spmi_source,tmp,user_photos} \
    "$APP_PUBLIC_UPLOADS_PATH/profil" \
    "$DIR/application/cache/sessions"; do
    if [ ! -d "$path" ] || [ ! -w "$path" ]; then
        printf 'Path runtime harus sudah ada dan writable: %s\n' "$path" >&2
        exit 1
    fi
done

echo "========================================================"
echo "Sistem Penjaminan Mutu Internal (SPMI) - Manual PHP Host"
echo "========================================================"
echo "Base URL          : $APP_BASE_URL"
echo "Database Host     : $DB_HOST"
echo "Database Name     : $DB_DATABASE"
echo "Private Storage   : $APP_PRIVATE_STORAGE_PATH"
echo "Public Uploads    : $APP_PUBLIC_UPLOADS_PATH"
echo "========================================================"
echo "Server berjalan di: http://127.0.0.1:8000"
echo "Buka Login        : ${APP_BASE_URL}index.php/auth"
echo "Tekan Ctrl+C untuk menghentikan server."
echo "========================================================"

exec php -S 127.0.0.1:8000 -t "$DIR"
