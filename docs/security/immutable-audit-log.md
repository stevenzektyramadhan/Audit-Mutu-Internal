# M1-08 Immutable Audit Log

## Tujuan dan boundary

M1-08 menambahkan ledger terpusat untuk aksi keamanan dan perubahan domain
sensitif. Ledger berbeda dari:

- log teknis CodeIgniter, yang digunakan untuk diagnosis;
- `auth_security_events`, yang tetap digunakan untuk login throttling; dan
- `file_security_events`, yang tetap menjadi histori operasional file.

Ketiga sumber khusus tersebut tetap dipertahankan, tetapi event penting juga
ditulis ke `security_audit_logs` sebagai sumber audit umum.

## Struktur dan immutability

Migration `014_immutable_security_audit_log.sql` membuat:

- `security_audit_logs`: event append-only;
- `security_audit_chain_state`: satu row head untuk serialisasi writer dan
  verifikasi chain;
- trigger `trg_security_audit_logs_no_update`;
- trigger `trg_security_audit_logs_no_delete`.

Model aplikasi hanya menyediakan `append()` dan `verify_chain()`. Tidak ada API
model, route, controller web, atau UI untuk mengubah/menghapus log. Trigger
database menolak `UPDATE` dan `DELETE` meskipun query dilakukan langsung dengan
akun aplikasi.

`actor_user_id` sengaja tidak memakai foreign key ke `users`. Identitas actor
historis tetap dipertahankan ketika user operasional dihapus.

## Tamper-evident hash chain

Setiap entry mempunyai:

- random `event_uuid`;
- `previous_hash` dari head sebelumnya;
- canonical event fields;
- `entry_hash = SHA-256(canonical event)`.

Writer mengunci row `security_audit_chain_state` dengan `SELECT ... FOR UPDATE`.
Hal ini mencegah dua request paralel membuat cabang chain dari head yang sama.
JSON dinormalisasi dan key diurutkan sebelum hashing agar hasil tetap sama
setelah disimpan/dibaca kembali oleh MySQL.

Verifikasi:

```powershell
$env:CI_ENV = 'development'
php index.php maintenance verify_audit_log
```

`CI_ENV` perlu disetel pada shell CLI Laragon. Tanpa nilai `development`,
`testing`, atau `production`, bootstrap sengaja berhenti dengan pesan
`The application environment is not set correctly.`

Exit code `0` berarti seluruh chain dan head state cocok. Hasil tidak
menampilkan isi snapshot, IP, user-agent, atau secret.

## Data yang disimpan

| Field | Isi |
|---|---|
| Actor/object/action | ID dan token domain, bukan label/nama pengguna. |
| `before_hash`, `after_hash` | HMAC-SHA256 snapshot; isi state tidak disimpan. |
| `changes_json` | Ringkasan allowlist seperti role/status transition, jumlah row, format, kategori, dan daftar nama field. |
| `ip_address` | `hmac-sha256:<digest>`, bukan IP mentah. |
| `user_agent` | `hmac-sha256:<digest>`, bukan user-agent mentah. |
| `request_id` | Correlation ID yang sama dengan response/log teknis. |

Audit logger tidak membaca request body. Key yang mengandung password, token,
secret, cookie, authorization, CSRF, atau session dikeluarkan dari snapshot.
Metadata bebas tidak diterima; hanya key yang ada dalam allowlist.

## Event aktif

| Area | Event |
|---|---|
| Authentication | login gagal/sukses/throttled, logout, timeout, session revoked, authorization override |
| Akun | create, update, role change, delete |
| Standar | create, update, delete |
| Indikator/pertanyaan | create, update, delete, bulk XLSX import |
| Periode | create, update, activate, deactivate, delete |
| Penugasan | create dan delete |
| Auditee | final submit |
| Auditor | submit/final assessment dan request revisi |
| File | upload, blocked upload/download, download, retirement/delete, temporary cleanup, purge, legacy registration |
| Export | export laporan sensitif XLSX |

Draft autosave tidak dicatat untuk menghindari volume/noise berlebihan; submit
dan perubahan state tetap dicatat.

Modul final report terpisah, RTM, PIC/target, dan verifikasi tindak lanjut belum
ada pada schema/aplikasi. Event finalisasi untuk modul tersebut wajib
ditambahkan ketika surface-nya dibuat dan tetap deny-by-default saat ini.

## Migration dan deployment

Terapkan migration 014 sebelum code M1-08 menerima traffic:

```bash
mysql -u <user> -p <database> < migrations/014_immutable_security_audit_log.sql
```

Migration dapat dijalankan ulang. Tabel/state dipertahankan, sedangkan kedua
trigger dipasang ulang dalam maintenance window.

Runner Laragon lokal:

```powershell
php scripts/database/apply_local_m1_08.php
```

Runner menolak production dan host database non-local. Migration sudah
diterapkan dua kali pada database lokal 2026-07-24 untuk membuktikan hasil
idempotent.

Untuk defense-in-depth, DBA production sebaiknya mencabut privilege `UPDATE`
dan `DELETE` akun aplikasi khusus pada `security_audit_logs`, sambil tetap
memberikan `INSERT` yang diperlukan dan `SELECT/UPDATE` terbatas pada chain
state. Trigger bukan pengganti least privilege, backup immutable, atau
monitoring DBA.

## Retention, backup, dan monitoring

Tidak ada purge aplikasi untuk ledger M1-08. Retention resmi membutuhkan
keputusan stakeholder/legal. Sampai keputusan itu ada:

- jangan hapus entry;
- backup ledger dan chain state bersama database;
- jalankan verifier terjadwal dan alert jika exit non-zero;
- simpan head hash berkala pada sistem monitoring/backup yang terpisah agar
  perubahan oleh DBA berprivilege dapat dibandingkan;
- uji restore lalu jalankan verifier dan smoke suite.

## Validasi

```text
[PASS] immutable security audit regression (112 checks)
Smoke tests passed: 30
```

Smoke test membuktikan event auth, assignment, submit, final assessment, file,
dan export; memverifikasi seluruh chain; memastikan IP/user-agent berbentuk
HMAC; serta melakukan percobaan langsung `UPDATE` dan `DELETE` yang ditolak
trigger.

## Residual risk

- DBA yang dapat mengubah schema dapat menjatuhkan trigger; least privilege,
  external head anchoring, alert, dan backup tetap diperlukan.
- Beberapa legacy mutation melakukan business commit sebelum append audit.
  Kegagalan append menghasilkan security log, tetapi transactional outbox
  lintas seluruh service belum tersedia.
- Kebijakan retention/legal hold dan restricted audit viewer/export belum
  diputuskan.
- Event untuk modul yang belum dibangun harus menjadi bagian Definition of
  Done modul tersebut.
