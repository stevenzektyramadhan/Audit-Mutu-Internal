<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$selected_stage = isset($selected_stage) ? (string) $selected_stage : 'penetapan';
$selected_year = isset($selected_year) ? (int) $selected_year : (int) date('Y');
$ppepp_stage_label = isset($stages[$selected_stage]) ? $stages[$selected_stage] : $selected_stage;
$stage_categories = isset($categories[$selected_stage]) && is_array($categories[$selected_stage]) ? $categories[$selected_stage] : [];
$checklist_categories = isset($penetapan_core_categories) && is_array($penetapan_core_categories) ? $penetapan_core_categories : [];
$year_options = [];
foreach ((array) $years as $year) {
    $year_value = is_object($year) ? $year->period_year : $year;
    $year_options[(string) $year_value] = $year_value;
}
$current_year = (string) date('Y');
$year_options[$current_year] = $current_year;
$query = '?stage=' . rawurlencode($selected_stage) . '&year=' . rawurlencode((string) $selected_year);
$stage_descriptions = [
    'penetapan' => 'Arsip kebijakan, manual, formulir, dan standar sebagai fondasi SPMI.',
    'pelaksanaan' => 'Arsip pelaksanaan standar dan bukti kegiatan pada tahun terpilih.',
    'pengendalian' => 'Arsip analisis penyebab dan tindakan koreksi untuk pengendalian mutu.',
    'peningkatan' => 'Arsip revisi standar dan rekomendasi peningkatan mutu berkelanjutan.',
];
$stage_icons = [
    'penetapan' => 'fa-compass',
    'pelaksanaan' => 'fa-play-circle',
    'pengendalian' => 'fa-shield-alt',
    'peningkatan' => 'fa-chart-line',
];
$stage_description = isset($stage_descriptions[$selected_stage]) ? $stage_descriptions[$selected_stage] : 'Arsip dokumen PPEPP SPMI pada tahap terpilih.';
$stage_icon = isset($stage_icons[$selected_stage]) ? $stage_icons[$selected_stage] : 'fa-folder-open';

