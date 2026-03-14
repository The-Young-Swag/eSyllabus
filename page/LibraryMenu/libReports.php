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

const BACKEND      = 'backend/bk_LibraryMenu/bk_libReports.php';
const DEFAULT_DAYS = 7;

const TAB_LABELS = {
    logs: 'Logs', users: 'Users', colleges: 'Colleges',
    courses: 'Courses', demographics: 'Demographics',
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

// ── STATE ─────────────────────────────────────────────────────────────────

const State = {
    activeTab:      'users',
    pendingRequest: null,
    viewAllTab:     'users',
    lastResponse:   null,
};

let currentViewAllPage = 1;

// ── CORE HELPERS ──────────────────────────────────────────────────────────

const apiPost    = (data) => $.ajax({ url: BACKEND, type: 'POST', dataType: 'json', data });
const escapeHtml = (value) => $('<div>').text(value ?? '').html();

function renderTypeBadge(value) {
    return `<span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:.68rem;">${escapeHtml(value)}</span>`;
}

function showSpinner() { $('#loadingSpinner').stop(true).css('display', 'flex').hide().fadeIn(150); }
function hideSpinner() { $('#loadingSpinner').fadeOut(200); }

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

// ── UTILITIES ─────────────────────────────────────────────────────────────

function resolveRankMedal(rankIndex, isTied) {
    const medals = ['🥇', '🥈', '🥉'];
    const medal  = medals[rankIndex] ?? `${rankIndex + 1}.`;
    return isTied
        ? medal + `<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1" style="font-size:.55rem;vertical-align:middle;">tied</span>`
        : medal;
}

function chartDonutFromObject(chartId, dataObject, colors, centerLabel) {
    ChartManager.donut(chartId, Object.keys(dataObject), Object.values(dataObject), colors, centerLabel);
}

const formatDateForExport = (rawDate) => {
    if (!rawDate) return '—';
    const parsedDate = new Date(rawDate.replace(' ', 'T'));
    return isNaN(parsedDate) ? rawDate : parsedDate.toLocaleString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
        hour: 'numeric', minute: '2-digit', hour12: true,
    });
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
                labels:   entries.map(({label}) => label),
                datasets: [{
                    label: unitLabel, data: entries.map(({value}) => value),
                    backgroundColor: colors.slice(0, entries.length),
                    borderRadius: 5, borderSkipped: false, barThickness: 36,
                }],
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                animation: { duration: 500, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: { ...CHART_TOOLTIP, callbacks: { label: chartContext => `  ${unitLabel}: ${chartContext.parsed.x.toLocaleString()}` } },
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
                            generateLabels: chartInstance => chartInstance.data.labels.map((label, index) => ({
                                text:        `${label} (${(chartInstance.data.datasets[0].data[index] || 0).toLocaleString()})`,
                                fillStyle:   chartInstance.data.datasets[0].backgroundColor[index],
                                strokeStyle: chartInstance.data.datasets[0].backgroundColor[index],
                                hidden: false, index, pointStyle: 'circle',
                            })),
                        },
                    },
                    tooltip: {
                        ...CHART_TOOLTIP,
                        callbacks: { label: chartContext => ` ${chartContext.label}: ${chartContext.parsed.toLocaleString()} (${total > 0 ? (chartContext.parsed / total * 100).toFixed(1) : 0}%)` },
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

// ── INLINE PAGINATION ─────────────────────────────────────────────────────

function paginateInlineTable(cardId, tbodyId, pagerId, rowRenderer) {
    const $card  = $(`#${cardId}`);
    const $tbody = $(`#${tbodyId}`);
    const $pager = $(`#${pagerId}`);
    if (!$card.length || !$tbody.length) return;

    let rows = [];
    try { rows = JSON.parse($card.attr('data-rows') || '[]'); } catch { return; }

    if (!rows.length) {
        $tbody.html('<tr><td colspan="9" class="text-center text-muted py-3">No data</td></tr>');
        return;
    }

    const pageSize   = parseInt($card.attr('data-per-page') || '10', 10);
    const totalPages = Math.ceil(rows.length / pageSize);
    let   currentPage = 1;

    const showPage = (page) => {
        currentPage      = Math.max(1, Math.min(page, totalPages));
        const startIndex = (currentPage - 1) * pageSize;
        $tbody.html(rows.slice(startIndex, startIndex + pageSize).map(rowRenderer).join(''));
        totalPages > 1 ? renderPager() : $pager.empty();
    };

    const renderPager = () => {
        const windowSize  = 5;
        const windowStart = Math.max(1, Math.min(currentPage - Math.floor(windowSize / 2), totalPages - windowSize + 1));
        const windowEnd   = Math.min(windowStart + windowSize - 1, totalPages);
        const isFirstPage = currentPage === 1;
        const isLastPage  = currentPage === totalPages;

        const pageItem = (label, targetPage, disabled, active) =>
            `<li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}">
                <a class="page-link" href="#" data-p="${targetPage}">${label}</a>
             </li>`;

        let items = pageItem('«', 1, isFirstPage, false) + pageItem('‹', currentPage - 1, isFirstPage, false);
        for (let pageNum = windowStart; pageNum <= windowEnd; pageNum++) {
            items += pageItem(pageNum, pageNum, false, pageNum === currentPage);
        }
        items += pageItem('›', currentPage + 1, isLastPage, false) + pageItem('»', totalPages, isLastPage, false);

        const fromRecord = (currentPage - 1) * pageSize + 1;
        const toRecord   = Math.min(currentPage * pageSize, rows.length);
        $pager.html(`
            <small class="text-muted d-block text-center mb-1" style="font-size:.7rem;">Showing ${fromRecord}–${toRecord} of ${rows.length}</small>
            <ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap">${items}</ul>
        `);
        $pager.find('.page-link').off('click').on('click', function (event) {
            event.preventDefault();
            const targetPage = parseInt($(this).data('p'), 10);
            if (!isNaN(targetPage) && targetPage > 0) showPage(targetPage);
        });
    };

    showPage(1);
}

// ── ROW RENDERERS ─────────────────────────────────────────────────────────

const renderCheckinRow = (row) => `<tr>
    <td class="ps-3 fw-semibold">${escapeHtml(row.display_label)}</td>
    <td class="text-muted">${escapeHtml(row.college  || '—')}</td>
    <td class="text-muted">${escapeHtml(row.course   || '—')}</td>
    <td>${renderTypeBadge(row.type)}</td>
    <td class="text-muted">${escapeHtml(row.library  || '—')}</td>
    <td class="text-end fw-semibold text-primary">${Number(row.count).toLocaleString()}</td>
    <td class="text-muted">${escapeHtml(row.agency_organization || '—')}</td>
    <td class="text-end text-muted pe-3">${escapeHtml(row.last_checkin)}</td>
</tr>`;

const renderDurationRow = (row) => `<tr>
    <td class="ps-3 fw-semibold">${escapeHtml(row.display_label)}</td>
    <td class="text-muted">${escapeHtml(row.college || '—')}</td>
    <td class="text-muted">${escapeHtml(row.course  || '—')}</td>
    <td>${renderTypeBadge(row.type)}</td>
    <td class="text-end fw-semibold text-success">${Math.round(row.minutes).toLocaleString()}</td>
    <td class="text-muted pe-3">${escapeHtml(row.agency_organization || '—')}</td>
</tr>`;

const renderLogRow = (row) => `<tr>
    <td class="ps-3 fw-semibold">${escapeHtml(row.id_number)}</td>
    <td class="text-muted">${escapeHtml(row.name                || '—')}</td>
    <td class="text-muted">${escapeHtml(row.college             || '—')}</td>
    <td class="text-muted">${escapeHtml(row.course              || '—')}</td>
    <td>${renderTypeBadge(row.classification || '—')}</td>
    <td class="text-muted">${escapeHtml(row.library             || '—')}</td>
    <td class="text-muted">${escapeHtml(row.sex                 || '—')}</td>
    <td class="text-muted">${escapeHtml(row.checkin_time        || '—')}</td>
    <td class="text-muted">${escapeHtml(row.checkout_time       || '—')}</td>
    <td class="text-muted">${escapeHtml(row.agency_organization || '—')}</td>
    <td class="text-end pe-3">${row.duration_minutes != null ? Math.round(row.duration_minutes) : '—'}</td>
</tr>`;

// ── TAB INITIALIZERS ──────────────────────────────────────────────────────

function initLogsTab() {
    paginateInlineTable('allLogsCard', 'allLogsTbody', 'allLogsPager', renderLogRow);
}

function initUsersTab(response) {
    // PHP pre-computes and sends chartTopCheckins / chartTopDuration as flat sorted arrays.
    ChartManager.bar('chartTopUserCheckins', response.chartTopCheckins, COLORS.rankCheckins, 'Check-ins');
    ChartManager.bar('chartTopUserDuration',
        response.chartTopDuration.map(({label, value}) => ({ label, value: Math.round(value) })),
        COLORS.rankDuration, 'Minutes');

    chartDonutFromObject('chartVisitorTypeDonut', response.classificationDistribution, COLORS.visitorType, 'Visitors');

    paginateInlineTable('checkinDetailsCard',  'checkinDetailsTbody',  'checkinDetailsPager',  renderCheckinRow);
    paginateInlineTable('durationDetailsCard', 'durationDetailsTbody', 'durationDetailsPager', renderDurationRow);
}

function initCollegesTab(response) {
    // College colors are already computed server-side and included in the response.
    const checkinKeys  = Object.keys(response.top3CollegesCheckin);
    const durationKeys = Object.keys(response.top3CollegesDuration);

    ChartManager.donut('chartCollegeCheckin',
        checkinKeys,
        checkinKeys.map(name => response.top3CollegesCheckin[name].count),
        checkinKeys.map(name => response.top3CollegesCheckin[name].color), 'Visitors');

    ChartManager.donut('chartCollegeDuration',
        durationKeys,
        durationKeys.map(name => Math.round(response.top3CollegesDuration[name].minutes)),
        durationKeys.map(name => response.top3CollegesDuration[name].color), 'Minutes');
}

function initCoursesTab(response) {
    // PHP pre-flattens course data into courseChartData: [{label, checkins, duration}].
    const { courseChartData } = response;
    if (!courseChartData.length) return;
    const colors = courseChartData.map((_, index) => COLORS.course[index % COLORS.course.length]);
    ChartManager.donut('chartCoursesCheckin',  courseChartData.map(({label}) => label), courseChartData.map(({checkins}) => checkins), colors, 'Visitors');
    ChartManager.donut('chartCoursesDuration', courseChartData.map(({label}) => label), courseChartData.map(({duration}) => duration), colors, 'Minutes');
}

function initDemographicsTab(response) {
    chartDonutFromObject('chartSexDonut', response.sexDistribution, COLORS.sex, 'Visitors');
}

// ── KPI RENDERING ─────────────────────────────────────────────────────────

function updateKpi(response) {
    const noData = '<div class="text-muted small fst-italic">No data</div>';

    const kpiRow = (index, itemCount, medal, leftHtml, rightHtml) =>
        `<div class="d-flex align-items-center justify-content-between gap-2 py-1 ${index < itemCount - 1 ? 'border-bottom' : ''}">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <span style="font-size:.9rem;flex-shrink:0;">${medal}</span>${leftHtml}
            </div>
            <div class="d-flex flex-column align-items-end" style="flex-shrink:0;">${rightHtml}</div>
         </div>`;

    $('#kpiTopStudents').html(!response.top3Students?.length ? noData :
        response.top3Students.map((student, index) => kpiRow(index, response.top3Students.length,
            resolveRankMedal(index, student.tied),
            `<div class="min-w-0">
                <div class="fw-bold text-dark" style="font-size:.85rem;line-height:1.2;">${escapeHtml(student.id_number)}</div>
                <div class="text-muted" style="font-size:.68rem;">${escapeHtml(student.college || '—')}${student.course ? ' · ' + escapeHtml(student.course) : ''}</div>
             </div>`,
            `<span class="badge rounded-pill bg-primary-subtle text-primary fw-semibold" style="font-size:.72rem;">${Number(student.count).toLocaleString()}</span>
             <span class="text-muted" style="font-size:.62rem;">check-ins</span>`
        )).join('')
    );

    $('#kpiTopColleges').html(!response.top3Colleges?.length ? noData :
        response.top3Colleges.map((college, index) => kpiRow(index, response.top3Colleges.length,
            resolveRankMedal(index, college.tied),
            `<div class="fw-bold text-dark text-truncate" style="font-size:.85rem;">${escapeHtml(college.name)}</div>`,
            `<span class="badge rounded-pill bg-success-subtle text-success fw-semibold" style="font-size:.72rem;">${Number(college.count).toLocaleString()}</span>
             <span class="text-muted" style="font-size:.62rem;">students</span>`
        )).join('')
    );

    $('#kpiTopCourses').html(!response.top3Courses?.length ? noData :
        response.top3Courses.map((course, index) => kpiRow(index, response.top3Courses.length,
            resolveRankMedal(index, course.tied),
            `<div class="min-w-0">
                <div class="fw-bold text-dark" style="font-size:.85rem;line-height:1.2;">${escapeHtml(course.course)}</div>
                <div style="font-size:.68rem;"><span class="badge rounded-pill bg-secondary-subtle text-secondary px-2 py-0">${escapeHtml(course.college || '—')}</span></div>
             </div>`,
            `<span class="badge rounded-pill bg-warning-subtle text-warning fw-semibold" style="font-size:.72rem;">${Number(course.count).toLocaleString()}</span>
             <span class="text-muted" style="font-size:.62rem;">students</span>`
        )).join('')
    );

    $('#lastUpdatedLabel').html(
        '<i class="fas fa-sync-alt me-1"></i>Last updated: ' +
        new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })
    );
}

