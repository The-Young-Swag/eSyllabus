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

        <!-- College Activity -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom px-4 py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-users me-2 text-primary"></i>College Activity</h6>
                    <small class="text-muted">Student distribution by college — today</small>
                </div>
                <div id="departmentChart" class="card-body px-4 py-4">
                    <div id="collegeActivityBars" class="d-flex flex-column gap-4">
                        <div class="text-center text-muted py-4" style="font-size:.8rem;">Loading...</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- College & Course Activity -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-layer-group me-2 text-info"></i>College &amp; Course Activity</h6>
                        <small class="text-muted">Visits grouped by college and course — today</small>
                    </div>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3" style="font-size:.72rem;">Today</span>
                </div>
                <div class="card-body px-4 py-3">
                    <div id="collegeCourseActivity">
                        <div class="text-center text-muted py-4" style="font-size:.8rem;">Loading...</div>
                    </div>
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
    loadDepartmentOverview();
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
    //  COLLEGE ACTIVITY
    // =========================================================

    function loadDepartmentOverview() {
        $.ajax({
            type:     "POST",
            url:      BACKEND_URL,
            data:     { request: "departmentOverview" },
            dataType: "json",
            success:  renderCollegeActivity,
            error: function () {
                $("#collegeActivityBars").html('<div class="text-center text-muted">No data available.</div>');
            }
        });
    }

    function renderCollegeActivity(rows) {
        if (!rows || !rows.length) {
            $("#collegeActivityBars").html('<div class="text-center text-muted py-4" style="font-size:.8rem;">No activity today.</div>');
            return;
        }

        const maxVal  = Math.max(...rows.map(r => parseInt(r.total)));
        const palette = ["bg-primary", "bg-primary bg-opacity-75", "bg-primary bg-opacity-50", "bg-info", "bg-info bg-opacity-75"];

        const html = rows.map(function (r, idx) {
            const pct   = maxVal > 0 ? (parseInt(r.total) / maxVal * 100).toFixed(1) : 0;
            const color = palette[idx % palette.length];
            return `
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="fw-semibold text-dark">${escHtml(r.college || '—')}</small>
                        <small class="text-muted">${r.total} students</small>
                    </div>
                    <div class="progress" style="height:8px;border-radius:8px;">
                        <div class="progress-bar ${color}" role="progressbar"
                             style="width:${pct}%;border-radius:8px;"></div>
                    </div>
                </div>
            `;
        }).join('');

        $("#collegeActivityBars").html(html);
    }


    // =========================================================
    //  COLLEGE & COURSE ACTIVITY
    // =========================================================

    function loadCollegeCourseActivity() {
        $.ajax({
            type:     "POST",
            url:      BACKEND_URL,
            data:     { request: "collegeCourseActivity" },
            dataType: "json",
            success:  renderCollegeCourseActivity,
            error: function () {
                $("#collegeCourseActivity").html('<div class="text-center text-muted py-4">No data available.</div>');
            }
        });
    }

    function renderCollegeCourseActivity(colleges) {
        if (!colleges || !colleges.length) {
            $("#collegeCourseActivity").html('<div class="text-center text-muted py-4" style="font-size:.8rem;">No activity today.</div>');
            return;
        }

        // colleges = [ { college, total, courses: [ { course, total }, ... ] }, ... ]
        const maxCollegeTotal = Math.max(...colleges.map(c => parseInt(c.total)));
        const palette = ["#3a6cf4", "#06B6D4", "#8b5cf6", "#f59e0b", "#ef4444", "#10b981"];

        const html = colleges.map(function (col, colIdx) {
            const color        = palette[colIdx % palette.length];
            const colPct       = maxCollegeTotal > 0 ? (parseInt(col.total) / maxCollegeTotal * 100).toFixed(1) : 0;
            const maxCourse    = Math.max(...col.courses.map(c => parseInt(c.total)));

            const courseRows = col.courses.map(function (course) {
                const coursePct = maxCourse > 0 ? (parseInt(course.total) / maxCourse * 100).toFixed(1) : 0;
                return `
                    <div class="d-flex align-items-center gap-3">
                        <small class="text-muted text-end" style="min-width:130px;font-size:.78rem;">${escHtml(course.course || '—')}</small>
                        <div class="flex-fill" style="position:relative;">
                            <div class="rounded" style="height:6px;background:#f1f3f5;">
                                <div class="rounded" style="height:6px;width:${coursePct}%;background:${color};opacity:.55;transition:width .4s;"></div>
                            </div>
                        </div>
                        <small class="text-muted fw-semibold" style="min-width:24px;font-size:.75rem;">${course.total}</small>
                    </div>
                `;
            }).join('');

            return `
                <div class="mb-4">
                    <!-- College header row -->
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <small class="fw-bold text-dark" style="min-width:130px;font-size:.82rem;">${escHtml(col.college || '—')}</small>
                        <div class="flex-fill" style="position:relative;">
                            <div class="rounded" style="height:8px;background:#f1f3f5;">
                                <div class="rounded" style="height:8px;width:${colPct}%;background:${color};transition:width .4s;"></div>
                            </div>
                        </div>
                        <small class="fw-semibold" style="min-width:24px;font-size:.78rem;color:${color};">${col.total}</small>
                    </div>
                    <!-- Course breakdown -->
                    <div class="d-flex flex-column gap-2 ps-1">
                        ${courseRows}
                    </div>
                </div>
            `;
        }).join('');

        $("#collegeCourseActivity").html(html);
    }


    // =========================================================
    //  UTIL
    // =========================================================

    function escHtml(str) {
        return $('<div>').text(str).html();
    }

});
</script>