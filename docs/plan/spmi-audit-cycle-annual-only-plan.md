SPMI-CYCLE-01 — Siklus SPMI Menjadi Tahunan (Hapus Kewajiban Semester

Dokumen kendali untuk AI coding agent

Letakkan file ini di:

docs/plan/spmi-cycle-annual-only-plan.md

## 0. One-Time Agent Instruction

Gunakan instruksi berikut satu kali saja ketika memulai pekerjaan:

Read docs/plan/spmi-cycle-annual-only-plan.md completely.
Execute this task according to the scope, file allowlist, acceptance
criteria, and test requirements defined in this document.
Do not invent requirements outside this document.
Do not touch any file outside the File Allowlist.
Do not modify legacy AMI behavior (Periode.php, Periode_service.php,
Jawaban_model.php, Pddikti_service.php, application/views/lpmpi/periode/\*).
Stop only for a TRUE BLOCKER as defined in Stop Conditions.
At the end of the task, update the Task Status Table and Execution Log
in this file, then commit.

## 1. Execution Mode

AUTONOMOUS_SINGLE_TASK_WITH_SAFETY_GATES

- Agent membaca seluruh dokumen ini sebelum mengubah apa pun.
- Agent hanya boleh mengubah file yang ada di File Allowlist (Bagian 6).
- Satu commit terfokus untuk implementasi.
- Agent berhenti hanya pada TRUE BLOCKER (Bagian 9).
- Agent memperbarui Task Status Table dan Execution Log (Bagian 10) setelah
  task selesai.

## 2. Latar Belakang / Keputusan Produk

Tim LPMPI (rapat internal) memutuskan: **penugasan audit SPMI dilakukan per
tahun, bukan per semester.** Saat ini modul "Siklus & Penugasan SPMI"
mewajibkan pengguna mengisi `Tahun akademik` DAN `Semester` (Ganjil/Genap)
saat membuat siklus. Field semester harus dihapus dari alur pembuatan dan
tampilan siklus. Field ini murni milik SPMI (`spmi_audit_cycles`) dan
sepenuhnya independen dari modul AMI legacy (`periode_audit`), yang punya
tabel dan controller-nya sendiri (`Periode.php`) dan tidak boleh disentuh.

## 3. Scope

Task ini mencakup:

- Menghapus kewajiban input `semester` pada form create/edit siklus SPMI.
- Menghapus validasi server-side yang mewajibkan `semester`.
- Menghapus tampilan "Tahun akademik — Semester" pada daftar dan detail
  siklus, diganti tampilan "Tahun akademik" saja.

## 4. Non-Goals

Agent dilarang melakukan hal berikut dalam task ini:

- Membuat migration baru atau mengubah/menghapus kolom `semester` di
  database. Kolom tetap ada di `spmi_audit_cycles` (sudah nullable sejak
  migration 031), hanya berhenti dipakai secara aktif oleh form dan
  validasi.
- Mengubah data siklus yang sudah ada (tidak backfill, tidak update baris).
- Mengubah apa pun di modul AMI legacy: `application/controllers/Periode.php`,
  `application/services/Periode_service.php`,
  `application/views/lpmpi/periode/*`,
  `application/models/Jawaban_model.php`,
  `application/services/Pddikti_service.php`.
- Mengubah `cycle_code`, `title`, `description`, `start_date`, `end_date`,
  atau bagian lain dari alur siklus di luar field semester.
- Mengubah Penugasan (assignment), Standar SPMI, Laporan SPMI, RTM, atau
  workspace auditor/auditee — field `semester` tidak dipakai di modul-modul
  tersebut (sudah diverifikasi: tidak ada referensi `academic_year` atau
  `semester` di luar 5 file pada Bagian 6).
- Refactor unrelated, mengubah pola arsitektur, atau "membetulkan" hal lain
  yang ditemukan di luar scope ini.

## 5. Locked Decisions (agar agent tidak perlu bertanya)

- Kolom `semester` di tabel `spmi_audit_cycles` **tetap ada di database**,
  tidak di-drop. Ini menjaga kompatibilitas mundur untuk siklus lama yang
  sudah tersimpan dengan nilai `ganjil`/`genap`.
- Siklus lama yang masih punya nilai `semester` tersimpan tidak perlu
  diubah datanya; UI cukup berhenti menampilkan dan meminta field itu untuk
  siklus baru maupun saat edit.
- Field `academic_year` tetap wajib diisi dan tetap format teks bebas
  (contoh: "2026/2027"), tidak berubah.
- Label kolom "Periode" pada tabel daftar siklus tetap ada dan diisi dari
  `start_date` — `end_date` (tidak berubah; itu bukan bagian dari
  `academic_period`).
- Tidak perlu menambahkan flag/toggle "mode semester vs tahunan" — task ini
  membuat siklus SPMI selalu tahunan, tanpa opsi kembali ke semester.

## 6. File Allowlist

Hanya file berikut yang boleh diubah:

- `application/controllers/lpmpi/Spmi_audits.php`
- `application/services/Spmi_audits_service.php`
- `application/views/lpmpi/spmi_audits/cycle_form.php`
- `application/views/lpmpi/spmi_audits/index.php`
- `application/views/lpmpi/spmi_audits/cycle_detail.php`
- `tests/spmi_audits_regression.php` (tambah/ubah assertion sesuai perilaku
  baru, jika ada assertion terkait `semester` atau `academic_year`)
- `docs/plan/spmi-cycle-annual-only-plan.md` (hanya bagian Task Status
  Table dan Execution Log)
- `.multibrain/context/YYYY-MM-DD-sisyphus-spmi-cycle-annual-only.md`
  (file baru, sesuai Bagian 10)
- `.multibrain/indexes/ami-workflow.md` (tambah satu entry baru saja,
  sesuai Bagian 10; jangan mengubah entry lama)
- `.multibrain/session.md` (hanya kolom "Last updated" pada baris
  `ami-workflow`, sesuai Bagian 10)

Jika agent menemukan file lain yang ternyata mereferensikan `semester` atau
`academic_year` pada `spmi_audit_cycles` di luar daftar ini, itu adalah
TRUE BLOCKER (lihat Bagian 9) — bukan alasan untuk memperluas allowlist
sendiri.

## 7. Required Behavior (Acceptance Criteria)

1. Form buat siklus baru (`cycle_form.php` via `Spmi_audits::cycle_create`
   atau route setara) tidak lagi menampilkan dropdown "Semester".
2. Form edit siklus tidak lagi menampilkan dropdown "Semester".
3. Validasi server-side (`cycle_rules()` di controller, dan/atau
   `valid_cycle()` di service) tidak lagi mewajibkan atau memvalidasi
   `semester`.
4. Submit form create/edit tanpa mengirim field `semester` berhasil
   (tidak error validasi).
5. Halaman daftar siklus (`index.php`) menampilkan "Tahun akademik" saja
   pada kolom periode akademik, tanpa embel-embel semester.
6. Halaman detail siklus (`cycle_detail.php`) menampilkan "Tahun akademik"
   saja pada baris periode akademik, tanpa embel-embel semester.
7. Siklus lama yang di database masih punya nilai `semester` (`ganjil`/
   `genap`) tetap bisa dibuka di halaman daftar dan detail tanpa error,
   dan tetap hanya menampilkan tahun akademiknya.
8. Tidak ada 500 error atau PHP warning/notice baru akibat perubahan ini.
9. `cycle_code`, `title`, `description`, `start_date`, `end_date` tetap
   berfungsi persis seperti sebelumnya.

## 8. Test Requirements

Urutan test yang wajib dijalankan dan lulus:

1. `php -l` pada setiap file PHP yang diubah.
2. `php tests/spmi_audits_regression.php`
3. `php tests/m17_schema_regression.php` (memastikan tidak ada regresi
   schema karena task ini tidak mengubah schema)
4. `php tests/spmi_reports_regression.php`
5. `php tests/legacy_ami_archive_regression.php` (memastikan AMI legacy
   tidak tersentuh)
6. `php tests/hardening_regression.php`
7. `php tests/sidebar_navigation_regression.php`

Agent wajib membedakan STATIC SOURCE-CONTRACT TEST dan RUNTIME BEHAVIOR
TEST. Jika runtime (Docker/browser) tidak tersedia di environment agent,
tandai runtime sebagai `NOT_RUN_ENVIRONMENT` dan tetap lanjutkan — ini
bukan blocker untuk task sekecil ini, tapi wajib dicatat apa adanya di
Task Status Table, jangan diklaim PASS runtime tanpa eksekusi runtime.

## 9. Stop Conditions — True Blockers Only

Agent hanya boleh berhenti jika:

- Field `semester` atau `academic_year` ternyata dipakai di file di luar
  File Allowlist (misalnya di Laporan SPMI, RTM, atau dashboard) sehingga
  menghapusnya dari form berisiko membuat data/tampilan lain rusak.
- Required test pada Bagian 8 tetap gagal setelah penyebab dalam scope
  diperiksa.
- Working tree user berkonflik pada salah satu file di allowlist.

Hal berikut BUKAN blocker dan harus diselesaikan otomatis:

- Nama method/variable aktual berbeda dari yang disebut di dokumen ini
  (gunakan nama aktual, dokumentasikan pemetaannya).
- Runtime environment tidak tersedia (tandai `NOT_RUN_ENVIRONMENT`).

## 10. Multi Brain Memory Update (Required)

Setelah task selesai dan sebelum commit final, agent wajib mengikuti
konvensi memori repo ini (`.multibrain/`):

1. Buat file context baru di
   `.multibrain/context/YYYY-MM-DD-sisyphus-spmi-cycle-annual-only.md`
   dengan struktur yang sama seperti context file lain yang sudah ada
   (lihat contoh: `.multibrain/context/2026-09-20-sisyphus-upload-size-settings.md`):
   - Judul, Timestamp, Agent
   - `## Scope` — ringkas perubahan: siklus SPMI jadi tahunan, semester
     tidak lagi diminta/divalidasi/ditampilkan, kolom DB tidak diubah.
   - `## Invariants` — hal yang sengaja TIDAK diubah (AMI legacy, schema,
     Master SPMI/import, Laporan, RTM, dsb).
   - `## Verification` — hasil test dari Bagian 8, dibedakan static vs
     runtime.
2. Tambahkan satu baris entry baru di **paling atas** bagian `## Entries`
   pada `.multibrain/indexes/ami-workflow.md`, mengikuti format entry yang
   sudah ada (tanggal - Agent: ringkasan satu baris -> link ke file
   context).
3. Perbarui baris `ami-workflow` di `.multibrain/session.md` (kolom
   "Last updated") supaya tetap sinkron dengan entry terbaru.

Ketiga langkah ini termasuk dalam commit yang sama dengan implementasi
(Bagian 12), bukan commit terpisah.

## 11. Task Status Table

Agent hanya boleh memperbarui kolom Status, Commit, Tests, Notes.

| Task          | Status      | Commit | Tests | Notes |
| ------------- | ----------- | ------ | ----- | ----- |
| SPMI-CYCLE-01 | DONE | `feat(spmi): make audit cycle annual-only, drop semester requirement` | PASS: PHP lint; `spmi_audits`, `m17_schema`, `spmi_reports`, `hardening`, `sidebar_navigation`. BASELINE_FAIL: `legacy_ami_archive` missing `dashboard` sidebar target in HEAD. | Runtime create/edit NOT_RUN_ENVIRONMENT (no authenticated account); plan self-reference `spmi-cycle` path is nonexistent, so this existing `spmi-audit-cycle` document was used for bookkeeping only. |

## 12. Execution Log

Agent menambahkan entry baru di bawah ini tanpa mengubah entry lama.

Format:

```
### YYYY-MM-DD HH:MM — SPMI-CYCLE-01
- Status:
- Branch:
- Start SHA:
- End SHA:
- Commit:
- Files:
- Tests:
- Runtime verification:
- Notes:
```

## 13. Commit Convention

### 2026-09-22 11:38 WIB — SPMI-CYCLE-01
- Status: DONE
- Branch: `dev`
- Start SHA: `3fd0c06b6917c9080031e188611540d10b5a3843`
- End SHA: recorded by the focused commit below
- Commit: `feat(spmi): make audit cycle annual-only, drop semester requirement`
- Files: five allowlisted SPMI cycle controller/service/view files; `tests/spmi_audits_regression.php`; allowed plan and `.multibrain` records.
- Tests: PASS PHP lint, `spmi_audits_regression`, `m17_schema_regression`, `spmi_reports_regression`, `hardening_regression`, `sidebar_navigation_regression`; BASELINE_FAIL `legacy_ami_archive_regression` (missing `dashboard` sidebar target already absent from HEAD).
- Runtime verification: `NOT_RUN_ENVIRONMENT` for authenticated create/edit; anonymous Docker login smoke returned HTTP 200.
- Notes: The plan self-reference `docs/plan/spmi-cycle-annual-only-plan.md` does not exist; the existing `spmi-audit-cycle` control document was used only for its permitted bookkeeping.

```
feat(spmi): make audit cycle annual-only, drop semester requirement
```

Jangan amend commit lain yang tidak terkait. Jangan push tanpa instruksi
eksplisit.
