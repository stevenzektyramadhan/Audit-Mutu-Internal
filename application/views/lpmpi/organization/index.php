<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

// Grouping hierarchy
$children = [];
foreach ($units as $unit) {
    $parent_key = $unit->parent_id === NULL ? 'root' : (int) $unit->parent_id;
    $children[$parent_key][] = $unit;
}

$assignment_counts = [];
foreach ($assignments as $assignment) {
    $assignment_counts[$assignment->organization_unit_id] = isset($assignment_counts[$assignment->organization_unit_id])
        ? $assignment_counts[$assignment->organization_unit_id] + 1
        : 1;
}

// Icon helper
$icon = static function ($name) {
    $paths = [
        'sitemap' => '<path d="M12 2v4M6 14v6M18 14v6M6 14h12M12 6v8M4 20h4M16 20h4M9 2h6v4H9z"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'user-plus' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M19 8v6M22 11h-6"/>',
        'shield' => '<path d="M12 3 4 7v5c0 4.4 3.4 7.7 8 9 4.6-1.3 8-4.6 8-9V7l-8-4Z"/><path d="m9 12 2 2 4-4"/>',
        'edit' => '<path d="m4 16-1 4 4-1L18 8l-3-3L4 16ZM13 6l3 3M20 4l1 1"/>',
        'toggle-on' => '<rect width="20" height="12" x="2" y="6" rx="6" ry="6"/><circle cx="16" cy="12" r="2"/>',
        'toggle-off' => '<rect width="20" height="12" x="2" y="6" rx="6" ry="6"/><circle cx="8" cy="12" r="2"/>',
        'users' => '<path d="M16 20v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 20v-1a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
    ];
    return '<svg class="org-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['sitemap']) . '</svg>';
};

$type_labels = [
    'university' => 'Universitas',
    'faculty' => 'Fakultas',
    'upps' => 'UPPS',
    'study_program' => 'Program Studi',
    'institute' => 'Institut',
    'bureau' => 'Biro',
    'unit' => 'Unit',
];

