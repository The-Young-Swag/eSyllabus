<div class="container-fluid px-4 py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Library Dashboard</h5>
            <small class="text-muted">Overview of library activities and trends</small>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold">
            <i class="fas fa-circle me-1 text-success" style="font-size:.55rem;vertical-align:middle;"></i>Live
        </span>
    </div>

    <!-- KPI Cards -->
    <div id="kpiContainer" class="row g-3 mb-4">

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted fw-semibold text-uppercase" style="letter-spacing:.05em;font-size:.7rem;">Filipiniana 1F</span>
                    </div>
                    <h3 class="fw-bold text-success mb-0 kpi-count" data-section-code="FIL1F">0</h3>
                    <small class="text-muted">active visits</small>
                </div>
                <div class="bg-success rounded-bottom" style="height:3px;"></div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted fw-semibold text-uppercase" style="letter-spacing:.05em;font-size:.7rem;">Filipiniana 2F</span>
                    </div>
                    <h3 class="fw-bold text-primary mb-0 kpi-count" data-section-code="FIL2F">0</h3>
                    <small class="text-muted">active visits</small>
                </div>
                <div class="bg-primary rounded-bottom" style="height:3px;"></div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted fw-semibold text-uppercase" style="letter-spacing:.05em;font-size:.7rem;">Manuscript</span>
                    </div>
                    <h3 class="fw-bold text-warning mb-0 kpi-count" data-section-code="MAN">0</h3>
                    <small class="text-muted">active visits</small>
                </div>
                <div class="bg-warning rounded-bottom" style="height:3px;"></div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted fw-semibold text-uppercase" style="letter-spacing:.05em;font-size:.7rem;">Recreational</span>
                    </div>
                    <h3 class="fw-bold text-danger mb-0 kpi-count" data-section-code="REC">0</h3>
                    <small class="text-muted">active visits</small>
                </div>
                <div class="bg-danger rounded-bottom" style="height:3px;"></div>
            </div>
        </div>

    </div>

    <!-- Daily Logs Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
            <div>
                <h6 class="mb-0 fw-bold text-dark">Daily Logs</h6>
                <small class="text-muted">Real-time check-in / check-out records</small>
            </div>
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3" style="font-size:.72rem;">Today</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="px-4 py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Student No.</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">College</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Course</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Library</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Check-In</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Check-Out</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="dailyLogs">
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-2 px-4">
            <nav>
                <ul class="pagination pagination-sm justify-content-center mb-0" id="logsPagination"></ul>
            </nav>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-3">

        <!-- Usage Trend -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom px-4 py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-bar me-2 text-success"></i>Usage Trend</h6>
                    <small class="text-muted">Monthly student logins — last 6 months</small>
                </div>
                <div id="userChart" class="card-body px-4 pb-4 pt-3">
                    <div id="trendBars" class="d-flex align-items-end gap-2 justify-content-between" style="height:180px;">
                        <div class="text-center text-muted w-100 py-4" style="font-size:.8rem;">Loading...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- College & Course Activity — stacked horizontal bar -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-layer-group me-2 text-primary"></i>College &amp; Course Activity</h6>
                        <small class="text-muted">Visit distribution by college and course — today</small>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3" style="font-size:.72rem;">Today</span>
                </div>
                <div class="card-body px-4 py-3">
                    <div id="collegeCourseActivity">
                        <div class="text-center text-muted py-4" style="font-size:.8rem;">Loading...</div>
                    </div>
                    <!-- Legend -->
                    <div id="collegeCourseActivityLegend" class="d-flex flex-wrap gap-2 mt-3"></div>
                </div>
            </div>
        </div>

    </div>

</div>


