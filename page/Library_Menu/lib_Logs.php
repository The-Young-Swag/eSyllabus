<div class="container-fluid mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h4 class="fw-bold text-dark d-flex align-items-center gap-2">
            <i class="fas fa-tachometer-alt text-primary"></i> Library Attendance Logs
        </h4>
        <small class="text-muted">Quick overview of today's library attendance</small>
    </div>

    <!-- KPI Counters -->
    <div class="row g-4 mb-4 text-center">
        <div class="col-12 col-md-3">
            <div class="card h-100 shadow-sm border-start border-4 border-success">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <h6 class="card-title text-muted mb-2">Total Students</h6>
                    <h2 class="fw-bold text-success mb-0" id="kpiTotalStudents">--</h2>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card h-100 shadow-sm border-start border-4 border-primary">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <h6 class="card-title text-muted mb-2">Total Colleges</h6>
                    <h2 class="fw-bold text-primary mb-0" id="kpiTotalColleges">--</h2>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card h-100 shadow-sm border-start border-4 border-warning">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <h6 class="card-title text-muted mb-2">Total Courses</h6>
                    <h2 class="fw-bold text-warning mb-0" id="kpiTotalCourses">--</h2>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card h-100 shadow-sm border-start border-4 border-danger">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <h6 class="card-title text-muted mb-2">Current Time</h6>
                    <h2 class="fw-bold text-danger mb-0" id="kpiCurrentTime">--</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Log Input Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-user-plus me-2"></i> Log Student Attendance
        </div>
        <div class="card-body">
            <form id="logForm" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Student Number</label>
                    <input type="text" class="form-control" id="inputStudentNumber" placeholder="Enter student number">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" id="inputName" placeholder="Auto-filled" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">College</label>
                    <input type="text" class="form-control" id="inputCollege" placeholder="Auto-filled" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Course</label>
                    <input type="text" class="form-control" id="inputCourse" placeholder="Auto-filled" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Library</label>
  <select class="form-select" id="inputLibrary"></select>

                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-50">Log Attendance</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-scroll"></i>
                <span>Library Attendance Logs (Today)</span>
            </div>
        </div>

        <!-- Filters -->
        <div class="card-body border-bottom py-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label mb-1 small">Library</label>
<select class="form-select" id="inputLibrary"></select>
                </div>
                <div class="col-md-4">
                    <label class="form-label mb-1 small">Search Student Number</label>
                    <input type="text" class="form-control form-control-sm" placeholder="Type student number">
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student Number</th>
                            <th>Name</th>
                            <th>College</th>
                            <th>Course</th>
                            <th>Library</th>
                            <th>Check-In Timestamp</th>
                            <th>Check-Out Timestamp</th>
                            <th>Status / Actions</th>
                        </tr>
                    </thead>
                    <tbody id="logsTableBody"></tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav class="mt-2">
                <ul class="pagination pagination-sm justify-content-center mb-0"></ul>
            </nav>
        </div>

        <!-- Footer -->
        <div class="card-footer bg-light d-flex justify-content-between small text-muted">
            <span><i class="fas fa-calendar-day me-1"></i> Showing logs for today</span>
            <span id="totalRecords">0 records</span>
        </div>
    </div>

    <!-- Confirm Attendance Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirm Attendance</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p><strong>Student Number:</strong> <span id="cStudentNumber"></span></p>
            <p><strong>Name:</strong> <span id="cName"></span></p>
            <p><strong>College:</strong> <span id="cCollege"></span></p>
            <p><strong>Course:</strong> <span id="cCourse"></span></p>
            <p><strong>Library:</strong> <span id="cLibrary"></span></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Edit</button>
            <button type="button" class="btn btn-primary" id="confirmSaveBtn">Confirm</button>
          </div>
        </div>
      </div>
    </div>

</div>

<script>
/* ===========================
   Library Attendance Controller
   =========================== */