function render_organization_units($parent_key, $children, $counts, $can_manage, $level = 0, $icon = NULL, $type_labels = NULL) {
    if (empty($children[$parent_key])) return;
    if ($icon === NULL) {
        $icon = static function ($name) {
            $paths = [
                'sitemap' => '<path d="M12 2v4M6 14v6M18 14v6M6 14h12M12 6v8M4 20h4M16 20h4M9 2h6v4H9z"/>',
                'plus' => '<path d="M12 5v14M5 12h14"/>',
                'user-plus' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M19 8v6M22 11h-6"/>',
                'shield' => '<path d="M12 3 4 7v5c0 4.4 3.4 7.7 8 9 4.6-1.3 8-4.6 8-9V7l-8-4Z"/><path d="m9 12 2 2 4-4"/>',
                'edit' => '<path d="m4 16-1 4 4-1L18 8l-3-3L4 16ZM13 6l3 3M20 4l1 1"/>',
                'toggle-on' => '<rect width="20" height="12" x="2" y="6" rx="6" ry="6"/><circle cx="16" cy="12" r="2"/>',
                'toggle-off' => '<rect width="20" height="12" x="2" y="6" rx="6" ry="6"/><circle cx="8" cy="12" r="2"/>',
                'users' => '<path d="M16 20v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 20v-1a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
                'check' => '<polyline points="20 6 9 17 4 12"/>',
                'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
            ];
            return '<svg class="org-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['sitemap']) . '</svg>';
        };
    }
    if ($type_labels === NULL) {
        $type_labels = [
            'university' => 'Universitas',
            'faculty' => 'Fakultas',
            'upps' => 'UPPS',
            'study_program' => 'Program Studi',
            'institute' => 'Institut',
            'bureau' => 'Biro',
            'unit' => 'Unit',
        ];
    }

    echo '<div class="' . ($level > 0 ? 'org-tree-item' : 'tw-space-y-3') . '">';
    if ($level > 0) {
        echo '<div class="org-tree-connector"></div>';
    }

    foreach ($children[$parent_key] as $unit) {
        $is_active = (int) $unit->is_active === 1;
        $unit_count = (int) (isset($counts[$unit->id]) ? $counts[$unit->id] : 0);
        $type_label = isset($type_labels[$unit->type]) ? $type_labels[$unit->type] : $unit->type;

        echo '<div class="org-tree-node ' . (!$is_active ? 'is-inactive' : '') . '">';
        echo '  <div class="tw-flex tw-items-center tw-gap-3 tw-flex-wrap">';
        echo '    <span class="tw-font-bold text-dark tw-text-base">' . html_escape($unit->name) . '</span>';
        echo '    <span class="org-muted tw-text-xs tw-font-mono tw-bg-slate-100 tw-px-2 tw-py-0.5 tw-rounded">' . html_escape($unit->code) . '</span>';
        echo '    <span class="org-badge org-badge-type">' . html_escape($type_label) . '</span>';
        echo '    <span class="org-badge ' . ($is_active ? 'org-badge-active' : 'org-badge-inactive') . '">' . ($is_active ? 'Aktif' : 'Nonaktif') . '</span>';
        echo '    <span class="org-muted tw-text-xs tw-inline-flex tw-items-center tw-gap-1">' . $icon('users') . ' ' . $unit_count . ' penempatan</span>';
        echo '  </div>';

        if ($can_manage) {
            echo '  <div class="tw-flex tw-items-center tw-gap-2">';
            if ($is_active) {
                echo '    <a class="org-action-btn" href="' . site_url('lpmpi/organization/unit/create?parent_id=' . (int) $unit->id) . '" title="Tambah Sub-unit">';
                echo        $icon('plus') . ' <span>Sub-unit</span>';
                echo '    </a>';
            }
            if ($unit->parent_id !== NULL) {
                echo '    <a class="org-action-btn" href="' . site_url('lpmpi/organization/unit/edit/' . (int) $unit->id) . '" title="Edit unit">';
                echo        $icon('edit') . ' <span>Edit</span>';
                echo '    </a>';
                echo form_open('lpmpi/organization/unit/toggle/' . (int) $unit->id, ['class' => 'tw-inline']);
                echo '      <button class="org-action-btn ' . ($is_active ? 'warning' : 'tw-text-emerald-700 hover:tw-bg-emerald-50') . '" type="submit" title="' . ($is_active ? 'Nonaktifkan unit' : 'Aktifkan unit') . '">';
                echo          ($is_active ? $icon('toggle-on') : $icon('toggle-off')) . ' <span>' . ($is_active ? 'Nonaktifkan' : 'Aktifkan') . '</span>';
                echo '      </button>';
                echo form_close();
            }
            echo '  </div>';
        }
        echo '</div>';

        render_organization_units((int) $unit->id, $children, $counts, $can_manage, $level + 1, $icon, $type_labels);
    }
    echo '</div>';
}
// Tab state
$current_tab = isset($active_tab) && in_array($active_tab, ['structure', 'assignments', 'access'], TRUE)
    ? $active_tab
    : 'structure';
?>

