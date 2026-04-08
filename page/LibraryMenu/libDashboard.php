<!-- =====
     LIBRARY DASHBOARD — FRONTEND
     • Date-range pickers in the Daily Logs header drive ALL data.
     • Force Checkout uses the universal #dynamicModal.
===== -->

<div class="container-fluid px-4 py-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Library Dashboard</h5>
            <small class="text-muted">Overview of library activities and trends</small>
        </div>
        <div class="d-flex align-items-center mb-1" style="gap:9px;">
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;
                         background:#10b981;flex-shrink:0;
                         animation:ldot 2.2s ease-in-out infinite;"></span>
            <span class="fw-bold text-dark" style="font-size:1rem;letter-spacing:-.2px;">Live</span>
        </div>
    </div>

    <!-- KPI CARDS — shells rendered by JS, counts filled by loadKPI() -->
    <div id="kpiContainer" class="row g-3 mb-4"></div>

    <!-- DAILY LOGS TABLE -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap">

                <div>
                    <h6 class="mb-0 font-weight-bold text-dark">Daily Logs</h6>
                    <small class="text-muted">Check-in / check-out records</small>
                </div>

                <div class="d-flex align-items-center flex-nowrap">
                    <div class="d-flex align-items-center mr-3">
                        <div class="input-group input-group-sm mr-2" style="width:150px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-calendar text-muted small"></i>
                                </span>
                            </div>
                            <input type="date" id="trendStartDate" class="form-control"
                                   value="<?= date('Y-m-d') ?>">
                        </div>
                        <span class="text-muted mx-1">—</span>
                        <div class="input-group input-group-sm ml-2" style="width:150px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-calendar-check text-muted small"></i>
                                </span>
                            </div>
                            <input type="date" id="trendEndDate" class="form-control"
                                   value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="border-left mx-3" style="height:28px;"></div>

                    <select id="sectionFilter" class="form-control form-control-sm mr-3" style="width:160px;">
                        <option value="">All Sections</option>
                    </select>

                    <button id="btnForceCheckout"
                            class="btn btn-sm text-white fw-semibold px-3 mr-3"
                            style="background:#d97706;"
                            title="Check out users who never checked out on previous days">
                        <i class="fas fa-clock-rotate-left me-1"></i>&nbsp;&nbsp;Force Checkout
                    </button>

                    <span id="dateBadge"
                          class="badge badge-pill badge-light border text-muted py-2 text-center"
                          style="min-width:210px;">Today</span>
                </div>

            </div>
        </div>

        <div class="card-body p-0" style="min-height:285px;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="px-4 py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">ID No.</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">College</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Course</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Library</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Check-In</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Check-Out</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="dailyLogs">
                        <tr><td colspan="7" class="text-center text-muted py-4">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white border-top py-3 px-4">
            <div class="d-flex flex-column align-items-center gap-1" id="logsPageInfo"></div>
        </div>
    </div>

    <!-- CHARTS ROW -->
    <div class="row g-3 mb-3">

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom px-4 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-bar me-2 text-success"></i>Usage Trend
                    </h6>
                    <small class="text-muted">Monthly visit counts — driven by date range above</small>
                </div>
                <div class="card-body px-4 pb-4 pt-3">
                    <div style="position:relative;height:220px;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom px-4 py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-layer-group me-2 text-primary"></i>College &amp; Course Activity
                            </h6>
                            <small class="text-muted">Visit distribution by college &amp; course</small>
                        </div>
                        <span id="chartSectionBadge"
                              class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 ms-2"
                              style="font-size:.72rem;white-space:nowrap;">All Sections</span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div style="position:relative;" id="activityChartWrap">
                        <canvas id="activityChart"></canvas>
                    </div>
                    <div id="activityEmpty" class="text-center text-muted py-4"
                         style="font-size:.8rem;display:none;">
                        No activity for selected range.
                    </div>
                </div>
            </div>
        </div>

    </div>
</div><!-- /container -->
<?php include 'LibModals.php'; ?>

