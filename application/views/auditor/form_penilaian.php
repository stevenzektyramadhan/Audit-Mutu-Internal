<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$jawaban = isset($jawaban) ? $jawaban : [];
$read_only = !empty($tugas->is_nilai_readonly);
$skor_options = skor_audit_options();
$jenis_labels = ['ob' => 'OB', 'kts' => 'KTS'];
$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>

<?php if (validation_errors()): ?>
    <div class="ami-alert ami-alert-danger"><?php echo validation_errors(); ?></div>
<?php endif; ?>

<div class="ami-panel ami-mb-3">
    <div class="ami-panel-body">
        <div class="ami-grid-4">
            <div>
                <div class="ami-stat-label">Periode</div>
                <div class="ami-font-bold"><?php echo html_escape($tugas->nama_periode ?: '-'); ?></div>
            </div>
            <div>
                <div class="ami-stat-label">Auditee</div>
                <div class="ami-font-bold"><?php echo html_escape($tugas->auditee_nama ?: '-'); ?></div>
                <?php if (!empty($tugas->auditee_unit)): ?>
                    <div class="ami-muted" style="font-size:12px;">
                        <?php echo html_escape($tugas->auditee_unit); ?>
                        <?php if (!empty($tugas->auditee_jenis_unit)): ?>
                            &middot; <?php echo html_escape(strtoupper($tugas->auditee_jenis_unit)); ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <div class="ami-stat-label">Standar</div>
                <div class="ami-font-bold"><?php echo html_escape($tugas->nama_standar ?: '-'); ?></div>
            </div>
            <div>
                <div class="ami-stat-label">Status penilaian</div>
                <span class="ami-status <?php echo html_escape($tugas->penilaian_status_class); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                    <?php echo html_escape($tugas->penilaian_status_label); ?>
                </span>
            </div>
        </div>

        <div class="ami-actions ami-mt-3">
            <a href="<?php echo site_url('auditor/penilaian'); ?>" class="btn-outline-ami btn-ami">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6M9 12h12"/></svg> Kembali
            </a>
        </div>
    </div>
</div>

<?php if (!empty($tugas->submitted_at)): ?>
                    <div class="ami-alert ami-alert-info" style="background:#e6f1fb;color:#185fa5;border:0;border-radius:8px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="M22 2 11 13"/></svg>
        Jawaban auditee submitted pada <?php echo html_escape(format_tanggal_indo($tugas->submitted_at)); ?>.
    </div>
<?php endif; ?>

<?php if ($read_only): ?>
                    <div class="ami-alert ami-alert-success" style="background:#eaf3de;color:#315d13;border:0;border-radius:8px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
        Penilaian sudah disubmit dan form terkunci.
    </div>
<?php endif; ?>

