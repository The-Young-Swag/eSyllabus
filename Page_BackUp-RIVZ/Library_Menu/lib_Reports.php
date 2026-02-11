<div class="container-fluid mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h4 class="fw-bold text-dark d-flex align-items-center gap-2">
            <i class="fas fa-chart-line text-primary"></i> Library Log Analytics
        </h4>

        <!-- Download PDF Button -->
        <button class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" onclick="alert('PDF download would be implemented here')">
            <i class="fas fa-file-pdf"></i> Download PDF
        </button>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-success text-white d-flex flex-wrap align-items-center gap-3">
            <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-filter"></i> Filters
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                
                <!-- College Filter -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold">College</label>
                    <select class="form-select" id="filterCollege">
                        <option value="all">All Colleges</option>
                        <option value="science">Science</option>
                        <option value="engineering">Engineering</option>
                        <option value="arts">Arts</option>
                    </select>
                </div>

                <!-- Course Filter -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Course</label>
                    <select class="form-select" id="filterCourse">
                        <option value="all">All Courses</option>
                        <option value="biology">Biology</option>
                        <option value="computer">Computer Engineering</option>
                        <option value="communication">Communication</option>
                    </select>
                </div>

                <!-- Date Range Filter -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date Range</label>
                    <select class="form-select" id="filterDateRange">
                        <option value="monthly">Monthly</option>
                        <option value="semestral">Semestral</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>

                <!-- Apply Filter Button -->
                <div class="col-md-3 d-grid">
                    <button class="btn btn-success" onclick="alert('Filters would apply here')">
                        <i class="fas fa-sync-alt me-1"></i> Apply Filters
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Chart Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-chart-area"></i> Library Usage Trends
            </h5>
            <small class="text-white">Activity overview</small>
        </div>
        <div class="card-body">
            <!-- STATIC CHART IMAGE -->
            <div class="text-center">
                <!-- Simple ASCII-style chart using HTML/CSS -->
                <div style="position: relative; height: 300px; background: #f8f9fa; border-radius: 8px; padding: 20px;">
                    <!-- Y-axis labels -->
                    <div style="position: absolute; left: 40px; top: 0; bottom: 40px; width: 30px; border-right: 2px solid #dee2e6;">
                        <div style="position: absolute; top: 0; right: 5px; color: #6c757d;">150</div>
                        <div style="position: absolute; top: 25%; right: 5px; color: #6c757d;">112</div>
                        <div style="position: absolute; top: 50%; right: 5px; color: #6c757d;">75</div>
                        <div style="position: absolute; top: 75%; right: 5px; color: #6c757d;">37</div>
                        <div style="position: absolute; bottom: 0; right: 5px; color: #6c757d;">0</div>
                    </div>
                    
                    <!-- Chart area -->
                    <div style="position: absolute; left: 70px; right: 20px; bottom: 40px; top: 0;">
                        <!-- Grid lines -->
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 1px; background: #e9ecef;"></div>
                        <div style="position: absolute; top: 25%; left: 0; right: 0; height: 1px; background: #e9ecef;"></div>
                        <div style="position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #e9ecef;"></div>
                        <div style="position: absolute; top: 75%; left: 0; right: 0; height: 1px; background: #e9ecef;"></div>
                        
                        <!-- Data points and line -->
                        <div style="position: absolute; bottom: 0; width: 100%;">
                            <!-- January (120) - 80% height -->
                            <div style="position: absolute; left: 10%; bottom: 0; width: 8%;">
                                <div style="position: relative;">
                                    <div style="height: 80%; background: rgba(16, 185, 129, 0.7); border-radius: 4px 4px 0 0;"></div>
                                    <div style="position: absolute; top: -25px; left: 50%; transform: translateX(-50%); background: #10b981; color: white; padding: 2px 6px; border-radius: 4px; font-size: 12px; white-space: nowrap;">
                                        120
                                    </div>
                                </div>
                                <div style="position: absolute; bottom: -30px; left: 50%; transform: translateX(-50%); color: #6c757d; font-size: 14px;">Jan</div>
                            </div>
                            
                            <!-- February (98) - 65% height -->
                            <div style="position: absolute; left: 26%; bottom: 0; width: 8%;">
                                <div style="position: relative;">
                                    <div style="height: 65%; background: rgba(16, 185, 129, 0.7); border-radius: 4px 4px 0 0;"></div>
                                    <div style="position: absolute; top: -25px; left: 50%; transform: translateX(-50%); background: #10b981; color: white; padding: 2px 6px; border-radius: 4px; font-size: 12px; white-space: nowrap;">
                                        98
                                    </div>
                                </div>
                                <div style="position: absolute; bottom: -30px; left: 50%; transform: translateX(-50%); color: #6c757d; font-size: 14px;">Feb</div>
                            </div>
                            
                            <!-- March (85) - 57% height -->
                            <div style="position: absolute; left: 42%; bottom: 0; width: 8%;">
                                <div style="position: relative;">
                                    <div style="height: 57%; background: rgba(16, 185, 129, 0.7); border-radius: 4px 4px 0 0;"></div>
                                    <div style="position: absolute; top: -25px; left: 50%; transform: translateX(-50%); background: #10b981; color: white; padding: 2px 6px; border-radius: 4px; font-size: 12px; white-space: nowrap;">
                                        85
                                    </div>
                                </div>
                                <div style="position: absolute; bottom: -30px; left: 50%; transform: translateX(-50%); color: #6c757d; font-size: 14px;">Mar</div>
                            </div>
                            
                            <!-- April (140) - 93% height -->
                            <div style="position: absolute; left: 58%; bottom: 0; width: 8%;">
                                <div style="position: relative;">
                                    <div style="height: 93%; background: rgba(16, 185, 129, 0.7); border-radius: 4px 4px 0 0;"></div>
                                    <div style="position: absolute; top: -25px; left: 50%; transform: translateX(-50%); background: #10b981; color: white; padding: 2px 6px; border-radius: 4px; font-size: 12px; white-space: nowrap;">
                                        140
                                    </div>
                                </div>
                                <div style="position: absolute; bottom: -30px; left: 50%; transform: translateX(-50%); color: #6c757d; font-size: 14px;">Apr</div>
                            </div>
                            
                            <!-- May (110) - 73% height -->
                            <div style="position: absolute; left: 74%; bottom: 0; width: 8%;">
                                <div style="position: relative;">
                                    <div style="height: 73%; background: rgba(16, 185, 129, 0.7); border-radius: 4px 4px 0 0;"></div>
                                    <div style="position: absolute; top: -25px; left: 50%; transform: translateX(-50%); background: #10b981; color: white; padding: 2px 6px; border-radius: 4px; font-size: 12px; white-space: nowrap;">
                                        110
                                    </div>
                                </div>
                                <div style="position: absolute; bottom: -30px; left: 50%; transform: translateX(-50%); color: #6c757d; font-size: 14px;">May</div>
                            </div>
                            
                            <!-- June (130) - 87% height -->
                            <div style="position: absolute; left: 90%; bottom: 0; width: 8%;">
                                <div style="position: relative;">
                                    <div style="height: 87%; background: rgba(16, 185, 129, 0.7); border-radius: 4px 4px 0 0;"></div>
                                    <div style="position: absolute; top: -25px; left: 50%; transform: translateX(-50%); background: #10b981; color: white; padding: 2px 6px; border-radius: 4px; font-size: 12px; white-space: nowrap;">
                                        130
                                    </div>
                                </div>
                                <div style="position: absolute; bottom: -30px; left: 50%; transform: translateX(-50%); color: #6c757d; font-size: 14px;">Jun</div>
                            </div>
                            
                            <!-- Connecting line (simulated) -->
                            <div style="position: absolute; top: 20%; left: 14%; right: 14%; height: 2px; background: #10b981; opacity: 0.3;"></div>
                        </div>
                    </div>
                    
                    <!-- X-axis label -->
                    <div style="position: absolute; bottom: -20px; left: 0; right: 0; text-align: center; color: #6c757d; font-size: 14px;">
                        Months
                    </div>
                    
                    <!-- Y-axis label -->
                    <div style="position: absolute; top: 50%; left: -50px; transform: translateY(-50%) rotate(-90deg); color: #6c757d; font-size: 14px; white-space: nowrap;">
                        Number of Students
                    </div>
                    
                    <!-- Chart title -->
                    <div style="position: absolute; top: 10px; left: 50%; transform: translateX(-50%); color: #495057; font-weight: bold; font-size: 16px;">
                        Monthly Library Attendance
                    </div>
                </div>
                
                <!-- Legend -->
                <div class="mt-4 text-center">
                    <div class="d-inline-block px-3 py-1 rounded" style="background: #f8f9fa; border: 1px solid #dee2e6;">
                        <span class="d-inline-block me-2" style="width: 15px; height: 15px; background: rgba(16, 185, 129, 0.7); vertical-align: middle;"></span>
                        <span class="text-muted">Student Attendance Count</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Table Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-table"></i> Detailed Usage Summary
            </h5>
            <small class="text-white">Top months, colleges, and courses</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0" id="usageSummaryTable">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th>College</th>
                            <th>Course</th>
                            <th># of Students</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- STATIC DATA - NO JS NEEDED -->
                        <tr>
                            <td>January</td>
                            <td>Science</td>
                            <td>Biology</td>
                            <td>120</td>
                        </tr>
                        <tr>
                            <td>February</td>
                            <td>Engineering</td>
                            <td>Computer</td>
                            <td>98</td>
                        </tr>
                        <tr>
                            <td>March</td>
                            <td>Arts</td>
                            <td>Communication</td>
                            <td>85</td>
                        </tr>
                        <tr>
                            <td>April</td>
                            <td>Science</td>
                            <td>Biology</td>
                            <td>140</td>
                        </tr>
                        <tr>
                            <td>May</td>
                            <td>Engineering</td>
                            <td>Computer</td>
                            <td>110</td>
                        </tr>
                        <tr>
                            <td>June</td>
                            <td>Arts</td>
                            <td>Communication</td>
                            <td>130</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Static pagination -->
            <nav class="mt-2">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item disabled">
                        <button class="page-link"><i class="fas fa-angle-double-left"></i></button>
                    </li>
                    <li class="page-item disabled">
                        <button class="page-link"><i class="fas fa-angle-left"></i></button>
                    </li>
                    <li class="page-item active">
                        <button class="page-link">1</button>
                    </li>
                    <li class="page-item">
                        <button class="page-link" onclick="alert('Page 2')">2</button>
                    </li>
                    <li class="page-item">
                        <button class="page-link" onclick="alert('Next page')"><i class="fas fa-angle-right"></i></button>
                    </li>
                    <li class="page-item">
                        <button class="page-link" onclick="alert('Last page')"><i class="fas fa-angle-double-right"></i></button>
                    </li>
                </ul>
            </nav>
        </div>
        <!-- Footer -->
        <div class="card-footer bg-light d-flex justify-content-between small text-muted">
            <span>Showing 6 records</span>
            <span>Page 1 of 2</span>
        </div>
    </div>

</div>

<script>
    // Minimal JavaScript for alerts only
    document.querySelectorAll('.page-link').forEach(button => {
        if (!button.closest('.disabled')) {
            button.addEventListener('click', function(e) {
                if(this.textContent.includes('angle') || this.textContent === '2') {
                    alert('Pagination would work here in a real implementation');
                }
            });
        }
    });
</script>