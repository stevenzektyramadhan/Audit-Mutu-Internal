-- Data dummy AMI untuk pengujian alur MVP
-- Aman dijalankan ulang: standar, pertanyaan, tugas, dan jawaban tidak diduplikasi.

USE `ami`;
START TRANSACTION;

SET @auditor_id = (SELECT `id` FROM `users` WHERE `email` = 'auditor@ami.test' AND `role` = 'auditor' LIMIT 1);
SET @auditee_id = (SELECT `id` FROM `users` WHERE `email` = 'auditee@ami.test' AND `role` = 'auditee' LIMIT 1);

-- Standar audit
INSERT INTO `standar` (`nama_standar`, `deskripsi`)
SELECT 'Standar Pendidikan dan Pembelajaran', 'Menilai perencanaan, pelaksanaan, evaluasi, dan peningkatan mutu pembelajaran.'
WHERE NOT EXISTS (
    SELECT 1 FROM `standar` WHERE `nama_standar` = 'Standar Pendidikan dan Pembelajaran'
);

INSERT INTO `standar` (`nama_standar`, `deskripsi`)
SELECT 'Standar Penelitian', 'Menilai kesesuaian perencanaan, pelaksanaan, luaran, dan dokumentasi kegiatan penelitian.'
WHERE NOT EXISTS (
    SELECT 1 FROM `standar` WHERE `nama_standar` = 'Standar Penelitian'
);

INSERT INTO `standar` (`nama_standar`, `deskripsi`)
SELECT 'Standar Pengabdian kepada Masyarakat', 'Menilai perencanaan, pelaksanaan, luaran, dan evaluasi kegiatan pengabdian kepada masyarakat.'
WHERE NOT EXISTS (
    SELECT 1 FROM `standar` WHERE `nama_standar` = 'Standar Pengabdian kepada Masyarakat'
);

SET @standar_pendidikan = (SELECT `id` FROM `standar` WHERE `nama_standar` = 'Standar Pendidikan dan Pembelajaran' ORDER BY `id` ASC LIMIT 1);
SET @standar_penelitian = (SELECT `id` FROM `standar` WHERE `nama_standar` = 'Standar Penelitian' ORDER BY `id` ASC LIMIT 1);
SET @standar_pengabdian = (SELECT `id` FROM `standar` WHERE `nama_standar` = 'Standar Pengabdian kepada Masyarakat' ORDER BY `id` ASC LIMIT 1);

-- Pertanyaan Standar Pendidikan dan Pembelajaran
INSERT INTO `pertanyaan` (`standar_id`, `isi_pertanyaan`)
SELECT @standar_pendidikan, 'Apakah Rencana Pembelajaran Semester tersedia dan telah disahkan?'
WHERE @standar_pendidikan IS NOT NULL AND NOT EXISTS (
    SELECT 1 FROM `pertanyaan` WHERE `standar_id` = @standar_pendidikan AND `isi_pertanyaan` = 'Apakah Rencana Pembelajaran Semester tersedia dan telah disahkan?'
);

INSERT INTO `pertanyaan` (`standar_id`, `isi_pertanyaan`)
SELECT @standar_pendidikan, 'Apakah pelaksanaan pembelajaran sesuai dengan Rencana Pembelajaran Semester?'
WHERE @standar_pendidikan IS NOT NULL AND NOT EXISTS (
    SELECT 1 FROM `pertanyaan` WHERE `standar_id` = @standar_pendidikan AND `isi_pertanyaan` = 'Apakah pelaksanaan pembelajaran sesuai dengan Rencana Pembelajaran Semester?'
);

INSERT INTO `pertanyaan` (`standar_id`, `isi_pertanyaan`)
SELECT @standar_pendidikan, 'Apakah hasil evaluasi pembelajaran terdokumentasi dengan baik?'
WHERE @standar_pendidikan IS NOT NULL AND NOT EXISTS (
    SELECT 1 FROM `pertanyaan` WHERE `standar_id` = @standar_pendidikan AND `isi_pertanyaan` = 'Apakah hasil evaluasi pembelajaran terdokumentasi dengan baik?'
);

INSERT INTO `pertanyaan` (`standar_id`, `isi_pertanyaan`)
SELECT @standar_pendidikan, 'Apakah terdapat tindak lanjut atas hasil evaluasi pembelajaran?'
WHERE @standar_pendidikan IS NOT NULL AND NOT EXISTS (
    SELECT 1 FROM `pertanyaan` WHERE `standar_id` = @standar_pendidikan AND `isi_pertanyaan` = 'Apakah terdapat tindak lanjut atas hasil evaluasi pembelajaran?'
);

