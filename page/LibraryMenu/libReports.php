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
            <button class="nav-link active" id="users" type="button">
                <i class="fas fa-user-graduate me-2"></i> Users
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="colleges" type="button">
                <i class="fas fa-university me-2"></i> Colleges
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="courses" type="button">
                <i class="fas fa-book me-2"></i> Courses
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="demographics" type="button">
                <i class="fas fa-venus-mars me-2"></i> Demographics
            </button>
        </li>
    </ul>
</div>



    <!-- TAB CONTENT-->
    <div class="tab-content" id="tabContent">
	<!-- INJECT TAB CONTENT HERE improved card layouts -->
		
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
const usersBtn = document.getElementById(".users");
const collegeBtn = document.getElementById(".college");
const courseBtn = document.getElementById(".course");
const sexBtn = document.getElementById(".sex");

const resultParagraph = document.getElementById("result");

function displayTab() {
    console.log("You clicked the button");
}
usersBtn.addEventListener('click', displayTab);
</script>




