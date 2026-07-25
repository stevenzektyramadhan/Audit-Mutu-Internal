# Product and Architecture Decision Register

- Dibuat: 2026-07-24
- Status register: Aktif
- Pemilik keputusan bisnis: **Belum ditetapkan**
- Sumber pertanyaan: `BUSINESS_REQUIREMENTS_SPMI_AMI_RTM.md` dan `CODEX_IMPLEMENTATION_PLAN_SPMI_AMI_RTM.md`

Register ini membedakan keputusan yang sudah diterima dari pertanyaan yang masih terbuka. Nilai atau perilaku aplikasi lama adalah bukti kondisi saat ini, bukan otomatis keputusan bisnis.

## Aturan penggunaan

Urutan sumber kebenaran untuk implementasi:

1. Keputusan stakeholder berstatus `Accepted` dalam register ini.
2. `BUSINESS_REQUIREMENTS_SPMI_AMI_RTM.md`.
3. Keadaan aktual repository dan database yang sudah diverifikasi.
4. `CODEX_IMPLEMENTATION_PLAN_SPMI_AMI_RTM.md`.
5. Asumsi agent yang diberi label sementara dan tidak mengubah aturan permanen.

Status yang digunakan:

| Status | Arti |
|---|---|
| Needs Decision | Belum ada jawaban stakeholder; implementasi permanen dilarang mengasumsikan jawaban. |
| Proposed | Ada usulan jawaban, tetapi belum disetujui pemilik keputusan. |
| Accepted | Sudah disetujui dan boleh menjadi sumber aturan permanen. |
| Rejected | Usulan ditolak; alasan tetap disimpan. |
| Superseded | Digantikan oleh keputusan baru yang disebutkan. |

Ketika sebuah pertanyaan dijawab, catat jawaban yang terukur, pemilik/otoritas yang menyetujui, tanggal keputusan, bukti rapat/dokumen, dampak data atau migrasi, task terkait, dan siapa yang mengubah register. Jangan hanya mengganti status.

## Keputusan arsitektur

| ID | Keputusan | Status | Catatan |
|---|---|---|---|
| ADR-0001 | [Batas service layer](../adr/0001-service-layer-boundary.md) | Accepted — implementation pending | Controller, service, policy, model, storage, dan report mempunyai tanggung jawab berbeda. |
| ADR-0002 | [Versioning SPMI](../adr/0002-spmi-versioning.md) | Accepted — implementation pending | Versi aktif immutable; revisi baru tidak menimpa histori. |
| ADR-0003 | [Snapshot audit](../adr/0003-audit-snapshot.md) | Accepted — implementation pending | Penugasan membaca snapshot yang stabil, bukan master terbaru. |
| ADR-0004 | [Private file storage](../adr/0004-private-file-storage.md) | Accepted — implementation pending | Dokumen privat disimpan di luar document root dan diakses melalui policy. |
| ADR-0005 | [Role and scope authorization](../adr/0005-role-and-scope-authorization.md) | Accepted — implementation pending | Capability, scope, ownership, dan state objek menentukan izin. |
| ADR-0006 | [Legacy migration strategy](../adr/0006-legacy-migration-strategy.md) | Accepted — implementation pending | Migrasi memakai expand, migrate, verify, cut over, lalu retire. |

## Pertanyaan keputusan bisnis

Semua entri berikut sengaja tetap `Needs Decision`. Kolom “kondisi/batas saat ini” membantu stakeholder memahami dampak, tetapi bukan jawaban.

