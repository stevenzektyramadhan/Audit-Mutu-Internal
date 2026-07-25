# M1-07 Security Headers

## Tujuan dan boundary

`application/config/security_headers.php` dipanggil oleh front controller
`index.php` sebelum CodeIgniter menangani request. Karena itu kebijakan berlaku
untuk seluruh controller HTML, termasuk controller lama yang belum mewarisi
`MY_Controller`.

Respons download sensitif tetap mengganti CSP halaman dengan
`sandbox; default-src 'none'` dan memakai cache policy yang lebih khusus.

## Header yang diterapkan

| Header | Nilai/kebijakan |
|---|---|
| `Content-Security-Policy` | Default same-origin, object/frame/media/worker ditolak, form hanya same-origin, serta nonce per request untuk script dan style element. |
| `X-Frame-Options` | `DENY`, konsisten dengan `frame-ancestors 'none'`. |
| `X-Content-Type-Options` | `nosniff`. |
| `Referrer-Policy` | `strict-origin-when-cross-origin`. |
| `Permissions-Policy` | Kamera, mikrofon, lokasi, payment, USB, dan capability sensitif lain ditolak; fullscreen hanya same-origin. |
| `Cross-Origin-Opener-Policy` | `same-origin`. |
| `Cross-Origin-Resource-Policy` | `same-origin`. |
| `X-Permitted-Cross-Domain-Policies` | `none`. |
| `Cache-Control` | Halaman dinamis memakai `private, no-store, max-age=0, must-revalidate`. |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains`, hanya pada request HTTPS di environment production. |

`Pragma: no-cache` dan `Expires: 0` dipertahankan untuk client/proxy lama.

## Content Security Policy

Sumber eksternal dibatasi pada dependency yang saat ini benar-benar digunakan:

- script: `cdnjs.cloudflare.com` dan `cdn.jsdelivr.net`;
- stylesheet: `cdnjs.cloudflare.com` dan `fonts.googleapis.com`;
- font: `cdnjs.cloudflare.com` dan `fonts.gstatic.com`.

Setiap `<script>` dan `<style>` mempunyai nonce yang dibuat ulang untuk setiap
request. CSP tidak mengizinkan `unsafe-eval`, tidak mengizinkan event handler
inline, dan memakai `script-src-attr 'none'`.

Aplikasi masih mempunyai atribut `style="..."` legacy pada banyak view.
Pengecualian `style-src-attr 'unsafe-inline'` dipertahankan hanya untuk konteks
tersebut; style element tetap wajib memakai nonce. Pengecualian ini tidak
berlaku pada JavaScript dan harus dihapus setelah atribut style dipindahkan ke
stylesheet/class.

Jika dependency frontend baru ditambahkan:

1. Utamakan asset lokal atau origin yang sudah disetujui.
2. Tambahkan nonce pada script/style element.
3. Jangan menambahkan wildcard, `unsafe-eval`, atau `script-src
   'unsafe-inline'`.
4. Perbarui regression dan jalankan smoke test.

## HSTS dan reverse proxy

HSTS hanya dikirim jika:

1. `ENVIRONMENT` adalah `production`; dan
2. web server melaporkan `HTTPS=on` atau `SERVER_PORT=443`.

`X-Forwarded-Proto` tidak dipercaya langsung karena header tersebut dapat
dipalsukan client. Reverse proxy tepercaya harus menghapus forwarded header
dari client dan menormalisasi request HTTPS menjadi server variable yang
dipercaya aplikasi. Uji staging wajib memastikan:

- production HTTPS mengirim HSTS;
- HTTP development/testing tidak mengirim HSTS;
- proxy tidak dapat membuat request HTTP terlihat seperti HTTPS dari sumber
  yang tidak tepercaya.

HSTS tidak memakai `preload`; keputusan preload membutuhkan kesiapan seluruh
subdomain dan proses operasional terpisah.

## Cache dan download

Semua halaman dinamis, termasuk login dan dashboard, memakai `no-store`.
Dokumen private memakai attachment, `nosniff`, CSP sandbox, dan `no-store`.
Static asset yang dilayani langsung oleh web server tidak melewati front
controller sehingga cache-nya tetap menjadi tanggung jawab konfigurasi web
server.

## Validasi

Jalankan:

```powershell
php tests/security_headers_regression.php
php tests/smoke/run.php
```

Regression memeriksa policy, kondisi HSTS, seluruh tag script/style, tidak
adanya event handler inline, serta header download. Smoke test mengambil
respons HTTP nyata dan memastikan nonce pada CSP sama dengan nonce HTML.

Validasi lokal 2026-07-24:

```text
[PASS] security headers regression (124 checks)
Smoke tests passed: 30
```

Browser visual/console dan ketersediaan CDN tetap perlu diperiksa manual karena
tidak tersedia browser interaktif pada sesi validasi ini.
