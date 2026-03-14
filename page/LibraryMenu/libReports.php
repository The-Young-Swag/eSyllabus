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

    const Analytics = {
        backendUrl:  'backend/bk_LibraryMenu/bk_libReports.php',
        defaultTab:  'users',
        defaultDays: 7,

        tabLabels: {
            logs:         'Logs',
            users:        'Users',
            colleges:     'Colleges',
            courses:      'Courses',
            demographics: 'Demographics',
        },

        rankColors: {
            checkins: ['rgba(59,130,246,0.88)',  'rgba(99,102,241,0.88)',  'rgba(139,92,246,0.88)'],
            duration: ['rgba(16,185,129,0.88)',  'rgba(20,184,166,0.88)',  'rgba(8,145,178,0.88)'],
        },

        donutColorsVisitorType: [
            'rgba(59,130,246,0.88)',
            'rgba(16,185,129,0.88)',
            'rgba(245,158,11,0.88)',
            'rgba(100,116,139,0.88)',
        ],

        donutColorsSex: [
            'rgba(59,130,246,0.88)',
            'rgba(239,68,68,0.88)',
            'rgba(100,116,139,0.88)',
        ],

        donutColorsCourse: [
            'rgba(59,130,246,0.82)',  'rgba(16,185,129,0.82)',
            'rgba(245,158,11,0.82)',  'rgba(139,92,246,0.82)',
            'rgba(239,68,68,0.82)',   'rgba(20,184,166,0.82)',
            'rgba(100,116,139,0.82)',
        ],

        collegeColorMap: {
            CAF: 'rgba(22,163,74,0.88)',   CAS: 'rgba(234,88,12,0.88)',
            CBM: 'rgba(202,138,4,0.88)',   CET: 'rgba(220,38,38,0.88)',
            CED: 'rgba(37,99,235,0.88)',   CVM: 'rgba(107,114,128,0.88)',
        },
        collegeColorFallback: 'rgba(139,92,246,0.88)',

        exportLibraries: {
            jspdf:     'libs/jspdf.umd.min.js',
            autotable: 'libs/jspdf.plugin.autotable.min.js',
            exceljs:   'libs/exceljs.min.js',

    // exportLibraries: {
    //         jspdf:     'https://unpkg.com/jspdf@2.5.1/dist/jspdf.umd.min.js',
    //         autotable: 'https://unpkg.com/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js',
    //         exceljs:   'https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js',
    //     },
},
    };


    // ── DOM ───────────────────────────────────────────────────────────────────

    const $tabContent  = $('#tabContent');
    const $tabButtons  = $('#analyticsTabs .nav-link');
    const $lastUpdated = $('#lastUpdatedLabel');
    const $spinner     = $('#loadingSpinner');

    const filters = {
        startDate:      $('#startDate'),
        endDate:        $('#endDate'),
        classification: $('#classificationFilter'),
        library:        $('#libraryFilter'),
    };

    const kpi = {
        topStudents: $('#kpiTopStudents'),
        topColleges: $('#kpiTopColleges'),
        topCourses:  $('#kpiTopCourses'),
    };


    // ── STATE ─────────────────────────────────────────────────────────────────

    let activeTab    = Analytics.defaultTab;
    let pendingXhr   = null;
    let viewAllTab   = Analytics.defaultTab;
    let viewAllPage  = 1;
    let lastResponse = null;


    // ── SPINNER ───────────────────────────────────────────────────────────────

    const showSpinner = () => $spinner.length && $spinner.stop(true).css('display', 'flex').hide().fadeIn(150);
    const hideSpinner = () => $spinner.length && $spinner.fadeOut(200);


    // ── FILTERS ───────────────────────────────────────────────────────────────

    const getFilters     = () => ({
        startDate:      filters.startDate.val(),
        endDate:        filters.endDate.val(),
        classification: filters.classification.val(),
        library:        filters.library.val(),
    });

    const hasDateRange   = () => !!(filters.startDate.val() && filters.endDate.val());

    const buildDateRangeLabel = () =>
        `${filters.startDate.val() || '—'} to ${filters.endDate.val() || '—'}`;

    function setDefaultDateRange() {
        if (filters.startDate.val()) return;
        const today = new Date();
        const start = new Date(today);
        start.setDate(today.getDate() - Analytics.defaultDays);
        filters.startDate.val(start.toISOString().split('T')[0]);
        filters.endDate.val(today.toISOString().split('T')[0]);
    }


    // ── UTILITIES ─────────────────────────────────────────────────────────────

    const escVal = (value) => $('<div>').text(value ?? '').html();


function resolveCollegeColor(collegeName) {
    const upper = (collegeName || '').toUpperCase();

    for (const [abbr, color] of Object.entries(Analytics.collegeColorMap)) {
        if (upper.includes(abbr)) return color;
    }

    return Analytics.collegeColorFallback;
}


function resolveRankMedal(rankItem, fallbackPosition) {
    const medals = ['🥇', '🥈', '🥉'];

    const rank = rankItem.rank ?? (fallbackPosition + 1);

    const tied = rankItem.tied
        ? `<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1"
                 style="font-size:.55rem;vertical-align:middle;">tied</span>`
        : '';

    return (medals[rank - 1] ?? `${rank}.`) + tied;
}


function flattenUserRanking(source, valueKey, topN) {
    const entries = [];

    for (const userGroup of Object.values(source)) {
        for (const userRecord of Object.values(userGroup)) {
            entries.push({
                label: userRecord.display_label,
                value: userRecord[valueKey] ?? 0
            });
        }
    }

    return entries
        .sort((a, b) => b.value - a.value)
        .slice(0, topN);
}


