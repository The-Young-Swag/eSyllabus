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
            <div class="row g-3 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1">Start Date</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-calendar text-muted"></i>
                        </span>
                        <input type="date" class="form-control border-start-0" id="startDate">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1">End Date</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-calendar-check text-muted"></i>
                        </span>
                        <input type="date" class="form-control border-start-0" id="endDate">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Classification</label>
                    <select class="form-select form-select-sm" id="classificationFilter">
                        <option value="All">All</option>
                        <option value="Student">Student</option>
                        <option value="Employee">Employee</option>
                        <option value="Guest">Guest</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Library Section</label>
                    <select class="form-select form-select-sm" id="libraryFilter">
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

// ── CONFIG ────────────────────────────────────────────────────────────────

const BACKEND = {
    tab:  'backend/bk_LibraryMenu/bk_tabReports.php',
    view: 'backend/bk_LibraryMenu/bk_viewReports.php',
};

const DEFAULT_DAYS = 7;

const TAB_LABELS = {
    logs: 'Logs', users: 'Users', colleges: 'Colleges',
    courses: 'Courses', demographics: 'Demographics',
};

const TAB_REQUEST_MAP = {
    logs:         'getTabLogs',
    users:        'getTabUsers',
    colleges:     'getTabColleges',
    courses:      'getTabCourses',
    demographics: 'getTabDemographics',
};

const VIEW_REQUEST_MAP = {
    logs:         'viewAllLogs',
    users:        'viewAllUsers',
    colleges:     'viewAllColleges',
    courses:      'viewAllCourses',
    demographics: 'viewAllDemographics',
};

// Called after tab HTML is injected — wires up charts and paginated tables.
const TAB_INITIALIZERS = {
    logs:         initLogsTab,
    users:        initUsersTab,
    colleges:     initCollegesTab,
    courses:      initCoursesTab,
    demographics: initDemographicsTab,
};

// autotable must load after jsPDF (it extends it) — sequential awaits are intentional.
const EXPORT_RUNNERS = {
    pdf: async (selectedTabs, responses) => {
        await loadScript(EXPORT_LIBS.jspdf);
        await loadScript(EXPORT_LIBS.autotable);
        await runExportPDF(selectedTabs, responses);
    },
    xlsx: async (selectedTabs, responses) => {
        await loadScript(EXPORT_LIBS.exceljs);
        await runExportExcel(selectedTabs, responses);
    },
};

const COLORS = {
    rankCheckins: ['rgba(59,130,246,0.88)', 'rgba(99,102,241,0.88)', 'rgba(139,92,246,0.88)'],
    rankDuration: ['rgba(16,185,129,0.88)', 'rgba(20,184,166,0.88)', 'rgba(8,145,178,0.88)'],
    visitorType:  ['rgba(59,130,246,0.88)', 'rgba(16,185,129,0.88)', 'rgba(245,158,11,0.88)', 'rgba(100,116,139,0.88)'],
    sex:          ['rgba(59,130,246,0.88)', 'rgba(239,68,68,0.88)',  'rgba(100,116,139,0.88)'],
    course:       ['rgba(59,130,246,0.82)', 'rgba(16,185,129,0.82)', 'rgba(245,158,11,0.82)', 'rgba(139,92,246,0.82)', 'rgba(239,68,68,0.82)', 'rgba(20,184,166,0.82)', 'rgba(100,116,139,0.82)'],
};

const EXPORT_LIBS = {
    jspdf:     'libs/jspdf.umd.min.js',
    autotable: 'libs/jspdf.plugin.autotable.min.js',
    exceljs:   'libs/exceljs.min.js',
};

const OFFSCREEN = { barW: 900, barH: 220, donutW: 500, donutH: 380 };

const CHART_TOOLTIP = {
    backgroundColor: 'rgba(15,23,42,0.92)', titleColor: '#f8fafc',
    bodyColor: '#94a3b8', borderColor: 'rgba(148,163,184,0.15)',
    borderWidth: 1, padding: 10, cornerRadius: 6,
};

