<?php defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';
?>
<main id="audits-root" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
  <div class="tw-mx-auto tw-max-w-5xl">
    <!-- Back Navigation to Parent Cycle Detail (#assignments tab) -->
    <div class="tw-mb-5">
      <a class="audits-back-link" href="<?php echo site_url('lpmpi/spmi-audits/cycle/detail/' . (int) $assignment->cycle_id . '#assignments'); ?>">
        <svg class="tw-h-4 tw-w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        <span>Kembali ke Detail Siklus</span>
      </a>
    </div>

    <div class="tw-mb-6">
      <p class="tw-mb-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-text-slate-500">Immutable snapshot</p>
      <h1 class="tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950"><?php echo html_escape($assignment->source_standard_code . ' — ' . $assignment->source_standard_title); ?></h1>
      <p class="tw-mt-2 tw-text-sm tw-text-slate-500">Konfigurasi penugasan tersimpan sebagai identitas standar dan indikator pada saat dibuat.</p>
    </div>

    <div class="tw-mb-6 tw-grid tw-gap-4 md:tw-grid-cols-2">
      <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm">
        <h2 class="tw-mb-4 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest tw-text-slate-500">Sumber standar</h2>
        <dl class="tw-grid tw-gap-4 tw-text-sm">
          <div>
            <dt class="tw-font-semibold tw-text-slate-500">Versi snapshot</dt>
            <dd class="tw-mt-1 tw-text-slate-900"><?php echo html_escape($assignment->source_version_code . ' — ' . $assignment->source_version_title); ?></dd>
          </div>
          <div>
            <dt class="tw-font-semibold tw-text-slate-500">Standar snapshot</dt>
            <dd class="tw-mt-1 tw-text-slate-900"><?php echo html_escape($assignment->source_standard_code . ' — ' . $assignment->source_standard_title); ?></dd>
          </div>
        </dl>
      </section>

      <section class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm">
        <h2 class="tw-mb-4 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest tw-text-slate-500">Identitas penugasan</h2>
        <dl class="tw-grid tw-gap-4 tw-text-sm">
          <div>
            <dt class="tw-font-semibold tw-text-slate-500">Auditor snapshot</dt>
            <dd class="tw-mt-1 tw-text-slate-900"><?php echo html_escape($assignment->auditor_name . ' — ' . $assignment->auditor_email); ?></dd>
          </div>
          <div>
            <dt class="tw-font-semibold tw-text-slate-500">Auditee snapshot</dt>
            <dd class="tw-mt-1 tw-text-slate-900"><?php echo html_escape($assignment->auditee_name . ' — ' . $assignment->auditee_email); ?></dd>
          </div>
        </dl>
      </section>
    </div>

    <section class="tw-mb-6 tw-rounded-2xl tw-border tw-border-blue-200 tw-bg-blue-50 tw-p-5 tw-text-sm tw-leading-6 tw-text-blue-800">
      Snapshot ini read-only. Isi indikator, kebutuhan bukti, dan rubrik tampil sesuai salinan saat penugasan dibuat.
    </section>

    <div class="tw-mb-4">
      <h2 class="tw-text-xl tw-font-bold tw-text-slate-950">Item snapshot</h2>
       <p class="tw-mt-1 tw-text-sm tw-text-slate-500">Indikator yang dikunci untuk penugasan ini.</p>
    </div>

    <?php if (empty($items)): ?>
      <div class="tw-rounded-2xl tw-border tw-border-dashed tw-border-slate-300 tw-p-8 tw-text-center tw-text-sm tw-text-slate-500">Snapshot item belum tersedia.</div>
    <?php else: foreach ($items as $item): ?>
      <article class="tw-mb-4 tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm">
        <h3 class="tw-text-base tw-font-bold tw-text-slate-950"><?php echo html_escape((string) $item->display_order . '. ' . $item->indicator_code . ' — ' . $item->indicator_title); ?></h3>
        <h4 class="tw-mt-5 tw-label">Kebutuhan bukti</h4>
        <p class="tw-mt-2 tw-whitespace-pre-line tw-text-sm tw-leading-6 tw-text-slate-700"><?php echo nl2br(html_escape($item->evidence_instruction)); ?></p>
        <h4 class="tw-mt-6 tw-label">Rubrik snapshot</h4>
        <ul class="tw-mt-2 tw-grid tw-gap-2 sm:tw-grid-cols-2">
          <?php foreach (isset($rubrics_by_item[$item->id]) ? $rubrics_by_item[$item->id] : [] as $rubric): ?>
            <li class="tw-rounded-lg tw-bg-slate-50 tw-p-3 tw-text-sm tw-text-slate-700">
              <strong class="tw-text-slate-950"><?php echo html_escape((string) $rubric->score); ?></strong> — <?php echo html_escape($rubric->descriptor); ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </article>
    <?php endforeach; endif; ?>
  </div>
</main>
<?php include APPPATH . 'views/layouts/footer.php'; ?>