<?php if (!empty($jawaban)): ?>
    <?php echo form_open('auditor/penilaian/save/' . (int) $tugas->id, ['id' => 'penilaian-form']); ?>
        <?php foreach ($jawaban as $index => $item): ?>
            <?php
            $skor = $item->skor !== NULL ? (int) $item->skor : 0;
            $has_score = $skor > 0;
            $jenis_temuan = strtolower((string) $item->jenis_temuan);
            $jenis_label = isset($jenis_labels[$jenis_temuan]) ? $jenis_labels[$jenis_temuan] : '-';
            $dokumen_bukti = (string) $item->dokumen_bukti;
            $download_url = $dokumen_bukti !== '' ? site_url('auditor/penilaian/download_bukti/' . (int) $item->id) : '';
            ?>
            <input type="hidden" name="skor[<?php echo (int) $item->id; ?>]" value="<?php echo $has_score ? (int) $skor : ''; ?>" data-hidden-jawaban="<?php echo (int) $item->id; ?>" data-hidden-field="skor">
            <input type="hidden" name="temuan[<?php echo (int) $item->id; ?>]" value="<?php echo html_escape($item->temuan); ?>" data-hidden-jawaban="<?php echo (int) $item->id; ?>" data-hidden-field="temuan">
            <input type="hidden" name="jenis_temuan[<?php echo (int) $item->id; ?>]" value="<?php echo html_escape($jenis_temuan); ?>" data-hidden-jawaban="<?php echo (int) $item->id; ?>" data-hidden-field="jenis_temuan">
            <input type="hidden" name="saran_perbaikan[<?php echo (int) $item->id; ?>]" value="<?php echo html_escape($item->saran_perbaikan); ?>" data-hidden-jawaban="<?php echo (int) $item->id; ?>" data-hidden-field="saran_perbaikan">
            <input type="hidden" name="rencana_perbaikan[<?php echo (int) $item->id; ?>]" value="<?php echo html_escape($item->rencana_perbaikan); ?>" data-hidden-jawaban="<?php echo (int) $item->id; ?>" data-hidden-field="rencana_perbaikan">
            <input type="hidden" name="tgl_bukti[<?php echo (int) $item->id; ?>]" value="<?php echo html_escape($item->tgl_bukti); ?>" data-hidden-jawaban="<?php echo (int) $item->id; ?>" data-hidden-field="tgl_bukti">

            <div class="ami-panel ami-mb-3" data-penilaian-row="<?php echo (int) $item->id; ?>">
                <div class="ami-panel-body">
                    <div class="ami-flex ami-align-start ami-justify-between ami-flex-wrap ami-mb-3" style="gap:10px;">
                        <div class="ami-flex ami-align-start" style="min-width:0;">
                            <span class="ami-nav-badge ami-mr-2" style="margin-left:0;"><?php echo (int) $index + 1; ?></span>
                            <div class="ami-font-bold"><?php echo html_escape($item->isi_pertanyaan); ?></div>
                        </div>
                        <span class="ami-status <?php echo $has_score ?'status-dinilai' : 'status-belum_diisi'; ?>" data-status-badge="<?php echo (int) $item->id; ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" data-status-icon="<?php echo (int) $item->id; ?>"><circle cx="12" cy="12" r="9"/><path d="<?php echo $has_score ? 'm8 12 3 3 5-6' : 'M12 8v5m0 3h.01'; ?>"/></svg>
                            <span data-status-label="<?php echo (int) $item->id; ?>"><?php echo $has_score ? 'Sudah dinilai' : 'Belum dinilai'; ?></span>
                        </span>
                    </div>

                    <div class="ami-grid-2">
                        <div>
                            <div class="ami-stat-label ami-mb-1">Jawaban auditee</div>
                            <div class="ami-mb-3"><?php echo nl2br(html_escape($item->jawaban ?: '-')); ?></div>

                            <div class="ami-stat-label ami-mb-1">Link bukti auditee</div>
                            <?php if (!empty($item->link_bukti)): ?>
                                <a href="<?php echo html_escape($item->link_bukti); ?>" target="_blank" rel="noopener noreferrer">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 3h7v7M10 14 21 3M21 14v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h6"/></svg>Buka bukti
                                </a>
                            <?php else: ?>
                                <span class="ami-muted">-</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="p-3" style="border-left:3px solid var(--ami-border);">
                                <div class="ami-grid-2">
                                    <div class="ami-mb-3">
                                        <div class="ami-stat-label ami-mb-1">Skor</div>
                                        <div class="ami-font-bold" data-score-label="<?php echo (int) $item->id; ?>">
                                            <?php echo $has_score ? (int) $skor . ' - ' . html_escape($skor_options[$skor] ?? '') : 'Belum ada'; ?>
                                        </div>
                                    </div>
                                    <div class="ami-mb-3">
                                        <div class="ami-stat-label ami-mb-1">Jenis temuan</div>
                                        <div class="ami-font-bold" data-jenis-label="<?php echo (int) $item->id; ?>"><?php echo html_escape($jenis_label); ?></div>
                                    </div>
                                </div>

                                <div class="ami-stat-label ami-mb-1">Temuan</div>
                                <div class="ami-mb-3" data-temuan-summary="<?php echo (int) $item->id; ?>"><?php echo nl2br(html_escape($item->temuan ?: '-')); ?></div>

                                <div class="ami-stat-label ami-mb-1">Saran perbaikan</div>
                                <div class="ami-mb-3" data-saran-summary="<?php echo (int) $item->id; ?>"><?php echo nl2br(html_escape($item->saran_perbaikan ?: '-')); ?></div>

                                <div class="ami-stat-label ami-mb-1">Rencana perbaikan</div>
                                <div class="ami-mb-3" data-rencana-summary="<?php echo (int) $item->id; ?>"><?php echo nl2br(html_escape($item->rencana_perbaikan ?: '-')); ?></div>

                                <div class="ami-grid-2 ami-align-end">
                                    <div class="ami-mb-3">
                                        <div class="ami-stat-label ami-mb-1">Tanggal bukti</div>
                                        <div data-tgl-label="<?php echo (int) $item->id; ?>">
                                            <?php echo !empty($item->tgl_bukti) ? html_escape(format_tanggal_indo($item->tgl_bukti)) : '-'; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="ami-stat-label ami-mb-1">Dokumen bukti</div>
                                        <a href="<?php echo html_escape($download_url); ?>" class="<?php echo $download_url ==='' ? 'ami-hidden' : ''; ?>" data-dokumen-link="<?php echo (int) $item->id; ?>">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5M5 21h14"/></svg><span data-dokumen-label="<?php echo (int) $item->id; ?>"><?php echo html_escape($dokumen_bukti); ?></span>
                                        </a>
                                         <span class="<?php echo $download_url !=='' ? 'ami-hidden' : 'ami-muted'; ?>" data-dokumen-empty="<?php echo (int) $item->id; ?>">-</span>
                                    </div>
                                </div>

                                <div class="ami-actions ami-mt-3">
                                    <button type="button"
                                            class="ami-action-btn"
                                             data-penilaian-button
                                            data-jawaban-id="<?php echo (int) $item->id; ?>"
                                            data-nomor="<?php echo (int) $index + 1; ?>"
                                            data-skor="<?php echo $has_score ? (int) $skor : ''; ?>"
                                            data-temuan="<?php echo html_escape($item->temuan); ?>"
                                            data-jenis-temuan="<?php echo html_escape($jenis_temuan); ?>"
                                            data-saran-perbaikan="<?php echo html_escape($item->saran_perbaikan); ?>"
                                            data-rencana-perbaikan="<?php echo html_escape($item->rencana_perbaikan); ?>"
                                            data-tgl-bukti="<?php echo html_escape($item->tgl_bukti); ?>"
                                            data-dokumen-bukti="<?php echo html_escape($dokumen_bukti); ?>"
                                            data-download-url="<?php echo html_escape($download_url); ?>">
                                         <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="<?php echo $read_only ? 'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z' : 'm12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z'; ?>"/><?php if ($read_only): ?><circle cx="12" cy="12" r="3"/><?php endif; ?></svg>
                                        <?php echo $read_only ? 'Detail' : 'Nilai'; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="ami-actions ami-justify-end">
            <a href="<?php echo site_url('auditor/penilaian'); ?>" class="btn-outline-ami btn-ami">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6M9 12h12"/></svg> Kembali
            </a>
            <?php if (!$read_only): ?>
                <button type="submit" class="btn-outline-ami btn-ami" data-loading-text="Menyimpan...">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 3h12l2 2v16H5V3Z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></svg> Save draft
                </button>
                <button type="submit"
                        class="tw-button-primary btn-ami"
                        formaction="<?php echo site_url('auditor/penilaian/submit/' . (int) $tugas->id); ?>"
                        data-confirm="Submit penilaian? Setelah submit, form akan terkunci."
                        data-loading-text="Submit...">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4 20-7Z"/><path d="M22 2 11 13"/></svg> Submit
                </button>
                <button type="submit"
                        class="btn-outline-ami btn-ami"
                        formaction="<?php echo site_url('auditor/penilaian/revisi/' . (int) $tugas->id); ?>"
                        data-confirm="Kembalikan tugas ini ke auditee untuk revisi?"
                        data-loading-text="Mengembalikan...">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v6h6"/></svg> Revisi
                </button>
            <?php endif; ?>
        </div>
    <?php echo form_close(); ?>
