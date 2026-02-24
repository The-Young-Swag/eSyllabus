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
        <div class="d-flex align-items-center gap-3">
            <div>
                <h4 class="fw-bold mb-1">Library Analytics Dashboard</h4>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge px-3 py-2 rounded-4" style="background: linear-gradient(90deg,#f3f4f6,#ffffff); color:#374151;">
                        <i class="fas fa-calendar-alt me-1 text-muted"></i> Last 30 Days
                    </span>
                    <span class="badge px-3 py-2 rounded-4" style="background: linear-gradient(90deg,#f3f4f6,#ffffff); color:#374151;">
                        <i class="fas fa-users me-1 text-muted"></i> 1,234 Visitors
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


    <!-- =========================================================
         FILTER PANEL
    ========================================================== -->
    <div class="card border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">

                <!-- Start Date -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Start Date</label>
                    <div class="input-group input-group-sm shadow-sm rounded-4 overflow-hidden"
                         style="background: linear-gradient(90deg, #e6f4ea, #ffffff);">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-calendar text-muted"></i>
                        </span>
                        <input type="date" class="form-control border-start-0 ps-0"
                               id="startDate" style="border-radius:0; background:transparent;">
                    </div>
                </div>

                <!-- End Date -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">End Date</label>
                    <div class="input-group input-group-sm shadow-sm rounded-4 overflow-hidden"
                         style="background: linear-gradient(90deg, #e6f4ea, #ffffff);">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-calendar-check text-muted"></i>
                        </span>
                        <input type="date" class="form-control border-start-0 ps-0"
                               id="endDate" style="border-radius:0; background:transparent;">
                    </div>
                </div>

                <!-- User Classification -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">User Classification</label>
                    <select class="form-select form-select-sm shadow-sm rounded-4" id="classificationFilter"
                            style="background: linear-gradient(90deg, #f3f9f7, #ffffff); border:none;">
                        <option value="All" selected>All</option>
                        <option value="Student">Student</option>
                        <option value="Employee">Employee</option>
                        <option value="Guest">Guest</option>
                    </select>
                </div>

                <!-- Library -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Library</label>
                    <select class="form-select form-select-sm shadow-sm rounded-4" id="libraryFilter"
                            style="background: linear-gradient(90deg, #f3f9f7, #ffffff); border:none;">
                        <option value="All" selected>All Libraries</option>
                        <?php foreach ($librarySections as $section): ?>
                            <option value="<?= $section['SectionID'] ?>">
                                <?= htmlspecialchars($section['SectionName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <!-- Generate Button -->
            <div class="row mt-4">
                <div class="col-12 col-md-4 offset-md-8">
                    <button class="btn w-100 fw-semibold shadow-sm" id="generateBtn"
                            style="background: linear-gradient(90deg, #047857, #10b981);
                                   color:white; border-radius:0.5rem; font-size:1rem;">
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

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100"
                 style="background: linear-gradient(135deg, #10b981, #047857);">
                <div class="card-body text-white">
                    <small class="fw-semibold text-white-50">Total Check-ins</small>
                    <h3 class="fw-bold mt-1 mb-0" id="kpiTotalCheckins">—</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100"
                 style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                <div class="card-body text-white">
                    <small class="fw-semibold text-white-50">Avg. Duration</small>
                    <h3 class="fw-bold mt-1 mb-0" id="kpiAvgDuration">—</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100"
                 style="background: linear-gradient(135deg, #6b7280, #374151);">
                <div class="card-body text-white">
                    <small class="fw-semibold text-white-50" id="kpiEndDateLabel">Check-ins on —</small>
                    <h3 class="fw-bold mt-1 mb-0" id="kpiEndDateCheckins">—</h3>
                </div>
            </div>
        </div>

    </div>


    <!-- =========================================================
         TAB NAVIGATION
    ========================================================== -->
    <div class="mb-4">
        <ul class="nav nav-tabs border-0 rounded-3 overflow-hidden shadow-sm d-flex flex-nowrap p-2"
            id="analyticsTabs" role="tablist"
            style="background:#f0f4fa; border:1px solid #d6dff0 !important; gap:6px;">

            <li class="nav-item flex-fill" role="presentation">
                <button class="nav-link active w-100 d-flex align-items-center justify-content-center gap-2 rounded-2 border-0 fw-medium px-3 py-2"
                        data-tab="users" role="tab"
                        style="background:linear-gradient(135deg,#3a6cf4,#6a3de8); color:#fff;
                               box-shadow:0 3px 12px rgba(58,108,244,0.28); font-size:0.875rem;">
                    <i class="bi bi-people-fill"></i> Users
                </button>
            </li>

            <li class="nav-item flex-fill" role="presentation">
                <button class="nav-link w-100 d-flex align-items-center justify-content-center gap-2 rounded-2 border-0 fw-medium px-3 py-2"
                        data-tab="colleges" role="tab"
                        style="background:transparent; color:#5a6a8a; font-size:0.875rem;">
                    <i class="bi bi-building-fill"></i> Colleges
                </button>
            </li>

            <li class="nav-item flex-fill" role="presentation">
                <button class="nav-link w-100 d-flex align-items-center justify-content-center gap-2 rounded-2 border-0 fw-medium px-3 py-2"
                        data-tab="courses" role="tab"
                        style="background:transparent; color:#5a6a8a; font-size:0.875rem;">
                    <i class="bi bi-journal-bookmark-fill"></i> Courses
                </button>
            </li>

            <li class="nav-item flex-fill" role="presentation">
                <button class="nav-link w-100 d-flex align-items-center justify-content-center gap-2 rounded-2 border-0 fw-medium px-3 py-2"
                        data-tab="demographics" role="tab"
                        style="background:transparent; color:#5a6a8a; font-size:0.875rem;">
                    <i class="bi bi-bar-chart-fill"></i> Demographics
                </button>
            </li>

        </ul>
    </div>


    <!-- =========================================================
         TAB CONTENT AREA (AJAX-injected)
    ========================================================== -->
    <div id="tabContent" class="tab-content">
        <div class="text-center p-4 text-muted">Select a tab to view content...</div>
    </div>


    <!-- =========================================================
         FOOTER
    ========================================================== -->
    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
        <small class="text-muted" id="lastUpdatedLabel">
            <i class="fas fa-sync-alt me-1"></i> Last updated: —
        </small>
        <div class="d-flex gap-3 flex-wrap">
            <small class="text-muted">
                <i class="fas fa-database me-1"></i> Source: Library System
            </small>
        </div>
    </div>

</div>


<?php include '../modalContainer.php'; ?>
<?php include 'LibModals.php'; ?>


<script>
$(function () {

    // =========================================================
    //  CONFIGURATION
    //  Single place to update URLs, page sizes, or tab metadata.
    // =========================================================

    const BACKEND_URL    = 'backend/bk_LibraryMenu/bk_libReports.php';
    const ROWS_PER_PAGE  = 10;
    const DEFAULT_TAB    = 'users';
    const DEFAULT_DAYS_BACK = 7;

    const TAB_MODAL_TITLES = {
        users:        'All Users',
        colleges:     'All Colleges',
        courses:      'All Courses',
        demographics: 'All Check-in Logs',
    };

    const TAB_STYLE_ACTIVE = {
        background: 'linear-gradient(135deg,#3a6cf4,#6a3de8)',
        color:      '#fff',
        boxShadow:  '0 3px 12px rgba(58,108,244,0.28)',
    };

    const TAB_STYLE_INACTIVE = {
        background: 'transparent',
        color:      '#5a6a8a',
        boxShadow:  'none',
    };


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

    const tabContentArea  = $('#tabContent');
    const tabNavButtons   = $('#analyticsTabs .nav-link');
    const lastUpdatedLabel = $('#lastUpdatedLabel');


    // =========================================================
    //  APPLICATION STATE
    // =========================================================

    let activeTab      = DEFAULT_TAB;
    let pendingRequest = null;   // Tracks the active AJAX call so it can be cancelled on re-load

    let viewAllTab       = DEFAULT_TAB;
    let viewAllPage      = 1;


    // =========================================================
    //  FILTER HELPERS
    // =========================================================

    /**
     * Collects current filter input values into a plain object
     * ready to spread into any AJAX data payload.
     */
    function getActiveFilters() {
        return {
            startDate:      filters.startDate.val(),
            endDate:        filters.endDate.val(),
            classification: filters.classification.val(),
            library:        filters.library.val(),
        };
    }

    /**
     * Returns true only when both a start and end date have been selected.
     * Used to guard requests that require a date range.
     */
    function hasDateRange() {
        return filters.startDate.val() && filters.endDate.val();
    }

    /**
     * Sets the default date range to the last N days when the page first loads
     * and no dates have been pre-filled.
     */
    function setDefaultDateRange() {
        if (filters.startDate.val()) return;

        const today         = new Date();
        const defaultStart  = new Date(today);
        defaultStart.setDate(today.getDate() - DEFAULT_DAYS_BACK);

        filters.startDate.val(defaultStart.toISOString().split('T')[0]);
        filters.endDate.val(today.toISOString().split('T')[0]);
    }


    // =========================================================
    //  CHART MANAGER
    //  Centralises Chart.js lifecycle — prevents canvas re-use errors
    //  by always destroying an existing chart before creating a new one.
    // =========================================================

    const ChartManager = {
        instances: {},

        destroy(id) {
            if (this.instances[id]) {
                this.instances[id].destroy();
                delete this.instances[id];
            }
        },

        create(id, config) {
            const canvas = document.getElementById(id);
            if (!canvas) return;
            this.destroy(id);
            this.instances[id] = new Chart(canvas, config);
        },

        renderBar(canvasId, labels, values, datasetLabel) {
            this.create(canvasId, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label:           datasetLabel,
                        data:            values,
                        backgroundColor: 'rgba(54,162,235,0.7)',
                        borderRadius:    8,
                        maxBarThickness: 40,
                    }]
                },
                options: {
                    responsive:          true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales:  { y: { beginAtZero: true, min: 0 } },
                }
            });
        },

        renderDoughnut(canvasId, labels, values) {
            this.create(canvasId, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data:            values,
                        backgroundColor: ['#4F46E5', '#06B6D4', '#F59E0B', '#EF4444'],
                    }]
                },
                options: {
                    responsive:          true,
                    maintainAspectRatio: false,
                    cutout:              '65%',
                }
            });
        },
    };


    // =========================================================
    //  CHART INITIALIZERS
    //  One function per tab — each reads its own slice of the
    //  backend response and maps it to the correct canvas IDs.
    // =========================================================

    /**
     * Extracts the display_label from each user entry across all classification groups.
     * Matches the backend USER_DISPLAY_FIELD — currently id_number.
     */
    function extractUserLabels(groupedData) {
        const labels = [];
        Object.values(groupedData).forEach(group =>
            Object.values(group).forEach(item => labels.push(item.display_label))
        );
        return labels;
    }

    /**
     * Extracts a numeric value (e.g. count, minutes) from each user entry
     * across all classification groups.
     */
    function extractUserValues(groupedData, valueKey) {
        const values = [];
        Object.values(groupedData).forEach(group =>
            Object.values(group).forEach(item => values.push(item[valueKey]))
        );
        return values;
    }

    function initUsersCharts(backendResponse) {
        ChartManager.renderBar(
            'chartUsersCheckin',
            extractUserLabels(backendResponse.topCheckins),
            extractUserValues(backendResponse.topCheckins, 'count'),
            'Check-ins'
        );
        ChartManager.renderBar(
            'chartUsersDuration',
            extractUserLabels(backendResponse.topDuration),
            extractUserValues(backendResponse.topDuration, 'minutes'),
            'Duration (min)'
        );
    }

    function initCollegesCharts(backendResponse) {
        ChartManager.renderBar(
            'chartCollegeCheckin',
            Object.keys(backendResponse.top3CollegesCheckin),
            Object.values(backendResponse.top3CollegesCheckin).map(college => college.count),
            'Check-ins'
        );
        ChartManager.renderBar(
            'chartCollegeDuration',
            Object.keys(backendResponse.top3CollegesDuration),
            Object.values(backendResponse.top3CollegesDuration).map(college => college.minutes),
            'Duration (min)'
        );
    }

    function initCoursesCharts(backendResponse) {
        Object.keys(backendResponse.topCoursesCheckin).forEach(collegeName => {
            const safeCollegeId        = collegeName.replace(/[^a-zA-Z0-9]/g, '');
            const topCoursesByCheckins = backendResponse.topCoursesCheckin[collegeName];
            const topCoursesByDuration = backendResponse.topCoursesDuration[collegeName];

            ChartManager.renderBar(
                'chartCourseCheckin_' + safeCollegeId,
                Object.keys(topCoursesByCheckins),
                Object.values(topCoursesByCheckins).map(course => course.count),
                'Check-ins'
            );

            if (topCoursesByDuration) {
                ChartManager.renderBar(
                    'chartCourseDuration_' + safeCollegeId,
                    Object.keys(topCoursesByDuration),
                    Object.values(topCoursesByDuration).map(course => course.minutes),
                    'Duration (min)'
                );
            }
        });
    }

    function initDemographicsCharts(backendResponse) {
        ChartManager.renderDoughnut(
            'chartSexCheckin',
            Object.keys(backendResponse.sexDistribution),
            Object.values(backendResponse.sexDistribution)
        );
    }

    /**
     * Routes chart initialisation to the correct tab-specific function.
     */
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

    /**
     * Pushes fresh KPI values from the backend response into the four metric cards.
     */
    function updateKpiCards(backendResponse) {
        kpi.totalCheckins.text(backendResponse.totalVisits.toLocaleString());
        kpi.avgDuration.text(backendResponse.avgDuration + ' min');

        const selectedEndDate  = filters.endDate.val();
        const formattedEndDate = selectedEndDate
            ? new Date(selectedEndDate).toLocaleDateString('en-US', {
                month: 'short', day: 'numeric', year: 'numeric'
              })
            : '—';

        kpi.endDateLabel.text(`Check-ins on ${formattedEndDate}`);
        kpi.endDateCheckins.text(backendResponse.endDateCheckins.toLocaleString());
    }

    /**
     * Stamps the footer label with the current time so users know when data was last fetched.
     */
    function updateLastUpdatedTimestamp() {
        const currentTime = new Date().toLocaleTimeString('en-US', {
            hour: 'numeric', minute: 'numeric', hour12: true
        });
        lastUpdatedLabel.html(`<i class="fas fa-sync-alt me-1"></i> Last updated: Today at ${currentTime}`);
    }


    // =========================================================
    //  TAB LOADER
    // =========================================================

    function showLoadingSpinner() {
        tabContentArea.html(`
            <div class="d-flex justify-content-center align-items-center" style="height:300px;">
                <div class="spinner-border text-primary"></div>
            </div>
        `);
    }

    /**
     * Highlights the clicked tab button and resets all others to inactive styling.
     */
    function setActiveTabStyle(clickedTabButton) {
        tabNavButtons.each(function () {
            $(this).removeClass('active').css(TAB_STYLE_INACTIVE);
        });
        $(clickedTabButton).addClass('active').css(TAB_STYLE_ACTIVE);
    }

    /**
     * Fetches tab HTML and chart data from the backend, then renders everything.
     * Cancels any in-flight request before starting a new one.
     */
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
            }
        })
        .done(function (backendResponse) {
            if (backendResponse.status !== 'success') {
                tabContentArea.html(`<div class="text-danger p-4">${backendResponse.message}</div>`);
                return;
            }

            tabContentArea.html(backendResponse.html);
            initializeCharts(backendResponse, tab);
            updateKpiCards(backendResponse);
            updateLastUpdatedTimestamp();
        })
        .fail(function () {
            tabContentArea.html(`<div class="text-danger p-4">Failed to load analytics. Please try again.</div>`);
        });
    }


    // =========================================================
    //  VIEW ALL MODAL
    // =========================================================

    /**
     * Fetches a paginated table and pagination bar from the backend
     * and injects them into the dynamic modal.
     */
    function loadViewAll(tab, page) {
        $('#dynamicModalBody').html(
            '<div class="text-center p-4"><div class="spinner-border"></div></div>'
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
            }
        })
        .done(function (backendResponse) {
            if (backendResponse.status !== 'success') {
                $('#dynamicModalBody').html('<div class="alert alert-danger">Failed to load data.</div>');
                return;
            }

            const { page: currentPage, totalPages, total, tableHtml, pagination } = backendResponse;

            $('#dynamicModalTitle').text(TAB_MODAL_TITLES[tab] ?? 'All Records');
            $('#dynamicModalSubtitle').text(
                `Showing page ${currentPage} of ${totalPages} (Total: ${total} records)`
            );
            $('#dynamicModalBody').html(tableHtml);
            $('#dynamicModalFooter').html(pagination);

            // Attach pagination clicks — each click re-loads this modal at the selected page
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

    /**
     * Captures the visible tab content area as a multi-page PDF using
     * html2canvas + jsPDF, scaled for A4 landscape.
     */
    function exportDashboardToPdf() {
        const { jsPDF }          = window.jspdf;
        const pdf                = new jsPDF('l', 'mm', 'a4');
        const tabContentElement  = document.getElementById('tabContent');

        const A4_LANDSCAPE_WIDTH_MM  = 280;
        const A4_LANDSCAPE_HEIGHT_MM = 210;
        const MARGIN_MM              = 5;

        html2canvas(tabContentElement, { scale: 2, logging: false, useCORS: true })
            .then(canvas => {
                const imageData      = canvas.toDataURL('image/png');
                const printableWidth = A4_LANDSCAPE_WIDTH_MM - MARGIN_MM * 2;
                const scaledHeight   = (canvas.height * printableWidth) / canvas.width;

                let remainingHeight  = scaledHeight;
                let verticalOffset   = 0;

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

    // Tab navigation click
    tabNavButtons.on('click', function (e) {
        e.preventDefault();
        setActiveTabStyle(this);
        loadTab($(this).data('tab'));
    });

    // Generate Analytics button — requires both dates to be set
    $('#generateBtn').on('click', function () {
        if (!hasDateRange()) {
            alert('Please select both a start and end date.');
            return;
        }
        loadTab(activeTab);
    });

    // Refresh button — silently reloads current tab
    $('#refreshBtn').on('click', function () {
        if (hasDateRange()) loadTab(activeTab);
    });

    // Auto-reload whenever any filter input changes (requires date range)
    filters.startDate
        .add(filters.endDate)
        .add(filters.classification)
        .add(filters.library)
        .on('change', function () {
            if (hasDateRange()) loadTab(activeTab);
        });

    // View All button — opens modal (delegated because button lives in injected HTML)
    $(document).on('click', '.view-all-btn', function () {
        viewAllTab  = $(this).data('tab');
        viewAllPage = 1;
        loadViewAll(viewAllTab, viewAllPage);
        $('#dynamicModal').modal('show');
    });

    // PDF export button
    $('#exportPDF').on('click', exportDashboardToPdf);


    // =========================================================
    //  INITIALIZATION
    // =========================================================

    setDefaultDateRange();
    loadTab(DEFAULT_TAB);
});
</script>