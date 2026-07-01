# DATABASE.md — Referensi Skema Database AMI

> Selalu rujuk file ini sebelum menulis query. Jangan menebak nama tabel atau kolom.
> Jika ada perubahan skema, update file ini sekaligus.

---

## TABEL YANG SUDAH ADA (existing)

### `users`

| Kolom      | Tipe                                                  | Keterangan                 |
| ---------- | ----------------------------------------------------- | -------------------------- |
| id         | INT PK AUTO_INCREMENT                                 |                            |
| nama       | VARCHAR(100)                                          |                            |
| email      | VARCHAR(100) UNIQUE                                   |                            |
| password   | VARCHAR(255)                                          | bcrypt via password_hash() |
| role       | ENUM('super_admin','admin_lpmpi','auditor','auditee') | ← admin_lpmpi ditambahkan  |
| nama_unit  | VARCHAR(100) NULL                                     | diisi jika role = auditee  |
| jenis_unit | ENUM('prodi','unit','lembaga') NULL                   | diisi jika role = auditee  |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP                    |                            |

> ⚠️ Kolom `role` perlu di-ALTER untuk menambah nilai `admin_lpmpi`.
> ⚠️ Kolom `nama_unit` dan `jenis_unit` ditambahkan jika belum ada.

---

### `standar`

| Kolom          | Tipe                               | Keterangan                           |
| -------------- | ---------------------------------- | ------------------------------------ |
| id             | INT PK AUTO_INCREMENT              |                                      |
| nama_standar   | VARCHAR(200)                       |                                      |
| deskripsi      | TEXT NULL                          |                                      |
| file_instrumen | VARCHAR(255) NULL                  | ← BARU: path file PDF/Word referensi |
| created_at     | DATETIME DEFAULT CURRENT_TIMESTAMP |                                      |

---

### `pertanyaan`

| Kolom          | Tipe                               | Keterangan           |
| -------------- | ---------------------------------- | -------------------- |
| id             | INT PK AUTO_INCREMENT              |                      |
| standar_id     | INT FK → standar.id                |                      |
| isi_pertanyaan | TEXT                               | isi pertanyaan audit |
| created_at     | DATETIME DEFAULT CURRENT_TIMESTAMP |                      |

---

### `tugas_audit`

| Kolom      | Tipe                               | Keterangan                |
| ---------- | ---------------------------------- | ------------------------- |
| id         | INT PK AUTO_INCREMENT              |                           |
| periode_id | INT FK → periode_audit.id          | ← BARU: relasi ke periode |
| standar_id | INT FK → standar.id                |                           |
| auditor_id | INT FK → users.id                  | role = auditor            |
| auditee_id | INT FK → users.id                  | role = auditee            |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP |                           |

---

### `jawaban_audit`

| Kolom              | Tipe                                 | Keterangan                             |
| ------------------ | ------------------------------------ | -------------------------------------- |
| id                 | INT PK AUTO_INCREMENT                |                                        |
| tugas_id           | INT FK → tugas_audit.id              |                                        |
| pertanyaan_id      | INT FK → pertanyaan.id               |                                        |
| jawaban            | TEXT NULL                            | jawaban auditee                        |
| link_bukti         | VARCHAR(500) NULL                    | URL link bukti dari auditee            |
| is_submitted       | TINYINT(1) DEFAULT 0                 | ← BARU: 0=draft, 1=submitted           |
| submitted_at       | DATETIME NULL                        | ← BARU: waktu submit auditee           |
| skor               | INT NULL                             | nilai auditor (1–4)                    |
| temuan             | TEXT NULL                            | ← BARU: ganti kolom "catatan"          |
| jenis_temuan       | ENUM('ob','kts') NULL                | ← BARU: Observasi atau Ketidaksesuaian |
| saran_perbaikan    | TEXT NULL                            | ← BARU                                 |
| rencana_perbaikan  | TEXT NULL                            | ← BARU                                 |
| dokumen_bukti      | VARCHAR(255) NULL                    | ← BARU: path file bukti auditor        |
| tgl_bukti          | DATE NULL                            | ← BARU: tanggal dokumen bukti          |
| is_nilai_submitted | TINYINT(1) DEFAULT 0                 | ← BARU: 0=draft nilai, 1=nilai fix     |
| nilai_submitted_at | DATETIME NULL                        | ← BARU: waktu auditor submit nilai     |
| updated_at         | DATETIME ON UPDATE CURRENT_TIMESTAMP |                                        |

