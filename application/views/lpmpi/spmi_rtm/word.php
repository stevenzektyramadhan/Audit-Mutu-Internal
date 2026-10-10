<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$reports_by_id = [];
foreach ($reports as $report) $reports_by_id[(int) $report->report_id] = $report;
$role_labels = ['super_admin' => 'Super Admin', 'admin_lpmpi' => 'Admin LPMPI', 'auditor' => 'Auditor', 'auditee' => 'Auditi'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?php echo html_escape($meeting->meeting_code); ?></title>
<style>
body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; }
h1 { font-size: 16pt; margin-bottom: 6pt; } h2 { font-size: 12pt; margin-top: 18pt; }
table { width: 100%; border-collapse: collapse; margin: 8pt 0 14pt; }
th, td { border: 1px solid #000; padding: 5pt; vertical-align: top; text-align: left; }
th { background: #e7edf5; } .meta td { width: 25%; } .empty { font-style: italic; }
</style>
</head>
<body>
<h1>Rapat Tinjauan Manajemen SPMI</h1>
<p><strong><?php echo html_escape($meeting->meeting_code . ' — ' . $meeting->meeting_title); ?></strong></p>
<table class="meta">
<tr><td><strong>Kode RTM</strong><br><?php echo html_escape($meeting->meeting_code); ?></td><td><strong>Status</strong><br><?php echo html_escape($meeting->status); ?></td><td><strong>Tanggal</strong><br><?php echo html_escape($meeting->meeting_date); ?></td><td><strong>Lokasi</strong><br><?php echo html_escape($meeting->location); ?></td></tr>
<tr><td colspan="4"><strong>Agenda rapat</strong><br><?php echo nl2br(html_escape($meeting->meeting_title)); ?></td></tr>
</table>

<h2>Laporan Snapshot Terhubung</h2>
<?php if (empty($reports)): ?>
<p class="empty">Tidak ada laporan snapshot yang terhubung.</p>
<?php else: ?>
<table>
<thead><tr><th>No</th><th>Nomor laporan</th><th>Siklus</th></tr></thead>
<tbody><?php foreach ($reports as $index => $report): ?><tr><td><?php echo html_escape((string) ($index + 1)); ?></td><td><?php echo html_escape($report->report_number); ?></td><td><?php echo html_escape($report->cycle_code_snapshot . ' — ' . $report->cycle_title_snapshot); ?></td></tr><?php endforeach; ?></tbody>
</table>
<?php endif; ?>

<h2>Peserta Snapshot</h2>
<?php if (empty($participants)): ?>
<p class="empty">Tidak ada peserta tercatat.</p>
<?php else: ?>
<table>
<thead><tr><th>No</th><th>Nama</th><th>Email</th><th>Peran</th></tr></thead>
<tbody><?php foreach ($participants as $index => $participant): ?><?php $role = (string) $participant->role_snapshot; ?><tr><td><?php echo html_escape((string) ($index + 1)); ?></td><td><?php echo html_escape($participant->name_snapshot); ?></td><td><?php echo html_escape($participant->email_snapshot); ?></td><td><?php echo html_escape(isset($role_labels[$role]) ? $role_labels[$role] : ucfirst(str_replace('_', ' ', $role))); ?></td></tr><?php endforeach; ?></tbody>
</table>
<?php endif; ?>

<h2>Keputusan dan Tindakan</h2>
<?php if (empty($decisions)): ?>
<p class="empty">Tidak ada keputusan tercatat.</p>
<?php else: ?>
<table>
<thead><tr><th>No</th><th>Keputusan</th><th>Tindakan</th><th>Sumber laporan</th></tr></thead>
<tbody><?php foreach ($decisions as $decision): ?><tr><td><?php echo html_escape($decision->display_order); ?></td><td><?php echo nl2br(html_escape($decision->decision_text)); ?></td><td><?php echo nl2br(html_escape($decision->action_text)); ?></td><td><?php $report_id = (int) $decision->report_id; ?><?php if ($report_id > 0 && isset($reports_by_id[$report_id])): ?><?php echo html_escape($reports_by_id[$report_id]->report_number); ?><?php if ($decision->report_item_id): ?><br><?php echo html_escape('Butir laporan #' . $decision->report_item_id); ?><?php endif; ?><?php elseif ($report_id > 0): ?><?php echo html_escape('Laporan #' . $decision->report_id . ($decision->report_item_id ? ' / Butir #' . $decision->report_item_id : '')); ?><?php else: ?>-<?php endif; ?></td></tr><?php endforeach; ?></tbody>
</table>
<?php endif; ?>
</body>
</html>
