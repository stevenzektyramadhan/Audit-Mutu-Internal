# TASKS.md — Daftar Task Pengembangan AMI

> Kerjakan **satu task per sesi** Cline. Tandai `[x]` setelah selesai.
> Selalu baca `.clinerules` dan `docs/DATABASE.md` sebelum memulai.

---

## FASE 1 — Fondasi (Kerjakan Pertama)

- [x] **TASK-01** · Buat semua file migration SQL
  - Buat folder `migrations/`
  - Buat file `001_add_admin_lpmpi_role.sql` — ALTER ENUM role di tabel users
  - Buat file `002_create_periode_audit.sql` — CREATE TABLE periode_audit
  - Buat file `003_create_penetapan.sql` — CREATE TABLE penetapan
  - Buat file `004_alter_tugas_audit_add_periode.sql` — tambah kolom periode_id
  - Buat file `005_alter_jawaban_audit_new_columns.sql` — tambah semua kolom baru (temuan, jenis_temuan, saran_perbaikan, rencana_perbaikan, dokumen_bukti, tgl_bukti, is_submitted, submitted_at, is_nilai_submitted, nilai_submitted_at)
  - Buat file `006_alter_standar_add_file_instrumen.sql` — tambah kolom file_instrumen
  - Buat file `007_alter_users_add_unit_columns.sql` — tambah kolom nama_unit, jenis_unit
  - Setiap file wajib punya section `-- UP` dan `-- DOWN`

- [x] **TASK-02** · Tambah role admin_lpmpi ke sistem autentikasi
  - Buat `application/core/MY_Controller.php` — berisi MY_Controller + 4 base class sesuai role
  - Buat `Admin_Lpmpi_Controller` extends MY_Controller dengan role check ['super_admin', 'admin_lpmpi']
  - super_admin bisa akses semua halaman admin_lpmpi (via \_check_role)
  - Logika role auditor/auditee tetap tidak berubah, tetap pakai Auth_guard atau base class baru

---

## FASE 2 — Fitur Admin LPMPI

- [x] **TASK-03** · Manajemen Periode Audit
  - Controller : `application/controllers/Periode.php` (extends Admin_Lpmpi_Controller)
  - Model : `application/models/Periode_model.php`
  - Service : `application/services/Periode_service.php`
  - Views : `application/views/lpmpi/periode/` (index, form)
  - Fitur : CRUD periode, toggle is_aktif (hanya 1 bisa aktif), tampilkan status buka/tutup
  - Sidebar : ditambahkan menu Periode Audit untuk super_admin & admin_lpmpi

- [ ] **TASK-04** · Manajemen Akun Auditee & Auditor
  - Controller : `application/controllers/lpmpi/Akun.php`
  - Model : gunakan `User_model.php` yang sudah ada, extend jika perlu
  - Views : `application/views/lpmpi/akun/` (list_auditee, list_auditor, form_tambah, form_edit)
  - Fitur : CRUD akun, filter berdasarkan role, tampilkan nama_unit & jenis_unit untuk auditee

- [ ] **TASK-05** · Upload Instrumen per Standar
  - Controller : `application/controllers/lpmpi/Instrumen.php`
  - Model : `application/models/Standar_model.php` (tambah method upload)
  - Views : `application/views/lpmpi/instrumen/` (index dengan daftar standar + tombol upload)
  - Fitur : Upload PDF/Word, simpan path ke kolom `standar.file_instrumen`, tombol hapus file
  - Validasi : tipe file hanya pdf/doc/docx, max 5MB

- [ ] **TASK-06** · Penugasan Auditor ke Auditee per Standar
  - Controller : `application/controllers/lpmpi/Penugasan.php`
  - Model : `application/models/Tugas_model.php`
  - Views : `application/views/lpmpi/penugasan/` (index, form assign)
  - Fitur : Pilih periode → pilih standar → pilih auditor → pilih auditee → simpan tugas
  - Cegah duplikasi tugas yang sama (periode+standar+auditor+auditee)

- [ ] **TASK-07** · Halaman Penetapan (3 tab)
  - Controller : `application/controllers/lpmpi/Penetapan.php`
  - Model : `application/models/Penetapan_model.php`
  - Views : `application/views/lpmpi/penetapan/index.php` (Bootstrap tab: Pelaksanaan, Pengendalian, Peningkatan)
  - Fitur : Tabel per tab (kolom: Standar, Status, Deskripsi, Upload File), inline edit status & deskripsi, upload file per baris

- [ ] **TASK-08** · Laporan & Statistik
  - Controller : `application/controllers/lpmpi/Laporan.php`
  - Model : `application/models/Laporan_model.php`
  - Views : `application/views/lpmpi/laporan/` (index, detail per standar)
  - Fitur : Rekap skor rata-rata per standar, filter per periode & per auditee, grafik skor (Chart.js), tombol export Excel

