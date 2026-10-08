<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$radar_items = [];
if (!empty($items)) {
    foreach ($items as $item) {
        $score = max(0, min(4, (float) $item->score));
        $radar_items[] = ['label' => (string) $item->indicator_code_snapshot, 'score' => $score];
    }
}
$radar_center = 180;
$radar_radius = 120;
$radar_count = count($radar_items);
$radar_point = function ($index, $value) use ($radar_count, $radar_center, $radar_radius) {
    $angle = -M_PI / 2 + (2 * M_PI * $index / max(1, $radar_count));
    $radius = $radar_radius * max(0, min(4, $value)) / 4;
    return [round($radar_center + cos($angle) * $radius), round($radar_center + sin($angle) * $radius)];
};
$radar_png = '';
if ($radar_count > 0) {
    $radar_width = 900;
    $radar_height = 700;
    $radar_center_x = 450;
    $radar_center_y = 350;
    $radar_radius = 220;
    $radar_image = imagecreatetruecolor($radar_width, $radar_height);
    $radar_background = imagecolorallocate($radar_image, 255, 255, 255);
    $radar_ring_color = imagecolorallocate($radar_image, 184, 196, 214);
    $radar_axis_color = imagecolorallocate($radar_image, 215, 222, 233);
    $radar_line_color = imagecolorallocate($radar_image, 37, 99, 235);
    $radar_fill_color = imagecolorallocatealpha($radar_image, 147, 197, 253, 75);
    $radar_text_color = imagecolorallocate($radar_image, 15, 23, 42);
    imagefill($radar_image, 0, 0, $radar_background);
    $radar_canvas_point = function ($index, $value) use ($radar_count, $radar_center_x, $radar_center_y, $radar_radius) {
        $angle = -M_PI / 2 + (2 * M_PI * $index / max(1, $radar_count));
        $radius = $radar_radius * max(0, min(4, $value)) / 4;
        return [round($radar_center_x + cos($angle) * $radius), round($radar_center_y + sin($angle) * $radius)];
    };
    for ($level = 1; $level <= 4; $level++) {
        $ring_points = [];
        foreach (range(0, $radar_count - 1) as $index) $ring_points = array_merge($ring_points, $radar_canvas_point($index, $level));
        if ($radar_count >= 3) imagepolygon($radar_image, $ring_points, $radar_count, $radar_ring_color);
        imagestring($radar_image, 2, $radar_center_x + 5, $radar_center_y - ($radar_radius * $level / 4) - 8, (string) $level, $radar_text_color);
    }
    foreach (range(0, $radar_count - 1) as $index) {
        $axis_point = $radar_canvas_point($index, 4);
        imageline($radar_image, $radar_center_x, $radar_center_y, $axis_point[0], $axis_point[1], $radar_axis_color);
        $label_point = $radar_canvas_point($index, 4.65);
        $label = strlen($radar_items[$index]['label']) > 18 ? substr($radar_items[$index]['label'], 0, 17) . '...' : $radar_items[$index]['label'];
        imagestring($radar_image, 3, max(4, min($radar_width - 120, $label_point[0] - 35)), max(4, min($radar_height - 18, $label_point[1] - 7)), $label, $radar_text_color);
    }
    $radar_score_points = [];
    foreach ($radar_items as $index => $radar_item) $radar_score_points = array_merge($radar_score_points, $radar_canvas_point($index, $radar_item['score']));
    if ($radar_count >= 3) {
        imagefilledpolygon($radar_image, $radar_score_points, $radar_count, $radar_fill_color);
        imagepolygon($radar_image, $radar_score_points, $radar_count, $radar_line_color);
    }
    ob_start();
    imagepng($radar_image);
    $radar_png = base64_encode(ob_get_clean());
    imagedestroy($radar_image);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?php echo html_escape($report->report_number); ?></title>
<style>
body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; }
h1 { font-size: 16pt; } h2 { font-size: 12pt; margin-top: 18pt; }
table { width: 100%; border-collapse: collapse; margin: 8pt 0 14pt; }
th, td { border: 1px solid #000; padding: 5pt; vertical-align: top; text-align: left; }
th { background: #e7edf5; } .meta td { width: 25%; } .small { font-size: 8pt; }
.radar-box { margin: 12pt 0 16pt; } .radar-image { width: 900px; height: 700px; }
.radar-legend th, .radar-legend td { font-size: 9pt; }
</style>
</head>
<body>
<h1>Laporan Audit Mutu Internal</h1>
<p><strong>Nomor laporan:</strong> <?php echo html_escape($report->report_number); ?></p>
<table class="meta">
<tr><td><strong>Siklus</strong><br><?php echo html_escape($report->cycle_code_snapshot . ' — ' . $report->cycle_title_snapshot); ?></td><td><strong>Periode</strong><br><?php echo html_escape($report->cycle_start_date_snapshot . ' — ' . $report->cycle_end_date_snapshot); ?></td><td><strong>Versi</strong><br><?php echo html_escape($report->source_version_code_snapshot . ' — ' . $report->source_version_title_snapshot); ?></td><td><strong>Auditee</strong><br><?php echo html_escape($report->auditee_name_snapshot); ?></td></tr>
<tr><td colspan="2"><strong>Kontributor auditor</strong><br><?php foreach ($contributors as $contributor): ?><?php echo html_escape($contributor['name'] . ($contributor['email'] !== '' ? ' <' . $contributor['email'] . '>' : '')); ?><br><?php endforeach; ?></td><td colspan="2"><strong>Finalisasi M9 terakhir</strong><br><?php echo html_escape($report->assessment_finalized_at_snapshot); ?></td></tr>
</table>
<?php if ($radar_count > 0): ?>
<div class="radar-box" aria-label="Radar capaian indikator SPMI berbasis snapshot immutable">
<h2>Radar Capaian Indikator</h2>
<img id="word-radar-capaian-indikator" class="radar-image" src="data:image/png;base64,<?php echo $radar_png; ?>" alt="Radar capaian indikator SPMI skala 0 sampai 4">
<table class="radar-legend">
<thead><tr><th>Indikator snapshot</th><th>Skor snapshot (0..4)</th></tr></thead>
<tbody><?php foreach ($radar_items as $radar_item): ?><tr><td><?php echo html_escape($radar_item['label']); ?></td><td><?php echo html_escape((string) $radar_item['score']); ?></td></tr><?php endforeach; ?></tbody>
</table>
</div>
<?php endif; ?>
<?php foreach ($standards as $standard): ?>
<h2><?php echo html_escape($standard['source_standard_code_snapshot'] . ' — ' . $standard['source_standard_title_snapshot']); ?></h2>
<table>
<thead><tr><th>No</th><th>Auditor penanggung jawab</th><th>Indikator</th><th>Realisasi</th><th>Skor</th><th>Deskriptor</th><th>Temuan</th><th>Rekomendasi</th><th>Rencana perbaikan</th></tr></thead>
<tbody><?php foreach ($standard['items'] as $item): ?><tr><td><?php echo html_escape($item->standard_item_display_order ?: $item->display_order); ?></td><td><?php echo html_escape((isset($item->auditor_name_snapshot) && trim((string) $item->auditor_name_snapshot) !== '') ? $item->auditor_name_snapshot : $report->auditor_name_snapshot); ?><br><span class="small"><?php echo html_escape(isset($item->auditor_email_snapshot) ? $item->auditor_email_snapshot : ''); ?></span></td><td><?php echo html_escape($item->indicator_code_snapshot . ' — ' . $item->indicator_title_snapshot); ?></td><td><?php echo nl2br(html_escape($item->realization_snapshot)); ?></td><td><?php echo html_escape($item->score); ?></td><td><?php echo nl2br(html_escape($item->descriptor_snapshot)); ?></td><td><?php echo nl2br(html_escape($item->finding_snapshot)); ?></td><td><?php echo nl2br(html_escape($item->recommendation_snapshot)); ?></td><td><?php echo nl2br(html_escape(isset($item->improvement_plan_snapshot) ? $item->improvement_plan_snapshot : '')); ?></td></tr><?php endforeach; ?></tbody>
</table>
<?php endforeach; ?>
</body>
</html>
