# AGENTS.md

# Source of Truth – Web Audit Mutu Internal (AMI) Perguruan Tinggi

## 1. Identitas Proyek

Nama sistem: **Web Audit Mutu Internal (AMI) Perguruan Tinggi**

Jenis proyek: **Aplikasi web berbasis CodeIgniter 3**

Target pengerjaan: **4 hari**

Tujuan utama:
Membangun aplikasi web sederhana untuk membantu proses Audit Mutu Internal di lingkungan perguruan tinggi. Sistem digunakan untuk mengelola standar audit, pertanyaan audit, penugasan auditor, pengisian bukti oleh auditee, dan penilaian oleh auditor.

Fokus proyek adalah **Minimum Viable Product (MVP)**. Jangan membuat fitur tambahan di luar kebutuhan inti.

---

## 2. Tech Stack Wajib

Framework backend: **CodeIgniter 3**

Bahasa: **PHP**

Database: **MySQL / MariaDB**

Local server: **Laragon atau XAMPP**

Frontend: **Bootstrap 4**

Template UI: **AdminLTE 3 atau SB Admin 2**

Autentikasi:

* Gunakan `password_hash()`
* Gunakan `password_verify()`
* Gunakan session bawaan CodeIgniter 3

Database access:

* Gunakan Query Builder / Active Record bawaan CodeIgniter 3
* Hindari raw SQL kecuali benar-benar diperlukan

---

## 3. Aturan Mutlak CodeIgniter

Wajib menggunakan **CodeIgniter 3**, bukan CodeIgniter 4.

Jangan membuat struktur CodeIgniter 4 seperti:

```text
app/Controllers
app/Models
app/Views
.env
spark
```

Gunakan struktur CodeIgniter 3:

```text
application/
├── controllers/
├── models/
├── views/
├── config/
├── libraries/
├── helpers/
└── services/
```

Controller harus menggunakan gaya CI 3:

```php
class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
}
```

Model harus menggunakan gaya CI 3:

```php
class User_model extends CI_Model
{
    public function get_all()
    {
        return $this->db->get('users')->result();
    }
}
```

Jangan menggunakan namespace PHP seperti CI 4.

Jangan menggunakan routing CI 4.

Jangan menggunakan migration CI 4.

---

## 4. Arsitektur Sistem

Gunakan arsitektur:

```text
Controller → Service → Model → Database
```

Tujuan arsitektur ini adalah agar kode lebih aman, rapi, dan mudah dirawat.

### 4.1 Controller Layer

Controller hanya bertugas:

* menerima request,
* mengecek akses dasar,
* memanggil service,
* mengirim data ke view,
* melakukan redirect.

Controller tidak boleh berisi business logic panjang.

Contoh yang benar:

```php
public function store()
{
    $result = $this->tugas_audit_service->create_tugas($this->input->post());

    if ($result['success']) {
        $this->session->set_flashdata('success', $result['message']);
    } else {
        $this->session->set_flashdata('error', $result['message']);
    }

    redirect('tugas_audit');
}
```

### 4.2 Service Layer

Service berisi business logic.

Contoh logic yang harus masuk service:

* proses login,
* verifikasi password,
* membuat tugas audit,
* otomatis membuat jawaban audit kosong,
* menyimpan jawaban auditee,
* menyimpan nilai auditor,
* update status tugas,
* ownership check,
* database transaction.

Service boleh dibuat di folder:

```text
application/services/
```

Cara load service di controller:

```php
require_once APPPATH . 'services/Tugas_audit_service.php';
$this->tugas_audit_service = new Tugas_audit_service();
```

### 4.3 Model Layer

Model hanya bertugas mengakses database.

Model boleh berisi:

* `insert`
* `update`
* `delete`
* `find`
* `get_all`
* `get_by_role`
* `get_by_auditor`
* `get_by_auditee`
* query join sederhana

Model tidak boleh berisi business logic utama.

---

## 5. Struktur Folder Final

Gunakan struktur berikut:

```text
application/
├── controllers/
│   ├── Auth.php
│   ├── Dashboard.php
│   ├── Users.php
│   ├── Standar.php
│   ├── Pertanyaan.php
│   ├── Tugas_audit.php
│   ├── Auditee.php
│   └── Auditor.php
│
├── models/
│   ├── User_model.php
│   ├── Standar_model.php
│   ├── Pertanyaan_model.php
│   ├── Tugas_audit_model.php
│   └── Jawaban_audit_model.php
│
├── services/
│   ├── Auth_service.php
│   ├── User_service.php
│   ├── Standar_service.php
│   ├── Pertanyaan_service.php
│   ├── Tugas_audit_service.php
│   ├── Auditee_service.php
│   └── Auditor_service.php
│
├── libraries/
│   └── Auth_guard.php
│
├── helpers/
│   └── app_helper.php
│
└── views/
    ├── layouts/
    │   ├── header.php
    │   ├── sidebar.php
    │   └── footer.php
    │
    ├── auth/
    │   └── login.php
    │
    ├── dashboard/
    │   ├── super_admin.php
    │   ├── auditor.php
    │   └── auditee.php
    │
    ├── users/
    │   ├── index.php
    │   ├── create.php
    │   └── edit.php
    │
    ├── standar/
    │   ├── index.php
    │   ├── create.php
    │   └── edit.php
    │
    ├── pertanyaan/
    │   ├── index.php
    │   ├── create.php
    │   └── edit.php
    │
    ├── tugas_audit/
    │   ├── index.php
    │   ├── create.php
    │   └── show.php
    │
    ├── auditee/
    │   ├── index.php
    │   ├── tugas.php
    │   └── isi.php
    │
    └── auditor/
        ├── index.php
        ├── tugas.php
        └── nilai.php
```

---

## 6. Role Pengguna

Sistem memiliki 3 role:

### 6.1 Super Admin

Hak akses:

* login,
* logout,
* melihat dashboard admin,
* mengelola data pengguna,
* mengelola data standar audit,
* mengelola data pertanyaan audit,
* membuat tugas audit,
* melihat hasil audit.

### 6.2 Auditee

Auditee adalah unit atau program studi yang diaudit.

Hak akses:

* login,
* logout,
* melihat dashboard auditee,
* melihat tugas audit yang diberikan,
* mengisi jawaban audit,
* menginput link bukti dokumen,
* melihat status penilaian.

### 6.3 Auditor

Auditor adalah dosen atau pihak yang melakukan penilaian audit.

Hak akses:

* login,
* logout,
* melihat dashboard auditor,
* melihat daftar tugas audit,
* melihat jawaban auditee,
* membuka link bukti dokumen,
* memberikan skor,
* memberikan catatan atau temuan.

---

## 7. Alur Utama Sistem

Alur wajib sistem:

1. Super Admin login.
2. Super Admin membuat akun auditor dan auditee.
3. Super Admin membuat data standar audit.
4. Super Admin membuat pertanyaan berdasarkan standar audit.
5. Super Admin membuat tugas audit dengan memilih auditor, auditee, dan standar.
6. Sistem otomatis membuat baris kosong di `jawaban_audit` berdasarkan pertanyaan pada standar tersebut.
7. Auditee login.
8. Auditee melihat tugas audit yang diberikan.
9. Auditee mengisi jawaban singkat dan link bukti dokumen.
10. Sistem mengubah status tugas menjadi `diisi`.
11. Auditor login.
12. Auditor melihat daftar auditee yang ditugaskan kepadanya.
13. Auditor memeriksa jawaban dan link bukti dokumen.
14. Auditor memberikan skor dan catatan.
15. Sistem mengubah status tugas menjadi `dinilai`.
16. Super Admin dapat melihat hasil audit.

---

## 8. Fitur MVP Wajib

### 8.1 Auth

Fitur:

* halaman login,
* proses login,
* logout,
* role redirect setelah login,
* proteksi halaman berdasarkan session,
* proteksi halaman berdasarkan role.

Redirect setelah login:

```text
super_admin → Dashboard Super Admin
auditor     → Dashboard Auditor
auditee     → Dashboard Auditee
```

### 8.2 Super Admin

Fitur:

* dashboard super admin,
* CRUD user,
* CRUD standar,
* CRUD pertanyaan,
* CRUD tugas audit,
* lihat hasil audit sederhana.

Dashboard menampilkan:

* jumlah user,
* jumlah auditor,
* jumlah auditee,
* jumlah standar,
* jumlah pertanyaan,
* jumlah tugas audit.

### 8.3 Auditee

Fitur:

* dashboard auditee,
* melihat tugas saya,
* mengisi jawaban audit,
* mengisi link bukti,
* melihat status tugas.

Dashboard menampilkan:

* jumlah tugas audit,
* jumlah tugas belum diisi,
* jumlah tugas sudah diisi,
* jumlah tugas sudah dinilai.

### 8.4 Auditor

Fitur:

* dashboard auditor,
* melihat tugas audit,
* melihat jawaban auditee,
* membuka link bukti,
* memberi skor,
* memberi catatan.

Dashboard menampilkan:

* jumlah tugas audit,
* jumlah tugas belum dinilai,
* jumlah tugas sudah dinilai.

---

## 9. Fitur yang Tidak Perlu Dibuat di MVP

Jangan membuat fitur berikut pada tahap awal:

* upload file langsung ke server,
* export PDF,
* export Excel,
* notifikasi email,
* reset password,
* multi periode audit,
* grafik statistik kompleks,
* log aktivitas detail,
* approval bertingkat,
* komentar diskusi,
* chat antar user,
* tanda tangan digital,
* integrasi Google Drive API.

Fitur tersebut boleh disebut sebagai pengembangan lanjutan.

---

## 10. Struktur Database

Gunakan 5 tabel inti:

1. `users`
2. `standar`
3. `pertanyaan`
4. `tugas_audit`
5. `jawaban_audit`

### 10.1 Tabel `users`

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'auditor', 'auditee') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### 10.2 Tabel `standar`

```sql
CREATE TABLE standar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_standar VARCHAR(150) NOT NULL,
    deskripsi TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### 10.3 Tabel `pertanyaan`

```sql
CREATE TABLE pertanyaan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    standar_id INT NOT NULL,
    isi_pertanyaan TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (standar_id) REFERENCES standar(id) ON DELETE CASCADE
);
```

### 10.4 Tabel `tugas_audit`

```sql
CREATE TABLE tugas_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    auditor_id INT NOT NULL,
    auditee_id INT NOT NULL,
    standar_id INT NOT NULL,
    status ENUM('belum_diisi', 'diisi', 'dinilai') DEFAULT 'belum_diisi',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (auditor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (auditee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (standar_id) REFERENCES standar(id) ON DELETE CASCADE
);
```

### 10.5 Tabel `jawaban_audit`

```sql
CREATE TABLE jawaban_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tugas_audit_id INT NOT NULL,
    pertanyaan_id INT NOT NULL,
    jawaban TEXT NULL,
    link_bukti TEXT NULL,
    skor INT NULL,
    catatan TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    FOREIGN KEY (tugas_audit_id) REFERENCES tugas_audit(id) ON DELETE CASCADE,
    FOREIGN KEY (pertanyaan_id) REFERENCES pertanyaan(id) ON DELETE CASCADE
);
```

---

## 11. Data Dummy Awal

Buat akun Super Admin:

```text
email    : admin@ami.test
password : admin123
role     : super_admin
```

Buat akun Auditor:

```text
email    : auditor@ami.test
password : auditor123
role     : auditor
```

Buat akun Auditee:

```text
email    : auditee@ami.test
password : auditee123
role     : auditee
```

Password harus disimpan dalam bentuk hash menggunakan `password_hash()`, bukan plain text.

---

## 12. Controller

Gunakan controller berikut:

```text
application/controllers/Auth.php
application/controllers/Dashboard.php
application/controllers/Users.php
application/controllers/Standar.php
application/controllers/Pertanyaan.php
application/controllers/Tugas_audit.php
application/controllers/Auditee.php
application/controllers/Auditor.php
```

### 12.1 Auth.php

Method:

* `index()`
* `login()`
* `logout()`

### 12.2 Dashboard.php

Method:

* `index()`

Dashboard membaca role dari session lalu menampilkan view sesuai role.

### 12.3 Users.php

Khusus role `super_admin`.

Method:

* `index()`
* `create()`
* `store()`
* `edit($id)`
* `update($id)`
* `delete($id)`

### 12.4 Standar.php

Khusus role `super_admin`.

Method:

* `index()`
* `create()`
* `store()`
* `edit($id)`
* `update($id)`
* `delete($id)`

### 12.5 Pertanyaan.php

Khusus role `super_admin`.

Method:

* `index()`
* `create()`
* `store()`
* `edit($id)`
* `update($id)`
* `delete($id)`

### 12.6 Tugas_audit.php

Khusus role `super_admin`.

Method:

* `index()`
* `create()`
* `store()`
* `show($id)`
* `delete($id)`

### 12.7 Auditee.php

Khusus role `auditee`.

Method:

* `index()`
* `tugas()`
* `isi($tugas_id)`
* `simpan_jawaban($tugas_id)`

### 12.8 Auditor.php

Khusus role `auditor`.

Method:

* `index()`
* `tugas()`
* `nilai($tugas_id)`
* `simpan_nilai($tugas_id)`

---

## 13. Model

Gunakan model berikut:

```text
application/models/User_model.php
application/models/Standar_model.php
application/models/Pertanyaan_model.php
application/models/Tugas_audit_model.php
application/models/Jawaban_audit_model.php
```

Model hanya bertugas untuk operasi database.

---

## 14. Service

Gunakan service berikut:

```text
application/services/Auth_service.php
application/services/User_service.php
application/services/Standar_service.php
application/services/Pertanyaan_service.php
application/services/Tugas_audit_service.php
application/services/Auditee_service.php
application/services/Auditor_service.php
```

### 14.1 Auth_service.php

Tanggung jawab:

* mencari user berdasarkan email,
* memverifikasi password,
* membuat data session,
* mengembalikan status login.

### 14.2 User_service.php

Tanggung jawab:

* validasi data user,
* hash password,
* membuat user baru,
* update user,
* mencegah email duplikat.

### 14.3 Standar_service.php

Tanggung jawab:

* validasi data standar,
* create/update/delete standar.

### 14.4 Pertanyaan_service.php

Tanggung jawab:

* validasi pertanyaan,
* memastikan standar tersedia,
* create/update/delete pertanyaan.

### 14.5 Tugas_audit_service.php

Tanggung jawab:

* validasi auditor,
* validasi auditee,
* validasi standar,
* membuat tugas audit,
* otomatis membuat jawaban audit kosong,
* menggunakan database transaction.

### 14.6 Auditee_service.php

Tanggung jawab:

* mengambil tugas berdasarkan auditee yang login,
* ownership check tugas auditee,
* menyimpan jawaban,
* menyimpan link bukti,
* mengubah status tugas menjadi `diisi`.

### 14.7 Auditor_service.php

Tanggung jawab:

* mengambil tugas berdasarkan auditor yang login,
* ownership check tugas auditor,
* menyimpan skor,
* menyimpan catatan,
* mengubah status tugas menjadi `dinilai`.

---

## 15. Library Auth Guard

Buat library:

```text
application/libraries/Auth_guard.php
```

Tanggung jawab:

* cek apakah user sudah login,
* cek role user,
* mencegah akses halaman yang tidak sesuai role.

Contoh penggunaan:

```php
$this->auth_guard->check();
$this->auth_guard->only(['super_admin']);
```

Aturan akses:

```text
Users.php          → super_admin
Standar.php        → super_admin
Pertanyaan.php     → super_admin
Tugas_audit.php    → super_admin
Auditee.php        → auditee
Auditor.php        → auditor
```

---

## 16. Session

Session minimal berisi:

```php
$this->session->userdata('user_id');
$this->session->userdata('nama');
$this->session->userdata('email');
$this->session->userdata('role');
```

Setelah login berhasil, wajib lakukan:

```php
$this->session->sess_regenerate(TRUE);
```

Tujuannya untuk mengurangi risiko session fixation.

---

## 17. Keamanan Wajib

### 17.1 Password Security

Gunakan:

```php
password_hash($password, PASSWORD_DEFAULT)
```

Verifikasi:

```php
password_verify($password_input, $user->password)
```

Jangan simpan password dalam bentuk plain text.

### 17.2 Role-Based Access Control

Setiap controller harus membatasi role.

Role check wajib dilakukan menggunakan `Auth_guard`.

### 17.3 Ownership Check

Jangan hanya cek role. Sistem juga harus mengecek kepemilikan data.

Auditee hanya boleh mengisi tugas miliknya sendiri:

```php
$tugas->auditee_id == $this->session->userdata('user_id')
```

Auditor hanya boleh menilai tugas yang ditugaskan kepadanya:

```php
$tugas->auditor_id == $this->session->userdata('user_id')
```

Hal ini penting agar user tidak bisa mengganti ID pada URL untuk membuka data orang lain.

### 17.4 CSRF Protection

Aktifkan CSRF di:

```text
application/config/config.php
```

Set:

```php
$config['csrf_protection'] = TRUE;
```

Gunakan helper form CI 3:

```php
<?php echo form_open('users/store'); ?>
```

Jangan membuat form POST manual tanpa token CSRF.

### 17.5 XSS Protection

Saat menampilkan data ke view, gunakan:

```php
<?= html_escape($data); ?>
```

Contoh:

```php
<?= html_escape($user->nama); ?>
<?= html_escape($jawaban->jawaban); ?>
```

Untuk link bukti:

```php
<a href="<?= html_escape($jawaban->link_bukti); ?>" target="_blank" rel="noopener noreferrer">
    Lihat Bukti
