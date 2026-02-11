<div class="container-fluid mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h4 class="fw-bold text-dark d-flex align-items-center gap-2">
            <i class="fas fa-tachometer-alt text-primary"></i> Library Attendance Logs
        </h4>
        <small class="text-muted">Quick overview of today's library attendance</small>
    </div>

    <!-- KPI Counters - Symmetrical -->
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

<!-- Current Time KPI -->
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
                <select class="form-select" id="inputLibrary">
                    <option value="Main Library">Main Library</option>
                    <option value="Science Library">Science Library</option>
                    <option value="Engineering Library">Engineering Library</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">Log Attendance</button>
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
       <!-- <button class="btn btn-danger btn-sm d-flex align-items-center gap-1">
            <i class="fas fa-file-pdf"></i> Download PDF
        </button> -->
    </div>

    <!-- Filters -->
    <div class="card-body border-bottom py-3">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label mb-1 small">Library</label>
                <select class="form-select form-select-sm">
                    <option value="all">All Libraries</option>
                    <option value="main">Main Library</option>
                    <option value="science">Science Library</option>
                    <option value="engineering">Engineering Library</option>
                </select>
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
                    </tr>
                </thead>
                <tbody id="logsTableBody">
                   <!-- <tr>
                        <td>2021-00123</td>
                        <td>John Doe</td>
                        <td>Science</td>
                        <td>Biology</td>
                        <td>Main Library</td>
                        <td>08:12:34</td>
                        <td>10:05:11</td>
                    </tr>
                    <tr>
                        <td>2022-00456</td>
                        <td>Jane Smith</td>
                        <td>Engineering</td>
                        <td>Computer</td>
                        <td>Engineering Library</td>
                        <td>09:01:09</td>
                        <td><span class="text-muted">—</span></td>
                    </tr> -->
                </tbody>
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
        <span>2 records</span>
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



<script>
/* ==========================================================
   Library Attendance Page Controller (SPA-SAFE)
   ========================================================== */

(function () {

    /* -------------------------
       GLOBAL NAMESPACE
    ------------------------- */
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

    /* -------------------------
       DOM REFERENCES (rebound on init)
    ------------------------- */
    let logsTableBody,
        inputStudentNumber,
        inputName,
        inputCollege,
        inputCourse,
        inputLibrary,
        confirmModal;

    let pendingLog = null;

    /* -------------------------
       CLEANUP (important!)
    ------------------------- */
    function cleanup() {
        Page.intervals.forEach(clearInterval);
        Page.intervals = [];
    }

    /* -------------------------
       KPI HELPERS
    ------------------------- */
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

    /* -------------------------
       TABLE RENDERING
    ------------------------- */
    function appendLogRow(log, toTop = true) {
        const tr = document.createElement('tr');

        tr.innerHTML = `
            <td>${log.student_number}</td>
            <td>${log.name}</td>
            <td>${log.college}</td>
            <td>${log.course}</td>
            <td>${log.library}</td>
			<td>${new Date(log.checkin_time).toLocaleString()}</td>
			<td>${log.checkout_time ? new Date(log.checkout_time).toLocaleString() : "<span class='text-muted'>—</span>"}</td>

        `;

        toTop ? logsTableBody.prepend(tr) : logsTableBody.appendChild(tr);

        const ts = new Date(log.checkin_time).getTime();
        if (!Page.lastTimestamp || ts > Page.lastTimestamp) {
            Page.lastTimestamp = ts;
            sessionStorage.setItem('lastTimestamp', Page.lastTimestamp);
        }
    }

    /* -------------------------
       STUDENT LOOKUP
    ------------------------- */
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

    /* -------------------------
       LOADERS
    ------------------------- */
    async function loadFullTable() {
        const res = await fetch('backend/bk_Library_Menu/bk_libLogs.php', {
            method: 'POST',
            body: new URLSearchParams({ request: 'getNewLogs' })
        });

        const logs = await res.json();

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

    async function fetchNewLogs() {
        const res = await fetch('backend/bk_Library_Menu/bk_libLogs.php', {
            method: 'POST',
            body: new URLSearchParams({
                request: 'getNewLogs',
                after: Page.lastTimestamp
            })
        });

        const logs = await res.json();
        logs.forEach(log => {
            appendLogRow(log);
            incrementKPIs(log);
        });

        sessionStorage.setItem('logsTableHTML', logsTableBody.innerHTML);
    }

    /* -------------------------
       EVENTS
    ------------------------- */
    function bindEvents() {

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

        document.getElementById('logForm').addEventListener('submit', async e => {
            e.preventDefault();

            const student_number = inputStudentNumber.value.trim();
            if (!student_number) return alert("Enter student number");

            const student = await getStudentInfo(student_number);
            if (!student) return alert("Student not found");

            pendingLog = {
                student_number,
                library: inputLibrary.value,
                ...student
            };

            document.getElementById('cStudentNumber').innerText = student_number;
            document.getElementById('cName').innerText = student.name;
            document.getElementById('cCollege').innerText = student.college;
            document.getElementById('cCourse').innerText = student.course;
            document.getElementById('cLibrary').innerText = inputLibrary.value;

            confirmModal.show();
        });

        document.getElementById('confirmSaveBtn').addEventListener('click', async () => {
            if (!pendingLog) return;

            const res = await fetch('backend/bk_Library_Menu/bk_libLogs.php', {
                method: 'POST',
                body: new URLSearchParams({
                    request: 'addLog',
                    student_number: pendingLog.student_number,
                    library: pendingLog.library
                })
            });

            const result = await res.json();
            if (!result.success) return alert(result.message);

            appendLogRow({
                ...pendingLog,
                checkin_time: new Date().toISOString(),
                checkout_time: null
            });

            incrementKPIs(pendingLog);

            confirmModal.hide();
            inputStudentNumber.value = '';
            inputName.value = '';
            inputCollege.value = '';
            inputCourse.value = '';
            inputStudentNumber.focus();
        });
    }

    /* -------------------------
       INIT
    ------------------------- */
    async function init() {
        cleanup();

        logsTableBody = document.getElementById('logsTableBody');
        inputStudentNumber = document.getElementById('inputStudentNumber');
        inputName = document.getElementById('inputName');
        inputCollege = document.getElementById('inputCollege');
        inputCourse = document.getElementById('inputCourse');
        inputLibrary = document.getElementById('inputLibrary');
        confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));

        Page.lastTimestamp = sessionStorage.getItem('lastTimestamp');

        const saved = sessionStorage.getItem('logsTableHTML');
        if (saved) {
            logsTableBody.innerHTML = saved;
            await loadFullTable();
        } else {
            await loadFullTable();
        }

        Page.intervals.push(setInterval(fetchNewLogs, 5000));
        Page.intervals.push(setInterval(() => {
            document.getElementById('kpiCurrentTime').innerText = new Date().toLocaleTimeString();
        }, 1000));

        bindEvents();
    }

    init();

})();
</script>
