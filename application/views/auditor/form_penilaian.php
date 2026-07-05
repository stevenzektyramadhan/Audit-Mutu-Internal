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
    <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
<?php endif; ?>

<div class="ami-panel mb-3">
    <div class="ami-panel-body">
        <div class="row">
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="ami-stat-label">Periode</div>
                <div class="font-weight-bold"><?php echo html_escape($tugas->nama_periode ?: '-'); ?></div>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="ami-stat-label">Auditee</div>
                <div class="font-weight-bold"><?php echo html_escape($tugas->auditee_nama ?: '-'); ?></div>
                <?php if (!empty($tugas->auditee_unit)): ?>
                    <div class="text-muted" style="font-size:12px;">
                        <?php echo html_escape($tugas->auditee_unit); ?>
                        <?php if (!empty($tugas->auditee_jenis_unit)): ?>
                            &middot; <?php echo html_escape(strtoupper($tugas->auditee_jenis_unit)); ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="ami-stat-label">Standar</div>
                <div class="font-weight-bold"><?php echo html_escape($tugas->nama_standar ?: '-'); ?></div>
            </div>
            <div class="col-md-3">
                <div class="ami-stat-label">Status penilaian</div>
                <span class="ami-status <?php echo html_escape($tugas->penilaian_status_class); ?>">
                    <i class="fas <?php echo html_escape($tugas->penilaian_status_icon); ?>" aria-hidden="true"></i>
                    <?php echo html_escape($tugas->penilaian_status_label); ?>
                </span>
            </div>
        </div>

        <div class="ami-actions mt-3">
            <a href="<?php echo site_url('auditor/penilaian'); ?>" class="btn btn-outline-ami btn-ami">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali
            </a>
        </div>
    </div>
</div>

<?php if (!empty($tugas->submitted_at)): ?>
    <div class="alert alert-primary d-flex align-items-center" style="background:#e6f1fb;color:#185fa5;border:0;border-radius:8px;">
        <i class="fas fa-paper-plane mr-2" aria-hidden="true"></i>
        Jawaban auditee submitted pada <?php echo html_escape(format_tanggal_indo($tugas->submitted_at)); ?>.
    </div>
<?php endif; ?>