(function () {

    if (!window.LibraryPage) {
        window.LibraryPage = {
            intervals: [],
            lastTimestamp: null,
            kpiState: {
                students: new Set(),
                colleges: new Set(),
                courses: new Set()
            }
        };
    }

    const Page = window.LibraryPage;

    // ---------------------------
    // Elements
    // ---------------------------
    let logsTableBody,
        inputStudentNumber,
        inputName,
        inputCollege,
        inputCourse,
        inputLibrary,
        confirmModal,
        pendingLog = null;

    // ---------------------------
    // Helper: Fetch JSON safely
    // ---------------------------
    async function fetchJSON(url, params = {}) {
        try {
            const res = await fetch(url, {
                method: 'POST',
                body: new URLSearchParams(params)
            });

            const data = await res.json();

            if (!res.ok || data.success === false) {
                console.error('Fetch error:', data.message || 'Unknown error');
                return null;
            }

            return data;
        } catch (err) {
            console.error('Fetch failed:', err);
            return null;
        }
    }

    // ---------------------------
    // Cleanup intervals on page reload
    // ---------------------------
    function cleanup() {
        Page.intervals.forEach(clearInterval);
        Page.intervals = [];
    }

    // ---------------------------
    // KPI Updates
    // ---------------------------
    function refreshKPIs() {
        document.getElementById('kpiTotalStudents').innerText = Page.kpiState.students.size;
        document.getElementById('kpiTotalColleges').innerText = Page.kpiState.colleges.size;
        document.getElementById('kpiTotalCourses').innerText  = Page.kpiState.courses.size;
        document.getElementById('kpiCurrentTime').innerText  = new Date().toLocaleTimeString();
    }

    function incrementKPIs(log) {
        Page.kpiState.students.add(log.student_number);
        Page.kpiState.colleges.add(log.college);
        Page.kpiState.courses.add(log.course);
        refreshKPIs();
    }

    // ---------------------------
    // Load Libraries for <select>
    // ---------------------------
    async function loadLibraries() {
        const libraries = await fetchJSON('backend/bk_Library_Menu/bk_libLogs.php', { request: 'getLibraries' });
        if (!libraries) return;

        inputLibrary.innerHTML = '';
        libraries.forEach(lib => {
            const opt = document.createElement('option');
            opt.value = lib.SectionName;
            opt.textContent = lib.SectionName;
            inputLibrary.appendChild(opt);
        });
    }

    // ---------------------------
    // Append Log Row to Table
    // ---------------------------
    function appendLogRow(log, toTop = true) {
        const tr = document.createElement('tr');
        const isCheckedOut = !!log.checkout_time;

        tr.innerHTML = `
            <td>${log.student_number}</td>
            <td>${log.name}</td>
            <td>${log.college}</td>
            <td>${log.course}</td>
            <td>${log.library}</td>
            <td>${new Date(log.checkin_time).toLocaleString()}</td>
            <td>${isCheckedOut ? new Date(log.checkout_time).toLocaleString() : "<span class='text-muted'>—</span>"}</td>
            <td>${isCheckedOut ? '<span class="text-success">Checked Out</span>' : '<button class="btn btn-sm btn-warning btn-checkout">Check-Out</button>'}</td>
        `;

        toTop ? logsTableBody.prepend(tr) : logsTableBody.appendChild(tr);

        // Check-out button
        if (!isCheckedOut) {
            tr.querySelector('.btn-checkout').addEventListener('click', async () => {
                if (!confirm(`Check out ${log.name}?`)) return;
                const result = await fetchJSON('backend/bk_Library_Menu/bk_libLogs.php', {
                    request: 'checkoutLog',
                    student_number: log.student_number,
                    library: log.library
                });

                if (!result) return;

                log.checkout_time = new Date().toISOString();
                tr.cells[6].textContent = new Date(log.checkout_time).toLocaleString();
                tr.cells[7].innerHTML = '<span class="text-success">Checked Out</span>';
            });
        }

        // Update last timestamp
        const ts = new Date(log.checkin_time).getTime();
        if (!Page.lastTimestamp || ts > Page.lastTimestamp) {
            Page.lastTimestamp = ts;
            sessionStorage.setItem('lastTimestamp', Page.lastTimestamp);
        }

        document.getElementById('totalRecords').innerText = logsTableBody.children.length + ' records';
    }

    // ---------------------------
    // Fetch Student Info
    // ---------------------------
    async function getStudentInfo(studentNumber) {
        const paths = [
            'API_requests/students.json',
            '../API_requests/students.json',
            '/eSyllabus/API_requests/students.json'
        ];

        for (const path of paths) {
            try {
                const res = await fetch(path);
                if (res.ok) {
                    const data = await res.json();
                    return data[studentNumber] || null;
                }
            } catch {}
        }
        return null;
    }

    // ---------------------------
    // Load All Logs for Today
    // ---------------------------
    async function loadFullTable() {
        const logs = await fetchJSON('backend/bk_Library_Menu/bk_libLogs.php', { request: 'getNewLogs' });
        if (!logs) return;

        logsTableBody.innerHTML = '';
        Page.kpiState.students.clear();
        Page.kpiState.colleges.clear();
        Page.kpiState.courses.clear();

        logs.forEach(log => {
            appendLogRow(log, false);
            incrementKPIs(log);
        });

        sessionStorage.setItem('logsTableHTML', logsTableBody.innerHTML);
    }

    // ---------------------------
    // Fetch Only New Logs
    // ---------------------------
    async function fetchNewLogs() {
        const logs = await fetchJSON('backend/bk_Library_Menu/bk_libLogs.php', {
            request: 'getNewLogs',
            after: Page.lastTimestamp
        });

        if (!logs) return;

        logs.forEach(log => {
            appendLogRow(log);
            incrementKPIs(log);
        });

        sessionStorage.setItem('logsTableHTML', logsTableBody.innerHTML);
    }

    // ---------------------------
    // Bind Form & Input Events
    // ---------------------------
    function bindEvents() {

        // Student Number Autofill
        inputStudentNumber.addEventListener('input', async e => {
            const student = await getStudentInfo(e.target.value.trim());
            if (student) {
                inputName.value = student.name;
                inputCollege.value = student.college;
                inputCourse.value = student.course;
            } else {
                inputName.value = '';
                inputCollege.value = '';
                inputCourse.value = '';
            }
        });

        // Log Form Submit -> Show Confirm Modal
        document.getElementById('logForm').addEventListener('submit', async e => {
            e.preventDefault();
            const student_number = inputStudentNumber.value.trim();
            if (!student_number) return alert("Enter student number");

            const student = await getStudentInfo(student_number);
            if (!student) return alert("Student not found");

            pendingLog = { student_number, library: inputLibrary.value, ...student };

            // Fill Confirm Modal
            document.getElementById('cStudentNumber').innerText = pendingLog.student_number;
            document.getElementById('cName').innerText = pendingLog.name;
            document.getElementById('cCollege').innerText = pendingLog.college;
            document.getElementById('cCourse').innerText = pendingLog.course;
            document.getElementById('cLibrary').innerText = pendingLog.library;

            confirmModal.show();
        });

        // Confirm Save -> Add Log
        document.getElementById('confirmSaveBtn').addEventListener('click', async () => {
            if (!pendingLog) return;

            const result = await fetchJSON('backend/bk_Library_Menu/bk_libLogs.php', {
                request: 'addLog',
                student_number: pendingLog.student_number,
                library: pendingLog.library
            });

            if (!result) return alert("Failed to log attendance");

            appendLogRow({ ...pendingLog, checkin_time: new Date().toISOString(), checkout_time: null });
            incrementKPIs(pendingLog);

            confirmModal.hide();

            // Reset form
            inputStudentNumber.value = '';
            inputName.value = '';
            inputCollege.value = '';
            inputCourse.value = '';
            inputStudentNumber.focus();
        });
    }

    // ---------------------------
    // Init Page
    // ---------------------------
    async function init() {
        cleanup();

        // Elements
        logsTableBody = document.getElementById('logsTableBody');
        inputStudentNumber = document.getElementById('inputStudentNumber');
        inputName = document.getElementById('inputName');
        inputCollege = document.getElementById('inputCollege');
        inputCourse = document.getElementById('inputCourse');
        inputLibrary = document.getElementById('inputLibrary');
        confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));

        // Load data
        await loadLibraries();
        await loadFullTable();

        // Intervals
        Page.intervals.push(setInterval(fetchNewLogs, 5000));
        Page.intervals.push(setInterval(() => {
            document.getElementById('kpiCurrentTime').innerText = new Date().toLocaleTimeString();
        }, 1000));

        bindEvents();
    }

    init();

})();

</script>
