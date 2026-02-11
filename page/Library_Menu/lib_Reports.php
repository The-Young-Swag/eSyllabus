<div class="container-fluid mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h4 class="fw-bold text-dark d-flex align-items-center gap-2">
            <i class="fas fa-chart-line text-primary"></i> Library Log Analytics
        </h4>

        <!-- Download PDF Button -->
        <button class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" id="downloadPdfBtn">
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

                <!-- Start Date -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Start Date</label>
                    <input type="date" class="form-control" id="filterStartDate">
                </div>

                <!-- End Date -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold">End Date</label>
                    <input type="date" class="form-control" id="filterEndDate">
                </div>

                <!-- Apply Filter Button -->
                <div class="col-md-3 d-grid">
                    <button class="btn btn-success" id="applyFiltersBtn">
                        <i class="fas fa-sync-alt me-1"></i> Generate Report
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Report Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-table"></i> Report Summary
            </h5>
            <small class="text-white" id="reportDateRange">Date Range: -</small>
        </div>
        <div class="card-body">
            <div class="row">

                <!-- Students per College -->
                <div class="col-md-6 mb-4">
                    <h6 class="fw-bold">Students per College</h6>
                    <table class="table table-bordered table-hover" id="studentsPerCollege">
                        <thead class="table-light">
                            <tr>
                                <th>College</th>
                                <th># of Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data injected by JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Students per Course -->
                <div class="col-md-6 mb-4">
                    <h6 class="fw-bold">Students per Course</h6>
                    <table class="table table-bordered table-hover" id="studentsPerCourse">
                        <thead class="table-light">
                            <tr>
                                <th>Course</th>
                                <th># of Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data injected by JS -->
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</div>

<script>
    async function fetchReportData(startDate, endDate) {
        // Call backend with start and end dates
        const res = await fetch('backend/bk_library_report.php', {
            method: 'POST',
            body: new URLSearchParams({
                start_date: startDate,
                end_date: endDate
            })
        });
        return await res.json();
    }

    function renderTable(tableId, data, labelField, valueField) {
        const tbody = document.querySelector(`#${tableId} tbody`);
        tbody.innerHTML = '';
        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${row[labelField]}</td>
                <td>${row[valueField]}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    document.getElementById('applyFiltersBtn').addEventListener('click', async () => {
        const startDate = document.getElementById('filterStartDate').value;
        const endDate = document.getElementById('filterEndDate').value;

        if (!startDate || !endDate) return alert("Please select both start and end dates.");

        const report = await fetchReportData(startDate, endDate);

        // Update date range display
        document.getElementById('reportDateRange').innerText = `Date Range: ${startDate} to ${endDate}`;

        // Populate tables
        renderTable('studentsPerCollege', report.studentsPerCollege, 'college', 'student_count');
        renderTable('studentsPerCourse', report.studentsPerCourse, 'course', 'student_count');
    });

    document.getElementById('downloadPdfBtn').addEventListener('click', () => {
        alert('PDF generation would be triggered here.');
    });
</script>
