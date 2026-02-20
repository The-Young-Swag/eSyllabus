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

        <!-- Emerald Background Wrapper -->
        <div class="card-body py-4 px-4 px-lg-5"
             style="background: linear-gradient(145deg, #064e3b, #065f46);">

            <!-- Elevated Form Container -->
            <div class="bg-white rounded-4 shadow-sm p-4 p-lg-5 mx-auto"
                 style="max-width: 1000px;">

                <!-- HEADER ROW -->
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4 flex-wrap">

                    <!-- Title -->
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            Library Access
                        </h5>
                        <small class="text-muted">
                            Secure identification verification
                        </small>
                    </div>

                    <!-- Library Section -->
                    <div class="mt-3 mt-md-0" style="min-width: 320px;">
                        <small class="text-muted fw-semibold d-block mb-1">
                            Library Section
                        </small>
                        <div class="form-control form-control-lg border-0 shadow-sm fw-semibold text-white fs-6 py-2"
                             style="background: linear-gradient(90deg, #047857, #10b981); border-radius:0.5rem;">
                            <span id="currentLibraryDisplay">Main Library</span>
                        </div>
                    </div>

                </div>

                <!-- INPUT AREA -->
                <form id="logForm" class="row g-3 align-items-end">

                    <!-- Identification Number -->
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark mb-2">
                            Identification Number
                        </label>

                        <div class="input-group input-group-lg shadow-sm">
                            <input type="password"
                                   class="form-control border-0 py-3"
                                   id="inputStudentNumber"
                                   placeholder="Enter or scan identification number"
                                   autocomplete="off"
                                   style="font-size:1.1rem;
                                          background: linear-gradient(90deg, #e6f4ea, #ffffff);
                                          border-radius: 0.5rem;
                                          transition: all 0.2s ease-in-out;">

                            <button type="button"
                                    class="btn btn-light border-0 px-4"
                                    id="toggleIdVisibility"
                                    style="border-radius: 0.5rem;">
                                <i class="fas fa-eye text-secondary" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Special Key -->
                    <div class="col-md-6" id="specialKeyContainer" style="display:none;">
                        <label class="form-label fw-semibold text-dark mb-2">
                            Special Key
                        </label>

                        <input type="password"
                               class="form-control border-0 shadow-sm py-2"
                               id="inputSpecialKey"
                               maxlength="6"
                               placeholder="MMDDYY"
                               autocomplete="off"
                               style="background: linear-gradient(90deg, #f3f9f7, #ffffff);
                                      border-radius:0.5rem;">
                    </div>

                    <!-- Divider -->
                    <div class="col-12">
                        <hr class="my-3">
                    </div>

                    <!-- Submit Button -->
                    <div class="col-12">
                        <div class="d-grid">
                            <button type="submit"
                                    class="btn fw-semibold text-white py-3 shadow-sm"
                                    style="background: linear-gradient(90deg, #047857, #10b981);
                                           border:none;
                                           font-size:1rem;
                                           border-radius:0.5rem;">
                                Confirm Access
                            </button>
                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- KPI SECTION -->
    <div class="row g-3 mb-4">
        <?php 
        $kpiCards = [
            ['id'=>'kpiTotalCheckins','label'=>'Total Check-Ins Today','color'=>'#10b981','border'=>'#047857'],   // Emerald
            ['id'=>'kpiActiveStudents','label'=>'Currently In Attendance','color'=>'#3b82f6','border'=>'#1d4ed8'], // Blue
            ['id'=>'topColleges','label'=>'Top 3 Colleges Today','color'=>'#facc15','border'=>'#ca8a04','type'=>'list'], // Yellow
            ['id'=>'topCourses','label'=>'Top 3 Courses Today','color'=>'#06b6d4','border'=>'#0e7490','type'=>'list']   // Cyan
        ];
        foreach($kpiCards as $card): ?>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card shadow-sm rounded-4 h-100"
                     style="border-left: 6px solid <?= $card['border'] ?>;
                            background: linear-gradient(135deg, #ffffff, #f3f4f6);">
                    <div class="card-body d-flex flex-column justify-content-between">

                        <!-- Label -->
                        <small class="fw-semibold text-muted mb-2"><?= $card['label'] ?></small>

                        <!-- KPI Value / Dynamic Data -->
                        <?php if(!isset($card['type'])): ?>
                            <div class="display-5 fw-bold"
                                 style="color: <?= $card['color'] ?>; font-size:2rem;"
                                 id="<?= $card['id'] ?>">
                                <?= $card['id'] ?>
                            </div>
                        <?php else: ?>
                            <div id="<?= $card['id'] ?>" class="small">
                                <?php for($i=1;$i<=3;$i++): ?>
                                    <div class="mb-1">
                                        <span class="fw-bold"><?= $i ?>.</span> 
                                        <span style="color: <?= $card['color'] ?>;">Loading...</span>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../modalContainer.php'; ?>
<?php include 'LibModals.php'; ?>

<script>
$(document).ready(function() {
    console.log("Library Dashboard initialized");

    // ======================
    // GLOBAL STATE
    // ======================
    let currentLibraryID = null;
    let currentLibraryName = '';
    let selectedUser = null;
    let duplicateCandidates = [];
    let currentAction = 'checkin';

    // ======================
    // CLOCK
    // ======================
    function startClock() {
        const updateTime = () => {
            const now = new Date();
            const options = { hour:"2-digit", minute:"2-digit", second:"2-digit", year:"numeric", month:"short", day:"numeric", hour12:true };
            $("#kpiCurrentTime").text(now.toLocaleString("en-US", options));
        };
        updateTime();
        setInterval(updateTime, 1000);
    }
    startClock();

// ======================
// LOAD LIBRARIES (automatically set based on user access)
// ======================
function loadLibraries() {
    if (typeof UserInfo === 'undefined' || !UserInfo.UserID) {
        console.error("User not logged in – UserInfo is undefined or missing UserID");
        $("#currentLibrarySection, #currentLibraryDisplay").text("User not logged in");
        return;
    }

    console.log("Loading libraries for user ID:", UserInfo.UserID);

    $.post("backend/bk_LibraryMenu/bk_libLogs.php", {
        request: "getLibraries",
        userID: UserInfo.UserID
    }, function(res) {
        console.log("Raw response from getLibraries:", res);

        if (res.error) {
            console.error("Server returned error:", res.error);
            $("#currentLibrarySection, #currentLibraryDisplay").text("Error: " + res.error);
            return;
        }

        if (!res.success) {
            console.error("Server returned success=false, data:", res);
            $("#currentLibrarySection, #currentLibraryDisplay").text("Failed to load libraries");
            return;
        }

        if (!res.data || !res.data.length) {
            console.warn("No libraries found for this user. Data array is empty.");
            currentLibraryName = "No Library Access";
            currentLibraryID = null;
            $("#currentLibrarySection, #currentLibraryDisplay").text(currentLibraryName);
            $("#submitButton").prop('disabled', true);
            return;
        }

        console.log("Libraries received:", res.data);

        // Use the first library (or you could add logic if multiple)
        const firstLib = res.data[0];
        currentLibraryID = firstLib.SectionID;
        currentLibraryName = firstLib.SectionName;

        console.log("Selected library:", currentLibraryName, "(ID:", currentLibraryID, ")");

        // Update displays
        $("#currentLibrarySection, #currentLibraryDisplay").text(currentLibraryName);
        $("#submitButton").prop('disabled', false);

        // Load KPI for this library
        loadKPI(currentLibraryID);
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error("AJAX request failed:", textStatus, errorThrown);
        console.error("Response text:", jqXHR.responseText);
        $("#currentLibrarySection, #currentLibraryDisplay").text("AJAX error: " + textStatus);
    });
}
    loadLibraries();

    // ======================
    // LOAD KPI
    // ======================
    function loadKPI(sectionID) {
        $.post("backend/bk_LibraryMenu/bk_libLogs-Test.php", { request: "getKPI", sectionID }, function(res) {
            if(!res.success || !res.data) return;

            const data = res.data;
            $("#kpiTotalCheckins").text(data.totalToday || 0);
            $("#kpiActiveStudents").text(data.currentlyInside || 0);

            const collegesHtml = (data.topColleges || ["-","-","-"]).map((c,i) =>
                `<div class="mb-1"><span class="fw-bold">${i+1}.</span> <span class="text-warning">${c}</span></div>`).join('');
            $("#topColleges").html(collegesHtml);

            const coursesHtml = (data.topCourses || ["-","-","-"]).map((c,i) =>
                `<div class="mb-1"><span class="fw-bold">${i+1}.</span> <span class="text-info">${c}</span></div>`).join('');
            $("#topCourses").html(coursesHtml);
        });
    }

    // ======================
    // EVENT HANDLERS
    // ======================
    $("#inputLibrary").on('change', function(){
        currentLibraryID = parseInt($(this).val());
        currentLibraryName = $(this).find('option:selected').text();
        $("#currentLibrarySection, #currentLibraryDisplay").text(currentLibraryName);
        loadKPI(currentLibraryID);
    });

    $("#toggleIdVisibility").on('click', function(){
        const $input = $("#inputStudentNumber");
        const $icon = $("#toggleIcon");
        if($input.attr("type")==="password"){
            $input.attr("type","text"); $icon.removeClass("fa-eye").addClass("fa-eye-slash");
        } else {
            $input.attr("type","password"); $icon.removeClass("fa-eye-slash").addClass("fa-eye");
        }
    });

    $("#logForm").on('submit', function(e){
        e.preventDefault();
        validateUser();
    });

    $(document).on('click','#confirmAttendance', function(){
        if(selectedUser && currentAction) processAttendance(selectedUser, currentAction);
    });

    // ======================
    // VALIDATE USER
    // ======================
    function validateUser() {
        const idNumber = $("#inputStudentNumber").val().trim();
        if(!idNumber) return alert("Please enter Identification Number");

        $.post("backend/bk_LibraryMenu/bk_libLogs-Test.php", { request: "validateUser", idNumber }, function(res) {
            if(res.error){
                selectedUser = { id_number:idNumber, name:"Guest", classification:"GUEST", college:null, course:null, sex:null };
                currentAction = "checkin";
                return showUserModal(selectedUser,"success","fa-sign-in-alt","Check In","Guest user");
            }

            if(res.duplicate){
                duplicateCandidates = res.matches;
                return showDuplicateModal();
            }

            selectedUser = res.data;
            $.post("backend/bk_LibraryMenu/bk_libLogs-Test.php",{ request:"checkStatusToday", idNumber:selectedUser.id_number }, function(status){
                determineAction(selectedUser,status);
            });
        });
    }

    // ======================
    // DETERMINE ACTION
    // ======================
    function determineAction(user,status){
        let action="checkin", color="success", icon="fa-sign-in-alt", btnText="Check In", message="Not checked in yet";

        const userSection = parseInt(status.sectionID || 0);
        const currentSection = parseInt(currentLibraryID || 0);

        if(status.checkedIn){
            if(userSection===currentSection){
                action="checkout"; color="danger"; icon="fa-sign-out-alt"; btnText="Check Out"; message="Currently in this library";
            } else {
                action="switch"; color="warning"; icon="fa-random"; btnText="Switch & Check In"; message="Checked in different library: auto-checkout previous";
            }
        }
        currentAction = action;
        showUserModal(user, color, icon, btnText, message);
    }

    // ======================
    // MODAL FUNCTIONS
    // ======================
    function showUserModal(user,color,icon,btnText,message){
        const body = `
            <div class="text-center mb-3">
                <div class="badge bg-${color} fs-6 p-2"><i class="fas ${icon} me-2"></i>${btnText} Confirmation</div>
                <p class="text-muted mt-2 small">${message}</p>
            </div>
            <div class="bg-light p-3 rounded">
                <div class="row mb-2"><div class="col-5 fw-semibold">ID</div><div class="col-7">${user.id_number}</div></div>
                <div class="row mb-2"><div class="col-5 fw-semibold">Name</div><div class="col-7">${user.name}</div></div>
                <div class="row mb-2"><div class="col-5 fw-semibold">Sex</div><div class="col-7">${user.sex||'N/A'}</div></div>
                <div class="row mb-2"><div class="col-5 fw-semibold">College</div><div class="col-7">${user.college||'N/A'}</div></div>
                <div class="row mb-2"><div class="col-5 fw-semibold">Course</div><div class="col-7">${user.course||'N/A'}</div></div>
                <hr>
                <div class="text-center fw-bold text-primary">Library: ${currentLibraryName}</div>
            </div>
        `;
        const footer = `
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-${color}" id="confirmAttendance"><i class="fas ${icon} me-1"></i>${btnText}</button>
        `;
        showModal("Attendance", body, footer);
    }

    function showDuplicateModal(){
        const body = `
            <div class="text-center mb-3">
                <div class="badge bg-warning fs-6 p-2"><i class="fas fa-user-shield me-2"></i>Duplicate ID Found</div>
                <p class="text-muted mt-2">Enter your Birth Date To Confirm (e.g. mm/dd/yy)
            <div class="card bg-light p-3 border-0">
                <div class="input-group mb-2">
                    <input type="password" id="modalSecretKey" class="form-control text-center fw-bold fs-4" maxlength="6" placeholder="••••••">
                    <button class="btn btn-outline-secondary" type="button" id="toggleSecretKey"><i class="fas fa-eye" id="secretIcon"></i></button>
                </div>
                <div id="secretKeyStatus" class="small text-muted mt-1"><i class="fas fa-info-circle me-1"></i>Enter 6-digit key</div>
            </div>
            <div id="verifiedStudentContainer" style="display:none;"></div>
        `;
        showModal("Identity Verification", body, "");
    }

    $(document).on('input','#modalSecretKey', function(){
        const key = $(this).val();
        if(key.length===6) validateSecretKey(key);
        else {
            $("#verifiedStudentContainer").hide().empty();
            $("#secretKeyStatus").html('<i class="fas fa-info-circle me-1"></i>Enter 6-digit key').removeClass('text-success text-danger');
        }
    });

    function validateSecretKey(key){
        const match = duplicateCandidates.find(u=>u.secretKey===key);
        if(match){
            selectedUser = match;
            currentAction = "checkin";
            $("#secretKeyStatus").html('<span class="text-success"><i class="fas fa-check-circle me-1"></i>Verified</span>');
            $("#verifiedStudentContainer").show();
            $.post("backend/bk_LibraryMenu/bk_libLogs-Test.php",{ request:"checkStatusToday", idNumber:selectedUser.id_number }, function(status){
                determineAction(selectedUser,status);
            });
        } else {
            $("#secretKeyStatus").html('<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>Invalid Key</span>');
            $("#verifiedStudentContainer").hide().empty();
        }
    }

    function showModal(title, body, footer=""){
        $("#dynamicModalTitle").html(title);
        $("#dynamicModalBody").html(body);
        $("#dynamicModalFooter").html(footer);
        $("#dynamicModal").modal("show");
    }

    // ======================
    // PROCESS ATTENDANCE
    // ======================
    function processAttendance(user,action){
        if(!currentLibraryID || !user.id_number) return;
        $("#dynamicModal").modal("hide");
        saveAttendance(user, action === "switch" ? "checkin" : action);
    }

    function saveAttendance(user, action){
        $.post("backend/bk_LibraryMenu/bk_libLogs-Test.php",{
            request:"saveAttendance",
            action,
            idNumber:user.id_number,
            sectionID:currentLibraryID,
            classification:user.classification || "STUDENT",
            name:user.name,
            college:user.college,
            course:user.course,
            sex:user.sex
        }, function(res){
            if(res.error) return alert(res.error);
            loadKPI(currentLibraryID);
            $("#inputStudentNumber").val("");
            showModal("Success", `<div class="alert alert-success text-center"><i class="fas fa-check-circle me-2"></i>Successfully ${action}ed!</div>`);
            setTimeout(()=>$("#dynamicModal").modal("hide"),2000);
        });
    }

});
</script>