---

## FASE 3 — Fitur Auditee

- [ ] **TASK-09** · Inbox Tugas & Form Pengisian
  - Controller : `application/controllers/auditee/Tugas.php`
  - Model : `application/models/Jawaban_model.php`
  - Views : `application/views/auditee/` (inbox, form_isian)
  - Fitur : Daftar tugas masuk (per periode), tampilkan status (belum diisi / draft / submitted / revisi), tombol download file instrumen referensi
  - Form isian : tampilkan semua pertanyaan dalam satu halaman dengan scroll, input jawaban teks + input link bukti per pertanyaan

- [ ] **TASK-10** · Alur Save & Submit Auditee
  - Endpoint Save : simpan jawaban, set `is_submitted = 0`
  - Endpoint Submit : set `is_submitted = 1`, `submitted_at = NOW()`, redirect ke halaman konfirmasi
  - Setelah submit : semua field readonly, tampilkan pesan "Menunggu penilaian auditor"
  - Jika status revisi : tampilkan banner notifikasi "Tugas dikembalikan, silakan perbaiki"

---

## FASE 4 — Fitur Auditor

- [ ] **TASK-11** · Inbox & Form Penilaian
  - Controller : `application/controllers/auditor/Penilaian.php`
  - Model : gunakan `Jawaban_model.php`
  - Views : `application/views/auditor/` (inbox, form_penilaian)
  - Fitur : Daftar tugas yang perlu dinilai, hanya tampil jika auditee sudah submit

- [ ] **TASK-12** · Form Penilaian per Pertanyaan (Popup)
  - Setiap pertanyaan punya tombol "Nilai" → muncul Bootstrap Modal
  - Modal berisi :
    - Skor : radio button atau button group (4 / 3 / 2 / 1)
    - Temuan : textarea
    - Jenis Temuan : radio button (OB / KTS)
    - Saran Perbaikan : textarea
    - Rencana Perbaikan : textarea
    - Dokumen Bukti : upload file
    - Tanggal Bukti : date picker
  - Save per pertanyaan via AJAX (jangan reload halaman)
  - Tampilkan indikator sudah dinilai / belum di daftar pertanyaan

- [ ] **TASK-13** · Save, Submit & Tombol Revisi Auditor
  - Tombol Save : simpan semua nilai draft, `is_nilai_submitted = 0`
  - Tombol Submit : set `is_nilai_submitted = 1`, `nilai_submitted_at = NOW()`, form terkunci
  - Tombol Revisi : reset `jawaban_audit.is_submitted = 0` untuk semua baris tugas ini → auditee bisa edit lagi
  - Konfirmasi dialog sebelum Submit dan Revisi

---

## FASE 5 — Export & Finishing

- [ ] **TASK-14** · Export Excel
  - Library : PhpSpreadsheet (install via Composer jika belum ada)
  - Controller : method `export()` di `Laporan.php`
  - Output : file .xlsx dengan sheet per standar, kolom: Auditee, Pertanyaan, Jawaban, Link Bukti, Skor, Temuan, Jenis Temuan, Saran Perbaikan
  - Filter : bisa export per periode atau per auditee

- [ ] **TASK-15** · Testing & Penyesuaian
  - Uji alur end-to-end: Admin LPMPI buat tugas → Auditee isi & submit → Auditor nilai → Revisi → Submit ulang → Laporan
  - Cek tidak ada bocor akses antar role
  - Cek validasi form semua halaman
  - Cek file upload (instrumen & bukti): berhasil tersimpan dan bisa didownload

---

## CATATAN BUG

> Tulis bug yang ditemukan di luar scope task di sini. Jangan langsung fix tanpa konfirmasi.

| #   | Ditemukan di Task | Deskripsi Bug | Status |
| --- | ----------------- | ------------- | ------ |
|     |                   |               |        |

---

## LOG PENGERJAAN

> Update setelah setiap task selesai.

| Task    | Selesai Tanggal | Catatan                                                                                                                                  |
| ------- | --------------- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| TASK-01 | 27 Juni 2026    | 7 file migration SQL berhasil dibuat di folder migrations/                                                                               |
| TASK-02 | 27 Juni 2026    | MY_Controller.php dibuat dengan 5 class: MY_Controller, Admin_Controller, Admin_Lpmpi_Controller, Auditor_Controller, Auditee_Controller |
| TASK-03 | 27 Juni 2026    | Controller Periode, model, service, dan views lpmpi/periode selesai. Sidebar ditambahkan menu admin_lpmpi. |