function formatDateForExport(rawDate) {
    if (!rawDate) return '—';

    const parsedDate = new Date(rawDate.replace(' ', 'T'));

    return isNaN(parsedDate)
        ? rawDate
        : parsedDate.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
}


    // ── CHART MANAGER ─────────────────────────────────────────────────────────

    const ChartManager = {
    _instances: {},

    destroy(chartId) {
        if (this._instances[chartId]) {
            this._instances[chartId].destroy();
            delete this._instances[chartId];
        }
    },

    _register(chartId, config) {
        const canvas = document.getElementById(chartId);
        if (!canvas) return;
        this.destroy(chartId);
        this._instances[chartId] = new Chart(canvas, config);
    },

    _tooltipDefaults: () => ({
        backgroundColor: 'rgba(15,23,42,0.92)',
        titleColor:      '#f8fafc',
        bodyColor:       '#94a3b8',
        borderColor:     'rgba(148,163,184,0.15)',
        borderWidth:     1,
        padding:         10,
        cornerRadius:    6,
    }),

    renderBarH(id, labels, values, colors, unit) {
        this._register(id, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: unit,
                    data: values,
                    backgroundColor: colors,
                    borderRadius: 5,
                    borderSkipped: false,
                    barThickness: 36,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 500, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...this._tooltipDefaults(),
                        callbacks: { 
                            label: chartContext => `  ${unit}: ${chartContext.parsed.x.toLocaleString()}` 
                        },
                    },
                },
                scales: {
                    x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { color: '#6b7280', font: { size: 10 } } },
                    y: { grid: { display: false }, ticks: { color: '#374151', font: { size: 12 }, padding: 8 } },
                },
                layout: { padding: { right: 8 } },
            },
        });
    },

    renderDonut(id, labels, values, colors, centerLabel) {
        const total = values.reduce((sum, v) => sum + v, 0);

        const centerTextPlugin = {
            id: `centerText_${id}`,
            afterDraw({ ctx: chartContext, chartArea }) {
                if (!chartArea) return;
                const cx = (chartArea.left + chartArea.right) / 2;
                const cy = (chartArea.top + chartArea.bottom) / 2;

                chartContext.save();
                chartContext.textAlign = 'center';
                chartContext.textBaseline = 'middle';

                chartContext.font = 'bold 22px sans-serif';
                chartContext.fillStyle = '#111827';
                chartContext.fillText(total.toLocaleString(), cx, cy - 10);

                chartContext.font = '12px sans-serif';
                chartContext.fillStyle = '#6b7280';
                chartContext.fillText(centerLabel, cx, cy + 14);

                chartContext.restore();
            },
        };

        this._register(id, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 600, easing: 'easeInOutQuart' },
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#374151',
                            font: { size: 11 },
                            padding: 12,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            generateLabels: chart => chart.data.labels.map((lbl, i) => ({
                                text: `${lbl} (${(chart.data.datasets[0].data[i] || 0).toLocaleString()})`,
                                fillStyle: chart.data.datasets[0].backgroundColor[i],
                                strokeStyle: chart.data.datasets[0].backgroundColor[i],
                                hidden: false,
                                index: i,
                                pointStyle: 'circle',
                            })),
                        },
                    },
                    tooltip: {
                        ...this._tooltipDefaults(),
                        callbacks: {
                            label: chartContext => {
                                const pct = total > 0 ? ((chartContext.parsed / total) * 100).toFixed(1) : 0;
                                return ` ${chartContext.label}: ${chartContext.parsed.toLocaleString()} (${pct}%)`;
                            },
                        },
                    },
                },
            },
            plugins: [centerTextPlugin],
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
    try {
        rows = JSON.parse($card.attr('data-rows') || '[]');
    } catch {
        return;
    }

    if (!rows.length) {
        $tbody.html('<tr><td colspan="9" class="text-center text-muted py-3">No data</td></tr>');
        return;
    }

    const pageSize   = parseInt($card.attr('data-per-page') || '10', 10);
    const totalPages = Math.ceil(rows.length / pageSize);
    let currentPage  = 1;

    function showPage(page) {
        currentPage = Math.max(1, Math.min(page, totalPages));
        const startIndex = (currentPage - 1) * pageSize;
        const endIndex   = currentPage * pageSize;

        $tbody.html(rows.slice(startIndex, endIndex).map(rowRenderer).join(''));
        totalPages > 1 ? renderPager() : $pager.empty();
    }

    function renderPager() {
        const windowSize = 5;
        const startPage  = Math.max(1, Math.min(currentPage - Math.floor(windowSize / 2), totalPages - windowSize + 1));
        const endPage    = Math.min(startPage + windowSize - 1, totalPages);
        const isFirst    = currentPage === 1;
        const isLast     = currentPage === totalPages;

        const createPageItem = (label, page, disabled, active) =>
            `<li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}">
                <a class="page-link" href="#" data-p="${page}">${label}</a>
             </li>`;

        let pageItems = '';
        pageItems += createPageItem('«', 1, isFirst, false);
        pageItems += createPageItem('‹', currentPage - 1, isFirst, false);

        for (let pageNum = startPage; pageNum <= endPage; pageNum++) {
            pageItems += createPageItem(pageNum, pageNum, false, pageNum === currentPage);
        }

        pageItems += createPageItem('›', currentPage + 1, isLast, false);
        pageItems += createPageItem('»', totalPages, isLast, false);

        const fromIndex = (currentPage - 1) * pageSize + 1;
        const toIndex   = Math.min(currentPage * pageSize, rows.length);

        $pager.html(`
            <small class="text-muted d-block text-center mb-1" style="font-size:.7rem;">
                Showing ${fromIndex}–${toIndex} of ${rows.length}
            </small>
            <ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap">
                ${pageItems}
            </ul>
        `);

        $pager.find('.page-link').off('click').on('click', function (event) {
            event.preventDefault();
            const selectedPage = parseInt($(this).data('p'), 10);
            if (!isNaN(selectedPage) && selectedPage > 0) showPage(selectedPage);
        });
    }

    // show first page by default
    showPage(1);
}


    // ── ROW RENDERERS ─────────────────────────────────────────────────────────

    const typeBadge = (val) =>
        `<span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:.68rem;">${escVal(val)}</span>`;

