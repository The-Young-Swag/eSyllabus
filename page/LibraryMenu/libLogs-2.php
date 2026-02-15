<div class="container-fluid py-4">

    <!-- HEADER -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>
                <h4 class="fw-bold mb-1">Library Attendance Dashboard</h4>
                <small class="text-muted">Real-time monitoring of today's attendance activity</small>
            </div>
            <div class="mt-3 mt-md-0 text-md-end">
                <small class="text-muted">Current Date & Time</small>
                <div class="fw-bold text-danger fs-5" id="kpiCurrentTime"></div>
            </div>
        </div>
    </div>

    <!-- KPI SECTION -->
    <div class="row g-3 mb-4">
        <?php 
        $kpiCards = [
            ['id'=>'kpiTotalCheckins','label'=>'Total Check-Ins Today','color'=>'success','border'=>'success'],
            ['id'=>'kpiActiveStudents','label'=>'Currently In Attendance','color'=>'primary','border'=>'primary'],
            ['id'=>'topColleges','label'=>'Top 3 Colleges Today','color'=>'warning','type'=>'list'],
            ['id'=>'topCourses','label'=>'Top 3 Courses Today','color'=>'info','type'=>'list']
        ];
        foreach($kpiCards as $card): ?>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card shadow-sm <?=isset($card['border'])?'border-start border-4 border-'.$card['border']:''?> h-100">
                    <div class="card-body">
                        <small class="text-muted fw-semibold mb-1"><?= $card['label'] ?></small>
                        <?php if(!isset($card['type'])): ?>
                            <div class="display-5 fw-bold text-<?= $card['color'] ?>" id="<?= $card['id'] ?>">0</div>
                        <?php else: ?>
                            <div id="<?= $card['id'] ?>" class="small">
                                <div class="mb-1"><span class="fw-bold">1.</span> <span class="text-<?= $card['color'] ?>">Loading...</span></div>
                                <div class="mb-1"><span class="fw-bold">2.</span> <span class="text-<?= $card['color'] ?>">Loading...</span></div>
                                <div class="mb-1"><span class="fw-bold">3.</span> <span class="text-<?= $card['color'] ?>">Loading...</span></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- LOG ATTENDANCE -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="fw-semibold fs-6">
                Log Attendance - <span id="currentLibrarySection"></span>
            </div>
            <div class="mt-2 mt-md-0 col-md-3">
                <label for="inputLibrary" class="form-label small fw-semibold mb-1">Library</label>
                <select class="form-select form-select-sm" id="inputLibrary"></select>
            </div>
        </div>
        <div class="card-body">
            <form id="logForm" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label fw-semibold">Identification Number</label>
                    <small class="text-muted d-block mb-2">(Student Number or Employee Number)</small>
                    <div class="input-group">
                        <input type="password" class="form-control form-control-lg" id="inputStudentNumber" placeholder="Enter identification number" autocomplete="off">
                        <button type="button" class="btn btn-outline-secondary" id="toggleIdVisibility">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="col-lg-3" id="specialKeyContainer" style="display:none;">
                    <label class="form-label fw-semibold">Special Key (Birthday: MMDDYY)</label>
                    <input type="password" class="form-control form-control-lg" id="inputSpecialKey" maxlength="6" placeholder="Enter 6-digit key" autocomplete="off">
                </div>

                <div class="col-lg-3">
                    <label class="form-label fw-semibold">Library Section</label>
                    <div class="form-control form-control-lg bg-light" id="currentLibraryDisplay"></div>
                </div>

                <div class="col-lg-2 d-grid">
                    <button type="submit" class="btn btn-success btn-lg fw-semibold" id="submitButton">Submit</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php include '../modalContainer.php'; ?>
<?php include 'LibModals.php'; ?>

