<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$icon = static function ($name) {
    $paths = [
        'tasks' => '<path d="M12 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
        'edit' => '<path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>',
        'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'play' => '<polygon points="5 3 19 12 5 21 5 3"/>',
        'alert-circle' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
    ];
    return '<svg class="fu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['tasks']) . '</svg>';
};

$today = date('Y-m-d');
?>

<main id="follow-ups-root" data-ui-contract="ami-row-actions ami-action-btn" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-7xl">
        <!-- Header / Hero -->
        <div class="tw-mb-8">
            <p class="tw-mb-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-text-slate-500">Tindak Lanjut Perbaikan Mutu</p>
            <h1 class="tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950">Tindak Lanjut RTM</h1>
            <p class="tw-mt-2 tw-max-w-2xl tw-text-sm tw-text-slate-500">
                Pelaksanaan dan pemantauan butir keputusan rapat tinjauan manajemen (RTM) yang telah diselesaikan.
            </p>
        </div>

        <!-- Filter & Search Toolbar -->
        <section class="tw-mb-5 tw-grid tw-gap-3 sm:tw-grid-cols-[1fr_200px]" aria-label="Filter Tindak Lanjut">
            <label class="tw-block">
                <span class="tw-mb-1 tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-slate-500">Cari tindak lanjut</span>
                <input id="fu-filter-search" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" type="search" placeholder="Kode, butir keputusan, atau nama PIC...">
            </label>
            <label class="tw-block">
                <span class="tw-mb-1 tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-slate-500">Status</span>
                <select id="fu-filter-status" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none">
                    <option value="">Semua status</option>
                    <option value="open">Belum dimulai</option>
                    <option value="in_progress">Sedang berjalan</option>
                    <option value="completed">Selesai</option>
                </select>
            </label>
        </section>

        <?php if (empty($follow_ups)): ?>
            <div class="tw-rounded-2xl tw-border tw-border-dashed tw-border-slate-300 tw-bg-white tw-p-12 tw-text-center">
                <div class="tw-mx-auto tw-mb-3 tw-flex tw-h-12 tw-w-12 tw-items-center tw-justify-center tw-rounded-full tw-bg-slate-100 tw-text-slate-400">
                    <?php echo $icon('tasks'); ?>
                </div>
                <h2 class="tw-text-base tw-font-bold tw-text-slate-900">Belum ada tindak lanjut</h2>
                <p class="tw-mt-1.5 tw-text-sm tw-text-slate-500">Buka detail RTM resolved untuk membuat tindak lanjut keputusan.</p>
            </div>
        <?php else: ?>
            <!-- Desktop Table View -->
            <div class="tw-hidden md:tw-block tw-overflow-hidden tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-shadow-sm">
                <div class="tw-overflow-x-auto">
                    <table class="tw-w-full tw-text-left tw-text-sm">
                        <thead class="tw-border-b tw-border-slate-200 tw-bg-slate-50 tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wider tw-text-slate-500">
                            <tr>
                                <th class="tw-px-5 tw-py-4 tw-w-36">Kode</th>
                                <th class="tw-px-5 tw-py-4">Ringkasan Keputusan</th>
                                <th class="tw-px-5 tw-py-4 tw-w-48">Penanggung Jawab</th>
                                <th class="tw-px-5 tw-py-4 tw-w-40">Batas Waktu</th>
                                <th class="tw-px-5 tw-py-4 tw-w-32">Status</th>
                                <th class="tw-px-5 tw-py-4 tw-text-right tw-w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="fu-list-table" class="tw-divide-y tw-divide-slate-100">
                            <?php foreach ($follow_ups as $item):
                                $status = strtolower($item->status);
                                $is_overdue = ($status !== 'completed' && !empty($item->due_date) && $item->due_date < $today);
                            ?>
                                <tr class="fu-row hover:tw-bg-slate-50"
                                    data-search="<?php echo html_escape(strtolower($item->follow_up_code . ' ' . $item->decision_text_snapshot . ' ' . $item->responsible_name_snapshot)); ?>"
                                    data-status="<?php echo html_escape($status); ?>">
                                    <td class="tw-px-5 tw-py-4">
                                        <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-slate-100 tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200 tw-inline-block">
                                            <?php echo html_escape($item->follow_up_code); ?>
                                        </span>
                                    </td>
                                    <td class="tw-px-5 tw-py-4">
                                        <div class="tw-text-xs tw-leading-relaxed tw-text-slate-900 tw-font-medium tw-line-clamp-2" title="<?php echo html_escape($item->decision_text_snapshot); ?>">
                                            <?php echo nl2br(html_escape($item->decision_text_snapshot)); ?>
                                        </div>
                                    </td>
                                    <td class="tw-px-5 tw-py-4">
                                        <div class="tw-flex tw-items-center tw-gap-1.5 tw-text-xs tw-text-slate-800 tw-font-medium">
                                            <span class="tw-text-slate-400"><?php echo $icon('user'); ?></span>
                                            <span class="tw-truncate"><?php echo html_escape($item->responsible_name_snapshot); ?></span>
                                        </div>
                                    </td>
                                    <td class="tw-px-5 tw-py-4 tw-whitespace-nowrap">
                                        <?php if (!empty($item->due_date)): ?>
                                            <div class="tw-flex tw-items-center tw-gap-1.5 tw-text-xs <?php echo $is_overdue ? 'tw-text-red-600 tw-font-semibold' : 'tw-text-slate-600'; ?>">
                                                <span><?php echo html_escape($item->due_date); ?></span>
                                                <?php if ($is_overdue): ?>
                                                    <span class="tw-inline-flex tw-items-center tw-rounded tw-bg-red-50 tw-border tw-border-red-200 tw-px-1.5 tw-py-0.5 tw-text-[10px] tw-font-bold tw-text-red-700">Terlewat</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="tw-text-xs tw-text-slate-400 tw-italic">Tidak ditentukan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="tw-px-5 tw-py-4">
                                        <?php if ($status === 'completed'): ?>
                                            <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-bg-emerald-50 tw-border tw-border-emerald-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-emerald-800">
                                                <?php echo $icon('check-circle'); ?>
                                                <span>Selesai</span>
                                            </span>
                                        <?php elseif ($status === 'in_progress'): ?>
                                            <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-bg-blue-50 tw-border tw-border-blue-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-blue-800">
                                                <?php echo $icon('play'); ?>
                                                <span>Sedang berjalan</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-bg-amber-50 tw-border tw-border-amber-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-amber-800">
                                                <?php echo $icon('clock'); ?>
                                                <span>Belum dimulai</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="tw-px-5 tw-py-4 tw-text-right">
                                        <div class="ami-row-actions tw-inline-flex tw-items-center tw-justify-end tw-gap-2">
                                            <a class="ami-action-btn tw-button-secondary tw-text-xs" href="<?php echo site_url('lpmpi/spmi-follow-ups/detail/' . (int) $item->id); ?>">
                                                <span>Detail</span>
                                                <?php echo $icon('arrow-right'); ?>
                                            </a>
                                            <?php if ($item->status === 'open'): ?>
                                                <a class="ami-action-btn tw-button-secondary tw-text-xs" href="<?php echo site_url('lpmpi/spmi-follow-ups/edit/' . (int) $item->id); ?>">
                                                    <?php echo $icon('edit'); ?>
                                                    <span>Edit</span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Cards View -->
            <div id="fu-list-cards" class="tw-grid tw-gap-3 md:tw-hidden">
                <?php foreach ($follow_ups as $item):
                    $status = strtolower($item->status);
                    $is_overdue = ($status !== 'completed' && !empty($item->due_date) && $item->due_date < $today);
                ?>
                    <article class="fu-row tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-5 tw-shadow-sm"
                             data-search="<?php echo html_escape(strtolower($item->follow_up_code . ' ' . $item->decision_text_snapshot . ' ' . $item->responsible_name_snapshot)); ?>"
                             data-status="<?php echo html_escape($status); ?>">
                        <div class="tw-flex tw-items-center tw-justify-between tw-gap-2 tw-mb-3">
                            <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-slate-100 tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200">
                                <?php echo html_escape($item->follow_up_code); ?>
                            </span>
                            <?php if ($status === 'completed'): ?>
                                <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-bg-emerald-50 tw-border tw-border-emerald-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-emerald-800">
                                    <?php echo $icon('check-circle'); ?>
                                    <span>Selesai</span>
                                </span>
                            <?php elseif ($status === 'in_progress'): ?>
                                <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-bg-blue-50 tw-border tw-border-blue-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-blue-800">
                                    <?php echo $icon('play'); ?>
                                    <span>Sedang berjalan</span>
                                </span>
                            <?php else: ?>
                                <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-bg-amber-50 tw-border tw-border-amber-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-amber-800">
                                    <?php echo $icon('clock'); ?>
                                    <span>Belum dimulai</span>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="tw-text-xs tw-leading-relaxed tw-text-slate-800 tw-mb-3">
                            <strong class="tw-block tw-text-slate-500 tw-mb-1">Keputusan:</strong>
                            <p class="tw-m-0"><?php echo nl2br(html_escape($item->decision_text_snapshot)); ?></p>
                        </div>

                        <div class="tw-mt-3 tw-space-y-1.5 tw-text-xs tw-text-slate-600 tw-bg-slate-50 tw-p-3 tw-rounded-xl tw-border tw-border-slate-100">
                            <div class="tw-flex tw-items-center tw-justify-between">
                                <span class="tw-text-slate-500">Penanggung Jawab:</span>
                                <strong class="tw-text-slate-900"><?php echo html_escape($item->responsible_name_snapshot); ?></strong>
                            </div>
                            <div class="tw-flex tw-items-center tw-justify-between">
                                <span class="tw-text-slate-500">Batas Waktu:</span>
                                <?php if (!empty($item->due_date)): ?>
                                    <span class="<?php echo $is_overdue ? 'tw-text-red-600 tw-font-bold' : 'tw-text-slate-800'; ?>">
                                        <?php echo html_escape($item->due_date); ?>
                                        <?php if ($is_overdue): ?>(Terlewat)<?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="tw-text-slate-400 tw-italic">Tidak ditentukan</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="tw-mt-4 tw-pt-3 tw-border-t tw-border-slate-100">
                            <div class="ami-row-actions tw-flex tw-items-center tw-gap-2">
                                <a class="ami-action-btn tw-button-secondary tw-flex-1 tw-min-h-[44px] tw-text-sm tw-justify-center" href="<?php echo site_url('lpmpi/spmi-follow-ups/detail/' . (int) $item->id); ?>">
                                    <span>Detail</span>
                                    <?php echo $icon('arrow-right'); ?>
                                </a>
                                <?php if ($item->status === 'open'): ?>
                                    <a class="ami-action-btn tw-button-secondary tw-min-h-[44px] tw-px-4 tw-text-sm" href="<?php echo site_url('lpmpi/spmi-follow-ups/edit/' . (int) $item->id); ?>">
                                        <?php echo $icon('edit'); ?>
                                        <span>Edit</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Empty Filter Notice -->
            <p id="fu-filter-empty" class="tw-hidden tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-8 tw-text-center tw-text-sm tw-text-slate-500 tw-mt-4">
                Tidak ada tindak lanjut yang cocok dengan filter pencarian.
            </p>
        <?php endif; ?>
    </div>
</main>

<script>
(function () {
    var rows = Array.prototype.slice.call(document.querySelectorAll('.fu-row'));
    var search = document.getElementById('fu-filter-search');
    var status = document.getElementById('fu-filter-status');
    var empty = document.getElementById('fu-filter-empty');

    function filter() {
        var query = search ? search.value.trim().toLowerCase() : '';
        var selectedStatus = status ? status.value.trim().toLowerCase() : '';

        var visibleCount = 0;
        rows.forEach(function (row) {
            var text = row.getAttribute('data-search') || '';
            var rowStatus = row.getAttribute('data-status') || '';

            var matchSearch = !query || text.indexOf(query) !== -1;
            var matchStatus = !selectedStatus || rowStatus === selectedStatus;

            if (matchSearch && matchStatus) {
                row.classList.remove('tw-hidden');
                visibleCount++;
            } else {
                row.classList.add('tw-hidden');
            }
        });

        if (empty) {
            if (visibleCount === 0 && rows.length > 0) {
                empty.classList.remove('tw-hidden');
            } else {
                empty.classList.add('tw-hidden');
            }
        }
    }

    if (search) search.addEventListener('input', filter);
    if (status) status.addEventListener('change', filter);
})();
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
