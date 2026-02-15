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
// SIMPLE LIBRARY DASHBOARD - CLEAN VERSION
$(document).ready(function() {
    console.log("Library Dashboard loaded");
    
    // Start clock (this is fine to keep)
    startClock();
    
    // Load initial data - just like loadUsers('all')
    loadLibraries();
    
    // Setup all event handlers
    setupLibraryEvents();
});

// Start the clock (keep this)
function startClock() {
    function updateTime() {
        const now = new Date();
        const options = { 
            hour: "2-digit", minute: "2-digit", second: "2-digit", 
            year: "numeric", month: "short", day: "numeric",
            hour12: true
        };
        $("#kpiCurrentTime").text(now.toLocaleString("en-US", options));
    }
    updateTime();
    setInterval(updateTime, 1000);
}

// Load libraries function - SIMPLE like loadUsers()
function loadLibraries() {
    console.log("Loading libraries...");
    
    $.post("backend/bk_LibraryMenu/bk_libLogs-Test.php", { 
        request: "getLibraries" 
    }, function(res) {
        if (res.error) {
            console.error("Error loading libraries:", res.error);
            return;
        }

        const libraryList = res.data || [];
        console.log("Libraries loaded:", libraryList);
        
        const $select = $("#inputLibrary");
        $select.empty();

        if (!libraryList.length) {
            $("#currentLibrarySection").text("No Library Available");
            $("#currentLibraryDisplay").text("No Library Available");
            return;
        }

        // Populate select options
        libraryList.forEach(lib => {
            $select.append(
                `<option value="${lib.SectionID}">
                    ${lib.SectionName}
                 </option>`
            );
        });

        // Always use first library as default (like your working pattern)
        const firstLib = libraryList[0];
        currentLibraryID = firstLib.SectionID;
        currentLibraryName = firstLib.SectionName;
        
        $select.val(currentLibraryID);
        $("#currentLibrarySection").text(currentLibraryName);
        $("#currentLibraryDisplay").text(currentLibraryName);
        
        // Load KPI for the selected library
        loadKPI(currentLibraryID);
    });
}

// Load KPI function
function loadKPI(sectionID) {
    if (!sectionID) return;

    console.log("Loading KPI for library:", sectionID);
    
    $.post("backend/bk_LibraryMenu/bk_libLogs-Test.php", { 
        request: "getKPI", 
        sectionID: sectionID 
    }, function(res) {
        if (res.success && res.data) {
            updateKPIDisplay(res.data);
        } else {
            updateKPIDisplay({
                totalToday: 0,
                currentlyInside: 0,
                topColleges: ['-', '-', '-'],
                topCourses: ['-', '-', '-']
            });
        }
    });
}

// Update KPI display
function updateKPIDisplay(data) {
    $("#kpiTotalCheckins").text(data.totalToday || 0);
    $("#kpiActiveStudents").text(data.currentlyInside || 0);

    const colleges = data.topColleges || ['-', '-', '-'];
    const collegesHtml = colleges.map((college, index) => 
        `<div class="mb-1"><span class="fw-bold">${index + 1}.</span> 
          <span class="text-warning">${college || '-'}</span></div>`
    ).join('');
    $("#topColleges").html(collegesHtml);

    const courses = data.topCourses || ['-', '-', '-'];
    const coursesHtml = courses.map((course, index) => 
        `<div class="mb-1"><span class="fw-bold">${index + 1}.</span> 
          <span class="text-info">${course || '-'}</span></div>`
    ).join('');
    $("#topCourses").html(coursesHtml);
}

