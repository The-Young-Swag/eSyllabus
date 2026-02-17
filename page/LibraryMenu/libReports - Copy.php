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
<!-- ===================== ANALYTICS TABS ===================== -->
<div class="mb-4">

    <ul class="nav nav-tabs" id="analyticsTabs" role="tablist">

        <!-- Users -->
        <li class="nav-item" role="presentation">
            <button
                class="nav-link active"
                id="users-tab"
                data-bs-toggle="tab"
                data-bs-target="#users"
                type="button"
                role="tab"
                aria-controls="users"
                aria-selected="true">
                <i class="fas fa-user-graduate me-2"></i>
                Users
            </button>
        </li>

        <!-- Colleges -->
        <li class="nav-item" role="presentation">
            <button
                class="nav-link"
                id="colleges-tab"
                data-bs-toggle="tab"
                data-bs-target="#colleges"
                type="button"
                role="tab"
                aria-controls="colleges"
                aria-selected="false">
                <i class="fas fa-university me-2"></i>
                Colleges
            </button>
        </li>

        <!-- Courses -->
        <li class="nav-item" role="presentation">
            <button
                class="nav-link"
                id="courses-tab"
                data-bs-toggle="tab"
                data-bs-target="#courses"
                type="button"
                role="tab"
                aria-controls="courses"
                aria-selected="false">
                <i class="fas fa-book me-2"></i>
                Courses
            </button>
        </li>

        <!-- Demographics -->
        <li class="nav-item" role="presentation">
            <button
                class="nav-link"
                id="demographics-tab"
                data-bs-toggle="tab"
                data-bs-target="#demographics"
                type="button"
                role="tab"
                aria-controls="demographics"
                aria-selected="false">
                <i class="fas fa-venus-mars me-2"></i>
                Demographics
            </button>
        </li>

    </ul>

</div>


    <!-- TAB CONTENT with improved card layouts -->
    <div class="tab-content" id="analyticsTabContent">