> ⚠️ Kolom lama `catatan` di-rename menjadi `temuan` via ALTER TABLE.

---

## TABEL BARU

### `periode_audit`

| Kolom          | Tipe                               | Keterangan                               |
| -------------- | ---------------------------------- | ---------------------------------------- |
| id             | INT PK AUTO_INCREMENT              |                                          |
| nama_periode   | VARCHAR(100)                       | contoh: "Semester Ganjil 2025/2026"      |
| tahun_akademik | VARCHAR(20)                        | contoh: "2025/2026"                      |
| semester       | ENUM('ganjil','genap')             |                                          |
| tanggal_buka   | DATE                               | mulai bisa diisi auditee                 |
| tanggal_tutup  | DATE                               | batas akhir pengisian                    |
| is_aktif       | TINYINT(1) DEFAULT 0               | hanya 1 periode yang aktif dalam 1 waktu |
| created_at     | DATETIME DEFAULT CURRENT_TIMESTAMP |                                          |

---

### `penetapan`

| Kolom      | Tipe                                             | Keterangan                    |
| ---------- | ------------------------------------------------ | ----------------------------- |
| id         | INT PK AUTO_INCREMENT                            |                               |
| standar_id | INT FK → standar.id                              |                               |
| kategori   | ENUM('pelaksanaan','pengendalian','peningkatan') |                               |
| status     | VARCHAR(100) NULL                                | contoh: "Terpenuhi", "Proses" |
| deskripsi  | TEXT NULL                                        |                               |
| file_path  | VARCHAR(255) NULL                                | path file yang diupload       |
| created_at | DATETIME DEFAULT CURRENT_TIMESTAMP               |                               |
| updated_at | DATETIME ON UPDATE CURRENT_TIMESTAMP             |                               |

---

## FILE MIGRATION

Semua perubahan skema dibuat sebagai file terpisah di folder `migrations/`:

```
migrations/
  001_add_admin_lpmpi_role.sql
  002_create_periode_audit.sql
  003_create_penetapan.sql
  004_alter_tugas_audit_add_periode.sql
  005_alter_jawaban_audit_new_columns.sql
  006_alter_standar_add_file_instrumen.sql
  007_alter_users_add_unit_columns.sql
```

Jalankan secara urut. Setiap file berisi `-- UP` dan `-- DOWN` section.

---

## RELASI ANTAR TABEL

```
users (auditee) ──┐
                  ├── tugas_audit ──── standar ──── pertanyaan
users (auditor) ──┘        │                            │
                            │                            ▼
periode_audit ─────────────┘                     jawaban_audit
                                                         │
standar ──── penetapan                          (skor, temuan,
                                                 jenis_temuan,
                                                 saran_perbaikan,
                                                 rencana_perbaikan)
```

---

## ATURAN QUERY PENTING

```php
// BENAR — gunakan query builder CI3
$this->db->where('tugas_id', $tugas_id);
$this->db->where('is_submitted', 1);
$this->db->get('jawaban_audit');

// SALAH — jangan raw string tanpa escape
$this->db->query("SELECT * FROM jawaban_audit WHERE tugas_id = " . $tugas_id);

// Cek skor valid (1-4) sebelum insert
if (!in_array($skor, [1, 2, 3, 4])) {
    // tolak input
}
```
