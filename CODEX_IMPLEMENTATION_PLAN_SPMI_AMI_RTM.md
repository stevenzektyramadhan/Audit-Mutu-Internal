# Rencana Implementasi Codex — SPMI, AMI, RTM, dan PPEPP

> **Repository target:** `stevenzektyramadhan/Audit-Mutu-Internal`
>
> **Dokumen requirement yang harus tersedia di repository:**
>
> 1. `BUSINESS_REQUIREMENTS_SPMI_AMI_RTM.md` — ringkasan requirement mandiri hasil analisis dokumen bisnis.
> 2. `CODEX_IMPLEMENTATION_PLAN_SPMI_AMI_RTM.md` — backlog dan instruksi teknis ini.
>
> **Catatan penting tentang PDF sumber:**
> PDF `STANDAR SPMI ✔✔✔✔.pdf` dan `LAPORAN RTM AMI 2024-2025.pdf` dianalisis di luar repository. Keduanya **tidak diasumsikan tersedia bagi Codex**. Codex tidak boleh mengklaim membaca PDF tersebut dan tidak boleh bergantung pada keberadaannya. Semua requirement yang diperlukan untuk implementasi harus dibaca dari `BUSINESS_REQUIREMENTS_SPMI_AMI_RTM.md`.
>
> **Tujuan akhir:** mengembangkan aplikasi AMI yang ada menjadi sistem SPMI berbasis siklus **PPEPP**:
>
> `Penetapan → Pelaksanaan → Evaluasi → Pengendalian → Peningkatan`

---

## 0. Status Struktur: Existing, Proposed, dan To Verify

Dokumen ini berisi tiga jenis informasi:

### A. Existing — hasil observasi awal

Observasi awal dilakukan secara statis terhadap branch `dev`, termasuk struktur CodeIgniter, routes, controller, service, model, schema, dan test yang terlihat saat analisis. Observasi tersebut bukan pengganti inspeksi repo oleh Codex pada commit yang sedang dikerjakan.

### B. Proposed — target desain

Semua nama seperti berikut pada umumnya adalah **struktur usulan**, bukan klaim bahwa struktur tersebut sudah tersedia:

- `spmi_versions`;
- `spmi_standards`;
- `spmi_statements`;
- `spmi_indicators`;
- `organization_units`;
- capability matrix;
- paket instrumen;
- snapshot audit baru;
- `rtm_meetings`;
- `rtm_items`;
- follow-up verification;
- state machine baru.

Codex wajib menyesuaikan nama dan desain dengan keadaan aktual repo, mencegah duplikasi, dan mencatat keputusan melalui ADR.

### C. To Verify — belum dipastikan

Hal yang harus diverifikasi pada M0/M1 sebelum implementasi:

- class dan route mana yang benar-benar aktif;
- tabel aktual production versus `database_schema.sql`;
- implementasi legacy mana yang masih dipakai;
- skala dan rubrik skor;
- definisi OB/KTS;
- formula risk-based audit;
- struktur organisasi aktual;
- kewajiban dokumen RTM;
- retensi file;
- aturan finalisasi.

### Aturan interpretasi

1. Requirement bisnis dibaca dari `BUSINESS_REQUIREMENTS_SPMI_AMI_RTM.md`.
2. Fakta kode dibaca dari repository yang sedang checkout.
3. Struktur dalam task adalah proposal awal.
4. Bila proposal bertabrakan dengan kode aktual, Codex harus membuat gap analysis, bukan langsung menggandakan struktur.
5. M0-01 sampai M0-04 harus selesai sebelum migration fitur utama dimulai.

---

## 1. Cara Menggunakan Dokumen Ini

Dokumen ini adalah backlog teknis dan instruksi kerja untuk AI coding agent, khususnya Codex.

### Aturan eksekusi

0. Baca `BUSINESS_REQUIREMENTS_SPMI_AMI_RTM.md` sebelum menyusun perubahan fitur. Jangan bergantung pada PDF yang tidak tersedia di repository.
1. Kerjakan task berdasarkan urutan dependensi, bukan berdasarkan urutan yang terlihat paling mudah.
2. Satu task harus menghasilkan satu perubahan yang dapat ditinjau dan diuji.
3. Jangan menggabungkan migrasi besar, refactor besar, dan fitur UI besar dalam satu commit.
4. Sebelum mengubah kode, agent wajib:
   - membaca `README.md`;
   - membaca `composer.json`;
   - membaca konfigurasi database dan environment;
   - membaca `application/config/routes.php`;
   - memetakan controller, service, model, view, helper, library, migration, dan test yang terkait;
   - memeriksa schema database aktual;
   - mencatat perbedaan antara dokumen ini dan keadaan repo.
5. Bila nama file, tabel, kolom, atau alur berbeda dari asumsi dokumen ini:
   - jangan membuat duplikasi;
   - gunakan struktur aktual repo;
   - tulis alasan adaptasi pada deskripsi commit atau PR.
6. Jangan menghapus workflow lama sebelum:
   - data lama dimigrasikan;
   - semua endpoint pengganti tersedia;
   - regression test lulus;
   - ada rollback plan.
7. Semua perubahan database harus:
   - idempotent sejauh memungkinkan;
   - memiliki migration `up` dan rollback/down atau prosedur rollback terdokumentasi;
   - tidak menghapus data secara langsung;
   - diuji pada salinan database.
8. Semua endpoint baru harus memiliki:
   - autentikasi;
   - otorisasi;
   - validasi input server-side;
   - proteksi CSRF untuk request berbasis session;
   - ownership/scope check;
   - test akses ilegal.
9. Jangan menyimpan secret, password, API key, atau kredensial di repository.
10. Jangan menggunakan nilai dari request untuk menentukan path file, nama tabel, nama kolom, atau query mentah tanpa allowlist.

---

## 2. Sasaran Produk

Aplikasi harus mampu mengelola alur berikut:

```text
Versi Dokumen SPMI
  └── Standar
      └── Pernyataan Standar
          └── Indikator IKU/IKT
              ├── Target per Tahun Akademik
              ├── Penanggung Jawab
              ├── Lingkup Penerapan
              └── Kebutuhan Bukti
                  └── Instrumen/Pertanyaan AMI
                      └── Penugasan Audit
                          ├── Realisasi Auditee
                          ├── Bukti
                          └── Penilaian Auditor
                              ├── Skor
                              ├── Temuan
                              ├── Akar Masalah
                              └── Rekomendasi
                                  └── RTM
                                      └── Rencana Aksi
                                          ├── PIC
                                          ├── Target Waktu
                                          ├── Bukti Tindak Lanjut
                                          └── Verifikasi Efektivitas
```

### Prinsip domain

- **Standar** bukan pertanyaan.
- **Pernyataan standar** bukan indikator.
- **Indikator** bukan skor audit.
- **Target** bukan realisasi.
- **Realisasi auditee** bukan hasil verifikasi auditor.
- **Rekomendasi auditor** bukan keputusan RTM.
- **Rencana aksi** bukan bukti penyelesaian.
- **Selesai dikerjakan** belum tentu **efektif**.
- Riwayat audit harus tetap terikat pada versi standar yang berlaku saat audit dibuat.

---

## 3. Batasan dan Non-Goal Tahap Awal

### Termasuk dalam scope