</a>
```

### 17.6 Query Builder

Gunakan Query Builder CI 3.

Contoh:

```php
$this->db->where('email', $email)->get('users');
```

Hindari raw SQL seperti:

```php
$this->db->query("SELECT * FROM users WHERE email = '$email'");
```

### 17.7 Validasi Input

Gunakan `form_validation` CodeIgniter 3.

Validasi minimal:

Login:

```text
email wajib diisi
password wajib diisi
```

User:

```text
nama wajib diisi
email wajib diisi
email harus valid
email harus unik
password wajib saat create
role wajib dipilih
```

Standar:

```text
nama_standar wajib diisi
```

Pertanyaan:

```text
standar_id wajib dipilih
isi_pertanyaan wajib diisi
```

Tugas audit:

```text
auditor_id wajib dipilih
auditee_id wajib dipilih
standar_id wajib dipilih
```

Jawaban auditee:

```text
link_bukti wajib diisi
link_bukti harus URL valid
jawaban boleh singkat
```

Penilaian auditor:

```text
skor wajib dipilih
skor hanya boleh 1, 2, 3, atau 4
catatan boleh diisi
```

### 17.8 Database Transaction

Gunakan database transaction saat membuat tugas audit.

Proses yang harus berada dalam satu transaction:

1. insert ke `tugas_audit`,
2. ambil daftar pertanyaan berdasarkan `standar_id`,
3. insert baris kosong ke `jawaban_audit`.

Jika salah satu proses gagal, semua proses harus batal.

---

## 18. Status Tugas Audit

Gunakan status:

```text
belum_diisi
diisi
dinilai
```

Makna status:

```text
belum_diisi = tugas sudah dibuat admin, tetapi auditee belum mengisi bukti
diisi       = auditee sudah mengisi jawaban dan link bukti
dinilai     = auditor sudah memberi skor dan catatan
```

Gunakan status secara konsisten.

Disarankan membuat konstanta di:

```text
application/config/app_constants.php
```

Contoh:

```php
defined('STATUS_BELUM_DIISI') OR define('STATUS_BELUM_DIISI', 'belum_diisi');
defined('STATUS_DIISI') OR define('STATUS_DIISI', 'diisi');
defined('STATUS_DINILAI') OR define('STATUS_DINILAI', 'dinilai');
```

---

## 19. Skema Skor Audit

Skor menggunakan angka 1 sampai 4.

Keterangan:

```text
1 = Tidak sesuai
2 = Kurang sesuai
3 = Sesuai
4 = Sangat sesuai
```

Validasi:

* skor hanya boleh 1, 2, 3, atau 4,
* skor boleh kosong sebelum dinilai,
* catatan boleh kosong, tetapi disarankan diisi jika skor rendah.

---

## 20. Menu Sidebar

### 20.1 Menu Super Admin

```text
Dashboard
Data Pengguna
Data Standar
Data Pertanyaan
Tugas Audit
Hasil Audit
Logout
```

### 20.2 Menu Auditee

```text
Dashboard
Tugas Saya
Pengisian Audit
Hasil Penilaian
Logout
```

### 20.3 Menu Auditor

```text
Dashboard
Tugas Audit
Penilaian Auditee
Logout
```

---

## 21. Batasan Sistem

Sistem hanya dibuat untuk tugas/proyek akademik.

Sistem belum mencakup semua kebutuhan AMI nyata.

Sistem tidak melakukan upload file bukti ke server.

Sistem hanya menyimpan link dokumen bukti.

Sistem belum memiliki fitur periode audit.

Sistem belum memiliki fitur export laporan.

Sistem belum memiliki fitur notifikasi.

Sistem belum memiliki fitur approval bertingkat.

---

## 22. Prioritas Pengerjaan

Urutan pengerjaan:

1. Setup project CodeIgniter 3.
2. Setup database.
3. Buat tabel.
4. Buat seed akun awal.
5. Buat login dan logout.
6. Buat role redirect.
7. Buat `Auth_guard`.
8. Buat struktur service.
9. Buat layout AdminLTE.
10. Buat dashboard berdasarkan role.
11. Buat CRUD user.
12. Buat CRUD standar.
13. Buat CRUD pertanyaan.
14. Buat fitur tugas audit.
15. Buat fitur auditee mengisi jawaban.
16. Buat fitur auditor memberi nilai.
17. Tes alur dari awal sampai akhir.
18. Rapikan tampilan.
19. Perbaiki bug.

---

## 23. Step Pengerjaan 4 Hari

### Hari 1

Target:

* project CI 3 berjalan,
* database terhubung,
* login berjalan,
* role redirect berjalan.

Pekerjaan:

1. Setup CodeIgniter 3.
2. Setup database.
3. Buat 5 tabel inti.
4. Buat akun dummy.
5. Konfigurasi `config.php`.
6. Konfigurasi `database.php`.
7. Konfigurasi `autoload.php`.
8. Buat `Auth.php`.
9. Buat `Auth_service.php`.
10. Buat `User_model.php`.
11. Buat halaman login.
12. Buat logout.
13. Buat dashboard berdasarkan role.

### Hari 2

Target:

* Super Admin bisa mengelola data master.

Pekerjaan:

1. Pasang AdminLTE 3 atau Bootstrap 4.
2. Buat layout header, sidebar, footer.
3. Buat `Auth_guard`.
4. Buat CRUD user.
5. Buat CRUD standar.
6. Buat CRUD pertanyaan.
7. Buat validasi form dasar.
8. Terapkan CSRF dan XSS escaping pada view.

### Hari 3

Target:

* alur audit utama berjalan.

Pekerjaan:

1. Buat fitur tugas audit.
2. Gunakan transaction saat membuat tugas audit.
3. Buat jawaban audit kosong otomatis.
4. Buat halaman tugas auditee.
5. Buat halaman isi jawaban auditee.
6. Buat halaman tugas auditor.
7. Buat halaman penilaian auditor.
8. Terapkan ownership check.

### Hari 4

Target:

* sistem siap demo.

Pekerjaan:

1. Rapikan dashboard.
2. Tambahkan card statistik sederhana.
3. Rapikan tabel dan tombol.
4. Tambahkan flash message.
5. Tes alur demo dari awal sampai akhir.
6. Tambahkan data dummy rapi.
7. Perbaiki bug kecil.
8. Siapkan penjelasan presentasi.

---

## 24. Alur Demo Wajib

Pastikan alur berikut berjalan:

```text
1. Login sebagai Super Admin.
2. Tambah auditor.
3. Tambah auditee.
4. Tambah standar.
5. Tambah pertanyaan.
6. Buat tugas audit.
7. Logout.
8. Login sebagai Auditee.
9. Isi jawaban dan link bukti.
10. Logout.
11. Login sebagai Auditor.
12. Beri skor dan catatan.
13. Logout.
14. Login sebagai Super Admin.
15. Lihat hasil audit.
```

---

## 25. Output Akhir

Output akhir proyek:

* aplikasi web AMI berbasis CodeIgniter 3,
* database MySQL/MariaDB,
* login 3 role,
* arsitektur Controller-Service-Model,
* Auth Guard,
* CRUD data master,
* fitur penugasan audit,
* fitur pengisian bukti oleh auditee,
* fitur penilaian oleh auditor,
* dashboard sederhana,
* data dummy untuk demo,
* alur demo berjalan dari awal sampai akhir.

---

## 26. Larangan untuk AI Coding

Jangan membuat fitur di luar MVP.

Jangan mengubah framework ke CodeIgniter 4.

Jangan menggunakan Laravel.

Jangan menggunakan React/Vue.

Jangan menggunakan Composer package tambahan jika tidak perlu.

Jangan membuat API-only backend.

Jangan membuat sistem upload file.

Jangan membuat sistem export PDF/Excel.

Jangan membuat struktur folder selain yang sudah ditentukan.

Jangan menaruh business logic panjang di controller.

Jangan menyimpan password plain text.

Jangan mengakses data auditee/auditor tanpa ownership check.

Jangan menampilkan data user tanpa `html_escape`.

Jangan membuat query raw SQL dari input user.

---

## 27. Prinsip Development

Prioritas utama:

```text
jalan dulu → aman dasar → rapi → mudah dijelaskan
```

Bukan prioritas:

```text
fitur banyak → UI mewah → arsitektur terlalu kompleks
```

Gunakan solusi sederhana, konsisten, dan mudah dirawat.

Jika ada konflik antara fitur tambahan dan deadline, pilih fitur inti MVP.

Jika ada konflik antara cepat dan keamanan dasar, tetap terapkan keamanan dasar seperti password hash, role check, ownership check, CSRF, dan escaping.
