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