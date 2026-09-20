<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$profil = isset($profil) ? $profil : NULL;
$institution_logo_limit_mib = isset($institution_logo_limit_mib) ? (int) $institution_logo_limit_mib : 4;

if (!function_exists('profil_value')) {
    function profil_value($profil, $field)
    {
        return $profil && isset($profil->{$field}) ? $profil->{$field} : '';
    }
}

$logo_src = '';
if ($profil && !empty($profil->logo_path)) {
    $logo_src = base_url('uploads/profil/' . rawurlencode($profil->logo_path));
} elseif ($profil && !empty($profil->logo_url)) {
    $logo_src = $profil->logo_url;
}

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'building' => '<rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M8 10h.01"/><path d="M16 10h.01"/><path d="M8 14h.01"/><path d="M16 14h.01"/>',
        'scale' => '<path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="M7 21h10"/><path d="M12 3v18"/><path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"/>',
        'award' => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
        'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'image' => '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
        'upload-cloud' => '<path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M12 12v9"/><path d="m16 16-4-4-4 4"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        'info' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
        'link' => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
    ];
    return '<svg class="prf-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['building']) . '</svg>';
};

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<main id="profil-root" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-4xl">
        <!-- Top Back Action -->
        <div class="ami-row-actions no-print tw-mb-5">
            <a class="ami-action-btn prf-back-link tw-text-sm" href="<?php echo site_url('profil'); ?>">
                <?php echo $icon('arrow-left'); ?>
                <span>Kembali ke Profil Lembaga</span>
            </a>
        </div>

        <!-- Header / Hero -->
        <div class="tw-mb-8">
            <p class="tw-mb-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-text-slate-500">Konfigurasi Institusi</p>
            <h1 class="tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950 tw-m-0">Edit Profil Lembaga</h1>
            <p class="tw-mt-2 tw-max-w-2xl tw-text-sm tw-text-slate-500">
                Perbarui identitas perguruan tinggi, legalitas, akreditasi, statistik SDM, kontak resmi, dan logo lembaga.
            </p>
        </div>

        <?php if (validation_errors()): ?>
            <div class="tw-mb-6 tw-rounded-xl tw-border tw-border-red-200 tw-bg-red-50 tw-p-4 tw-text-sm tw-text-red-700">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <?php echo form_open_multipart('profil/update', ['id' => 'profile-form', 'class' => 'tw-space-y-6']); ?>

            <!-- Section 1: Identitas Perguruan Tinggi -->
            <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 sm:tw-p-8 tw-shadow-sm">
                <div class="tw-flex tw-items-center tw-gap-2.5 tw-pb-4 tw-border-b tw-border-slate-100 tw-mb-5">
                    <span class="tw-text-blue-600"><?php echo $icon('building'); ?></span>
                    <h2 class="tw-text-base tw-font-bold tw-text-slate-950 tw-m-0">1. Identitas Perguruan Tinggi</h2>
                </div>

                <div class="tw-grid tw-gap-4 sm:tw-grid-cols-2">
                    <div class="sm:tw-col-span-2">
                        <label for="nama_pt" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Nama Universitas <span class="tw-text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_pt" name="nama_pt" value="<?php echo html_escape(set_value('nama_pt', profil_value($profil, 'nama_pt'))); ?>" required maxlength="200" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" placeholder="Contoh: Universitas Muhammadiyah Bangka Belitung">
                    </div>

                    <div>
                        <label for="kode_pt" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Kode PT
                        </label>
                        <input type="text" id="kode_pt" name="kode_pt" value="<?php echo html_escape(set_value('kode_pt', profil_value($profil, 'kode_pt'))); ?>" maxlength="20" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-2.5 tw-text-sm tw-font-mono tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" placeholder="Contoh: 021001">
                    </div>

                    <div>
                        <label for="status_pt" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Status PT
                        </label>
                        <input type="text" id="status_pt" name="status_pt" value="<?php echo html_escape(set_value('status_pt', profil_value($profil, 'status_pt'))); ?>" maxlength="50" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" placeholder="Contoh: Aktif">
                    </div>
                </div>

                <!-- PDDikti Subsection Callout -->
                <div class="tw-mt-5 tw-rounded-xl tw-border tw-border-slate-200 tw-bg-slate-50/70 tw-p-4 sm:tw-p-5">
                    <div class="tw-flex tw-items-center tw-gap-2 tw-mb-2">
                        <span class="tw-text-slate-400"><?php echo $icon('info'); ?></span>
                        <h3 class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-m-0">Konfigurasi Sinkronisasi PDDikti</h3>
                    </div>
                    <p class="tw-text-[11px] tw-text-slate-500 tw-leading-relaxed tw-mb-4">
                        Data ini digunakan saat sinkronisasi otomatis dengan API PDDikti Kemdikbud. Kosongkan jika belum tersedia.
                    </p>

                    <div class="tw-grid tw-gap-4 sm:tw-grid-cols-2">
                        <div>
                            <label for="nama_pt_pddikti" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-600 tw-mb-1">
                                Nama PT di PDDikti
                            </label>
                            <input type="text" id="nama_pt_pddikti" name="nama_pt_pddikti" value="<?php echo html_escape(set_value('nama_pt_pddikti', profil_value($profil, 'nama_pt_pddikti'))); ?>" maxlength="200" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2 tw-text-xs tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" placeholder="Nama PT persis terdaftar di PDDikti">
                        </div>

                        <div>
                            <label for="id_pt_pddikti" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-600 tw-mb-1">
                                ID PT PDDikti (UUID)
                            </label>
                            <input type="text" id="id_pt_pddikti" name="id_pt_pddikti" value="<?php echo html_escape(set_value('id_pt_pddikti', profil_value($profil, 'id_pt_pddikti'))); ?>" maxlength="255" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2 tw-text-xs tw-font-mono tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" placeholder="UUID institusi di PDDikti">
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 2: Legalitas & Pendirian -->
            <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 sm:tw-p-8 tw-shadow-sm">
                <div class="tw-flex tw-items-center tw-gap-2.5 tw-pb-4 tw-border-b tw-border-slate-100 tw-mb-5">
                    <span class="tw-text-blue-600"><?php echo $icon('scale'); ?></span>
                    <h2 class="tw-text-base tw-font-bold tw-text-slate-950 tw-m-0">2. Legalitas &amp; Pendirian</h2>
                </div>

                <div class="tw-grid tw-gap-4 sm:tw-grid-cols-3">
                    <div class="sm:tw-col-span-3">
                        <label for="nomor_sk_pt" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Nomor SK PT
                        </label>
                        <input type="text" id="nomor_sk_pt" name="nomor_sk_pt" value="<?php echo html_escape(set_value('nomor_sk_pt', profil_value($profil, 'nomor_sk_pt'))); ?>" maxlength="100" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" placeholder="Nomor SK Izin Pendirian / Operasional">
                    </div>

                    <div>
                        <label for="tanggal_sk_pt" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Tanggal SK PT
                        </label>
                        <input type="date" id="tanggal_sk_pt" name="tanggal_sk_pt" value="<?php echo html_escape(set_value('tanggal_sk_pt', profil_value($profil, 'tanggal_sk_pt'))); ?>" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none">
                    </div>

                    <div class="sm:tw-col-span-2">
                        <label for="tanggal_berdiri" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Tanggal Berdiri / Dies Natalis
                        </label>
                        <input type="date" id="tanggal_berdiri" name="tanggal_berdiri" value="<?php echo html_escape(set_value('tanggal_berdiri', profil_value($profil, 'tanggal_berdiri'))); ?>" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none">
                    </div>
                </div>
            </section>

            <!-- Section 3: Akreditasi & Statistik SDM -->
            <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 sm:tw-p-8 tw-shadow-sm">
                <div class="tw-flex tw-items-center tw-gap-2.5 tw-pb-4 tw-border-b tw-border-slate-100 tw-mb-5">
                    <span class="tw-text-blue-600"><?php echo $icon('award'); ?></span>
                    <h2 class="tw-text-base tw-font-bold tw-text-slate-950 tw-m-0">3. Akreditasi &amp; Statistik SDM</h2>
                </div>

                <div class="tw-grid tw-gap-4 sm:tw-grid-cols-2">
                    <div>
                        <label for="akreditasi" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Peringkat Akreditasi Institusi
                        </label>
                        <input type="text" id="akreditasi" name="akreditasi" value="<?php echo html_escape(set_value('akreditasi', profil_value($profil, 'akreditasi'))); ?>" maxlength="100" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" placeholder="Contoh: Unggul / Baik Sekali / Baik / A / B">
                    </div>

                    <div>
                        <label for="akreditasi_berlaku_sampai" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Akreditasi Berlaku Sampai
                        </label>
                        <input type="date" id="akreditasi_berlaku_sampai" name="akreditasi_berlaku_sampai" value="<?php echo html_escape(set_value('akreditasi_berlaku_sampai', profil_value($profil, 'akreditasi_berlaku_sampai'))); ?>" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none">
                    </div>

                    <div>
                        <label for="jumlah_dosen" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Jumlah Dosen Aktif
                        </label>
                        <input type="number" min="0" id="jumlah_dosen" name="jumlah_dosen" value="<?php echo html_escape(set_value('jumlah_dosen', profil_value($profil, 'jumlah_dosen'))); ?>" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" placeholder="Total dosen tetap">
                    </div>

                    <div>
                        <label for="jumlah_tendik" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Jumlah Tenaga Kependidikan
                        </label>
                        <input type="number" min="0" id="jumlah_tendik" name="jumlah_tendik" value="<?php echo html_escape(set_value('jumlah_tendik', profil_value($profil, 'jumlah_tendik'))); ?>" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" placeholder="Total staf tendik">
                    </div>
                </div>
            </section>

            <!-- Section 4: Kontak Lembaga -->
            <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 sm:tw-p-8 tw-shadow-sm">
                <div class="tw-flex tw-items-center tw-gap-2.5 tw-pb-4 tw-border-b tw-border-slate-100 tw-mb-5">
                    <span class="tw-text-blue-600"><?php echo $icon('phone'); ?></span>
                    <h2 class="tw-text-base tw-font-bold tw-text-slate-950 tw-m-0">4. Kontak &amp; Lokasi Lembaga</h2>
                </div>

                <div class="tw-grid tw-gap-4 sm:tw-grid-cols-2">
                    <div class="sm:tw-col-span-2">
                        <label for="email" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Alamat Email Resmi
                        </label>
                        <input type="email" id="email" name="email" value="<?php echo html_escape(set_value('email', profil_value($profil, 'email'))); ?>" maxlength="100" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" placeholder="info@universitas.ac.id">
                    </div>

                    <div>
                        <label for="telepon" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Nomor Telepon
                        </label>
                        <input type="text" id="telepon" name="telepon" value="<?php echo html_escape(set_value('telepon', profil_value($profil, 'telepon'))); ?>" maxlength="30" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" placeholder="(0717) 431xxx">
                    </div>

                    <div>
                        <label for="faksimile" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Nomor Faksimile
                        </label>
                        <input type="text" id="faksimile" name="faksimile" value="<?php echo html_escape(set_value('faksimile', profil_value($profil, 'faksimile'))); ?>" maxlength="30" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" placeholder="(0717) 431xxx">
                    </div>

                    <div class="sm:tw-col-span-2">
                        <label for="kode_pos" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Kode Pos
                        </label>
                        <input type="text" id="kode_pos" name="kode_pos" value="<?php echo html_escape(set_value('kode_pos', profil_value($profil, 'kode_pos'))); ?>" maxlength="10" class="tw-w-full sm:tw-w-48 tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-2.5 tw-text-sm tw-font-mono tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" placeholder="33171">
                    </div>
                </div>
            </section>

            <!-- Section 5: Logo Lembaga -->
            <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 sm:tw-p-8 tw-shadow-sm">
                <div class="tw-flex tw-items-center tw-gap-2.5 tw-pb-4 tw-border-b tw-border-slate-100 tw-mb-5">
                    <span class="tw-text-blue-600"><?php echo $icon('image'); ?></span>
                    <h2 class="tw-text-base tw-font-bold tw-text-slate-950 tw-m-0">5. Logo Lembaga</h2>
                </div>

                <div class="tw-grid tw-gap-6 md:tw-grid-cols-[140px_1fr] tw-items-start">
                    <!-- Current / Live Preview Logo Container -->
                    <div class="tw-flex tw-flex-col tw-items-center tw-text-center">
                        <div class="tw-relative tw-flex tw-h-32 tw-w-32 tw-items-center tw-justify-center tw-rounded-2xl tw-border-2 tw-border-slate-200 tw-bg-slate-50 tw-p-3 tw-shadow-sm tw-overflow-hidden">
                            <?php if ($logo_src !== ''): ?>
                                <img id="logo-preview-img" src="<?php echo html_escape($logo_src); ?>" alt="Logo Lembaga" class="tw-h-full tw-w-full tw-object-contain">
                                <span id="logo-preview-placeholder" class="tw-hidden tw-text-slate-400"><?php echo $icon('image'); ?></span>
                            <?php else: ?>
                                <img id="logo-preview-img" src="" alt="Preview Logo" class="tw-hidden tw-h-full tw-w-full tw-object-contain">
                                <span id="logo-preview-placeholder" class="tw-text-slate-400"><?php echo $icon('image'); ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="tw-mt-2 tw-text-[11px] tw-font-medium tw-text-slate-500">Preview Logo</span>
                    </div>

                    <!-- Custom Dropzone & PDDikti URL -->
                    <div class="tw-space-y-4">
                        <div>
                            <label for="logo" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                                Upload Logo Manual
                            </label>

                            <!-- Accessible hidden native input -->
                            <input type="file" id="logo" name="logo" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif" class="tw-sr-only">

                            <!-- Custom UI Dropzone -->
                            <label for="logo" id="logo-dropzone" class="tw-group tw-flex tw-flex-col sm:tw-flex-row tw-items-center tw-gap-4 tw-rounded-xl tw-border-2 tw-border-dashed tw-border-slate-300 hover:tw-border-blue-500 tw-bg-slate-50/50 hover:tw-bg-blue-50/20 tw-p-5 tw-cursor-pointer tw-transition tw-m-0">
                                <div class="tw-flex tw-h-12 tw-w-12 tw-items-center tw-justify-center tw-rounded-full tw-bg-white tw-border tw-border-slate-200 tw-text-slate-400 group-hover:tw-text-blue-600 tw-shadow-sm tw-transition">
                                    <?php echo $icon('upload-cloud'); ?>
                                </div>
                                <div class="tw-flex-1 tw-text-center sm:tw-text-left tw-min-w-0">
                                    <div id="logo-label-main" class="tw-text-xs tw-font-bold tw-text-slate-800 group-hover:tw-text-blue-600 tw-truncate tw-transition">
                                        Klik atau seret logo institusi ke sini
                                    </div>
                                    <div class="tw-text-[11px] tw-text-slate-500 tw-mt-0.5">
                                        Format JPG, JPEG, PNG, atau GIF (Maksimal <?php echo html_escape($institution_logo_limit_mib); ?> MiB).
                                    </div>
                                </div>
                                <span class="tw-button-secondary tw-text-xs tw-pointer-events-none">
                                    Pilih Berkas
                                </span>
                            </label>

                            <!-- Selected File Feedback -->
                            <div id="selected-logo-feedback" class="tw-hidden tw-mt-2.5 tw-flex tw-items-center tw-justify-between tw-rounded-lg tw-border tw-border-emerald-200 tw-bg-emerald-50 tw-px-3 tw-py-2 tw-text-xs tw-text-emerald-900">
                                <div class="tw-flex tw-items-center tw-gap-2 tw-min-w-0">
                                    <span class="tw-text-emerald-600"><?php echo $icon('image'); ?></span>
                                    <span id="selected-logo-name" class="tw-font-medium tw-truncate">logo_baru.png</span>
                                    <span id="selected-logo-size" class="tw-text-[11px] tw-text-emerald-700 tw-whitespace-nowrap">(500 KB)</span>
                                </div>
                                <button type="button" id="btn-clear-logo" class="tw-text-xs tw-font-bold tw-text-emerald-700 hover:tw-text-emerald-900 tw-ml-2 hover:tw-underline">
                                    Batal
                                </button>
                            </div>

                            <p class="tw-mt-2 tw-text-[11px] tw-text-slate-500 tw-leading-relaxed tw-m-0">
                                <strong>Catatan Prioritas:</strong> Logo hasil upload manual akan selalu diprioritaskan dibanding logo dari URL PDDikti.
                            </p>
                        </div>

                        <!-- Secondary: URL Logo PDDikti -->
                        <div class="tw-pt-3 tw-border-t tw-border-slate-100">
                            <label for="logo_url" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-600 tw-mb-1">
                                URL Logo PDDikti (Sekunder / Remote)
                            </label>
                            <input type="url" id="logo_url" name="logo_url" value="<?php echo html_escape(set_value('logo_url', profil_value($profil, 'logo_url'))); ?>" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2 tw-text-xs tw-font-mono tw-text-slate-700 focus:tw-border-slate-950 focus:tw-outline-none" placeholder="https://pddikti.kemdikbud.go.id/...">
                            <span class="tw-mt-1 tw-block tw-text-[11px] tw-text-slate-400">
                                Diisi otomatis oleh proses sinkronisasi PDDikti sebagai tautan alternatif.
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Submit Action Footer -->
            <div class="tw-flex tw-items-center tw-justify-end tw-gap-3 tw-pt-2">
                <a href="<?php echo site_url('profil'); ?>" class="tw-button-secondary">
                    Batal
                </a>
                <button type="submit" class="btn-ami tw-button-primary" data-loading-text="Menyimpan...">
                    <?php echo $icon('check'); ?>
                    <span>Simpan Profil</span>
                </button>
            </div>
        <?php echo form_close(); ?>
    </div>