| ID | Pertanyaan yang harus diputuskan | Kondisi/batas saat ini | Guard implementasi selama terbuka | Status |
|---|---|---|---|---|
| BIZ-001 | Apakah rentang skor audit yang sah `1–4` atau `0–4`? | Kode lama memvalidasi integer `1–4`; hal itu belum dibuktikan sebagai keputusan bisnis. | Pertahankan kompatibilitas lama, tetapi jangan menjadikannya constraint target permanen atau memigrasikan nilai dengan asumsi skala. | Needs Decision |
| BIZ-002 | Apa definisi/rubrik skor 1, 2, 3, dan 4, serta skor 0 bila dipilih? | Label presentasi lama belum menjadi rubrik formal yang disetujui. | Jangan menghitung status kepatuhan atau rekomendasi otomatis hanya dari angka tanpa rubrik yang diterima. | Needs Decision |
| BIZ-003 | Apa arti formal OB dan KTS? | Schema lama menyediakan enum `ob` dan `kts`, tetapi definisi formal tidak tercatat. | Perlakukan nilai lama sebagai kode legacy; jangan mengarang ekspansi, threshold, atau akibat workflow. | Needs Decision |
| BIZ-004 | Apakah KTS mempunyai severity minor/major atau klasifikasi lain? | Model lama hanya membedakan `ob` dan `kts`. | Jangan menambah severity wajib, SLA, atau eskalasi otomatis sebelum taksonomi disetujui. | Needs Decision |
| BIZ-005 | Bagaimana formula risk-based audit? | Belum ada formula yang menjadi sumber kebenaran. | Jangan mengurutkan prioritas audit secara otomatis berdasarkan formula buatan agent. | Needs Decision |
| BIZ-006 | Apakah indikator berbobot; jika ya, siapa menetapkan bobot dan bagaimana masa berlakunya? | Kebutuhan indikator ada, tetapi aturan bobot belum final. | Simpan desain agar bobot dapat ditambahkan kemudian; jangan hard-code bobot sama atau bobot tertentu sebagai aturan bisnis. | Needs Decision |
| BIZ-007 | Bagaimana agregasi ketika indikator berstatus N/A? | Aturan agregasi/denominator belum ditetapkan. | Jangan menghasilkan skor agregat final yang memperlakukan N/A sebagai nol atau mengeluarkannya tanpa keputusan. | Needs Decision |
| BIZ-008 | Siapa yang boleh meninjau, menandatangani, dan memfinalisasi laporan AMI? | Requirement menyebut lead auditor pada alur target; matrix capability dan otoritas final belum disetujui. | Finalizer harus configurable/capability-based; jangan menyamakan finalisasi penilaian lama dengan persetujuan laporan target. | Needs Decision |
| BIZ-009 | Siapa yang boleh meninjau, menandatangani, dan memfinalisasi RTM? | RTM terstruktur belum tersedia pada sistem lama. | Jangan menetapkan role finalizer atau state final permanen sebelum otoritas dan separation of duties diputuskan. | Needs Decision |
| BIZ-010 | Apakah auditor dapat menjadi verifier tindak lanjut dari temuan yang diauditnya? | Belum ada aturan conflict of interest/separation of duties final. | Modelkan verifier sebagai assignment terpisah dan jangan otomatis menunjuk auditor yang sama. | Needs Decision |
| BIZ-011 | Berapa lama bukti dan artefak audit harus disimpan untuk tiap klasifikasi? | Belum ada jadwal retensi, legal hold, atau trigger awal perhitungan. | Default aman adalah mempertahankan file; jangan menjalankan purge permanen. | Needs Decision |
| BIZ-012 | Apakah file lama boleh dihapus, kapan, dan siapa yang menyetujui? | File legacy masih diperlukan untuk histori dan belum seluruhnya termigrasi/terverifikasi. | Tidak ada penghapusan permanen sebelum retensi, backup/restore, rekonsiliasi, dan approval disetujui. | Needs Decision |
| BIZ-013 | Apakah notulen dan daftar hadir wajib untuk finalisasi RTM? | Requirement mengakui keduanya sebagai artefak, tetapi kewajibannya belum ditetapkan. | Dukung sebagai tipe bukti, tetapi jangan menjadikannya blocker finalisasi permanen sebelum keputusan. | Needs Decision |
| BIZ-014 | Apakah satu RTM dapat membahas temuan dari banyak periode audit? | Kardinalitas RTM terhadap periode belum final. | Jangan membuat constraint satu-periode atau multi-periode yang sulit dimigrasikan sebelum diputuskan. | Needs Decision |
| BIZ-015 | Apakah satu temuan dapat dibahas pada lebih dari satu RTM? | Kardinalitas temuan terhadap RTM belum final. | Pertahankan opsi relasi many-to-many pada desain; jangan menggandakan temuan untuk mensimulasikan keputusan. | Needs Decision |
| BIZ-016 | Apakah target indikator dapat diubah di tengah tahun akademik; jika ya, bagaimana approval dan dampaknya pada audit berjalan? | Histori target wajib dipertahankan; mekanisme amendment belum diputuskan. | Jangan menimpa target. Audit berjalan tetap memakai snapshot sampai aturan amendment disetujui. | Needs Decision |
| BIZ-017 | Apakah tiap unit/jenis objek dapat mempunyai paket instrumen berbeda, dan bagaimana aturan pemilihannya? | Requirement melarang asumsi semua program studi memakai instrumen identik dan mengharuskan paket dipilih sesuai objek audit. | Arsitektur harus mendukung variasi scope; algoritma pemilihan/fallback belum boleh diasumsikan. | Needs Decision |
| BIZ-018 | Apakah server boleh mengambil atau menampilkan preview bukti dari URL eksternal? | Sistem lama menerima URL bukti; URL eksternal dapat berubah dan server-side fetch menambah risiko SSRF serta privasi. | Simpan URL sebagai data dan validasi format; jangan melakukan server-side fetch/preview sampai allowlist, keamanan jaringan, timeout, ukuran, dan audit akses diputuskan. | Needs Decision |
| BIZ-019 | Apakah tanda tangan elektronik/digital dibutuhkan, untuk artefak apa, dan pada tingkat jaminan apa? | Belum ada keputusan jenis tanda tangan atau penyedia sertifikat. | Jangan mengklaim checkbox/nama pengguna sebagai tanda tangan digital tersertifikasi dan jangan memilih vendor. | Needs Decision |
| BIZ-020 | Apakah perlu integrasi dengan SIAKAD, sistem keuangan, LPPMPP, atau sistem eksternal lain; data apa dan siapa sumber kebenarannya? | Belum ada kontrak integrasi, pemilik data, SLA, maupun mekanisme identitas. | Jangan membangun konektor atau menjadikan data eksternal authoritative tanpa kontrak, klasifikasi data, dan keputusan stakeholder. | Needs Decision |

## Format pencatatan jawaban

Gunakan format berikut di bawah entri terkait atau pada tabel riwayat:

```text
Decision ID:
Status:
Jawaban yang disetujui:
Alasan:
Pemilik/otoritas keputusan:
Tanggal keputusan:
Bukti/rujukan:
Dampak schema/data/workflow/security:
ADR dan task terdampak:
Dicatat oleh:
```

## Riwayat perubahan keputusan

| Tanggal | ID | Perubahan | Otoritas/bukti |
|---|---|---|---|
| 2026-07-24 | ADR-0001–ADR-0006 | Enam arah arsitektur awal dicatat sebagai accepted target dengan implementasi masih pending. | Backlog M0-04 dan dokumen requirement repository. |
| 2026-07-24 | BIZ-001–BIZ-020 | Pertanyaan stakeholder awal dicatat tanpa membuat jawaban asumtif. | Daftar keputusan terbuka pada requirement dan implementation plan. |