const EXCEL = {
    fill: {
        title:  { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF111827' } },
        meta:   { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFf3f4f6' } },
        header: { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF059669' } },
        white:  { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFFFFF' } },
        zebra:  { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFf0fdf4' } },
    },
    border: {
        header: { top: { style: 'thin', color: { argb: 'FF047857' } }, bottom: { style: 'thin', color: { argb: 'FF047857' } }, left: { style: 'thin', color: { argb: 'FF047857' } }, right: { style: 'thin', color: { argb: 'FF047857' } } },
        data:   { top: { style: 'hair', color: { argb: 'FFe5e7eb' } }, bottom: { style: 'hair', color: { argb: 'FFe5e7eb' } }, left: { style: 'hair', color: { argb: 'FFe5e7eb' } }, right: { style: 'hair', color: { argb: 'FFe5e7eb' } } },
    },
    align: {
        center: { horizontal: 'center', vertical: 'middle' },
        left:   { horizontal: 'left',   vertical: 'middle' },
        right:  { horizontal: 'right',  vertical: 'middle' },
    },
};

// ── STATE ─────────────────────────────────────────────────────────────────

const State = {
    activeTab:      'logs',
    pendingRequest: null,
    viewAllTab:     'logs',
    viewAllPage:    1,
    responses:      {},   // keyed by tab name, cached after first successful load
};

// ── MODAL HELPER ──────────────────────────────────────────────────────────
// Bootstrap 4 uses $.fn.modal('show'/'hide').
// Centralise all modal calls here so there's one place to update if needed.

const Modal = {
    show: (id) => $(id).modal('show'),
    hide: (id) => $(id).modal('hide'),
};

// ── SPINNER ───────────────────────────────────────────────────────────────

const showSpinner = () => $('#loadingSpinner').stop(true).css('display', 'flex').hide().fadeIn(150);
const hideSpinner = () => $('#loadingSpinner').fadeOut(200);

// ── FILTERS ───────────────────────────────────────────────────────────────

const Filters = {
    get() {
        return {
            startDate:      $('#startDate').val()            || '',
            endDate:        $('#endDate').val()              || '',
            classification: $('#classificationFilter').val() || '',
            library:        $('#libraryFilter').val()        || '',
        };
    },
    hasRange:  () => !!($('#startDate').val() && $('#endDate').val()),
    dateLabel: () => `${$('#startDate').val() || '—'} to ${$('#endDate').val() || '—'}`,

    setDefaults() {
        if ($('#startDate').val()) return;
        const today = new Date();
        const start = new Date(today);
        start.setDate(today.getDate() - DEFAULT_DAYS);
        $('#startDate').val(start.toISOString().split('T')[0]);
        $('#endDate').val(today.toISOString().split('T')[0]);
    },
};

// ── CHART MANAGER ─────────────────────────────────────────────────────────

const ChartManager = {
    _instances: {},

    destroy(chartId) {
        this._instances[chartId]?.destroy();
        delete this._instances[chartId];
    },

    _register(chartId, config) {
        const canvas = document.getElementById(chartId);
        if (!canvas) return;
        this.destroy(chartId);
        this._instances[chartId] = new Chart(canvas, config);
    },

    bar(chartId, entries, colors, unitLabel) {
        this._register(chartId, {
            type: 'bar',
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
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                animation: { duration: 500, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...CHART_TOOLTIP,
                        callbacks: { label: context => `  ${unitLabel}: ${context.parsed.x.toLocaleString()}` },
                    },
                },
                scales: {
                    x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { color: '#6b7280', font: { size: 10 } } },
                    y: { grid: { display: false },                               ticks: { color: '#374151', font: { size: 12 }, padding: 8 } },
                },
                layout: { padding: { right: 8 } },
            },
        });
    },

    donut(chartId, labels, values, colors, centerLabel) {
        const total = values.reduce((sum, value) => sum + value, 0);
        this._register(chartId, {
            type: 'doughnut',
            data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: '#ffffff', hoverOffset: 6 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                animation: { duration: 600, easing: 'easeInOutQuart' }, cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#374151', font: { size: 11 }, padding: 12,
                            usePointStyle: true, pointStyle: 'circle',
                            generateLabels: chart => chart.data.labels.map((label, index) => ({
                                text:        `${label} (${(chart.data.datasets[0].data[index] || 0).toLocaleString()})`,
                                fillStyle:   chart.data.datasets[0].backgroundColor[index],
                                strokeStyle: chart.data.datasets[0].backgroundColor[index],
                                hidden: false, index, pointStyle: 'circle',
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
                    chartContext.textAlign = 'center'; chartContext.textBaseline = 'middle';
                    chartContext.font = 'bold 22px sans-serif'; chartContext.fillStyle = '#111827';
                    chartContext.fillText(total.toLocaleString(), centerX, centerY - 10);
                    chartContext.font = '12px sans-serif'; chartContext.fillStyle = '#6b7280';
                    chartContext.fillText(centerLabel, centerX, centerY + 14);
                    chartContext.restore();
                },
            }],
        });
    },
};

// ── PAGINATION ────────────────────────────────────────────────────────────
// Shared pager HTML builder — used by all inline tables.
// Renders: «  ‹  1  2  3  4  5  ›  »  (sliding window of 5 pages max)

function buildPagerHtml(currentPage, totalPages, totalRowCount, pageSize) {
    const windowSize  = 5;
    const windowStart = Math.max(1, Math.min(currentPage - Math.floor(windowSize / 2), totalPages - windowSize + 1));
    const windowEnd   = Math.min(windowStart + windowSize - 1, totalPages);
    const isFirstPage = currentPage === 1;
    const isLastPage  = currentPage === totalPages;
    const fromRecord  = (currentPage - 1) * pageSize + 1;
    const toRecord    = Math.min(currentPage * pageSize, totalRowCount);

    const pageItem = (label, targetPage, isDisabled, isActive) =>
        `<li class="page-item ${isDisabled ? 'disabled' : ''} ${isActive ? 'active' : ''}">
            <a class="page-link" href="#" data-p="${targetPage}">${label}</a>
         </li>`;

    let pageItems = pageItem('«', 1, isFirstPage, false) + pageItem('‹', currentPage - 1, isFirstPage, false);
    for (let pageNum = windowStart; pageNum <= windowEnd; pageNum++) {
        pageItems += pageItem(pageNum, pageNum, false, pageNum === currentPage);
    }
    pageItems += pageItem('›', currentPage + 1, isLastPage, false) + pageItem('»', totalPages, isLastPage, false);

    return `
        <small class="text-muted d-block text-center mb-1" style="font-size:.7rem;">
            Showing ${fromRecord}–${toRecord} of ${totalRowCount}
        </small>
        <ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap">${pageItems}</ul>
    `;
}

// ── INLINE PAGINATION ─────────────────────────────────────────────────────
// PHP pre-renders rows as HTML strings and JSON-encodes them into data-rows.
// This function slices and injects them — no client-side row rendering needed.

function paginateInlineTable(cardId, tbodyId, pagerId) {
    const $card  = $(`#${cardId}`);
    const $tbody = $(`#${tbodyId}`);
    const $pager = $(`#${pagerId}`);
    if (!$card.length || !$tbody.length) return;

    let rows = [];
    try { rows = JSON.parse($card.attr('data-rows') || '[]'); } catch { return; }

    if (!rows.length) {
        $tbody.html('<tr><td colspan="99" class="text-center text-muted py-3">No data</td></tr>');
        return;
    }

    const pageSize   = parseInt($card.attr('data-per-page') || '10', 10);
    const totalPages = Math.ceil(rows.length / pageSize);
    let   currentPage = 1;

    function showPage(page) {
        currentPage      = Math.max(1, Math.min(page, totalPages));
        const startIndex = (currentPage - 1) * pageSize;
        $tbody.html(rows.slice(startIndex, startIndex + pageSize).join(''));

        if (totalPages > 1) {
            $pager.html(buildPagerHtml(currentPage, totalPages, rows.length, pageSize));
            $pager.find('.page-link').off('click').on('click', function (event) {
                event.preventDefault();
                const targetPage = parseInt($(this).data('p'), 10);
                if (!isNaN(targetPage) && targetPage > 0) showPage(targetPage);
            });
        } else {
            $pager.empty();
        }
    }

    showPage(1);
}

// ── TAB INITIALIZERS ──────────────────────────────────────────────────────

function initLogsTab() {
    paginateInlineTable('allLogsCard', 'allLogsTbody', 'allLogsPager');
}

function initUsersTab(response) {
    ChartManager.bar('chartTopUserCheckins', response.chartTopCheckins, COLORS.rankCheckins, 'Check-ins');
    ChartManager.bar('chartTopUserDuration',
        response.chartTopDuration.map(entry => ({ label: entry.label, value: Math.round(entry.value) })),
        COLORS.rankDuration, 'Minutes');
    ChartManager.donut('chartVisitorTypeDonut',
        Object.keys(response.classificationDistribution),
        Object.values(response.classificationDistribution),
        COLORS.visitorType, 'Visitors');

    paginateInlineTable('checkinDetailsCard',  'checkinDetailsTbody',  'checkinDetailsPager');
    paginateInlineTable('durationDetailsCard', 'durationDetailsTbody', 'durationDetailsPager');
}

function initCollegesTab(response) {
    const checkinKeys  = Object.keys(response.top3CollegesCheckin);
    const durationKeys = Object.keys(response.top3CollegesDuration);

    ChartManager.donut('chartCollegeCheckin',
        checkinKeys,
        checkinKeys.map(name => response.top3CollegesCheckin[name].count),
        checkinKeys.map(name => response.top3CollegesCheckin[name].color),
        'Visitors');

    ChartManager.donut('chartCollegeDuration',
        durationKeys,
        durationKeys.map(name => Math.round(response.top3CollegesDuration[name].minutes)),
        durationKeys.map(name => response.top3CollegesDuration[name].color),
        'Minutes');
}

function initCoursesTab(response) {
    const { courseChartData } = response;
    if (!courseChartData.length) return;
    const labels = courseChartData.map(entry => entry.label);
    const colors = courseChartData.map((_, index) => COLORS.course[index % COLORS.course.length]);
    ChartManager.donut('chartCoursesCheckin',  labels, courseChartData.map(entry => entry.checkins), colors, 'Visitors');
    ChartManager.donut('chartCoursesDuration', labels, courseChartData.map(entry => entry.duration), colors, 'Minutes');
}

function initDemographicsTab(response) {
    ChartManager.donut('chartSexDonut',
        Object.keys(response.sexDistribution),
        Object.values(response.sexDistribution),
        COLORS.sex, 'Visitors');
}

// ── KPI UPDATER ───────────────────────────────────────────────────────────
// PHP pre-renders all KPI HTML via renderKpiSections() — just inject it here.

function updateKpi(response) {
    $('#kpiTopStudents').html(response.kpiStudentsHtml);
    $('#kpiTopColleges').html(response.kpiCollegesHtml);
    $('#kpiTopCourses').html(response.kpiCoursesHtml);
    $('#lastUpdatedLabel').html(response.kpiLastUpdatedHtml);
}

// ── TAB LOADER ────────────────────────────────────────────────────────────

function loadTab(tabName) {
    State.activeTab = tabName;
    document.querySelectorAll('#analyticsTabs .nav-link').forEach(button =>
        button.classList.toggle('active', button.dataset.tab === tabName)
    );

    State.pendingRequest?.abort();
    showSpinner();

    State.pendingRequest = $.post(BACKEND.tab, { request: TAB_REQUEST_MAP[tabName], ...Filters.get() })
        .done(raw => {
            hideSpinner();
            const response = parseJsonResponse(raw);
            if (!response || response.status !== 'success') {
                $('#tabContent').html(`<div class="alert alert-danger m-3">${response?.message || 'Error loading tab.'}<br><pre class="mt-2 small text-muted">${typeof raw === 'string' ? raw.substring(0, 300) : ''}</pre></div>`);
                return;
            }
            $('#tabContent').html(response.html);
            TAB_INITIALIZERS[tabName]?.(response);
            updateKpi(response);
            State.responses[tabName] = response;
            preloadExportLibraries();
            $('#exportBtn').prop('disabled', false);
        })
        .fail((unusedXhr, status) => {
            hideSpinner();
            if (status !== 'abort')
                $('#tabContent').html('<div class="alert alert-danger m-3">Failed to load analytics. Please try again.</div>');
        });
}

// ── VIEW ALL ──────────────────────────────────────────────────────────────
// Always opens the modal after the request completes — success or failure.
// Parsing is done manually so stray PHP output doesn't silently kill the call.

function loadViewAll(tabName, page) {
    showSpinner();

    $.post(BACKEND.view, { request: VIEW_REQUEST_MAP[tabName], page, ...Filters.get() })
        .always(raw => {
            hideSpinner();
            const response = parseJsonResponse(raw);

            if (!response || response.status !== 'success') {
                const serverOutput = typeof raw === 'string' ? raw.substring(0, 500) : JSON.stringify(raw);
                $('#viewAllModalTitle').text('Error');
                $('#viewAllModalSubtitle').text('');
                $('#viewAllModalBody').html(`
                    <div class="alert alert-danger m-3">
                        <strong>Failed to load records.</strong>
                        ${response?.message ? `<br>${response.message}` : ''}
                        ${serverOutput ? `<pre class="mt-2 small text-muted mb-0" style="white-space:pre-wrap;">${serverOutput}</pre>` : ''}
                    </div>`);
                $('#viewAllModalFooter').html('');
                Modal.show('#viewAllModal');
                return;
            }

            $('#viewAllModalTitle').text((TAB_LABELS[tabName] ?? 'All') + ' Records');
            $('#viewAllModalSubtitle').text(`Page ${response.page} of ${response.totalPages} · ${response.total} records`);
            $('#viewAllModalBody').html(response.tableHtml);
            $('#viewAllModalFooter').html(response.pagination);
            Modal.show('#viewAllModal');
        });
}

// ── JSON PARSER ───────────────────────────────────────────────────────────
// $.post auto-parses when Content-Type is correct, but returns a plain string
// if there's any stray output (PHP notice, BOM, whitespace) before the JSON.
// This handles both cases so the modal always shows something useful.

function parseJsonResponse(raw) {
    if (raw && typeof raw === 'object') return raw;   // already parsed by jQuery
    if (typeof raw !== 'string') return null;
    try {
        // Strip any stray output before the first '{' (PHP notices, whitespace, etc.)
        const jsonStart = raw.indexOf('{');
        return jsonStart !== -1 ? JSON.parse(raw.slice(jsonStart)) : null;
    } catch {
        return null;
    }
}

// ── EXPORT SCHEMA ─────────────────────────────────────────────────────────
// rowMappers read from PHP flat arrays — all values and dates pre-computed server-side.

const EXPORT_SCHEMA = {
    logs: {
        label:            'Visit Logs',
        headers:          ['ID Number', 'Name', 'College', 'Course', 'Type', 'Section', 'Sex', 'Check-in', 'Check-out', 'Agency / Organization', 'Duration (min)'],
        columnAlignments: [null, null, null, null, null, null, null, null, null, null, 'center'],
        rowMapper: (response) => (response.flatLogs || []).map(log => [
            log.id_number,
            log.name              || '—',
            log.college           || '—',
            log.course            || '—',
            log.classification    || '—',
            log.library           || '—',
            log.sex               || '—',
            log.checkin_formatted,
            log.checkout_formatted,
            log.agency_organization || '—',
            log.duration_minutes != null ? Math.round(log.duration_minutes) : '—',
        ]),
    },
    users: {
        label:   'Users',
        headers: ['ID Number', 'Name', 'College', 'Course', 'Type', 'Library Section', 'Check-ins', 'Duration (min)', 'Last Check-in'],
        rowMapper: (response) => (response.flatUsers || []).map(user => [
            user.display_label,
            user.name    || '—',
            user.college || '—',
            user.course  || '—',
            user.type,
            user.library || '—',
            user.checkins,
            Math.round(user.duration),
            user.last_checkin_formatted,
        ]),
    },
    colleges: {
        label:   'Colleges',
        headers: ['College', 'Unique Visitors', 'Total Duration (min)', 'Last Check-in'],
        rowMapper: (response) => (response.flatColleges || []).map(college => [
            college.name, college.visitors, college.duration, college.last_checkin,
        ]),
    },
    courses: {
        label:   'Courses',
        headers: ['College', 'Course', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
        rowMapper: (response) => (response.flatCourses || []).map(course => [
            course.college, course.course, course.visitors, course.duration, course.last_checkin,
        ]),
    },
    demographics: {
        label:   'Demographics',
        headers: ['Sex', 'Visitors', '% of Total'],
        rowMapper: (response) => (response.flatDemographics || []).map(entry => [
            entry.sex, entry.count, entry.pct + '%',
        ]),
    },
};

// ── EXPORT: FETCH MISSING TABS ────────────────────────────────────────────
// Fetches any selected tab whose data isn't cached yet — in parallel —
// so exports always include every selected section, not just visited tabs.

async function fetchMissingTabsForExport(selectedTabs) {
    const unloadedTabs = selectedTabs.filter(tabName => !State.responses[tabName]);
    if (!unloadedTabs.length) return;

    await Promise.all(unloadedTabs.map(tabName =>
        new Promise(resolve => {
            $.post(BACKEND.tab, { request: TAB_REQUEST_MAP[tabName], ...Filters.get() })
                .always(raw => {
                    const response = parseJsonResponse(raw);
                    if (response?.status === 'success') State.responses[tabName] = response;
                    resolve();
                });
        })
    ));
}

// ── OFFSCREEN CHART BUILDER ───────────────────────────────────────────────
// Renders a Chart.js chart onto an offscreen canvas for PDF export only.
// Never shown in the DOM.

function buildOffscreenChart(type, labels, values, colors, unitLabel, title) {
    const isBar      = type === 'bar';
    const canvasW    = isBar ? OFFSCREEN.barW  : OFFSCREEN.donutW;
    const canvasH    = isBar ? OFFSCREEN.barH  : OFFSCREEN.donutH;
    const donutTotal = isBar ? 0 : values.reduce((sum, value) => sum + value, 0);
    const canvas     = Object.assign(document.createElement('canvas'), { width: canvasW, height: canvasH });

    const config = isBar ? {
        type: 'bar',
        data: {
            labels,
            datasets: [{ label: unitLabel, data: values, backgroundColor: colors, borderRadius: 5, borderSkipped: false, barThickness: 50 }],
        },
        options: {
            indexAxis: 'y', responsive: false, animation: false, devicePixelRatio: 2,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.07)' }, ticks: { font: { size: 13 }, color: '#6b7280' } },
                y: { grid: { display: false },                               ticks: { font: { size: 14 }, color: '#1f2937', padding: 6 } },
            },
            layout: { padding: { left: 4, right: 20, top: 6, bottom: 6 } },
        },
    } : {
        type: 'doughnut',
        data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 3, borderColor: '#fff', hoverOffset: 0 }] },
        options: {
            responsive: false, animation: false, cutout: '60%', devicePixelRatio: 2,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { size: 13 }, padding: 14, usePointStyle: true, pointStyle: 'circle',
                        generateLabels: chart => chart.data.labels.map((label, index) => ({
                            text:        `${label}  (${(chart.data.datasets[0].data[index] || 0).toLocaleString()})`,
                            fillStyle:   chart.data.datasets[0].backgroundColor[index],
                            strokeStyle: chart.data.datasets[0].backgroundColor[index],
                            hidden: false, index, pointStyle: 'circle',
                        })),
                    },
                },
            },
        },
        plugins: [{
            id: 'offscreenCenterLabel',
            afterDraw({ ctx: chartContext, chartArea }) {
                if (!chartArea) return;
                const centerX = (chartArea.left + chartArea.right) / 2;
                const centerY = (chartArea.top  + chartArea.bottom) / 2;
                chartContext.save();
                chartContext.textAlign = 'center'; chartContext.textBaseline = 'middle';
                chartContext.font = 'bold 34px sans-serif'; chartContext.fillStyle = '#111827';
                chartContext.fillText(donutTotal.toLocaleString(), centerX, centerY - 14);
                chartContext.font = '17px sans-serif'; chartContext.fillStyle = '#6b7280';
                chartContext.fillText(unitLabel, centerX, centerY + 18);
                chartContext.restore();
            },
        }],
    };

    const offscreenChart = new Chart(canvas, config);
    const imageDataUrl   = canvas.toDataURL('image/png');
    offscreenChart.destroy();
    return { imageDataUrl, label: title, type };
}