// Setup all event handlers - EXACT pattern like setupUserEvents()
function setupLibraryEvents() {
    
    // Library change event
    $(document).off('change.library', '#inputLibrary').on('change.library', '#inputLibrary', function() {
        const newLibId = parseInt($(this).val());
        console.log("Library changed to:", newLibId);
        
        // Update global variables
        currentLibraryID = newLibId;
        
        // Update display
        const selectedLibText = $(this).find("option:selected").text();
        currentLibraryName = selectedLibText;
        $("#currentLibrarySection").text(selectedLibText);
        $("#currentLibraryDisplay").text(selectedLibText);
        
        // Load fresh KPI data
        loadKPI(currentLibraryID);
    });

    // Form submit
    $(document).off('submit.library', '#logForm').on('submit.library', '#logForm', function(e) {
        e.preventDefault();
        validateStudent();
    });

    // Toggle ID visibility
    $(document).off('click.library', '#toggleIdVisibility').on('click.library', '#toggleIdVisibility', function() {
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

    // Toggle secret key visibility in modal
    $(document).off('click.library', '#toggleSecretKey').on('click.library', '#toggleSecretKey', function() {
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

    // Secret key input
    $(document).off('input.library', '#modalSecretKey').on('input.library', '#modalSecretKey', function() {
        const key = $(this).val();
        if (key.length === 6) {
            validateSecretKey(key);
        } else {
            $("#verifiedStudentContainer").hide().empty();
            $("#secretKeyStatus").html('<i class="fas fa-info-circle me-1"></i>Enter 6-digit key to verify')
                .removeClass('text-danger text-success');
        }
    });

    // Confirm attendance button
    $(document).off('click.library', '#confirmAttendance').on('click.library', '#confirmAttendance', function() {
        if (selectedStudent) {
            processAttendance(selectedStudent.student_number, currentAction);
        }
    });
}

// Student validation
function validateStudent() {
    const studentNumber = $("#inputStudentNumber").val().trim();
    if (!studentNumber) {
        alert("Please enter an Identification Number");
        return;
    }

    $.post("backend/bk_LibraryMenu/bk_libLogs-Test.php", { 
        request: "validateUser", 
        studentNumber: studentNumber 
    }, function(res) {
        if (res.error) {
            showModal("Validation Error", `<div class="alert alert-danger">${res.error}</div>`);
            return;
        }

        if (res.success && res.data) {
            selectedStudent = res.data;
            checkStudentStatus(selectedStudent);
            return;
        }

        if (res.duplicate) {
            duplicateCandidates = res.matches;
            showDuplicateModal();
        }
    });
}

// Check student status
function checkStudentStatus(student) {
    $.post("backend/bk_LibraryMenu/bk_libLogs-Test.php", { 
        request: "checkStatusToday", 
        studentNumber: student.student_number 
    }, function(res) {
        determineAttendanceAction(student, res);
    });
}

// Determine attendance action
function determineAttendanceAction(student, status) {
    let action = "checkin";
    let headerColor = "success";
    let headerIcon = "fa-sign-in-alt";
    let modalTitle = "Check-In Confirmation";
    let buttonText = "Check In";
    let message = "You are not checked in yet. Do you want to check in?";
    
    if (status.checkedIn) {
        if (status.sectionID === currentLibraryID) {
            action = "checkout";
            headerColor = "danger";
            headerIcon = "fa-sign-out-alt";
            modalTitle = "Check-Out Confirmation";
            buttonText = "Check Out";
            message = "You are currently checked in to this library. Do you want to check out?";
        } else {
            action = "switch";
            headerColor = "warning";
            headerIcon = "fa-random";
            modalTitle = "Library Switch";
            buttonText = "Switch & Check In";
            message = "You are checked in to a different library. System will automatically check you out from the previous library and check you in here.";
        }
    }
    
    currentAction = action;
    showStudentModal(student, headerColor, headerIcon, modalTitle, buttonText, message);
}

// Show student modal
function showStudentModal(student, headerColor, headerIcon, modalTitle, buttonText, message) {
    const body = `
        <div class="text-center mb-3">
            <div class="badge bg-${headerColor} fs-6 p-3 w-100">
                <i class="fas ${headerIcon} me-2"></i>${modalTitle}
            </div>
            <p class="text-muted mt-2">${message}</p>
        </div>
        <div class="card border-0 bg-light">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-5 fw-semibold text-muted">Student Number</div>
                    <div class="col-7 fw-bold">${student.student_number}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 fw-semibold text-muted">Name</div>
                    <div class="col-7 fw-bold">${student.name}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 fw-semibold text-muted">Sex</div>
                    <div class="col-7">${student.sex || 'N/A'}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 fw-semibold text-muted">College</div>
                    <div class="col-7">${student.college || 'N/A'}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-5 fw-semibold text-muted">Course</div>
                    <div class="col-7">${student.course || 'N/A'}</div>
                </div>
                <hr>
                <div class="text-center">
                    <span class="fw-bold text-primary">Library: ${currentLibraryName}</span>
                </div>
            </div>
        </div>
    `;

    const footer = `
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Cancel
        </button>
        <button type="button" class="btn btn-${headerColor}" id="confirmAttendance">
            <i class="fas ${headerIcon} me-1"></i>${buttonText}
        </button>
    `;

    showModal(modalTitle, body, footer);
}

// Show duplicate modal
function showDuplicateModal() {
    const body = `
        <div class="text-center mb-3">
            <div class="badge bg-warning fs-6 p-3 w-100">
                <i class="fas fa-user-shield me-2"></i>DUPLICATE IDENTIFICATION NUMBER
            </div>
            <p class="text-muted mt-2">Multiple records found. Please enter your secret key to verify identity.</p>
        </div>

        <div class="card border-0 bg-light mb-3">
            <div class="card-body">
                <label class="fw-semibold mb-2">Secret Key (Birthday: MMDDYY)</label>
                <div class="input-group mb-2">
                    <input type="password" id="modalSecretKey" 
                        class="form-control text-center fw-bold fs-4" 
                        maxlength="6" placeholder="••••••" autocomplete="off">
                    <button class="btn btn-outline-secondary" type="button" id="toggleSecretKey">
                        <i class="fas fa-eye" id="secretIcon"></i>
                    </button>
                </div>
                <div id="secretKeyStatus" class="small text-muted mt-1">
                    <i class="fas fa-info-circle me-1"></i>Enter 6-digit key to verify
                </div>
            </div>
        </div>

        <div id="verifiedStudentContainer" style="display: none;"></div>
    `;

    showModal("Identity Verification Required", body, "");
}

// Validate secret key
function validateSecretKey(key) {
    const match = duplicateCandidates.find(s => s.secretKey === key);
    
    if (match) {
        selectedStudent = match;
        $("#secretKeyStatus").html('<span class="text-success"><i class="fas fa-check-circle me-1"></i>Identity Verified</span>')
            .removeClass('text-muted text-danger').addClass('text-success');
        showVerifiedStudent(match);
    } else {
        $("#secretKeyStatus").html('<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>Invalid Secret Key</span>')
            .removeClass('text-muted text-success').addClass('text-danger');
        $("#verifiedStudentContainer").hide().empty();
    }
}

// Show verified student
function showVerifiedStudent(student) {
    $.post("backend/bk_LibraryMenu/bk_libLogs-Test.php", { 
        request: "checkStatusToday", 
        studentNumber: student.student_number 
    }, function(res) {
        let action = "checkin";
        let headerColor = "success";
        let headerIcon = "fa-sign-in-alt";
        let buttonText = "Check In";
        let message = "You are not checked in yet. Do you want to check in?";
        
        if (res.checkedIn) {
            if (res.sectionID === currentLibraryID) {
                action = "checkout";
                headerColor = "danger";
                headerIcon = "fa-sign-out-alt";
                buttonText = "Check Out";
                message = "You are currently checked in to this library. Do you want to check out?";
            } else {
                action = "switch";
                headerColor = "warning";
                headerIcon = "fa-random";
                buttonText = "Switch & Check In";
                message = "You are checked in to a different library. System will automatically check you out from the previous library and check you in here.";
            }
        }
        
        currentAction = action;
        
        const studentHtml = `
            <div class="card border-${headerColor} mt-3">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="badge bg-${headerColor} fs-6 p-2">
                            <i class="fas ${headerIcon} me-2"></i>${buttonText} Confirmation
                        </div>
                        <p class="text-muted mt-2 small">${message}</p>
                    </div>
                    
                    <div class="bg-light p-3 rounded">
                        <div class="row mb-2">
                            <div class="col-5 fw-semibold">Student Number</div>
                            <div class="col-7">${student.student_number}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 fw-semibold">Name</div>
                            <div class="col-7">${student.name}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 fw-semibold">Sex</div>
                            <div class="col-7">${student.sex || 'N/A'}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 fw-semibold">College</div>
                            <div class="col-7">${student.college || 'N/A'}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 fw-semibold">Course</div>
                            <div class="col-7">${student.course || 'N/A'}</div>
                        </div>
                        <hr>
                        <div class="text-center">
                            <span class="fw-bold text-primary">Library: ${currentLibraryName}</span>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-${headerColor}" id="confirmAttendance">
                            <i class="fas ${headerIcon} me-1"></i>${buttonText}
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $("#verifiedStudentContainer").html(studentHtml).show();
    });
}

// Show modal
function showModal(title, body, footer = "") {
    $("#dynamicModalTitle").html(title);
    $("#dynamicModalBody").html(body);
    $("#dynamicModalFooter").html(footer);
    $("#dynamicModal").modal("show");
}

// Process attendance
function processAttendance(studentNumber, action) {
    if (!currentLibraryID || !studentNumber) {
        alert("Missing required data");
        return;
    }

    $("#dynamicModal").modal("hide");

    if (action === "switch") {
        $.post("backend/bk_LibraryMenu/bk_libLogs-Test.php", { 
            request: "checkStatusToday", 
            studentNumber 
        }, function(status) {
            if (status.checkedIn && status.sectionID !== currentLibraryID) {
                $.post("backend/bk_LibraryMenu/bk_libLogs-Test.php", { 
                    request: "forceCheckout", 
                    studentNumber, 
                    sectionID: status.sectionID 
                }, function() {
                    saveAttendance("checkin", studentNumber);
                });
            }
        });
    } else {
        saveAttendance(action, studentNumber);
    }
}

// Save attendance
function saveAttendance(action, studentNumber) {
    $.post("backend/bk_LibraryMenu/bk_libLogs-Test.php", {
        request: "saveAttendance",
        action: action,
        studentNumber: studentNumber,
        sectionID: currentLibraryID
    }, function(res) {
        if (res.error) {
            showModal("Warning", `<div class="alert alert-warning">${res.error}</div>`);
            return;
        }

        if (res.success) {
            const successMsg = action === "checkin" ? "checked in" : "checked out";
            const successColor = action === "checkin" ? "success" : "danger";
            
            showModal("Success",
                `<div class="alert alert-${successColor} text-center">
                    <i class="fas fa-check-circle me-2"></i>
                    Successfully ${successMsg}!
                </div>`
            );

            $("#inputStudentNumber").val("");

            // Update KPI with new data
            if (res.kpi) {
                updateKPIDisplay(res.kpi);
            } else {
                loadKPI(currentLibraryID);
            }

            setTimeout(() => {
                $("#dynamicModal").modal("hide");
            }, 2000);
        }
    });
}
</script>