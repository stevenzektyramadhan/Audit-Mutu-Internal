<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$icon = static function ($name) {
    $paths = [
        'users-cog' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><circle cx="19" cy="11" r="2"/><path d="M19 8v1"/><path d="M19 13v1"/><path d="m21.6 9.5-.87.5"/><path d="m17.27 12-.87.5"/><path d="m21.6 12.5-.87-.5"/><path d="m17.27 10-.87-.5"/>',
        'plus' => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'map-pin' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
        'edit' => '<path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>',
        'check-circle' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
    ];
    return '<svg class="rtm-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['users-cog']) . '</svg>';
};
?>

<main id="rtm-root" data-ui-contract="ami-row-actions ami-action-btn" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
    <div class="tw-mx-auto tw-max-w-7xl">
        <!-- Header / Hero -->
        <div class="tw-mb-8 tw-flex tw-flex-col tw-gap-4 sm:tw-flex-row sm:tw-items-end sm:tw-justify-between">
            <div>
                <p class="tw-mb-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-text-slate-500">Tinjauan Manajemen</p>
                <h1 class="tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950">RTM SPMI</h1>
                <p class="tw-mt-2 tw-max-w-2xl tw-text-sm tw-text-slate-500">
                    <?php echo nl2br(html_escape('Manajemen rapat tinjauan manajemen SPMI. RTM terpisah dari laporan AMI legacy.')); ?>
                </p>
            </div>
            <a class="tw-button-primary" href="<?php echo site_url('lpmpi/spmi-rtm/create'); ?>">
                <?php echo $icon('plus'); ?>
                <span>Tambah RTM</span>
            </a>
        </div>

        <!-- Filter & Search Toolbar -->
        <section class="tw-mb-5 tw-grid tw-gap-3 sm:tw-grid-cols-[1fr_200px]" aria-label="Filter RTM">
            <label class="tw-block">
                <span class="tw-mb-1 tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-slate-500">Cari RTM</span>
                <input id="rtm-filter-search" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" type="search" placeholder="Kode rapat, judul, atau lokasi...">
            </label>
            <label class="tw-block">
                <span class="tw-mb-1 tw-block tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-slate-500">Status</span>
                <select id="rtm-filter-status" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2.5 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none">
                    <option value="">Semua status</option>
                    <option value="draft">Draft</option>
                    <option value="resolved">Resolved</option>
                </select>
            </label>
        </section>

        <?php if (empty($meetings)): ?>
            <div class="tw-rounded-2xl tw-border tw-border-dashed tw-border-slate-300 tw-bg-white tw-p-12 tw-text-center">
                <div class="tw-mx-auto tw-mb-3 tw-flex tw-h-12 tw-w-12 tw-items-center tw-justify-center tw-rounded-full tw-bg-slate-100 tw-text-slate-400">
                    <?php echo $icon('users-cog'); ?>
                </div>
                <h2 class="tw-text-base tw-font-bold tw-text-slate-900">Belum ada RTM SPMI</h2>
                <p class="tw-mt-1.5 tw-text-sm tw-text-slate-500">Buat draft RTM untuk menghubungkan laporan M10.</p>
            </div>
        <?php else: ?>
            <!-- Desktop Table View -->
            <div class="tw-hidden md:tw-block tw-overflow-hidden tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-shadow-sm">
                <div class="tw-overflow-x-auto">
                    <table class="tw-w-full tw-text-left tw-text-sm">
                        <thead class="tw-border-b tw-border-slate-200 tw-bg-slate-50 tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wider tw-text-slate-500">
                            <tr>
                                <th class="tw-px-5 tw-py-4">Kode &amp; Judul RTM</th>
                                <th class="tw-px-5 tw-py-4">Tanggal Rapat</th>
                                <th class="tw-px-5 tw-py-4">Lokasi</th>
                                <th class="tw-px-5 tw-py-4">Status</th>
                                <th class="tw-px-5 tw-py-4 tw-text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="rtm-list-table" class="tw-divide-y tw-divide-slate-100">
                            <?php foreach ($meetings as $meeting):
                                $status = strtolower($meeting->status);
                            ?>
                                <tr class="rtm-row hover:tw-bg-slate-50"
                                    data-search="<?php echo html_escape(strtolower($meeting->meeting_code . ' ' . $meeting->meeting_title . ' ' . $meeting->location)); ?>"
                                    data-status="<?php echo html_escape($status); ?>">
                                    <td class="tw-px-5 tw-py-4">
                                        <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-slate-100 tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200">
                                            <?php echo html_escape($meeting->meeting_code); ?>
                                        </span>
                                        <div class="tw-mt-1.5 tw-font-semibold tw-text-slate-900">
                                            <?php echo html_escape($meeting->meeting_title); ?>
                                        </div>
                                    </td>
                                    <td class="tw-px-5 tw-py-4 tw-whitespace-nowrap tw-text-slate-700">
                                        <div class="tw-flex tw-items-center tw-gap-1.5 tw-text-xs">
                                            <span class="tw-text-slate-400"><?php echo $icon('calendar'); ?></span>
                                            <span><?php echo html_escape($meeting->meeting_date); ?></span>
                                        </div>
                                    </td>
                                    <td class="tw-px-5 tw-py-4 tw-text-slate-700">
                                        <div class="tw-flex tw-items-center tw-gap-1.5 tw-text-xs">
                                            <span class="tw-text-slate-400"><?php echo $icon('map-pin'); ?></span>
                                            <span><?php echo html_escape($meeting->location); ?></span>
                                        </div>
                                    </td>
                                    <td class="tw-px-5 tw-py-4">
                                        <?php if ($status === 'resolved'): ?>
                                            <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-bg-emerald-50 tw-border tw-border-emerald-200 tw-px-2.5 tw-py-1 tw-text-xs tw-font-bold tw-text-emerald-800">
                                                <?php echo $icon('check-circle'); ?>
                                                <span>Resolved</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-bg-amber-50 tw-border tw-border-amber-200 tw-px-2.5 tw-py-1 tw-text-xs tw-font-bold tw-text-amber-800">
                                                <?php echo $icon('clock'); ?>
                                                <span>Draft</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="tw-px-5 tw-py-4 tw-text-right">
                                        <div class="ami-row-actions tw-inline-flex tw-items-center tw-justify-end tw-gap-2">
                                            <a class="ami-action-btn tw-button-secondary tw-text-xs" href="<?php echo site_url('lpmpi/spmi-rtm/detail/' . (int) $meeting->id); ?>">
                                                <span>Detail</span>
                                                <?php echo $icon('arrow-right'); ?>
                                            </a>
                                            <?php if ($meeting->status === 'draft'): ?>
                                                <a class="ami-action-btn tw-button-secondary tw-text-xs" href="<?php echo site_url('lpmpi/spmi-rtm/edit/' . (int) $meeting->id); ?>">
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
            <div id="rtm-list-cards" class="tw-grid tw-gap-3 md:tw-hidden">
                <?php foreach ($meetings as $meeting):
                    $status = strtolower($meeting->status);
                ?>
                    <article class="rtm-row tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-5 tw-shadow-sm"
                             data-search="<?php echo html_escape(strtolower($meeting->meeting_code . ' ' . $meeting->meeting_title . ' ' . $meeting->location)); ?>"
                             data-status="<?php echo html_escape($status); ?>">
                        <div class="tw-flex tw-items-center tw-justify-between tw-gap-2 tw-mb-3">
                            <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-900 tw-bg-slate-100 tw-px-2.5 tw-py-1 tw-rounded tw-border tw-border-slate-200">
                                <?php echo html_escape($meeting->meeting_code); ?>
                            </span>
                            <?php if ($status === 'resolved'): ?>
                                <span class="tw-inline-flex tw-items-center tw-gap-1 tw-rounded-full tw-bg-emerald-50 tw-border tw-border-emerald-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-emerald-800">
                                    <?php echo $icon('check-circle'); ?>
                                    <span>Resolved</span>
                                </span>
                            <?php else: ?>
                                <span class="tw-inline-flex tw-items-center tw-gap-1 tw-rounded-full tw-bg-amber-50 tw-border tw-border-amber-200 tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-bold tw-text-amber-800">
                                    <?php echo $icon('clock'); ?>
                                    <span>Draft</span>
                                </span>
                            <?php endif; ?>
                        </div>

                        <h3 class="tw-text-base tw-font-bold tw-text-slate-900 tw-m-0">
                            <?php echo html_escape($meeting->meeting_title); ?>
                        </h3>

                        <div class="tw-mt-3 tw-space-y-1.5 tw-text-xs tw-text-slate-600">
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <span class="tw-text-slate-400"><?php echo $icon('calendar'); ?></span>
                                <span><?php echo html_escape($meeting->meeting_date); ?></span>
                            </div>
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <span class="tw-text-slate-400"><?php echo $icon('map-pin'); ?></span>
                                <span><?php echo html_escape($meeting->location); ?></span>
                            </div>
                        </div>

                        <div class="tw-mt-4 tw-pt-3 tw-border-t tw-border-slate-100">
                            <div class="ami-row-actions tw-flex tw-items-center tw-gap-2">
                                <a class="ami-action-btn tw-button-secondary tw-flex-1 tw-min-h-[44px] tw-text-sm tw-justify-center" href="<?php echo site_url('lpmpi/spmi-rtm/detail/' . (int) $meeting->id); ?>">
                                    <span>Detail RTM</span>
                                    <?php echo $icon('arrow-right'); ?>
                                </a>
                                <?php if ($meeting->status === 'draft'): ?>
                                    <a class="ami-action-btn tw-button-secondary tw-min-h-[44px] tw-px-4 tw-text-sm" href="<?php echo site_url('lpmpi/spmi-rtm/edit/' . (int) $meeting->id); ?>">
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
            <p id="rtm-filter-empty" class="tw-hidden tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-8 tw-text-center tw-text-sm tw-text-slate-500 tw-mt-4">
                Tidak ada agenda RTM yang cocok dengan filter pencarian.
            </p>
        <?php endif; ?>
    </div>
</main>

<script>
(function () {
    var rows = Array.prototype.slice.call(document.querySelectorAll('.rtm-row'));
    var search = document.getElementById('rtm-filter-search');
    var status = document.getElementById('rtm-filter-status');
    var empty = document.getElementById('rtm-filter-empty');

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
