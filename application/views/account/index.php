<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$name_parts = preg_split('/\s+/', trim((string) $account->nama));
$initials = '';
foreach (array_slice($name_parts, 0, 2) as $part) {
    if ($part !== '') {
        $initials .= strtoupper(substr($part, 0, 1));
    }
}
if ($initials === '') {
    $initials = 'A';
}

$role_labels = [
    'super_admin' => 'Super Admin',
    'admin_lpmpi' => 'Admin LPMPI',
    'auditor' => 'Auditor',
    'auditee' => 'Auditee',
];
$clean_role = isset($role_labels[$account->role]) ? $role_labels[$account->role] : ucfirst(str_replace('_', ' ', (string) $account->role));

$icon = static function ($name) {
    $paths = [
        'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'building' => '<rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M8 10h.01"/><path d="M16 10h.01"/><path d="M8 14h.01"/><path d="M16 14h.01"/>',
        'lock' => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'upload-cloud' => '<path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M12 12v9"/><path d="m16 16-4-4-4 4"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        'image' => '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
    ];
    return '<svg class="acc-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['user']) . '</svg>';
};

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<main id="account-root" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-5xl">
        <!-- Header / Hero -->
        <div class="tw-mb-8">
            <p class="tw-mb-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-text-slate-500">Pengaturan Akun</p>
            <h1 class="tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950">Akun Saya</h1>
            <p class="tw-mt-2 tw-max-w-2xl tw-text-sm tw-text-slate-500">
                Kelola informasi nama lengkap dan foto profil untuk identitas kerja Anda di sistem AMI / SPMI.
            </p>
        </div>

        <?php if (validation_errors()): ?>
            <div class="tw-mb-6 tw-rounded-xl tw-border tw-border-red-200 tw-bg-red-50 tw-p-4 tw-text-sm tw-text-red-700">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif; ?>

        <!-- 2-Column Responsive Layout -->
        <div class="tw-grid tw-gap-8 lg:tw-grid-cols-[320px_1fr]">
            <!-- Left Column: Profile / Identity Card -->
            <aside class="tw-space-y-6">
                <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm tw-text-center">
                    <!-- Avatar Preview Container -->
                    <div class="tw-relative tw-mx-auto tw-mb-4 tw-flex tw-h-24 tw-w-24 tw-items-center tw-justify-center tw-rounded-full tw-border-2 tw-border-slate-200 tw-bg-blue-50 tw-shadow-inner tw-overflow-hidden">
                        <?php if (!empty($account->profile_photo_path)): ?>
                            <img id="avatar-preview-img" src="<?php echo site_url('account/photo'); ?>" alt="Foto profil <?php echo html_escape($account->nama); ?>" class="tw-h-full tw-w-full tw-object-cover">
                            <span id="avatar-preview-initials" class="tw-hidden tw-text-2xl tw-font-bold tw-text-blue-700">
                                <?php echo html_escape($initials); ?>
                            </span>
                        <?php else: ?>
                            <img id="avatar-preview-img" src="" alt="Preview Foto" class="tw-hidden tw-h-full tw-w-full tw-object-cover">
                            <span id="avatar-preview-initials" class="tw-text-2xl tw-font-bold tw-text-blue-700" aria-label="Inisial <?php echo html_escape($initials); ?>">
                                <?php echo html_escape($initials); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Identity Text -->
                    <h2 class="tw-text-lg tw-font-bold tw-text-slate-900 tw-m-0" id="profile-display-name">
                        <?php echo html_escape($account->nama); ?>
                    </h2>
                    <p class="tw-mt-1 tw-text-xs tw-text-slate-500 tw-break-all tw-m-0">
                        <?php echo html_escape($account->email); ?>
                    </p>

                    <!-- Role Badge -->
                    <div class="tw-mt-3">
                        <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-bg-slate-100 tw-border tw-border-slate-200 tw-px-3 tw-py-1 tw-text-xs tw-font-bold tw-text-slate-700">
                            <?php echo $icon('shield'); ?>
                            <span><?php echo html_escape($clean_role); ?></span>
                        </span>
                    </div>

                    <!-- Unit Info if Available -->
                    <?php if (!empty($account->nama_unit)): ?>
                        <div class="tw-mt-4 tw-pt-4 tw-border-t tw-border-slate-100 tw-text-left tw-text-xs">
                            <span class="tw-block tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-400 tw-mb-1">
                                Unit Organisasi
                            </span>
                            <div class="tw-flex tw-items-center tw-gap-1.5 tw-text-slate-800 tw-font-semibold">
                                <span class="tw-text-slate-400"><?php echo $icon('building'); ?></span>
                                <span class="tw-truncate"><?php echo html_escape($account->nama_unit); ?></span>
                            </div>
                            <?php if (!empty($account->jenis_unit)): ?>
                                <span class="tw-inline-block tw-mt-1 tw-text-[10px] tw-text-slate-500 tw-uppercase tw-font-medium">
                                    Jenis: <?php echo html_escape($account->jenis_unit); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Privacy & Storage Note -->
                <div class="tw-rounded-xl tw-border tw-border-slate-200 tw-bg-slate-50/70 tw-p-4 tw-text-xs tw-text-slate-600 tw-space-y-1.5">
                    <div class="tw-flex tw-items-center tw-gap-1.5 tw-font-semibold tw-text-slate-800">
                        <span class="tw-text-slate-400"><?php echo $icon('lock'); ?></span>
                        <span>Penyimpanan Aman</span>
                    </div>
                    <p class="tw-m-0 tw-text-[11px] tw-leading-relaxed tw-text-slate-500">
                        Foto profil disimpan di penyimpanan privat dan hanya dapat diakses melalui akun Anda.
                    </p>
                </div>
            </aside>

            <!-- Right Column: Editable Profile Form -->
            <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 sm:tw-p-8 tw-shadow-sm">
                <div class="tw-border-b tw-border-slate-100 tw-pb-5 tw-mb-6">
                    <h2 class="tw-text-lg tw-font-bold tw-text-slate-950 tw-m-0">Perbarui Informasi Pribadi</h2>
                    <p class="tw-mt-1 tw-text-xs tw-text-slate-500 tw-m-0">
                        Pastikan nama lengkap sesuai dengan identitas resmi kampus Anda.
                    </p>
                </div>

                <?php echo form_open_multipart('account/update', ['id' => 'account-form', 'class' => 'tw-space-y-6']); ?>
                    <!-- Field: Nama Lengkap -->
                    <div>
                        <label for="nama" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Nama Lengkap <span class="tw-text-red-500">*</span>
                        </label>
                        <input type="text" id="nama" name="nama" value="<?php echo html_escape(set_value('nama', $account->nama)); ?>" required maxlength="100" autocomplete="name" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3.5 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" placeholder="Masukkan nama lengkap Anda...">
                    </div>

                    <!-- Locked Field: Email (Read-only) -->
                    <div>
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-1.5">
                            <label class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-500">
                                Alamat Email
                            </label>
                            <span class="tw-inline-flex tw-items-center tw-gap-1 tw-text-[11px] tw-text-slate-400">
                                <?php echo $icon('lock'); ?>
                                <span>Terkunci</span>
                            </span>
                        </div>
                        <div class="tw-flex tw-items-center tw-gap-2.5 tw-rounded-lg tw-border tw-border-slate-200 tw-bg-slate-50 tw-px-3.5 tw-py-2.5 tw-text-sm tw-text-slate-600">
                            <span class="tw-text-slate-400"><?php echo $icon('mail'); ?></span>
                            <span class="tw-font-medium"><?php echo html_escape($account->email); ?></span>
                        </div>
                        <span class="tw-mt-1 tw-block tw-text-[11px] tw-text-slate-400">
                            Alamat email login dikelola langsung oleh administrator sistem.
                        </span>
                    </div>

                    <!-- Locked Field: Peran Akun (Read-only) -->
                    <div>
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-1.5">
                            <label class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-500">
                                Hak Akses / Peran
                            </label>
                            <span class="tw-inline-flex tw-items-center tw-gap-1 tw-text-[11px] tw-text-slate-400">
                                <?php echo $icon('lock'); ?>
                                <span>Terkunci</span>
                            </span>
                        </div>
                        <div class="tw-flex tw-items-center tw-gap-2.5 tw-rounded-lg tw-border tw-border-slate-200 tw-bg-slate-50 tw-px-3.5 tw-py-2.5 tw-text-sm tw-text-slate-600">
                            <span class="tw-text-slate-400"><?php echo $icon('shield'); ?></span>
                            <span class="tw-font-medium"><?php echo html_escape($clean_role); ?></span>
                        </div>
                        <span class="tw-mt-1 tw-block tw-text-[11px] tw-text-slate-400">
                            Peran penugasan audit dan akses menu ditetapkan melalui kebijakan institusi.
                        </span>
                    </div>

                    <!-- Custom Photo Upload Dropzone -->
                    <div>
                        <label for="profile_photo" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                            Foto Profil
                        </label>

                        <!-- Visually hidden real file input (accessible via label / keyboard) -->
                        <div class="tw-relative">
                            <input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png" class="tw-sr-only">

                            <!-- Custom UI Card Trigger -->
                            <label for="profile_photo" id="photo-dropzone" class="tw-group tw-flex tw-flex-col sm:tw-flex-row tw-items-center tw-gap-4 tw-rounded-xl tw-border-2 tw-border-dashed tw-border-slate-300 hover:tw-border-blue-500 tw-bg-slate-50/50 hover:tw-bg-blue-50/20 tw-p-5 tw-cursor-pointer tw-transition tw-m-0">
                                <div class="tw-flex tw-h-12 tw-w-12 tw-items-center tw-justify-center tw-rounded-full tw-bg-white tw-border tw-border-slate-200 tw-text-slate-400 group-hover:tw-text-blue-600 tw-shadow-sm tw-transition">
                                    <?php echo $icon('upload-cloud'); ?>
                                </div>
                                <div class="tw-flex-1 tw-text-center sm:tw-text-left tw-min-w-0">
                                    <div id="file-label-main" class="tw-text-xs tw-font-bold tw-text-slate-800 group-hover:tw-text-blue-600 tw-truncate tw-transition">
                                        Klik atau seret berkas foto ke sini
                                    </div>
                                    <div class="tw-text-[11px] tw-text-slate-500 tw-mt-0.5">
                                        Format JPEG atau PNG, ukuran maksimal 2 MiB.
                                    </div>
                                </div>
                                <span class="tw-button-secondary tw-text-xs tw-pointer-events-none">
                                    Pilih Berkas
                                </span>
                            </label>
                        </div>

                        <!-- Feedback Info File Terpilih -->
                        <div id="selected-file-feedback" class="tw-hidden tw-mt-2.5 tw-flex tw-items-center tw-justify-between tw-rounded-lg tw-border tw-border-emerald-200 tw-bg-emerald-50 tw-px-3 tw-py-2 tw-text-xs tw-text-emerald-900">
                            <div class="tw-flex tw-items-center tw-gap-2 tw-min-w-0">
                                <span class="tw-text-emerald-600"><?php echo $icon('image'); ?></span>
                                <span id="selected-file-name" class="tw-font-medium tw-truncate">nama_file.jpg</span>
                                <span id="selected-file-size" class="tw-text-[11px] tw-text-emerald-700 tw-whitespace-nowrap">(1.2 MB)</span>
                            </div>
                            <button type="button" id="btn-clear-photo" class="tw-text-xs tw-font-bold tw-text-emerald-700 hover:tw-text-emerald-900 tw-ml-2 hover:tw-underline">
                                Batal
                            </button>
                        </div>
                    </div>

                    <!-- Form Submit Actions -->
                    <div class="tw-pt-4 tw-border-t tw-border-slate-100 tw-flex tw-items-center tw-justify-end">
                        <button type="submit" class="btn-ami tw-button-primary">
                            <?php echo $icon('check'); ?>
                            <span>Simpan perubahan</span>
                        </button>
                    </div>
                <?php echo form_close(); ?>
            </section>
        </div>
    </div>