-- Pertanyaan Standar Penelitian
INSERT INTO `pertanyaan` (`standar_id`, `isi_pertanyaan`)
SELECT @standar_penelitian, 'Apakah program penelitian sesuai dengan peta jalan penelitian perguruan tinggi?'
WHERE @standar_penelitian IS NOT NULL AND NOT EXISTS (
    SELECT 1 FROM `pertanyaan` WHERE `standar_id` = @standar_penelitian AND `isi_pertanyaan` = 'Apakah program penelitian sesuai dengan peta jalan penelitian perguruan tinggi?'
);

INSERT INTO `pertanyaan` (`standar_id`, `isi_pertanyaan`)
SELECT @standar_penelitian, 'Apakah proposal dan laporan penelitian terdokumentasi dengan lengkap?'
WHERE @standar_penelitian IS NOT NULL AND NOT EXISTS (
    SELECT 1 FROM `pertanyaan` WHERE `standar_id` = @standar_penelitian AND `isi_pertanyaan` = 'Apakah proposal dan laporan penelitian terdokumentasi dengan lengkap?'
);

INSERT INTO `pertanyaan` (`standar_id`, `isi_pertanyaan`)
SELECT @standar_penelitian, 'Apakah penelitian melibatkan mahasiswa dalam pelaksanaannya?'
WHERE @standar_penelitian IS NOT NULL AND NOT EXISTS (
    SELECT 1 FROM `pertanyaan` WHERE `standar_id` = @standar_penelitian AND `isi_pertanyaan` = 'Apakah penelitian melibatkan mahasiswa dalam pelaksanaannya?'
);

INSERT INTO `pertanyaan` (`standar_id`, `isi_pertanyaan`)
SELECT @standar_penelitian, 'Apakah luaran penelitian telah dipublikasikan atau didiseminasikan?'
WHERE @standar_penelitian IS NOT NULL AND NOT EXISTS (
    SELECT 1 FROM `pertanyaan` WHERE `standar_id` = @standar_penelitian AND `isi_pertanyaan` = 'Apakah luaran penelitian telah dipublikasikan atau didiseminasikan?'
);

-- Pertanyaan Standar Pengabdian kepada Masyarakat
INSERT INTO `pertanyaan` (`standar_id`, `isi_pertanyaan`)
SELECT @standar_pengabdian, 'Apakah kegiatan pengabdian sesuai dengan kebutuhan masyarakat mitra?'
WHERE @standar_pengabdian IS NOT NULL AND NOT EXISTS (
    SELECT 1 FROM `pertanyaan` WHERE `standar_id` = @standar_pengabdian AND `isi_pertanyaan` = 'Apakah kegiatan pengabdian sesuai dengan kebutuhan masyarakat mitra?'
);

INSERT INTO `pertanyaan` (`standar_id`, `isi_pertanyaan`)
SELECT @standar_pengabdian, 'Apakah kegiatan pengabdian memiliki proposal dan surat tugas yang lengkap?'
WHERE @standar_pengabdian IS NOT NULL AND NOT EXISTS (
    SELECT 1 FROM `pertanyaan` WHERE `standar_id` = @standar_pengabdian AND `isi_pertanyaan` = 'Apakah kegiatan pengabdian memiliki proposal dan surat tugas yang lengkap?'
);

INSERT INTO `pertanyaan` (`standar_id`, `isi_pertanyaan`)
SELECT @standar_pengabdian, 'Apakah hasil kegiatan pengabdian terdokumentasi dan dipublikasikan?'
WHERE @standar_pengabdian IS NOT NULL AND NOT EXISTS (
    SELECT 1 FROM `pertanyaan` WHERE `standar_id` = @standar_pengabdian AND `isi_pertanyaan` = 'Apakah hasil kegiatan pengabdian terdokumentasi dan dipublikasikan?'
);

INSERT INTO `pertanyaan` (`standar_id`, `isi_pertanyaan`)
SELECT @standar_pengabdian, 'Apakah terdapat evaluasi kepuasan dan tindak lanjut dari masyarakat mitra?'
WHERE @standar_pengabdian IS NOT NULL AND NOT EXISTS (
    SELECT 1 FROM `pertanyaan` WHERE `standar_id` = @standar_pengabdian AND `isi_pertanyaan` = 'Apakah terdapat evaluasi kepuasan dan tindak lanjut dari masyarakat mitra?'
);

-- Tugas belum diisi
INSERT INTO `tugas_audit` (`auditor_id`, `auditee_id`, `standar_id`, `status`)
SELECT @auditor_id, @auditee_id, @standar_pendidikan, 'belum_diisi'
WHERE @auditor_id IS NOT NULL AND @auditee_id IS NOT NULL AND @standar_pendidikan IS NOT NULL
AND NOT EXISTS (
    SELECT 1 FROM `tugas_audit`
    WHERE `auditor_id` = @auditor_id AND `auditee_id` = @auditee_id AND `standar_id` = @standar_pendidikan
);
SET @tugas_pendidikan_baru = ROW_COUNT();
SET @tugas_pendidikan = (
    SELECT `id` FROM `tugas_audit`
    WHERE `auditor_id` = @auditor_id AND `auditee_id` = @auditee_id AND `standar_id` = @standar_pendidikan
    ORDER BY `id` ASC LIMIT 1
);

