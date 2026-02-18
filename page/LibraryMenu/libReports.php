<div class="container-fluid py-4">
    <!-- HEADER SECTION with improved visual hierarchy -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">

            <div>
                <h4 class="fw-bold mb-1">Library Analytics Dashboard</h4>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark px-3 py-2">
                        <i class="fas fa-calendar-alt me-1 text-muted"></i> Last 30 Days
                    </span>
                    <span class="badge bg-light text-dark px-3 py-2">
                        <i class="fas fa-users me-1 text-muted"></i> 1,234 Visitors
                    </span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" id="refreshBtn">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
            <button class="btn btn-danger btn-sm" id="exportPDF">
                <i class="fas fa-file-pdf me-1"></i> Export Report
            </button>
        </div>
    </div>

    <!-- ENHANCED FILTER SECTION with better layout -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fas fa-sliders-h text-primary"></i>
                        <h6 class="fw-semibold mb-0">Report Parameters</h6>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Start Date</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-calendar text-muted"></i>
                        </span>
                        <input type="date" class="form-control border-start-0 ps-0" id="startDate">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">End Date</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-calendar-check text-muted"></i>
                        </span>
                        <input type="date" class="form-control border-start-0 ps-0" id="endDate">
                    </div>
                </div>

                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button class="btn btn-primary flex-grow-1" id="generateBtn">
                        <i class="fas fa-chart-bar me-1"></i> Generate Analytics
                    </button>
             
                </div>
                
                <!-- Advanced Filters (Collapsible) -->
                <div class="col-12 collapse" id="advancedFilters">
                    <div class="row g-3 mt-2 pt-2 border-top">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Library Branch</label>
                            <select class="form-select form-select-sm">
                                <option>All Branches</option>
                                <option>Main Library</option>
                                <option>Science Library</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">User Type</label>
                            <select class="form-select form-select-sm">
                                <option>All Users</option>
                                <option>Undergraduate</option>
                                <option>Graduate</option>
                                <option>Faculty</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Time of Day</label>
                            <select class="form-select form-select-sm">
                                <option>All Hours</option>
                                <option>Morning (8AM-12PM)</option>
                                <option>Afternoon (12PM-5PM)</option>
                                <option>Evening (5PM-10PM)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KEY METRICS OVERVIEW (New section for better analytics) -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small text-muted">Total Check-ins</span>
                            <h3 class="fw-bold mt-1 mb-0">12,345</h3>
                            <span class="badge bg-success bg-opacity-25 text-success mt-2">
                                <i class="fas fa-arrow-up me-1"></i> +12.3%
                            </span>
                        </div>
                        <div class="bg-primary bg-opacity-25 p-3 rounded-3">
                            <i class="fas fa-user-check text-primary fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small text-muted">Avg. Duration</span>
                            <h3 class="fw-bold mt-1 mb-0">2.4 hrs</h3>
                            <span class="badge bg-success bg-opacity-25 text-success mt-2">
                                <i class="fas fa-arrow-up me-1"></i> +5.2%
                            </span>
                        </div>
                        <div class="bg-info bg-opacity-25 p-3 rounded-3">
                            <i class="fas fa-clock text-info fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small text-muted">Active Colleges</span>
                            <h3 class="fw-bold mt-1 mb-0">24</h3>
                            <span class="badge bg-success bg-opacity-25 text-success mt-2">
                                <i class="fas fa-arrow-up me-1"></i> +3
                            </span>
                        </div>
                        <div class="bg-warning bg-opacity-25 p-3 rounded-3">
                            <i class="fas fa-university text-warning fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="small text-muted">Unique Courses</span>
                            <h3 class="fw-bold mt-1 mb-0">156</h3>
                            <span class="badge bg-success bg-opacity-25 text-success mt-2">
                                <i class="fas fa-arrow-up me-1"></i> +8.1%
                            </span>
                        </div>
                        <div class="bg-success bg-opacity-25 p-3 rounded-3">
                            <i class="fas fa-book text-success fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION TABS for better organization -->
<!-- TAB NAVIGATION -->
<div class="mb-4">
    <ul class="nav nav-tabs" id="analyticsTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-tab="users">Users</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-tab="colleges">Colleges</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-tab="courses">Courses</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-tab="demographics">Demographics</button>
        </li>
    </ul>
</div>

<!-- TAB CONTENT -->
<div id="tabContent" class="tab-content">
    <div class="text-center p-4">Select a tab to view content...</div>
</div>



    <!-- FOOTER with last update info -->
    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
        <small class="text-muted">
            <i class="fas fa-sync-alt me-1"></i> Last updated: Today at 10:30 AM
        </small>
        <div class="d-flex gap-3">
            <small class="text-muted">
                <i class="fas fa-database me-1"></i> Source: Library System
            </small>
            <small class="text-muted">
                <i class="fas fa-chart-pie me-1"></i> 8 metrics displayed
            </small>
        </div>
    </div>
