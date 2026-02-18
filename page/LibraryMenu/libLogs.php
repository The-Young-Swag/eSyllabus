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
        <!-- Library dropdown removed -->
    </div>
    <div class="card-body">
        <form id="logForm" class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label class="form-label fw-semibold">Identification Number</label>
                <small class="text-muted d-block mb-2">(Student Number or Employee Number)</small>
                <div class="input-group">
                    <input type="password" class="form-control form-control-lg" id="inputStudentNumber" placeholder="Enter identification number" autocomplete="off">
                    <button type="button" class="btn btn-outline-secondary" id="toggleIdVisibility">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <div class="col-lg-4" id="specialKeyContainer" style="display:none;">
                <label class="form-label fw-semibold">Special Key (Birthday: MMDDYY)</label>
                <input type="password" class="form-control form-control-lg" id="inputSpecialKey" maxlength="6" placeholder="Enter 6-digit key" autocomplete="off">
            </div>

            <div class="col-lg-3">
                <label class="form-label fw-semibold">Library Section</label>
                <div class="form-control form-control-lg bg-light" id="currentLibraryDisplay"></div>
            </div>

            <div class="col-lg-12 d-grid mt-3">
                <button type="submit" class="btn btn-success btn-lg fw-semibold" id="submitButton">Submit</button>
            </div>
        </form>
    </div>
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
                <p class="text-muted mt-2">Enter your secret key to verify identity</p>
            </div>
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
