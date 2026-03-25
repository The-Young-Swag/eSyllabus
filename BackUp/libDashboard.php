<!-- =====================================================================
     LIBRARY DASHBOARD — FRONTEND
     Changes:
       1. Date-range pickers moved to the Daily Logs card header
          and now drive ALL data (KPI, logs, trend, activity).
       2. Force-Checkout button + confirmation modal added.
          Only processes records with no checkout_time from
          days BEFORE today (ignores today completely).
===================================================================== -->

<div class="container-fluid px-4 py-4">
<style>
  @keyframes ldot {
    0%,100% { opacity:1; }
    50%      { opacity:.35; }
  }
  #toggleIdVisibility:hover { color:#064e3b !important; }
  #logForm button[type=submit]:hover {
    background:#047857 !important;
    box-shadow:0 5px 18px rgba(6,78,59,.25) !important;
  }
  /* Force-checkout button */
  #btnForceCheckout {
    font-size:.72rem;
    border-radius:20px;
    transition:background .18s, box-shadow .18s;
  }
  #btnForceCheckout:hover:not(:disabled) {
    background:#b45309 !important;
    box-shadow:0 4px 12px rgba(180,83,9,.22);
  }
  /* Modal confirm button */
  #btnConfirmCheckout {
    transition:background .18s;
  }
  #btnConfirmCheckout:hover {
    background:#b45309 !important;
  }
</style>

<!-- ================================================================
     FORCE-CHECKOUT CONFIRMATION MODAL
     Shows a count of affected records before the user commits.
================================================================ -->
<div class="modal fade" id="forceCheckoutModal" tabindex="-1" aria-labelledby="forceCheckoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h6 class="modal-title fw-bold text-dark" id="forceCheckoutModalLabel">
          <i class="fas fa-clock-rotate-left me-2 text-warning"></i>Force Checkout — Previous Days
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2">
        <!-- Loading state -->
        <div id="fcoLoading" class="text-center py-3 text-muted" style="font-size:.82rem;">
          <span class="spinner-border spinner-border-sm me-2"></span>Checking pending records…
        </div>
        <!-- Info state (populated after count fetch) -->
        <div id="fcoInfo" style="display:none;">
          <div class="alert alert-warning border-0 rounded-3 mb-3" style="font-size:.82rem;background:rgba(245,158,11,.1);">
            <i class="fas fa-triangle-exclamation me-2 text-warning"></i>
            <span id="fcoMessage"></span>
          </div>
          <p class="text-muted mb-0" style="font-size:.78rem;">
            Each record will have its <code>checkout_time</code> set to
            <strong>7:00 PM</strong> of its own check-in date.
            <br>Records from <strong>today</strong> are never affected.
          </p>
        </div>
        <!-- Empty state -->
        <div id="fcoEmpty" style="display:none;" class="text-center py-2 text-muted" style="font-size:.82rem;">
          <i class="fas fa-circle-check text-success me-1"></i>
          No unchecked records found from previous days.
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="btnConfirmCheckout"
                class="btn btn-sm text-white fw-semibold px-4"
                style="background:#d97706;display:none;">
          <i class="fas fa-check me-1"></i>Confirm Checkout
        </button>
      </div>
    </div>
  </div>
</div>


<!-- ================================================================
     HEADER
================================================================ -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0 text-dark">Library Dashboard</h5>
        <small class="text-muted">Overview of library activities and trends</small>
    </div>
    <div class="d-flex align-items-center mb-1" style="gap:9px;">
        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;
                     background:#10b981;flex-shrink:0;
                     animation:ldot 2.2s ease-in-out infinite;"></span>
        <span class="fw-bold text-dark" style="font-size:1rem;letter-spacing:-.2px;">Live</span>
    </div>
</div>

<!-- ================================================================
     KPI CARDS
================================================================ -->
<div id="kpiContainer" class="row g-3 mb-4">
    <!-- Dynamically rendered by JS via loadSections() + loadKPI() -->
</div>


<!-- ================================================================
     DAILY LOGS TABLE
     • Date-range pickers here now drive ALL data.
     • "Force Checkout" trigger button in this header.
