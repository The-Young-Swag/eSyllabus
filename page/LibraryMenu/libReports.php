<?php
/**
 * Library Analytics Dashboard — Frontend View
 */
include '../../db/dbconnection.php';
$librarySections = execsqlSRS(
    'SELECT SectionID, SectionName FROM LibrarySection WHERE IsActive = 1 ORDER BY SectionName',
    'Select', []
);
?>

<div class="container-fluid py-4 px-4">

    <!-- HEADER -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1">Library Analytics</h5>
            <p class="text-muted small mb-0">Visitor trends, usage patterns, and demographic insights</p>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <button class="btn btn-outline-secondary btn-sm" id="refreshBtn">
                <i class="fas fa-sync-alt me-1"></i>Refresh
            </button>
            <button class="btn btn-outline-primary btn-sm" id="exportBtn" disabled>
                <i class="fas fa-file-export me-1"></i>Export
            </button>
        </div>
    </div>

    <!-- FILTERS -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <!-- row-cols-md-4 ensures 4 equal columns on desktop -->
        <div class="row align-items-end">
            
            <!-- Start Date -->
            <div class="col-md-3 mb-3 mb-md-0">
                <label class="small font-weight-bold mb-1">Start Date</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white text-muted border-right-0">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                    </div>
                    <input type="date" class="form-control border-left-0" id="startDate" value="2026-03-19">
                </div>
            </div>

            <!-- End Date -->
            <div class="col-md-3 mb-3 mb-md-0">
                <label class="small font-weight-bold mb-1">End Date</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white text-muted border-right-0">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                    </div>
                    <input type="date" class="form-control border-left-0" id="endDate" value="2026-03-26">
                </div>
            </div>

            <!-- Classification -->
            <div class="col-md-3 mb-3 mb-md-0">
                <label class="small font-weight-bold mb-1">Classification</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white text-muted border-right-0">
                            <i class="fas fa-filter"></i>
                        </span>
                    </div>
                    <select class="custom-select border-left-0" id="classificationFilter">
                        <option value="All">All</option>
                        <option value="Student">Student</option>
                        <option value="Employee">Employee</option>
                        <option value="Guest">Guest</option>
                    </select>
                </div>
            </div>

            <!-- Library Section -->
            <div class="col-md-3">
                <label class="small font-weight-bold mb-1">Library Section</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white text-muted border-right-0">
                            <i class="fas fa-book"></i>
                        </span>
                    </div>
                    <select class="custom-select border-left-0" id="libraryFilter">
                        <option value="All">All Sections</option>
                        <?php foreach ($librarySections as $s): ?>
                            <option value="<?= $s['SectionID'] ?>">
                                <?= htmlspecialchars($s['SectionName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

        </div>
    </div>
</div>


    <!-- KPI CARDS -->
    <div class="row g-3 mb-4">

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #3b82f6 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-muted small fw-semibold mb-0">Top Students</p>
                        <div class="rounded-2 bg-primary-subtle d-flex align-items-center justify-content-center"
                             style="width:30px;height:30px;flex-shrink:0;">
                            <i class="bi bi-person-fill text-primary" style="font-size:.8rem;"></i>
                        </div>
                    </div>
                    <div id="kpiTopStudents"><div class="text-muted small fst-italic">Loading…</div></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #10b981 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-muted small fw-semibold mb-0">Top Colleges</p>
                        <div class="rounded-2 bg-success-subtle d-flex align-items-center justify-content-center"
                             style="width:30px;height:30px;flex-shrink:0;">
                            <i class="bi bi-building-fill text-success" style="font-size:.8rem;"></i>
                        </div>
                    </div>
                    <div id="kpiTopColleges"><div class="text-muted small fst-italic">Loading…</div></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #f59e0b !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-muted small fw-semibold mb-0">Top Courses</p>
                        <div class="rounded-2 bg-warning-subtle d-flex align-items-center justify-content-center"
                             style="width:30px;height:30px;flex-shrink:0;">
                            <i class="bi bi-journal-bookmark-fill text-warning" style="font-size:.8rem;"></i>
                        </div>
                    </div>
                    <div id="kpiTopCourses"><div class="text-muted small fst-italic">Loading…</div></div>
                </div>
            </div>
        </div>

    </div>

    <!-- TABS -->
    <ul class="nav nav-tabs mb-0" id="analyticsTabs">
        <li class="nav-item">
            <button class="nav-link d-flex align-items-center gap-2 small fw-semibold" data-tab="logs">
                <i class="bi bi-journal-text"></i>Logs
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link active d-flex align-items-center gap-2 small fw-semibold" data-tab="users">
                <i class="bi bi-people-fill"></i>Users
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link d-flex align-items-center gap-2 small fw-semibold" data-tab="colleges">
                <i class="bi bi-building-fill"></i>Colleges
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link d-flex align-items-center gap-2 small fw-semibold" data-tab="courses">
                <i class="bi bi-journal-bookmark-fill"></i>Courses
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link d-flex align-items-center gap-2 small fw-semibold" data-tab="demographics">
                <i class="bi bi-bar-chart-fill"></i>Demographics
            </button>
        </li>
    </ul>

    <!-- TAB CONTENT -->
    <div class="card border-0 shadow-sm" style="border-top-left-radius:0;">
        <div class="card-body p-4" id="tabContent">
            <div class="text-center py-5 text-muted">
                <i class="bi bi-bar-chart-line fs-1 d-block mb-3 opacity-25"></i>
                <p class="mb-0">Select a date range to view analytics.</p>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted" id="lastUpdatedLabel">
            <i class="fas fa-sync-alt me-1"></i>Last updated: —
        </small>
        <small class="text-muted"><i class="fas fa-database me-1"></i>Library System</small>
    </div>

</div>

<?php include '../modalContainer.php'; ?>
<?php include 'LibModals.php'; ?>

<script>
$(function () {

// CONFIG
const BACKEND_TAB  = "backend/bk_LibraryMenu/bk_tabReports.php";
const BACKEND_VIEW = "backend/bk_LibraryMenu/bk_viewReports.php";
const DEFAULT_DAYS = 7;

const TAB_LABELS = {
    logs: "Logs", users: "Users", colleges: "Colleges",
    courses: "Courses", demographics: "Demographics",
};

const TAB_REQUESTS = {
    logs: "getTabLogs", users: "getTabUsers", colleges: "getTabColleges",
    courses: "getTabCourses", demographics: "getTabDemographics",
};

const VIEW_REQUESTS = {
    logs: "viewAllLogs", users: "viewAllUsers", colleges: "viewAllColleges",
    courses: "viewAllCourses", demographics: "viewAllDemographics",
};

const COLORS = {
    rankCheckins: ["rgba(59,130,246,0.88)",  "rgba(99,102,241,0.88)",  "rgba(139,92,246,0.88)"],
    rankDuration: ["rgba(16,185,129,0.88)",  "rgba(20,184,166,0.88)",  "rgba(8,145,178,0.88)"],
    visitorType:  ["rgba(59,130,246,0.88)",  "rgba(16,185,129,0.88)",  "rgba(245,158,11,0.88)",  "rgba(100,116,139,0.88)"],
    sex:          ["rgba(59,130,246,0.88)",  "rgba(239,68,68,0.88)",   "rgba(100,116,139,0.88)"],
    course:       ["rgba(59,130,246,0.82)",  "rgba(16,185,129,0.82)",  "rgba(245,158,11,0.82)",  "rgba(139,92,246,0.82)", "rgba(239,68,68,0.82)", "rgba(20,184,166,0.82)", "rgba(100,116,139,0.82)"],
};

const EXPORT_LIBS = {
    jspdf:     "libs/jspdf.umd.min.js",
    autotable: "libs/jspdf.plugin.autotable.min.js",
    exceljs:   "libs/exceljs.min.js",
};

const OFFSCREEN = { barW: 900, barH: 220, donutW: 500, donutH: 380 };

const CHART_TOOLTIP = {
    backgroundColor: "rgba(15,23,42,0.92)", titleColor: "#f8fafc",
    bodyColor: "#94a3b8", borderColor: "rgba(148,163,184,0.15)",
    borderWidth: 1, padding: 10, cornerRadius: 6,
};

const EXCEL = {
    fill: {
        title:  { type: "pattern", pattern: "solid", fgColor: { argb: "FF111827" } },
        meta:   { type: "pattern", pattern: "solid", fgColor: { argb: "FFf3f4f6" } },
        header: { type: "pattern", pattern: "solid", fgColor: { argb: "FF059669" } },
        white:  { type: "pattern", pattern: "solid", fgColor: { argb: "FFFFFFFF" } },
        zebra:  { type: "pattern", pattern: "solid", fgColor: { argb: "FFf0fdf4" } },
    },
    border: {
        header: { top: { style: "thin", color: { argb: "FF047857" } }, bottom: { style: "thin", color: { argb: "FF047857" } }, left: { style: "thin", color: { argb: "FF047857" } }, right: { style: "thin", color: { argb: "FF047857" } } },
        data:   { top: { style: "hair", color: { argb: "FFe5e7eb" } }, bottom: { style: "hair", color: { argb: "FFe5e7eb" } }, left: { style: "hair", color: { argb: "FFe5e7eb" } }, right: { style: "hair", color: { argb: "FFe5e7eb" } } },
    },
    align: {
        center: { horizontal: "center", vertical: "middle" },
        left:   { horizontal: "left",   vertical: "middle" },
        right:  { horizontal: "right",  vertical: "middle" },
    },
};

// STATE
let activeTab       = "logs";
let pendingRequest  = null;
let viewAllTab      = "logs";
let viewAllPage     = 1;
let cachedResponses = {};
let chartInstances  = {};

// SPINNER
function showSpinner() { $("#loadingSpinner").stop(true).css("display", "flex").hide().fadeIn(150); }
function hideSpinner() { $("#loadingSpinner").fadeOut(200); }

// FILTERS
function getFilters() {
    return {
        startDate:      $("#startDate").val()            || "",
        endDate:        $("#endDate").val()              || "",
        classification: $("#classificationFilter").val() || "",
        library:        $("#libraryFilter").val()        || "",
    };
}

function hasDateRange() {
    return !!($("#startDate").val() && $("#endDate").val());
}

function getDateLabel() {
    return `${$("#startDate").val() || "—"} to ${$("#endDate").val() || "—"}`;
}

function setDefaultDates() {
    if ($("#startDate").val()) return;
    const today = new Date();
    const start = new Date(today);
    start.setDate(today.getDate() - DEFAULT_DAYS);
    $("#startDate").val(start.toISOString().split("T")[0]);
    $("#endDate").val(today.toISOString().split("T")[0]);
}

// CHARTS
function destroyChart(chartId) {
    if (chartInstances[chartId]) {
        chartInstances[chartId].destroy();
        delete chartInstances[chartId];
    }
}

function drawBarChart(chartId, entries, colors, unitLabel) {
    const canvas = document.getElementById(chartId);
    if (!canvas) return;
    destroyChart(chartId);
    chartInstances[chartId] = new Chart(canvas, {
        type: "bar",
        data: {
            labels:   entries.map(entry => entry.label),
            datasets: [{
                label:           unitLabel,
                data:            entries.map(entry => entry.value),
                backgroundColor: colors.slice(0, entries.length),
                borderRadius: 5, borderSkipped: false, barThickness: 36,
            }],
        },
        options: {
            indexAxis: "y", responsive: true, maintainAspectRatio: false,
            animation: { duration: 500, easing: "easeOutQuart" },
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...CHART_TOOLTIP,
                    callbacks: { label: context => `  ${unitLabel}: ${context.parsed.x.toLocaleString()}` },
                },
            },
            scales: {
                x: { beginAtZero: true, grid: { color: "rgba(0,0,0,0.04)" }, ticks: { color: "#6b7280", font: { size: 10 } } },
                y: { grid: { display: false },                               ticks: { color: "#374151", font: { size: 12 }, padding: 8 } },
            },
            layout: { padding: { right: 8 } },
        },
    });
}

function drawDonutChart(chartId, labels, values, colors, centerLabel) {
    const canvas = document.getElementById(chartId);
    if (!canvas) return;
    destroyChart(chartId);
    const total = values.reduce((sum, value) => sum + value, 0);
    chartInstances[chartId] = new Chart(canvas, {
        type: "doughnut",
        data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: "#ffffff", hoverOffset: 6 }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            animation: { duration: 600, easing: "easeInOutQuart" }, cutout: "65%",
            plugins: {
                legend: {
                    position: "bottom",
                    labels: {
                        color: "#374151", font: { size: 11 }, padding: 12,
                        usePointStyle: true, pointStyle: "circle",
                        generateLabels: chart => chart.data.labels.map((label, index) => ({
                            text:        `${label} (${(chart.data.datasets[0].data[index] || 0).toLocaleString()})`,
                            fillStyle:   chart.data.datasets[0].backgroundColor[index],
                            strokeStyle: chart.data.datasets[0].backgroundColor[index],
                            hidden: false, index, pointStyle: "circle",
                        })),
                    },
                },
                tooltip: {
                    ...CHART_TOOLTIP,
                    callbacks: {
                        label: context => ` ${context.label}: ${context.parsed.toLocaleString()} (${total > 0 ? (context.parsed / total * 100).toFixed(1) : 0}%)`,
                    },
                },
            },
        },
        plugins: [{
            id: `centerText_${chartId}`,
            afterDraw({ ctx: chartContext, chartArea }) {
                if (!chartArea) return;
                const centerX = (chartArea.left + chartArea.right) / 2;
                const centerY = (chartArea.top  + chartArea.bottom) / 2;
                chartContext.save();
                chartContext.textAlign = "center"; chartContext.textBaseline = "middle";
                chartContext.font = "bold 22px sans-serif"; chartContext.fillStyle = "#111827";
                chartContext.fillText(total.toLocaleString(), centerX, centerY - 10);
                chartContext.font = "12px sans-serif"; chartContext.fillStyle = "#6b7280";
                chartContext.fillText(centerLabel, centerX, centerY + 14);
                chartContext.restore();
            },
        }],
    });
}