// ── TAB LOADER ────────────────────────────────────────────────────────────

function loadTab(tabName) {
    State.activeTab = tabName;
    document.querySelectorAll('#analyticsTabs .nav-link').forEach(button =>
        button.classList.toggle('active', button.dataset.tab === tabName)
    );
    State.pendingRequest?.abort();
    showSpinner();

    State.pendingRequest = apiPost({ action: 'tab', tab: tabName, ...Filters.get() })
        .done(response => {
            hideSpinner();
            if (response.status !== 'success') {
                $('#tabContent').html(`<div class="alert alert-danger m-3">${response.message || 'Error'}</div>`);
                return;
            }
            $('#tabContent').html(response.html);

            switch (tabName) {
                case 'logs':         initLogsTab();                 break;
                case 'users':        initUsersTab(response);        break;
                case 'colleges':     initCollegesTab(response);     break;
                case 'courses':      initCoursesTab(response);      break;
                case 'demographics': initDemographicsTab(response); break;
            }

            updateKpi(response);
            State.lastResponse = response;
            preloadExportLibraries();
            $('#exportBtn').prop('disabled', false);
        })
        .fail((_, status) => {
            hideSpinner();
            if (status !== 'abort')
                $('#tabContent').html('<div class="alert alert-danger m-3">Failed to load analytics. Please try again.</div>');
        });
}

