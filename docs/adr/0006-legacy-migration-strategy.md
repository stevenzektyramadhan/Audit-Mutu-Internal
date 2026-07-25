# ADR 0006: Strategi Migrasi Legacy

- Status: **Accepted — target architecture; implementation pending**
- Tanggal: 2026-07-24
- Jenis: Evolusi sistem dan data
- Menggantikan: Tidak ada
- Terkait: [ADR 0001](0001-service-layer-boundary.md), [ADR 0002](0002-spmi-versioning.md), [ADR 0003](0003-audit-snapshot.md), [ADR 0004](0004-private-file-storage.md), [ADR 0005](0005-role-and-scope-authorization.md)

## Konteks

Repository mempunyai alur aktif dan legacy yang sebagian tumpang tindih, migrasi SQL manual, konfigurasi migration framework yang belum menjadi jalur utama, serta data historis yang harus dipertahankan. Ada risiko route lama melewati aturan baru, sementara penghapusan langsung dapat merusak data dan operasional.

Target domain—versioning SPMI, snapshot audit, policy objek, file privat, RTM terstruktur—berbeda cukup jauh dari schema saat ini. Karena behavior lama sudah dibekukan dengan smoke test M0-03, perubahan harus dilakukan secara inkremental, dapat dibandingkan, dan dapat dipulihkan.

## Keputusan

Gunakan pola **expand -> migrate -> verify -> cut over -> retire**:

1. **Inventory dan mapping**
   - petakan route, controller, model, tabel, kolom, status, file, dan consumer laporan;
   - klasifikasikan mapping sebagai direct, transformed, ambiguous, obsolete, atau manual review;
   - catat kualitas data dan constraint/index yang hilang.
2. **Expand**
   - tambahkan schema/layanan target secara backward-compatible;
   - gunakan migration bernomor unik dan ledger yang dapat diperiksa;
   - jangan menghapus kolom, tabel, route, atau file lama pada tahap ini.
3. **Migrate**
   - backfill dalam batch kecil, idempotent, dapat dilanjutkan, dan mencatat hasil;
   - simpan legacy source ID/provenance;
   - data ambigu masuk antrean manual review, bukan ditebak;
   - adapter/dual-read diperbolehkan sementara dan harus memiliki batas waktu serta metrik.
4. **Verify**
   - bandingkan count, relasi, checksum, agregat, sample terkontrol, dan hasil laporan;
   - jalankan smoke/regression serta negative authorization test;
   - lakukan dry run, backup, dan uji rollback/restore sebelum cutover produksi.
5. **Cut over**
   - pindahkan satu use case/consumer pada satu waktu ke jalur target;
   - hentikan write legacy untuk scope yang sudah beralih;
   - pantau error, rekonsiliasi, dan audit log.
6. **Retire**
   - jadikan komponen legacy read-only lebih dahulu;
   - nonaktifkan route setelah endpoint pengganti, migrasi data, test, observasi, dan rollback plan terpenuhi;
   - hapus data/file hanya setelah keputusan retensi dan persetujuan eksplisit.

Dual-write tidak menjadi default karena kegagalan parsial dapat membuat dua sumber kebenaran. Jika benar-benar diperlukan, desain sinkronisasi, idempotency, rekonsiliasi, dan cara pemulihannya harus didokumentasikan lebih dahulu.

Setiap tahap migrasi wajib menyatakan sumber kebenaran yang aktif. Tidak boleh ada dua jalur write permanen untuk domain yang sama.

## Alternatif yang dipertimbangkan

### Big-bang rewrite dan satu kali migrasi

Ditolak karena risiko downtime, kehilangan behavior, serta kegagalan migrasi data terlalu tinggi.

### Mempertahankan dua sistem permanen

Ditolak karena menciptakan dua sumber kebenaran dan biaya sinkronisasi tanpa akhir.

### Langsung mengubah tabel/kolom lama in-place

Ditolak untuk perubahan domain besar karena rollback sulit dan consumer lama dapat rusak.

### Menghapus data yang tidak cocok

Ditolak. Data ambigu atau tampak usang tetap dipertahankan dan ditandai untuk review sampai ada dasar retensi/penghapusan.

### Dual-write untuk seluruh masa transisi

Tidak dipilih sebagai default. Adapter baca, backfill, dan cutover bertahap lebih mudah direkonsiliasi.

## Konsekuensi

### Positif

- Risiko perubahan dapat dibatasi per use case.
- Data lama tetap dapat ditelusuri dan dibandingkan.
- Windows/Laragon dan Linux/Docker dapat menjalankan migrasi yang sama melalui kontrak environment yang eksplisit.
- Rollback dan cutover memiliki bukti, bukan asumsi.

### Negatif dan biaya

- Masa transisi lebih panjang dan sementara menambah schema/adapter.
- Diperlukan script rekonsiliasi, metrik, dan manual review.
- Operasional harus mengetahui sumber kebenaran pada setiap fase.
- Penyimpanan bertambah sampai retirement selesai.

## Risiko dan mitigasi

| Risiko | Mitigasi |
|---|---|
| Migration dijalankan dua kali | Migration ledger, nomor unik, idempotency, dan precondition. |
| Backfill berhenti di tengah | Batch kecil, checkpoint, retry aman, dan transaksi per batch. |
| Dua sumber kebenaran menyimpang | Satu write authority per fase, rekonsiliasi, dan adapter sementara yang terukur. |
| Route lama melewati keamanan baru | Inventory route, deny/negative test, observability, lalu disable setelah cutover. |
| Data ambigu dipetakan keliru | Kategori `manual review`, provenance, dan larangan menebak. |
| Rollback hanya ada di dokumen | Uji restore/rollback pada salinan terisolasi sebelum produksi. |
| File hilang saat pemindahan | Copy lalu checksum/authorize test; jangan hapus sumber sebelum verifikasi dan retensi disetujui. |

## Status dan pemicu peninjauan

Keputusan ini diterima sebagai strategi seluruh migrasi legacy. Ia tidak memberi izin untuk menghapus data, file, route, atau tabel. Tinjau jika terdapat jendela downtime resmi yang mengubah metode cutover, atau hasil inventory menunjukkan migration pattern tertentu tidak aman.