// PAGINATION — builds prev/next/numbered page controls
function buildPagerHtml(currentPage, totalPages, totalRowCount, pageSize) {
    const windowSize  = 5;
    const windowStart = Math.max(1, Math.min(currentPage - Math.floor(windowSize / 2), totalPages - windowSize + 1));
    const windowEnd   = Math.min(windowStart + windowSize - 1, totalPages);
    const isFirstPage = currentPage === 1;
    const isLastPage  = currentPage === totalPages;
    const fromRecord  = (currentPage - 1) * pageSize + 1;
    const toRecord    = Math.min(currentPage * pageSize, totalRowCount);

    const pageButton = (label, targetPage, isDisabled, isActive) =>
        `<li class="page-item ${isDisabled ? "disabled" : ""} ${isActive ? "active" : ""}">
            <a class="page-link" href="#" data-p="${targetPage}">${label}</a>
         </li>`;

    let pageItems = pageButton("«", 1, isFirstPage, false) + pageButton("‹", currentPage - 1, isFirstPage, false);
    for (let pageNum = windowStart; pageNum <= windowEnd; pageNum++) {
        pageItems += pageButton(pageNum, pageNum, false, pageNum === currentPage);
    }
    pageItems += pageButton("›", currentPage + 1, isLastPage, false) + pageButton("»", totalPages, isLastPage, false);

    return `
        <small class="text-muted d-block text-center mb-1" style="font-size:.7rem;">
            Showing ${fromRecord}–${toRecord} of ${totalRowCount}
        </small>
        <ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap">${pageItems}</ul>
    `;
}