<?php if ($read_only): ?>
    <div class="alert alert-success d-flex align-items-center" style="background:#eaf3de;color:#315d13;border:0;border-radius:8px;">
        <i class="fas fa-lock mr-2" aria-hidden="true"></i>
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

            <div class="ami-panel mb-3" data-penilaian-row="<?php echo (int) $item->id; ?>">
                <div class="ami-panel-body">
                    <div class="d-flex align-items-start justify-content-between flex-wrap mb-3" style="gap:10px;">
                        <div class="d-flex align-items-start" style="min-width:0;">
                            <span class="ami-nav-badge mr-2" style="margin-left:0;"><?php echo (int) $index + 1; ?></span>
                            <div class="font-weight-bold"><?php echo html_escape($item->isi_pertanyaan); ?></div>
                        </div>
                        <span class="ami-status <?php echo $has_score ? 'status-dinilai' : 'status-belum_diisi'; ?>" data-status-badge="<?php echo (int) $item->id; ?>">
                            <i class="fas <?php echo $has_score ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>" aria-hidden="true" data-status-icon="<?php echo (int) $item->id; ?>"></i>
                            <span data-status-label="<?php echo (int) $item->id; ?>"><?php echo $has_score ? 'Sudah dinilai' : 'Belum dinilai'; ?></span>
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-lg-7 mb-3 mb-lg-0">
                            <div class="ami-stat-label mb-1">Jawaban auditee</div>
                            <div class="mb-3"><?php echo nl2br(html_escape($item->jawaban ?: '-')); ?></div>

                            <div class="ami-stat-label mb-1">Link bukti auditee</div>
                            <?php if (!empty($item->link_bukti)): ?>
                                <a href="<?php echo html_escape($item->link_bukti); ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="fas fa-external-link-alt mr-1" aria-hidden="true"></i>Buka bukti
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-lg-5">
                            <div class="p-3" style="border-left:3px solid var(--ami-border);">
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <div class="ami-stat-label mb-1">Skor</div>
                                        <div class="font-weight-bold" data-score-label="<?php echo (int) $item->id; ?>">
                                            <?php echo $has_score ? (int) $skor . ' - ' . html_escape($skor_options[$skor] ?? '') : 'Belum ada'; ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <div class="ami-stat-label mb-1">Jenis temuan</div>
                                        <div class="font-weight-bold" data-jenis-label="<?php echo (int) $item->id; ?>"><?php echo html_escape($jenis_label); ?></div>
                                    </div>
                                </div>

                                <div class="ami-stat-label mb-1">Temuan</div>
                                <div class="mb-3" data-temuan-summary="<?php echo (int) $item->id; ?>"><?php echo nl2br(html_escape($item->temuan ?: '-')); ?></div>

                                <div class="ami-stat-label mb-1">Saran perbaikan</div>
                                <div class="mb-3" data-saran-summary="<?php echo (int) $item->id; ?>"><?php echo nl2br(html_escape($item->saran_perbaikan ?: '-')); ?></div>

                                <div class="ami-stat-label mb-1">Rencana perbaikan</div>
                                <div class="mb-3" data-rencana-summary="<?php echo (int) $item->id; ?>"><?php echo nl2br(html_escape($item->rencana_perbaikan ?: '-')); ?></div>

                                <div class="row align-items-end">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <div class="ami-stat-label mb-1">Tanggal bukti</div>
                                        <div data-tgl-label="<?php echo (int) $item->id; ?>">
                                            <?php echo !empty($item->tgl_bukti) ? html_escape(format_tanggal_indo($item->tgl_bukti)) : '-'; ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="ami-stat-label mb-1">Dokumen bukti</div>
                                        <a href="<?php echo html_escape($download_url); ?>" class="<?php echo $download_url === '' ? 'd-none' : ''; ?>" data-dokumen-link="<?php echo (int) $item->id; ?>">
                                            <i class="fas fa-download mr-1" aria-hidden="true"></i><span data-dokumen-label="<?php echo (int) $item->id; ?>"><?php echo html_escape($dokumen_bukti); ?></span>
                                        </a>
                                        <span class="<?php echo $download_url !== '' ? 'd-none' : 'text-muted'; ?>" data-dokumen-empty="<?php echo (int) $item->id; ?>">-</span>
                                    </div>
                                </div>

                                <div class="ami-actions mt-3">
                                    <button type="button"
                                            class="ami-action-btn"
                                            data-toggle="modal"
                                            data-target="#penilaian-modal"
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
                                        <i class="fas <?php echo $read_only ? 'fa-eye' : 'fa-star'; ?>" aria-hidden="true"></i>
                                        <?php echo $read_only ? 'Detail' : 'Nilai'; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="ami-actions justify-content-end">
            <a href="<?php echo site_url('auditor/penilaian'); ?>" class="btn btn-outline-ami btn-ami">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali
            </a>
            <?php if (!$read_only): ?>
                <button type="submit" class="btn btn-outline-ami btn-ami" data-loading-text="Menyimpan...">
                    <i class="fas fa-save" aria-hidden="true"></i> Save draft
                </button>
                <button type="submit"
                        class="btn btn-primary btn-ami"
                        formaction="<?php echo site_url('auditor/penilaian/submit/' . (int) $tugas->id); ?>"
                        data-confirm="Submit penilaian? Setelah submit, form akan terkunci."
                        data-loading-text="Submit...">
                    <i class="fas fa-paper-plane" aria-hidden="true"></i> Submit
                </button>
                <button type="submit"
                        class="btn btn-outline-ami btn-ami"
                        formaction="<?php echo site_url('auditor/penilaian/revisi/' . (int) $tugas->id); ?>"
                        data-confirm="Kembalikan tugas ini ke auditee untuk revisi?"
                        data-loading-text="Mengembalikan...">
                    <i class="fas fa-undo" aria-hidden="true"></i> Revisi
                </button>
            <?php endif; ?>
        </div>
    <?php echo form_close(); ?>
<?php else: ?>
    <div class="ami-panel">
        <div class="ami-empty">
            <div class="ami-empty-icon"><i class="fas fa-list" aria-hidden="true"></i></div>
            <div class="ami-empty-title">Tidak ada pertanyaan</div>
            <div>Pertanyaan audit untuk tugas ini belum tersedia.</div>
        </div>
    </div>
<?php endif; ?>

<style>
    #penilaian-modal .modal-dialog {
        height: calc(100vh - 3.5rem);
        max-height: calc(100vh - 3.5rem);
        display: flex;
        align-items: stretch;
    }

    #penilaian-modal .modal-content {
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

    #penilaian-modal .modal-header,
    #penilaian-modal .modal-footer {
        flex: 0 0 auto;
    }

    #penilaian-modal .modal-body {
        min-height: 0;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    @media (max-width: 575.98px) {
        #penilaian-modal .modal-dialog {
            height: calc(100vh - 1rem);
            max-height: calc(100vh - 1rem);
        }
    }
</style>

