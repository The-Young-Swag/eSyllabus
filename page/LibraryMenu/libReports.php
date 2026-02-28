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
            <div class="btn-group">
                <button class="btn btn-danger btn-sm" id="exportPDF">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </button>
                <button class="btn btn-success btn-sm" id="exportXLSX">
                    <i class="fas fa-file-excel me-1"></i> Export XLSX
                </button>
            </div>
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


<!-- =========================================================
     VIEW ALL MODAL
     Fixed: proper Bootstrap structure, subtitle support, clean footer pagination
========================================================== -->
<div class="modal fade" id="viewAllModal" tabindex="-1" aria-labelledby="viewAllModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-semibold mb-0" id="viewAllModalLabel">Records</h5>
                    <small class="text-muted" id="viewAllModalSubtitle"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0" id="viewAllModalBody">
                <!-- Table injected here via AJAX -->
            </div>

            <div class="modal-footer d-flex justify-content-between align-items-center">
                <small class="text-muted" id="viewAllModalCount"></small>
                <div id="viewAllModalPagination"></div>
            </div>

        </div>
    </div>
</div>


<?php include '../modalContainer.php'; ?>
<?php include 'LibModals.php'; ?>


<script>
$(function () {

    // =========================================================
    //  DASHBOARD SETTINGS
    //  Single source of truth for all constants.
    //  Rename a tab, swap a color, or change the backend URL here
    //  without touching any other part of the code.
    // =========================================================

    const Analytics = {

        backendUrl:  'backend/bk_LibraryMenu/bk_libReports.php',
        defaultTab:  'users',
        defaultDays: 7,

        // Human-readable labels for each tab key
        // — used in modal titles, export filenames, and PDF headers
        tabLabels: {
            users:        'All Users',
            colleges:     'All Colleges',
            courses:      'All Courses',
            demographics: 'All Check-in Logs',
        },

        // Bar chart colors per rank position (Rank 1 / 2 / 3)
        // checkins: blue-indigo range  |  duration: green-teal range
        rankColors: {
            checkins: ['rgba(59,130,246,0.88)', 'rgba(99,102,241,0.88)', 'rgba(139,92,246,0.88)'],
            duration: ['rgba(16,185,129,0.88)', 'rgba(20,184,166,0.88)', 'rgba(8,145,178,0.88)'],
        },

        // Donut palette: visitor type (Student / Employee / Guest / fallback)
        donutColorsVisitorType: [
            'rgba(59,130,246,0.88)',
            'rgba(16,185,129,0.88)',
            'rgba(245,158,11,0.88)',
            'rgba(100,116,139,0.88)',
        ],

        // Donut palette: sex distribution (Male / Female / Unknown)
        donutColorsSex: [
            'rgba(59,130,246,0.88)',
            'rgba(239,68,68,0.88)',
            'rgba(100,116,139,0.88)',
        ],

        // Donut palette: courses (cycles)
        donutColorsCourse: [
            'rgba(59,130,246,0.82)',  'rgba(16,185,129,0.82)',
            'rgba(245,158,11,0.82)',  'rgba(139,92,246,0.82)',
            'rgba(239,68,68,0.82)',   'rgba(20,184,166,0.82)',
            'rgba(100,116,139,0.82)',
        ],

        // College abbreviation → donut color
        // Keep in sync with PHP COLLEGE_COLOR_MAP constant
        collegeColorMap: {
            CAF: 'rgba(22,163,74,0.88)',
            CAS: 'rgba(234,88,12,0.88)',
            CBM: 'rgba(202,138,4,0.88)',
            CET: 'rgba(220,38,38,0.88)',
            CED: 'rgba(37,99,235,0.88)',
            CVM: 'rgba(107,114,128,0.88)',
        },
        collegeColorFallback: 'rgba(139,92,246,0.88)',

        // CDN scripts loaded on demand for PDF/XLSX export
        // Update version numbers here when upgrading libraries
        exportLibraries: {
            jspdf:     'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
            autotable: 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js',
            xlsx:      'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js',
        },
    };


    // =========================================================
    //  DOM REFERENCES
    //  All jQuery selectors in one place — update selectors here
    //  if IDs ever change without touching logic elsewhere.
    // =========================================================

    const $tabContentArea  = $('#tabContent');
    const $tabNavButtons   = $('#analyticsTabs .nav-link');
    const $lastUpdatedLabel = $('#lastUpdatedLabel');
    const $loadingSpinner  = $('#loadingSpinner');   // owned by parent layout

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


    // =========================================================
    //  APPLICATION STATE
    // =========================================================

    let activeTab          = Analytics.defaultTab;
    let pendingTabRequest  = null;
    let viewAllActiveTab   = Analytics.defaultTab;
    let viewAllCurrentPage = 1;
    let lastResponse = null;  // cached for export use


    // =========================================================
    //  SPINNER
    //  Uses the #loadingSpinner element defined in the parent layout.
    // =========================================================

    function showSpinner() { $loadingSpinner.stop(true).css('display', 'flex').hide().fadeIn(150); }
    function hideSpinner() { $loadingSpinner.fadeOut(200); }


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

    function hasValidDateRange() {
        return filters.startDate.val() && filters.endDate.val();
    }

    function setDefaultDateRange() {
        if (filters.startDate.val()) return;
        const today         = new Date();
        const defaultStart  = new Date(today);
        defaultStart.setDate(today.getDate() - Analytics.defaultDays);
        filters.startDate.val(defaultStart.toISOString().split('T')[0]);
        filters.endDate.val(today.toISOString().split('T')[0]);
    }

    function buildDateRangeLabel() {
        const startVal = filters.startDate.val() || '—';
        const endVal   = filters.endDate.val()   || '—';
        return `${startVal} to ${endVal}`;
    }


    // =========================================================
    //  CHART MANAGER
    //  Owns Chart.js instance lifecycle (create / destroy / render).
    //  Always call renderBarH or renderDonut — never Chart() directly.
    // =========================================================

    const ChartManager = {

        _activeInstances: {},

        // Destroy an existing chart instance before re-rendering
        destroyChart(canvasId) {
            if (this._activeInstances[canvasId]) {
                this._activeInstances[canvasId].destroy();
                delete this._activeInstances[canvasId];
            }
        },

        // Internal: create and register a Chart.js instance
        _createChart(canvasId, chartConfig) {
            const canvasElement = document.getElementById(canvasId);
            if (!canvasElement) return;
            this.destroyChart(canvasId);
            this._activeInstances[canvasId] = new Chart(canvasElement, chartConfig);
        },

        // Shared tooltip appearance used across all chart types
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

        // ── Horizontal Bar Chart ──────────────────────────────────────────
        // Renders a horizontal bar chart ideal for ranked top-N lists.
        //
        // @param {string}   canvasId    Target canvas element ID
        // @param {string[]} barLabels   Y-axis labels (one per bar, e.g. user IDs)
        // @param {number[]} barValues   Bar lengths (e.g. check-in counts)
        // @param {string[]} barColors   Per-bar colors — one color per rank position
        // @param {string}   valueUnit   Unit shown in tooltip (e.g. "Check-ins")
        renderBarH(canvasId, barLabels, barValues, barColors, valueUnit) {
            this._createChart(canvasId, {
                type: 'bar',
                data: {
                    labels:   barLabels,
                    datasets: [{
                        label:           valueUnit,      // fixes "undefined" in legend/tooltip
                        data:            barValues,
                        backgroundColor: barColors,
                        borderRadius:    5,
                        borderSkipped:   false,
                    }],
                },
                options: {
                    indexAxis:           'y',            // ← horizontal bars
                    responsive:          true,
                    maintainAspectRatio: false,
                    animation:           { duration: 500, easing: 'easeInOutQuart' },
                    plugins: {
                        legend:  { display: false },    // legend off — colors tell the story
                        tooltip: {
                            ...this._tooltip(),
                            callbacks: {
                                label: (tooltipItem) =>
                                    `  ${valueUnit}: ${tooltipItem.parsed.x.toLocaleString()}`,
                            },
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
                },
            });
        },

        // ── Doughnut Chart ────────────────────────────────────────────────
        // Renders a doughnut with a bold total + label drawn in the center hole.
        //
        // @param {string}   canvasId    Target canvas element ID
        // @param {string[]} sliceLabels One label per slice
        // @param {number[]} sliceValues One value per slice
        // @param {string[]} sliceColors One color per slice
        // @param {string}   centerLabel Small text drawn below the total in the hole
        renderDonut(canvasId, sliceLabels, sliceValues, sliceColors, centerLabel) {
            const totalValue = sliceValues.reduce((sum, value) => sum + value, 0);

            // Inline plugin: draws the total count + label text inside the donut hole
            const centerTextPlugin = {
                id: `centerText_${canvasId}`,
                afterDraw(chartInstance) {
                    const { ctx, chartArea } = chartInstance;
                    if (!chartArea) return;
                    const centerX = (chartArea.left + chartArea.right)  / 2;
                    const centerY = (chartArea.top  + chartArea.bottom) / 2;
                    ctx.save();
                    ctx.textAlign    = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font         = 'bold 22px sans-serif';
                    ctx.fillStyle    = '#111827';
                    ctx.fillText(totalValue.toLocaleString(), centerX, centerY - 9);
                    ctx.font      = '11px sans-serif';
                    ctx.fillStyle = '#6b7280';
                    ctx.fillText(centerLabel, centerX, centerY + 13);
                    ctx.restore();
                },
            };

            this._createChart(canvasId, {
                type: 'doughnut',
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
                                padding:       14,
                                usePointStyle: true,
                                pointStyle:    'circle',
                                generateLabels: (chartInstance) =>
                                    chartInstance.data.labels.map((labelText, sliceIndex) => ({
                                        text:        `${labelText} (${(chartInstance.data.datasets[0].data[sliceIndex] || 0).toLocaleString()})`,
                                        fillStyle:   chartInstance.data.datasets[0].backgroundColor[sliceIndex],
                                        strokeStyle: chartInstance.data.datasets[0].backgroundColor[sliceIndex],
                                        hidden:      false,
                                        index:       sliceIndex,
                                        pointStyle:  'circle',
                                    })),
                            },
                        },
                        tooltip: {
                            ...this._tooltip(),
                            callbacks: {
                                label: (tooltipItem) => {
                                    const percentage = totalValue > 0
                                        ? ((tooltipItem.parsed / totalValue) * 100).toFixed(1)
                                        : 0;
                                    return ` ${tooltipItem.label}: ${tooltipItem.parsed.toLocaleString()} (${percentage}%)`;
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
    //  CHART INITIALIZERS
    //  One function per tab. Add a new ChartManager.render*() call
    //  inside the matching init function to add more charts.
    //  Add a new tab by adding one entry to TAB_CHART_INITIALIZERS.
    // =========================================================

    // Resolves a college name string to its configured donut color
    function resolveCollegeColor(collegeName) {
        const upperCaseName = (collegeName || '').toUpperCase();
        for (const [abbreviation, color] of Object.entries(Analytics.collegeColorMap)) {
            if (upperCaseName.includes(abbreviation)) return color;
        }
        return Analytics.collegeColorFallback;
    }

    // Flattens the nested classification → userId → data structure
    // into a flat sorted array, returning only the top N items.
    // valueKey: 'count' for check-ins, 'minutes' for duration
    function flattenUserRanking(source, valueKey, topCount) {
        const flatItems = [];
        for (const [classificationType, userMap] of Object.entries(source)) {
            for (const userData of Object.values(userMap)) {
                flatItems.push({
                    displayLabel:       userData.display_label,
                    value:              userData[valueKey] ?? 0,
                    classificationName: classificationType,
                });
            }
        }
        return flatItems
            .sort((itemA, itemB) => itemB.value - itemA.value)
            .slice(0, topCount);
    }

    function initUsersTab(responseData) {
        const TOP_COUNT = 3;

        // Top users by check-in count — horizontal bar
        const topCheckinUsers = flattenUserRanking(responseData.topCheckins, 'count', TOP_COUNT);
        ChartManager.renderBarH(
            'chartTopUserCheckins',
            topCheckinUsers.map(item => item.displayLabel),
            topCheckinUsers.map(item => item.value),
            Analytics.rankColors.checkins.slice(0, topCheckinUsers.length),
            'Check-ins'
        );

        // Top users by session duration — horizontal bar
        const topDurationUsers = flattenUserRanking(responseData.topDuration, 'minutes', TOP_COUNT);
        ChartManager.renderBarH(
            'chartTopUserDuration',
            topDurationUsers.map(item => item.displayLabel),
            topDurationUsers.map(item => Math.round(item.value)),
            Analytics.rankColors.duration.slice(0, topDurationUsers.length),
            'Minutes'
        );

        // Visitor type breakdown — doughnut
        ChartManager.renderDonut(
            'chartVisitorTypeDonut',
            Object.keys(responseData.classificationDistribution),
            Object.values(responseData.classificationDistribution),
            Analytics.donutColorsVisitorType,
            'Visitors'
        );
    }

    function initCollegesTab(responseData) {
        const checkinCollegeNames  = Object.keys(responseData.top3CollegesCheckin);
        const durationCollegeNames = Object.keys(responseData.top3CollegesDuration);

        ChartManager.renderDonut(
            'chartCollegeCheckin',
            checkinCollegeNames,
            checkinCollegeNames.map(name => responseData.top3CollegesCheckin[name].count),
            checkinCollegeNames.map(resolveCollegeColor),
            'Visitors'
        );

        ChartManager.renderDonut(
            'chartCollegeDuration',
            durationCollegeNames,
            durationCollegeNames.map(name => Math.round(responseData.top3CollegesDuration[name].minutes)),
            durationCollegeNames.map(resolveCollegeColor),
            'Minutes'
        );
    }

    function initCoursesTab(responseData) {
        // Flatten all college→course entries into parallel arrays for the donut chart
        function flattenCourseData(sourceObject, valueKey) {
            const labelList  = [];
            const valueList  = [];
            const colorList  = [];
            Object.entries(sourceObject).forEach(([collegeName, courseMap], collegeIndex) => {
                Object.entries(courseMap).forEach(([courseName, courseData], courseIndex) => {
                    labelList.push(`${collegeName} · ${courseName}`);
                    valueList.push(valueKey === 'count' ? courseData.count : Math.round(courseData.minutes));
                    colorList.push(Analytics.donutColorsCourse[(collegeIndex * 3 + courseIndex) % Analytics.donutColorsCourse.length]);
                });
            });
            return { labelList, valueList, colorList };
        }

        const checkinCourseData  = flattenCourseData(responseData.topCoursesCheckin,  'count');
        const durationCourseData = flattenCourseData(responseData.topCoursesDuration, 'minutes');

        if (checkinCourseData.labelList.length) {
            ChartManager.renderDonut('chartCoursesCheckin',  checkinCourseData.labelList,  checkinCourseData.valueList,  checkinCourseData.colorList,  'Visitors');
        }
        if (durationCourseData.labelList.length) {
            ChartManager.renderDonut('chartCoursesDuration', durationCourseData.labelList, durationCourseData.valueList, durationCourseData.colorList, 'Minutes');
        }
    }

    function initDemographicsTab(responseData) {
        ChartManager.renderDonut(
            'chartSexDonut',
            Object.keys(responseData.sexDistribution),
            Object.values(responseData.sexDistribution),
            Analytics.donutColorsSex,
            'Visitors'
        );
    }

    // Maps each tab key to its chart initializer function.
    // To add a new tab: add one entry here + its PHP tab renderer.
    const TAB_CHART_INITIALIZERS = {
        users:        initUsersTab,
        colleges:     initCollegesTab,
        courses:      initCoursesTab,
        demographics: initDemographicsTab,
    };


    // =========================================================
    //  KPI CARDS
    // =========================================================

    function updateKpiCards(responseData) {
        const selectedEndDate  = filters.endDate.val();
        const formattedEndDate = selectedEndDate
            ? new Date(selectedEndDate).toLocaleDateString('en-US', {
                month: 'short', day: 'numeric', year: 'numeric',
              })
            : '—';

        kpi.totalCheckins.text(responseData.totalVisits.toLocaleString());
        kpi.avgDuration.text(responseData.avgDuration + ' min');
        kpi.endDateLabel.text('Check-ins on ' + formattedEndDate);
        kpi.endDateCheckins.text(responseData.endDateCheckins.toLocaleString());

        const currentTime = new Date().toLocaleTimeString('en-US', {
            hour: 'numeric', minute: 'numeric', hour12: true,
        });
        $lastUpdatedLabel.html(`<i class="fas fa-sync-alt me-1"></i> Last updated: Today at ${currentTime}`);
    }


    // =========================================================
    //  TAB LOADER
    // =========================================================

    function loadTab(tabKey) {
        activeTab = tabKey;

        $tabNavButtons.removeClass('active');
        $tabNavButtons.filter(`[data-tab="${tabKey}"]`).addClass('active');

        if (pendingTabRequest) pendingTabRequest.abort();

        showSpinner();

        pendingTabRequest = $.ajax({
            url:      Analytics.backendUrl,
            type:     'POST',
            dataType: 'json',
            data:     { action: 'tab', tab: tabKey, ...getActiveFilters() },
        })
        .done(function (responseData) {
            hideSpinner();
            if (responseData.status !== 'success') {
                $tabContentArea.html(`<div class="alert alert-danger m-3">${responseData.message}</div>`);
                return;
            }
            $tabContentArea.html(responseData.html);
            TAB_CHART_INITIALIZERS[tabKey]?.(responseData);
            updateKpiCards(responseData);
            lastResponse = responseData;
        })
        .fail(function (xhr, statusText) {
            hideSpinner();
            if (statusText !== 'abort') {
                $tabContentArea.html('<div class="alert alert-danger m-3">Failed to load analytics. Please try again.</div>');
            }
        });
    }


    // =========================================================
    //  VIEW ALL MODAL
    //  Follows the openAddModal pattern: destroys previous AJAX
    //  injection and removes stale backdrop before showing fresh data.
    // =========================================================

    function loadViewAll(tabKey, pageNumber) {
        showSpinner();

        // Destroy any open modal and stale backdrop before injecting new content
        $('.modal').modal('hide');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');

        $.ajax({
            url:      Analytics.backendUrl,
            type:     'POST',
            dataType: 'json',
            data:     { action: 'viewAll', tab: tabKey, page: pageNumber, ...getActiveFilters() },
        })
        .done(function (responseData) {
            hideSpinner();
            if (responseData.status !== 'success') {
                $('#viewAllModalBody').html('<div class="alert alert-danger m-3">Failed to load records.</div>');
                $('#viewAllModal').modal('show');
                return;
            }

            const tabLabel = Analytics.tabLabels[tabKey] ?? 'All Records';
            $('#viewAllModalLabel').text(tabLabel);
            $('#viewAllModalSubtitle').text(
                `Page ${responseData.page} of ${responseData.totalPages} · ${responseData.total} total records`
            );
            $('#viewAllModalBody').html(responseData.tableHtml);
            $('#viewAllModalCount').text(`Showing ${responseData.total} records`);
            $('#viewAllModalPagination').html(responseData.pagination);

            $('#viewAllModal').modal('show');
        })
        .fail(function () {
            hideSpinner();
        });
    }

    // Paginator — delegated, scoped to the modal footer element
    $('#viewAllModalPagination').on('click', '.page-link', function (event) {
        event.preventDefault();
        viewAllCurrentPage = parseInt($(this).data('page'), 10);
        loadViewAll(viewAllActiveTab, viewAllCurrentPage);
    });


    // =========================================================
    //  EXPORT MANAGER
    //  ─────────────────────────────────────────────────────────
    //  To add a new tab's export columns:
    //    → Add one entry to EXPORT_CONFIG below.
    //  To add a new export format (e.g. CSV):
    //    → Add a new method and bind a button in EVENT BINDINGS.
    // =========================================================

    const ExportManager = {

        // Column headers and row data mappers per tab.
        // rowMapper receives the full backend response and returns string[][].
        EXPORT_CONFIG: {
            users: {
                headers: ['ID Number', 'Type', 'Library Section', 'Check-ins', 'Duration (min)', 'Last Check-in'],
                rowMapper(responseData) {
                    const exportRows = [];
                    for (const [classificationType, userMap] of Object.entries(responseData.topCheckins)) {
                        for (const [userId, userData] of Object.entries(userMap)) {
                            const durationEntry = responseData.topDuration?.[classificationType]?.[userId];
                            exportRows.push([
                                userData.display_label,
                                classificationType,
                                userData.library ?? '—',
                                userData.count,
                                durationEntry ? Math.round(durationEntry.minutes) : '—',
                                ExportManager._fmtDate(userData.last_checkin),
                            ]);
                        }
                    }
                    return exportRows;
                },
            },

            colleges: {
                headers: ['College', 'Unique Visitors', 'Total Duration (min)', 'Last Check-in'],
                rowMapper(responseData) {
                    const mergedColleges = {};
                    for (const [collegeName, collegeData] of Object.entries(responseData.top3CollegesCheckin)) {
                        mergedColleges[collegeName] = { count: collegeData.count, minutes: '—', lastCheckin: collegeData.last_checkin };
                    }
                    for (const [collegeName, collegeData] of Object.entries(responseData.top3CollegesDuration)) {
                        mergedColleges[collegeName] ??= { count: '—', minutes: '—', lastCheckin: collegeData.last_checkin };
                        mergedColleges[collegeName].minutes = Math.round(collegeData.minutes);
                    }
                    return Object.entries(mergedColleges).map(([collegeName, row]) => [
                        collegeName, row.count, row.minutes, ExportManager._fmtDate(row.lastCheckin),
                    ]);
                },
            },

            courses: {
                headers: ['College', 'Course', 'Unique Visitors', 'Duration (min)', 'Last Check-in'],
                rowMapper(responseData) {
                    const exportRows = [];
                    for (const [collegeName, courseMap] of Object.entries(responseData.topCoursesCheckin)) {
                        for (const [courseName, courseData] of Object.entries(courseMap)) {
                            const durationData = responseData.topCoursesDuration?.[collegeName]?.[courseName];
                            exportRows.push([
                                collegeName,
                                courseName,
                                courseData.count,
                                durationData ? Math.round(durationData.minutes) : '—',
                                ExportManager._fmtDate(courseData.last_checkin),
                            ]);
                        }
                    }
                    return exportRows;
                },
            },

            demographics: {
                headers: ['Sex', 'Visitors', '% of Total'],
                rowMapper(responseData) {
                    const totalVisitors = Object.values(responseData.sexDistribution).reduce((sum, count) => sum + count, 0);
                    return Object.entries(responseData.sexDistribution).map(([sexLabel, visitorCount]) => [
                        sexLabel,
                        visitorCount,
                        totalVisitors > 0 ? (visitorCount / totalVisitors * 100).toFixed(1) + '%' : '0%',
                    ]);
                },
            },
        },

        // ── Internal Utilities ────────────────────────────────────────────

        _fmtDate(rawDateString) {
            if (!rawDateString) return '—';
            const parsedDate = new Date(rawDateString.replace(' ', 'T'));
            return isNaN(parsedDate)
                ? rawDateString
                : parsedDate.toLocaleString('en-US', {
                    month: 'short', day: 'numeric', year: 'numeric',
                    hour: 'numeric', minute: '2-digit', hour12: true,
                  });
        },

        _getExportData(tabKey) {
            if (!lastResponse) {
                alert('No data loaded. Please generate analytics first.');
                return null;
            }
            const definition = this.EXPORT_CONFIG[tabKey];
            if (!definition) {
                alert('Export is not configured for this tab.');
                return null;
            }
            return {
                headers: definition.headers,
                rows:    definition.rowMapper(lastResponse),
            };
        },

        _buildExportFilename(tabKey, fileExtension) {
            const startDate = filters.startDate.val() || 'unknown';
            const endDate   = filters.endDate.val()   || 'unknown';
            return `LibraryReport_${tabKey}_${startDate}_${endDate}.${fileExtension}`;
        },

        // Loads an external script only once — resolves instantly on repeat calls
        _loadScript(scriptUrl) {
            return new Promise((resolve, reject) => {
                if (document.querySelector(`script[src="${scriptUrl}"]`)) {
                    resolve();
                    return;
                }
                const scriptElement    = document.createElement('script');
                scriptElement.src      = scriptUrl;
                scriptElement.onload   = resolve;
                scriptElement.onerror  = () => reject(new Error(`Failed to load: ${scriptUrl}`));
                document.head.appendChild(scriptElement);
            });
        },

        // Captures all Chart.js <canvas> elements in #tabContent as PNG data URLs for PDF embedding
        _captureCharts() {
            const capturedImages = [];
            document.querySelectorAll('#tabContent canvas').forEach(canvasElement => {
                try {
                    capturedImages.push({
                        dataUrl:       canvasElement.toDataURL('image/png'),
                        pixelWidth:    canvasElement.offsetWidth  || canvasElement.width,
                        pixelHeight:   canvasElement.offsetHeight || canvasElement.height,
                    });
                } catch (captureError) {
                    // Skip tainted canvases (cross-origin)
                }
            });
            return capturedImages;
        },

        // ── PDF Export ────────────────────────────────────────────────────
        exportPDF(tabKey) {
            const exportData = this._getExportData(tabKey);
            if (!exportData) return;

            showSpinner();

            this._loadScript(Analytics.exportLibraries.jspdf)
            .then(() => this._loadScript(Analytics.exportLibraries.autotable))
            .then(() => {
                const { jsPDF }     = window.jspdf;
                const pdfDoc        = new jsPDF('l', 'mm', 'a4');
                const PAGE_MARGIN   = 14;
                const PAGE_WIDTH    = pdfDoc.internal.pageSize.getWidth();
                const PAGE_HEIGHT   = pdfDoc.internal.pageSize.getHeight();
                const CONTENT_WIDTH = PAGE_WIDTH - PAGE_MARGIN * 2;
                const tabLabel      = Analytics.tabLabels[tabKey] ?? tabKey;

                // Title block
                pdfDoc.setFont('helvetica', 'bold').setFontSize(14).setTextColor(31, 41, 55);
                pdfDoc.text('Library Analytics Report', PAGE_MARGIN, 18);
                pdfDoc.setFont('helvetica', 'normal').setFontSize(10).setTextColor(100, 100, 100);
                pdfDoc.text(`Section: ${tabLabel}   |   Period: ${buildDateRangeLabel()}`, PAGE_MARGIN, 26);
                pdfDoc.text(`Generated: ${new Date().toLocaleString()}`, PAGE_MARGIN, 32);

                // Chart images (2 per row, max 70mm tall)
                let cursorY = 38;
                const chartImages = this._captureCharts();
                if (chartImages.length > 0) {
                    const CHART_GAP      = 6;
                    const CHART_MAX_H    = 70;
                    const CHARTS_PER_ROW = Math.min(chartImages.length, 2);
                    const chartWidth     = (CONTENT_WIDTH - (CHARTS_PER_ROW - 1) * CHART_GAP) / CHARTS_PER_ROW;

                    for (let rowStart = 0; rowStart < chartImages.length; rowStart += CHARTS_PER_ROW) {
                        if (cursorY + CHART_MAX_H + 10 > PAGE_HEIGHT) {
                            pdfDoc.addPage();
                            cursorY = PAGE_MARGIN;
                        }
                        chartImages.slice(rowStart, rowStart + CHARTS_PER_ROW).forEach((chartImage, colIndex) => {
                            const drawHeight = Math.min(CHART_MAX_H, chartWidth * (chartImage.pixelHeight / chartImage.pixelWidth));
                            pdfDoc.addImage(
                                chartImage.dataUrl, 'PNG',
                                PAGE_MARGIN + colIndex * (chartWidth + CHART_GAP),
                                cursorY, chartWidth, drawHeight
                            );
                        });
                        cursorY += CHART_MAX_H + CHART_GAP;
                    }
                    cursorY += 4;
                }

                // Data table
                if (cursorY + 20 > PAGE_HEIGHT) { pdfDoc.addPage(); cursorY = PAGE_MARGIN; }
                pdfDoc.autoTable({
                    head:               [exportData.headers],
                    body:               exportData.rows,
                    startY:             cursorY,
                    styles:             { fontSize: 9, cellPadding: 3 },
                    headStyles:         { fillColor: [31, 41, 55], textColor: 255, fontStyle: 'bold' },
                    alternateRowStyles: { fillColor: [248, 250, 252] },
                    margin:             { left: PAGE_MARGIN, right: PAGE_MARGIN },
                });

                pdfDoc.save(this._buildExportFilename(tabKey, 'pdf'));
            })
            .catch(error => {
                console.error('PDF export error:', error);
                alert('PDF export failed. Check your internet connection.');
            })
            .finally(hideSpinner);
        },

        // ── XLSX Export ───────────────────────────────────────────────────
        exportXLSX(tabKey) {
            const exportData = this._getExportData(tabKey);
            if (!exportData) return;

            showSpinner();

            this._loadScript(Analytics.exportLibraries.xlsx)
            .then(() => {
                const XLSXLib       = window.XLSX;
                const tabLabel      = Analytics.tabLabels[tabKey] ?? tabKey;
                const worksheetData = [
                    ['Library Analytics Report'],
                    [`Section: ${tabLabel}   |   Period: ${buildDateRangeLabel()}`],
                    [`Generated: ${new Date().toLocaleString()}`],
                    [],
                    exportData.headers,
                    ...exportData.rows,
                ];

                const worksheet = XLSXLib.utils.aoa_to_sheet(worksheetData);

                // Auto-fit column widths based on the longest value in each column
                worksheet['!cols'] = exportData.headers.map((headerText, colIndex) => {
                    const allCellValues = [headerText, ...exportData.rows.map(row => String(row[colIndex] ?? ''))];
                    return { wch: Math.min(50, Math.max(...allCellValues.map(cellValue => cellValue.length)) + 2) };
                });

                // Merge title row across all columns
                worksheet['!merges'] = [{
                    s: { r: 0, c: 0 },
                    e: { r: 0, c: exportData.headers.length - 1 },
                }];

                const workbook = XLSXLib.utils.book_new();
                XLSXLib.utils.book_append_sheet(workbook, worksheet, tabLabel.substring(0, 31));
                XLSXLib.writeFile(workbook, this._buildExportFilename(tabKey, 'xlsx'));
            })
            .catch(error => {
                console.error('XLSX export error:', error);
                alert('XLSX export failed. Check your internet connection.');
            })
            .finally(hideSpinner);
        },
    };


    // =========================================================
    //  EVENT BINDINGS
    //  All user interactions wired here — no inline handlers in HTML.
    // =========================================================

    // Tab navigation
    $tabNavButtons.on('click', function (event) {
        event.preventDefault();
        loadTab($(this).data('tab'));
    });

    // Generate analytics
    $('#generateBtn').on('click', function () {
        if (!hasValidDateRange()) {
            alert('Please select both a start and end date.');
            return;
        }
        loadTab(activeTab);
    });

    // Refresh current tab
    $('#refreshBtn').on('click', function () {
        if (hasValidDateRange()) loadTab(activeTab);
    });

    // Auto-reload when any filter input changes
    Object.values(filters).forEach(function ($filterElement) {
        $filterElement.on('change', function () {
            if (hasValidDateRange()) loadTab(activeTab);
        });
    });

    // View All button — delegated because button lives inside AJAX-injected HTML
    $(document).on('click', '.view-all-btn', function () {
        viewAllActiveTab   = $(this).data('tab');
        viewAllCurrentPage = 1;
        loadViewAll(viewAllActiveTab, viewAllCurrentPage);
    });

    // Export buttons
    $('#exportPDF').on('click',  () => ExportManager.exportPDF(activeTab));
    $('#exportXLSX').on('click', () => ExportManager.exportXLSX(activeTab));


    // =========================================================
    //  INITIALISATION
    // =========================================================

    setDefaultDateRange();
    loadTab(Analytics.defaultTab);

});
</script>