// INLINE PAGINATION — PHP pre-renders rows as HTML, stored in data-rows attribute.
// This slices and injects them client-side — no re-rendering needed.
function paginateInlineTable(cardId, tbodyId, pagerId) {
    const $card  = $(`#${cardId}`);
    const $tbody = $(`#${tbodyId}`);
    const $pager = $(`#${pagerId}`);
    if (!$card.length || !$tbody.length) return;

    let rows = [];
    try { rows = JSON.parse($card.attr("data-rows") || "[]"); } catch { return; }

    if (!rows.length) {
        $tbody.html('<tr><td colspan="99" class="text-center text-muted py-3">No data</td></tr>');
        return;
    }

    const pageSize   = parseInt($card.attr("data-per-page") || "10", 10);
    const totalPages = Math.ceil(rows.length / pageSize);
    let   currentPage = 1;

    function showPage(page) {
        currentPage      = Math.max(1, Math.min(page, totalPages));
        const startIndex = (currentPage - 1) * pageSize;
        $tbody.html(rows.slice(startIndex, startIndex + pageSize).join(""));

        if (totalPages > 1) {
            $pager.html(buildPagerHtml(currentPage, totalPages, rows.length, pageSize));
            $pager.find(".page-link").off("click").on("click", function (event) {
                event.preventDefault();
                const targetPage = parseInt($(this).data("p"), 10);
                if (!isNaN(targetPage) && targetPage > 0) showPage(targetPage);
            });
        } else {
            $pager.empty();
        }
    }

    showPage(1);
}

// TAB INITIALIZERS — called after tab HTML is injected to wire up charts and tables
function initLogsTab() {
    paginateInlineTable("allLogsCard", "allLogsTbody", "allLogsPager");
}

