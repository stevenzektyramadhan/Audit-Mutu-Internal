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

<div id="ppepp-documents-root" class="tw-p-4 md:tw-p-6 tw-max-w-5xl tw-mx-auto tw-space-y-6">

    <!-- Top Card Header -->
    <div class="tw-bg-white tw-rounded-xl tw-border tw-border-slate-200/80 tw-p-5 md:tw-p-6 tw-shadow-sm">
        <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-4">
            <div class="tw-space-y-1">
                <nav class="tw-flex tw-items-center tw-gap-2 tw-text-xs tw-text-slate-500 tw-mb-1" aria-label="Breadcrumb">
                    <a href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents') . $return_query); ?>" class="tw-text-slate-500 hover:tw-text-blue-600 tw-transition-colors">Dokumen <?php echo html_escape($selected_stage_label); ?></a>
                    <span class="tw-text-slate-300" aria-hidden="true">/</span>
                    <span class="tw-font-medium tw-text-slate-800"><?php echo html_escape($title); ?></span>
                </nav>
                <div class="tw-flex tw-items-center tw-gap-3">
                    <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-8 tw-h-8 tw-rounded-lg tw-font-mono tw-text-xs tw-font-bold tw-border <?php echo $active_stage_meta['badge']; ?>">
                        <?php echo $active_stage_meta['code']; ?>
                    </span>
                    <h1 class="tw-text-xl tw-font-bold tw-text-slate-900 tw-m-0 ami-section-title">
                        <?php echo html_escape($title); ?>
                    </h1>
                </div>
                <p class="tw-text-xs md:tw-text-sm tw-text-slate-500 tw-m-0">
                    Simpan file arsip (PDF/Office), link URL eksternal, atau keduanya ke dalam repository PPEPP.
                </p>
            </div>
            <a class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-3.5 tw-py-2 tw-rounded-lg tw-border tw-border-slate-200 hover:tw-bg-slate-50 tw-text-slate-700 tw-text-sm tw-font-medium tw-transition-colors btn-ami btn-outline-ami" href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents') . $return_query); ?>">
                <svg class="tw-w-4 tw-h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Validation error alert -->
    <?php echo validation_errors('<div class="tw-bg-rose-50 tw-border tw-border-rose-200 tw-text-rose-700 tw-text-sm tw-p-4 tw-rounded-xl alert alert-danger">', '</div>'); ?>

    <!-- Information & Existing Metadata Notice -->
    <div class="tw-bg-blue-50/60 tw-border tw-border-blue-200/80 tw-rounded-xl tw-p-4 tw-text-xs md:tw-text-sm tw-text-blue-900 tw-flex tw-items-start tw-gap-3 alert alert-light">
        <svg class="tw-w-5 tw-h-5 tw-text-blue-600 tw-flex-shrink-0 tw-mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" /></svg>
        <div class="tw-space-y-1">
            <div>
                <span class="tw-font-semibold">Ketentuan Sumber Dokumen:</span> File upload dan link URL eksternal bersifat independen, namun minimal salah satu wajib diisi. Pada mode edit, file/URL yang sudah tersimpan akan tetap dipertahankan jika Anda tidak mengunggah file baru.
            </div>
            <?php if ($document && (!empty($document->original_name) || !empty($document->external_url))): ?>
                <div class="tw-mt-2 tw-pt-2 tw-border-t tw-border-blue-200/60 tw-text-xs tw-text-blue-800">
                    <span class="tw-font-bold">Metadata saat ini:</span>
                    <?php if (!empty($document->original_name)): ?>
                        <span class="tw-inline-flex tw-items-center tw-gap-1 tw-ml-1 tw-px-2 tw-py-0.5 tw-bg-white tw-rounded tw-border tw-border-blue-200">
                            <svg class="tw-w-3 tw-h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                            File: <?php echo html_escape($document->original_name); ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($document->external_url)): ?>
                        <span class="tw-inline-flex tw-items-center tw-gap-1 tw-ml-1 tw-px-2 tw-py-0.5 tw-bg-white tw-rounded tw-border tw-border-blue-200">
                            <svg class="tw-w-3 tw-h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                            URL eksternal tersimpan
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Form Container -->
    <div class="tw-bg-white tw-rounded-xl tw-border tw-border-slate-200/80 tw-p-5 md:tw-p-6 tw-shadow-sm ami-panel">
        <?php echo form_open_multipart($action, ['class' => 'tw-space-y-5 needs-validation']); ?>

            <!-- Row 1: Locked Stage & Category Selection -->
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-5">
                <!-- Locked Stage Context -->
                <div class="tw-space-y-1.5 form-group">
                    <label for="ppepp-stage" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700">
                        Tahap PPEPP <span class="tw-text-rose-500">*</span>
                    </label>
                    <div class="tw-flex tw-items-center tw-gap-2.5 tw-px-3.5 tw-py-2.5 tw-rounded-lg tw-bg-slate-50 tw-border tw-border-slate-200">
                        <span class="tw-w-2 tw-h-2 tw-rounded-full tw-bg-blue-600"></span>
                        <input class="form-control-plaintext tw-bg-transparent tw-border-0 tw-p-0 tw-text-sm tw-font-semibold tw-text-slate-800 tw-w-full focus:tw-outline-none" id="ppepp-stage" type="text" value="<?php echo html_escape($selected_stage_label); ?>" readonly>
                        <input type="hidden" name="stage" value="<?php echo html_escape($selected_stage); ?>">
                    </div>
                    <span class="tw-block tw-text-[11px] tw-text-slate-500">Tahap terkunci sesuai konteks navigasi siklus PPEPP.</span>
                </div>

                <!-- Category -->
                <div class="tw-space-y-1.5 form-group">
                    <label for="ppepp-category" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700">
                        Kategori Dokumen <span class="tw-text-rose-500">*</span>
                    </label>
                    <select class="form-control tw-w-full tw-text-sm tw-py-2.5 tw-px-3 tw-rounded-lg tw-border tw-border-slate-200 focus:tw-border-blue-500 focus:tw-ring-2 focus:tw-ring-blue-100 tw-bg-white tw-outline-none" id="ppepp-category" name="category" required>
                        <option value="">Pilih kategori dokumen...</option>
                        <?php foreach ($selected_stage_categories as $category_key => $label): ?>
                            <option value="<?php echo html_escape($category_key); ?>" <?php echo $selected_category === (string) $category_key ? 'selected' : ''; ?>>
                                <?php echo html_escape($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="tw-block tw-text-[11px] tw-text-slate-500">Pilih klasifikasi kategori yang sesuai pada tahap <?php echo html_escape($selected_stage_label); ?>.</span>
                </div>
            </div>

            <!-- Row 2: Period Year, Document Date, File Upload -->
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-5 tw-pt-2 tw-border-t tw-border-slate-100">
                <!-- Period Year -->
                <div class="tw-space-y-1.5 form-group">
                    <label for="ppepp-period-year" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700">
                        Tahun Dokumen / Siklus <span class="tw-text-rose-500">*</span>
                    </label>
                    <input class="form-control tw-w-full tw-text-sm tw-py-2 tw-px-3 tw-rounded-lg tw-border tw-border-slate-200 focus:tw-border-blue-500 focus:tw-ring-2 focus:tw-ring-blue-100 tw-outline-none" id="ppepp-period-year" name="period_year" type="number" min="2000" max="<?php echo html_escape((string) ((int) date('Y') + 1)); ?>" value="<?php echo html_escape((string) $selected_year); ?>" required>
                    <span class="tw-block tw-text-[11px] tw-text-slate-500">Tahun siklus operasional standar.</span>
                </div>

                <!-- Document Date -->
                <div class="tw-space-y-1.5 form-group">
                    <label for="ppepp-document-date" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700">
                        Tanggal Dokumen
                    </label>
                    <input class="form-control tw-w-full tw-text-sm tw-py-2 tw-px-3 tw-rounded-lg tw-border tw-border-slate-200 focus:tw-border-blue-500 focus:tw-ring-2 focus:tw-ring-blue-100 tw-outline-none" id="ppepp-document-date" name="document_date" type="date" value="<?php echo html_escape($document->document_date ?? ''); ?>">
                    <span class="tw-block tw-text-[11px] tw-text-slate-500">Tanggal penetapan atau pengesahan dokumen.</span>
                </div>

                <!-- Document File -->
                <div class="tw-space-y-1.5 form-group">
                    <label for="ppepp-document-file" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700">
                        Unggah File Dokumen
                    </label>
                    <input class="form-control-file tw-w-full tw-text-xs tw-text-slate-500 file:tw-mr-3 file:tw-py-2 file:tw-px-3 file:tw-rounded-lg file:tw-border-0 file:tw-text-xs file:tw-font-medium file:tw-bg-blue-50 file:tw-text-blue-700 hover:file:tw-bg-blue-100 tw-cursor-pointer" id="ppepp-document-file" name="document_file" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                    <span class="tw-block tw-text-[11px] tw-text-slate-500">PDF, Word, Excel, PPT (Maks. <?php echo html_escape((string) $upload_limit_mib); ?> MiB).</span>
                </div>
            </div>

            <!-- Row 3: Title -->
            <div class="tw-space-y-1.5 form-group tw-pt-2 tw-border-t tw-border-slate-100">
                <label for="ppepp-title" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700">
                    Judul Dokumen <span class="tw-text-rose-500">*</span>
                </label>
                <input class="form-control tw-w-full tw-text-sm tw-py-2.5 tw-px-3 tw-rounded-lg tw-border tw-border-slate-200 focus:tw-border-blue-500 focus:tw-ring-2 focus:tw-ring-blue-100 tw-outline-none" id="ppepp-title" name="title" type="text" maxlength="200" value="<?php echo html_escape($document->title ?? ''); ?>" placeholder="Contoh: Standar Kompetensi Lulusan TA 2024/2025" required>
            </div>

            <!-- Row 4: Description -->
            <div class="tw-space-y-1.5 form-group">
                <label for="ppepp-description" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700">
                    Deskripsi Ringkas / Catatan Dokumen
                </label>
                <textarea class="form-control tw-w-full tw-text-sm tw-py-2.5 tw-px-3 tw-rounded-lg tw-border tw-border-slate-200 focus:tw-border-blue-500 focus:tw-ring-2 focus:tw-ring-blue-100 tw-outline-none tw-resize-y" id="ppepp-description" name="description" rows="4" maxlength="10000" placeholder="Keterangan isi dokumen, nomor SK, atau catatan penting terkait standar..."><?php echo html_escape($document->description ?? ''); ?></textarea>
            </div>

            <!-- Row 5: External URL -->
            <div class="tw-space-y-1.5 form-group">
                <label for="ppepp-external-url" class="tw-block tw-text-xs tw-font-semibold tw-text-slate-700">
                    URL Eksternal (Cloud Storage / Google Drive / Repository)
                </label>
                <input class="form-control tw-w-full tw-text-sm tw-py-2.5 tw-px-3 tw-rounded-lg tw-border tw-border-slate-200 focus:tw-border-blue-500 focus:tw-ring-2 focus:tw-ring-blue-100 tw-outline-none" id="ppepp-external-url" name="external_url" type="url" maxlength="500" value="<?php echo html_escape($document->external_url ?? ''); ?>" placeholder="https://drive.google.com/...">
                <span class="tw-block tw-text-[11px] tw-text-slate-500">Tautan tautan langsung file atau folder repositori eksternal yang dapat diakses oleh auditor/auditee.</span>
            </div>

            <!-- Bottom Actions -->
            <div class="tw-flex tw-items-center tw-justify-end tw-gap-3 tw-pt-4 tw-border-t tw-border-slate-100">
                <a class="tw-px-4 tw-py-2.5 tw-rounded-lg tw-border tw-border-slate-200 hover:tw-bg-slate-50 tw-text-slate-700 tw-text-sm tw-font-medium tw-transition-colors btn-ami btn-outline-ami" href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents') . $return_query); ?>">
                    Batal
                </a>
                <button class="tw-inline-flex tw-items-center tw-gap-2 tw-px-5 tw-py-2.5 tw-rounded-lg tw-bg-blue-600 hover:tw-bg-blue-700 tw-text-white tw-text-sm tw-font-medium tw-shadow-sm tw-transition-colors btn-ami btn-primary" type="submit">
                    <svg class="tw-w-4 tw-h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                    <span>Simpan dokumen</span>
                </button>
            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
