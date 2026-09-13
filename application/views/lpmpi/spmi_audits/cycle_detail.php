<?php defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'views/layouts/header.php';
include APPPATH . 'views/layouts/sidebar.php';

$academic_year = isset($cycle->academic_year) ? trim((string) $cycle->academic_year) : '';
$semester = isset($cycle->semester) ? strtolower(trim((string) $cycle->semester)) : '';
$academic_period = ($academic_year !== '' && in_array($semester, ['ganjil', 'genap'], TRUE)) ? $academic_year . ' — ' . ucfirst($semester) : 'Periode akademik belum dicatat';
$mutable = $cycle->state === 'draft';
?>
<main id="audits-root" class="tw-min-w-0 tw-flex-1 tw-p-4 md:tw-p-8">
  <div class="tw-mx-auto tw-max-w-7xl">
    <div class="tw-mb-6 tw-flex tw-flex-col tw-gap-4 md:tw-flex-row md:tw-items-end md:tw-justify-between">
      <div>
        <p class="tw-mb-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-[0.2em] tw-text-slate-500">Siklus SPMI</p>
        <h1 class="tw-text-3xl tw-font-bold tw-tracking-tight tw-text-slate-950"><?php echo html_escape($cycle->cycle_code . ' — ' . $cycle->title); ?></h1>
        <p class="tw-mt-2 tw-text-sm tw-text-slate-500">Periode akademik: <?php echo html_escape($academic_year !== '' && in_array($semester, ['ganjil', 'genap'], TRUE) ? $academic_year . ' — ' . ucfirst($semester) : ($academic_period ?? 'Periode akademik belum dicatat')); ?></p>
      </div>
      <?php if ($mutable): ?>
        <div class="tw-flex tw-flex-wrap tw-gap-2">
          <a class="tw-button-secondary" href="<?php echo site_url('lpmpi/spmi-audits/cycle/edit/' . (int) $cycle->id); ?>">Edit</a>
          <a class="tw-button-primary" href="<?php echo site_url('lpmpi/spmi-audits/assignment/create/' . (int) $cycle->id); ?>">Tambah penugasan</a>
        </div>
      <?php endif; ?>
    </div>

    <!-- Cycle Detail Tabs -->
    <nav class="tw-mb-6 tw-flex tw-gap-2 tw-border-b tw-border-slate-200 tw-overflow-x-auto" aria-label="Cycle sections" role="tablist">
      <a
        id="tab-overview"
        class="cycle-tab-btn is-active tw-font-bold"
        href="#overview"
        role="tab"
        data-cycle-tab="overview"
        aria-controls="panel-overview"
        aria-selected="true"
        tabindex="0"
      >
        <svg class="tw-h-4 tw-w-4 tw-flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
        <span>Overview</span>
      </a>
      <a
        id="tab-assignments"
        class="cycle-tab-btn tw-font-semibold"
        href="#assignments"
        role="tab"
        data-cycle-tab="assignments"
        aria-controls="panel-assignments"
        aria-selected="false"
        tabindex="-1"
      >
        <svg class="tw-h-4 tw-w-4 tw-flex-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Assignments (<?php echo count($assignments); ?>)</span>
      </a>
    </nav>

    <!-- Tab 1: Overview Panel -->
    <div id="panel-overview" class="cycle-tab-pane" role="tabpanel" aria-labelledby="tab-overview">
      <div class="tw-grid tw-gap-5 lg:tw-grid-cols-[minmax(0,1fr)_300px]">
        <section id="overview" class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-sm">
          <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3">
            <h2 class="tw-text-lg tw-font-bold tw-text-slate-950">Overview</h2>
            <span class="tw-rounded-full tw-bg-slate-100 tw-px-3 tw-py-1 tw-text-xs tw-font-bold tw-text-slate-700"><?php echo html_escape($cycle->state); ?></span>
          </div>
          <p class="tw-mt-4 tw-text-sm tw-text-slate-600">Periode: <?php echo html_escape($cycle->start_date . ' — ' . $cycle->end_date); ?></p>
          <p class="tw-mt-4 tw-whitespace-pre-line tw-text-sm tw-leading-6 tw-text-slate-600"><?php echo nl2br(html_escape($cycle->description ?: 'Deskripsi belum diisi.')); ?></p>
          <?php if (!$mutable): ?>
            <div class="tw-mt-6 tw-rounded-lg tw-border tw-border-blue-200 tw-bg-blue-50 tw-p-4 tw-text-sm tw-text-blue-800">Siklus configured atau closed bersifat frozen dan hanya-baca. Snapshot tidak berubah mengikuti sumber.</div>
          <?php else: ?>
            <div class="tw-mt-6 tw-rounded-lg tw-border tw-border-amber-200 tw-bg-amber-50 tw-p-4 tw-text-sm tw-text-amber-800">Siklus draft: metadata dan penugasan masih dapat diubah.</div>
          <?php endif; ?>
        </section>

        <aside class="tw-rounded-2xl tw-border tw-border-slate-200 tw-bg-slate-950 tw-p-6 tw-text-white">
          <p class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest tw-text-slate-400">Snapshot registry</p>
          <p class="tw-mt-3 tw-text-4xl tw-font-bold"><?php echo count($assignments); ?></p>
          <p class="tw-mt-1 tw-text-sm tw-text-slate-300">penugasan tersimpan</p>
        </aside>
      </div>

      <?php if ($mutable): ?>
        <div class="tw-mt-5 tw-flex tw-flex-wrap tw-gap-2">
          <?php foreach (['configured' => 'Tandai configured', 'closed' => 'Tutup siklus'] as $state => $label): ?>
            <?php echo form_open('lpmpi/spmi-audits/cycle/transition/' . (int) $cycle->id, ['class' => 'tw-inline']); ?>
            <input type="hidden" name="state" value="<?php echo html_escape($state); ?>">
            <button class="tw-button-secondary" type="submit"><?php echo html_escape($label); ?></button>
            </form>
          <?php endforeach; ?>
        </div>
      <?php elseif ($cycle->state === 'configured'): ?>
        <?php echo form_open('lpmpi/spmi-audits/cycle/transition/' . (int) $cycle->id, ['class' => 'tw-mt-5']); ?>
        <input type="hidden" name="state" value="draft">
        <button class="tw-button-secondary" type="submit">Kembalikan ke draft</button>
        </form>
      <?php endif; ?>
    </div>

    <!-- Tab 2: Assignments Panel -->
    <div id="panel-assignments" class="cycle-tab-pane tw-hidden" role="tabpanel" aria-labelledby="tab-assignments">
      <section id="assignments" class="tw-mt-0">
        <div class="tw-mb-4">
          <h2 class="tw-text-xl tw-font-bold tw-text-slate-950">Assignments</h2>
          <p class="tw-mt-1 tw-text-sm tw-text-slate-500">Identity snapshot dari paket, auditor, dan auditee.</p>
        </div>
        <?php if (empty($assignments)): ?>
          <div class="tw-rounded-2xl tw-border tw-border-dashed tw-border-slate-300 tw-p-8 tw-text-center tw-text-sm tw-text-slate-500">Belum ada snapshot penugasan.</div>
        <?php else: ?>
          <div class="tw-grid tw-gap-3">
            <?php foreach ($assignments as $assignment): ?>
              <article class="tw-rounded-xl tw-border tw-border-slate-200 tw-bg-white tw-p-5">
                <div class="tw-flex tw-flex-col tw-gap-3 md:tw-flex-row md:tw-items-start md:tw-justify-between">
                  <div>
                    <p class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-slate-500">Paket snapshot</p>
                    <h3 class="tw-mt-1 tw-font-bold tw-text-slate-950"><?php echo html_escape($assignment->source_package_code . ' — ' . $assignment->source_package_title); ?></h3>
                    <p class="tw-mt-3 tw-text-sm tw-text-slate-600">Auditor: <?php echo html_escape($assignment->auditor_name); ?> · Auditee: <?php echo html_escape($assignment->auditee_name); ?></p>
                  </div>
                  <div class="tw-flex tw-flex-wrap tw-gap-3">
                    <a class="tw-button-secondary" href="<?php echo site_url('lpmpi/spmi-audits/assignment/detail/' . (int) $assignment->id); ?>">Lihat snapshot</a>
                    <?php if ($mutable): ?>
                      <?php echo form_open('lpmpi/spmi-audits/assignment/delete/' . (int) $assignment->id, ['class' => 'tw-inline', 'onsubmit' => "return confirm('Hapus snapshot penugasan ini?');"]); ?>
                      <button class="tw-button-danger" type="submit">Hapus</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </div>