function initUsersTab(response) {
    drawBarChart("chartTopUserCheckins", response.chartTopCheckins, COLORS.rankCheckins, "Check-ins");
    drawBarChart("chartTopUserDuration",
        response.chartTopDuration.map(entry => ({ label: entry.label, value: Math.round(entry.value) })),
        COLORS.rankDuration, "Minutes");
    drawDonutChart("chartVisitorTypeDonut",
        Object.keys(response.classificationDistribution),
        Object.values(response.classificationDistribution),
        COLORS.visitorType, "Visitors");
    paginateInlineTable("checkinDetailsCard",  "checkinDetailsTbody",  "checkinDetailsPager");
    paginateInlineTable("durationDetailsCard", "durationDetailsTbody", "durationDetailsPager");
}

function initCollegesTab(response) {
    const checkinKeys  = Object.keys(response.top3CollegesCheckin);
    const durationKeys = Object.keys(response.top3CollegesDuration);
    drawDonutChart("chartCollegeCheckin",
        checkinKeys,
        checkinKeys.map(name => response.top3CollegesCheckin[name].count),
        checkinKeys.map(name => response.top3CollegesCheckin[name].color),
        "Visitors");
    drawDonutChart("chartCollegeDuration",
        durationKeys,
        durationKeys.map(name => Math.round(response.top3CollegesDuration[name].minutes)),
        durationKeys.map(name => response.top3CollegesDuration[name].color),
        "Minutes");
}

function initCoursesTab(response) {
    const { courseChartData } = response;
    if (!courseChartData.length) return;
    const labels = courseChartData.map(entry => entry.label);
    const colors = courseChartData.map((_, index) => COLORS.course[index % COLORS.course.length]);
    drawDonutChart("chartCoursesCheckin",  labels, courseChartData.map(entry => entry.checkins), colors, "Visitors");
    drawDonutChart("chartCoursesDuration", labels, courseChartData.map(entry => entry.duration), colors, "Minutes");
}

function initDemographicsTab(response) {
    drawDonutChart("chartSexDonut",
        Object.keys(response.sexDistribution),
        Object.values(response.sexDistribution),
        COLORS.sex, "Visitors");
}

function initActiveTab(tabName, response) {
    switch (tabName) {
        case "logs":         initLogsTab();           break;
        case "users":        initUsersTab(response);  break;
        case "colleges":     initCollegesTab(response); break;
        case "courses":      initCoursesTab(response); break;
        case "demographics": initDemographicsTab(response); break;
    }
}

// KPI — PHP pre-renders all KPI HTML, just inject it
function updateKpi(response) {
    $("#kpiTopStudents").html(response.kpiStudentsHtml);
    $("#kpiTopColleges").html(response.kpiCollegesHtml);
    $("#kpiTopCourses").html(response.kpiCoursesHtml);
    $("#lastUpdatedLabel").html(response.kpiLastUpdatedHtml);
}

// LOAD TAB
function loadTab(tabName) {
    activeTab = tabName;

    document.querySelectorAll("#analyticsTabs .nav-link").forEach(button =>
        button.classList.toggle("active", button.dataset.tab === tabName)
    );

    if (pendingRequest) pendingRequest.abort();
    showSpinner();

    pendingRequest = $.post(BACKEND_TAB, { request: TAB_REQUESTS[tabName], ...getFilters() })
        .done(function (raw) {
            hideSpinner();
            const response = parseJsonResponse(raw);

            if (!response || response.status !== "success") {
                $("#tabContent").html(`<div class="alert alert-danger m-3">${response?.message || "Error loading tab."}<br><pre class="mt-2 small text-muted">${typeof raw === "string" ? raw.substring(0, 300) : ""}</pre></div>`);
                return;
            }

            $("#tabContent").html(response.html);
            initActiveTab(tabName, response);
            updateKpi(response);
            cachedResponses[tabName] = response;
            preloadExportLibraries();
            $("#exportBtn").prop("disabled", false);
        })
        .fail(function (unusedXhr, status) {
            hideSpinner();
            if (status !== "abort")
                $("#tabContent").html('<div class="alert alert-danger m-3">Failed to load analytics. Please try again.</div>');
        });
}

// VIEW ALL MODAL
function loadViewAll(tabName, page) {
    showSpinner();

    $.post(BACKEND_VIEW, { request: VIEW_REQUESTS[tabName], page, ...getFilters() })
        .always(function (raw) {
            hideSpinner();
            const response = parseJsonResponse(raw);

            if (!response || response.status !== "success") {
                const serverOutput = typeof raw === "string" ? raw.substring(0, 500) : JSON.stringify(raw);
                $("#viewAllModalTitle").text("Error");
                $("#viewAllModalSubtitle").text("");
                $("#viewAllModalBody").html(`
                    <div class="alert alert-danger m-3">
                        <strong>Failed to load records.</strong>
                        ${response?.message ? `<br>${response.message}` : ""}
                        ${serverOutput ? `<pre class="mt-2 small text-muted mb-0" style="white-space:pre-wrap;">${serverOutput}</pre>` : ""}
                    </div>`);
                $("#viewAllModalFooter").html("");
                $("#viewAllModal").modal("show");
                return;
            }

            $("#viewAllModalTitle").text((TAB_LABELS[tabName] ?? "All") + " Records");
            $("#viewAllModalSubtitle").text(`Page ${response.page} of ${response.totalPages} · ${response.total} records`);
            $("#viewAllModalBody").html(response.tableHtml);
            $("#viewAllModalFooter").html(response.pagination);
            $("#viewAllModal").modal("show");
        });
}

// JSON PARSER — handles stray PHP output before the JSON (notices, whitespace, BOM)
function parseJsonResponse(raw) {
    if (raw && typeof raw === "object") return raw;
    if (typeof raw !== "string") return null;
    try {
        const jsonStart = raw.indexOf("{");
        return jsonStart !== -1 ? JSON.parse(raw.slice(jsonStart)) : null;
    } catch {
        return null;
    }
}

