<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$document = isset($document) ? $document : NULL;
$selected_stage = $document && isset($document->stage) ? (string) $document->stage : 'penetapan';
$selected_category = $document && isset($document->category) ? (string) $document->category : '';
$selected_year = $document && isset($document->period_year) ? (int) $document->period_year : (int) date('Y');
$selected_stage_label = isset($stages[$selected_stage]) ? $stages[$selected_stage] : $selected_stage;
$selected_stage_categories = isset($categories[$selected_stage]) && is_array($categories[$selected_stage]) ? $categories[$selected_stage] : [];
$return_query = '?stage=' . rawurlencode($selected_stage) . '&year=' . rawurlencode((string) $selected_year);

$stage_badges = [
    'penetapan' => ['code' => '01', 'badge' => 'tw-bg-sky-50 tw-text-sky-700 tw-border-sky-200'],
    'pelaksanaan' => ['code' => '02', 'badge' => 'tw-bg-emerald-50 tw-text-emerald-700 tw-border-emerald-200'],
    'pengendalian' => ['code' => '03', 'badge' => 'tw-bg-amber-50 tw-text-amber-700 tw-border-amber-200'],
    'peningkatan' => ['code' => '04', 'badge' => 'tw-bg-indigo-50 tw-text-indigo-700 tw-border-indigo-200'],
];
$active_stage_meta = isset($stage_badges[$selected_stage]) ? $stage_badges[$selected_stage] : ['code' => '00', 'badge' => 'tw-bg-slate-50 tw-text-slate-700 tw-border-slate-200'];
?>