// ── VIEW ALL ──────────────────────────────────────────────────────────────

function loadViewAll(tabName, page) {
    showSpinner();
    apiPost({ action: 'viewAll', tab: tabName, page, ...Filters.get() })
        .done(response => {
            hideSpinner();
            if (response.status !== 'success') {
                $('#viewAllModalBody').html('<div class="alert alert-danger m-3">Failed.</div>');
                if (!$('#viewAllModal').hasClass('show')) $('#viewAllModal').modal('show');
                return;
            }
            $('#viewAllModalTitle').text((TAB_LABELS[tabName] ?? 'All') + ' Records');
            $('#viewAllModalSubtitle').text(`Page ${response.page} of ${response.totalPages} · ${response.total} records`);
            $('#viewAllModalBody').html(response.tableHtml);
            $('#viewAllModalFooter').html(response.pagination);
            if (!$('#viewAllModal').hasClass('show')) $('#viewAllModal').modal('show');
        })
        .fail(() => hideSpinner());
}

// ── EXPORT SCHEMA ─────────────────────────────────────────────────────────

const EXPORT_SCHEMA = {
    logs: {
        label:            'Visit Logs',
        headers:          ['ID Number', 'Name', 'College', 'Course', 'Type', 'Section', 'Sex', 'Check-in', 'Check-out', 'Agency / Organization', 'Duration (min)'],
        columnAlignments: [null, null, null, null, null, null, null, null, null, null, 'center'],
        rowMapper: (response) => (response.allLogs || []).map(log => [
            log.id_number, log.name || '—', log.college || '—', log.course || '—',
            log.classification || '—', log.library || '—', log.sex || '—',
            formatDateForExport(log.checkin_time),
            log.checkout_time ? formatDateForExport(log.checkout_time) : '—',
            log.agency_organization || '—',
            log.duration_minutes != null ? Math.round(log.duration_minutes) : '—',
        ]),
    },
    users: {
        label:   'Users',
        headers: ['ID Number', 'Name', 'College', 'Course', 'Type', 'Library Section', 'Check-ins', 'Duration (min)', 'Last Check-in'],
        rowMapper: (response) => {
            const rows = [];
            for (const [classification, userGroup] of Object.entries(response.topCheckins))
                for (const [userId, userRecord] of Object.entries(userGroup)) {
                    const durationEntry = response.topDuration?.[classification]?.[userId];
                    rows.push([
                        userRecord.display_label, userRecord.name ?? '—', userRecord.college ?? '—', userRecord.course ?? '—',
                        classification, userRecord.library ?? '—', userRecord.count,
                        durationEntry ? Math.round(durationEntry.minutes) : '—',
                        formatDateForExport(userRecord.last_checkin),
                    ]);
                }
            return rows;
        },
    },
    colleges: {
        label:   'Colleges',
        headers: ['College', 'Unique Visitors', 'Total Duration (min)', 'Last Check-in'],
        rowMapper: (response) => {
            const merged = {};
            for (const [collegeName, data] of Object.entries(response.top3CollegesCheckin))
                merged[collegeName] = { count: data.count, minutes: '—', last: data.last_checkin };
            for (const [collegeName, data] of Object.entries(response.top3CollegesDuration)) {
                merged[collegeName] ??= { count: '—', minutes: '—', last: data.last_checkin };
                merged[collegeName].minutes = Math.round(data.minutes);
            }
            return Object.entries(merged).map(([collegeName, row]) => [collegeName, row.count, row.minutes, formatDateForExport(row.last)]);
        },
    },
    courses: {
        label:   'Courses',
        headers: ['College', 'Course', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
        rowMapper: (response) => {
            const rows = [];
            for (const [college, courses] of Object.entries(response.topCoursesCheckin))
                for (const [course, data] of Object.entries(courses)) {
                    const durationEntry = response.topCoursesDuration?.[college]?.[course];
                    rows.push([college, course, data.count, durationEntry ? Math.round(durationEntry.minutes) : '—', formatDateForExport(data.last_checkin)]);
                }
            return rows;
        },
    },
    demographics: {
        label:   'Demographics',
        headers: ['Sex', 'Visitors', '% of Total'],
        rowMapper: (response) => {
            const total = Object.values(response.sexDistribution).reduce((sum, count) => sum + count, 0);
            return Object.entries(response.sexDistribution).map(([sex, count]) => [
                sex, count, total > 0 ? (count / total * 100).toFixed(1) + '%' : '0%',
            ]);
        },
    },
};

// ── OFFSCREEN CHART BUILDER ───────────────────────────────────────────────

function buildOffscreenChart(type, labels, values, colors, unitLabel, title) {
    const isBar  = type === 'bar';
    const width  = isBar ? OFFSCREEN.barW  : OFFSCREEN.donutW;
    const height = isBar ? OFFSCREEN.barH  : OFFSCREEN.donutH;
    const total  = isBar ? 0 : values.reduce((sum, value) => sum + value, 0);
    const canvas = Object.assign(document.createElement('canvas'), { width, height });

    const config = isBar ? {
        type: 'bar',
        data: { labels, datasets: [{ label: unitLabel, data: values, backgroundColor: colors, borderRadius: 5, borderSkipped: false, barThickness: 50 }] },
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
                        generateLabels: chartInstance => chartInstance.data.labels.map((label, index) => ({
                            text:        `${label}  (${(chartInstance.data.datasets[0].data[index] || 0).toLocaleString()})`,
                            fillStyle:   chartInstance.data.datasets[0].backgroundColor[index],
                            strokeStyle: chartInstance.data.datasets[0].backgroundColor[index],
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
                chartContext.fillText(total.toLocaleString(), centerX, centerY - 14);
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
                buildOffscreenChart('bar',   chartTopCheckins.map(({label}) => label), chartTopCheckins.map(({value}) => value),               COLORS.rankCheckins.slice(0, chartTopCheckins.length), 'Check-ins', 'Top Visitors by Check-ins'),
                buildOffscreenChart('bar',   chartTopDuration.map(({label}) => label), chartTopDuration.map(({value}) => Math.round(value)),   COLORS.rankDuration.slice(0, chartTopDuration.length), 'Minutes',   'Top Visitors by Duration'),
                buildOffscreenChart('donut', Object.keys(response.classificationDistribution), Object.values(response.classificationDistribution), COLORS.visitorType, 'Visitors', 'Visitor Type Breakdown'),
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
            if (!courseChartData.length) return [];
            const colors = courseChartData.map((_, index) => COLORS.course[index % COLORS.course.length]);
            return [
                buildOffscreenChart('donut', courseChartData.map(({label}) => label), courseChartData.map(({checkins}) => checkins), colors, 'Visitors', 'Top Courses by Check-ins'),
                buildOffscreenChart('donut', courseChartData.map(({label}) => label), courseChartData.map(({duration}) => duration), colors, 'Minutes',  'Top Courses by Duration'),
            ];
        }

        case 'demographics':
            return [buildOffscreenChart('donut',
                Object.keys(response.sexDistribution),
                Object.values(response.sexDistribution),
                COLORS.sex, 'Visitors', 'Sex Distribution'
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
        } catch (error) { if (error.name === 'AbortError') return; }
    }
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

async function runExportPDF(tabs, response) {
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
    pdf.text(tabs.map(tabName => TAB_LABELS[tabName]).join(' · ') + '   ·   ' + Filters.dateLabel(), pageWidth - margin, 12, { align: 'right' });

    cursorY = 24;
    pdf.setFont('helvetica', 'normal').setFontSize(7.5).setTextColor(100, 116, 139);
    pdf.text('Generated: ' + new Date().toLocaleString(), margin, cursorY);
    cursorY += 5; drawDivider(cursorY); cursorY += 6;

    for (const tabName of tabs) {
        if (!isFirstTab) { pdf.addPage(); cursorY = margin; pageNumber++; }
        isFirstTab = false;

        const schema    = EXPORT_SCHEMA[tabName];
        if (!schema) continue;
        const tableRows = schema.rowMapper(response);

        pdf.setFillColor(248, 250, 252);
        pdf.rect(margin, cursorY - 2, contentWidth, 8, 'F');
        pdf.setFont('helvetica', 'bold').setFontSize(9.5).setTextColor(17, 24, 39);
        pdf.text(schema.label, margin + 3, cursorY + 4);
        cursorY += 12;

        const charts      = buildChartsForTab(tabName, response);
        const barCharts   = charts.filter(chart => chart.type === 'bar');
        const donutCharts = charts.filter(chart => chart.type === 'donut');

        if (charts.length) {
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
        buildExportFilename(tabs, 'pdf'), 'application/pdf', 'pdf'
    );
}

// ── EXCEL EXPORT ──────────────────────────────────────────────────────────

async function runExportExcel(tabs, response) {
    const workbook  = new window.ExcelJS.Workbook();
    const dateRange = Filters.dateLabel();

    const FILL = {
        title:  { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF111827' } },
        meta:   { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFf3f4f6' } },
        header: { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF059669' } },
        white:  { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFFFFF' } },
        zebra:  { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFf0fdf4' } },
    };
    const BORDER = {
        header: { top: { style: 'thin', color: { argb: 'FF047857' } }, bottom: { style: 'thin', color: { argb: 'FF047857' } }, left: { style: 'thin', color: { argb: 'FF047857' } }, right: { style: 'thin', color: { argb: 'FF047857' } } },
        data:   { top: { style: 'hair', color: { argb: 'FFe5e7eb' } }, bottom: { style: 'hair', color: { argb: 'FFe5e7eb' } }, left: { style: 'hair', color: { argb: 'FFe5e7eb' } }, right: { style: 'hair', color: { argb: 'FFe5e7eb' } } },
    };
    const ALIGN = {
        center: { horizontal: 'center', vertical: 'middle' },
        left:   { horizontal: 'left',   vertical: 'middle' },
        right:  { horizontal: 'right',  vertical: 'middle' },
    };

    for (const tabName of tabs) {
        const schema   = EXPORT_SCHEMA[tabName];
        if (!schema) continue;
        const dataRows = schema.rowMapper(response);
        const colCount = schema.headers.length;
        const sheet    = workbook.addWorksheet(schema.label.substring(0, 31));
        sheet.views    = [{ state: 'frozen', ySplit: 5 }];

        const addMetaRow = (text, rowHeight, fontOptions, fillKey) => {
            sheet.addRow([text]);
            const worksheetRow = sheet.lastRow;
            worksheetRow.height = rowHeight;
            worksheetRow.getCell(1).font      = fontOptions;
            worksheetRow.getCell(1).fill      = FILL[fillKey];
            worksheetRow.getCell(1).alignment = ALIGN.center;
            sheet.mergeCells(worksheetRow.number, 1, worksheetRow.number, colCount);
            for (let colIndex = 2; colIndex <= colCount; colIndex++) worksheetRow.getCell(colIndex).fill = FILL[fillKey];
        };

        addMetaRow(`Library Analytics Report — ${schema.label}`, 30, { bold: true, color: { argb: 'FFFFFFFF' }, size: 14 }, 'title');
        addMetaRow(`Period: ${dateRange}`,                        18, { color: { argb: 'FF6b7280' }, size: 10 },             'meta');
        addMetaRow(`Generated: ${new Date().toLocaleString()}   ·   ${dataRows.length} records`, 16, { italic: true, color: { argb: 'FF9ca3af' }, size: 9 }, 'meta');
        sheet.addRow([]); sheet.lastRow.height = 6;

        sheet.addRow(schema.headers);
        sheet.lastRow.height = 22;
        sheet.lastRow.eachCell(cell => {
            cell.font = { bold: true, color: { argb: 'FFFFFFFF' }, size: 10 };
            cell.fill = FILL.header; cell.alignment = ALIGN.center; cell.border = BORDER.header;
        });

        sheet.addRows(dataRows);
        const firstDataRowIndex = 6;
        for (let rowIndex = firstDataRowIndex; rowIndex <= firstDataRowIndex + dataRows.length - 1; rowIndex++) {
            const worksheetRow = sheet.getRow(rowIndex);
            const isZebra      = (rowIndex - firstDataRowIndex) % 2 !== 0;
            worksheetRow.height = 18;
            worksheetRow.eachCell({ includeEmpty: true }, (cell, colNumber) => {
                const alignOverride = schema.columnAlignments?.[colNumber - 1];
                cell.fill      = isZebra ? FILL.zebra : FILL.white;
                cell.border    = BORDER.data;
                cell.font      = { size: 10 };
                cell.alignment = alignOverride === 'center' ? ALIGN.center
                               : alignOverride === 'right'  ? ALIGN.right
                               : alignOverride === 'left'   ? ALIGN.left
                               : typeof cell.value === 'number' ? ALIGN.right : ALIGN.left;
            });
        }

        schema.headers.forEach((header, index) => {
            const maxLength = dataRows.reduce((max, dataRow) => Math.max(max, String(dataRow[index] ?? '').length), header.length);
            sheet.getColumn(index + 1).width = Math.min(50, maxLength + 4);
        });
    }

    const buffer = await workbook.xlsx.writeBuffer();
    await saveBlob(
        new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }),
        buildExportFilename(tabs, 'xlsx'), 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'xlsx'
    );
}

// ── EVENT BINDINGS ────────────────────────────────────────────────────────

$(document).off('.analytics');

$(document)
    .on('click.analytics', '#analyticsTabs .nav-link', function (event) {
        event.preventDefault();
        loadTab($(this).data('tab'));
    })
    .on('click.analytics', '#refreshBtn', () => { if (Filters.hasRange()) loadTab(State.activeTab); })
    .on('click.analytics', '.view-all-btn', function () {
        State.viewAllTab   = $(this).data('tab');
        currentViewAllPage = 1;
        loadViewAll(State.viewAllTab, currentViewAllPage);
    })
    .on('click.analytics', '#viewAllModalFooter .page-link', function (event) {
        event.preventDefault();
        const targetPage = parseInt($(this).data('page'), 10);
        if (!isNaN(targetPage)) { currentViewAllPage = targetPage; loadViewAll(State.viewAllTab, currentViewAllPage); }
    })
    .on('click.analytics', '.export-format-option', function () {
        $('.export-format-option').removeClass('active-format');
        $(this).addClass('active-format').find('input[type="radio"]').prop('checked', true);
    })
    .on('change.analytics', '#exportCheckAll', function () {
        const isChecked = $(this).is(':checked');
        $('#exportSectionIndividual .export-section-check')
            .prop('checked', isChecked)
            .closest('label').toggleClass('opacity-50', !isChecked);
    })
    .on('change.analytics', '#exportSectionIndividual .export-section-check', function () {
        const $allChecks = $('#exportSectionIndividual .export-section-check');
        $('#exportCheckAll').prop('checked', $allChecks.length === $allChecks.filter(':checked').length);
    })
    .on('click.analytics', '#exportBtn', function () {
        if (!State.lastResponse) { alert('No data loaded. Please generate analytics first.'); return; }
        $('#exportModal').modal('show');
    })
    .on('click.analytics', '#exportConfirmBtn', async function () {
        const selectedSections = $('#exportSectionIndividual .export-section-check:checked')
            .map(function () { return $(this).val(); }).get();

        if (!selectedSections.length) { alert('Please select at least one section to export.'); return; }
        if (!State.lastResponse)      { alert('No data available. Please generate analytics first.'); return; }

        const exportFormat = $('input[name="exportFormat"]:checked').val() || 'xlsx';
        $('#exportModal').modal('hide');
        showSpinner();

        try {
            if (exportFormat === 'pdf') {
                await loadScript(EXPORT_LIBS.jspdf);
                await loadScript(EXPORT_LIBS.autotable); // must be sequential — autotable extends jsPDF
                await runExportPDF(selectedSections, State.lastResponse);
            } else {
                await loadScript(EXPORT_LIBS.exceljs);
                await runExportExcel(selectedSections, State.lastResponse);
            }
        } catch (error) {
            console.error('Export error:', error);
            alert('Export failed: ' + error.message);
        } finally {
            hideSpinner();
        }
    });

$('#startDate, #endDate, #classificationFilter, #libraryFilter')
    .on('change.analytics', () => { if (Filters.hasRange()) loadTab(State.activeTab); });

// ── BOOT ──────────────────────────────────────────────────────────────────

Filters.setDefaults();
if (Filters.hasRange()) loadTab('users');

});
</script>