================================================================ -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4">

        <!-- Row 1: title + controls -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

            <!-- Title -->
            <div>
                <h6 class="mb-0 fw-bold text-dark">Daily Logs</h6>
                <small class="text-muted">Check-in / check-out records</small>
            </div>

            <!-- Right-side controls -->
            <div class="d-flex flex-wrap align-items-center gap-2">

                <!-- ── Global date-range pickers ── -->
                <div class="input-group input-group-sm" style="width:130px;">
                    <span class="input-group-text bg-light border-end-0 px-2">
                        <i class="fas fa-calendar text-muted" style="font-size:.65rem;"></i>
                    </span>
                    <input type="date" id="trendStartDate"
                           class="form-control border-start-0 ps-0"
                           style="font-size:.75rem;">
                </div>
                <span class="text-muted small">—</span>
                <div class="input-group input-group-sm" style="width:130px;">
                    <span class="input-group-text bg-light border-end-0 px-2">
                        <i class="fas fa-calendar-check text-muted" style="font-size:.65rem;"></i>
                    </span>
                    <input type="date" id="trendEndDate"
                           class="form-control border-start-0 ps-0"
                           style="font-size:.75rem;">
                </div>

                <!-- ── Section filter ── -->
                <select id="sectionFilter" class="form-select form-select-sm" style="font-size:.75rem;width:auto;">
                    <option value="">All Sections</option>
                </select>

                <!-- ── Date label badge ── -->
                <span id="dateBadge"
                      class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3"
                      style="font-size:.72rem;">Today</span>

                <!-- ── Force Checkout trigger ── -->
                <button id="btnForceCheckout"
                        class="btn btn-sm text-white fw-semibold px-3"
                        style="background:#d97706;"
                        title="Check out users who never checked out on previous days">
                    <i class="fas fa-clock-rotate-left me-1"></i>Force Checkout
                </button>

            </div>
        </div>

    </div>

    <div class="card-body p-0" style="min-height:285px;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                <thead class="table-light border-bottom">
                    <tr>
                        <th class="px-4 py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">ID No.</th>
                        <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">College</th>
                        <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Course</th>
                        <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Library</th>
                        <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Check-In</th>
                        <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Check-Out</th>
                        <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Status</th>
                    </tr>
                </thead>
                <tbody id="dailyLogs">
                    <tr><td colspan="7" class="text-center text-muted py-4">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination footer -->
    <div class="card-footer bg-white border-top py-3 px-4">
        <div class="d-flex flex-column align-items-center gap-1" id="logsPageInfo"></div>
    </div>
</div>


<!-- ================================================================
     CHARTS ROW
================================================================ -->
<div class="row g-3 mb-3">

    <!-- Usage Trend -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom px-4 py-3">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-chart-bar me-2 text-success"></i>Usage Trend
                </h6>
                <small class="text-muted">Monthly visit counts — driven by date range above</small>
            </div>
            <div class="card-body px-4 pb-4 pt-3">
                <div style="position:relative;height:220px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- College & Course Activity -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom px-4 py-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-layer-group me-2 text-primary"></i>College &amp; Course Activity
                        </h6>
                        <small class="text-muted">Visit distribution by college &amp; course</small>
                    </div>
                    <span id="chartSectionBadge"
                          class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 ms-2"
                          style="font-size:.72rem;white-space:nowrap;">All Sections</span>
                </div>
            </div>
            <div class="card-body px-3 py-3">
                <div style="position:relative;" id="activityChartWrap">
                    <canvas id="activityChart"></canvas>
                </div>
                <div id="activityEmpty" class="text-center text-muted py-4"
                     style="font-size:.8rem;display:none;">
                    No activity for selected range.
                </div>
            </div>
        </div>
    </div>

</div><!-- /charts row -->
</div><!-- /container -->


<!-- ================================================================
     JAVASCRIPT
