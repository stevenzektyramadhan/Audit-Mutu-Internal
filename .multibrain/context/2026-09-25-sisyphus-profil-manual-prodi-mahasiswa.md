# Manual Profile Prodi dan Statistik Mahasiswa

## Scope

- Menambahkan CRUD manual individual untuk `profil_prodi` dan
  `profil_mahasiswa_stats` tanpa migration atau perubahan schema.
- Menambahkan route `profil/prodi/*` dan `profil/mahasiswa/*`, controller
  `Profil`, service `Profil_service`, model row-scoped, dua form, dan aksi
  manajemen pada halaman Profil.

## Invariants

- Hanya `super_admin` dan `admin_lpmpi`; setiap store, update, dan delete
  memerlukan POST dan memakai `form_open()` agar CSRF global tetap aktif.
- Controller -> `Profil_service` -> `Profil_model`; update/delete dibatasi
  primary key dan service memvalidasi ulang input serta keberadaan baris.
- Form manual tidak mengekspos `id_prodi_pddikti`.
- Sync PDDikti tidak berubah: payload Prodi/statistik non-empty tetap replace
  penuh, sedangkan payload kosong tetap mempertahankan data lokal/manual.
- AMI legacy, migration, schema, dan `Pddikti_service` tidak disentuh.

## Verification

- Lint PHP untuk seluruh file Profil yang diubah lulus.
- `profil_manual_data_regression`, `pddikti_sync_regression`, hardening,
  sidebar navigation, dan account settings lulus.
- `legacy_ami_archive_regression` tetap gagal sebelumnya pada sidebar key
  `dashboard`, tidak terkait perubahan ini.
- QA browser super_admin: create, edit, tampilkan, dan delete ulang satu Prodi
  serta satu statistik mahasiswa; chart mahasiswa tetap tampil. GET delete
  terautentikasi menghasilkan HTTP 405. Tidak ada data QA tersisa.
- Pada mobile, aksi manajemen ditumpuk secara lokal tanpa overflow; PDDikti
  sync tetap tersedia. Screenshot inspeksi piksel tidak didukung reviewer,
  tetapi DOM 375px menunjukkan lebar tindakan konsisten dan console bersih.