<div class="modal fade" id="penilaian-modal" tabindex="-1" role="dialog" aria-labelledby="penilaian-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content" style="background:var(--ami-panel);color:var(--ami-text);border:1px solid var(--ami-border);border-radius:8px;">
            <form id="penilaian-modal-form" method="post" enctype="multipart/form-data">
                <input type="hidden" name="<?php echo html_escape($csrf_name); ?>" value="<?php echo html_escape($csrf_hash); ?>">
                <div class="modal-header" style="border-bottom:1px solid var(--ami-border);">
                    <h5 class="modal-title" id="penilaian-modal-title">Penilaian pertanyaan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup" style="color:var(--ami-text);">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert d-none" data-modal-message role="alert"></div>

                    <div class="form-group">
                        <label>Skor</label>
                        <div class="d-flex flex-wrap" style="gap:8px;">
                            <?php foreach ([4, 3, 2, 1] as $score): ?>
                                <label class="ami-action-btn mb-0" style="min-height:38px;">
                                    <input type="radio" name="skor" value="<?php echo (int) $score; ?>" class="mr-2" <?php echo $read_only ? 'disabled' : ''; ?>>
                                    <?php echo (int) $score; ?> - <?php echo html_escape($skor_options[$score]); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="modal-temuan">Temuan</label>
                        <textarea id="modal-temuan" name="temuan" rows="4" class="form-control" <?php echo $read_only ? 'readonly' : ''; ?>></textarea>
                    </div>

                    <div class="form-group">
                        <label>Jenis Temuan</label>
                        <div class="d-flex flex-wrap" style="gap:12px;">
                            <label class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="jenis_temuan" value="ob" <?php echo $read_only ? 'disabled' : ''; ?>>
                                <span class="form-check-label">OB</span>
                            </label>
                            <label class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="jenis_temuan" value="kts" <?php echo $read_only ? 'disabled' : ''; ?>>
                                <span class="form-check-label">KTS</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="modal-saran">Saran Perbaikan</label>
                        <textarea id="modal-saran" name="saran_perbaikan" rows="3" class="form-control" <?php echo $read_only ? 'readonly' : ''; ?>></textarea>
                    </div>

                    <div class="form-group">
                        <label for="modal-rencana">Rencana Perbaikan</label>
                        <textarea id="modal-rencana" name="rencana_perbaikan" rows="3" class="form-control" <?php echo $read_only ? 'readonly' : ''; ?>></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="modal-dokumen">Dokumen Bukti</label>
                                <input id="modal-dokumen" type="file" name="dokumen_bukti" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" <?php echo $read_only ? 'disabled' : ''; ?>>
                                <div class="mt-2" data-modal-dokumen-wrap>
                                    <a href="#" target="_blank" rel="noopener noreferrer" data-modal-dokumen-link>
                                        <i class="fas fa-download mr-1" aria-hidden="true"></i><span data-modal-dokumen-label></span>
                                    </a>
                                    <span class="text-muted" data-modal-dokumen-empty>-</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="modal-tgl-bukti">Tanggal Bukti</label>
                                <input id="modal-tgl-bukti" type="date" name="tgl_bukti" class="form-control" <?php echo $read_only ? 'readonly' : ''; ?>>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--ami-border);">
                    <button type="button" class="btn btn-outline-ami btn-ami" data-dismiss="modal">
                        <i class="fas fa-times" aria-hidden="true"></i> Tutup
                    </button>
                    <?php if (!$read_only): ?>
                        <button type="submit" class="btn btn-primary btn-ami" data-modal-submit>
                            <i class="fas fa-save" aria-hidden="true"></i> Simpan
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
        modalMessage.className = 'alert alert-' + type;
        modalMessage.textContent = message;
        modalMessage.classList.remove('d-none');
    }

    function clearModalMessage() {
        if (!modalMessage) return;
        modalMessage.className = 'alert d-none';
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
            link.classList.remove('d-none');
            empty.classList.add('d-none');
        } else {
            link.href = '#';
            label.textContent = '';
            link.classList.add('d-none');
            empty.classList.remove('d-none');
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
        if (statusIcon) statusIcon.className = 'fas ' + item.status_icon;
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
                dokumenLink.classList.remove('d-none');
            }
            if (dokumenLabel) dokumenLabel.textContent = item.dokumen_bukti_label;
            if (dokumenEmpty) dokumenEmpty.classList.add('d-none');
        } else {
            if (dokumenLink) dokumenLink.classList.add('d-none');
            if (dokumenEmpty) dokumenEmpty.classList.remove('d-none');
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
        });
    });

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
                        throw new Error('Response server tidak valid.');
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
                if (window.jQuery) {
                    setTimeout(function () { window.jQuery(modal).modal('hide'); }, 450);
                }
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