- master dan versioning SPMI;
- organisasi dan penanggung jawab;
- indikator IKU/IKT;
- target tahunan;
- kebutuhan bukti;
- instrumen AMI;
- pelaksanaan audit;
- penilaian auditor;
- laporan hasil AMI;
- RTM;
- tindak lanjut;
- verifikasi;
- audit trail;
- hardening keamanan;
- migrasi data legacy.

### Tidak dilakukan sebelum ada keputusan bisnis

- mengubah skala skor menjadi `0–4`;
- menetapkan formula risk-based audit;
- menutup temuan otomatis hanya berdasarkan persentase progres;
- menghapus tabel legacy;
- mengekstrak semua nilai radar chart lama secara otomatis dari PDF;
- membuat tanda tangan elektronik tersertifikasi;
- integrasi SSO universitas;
- integrasi antivirus berbayar;
- integrasi sistem akademik eksternal tanpa kontrak API.

---

# 4. Milestone dan Dependensi

| Milestone | Nama | Dependensi |
|---|---|---|
| M0 | Discovery dan baseline | Tidak ada |
| M1 | Security foundation | M0 |
| M2 | Organisasi dan authorization | M0, M1 |
| M3 | Master SPMI dan versioning | M0, M1 |
| M4 | Indikator, target, PIC, bukti | M2, M3 |
| M5 | Import data SPMI | M3, M4 |
| M6 | Instrumen dan paket AMI | M3, M4 |
| M7 | Penugasan dan snapshot audit | M2, M6 |
| M8 | Pengisian auditee | M7 |
| M9 | Penilaian auditor dan temuan | M8 |
| M10 | Laporan hasil AMI | M9 |
| M11 | RTM | M9, M10 |
| M12 | Tindak lanjut dan verifikasi | M11 |
| M13 | Peningkatan standar/PPEPP | M10, M12 |
| M14 | Dashboard, ekspor, notifikasi | M10–M13 |
| M15 | Migrasi legacy dan konsolidasi | M7–M14 |
| M16 | Hardening akhir dan deployment | Semua milestone |

---

# 5. M0 — Discovery dan Baseline

## TASK M0-01 — Buat Peta Arsitektur Aktual

### Tujuan

Menghasilkan dokumen faktual mengenai struktur repo sebelum perubahan.

### Instruksi untuk Codex

1. Checkout branch kerja dari branch integrasi yang digunakan tim.
2. Catat commit SHA awal.
3. Petakan:
   - entry point aplikasi;
   - versi PHP;
   - versi CodeIgniter;
   - dependency Composer;
   - database driver;
   - struktur session;
   - konfigurasi CSRF;
   - konfigurasi cookie;
   - private storage;
   - route;
   - controller per role;
   - service;
   - model;
   - schema/migration;
   - test suite;
   - Docker/deployment.
4. Temukan semua implementasi yang memiliki nama atau tanggung jawab serupa, misalnya:
   - model tugas audit lama dan baru;
   - model jawaban lama dan baru;
   - controller auditor/auditee lama dan baru;
   - route alias/legacy.
5. Buat `docs/architecture/current-state.md`.
6. Sertakan diagram alur:
   - login;
   - penugasan;
   - pengisian auditee;
   - submit;
   - penilaian auditor;
   - laporan.
7. Jangan mengubah behavior aplikasi pada task ini.

### Acceptance criteria

- Dokumen menyebut nama file dan method aktual.
- Duplikasi dan dead code diberi label: `aktif`, `legacy`, `tidak pasti`.
- Setiap klaim memiliki referensi path.
- Tidak ada perubahan fungsional.

---

## TASK M0-02 — Buat Inventaris Database

### Tujuan

Menentukan schema aktual, constraint, index, dan kualitas data.

### Langkah agent

1. Ekspor struktur database tanpa data sensitif.
2. Bandingkan:
   - `database_schema.sql`;
   - file migration;
   - query pada model;
   - schema database runtime bila tersedia.
3. Catat:
   - tabel;
   - kolom;
   - tipe;
   - nullability;
   - foreign key;
   - unique key;
   - index;
   - enum;
   - orphan records potensial.
4. Buat `docs/database/current-schema.md`.
5. Buat query pemeriksaan data untuk:
   - tugas tanpa jawaban;
   - jawaban tanpa pertanyaan;
   - tugas tanpa periode;
   - user role invalid;
   - status tugas tidak konsisten;
   - `is_submitted` tidak seragam dalam satu tugas;
   - `is_nilai_submitted` tidak seragam;
   - file path yang menunjuk file hilang.

### Security

- Jangan menyalin data pribadi ke dokumentasi.
- Masking email, nama, URL bukti, dan path sensitif pada output contoh.

### Acceptance criteria

- Ada daftar constraint yang hilang.
- Ada daftar index yang dibutuhkan.
- Ada script read-only untuk pemeriksaan konsistensi.

---

## TASK M0-03 — Baseline Test dan Smoke Test

### Tujuan

Membekukan behavior lama agar perubahan dapat diregresi.

### Langkah agent

1. Dokumentasikan cara menjalankan test.
2. Tambahkan smoke test minimal untuk:
   - login valid;
   - login invalid;
   - akses dashboard per role;
   - pembuatan penugasan;
   - auditee menyimpan draft;
   - auditee submit;
   - auditor menilai;
   - ownership check;
   - laporan dapat dibuka.
3. Bila test framework belum memadai, buat test harness sementara yang konsisten dengan repo.
4. Simpan fixture tanpa data produksi.

### Acceptance criteria

- Test dapat dijalankan dengan satu perintah.
- Exit code gagal bila salah satu smoke test gagal.
- Test tidak bergantung pada internet.
- Test database terisolasi dari database production.

---

## TASK M0-04 — Decision Log dan ADR

### Tujuan

Menghindari perubahan arsitektur tanpa alasan tertulis.

### Langkah agent

Buat folder:

```text
docs/adr/
```

Minimal buat ADR:

- `0001-service-layer-boundary.md`
- `0002-spmi-versioning.md`
- `0003-audit-snapshot.md`
- `0004-private-file-storage.md`
- `0005-role-and-scope-authorization.md`
- `0006-legacy-migration-strategy.md`

### Acceptance criteria

Setiap ADR berisi:

- konteks;
- keputusan;
- alternatif;
- konsekuensi;
- risiko;
- status.

---

# 6. M1 — Security Foundation

## TASK M1-01 — Threat Modeling

### Tujuan

Mengidentifikasi ancaman utama sebelum menambah surface area.

### Asset yang harus dimodelkan

- akun pengguna;
- session;
- dokumen SPMI;
- bukti auditee;
- bukti auditor;
- notulen RTM;
- daftar hadir;
- data nilai;
- temuan;
- identitas auditor;
- keputusan RTM;
- audit log;
- backup database.

### Threat scenario minimum

- credential stuffing;
- session hijacking/fixation;
- CSRF;
- stored XSS pada jawaban, temuan, rekomendasi, dan catatan;
- IDOR pada tugas, bukti, laporan, dan RTM;
- SQL injection;
- mass assignment;
- upload file berbahaya;
- MIME spoofing;
- path traversal;
- SSRF melalui URL bukti;
- open redirect;
- CSV/Excel formula injection;
- privilege escalation;
- perubahan nilai setelah finalisasi;
- penghapusan bukti tanpa audit trail;
- kebocoran file private;
- log berisi password/token;
- race condition saat submit;
- replay request finalisasi;
- broken object-level authorization;
- backup tidak terenkripsi;
- migration destructive.

### Output