const renderCheckinRow = (row) => `<tr>
    <td class="ps-3 fw-semibold">${escVal(row.display_label)}</td>
    <td class="text-muted">${escVal(row.college  || '—')}</td>
    <td class="text-muted">${escVal(row.course   || '—')}</td>
    <td>${typeBadge(row.type)}</td>
    <td class="text-muted">${escVal(row.library  || '—')}</td>
    <td class="text-end fw-semibold text-primary">${Number(row.count).toLocaleString()}</td>
    <td class="text-muted">${escVal(row.agency_organization || '—')}</td>
    <td class="text-end text-muted pe-3">${escVal(row.last_checkin)}</td>
</tr>`;

const renderDurationRow = (row) => `<tr>
    <td class="ps-3 fw-semibold">${escVal(row.display_label)}</td>
    <td class="text-muted">${escVal(row.college || '—')}</td>
    <td class="text-muted">${escVal(row.course  || '—')}</td>
    <td>${typeBadge(row.type)}</td>
    <td class="text-end fw-semibold text-success">${Math.round(row.minutes).toLocaleString()}</td>
    <td class="text-muted pe-3">${escVal(row.agency_organization || '—')}</td>
</tr>`;

    const renderAllLogsRow = (row) => `<tr>
        <td class="ps-3 fw-semibold">${escVal(row.id_number)}</td>
        <td class="text-muted">${escVal(row.name           || '—')}</td>
        <td class="text-muted">${escVal(row.college        || '—')}</td>
        <td class="text-muted">${escVal(row.course         || '—')}</td>
        <td>${typeBadge(row.classification || '—')}</td>
        <td class="text-muted">${escVal(row.library        || '—')}</td>
        <td class="text-muted">${escVal(row.sex            || '—')}</td>
        <td class="text-muted">${escVal(row.checkin_time   || '—')}</td>
        <td class="text-muted">${escVal(row.checkout_time  || '—')}</td>
        <td class="text-muted">${escVal(row.agency_organization || '—')}</td>
        <td class="text-end pe-3">${row.duration_minutes != null ? Math.round(row.duration_minutes) : '—'}</td>
    </tr>`;


    // ── TAB INITIALIZERS ──────────────────────────────────────────────────────

    function initLogsTab() {
        paginateInlineTable('allLogsCard', 'allLogsTbody', 'allLogsPager', renderAllLogsRow);
    }

    function initUsersTab(res) {
        const top = (src, key) => flattenUserRanking(src, key, 3);
        const topCheckin  = top(res.topCheckins, 'count');
        const topDuration = top(res.topDuration, 'minutes');

        ChartManager.renderBarH(
            'chartTopUserCheckins',
            topCheckin.map(e => e.label),  topCheckin.map(e => e.value),
            Analytics.rankColors.checkins.slice(0, topCheckin.length),  'Check-ins'
        );
        ChartManager.renderBarH(
            'chartTopUserDuration',
            topDuration.map(e => e.label), topDuration.map(e => Math.round(e.value)),
            Analytics.rankColors.duration.slice(0, topDuration.length), 'Minutes'
        );
        ChartManager.renderDonut(
            'chartVisitorTypeDonut',
            Object.keys(res.classificationDistribution),
            Object.values(res.classificationDistribution),
            Analytics.donutColorsVisitorType, 'Visitors'
        );

        paginateInlineTable('checkinDetailsCard',  'checkinDetailsTbody',  'checkinDetailsPager',  renderCheckinRow);
        paginateInlineTable('durationDetailsCard', 'durationDetailsTbody', 'durationDetailsPager', renderDurationRow);
    }

    function initCollegesTab(res) {
        const ck = Object.keys(res.top3CollegesCheckin);
        const dk = Object.keys(res.top3CollegesDuration);

        ChartManager.renderDonut(
            'chartCollegeCheckin',
            ck, ck.map(k => res.top3CollegesCheckin[k].count),
            ck.map(resolveCollegeColor), 'Visitors'
        );
        ChartManager.renderDonut(
            'chartCollegeDuration',
            dk, dk.map(k => Math.round(res.top3CollegesDuration[k].minutes)),
            dk.map(resolveCollegeColor), 'Minutes'
        );
    }

    function initCoursesTab(res) {
        const labels = [], checkins = [], durations = [], colors = [];

        Object.entries(res.topCoursesCheckin).forEach(([college, courses], ci) =>
            Object.entries(courses).forEach(([course, data], di) => {
                labels.push(`${college} · ${course}`);
                checkins.push(data.count);
                durations.push(Math.round(res.topCoursesDuration?.[college]?.[course]?.minutes || 0));
                colors.push(Analytics.donutColorsCourse[(ci * 3 + di) % Analytics.donutColorsCourse.length]);
            })
        );

        if (!labels.length) return;
        ChartManager.renderDonut('chartCoursesCheckin',  labels, checkins,  colors, 'Visitors');
        ChartManager.renderDonut('chartCoursesDuration', labels, durations, colors, 'Minutes');
    }

    function initDemographicsTab(res) {
        ChartManager.renderDonut(
            'chartSexDonut',
            Object.keys(res.sexDistribution),
            Object.values(res.sexDistribution),
            Analytics.donutColorsSex, 'Visitors'
        );
    }

    const TAB_INITIALIZERS = {
        logs:         initLogsTab,
        users:        initUsersTab,
        colleges:     initCollegesTab,
        courses:      initCoursesTab,
        demographics: initDemographicsTab,
    };


    // ── KPI RENDERING ─────────────────────────────────────────────────────────

    function updateKpi(res) {
        const noData = '<div class="text-muted small fst-italic">No data</div>';

        const kpiRow = (index, total, medal, left, right) =>
            `<div class="d-flex align-items-center justify-content-between gap-2 py-1
                         ${index < total - 1 ? 'border-bottom' : ''}">
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <span style="font-size:.9rem;flex-shrink:0;">${medal}</span>
                    ${left}
                </div>
                <div class="d-flex flex-column align-items-end" style="flex-shrink:0;">${right}</div>
            </div>`;

        kpi.topStudents.html(
            !res.top3Students?.length ? noData : res.top3Students.map((s, i) => kpiRow(
                i, res.top3Students.length,
                resolveRankMedal(s, i),
                `<div class="min-w-0">
                    <div class="fw-bold text-dark" style="font-size:.85rem;line-height:1.2;">${escVal(s.id_number)}</div>
                    <div class="text-muted" style="font-size:.68rem;">${escVal(s.college || '—')}${s.course ? ' · ' + escVal(s.course) : ''}</div>
                 </div>`,
                `<span class="badge rounded-pill bg-primary-subtle text-primary fw-semibold" style="font-size:.72rem;">${Number(s.count).toLocaleString()}</span>
                 <span class="text-muted" style="font-size:.62rem;">check-ins</span>`
            )).join('')
        );

        kpi.topColleges.html(
            !res.top3Colleges?.length ? noData : res.top3Colleges.map((c, i) => kpiRow(
                i, res.top3Colleges.length,
                resolveRankMedal(c, i),
                `<div class="fw-bold text-dark text-truncate" style="font-size:.85rem;">${escVal(c.name)}</div>`,
                `<span class="badge rounded-pill bg-success-subtle text-success fw-semibold" style="font-size:.72rem;">${Number(c.count).toLocaleString()}</span>
                 <span class="text-muted" style="font-size:.62rem;">students</span>`
            )).join('')
        );

        kpi.topCourses.html(
            !res.top3Courses?.length ? noData : res.top3Courses.map((c, i) => kpiRow(
                i, res.top3Courses.length,
                resolveRankMedal(c, i),
                `<div class="min-w-0">
                    <div class="fw-bold text-dark" style="font-size:.85rem;line-height:1.2;">${escVal(c.course)}</div>
                    <div style="font-size:.68rem;">
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary px-2 py-0">${escVal(c.college || '—')}</span>
                    </div>
                 </div>`,
                `<span class="badge rounded-pill bg-warning-subtle text-warning fw-semibold" style="font-size:.72rem;">${Number(c.count).toLocaleString()}</span>
                 <span class="text-muted" style="font-size:.62rem;">students</span>`
            )).join('')
        );

        $lastUpdated.html(
            '<i class="fas fa-sync-alt me-1"></i>Last updated: ' +
            new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })
        );
    }


    // ── TAB LOADER ────────────────────────────────────────────────────────────

    function loadTab(tab) {
        activeTab = tab;
        $tabButtons.removeClass('active').filter(`[data-tab="${tab}"]`).addClass('active');
        pendingXhr?.abort();
        showSpinner();

        pendingXhr = $.ajax({
            url:      Analytics.backendUrl,
            type:     'POST',
            dataType: 'json',
            data:     { action: 'tab', tab, ...getFilters() },
        })
        .done(res => {
            hideSpinner();
            if (res.status !== 'success') {
                $tabContent.html(`<div class="alert alert-danger m-3">${res.message || 'Error'}</div>`);
                return;
            }
            $tabContent.html(res.html);
            TAB_INITIALIZERS[tab]?.(res);
            updateKpi(res);
            lastResponse = res;
            preloadExportLibraries();
            $('#exportBtn').prop('disabled', false);
        })
        .fail((_, status) => {
            hideSpinner();
            if (status !== 'abort')
                $tabContent.html('<div class="alert alert-danger m-3">Failed to load analytics. Please try again.</div>');
        });
    }


    // ── VIEW ALL MODAL ────────────────────────────────────────────────────────

    function loadViewAll(tab, page) {
        showSpinner();

        $.ajax({
            url:      Analytics.backendUrl,
            type:     'POST',
            dataType: 'json',
            data:     { action: 'viewAll', tab, page, ...getFilters() },
        })
        .done(res => {
            hideSpinner();
            if (res.status !== 'success') {
                $('#viewAllModalBody').html('<div class="alert alert-danger m-3">Failed.</div>');
                if (!$('#viewAllModal').hasClass('show')) $('#viewAllModal').modal('show');
                return;
            }
            $('#viewAllModalTitle').text((Analytics.tabLabels[tab] ?? 'All') + ' Records');
            $('#viewAllModalSubtitle').text(`Page ${res.page} of ${res.totalPages} · ${res.total} records`);
            $('#viewAllModalBody').html(res.tableHtml);
            $('#viewAllModalFooter').html(res.pagination);
            if (!$('#viewAllModal').hasClass('show')) $('#viewAllModal').modal('show');
        })
        .fail(() => hideSpinner());
    }

    $(document).on('click', '#viewAllModalFooter .page-link', function (e) {
        e.preventDefault();
        const p = parseInt($(this).data('page'), 10);
        if (!isNaN(p)) { viewAllPage = p; loadViewAll(viewAllTab, viewAllPage); }
    });


    // ── EXPORT SCHEMA ─────────────────────────────────────────────────────────

    const EXPORT_SCHEMA = {

        logs: {
            label:   'Visit Logs',
            headers: [
                'ID Number', 'Name', 'College', 'Course', 'Type', 'Section',
                'Sex', 'Check-in', 'Check-out', 'Agency / Organization', 'Duration (min)',
            ],
            columnAlignments: [null, null, null, null, null, null, null, null, null, null, 'center'],
            rowMapper: res => (res.allLogs || []).map(l => [
                l.id_number,
                l.name                || '—',
                l.college             || '—',
                l.course              || '—',
                l.classification      || '—',
                l.library             || '—',
                l.sex                 || '—',
                formatDateForExport(l.checkin_time),
                l.checkout_time       ? formatDateForExport(l.checkout_time) : '—',
                l.agency_organization || '—',
                l.duration_minutes != null ? Math.round(l.duration_minutes) : '—',
            ]),
        },

        users: {
            label:   'Users',
            headers: ['ID Number', 'Name', 'College', 'Course', 'Type', 'Library Section', 'Check-ins', 'Duration (min)', 'Last Check-in'],
            rowMapper: res => {
                const rows = [];
                for (const [cls, userMap] of Object.entries(res.topCheckins))
                    for (const [uid, user] of Object.entries(userMap)) {
                        const dur = res.topDuration?.[cls]?.[uid];
                        rows.push([
                            user.display_label,
                            user.name    ?? '—',
                            user.college ?? '—',
                            user.course  ?? '—',
                            cls,
                            user.library ?? '—',
                            user.count,
                            dur ? Math.round(dur.minutes) : '—',
                            formatDateForExport(user.last_checkin),
                        ]);
                    }
                return rows;
            },
        },

        colleges: {
            label:   'Colleges',
            headers: ['College', 'Unique Visitors', 'Total Duration (min)', 'Last Check-in'],
            rowMapper: res => {
                const merged = {};
                for (const [name, data] of Object.entries(res.top3CollegesCheckin))
                    merged[name] = { count: data.count, minutes: '—', last: data.last_checkin };
                for (const [name, data] of Object.entries(res.top3CollegesDuration)) {
                    merged[name] ??= { count: '—', minutes: '—', last: data.last_checkin };
                    merged[name].minutes = Math.round(data.minutes);
                }
                return Object.entries(merged).map(([name, r]) => [
                    name, r.count, r.minutes, formatDateForExport(r.last),
                ]);
            },
        },

        courses: {
            label:   'Courses',
            headers: ['College', 'Course', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
            rowMapper: res => {
                const rows = [];
                for (const [college, courses] of Object.entries(res.topCoursesCheckin))
                    for (const [course, data] of Object.entries(courses)) {
                        const dur = res.topCoursesDuration?.[college]?.[course];
                        rows.push([college, course, data.count, dur ? Math.round(dur.minutes) : '—', formatDateForExport(data.last_checkin)]);
                    }
                return rows;
            },
        },

        demographics: {
            label:   'Demographics',
            headers: ['Sex', 'Visitors', '% of Total'],
            rowMapper: res => {
                const total = Object.values(res.sexDistribution).reduce((s, c) => s + c, 0);
                return Object.entries(res.sexDistribution).map(([sex, count]) => [
                    sex, count, total > 0 ? (count / total * 100).toFixed(1) + '%' : '0%',
                ]);
            },
        },
    };


    // ── OFFSCREEN CHART BUILDERS ──────────────────────────────────────────────

    const OFFSCREEN = { BAR_W: 900, BAR_H: 220, DONUT_W: 500, DONUT_H: 380 };

    function buildOffscreenBarChart(labels, values, colors, unit, title) {
        const canvas = Object.assign(document.createElement('canvas'), { width: OFFSCREEN.BAR_W, height: OFFSCREEN.BAR_H });
        const chart  = new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{ label: unit, data: values, backgroundColor: colors, borderRadius: 5, borderSkipped: false, barThickness: 50 }],
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
        });
        const imageDataUrl = canvas.toDataURL('image/png');
        chart.destroy();
        return { imageDataUrl, label: title, type: 'bar' };
    }

    function buildOffscreenDonutChart(labels, values, colors, centerLabel, title) {
        const canvas = Object.assign(document.createElement('canvas'), { width: OFFSCREEN.DONUT_W, height: OFFSCREEN.DONUT_H });
        const total  = values.reduce((s, v) => s + v, 0);

        const centerTextPlugin = {
            id: 'offscreenCenterText',
            afterDraw({ ctx: c, chartArea: a }) {
                if (!a) return;
                const cx = (a.left + a.right) / 2, cy = (a.top + a.bottom) / 2;
                c.save();
                c.textAlign    = 'center';
                c.textBaseline = 'middle';
                c.font         = 'bold 34px sans-serif';
                c.fillStyle    = '#111827';
                c.fillText(total.toLocaleString(), cx, cy - 14);
                c.font         = '17px sans-serif';
                c.fillStyle    = '#6b7280';
                c.fillText(centerLabel, cx, cy + 18);
                c.restore();
            },
        };

        const chart = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data: values, backgroundColor: colors, borderWidth: 3, borderColor: '#fff', hoverOffset: 0 }],
            },
            options: {
                responsive: false, animation: false, cutout: '60%', devicePixelRatio: 2,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 13 }, padding: 14, usePointStyle: true, pointStyle: 'circle',
                            generateLabels: ch => ch.data.labels.map((lbl, i) => ({
                                text:        `${lbl}  (${(ch.data.datasets[0].data[i] || 0).toLocaleString()})`,
                                fillStyle:   ch.data.datasets[0].backgroundColor[i],
                                strokeStyle: ch.data.datasets[0].backgroundColor[i],
                                hidden: false, index: i, pointStyle: 'circle',
                            })),
                        },
                    },
                },
            },
            plugins: [centerTextPlugin],
        });

        const imageDataUrl = canvas.toDataURL('image/png');
        chart.destroy();
        return { imageDataUrl, label: title, type: 'donut' };
    }

    function buildChartsForTab(tab, res) {
        switch (tab) {

            case 'logs':
                return [];

            case 'users': {
                const flatten = (src, key) => {
                    const list = [];
                    for (const m of Object.values(src))
                        for (const u of Object.values(m))
                            list.push({ label: u.display_label, value: Math.round(u[key] ?? 0) });
                    return list.sort((a, b) => b.value - a.value).slice(0, 3);
                };
                const topC = flatten(res.topCheckins, 'count');
                const topD = flatten(res.topDuration, 'minutes');
                return [
                    buildOffscreenBarChart(topC.map(e => e.label), topC.map(e => e.value), Analytics.rankColors.checkins.slice(0, topC.length), 'Check-ins', 'Top Visitors by Check-ins'),
                    buildOffscreenBarChart(topD.map(e => e.label), topD.map(e => e.value), Analytics.rankColors.duration.slice(0, topD.length), 'Minutes',   'Top Visitors by Duration'),
                    buildOffscreenDonutChart(Object.keys(res.classificationDistribution), Object.values(res.classificationDistribution), Analytics.donutColorsVisitorType, 'Visitors', 'Visitor Type Breakdown'),
                ];
            }

            case 'colleges': {
                const ck = Object.keys(res.top3CollegesCheckin);
                const dk = Object.keys(res.top3CollegesDuration);
                return [
                    buildOffscreenDonutChart(ck, ck.map(k => res.top3CollegesCheckin[k].count),              ck.map(resolveCollegeColor), 'Visitors', 'Top Colleges by Check-ins'),
                    buildOffscreenDonutChart(dk, dk.map(k => Math.round(res.top3CollegesDuration[k].minutes)), dk.map(resolveCollegeColor), 'Minutes',  'Top Colleges by Duration'),
                ];
            }

            case 'courses': {
                const labels = [], checkins = [], durations = [], colors = [];
                Object.entries(res.topCoursesCheckin).forEach(([college, courses], ci) =>
                    Object.entries(courses).forEach(([course, data], di) => {
                        labels.push(`${college} · ${course}`);
                        checkins.push(data.count);
                        durations.push(Math.round(res.topCoursesDuration?.[college]?.[course]?.minutes || 0));
                        colors.push(Analytics.donutColorsCourse[(ci * 3 + di) % Analytics.donutColorsCourse.length]);
                    })
                );
                return labels.length ? [
                    buildOffscreenDonutChart(labels, checkins,  colors, 'Visitors', 'Top Courses by Check-ins'),
                    buildOffscreenDonutChart(labels, durations, colors, 'Minutes',  'Top Courses by Duration'),
                ] : [];
            }

            case 'demographics':
                return [
                    buildOffscreenDonutChart(Object.keys(res.sexDistribution), Object.values(res.sexDistribution), Analytics.donutColorsSex, 'Visitors', 'Sex Distribution'),
                ];

            default:
                return [];
        }
    }


    // ── EXPORT HELPERS ────────────────────────────────────────────────────────

    const _scriptCache = {};

    function loadScript(url) {
        if (_scriptCache[url]) return _scriptCache[url];
        _scriptCache[url] = new Promise((resolve, reject) => {
            const existing = document.querySelector(`script[src="${url}"]`);
            if (existing) {
                // Tag exists but load may be in-flight — wait for it
                existing.addEventListener('load',  resolve, { once: true });
                existing.addEventListener('error', reject,  { once: true });
                // If already loaded (readyState trick isn't reliable for dynamic scripts,
                // so just attempt a redundant resolve after a tick)
                setTimeout(resolve, 0);
                return;
            }
            const s    = document.createElement('script');
            s.src      = url;
            s.onload   = resolve;
            s.onerror  = () => reject(new Error('Failed to load: ' + url));
            document.head.appendChild(s);
        });
        return _scriptCache[url];
    }

    function preloadExportLibraries() {
        // autotable MUST come after jsPDF — chain them
        loadScript(Analytics.exportLibraries.jspdf)
            .then(() => loadScript(Analytics.exportLibraries.autotable))
            .catch(() => {});
        // exceljs is independent
        loadScript(Analytics.exportLibraries.exceljs).catch(() => {});
    }

    async function saveBlob(blob, suggestedName, mimeType, ext) {
        if (window.showSaveFilePicker) {
            try {
                const fh = await window.showSaveFilePicker({
                    suggestedName,
                    types: [{ description: `${ext.toUpperCase()} File`, accept: { [mimeType]: ['.' + ext] } }],
                });
                const ws = await fh.createWritable();
                await ws.write(blob);
                await ws.close();
                return;
            } catch (err) {
                if (err.name === 'AbortError') return;
            }
        }
        const url = URL.createObjectURL(blob);
        const a   = Object.assign(document.createElement('a'), { href: url, download: suggestedName });
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(url), 2000);
    }

    const defaultFilename = (tabs, ext) => {
        const suffix = tabs.length === 1 ? tabs[0] : 'full';
        return `LibraryReport_${suffix}_${filters.startDate.val() || 'unknown'}_${filters.endDate.val() || 'unknown'}.${ext}`;
    };


    // ── PDF EXPORT ────────────────────────────────────────────────────────────

    async function runExportPDF(tabs, res) {
        const { jsPDF } = window.jspdf;
        const pdf       = new jsPDF('l', 'mm', 'a4');
        const margin    = 16;
        const pageW     = pdf.internal.pageSize.getWidth();
        const pageH     = pdf.internal.pageSize.getHeight();
        const contentW  = pageW - margin * 2;
        const maxDonutW = 85;
        const chartGap  = 6;
        let isFirstTab  = true;
        let currentPage = 1;
        let cursorY     = 0;

        const drawHR = (y) => {
            pdf.setDrawColor(226, 232, 240).setLineWidth(0.25);
            pdf.line(margin, y, pageW - margin, y);
        };

        const drawSectionHeading = (text, y) => {
            pdf.setFont('helvetica', 'bold').setFontSize(8.5).setTextColor(17, 24, 39);
            pdf.text(text, margin, y);
        };

        const drawChartCaption = (text, x, y, w, center = false) => {
            pdf.setFont('helvetica', 'normal').setFontSize(6.5).setTextColor(100, 116, 139);
            center ? pdf.text(text, x + w / 2, y, { align: 'center' }) : pdf.text(text, x, y);
        };

        const drawPageFooter = (n) => {
            pdf.setFont('helvetica', 'normal').setFontSize(7).setTextColor(148, 163, 184);
            pdf.text('Library Analytics Report   ·   Page ' + n, pageW / 2, pageH - 6, { align: 'center' });
            pdf.setDrawColor(226, 232, 240).setLineWidth(0.2);
            pdf.line(margin, pageH - 10, pageW - margin, pageH - 10);
        };

        // Cover header
        pdf.setFillColor(17, 24, 39);
        pdf.rect(0, 0, pageW, 18, 'F');
        pdf.setFont('helvetica', 'bold').setFontSize(11).setTextColor(255, 255, 255);
        pdf.text('Library Analytics Report', margin, 12);
        pdf.setFont('helvetica', 'normal').setFontSize(8).setTextColor(148, 163, 184);
        pdf.text(tabs.map(t => Analytics.tabLabels[t]).join(' · ') + '   ·   ' + buildDateRangeLabel(), pageW - margin, 12, { align: 'right' });

        cursorY = 24;
        pdf.setFont('helvetica', 'normal').setFontSize(7.5).setTextColor(100, 116, 139);
        pdf.text('Generated: ' + new Date().toLocaleString(), margin, cursorY);
        cursorY += 5;
        drawHR(cursorY);
        cursorY += 6;

        for (const tab of tabs) {
            if (!isFirstTab) { pdf.addPage(); cursorY = margin; currentPage++; }
            isFirstTab = false;

            const schema    = EXPORT_SCHEMA[tab];
            if (!schema) continue;
            const tableRows = schema.rowMapper(res);

            // Section banner
            pdf.setFillColor(248, 250, 252);
            pdf.rect(margin, cursorY - 2, contentW, 8, 'F');
            pdf.setFont('helvetica', 'bold').setFontSize(9.5).setTextColor(17, 24, 39);
            pdf.text(schema.label, margin + 3, cursorY + 4);
            cursorY += 12;

            // Charts
            const charts      = buildChartsForTab(tab, res);
            const barCharts   = charts.filter(c => c.type === 'bar');
            const donutCharts = charts.filter(c => c.type === 'donut');

            if (charts.length) {
                drawSectionHeading('Charts', cursorY);
                cursorY += 5;

                if (barCharts.length) {
                    const barW = (contentW - (barCharts.length - 1) * chartGap) / barCharts.length;
                    const barH = barW * (OFFSCREEN.BAR_H / OFFSCREEN.BAR_W);
                    barCharts.forEach((c, i) => {
                        const x = margin + i * (barW + chartGap);
                        drawChartCaption(c.label, x, cursorY + 4, barW);
                        pdf.addImage(c.imageDataUrl, 'PNG', x, cursorY + 6, barW, barH);
                    });
                    cursorY += barW * (OFFSCREEN.BAR_H / OFFSCREEN.BAR_W) + 12;
                }

                if (donutCharts.length) {
                    const rawW   = (contentW - (donutCharts.length - 1) * chartGap) / donutCharts.length;
                    const dW     = Math.min(maxDonutW, rawW);
                    const dH     = dW * (OFFSCREEN.DONUT_H / OFFSCREEN.DONUT_W);
                    const rowW   = donutCharts.length * dW + (donutCharts.length - 1) * chartGap;
                    const startX = margin + (contentW - rowW) / 2;
                    donutCharts.forEach((c, i) => {
                        const x = startX + i * (dW + chartGap);
                        drawChartCaption(c.label, x, cursorY + 4, dW, true);
                        pdf.addImage(c.imageDataUrl, 'PNG', x, cursorY + 6, dW, dH);
                    });
                    cursorY += dH + 12;
                }

                drawHR(cursorY);
                cursorY += 5;
            }

            // Data table
            if (cursorY + 20 > pageH - 14) {
                drawPageFooter(currentPage);
                pdf.addPage(); currentPage++; cursorY = margin;
            }

            drawSectionHeading('Data Summary', cursorY);
            pdf.setFont('helvetica', 'normal').setFontSize(7.5).setTextColor(100, 116, 139);
            pdf.text(tableRows.length + ' records', pageW - margin, cursorY, { align: 'right' });
            cursorY += 5;

            pdf.autoTable({
                head:               [schema.headers],
                body:               tableRows,
                startY:             cursorY,
                styles:             { fontSize: 8, cellPadding: 3, lineColor: [226, 232, 240], lineWidth: 0.2 },
                headStyles:         { fillColor: [17, 24, 39], textColor: [255, 255, 255], fontStyle: 'bold', fontSize: 8, cellPadding: 3.5 },
                alternateRowStyles: { fillColor: [248, 250, 252] },
                columnStyles:       { 0: { fontStyle: 'bold' } },
                margin:             { left: margin, right: margin },
                tableLineColor:     [226, 232, 240],
                tableLineWidth:     0.2,
                didDrawPage:        hook => drawPageFooter(hook.pageNumber),
            });

            cursorY = pdf.lastAutoTable.finalY + 8;
        }

        await saveBlob(
            new Blob([pdf.output('arraybuffer')], { type: 'application/pdf' }),
            defaultFilename(tabs, 'pdf'), 'application/pdf', 'pdf'
        );
    }


    // ── EXCEL EXPORT ──────────────────────────────────────────────────────────

    async function runExportExcel(tabs, res) {
        const workbook  = new window.ExcelJS.Workbook();
        const dateRange = buildDateRangeLabel();

        const fills = {
            title:  { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF111827' } },
            meta:   { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFf3f4f6' } },
            header: { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF059669' } },
            white:  { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFFFFF' } },
            zebra:  { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFf0fdf4' } },
        };

        const borders = {
            header: { top: { style: 'thin', color: { argb: 'FF047857' } }, bottom: { style: 'thin', color: { argb: 'FF047857' } }, left: { style: 'thin', color: { argb: 'FF047857' } }, right: { style: 'thin', color: { argb: 'FF047857' } } },
            data:   { top: { style: 'hair', color: { argb: 'FFe5e7eb' } }, bottom: { style: 'hair', color: { argb: 'FFe5e7eb' } }, left: { style: 'hair', color: { argb: 'FFe5e7eb' } }, right: { style: 'hair', color: { argb: 'FFe5e7eb' } } },
        };

        const align = {
            center: { horizontal: 'center', vertical: 'middle' },
            left:   { horizontal: 'left',   vertical: 'middle' },
            right:  { horizontal: 'right',  vertical: 'middle' },
        };

        const fillRow = (row, fill, colCount) => {
            for (let c = 2; c <= colCount; c++) row.getCell(c).fill = fill;
        };

        for (const tab of tabs) {
            const schema   = EXPORT_SCHEMA[tab];
            if (!schema) continue;

            const dataRows  = schema.rowMapper(res);
            const colCount  = schema.headers.length;
            const sheet     = workbook.addWorksheet(schema.label.substring(0, 31));
            sheet.views     = [{ state: 'frozen', ySplit: 5 }];

            // Row 1 — Title
            sheet.addRow([`Library Analytics Report — ${schema.label}`]);
            const r1 = sheet.getRow(1);
            r1.height = 30;
            r1.getCell(1).font      = { bold: true, color: { argb: 'FFFFFFFF' }, size: 14 };
            r1.getCell(1).fill      = fills.title;
            r1.getCell(1).alignment = align.center;
            sheet.mergeCells(1, 1, 1, colCount);
            fillRow(r1, fills.title, colCount);

            // Row 2 — Date range
            sheet.addRow([`Period: ${dateRange}`]);
            const r2 = sheet.getRow(2);
            r2.height = 18;
            r2.getCell(1).font      = { color: { argb: 'FF6b7280' }, size: 10 };
            r2.getCell(1).fill      = fills.meta;
            r2.getCell(1).alignment = align.center;
            sheet.mergeCells(2, 1, 2, colCount);
            fillRow(r2, fills.meta, colCount);

            // Row 3 — Timestamp
            sheet.addRow([`Generated: ${new Date().toLocaleString()}   ·   ${dataRows.length} records`]);
            const r3 = sheet.getRow(3);
            r3.height = 16;
            r3.getCell(1).font      = { italic: true, color: { argb: 'FF9ca3af' }, size: 9 };
            r3.getCell(1).fill      = fills.meta;
            r3.getCell(1).alignment = align.center;
            sheet.mergeCells(3, 1, 3, colCount);
            fillRow(r3, fills.meta, colCount);

            // Row 4 — Spacer
            sheet.addRow([]);
            sheet.getRow(4).height = 6;

            // Row 5 — Column headers
            sheet.addRow(schema.headers);
            const r5 = sheet.getRow(5);
            r5.height = 22;
            r5.eachCell(cell => {
                cell.font      = { bold: true, color: { argb: 'FFFFFFFF' }, size: 10 };
                cell.fill      = fills.header;
                cell.alignment = align.center;
                cell.border    = borders.header;
            });

            // Rows 6+ — Data
            sheet.addRows(dataRows);
            const firstDataRow = 6;
            const lastDataRow  = firstDataRow + dataRows.length - 1;

            for (let ri = firstDataRow; ri <= lastDataRow; ri++) {
                const row     = sheet.getRow(ri);
                const isZebra = (ri - firstDataRow) % 2 !== 0;
                row.height    = 18;
                row.eachCell({ includeEmpty: true }, (cell, colNum) => {
                    const override    = schema.columnAlignments?.[colNum - 1];
                    const defaultAlign = typeof cell.value === 'number' ? align.right : align.left;
                    cell.fill      = isZebra ? fills.zebra : fills.white;
                    cell.border    = borders.data;
                    cell.font      = { size: 10 };
                    cell.alignment = override === 'center' ? align.center
                                   : override === 'right'  ? align.right
                                   : override === 'left'   ? align.left
                                   : defaultAlign;
                });
            }

            // Column widths
            schema.headers.forEach((hdr, i) => {
                const maxLen = dataRows.reduce((max, row) => Math.max(max, String(row[i] ?? '').length), hdr.length);
                sheet.getColumn(i + 1).width = Math.min(50, maxLen + 4);
            });
        }

        const buffer = await workbook.xlsx.writeBuffer();
        const blob   = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        await saveBlob(blob, defaultFilename(tabs, 'xlsx'), blob.type, 'xlsx');
    }


    // ── EXPORT MODAL EVENTS ───────────────────────────────────────────────────

    $(document).on('click', '.export-format-option', function () {
        $('.export-format-option').removeClass('active-format');
        $(this).addClass('active-format').find('input[type="radio"]').prop('checked', true);
    });

    $('#exportCheckAll').on('change', function () {
        const checked = $(this).is(':checked');
        $('#exportSectionIndividual .export-section-check')
            .prop('checked', checked)
            .closest('label').toggleClass('opacity-50', !checked);
    });

    $('#exportSectionIndividual').on('change', '.export-section-check', function () {
        const $all = $('#exportSectionIndividual .export-section-check');
        $('#exportCheckAll').prop('checked', $all.length === $all.filter(':checked').length);
    });

    $('#exportBtn').on('click', function () {
        if (!lastResponse) { alert('No data loaded. Please generate analytics first.'); return; }
        $('#exportModal').modal('show');
    });

    $('#exportConfirmBtn').on('click', async function () {
        const sections = [];
        $('#exportSectionIndividual .export-section-check:checked').each(function () {
            sections.push($(this).val());
        });

        if (!sections.length) { alert('Please select at least one section to export.'); return; }
        if (!lastResponse)    { alert('No data available. Please generate analytics first.'); return; }

        const format = $('input[name="exportFormat"]:checked').val() || 'xlsx';
        $('#exportModal').modal('hide');
        showSpinner();

        try {
            if (format === 'pdf') {
                await loadScript(Analytics.exportLibraries.jspdf);
                await loadScript(Analytics.exportLibraries.autotable);  // strictly sequential
                await runExportPDF(sections, lastResponse);
            } else {
                await loadScript(Analytics.exportLibraries.exceljs);
                await runExportExcel(sections, lastResponse);
            }
        } catch (err) {
            console.error('Export error:', err);
            alert('Export failed: ' + err.message);
        } finally {
            hideSpinner();
        }
    });


    // ── EVENT BINDINGS ────────────────────────────────────────────────────────

    $tabButtons.on('click', function (e) {
        e.preventDefault();
        loadTab($(this).data('tab'));
    });

    $('#refreshBtn').on('click', () => { if (hasDateRange()) loadTab(activeTab); });

    $.each(filters, (_, $el) => {
        $el.on('change', () => { if (hasDateRange()) loadTab(activeTab); });
    });

    $(document).on('click', '.view-all-btn', function () {
        viewAllTab  = $(this).data('tab');
        viewAllPage = 1;
        loadViewAll(viewAllTab, viewAllPage);
    });


    // ── INIT ──────────────────────────────────────────────────────────────────

    setDefaultDateRange();
    if (hasDateRange()) loadTab(Analytics.defaultTab);

});
</script>