<script>
const LibrarySystem = (() => {

    const API_URL = "backend/bk_LibraryMenu/bk_libLogs-Test.php";
    let currentLibraryID = null;
    let currentLibraryName = "";
    let libraryList = [];

    const UI = {
        librarySelect: "#inputLibrary",
        libraryTitle: "#currentLibrarySection",
        libraryDisplay: "#currentLibraryDisplay",
        studentInput: "#inputStudentNumber",
        modal: "#dynamicModal",
        modalTitle: "#dynamicModalTitle",
        modalBody: "#dynamicModalBody",
        modalFooter: "#dynamicModalFooter"
    };

    function apiCall(request, data = {}, callback) {
        $.post(API_URL, { request, ...data }, callback, "json").fail(function(xhr) {
            console.error("API Call Failed:", xhr.responseText);
            alert("Network error. Please try again.");
        });
    }

    function loadLibraries() {
        apiCall("getLibraries", {}, function(res) {
            if (res.error) {
                console.error(res.error);
                return;
            }

            libraryList = res.data || [];
            const $select = $(UI.librarySelect).empty();

            if (!libraryList.length) {
                updateLibraryDisplay("No Library Available");
                return;
            }

            libraryList.forEach(lib => {
                $select.append(
                    `<option value="${lib.SectionID}">
                        ${lib.SectionName}
                     </option>`
                );
            });

            const firstLib = libraryList[0];
            currentLibraryID = parseInt(firstLib.SectionID);
            currentLibraryName = firstLib.SectionName;

            $select.val(currentLibraryID);
            updateLibraryDisplay(currentLibraryName);

            // Load KPI for the first library
            loadKPI(currentLibraryID);
        });
    }

    function loadKPI(sectionID) {
        if (!sectionID) return;

        apiCall("getKPI", { sectionID }, function(res) {
            if (res.success && res.data) {
                const data = res.data;

                // Numeric KPIs
                $("#kpiTotalCheckins").text(data.totalToday || 0);
                $("#kpiActiveStudents").text(data.currentlyInside || 0);

                // List KPIs
                $("#topColleges").html(`
                    <div class="mb-1"><span class="fw-bold">1.</span> <span class="text-warning">${data.topColleges[0] || '-'}</span></div>
                    <div class="mb-1"><span class="fw-bold">2.</span> <span class="text-warning">${data.topColleges[1] || '-'}</span></div>
                    <div class="mb-1"><span class="fw-bold">3.</span> <span class="text-warning">${data.topColleges[2] || '-'}</span></div>
                `);

                $("#topCourses").html(`
                    <div class="mb-1"><span class="fw-bold">1.</span> <span class="text-info">${data.topCourses[0] || '-'}</span></div>
                    <div class="mb-1"><span class="fw-bold">2.</span> <span class="text-info">${data.topCourses[1] || '-'}</span></div>
                    <div class="mb-1"><span class="fw-bold">3.</span> <span class="text-info">${data.topCourses[2] || '-'}</span></div>
                `);
            }
        });
    }

    function startClock() {
        function updateTime() {
            const now = new Date();
            const options = { 
                hour: "2-digit", minute: "2-digit", second: "2-digit", 
                year: "numeric", month: "short", day: "numeric" 
            };
            $("#kpiCurrentTime").text(now.toLocaleString("en-PH", options));
        }
        updateTime();
        setInterval(updateTime, 1000);
    }

    function updateLibraryDisplay(name) {
        currentLibraryName = name;
        $(UI.libraryTitle).text(name);
        $(UI.libraryDisplay).text(name);
    }

    function validateStudent() {
        const studentNumber = $(UI.studentInput).val().trim();
        if (!studentNumber) {
            alert("Enter ID Number");
            return;
        }

        apiCall("validateUser", { studentNumber }, function(res) {
            if (res.error) {
                showModal("Validation Error", `<div class="alert alert-danger">${res.error}</div>`);
                return;
            }

            if (res.success && res.data) {
                selectedStudent = res.data;
                detectAttendanceAction(selectedStudent);
                return;
            }

            if (res.duplicate) {
                duplicateCandidates = res.matches;
                showDuplicateModal();
            }
        });
    }

    function detectAttendanceAction(student) {
        apiCall("checkStatusToday", { studentNumber: student.student_number }, function(res) {
            let action = "checkin";
            if (res.checkedIn && res.sectionID === currentLibraryID) action = "checkout";
            if (res.checkedIn && res.sectionID !== currentLibraryID) action = "switch";
            showModalForStudent(student, action);
        });
    }

    function showDuplicateModal() {
        const body = `
            <div id="modalHeaderContainer" class="text-center mb-3">
                <div class="badge bg-warning fs-6 p-2">
                    <i class="fas fa-user-shield me-2"></i>IDENTITY VERIFICATION REQUIRED
                </div>
            </div>

            <div class="card border-0 bg-light mb-3">
                <div class="card-body">
                    <label class="fw-semibold mb-2">Enter Secret Key</label>
                    <div class="input-group mb-2">
                        <input type="password" id="modalSecretKey" 
                            class="form-control text-center fw-bold fs-4" 
                            maxlength="6" placeholder="••••••">
                        <button class="btn btn-outline-secondary" type="button" id="toggleSecretKey">
                            <i class="fas fa-eye" id="secretIcon"></i>
                        </button>
                    </div>
                    <small class="text-muted">Secret key is the student's birthday (MMDDYY)</small>
                    <div id="secretStatus" class="mt-3 fw-bold text-danger"></div>
                </div>
            </div>

            <div id="verifiedStudentContainer" style="display:none;"></div>
        `;
        showModal("Identity Verification", body);

        // Toggle secret key visibility
        $("#toggleSecretKey").off("click").on("click", function() {
            const input = $("#modalSecretKey");
            const icon = $("#secretIcon");
            if (input.attr("type") === "password") {
                input.attr("type", "text");
                icon.removeClass("fa-eye").addClass("fa-eye-slash");
            } else {
                input.attr("type", "password");
                icon.removeClass("fa-eye-slash").addClass("fa-eye");
            }
        });

        // Validate secret key
        $("#modalSecretKey").off("input").on("input", function() {
            const key = $(this).val();
            if (key.length !== 6) return;
            
            const match = duplicateCandidates.find(s => s.secretKey === key);
            if (!match) {
                $("#secretStatus").text("Invalid Secret Key");
                $("#verifiedStudentContainer").hide().empty();
                return;
            }

            selectedStudent = match;
            $("#secretStatus").html(`<span class="text-success">Identity Verified</span>`);
            showModalForStudent(match, "checkin", true);
        });
    }

    function showModalForStudent(student, action, isDuplicate = false) {
        let headerColor = "success";
        let headerIcon = "fa-sign-in-alt";
        let modalTitle = "Check-In Validation";

        if (action === "checkout") {
            headerColor = "primary";
            headerIcon = "fa-sign-out-alt";
            modalTitle = "Check-Out Validation";
        }
        if (action === "switch") {
            headerColor = "warning";
            headerIcon = "fa-random";
            modalTitle = "Library Switch Validation";
        }

        const body = `
            <div class="text-center mb-3">
                <div class="badge bg-${headerColor} fs-6 p-2">
                    <i class="fas ${headerIcon} me-2"></i>${modalTitle.toUpperCase()}
                </div>
            </div>
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <div class="row mb-2"><div class="col-4 fw-semibold text-muted">Name</div><div class="col-8 fw-bold">${student.name}</div></div>
                    <div class="row mb-2"><div class="col-4 fw-semibold text-muted">Sex</div><div class="col-8">${student.sex}</div></div>
                    <div class="row mb-2"><div class="col-4 fw-semibold text-muted">College</div><div class="col-8">${student.college}</div></div>
                    <div class="row mb-2"><div class="col-4 fw-semibold text-muted">Course</div><div class="col-8">${student.course}</div></div>
                    <hr>
                    <div class="text-center fw-bold text-primary">Library: ${currentLibraryName}</div>
                </div>
            </div>
        `;

        const footer = `
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-${headerColor}" id="confirmAttendance">
                <i class="fas ${headerIcon} me-1"></i>Confirm
            </button>
        `;

        if (isDuplicate) {
            $("#verifiedStudentContainer").html(body).show();
            $("#dynamicModalFooter").html(footer);
        } else {
            showModal(modalTitle, body, footer);
        }

        // Remove any existing click handlers and attach new one
        $("#confirmAttendance").off("click").on("click", function() {
            autoAttendanceFlow(student.student_number);
            $("#dynamicModal").modal("hide");
        });
    }

    function autoAttendanceFlow(studentNumber) {
        apiCall("checkStatusToday", { studentNumber }, function(res) {
            if (!res.checkedIn) {
                saveAttendance("checkin", studentNumber);
            } else if (res.sectionID === currentLibraryID) {
                saveAttendance("checkout", studentNumber);
            } else if (res.sectionID !== currentLibraryID) {
                // First checkout from previous library, then checkin to new one
                apiCall("forceCheckout", { 
                    studentNumber: studentNumber, 
                    sectionID: res.sectionID 
                }, function() {
                    saveAttendance("checkin", studentNumber);
                });
            }
        });
    }

    function saveAttendance(action, studentNumber) {
        if (!currentLibraryID) {
            alert("No library selected.");
            return;
        }

        if (!studentNumber) {
            alert("Student number missing.");
            return;
        }

        apiCall("saveAttendance", {
            action: action,
            studentNumber: studentNumber,
            sectionID: currentLibraryID
        }, function(res) {
            if (res.error) {
                showModal("Warning", `<div class="alert alert-warning">${res.error}</div>`);
                return;
            }

            if (res.success) {
                const msg = action === "checkin" ? "Checked In" : "Checked Out";
                showModal("Success",
                    `<div class="alert alert-success">${msg} successfully.</div>`
                );

                // Clear input
                $(UI.studentInput).val("");

                // Update KPI with the new data from server
                if (res.kpi) {
                    $("#kpiTotalCheckins").text(res.kpi.totalToday || 0);
                    $("#kpiActiveStudents").text(res.kpi.currentlyInside || 0);
                } else {
                    // Fallback: reload all KPI data
                    loadKPI(currentLibraryID);
                }
            }
        });
    }

    function showModal(title, body, footer = "") {
        $("#dynamicModalTitle").html(title);
        $("#dynamicModalBody").html(body);
        $("#dynamicModalFooter").html(footer);
        $("#dynamicModal").modal("show");
    }

    function bindEvents() {
        // Library change event
        $(document).on("change", UI.librarySelect, function() {
            currentLibraryID = parseInt($(this).val());
            currentLibraryName = $(this).find("option:selected").text();
            updateLibraryDisplay(currentLibraryName);
            loadKPI(currentLibraryID);
        });

        // Form submit
        $(document).on("submit", "#logForm", function(e) {
            e.preventDefault();
            validateStudent();
        });

        // Toggle ID visibility
        $(document).on("click", "#toggleIdVisibility", function() {
            const input = $("#inputStudentNumber");
            const icon = $("#toggleIcon");
            if (input.attr("type") === "password") {
                input.attr("type", "text");
                icon.removeClass("fa-eye").addClass("fa-eye-slash");
            } else {
                input.attr("type", "password");
                icon.removeClass("fa-eye-slash").addClass("fa-eye");
            }
        });

        // Special key container visibility (if needed)
        $(document).on("input", "#inputStudentNumber", function() {
            const val = $(this).val();
            // Show special key container for employees or specific pattern
            // Add your logic here if needed
        });
    }

    function init() {
        startClock();
        bindEvents();
        loadLibraries();
    }

    return { init };
})();

$(document).ready(() => LibrarySystem.init());
</script>