function buildChartsForTab(tabName, response) {
    switch (tabName) {
        case 'logs': return [];

        case 'users': {
            const { chartTopCheckins, chartTopDuration } = response;
            return [
                buildOffscreenChart('bar',   chartTopCheckins.map(entry => entry.label), chartTopCheckins.map(entry => entry.value),              COLORS.rankCheckins.slice(0, chartTopCheckins.length), 'Check-ins', 'Top Visitors by Check-ins'),
                buildOffscreenChart('bar',   chartTopDuration.map(entry => entry.label), chartTopDuration.map(entry => Math.round(entry.value)),   COLORS.rankDuration.slice(0, chartTopDuration.length), 'Minutes',   'Top Visitors by Duration'),
                buildOffscreenChart('donut', Object.keys(response.classificationDistribution), Object.values(response.classificationDistribution), COLORS.visitorType,                                    'Visitors',  'Visitor Type Breakdown'),
            ];
        }

        case 'colleges': {
            const checkinKeys  = Object.keys(response.top3CollegesCheckin);
            const durationKeys = Object.keys(response.top3CollegesDuration);
            return [
                buildOffscreenChart('donut', checkinKeys,  checkinKeys.map(name => response.top3CollegesCheckin[name].count),                checkinKeys.map(name => response.top3CollegesCheckin[name].color),  'Visitors', 'Top Colleges by Check-ins'),
                buildOffscreenChart('donut', durationKeys, durationKeys.map(name => Math.round(response.top3CollegesDuration[name].minutes)), durationKeys.map(name => response.top3CollegesDuration[name].color), 'Minutes',  'Top Colleges by Duration'),
            ];
        }

        case 'courses': {
            const { courseChartData } = response;
            if (!courseChartData?.length) return [];
            const labels = courseChartData.map(entry => entry.label);
            const colors = courseChartData.map((_, index) => COLORS.course[index % COLORS.course.length]);
            return [
                buildOffscreenChart('donut', labels, courseChartData.map(entry => entry.checkins), colors, 'Visitors', 'Top Courses by Check-ins'),
                buildOffscreenChart('donut', labels, courseChartData.map(entry => entry.duration), colors, 'Minutes',  'Top Courses by Duration'),
            ];
        }

        case 'demographics':
            return [buildOffscreenChart('donut',
                Object.keys(response.sexDistribution),
                Object.values(response.sexDistribution),
                COLORS.sex, 'Visitors', 'Sex Distribution',
            )];

        default: return [];
    }
}

