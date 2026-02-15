<div class="container-fluid mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h4 class="fw-bold text-dark d-flex align-items-center gap-2">
            <i class="fas fa-tachometer-alt text-primary"></i> Dashboard
        </h4>

        <small class="text-muted">Overview of library activities and trends</small>
    </div>

    <!-- KPI Cards Row -->
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card shadow-sm border-start border-4 border-success">
                <div class="card-body text-center">
                    <h6 class="card-title text-muted">Total Students Today</h6>
                    <h3 class="fw-bold text-success">153</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-start border-4 border-primary">
                <div class="card-body text-center">
                    <h6 class="card-title text-muted">Active Students Now</h6>
                    <h3 class="fw-bold text-primary">42</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-start border-4 border-warning">
                <div class="card-body text-center">
                    <h6 class="card-title text-muted">Top College</h6>
                    <h3 class="fw-bold text-warning">Science</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-start border-4 border-danger">
                <div class="card-body text-center">
                    <h6 class="card-title text-muted">Top Course</h6>
                    <h3 class="fw-bold text-danger">Biology</h3>
                </div>
            </div>
        </div>

    </div>

    <!-- Placeholder Charts Row -->
    <div class="row g-4 mb-4">

        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white d-flex align-items-center gap-2">
                    <i class="fas fa-chart-line"></i> Usage Trend (Monthly)
                </div>
                <div class="card-body">
                    <!-- STATIC CHART 1 -->
                    <div style="position: relative; height: 250px; background: #f8f9fa; border-radius: 8px; padding: 15px;">
                        <!-- Y-axis -->
                        <div style="position: absolute; left: 40px; top: 0; bottom: 30px; width: 30px; border-right: 2px solid #dee2e6;">
                            <div style="position: absolute; top: 0; right: 5px; color: #6c757d; font-size: 12px;">200</div>
                            <div style="position: absolute; top: 25%; right: 5px; color: #6c757d; font-size: 12px;">150</div>
                            <div style="position: absolute; top: 50%; right: 5px; color: #6c757d; font-size: 12px;">100</div>
                            <div style="position: absolute; top: 75%; right: 5px; color: #6c757d; font-size: 12px;">50</div>
                            <div style="position: absolute; bottom: 0; right: 5px; color: #6c757d; font-size: 12px;">0</div>
                        </div>
                        
                        <!-- Chart area -->
                        <div style="position: absolute; left: 70px; right: 20px; bottom: 30px; top: 0;">
                            <!-- Grid lines -->
                            <div style="position: absolute; top: 0; left: 0; right: 0; height: 1px; background: #e9ecef;"></div>
                            <div style="position: absolute; top: 25%; left: 0; right: 0; height: 1px; background: #e9ecef;"></div>
                            <div style="position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #e9ecef;"></div>
                            <div style="position: absolute; top: 75%; left: 0; right: 0; height: 1px; background: #e9ecef;"></div>
                            
                            <!-- Data points -->
                            <div style="position: absolute; bottom: 0; width: 100%;">
                                <!-- Sep (85) - 42.5% -->
                                <div style="position: absolute; left: 10%; bottom: 0; width: 6%;">
                                    <div style="height: 42.5%; background: rgba(16, 185, 129, 0.5); border-radius: 4px 4px 0 0;"></div>
                                    <div style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); color: #6c757d; font-size: 12px;">Sep</div>
                                </div>
                                
                                <!-- Oct (120) - 60% -->
                                <div style="position: absolute; left: 26%; bottom: 0; width: 6%;">
                                    <div style="height: 60%; background: rgba(16, 185, 129, 0.5); border-radius: 4px 4px 0 0;"></div>
                                    <div style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); color: #6c757d; font-size: 12px;">Oct</div>
                                </div>
                                
                                <!-- Nov (98) - 49% -->
                                <div style="position: absolute; left: 42%; bottom: 0; width: 6%;">
                                    <div style="height: 49%; background: rgba(16, 185, 129, 0.5); border-radius: 4px 4px 0 0;"></div>
                                    <div style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); color: #6c757d; font-size: 12px;">Nov</div>
                                </div>
                                
                                <!-- Dec (65) - 32.5% -->
                                <div style="position: absolute; left: 58%; bottom: 0; width: 6%;">
                                    <div style="height: 32.5%; background: rgba(16, 185, 129, 0.5); border-radius: 4px 4px 0 0;"></div>
                                    <div style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); color: #6c757d; font-size: 12px;">Dec</div>
                                </div>
                                
                                <!-- Jan (140) - 70% -->
                                <div style="position: absolute; left: 74%; bottom: 0; width: 6%;">
                                    <div style="height: 70%; background: rgba(16, 185, 129, 0.5); border-radius: 4px 4px 0 0;"></div>
                                    <div style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); color: #6c757d; font-size: 12px;">Jan</div>
                                </div>
                                
                                <!-- Feb (153) - 76.5% -->
                                <div style="position: absolute; left: 90%; bottom: 0; width: 6%;">
                                    <div style="height: 76.5%; background: rgba(16, 185, 129, 0.5); border-radius: 4px 4px 0 0;"></div>
                                    <div style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); color: #6c757d; font-size: 12px;">Feb</div>
                                </div>
                            </div>
                            
                            <!-- Connecting line -->
                            <div style="position: absolute; top: 58%; left: 13%; right: 13%; height: 2px; background: #10b981; opacity: 0.3;"></div>
                        </div>
                        
                        <!-- Chart title -->
                        <div style="position: absolute; top: 10px; left: 50%; transform: translateX(-50%); color: #495057; font-weight: bold; font-size: 14px;">
                            Monthly Student Logins
                        </div>
                    </div>
                    <p class="text-muted small mt-2">Student logins over the last 6 months</p>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
                    <i class="fas fa-users"></i> College Activity Overview
                </div>
                <div class="card-body">
                    <!-- STATIC CHART 2 -->
                    <div style="position: relative; height: 250px; background: #f8f9fa; border-radius: 8px; padding: 15px;">
                        <!-- Y-axis -->
                        <div style="position: absolute; left: 40px; top: 0; bottom: 30px; width: 30px; border-right: 2px solid #dee2e6;">
                            <div style="position: absolute; top: 0; right: 5px; color: #6c757d; font-size: 12px;">80</div>
                            <div style="position: absolute; top: 25%; right: 5px; color: #6c757d; font-size: 12px;">60</div>
                            <div style="position: absolute; top: 50%; right: 5px; color: #6c757d; font-size: 12px;">40</div>
                            <div style="position: absolute; top: 75%; right: 5px; color: #6c757d; font-size: 12px;">20</div>
                            <div style="position: absolute; bottom: 0; right: 5px; color: #6c757d; font-size: 12px;">0</div>
                        </div>
                        
                        <!-- Chart area -->
                        <div style="position: absolute; left: 70px; right: 20px; bottom: 30px; top: 0;">
                            <!-- Grid lines -->
                            <div style="position: absolute; top: 0; left: 0; right: 0; height: 1px; background: #e9ecef;"></div>
                            <div style="position: absolute; top: 25%; left: 0; right: 0; height: 1px; background: #e9ecef;"></div>
                            <div style="position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: #e9ecef;"></div>
                            <div style="position: absolute; top: 75%; left: 0; right: 0; height: 1px; background: #e9ecef;"></div>
                            
                            <!-- Data bars -->
                            <div style="position: absolute; bottom: 0; width: 100%;">
                                <!-- Science (72) - 90% -->
                                <div style="position: absolute; left: 15%; bottom: 0; width: 15%;">
                                    <div style="height: 90%; background: rgba(59, 130, 246, 0.7); border-radius: 4px 4px 0 0;"></div>
                                    <div style="position: absolute; top: -25px; left: 50%; transform: translateX(-50%); background: #3b82f6; color: white; padding: 2px 6px; border-radius: 4px; font-size: 12px;">
                                        72
                                    </div>
                                    <div style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); color: #6c757d; font-size: 12px;">Science</div>
                                </div>
                                
                                <!-- Engineering (58) - 72.5% -->
                                <div style="position: absolute; left: 40%; bottom: 0; width: 15%;">
                                    <div style="height: 72.5%; background: rgba(59, 130, 246, 0.7); border-radius: 4px 4px 0 0;"></div>
                                    <div style="position: absolute; top: -25px; left: 50%; transform: translateX(-50%); background: #3b82f6; color: white; padding: 2px 6px; border-radius: 4px; font-size: 12px;">
                                        58
                                    </div>
                                    <div style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); color: #6c757d; font-size: 12px;">Engineering</div>
                                </div>
                                
                                <!-- Arts (42) - 52.5% -->
                                <div style="position: absolute; left: 65%; bottom: 0; width: 15%;">
                                    <div style="height: 52.5%; background: rgba(59, 130, 246, 0.7); border-radius: 4px 4px 0 0;"></div>
                                    <div style="position: absolute; top: -25px; left: 50%; transform: translateX(-50%); background: #3b82f6; color: white; padding: 2px 6px; border-radius: 4px; font-size: 12px;">
                                        42
                                    </div>
                                    <div style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); color: #6c757d; font-size: 12px;">Arts</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chart title -->
                        <div style="position: absolute; top: 10px; left: 50%; transform: translateX(-50%); color: #495057; font-weight: bold; font-size: 14px;">
                            Students by College (Today)
                        </div>
                    </div>
                    <p class="text-muted small mt-2">Current student distribution across colleges</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Quick Links / Actions -->
    

</div>

<!-- NO Chart.js needed -->
<script>
    // No JavaScript needed - everything is static
    console.log("Dashboard loaded with static data");
</script>