// EXPORT SCHEMA — defines headers and row structure for each tab's export
const EXPORT_SCHEMA = {
    logs: {
        label:            "Visit Logs",
        headers:          ["ID Number", "Name", "College", "Course", "Type", "Section", "Sex", "Check-in", "Check-out", "Agency / Organization", "Duration (min)"],
        columnAlignments: [null, null, null, null, null, null, null, null, null, null, "center"],
        rowMapper: (response) => (response.flatLogs || []).map(log => [
            log.id_number,
            log.name              || "—",
            log.college           || "—",
            log.course            || "—",
            log.classification    || "—",
            log.library           || "—",
            log.sex               || "—",
            log.checkin_formatted,
            log.checkout_formatted,
            log.agency_organization || "—",
            log.duration_minutes != null ? Math.round(log.duration_minutes) : "—",
        ]),
    },
    users: {
        label:   "Users",
        headers: ["ID Number", "Name", "College", "Course", "Type", "Library Section", "Check-ins", "Duration (min)", "Last Check-in"],
        rowMapper: (response) => (response.flatUsers || []).map(user => [
            user.display_label,
            user.name    || "—",
            user.college || "—",
            user.course  || "—",
            user.type,
            user.library || "—",
            user.checkins,
            Math.round(user.duration),
            user.last_checkin_formatted,
        ]),
    },
    colleges: {
        label:   "Colleges",
        headers: ["College", "Unique Visitors", "Total Duration (min)", "Last Check-in"],
        rowMapper: (response) => (response.flatColleges || []).map(college => [
            college.name, college.visitors, college.duration, college.last_checkin,
        ]),
    },
    courses: {
        label:   "Courses",
        headers: ["College", "Course", "Unique Visitors", "Duration (min)", "Last Check-in"],
        rowMapper: (response) => (response.flatCourses || []).map(course => [
            course.college, course.course, course.visitors, course.duration, course.last_checkin,
        ]),
    },
    demographics: {
        label:   "Demographics",
        headers: ["Sex", "Visitors", "% of Total"],
        rowMapper: (response) => (response.flatDemographics || []).map(entry => [
            entry.sex, entry.count, entry.pct + "%",
        ]),
    },
};

// FETCH MISSING TABS FOR EXPORT — loads any uncached tabs in parallel before exporting
async function fetchMissingTabsForExport(selectedTabs) {
    const unloadedTabs = selectedTabs.filter(tabName => !cachedResponses[tabName]);
    if (!unloadedTabs.length) return;

    await Promise.all(unloadedTabs.map(tabName =>
        new Promise(function (resolve) {
            $.post(BACKEND_TAB, { request: TAB_REQUESTS[tabName], ...getFilters() })
                .always(function (raw) {
                    const response = parseJsonResponse(raw);
                    if (response?.status === "success") cachedResponses[tabName] = response;
                    resolve();
                });
        })
    ));
}

// OFFSCREEN CHART — renders a Chart.js chart on a hidden canvas for PDF export
function buildOffscreenChart(type, labels, values, colors, unitLabel, title) {
    const isBar    = type === "bar";
    const canvasW  = isBar ? OFFSCREEN.barW  : OFFSCREEN.donutW;
    const canvasH  = isBar ? OFFSCREEN.barH  : OFFSCREEN.donutH;
    const total    = isBar ? 0 : values.reduce((sum, value) => sum + value, 0);
    const canvas   = Object.assign(document.createElement("canvas"), { width: canvasW, height: canvasH });

    const config = isBar ? {
        type: "bar",
        data: {
            labels,
            datasets: [{ label: unitLabel, data: values, backgroundColor: colors, borderRadius: 5, borderSkipped: false, barThickness: 50 }],
        },
        options: {
            indexAxis: "y", responsive: false, animation: false, devicePixelRatio: 2,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { color: "rgba(0,0,0,0.07)" }, ticks: { font: { size: 13 }, color: "#6b7280" } },
                y: { grid: { display: false },                               ticks: { font: { size: 14 }, color: "#1f2937", padding: 6 } },
            },
            layout: { padding: { left: 4, right: 20, top: 6, bottom: 6 } },
        },
    } : {
        type: "doughnut",
        data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 3, borderColor: "#fff", hoverOffset: 0 }] },
        options: {
            responsive: false, animation: false, cutout: "60%", devicePixelRatio: 2,
            plugins: {
                legend: {
                    position: "bottom",
                    labels: {
                        font: { size: 13 }, padding: 14, usePointStyle: true, pointStyle: "circle",
                        generateLabels: chart => chart.data.labels.map((label, index) => ({
                            text:        `${label}  (${(chart.data.datasets[0].data[index] || 0).toLocaleString()})`,
                            fillStyle:   chart.data.datasets[0].backgroundColor[index],
                            strokeStyle: chart.data.datasets[0].backgroundColor[index],
                            hidden: false, index, pointStyle: "circle",
                        })),
                    },
                },
            },
        },
        plugins: [{
            id: "offscreenCenterLabel",
            afterDraw({ ctx: chartContext, chartArea }) {
                if (!chartArea) return;
                const centerX = (chartArea.left + chartArea.right) / 2;
                const centerY = (chartArea.top  + chartArea.bottom) / 2;
                chartContext.save();
                chartContext.textAlign = "center"; chartContext.textBaseline = "middle";
                chartContext.font = "bold 34px sans-serif"; chartContext.fillStyle = "#111827";
                chartContext.fillText(total.toLocaleString(), centerX, centerY - 14);
                chartContext.font = "17px sans-serif"; chartContext.fillStyle = "#6b7280";
                chartContext.fillText(unitLabel, centerX, centerY + 18);
                chartContext.restore();
            },
        }],
    };

    const offscreenChart = new Chart(canvas, config);
    const imageDataUrl   = canvas.toDataURL("image/png");
    offscreenChart.destroy();
    return { imageDataUrl, label: title, type };
}