// ── SCRIPT LOADER ─────────────────────────────────────────────────────────

const _scriptCache = {};

function loadScript(url) {
    if (_scriptCache[url]) return _scriptCache[url];
    _scriptCache[url] = new Promise((resolve, reject) => {
        if (document.querySelector(`script[src="${url}"]`)) { setTimeout(resolve, 0); return; }
        const script   = document.createElement('script');
        script.src     = url;
        script.onload  = resolve;
        script.onerror = () => reject(new Error('Failed to load: ' + url));
        document.head.appendChild(script);
    });
    return _scriptCache[url];
}

function preloadExportLibraries() {
    loadScript(EXPORT_LIBS.jspdf).then(() => loadScript(EXPORT_LIBS.autotable)).catch(() => {});
    loadScript(EXPORT_LIBS.exceljs).catch(() => {});
}

async function saveBlob(blob, filename, mimeType, extension) {
    if (window.showSaveFilePicker) {
        try {
            const fileHandle = await window.showSaveFilePicker({
                suggestedName: filename,
                types: [{ description: `${extension.toUpperCase()} File`, accept: { [mimeType]: ['.' + extension] } }],
            });
            const writable = await fileHandle.createWritable();
            await writable.write(blob);
            await writable.close();
            return;
        } catch (error) {
            if (error.name === 'AbortError') return;
        }
    }
    // Fallback for browsers without the File System Access API (Firefox, older Safari).
    const url    = URL.createObjectURL(blob);
    const anchor = Object.assign(document.createElement('a'), { href: url, download: filename });
    document.body.appendChild(anchor);
    anchor.click();
    document.body.removeChild(anchor);
    setTimeout(() => URL.revokeObjectURL(url), 2000);
}