$stage_badges = [
    'penetapan' => ['code' => '01', 'badge' => 'tw-bg-sky-50 tw-text-sky-700 tw-border-sky-200', 'desc' => 'Fondasi tata kelola: Kebijakan, Manual, Standar, Formulir, dan Mekanisme SPMI.'],
    'pelaksanaan' => ['code' => '02', 'badge' => 'tw-bg-emerald-50 tw-text-emerald-700 tw-border-emerald-200', 'desc' => 'Arsip bukti implementasi dan laporan kegiatan operasional standar SPMI.'],
    'pengendalian' => ['code' => '03', 'badge' => 'tw-bg-amber-50 tw-text-amber-700 tw-border-amber-200', 'desc' => 'Arsip analisis ketidaktercapaian standar dan perumusan tindakan koreksi.'],
    'peningkatan' => ['code' => '04', 'badge' => 'tw-bg-indigo-50 tw-text-indigo-700 tw-border-indigo-200', 'desc' => 'Arsip continuous quality improvement (CQI) dan penetapan/revisi standar baru.'],
];
$active_stage_meta = isset($stage_badges[$selected_stage]) ? $stage_badges[$selected_stage] : ['code' => '00', 'badge' => 'tw-bg-slate-50 tw-text-slate-700 tw-border-slate-200', 'desc' => 'Dokumen arsip SPMI PPEPP.'];

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<div id="ppepp-documents-root" class="tw-p-4 md:tw-p-6 tw-space-y-6">

    <!-- Top Hero Header -->
    <div class="tw-bg-white tw-rounded-xl tw-border tw-border-slate-200/80 tw-p-5 md:tw-p-6 tw-shadow-sm">
        <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-justify-between tw-gap-4">
            <div class="tw-space-y-1.5 ppepp-heading">
                <div class="ppepp-heading-icon tw-sr-only" aria-hidden="true"><i class="fas <?php echo html_escape($stage_icon); ?>"></i></div>
                <nav class="tw-flex tw-items-center tw-gap-2 tw-text-xs tw-text-slate-500 ppepp-breadcrumb" aria-label="Breadcrumb">
                    <a href="<?php echo html_escape(site_url('lpmpi/spmi-dashboard')); ?>" class="tw-text-slate-500 hover:tw-text-blue-600 tw-transition-colors">Beranda</a>
                    <span class="tw-text-slate-300" aria-hidden="true">/</span>
                    <span class="tw-text-slate-500">Dokumen PPEPP</span>
                    <span class="tw-text-slate-300" aria-hidden="true">/</span>
                    <span class="tw-font-medium tw-text-slate-800">Dokumen <?php echo html_escape($ppepp_stage_label); ?></span>
                </nav>
                <div class="tw-flex tw-items-center tw-gap-3">
                    <span class="tw-inline-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-rounded-lg tw-font-mono tw-text-sm tw-font-bold tw-border <?php echo $active_stage_meta['badge']; ?>">
                        <?php echo $active_stage_meta['code']; ?>
                    </span>
                    <div>
                        <h1 class="tw-text-xl md:tw-text-2xl tw-font-bold tw-text-slate-900 tw-tracking-tight tw-m-0 ami-section-title">
                            Dokumen <?php echo html_escape($ppepp_stage_label); ?>
                        </h1>
                        <p class="tw-text-xs md:tw-text-sm tw-text-slate-500 tw-m-0 tw-mt-0.5 text-muted">
                            <?php echo html_escape($stage_description); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="tw-flex tw-items-center tw-w-full sm:tw-w-auto sm:tw-flex-shrink-0">
                <a class="btn ppepp-primary-action tw-w-full sm:tw-w-auto" href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents/create') . $query); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Tambah dokumen</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Stage Specific Banners & Callouts -->
    <?php if ($selected_stage === 'penetapan'): ?>
        <!-- Penetapan Readiness Checklist -->
        <section class="tw-bg-white tw-rounded-xl tw-border tw-border-slate-200/80 tw-p-5 tw-shadow-sm" aria-labelledby="penetapan-checklist-title">
            <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center tw-justify-between tw-gap-2 tw-mb-4 tw-pb-3 tw-border-b tw-border-slate-100">
                <div>
                    <h2 class="tw-text-base tw-font-bold tw-text-slate-800 tw-m-0" id="penetapan-checklist-title">Checklist Kesiapan Penetapan SPMI</h2>
                    <p class="tw-text-xs tw-text-slate-500 tw-m-0 tw-mt-0.5">Kelengkapan 5 pilar dokumen inti SPMI untuk siklus tahun <?php echo html_escape((string) $selected_year); ?>.</p>
                </div>
                <div class="tw-flex tw-items-center tw-gap-2">
                    <?php
                    $complete_count = 0;
                    foreach ($checklist_categories as $chk_key) {
                        if (isset($checklist[$chk_key]) && (int) $checklist[$chk_key] > 0) {
                            $complete_count++;
                        }
                    }
                    $total_core = count($checklist_categories);
                    $pct = $total_core > 0 ? round(($complete_count / $total_core) * 100) : 0;
                    ?>
                    <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-2.5 tw-py-1 tw-rounded-full tw-text-xs tw-font-semibold <?php echo $pct === 100 ? 'tw-bg-emerald-50 tw-text-emerald-700 tw-border tw-border-emerald-200' : 'tw-bg-amber-50 tw-text-amber-700 tw-border tw-border-amber-200'; ?>">
                        <span class="tw-w-1.5 tw-h-1.5 tw-rounded-full <?php echo $pct === 100 ? 'tw-bg-emerald-500' : 'tw-bg-amber-500'; ?>"></span>
                        <?php echo $complete_count; ?>/<?php echo $total_core; ?> Pilar Lengkap (<?php echo $pct; ?>%)
                    </span>
                    <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-1 tw-rounded-md tw-bg-slate-100 tw-text-slate-600 tw-text-xs tw-font-semibold tw-border tw-border-slate-200">
                        Tahun <?php echo html_escape((string) $selected_year); ?>
                    </span>
                </div>
            </div>

            <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-5 tw-gap-3 ppepp-checklist-grid">
                <?php foreach ($checklist_categories as $category_key): ?>
                    <?php
                    $category_count = isset($checklist[$category_key]) ? (int) $checklist[$category_key] : 0;
                    $category_label = isset($stage_categories[$category_key]) ? $stage_categories[$category_key] : $category_key;
                    $is_ready = $category_count > 0;
                    ?>
                    <div class="ppepp-checklist-card tw-rounded-lg tw-border <?php echo $is_ready ? 'tw-border-emerald-200/90 tw-bg-emerald-50/30' : 'tw-border-slate-200 tw-bg-slate-50/50'; ?> tw-p-3.5 tw-flex tw-flex-col tw-justify-between tw-gap-2.5">
                        <div class="tw-flex tw-items-start tw-justify-between tw-gap-2">
                            <span class="tw-text-xs tw-font-semibold tw-text-slate-800 tw-line-clamp-2"><?php echo html_escape($category_label); ?></span>
                            <?php if ($is_ready): ?>
                                <svg class="tw-w-4 tw-h-4 tw-text-emerald-600 tw-flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <?php else: ?>
                                <svg class="tw-w-4 tw-h-4 tw-text-slate-400 tw-flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" /></svg>
                            <?php endif; ?>
                        </div>
                        <div class="ppepp-checklist-card-inner tw-flex tw-items-center tw-justify-between">
                            <span class="tw-text-[11px] tw-text-slate-500">Status arsip:</span>
                            <span class="badge <?php echo $is_ready ? 'badge-success tw-bg-emerald-100 tw-text-emerald-800 tw-border-emerald-200' : 'badge-secondary tw-bg-slate-200 tw-text-slate-600 tw-border-slate-300'; ?> tw-text-[11px] tw-px-2 tw-py-0.5 tw-rounded tw-font-medium">
                                <?php echo html_escape($category_count > 0 ? (string) $category_count . ' dokumen' : 'Belum ada'); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    <?php elseif ($selected_stage === 'pelaksanaan'): ?>
        <!-- Pelaksanaan Operational Overview Banner -->
        <div class="tw-bg-emerald-50/60 tw-rounded-xl tw-border tw-border-emerald-200/80 tw-p-4 tw-flex tw-items-center tw-gap-3.5">
            <div class="tw-w-9 tw-h-9 tw-rounded-lg tw-bg-emerald-100 tw-text-emerald-700 tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                <svg class="tw-w-5 tw-h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="tw-text-xs md:tw-text-sm tw-text-emerald-900">
                <span class="tw-font-bold">Tahap Pelaksanaan:</span> Bukti keterlaksanaan standar SPMI (Laporan Pelaksanaan, Notula, Bukti Kegiatan, dsb.) diarsipkan di sini untuk kemudian dievaluasi pada saat Audit Mutu Internal (AMI).
            </div>
        </div>

    <?php elseif ($selected_stage === 'pengendalian'): ?>
        <!-- Pengendalian Root Cause / Corrective Action Banner -->
        <div class="tw-bg-amber-50/70 tw-rounded-xl tw-border tw-border-amber-200/90 tw-p-4 tw-flex tw-items-center tw-gap-3.5">
            <div class="tw-w-9 tw-h-9 tw-rounded-lg tw-bg-amber-100 tw-text-amber-800 tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                <svg class="tw-w-5 tw-h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </div>
            <div class="tw-text-xs md:tw-text-sm tw-text-amber-900">
                <span class="tw-font-bold">Tahap Pengendalian Mutu:</span> Mengarsipkan dokumen analisis ketidaktercapaian indikator/standar serta rumusan tindakan koreksi pencegahan deviasi mutu sebelum masuk ke peningkatan.
            </div>
        </div>

    <?php elseif ($selected_stage === 'peningkatan'): ?>
        <!-- Peningkatan CQI Banner -->
        <div class="tw-bg-indigo-50/60 tw-rounded-xl tw-border tw-border-indigo-200/80 tw-p-4 tw-flex tw-items-center tw-gap-3.5">
            <div class="tw-w-9 tw-h-9 tw-rounded-lg tw-bg-indigo-100 tw-text-indigo-800 tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
                <svg class="tw-w-5 tw-h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
            </div>
            <div class="tw-text-xs md:tw-text-sm tw-text-indigo-900">
                <span class="tw-font-bold">Tahap Peningkatan (Continuous Quality Improvement):</span> Mengarsipkan rekomendasi peningkatan dan penetapan standar yang ditingkatkan/dinaikkan standarnya untuk menjadi siklus Penetapan tahun berikutnya.
            </div>
        </div>
    <?php endif; ?>

    <!-- Interactive Filtering Toolbar -->
    <div class="tw-bg-white tw-rounded-xl tw-border tw-border-slate-200/80 tw-p-4 tw-shadow-sm tw-space-y-3">
        <div class="tw-flex tw-flex-col lg:tw-flex-row lg:tw-items-center lg:tw-justify-between tw-gap-3 ppepp-toolbar">
            <!-- Real-time Search Box -->
            <div class="ppepp-search">
                <label for="ppepp-document-search" class="tw-sr-only">Cari dokumen</label>
                <div class="ppepp-search-control tw-relative tw-w-full">
                    <span class="tw-absolute tw-inset-y-0 tw-left-0 tw-pl-3 tw-flex tw-items-center tw-pointer-events-none tw-text-slate-400">
                        <svg class="tw-w-4 tw-h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35" /></svg>
                    </span>
                    <input type="search" class="form-control tw-w-full tw-pl-9 tw-pr-16 tw-py-2 tw-text-sm tw-rounded-lg tw-border tw-border-slate-200 focus:tw-border-blue-500 focus:tw-ring-2 focus:tw-ring-blue-100 tw-outline-none" id="ppepp-document-search" placeholder="Cari judul, kategori, uploader..." autocomplete="off">
                    <button type="button" class="ppepp-search-clear tw-absolute tw-inset-y-0 tw-right-0 tw-pr-3 tw-flex tw-items-center tw-text-xs tw-text-slate-400 hover:tw-text-slate-600" id="ppepp-document-search-clear" hidden>Hapus</button>
                </div>
            </div>

            <!-- Year Selector Form -->
            <div class="tw-flex tw-items-center tw-gap-2 tw-flex-shrink-0">
                <?php echo form_open('lpmpi/spmi-ppepp-documents', ['method' => 'get', 'class' => 'tw-flex tw-items-center tw-gap-2 tw-m-0']); ?>
                    <input type="hidden" name="stage" value="<?php echo html_escape($selected_stage); ?>">
                    <label class="tw-text-xs tw-font-semibold tw-text-slate-600 tw-m-0" for="ppepp-year">Tahun Dokumen:</label>
                    <select class="form-control tw-text-sm tw-py-1.5 tw-px-3 tw-rounded-lg tw-border tw-border-slate-200 focus:tw-border-blue-500 tw-bg-white" id="ppepp-year" name="year">
                        <?php foreach ($year_options as $year_value): ?>
                            <option value="<?php echo html_escape((string) $year_value); ?>" <?php echo (int) $year_value === $selected_year ? 'selected' : ''; ?>>
                                <?php echo html_escape((string) $year_value); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn-ami btn-outline-ami tw-px-3 tw-py-1.5 tw-rounded-lg tw-text-sm tw-border tw-border-slate-200 hover:tw-bg-slate-50 tw-font-medium tw-transition-colors" type="submit">Terapkan</button>
                <?php echo form_close(); ?>
            </div>
        </div>

        <!-- Category Filter Pills -->
        <?php if (!empty($stage_categories)): ?>
            <div class="tw-flex tw-items-center tw-gap-1.5 tw-overflow-x-auto tw-pt-2 tw-border-t tw-border-slate-100" id="ppepp-category-filter-bar">
                <span class="tw-text-[11px] tw-font-semibold tw-text-slate-400 tw-uppercase tw-tracking-wider tw-mr-1">Kategori:</span>
                <button type="button" class="ppepp-filter-pill active tw-px-2.5 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-transition-colors tw-bg-blue-600 tw-text-white" data-filter-category="all">
                    Semua (<?php echo count($documents); ?>)
                </button>
                <?php foreach ($stage_categories as $cat_key => $cat_name): ?>
                    <?php
                    $count_in_cat = 0;
                    foreach ($documents as $doc_item) {
                        if ($doc_item->category === $cat_key) $count_in_cat++;
                    }
                    ?>
                    <button type="button" class="ppepp-filter-pill tw-px-2.5 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-transition-colors tw-bg-slate-100 tw-text-slate-600 hover:tw-bg-slate-200" data-filter-category="<?php echo html_escape($cat_key); ?>">
                        <?php echo html_escape($cat_name); ?> (<?php echo $count_in_cat; ?>)
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Document Grid / Empty State -->
    <?php if (empty($documents)): ?>
        <div class="tw-bg-white tw-rounded-xl tw-border tw-border-slate-200/80 tw-p-12 tw-text-center ami-empty">
            <div class="tw-w-16 tw-h-16 tw-rounded-2xl tw-bg-slate-100 tw-text-slate-400 tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4 ami-empty-icon">
                <svg class="tw-w-8 tw-h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
            </div>
            <h3 class="tw-text-base tw-font-bold tw-text-slate-800 tw-mb-1 ami-empty-title">Belum ada dokumen</h3>
            <p class="tw-text-sm tw-text-slate-500 tw-max-w-md tw-mx-auto tw-mb-5">
                Belum ada arsip <?php echo html_escape($ppepp_stage_label); ?> untuk tahun <?php echo html_escape((string) $selected_year); ?>.
            </p>
            <a href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents/create') . $query); ?>" class="tw-inline-flex tw-items-center tw-gap-2 tw-px-4 tw-py-2.5 tw-rounded-lg tw-bg-blue-600 hover:tw-bg-blue-700 tw-text-white tw-font-medium tw-text-sm tw-shadow-sm tw-transition-colors btn-ami btn-primary">
                <svg class="tw-w-4 tw-h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                <span>Tambah dokumen pertama</span>
            </a>
        </div>
    <?php else: ?>
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 xl:tw-grid-cols-3 tw-gap-4 ppepp-document-grid" id="ppepp-document-grid">
            <?php foreach ($documents as $document): ?>
                <?php
                $category_label = isset($stage_categories[$document->category]) ? $stage_categories[$document->category] : $document->category;
                $has_file = !empty($document->stored_name);
                $has_url = !empty($document->external_url);
                ?>
                <article class="ppepp-document-card tw-bg-white tw-rounded-xl tw-border tw-border-slate-200/90 tw-p-5 tw-shadow-sm hover:tw-shadow-md hover:tw-border-slate-300 tw-transition-all tw-flex tw-flex-col tw-justify-between tw-gap-4" data-ppepp-document-card data-document-category="<?php echo html_escape($document->category); ?>">
                    <div class="tw-space-y-2.5">
                        <!-- Top meta badges -->
                        <div class="ppepp-document-card-top tw-flex tw-items-start tw-justify-between tw-gap-2">
                            <span class="ppepp-document-category tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-md tw-bg-blue-50 tw-text-blue-700 tw-text-xs tw-font-medium tw-border tw-border-blue-100/80">
                                <?php echo html_escape($category_label); ?>
                            </span>
                            <span class="ppepp-document-year tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-bg-slate-100 tw-text-slate-600 tw-text-[11px] tw-font-semibold tw-border tw-border-slate-200/70">
                                <?php echo html_escape((string) $document->period_year); ?>
                            </span>
                        </div>

                        <!-- Title -->
                        <h3 class="tw-text-base tw-font-bold tw-text-slate-900 tw-leading-snug tw-m-0">
                            <?php echo html_escape($document->title); ?>
                        </h3>

                        <!-- Description if present -->
                        <?php if (!empty($document->description)): ?>
                            <div class="tw-text-xs tw-text-slate-500 tw-line-clamp-2 ppepp-document-description">
                                <?php echo nl2br(html_escape($document->description)); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Meta details -->
                        <dl class="ppepp-document-meta tw-grid tw-grid-cols-2 tw-gap-2 tw-pt-2 tw-border-t tw-border-slate-100 tw-text-[11px] tw-m-0">
                            <div>
                                <dt class="tw-text-slate-400 tw-font-normal">Tanggal Dokumen</dt>
                                <dd class="tw-text-slate-700 tw-font-medium tw-m-0"><?php echo html_escape($document->document_date ?: '-'); ?></dd>
                            </div>
                            <div>
                                <dt class="tw-text-slate-400 tw-font-normal">Uploader</dt>
                                <dd class="tw-text-slate-700 tw-font-medium tw-m-0 tw-truncate" title="<?php echo html_escape($document->uploader_name ?: '-'); ?>">
                                    <?php echo html_escape($document->uploader_name ?: '-'); ?>
                                </dd>
                            </div>
                        </dl>

                        <!-- Media Source Badges -->
                        <div class="ppepp-document-types tw-flex tw-items-center tw-gap-1.5" aria-label="Jenis dokumen">
                            <?php if ($has_file): ?>
                                <span class="badge badge-info tw-inline-flex tw-items-center tw-gap-1 tw-px-2 tw-py-0.5 tw-rounded tw-bg-sky-50 tw-text-sky-700 tw-border tw-border-sky-200 tw-text-[11px] tw-font-medium">
                                    <svg class="tw-w-3 tw-h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    <span>File</span>
                                </span>
                            <?php endif; ?>
                            <?php if ($has_url): ?>
                                <span class="badge badge-secondary tw-inline-flex tw-items-center tw-gap-1 tw-px-2 tw-py-0.5 tw-rounded tw-bg-indigo-50 tw-text-indigo-700 tw-border tw-border-indigo-200 tw-text-[11px] tw-font-medium">
                                    <svg class="tw-w-3 tw-h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                    <span>URL</span>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="ami-row-actions ppepp-document-actions tw-flex tw-items-center tw-justify-between tw-pt-3 tw-border-t tw-border-slate-100 tw-gap-1">
                        <div class="tw-flex tw-items-center tw-gap-1.5">
                            <?php if ($has_file): ?>
                                <a class="ami-action-btn tw-inline-flex tw-items-center tw-gap-1 tw-px-2.5 tw-py-1 tw-rounded-md tw-bg-slate-50 hover:tw-bg-slate-100 tw-text-slate-700 tw-text-xs tw-font-medium tw-border tw-border-slate-200 tw-transition-colors" href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents/download/' . (int) $document->id) . $query); ?>" title="Download file">
                                    <svg class="tw-w-3.5 tw-h-3.5 tw-text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    <span>Download</span>
                                </a>
                            <?php endif; ?>
                            <?php if ($has_url): ?>
                                <a class="ami-action-btn tw-inline-flex tw-items-center tw-gap-1 tw-px-2.5 tw-py-1 tw-rounded-md tw-bg-slate-50 hover:tw-bg-slate-100 tw-text-slate-700 tw-text-xs tw-font-medium tw-border tw-border-slate-200 tw-transition-colors" href="<?php echo html_escape($document->external_url); ?>" target="_blank" rel="noopener noreferrer" title="Buka URL dokumen">
                                    <svg class="tw-w-3.5 tw-h-3.5 tw-text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                    <span>URL</span>
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="tw-flex tw-items-center tw-gap-1">
                            <a class="ami-action-btn tw-inline-flex tw-items-center tw-gap-1 tw-p-1.5 tw-rounded-md tw-text-slate-500 hover:tw-text-blue-600 hover:tw-bg-blue-50 tw-transition-colors" href="<?php echo html_escape(site_url('lpmpi/spmi-ppepp-documents/edit/' . (int) $document->id) . $query); ?>" title="Edit dokumen">
                                <svg class="tw-w-4 tw-h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                <span class="tw-sr-only">Edit</span>
                            </a>
                            <?php echo form_open('lpmpi/spmi-ppepp-documents/delete/' . (int) $document->id . $query, ['class' => 'tw-inline tw-m-0', 'onsubmit' => 'return confirm(\'Hapus dokumen PPEPP ini?\');']); ?>
                                <button type="submit" class="ami-action-btn danger tw-inline-flex tw-items-center tw-gap-1 tw-p-1.5 tw-rounded-md tw-text-slate-500 hover:tw-text-rose-600 hover:tw-bg-rose-50 tw-border-0 tw-bg-transparent tw-transition-colors" title="Hapus dokumen">
                                    <svg class="tw-w-4 tw-h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    <span class="tw-sr-only">Hapus</span>
                                </button>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="tw-bg-white tw-rounded-xl tw-border tw-border-slate-200/80 tw-p-8 tw-text-center ami-empty ppepp-no-match" id="ppepp-document-no-match" hidden>
            <div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-slate-100 tw-text-slate-400 tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-3 ami-empty-icon">
                <svg class="tw-w-6 tw-h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35" /></svg>
            </div>
            <div class="tw-text-sm tw-font-bold tw-text-slate-800 tw-mb-1 ami-empty-title">Dokumen tidak ditemukan</div>
            <div class="tw-text-xs tw-text-slate-500">Coba kata kunci lain atau hapus pencarian/filter kategori.</div>
        </div>
    <?php endif; ?>

