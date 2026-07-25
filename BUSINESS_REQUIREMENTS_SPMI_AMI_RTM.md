# Business Requirements — SPMI, AMI, RTM, dan PPEPP

## 1. Status Dokumen Ini

Dokumen ini adalah **ringkasan requirement mandiri** yang diekstrak dari dua dokumen bisnis yang dianalisis di luar repository:

1. `STANDAR SPMI ✔✔✔✔.pdf`
2. `LAPORAN RTM AMI 2024-2025.pdf`

Kedua PDF tersebut **tidak diasumsikan tersedia di repository** dan tidak wajib diberikan kepada coding agent. Coding agent harus menggunakan dokumen ini sebagai sumber requirement bisnis.

Dokumen ini bukan transkripsi penuh PDF. Jika terdapat ketidakjelasan, konflik, atau keputusan bisnis yang belum ditetapkan, coding agent harus:
- mencatatnya sebagai pertanyaan stakeholder;
- tidak mengarang aturan;
- tidak mengubah skema atau workflow berdasarkan tebakan.

## 2. Hierarki Sumber Kebenaran

Urutan sumber kebenaran untuk implementasi:

1. Keputusan stakeholder yang sudah dicatat dalam `docs/product/decision-register.md`.
2. Dokumen requirement ini.
3. Keadaan aktual repository dan database.
4. Rencana implementasi teknis.
5. Asumsi agent — hanya boleh digunakan bila diberi label dan tidak memengaruhi aturan permanen.

Bila requirement bertentangan dengan keadaan repo:
- jangan langsung mengganti behavior;
- dokumentasikan gap;
- buat migration/cutover plan;
- pertahankan data historis.

## 3. Tujuan Produk

Aplikasi harus mendukung siklus:

```text
Penetapan
→ Pelaksanaan
→ Evaluasi
→ Pengendalian
→ Peningkatan
```

atau PPEPP.

Hubungan domain yang diinginkan:

```text
Versi Dokumen SPMI
  → Standar
    → Pernyataan Standar
      → Indikator IKU/IKT
        → Target Tahunan
        → Penanggung Jawab
        → Lingkup Penerapan
        → Kebutuhan Bukti
          → Pertanyaan/Instrumen AMI
            → Penugasan Audit
              → Realisasi dan Bukti Auditee
                → Penilaian Auditor
                  → Temuan dan Rekomendasi
                    → RTM
                      → Rencana Aksi
                        → Bukti Tindak Lanjut
                          → Verifikasi Efektivitas
                            → Peningkatan Standar/Target
```

## 4. Fakta Utama dari Dokumen Standar SPMI

### 4.1 Identitas dan versioning

Dokumen sumber memiliki identitas dokumen, tanggal, dan nomor revisi. Artinya sistem harus memperlakukan standar sebagai dokumen yang memiliki versi dan masa berlaku.

Audit historis harus tetap menunjuk pada versi standar yang digunakan saat audit dibuat. Perubahan versi baru tidak boleh mengubah hasil audit lama.

### 4.2 Jumlah dan kelompok standar

Dokumen menetapkan 21 standar:
- 14 standar yang terkait SN Dikti;
- 7 standar tambahan internal.

Kelompoknya:

#### Pendidikan
1. Standar Kompetensi Lulusan
2. Standar Proses Pembelajaran
3. Standar Penilaian Pembelajaran
4. Standar Pengelolaan Pembelajaran
5. Standar Isi Pembelajaran
6. Standar Dosen dan Tenaga Kependidikan
7. Standar Sarana dan Prasarana
8. Standar Pembiayaan

#### Penelitian
9. Standar Luaran Penelitian
10. Standar Proses Penelitian
11. Standar Masukan Penelitian

#### Pengabdian kepada Masyarakat
12. Standar Luaran Pengabdian
13. Standar Proses Pengabdian
14. Standar Masukan Pengabdian

#### Standar tambahan perguruan tinggi
15. Standar Jati Diri
16. Standar AIK
17. Standar VMTS
18. Standar Tata Pamong
19. Standar Kerja Sama
20. Standar Kemahasiswaan dan Alumni
21. Standar Pengelolaan Keuangan

### 4.3 Anatomi standar

Setiap standar dapat memiliki:
- visi, misi, dan tujuan yang terkait;
- rasional;
- pihak yang bertanggung jawab;
- definisi dan istilah;
- pernyataan isi standar;
- strategi pelaksanaan;
- indikator ketercapaian;
- dokumen terkait;
- referensi.

