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
            <div class="d-flex align-items-center gap-2">
                <select id="sectionFilter" class="form-select form-select-sm" style="font-size:.75rem;width:auto;">
                    <option value="">All Sections</option>
                </select>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3" style="font-size:.72rem;">Today</span>
            </div>
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
                    <div id="trendBars">
                        <div class="text-center text-muted w-100 py-4" style="font-size:.8rem;">Loading...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- College & Course Activity — stacked horizontal bar -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom px-4 py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-layer-group me-2 text-primary"></i>College &amp; Course Activity</h6>
                            <small class="text-muted">Visit distribution by college, course &amp; library section — today</small>
                        </div>
                        <span id="chartSectionBadge" class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 ms-2" style="font-size:.72rem;white-space:nowrap;">All Sections</span>
                    </div>
                    <!-- Course legend -->
                    <div id="collegeCourseActivityLegend" class="d-flex flex-wrap gap-2 mt-2" style="min-height:16px;"></div>
                </div>
                <div class="card-body px-4 py-3" style="overflow-x:auto;">
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
                populateSectionFilter(sections);
            },
            error: function () {
                console.error("KPI load failed.");
            }
        });
    }


    // =========================================================
    //  DAILY LOGS
    // =========================================================

    function loadLogs(page = 1, sectionID = "") {
        $.ajax({
            type: "POST",
            url:  BACKEND_URL,
            data: { request: "dailyLogs", page, sectionID },
            success: function (raw) {
                const res = JSON.parse(raw);
                $("#dailyLogs").html(res.rows);
                renderPagination(res.totalPages, res.currentPage, sectionID);
            }
        });
    }

    function renderPagination(totalPages, currentPage, sectionID = "") {
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
            loadLogs(parseInt($(this).data("page")), sectionID);
        });
    }


    // =========================================================
    //  USAGE TREND — vertical bar chart with precise Y-axis
    // =========================================================

    function loadMonthlyTrend(sectionID = "") {
        $.ajax({
            type:     "POST",
            url:      BACKEND_URL,
            data:     { request: "monthlyTrend", sectionID },
            dataType: "json",
            success: function (rows) { renderMonthlyTrend(rows, sectionID); },
            error: function () {
                $("#trendBars").html('<div class="text-center text-muted w-100">No data available.</div>');
            }
        });
    }

    function renderMonthlyTrend(rows, sectionID) {
        if (!rows || !rows.length) {
            $("#trendBars").html('<div class="text-center text-muted w-100 py-4">No data available.</div>');
            return;
        }

        const maxRaw   = Math.max(...rows.map(r => parseInt(r.total)));
        const niceMax  = niceUpperBound(maxRaw);
        const STEPS    = 4;
        const stepVal  = niceMax / STEPS;
        const CHART_H  = 160; // px — plotting area height

        // Y-axis labels (top → bottom: niceMax down to 0)
        const yLabels = Array.from({ length: STEPS + 1 }, (_, i) => niceMax - i * stepVal);

        // Section context label
        const sectionLabel = sectionID
            ? `<small class="text-primary fw-semibold" style="font-size:.7rem;">
                   <i class="fas fa-filter me-1" style="font-size:.6rem;"></i>${escHtml($("#sectionFilter option:selected").text())}
               </small>`
            : `<small class="text-muted" style="font-size:.7rem;">All Sections</small>`;

        // Build grid + bars wrapper
        const yAxisHtml = yLabels.map((val, i) => {
            const topPct = (i / STEPS * 100).toFixed(1);
            return `<div style="position:absolute;top:${topPct}%;left:0;right:0;display:flex;align-items:center;gap:4px;pointer-events:none;">
                        <span style="width:28px;text-align:right;flex-shrink:0;">
                            <small class="text-muted" style="font-size:.62rem;line-height:1;">${val}</small>
                        </span>
                        <div style="flex:1;border-top:1px ${i === STEPS ? 'solid #adb5bd' : 'dashed #e9ecef'};"></div>
                    </div>`;
        }).join('');

        const barsHtml = rows.map(function (r, idx) {
            const val       = parseInt(r.total);
            const heightPct = niceMax > 0 ? (val / niceMax * 100).toFixed(2) : 0;
            const isCurrent = idx === rows.length - 1;
            const barColor  = isCurrent ? "#198754" : "#198754";
            const barOpacity = isCurrent ? "1" : "0.38";
            return `
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;min-width:0;">
                    <small style="font-size:.65rem;font-weight:${isCurrent ? 700 : 500};color:${isCurrent ? '#198754' : '#6c757d'};">${val}</small>
                    <div style="width:100%;flex:1;display:flex;align-items:flex-end;">
                        <div style="width:100%;background:${barColor};opacity:${barOpacity};border-radius:3px 3px 0 0;height:${heightPct}%;min-height:${val > 0 ? 3 : 0}px;transition:height .3s;"></div>
                    </div>
                    <small style="font-size:.68rem;font-weight:${isCurrent ? 600 : 400};color:${isCurrent ? '#198754' : '#6c757d'};">${r.month}</small>
                </div>`;
        }).join('');

        $("#userChart .chart-section-label").remove();
        $("#userChart").prepend(`<div class="chart-section-label d-flex justify-content-end mb-1">${sectionLabel}</div>`);

        $("#trendBars").html(`
            <div style="display:flex;gap:0;height:${CHART_H}px;width:100%;position:relative;">
                <!-- Y-axis grid overlay -->
                <div style="position:absolute;inset:0;padding-left:32px;pointer-events:none;">
                    ${yAxisHtml}
                </div>
                <!-- Bars -->
                <div style="flex:1;display:flex;gap:6px;align-items:stretch;padding-left:32px;position:relative;z-index:1;">
                    ${barsHtml}
                </div>
            </div>
        `);
    }


    // =========================================================
    //  COLLEGE & COURSE ACTIVITY
    //  Each college = one row.
    //  Bar is segmented by course (colour-coded, label inside).
    //  Below each bar: a thin section strip shows which library
    //  section contributed what portion of that college's visits.
    // =========================================================

    const COURSE_PALETTE = [
        "#4f7df3","#2ebd85","#f5a623","#e05c5c","#9b6fe8",
        "#17a2b8","#fd7e14","#20c997","#d63384","#6f42c1"
    ];

    const courseColorMap  = {};
    let   coursePaletteIdx  = 0;

    function getCourseColor(name) {
        if (!courseColorMap[name]) {
            courseColorMap[name] = COURSE_PALETTE[coursePaletteIdx % COURSE_PALETTE.length];
            coursePaletteIdx++;
        }
        return courseColorMap[name];
    }

    let sectionFilterPopulated = false;

    function populateSectionFilter(sections) {
        if (sectionFilterPopulated) return;
        sectionFilterPopulated = true;

        const $filter = $("#sectionFilter");
        const seenIDs = new Set();

        $filter.find("option:not(:first)").remove();

        sections.forEach(function (s) {
            const id = String(s.SectionID ?? "").trim();
            if (!id || seenIDs.has(id)) return;
            seenIDs.add(id);
            $filter.append(`<option value="${escHtml(id)}">${escHtml(s.SectionName)}</option>`);
        });
    }

    function loadCollegeCourseActivity(sectionID = "") {
        $("#collegeCourseActivity").html(
            '<div class="text-center text-muted py-4" style="font-size:.8rem;"><span class="spinner-border spinner-border-sm me-1"></span> Loading...</div>'
        );
        $.ajax({
            type:     "POST",
            url:      BACKEND_URL,
            data:     { request: "collegeCourseActivity", sectionID },
            dataType: "json",
            success:  function (data) { renderCollegeCourseStackedChart(data, sectionID); },
            error: function () {
                $("#collegeCourseActivity").html('<div class="text-center text-muted py-4">No data available.</div>');
            }
        });
    }

    function renderCollegeCourseStackedChart(colleges, sectionID) {
        const $area = $("#collegeCourseActivity");

        if (!colleges || !colleges.length) {
            $area.html('<div class="text-center text-muted py-4" style="font-size:.8rem;">No activity today.</div>');
            $("#collegeCourseActivityLegend").empty();
            return;
        }

        // ── Badge in card header ──────────────────────────────────
        const badgeText = sectionID
            ? $("#sectionFilter option:selected").text()
            : "All Sections";
        $("#chartSectionBadge").text(badgeText);

        // ── Pre-assign colours for every course seen ──────────────
        colleges.forEach(function (col) {
            col.courses.forEach(function (c) { getCourseColor(c.course); });
        });

        // ── Course legend ─────────────────────────────────────────
        const seenCourses = new Set();
        colleges.forEach(c => c.courses.forEach(cr => seenCourses.add(cr.course)));

        $("#collegeCourseActivityLegend").html(
            [...seenCourses].map(function (course) {
                const color = getCourseColor(course);
                return `<span class="d-inline-flex align-items-center gap-1" style="font-size:.7rem;">
                            <span style="width:9px;height:9px;border-radius:2px;background:${color};flex-shrink:0;display:inline-block;"></span>
                            <span class="text-muted">${escHtml(course)}</span>
                        </span>`;
            }).join('')
        );

        // ── Axis scale ────────────────────────────────────────────
        const rawMax  = Math.max(...colleges.map(c => parseInt(c.total)));
        const niceMax = niceUpperBound(rawMax);
        const TICKS   = 4;
        const ticks   = Array.from({ length: TICKS + 1 }, (_, i) => Math.round(i / TICKS * niceMax));

        // ── Layout constants ──────────────────────────────────────
        const LABEL_W = 116; // px  college name column
        const COUNT_W = 36;  // px  total count column
        const BAR_H   = 24;  // px  bar height
        const ROW_GAP = 14;  // px  gap between college rows

        // ── Tick header ───────────────────────────────────────────
        const tickRowHtml = `
            <div style="display:flex;align-items:flex-end;margin-bottom:4px;">
                <div style="width:${LABEL_W}px;flex-shrink:0;"></div>
                <div style="flex:1;position:relative;height:14px;">
                    ${ticks.map(t => {
                        const leftPct = niceMax > 0 ? (t / niceMax * 100).toFixed(2) : 0;
                        return `<div style="position:absolute;left:${leftPct}%;transform:translateX(-50%);">
                                    <small class="text-muted" style="font-size:.6rem;white-space:nowrap;">${t}</small>
                                </div>`;
                    }).join('')}
                </div>
                <div style="width:${COUNT_W}px;flex-shrink:0;"></div>
            </div>`;

        // ── Bar rows ──────────────────────────────────────────────
        const rowsHtml = colleges.map(function (col) {
            const colTotal    = parseInt(col.total);
            const barWidthPct = niceMax > 0 ? (colTotal / niceMax * 100).toFixed(3) : 0;

            // Grid lines at tick positions
            const gridLines = ticks.map(t => {
                if (t === 0) return '';
                const leftPct = (t / niceMax * 100).toFixed(2);
                return `<div style="position:absolute;left:${leftPct}%;top:0;bottom:0;width:1px;background:#e9ecef;pointer-events:none;"></div>`;
            }).join('');

            // Course segments
            const courseSegments = col.courses.map(function (course) {
                const segPct = colTotal > 0 ? (course.total / colTotal * 100).toFixed(3) : 0;
                const color  = getCourseColor(course.course);
                return `<div style="width:${segPct}%;background:${color};height:100%;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
                            <span style="color:#fff;font-size:.65rem;font-weight:600;white-space:nowrap;padding:0 5px;text-shadow:0 1px 2px rgba(0,0,0,.35);">
                                ${escHtml(course.course)}: ${course.total}
                            </span>
                        </div>`;
            }).join('');

            // ── Section breakdown list ─────────────────────────────
            // Aggregate section totals across all courses for this college
            const sectionTotals = {};
            col.courses.forEach(function (cr) {
                cr.sections.forEach(function (s) {
                    sectionTotals[s.section_name] = (sectionTotals[s.section_name] || 0) + s.total;
                });
            });

            // Sort sections by total descending
            const sectionItems = Object.entries(sectionTotals)
                .sort((a, b) => b[1] - a[1])
                .map(function ([secName, secTotal]) {
                    return `<span style="display:inline-flex;align-items:center;gap:4px;font-size:.68rem;color:#6c757d;">
                                <span style="width:5px;height:5px;border-radius:50%;background:#adb5bd;flex-shrink:0;display:inline-block;"></span>
                                <span class="fw-semibold" style="color:#495057;">${escHtml(secName)}</span>
                                <span>: ${secTotal}</span>
                            </span>`;
                }).join('<span style="color:#dee2e6;margin:0 4px;font-size:.65rem;">|</span>');

            const sectionBreakdownHtml = `
                <div style="display:flex;align-items:flex-start;margin-top:4px;">
                    <div style="width:${LABEL_W}px;flex-shrink:0;"></div>
                    <div style="flex:1;display:flex;flex-wrap:wrap;gap:3px 0;align-items:center;padding-right:${COUNT_W}px;">
                        ${sectionItems}
                    </div>
                </div>`;

            return `
                <div style="margin-bottom:${ROW_GAP}px;">
                    <!-- Bar row -->
                    <div style="display:flex;align-items:center;gap:0;">
                        <div style="width:${LABEL_W}px;flex-shrink:0;text-align:right;padding-right:10px;">
                            <small class="fw-semibold text-dark" style="font-size:.74rem;line-height:1.3;">${escHtml(col.college || '—')}</small>
                        </div>
                        <div style="flex:1;position:relative;height:${BAR_H}px;">
                            ${gridLines}
                            <div style="position:relative;width:${barWidthPct}%;height:100%;display:flex;border-radius:3px;overflow:hidden;">
                                ${courseSegments}
                            </div>
                        </div>
                        <div style="width:${COUNT_W}px;flex-shrink:0;padding-left:7px;">
                            <small class="text-muted fw-semibold" style="font-size:.71rem;">${colTotal}</small>
                        </div>
                    </div>
                    <!-- Section breakdown listed below each row -->
                    ${sectionBreakdownHtml}
                </div>`;
        }).join('');

        // ── X-axis label ──────────────────────────────────────────
        const xAxisHtml = `
            <div style="display:flex;margin-top:2px;">
                <div style="width:${LABEL_W}px;flex-shrink:0;"></div>
                <div style="flex:1;text-align:center;">
                    <small class="text-muted" style="font-size:.66rem;letter-spacing:.04em;text-transform:uppercase;">No. of Visits</small>
                </div>
                <div style="width:${COUNT_W}px;flex-shrink:0;"></div>
            </div>`;

        $area.html(tickRowHtml + rowsHtml + xAxisHtml);
    }


    // =========================================================
    //  SCALE UTIL — "nice" upper bound for axis max
    //  Rounds up to the nearest clean number: 1,2,5 × 10^n
    // =========================================================

    function niceUpperBound(rawMax) {
        if (rawMax <= 0) return 10;
        const magnitude  = Math.pow(10, Math.floor(Math.log10(rawMax)));
        const normalized = rawMax / magnitude;
        let   niceFactor;
        if      (normalized <= 1)  niceFactor = 1;
        else if (normalized <= 2)  niceFactor = 2;
        else if (normalized <= 5)  niceFactor = 5;
        else                       niceFactor = 10;
        return Math.max(niceFactor * magnitude, rawMax);
    }


    // ── Single filter in the table header drives everything ──────
    $("#sectionFilter").on("change", function () {
        const sectionID = $(this).val();
        loadLogs(1, sectionID);
        loadCollegeCourseActivity(sectionID);
        loadMonthlyTrend(sectionID);
    });


    // =========================================================
    //  UTIL
    // =========================================================

    function escHtml(str) {
        return $('<div>').text(str).html();
    }

});
</script>