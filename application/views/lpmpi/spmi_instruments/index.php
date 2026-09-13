<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

// Icon helper
$icon = static function ($name) {
    $paths = [
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'box' => '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'lock' => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'edit' => '<path d="m4 16-1 4 4-1L18 8l-3-3L4 16ZM13 6l3 3M20 4l1 1"/>',
        'external-link' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>',
        'trash' => '<path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
        'x' => '<path d="M18 6 6 18M6 6l12 12"/>',
        'filter' => '<path d="M4 5h16M7 12h10M10 19h4"/>',
        'undo' => '<path d="M9 14 4 9l5-5M4 9h10a6 6 0 0 1 6 6v1"/>',
    ];
    return '<svg class="inst-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['box']) . '</svg>';
};

// Hitung jumlah pertanyaan per paket
$ci =& get_instance();
$ci->load->model('Spmi_instruments_model');
$question_counts = [];
foreach ($packages as $pkg) {
    $question_counts[$pkg->id] = $ci->Spmi_instruments_model->count_questions($pkg->id);
}

// Koleksi versi & standar untuk modal selector pembuatan paket
// Hanya versi mutable (draft / review) yang dapat dipilih
$mutable_standards_by_version = [];
$version_options = [];
foreach ($standards as $std) {
    $v_status = strtolower($std->version_status);
    if (in_array($v_status, ['draft', 'review'], TRUE)) {
        $v_id = (int) $std->version_id;
        $version_options[$v_id] = $std->version_code . ' — ' . $std->version_title;
        $mutable_standards_by_version[$v_id][] = [
            'id' => (int) $std->id,
            'code' => $std->standard_code,
            'title' => $std->title,
        ];
    }
}
?>

