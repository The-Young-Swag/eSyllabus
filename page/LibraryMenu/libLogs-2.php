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
let duplicateCandidates = [];
let selectedStudent = null;

function loadKPI(sectionID) {
    if (!sectionID) return;

    apiCall("getKPI", { sectionID }, res => {
        if (res.success && res.data) {
            const data = res.data;

            // Numeric KPIs
            $("#kpiTotalCheckins").text(data.totalToday);
            $("#kpiActiveStudents").text(data.currentlyInside);

            // List KPIs
            $("#topColleges").html(`
                <div class="mb-1"><span class="fw-bold">1.</span> <span class="text-warning">${data.topColleges[0]}</span></div>
                <div class="mb-1"><span class="fw-bold">2.</span> <span class="text-warning">${data.topColleges[1]}</span></div>
                <div class="mb-1"><span class="fw-bold">3.</span> <span class="text-warning">${data.topColleges[2]}</span></div>
            `);

            $("#topCourses").html(`
                <div class="mb-1"><span class="fw-bold">1.</span> <span class="text-info">${data.topCourses[0]}</span></div>
                <div class="mb-1"><span class="fw-bold">2.</span> <span class="text-info">${data.topCourses[1]}</span></div>
                <div class="mb-1"><span class="fw-bold">3.</span> <span class="text-info">${data.topCourses[2]}</span></div>
            `);
        }
    });
}



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
        $.post(API_URL, { request, ...data }, callback, "json");
    }

function loadLibraries() {
    apiCall("getLibraries", {}, res => {

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

        loadKPI(currentLibraryID);
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
        if (!studentNumber) return alert("Enter ID Number");

        apiCall("validateUser", { studentNumber }, res => {
            if (res.error)
                return showModal("Validation Error", `<div class="alert alert-danger">${res.error}</div>`);

            if (res.success && res.data) {
                selectedStudent = res.data;
                return detectAttendanceAction(selectedStudent);
            }

            if (res.duplicate) {
                duplicateCandidates = res.matches;
                return showDuplicateModal();
            }
        });
    }

    function detectAttendanceAction(student) {
        apiCall("checkStatusToday", { studentNumber: student.student_number }, res => {
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
                        <button class="btn btn-outline-secondary" id="toggleSecretKey">
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

        $(document).off("click", "#toggleSecretKey").on("click", "#toggleSecretKey", function () {
            const input = $("#modalSecretKey");
            const icon  = $("#secretIcon");
            if (input.attr("type") === "password") {
                input.attr("type", "text");
                icon.removeClass("fa-eye").addClass("fa-eye-slash");
            } else {
                input.attr("type", "password");
                icon.removeClass("fa-eye-slash").addClass("fa-eye");
            }
        });

        $(document).off("input", "#modalSecretKey").on("input", "#modalSecretKey", function () {
            const key = $(this).val();
            if (key.length !== 6) return;
            const match = duplicateCandidates.find(s => s.secretKey === key);
            if (!match) {
                $("#secretStatus").text("Invalid Secret Key");
                $("#verifiedStudentContainer").hide();
                return;
            }

            selectedStudent = match;
            $("#secretStatus").html(`<span class="text-success">Identity Verified</span>`);

            showModalForStudent(match, "checkin", true);
        });
    }

    function showModalForStudent(student, action, isDuplicate = false) {
        let headerColor = "success";
        let headerIcon  = "fa-sign-in-alt";
        let modalTitle  = "Check-In Validation";

        if (action === "checkout") {
            headerColor = "primary";
            headerIcon  = "fa-sign-out-alt";
            modalTitle  = "Check-Out Validation";
        }
        if (action === "switch") {
            headerColor = "warning";
            headerIcon  = "fa-random";
            modalTitle  = "Library Switch Validation";
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
            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-${headerColor}" id="confirmAttendance">
                <i class="fas ${headerIcon} me-1"></i>Confirm
            </button>
        `;

        if (isDuplicate) {
            $("#verifiedStudentContainer").html(body).show();
            $(UI.modalFooter).html(footer);
        } else {
            showModal(modalTitle, body, footer);
        }

        $(document).off("click", "#confirmAttendance").on("click", "#confirmAttendance", () => {
            autoAttendanceFlow(student.student_number);
            $(UI.modal).modal("hide");
        });
    }

    function autoAttendanceFlow(studentNumber) {
        apiCall("checkStatusToday", { studentNumber }, res => {
            if (!res.checkedIn) return saveAttendance("checkin", studentNumber);
            if (res.sectionID === currentLibraryID) return saveAttendance("checkout", studentNumber);
            if (res.sectionID !== currentLibraryID) {
                apiCall("forceCheckout", { studentNumber, sectionID: res.sectionID }, () => {
                    saveAttendance("checkin", studentNumber);
                });
            }
        });
    }

function saveAttendance(action, studentNumber) {

    apiCall("saveAttendance",
        { action, studentNumber, sectionID: currentLibraryID },
        function (res) {

            if (!res || res.error) {
                return showModal(
                    "Warning",
                    `<div class="alert alert-warning">
                        ${res?.error || "Unexpected server error"}
                     </div>`
                );
            }

            /* ✅ Use backend-confirmed action */
            const finalAction = res.action || action;

            const msg = finalAction === "checkin"
                ? "Checked In"
                : "Checked Out";

            showModal(
                "Success",
                `<div class="alert alert-success">
                    ${msg} successfully.
                 </div>`
            );

            /* ✅ Clear ID input */
            $(UI.studentInput).val("").focus();

            /* 🔥 EVENT-DRIVEN KPI UPDATE (NO REQUERY) */
            if (res.kpi) {

                if (typeof res.kpi.totalToday !== "undefined") {
                    $("#kpiTotalCheckins")
                        .text(res.kpi.totalToday);
                }

                if (typeof res.kpi.currentlyInside !== "undefined") {
                    $("#kpiActiveStudents")
                        .text(res.kpi.currentlyInside);
                }
            }
        }
    );
}



    function showModal(title, body, footer = "") {
        $(UI.modalTitle).html(title);
        $(UI.modalBody).html(body);
        $(UI.modalFooter).html(footer);
        $(UI.modal).modal("show");
    }

    function bindEvents() {
        $(document).on("change", UI.librarySelect, function () {
            currentLibraryID = $(this).val();
            currentLibraryName = $(this).find("option:selected").text();
            updateLibraryDisplay(currentLibraryName);
			loadKPI(currentLibraryID);
        });



        $(document).on("submit", "#logForm", function (e) {
            e.preventDefault();
            validateStudent();
        });

        $(document).on("click", "#toggleIdVisibility", function () {
            const input = $("#inputStudentNumber");
            const icon  = $("#toggleIcon");
            if (input.attr("type") === "password") {
                input.attr("type", "text");
                icon.removeClass("fa-eye").addClass("fa-eye-slash");
            } else {
                input.attr("type", "password");
                icon.removeClass("fa-eye-slash").addClass("fa-eye");
            }
        });
    }

function init() {
    startClock();
    bindEvents();
    loadLibraries(); // this will auto-load KPI safely
}


    return { init };

})();

$(document).ready(() => LibrarySystem.init());


</script>


