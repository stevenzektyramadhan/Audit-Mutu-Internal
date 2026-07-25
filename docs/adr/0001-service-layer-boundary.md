# ADR 0001: Batas Service Layer

- Status: **Accepted — target architecture; implementation pending**
- Tanggal: 2026-07-24
- Jenis: Arsitektur aplikasi
- Menggantikan: Tidak ada
- Terkait: [ADR 0005](0005-role-and-scope-authorization.md), [ADR 0006](0006-legacy-migration-strategy.md)

## Konteks

Aplikasi saat ini adalah monolit CodeIgniter 3. Tanggung jawab request, aturan status, akses data, dan sebagian otorisasi masih tersebar di controller dan model. Pada alur aktif, controller Auditee dan Auditor dapat memakai model jawaban secara langsung. Belum ada batas seragam untuk policy, storage, maupun report query.

Pola tersebut bekerja untuk fitur lama, tetapi berisiko menghasilkan:

- aturan yang berbeda untuk operasi yang sama;
- transaksi parsial ketika satu use case mengubah beberapa tabel;
- pemeriksaan otorisasi yang terlewat pada endpoint baru;
- controller atau model yang sulit diuji tanpa menjalankan seluruh aplikasi;
- alur legacy yang melewati aturan baru.

Refactor besar sekaligus tidak aman karena behavior lama masih harus dipertahankan oleh baseline smoke test.

## Keputusan

Gunakan pemisahan tanggung jawab berikut secara bertahap:

| Lapisan | Tanggung jawab |
|---|---|
| Controller | Menerima request, memvalidasi bentuk input dasar, memanggil satu use case, dan membentuk response/redirect. |
| Application service | Mengorkestrasi use case, transaksi, aturan state transition, audit event, serta pemanggilan policy/storage. |
| Policy/authorization | Menjawab apakah aktor boleh melakukan aksi terhadap objek dan scope tertentu. |
| Model/repository | Query dan persistensi data; tidak mengambil keputusan berdasarkan session atau tampilan. |
| Storage service | Validasi, penyimpanan, metadata, dan pengambilan file melalui abstraksi yang terotorisasi. |
| Report/read model | Query baca khusus laporan/dashboard tanpa menjadi jalur mutasi domain. |

Aturan tambahan:

1. Arah dependensi bergerak dari controller ke service, kemudian ke policy/model/storage. Model tidak memanggil controller atau membaca session.
2. Semua mutasi use case lintas tabel menggunakan satu batas transaksi di service.
3. State transition penting hanya didefinisikan pada satu service domain dan diperiksa ulang di server.
4. Endpoint legacy boleh memakai adapter sementara, tetapi tidak boleh menjadi jalan pintas permanen.
5. Refactor dilakukan per use case; route lama dipertahankan sampai pengganti dan regression test tersedia.
6. Tidak diwajibkan membuat framework abstraksi generik. Service dibentuk berdasarkan bahasa domain, misalnya penugasan audit, finalisasi, dan tindak lanjut.

## Alternatif yang dipertimbangkan

### Mempertahankan controller dan model gemuk

Ditolak sebagai arah target karena memperbesar duplikasi aturan, transaksi parsial, dan celah otorisasi.

### Big-bang rewrite ke arsitektur atau framework baru

Ditolak karena risiko kehilangan behavior, data historis, dan kompatibilitas operasional terlalu tinggi.

### Generic repository/service untuk semua tabel

Ditolak karena abstraksi CRUD seragam dapat menyembunyikan aturan domain yang berbeda. Abstraksi hanya dibuat ketika ada kebutuhan yang berulang dan terbukti.

### Event sourcing penuh

Tidak dipilih untuk tahap ini karena kompleksitas operasionalnya tidak sebanding dengan kebutuhan saat ini. Audit event tetap wajib untuk operasi kritis.

## Konsekuensi

### Positif

- Aturan use case memiliki satu tempat utama.
- Policy dan transaksi dapat diuji tanpa ketergantungan pada tampilan.
- Migrasi alur lama dapat dilakukan per bagian.
- Contributor memiliki batas yang sama ketika menambah fitur.

### Negatif dan biaya

- Selama transisi, alur lama dan baru dapat hidup berdampingan.
- Akan ada adapter atau kode penghubung sementara.
- Sebagian model lama perlu dipecah tanpa mengubah kontrak eksternal secara tiba-tiba.
- Test service dan negative authorization menjadi pekerjaan wajib, bukan opsional.

## Risiko dan mitigasi

| Risiko | Mitigasi |
|---|---|
| Service berubah menjadi kelas besar baru | Pecah berdasarkan use case/domain, bukan berdasarkan seluruh modul. |
| Aturan tetap diduplikasi di controller lama | Tandai adapter legacy, tambahkan characterization test, dan migrasikan satu entry point pada satu waktu. |
| Abstraksi dibuat terlalu dini | Tambahkan interface hanya pada boundary yang nyata: policy, storage, atau integrasi eksternal. |
| Transaksi bersarang/terputus | Service terluar menjadi pemilik transaksi dan model tidak melakukan commit mandiri. |
| Endpoint lama melewati service | Inventarisasi route, negative test, lalu nonaktifkan route hanya setelah cutover sesuai ADR 0006. |

## Status dan pemicu peninjauan

Keputusan ini diterima sebagai target, tetapi belum menyatakan struktur aplikasi saat ini sudah patuh. Tinjau ADR bila framework utama diganti, aplikasi dipecah menjadi beberapa service deployable, atau batas transaksi tidak lagi dapat dimiliki oleh application service.
