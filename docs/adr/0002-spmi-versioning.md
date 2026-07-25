# ADR 0002: Versioning Dokumen SPMI

- Status: **Accepted — target architecture; implementation pending**
- Tanggal: 2026-07-24
- Jenis: Model domain dan data
- Menggantikan: Tidak ada
- Terkait: [ADR 0003](0003-audit-snapshot.md), [ADR 0004](0004-private-file-storage.md), [decision register](../product/decision-register.md)

## Konteks

Dokumen sumber SPMI memiliki identitas, nomor revisi, tanggal berlaku, dan isi yang dapat berubah. Audit historis harus tetap terikat pada acuan yang digunakan ketika audit dibuat. Pada sistem saat ini, data standar dan pertanyaan masih bersifat mutable dan belum memiliki lifecycle versi yang utuh. Perubahan atau penghapusan master berpotensi mengubah makna riwayat audit.

Pernyataan standar, indikator, target tahunan, kebutuhan bukti, dan pertanyaan audit juga merupakan konsep yang berbeda. Menyatukannya menjadi teks yang ditimpa akan menghilangkan provenance dan kemampuan audit.

## Keputusan

1. Dokumen SPMI direpresentasikan sebagai identitas dokumen yang memiliki satu atau lebih versi.
2. Versi minimal menyimpan kode dokumen, judul, nomor revisi, tanggal berlaku, tanggal berakhir opsional, status, sumber file privat, checksum, pembuat, penyetuju, dan waktu perubahan status.
3. Lifecycle versi adalah `draft -> review -> approved -> active -> retired`.
4. Versi `active` tidak boleh diubah isinya. Koreksi material dibuat sebagai versi/revisi baru.
5. Versi lama tidak dihapus ketika versi baru diaktifkan; status dan masa berlakunya dipertahankan.
6. Pernyataan, indikator, target, scope, kebutuhan bukti, dan paket instrumen memiliki relasi eksplisit ke versi yang menaunginya.
7. Target tahunan disimpan per indikator dan tahun akademik; pergantian tahun tidak menimpa target lama.
8. Secara default hanya ada satu versi aktif untuk identitas, scope, dan rentang waktu yang sama. Koeksistensi hanya boleh diterapkan setelah aturan bisnisnya disetujui dan dicatat.
9. Aktivasi dan retirement harus atomik, terotorisasi, dan menghasilkan audit event.
10. Penugasan audit tidak membaca master versi aktif secara dinamis; ia memakai snapshot sesuai ADR 0003.

Detail rubrik skor, bobot indikator, perubahan target di tengah tahun, dan variasi instrumen antarunit tetap mengikuti keputusan stakeholder di decision register.

## Alternatif yang dipertimbangkan

### Menimpa baris standar yang sedang digunakan

Ditolak karena audit lama akan terlihat memakai isi terbaru dan provenance tidak dapat dibuktikan.

### Menyalin seluruh database untuk setiap revisi

Ditolak karena boros, sulit dibandingkan, dan mengaburkan relasi antarversi.

### Menyimpan versi hanya sebagai file PDF

Ditolak karena elemen yang perlu dicari, difilter, dipetakan, dan diaudit harus menjadi data terstruktur. PDF tetap disimpan sebagai sumber/provenance.

### Event sourcing penuh untuk seluruh master SPMI

Tidak dipilih pada tahap ini. Baris versi immutable dan audit event memberikan histori yang dibutuhkan dengan kompleksitas lebih rendah.

## Konsekuensi

### Positif

- Audit historis dapat ditelusuri ke versi acuan.
- Revisi dapat dibandingkan tanpa merusak data lama.
- Aktivasi menjadi operasi eksplisit dan dapat diaudit.
- Target serta indikator tidak kehilangan histori tahunan.

### Negatif dan biaya

- Query harus memilih versi dan effective date dengan benar.
- UI perlu membedakan edit draft, membuat revisi, aktivasi, dan retirement.
- Backfill data lama membutuhkan penetapan versi awal dan provenance migrasi.
- Penyimpanan bertambah karena versi lama dipertahankan.

## Risiko dan mitigasi

| Risiko | Mitigasi |
|---|---|
| Dua versi aktif tumpang tindih tanpa sengaja | Constraint/locking aplikasi, transaksi aktivasi, dan pemeriksaan overlap. |
| Versi aktif diedit melalui endpoint legacy | Jadikan immutable di service dan database sejauh aman; tambahkan regression test dan audit. |
| Nomor revisi tidak unik atau tidak berurutan | Definisikan uniqueness per identitas dokumen dan validasi saat membuat revisi. |
| Data lama tidak memiliki metadata versi | Buat versi migrasi eksplisit, simpan sumber migrasi, dan masukkan kasus ambigu ke manual review. |
| Keputusan bisnis rubrik ikut di-hardcode | Gunakan status `Needs Decision`; jangan mengaktifkan aturan permanen sebelum keputusan stakeholder. |

## Status dan pemicu peninjauan

Keputusan ini diterima sebagai model target; schema saat ini belum dianggap memenuhi keputusan. Tinjau bila regulasi menetapkan model koeksistensi versi yang berbeda, aturan koreksi versi aktif, atau kebutuhan tanda tangan/sertifikasi digital.