================================================================ -->
<script>
$(document).ready(function () {

  /* ──────────────────────────────────────────────
     CONFIG
  ────────────────────────────────────────────── */
  const BACKEND_URL = "backend/bk_LibraryMenu/bk_libDashboard.php";
  const TODAY_STR   = new Date().toISOString().split('T')[0];

  const PALETTE = [
    'rgba(59,130,246,0.85)',  'rgba(16,185,129,0.85)', 'rgba(245,158,11,0.85)',
    'rgba(239,68,68,0.85)',   'rgba(139,92,246,0.85)', 'rgba(20,184,166,0.85)',
    'rgba(249,115,22,0.85)',  'rgba(236,72,153,0.85)', 'rgba(34,197,94,0.85)',
    'rgba(99,102,241,0.85)',
  ];

  const TOOLTIP_DEFAULTS = {
    backgroundColor: 'rgba(15,23,42,0.92)',
    titleColor:      '#f8fafc',
    bodyColor:       '#94a3b8',
    borderColor:     'rgba(148,163,184,0.15)',
    borderWidth:     1,
    padding:         10,
    cornerRadius:    6,
  };

  let trendChartInst    = null;
  let activityChartInst = null;
  let sectionFilterPopulated = false;

  /* ──────────────────────────────────────────────
     INIT — set defaults and load everything
  ────────────────────────────────────────────── */
  $('#trendStartDate').val(TODAY_STR);
  $('#trendEndDate').val(TODAY_STR);
  updateDateBadge();

  loadSections();   // renders KPI cards first, then loadKPI fires inside
  loadLogs();
  loadTrend();
  loadCollegeCourseActivity();


  /* ──────────────────────────────────────────────
     HELPER — read current date range
  ────────────────────────────────────────────── */
  function getDateRange() {
    return {
      startDate: $('#trendStartDate').val() || TODAY_STR,
      endDate:   $('#trendEndDate').val()   || TODAY_STR,
    };
  }

  function updateDateBadge() {
    const { startDate, endDate } = getDateRange();
    if (startDate === TODAY_STR && endDate === TODAY_STR) {
      $('#dateBadge').text('Today');
    } else if (startDate === endDate) {
      $('#dateBadge').text(formatDate(startDate));
    } else {
      $('#dateBadge').text(formatDate(startDate) + ' – ' + formatDate(endDate));
    }
  }

  function formatDate(iso) {
    const d = new Date(iso + 'T00:00:00');
    return d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
  }

  function isToday() {
    const { startDate, endDate } = getDateRange();
    return startDate === TODAY_STR && endDate === TODAY_STR;
  }


  /* ──────────────────────────────────────────────
     SECTIONS — build KPI card shells
  ────────────────────────────────────────────── */
  function loadSections() {
    $.ajax({
      type: 'POST', url: BACKEND_URL, data: { request: 'sections' }, dataType: 'json',
      success: function (sections) {
        renderSectionCards(sections);
        loadKPI();   // populate counts after cards exist
      },
    });
  }

  function renderSectionCards(sections) {
    const colors = [
      { border: '#10b981', text: 'text-success' },
      { border: '#3b82f6', text: 'text-primary' },
      { border: '#f59e0b', text: 'text-warning' },
      { border: '#ef4444', text: 'text-danger'  },
    ];
    let html = '';
    sections.forEach((sec, i) => {
      const c = colors[i % colors.length];
      html += `
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm h-100" style="border-top:3px solid ${c.border};">
            <div class="card-body p-3">
              <span class="text-muted fw-semibold text-uppercase d-block mb-2"
                    style="letter-spacing:.05em;font-size:.7rem;">${escHtml(sec.SectionName)}</span>
              <h3 class="fw-bold ${c.text} mb-0 kpi-count"
                  data-section-code="${escHtml(sec.SectionCode)}">—</h3>
              <small class="text-muted kpi-label">active visits</small>
            </div>
          </div>
        </div>`;
    });
    $('#kpiContainer').html(html);
    populateSectionFilter(sections);
  }


  /* ──────────────────────────────────────────────
     KPI — respects date range
     Label: "active visits" only when range = today
  ────────────────────────────────────────────── */
  function loadKPI() {
    const { startDate, endDate } = getDateRange();
    $.ajax({
      type: 'POST', url: BACKEND_URL,
      data: { request: 'kpiData', startDate, endDate },
      dataType: 'json',
      success: function (sections) {
        $('.kpi-count').text('0');
        const label = isToday() ? 'active visits' : 'total visits';
        $('.kpi-label').text(label);
        sections.forEach(function (s) {
          const code = (s.SectionCode ?? '').trim();
          $(`.kpi-count[data-section-code="${code}"]`).text(s.total ?? 0);
        });
      },
    });
  }


  /* ──────────────────────────────────────────────
     SECTION FILTER — populate once
  ────────────────────────────────────────────── */
  function populateSectionFilter(sections) {
    if (sectionFilterPopulated) return;
    sectionFilterPopulated = true;
    const $f = $('#sectionFilter');
    $f.find('option:not(:first)').remove();
    const seen = new Set();
    sections.forEach(s => {
      const id = String(s.SectionID ?? '').trim();
      if (!id || seen.has(id)) return;
      seen.add(id);
      $f.append(`<option value="${escHtml(id)}">${escHtml(s.SectionName)}</option>`);
    });
  }


  /* ──────────────────────────────────────────────
     DAILY LOGS + PAGINATION
  ────────────────────────────────────────────── */
  function loadLogs(page, sectionID) {
    page      = page      ?? 1;
    sectionID = sectionID ?? $('#sectionFilter').val();
    const { startDate, endDate } = getDateRange();

    $.ajax({
      type: 'POST', url: BACKEND_URL,
      data: { request: 'dailyLogs', page, sectionID, startDate, endDate },
      success: function (raw) {
        const res = JSON.parse(raw);
        $('#dailyLogs').html(res.rows);
        renderPagination(res.totalRows, res.totalPages, res.currentPage, res.limit, sectionID);
      },
    });
  }

  function renderPagination(totalRows, totalPages, current, limit, sectionID) {
    const $wrap = $('#logsPageInfo').empty();
    if (totalPages <= 1 && totalRows === 0) return;

    const from = ((current - 1) * limit) + 1;
    const to   = Math.min(current * limit, totalRows);
    $wrap.append(`<small class="text-muted">Showing ${from}–${to} of ${totalRows} records</small>`);
    if (totalPages <= 1) return;

    const $ul    = $('<ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center"></ul>');
    const isFirst = current === 1;
    const isLast  = current === totalPages;

    const addItem = (label, page, disabled, active) => {
      const $li = $(`<li class="page-item ${disabled?'disabled':''} ${active?'active':''}"></li>`);
      const $a  = $(`<a class="page-link" href="#">${label}</a>`);
      if (!disabled) $a.data('page', page);
      $li.append($a);
      $ul.append($li);
    };

    addItem('«', 1,           isFirst, false);
    addItem('‹', current - 1, isFirst, false);
    const WINDOW = 5;
    const start  = Math.max(1, Math.min(current - Math.floor(WINDOW/2), totalPages - WINDOW + 1));
    const end    = Math.min(totalPages, start + WINDOW - 1);
    for (let p = start; p <= end; p++) addItem(p, p, false, p === current);
    addItem('›', current + 1, isLast, false);
    addItem('»', totalPages,  isLast, false);

    $ul.on('click', '.page-link', function (e) {
      e.preventDefault();
      const p = $(this).data('page');
      if (p) loadLogs(p, sectionID);
    });
    $wrap.append($ul);
  }


  /* ──────────────────────────────────────────────
     USAGE TREND — driven by global date range
  ────────────────────────────────────────────── */
  function loadTrend(sectionID) {
    sectionID = sectionID ?? $('#sectionFilter').val();
    const { startDate, endDate } = getDateRange();

    $.ajax({
      type: 'POST', url: BACKEND_URL,
      data: { request: 'monthlyTrend', sectionID, startDate, endDate },
      dataType: 'json',
      success: rows => renderTrend(rows),
      error:   ()   => renderTrend([]),
    });
  }

  function renderTrend(rows) {
    const canvas = document.getElementById('trendChart');
    if (!canvas) return;
    if (trendChartInst) { trendChartInst.destroy(); trendChartInst = null; }

    if (!rows || !rows.length) {
      canvas.style.display = 'none';
      const $wrap = $('#trendChart').parent();
      $wrap.find('.trend-empty').remove();
      $wrap.append('<div class="trend-empty text-center text-muted py-4" style="font-size:.8rem;">No data for selected range.</div>');
      return;
    }
    canvas.style.display = '';
    $('#trendChart').parent().find('.trend-empty').remove();

    const labels  = rows.map(r => r.month);
    const values  = rows.map(r => parseInt(r.total));
    const lastIdx = rows.length - 1;

    const bgColors     = values.map((_,i) => i===lastIdx ? 'rgba(16,185,129,0.88)' : 'rgba(16,185,129,0.35)');
    const borderColors = values.map((_,i) => i===lastIdx ? 'rgba(16,185,129,1)'    : 'rgba(16,185,129,0.6)');

    trendChartInst = new Chart(canvas, {
      type: 'bar',
      data: { labels, datasets: [{
        label: 'Visits', data: values,
        backgroundColor: bgColors, borderColor: borderColors,
        borderWidth: 1.5, borderRadius: 4, borderSkipped: false,
      }]},
      options: {
        responsive: true, maintainAspectRatio: false,
        animation: { duration: 500, easing: 'easeOutQuart' },
        plugins: {
          legend: { display: false },
          tooltip: { ...TOOLTIP_DEFAULTS,
            callbacks: { label: ctx => `  Visits: ${ctx.parsed.y.toLocaleString()}` },
          },
        },
        scales: {
          x: { grid: { display: false }, ticks: { color: '#6b7280', font: { size: 11 } } },
          y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' },
               ticks: { color: '#6b7280', font: { size: 10 }, precision: 0,
                        callback: v => Number.isInteger(v) ? v : '' } },
        },
        layout: { padding: { top: 8 } },
      },
    });
  }


  /* ──────────────────────────────────────────────
     COLLEGE & COURSE ACTIVITY — driven by global date range
  ────────────────────────────────────────────── */
  function loadCollegeCourseActivity(sectionID) {
    sectionID = sectionID ?? $('#sectionFilter').val();
    const { startDate, endDate } = getDateRange();
    $('#chartSectionBadge').text(sectionID ? $('#sectionFilter option:selected').text() : 'All Sections');
    $('#activityEmpty').hide();

    $.ajax({
      type: 'POST', url: BACKEND_URL,
      data: { request: 'collegeCourseActivity', sectionID, startDate, endDate },
      dataType: 'json',
      success: data => renderActivityChart(data),
      error:   ()   => renderActivityChart([]),
    });
  }

  function renderActivityChart(colleges) {
    const canvas = document.getElementById('activityChart');
    if (!canvas) return;
    if (activityChartInst) { activityChartInst.destroy(); activityChartInst = null; }

    if (!colleges || !colleges.length) {
      canvas.style.display = 'none';
      $('#activityEmpty').show();
      return;
    }
    canvas.style.display = '';
    $('#activityEmpty').hide();

    const allCourses = [];
    const courseSet  = new Set();
    colleges.forEach(col => col.courses.forEach(cr => {
      if (!courseSet.has(cr.course)) { courseSet.add(cr.course); allCourses.push(cr.course); }
    }));

    const courseColor = {};
    allCourses.forEach((c, i) => { courseColor[c] = PALETTE[i % PALETTE.length]; });

    const labels   = colleges.map(c => c.college || '—');
    const datasets = allCourses.map(course => ({
      label:           course,
      data:            colleges.map(col => { const f = col.courses.find(cr => cr.course === course); return f ? f.total : 0; }),
      backgroundColor: courseColor[course],
      borderColor:     courseColor[course].replace('0.85','1'),
      borderWidth:     0, borderRadius: 3, borderSkipped: false,
    }));

    const chartH = Math.max(200, colleges.length * 42 + 60);
    canvas.parentElement.style.height = chartH + 'px';

    activityChartInst = new Chart(canvas, {
      type: 'bar',
      data: { labels, datasets },
      options: {
        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
        animation: { duration: 500, easing: 'easeOutQuart' },
        plugins: {
          legend: { position: 'bottom', labels: { color:'#374151', font:{size:11}, padding:12,
                    usePointStyle:true, pointStyle:'circle', boxWidth:8, boxHeight:8 } },
          tooltip: { ...TOOLTIP_DEFAULTS, mode:'index', intersect:false,
            callbacks: {
              title:  items => items[0]?.label ?? '',
              label:  ctx   => ctx.parsed.x > 0 ? `  ${ctx.dataset.label}: ${ctx.parsed.x}` : null,
              footer: items => { const t = items.reduce((s,i)=>s+(i.parsed.x||0),0); return `  Total: ${t}`; },
            },
          },
        },
        scales: {
          x: { stacked:true, beginAtZero:true, grid:{color:'rgba(0,0,0,0.05)'},
               ticks:{color:'#6b7280',font:{size:10},precision:0,callback:v=>Number.isInteger(v)?v:''},
               title:{display:true,text:'No. of Visits',color:'#9ca3af',font:{size:10},padding:{top:6}} },
          y: { stacked:true, grid:{display:false}, ticks:{color:'#374151',font:{size:12}} },
        },
        layout: { padding: { right: 8 } },
      },
    });
  }


  /* ──────────────────────────────────────────────
     FORCE CHECKOUT — modal flow
     Step 1: open modal → fetch pending count
     Step 2: user confirms → run update → reload all
  ────────────────────────────────────────────── */
  $('#btnForceCheckout').on('click', function () {
    // Reset modal to loading state
    $('#fcoLoading').show();
    $('#fcoInfo').hide();
    $('#fcoEmpty').hide();
    $('#btnConfirmCheckout').hide();

    const modal = new bootstrap.Modal(document.getElementById('forceCheckoutModal'));
    modal.show();

    // Fetch count of pending records from previous days
    $.ajax({
      type: 'POST', url: BACKEND_URL,
      data: { request: 'countPendingCheckout' },
      dataType: 'json',
      success: function (res) {
        $('#fcoLoading').hide();
        const count = parseInt(res.count ?? 0);
        if (count === 0) {
          $('#fcoEmpty').show();
        } else {
          $('#fcoMessage').html(
            `Found <strong>${count}</strong> record${count !== 1 ? 's' : ''} with no check-out
             from <strong>previous days</strong>. They will each be checked out at
             <strong>7:00 PM</strong> of their respective check-in date.`
          );
          $('#fcoInfo').show();
          $('#btnConfirmCheckout').show();
        }
      },
      error: function () {
        $('#fcoLoading').hide();
        $('#fcoEmpty').text('Could not fetch pending records. Please try again.').show();
      },
    });
  });

  $('#btnConfirmCheckout').on('click', function () {
    const $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing…');

    $.ajax({
      type: 'POST', url: BACKEND_URL,
      data: { request: 'forceCheckout' },
      dataType: 'json',
      success: function (res) {
        bootstrap.Modal.getInstance(document.getElementById('forceCheckoutModal')).hide();
        reloadAll();   // refresh everything after checkout
        // Brief toast-style confirmation (Bootstrap toast or alert)
        showToast(`Force checkout complete — ${res.affected} record(s) updated.`, 'success');
      },
      error: function () {
        $btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i>Confirm Checkout');
        showToast('An error occurred. Please try again.', 'danger');
      },
    });
  });

  /* Simple inline toast helper */
  function showToast(msg, type) {
    const id  = 'toast_' + Date.now();
    const $t  = $(`
      <div id="${id}" class="toast align-items-center text-white border-0 show"
           role="alert" style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;
                               background:${type==='success'?'#059669':'#dc2626'};">
        <div class="d-flex">
          <div class="toast-body" style="font-size:.82rem;">${escHtml(msg)}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto"
                  data-bs-dismiss="toast"></button>
        </div>
      </div>`);
    $('body').append($t);
    setTimeout(() => $t.remove(), 4000);
  }


  /* ──────────────────────────────────────────────
     RELOAD ALL — one call to refresh every widget
  ────────────────────────────────────────────── */
  function reloadAll() {
    const sid = $('#sectionFilter').val();
    loadKPI();
    loadLogs(1, sid);
    loadTrend(sid);
    loadCollegeCourseActivity(sid);
  }


  /* ──────────────────────────────────────────────
     EVENTS
  ────────────────────────────────────────────── */

  // Section dropdown
  $('#sectionFilter').on('change', function () {
    const sid = $(this).val();
    loadLogs(1, sid);
    loadCollegeCourseActivity(sid);
    loadTrend(sid);
    // KPI doesn't need sectionID (shows all sections)
  });

  // Date-range pickers — reload everything when both are valid
  $('#trendStartDate, #trendEndDate').on('change', function () {
    const { startDate, endDate } = getDateRange();
    if (startDate && endDate && startDate <= endDate) {
      updateDateBadge();
      reloadAll();
    }
  });


  /* ──────────────────────────────────────────────
     UTIL
  ────────────────────────────────────────────── */
  function escHtml(str) {
    return $('<div>').text(str).html();
  }

});
</script>