<div id="ppepp-documents-root" class="tw-p-4 md:tw-p-6 tw-max-w-4xl tw-mx-auto tw-space-y-5">

    <!-- 1. Compact Header -->
    <div class="tw-bg-white tw-rounded-xl tw-border tw-border-slate-200/80 tw-px-5 tw-py-4 tw-shadow-sm">
        <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-3">
            <div class="tw-space-y-1">
                <nav class="tw-flex tw-items-center tw-gap-1.5 tw-text-xs tw-text-slate-500" aria-label="Breadcrumb">
                    <a href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents') . $return_query); ?>" class="tw-text-slate-500 hover:tw-text-blue-600 tw-transition-colors">Dokumen <?php echo html_escape($selected_stage_label); ?></a>
                    <span class="tw-text-slate-300" aria-hidden="true">/</span>
                    <span class="tw-font-medium tw-text-slate-700"><?php echo html_escape($title); ?></span>
                </nav>
                <div class="tw-flex tw-items-center tw-gap-2.5">
                    <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-7 tw-h-7 tw-rounded-md tw-font-mono tw-text-xs tw-font-bold tw-border <?php echo $active_stage_meta['badge']; ?>">
                        <?php echo $active_stage_meta['code']; ?>
                    </span>
                    <h1 class="tw-text-lg md:tw-text-xl tw-font-bold tw-text-slate-900 tw-m-0 ami-section-title">
                        <?php echo html_escape($title); ?>
                    </h1>
                </div>
            </div>
            <a class="tw-inline-flex tw-items-center tw-justify-center tw-gap-1.5 tw-px-3.5 tw-h-10 tw-rounded-lg tw-border tw-border-slate-200 hover:tw-bg-slate-50 tw-text-slate-700 tw-text-xs md:tw-text-sm tw-font-medium tw-transition-colors btn-ami btn-outline-ami" href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents') . $return_query); ?>">
                <svg class="tw-w-4 tw-h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Validation error alert -->
    <?php echo validation_errors('<div class="tw-bg-rose-50 tw-border tw-border-rose-200 tw-text-rose-700 tw-text-sm tw-p-4 tw-rounded-xl alert alert-danger">', '</div>'); ?>

    <!-- Main Form -->
    <?php echo form_open_multipart($action, ['class' => 'tw-space-y-5 needs-validation', 'id' => 'ppepp-document-form']); ?>

        <!-- 2. Section: Informasi Dokumen -->
        <div class="tw-bg-white tw-rounded-xl tw-border tw-border-slate-200/80 tw-p-5 md:tw-p-6 tw-shadow-sm ami-panel tw-space-y-4">
            <div class="tw-flex tw-items-center tw-gap-2 tw-pb-3 tw-border-b tw-border-slate-100">
                <svg class="tw-w-4 tw-h-4 tw-text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h2 class="tw-text-sm tw-font-bold tw-text-slate-800 tw-m-0">Informasi Dokumen</h2>
            </div>

            <!-- Desktop 2-column grid -->
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                <!-- Tahap PPEPP (Readonly Context) -->
                <div class="tw-space-y-1.5 form-group">
                    <label for="ppepp-stage" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700">
                        Tahap PPEPP <span class="tw-text-rose-500">*</span>
                    </label>
                    <div class="tw-flex tw-items-center tw-gap-2.5 tw-px-3 tw-h-11 tw-rounded-lg tw-bg-slate-50 tw-border tw-border-slate-200">
                        <span class="tw-w-2 tw-h-2 tw-rounded-full tw-bg-blue-600 tw-flex-shrink-0"></span>
                        <input class="form-control-plaintext tw-bg-transparent tw-border-0 tw-p-0 tw-text-xs md:tw-text-sm tw-font-semibold tw-text-slate-800 tw-w-full focus:tw-outline-none" id="ppepp-stage" type="text" value="<?php echo html_escape($selected_stage_label); ?>" readonly>
                        <input type="hidden" name="stage" value="<?php echo html_escape($selected_stage); ?>">
                    </div>
                    <span class="tw-block tw-text-[11px] tw-text-slate-500">Tahap terkunci sesuai konteks navigasi siklus PPEPP.</span>
                </div>

                <!-- Kategori Dokumen -->
                <div class="tw-space-y-1.5 form-group">
                    <label for="ppepp-category" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700">
                        Kategori Dokumen <span class="tw-text-rose-500">*</span>
                    </label>
                    <select class="form-control tw-w-full tw-text-xs md:tw-text-sm tw-h-11 tw-px-3 tw-rounded-lg tw-border tw-border-slate-200 focus:tw-border-blue-500 focus:tw-ring-2 focus:tw-ring-blue-100 tw-bg-white tw-outline-none" id="ppepp-category" name="category" required>
                        <option value="">Pilih kategori dokumen...</option>
                        <?php foreach ($selected_stage_categories as $category_key => $label): ?>
                            <option value="<?php echo html_escape($category_key); ?>" <?php echo $selected_category === (string) $category_key ? 'selected' : ''; ?>>
                                <?php echo html_escape($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="tw-block tw-text-[11px] tw-text-slate-500">Klasifikasi dokumen pada tahap <?php echo html_escape($selected_stage_label); ?>.</span>
                </div>

                <!-- Tahun / Siklus -->
                <div class="tw-space-y-1.5 form-group">
                    <label for="ppepp-period-year" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700">
                        Tahun Dokumen / Siklus <span class="tw-text-rose-500">*</span>
                    </label>
                    <input class="form-control tw-w-full tw-text-xs md:tw-text-sm tw-h-11 tw-px-3 tw-rounded-lg tw-border tw-border-slate-200 focus:tw-border-blue-500 focus:tw-ring-2 focus:tw-ring-blue-100 tw-outline-none" id="ppepp-period-year" name="period_year" type="number" min="2000" max="<?php echo html_escape((string) ((int) date('Y') + 1)); ?>" value="<?php echo html_escape((string) $selected_year); ?>" required>
                    <span class="tw-block tw-text-[11px] tw-text-slate-500">Tahun siklus operasional standar.</span>
                </div>

                <!-- Tanggal Dokumen -->
                <div class="tw-space-y-1.5 form-group">
                    <label for="ppepp-document-date" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700">
                        Tanggal Dokumen
                    </label>
                    <input class="form-control tw-w-full tw-text-xs md:tw-text-sm tw-h-11 tw-px-3 tw-rounded-lg tw-border tw-border-slate-200 focus:tw-border-blue-500 focus:tw-ring-2 focus:tw-ring-blue-100 tw-outline-none" id="ppepp-document-date" name="document_date" type="date" value="<?php echo html_escape($document->document_date ?? ''); ?>">
                    <span class="tw-block tw-text-[11px] tw-text-slate-500">Tanggal penetapan atau pengesahan dokumen resmi.</span>
                </div>
            </div>

            <!-- Judul Dokumen (Full Width) -->
            <div class="tw-space-y-1.5 form-group tw-pt-1">
                <label for="ppepp-title" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700">
                    Judul Dokumen <span class="tw-text-rose-500">*</span>
                </label>
                <input class="form-control tw-w-full tw-text-xs md:tw-text-sm tw-h-11 tw-px-3.5 tw-rounded-lg tw-border tw-border-slate-200 focus:tw-border-blue-500 focus:tw-ring-2 focus:tw-ring-blue-100 tw-outline-none" id="ppepp-title" name="title" type="text" maxlength="200" value="<?php echo html_escape($document->title ?? ''); ?>" placeholder="Contoh: Standar Kompetensi Lulusan TA 2024/2025" required>
            </div>

            <!-- Deskripsi (Full Width) -->
            <div class="tw-space-y-1.5 form-group">
                <label for="ppepp-description" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700">
                    Deskripsi Ringkas / Catatan Dokumen
                </label>
                <textarea class="form-control tw-w-full tw-text-xs md:tw-text-sm tw-p-3.5 tw-rounded-lg tw-border tw-border-slate-200 focus:tw-border-blue-500 focus:tw-ring-2 focus:tw-ring-blue-100 tw-outline-none tw-resize-y tw-min-h-[120px]" id="ppepp-description" name="description" rows="4" maxlength="10000" placeholder="Keterangan ringkas isi dokumen, nomor SK penetapan, atau catatan penjelas terkait standar..."><?php echo html_escape($document->description ?? ''); ?></textarea>
            </div>
        </div>

        <!-- 3. Section: Sumber Dokumen -->
        <div class="tw-bg-white tw-rounded-xl tw-border tw-border-slate-200/80 tw-p-5 md:tw-p-6 tw-shadow-sm ami-panel tw-space-y-4">
            <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-1 tw-pb-3 tw-border-b tw-border-slate-100">
                <div class="tw-flex tw-items-center tw-gap-2">
                    <svg class="tw-w-4 tw-h-4 tw-text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <h2 class="tw-text-sm tw-font-bold tw-text-slate-800 tw-m-0">Sumber Dokumen</h2>
                </div>
                <span class="tw-text-[11px] md:tw-text-xs tw-text-slate-500">Tambahkan file, URL eksternal, atau keduanya.</span>
            </div>

            <!-- Compact Informational Callout -->
            <div class="tw-bg-slate-50 tw-border tw-border-slate-200 tw-rounded-lg tw-p-3 tw-text-xs tw-text-slate-600 tw-flex tw-items-start tw-gap-2.5">
                <svg class="tw-w-4 tw-h-4 tw-text-blue-600 tw-flex-shrink-0 tw-mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
                </svg>
                <div class="tw-leading-relaxed">
                    <span class="tw-font-semibold tw-text-slate-700">Ketentuan Sumber:</span> Unggah file arsip atau masukkan link repositori eksternal (minimal salah satu). Pada pembaruan dokumen, file/URL lama tetap tersimpan bila tidak diganti.
                </div>
            </div>

            <!-- Existing Stored File Status Card (Edit Mode) -->
            <?php if ($document && (!empty($document->original_name) || !empty($document->external_url))): ?>
                <div class="tw-bg-blue-50/50 tw-border tw-border-blue-100 tw-rounded-lg tw-p-3 tw-space-y-1.5">
                    <div class="tw-text-[11px] tw-font-bold tw-text-blue-900 tw-uppercase tw-tracking-wider">Sumber Dokumen Tersimpan</div>
                    <div class="tw-flex tw-flex-wrap tw-gap-2">
                        <?php if (!empty($document->original_name)): ?>
                            <div class="tw-inline-flex tw-items-center tw-gap-2 tw-px-3 tw-py-1.5 tw-bg-white tw-rounded-md tw-border tw-border-blue-200/80 tw-text-xs tw-text-slate-800 shadow-2xs">
                                <svg class="tw-w-4 tw-h-4 tw-text-blue-600 tw-flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="tw-font-medium"><?php echo html_escape($document->original_name); ?></span>
                                <span class="tw-text-[10px] tw-bg-blue-100 tw-text-blue-700 tw-px-1.5 tw-py-0.5 tw-rounded">Tersimpan</span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($document->external_url)): ?>
                            <div class="tw-inline-flex tw-items-center tw-gap-2 tw-px-3 tw-py-1.5 tw-bg-white tw-rounded-md tw-border tw-border-blue-200/80 tw-text-xs tw-text-slate-800 shadow-2xs">
                                <svg class="tw-w-4 tw-h-4 tw-text-emerald-600 tw-flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                <span class="tw-font-medium tw-truncate tw-max-w-xs"><?php echo html_escape($document->external_url); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Custom File Dropzone -->
            <div class="tw-space-y-1.5 form-group">
                <label for="ppepp-document-file" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700">
                    File Dokumen (Arsip Lokal)
                </label>
                <div id="ppepp-dropzone-container" class="ppepp-dropzone tw-relative tw-border-2 tw-border-dashed tw-border-slate-200 hover:tw-border-blue-400 tw-rounded-xl tw-p-5 tw-bg-slate-50/50 hover:tw-bg-blue-50/20 tw-text-center tw-cursor-pointer tw-transition-colors">
                    <!-- Real native file input (preserved for browser/CI handling) -->
                    <input class="form-control-file tw-absolute tw-inset-0 tw-w-full tw-h-full tw-opacity-0 tw-cursor-pointer tw-z-10" id="ppepp-document-file" name="document_file" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                    <div class="tw-space-y-2 pointer-events-none">
                        <div class="tw-w-10 tw-h-10 tw-mx-auto tw-rounded-full tw-bg-blue-100/70 tw-text-blue-600 tw-flex tw-items-center tw-justify-center">
                            <svg class="tw-w-5 tw-h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <div class="tw-text-xs md:tw-text-sm tw-text-slate-700">
                            <span class="tw-font-semibold tw-text-blue-600">Klik untuk memilih file</span> atau seret file ke sini
                        </div>
                        <p class="tw-text-[11px] tw-text-slate-500 tw-m-0">
                            Mendukung file PDF, Word (.doc/.docx), Excel (.xls/.xlsx), PPT (.ppt/.pptx) (Maks. <?php echo html_escape((string) $upload_limit_mib); ?> MiB)
                        </p>
                    </div>

                    <!-- Selected File Feedback -->
                    <div id="ppepp-file-feedback" class="tw-hidden tw-mt-3 tw-pt-3 tw-border-t tw-border-slate-200/80 tw-text-xs tw-text-left tw-flex tw-items-center tw-justify-between tw-bg-white tw-p-2.5 tw-rounded-lg tw-border tw-border-slate-200">
                        <div class="tw-flex tw-items-center tw-gap-2 tw-min-w-0">
                            <svg class="tw-w-4 tw-h-4 tw-text-blue-600 tw-flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span id="ppepp-filename-display" class="tw-font-medium tw-text-slate-800 tw-truncate">Nama file</span>
                            <span id="ppepp-filesize-display" class="tw-text-slate-400 tw-text-[11px] tw-flex-shrink-0">(0 KB)</span>
                        </div>
                        <button type="button" id="ppepp-file-clear-btn" class="tw-text-slate-400 hover:tw-text-rose-600 tw-p-1 tw-rounded hover:tw-bg-slate-100 tw-transition-colors" title="Batalkan pilihan file">
                            <svg class="tw-w-4 tw-h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- URL External -->
            <div class="tw-space-y-1.5 form-group tw-pt-1">
                <label for="ppepp-external-url" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700">
                    URL Eksternal (Cloud Storage / Google Drive / Repository)
                </label>
                <div class="tw-relative">
                    <div class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3.5 tw-flex tw-items-center tw-pointer-events-none tw-text-slate-400">
                        <svg class="tw-w-4 tw-h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </div>
                    <input class="form-control tw-w-full tw-text-xs md:tw-text-sm tw-h-11 tw-pl-10 tw-pr-3.5 tw-rounded-lg tw-border tw-border-slate-200 focus:tw-border-blue-500 focus:tw-ring-2 focus:tw-ring-blue-100 tw-outline-none" id="ppepp-external-url" name="external_url" type="url" maxlength="500" value="<?php echo html_escape($document->external_url ?? ''); ?>" placeholder="https://drive.google.com/...">
                </div>
                <span class="tw-block tw-text-[11px] tw-text-slate-500">Tautan langsung file atau folder repositori eksternal yang dapat diakses oleh auditor/auditi.</span>
            </div>
        </div>

        <!-- 4. Action Bar (Right-aligned desktop, full-width touch mobile) -->
        <div class="tw-flex tw-flex-col-reverse sm:tw-flex-row sm:tw-items-center sm:tw-justify-end tw-gap-2.5 tw-pt-2">
            <a class="tw-inline-flex tw-items-center tw-justify-center tw-px-5 tw-h-11 tw-rounded-lg tw-border tw-border-slate-200 hover:tw-bg-slate-50 tw-text-slate-700 tw-text-xs md:tw-text-sm tw-font-medium tw-transition-colors btn-ami btn-outline-ami tw-w-full sm:tw-w-auto" href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents') . $return_query); ?>">
                Batal
            </a>
            <button class="tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-px-6 tw-h-11 tw-rounded-lg tw-bg-blue-600 hover:tw-bg-blue-700 tw-text-white tw-text-xs md:tw-text-sm tw-font-medium tw-shadow-sm tw-transition-colors btn-ami btn-primary tw-w-full sm:tw-w-auto" type="submit">
                <svg class="tw-w-4 tw-h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                <span>Simpan dokumen</span>
            </button>
        </div>

    <?php echo form_close(); ?>