<!-- ================= USER ANALYTICS TAB ================= -->
<div class="tab-pane fade show active" id="users" role="tabpanel">

    <!-- SECTION TITLE -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-1">User Performance Overview</h5>
            <small class="text-muted">Engagement metrics and behavioral insights</small>
        </div>
        <div class="badge bg-light text-dark px-3 py-2">
            <i class="fas fa-users me-1 text-muted"></i> 1,234 Active Users
        </div>
    </div>

    <!-- ================= FILTER STRIP ================= -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">

            <div class="d-flex flex-wrap align-items-center gap-3">

                <div class="fw-semibold text-muted small me-2">
                    Filter by:
                </div>

                <div class="btn-group btn-group-sm" role="group">
                    <input type="radio" class="btn-check" name="userType" id="allUsers" checked>
                    <label class="btn btn-outline-dark" for="allUsers">All</label>

                    <input type="radio" class="btn-check" name="userType" id="studentsOnly">
                    <label class="btn btn-outline-dark" for="studentsOnly">Students</label>

                    <input type="radio" class="btn-check" name="userType" id="employeesOnly">
                    <label class="btn btn-outline-dark" for="employeesOnly">Employees</label>

                    <input type="radio" class="btn-check" name="userType" id="guestsOnly">
                    <label class="btn btn-outline-dark" for="guestsOnly">Guests</label>
                </div>

                <div class="ms-auto small text-muted">
                    Updated 2 minutes ago
                </div>

            </div>

        </div>
    </div>


    <!-- ================= USER TYPE SUMMARY ================= -->
    <div class="row g-4 mb-4">

        <!-- Students -->
        <div class="col-md-4">
            <div class="card border shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Students</div>
                    <div class="d-flex justify-content-between align-items-end mt-2">
                        <h3 class="fw-bold mb-0">856</h3>
                        <span class="text-success small">
                            <i class="fas fa-arrow-up me-1"></i>12%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employees -->
        <div class="col-md-4">
            <div class="card border shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Employees</div>
                    <div class="d-flex justify-content-between align-items-end mt-2">
                        <h3 class="fw-bold mb-0">234</h3>
                        <span class="text-success small">
                            <i class="fas fa-arrow-up me-1"></i>5%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guests -->
        <div class="col-md-4">
            <div class="card border shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Guests</div>
                    <div class="d-flex justify-content-between align-items-end mt-2">
                        <h3 class="fw-bold mb-0">144</h3>
                        <span class="text-danger small">
                            <i class="fas fa-arrow-down me-1"></i>3%
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <!-- ================= MAIN ANALYTICS GRID ================= -->
    <div class="row g-4">

        <!-- Top Users -->
        <div class="col-xl-8">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">Top Users by Check-ins</div>
                        <small class="text-muted">Most frequent visitors</small>
                    </div>
                    <span class="badge bg-light text-dark">Last 30 Days</span>
                </div>

                <div class="card-body">

                    <div style="height: 300px;">
                        <canvas id="chartUsersCheckin"></canvas>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th class="text-end">Check-ins</th>
                                    <th class="text-end">Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-semibold">John Smith</td>
                                    <td><span class="badge bg-light text-dark">Student</span></td>
                                    <td class="text-end">156</td>
                                    <td class="text-end text-success">+12%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>


        <!-- Duration + Activity -->
        <div class="col-xl-4">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <div class="fw-semibold">Session Insights</div>
                    <small class="text-muted">Duration & recent events</small>
                </div>

                <div class="card-body">

                    <div style="height: 200px;">
                        <canvas id="chartUsersDuration"></canvas>
                    </div>

                    <div class="mt-4">
                        <div class="fw-semibold mb-3">Recent Activity</div>

                        <div class="border rounded p-3 bg-light bg-opacity-50">

                            <div class="d-flex justify-content-between small mb-2">
                                <div><strong>Emily Davis</strong> checked in</div>
                                <div class="text-muted">5m ago</div>
                            </div>

                            <div class="d-flex justify-content-between small">
                                <div><strong>Prof. Wilson</strong> started session</div>
                                <div class="text-muted">15m ago</div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>


    <!-- ================= USAGE DISTRIBUTION ================= -->
    <div class="card border shadow-sm mt-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-semibold">Usage Patterns</div>
                <small class="text-muted">Peak hour distribution</small>
            </div>
            <select class="form-select form-select-sm w-auto">
                <option>Last 7 days</option>
                <option>Last 30 days</option>
            </select>
        </div>

        <div class="card-body">
            <div style="height: 260px;">
                <canvas id="chartTimeDistribution"></canvas>
            </div>
        </div>
    </div>

</div>


        <!-- COLLEGES TAB -->
        <div class="tab-pane fade" id="colleges" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-3">
                            <h6 class="fw-semibold mb-0">Top Colleges by Check-ins</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="chartCollegesCheckin"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-3">
                            <h6 class="fw-semibold mb-0">Top Colleges by Duration</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="chartCollegesDuration"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COURSES TAB -->
        <div class="tab-pane fade" id="courses" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-3">
                            <h6 class="fw-semibold mb-0">Top Courses by Check-ins</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="chartCoursesCheckin"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-3">
                            <h6 class="fw-semibold mb-0">Top Courses by Duration</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="chartCoursesDuration"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DEMOGRAPHICS TAB -->
        <div class="tab-pane fade" id="demographics" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-3">
                            <h6 class="fw-semibold mb-0">Check-ins by Gender</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="chartSexCheckin"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 pt-3">
                            <h6 class="fw-semibold mb-0">Duration by Gender</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 250px;">
                                <canvas id="chartSexDuration"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
$('.personalInfomation .reg-next-button, .employerInformation .reg-next-button', .accountInformation .reg-next-button').click(function (e) {
    // Get the section that this button lives in
    var form = $(this).closest("section");

    //validation and other functions commented out for ease of reading

    // Hide this section...
    section.toggle();
    // And show the next section
    section.next().toggle();

});
</script>
