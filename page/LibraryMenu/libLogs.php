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
    <div class="text-uppercase fw-bold" style="font-size:.6rem;letter-spacing:.12em;color:#6ee7b7;margin-bottom:1px;"><b>Library Section</b></div>
    <div class="fw-bold" style="font-size:.9rem;color:#064e3b;white-space:nowrap;" id="currentLibraryDisplay">Main Library</div>
  </div>
</div>


<!-- Time badge -->
<div class="px-4 py-3 d-flex flex-column"
style="background:linear-gradient(135deg,#ffffff,#ecfdf5);
    border:2px solid #6ee7b7;
    border-radius:18px;
    min-width:280px;
    padding:14px 20px;
    box-shadow: 0 6px 20px rgba(0,0,0,.06),0 2px 6px rgba(0,0,0,.04),
    inset 0 1px 0 rgba(255,255,255,.9);">
  <span class="text-uppercase fw-bold" style="font-size:.6rem;letter-spacing:.12em;color:#6ee7b7;"><b>Current Date &amp; Time</b></span>
  <span id="kpiCurrentTime" class="fw-semibold mt-1" style="font-size:1rem;color:#064e3b;letter-spacing:-.2px;">—</span>
</div>


      </div>
      <form id="logForm" autocomplete="off">
        <label for="inputIDNumber"
               class="d-block fw-bold text-uppercase mb-2"
               style="font-size:.67rem;letter-spacing:.09em;color:#3d8a6e;">Identification Number</label>
        <div style="padding:2px;border-radius:11px;
                    background:linear-gradient(130deg,#10b981 0%,#3b82f6 55%,#06b6d4 100%);
                    box-shadow:0 3px 16px rgba(16,185,129,.15);">
          <div class="d-flex align-items-center bg-white" style="border-radius:9px;overflow:hidden;">
            
			<input type="password"
                   id="inputIDNumber"
                   placeholder="Enter Student Number or Employee Number :"
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
  <!-- 1. Changed to d-flex for w-100 support; justify-content-center handles the alignment -->
  <div class="d-flex justify-content-center align-items-center position-relative w-100">

    <!-- CHECK IN WRAPPER -->
    <!-- 2. Added mr-4 (margin-right) to replace gap-50 -->
    <div class="position-relative mr-4">
      <button type="button" id="guestCheckIn"
              class="btn rounded-pill font-weight-bold px-4 py-2"
              style="background:#f0fdf9;color:#047857;border:1.5px solid #6ee7b7;
                     font-size:.82rem;letter-spacing:.02em;
                     transition:all .18s;">
        <i class="fas fa-user-clock mr-2"></i>Check In as Guest
      </button>

      <!-- GUEST NUDGE -->
      <div id="guestNudge" class="position-absolute" 
           style="right:calc(100% + 15px);top:50%;transform:translateY(-50%);
                  background:#fff;border:1.5px solid #a7f3d0;border-radius:12px;
                  padding:6px 14px;font-size:.75rem;color:#047857;font-weight:600;
                  white-space:nowrap;box-shadow:0 4px 14px rgba(6,78,59,.12);
                  opacity:1; /* Set to 1 to test visibility */
                  pointer-events:none;transition:opacity .4s ease;z-index:10;">
        <!-- Tooltip Arrow -->
        <span style="position:absolute;right:-8px;top:50%;transform:translateY(-50%);
                    border-top:7px solid transparent;border-bottom:7px solid transparent;
                    border-left:8px solid #a7f3d0;"></span>
        👋 Not a student / just visiting?
      </div>
    </div>

    <!-- CHECK OUT -->
    <button type="button" id="guestCheckOut"
            class="btn rounded-pill font-weight-bold px-4 py-2"
            style="background:#fff5f5;color:#dc2626;border:1.5px solid #fca5a5;
                   font-size:.82rem;letter-spacing:.02em;
                   transition:all .18s;">
      <i class="fas fa-sign-out-alt mr-2"></i>Check Out as Guest
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
$(function () {

const BACKEND = "backend/bk_LibraryMenu/bk_libLogs.php";

let libraryID = null;
let libraryName = "";
let currentUser = null;
let duplicates = [];
let successTimer = null;

// Single teardown prevents stacked listeners on AJAX reload
$(document).off(".libLogs");

// Clock
setInterval(function () {
    $("#kpiCurrentTime").text(new Date().toLocaleString("en-US", {
        hour: "2-digit", minute: "2-digit", second: "2-digit",
        year: "numeric", month: "short", day: "numeric", hour12: true
    }));
}, 1000);

// Nudge animation
setTimeout(function cycle() {
    $("#guestNudge").css("opacity", "1");
    setTimeout(function () {
        $("#guestNudge").css("opacity", "0");
        setTimeout(cycle, 2000);
    }, 5000);
}, 2000);

// Hover effects
function applyHover(selector, enterStyles, leaveStyles) {
    $(selector)
        .on("mouseenter", function () { $(this).css(enterStyles); })
        .on("mouseleave", function () { $(this).css(leaveStyles); });
}

applyHover("#guestCheckIn",
    { background: "#d1fae5", boxShadow: "0 4px 14px rgba(6,78,59,.15)", transform: "translateY(-1px)" },
    { background: "#f0fdf9", boxShadow: "", transform: "" }
);
applyHover("#guestCheckOut",
    { background: "#fee2e2", boxShadow: "0 4px 14px rgba(220,38,38,.15)", transform: "translateY(-1px)" },
    { background: "#fff5f5", boxShadow: "", transform: "" }
);

// Modal helpers
function showModal(title, body, footer) {
    clearTimeout(successTimer);
    successTimer = null;
    $("#dynamicModalTitle").html(title);
    $("#dynamicModalBody").html(body);
    $("#dynamicModalFooter").html(footer || "");
    $("#dynamicModal").modal("show");
}

function hideModal() {
    clearTimeout(successTimer);
    successTimer = null;
    $("#dynamicModal").modal("hide");
}

function showMessage(type, message, autoClose) {
    const styleMap = {
        success: { icon: "fa-check-circle", colorClass: "alert-success", title: "Success" },
        error: { icon: "fa-exclamation-circle",  colorClass: "alert-danger",  title: "Error"   },
        info: { icon: "fa-info-circle", colorClass: "alert-info", title: "Info"    }
    };
    const style = styleMap[type];
    showModal(style.title, `
        <div class="alert ${style.colorClass} text-center mb-0">
            <i class="fas ${style.icon} me-2"></i>${message}
        </div>
    `);
    if (autoClose) successTimer = setTimeout(hideModal, autoClose);
}

// Load library on boot
$.ajax({
    type: "POST",
    url: BACKEND,
    data: { request: "getLibraries", userID: UserInfo.UserID },
    success: function (response) {
        if (!response.success || !response.data || !response.data.length) {
            $("#currentLibraryDisplay").text(response.error || "No Library Access");
            return;
        }
        libraryID = response.data[0].SectionID;
        libraryName = response.data[0].SectionName;
        $("#currentLibraryDisplay").text(libraryName);
        loadKPI();
    },
    error: function (xhr) {
    const msg = xhr.status === 0
        ? "Cannot reach the server. Check your connection."
        : "A server error occurred. Please try again.";
    showMessage("error", msg);
}
});

function loadKPI() {
    if (!libraryID) return;
    $.ajax({
        type: "POST",
        url: BACKEND,
        data: { request: "getKPI", sectionID: libraryID },
        success: function (response) {
            if (!response.success || !response.data) return;
            $("#kpiTotalCheckins").text(response.data.totalToday || 0);
            $("#kpiActiveStudents").text(response.data.currentlyInside || 0);

            function renderRankList(items, colorClass) {
                if (!items) items = ["-", "-", "-"];
                let html = "";
                items.forEach(function (label, index) {
                    html += `<div class="mb-1">
                                <span class="fw-bold">${index + 1}.</span>
                                <span class="${colorClass}">${label}</span>
                             </div>`;
                });
                return html;
            }

            $("#topColleges").html(renderRankList(response.data.topColleges, "text-warning"));
            $("#topCourses").html(renderRankList(response.data.topCourses, "text-info"));
        },
        error: function (xhr) {
    const msg = xhr.status === 0
        ? "Cannot reach the server. Check your connection."
        : "A server error occurred. Please try again.";
    showMessage("error", msg);
}
    });
}

function checkAndShowAttendance(user) {
    let resolvedAction;

    $.post(BACKEND, {
        request: "checkStatusToday",
        idNumber: user.id_number,
        name: user.name
    })
    .then(function (status) {
        const rawAction = !status.checkedIn ? "checkin"
                        : Number(status.sectionID) === Number(libraryID) ? "checkout"
                        : "switch";

        resolvedAction = rawAction === "switch" ? "checkin" : rawAction;

        return $.post(BACKEND, {
            request: "getAttendanceModal",
            user: JSON.stringify(user),
            action: rawAction,
            sectionName: status.sectionName || "",
            libraryName: libraryName
        });
    })
    .then(function (modal) {
        if (modal.success) {
            showModal("Attendance Confirmation", modal.body, modal.footer);
            // Store action on the button — no shared pendingAction variable needed
            $("#confirmAttendance").data("resolved-action", resolvedAction);
        }
    })
    .fail(function () { alert("Connection error."); });
}

function saveAttendance(user, resolvedAction) {
    if (!libraryID) return;
    if (user.classification !== "GUEST" && !user.id_number) return;

    const actionLabel = resolvedAction === "checkin" ? "checked in" : "checked out";
    showMessage("success", `<strong>${user.name}</strong> successfully ${actionLabel}.`, 2000);

    $.ajax({
        type: "POST",
        url: BACKEND,
        data: {
            request: "getSaveAttendance",
            action: resolvedAction,
            idNumber: user.id_number,
            sectionID: libraryID,
            classification: user.classification || "STUDENT",
            name: user.name,
            college: user.college || "",
            course: user.course || "",
            sex: user.sex || "",
            agency_organization: user.agency_organization || ""
        },
        success: function (response) {
            if (response.error) { showMessage("error", response.error); return; }
            loadKPI();
            $("#inputIDNumber").val("");
        },
        error: function (xhr) {
    const msg = xhr.status === 0
        ? "Cannot reach the server. Check your connection."
        : "A server error occurred. Please try again.";
    showMessage("error", msg);
}
    });
}

function updateGuestUI() {
    const guestCount = $("#guestCheckOutList .guest-row").length;
    const guestLabel = guestCount === 1 ? "guest" : "guests";
    $("#dynamicModal .badge.rounded-pill").text(`${guestCount} ${guestLabel}`);
    $("#guestEmptyState").toggle(guestCount === 0);
    $("#guestSearchInput").trigger("input");
}

// ── Event handlers ────────────────────────────────────────────────────────────

// ID form submit
$(document).on("submit.libLogs", "#logForm", function (event) {
    event.preventDefault();
    const idNumber = $("#inputIDNumber").val().trim();
    if (!idNumber) { alert("Please enter an Identification Number."); return; }

    $.ajax({
        type: "POST",
        url: BACKEND,
        data: { request: "getValidateUser", idNumber: idNumber },
        success: function (response) {
            if (response.error) {
                alert("No record found for that ID number.");
                $("#inputIDNumber").val("").focus();
                return;
            }
            if (response.duplicate) {
                duplicates = response.matches;
                showModal("Identity Verification", response.modalHTML);
                return;
            }
            currentUser = response.data;
            checkAndShowAttendance(response.data);
        },
        error: function (xhr) {
    const msg = xhr.status === 0
        ? "Cannot reach the server. Check your connection."
        : "A server error occurred. Please try again.";
    showMessage("error", msg);
}
    });
});

// Confirm attendance — action is read from button data, not a shared variable
$(document).on("click.libLogs", "#confirmAttendance", function () {
    if (!currentUser) return;
    saveAttendance(currentUser, $(this).data("resolved-action"));
});

// Secret key for duplicate users
$(document).on("input.libLogs", "#modalSecretKey", function () {
    const digits = $(this).val().replace(/\D/g, "").substring(0, 8);
    const formatted = digits.length > 4 ? `${digits.slice(0,2)}/${digits.slice(2,4)}/${digits.slice(4)}`
                    : digits.length > 2 ? `${digits.slice(0,2)}/${digits.slice(2)}`
                    : digits;
    $(this).val(formatted);

    if (digits.length < 8) {
        $("#verifiedStudentContainer").hide().empty();
        $("#secretKeyStatus").html(`<span class="text-muted"><i class="fas fa-info-circle me-1"></i>Enter birth date (MM/DD/YYYY)</span>`);
        return;
    }

    const matchedUser = duplicates.find(function (candidate) {
        return candidate.secretKey?.replace(/\D/g, "") === digits;
    });

    if (matchedUser) {
        currentUser = matchedUser;
        $("#secretKeyStatus").html(`<span class="text-success"><i class="fas fa-check-circle me-1"></i>Identity verified</span>`);
        $("#verifiedStudentContainer").show();
        checkAndShowAttendance(matchedUser);
    } else {
        $("#secretKeyStatus").html(`<span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>Invalid key — try again</span>`);
        $("#verifiedStudentContainer").hide().empty();
    }
});

// Toggle ID visibility
$(document).on("click.libLogs", "#toggleIdVisibility", function () {
    const inputField = $("#inputIDNumber");
    const isHidden = inputField.attr("type") === "password";
    inputField.attr("type", isHidden ? "text" : "password");
    $("#toggleIcon").toggleClass("fa-eye fa-eye-slash");
});

// Toggle secret key visibility
$(document).on("click.libLogs", "#toggleSecretKey", function () {
    const inputField = $("#modalSecretKey");
    const isHidden = inputField.attr("type") === "password";
    inputField.attr("type", isHidden ? "text" : "password");
    $("#secretIcon").toggleClass("fa-eye fa-eye-slash");
});

// Guest check-in modal
$(document).on("click.libLogs", "#guestCheckIn", function () {
    if (!libraryID) { alert("Library section not loaded yet."); return; }
    $.ajax({
        type: "POST",
        url: BACKEND,
        data: { request: "guestCheckIn", libraryName: libraryName },
        success: function (response) {
            if (response.success) {
                showModal("Guest Check-In", response.body, response.footer);
            } else {
                alert(response.error || "Failed to load guest form.");
            }
        },
        error: function (xhr) {
    const msg = xhr.status === 0
        ? "Cannot reach the server. Check your connection."
        : "A server error occurred. Please try again.";
    showMessage("error", msg);
}
    });
});

// Confirm guest check-in
$(document).on("click.libLogs", "#confirmGuestCheckIn", function () {
    const guestFirst = $("#guestFirstName").val().trim();
    const guestMI    = $("#guestMiddleInitial").val().trim().replace(/\.+$/, "");
    const guestLast  = $("#guestLastName").val().trim();
    const guestName  = guestMI
    ? `${guestFirst} ${guestMI}. ${guestLast}`
    : `${guestFirst} ${guestLast}`;
    const guestSex = $("#guestSex").val();
    const guestOrganization = $("#guestAgency").val().trim();
    if (!guestFirst) { alert("First name is required."); return; }
    if (!guestLast)  { alert("Last name is required.");  return; } 
    if (!guestSex)
        { alert("Please select a sex.");
        return; 
    } if (!guestOrganization)
        { alert("Agency / Organization required."); 
        return;
    }
    saveAttendance({
        id_number: "",
        name: guestName,
        classification: "GUEST",
        college: "",
        course: "",
        sex: guestSex,
        agency_organization: guestOrganization
    }, "checkin");
});

// Guest check-out modal
$(document).on("click.libLogs", "#guestCheckOut", function () {
    if (!libraryID) { alert("Library section not loaded yet."); return; }
    $.ajax({
        type: "POST",
        url: BACKEND,
        data: { request: "getGuestCheckOutModal", sectionID: libraryID, libraryName: libraryName },
        success: function (response) {
            if (response.success) {
                showModal("Guest Check-Out", response.body, response.footer);
            } else {
                alert(response.error || "Failed to load guest list.");
            }
        },
        error: function (xhr) {
    const msg = xhr.status === 0
        ? "Cannot reach the server. Check your connection."
        : "A server error occurred. Please try again.";
    showMessage("error", msg);
}
    });
});

// Guest check-out action
$(document).on("click.libLogs", ".btn-guest-checkout", function () {
    const logID = $(this).data("logid");
    const guestName = $(this).data("name");
    if (!logID) return;
    $.ajax({
        type: "POST",
        url: BACKEND,
        data: { request: "guestCheckOut", logID: logID, sectionID: libraryID },
        success: function (response) {
            if (response.error) { alert(response.error); return; }
            $(`.btn-guest-checkout[data-logid="${logID}"]`)
                .closest(".guest-row")
                .fadeOut(200, function () {
                    $(this).remove();
                    updateGuestUI();
                });
            const successAlert = $(`
                <div class="alert alert-success py-2 px-3 mb-2 text-center">
                    <i class="fas fa-check-circle me-1"></i>
                    <strong>${guestName}</strong> successfully checked out.
                </div>
            `).prependTo("#guestCheckOutList");
            setTimeout(function () {
                successAlert.fadeOut(400, function () { successAlert.remove(); });
            }, 2000);
            loadKPI();
        },
        error: function (xhr) {
    const msg = xhr.status === 0
        ? "Cannot reach the server. Check your connection."
        : "A server error occurred. Please try again.";
    showMessage("error", msg);
}
    });
});

// Guest search
$(document).on("input.libLogs", "#guestSearchInput", function () {
    const searchQuery = $(this).val().toLowerCase().trim();
    let visibleCount = 0;
    $("#guestCheckOutList .guest-row").each(function () {
        const nameMatches = ($(this).data("name") || "").toLowerCase().includes(searchQuery);
        $(this).toggle(nameMatches);
        if (nameMatches) visibleCount++;
    });
    $("#guestNoResults").toggle(visibleCount === 0 && searchQuery !== "");
});

// Close modal
$(document).on("click.libLogs",
    "#dynamicModal .btn-close, #dynamicModal [data-dismiss='modal'], #dynamicModal [data-bs-dismiss='modal']",
    hideModal
);

});
</script>