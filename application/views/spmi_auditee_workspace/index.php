<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$icon = static function ($name) {
    $paths = [
        'laptop-house' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'filter' => '<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>',
        'refresh' => '<path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/>',
        'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
        'file-text' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
    ];
    return '<svg class="adte-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['laptop-house']) . '</svg>';
};
?>

<main id="auditee-root" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-7xl">
        <!-- Header / Hero -->
        <div class="tw-mb-8">
            <p class="tw-mb-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-text-slate-500">Pelaksanaan SPMI</p>
            <h1 class="tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950 tw-m-0">Workspace SPMI</h1>
            <p class="tw-mt-2 tw-max-w-2xl tw-text-sm tw-text-slate-500">
                Ruang kerja pengisian realisasi capaian standar, pengunggahan berkas bukti, dan pemantauan status pengajuan auditee.
            </p>
        </div>

        <!-- Filter & Search Toolbar -->
        <form method="get" action="<?php echo site_url('auditee/spmi'); ?>" class="tw-mb-6 tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-end tw-gap-3 tw-bg-white tw-p-4 tw-rounded-2xl tw-border tw-border-slate-200 tw-shadow-sm">
            <div class="tw-flex-1">
                <label for="cycle_id" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                    Siklus Audit
                </label>
                <select id="cycle_id" name="cycle_id" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none">
                    <option value="0"><?php echo html_escape('Semua siklus'); ?></option>
                    <?php foreach ($cycle_options as $cycle): ?>
                        <option value="<?php echo html_escape((string) $cycle->id); ?>" <?php echo (int) $filters['cycle_id'] === (int) $cycle->id ? 'selected' : ''; ?>>
                            <?php echo html_escape($cycle->cycle_code . ' — ' . $cycle->title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="sm:tw-w-64">
                <label for="status" class="tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-slate-700 tw-mb-1.5">
                    Status Pengajuan
                </label>
                <select id="status" name="status" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none">
                    <option value="">Semua status</option>
                    <option value="draft" <?php echo $filters['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="submitted" <?php echo $filters['status'] === 'submitted' ? 'selected' : ''; ?>>Dikirim</option>
                    <option value="returned_for_revision" <?php echo $filters['status'] === 'returned_for_revision' ? 'selected' : ''; ?>>Perlu revisi</option>
                    <option value="resubmitted" <?php echo $filters['status'] === 'resubmitted' ? 'selected' : ''; ?>>Dikirim ulang</option>
                </select>
            </div>

            <div class="tw-flex tw-items-center tw-gap-2 tw-pt-2 sm:tw-pt-0">
                <button type="submit" class="btn-ami tw-button-primary tw-text-xs">
                    <?php echo $icon('filter'); ?>
                    <span>Filter</span>
                </button>
                <a href="<?php echo site_url('auditee/spmi'); ?>" class="btn-ami tw-button-secondary tw-text-xs">
                    <?php echo $icon('refresh'); ?>
                    <span>Reset</span>
                </a>
            </div>
        </form>

        <?php if (empty($assignments)): ?>
            <div class="tw-rounded-2xl tw-border tw-border-dashed tw-border-slate-300 tw-bg-white tw-p-12 tw-text-center">
                <div class="tw-mx-auto tw-mb-3 tw-flex tw-h-12 tw-w-12 tw-items-center tw-justify-center tw-rounded-full tw-bg-slate-100 tw-text-slate-400">
                    <?php echo $icon('laptop-house'); ?>
                </div>
                <h2 class="tw-text-base tw-font-bold tw-text-slate-900">Belum ada penugasan SPMI yang dapat diisi.</h2>
                <p class="tw-mt-1.5 tw-text-sm tw-text-slate-500">Penugasan audit dari LPM akan muncul di sini saat siklus audit telah dikonfigurasi.</p>
            </div>
        <?php else: ?>
            <!-- Desktop Table View -->
            <div class="tw-hidden md:tw-block tw-overflow-hidden tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-shadow-sm">
                <div class="tw-overflow-x-auto">
                    <table class="tw-w-full tw-text-left tw-text-sm">
                        <thead class="tw-border-b tw-border-slate-200 tw-bg-slate-50 tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wider tw-text-slate-500">
                            <tr>
                                <th class="tw-px-5 tw-py-4">Siklus</th>
                                <th class="tw-px-5 tw-py-4">Standar</th>
                                <th class="tw-px-5 tw-py-4">Status</th>
                                <th class="tw-px-5 tw-py-4 tw-text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="tw-divide-y tw-divide-slate-100">
                            <?php foreach ($assignments as $assignment):
                                $status = strtolower((string) ($assignment->submission_status ?: 'draft'));
                            ?>
                                <tr class="hover:tw-bg-slate-50">
                                    <td class="tw-px-5 tw-py-4">
                                        <div class="tw-font-bold tw-text-slate-900">
                                            <?php echo html_escape($assignment->cycle_code . ' — ' . $assignment->cycle_title); ?>
                                        </div>
                                    </td>
                                    <td class="tw-px-5 tw-py-4">
                                        <div class="tw-font-semibold tw-text-slate-900">
                                            <?php echo html_escape($assignment->source_standard_code . ' — ' . $assignment->source_standard_title); ?>
                                        </div>
                                    </td>
                                    <td class="tw-px-5 tw-py-4">
                                        <?php if ($status === 'submitted'): ?>
                                            <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-blue-50 tw-border tw-border-blue-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-blue-800">
                                                Dikirim
                                            </span>
                                        <?php elseif ($status === 'resubmitted'): ?>
                                            <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-teal-50 tw-border tw-border-teal-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-teal-800">
                                                Dikirim ulang
                                            </span>
                                        <?php elseif ($status === 'returned_for_revision'): ?>
                                            <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-rose-50 tw-border tw-border-rose-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-rose-800">
                                                Perlu revisi
                                            </span>
                                        <?php else: ?>
                                            <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-amber-50 tw-border tw-border-amber-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-amber-800">
                                                Draft
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="tw-px-5 tw-py-4 tw-text-right">
                                        <div class="ami-row-actions tw-inline-flex tw-justify-end">
                                            <a class="ami-action-btn tw-button-primary tw-text-xs" href="<?php echo site_url('auditee/spmi/assignment/' . (int) $assignment->id); ?>">
                                                <span>Buka</span>
                                                <?php echo $icon('arrow-right'); ?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Cards View -->
            <div class="tw-grid tw-gap-4 md:tw-hidden">
                <?php foreach ($assignments as $assignment):
                    $status = strtolower((string) ($assignment->submission_status ?: 'draft'));
                ?>
                    <article class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-5 tw-shadow-sm tw-space-y-3">
                        <div class="tw-flex tw-items-center tw-justify-between tw-gap-2">
                            <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-slate-100 tw-px-2 tw-py-0.5 tw-rounded tw-border tw-border-slate-200">
                                <?php echo html_escape($assignment->cycle_code); ?>
                            </span>
                            <?php if ($status === 'submitted'): ?>
                                <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-blue-50 tw-border tw-border-blue-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-blue-800">
                                    Dikirim
                                </span>
                            <?php elseif ($status === 'resubmitted'): ?>
                                <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-teal-50 tw-border tw-border-teal-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-teal-800">
                                    Dikirim ulang
                                </span>
                            <?php elseif ($status === 'returned_for_revision'): ?>
                                <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-rose-50 tw-border tw-border-rose-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-rose-800">
                                    Perlu revisi
                                </span>
                            <?php else: ?>
                                <span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-amber-50 tw-border tw-border-amber-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-amber-800">
                                    Draft
                                </span>
                            <?php endif; ?>
                        </div>

                        <div>
                            <h3 class="tw-text-sm tw-font-bold tw-text-slate-900 tw-m-0">
                                <?php echo html_escape($assignment->source_standard_code . ' — ' . $assignment->source_standard_title); ?>
                            </h3>
                            <p class="tw-text-xs tw-text-slate-500 tw-m-0 tw-mt-0.5">
                                <?php echo html_escape($assignment->cycle_title); ?>
                            </p>
                        </div>

                        <div class="tw-pt-2">
                            <div class="ami-row-actions">
                                <a class="ami-action-btn tw-button-primary tw-w-full tw-min-h-[44px] tw-text-sm tw-justify-center" href="<?php echo site_url('auditee/spmi/assignment/' . (int) $assignment->id); ?>">
                                    <span>Buka Ruang Kerja</span>
                                    <?php echo $icon('arrow-right'); ?>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