<div id="instruments-root" class="tw-mx-auto tw-max-w-screen-2xl">
    <!-- Hero Banner -->
    <section class="inst-hero tw-mb-6 tw-flex tw-flex-col tw-items-start tw-justify-between tw-gap-4 tw-rounded-lg tw-border tw-p-5 sm:tw-flex-row sm:tw-items-center">
        <div>
            <p class="inst-eyebrow">Instrumen Penilaian</p>
            <h2 class="tw-m-0">Instrumen Audit SPMI</h2>
            <p class="inst-muted tw-mb-0 tw-mt-1">Kelola paket dan butir instrumen audit untuk setiap standar mutu internal.</p>
        </div>
        <div>
            <button type="button" class="inst-button inst-button-primary" data-modal-trigger="modal-create-package">
                <?php echo $icon('plus'); ?> Buat Paket Baru
            </button>
        </div>
    </section>

    <!-- Package-Centric List Section -->
    <section class="inst-surface">
        <div class="inst-surface-body">
            <!-- Filter & Search Toolbar -->
            <div class="tw-flex tw-flex-col lg:tw-flex-row lg:tw-items-center lg:tw-justify-between tw-gap-4 tw-mb-6 tw-pb-5 tw-border-b tw-border-slate-200">
                <div class="tw-flex tw-items-center tw-gap-2">
                    <span class="tw-font-bold tw-text-base tw-text-slate-800">Daftar Paket Instrumen</span>
                    <span class="inst-badge inst-badge-draft tw-font-mono"><?php echo count($packages); ?> Total</span>
                </div>

                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-items-stretch sm:tw-items-center tw-gap-2.5">
                    <input type="search" id="pkg-search" placeholder="Cari paket / standar..." class="inst-control tw-text-xs sm:tw-w-56" data-filter-search>
                    <select id="pkg-version-filter" class="inst-control tw-text-xs sm:tw-w-44" data-filter-version>
                        <option value="">Semua Versi</option>
                        <?php
                        $seen_versions = [];
                        foreach ($packages as $p) {
                            if (!isset($seen_versions[$p->version_code])) {
                                $seen_versions[$p->version_code] = true;
                                echo '<option value="' . html_escape($p->version_code) . '">' . html_escape($p->version_code) . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <select id="pkg-status-filter" class="inst-control tw-text-xs sm:tw-w-36" data-filter-status>
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="review">Review</option>
                        <option value="approved">Approved</option>
                        <option value="active">Active</option>
                        <option value="retired">Retired</option>
                    </select>
                </div>
            </div>

            <?php if (empty($packages)): ?>
                <div class="tw-text-center tw-py-12 tw-text-slate-500">
                    <div class="tw-mb-3"><?php echo $icon('box'); ?></div>
                    <p class="tw-font-bold tw-text-base tw-text-slate-700">Belum ada paket instrumen</p>
                    <p class="tw-text-sm">Gunakan tombol 'Buat Paket Baru' untuk memilih standar dan mulai menyusun instrumen audit.</p>
                </div>
            <?php else: ?>
                <div class="inst-table-wrap">
                    <table class="inst-table" id="pkg-table">
                        <thead>
                            <tr>
                                <th>Paket Instrumen</th>
                                <th>Standar Induk</th>
                                <th>Versi SPMI</th>
                                <th>Status</th>
                                <th>Pertanyaan</th>
                                <th class="tw-text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($packages as $pkg):
                                $q_count = isset($question_counts[$pkg->id]) ? $question_counts[$pkg->id] : 0;
                                $status = strtolower($pkg->version_status);
                                $is_mutable = in_array($status, ['draft', 'review'], TRUE);
                                $can_delete = $is_mutable && ($q_count === 0);
                            ?>
                                <tr data-pkg-row data-version="<?php echo html_escape($pkg->version_code); ?>" data-status="<?php echo html_escape($status); ?>">
                                    <td data-label="Paket Instrumen">
                                        <span class="tw-font-mono tw-font-bold tw-text-xs tw-text-slate-600 tw-block">
                                            <?php echo html_escape($pkg->package_code); ?>
                                        </span>
                                        <strong class="tw-text-slate-900 tw-text-sm"><?php echo html_escape($pkg->title); ?></strong>
                                    </td>
                                    <td data-label="Standar Induk">
                                        <span class="tw-text-xs tw-font-medium tw-text-slate-700">
                                            <?php echo html_escape($pkg->standard_code . ' — ' . $pkg->standard_title); ?>
                                        </span>
                                    </td>
                                    <td data-label="Versi SPMI">
                                        <span class="tw-text-xs tw-font-mono tw-text-slate-600">
                                            <?php echo html_escape($pkg->version_code); ?>
                                        </span>
                                    </td>
                                    <td data-label="Status">
                                        <span class="inst-badge inst-badge-<?php echo $status; ?>">
                                            <?php echo html_escape(ucfirst($pkg->version_status)); ?>
                                        </span>
                                    </td>
                                    <td data-label="Pertanyaan">
                                        <span class="tw-text-xs tw-font-bold tw-text-slate-800">
                                            <?php echo $q_count; ?> butir
                                        </span>
                                    </td>
                                    <td data-label="Aksi" class="tw-text-right">
                                        <div class="tw-inline-flex tw-items-center tw-gap-2">
                                            <a class="inst-button inst-button-secondary tw-text-xs" href="<?php echo site_url('lpmpi/spmi-instruments/package/detail/' . (int) $pkg->id); ?>">
                                                <?php echo $icon('external-link'); ?> Detail
                                            </a>

                                            <?php if ($is_mutable): ?>
                                            <div class="inst-dropdown" data-inst-dropdown>
                                                <button type="button" class="inst-button inst-button-secondary tw-text-xs" data-dropdown-trigger aria-haspopup="true" aria-expanded="false">
                                                    Aksi <?php echo $icon('chevron-down'); ?>
                                                </button>
                                                <div class="inst-dropdown-menu tw-hidden" data-dropdown-menu>
                                                        <a class="inst-dropdown-item" href="<?php echo site_url('lpmpi/spmi-instruments/package/edit/' . (int) $pkg->id); ?>">
                                                            <?php echo $icon('edit'); ?> Edit Paket
                                                        </a>
                                                        <a class="inst-dropdown-item" href="<?php echo site_url('lpmpi/spmi-instruments/question/create/' . (int) $pkg->id); ?>">
                                                            <?php echo $icon('plus'); ?> Tambah Pertanyaan
                                                        </a>
                                                        <?php if ($can_delete): ?>
                                                            <div class="inst-dropdown-divider"></div>
                                                            <?php echo form_open('lpmpi/spmi-instruments/package/delete/' . (int) $pkg->id, ['class' => 'tw-m-0', 'onsubmit' => "return confirm('Hapus paket instrumen ini?');"]); ?>
                                                                <button type="submit" class="inst-dropdown-item danger">
                                                                    <?php echo $icon('trash'); ?> Hapus Paket
                                                                </button>
                                                            <?php echo form_close(); ?>
                                                        <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php else: ?>
                                                <span class="inst-readonly-badge" aria-label="Paket hanya-baca">
                                                    <?php echo $icon('lock'); ?> Hanya baca
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modal Selector Pembuatan Paket Baru -->
    <div id="modal-create-package" class="inst-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-create-title">
        <div class="inst-modal-card">
            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                <div>
                    <h4 id="modal-create-title" class="tw-text-base tw-font-bold tw-m-0 tw-text-slate-800">
                        Buat Paket Instrumen Baru
                    </h4>
                    <p class="inst-muted tw-text-xs tw-mt-0.5">
                        Pilih versi draft/review dan standar tujuan untuk membuat paket instrumen.
                    </p>
                </div>
                <button type="button" class="tw-text-slate-400 hover:tw-text-slate-600 tw-p-1 tw-rounded" data-modal-close aria-label="Tutup modal">
                    <?php echo $icon('x'); ?>
                </button>
            </div>

            <?php if (empty($version_options)): ?>
                <div class="tw-bg-amber-50 tw-border tw-border-amber-200 tw-text-amber-900 tw-p-4 tw-rounded-lg tw-text-xs tw-mb-4">
                    <strong>Tidak ada versi yang dapat diubah.</strong><br>
                    Paket instrumen hanya dapat dibuat pada versi standar berstatus <em>draft</em> atau <em>review</em>.
                </div>
                <div class="tw-flex tw-justify-end">
                    <button type="button" class="inst-button inst-button-secondary tw-text-xs" data-modal-close>Tutup</button>
                </div>
            <?php else: ?>
                <form id="form-select-standard" onsubmit="return handleStandardSelect(event);">
                    <div class="tw-space-y-4 tw-mb-6">
                        <div>
                            <label for="select-version" class="inst-label">1. Pilih Versi SPMI (Draft / Review)</label>
                            <select id="select-version" class="inst-control tw-text-sm" required onchange="handleVersionChange(this.value)">
                                <option value="">Pilih versi...</option>
                                <?php foreach ($version_options as $vid => $vlabel): ?>
                                    <option value="<?php echo (int) $vid; ?>"><?php echo html_escape($vlabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="select-standard" class="inst-label">2. Pilih Standar Induk</label>
                            <select id="select-standard" class="inst-control tw-text-sm" required disabled>
                                <option value="">Pilih versi terlebih dahulu...</option>
                            </select>
                        </div>
                    </div>

                    <div class="tw-flex tw-justify-end tw-gap-2">
                        <button type="button" class="inst-button inst-button-secondary tw-text-xs" data-modal-close>Batal</button>
                        <button type="submit" id="btn-next-create" class="inst-button inst-button-primary tw-text-xs" disabled>
                            Lanjutkan ke Form Pembuatan
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
var standardsByVersion = <?php echo json_encode($mutable_standards_by_version); ?>;

function handleVersionChange(versionId) {
    var stdSelect = document.getElementById('select-standard');
    var nextBtn = document.getElementById('btn-next-create');
    stdSelect.innerHTML = '<option value="">Pilih standar...</option>';

    if (!versionId || !standardsByVersion[versionId] || standardsByVersion[versionId].length === 0) {
        stdSelect.disabled = true;
        nextBtn.disabled = true;
        if (versionId && (!standardsByVersion[versionId] || standardsByVersion[versionId].length === 0)) {
            stdSelect.innerHTML = '<option value="">Tidak ada standar pada versi ini</option>';
        }
        return;
    }

    standardsByVersion[versionId].forEach(function (std) {
        var opt = document.createElement('option');
        opt.value = std.id;
        opt.textContent = std.code + ' — ' + std.title;
        stdSelect.appendChild(opt);
    });

    stdSelect.disabled = false;
    stdSelect.onchange = function () {
        nextBtn.disabled = !stdSelect.value;
    };
}

function handleStandardSelect(e) {
    e.preventDefault();
    var stdId = document.getElementById('select-standard').value;
    if (stdId) {
        window.location.href = '<?php echo site_url("lpmpi/spmi-instruments/package/create"); ?>/' + encodeURIComponent(stdId);
    }
    return false;
}

document.addEventListener('DOMContentLoaded', function () {
    // 1. Dropdown Aksi
    document.querySelectorAll('[data-inst-dropdown]').forEach(function (container) {
        var trigger = container.querySelector('[data-dropdown-trigger]');
        var menu = container.querySelector('[data-dropdown-menu]');
        if (!trigger || !menu) return;

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = !menu.classList.contains('tw-hidden');
            document.querySelectorAll('[data-dropdown-menu]').forEach(function (m) {
                m.classList.add('tw-hidden');
            });
            if (!isOpen) {
                menu.classList.remove('tw-hidden');
                trigger.setAttribute('aria-expanded', 'true');
            } else {
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    });

    document.addEventListener('click', function () {
        document.querySelectorAll('[data-dropdown-menu]').forEach(function (menu) {
            menu.classList.add('tw-hidden');
        });
        document.querySelectorAll('[data-dropdown-trigger]').forEach(function (trig) {
            trig.setAttribute('aria-expanded', 'false');
        });
    });

    // 2. Modal Create Package
    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function openModal(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }
    }

    document.querySelectorAll('[data-modal-trigger]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var target = btn.getAttribute('data-modal-trigger');
            openModal(target);
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            closeModal(btn.closest('.inst-modal-overlay'));
        });
    });

    document.querySelectorAll('.inst-modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                closeModal(overlay);
            }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.key === 'Esc') {
            document.querySelectorAll('.inst-modal-overlay.is-open').forEach(function (m) {
                closeModal(m);
            });
        }
    });

    // 3. Client-side Filter & Search
    var searchInput = document.querySelector('[data-filter-search]');
    var versionFilter = document.querySelector('[data-filter-version]');
    var statusFilter = document.querySelector('[data-filter-status]');
    var rows = document.querySelectorAll('[data-pkg-row]');

    function filterRows() {
        var query = (searchInput ? searchInput.value : '').toLowerCase().trim();
        var selectedVersion = (versionFilter ? versionFilter.value : '').toLowerCase().trim();
        var selectedStatus = (statusFilter ? statusFilter.value : '').toLowerCase().trim();

        rows.forEach(function (row) {
            var text = row.textContent.toLowerCase();
            var rVer = (row.getAttribute('data-version') || '').toLowerCase();
            var rStat = (row.getAttribute('data-status') || '').toLowerCase();

            var matchSearch = !query || text.indexOf(query) !== -1;
            var matchVersion = !selectedVersion || rVer === selectedVersion;
            var matchStatus = !selectedStatus || rStat === selectedStatus;

            if (matchSearch && matchVersion && matchStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterRows);
    if (versionFilter) versionFilter.addEventListener('change', filterRows);
    if (statusFilter) statusFilter.addEventListener('change', filterRows);
});
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