<div id="organization-root" class="tw-mx-auto tw-max-w-screen-2xl">
    <!-- Hero Banner -->
    <section class="org-hero tw-mb-6 tw-flex tw-flex-col tw-items-start tw-justify-between tw-gap-4 tw-rounded-lg tw-border tw-p-5 sm:tw-flex-row sm:tw-items-center">
        <div>
            <p class="org-eyebrow">Pengaturan Kelembagaan</p>
            <h2 class="tw-m-0">Struktur Organisasi</h2>
            <p class="org-muted tw-mb-0 tw-mt-1">Kelola bagan hierarki unit kerja, penempatan pejabat, dan wewenang organisasi.</p>
        </div>
        <div class="tw-flex tw-items-center tw-gap-2 tw-flex-wrap">
            <?php if ($can_manage): ?>
                <a class="org-button org-button-primary" href="<?php echo site_url('lpmpi/organization/unit/create'); ?>">
                    <?php echo $icon('plus'); ?> Tambah Unit
                </a>
            <?php endif; ?>
            <?php if ($can_assign): ?>
                <a class="org-button org-button-secondary" href="<?php echo site_url('lpmpi/organization/assignment/create'); ?>">
                    <?php echo $icon('user-plus'); ?> Tambah Penempatan
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Navigation Tabs -->
    <div class="org-tabs" role="tablist">
        <button type="button" class="org-tab-button <?php echo $current_tab === 'structure' ? 'is-active' : ''; ?>" data-org-tab="structure" role="tab" aria-selected="<?php echo $current_tab === 'structure' ? 'true' : 'false'; ?>" aria-controls="tab-structure">
            <?php echo $icon('sitemap'); ?> Struktur Unit
        </button>
        <button type="button" class="org-tab-button <?php echo $current_tab === 'assignments' ? 'is-active' : ''; ?>" data-org-tab="assignments" role="tab" aria-selected="<?php echo $current_tab === 'assignments' ? 'true' : 'false'; ?>" aria-controls="tab-assignments">
            <?php echo $icon('users'); ?> Penempatan (<?php echo count($assignments); ?>)
        </button>
        <?php if ($can_view_capabilities): ?>
            <button type="button" class="org-tab-button <?php echo $current_tab === 'access' ? 'is-active' : ''; ?>" data-org-tab="access" role="tab" aria-selected="<?php echo $current_tab === 'access' ? 'true' : 'false'; ?>" aria-controls="tab-access">
                <?php echo $icon('shield'); ?> Hak Akses Organisasi
            </button>
        <?php endif; ?>
    </div>

    <!-- TAB 1: Struktur Unit -->
    <div id="tab-structure" class="org-tab-pane <?php echo $current_tab === 'structure' ? '' : 'tw-hidden'; ?>" role="tabpanel">
        <section class="org-surface">
            <div class="org-surface-body">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4 tw-flex-wrap tw-gap-2">
                    <div>
                        <h3 class="tw-text-lg tw-font-bold tw-m-0">Bagan Hierarki Unit Kerja</h3>
                        <p class="org-muted tw-text-xs tw-mt-1">Pohon struktur organisasi dari tingkat universitas hingga unit terkecil.</p>
                    </div>
                </div>

                <?php if (empty($units)): ?>
                    <div class="tw-text-center tw-py-12 tw-text-slate-500">
                        <div class="tw-mb-3"><?php echo $icon('sitemap'); ?></div>
                        <p class="tw-font-bold tw-text-base tw-text-slate-700">Belum ada struktur organisasi</p>
                        <p class="tw-text-sm">Tambahkan unit kerja root terlebih dahulu untuk menyusun hierarki.</p>
                    </div>
                <?php else: ?>
                    <div class="tw-mt-4">
                        <?php render_organization_units('root', $children, $assignment_counts, $can_manage); ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- TAB 2: Penempatan -->
    <div id="tab-assignments" class="org-tab-pane <?php echo $current_tab === 'assignments' ? '' : 'tw-hidden'; ?>" role="tabpanel">
        <section class="org-surface">
            <div class="org-surface-body">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4 tw-flex-wrap tw-gap-2">
                    <div>
                        <h3 class="tw-text-lg tw-font-bold tw-m-0">Daftar Penempatan Pegawai</h3>
                        <p class="org-muted tw-text-xs tw-mt-1">Seluruh data riwayat dan penempatan aktif pengguna dalam unit kerja.</p>
                    </div>
                    <?php if ($can_assign): ?>
                        <a class="org-button org-button-primary tw-text-xs" href="<?php echo site_url('lpmpi/organization/assignment/create'); ?>">
                            <?php echo $icon('user-plus'); ?> Tambah Penempatan Baru
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($assignments)): ?>
                    <div class="tw-text-center tw-py-12 tw-text-slate-500">
                        <div class="tw-mb-3"><?php echo $icon('users'); ?></div>
                        <p class="tw-font-bold tw-text-base tw-text-slate-700">Belum ada data penempatan</p>
                        <p class="tw-text-sm">Gunakan tombol 'Tambah Penempatan' untuk menetapkan pengguna ke unit kerja.</p>
                    </div>
                <?php else: ?>
                    <div class="org-table-wrap">
                        <table class="org-table">
                            <thead>
                                <tr>
                                    <th>Pegawai</th>
                                    <th>Unit Kerja</th>
                                    <th>Jabatan / Posisi</th>
                                    <th>Masa Berlaku</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $today = date('Y-m-d');
                                foreach ($assignments as $row):
                                    $is_ended = !empty($row->valid_until) && $row->valid_until < $today;
                                    $is_current = $row->valid_from <= $today && (empty($row->valid_until) || $row->valid_until >= $today);
                                ?>
                                    <tr>
                                        <td data-label="Pegawai">
                                            <strong><?php echo html_escape($row->nama); ?></strong><br>
                                            <span class="org-muted tw-text-xs"><?php echo html_escape($row->email); ?></span>
                                        </td>
                                        <td data-label="Unit Kerja">
                                            <strong><?php echo html_escape($row->unit_name); ?></strong><br>
                                            <span class="org-muted tw-text-xs tw-font-mono"><?php echo html_escape($row->unit_code); ?></span>
                                        </td>
                                        <td data-label="Jabatan">
                                            <span class="tw-font-bold tw-text-sm text-dark"><?php echo html_escape($row->position_code); ?></span>
                                            <?php if ((int) $row->is_primary === 1): ?>
                                                <span class="org-badge org-badge-primary tw-ml-1">Utama</span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Masa Berlaku">
                                            <span class="tw-text-xs">
                                                <?php echo html_escape($row->valid_from); ?> s/d
                                                <?php echo !empty($row->valid_until) ? html_escape($row->valid_until) : '<span class="tw-text-emerald-700 tw-font-bold">Sekarang</span>'; ?>
                                            </span>
                                        </td>
                                        <td data-label="Status">
                                            <?php if ($is_ended): ?>
                                                <span class="org-badge org-badge-inactive">Selesai</span>
                                            <?php elseif ($is_current): ?>
                                                <span class="org-badge org-badge-active">Aktif</span>
                                            <?php else: ?>
                                                <span class="org-badge org-badge-type">Terjadwal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Aksi">
                                            <?php if ($can_assign && !$is_ended): ?>
                                                <?php echo form_open('lpmpi/organization/assignment/end/' . (int) $row->id, ['class' => 'tw-flex tw-items-center tw-gap-1 tw-flex-wrap', 'onsubmit' => "return confirm('Akhiri penempatan untuk " . html_escape($row->nama) . "?');"]); ?>
                                                    <input type="date" name="valid_until" value="<?php echo date('Y-m-d'); ?>" required class="tw-text-xs tw-border tw-rounded tw-px-2 tw-py-1 tw-border-slate-300">
                                                    <button type="submit" class="org-action-btn danger tw-text-xs">
                                                        Akhiri
                                                    </button>
                                                <?php echo form_close(); ?>
                                            <?php else: ?>
                                                <span class="org-muted tw-text-xs">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- TAB 3: Hak Akses Organisasi -->
    <?php if ($can_view_capabilities): ?>
        <div id="tab-access" class="org-tab-pane <?php echo $current_tab === 'access' ? '' : 'tw-hidden'; ?>" role="tabpanel">
            <section class="org-surface">
                <div class="org-surface-body">
                    <div class="tw-mb-4">
                        <h3 class="tw-text-lg tw-font-bold tw-m-0">Matriks Hak Akses Organisasi</h3>
                        <p class="org-muted tw-text-xs tw-mt-1">Pengaturan wewenang pengelolaan struktur dan penempatan organisasi per role.</p>
                    </div>

                    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-6">
                        <?php
                        $roles_to_display = Organization_service::ROLES;
                        require_once APPPATH . 'services/Organization_service.php';
                        $org_service = new Organization_service();
                        $capabilities_list = $org_service->capabilities();

                        foreach ($roles_to_display as $role_key):
                            $role_caps = $org_service->role_codes($role_key);
                            $is_super = $role_key === 'super_admin';
                        ?>
                            <div class="tw-border tw-border-slate-200 tw-rounded-lg tw-p-5 tw-bg-slate-50">
                                <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
                                    <h4 class="tw-text-base tw-font-bold tw-m-0 tw-text-slate-800">
                                        <?php echo $role_key === 'super_admin' ? 'Super Admin' : 'Admin LPMPI'; ?>
                                    </h4>
                                    <span class="org-badge org-badge-type tw-font-mono"><?php echo html_escape($role_key); ?></span>
                                </div>

                                <?php if ($can_capabilities): ?>
                                    <?php echo form_open('lpmpi/organization/capabilities/update'); ?>
                                        <input type="hidden" name="role" value="<?php echo html_escape($role_key); ?>">
                                        <div class="tw-space-y-3 tw-mb-4">
                                            <?php foreach ($capabilities_list as $cap):
                                                $checked = in_array($cap->code, $role_caps, TRUE);
                                                $locked = $is_super && in_array($cap->code, ['organization.view', 'organization.capability.manage'], TRUE);
                                            ?>
                                                <label class="tw-flex tw-items-start tw-gap-3 tw-p-2 tw-rounded hover:tw-bg-white tw-cursor-pointer tw-border tw-border-transparent hover:tw-border-slate-200">
                                                    <input type="checkbox" name="capability_ids[]" value="<?php echo (int) $cap->id; ?>"
                                                           <?php echo $checked ? 'checked' : ''; ?>
                                                           <?php echo $locked ? 'disabled' : ''; ?>
                                                           class="tw-mt-1 tw-rounded tw-text-blue-600">
                                                    <?php if ($locked): ?>
                                                        <input type="hidden" name="capability_ids[]" value="<?php echo (int) $cap->id; ?>">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong class="tw-text-sm tw-text-slate-800 tw-block"><?php echo html_escape($cap->label); ?></strong>
                                                        <span class="org-muted tw-text-xs"><?php echo html_escape($cap->description); ?></span>
                                                        <?php if ($locked): ?>
                                                            <span class="tw-text-amber-700 tw-text-[10px] tw-font-bold tw-block">(Wajib untuk Super Admin)</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="submit" class="org-button org-button-primary tw-text-xs tw-w-full tw-justify-center">
                                            Simpan Hak Akses <?php echo $role_key === 'super_admin' ? 'Super Admin' : 'Admin LPMPI'; ?>
                                        </button>
                                    <?php echo form_close(); ?>
                                <?php else: ?>
                                    <div class="tw-space-y-2">
                                        <?php foreach ($capabilities_list as $cap):
                                            $has_cap = in_array($cap->code, $role_caps, TRUE);
                                        ?>
                                            <div class="tw-flex tw-items-center tw-justify-between tw-p-2 tw-rounded tw-bg-white tw-border tw-border-slate-200">
                                                <div>
                                                    <strong class="tw-text-xs tw-text-slate-800 tw-block"><?php echo html_escape($cap->label); ?></strong>
                                                    <span class="org-muted tw-text-[11px]"><?php echo html_escape($cap->description); ?></span>
                                                </div>
                                                <span>
                                                    <?php if ($has_cap): ?>
                                                        <span class="org-badge org-badge-active"><?php echo $icon('check'); ?> Aktif</span>
                                                    <?php else: ?>
                                                        <span class="org-badge org-badge-inactive">Tidak Aktif</span>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </div>
    <?php endif; ?>
</div>

<!-- Tab Interactivity Script (Vanilla JS) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tabs = document.querySelectorAll('[data-org-tab]');
    var panes = document.querySelectorAll('.org-tab-pane');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var target = tab.getAttribute('data-org-tab');

            tabs.forEach(function (t) {
                t.classList.remove('is-active');
                t.setAttribute('aria-selected', 'false');
            });
            panes.forEach(function (p) {
                p.classList.add('tw-hidden');
            });

            tab.classList.add('is-active');
            tab.setAttribute('aria-selected', 'true');

            var activePane = document.getElementById('tab-' + target);
            if (activePane) {
                activePane.classList.remove('tw-hidden');
            }

            if (window.history && window.history.replaceState) {
                var url = new URL(window.location.href);
                url.searchParams.set('tab', target);
                window.history.replaceState(null, '', url.toString());
            }
        });
    });
});
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
