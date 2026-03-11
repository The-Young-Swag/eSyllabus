<div class="container-fluid py-4">

<style>
  @keyframes ldot {
    0%,100% { opacity:1; }
    50%      { opacity:.35; }
  }
  #toggleIdVisibility:hover { color:#064e3b !important; }
  #logForm button[type=submit]:hover {
    background:#047857 !important;
    box-shadow:0 5px 18px rgba(6,78,59,.25) !important;
  }
</style>

<div class="px-1">

  <!-- ═══ HEADER ═══════════════════════════════════════════════ -->
  <div class="card border-0 rounded-4 mb-3" style="box-shadow:0 1px 6px rgba(0,0,0,.07);">
    <div class="card-body py-3 px-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>
          <div class="d-flex align-items-center mb-1" style="gap:9px;">
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;
                         background:#10b981;flex-shrink:0;
                         animation:ldot 2.2s ease-in-out infinite;"></span>
            <span class="fw-bold text-dark" style="font-size:1rem;letter-spacing:-.2px;">Library Attendance Dashboard</span>
          </div>
          <div class="text-muted" style="font-size:.76rem;padding-left:17px;">Real-time monitoring of today's attendance activity</div>
        </div>

<!-- Time badge -->
<div class="px-4 py-3 d-flex flex-column"
     style="background:linear-gradient(135deg,#f0fdf9,#e6faf4);
            border:1px solid #a7f3d0;
            border-radius:16px;
            min-width:280px;
            box-shadow:0 2px 12px rgba(6,78,59,.08),inset 0 1px 0 rgba(255,255,255,.8);">
  <span class="text-uppercase fw-bold" style="font-size:.6rem;letter-spacing:.12em;color:#6ee7b7;">Current Date &amp; Time</span>
  <span id="kpiCurrentTime" class="fw-semibold mt-1" style="font-size:1rem;color:#064e3b;letter-spacing:-.2px;">—</span>
</div>

      </div>
    </div>
  </div>

  <!-- ═══ LOG ATTENDANCE ════════════════════════════════════════ -->
  <div class="card border-0 rounded-4 mb-3"
       style="box-shadow:0 2px 16px rgba(6,78,59,.1);border:1.5px solid #6ee7b7 !important;">
    <div class="card-body p-4"
         style="background:linear-gradient(150deg,#e8faf3 0%,#f0fdf9 40%,#eaf6f2 100%);
                border-radius:calc(1rem - 1px);">

      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pb-3 mb-4"
           style="border-bottom:1px solid #c6ead9;">
        <div>
          <div class="fw-bold text-dark mb-1" style="font-size:.96rem;">Library Access</div>
          <div class="text-muted" style="font-size:.75rem;">Secure identification verification</div>
        </div>

