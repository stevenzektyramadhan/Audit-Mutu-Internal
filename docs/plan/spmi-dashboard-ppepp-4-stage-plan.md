SPMI-DASHBOARD-PPEPP-01 — Selaraskan "Alur PPEPP" Dashboard SPMI ke 4 Tahap

Dokumen kendali untuk AI coding agent

Letakkan file ini di:

docs/plan/spmi-dashboard-ppepp-4-stage-plan.md

## 0. One-Time Agent Instruction

Read docs/plan/spmi-dashboard-ppepp-4-stage-plan.md completely.
Execute this task according to the scope, file allowlist, locked decisions,
acceptance criteria, and test requirements defined in this document.
Do not invent requirements outside this document.
Do not touch any file outside the File Allowlist.
Do not modify the Dokumen PPEPP module itself (it is already correct at
4 stages and is the reference/source of truth for this task).
Do not modify legacy AMI behavior.
Stop only for a TRUE BLOCKER as defined in Stop Conditions.
At the end of the task, update the Task Status Table, Execution Log, and
Multi Brain records, then commit.

## 1. Execution Mode

AUTONOMOUS_SINGLE_TASK_WITH_SAFETY_GATES — satu task, satu commit
terfokus, file allowlist ketat, berhenti hanya pada TRUE BLOCKER.

## 2. Latar Belakang / Keputusan Produk

Modul **Dokumen PPEPP** (`application/config/spmi_ppepp.php`,
`spmi_ppepp_documents` table) sudah sengaja dirancang dengan **4 tahap**:
Penetapan, Pelaksanaan, Pengendalian, Peningkatan — **tanpa Evaluasi**,
karena hasil evaluasi (penilaian auditor, laporan) sudah dihasilkan secara
otomatis oleh alur audit SPMI itu sendiri (Assessments + Laporan SPMI),
bukan dokumen yang diunggah manual ke arsip.

Widget **"Alur PPEPP"** dan **"Ringkasan PPEPP"** di Dashboard SPMI
(`lpmpi/spmi-dashboard`, dilihat oleh Super Admin/Admin LPMPI) masih
menampilkan **5 tahap** (termasuk "Evaluasi" sebagai tahap terpisah).
Ini tidak konsisten dengan keputusan Dokumen PPEPP di atas dan harus
diselaraskan menjadi 4 tahap.

## 3. Scope

- Menghapus tahap "Evaluasi" sebagai kartu/step terpisah dari widget
  "Alur PPEPP" (stepper 5 kotak di bagian atas Dashboard SPMI) dan dari
  grid "Ringkasan PPEPP" (kartu detail per tahap di bagian bawah).
- Alur PPEPP menjadi 4 langkah bernomor 01–04: Penetapan, Pelaksanaan,
  Pengendalian, Peningkatan — otomatis ter-renumber karena penomoran
  dihitung dari posisi array, bukan hardcoded.
- Mengubah label teks "5 tahap terhubung" -> "4 tahap terhubung" dan
  "5 tahap mutu" -> "4 tahap mutu" pada dua section terkait.

## 4. Non-Goals — Locked Decisions (agar agent tidak perlu bertanya)

- **Data evaluasi (assessments_draft, assessments_finalized, reports)
  TIDAK dihapus dan TIDAK hilang dari dashboard.** Dua tempat yang sudah
  memakainya secara langsung tetap dipertahankan apa adanya:
  - KPI card "Laporan" (`$metrics['evaluasi']['reports']`) di baris 4
    KPI utama.
  - Item "Penilaian masih draft" (`$metrics['evaluasi']['assessments_draft']`)
    di panel "Perlu perhatian".
  Kedua referensi ini TIDAK perlu diubah — mereka membaca langsung dari
  array `$metrics` yang independen dari `$stage_details`, bukan dari loop
  yang di-scope task ini.
- **Array `$metrics['evaluasi']` di model (`Spmi_management_dashboard_model.php`)
  TIDAK dihapus/diubah.** Menghapusnya akan merusak dua referensi di atas
  dan juga fitur "Export XLSX Tahunan" (`export_rows()`), yang memang
  masih dan tetap boleh menyertakan baris Tahap "evaluasi" — export adalah
  data mentah historis, bukan bagian dari visual Alur PPEPP yang di-scope
  task ini.
