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
            users: 'Users', colleges: 'Colleges',
            courses: 'Courses', demographics: 'Demographics',
        },
        rankColors: {
            checkins: ['rgba(59,130,246,0.88)', 'rgba(99,102,241,0.88)', 'rgba(139,92,246,0.88)'],
            duration: ['rgba(16,185,129,0.88)', 'rgba(20,184,166,0.88)', 'rgba(8,145,178,0.88)'],
        },
        donutColorsVisitorType: ['rgba(59,130,246,0.88)', 'rgba(16,185,129,0.88)', 'rgba(245,158,11,0.88)', 'rgba(100,116,139,0.88)'],
        donutColorsSex:         ['rgba(59,130,246,0.88)', 'rgba(239,68,68,0.88)',  'rgba(100,116,139,0.88)'],
        donutColorsCourse:      ['rgba(59,130,246,0.82)', 'rgba(16,185,129,0.82)', 'rgba(245,158,11,0.82)', 'rgba(139,92,246,0.82)', 'rgba(239,68,68,0.82)', 'rgba(20,184,166,0.82)', 'rgba(100,116,139,0.82)'],
        collegeColorMap: { CAF:'rgba(22,163,74,0.88)', CAS:'rgba(234,88,12,0.88)', CBM:'rgba(202,138,4,0.88)', CET:'rgba(220,38,38,0.88)', CED:'rgba(37,99,235,0.88)', CVM:'rgba(107,114,128,0.88)' },
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
        topStudents:  $('#kpiTopStudents'),
        topColleges:  $('#kpiTopColleges'),
        topCourses:   $('#kpiTopCourses'),
    };

    // =========================================================
    //  STATE
    // =========================================================
    let activeTab = Analytics.defaultTab, pendingXhr = null;
    let viewAllTab = Analytics.defaultTab, viewAllPage = 1;
    let lastResponse = null;

    // =========================================================
    //  SPINNER
    // =========================================================
    function showSpinner() { if ($spinner.length) $spinner.stop(true).css('display', 'flex').hide().fadeIn(150); }
    function hideSpinner() { if ($spinner.length) $spinner.fadeOut(200); }

    // =========================================================
    //  FILTERS
    // =========================================================
    function getFilters() {
        return { startDate: filters.startDate.val(), endDate: filters.endDate.val(), classification: filters.classification.val(), library: filters.library.val() };
    }
    function hasDateRange() { return filters.startDate.val() && filters.endDate.val(); }
    function setDefaultDateRange() {
        if (filters.startDate.val()) return;
        const today = new Date(), start = new Date(today);
        start.setDate(today.getDate() - Analytics.defaultDays);
        filters.startDate.val(start.toISOString().split('T')[0]);
        filters.endDate.val(today.toISOString().split('T')[0]);
    }
    function buildDateRangeLabel() {
        return `${filters.startDate.val() || '—'} to ${filters.endDate.val() || '—'}`;
    }

    // =========================================================
    //  CHART MANAGER
    // =========================================================
    const ChartManager = {
        _instances: {},
        destroy(id) { if (this._instances[id]) { this._instances[id].destroy(); delete this._instances[id]; } },
        _register(id, cfg) { const c = document.getElementById(id); if (!c) return; this.destroy(id); this._instances[id] = new Chart(c, cfg); },
        _tooltip() {
            return { backgroundColor: 'rgba(15,23,42,0.92)', titleColor: '#f8fafc', bodyColor: '#94a3b8', borderColor: 'rgba(148,163,184,0.15)', borderWidth: 1, padding: 10, cornerRadius: 6 };
        },
        renderBarH(id, labels, values, colors, unit) {
            this._register(id, {
                type: 'bar',
                data: { labels, datasets: [{ label: unit, data: values, backgroundColor: colors, borderRadius: 5, borderSkipped: false, barThickness: 36 }] },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    animation: { duration: 500, easing: 'easeOutQuart' },
                    plugins: { legend: { display: false }, tooltip: { ...this._tooltip(), callbacks: { label: ctx => `  ${unit}: ${ctx.parsed.x.toLocaleString()}` } } },
                    scales: {
                        x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { color: '#6b7280', font: { size: 10 } } },
                        y: { grid: { display: false }, ticks: { color: '#374151', font: { size: 12 }, padding: 8 } },
                    },
                    layout: { padding: { right: 8 } },
                },
            });
        },
        renderDonut(id, labels, values, colors, centerLabel) {
            const total = values.reduce((s, v) => s + v, 0);
            const centerPlugin = {
                id: `center_${id}`,
                afterDraw(chart) {
                    const { ctx, chartArea: ca } = chart; if (!ca) return;
                    const cx = (ca.left + ca.right) / 2, cy = (ca.top + ca.bottom) / 2;
                    ctx.save(); ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                    ctx.font = 'bold 22px sans-serif'; ctx.fillStyle = '#111827';
                    ctx.fillText(total.toLocaleString(), cx, cy - 10);
                    ctx.font = '12px sans-serif'; ctx.fillStyle = '#6b7280';
                    ctx.fillText(centerLabel, cx, cy + 14); ctx.restore();
                },
            };
            this._register(id, {
                type: 'doughnut',
                data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: '#ffffff', hoverOffset: 6 }] },
                options: {
                    responsive: true, maintainAspectRatio: false, animation: { duration: 600, easing: 'easeInOutQuart' }, cutout: '65%',
                    plugins: {
                        legend: { position: 'bottom', labels: { color: '#374151', font: { size: 11 }, padding: 12, usePointStyle: true, pointStyle: 'circle',
                            generateLabels: chart => chart.data.labels.map((lbl, i) => ({ text: `${lbl} (${(chart.data.datasets[0].data[i] || 0).toLocaleString()})`, fillStyle: chart.data.datasets[0].backgroundColor[i], strokeStyle: chart.data.datasets[0].backgroundColor[i], hidden: false, index: i, pointStyle: 'circle' })),
                        }},
                        tooltip: { ...this._tooltip(), callbacks: { label: ctx => { const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0; return ` ${ctx.label}: ${ctx.parsed.toLocaleString()} (${pct}%)`; } } },
                    },
                },
                plugins: [centerPlugin],
            });
        },
    };

    // =========================================================
    //  CHART INITIALIZERS (live tab charts)
    // =========================================================
    function resolveCollegeColor(name) {
        const u = (name || '').toUpperCase();
        for (const [abbr, col] of Object.entries(Analytics.collegeColorMap)) { if (u.includes(abbr)) return col; }
        return Analytics.collegeColorFallback;
    }
    function flattenUserRanking(source, valueKey, topN) {
        const rows = [];
        for (const userMap of Object.values(source)) for (const u of Object.values(userMap)) rows.push({ label: u.display_label, value: u[valueKey] ?? 0 });
        return rows.sort((a, b) => b.value - a.value).slice(0, topN);
    }

    function initUsersTab(res) {
        const TOP = 3;
        const byC = flattenUserRanking(res.topCheckins, 'count', TOP);
        ChartManager.renderBarH('chartTopUserCheckins', byC.map(r => r.label), byC.map(r => r.value), Analytics.rankColors.checkins.slice(0, byC.length), 'Check-ins');
        const byD = flattenUserRanking(res.topDuration, 'minutes', TOP);
        ChartManager.renderBarH('chartTopUserDuration', byD.map(r => r.label), byD.map(r => Math.round(r.value)), Analytics.rankColors.duration.slice(0, byD.length), 'Minutes');
        ChartManager.renderDonut('chartVisitorTypeDonut', Object.keys(res.classificationDistribution), Object.values(res.classificationDistribution), Analytics.donutColorsVisitorType, 'Visitors');
        // Paginate inline tables using data-rows JSON embedded by backend
        paginateInlineTable('checkinDetailsCard', 'checkinDetailsTbody', 'checkinDetailsPager', renderCheckinRow);
        paginateInlineTable('durationDetailsCard', 'durationDetailsTbody', 'durationDetailsPager', renderDurationRow);
    }

    /** Render a single check-in detail row from JSON data */
    function renderCheckinRow(r) {
        return `<tr>
            <td class="ps-3 fw-semibold">${escVal(r.display_label)}</td>
            <td class="text-muted">${escVal(r.college || '—')}</td>
            <td class="text-muted">${escVal(r.course || '—')}</td>
            <td><span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:.68rem;">${escVal(r.type)}</span></td>
            <td class="text-muted">${escVal(r.library || '—')}</td>
            <td class="text-end fw-semibold text-primary">${Number(r.count).toLocaleString()}</td>
            <td class="text-end text-muted pe-3">${escVal(r.last_checkin)}</td>
        </tr>`;
    }

    /** Render a single duration detail row from JSON data */
    function renderDurationRow(r) {
        return `<tr>
            <td class="ps-3 fw-semibold">${escVal(r.display_label)}</td>
            <td class="text-muted">${escVal(r.college || '—')}</td>
            <td><span class="badge bg-secondary-subtle text-secondary rounded-pill" style="font-size:.68rem;">${escVal(r.type)}</span></td>
            <td class="text-end fw-semibold text-success pe-3">${Math.round(r.minutes).toLocaleString()}</td>
        </tr>`;
    }

    function escVal(v) { return $('<div>').text(v ?? '').html(); }


    /**
     * Reads rows from data-rows JSON on cardId, renders into tbodyId,
     * and builds a First/‹/pages/›/Last paginator in pagerId.
     * Page size is read from data-per-page attribute (default 3).
     */
    function paginateInlineTable(cardId, tbodyId, pagerId, rowRenderer) {
        const $card  = $('#' + cardId);
        const $tbody = $('#' + tbodyId);
        const $pager = $('#' + pagerId);
        if (!$card.length || !$tbody.length) return;

        let rows = [];
        try { rows = JSON.parse($card.attr('data-rows') || '[]'); } catch(e) { return; }
        if (!rows.length) { $tbody.html('<tr><td colspan="9" class="text-center text-muted py-3">No data</td></tr>'); return; }

        const pageSize  = parseInt($card.attr('data-per-page') || '3', 10);
        const totalPages = Math.ceil(rows.length / pageSize);
        let current = 1;

        function showPage(p) {
            current = Math.max(1, Math.min(p, totalPages));
            const slice = rows.slice((current - 1) * pageSize, current * pageSize);
            $tbody.html(slice.map(rowRenderer).join(''));
            if (totalPages <= 1) { $pager.empty(); return; }
            renderPagerNav();
        }

        function renderPagerNav() {
            const WINDOW = 5;
            const start  = Math.max(1, Math.min(current - Math.floor(WINDOW / 2), totalPages - WINDOW + 1));
            const end    = Math.min(totalPages, start + WINDOW - 1);
            const isFirst = current === 1, isLast = current === totalPages;

            const li = (label, page, disabled, active) =>
                `<li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}">` +
                `<a class="page-link" href="#" data-p="${page}">${label}</a></li>`;

            let items = '';
            items += li('«', 1,           isFirst, false);
            items += li('‹', current - 1, isFirst, false);
            if (start > 1) { items += li('1', 1, false, false); if (start > 2) items += li('…', 0, true, false); }
            for (let p = start; p <= end; p++) items += li(p, p, false, p === current);
            if (end < totalPages) { if (end < totalPages - 1) items += li('…', 0, true, false); items += li(totalPages, totalPages, false, false); }
            items += li('›', current + 1, isLast, false);
            items += li('»', totalPages,  isLast, false);

            const from = (current - 1) * pageSize + 1, to = Math.min(current * pageSize, rows.length);
            $pager.html(
                `<small class="text-muted d-block text-center mb-1" style="font-size:.7rem;">Showing ${from}–${to} of ${rows.length}</small>` +
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

    function initCollegesTab(res) {
        const cN = Object.keys(res.top3CollegesCheckin), dN = Object.keys(res.top3CollegesDuration);
        ChartManager.renderDonut('chartCollegeCheckin', cN, cN.map(n => res.top3CollegesCheckin[n].count), cN.map(resolveCollegeColor), 'Visitors');
        ChartManager.renderDonut('chartCollegeDuration', dN, dN.map(n => Math.round(res.top3CollegesDuration[n].minutes)), dN.map(resolveCollegeColor), 'Minutes');
    }
    function initCoursesTab(res) {
        function flat(source, vk) {
            const labels = [], values = [], colors = [];
            Object.entries(source).forEach(([col, courses], ci) => {
                Object.entries(courses).forEach(([crs, data], ri) => {
                    labels.push(`${col} · ${crs}`);
                    values.push(vk === 'count' ? data.count : Math.round(data.minutes));
                    colors.push(Analytics.donutColorsCourse[(ci * 3 + ri) % Analytics.donutColorsCourse.length]);
                });
            });
            return { labels, values, colors };
        }
        const c = flat(res.topCoursesCheckin, 'count'), d = flat(res.topCoursesDuration, 'minutes');
        if (c.labels.length) ChartManager.renderDonut('chartCoursesCheckin',  c.labels, c.values, c.colors, 'Visitors');
        if (d.labels.length) ChartManager.renderDonut('chartCoursesDuration', d.labels, d.values, d.colors, 'Minutes');
    }
    function initDemographicsTab(res) {
        ChartManager.renderDonut('chartSexDonut', Object.keys(res.sexDistribution), Object.values(res.sexDistribution), Analytics.donutColorsSex, 'Visitors');
    }

    const TAB_CHART_INIT = { users: initUsersTab, colleges: initCollegesTab, courses: initCoursesTab, demographics: initDemographicsTab };

    // =========================================================
    //  KPI
    // =========================================================
function renderTop3Rows(items, valueKey, labelFn, colorClass, unit = 'visits') {
        if (!items || !items.length) return '<div class="text-muted small fst-italic">No data</div>';
        const medals = ['🥇','🥈','🥉'];
        return items.map((item, i) => `
            <div class="d-flex align-items-center justify-content-between gap-2 py-1 ${i < items.length - 1 ? 'border-bottom' : ''}">
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <span style="font-size:.9rem;flex-shrink:0;">${medals[i] || (i+1)+'.'}</span>
                    <span class="small text-truncate fw-semibold">${escVal(labelFn(item))}</span>
                </div>
               <div class="d-flex flex-column align-items-end" style="flex-shrink:0;">
    <span class="badge rounded-pill ${colorClass} fw-semibold" style="font-size:.72rem;">${Number(item[valueKey]).toLocaleString()}</span>
<span class="text-muted" style="font-size:.62rem;">${unit}</span>
</div>
            </div>`).join('');
    }

    function updateKpi(res) {
        // Top 3 Students
kpi.topStudents.html(renderTop3Rows(
    res.top3Students, 'count',
    s => s.id_number + (s.college ? ' · ' + s.college : ''),
    'bg-primary-subtle text-primary', 'check-ins'
));

        // Top 3 Colleges
kpi.topColleges.html(renderTop3Rows(
    res.top3Colleges, 'count',
    c => c.name,
    'bg-success-subtle text-success', 'students from this college'
));

        // Top 3 Courses
kpi.topCourses.html(renderTop3Rows(
    res.top3Courses, 'count',
    c => c.course + (c.college ? ' · ' + c.college : ''),
    'bg-warning-subtle text-warning', 'students from this course'
));

        $lastUpdated.html('<i class="fas fa-sync-alt me-1"></i>Last updated: ' + new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }));
    }

    // =========================================================
    //  TAB LOADER
    // =========================================================
    function loadTab(tab) {
        activeTab = tab;
        $tabButtons.removeClass('active');
        $tabButtons.filter('[data-tab="' + tab + '"]').addClass('active');
        if (pendingXhr) pendingXhr.abort();
        showSpinner();
        pendingXhr = $.ajax({ url: Analytics.backendUrl, type: 'POST', dataType: 'json', data: { action: 'tab', tab, ...getFilters() } })
        .done(function (res) {
            hideSpinner();
            if (res.status !== 'success') { $tabContent.html('<div class="alert alert-danger m-3">' + (res.message || 'Error') + '</div>'); return; }
            $tabContent.html(res.html);
            TAB_CHART_INIT[tab]?.(res);
            updateKpi(res);
            lastResponse = res;
            $('#exportBtn').prop('disabled', false);
        })
        .fail(function (xhr, status) {
            hideSpinner();
            if (status !== 'abort') $tabContent.html('<div class="alert alert-danger m-3">Failed to load analytics. Please try again.</div>');
        });
    }

    // =========================================================
    //  VIEW ALL MODAL
    // =========================================================
    function loadViewAll(tab, page) {
        showSpinner();
        $.ajax({ url: Analytics.backendUrl, type: 'POST', dataType: 'json', data: { action: 'viewAll', tab, page, ...getFilters() } })
        .done(function (res) {
            hideSpinner();
            if (res.status !== 'success') { $('#viewAllModalBody').html('<div class="alert alert-danger m-3">Failed.</div>'); const $vm = $('#viewAllModal'); if (!$vm.hasClass('show')) $vm.modal('show'); return; }
            $('#viewAllModalTitle').text((Analytics.tabLabels[tab] ?? 'All') + ' Records');
            $('#viewAllModalSubtitle').text('Page ' + res.page + ' of ' + res.totalPages + ' · ' + res.total + ' records');
            $('#viewAllModalBody').html(res.tableHtml);
            // count now embedded in pagination HTML
            $('#viewAllModalFooter').html(res.pagination);
            const $vm = $('#viewAllModal'); if (!$vm.hasClass('show')) $vm.modal('show');
        })
        .fail(() => hideSpinner());
    }

    $(document).on('click', '#viewAllModalFooter .page-link', function (e) {
        e.preventDefault();
        const p = parseInt($(this).data('page'), 10);
        if (!isNaN(p)) { viewAllPage = p; loadViewAll(viewAllTab, viewAllPage); }
    });

    // =========================================================
    //  EXPORT SYSTEM
    // =========================================================

    /** Lazy-load a CDN script once; resolves immediately if already present */
    function loadScript(url) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${url}"]`)) { resolve(); return; }
            const s = document.createElement('script');
            s.src = url; s.onload = resolve;
            s.onerror = () => reject(new Error('Failed to load: ' + url));
            document.head.appendChild(s);
        });
    }

    /** Save a Blob using the File System Access API (Save As dialog) or <a download> fallback */
    async function saveBlob(blob, suggestedName, mimeType, ext) {
        if (window.showSaveFilePicker) {
            try {
                const handle = await window.showSaveFilePicker({ suggestedName, types: [{ description: ext.toUpperCase() + ' File', accept: { [mimeType]: ['.' + ext] } }] });
                const w = await handle.createWritable();
                await w.write(blob); await w.close(); return;
            } catch (err) { if (err.name === 'AbortError') return; }
        }
        const url = URL.createObjectURL(blob);
        const a   = Object.assign(document.createElement('a'), { href: url, download: suggestedName });
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(url), 2000);
    }

    function defaultFilename(tabs, ext) {
        const suffix = tabs.length === 1 ? tabs[0] : 'full';
        return `LibraryReport_${suffix}_${filters.startDate.val() || 'unknown'}_${filters.endDate.val() || 'unknown'}.${ext}`;
    }

    function fmtDate(raw) {
        if (!raw) return '—';
        const d = new Date(raw.replace(' ', 'T'));
        return isNaN(d) ? raw : d.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
    }

    /**
     * EXPORT SCHEMA — single source of truth for both PDF and Excel.
     * Each entry: { label, headers[], rowMapper(res) }
     */
    const EXPORT_SCHEMA = {
        users: {
            label: 'Users',
            headers: ['ID Number', 'Name', 'College', 'Course', 'Type', 'Library Section', 'Check-ins', 'Duration (min)', 'Last Check-in'],
            rowMapper(res) {
                const rows = [];
                for (const [type, userMap] of Object.entries(res.topCheckins)) {
                    for (const [uid, u] of Object.entries(userMap)) {
                        const dur = res.topDuration?.[type]?.[uid];
                        rows.push([u.display_label, u.name ?? '—', u.college || '—', u.course || '—', type, u.library ?? '—', u.count, dur ? Math.round(dur.minutes) : '—', fmtDate(u.last_checkin)]);
                    }
                }
                return rows;
            },
        },
        colleges: {
            label: 'Colleges',
            headers: ['College', 'Unique Visitors', 'Total Duration (min)', 'Last Check-in'],
            rowMapper(res) {
                const merged = {};
                for (const [n, d] of Object.entries(res.top3CollegesCheckin))  { merged[n] = { count: d.count, minutes: '—', last: d.last_checkin }; }
                for (const [n, d] of Object.entries(res.top3CollegesDuration)) { merged[n] ??= { count: '—', minutes: '—', last: d.last_checkin }; merged[n].minutes = Math.round(d.minutes); }
                return Object.entries(merged).map(([n, r]) => [n, r.count, r.minutes, fmtDate(r.last)]);
            },
        },
        courses: {
            label: 'Courses',
            headers: ['College', 'Course', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
            rowMapper(res) {
                const rows = [];
                for (const [col, courses] of Object.entries(res.topCoursesCheckin)) {
                    for (const [crs, data] of Object.entries(courses)) {
                        const dur = res.topCoursesDuration?.[col]?.[crs];
                        rows.push([col, crs, data.count, dur ? Math.round(dur.minutes) : '—', fmtDate(data.last_checkin)]);
                    }
                }
                return rows;
            },
        },
        demographics: {
            label: 'Demographics',
            headers: ['Sex', 'Visitors', '% of Total'],
            rowMapper(res) {
                const total = Object.values(res.sexDistribution).reduce((s, n) => s + n, 0);
                return Object.entries(res.sexDistribution).map(([sex, count]) => [sex, count, total > 0 ? (count / total * 100).toFixed(1) + '%' : '0%']);
            },
        },
    };

    // ── OFFSCREEN CHART RENDERER (for PDF export) ───────────────────────────

    const PDF_BAR_W = 900, PDF_BAR_H = 220;
    const PDF_DNT_W = 500, PDF_DNT_H = 380;

    function offscreenBarH(labels, values, colors, unit, title) {
        const canvas = document.createElement('canvas');
        canvas.width = PDF_BAR_W; canvas.height = PDF_BAR_H;
        const chart = new Chart(canvas, {
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
        const canvas = document.createElement('canvas');
        canvas.width = PDF_DNT_W; canvas.height = PDF_DNT_H;
        const total = values.reduce((s, v) => s + v, 0);
        const centerPlugin = {
            id: 'pdfCenter',
            afterDraw(chart) {
                const { ctx: c, chartArea: ca } = chart; if (!ca) return;
                const cx = (ca.left + ca.right) / 2, cy = (ca.top + ca.bottom) / 2;
                c.save(); c.textAlign = 'center'; c.textBaseline = 'middle';
                c.font = 'bold 34px sans-serif'; c.fillStyle = '#111827'; c.fillText(total.toLocaleString(), cx, cy - 14);
                c.font = '17px sans-serif'; c.fillStyle = '#6b7280'; c.fillText(centerLabel, cx, cy + 18); c.restore();
            },
        };
        const chart = new Chart(canvas, {
            type: 'doughnut',
            data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 3, borderColor: '#fff', hoverOffset: 0 }] },
            options: {
                responsive: false, animation: false, cutout: '60%', devicePixelRatio: 2,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 13 }, padding: 14, usePointStyle: true, pointStyle: 'circle',
                    generateLabels: chart => chart.data.labels.map((lbl, i) => ({ text: `${lbl}  (${(chart.data.datasets[0].data[i] || 0).toLocaleString()})`, fillStyle: chart.data.datasets[0].backgroundColor[i], strokeStyle: chart.data.datasets[0].backgroundColor[i], hidden: false, index: i, pointStyle: 'circle' })),
                }}},
            },
            plugins: [centerPlugin],
        });
        const dataUrl = canvas.toDataURL('image/png');
        chart.destroy();
        return { dataUrl, W: PDF_DNT_W, H: PDF_DNT_H, label: title, type: 'donut' };
    }

    function buildChartsForTab(tab, res) {
        const rc = c => { const u = (c || '').toUpperCase(); for (const [k, v] of Object.entries(Analytics.collegeColorMap)) if (u.includes(k)) return v; return Analytics.collegeColorFallback; };
        switch (tab) {
            case 'users': {
                const byC = [], byD = [];
                for (const m of Object.values(res.topCheckins))  for (const u of Object.values(m)) byC.push({ label: u.display_label, value: u.count ?? 0 });
                for (const m of Object.values(res.topDuration))  for (const u of Object.values(m)) byD.push({ label: u.display_label, value: Math.round(u.minutes ?? 0) });
                byC.sort((a, b) => b.value - a.value); byD.sort((a, b) => b.value - a.value);
                const topC = byC.slice(0, 3), topD = byD.slice(0, 3);
                return [
                    offscreenBarH(topC.map(r => r.label), topC.map(r => r.value), Analytics.rankColors.checkins.slice(0, topC.length), 'Check-ins', 'Top Visitors by Check-ins'),
                    offscreenBarH(topD.map(r => r.label), topD.map(r => r.value), Analytics.rankColors.duration.slice(0, topD.length),  'Minutes',   'Top Visitors by Duration'),
                    offscreenDonut(Object.keys(res.classificationDistribution), Object.values(res.classificationDistribution), Analytics.donutColorsVisitorType, 'Visitors', 'Visitor Type Breakdown'),
                ];
            }
            case 'colleges': {
                const cN = Object.keys(res.top3CollegesCheckin), dN = Object.keys(res.top3CollegesDuration);
                return [
                    offscreenDonut(cN, cN.map(n => res.top3CollegesCheckin[n].count), cN.map(rc), 'Visitors', 'Top Colleges by Check-ins'),
                    offscreenDonut(dN, dN.map(n => Math.round(res.top3CollegesDuration[n].minutes)), dN.map(rc), 'Minutes', 'Top Colleges by Duration'),
                ];
            }
            case 'courses': {
                const labels = [], cVals = [], dVals = [], colors = [];
                Object.entries(res.topCoursesCheckin).forEach(([col, courses], ci) =>
                    Object.entries(courses).forEach(([crs, data], ri) => {
                        labels.push(`${col} · ${crs}`);
                        cVals.push(data.count);
                        dVals.push(Math.round((res.topCoursesDuration?.[col]?.[crs]?.minutes) || 0));
                        colors.push(Analytics.donutColorsCourse[(ci * 3 + ri) % Analytics.donutColorsCourse.length]);
                    })
                );
                return labels.length ? [
                    offscreenDonut(labels, cVals, colors, 'Visitors', 'Top Courses by Check-ins'),
                    offscreenDonut(labels, dVals, colors, 'Minutes',  'Top Courses by Duration'),
                ] : [];
            }
            case 'demographics':
                return [offscreenDonut(Object.keys(res.sexDistribution), Object.values(res.sexDistribution), Analytics.donutColorsSex, 'Visitors', 'Sex Distribution')];
            default:
                return [];
        }
    }

    // ── PDF EXPORT ──────────────────────────────────────────────────────────
    /**
     * Builds a PDF for one or more sections.
     * @param {string[]} tabs  - e.g. ['users', 'colleges', ...]
     * @param {object}   res   - lastResponse from the backend
     */
    async function runExportPDF(tabs, res) {
        const { jsPDF } = window.jspdf;
        const doc    = new jsPDF('l', 'mm', 'a4');
        const MARGIN = 16;
        const PW     = doc.internal.pageSize.getWidth();   // 297 mm
        const PH     = doc.internal.pageSize.getHeight();  // 210 mm
        const CW     = PW - MARGIN * 2;                    // 265 mm
        const FOOTER = 14;
        const DONUT_MAX_W = 85, GAP = 6;
        let isFirstPage = true;

        const hRule = y => { doc.setDrawColor(226, 232, 240); doc.setLineWidth(0.25); doc.line(MARGIN, y, PW - MARGIN, y); };
        const sectionLabel = (text, y) => { doc.setFont('helvetica', 'bold').setFontSize(8.5).setTextColor(17, 24, 39); doc.text(text, MARGIN, y); };
        const chartLabel   = (text, x, y, w, centered = false) => { doc.setFont('helvetica', 'normal').setFontSize(6.5).setTextColor(100, 116, 139); centered ? doc.text(text, x + w / 2, y, { align: 'center' }) : doc.text(text, x, y); };
        const pageFooter   = pageNum => { doc.setFont('helvetica', 'normal').setFontSize(7).setTextColor(148, 163, 184); doc.text('Library Analytics Report   ·   Page ' + pageNum, PW / 2, PH - 6, { align: 'center' }); doc.setDrawColor(226, 232, 240); doc.setLineWidth(0.2); doc.line(MARGIN, PH - 10, PW - MARGIN, PH - 10); };

        // Global header (first page only)
        doc.setFillColor(17, 24, 39); doc.rect(0, 0, PW, 18, 'F');
        doc.setFont('helvetica', 'bold').setFontSize(11).setTextColor(255, 255, 255);
        doc.text('Library Analytics Report', MARGIN, 12);
        doc.setFont('helvetica', 'normal').setFontSize(8).setTextColor(148, 163, 184);
        doc.text(tabs.map(t => Analytics.tabLabels[t]).join(' · ') + '   ·   ' + buildDateRangeLabel(), PW - MARGIN, 12, { align: 'right' });

        let Y = 24;
        doc.setFont('helvetica', 'normal').setFontSize(7.5).setTextColor(100, 116, 139);
        doc.text('Generated: ' + new Date().toLocaleString(), MARGIN, Y);
        Y += 5; hRule(Y); Y += 6;

        let pageNum = 1;

        for (const tab of tabs) {
            if (!isFirstPage) { doc.addPage(); Y = MARGIN; pageNum++; }
            isFirstPage = false;

            const schema = EXPORT_SCHEMA[tab];
            if (!schema) continue;
            const data = schema.rowMapper(res);

            // Section heading
            doc.setFillColor(248, 250, 252); doc.rect(MARGIN, Y - 2, CW, 8, 'F');
            doc.setFont('helvetica', 'bold').setFontSize(9.5).setTextColor(17, 24, 39);
            doc.text(schema.label, MARGIN + 3, Y + 4);
            Y += 12;

            // Charts
            const chartDefs = buildChartsForTab(tab, res);
            if (chartDefs.length) {
                const bars   = chartDefs.filter(c => c.type === 'bar');
                const donuts = chartDefs.filter(c => c.type === 'donut');

                sectionLabel('Charts', Y); Y += 5;

                if (bars.length) {
                    const n = bars.length, barW = (CW - (n - 1) * GAP) / n, barH = barW * (PDF_BAR_H / PDF_BAR_W);
                    bars.forEach((ch, j) => { const x = MARGIN + j * (barW + GAP); chartLabel(ch.label, x, Y + 4, barW); doc.addImage(ch.dataUrl, 'PNG', x, Y + 6, barW, barH); });
                    Y += barH + 6 + 6;
                }
                if (donuts.length) {
                    const n = donuts.length, rawW = (CW - (n - 1) * GAP) / n, donutW = Math.min(DONUT_MAX_W, rawW), donutH = donutW * (PDF_DNT_H / PDF_DNT_W);
                    const startX = MARGIN + (CW - (n * donutW + (n - 1) * GAP)) / 2;
                    donuts.forEach((ch, j) => { const x = startX + j * (donutW + GAP); chartLabel(ch.label, x, Y + 4, donutW, true); doc.addImage(ch.dataUrl, 'PNG', x, Y + 6, donutW, donutH); });
                    Y += donutH + 6 + 6;
                }
                hRule(Y); Y += 5;
            }

            // Data table
            if (Y + 20 > PH - FOOTER) { pageFooter(pageNum); doc.addPage(); pageNum++; Y = MARGIN; }
            sectionLabel('Data Summary', Y);
            doc.setFont('helvetica', 'normal').setFontSize(7.5).setTextColor(100, 116, 139);
            doc.text(data.length + ' records', PW - MARGIN, Y, { align: 'right' });
            Y += 5;

            doc.autoTable({
                head:   [schema.headers],
                body:   data,
                startY: Y,
                styles:             { fontSize: 8, cellPadding: 3, lineColor: [226, 232, 240], lineWidth: 0.2 },
                headStyles:         { fillColor: [17, 24, 39], textColor: [255, 255, 255], fontStyle: 'bold', fontSize: 8, cellPadding: 3.5 },
                alternateRowStyles: { fillColor: [248, 250, 252] },
                columnStyles:       { 0: { fontStyle: 'bold' } },
                margin:             { left: MARGIN, right: MARGIN },
                tableLineColor:     [226, 232, 240],
                tableLineWidth:     0.2,
                didDrawPage(hook) {
                    pageNum = hook.pageNumber + (isFirstPage ? 0 : 1);
                    pageFooter(hook.pageNumber);
                },
            });

            Y = doc.lastAutoTable.finalY + 8;
        }

        const pdfBlob = new Blob([doc.output('arraybuffer')], { type: 'application/pdf' });
        await saveBlob(pdfBlob, defaultFilename(tabs, 'pdf'), 'application/pdf', 'pdf');
    }

    // ── EXCEL EXPORT ────────────────────────────────────────────────────────
    /**
     * Builds an Excel workbook with one sheet per section.
     * @param {string[]} tabs
     * @param {object}   res
     */
    async function runExportExcel(tabs, res) {
        const XLSX     = window.XLSX;
        const wb       = XLSX.utils.book_new();
        const dateRange = buildDateRangeLabel();

        for (const tab of tabs) {
            const schema = EXPORT_SCHEMA[tab];
            if (!schema) continue;
            const data = schema.rowMapper(res);

            const ws = XLSX.utils.aoa_to_sheet([
                ['Library Analytics Report — ' + schema.label],
                ['Period: ' + dateRange],
                ['Generated: ' + new Date().toLocaleString()],
                [],
                schema.headers,
                ...data,
            ]);

            // Auto column widths
            ws['!cols'] = schema.headers.map((h, ci) => {
                const vals = [h, ...data.map(r => String(r[ci] ?? ''))];
                return { wch: Math.min(50, Math.max(...vals.map(v => v.length)) + 2) };
            });

            // Merge title row
            ws['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: schema.headers.length - 1 } }];

            XLSX.utils.book_append_sheet(wb, ws, schema.label.substring(0, 31));
        }

        const wbArray = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
        const blob    = new Blob([wbArray], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        await saveBlob(blob, defaultFilename(tabs, 'xlsx'), 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'xlsx');
    }

    // ── EXPORT MODAL LOGIC ──────────────────────────────────────────────────

    // Format toggle UI
    $(document).on('click', '.export-format-option', function () {
        $('.export-format-option').removeClass('active-format');
        $(this).addClass('active-format');
        $(this).find('input[type="radio"]').prop('checked', true);
    });

    // "All Sections" master checkbox
    $('#exportCheckAll').on('change', function () {
        const checked = $(this).is(':checked');
        $('#exportSectionIndividual .export-section-check').prop('checked', checked).closest('label').toggleClass('opacity-50', !checked);
    });

    // Sync master checkbox state
    $('#exportSectionIndividual').on('change', '.export-section-check', function () {
        const allChecked = $('#exportSectionIndividual .export-section-check').length === $('#exportSectionIndividual .export-section-check:checked').length;
        $('#exportCheckAll').prop('checked', allChecked);
    });

    // Open modal
    $('#exportBtn').on('click', function () {
        if (!lastResponse) { alert('No data loaded. Please generate analytics first.'); return; }
        $('#exportModal').modal('show');
    });

    // Confirm export
    $('#exportConfirmBtn').on('click', async function () {
        const selectedSections = [];
        $('#exportSectionIndividual .export-section-check:checked').each(function () {
            selectedSections.push($(this).val());
        });

        if (!selectedSections.length) { alert('Please select at least one section to export.'); return; }

        const format = $('input[name="exportFormat"]:checked').val() || 'xlsx';
        $('#exportModal').modal('hide');

        if (!lastResponse) { alert('No data available. Please generate analytics first.'); return; }

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
    $tabButtons.on('click', function (e) { e.preventDefault(); loadTab($(this).data('tab')); });

    $('#refreshBtn').on('click', function () { if (hasDateRange()) loadTab(activeTab); });
    // Auto-generate on any filter change when both dates are set
    $.each(filters, function (k, $el) { $el.on('change', function () { if (hasDateRange()) loadTab(activeTab); }); });
    $(document).on('click', '.view-all-btn', function () { viewAllTab = $(this).data('tab'); viewAllPage = 1; loadViewAll(viewAllTab, viewAllPage); });

    // =========================================================
    //  INIT
    // =========================================================
    setDefaultDateRange();
    // Auto-load immediately since we always have dates set by setDefaultDateRange
    if (hasDateRange()) loadTab(Analytics.defaultTab);
});
</script>