<?php else: ?>
    <div class="ami-panel">
        <div class="ami-empty">
            <div class="ami-empty-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg></div>
            <div class="ami-empty-title">Tidak ada pertanyaan</div>
            <div>Pertanyaan audit untuk tugas ini belum tersedia.</div>
        </div>
    </div>
<?php endif; ?>

<style>
    #penilaian-modal {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 1050;
        display: none;
        overflow-x: hidden;
        overflow-y: auto;
        outline: 0;
        background: rgba(0, 0, 0, .5);
    }

    #penilaian-modal.is-open {
        display: block;
    }

    #penilaian-modal-dialog {
        height: calc(100vh - 3.5rem);
        max-height: calc(100vh - 3.5rem);
        max-width: 800px;
        margin: 1.75rem auto;
        display: flex;
        align-items: stretch;
    }

    #penilaian-modal-content {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        height: 100%;
        max-height: 100%;
        min-height: 0;
        overflow: hidden;
    }

    #penilaian-modal-form {
        width: 100%;
        height: 100%;
        max-height: 100%;
        min-height: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    #penilaian-modal-header,
    #penilaian-modal-footer {
        flex: 0 0 auto;
        padding: 1rem;
    }

    #penilaian-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    #penilaian-modal-title {
        margin-bottom: 0;
        line-height: 1.5;
    }

    #penilaian-modal-footer {
        display: flex;
        justify-content: flex-end;
        padding: .75rem;
    }

    #penilaian-modal-close {
        float: right;
        padding: 0;
        border: 0;
        background: transparent;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        color: var(--ami-text);
        text-shadow: 0 1px 0 #fff;
        opacity: .5;
    }

    #penilaian-modal-body {
        position: relative;
        flex: 1 1 auto;
        padding: 1rem;
        min-height: 0;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    @media (max-width: 575.98px) {
        #penilaian-modal-dialog {
            max-width: none;
            margin: .5rem;
            height: calc(100vh - 1rem);
            max-height: calc(100vh - 1rem);
        }
    }
