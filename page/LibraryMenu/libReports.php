<?php
/**
 * Library Analytics Dashboard - Frontend View
 */
include "../../db/dbconnection.php";
$librarySections = execsqlSRS("SELECT SectionID, SectionName FROM LibrarySection WHERE IsActive = 1 ORDER BY SectionName", 'Select', []);
?>

<div class="container-fluid py-4">

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
                <div class="card-body py-3 px-3">
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
                <div class="card-body py-3 px-3">
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
                <div class="card-body py-3 px-3">
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
            jspdf:     'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
            autotable: 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js',
            xlsx:      'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js',
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
        const upper = (name || '').toUpperCase();
        for (const [abbr, color] of Object.entries(Analytics.collegeColorMap)) {
            if (upper.includes(abbr)) return color;
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
        const rows = [];

        for (const userMap of Object.values(source)) {
            for (const user of Object.values(userMap)) {
                rows.push({ label: user.display_label, value: user[valueKey] ?? 0 });
            }
        }

        return rows.sort((a, b) => b.value - a.value).slice(0, topN);
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

        _register(id, cfg) {
            const canvas = document.getElementById(id);
            if (!canvas) return;
            this.destroy(id);
            this._instances[id] = new Chart(canvas, cfg);
        },

        _tooltip() {
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
                    indexAxis:              'y',
                    responsive:             true,
                    maintainAspectRatio:    false,
                    animation:              { duration: 500, easing: 'easeOutQuart' },
                    plugins: {
                        legend:  { display: false },
                        tooltip: {
                            ...this._tooltip(),
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

            const centerPlugin = {
                id: `center_${id}`,
                afterDraw(chart) {
                    const { ctx, chartArea: ca } = chart;
                    if (!ca) return;

                    const cx = (ca.left + ca.right) / 2;
                    const cy = (ca.top + ca.bottom) / 2;

                    ctx.save();
                    ctx.textAlign    = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font         = 'bold 22px sans-serif';
                    ctx.fillStyle    = '#111827';
                    ctx.fillText(total.toLocaleString(), cx, cy - 10);
                    ctx.font      = '12px sans-serif';
                    ctx.fillStyle = '#6b7280';
                    ctx.fillText(centerLabel, cx, cy + 14);
                    ctx.restore();
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
                                color:        '#374151',
                                font:         { size: 11 },
                                padding:      12,
                                usePointStyle: true,
                                pointStyle:   'circle',
                                generateLabels: chart => chart.data.labels.map((lbl, i) => ({
                                    text:        `${lbl} (${(chart.data.datasets[0].data[i] || 0).toLocaleString()})`,
                                    fillStyle:   chart.data.datasets[0].backgroundColor[i],
                                    strokeStyle: chart.data.datasets[0].backgroundColor[i],
                                    hidden:      false,
                                    index:       i,
                                    pointStyle:  'circle',
                                })),
                            },
                        },
                        tooltip: {
                            ...this._tooltip(),
                            callbacks: {
                                label: ctx => {
                                    const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                    return ` ${ctx.label}: ${ctx.parsed.toLocaleString()} (${pct}%)`;
                                },
                            },
                        },
                    },
                },
                plugins: [centerPlugin],
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
        let   current    = 1;

        function showPage(page) {
            current      = Math.max(1, Math.min(page, totalPages));
            const slice  = rows.slice((current - 1) * pageSize, current * pageSize);
            $tbody.html(slice.map(rowRenderer).join(''));
            totalPages > 1 ? renderPagerNav() : $pager.empty();
        }

function renderPagerNav() {
    const WINDOW  = 5;
    const start   = Math.max(1, Math.min(current - Math.floor(WINDOW / 2), totalPages - WINDOW + 1));
    const end     = Math.min(start + WINDOW - 1, totalPages);
    const isFirst = current === 1;
    const isLast  = current === totalPages;

    const li = (label, page, disabled, active) =>
        `<li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}">` +
        `<a class="page-link" href="#" data-p="${page}">${label}</a></li>`;

    let items = '';
    items += li('«', 1,           isFirst, false);
    items += li('‹', current - 1, isFirst, false);
    for (let p = start; p <= end; p++) items += li(p, p, false, p === current);
    items += li('›', current + 1, isLast, false);
    items += li('»', totalPages,  isLast, false);

    const from = (current - 1) * pageSize + 1;
    const to   = Math.min(current * pageSize, rows.length);

    $pager.html(
        `<small class="text-muted d-block text-center mb-1" style="font-size:.7rem;">
            Showing ${from}–${to} of ${rows.length}
        </small>` +
        `<ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap">${items}</ul>`
    );

    $pager.find('.page-link').off('click').on('click', function (e) {
        e.preventDefault();
        const p = parseInt($(this).data('p'), 10);
        if (!isNaN(p) && p > 0) showPage(p);
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
            <td class="text-muted">${escVal(row.name      || '—')}</td>
            <td class="text-muted">${escVal(row.college   || '—')}</td>
            <td class="text-muted">${escVal(row.course    || '—')}</td>
            <td><span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:.68rem;">
                ${escVal(row.classification || '—')}</span></td>
            <td class="text-muted">${escVal(row.library   || '—')}</td>
            <td class="text-muted">${escVal(row.sex       || '—')}</td>
            <td class="text-end text-muted">${escVal(row.checkin_time)}</td>
            <td class="text-end text-muted pe-3">${escVal(row.checkout_time || '—')}</td>
            <td class="text-end pe-3">${row.duration_minutes != null ? Math.round(row.duration_minutes) : '—'}</td>
        </tr>`;
    }


    // =========================================================
    //  CHART INITIALIZERS
    // =========================================================
function initUsersTab(res) {
    const TOP = 3;

    const byC = flattenUserRanking(res.topCheckins, 'count',   TOP);
    const byD = flattenUserRanking(res.topDuration, 'minutes', TOP);

    ChartManager.renderBarH(
        'chartTopUserCheckins',
        byC.map(r => r.label), byC.map(r => r.value),
        Analytics.rankColors.checkins.slice(0, byC.length), 'Check-ins'
    );
    ChartManager.renderBarH(
        'chartTopUserDuration',
        byD.map(r => r.label), byD.map(r => Math.round(r.value)),
        Analytics.rankColors.duration.slice(0, byD.length), 'Minutes'
    );
    ChartManager.renderDonut(
        'chartVisitorTypeDonut',
        Object.keys(res.classificationDistribution),
        Object.values(res.classificationDistribution),
        Analytics.donutColorsVisitorType, 'Visitors'
    );

    paginateInlineTable('checkinDetailsCard',  'checkinDetailsTbody',  'checkinDetailsPager',  renderCheckinRow);
    paginateInlineTable('durationDetailsCard', 'durationDetailsTbody', 'durationDetailsPager', renderDurationRow);
    paginateInlineTable('allLogsCard',         'allLogsTbody',         'allLogsPager',         renderAllLogsRow);
}

    function initCollegesTab(res) {
        const cN = Object.keys(res.top3CollegesCheckin);
        const dN = Object.keys(res.top3CollegesDuration);

        ChartManager.renderDonut(
            'chartCollegeCheckin',
            cN, cN.map(n => res.top3CollegesCheckin[n].count),
            cN.map(resolveCollegeColor),
            'Visitors'
        );
        ChartManager.renderDonut(
            'chartCollegeDuration',
            dN, dN.map(n => Math.round(res.top3CollegesDuration[n].minutes)),
            dN.map(resolveCollegeColor),
            'Minutes'
        );
    }

    function initCoursesTab(res) {
        const labels = [], cVals = [], dVals = [], colors = [];

        Object.entries(res.topCoursesCheckin).forEach(([college, courses], ci) => {
            Object.entries(courses).forEach(([course, data], ri) => {
                labels.push(`${college} · ${course}`);
                cVals.push(data.count);
                dVals.push(Math.round((res.topCoursesDuration?.[college]?.[course]?.minutes) || 0));
                colors.push(Analytics.donutColorsCourse[(ci * 3 + ri) % Analytics.donutColorsCourse.length]);
            });
        });

        if (labels.length) {
            ChartManager.renderDonut('chartCoursesCheckin',  labels, cVals, colors, 'Visitors');
            ChartManager.renderDonut('chartCoursesDuration', labels, dVals, colors, 'Minutes');
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

    const TAB_CHART_INIT = {
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
                : res.top3Students.map((s, i) => `
                    <div class="d-flex align-items-center justify-content-between gap-2 py-1
                                ${i < res.top3Students.length - 1 ? 'border-bottom' : ''}">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <span style="font-size:.9rem;flex-shrink:0;">${resolveRankMedal(s, i)}</span>
                            <div class="min-w-0">
                                <div class="fw-bold text-dark" style="font-size:.85rem;line-height:1.2;">
                                    ${escVal(s.id_number)}</div>
                                <div class="text-muted" style="font-size:.68rem;">
                                    ${escVal(s.college || '—')}${s.course ? ' · ' + escVal(s.course) : ''}</div>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-end" style="flex-shrink:0;">
                            <span class="badge rounded-pill bg-primary-subtle text-primary fw-semibold"
                                  style="font-size:.72rem;">${Number(s.count).toLocaleString()}</span>
                            <span class="text-muted" style="font-size:.62rem;">check-ins</span>
                        </div>
                    </div>`).join('')
        );

        // ── Top 3 Colleges ────────────────────────────────────
        kpi.topColleges.html(
            !res.top3Colleges?.length
                ? '<div class="text-muted small fst-italic">No data</div>'
                : res.top3Colleges.map((c, i) => `
                    <div class="d-flex align-items-center justify-content-between gap-2 py-1
                                ${i < res.top3Colleges.length - 1 ? 'border-bottom' : ''}">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <span style="font-size:.9rem;flex-shrink:0;">${resolveRankMedal(c, i)}</span>
                            <div class="fw-bold text-dark text-truncate" style="font-size:.85rem;">
                                ${escVal(c.name)}</div>
                        </div>
                        <div class="d-flex flex-column align-items-end" style="flex-shrink:0;">
                            <span class="badge rounded-pill bg-success-subtle text-success fw-semibold"
                                  style="font-size:.72rem;">${Number(c.count).toLocaleString()}</span>
                            <span class="text-muted" style="font-size:.62rem;">students</span>
                        </div>
                    </div>`).join('')
        );

        // ── Top 3 Courses ─────────────────────────────────────
        kpi.topCourses.html(
            !res.top3Courses?.length
                ? '<div class="text-muted small fst-italic">No data</div>'
                : res.top3Courses.map((c, i) => `
                    <div class="d-flex align-items-center justify-content-between gap-2 py-1
                                ${i < res.top3Courses.length - 1 ? 'border-bottom' : ''}">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <span style="font-size:.9rem;flex-shrink:0;">${resolveRankMedal(c, i)}</span>
                            <div class="min-w-0">
                                <div class="fw-bold text-dark" style="font-size:.85rem;line-height:1.2;">
                                    ${escVal(c.course)}</div>
                                <div style="font-size:.68rem;">
                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary px-2 py-0">
                                        ${escVal(c.college || '—')}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-end" style="flex-shrink:0;">
                            <span class="badge rounded-pill bg-warning-subtle text-warning fw-semibold"
                                  style="font-size:.72rem;">${Number(c.count).toLocaleString()}</span>
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
            TAB_CHART_INIT[tab]?.(res);
            updateKpi(res);

            lastResponse = res;
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
        const page = parseInt($(this).data('page'), 10);
        if (!isNaN(page)) { viewAllPage = page; loadViewAll(viewAllTab, viewAllPage); }
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

    async function saveBlob(blob, suggestedName, mimeType, ext) {
        if (window.showSaveFilePicker) {
            try {
                const handle = await window.showSaveFilePicker({
                    suggestedName,
                    types: [{ description: `${ext.toUpperCase()} File`, accept: { [mimeType]: ['.' + ext] } }],
                });
                const writer = await handle.createWritable();
                await writer.write(blob);
                await writer.close();
                return;
            } catch (err) {
                if (err.name === 'AbortError') return;
            }
        }

        const url    = URL.createObjectURL(blob);
        const anchor = Object.assign(document.createElement('a'), { href: url, download: suggestedName });
        document.body.appendChild(anchor);
        anchor.click();
        document.body.removeChild(anchor);
        setTimeout(() => URL.revokeObjectURL(url), 2000);
    }

    function defaultFilename(tabs, ext) {
        const suffix = tabs.length === 1 ? tabs[0] : 'full';
        return `LibraryReport_${suffix}_${filters.startDate.val() || 'unknown'}_${filters.endDate.val() || 'unknown'}.${ext}`;
    }

    function fmtDate(raw) {
        if (!raw) return '—';
        const date = new Date(raw.replace(' ', 'T'));
        return isNaN(date)
            ? raw
            : date.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
    }

    // ── Export schema ─────────────────────────────────────────
    const EXPORT_SCHEMA = {

        users: {
            label:   'Users',
            headers: ['ID Number', 'Name', 'College', 'Course', 'Type', 'Library Section', 'Check-ins', 'Duration (min)', 'Last Check-in'],
            rowMapper(res) {
                const rows = [];
                for (const [type, userMap] of Object.entries(res.topCheckins)) {
                    for (const [userId, user] of Object.entries(userMap)) {
                        const dur = res.topDuration?.[type]?.[userId];
                        rows.push([
                            user.display_label,
                            user.name    ?? '—',
                            user.college || '—',
                            user.course  || '—',
                            type,
                            user.library ?? '—',
                            user.count,
                            dur ? Math.round(dur.minutes) : '—',
                            fmtDate(user.last_checkin),
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
                return Object.entries(merged).map(([name, row]) => [name, row.count, row.minutes, fmtDate(row.last)]);
            },
        },

        courses: {
            label:   'Courses',
            headers: ['College', 'Course', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
            rowMapper(res) {
                const rows = [];
                for (const [college, courses] of Object.entries(res.topCoursesCheckin)) {
                    for (const [course, data] of Object.entries(courses)) {
                        const dur = res.topCoursesDuration?.[college]?.[course];
                        rows.push([college, course, data.count, dur ? Math.round(dur.minutes) : '—', fmtDate(data.last_checkin)]);
                    }
                }
                return rows;
            },
        },

        demographics: {
            label:   'Demographics',
            headers: ['Sex', 'Visitors', '% of Total'],
            rowMapper(res) {
                const total = Object.values(res.sexDistribution).reduce((sum, n) => sum + n, 0);
                return Object.entries(res.sexDistribution).map(([sex, count]) => [
                    sex, count,
                    total > 0 ? (count / total * 100).toFixed(1) + '%' : '0%',
                ]);
            },
        },
    };

    // ── Offscreen chart helpers ───────────────────────────────
    const PDF_BAR_W = 900, PDF_BAR_H = 220;
    const PDF_DNT_W = 500, PDF_DNT_H = 380;

    function offscreenBarH(labels, values, colors, unit, title) {
        const canvas  = Object.assign(document.createElement('canvas'), { width: PDF_BAR_W, height: PDF_BAR_H });
        const chart   = new Chart(canvas, {
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
        const dataUrl = canvas.toDataURL('image/png');
        chart.destroy();
        return { dataUrl, W: PDF_BAR_W, H: PDF_BAR_H, label: title, type: 'bar' };
    }

    function offscreenDonut(labels, values, colors, centerLabel, title) {
        const canvas = Object.assign(document.createElement('canvas'), { width: PDF_DNT_W, height: PDF_DNT_H });
        const total  = values.reduce((s, v) => s + v, 0);

        const centerPlugin = {
            id: 'pdfCenter',
            afterDraw(chart) {
                const { ctx, chartArea: ca } = chart;
                if (!ca) return;
                const cx = (ca.left + ca.right) / 2;
                const cy = (ca.top + ca.bottom) / 2;
                ctx.save();
                ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                ctx.font = 'bold 34px sans-serif'; ctx.fillStyle = '#111827';
                ctx.fillText(total.toLocaleString(), cx, cy - 14);
                ctx.font = '17px sans-serif'; ctx.fillStyle = '#6b7280';
                ctx.fillText(centerLabel, cx, cy + 18);
                ctx.restore();
            },
        };

        const chart   = new Chart(canvas, {
            type: 'doughnut',
            data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 3, borderColor: '#fff', hoverOffset: 0 }] },
            options: {
                responsive: false, animation: false, cutout: '60%', devicePixelRatio: 2,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 13 }, padding: 14, usePointStyle: true, pointStyle: 'circle',
                            generateLabels: chart => chart.data.labels.map((lbl, i) => ({
                                text:        `${lbl}  (${(chart.data.datasets[0].data[i] || 0).toLocaleString()})`,
                                fillStyle:   chart.data.datasets[0].backgroundColor[i],
                                strokeStyle: chart.data.datasets[0].backgroundColor[i],
                                hidden:      false,
                                index:       i,
                                pointStyle:  'circle',
                            })),
                        },
                    },
                },
            },
            plugins: [centerPlugin],
        });

        const dataUrl = canvas.toDataURL('image/png');
        chart.destroy();
        return { dataUrl, W: PDF_DNT_W, H: PDF_DNT_H, label: title, type: 'donut' };
    }

    function buildChartsForTab(tab, res) {
        switch (tab) {

            case 'users': {
                const byC = [], byD = [];
                for (const userMap of Object.values(res.topCheckins))
                    for (const user of Object.values(userMap))
                        byC.push({ label: user.display_label, value: user.count ?? 0 });
                for (const userMap of Object.values(res.topDuration))
                    for (const user of Object.values(userMap))
                        byD.push({ label: user.display_label, value: Math.round(user.minutes ?? 0) });

                byC.sort((a, b) => b.value - a.value);
                byD.sort((a, b) => b.value - a.value);

                const topC = byC.slice(0, 3);
                const topD = byD.slice(0, 3);

                return [
                    offscreenBarH(topC.map(r => r.label), topC.map(r => r.value), Analytics.rankColors.checkins.slice(0, topC.length), 'Check-ins', 'Top Visitors by Check-ins'),
                    offscreenBarH(topD.map(r => r.label), topD.map(r => r.value), Analytics.rankColors.duration.slice(0, topD.length),  'Minutes',   'Top Visitors by Duration'),
                    offscreenDonut(Object.keys(res.classificationDistribution), Object.values(res.classificationDistribution), Analytics.donutColorsVisitorType, 'Visitors', 'Visitor Type Breakdown'),
                ];
            }

            case 'colleges': {
                const cN = Object.keys(res.top3CollegesCheckin);
                const dN = Object.keys(res.top3CollegesDuration);
                return [
                    offscreenDonut(cN, cN.map(n => res.top3CollegesCheckin[n].count),                       cN.map(resolveCollegeColor), 'Visitors', 'Top Colleges by Check-ins'),
                    offscreenDonut(dN, dN.map(n => Math.round(res.top3CollegesDuration[n].minutes)), dN.map(resolveCollegeColor), 'Minutes',  'Top Colleges by Duration'),
                ];
            }

            case 'courses': {
                const labels = [], cVals = [], dVals = [], colors = [];
                Object.entries(res.topCoursesCheckin).forEach(([college, courses], ci) =>
                    Object.entries(courses).forEach(([course, data], ri) => {
                        labels.push(`${college} · ${course}`);
                        cVals.push(data.count);
                        dVals.push(Math.round((res.topCoursesDuration?.[college]?.[course]?.minutes) || 0));
                        colors.push(Analytics.donutColorsCourse[(ci * 3 + ri) % Analytics.donutColorsCourse.length]);
                    })
                );
                return labels.length ? [
                    offscreenDonut(labels, cVals, colors, 'Visitors', 'Top Courses by Check-ins'),
                    offscreenDonut(labels, dVals, colors, 'Minutes',  'Top Courses by Duration'),
                ] : [];
            }

            case 'demographics':
                return [
                    offscreenDonut(Object.keys(res.sexDistribution), Object.values(res.sexDistribution), Analytics.donutColorsSex, 'Visitors', 'Sex Distribution'),
                ];

            default:
                return [];
        }
    }

    // ── PDF export ────────────────────────────────────────────
    async function runExportPDF(tabs, res) {
        const { jsPDF } = window.jspdf;
        const doc    = new jsPDF('l', 'mm', 'a4');
        const MARGIN = 16;
        const PW     = doc.internal.pageSize.getWidth();
        const PH     = doc.internal.pageSize.getHeight();
        const CW     = PW - MARGIN * 2;
        const FOOTER = 14;
        const DONUT_MAX_W = 85, GAP = 6;
        let isFirstPage = true;
        let pageNum     = 1;

        const hRule        = y => { doc.setDrawColor(226,232,240); doc.setLineWidth(0.25); doc.line(MARGIN, y, PW - MARGIN, y); };
        const sectionLabel = (text, y) => { doc.setFont('helvetica','bold').setFontSize(8.5).setTextColor(17,24,39); doc.text(text, MARGIN, y); };
        const chartLabel   = (text, x, y, w, centered = false) => { doc.setFont('helvetica','normal').setFontSize(6.5).setTextColor(100,116,139); centered ? doc.text(text, x + w / 2, y, { align: 'center' }) : doc.text(text, x, y); };
        const pageFooter   = n => { doc.setFont('helvetica','normal').setFontSize(7).setTextColor(148,163,184); doc.text('Library Analytics Report   ·   Page ' + n, PW / 2, PH - 6, { align: 'center' }); doc.setDrawColor(226,232,240); doc.setLineWidth(0.2); doc.line(MARGIN, PH - 10, PW - MARGIN, PH - 10); };

        doc.setFillColor(17,24,39); doc.rect(0, 0, PW, 18, 'F');
        doc.setFont('helvetica','bold').setFontSize(11).setTextColor(255,255,255);
        doc.text('Library Analytics Report', MARGIN, 12);
        doc.setFont('helvetica','normal').setFontSize(8).setTextColor(148,163,184);
        doc.text(tabs.map(t => Analytics.tabLabels[t]).join(' · ') + '   ·   ' + buildDateRangeLabel(), PW - MARGIN, 12, { align: 'right' });

        let Y = 24;
        doc.setFont('helvetica','normal').setFontSize(7.5).setTextColor(100,116,139);
        doc.text('Generated: ' + new Date().toLocaleString(), MARGIN, Y);
        Y += 5; hRule(Y); Y += 6;

        for (const tab of tabs) {
            if (!isFirstPage) { doc.addPage(); Y = MARGIN; pageNum++; }
            isFirstPage = false;

            const schema = EXPORT_SCHEMA[tab];
            if (!schema) continue;

            const data = schema.rowMapper(res);

            doc.setFillColor(248,250,252); doc.rect(MARGIN, Y - 2, CW, 8, 'F');
            doc.setFont('helvetica','bold').setFontSize(9.5).setTextColor(17,24,39);
            doc.text(schema.label, MARGIN + 3, Y + 4);
            Y += 12;

            const chartDefs = buildChartsForTab(tab, res);
            if (chartDefs.length) {
                const bars   = chartDefs.filter(c => c.type === 'bar');
                const donuts = chartDefs.filter(c => c.type === 'donut');

                sectionLabel('Charts', Y); Y += 5;

                if (bars.length) {
                    const barW = (CW - (bars.length - 1) * GAP) / bars.length;
                    const barH = barW * (PDF_BAR_H / PDF_BAR_W);
                    bars.forEach((c, i) => { const x = MARGIN + i * (barW + GAP); chartLabel(c.label, x, Y + 4, barW); doc.addImage(c.dataUrl, 'PNG', x, Y + 6, barW, barH); });
                    Y += barH + 12;
                }
                if (donuts.length) {
                    const rawW   = (CW - (donuts.length - 1) * GAP) / donuts.length;
                    const donutW = Math.min(DONUT_MAX_W, rawW);
                    const donutH = donutW * (PDF_DNT_H / PDF_DNT_W);
                    const startX = MARGIN + (CW - (donuts.length * donutW + (donuts.length - 1) * GAP)) / 2;
                    donuts.forEach((c, i) => { const x = startX + i * (donutW + GAP); chartLabel(c.label, x, Y + 4, donutW, true); doc.addImage(c.dataUrl, 'PNG', x, Y + 6, donutW, donutH); });
                    Y += donutH + 12;
                }
                hRule(Y); Y += 5;
            }

            if (Y + 20 > PH - FOOTER) { pageFooter(pageNum); doc.addPage(); pageNum++; Y = MARGIN; }
            sectionLabel('Data Summary', Y);
            doc.setFont('helvetica','normal').setFontSize(7.5).setTextColor(100,116,139);
            doc.text(data.length + ' records', PW - MARGIN, Y, { align: 'right' });
            Y += 5;

            doc.autoTable({
                head: [schema.headers], body: data, startY: Y,
                styles:             { fontSize: 8, cellPadding: 3, lineColor: [226,232,240], lineWidth: 0.2 },
                headStyles:         { fillColor: [17,24,39], textColor: [255,255,255], fontStyle: 'bold', fontSize: 8, cellPadding: 3.5 },
                alternateRowStyles: { fillColor: [248,250,252] },
                columnStyles:       { 0: { fontStyle: 'bold' } },
                margin:             { left: MARGIN, right: MARGIN },
                tableLineColor:     [226,232,240], tableLineWidth: 0.2,
                didDrawPage:        hook => pageFooter(hook.pageNumber),
            });
            Y = doc.lastAutoTable.finalY + 8;
        }

        const pdfBlob = new Blob([doc.output('arraybuffer')], { type: 'application/pdf' });
        await saveBlob(pdfBlob, defaultFilename(tabs, 'pdf'), 'application/pdf', 'pdf');
    }

    // ── Excel export ──────────────────────────────────────────
    async function runExportExcel(tabs, res) {
        const XLSX      = window.XLSX;
        const wb        = XLSX.utils.book_new();
        const dateRange = buildDateRangeLabel();

        for (const tab of tabs) {
            const schema = EXPORT_SCHEMA[tab];
            if (!schema) continue;

            const data = schema.rowMapper(res);
            const ws   = XLSX.utils.aoa_to_sheet([
                [`Library Analytics Report — ${schema.label}`],
                [`Period: ${dateRange}`],
                [`Generated: ${new Date().toLocaleString()}`],
                [],
                schema.headers,
                ...data,
            ]);

            ws['!cols']   = schema.headers.map((h, ci) => {
                const vals = [h, ...data.map(row => String(row[ci] ?? ''))];
                return { wch: Math.min(50, Math.max(...vals.map(v => v.length)) + 2) };
            });
            ws['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: schema.headers.length - 1 } }];

            XLSX.utils.book_append_sheet(wb, ws, schema.label.substring(0, 31));
        }

        const wbArray = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
        const blob    = new Blob([wbArray], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        await saveBlob(blob, defaultFilename(tabs, 'xlsx'), 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'xlsx');
    }


    // =========================================================
    //  EXPORT MODAL EVENTS
    // =========================================================
    $(document).on('click', '.export-format-option', function () {
        $('.export-format-option').removeClass('active-format');
        $(this).addClass('active-format').find('input[type="radio"]').prop('checked', true);
    });

    $('#exportCheckAll').on('change', function () {
        const checked = $(this).is(':checked');
        $('#exportSectionIndividual .export-section-check')
            .prop('checked', checked)
            .closest('label')
            .toggleClass('opacity-50', !checked);
    });

    $('#exportSectionIndividual').on('change', '.export-section-check', function () {
        const $checks    = $('#exportSectionIndividual .export-section-check');
        const allChecked = $checks.length === $checks.filter(':checked').length;
        $('#exportCheckAll').prop('checked', allChecked);
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

        const format = $('input[name="exportFormat"]:checked').val() || 'xlsx';
        $('#exportModal').modal('hide');
        showSpinner();

        try {
            if (format === 'pdf') {
                await loadScript(Analytics.exportLibraries.jspdf);
                await loadScript(Analytics.exportLibraries.autotable);
                await runExportPDF(selectedSections, lastResponse);
            } else {
                await loadScript(Analytics.exportLibraries.xlsx);
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

    $.each(filters, function (key, $el) {
        $el.on('change', function () {
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