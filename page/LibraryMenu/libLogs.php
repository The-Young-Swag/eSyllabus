<div class="container-fluid py-4" style="font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; max-width: 1300px; margin: 0 auto; padding-left: 24px; padding-right: 24px;">
    
    <style>
        @keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
        @keyframes pulseDot{0%,100%{opacity:1;transform:scale(1)}50%{opacity:0.4;transform:scale(0.7)}}
        @keyframes breatheBtn{0%,100%{box-shadow:0 0 0 0 rgba(6,78,59,0.4), 0 2px 8px rgba(6,78,59,0.2)}50%{box-shadow:0 0 0 12px rgba(6,78,59,0), 0 2px 8px rgba(6,78,59,0.2)}}
        @keyframes floatNudge{0%,100%{transform:translateY(-50%) translateX(0)}50%{transform:translateY(-50%) translateX(-5px)}}
        @keyframes subtlePop{0%{transform:scale(1)}50%{transform:scale(1.02)}100%{transform:scale(1)}}
        .kpi-card:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,0.08)!important;transition:all 0.2s ease;}
    </style>

    <!-- HEADER -->
    <div style="display:flex;align-items:center;gap:14px;padding:20px 24px;background:#fff;border-radius:18px;box-shadow:0 2px 8px rgba(0,0,0,0.04),0 1px 2px rgba(0,0,0,0.02);margin-bottom:16px;border:1px solid #42ff5f;animation:fadeUp .45s ease-out;">
        <div style="width:12px;height:12px;border-radius:50%;background:#10b981;flex-shrink:0;animation:pulseDot 2s ease-in-out infinite;"></div>
        <div>
            <div style="font-size:1.15rem;font-weight:700;color:#0f172a;letter-spacing:-0.3px;line-height:1.2;">Library Attendance Dashboard</div>
            <div style="font-size:0.78rem;color:#64748b;font-weight:450;">Real-time monitoring of today's attendance activity</div>
        </div>
    </div>

    <!-- Main Card -->
    <div style="background:#f0fdf4; border-radius:22px; box-shadow:0 6px 24px rgba(0,0,0,0.06),0 2px 6px rgba(0,0,0,0.04); border:1px solid #42ff5f; overflow:hidden; margin-bottom:16px; animation:fadeUp .5s ease-out .05s both;">
        <div style="padding:28px 30px; background:linear-gradient(175deg, #f0fdf4 0%, #e8faf3 40%, #f0fdf4 100%);">
            
            <!-- TOP ROW -->
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; padding-bottom:20px; margin-bottom:20px; border-bottom:1px solid #d1fae5;">
                <div style="display:flex; align-items:center; gap:14px; padding:14px 20px; background:#ffffff; border-radius:16px; border:1px solid #a7f3d0; box-shadow:0 1px 4px rgba(0,0,0,0.04); min-width:270px;">
                    <div style="width:42px; height:42px; border-radius:12px; background:linear-gradient(135deg,#d1fae5,#a7f3d0); display:flex; align-items:center; justify-content:center; color:#047857; font-size:1rem; flex-shrink:0;">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <div style="font-size:0.62rem; font-weight:800; letter-spacing:0.1em; text-transform:uppercase; color:#059669; margin-bottom:2px;">Library Section</div>
                        <div id="currentLibraryDisplay" style="font-size:0.95rem; font-weight:700; color:#064e3b; letter-spacing:-0.3px; white-space:nowrap;">Main Library</div>
                    </div>
                </div>
                <div style="display:flex; flex-direction:column; padding:14px 22px; background:#ffffff; border-radius:16px; border:1px solid #a7f3d0; box-shadow:0 1px 4px rgba(0,0,0,0.04); min-width:270px;">
                    <span style="font-size:0.62rem; font-weight:800; letter-spacing:0.1em; text-transform:uppercase; color:#059669; margin-bottom:4px;">Current Date &amp; Time</span>
                    <span id="kpiCurrentTime" style="font-size:1rem; font-weight:650; color:#064e3b; letter-spacing:-0.3px;">—</span>
                </div>
            </div>

            <form id="logForm" autocomplete="off">
                <!-- Label -->
                <div style="margin-bottom:10px; font-size:0.68rem; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:#047857;">
                    Students &amp; Employees — use your ID number below
                </div>

                <!-- ID input wrapper -->
                <div style="display:flex; align-items:stretch; border-radius:14px; border:2px solid #006700; background:linear-gradient(135deg,#d1fae5,#a7f3d0); overflow:hidden; transition:all 0.25s; box-shadow:0 2px 8px rgba(16,185,129,0.1), 0 1px 3px rgba(0,0,0,0.04);">
                    <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; padding:0 16px; background:transparent; border-right:1px solid rgba(16,185,129,0.2); min-width:62px; flex-shrink:0;">
                        <i class="fas fa-id-card" style="color:#047857; font-size:1rem;"></i>
                        <span style="font-size:0.5rem; font-weight:800; letter-spacing:0.1em; text-transform:uppercase; color:#047857; white-space:nowrap;">ID No.</span>
                    </div>
                    <input type="password" id="inputIDNumber" placeholder="Enter student/employee number here" autocomplete="new-password" spellcheck="false"
                        style="flex:1; border:none; outline:none; background:transparent; padding:16px 18px; font-size:0.96rem; color:#0f172a; letter-spacing:0.2px; font-family:inherit;">
                    <button type="button" id="toggleIdVisibility" style="background:transparent; border:none; outline:none; padding:0 16px; color:#6b7280; cursor:pointer; font-size:1rem; flex-shrink:0; transition:color 0.15s;">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>

                <!-- Subtle green gradient divider -->
                <hr style="margin:22px 0; border:none; height:1px; background:linear-gradient(to right, #10b981, transparent);">

                <!-- Confirm button -->
                <button type="submit" style="width:100%; padding:16px; background:linear-gradient(135deg, #009900 0%, #006600 100%); border:none; border-radius:12px; font-size:0.95rem; font-weight:650; color:#fff; letter-spacing:0.02em; cursor:pointer; transition:all 0.25s; display:flex; align-items:center; justify-content:center; gap:10px; font-family:inherit; box-shadow:0 6px 20px rgba(0,102,0,0.45), 0 2px 4px rgba(0,0,0,0.2); text-shadow:0 1px 2px rgba(0,0,0,0.25); animation:breatheBtn 3.2s ease-in-out infinite, subtlePop 3.2s ease-in-out infinite;">
                    <i class="fas fa-check-circle" style="font-size:1.1rem;"></i> Confirm Access
                </button>
            </form>

            <!-- GUEST SECTION -->
            <div style="margin-top:24px; border-top:1px dashed #a7f3d0; padding-top:16px;">
                <details style="font-size:0.78rem; color:#4b5563; font-family:inherit;">
                    <summary style="cursor:pointer; font-weight:600; padding:8px 0; outline:none; user-select:none; list-style:none; display:inline-flex; align-items:center; gap:6px;">
                        <span style="background:linear-gradient(135deg,#e0f2fe,#bae6fd); color:#0369a1; padding:6px 14px; border-radius:20px; font-weight:700; letter-spacing:0.03em; box-shadow:0 1px 4px rgba(14,165,233,0.15); transition:all 0.2s;">
                            <i class="fas fa-chevron-right" style="font-size:0.65rem; margin-right:4px;"></i> Not a student or employee? Click here for visitor access
                        </span>
                    </summary>
                    <div style="margin-top:12px; background:#f9fafb; border-radius:16px; padding:16px 20px; border:1px solid #10b981;">
                        <div style="font-size:0.7rem; color:#6b7280; margin-bottom:12px; text-align:center;">
                            <i class="fas fa-info-circle" style="color:#059669; margin-right:4px;"></i> 
                            Visitors only — no ID required. Please use the ID field above if you have one.
                        </div>
                        <div style="display:flex; justify-content:center; align-items:center; gap:14px; flex-wrap:wrap;">
                            <div style="position:relative; display:inline-flex;">
                                <button type="button" id="guestCheckIn" style="display:inline-flex; align-items:center; gap:8px; padding:10px 20px; border-radius:999px; border:1.5px solid #a7f3d0; background:#f0fdf6; color:#047857; font-size:0.82rem; font-weight:650; cursor:pointer; font-family:inherit; transition:all 0.15s; box-shadow:0 2px 6px rgba(0,0,0,0.05);">
                                    <i class="fas fa-user-plus" style="font-size:0.8rem;"></i> Guest Check In
                                </button>
                                <div id="guestNudge" style="position:absolute; right:calc(100% + 16px); top:50%; transform:translateY(-50%); background:#fff; border:1.5px solid #a7f3d0; border-radius:12px; padding:7px 14px; font-size:0.74rem; color:#047857; font-weight:600; white-space:nowrap; box-shadow:0 4px 14px rgba(6,78,59,0.12); pointer-events:none; z-index:5; animation:floatNudge 3s ease-in-out infinite;">
                                    <span style="position:absolute; right:-7px; top:50%; transform:translateY(-50%); border-top:6px solid transparent; border-bottom:6px solid transparent; border-left:7px solid #a7f3d0;"></span>
                                    👋 Not a student? Just visiting?
                                </div>
                            </div>
                            <button type="button" id="guestCheckOut" style="display:inline-flex; align-items:center; gap:8px; padding:10px 20px; border-radius:999px; border:1.5px solid #fca5a5; background:#fffbfb; color:#dc2626; font-size:0.82rem; font-weight:650; cursor:pointer; font-family:inherit; transition:all 0.15s; box-shadow:0 2px 6px rgba(0,0,0,0.05);">
                                <i class="fas fa-sign-out-alt" style="font-size:0.8rem;"></i> Guest Check Out
                            </button>
                        </div>
                    </div>
                </details>
            </div>

        </div>
    </div>

    <!-- ═ KPI CARDS ═ -->
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
  </div><!-- /KPI row -->

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

    const isCheckIn = resolvedAction === "checkin";

    const actionLabel = isCheckIn
        ? "checked in"
        : "checked out";

    // Inline-styled reminder cards (no external CSS dependencies)
    const reminder = isCheckIn
        ? `<div style="display:flex; align-items:center; gap:14px; margin-top:16px; padding:14px 16px; border-radius:14px; border:1px solid #fde68a; background:linear-gradient(135deg, #fffbeb, #fef3c7); box-shadow:0 2px 12px rgba(245,158,11,0.12);">
               <div style="width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; background:#fef9c3; border:1.5px solid #fde68a;">
                   ⏰
               </div>
               <div>
                   <div style="font-size:0.67rem; font-weight:800; letter-spacing:0.1em; text-transform:uppercase; color:#d97706; margin-bottom:2px;">Reminder</div>
                   <div style="font-size:0.82rem; font-weight:500; color:#92400e; line-height:1.45;">Please don't forget to <strong>check out</strong> before leaving.</div>
               </div>
           </div>`
        : `<div style="display:flex; align-items:center; gap:14px; margin-top:16px; padding:14px 16px; border-radius:14px; border:1px solid #6ee7b7; background:linear-gradient(135deg, #ecfdf5, #d1fae5); box-shadow:0 2px 12px rgba(16,185,129,0.12);">
               <div style="width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; background:#dcfce7; border:1.5px solid #6ee7b7;">
                   ✅
               </div>
               <div>
                   <div style="font-size:0.67rem; font-weight:800; letter-spacing:0.1em; text-transform:uppercase; color:#059669; margin-bottom:2px;">All Done</div>
                   <div style="font-size:0.82rem; font-weight:500; color:#065f46; line-height:1.45;">Thank you for visiting. See you next time!</div>
               </div>
           </div>`;

    showMessage(
        "success",
        `<strong>${user.name}</strong> successfully ${actionLabel}.${reminder}`,
        3500
    );

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

// Event handlers 

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
    const guestMI = $("#guestMiddleInitial").val().trim().replace(/\.+$/, "");
    const guestLast = $("#guestLastName").val().trim();
    const guestName = guestMI
    ? `${guestFirst} ${guestMI}. ${guestLast}`
    : `${guestFirst} ${guestLast}`;
    const guestSex = $("#guestSex").val();
    const guestOrganization = $("#guestAgency").val().trim();
    if (!guestFirst){ 
        alert("First name is required."); 
        return; 
    }
    if (!guestLast){ 
        alert("Last name is required.");  
        return; 
    } 
    if (!guestSex){ 
        alert("Please select a sex.");
        return; 
    } 
    if (!guestOrganization){ 
        alert("Agency / Organization required."); 
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