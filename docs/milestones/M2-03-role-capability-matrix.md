# M2-03 — Role Capability Matrix

- Status: **Implemented and verified**
- Tanggal: 2026-07-25
- Branch milestone: `codex/m2-organization-role-scope`
- Checkpoint sebelumnya: M2-02 commit `2d57026`
- Dependensi: M2-01 organization units dan M2-02 active assignments

## Hasil

M2-03 mengganti capability legacy yang terlalu luas dengan capability per
domain dan aksi. Matriks dideklarasikan di `Authorization_policy` dan menjadi
sumber yang dipakai guard, controller, object policy, serta visibilitas menu.

## Matriks minimum

| Capability | Super Admin | Admin LPMPI | Auditor | Auditee | Scope |
|---|:---:|:---:|:---:|:---:|---|
| `spmi.version.manage` | Ya | Ya | Tidak | Tidak | Organisasi |
| `spmi.standard.manage` | Ya | Ya | Tidak | Tidak | Organisasi |
| `spmi.indicator.manage` | Ya | Ya | Tidak | Tidak | Organisasi |
| `spmi.import` | Ya | Ya | Tidak | Tidak | Organisasi |
| `audit.period.manage` | Ya | Ya | Tidak | Tidak | Organisasi |
| `audit.package.manage` | Ya | Ya | Tidak | Tidak | Organisasi |
| `audit.assignment.manage` | Ya | Ya | Tidak | Tidak | Organisasi |
| `audit.submission.fill` | Tidak | Tidak | Tidak | Ya | Organisasi + ownership |
| `audit.submission.submit` | Tidak | Tidak | Tidak | Ya | Organisasi + ownership/state |
| `audit.assessment.fill` | Tidak | Tidak | Ya | Tidak | Organisasi + ownership |
| `audit.assessment.submit` | Tidak | Tidak | Ya | Tidak | Organisasi + ownership/state |
| `audit.report.view` | Ya | Ya | Tidak | Tidak | Organisasi |
| `audit.report.export` | Ya | Ya | Tidak | Tidak | Organisasi |
| `rtm.manage` | Tidak | Tidak | Tidak | Tidak | Organisasi; belum tersedia |
| `rtm.finalize` | Tidak | Tidak | Tidak | Tidak | Organisasi; belum tersedia |
| `followup.fill` | Tidak | Tidak | Tidak | Tidak | Organisasi; belum tersedia |
| `followup.verify` | Tidak | Tidak | Tidak | Tidak | Organisasi; belum tersedia |
| `security.auditlog.view` | Ya | Tidak | Tidak | Tidak | Global |

Capability identity/administration yang sudah ada tetap dipertahankan:
dashboard, akun sendiri, profil, pengguna, akun participant, master unit, dan
assignment unit/jabatan.

RTM/follow-up sengaja mempunyai mapping role kosong. Entity, finalizer,
verifier, dan separation-of-duties belum diputuskan; keberadaan nama
capability tidak membuka akses.

## Kombinasi capability dan unit scope

Policy menyediakan:

- `allows()` untuk grant role global;
- `capabilityScope()` untuk klasifikasi `global` atau `organization`;
- `allowsInOrganizationUnit()` untuk capability + satu unit aktif;
- `allowsInAnyOrganizationUnit()` untuk capability + minimal satu assignment
  aktif;
- guard controller `require_capability_in_organization_unit()`.

Aturan efektif untuk capability organisasi:

1. akun harus aktif;
2. role harus mempunyai capability;
3. unit target harus ada dan aktif;
4. Super Admin memiliki tanggung jawab organisasi global yang eksplisit;
5. role lain harus mempunyai direct active assignment pada unit target;
6. assignment di luar masa berlaku tidak memberikan scope;
7. membership parent tidak otomatis mencakup descendant.

Modul legacy yang objeknya belum mempunyai `organization_unit_id` tetap
memakai capability + ownership/state yang sudah tersedia. M2-03 tidak
mengarang unit dari `users.nama_unit`. Guard kombinasi unit dipakai saat
entity target mempunyai unit ID stabil pada milestone berikutnya.

## Controller mapping

- Standar: `spmi.standard.manage`.
- Indikator/pertanyaan: `spmi.indicator.manage`; import dan template:
  `spmi.import`.
- Periode: `audit.period.manage`.
- Instrumen/penetapan: `audit.package.manage`.
- Tugas/penugasan: `audit.assignment.manage`.
- Pengisian draft: `audit.submission.fill`; submit:
  `audit.submission.submit`.
- Penilaian draft/bukti: `audit.assessment.fill`; submit/revisi final:
  `audit.assessment.submit`.
- Laporan: `audit.report.view`; export: `audit.report.export`.

Controller public `Auth`/`Welcome` dan controller CLI-only `Maintenance`
merupakan pengecualian yang tidak memakai session capability. Seluruh
controller web bisnis yang terproteksi memakai capability atau mewarisi
controller yang memakainya.

Sidebar tetap dikelompokkan berdasarkan pengalaman tiap role, tetapi setiap
item kini juga difilter melalui central capability policy. Menu bukan
enforcement; controller guard tetap menjadi batas keamanan.

## Migration

Tidak ada migration M2-03. Matriks bersifat code-declared dan scope membaca
`organization_units` serta `user_unit_assignments` yang sudah dibuat oleh
migration 015 dan 016. Perubahan role-capability memerlukan code review,
regression matrix, dan deployment aplikasi, bukan mutasi data tersembunyi.

## Verification

Hasil Windows/Laragon, PHP 8.3.30, dan MySQL 8.4.3:

| Pemeriksaan | Hasil |
|---|---|
| `php tests/role_capability_matrix_regression.php` | PASS — 155 checks |
| `php tests/authorization_policy_regression.php` | PASS — 54 checks |
| Seluruh `tests/*_regression.php` | PASS |
| `php tests/smoke/run.php` | PASS — 32 cases |
| Full PHP lint `application`, `tests`, dan `scripts` | PASS — 157 files |
| `git diff --check` | PASS |

Regression M2-03 memeriksa seluruh capability minimum terhadap keempat role,
akun nonaktif, capability tidak dikenal, unit tidak ada/nonaktif, direct
membership, membership kedaluwarsa, tidak adanya inheritance, Super Admin
scope, deny-default RTM/follow-up, mapping controller, dan menu capability.

## Acceptance criteria

- Matrix terdokumentasi: **terpenuhi**.
- Setiap controller bisnis terproteksi memakai capability: **terpenuhi dan
  diuji**.
- Capability dapat dikombinasikan dengan unit scope: **terpenuhi melalui
  policy dan guard reusable, diuji lintas role/unit/tanggal**.
- Regression seluruh role: **terpenuhi untuk empat role dan negative cases**.

## Langkah berikutnya

M2 selesai. Task berikutnya adalah M3-01 pada branch milestone M3 tersendiri.
Entity M3 yang mempunyai organization unit harus memanggil guard kombinasi
capability + unit sejak list/query hingga mutasi.