function buildChartsForTab(tabName, response) {
    switch (tabName) {
        case "logs": return [];

        case "users": {
            const { chartTopCheckins, chartTopDuration } = response;
            return [
                buildOffscreenChart("bar",   chartTopCheckins.map(e => e.label), chartTopCheckins.map(e => e.value),              COLORS.rankCheckins.slice(0, chartTopCheckins.length), "Check-ins", "Top Visitors by Check-ins"),
                buildOffscreenChart("bar",   chartTopDuration.map(e => e.label), chartTopDuration.map(e => Math.round(e.value)),   COLORS.rankDuration.slice(0, chartTopDuration.length), "Minutes",   "Top Visitors by Duration"),
                buildOffscreenChart("donut", Object.keys(response.classificationDistribution), Object.values(response.classificationDistribution), COLORS.visitorType, "Visitors", "Visitor Type Breakdown"),
            ];
        }

        case "colleges": {
            const checkinKeys  = Object.keys(response.top3CollegesCheckin);
            const durationKeys = Object.keys(response.top3CollegesDuration);
            return [
                buildOffscreenChart("donut", checkinKeys,  checkinKeys.map(name => response.top3CollegesCheckin[name].count),                checkinKeys.map(name => response.top3CollegesCheckin[name].color),  "Visitors", "Top Colleges by Check-ins"),
                buildOffscreenChart("donut", durationKeys, durationKeys.map(name => Math.round(response.top3CollegesDuration[name].minutes)), durationKeys.map(name => response.top3CollegesDuration[name].color), "Minutes",  "Top Colleges by Duration"),
            ];
        }

        case "courses": {
            const { courseChartData } = response;
            if (!courseChartData?.length) return [];
            const labels = courseChartData.map(entry => entry.label);
            const colors = courseChartData.map((_, index) => COLORS.course[index % COLORS.course.length]);
            return [
                buildOffscreenChart("donut", labels, courseChartData.map(entry => entry.checkins), colors, "Visitors", "Top Courses by Check-ins"),
                buildOffscreenChart("donut", labels, courseChartData.map(entry => entry.duration), colors, "Minutes",  "Top Courses by Duration"),
            ];
        }

        case "demographics":
            return [buildOffscreenChart("donut",
                Object.keys(response.sexDistribution),
                Object.values(response.sexDistribution),
                COLORS.sex, "Visitors", "Sex Distribution",
            )];

        default: return [];
    }
}

// SCRIPT LOADER — loads a JS file once and caches the promise so it's never loaded twice
const scriptLoadCache = {};

function loadScript(url) {
    if (scriptLoadCache[url]) return scriptLoadCache[url];
    scriptLoadCache[url] = new Promise(function (resolve, reject) {
        if (document.querySelector(`script[src="${url}"]`)) { setTimeout(resolve, 0); return; }
        const script   = document.createElement("script");
        script.src     = url;
        script.onload  = resolve;
        script.onerror = () => reject(new Error("Failed to load: " + url));
        document.head.appendChild(script);
    });
    return scriptLoadCache[url];
}

function preloadExportLibraries() {
    loadScript(EXPORT_LIBS.jspdf).then(() => loadScript(EXPORT_LIBS.autotable)).catch(() => {});
    loadScript(EXPORT_LIBS.exceljs).catch(() => {});
}

// FILE SAVER — uses File System Access API if available, falls back to anchor click
async function saveBlob(blob, filename, mimeType, extension) {
    if (window.showSaveFilePicker) {
        try {
            const fileHandle = await window.showSaveFilePicker({
                suggestedName: filename,
                types: [{ description: `${extension.toUpperCase()} File`, accept: { [mimeType]: ["." + extension] } }],
            });
            const writable = await fileHandle.createWritable();
            await writable.write(blob);
            await writable.close();
            return;
        } catch (error) {
            if (error.name === "AbortError") return;
        }
    }
    const url    = URL.createObjectURL(blob);
    const anchor = Object.assign(document.createElement("a"), { href: url, download: filename });
    document.body.appendChild(anchor);
    anchor.click();
    document.body.removeChild(anchor);
    setTimeout(() => URL.revokeObjectURL(url), 2000);
}

function buildExportFilename(tabs, extension) {
    const { startDate, endDate } = getFilters();
    return `LibraryReport_${tabs.length === 1 ? tabs[0] : "full"}_${startDate || "unknown"}_${endDate || "unknown"}.${extension}`;
}

