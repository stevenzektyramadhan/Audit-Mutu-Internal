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
        'pencil' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
        'eye' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'file-text' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
        'more-vertical' => '<circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/>',
    ];
    return '<svg class="rtm-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['users-cog']) . '</svg>';
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
                <div class="tw-relative">
                    <input id="rtm-filter-search" class="tw-w-full tw-rounded-lg tw-border tw-border-slate-300 tw-bg-white tw-px-3 tw-py-2.5 tw-pr-10 tw-text-sm tw-text-slate-900 focus:tw-border-slate-950 focus:tw-outline-none" type="search" placeholder="Kode rapat, judul, atau lokasi...">
                    <button id="rtm-filter-search-clear" class="tw-absolute tw-right-2 tw-top-1/2 tw-hidden tw-h-7 tw-w-7 -tw-translate-y-1/2 tw-items-center tw-justify-center tw-rounded tw-text-slate-400 hover:tw-bg-slate-100 hover:tw-text-slate-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-slate-300" type="button" aria-label="Hapus pencarian">&times;</button>
                </div>
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
                                <th class="tw-px-5 tw-py-4 tw-text-right tw-w-44 tw-whitespace-nowrap">Aksi</th>
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
                                            <span data-rtm-field="code"><?php echo html_escape($meeting->meeting_code); ?></span>
                                        </span>
                                        <div class="tw-mt-1.5 tw-font-semibold tw-text-slate-900">
                                            <span data-rtm-field="title"><?php echo html_escape($meeting->meeting_title); ?></span>
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
                                            <span data-rtm-field="location"><?php echo html_escape($meeting->location); ?></span>
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
                                    <td class="tw-px-5 tw-py-4 tw-text-right tw-w-44 tw-whitespace-nowrap">
                                        <div class="ami-row-actions tw-relative tw-inline-flex tw-flex-nowrap tw-items-center tw-justify-end tw-gap-2">
                                            <a class="ami-action-btn tw-button-primary tw-inline-flex tw-min-h-[44px] tw-items-center tw-justify-center tw-gap-1.5 tw-px-3.5 tw-text-xs tw-font-semibold tw-whitespace-nowrap" href="<?php echo site_url('lpmpi/spmi-rtm/detail/' . (int) $meeting->id); ?>" title="Detail RTM">
                                                <?php echo $icon('eye'); ?><span>Detail</span>
                                            </a>
                                            <div class="rtm-action-dropdown tw-relative tw-shrink-0">
                                                <button type="button" class="rtm-dropdown-toggle ami-action-btn tw-button-secondary tw-h-[44px] tw-w-[44px] tw-min-h-[44px] tw-min-w-[44px] tw-p-0 tw-inline-flex tw-items-center tw-justify-center tw-shrink-0" aria-haspopup="true" aria-expanded="false" aria-controls="rtm-action-menu-<?php echo (int) $meeting->id; ?>" aria-label="Menu opsi RTM <?php echo html_escape($meeting->meeting_code); ?>" title="Opsi Lainnya">
                                                    <?php echo $icon('more-vertical'); ?><span class="tw-sr-only">Opsi lainnya</span>
                                                </button>
                                                <div id="rtm-action-menu-<?php echo (int) $meeting->id; ?>" class="rtm-dropdown-menu tw-absolute tw-right-0 tw-top-full tw-z-30 tw-mt-1 tw-hidden tw-w-44 tw-rounded-xl tw-border tw-border-slate-200 tw-bg-white tw-py-1 tw-text-left tw-shadow-lg" role="menu">
                                                    <a class="tw-flex tw-w-full tw-items-center tw-gap-2.5 tw-px-3.5 tw-py-2 tw-text-xs tw-font-medium tw-text-slate-700 hover:tw-bg-slate-50 hover:tw-text-slate-900" href="<?php echo site_url('lpmpi/spmi-rtm/export-word/' . (int) $meeting->id); ?>" role="menuitem">
                                                        <?php echo $icon('file-text'); ?><span>Export DOC</span>
                                                    </a>
                                                    <?php if ($meeting->status === 'draft'): ?>
                                                        <a class="tw-flex tw-w-full tw-items-center tw-gap-2.5 tw-px-3.5 tw-py-2 tw-text-xs tw-font-medium tw-text-slate-700 hover:tw-bg-slate-50 hover:tw-text-slate-900" href="<?php echo site_url('lpmpi/spmi-rtm/edit/' . (int) $meeting->id); ?>" role="menuitem">
                                                            <?php echo $icon('pencil'); ?><span>Edit RTM</span>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
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
                                <span data-rtm-field="code"><?php echo html_escape($meeting->meeting_code); ?></span>
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
                            <span data-rtm-field="title"><?php echo html_escape($meeting->meeting_title); ?></span>
                        </h3>

                        <div class="tw-mt-3 tw-space-y-1.5 tw-text-xs tw-text-slate-600">
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <span class="tw-text-slate-400"><?php echo $icon('calendar'); ?></span>
                                <span><?php echo html_escape($meeting->meeting_date); ?></span>
                            </div>
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <span class="tw-text-slate-400"><?php echo $icon('map-pin'); ?></span>
                                <span data-rtm-field="location"><?php echo html_escape($meeting->location); ?></span>
                            </div>
                        </div>

                        <div class="tw-mt-4 tw-pt-3 tw-border-t tw-border-slate-100">
                            <div class="ami-row-actions tw-flex tw-flex-nowrap tw-items-center tw-justify-end tw-gap-2">
                                <a class="ami-action-btn tw-button-primary tw-inline-flex tw-min-h-[44px] tw-items-center tw-justify-center tw-gap-1.5 tw-px-4 tw-text-xs tw-font-semibold tw-whitespace-nowrap" href="<?php echo site_url('lpmpi/spmi-rtm/detail/' . (int) $meeting->id); ?>" title="Detail RTM">
                                    <?php echo $icon('eye'); ?><span>Detail</span>
                                </a>
                                <div class="rtm-action-dropdown tw-relative tw-shrink-0">
                                    <button type="button" class="rtm-dropdown-toggle ami-action-btn tw-button-secondary tw-h-[44px] tw-w-[44px] tw-min-h-[44px] tw-min-w-[44px] tw-p-0 tw-inline-flex tw-items-center tw-justify-center tw-shrink-0" aria-haspopup="true" aria-expanded="false" aria-controls="rtm-mobile-action-menu-<?php echo (int) $meeting->id; ?>" aria-label="Menu opsi RTM <?php echo html_escape($meeting->meeting_code); ?>" title="Opsi Lainnya">
                                        <?php echo $icon('more-vertical'); ?><span class="tw-sr-only">Opsi lainnya</span>
                                    </button>
                                    <div id="rtm-mobile-action-menu-<?php echo (int) $meeting->id; ?>" class="rtm-dropdown-menu tw-absolute tw-right-0 tw-top-full tw-z-30 tw-mt-1 tw-hidden tw-w-44 tw-rounded-xl tw-border tw-border-slate-200 tw-bg-white tw-py-1 tw-text-left tw-shadow-lg" role="menu">
                                        <a class="tw-flex tw-w-full tw-items-center tw-gap-2.5 tw-px-3.5 tw-py-2.5 tw-text-xs tw-font-medium tw-text-slate-700 hover:tw-bg-slate-50 hover:tw-text-slate-900" href="<?php echo site_url('lpmpi/spmi-rtm/export-word/' . (int) $meeting->id); ?>" role="menuitem">
                                            <?php echo $icon('file-text'); ?><span>Export DOC</span>
                                        </a>
                                        <?php if ($meeting->status === 'draft'): ?>
                                            <a class="tw-flex tw-w-full tw-items-center tw-gap-2.5 tw-px-3.5 tw-py-2.5 tw-text-xs tw-font-medium tw-text-slate-700 hover:tw-bg-slate-50 hover:tw-text-slate-900" href="<?php echo site_url('lpmpi/spmi-rtm/edit/' . (int) $meeting->id); ?>" role="menuitem">
                                                <?php echo $icon('pencil'); ?><span>Edit RTM</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
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
    var clear = document.getElementById('rtm-filter-search-clear');
    var status = document.getElementById('rtm-filter-status');
    var empty = document.getElementById('rtm-filter-empty');
    var debounceTimer;

    function closeAllDropdowns() {
        var menus = document.querySelectorAll('#rtm-root .rtm-dropdown-menu:not(.tw-hidden)');
        for (var i = 0; i < menus.length; i++) {
            var menu = menus[i];
            menu.classList.add('tw-hidden');
            menu.classList.remove('tw-fixed', 'tw-bottom-full', 'tw-top-auto', 'tw-mb-1');
            menu.classList.add('tw-absolute', 'tw-top-full', 'tw-mt-1');
            menu.style.left = '';
            menu.style.top = '';
            var toggle = menu.parentNode.querySelector('.rtm-dropdown-toggle');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }
    }

    document.getElementById('rtm-root').addEventListener('click', function (event) {
        var toggle = event.target.closest('.rtm-dropdown-toggle');
        if (toggle) {
            event.preventDefault();
            event.stopPropagation();
            var container = toggle.closest('.rtm-action-dropdown');
            var menu = container ? container.querySelector('.rtm-dropdown-menu') : null;
            if (!menu) return;
            var isHidden = menu.classList.contains('tw-hidden');
            closeAllDropdowns();
            if (isHidden) {
                var rect = toggle.getBoundingClientRect();
                menu.classList.remove('tw-hidden');
                menu.classList.remove('tw-absolute', 'tw-top-full', 'tw-mt-1');
                menu.classList.add('tw-fixed');
                var menuWidth = menu.offsetWidth || 176;
                var menuHeight = menu.offsetHeight || 96;
                var dropUp = (window.innerHeight - rect.bottom < menuHeight + 8) && (rect.top > menuHeight + 8);
                var left = Math.max(8, Math.min(rect.right - menuWidth, window.innerWidth - menuWidth - 8));
                var top = dropUp ? Math.max(8, rect.top - menuHeight - 4) : (rect.bottom + 4);
                menu.style.left = left + 'px';
                menu.style.top = top + 'px';
                toggle.setAttribute('aria-expanded', 'true');
                if (event.detail === 0) {
                    var firstItem = menu.querySelector('[role="menuitem"]');
                    if (firstItem) firstItem.focus();
                }
            }
            return;
        }
        if (event.target.closest('.rtm-dropdown-menu a') || !event.target.closest('.rtm-action-dropdown')) closeAllDropdowns();
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('#rtm-root .rtm-action-dropdown')) closeAllDropdowns();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' || event.key === 'Esc') closeAllDropdowns();
    });

    window.addEventListener('scroll', closeAllDropdowns, true);
    window.addEventListener('resize', closeAllDropdowns);

    function updateUrl(query, selectedStatus) {
        if (!window.history || !window.history.replaceState) return;
        var params = new URLSearchParams(window.location.search);
        if (query) params.set('q', query); else params.delete('q');
        if (selectedStatus) params.set('status', selectedStatus); else params.delete('status');
        var queryString = params.toString();
        window.history.replaceState(null, '', window.location.pathname + (queryString ? '?' + queryString : '') + window.location.hash);
    }

    function highlight(field, query) {
        var original = field.getAttribute('data-rtm-original');
        if (original === null) {
            original = field.textContent;
            field.setAttribute('data-rtm-original', original);
        }
        field.textContent = '';
        if (!query) {
            field.appendChild(document.createTextNode(original));
            return;
        }

        var matcher = new RegExp(query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
        var cursor = 0;
        var match;
        while ((match = matcher.exec(original)) !== null) {
            field.appendChild(document.createTextNode(original.slice(cursor, match.index)));
            var mark = document.createElement('mark');
            mark.className = 'tw-rounded tw-bg-amber-100 tw-px-0.5 tw-text-slate-950';
            mark.textContent = match[0];
            field.appendChild(mark);
            cursor = match.index + match[0].length;
            if (match[0] === '') matcher.lastIndex++;
        }
        field.appendChild(document.createTextNode(original.slice(cursor)));
    }

    function updateClearControl(query) {
        if (!clear) return;
        clear.classList.toggle('tw-hidden', !query);
        clear.classList.toggle('tw-flex', !!query);
    }

    function filter(syncUrl) {
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
            Array.prototype.forEach.call(row.querySelectorAll('[data-rtm-field]'), function (field) {
                highlight(field, query);
            });
        });

        updateClearControl(query);
        if (syncUrl) updateUrl(query, selectedStatus);

        if (empty) {
            if (visibleCount === 0 && rows.length > 0) {
                empty.classList.remove('tw-hidden');
            } else {
                empty.classList.add('tw-hidden');
            }
        }
    }

    function scheduleFilter() {
        window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(function () { filter(true); }, 300);
    }

    if (search) search.addEventListener('input', scheduleFilter);
    if (status) status.addEventListener('change', scheduleFilter);
    if (clear) clear.addEventListener('click', function () {
        if (!search) return;
        search.value = '';
        search.focus();
        scheduleFilter();
    });

    var initialParams = new URLSearchParams(window.location.search);
    if (search && initialParams.has('q')) search.value = initialParams.get('q') || '';
    if (status && initialParams.has('status')) status.value = initialParams.get('status') || '';
    filter(false);
})();
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