<script>
$(document).ready(function () {

    // =========================================================
    //  CONFIG
    // =========================================================

    const BACKEND_URL = "backend/bk_LibraryMenu/bk_libDashboard.php";


    // =========================================================
    //  INIT
    // =========================================================

    loadKPI();
    loadLogs();
    loadMonthlyTrend();
    loadCollegeCourseActivity();


    // =========================================================
    //  KPI
    // =========================================================

    function loadKPI() {
        $.ajax({
            type:     "POST",
            url:      BACKEND_URL,
            data:     { request: "kpiData" },
            dataType: "json",
            success: function (sections) {
                $(".kpi-count").text("0");
                sections.forEach(function (section) {
                    const code  = (section.SectionCode ?? "").trim();
                    const total = section.total ?? 0;
                    $(`.kpi-count[data-section-code="${code}"]`).text(total);
                });
            },
            error: function () {
                console.error("KPI load failed.");
            }
        });
    }


    // =========================================================
    //  DAILY LOGS
    // =========================================================

    function loadLogs(page = 1) {
        $.ajax({
            type: "POST",
            url:  BACKEND_URL,
            data: { request: "dailyLogs", page },
            success: function (raw) {
                const res = JSON.parse(raw);
                $("#dailyLogs").html(res.rows);
                renderPagination(res.totalPages, res.currentPage);
            }
        });
    }

    function renderPagination(totalPages, currentPage) {
        const $pag = $("#logsPagination").empty();
        if (totalPages <= 1) return;

        for (let p = 1; p <= totalPages; p++) {
            const active = p === currentPage ? "active" : "";
            $pag.append(`
                <li class="page-item ${active}">
                    <a class="page-link" href="#" data-page="${p}">${p}</a>
                </li>
            `);
        }

        $pag.find(".page-link").on("click", function (e) {
            e.preventDefault();
            loadLogs(parseInt($(this).data("page")));
        });
    }


    // =========================================================
    //  USAGE TREND
    // =========================================================

    function loadMonthlyTrend() {
        $.ajax({
            type:     "POST",
            url:      BACKEND_URL,
            data:     { request: "monthlyTrend" },
            dataType: "json",
            success:  renderMonthlyTrend,
            error: function () {
                $("#trendBars").html('<div class="text-center text-muted w-100">No data available.</div>');
            }
        });
    }

    function renderMonthlyTrend(rows) {
        if (!rows || !rows.length) {
            $("#trendBars").html('<div class="text-center text-muted w-100">No data available.</div>');
            return;
        }

        const maxVal = Math.max(...rows.map(r => parseInt(r.total)));
        const bars   = rows.map(function (r) {
            const pct      = maxVal > 0 ? (parseInt(r.total) / maxVal * 100).toFixed(1) : 0;
            const isCurrent = rows.indexOf(r) === rows.length - 1;
            const barColor = isCurrent ? "bg-success" : "bg-success bg-opacity-50";
            const labelClass = isCurrent ? "text-success fw-bold" : "text-muted fw-semibold";
            return `
                <div class="d-flex flex-column align-items-center flex-fill gap-1">
                    <small class="${labelClass}" style="font-size:.7rem;">${r.total}</small>
                    <div class="w-100 rounded-top ${barColor}" style="height:${pct}%;min-height:4px;"></div>
                    <small class="${isCurrent ? 'text-success fw-semibold' : 'text-muted'}" style="font-size:.72rem;">${r.month}</small>
                </div>
            `;
        }).join('');

        $("#trendBars").html(bars);
    }


    // =========================================================
    //  COLLEGE & COURSE ACTIVITY — stacked horizontal bar chart
    //  Each row = one college. The bar is segmented by course.
    // =========================================================

    function loadCollegeCourseActivity() {
        $.ajax({
            type:     "POST",
            url:      BACKEND_URL,
            data:     { request: "collegeCourseActivity" },
            dataType: "json",
            success:  renderCollegeCourseStackedChart,
            error: function () {
                $("#collegeCourseActivity").html('<div class="text-center text-muted py-4">No data available.</div>');
            }
        });
    }

    function renderCollegeCourseStackedChart(colleges) {
        if (!colleges || !colleges.length) {
            $("#collegeCourseActivity").html('<div class="text-center text-muted py-4" style="font-size:.8rem;">No activity today.</div>');
            return;
        }

        // Build a global course → color map so each course has one consistent color
        const courseColorPalette = ["#4f7df3","#e05c5c","#3dbfa8","#f59e0b","#8b5cf6","#06B6D4","#ef4444","#10b981","#f97316","#a855f7"];
        const courseColors       = {};
        let   colorIdx           = 0;
        colleges.forEach(function (col) {
            col.courses.forEach(function (course) {
                if (!courseColors[course.course]) {
                    courseColors[course.course] = courseColorPalette[colorIdx % courseColorPalette.length];
                    colorIdx++;
                }
            });
        });

        const globalMax = Math.max(...colleges.map(c => parseInt(c.total)));

        // X-axis tick markers
        const tickCount = 5;
        const tickStep  = Math.ceil(globalMax / tickCount);
        const ticks     = [];
        for (let t = 0; t <= tickCount; t++) ticks.push(t * tickStep);

        // Build tick header
        const tickHtml = `
            <div class="d-flex mb-1" style="padding-left:110px;">
                ${ticks.map(t => `<div style="flex:${t === 0 ? 0 : tickStep};min-width:0;text-align:${t === 0 ? 'left' : 'right'}">
                    <small class="text-muted" style="font-size:.68rem;">${t === 0 ? '' : t}</small>
                </div>`).join('')}
            </div>
        `;

        // Build one row per college
        const rowsHtml = colleges.map(function (col) {
            const colTotal  = parseInt(col.total);
            const barWidthPct = globalMax > 0 ? (colTotal / globalMax * 100).toFixed(2) : 0;

            // Segments: each course is a proportional slice of the college's full bar
            const segments = col.courses.map(function (course) {
                const segPct = colTotal > 0 ? (parseInt(course.total) / colTotal * 100).toFixed(2) : 0;
                const color  = courseColors[course.course];
                return `<div title="${escHtml(course.course)}: ${course.total}"
                              style="width:${segPct}%;background:${color};height:100%;display:inline-block;"></div>`;
            }).join('');

            return `
                <div class="d-flex align-items-center mb-2" style="gap:8px;">
                    <!-- College label -->
                    <div style="width:102px;flex-shrink:0;text-align:right;">
                        <small class="fw-semibold text-dark" style="font-size:.78rem;">${escHtml(col.college || '—')}</small>
                    </div>
                    <!-- Stacked bar -->
                    <div style="flex:1;position:relative;">
                        <!-- Grid lines -->
                        <div style="position:absolute;inset:0;display:flex;pointer-events:none;">
                            ${ticks.slice(1).map(t => `<div style="position:absolute;left:${(t/globalMax*100).toFixed(1)}%;top:0;bottom:0;border-left:1px solid #e9ecef;"></div>`).join('')}
                        </div>
                        <div style="width:${barWidthPct}%;height:18px;border-radius:3px;overflow:hidden;display:flex;">
                            ${segments}
                        </div>
                    </div>
                    <!-- Total count -->
                    <small class="text-muted fw-semibold" style="font-size:.75rem;min-width:28px;">${colTotal}</small>
                </div>
            `;
        }).join('');

        // X-axis label
        const xAxisHtml = `
            <div class="d-flex mt-1" style="padding-left:110px;">
                <small class="text-muted w-100 text-center" style="font-size:.72rem;">Frequency</small>
            </div>
        `;

        // Legend — one entry per unique course
        const legendHtml = Object.entries(courseColors).map(function ([course, color]) {
            return `<span class="d-flex align-items-center gap-1" style="font-size:.73rem;">
                        <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:${color};flex-shrink:0;"></span>
                        <span class="text-muted">${escHtml(course)}</span>
                    </span>`;
        }).join('');

        $("#collegeCourseActivity").html(tickHtml + rowsHtml + xAxisHtml);
        $("#collegeCourseActivityLegend").html(legendHtml);
    }


    // =========================================================
    //  UTIL
    // =========================================================

    function escHtml(str) {
        return $('<div>').text(str).html();
    }

});
</script>