Buat:

```text
docs/security/threat-model.md
```

Gunakan tabel:

| Asset | Actor | Threat | Impact | Existing control | Required control | Test |
|---|---|---|---|---|---|---|

---

## TASK M1-02 — Security Configuration Audit

### Periksa

- `ENVIRONMENT`;
- display error;
- logging;
- encryption key;
- session driver;
- session expiration;
- session regeneration;
- cookie `Secure`;
- cookie `HttpOnly`;
- cookie `SameSite`;
- CSRF;
- allowed hosts/base URL;
- proxy headers;
- upload limits;
- database credentials;
- private storage path;
- file permissions;
- production debug;
- default account.

### Perbaikan wajib

1. Production harus fail-closed bila:
   - encryption key kosong;
   - database password default;
   - private storage tidak writable;
   - debug aktif;
   - base URL tidak valid.
2. Secret hanya berasal dari environment.
3. Tambahkan `.env.example` tanpa secret.
4. Dokumentasikan konfigurasi production.

### Acceptance criteria

- Startup production gagal dengan pesan aman bila secret wajib tidak tersedia.
- Error page production tidak menampilkan stack trace/path.
- Log tetap menyimpan correlation ID dan pesan teknis yang aman.

---

## TASK M1-03 — Session dan Authentication Hardening

### Langkah agent

1. Regenerasi session ID setelah login.
2. Hapus session sepenuhnya saat logout.
3. Terapkan idle timeout dan absolute timeout.
4. Tambahkan login throttling:
   - per username/email;
   - per IP;
   - exponential backoff atau lock sementara.
5. Gunakan password hashing API bawaan PHP.
6. Jangan membedakan pesan “akun tidak ditemukan” dan “password salah”.
7. Catat login sukses/gagal pada security audit log tanpa menyimpan password.
8. Tambahkan proteksi terhadap session fixation.
9. Pastikan perubahan password mencabut session lain bila desain mendukung.
10. Pastikan akun nonaktif tidak dapat login.

### Test

- session ID berubah setelah login;
- cookie aman di production;
- brute-force dibatasi;
- logout menginvalidasi session;
- akun nonaktif ditolak.

---

## TASK M1-04 — Central Authorization Guard

### Tujuan

Menghindari pengecekan role dan ownership yang tersebar dan tidak konsisten.

### Implementasi

Buat service/helper/policy yang menyediakan operasi seperti:

```php
canViewAuditAssignment($userId, $assignmentId)
canEditAuditeeSubmission($userId, $assignmentId)
canAssessAssignment($userId, $assignmentId)
canViewEvidence($userId, $evidenceId)
canManageSpmiVersion($userId)
canManageRtm($userId, $rtmId)
canSubmitFollowUp($userId, $rtmItemId)
canVerifyFollowUp($userId, $rtmItemId)
```

### Aturan

- Controller tidak boleh percaya ID dari URL.
- Query object harus sekaligus membatasi scope user bila memungkinkan.
- Role check saja tidak cukup; harus ada object-level authorization.
- Super admin override harus eksplisit dan tercatat.

### Test negatif wajib

- auditee A mengakses tugas auditee B;
- auditor A menilai tugas auditor B;
- user menebak ID bukti;
- PIC mengubah tindak lanjut milik PIC lain;
- auditor mengubah skor setelah final;
- admin non-LPMPI memfinalisasi RTM.

---

## TASK M1-05 — Output Encoding dan XSS Protection

### Area rawan

- nama standar;
- isi pernyataan;
- pertanyaan;
- jawaban;
- temuan;
- akar masalah;
- rekomendasi;
- keputusan RTM;
- catatan progres;
- nama file;
- URL bukti.

### Langkah

1. Buat helper output encoding konsisten.
2. Default semua output menjadi escaped.
3. HTML rich text hanya diizinkan bila benar-benar dibutuhkan dan disanitasi menggunakan allowlist.
4. Jangan menampilkan nama file asli tanpa escaping.
5. Pastikan export PDF/Excel juga memperlakukan input sebagai data, bukan markup/formula.

### Test

Masukkan payload XSS ke setiap field text dan pastikan tidak dieksekusi.

---

## TASK M1-06 — File Security Foundation

### Kebijakan file

- semua bukti, instrumen, notulen, dan daftar hadir disimpan di luar document root;
- akses hanya melalui download controller yang terotorisasi;
- nama file storage menggunakan UUID/random name;
- nama asli hanya metadata;
- allowlist extension;
- validasi MIME dari isi file, bukan header browser;
- batas ukuran per kategori;
- larang file executable;
- set `Content-Disposition: attachment`;
- set `X-Content-Type-Options: nosniff`;
- log upload dan download sensitif;
- cegah path traversal;
- checksum SHA-256;
- soft-delete/retention, bukan unlink langsung pada flow normal.

### Extension awal yang disarankan

- PDF;
- DOCX;
- XLSX;
- PNG;
- JPG/JPEG.

Jangan izinkan:

- PHP;
- PHAR;
- PHTML;
- executable;
- script;
- HTML;
- SVG kecuali ada sanitasi khusus;
- archive tanpa kebutuhan bisnis.

### Optional hardening

- integrasi antivirus;
- quarantine state;
- asynchronous scanning.

---

## TASK M1-07 — Security Headers

Tambahkan dan uji:

- `Content-Security-Policy`;
- `X-Frame-Options` atau `frame-ancestors`;
- `X-Content-Type-Options`;
- `Referrer-Policy`;
- `Permissions-Policy`;
- HSTS hanya pada HTTPS production;
- cache-control untuk halaman dan download sensitif.

CSP harus diuji terhadap Bootstrap, JS lokal, dan library chart yang dipakai. Hindari `unsafe-inline` bila memungkinkan.

---

## TASK M1-08 — Immutable Audit Log

### Tabel

Buat tabel append-only:

```text
security_audit_logs
- id
- actor_user_id
- event_type
- object_type
- object_id
- action
- before_hash atau ringkasan perubahan
- after_hash atau ringkasan perubahan
- ip_address
- user_agent
- request_id
- created_at
```

### Event minimum

- login sukses/gagal;
- logout;
- perubahan role;
- pembuatan/aktivasi versi standar;
- perubahan indikator;
- pembuatan penugasan;
- submit auditee;
- reopen/revisi;
- submit auditor;
- finalisasi laporan;
- finalisasi RTM;
- perubahan PIC/target;
- upload/delete bukti;
- verifikasi tindak lanjut;
- export data sensitif.

### Security

- UI tidak menyediakan edit/delete log.
- Jangan simpan body request utuh.
- Redact token, password, cookie, dan data sensitif.

---

# 7. M2 — Organisasi, Role, dan Scope

## TASK M2-01 — Master Unit Organisasi

### Tujuan

Mendukung audit tingkat universitas, fakultas/UPPS, program studi, lembaga, biro, dan unit.

### Tabel yang disarankan

```text
organization_units
- id
- parent_id
- code
- name
- type: university|faculty|study_program|institute|bureau|unit
- active
- metadata_json (opsional, dibatasi)
```

### Acceptance criteria

- hierarchy dapat direpresentasikan;
- program studi terhubung ke fakultas;
- unit dapat dinonaktifkan tanpa menghapus histori;
- code unik;
- ada seed universitas root.

---

## TASK M2-02 — Keanggotaan User dan Jabatan

### Tabel

