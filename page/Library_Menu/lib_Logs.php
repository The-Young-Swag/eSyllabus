<div class="container-fluid mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h4 class="fw-bold text-dark d-flex align-items-center gap-2">
            <i class="fas fa-tachometer-alt text-primary"></i> Library Attendance Logs
        </h4>
        <small class="text-muted">Quick overview of today's library attendance</small>
    </div>

    <!-- KPI Counters (Compact) -->
    <div class="row g-2 mb-3 text-center">

        <div class="col-6 col-md-2">
            <div class="card shadow-sm border-start border-3 border-success py-2">
                <small class="text-muted d-block">Total Check-Ins</small>
                <div class="fw-bold text-success" id="kpiTotalCheckins">0</div>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card shadow-sm border-start border-3 border-primary py-2">
                <small class="text-muted d-block">Currently Inside</small>
                <div class="fw-bold text-primary" id="kpiActiveStudents">0</div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-start border-3 border-warning py-2">
                <small class="text-muted d-block">Top 3 Colleges</small>
                <div class="fw-bold text-warning small" id="kpiTopColleges">—</div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-start border-3 border-info py-2">
                <small class="text-muted d-block">Top 3 Courses</small>
                <div class="fw-bold text-info small" id="kpiTopCourses">—</div>
            </div>
        </div>

        <div class="col-12 col-md-2">
            <div class="card shadow-sm border-start border-3 border-danger py-2">
                <small class="text-muted d-block">Date & Time</small>
                <div class="fw-bold text-danger small" id="kpiCurrentTime"></div>
            </div>
        </div>

    </div>


    <!-- Student Log Input Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-user-plus me-2"></i> Log Student Attendance
        </div>
        <div class="card-body">
            <form id="logForm" class="row g-3 align-items-end">

                <div class="col-md-2">
                    <label class="form-label">Student Number</label>
                    <input type="text" class="form-control" id="inputStudentNumber" placeholder="Enter student number">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" id="inputName" readonly>
                </div>

                <div class="col-md-2">
                    <label class="form-label">College</label>
                    <input type="text" class="form-control" id="inputCollege" readonly>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Course</label>
                    <input type="text" class="form-control" id="inputCourse" readonly>
                </div>

                <!-- Make Library wider -->
                <div class="col-md-3">
                    <label class="form-label">Library</label>
                    <select class="form-select" id="inputLibrary"></select>
                </div>

                <!-- Button smaller column -->
                <div class="col-md-1">
                    <button type="submit" class="btn btn-success w-100">
                        Log
                    </button>
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
        <div class="card-body border-bottom py-2">
            <div class="row g-2">

                <div class="col-md-4">
                    <label class="form-label small mb-1">Library</label>
                    <select class="form-select form-select-sm" id="filterLibrary">
                        <option value="">All Libraries</option>
                    </select>
                </div>

<div class="col-md-4">
    <label class="form-label small mb-1">Search Student Number</label>
    <div class="input-group input-group-sm">
        <input type="text"
            id="filterSearch"
            class="form-control"
            placeholder="Type student number">
        <button class="btn btn-primary" id="btnSearchStudent">
            Search
        </button>
    </div>
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
<!-- Check-Out Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Check-Out</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Student: <strong id="coStudentName"></strong></p>
                <p>Library: <strong id="coLibrary"></strong></p>
                <div class="mb-3">
                    <label for="coStudentNumber" class="form-label">Enter Your Student Number to Confirm</label>
                    <input type="text" class="form-control" id="coStudentNumber" placeholder="Student Number">
                </div>
                <div id="coError" class="text-danger small" style="display:none;">Student number does not match.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmCheckoutBtn">Check-Out</button>
            </div>
        </div>
    </div>
</div>