- **Tidak ada migration, tidak ada perubahan schema/database.** Task ini
  murni perubahan tampilan (view-level array `$stage_details`).
- **Tidak ada perubahan ke controller** (`Spmi_management_dashboard.php`)
  kecuali ternyata dibutuhkan setelah investigasi ulang oleh agent — jika
  iya, itu wajib dicatat di Execution Log dengan alasannya, bukan
  dilakukan diam-diam.
- Urutan 4 tahap yang tersisa TETAP: Penetapan, Pelaksanaan, Pengendalian,
  Peningkatan (urutan yang sama seperti Dokumen PPEPP), bukan urutan lain.

## 5. File Allowlist

- `application/views/lpmpi/spmi_management_dashboard/index.php`
  (hapus entry `'evaluasi'` dari array `$stage_details`; ubah teks
  "5 tahap terhubung" dan "5 tahap mutu" menjadi "4 tahap...")
- `tests/` — tambahkan/ubah regression guard yang relevan jika sudah ada
  test untuk dashboard ini (cek dulu apakah ada file seperti
  `tests/spmi_management_dashboard_regression.php`; jika tidak ada, buat
  satu file baru dengan nama itu, static-only, mengikuti pola regression
  test lain di repo ini — periksa isinya tidak menghapus assertion lama).
- `docs/plan/spmi-dashboard-ppepp-4-stage-plan.md` (hanya bagian Task
  Status Table dan Execution Log)
- `.multibrain/context/YYYY-MM-DD-sisyphus-spmi-dashboard-ppepp-4-stage.md`
  (file baru)
- `.multibrain/indexes/ami-workflow.md` (tambah satu entry baru di paling
  atas bagian `## Entries`, jangan ubah entry lama)
- `.multibrain/session.md` (hanya kolom "Last updated" pada baris
  `ami-workflow`)

Jika agent menemukan tempat lain yang menampilkan "5 tahap"/"Evaluasi"
sebagai bagian dari Alur PPEPP di luar file `index.php` di atas, itu
adalah TRUE BLOCKER, bukan alasan memperluas allowlist sendiri.

## 6. Required Behavior (Acceptance Criteria)

1. Section "Alur PPEPP" menampilkan tepat 4 kartu langkah: Penetapan (01),
   Pelaksanaan (02), Pengendalian (03), Peningkatan (04) — tidak ada
   kartu "Evaluasi".
2. Label di section tersebut berbunyi "4 tahap terhubung", bukan "5 tahap
   terhubung".
3. Section "Ringkasan PPEPP" menampilkan tepat 4 panel detail (Penetapan,
   Pelaksanaan, Pengendalian, Peningkatan) — tidak ada panel "Evaluasi".
4. Label di section tersebut berbunyi "4 tahap mutu", bukan "5 tahap
   mutu".
5. Anchor link `#spmi-stage-evaluasi` tidak lagi punya target section
   di halaman ini (karena kartunya dihapus) — pastikan tidak ada elemen
   lain yang masih me-link ke anchor tersebut dalam file yang sama.
6. KPI card "Laporan" di baris 4-KPI tetap tampil dan tetap menunjukkan
   angka yang sama seperti sebelumnya (dari `$metrics['evaluasi']['reports']`).
7. Item "Penilaian masih draft" di panel "Perlu perhatian" tetap tampil
   dan tetap menunjukkan angka yang sama seperti sebelumnya.
8. Fitur "Export XLSX Tahunan" tetap menghasilkan file yang sama seperti
   sebelumnya (masih menyertakan baris Tahap "evaluasi" di data mentah).
9. Tidak ada PHP warning/notice/error baru (terutama undefined array key)
   akibat perubahan ini.
10. Halaman Dashboard SPMI untuk role Super Admin dan Admin LPMPI tetap
    memuat tanpa error 500.

## 7. Test Requirements

1. `php -l` pada setiap file PHP yang diubah/ditambah.
2. Jika sudah ada file regression untuk dashboard ini, jalankan; jika
   belum ada, agent membuatnya sesuai Bagian 5 dan menjalankannya.
3. `php tests/sidebar_navigation_regression.php`
4. `php tests/hardening_regression.php`
5. Runtime/browser verification (login sebagai Super Admin atau Admin
   LPMPI, buka `lpmpi/spmi-dashboard`, screenshot sebelum/sesudah):
   jalankan jika environment tersedia; jika tidak, tandai
   `NOT_RUN_ENVIRONMENT` dan catat apa adanya — ini BUKAN blocker untuk
   task sekecil ini, tapi wajib dicatat jujur, jangan diklaim PASS tanpa
   dijalankan.