```text
user_unit_assignments
- id
- user_id
- organization_unit_id
- position_code
- valid_from
- valid_until
- is_primary
```

### Aturan

- satu user dapat memiliki beberapa unit/jabatan;
- assignment lama tidak dihapus, tetapi diberi masa berlaku;
- otorisasi memakai assignment yang aktif.

---

## TASK M2-03 — Role Capability Matrix

### Tujuan

Memisahkan role global dari tanggung jawab organisasi.

### Capability minimum

```text
spmi.version.manage
spmi.standard.manage
spmi.indicator.manage
spmi.import
audit.period.manage
audit.package.manage
audit.assignment.manage
audit.submission.fill
audit.submission.submit
audit.assessment.fill
audit.assessment.submit
audit.report.view
audit.report.export
rtm.manage
rtm.finalize
followup.fill
followup.verify
security.auditlog.view
```

### Acceptance criteria

- matrix terdokumentasi;
- setiap controller memakai capability;
- capability dapat dikombinasikan dengan unit scope;
- regression test seluruh role.

---

# 8. M3 — Master SPMI dan Versioning

## TASK M3-01 — Tabel Versi Dokumen SPMI

### Schema minimum

```text
spmi_versions
- id
- document_code
- title
- revision_number
- effective_date
- expires_at nullable
- source_file_path
- source_file_sha256
- status: draft|review|approved|active|retired
- created_by
- approved_by nullable
- approved_at nullable
- created_at
- updated_at
```

### Business rule

- hanya satu versi `active` untuk tanggal/lingkup yang sama, kecuali bisnis menyetujui coexistence;
- versi active tidak boleh diedit langsung;
- revisi dibuat sebagai versi baru;
- source PDF disimpan private;
- checksum diverifikasi.

---

## TASK M3-02 — Workflow Persetujuan Versi

Status:

```text
draft → review → approved → active → retired
```

### Aturan

- pembuat tidak boleh menjadi satu-satunya approver bila separation of duties diterapkan;
- activation dicatat;
- versi aktif menjadi read-only;
- rollback activation tidak menghapus histori.

### Test

- user tanpa capability tidak dapat approve;
- draft dapat diedit;
- active tidak dapat diedit;
- clone active ke draft baru bekerja.

---

## TASK M3-03 — Master 21 Standar

### Schema

```text
spmi_standards
- id
- spmi_version_id
- code
- name
- group_type
- standard_type: sn_dikti|internal
- rationale
- definitions
- sort_order
- active
```

### Seed/import awal

Masukkan struktur 21 standar:

- pendidikan: 8;
- penelitian: 3;
- pengabdian: 3;
- standar tambahan universitas: 7.

### Acceptance criteria

- tidak hardcode di view;
- urutan dapat diatur;
- code unik per versi;
- versi berbeda boleh memiliki code sama.

---

## TASK M3-04 — Pernyataan Isi Standar

### Schema

```text
spmi_statements
- id
- standard_id
- code
- statement_text
- sort_order
- active
```

### Aturan

- satu standar memiliki banyak pernyataan;
- kode tidak wajib secara global, tetapi unik dalam standar bila tersedia;
- pernyataan active pada versi active immutable.

---

## TASK M3-05 — Strategi, Referensi, dan Dokumen Terkait

Buat struktur terpisah:

```text
spmi_strategies
spmi_references
spmi_related_document_types
```

Hindari satu kolom JSON besar untuk seluruh data bila data akan dicari, difilter, atau diaudit.

### Acceptance criteria

- strategi dapat diurutkan;
- referensi dapat menyimpan judul dan nomor;
- dokumen terkait dapat ditautkan ke kebutuhan bukti.

---

# 9. M4 — Indikator, Target, PIC, dan Kebutuhan Bukti

## TASK M4-01 — Master Indikator IKU/IKT

### Schema

```text
spmi_indicators
- id
- statement_id
- code
- name
- category: IKU|IKT
- measurement_type
- unit
- comparator
- frequency
- calculation_rule nullable
- evidence_summary
- sort_order
- active
```

### Measurement type

```text
boolean
percentage
integer
decimal
ratio
currency
duration
frequency
text
composite
```

### Security

`calculation_rule` tidak boleh dieksekusi sebagai kode PHP atau SQL. Gunakan rule engine berbasis allowlist atau konfigurasi terstruktur.

---

## TASK M4-02 — Target Tahunan dan Baseline

### Schema

```text
spmi_indicator_targets
- id
- indicator_id
- academic_year
- is_baseline
- target_numeric
- target_text
- numerator_target nullable
- denominator_target nullable
- comparator
- notes
```

### Rule

- satu target per indikator per tahun;
- histori target tidak ditimpa;
- target versi aktif read-only;
- perubahan target memerlukan versi/revisi atau mekanisme approval.

---

## TASK M4-03 — Lingkup Penerapan Indikator

### Schema

```text
spmi_indicator_scopes
- indicator_id
- organization_unit_type
- organization_unit_id nullable
- required
```

### Contoh

- CPL → program studi;
- pembiayaan → universitas/unit keuangan;
- tata pamong → universitas/fakultas;
- penelitian → LPPMPP dan program studi.

### Acceptance criteria

Penugasan audit hanya menawarkan indikator yang sesuai dengan scope auditee.

---

## TASK M4-04 — Penanggung Jawab Indikator

### Schema

```text
spmi_indicator_responsibles
- id
- indicator_id
- organization_unit_id nullable
- position_code nullable
- user_id nullable
- responsibility_type: owner|contributor|reviewer
- valid_from
- valid_until
```

### Rule

- utamakan jabatan/unit daripada user permanen;
- user aktual diturunkan dari assignment jabatan;
- perubahan pejabat tidak mengubah histori audit.

---

## TASK M4-05 — Kebutuhan Bukti

### Schema

```text
spmi_evidence_requirements
- id
- indicator_id
- code
- name
- description
- evidence_type: file|url|data|file_or_url
- required
- allowed_extensions
- max_size_bytes
- validity_months nullable
- sort_order
```

### Security

- konfigurasi extension server-side;
- jangan percaya extension dari UI;
- URL eksternal hanya disimpan dan divalidasi; server tidak otomatis fetch URL;
- bila fitur preview URL dibuat, wajib mitigasi SSRF.

---

## TASK M4-06 — UI Master SPMI

Buat halaman:

```text
LPMPI > SPMI > Versi
LPMPI > SPMI > Standar
LPMPI > SPMI > Pernyataan
LPMPI > SPMI > Indikator
LPMPI > SPMI > Target
LPMPI > SPMI > Penanggung Jawab
LPMPI > SPMI > Kebutuhan Bukti
```

### UX

- breadcrumb;
- filter versi/kelompok/IKU-IKT;
- pagination;
- empty state;
- validation error per field;
- confirmation untuk aksi irreversible;
- read-only badge untuk versi aktif.

---

# 10. M5 — Import Data SPMI

## TASK M5-01 — Definisikan Template Import

Gunakan XLSX, bukan import PDF otomatis.

Sheet minimum:

1. `Standards`
2. `Statements`
3. `Indicators`
4. `Targets`
5. `EvidenceRequirements`
6. `Scopes`
7. `Responsibles`

### Security

- batasi ukuran file;
- private temp directory;
- random file name;
- hapus temp file setelah proses;
- cegah zip bomb;
- batasi jumlah row;
- jangan evaluasi formula;
- treat cell sebagai value;
- log import.

---

