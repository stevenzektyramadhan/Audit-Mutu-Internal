<?php defined('BASEPATH') OR exit('No direct script access allowed'); include APPPATH . 'views/layouts/header.php'; include APPPATH . 'views/layouts/sidebar.php'; ?>
<style>
    .spmi-master-actions {
        display: grid;
        gap: var(--ami-space-sm);
        min-width: 250px;
    }

    .spmi-master-actions__row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: var(--ami-space-sm);
    }

    .spmi-master-actions .spmi-master-actions__button,
    .spmi-master-actions .spmi-master-actions__file-label {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 32px;
        border-radius: var(--ami-radius-sm);
        font-size: 12px;
        line-height: 1.2;
        white-space: nowrap;
    }

    .spmi-master-actions__button {
        border: 1px solid var(--ami-blue);
        padding: 6px 10px;
    }

    .spmi-master-actions__button.btn-outline-ami {
        color: var(--ami-blue);
        background: transparent;
    }

    .spmi-master-actions__button.btn-outline-ami:hover,
    .spmi-master-actions__button.btn-outline-ami:focus {
        color: var(--ami-blue);
        background: var(--ami-link-soft);
    }

    .spmi-master-actions__file-input {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    .spmi-master-actions__file-input:focus-visible + .spmi-master-actions__file-label {
        outline: 2px solid var(--ami-blue);
        outline-offset: 2px;
    }

    .spmi-master-actions__file-label {
        cursor: pointer;
        border: 1px solid var(--ami-border);
        padding: 6px 10px;
        color: var(--ami-text);
        background: var(--ami-panel);
    }

    .spmi-master-actions__file-label:hover,
    .spmi-master-actions__file-label:focus {
        border-color: var(--ami-blue);
        color: var(--ami-blue);
    }

    .spmi-master-actions__submit.btn-primary {
        border: 1px solid var(--ami-blue);
        padding: 6px 10px;
    }

    .spmi-master-actions__submit:disabled {
        cursor: not-allowed;
        opacity: 0.55;
    }

    .spmi-master-actions__filename {
        max-width: 150px;
        overflow: hidden;
        color: var(--ami-muted);
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .spmi-master-actions__icon {
        width: 14px;
        height: 14px;
        flex: 0 0 14px;
    }

    @media (max-width: 575.98px) {
        .spmi-master-actions {
            min-width: 0;
        }

        .spmi-master-actions__row > * {
            flex: 1 1 auto;
        }

        .spmi-master-actions__filename {
            max-width: 100%;
            flex-basis: 100%;
        }
    }
</style>
<div class="ami-panel"><div class="ami-panel-body">
    <h2 class="ami-section-title">Import/Export Master SPMI</h2>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo html_escape($error); ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo html_escape($success); ?></div><?php endif; ?>
    <p>Import bersifat additive/no-delete: baris yang tidak ada dalam workbook tetap tersimpan. Template dan import hanya tersedia untuk versi draft/review.</p>
    <?php if (empty($versions)): ?><div class="ami-empty"><div class="ami-empty-title">Belum ada versi</div><div>Buat versi M3 sebelum memakai Master SPMI.</div></div>
    <?php else: ?><div class="table-responsive"><table class="table ami-table"><thead><tr><th>Versi</th><th>Status</th><th>Aksi</th></tr></thead><tbody><?php foreach ($versions as $version): ?><tr><td><?php echo html_escape($version->version_code . ' - ' . $version->title); ?></td><td><?php echo html_escape($version->status); ?></td><td><?php if (in_array($version->status, ['draft', 'review'], TRUE)): ?>
        <div class="spmi-master-actions">
            <div class="spmi-master-actions__row">
                <a class="btn-ami btn-sm btn-outline-ami spmi-master-actions__button" href="<?php echo site_url('lpmpi/spmi-master/template/' . (int) $version->id); ?>">
                    <svg class="spmi-master-actions__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h8l4 4v14H6z"></path><path d="M14 3v5h4M9 13h6M9 17h4"></path></svg>Template
                </a>
                <a class="btn-ami btn-sm btn-outline-ami spmi-master-actions__button" href="<?php echo site_url('lpmpi/spmi-master/export/' . (int) $version->id); ?>">
                    <svg class="spmi-master-actions__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"></path></svg>Export XLSX
                </a>
            </div>
            <?php echo form_open_multipart('lpmpi/spmi-master/preview/' . (int) $version->id, ['class' => 'spmi-master-actions__form']); ?>
                <div class="spmi-master-actions__row">
                    <svg class="spmi-master-actions__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h8l4 4v14H6z"></path><path d="M14 3v5h4"></path></svg>
                    <input class="spmi-master-actions__file-input" id="master-file-<?php echo (int) $version->id; ?>" type="file" name="master_file" accept=".xlsx" required aria-describedby="master-file-name-<?php echo (int) $version->id; ?>">
                    <label class="spmi-master-actions__file-label" for="master-file-<?php echo (int) $version->id; ?>">Pilih File</label>
                    <span class="spmi-master-actions__filename" id="master-file-name-<?php echo (int) $version->id; ?>" aria-live="polite">Belum ada file dipilih</span>
                    <span class="spmi-master-actions__filename">Maks <?php echo html_escape((string) $upload_limit_mib); ?> MiB</span>
                    <button class="btn-ami btn-sm btn-primary spmi-master-actions__submit" type="submit" disabled>Unggah &amp; Preview</button>
                </div>
            <?php echo form_close(); ?>
        </div>
    <?php else: ?><div class="spmi-master-actions"><div class="spmi-master-actions__row"><span>Hanya-baca</span><a class="btn-ami btn-sm btn-outline-ami spmi-master-actions__button" href="<?php echo site_url('lpmpi/spmi-master/export/' . (int) $version->id); ?>"><svg class="spmi-master-actions__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"></path></svg>Export XLSX</a></div></div><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</div></div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.spmi-master-actions__form').forEach(function (form) {
            var input = form.querySelector('.spmi-master-actions__file-input');
            var label = form.querySelector('.spmi-master-actions__file-label');
            var filename = form.querySelector('.spmi-master-actions__filename');
            var submit = form.querySelector('.spmi-master-actions__submit');

            input.addEventListener('change', function () {
                var selected = input.files && input.files.length > 0;
                label.textContent = selected ? 'Ganti File' : 'Pilih File';
                filename.textContent = selected ? input.files[0].name : 'Belum ada file dipilih';
                submit.disabled = !selected;
                filename.setAttribute('aria-label', selected ? 'File dipilih: ' + input.files[0].name : 'Belum ada file dipilih');
            });
        });
    });
</script>
<?php include APPPATH . 'views/layouts/footer.php'; ?>