// PDF EXPORT
async function runExportPDF(selectedTabs, responses) {
    const { jsPDF }    = window.jspdf;
    const pdf          = new jsPDF("l", "mm", "a4");
    const margin       = 16;
    const pageWidth    = pdf.internal.pageSize.getWidth();
    const pageHeight   = pdf.internal.pageSize.getHeight();
    const contentWidth = pageWidth - margin * 2;
    const maxDonutW    = 85;
    const chartGap     = 6;
    let   isFirstTab   = true;
    let   pageNumber   = 1;
    let   cursorY      = 0;

    const drawDivider = (posY) => { pdf.setDrawColor(226, 232, 240).setLineWidth(0.25); pdf.line(margin, posY, pageWidth - margin, posY); };
    const drawHeading = (text, posY) => { pdf.setFont("helvetica", "bold").setFontSize(8.5).setTextColor(17, 24, 39); pdf.text(text, margin, posY); };
    const drawCaption = (text, posX, posY, width, centered = false) => {
        pdf.setFont("helvetica", "normal").setFontSize(6.5).setTextColor(100, 116, 139);
        centered ? pdf.text(text, posX + width / 2, posY, { align: "center" }) : pdf.text(text, posX, posY);
    };
    const drawFooter = (pageNum) => {
        pdf.setFont("helvetica", "normal").setFontSize(7).setTextColor(148, 163, 184);
        pdf.text("Library Analytics Report   ·   Page " + pageNum, pageWidth / 2, pageHeight - 6, { align: "center" });
        pdf.setDrawColor(226, 232, 240).setLineWidth(0.2);
        pdf.line(margin, pageHeight - 10, pageWidth - margin, pageHeight - 10);
    };

    pdf.setFillColor(17, 24, 39);
    pdf.rect(0, 0, pageWidth, 18, "F");
    pdf.setFont("helvetica", "bold").setFontSize(11).setTextColor(255, 255, 255);
    pdf.text("Library Analytics Report", margin, 12);
    pdf.setFont("helvetica", "normal").setFontSize(8).setTextColor(148, 163, 184);
    pdf.text(selectedTabs.map(tabName => TAB_LABELS[tabName]).join(" · ") + "   ·   " + getDateLabel(), pageWidth - margin, 12, { align: "right" });

    cursorY = 24;
    pdf.setFont("helvetica", "normal").setFontSize(7.5).setTextColor(100, 116, 139);
    pdf.text("Generated: " + new Date().toLocaleString(), margin, cursorY);
    cursorY += 5; drawDivider(cursorY); cursorY += 6;

    for (const tabName of selectedTabs) {
        const tabResponse = responses[tabName];
        if (!tabResponse) continue;

        if (!isFirstTab) { pdf.addPage(); cursorY = margin; pageNumber++; }
        isFirstTab = false;

        const schema    = EXPORT_SCHEMA[tabName];
        if (!schema) continue;
        const tableRows = schema.rowMapper(tabResponse);

        pdf.setFillColor(248, 250, 252);
        pdf.rect(margin, cursorY - 2, contentWidth, 8, "F");
        pdf.setFont("helvetica", "bold").setFontSize(9.5).setTextColor(17, 24, 39);
        pdf.text(schema.label, margin + 3, cursorY + 4);
        cursorY += 12;

        const allCharts   = buildChartsForTab(tabName, tabResponse);
        const barCharts   = allCharts.filter(chart => chart.type === "bar");
        const donutCharts = allCharts.filter(chart => chart.type === "donut");

        if (allCharts.length) {
            drawHeading("Charts", cursorY); cursorY += 5;

            if (barCharts.length) {
                const barWidth  = (contentWidth - (barCharts.length - 1) * chartGap) / barCharts.length;
                const barHeight = barWidth * (OFFSCREEN.barH / OFFSCREEN.barW);
                barCharts.forEach(function (chart, index) {
                    const posX = margin + index * (barWidth + chartGap);
                    drawCaption(chart.label, posX, cursorY + 4, barWidth);
                    pdf.addImage(chart.imageDataUrl, "PNG", posX, cursorY + 6, barWidth, barHeight);
                });
                cursorY += barWidth * (OFFSCREEN.barH / OFFSCREEN.barW) + 12;
            }

            if (donutCharts.length) {
                const rawDonutW = (contentWidth - (donutCharts.length - 1) * chartGap) / donutCharts.length;
                const donutW    = Math.min(maxDonutW, rawDonutW);
                const donutH    = donutW * (OFFSCREEN.donutH / OFFSCREEN.donutW);
                const startX    = margin + (contentWidth - (donutCharts.length * donutW + (donutCharts.length - 1) * chartGap)) / 2;
                donutCharts.forEach(function (chart, index) {
                    const posX = startX + index * (donutW + chartGap);
                    drawCaption(chart.label, posX, cursorY + 4, donutW, true);
                    pdf.addImage(chart.imageDataUrl, "PNG", posX, cursorY + 6, donutW, donutH);
                });
                cursorY += donutH + 12;
            }

            drawDivider(cursorY); cursorY += 5;
        }

        if (cursorY + 20 > pageHeight - 14) { drawFooter(pageNumber); pdf.addPage(); pageNumber++; cursorY = margin; }

        drawHeading("Data Summary", cursorY);
        pdf.setFont("helvetica", "normal").setFontSize(7.5).setTextColor(100, 116, 139);
        pdf.text(tableRows.length + " records", pageWidth - margin, cursorY, { align: "right" });
        cursorY += 5;

        pdf.autoTable({
            head: [schema.headers], body: tableRows, startY: cursorY,
            styles:             { fontSize: 8, cellPadding: 3, lineColor: [226, 232, 240], lineWidth: 0.2 },
            headStyles:         { fillColor: [17, 24, 39], textColor: [255, 255, 255], fontStyle: "bold", fontSize: 8, cellPadding: 3.5 },
            alternateRowStyles: { fillColor: [248, 250, 252] },
            columnStyles:       { 0: { fontStyle: "bold" } },
            margin:             { left: margin, right: margin },
            tableLineColor:     [226, 232, 240], tableLineWidth: 0.2,
            didDrawPage:        pageHook => drawFooter(pageHook.pageNumber),
        });

        cursorY = pdf.lastAutoTable.finalY + 8;
    }

    await saveBlob(
        new Blob([pdf.output("arraybuffer")], { type: "application/pdf" }),
        buildExportFilename(selectedTabs, "pdf"), "application/pdf", "pdf"
    );
}

