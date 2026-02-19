<?php
// Fetch library sections for the dropdown
include "../../db/dbconnection.php";
$librarySections = execsqlSRS(
    "SELECT SectionID, SectionName FROM LibrarySection WHERE IsActive = 1 ORDER BY SectionName",
    'Select',
    []  // Empty array for parameters
);
?>

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
        <!-- Symmetrical 4‑column filter row -->
        <div class="row g-3 align-items-end">
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
            <div class="col-md-3">
                <label class="form-label small fw-semibold">User Classification</label>
                <select class="form-select form-select-sm" id="globalClassification">
                    <option value="All" selected>All</option>
                    <option value="Student">Student</option>
                    <option value="Employee">Employee</option>
                    <option value="Guest">Guest</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Library</label>
                <select class="form-select form-select-sm" id="libraryFilter">
                    <option value="All" selected>All Libraries</option>
                    <?php foreach ($librarySections as $lib): ?>
                        <option value="<?= $lib['SectionID'] ?>"><?= htmlspecialchars($lib['SectionName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Generate button row (full width on mobile, inline on larger screens) -->
        <div class="row mt-3">
            <div class="col-12 col-md-4 offset-md-8">
                <button class="btn btn-primary w-100" id="generateBtn">
                    <i class="fas fa-chart-bar me-1"></i> Generate Analytics
                </button>
            </div>
        </div>
    </div>
</div>

    <!-- KEY METRICS OVERVIEW (New section for better analytics) -->
<!-- KEY METRICS OVERVIEW with IDs -->
<div class="row g-3 mb-4">
    <!-- Total Check-ins -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary bg-gradient text-white rounded-4">
            <div class="card-body">
                <div class="small text-white-50">Total Check-ins</div>
                <h3 class="fw-bold mt-1 mb-0" id="totalCheckinsValue">—</h3>
                <span class="badge bg-white text-primary mt-2">+12.3%</span>
            </div>
        </div>
    </div>
    <!-- Avg. Duration -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-info bg-gradient text-white rounded-4">
            <div class="card-body">
                <div class="small text-white-50">Avg. Duration</div>
                <h3 class="fw-bold mt-1 mb-0" id="avgDurationValue">—</h3>
                <span class="badge bg-white text-info mt-2">+5.2%</span>
            </div>
        </div>
    </div>
    <!-- Active Colleges -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning bg-gradient text-dark rounded-4">
            <div class="card-body">
                <div class="small text-muted">Active Colleges</div>
                <h3 class="fw-bold mt-1 mb-0" id="activeCollegesValue">—</h3>
                <span class="badge bg-dark text-white mt-2">+3</span>
            </div>
        </div>
    </div>
    <!-- Unique Courses -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success bg-gradient text-white rounded-4">
            <div class="card-body">
                <div class="small text-white-50">Unique Courses</div>
                <h3 class="fw-bold mt-1 mb-0" id="uniqueCoursesValue">—</h3>
                <span class="badge bg-white text-success mt-2">+8.1%</span>
            </div>
        </div>
    </div>
	
	<div class="col-md-3">
    <div class="card border-0 shadow-sm bg-secondary bg-gradient text-white rounded-4">
        <div class="card-body">
            <div class="small text-white-50" id="endDateCheckinsLabel">Check‑ins on —</div>
            <h3 class="fw-bold mt-1 mb-0" id="endDateCheckinsValue">—</h3>
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
<?php include '../modalContainer.php'; ?>
<?php include 'LibModals.php'; ?>

<script>
$(function () {
    const UI = {
        tabContent: $("#tabContent"),
        tabs: $("#analyticsTabs .nav-link"),
        startDate: $('#startDate'),
        endDate: $('#endDate'),
        globalClassification: $('#globalClassification'),
        libraryFilter: $('#libraryFilter'),          // new
        generateBtn: $('#generateBtn')
    };

    let currentRequest = null;
    let activeTab = 'users';
    let currentClassification = 'All'; // store the selected classification
	
// ========== View All Modal ==========
let currentViewAllTab = 'users';
let currentViewAllPage = 1;
let totalViewAllPages = 1;

$(document).on('click', '.view-all-btn', function() {
    currentViewAllTab = $(this).data('tab');
    currentViewAllPage = 1;
    loadViewAll(currentViewAllTab, currentViewAllPage);
    $('#dynamicModal').modal('show');
});

function loadViewAll(tab, page) {
    $('#viewAllModalBody').html('<div class="text-center"><div class="spinner-border"></div></div>');
    $('#viewAllModalFooter').empty();

    $.ajax({
        // ... ajax settings ...
        success: function(res) {
            if (res.status !== 'success') {
                $('#viewAllModalBody').html('<div class="alert alert-danger">Failed to load data.</div>');
                return;
            }
            const data = res.data;
            totalViewAllPages = Math.ceil(data.total / 10);
            renderViewAllTable(tab, data.rows);
            renderPagination(totalViewAllPages, currentViewAllPage, tab);
            $('#viewAllModalTitle').text(getModalTitle(tab));
            $('#viewAllModalSubtitle').text(`Showing page ${currentViewAllPage} of ${totalViewAllPages} (Total: ${data.total} records)`);
        }
    });
}

function getModalTitle(tab) {
    const titles = {
        users: 'All Users',
        colleges: 'All Colleges',
        courses: 'All Courses',
        demographics: 'All Check‑in Logs'
    };
    return titles[tab] || 'All Records';
}

function renderViewAllTable(tab, rows) {
    let html = '<div class="table-responsive"><table class="table table-sm table-striped">';
    if (tab === 'users') {
        html += '<thead><tr><th>Name</th><th>Type</th><th>Library</th><th class="text-end">Check-ins</th><th class="text-end">Duration (min)</th><th>Last Check-in</th></tr></thead><tbody>';
        rows.forEach(r => {
            html += `<tr>
                <td>${escapeHtml(r.name)}</td>
                <td>${escapeHtml(r.type)}</td>
                <td>${escapeHtml(r.library)}</td>
                <td class="text-end">${r.checkins}</td>
                <td class="text-end">${Math.round(r.duration)}</td>
                <td>${new Date(r.last_checkin).toLocaleString()}</td>
            </tr>`;
        });
    } else if (tab === 'colleges') {
        html += '<thead><tr><th>College</th><th class="text-end">Unique Users</th><th class="text-end">Total Duration (min)</th><th>Last Check-in</th></tr></thead><tbody>';
        rows.forEach(r => {
            html += `<tr>
                <td>${escapeHtml(r.name)}</td>
                <td class="text-end">${r.checkins}</td>
                <td class="text-end">${Math.round(r.duration)}</td>
                <td>${new Date(r.last_checkin).toLocaleString()}</td>
            </tr>`;
        });
    } else if (tab === 'courses') {
        html += '<thead><tr><th>College</th><th>Course</th><th class="text-end">Unique Users</th><th class="text-end">Total Duration (min)</th><th>Last Check-in</th></tr></thead><tbody>';
        rows.forEach(r => {
            html += `<tr>
                <td>${escapeHtml(r.college)}</td>
                <td>${escapeHtml(r.course)}</td>
                <td class="text-end">${r.checkins}</td>
                <td class="text-end">${Math.round(r.duration)}</td>
                <td>${new Date(r.last_checkin).toLocaleString()}</td>
            </tr>`;
        });
    } else if (tab === 'demographics') {
        html += '<thead><tr><th>Name</th><th>Sex</th><th>Check-in</th><th>Check-out</th><th class="text-end">Duration (min)</th></tr></thead><tbody>';
        rows.forEach(r => {
            html += `<tr>
                <td>${escapeHtml(r.name)}</td>
                <td>${escapeHtml(r.sex)}</td>
                <td>${new Date(r.checkin).toLocaleString()}</td>
                <td>${r.checkout ? new Date(r.checkout).toLocaleString() : '—'}</td>
                <td class="text-end">${Math.round(r.duration)}</td>
            </tr>`;
        });
    }
    html += '</tbody></table></div>';
    $('#dynamicModalBody').html(html);
}

function renderPagination(totalPages, currentPage, tab) {
    let pagHtml = '';
    for (let i = 1; i <= totalPages; i++) {
        pagHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
    }
    $('#dynamicModalFooter').html('<nav><ul class="pagination">' + pagHtml + '</ul></nav>');
    $('#dynamicModalFooter .page-link').click(function(e) {
        e.preventDefault();
        currentViewAllPage = $(this).data('page');
        loadViewAll(tab, currentViewAllPage);
    });
}

// Helper escape function
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe.toString().replace(/[&<>"']/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        if (m === '"') return '&quot;';
        if (m === "'") return '&#039;';
    });
}

// ========== PDF Export ==========
$('#exportPDF').click(function() {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('l', 'mm', 'a4');
    const tabContent = document.getElementById('tabContent');

    html2canvas(tabContent, { scale: 2, logging: false, allowTaint: false, useCORS: true }).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        const imgWidth = 280; // A4 landscape width in mm
        const pageHeight = 210;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        let heightLeft = imgHeight;
        let position = 0;

        pdf.addImage(imgData, 'PNG', 5, position + 5, imgWidth - 10, imgHeight, undefined, 'FAST');
        heightLeft -= pageHeight;

        while (heightLeft >= 0) {
            position = heightLeft - imgHeight;
            pdf.addPage();
            pdf.addImage(imgData, 'PNG', 5, position + 5, imgWidth - 10, imgHeight, undefined, 'FAST');
            heightLeft -= pageHeight;
        }
        pdf.save('Library_Report.pdf');
    });
});
    /* ===========================
       Chart Manager
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

    function showLoading() {
        UI.tabContent.html(`
            <div class="d-flex justify-content-center align-items-center" style="height:300px">
                <div class="spinner-border text-primary"></div>
            </div>
        `);
    }

function loadTab(tab) {
        activeTab = tab;
        if (currentRequest) currentRequest.abort();
        showLoading();

        // Gather all filter values
        let classification = UI.globalClassification.val() || 'All';
        let library = UI.libraryFilter.val() || 'All';

        currentRequest = $.ajax({
            url: "backend/bk_LibraryMenu/bk_libReports.php",
            type: "POST",
            dataType: "json",
            data: {
                tab: tab,
                startDate: UI.startDate.val(),
                endDate: UI.endDate.val(),
                classification: classification,
                library: library                 // send library
            }
        })
        .done(function(response) {
            if (response.status !== 'success') {
                UI.tabContent.html(`<div class="text-danger p-4">${response.message}</div>`);
                return;
            }
            UI.tabContent.html(response.html);
            initializeCharts(response, tab);

            // Update global metric cards
            $('#totalCheckinsValue').text(response.totalVisits.toLocaleString());
            $('#avgDurationValue').text(response.avgDuration + ' hrs');
            $('#activeCollegesValue').text(response.activeColleges);
            $('#uniqueCoursesValue').text(response.uniqueCourses);
			$('#endDateCheckinsLabel').text(`Check‑ins on ${new Date(UI.endDate.val()).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`);
			$('#endDateCheckinsValue').text(response.endDateCheckins.toLocaleString());
            // Update footer timestamp
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true });
            $('.text-muted:contains("Last updated")').html(`<i class="fas fa-sync-alt me-1"></i> Last updated: Today at ${timeStr}`);
        })
        .fail(function() {
            UI.tabContent.html(`<div class="text-danger p-4">Failed to load analytics.</div>`);
        });
    }

    // Chart initialization functions (unchanged)
function initializeCharts(data, tab) {
    if (tab === 'users') {
        renderBar('chartUsersCheckin', 
            extractLabels(data.topCheckins), 
            extractValues(data.topCheckins, 'count'), 
            'Check-ins');
        renderBar('chartUsersDuration', 
            extractLabels(data.topDuration), 
            extractValues(data.topDuration, 'minutes'), 
            'Duration (min)');
    }
    if (tab === 'colleges') {
        renderBar('chartCollegeCheckin', 
            Object.keys(data.top3CollegesCheckin), 
            Object.values(data.top3CollegesCheckin).map(v => v.count), 
            'Check-ins');
        renderBar('chartCollegeDuration', 
            Object.keys(data.top3CollegesDuration), 
            Object.values(data.top3CollegesDuration).map(v => v.minutes), 
            'Duration (min)');
    }
    if (tab === 'courses') {
        Object.keys(data.topCoursesCheckin).forEach(college => {
            const cleanId = college.replace(/[^a-zA-Z0-9]/g, '');
            const courses = data.topCoursesCheckin[college];
            renderBar('chartCourseCheckin_' + cleanId, 
                Object.keys(courses), 
                Object.values(courses).map(v => v.count), 
                'Check-ins');
            if (data.topCoursesDuration[college]) {
                const durCourses = data.topCoursesDuration[college];
                renderBar('chartCourseDuration_' + cleanId, 
                    Object.keys(durCourses), 
                    Object.values(durCourses).map(v => v.minutes), 
                    'Duration (min)');
            }
        });
    }
    if (tab === 'demographics') {
        renderPie('chartSexCheckin', 
            Object.keys(data.sexDistribution), 
            Object.values(data.sexDistribution));
    }
}

    function renderBar(id, labels, data, label) {
        ChartManager.create(id, {
            type: 'bar',
            data: { labels, datasets: [{ label, data, backgroundColor: 'rgba(54,162,235,0.7)', borderRadius: 8, maxBarThickness: 40 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    function renderPie(id, labels, data) {
        ChartManager.create(id, {
            type: 'doughnut',
            data: { labels, datasets: [{ data, backgroundColor: ['#4F46E5', '#06B6D4', '#F59E0B', '#EF4444'] }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%' }
        });
    }

function extractLabels(groupedData) {
    const labels = [];
    Object.values(groupedData).forEach(group => {
        Object.values(group).forEach(item => {
            labels.push(item.name);
        });
    });
    return labels;
}

function extractValues(groupedData, valueKey) {
    const values = [];
    Object.values(groupedData).forEach(group => {
        Object.values(group).forEach(item => {
            values.push(item[valueKey]);
        });
    });
    return values;
}

    // Event handlers
       UI.tabs.on('click', function (e) {
        e.preventDefault();
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

    // Reload when any filter changes (dates, classification, library)
    UI.startDate.add(UI.endDate).add(UI.globalClassification).add(UI.libraryFilter).on('change', function () {
        if (UI.startDate.val() && UI.endDate.val()) {
            loadTab(activeTab);
        }
    });

    // Set default dates if empty (last 7 days)
    if (!UI.startDate.val()) {
        let today = new Date();
        let lastWeek = new Date(today);
        lastWeek.setDate(today.getDate() - 7);
        UI.startDate.val(lastWeek.toISOString().split('T')[0]);
        UI.endDate.val(today.toISOString().split('T')[0]);
    }

    // Initial load
    loadTab('users');
});
</script>