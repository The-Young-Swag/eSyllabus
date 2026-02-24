<?php
include "../../db/dbconnection.php";

$librarySections = execsqlSRS(
    "SELECT SectionID, SectionName FROM LibrarySection WHERE IsActive = 1 ORDER BY SectionName",
    'Select', []
);

$tabs = [
    ['key' => 'users',        'icon' => 'bi-people-fill',          'label' => 'Users'],
    ['key' => 'colleges',     'icon' => 'bi-building-fill',         'label' => 'Colleges'],
    ['key' => 'courses',      'icon' => 'bi-journal-bookmark-fill', 'label' => 'Courses'],
    ['key' => 'demographics', 'icon' => 'bi-bar-chart-fill',        'label' => 'Demographics'],
];
?>


<div class="container-fluid py-4">

    <!-- HEADER -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div>
                <h4 class="fw-bold mb-1">Library Analytics Dashboard</h4>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge px-3 py-2 rounded-4" style="background:linear-gradient(90deg,#f3f4f6,#ffffff); color:#374151;">
                        <i class="fas fa-calendar-alt me-1 text-muted"></i> <span id="headerDateRange">—</span>
                    </span>
                    <span class="badge px-3 py-2 rounded-4" style="background:linear-gradient(90deg,#f3f4f6,#ffffff); color:#374151;">
                        <i class="fas fa-users me-1 text-muted"></i> <span id="headerVisitors">—</span> Visitors
                    </span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-outline-secondary btn-sm rounded-4 shadow-sm" id="refreshBtn">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
            <button class="btn btn-danger btn-sm rounded-4 shadow-sm" id="exportPDF">
                <i class="fas fa-file-pdf me-1"></i> Export Report
            </button>
        </div>
    </div>

    <!-- FILTERS -->
    <div class="card border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Start Date</label>
                    <div class="input-group input-group-sm shadow-sm rounded-4 overflow-hidden" style="background:linear-gradient(90deg,#e6f4ea,#ffffff);">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar text-muted"></i></span>
                        <input type="date" class="form-control border-start-0 ps-0" id="startDate" style="background:transparent;">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">End Date</label>
                    <div class="input-group input-group-sm shadow-sm rounded-4 overflow-hidden" style="background:linear-gradient(90deg,#e6f4ea,#ffffff);">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar-check text-muted"></i></span>
                        <input type="date" class="form-control border-start-0 ps-0" id="endDate" style="background:transparent;">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">User Classification</label>
                    <select class="form-select form-select-sm shadow-sm rounded-4" id="filterClassification" style="background:linear-gradient(90deg,#f3f9f7,#ffffff); border:none;">
                        <option value="All">All</option>
                        <option value="Student">Student</option>
                        <option value="Employee">Employee</option>
                        <option value="Guest">Guest</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Library</label>
                    <select class="form-select form-select-sm shadow-sm rounded-4" id="filterLibrary" style="background:linear-gradient(90deg,#f3f9f7,#ffffff); border:none;">
                        <option value="All">All Libraries</option>
                        <?php foreach ($librarySections as $lib): ?>
                            <option value="<?= $lib['SectionID'] ?>"><?= htmlspecialchars($lib['SectionName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>
            <div class="row mt-4">
                <div class="col-12 col-md-4 offset-md-8">
                    <button class="btn w-100 fw-semibold text-white shadow-sm" id="generateBtn"
                        style="background:linear-gradient(90deg,#047857,#10b981); border-radius:0.5rem; font-size:1rem;">
                        <i class="fas fa-chart-bar me-1"></i> Generate Analytics
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background:linear-gradient(135deg,#10b981,#047857);">
                <div class="card-body text-white">
                    <small class="fw-semibold text-white-50">Total Check-ins</small>
                    <h3 class="fw-bold mt-1 mb-0" id="kpiTotalVisits">—</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                <div class="card-body text-white">
                    <small class="fw-semibold text-white-50">Avg. Duration</small>
                    <h3 class="fw-bold mt-1 mb-0" id="kpiAvgDuration">—</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background:linear-gradient(135deg,#6b7280,#374151);">
                <div class="card-body text-white">
                    <small class="fw-semibold text-white-50" id="kpiEndDateLabel">Check-ins on —</small>
                    <h3 class="fw-bold mt-1 mb-0" id="kpiEndDateCheckins">—</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB NAV -->
    <div class="mb-4">
        <ul class="nav nav-tabs border-0 rounded-3 overflow-hidden shadow-sm d-flex flex-nowrap p-2"
            id="analyticsTabs"
            style="background:#f0f4fa; border:1px solid #d6dff0 !important; gap:6px;">
            <?php foreach ($tabs as $tab): ?>
            <li class="nav-item flex-fill" role="presentation">
                <button class="tab-btn nav-link w-100 d-flex align-items-center justify-content-center gap-2 rounded-2 border-0 fw-medium px-3 py-2"
                    data-tab="<?= $tab['key'] ?>"
                    style="background:transparent; color:#5a6a8a; font-size:0.875rem;">
                    <i class="bi <?= $tab['icon'] ?>"></i> <?= $tab['label'] ?>
                </button>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!--
        ALL PANES LIVE IN THE DOM PERMANENTLY.
        Tab clicks only toggle CSS display — no HTML is destroyed, no reflow, no bounce.
        Content inside .tab-inner is replaced only when new data arrives from the server.
    -->
    <div id="tabPanesWrapper">
 <!--
        <div id="tabPlaceholder" class="text-center text-muted p-5">
            Select a date range and click <strong>Generate Analytics</strong> to begin.
        </div>
    -->
        <?php foreach ($tabs as $tab): ?>
        <div class="tab-pane-content" id="pane-<?= $tab['key'] ?>" style="display:none;">
            <!-- Spinner floats above content — pane height is never lost -->
            <div class="tab-pane-overlay">
                <div class="spinner-border text-primary"></div>
            </div>
            <div class="tab-inner"></div>
        </div>
        <?php endforeach; ?>

    </div>

    <!-- FOOTER -->
    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
        <small class="text-muted" id="lastUpdated">
            <i class="fas fa-sync-alt me-1"></i> Not yet loaded
        </small>
        <div class="d-flex gap-3 flex-wrap">
            <small class="text-muted"><i class="fas fa-database me-1"></i> Source: Library System</small>
        </div>
    </div>

</div>

<?php include '../modalContainer.php'; ?>
<?php include 'LibModals.php'; ?>

<script>
$(function () {

    // ============================================================
    //  STATE
    // ============================================================
    let activeTab    = 'users';
    let activeXHR    = null;     // In-flight request — aborted if a new one starts
    let chartReg     = {};       // { canvasId: Chart instance }
    let loadedTabs   = {};       // { tabKey: true } — tabs with current data
    let lastFilters  = null;     // JSON snapshot of filters at last successful load


    // ============================================================
    //  INIT
    // ============================================================
    setDefaultDates();
    loadTab('users', true);


    // ============================================================
    //  EVENTS
    // ============================================================

    // Tab click: show pane instantly, fetch only if data is stale
    $('#analyticsTabs').on('click', '.tab-btn', function () {
        loadTab($(this).data('tab'), false);
    });

    // Generate / Refresh: invalidate all tabs, reload active tab now
    $('#generateBtn, #refreshBtn').on('click', function () {
        if (!$('#startDate').val() || !$('#endDate').val()) {
            return alert('Please select a start and end date.');
        }
        invalidateAndReload();
    });

    // Filter change: invalidate all, reload active tab immediately
    // Other tabs will lazily re-fetch when next visited
    $('#startDate, #endDate, #filterClassification, #filterLibrary').on('change', function () {
        if ($('#startDate').val() && $('#endDate').val()) invalidateAndReload();
    });

    // View All modal
    $(document).on('click', '.view-all-btn', function () {
        openViewAllModal($(this).data('tab'));
    });


    // ============================================================
    //  CORE: LOAD TAB
    //  1. Show the pane instantly via CSS (zero AJAX, zero bounce).
    //  2. Only fetch if the tab is stale or filters changed.
    // ============================================================
    function loadTab(tab, force) {
        showPane(tab);

        const needsFetch = force || !loadedTabs[tab] || filtersChanged();
        if (!needsFetch) return;

        fetchTabData(tab);
    }

    function invalidateAndReload() {
        loadedTabs = {};
        loadTab(activeTab, true);
    }


    // ============================================================
    //  FETCH
    //  Overlays spinner on existing content so pane height stays stable.
    // ============================================================
    function fetchTabData(tab) {
        const $pane    = $('#pane-' + tab);
        const $overlay = $pane.find('.tab-pane-overlay');
        const $inner   = $pane.find('.tab-inner');

        $overlay.show();
        $inner.css('opacity', 0.3);

        if (activeXHR) activeXHR.abort();

        activeXHR = $.post('backend/bk_LibraryMenu/bk_libReports - (UPDATED-BACKUP).php', {
            request: 'getTabData',
            tab,
            ...getFilters(),
        })
        .done(function (res) {
            if (res.status !== 'success') {
                return $inner.html('<div class="alert alert-danger">' + (res.message || 'Failed to load.') + '</div>');
            }

            $inner.html(res.html);
            renderCharts(tab, res.chartData);
            updateKPIs(res.kpis);

            loadedTabs[tab] = true;
            lastFilters     = { ...getFilters() };

            updateHeader();
            updateFooterTimestamp();
        })
        .fail(function (xhr) {
            if (xhr.statusText !== 'abort') {
                $inner.html('<div class="alert alert-danger">Request failed. Please try again.</div>');
            }
        })
        .always(function () {
            $overlay.hide();
            $inner.css('opacity', 1);
        });
    }


    // ============================================================
    //  PANE VISIBILITY  ← THE FIX FOR BOUNCING
    //  Pure CSS show/hide. Nothing is removed from the DOM.
    // ============================================================
    function showPane(tab) {
        $('.tab-pane-content').hide();
        $('#pane-' + tab).show();

        if (Object.keys(loadedTabs).length > 0) {
            $('#tabPlaceholder').hide();
        }

        setActiveTabButton(tab);
        activeTab = tab;
    }

    function setActiveTabButton(tab) {
        $('.tab-btn').each(function () {
            const isActive = $(this).data('tab') === tab;
            $(this).toggleClass('active', isActive).css(
                isActive
                    ? { background: 'linear-gradient(135deg,#3a6cf4,#6a3de8)', color: '#fff', boxShadow: '0 3px 12px rgba(58,108,244,0.28)' }
                    : { background: 'transparent', color: '#5a6a8a', boxShadow: 'none' }
            );
        });
    }


    // ============================================================
    //  KPI + HEADER UPDATES
    // ============================================================
    function updateKPIs(kpis) {
        $('#kpiTotalVisits').text(Number(kpis.totalVisits).toLocaleString());
        $('#kpiAvgDuration').text(kpis.avgDuration + ' hrs');

        const endDate = $('#endDate').val();
        const label   = endDate ? fmtDateShort(endDate + 'T00:00:00') : '—';
        $('#kpiEndDateLabel').text('Check-ins on ' + label);
        $('#kpiEndDateCheckins').text(Number(kpis.endDateCheckins).toLocaleString());

        $('#headerVisitors').text(Number(kpis.totalVisits).toLocaleString());
    }

    function updateHeader() {
        const start = $('#startDate').val();
        const end   = $('#endDate').val();
        if (start && end) {
            $('#headerDateRange').text(fmtDateShort(start + 'T00:00:00') + ' – ' + fmtDateShort(end + 'T00:00:00'));
        }
    }

    function updateFooterTimestamp() {
        const time = new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true });
        $('#lastUpdated').html('<i class="fas fa-sync-alt me-1"></i> Last updated: Today at ' + time);
    }


    // ============================================================
    //  CHARTS
    // ============================================================
    function destroyChart(id) {
        if (chartReg[id]) { chartReg[id].destroy(); delete chartReg[id]; }
    }

    function destroyChartsForTab(tab) {
        const staticIds = {
            users:        ['chartUsersCheckin', 'chartUsersDuration'],
            colleges:     ['chartCollegeCheckin', 'chartCollegeDuration'],
            demographics: ['chartSexCheckin'],
        };

        if (staticIds[tab]) {
            staticIds[tab].forEach(destroyChart);
        } else {
            // Courses: dynamic canvas IDs
            Object.keys(chartReg).filter(id => id.startsWith('chartCourse')).forEach(destroyChart);
        }
    }

    function makeBarChart(canvasId, labels, values, label) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        destroyChart(canvasId);
        chartReg[canvasId] = new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{ label, data: values, backgroundColor: 'rgba(54,162,235,0.7)', borderRadius: 8, maxBarThickness: 40 }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, animation: { duration: 250 },
                plugins: { legend: { display: false } },
                scales:  { y: { beginAtZero: true } },
            },
        });
    }

    function makeDoughnutChart(canvasId, labels, values) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        destroyChart(canvasId);
        chartReg[canvasId] = new Chart(canvas, {
            type: 'doughnut',
            data: { labels, datasets: [{ data: values, backgroundColor: ['#4F46E5','#06B6D4','#F59E0B','#EF4444'] }] },
            options: { responsive: true, maintainAspectRatio: false, animation: { duration: 250 }, cutout: '65%' },
        });
    }

    function renderCharts(tab, chartData) {
        destroyChartsForTab(tab);

        switch (tab) {
            case 'users':
                makeBarChart('chartUsersCheckin',  chartData.checkins.labels, chartData.checkins.values, 'Check-ins');
                makeBarChart('chartUsersDuration', chartData.duration.labels,  chartData.duration.values,  'Duration (min)');
                break;
            case 'colleges':
                makeBarChart('chartCollegeCheckin',  chartData.checkins.labels, chartData.checkins.values, 'Unique Users');
                makeBarChart('chartCollegeDuration', chartData.duration.labels,  chartData.duration.values,  'Duration (min)');
                break;
            case 'courses':
                $.each(chartData, function (cleanId, data) {
                    makeBarChart('chartCourseCheckin_'  + cleanId, data.checkins.labels, data.checkins.values, 'Unique Users');
                    makeBarChart('chartCourseDuration_' + cleanId, data.duration.labels,  data.duration.values,  'Duration (min)');
                });
                break;
            case 'demographics':
                makeDoughnutChart('chartSexCheckin', chartData.labels, chartData.values);
                break;
        }
    }


    // ============================================================
    //  VIEW ALL MODAL
    // ============================================================
    let modalTab  = 'users';
    let modalPage = 1;

    function openViewAllModal(tab) {
        modalTab  = tab;
        modalPage = 1;
        fetchViewAll();
        $('#dynamicModal').modal('show');
    }

    function fetchViewAll() {
        $('#dynamicModalTitle').text(modalTitle(modalTab));
        $('#dynamicModalBody').html('<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>');
        $('#dynamicModalFooter').empty();

        $.post('backend/bk_LibraryMenu/bk_libReports - (UPDATED-BACKUP).php', {
            request: 'viewAll',
            tab:     modalTab,
            page:    modalPage,
            ...getFilters(),
        })
        .done(function (res) {
            if (res.status !== 'success') {
                return $('#dynamicModalBody').html('<div class="alert alert-danger">Failed to load data.</div>');
            }

            const { rows, total } = res.data;
            const totalPages      = Math.ceil(total / 10);

            $('#dynamicModalSubtitle').text('Page ' + modalPage + ' of ' + totalPages + ' (' + total + ' records)');
            $('#dynamicModalBody').html(buildModalTable(modalTab, rows));
            renderModalPagination(totalPages);
        });
    }

    function modalTitle(tab) {
        return { users: 'All Users', colleges: 'All Colleges', courses: 'All Courses', demographics: 'All Logs' }[tab] || 'Records';
    }

    function buildModalTable(tab, rows) {
        let head = '', body = '';

        switch (tab) {
            case 'users':
                head = '<tr><th>Name</th><th>Type</th><th>Library</th><th class="text-end">Check-ins</th><th class="text-end">Duration (min)</th><th>Last Check-in</th></tr>';
                rows.forEach(r => { body += `<tr><td>${esc(r.name)}</td><td>${esc(r.type)}</td><td>${esc(r.library)}</td><td class="text-end">${r.checkins}</td><td class="text-end">${r.duration}</td><td>${fmtDate(r.last_checkin)}</td></tr>`; });
                break;
            case 'colleges':
                head = '<tr><th>College</th><th class="text-end">Unique Users</th><th class="text-end">Duration (min)</th><th>Last Check-in</th></tr>';
                rows.forEach(r => { body += `<tr><td>${esc(r.name)}</td><td class="text-end">${r.checkins}</td><td class="text-end">${r.duration}</td><td>${fmtDate(r.last_checkin)}</td></tr>`; });
                break;
            case 'courses':
                head = '<tr><th>College</th><th>Course</th><th class="text-end">Unique Users</th><th class="text-end">Duration (min)</th><th>Last Check-in</th></tr>';
                rows.forEach(r => { body += `<tr><td>${esc(r.college)}</td><td>${esc(r.course)}</td><td class="text-end">${r.checkins}</td><td class="text-end">${r.duration}</td><td>${fmtDate(r.last_checkin)}</td></tr>`; });
                break;
            case 'demographics':
                head = '<tr><th>Name</th><th>Sex</th><th>Check-in</th><th>Check-out</th><th class="text-end">Duration (min)</th></tr>';
                rows.forEach(r => { body += `<tr><td>${esc(r.name)}</td><td>${esc(r.sex)}</td><td>${fmtDate(r.checkin)}</td><td>${r.checkout ? fmtDate(r.checkout) : '—'}</td><td class="text-end">${r.duration}</td></tr>`; });
                break;
        }

        return `<div class="table-responsive"><table class="table table-sm table-striped align-middle"><thead class="table-light">${head}</thead><tbody>${body}</tbody></table></div>`;
    }

    function renderModalPagination(totalPages) {
        if (totalPages <= 1) return;
        let html = '<nav><ul class="pagination pagination-sm mb-0">';
        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="page-item ${i === modalPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
        html += '</ul></nav>';
        $('#dynamicModalFooter').html(html);
    }

    $(document).on('click', '#dynamicModalFooter .page-link', function (e) {
        e.preventDefault();
        modalPage = parseInt($(this).data('page'));
        fetchViewAll();
    });


    // ============================================================
    //  UTILITIES
    // ============================================================
    function getFilters() {
        return {
            startDate:      $('#startDate').val(),
            endDate:        $('#endDate').val(),
            classification: $('#filterClassification').val(),
            library:        $('#filterLibrary').val(),
        };
    }

    function filtersChanged() {
        return JSON.stringify(getFilters()) !== JSON.stringify(lastFilters);
    }

    function setDefaultDates() {
        const today    = new Date();
        const lastWeek = new Date();
        lastWeek.setDate(today.getDate() - 7);
        $('#startDate').val(lastWeek.toISOString().split('T')[0]);
        $('#endDate').val(today.toISOString().split('T')[0]);
    }

    function fmtDate(str) {
        if (!str) return '—';
        return new Date(str).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
    }

    function fmtDateShort(str) {
        return new Date(str).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function esc(str) {
        if (!str) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(str).replace(/[&<>"']/g, c => map[c]);
    }

});
</script>