Data tersebut tidak boleh dipaksa menjadi satu kolom teks bila perlu dicari, difilter, diberi relasi, atau diaudit.

### 4.4 Pernyataan, indikator, pertanyaan

Ketiganya berbeda:

- **Pernyataan standar**: kewajiban/kondisi normatif.
- **Indikator**: ukuran ketercapaian pernyataan.
- **Pertanyaan audit**: cara auditor memeriksa indikator.

Satu pernyataan dapat memiliki banyak indikator. Satu indikator dapat memiliki satu atau beberapa pertanyaan audit.

### 4.5 IKU dan IKT

Indikator dikategorikan sebagai:
- IKU — Indikator Kinerja Utama;
- IKT — Indikator Kinerja Tambahan.

Kategori harus disimpan sebagai data dan tidak di-hardcode ke tampilan.

### 4.6 Baseline dan target tahunan

Dokumen memuat baseline dan target berdasarkan tahun akademik, termasuk rentang seperti:
- baseline 2024/2025;
- target 2025/2026;
- target 2026/2027;
- target 2027/2028;
- target 2028/2029;
- target 2029/2030.

Target tidak boleh ditimpa ketika tahun berubah. Sistem harus menyimpan histori target per indikator dan tahun akademik.

### 4.7 Tipe pengukuran

Indikator tidak hanya berupa skor. Tipe pengukuran dapat meliputi:
- tersedia/tidak tersedia;
- terlaksana/tidak terlaksana;
- persentase;
- jumlah;
- desimal;
- rasio;
- nominal mata uang;
- durasi;
- frekuensi;
- teks/kualitatif;
- indikator komposit.

Sistem perlu memisahkan:
- target;
- realisasi auditee;
- hasil verifikasi;
- skor audit.

### 4.8 Penanggung jawab dan lingkup organisasi

Penanggung jawab dapat berada pada:
- universitas;
- fakultas/UPPS;
- program studi;
- lembaga;
- biro;
- unit;
- jabatan tertentu;
- pengguna tertentu.

Satu pengguna dapat memiliki beberapa penugasan unit/jabatan dengan masa berlaku.

Audit harus dapat dilakukan pada beberapa jenis objek organisasi, tidak hanya program studi.

### 4.9 Kebutuhan bukti

Kebutuhan bukti dapat berupa:
- dokumen;
- formulir;
- kebijakan;
- SOP/manual;
- laporan;
- berita acara;
- daftar hadir;
- kuesioner;
- data numerik;
- file;
- URL.

Satu indikator dapat membutuhkan banyak bukti. Satu kolom `link_bukti` tidak cukup untuk model jangka panjang.

## 5. Fakta Utama dari Dokumen Laporan Hasil AMI/RTM

### 5.1 Format hasil audit

Laporan per program studi menggunakan struktur utama:

```text
Kriteria
| Temuan
| Akar Masalah
| Rekomendasi
```

Laporan juga menampilkan grafik/radar hasil audit berdasarkan kode kriteria dan nilai.

### 5.2 Program studi dan instrumen berbeda

Kode kriteria tidak seragam untuk semua program studi. Terdapat beberapa keluarga kode instrumen. Sistem tidak boleh mengasumsikan semua unit/program studi memakai paket instrumen yang identik.

Paket instrumen perlu memiliki:
- versi;
- lingkup penerapan;
- daftar pertanyaan;
- mapping ke indikator;
- rubrik;
- status draft/aktif/pensiun.

### 5.3 Hasil auditor

Auditor minimal perlu dapat mencatat:
- status ketercapaian;
- skor bila rubrik berlaku;
- validitas bukti;
- temuan;
- akar masalah awal;
- rekomendasi;
- jenis/severity temuan;
- catatan.

### 5.4 RTM bukan hanya file laporan

RTM harus menjadi workflow terstruktur:
- memilih temuan final;
- menyepakati akar masalah;
- menetapkan keputusan manajemen;
- membuat rencana aksi;
- menetapkan PIC;
- menetapkan prioritas;
- menetapkan target waktu;
- menerima bukti tindak lanjut;
- memverifikasi pelaksanaan;
- memverifikasi efektivitas;
- menutup atau mengembalikan tindakan.

Rekomendasi auditor tidak otomatis menjadi keputusan RTM.

### 5.5 Dokumen rapat

RTM dapat memerlukan:
- nomor rapat;
- agenda;
- tanggal/waktu;
- tempat;
- pimpinan;
- notulis;
- peserta;
- notulen;
- daftar hadir;
- status draft/final.

Kewajiban dokumen tersebut harus dikonfirmasi stakeholder sebelum dibuat mandatory.