</style>

<div id="penilaian-modal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="penilaian-modal-title" aria-hidden="true" hidden>
    <div id="penilaian-modal-dialog" role="document">
        <div id="penilaian-modal-content" style="background:var(--ami-panel);color:var(--ami-text);border:1px solid var(--ami-border);border-radius:8px;">
            <form id="penilaian-modal-form" method="post" enctype="multipart/form-data">
                <input type="hidden" name="<?php echo html_escape($csrf_name); ?>" value="<?php echo html_escape($csrf_hash); ?>">
                <div id="penilaian-modal-header" style="border-bottom:1px solid var(--ami-border);">
                    <h5 id="penilaian-modal-title">Penilaian pertanyaan</h5>
                    <button type="button" id="penilaian-modal-close" data-penilaian-modal-close aria-label="Tutup" style="color:var(--ami-text);">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="penilaian-modal-body">
            <div class="ami-alert ami-hidden" data-modal-message role="alert"></div>

                <div class="ami-form-group">
                        <label>Skor</label>
                        <div class="ami-flex ami-flex-wrap" style="gap:8px;">
                            <?php foreach ([4, 3, 2, 1] as $score): ?>
                                <label class="ami-action-btn ami-mb-0" style="min-height:38px;">
                                    <input type="radio" name="skor" value="<?php echo (int) $score; ?>" class="ami-mr-2" <?php echo $read_only ? 'disabled' : ''; ?>>
                                    <?php echo (int) $score; ?> - <?php echo html_escape($skor_options[$score]); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                <div class="ami-form-group">
                        <label for="modal-temuan">Temuan</label>
                <textarea id="modal-temuan" name="temuan" rows="4" class="ami-form-control" <?php echo $read_only ? 'readonly' : ''; ?>></textarea>
                    </div>

                <div class="ami-form-group">
                        <label>Jenis Temuan</label>
                        <div class="ami-flex ami-flex-wrap" style="gap:12px;">
                            <label class="ami-form-check ami-mb-0">
                                <input class="ami-form-check-input" type="radio" name="jenis_temuan" value="ob" <?php echo $read_only ? 'disabled' : ''; ?>>
                                <span class="ami-form-check-label">OB</span>
                            </label>
                            <label class="ami-form-check ami-mb-0">
                                <input class="ami-form-check-input" type="radio" name="jenis_temuan" value="kts" <?php echo $read_only ? 'disabled' : ''; ?>>
                                <span class="ami-form-check-label">KTS</span>
                            </label>
                        </div>
                    </div>

                <div class="ami-form-group">
                        <label for="modal-saran">Saran Perbaikan</label>
                <textarea id="modal-saran" name="saran_perbaikan" rows="3" class="ami-form-control" <?php echo $read_only ? 'readonly' : ''; ?>></textarea>
                    </div>

                    <div class="ami-form-group">
                        <label for="modal-rencana">Rencana Perbaikan</label>
                <textarea id="modal-rencana" name="rencana_perbaikan" rows="3" class="ami-form-control" <?php echo $read_only ? 'readonly' : ''; ?>></textarea>
                    </div>

                <div class="ami-grid-3-9">
                    <div>
                        <div class="ami-form-group">
                                <label for="modal-dokumen">Dokumen Bukti</label>
                    <input id="modal-dokumen" type="file" name="dokumen_bukti" class="ami-form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" <?php echo $read_only ? 'disabled' : ''; ?>>
                                <div class="ami-mt-2" data-modal-dokumen-wrap>
                                    <a href="#" target="_blank" rel="noopener noreferrer" data-modal-dokumen-link>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5M5 21h14"/></svg><span data-modal-dokumen-label></span>
                                    </a>
                    <span class="ami-muted" data-modal-dokumen-empty>-</span>
                                </div>
                            </div>
                        </div>
                    <div>
                        <div class="ami-form-group">
                                <label for="modal-tgl-bukti">Tanggal Bukti</label>
                    <input id="modal-tgl-bukti" type="date" name="tgl_bukti" class="ami-form-control" <?php echo $read_only ? 'readonly' : ''; ?>>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="penilaian-modal-footer" style="border-top:1px solid var(--ami-border);">
                    <button type="button" class="btn-outline-ami btn-ami" data-penilaian-modal-close>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg> Tutup
                    </button>
                    <?php if (!$read_only): ?>
                        <button type="submit" class="tw-button-primary btn-ami" data-modal-submit>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 3h12l2 2v16H5V3Z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></svg> Simpan
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var readOnly = <?php echo $read_only ? 'true' : 'false'; ?>;
    var saveBaseUrl = <?php echo json_encode(site_url('auditor/penilaian/save_item/')); ?>;
    var modal = document.getElementById('penilaian-modal');
    var modalForm = document.getElementById('penilaian-modal-form');
    var modalMessage = modal ? modal.querySelector('[data-modal-message]') : null;
    var activeButton = null;
    var previousBodyOverflow = '';
    var focusableSelector = 'a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])';

    function hideModal() {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        modal.hidden = true;
        document.body.style.overflow = previousBodyOverflow;
        if (activeButton && typeof activeButton.focus === 'function') activeButton.focus();
    }

    function showModal() {
        if (!modal) return;
        modal.hidden = false;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        previousBodyOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        var focusable = modal.querySelector(focusableSelector);
        if (focusable) focusable.focus();
    }

    function setStatusIcon(icon, token) {
        if (!icon) return;
        var name = String(token || '');
        if (name.indexOf('check') !== -1) {
            icon.innerHTML = '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>';
        } else if (name.indexOf('save') !== -1) {
            icon.innerHTML = '<path d="M5 3h12l2 2v16H5V3Z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/>';
        } else if (name.indexOf('star') !== -1) {
            icon.innerHTML = '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.6l6.2-.9L12 3Z"/>';
        } else {
            icon.innerHTML = '<circle cx="12" cy="12" r="9"/><path d="M12 8v5m0 3h.01"/>';
        }
    }

    function setText(selector, id, value) {
        var element = document.querySelector(selector + '="' + id + '"]');
        if (element) element.textContent = value && value !== '' ? value : '-';
    }

    function setHtmlText(selector, id, value) {
        var element = document.querySelector(selector + '="' + id + '"]');
        if (!element) return;
        element.textContent = value && value !== '' ? value : '-';
    }

    function updateHidden(id, field, value) {
        var input = document.querySelector('[data-hidden-jawaban="' + id + '"][data-hidden-field="' + field + '"]');
        if (input) input.value = value || '';
    }

    function updateCsrf(csrf) {
        if (!csrf || !csrf.name) return;
        document.querySelectorAll('input[name="' + csrf.name + '"]').forEach(function (input) {
            input.value = csrf.hash;
            input.defaultValue = csrf.hash;
        });
    }

    function showModalMessage(type, message) {
        if (!modalMessage) return;
        modalMessage.className = 'ami-alert ami-alert-' + (type === 'danger' ? 'danger' : type === 'success' ? 'success' : 'info');
        modalMessage.textContent = message;
        modalMessage.classList.remove('ami-hidden');
    }

    function clearModalMessage() {
        if (!modalMessage) return;
        modalMessage.className = 'ami-alert ami-hidden';
        modalMessage.textContent = '';
    }

    function setRadioValue(name, value) {
        modalForm.querySelectorAll('input[name="' + name + '"]').forEach(function (input) {
            input.checked = input.value === String(value || '');
        });
    }

    function setModalFile(dokumen, url) {
        var link = modal.querySelector('[data-modal-dokumen-link]');
        var label = modal.querySelector('[data-modal-dokumen-label]');
        var empty = modal.querySelector('[data-modal-dokumen-empty]');
        if (dokumen && url) {
            link.href = url;
            label.textContent = dokumen;
            link.classList.remove('ami-hidden');
            empty.classList.add('ami-hidden');
        } else {
            link.href = '#';
            label.textContent = '';
            link.classList.add('ami-hidden');
            empty.classList.remove('ami-hidden');
        }
    }

    function fillModal(button) {
        activeButton = button;
        clearModalMessage();
        modalForm.reset();
        modalForm.action = saveBaseUrl + button.dataset.jawabanId;

        var title = modal.querySelector('#penilaian-modal-title');
        if (title) title.textContent = (readOnly ? 'Detail penilaian' : 'Penilaian') + ' pertanyaan ' + button.dataset.nomor;

        setRadioValue('skor', button.dataset.skor);
        setRadioValue('jenis_temuan', button.dataset.jenisTemuan);
        modal.querySelector('[name="temuan"]').value = button.dataset.temuan || '';
        modal.querySelector('[name="saran_perbaikan"]').value = button.dataset.saranPerbaikan || '';
        modal.querySelector('[name="rencana_perbaikan"]').value = button.dataset.rencanaPerbaikan || '';
        modal.querySelector('[name="tgl_bukti"]').value = button.dataset.tglBukti || '';
        setModalFile(button.dataset.dokumenBukti || '', button.dataset.downloadUrl || '');
    }

    function applySavedData(data) {
        if (!data || !data.jawaban) return;

        var item = data.jawaban;
        var id = item.id;
        var statusBadge = document.querySelector('[data-status-badge="' + id + '"]');
        var statusIcon = document.querySelector('[data-status-icon="' + id + '"]');
        var dokumenLink = document.querySelector('[data-dokumen-link="' + id + '"]');
        var dokumenEmpty = document.querySelector('[data-dokumen-empty="' + id + '"]');
        var dokumenLabel = document.querySelector('[data-dokumen-label="' + id + '"]');

        if (statusBadge) statusBadge.className = 'ami-status ' + item.status_class;
        setStatusIcon(statusIcon, item.status_icon);
        setText('[data-status-label', id, item.status_label);
        setText('[data-score-label', id, item.skor_label);
        setText('[data-jenis-label', id, item.jenis_temuan_label);
        setHtmlText('[data-temuan-summary', id, item.temuan);
        setHtmlText('[data-saran-summary', id, item.saran_perbaikan);
        setHtmlText('[data-rencana-summary', id, item.rencana_perbaikan);
        setText('[data-tgl-label', id, item.tgl_bukti_label);

        if (item.download_url) {
            if (dokumenLink) {
                dokumenLink.href = item.download_url;
                dokumenLink.classList.remove('ami-hidden');
            }
            if (dokumenLabel) dokumenLabel.textContent = item.dokumen_bukti_label;
            if (dokumenEmpty) dokumenEmpty.classList.add('ami-hidden');
        } else {
            if (dokumenLink) dokumenLink.classList.add('ami-hidden');
            if (dokumenEmpty) dokumenEmpty.classList.remove('ami-hidden');
        }

        updateHidden(id, 'skor', item.skor || '');
        updateHidden(id, 'temuan', item.temuan || '');
        updateHidden(id, 'jenis_temuan', item.jenis_temuan || '');
        updateHidden(id, 'saran_perbaikan', item.saran_perbaikan || '');
        updateHidden(id, 'rencana_perbaikan', item.rencana_perbaikan || '');
        updateHidden(id, 'tgl_bukti', item.tgl_bukti || '');

        document.querySelectorAll('[data-penilaian-button][data-jawaban-id="' + id + '"]').forEach(function (button) {
            button.dataset.skor = item.skor || '';
            button.dataset.temuan = item.temuan || '';
            button.dataset.jenisTemuan = item.jenis_temuan || '';
            button.dataset.saranPerbaikan = item.saran_perbaikan || '';
            button.dataset.rencanaPerbaikan = item.rencana_perbaikan || '';
            button.dataset.tglBukti = item.tgl_bukti || '';
            button.dataset.dokumenBukti = item.dokumen_bukti || '';
            button.dataset.downloadUrl = item.download_url || '';
        });
    }

    document.querySelectorAll('[data-penilaian-button]').forEach(function (button) {
        button.addEventListener('click', function () {
            fillModal(button);
            showModal();
        });
    });

    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) hideModal();
        });
        modal.querySelectorAll('[data-penilaian-modal-close]').forEach(function (button) {
            button.addEventListener('click', hideModal);
        });
        modal.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                event.preventDefault();
                hideModal();
                return;
            }
            if (event.key !== 'Tab') return;
            var focusable = Array.prototype.slice.call(modal.querySelectorAll(focusableSelector));
            if (!focusable.length) return;
            var first = focusable[0];
            var last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    }

    if (modalForm) {
        modalForm.addEventListener('submit', function (event) {
            event.preventDefault();
            if (readOnly) return;

            clearModalMessage();
            var submitButton = modalForm.querySelector('[data-modal-submit]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.dataset.originalHtml = submitButton.innerHTML;
                submitButton.innerHTML = '<span class="ami-loading-spinner" aria-hidden="true"></span><span>Menyimpan...</span>';
            }

            fetch(modalForm.action, {
                method: 'POST',
                body: new FormData(modalForm),
                credentials: 'same-origin',
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            }).then(function (response) {
                return response.text().then(function (text) {
                    try {
                        return JSON.parse(text);
                    } catch (error) {
                        th new Error('Response server tidak valid.');
                    }
                });
            }).then(function (data) {
                updateCsrf(data.csrf);
                if (!data.success) {
                    showModalMessage('danger', data.message || 'Penilaian gagal disimpan.');
                    return;
                }

                applySavedData(data);
                showModalMessage('success', data.message || 'Penilaian berhasil disimpan.');
                setTimeout(hideModal, 450);
            }).catch(function (error) {
                showModalMessage('danger', error.message || 'Penilaian gagal disimpan.');
            }).finally(function () {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = submitButton.dataset.originalHtml || 'Simpan';
                    delete submitButton.dataset.originalHtml;
                }
            });
        });
    }

    document.querySelectorAll('[data-confirm]').forEach(function (button) {
        button.addEventListener('click', function (event) {
            var message = button.getAttribute('data-confirm');
            if (message && !window.confirm(message)) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });
})();
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