## TASK M5-02 — Dry-Run Import

### Flow

```text
Upload → Parse → Validate → Preview → Confirm → Commit transaction
```

### Validation

- referensi code valid;
- duplicate code;
- tahun akademik valid;
- category IKU/IKT;
- measurement type valid;
- comparator kompatibel;
- numeric target valid;
- scope valid;
- extension bukti valid.

### Acceptance criteria

- dry-run tidak menulis database;
- error menunjukkan sheet dan row;
- confirm memakai token import sekali pakai;
- commit all-or-nothing;
- file temp kadaluarsa otomatis.

---

## TASK M5-03 — Export Master SPMI

Export untuk backup operasional dan review.

### Security

- prefix cell berbahaya yang dimulai `=`, `+`, `-`, `@`;
- hanya user berizin;
- export dicatat;
- jangan sertakan path internal storage.

---

# 11. M6 — Instrumen dan Paket AMI

## TASK M6-01 — Paket Instrumen

### Schema

```text
audit_instrument_packages
- id
- spmi_version_id
- name
- code
- applicable_scope_type
- status: draft|active|retired
- created_by
- approved_by
```

Paket dapat berbeda untuk:

- pendidikan;
- teknik;
- informatika;
- ekonomi;
- standar internal;
- universitas;
- fakultas;
- program studi.

---

## TASK M6-02 — Mapping Pertanyaan ke Indikator

Perluas atau buat tabel pertanyaan:

```text
audit_questions
- id
- instrument_package_id
- indicator_id
- code
- question_text
- guidance
- required
- sort_order
```

### Rule

- satu indikator dapat memiliki beberapa pertanyaan;
- satu pertanyaan memiliki satu basis indikator utama;
- audit report dapat ditelusuri ke versi standar.

---

## TASK M6-03 — Rubrik Penilaian

### Schema

```text
audit_scoring_rubrics
- id
- question_id atau measurement_type
- score
- label
- criteria
```

### Keputusan bisnis yang harus dikonfirmasi

- rentang `1–4` atau `0–4`;
- arti setiap skor;
- perlakuan N/A;
- formula agregasi;
- bobot;
- pembulatan;
- formula risk-based audit.

### Guard

Jangan hardcode skor sebelum stakeholder menyetujui rubrik.

---

## TASK M6-04 — Approval Paket Instrumen

Status:

```text
draft → reviewed → active → retired
```

Paket aktif tidak diedit; buat revisi/clone.

---

# 12. M7 — Penugasan dan Snapshot Audit

## TASK M7-01 — Periode/Siklus Audit

Periode harus menyimpan:

```text
audit_cycles
- id
- name
- academic_year
- start_date
- end_date
- spmi_version_id
- status: draft|open|assessment|closed|archived
```

### Rule

- versi SPMI dipilih saat cycle dibuat;
- cycle closed read-only;
- date range tervalidasi.

---

## TASK M7-02 — Objek Audit dan Assignment

### Schema konseptual

```text
audit_assignments
- id
- audit_cycle_id
- organization_unit_id
- instrument_package_id
- lead_auditor_id
- status
- due_date_submission
- due_date_assessment
- created_by
```

Auditor tambahan:

```text
audit_assignment_auditors
- assignment_id
- auditor_user_id
- role: lead|member|observer
```

### Constraint

- auditor tidak boleh menjadi auditee/owner utama pada unit yang sama bila conflict-of-interest rule diterapkan;
- duplicate assignment dicegah;
- auditor dan auditee harus aktif.

---

## TASK M7-03 — Snapshot Instrumen

### Tujuan

Audit historis tidak berubah ketika master standar direvisi.

Saat assignment dibuat, snapshot:

- versi standar;
- standard code/name;
- statement;
- indicator;
- target;
- question;
- rubrik;
- evidence requirements;
- responsible unit.

### Implementasi

Gunakan tabel snapshot terstruktur atau immutable audit item rows. Jangan hanya menyimpan foreign key ke master aktif.

### Acceptance criteria

- mengubah draft versi baru tidak mengubah assignment lama;
- report lama tetap identik;
- checksum snapshot tersedia bila dibutuhkan.

---

## TASK M7-04 — State Machine Assignment

Definisikan state resmi:

```text
draft
assigned
auditee_in_progress
auditee_submitted
returned_to_auditee
auditor_in_progress
auditor_submitted
finalized
closed
cancelled
```

### Rule

- transition hanya lewat service;
- model tidak mengubah status secara ad hoc;
- transition invalid ditolak;
- setiap transition dicatat;
- finalization idempotent.

---

# 13. M8 — Pengisian Auditee

## TASK M8-01 — Realisasi Indikator

### Data per audit item

```text
audit_submissions
- audit_item_id
- realization_numeric
- realization_text
- numerator nullable
- denominator nullable
- self_assessment_status
- explanation
- obstacle
- initial_improvement_plan
- updated_by
- updated_at
```

### Validation

- sesuai measurement type;
- denominator tidak nol;
- persentase range valid;
- currency non-negatif;
- ratio valid;
- text length dibatasi.

---

## TASK M8-02 — Multiple Evidence

### Schema

```text
audit_evidences
- id
- audit_item_id
- requirement_id nullable
- original_name
- stored_name
- mime_type
- extension
- size
- sha256
- storage_path
- external_url nullable
- document_date
- valid_until nullable
- description
- uploaded_by
- status
- created_at
```

### Security test

- upload PHP disguised as PDF;
- double extension;
- filename traversal;
- oversized upload;
- MIME mismatch;
- malicious office macro;
- unauthorized download;
- deleted assignment;
- URL dengan `javascript:` atau non-HTTP scheme.

---

## TASK M8-03 — Draft Autosave Aman

Bila autosave digunakan:

- CSRF tetap wajib;
- debounce;
- optimistic locking/version field;
- response tidak mengembalikan data sensitif berlebih;
- conflict UI saat data berubah di tab lain.

Tanpa autosave, pastikan save draft atomik dan jelas.

---

## TASK M8-04 — Pre-Submit Validation

Sebelum submit:

- semua pertanyaan wajib terisi;
- semua kebutuhan bukti wajib terpenuhi;
- file selesai diproses;
- URL valid;
- target dan realisasi kompatibel;
- tidak ada conflict version;
- deadline diperiksa sesuai kebijakan.

Tampilkan daftar error per indikator.

---

## TASK M8-05 — Submit Auditee

### Rule

- transaction;
- lock assignment row;
- idempotency token;
- snapshot submitted data;
- set timestamp dan actor;
- setelah submit read-only;
- reopen hanya melalui workflow resmi;
- audit log.

---

# 14. M9 — Penilaian Auditor dan Temuan

## TASK M9-01 — Workspace Auditor

Auditor harus melihat:

- standar;
- pernyataan;
- indikator;
- target;
- realisasi;
- perhitungan capaian;
- semua bukti;
- histori revisi;
- rubrik;
- conflict/flag.

---

## TASK M9-02 — Verifikasi Bukti

Status bukti:

```text
pending
valid
invalid
revision_required
not_applicable
```

Auditor mengisi catatan bila invalid/revision required.

---

## TASK M9-03 — Assessment

### Field

```text
audit_assessments
- audit_item_id
- achievement_status
- score nullable
- assessment_note
- evidence_conclusion
- assessed_by
- assessed_at
```

Achievement status:

```text
achieved
partially_achieved
not_achieved
not_applicable
not_assessable
```

