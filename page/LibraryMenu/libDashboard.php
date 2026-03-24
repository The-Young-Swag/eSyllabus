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
</style>
    <!-- Header -->
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

    <!-- KPI Cards -->
<div id="kpiContainer" class="row g-3 mb-4">
<?php foreach ($sections as $sec): 
    $name = htmlspecialchars($sec['SectionName']);
    $code = htmlspecialchars($sec['SectionID']); // or SectionCode if you have one

    // Optional: rotate colors (clean + scalable)
    $colors = [
        ['border' => '#10b981', 'text' => 'text-success'],
        ['border' => '#3b82f6', 'text' => 'text-primary'],
        ['border' => '#f59e0b', 'text' => 'text-warning'],
        ['border' => '#ef4444', 'text' => 'text-danger'],
    ];

    static $i = 0;
    $color = $colors[$i++ % count($colors)];
?>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-top:3px solid <?= $color['border'] ?>;">
            <div class="card-body p-3">
                <span class="text-muted fw-semibold text-uppercase d-block mb-2"
                      style="letter-spacing:.05em;font-size:.7rem;">
                      <?= $name ?>
                </span>

                <h3 class="fw-bold <?= $color['text'] ?> mb-0 kpi-count"
                    data-section-code="<?= $code ?>">
                    —
                </h3>

                <small class="text-muted">active visits</small>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>


    <!-- Daily Logs Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
            <div>
                <h6 class="mb-0 fw-bold text-dark">Daily Logs</h6>
                <small class="text-muted">Real-time check-in / check-out records</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <select id="sectionFilter" class="form-select form-select-sm" style="font-size:.75rem;width:auto;">
                    <option value="">All Sections</option>
                </select>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3" style="font-size:.72rem;">Today</span>
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
                        <tr><td colspan="7" class="text-center text-muted py-4">LORDES</td></tr>
                        <tr><td colspan="7" class="text-center text-muted py-4">HONEY</td></tr>
                        <tr><td colspan="7" class="text-center text-muted py-4">AIM</td></tr>
                        <tr><td colspan="7" class="text-center text-muted py-4">Loading...</td></tr>
                        <tr><td colspan="7" class="text-center text-muted py-4">Loading...</td></tr>
                        <tr><td colspan="7" class="text-center text-muted py-4">Loading...</td></tr>




                    </tbody>
                </table>
            </div>
        </div>
        <!-- Pagination footer -->
        <div class="card-footer bg-white border-top py-3 px-4">
            <div class="d-flex flex-column align-items-center gap-1" id="logsPageInfo">
                <!-- Injected: "Showing X–Y of Z" + pagination nav -->
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-3">

        <!-- Usage Trend -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom px-4 py-3">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-bar me-2 text-success"></i>Usage Trend</h6>
                            <small class="text-muted">Monthly visit counts by date range</small>
                        </div>
                        <!-- Date range inputs -->
                        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end" style="flex-shrink:0;">
                            <div class="input-group input-group-sm" style="width:130px;">
                                <span class="input-group-text bg-light border-end-0 px-2"><i class="fas fa-calendar text-muted" style="font-size:.65rem;"></i></span>
                                <input type="date" id="trendStartDate" class="form-control border-start-0 ps-0" style="font-size:.75rem;">
                            </div>
                            <span class="text-muted small">—</span>
                            <div class="input-group input-group-sm" style="width:130px;">
                                <span class="input-group-text bg-light border-end-0 px-2"><i class="fas fa-calendar-check text-muted" style="font-size:.65rem;"></i></span>
                                <input type="date" id="trendEndDate" class="form-control border-start-0 ps-0" style="font-size:.75rem;">
                            </div>

                        </div>
                    </div>
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
                            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-layer-group me-2 text-primary"></i>College &amp; Course Activity</h6>
                            <small class="text-muted">Visit distribution by college &amp; course — today</small>
                        </div>
                        <span id="chartSectionBadge" class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 ms-2" style="font-size:.72rem;white-space:nowrap;">All Sections</span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div style="position:relative;" id="activityChartWrap">
                        <canvas id="activityChart"></canvas>
                    </div>
                    <div id="activityEmpty" class="text-center text-muted py-4" style="font-size:.8rem;display:none;">
                        No activity today.
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>