<!-- Section badge -->
<div class="d-flex align-items-center px-3 py-3"
     style="background:#ffffff;
            border:1px solid #a7f3d0;
            border-radius:16px;
            gap:12px;min-width:280px;
            box-shadow:0 2px 12px rgba(6,78,59,.08),inset 0 1px 0 rgba(255,255,255,.9);">
  <div class="d-flex align-items-center justify-content-center flex-shrink-0"
       style="width:36px;height:36px;
              border-radius:10px;
              background:linear-gradient(135deg,#d1fae5,#a7f3d0);
              color:#047857;font-size:.85rem;
              box-shadow:0 1px 4px rgba(6,78,59,.12);">
    <i class="fas fa-book-open"></i>
  </div>
  <div style="line-height:1.5;">
    <div class="text-uppercase fw-bold" style="font-size:.6rem;letter-spacing:.12em;color:#6ee7b7;margin-bottom:1px;">Library Section</div>
    <div class="fw-bold" style="font-size:.9rem;color:#064e3b;white-space:nowrap;" id="currentLibraryDisplay">Main Library</div>
  </div>
</div>
		
      </div>
      <form id="logForm" autocomplete="off">
        <label for="inputStudentNumber"
               class="d-block fw-bold text-uppercase mb-2"
               style="font-size:.67rem;letter-spacing:.09em;color:#3d8a6e;">Identification Number</label>
        <div style="padding:2px;border-radius:11px;
                    background:linear-gradient(130deg,#10b981 0%,#3b82f6 55%,#06b6d4 100%);
                    box-shadow:0 3px 16px rgba(16,185,129,.15);">
          <div class="d-flex align-items-center bg-white" style="border-radius:9px;overflow:hidden;">
            <input type="password"
                   id="inputStudentNumber"
                   placeholder="Enter or scan identification number"
                   autocomplete="new-password"
                   spellcheck="false"
                   style="flex:1;border:none;outline:none;box-shadow:none;
                          background:transparent;padding:12px 16px;
                          font-size:.92rem;color:#0f172a;">
            <button type="button" id="toggleIdVisibility"
                    style="background:transparent;border:none;outline:none;
                           padding:0 14px;color:#94a3b8;cursor:pointer;
                           line-height:1;transition:color .15s;">
              <i class="fas fa-eye" id="toggleIcon"></i>
            </button>
          </div>
        </div>

        <hr class="my-4" style="border-color:#c6ead9;">
        <button type="submit"
                class="btn w-100 fw-semibold text-white py-3"
                style="background:#064e3b;border:none;border-radius:8px;
                       font-size:.9rem;letter-spacing:.02em;
                       transition:background .18s,box-shadow .18s;">
          Confirm Access
        </button>
      </form>
	  
<!-- ═══ GUEST CHECK-IN / CHECK-OUT BUTTONS ═══ -->
<div class="text-center mt-3">
  <div class="d-inline-flex gap-2 position-relative">

    <!-- CHECK IN -->
    <div class="d-inline-block position-relative">
      <button type="button" id="guestCheckIn"
              class="btn rounded-pill fw-semibold px-4 py-2"
              style="background:#f0fdf9;color:#047857;border:1.5px solid #6ee7b7;
                     font-size:.82rem;letter-spacing:.02em;
                     transition:background .18s,box-shadow .18s,transform .15s;">
        <i class="fas fa-user-clock me-2"></i>&nbsp;&nbsp;Check In as Guest
      </button>
<div id="guestNudge" class="position-absolute" 
     style="right:calc(100% + 12px);top:50%;transform:translateY(-50%);
            background:#fff;border:1.5px solid #a7f3d0;border-radius:12px;
            padding:6px 14px;font-size:.75rem;color:#047857;font-weight:600;
            white-space:nowrap;box-shadow:0 4px 14px rgba(6,78,59,.12);
            opacity:0;pointer-events:none;transition:opacity .4s ease;z-index:10;">
  <span style="position:absolute;right:-8px;top:50%;transform:translateY(-50%);
               border-top:7px solid transparent;border-bottom:7px solid transparent;
               border-left:8px solid #a7f3d0;"></span>
  <span style="position:absolute;right:-6px;top:50%;transform:translateY(-50%);
               border-top:6px solid transparent;border-bottom:6px solid transparent;
               border-left:7px solid #fff;"></span>
  👋 Not a student / just visiting?
</div>
    </div>
    <!-- CHECK OUT -->
    <button type="button" id="guestCheckOut"
            class="btn rounded-pill fw-semibold px-4 py-2"
            style="background:#fff5f5;color:#dc2626;border:1.5px solid #fca5a5;
                   font-size:.82rem;letter-spacing:.02em;
                   transition:background .18s,box-shadow .18s,transform .15s;">
      <i class="fas fa-sign-out-alt me-2"></i>&nbsp;&nbsp;Check Out as Guest
    </button>

  </div>
</div>
    </div>
  </div>

<!-- ═══ KPI CARDS ════════════════════════════════════════════ -->
<div class="row g-3">
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card border-0 rounded-4 overflow-hidden"
         style="box-shadow:0 1px 6px rgba(0,0,0,.07);border-top:3px solid #10b981 !important;height:130px;">
      <div class="card-body d-flex flex-column align-items-center text-center" style="padding:14px 12px;">
        <div class="text-uppercase fw-bold text-muted" style="font-size:.64rem;letter-spacing:.09em;">Total Check-Ins Today</div>
        <div class="flex-grow-1 d-flex align-items-center justify-content-center">
          <div id="kpiTotalCheckins" style="font-size:1.75rem;font-weight:600;color:#10b981;line-height:1;">—</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card border-0 rounded-4 overflow-hidden"
         style="box-shadow:0 1px 6px rgba(0,0,0,.07);border-top:3px solid #3b82f6 !important;height:130px;">
      <div class="card-body d-flex flex-column align-items-center text-center" style="padding:14px 12px;">
        <div class="text-uppercase fw-bold text-muted" style="font-size:.64rem;letter-spacing:.09em;">Currently In Attendance</div>
        <div class="flex-grow-1 d-flex align-items-center justify-content-center">
          <div id="kpiActiveStudents" style="font-size:1.75rem;font-weight:600;color:#3b82f6;line-height:1;">—</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card border-0 rounded-4 overflow-hidden"
         style="box-shadow:0 1px 6px rgba(0,0,0,.07);border-top:3px solid #f59e0b !important;height:130px;">
      <div class="card-body d-flex flex-column justify-content-center" style="padding:14px 16px;">
        <div class="text-uppercase fw-bold text-muted mb-2 text-center" style="font-size:.64rem;letter-spacing:.09em;">Top 3 Colleges Today</div>
        <div id="topColleges">
          <div class="d-flex align-items-baseline gap-2 mb-1">
            <span class="fw-bold text-muted" style="font-size:.67rem;width:14px;flex-shrink:0;">1.</span>
            <span class="text-warning fw-semibold" style="font-size:.75rem;">Loading...</span>
          </div>
          <div class="d-flex align-items-baseline gap-2 mb-1">
            <span class="fw-bold text-muted" style="font-size:.67rem;width:14px;flex-shrink:0;">2.</span>
            <span class="text-warning fw-semibold" style="font-size:.75rem;">Loading...</span>
          </div>
          <div class="d-flex align-items-baseline gap-2">
            <span class="fw-bold text-muted" style="font-size:.67rem;width:14px;flex-shrink:0;">3.</span>
            <span class="text-warning fw-semibold" style="font-size:.75rem;">Loading...</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-sm-6 col-lg-3">
    <div class="card border-0 rounded-4 overflow-hidden"
         style="box-shadow:0 1px 6px rgba(0,0,0,.07);border-top:3px solid #06b6d4 !important;height:130px;">
      <div class="card-body d-flex flex-column justify-content-center" style="padding:14px 16px;">
        <div class="text-uppercase fw-bold text-muted mb-2 text-center" style="font-size:.64rem;letter-spacing:.09em;">Top 3 Courses Today</div>
        <div id="topCourses">
          <div class="d-flex align-items-baseline gap-2 mb-1">
            <span class="fw-bold text-muted" style="font-size:.67rem;width:14px;flex-shrink:0;">1.</span>
            <span class="text-info fw-semibold" style="font-size:.75rem;">Loading...</span>
          </div>
          <div class="d-flex align-items-baseline gap-2 mb-1">
            <span class="fw-bold text-muted" style="font-size:.67rem;width:14px;flex-shrink:0;">2.</span>
            <span class="text-info fw-semibold" style="font-size:.75rem;">Loading...</span>
          </div>
          <div class="d-flex align-items-baseline gap-2">
            <span class="fw-bold text-muted" style="font-size:.67rem;width:14px;flex-shrink:0;">3.</span>
            <span class="text-info fw-semibold" style="font-size:.75rem;">Loading...</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div><!-- /row -->

</div>

</div>

<?php include '../modalContainer.php'; ?>
<?php include 'LibModals.php'; ?>

<script>
(() => {
  const n=document.getElementById('guestNudge'),b=document.getElementById('guestCheckIn');

  b.onmouseenter=()=>{b.style.background='#d1fae5';b.style.boxShadow='0 4px 14px rgba(6,78,59,.15)';b.style.transform='translateY(-1px)';};
  b.onmouseleave=()=>{b.style.background='#f0fdf9';b.style.boxShadow='';b.style.transform='';};

  const show=()=>{n.style.opacity='1';setTimeout(()=>{n.style.opacity='0'},1800)};
  setTimeout(()=>{show();setInterval(show,3000)},10000);
})();

$(document).ready(function () {

    // =========================================================================
    // STATE
    // =========================================================================
    let currentLibraryID    = null;
    let currentLibraryName  = '';
    let selectedUser        = null;
    let duplicateCandidates = [];
    let currentAction       = 'checkin';

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

$.post(BACKEND, {
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
// GUEST CHECK-IN
// =========================================================================
$("#guestCheckIn").on("click", () => {
    if (!currentLibraryID) return alert("Library section not loaded yet.");
    $.post(BACKEND, { request: "GuestModal", libraryName: currentLibraryName },
        res => {
            if (!res.success) return alert(res.error || "Failed to load guest form.");
            showModal("Guest Check-In", res.body, res.footer);
        }
    ).fail(xhr => { console.error(xhr.responseText); alert("Connection error."); });
});

// Hover styles for checkout button
$("#guestCheckOut").on("mouseenter", function () {
    $(this).css({ background: "#fee2e2", boxShadow: "0 4px 14px rgba(220,38,38,.15)", transform: "translateY(-1px)" });
}).on("mouseleave", function () {
    $(this).css({ background: "#fff5f5", boxShadow: "", transform: "" });
});

// =========================================================================
// GUEST CHECK-OUT — load active guests list
// =========================================================================
$("#guestCheckOut").on("click", () => {
    if (!currentLibraryID) return alert("Library section not loaded yet.");
    $.post(BACKEND, { request: "GuestCheckoutModal", sectionID: currentLibraryID, libraryName: currentLibraryName },
        res => {
            if (!res.success) return alert(res.error || "Failed to load guest list.");
            showModal("Guest Check-Out", res.body, res.footer);
        }
    ).fail(xhr => { console.error(xhr.responseText); alert("Connection error."); });
});

// =========================================================================
// GUEST CHECK-OUT — search filter (live)
// =========================================================================
$(document).on("input", "#guestSearchInput", function () {
    const q = $(this).val().toLowerCase();
    $("#guestCheckoutList .guest-row").each(function () {
        $(this).toggle($(this).data("name").toLowerCase().includes(q));
    });
    $("#guestNoResults").toggle($("#guestCheckoutList .guest-row:visible").length === 0);
});

// ─── Guest Checkout Handler ──────────────────────────────
$(document).on("click",".btn-guest-checkout",function(){
    const logID=$(this).data("logid"),
          guestName=$(this).data("name");
    if(!logID) return;

    showModal("Success",
        `<div class="alert alert-danger text-center mb-0">
            <i class="fas fa-sign-out-alt me-2"></i>
            <strong>${guestName}</strong> successfully checked out.
         </div>`
    );

    let checkoutTimer=setTimeout(()=>{
        checkoutTimer=null;
        $("#dynamicModal").modal("hide");
    },2000);

    $.post(BACKEND,
        {request:"guestCheckout",logID,sectionID:currentLibraryID},
        response=>{
            if(response.error){
                showModal("Error",
                    `<div class="alert alert-danger text-center mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i>${response.error}
                     </div>`
                );
                return;
            }
            loadKPI(currentLibraryID);
        }
    );
});

// ─── Guest Check-In Handler ──────────────────────────────
$(document).on("click","#confirmGuestCheckIn",function(){
    const guestName=$("#guestName").val().trim(),
          guestSex=$("#guestSex").val(),
          guestAgency=$("#guestAgency").val().trim();

    if(!guestName){ alert("Guest name is required."); return; }
    if(!guestSex){ alert("Please select a sex."); return; }
    if(!guestAgency){ alert("Agency / Organization required."); return; }

    const guestUser={
        id_number:"",
        name:guestName,
        classification:"GUEST",
        college:"",
        course:"",
        sex:guestSex,
        agency_organization:guestAgency
    };

    processAttendance(guestUser,"checkin");
});
	
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
        const $input   = $("#inputStudentNumber");
        const $icon    = $("#toggleIcon");
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

    // Secret key input — auto-format and fire once 8 digits are entered
    $(document).on('input', '#modalSecretKey', function () {
        let raw = $(this).val().replace(/\D/g, '').substring(0, 8);

        // Auto-format as MM/DD/YYYY while typing
        let formatted = raw;
        if (raw.length > 4) formatted = raw.slice(0,2) + '/' + raw.slice(2,4) + '/' + raw.slice(4);
        else if (raw.length > 2) formatted = raw.slice(0,2) + '/' + raw.slice(2);

        $(this).val(formatted);

        if (raw.length === 8) {
            validateSecretKey(raw);
        } else {
            $("#verifiedStudentContainer").hide().empty();
            setSecretKeyStatus('muted', 'fa-info-circle', 'Enter birth date (MM/DD/YYYY)');
        }
    });

    // =========================================================================
    // USER VALIDATION
    // JS owns: routing, state, action determination
    // PHP owns: HTML rendering (modal body/footer)
    // =========================================================================
function validateUser() {
    const idNumber = $("#inputStudentNumber").val().trim();
    if (!idNumber) return alert("Please enter an Identification Number.");

    $.post(BACKEND, { request: "validateUser", idNumber }, function (res) {

        if (res.error) {
            alert("No record found for that ID number.");
            $("#inputStudentNumber").val("").focus();
            return;
        }

        if (res.duplicate) {
            duplicateCandidates = res.matches;
            showModal("Identity Verification", res.modalHTML, "");
            return;
        }

        selectedUser = res.data;
        $.post(BACKEND, { 
    request: "checkStatusToday", 
    idNumber: selectedUser.id_number,
    name: selectedUser.name
}, function (status) {
            determineAction(selectedUser, status);
        });
    });
}

function determineAction(user,status){
    let action="checkin",color="success",icon="fa-sign-in-alt",btnText="Check In",message="Not checked in yet";

    if(status.checkedIn){
        const sameSection=Number(status.sectionID)===Number(currentLibraryID);
        if(sameSection){
            action="checkout"; color="danger"; icon="fa-sign-out-alt"; btnText="Check Out"; message="Currently in this library";
        } else {
            const prevLibrary=status.sectionName||"another library";
            action="switch"; color="warning"; icon="fa-random"; btnText="Switch & Check In";
            message=`You forgot to check out at <strong>${prevLibrary}</strong>. No worries — we've automatically checked you out and completed your check-in here.`;
        }
    }

    currentAction=action;

    $.post(BACKEND,{
        request:"AttendanceModal",
        user:JSON.stringify(user),
        color,icon,btnText,message,
        libraryName:currentLibraryName
    },res=>res.success && showModal("Attendance Confirmation",res.body,res.footer));
}

    // =========================================================================
    // SECRET KEY VERIFICATION  (duplicate ID flow)
    // JS finds the match from cached candidates, then asks PHP to render modal
    // =========================================================================
    function validateSecretKey(key) {
        const match = duplicateCandidates.find(u => {
            if (!u.secretKey) return false;
            return u.secretKey.replace(/\D/g, '') === key;
        });

        if (match) {
            selectedUser = match;
            setSecretKeyStatus('success', 'fa-check-circle', 'Identity verified');
            $("#verifiedStudentContainer").show();
            $.post(BACKEND, { 
    request: "checkStatusToday", 
    idNumber: selectedUser.id_number,
    name: selectedUser.name
}, function (status) {
                determineAction(selectedUser, status);
            });
        } else {
            setSecretKeyStatus('danger', 'fa-exclamation-circle', 'Invalid key — try again');
            $("#verifiedStudentContainer").hide().empty();
        }
    }

    function setSecretKeyStatus(type, faIcon, text) {
        const colorClass = type === 'success' ? 'text-success'
                         : type === 'danger'  ? 'text-danger'
                         : 'text-muted';
        $("#secretKeyStatus").html(
            `<span class="${colorClass}"><i class="fas ${faIcon} me-1"></i>${text}</span>`
        );
    }

    // =========================================================================
    // MODAL HELPER — pure injection, no HTML built here
    // =========================================================================
    let successTimer = null;

    function showModal(title, body, footer = "") {
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
    if (!currentLibraryID) return;
    if (user.classification !== 'GUEST' && !user.id_number) return;

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
        request:             "saveAttendance",
        action,
        idNumber:            user.id_number,
        sectionID:           currentLibraryID,
        classification:      user.classification || "STUDENT",
        name:                user.name,
        college:             user.college  || "",
        course:              user.course   || "",
        sex:                 user.sex      || "",
        agency_organization: user.agency_organization || "",
    }, function (res) {
        if (res.error) {
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