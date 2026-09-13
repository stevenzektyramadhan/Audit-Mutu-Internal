<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function spmi_report_print_value($value) {
    $value = trim((string) $value);
    return $value === '' ? '-' : $value;
}

function spmi_report_print_format_bytes($bytes) {
    $bytes = (int) $bytes;
    if ($bytes <= 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = (int) floor(log($bytes, 1024));
    $i = min($i, count($units) - 1);
    return round($bytes / pow(1024, $i), 1) . ' ' . $units[$i];
}

function spmi_report_print_auditor_evidence($value) {
    $items = json_decode((string) $value, TRUE);
    if (!is_array($items)) return [];
    $valid = [];
    foreach ($items as $item) {
        if (!is_array($item)) continue;
        $valid[] = [
            'original_name' => isset($item['original_name']) ? (string) $item['original_name'] : '-',
            'mime_type' => isset($item['mime_type']) ? (string) $item['mime_type'] : '-',
            'size_bytes' => isset($item['size_bytes']) ? (int) $item['size_bytes'] : 0,
            'sha256' => isset($item['sha256']) ? (string) $item['sha256'] : '-',
        ];
    }
    return $valid;
}
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html_escape($title); ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 12mm 12mm 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #0f172a;
            background: #ffffff;
            margin: 0;
            padding: 24px;
            font-size: 11px;
            line-height: 1.45;
        }

        /* Screen toolbar */
        .no-print {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .no-print-text {
            color: #475569;
            font-size: 12px;
        }

        .no-print-text strong {
            color: #0f172a;
        }

        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #185fa5;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-print:hover {
            background: #124e89;
        }

        /* Official Report Header */
        .report-header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .institution-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #475569;
            margin-bottom: 2px;
        }

        .report-title {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.01em;
        }

        .report-badge {
            display: inline-block;
            font-family: "Courier New", Courier, monospace;
            font-size: 12px;
            font-weight: 700;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 3px 8px;
            border-radius: 4px;
            color: #0f172a;
        }

        .metadata-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px 16px;
            font-size: 10.5px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            font-size: 9.5px;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.04em;
        }

        .meta-value {
            font-weight: 600;
            color: #1e293b;
            margin-top: 1px;
        }

        .section-heading {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin: 14px 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* Report Table */
        table.report-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 10px;
        }

        table.report-table thead {
            display: table-header-group;
        }

        table.report-table tr {
            page-break-inside: avoid;
        }

        table.report-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.04em;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            vertical-align: middle;
        }

        table.report-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            vertical-align: top;
            color: #1e293b;
        }

        .col-no { width: 26px; text-align: center; font-weight: 700; color: #64748b; }
        .col-question { width: 22%; }
        .col-evidence { width: 26%; }
        .col-score { width: 44px; text-align: center; }
        .col-desc { width: 14%; }
        .col-finding { width: 13%; }
        .col-recommendation { width: 12%; }
        .col-plan { width: 11%; }
        .col-date { width: 65px; text-align: center; }

        .score-pill {
            display: inline-block;
            font-weight: 800;
            font-size: 11px;
            background: #e2e8f0;
            color: #0f172a;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .finding-badge {
            display: inline-block;
            font-size: 9px;
            font-weight: 700;
            padding: 1px 4px;
            border-radius: 3px;
            background: #fef3c7;
            color: #92400e;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .evidence-block {
            margin-top: 5px;
            padding-top: 4px;
            border-top: 1px dashed #cbd5e1;
            font-size: 9.5px;
        }

        .evidence-label {
            font-weight: 700;
            color: #475569;
        }

        .evidence-url {
            word-break: break-all;
            color: #0369a1;
        }

        .evidence-file {
            font-weight: 600;
            color: #0f172a;
        }

        .evidence-tech {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 2px;
            font-family: "Courier New", Courier, monospace;
            word-break: break-all;
        }

        .auditor-ev-list {
            margin-top: 4px;
            padding-top: 3px;
            border-top: 1px dotted #e2e8f0;
        }

        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            table.report-table th {
                background: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<!-- On-screen toolbar -->
<div class="no-print">
    <div class="no-print-text">
        <strong>Gunakan dialog browser: pilih “Save as PDF”</strong> dengan orientasi <em>Landscape</em>.
    </div>
    <button type="button" class="btn-print" onclick="window.print()">Print / Save as PDF</button>
</div>

<!-- Report Header -->
<header class="report-header">
    <div class="header-top">
        <div>
            <div class="institution-title">Sistem Penjaminan Mutu Internal (SPMI)</div>
            <h1 class="report-title">Laporan Audit Mutu Internal</h1>
        </div>
        <div class="report-badge">
            <?php echo html_escape($report->report_number); ?>
        </div>
    </div>

    <div class="metadata-grid">
        <div class="meta-item">
            <span class="meta-label">Siklus Audit</span>
            <span class="meta-value"><?php echo html_escape($report->cycle_code_snapshot . ' — ' . $report->cycle_title_snapshot); ?></span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Periode Pelaksanaan</span>
            <span class="meta-value"><?php echo html_escape($report->cycle_start_date_snapshot . ' — ' . $report->cycle_end_date_snapshot); ?></span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Auditor</span>
            <span class="meta-value"><?php echo html_escape($report->auditor_name_snapshot); ?></span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Auditee</span>
            <span class="meta-value"><?php echo html_escape($report->auditee_name_snapshot); ?></span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Sumber Versi / Standar / Paket</span>
            <span class="meta-value" style="font-family: monospace; font-size: 10px;"><?php echo html_escape($report->source_version_code_snapshot . ' / ' . $report->source_standard_code_snapshot . ' / ' . $report->source_package_code_snapshot); ?></span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Finalisasi M9</span>
            <span class="meta-value"><?php echo html_escape($report->assessment_finalized_at_snapshot); ?></span>
        </div>
    </div>
</header>

<div class="section-heading">Hasil Audit Mutu</div>

<table class="report-table">
    <thead>
        <tr>
            <th class="col-no">No</th>
            <th class="col-question">Pertanyaan &amp; Indikator</th>
            <th class="col-evidence">Realisasi &amp; Bukti</th>
            <th class="col-score">Skor</th>
            <th class="col-desc">Deskriptor</th>
            <th class="col-finding">Temuan</th>
            <th class="col-recommendation">Rekomendasi</th>
            <th class="col-plan">Rencana Perbaikan</th>
            <th class="col-date">Tanggal Bukti</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item):
            $url = isset($item->evidence_url_snapshot) ? trim((string) $item->evidence_url_snapshot) : '';
            $file_name = isset($item->evidence_file_original_name_snapshot) ? trim((string) $item->evidence_file_original_name_snapshot) : '';
            $has_file = $file_name !== '';
            $auditor_evidences = spmi_report_print_auditor_evidence(isset($item->auditor_evidence_snapshot) ? $item->auditor_evidence_snapshot : NULL);
            $finding_type = spmi_report_print_value(isset($item->finding_type_snapshot) ? $item->finding_type_snapshot : NULL);
        ?>
            <tr>
                <td class="col-no"><?php echo html_escape($item->display_order); ?></td>
                <td class="col-question">
                    <strong><?php echo html_escape($item->question_code_snapshot); ?></strong><br>
                    <?php echo nl2br(html_escape($item->question_text_snapshot)); ?><br>
                    <small style="color: #64748b;"><?php echo html_escape($item->indicator_code_snapshot . ' — ' . $item->indicator_title_snapshot); ?></small>
                </td>
                <td class="col-evidence">
                    <div><?php echo nl2br(html_escape($item->realization_snapshot)); ?></div>

                    <?php if ($url !== '' || $has_file): ?>
                        <div class="evidence-block">
                            <?php if ($url !== ''): ?>
                                <div><span class="evidence-label">URL:</span> <span class="evidence-url"><?php echo nl2br(html_escape(spmi_report_print_value($item->evidence_url_snapshot))); ?></span></div>
                            <?php endif; ?>
                            <?php if ($has_file): ?>
                                <div><span class="evidence-label">File:</span> <span class="evidence-file"><?php echo nl2br(html_escape(spmi_report_print_value($item->evidence_file_original_name_snapshot))); ?></span> (<?php echo nl2br(html_escape(spmi_report_print_format_bytes(spmi_report_print_value(isset($item->evidence_file_size_bytes_snapshot) ? $item->evidence_file_size_bytes_snapshot : NULL)))); ?>)</div>
                                <div class="evidence-tech">
                                    MIME: <?php echo nl2br(html_escape(spmi_report_print_value($item->evidence_file_mime_type_snapshot))); ?> | SHA-256: <?php echo nl2br(html_escape(spmi_report_print_value($item->evidence_file_sha256_snapshot))); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($auditor_evidences)): ?>
                        <div class="auditor-ev-list">
                            <span class="evidence-label">Bukti Auditor:</span>
                            <?php foreach ($auditor_evidences as $aud_ev): ?>
                                <div style="margin-top: 2px;">
                                    <span class="evidence-file"><?php echo html_escape($aud_ev['original_name']); ?></span> (<?php echo html_escape(spmi_report_print_format_bytes($aud_ev['size_bytes'])); ?>)
                                    <div class="evidence-tech">
                                        MIME: <?php echo html_escape($aud_ev['mime_type']); ?> | SHA-256: <?php echo html_escape($aud_ev['sha256']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td class="col-score">
                    <span class="score-pill"><?php echo html_escape($item->score); ?></span>
                </td>
                <td class="col-desc">
                    <?php echo nl2br(html_escape($item->descriptor_snapshot)); ?>
                </td>
                <td class="col-finding">
                    <?php if ($finding_type !== '-'): ?>
                        <span class="finding-badge"><?php echo html_escape($finding_type); ?></span><br>
                    <?php endif; ?>
                    <?php echo nl2br(html_escape(spmi_report_print_value(isset($item->finding_snapshot) ? $item->finding_snapshot : NULL))); ?>
                </td>
                <td class="col-recommendation">
                    <?php echo nl2br(html_escape(spmi_report_print_value(isset($item->recommendation_snapshot) ? $item->recommendation_snapshot : NULL))); ?>
                </td>
                <td class="col-plan">
                    <?php echo nl2br(html_escape(spmi_report_print_value(isset($item->improvement_plan_snapshot) ? $item->improvement_plan_snapshot : NULL))); ?>
                </td>
                <td class="col-date">
                    <?php echo html_escape(spmi_report_print_value(isset($item->evidence_date_snapshot) ? $item->evidence_date_snapshot : NULL)); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