<script>
$(document).ready(function () {

    const BACKEND = "backend/bk_LibraryMenu/bk_libDashboard.php";
    const TODAY   = new Date().toISOString().split('T')[0];

    const PALETTE = [
        'rgba(59,130,246,0.85)', 'rgba(16,185,129,0.85)', 'rgba(245,158,11,0.85)',
        'rgba(239,68,68,0.85)',  'rgba(139,92,246,0.85)', 'rgba(20,184,166,0.85)',
        'rgba(249,115,22,0.85)', 'rgba(236,72,153,0.85)', 'rgba(34,197,94,0.85)',
        'rgba(99,102,241,0.85)',
    ];

    const TOOLTIP = {
        backgroundColor: 'rgba(15,23,42,0.92)', titleColor: '#f8fafc',
        bodyColor: '#94a3b8', borderColor: 'rgba(148,163,184,0.15)',
        borderWidth: 1, padding: 10, cornerRadius: 6,
    };

    let trendChartInst    = null;
    let activityChartInst = null;


    // ── DATE BADGE ────────────────────────────────────────────────────────────

    function updateDateBadge() {
        const startDate = $('#trendStartDate').val() || TODAY;
        const endDate   = $('#trendEndDate').val()   || TODAY;
        const fmt = iso => new Date(iso + 'T00:00:00').toLocaleDateString('en-PH', {
            month: 'short', day: 'numeric', year: 'numeric',
        });

        if (startDate === TODAY && endDate === TODAY) {
            $('#dateBadge').text('Today');
        } else if (startDate === endDate) {
            $('#dateBadge').text(fmt(startDate));
        } else {
            $('#dateBadge').text(fmt(startDate) + ' – ' + fmt(endDate));
        }
    }


    // ── SECTIONS + KPI ────────────────────────────────────────────────────────
    // Fetches sections once at boot: builds KPI card shells, populates the
    // section dropdown, then calls loadKPI() to fill in the counts.

    function loadSections() {
        $.ajax({
            type: 'POST', url: BACKEND, data: { request: 'sections' }, dataType: 'json',
            success(sections) {
                const colors = [
                    { border: '#10b981', text: 'text-success' },
                    { border: '#3b82f6', text: 'text-primary' },
                    { border: '#f59e0b', text: 'text-warning' },
                    { border: '#ef4444', text: 'text-danger'  },
                ];

                $('#kpiContainer').html(sections.map((sec, i) => {
                    const c = colors[i % colors.length];
                    return `
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid ${c.border};">
                                <div class="card-body p-3">
                                    <span class="text-muted fw-semibold text-uppercase d-block mb-2"
                                          style="letter-spacing:.05em;font-size:.7rem;">${sec.SectionName}</span>
                                    <h3 class="fw-bold ${c.text} mb-0 kpi-count"
                                        data-section-code="${sec.SectionCode.trim()}">—</h3>
                                    <small class="text-muted kpi-label">active visits</small>
                                </div>
                            </div>
                        </div>`;
                }).join(''));

                const $filter = $('#sectionFilter');
                $filter.find('option:not(:first)').remove();
                sections.forEach(s => {
                    const id = String(s.SectionID ?? '').trim();
                    if (id) $filter.append(`<option value="${id}">${s.SectionName}</option>`);
                });

                loadKPI();
            },
        });
    }

    function loadKPI() {
        const startDate = $('#trendStartDate').val() || TODAY;
        const endDate   = $('#trendEndDate').val()   || TODAY;

        $.ajax({
            type: 'POST', url: BACKEND,
            data: { request: 'kpiData', startDate, endDate },
            dataType: 'json',
            success(sections) {
                $('.kpi-count').text('0');
                $('.kpi-label').text(startDate === TODAY && endDate === TODAY ? 'active visits' : 'total visits');
                sections.forEach(s => {
                    $(`.kpi-count[data-section-code="${(s.SectionCode ?? '').trim()}"]`).text(s.total ?? 0);
                });
            },
        });
    }


    // ── DAILY LOGS ────────────────────────────────────────────────────────────

    function loadLogs(page) {
        page = page ?? 1;

        $.ajax({
            type: 'POST', url: BACKEND,
            data: {
                request:   'dailyLogs',
                page,
                sectionID: $('#sectionFilter').val(),
                startDate: $('#trendStartDate').val() || TODAY,
                endDate:   $('#trendEndDate').val()   || TODAY,
            },
            success(raw) {
                const res = JSON.parse(raw);
                $('#dailyLogs').html(res.rows);

                const $wrap = $('#logsPageInfo').empty();
                if (res.totalRows === 0) return;

                const from = (res.currentPage - 1) * res.limit + 1;
                const to   = Math.min(res.currentPage * res.limit, res.totalRows);
                $wrap.append(`<small class="text-muted">Showing ${from}–${to} of ${res.totalRows} records</small>`);

                if (res.totalPages <= 1) return;

                const cur      = res.currentPage;
                const pages    = res.totalPages;
                const winStart = Math.max(1, Math.min(cur - 2, pages - 4));
                const winEnd   = Math.min(pages, winStart + 4);
                const $ul      = $('<ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center"></ul>');

                function addPage(label, p, disabled, active) {
                    const $a = $(`<a class="page-link" href="#">${label}</a>`);
                    if (!disabled) $a.data('page', p);
                    $ul.append($(`<li class="page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}"></li>`).append($a));
                }

                addPage('«', 1,       cur === 1,     false);
                addPage('‹', cur - 1, cur === 1,     false);
                for (let p = winStart; p <= winEnd; p++) addPage(p, p, false, p === cur);
                addPage('›', cur + 1, cur === pages, false);
                addPage('»', pages,   cur === pages, false);

                $ul.on('click', '.page-link', function (e) {
                    e.preventDefault();
                    const p = $(this).data('page');
                    if (p) loadLogs(p);
                });
                $wrap.append($ul);
            },
        });
    }


    // ── USAGE TREND ───────────────────────────────────────────────────────────

    function loadTrend() {
        $.ajax({
            type: 'POST', url: BACKEND,
            data: {
                request:   'monthlyTrend',
                sectionID: $('#sectionFilter').val(),
                startDate: $('#trendStartDate').val() || TODAY,
                endDate:   $('#trendEndDate').val()   || TODAY,
            },
            dataType: 'json',
            success(rows) {
                const canvas = document.getElementById('trendChart');
                if (!canvas) return;

                if (trendChartInst) { trendChartInst.destroy(); trendChartInst = null; }

                const $wrap = $(canvas).parent();
                $wrap.find('.trend-empty').remove();

                if (!rows?.length) {
                    canvas.style.display = 'none';
                    $wrap.append('<div class="trend-empty text-center text-muted py-4" style="font-size:.8rem;">No data for selected range.</div>');
                    return;
                }
                canvas.style.display = '';

                const lastIdx = rows.length - 1;
                trendChartInst = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: rows.map(r => r.month),
                        datasets: [{
                            label:           'Visits',
                            data:            rows.map(r => parseInt(r.total)),
                            backgroundColor: rows.map((_, i) => i === lastIdx ? 'rgba(16,185,129,0.88)' : 'rgba(16,185,129,0.35)'),
                            borderColor:     rows.map((_, i) => i === lastIdx ? 'rgba(16,185,129,1)'    : 'rgba(16,185,129,0.6)'),
                            borderWidth: 1.5, borderRadius: 4, borderSkipped: false,
                        }],
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        animation: { duration: 500, easing: 'easeOutQuart' },
                        plugins: {
                            legend: { display: false },
                            tooltip: { ...TOOLTIP, callbacks: { label: ctx => `  Visits: ${ctx.parsed.y.toLocaleString()}` } },
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: '#6b7280', font: { size: 11 } } },
                            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' },
                                 ticks: { color: '#6b7280', font: { size: 10 }, precision: 0,
                                          callback: v => Number.isInteger(v) ? v : '' } },
                        },
                        layout: { padding: { top: 8 } },
                    },
                });
            },
            error() {
                $('#trendChart').hide();
            },
        });
    }


    // ── COLLEGE & COURSE ACTIVITY ─────────────────────────────────────────────

    function loadCollegeCourseActivity() {
        const sectionID = $('#sectionFilter').val();
        $('#chartSectionBadge').text(sectionID ? $('#sectionFilter option:selected').text() : 'All Sections');
        $('#activityEmpty').hide();

        $.ajax({
            type: 'POST', url: BACKEND,
            data: {
                request:   'collegeCourseActivity',
                sectionID,
                startDate: $('#trendStartDate').val() || TODAY,
                endDate:   $('#trendEndDate').val()   || TODAY,
            },
            dataType: 'json',
            success(colleges) {
                const canvas = document.getElementById('activityChart');
                if (!canvas) return;

                if (activityChartInst) { activityChartInst.destroy(); activityChartInst = null; }

                if (!colleges?.length) {
                    canvas.style.display = 'none';
                    $('#activityEmpty').show();
                    return;
                }
                canvas.style.display = '';
                $('#activityEmpty').hide();

                const seen    = new Set();
                const courses = [];
                colleges.forEach(col => col.courses.forEach(cr => {
                    if (!seen.has(cr.course)) { seen.add(cr.course); courses.push(cr.course); }
                }));
                const courseColor = Object.fromEntries(courses.map((c, i) => [c, PALETTE[i % PALETTE.length]]));

                canvas.parentElement.style.height = Math.max(200, colleges.length * 42 + 60) + 'px';

                activityChartInst = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: colleges.map(c => c.college || '—'),
                        datasets: courses.map(course => ({
                            label:           course,
                            data:            colleges.map(col => col.courses.find(cr => cr.course === course)?.total ?? 0),
                            backgroundColor: courseColor[course],
                            borderColor:     courseColor[course].replace('0.85', '1'),
                            borderWidth: 0, borderRadius: 3, borderSkipped: false,
                        })),
                    },
                    options: {
                        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                        animation: { duration: 500, easing: 'easeOutQuart' },
                        plugins: {
                            legend: { position: 'bottom', labels: { color: '#374151', font: { size: 11 }, padding: 12,
                                      usePointStyle: true, pointStyle: 'circle', boxWidth: 8, boxHeight: 8 } },
                            tooltip: { ...TOOLTIP, mode: 'index', intersect: false,
                                callbacks: {
                                    title:  items => items[0]?.label ?? '',
                                    label:  ctx   => ctx.parsed.x > 0 ? `  ${ctx.dataset.label}: ${ctx.parsed.x}` : null,
                                    footer: items => `  Total: ${items.reduce((s, i) => s + (i.parsed.x || 0), 0)}`,
                                },
                            },
                        },
                        scales: {
                            x: { stacked: true, beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' },
                                 ticks: { color: '#6b7280', font: { size: 10 }, precision: 0, callback: v => Number.isInteger(v) ? v : '' },
                                 title: { display: true, text: 'No. of Visits', color: '#9ca3af', font: { size: 10 }, padding: { top: 6 } } },
                            y: { stacked: true, grid: { display: false }, ticks: { color: '#374151', font: { size: 12 } } },
                        },
                        layout: { padding: { right: 8 } },
                    },
                });
            },
            error() {
                $('#activityChart').hide();
                $('#activityEmpty').show();
            },
        });
    }


    // ── FORCE CHECKOUT ────────────────────────────────────────────────────────
    // Step 1 — fetch pending count → show in #dynamicModal.
    // Step 2 — user confirms → run update → reload all widgets.
    // Confirm is disabled immediately on click to block double-submit.
    // Namespaced events (.fc) on $foot are cleaned up when the modal closes.

    $('#btnForceCheckout').on('click', function () {
        const $modal = $('#dynamicModal');
        const $body  = $('#dynamicModalBody');
        const $foot  = $('#dynamicModalFooter');

        $('#dynamicModalTitle').text('Force Checkout — Previous Days');
        $body.html(`
            <div class="text-center py-3 text-muted" style="font-size:.82rem;">
                <span class="spinner-border spinner-border-sm me-2"></span>Checking pending records…
            </div>`);
        $foot.html(`
            <button type="button" class="btn btn-sm btn-light border btn-fc-cancel">Cancel</button>`);
        $modal.modal('show');

        // Clean up delegated handlers every time the modal closes,
        // preventing accumulation across multiple opens.
        $foot.on('click.fc', '.btn-fc-cancel', () => $modal.modal('hide'));
        $modal.one('hidden.bs.modal', () => $foot.off('click.fc'));

        $.ajax({
            type: 'POST', url: BACKEND,
            data: { request: 'countPendingCheckout' },
            dataType: 'json',
            success(res) {
                const count = parseInt(res.count) || 0;

                if (count === 0) {
                    $body.html(`
                        <div class="text-center py-3" style="font-size:.82rem;">
                            <i class="fas fa-circle-check text-success me-2"></i>
                            No unchecked records from previous days.
                        </div>`);
                    return;
                }

                $body.html(`
                    <div class="alert alert-warning border-0 rounded-3 mb-3"
                         style="font-size:.82rem;background:rgba(245,158,11,.1);">
                        <i class="fas fa-triangle-exclamation me-2 text-warning"></i>
                        Found <strong>${count}</strong> record${count !== 1 ? 's' : ''} with no check-out
                        from <strong>previous days</strong>. Each will be checked out at
                        <strong>7:00 PM</strong> of its own check-in date.
                    </div>
                    <p class="text-muted mb-0" style="font-size:.78rem;">
                        Records from <strong>today</strong> are never affected.
                    </p>`);
                $foot.html(`
                    <button type="button" class="btn btn-sm btn-light border btn-fc-cancel">Cancel</button>
                    <button type="button" class="btn btn-sm text-white fw-semibold px-4 btn-fc-confirm"
                            style="background:#d97706;">
                        <i class="fas fa-check me-1"></i>Confirm
                    </button>`);

                $foot.one('click.fc', '.btn-fc-confirm', function () {
                    $(this).prop('disabled', true)
                           .html('<span class="spinner-border spinner-border-sm me-1"></span>Processing…');

                    $.ajax({
                        type: 'POST', url: BACKEND,
                        data: { request: 'forceCheckout' },
                        dataType: 'json',
                        // AFTER — reuse the modal, no toast needed:
                        success(res) {
                            reloadAll();
                            $body.html(`
                                <div class="text-center py-3">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle mb-3"
                                        style="width:48px;height:48px;">
                                        <i class="fas fa-circle-check text-success" style="font-size:1.3rem;"></i>
                                    </div>
                                    <p class="fw-semibold text-dark mb-1">Force Checkout Complete</p>
                                    <p class="text-muted mb-0" style="font-size:.82rem;">
                                        <strong>${res.affected}</strong> record${res.affected !== 1 ? 's' : ''} updated to 7:00 PM check-out.
                                    </p>
                                </div>`);
                            $foot.html(`
                                <button type="button" class="btn btn-sm btn-primary px-4"
                                        data-dismiss="modal" data-bs-dismiss="modal">Done</button>`);
                        },
                        error() {
                            $body.html(`
                                <div class="text-center py-3">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle mb-3"
                                        style="width:48px;height:48px;">
                                        <i class="fas fa-triangle-exclamation text-danger" style="font-size:1.3rem;"></i>
                                    </div>
                                    <p class="fw-semibold text-dark mb-1">Something went wrong</p>
                                    <p class="text-muted mb-0" style="font-size:.82rem;">Please try again.</p>
                                </div>`);
                            $foot.html(`
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        data-dismiss="modal" data-bs-dismiss="modal">Close</button>`);
                        },
                    });
                });
            },
            error() {
                $body.html(`
                    <div class="text-center text-muted py-2" style="font-size:.82rem;">
                        Could not fetch pending records. Please try again.
                    </div>`);
            },
        });
    });


    // ── RELOAD ALL ────────────────────────────────────────────────────────────

    function reloadAll() {
        loadKPI();
        loadLogs(1);
        loadTrend();
        loadCollegeCourseActivity();
    }


    // ── EVENTS ────────────────────────────────────────────────────────────────

    $('#sectionFilter').on('change', function () {
        loadLogs(1);
        loadTrend();
        loadCollegeCourseActivity();
    });

    $('#trendStartDate, #trendEndDate').on('change', function () {
        const startDate = $('#trendStartDate').val();
        const endDate   = $('#trendEndDate').val();
        if (startDate && endDate && startDate <= endDate) {
            updateDateBadge();
            reloadAll();
        }
    });


    // ── TOAST ─────────────────────────────────────────────────────────────────

    function showToast(msg, type) {
        const $t = $(`
            <div class="toast align-items-center text-white border-0 show" role="alert"
                 style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;
                        background:${type === 'success' ? '#059669' : '#dc2626'};">
                <div class="d-flex">
                    <div class="toast-body" style="font-size:.82rem;">${msg}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast"></button>
                </div>
            </div>`);
        $('body').append($t);
        setTimeout(() => $t.remove(), 4000);
    }


    // ── BOOT ──────────────────────────────────────────────────────────────────

    updateDateBadge();
    loadSections(); // → calls loadKPI() internally after building KPI shells
    loadLogs();
    loadTrend();
    loadCollegeCourseActivity();

});
</script>