<script>
    (function() {

        const API_URL = 'backend/bk_Library_Menu/bk_libLogs.php';

const State = {
    logs: [],
    currentPage: 1,
    rowsPerPage: 10,
    filters: {
        library: '',
        search: ''
    }
};


        const el = {
            tableBody: document.getElementById('logsTableBody'),
            totalRecords: document.getElementById('totalRecords'),

            inputStudentNumber: document.getElementById('inputStudentNumber'),
            inputName: document.getElementById('inputName'),
            inputCollege: document.getElementById('inputCollege'),
            inputCourse: document.getElementById('inputCourse'),
            inputLibrary: document.getElementById('inputLibrary'),

            filterLibrary: document.getElementById('filterLibrary'),
            filterSearch: document.getElementById('filterSearch'),

            kpiTotalCheckins: document.getElementById('kpiTotalCheckins'),
            kpiActiveStudents: document.getElementById('kpiActiveStudents'),
            kpiTopColleges: document.getElementById('kpiTopColleges'),
            kpiTopCourses: document.getElementById('kpiTopCourses'),
            kpiCurrentTime: document.getElementById('kpiCurrentTime')
        };

        const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
        const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));

        let pendingLog = null;
        let pendingCheckout = null;

        /* ================= API ================= */

        async function api(request, data = {}) {
            const res = await fetch(API_URL, {
                method: 'POST',
                body: new URLSearchParams({
                    request,
                    ...data
                })
            });
            return res.json();
        }

        /* ================= INIT ================= */

        async function init() {
            await loadLibraries();
            await loadTodayLogs();
            bindEvents();
            startClock();
            scheduleMidnightReset();
        }

        /* ================= LIBRARIES ================= */

async function loadLibraries() {

    const libs = await api('getLibraries');
    if (!libs) return;

    el.inputLibrary.innerHTML = '';
    el.filterLibrary.innerHTML = '';

    // 🔥 Add COMBINED option first
    const combinedValue = "FILIPINIANA_COMBINED";

    el.filterLibrary.innerHTML += `
        <option value="${combinedValue}">
            Filipiniana (1st & 2nd Floor)
        </option>
    `;

    libs.forEach(lib => {

        const option = `
            <option value="${lib.SectionName}">
                ${lib.SectionName}
            </option>
        `;

        el.inputLibrary.innerHTML += option;
        el.filterLibrary.innerHTML += option;
    });

    // 🔥 Default filter = combined
    el.filterLibrary.value = combinedValue;
    State.filters.library = combinedValue;
}



        /* ================= LOAD TODAY ================= */

        async function loadTodayLogs() {
            const logs = await api('getNewLogs');
            if (!logs) return;

            State.logs = logs;
            renderTable();
            updateKPIs();
            sessionStorage.setItem('todayLogs', JSON.stringify(State.logs));
        }
		
		        /* ================= RENDER PAGINATION ================= */
		function renderPagination(totalRecords) {

    const totalPages = Math.ceil(totalRecords / State.rowsPerPage);
    const pagination = document.querySelector('.pagination');

    pagination.innerHTML = '';

    if (totalPages <= 1) return;

    for (let i = 1; i <= totalPages; i++) {

        pagination.innerHTML += `
            <li class="page-item ${i === State.currentPage ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>
        `;
    }

    pagination.querySelectorAll('.page-link').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            State.currentPage = parseInt(btn.dataset.page);
            renderTable();
        });
    });
}


        /* ================= RENDER ================= */

function renderTable() {

    const filtered = getFilteredLogs();

    const start = (State.currentPage - 1) * State.rowsPerPage;
    const end = start + State.rowsPerPage;

    const paginated = filtered.slice(start, end);

    el.tableBody.innerHTML = paginated
        .map(log => buildRowHTML(log))
        .join('');

    renderPagination(filtered.length);

    el.totalRecords.textContent = filtered.length + ' records';
}


        function buildRowHTML(log) {
            return `
            <tr data-id="${log.id}">
                <td>${log.student_number}</td>
                <td>${log.name}</td>
                <td>${log.college}</td>
                <td>${log.course}</td>
                <td>${log.library}</td>
                <td>${formatDate(log.checkin_time)}</td>
                <td>${log.checkout_time ? formatDate(log.checkout_time) : '—'}</td>
                <td>
                    ${log.checkout_time
                        ? '<span class="text-success">Checked Out</span>'
                        : `<button class="btn btn-sm btn-warning btn-checkout">Check-Out</button>`
                    }
                </td>
            </tr>
        `;
        }

