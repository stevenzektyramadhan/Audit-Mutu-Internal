# Manifest Asset Manual Pengguna AMI/SPMI

## Capture contract

- Sumber seluruh gambar: disposable fixture **M17-07A** melalui project Compose terisolasi `m17_07a_docs_manual`; bukan aplikasi shared.
- Viewport tetap: 1440 x 960 CSS px. Gambar adalah viewport aplikasi saja, tanpa browser chrome.
- Akun yang digunakan hanya role sintetis pada fixture: Super Admin, Admin LPMPI, Auditee, dan Auditor. Password, alamat akun, cookie, token, dan URL host tidak didokumentasikan.
- Data fixture menggunakan label `M17-07A` dan `m17-07a.test`; tidak ada data institusi nyata atau evidence eksternal yang dibuka.
- Status redaksi semua gambar tercapture: `REVIEWED — no crop/redaction required`. Tidak ada field kredensial terisi pada gambar. URL evidence sintetis tidak ditampilkan pada viewport yang dipakai.
- Anchor manual adalah ID bagian stabil untuk `docs/manual-pengguna-ami-spmi.md`.

## Asset matrix

| ID | Role | Route template | Synthetic fixture precondition | Redaction status | Source paths | Filename | Manual anchor |
|---|---|---|---|---|---|---|---|
| G-01 | Public | `/auth` | Form login kosong sebelum autentikasi | REVIEWED — none | `application/config/routes.php:52-59`; `application/views/auth/login.php` | `g-01-login-empty.png` | `#login` |
| SA-01 | Super Admin | `/lpmpi/spmi-dashboard` | Dashboard role Super Admin; seeded M17R lifecycle graph | REVIEWED — none | `application/config/routes.php:168-169`; `application/views/layouts/sidebar.php:10-25` | `sa-01-admin-dashboard.png` | `#super-admin-dashboard` |
| SA-02 | Super Admin | `/users` | Enam user fixture tersedia; halaman manajemen pengguna dibuka tanpa mutasi | REVIEWED — none | `application/config/routes.php`; `application/views/layouts/sidebar.php:10-16` | `sa-02-user-management.png` | `#manajemen-pengguna` |
| M-01 | Admin LPMPI | `/lpmpi/organization` | Root unit dan dua unit sintetis M17R tersedia | REVIEWED — none | `application/config/routes.php:77-87`; `application/views/layouts/sidebar.php:27-35` | `m-01-organization.png` | `#struktur-organisasi` |
| M-03 | Admin LPMPI | `/lpmpi/spmi-standards` | Active version, standard, dan indicator M17R tersedia | REVIEWED — none | `application/config/routes.php:88-101`; `application/views/layouts/sidebar.php:31-33` | `m-03-standards.png` | `#standar-spmi` |
| M-06 | Admin LPMPI | `/lpmpi/spmi-instruments` | Paket M17R-P1 dengan lima policy question dan rubric 1–4 tersedia | REVIEWED — none | `application/config/routes.php:118-135`; `application/views/layouts/sidebar.php:34` | `m-06-instruments.png` | `#instrumen-audit` |
| M-07 | Admin LPMPI | `/lpmpi/spmi-audits` | Tiga cycle configured dan empat assignment fixture tersedia | REVIEWED — none | `application/config/routes.php:136-146`; `application/views/layouts/sidebar.php:35` | `m-07-cycle-list.png` | `#siklus-dan-penugasan` |
| M-07D | Admin LPMPI | `/lpmpi/spmi-audits/cycle/detail/<fixture-cycle>` | Cycle configured dibuka dalam mode detail | REVIEWED — none | `application/config/routes.php:139-142` | `m-07-cycle-detail.png` | `#detail-siklus` |
| AU-01 | Auditee | `/auditee/spmi` | Auditee A memiliki assignment configured miliknya | REVIEWED — none | `application/config/routes.php:190-196`; `application/views/layouts/sidebar.php:48-51` | `au-01-workspace-list.png` | `#workspace-auditee` |
| AU-02 | Auditee | `/auditee/spmi/assignment/<fixture-assignment>` | Assignment Auditee A masih draft/editable; lima item policy tersedia | REVIEWED — none | `application/config/routes.php:191-195`; `application/views/layouts/sidebar.php:50` | `au-02-editable-assignment.png` | `#pengisian-assignment` |
| AU-04 | Auditee | `/auditee/spmi/assignment/<fixture-assignment>/confirm` | **NOT CAPTURED** — seed awal tidak membuat submission; route confirmation membutuhkan state submitted | N/A — not captured | `application/config/routes.php:192` | `au-04-submitted-confirmation.png` | `#konfirmasi-submission` |
| AU-06 | Auditee | `/auditee/spmi/assignment/<fixture-assignment>/final-result` | **NOT CAPTURED** — seed awal tidak membuat assessment/final result; route mengembalikan 404 | N/A — not captured | `application/config/routes.php:196` | `au-06-final-result.png` | `#hasil-akhir-auditee` |
| AR-01 | Auditor | `/auditor/spmi` | Auditor A memiliki assignment configured miliknya | REVIEWED — none | `application/config/routes.php:210-220`; `application/views/layouts/sidebar.php:43-46` | `ar-01-auditor-inbox.png` | `#inbox-auditor` |
| AR-02 | Auditor | `/auditor/spmi/assignment/<fixture-assignment>` | Assignment Auditor A terbuka; belum ada assessment tersimpan | REVIEWED — none | `application/config/routes.php:211-218`; `application/views/layouts/sidebar.php:45` | `ar-02-assessment-workspace.png` | `#workspace-penilaian` |
| AR-03 | Auditor | `/auditor/spmi/assignment/<fixture-assignment>` | Deterministic pre-assessment draft; score/finding controls terlihat dan belum terisi | REVIEWED — none | `application/config/routes.php:212-215` | `ar-03-score-finding-draft.png` | `#draft-skor-temuan` |
| AR-05 | Auditor | `/auditor/spmi/assignment/<fixture-assignment>/return` | **NOT CAPTURED** — fixture seed tidak membuat submitted assessment yang dapat dikembalikan secara deterministik | N/A — not captured | `application/config/routes.php:215` | — | `#return-revisi` |
| AR-06 | Auditor | `/auditor/spmi/assignment/<fixture-assignment>/finalize` | **NOT CAPTURED** — fixture seed tidak membuat assessment final; finalize adalah POST transition, bukan state awal | N/A — not captured | `application/config/routes.php:214` | — | `#assessment-final` |
| R-01 | Admin LPMPI | `/lpmpi/spmi-reports` | Reports surface dibuka pada fixture tanpa pre-created report | REVIEWED — none | `application/config/routes.php:147-151`; `application/views/layouts/sidebar.php:36-39` | `r-01-reports.png` | `#laporan-spmi` |
| R-03 | Admin LPMPI | `/lpmpi/spmi-rtm` | RTM surface dibuka; fixture tidak pre-create meeting | REVIEWED — none | `application/config/routes.php:152-159`; `application/views/layouts/sidebar.php:37` | `r-03-rtm.png` | `#rtm-spmi` |
| R-04 | Admin LPMPI | `/lpmpi/spmi-follow-ups` | Follow-up surface dibuka; fixture tidak pre-create follow-up | REVIEWED — none | `application/config/routes.php:160-166`; `application/views/layouts/sidebar.php:38` | `r-04-follow-ups.png` | `#tindak-lanjut` |
| R-05 | Admin LPMPI | `/lpmpi/spmi-recap` | Rekap PPEPP surface dibuka pada lifecycle fixture | REVIEWED — none | `application/config/routes.php:167-169`; `application/views/layouts/sidebar.php:39` | `r-05-recap-dashboard.png` | `#rekap-ppepp` |
| L-01 | Admin LPMPI | `/lpmpi/legacy-ami-archive` dan `/lpmpi/legacy-ami-archive/preflight` | Satu archive run, task, answer, dan issue sintetis tersedia; hanya GET/read-only | REVIEWED — none | `application/config/routes.php:170-174` | `l-01-archive-overview.png`, `l-01-archive-preflight.png` | `#arsip-ami-legacy` |

## Verification notes

- Required static fixture regression was run before startup and failed on a pre-existing plan-status assertion: it expected the current `M17-08` status-table title without the title punctuation used in the plan. No source was changed.
- Fixture bootstrap and topology verification passed before browser capture.
- Required HTTP smoke was run against the same isolated project. It reached the auditee submission lane but failed at the existing evidence-render assertion: Auditor A did not render four synthetic evidence URLs. This is why submission-dependent AU-04/AU-06/AR-05/AR-06 are explicitly not captured.
- Browser capture used only normal authenticated UI actions and GET navigation. No external evidence URL was dereferenced.
- The two unavailable auditee probe filenames were produced while checking the 404 response and are intentionally excluded from the valid asset listing above; they are not claimed as screenshots of supported application states.