</div>

<script>
(function() {
    var ppeppSearch = document.getElementById('ppepp-document-search');
    var ppeppClear = document.getElementById('ppepp-document-search-clear');
    var ppeppNoMatch = document.getElementById('ppepp-document-no-match');
    var categoryPills = document.querySelectorAll('#ppepp-category-filter-bar .ppepp-filter-pill');
    var selectedCategory = 'all';

    function runFilter() {
        var query = ppeppSearch ? ppeppSearch.value.trim().toLowerCase() : '';
        var cards = document.querySelectorAll('[data-ppepp-document-card]');
        var visible = 0;

        cards.forEach(function(card) {
            var text = card.textContent.toLowerCase();
            var matchesQuery = query === '' || text.indexOf(query) !== -1;
            var docCat = card.getAttribute('data-document-category') || '';
            var matchesCat = selectedCategory === 'all' || docCat === selectedCategory;

            var show = matchesQuery && matchesCat;
            card.hidden = !show;
            if (show) visible++;
        });

        if (ppeppClear) ppeppClear.hidden = query === '';
        if (ppeppNoMatch) ppeppNoMatch.hidden = visible !== 0 || (query === '' && selectedCategory === 'all');
    }

    if (ppeppSearch) {
        ppeppSearch.addEventListener('input', runFilter);
    }
    if (ppeppClear) {
        ppeppClear.addEventListener('click', function() {
            ppeppSearch.value = '';
            ppeppSearch.focus();
            runFilter();
        });
    }

    categoryPills.forEach(function(pill) {
        pill.addEventListener('click', function() {
            categoryPills.forEach(function(p) {
                p.classList.remove('active', 'tw-bg-blue-600', 'tw-text-white');
                p.classList.add('tw-bg-slate-100', 'tw-text-slate-600');
            });
            pill.classList.add('active', 'tw-bg-blue-600', 'tw-text-white');
            pill.classList.remove('tw-bg-slate-100', 'tw-text-slate-600');
            selectedCategory = pill.getAttribute('data-filter-category') || 'all';
            runFilter();
        });
    });
})();
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