<script>
$(document).ready(function () {

  
    //  CONFIG
  
    const BACKEND_URL = "backend/bk_LibraryMenu/bk_libDashboard.php";

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

    // Chart instances
    let trendChartInst    = null;
    let activityChartInst = null;

  
    //  INIT
  
    setDefaultTrendDates();
    loadSections();
	loadKPI();
    loadLogs();
    loadTrend();
    loadCollegeCourseActivity();


  
    //  KPI
  
    function loadKPI() {
        $.ajax({
            type: 'POST', url: BACKEND_URL, data: { request: 'kpiData' }, dataType: 'json',
            success: function (sections) {
                $('.kpi-count').text('0');
                sections.forEach(function (s) {
                    const code = (s.SectionCode ?? '').trim();
                    $(`.kpi-count[data-section-code="${code}"]`).text(s.total ?? 0);
                });
                populateSectionFilter(sections);
            },
        });
    }


  function loadSections() {
    $.ajax({
        type: 'POST',
        url: BACKEND_URL,
        data: { request: 'sections' },
        dataType: 'json',
        success: function (sections) {
            renderSectionCards(sections);
        }
    });
}

function renderSectionCards(sections) {
    const colors = [
        { border: '#10b981', text: 'text-success' },
        { border: '#3b82f6', text: 'text-primary' },
        { border: '#f59e0b', text: 'text-warning' },
        { border: '#ef4444', text: 'text-danger' },
    ];

    let html = '';

    sections.forEach((sec, i) => {
        const color = colors[i % colors.length];

        html += `
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid ${color.border};">
                <div class="card-body p-3">
                    <span class="text-muted fw-semibold text-uppercase d-block mb-2"
                          style="letter-spacing:.05em;font-size:.7rem;">
                        ${escHtml(sec.SectionName)}
                    </span>

                    <h3 class="fw-bold ${color.text} mb-0 kpi-count"
                        data-section-code="${sec.SectionCode}">
                        —
                    </h3>

                    <small class="text-muted">active visits</small>
                </div>
            </div>
        </div>`;
    });

    $('#kpiContainer').html(html);
}
    //  DAILY LOGS + PAGINATION
  
    function loadLogs(page, sectionID) {
        page      = page      ?? 1;
        sectionID = sectionID ?? $('#sectionFilter').val();

        $.ajax({
            type: 'POST', url: BACKEND_URL,
            data: { request: 'dailyLogs', page, sectionID },
            success: function (raw) {
                const res = JSON.parse(raw);
                $('#dailyLogs').html(res.rows);
                renderPagination(res.totalRows, res.totalPages, res.currentPage, res.limit, sectionID);
            },
        });
    }

    /**
     * Renders First « / ‹ / 1…N / › / » Last with ellipsis.
     * Also renders "Showing X–Y of Z records" above the nav.
     */
function renderPagination(totalRows, totalPages, current, limit, sectionID) {
    const $wrap = $('#logsPageInfo').empty();
    if (totalPages <= 1 && totalRows === 0) return;

    const from = ((current - 1) * limit) + 1;
    const to   = Math.min(current * limit, totalRows);
    $wrap.append(`<small class="text-muted">Showing ${from}–${to} of ${totalRows} records</small>`);

    if (totalPages <= 1) return;

    const $ul     = $('<ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center"></ul>');
    const isFirst = current === 1;
    const isLast  = current === totalPages;

    const addItem = (label, page, disabled, active) => {
        const $li = $(`<li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}"></li>`);
        const $a  = $(`<a class="page-link" href="#">${label}</a>`);
        if (!disabled) $a.data('page', page);
        $li.append($a);
        $ul.append($li);
    };

    // «  ‹
    addItem('«', 1,           isFirst, false);
    addItem('‹', current - 1, isFirst, false);

    // Up to 5 page numbers centred on current
    const WINDOW = 5;
    const start  = Math.max(1, Math.min(current - Math.floor(WINDOW / 2), totalPages - WINDOW + 1));
    const end    = Math.min(totalPages, start + WINDOW - 1);
    for (let p = start; p <= end; p++) addItem(p, p, false, p === current);

    // ›  »
    addItem('›', current + 1, isLast, false);
    addItem('»', totalPages,  isLast, false);

    $ul.on('click', '.page-link', function (e) {
        e.preventDefault();
        const p = $(this).data('page');
        if (p) loadLogs(p, sectionID);
    });

    $wrap.append($ul);
}


  
    //  USAGE TREND — Chart.js vertical bar
    function setDefaultTrendDates() {
        const today = new Date();
        const start = new Date(today.getFullYear(), today.getMonth() - 5, 1);
        $('#trendStartDate').val(start.toISOString().split('T')[0]);
        $('#trendEndDate').val(today.toISOString().split('T')[0]);
    }

    function loadTrend(sectionID) {
        sectionID = sectionID ?? $('#sectionFilter').val();
        const startDate = $('#trendStartDate').val();
        const endDate   = $('#trendEndDate').val();

        $.ajax({
            type: 'POST', url: BACKEND_URL,
            data: { request: 'monthlyTrend', sectionID, startDate, endDate },
            dataType: 'json',
            success: function (rows) { renderTrend(rows); },
            error:   function () { renderTrend([]); },
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

        const bgColors = values.map((_, i) =>
            i === lastIdx ? 'rgba(16,185,129,0.88)' : 'rgba(16,185,129,0.35)'
        );
        const borderColors = values.map((_, i) =>
            i === lastIdx ? 'rgba(16,185,129,1)' : 'rgba(16,185,129,0.6)'
        );

        trendChartInst = new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label:           'Visits',
                    data:            values,
                    backgroundColor: bgColors,
                    borderColor:     borderColors,
                    borderWidth:     1.5,
                    borderRadius:    4,
                    borderSkipped:   false,
                }],
            },
            options: {
                responsive:          true,
                maintainAspectRatio: false,
                animation:           { duration: 500, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...TOOLTIP_DEFAULTS,
                        callbacks: {
                            label: ctx => `  Visits: ${ctx.parsed.y.toLocaleString()}`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid:  { display: false },
                        ticks: { color: '#6b7280', font: { size: 11 } },
                    },
                    y: {
                        beginAtZero: true,
                        grid:        { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            color:     '#6b7280',
                            font:      { size: 10 },
                            precision: 0,
                            callback:  v => Number.isInteger(v) ? v : '',
                        },
                    },
                },
                layout: { padding: { top: 8 } },
            },
        });
    }


  
    //  COLLEGE & COURSE ACTIVITY — Chart.js horizontal stacked bar
  
    let sectionFilterPopulated = false;

    function populateSectionFilter(sections) {
        if (sectionFilterPopulated) return;
        sectionFilterPopulated = true;

        const $filter = $('#sectionFilter');
        const seenIDs = new Set();
        $filter.find('option:not(:first)').remove();

        sections.forEach(function (s) {
            const id = String(s.SectionID ?? '').trim();
            if (!id || seenIDs.has(id)) return;
            seenIDs.add(id);
            $filter.append(`<option value="${escHtml(id)}">${escHtml(s.SectionName)}</option>`);
        });
    }

    function loadCollegeCourseActivity(sectionID) {
        sectionID = sectionID ?? $('#sectionFilter').val();
        $('#chartSectionBadge').text(sectionID ? $('#sectionFilter option:selected').text() : 'All Sections');
        $('#activityEmpty').hide();

        $.ajax({
            type: 'POST', url: BACKEND_URL,
            data: { request: 'collegeCourseActivity', sectionID },
            dataType: 'json',
            success:  function (data) { renderActivityChart(data); },
            error:    function ()     { renderActivityChart([]); },
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

        // ── Collect all unique courses across all colleges ────────
        const allCourses = [];
        const courseSet  = new Set();
        colleges.forEach(col => col.courses.forEach(cr => {
            if (!courseSet.has(cr.course)) { courseSet.add(cr.course); allCourses.push(cr.course); }
        }));

        // Assign stable colours per course
        const courseColor = {};
        allCourses.forEach((c, i) => { courseColor[c] = PALETTE[i % PALETTE.length]; });

        // ── Labels = college names (Y axis) ───────────────────────
        const labels = colleges.map(c => c.college || '—');

        // ── One dataset per course ─────────────────────────────────
        const datasets = allCourses.map(course => ({
            label:           course,
            data:            colleges.map(col => {
                const found = col.courses.find(cr => cr.course === course);
                return found ? found.total : 0;
            }),
            backgroundColor: courseColor[course],
            borderColor:     courseColor[course].replace('0.85', '1'),
            borderWidth:     0,
            borderRadius:    3,
            borderSkipped:   false,
        }));

        // ── Dynamic canvas height: min 200px, 42px per college row ─
        const rowH      = 42;
        const chartH    = Math.max(200, colleges.length * rowH + 60);
        canvas.parentElement.style.height = chartH + 'px';

        activityChartInst = new Chart(canvas, {
            type: 'bar',
            data: { labels, datasets },
            options: {
                indexAxis:           'y',
                responsive:          true,
                maintainAspectRatio: false,
                animation:           { duration: 500, easing: 'easeOutQuart' },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color:         '#374151',
                            font:          { size: 11 },
                            padding:       12,
                            usePointStyle: true,
                            pointStyle:    'circle',
                            boxWidth:      8,
                            boxHeight:     8,
                        },
                    },
                    tooltip: {
                        ...TOOLTIP_DEFAULTS,
                        mode:      'index',
                        intersect: false,
                        callbacks: {
                            title:  items => items[0]?.label ?? '',
                            label:  ctx   => ctx.parsed.x > 0 ? `  ${ctx.dataset.label}: ${ctx.parsed.x}` : null,
                            footer: items => {
                                const total = items.reduce((s, i) => s + (i.parsed.x || 0), 0);
                                return `  Total: ${total}`;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        stacked:     true,
                        beginAtZero: true,
                        grid:        { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            color:     '#6b7280',
                            font:      { size: 10 },
                            precision: 0,
                            callback:  v => Number.isInteger(v) ? v : '',
                        },
                        title: {
                            display: true,
                            text:    'No. of Visits',
                            color:   '#9ca3af',
                            font:    { size: 10 },
                            padding: { top: 6 },
                        },
                    },
                    y: {
                        stacked: true,
                        grid:    { display: false },
                        ticks: {
                            color: '#374151',
                            font:  { size: 12 },
                        },
                    },
                },
                layout: { padding: { right: 8 } },
            },
        });
    }


  
    //  FILTER — section dropdown drives logs + charts
  
    $('#sectionFilter').on('change', function () {
        const sid = $(this).val();
        loadLogs(1, sid);
        loadCollegeCourseActivity(sid);
        loadTrend(sid);
    });

    // Reload trend whenever both date fields are filled
    $('#trendStartDate, #trendEndDate').on('change', function () {
        const s = $('#trendStartDate').val(), e = $('#trendEndDate').val();
        if (s && e && s <= e) loadTrend($('#sectionFilter').val());
    });


  
    //  UTIL
  
    function escHtml(str) {
        return $('<div>').text(str).html();
    }

});
</script>