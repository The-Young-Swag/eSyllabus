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
    let selectedStudent = null;
    let duplicateCandidates = [];
    let currentAction = null;

    const UI = {
        librarySelect: "#inputLibrary",
        libraryTitle: "#currentLibrarySection",
        libraryDisplay: "#currentLibraryDisplay",
        studentInput: "#inputStudentNumber",
        modal: "#dynamicModal",
        modalTitle: "#dynamicModalTitle",
        modalBody: "#dynamicModalBody",
        modalFooter: "#dynamicModalFooter",
        kpiTotalCheckins: "#kpiTotalCheckins",
        kpiActiveStudents: "#kpiActiveStudents",
        topColleges: "#topColleges",
        topCourses: "#topCourses",
        currentTime: "#kpiCurrentTime"
    };

    // ==================== API CALLS ====================
    function apiCall(request, data = {}, callback) {
        $.post(API_URL, { request, ...data }, callback, "json").fail(function(xhr) {
            console.error("API Call Failed:", xhr.responseText);
            showModal("Error", `<div class="alert alert-danger">Network error. Please try again.</div>`);
        });
    }

    // ==================== INITIALIZATION ====================
    function init() {
        startClock();
        bindEvents();
        loadLibraries();
        
        // Check if we have a saved library in sessionStorage
        const savedLibrary = sessionStorage.getItem('selectedLibrary');
        if (savedLibrary) {
            currentLibraryID = parseInt(savedLibrary);
        }
    }

    // ==================== CLOCK ====================
    function startClock() {
        function updateTime() {
            const now = new Date();
            const options = { 
                hour: "2-digit", minute: "2-digit", second: "2-digit", 
                year: "numeric", month: "short", day: "numeric",
                hour12: true
            };
            $(UI.currentTime).text(now.toLocaleString("en-US", options));
        }
        updateTime();
        setInterval(updateTime, 1000);
    }

    // ==================== LIBRARY MANAGEMENT ====================
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

            // Use saved library or first one
            let selectedLib = currentLibraryID || libraryList[0].SectionID;
            
            // Verify saved library still exists
            const libExists = libraryList.some(lib => lib.SectionID === selectedLib);
            if (!libExists) {
                selectedLib = libraryList[0].SectionID;
            }

            currentLibraryID = parseInt(selectedLib);
            const selectedLibData = libraryList.find(lib => lib.SectionID === currentLibraryID);
            currentLibraryName = selectedLibData ? selectedLibData.SectionName : libraryList[0].SectionName;

            $select.val(currentLibraryID);
            updateLibraryDisplay(currentLibraryName);
            
            // Save to sessionStorage
            sessionStorage.setItem('selectedLibrary', currentLibraryID);

            // Load KPI for the selected library
            loadKPI(currentLibraryID);
        });
    }

    function updateLibraryDisplay(name) {
        currentLibraryName = name;
        $(UI.libraryTitle).text(name);
        $(UI.libraryDisplay).text(name);
    }

    // ==================== KPI MANAGEMENT ====================
    function loadKPI(sectionID) {
        if (!sectionID) return;

        apiCall("getKPI", { sectionID }, function(res) {
            if (res.success && res.data) {
                updateKPIDisplay(res.data);
            }
        });
    }

    function updateKPIDisplay(data) {
        // Numeric KPIs
        $(UI.kpiTotalCheckins).text(data.totalToday || 0);
        $(UI.kpiActiveStudents).text(data.currentlyInside || 0);

        // Top Colleges
        const collegesHtml = data.topColleges.map((college, index) => 
            `<div class="mb-1"><span class="fw-bold">${index + 1}.</span> 
              <span class="text-warning">${college || '-'}</span></div>`
        ).join('');
        $(UI.topColleges).html(collegesHtml);

        // Top Courses
        const coursesHtml = data.topCourses.map((course, index) => 
            `<div class="mb-1"><span class="fw-bold">${index + 1}.</span> 
              <span class="text-info">${course || '-'}</span></div>`
        ).join('');
        $(UI.topCourses).html(coursesHtml);
    }

    // ==================== STUDENT VALIDATION ====================
    function validateStudent() {
        const studentNumber = $(UI.studentInput).val().trim();
        if (!studentNumber) {
            alert("Please enter an Identification Number");
            return;
        }

        apiCall("validateUser", { studentNumber }, function(res) {
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

    function checkStudentStatus(student) {
        apiCall("checkStatusToday", { studentNumber: student.student_number }, function(res) {
            determineAttendanceAction(student, res);
        });
    }

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
                headerColor = "primary";
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

    // ==================== MODAL MANAGEMENT (SINGLE MODAL) ====================
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

        // Attach confirm handler
        $("#confirmAttendance").off("click").on("click", function() {
            processAttendance(student.student_number, currentAction);
        });
    }

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

            <!-- Student data will appear here only after successful validation -->
            <div id="verifiedStudentContainer" style="display: none;"></div>
        `;

        // Empty footer for now, will be added after validation
        showModal("Identity Verification Required", body, "");

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

        // Validate secret key when it reaches 6 digits
        $("#modalSecretKey").off("input").on("input", function() {
            const key = $(this).val();
            
            if (key.length === 6) {
                validateSecretKey(key);
            } else {
                // Hide student data if key is not 6 digits
                $("#verifiedStudentContainer").hide().empty();
                $("#secretKeyStatus").html('<i class="fas fa-info-circle me-1"></i>Enter 6-digit key to verify').removeClass('text-danger text-success');
            }
        });
    }

    function validateSecretKey(key) {
        // Find match in duplicate candidates
        const match = duplicateCandidates.find(s => s.secretKey === key);
        
        if (match) {
            // Valid key - show student data
            selectedStudent = match;
            $("#secretKeyStatus").html('<span class="text-success"><i class="fas fa-check-circle me-1"></i>Identity Verified</span>')
                .removeClass('text-muted text-danger').addClass('text-success');
            
            // Show student data
            showVerifiedStudent(match);
        } else {
            // Invalid key
            $("#secretKeyStatus").html('<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>Invalid Secret Key</span>')
                .removeClass('text-muted text-success').addClass('text-danger');
            $("#verifiedStudentContainer").hide().empty();
        }
    }

    function showVerifiedStudent(student) {
        // Check status to determine action
        apiCall("checkStatusToday", { studentNumber: student.student_number }, function(res) {
            let action = "checkin";
            let headerColor = "success";
            let headerIcon = "fa-sign-in-alt";
            let buttonText = "Check In";
            let message = "You are not checked in yet. Do you want to check in?";
            
            if (res.checkedIn) {
                if (res.sectionID === currentLibraryID) {
                    action = "checkout";
                    headerColor = "primary";
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
            
            // Attach confirm handler
            $("#confirmAttendance").off("click").on("click", function() {
                processAttendance(student.student_number, currentAction);
            });
        });
    }

    function showModal(title, body, footer = "") {
        $(UI.modalTitle).html(title);
        $(UI.modalBody).html(body);
        $(UI.modalFooter).html(footer);
        $(UI.modal).modal("show");
    }

    // ==================== ATTENDANCE PROCESSING ====================
    function processAttendance(studentNumber, action) {
        if (!currentLibraryID || !studentNumber) {
            alert("Missing required data");
            return;
        }

        $(UI.modal).modal("hide");

        if (action === "switch") {
            // For switch, we need to checkout from previous first
            apiCall("checkStatusToday", { studentNumber }, function(status) {
                if (status.checkedIn && status.sectionID !== currentLibraryID) {
                    // Force checkout from previous library
                    apiCall("forceCheckout", { 
                        studentNumber, 
                        sectionID: status.sectionID 
                    }, function() {
                        // Then check in to new library
                        saveAttendance("checkin", studentNumber);
                    });
                }
            });
        } else {
            // Direct checkin or checkout
            saveAttendance(action, studentNumber);
        }
    }

    function saveAttendance(action, studentNumber) {
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
                const successMsg = action === "checkin" ? "checked in" : "checked out";
                showModal("Success",
                    `<div class="alert alert-success text-center">
                        <i class="fas fa-check-circle me-2"></i>
                        Successfully ${successMsg}!
                    </div>`
                );

                // Clear input
                $(UI.studentInput).val("");

                // Update KPI with new data from server
                if (res.kpi) {
                    updateKPIDisplay(res.kpi);
                } else {
                    // Fallback: reload KPI
                    loadKPI(currentLibraryID);
                }

                // Auto-hide success modal after 2 seconds
                setTimeout(() => {
                    $(UI.modal).modal("hide");
                }, 2000);
            }
        });
    }

    // ==================== EVENT BINDINGS ====================
    function bindEvents() {
        // Library change event
        $(document).on("change", UI.librarySelect, function() {
            currentLibraryID = parseInt($(this).val());
            const selectedLib = libraryList.find(lib => lib.SectionID === currentLibraryID);
            currentLibraryName = selectedLib ? selectedLib.SectionName : "";
            
            updateLibraryDisplay(currentLibraryName);
            loadKPI(currentLibraryID);
            
            // Save to sessionStorage
            sessionStorage.setItem('selectedLibrary', currentLibraryID);
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

        // Handle page visibility change (when user returns to tab)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                // User returned to the page, refresh KPI for current library
                if (currentLibraryID) {
                    loadKPI(currentLibraryID);
                }
            }
        });

        // Handle before unload to save state
        window.addEventListener('beforeunload', function() {
            if (currentLibraryID) {
                sessionStorage.setItem('selectedLibrary', currentLibraryID);
            }
        });
    }

    return { init };
})();

// Initialize when document is ready
$(document).ready(() => LibrarySystem.init());
</script>