### Rule

- `not_applicable` memerlukan alasan;
- score sesuai rubric;
- assessor harus assigned;
- lead auditor finalizes.

---

## TASK M9-04 — Temuan

### Schema

```text
audit_findings
- id
- audit_item_id
- finding_code
- finding_type
- severity
- finding_text
- root_cause_preliminary
- recommendation
- status
- created_by
- finalized_at
```

### Finding type

Konfirmasi istilah bisnis, misalnya:

- OB;
- KTS minor;
- KTS major;
- opportunity for improvement;
- positive practice.

Jangan menyimpulkan arti OB/KTS tanpa dokumen resmi.

---

## TASK M9-05 — Return for Revision

### Rule

- auditor memilih indikator yang perlu revisi;
- alasan wajib;
- hanya item terpilih yang dapat diedit bila workflow mendukung;
- jawaban lama tetap tersimpan sebagai revision history;
- revisi tidak menghapus bukti lama;
- resubmit tercatat.

---

## TASK M9-06 — Submit dan Finalisasi Auditor

Pisahkan:

```text
submit assessment
finalize audit
```

### Finalize rule

- seluruh item dinilai;
- semua temuan lengkap;
- lead auditor melakukan finalisasi;
- transaction dan row lock;
- setelah final read-only;
- koreksi setelah final memerlukan amendment resmi.

---

## TASK M9-07 — Amendment

Buat mekanisme koreksi terkontrol:

```text
audit_amendments
- audit_assignment_id
- reason
- requested_by
- approved_by
- affected_items
- created_at
```

Jangan membuka data final secara diam-diam.

---

# 15. M10 — Laporan Hasil AMI

## TASK M10-01 — Tabel Hasil AMI

Format minimal:

| Kriteria/Indikator | Realisasi dan Bukti | Temuan | Akar Masalah | Rekomendasi | Skor |
|---|---|---|---|---|---|

### Filter

- periode;
- unit/program studi;
- standar;
- indikator;
- IKU/IKT;
- auditor;
- skor;
- jenis temuan;
- status ketercapaian.

---

## TASK M10-02 — Radar Chart

### Rule

- chart memakai data numerik yang tersimpan, bukan ekstraksi gambar;
- label panjang ditangani;
- jumlah indikator besar dapat dikelompokkan per standar;
- tampilkan tabel alternatif untuk aksesibilitas;
- skala tidak auto-menyesatkan;
- skala dan formula tercantum.

### Security

Escape label indikator sebelum masuk data chart.

---

## TASK M10-03 — Export PDF

### Requirements

- identitas periode dan unit;
- versi SPMI;
- daftar auditor;
- tanggal finalisasi;
- tabel hasil;
- chart;
- nomor halaman;
- checksum/report identifier opsional.

### Security

- generate server-side dari data terotorisasi;
- temp file private;
- hapus temp;
- jangan menerima HTML arbitrary;
- escape semua user text;
- tidak menampilkan internal path.

---

## TASK M10-04 — Export XLSX

### Security

- cegah formula injection;
- format angka sesuai measurement;
- freeze header;
- metadata export;
- authorization dan audit log.

---

## TASK M10-05 — Finalisasi Laporan

Laporan final memiliki:

- unique report number;
- finalization timestamp;
- generated by;
- data version/hash;
- immutable snapshot.

Regenerate laporan final harus menghasilkan revisi atau salinan teridentifikasi, bukan mengganti diam-diam.

---

# 16. M11 — RTM

## TASK M11-01 — Master Rapat RTM

### Schema

```text
rtm_meetings
- id
- audit_cycle_id
- meeting_number
- title
- meeting_date
- start_time
- end_time
- location
- agenda
- chair_user_id
- secretary_user_id
- status: draft|final
- minutes_file_id nullable
- attendance_file_id nullable
- general_notes
- finalized_at
```

### Security

- file private;
- hanya role/capability tertentu;
- final read-only;
- download terotorisasi.

---

## TASK M11-02 — Peserta RTM

```text
rtm_participants
- rtm_meeting_id
- user_id nullable
- participant_name_snapshot
- position_snapshot
- organization_snapshot
- attendance_status
- role_in_meeting
```

Snapshot diperlukan agar histori tidak berubah ketika jabatan user berubah.

---

## TASK M11-03 — Pilih Temuan untuk RTM

### Flow

- filter temuan final;
- pilih satu atau banyak;
- cegah duplikat dalam rapat;
- tampilkan sumber temuan;
- izinkan penggabungan hanya dengan relasi yang jelas.

Bila beberapa temuan digabung, simpan junction table, bukan hanya satu `finding_id`.

---

## TASK M11-04 — Item Keputusan RTM

### Schema

```text
rtm_items
- id
- rtm_meeting_id
- title
- agreed_root_cause
- auditor_recommendation_snapshot
- management_decision
- action_plan
- priority
- target_date
- status
```

Relasi:

```text
rtm_item_findings
- rtm_item_id
- audit_finding_id
```

---

## TASK M11-05 — Penetapan PIC

PIC dapat berupa:

- user;
- jabatan;
- unit.

Simpan snapshot tanggung jawab saat finalisasi.

### Rule

- target date wajib;
- PIC aktif;
- conflict dicegah;
- PIC menerima akses hanya pada item terkait.

---

## TASK M11-06 — Finalisasi RTM

### Validasi

- semua item punya keputusan;
- semua item punya action plan;
- PIC;
- target;
- priority;
- notulen/daftar hadir sesuai kebijakan;
- meeting metadata lengkap.

### Security

- finalisasi dengan re-authentication opsional untuk aksi berisiko tinggi;
- idempotent;
- audit log;
- immutable.

---

# 17. M12 — Tindak Lanjut dan Verifikasi

## TASK M12-01 — Workspace PIC/Auditee

PIC melihat:

- sumber temuan;
- keputusan RTM;
- action plan;
- target;
- status;
- histori progres;
- bukti.

---

## TASK M12-02 — Catatan Progres

### Schema

```text
followup_updates
- id
- rtm_item_id
- progress_percent
- note
- created_by
- created_at
```

### Rule

- progress 0–100;
- append-only;
- koreksi dibuat sebagai entry baru;
- status item diturunkan melalui service.

---

## TASK M12-03 — Bukti Tindak Lanjut

Gunakan security control file yang sama dengan bukti audit.

Tambahkan kategori:

- implementation evidence;
- monitoring evidence;
- effectiveness evidence.

---

## TASK M12-04 — Request Verification

PIC mengirim item ke verifier.

State:

```text
assigned
in_progress
verification_requested
revision_required
verified_effective
verified_ineffective
closed
```

### Rule

- progress 100 bukan otomatis selesai;
- bukti minimum terpenuhi;
- request idempotent.

---

## TASK M12-05 — Verification

Verifier mengisi:

- evidence validity;
- implementation status;
- effectiveness conclusion;
- verification note;
- follow-up recommendation;
- next review date bila perlu.

### Separation of duties

Sebisa mungkin PIC tidak memverifikasi pekerjaannya sendiri.

---

## TASK M12-06 — Overdue dan Escalation

Buat query/status untuk:

- due soon;
- overdue;
- verification pending;
- repeatedly rejected.

Jangan mengirim detail sensitif lewat email; email cukup memberi notifikasi dan link login.

---

# 18. M13 — PPEPP dan Peningkatan

## TASK M13-01 — Mapping PPEPP