const buildExportFilename = (tabs, extension) => {
    const { startDate, endDate } = Filters.get();
    return `LibraryReport_${tabs.length === 1 ? tabs[0] : 'full'}_${startDate || 'unknown'}_${endDate || 'unknown'}.${extension}`;
};

// ── PDF EXPORT ────────────────────────────────────────────────────────────

async function runExportPDF(selectedTabs, responses) {
    const { jsPDF }     = window.jspdf;
    const pdf           = new jsPDF('l', 'mm', 'a4');
    const margin        = 16;
    const pageWidth     = pdf.internal.pageSize.getWidth();
    const pageHeight    = pdf.internal.pageSize.getHeight();
    const contentWidth  = pageWidth - margin * 2;
    const maxDonutWidth = 85;
    const chartGap      = 6;
    let   isFirstTab    = true;
    let   pageNumber    = 1;
    let   cursorY       = 0;

    const drawDivider = (posY) => { pdf.setDrawColor(226, 232, 240).setLineWidth(0.25); pdf.line(margin, posY, pageWidth - margin, posY); };
    const drawHeading = (text, posY) => { pdf.setFont('helvetica', 'bold').setFontSize(8.5).setTextColor(17, 24, 39); pdf.text(text, margin, posY); };
    const drawCaption = (text, posX, posY, width, centered = false) => {
        pdf.setFont('helvetica', 'normal').setFontSize(6.5).setTextColor(100, 116, 139);
        centered ? pdf.text(text, posX + width / 2, posY, { align: 'center' }) : pdf.text(text, posX, posY);
    };
    const drawFooter = (pageNum) => {
        pdf.setFont('helvetica', 'normal').setFontSize(7).setTextColor(148, 163, 184);
        pdf.text('Library Analytics Report   ·   Page ' + pageNum, pageWidth / 2, pageHeight - 6, { align: 'center' });
        pdf.setDrawColor(226, 232, 240).setLineWidth(0.2);
        pdf.line(margin, pageHeight - 10, pageWidth - margin, pageHeight - 10);
    };

    pdf.setFillColor(17, 24, 39);
    pdf.rect(0, 0, pageWidth, 18, 'F');
    pdf.setFont('helvetica', 'bold').setFontSize(11).setTextColor(255, 255, 255);
    pdf.text('Library Analytics Report', margin, 12);
    pdf.setFont('helvetica', 'normal').setFontSize(8).setTextColor(148, 163, 184);
    pdf.text(selectedTabs.map(tabName => TAB_LABELS[tabName]).join(' · ') + '   ·   ' + Filters.dateLabel(), pageWidth - margin, 12, { align: 'right' });

    cursorY = 24;
    pdf.setFont('helvetica', 'normal').setFontSize(7.5).setTextColor(100, 116, 139);
    pdf.text('Generated: ' + new Date().toLocaleString(), margin, cursorY);
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
        pdf.rect(margin, cursorY - 2, contentWidth, 8, 'F');
        pdf.setFont('helvetica', 'bold').setFontSize(9.5).setTextColor(17, 24, 39);
        pdf.text(schema.label, margin + 3, cursorY + 4);
        cursorY += 12;

        const allCharts   = buildChartsForTab(tabName, tabResponse);
        const barCharts   = allCharts.filter(chart => chart.type === 'bar');
        const donutCharts = allCharts.filter(chart => chart.type === 'donut');

        if (allCharts.length) {
            drawHeading('Charts', cursorY); cursorY += 5;

            if (barCharts.length) {
                const barWidth  = (contentWidth - (barCharts.length - 1) * chartGap) / barCharts.length;
                const barHeight = barWidth * (OFFSCREEN.barH / OFFSCREEN.barW);
                barCharts.forEach((chart, index) => {
                    const posX = margin + index * (barWidth + chartGap);
                    drawCaption(chart.label, posX, cursorY + 4, barWidth);
                    pdf.addImage(chart.imageDataUrl, 'PNG', posX, cursorY + 6, barWidth, barHeight);
                });
                cursorY += barWidth * (OFFSCREEN.barH / OFFSCREEN.barW) + 12;
            }

            if (donutCharts.length) {
                const rawDonutWidth = (contentWidth - (donutCharts.length - 1) * chartGap) / donutCharts.length;
                const donutWidth    = Math.min(maxDonutWidth, rawDonutWidth);
                const donutHeight   = donutWidth * (OFFSCREEN.donutH / OFFSCREEN.donutW);
                const startX        = margin + (contentWidth - (donutCharts.length * donutWidth + (donutCharts.length - 1) * chartGap)) / 2;
                donutCharts.forEach((chart, index) => {
                    const posX = startX + index * (donutWidth + chartGap);
                    drawCaption(chart.label, posX, cursorY + 4, donutWidth, true);
                    pdf.addImage(chart.imageDataUrl, 'PNG', posX, cursorY + 6, donutWidth, donutHeight);
                });
                cursorY += donutHeight + 12;
            }

            drawDivider(cursorY); cursorY += 5;
        }

        if (cursorY + 20 > pageHeight - 14) { drawFooter(pageNumber); pdf.addPage(); pageNumber++; cursorY = margin; }

        drawHeading('Data Summary', cursorY);
        pdf.setFont('helvetica', 'normal').setFontSize(7.5).setTextColor(100, 116, 139);
        pdf.text(tableRows.length + ' records', pageWidth - margin, cursorY, { align: 'right' });
        cursorY += 5;

        pdf.autoTable({
            head: [schema.headers], body: tableRows, startY: cursorY,
            styles:             { fontSize: 8, cellPadding: 3, lineColor: [226, 232, 240], lineWidth: 0.2 },
            headStyles:         { fillColor: [17, 24, 39], textColor: [255, 255, 255], fontStyle: 'bold', fontSize: 8, cellPadding: 3.5 },
            alternateRowStyles: { fillColor: [248, 250, 252] },
            columnStyles:       { 0: { fontStyle: 'bold' } },
            margin:             { left: margin, right: margin },
            tableLineColor:     [226, 232, 240], tableLineWidth: 0.2,
            didDrawPage:        pageHook => drawFooter(pageHook.pageNumber),
        });

        cursorY = pdf.lastAutoTable.finalY + 8;
    }

    await saveBlob(
        new Blob([pdf.output('arraybuffer')], { type: 'application/pdf' }),
        buildExportFilename(selectedTabs, 'pdf'), 'application/pdf', 'pdf'
    );
}