-- Tugas sudah diisi dan menunggu penilaian
INSERT INTO `tugas_audit` (`auditor_id`, `auditee_id`, `standar_id`, `status`)
SELECT @auditor_id, @auditee_id, @standar_penelitian, 'diisi'
WHERE @auditor_id IS NOT NULL AND @auditee_id IS NOT NULL AND @standar_penelitian IS NOT NULL
AND NOT EXISTS (
    SELECT 1 FROM `tugas_audit`
    WHERE `auditor_id` = @auditor_id AND `auditee_id` = @auditee_id AND `standar_id` = @standar_penelitian
);
SET @tugas_penelitian_baru = ROW_COUNT();
SET @tugas_penelitian = (
    SELECT `id` FROM `tugas_audit`
    WHERE `auditor_id` = @auditor_id AND `auditee_id` = @auditee_id AND `standar_id` = @standar_penelitian
    ORDER BY `id` ASC LIMIT 1
);

-- Tugas sudah dinilai
INSERT INTO `tugas_audit` (`auditor_id`, `auditee_id`, `standar_id`, `status`)
SELECT @auditor_id, @auditee_id, @standar_pengabdian, 'dinilai'
WHERE @auditor_id IS NOT NULL AND @auditee_id IS NOT NULL AND @standar_pengabdian IS NOT NULL
AND NOT EXISTS (
    SELECT 1 FROM `tugas_audit`
    WHERE `auditor_id` = @auditor_id AND `auditee_id` = @auditee_id AND `standar_id` = @standar_pengabdian
);
SET @tugas_pengabdian_baru = ROW_COUNT();
SET @tugas_pengabdian = (
    SELECT `id` FROM `tugas_audit`
    WHERE `auditor_id` = @auditor_id AND `auditee_id` = @auditee_id AND `standar_id` = @standar_pengabdian
    ORDER BY `id` ASC LIMIT 1
);

-- Jawaban kosong otomatis untuk seluruh tugas dummy
INSERT INTO `jawaban_audit` (`tugas_id`, `pertanyaan_id`)
SELECT @tugas_pendidikan, p.`id`
FROM `pertanyaan` p
WHERE p.`standar_id` = @standar_pendidikan
AND @tugas_pendidikan IS NOT NULL
AND NOT EXISTS (
    SELECT 1 FROM `jawaban_audit` j
    WHERE j.`tugas_id` = @tugas_pendidikan AND j.`pertanyaan_id` = p.`id`
);

INSERT INTO `jawaban_audit` (`tugas_id`, `pertanyaan_id`)
SELECT @tugas_penelitian, p.`id`
FROM `pertanyaan` p
WHERE p.`standar_id` = @standar_penelitian
AND @tugas_penelitian IS NOT NULL
AND NOT EXISTS (
    SELECT 1 FROM `jawaban_audit` j
    WHERE j.`tugas_id` = @tugas_penelitian AND j.`pertanyaan_id` = p.`id`
);

INSERT INTO `jawaban_audit` (`tugas_id`, `pertanyaan_id`)
SELECT @tugas_pengabdian, p.`id`
FROM `pertanyaan` p
WHERE p.`standar_id` = @standar_pengabdian
AND @tugas_pengabdian IS NOT NULL
AND NOT EXISTS (
    SELECT 1 FROM `jawaban_audit` j
    WHERE j.`tugas_id` = @tugas_pengabdian AND j.`pertanyaan_id` = p.`id`
);

-- Isi contoh hanya ketika tugas tersebut baru dibuat oleh seed ini.
UPDATE `jawaban_audit`
SET
    `jawaban` = 'Dokumen penelitian tersedia dan telah diterapkan oleh unit.',
    `link_bukti` = 'https://example.com/bukti-penelitian',
    `updated_at` = CURRENT_TIMESTAMP
WHERE `tugas_id` = @tugas_penelitian AND @tugas_penelitian_baru = 1;

UPDATE `jawaban_audit` j
JOIN `pertanyaan` p ON p.`id` = j.`pertanyaan_id`
SET
    j.`jawaban` = 'Dokumen kegiatan pengabdian tersedia dan telah diverifikasi.',
    j.`link_bukti` = 'https://example.com/bukti-pengabdian',
    j.`skor` = CASE WHEN MOD(p.`id`, 2) = 0 THEN 4 ELSE 3 END,
    j.`temuan` = CASE WHEN MOD(p.`id`, 2) = 0 THEN 'Bukti sangat lengkap.' ELSE 'Sesuai, dokumentasi dapat ditingkatkan.' END,
    j.`updated_at` = CURRENT_TIMESTAMP
WHERE j.`tugas_id` = @tugas_pengabdian AND @tugas_pengabdian_baru = 1;

COMMIT;