function appendRow(log) {

    State.logs.unshift(log);
    State.currentPage = 1;
    renderTable();

    // Highlight first row (new check-in)
    const firstRow = el.tableBody.querySelector('tr');
    if (firstRow) {
        firstRow.classList.add('table-success');
        setTimeout(() => {
            firstRow.classList.remove('table-success');
        }, 3000);
    }

    updateKPIs();
}


        /* ================= FILTERS ================= */

        function bindEvents() {

el.filterLibrary.addEventListener('change', e => {
    State.filters.library = e.target.value;
    State.currentPage = 1;
    renderTable();
    updateKPIs();
});

el.filterSearch.addEventListener('keypress', e => {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('btnSearchStudent').click();
    }
});



            /* ===== Student Auto Fill ===== */

el.inputStudentNumber.addEventListener('input', async e => {

    const sn = e.target.value.trim();

    // Only validate at exactly 10 digits
    if (sn.length !== 10) {
        clearStudentFields();
        return;
    }

    try {
        const res = await fetch('API_requests/students.json');
        const data = await res.json();

        if (!data[sn]) {
            clearStudentFields();
            return;
        }

        el.inputName.value = data[sn].name;
        el.inputCollege.value = data[sn].college;
        el.inputCourse.value = data[sn].course;

    } catch (err) {
        console.error("Student fetch error:", err);
    }
});


            /* ===== Submit (Check-In) ===== */

            document.getElementById('logForm').addEventListener('submit', async e => {
                e.preventDefault();

                const student_number = el.inputStudentNumber.value.trim();
                const library = el.inputLibrary.value;

                if (!student_number || !library) return;

                pendingLog = {
                    student_number,
                    name: el.inputName.value,
                    college: el.inputCollege.value,
                    course: el.inputCourse.value,
                    library
                };

                fillConfirmModal();
                confirmModal.show();
            });

            document.getElementById('confirmSaveBtn')
                .addEventListener('click', confirmCheckIn);

            /* ===== Checkout Delegation ===== */

            el.tableBody.addEventListener('click', e => {
                if (!e.target.classList.contains('btn-checkout')) return;

                const row = e.target.closest('tr');
                const id = row.dataset.id;
                const log = State.logs.find(l => l.id == id);

                pendingCheckout = {
                    log,
                    row
                };
                prepareCheckoutModal(log);
                checkoutModal.show();
            });

            document.getElementById('confirmCheckoutBtn')
                .addEventListener('click', confirmCheckout);
        
		
		document.getElementById('btnSearchStudent')
    .addEventListener('click', () => {

        const sn = el.filterSearch.value.trim();
        if (!sn) return;

        const activeLog = State.logs.find(l =>
            l.student_number === sn && !l.checkout_time
        );

        if (!activeLog) {
            alert("No active check-in found.");
            return;
        }

        pendingCheckout = {
            log: activeLog
        };

        prepareCheckoutModal(activeLog);
        checkoutModal.show();
    });

		}

        /* ================= CHECK-IN ================= */

        /* ================= CHECK-IN ================= */
        async function confirmCheckIn() {
            if (!pendingLog) return;

            // Disable button to prevent multiple clicks
            const btn = document.getElementById('confirmSaveBtn');
            btn.disabled = true;
            btn.textContent = "Saving...";

try {
    // Call backend
    const result = await api('addLog', {
        student_number: pendingLog.student_number,
        library: pendingLog.library
    });

    // Only show alert if backend explicitly failed
    if (!result?.success) {
        alert(result?.message || "Failed to insert log. Try again.");
        return;
    }

    // Append new row in real-time
    const newLog = {
        ...pendingLog,
        id: Date.now(), // temporary unique id
        checkin_time: new Date().toISOString(),
        checkout_time: null
    };

    appendRow(newLog);

    // Close modal & clear form
    confirmModal.hide();
    clearStudentFields();
    el.inputStudentNumber.value = '';
    el.inputStudentNumber.focus();

} catch (err) {
    console.error(err);
    // Only show network alert if truly network error
    if (!err.name.includes("AbortError")) {
        alert("Network error. Please try again.");
    }
} finally {
    btn.disabled = false;
    btn.textContent = "Confirm";
    pendingLog = null;
}
        }


        /* ================= CHECKOUT ================= */