Setiap indikator harus dapat menampilkan:

- Penetapan: versi/target;
- Pelaksanaan: realisasi/bukti;
- Evaluasi: assessment/temuan;
- Pengendalian: RTM/action;
- Peningkatan: target/revisi baru.

Buat view/report traceability.

---

## TASK M13-02 — Usulan Peningkatan Standar

### Schema

```text
spmi_improvement_proposals
- id
- source_type
- source_id
- current_version_id
- proposed_change
- rationale
- proposed_by
- status
- reviewed_by
```

Source dapat berasal dari:

- temuan berulang;
- RTM;
- tren tidak tercapai;
- perubahan regulasi;
- benchmarking.

---

## TASK M13-03 — Clone Versi Standar

Fitur clone:

- standards;
- statements;
- indicators;
- targets;
- scope;
- responsibles;
- evidence requirements.

Semua row mendapat ID baru. Jangan mengubah versi lama.

---

## TASK M13-04 — Comparative Review

Tampilkan diff antarversi:

- standard text;
- statement;
- indicator;
- target;
- scope;
- evidence.

Escape semua konten diff.

---

# 19. M14 — Dashboard, Notifikasi, dan Ekspor

## TASK M14-01 — Dashboard LPMPI

Metric:

- assignment per status;
- submission overdue;
- assessment overdue;
- capaian per standar;
- IKU vs IKT;
- temuan per severity;
- temuan berulang;
- RTM belum final;
- tindak lanjut overdue;
- verification pending;
- tren antarperiode.

Semua metric harus memiliki definisi dan query test.

---

## TASK M14-02 — Dashboard Auditee/PIC

Tampilkan hanya scope user:

- tugas aktif;
- deadline;
- indikator belum lengkap;
- bukti invalid;
- revisi;
- tindak lanjut;
- overdue.

Test object-level authorization.

---

## TASK M14-03 — Dashboard Auditor

- tugas assigned;
- menunggu penilaian;
- revision resubmitted;
- assessment due;
- finalization pending.

---

## TASK M14-04 — Notification Service

Event:

- assignment dibuat;
- deadline mendekat;
- auditee submit;
- return revision;
- auditor submit;
- RTM final;
- PIC assigned;
- verification requested;
- rejected;
- overdue.

### Security

- queue/retry bila tersedia;
- no secrets in email;
- sanitize subject/body;
- user preference;
- anti-spam/deduplication;
- log delivery tanpa body sensitif.

---

# 20. M15 — Migrasi Legacy dan Konsolidasi

## TASK M15-01 — Mapping Legacy

Buat `docs/migration/legacy-mapping.md`.

Mapping minimum:

```text
standar              → spmi_standards
pertanyaan           → audit_questions / indicators
periode_audit        → audit_cycles
tugas_audit          → audit_assignments
jawaban_audit        → submissions + assessments + findings
penetapan             → tetap legacy atau dimapping ke PPEPP sesuai data
```

Setiap kolom harus diberi status:

- direct;
- transformed;
- ambiguous;
- obsolete;
- requires manual review.

---

## TASK M15-02 — Read-Only Migration Report

Sebelum migrasi:

- jumlah row sumber;
- row valid;
- row orphan;
- row duplicate;
- mapping confidence;
- file missing;
- user missing;
- status inconsistent.

Tidak ada write pada task ini.

---

## TASK M15-03 — Backfill Organisasi

Hubungkan user/auditee legacy ke unit organisasi.

Ambiguous mapping harus masuk queue manual, bukan ditebak.

---

## TASK M15-04 — Backfill Audit Snapshot

Untuk audit lama:

- simpan text snapshot dari standar/pertanyaan lama;
- jangan menghubungkan paksa ke indikator 2025 bila tidak ekuivalen;
- tandai `legacy_source`;
- pertahankan skor dan timestamp.

---

## TASK M15-05 — Dual Read atau Compatibility Layer

Bila cutover bertahap:

- endpoint laporan dapat membaca legacy dan new schema melalui service;
- UI memberi label legacy;
- jangan menduplikasi business rule di controller.

---

## TASK M15-06 — Deprecation

Setelah:

- data reconciliation 100%;
- regression test;
- stakeholder sign-off;
- backup;
- rollback drill;

baru:

- nonaktifkan route legacy;
- jadikan tabel legacy read-only;
- hapus kode pada release terpisah;
- jangan drop tabel pada release yang sama.

---

# 21. M16 — Hardening Akhir dan Deployment

## TASK M16-01 — Test Matrix Lengkap

### Unit test

- comparator;
- calculation;
- state transition;
- score validation;
- target matching;
- authorization policy;
- filename sanitizer;
- formula injection sanitizer.

### Integration test

- create version;
- activate version;
- create assignment snapshot;
- auditee submit;
- auditor assess;
- finalize;
- RTM;
- follow-up;
- verification;
- report.

### Security test

- CSRF;
- XSS;
- IDOR;
- upload bypass;
- path traversal;
- SQL injection;
- mass assignment;
- authorization bypass;
- session fixation;
- rate limit;
- formula injection.

---

## TASK M16-02 — Concurrency dan Transaction Test

Skenario:

- dua tab submit auditee;
- auditor dan auditee update bersamaan;
- dua admin finalisasi RTM;
- dua verifier memverifikasi item;
- retry request setelah timeout.

Gunakan row lock/optimistic lock dan idempotency.

---

## TASK M16-03 — Performance

Test:

- indikator ribuan;
- bukti banyak;
- report besar;
- export;
- dashboard aggregate;
- pagination;
- N+1 query.

Tambahkan index berdasarkan query nyata, bukan tebakan.

---

## TASK M16-04 — Dependency and SAST Gate

CI minimum:

- Composer install locked;
- dependency vulnerability check;
- syntax/lint;
- unit test;
- integration test;
- static analysis bila kompatibel;
- secret scanning;
- migration validation.

Jangan menjalankan pipeline dengan credential production.

---

## TASK M16-05 — Backup dan Restore Drill

Sebelum rollout:

- full DB backup;
- private file backup;
- checksum;
- restore ke environment terisolasi;
- ukur RTO/RPO;
- dokumentasikan rollback.

Backup harus dilindungi dan aksesnya dibatasi.

---

## TASK M16-06 — Deployment Checklist

### Sebelum deploy

- migration reviewed;
- backup valid;
- maintenance plan;
- feature flag;
- config production;
- HTTPS;
- secure cookie;
- storage permission;
- queue/cron;
- disk capacity;
- log rotation;
- monitoring;
- rollback command.

### Sesudah deploy

- health check;
- login smoke test;
- permission test;
- upload/download test;
- submit/finalize test;
- report test;
- error/log review;
- audit log review.

---

# 22. Struktur Kode yang Diinginkan

Agent harus menyesuaikan dengan struktur aktual repo, tetapi pertahankan batas tanggung jawab berikut:

```text
Controller
  → validasi bentuk request dasar
  → panggil service
  → render/redirect

Service
  → business rule
  → authorization orchestration
  → state transition
  → transaction
  → audit event

Model/Repository
  → query database
  → persistence
  → tidak menentukan workflow lintas-entitas

Policy/Guard
  → capability
  → ownership
  → organization scope

Storage Service
  → file validation
  → private storage
  → download authorization support

Report Service
  → query read model
  → export
```

### Larangan

