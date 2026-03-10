<?php
/**
 * Library Analytics Dashboard - Frontend View
 */
include "../../db/dbconnection.php";
$librarySections = execsqlSRS("SELECT SectionID, SectionName FROM LibrarySection WHERE IsActive = 1 ORDER BY SectionName", 'Select', []);
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
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar text-muted"></i></span>
                        <input type="date" class="form-control border-start-0" id="startDate">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1">End Date</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar-check text-muted"></i></span>
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
                            <option value="<?= $s['SectionID'] ?>"><?= htmlspecialchars($s['SectionName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI CARDS -->
    <div class="row g-3 mb-4">

        <!-- Top 3 Students -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #3b82f6 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-muted small fw-semibold mb-0">Top Students</p>
                        <div class="rounded-2 bg-primary-subtle d-flex align-items-center justify-content-center" style="width:30px;height:30px;flex-shrink:0;">
                            <i class="bi bi-person-fill text-primary" style="font-size:.8rem;"></i>
                        </div>
                    </div>
                    <div id="kpiTopStudents">
                        <div class="text-muted small fst-italic">Loading…</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 3 Colleges -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #10b981 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-muted small fw-semibold mb-0">Top Colleges</p>
                        <div class="rounded-2 bg-success-subtle d-flex align-items-center justify-content-center" style="width:30px;height:30px;flex-shrink:0;">
                            <i class="bi bi-building-fill text-success" style="font-size:.8rem;"></i>
                        </div>
                    </div>
                    <div id="kpiTopColleges">
                        <div class="text-muted small fst-italic">Loading…</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 3 Courses -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #f59e0b !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-muted small fw-semibold mb-0">Top Courses</p>
                        <div class="rounded-2 bg-warning-subtle d-flex align-items-center justify-content-center" style="width:30px;height:30px;flex-shrink:0;">
                            <i class="bi bi-journal-bookmark-fill text-warning" style="font-size:.8rem;"></i>
                        </div>
                    </div>
                    <div id="kpiTopCourses">
                        <div class="text-muted small fst-italic">Loading…</div>
                    </div>
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
        <small class="text-muted" id="lastUpdatedLabel"><i class="fas fa-sync-alt me-1"></i>Last updated: —</small>
        <small class="text-muted"><i class="fas fa-database me-1"></i>Library System</small>
    </div>

</div>



<?php include '../modalContainer.php'; ?>
<?php include 'LibModals.php'; ?>


<script>
$(function () {

    // =========================================================
    //  CONFIG
    // =========================================================
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
            checkins: ['rgba(59,130,246,0.88)', 'rgba(99,102,241,0.88)', 'rgba(139,92,246,0.88)'],
            duration: ['rgba(16,185,129,0.88)', 'rgba(20,184,166,0.88)', 'rgba(8,145,178,0.88)'],
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
            jspdf:     'https://unpkg.com/jspdf@2.5.1/dist/jspdf.umd.min.js',
  autotable: 'https://unpkg.com/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js',
            exceljs:   'https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js',
        },
    };


    // =========================================================
    //  DOM REFS
    // =========================================================
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


    // =========================================================
    //  STATE
    // =========================================================
    let activeTab    = Analytics.defaultTab;
    let pendingXhr   = null;
    let viewAllTab   = Analytics.defaultTab;
    let viewAllPage  = 1;
    let lastResponse = null;


    // =========================================================
    //  SPINNER
    // =========================================================
    function showSpinner() {
        if ($spinner.length) $spinner.stop(true).css('display', 'flex').hide().fadeIn(150);
    }

    function hideSpinner() {
        if ($spinner.length) $spinner.fadeOut(200);
    }


    // =========================================================
    //  FILTERS
    // =========================================================
    function getFilters() {
        return {
            startDate:      filters.startDate.val(),
            endDate:        filters.endDate.val(),
            classification: filters.classification.val(),
            library:        filters.library.val(),
        };
    }

    function hasDateRange() {
        return filters.startDate.val() && filters.endDate.val();
    }

    function setDefaultDateRange() {
        if (filters.startDate.val()) return;

        const today = new Date();
        const start = new Date(today);
        start.setDate(today.getDate() - Analytics.defaultDays);

        filters.startDate.val(start.toISOString().split('T')[0]);
        filters.endDate.val(today.toISOString().split('T')[0]);
    }

    function buildDateRangeLabel() {
        return `${filters.startDate.val() || '—'} to ${filters.endDate.val() || '—'}`;
    }


    // =========================================================
    //  UTILITIES
    // =========================================================
    function escVal(value) {
        return $('<div>').text(value ?? '').html();
    }

    function resolveCollegeColor(name) {
        const upperName = (name || '').toUpperCase();
        for (const [abbreviation, color] of Object.entries(Analytics.collegeColorMap)) {
            if (upperName.includes(abbreviation)) return color;
        }
        return Analytics.collegeColorFallback;
    }

    /**
     * Returns medal emoji + optional tied badge using PHP-annotated rank metadata.
     */
    function resolveRankMedal(item, fallbackIndex) {
        const medals    = ['🥇', '🥈', '🥉'];
        const rank      = item.rank ?? (fallbackIndex + 1);
        const medal     = medals[rank - 1] ?? `${rank}.`;
        const tiedBadge = item.tied
            ? `<span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1"
                    style="font-size:.55rem;vertical-align:middle;">tied</span>`
            : '';
        return medal + tiedBadge;
    }

    /**
     * Flattens nested { classification -> { userId -> userData } } into a sorted array.
     */
    function flattenUserRanking(source, valueKey, topN) {
        const entries = [];

        for (const userMap of Object.values(source)) {
            for (const user of Object.values(userMap)) {
                entries.push({ label: user.display_label, value: user[valueKey] ?? 0 });
            }
        }

        return entries.sort((a, b) => b.value - a.value).slice(0, topN);
    }


    // =========================================================
    //  CHART MANAGER
    // =========================================================
    const ChartManager = {

        _instances: {},

        destroy(id) {
            if (this._instances[id]) {
                this._instances[id].destroy();
                delete this._instances[id];
            }
        },

        _register(id, config) {
            const canvas = document.getElementById(id);
            if (!canvas) return;
            this.destroy(id);
            this._instances[id] = new Chart(canvas, config);
        },

        _tooltipDefaults() {
            return {
                backgroundColor: 'rgba(15,23,42,0.92)',
                titleColor:      '#f8fafc',
                bodyColor:       '#94a3b8',
                borderColor:     'rgba(148,163,184,0.15)',
                borderWidth:     1,
                padding:         10,
                cornerRadius:    6,
            };
        },

        renderBarH(id, labels, values, colors, unit) {
            this._register(id, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label:           unit,
                        data:            values,
                        backgroundColor: colors,
                        borderRadius:    5,
                        borderSkipped:   false,
                        barThickness:    36,
                    }],
                },
                options: {
                    indexAxis:           'y',
                    responsive:          true,
                    maintainAspectRatio: false,
                    animation:           { duration: 500, easing: 'easeOutQuart' },
                    plugins: {
                        legend:  { display: false },
                        tooltip: {
                            ...this._tooltipDefaults(),
                            callbacks: { label: ctx => `  ${unit}: ${ctx.parsed.x.toLocaleString()}` },
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid:  { color: 'rgba(0,0,0,0.04)' },
                            ticks: { color: '#6b7280', font: { size: 10 } },
                        },
                        y: {
                            grid:  { display: false },
                            ticks: { color: '#374151', font: { size: 12 }, padding: 8 },
                        },
                    },
                    layout: { padding: { right: 8 } },
                },
            });
        },

        renderDonut(id, labels, values, colors, centerLabel) {
            const total = values.reduce((sum, val) => sum + val, 0);

            const centerTextPlugin = {
                id: `centerText_${id}`,
                afterDraw(chartInstance) {
                    const { ctx: context, chartArea } = chartInstance;
                    if (!chartArea) return;
                    const centerX = (chartArea.left + chartArea.right) / 2;
                    const centerY = (chartArea.top  + chartArea.bottom) / 2;
                    context.save();
                    context.textAlign    = 'center';
                    context.textBaseline = 'middle';
                    context.font         = 'bold 22px sans-serif';
                    context.fillStyle    = '#111827';
                    context.fillText(total.toLocaleString(), centerX, centerY - 10);
                    context.font      = '12px sans-serif';
                    context.fillStyle = '#6b7280';
                    context.fillText(centerLabel, centerX, centerY + 14);
                    context.restore();
                },
            };

            this._register(id, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data:            values,
                        backgroundColor: colors,
                        borderWidth:     2,
                        borderColor:     '#ffffff',
                        hoverOffset:     6,
                    }],
                },
                options: {
                    responsive:          true,
                    maintainAspectRatio: false,
                    animation:           { duration: 600, easing: 'easeInOutQuart' },
                    cutout:              '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color:         '#374151',
                                font:          { size: 11 },
                                padding:       12,
                                usePointStyle: true,
                                pointStyle:    'circle',
                                generateLabels: chartInstance => chartInstance.data.labels.map((label, index) => ({
                                    text:        `${label} (${(chartInstance.data.datasets[0].data[index] || 0).toLocaleString()})`,
                                    fillStyle:   chartInstance.data.datasets[0].backgroundColor[index],
                                    strokeStyle: chartInstance.data.datasets[0].backgroundColor[index],
                                    hidden:      false,
                                    index,
                                    pointStyle:  'circle',
                                })),
                            },
                        },
                        tooltip: {
                            ...this._tooltipDefaults(),
                            callbacks: {
                                label: ctx => {
                                    const percentage = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                    return ` ${ctx.label}: ${ctx.parsed.toLocaleString()} (${percentage}%)`;
                                },
                            },
                        },
                    },
                },
                plugins: [centerTextPlugin],
            });
        },
    };


    // =========================================================
    //  INLINE PAGINATION
    // =========================================================
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

        function showPage(page) {
            currentPage    = Math.max(1, Math.min(page, totalPages));
            const pageRows = rows.slice((currentPage - 1) * pageSize, currentPage * pageSize);
            $tbody.html(pageRows.map(rowRenderer).join(''));
            totalPages > 1 ? renderPagerNav() : $pager.empty();
        }

        function renderPagerNav() {
            const windowSize = 5;
            const start      = Math.max(1, Math.min(currentPage - Math.floor(windowSize / 2), totalPages - windowSize + 1));
            const end        = Math.min(start + windowSize - 1, totalPages);
            const isFirst    = currentPage === 1;
            const isLast     = currentPage === totalPages;

            const buildPageItem = (label, page, disabled, active) =>
                `<li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}">` +
                `<a class="page-link" href="#" data-p="${page}">${label}</a></li>`;

            let pageItems = '';
            pageItems += buildPageItem('«', 1,               isFirst, false);
            pageItems += buildPageItem('‹', currentPage - 1, isFirst, false);
            for (let pageNum = start; pageNum <= end; pageNum++) {
                pageItems += buildPageItem(pageNum, pageNum, false, pageNum === currentPage);
            }
            pageItems += buildPageItem('›', currentPage + 1, isLast, false);
            pageItems += buildPageItem('»', totalPages,       isLast, false);

            const rangeFrom = (currentPage - 1) * pageSize + 1;
            const rangeTo   = Math.min(currentPage * pageSize, rows.length);

            $pager.html(
                `<small class="text-muted d-block text-center mb-1" style="font-size:.7rem;">
                    Showing ${rangeFrom}–${rangeTo} of ${rows.length}
                </small>` +
                `<ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap">${pageItems}</ul>`
            );

            $pager.find('.page-link').off('click').on('click', function (e) {
                e.preventDefault();
                const targetPage = parseInt($(this).data('p'), 10);
                if (!isNaN(targetPage) && targetPage > 0) showPage(targetPage);
            });
        }

        showPage(1);
    }


    // =========================================================
    //  ROW RENDERERS
    // =========================================================
    function renderCheckinRow(row) {
        return `<tr>
            <td class="ps-3 fw-semibold">${escVal(row.display_label)}</td>
            <td class="text-muted">${escVal(row.college  || '—')}</td>
            <td class="text-muted">${escVal(row.course   || '—')}</td>
            <td><span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:.68rem;">
                ${escVal(row.type)}</span></td>
            <td class="text-muted">${escVal(row.library  || '—')}</td>
            <td class="text-end fw-semibold text-primary">${Number(row.count).toLocaleString()}</td>
            <td class="text-end text-muted pe-3">${escVal(row.last_checkin)}</td>
        </tr>`;
    }

    function renderDurationRow(row) {
        return `<tr>
            <td class="ps-3 fw-semibold">${escVal(row.display_label)}</td>
            <td class="text-muted">${escVal(row.college || '—')}</td>
            <td><span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:.68rem;">
                ${escVal(row.type)}</span></td>
            <td class="text-end fw-semibold text-success pe-3">${Math.round(row.minutes).toLocaleString()}</td>
        </tr>`;
    }

    function renderAllLogsRow(row) {
        return `<tr>
            <td class="ps-3 fw-semibold">${escVal(row.id_number)}</td>
            <td class="text-muted">${escVal(row.name           || '—')}</td>
            <td class="text-muted">${escVal(row.college        || '—')}</td>
            <td class="text-muted">${escVal(row.course         || '—')}</td>
            <td><span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:.68rem;">
                ${escVal(row.classification || '—')}</span></td>
            <td class="text-muted">${escVal(row.library        || '—')}</td>
            <td class="text-muted">${escVal(row.sex            || '—')}</td>
            <td class="text-end text-muted">${escVal(row.checkin_time)}</td>
            <td class="text-end text-muted pe-3">${escVal(row.checkout_time || '—')}</td>
            <td class="text-end pe-3">${row.duration_minutes != null ? Math.round(row.duration_minutes) : '—'}</td>
        </tr>`;
    }


    // =========================================================
    //  TAB INITIALIZERS
    // =========================================================
    function initLogsTab(res) {
        paginateInlineTable('allLogsCard', 'allLogsTbody', 'allLogsPager', renderAllLogsRow);
    }

    function initUsersTab(res) {
        const rankLimit        = 3;
        const topCheckinUsers  = flattenUserRanking(res.topCheckins, 'count',   rankLimit);
        const topDurationUsers = flattenUserRanking(res.topDuration, 'minutes', rankLimit);

        ChartManager.renderBarH(
            'chartTopUserCheckins',
            topCheckinUsers.map(entry => entry.label),
            topCheckinUsers.map(entry => entry.value),
            Analytics.rankColors.checkins.slice(0, topCheckinUsers.length),
            'Check-ins'
        );
        ChartManager.renderBarH(
            'chartTopUserDuration',
            topDurationUsers.map(entry => entry.label),
            topDurationUsers.map(entry => Math.round(entry.value)),
            Analytics.rankColors.duration.slice(0, topDurationUsers.length),
            'Minutes'
        );
        ChartManager.renderDonut(
            'chartVisitorTypeDonut',
            Object.keys(res.classificationDistribution),
            Object.values(res.classificationDistribution),
            Analytics.donutColorsVisitorType,
            'Visitors'
        );

        paginateInlineTable('checkinDetailsCard',  'checkinDetailsTbody',  'checkinDetailsPager',  renderCheckinRow);
        paginateInlineTable('durationDetailsCard', 'durationDetailsTbody', 'durationDetailsPager', renderDurationRow);
    }

    function initCollegesTab(res) {
        const checkinColleges  = Object.keys(res.top3CollegesCheckin);
        const durationColleges = Object.keys(res.top3CollegesDuration);

        ChartManager.renderDonut(
            'chartCollegeCheckin',
            checkinColleges,
            checkinColleges.map(college => res.top3CollegesCheckin[college].count),
            checkinColleges.map(resolveCollegeColor),
            'Visitors'
        );
        ChartManager.renderDonut(
            'chartCollegeDuration',
            durationColleges,
            durationColleges.map(college => Math.round(res.top3CollegesDuration[college].minutes)),
            durationColleges.map(resolveCollegeColor),
            'Minutes'
        );
    }

    function initCoursesTab(res) {
        const courseLabels   = [];
        const checkinValues  = [];
        const durationValues = [];
        const courseColors   = [];

        Object.entries(res.topCoursesCheckin).forEach(([college, courses], collegeIndex) => {
            Object.entries(courses).forEach(([course, data], courseIndex) => {
                courseLabels.push(`${college} · ${course}`);
                checkinValues.push(data.count);
                durationValues.push(Math.round((res.topCoursesDuration?.[college]?.[course]?.minutes) || 0));
                courseColors.push(Analytics.donutColorsCourse[(collegeIndex * 3 + courseIndex) % Analytics.donutColorsCourse.length]);
            });
        });

        if (courseLabels.length) {
            ChartManager.renderDonut('chartCoursesCheckin',  courseLabels, checkinValues,  courseColors, 'Visitors');
            ChartManager.renderDonut('chartCoursesDuration', courseLabels, durationValues, courseColors, 'Minutes');
        }
    }

    function initDemographicsTab(res) {
        ChartManager.renderDonut(
            'chartSexDonut',
            Object.keys(res.sexDistribution),
            Object.values(res.sexDistribution),
            Analytics.donutColorsSex,
            'Visitors'
        );
    }

    const TAB_INITIALIZERS = {
        logs:         initLogsTab,
        users:        initUsersTab,
        colleges:     initCollegesTab,
        courses:      initCoursesTab,
        demographics: initDemographicsTab,
    };


    // =========================================================
    //  KPI
    // =========================================================
    function updateKpi(res) {

        // ── Top 3 Students ────────────────────────────────────
        kpi.topStudents.html(
            !res.top3Students?.length
                ? '<div class="text-muted small fst-italic">No data</div>'
                : res.top3Students.map((student, index) => `
                    <div class="d-flex align-items-center justify-content-between gap-2 py-1
                                ${index < res.top3Students.length - 1 ? 'border-bottom' : ''}">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <span style="font-size:.9rem;flex-shrink:0;">${resolveRankMedal(student, index)}</span>
                            <div class="min-w-0">
                                <div class="fw-bold text-dark" style="font-size:.85rem;line-height:1.2;">
                                    ${escVal(student.id_number)}</div>
                                <div class="text-muted" style="font-size:.68rem;">
                                    ${escVal(student.college || '—')}${student.course ? ' · ' + escVal(student.course) : ''}</div>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-end" style="flex-shrink:0;">
                            <span class="badge rounded-pill bg-primary-subtle text-primary fw-semibold"
                                  style="font-size:.72rem;">${Number(student.count).toLocaleString()}</span>
                            <span class="text-muted" style="font-size:.62rem;">check-ins</span>
                        </div>
                    </div>`).join('')
        );

        // ── Top 3 Colleges ────────────────────────────────────
        kpi.topColleges.html(
            !res.top3Colleges?.length
                ? '<div class="text-muted small fst-italic">No data</div>'
                : res.top3Colleges.map((college, index) => `
                    <div class="d-flex align-items-center justify-content-between gap-2 py-1
                                ${index < res.top3Colleges.length - 1 ? 'border-bottom' : ''}">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <span style="font-size:.9rem;flex-shrink:0;">${resolveRankMedal(college, index)}</span>
                            <div class="fw-bold text-dark text-truncate" style="font-size:.85rem;">
                                ${escVal(college.name)}</div>
                        </div>
                        <div class="d-flex flex-column align-items-end" style="flex-shrink:0;">
                            <span class="badge rounded-pill bg-success-subtle text-success fw-semibold"
                                  style="font-size:.72rem;">${Number(college.count).toLocaleString()}</span>
                            <span class="text-muted" style="font-size:.62rem;">students</span>
                        </div>
                    </div>`).join('')
        );

        // ── Top 3 Courses ─────────────────────────────────────
        kpi.topCourses.html(
            !res.top3Courses?.length
                ? '<div class="text-muted small fst-italic">No data</div>'
                : res.top3Courses.map((course, index) => `
                    <div class="d-flex align-items-center justify-content-between gap-2 py-1
                                ${index < res.top3Courses.length - 1 ? 'border-bottom' : ''}">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <span style="font-size:.9rem;flex-shrink:0;">${resolveRankMedal(course, index)}</span>
                            <div class="min-w-0">
                                <div class="fw-bold text-dark" style="font-size:.85rem;line-height:1.2;">
                                    ${escVal(course.course)}</div>
                                <div style="font-size:.68rem;">
                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary px-2 py-0">
                                        ${escVal(course.college || '—')}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-end" style="flex-shrink:0;">
                            <span class="badge rounded-pill bg-warning-subtle text-warning fw-semibold"
                                  style="font-size:.72rem;">${Number(course.count).toLocaleString()}</span>
                            <span class="text-muted" style="font-size:.62rem;">students</span>
                        </div>
                    </div>`).join('')
        );

        $lastUpdated.html(
            '<i class="fas fa-sync-alt me-1"></i>Last updated: ' +
            new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })
        );
    }


    // =========================================================
    //  TAB LOADER
    // =========================================================
    function loadTab(tab) {
        activeTab = tab;

        $tabButtons.removeClass('active');
        $tabButtons.filter(`[data-tab="${tab}"]`).addClass('active');

        if (pendingXhr) pendingXhr.abort();

        showSpinner();

        pendingXhr = $.ajax({
            url:      Analytics.backendUrl,
            type:     'POST',
            dataType: 'json',
            data:     { action: 'tab', tab, ...getFilters() },
        })
        .done(function (res) {
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
        .fail(function (xhr, status) {
            hideSpinner();
            if (status !== 'abort') {
                $tabContent.html('<div class="alert alert-danger m-3">Failed to load analytics. Please try again.</div>');
            }
        });
    }


    // =========================================================
    //  VIEW ALL MODAL
    // =========================================================
    function loadViewAll(tab, page) {
        showSpinner();

        $.ajax({
            url:      Analytics.backendUrl,
            type:     'POST',
            dataType: 'json',
            data:     { action: 'viewAll', tab, page, ...getFilters() },
        })
        .done(function (res) {
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
        const targetPage = parseInt($(this).data('page'), 10);
        if (!isNaN(targetPage)) { viewAllPage = targetPage; loadViewAll(viewAllTab, viewAllPage); }
    });


    // =========================================================
    //  EXPORT SYSTEM
    // =========================================================
    function loadScript(url) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${url}"]`)) { resolve(); return; }
            const script   = document.createElement('script');
            script.src     = url;
            script.onload  = resolve;
            script.onerror = () => reject(new Error('Failed to load: ' + url));
            document.head.appendChild(script);
        });
    }

    function preloadExportLibraries() {
        Object.values(Analytics.exportLibraries).forEach(url => loadScript(url).catch(() => {}));
    }

    async function saveBlob(blob, suggestedName, mimeType, extension) {
        if (window.showSaveFilePicker) {
            try {
                const fileHandle = await window.showSaveFilePicker({
                    suggestedName,
                    types: [{ description: `${extension.toUpperCase()} File`, accept: { [mimeType]: ['.' + extension] } }],
                });
                const writableStream = await fileHandle.createWritable();
                await writableStream.write(blob);
                await writableStream.close();
                return;
            } catch (err) {
                if (err.name === 'AbortError') return;
            }
        }

        const objectUrl    = URL.createObjectURL(blob);
        const downloadLink = Object.assign(document.createElement('a'), { href: objectUrl, download: suggestedName });
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
        setTimeout(() => URL.revokeObjectURL(objectUrl), 2000);
    }

    function defaultFilename(tabs, extension) {
        const suffix = tabs.length === 1 ? tabs[0] : 'full';
        return `LibraryReport_${suffix}_${filters.startDate.val() || 'unknown'}_${filters.endDate.val() || 'unknown'}.${extension}`;
    }

    function formatDateForExport(rawDateString) {
        if (!rawDateString) return '—';
        const date = new Date(rawDateString.replace(' ', 'T'));
        return isNaN(date)
            ? rawDateString
            : date.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
    }


    // =========================================================
    //  EXPORT SCHEMA
    // =========================================================
    const EXPORT_SCHEMA = {

        logs: {
            label:   'Visit Logs',
            headers: ['ID Number', 'Name', 'College', 'Course', 'Type', 'Section', 'Sex', 'Check-in', 'Check-out', 'Duration (min)'],
			columnAlignments: [ null,        null,   null,      null,     null,   null,      null,  null,       null,        'center' ],
            rowMapper(res) {
                return (res.allLogs || []).map(log => [
                    log.id_number,
                    log.name           || '—',
                    log.college        || '—',
                    log.course         || '—',
                    log.classification || '—',
                    log.library        || '—',
                    log.sex            || '—',
                    formatDateForExport(log.checkin_time),
                    log.checkout_time  ? formatDateForExport(log.checkout_time) : '—',
                    log.duration_minutes != null ? Math.round(log.duration_minutes) : '—',
                ]);
            },
        },

        users: {
            label:   'Users',
            headers: ['ID Number', 'Name', 'College', 'Course', 'Type', 'Library Section', 'Check-ins', 'Duration (min)', 'Last Check-in'],
            rowMapper(res) {
                const rows = [];
                for (const [classification, userMap] of Object.entries(res.topCheckins)) {
                    for (const [userId, user] of Object.entries(userMap)) {
                        const durationEntry = res.topDuration?.[classification]?.[userId];
                        rows.push([
                            user.display_label,
                            user.name    ?? '—',
                            user.college || '—',
                            user.course  || '—',
                            classification,
                            user.library ?? '—',
                            user.count,
                            durationEntry ? Math.round(durationEntry.minutes) : '—',
                            formatDateForExport(user.last_checkin),
                        ]);
                    }
                }
                return rows;
            },
        },

        colleges: {
            label:   'Colleges',
            headers: ['College', 'Unique Visitors', 'Total Duration (min)', 'Last Check-in'],
            rowMapper(res) {
                const merged = {};
                for (const [name, data] of Object.entries(res.top3CollegesCheckin)) {
                    merged[name] = { count: data.count, minutes: '—', last: data.last_checkin };
                }
                for (const [name, data] of Object.entries(res.top3CollegesDuration)) {
                    merged[name]         ??= { count: '—', minutes: '—', last: data.last_checkin };
                    merged[name].minutes   = Math.round(data.minutes);
                }
                return Object.entries(merged).map(([name, row]) => [
                    name, row.count, row.minutes, formatDateForExport(row.last),
                ]);
            },
        },

        courses: {
            label:   'Courses',
            headers: ['College', 'Course', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
            rowMapper(res) {
                const rows = [];
                for (const [college, courses] of Object.entries(res.topCoursesCheckin)) {
                    for (const [course, data] of Object.entries(courses)) {
                        const durationEntry = res.topCoursesDuration?.[college]?.[course];
                        rows.push([
                            college,
                            course,
                            data.count,
                            durationEntry ? Math.round(durationEntry.minutes) : '—',
                            formatDateForExport(data.last_checkin),
                        ]);
                    }
                }
                return rows;
            },
        },

        demographics: {
            label:   'Demographics',
            headers: ['Sex', 'Visitors', '% of Total'],
            rowMapper(res) {
                const totalVisitors = Object.values(res.sexDistribution).reduce((sum, count) => sum + count, 0);
                return Object.entries(res.sexDistribution).map(([sex, count]) => [
                    sex,
                    count,
                    totalVisitors > 0 ? (count / totalVisitors * 100).toFixed(1) + '%' : '0%',
                ]);
            },
        },
    };


    // =========================================================
    //  OFFSCREEN CHART BUILDERS  (for PDF export)
    // =========================================================
    const OFFSCREEN_BAR_WIDTH    = 900;
    const OFFSCREEN_BAR_HEIGHT   = 220;
    const OFFSCREEN_DONUT_WIDTH  = 500;
    const OFFSCREEN_DONUT_HEIGHT = 380;

    function buildOffscreenBarChart(labels, values, colors, unit, title) {
        const canvas        = Object.assign(document.createElement('canvas'), { width: OFFSCREEN_BAR_WIDTH, height: OFFSCREEN_BAR_HEIGHT });
        const offscreenChart = new Chart(canvas, {
            type: 'bar',
            data: { labels, datasets: [{ label: unit, data: values, backgroundColor: colors, borderRadius: 5, borderSkipped: false, barThickness: 50 }] },
            options: {
                indexAxis: 'y', responsive: false, animation: false, devicePixelRatio: 2,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.07)' }, ticks: { font: { size: 13 }, color: '#6b7280' } },
                    y: { grid: { display: false }, ticks: { font: { size: 14 }, color: '#1f2937', padding: 6 } },
                },
                layout: { padding: { left: 4, right: 20, top: 6, bottom: 6 } },
            },
        });

        const imageDataUrl = canvas.toDataURL('image/png');
        offscreenChart.destroy();
        return { imageDataUrl, label: title, type: 'bar' };
    }

    function buildOffscreenDonutChart(labels, values, colors, centerLabel, title) {
        const canvas     = Object.assign(document.createElement('canvas'), { width: OFFSCREEN_DONUT_WIDTH, height: OFFSCREEN_DONUT_HEIGHT });
        const totalValue = values.reduce((sum, value) => sum + value, 0);

        const centerTextPlugin = {
            id: 'offscreenCenterText',
            afterDraw(chartInstance) {
                const { ctx: context, chartArea } = chartInstance;
                if (!chartArea) return;
                const centerX = (chartArea.left + chartArea.right) / 2;
                const centerY = (chartArea.top  + chartArea.bottom) / 2;
                context.save();
                context.textAlign    = 'center';
                context.textBaseline = 'middle';
                context.font         = 'bold 34px sans-serif';
                context.fillStyle    = '#111827';
                context.fillText(totalValue.toLocaleString(), centerX, centerY - 14);
                context.font      = '17px sans-serif';
                context.fillStyle = '#6b7280';
                context.fillText(centerLabel, centerX, centerY + 18);
                context.restore();
            },
        };

        const offscreenChart = new Chart(canvas, {
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
                                hidden:      false,
                                index,
                                pointStyle:  'circle',
                            })),
                        },
                    },
                },
            },
            plugins: [centerTextPlugin],
        });

        const imageDataUrl = canvas.toDataURL('image/png');
        offscreenChart.destroy();
        return { imageDataUrl, label: title, type: 'donut' };
    }

    function buildChartsForTab(tab, res) {
        switch (tab) {

            case 'logs':
                return [];

            case 'users': {
                const checkinUserList  = [];
                const durationUserList = [];

                for (const userMap of Object.values(res.topCheckins))
                    for (const user of Object.values(userMap))
                        checkinUserList.push({ label: user.display_label, value: user.count ?? 0 });

                for (const userMap of Object.values(res.topDuration))
                    for (const user of Object.values(userMap))
                        durationUserList.push({ label: user.display_label, value: Math.round(user.minutes ?? 0) });

                checkinUserList.sort((a, b)  => b.value - a.value);
                durationUserList.sort((a, b) => b.value - a.value);

                const topCheckinUsers  = checkinUserList.slice(0, 3);
                const topDurationUsers = durationUserList.slice(0, 3);

                return [
                    buildOffscreenBarChart(
                        topCheckinUsers.map(entry => entry.label),
                        topCheckinUsers.map(entry => entry.value),
                        Analytics.rankColors.checkins.slice(0, topCheckinUsers.length),
                        'Check-ins', 'Top Visitors by Check-ins'
                    ),
                    buildOffscreenBarChart(
                        topDurationUsers.map(entry => entry.label),
                        topDurationUsers.map(entry => entry.value),
                        Analytics.rankColors.duration.slice(0, topDurationUsers.length),
                        'Minutes', 'Top Visitors by Duration'
                    ),
                    buildOffscreenDonutChart(
                        Object.keys(res.classificationDistribution),
                        Object.values(res.classificationDistribution),
                        Analytics.donutColorsVisitorType,
                        'Visitors', 'Visitor Type Breakdown'
                    ),
                ];
            }

            case 'colleges': {
                const checkinColleges  = Object.keys(res.top3CollegesCheckin);
                const durationColleges = Object.keys(res.top3CollegesDuration);
                return [
                    buildOffscreenDonutChart(
                        checkinColleges,
                        checkinColleges.map(college => res.top3CollegesCheckin[college].count),
                        checkinColleges.map(resolveCollegeColor),
                        'Visitors', 'Top Colleges by Check-ins'
                    ),
                    buildOffscreenDonutChart(
                        durationColleges,
                        durationColleges.map(college => Math.round(res.top3CollegesDuration[college].minutes)),
                        durationColleges.map(resolveCollegeColor),
                        'Minutes', 'Top Colleges by Duration'
                    ),
                ];
            }

            case 'courses': {
                const courseLabels   = [];
                const checkinValues  = [];
                const durationValues = [];
                const courseColors   = [];

                Object.entries(res.topCoursesCheckin).forEach(([college, courses], collegeIndex) =>
                    Object.entries(courses).forEach(([course, data], courseIndex) => {
                        courseLabels.push(`${college} · ${course}`);
                        checkinValues.push(data.count);
                        durationValues.push(Math.round((res.topCoursesDuration?.[college]?.[course]?.minutes) || 0));
                        courseColors.push(Analytics.donutColorsCourse[(collegeIndex * 3 + courseIndex) % Analytics.donutColorsCourse.length]);
                    })
                );

                return courseLabels.length ? [
                    buildOffscreenDonutChart(courseLabels, checkinValues,  courseColors, 'Visitors', 'Top Courses by Check-ins'),
                    buildOffscreenDonutChart(courseLabels, durationValues, courseColors, 'Minutes',  'Top Courses by Duration'),
                ] : [];
            }

            case 'demographics':
                return [
                    buildOffscreenDonutChart(
                        Object.keys(res.sexDistribution),
                        Object.values(res.sexDistribution),
                        Analytics.donutColorsSex,
                        'Visitors', 'Sex Distribution'
                    ),
                ];

            default:
                return [];
        }
    }


    // =========================================================
    //  PDF EXPORT
    // =========================================================
    async function runExportPDF(tabs, res) {
        const { jsPDF }       = window.jspdf;
        const pdf             = new jsPDF('l', 'mm', 'a4');
        const margin          = 16;
        const pageWidth       = pdf.internal.pageSize.getWidth();
        const pageHeight      = pdf.internal.pageSize.getHeight();
        const contentWidth    = pageWidth - margin * 2;
        const footerClearance = 14;
        const maxDonutWidth   = 85;
        const chartGap        = 6;
        let   isFirstTab      = true;
        let   currentPage     = 1;
        let   cursorY         = 0;

        // ── Drawing helpers ───────────────────────────────────────────────
        const drawHorizontalRule = (y) => {
            pdf.setDrawColor(226, 232, 240);
            pdf.setLineWidth(0.25);
            pdf.line(margin, y, pageWidth - margin, y);
        };

        const drawSectionHeading = (text, y) => {
            pdf.setFont('helvetica', 'bold').setFontSize(8.5).setTextColor(17, 24, 39);
            pdf.text(text, margin, y);
        };

        const drawChartCaption = (text, x, y, width, centered = false) => {
            pdf.setFont('helvetica', 'normal').setFontSize(6.5).setTextColor(100, 116, 139);
            centered
                ? pdf.text(text, x + width / 2, y, { align: 'center' })
                : pdf.text(text, x, y);
        };

        const drawPageFooter = (pageNumber) => {
            pdf.setFont('helvetica', 'normal').setFontSize(7).setTextColor(148, 163, 184);
            pdf.text('Library Analytics Report   ·   Page ' + pageNumber, pageWidth / 2, pageHeight - 6, { align: 'center' });
            pdf.setDrawColor(226, 232, 240);
            pdf.setLineWidth(0.2);
            pdf.line(margin, pageHeight - 10, pageWidth - margin, pageHeight - 10);
        };

        // ── Cover header ──────────────────────────────────────────────────
        pdf.setFillColor(17, 24, 39);
        pdf.rect(0, 0, pageWidth, 18, 'F');
        pdf.setFont('helvetica', 'bold').setFontSize(11).setTextColor(255, 255, 255);
        pdf.text('Library Analytics Report', margin, 12);
        pdf.setFont('helvetica', 'normal').setFontSize(8).setTextColor(148, 163, 184);
        pdf.text(
            tabs.map(tab => Analytics.tabLabels[tab]).join(' · ') + '   ·   ' + buildDateRangeLabel(),
            pageWidth - margin, 12, { align: 'right' }
        );

        cursorY = 24;
        pdf.setFont('helvetica', 'normal').setFontSize(7.5).setTextColor(100, 116, 139);
        pdf.text('Generated: ' + new Date().toLocaleString(), margin, cursorY);
        cursorY += 5;
        drawHorizontalRule(cursorY);
        cursorY += 6;

        // ── Tab sections ──────────────────────────────────────────────────
        for (const tab of tabs) {
            if (!isFirstTab) {
                pdf.addPage();
                cursorY = margin;
                currentPage++;
            }
            isFirstTab = false;

            const tabSchema = EXPORT_SCHEMA[tab];
            if (!tabSchema) continue;

            const tableRows = tabSchema.rowMapper(res);

            // Section banner
            pdf.setFillColor(248, 250, 252);
            pdf.rect(margin, cursorY - 2, contentWidth, 8, 'F');
            pdf.setFont('helvetica', 'bold').setFontSize(9.5).setTextColor(17, 24, 39);
            pdf.text(tabSchema.label, margin + 3, cursorY + 4);
            cursorY += 12;

            // ── Charts block ──────────────────────────────────────────────
            const chartDefinitions = buildChartsForTab(tab, res);

            if (chartDefinitions.length) {
                const barCharts   = chartDefinitions.filter(chart => chart.type === 'bar');
                const donutCharts = chartDefinitions.filter(chart => chart.type === 'donut');

                drawSectionHeading('Charts', cursorY);
                cursorY += 5;

                if (barCharts.length) {
                    const barWidth  = (contentWidth - (barCharts.length - 1) * chartGap) / barCharts.length;
                    const barHeight = barWidth * (OFFSCREEN_BAR_HEIGHT / OFFSCREEN_BAR_WIDTH);

                    barCharts.forEach((chart, index) => {
                        const chartX = margin + index * (barWidth + chartGap);
                        drawChartCaption(chart.label, chartX, cursorY + 4, barWidth);
                        pdf.addImage(chart.imageDataUrl, 'PNG', chartX, cursorY + 6, barWidth, barHeight);
                    });
                    cursorY += barHeight + 12;
                }

                if (donutCharts.length) {
                    const rawDonutWidth  = (contentWidth - (donutCharts.length - 1) * chartGap) / donutCharts.length;
                    const donutWidth     = Math.min(maxDonutWidth, rawDonutWidth);
                    const donutHeight    = donutWidth * (OFFSCREEN_DONUT_HEIGHT / OFFSCREEN_DONUT_WIDTH);
                    const donutRowWidth  = donutCharts.length * donutWidth + (donutCharts.length - 1) * chartGap;
                    const donutRowStartX = margin + (contentWidth - donutRowWidth) / 2;

                    donutCharts.forEach((chart, index) => {
                        const chartX = donutRowStartX + index * (donutWidth + chartGap);
                        drawChartCaption(chart.label, chartX, cursorY + 4, donutWidth, true);
                        pdf.addImage(chart.imageDataUrl, 'PNG', chartX, cursorY + 6, donutWidth, donutHeight);
                    });
                    cursorY += donutHeight + 12;
                }

                drawHorizontalRule(cursorY);
                cursorY += 5;
            }

            // ── Data table ────────────────────────────────────────────────
            const nearPageBottom = cursorY + 20 > pageHeight - footerClearance;
            if (nearPageBottom) {
                drawPageFooter(currentPage);
                pdf.addPage();
                currentPage++;
                cursorY = margin;
            }

            drawSectionHeading('Data Summary', cursorY);
            pdf.setFont('helvetica', 'normal').setFontSize(7.5).setTextColor(100, 116, 139);
            pdf.text(tableRows.length + ' records', pageWidth - margin, cursorY, { align: 'right' });
            cursorY += 5;

            pdf.autoTable({
                head:               [tabSchema.headers],
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

        // ── Save ──────────────────────────────────────────────────────────
        const pdfBlob = new Blob([pdf.output('arraybuffer')], { type: 'application/pdf' });
        await saveBlob(pdfBlob, defaultFilename(tabs, 'pdf'), 'application/pdf', 'pdf');
    }


    // =========================================================
    //  EXCEL EXPORT
    // =========================================================
    async function runExportExcel(tabs, res) {
        const ExcelJS   = window.ExcelJS;
        const workbook  = new ExcelJS.Workbook();
        const dateRange = buildDateRangeLabel();

        // ── Style definitions ─────────────────────────────────────────────
        const titleFill  = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF111827' } };
        const metaFill   = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFf3f4f6' } };
        const headerFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF059669' } };
        const whiteFill  = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFFFFF' } };
        const zebraFill  = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFf0fdf4' } };

        const headerBorder = {
            top:    { style: 'thin', color: { argb: 'FF047857' } },
            bottom: { style: 'thin', color: { argb: 'FF047857' } },
            left:   { style: 'thin', color: { argb: 'FF047857' } },
            right:  { style: 'thin', color: { argb: 'FF047857' } },
        };
        const dataBorder = {
            top:    { style: 'hair', color: { argb: 'FFe5e7eb' } },
            bottom: { style: 'hair', color: { argb: 'FFe5e7eb' } },
            left:   { style: 'hair', color: { argb: 'FFe5e7eb' } },
            right:  { style: 'hair', color: { argb: 'FFe5e7eb' } },
        };

        const centerAlign = { horizontal: 'center', vertical: 'middle' };
        const leftAlign   = { horizontal: 'left',   vertical: 'middle' };
        const rightAlign  = { horizontal: 'right',  vertical: 'middle' };

        // ── Per-tab sheet builder ─────────────────────────────────────────
        for (const tab of tabs) {
            const tabSchema = EXPORT_SCHEMA[tab];
            if (!tabSchema) continue;

            const dataRows = tabSchema.rowMapper(res);
            const colCount = tabSchema.headers.length;
            const sheet    = workbook.addWorksheet(tabSchema.label.substring(0, 31));

            sheet.views = [{ state: 'frozen', ySplit: 5 }];

            // ── Row 1 — Title ─────────────────────────────────────────────
            sheet.addRow([`Library Analytics Report — ${tabSchema.label}`]);
            const titleRow    = sheet.getRow(1);
            titleRow.height   = 30;
            titleRow.getCell(1).font      = { bold: true, color: { argb: 'FFFFFFFF' }, size: 14 };
            titleRow.getCell(1).fill      = titleFill;
            titleRow.getCell(1).alignment = centerAlign;
            sheet.mergeCells(1, 1, 1, colCount);
            for (let col = 2; col <= colCount; col++) titleRow.getCell(col).fill = titleFill;

            // ── Row 2 — Date range ────────────────────────────────────────
            sheet.addRow([`Period: ${dateRange}`]);
            const periodRow    = sheet.getRow(2);
            periodRow.height   = 18;
            periodRow.getCell(1).font      = { color: { argb: 'FF6b7280' }, size: 10 };
            periodRow.getCell(1).fill      = metaFill;
            periodRow.getCell(1).alignment = centerAlign;
            sheet.mergeCells(2, 1, 2, colCount);
            for (let col = 2; col <= colCount; col++) periodRow.getCell(col).fill = metaFill;

            // ── Row 3 — Generated timestamp + record count ────────────────
            sheet.addRow([`Generated: ${new Date().toLocaleString()}   ·   ${dataRows.length} records`]);
            const timestampRow    = sheet.getRow(3);
            timestampRow.height   = 16;
            timestampRow.getCell(1).font      = { italic: true, color: { argb: 'FF9ca3af' }, size: 9 };
            timestampRow.getCell(1).fill      = metaFill;
            timestampRow.getCell(1).alignment = centerAlign;
            sheet.mergeCells(3, 1, 3, colCount);
            for (let col = 2; col <= colCount; col++) timestampRow.getCell(col).fill = metaFill;

            // ── Row 4 — Spacer ────────────────────────────────────────────
            sheet.addRow([]);
            sheet.getRow(4).height = 6;

            // ── Row 5 — Column headers ────────────────────────────────────
            sheet.addRow(tabSchema.headers);
            const columnHeaderRow  = sheet.getRow(5);
            columnHeaderRow.height = 22;
            columnHeaderRow.eachCell(cell => {
                cell.font      = { bold: true, color: { argb: 'FFFFFFFF' }, size: 10 };
                cell.fill      = headerFill;
                cell.alignment = centerAlign;
                cell.border    = headerBorder;
            });

            // ── Rows 6+ — Data ────────────────────────────────────────────
            sheet.addRows(dataRows);
            const firstDataRowIndex = 6;
            const lastDataRowIndex  = firstDataRowIndex + dataRows.length - 1;

for (let rowIndex = firstDataRowIndex; rowIndex <= lastDataRowIndex; rowIndex++) {
    const dataRow        = sheet.getRow(rowIndex);
    const isAlternateRow = (rowIndex - firstDataRowIndex) % 2 !== 0;
    dataRow.height       = 18;
    dataRow.eachCell({ includeEmpty: true }, (cell, colNumber) => {
        const colIndex         = colNumber - 1;  // convert to 0-based
        const overrideAlign    = tabSchema.columnAlignments?.[colIndex];
        const defaultAlign     = typeof cell.value === 'number' ? rightAlign : leftAlign;

        cell.fill      = isAlternateRow ? zebraFill : whiteFill;
        cell.border    = dataBorder;
        cell.font      = { size: 10 };
        cell.alignment = overrideAlign === 'center' ? centerAlign
                       : overrideAlign === 'right'  ? rightAlign
                       : overrideAlign === 'left'   ? leftAlign
                       : defaultAlign;
    });
}

            // ── Column widths — auto-fit from content ─────────────────────
            tabSchema.headers.forEach((headerLabel, colIndex) => {
                const maxContentLength = dataRows.reduce((max, row) => {
                    return Math.max(max, String(row[colIndex] ?? '').length);
                }, headerLabel.length);
                sheet.getColumn(colIndex + 1).width = Math.min(50, maxContentLength + 4);
            });
        }

        // ── Write and trigger download ────────────────────────────────────
        const fileBuffer = await workbook.xlsx.writeBuffer();
        const fileBlob   = new Blob([fileBuffer], {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        });
        await saveBlob(fileBlob, defaultFilename(tabs, 'xlsx'), fileBlob.type, 'xlsx');
    }


    // =========================================================
    //  EXPORT MODAL EVENTS
    // =========================================================
    $(document).on('click', '.export-format-option', function () {
        $('.export-format-option').removeClass('active-format');
        $(this).addClass('active-format').find('input[type="radio"]').prop('checked', true);
    });

    $('#exportCheckAll').on('change', function () {
        const isChecked = $(this).is(':checked');
        $('#exportSectionIndividual .export-section-check')
            .prop('checked', isChecked)
            .closest('label')
            .toggleClass('opacity-50', !isChecked);
    });

    $('#exportSectionIndividual').on('change', '.export-section-check', function () {
        const $allChecks  = $('#exportSectionIndividual .export-section-check');
        const allSelected = $allChecks.length === $allChecks.filter(':checked').length;
        $('#exportCheckAll').prop('checked', allSelected);
    });

    $('#exportBtn').on('click', function () {
        if (!lastResponse) { alert('No data loaded. Please generate analytics first.'); return; }
        $('#exportModal').modal('show');
    });

    $('#exportConfirmBtn').on('click', async function () {
        const selectedSections = [];
        $('#exportSectionIndividual .export-section-check:checked').each(function () {
            selectedSections.push($(this).val());
        });

        if (!selectedSections.length) { alert('Please select at least one section to export.'); return; }
        if (!lastResponse)            { alert('No data available. Please generate analytics first.'); return; }

        const selectedFormat = $('input[name="exportFormat"]:checked').val() || 'xlsx';
        $('#exportModal').modal('hide');
        showSpinner();

        try {
            if (selectedFormat === 'pdf') {
                await loadScript(Analytics.exportLibraries.jspdf);
                await loadScript(Analytics.exportLibraries.autotable);
                await runExportPDF(selectedSections, lastResponse);
            } else {
                await loadScript(Analytics.exportLibraries.exceljs);
                await runExportExcel(selectedSections, lastResponse);
            }
        } catch (err) {
            console.error('Export error:', err);
            alert('Export failed: ' + err.message);
        } finally {
            hideSpinner();
        }
    });


    // =========================================================
    //  EVENT BINDINGS
    // =========================================================
    $tabButtons.on('click', function (e) {
        e.preventDefault();
        loadTab($(this).data('tab'));
    });

    $('#refreshBtn').on('click', function () {
        if (hasDateRange()) loadTab(activeTab);
    });

    $.each(filters, function (key, $filterElement) {
        $filterElement.on('change', function () {
            if (hasDateRange()) loadTab(activeTab);
        });
    });

    $(document).on('click', '.view-all-btn', function () {
        viewAllTab  = $(this).data('tab');
        viewAllPage = 1;
        loadViewAll(viewAllTab, viewAllPage);
    });


    // =========================================================
    //  INIT
    // =========================================================
    setDefaultDateRange();
    if (hasDateRange()) loadTab(Analytics.defaultTab);
});
</script>