</div>

<script>
$(function () {

    const UI = {
        tabContent: $("#tabContent"),
        tabs: $("#analyticsTabs .nav-link"),
        startDate: $('#startDate'),
        endDate: $('#endDate'),
        generateBtn: $('#generateBtn')
    };

    let currentRequest = null;
    let activeTab = 'users';

    /* ===========================
       Chart Manager (No Leaks)
    ============================ */
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
        }
    };

    /* ===========================
       Loading State
    ============================ */
    function showLoading() {
        UI.tabContent.html(`
            <div class="d-flex justify-content-center align-items-center" style="height:300px">
                <div class="spinner-border text-primary"></div>
            </div>
        `);
    }

    /* ===========================
       Load Tab (AJAX Only)
    ============================ */
    function loadTab(tab) {

        activeTab = tab;

        if (currentRequest) currentRequest.abort();

        showLoading();

        currentRequest = $.ajax({
            url: "backend/bk_LibraryMenu/bk_libReports.php",
            type: "POST",
            dataType: "json",
            data: {
                tab,
                startDate: UI.startDate.val(),
                endDate: UI.endDate.val()
            }
        })
        .done(response => {

            if (response.status !== 'success') {
                UI.tabContent.html(`<div class="text-danger p-4">${response.message}</div>`);
                return;
            }

            UI.tabContent.html(response.html);
            initializeCharts(response, tab);
        })
        .fail(() => {
            UI.tabContent.html(`<div class="text-danger p-4">Failed to load analytics.</div>`);
        });
    }

    /* ===========================
       Chart Initialization
    ============================ */
    function initializeCharts(data, tab) {

        if (tab === 'users') {

            renderBar(
                'chartUsersCheckin',
                extractLabels(data.topCheckins),
                extractValues(data.topCheckins),
                'Check-ins'
            );

            renderBar(
                'chartUsersDuration',
                extractLabels(data.topDuration),
                extractValues(data.topDuration),
                'Duration (min)'
            );
        }

        if (tab === 'colleges') {

            renderBar(
                'chartCollegeCheckin',
                Object.keys(data.top3CollegesCheckin),
                Object.values(data.top3CollegesCheckin),
                'Check-ins'
            );

            renderBar(
                'chartCollegeDuration',
                Object.keys(data.top3CollegesDuration),
                Object.values(data.top3CollegesDuration),
                'Duration (min)'
            );
        }

        if (tab === 'courses') {

            Object.keys(data.topCoursesCheckin).forEach(college => {

                const cleanId = college.replace(/[^a-zA-Z0-9]/g,'');

                renderBar(
                    'chartCourseCheckin_' + cleanId,
                    Object.keys(data.topCoursesCheckin[college]),
                    Object.values(data.topCoursesCheckin[college]),
                    'Check-ins'
                );

                if (data.topCoursesDuration[college]) {
                    renderBar(
                        'chartCourseDuration_' + cleanId,
                        Object.keys(data.topCoursesDuration[college]),
                        Object.values(data.topCoursesDuration[college]),
                        'Duration (min)'
                    );
                }
            });
        }

        if (tab === 'demographics') {

            renderPie(
                'chartSexCheckin',
                Object.keys(data.sexDistribution),
                Object.values(data.sexDistribution)
            );
        }
    }

    /* ===========================
       Chart Helpers
    ============================ */
    function renderBar(id, labels, data, label) {
        ChartManager.create(id, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label,
                    data,
                    backgroundColor: 'rgba(54,162,235,0.7)',
                    borderRadius: 8,
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    function renderPie(id, labels, data) {
        ChartManager.create(id, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: ['#4F46E5','#06B6D4','#F59E0B','#EF4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%'
            }
        });
    }

    function extractLabels(groupedData) {
        const labels = [];
        Object.values(groupedData).forEach(group => {
            Object.entries(group).forEach(([key]) => {
                labels.push(key.split('|')[1]);
            });
        });
        return labels;
    }

    function extractValues(groupedData) {
        const values = [];
        Object.values(groupedData).forEach(group => {
            Object.values(group).forEach(val => {
                values.push(val);
            });
        });
        return values;
    }

    /* ===========================
       Events
    ============================ */
    UI.tabs.on('click', function () {
        UI.tabs.removeClass('active');
        $(this).addClass('active');
        loadTab($(this).data('tab'));
    });

    UI.generateBtn.on('click', function () {
        if (!UI.startDate.val() || !UI.endDate.val()) {
            alert("Select start and end date.");
            return;
        }
        loadTab(activeTab);
    });

    UI.startDate.add(UI.endDate).on('change', function () {
        if (UI.startDate.val() && UI.endDate.val()) {
            loadTab(activeTab);
        }
    });

    /* Initial Load */
    loadTab('users');
});
</script>
