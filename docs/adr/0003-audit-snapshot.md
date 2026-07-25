# ADR 0003: Snapshot Acuan Audit

- Status: **Accepted — target architecture; implementation pending**
- Tanggal: 2026-07-24
- Jenis: Integritas histori audit
- Menggantikan: Tidak ada
- Terkait: [ADR 0002](0002-spmi-versioning.md), [ADR 0006](0006-legacy-migration-strategy.md)

## Konteks

Penugasan, jawaban, dan penilaian pada sistem saat ini masih merujuk pada master pertanyaan yang dapat berubah. Jika nama standar, teks pertanyaan, target, rubrik, atau kebutuhan bukti diedit setelah audit dimulai, tampilan audit lama dapat berubah tanpa ada tindakan pada audit tersebut.

Referensi ke ID versi saja belum cukup apabila detail turunannya masih mutable atau suatu baris kemudian tidak tersedia. Sebaliknya, menyalin seluruh graph database tanpa batas membuat data sulit dikelola. Diperlukan snapshot yang terdefinisi pada boundary penugasan audit.

## Keputusan

1. Ketika penugasan audit diterbitkan/dibuka untuk dikerjakan, sistem membekukan snapshot acuan audit dalam satu transaksi.
2. Snapshot minimal memuat:
   - identitas dan revisi versi SPMI;
   - paket serta versi instrumen;
   - scope/unit dan periode audit;
   - kode/judul standar serta pernyataan terkait;
   - indikator, tipe pengukuran, baseline, dan target yang berlaku;
   - teks pertanyaan, urutan, rubrik yang berlaku, dan kebutuhan bukti;
   - ID sumber untuk provenance, nilai salinan yang diperlukan untuk histori, dan checksum/manifes snapshot.
3. Operasi audit setelah penerbitan membaca snapshot, bukan master aktif.
4. Jawaban auditee, bukti, verifikasi auditor, temuan, dan laporan mengacu pada item snapshot yang stabil.
5. Perubahan master setelah snapshot dibuat hanya memengaruhi penugasan baru.
6. Koreksi penugasan yang belum dimulai dapat membuat ulang snapshot melalui aksi terotorisasi dan tercatat. Setelah ada respons atau penilaian, koreksi material harus menjadi amendment/revisi yang tidak menimpa histori.
7. Snapshot bersifat immutable, kecuali metadata teknis non-domain yang secara eksplisit diizinkan.
8. Pembuatan, penggantian yang diizinkan, dan verifikasi snapshot menghasilkan audit event.

Waktu pasti pembekuan (`assignment created`, `published`, atau `opened`) harus dipilih dalam implementasi lifecycle penugasan. Default arsitekturalnya adalah saat penugasan pertama kali menjadi tersedia bagi auditee, dan tidak boleh lebih lambat dari respons pertama.

## Alternatif yang dipertimbangkan

### Selalu membaca master terbaru

Ditolak karena mengubah arti audit historis dan laporan yang sudah final.

### Hanya menyimpan foreign key ke versi SPMI

Tidak cukup. Detail instrumen, target, rubrik, dan label terkait juga harus stabil atau tersalin.

### Menyalin semua tabel beserta seluruh kolom

Ditolak sebagai aturan umum karena memasukkan data yang tidak relevan dan memperbesar coupling. Snapshot memakai kontrak data yang eksplisit.

### Satu blob JSON tanpa relasi/provenance

Ditolak sebagai satu-satunya representasi karena menyulitkan query, constraint, dan pelacakan sumber. JSON/manifes boleh dipakai sebagai pelengkap checksum, bukan pengganti seluruh model.

### Event sourcing penuh

Tidak dipilih untuk tahap ini karena snapshot immutable lebih sederhana untuk kebutuhan baca audit.

## Konsekuensi

### Positif

- Audit dan laporan lama tidak berubah ketika master direvisi.
- Sumber setiap item audit dapat ditelusuri.
- Reproduksi laporan dan investigasi integritas menjadi mungkin.
- Cutover dari struktur lama dapat diverifikasi per penugasan.

### Negatif dan biaya

- Data referensi tertentu disalin secara sengaja.
- Pembuatan penugasan lebih kompleks dan perlu transaksi.
- Perubahan sebelum audit dimulai memerlukan lifecycle yang jelas.
- Query laporan harus memilih item snapshot, bukan langsung memakai master.

## Risiko dan mitigasi

| Risiko | Mitigasi |
|---|---|
| Snapshot tidak lengkap | Definisikan schema/manifest wajib dan gagalkan penerbitan jika validasi gagal. |
| Snapshot dan master tercampur dalam satu laporan | Gunakan ID item snapshot pada semua data audit dan tambahkan regression test. |
| Snapshot dibuat dua kali oleh request berulang | Gunakan idempotency/unique constraint serta locking transaksi. |
| Ukuran data tumbuh | Salin hanya field domain yang dibutuhkan; ukur pertumbuhan dan terapkan index yang tepat. |
| Amendment dipakai untuk mengubah histori diam-diam | Immutable record, alasan wajib, capability khusus, audit event, dan jejak versi amendment. |

## Status dan pemicu peninjauan

Keputusan ini diterima sebagai target; penugasan legacy belum dianggap memiliki snapshot yang memenuhi kontrak ini. Tinjau bila lifecycle penugasan menetapkan boundary pembekuan berbeda atau regulasi mengharuskan artefak snapshot bertanda tangan digital.
