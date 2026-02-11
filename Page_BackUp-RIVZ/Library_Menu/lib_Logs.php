<div class="container-fluid mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h4 class="fw-bold text-dark d-flex align-items-center gap-2">
            <i class="fas fa-tachometer-alt text-primary"></i> Library Attendance Logs
        </h4>
        <small class="text-muted">Quick overview of today's library attendance</small>
    </div>

    <!-- KPI Counters -->
<div class="row row-cols-1 row-cols-md-4 g-4 mb-4">

    <!-- Total Students -->
    <div class="col">
        <div class="card h-100 shadow-sm border-start border-4 border-success">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                <h6 class="card-title text-muted mb-2">Total Students In Attendance</h6>
                <h2 class="fw-bold text-success mb-0">3</h2>
            </div>
        </div>
    </div>

    <!-- Total Colleges -->
    <div class="col">
        <div class="card h-100 shadow-sm border-start border-4 border-primary">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                <h6 class="card-title text-muted mb-2">Total Colleges In Attendance</h6>
                <h2 class="fw-bold text-primary mb-0">3</h2>
            </div>
        </div>
    </div>

    <!-- Total Courses -->
    <div class="col">
        <div class="card h-100 shadow-sm border-start border-4 border-warning">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                <h6 class="card-title text-muted mb-2">Total Courses In Attendance</h6>
                <h2 class="fw-bold text-warning mb-0">4</h2>
            </div>
        </div>
    </div>

    <!-- Current Time -->
    <div class="col">
        <div class="card h-100 shadow-sm border-start border-4 border-danger">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                <h6 class="card-title text-muted mb-2">Current Time</h6>
                <h2 class="fw-bold text-danger mb-0" id="kpiCurrentTime">--</h2>
            </div>
        </div>
    </div>

</div>


    <!-- Logs Table -->
    <div class="card shadow-sm">

        <!-- Header -->
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-scroll"></i>
                <span>Library Attendance Logs (Today)</span>
            </div>

            <button class="btn btn-danger btn-sm d-flex align-items-center gap-1" id="btnDownloadPDF">
                <i class="fas fa-file-pdf"></i> Download PDF
            </button>
        </div>

        <!-- Filters -->
        <div class="card-body border-bottom py-3">
            <div class="row g-3">

                <div class="col-md-4">
                    <label class="form-label mb-1 small">Library</label>
                    <select class="form-select form-select-sm" id="librarySelect">
                        <option value="all">All Libraries</option>
                        <option value="main">Main Library</option>
                        <option value="science">Science Library</option>
                        <option value="engineering">Engineering Library</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label mb-1 small">Search Student Number</label>
                    <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Type student number">
                </div>

            </div>
        </div>

        <!-- Table -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0" id="dashboardLogsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Student Number</th>
                            <th>College</th>
                            <th>Course</th>
                            <th>Check-In Timestamp</th>
                            <th>Check-Out Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- STATIC DATA - NO JS NEEDED -->
                        <tr>
                            <td>2021-00123</td>
                            <td>Science</td>
                            <td>Biology</td>
                            <td>08:12:34</td>
                            <td>10:05:11</td>
                        </tr>
                        <tr>
                            <td>2022-00456</td>
                            <td>Engineering</td>
                            <td>Computer</td>
                            <td>09:01:09</td>
                            <td><span class="text-muted">—</span></td>
                        </tr>
                        <tr>
                            <td>2020-00987</td>
                            <td>Arts</td>
                            <td>Communication</td>
                            <td>07:45:22</td>
                            <td>08:30:47</td>
                        </tr>
                        <tr>
                            <td>2022-00111</td>
                            <td>Science</td>
                            <td>Biology</td>
                            <td>10:15:12</td>
                            <td><span class="text-muted">—</span></td>
                        </tr>
                        <tr>
                            <td>2023-00021</td>
                            <td>Science</td>
                            <td>Physics</td>
                            <td>08:05:12</td>
                            <td><span class="text-muted">—</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (like Reports) -->
            <nav class="mt-2">
                <ul class="pagination pagination-sm justify-content-center mb-0" id="dashboardPagination"></ul>
            </nav>
        </div>

        <!-- Footer -->
        <div class="card-footer bg-light d-flex justify-content-between small text-muted">
            <span><i class="fas fa-calendar-day me-1"></i> Showing logs for today</span>
            <span>5 records</span>
        </div>

    </div>
</div>

<script>
    // Just update the time - that's it
    function updateCurrentTime() {
        document.getElementById("kpiCurrentTime").textContent = new Date().toLocaleTimeString();
    }
    
    // Update time on load and every second
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);
</script>