</main>

<script>
(function () {
    var fileInput = document.getElementById('profile_photo');
    var dropzone = document.getElementById('photo-dropzone');
    var feedback = document.getElementById('selected-file-feedback');
    var fileNameSpan = document.getElementById('selected-file-name');
    var fileSizeSpan = document.getElementById('selected-file-size');
    var clearBtn = document.getElementById('btn-clear-photo');
    var labelMain = document.getElementById('file-label-main');

    var previewImg = document.getElementById('avatar-preview-img');
    var previewInitials = document.getElementById('avatar-preview-initials');
    var originalImgSrc = previewImg ? previewImg.getAttribute('src') : '';
    var hasOriginalPhoto = originalImgSrc !== '' && !originalImgSrc.endsWith('account/photo#');

    if (dropzone && fileInput) {
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
                fileInput.files = e.dataTransfer.files;
                var event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            }
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            if (fileInput.files && fileInput.files[0]) {
                var file = fileInput.files[0];
                var sizeMb = (file.size / (1024 * 1024)).toFixed(2);

                if (fileNameSpan) fileNameSpan.textContent = file.name;
                if (fileSizeSpan) fileSizeSpan.textContent = '(' + sizeMb + ' MB)';
                if (labelMain) labelMain.textContent = file.name;
                if (feedback) feedback.classList.remove('tw-hidden');

                // Instant Preview
                var reader = new FileReader();
                reader.onload = function (e) {
                    if (previewImg) {
                        previewImg.src = e.target.result;
                        previewImg.classList.remove('tw-hidden');
                    }
                    if (previewInitials) {
                        previewInitials.classList.add('tw-hidden');
                    }
                };
                reader.readAsDataURL(file);
            } else {
                resetFileInput();
            }
        });
    }

    function resetFileInput() {
        if (fileInput) fileInput.value = '';
        if (feedback) feedback.classList.add('tw-hidden');
        if (labelMain) labelMain.textContent = 'Klik untuk memilih berkas foto baru';

        if (hasOriginalPhoto && previewImg) {
            previewImg.src = originalImgSrc;
            previewImg.classList.remove('tw-hidden');
            if (previewInitials) previewInitials.classList.add('tw-hidden');
        } else {
            if (previewImg) {
                previewImg.src = '';
                previewImg.classList.add('tw-hidden');
            }
            if (previewInitials) {
                previewInitials.classList.remove('tw-hidden');
            }
        }
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            resetFileInput();
        });
    }
})();
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
