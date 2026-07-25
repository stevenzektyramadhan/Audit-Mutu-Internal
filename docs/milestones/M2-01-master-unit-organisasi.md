# M2-01 — Master Unit Organisasi

- Status: **Implemented and verified**
- Tanggal: 2026-07-25
- Branch: `codex/m2-01-master-unit-organisasi`
- Dependensi: M0 dan M1

## Hasil

M2-01 menambahkan master unit dengan ID stabil dan hierarki eksplisit melalui
tabel `organization_units`. Surface administrasi tersedia untuk
`super_admin` dan `admin_lpmpi` melalui capability
`organization_units.manage`.

Jenis unit yang didukung:

| Jenis | Induk yang diizinkan |
|---|---|
| Universitas | Tidak ada; menjadi root |
| Fakultas / UPPS | Universitas |
| Program studi | Fakultas / UPPS |
| Lembaga | Universitas |
| Biro | Universitas |
| Unit | Universitas, fakultas/UPPS, lembaga, biro, atau unit lain |

Service menolak kode duplikat, kode dengan format tidak aman, induk yang tidak
sesuai, siklus, perubahan jenis yang merusak relasi anak, aktivasi di bawah
induk nonaktif, dan deaktivasi induk yang masih mempunyai turunan aktif.

Tidak ada endpoint atau method hard delete. Unit yang tidak lagi digunakan
diubah menjadi `active = 0`, sehingga ID dan histori referensinya tetap
tersedia.

## Schema dan seed

Migration:

```text
migrations/015_create_organization_units.sql
```

Migration membuat:

- self foreign key `parent_id` dengan update/delete `RESTRICT`;
- unique key case-insensitive pada `code`;
- index parent dan `type, active`;
- kolom JSON opsional yang belum dibuka sebagai input UI;
- seed root `UNIVERSITY` / `Universitas`.

Migration aman dijalankan ulang. Untuk upgrade database yang sudah ada:

```bash
mysql -u <user> -p <database> < migrations/015_create_organization_units.sql
```

Jalankan setelah backup dan verifikasi target database. Production tetap
memerlukan prosedur DBA/deployment; jangan memakai database pengguna bersama
untuk pengujian.

## Boundary dan keamanan

- Controller hanya menerima mutasi melalui POST dengan CSRF global.
- Capability diperiksa oleh `Authorization_policy`.
- Controller hanya mengorkestrasi request/response.
- Validasi hierarki dan transisi status berada di
  `Organization_unit_service`.
- Query dan write berada di `Organization_unit_model`.
- Create, update, activate, dan deactivate dicatat ke immutable audit ledger.
- Semua output HTML dan data JavaScript memakai helper encoding kontekstual.
- Metadata JSON tidak dapat diisi melalui form untuk mencegah mass assignment.

Kolom legacy `users.nama_unit` dan `users.jenis_unit` belum dihubungkan atau
dihapus. Migrasi membership user merupakan scope M2-02. Karena belum ada
`user_unit_assignments`, scope admin dan objek audit masih institution-wide;
M2-01 tidak mengarang mapping dari teks legacy ke ID organisasi.

## Verification

Hasil terakhir pada Windows/Laragon, PHP 8.3.30, dan MySQL 8.4.3:

| Pemeriksaan | Hasil |
|---|---|
| `php tests/organization_units_regression.php` | PASS — 42 checks |
| Seluruh regression `tests/*regression.php` | PASS |
| `php tests/smoke/run.php` | PASS — 31 cases |
| Migration 015 pada database disposable | PASS — dua kali, seed tetap satu |
| Import `database_schema.sql` oleh smoke suite | PASS |
| Targeted PHP lint | PASS |
| `git diff --check` | PASS |

Browser visual otomatis tidak dijalankan karena tidak ada browser yang
terhubung pada sesi implementasi. Surface yang sama sudah diuji melalui HTTP
end-to-end oleh smoke suite.

## Acceptance criteria

- Hierarki seluruh jenis unit dapat direpresentasikan: **terpenuhi**.
- Program studi terhubung langsung ke fakultas/UPPS: **terpenuhi dan diuji**.
- Unit dapat dinonaktifkan tanpa menghapus histori: **terpenuhi dan diuji**.
- Kode unik: **terpenuhi pada database dan service**.
- Seed universitas root: **terpenuhi dan idempotent**.

## Langkah berikutnya

M2-02 belum dimulai. Setelah M2-01 diterima, task berikutnya adalah
`user_unit_assignments` beserta jabatan, masa berlaku, primary assignment,
strategi mapping legacy, dan regression scope lintas unit.