- jangan menambah business rule baru ke model yang sudah terlalu berat;
- jangan melakukan query raw di view;
- jangan mengecek role hanya dengan menyembunyikan menu;
- jangan melakukan state transition langsung dari controller;
- jangan menggunakan `$_POST`/`$_FILES` tanpa wrapper dan validasi;
- jangan menyimpan file berdasarkan nama dari user;
- jangan membuat URL download langsung ke storage path.

---

# 23. Konvensi Migration

Gunakan nomor/timestamp yang konsisten dengan repo.

Setiap migration harus menyertakan:

```text
Purpose
Precondition
Up
Data backfill
Verification query
Rollback
Known risk
Estimated lock time
```

Untuk tabel besar:

- add nullable column dahulu;
- backfill batch;
- validate;
- add constraint;
- switch read/write;
- baru enforce not null.

---

# 24. Definition of Done untuk Setiap Task

Task dianggap selesai bila:

- [ ] code mengikuti style repo;
- [ ] tidak ada secret;
- [ ] migration tersedia bila perlu;
- [ ] rollback terdokumentasi;
- [ ] server-side validation;
- [ ] authorization dan ownership check;
- [ ] CSRF untuk mutation;
- [ ] output escaping;
- [ ] audit log untuk aksi sensitif;
- [ ] unit/integration test;
- [ ] negative security test;
- [ ] dokumentasi route/schema diperbarui;
- [ ] tidak merusak smoke test lama;
- [ ] commit kecil dan deskriptif;
- [ ] acceptance criteria dapat dibuktikan.

---

# 25. Template Prompt untuk Menjalankan Codex per Task

Gunakan prompt berikut dan ganti bagian dalam kurung:

```text
Kerjakan TASK [ID] dari file CODEX_IMPLEMENTATION_PLAN_SPMI_AMI_RTM.md.
Gunakan BUSINESS_REQUIREMENTS_SPMI_AMI_RTM.md sebagai sumber requirement bisnis. Jangan menganggap PDF sumber tersedia.

Sebelum menulis kode:
1. Baca README, routes, schema/migrations, controller, service, model, view, dan test yang terkait.
2. Jelaskan keadaan aktual repo dan perbedaan terhadap asumsi task.
3. Buat rencana perubahan file-per-file.
4. Identifikasi risiko keamanan dan risiko migrasi.
5. Jangan ubah file sebelum analisis selesai.

Saat implementasi:
- Pertahankan kompatibilitas workflow lama kecuali task menyatakan cutover.
- Taruh business logic pada service.
- Semua mutation wajib autentikasi, authorization object-level, validasi server-side, dan CSRF.
- Gunakan transaction untuk perubahan lintas tabel.
- Tambahkan audit log untuk aksi sensitif.
- Jangan menyimpan file di document root.
- Jangan menambahkan dependency tanpa alasan dan review.

Setelah implementasi:
1. Jalankan lint/test yang tersedia.
2. Tambahkan test positif dan negatif.
3. Tampilkan migration verification query.
4. Tampilkan daftar file yang berubah.
5. Jelaskan cara rollback.
6. Sebutkan pekerjaan lanjutan yang belum dilakukan.
7. Jangan mengklaim berhasil bila test belum dijalankan.
```

---

# 26. Template Laporan Hasil Codex

Setiap task harus menghasilkan ringkasan:

```markdown
## TASK [ID]

### Current state
...

### Changes
...

### Database migration
...

### Security controls
...

### Tests run
- Command:
- Result:

### Manual test
...

### Rollback
...

### Known limitations
...

### Files changed
...
```

---

# 27. Pertanyaan yang Harus Dikunci dengan Stakeholder

Jangan membuat asumsi permanen untuk hal berikut:

1. Apakah skor sah `1–4` atau `0–4`?
2. Apa definisi skor 1, 2, 3, dan 4?
3. Apa arti formal OB dan KTS?
4. Apakah ada KTS minor/major?
5. Bagaimana formula risk-based audit?
6. Apakah indikator berbobot?
7. Bagaimana agregasi bila indikator N/A?
8. Siapa yang boleh memfinalisasi laporan?
9. Siapa yang boleh memfinalisasi RTM?
10. Apakah auditor dapat menjadi verifier tindak lanjut?
11. Berapa lama retensi bukti?
12. Apakah file lama boleh dihapus?
13. Apakah notulen dan daftar hadir wajib?
14. Apakah satu RTM dapat membahas banyak periode?
15. Apakah satu temuan dapat masuk lebih dari satu RTM?
16. Apakah target dapat diubah di tengah tahun?
17. Apakah unit dapat memiliki instrumen yang berbeda?
18. Apakah bukti URL boleh di-fetch/preview oleh server?
19. Apakah tanda tangan digital dibutuhkan?
20. Apakah data perlu diintegrasikan dengan SIAKAD/keuangan/LPPMPP?

Simpan jawaban di:

```text
docs/product/decision-register.md
```

---

# 28. Prioritas Implementasi Praktis

## Release 1 — Fondasi aman

- M0 seluruhnya;
- M1 seluruhnya;
- M2 seluruhnya;
- M3 versi dan master standar;
- M4 indikator dan target.

## Release 2 — Pelaksanaan AMI baru

- M5 import;
- M6 instrumen;
- M7 assignment snapshot;
- M8 auditee;
- M9 auditor.

## Release 3 — Laporan dan RTM

- M10 laporan;
- M11 RTM;
- M12 tindak lanjut.

## Release 4 — PPEPP lengkap

- M13 peningkatan;
- M14 dashboard/notifikasi;
- M15 migrasi legacy;
- M16 hardening akhir.

---

# 29. Security Release Gate

Production release **ditolak** bila salah satu berikut belum terpenuhi:

- [ ] Tidak ada object-level authorization test.
- [ ] File upload masih berada di public directory.
- [ ] Download file tidak melalui authorization.
- [ ] CSRF tidak aktif pada mutation.
- [ ] Production menampilkan stack trace.
- [ ] Secret masih hardcoded.
- [ ] Session cookie tidak aman pada HTTPS.
- [ ] Finalisasi dapat dipanggil ulang tanpa idempotency.
- [ ] Data final dapat diedit tanpa amendment.
- [ ] Migration belum diuji rollback/restore.
- [ ] Backup belum diuji restore.
- [ ] Export rentan formula injection.
- [ ] Stored XSS test gagal.
- [ ] Audit log aksi sensitif belum tersedia.
- [ ] Terdapat endpoint legacy yang melewati guard baru.
- [ ] Dependency lock file tidak konsisten.
- [ ] Test suite gagal atau belum dijalankan.

---

# 30. Hasil Akhir yang Diharapkan

Setelah semua milestone selesai, sistem harus menyediakan keterlacakan penuh:

```text
Dokumen SPMI revisi berapa?
→ Standar mana?
→ Pernyataan mana?
→ Indikator IKU/IKT mana?
→ Target tahun berapa?
→ Unit dan PIC siapa?
→ Bukti yang diwajibkan apa?
→ Realisasi auditee berapa?
→ Bukti aktual apa?
→ Auditor menilai apa?
→ Temuan dan rekomendasi apa?
→ Keputusan RTM apa?
→ Rencana aksi siapa dan kapan?
→ Bukti tindak lanjut apa?
→ Apakah efektif?
→ Apakah standar/target perlu ditingkatkan?
```

Semua jawaban harus dapat ditemukan tanpa mengubah atau menghilangkan histori audit sebelumnya.