</main>

<script>
(function () {
    var logoInput = document.getElementById('logo');
    var dropzone = document.getElementById('logo-dropzone');
    var feedback = document.getElementById('selected-logo-feedback');
    var nameSpan = document.getElementById('selected-logo-name');
    var sizeSpan = document.getElementById('selected-logo-size');
    var clearBtn = document.getElementById('btn-clear-logo');
    var labelMain = document.getElementById('logo-label-main');

    var previewImg = document.getElementById('logo-preview-img');
    var previewPlaceholder = document.getElementById('logo-preview-placeholder');
    var originalImgSrc = previewImg ? previewImg.getAttribute('src') : '';
    var hasOriginalLogo = originalImgSrc !== '';

    if (dropzone && logoInput) {
        ['dragenter', 'dragover'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('tw-border-blue-500', 'tw-bg-blue-50/40');
            });
        });

        ['dragleave', 'drop'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('tw-border-blue-500', 'tw-bg-blue-50/40');
            });
        });

        dropzone.addEventListener('drop', function (e) {
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                logoInput.files = e.dataTransfer.files;
                var event = new Event('change', { bubbles: true });
                logoInput.dispatchEvent(event);
            }
        });
    }

    if (logoInput) {
        logoInput.addEventListener('change', function () {
            if (logoInput.files && logoInput.files[0]) {
                var file = logoInput.files[0];
                var sizeKb = (file.size / 1024).toFixed(0);
                var sizeText = sizeKb >= 1024 ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';

                if (nameSpan) nameSpan.textContent = file.name;
                if (sizeSpan) sizeSpan.textContent = '(' + sizeText + ')';
                if (labelMain) labelMain.textContent = file.name;
                if (feedback) feedback.classList.remove('tw-hidden');

                // Live Preview
                var reader = new FileReader();
                reader.onload = function (e) {
                    if (previewImg) {
                        previewImg.src = e.target.result;
                        previewImg.classList.remove('tw-hidden');
                    }
                    if (previewPlaceholder) {
                        previewPlaceholder.classList.add('tw-hidden');
                    }
                };
                reader.readAsDataURL(file);
            } else {
                resetLogoInput();
            }
        });
    }

    function resetLogoInput() {
        if (logoInput) logoInput.value = '';
        if (feedback) feedback.classList.add('tw-hidden');
        if (labelMain) labelMain.textContent = 'Klik atau seret logo institusi ke sini';

        if (hasOriginalLogo && previewImg) {
            previewImg.src = originalImgSrc;
            previewImg.classList.remove('tw-hidden');
            if (previewPlaceholder) previewPlaceholder.classList.add('tw-hidden');
        } else {
            if (previewImg) {
                previewImg.src = '';
                previewImg.classList.add('tw-hidden');
            }
            if (previewPlaceholder) {
                previewPlaceholder.classList.remove('tw-hidden');
            }
        }
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            resetLogoInput();
        });
    }
})();
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
