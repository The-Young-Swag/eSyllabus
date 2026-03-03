<div class="container-fluid py-4">

    <!-- HEADER -->
    <div class="card shadow-sm mb-4 border-0 rounded-4">
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

    <!-- LOG ATTENDANCE -->
    <div class="card border-0 shadow-lg overflow-hidden rounded-4 mb-4">
        <div class="card-body py-4 px-4 px-lg-5"
             style="background: linear-gradient(145deg, #064e3b, #065f46);">

            <div class="bg-white rounded-4 shadow-sm p-4 p-lg-5 mx-auto" style="max-width: 1000px;">

                <!-- Header Row -->
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4 flex-wrap">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Library Access</h5>
                        <small class="text-muted">Secure identification verification</small>
                    </div>
                    <div class="mt-3 mt-md-0" style="min-width: 320px;">
                        <small class="text-muted fw-semibold d-block mb-1">Library Section</small>
                        <div class="form-control form-control-lg border-0 shadow-sm fw-semibold text-white fs-6 py-2"
                             style="background: linear-gradient(90deg, #047857, #10b981); border-radius: 0.5rem;">
                            <span id="currentLibraryDisplay">Main Library</span>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form id="logForm" class="row g-3 align-items-end">

                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark mb-2">Identification Number</label>
                        <div class="input-group input-group-lg shadow-sm">
                            <input type="password"
                                   class="form-control border-0 py-3"
                                   id="inputStudentNumber"
                                   placeholder="Enter or scan identification number"
                                   autocomplete="off"
                                   style="font-size: 1.1rem;
                                          background: linear-gradient(90deg, #e6f4ea, #ffffff);
                                          border-radius: 0.5rem;
                                          transition: all 0.2s ease-in-out;">
                            <button type="button" class="btn btn-light border-0 px-4" id="toggleIdVisibility"
                                    style="border-radius: 0.5rem;">
                                <i class="fas fa-eye text-secondary" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-12"><hr class="my-3"></div>

                    <div class="col-12">
                        <div class="d-grid">
                            <button type="submit"
                                    class="btn fw-semibold text-white py-3 shadow-sm"
                                    style="background: linear-gradient(90deg, #047857, #10b981);
                                           border: none; font-size: 1rem; border-radius: 0.5rem;">
                                Confirm Access
                            </button>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- KPI CARDS -->
<!-- KPI CARDS -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #10b981 !important;">
            <div class="card-body p-3">
                <small class="fw-semibold text-muted d-block mb-2">Total Check-Ins Today</small>
                <div class="fw-bold text-success" style="font-size:2rem;" id="kpiTotalCheckins">—</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #3b82f6 !important;">
            <div class="card-body p-3">
                <small class="fw-semibold text-muted d-block mb-2">Currently In Attendance</small>
                <div class="fw-bold text-primary" style="font-size:2rem;" id="kpiActiveStudents">—</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #f59e0b !important;">
            <div class="card-body p-3">
                <small class="fw-semibold text-muted d-block mb-2">Top 3 Colleges Today</small>
                <div id="topColleges" class="small">
                    <div class="mb-1"><span class="fw-bold">1.</span> <span class="text-warning">Loading...</span></div>
                    <div class="mb-1"><span class="fw-bold">2.</span> <span class="text-warning">Loading...</span></div>
                    <div class="mb-1"><span class="fw-bold">3.</span> <span class="text-warning">Loading...</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #06b6d4 !important;">
            <div class="card-body p-3">
                <small class="fw-semibold text-muted d-block mb-2">Top 3 Courses Today</small>
                <div id="topCourses" class="small">
                    <div class="mb-1"><span class="fw-bold">1.</span> <span class="text-info">Loading...</span></div>
                    <div class="mb-1"><span class="fw-bold">2.</span> <span class="text-info">Loading...</span></div>
                    <div class="mb-1"><span class="fw-bold">3.</span> <span class="text-info">Loading...</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<?php include '../modalContainer.php'; ?>
<?php include 'LibModals.php'; ?>

<script>
$(document).ready(function () {

    // =========================================================================
    // STATE
    // =========================================================================
    let currentLibraryID   = null;
    let currentLibraryName = '';
    let selectedUser       = null;
    let duplicateCandidates = [];
    let currentAction      = 'checkin';

    const BACKEND = "backend/bk_LibraryMenu/bk_libLogs-Test.php";

    // =========================================================================
    // CLOCK
    // =========================================================================
    function startClock() {
        const fmt = { hour:"2-digit", minute:"2-digit", second:"2-digit",
                      year:"numeric", month:"short", day:"numeric", hour12:true };
        const tick = () => $("#kpiCurrentTime").text(new Date().toLocaleString("en-US", fmt));
        tick();
        setInterval(tick, 1000);
    }
    startClock();

    // =========================================================================
    // LIBRARIES  (auto-assigned based on logged-in user)
    // =========================================================================
    function loadLibraries() {
        if (typeof UserInfo === 'undefined' || !UserInfo.UserID) {
            $("#currentLibraryDisplay").text("User not logged in");
            return;
        }

        $.post("backend/bk_LibraryMenu/bk_libLogs.php", {
            request: "getLibraries",
            userID:  UserInfo.UserID
        }, function (res) {
            if (!res.success || !res.data?.length) {
                currentLibraryName = res.error ?? "No Library Access";
                currentLibraryID   = null;
                $("#currentLibraryDisplay").text(currentLibraryName);
                return;
            }

            const lib          = res.data[0];
            currentLibraryID   = lib.SectionID;
            currentLibraryName = lib.SectionName;
            $("#currentLibraryDisplay").text(currentLibraryName);
            loadKPI(currentLibraryID);

        }).fail(function (xhr) {
            $("#currentLibraryDisplay").text("Connection error");
            console.error("getLibraries failed:", xhr.responseText);
        });
    }
    loadLibraries();

    // =========================================================================
    // KPI
    // =========================================================================
    function loadKPI(sectionID) {
        $.post(BACKEND, { request: "getKPI", sectionID }, function (res) {
            if (!res.success || !res.data) return;
            const d = res.data;

            $("#kpiTotalCheckins").text(d.totalToday      ?? 0);
            $("#kpiActiveStudents").text(d.currentlyInside ?? 0);

            $("#topColleges").html(
                (d.topColleges || ["-","-","-"]).map((c, i) =>
                    `<div class="mb-1"><span class="fw-bold">${i+1}.</span>
                     <span class="text-warning">${c}</span></div>`
                ).join('')
            );

            $("#topCourses").html(
                (d.topCourses || ["-","-","-"]).map((c, i) =>
                    `<div class="mb-1"><span class="fw-bold">${i+1}.</span>
                     <span class="text-info">${c}</span></div>`
                ).join('')
            );
        });
    }

    // =========================================================================
    // EVENT HANDLERS
    // =========================================================================

    // Toggle ID number visibility
    $("#toggleIdVisibility").on('click', function () {
        const $input = $("#inputStudentNumber");
        const $icon  = $("#toggleIcon");
        const isHidden = $input.attr("type") === "password";
        $input.attr("type", isHidden ? "text" : "password");
        $icon.toggleClass("fa-eye", !isHidden).toggleClass("fa-eye-slash", isHidden);
    });

    // Toggle secret key visibility (duplicate modal)
    $(document).on('click', '#toggleSecretKey', function () {
        const $input   = $("#modalSecretKey");
        const $icon    = $("#secretIcon");
        const isHidden = $input.attr("type") === "password";
        $input.attr("type", isHidden ? "text" : "password");
        $icon.toggleClass("fa-eye", !isHidden).toggleClass("fa-eye-slash", isHidden);
    });

    // Form submit
    $("#logForm").on('submit', function (e) {
        e.preventDefault();
        validateUser();
    });

    // Confirm attendance button (inside dynamic modal)
    $(document).on('click', '#confirmAttendance', function () {
        if (selectedUser && currentAction) processAttendance(selectedUser, currentAction);
    });

    // Secret key input — validate on 6 chars
$(document).on('input', '#modalSecretKey', function () {
    let raw = $(this).val().replace(/\D/g, '').substring(0, 8);

    // Auto-format as MM/DD/YYYY while typing
    let formatted = raw;
    if (raw.length > 4) formatted = raw.slice(0,2) + '/' + raw.slice(2,4) + '/' + raw.slice(4);
    else if (raw.length > 2) formatted = raw.slice(0,2) + '/' + raw.slice(2);

    $(this).val(formatted);

    if (raw.length === 8) {
        validateSecretKey(raw);   // compare digits-only against stored secretKey
    } else {
        $("#verifiedStudentContainer").hide().empty();
        setSecretKeyStatus('muted', 'fa-info-circle', 'Enter birth date (MM/DD/YYYY)');
    }
});

    // =========================================================================
    // USER VALIDATION
    // =========================================================================
    function validateUser() {
        const idNumber = $("#inputStudentNumber").val().trim();
        if (!idNumber) return alert("Please enter an Identification Number.");

        $.post(BACKEND, { request: "validateUser", idNumber }, function (res) {

            if (res.error) {
                // Not found — treat as walk-in guest
                selectedUser  = { id_number: idNumber, name: "Guest", classification: "GUEST",
                                  college: "", course: "", sex: "" };
                currentAction = "checkin";
                return showUserModal(selectedUser, "success", "fa-sign-in-alt", "Check In", "Walk-in guest");
            }

            if (res.duplicate) {
                duplicateCandidates = res.matches;
                return showDuplicateModal();
            }

            selectedUser = res.data;
            $.post(BACKEND, { request: "checkStatusToday", idNumber: selectedUser.id_number }, function (status) {
                determineAction(selectedUser, status);
            });
        });
    }

    // =========================================================================
    // DETERMINE CHECK-IN / CHECK-OUT / SWITCH
    // =========================================================================
    function determineAction(user, status) {
        let action  = "checkin",       color  = "success";
        let icon    = "fa-sign-in-alt", btnText = "Check In";
        let message = "Not checked in yet";

        if (status.checkedIn) {
            const sameSection = parseInt(status.sectionID) === parseInt(currentLibraryID);
            if (sameSection) {
                action = "checkout"; color = "danger"; icon = "fa-sign-out-alt";
                btnText = "Check Out"; message = "Currently in this library";
            } else {
                const prevLibrary = status.sectionName || "another library";
                action  = "switch"; color = "warning"; icon = "fa-random";
                btnText = "Switch & Check In";
                message = `You forgot to check out at <strong>${prevLibrary}</strong>. No worries — we’ve automatically checked you out and completed your check-in here.`;
            }
        }

        currentAction = action;
        showUserModal(user, color, icon, btnText, message);
    }

    // =========================================================================
    // MODALS
    // =========================================================================
    function showUserModal(user, color, icon, btnText, message) {
        const isEmployee = user.classification === "EMPLOYEE";
        const isGuest    = user.classification === "GUEST";

        let rows = `
            <div class="row mb-2"><div class="col-5 fw-semibold">ID</div><div class="col-7">${user.id_number}</div></div>
            <div class="row mb-2"><div class="col-5 fw-semibold">Name</div><div class="col-7">${user.name}</div></div>
            <div class="row mb-2"><div class="col-5 fw-semibold">Sex</div><div class="col-7">${user.sex || 'N/A'}</div></div>
            <div class="row mb-2"><div class="col-5 fw-semibold">Type</div><div class="col-7">${user.classification}</div></div>
        `;

        // Students also show college & course
        if (!isEmployee && !isGuest) {
            rows += `
                <div class="row mb-2"><div class="col-5 fw-semibold">College</div><div class="col-7">${user.college || 'N/A'}</div></div>
                <div class="row mb-2"><div class="col-5 fw-semibold">Course</div><div class="col-7">${user.course  || 'N/A'}</div></div>
            `;
        }

        const body = `
            <div class="text-center mb-3">
                <div class="badge bg-${color} fs-6 p-2">
                    <i class="fas ${icon} me-2"></i>${btnText} Confirmation
                </div>
                <p class="text-muted mt-2 small">${message}</p>
            </div>
            <div class="bg-light p-3 rounded">
                ${rows}
                <hr>
                <div class="text-center fw-bold text-primary">Library: ${currentLibraryName}</div>
            </div>
        `;

        const footer = `
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-${color}" id="confirmAttendance">
                <i class="fas ${icon} me-1"></i>${btnText}
            </button>
        `;

        showModal("Attendance Confirmation", body, footer);
    }

    function showDuplicateModal() {
        const body = `
            <div class="text-center mb-3">
                <div class="badge bg-warning fs-6 p-2">
                    <i class="fas fa-user-shield me-2"></i>Duplicate ID Found
                </div>
                <p class="text-muted mt-2 small">Enter your birth date (MM/DD/YYYY)</p>
            </div>
            <div class="card bg-light p-3 border-0">
                <div class="input-group mb-2">
    <input type="password" id="modalSecretKey"
       class="form-control text-center fw-bold fs-5"
       maxlength="10" placeholder="MM/DD/YYYY" autocomplete="off">
                    <button class="btn btn-outline-secondary" type="button" id="toggleSecretKey">
                        <i class="fas fa-eye" id="secretIcon"></i>
                    </button>
                </div>
                <div id="secretKeyStatus" class="small text-muted mt-1">
                    <i class="fas fa-info-circle me-1"></i>Enter 6-digit key
                </div>
            </div>
            <div id="verifiedStudentContainer" class="mt-3" style="display:none;"></div>
        `;
        showModal("Identity Verification", body, "");
    }

    function setSecretKeyStatus(type, faIcon, text) {
        const colorClass = type === 'success' ? 'text-success' : type === 'danger' ? 'text-danger' : 'text-muted';
        $("#secretKeyStatus").html(
            `<span class="${colorClass}"><i class="fas ${faIcon} me-1"></i>${text}</span>`
        );
    }

function validateSecretKey(key) {
    const match = duplicateCandidates.find(u => {
        if (!u.secretKey) return false;
        return u.secretKey.replace(/\D/g, '') === key;
    });
    if (match) {
        selectedUser  = match;
        currentAction = "checkin";
        setSecretKeyStatus('success', 'fa-check-circle', 'Identity verified');
        $("#verifiedStudentContainer").show();
        $.post(BACKEND, { request: "checkStatusToday", idNumber: selectedUser.id_number }, function (status) {
            determineAction(selectedUser, status);
        });
    } else {
        setSecretKeyStatus('danger', 'fa-exclamation-circle', 'Invalid key — try again');
        $("#verifiedStudentContainer").hide().empty();
    }
}

    // Tracks the auto-close timer so we can cancel it if the user manually closes
    let successTimer = null;

    function showModal(title, body, footer = "") {
        // Cancel any pending auto-close from a previous success modal
        if (successTimer) {
            clearTimeout(successTimer);
            successTimer = null;
        }
        $("#dynamicModalTitle").html(title);
        $("#dynamicModalBody").html(body);
        $("#dynamicModalFooter").html(footer);
        $("#dynamicModal").modal("show");
    }

    // Ensure the X / close button always works regardless of Bootstrap 4 vs 5
    // data-dismiss vs data-bs-dismiss attribute differences in modalContainer.php
    $(document).on("click", "#dynamicModal .btn-close, #dynamicModal [data-dismiss='modal'], #dynamicModal [data-bs-dismiss='modal']", function () {
        if (successTimer) {
            clearTimeout(successTimer);
            successTimer = null;
        }
        $("#dynamicModal").modal("hide");
    });

    // =========================================================================
    // PROCESS & SAVE ATTENDANCE
    // =========================================================================
    function processAttendance(user, action) {
        if (!currentLibraryID || !user.id_number) return;

        // Capture name + label NOW — synchronously — before any async work or
        // globals change. Showing the success modal here prevents a race where a
        // second scan arrives before the POST response and overwrites selectedUser,
        // causing the wrong name to flash in the success message.
        const resolvedAction = action === "switch" ? "checkin" : action;
        const label          = resolvedAction === "checkin" ? "checked in" : "checked out";
        const successName    = user.name;

        showModal("Success",
            `<div class="alert alert-success text-center mb-0">
                <i class="fas fa-check-circle me-2"></i>
                <strong>${successName}</strong> successfully ${label}.
             </div>`
        );
        successTimer = setTimeout(() => {
            successTimer = null;
            $("#dynamicModal").modal("hide");
        }, 2000);

        saveAttendance(user, resolvedAction);
    }

    function saveAttendance(user, action) {
        $.post(BACKEND, {
            request:        "saveAttendance",
            action,
            idNumber:       user.id_number,
            sectionID:      currentLibraryID,
            classification: user.classification || "STUDENT",
            name:           user.name,
            college:        user.college || "",
            course:         user.course  || "",
            sex:            user.sex     || "",
        }, function (res) {
            if (res.error) {
                // Replace the success modal with the actual error
                showModal("Error",
                    `<div class="alert alert-danger text-center mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i>${res.error}
                     </div>`
                );
                return;
            }
            loadKPI(currentLibraryID);
            $("#inputStudentNumber").val("");
        });
    }

});
</script>