// ── EXCEL EXPORT ──────────────────────────────────────────────────────────

async function runExportExcel(selectedTabs, responses) {
    const workbook  = new window.ExcelJS.Workbook();
    const dateRange = Filters.dateLabel();

    for (const tabName of selectedTabs) {
        const tabResponse = responses[tabName];
        if (!tabResponse) continue;

        const schema   = EXPORT_SCHEMA[tabName];
        if (!schema) continue;
        const dataRows = schema.rowMapper(tabResponse);
        const colCount = schema.headers.length;
        const sheet    = workbook.addWorksheet(schema.label.substring(0, 31));
        sheet.views    = [{ state: 'frozen', ySplit: 5 }];

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

        addMetaRow(`Library Analytics Report — ${schema.label}`, 30, { bold: true, color: { argb: 'FFFFFFFF' }, size: 14 }, 'title');
        addMetaRow(`Period: ${dateRange}`,                        18, { color: { argb: 'FF6b7280' }, size: 10 },             'meta');
        addMetaRow(`Generated: ${new Date().toLocaleString()}   ·   ${dataRows.length} records`, 16, { italic: true, color: { argb: 'FF9ca3af' }, size: 9 }, 'meta');
        sheet.addRow([]); sheet.lastRow.height = 6;

        sheet.addRow(schema.headers);
        sheet.lastRow.height = 22;
        sheet.lastRow.eachCell(cell => {
            cell.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 10 };
            cell.fill = EXCEL.fill.header; cell.alignment = EXCEL.align.center; cell.border = EXCEL.border.header;
        });

        sheet.addRows(dataRows);
        const firstDataRowIndex = 6;
        for (let rowIndex = firstDataRowIndex; rowIndex < firstDataRowIndex + dataRows.length; rowIndex++) {
            const worksheetRow = sheet.getRow(rowIndex);
            const isZebra      = (rowIndex - firstDataRowIndex) % 2 !== 0;
            worksheetRow.height = 18;
            worksheetRow.eachCell({ includeEmpty: true }, (cell, colNumber) => {
                const alignOverride = schema.columnAlignments?.[colNumber - 1];
                cell.fill      = isZebra ? EXCEL.fill.zebra : EXCEL.fill.white;
                cell.border    = EXCEL.border.data;
                cell.font      = { size: 10 };
                cell.alignment = alignOverride === 'center' ? EXCEL.align.center
                               : alignOverride === 'right'  ? EXCEL.align.right
                               : alignOverride === 'left'   ? EXCEL.align.left
                               : typeof cell.value === 'number' ? EXCEL.align.right : EXCEL.align.left;
            });
        }

        schema.headers.forEach((header, index) => {
            const maxLength = dataRows.reduce((max, row) => Math.max(max, String(row[index] ?? '').length), header.length);
            sheet.getColumn(index + 1).width = Math.min(50, maxLength + 4);
        });
    }

    const buffer = await workbook.xlsx.writeBuffer();
    await saveBlob(
        new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }),
        buildExportFilename(selectedTabs, 'xlsx'), 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'xlsx'
    );
}