## 6. Workflow Utama

### 6.1 Penetapan

1. LPMPI membuat versi SPMI.
2. Standar, pernyataan, indikator, target, lingkup, PIC, dan bukti dikelola dalam versi tersebut.
3. Versi melalui approval.
4. Versi aktif menjadi read-only.
5. Revisi dilakukan dengan versi baru.

### 6.2 Pelaksanaan

1. Periode/siklus audit memilih versi SPMI.
2. Paket instrumen dipilih sesuai objek audit.
3. Penugasan membuat snapshot standar/instrumen.
4. Auditee mengisi realisasi, penjelasan, dan bukti.
5. Auditee menyimpan draft lalu submit.

### 6.3 Evaluasi

1. Auditor memeriksa realisasi dan bukti.
2. Auditor memberi status ketercapaian dan skor bila relevan.
3. Auditor membuat temuan dan rekomendasi.
4. Auditor dapat meminta revisi.
5. Lead auditor melakukan finalisasi.
6. Sistem menghasilkan laporan hasil AMI.

### 6.4 Pengendalian

1. LPMPI membuat rapat RTM.
2. Temuan final dipilih.
3. RTM menetapkan keputusan dan rencana aksi.
4. PIC dan target waktu ditentukan.
5. RTM difinalisasi.
6. PIC mengirim progres dan bukti.
7. Verifier memeriksa pelaksanaan dan efektivitas.

### 6.5 Peningkatan

1. Temuan berulang dan tren dianalisis.
2. Usulan peningkatan standar/target dibuat.
3. Usulan direview.
4. Versi SPMI baru dibuat.
5. Histori lama dipertahankan.

## 7. Aturan Integritas dan Historis

- Data final tidak diedit langsung.
- Koreksi setelah finalisasi memakai amendment/revision workflow.
- Assignment menyimpan snapshot.
- Perubahan nama unit, jabatan, atau user tidak mengubah histori.
- Bukti tidak dihapus tanpa jejak.
- State transition dilakukan melalui service.
- Finalisasi harus transactional dan idempotent.
- Audit lama tidak dipaksa terhubung ke indikator baru bila mapping tidak pasti.

## 8. Keamanan Minimum

### Authentication dan session
- session ID diregenerasi setelah login;
- cookie Secure/HttpOnly/SameSite di production;
- logout menginvalidasi session;
- login throttling;
- akun nonaktif ditolak.

### Authorization
- capability check;
- object-level authorization;
- organization scope;
- ownership check;
- test IDOR untuk tugas, bukti, laporan, RTM, dan tindak lanjut.

### Input/output
- validasi server-side;
- CSRF untuk mutation;
- escaped output;
- rich text hanya dengan sanitizer allowlist;
- prepared query/query builder;
- cegah mass assignment.

### File
- private storage;
- random stored filename;
- allowlist extension;
- MIME validation dari isi;
- ukuran dibatasi;
- checksum;
- download melalui endpoint terotorisasi;
- attachment disposition;
- cegah path traversal;
- jangan otomatis mengambil URL bukti dari server tanpa mitigasi SSRF.

### Export
- cegah CSV/XLSX formula injection;
- jangan bocorkan path storage;
- temp file private;
- export dicatat.

### Audit trail
Catat aksi sensitif:
- login;
- perubahan role;
- aktivasi versi;
- penugasan;
- submit;
- finalisasi;
- perubahan PIC;
- upload/download sensitif;
- verifikasi;
- export.

## 9. Keputusan Stakeholder yang Belum Boleh Diasumsikan

1. Skor `1–4` atau `0–4`.
2. Definisi tiap skor.
3. Definisi formal OB/KTS.
4. Severity temuan.
5. Formula risk-based audit.
6. Bobot indikator.
7. Agregasi N/A.
8. Penandatangan/finalizer laporan.
9. Finalizer RTM.
10. Verifier tindak lanjut.
11. Retensi bukti.
12. Kebijakan penghapusan.
13. Kewajiban notulen dan daftar hadir.
14. Perubahan target di tengah periode.
15. Integrasi sistem eksternal.
16. Kebutuhan tanda tangan elektronik.

## 10. Batas Penggunaan oleh Coding Agent

Coding agent tidak boleh mengklaim telah membaca PDF sumber kecuali PDF memang diberikan pada workspace.

Coding agent harus menyebut:
- requirement berasal dari dokumen ini;
- struktur aktual berasal dari inspeksi repo;
- struktur baru adalah proposal teknis;
- gap harus diverifikasi sebelum migration/cutover.
