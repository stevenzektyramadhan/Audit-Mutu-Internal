# Architecture Decision Records

Folder ini menyimpan keputusan arsitektur yang berlaku untuk pengembangan AMI. ADR menjelaskan alasan di balik suatu arah teknis; ADR bukan bukti bahwa arah tersebut sudah selesai diimplementasikan.

## Arti status

| Status | Arti |
|---|---|
| Proposed | Usulan yang masih perlu ditinjau. |
| Accepted | Arah arsitektur disetujui untuk implementasi bertahap. |
| Superseded | Digantikan oleh ADR yang lebih baru. |
| Rejected | Sudah dipertimbangkan dan tidak dipilih. |

Keterangan `implementation pending` berarti keputusan telah diterima, tetapi kondisi aplikasi lama masih dapat berbeda. Selama masa transisi, perbedaan tersebut harus dicatat dan ditutup melalui task implementasi, bukan disembunyikan.

## Daftar ADR

| ADR | Keputusan | Status |
|---|---|---|
| [0001](0001-service-layer-boundary.md) | Batas controller, service, policy, model, storage, dan report | Accepted — implementation pending |
| [0002](0002-spmi-versioning.md) | Versioning dan masa berlaku dokumen SPMI | Accepted — implementation pending |
| [0003](0003-audit-snapshot.md) | Snapshot data acuan ketika penugasan audit dibuat | Accepted — implementation pending |
| [0004](0004-private-file-storage.md) | Penyimpanan file privat dan akses terotorisasi | Accepted — implementation pending |
| [0005](0005-role-and-scope-authorization.md) | Otorisasi berdasarkan capability, role, dan scope objek | Accepted — implementation pending |
| [0006](0006-legacy-migration-strategy.md) | Migrasi legacy secara inkremental dan dapat diverifikasi | Accepted — implementation pending |

Keputusan bisnis yang belum final dicatat terpisah di [decision register](../product/decision-register.md).

## Aturan pemeliharaan

1. Jangan mengubah isi keputusan ADR yang sudah `Accepted` untuk menyamarkan perubahan arah.
2. Buat ADR baru yang menyebut ADR lama sebagai `Superseded` bila arah berubah secara material.
3. Implementasi harus menautkan ADR terkait dalam task, commit, atau pull request.
4. Keputusan bisnis yang memengaruhi ADR harus lebih dulu berstatus `Accepted` di decision register.
5. ADR harus tetap membedakan kondisi sistem saat ini dengan arsitektur target.