</div>

<script>
(function() {
    var fileInput = document.getElementById('ppepp-document-file');
    var dropzone = document.getElementById('ppepp-dropzone-container');
    var feedback = document.getElementById('ppepp-file-feedback');
    var nameDisplay = document.getElementById('ppepp-filename-display');
    var sizeDisplay = document.getElementById('ppepp-filesize-display');
    var clearBtn = document.getElementById('ppepp-file-clear-btn');

    if (!fileInput || !dropzone || !feedback) return;

    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    function updateFileFeedback() {
        if (fileInput.files && fileInput.files.length > 0) {
            var file = fileInput.files[0];
            nameDisplay.textContent = file.name;
            sizeDisplay.textContent = '(' + formatBytes(file.size) + ')';
            feedback.classList.remove('tw-hidden');
        } else {
            feedback.classList.add('tw-hidden');
            nameDisplay.textContent = '';
            sizeDisplay.textContent = '';
        }
    }

    fileInput.onchange = updateFileFeedback;

    if (clearBtn) {
        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            fileInput.value = '';
            updateFileFeedback();
        });
    }

    // Drag & drop styling
    ['dragenter', 'dragover'].forEach(function(eventName) {
        dropzone.addEventListener(eventName, function(e) {
            dropzone.classList.add('is-dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(function(eventName) {
        dropzone.addEventListener(eventName, function(e) {
            dropzone.classList.remove('is-dragover');
        }, false);
    });
})();
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
