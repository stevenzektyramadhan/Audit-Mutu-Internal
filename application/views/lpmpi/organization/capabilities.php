<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$icon = static function ($name) {
    $paths = [
        'arrow-left' => '<path d="m15 18-6-6 6-6"/>',
        'shield' => '<path d="M12 3 4 7v5c0 4.4 3.4 7.7 8 9 4.6-1.3 8-4.6 8-9V7l-8-4Z"/><path d="m9 12 2 2 4-4"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
    ];
    return '<svg class="org-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['shield']) . '</svg>';
};
?>

<div id="organization-root" class="tw-mx-auto tw-max-w-screen-2xl">
    <section class="org-surface form-shell" style="max-width: 56rem;">
        <div class="org-surface-body">
            <div class="form-header">
                <a href="<?php echo site_url('lpmpi/organization'); ?>" class="org-back-link">
                    <?php echo $icon('arrow-left'); ?> Kembali ke Struktur Organisasi
                </a>
                <p class="org-eyebrow">Pengaturan Wewenang</p>
                <h2 class="tw-text-2xl tw-font-bold tw-m-0 tw-text-slate-800">Hak Akses Organisasi</h2>
                <p class="form-subtitle">Matriks izin kelola bagan unit kerja, penugasan pengguna, dan pengaturan wewenang.</p>
            </div>

            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6">
                <?php foreach (Organization_service::ROLES as $role):
                    $is_super = $role === 'super_admin';
                ?>
                    <div class="tw-border tw-border-slate-200 tw-rounded-lg tw-p-5 tw-bg-slate-50">
                        <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                            <h3 class="tw-text-base tw-font-bold tw-m-0 tw-text-slate-800">
                                <?php echo $role === 'super_admin' ? 'Super Admin' : 'Admin LPMPI'; ?>
                            </h3>
                            <span class="org-badge org-badge-type tw-font-mono"><?php echo html_escape($role); ?></span>
                        </div>

                        <?php if ($can_manage_capabilities): ?>
                            <?php foreach (Organization_service::ROLES as $scoped_role): if ($scoped_role !== $role) continue; ?>
                            <?php echo form_open('lpmpi/organization/capabilities/update'); ?>
                                <input type="hidden" name="role" value="<?php echo html_escape($role); ?>">
                                <div class="tw-space-y-3 tw-mb-4">
                                    <?php foreach ($capabilities as $cap):
                                        $checked = in_array($cap->code, $role_capabilities[$role], TRUE);
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
                                    Simpan Hak Akses <?php echo $role === 'super_admin' ? 'Super Admin' : 'Admin LPMPI'; ?>
                                </button>
                            <?php echo form_close(); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach (Organization_service::ROLES as $scoped_role): if ($scoped_role !== $role) continue; ?>
                            <div class="tw-space-y-2">
                                <?php foreach ($capabilities as $cap):
                                    $has_cap = in_array($cap->code, $role_capabilities[$role], TRUE);
                                ?>
                                    <div class="tw-flex tw-items-center tw-justify-between tw-p-2.5 tw-rounded tw-bg-white tw-border tw-border-slate-200">
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
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
