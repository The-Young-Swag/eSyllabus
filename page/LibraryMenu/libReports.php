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

    <!-- HEADER SECTION -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">

            <div>
                <h4 class="fw-bold mb-1">Library Analytics Dashboard</h4>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge px-3 py-2 rounded-4" style="background: linear-gradient(90deg,#f3f4f6,#ffffff); color:#374151;">
                        <i class="fas fa-calendar-alt me-1 text-muted"></i> Last 30 Days
                    </span>
                    <span class="badge px-3 py-2 rounded-4" style="background: linear-gradient(90deg,#f3f4f6,#ffffff); color:#374151;">
                        <i class="fas fa-users me-1 text-muted"></i> 1,234 Visitors
                    </span>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-outline-secondary btn-sm rounded-4 shadow-sm" id="refreshBtn">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
            <button class="btn btn-danger btn-sm rounded-4 shadow-sm" id="exportPDF">
                <i class="fas fa-file-pdf me-1"></i> Export Report
            </button>
        </div>
    </div>

    <!-- FILTER SECTION -->
<div class="card border-0 shadow-sm mb-4 rounded-4">
    <div class="card-body">

        <!-- FILTER ROW -->
        <div class="row g-3 align-items-end">

            <!-- Start Date -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Start Date</label>
                <div class="input-group input-group-sm shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(90deg, #e6f4ea, #ffffff);">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-calendar text-muted"></i>
                    </span>
                    <input type="date" class="form-control border-start-0 ps-0" id="startDate" style="border-radius:0; background:transparent;">
                </div>
            </div>

            <!-- End Date -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">End Date</label>
                <div class="input-group input-group-sm shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(90deg, #e6f4ea, #ffffff);">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-calendar-check text-muted"></i>
                    </span>
                    <input type="date" class="form-control border-start-0 ps-0" id="endDate" style="border-radius:0; background:transparent;">
                </div>
            </div>

            <!-- User Classification -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">User Classification</label>
                <select class="form-select form-select-sm shadow-sm rounded-4" id="globalClassification"
                        style="background: linear-gradient(90deg, #f3f9f7, #ffffff); border:none;">
                    <option value="All" selected>All</option>
                    <option value="Student">Student</option>
                    <option value="Employee">Employee</option>
                    <option value="Guest">Guest</option>
                </select>
            </div>

            <!-- Library Filter -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Library</label>
                <select class="form-select form-select-sm shadow-sm rounded-4" id="libraryFilter"
                        style="background: linear-gradient(90deg, #f3f9f7, #ffffff); border:none;">
                    <option value="All" selected>All Libraries</option>
                    <?php foreach ($librarySections as $lib): ?>
                        <option value="<?= $lib['SectionID'] ?>"><?= htmlspecialchars($lib['SectionName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

        <!-- GENERATE BUTTON ROW -->
        <div class="row mt-4">
            <div class="col-12 col-md-4 offset-md-8">
                <button class="btn w-100 fw-semibold shadow-sm"
                        style="background: linear-gradient(90deg, #047857, #10b981); color:white; border-radius:0.5rem; font-size:1rem;"
                        id="generateBtn">
                    <i class="fas fa-chart-bar me-1"></i> Generate Analytics
                </button>
            </div>
        </div>

    </div>
</div>

    <!-- KPI CARDS -->
    <div class="row g-3 mb-4">
        <!-- Total Check-ins -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #10b981, #047857);">
                <div class="card-body text-white">
                    <small class="fw-semibold text-white-50">Total Check-ins</small>
                    <h3 class="fw-bold mt-1 mb-0" id="totalCheckinsValue">—</h3>
                </div>
            </div>
        </div>

        <!-- Avg Duration -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                <div class="card-body text-white">
                    <small class="fw-semibold text-white-50">Avg. Duration</small>
                    <h3 class="fw-bold mt-1 mb-0" id="avgDurationValue">—</h3>
                </div>
            </div>
        </div>

        <!-- Active Colleges -->

        <!-- Unique Courses -->


        <!-- End Date Check-ins -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #6b7280, #374151);">
                <div class="card-body text-white">
                    <small class="fw-semibold text-white-50" id="endDateCheckinsLabel">Check ins on —</small>
                    <h3 class="fw-bold mt-1 mb-0" id="endDateCheckinsValue">—</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB SECTION -->
<!-- TAB SECTION -->
<div class="mb-4">
    <ul class="nav nav-tabs border-0 rounded-3 overflow-hidden shadow-sm d-flex flex-nowrap p-2" 
        id="analyticsTabs" role="tablist" 
        style="background:#f0f4fa; border:1px solid #d6dff0 !important; gap:6px;">
        
        <!-- Users Tab -->
        <li class="nav-item flex-fill" role="presentation">
            <button class="nav-link active w-100 d-flex align-items-center justify-content-center gap-2 rounded-2 border-0 fw-medium px-3 py-2" 
                    data-tab="users" role="tab" aria-selected="true"
                    style="background:linear-gradient(135deg,#3a6cf4,#6a3de8); color:#fff; box-shadow:0 3px 12px rgba(58,108,244,0.28); font-size:0.875rem;">
                <i class="bi bi-people-fill"></i> Users
            </button>
        </li>

        <!-- Colleges Tab -->
        <li class="nav-item flex-fill" role="presentation">
            <button class="nav-link w-100 d-flex align-items-center justify-content-center gap-2 rounded-2 border-0 fw-medium px-3 py-2" 
                    data-tab="colleges" role="tab" aria-selected="false"
                    style="background:transparent; color:#5a6a8a; font-size:0.875rem;">
                <i class="bi bi-building-fill"></i> Colleges
            </button>
        </li>

        <!-- Courses Tab -->
        <li class="nav-item flex-fill" role="presentation">
            <button class="nav-link w-100 d-flex align-items-center justify-content-center gap-2 rounded-2 border-0 fw-medium px-3 py-2" 
                    data-tab="courses" role="tab" aria-selected="false"
                    style="background:transparent; color:#5a6a8a; font-size:0.875rem;">
                <i class="bi bi-journal-bookmark-fill"></i> Courses
            </button>
        </li>

        <!-- Demographics Tab -->
        <li class="nav-item flex-fill" role="presentation">
            <button class="nav-link w-100 d-flex align-items-center justify-content-center gap-2 rounded-2 border-0 fw-medium px-3 py-2" 
                    data-tab="demographics" role="tab" aria-selected="false"
                    style="background:transparent; color:#5a6a8a; font-size:0.875rem;">
                <i class="bi bi-bar-chart-fill"></i> Demographics
            </button>
        </li>

    </ul>
</div>

    <div id="tabContent" class="tab-content">
        <div class="text-center p-4">Select a tab to view content...</div>
    </div>

    <!-- FOOTER -->
    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
        <small class="text-muted">
            <i class="fas fa-sync-alt me-1"></i> Last updated: Today at 10:30 AM
        </small>
        <div class="d-flex gap-3 flex-wrap">
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
    $('#dynamicModalBody').html('<div class="text-center"><div class="spinner-border"></div></div>');
    $('#dynamicModalFooter').empty();

    $.ajax({
        url: "backend/bk_LibraryMenu/bk_libReports.php",
        type: "POST",
        data: {
            action: 'viewAll',
            tab: tab,
            page: page,
            startDate: UI.startDate.val(),
            endDate: UI.endDate.val(),
            classification: UI.globalClassification.val(),
            library: UI.libraryFilter.val()
        },
        dataType: "json",
        success: function(res) {
            if (res.status !== 'success') {
                $('#dynamicModalBody').html('<div class="alert alert-danger">Failed to load data.</div>');
                return;
            }
            const data = res.data;
            totalViewAllPages = Math.ceil(data.total / 10);
            renderViewAllTable(tab, data.rows);
            renderPagination(totalViewAllPages, currentViewAllPage, tab);
            $('#dynamicModalTitle').text(getModalTitle(tab));
            $('#dynamicModalSubtitle').text(`Showing page ${currentViewAllPage} of ${totalViewAllPages} (Total: ${data.total} records)`);
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

			$('#endDateCheckinsLabel').text(`Check ins on ${new Date(UI.endDate.val()).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`);
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
        // ----- Top Users by Check ins (chartUsersCheckin) – force y axis to start at 0 -----
        ChartManager.create('chartUsersCheckin', {
            type: 'bar',
            data: {
                labels: extractLabels(data.topCheckins),
                datasets: [{
                    label: 'Check‑ins',
                    data: extractValues(data.topCheckins, 'count'),
                    backgroundColor: 'rgba(54,162,235,0.7)',
                    borderRadius: 8,
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        min: 0
                    }
                }
            }
        });

        // ----- Top Users by Duration (chartUsersDuration) – use standard renderBar -----
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
// Tab click event with active highlight management
UI.tabs.on('click', function (e) {
    e.preventDefault();
    
    // Remove active class and reset styles for all tabs
    UI.tabs.each(function() {
        $(this).removeClass('active')
               .css({
                   background: 'transparent',
                   color: '#5a6a8a',
                   boxShadow: 'none'
               });
    });

    // Add active class and apply highlight styles to clicked tab
    $(this).addClass('active')
           .css({
               background: 'linear-gradient(135deg,#3a6cf4,#6a3de8)',
               color: '#fff',
               boxShadow: '0 3px 12px rgba(58,108,244,0.28)'
           });

    // Load the selected tab content
    activeTab = $(this).data('tab');
    loadTab(activeTab);
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