// ── EVENT BINDINGS ────────────────────────────────────────────────────────

$(document).off('.analytics')
    .on('click.analytics', '#analyticsTabs .nav-link', function (event) {
        event.preventDefault();
        loadTab($(this).data('tab'));
    })
    .on('click.analytics', '#refreshBtn', () => {
        if (Filters.hasRange()) { State.responses = {}; loadTab(State.activeTab); }
    })
    .on('click.analytics', '.view-all-btn', function () {
        State.viewAllTab  = $(this).data('tab');
        State.viewAllPage = 1;
        loadViewAll(State.viewAllTab, State.viewAllPage);
    })
    .on('click.analytics', '#viewAllModalFooter .page-link', function (event) {
        event.preventDefault();
        const targetPage = parseInt($(this).data('page'), 10);
        if (!isNaN(targetPage)) { State.viewAllPage = targetPage; loadViewAll(State.viewAllTab, State.viewAllPage); }
    })
    .on('click.analytics', '.export-format-option', function () {
        $('.export-format-option').removeClass('active-format');
        $(this).addClass('active-format').find('input[type="radio"]').prop('checked', true);
    })
    // Sync "All Tabs" state every time the export modal opens.
    .on('show.bs.modal', '#exportModal', function () {
        const allCheckboxes = $('#exportSectionIndividual .export-section-check');
        $('#exportCheckAll').prop('checked', allCheckboxes.length === allCheckboxes.filter(':checked').length);
    })
    .on('change.analytics', '#exportCheckAll', function () {
        const isChecked = $(this).is(':checked');
        $('#exportSectionIndividual .export-section-check')
            .prop('checked', isChecked)
            .closest('label').toggleClass('opacity-50', !isChecked);
    })
    .on('change.analytics', '#exportSectionIndividual .export-section-check', function () {
        const allCheckboxes = $('#exportSectionIndividual .export-section-check');
        $('#exportCheckAll').prop('checked', allCheckboxes.length === allCheckboxes.filter(':checked').length);
    })
    .on('click.analytics', '#exportBtn', function () {
        if (!Filters.hasRange()) { alert('Please set a date range before exporting.'); return; }
        Modal.show('#exportModal');
    })
    .on('click.analytics', '#exportConfirmBtn', async function () {
        const selectedSections = $('#exportSectionIndividual .export-section-check:checked')
            .map(function () { return $(this).val(); }).get();

        if (!selectedSections.length) { alert('Please select at least one section to export.'); return; }

        const exportFormat = $('input[name="exportFormat"]:checked').val() || 'xlsx';
        Modal.hide('#exportModal');
        showSpinner();

        try {
            await fetchMissingTabsForExport(selectedSections);
            await EXPORT_RUNNERS[exportFormat]?.(selectedSections, State.responses);
        } catch (error) {
            console.error('Export error:', error);
            alert('Export failed: ' + error.message);
        } finally {
            hideSpinner();
        }
    });

$('#startDate, #endDate, #classificationFilter, #libraryFilter')
    .on('change.analytics', () => {
        if (Filters.hasRange()) { State.responses = {}; loadTab(State.activeTab); }
    });

// ── BOOT ──────────────────────────────────────────────────────────────────

Filters.setDefaults();
if (Filters.hasRange()) loadTab('logs');

});
</script>