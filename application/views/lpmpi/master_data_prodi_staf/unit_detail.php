<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$assignments = isset($assignments) && is_array($assignments) ? $assignments : [];
$type_labels = ['university' => 'Universitas', 'faculty' => 'Fakultas', 'study_program' => 'Program Studi', 'bureau' => 'Biro', 'unit' => 'Unit', 'institute' => 'Lembaga', 'upps' => 'UPPS'];
$unit_type = (string) $unit->type;
$today = date('Y-m-d');
$is_current = static function ($assignment, $today) { return (string) $assignment->valid_from <= $today && (empty($assignment->valid_until) || (string) $assignment->valid_until > $today); };

$format_date_id = static function ($date_str) {
    if (empty($date_str) || $date_str === 'Sekarang') return 'Sekarang';
    $time = strtotime($date_str);
    if (!$time) return (string) $date_str;
    $months = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
    return date('j', $time) . ' ' . $months[(int) date('n', $time)] . ' ' . date('Y', $time);
};

$format_position = static function ($code) {
    $code_str = trim((string) $code);
    if ($code_str === '') return '-';
    $known = [
        'KEPALA_BIRO' => 'Kepala Biro',
        'STAF_ADMIN' => 'Staf Administrasi',
        'KETUA_LEMBAGA' => 'Ketua Lembaga',
        'DOSEN_PENELITI' => 'Dosen Peneliti',
        'KEPALA_UNIT' => 'Kepala Unit',
        'SEKRETARIS' => 'Sekretaris',
        'BENDAHARA' => 'Bendahara',
        'DEKAN' => 'Dekan',
        'WAKIL_DEKAN' => 'Wakil Dekan',
        'KAPRODI' => 'Ketua Program Studi',
        'SEKPRODI' => 'Sekretaris Program Studi',
    ];
    if (isset($known[$code_str])) return $known[$code_str];
    return ucwords(strtolower(str_replace('_', ' ', $code_str)));
};

$parse_user_name_title = static function ($raw_name) {
    $raw = trim((string) $raw_name);
    if (preg_match('/^(.*?)\s*\((.*?)\)$/', $raw, $matches)) {
        return ['name' => trim($matches[1]), 'title' => trim($matches[2])];
    }
    return ['name' => $raw, 'title' => ''];
};

$active_assignments_count = 0;
foreach ($assignments as $a) {
    if ($is_current($a, $today)) {
        $active_assignments_count++;
    }
}