## 8. Multi Brain Memory Update (Required)

Sebelum commit final:

1. Buat `.multibrain/context/YYYY-MM-DD-sisyphus-spmi-dashboard-ppepp-4-stage.md`
   mengikuti struktur context file lain yang sudah ada di repo ini
   (Judul, Timestamp, Agent, `## Scope`, `## Invariants`, `## Verification`).
   - Scope: Dashboard SPMI "Alur PPEPP"/"Ringkasan PPEPP" diselaraskan ke
     4 tahap, konsisten dengan modul Dokumen PPEPP.
   - Invariants: data metrics evaluasi (`assessments_draft`, `reports`)
     tidak dihapus; Export XLSX tidak berubah; tidak ada migration.
   - Verification: hasil test Bagian 7.
2. Tambahkan satu baris entry baru di paling atas `## Entries` pada
   `.multibrain/indexes/ami-workflow.md`.
3. Perbarui kolom "Last updated" pada baris `ami-workflow` di
   `.multibrain/session.md`.

## 9. Stop Conditions — True Blockers Only

- Ditemukan tempat lain (di luar file allowlist) yang menampilkan "5
  tahap"/kartu Evaluasi sebagai bagian dari visual Alur PPEPP, sehingga
  scope file allowlist tidak cukup.
- Menghapus entry `'evaluasi'` dari `$stage_details` ternyata memicu
  error PHP di tempat lain yang tidak terduga (mis. ada kode lain yang
  mengasumsikan tepat 5 entries).
- Required test pada Bagian 7 tetap gagal setelah penyebab dalam scope
  diperiksa.

Bukan blocker:
- Nama variable/method aktual berbeda dari yang disebut di dokumen ini —
  gunakan nama aktual, dokumentasikan pemetaannya.
- Runtime environment tidak tersedia (tandai `NOT_RUN_ENVIRONMENT`).
- Belum ada file regression test untuk dashboard ini — agent membuatnya
  sesuai Bagian 5, ini bagian dari task, bukan alasan berhenti.

## 10. Task Status Table

| Task | Status | Commit | Tests | Notes |
|---|---|---|---|---|
| SPMI-DASHBOARD-PPEPP-01 | DONE | fix(spmi): align dashboard Alur PPEPP widget to 4 stages, drop Evaluasi | PASS: PHP lint, spmi_dashboard_regression, sidebar_navigation_regression, hardening_regression; runtime NOT_RUN_ENVIRONMENT | Visual `$stage_details` is four stages; evaluasi metrics/KPI/attention/export remain unchanged. |

## 11. Execution Log

```
### YYYY-MM-DD HH:MM — SPMI-DASHBOARD-PPEPP-01
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

### 2026-09-22 14:48 WIB — SPMI-DASHBOARD-PPEPP-01
- Status: DONE
- Branch: dev
- Start SHA: b1ffca58c2edf45c904d5e208cf195a23c7dfc51
- End SHA: recorded by focused commit below
- Commit: fix(spmi): align dashboard Alur PPEPP widget to 4 stages, drop Evaluasi
- Files: `application/views/lpmpi/spmi_management_dashboard/index.php`, `tests/spmi_dashboard_regression.php`, this control plan, and the required `.multibrain` records.
- Tests: PASS `php -l` (view and regression), `php tests/spmi_dashboard_regression.php`, `php tests/sidebar_navigation_regression.php`, `php tests/hardening_regression.php`, and `git diff --check`.
- Runtime verification: NOT_RUN_ENVIRONMENT — no authorized Super Admin/Admin LPMPI credentials were available for the dashboard route.
- Notes: Removed only the visual Evaluasi stage and updated the two stage-count labels. The `$metrics['evaluasi']` KPI/attention references and model export contract were retained; no Dokumen PPEPP, model, controller, schema, migration, export, or AMI legacy file changed. The regression guard was narrowed to management-only markup where prior universal assertions did not match the distinct role dashboard views.

## 12. Commit Convention

```
fix(spmi): align dashboard Alur PPEPP widget to 4 stages, drop Evaluasi
```

Jangan amend commit lain. Jangan push tanpa instruksi eksplisit.