</main>

<script>
(function () {
  function getHashTab() {
    var hash = (window.location.hash || '').toLowerCase();
    return hash === '#assignments' ? 'assignments' : 'overview';
  }

  function activateTab(target, updateHistory) {
    var validTarget = target === 'assignments' ? 'assignments' : 'overview';
    var tabs = document.querySelectorAll('[data-cycle-tab]');
    var panes = document.querySelectorAll('.cycle-tab-pane');

    tabs.forEach(function (tab) {
      var isCurrent = tab.getAttribute('data-cycle-tab') === validTarget;
      tab.setAttribute('aria-selected', isCurrent ? 'true' : 'false');
      tab.setAttribute('tabindex', isCurrent ? '0' : '-1');
      tab.classList.toggle('is-active', isCurrent);
      tab.classList.toggle('tw-font-bold', isCurrent);
      tab.classList.toggle('tw-font-semibold', !isCurrent);
    });

    panes.forEach(function (pane) {
      if (pane.id === 'panel-' + validTarget) {
        pane.classList.remove('tw-hidden');
      } else {
        pane.classList.add('tw-hidden');
      }
    });

    if (updateHistory) {
      var newHash = '#' + validTarget;
      if (window.location.hash !== newHash) {
        if (window.history && window.history.pushState) {
          window.history.pushState(null, '', newHash);
        } else {
          window.location.hash = newHash;
        }
      }
    }
  }

  function initTabs() {
    var tabs = document.querySelectorAll('[data-cycle-tab]');
    if (!tabs.length) return;

    tabs.forEach(function (tab, idx) {
      tab.addEventListener('click', function (e) {
        e.preventDefault();
        var target = tab.getAttribute('data-cycle-tab');
        activateTab(target, true);
      });

      tab.addEventListener('keydown', function (e) {
        var targetIdx = null;
        if (e.key === 'ArrowRight') {
          targetIdx = (idx + 1) % tabs.length;
        } else if (e.key === 'ArrowLeft') {
          targetIdx = (idx - 1 + tabs.length) % tabs.length;
        } else if (e.key === 'Home') {
          targetIdx = 0;
        } else if (e.key === 'End') {
          targetIdx = tabs.length - 1;
        }
        if (targetIdx !== null) {
          e.preventDefault();
          tabs[targetIdx].focus();
          tabs[targetIdx].click();
        }
      });
    });

    window.addEventListener('hashchange', function () {
      activateTab(getHashTab(), false);
    });

    window.addEventListener('popstate', function () {
      activateTab(getHashTab(), false);
    });

    // Initial sync on load
    activateTab(getHashTab(), false);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTabs);
  } else {
    initTabs();
  }
})();
</script>

<?php include APPPATH . 'views/layouts/footer.php'; ?>