// EXCEL EXPORT
async function runExportExcel(selectedTabs, responses) {
    const workbook  = new window.ExcelJS.Workbook();
    const dateRange = getDateLabel();

    for (const tabName of selectedTabs) {
        const tabResponse = responses[tabName];
        if (!tabResponse) continue;

        const schema   = EXPORT_SCHEMA[tabName];
        if (!schema) continue;
        const dataRows = schema.rowMapper(tabResponse);
        const colCount = schema.headers.length;
        const sheet    = workbook.addWorksheet(schema.label.substring(0, 31));
        sheet.views    = [{ state: "frozen", ySplit: 5 }];

        const addMetaRow = (text, rowHeight, fontOptions, fillKey) => {
            sheet.addRow([text]);
            const worksheetRow = sheet.lastRow;
            worksheetRow.height = rowHeight;
            worksheetRow.getCell(1).font      = fontOptions;
            worksheetRow.getCell(1).fill      = EXCEL.fill[fillKey];
            worksheetRow.getCell(1).alignment = EXCEL.align.center;
            sheet.mergeCells(worksheetRow.number, 1, worksheetRow.number, colCount);
            for (let colIndex = 2; colIndex <= colCount; colIndex++) worksheetRow.getCell(colIndex).fill = EXCEL.fill[fillKey];
        };

        addMetaRow(`Library Analytics Report — ${schema.label}`, 30, { bold: true, color: { argb: "FFFFFFFF" }, size: 14 }, "title");
        addMetaRow(`Period: ${dateRange}`,                        18, { color: { argb: "FF6b7280" }, size: 10 },             "meta");
        addMetaRow(`Generated: ${new Date().toLocaleString()}   ·   ${dataRows.length} records`, 16, { italic: true, color: { argb: "FF9ca3af" }, size: 9 }, "meta");
        sheet.addRow([]); sheet.lastRow.height = 6;

        sheet.addRow(schema.headers);
        sheet.lastRow.height = 22;
        sheet.lastRow.eachCell(function (cell) {
            cell.font = { bold: true, color: { argb: "FFFFFFFF" }, size: 10 };
            cell.fill = EXCEL.fill.header; cell.alignment = EXCEL.align.center; cell.border = EXCEL.border.header;
        });

        sheet.addRows(dataRows);
        const firstDataRowIndex = 6;
        for (let rowIndex = firstDataRowIndex; rowIndex < firstDataRowIndex + dataRows.length; rowIndex++) {
            const worksheetRow = sheet.getRow(rowIndex);
            const isZebra      = (rowIndex - firstDataRowIndex) % 2 !== 0;
            worksheetRow.height = 18;
            worksheetRow.eachCell({ includeEmpty: true }, function (cell, colNumber) {
                const alignOverride = schema.columnAlignments?.[colNumber - 1];
                cell.fill      = isZebra ? EXCEL.fill.zebra : EXCEL.fill.white;
                cell.border    = EXCEL.border.data;
                cell.font      = { size: 10 };
                cell.alignment = alignOverride === "center" ? EXCEL.align.center
                               : alignOverride === "right"  ? EXCEL.align.right
                               : alignOverride === "left"   ? EXCEL.align.left
                               : typeof cell.value === "number" ? EXCEL.align.right : EXCEL.align.left;
            });
        }

        schema.headers.forEach(function (header, index) {
            const maxLength = dataRows.reduce((max, row) => Math.max(max, String(row[index] ?? "").length), header.length);
            sheet.getColumn(index + 1).width = Math.min(50, maxLength + 4);
        });
    }

    const buffer = await workbook.xlsx.writeBuffer();
    await saveBlob(
        new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" }),
        buildExportFilename(selectedTabs, "xlsx"), "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "xlsx"
    );
}

// EVENTS
$(document).off(".analytics")
    .on("click.analytics", "#analyticsTabs .nav-link", function (event) {
        event.preventDefault();
        loadTab($(this).data("tab"));
    })
    .on("click.analytics", "#refreshBtn", function () {
        if (hasDateRange()) { cachedResponses = {}; loadTab(activeTab); }
    })
    .on("click.analytics", ".view-all-btn", function () {
        viewAllTab  = $(this).data("tab");
        viewAllPage = 1;
        loadViewAll(viewAllTab, viewAllPage);
    })
    .on("click.analytics", "#viewAllModalFooter .page-link", function (event) {
        event.preventDefault();
        const targetPage = parseInt($(this).data("page"), 10);
        if (!isNaN(targetPage)) { viewAllPage = targetPage; loadViewAll(viewAllTab, viewAllPage); }
    })
    .on("click.analytics", ".export-format-option", function () {
        $(".export-format-option").removeClass("active-format");
        $(this).addClass("active-format").find("input[type='radio']").prop("checked", true);
    })
    .on("show.bs.modal", "#exportModal", function () {
        const allCheckboxes = $("#exportSectionIndividual .export-section-check");
        $("#exportCheckAll").prop("checked", allCheckboxes.length === allCheckboxes.filter(":checked").length);
    })
    .on("change.analytics", "#exportCheckAll", function () {
        const isChecked = $(this).is(":checked");
        $("#exportSectionIndividual .export-section-check")
            .prop("checked", isChecked)
            .closest("label").toggleClass("opacity-50", !isChecked);
    })
    .on("change.analytics", "#exportSectionIndividual .export-section-check", function () {
        const allCheckboxes = $("#exportSectionIndividual .export-section-check");
        $("#exportCheckAll").prop("checked", allCheckboxes.length === allCheckboxes.filter(":checked").length);
    })
    .on("click.analytics", "#exportBtn", function () {
        if (!hasDateRange()) { alert("Please set a date range before exporting."); return; }
        $("#exportModal").modal("show");
    })
    .on("click.analytics", "#exportConfirmBtn", async function () {
        const selectedSections = $("#exportSectionIndividual .export-section-check:checked")
            .map(function () { return $(this).val(); }).get();

        if (!selectedSections.length) { alert("Please select at least one section to export."); return; }

        const exportFormat = $("input[name='exportFormat']:checked").val() || "xlsx";
        $("#exportModal").modal("hide");
        showSpinner();

        try {
            await fetchMissingTabsForExport(selectedSections);

            if (exportFormat === "pdf") {
                await loadScript(EXPORT_LIBS.jspdf);
                await loadScript(EXPORT_LIBS.autotable);
                await runExportPDF(selectedSections, cachedResponses);
            } else {
                await loadScript(EXPORT_LIBS.exceljs);
                await runExportExcel(selectedSections, cachedResponses);
            }
        } catch (error) {
            console.error("Export error:", error);
            alert("Export failed: " + error.message);
        } finally {
            hideSpinner();
        }
    });

$("#startDate, #endDate, #classificationFilter, #libraryFilter")
    .on("change.analytics", function () {
        if (hasDateRange()) { cachedResponses = {}; loadTab(activeTab); }
    });

// BOOT
setDefaultDates();
if (hasDateRange()) loadTab("logs");

});
</script>