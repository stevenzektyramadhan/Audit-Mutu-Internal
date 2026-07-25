# ADR 0004: Penyimpanan File Privat

- Status: **Accepted — target architecture; implementation pending**
- Tanggal: 2026-07-24
- Jenis: Keamanan dan penyimpanan
- Menggantikan: Tidak ada
- Terkait: [ADR 0001](0001-service-layer-boundary.md), [ADR 0005](0005-role-and-scope-authorization.md), [decision register](../product/decision-register.md)

## Konteks

Sistem menyimpan instrumen, dokumen penetapan, bukti audit, notulen, daftar hadir, dan dokumen tindak lanjut. Sebagian besar artefak dapat memuat data internal atau sensitif dan tidak boleh dapat diunduh hanya dengan mengetahui URL.

Repository sudah mempunyai helper lokasi storage privat untuk beberapa kategori dan fallback legacy yang dilindungi `.htaccess`. Namun pola penyimpanan belum seragam: metadata checksum/retensi belum lengkap, file legacy masih dapat berada di bawah document root, dan aturan download harus tetap dipastikan pada level objek. Perlindungan konfigurasi web server saja tidak cukup dan dapat berbeda antara Linux/Docker, Apache, Nginx, dan Laragon/Windows.

## Keputusan

1. Semua bukti, instrumen, dokumen penetapan, notulen, daftar hadir, laporan nonpublik, dan dokumen tindak lanjut bersifat privat secara default.
2. File privat baru disimpan di luar document root melalui storage service.
3. Nama fisik menggunakan UUID atau nama acak yang tidak berasal dari nama file pengguna.
4. Database menyimpan metadata minimal:
   - storage disk/provider dan key/path relatif;
   - nama asli yang telah diperlakukan sebagai data;
   - MIME terdeteksi, ekstensi, ukuran, dan checksum;
   - kategori/konteks domain serta ID objek pemilik;
   - pengunggah dan waktu unggah;
   - status retensi, quarantine, dan penghapusan bila kemudian berlaku.
5. Upload memakai allowlist kategori dan tipe, batas ukuran, deteksi tipe dari isi, nama aman, serta penolakan file yang tidak valid. Ekstensi dan header dari browser tidak dipercaya.
6. File hanya diakses melalui controller/service download yang:
   - mengautentikasi pengguna;
   - memeriksa capability dan scope terhadap objek;
   - mengambil metadata dari database;
   - mengirim header aman dan nama unduh yang di-escape;
   - mencatat akses sensitif sesuai kebijakan audit.
7. Path atau storage key dari request tidak boleh dipakai langsung untuk membaca filesystem.
8. Logo atau aset publik hanya boleh menjadi pengecualian yang dinyatakan eksplisit dan tidak boleh berbagi jalur dengan dokumen privat.
9. File legacy dimigrasikan bertahap. Fallback lama hanya menjadi adapter terbatas waktu, tetap melalui otorisasi, dan dihapus setelah rekonsiliasi.
10. File tidak dihapus permanen sampai kebijakan retensi, legal hold, backup, dan persetujuan stakeholder ditetapkan.

Keputusan tentang lama retensi, penghapusan file lama, antivirus berbayar, preview/fetch URL oleh server, dan tanda tangan digital tetap terbuka di decision register.

## Alternatif yang dipertimbangkan

### Menyimpan file di folder publik dengan nama sulit ditebak

Ditolak. URL acak bukan kontrol otorisasi dan dapat bocor dari log, referer, atau pengguna lain.

### Mengandalkan `.htaccess` saja

Ditolak sebagai kontrol utama karena bergantung pada web server dan konfigurasi deployment. Ia hanya boleh menjadi defense-in-depth selama migrasi.

### Menyimpan seluruh file sebagai BLOB database

Tidak dipilih sebagai default karena beban database, backup, dan streaming. Metadata dan relasi tetap di database; objek file berada pada storage privat.

### Menyimpan URL/path absolut pada record domain

Ditolak karena membocorkan detail deployment dan menyulitkan perpindahan provider. Simpan disk/provider dan key relatif melalui storage service.

## Konsekuensi

### Positif

- Mengetahui URL atau nama file tidak cukup untuk mengakses dokumen.
- Deploy Windows/Laragon dan Linux/Docker dapat memakai kontrak storage yang sama.
- Integritas file dapat diverifikasi melalui checksum.
- Migrasi ke object storage di masa depan tidak mengubah aturan domain.

### Negatif dan biaya

- Semua download menambah pemeriksaan aplikasi.
- File lama harus diinventarisasi, dicocokkan, dan dipindahkan.
- Backup harus mencakup database metadata dan storage secara konsisten.
- Preview file memerlukan endpoint terotorisasi, bukan URL langsung.

## Risiko dan mitigasi

| Risiko | Mitigasi |
|---|---|
| Path traversal atau arbitrary file read | Gunakan storage key dari database, canonicalization internal, dan jangan menerima path mentah. |
| File berbahaya dengan ekstensi aman | Deteksi tipe dari isi, allowlist, quarantine, dan integrasikan scanner bila kebijakannya disetujui. |
| Metadata database dan file tidak sinkron | Operasi terorkestrasi, status upload, cleanup terjadwal, checksum, dan laporan orphan. |
| File legacy tetap dapat diakses langsung | Inventarisasi document root, aturan deny sementara, negative test, lalu migrasi sesuai ADR 0006. |
| Penghapusan melanggar retensi | Default tidak menghapus; gunakan status retensi/legal hold dan keputusan stakeholder. |
| Download objek milik unit lain | Terapkan policy objek sesuai ADR 0005 dan uji direct-object-reference. |

## Status dan pemicu peninjauan

Keputusan ini diterima sebagai target. Keberadaan helper privat saat ini adalah fondasi, bukan bukti seluruh kategori dan file legacy sudah patuh. Tinjau bila storage provider berubah, regulasi retensi ditetapkan, atau sistem mulai menerima file dengan klasifikasi keamanan baru.
