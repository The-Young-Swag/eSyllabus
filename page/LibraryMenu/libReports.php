<?php
/**
 * Library Analytics Dashboard - Frontend View
 * Renders the filter panel, KPI cards, tab navigation, and bootstraps the JS.
 */

include "../../db/dbconnection.php";

$librarySections = execsqlSRS(
    "SELECT SectionID, SectionName FROM LibrarySection WHERE IsActive = 1 ORDER BY SectionName",
    'Select',
    []
);
?>

<div class="container-fluid py-4">

    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1">Library Analytics Dashboard</h5>
            <p class="text-muted small mb-0">Visitor trends, usage patterns, and demographic insights</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-outline-secondary btn-sm" id="refreshBtn">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
            <button class="btn btn-danger btn-sm" id="exportPDF">
                <i class="fas fa-file-pdf me-1"></i> Export Report
            </button>
        </div>
    </div>


    <!-- =========================================================
         FILTER PANEL
    ========================================================== -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">

                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Start Date</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fas fa-calendar text-muted"></i></span>
                        <input type="date" class="form-control" id="startDate">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">End Date</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fas fa-calendar-check text-muted"></i></span>
                        <input type="date" class="form-control" id="endDate">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">User Classification</label>
                    <select class="form-select form-select-sm" id="classificationFilter">
                        <option value="All" selected>All Classifications</option>
                        <option value="Student">Student</option>
                        <option value="Employee">Employee</option>
                        <option value="Guest">Guest</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Library Section</label>
                    <select class="form-select form-select-sm" id="libraryFilter">
                        <option value="All" selected>All Sections</option>
                        <?php foreach ($librarySections as $section): ?>
                            <option value="<?= $section['SectionID'] ?>">
                                <?= htmlspecialchars($section['SectionName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div class="row mt-3">
                <div class="col-12 col-md-3 offset-md-9">
                    <button class="btn btn-success btn-sm w-100 fw-semibold" id="generateBtn">
                        <i class="fas fa-chart-bar me-1"></i> Generate Analytics
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- =========================================================
         KPI CARDS
    ========================================================== -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #10b981 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Total Check-ins</p>
                            <h3 class="fw-bold mb-0" id="kpiTotalCheckins">—</h3>
                        </div>
                        <div class="rounded-3 bg-success-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-box-arrow-in-right text-success fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #3b82f6 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Avg. Session Duration</p>
                            <h3 class="fw-bold mb-0" id="kpiAvgDuration">—</h3>
                        </div>
                        <div class="rounded-3 bg-primary-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-clock-history text-primary fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #6b7280 !important;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1" id="kpiEndDateLabel">Check-ins on —</p>
                            <h3 class="fw-bold mb-0" id="kpiEndDateCheckins">—</h3>
                        </div>
                        <div class="rounded-3 bg-secondary-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-calendar-day text-secondary fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <!-- =========================================================
         TAB NAVIGATION
    ========================================================== -->
    <div class="mb-4">
        <ul class="nav nav-tabs" id="analyticsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active d-flex align-items-center gap-2" data-tab="users" role="tab">
                    <i class="bi bi-people-fill"></i> Users
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center gap-2" data-tab="colleges" role="tab">
                    <i class="bi bi-building-fill"></i> Colleges
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center gap-2" data-tab="courses" role="tab">
                    <i class="bi bi-journal-bookmark-fill"></i> Courses
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center gap-2" data-tab="demographics" role="tab">
                    <i class="bi bi-bar-chart-fill"></i> Demographics
                </button>
            </li>
        </ul>
    </div>


    <!-- =========================================================
         TAB CONTENT AREA (AJAX-injected)
    ========================================================== -->
    <div id="tabContent" class="tab-content">
        <div class="text-center p-5 text-muted">
            <i class="bi bi-bar-chart-line fs-1 d-block mb-3 opacity-25"></i>
            Select a date range and click <strong>Generate Analytics</strong> to view data.
        </div>
    </div>


    <!-- =========================================================
         FOOTER
    ========================================================== -->
    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
        <small class="text-muted" id="lastUpdatedLabel">
            <i class="fas fa-sync-alt me-1"></i> Last updated: —
        </small>
        <small class="text-muted">
            <i class="fas fa-database me-1"></i> Source: Library System
        </small>
    </div>

</div>


<?php include '../modalContainer.php'; ?>
<?php include 'LibModals.php'; ?>


<script>
$(function () {

    // =========================================================
    //  CONFIGURATION
    // =========================================================

    const BACKEND_URL       = 'backend/bk_LibraryMenu/bk_libReports.php';
    const ROWS_PER_PAGE     = 10;
    const DEFAULT_TAB       = 'users';
    const DEFAULT_DAYS_BACK = 7;

    const TAB_MODAL_TITLES = {
        users:        'All Users',
        colleges:     'All Colleges',
        courses:      'All Courses',
        demographics: 'All Check-in Logs',
    };

    // =========================================================
    //  COLLEGE COLOR MAP
    //  Mirrors the PHP COLLEGE_COLOR_MAP for consistent chart colors.
    //  To update a college color: change both here AND in the PHP constant.
    // =========================================================
    const COLLEGE_COLOR_MAP = {
        CAF: 'rgba(22,163,74,0.88)',   // green
        CAS: 'rgba(234,88,12,0.88)',   // orange
        CBM: 'rgba(202,138,4,0.88)',   // yellow
        CET: 'rgba(220,38,38,0.88)',   // red
        CED: 'rgba(37,99,235,0.88)',   // blue
        CVM: 'rgba(107,114,128,0.88)', // grey
    };
    const COLLEGE_COLOR_FALLBACK = 'rgba(139,92,246,0.88)'; // violet

    /**
     * Resolves a college name string to its designated color.
     * Checks if any known abbreviation key appears in the college string.
     */
    function resolveCollegeColor(collegeName) {
        const upperName = (collegeName || '').toUpperCase();
        for (const [abbr, color] of Object.entries(COLLEGE_COLOR_MAP)) {
            if (upperName.includes(abbr)) return color;
        }
        return COLLEGE_COLOR_FALLBACK;
    }

    // Visitor type donut color palette (Student / Employee / Guest / fallback)
    const VISITOR_TYPE_DONUT_COLORS = [
        'rgba(59,130,246,0.88)',   // Student  — blue
        'rgba(16,185,129,0.88)',   // Employee — green
        'rgba(245,158,11,0.88)',   // Guest    — amber
        'rgba(100,116,139,0.88)', // fallback — slate
    ];

    // Sex distribution donut color palette (Male / Female / Unknown)
    const SEX_DONUT_COLORS = [
        'rgba(59,130,246,0.88)',   // Male    — blue
        'rgba(239,68,68,0.88)',    // Female  — rose
        'rgba(100,116,139,0.88)', // Unknown — slate
    ];

    // Course donut fallback color palette (when no college-based color applies)
    const COURSE_DONUT_COLORS = [
        'rgba(59,130,246,0.82)',
        'rgba(16,185,129,0.82)',
        'rgba(245,158,11,0.82)',
        'rgba(139,92,246,0.82)',
        'rgba(239,68,68,0.82)',
        'rgba(20,184,166,0.82)',
        'rgba(100,116,139,0.82)',
    ];


    // =========================================================
    //  DOM REFERENCES
    // =========================================================

    const filters = {
        startDate:      $('#startDate'),
        endDate:        $('#endDate'),
        classification: $('#classificationFilter'),
        library:        $('#libraryFilter'),
    };

    const kpi = {
        totalCheckins:   $('#kpiTotalCheckins'),
        avgDuration:     $('#kpiAvgDuration'),
        endDateLabel:    $('#kpiEndDateLabel'),
        endDateCheckins: $('#kpiEndDateCheckins'),
    };

    const tabContentArea   = $('#tabContent');
    const tabNavButtons    = $('#analyticsTabs .nav-link');
    const lastUpdatedLabel = $('#lastUpdatedLabel');


    // =========================================================
    //  APPLICATION STATE
    // =========================================================

    let activeTab      = DEFAULT_TAB;
    let pendingRequest = null;
    let viewAllTab     = DEFAULT_TAB;
    let viewAllPage    = 1;


    // =========================================================
    //  FILTER HELPERS
    // =========================================================

    function getActiveFilters() {
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
        const today        = new Date();
        const defaultStart = new Date(today);
        defaultStart.setDate(today.getDate() - DEFAULT_DAYS_BACK);
        filters.startDate.val(defaultStart.toISOString().split('T')[0]);
        filters.endDate.val(today.toISOString().split('T')[0]);
    }


    // =========================================================
    //  CHART MANAGER
    //  Centralised Chart.js lifecycle management.
    //  All charts use consistent professional base options.
    // =========================================================

    const ChartManager = {
        instances: {},

        destroy(canvasId) {
            if (this.instances[canvasId]) {
                this.instances[canvasId].destroy();
                delete this.instances[canvasId];
            }
        },

        create(canvasId, config) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            this.destroy(canvasId);
            this.instances[canvasId] = new Chart(canvas, config);
        },

        /** Shared base options for all donut/doughnut charts */
        _donutBaseOptions(centerLabelText) {
            return {
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
                            padding:       14,
                            usePointStyle: true,
                            pointStyle:    'circle',
                            // Format legend: "Label (count)"
                            generateLabels: (chart) => {
                                const data = chart.data;
                                return data.labels.map((label, i) => ({
                                    text:        `${label} (${(data.datasets[0].data[i] || 0).toLocaleString()})`,
                                    fillStyle:   data.datasets[0].backgroundColor[i],
                                    strokeStyle: data.datasets[0].backgroundColor[i],
                                    hidden:      false,
                                    index:       i,
                                    pointStyle:  'circle',
                                }));
                            },
                        },
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15,23,42,0.92)',
                        titleColor:      '#f8fafc',
                        bodyColor:       '#94a3b8',
                        borderColor:     'rgba(148,163,184,0.15)',
                        borderWidth:     1,
                        padding:         10,
                        cornerRadius:    6,
                        callbacks: {
                            label: (ctx) => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct   = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${ctx.parsed.toLocaleString()} (${pct}%)`;
                            },
                        },
                    },
                },
            };
        },

        /**
         * Renders a professional donut chart with a center label showing
         * the total count and a custom inner label text.
         *
         * @param {string}   canvasId       - Canvas element ID
         * @param {string[]} sliceLabels    - Label for each slice
         * @param {number[]} sliceValues    - Value for each slice
         * @param {string[]} sliceColors    - RGBA color strings per slice
         * @param {string}   centerLabel    - Text shown inside the donut hole
         */
        renderDonut(canvasId, sliceLabels, sliceValues, sliceColors, centerLabel = 'Total') {
            const totalCount = sliceValues.reduce((sum, v) => sum + v, 0);

            // Custom center-text plugin scoped to this instance
            const centerTextPlugin = {
                id: 'centerText_' + canvasId,
                afterDraw(chart) {
                    const { ctx, chartArea } = chart;
                    if (!chartArea) return;

                    const centerX = (chartArea.left + chartArea.right)  / 2;
                    const centerY = (chartArea.top  + chartArea.bottom) / 2;

                    ctx.save();

                    // Total count — large bold
                    ctx.font         = 'bold 22px sans-serif';
                    ctx.fillStyle    = '#111827';
                    ctx.textAlign    = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(totalCount.toLocaleString(), centerX, centerY - 9);

                    // Center label text — small muted
                    ctx.font      = '11px sans-serif';
                    ctx.fillStyle = '#6b7280';
                    ctx.fillText(centerLabel, centerX, centerY + 13);

                    ctx.restore();
                },
            };

            this.create(canvasId, {
                type:    'doughnut',
                data: {
                    labels:   sliceLabels,
                    datasets: [{
                        data:            sliceValues,
                        backgroundColor: sliceColors,
                        borderWidth:     2,
                        borderColor:     '#ffffff',
                        hoverOffset:     6,
                    }],
                },
                options: this._donutBaseOptions(centerLabel),
                plugins: [centerTextPlugin],
            });
        },
    };


    // =========================================================
    //  CHART INITIALIZERS
    // =========================================================

    /**
     * Initialises all charts for the Users tab.
     * - Visitor type donut: Student / Employee / Guest breakdown.
     */
    function initUsersCharts(backendResponse) {
        const visitorTypeLabels = Object.keys(backendResponse.classificationDistribution);
        const visitorTypeValues = Object.values(backendResponse.classificationDistribution);

        ChartManager.renderDonut(
            'chartVisitorTypeDonut',
            visitorTypeLabels,
            visitorTypeValues,
            VISITOR_TYPE_DONUT_COLORS,
            'Visitors'
        );
    }

    /**
     * Initialises all charts for the Colleges tab.
     * Uses college-specific colors resolved from COLLEGE_COLOR_MAP.
     */
    function initCollegesCharts(backendResponse) {
        const checkinCollegeNames  = Object.keys(backendResponse.top3CollegesCheckin);
        const checkinVisitorCounts = checkinCollegeNames.map(name => backendResponse.top3CollegesCheckin[name].count);
        const checkinColors        = checkinCollegeNames.map(name => resolveCollegeColor(name));

        ChartManager.renderDonut(
            'chartCollegeCheckin',
            checkinCollegeNames,
            checkinVisitorCounts,
            checkinColors,
            'Visitors'
        );

        const durationCollegeNames  = Object.keys(backendResponse.top3CollegesDuration);
        const durationTotalMinutes  = durationCollegeNames.map(name => Math.round(backendResponse.top3CollegesDuration[name].minutes));
        const durationColors        = durationCollegeNames.map(name => resolveCollegeColor(name));

        ChartManager.renderDonut(
            'chartCollegeDuration',
            durationCollegeNames,
            durationTotalMinutes,
            durationColors,
            'Minutes'
        );
    }

    /**
     * Initialises all course donut charts, one pair per college.
     * Course slices inherit the parent college's color with slight opacity variation.
     */
    function initCoursesCharts(backendResponse) {
        Object.keys(backendResponse.topCoursesCheckin).forEach(collegeName => {
            const safeCollegeId        = collegeName.replace(/[^a-zA-Z0-9]/g, '');
            const topCoursesByCheckins = backendResponse.topCoursesCheckin[collegeName];
            const topCoursesByDuration = backendResponse.topCoursesDuration[collegeName];

            // Course slices use generic palette since course abbreviations vary per college
            const checkinCourseNames   = Object.keys(topCoursesByCheckins);
            const checkinCourseValues  = checkinCourseNames.map(c => topCoursesByCheckins[c].count);
            const checkinCourseColors  = checkinCourseNames.map((_, i) => COURSE_DONUT_COLORS[i % COURSE_DONUT_COLORS.length]);

            ChartManager.renderDonut(
                'chartCourseCheckin_' + safeCollegeId,
                checkinCourseNames,
                checkinCourseValues,
                checkinCourseColors,
                'Visitors'
            );

            if (topCoursesByDuration) {
                const durationCourseNames  = Object.keys(topCoursesByDuration);
                const durationCourseValues = durationCourseNames.map(c => Math.round(topCoursesByDuration[c].minutes));
                const durationCourseColors = durationCourseNames.map((_, i) => COURSE_DONUT_COLORS[i % COURSE_DONUT_COLORS.length]);

                ChartManager.renderDonut(
                    'chartCourseDuration_' + safeCollegeId,
                    durationCourseNames,
                    durationCourseValues,
                    durationCourseColors,
                    'Minutes'
                );
            }
        });
    }

    /**
     * Initialises all charts for the Demographics tab.
     * - Sex distribution donut: Male / Female / Unknown.
     */
    function initDemographicsCharts(backendResponse) {
        const sexLabels = Object.keys(backendResponse.sexDistribution);
        const sexValues = Object.values(backendResponse.sexDistribution);

        ChartManager.renderDonut(
            'chartSexDonut',
            sexLabels,
            sexValues,
            SEX_DONUT_COLORS,
            'Visitors'
        );
    }

    function initializeCharts(backendResponse, tab) {
        switch (tab) {
            case 'users':        initUsersCharts(backendResponse);        break;
            case 'colleges':     initCollegesCharts(backendResponse);     break;
            case 'courses':      initCoursesCharts(backendResponse);      break;
            case 'demographics': initDemographicsCharts(backendResponse); break;
        }
    }


    // =========================================================
    //  KPI CARD UPDATERS
    // =========================================================

    function updateKpiCards(backendResponse) {
        kpi.totalCheckins.text(backendResponse.totalVisits.toLocaleString());
        kpi.avgDuration.text(backendResponse.avgDuration + ' min');

        const selectedEndDate  = filters.endDate.val();
        const formattedEndDate = selectedEndDate
            ? new Date(selectedEndDate).toLocaleDateString('en-US', {
                month: 'short', day: 'numeric', year: 'numeric'
              })
            : '—';

        kpi.endDateLabel.text('Check-ins on ' + formattedEndDate);
        kpi.endDateCheckins.text(backendResponse.endDateCheckins.toLocaleString());
    }

    function updateLastUpdatedTimestamp() {
        const currentTime = new Date().toLocaleTimeString('en-US', {
            hour: 'numeric', minute: 'numeric', hour12: true,
        });
        lastUpdatedLabel.html('<i class="fas fa-sync-alt me-1"></i> Last updated: Today at ' + currentTime);
    }


    // =========================================================
    //  TAB LOADER
    // =========================================================

    function showLoadingSpinner() {
        tabContentArea.html(`
            <div class="d-flex justify-content-center align-items-center p-5">
                <div class="spinner-border text-secondary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
    }

    function setActiveTabStyle(clickedTabButton) {
        tabNavButtons.each(function () {
            $(this).removeClass('active');
        });
        $(clickedTabButton).addClass('active');
    }

    function loadTab(tab) {
        activeTab = tab;

        if (pendingRequest) pendingRequest.abort();
        showLoadingSpinner();

        pendingRequest = $.ajax({
            url:      BACKEND_URL,
            type:     'POST',
            dataType: 'json',
            data: {
                action: 'tab',
                tab,
                ...getActiveFilters(),
            },
        })
        .done(function (backendResponse) {
            if (backendResponse.status !== 'success') {
                tabContentArea.html(`<div class="alert alert-danger m-3">${backendResponse.message}</div>`);
                return;
            }

            tabContentArea.html(backendResponse.html);
            initializeCharts(backendResponse, tab);
            updateKpiCards(backendResponse);
            updateLastUpdatedTimestamp();
        })
        .fail(function (xhr, status) {
            if (status === 'abort') return; // Silently ignore aborted requests
            tabContentArea.html(`<div class="alert alert-danger m-3">Failed to load analytics. Please try again.</div>`);
        });
    }


    // =========================================================
    //  VIEW ALL MODAL
    // =========================================================

    function loadViewAll(tab, page) {
        $('#dynamicModalBody').html(
            '<div class="text-center p-4"><div class="spinner-border text-secondary"></div></div>'
        );
        $('#dynamicModalFooter').empty();

        $.ajax({
            url:      BACKEND_URL,
            type:     'POST',
            dataType: 'json',
            data: {
                action: 'viewAll',
                tab,
                page,
                ...getActiveFilters(),
            },
        })
        .done(function (backendResponse) {
            if (backendResponse.status !== 'success') {
                $('#dynamicModalBody').html('<div class="alert alert-danger">Failed to load data.</div>');
                return;
            }

            const { page: currentPage, totalPages, total, tableHtml, pagination } = backendResponse;

            $('#dynamicModalTitle').text(TAB_MODAL_TITLES[tab] ?? 'All Records');
            $('#dynamicModalSubtitle').text(
                'Page ' + currentPage + ' of ' + totalPages + '  ·  ' + total + ' total records'
            );
            $('#dynamicModalBody').html(tableHtml);
            $('#dynamicModalFooter').html(pagination);

            $('#dynamicModalFooter .page-link').on('click', function (e) {
                e.preventDefault();
                viewAllPage = parseInt($(this).data('page'));
                loadViewAll(tab, viewAllPage);
            });
        });
    }


    // =========================================================
    //  PDF EXPORT
    // =========================================================

    function exportDashboardToPdf() {
        const { jsPDF }         = window.jspdf;
        const pdf               = new jsPDF('l', 'mm', 'a4');
        const tabContentElement = document.getElementById('tabContent');

        const A4_LANDSCAPE_WIDTH_MM  = 280;
        const A4_LANDSCAPE_HEIGHT_MM = 210;
        const MARGIN_MM              = 5;

        html2canvas(tabContentElement, { scale: 2, logging: false, useCORS: true })
            .then(canvas => {
                const imageData      = canvas.toDataURL('image/png');
                const printableWidth = A4_LANDSCAPE_WIDTH_MM - MARGIN_MM * 2;
                const scaledHeight   = (canvas.height * printableWidth) / canvas.width;

                let remainingHeight = scaledHeight;
                let verticalOffset  = 0;

                pdf.addImage(imageData, 'PNG', MARGIN_MM, MARGIN_MM, printableWidth, scaledHeight, undefined, 'FAST');
                remainingHeight -= A4_LANDSCAPE_HEIGHT_MM;

                while (remainingHeight >= 0) {
                    verticalOffset = remainingHeight - scaledHeight;
                    pdf.addPage();
                    pdf.addImage(imageData, 'PNG', MARGIN_MM, verticalOffset + MARGIN_MM, printableWidth, scaledHeight, undefined, 'FAST');
                    remainingHeight -= A4_LANDSCAPE_HEIGHT_MM;
                }

                pdf.save('Library_Analytics_Report.pdf');
            });
    }


    // =========================================================
    //  EVENT BINDINGS
    // =========================================================

    tabNavButtons.on('click', function (e) {
        e.preventDefault();
        setActiveTabStyle(this);
        loadTab($(this).data('tab'));
    });

    $('#generateBtn').on('click', function () {
        if (!hasDateRange()) {
            alert('Please select both a start and end date.');
            return;
        }
        loadTab(activeTab);
    });

    $('#refreshBtn').on('click', function () {
        if (hasDateRange()) loadTab(activeTab);
    });

    filters.startDate
        .add(filters.endDate)
        .add(filters.classification)
        .add(filters.library)
        .on('change', function () {
            if (hasDateRange()) loadTab(activeTab);
        });

    $(document).on('click', '.view-all-btn', function () {
        viewAllTab  = $(this).data('tab');
        viewAllPage = 1;
        loadViewAll(viewAllTab, viewAllPage);
        $('#dynamicModal').modal('show');
    });

    $('#exportPDF').on('click', exportDashboardToPdf);


    // =========================================================
    //  INITIALIZATION
    // =========================================================

    setDefaultDateRange();
    loadTab(DEFAULT_TAB);
});
</script>