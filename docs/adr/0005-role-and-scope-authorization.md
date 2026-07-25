# ADR 0005: Otorisasi Role, Capability, dan Scope

- Status: **Accepted — implemented through M2-03 role-capability and direct scope matrix**
- Tanggal: 2026-07-24
- Jenis: Keamanan aplikasi
- Menggantikan: Tidak ada
- Terkait: [ADR 0001](0001-service-layer-boundary.md), [ADR 0004](0004-private-file-storage.md), [decision register](../product/decision-register.md)

## Konteks

Sistem mengenal role `super_admin`, `admin_lpmpi`, `auditor`, dan `auditee`.
M1-04 menambahkan matriks capability dan policy objek/state seragam. M2-02
menambahkan membership langsung user/unit/jabatan yang bertanggal. Model
multi-role capability, lead Auditor, PIC, verifier, finalizer, dan aturan
pewarisan scope organisasi belum tersedia. Role saja tetap tidak cukup untuk
menjawab izin pada objek target tersebut.

ID pada URL/request tidak dapat dipercaya. Menyembunyikan tombol juga bukan kontrol keamanan. Kebutuhan target mencakup beberapa jenis unit, masa berlaku penugasan, kemungkinan satu pengguna memiliki beberapa peran/scope, dan aksi finalisasi yang keputusan bisnisnya belum seluruhnya ditetapkan.

## Keputusan

1. Authentication hanya membuktikan identitas. Authorization diputuskan terpisah untuk setiap aksi.
2. Role menjadi kumpulan capability, bukan satu-satunya syarat akses.
3. Izin efektif dihitung dari kombinasi:
   - capability untuk aksi;
   - hubungan aktor dengan objek/assignment;
   - scope organisasi yang aktif dan masa berlakunya;
   - state objek;
   - aturan separation of duties/conflict of interest yang telah disetujui.
4. Policy objek menjadi sumber keputusan otorisasi. Controller memanggil use case, dan service memeriksa policy sebelum membaca data sensitif atau bermutasi.
5. Query harus di-scope sejak awal sebagai defense-in-depth. Jangan mengambil objek global lalu hanya memeriksa role.
6. Default adalah deny. Tidak adanya mapping capability atau scope tidak berarti akses diizinkan.
7. Semua endpoint HTML, API, download, export, dan aksi massal memakai policy yang sama. Visibilitas menu/tombol hanya mengikuti hasil policy, bukan menggantikannya.
8. Identitas aktor diambil dari session/token server. `user_id`, `unit_id`, atau `role` dari request tidak boleh menjadi sumber kewenangan.
9. `super_admin` tidak menjadi bypass diam-diam. Override hanya untuk capability yang dinyatakan, memerlukan alasan bila sensitif, dibatasi scope seperlunya, dan menghasilkan audit event.
10. Perubahan role, capability, scope, assignment auditor, dan override dicatat.
11. Negative authorization test wajib meliputi ID objek milik unit lain, role lain, assignment lain, state terlarang, dan download privat.

M2-03 menetapkan capability minimum per aksi. Capability domain SPMI/audit
diklasifikasikan organization-scoped. Super Admin memiliki tanggung jawab
organisasi global yang eksplisit; role lain memerlukan direct active
membership. Tidak ada inheritance parent ke descendant. Finalizer
laporan/RTM, Auditor sebagai verifier, multi-role, dan conflict-of-interest
tetap menunggu keputusan stakeholder; capability yang belum mempunyai model
atau keputusan role selalu deny.

## Alternatif yang dipertimbangkan

### Pemeriksaan role di setiap controller

Ditolak karena mudah tidak konsisten dan tidak cukup untuk ownership/scope per objek.

### Scoped query saja tanpa policy

Tidak cukup untuk state transition, separation of duties, override, dan alasan penolakan yang konsisten.

### Policy saja setelah mengambil objek secara global

Tidak dipilih karena memperbesar risiko kebocoran list/count/export. Query juga harus di-scope.

### Access control list per pengguna untuk semua objek

Tidak dipilih sebagai default karena biaya administrasi tinggi. Assignment atau pengecualian eksplisit dapat memakai relasi per pengguna ketika memang diperlukan.

### Super admin selalu boleh melakukan apa pun

Ditolak karena menyulitkan audit dan membuat satu akun dapat melewati seluruh pemisahan tugas tanpa jejak.

## Konsekuensi

### Positif

- Aturan akses konsisten lintas UI, endpoint, download, dan export.
- Multi-unit dan beberapa penugasan pengguna dapat dimodelkan.
- Celah insecure direct object reference lebih mudah diuji.
- Override administratif dapat dilacak.

### Negatif dan biaya

- Diperlukan katalog capability, policy, dan fixture test.
- Query serta service lama harus dimigrasikan bertahap.
- Perubahan role tidak otomatis sederhana karena dapat memengaruhi banyak capability.
- Cache permission, bila digunakan, harus diinvalidation dengan benar.

## Risiko dan mitigasi

| Risiko | Mitigasi |
|---|---|
| Role dan capability menyimpang | Capability matrix berversi, review perubahan, dan test per role. |
| Policy dilewati endpoint legacy | Inventarisasi route, adapter melalui service, negative test, dan cutover ADR 0006. |
| Scoped query terlalu longgar | Query builder khusus scope dan fixture dua unit/dua assignment. |
| Scoped query terlalu ketat menghambat tugas sah | Test use case positif dan mekanisme assignment yang eksplisit; jangan membuat bypass global. |
| Perubahan permission tidak langsung berlaku | Invalidation cache/session dan masa berlaku mapping. |
| Override disalahgunakan | Capability khusus, alasan wajib, audit event, dan review berkala. |

## Status dan pemicu peninjauan

Keputusan ini diterapkan sebagai foundation pada `Authorization_policy`,
`Auth_guard`, scoped query participant, explicit Super Admin override, dan
negative matrix M1-04. M2-01 menambahkan master unit ber-ID stabil. M2-02
menambahkan assignment user/unit/jabatan bertanggal, primary assignment, serta
policy membership langsung yang hanya mengembalikan assignment aktif.

M2-03 mengganti capability coarse dengan matriks per domain/aksi, memetakan
controller, menambahkan scope mode serta guard capability + unit, dan
menetapkan direct membership tanpa parent/descendant inheritance. RTM,
follow-up, finalizer, dan entity legacy tanpa unit ID tetap deny atau
institution-wide sesuai boundary terdokumentasi. Tinjau kembali ketika entity
M3 mulai menyimpan organization unit, setelah keputusan BIZ-008–BIZ-010, atau
bila identity provider eksternal mengubah identitas dan claim.