$icon = static function ($name, $class = 'tw-w-4 tw-h-4') {
    $paths = [
        'arrow-left' => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
        'pencil' => '<path d="M21.174 6.812a1 1 0 0 0-1.414 0l-1.05 1.05 2.828 2.828 1.05-1.05a1 1 0 0 0 0-1.414z"/><path d="M17.296 9.276 6.12 20.452l-3.536.708.708-3.536L14.468 6.448z"/>',
        'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'briefcase' => '<rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        'calendar' => '<rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>',
        'power' => '<path d="M12 2v10"/><path d="M18.4 6.6a9 9 0 1 1-12.77.04"/>',
    ];
    if (!isset($paths[$name])) return '';
    return '<svg xmlns="http://www.w3.org/2000/svg" class="' . html_escape($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[$name] . '</svg>';
};

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<main id="master-data-prodi-staf" class="master-data-root">
    <div id="organization-root">
        <div class="unit-detail-root tw-space-y-6">
            <div class="master-data-breadcrumb">Beranda / Management / Master Data Organisasi &amp; Staf / Detail Unit</div>

            <section class="unit-detail-card" aria-labelledby="unit-detail-title">
                <div class="tw-flex tw-flex-col lg:tw-flex-row lg:tw-items-center lg:tw-justify-between tw-gap-4 tw-pb-6 tw-border-b tw-border-slate-100">
                    <div class="tw-min-w-0">
                        <div class="ami-eyebrow tw-inline-flex tw-items-center tw-gap-1.5 tw-text-xs tw-font-semibold tw-text-blue-600 tw-uppercase tw-tracking-wider tw-mb-1">
                            Unit organisasi
                        </div>
                        <h2 id="unit-detail-title" class="ami-section-title tw-text-xl sm:tw-text-2xl tw-font-bold tw-text-slate-900 tw-tracking-tight tw-m-0">
                            <?php echo html_escape($unit->name); ?>
                        </h2>
                        <p class="master-code tw-mt-1.5 tw-mb-0 tw-text-sm tw-text-slate-600 tw-font-medium">
                            <span class="tw-font-mono tw-text-slate-800"><?php echo html_escape($unit->code); ?></span>
                            <span class="tw-mx-1.5 tw-text-slate-300">·</span>
                            <span><?php echo html_escape(isset($type_labels[$unit_type]) ? $type_labels[$unit_type] : $unit_type); ?></span>
                        </p>
                    </div>

                    <div class="master-controls tw-flex tw-items-center tw-gap-2.5 tw-flex-wrap sm:tw-flex-nowrap">
                        <a class="btn btn-outline-ami btn-ami tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-h-11 tw-px-4 tw-text-sm tw-font-medium tw-rounded-xl tw-border tw-border-slate-300 tw-text-slate-700 tw-bg-white hover:tw-bg-slate-50 tw-transition-colors" href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf')); ?>">
                            <?php echo $icon('arrow-left', 'tw-w-4 tw-h-4'); ?>
                            <span>Kembali</span>
                        </a>
                        <?php if ($unit->parent_id !== NULL && $unit_type !== 'study_program'): ?>
                            <a class="btn btn-primary btn-ami tw-inline-flex tw-items-center tw-justify-center tw-gap-2 tw-h-11 tw-px-5 tw-text-sm tw-font-semibold tw-rounded-xl tw-bg-blue-600 hover:tw-bg-blue-700 tw-text-white tw-shadow-sm tw-transition-colors" href="<?php echo html_escape(site_url('lpmpi/master-data-prodi-staf/unit/edit/' . (int) $unit->id)); ?>">
                                <?php echo $icon('pencil', 'tw-w-4 tw-h-4'); ?>
                                <span>Edit Unit</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="unit-metadata-grid tw-mt-6">
                    <div class="tw-min-w-0">
                        <small class="tw-block tw-text-xs tw-font-medium tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-1">Parent</small>
                        <?php if ($unit->parent_id === NULL): ?>
                            <strong class="tw-text-sm tw-font-semibold tw-text-slate-800">Root</strong>
                        <?php elseif ($parent_unit): ?>
                            <div class="tw-truncate">
                                <strong class="tw-text-sm tw-font-semibold tw-text-slate-900 tw-block tw-truncate" title="<?php echo html_escape($parent_unit->name); ?>">
                                    <?php echo html_escape($parent_unit->name); ?>
                                </strong>
                                <span class="tw-text-xs tw-font-mono tw-text-slate-500">
                                    (<?php echo html_escape($parent_unit->code); ?>)
                                </span>
                            </div>
                        <?php else: ?>
                            <strong class="tw-text-sm tw-font-semibold tw-text-slate-500">Unit induk tidak ditemukan</strong>
                        <?php endif; ?>
                    </div>

                    <div class="tw-min-w-0">
                        <small class="tw-block tw-text-xs tw-font-medium tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-1">Tipe</small>
                        <strong class="tw-text-sm tw-font-semibold tw-text-slate-900">
                            <?php echo html_escape(isset($type_labels[$unit_type]) ? $type_labels[$unit_type] : $unit_type); ?>
                        </strong>
                    </div>

                    <div class="tw-min-w-0">
                        <small class="tw-block tw-text-xs tw-font-medium tw-text-slate-500 tw-uppercase tw-tracking-wider tw-mb-1">Status</small>
                        <div class="tw-inline-flex tw-items-center tw-gap-1.5">
                            <?php if ((int) $unit->is_active === 1): ?>
                                <span class="master-status-badge master-status-active tw-inline-flex tw-items-center tw-gap-1.5 tw-px-2.5 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-emerald-50 tw-text-emerald-700 tw-border tw-border-emerald-200">
                                    <span class="tw-w-1.5 tw-h-1.5 tw-rounded-full tw-bg-emerald-500" aria-hidden="true"></span>
                                    <span>Aktif</span>
                                </span>
                            <?php else: ?>
                                <span class="master-status-badge master-status-inactive tw-inline-flex tw-items-center tw-gap-1.5 tw-px-2.5 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-slate-100 tw-text-slate-600 tw-border tw-border-slate-200">
                                    <span class="tw-w-1.5 tw-h-1.5 tw-rounded-full tw-bg-slate-400" aria-hidden="true"></span>
                                    <span>Nonaktif</span>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <section class="unit-detail-card" aria-labelledby="unit-placement-title">
                <div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-center sm:tw-justify-between tw-gap-3 tw-pb-5 tw-border-b tw-border-slate-100">
                    <div>
                        <div class="tw-flex tw-items-center tw-gap-2.5">
                            <h3 id="unit-placement-title" class="tw-text-lg tw-font-bold tw-text-slate-900 tw-tracking-tight tw-m-0">
                                Penempatan Non-Prodi
                            </h3>
                            <?php if ($active_assignments_count > 0): ?>
                                <span class="tw-inline-flex tw-items-center tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-blue-50 tw-text-blue-700 tw-border tw-border-blue-100">
                                    <?php echo $active_assignments_count; ?> aktif
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="tw-mt-1 tw-mb-0 tw-text-sm tw-text-slate-600">
                            Riwayat penempatan staf pada unit ini. Penempatan aktif yang sedang berlaku dapat diakhiri.
                        </p>
                    </div>
                </div>

                <?php if (empty($assignments)): ?>
                    <div class="tw-py-12 tw-px-4 tw-text-center">
                        <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-12 tw-h-12 tw-rounded-full tw-bg-slate-100 tw-text-slate-400 tw-mb-3">
                            <?php echo $icon('users', 'tw-w-6 tw-h-6'); ?>
                        </div>
                        <h4 class="tw-text-base tw-font-semibold tw-text-slate-800 tw-m-0">Belum ada penempatan staf</h4>
                        <p class="tw-text-sm tw-text-slate-500 tw-mt-1 tw-mb-0">Unit ini belum memiliki riwayat penempatan Non-Prodi.</p>
                        <div class="ami-empty tw-sr-only">Belum ada penempatan pada unit ini.</div>
                    </div>
                <?php else: ?>
                    <div class="master-table-wrap tw-hidden md:tw-block tw-mt-4 tw-overflow-x-auto">
                        <table class="table ami-table tw-w-full tw-text-left tw-text-sm">
                            <thead>
                                <tr class="tw-border-b tw-border-slate-200 tw-bg-slate-50/75 tw-text-slate-600 tw-text-xs tw-uppercase tw-tracking-wider">
                                    <th class="tw-py-3.5 tw-px-4 tw-font-semibold">Pengguna</th>
                                    <th class="tw-py-3.5 tw-px-4 tw-font-semibold">Posisi</th>
                                    <th class="tw-py-3.5 tw-px-4 tw-font-semibold">Masa Berlaku</th>
                                    <th class="tw-py-3.5 tw-px-4 tw-font-semibold">Status</th>
                                    <th class="tw-py-3.5 tw-px-4 tw-font-semibold tw-text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="tw-divide-y tw-divide-slate-100">
                                <?php foreach ($assignments as $assignment): ?>
                                    <?php
                                    $current = $is_current($assignment, $today);
                                    $parsed_user = $parse_user_name_title($assignment->nama);
                                    ?>
                                    <tr class="hover:tw-bg-slate-50/60 tw-transition-colors">
                                        <td class="tw-py-3.5 tw-px-4">
                                            <div class="tw-font-semibold tw-text-slate-900">
                                                <?php echo html_escape($parsed_user['name']); ?>
                                            </div>
                                            <?php if ($parsed_user['title'] !== ''): ?>
                                                <div class="tw-text-xs tw-font-medium tw-text-slate-500 tw-mt-0.5">
                                                    <?php echo html_escape($parsed_user['title']); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="tw-inline-flex tw-items-center tw-gap-1 tw-text-xs tw-text-slate-400 tw-mt-0.5">
                                                <?php echo $icon('mail', 'tw-w-3.5 tw-h-3.5'); ?>
                                                <span class="master-subtext"><?php echo html_escape($assignment->email); ?></span>
                                            </div>
                                        </td>
                                        <td class="tw-py-3.5 tw-px-4">
                                            <div class="tw-font-medium tw-text-slate-800">
                                                <?php echo html_escape($format_position($assignment->position_code)); ?>
                                            </div>
                                            <span class="tw-sr-only"><?php echo html_escape($assignment->position_code); ?></span>
                                            <?php if ((int) $assignment->is_primary === 1): ?>
                                                <span class="master-mapping-badge master-mapping-mapped tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded tw-text-[11px] tw-font-medium tw-bg-blue-50 tw-text-blue-700 tw-border tw-border-blue-200 tw-mt-1">
                                                    Utama
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="tw-py-3.5 tw-px-4 tw-whitespace-nowrap">
                                            <div class="tw-text-xs tw-font-medium tw-text-slate-700">
                                                <span><?php echo html_escape($format_date_id($assignment->valid_from)); ?></span>
                                                <span class="tw-text-slate-400 tw-mx-1">—</span>
                                                <span><?php echo html_escape($format_date_id($assignment->valid_until)); ?></span>
                                            </div>
                                            <span class="tw-sr-only"><?php echo html_escape($assignment->valid_from); ?> s/d <?php echo html_escape($assignment->valid_until ?: 'Sekarang'); ?></span>
                                        </td>
                                        <td class="tw-py-3.5 tw-px-4 tw-whitespace-nowrap">
                                            <?php if ($current): ?>
                                                <span class="master-status-badge master-status-active tw-inline-flex tw-items-center tw-gap-1.5 tw-px-2.5 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-emerald-50 tw-text-emerald-700 tw-border tw-border-emerald-200">
                                                    <span class="tw-w-1.5 tw-h-1.5 tw-rounded-full tw-bg-emerald-500" aria-hidden="true"></span>
                                                    <span>Aktif</span>
                                                </span>
                                            <?php else: ?>
                                                <span class="master-status-badge master-status-inactive tw-inline-flex tw-items-center tw-gap-1.5 tw-px-2.5 tw-py-1 tw-rounded-full tw-text-xs tw-font-medium tw-bg-slate-100 tw-text-slate-600 tw-border tw-border-slate-200">
                                                    <span class="tw-w-1.5 tw-h-1.5 tw-rounded-full tw-bg-slate-400" aria-hidden="true"></span>
                                                    <span>Selesai / Terjadwal</span>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="tw-py-3.5 tw-px-4 tw-text-right tw-whitespace-nowrap">
                                            <?php if ($current && in_array($unit_type, ['faculty', 'bureau', 'unit', 'institute'], TRUE)): ?>
                                                <?php echo form_open('lpmpi/master-data-prodi-staf/placement/end/' . (int) $assignment->id, ['class' => 'd-inline']); ?>
                                                    <input type="hidden" name="valid_until" value="<?php echo html_escape($today); ?>">
                                                    <button type="submit" class="unit-action-btn-danger" onclick="return window.confirm('Akhiri penempatan ini?');">
                                                        <?php echo $icon('power', 'tw-w-3.5 tw-h-3.5'); ?>
                                                        <span>Akhiri</span>
                                                    </button>
                                                <?php echo form_close(); ?>
                                            <?php else: ?>
                                                <span class="tw-text-slate-400 tw-text-xs">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="md:tw-hidden tw-mt-4 tw-space-y-3">
                        <?php foreach ($assignments as $assignment): ?>
                            <?php
                            $current = $is_current($assignment, $today);
                            $parsed_user = $parse_user_name_title($assignment->nama);
                            ?>
                            <div class="tw-p-4 tw-rounded-xl tw-border tw-border-slate-200 tw-bg-white tw-shadow-sm tw-space-y-3">
                                <div class="tw-flex tw-items-start tw-justify-between tw-gap-3">
                                    <div class="tw-min-w-0">
                                        <h4 class="tw-text-sm tw-font-bold tw-text-slate-900 tw-m-0">
                                            <?php echo html_escape($parsed_user['name']); ?>
                                        </h4>
                                        <?php if ($parsed_user['title'] !== ''): ?>
                                            <p class="tw-text-xs tw-font-medium tw-text-slate-500 tw-mt-0.5 tw-mb-0">
                                                <?php echo html_escape($parsed_user['title']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <div class="tw-inline-flex tw-items-center tw-gap-1 tw-text-xs tw-text-slate-400 tw-mt-0.5">
                                            <?php echo $icon('mail', 'tw-w-3.5 tw-h-3.5'); ?>
                                            <span><?php echo html_escape($assignment->email); ?></span>
                                        </div>
                                    </div>
                                    <div>
                                        <?php if ($current): ?>
                                            <span class="tw-inline-flex tw-items-center tw-gap-1 tw-px-2 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-emerald-50 tw-text-emerald-700 tw-border tw-border-emerald-200">
                                                <span class="tw-w-1.5 tw-h-1.5 tw-rounded-full tw-bg-emerald-500"></span>
                                                <span>Aktif</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="tw-inline-flex tw-items-center tw-px-2 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium tw-bg-slate-100 tw-text-slate-600 tw-border tw-border-slate-200">
                                                <span>Selesai</span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="tw-grid tw-grid-cols-2 tw-gap-2 tw-pt-2 tw-border-t tw-border-slate-100 tw-text-xs">
                                    <div>
                                        <span class="tw-text-slate-500 tw-block tw-mb-0.5">Posisi</span>
                                        <span class="tw-font-semibold tw-text-slate-800">
                                            <?php echo html_escape($format_position($assignment->position_code)); ?>
                                        </span>
                                        <?php if ((int) $assignment->is_primary === 1): ?>
                                            <span class="tw-block tw-text-[11px] tw-text-blue-600 tw-font-medium tw-mt-0.5">
                                                (Utama)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <span class="tw-text-slate-500 tw-block tw-mb-0.5">Masa Berlaku</span>
                                        <span class="tw-font-medium tw-text-slate-800">
                                            <?php echo html_escape($format_date_id($assignment->valid_from)); ?> — <?php echo html_escape($format_date_id($assignment->valid_until)); ?>
                                        </span>
                                    </div>
                                </div>

                                <?php if ($current && in_array($unit_type, ['faculty', 'bureau', 'unit', 'institute'], TRUE)): ?>
                                    <div class="tw-pt-2 tw-border-t tw-border-slate-100 tw-flex tw-justify-end">
                                        <?php echo form_open('lpmpi/master-data-prodi-staf/placement/end/' . (int) $assignment->id, ['class' => 'tw-w-full sm:tw-w-auto']); ?>
                                            <input type="hidden" name="valid_until" value="<?php echo html_escape($today); ?>">
                                            <button type="submit" class="unit-action-btn-danger tw-w-full sm:tw-w-auto tw-justify-center" onclick="return window.confirm('Akhiri penempatan ini?');">
                                                <?php echo $icon('power', 'tw-w-3.5 tw-h-3.5'); ?>
                                                <span>Akhiri Penempatan</span>
                                            </button>
                                        <?php echo form_close(); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
