<div class="container-fluid px-4 py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Library Dashboard</h5>
            <small class="text-muted">Overview of library activities and trends</small>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold">
            <i class="fas fa-circle me-1 text-success" style="font-size:.55rem;vertical-align:middle;"></i>Live
        </span>
    </div>

    <!-- KPI Cards -->
    <div id="kpiContainer" class="row g-3 mb-4">

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted fw-semibold text-uppercase" style="letter-spacing:.05em;font-size:.7rem;">Filipiniana 1F</span>

                    </div>
                    <h3 id="Lib1" class="fw-bold text-success mb-0 kpi-count"><?php ?></h3>
                    <small class="text-muted">active visits</small>
                </div>
                <div class="bg-success rounded-bottom" style="height:3px;"></div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted fw-semibold text-uppercase" style="letter-spacing:.05em;font-size:.7rem;">Filipiniana 2F</span>

                    </div>
                    <h3 id="Lib2" class="fw-bold text-primary mb-0 kpi-count"><?php ?></h3>
                    <small class="text-muted">active visits</small>
                </div>
                <div class="bg-primary rounded-bottom" style="height:3px;"></div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted fw-semibold text-uppercase" style="letter-spacing:.05em;font-size:.7rem;">Manuscript</span>

                    </div>
                    <h3 id="Lib3" class="fw-bold text-warning mb-0 kpi-count"><?php ?></h3>
                    <small class="text-muted">active visits</small>
                </div>
                <div class="bg-warning rounded-bottom" style="height:3px;"></div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted fw-semibold text-uppercase" style="letter-spacing:.05em;font-size:.7rem;">Recreational</span>

                    </div>
                    <h3 id="Lib4" class="fw-bold text-danger mb-0 kpi-count"><?php ?></h3>
                    <small class="text-muted">active visits</small>
                </div>
                <div class="bg-danger rounded-bottom" style="height:3px;"></div>
            </div>
        </div>

    </div>

    <!-- Daily Logs Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
            <div>
                <h6 class="mb-0 fw-bold text-dark">Daily Logs</h6>
                <small class="text-muted">Real-time check-in / check-out records</small>
            </div>
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3" style="font-size:.72rem;">Today</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="px-4 py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Student No.</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">College</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Course</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Library</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Check-In</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Check-Out</th>
                            <th class="py-3 fw-semibold text-muted text-uppercase" style="font-size:.7rem;letter-spacing:.06em;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="dailyLogs">
                        <tr>
                            <td class="px-4 fw-semibold text-dark">2022100114</td>
                            <td class="text-muted">—</td>
                            <td class="text-muted">—</td>
                            <td><span class="badge bg-light text-secondary border">—</span></td>
                            <td class="text-muted">—</td>
                            <td class="text-muted">—</td>
                            <td><span class="badge bg-success-subtle text-success rounded-pill px-3">Active</span></td>
                        </tr>
                        <tr>
                            <td class="px-4 fw-semibold text-dark">2022100114</td>
                            <td class="text-muted">—</td>
                            <td class="text-muted">—</td>
                            <td><span class="badge bg-light text-secondary border">—</span></td>
                            <td class="text-muted">—</td>
                            <td class="text-muted">—</td>
                            <td><span class="badge bg-success-subtle text-success rounded-pill px-3">Active</span></td>
                        </tr>
                        <tr>
                            <td class="px-4 fw-semibold text-dark">2022100114</td>
                            <td class="text-muted">—</td>
                            <td class="text-muted">—</td>
                            <td><span class="badge bg-light text-secondary border">—</span></td>
                            <td class="text-muted">—</td>
                            <td class="text-muted">—</td>
                            <td><span class="badge bg-success-subtle text-success rounded-pill px-3">Active</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-2 px-4">
            <nav>
                <ul class="pagination pagination-sm justify-content-center mb-0"></ul>
            </nav>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3">

        <!-- Usage Trend -->
        <!-- Should be an interactive calandar with visual display of data similar to github calendar but more intuitive
	-->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom px-4 py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-bar me-2 text-success"></i>Usage Trend</h6>
                    <small class="text-muted">Monthly student logins — last 6 months</small>
                </div>
                <div id="userChart" class="card-body px-4 pb-4 pt-3">
                    <div class="d-flex align-items-end gap-2 justify-content-between" style="height:180px;">
                        <div class="d-flex flex-column align-items-center flex-fill gap-1">
                            <small class="text-muted fw-semibold" style="font-size:.7rem;">85</small>
                            <div class="w-100 rounded-top bg-success bg-opacity-50" style="height:42.5%;min-height:4px;"></div>
                            <small class="text-muted" style="font-size:.72rem;">Sep</small>
                        </div>
                        <div class="d-flex flex-column align-items-center flex-fill gap-1">
                            <small class="text-muted fw-semibold" style="font-size:.7rem;">120</small>
                            <div class="w-100 rounded-top bg-success bg-opacity-50" style="height:60%;min-height:4px;"></div>
                            <small class="text-muted" style="font-size:.72rem;">Oct</small>
                        </div>
                        <div class="d-flex flex-column align-items-center flex-fill gap-1">
                            <small class="text-muted fw-semibold" style="font-size:.7rem;">98</small>
                            <div class="w-100 rounded-top bg-success bg-opacity-50" style="height:49%;min-height:4px;"></div>
                            <small class="text-muted" style="font-size:.72rem;">Nov</small>
                        </div>
                        <div class="d-flex flex-column align-items-center flex-fill gap-1">
                            <small class="text-muted fw-semibold" style="font-size:.7rem;">65</small>
                            <div class="w-100 rounded-top bg-success bg-opacity-50" style="height:32.5%;min-height:4px;"></div>
                            <small class="text-muted" style="font-size:.72rem;">Dec</small>
                        </div>
                        <div class="d-flex flex-column align-items-center flex-fill gap-1">
                            <small class="text-muted fw-semibold" style="font-size:.7rem;">140</small>
                            <div class="w-100 rounded-top bg-success bg-opacity-75" style="height:70%;min-height:4px;"></div>
                            <small class="text-muted" style="font-size:.72rem;">Jan</small>
                        </div>
                        <div class="d-flex flex-column align-items-center flex-fill gap-1">
                            <small class="text-success fw-bold" style="font-size:.7rem;">153</small>
                            <div class="w-100 rounded-top bg-success" style="height:76.5%;min-height:4px;"></div>
                            <small class="text-success fw-semibold" style="font-size:.72rem;">Feb</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- College Activity -->
        <!-- Should be an interactive chart with accurate visual display of data and more intuitive
	-->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom px-4 py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-users me-2 text-primary"></i>College Activity</h6>
                    <small class="text-muted">Student distribution by college — today</small>
                </div>
                <div id="departmentChart" class="card-body px-4 py-4">
                    <div class="d-flex flex-column gap-4">

                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="fw-semibold text-dark">Science</small>
                                <small class="text-muted">72 students</small>
                            </div>
                            <div class="progress" style="height:8px;border-radius:8px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width:90%;border-radius:8px;"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="fw-semibold text-dark">Engineering</small>
                                <small class="text-muted">58 students</small>
                            </div>
                            <div class="progress" style="height:8px;border-radius:8px;">
                                <div class="progress-bar bg-primary bg-opacity-75" role="progressbar" style="width:72.5%;border-radius:8px;"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="fw-semibold text-dark">Arts</small>
                                <small class="text-muted">42 students</small>
                            </div>
                            <div class="progress" style="height:8px;border-radius:8px;">
                                <div class="progress-bar bg-primary bg-opacity-50" role="progressbar" style="width:52.5%;border-radius:8px;"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

</div>


<script>
    $(document).ready(function() {

        loadKPI();
        loadLogs();
        loadMonthlyTrend();
        loadDepartmentOverview();

    });

    function loadKPI() {
        $.ajax({
            type: "POST",
            url: "backend/bk_LibraryMenu/bk_libDashboard.php",
            data: {
                request: "kpiData"
            },
            dataType: "json",
            success: function(sections) {

                // Reset all counts to 0 first
                $(".kpi-count").text("0");

                sections.forEach(section => {
                    let code = section.SectionCode?.trim();
                    let total = section.total ?? 0;

                    // Find the element with matching data-section-code
                    $(`.kpi-count[data-section-code="${code}"]`).text(total);
                });
            },
            error: function(xhr, status, error) {
                console.error("Failed to load KPI data:", error);
            }
        });
    }

    function loadLogs(page = 1) {

        $.post(
            "backend/bk_LibraryMenu/bk_libDashboard.php", {
                request: "dailyLogs",
                page: page
            },
            function(response) {

                let res = JSON.parse(response);

                $("#dailyLogs").html(res.rows);

                renderPagination(res.totalPages, res.currentPage);
            }
        );
    }
</script>