async function confirmCheckout() {

    const inputSN = document.getElementById('coStudentNumber').value.trim();

    if (inputSN !== pendingCheckout.log.student_number) {
        document.getElementById('coError').style.display = 'block';
        return;
    }

    const result = await api('checkoutLog', {
        id: pendingCheckout.log.id
    });

    if (!result?.success) return;

    pendingCheckout.log.checkout_time = new Date().toISOString();

    State.currentPage = 1;
    renderTable();

    // Blue highlight
    const updatedRow = el.tableBody.querySelector(
        `tr[data-id="${pendingCheckout.log.id}"]`
    );

    if (updatedRow) {
        updatedRow.classList.add('table-primary');
        setTimeout(() => {
            updatedRow.classList.remove('table-primary');
        }, 3000);
    }

    updateKPIs();
    checkoutModal.hide();
}


        /* ================= KPI ================= */

function updateKPIs() {

    const filtered = getFilteredLogs(); // 🔥 use filtered logs

    el.kpiTotalCheckins.textContent = filtered.length;

    el.kpiActiveStudents.textContent =
        filtered.filter(l => !l.checkout_time).length;

    el.kpiTopColleges.textContent = getTop('college', filtered);
    el.kpiTopCourses.textContent = getTop('course', filtered);
}


function getTop(key, dataset) {
    if (!dataset.length) return '—';

    const count = {};

    dataset.forEach(l => {
        count[l[key]] = (count[l[key]] || 0) + 1;
    });

    return Object.entries(count)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 3)
        .map(e => e[0])
        .join(', ');
}


        /* ================= HELPERS ================= */

function getFilteredLogs() {

    return State.logs.filter(log => {

        let libMatch = true;

        if (State.filters.library === "FILIPINIANA_COMBINED") {

            libMatch =
                log.library === "Filipiniana 1st" ||
                log.library === "Filipiniana 2nd";

        } else if (State.filters.library) {

            libMatch = log.library === State.filters.library;
        }

        const searchMatch =
            !State.filters.search ||
            log.student_number.includes(State.filters.search);

        return libMatch && searchMatch;
    });
}


        function clearStudentFields() {
            el.inputName.value = '';
            el.inputCollege.value = '';
            el.inputCourse.value = '';
        }

        function fillConfirmModal() {
            document.getElementById('cStudentNumber').textContent = pendingLog.student_number;
            document.getElementById('cName').textContent = pendingLog.name;
            document.getElementById('cCollege').textContent = pendingLog.college;
            document.getElementById('cCourse').textContent = pendingLog.course;
            document.getElementById('cLibrary').textContent = pendingLog.library;
        }

        function prepareCheckoutModal(log) {
            document.getElementById('coStudentName').textContent = log.name;
            document.getElementById('coLibrary').textContent = log.library;
            document.getElementById('coStudentNumber').value = '';
            document.getElementById('coError').style.display = 'none';
        }

        function formatDate(d) {
            return new Date(d).toLocaleString();
        }

        function startClock() {
            setInterval(() => {
                el.kpiCurrentTime.textContent = new Date().toLocaleString();
            }, 1000);
        }

        function scheduleMidnightReset() {
            const now = new Date();
            const midnight = new Date();
            midnight.setHours(24, 0, 0, 0);

            setTimeout(() => {
                State.logs = [];
                renderTable();
                updateKPIs();
            }, midnight - now);
        }

        init();

    })();
</script>